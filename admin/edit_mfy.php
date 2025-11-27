<?php
session_start();
require_once "../connection/config.php";

$db = new Database();

if (!isset($_SESSION['user_id'])) {
    die("Foydalanuvchi tizimga kirmagan");
}

$user_id = $_SESSION['user_id'];
$user = $db->select('users', '*', 'id = ?', [$user_id], 'i')[0] ?? null;
if (!$user) die("Foydalanuvchi topilmadi");

$operator_id = $user_id;
$viloyatlar = $db->select('viloyatlar', '*');
$tumanlar   = $db->select('tumanlar', '*');

$mahalle = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $mahalle = $db->select('mahallelar', '*', 'id = ?', [$id], 'i')[0] ?? null;
    if (!$mahalle) die("Bunday mahalla topilmadi");
}

// POST orqali saqlash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'So‘rov maʼlumotlari topilmadi']);
        exit;
    }

    $viloyat_id = intval($data['viloyat_id'] ?? 0);
    $tuman_id   = intval($data['tuman_id'] ?? 0);
    $nomi       = trim($data['nomi'] ?? '');
    $polygon    = $data['polygon'] ?? [];

    if (!$viloyat_id || !$tuman_id || !$nomi || !is_array($polygon) || !isset($polygon[0][0])) {
        echo json_encode(['status' => 'error', 'message' => 'Barcha maydonlar to‘ldirilmagan']);
        exit;
    }

    $polygon_geojson = [
        "type" => "Feature",
        "geometry" => ["type" => "Polygon", "coordinates" => $polygon],
        "properties" => ["nomi" => $nomi]
    ];

    $coords = $polygon[0];
    $sumLat = $sumLng = 0;
    foreach ($coords as $p) {
        $sumLng += $p[0];
        $sumLat += $p[1];
    }
    $center_lat = $sumLat / count($coords);
    $center_lng = $sumLng / count($coords);

    $db->update("mahallelar", [
        'viloyat_id' => $viloyat_id,
        'tuman_id'   => $tuman_id,
        'nomi'       => $nomi,
        'polygon'    => json_encode($polygon_geojson, JSON_UNESCAPED_UNICODE),
        'operator_id' => $operator_id,
        'markaz_lat' => $center_lat,
        'markaz_lng' => $center_lng
    ], 'id = ?', [$mahalle['id']], 'i');

    echo json_encode(['status' => 'success', 'message' => 'Mahalla maʼlumotlari yangilandi']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFY Tahrirlash | GeoTizim</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Sizning mavjud CSS shu yerga qo‘shiladi */
        body {
            font-family: 'Inter', sans-serif;
            background: #000814;
            color: #cfd8dc;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 280px;
            padding: 40px;
        }

        .card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(0, 229, 255, 0.2);
        }

        #map {
            height: 550px;
            border-radius: 16px;
            border: 2px solid #00e5ff;
            margin: 25px 0;
        }

        input,
        select {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 12px;
            border: 1px solid rgba(0, 229, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .save-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #06d6a0, #1a936f);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .save-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(6, 214, 160, 0.4);
        }
    </style>
    <style>
        /* Sidebar asosiy */
        .sidebar {
            background: linear-gradient(180deg, #001b46 0%, #000e24 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 270px;
            height: 100vh;
            padding-top: 20px;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        /* Scroll bar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #00e5ff;
            border-radius: 4px;
        }

        /* Logo va title */
        .sidebar h2 {
            text-align: center;
            color: #00e5ff;
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        /* Asosiy tugmalar */
        .sidebar-main-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 0 15px;
        }

        .sidebar-main-buttons .main-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            font-size: 1rem;
            color: #cfd8dc;
            background: #012969ff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .sidebar-main-buttons .main-btn i {
            color: #00e5ff;
            font-size: 1.1rem;
        }

        .sidebar-main-buttons .main-btn:hover {
            background: rgba(0, 229, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }

        /* Active link */
        .sidebar-main-buttons .main-btn.active {
            background: #00e5ff;
            color: #001b46;
        }

        /* Submenu (agar kerak bo‘lsa) */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-left: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .submenu.open {
            max-height: 500px;
            /* Ochilganda */
            opacity: 1;
        }

        /* Responsive (kichik ekranlar) */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                padding-top: 15px;
            }

            .sidebar h2 {
                font-size: 1rem;
            }

            .sidebar-main-buttons .main-btn {
                justify-content: center;
                padding: 10px 5px;
            }

            .sidebar-main-buttons .main-btn span {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="main-content">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2><i class="fa-solid fa-shield-halved"></i> GeoTizim</h2>

            <ul class="sidebar-main-buttons">
                <li>
                    <button class="main-btn" onclick="location.href='../'">
                        <i class="fa-solid fa-house"></i> Asosiy sahifa
                    </button>
                </li>
                <li>
                    <button class="main-btn" onclick="location.href='./creat_mfy.php'">
                        <i class="fa-solid fa-database"></i> Mahalla yaratish
                    </button>
                </li>
                <li>
                    <button class="main-btn" onclick="location.href='./edit_mfy.php'">
                        <i class="fa-solid fa-database"></i> Mahallani tahrirlash
                    </button>
                </li>

                <?php if ($user['role'] === 'admin'): ?>
                    <li>
                        <button class="main-btn" onclick="location.href='../admin/'">
                            <i class="fa-solid fa-user-shield"></i> Admin
                        </button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <h1 class="page-title">Mahalla tahrirlash</h1>
        <div class="card">
            <div class="form-group">
                <label>Viloyat</label>
                <select id="viloyatSelect">
                    <option value="">Viloyatni tanlang</option>
                    <?php foreach ($viloyatlar as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= $mahalle && $mahalle['viloyat_id'] == $v['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['nomi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tuman/Shahar</label>
                <select id="tumanSelect">
                    <option value="">Tumanni tanlang</option>
                    <?php foreach ($tumanlar as $t): ?>
                        <option value="<?= $t['id'] ?>" data-viloyat="<?= $t['viloyat_id'] ?>" <?= $mahalle && $t['id'] == $mahalle['tuman_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nomi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Mahalla nomi</label>
                <input type="text" id="mahalleName" value="<?= htmlspecialchars($mahalle['nomi'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div id="map"></div>
            <button type="button" id="savePolygon" class="save-btn"><i class="fas fa-save"></i> Saqlash</button>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        // Tuman filtr
        const viloyatSelect = document.getElementById('viloyatSelect');
        const tumanSelect = document.getElementById('tumanSelect');
        viloyatSelect.addEventListener('change', function() {
            const vilId = this.value;
            const allOptions = Array.from(tumanSelect.querySelectorAll('option[data-viloyat]'));
            tumanSelect.innerHTML = '<option value="">Tumanni tanlang</option>';
            allOptions.forEach(opt => {
                if (opt.dataset.viloyat == vilId) tumanSelect.appendChild(opt.cloneNode(true));
            });
        });

        // Xarita
        const map = L.map('map').setView([41.3111, 69.2797], 10);
        L.tileLayer('https://core-renderer-tiles.maps.yandex.net/tiles?l=map&x={x}&y={y}&z={z}&scale=1&lang=tr_TR', {
            attribution: '© Yandex'
        }).addTo(map);

        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);
        const drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems
            },
            draw: {
                polygon: {
                    shapeOptions: {
                        color: '#00e5ff',
                        weight: 5
                    }
                },
                polyline: false,
                rectangle: false,
                circle: false,
                marker: false,
                circlemarker: false
            }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, e => {
            drawnItems.clearLayers();
            drawnItems.addLayer(e.layer);
        });

        // Mavjud polygonni chizish
        <?php if ($mahalle && !empty($mahalle['polygon'])): ?>
            const existingPolygon = <?= $mahalle['polygon'] ?>;
            const coords = existingPolygon.geometry.coordinates[0].map(p => [p[1], p[0]]);
            const layer = L.polygon(coords, {
                color: '#00e5ff'
            });
            drawnItems.addLayer(layer);
            map.fitBounds(layer.getBounds());
        <?php endif; ?>

        // Saqlash
        document.getElementById('savePolygon').addEventListener('click', () => {
            const viloyat_id = viloyatSelect.value;
            const tuman_id = tumanSelect.value;
            const nomi = document.getElementById('mahalleName').value.trim();

            if (!viloyat_id || !tuman_id || !nomi) return alert('Barcha maydonlarni to‘ldiring!');
            if (drawnItems.getLayers().length === 0) return alert('Hududni xaritada belgilang!');

            const polygons = [];
            drawnItems.eachLayer(l => {
                polygons.push(l.getLatLngs()[0].map(p => [p.lng, p.lat]));
            });

            fetch('edit_mfy.php?id=<?= $mahalle['id'] ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    viloyat_id,
                    tuman_id,
                    nomi,
                    polygon: polygons
                })
            }).then(r => r.json()).then(res => {
                if (res.status === 'success') alert(res.message);
                else alert('Xatolik: ' + res.message);
            });
        });
        const links = document.querySelectorAll('.main-btn');
        links.forEach(link => {
            if (link.href === window.location.href) link.classList.add('active');
        });
    </script>
</body>

</html>
<?php
session_start();

// ======= Session va xavfsizlik =======
if (empty($_SESSION['loggedin']) || empty($_SESSION['username'])) {
    header("Location: ./login/");
    exit;
}

// Regenerate session ID once
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// ======= Database ulanishi =======
$mysqli = new mysqli("172.16.5.163", "root", "", "uzb_gis");
if ($mysqli->connect_errno) {
    die("MySQL ulanmadi: " . $mysqli->connect_error);
}

// ======= Foydalanuvchi ma'lumotlarini olish =======
$username = $_SESSION['username'];
$stmt = $mysqli->prepare("SELECT id, username, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    session_destroy();
    header("Location: ./login/");
    exit;
}

$user = $user_result->fetch_assoc();
$_SESSION['user'] = $user;

// ======= Viloyatlar ma'lumotlarini olish =======
$viloyatlar_stmt = $mysqli->prepare("SELECT id, nomi FROM viloyatlar ORDER BY nomi ASC");
$viloyatlar_stmt->execute();
$viloyatlar_result = $viloyatlar_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Huquqiy Statistika GeoTizimi</title>

    <!-- Bootstrap, FontAwesome va Leaflet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
            color: white;
            background: radial-gradient(ellipse at bottom, #01021a 0%, #000000 100%);
            font-family: 'Segoe UI', sans-serif;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, #001b46 0%, #000e24 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 270px;
            height: 100vh;
            padding-top: 25px;
            z-index: 1000;
            overflow-y: auto;
        }

        .submenu.open {
            max-height: 600px;
            /* ochilganda to‘liq */
            opacity: 1;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #00e5ff;
            border-radius: 4px;
        }

        .sidebar h2 {
            text-align: center;
            color: #00e5ff;
            font-size: 1.4rem;
            margin-bottom: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin-bottom: 6px;
        }

        .sidebar ul li button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #cfd8dc;
            background: none;
            border: none;
            padding: 12px 20px;
            font-size: 1rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .sidebar ul li button i {
            color: #00e5ff;
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar ul li button:hover {
            background: rgba(0, 229, 255, 0.1);
            color: #fff;
            border-left: 3px solid #00e5ff;
            transform: translateX(3px);
        }

        /* Submenu */
        .submenu {
            background: rgba(255, 255, 255, 0.05);
            padding-left: 10px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, opacity 0.4s ease;
            opacity: 0;
            border-left: 2px solid #00e5ff33;
        }

        .submenu.open {
            max-height: 600px;
            opacity: 1;
        }

        .submenu button {
            width: 100%;
            background: none;
            border: none;
            color: #ccc;
            text-align: left;
            padding: 8px 20px;
            font-size: 0.92rem;
            transition: 0.3s;
        }

        .submenu button:hover {
            color: #00e5ff;
            background: rgba(0, 229, 255, 0.07);
            border-radius: 5px;
            transform: translateX(3px);
        }

        .submenu button.active {
            background: rgba(0, 229, 255, 0.15);
            color: #00e5ff;
            font-weight: 600;
        }

        /* Main content */
        main {
            margin-left: 280px;
            padding: 40px;
            text-align: center;
        }

        main h1 {
            font-weight: 700;
            color: #00e5ff;
        }

        #map {
            width: 80%;
            height: 500px;
            margin: 40px auto;
            border-radius: 15px;
            border: white 2px solid;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.8);
        }

        .sidebar-main-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 15px 20px;
        }

        .sidebar-main-buttons .main-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            background: #012969ff;
            color: #cfd8dc;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            text-align: left;
        }

        .sidebar-main-buttons .main-btn i {
            color: #00e5ff;
        }

        .sidebar-main-buttons .main-btn:hover {
            background: rgba(0, 229, 255, 0.1);
            color: #fff;
            transform: translateX(3px);
        }

        .sidebar-main-buttons .main-btn a {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 14px 20px;
            color: #cfd8dc;
            text-decoration: none;
            background: none;
            border: none;
            text-align: left;
            font-size: 1rem;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-link:hover {
            background-color: #000000;
            color: aliceblue;
            border-left-color: #00e5ff;
        }

        .active {
            background: rgba(0, 229, 255, 0.15);
            color: #fff;
            border-left-color: #00e5ff;
        }

        .nav-link i {
            width: 24px;
            color: #00e5ff;
        }

        .nav-link span {
            flex: 1;
            margin-left: 12px;
        }

        .arrow {
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .has-submenu.open .arrow {
            transform: rotate(180deg);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.4s ease;
            background: rgba(0, 0, 0, 0.3);
            margin-left: 20px;
            border-left: 2px solid #00e5ff40;
        }

        .has-submenu.open .submenu {
            max-height: 400px;
            padding: 8px 0;
        }

        .submenu a {
            display: block;
            padding: 10px 25px;
            color: #b0bec5;
            text-decoration: none;
            font-size: 0.94rem;
            transition: 0.3s;
        }

        .submenu a:hover {
            color: #00e5ff;
            background: rgba(0, 229, 255, 0.1);
            padding-left: 30px;
        }

        .user-profile {
            background: rgba(0, 229, 255, 0.1);
            border-radius: 10px;
            margin: 0 15px 15px;
            border: 1px solid rgba(0, 229, 255, 0.2);
        }

        .mt-auto {
            margin-top: auto !important;
        }
    </style>

    <style>
        /* Canvas butun ekran foniga */
        #network-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: radial-gradient(ellipse at bottom, #01021a 0%, #000000 100%);
            z-index: -1;
            /* kontent ustida chiqmasligi uchun */
        }

        /* SVG xarita animatsiyasi */
        #uz-map {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40vw;
            stroke: #00e5ff;
            stroke-width: 2;
            fill: none;
            z-index: 0;
            animation: drawMap 5s ease-in-out forwards;
        }

        @keyframes drawMap {
            from {
                stroke-dasharray: 1000;
                stroke-dashoffset: 1000;
            }

            to {
                stroke-dasharray: 1000;
                stroke-dashoffset: 0;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2><i class="fa-solid fa-shield-halved"></i> GeoTizim</h2>

        <ul class="nav-list">
            <!-- Asosiy sahifa -->
            <li>
                <a href="./" class="nav-link active">
                    <i class="fa-solid fa-house"></i>
                    <span>Asosiy sahifa</span>
                </a>
                <a href="./jinoyatlar/" class="nav-link">
                    <i class="fa-solid fa-handcuffs"></i>
                    <span> Jinoyatlar </span>
                </a>
                <a href="./nizokash/" class="nav-link">
                     <i class="fa-solid fa-users-slash"></i>
                     <span>Nizoli oilalar</span>
                </a>
                <a href="./order/" class="nav-link">
                    <i class="fa-solid fa-medal"></i>
                    <span>Order</span>
                </a>
                <a href="./dashboard.php" class="nav-link">
                    <i class="fa-solid fa-database"></i>
                    <span>Ma'lumotlar</span>
                </a>
            </li>

            <!-- Admin panel (faqat admin uchun) -->
            <?php if ($user['role'] === 'admin'): ?>
                <li class="has-submenu">
                    <button class="nav-link dropdown-toggle">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Admin panel</span>
                        <i class="fa-solid fa-chevron-down arrow"></i>
                    </button>
                    
                </li>
            <?php endif; ?>

            <!-- Profil va chiqish -->
            <li class="mt-auto pb-3">
                <!-- <div class="user-profile px-3 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-user-circle fa-2x text-cyan"></i>
                        <div class="text-start">
                            <div class="fw-bold"><?= htmlspecialchars($user['username']) ?></div>
                            <div class="small text-cyan"><?= ucfirst($user['role']) ?></div>
                        </div>
                    </div>
                </div> -->
                <a href="./logout/" class="nav-link text-danger">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Chiqish</span>
                </a>
            </li>
        </ul>
    </div>

    <canvas id="network-bg"></canvas>
    <svg id="uz-map" viewBox="0 0 800 400">
        <path d="M100,250 L180,200 L300,220 L380,160 L460,180 L520,140 L600,160 L680,200 L720,260 L680,300 L560,320 L440,300 L300,320 L200,280 Z" />
    </svg>
    <!-- Main content -->
    <main>
        <h1>O‘zbekiston Respublikasi IIV Huquqiy Statistika GeoTizimi</h1>
        <div id="map"></div>
    </main>

    <!-- JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Leaflet map
        const map = L.map('map').setView([41.0, 64.0], 6);

        var OSMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var googleRoadmap = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            attribution: '© Google Maps Roadmap'
        });

        var googleHybrid = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            attribution: '© Google Maps Hybrid'
        });

        // Layerlarni birlashtirish va nazorat tugmasi
        var baseMaps = {
            "© OpenStreetMap": OSMap,
            "🛣 Google Roadmap": googleRoadmap,
            "🌐 Google Hybrid": googleHybrid
        };

        L.control.layers(baseMaps, null, {
            position: 'topright'
        }).addTo(map);

        document.querySelectorAll('.has-submenu .dropdown-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.classList.toggle('open');
            });
        });

        // Aktiv sahifani belgilash (masalan, URL ga qarab)
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    </script>
    <script>
        // === NETWORK ANIMATSIYA ===
        const canvas = document.getElementById('network-bg');
        const ctx = canvas.getContext('2d');
        let w, h, dots;

        function resize() {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
            dots = Array.from({
                length: 80
            }, () => ({
                x: Math.random() * w,
                y: Math.random() * h,
                vx: (Math.random() - 0.5) * 0.7,
                vy: (Math.random() - 0.5) * 0.7
            }));
        }

        window.addEventListener('resize', resize);
        resize();

        function animate() {
            ctx.clearRect(0, 0, w, h);
            // Yulduzlarni chizish
            ctx.fillStyle = '#00e5ff';
            for (let d of dots) {
                d.x += d.vx;
                d.y += d.vy;

                if (d.x < 0 || d.x > w) d.vx *= -1;
                if (d.y < 0 || d.y > h) d.vy *= -1;

                ctx.beginPath();
                ctx.arc(d.x, d.y, 1.6, 0, Math.PI * 2);
                ctx.fill();
            }

            // Chiziqlar (yulduzlar orasidagi network)
            for (let i = 0; i < dots.length; i++) {
                for (let j = i + 1; j < dots.length; j++) {
                    const dx = dots[i].x - dots[j].x;
                    const dy = dots[i].y - dots[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 120) {
                        ctx.beginPath();
                        ctx.strokeStyle = `rgba(0, 229, 255, ${1 - dist / 120})`;
                        ctx.lineWidth = 0.4;
                        ctx.moveTo(dots[i].x, dots[i].y);
                        ctx.lineTo(dots[j].x, dots[j].y);
                        ctx.stroke();
                    }
                }
            }

            requestAnimationFrame(animate);
        }

        animate();
    </script>
</body>

</html>
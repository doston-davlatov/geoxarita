CREATE DATABASE IF NOT EXISTS uzb_gis CHARACTER SET utf8mb4;
USE uzb_gis;
---------------
---------------
---------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO users (name, username, password)
VALUES 
('Doston Davlatov', 'admin', '$2y$10$M7bOo5D228YqtxP1pUicGOYWtVryXknZUhTX1S2Mwjg3DtJzjEEJG');
ALTER TABLE users
ADD COLUMN role ENUM('user','operator','admin','super_admin') NOT NULL DEFAULT 'user',
ADD COLUMN mahalla_id INT NULL;  -- operator faqat o‘z MFY sini biladi


CREATE TABLE viloyatlar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomi VARCHAR(100) NOT NULL
);

INSERT INTO viloyatlar (nomi) VALUES
('Qoraqalpog‘iston Respublikasi'),
('Andijon viloyati'),
('Buxoro viloyati'),
('Farg‘ona viloyati'),
('Jizzax viloyati'),
('Namangan viloyati'),
('Navoiy viloyati'),
('Qashqadaryo viloyati'),
('Samarqand viloyati'),
('Sirdaryo viloyati'),
('Surxondaryo viloyati'),
('Toshkent viloyati'),
('Toshkent shahri'),
('Xorazm viloyati');

CREATE TABLE tumanlar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  viloyat_id INT NOT NULL,
  nomi VARCHAR(100) NOT NULL,
  FOREIGN KEY (viloyat_id) REFERENCES viloyatlar(id) ON DELETE CASCADE
);

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(1, 'Amudaryo tumani'),
(1, 'Beruniy tumani'),
(1, 'Chimboy tumani'),
(1, 'Ellikqal’a tumani'),
(1, 'Kegeyli tumani'),
(1, 'Mo‘ynoq tumani'),
(1, 'Nukus tumani'),
(1, 'Nukus shahri'),
(1, 'Qanliko‘l tumani'),
(1, 'Qo‘ng‘irot tumani'),
(1, 'Qorauzak tumani'),
(1, 'Shumanay tumani'),
(1, 'Taxtako‘pir tumani'),
(1, 'To‘rtko‘l tumani'),
(1, 'Xo‘jayli tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(2, 'Andijon shahri'),
(2, 'Andijon tumani'),
(2, 'Asaka tumani'),
(2, 'Baliqchi tumani'),
(2, 'Bo‘z tumani'),
(2, 'Buloqboshi tumani'),
(2, 'Izboskan tumani'),
(2, 'Jalaquduq tumani'),
(2, 'Marhamat tumani'),
(2, 'Oltinko‘l tumani'),
(2, 'Paxtaobod tumani'),
(2, 'Qo‘rg‘ontepa tumani'),
(2, 'Shahrixon tumani'),
(2, 'Ulug‘nor tumani'),
(2, 'Xo‘jaobod tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(3, 'Buxoro shahri'),
(3, 'Buxoro tumani'),
(3, 'G‘ijduvon tumani'),
(3, 'Jondor tumani'),
(3, 'Kogon tumani'),
(3, 'Kogon shahri'),
(3, 'Olot tumani'),
(3, 'Peshku tumani'),
(3, 'Qorako‘l tumani'),
(3, 'Qorovulbozor tumani'),
(3, 'Romitan tumani'),
(3, 'Shofirkon tumani'),
(3, 'Vobkent tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(4, 'Farg‘ona shahri'),
(4, 'Bag‘dod tumani'),
(4, 'Beshariq tumani'),
(4, 'Buvayda tumani'),
(4, 'Dang‘ara tumani'),
(4, 'Farg‘ona tumani'),
(4, 'Furqat tumani'),
(4, 'Qo‘qon shahri'),
(4, 'Quva tumani'),
(4, 'Quvasoy shahri'),
(4, 'Oltiariq tumani'),
(4, 'Rishton tumani'),
(4, 'So‘x tumani'),
(4, 'Toshloq tumani'),
(4, 'Uchko‘prik tumani'),
(4, 'Yozyovon tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(5, 'Arnasoy tumani'),
(5, 'Baxmal tumani'),
(5, 'Do‘stlik tumani'),
(5, 'Forish tumani'),
(5, 'G‘allaorol tumani'),
(5, 'Jizzax shahri'),
(5, 'Mirzacho‘l tumani'),
(5, 'Paxtakor tumani'),
(5, 'Yangiobod tumani'),
(5, 'Zomin tumani'),
(5, 'Zafarobod tumani'),
(5, 'Zarbdor tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(6, 'Chortoq tumani'),
(6, 'Chust tumani'),
(6, 'Kosonsoy tumani'),
(6, 'Mingbuloq tumani'),
(6, 'Namangan shahri'),
(6, 'Namangan tumani'),
(6, 'Norin tumani'),
(6, 'Pop tumani'),
(6, 'To‘raqo‘rg‘on tumani'),
(6, 'Uchqo‘rg‘on tumani'),
(6, 'Yangiqo‘rg‘on tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(7, 'Karmana tumani'),
(7, 'Konimex tumani'),
(7, 'Navbahor tumani'),
(7, 'Navoiy shahri'),
(7, 'Nurota tumani'),
(7, 'Qiziltepa tumani'),
(7, 'Tomdi tumani'),
(7, 'Uchquduq tumani'),
(7, 'Xatirchi tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(8, 'Chiroqchi tumani'),
(8, 'Dehqonobod tumani'),
(8, 'G‘uzor tumani'),
(8, 'Kasbi tumani'),
(8, 'Kitob tumani'),
(8, 'Koson tumani'),
(8, 'Mirishkor tumani'),
(8, 'Muborak tumani'),
(8, 'Nishon tumani'),
(8, 'Qamashi tumani'),
(8, 'Qarshi shahri'),
(8, 'Qarshi tumani'),
(8, 'Shahrisabz tumani'),
(8, 'Yakkabog‘ tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(9, 'Bulung‘ur tumani'),
(9, 'Ishtixon tumani'),
(9, 'Jomboy tumani'),
(9, 'Kattaqo‘rg‘on tumani'),
(9, 'Kattaqo‘rg‘on shahri'),
(9, 'Narpay tumani'),
(9, 'Nurobod tumani'),
(9, 'Oqdaryo tumani'),
(9, 'Pastdarg‘om tumani'),
(9, 'Payariq tumani'),
(9, 'Samarqand shahri'),
(9, 'Samarqand tumani'),
(9, 'Toyloq tumani'),
(9, 'Urgut tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(10, 'Boyovut tumani'),
(10, 'Guliston tumani'),
(10, 'Guliston shahri'),
(10, 'Mirzaobod tumani'),
(10, 'Oqoltin tumani'),
(10, 'Sardoba tumani'),
(10, 'Sayxunobod tumani'),
(10, 'Shirin shahri'),
(10, 'Sirdaryo tumani'),
(10, 'Xovos tumani'),
(10, 'Yangiyer shahri');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(11, 'Angor tumani'),
(11, 'Boysun tumani'),
(11, 'Denov tumani'),
(11, 'Jarqo‘rg‘on tumani'),
(11, 'Qiziriq tumani'),
(11, 'Qumqo‘rg‘on tumani'),
(11, 'Muzrabot tumani'),
(11, 'Oltinsoy tumani'),
(11, 'Sariosiyo tumani'),
(11, 'Sherobod tumani'),
(11, 'Sho‘rchi tumani'),
(11, 'Termiz shahri'),
(11, 'Termiz tumani'),
(11, 'Uzun tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(12, 'Angren shahri'),
(12, 'Bekobod shahri'),
(12, 'Bekobod tumani'),
(12, 'Bo‘ka tumani'),
(12, 'Chinoz tumani'),
(12, 'Ohangaron tumani'),
(12, 'Parkent tumani'),
(12, 'Piskent tumani'),
(12, 'Qibray tumani'),
(12, 'Quyi Chirchiq tumani'),
(12, 'Oqqo‘rg‘on tumani'),
(12, 'O‘rta Chirchiq tumani'),
(12, 'Yangiyo‘l tumani'),
(12, 'Yuqori Chirchiq tumani'),
(12, 'Zangiota tumani'),
(12, 'Nurafshon shahri');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(13, 'Bektemir tumani'),
(13, 'Chilonzor tumani'),
(13, 'Mirobod tumani'),
(13, 'Mirzo Ulug‘bek tumani'),
(13, 'Olmazor tumani'),
(13, 'Sergeli tumani'),
(13, 'Shayxontohur tumani'),
(13, 'Uchtepa tumani'),
(13, 'Yakkasaroy tumani'),
(13, 'Yashnobod tumani'),
(13, 'Yunusobod tumani');

INSERT INTO tumanlar (viloyat_id, nomi) VALUES
(14, 'Bog‘ot tumani'),
(14, 'Gurlan tumani'),
(14, 'Hazorasp tumani'),
(14, 'Khiva shahri'),
(14, 'Qo‘shko‘pir tumani'),
(14, 'Shovot tumani'),
(14, 'Urganch shahri'),
(14, 'Urganch tumani'),
(14, 'Xiva tumani'),
(14, 'Yangiariq tumani'),
(14, 'Yangibozor tumani');


CREATE TABLE crimes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jk_modda VARCHAR(100),
  qismi VARCHAR(50),
  bandi VARCHAR(50),
  ogrilik_turi VARCHAR(150),
  sodir_vaqti DATETIME,
  viloyat_id INT,
  tuman_id INT,
  mahalla_id INT,
  jinoyat_matni TEXT,
  lat DOUBLE,
  lng DOUBLE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (viloyat_id) REFERENCES viloyatlar(id) ON DELETE SET NULL,
  FOREIGN KEY (tuman_id) REFERENCES tumanlar(id) ON DELETE SET NULL,
  FOREIGN KEY (mahalla_id) REFERENCES mahallelar(id) ON DELETE SET NULL
);


CREATE TABLE mahallelar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  viloyat_id INT NOT NULL,
  tuman_id INT NOT NULL,
  nomi VARCHAR(150) NOT NULL,        -- Mahalla nomi (MFY)
  polygon JSON DEFAULT NULL,         -- Xaritadagi hudud (Leaflet koordinatalari)
  operator_id INT DEFAULT NULL,      -- Shu mahallaga biriktirilgan operator
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (viloyat_id) REFERENCES viloyatlar(id) ON DELETE CASCADE,
  FOREIGN KEY (tuman_id) REFERENCES tumanlar(id) ON DELETE CASCADE,
  FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE SET NULL
);



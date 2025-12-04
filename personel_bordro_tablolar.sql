-- Personel Bordro Yönetim Sistemi - Veritabanı Tabloları
-- Oluşturulma Tarihi: 2025-12-04

-- Personel maaş ödemeleri tablosu
CREATE TABLE IF NOT EXISTS `personel_maas_odemeleri` (
  `odeme_id` INT(11) NOT NULL AUTO_INCREMENT,
  `personel_id` INT(11) NOT NULL,
  `personel_adi` VARCHAR(255) NOT NULL,
  `donem_yil` INT(4) NOT NULL,
  `donem_ay` INT(2) NOT NULL,
  `aylik_brut_ucret` DECIMAL(10,2) NOT NULL,
  `avans_toplami` DECIMAL(10,2) DEFAULT 0.00,
  `net_odenen` DECIMAL(10,2) NOT NULL,
  `odeme_tarihi` DATE NOT NULL,
  `odeme_tipi` VARCHAR(50) DEFAULT 'Havale',
  `aciklama` TEXT,
  `kaydeden_personel_id` INT(11),
  `kaydeden_personel_adi` VARCHAR(255),
  `kayit_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `gider_kayit_id` INT(11),
  PRIMARY KEY (`odeme_id`),
  KEY `idx_personel_id` (`personel_id`),
  KEY `idx_donem` (`donem_yil`, `donem_ay`),
  FOREIGN KEY (`personel_id`) REFERENCES `personeller`(`personel_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personel avanslar tablosu
CREATE TABLE IF NOT EXISTS `personel_avanslar` (
  `avans_id` INT(11) NOT NULL AUTO_INCREMENT,
  `personel_id` INT(11) NOT NULL,
  `personel_adi` VARCHAR(255) NOT NULL,
  `avans_tutari` DECIMAL(10,2) NOT NULL,
  `avans_tarihi` DATE NOT NULL,
  `donem_yil` INT(4) NOT NULL,
  `donem_ay` INT(2) NOT NULL,
  `odeme_tipi` VARCHAR(50) DEFAULT 'Nakit',
  `aciklama` TEXT,
  `kaydeden_personel_id` INT(11),
  `kaydeden_personel_adi` VARCHAR(255),
  `kayit_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `maas_odemesinde_kullanildi` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`avans_id`),
  KEY `idx_personel_id` (`personel_id`),
  KEY `idx_donem` (`donem_yil`, `donem_ay`),
  FOREIGN KEY (`personel_id`) REFERENCES `personeller`(`personel_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

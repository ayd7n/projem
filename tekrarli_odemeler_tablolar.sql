-- Tekrarlı Ödeme Hatırlatma Sistemi - Veritabanı Tabloları
-- Oluşturulma Tarihi: 2025-12-04

-- Tekrarlı ödemeler tanımlama tablosu
CREATE TABLE IF NOT EXISTS `tekrarli_odemeler` (
  `odeme_id` INT(11) NOT NULL AUTO_INCREMENT,
  `odeme_adi` VARCHAR(255) NOT NULL,
  `odeme_tipi` VARCHAR(100) NOT NULL COMMENT 'Kira, Elektrik, Su, Doğalgaz, İnternet, Vergi, Sigorta, vb.',
  `tutar` DECIMAL(10,2) NOT NULL,
  `odeme_gunu` INT(2) NOT NULL COMMENT 'Ayın kaçında ödeme yapılacak (1-31)',
  `alici_firma` VARCHAR(255),
  `aciklama` TEXT,
  `aktif` TINYINT(1) DEFAULT 1,
  `kaydeden_personel_id` INT(11),
  `kaydeden_personel_adi` VARCHAR(255),
  `kayit_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`odeme_id`),
  KEY `idx_aktif` (`aktif`),
  KEY `idx_odeme_gunu` (`odeme_gunu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tekrarlı ödeme geçmişi tablosu
CREATE TABLE IF NOT EXISTS `tekrarli_odeme_gecmisi` (
  `gecmis_id` INT(11) NOT NULL AUTO_INCREMENT,
  `odeme_id` INT(11) NOT NULL,
  `odeme_adi` VARCHAR(255) NOT NULL,
  `odeme_tipi` VARCHAR(100) NOT NULL,
  `tutar` DECIMAL(10,2) NOT NULL,
  `donem_yil` INT(4) NOT NULL,
  `donem_ay` INT(2) NOT NULL,
  `odeme_tarihi` DATE NOT NULL,
  `odeme_yontemi` VARCHAR(50) DEFAULT 'Havale',
  `aciklama` TEXT,
  `kaydeden_personel_id` INT(11),
  `kaydeden_personel_adi` VARCHAR(255),
  `kayit_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `gider_kayit_id` INT(11),
  PRIMARY KEY (`gecmis_id`),
  KEY `idx_odeme_id` (`odeme_id`),
  KEY `idx_donem` (`donem_yil`, `donem_ay`),
  FOREIGN KEY (`odeme_id`) REFERENCES `tekrarli_odemeler`(`odeme_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

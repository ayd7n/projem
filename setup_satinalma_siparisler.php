<?php
/**
 * Satınalma Siparişler Tabloları Kurulum Scripti
 * Eski malzeme_siparisler tablosunu siler ve yeni tabloları oluşturur
 */

include 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    die('Oturum açmanız gerekiyor.');
}

echo "<h1>Satınalma Siparişler Tabloları Kurulumu</h1>";
echo "<pre>";

// 1. Drop old table
echo "1. Eski malzeme_siparisler tablosu siliniyor...\n";
$connection->query("DROP TABLE IF EXISTS malzeme_siparisler");
echo "   [OK] Tablo silindi.\n\n";

// 2. Create satinalma_siparisler table
echo "2. satinalma_siparisler tablosu oluşturuluyor...\n";
$sql1 = "CREATE TABLE IF NOT EXISTS `satinalma_siparisler` (
  `siparis_id` INT(11) NOT NULL AUTO_INCREMENT,
  `siparis_no` VARCHAR(20) NOT NULL UNIQUE,
  `tedarikci_id` INT(11) NOT NULL,
  `tedarikci_adi` VARCHAR(255) NOT NULL,
  `siparis_tarihi` DATE NOT NULL,
  `istenen_teslim_tarihi` DATE DEFAULT NULL,
  `durum` ENUM('taslak','onaylandi','gonderildi','kismen_teslim','tamamlandi','iptal') DEFAULT 'taslak',
  `toplam_tutar` DECIMAL(15,2) DEFAULT 0.00,
  `para_birimi` VARCHAR(10) DEFAULT 'TRY',
  `aciklama` TEXT DEFAULT NULL,
  `olusturan_id` INT(11) DEFAULT NULL,
  `olusturan_adi` VARCHAR(255) DEFAULT NULL,
  `olusturma_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `guncelleme_tarihi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`siparis_id`),
  KEY `idx_tedarikci` (`tedarikci_id`),
  KEY `idx_durum` (`durum`),
  KEY `idx_tarih` (`siparis_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($connection->query($sql1)) {
    echo "   [OK] satinalma_siparisler tablosu oluşturuldu.\n\n";
} else {
    echo "   [HATA] " . $connection->error . "\n\n";
}

// 3. Create satinalma_siparis_kalemleri table
echo "3. satinalma_siparis_kalemleri tablosu oluşturuluyor...\n";
$sql2 = "CREATE TABLE IF NOT EXISTS `satinalma_siparis_kalemleri` (
  `kalem_id` INT(11) NOT NULL AUTO_INCREMENT,
  `siparis_id` INT(11) NOT NULL,
  `malzeme_kodu` INT(11) NOT NULL,
  `malzeme_adi` VARCHAR(255) NOT NULL,
  `miktar` DECIMAL(10,2) NOT NULL,
  `birim` VARCHAR(50) DEFAULT 'adet',
  `birim_fiyat` DECIMAL(10,2) DEFAULT 0.00,
  `para_birimi` VARCHAR(10) DEFAULT 'TRY',
  `toplam_fiyat` DECIMAL(15,2) DEFAULT 0.00,
  `teslim_edilen_miktar` DECIMAL(10,2) DEFAULT 0.00,
  `aciklama` TEXT DEFAULT NULL,
  PRIMARY KEY (`kalem_id`),
  KEY `idx_siparis` (`siparis_id`),
  KEY `idx_malzeme` (`malzeme_kodu`),
  CONSTRAINT `fk_satinalma_siparis` FOREIGN KEY (`siparis_id`) REFERENCES `satinalma_siparisler` (`siparis_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($connection->query($sql2)) {
    echo "   [OK] satinalma_siparis_kalemleri tablosu oluşturuldu.\n\n";
} else {
    echo "   [HATA] " . $connection->error . "\n\n";
}

echo "</pre>";
echo "<h2 style='color:green'>Kurulum Tamamlandı!</h2>";
echo "<p><a href='satinalma_siparisler.php'>Satınalma Siparişleri sayfasına git</a></p>";
?>
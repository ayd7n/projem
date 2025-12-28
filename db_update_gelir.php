<?php
include 'config.php';

// 1. Create gelir_yonetimi table
$sql_create_gelir = "CREATE TABLE IF NOT EXISTS `gelir_yonetimi` (
  `gelir_id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime NOT NULL DEFAULT current_timestamp(),
  `tutar` decimal(10,2) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_ismi` varchar(100) DEFAULT NULL,
  `siparis_id` int(11) DEFAULT NULL,
  `odeme_tipi` varchar(50) DEFAULT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `musteri_adi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`gelir_id`),
  KEY `siparis_id` (`siparis_id`),
  KEY `musteri_id` (`musteri_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($connection->query($sql_create_gelir)) {
    echo "Table 'gelir_yonetimi' created successfully.\n";
} else {
    echo "Error creating table 'gelir_yonetimi': " . $connection->error . "\n";
}

// 2. Modifying siparisler table to add payment tracking columns
// Check if columns exist first to avoid errors
$check_columns = $connection->query("SHOW COLUMNS FROM `siparisler` LIKE 'odeme_durumu'");
if ($check_columns->num_rows == 0) {
    $sql_alter_siparis = "ALTER TABLE `siparisler` 
    ADD COLUMN `odeme_durumu` ENUM('bekliyor', 'odendi', 'kismi_odendi') DEFAULT 'bekliyor',
    ADD COLUMN `odeme_yontemi` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN `odenen_tutar` DECIMAL(10,2) DEFAULT 0.00";

    if ($connection->query($sql_alter_siparis)) {
        echo "Table 'siparisler' altered successfully.\n";
    } else {
        echo "Error altering table 'siparisler': " . $connection->error . "\n";
    }
} else {
    echo "Columns already exist in 'siparisler'.\n";
}

echo "Database updates completed.";
?>
<?php
// Durum enum'unu güncellemek için yardımcı script
include 'config.php';

$sql = "ALTER TABLE malzeme_siparisler 
        MODIFY COLUMN durum ENUM('olusturuldu', 'siparis_verildi', 'iptal_edildi', 'teslim_edildi') DEFAULT 'olusturuldu'";

if ($connection->query($sql) === TRUE) {
    echo "Durum enum'u başarıyla güncellendi\n";
} else {
    echo "Hata: " . $connection->error . "\n";
}

$connection->close();
?>
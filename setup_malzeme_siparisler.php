<?php
// Malzeme siparişler tablosunu oluşturmak için yardımcı script
include 'config.php';

$sql = "CREATE TABLE IF NOT EXISTS malzeme_siparisler (
    siparis_id INT AUTO_INCREMENT PRIMARY KEY,
    malzeme_kodu INT NOT NULL,
    malzeme_ismi VARCHAR(255),
    tedarikci_id INT NOT NULL,
    tedarikci_ismi VARCHAR(255),
    miktar DECIMAL(10,2) NOT NULL,
    siparis_tarihi DATE NOT NULL,
    teslim_tarihi DATE NULL,
    durum ENUM('siparis_verildi', 'iptal_edildi', 'teslim_edildi') DEFAULT 'siparis_verildi',
    aciklama TEXT,
    kaydeden_personel_id INT,
    kaydeden_personel_adi VARCHAR(255),
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_malzeme_kodu (malzeme_kodu),
    INDEX idx_tedarikci_id (tedarikci_id),
    INDEX idx_durum (durum),
    INDEX idx_siparis_tarihi (siparis_tarihi),
    FOREIGN KEY (malzeme_kodu) REFERENCES malzemeler(malzeme_kodu) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($connection->query($sql) === TRUE) {
    echo "Tablo başarıyla oluşturuldu: malzeme_siparisler\n";
} else {
    echo "Hata: " . $connection->error . "\n";
}

$connection->close();
?>

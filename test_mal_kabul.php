<?php
include 'config.php';

// Test için gerekli bazı verileri göster
echo "Mal Kabul Test Sayfası\n";
echo "=====================\n\n";

// Mevcut malzeme listesi
echo "Mevcut Malzemeler:\n";
$malzeme_query = "SELECT malzeme_kodu, malzeme_ismi FROM malzemeler LIMIT 10";
$malzeme_result = $connection->query($malzeme_query);
while($row = $malzeme_result->fetch_assoc()) {
    echo "- {$row['malzeme_kodu']}: {$row['malzeme_ismi']}\n";
}

echo "\nTedarikçiler:\n";
$tedarikci_query = "SELECT tedarikci_id, tedarikci_adi FROM tedarikciler LIMIT 10";
$tedarikci_result = $connection->query($tedarikci_query);
while($row = $tedarikci_result->fetch_assoc()) {
    echo "- {$row['tedarikci_id']}: {$row['tedarikci_adi']}\n";
}

echo "\nÇerçeve Sözleşmeler (ilk 10):\n";
$sozlesme_query = "SELECT sozlesme_id, malzeme_kodu, tedarikci_adi, birim_fiyat, para_birimi, baslangic_tarihi, bitis_tarihi FROM cerceve_sozlesmeler LIMIT 10";
$sozlesme_result = $connection->query($sozlesme_query);
while($row = $sozlesme_result->fetch_assoc()) {
    echo "- {$row['sozlesme_id']}: Malzeme={$row['malzeme_kodu']}, Tedarikçi={$row['tedarikci_adi']}, Fiyat={$row['birim_fiyat']} {$row['para_birimi']}, Tarih={$row['baslangic_tarihi']} - {$row['bitis_tarihi']}\n";
}

echo "\nStok Hareketleri Sözleşmeler Tablosu Yapısı:\n";
$desc_query = "DESCRIBE stok_hareketleri_sozlesmeler";
$desc_result = $connection->query($desc_query);
while($row = $desc_result->fetch_assoc()) {
    echo "- {$row['Field']}: {$row['Type']} (Null: {$row['Null']}, Key: {$row['Key']})\n";
}

echo "\nTest tamamlandı.\n";
?>
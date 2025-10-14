<?php
include 'config.php';
echo "Veritabanı bağlantısı başarılı!\n";
echo "Kullanılabilir veritabanları:\n";
$result = $connection->query('SHOW DATABASES;');
while($row = $result->fetch_assoc()) {
    echo $row['Database'] . "\n";
}

echo "\nÇerçeve sözleşmeler tablosu kontrolü:\n";
$result = $connection->query('DESCRIBE cerceve_sozlesmeler;');
if ($result) {
    echo "Tablo mevcut. Yapısı:\n";
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Tablo bulunamadı veya hata oluştu.\n";
}

echo "\nMevcut çerçeve sözleşmelerde para birimi değerleri:\n";
$result = $connection->query('SELECT sozlesme_id, para_birimi, birim_fiyat FROM cerceve_sozlesmeler LIMIT 10;');
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row['sozlesme_id'] . " - Para Birimi: '" . $row['para_birimi'] . "' - Birim Fiyat: " . $row['birim_fiyat'] . "\n";
        }
    } else {
        echo "Henüz kayıtlı sözleşme bulunmuyor.\n";
    }
} else {
    echo "Sorgu çalıştırılırken hata oluştu.\n";
}
?>
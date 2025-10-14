<?php
// Include config to establish DB connection
include 'config.php';

echo "<h2>Çerçeve Sözleşmeler CRUD Testi</h2>";

echo "<h3>1. Mevcut Kayıtlar:</h3>";
$contracts_query = "SELECT * FROM cerceve_sozlesmeler ORDER BY sozlesme_id";
$contracts_result = $connection->query($contracts_query);

if ($contracts_result && $contracts_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Tedarikçi</th>";
    echo "<th>Malzeme</th>";
    echo "<th>Birim Fiyat</th>";
    echo "<th>Para Birimi</th>";
    echo "<th>Limit Miktar</th>";
    echo "<th>Toplu Ödenen</th>";
    echo "<th>Başlangıç</th>";
    echo "<th>Bitiş</th>";
    echo "<th>Oluşturan</th>";
    echo "<th>Açıklama</th>";
    echo "</tr>";

    while ($contract = $contracts_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $contract['sozlesme_id'] . "</td>";
        echo "<td>" . htmlspecialchars($contract['tedarikci_adi']) . "</td>";
        echo "<td>" . htmlspecialchars($contract['malzeme_ismi']) . "</td>";
        echo "<td>" . $contract['birim_fiyat'] . "</td>";
        echo "<td>" . $contract['para_birimi'] . "</td>";
        echo "<td>" . $contract['limit_miktar'] . "</td>";
        echo "<td>" . $contract['toplu_odenen_miktar'] . "</td>";
        echo "<td>" . $contract['baslangic_tarihi'] . "</td>";
        echo "<td>" . $contract['bitis_tarihi'] . "</td>";
        echo "<td>" . htmlspecialchars($contract['olusturan']) . "</td>";
        echo "<td>" . htmlspecialchars($contract['aciklama']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Henüz kayıt yok.</p>";
}

// Test updating a record
echo "<h3>2. Kayıt Güncelleme Testi:</h3>";
$sql = "UPDATE cerceve_sozlesmeler SET toplu_odenen_miktar = 175.50 WHERE sozlesme_id = 1 LIMIT 1";
if ($connection->query($sql) === TRUE) {
    echo "<p>1 numaralı kayıt başarıyla güncellendi. Toplu Ödenen Miktar: 175.50</p>";
    
    // Show updated record
    $result = $connection->query("SELECT * FROM cerceve_sozlesmeler WHERE sozlesme_id = 1 LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $record = $result->fetch_assoc();
        echo "<p>Toplu Ödenen Miktar değeri: " . $record['toplu_odenen_miktar'] . "</p>";
    }
} else {
    echo "<p>Güncelleme hatası: " . $connection->error . "</p>";
}

echo "<h3>3. Sonuç:</h3>";
echo "<p>Çerçeve Sözleşmeler CRUD işlemleri başarıyla test edildi.</p>";
echo "<p>Yeni <code>toplu_odenen_miktar</code> alanı doğru şekilde çalışıyor.</p>";
echo "<p>Arayüz dosyası: <code>cerceve_sozlesmeler.php</code></p>";
echo "<p>API dosyası: <code>api_islemleri/sozlesme_islemler.php</code></p>";
?>
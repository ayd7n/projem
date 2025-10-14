<?php
include 'config.php';

echo "<h1>Siparişler ve Sipariş Kalemleri Tablosu Bilgileri</h1>";

// Query to get orders with customer information
echo "<h2>En Yeni 10 Sipariş</h2>";
$orders_query = "SELECT siparis_id, musteri_adi, tarih, durum, toplam_adet, aciklama FROM siparisler ORDER BY tarih DESC LIMIT 10";
$orders_result = $connection->query($orders_query);

if ($orders_result && $orders_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Sipariş ID</th>";
    echo "<th>Müşteri Adı</th>";
    echo "<th>Tarih</th>";
    echo "<th>Durum</th>";
    echo "<th>Toplam Adet</th>";
    echo "<th>Açıklama</th>";
    echo "</tr>";
    
    while ($row = $orders_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['siparis_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['musteri_adi']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tarih']) . "</td>";
        echo "<td>" . htmlspecialchars($row['durum']) . "</td>";
        echo "<td>" . htmlspecialchars($row['toplam_adet']) . "</td>";
        echo "<td>" . htmlspecialchars($row['aciklama']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Hiç sipariş bulunamadı.</p>";
}

// Query to get order items
echo "<h2>En Yeni 10 Sipariş Kalemi</h2>";
$items_query = "SELECT * FROM siparis_kalemleri LIMIT 10";
$items_result = $connection->query($items_query);

if ($items_result && $items_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    $fields = $items_result->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";
    
    while ($row = $items_result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Hiç sipariş kalemi bulunamadı.</p>";
}

// Query to get recent orders with their items
echo "<h2>En Yeni 5 Sipariş ve Kalemleri</h2>";
$combined_query = "SELECT s.siparis_id, s.musteri_adi, s.tarih, s.durum, sk.urun_ismi, sk.adet, sk.toplam_tutar 
                   FROM siparisler s 
                   JOIN siparis_kalemleri sk ON s.siparis_id = sk.siparis_id 
                   ORDER BY s.tarih DESC 
                   LIMIT 5";
$combined_result = $connection->query($combined_query);

if ($combined_result && $combined_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Sipariş ID</th>";
    echo "<th>Müşteri Adı</th>";
    echo "<th>Tarih</th>";
    echo "<th>Durum</th>";
    echo "<th>Ürün İsmi</th>";
    echo "<th>Adet</th>";
    echo "<th>Toplam Tutar</th>";
    echo "</tr>";
    
    while ($row = $combined_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['siparis_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['musteri_adi']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tarih']) . "</td>";
        echo "<td>" . htmlspecialchars($row['durum']) . "</td>";
        echo "<td>" . htmlspecialchars($row['urun_ismi']) . "</td>";
        echo "<td>" . htmlspecialchars($row['adet']) . "</td>";
        echo "<td>" . htmlspecialchars($row['toplam_tutar']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Hiç birleşik sipariş verisi bulunamadı.</p>";
}

$connection->close();
?>
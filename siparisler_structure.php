<?php
include 'config.php';

echo "<h1>Siparişler ve Sipariş Kalemleri Tablo Yapısı</h1>";

// Query to get the structure of the siparisler table
echo "<h2>siparisler Tablosu Yapısı</h2>";
$structure_query = "DESCRIBE siparisler";
$structure_result = $connection->query($structure_query);

if ($structure_result && $structure_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Sütun Adı</th>";
    echo "<th>Veri Türü</th>";
    echo "<th>Boş Geçilebilir</th>";
    echo "<th>Varsayılan Değer</th>";
    echo "<th>Ekstra</th>";
    echo "</tr>";
    
    while ($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>siparisler tablosu yapısı alınamadı.</p>";
}

// Query to get the structure of the siparis_kalemleri table
echo "<h2>siparis_kalemleri Tablosu Yapısı</h2>";
$items_structure_query = "DESCRIBE siparis_kalemleri";
$items_structure_result = $connection->query($items_structure_query);

if ($items_structure_result && $items_structure_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Sütun Adı</th>";
    echo "<th>Veri Türü</th>";
    echo "<th>Boş Geçilebilir</th>";
    echo "<th>Varsayılan Değer</th>";
    echo "<th>Ekstra</th>";
    echo "</tr>";
    
    while ($row = $items_structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>siparis_kalemleri tablosu yapısı alınamadı.</p>";
}

$connection->close();
?>
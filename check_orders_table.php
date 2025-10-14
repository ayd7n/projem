<?php
include 'config.php';

// Query to get the structure of the siparisler table
$query = "DESCRIBE siparisler";
$result = $connection->query($query);

if ($result) {
    echo "<h2>siparisler (Müşteri Siparişleri) Tablosu Yapısı</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Sütun Adı</th>";
    echo "<th>Veri Türü</th>";
    echo "<th>Boş Geçilebilir</th>";
    echo "<th>Varsayılan Değer</th>";
    echo "<th>Ekstra</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
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
    echo "Tablo yapısı alınamadı: " . $connection->error;
}

// Also get information about related order items table
echo "<br><h2>siparis_kalemleri (Sipariş Kalemleri) Tablosu Yapısı</h2>";
$query2 = "DESCRIBE siparis_kalemleri";
$result2 = $connection->query($query2);

if ($result2) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Sütun Adı</th>";
    echo "<th>Veri Türü</th>";
    echo "<th>Boş Geçilebilir</th>";
    echo "<th>Varsayılan Değer</th>";
    echo "<th>Ekstra</th>";
    echo "</tr>";
    
    while ($row = $result2->fetch_assoc()) {
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
    echo "siparis_kalemleri tablosu yapısı alınamadı: " . $connection->error;
}

$connection->close();
?>
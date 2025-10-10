<?php
header('Content-Type: text/html; charset=utf-8');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parfum_erp";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);

    // Update the existing records to fix Turkish characters
    $updates = [
        [24, 'Ahmet Yılmaz', 'Satış Müdürü', 'Satış'],
        [25, 'Ayşe Kaya', 'Pazarlama Uzmanı', 'Pazarlama'],
        [26, 'Mehmet Demir', 'Depo Sorumlusu', 'Lojistik'],
        [27, 'Fatma Öztürk', 'Muhasebe Uzmanı', 'Muhasebe'],
        [28, 'Ali Can', 'Üretim Müdürü', 'Üretim'],
        [29, 'Zeynep Şahin', 'İK Uzmanı', 'İnsan Kaynakları'],
        [30, 'Caner Aktaş', 'Finans Uzmanı', 'Finans'],
        [31, 'Ebru Güneş', 'Sistem Uzmanı', 'Bilgi İşlem'],
        [32, 'Kemal Başbakan', 'Kalite Kontrol Müdürü', 'Kalite'],
        [33, 'Gülşah Arslan', 'Arama Uzmanı', 'Arama']
    ];

    $stmt = $pdo->prepare("UPDATE personeller SET ad_soyad=?, pozisyon=?, departman=? WHERE personel_id=?");
    
    foreach ($updates as $update) {
        $stmt->execute([$update[1], $update[2], $update[3], $update[0]]);
    }
    
    echo "Turkish characters have been fixed for all employee records!";
    
    // Verify the updated data
    echo "<h3>Verification of Fixed Records:</h3>";
    $verifyStmt = $pdo->query("SELECT personel_id, ad_soyad, pozisyon, departman FROM personeller WHERE personel_id > 1 ORDER BY personel_id");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Personel ID</th><th>Ad Soyad</th><th>Pozisyon</th><th>Departman</th></tr>";
    
    while ($row = $verifyStmt->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['personel_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ad_soyad']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pozisyon']) . "</td>";
        echo "<td>" . htmlspecialchars($row['departman']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "Connection or query failed: " . $e->getMessage();
}
?>
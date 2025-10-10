<?php
header('Content-Type: text/html; charset=utf-8');
require_once('config.php'); // Assuming there's a config file

// Database connection - adjust these values as needed
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parfum_erp";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $stmt = $pdo->query("SELECT personel_id, ad_soyad, pozisyon, departman FROM personeller ORDER BY personel_id");
    $employees = $stmt->fetchAll();
    
    echo "<html><head><meta charset='utf-8'><title>Employee Verification</title></head><body>";
    echo "<h2>Employee Records Verification</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Personel ID</th><th>Ad Soyad</th><th>Pozisyon</th><th>Departman</th></tr>";
    
    foreach ($employees as $emp) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($emp['personel_id']) . "</td>";
        echo "<td>" . htmlspecialchars($emp['ad_soyad']) . "</td>";
        echo "<td>" . htmlspecialchars($emp['pozisyon']) . "</td>";
        echo "<td>" . htmlspecialchars($emp['departman']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</body></html>";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
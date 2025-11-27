<?php
include 'config.php';

// Simulate session for permission check if needed, or just bypass for debug
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['taraf'] = 'personel';
}

// We assume yetkisi_var is available from config.php -> auth_functions.php
// But we need to make sure we have the permission in session or mock the function if not included
// config.php usually includes auth functions.

$can_view_cost = true;
$cost_column = $can_view_cost ? ", COALESCE(vum.teorik_maliyet, 0) as teorik_maliyet" : "";

$query = "SELECT u.* {$cost_column} 
          FROM urunler u 
          " . ($can_view_cost ? "LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu" : "") . "
          ORDER BY u.urun_ismi LIMIT 5";

$result = $connection->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Urun: " . $row['urun_ismi'] . "\n";
        echo "Satis Fiyati: " . $row['satis_fiyati'] . " (Type: " . gettype($row['satis_fiyati']) . ")\n";
        echo "Teorik Maliyet: " . $row['teorik_maliyet'] . " (Type: " . gettype($row['teorik_maliyet']) . ")\n";
        echo "-------------------\n";
    }
} else {
    echo "Query Error: " . $connection->error;
}
?>
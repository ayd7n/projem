<?php
include 'config.php';
$kod = '22';
$query = "SELECT teorik_maliyet FROM v_urun_maliyetleri WHERE urun_kodu = '$kod'";
$result = $connection->query($query);
if ($result && $row = $result->fetch_assoc()) {
    echo "Cost for $kod: " . $row['teorik_maliyet'] . "\n";
} else {
    echo "No data.\n";
}
?>

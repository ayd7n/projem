<?php
include 'config.php';

echo "Checking v_esans_maliyetleri for non-zero costs:\n";
$query = "SELECT * FROM v_esans_maliyetleri WHERE toplam_maliyet > 0 LIMIT 1";
$result = $connection->query($query);
if ($result && $row = $result->fetch_assoc()) {
    print_r($row);
} else {
    echo "No esans with non-zero cost found.\n";
}

echo "\nChecking v_urun_maliyetleri for non-zero costs:\n";
$query = "SELECT * FROM v_urun_maliyetleri WHERE toplam_maliyet > 0 LIMIT 1";
$result = $connection->query($query);
if ($result && $row = $result->fetch_assoc()) {
    print_r($row);
} else {
    echo "No urun with non-zero cost found.\n";
}
?>

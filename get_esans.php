<?php
include 'config.php';
$result = $connection->query("SELECT esans_kodu FROM esanslar LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "Esans Code: " . $row['esans_kodu'] . "\n";
}
?>

<?php
// Script to update character encoding for existing employee records
require_once('config.php');

// Ensure we're using UTF8MB4
$connection->set_charset("utf8mb4");

// Update the records with proper encoding
$update_query = "UPDATE personeller SET 
    ad_soyad = CONVERT(BINARY CONVERT(ad_soyad USING latin1) USING utf8mb4),
    pozisyon = CONVERT(BINARY CONVERT(pozisyon USING latin1) USING utf8mb4),
    departman = CONVERT(BINARY CONVERT(departman USING latin1) USING utf8mb4)
    WHERE personel_id > 1";

if ($connection->query($update_query)) {
    echo "Character encoding updated for all employee records. Please refresh the personeller.php page.";
} else {
    echo "Error updating character encoding: " . $connection->error;
}

$connection->close();
?>
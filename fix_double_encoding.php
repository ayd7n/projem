<?php
require_once 'config.php';

$query = "SELECT hareket_id, hareket_turu FROM stok_hareket_kayitlari";
$result = $connection->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hareket_id = $row['hareket_id'];
        $hareket_turu = $row['hareket_turu'];

        // Try to fix double encoding
        $fixed_hareket_turu = utf8_decode($hareket_turu);
        $fixed_hareket_turu = utf8_encode($fixed_hareket_turu);

        if ($hareket_turu !== $fixed_hareket_turu) {
            $update_query = "UPDATE stok_hareket_kayitlari SET hareket_turu = '" . $connection->real_escape_string($fixed_hareket_turu) . "' WHERE hareket_id = '" . $hareket_id . "'";
            if ($connection->query($update_query)) {
                echo "Record updated: ID = " . $hareket_id . "\n";
            } else {
                echo "Error updating record: ID = " . $id . " - " . $connection->error . "\n";
            }
        }
    }
}

echo "Double encoding fix script finished.\n";
?>

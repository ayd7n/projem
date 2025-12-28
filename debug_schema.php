<?php
include 'config.php';

echo "--- Siparis Kalemleri Columns ---\n";
$result = $connection->query("SHOW COLUMNS FROM siparis_kalemleri");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error showing columns: " . $connection->error . "\n";
}

echo "\n--- Siparisler Columns ---\n";
$result = $connection->query("SHOW COLUMNS FROM siparisler");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error showing columns: " . $connection->error . "\n";
}
?>
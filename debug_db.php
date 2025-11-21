<?php
include 'config.php';

function check_table($table_name) {
    global $connection;
    echo "Table: $table_name\n";
    $result = $connection->query("SHOW COLUMNS FROM $table_name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "  Error: " . $connection->error . "\n";
    }
    echo "\n";
}

check_table('gider_yonetimi');

echo "Checking cost columns in 'malzemeler':\n";
check_table('malzemeler');

echo "Checking cost columns in 'urunler':\n";
check_table('urunler');

echo "Checking cost columns in 'esanslar':\n";
check_table('esanslar');
?>

<?php
include 'config.php';

try {
    $result = $connection->query("SHOW COLUMNS FROM gelir_yonetimi LIKE 'taksit_id'");
    if ($result->num_rows == 0) {
        $connection->query("ALTER TABLE gelir_yonetimi ADD COLUMN taksit_id INT DEFAULT NULL");
        echo "Column 'taksit_id' added successfully.";
    } else {
        echo "Column 'taksit_id' already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
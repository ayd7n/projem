<?php
include 'config.php';
$query = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'projem' AND TABLE_NAME = 'gelir_yonetimi'";
$result = $connection->query($query);
echo "--- Foreign Keys in gelir_yonetimi ---\n";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
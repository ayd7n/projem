<?php
require_once 'config.php';

echo "Table: stok_hareket_kayitlari\nColumns:\n";
$result = $connection->query('DESCRIBE stok_hareket_kayitlari');
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
<?php
include 'config.php';
$result = $connection->query("SHOW COLUMNS FROM urunler");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>

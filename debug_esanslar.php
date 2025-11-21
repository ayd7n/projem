<?php
include 'config.php';
$result = $connection->query("SHOW COLUMNS FROM esanslar");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>

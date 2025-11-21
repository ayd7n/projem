<?php
include 'config.php';
$result = $connection->query("SHOW COLUMNS FROM gider_yonetimi");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>

<?php
include 'config.php';

$result = $connection->query("SELECT value, label FROM malzeme_turleri ORDER BY label");
while ($row = $result->fetch_assoc()) {
    echo $row['value'] . ' - ' . $row['label'] . "\n";
}
?>

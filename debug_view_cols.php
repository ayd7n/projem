<?php
include 'config.php';
$result = $connection->query("SHOW COLUMNS FROM v_esans_maliyetleri");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>

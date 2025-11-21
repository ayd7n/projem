<?php
include 'config.php';
$result = $connection->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
while ($row = $result->fetch_row()) {
    echo $row[0] . "\n";
}
?>

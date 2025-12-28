<?php
include 'config.php';
$result = $connection->query("SHOW COLUMNS FROM siparis_kalemleri");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
}
?>
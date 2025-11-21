<?php
include 'config.php';
$result = $connection->query("SHOW COLUMNS FROM v_urun_maliyetleri");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Error: " . $connection->error . "\n";
}
?>

<?php
include 'config.php';
$fp = fopen('debug_schema_out.txt', 'w');
$tables = ['malzemeler', 'cerceve_sozlesmeler'];
foreach ($tables as $table) {
    fwrite($fp, "Table: $table\n");
    $res = $connection->query("SHOW COLUMNS FROM $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fwrite($fp, $row['Field'] . "\n");
        }
    } else {
        fwrite($fp, "Error: " . $connection->error . "\n");
    }
    fwrite($fp, "-------------------\n");
}
fclose($fp);
?>

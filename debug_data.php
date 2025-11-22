<?php
include 'config.php';

$result = $connection->query("SELECT * FROM montaj_is_emirleri LIMIT 5");
if ($result) {
    echo "Total Rows: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $connection->error;
}
?>

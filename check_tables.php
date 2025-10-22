<?php
require_once 'config.php';

// Get all tables in the database
$tables_query = 'SHOW TABLES';
$result = $connection->query($tables_query);

echo 'Database tables:' . PHP_EOL;
if($result) {
    while($row = $result->fetch_row()) {
        echo '- ' . $row[0] . PHP_EOL;
    }
} else {
    echo 'Error: Could not fetch tables.' . PHP_EOL;
    echo $connection->error;
}
?>
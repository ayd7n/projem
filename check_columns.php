<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/config.php';
$result = $connection->query("SHOW COLUMNS FROM siparis_kalemleri");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>

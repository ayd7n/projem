<?php
include 'config.php';
$code = 'ES001';
$query = "SELECT * FROM v_esans_maliyetleri WHERE esans_kodu = '$code'";
$result = $connection->query($query);
if ($result && $row = $result->fetch_assoc()) {
    print_r($row);
} else {
    echo "No data found for $code in view.\n";
    echo "Error: " . $connection->error . "\n";
}

// Also check if the view has any data
$query_all = "SELECT COUNT(*) as count FROM v_esans_maliyetleri";
$result_all = $connection->query($query_all);
$row_all = $result_all->fetch_assoc();
echo "Total rows in view: " . $row_all['count'] . "\n";
?>

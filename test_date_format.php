<?php
// Test script to debug date format issues
include 'config.php';

echo "<h2>Date Format Test</h2>";

// Check MySQL SQL mode
$result = $connection->query("SELECT @@sql_mode as mode");
$row = $result->fetch_assoc();
echo "<p><strong>MySQL SQL Mode:</strong> " . $row['mode'] . "</p>";

// Check current date format
echo "<p><strong>Current date (Y-m-d):</strong> " . date('Y-m-d') . "</p>";

// Check dates in the malzeme_siparisler table
$result = $connection->query("SELECT siparis_id, siparis_tarihi, teslim_tarihi FROM malzeme_siparisler ORDER BY siparis_id DESC LIMIT 5");
echo "<p><strong>Sample dates from DB (most recent 5):</strong></p>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Sipariş Tarihi</th><th>Teslim Tarihi</th></tr>";
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['siparis_id']}</td>";
        echo "<td>{$row['siparis_tarihi']}</td>";
        echo "<td>{$row['teslim_tarihi']}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No records found</td></tr>";
}
echo "</table>";

// Test date validation
$test_dates = [
    '2025-12-05',
    '0000-00-00',
    '2025-1-5',
    'invalid-date',
    date('Y-m-d')
];

echo "<p><strong>Date validation test (Y-m-d format):</strong></p>";
foreach ($test_dates as $test_date) {
    $date_obj = DateTime::createFromFormat('Y-m-d', $test_date);
    $is_valid = $date_obj && $date_obj->format('Y-m-d') === $test_date;
    $status = $is_valid ? 'VALID' : 'INVALID';
    echo "<p>  '$test_date' => $status</p>";
}
?>
</body>
</html>
<?php
// Mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'Test User';

// Mock GET request
$_GET['action'] = 'get_report_data';

// Capture output
ob_start();
include 'musteri_satis_raporu_islemler.php';
$output = ob_get_clean();

$data = json_decode($output, true);
echo "Status: " . ($data['status'] ?? 'null') . "\n";
if (isset($data['customer_totals'][0])) {
    echo "Sample Customer Total: \n";
    print_r($data['customer_totals'][0]);
} else {
    echo "No customer totals found.\n";
}
if (isset($data['data'][0])) {
    echo "Sample Data Item: \n";
    print_r($data['data'][0]);
} else {
    echo "No data items found.\n";
}
?>

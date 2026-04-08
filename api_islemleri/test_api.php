<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

// Mock session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'Test User';

// Mock GET request
$_GET['action'] = 'get_report_data';

// Capture output
ob_start();
require_once __DIR__ . '/musteri_satis_raporu_islemler.php';
$output = ob_get_clean();

echo $output;
?>

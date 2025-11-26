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
include 'api_islemleri/musteri_satis_raporu_islemler.php';
$output = ob_get_clean();

echo $output;
?>

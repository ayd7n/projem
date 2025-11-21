<?php
// Mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'TestUser';

// Mock POST data
$_POST['action'] = 'add_movement';
$_POST['stok_turu'] = 'malzeme';
$_POST['kod'] = '22'; // Valid code found earlier
$_POST['miktar'] = 1;
$_POST['yon'] = 'cikis';
$_POST['hareket_turu'] = 'fire';
$_POST['depo'] = 'TestDepo';
$_POST['raf'] = 'TestRaf';
$_POST['aciklama'] = 'Test Fire';
$_POST['ilgili_belge_no'] = 'TEST-001';

// Capture output
ob_start();
include 'api_islemleri/stok_hareket_islemler.php';
$output = ob_get_clean();

echo "Output:\n" . $output . "\n";
?>

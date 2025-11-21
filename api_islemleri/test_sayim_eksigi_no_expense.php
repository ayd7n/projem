<?php
// Mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'TestUser';

// Mock POST data for Sayim Eksigi
$_POST['action'] = 'add_movement';
$_POST['stok_turu'] = 'urun';
$_POST['kod'] = '22'; 
$_POST['miktar'] = 1;
$_POST['yon'] = 'cikis';
$_POST['hareket_turu'] = 'sayim_eksigi';
$_POST['depo'] = 'TestDepo';
$_POST['raf'] = 'TestRaf';
$_POST['aciklama'] = 'Test Sayim Eksigi No Expense';
$_POST['ilgili_belge_no'] = 'TEST-NO-EXPENSE-001';

// Capture output
ob_start();
include 'stok_hareket_islemler.php';
$output = ob_get_clean();

echo "Output:\n" . $output . "\n";

// Verify NO expense creation
include '../config.php';
$result = $connection->query("SELECT * FROM gider_yonetimi WHERE aciklama LIKE '%Test Sayim Eksigi No Expense%'");
if ($result && $result->num_rows > 0) {
    echo "Verification: FAILURE (Expense was created but shouldn't have been)\n";
} else {
    echo "Verification: SUCCESS (No expense created)\n";
}
?>

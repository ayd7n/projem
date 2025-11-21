<?php
// Mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['taraf'] = 'personel';
$_SESSION['kullanici_adi'] = 'TestUser';

// Mock POST data for Urun Fire
$_POST['action'] = 'add_movement';
$_POST['stok_turu'] = 'urun';
$_POST['kod'] = '22'; 
$_POST['miktar'] = 1;
$_POST['yon'] = 'cikis';
$_POST['hareket_turu'] = 'fire';
$_POST['depo'] = 'TestDepo';
$_POST['raf'] = 'TestRaf';
$_POST['aciklama'] = 'Test Urun Fire Fix';
$_POST['ilgili_belge_no'] = 'TEST-FIX-URUN-FINAL';

// Capture output
ob_start();
include 'stok_hareket_islemler.php';
$output = ob_get_clean();

echo "Output:\n" . $output . "\n";

// Verify expense creation
include '../config.php';
// Check the latest expense record created today
$today = date('Y-m-d');
$result = $connection->query("SELECT * FROM gider_yonetimi WHERE tarih = '$today' ORDER BY gider_id DESC LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    echo "Latest Expense ID: " . $row['gider_id'] . "\n";
    echo "Amount: " . $row['tutar'] . "\n";
    echo "Description: " . $row['aciklama'] . "\n";
    
    // Check if description contains the item code (22) and category is 'Fire Gideri'
    if (strpos($row['aciklama'], '(22)') !== false && $row['kategori'] === 'Fire Gideri') {
         echo "Verification: SUCCESS\n";
    } else {
         echo "Verification: FAILURE (Mismatch)\n";
         echo "Expected Category: Fire Gideri, Found: " . $row['kategori'] . "\n";
    }
} else {
    echo "Expense Created: No\n";
    echo "Error: " . $connection->error . "\n";
}
?>

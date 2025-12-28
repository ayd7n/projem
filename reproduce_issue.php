<?php
include 'config.php';
include 'api_islemleri/gelir_yonetimi_islemler.php';

// 1. Create a dummy order
$conn = $connection;
$conn->query("INSERT INTO siparisler (musteri_id, musteri_adi, tarih, durum, odeme_durumu, odenen_tutar) VALUES (1, 'Test Musteri', NOW(), 'onaylandi', 'bekliyor', 0)");
$siparis_id = $conn->insert_id;
echo "Created Order ID: $siparis_id\n";

$conn->query("INSERT INTO siparis_kalemleri (siparis_id, urun_kodu, adet, birim_fiyat) VALUES ($siparis_id, 'TEST', 1, 100)");

// 2. Add Income
$_POST = [
    'tarih' => date('Y-m-d'),
    'tutar' => 100,
    'kategori' => 'Sipariş Ödemesi',
    'odeme_tipi' => 'Nakit',
    'siparis_id' => $siparis_id,
    'aciklama' => 'Test',
    'musteri_adi' => 'Test Musteri'
];
// Mock session
$_SESSION['user_id'] = 1;
$_SESSION['kullanici_adi'] = 'Admin';

// We can't call addIncome directly because it uses echo json_encode and exits usually? 
// No, the function provided in previous view doesn't exit, it echos.
// I will simulate addIncome logic manually to avoid output buffering issues or just insert manually
$conn->query("INSERT INTO gelir_yonetimi (tarih, tutar, kategori, aciklama, siparis_id) VALUES (NOW(), 100, 'Sipariş Ödemesi', 'Test', $siparis_id)");
$gelir_id = $conn->insert_id;
echo "Created Income ID: $gelir_id\n";

updateOrderPaymentStatus($siparis_id);
echo "Order Status after Payment: " . $conn->query("SELECT odeme_durumu FROM siparisler WHERE siparis_id=$siparis_id")->fetch_object()->odeme_durumu . "\n";

// 3. Delete Income
// Simulate deleteIncome
$_POST['gelir_id'] = $gelir_id;
// Instead of calling deleteIncome which echoes json, I'll copy the logic logic.
$query = "DELETE FROM gelir_yonetimi WHERE gelir_id = $gelir_id";
$conn->query($query);
updateOrderPaymentStatus($siparis_id);

// 4. Check Order
$res = $conn->query("SELECT * FROM siparisler WHERE siparis_id = $siparis_id");
if ($res->num_rows > 0) {
    echo "Order EXISTS. Status: " . $res->fetch_object()->odeme_durumu . "\n";
} else {
    echo "Order DELETED!\n";
}

// Cleanup
$conn->query("DELETE FROM siparisler WHERE siparis_id = $siparis_id");
?>
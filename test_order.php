<?php
echo "=== DOĞRU SIPARIŞ OLUŞTURMA TESTİ ===\n\n";

// Session ve config ayarları
include 'config.php';
session_start();

// Simüle edilmiş session verisi
$_SESSION['cart'] = array(21 => 2, 22 => 3); // Örnek cart
$_SESSION['user_id'] = 3; // Test kullanıcısı
$_SESSION['id'] = 3;
$_SESSION['kullanici_adi'] = 'Test Customer';
$_SESSION['taraf'] = 'musteri';

echo "Session kontrolü:\n";
echo "user_id: " . $_SESSION['user_id'] . "\n";
echo "taraf: " . $_SESSION['taraf'] . "\n";
echo "kullanici_adi: " . $_SESSION['kullanici_adi'] . "\n";
echo "Cart: " . print_r($_SESSION['cart'], true) . "\n\n";

// GLOBALS ile REQUEST_METHOD'ı ayarla
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'submit_order';
$_POST['order_description'] = 'Test siparişi - manual test';

echo "=== ORDER_OPERATIONS.PHP ÇALIŞIYOR ===\n";

// order_operations.php'yi include et
ob_start();
include 'api_islemleri/order_operations.php';
$result = ob_get_clean();

echo "\nAPI Sonucu: $result\n";

// Test sonrası veritabanı kontrolü
echo "\n=== VERİTABANI KONTROLÜ ===\n";

$result = $connection->query("SELECT * FROM siparisler ORDER BY tarih DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $siparis = $result->fetch_assoc();
    echo "Yeni sipariş: ID " . $siparis['siparis_id'] . " - Durum: " . $siparis['durum'] . "\n";

    // Kalemleri kontrol et
    $result2 = $connection->query("SELECT * FROM siparis_kalemleri WHERE siparis_id = " . $siparis['siparis_id']);
    echo "Sipariş kalem sayısı: " . $result2->num_rows . "\n";

    if ($result2->num_rows > 0) {
        echo "Kalemler:\n";
        while ($kalem = $result2->fetch_assoc()) {
            echo "- {$kalem['urun_ismi']} ({$kalem['urun_kodu']}): {$kalem['adet']} {$kalem['birim']}\n";
        }
    }
} else {
    echo "Sipariş oluşturulmamış!\n";
}

$connection->close();
?>

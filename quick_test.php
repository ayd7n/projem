<?php
// Quick debug for product data retrieval
include 'config.php';

echo "=== TEST: ORDER_OPERATIONS.PHP ÜRÜN SORGUSU ===\n\n";

// Simüle edilmiş session verisi - test için
$_SESSION['user_id'] = 3;
$_SESSION['id'] = 3;
$_SESSION['kullanici_adi'] = 'Test User';
$_SESSION['taraf'] = 'musteri';

// Simüle edilmiş post verisi
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'submit_order';
$_POST['order_description'] = 'Test order';

// Test cart
$_SESSION['cart'] = array(21 => 2);

// Order operations kodunu çalıştır
echo "Config include işlemi\n";

// Sipariş oluşturma döngüsünü test et
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

foreach ($cart as $urun_kodu => $adet) {
    echo "\n--- Ürün {$urun_kodu} için sorgu test ---\n";

    $urun_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
    $urun_stmt = $connection->prepare($urun_query);
    $urun_stmt->bind_param('i', $urun_kodu);
    $urun_stmt->execute();
    $urun_result = $urun_stmt->get_result();
    $urun = $urun_result->fetch_assoc();

    echo "Raw query result: " . print_r($urun, true) . "\n";

    if ($urun) {
        $urun_ismi = $urun['urun_ismi'] ?: 'Bilinmeyen Ürün';
        $urun_birimi = $urun['birim'] ?: 'adet';
        $satis_fiyati = $urun['satis_fiyati'] ?: 0;

        echo "İşlenmiş değerler:\n";
        echo "  urun_ismi: '$urun_ismi'\n";
        echo "  birim: '$urun_birimi'\n";
        echo "  satisfiyati: '$satis_fiyati'\n";

        // Insert test WITHOUT transaction (just for debug)
        $order_item_query = "INSERT INTO siparis_kalemleri
                             (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar)
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
        $order_item_stmt = $connection->prepare($order_item_query);
        $siparis_id = 999; // Test ID
        $toplam_tutar = $adet * $satis_fiyati;

        // Explicit casting to fix binding issues
        $siparis_id = (int)$siparis_id;
        $urun_kodu = (int)$urun_kodu;
        $urun_ismi = (string)$urun_ismi;
        $adet = (int)$adet;
        $urun_birimi = (string)$urun_birimi;
        $satis_fiyati = (float)$satis_fiyati;
        $toplam_tutar = (float)$toplam_tutar;

        echo "Cast edilmiş değerler:\n";
        echo "  siparis_id: '$siparis_id' (" . gettype($siparis_id) . ")\n";
        echo "  urun_kodu: '$urun_kodu' (" . gettype($urun_kodu) . ")\n";
        echo "  urun_ismi: '$urun_ismi' (" . gettype($urun_ismi) . ")\n";
        echo "  adet: '$adet' (" . gettype($adet) . ")\n";
        echo "  urun_birimi: '$urun_birimi' (" . gettype($urun_birimi) . ")\n";
        echo "  satis_fiyati: '$satis_fiyati' (" . gettype($satis_fiyati) . ")\n";
        echo "  toplam_tutar: '$toplam_tutar' (" . gettype($toplam_tutar) . ")\n";

        $order_item_stmt->bind_param('iiisidd', $siparis_id, $urun_kodu, $urun_ismi,
                                     $adet, $urun_birimi, $satis_fiyati, $toplam_tutar);

        if ($order_item_stmt->execute()) {
            echo "Insert başarılı!\n";
        } else {
            echo "Insert başarısız: " . $order_item_stmt->error . "\n";
        }

        // Check what was actually inserted
        $check_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = 999 AND urun_kodu = ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param('i', $urun_kodu);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $inserted = $check_result->fetch_assoc();

        echo "Inserted row: " . print_r($inserted, true) . "\n";

        // Clean up test data
        $connection->query("DELETE FROM siparis_kalemleri WHERE siparis_id = 999");

    } else {
        echo "Ürün bulunamadı!\n";
    }
}

$connection->close();
?>

<?php
include '../config.php';

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'musteri') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_order') {
    // Get cart from session
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
    
    if (empty($cart)) {
        echo json_encode(['status' => 'error', 'message' => 'Sepetiniz boş!']);
        exit;
    }
    
    // Get customer info
    $musteri_id = $_SESSION['id'];
    $musteri_adi = $_SESSION['kullanici_adi'];
    $aciklama = isset($_POST['order_description']) ? $_POST['order_description'] : '';
    
    // Start transaction
    $connection->autocommit(FALSE);
    
    try {
        // Insert order
        $order_query = "INSERT INTO siparisler (musteri_id, musteri_adi, aciklama, olusturan_musteri) 
                        VALUES (?, ?, ?, ?)";
        $order_stmt = $connection->prepare($order_query);
        $order_stmt->bind_param('isss', $musteri_id, $musteri_adi, $aciklama, $_SESSION['kullanici_adi']);
        $order_stmt->execute();
        $siparis_id = $connection->insert_id;
        
        $toplam_adet = 0;
        
        // Insert order items
        foreach ($cart as $urun_kodu => $adet) {
            // Debug: Product query
            $urun_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
            $urun_stmt = $connection->prepare($urun_query);
            $urun_stmt->bind_param('i', $urun_kodu);
            $urun_stmt->execute();
            $urun_result = $urun_stmt->get_result();
            $urun = $urun_result->fetch_assoc();

            // Debug output to understand what's being retrieved
            error_log("Product query for code {$urun_kodu}: " . print_r($urun, true));

            if ($urun) {
                // Ensure we have valid data
                $urun_ismi = $urun['urun_ismi'] ?: 'Bilinmeyen Ürün';
                $urun_birimi = $urun['birim'] ?: 'adet';
                $satis_fiyati = $urun['satis_fiyati'] ?: 0;

                $toplam_tutar = $adet * $satis_fiyati;

                // Use direct SQL insert instead of prepared statement to avoid binding issues
                $urun_ismi_escaped = $connection->real_escape_string($urun_ismi);
                $urun_birimi_escaped = $connection->real_escape_string($urun_birimi);

                $order_item_sql = "INSERT INTO siparis_kalemleri
                                   (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar)
                                   VALUES ($siparis_id, $urun_kodu, '$urun_ismi_escaped', $adet, '$urun_birimi_escaped', $satis_fiyati, $toplam_tutar)";

                $connection->query($order_item_sql);
                $toplam_adet += $adet;
            } else {
                // If product not found, create a placeholder
                $order_item_sql = "INSERT INTO siparis_kalemleri
                                   (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar)
                                   VALUES ($siparis_id, $urun_kodu, 'Bilinmeyen Ürün', $adet, 'adet', 0, 0)";
                $connection->query($order_item_sql);

                $toplam_adet += $adet;
            }
        }
        
        // Update total quantity in order
        $update_order_query = "UPDATE siparisler SET toplam_adet = ? WHERE siparis_id = ?";
        $update_order_stmt = $connection->prepare($update_order_query);
        $update_order_stmt->bind_param('ii', $toplam_adet, $siparis_id);
        $update_order_stmt->execute();
        
        $connection->commit();
        unset($_SESSION['cart']); // Clear cart
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Siparişiniz başarıyla oluşturuldu!'
        ]);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode([
            'status' => 'error', 
            'message' => 'Sipariş oluşturulurken bir hata oluştu: ' . $e->getMessage()
        ]);
    }
    
    $connection->autocommit(TRUE);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek!']);
}

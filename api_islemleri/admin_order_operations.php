<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_order') {
    
    $musteri_id = isset($_POST['musteri_id']) ? intval($_POST['musteri_id']) : 0;
    $items = isset($_POST['items']) ? $_POST['items'] : [];
    $aciklama = isset($_POST['aciklama']) ? $_POST['aciklama'] : '';
    
    if ($musteri_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz müşteri seçimi!']);
        exit;
    }

    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Sepet boş!']);
        exit;
    }

    // Get customer name
    $musteri_query = "SELECT musteri_adi FROM musteriler WHERE musteri_id = ?";
    $musteri_stmt = $connection->prepare($musteri_query);
    $musteri_stmt->bind_param('i', $musteri_id);
    $musteri_stmt->execute();
    $musteri_result = $musteri_stmt->get_result();
    
    if ($musteri_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri bulunamadı!']);
        exit;
    }
    
    $musteri_row = $musteri_result->fetch_assoc();
    $musteri_adi = $musteri_row['musteri_adi'];
    
    // Creator info (Admin/Staff)
    $olusturan = "Personel: " . ($_SESSION['kullanici_adi'] ?? 'Bilinmeyen');

    // Start transaction
    $connection->autocommit(FALSE);
    
    try {
        // Insert order
        $order_query = "INSERT INTO siparisler (musteri_id, musteri_adi, aciklama, olusturan_musteri, durum, tarih) 
                        VALUES (?, ?, ?, ?, 'beklemede', NOW())";
        $order_stmt = $connection->prepare($order_query);
        $order_stmt->bind_param('isss', $musteri_id, $musteri_adi, $aciklama, $olusturan);
        
        if (!$order_stmt->execute()) {
            throw new Exception("Sipariş oluşturulamadı: " . $order_stmt->error);
        }
        
        $siparis_id = $connection->insert_id;
        $toplam_adet = 0;
        
        // Insert order items
        foreach ($items as $item) {
            $urun_kodu = intval($item['id']);
            $adet = floatval($item['quantity']);
            
            if ($adet <= 0) continue;

            // Get product details for current price and unit
            $urun_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
            $urun_stmt = $connection->prepare($urun_query);
            $urun_stmt->bind_param('i', $urun_kodu);
            $urun_stmt->execute();
            $urun_result = $urun_stmt->get_result();
            $urun = $urun_result->fetch_assoc();

            if ($urun) {
                $urun_ismi = $urun['urun_ismi'];
                $urun_birimi = $urun['birim'];
                $satis_fiyati = $urun['satis_fiyati'];
                $toplam_tutar = $adet * $satis_fiyati;

                $urun_ismi_escaped = $connection->real_escape_string($urun_ismi);
                $urun_birimi_escaped = $connection->real_escape_string($urun_birimi);

                $order_item_sql = "INSERT INTO siparis_kalemleri
                                   (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar)
                                   VALUES ($siparis_id, $urun_kodu, '$urun_ismi_escaped', $adet, '$urun_birimi_escaped', $satis_fiyati, $toplam_tutar)";

                if (!$connection->query($order_item_sql)) {
                    throw new Exception("Sipariş kalemi eklenemedi: " . $connection->error);
                }
                
                $toplam_adet += $adet;
            }
        }
        
        // Update total quantity in order
        $update_order_query = "UPDATE siparisler SET toplam_adet = ? WHERE siparis_id = ?";
        $update_order_stmt = $connection->prepare($update_order_query);
        $update_order_stmt->bind_param('ii', $toplam_adet, $siparis_id);
        $update_order_stmt->execute();
        
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$musteri_adi müşterisi için yeni sipariş oluşturuldu (ID: $siparis_id)", 'CREATE');

        $connection->commit();

        // Get order items for Telegram message
        $order_items_query = "SELECT urun_ismi, adet, birim FROM siparis_kalemleri WHERE siparis_id = ?";
        $order_items_stmt = $connection->prepare($order_items_query);
        $order_items_stmt->bind_param('i', $siparis_id);
        $order_items_stmt->execute();
        $order_items_result = $order_items_stmt->get_result();

        $order_items_text = "SİPARİŞ KALEMLERİ:\n";
        while ($item = $order_items_result->fetch_assoc()) {
            $order_items_text .= "- {$item['urun_ismi']} ({$item['adet']} {$item['birim']})\n";
        }
        $order_items_stmt->close();

        // Send Telegram notification
        $telegram_message = "🆕 YENİ MÜŞTERİ SİPARİŞİ\n\n";
        $telegram_message .= "Sipariş No: #$siparis_id\n";
        $telegram_message .= "Müşteri: $musteri_adi\n";
        $telegram_message .= "Oluşturan: {$_SESSION['kullanici_adi']}\n";
        $telegram_message .= "Tarih: " . date('d.m.Y H:i') . "\n\n";
        $telegram_message .= $order_items_text;

        telegram_gonder($telegram_message);

        echo json_encode([
            'status' => 'success',
            'message' => 'Sipariş başarıyla oluşturuldu!',
            'siparis_id' => $siparis_id
        ]);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
    
    $connection->autocommit(TRUE);
    
} else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek!']);
}

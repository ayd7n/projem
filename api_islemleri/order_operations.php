<?php
include '../config.php';

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'musteri') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erisim!']);
    exit;
}

function prepare_or_throw($connection, $query, $error_message)
{
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        throw new Exception($error_message . ': ' . $connection->error);
    }

    return $stmt;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_order') {
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
    if (empty($cart)) {
        echo json_encode(['status' => 'error', 'message' => 'Sepetiniz bos!']);
        exit;
    }

    $validated_cart = [];
    foreach ($cart as $urun_kodu => $adet) {
        $urun_kodu = (int) $urun_kodu;
        $adet = (int) $adet;

        if ($urun_kodu <= 0 || $adet <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sepette gecersiz urun veya adet bulundu. Lutfen sepetinizi yeniden olusturun.'
            ]);
            exit;
        }

        $validated_cart[$urun_kodu] = $adet;
    }

    $musteri_id = (int) ($_SESSION['id'] ?? 0);
    $musteri_adi = (string) ($_SESSION['kullanici_adi'] ?? '');
    $aciklama = isset($_POST['order_description']) ? trim((string) $_POST['order_description']) : '';

    $connection->begin_transaction();

    try {
        $order_stmt = prepare_or_throw(
            $connection,
            "INSERT INTO siparisler (musteri_id, musteri_adi, aciklama, olusturan_musteri) VALUES (?, ?, ?, ?)",
            'Siparis olusturma sorgusu hazirlanamadi'
        );
        $order_stmt->bind_param('isss', $musteri_id, $musteri_adi, $aciklama, $_SESSION['kullanici_adi']);
        if (!$order_stmt->execute()) {
            throw new Exception('Siparis kaydi olusturulamadi: ' . $order_stmt->error);
        }
        $order_stmt->close();

        $siparis_id = (int) $connection->insert_id;
        $toplam_adet = 0;

        $product_stmt = prepare_or_throw(
            $connection,
            "SELECT urun_ismi, birim, satis_fiyati, satis_fiyati_para_birimi, stok_miktari FROM urunler WHERE urun_kodu = ?",
            'Urun sorgusu hazirlanamadi'
        );
        $item_stmt = prepare_or_throw(
            $connection,
            "INSERT INTO siparis_kalemleri (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar, para_birimi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            'Siparis kalemi sorgusu hazirlanamadi'
        );

        foreach ($validated_cart as $urun_kodu => $adet) {
            $product_stmt->bind_param('i', $urun_kodu);
            if (!$product_stmt->execute()) {
                throw new Exception('Urun bilgisi alinamadi: ' . $product_stmt->error);
            }

            $urun_result = $product_stmt->get_result();
            $urun = $urun_result ? $urun_result->fetch_assoc() : null;
            if (!$urun) {
                throw new Exception("Urun bulunamadi: {$urun_kodu}");
            }

            $stok_miktari = (int) ($urun['stok_miktari'] ?? 0);
            if ($stok_miktari <= 0 || $adet > $stok_miktari) {
                throw new Exception("{$urun['urun_ismi']} icin yeterli stok bulunmuyor.");
            }

            $urun_ismi = (string) ($urun['urun_ismi'] ?: 'Bilinmeyen Urun');
            $urun_birimi = (string) ($urun['birim'] ?: 'adet');
            $satis_fiyati = (float) ($urun['satis_fiyati'] ?: 0);
            $para_birimi = (string) ($urun['satis_fiyati_para_birimi'] ?? 'TRY');
            $toplam_tutar = $adet * $satis_fiyati;

            $item_stmt->bind_param(
                'iisisdds',
                $siparis_id,
                $urun_kodu,
                $urun_ismi,
                $adet,
                $urun_birimi,
                $satis_fiyati,
                $toplam_tutar,
                $para_birimi
            );

            if (!$item_stmt->execute()) {
                throw new Exception('Siparis kalemi kaydedilemedi: ' . $item_stmt->error);
            }

            $toplam_adet += $adet;
        }

        $product_stmt->close();
        $item_stmt->close();

        if ($toplam_adet <= 0) {
            throw new Exception('Siparis toplami gecersiz.');
        }

        $update_order_stmt = prepare_or_throw(
            $connection,
            "UPDATE siparisler SET toplam_adet = ? WHERE siparis_id = ?",
            'Siparis toplam sorgusu hazirlanamadi'
        );
        $update_order_stmt->bind_param('ii', $toplam_adet, $siparis_id);
        if (!$update_order_stmt->execute()) {
            throw new Exception('Siparis toplami guncellenemedi: ' . $update_order_stmt->error);
        }
        $update_order_stmt->close();

        $log_stmt = prepare_or_throw(
            $connection,
            "INSERT INTO log_tablosu (kullanici_adi, log_metni, islem_turu) VALUES (?, ?, ?)",
            'Log sorgusu hazirlanamadi'
        );
        $log_message = "$musteri_adi musterisi tarafindan siparis olusturuldu (ID: $siparis_id)";
        $islem_turu = 'CREATE';
        $log_stmt->bind_param('sss', $_SESSION['kullanici_adi'], $log_message, $islem_turu);
        if (!$log_stmt->execute()) {
            throw new Exception('Log kaydi olusturulamadi: ' . $log_stmt->error);
        }
        $log_stmt->close();

        $connection->commit();
        unset($_SESSION['cart']);

        $order_items_stmt = prepare_or_throw(
            $connection,
            "SELECT urun_ismi, adet, birim FROM siparis_kalemleri WHERE siparis_id = ?",
            'Siparis kalemleri sorgusu hazirlanamadi'
        );
        $order_items_stmt->bind_param('i', $siparis_id);
        $order_items_stmt->execute();
        $order_items_result = $order_items_stmt->get_result();

        $order_items_text = "SIPARIS KALEMLERI:\n";
        while ($item = $order_items_result->fetch_assoc()) {
            $order_items_text .= "- {$item['urun_ismi']} ({$item['adet']} {$item['birim']})\n";
        }
        $order_items_stmt->close();

        $telegram_message = "YENI MUSTERI SIPARISI\n\n";
        $telegram_message .= "Siparis No: #$siparis_id\n";
        $telegram_message .= "Musteri: $musteri_adi\n";
        $telegram_message .= "Olusturan: {$_SESSION['kullanici_adi']}\n";
        $telegram_message .= "Tarih: " . date('d.m.Y H:i') . "\n\n";
        $telegram_message .= $order_items_text;

        telegram_gonder($telegram_message);

        echo json_encode([
            'status' => 'success',
            'message' => 'Siparisiniz basariyla olusturuldu!'
        ]);
    } catch (Throwable $e) {
        $connection->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => 'Siparis olusturulurken bir hata olustu: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gecersiz istek!']);
}

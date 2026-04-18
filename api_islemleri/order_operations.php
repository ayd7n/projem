<?php
include '../config.php';

header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'musteri') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erisim!']);
    exit;
}

function ensure_product_brand_and_box_columns_for_customer($connection)
{
    static $ensured = false;
    if ($ensured) {
        return;
    }
    $ensured = true;

    try {
        $column_result = $connection->query("SHOW COLUMNS FROM urunler");
        if (!$column_result) {
            return;
        }

        $columns = [];
        while ($column = $column_result->fetch_assoc()) {
            $columns[$column['Field']] = true;
        }

        if (!isset($columns['marka'])) {
            $connection->query("ALTER TABLE urunler ADD COLUMN marka VARCHAR(255) NOT NULL DEFAULT 'Belirtilmedi' AFTER urun_ismi");
        }

        if (!isset($columns['koli_ici_adet'])) {
            $connection->query("ALTER TABLE urunler ADD COLUMN koli_ici_adet INT UNSIGNED NOT NULL DEFAULT 1 AFTER birim");
        }

        $connection->query("UPDATE urunler SET marka = 'Belirtilmedi' WHERE marka IS NULL OR TRIM(marka) = ''");
        $connection->query("UPDATE urunler SET koli_ici_adet = 1 WHERE koli_ici_adet IS NULL OR koli_ici_adet < 1");
    } catch (Throwable $e) {
        // no-op
    }
}

function normalize_order_currency_code($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === '' || $currency === 'TL') {
        return 'TRY';
    }

    return $currency;
}

function normalize_cart_unit($value)
{
    $unit = strtolower(trim((string) $value));
    return $unit === 'koli' ? 'koli' : 'adet';
}

function parse_positive_int($value)
{
    if (is_string($value)) {
        $value = trim($value);
    }

    if ($value === null || $value === '') {
        return null;
    }

    $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($parsed === false) {
        return null;
    }

    return (int) $parsed;
}

function prepare_or_throw($connection, $query, $error_message)
{
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        throw new Exception($error_message . ': ' . $connection->error);
    }

    return $stmt;
}

function normalize_cart_entry($urun_kodu, $entry)
{
    $siparis_birimi = 'adet';
    $siparis_miktari = null;
    $koli_ici_adet = 1;

    if (is_array($entry)) {
        $siparis_birimi = normalize_cart_unit($entry['siparis_birimi'] ?? 'adet');
        $siparis_miktari = parse_positive_int($entry['siparis_miktari'] ?? null);
        $koli_ici_adet = max(1, (int) ($entry['koli_ici_adet'] ?? 1));

        if ($siparis_miktari === null) {
            $legacy_adet = parse_positive_int($entry['adet'] ?? null);
            if ($legacy_adet !== null) {
                $siparis_miktari = $legacy_adet;
                $siparis_birimi = 'adet';
            }
        }

        if ($siparis_miktari === null) {
            $gercek_adet = parse_positive_int($entry['gercek_adet'] ?? null);
            if ($gercek_adet !== null) {
                if ($siparis_birimi === 'koli') {
                    $siparis_miktari = max(1, (int) ceil($gercek_adet / $koli_ici_adet));
                } else {
                    $siparis_miktari = $gercek_adet;
                }
            }
        }
    } else {
        $siparis_miktari = parse_positive_int($entry);
        $siparis_birimi = 'adet';
    }

    if ((int) $urun_kodu <= 0 || $siparis_miktari === null) {
        return null;
    }

    $gercek_adet = $siparis_birimi === 'koli'
        ? $siparis_miktari * $koli_ici_adet
        : $siparis_miktari;

    return [
        'urun_kodu' => (int) $urun_kodu,
        'siparis_birimi' => $siparis_birimi,
        'siparis_miktari' => $siparis_miktari,
        'gercek_adet' => $gercek_adet,
        'koli_ici_adet' => $koli_ici_adet
    ];
}

ensure_product_brand_and_box_columns_for_customer($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_order') {
    $cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if (empty($cart)) {
        echo json_encode(['status' => 'error', 'message' => 'Sepetiniz bos!']);
        exit;
    }

    $validated_cart = [];
    foreach ($cart as $urun_kodu => $entry) {
        $normalized_entry = normalize_cart_entry($urun_kodu, $entry);
        if ($normalized_entry === null || (int) $normalized_entry['gercek_adet'] <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sepette gecersiz urun veya miktar bulundu. Lutfen sepetinizi yeniden olusturun.'
            ]);
            exit;
        }

        $validated_cart[(int) $urun_kodu] = $normalized_entry;
    }

    $musteri_id = (int) ($_SESSION['id'] ?? ($_SESSION['user_id'] ?? 0));
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
        $siparis_para_birimi = 'TRY';
        $is_currency_set = false;
        $telegram_lines = [];

        $product_stmt = prepare_or_throw(
            $connection,
            "SELECT urun_ismi, birim, satis_fiyati, satis_fiyati_para_birimi, stok_miktari, COALESCE(koli_ici_adet, 1) AS koli_ici_adet
             FROM urunler WHERE urun_kodu = ?",
            'Urun sorgusu hazirlanamadi'
        );
        $item_stmt = prepare_or_throw(
            $connection,
            "INSERT INTO siparis_kalemleri (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar, para_birimi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            'Siparis kalemi sorgusu hazirlanamadi'
        );

        foreach ($validated_cart as $urun_kodu => $cart_item) {
            $product_stmt->bind_param('i', $urun_kodu);
            if (!$product_stmt->execute()) {
                throw new Exception('Urun bilgisi alinamadi: ' . $product_stmt->error);
            }

            $urun_result = $product_stmt->get_result();
            $urun = $urun_result ? $urun_result->fetch_assoc() : null;
            if (!$urun) {
                throw new Exception("Urun bulunamadi: {$urun_kodu}");
            }

            $siparis_birimi = normalize_cart_unit($cart_item['siparis_birimi'] ?? 'adet');
            $siparis_miktari = parse_positive_int($cart_item['siparis_miktari'] ?? null);
            if ($siparis_miktari === null) {
                throw new Exception('Sepette gecersiz miktar bulundu.');
            }

            $koli_ici_adet = max(1, (int) ($urun['koli_ici_adet'] ?? 1));
            $gercek_adet = $siparis_birimi === 'koli'
                ? $siparis_miktari * $koli_ici_adet
                : $siparis_miktari;

            $stok_miktari = (int) ($urun['stok_miktari'] ?? 0);
            if ($stok_miktari <= 0 || $gercek_adet > $stok_miktari) {
                throw new Exception("{$urun['urun_ismi']} icin yeterli stok bulunmuyor.");
            }

            $urun_ismi = (string) ($urun['urun_ismi'] ?: 'Bilinmeyen Urun');
            $urun_birimi = (string) ($urun['birim'] ?: 'adet');
            $satis_fiyati = (float) ($urun['satis_fiyati'] ?: 0);
            $para_birimi = normalize_order_currency_code($urun['satis_fiyati_para_birimi'] ?? 'TRY');

            if (!$is_currency_set) {
                $siparis_para_birimi = $para_birimi;
                $is_currency_set = true;
            } elseif ($siparis_para_birimi !== $para_birimi) {
                throw new Exception('Ayni sipariste farkli para birimlerine sahip urunler kullanilamaz.');
            }

            $toplam_tutar = $gercek_adet * $satis_fiyati;

            $item_stmt->bind_param(
                'iisisdds',
                $siparis_id,
                $urun_kodu,
                $urun_ismi,
                $gercek_adet,
                $urun_birimi,
                $satis_fiyati,
                $toplam_tutar,
                $para_birimi
            );

            if (!$item_stmt->execute()) {
                throw new Exception('Siparis kalemi kaydedilemedi: ' . $item_stmt->error);
            }

            $toplam_adet += $gercek_adet;

            if ($siparis_birimi === 'koli') {
                $telegram_lines[] = "- {$urun_ismi} ({$siparis_miktari} koli = {$gercek_adet} adet)";
            } else {
                $telegram_lines[] = "- {$urun_ismi} ({$siparis_miktari} adet)";
            }
        }

        $product_stmt->close();
        $item_stmt->close();

        if ($toplam_adet <= 0) {
            throw new Exception('Siparis toplami gecersiz.');
        }

        $update_order_stmt = prepare_or_throw(
            $connection,
            "UPDATE siparisler SET toplam_adet = ?, para_birimi = ? WHERE siparis_id = ?",
            'Siparis toplam sorgusu hazirlanamadi'
        );
        $update_order_stmt->bind_param('isi', $toplam_adet, $siparis_para_birimi, $siparis_id);
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

        $order_items_text = "SIPARIS KALEMLERI:\n";
        foreach ($telegram_lines as $line) {
            $order_items_text .= $line . "\n";
        }

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

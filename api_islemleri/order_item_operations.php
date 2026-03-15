<?php
include '../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erisim!']);
    exit;
}

function require_pending_order($connection, $siparis_id)
{
    $stmt = $connection->prepare("SELECT durum FROM siparisler WHERE siparis_id = ?");
    if (!$stmt) {
        throw new Exception('Siparis durumu okunamadi: ' . $connection->error);
    }

    $stmt->bind_param('i', $siparis_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$order) {
        throw new Exception('Siparis bulunamadi.');
    }

    if (($order['durum'] ?? '') !== 'beklemede') {
        throw new Exception('Sadece beklemede olan siparislerde degisiklik yapilabilir!');
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

function get_order_item_summary($connection, $siparis_id, $exclude_kalem_id = 0)
{
    if ($exclude_kalem_id > 0) {
        $stmt = $connection->prepare(
            "SELECT COUNT(*) AS item_count,
                    COALESCE(SUM(adet), 0) AS total_qty,
                    COUNT(DISTINCT para_birimi) AS currency_count,
                    MIN(para_birimi) AS order_currency
             FROM siparis_kalemleri
             WHERE siparis_id = ? AND kalem_id <> ?"
        );
        if (!$stmt) {
            throw new Exception('Siparis ozet sorgusu hazirlanamadi: ' . $connection->error);
        }

        $stmt->bind_param('ii', $siparis_id, $exclude_kalem_id);
    } else {
        $stmt = $connection->prepare(
            "SELECT COUNT(*) AS item_count,
                    COALESCE(SUM(adet), 0) AS total_qty,
                    COUNT(DISTINCT para_birimi) AS currency_count,
                    MIN(para_birimi) AS order_currency
             FROM siparis_kalemleri
             WHERE siparis_id = ?"
        );
        if (!$stmt) {
            throw new Exception('Siparis ozet sorgusu hazirlanamadi: ' . $connection->error);
        }

        $stmt->bind_param('i', $siparis_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $summary ?: [
        'item_count' => 0,
        'total_qty' => 0,
        'currency_count' => 0,
        'order_currency' => null,
    ];
}

function assert_order_currency_is_compatible($connection, $siparis_id, $candidate_currency, $exclude_kalem_id = 0)
{
    $summary = get_order_item_summary($connection, $siparis_id, $exclude_kalem_id);
    $currency_count = (int) ($summary['currency_count'] ?? 0);

    if ($currency_count <= 0) {
        return;
    }

    if ($currency_count > 1) {
        throw new Exception('Bu sipariste birden fazla para birimi tespit edildi. Lutfen siparisi duzeltmeden devam etmeyin.');
    }

    $existing_currency = normalize_order_currency_code($summary['order_currency'] ?? 'TRY');
    $candidate_currency = normalize_order_currency_code($candidate_currency);
    if ($existing_currency !== $candidate_currency) {
        throw new Exception('Ayni sipariste farkli para birimleri kullanilamaz.');
    }
}

function refresh_order_total_quantity($connection, $siparis_id)
{
    $summary = get_order_item_summary($connection, $siparis_id);
    $currency_count = (int) ($summary['currency_count'] ?? 0);
    if ($currency_count > 1) {
        throw new Exception('Siparis ozeti guncellenemedi: sipariste birden fazla para birimi var.');
    }

    $current_stmt = $connection->prepare("SELECT para_birimi FROM siparisler WHERE siparis_id = ?");
    if (!$current_stmt) {
        throw new Exception('Siparis para birimi okunamadi: ' . $connection->error);
    }

    $current_stmt->bind_param('i', $siparis_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $current_order = $current_result ? $current_result->fetch_assoc() : null;
    $current_stmt->close();

    $toplam_adet = (int) ($summary['total_qty'] ?? 0);
    if ((int) ($summary['item_count'] ?? 0) > 0) {
        $order_currency = normalize_order_currency_code($summary['order_currency'] ?? 'TRY');
    } else {
        $order_currency = normalize_order_currency_code($current_order['para_birimi'] ?? 'TRY');
    }

    $stmt = $connection->prepare(
        "UPDATE siparisler
         SET toplam_adet = ?, para_birimi = ?
         WHERE siparis_id = ?"
    );
    if (!$stmt) {
        throw new Exception('Siparis toplam sorgusu hazirlanamadi: ' . $connection->error);
    }

    $stmt->bind_param('isi', $toplam_adet, $order_currency, $siparis_id);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new Exception('Siparis toplami guncellenemedi: ' . $error);
    }
    $stmt->close();
}

function get_order_item_by_id($connection, $siparis_id, $kalem_id)
{
    $stmt = $connection->prepare("SELECT * FROM siparis_kalemleri WHERE siparis_id = ? AND kalem_id = ?");
    if (!$stmt) {
        throw new Exception('Siparis kalemi sorgusu hazirlanamadi: ' . $connection->error);
    }

    $stmt->bind_param('ii', $siparis_id, $kalem_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $item;
}

function get_product_snapshot($connection, $urun_kodu)
{
    $stmt = $connection->prepare(
        "SELECT urun_ismi, birim, satis_fiyati, satis_fiyati_para_birimi
         FROM urunler
         WHERE urun_kodu = ?"
    );
    if (!$stmt) {
        throw new Exception('Urun sorgusu hazirlanamadi: ' . $connection->error);
    }

    $stmt->bind_param('i', $urun_kodu);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $product;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $siparis_id = isset($_POST['siparis_id']) ? (int) $_POST['siparis_id'] : 0;

    if ($siparis_id > 0) {
        try {
            require_pending_order($connection, $siparis_id);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'add_order_item') {
        $urun_kodu = isset($_POST['urun_kodu']) ? (int) $_POST['urun_kodu'] : 0;
        $adet = isset($_POST['adet']) ? (int) $_POST['adet'] : 0;

        if ($siparis_id <= 0 || $urun_kodu <= 0 || $adet <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz siparis ID, urun kodu veya adet!']);
            exit;
        }

        try {
            $product = get_product_snapshot($connection, $urun_kodu);
            if (!$product) {
                echo json_encode(['status' => 'error', 'message' => 'Secilen urun bulunamadi!']);
                exit;
            }

            $urun_ismi = trim((string) ($product['urun_ismi'] ?? ''));
            $birim = trim((string) ($product['birim'] ?? ''));
            $fiyat = (float) ($product['satis_fiyati'] ?? 0);
            $para_birimi = (string) ($product['satis_fiyati_para_birimi'] ?? 'TRY');

            if ($urun_ismi === '' || $urun_ismi === '0') {
                $urun_ismi = 'Bilinmeyen Urun';
            }
            if ($birim === '' || $birim === '0') {
                $birim = 'adet';
            }
            if ($fiyat < 0) {
                $fiyat = 0;
            }

            $toplam_tutar = $adet * $fiyat;
            $para_birimi = normalize_order_currency_code($para_birimi);

            $connection->begin_transaction();
            assert_order_currency_is_compatible($connection, $siparis_id, $para_birimi);

            $stmt = $connection->prepare(
                "INSERT INTO siparis_kalemleri
                 (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar, para_birimi)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$stmt) {
                throw new Exception('Siparis kalemi sorgusu hazirlanamadi: ' . $connection->error);
            }

            $stmt->bind_param('iisisdds', $siparis_id, $urun_kodu, $urun_ismi, $adet, $birim, $fiyat, $toplam_tutar, $para_birimi);
            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                throw new Exception('Siparis kalemi eklenemedi: ' . $error);
            }

            $kalem_id = (int) $connection->insert_id;
            $stmt->close();

            refresh_order_total_quantity($connection, $siparis_id);
            $connection->commit();

            log_islem($connection, $_SESSION['kullanici_adi'], "Siparise $urun_ismi urunu eklendi (ID: $siparis_id)", 'CREATE');

            echo json_encode([
                'status' => 'success',
                'message' => 'Siparis kalemi basariyla eklendi.',
                'item_data' => [
                    'kalem_id' => $kalem_id,
                    'siparis_id' => $siparis_id,
                    'urun_kodu' => $urun_kodu,
                    'urun_ismi' => $urun_ismi,
                    'adet' => $adet,
                    'birim' => $birim,
                    'birim_fiyat' => number_format($fiyat, 2, '.', ''),
                    'toplam_tutar' => number_format($toplam_tutar, 2, '.', ''),
                    'para_birimi' => $para_birimi,
                ],
            ]);
        } catch (Exception $e) {
            @$connection->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Islem sirasinda hata olustu: ' . $e->getMessage()]);
        }
    } elseif ($action === 'update_order_item') {
        $kalem_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : (isset($_POST['old_urun_kodu']) ? (int) $_POST['old_urun_kodu'] : 0);
        $urun_kodu = isset($_POST['urun_kodu']) ? (int) $_POST['urun_kodu'] : 0;
        $adet = isset($_POST['adet']) ? (int) $_POST['adet'] : 0;

        if ($siparis_id <= 0 || $kalem_id <= 0 || $urun_kodu <= 0 || $adet <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz parametreler!']);
            exit;
        }

        try {
            $existing_item = get_order_item_by_id($connection, $siparis_id, $kalem_id);
            if (!$existing_item) {
                echo json_encode(['status' => 'error', 'message' => 'Guncellenecek siparis kalemi bulunamadi!']);
                exit;
            }

            $product = get_product_snapshot($connection, $urun_kodu);
            if (!$product) {
                echo json_encode(['status' => 'error', 'message' => 'Secilen urun bulunamadi!']);
                exit;
            }

            $urun_ismi = trim((string) ($product['urun_ismi'] ?? ''));
            $birim = trim((string) ($product['birim'] ?? ''));
            $fiyat = (float) ($product['satis_fiyati'] ?? 0);
            $para_birimi = (string) ($product['satis_fiyati_para_birimi'] ?? 'TRY');

            if ($urun_ismi === '' || $urun_ismi === '0') {
                $urun_ismi = 'Bilinmeyen Urun';
            }
            if ($birim === '' || $birim === '0') {
                $birim = 'adet';
            }
            if ($fiyat < 0) {
                $fiyat = 0;
            }

            $toplam_tutar = $adet * $fiyat;
            $para_birimi = normalize_order_currency_code($para_birimi);

            $connection->begin_transaction();
            assert_order_currency_is_compatible($connection, $siparis_id, $para_birimi, $kalem_id);

            $stmt = $connection->prepare(
                "UPDATE siparis_kalemleri
                 SET urun_kodu = ?, urun_ismi = ?, adet = ?, birim = ?, birim_fiyat = ?, toplam_tutar = ?, para_birimi = ?
                 WHERE kalem_id = ? AND siparis_id = ?"
            );
            if (!$stmt) {
                throw new Exception('Siparis kalemi guncelleme sorgusu hazirlanamadi: ' . $connection->error);
            }

            $stmt->bind_param('isisddsii', $urun_kodu, $urun_ismi, $adet, $birim, $fiyat, $toplam_tutar, $para_birimi, $kalem_id, $siparis_id);
            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                throw new Exception('Siparis kalemi guncellenemedi: ' . $error);
            }
            $stmt->close();

            refresh_order_total_quantity($connection, $siparis_id);
            $connection->commit();

            $old_product_name = $existing_item['urun_ismi'] ?? 'Bilinmeyen Urun';
            log_islem($connection, $_SESSION['kullanici_adi'], "Siparis kalemi guncellendi: $old_product_name urunu $urun_ismi olarak degistirildi (ID: $siparis_id)", 'UPDATE');

            echo json_encode([
                'status' => 'success',
                'message' => 'Siparis kalemi basariyla guncellendi.',
                'item_data' => [
                    'kalem_id' => $kalem_id,
                    'siparis_id' => $siparis_id,
                    'urun_kodu' => $urun_kodu,
                    'urun_ismi' => $urun_ismi,
                    'adet' => $adet,
                    'birim' => $birim,
                    'birim_fiyat' => number_format($fiyat, 2, '.', ''),
                    'toplam_tutar' => number_format($toplam_tutar, 2, '.', ''),
                    'para_birimi' => $para_birimi,
                ],
            ]);
        } catch (Exception $e) {
            @$connection->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Islem sirasinda hata olustu: ' . $e->getMessage()]);
        }
    } elseif ($action === 'delete_order_item') {
        $kalem_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : (isset($_POST['urun_kodu']) ? (int) $_POST['urun_kodu'] : 0);

        if ($siparis_id <= 0 || $kalem_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz parametreler!']);
            exit;
        }

        try {
            $connection->begin_transaction();
            $existing_item = get_order_item_by_id($connection, $siparis_id, $kalem_id);
            if (!$existing_item) {
                throw new Exception('Silinecek siparis kalemi bulunamadi!');
            }

            $stmt = $connection->prepare("DELETE FROM siparis_kalemleri WHERE kalem_id = ? AND siparis_id = ?");
            if (!$stmt) {
                throw new Exception('Siparis kalemi silme sorgusu hazirlanamadi: ' . $connection->error);
            }

            $stmt->bind_param('ii', $kalem_id, $siparis_id);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $error = $stmt->error;
                $stmt->close();
                throw new Exception('Siparis kalemi silinemedi: ' . $error);
            }
            $stmt->close();

            refresh_order_total_quantity($connection, $siparis_id);
            $connection->commit();

            $deleted_product_name = $existing_item['urun_ismi'] ?? 'Bilinmeyen Urun';
            log_islem($connection, $_SESSION['kullanici_adi'], "Siparisten $deleted_product_name urunu silindi (ID: $siparis_id)", 'DELETE');

            echo json_encode([
                'status' => 'success',
                'message' => 'Siparis kalemi basariyla silindi.',
                'kalem_id' => $kalem_id,
            ]);
        } catch (Exception $e) {
            @$connection->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Islem sirasinda hata olustu: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gecersiz islem!']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_product_info') {
        $urun_kodu = isset($_GET['urun_kodu']) ? (int) $_GET['urun_kodu'] : 0;

        if ($urun_kodu <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Gecersiz urun kodu!']);
            exit;
        }

        try {
            $product = get_product_snapshot($connection, $urun_kodu);
            if ($product) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'urun_ismi' => $product['urun_ismi'],
                        'birim' => $product['birim'],
                        'satis_fiyati' => (float) $product['satis_fiyati'],
                        'para_birimi' => $product['satis_fiyati_para_birimi'] ?? 'TRY',
                    ],
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Urun bulunamadi!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Islem sirasinda hata olustu: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gecersiz GET islemi!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gecersiz istek yontemi!']);
}

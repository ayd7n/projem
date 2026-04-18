<?php
// Must be first - prevent any HTML output before JSON
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unexpected output
ob_start();

include '../config.php';

// Clear any output from config.php
ob_end_clean();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

// Only staff can access this API
if ($_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz yok.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

function get_purchase_order_status_group($durum)
{
    if (in_array($durum, ['taslak', 'onaylandi', 'olusturuldu'], true)) {
        return 'olusturuldu';
    }

    if (in_array($durum, ['gonderildi', 'kismen_teslim', 'tamamlandi', 'yollandi'], true)) {
        return 'yollandi';
    }

    if ($durum === 'kapatildi') {
        return 'kapatildi';
    }

    return $durum;
}

function resolve_purchase_order_status($durum, $existing_durum = '')
{
    if ($durum === 'olusturuldu') {
        $durum = 'taslak';
    } elseif ($durum === 'yollandi') {
        $durum = 'gonderildi';
    }

    if (!empty($existing_durum) && get_purchase_order_status_group($existing_durum) === get_purchase_order_status_group($durum)) {
        return $existing_durum;
    }

    return $durum;
}

switch ($action) {
    case 'get_all_orders':
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1) {
            $limit = 10;
        }
        if ($limit > 200) {
            $limit = 200;
        }
        $search = $_GET['search'] ?? '';
        $durum_filter = $_GET['durum'] ?? '';
        $tedarikci_filter = $_GET['tedarikci_id'] ?? '';

        $offset = ($page - 1) * $limit;

        // Build query
        $where_parts = [];
        if (!empty($search)) {
            $where_parts[] = "(siparis_no LIKE '%$search%' OR tedarikci_adi LIKE '%$search%' OR aciklama LIKE '%$search%')";
        }
        if (!empty($durum_filter)) {
            $where_parts[] = "durum = '$durum_filter'";
        }
        if (!empty($tedarikci_filter)) {
            $tedarikci_filter = (int) $tedarikci_filter;
            $where_parts[] = "tedarikci_id = $tedarikci_filter";
        }

        $where_sql = !empty($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';

        // Get total count
        $count_result = $connection->query("SELECT COUNT(*) as total FROM satinalma_siparisler $where_sql");
        $total = $count_result ? $count_result->fetch_assoc()['total'] : 0;

        // Get paginated orders
        $query = "SELECT s.*, 
                  (SELECT COALESCE((SUM(teslim_edilen_miktar) / NULLIF(SUM(miktar), 0)) * 100, 0) 
                   FROM satinalma_siparis_kalemleri 
                   WHERE siparis_id = s.siparis_id) as teslimat_yuzdesi
                  FROM satinalma_siparisler s 
                  $where_sql 
                  ORDER BY s.siparis_id DESC 
                  LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);

        $orders = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => $orders,
            'total' => (int) $total,
            'page' => $page,
            'pages' => ceil($total / $limit)
        ]);
        break;

    case 'get_order':
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş ID belirtilmedi.']);
            break;
        }

        // Get order details
        $result = $connection->query("SELECT * FROM satinalma_siparisler WHERE siparis_id = $id");

        if (!$result || $result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş bulunamadı.']);
            break;
        }

        $order = $result->fetch_assoc();

        // Get order items
        $items_result = $connection->query("SELECT * FROM satinalma_siparis_kalemleri WHERE siparis_id = $id");

        $items = [];
        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = $item;
            }
        }

        $order['kalemler'] = $items;

        echo json_encode(['status' => 'success', 'data' => $order]);
        break;

    case 'generate_order_number':
        $year = date('Y');

        $result = $connection->query("SELECT siparis_no FROM satinalma_siparisler WHERE siparis_no LIKE 'PO-$year-%' ORDER BY siparis_id DESC LIMIT 1");

        if ($result && $result->num_rows > 0) {
            $last = $result->fetch_assoc()['siparis_no'];
            $parts = explode('-', $last);
            $next_num = ((int) end($parts)) + 1;
        } else {
            $next_num = 1;
        }

        $order_no = sprintf("PO-%s-%05d", $year, $next_num);
        echo json_encode(['status' => 'success', 'siparis_no' => $order_no]);
        break;

    case 'add_order':
        $tedarikci_id = (int) ($_POST['tedarikci_id'] ?? 0);
        $siparis_tarihi = $_POST['siparis_tarihi'] ?? date('Y-m-d');
        $istenen_teslim_tarihi = $_POST['istenen_teslim_tarihi'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';
        $durum = resolve_purchase_order_status($_POST['durum'] ?? 'taslak');
        $para_birimi = $_POST['para_birimi'] ?? 'TRY';
        $kalemler = isset($_POST['kalemler']) ? json_decode($_POST['kalemler'], true) : [];

        // Validation
        if (!$tedarikci_id || empty($kalemler)) {
            echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ve en az bir sipariş kalemi gereklidir.']);
            break;
        }

        // Get tedarikci name
        $tedarikci_result = $connection->query("SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = $tedarikci_id");
        if (!$tedarikci_result || $tedarikci_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
            break;
        }
        $tedarikci_adi = $tedarikci_result->fetch_assoc()['tedarikci_adi'];

        // Generate order number
        $year = date('Y');
        $no_result = $connection->query("SELECT siparis_no FROM satinalma_siparisler WHERE siparis_no LIKE 'PO-$year-%' ORDER BY siparis_id DESC LIMIT 1");

        if ($no_result && $no_result->num_rows > 0) {
            $last = $no_result->fetch_assoc()['siparis_no'];
            $parts = explode('-', $last);
            $next_num = ((int) end($parts)) + 1;
        } else {
            $next_num = 1;
        }
        $siparis_no = sprintf("PO-%s-%05d", $year, $next_num);

        // Calculate total
        $toplam_tutar = 0;
        foreach ($kalemler as $kalem) {
            $toplam_tutar += floatval($kalem['toplam_fiyat'] ?? 0);
        }

        $olusturan_id = (int) $_SESSION['user_id'];
        $olusturan_adi = $_SESSION['kullanici_adi'] ?? '';

        // Escape values
        $siparis_no_esc = $connection->real_escape_string($siparis_no);
        $tedarikci_adi_esc = $connection->real_escape_string($tedarikci_adi);
        $siparis_tarihi_esc = $connection->real_escape_string($siparis_tarihi);
        $istenen_teslim_esc = !empty($istenen_teslim_tarihi) ? "'" . $connection->real_escape_string($istenen_teslim_tarihi) . "'" : "NULL";
        $durum_esc = $connection->real_escape_string($durum);
        $para_birimi_esc = $connection->real_escape_string($para_birimi);
        $aciklama_esc = $connection->real_escape_string($aciklama);
        $olusturan_adi_esc = $connection->real_escape_string($olusturan_adi);

        $connection->begin_transaction();

        try {
            // Insert order
            $insert_sql = "INSERT INTO satinalma_siparisler 
                (siparis_no, tedarikci_id, tedarikci_adi, siparis_tarihi, istenen_teslim_tarihi, durum, toplam_tutar, para_birimi, aciklama, olusturan_id, olusturan_adi)
                VALUES ('$siparis_no_esc', $tedarikci_id, '$tedarikci_adi_esc', '$siparis_tarihi_esc', $istenen_teslim_esc, '$durum_esc', $toplam_tutar, '$para_birimi_esc', '$aciklama_esc', $olusturan_id, '$olusturan_adi_esc')";

            if (!$connection->query($insert_sql)) {
                throw new Exception('Sipariş kaydedilemedi: ' . $connection->error);
            }

            $siparis_id = $connection->insert_id;

            $seen_codes = [];

            // Insert order items
            foreach ($kalemler as $kalem) {
                $malzeme_kodu = (int) ($kalem['malzeme_kodu'] ?? 0);
                $malzeme_adi_raw = trim((string) ($kalem['malzeme_adi'] ?? ''));

                // Kokpitten gelen kalemlerde kod boş/0 gelebilir; isimden eşleyip kaydet.
                if ($malzeme_kodu <= 0 && $malzeme_adi_raw !== '') {
                    $malzeme_adi_lookup = $connection->real_escape_string($malzeme_adi_raw);
                    $code_result = $connection->query("SELECT malzeme_kodu FROM malzemeler WHERE malzeme_ismi = '$malzeme_adi_lookup' LIMIT 1");
                if ($code_result && $code_row = $code_result->fetch_assoc()) {
                    $malzeme_kodu = (int) $code_row['malzeme_kodu'];
                }
            }

                if ($malzeme_kodu > 0 && isset($seen_codes[$malzeme_kodu])) {
                    throw new Exception('Ayni malzeme ayni satinalma siparisinde birden fazla kez eklenemez.');
                }

                $malzeme_adi = $connection->real_escape_string($malzeme_adi_raw);
                $miktar = floatval($kalem['miktar']);
                $birim = $connection->real_escape_string($kalem['birim'] ?? 'adet');
                $birim_fiyat = floatval($kalem['birim_fiyat'] ?? 0);
                $kalem_para_birimi = $connection->real_escape_string($kalem['para_birimi'] ?? 'TRY');
                $toplam_fiyat = floatval($kalem['toplam_fiyat'] ?? 0);
                $kalem_aciklama = $connection->real_escape_string($kalem['aciklama'] ?? '');

                $seen_codes[$malzeme_kodu] = true;

                $item_sql = "INSERT INTO satinalma_siparis_kalemleri 
                    (siparis_id, malzeme_kodu, malzeme_adi, miktar, birim, birim_fiyat, para_birimi, toplam_fiyat, aciklama)
                    VALUES ($siparis_id, $malzeme_kodu, '$malzeme_adi', $miktar, '$birim', $birim_fiyat, '$kalem_para_birimi', $toplam_fiyat, '$kalem_aciklama')";

                if (!$connection->query($item_sql)) {
                    throw new Exception('Sipariş kalemi kaydedilemedi: ' . $connection->error);
                }
            }

            $connection->commit();

            log_islem($connection, $_SESSION['kullanici_adi'], "$tedarikci_adi tedarikçisine $siparis_no no'lu satınalma siparişi oluşturuldu", 'CREATE');
            echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla oluşturuldu.', 'siparis_id' => $siparis_id, 'siparis_no' => $siparis_no]);
        } catch (Exception $e) {
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'update_order':
        $siparis_id = (int) ($_POST['siparis_id'] ?? 0);
        $istenen_teslim_tarihi = $_POST['istenen_teslim_tarihi'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';
        $durum = $_POST['durum'] ?? 'taslak';
        $para_birimi = $_POST['para_birimi'] ?? 'TRY';
        $kalemler = isset($_POST['kalemler']) ? json_decode($_POST['kalemler'], true) : [];

        if (!$siparis_id || empty($kalemler)) {
            echo json_encode(['status' => 'error', 'message' => 'Siparis ID ve en az bir siparis kalemi gereklidir.']);
            break;
        }

        $connection->begin_transaction();

        try {
            $current_order_result = $connection->query("SELECT durum FROM satinalma_siparisler WHERE siparis_id = $siparis_id FOR UPDATE");
            if (!$current_order_result || $current_order_result->num_rows === 0) {
                throw new Exception('Guncellenecek siparis bulunamadi.');
            }

            $current_order = $current_order_result->fetch_assoc();
            $resolved_status = resolve_purchase_order_status($durum, $current_order['durum'] ?? '');

            $existing_items_result = $connection->query("SELECT malzeme_kodu, teslim_edilen_miktar FROM satinalma_siparis_kalemleri WHERE siparis_id = $siparis_id");
            $existing_delivered = [];
            if ($existing_items_result) {
                while ($existing_item = $existing_items_result->fetch_assoc()) {
                    $existing_delivered[(int) $existing_item['malzeme_kodu']] = (float) $existing_item['teslim_edilen_miktar'];
                }
            }

            $normalized_items = [];
            $seen_codes = [];
            $toplam_tutar = 0;

            foreach ($kalemler as $kalem) {
                $malzeme_kodu = (int) ($kalem['malzeme_kodu'] ?? 0);
                $malzeme_adi_raw = trim((string) ($kalem['malzeme_adi'] ?? ''));

                if ($malzeme_kodu <= 0 && $malzeme_adi_raw !== '') {
                    $malzeme_adi_lookup = $connection->real_escape_string($malzeme_adi_raw);
                    $code_result = $connection->query("SELECT malzeme_kodu FROM malzemeler WHERE malzeme_ismi = '$malzeme_adi_lookup' LIMIT 1");
                    if ($code_result && $code_row = $code_result->fetch_assoc()) {
                        $malzeme_kodu = (int) $code_row['malzeme_kodu'];
                    }
                }

                if (isset($seen_codes[$malzeme_kodu])) {
                    throw new Exception('Ayni malzeme ayni satinalma siparisinde birden fazla kez yer alamaz.');
                }

                $miktar = floatval($kalem['miktar'] ?? 0);
                if ($malzeme_kodu <= 0 || $miktar <= 0) {
                    throw new Exception('Siparis kalemlerinde gecersiz malzeme veya miktar bulundu.');
                }

                $teslim_edilen = $existing_delivered[$malzeme_kodu] ?? 0.0;
                if ($teslim_edilen > $miktar) {
                    throw new Exception("{$malzeme_adi_raw} kalemi icin miktar, teslim edilen miktarin altina dusurulemez.");
                }

                $birim_fiyat = floatval($kalem['birim_fiyat'] ?? 0);
                $toplam_fiyat = floatval($kalem['toplam_fiyat'] ?? ($miktar * $birim_fiyat));
                $toplam_tutar += $toplam_fiyat;

                $normalized_items[] = [
                    'malzeme_kodu' => $malzeme_kodu,
                    'malzeme_adi' => $connection->real_escape_string($malzeme_adi_raw),
                    'miktar' => $miktar,
                    'birim' => $connection->real_escape_string($kalem['birim'] ?? 'adet'),
                    'birim_fiyat' => $birim_fiyat,
                    'para_birimi' => $connection->real_escape_string($kalem['para_birimi'] ?? 'TRY'),
                    'toplam_fiyat' => $toplam_fiyat,
                    'teslim_edilen_miktar' => $teslim_edilen,
                    'aciklama' => $connection->real_escape_string($kalem['aciklama'] ?? ''),
                ];
                $seen_codes[$malzeme_kodu] = true;
            }

            foreach ($existing_delivered as $existing_code => $delivered_amount) {
                if ($delivered_amount > 0 && !isset($seen_codes[$existing_code])) {
                    throw new Exception('Teslim alinmis kalemler siparisten kaldirilamaz.');
                }
            }

            $istenen_teslim_esc = !empty($istenen_teslim_tarihi) ? "'" . $connection->real_escape_string($istenen_teslim_tarihi) . "'" : "NULL";
            $durum_esc = $connection->real_escape_string($resolved_status);
            $para_birimi_esc = $connection->real_escape_string($para_birimi);
            $aciklama_esc = $connection->real_escape_string($aciklama);

            $update_sql = "UPDATE satinalma_siparisler SET 
                istenen_teslim_tarihi = $istenen_teslim_esc, 
                durum = '$durum_esc', 
                toplam_tutar = $toplam_tutar, 
                para_birimi = '$para_birimi_esc', 
                aciklama = '$aciklama_esc' 
                WHERE siparis_id = $siparis_id";

            if (!$connection->query($update_sql)) {
                throw new Exception('Siparis guncellenemedi: ' . $connection->error);
            }

            if (!$connection->query("DELETE FROM satinalma_siparis_kalemleri WHERE siparis_id = $siparis_id")) {
                throw new Exception('Eski siparis kalemleri silinemedi: ' . $connection->error);
            }

            foreach ($normalized_items as $kalem) {
                $item_sql = "INSERT INTO satinalma_siparis_kalemleri 
                    (siparis_id, malzeme_kodu, malzeme_adi, miktar, birim, birim_fiyat, para_birimi, toplam_fiyat, teslim_edilen_miktar, aciklama)
                    VALUES ($siparis_id, {$kalem['malzeme_kodu']}, '{$kalem['malzeme_adi']}', {$kalem['miktar']}, '{$kalem['birim']}', {$kalem['birim_fiyat']}, '{$kalem['para_birimi']}', {$kalem['toplam_fiyat']}, {$kalem['teslim_edilen_miktar']}, '{$kalem['aciklama']}')";

                if (!$connection->query($item_sql)) {
                    throw new Exception('Siparis kalemi kaydedilemedi: ' . $connection->error);
                }
            }

            $connection->commit();

            log_islem($connection, $_SESSION['kullanici_adi'], "Satinalma siparisi #$siparis_id guncellendi", 'UPDATE');
            echo json_encode(['status' => 'success', 'message' => 'Siparis basariyla guncellendi.']);
        } catch (Exception $e) {
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case '__legacy_update_order':
        $siparis_id = (int) ($_POST['siparis_id'] ?? 0);
        $istenen_teslim_tarihi = $_POST['istenen_teslim_tarihi'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';
        $durum = $_POST['durum'] ?? 'taslak';
        $para_birimi = $_POST['para_birimi'] ?? 'TRY';
        $kalemler = isset($_POST['kalemler']) ? json_decode($_POST['kalemler'], true) : [];

        if (!$siparis_id) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş ID belirtilmedi.']);
            break;
        }

        // Calculate total
        $toplam_tutar = 0;
        foreach ($kalemler as $kalem) {
            $toplam_tutar += floatval($kalem['toplam_fiyat'] ?? 0);
        }

        // Escape values
        $istenen_teslim_esc = !empty($istenen_teslim_tarihi) ? "'" . $connection->real_escape_string($istenen_teslim_tarihi) . "'" : "NULL";
        $durum_esc = $connection->real_escape_string($durum);
        $para_birimi_esc = $connection->real_escape_string($para_birimi);
        $aciklama_esc = $connection->real_escape_string($aciklama);

        $connection->begin_transaction();

        try {
            // Update order
            $update_sql = "UPDATE satinalma_siparisler SET 
                istenen_teslim_tarihi = $istenen_teslim_esc, 
                durum = '$durum_esc', 
                toplam_tutar = $toplam_tutar, 
                para_birimi = '$para_birimi_esc', 
                aciklama = '$aciklama_esc' 
                WHERE siparis_id = $siparis_id";

            if (!$connection->query($update_sql)) {
                throw new Exception('Sipariş güncellenemedi: ' . $connection->error);
            }

            // Delete existing items
            $connection->query("DELETE FROM satinalma_siparis_kalemleri WHERE siparis_id = $siparis_id");

            // Insert order items
            foreach ($kalemler as $kalem) {
                $malzeme_kodu = (int) ($kalem['malzeme_kodu'] ?? 0);
                $malzeme_adi_raw = trim((string) ($kalem['malzeme_adi'] ?? ''));

                if ($malzeme_kodu <= 0 && $malzeme_adi_raw !== '') {
                    $malzeme_adi_lookup = $connection->real_escape_string($malzeme_adi_raw);
                    $code_result = $connection->query("SELECT malzeme_kodu FROM malzemeler WHERE malzeme_ismi = '$malzeme_adi_lookup' LIMIT 1");
                    if ($code_result && $code_row = $code_result->fetch_assoc()) {
                        $malzeme_kodu = (int) $code_row['malzeme_kodu'];
                    }
                }

                $malzeme_adi = $connection->real_escape_string($malzeme_adi_raw);
                $miktar = floatval($kalem['miktar']);
                $birim = $connection->real_escape_string($kalem['birim'] ?? 'adet');
                $birim_fiyat = floatval($kalem['birim_fiyat'] ?? 0);
                $kalem_para_birimi = $connection->real_escape_string($kalem['para_birimi'] ?? 'TRY');
                $toplam_fiyat = floatval($kalem['toplam_fiyat'] ?? 0);
                $teslim_edilen = floatval($kalem['teslim_edilen_miktar'] ?? 0);
                $kalem_aciklama = $connection->real_escape_string($kalem['aciklama'] ?? '');

                $item_sql = "INSERT INTO satinalma_siparis_kalemleri 
                    (siparis_id, malzeme_kodu, malzeme_adi, miktar, birim, birim_fiyat, para_birimi, toplam_fiyat, teslim_edilen_miktar, aciklama)
                    VALUES ($siparis_id, $malzeme_kodu, '$malzeme_adi', $miktar, '$birim', $birim_fiyat, '$kalem_para_birimi', $toplam_fiyat, $teslim_edilen, '$kalem_aciklama')";

                if (!$connection->query($item_sql)) {
                    throw new Exception('Sipariş kalemi kaydedilemedi: ' . $connection->error);
                }
            }

            $connection->commit();

            log_islem($connection, $_SESSION['kullanici_adi'], "Satınalma siparişi #$siparis_id güncellendi", 'UPDATE');
            echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla güncellendi.']);
        } catch (Exception $e) {
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'update_status':
        $siparis_id = (int) ($_POST['siparis_id'] ?? 0);
        $durum = $_POST['durum'] ?? '';

        if (!$siparis_id || !$durum) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş ID ve durum belirtilmedi.']);
            break;
        }

        $valid_statuses = ['taslak', 'onaylandi', 'gonderildi', 'kismen_teslim', 'tamamlandi', 'iptal', 'kapatildi'];
        if (!in_array($durum, $valid_statuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz durum.']);
            break;
        }

        $current_status_result = $connection->query("SELECT durum FROM satinalma_siparisler WHERE siparis_id = $siparis_id");
        if (!$current_status_result || $current_status_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Siparis bulunamadi.']);
            break;
        }

        $current_status = $current_status_result->fetch_assoc()['durum'] ?? '';
        $durum = resolve_purchase_order_status($durum, $current_status);

        if ($connection->query("UPDATE satinalma_siparisler SET durum = '$durum' WHERE siparis_id = $siparis_id")) {
            log_islem($connection, $_SESSION['kullanici_adi'], "Satınalma siparişi #$siparis_id durumu '$durum' olarak güncellendi", 'UPDATE');
            echo json_encode(['status' => 'success', 'message' => 'Durum başarıyla güncellendi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Durum güncellenirken hata oluştu.']);
        }
        break;

    case 'delete_order':
        $siparis_id = (int) ($_POST['siparis_id'] ?? 0);

        if (!$siparis_id) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş ID belirtilmedi.']);
            break;
        }

        // Get order info for logging
        $result = $connection->query("SELECT siparis_no, tedarikci_adi FROM satinalma_siparisler WHERE siparis_id = $siparis_id");

        if (!$result || $result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Silinecek sipariş bulunamadı.']);
            break;
        }

        $order = $result->fetch_assoc();

        if ($connection->query("DELETE FROM satinalma_siparisler WHERE siparis_id = $siparis_id")) {
            log_islem($connection, $_SESSION['kullanici_adi'], "{$order['siparis_no']} no'lu {$order['tedarikci_adi']} siparişi silindi", 'DELETE');
            echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla silindi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş silinirken hata oluştu.']);
        }
        break;

    case 'get_suppliers':
        $result = $connection->query("SELECT tedarikci_id, tedarikci_adi FROM tedarikciler ORDER BY tedarikci_adi");

        $suppliers = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $suppliers[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $suppliers]);
        break;

    case 'get_materials_for_supplier':
        $tedarikci_id = (int) ($_GET['tedarikci_id'] ?? 0);

        if (!$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ID belirtilmedi.']);
            break;
        }

        // Get materials from cerceve_sozlesmeler for this supplier
        $query = "SELECT DISTINCT 
            cs.malzeme_kodu, 
            cs.malzeme_ismi as malzeme_adi, 
            cs.birim_fiyat, 
            cs.para_birimi,
            m.birim
            FROM cerceve_sozlesmeler cs
            LEFT JOIN malzemeler m ON cs.malzeme_kodu = m.malzeme_kodu
            WHERE cs.tedarikci_id = $tedarikci_id
            AND (cs.baslangic_tarihi IS NULL OR cs.baslangic_tarihi <= CURDATE())
            AND (cs.bitis_tarihi IS NULL OR cs.bitis_tarihi >= CURDATE())
            ORDER BY cs.malzeme_ismi";

        $result = $connection->query($query);

        $materials = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $materials[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $materials]);
        break;

    case 'get_orders_by_supplier':
        $tedarikci_id = (int) ($_GET['tedarikci_id'] ?? 0);
        $durum_filter = $_GET['durum'] ?? '';

        if (!$tedarikci_id) {
            echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ID belirtilmedi.']);
            break;
        }

        $where_sql = "WHERE tedarikci_id = $tedarikci_id";
        if (!empty($durum_filter)) {
            $durum_esc = $connection->real_escape_string($durum_filter);
            $where_sql .= " AND durum = '$durum_esc'";
        }

        $result = $connection->query("SELECT * FROM satinalma_siparisler $where_sql ORDER BY siparis_tarihi DESC");

        $orders = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Get items for each order
                $items_result = $connection->query("SELECT * FROM satinalma_siparis_kalemleri WHERE siparis_id = " . $row['siparis_id']);

                $items = [];
                if ($items_result) {
                    while ($item = $items_result->fetch_assoc()) {
                        $items[] = $item;
                    }
                }

                $row['kalemler'] = $items;
                $orders[] = $row;
            }
        }

        // Get summary stats
        $stats_result = $connection->query("SELECT 
            COUNT(*) as toplam_siparis,
            SUM(CASE WHEN durum NOT IN ('tamamlandi', 'iptal') THEN 1 ELSE 0 END) as bekleyen_siparis,
            SUM(CASE WHEN durum = 'tamamlandi' THEN 1 ELSE 0 END) as tamamlanan_siparis
            FROM satinalma_siparisler WHERE tedarikci_id = $tedarikci_id");
        $stats = $stats_result ? $stats_result->fetch_assoc() : [];

        $rates = ['USD' => 0.0, 'EUR' => 0.0];
        $rate_result = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
        if ($rate_result) {
            while ($rate_row = $rate_result->fetch_assoc()) {
                if (($rate_row['ayar_anahtar'] ?? '') === 'dolar_kuru') {
                    $rates['USD'] = max(0.0, (float) ($rate_row['ayar_deger'] ?? 0));
                } elseif (($rate_row['ayar_anahtar'] ?? '') === 'euro_kuru') {
                    $rates['EUR'] = max(0.0, (float) ($rate_row['ayar_deger'] ?? 0));
                }
            }
        }

        $stats['toplam_tutar'] = 0.0;
        $stats['toplam_tutar_detay'] = ['TRY' => 0.0, 'USD' => 0.0, 'EUR' => 0.0];
        $total_by_currency_result = $connection->query("SELECT para_birimi, SUM(toplam_tutar) as toplam_tutar FROM satinalma_siparisler WHERE tedarikci_id = $tedarikci_id GROUP BY para_birimi");
        if ($total_by_currency_result) {
            while ($total_row = $total_by_currency_result->fetch_assoc()) {
                $currency = strtoupper(trim((string) ($total_row['para_birimi'] ?? 'TRY')));
                if ($currency === 'TL' || $currency === '') {
                    $currency = 'TRY';
                }
                if (!in_array($currency, ['TRY', 'USD', 'EUR'], true)) {
                    $currency = 'TRY';
                }

                $amount = (float) ($total_row['toplam_tutar'] ?? 0);
                $stats['toplam_tutar_detay'][$currency] += $amount;

                if ($currency === 'USD') {
                    $usdRate = (float) ($rates['USD'] ?? 0);
                    if ($usdRate <= 0) {
                        echo json_encode(['status' => 'error', 'message' => 'USD kuru tanimli degil veya 0.']);
                        exit;
                    }
                    $stats['toplam_tutar'] += $amount * $usdRate;
                } elseif ($currency === 'EUR') {
                    $eurRate = (float) ($rates['EUR'] ?? 0);
                    if ($eurRate <= 0) {
                        echo json_encode(['status' => 'error', 'message' => 'EUR kuru tanimli degil veya 0.']);
                        exit;
                    }
                    $stats['toplam_tutar'] += $amount * $eurRate;
                } else {
                    $stats['toplam_tutar'] += $amount;
                }
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => $orders,
            'stats' => $stats
        ]);
        break;

    case 'get_print_data':
        $siparis_id = (int) ($_GET['siparis_id'] ?? 0);

        if (!$siparis_id) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş ID belirtilmedi.']);
            break;
        }

        // Get order details with supplier info
        $result = $connection->query("SELECT s.*, t.adres as tedarikci_adres, t.telefon as tedarikci_telefon, t.e_posta as tedarikci_email
            FROM satinalma_siparisler s
            LEFT JOIN tedarikciler t ON s.tedarikci_id = t.tedarikci_id
            WHERE s.siparis_id = $siparis_id");

        if (!$result || $result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş bulunamadı.']);
            break;
        }

        $order = $result->fetch_assoc();

        // Get order items
        $items_result = $connection->query("SELECT * FROM satinalma_siparis_kalemleri WHERE siparis_id = $siparis_id");

        $items = [];
        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = $item;
            }
        }

        $order['kalemler'] = $items;

        echo json_encode(['status' => 'success', 'data' => $order]);
        break;

    case 'get_stats':
        $result = $connection->query("SELECT 
            COUNT(*) as toplam,
            SUM(CASE WHEN durum = 'taslak' THEN 1 ELSE 0 END) as taslak,
            SUM(CASE WHEN durum = 'onaylandi' THEN 1 ELSE 0 END) as onaylandi,
            SUM(CASE WHEN durum = 'gonderildi' THEN 1 ELSE 0 END) as gonderildi,
            SUM(CASE WHEN durum = 'kismen_teslim' THEN 1 ELSE 0 END) as kismen_teslim,
            SUM(CASE WHEN durum = 'tamamlandi' THEN 1 ELSE 0 END) as tamamlandi,
            SUM(CASE WHEN durum = 'iptal' THEN 1 ELSE 0 END) as iptal
            FROM satinalma_siparisler");

        $stats = $result ? $result->fetch_assoc() : [];

        echo json_encode(['status' => 'success', 'data' => $stats]);
        break;

    case 'check_material_best_price':
        $malzeme_kodu = (int) ($_GET['malzeme_kodu'] ?? 0);
        $current_tedarikci_id = (int) ($_GET['current_tedarikci_id'] ?? 0);

        if (!$malzeme_kodu) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu belirtilmedi.']);
            break;
        }

        // Find the best price for this material in VALID contracts, excluding the current supplier
        // Use cerceve_sozlesmeler directly with date validity check
        $query = "SELECT birim_fiyat, para_birimi, tedarikci_adi 
                  FROM cerceve_sozlesmeler 
                  WHERE malzeme_kodu = $malzeme_kodu 
                  AND tedarikci_id != $current_tedarikci_id
                  AND (baslangic_tarihi IS NULL OR baslangic_tarihi <= CURDATE())
                  AND (bitis_tarihi IS NULL OR bitis_tarihi >= CURDATE())
                  ORDER BY birim_fiyat ASC 
                  LIMIT 1";

        $result = $connection->query($query);

        if ($result && $result->num_rows > 0) {
            $best = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $best]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Daha ucuz bir gecerli sozlesme bulunamadi.']);
        }
        break;

    case 'send_pdf_email':
        $siparis_id = (int) ($_POST['siparis_id'] ?? 0);
        $email = $_POST['email'] ?? '';
        $mesaj = $_POST['mesaj'] ?? '';

        if (!$siparis_id || !$email) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş ID ve email adresi gereklidir.']);
            break;
        }

        // Get order details
        $result = $connection->query("SELECT * FROM satinalma_siparisler WHERE siparis_id = $siparis_id");
        if (!$result || $result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş bulunamadı.']);
            break;
        }
        $order = $result->fetch_assoc();

        // Create PDF content (simplified HTML version)
        $pdf_content = generateOrderPDFContent($connection, $siparis_id);

        // Email headers
        $subject = 'Satınalma Siparişi: ' . $order['siparis_no'];
        $headers = "From: IDO Kozmetik ERP <noreply@ido-kozmetik.com>\r\n";
        $headers .= "Reply-To: noreply@ido-kozmetik.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // Email body
        $body = "
        <html>
        <head>
            <title>Satınalma Siparişi</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background: #4a0e63; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f5f5f5; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>SATINALMA SİPARİŞİ</h1>
                <h2>{$order['siparis_no']}</h2>
            </div>
            <div class='content'>
                <p>Değerli tedarikçimiz,</p>
                <p>Aşağıda bulunan satınalma siparişinin PDF eki ile gönderilmiştir.</p>
                " . (!empty($mesaj) ? "<p><strong>Ek Mesaj:</strong> " . htmlspecialchars($mesaj) . "</p>" : "") . "
                <p>Sipariş detayları için lütfen ekteki PDF dosyasına bakınız.</p>
                <p>Saygılarımla,<br>IDO Kozmetik ERP Sistemi</p>
            </div>
            <div class='footer'>
                Bu email otomatik olarak gönderilmiştir. Lütfen yanıtlamayınız.
            </div>
        </body>
        </html>";

        // For now, just log the email attempt (since mail() may not be configured)
        $log_message = "PDF email gönderildi: Sipariş #{$order['siparis_no']} -> {$email}";
        log_islem($connection, $_SESSION['kullanici_adi'], $log_message, 'EMAIL');

        // Try to send email (this may not work without proper mail configuration)
        $mail_sent = false;
        if (function_exists('mail')) {
            // Create temporary PDF file
            $temp_file = tempnam(sys_get_temp_dir(), 'order_') . '.pdf';

            // For now, create a simple text file instead of PDF
            file_put_contents($temp_file, $pdf_content);

            // Send email with attachment (simplified - would need proper mail library)
            $boundary = md5(time());
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

            $message = "--$boundary\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $message .= $body . "\r\n\r\n";
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: text/plain; name=\"satinalma_siparişi_{$order['siparis_no']}.txt\"\r\n";
            $message .= "Content-Disposition: attachment; filename=\"satinalma_siparişi_{$order['siparis_no']}.txt\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($pdf_content)) . "\r\n\r\n";
            $message .= "--$boundary--";

            $mail_sent = mail($email, $subject, $message, $headers);

            // Clean up
            unlink($temp_file);
        }

        if ($mail_sent) {
            echo json_encode(['status' => 'success', 'message' => 'PDF başarıyla gönderildi.']);
        } else {
            echo json_encode(['status' => 'warning', 'message' => 'Email gönderimi simüle edildi. Gerçek ortamda mail yapılandırması gereklidir.']);
        }
        break;

    case 'get_whatsapp_data':
        $siparis_id = (int) ($_GET['siparis_id'] ?? $_POST['siparis_id'] ?? 0);
        if (!$siparis_id) {
            echo json_encode(['status' => 'error', 'message' => 'Siparis ID belirtilmedi.']);
            break;
        }

        $order_result = $connection->query("SELECT s.*, t.telefon, t.telefon_2
            FROM satinalma_siparisler s
            LEFT JOIN tedarikciler t ON s.tedarikci_id = t.tedarikci_id
            WHERE s.siparis_id = $siparis_id
            LIMIT 1");

        if (!$order_result || $order_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Siparis bulunamadi.']);
            break;
        }

        $order = $order_result->fetch_assoc();
        $whatsapp_phone = pick_whatsapp_phone($order['telefon'] ?? '', $order['telefon_2'] ?? '');

        if ($whatsapp_phone === '') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Tedarikci icin gecerli bir cep telefonu bulunamadi. Lutfen tedarikci kartini kontrol edin.'
            ]);
            break;
        }

        $whatsapp_message = build_whatsapp_order_message($connection, $siparis_id, $order);
        $whatsapp_url = 'https://web.whatsapp.com/send?phone=' . rawurlencode($whatsapp_phone) . '&text=' . rawurlencode($whatsapp_message);

        log_islem(
            $connection,
            $_SESSION['kullanici_adi'] ?? 'Sistem',
            "{$order['siparis_no']} no'lu siparis icin WhatsApp gonderim linki olusturuldu ({$whatsapp_phone})",
            'WHATSAPP'
        );

        echo json_encode([
            'status' => 'success',
            'data' => [
                'siparis_id' => $siparis_id,
                'siparis_no' => $order['siparis_no'],
                'telefon' => $whatsapp_phone,
                'whatsapp_url' => $whatsapp_url
            ]
        ]);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}

// Helper function to generate PDF content
function generateOrderPDFContent($connection, $siparis_id)
{
    // Get order details with supplier info
    $result = $connection->query("SELECT s.*, t.adres as tedarikci_adres, t.telefon as tedarikci_telefon, t.e_posta as tedarikci_email
        FROM satinalma_siparisler s
        LEFT JOIN tedarikciler t ON s.tedarikci_id = t.tedarikci_id
        WHERE s.siparis_id = $siparis_id");

    $order = $result->fetch_assoc();

    // Get order items
    $items_result = $connection->query("SELECT * FROM satinalma_siparis_kalemleri WHERE siparis_id = $siparis_id");
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    // Format functions
    $formatCurrency = function ($value, $currency = 'TRY') {
        $num = floatval($value);
        $symbols = ['TRY' => '₺', 'TL' => '₺', 'USD' => '$', 'EUR' => '€'];
        return number_format($num, 2, ',', '.') . ' ' . ($symbols[$currency] ?? $currency);
    };

    $formatDate = function ($dateString) {
        if (!$dateString)
            return '-';
        return date('d/m/Y', strtotime($dateString));
    };

    $getDurumText = function ($durum) {
        $map = [
            'taslak' => 'Taslak',
            'onaylandi' => 'Onaylandı',
            'gonderildi' => 'Gönderildi',
            'kismen_teslim' => 'Kısmen Teslim',
            'tamamlandi' => 'Tamamlandı',
            'iptal' => 'İptal'
        ];
        return $map[$durum] ?? $durum;
    };

    // Generate text content
    $content = "SATINALMA SIPARISI\n";
    $content .= "Sipariş No: {$order['siparis_no']}\n\n";

    $content .= "FIRMA BILGILERI:\n";
    $content .= "IDO KOZMETIK\n";
    $content .= "Adres: [Firma Adresi]\n";
    $content .= "Tel: [Telefon]\n\n";

    $content .= "TEDARIKCI BILGILERI:\n";
    $content .= "Firma: {$order['tedarikci_adi']}\n";
    $content .= "Adres: " . ($order['tedarikci_adres'] ?? '') . "\n";
    $content .= "Tel: " . ($order['tedarikci_telefon'] ?? '') . "\n";
    $content .= "Email: " . ($order['tedarikci_email'] ?? '') . "\n\n";

    $content .= "SIPARIS BILGILERI:\n";
    $content .= "Sipariş Tarihi: " . $formatDate($order['siparis_tarihi']) . "\n";
    $content .= "İstenen Teslim: " . ($order['istenen_teslim_tarihi'] ? $formatDate($order['istenen_teslim_tarihi']) : '-') . "\n";
    $content .= "Durum: " . $getDurumText($order['durum']) . "\n\n";

    $content .= "SIPARIS KALEMLERI:\n";
    $content .= str_pad("Malzeme Adı", 30) . str_pad("Miktar", 15) . str_pad("Birim Fiyat", 15) . str_pad("Toplam", 15) . "\n";
    $content .= str_repeat("-", 75) . "\n";

    foreach ($items as $item) {
        $content .= str_pad(substr($item['malzeme_adi'], 0, 28), 30) . " ";
        $content .= str_pad($item['miktar'] . " " . $item['birim'], 15) . " ";
        $content .= str_pad($formatCurrency($item['birim_fiyat'], $item['para_birimi']), 15) . " ";
        $content .= str_pad($formatCurrency($item['toplam_fiyat'], $item['para_birimi']), 15) . "\n";
    }

    $content .= str_repeat("-", 75) . "\n";
    $content .= str_pad("GENEL TOPLAM:", 60) . str_pad($formatCurrency($order['toplam_tutar'], $order['para_birimi']), 15) . "\n\n";

    if (!empty($order['aciklama'])) {
        $content .= "Açıklama: {$order['aciklama']}\n\n";
    }

    $content .= "Sipariş Veren: " . ($_SESSION['kullanici_adi'] ?? 'Sistem') . "\n";
    $content .= "Tarih: " . date('d/m/Y H:i') . "\n";

    return $content;
}
function normalize_whatsapp_phone($phone)
{
    $digits = preg_replace('/\D+/', '', (string) $phone);
    if ($digits === '') {
        return '';
    }

    if (strpos($digits, '00') === 0) {
        $digits = substr($digits, 2);
    }

    if (strlen($digits) === 10) {
        $digits = '90' . $digits;
    } elseif (strpos($digits, '0') === 0 && strlen($digits) === 11) {
        $digits = '90' . substr($digits, 1);
    }

    if (strlen($digits) < 10) {
        return '';
    }

    return $digits;
}

function pick_whatsapp_phone($primary_phone, $secondary_phone)
{
    $candidates = [];

    $normalized_primary = normalize_whatsapp_phone($primary_phone);
    if ($normalized_primary !== '') {
        $candidates[] = $normalized_primary;
    }

    $normalized_secondary = normalize_whatsapp_phone($secondary_phone);
    if ($normalized_secondary !== '') {
        $candidates[] = $normalized_secondary;
    }

    if (empty($candidates)) {
        return '';
    }

    foreach ($candidates as $candidate) {
        if (preg_match('/^905\d{9}$/', $candidate)) {
            return $candidate;
        }
    }

    return $candidates[0];
}

function build_whatsapp_order_message($connection, $siparis_id, $order)
{
    $items_result = $connection->query("SELECT * FROM satinalma_siparis_kalemleri WHERE siparis_id = $siparis_id ORDER BY kalem_id ASC");
    $items = [];
    if ($items_result) {
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }
    }

    $formatCurrency = function ($value, $currency = 'TRY') {
        $num = floatval($value);
        $symbols = ['TRY' => 'TL', 'TL' => 'TL', 'USD' => 'USD', 'EUR' => 'EUR'];
        return number_format($num, 2, ',', '.') . ' ' . ($symbols[$currency] ?? $currency);
    };

    $formatDate = function ($dateString) {
        if (empty($dateString)) {
            return '-';
        }

        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return '-';
        }

        return date('d.m.Y', $timestamp);
    };

    $message_lines = [];
    $message_lines[] = '🧾 SATINALMA SIPARISI';
    $message_lines[] = '';
    $message_lines[] = '📌 Siparis Ozeti';
    $message_lines[] = '• Siparis No: ' . ($order['siparis_no'] ?? ('#' . $siparis_id));
    $message_lines[] = '• Siparis Tarihi: ' . $formatDate($order['siparis_tarihi'] ?? '');
    $message_lines[] = '• Istenen Teslim Tarihi: ' . $formatDate($order['istenen_teslim_tarihi'] ?? '');
    $message_lines[] = '• Toplam Tutar: ' . $formatCurrency($order['toplam_tutar'] ?? 0, $order['para_birimi'] ?? 'TRY');
    $message_lines[] = '';
    $message_lines[] = '📦 Siparis Kalemleri';

    if (empty($items)) {
        $message_lines[] = '• Kalem bulunamadi.';
    }

    foreach ($items as $index => $item) {
        $message_lines[] = ($index + 1) . ') ' . ($item['malzeme_adi'] ?? '-');
        $message_lines[] = '   • Miktar: ' . ($item['miktar'] ?? 0) . ' ' . ($item['birim'] ?? '');
        $message_lines[] = '   • Birim Fiyat: ' . $formatCurrency($item['birim_fiyat'] ?? 0, $item['para_birimi'] ?? ($order['para_birimi'] ?? 'TRY'));
        $message_lines[] = '   • Kalem Toplami: ' . $formatCurrency($item['toplam_fiyat'] ?? 0, $item['para_birimi'] ?? ($order['para_birimi'] ?? 'TRY'));

        if (!empty($item['aciklama'])) {
            $message_lines[] = '   • Not: ' . trim((string) $item['aciklama']);
        }

        $message_lines[] = '';
    }

    if (!empty($order['aciklama'])) {
        $message_lines[] = '🗒️ Genel Not';
        $message_lines[] = '• ' . trim((string) $order['aciklama']);
    }

    return implode("\n", $message_lines);
}
?>

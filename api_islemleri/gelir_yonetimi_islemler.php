<?php
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz yok.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_incomes':
        getIncomes();
        break;
    case 'get_income':
        getIncome();
        break;
    case 'get_total_income':
        getTotalIncome();
        break;
    case 'add_income':
        addIncome();
        break;
    case 'update_income':
        updateIncome();
        break;
    case 'delete_income':
        deleteIncome();
        break;
    case 'get_pending_orders':
        getPendingOrders();
        break;
    case 'get_pending_stats':
        getPendingStats();
        break;
    // New Installment Actions
    case 'get_customers_with_debt':
        getCustomersWithDebt();
        break;
    case 'get_customer_orders_for_plan':
        getCustomerOrdersForPlan();
        break;
    case 'create_installment_plan':
        createInstallmentPlan();
        break;
    case 'get_installment_plans':
        getInstallmentPlans();
        break;
    case 'get_plan_details':
        getPlanDetails();
        break;
    case 'pay_installment':
        payInstallment();
        break;
    case 'cancel_installment_plan':
        cancelInstallmentPlan();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getIncomes()
{
    global $connection;

    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
    $search = trim($_GET['search'] ?? '');

    if ($page < 1)
        $page = 1;
    if ($per_page < 1)
        $per_page = 10;
    $per_page = min($per_page, 200);

    $offset = ($page - 1) * $per_page;

    $searchLike = '';
    $whereClause = '';
    if ($search !== '') {
        $searchLike = "'" . $connection->real_escape_string('%' . $search . '%') . "'";
        $whereClause = " WHERE (
            kategori LIKE {$searchLike}
            OR aciklama LIKE {$searchLike}
            OR odeme_tipi LIKE {$searchLike}
            OR kaydeden_personel_ismi LIKE {$searchLike}
            OR musteri_adi LIKE {$searchLike}
            OR DATE_FORMAT(tarih, '%d.%m.%Y') LIKE {$searchLike}
            OR DATE_FORMAT(tarih, '%Y-%m-%d') LIKE {$searchLike}
            OR CAST(tutar AS CHAR) LIKE {$searchLike}
        )";
    }

    $countQuery = "SELECT COUNT(*) AS total FROM gelir_yonetimi" . $whereClause;
    $countResult = $connection->query($countQuery);
    if (!$countResult) {
        echo json_encode(['status' => 'error', 'message' => 'Toplam kayıt sayısı alınamadı: ' . $connection->error]);
        return;
    }

    $totalRow = $countResult->fetch_assoc();
    $total = isset($totalRow['total']) ? (int) $totalRow['total'] : 0;
    $countResult->free();

    $sumQuery = "SELECT IFNULL(SUM(tutar), 0) AS total_sum FROM gelir_yonetimi" . $whereClause;
    $sumResult = $connection->query($sumQuery);
    $filteredSum = 0.0;
    if ($sumResult) {
        $sumRow = $sumResult->fetch_assoc();
        $filteredSum = isset($sumRow['total_sum']) ? (float) $sumRow['total_sum'] : 0.0;
        $sumResult->free();
    }

    $maxPage = $total > 0 ? (int) ceil($total / $per_page) : 1;
    if ($total > 0 && $page > $maxPage) {
        $page = $maxPage;
        $offset = ($page - 1) * $per_page;
    }

    if ($total === 0) {
        $page = 1;
        $offset = 0;
    }

    $dataQuery = "SELECT * FROM gelir_yonetimi" . $whereClause . " ORDER BY tarih DESC, gelir_id DESC LIMIT {$per_page} OFFSET {$offset}";
    $dataResult = $connection->query($dataQuery);

    $incomes = [];
    if ($dataResult) {
        while ($row = $dataResult->fetch_assoc()) {
            $incomes[] = $row;
        }
        $dataResult->free();
    }

    $current_month_start = date('Y-m-01');
    $current_month_end = date('Y-m-t');
    $overallResult = $connection->query("SELECT IFNULL(SUM(tutar), 0) AS overall_sum, para_birimi FROM gelir_yonetimi WHERE tarih >= '$current_month_start' AND tarih <= '$current_month_end' GROUP BY para_birimi");
    $overallSums = [];
    if ($overallResult) {
        while ($row = $overallResult->fetch_assoc()) {
            $currency = $row['para_birimi'] ?: 'TL';
            $overallSums[$currency] = (float) $row['overall_sum'];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $incomes,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_sum' => $filteredSum,
        'overall_sum' => $overallSums 
    ]);
}

function getIncome()
{
    global $connection;

    $id = (int) ($_GET['id'] ?? 0);
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gelir ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM gelir_yonetimi WHERE gelir_id = $id";
    $result = $connection->query($query);

    if ($result && $result->num_rows > 0) {
        $income = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $income]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gelir bulunamadı.']);
    }
}

function getTotalIncome()
{
    global $connection;
    $query = "SELECT IFNULL(SUM(tutar), 0) AS total FROM gelir_yonetimi";
    $result = $connection->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => (float) $row['total']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Toplam gelir alınırken hata oluştu.']);
    }
}

function addIncome()
{
    global $connection;

    $tarih = $connection->real_escape_string($_POST['tarih'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $para_birimi = $connection->real_escape_string($_POST['para_birimi'] ?? 'TL');
    $kategori = $connection->real_escape_string($_POST['kategori'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $musteri_adi = $connection->real_escape_string($_POST['musteri_adi'] ?? '');
    $kasa_secimi = $connection->real_escape_string($_POST['kasa_secimi'] ?? 'TL');

    $personel_id = $_SESSION['user_id'];
    $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');

    // Optional: Link to order if provided
    $siparis_id = !empty($_POST['siparis_id']) ? (int) $_POST['siparis_id'] : 'NULL';
    $musteri_id = !empty($_POST['musteri_id']) ? (int) $_POST['musteri_id'] : 'NULL';

    // Çek bilgileri
    $cek_no = $connection->real_escape_string($_POST['cek_no'] ?? '');
    $cek_sahibi = $connection->real_escape_string($_POST['cek_sahibi'] ?? '');
    $cek_vade = $connection->real_escape_string($_POST['cek_vade'] ?? '');

    if (empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tarih, tutar, kategori, açıklama ve ödeme tipi alanları zorunludur.']);
        return;
    }

    $connection->begin_transaction();
    try {
        $cek_secimi = 'NULL';
        
        // Çek kasası seçildiyse çek kasasına kayıt ekle
        if ($kasa_secimi === 'cek_kasasi') {
            $cek_insert = "INSERT INTO cek_kasasi (cek_no, cek_tutari, cek_para_birimi, cek_sahibi, vade_tarihi, cek_tipi, cek_durumu, aciklama, kaydeden_personel)
                VALUES ('$cek_no', $tutar, '$para_birimi', '$cek_sahibi', '$cek_vade', 'alacak', 'alindi', '$aciklama', '$personel_adi')";
            if (!$connection->query($cek_insert)) throw new Exception("Çek kaydedilemedi.");
            $cek_secimi = $connection->insert_id;
            $odeme_tipi = 'Çek';
        } else {
            // Nakit kasalara bakiye ekle
            if (in_array($kasa_secimi, ['TL', 'USD', 'EUR'])) {
                $bakiye_check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$kasa_secimi'");
                if ($bakiye_check->num_rows > 0) {
                    $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$kasa_secimi'");
                } else {
                    $connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$kasa_secimi', $tutar)");
                }
            }
        }

        // Gelir kaydı ekle
        $cek_col = ($cek_secimi !== 'NULL') ? ", cek_secimi" : "";
        $cek_val = ($cek_secimi !== 'NULL') ? ", $cek_secimi" : "";
        $query = "INSERT INTO gelir_yonetimi (tarih, tutar, para_birimi, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, siparis_id, odeme_tipi, musteri_id, musteri_adi, kasa_secimi $cek_col)
              VALUES ('$tarih', $tutar, '$para_birimi', '$kategori', '$aciklama', $personel_id, '$personel_adi', $siparis_id, '$odeme_tipi', $musteri_id, '$musteri_adi', '$kasa_secimi' $cek_val)";

        if (!$connection->query($query)) throw new Exception("Gelir eklenemedi: " . $connection->error);
        $gelir_id = $connection->insert_id;

        // Kasa hareketi kaydet
        $kasa_adi = ($kasa_secimi === 'cek_kasasi') ? 'cek_kasasi' : $kasa_secimi;
        $cek_id_col = ($cek_secimi !== 'NULL') ? $cek_secimi : "NULL";
        
        $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_musteri, odeme_tipi)
            VALUES ('$tarih', 'gelir_girisi', '$kasa_adi', $cek_id_col, $tutar, '$para_birimi', $tutar, 'gelir_yonetimi', $gelir_id, '$aciklama', '$personel_adi', '$musteri_adi', '$odeme_tipi')";
        $connection->query($hareket_sql);

        if ($siparis_id > 0) {
            updateOrderPaymentStatus($siparis_id);
        }

        $connection->commit();
        log_islem($connection, $_SESSION['kullanici_adi'], "$kategori kategorisinde $tutar $para_birimi tutarında gelir eklendi", 'CREATE');
        echo json_encode(['status' => 'success', 'message' => 'Gelir başarıyla eklendi.']);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function updateIncome()
{
    global $connection;

    $gelir_id = (int) ($_POST['gelir_id'] ?? 0);
    $tarih = $connection->real_escape_string($_POST['tarih'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $para_birimi = $connection->real_escape_string($_POST['para_birimi'] ?? 'TL');
    $kategori = $connection->real_escape_string($_POST['kategori'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $musteri_adi = $connection->real_escape_string($_POST['musteri_adi'] ?? '');

    $siparis_check = $connection->query("SELECT siparis_id FROM gelir_yonetimi WHERE gelir_id = $gelir_id");
    $siparis_row = $siparis_check->fetch_assoc();
    $siparis_id = $siparis_row['siparis_id'] ?? 0;

    if (empty($gelir_id) || empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tüm alanlar zorunludur.']);
        return;
    }

    $old_query = "SELECT kategori, tutar, para_birimi FROM gelir_yonetimi WHERE gelir_id = $gelir_id";
    $old_res = $connection->query($old_query);
    $old_rec = $old_res->fetch_assoc();
    $old_cat = $old_rec['kategori'] ?? '';
    $old_amt = $old_rec['tutar'] ?? 0;
    $old_currency = $old_rec['para_birimi'] ?? 'TL';

    $query = "UPDATE gelir_yonetimi SET tarih = '$tarih', tutar = $tutar, para_birimi = '$para_birimi', kategori = '$kategori', aciklama = '$aciklama', odeme_tipi = '$odeme_tipi', musteri_adi = '$musteri_adi' WHERE gelir_id = $gelir_id";

    if ($connection->query($query)) {
        if ($siparis_id > 0) {
            updateOrderPaymentStatus($siparis_id);
        }
        log_islem($connection, $_SESSION['kullanici_adi'], "$old_cat kategorisindeki $old_amt $old_currency tutarlı gelir güncellendi", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Gelir başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gelir güncellenirken hata oluştu: ' . $connection->error]);
    }
}

function deleteIncome()
{
    global $connection;

    $gelir_id = (int) ($_POST['gelir_id'] ?? 0);
    if (empty($gelir_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gelir ID gerekli.']);
        return;
    }

    $old_query = "SELECT kategori, tutar, siparis_id, taksit_id FROM gelir_yonetimi WHERE gelir_id = $gelir_id";
    $old_res = $connection->query($old_query);
    $old_rec = $old_res->fetch_assoc();
    $old_cat = $old_rec['kategori'] ?? '';
    $old_amt = $old_rec['tutar'] ?? 0;
    $siparis_id = $old_rec['siparis_id'] ?? 0;
    $taksit_id = $old_rec['taksit_id'] ?? 0;

    $query = "DELETE FROM gelir_yonetimi WHERE gelir_id = $gelir_id";

    if ($connection->query($query)) {
        $extra_msg = "";
        if ($siparis_id > 0) {
            updateOrderPaymentStatus($siparis_id);

            $check_order = $connection->query("SELECT siparis_id, odeme_durumu FROM siparisler WHERE siparis_id = $siparis_id");
            if ($check_order->num_rows > 0) {
                $ord = $check_order->fetch_assoc();
                $extra_msg = " Bağlı Sipariş Durumu: " . $ord['odeme_durumu'] . " olarak güncellendi.";
            } else {
                $extra_msg = " DiKKAT: Bağlı sipariş bulunamadı!";
            }
        }

        // Handle linked Installment (Taksit)
        if ($taksit_id > 0) {
            // Revert installment status
            $revert_inst = "UPDATE taksit_detaylari SET durum = 'bekliyor', odenen_tutar = 0, kalan_tutar = tutar, odeme_tarihi = NULL WHERE taksit_id = $taksit_id";
            $connection->query($revert_inst);

            // Check plan status
            $plan_q = $connection->query("SELECT plan_id FROM taksit_detaylari WHERE taksit_id = $taksit_id");
            $plan_id = $plan_q->fetch_assoc()['plan_id'] ?? 0;
            
            if ($plan_id > 0) {
                $plan_check = $connection->query("SELECT durum FROM taksit_planlari WHERE plan_id = $plan_id");
                $plan_status = $plan_check->fetch_assoc()['durum'] ?? '';
                
                if ($plan_status === 'tamamlandi') {
                    $connection->query("UPDATE taksit_planlari SET durum = 'aktif' WHERE plan_id = $plan_id");
                    
                    // Also revert orders linked to this plan to pending
                    $linked_orders = $connection->query("SELECT siparis_id FROM taksit_siparis_baglantisi WHERE plan_id = $plan_id");
                    while ($lo = $linked_orders->fetch_assoc()) {
                        $lsid = $lo['siparis_id'];
                        $connection->query("UPDATE siparisler SET odeme_durumu = 'kismi_odendi' WHERE siparis_id = $lsid");
                    }
                }
                $extra_msg .= " Taksit ödenmemiş olarak işaretlendi.";
            }
        }

        log_islem($connection, $_SESSION['kullanici_adi'], "$old_cat kategorisindeki $old_amt TL tutarlı gelir silindi", 'DELETE');
        echo json_encode(['status' => 'success', 'message' => 'Gelir başarıyla silindi.' . $extra_msg]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gelir silinirken hata oluştu: ' . $connection->error]);
    }
}

function getPendingOrders()
{
    global $connection;

    $query = "SELECT s.siparis_id, s.musteri_id, s.musteri_adi, s.tarih, s.odeme_durumu, s.odenen_tutar, s.para_birimi,
              (SELECT SUM(sk.birim_fiyat * sk.adet) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) as toplam_tutar
              FROM siparisler s
              WHERE s.durum IN ('onaylandi', 'tamamlandi')
              AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
              AND s.siparis_id NOT IN (SELECT tsb.siparis_id FROM taksit_siparis_baglantisi tsb JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id WHERE tp.durum != 'iptal')
              ORDER BY s.siparis_id DESC";

    $result = $connection->query($query);
    $orders = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $total = floatval($row['toplam_tutar'] ?? 0);
            $paid = floatval($row['odenen_tutar'] ?? 0);
            $remaining = $total - $paid;

            if ($remaining > 0.01) {
                $row['kalan_tutar'] = $remaining;
                $row['toplam_tutar'] = $total;
                $orders[] = $row;
            }
        }
    }
    echo json_encode(['status' => 'success', 'data' => $orders]);
}

function updateOrderPaymentStatus($siparis_id)
{
    global $connection;

    if (empty($siparis_id))
        return;

    $income_query = "SELECT SUM(tutar) as total_paid FROM gelir_yonetimi WHERE siparis_id = $siparis_id";
    $income_curr = $connection->query($income_query)->fetch_assoc();
    $total_paid = floatval($income_curr['total_paid'] ?? 0);

    $order_total_query = "SELECT SUM(sk.birim_fiyat * sk.adet) as order_total, s.musteri_adi FROM siparis_kalemleri sk 
                          JOIN siparisler s ON s.siparis_id = sk.siparis_id 
                          WHERE sk.siparis_id = $siparis_id";
    $order_total_res = $connection->query($order_total_query);
    $order_total_row = $order_total_res->fetch_assoc();
    $order_total = floatval($order_total_row['order_total'] ?? 0);

    $new_status = 'bekliyor';
    if ($total_paid >= $order_total - 0.01) {
        $new_status = 'odendi';
    } elseif ($total_paid > 0) {
        $new_status = 'kismi_odendi';
    }

    $update = "UPDATE siparisler SET odeme_durumu = '$new_status', odenen_tutar = $total_paid WHERE siparis_id = $siparis_id";
    $connection->query($update);
}

function getPendingStats()
{
    global $connection;

    // 1. Pending Orders (Standard) - Excludes installments
    $queryOrders = "SELECT 
                COUNT(*) as count,
                SUM(
                    (SELECT IFNULL(SUM(sk.birim_fiyat * sk.adet), 0) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) - IFNULL(s.odenen_tutar, 0)
                ) as total_remaining,
                s.para_birimi
              FROM siparisler s 
              WHERE s.durum IN ('onaylandi', 'tamamlandi') 
              AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
              AND s.siparis_id NOT IN (SELECT tsb.siparis_id FROM taksit_siparis_baglantisi tsb JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id WHERE tp.durum != 'iptal')
              GROUP BY s.para_birimi";

    $resultOrders = $connection->query($queryOrders);
    $totals = [];
    $orderCount = 0;

    if ($resultOrders) {
        while ($row = $resultOrders->fetch_assoc()) {
            $orderCount += (int) $row['count'];
            $currency = $row['para_birimi'] ?: 'TL';
            if (!isset($totals[$currency])) $totals[$currency] = 0;
            $totals[$currency] += (float) $row['total_remaining'];
        }
    }

    // 2. Unpaid Installments (Active Plans) - Add to totals
    $queryInstallments = "SELECT 
                            SUM(td.kalan_tutar) as total_remaining,
                            tp.para_birimi
                          FROM taksit_detaylari td
                          JOIN taksit_planlari tp ON tp.plan_id = td.plan_id
                          WHERE tp.durum = 'aktif' AND td.durum != 'odendi'
                          GROUP BY tp.para_birimi";
    
    $resultInst = $connection->query($queryInstallments);
    if ($resultInst) {
        while ($row = $resultInst->fetch_assoc()) {
            $currency = $row['para_birimi'] ?: 'TL';
            if (!isset($totals[$currency])) $totals[$currency] = 0;
            $totals[$currency] += (float) $row['total_remaining'];
        }
    }

    // 3. New Stats: Active Plans Count
    $activePlansCount = $connection->query("SELECT COUNT(*) as c FROM taksit_planlari WHERE durum = 'aktif'")->fetch_assoc()['c'];

    // 4. New Stats: Overdue Installments
    $overdueQuery = "SELECT COUNT(*) as count, SUM(td.kalan_tutar) as total, tp.para_birimi
                     FROM taksit_detaylari td
                     JOIN taksit_planlari tp ON tp.plan_id = td.plan_id
                     WHERE tp.durum = 'aktif' AND td.durum != 'odendi' AND td.vade_tarihi < CURDATE()
                     GROUP BY tp.para_birimi";
    
    $overdueStats = ['count' => 0, 'totals' => []];
    $resOverdue = $connection->query($overdueQuery);
    if($resOverdue) {
        while($row = $resOverdue->fetch_assoc()) {
            $overdueStats['count'] += $row['count'];
            $currency = $row['para_birimi'] ?: 'TL';
            $overdueStats['totals'][$currency] = (float)$row['total'];
        }
    }

    echo json_encode([
        'status' => 'success', 
        'data' => [
            'pending_orders_count' => $orderCount,
            'total_receivables' => $totals, // Combined Orders + Installments
            'active_plans_count' => $activePlansCount,
            'overdue_installments' => $overdueStats
        ]
    ]);
}

function getCustomersWithDebt() {
    global $connection;
    // Get customers who have approved orders not fully paid
    $query = "SELECT DISTINCT s.musteri_id, s.musteri_adi 
              FROM siparisler s
              WHERE s.durum IN ('onaylandi', 'tamamlandi') 
              AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
              AND s.siparis_id NOT IN (SELECT tsb.siparis_id FROM taksit_siparis_baglantisi tsb JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id WHERE tp.durum != 'iptal')
              ORDER BY s.musteri_adi ASC";
    
    $result = $connection->query($query);
    $customers = [];
    if($result) {
        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $customers]);
}

function getCustomerOrdersForPlan() {
    global $connection;
    $musteri_id = (int)($_GET['musteri_id'] ?? 0);
    
    if(!$musteri_id) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri seçilmeli']);
        return;
    }

    $query = "SELECT s.siparis_id, s.tarih, s.para_birimi,
              (SELECT SUM(sk.birim_fiyat * sk.adet) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) as toplam_tutar,
              s.odenen_tutar
              FROM siparisler s
              WHERE s.musteri_id = $musteri_id
              AND s.durum IN ('onaylandi', 'tamamlandi')
              AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
              AND s.siparis_id NOT IN (SELECT tsb.siparis_id FROM taksit_siparis_baglantisi tsb JOIN taksit_planlari tp ON tp.plan_id = tsb.plan_id WHERE tp.durum != 'iptal')
              ORDER BY s.tarih DESC";

    $result = $connection->query($query);
    $orders = [];
    if($result) {
        while($row = $result->fetch_assoc()) {
            $total = floatval($row['toplam_tutar'] ?? 0);
            $paid = floatval($row['odenen_tutar'] ?? 0);
            $remaining = $total - $paid;
            
            if($remaining > 0.01) {
                $row['kalan_tutar'] = $remaining;
                $row['toplam_tutar'] = $total;
                $orders[] = $row;
            }
        }
    }
    echo json_encode(['status' => 'success', 'data' => $orders]);
}

function createInstallmentPlan() {
    global $connection;
    
    $musteri_id = (int)($_POST['musteri_id'] ?? 0);
    $siparis_ids = $_POST['siparis_ids'] ?? []; 
    $taksit_sayisi = (int)($_POST['taksit_sayisi'] ?? 1);
    $vade_farki_orani = floatval($_POST['vade_farki_orani'] ?? 0);
    $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? date('Y-m-d');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $kaydeden_id = $_SESSION['user_id'];
    
    if(!$musteri_id || empty($siparis_ids) || $taksit_sayisi < 1) {
        echo json_encode(['status' => 'error', 'message' => 'Eksik bilgi.']);
        return;
    }

    $total_principal = 0;
    $order_contributions = [];
    $first_order_curr = '';
    
    foreach($siparis_ids as $sid) {
        $sid = (int)$sid;
        $q = "SELECT s.para_birimi, 
              (SELECT SUM(sk.birim_fiyat * sk.adet) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) as toplam,
              s.odenen_tutar
              FROM siparisler s WHERE s.siparis_id = $sid";
        $res = $connection->query($q)->fetch_assoc();
        
        if(!$first_order_curr) $first_order_curr = $res['para_birimi'] ?: 'TL';
        if(($res['para_birimi'] ?: 'TL') !== $first_order_curr) {
             echo json_encode(['status' => 'error', 'message' => 'Seçilen siparişlerin para birimleri aynı olmalıdır.']);
             return;
        }
        
        $rem = floatval($res['toplam']) - floatval($res['odenen_tutar']);
        if($rem > 0) {
            $total_principal += $rem;
            $order_contributions[$sid] = $rem;
        }
    }
    $currency = $first_order_curr;

    $interest_amount = $total_principal * ($vade_farki_orani / 100);
    $total_payable = $total_principal + $interest_amount;
    $monthly_amount = $total_payable / $taksit_sayisi;

    $musteri_adi_q = $connection->query("SELECT musteri_adi FROM musteriler WHERE musteri_id = $musteri_id");
    $musteri_adi = $musteri_adi_q->fetch_assoc()['musteri_adi'] ?? '';

    $ins_plan = "INSERT INTO taksit_planlari (musteri_id, musteri_adi, toplam_anapara, vade_farki_orani, vade_farki_tutari, toplam_odenecek, para_birimi, taksit_sayisi, baslangic_tarihi, aciklama, kaydeden_personel_id)
                 VALUES ($musteri_id, '$musteri_adi', $total_principal, $vade_farki_orani, $interest_amount, $total_payable, '$currency', $taksit_sayisi, '$baslangic_tarihi', '$aciklama', $kaydeden_id)";
    
    if($connection->query($ins_plan)) {
        $plan_id = $connection->insert_id;
        
        foreach($order_contributions as $sid => $amt) {
            $connection->query("INSERT INTO taksit_siparis_baglantisi (plan_id, siparis_id, tutar_katkisi) VALUES ($plan_id, $sid, $amt)");
        }

        $current_date = new DateTime($baslangic_tarihi);
        
        for($i = 1; $i <= $taksit_sayisi; $i++) {
            $vade = $current_date->format('Y-m-d');
            $this_amount = round($monthly_amount, 2);
            if ($i == $taksit_sayisi) {
                $so_far = round($monthly_amount, 2) * ($taksit_sayisi - 1);
                $this_amount = $total_payable - $so_far;
            }

            $connection->query("INSERT INTO taksit_detaylari (plan_id, sira_no, vade_tarihi, tutar, kalan_tutar) 
                              VALUES ($plan_id, $i, '$vade', $this_amount, $this_amount)");
            
            $current_date->modify('+1 month');
        }

        echo json_encode(['status' => 'success', 'message' => 'Taksit planı başarıyla oluşturuldu.', 'plan_id' => $plan_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Plan oluşturulamadı: ' . $connection->error]);
    }
}

function getInstallmentPlans() {
    global $connection;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    
    $q = "SELECT * FROM taksit_planlari ORDER BY olusturma_tarihi DESC LIMIT $per_page OFFSET $offset";
    $res = $connection->query($q);
    $plans = [];
    while($row = $res->fetch_assoc()) {
        $pid = $row['plan_id'];
        $stats = $connection->query("SELECT COUNT(*) as total, SUM(CASE WHEN durum='odendi' THEN 1 ELSE 0 END) as paid_count FROM taksit_detaylari WHERE plan_id=$pid")->fetch_assoc();
        $row['odenen_taksit'] = $stats['paid_count'];
        $row['toplam_taksit_sayisi'] = $stats['total'];
        $plans[] = $row;
    }
    
    $total = $connection->query("SELECT COUNT(*) as t FROM taksit_planlari")->fetch_assoc()['t'];
    
    echo json_encode(['status' => 'success', 'data' => $plans, 'total' => $total, 'page' => $page]);
}

function getPlanDetails() {
    global $connection;
    $plan_id = (int)$_GET['plan_id'];
    
    $plan = $connection->query("SELECT * FROM taksit_planlari WHERE plan_id = $plan_id")->fetch_assoc();
    
    $inst = [];
    $res = $connection->query("SELECT * FROM taksit_detaylari WHERE plan_id = $plan_id ORDER BY sira_no ASC");
    while($r = $res->fetch_assoc()) $inst[] = $r;
    
    $orders = [];
    $res2 = $connection->query("SELECT tsb.tutar_katkisi, s.siparis_id, s.tarih 
                               FROM taksit_siparis_baglantisi tsb 
                               JOIN siparisler s ON s.siparis_id = tsb.siparis_id 
                               WHERE tsb.plan_id = $plan_id");
    while($r = $res2->fetch_assoc()) $orders[] = $r;

    echo json_encode(['status' => 'success', 'plan' => $plan, 'installments' => $inst, 'orders' => $orders]);
}

function payInstallment() {
    global $connection;
    $taksit_id = (int)$_POST['taksit_id'];
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? 'Nakit');
    
    $td = $connection->query("SELECT * FROM taksit_detaylari WHERE taksit_id = $taksit_id")->fetch_assoc();
    if(!$td || $td['kalan_tutar'] <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Taksit bulunamadı veya zaten ödenmiş.']);
        return;
    }

    $plan = $connection->query("SELECT * FROM taksit_planlari WHERE plan_id = {$td['plan_id']}")->fetch_assoc();
    
    $amount_to_pay = $td['kalan_tutar'];
    $tarih = date('Y-m-d');
    
    $upd = "UPDATE taksit_detaylari SET odenen_tutar = odenen_tutar + $amount_to_pay, kalan_tutar = 0, durum = 'odendi', odeme_tarihi = NOW() WHERE taksit_id = $taksit_id";
    
    if($connection->query($upd)) {
        $desc = "Taksit Ödemesi - Taksit {$td['sira_no']}/{$plan['taksit_sayisi']} - {$plan['musteri_adi']}";
        $personel_id = $_SESSION['user_id'];
        $personel_adi = $_SESSION['kullanici_adi'];
        
        $ins_inc = "INSERT INTO gelir_yonetimi (tarih, tutar, para_birimi, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_tipi, musteri_id, musteri_adi, taksit_id)
                    VALUES ('$tarih', $amount_to_pay, '{$plan['para_birimi']}', 'Sipariş Ödemesi', '$desc', $personel_id, '$personel_adi', '$odeme_tipi', {$plan['musteri_id']}, '{$plan['musteri_adi']}', $taksit_id)";
        $connection->query($ins_inc);
        
        $check = $connection->query("SELECT COUNT(*) as rem FROM taksit_detaylari WHERE plan_id = {$plan['plan_id']} AND durum != 'odendi'")->fetch_assoc();
        if($check['rem'] == 0) {
            $connection->query("UPDATE taksit_planlari SET durum = 'tamamlandi' WHERE plan_id = {$plan['plan_id']}");
            
             $linked = $connection->query("SELECT siparis_id FROM taksit_siparis_baglantisi WHERE plan_id = {$plan['plan_id']}");
             while($l = $linked->fetch_assoc()) {
                 $sid = $l['siparis_id'];
                 $connection->query("UPDATE siparisler SET odeme_durumu = 'odendi', odenen_tutar = (SELECT SUM(birim_fiyat*adet) FROM siparis_kalemleri WHERE siparis_id=$sid) WHERE siparis_id = $sid");
             }
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Taksit ödendi ve gelir kaydedildi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $connection->error]);
    }
}

function cancelInstallmentPlan() {
    global $connection;
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    
    if(!$plan_id) {
        echo json_encode(['status' => 'error', 'message' => 'Plan ID gerekli.']);
        return;
    }

    $upd = "UPDATE taksit_planlari SET durum = 'iptal' WHERE plan_id = $plan_id";
    if($connection->query($upd)) {
        log_islem($connection, $_SESSION['kullanici_adi'], "Taksit Planı #$plan_id iptal edildi.", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Plan iptal edildi. Siparişler tekrar tahsilat listesine düşecektir.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hata: ' . $connection->error]);
    }
}
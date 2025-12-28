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
        'overall_sum' => $overallSums // Now returning an array
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

    $personel_id = $_SESSION['user_id'];
    $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');

    // Optional: Link to order if provided
    $siparis_id = !empty($_POST['siparis_id']) ? (int) $_POST['siparis_id'] : 'NULL';
    $musteri_id = !empty($_POST['musteri_id']) ? (int) $_POST['musteri_id'] : 'NULL';
    // If siparis_id is 'NULL' string, we keep it as is for query. If it's a number, it's fine.

    if (empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tarih, tutar, kategori, açıklama ve ödeme tipi alanları zorunludur.']);
        return;
    }

    $query = "INSERT INTO gelir_yonetimi (tarih, tutar, para_birimi, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, siparis_id, odeme_tipi, musteri_id, musteri_adi)
              VALUES ('$tarih', $tutar, '$para_birimi', '$kategori', '$aciklama', $personel_id, '$personel_adi', $siparis_id, '$odeme_tipi', $musteri_id, '$musteri_adi')";

    if ($connection->query($query)) {
        if ($siparis_id > 0) {
            updateOrderPaymentStatus($siparis_id);
        }
        log_islem($connection, $_SESSION['kullanici_adi'], "$kategori kategorisinde $tutar $para_birimi tutarında gelir eklendi", 'CREATE');
        echo json_encode(['status' => 'success', 'message' => 'Gelir başarıyla eklendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gelir eklenirken hata oluştu: ' . $connection->error]);
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

    // We might need siparis_id if it was linked, to update it again.
    // Let's fetch the current siparis_id of this income before update
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

    $old_query = "SELECT kategori, tutar, siparis_id FROM gelir_yonetimi WHERE gelir_id = $gelir_id";
    $old_res = $connection->query($old_query);
    $old_rec = $old_res->fetch_assoc();
    $old_cat = $old_rec['kategori'] ?? '';
    $old_amt = $old_rec['tutar'] ?? 0;
    $siparis_id = $old_rec['siparis_id'] ?? 0;

    $query = "DELETE FROM gelir_yonetimi WHERE gelir_id = $gelir_id";

    if ($connection->query($query)) {
        $extra_msg = "";
        if ($siparis_id > 0) {
            updateOrderPaymentStatus($siparis_id);

            // Verify Order Exists
            $check_order = $connection->query("SELECT siparis_id, odeme_durumu FROM siparisler WHERE siparis_id = $siparis_id");
            if ($check_order->num_rows > 0) {
                $ord = $check_order->fetch_assoc();
                $extra_msg = " Bağlı Sipariş Durumu: " . $ord['odeme_durumu'] . " olarak güncellendi.";
            } else {
                $extra_msg = " DiKKAT: Bağlı sipariş bulunamadı!";
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

    // Fetch orders that are approved/completed but not fully paid
    // Also include 'kismi_odendi'
    // NOTE: Column is birim_fiyat, not fiyat
    $query = "SELECT s.siparis_id, s.musteri_id, s.musteri_adi, s.tarih, s.odeme_durumu, s.odenen_tutar, s.para_birimi,
              (SELECT SUM(sk.birim_fiyat * sk.adet) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) as toplam_tutar
              FROM siparisler s
              WHERE s.durum IN ('onaylandi', 'tamamlandi')
              AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
              ORDER BY s.siparis_id DESC";

    $result = $connection->query($query);
    $orders = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Calculate remaining amount
            $total = floatval($row['toplam_tutar'] ?? 0);
            $paid = floatval($row['odenen_tutar'] ?? 0);
            $remaining = $total - $paid;

            if ($remaining > 0.01) { // Floating point tolerance
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

    // 1. Calculate Total Income for this Order
    $income_query = "SELECT SUM(tutar) as total_paid FROM gelir_yonetimi WHERE siparis_id = $siparis_id";
    $income_curr = $connection->query($income_query)->fetch_assoc();
    $total_paid = floatval($income_curr['total_paid'] ?? 0);

    // 2. Calculate Order Total
    $order_total_query = "SELECT SUM(sk.birim_fiyat * sk.adet) as order_total, s.musteri_adi FROM siparis_kalemleri sk 
                          JOIN siparisler s ON s.siparis_id = sk.siparis_id 
                          WHERE sk.siparis_id = $siparis_id";
    $order_total_res = $connection->query($order_total_query);
    $order_total_row = $order_total_res->fetch_assoc();
    $order_total = floatval($order_total_row['order_total'] ?? 0);
    $musteri_adi = $order_total_row['musteri_adi'] ?? '';

    // 3. Determine Status
    $new_status = 'bekliyor';
    if ($total_paid >= $order_total - 0.01) { // Tolerance
        $new_status = 'odendi';
    } elseif ($total_paid > 0) {
        $new_status = 'kismi_odendi';
    }

    // 4. Update Order
    $update = "UPDATE siparisler SET odeme_durumu = '$new_status', odenen_tutar = $total_paid WHERE siparis_id = $siparis_id";
    $connection->query($update);

    // Log
    // log_islem is handled by the caller usually, but internal update logging is good too
}

function getPendingStats()
{
    global $connection;

    // Calculate total pending amount and count
    // We strictly need: Approved/Completed orders that are NOT fully paid.
    $query = "SELECT 
                COUNT(*) as count,
                SUM(
                    (SELECT IFNULL(SUM(sk.birim_fiyat * sk.adet), 0) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) - IFNULL(s.odenen_tutar, 0)
                ) as total_remaining,
                s.para_birimi
              FROM siparisler s 
              WHERE s.durum IN ('onaylandi', 'tamamlandi') 
              AND (s.odeme_durumu IS NULL OR s.odeme_durumu != 'odendi')
              GROUP BY s.para_birimi";

    $result = $connection->query($query);
    $data = ['count' => 0, 'totals' => []];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data['count'] += (int) $row['count'];
            $currency = $row['para_birimi'] ?: 'TL';
            $data['totals'][$currency] = (float) $row['total_remaining'];
        }
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
}
?>
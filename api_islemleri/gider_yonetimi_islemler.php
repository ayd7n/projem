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
    case 'get_expenses':
        getExpenses();
        break;
    case 'get_expense':
        getExpense();
        break;
    case 'get_total_expenses':
        getTotalExpenses();
        break;
    case 'add_expense':
        addExpense();
        break;
    case 'update_expense':
        updateExpense();
        break;
    case 'delete_expense':
        deleteExpense();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getExpenses() {
    global $connection;

    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
    $search = trim($_GET['search'] ?? '');

    if ($page < 1) {
        $page = 1;
    }

    if ($per_page < 1) {
        $per_page = 10;
    }
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
            OR fatura_no LIKE {$searchLike}
            OR kaydeden_personel_ismi LIKE {$searchLike}
            OR CAST(kaydeden_personel_id AS CHAR) LIKE {$searchLike}
            OR DATE_FORMAT(tarih, '%d.%m.%Y') LIKE {$searchLike}
            OR DATE_FORMAT(tarih, '%Y-%m-%d') LIKE {$searchLike}
            OR CAST(tutar AS CHAR) LIKE {$searchLike}
            OR odeme_yapilan_firma LIKE {$searchLike}
        )";
    }

    $countQuery = "SELECT COUNT(*) AS total FROM gider_yonetimi" . $whereClause;
    $countResult = $connection->query($countQuery);
    if (!$countResult) {
        echo json_encode(['status' => 'error', 'message' => 'Toplam kayıt sayısı alınamadı: ' . $connection->error]);
        return;
    }

    $totalRow = $countResult->fetch_assoc();
    $total = isset($totalRow['total']) ? (int) $totalRow['total'] : 0;
    $countResult->free();

    $sumQuery = "SELECT IFNULL(SUM(tutar), 0) AS total_sum FROM gider_yonetimi" . $whereClause;
    $sumResult = $connection->query($sumQuery);
    if (!$sumResult) {
        echo json_encode(['status' => 'error', 'message' => 'Toplam tutar alınamadı: ' . $connection->error]);
        return;
    }

    $sumRow = $sumResult->fetch_assoc();
    $filteredSum = isset($sumRow['total_sum']) ? (float) $sumRow['total_sum'] : 0.0;
    $sumResult->free();

    $maxPage = $total > 0 ? (int) ceil($total / $per_page) : 1;
    if ($total > 0 && $page > $maxPage) {
        $page = $maxPage;
        $offset = ($page - 1) * $per_page;
    }

    if ($total === 0) {
        $page = 1;
        $offset = 0;
    }

    $perPageSql = (int) $per_page;
    $offsetSql = (int) $offset;

    $dataQuery = "SELECT * FROM gider_yonetimi" . $whereClause . " ORDER BY gider_id DESC LIMIT {$perPageSql} OFFSET {$offsetSql}";
    $dataResult = $connection->query($dataQuery);
    if (!$dataResult) {
        echo json_encode(['status' => 'error', 'message' => 'Giderler alınamadı: ' . $connection->error]);
        return;
    }

    $expenses = [];
    while ($row = $dataResult->fetch_assoc()) {
        $expenses[] = $row;
    }
    $dataResult->free();

    $current_month_start = date('Y-m-01');
    $current_month_end = date('Y-m-t');
    $overallResult = $connection->query("SELECT IFNULL(SUM(tutar), 0) AS overall_sum FROM gider_yonetimi WHERE tarih >= '$current_month_start' AND tarih <= '$current_month_end'");
    if ($overallResult && $overallRow = $overallResult->fetch_assoc()) {
        $overallSum = (float) $overallRow['overall_sum'];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $expenses,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_sum' => $filteredSum,
        'overall_sum' => $overallSum
    ]);
}

function getExpense() {
    global $connection;

    $id = (int)($_GET['id'] ?? 0);
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gider ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM gider_yonetimi WHERE gider_id = $id";
    $result = $connection->query($query);

    if ($result && $result->num_rows > 0) {
        $expense = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $expense]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider bulunamadı.']);
    }
}

function getTotalExpenses() {
    global $connection;

    $query = "SELECT IFNULL(SUM(tutar), 0) AS total FROM gider_yonetimi";
    $result = $connection->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => (float)$row['total']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Toplam giderler alınırken hata oluştu.']);
    }
}

function addExpense() {
    global $connection;

    $tarih = $connection->real_escape_string($_POST['tarih'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $kategori = $connection->real_escape_string($_POST['kategori'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $fatura_no = $connection->real_escape_string($_POST['fatura_no'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $personel_id = $_SESSION['user_id'];
    $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');
    $odeme_yapilan_firma = $connection->real_escape_string($_POST['odeme_yapilan_firma'] ?? '');
    $kasa_secimi = $connection->real_escape_string($_POST['kasa_secimi'] ?? 'TL');
    $cek_secimi = !empty($_POST['cek_secimi']) ? intval($_POST['cek_secimi']) : null;

    if (empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tarih, tutar, kategori, açıklama ve ödeme tipi alanları zorunludur.']);
        return;
    }

    $connection->begin_transaction();
    try {
        // Çek kasası seçildiyse çek durumunu güncelle
        if ($kasa_secimi === 'cek_kasasi' && $cek_secimi) {
            $cek_update = "UPDATE cek_kasasi SET cek_durumu = 'kullanildi', cek_kullanim_tarihi = NOW() WHERE cek_id = $cek_secimi";
            if (!$connection->query($cek_update)) throw new Exception("Çek durumu güncellenemedi.");
            $odeme_tipi = 'Çek';
        } else {
            // Nakit kasalardan bakiye düş
            if (in_array($kasa_secimi, ['TL', 'USD', 'EUR'])) {
                $bakiye_check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$kasa_secimi'");
                if ($bakiye_check->num_rows > 0) {
                    $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$kasa_secimi'");
                }
            }
        }

        // Gider kaydı ekle
        $cek_col = $cek_secimi ? ", cek_secimi" : "";
        $cek_val = $cek_secimi ? ", $cek_secimi" : "";
        $query = "INSERT INTO gider_yonetimi (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi, odeme_yapilan_firma, kasa_secimi $cek_col) VALUES ('$tarih', $tutar, '$kategori', '$aciklama', $personel_id, '$personel_adi', '$fatura_no', '$odeme_tipi', '$odeme_yapilan_firma', '$kasa_secimi' $cek_val)";

        if (!$connection->query($query)) throw new Exception("Gider eklenemedi: " . $connection->error);
        $gider_id = $connection->insert_id;

        // Kasa hareketi kaydet
        $kasa_adi = ($kasa_secimi === 'cek_kasasi') ? 'cek_kasasi' : $kasa_secimi;
        $para_birimi = in_array($kasa_secimi, ['TL', 'USD', 'EUR']) ? $kasa_secimi : 'TL';
        $cek_id_col = $cek_secimi ? $cek_secimi : "NULL";

        // Döviz kurlarını çek
        $rates = ['TL' => 1, 'USD' => 1, 'EUR' => 1];
        $rate_query = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
        while ($row = $rate_query->fetch_assoc()) {
            if ($row['ayar_anahtar'] === 'dolar_kuru') $rates['USD'] = floatval($row['ayar_deger']);
            if ($row['ayar_anahtar'] === 'euro_kuru') $rates['EUR'] = floatval($row['ayar_deger']);
        }
        
        $tl_karsiligi = $tutar * ($rates[$para_birimi] ?? 1);
        
        $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_firma, odeme_tipi)
            VALUES ('$tarih', 'gider_cikisi', '$kasa_adi', $cek_id_col, $tutar, '$para_birimi', $tl_karsiligi, 'gider_yonetimi', $gider_id, '$aciklama', '$personel_adi', '$odeme_yapilan_firma', '$odeme_tipi')";
        $connection->query($hareket_sql);

        $connection->commit();
        log_islem($connection, $_SESSION['kullanici_adi'], "$kategori kategorisinde $tutar TL tutarında gider eklendi", 'CREATE');
        echo json_encode(['status' => 'success', 'message' => 'Gider başarıyla eklendi.']);
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function updateExpense() {
    global $connection;

    $gider_id = (int)($_POST['gider_id'] ?? 0);
    $tarih = $connection->real_escape_string($_POST['tarih'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $kategori = $connection->real_escape_string($_POST['kategori'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $fatura_no = $connection->real_escape_string($_POST['fatura_no'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $odeme_yapilan_firma = $connection->real_escape_string($_POST['odeme_yapilan_firma'] ?? '');

    if (empty($gider_id) || empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tüm alanlar zorunludur.']);
        return;
    }

    // Eski gider bilgilerini al
    $old_expense_query = "SELECT kategori, tutar FROM gider_yonetimi WHERE gider_id = $gider_id";
    $old_expense_result = $connection->query($old_expense_query);
    $old_expense = $old_expense_result->fetch_assoc();
    $old_category = $old_expense['kategori'] ?? 'Bilinmeyen Kategori';
    $old_amount = $old_expense['tutar'] ?? 0;

    $query = "UPDATE gider_yonetimi SET tarih = '$tarih', tutar = $tutar, kategori = '$kategori', aciklama = '$aciklama', fatura_no = '$fatura_no', odeme_tipi = '$odeme_tipi', odeme_yapilan_firma = '$odeme_yapilan_firma' WHERE gider_id = $gider_id";

    if ($connection->query($query)) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$old_category kategorisindeki $old_amount TL tutarlı gider güncellendi", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Gider başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider güncellenirken hata oluştu: ' . $connection->error]);
    }
}

function deleteExpense() {
    global $connection;

    $gider_id = (int)($_POST['gider_id'] ?? 0);
    if (empty($gider_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gider ID gerekli.']);
        return;
    }

    // Silinen gider bilgilerini al
    $old_expense_query = "SELECT kategori, tutar FROM gider_yonetimi WHERE gider_id = $gider_id";
    $old_expense_result = $connection->query($old_expense_query);
    $old_expense = $old_expense_result->fetch_assoc();
    $deleted_category = $old_expense['kategori'] ?? 'Bilinmeyen Kategori';
    $deleted_amount = $old_expense['tutar'] ?? 0;

    $query = "DELETE FROM gider_yonetimi WHERE gider_id = $gider_id";

    if ($connection->query($query)) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_category kategorisindeki $deleted_amount TL tutarlı gider silindi", 'DELETE');
        echo json_encode(['status' => 'success', 'message' => 'Gider başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider silinirken hata oluştu: ' . $connection->error]);
    }
}
?>

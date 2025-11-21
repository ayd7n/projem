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

    if (empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tarih, tutar, kategori, açıklama ve ödeme tipi alanları zorunludur.']);
        return;
    }

    $query = "INSERT INTO gider_yonetimi (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi, odeme_yapilan_firma) VALUES ('$tarih', $tutar, '$kategori', '$aciklama', $personel_id, '$personel_adi', '$fatura_no', '$odeme_tipi', '$odeme_yapilan_firma')";

    if ($connection->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Gider başarıyla eklendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider eklenirken hata oluştu: ' . $connection->error]);
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

    $query = "UPDATE gider_yonetimi SET tarih = '$tarih', tutar = $tutar, kategori = '$kategori', aciklama = '$aciklama', fatura_no = '$fatura_no', odeme_tipi = '$odeme_tipi', odeme_yapilan_firma = '$odeme_yapilan_firma' WHERE gider_id = $gider_id";

    if ($connection->query($query)) {
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

    $query = "DELETE FROM gider_yonetimi WHERE gider_id = $gider_id";

    if ($connection->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Gider başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider silinirken hata oluştu: ' . $connection->error]);
    }
}
?>

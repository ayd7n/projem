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

    $query = "SELECT * FROM gider_yonetimi ORDER BY tarih DESC, gider_id DESC";
    $result = $connection->query($query);

    $expenses = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $expenses[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $expenses]);
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

    if (empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tarih, tutar, kategori, açıklama ve ödeme tipi alanları zorunludur.']);
        return;
    }

    $query = "INSERT INTO gider_yonetimi (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi) VALUES ('$tarih', $tutar, '$kategori', '$aciklama', $personel_id, '$personel_adi', '$fatura_no', '$odeme_tipi')";

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

    if (empty($gider_id) || empty($tarih) || $tutar <= 0 || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tüm alanlar zorunludur.']);
        return;
    }

    $query = "UPDATE gider_yonetimi SET tarih = '$tarih', tutar = $tutar, kategori = '$kategori', aciklama = '$aciklama', fatura_no = '$fatura_no', odeme_tipi = '$odeme_tipi' WHERE gider_id = $gider_id";

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

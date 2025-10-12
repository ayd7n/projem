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

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gider ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM gider_yonetimi WHERE gider_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $expense = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $expense]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider bulunamadı.']);
    }

    $stmt->close();
}

function addExpense() {
    global $connection;

    $tarih = $_POST['tarih'] ?? '';
    $tutar = $_POST['tutar'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';
    $fatura_no = $_POST['fatura_no'] ?? '';
    $odeme_tipi = $_POST['odeme_tipi'] ?? '';
    $personel_id = $_SESSION['id'];
    $personel_adi = $_SESSION['kullanici_adi'];

    if (empty($tarih) || empty($tutar) || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tarih, tutar, kategori, açıklama ve ödeme tipi alanları zorunludur.']);
        return;
    }

    $query = "INSERT INTO gider_yonetimi (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, fatura_no, odeme_tipi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sdssssis', $tarih, $tutar, $kategori, $aciklama, $personel_id, $personel_adi, $fatura_no, $odeme_tipi);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Gider başarıyla eklendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider eklenirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function updateExpense() {
    global $connection;

    $gider_id = $_POST['gider_id'] ?? '';
    $tarih = $_POST['tarih'] ?? '';
    $tutar = $_POST['tutar'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';
    $fatura_no = $_POST['fatura_no'] ?? '';
    $odeme_tipi = $_POST['odeme_tipi'] ?? '';

    if (empty($gider_id) || empty($tarih) || empty($tutar) || empty($kategori) || empty($aciklama) || empty($odeme_tipi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tüm alanlar zorunludur.']);
        return;
    }

    $query = "UPDATE gider_yonetimi SET tarih = ?, tutar = ?, kategori = ?, aciklama = ?, fatura_no = ?, odeme_tipi = ? WHERE gider_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sdssssi', $tarih, $tutar, $kategori, $aciklama, $fatura_no, $odeme_tipi, $gider_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Gider başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider güncellenirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function deleteExpense() {
    global $connection;

    $gider_id = $_POST['gider_id'] ?? '';
    if (empty($gider_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Gider ID gerekli.']);
        return;
    }

    $query = "DELETE FROM gider_yonetimi WHERE gider_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $gider_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Gider başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gider silinirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}
?>

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
    case 'get_customers':
        getCustomers();
        break;
    case 'get_customer':
        getCustomer();
        break;
    case 'add_customer':
        addCustomer();
        break;
    case 'update_customer':
        updateCustomer();
        break;
    case 'delete_customer':
        deleteCustomer();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getCustomers() {
    global $connection;

    $query = "SELECT * FROM musteriler ORDER BY musteri_adi";
    $result = $connection->query($query);

    $customers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $customers]);
}

function getCustomer() {
    global $connection;

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM musteriler WHERE musteri_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $customer]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri bulunamadı.']);
    }

    $stmt->close();
}

function addCustomer() {
    global $connection;

    $musteri_adi = $_POST['musteri_adi'] ?? '';
    $vergi_no_tc = $_POST['vergi_no_tc'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $e_posta = $_POST['e_posta'] ?? '';
    $sifre = $_POST['sifre'] ?? '';
    $aciklama_notlar = $_POST['aciklama_notlar'] ?? '';
    $giris_yetkisi = isset($_POST['giris_yetkisi']) ? 1 : 0;

    if (empty($musteri_adi) || empty($sifre)) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri adı ve şifre alanları zorunludur.']);
        return;
    }

    // Hash the password
    $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);

    $query = "INSERT INTO musteriler (musteri_adi, vergi_no_tc, adres, telefon, e_posta, sistem_sifresi, aciklama_notlar, giris_yetkisi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssssssi', $musteri_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $hashed_password, $aciklama_notlar, $giris_yetkisi);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Müşteri başarıyla oluşturuldu.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri oluşturulurken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function updateCustomer() {
    global $connection;

    $musteri_id = $_POST['musteri_id'] ?? '';
    $musteri_adi = $_POST['musteri_adi'] ?? '';
    $vergi_no_tc = $_POST['vergi_no_tc'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $e_posta = $_POST['e_posta'] ?? '';
    $aciklama_notlar = $_POST['aciklama_notlar'] ?? '';
    $giris_yetkisi = isset($_POST['giris_yetkisi']) ? 1 : 0;

    if (empty($musteri_id) || empty($musteri_adi)) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri ID ve müşteri adı alanları zorunludur.']);
        return;
    }

    // Update password if provided, otherwise don't update password field
    if (!empty($_POST['sifre'])) {
        $hashed_password = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
        $query = "UPDATE musteriler SET musteri_adi = ?, vergi_no_tc = ?, adres = ?, telefon = ?, e_posta = ?, sistem_sifresi = ?, aciklama_notlar = ?, giris_yetkisi = ? WHERE musteri_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('sssssssi', $musteri_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $hashed_password, $aciklama_notlar, $giris_yetkisi, $musteri_id);
    } else {
        $query = "UPDATE musteriler SET musteri_adi = ?, vergi_no_tc = ?, adres = ?, telefon = ?, e_posta = ?, aciklama_notlar = ?, giris_yetkisi = ? WHERE musteri_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ssssssi', $musteri_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $aciklama_notlar, $giris_yetkisi, $musteri_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Müşteri başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri güncellenirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function deleteCustomer() {
    global $connection;

    $musteri_id = $_POST['musteri_id'] ?? '';
    if (empty($musteri_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri ID gerekli.']);
        return;
    }

    $query = "DELETE FROM musteriler WHERE musteri_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $musteri_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Müşteri başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Müşteri silinirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}
?>

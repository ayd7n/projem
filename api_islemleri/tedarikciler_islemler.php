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
    case 'get_suppliers':
        getSuppliers();
        break;
    case 'get_supplier':
        getSupplier();
        break;
    case 'add_supplier':
        addSupplier();
        break;
    case 'update_supplier':
        updateSupplier();
        break;
    case 'delete_supplier':
        deleteSupplier();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getSuppliers() {
    global $connection;

    $query = "SELECT * FROM tedarikciler ORDER BY tedarikci_adi";
    $result = $connection->query($query);

    $suppliers = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $suppliers]);
}

function getSupplier() {
    global $connection;

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ID gerekli.']);
        return;
    }

    $query = "SELECT * FROM tedarikciler WHERE tedarikci_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $supplier = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $supplier]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi bulunamadı.']);
    }

    $stmt->close();
}

function addSupplier() {
    global $connection;

    $tedarikci_adi = $_POST['tedarikci_adi'] ?? '';
    $vergi_no_tc = $_POST['vergi_no_tc'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $e_posta = $_POST['e_posta'] ?? '';
    $yetkili_kisi = $_POST['yetkili_kisi'] ?? '';
    $aciklama_notlar = $_POST['aciklama_notlar'] ?? '';

    if (empty($tedarikci_adi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi adı zorunludur.']);
        return;
    }

    $query = "INSERT INTO tedarikciler (tedarikci_adi, vergi_no_tc, adres, telefon, e_posta, yetkili_kisi, aciklama_notlar) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssssss', $tedarikci_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $yetkili_kisi, $aciklama_notlar);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Tedarikçi başarıyla oluşturuldu.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi oluşturulurken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function updateSupplier() {
    global $connection;

    $tedarikci_id = $_POST['tedarikci_id'] ?? '';
    $tedarikci_adi = $_POST['tedarikci_adi'] ?? '';
    $vergi_no_tc = $_POST['vergi_no_tc'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $e_posta = $_POST['e_posta'] ?? '';
    $yetkili_kisi = $_POST['yetkili_kisi'] ?? '';
    $aciklama_notlar = $_POST['aciklama_notlar'] ?? '';

    if (empty($tedarikci_id) || empty($tedarikci_adi)) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ID ve tedarikçi adı alanları zorunludur.']);
        return;
    }

    $query = "UPDATE tedarikciler SET tedarikci_adi = ?, vergi_no_tc = ?, adres = ?, telefon = ?, e_posta = ?, yetkili_kisi = ?, aciklama_notlar = ? WHERE tedarikci_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('sssssssi', $tedarikci_adi, $vergi_no_tc, $adres, $telefon, $e_posta, $yetkili_kisi, $aciklama_notlar, $tedarikci_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Tedarikçi başarıyla güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi güncellenirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}

function deleteSupplier() {
    global $connection;

    $tedarikci_id = $_POST['tedarikci_id'] ?? '';
    if (empty($tedarikci_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi ID gerekli.']);
        return;
    }

    $query = "DELETE FROM tedarikciler WHERE tedarikci_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $tedarikci_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Tedarikçi başarıyla silindi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi silinirken hata oluştu: ' . $connection->error]);
    }

    $stmt->close();
}
?>

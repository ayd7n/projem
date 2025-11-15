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

    if (!yetkisi_var('page:view:tedarikciler')) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçileri görüntüleme yetkiniz yok.']);
        return;
    }

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $offset = ($page - 1) * $limit;

    $where_clause = "";
    if (!empty($search)) {
        $search_escaped = $connection->real_escape_string($search);
        $search_param = '%' . $search_escaped . '%';
        $where_clause = "WHERE tedarikci_adi LIKE '$search_param' OR e_posta LIKE '$search_param' OR yetkili_kisi LIKE '$search_param'";
    }

    $count_query = "SELECT COUNT(*) as total FROM tedarikciler " . $where_clause;
    $result = $connection->query($count_query);
    $total_suppliers = $result->fetch_assoc()['total'];

    $total_pages = $limit > 0 ? ceil($total_suppliers / $limit) : 0;

    $query = "SELECT * FROM tedarikciler " . $where_clause . " ORDER BY tedarikci_adi LIMIT $limit OFFSET $offset";
    $result = $connection->query($query);

    $suppliers = [];
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }

    $response = [
        'status' => 'success',
        'data' => $suppliers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_suppliers' => $total_suppliers,
            'limit' => $limit
        ]
    ];

    echo json_encode($response);
}

function getSupplier() {
    global $connection;

    if (!yetkisi_var('page:view:tedarikciler')) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi görüntüleme yetkiniz yok.']);
        return;
    }

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

    if (!yetkisi_var('action:tedarikciler:create')) {
        echo json_encode(['status' => 'error', 'message' => 'Yeni tedarikçi ekleme yetkiniz yok.']);
        return;
    }

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

    if (!yetkisi_var('action:tedarikciler:edit')) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi bilgilerini düzenleme yetkiniz yok.']);
        return;
    }

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

    if (!yetkisi_var('action:tedarikciler:delete')) {
        echo json_encode(['status' => 'error', 'message' => 'Tedarikçi silme yetkiniz yok.']);
        return;
    }

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

<?php
include '../config.php';

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

switch ($action) {
    case 'get_all_orders':
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $search = $_GET['search'] ?? '';
        $durum_filter = $_GET['durum'] ?? '';

        $offset = ($page - 1) * $limit;

        // Build WHERE clause
        $where_clauses = [];
        if (!empty($search)) {
            $search_escaped = $connection->real_escape_string($search);
            $where_clauses[] = "(malzeme_ismi LIKE '%$search_escaped%' OR tedarikci_ismi LIKE '%$search_escaped%' OR aciklama LIKE '%$search_escaped%')";
        }
        if (!empty($durum_filter)) {
            $durum_escaped = $connection->real_escape_string($durum_filter);
            $where_clauses[] = "durum = '$durum_escaped'";
        }

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM malzeme_siparisler $where_sql";
        $count_result = $connection->query($count_query);
        $total = $count_result->fetch_assoc()['total'] ?? 0;

        // Get paginated orders
        $query = "SELECT * FROM malzeme_siparisler $where_sql ORDER BY siparis_id DESC LIMIT $limit OFFSET $offset";
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
            'total' => $total,
            'page' => $page,
            'pages' => ceil($total / $limit)
        ]);
        break;

    case 'get_order':
        $id = $_GET['id'] ?? $_POST['id'] ?? 0;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş ID belirtilmedi.']);
            break;
        }

        $query = "SELECT * FROM malzeme_siparisler WHERE siparis_id = $id";
        $result = $connection->query($query);

        if ($result && $result->num_rows > 0) {
            $order = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $order]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş bulunamadı.']);
        }
        break;

    case 'add_order':
        $malzeme_kodu = $_POST['malzeme_kodu'] ?? '';
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        $miktar = $_POST['miktar'] ?? 0;
        $siparis_tarihi = date('Y-m-d');
        $teslim_tarihi = $_POST['teslim_tarihi'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';

        // Validate date formats
        $date2 = DateTime::createFromFormat('Y-m-d', $teslim_tarihi);

        if (!$date2 || $date2->format('Y-m-d') !== $teslim_tarihi) {
            echo json_encode(['status' => 'error', 'message' => 'Teslim tarihi doğru formatta değil (YYYY-MM-DD).']);
            break;
        }

        // Validation
        if (!$malzeme_kodu || !$tedarikci_id || !is_numeric($miktar) || $miktar <= 0 || !$teslim_tarihi) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Get malzeme name
        $malzeme_stmt = $connection->prepare("SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?");
        $malzeme_stmt->bind_param("i", $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        if (!$malzeme_result || $malzeme_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme.']);
            break;
        }
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        $malzeme_stmt->close();

        // Get tedarikci name
        $tedarikci_stmt = $connection->prepare("SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?");
        $tedarikci_stmt->bind_param("i", $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        if (!$tedarikci_result || $tedarikci_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
            break;
        }
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_ismi = $tedarikci['tedarikci_adi'];
        $tedarikci_stmt->close();

        $kaydeden_personel_id = $_SESSION['user_id'];
        $kaydeden_personel_adi = $_SESSION['kullanici_adi'] ?? '';

        $stmt = $connection->prepare("INSERT INTO malzeme_siparisler (malzeme_kodu, malzeme_ismi, tedarikci_id, tedarikci_ismi, miktar, siparis_tarihi, teslim_tarihi, aciklama, kaydeden_personel_id, kaydeden_personel_adi)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisdsssis", $malzeme_kodu, $malzeme_ismi, $tedarikci_id, $tedarikci_ismi, $miktar, $siparis_tarihi, $teslim_tarihi, $aciklama, $kaydeden_personel_id, $kaydeden_personel_adi);

        if ($stmt->execute()) {
            log_islem($connection, $_SESSION['kullanici_adi'], "$tedarikci_ismi tedarikçisine $malzeme_ismi malzemesi için sipariş oluşturuldu", 'CREATE');
            echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla oluşturuldu.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş oluşturulurken hata oluştu: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'update_order':
        $siparis_id = $_POST['siparis_id'] ?? 0;
        $malzeme_kodu = $_POST['malzeme_kodu'] ?? '';
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        $miktar = $_POST['miktar'] ?? 0;
        // siparis_tarihi is read-only on update
        $teslim_tarihi = $_POST['teslim_tarihi'] ?? '';
        $durum = $_POST['durum'] ?? 'siparis_verildi';
        $aciklama = $_POST['aciklama'] ?? '';

        // Validate date formats
        $date2 = DateTime::createFromFormat('Y-m-d', $teslim_tarihi);

        if ($teslim_tarihi && (!$date2 || $date2->format('Y-m-d') !== $teslim_tarihi)) {
            echo json_encode(['status' => 'error', 'message' => 'Teslim tarihi doğru formatta değil (YYYY-MM-DD).']);
            break;
        }

        // Validation
        if (!$siparis_id || !$malzeme_kodu || !$tedarikci_id || !is_numeric($miktar) || $miktar <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Get malzeme name
        $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = $malzeme_kodu";
        $malzeme_result = $connection->query($malzeme_query);
        if (!$malzeme_result || $malzeme_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme.']);
            break;
        }
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $connection->real_escape_string($malzeme['malzeme_ismi']);

        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = $tedarikci_id";
        $tedarikci_result = $connection->query($tedarikci_query);
        if (!$tedarikci_result || $tedarikci_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
            break;
        }
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_ismi = $connection->real_escape_string($tedarikci['tedarikci_adi']);

        $aciklama_escaped = $connection->real_escape_string($aciklama);
        $teslim_tarihi_sql = $teslim_tarihi ? "'$teslim_tarihi'" : "NULL";

        $query = "UPDATE malzeme_siparisler SET
                  malzeme_kodu = $malzeme_kodu,
                  malzeme_ismi = '$malzeme_ismi',
                  tedarikci_id = $tedarikci_id,
                  tedarikci_ismi = '$tedarikci_ismi',
                  miktar = " . floatval($miktar) . ",
                  teslim_tarihi = $teslim_tarihi_sql,
                  durum = '$durum',
                  aciklama = '$aciklama_escaped'
                  WHERE siparis_id = $siparis_id";

        if ($connection->query($query)) {
            log_islem($connection, $_SESSION['kullanici_adi'], "Sipariş #$siparis_id güncellendi", 'UPDATE');
            echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla güncellendi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş güncellenirken hata oluştu: ' . $connection->error]);
        }
        break;

    case 'delete_order':
        $siparis_id = $_POST['siparis_id'] ?? 0;

        if (!$siparis_id) {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş ID belirtilmedi.']);
            break;
        }

        // Get order info for logging
        $order_query = "SELECT malzeme_ismi, tedarikci_ismi FROM malzeme_siparisler WHERE siparis_id = $siparis_id";
        $order_result = $connection->query($order_query);
        if (!$order_result || $order_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Silinecek sipariş bulunamadı.']);
            break;
        }
        $order = $order_result->fetch_assoc();

        $query = "DELETE FROM malzeme_siparisler WHERE siparis_id = $siparis_id";

        if ($connection->query($query)) {
            log_islem($connection, $_SESSION['kullanici_adi'], "{$order['tedarikci_ismi']} tedarikçisine ait {$order['malzeme_ismi']} malzemesi siparişi silindi", 'DELETE');
            echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla silindi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sipariş silinirken hata oluştu: ' . $connection->error]);
        }
        break;

    case 'get_materials':
        // Get all materials for dropdown
        $query = "SELECT malzeme_kodu, malzeme_ismi FROM malzemeler ORDER BY malzeme_ismi";
        $result = $connection->query($query);

        $materials = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $materials[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $materials]);
        break;

    case 'get_suppliers_for_material':
        $material_code = $_GET['material_code'] ?? '';

        if (!$material_code) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme kodu belirtilmedi.']);
            break;
        }

        // Get suppliers from cerceve_sozlesmeler table who have contracts with this material
        $query = "SELECT DISTINCT t.tedarikci_id, t.tedarikci_adi as tedarikci_ismi 
                  FROM tedarikciler t
                  INNER JOIN cerceve_sozlesmeler cs ON t.tedarikci_id = cs.tedarikci_id
                  WHERE cs.malzeme_kodu = $material_code
                  ORDER BY t.tedarikci_adi";

        $result = $connection->query($query);

        $suppliers = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $suppliers[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $suppliers]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}
?>
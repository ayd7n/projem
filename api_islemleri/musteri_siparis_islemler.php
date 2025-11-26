<?php
include '../config.php';

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'musteri') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim!']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'get_orders':
        getOrders();
        break;
    case 'cancel_order':
        cancelOrder();
        break;
    case 'update_order':
        updateOrder();
        break;
    case 'get_order_items':
        getOrderItems();
        break;
    case 'get_order':
        getOrder();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem!']);
        break;
}

function getOrders() {
    global $connection;
    
    $musteri_id = $_SESSION['id'];
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    $sql = "SELECT siparis_id, musteri_adi, tarih, durum, toplam_adet, aciklama FROM siparisler WHERE musteri_id = ?";
    
    if ($status_filter !== 'all') {
        $sql .= " AND durum = ?";
    }
    
    $sql .= " ORDER BY tarih DESC";
    
    $stmt = $connection->prepare($sql);
    
    if ($status_filter !== 'all') {
        $stmt->bind_param('is', $musteri_id, $status_filter);
    } else {
        $stmt->bind_param('i', $musteri_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $orders]);
}

function cancelOrder() {
    global $connection;
    
    $siparis_id = isset($_POST['siparis_id']) ? (int)$_POST['siparis_id'] : 0;
    $musteri_id = $_SESSION['id'];
    
    // Check if order belongs to customer and is still pending
    $check_sql = "SELECT durum FROM siparisler WHERE siparis_id = ? AND musteri_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param('ii', $siparis_id, $musteri_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Yetkisiz işlem!']);
        return;
    }
    
    $order = $result->fetch_assoc();
    
    // Only allow cancellation of pending orders
    if ($order['durum'] !== 'beklemede') {
        echo json_encode(['status' => 'error', 'message' => 'Sadece beklemede olan siparişler iptal edilebilir!']);
        return;
    }
    
    // Update order status to cancelled
    $update_sql = "UPDATE siparisler SET durum = 'iptal_edildi', tarih = NOW() WHERE siparis_id = ? AND musteri_id = ?";
    $update_stmt = $connection->prepare($update_sql);
    $update_stmt->bind_param('ii', $siparis_id, $musteri_id);
    
    if ($update_stmt->execute()) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "Sipariş iptal edildi (ID: $siparis_id)", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla iptal edildi!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sipariş iptal edilirken bir hata oluştu!']);
    }
}

function updateOrder() {
    global $connection;
    
    $siparis_id = isset($_POST['siparis_id']) ? (int)$_POST['siparis_id'] : 0;
    $musteri_id = $_SESSION['id'];
    $aciklama = isset($_POST['aciklama']) ? $_POST['aciklama'] : '';
    
    // Check if order belongs to customer and is still pending
    $check_sql = "SELECT durum FROM siparisler WHERE siparis_id = ? AND musteri_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param('ii', $siparis_id, $musteri_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Yetkisiz işlem!']);
        return;
    }
    
    $order = $result->fetch_assoc();
    
    // Only allow updates to pending orders
    if ($order['durum'] !== 'beklemede') {
        echo json_encode(['status' => 'error', 'message' => 'Sadece beklemede olan siparişler düzenlenebilir!']);
        return;
    }
    
    // Update order description
    $update_sql = "UPDATE siparisler SET aciklama = ? WHERE siparis_id = ? AND musteri_id = ?";
    $update_stmt = $connection->prepare($update_sql);
    $update_stmt->bind_param('sii', $aciklama, $siparis_id, $musteri_id);
    
    if ($update_stmt->execute()) {
        // Log ekleme
        log_islem($connection, $_SESSION['kullanici_adi'], "Sipariş güncellendi (ID: $siparis_id)", 'UPDATE');
        echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla güncellendi!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sipariş güncellenirken bir hata oluştu!']);
    }
}

function getOrderItems() {
    global $connection;

    $siparis_id = isset($_GET['siparis_id']) ? (int)$_GET['siparis_id'] : 0;
    $musteri_id = $_SESSION['id'];

    // Verify that the order belongs to the customer
    $check_sql = "SELECT siparis_id FROM siparisler WHERE siparis_id = ? AND musteri_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param('ii', $siparis_id, $musteri_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Yetkisiz işlem!']);
        return;
    }

    // Get order items with full information
    $sql = "SELECT sk.urun_kodu, sk.urun_ismi, sk.adet, sk.birim, u.urun_ismi as urun_gercek_ismi, u.birim as urun_gercek_birim
            FROM siparis_kalemleri sk
            LEFT JOIN urunler u ON sk.urun_kodu = u.urun_kodu
            WHERE sk.siparis_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $siparis_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        // Use data from siparis_kalemleri, but fallback to urunler if missing
        $items[] = [
            'urun_kodu' => $row['urun_kodu'],
            'urun_ismi' => $row['urun_ismi'] ?: $row['urun_gercek_ismi'] ?: 'Bilinmeyen Ürün',
            'adet' => $row['adet'],
            'birim' => $row['birim'] ?: $row['urun_gercek_birim'] ?: 'adet'
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $items]);
}

function getOrder() {
    global $connection;
    
    $siparis_id = isset($_GET['siparis_id']) ? (int)$_GET['siparis_id'] : 0;
    $musteri_id = $_SESSION['id'];
    
    // Get order details
    $sql = "SELECT siparis_id, musteri_adi, tarih, durum, toplam_adet, aciklama FROM siparisler WHERE siparis_id = ? AND musteri_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('ii', $siparis_id, $musteri_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Yetkisiz işlem!']);
        return;
    }
    
    $order = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $order]);
}
?>

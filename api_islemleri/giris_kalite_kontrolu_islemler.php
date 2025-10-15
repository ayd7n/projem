<?php
include '../config.php';

// Start session to access session variables
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum süresi dolmuş. Lütfen tekrar giriş yapın.']);
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Bu işlemi gerçekleştirme yetkiniz yok.']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $response = [];

        if ($action === 'add_control') {
            $tedarikci_id = $_POST['tedarikci_id'];
            $malzeme_kodu = $_POST['malzeme_kodu'];
            $red_edilen_miktar = $_POST['red_edilen_miktar'];
            $red_nedeni = $_POST['red_nedeni'];
            $ilgili_belge_no = $_POST['ilgili_belge_no'];
            $aciklama = $_POST['aciklama'];
            
            // Get tedarikci name
            $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
            $tedarikci_stmt = $connection->prepare($tedarikci_query);
            $tedarikci_stmt->bind_param('i', $tedarikci_id);
            $tedarikci_stmt->execute();
            $tedarikci_result = $tedarikci_stmt->get_result();
            $tedarikci = $tedarikci_result->fetch_assoc();
            $tedarikci_adi = $tedarikci['tedarikci_adi'];
            
            // Get malzeme details
            $malzeme_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
            $malzeme_stmt = $connection->prepare($malzeme_query);
            $malzeme_stmt->bind_param('i', $malzeme_kodu);
            $malzeme_stmt->execute();
            $malzeme_result = $malzeme_stmt->get_result();
            $malzeme = $malzeme_result->fetch_assoc();
            $malzeme_ismi = $malzeme['malzeme_ismi'];
            $birim = $malzeme['birim'];
            
            $query = "INSERT INTO giris_kalite_kontrolu (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim, reddedilen_miktar, red_nedeni, kontrol_eden_personel_id, kontrol_eden_personel_adi, ilgili_belge_no, aciklama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
        $stmt->bind_param('issssdssiss', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim, $red_edilen_miktar, $red_nedeni, $_SESSION['user_id'], $_SESSION['kullanici_adi'], $ilgili_belge_no, $aciklama);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Giriş kalite kontrolü kaydı başarıyla oluşturuldu.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Giriş kalite kontrolü kaydı oluşturulurken hata oluştu: ' . $connection->error];
            }
            $stmt->close();
        } 
        elseif ($action === 'update_control') {
            $kontrol_id = $_POST['kontrol_id'];
            $tedarikci_id = $_POST['tedarikci_id'];
            $malzeme_kodu = $_POST['malzeme_kodu'];
            $red_edilen_miktar = $_POST['red_edilen_miktar'];
            $red_nedeni = $_POST['red_nedeni'];
            $ilgili_belge_no = $_POST['ilgili_belge_no'];
            $aciklama = $_POST['aciklama'];
            
            // Get tedarikci name
            $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
            $tedarikci_stmt = $connection->prepare($tedarikci_query);
            $tedarikci_stmt->bind_param('i', $tedarikci_id);
            $tedarikci_stmt->execute();
            $tedarikci_result = $tedarikci_stmt->get_result();
            $tedarikci = $tedarikci_result->fetch_assoc();
            $tedarikci_adi = $tedarikci['tedarikci_adi'];
            
            // Get malzeme details
            $malzeme_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
            $malzeme_stmt = $connection->prepare($malzeme_query);
            $malzeme_stmt->bind_param('i', $malzeme_kodu);
            $malzeme_stmt->execute();
            $malzeme_result = $malzeme_stmt->get_result();
            $malzeme = $malzeme_result->fetch_assoc();
            $malzeme_ismi = $malzeme['malzeme_ismi'];
            $birim = $malzeme['birim'];
            
            $query = "UPDATE giris_kalite_kontrolu SET tedarikci_id = ?, tedarikci_adi = ?, malzeme_kodu = ?, malzeme_ismi = ?, birim = ?, reddedilen_miktar = ?, red_nedeni = ?, ilgili_belge_no = ?, aciklama = ? WHERE kontrol_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('issssdsssi', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim, $red_edilen_miktar, $red_nedeni, $ilgili_belge_no, $aciklama, $kontrol_id);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Giriş kalite kontrolü kaydı başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Giriş kalite kontrolü kaydı güncellenirken hata oluştu: ' . $connection->error];
            }
            $stmt->close();
        } 
        elseif ($action === 'delete_control') {
            $kontrol_id = $_POST['kontrol_id'];
            
            $query = "DELETE FROM giris_kalite_kontrolu WHERE kontrol_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $kontrol_id);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Giriş kalite kontrolü kaydı başarıyla silindi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Giriş kalite kontrolü kaydı silinirken hata oluştu: ' . $connection->error];
            }
            $stmt->close();
        } else {
            $response = ['status' => 'error', 'message' => 'Geçersiz işlem'];
        }
        
        echo json_encode($response);
    } 
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'get_control' && isset($_GET['id'])) {
            $kontrol_id = $_GET['id'];
            
            $query = "SELECT * FROM giris_kalite_kontrolu WHERE kontrol_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('i', $kontrol_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($control = $result->fetch_assoc()) {
                $response = ['status' => 'success', 'data' => $control];
            } else {
                $response = ['status' => 'error', 'message' => 'Kalite kontrol kaydı bulunamadı'];
            }
            
            echo json_encode($response);
            $stmt->close();
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Sistem hatası: ' . $e->getMessage()]);
}
?>

<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

// Define the protected user
define('PROTECTED_USER_NAME', 'Admin User');

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_employee' && isset($_GET['id'])) {
        $personel_id = (int)$_GET['id'];
        $query = "SELECT * FROM personeller WHERE personel_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $personel_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $stmt->close();

        if ($employee) {
            $response = ['status' => 'success', 'data' => $employee];
        } else {
            $response = ['status' => 'error', 'message' => 'Personel bulunamadı.'];
        }
    }
    elseif ($action == 'get_all_employees') {
        $query = "SELECT * FROM personeller ORDER BY ad_soyad";
        $result = $connection->query($query);
        
        if ($result) {
            $employees = [];
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
            $response = ['status' => 'success', 'data' => $employees];
        } else {
            $response = ['status' => 'error', 'message' => 'Personel listesi alınamadı.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Extract data from POST
    $ad_soyad = $_POST['ad_soyad'] ?? null;
    $tc_kimlik_no = $_POST['tc_kimlik_no'] ?? '';
    $dogum_tarihi = $_POST['dogum_tarihi'] ?? '';
    $ise_giris_tarihi = $_POST['ise_giris_tarihi'] ?? date('Y-m-d');
    $pozisyon = $_POST['pozisyon'] ?? '';
    $departman = $_POST['departman'] ?? '';
    $e_posta = $_POST['e_posta'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $notlar = $_POST['notlar'] ?? '';
    $sifre = $_POST['sifre'] ?? '';

    if ($action == 'add_employee') {
        if (empty($ad_soyad)) {
            $response = ['status' => 'error', 'message' => 'Ad soyad boş olamaz.'];
        } else {
            // Hash the password if provided, otherwise use default
            $hashed_password = !empty($sifre) ? password_hash($sifre, PASSWORD_DEFAULT) : password_hash('12345', PASSWORD_DEFAULT);
            
            $query = "INSERT INTO personeller (ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, adres, notlar, sistem_sifresi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssssssssss', $ad_soyad, $tc_kimlik_no, $dogum_tarihi, $ise_giris_tarihi, $pozisyon, $departman, $e_posta, $telefon, $adres, $notlar, $hashed_password);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Personel başarıyla eklendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'update_employee' && isset($_POST['personel_id'])) {
        $personel_id = (int)$_POST['personel_id'];
        
        // Check if this is the protected user
        $check_stmt = $connection->prepare("SELECT ad_soyad FROM personeller WHERE personel_id = ?");
        $check_stmt->bind_param('i', $personel_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            $user_to_check = $result->fetch_assoc();
            if ($user_to_check['ad_soyad'] === PROTECTED_USER_NAME) {
                $response = ['status' => 'error', 'message' => PROTECTED_USER_NAME . ' kaydı güncellenemez.'];
                $check_stmt->close();
                echo json_encode($response);
                exit;
            }
        }
        $check_stmt->close();
        
        if (empty($ad_soyad)) {
            $response = ['status' => 'error', 'message' => 'Ad soyad boş olamaz.'];
        } else {
            // Update password if provided
            if (!empty($sifre)) {
                $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
                $query = "UPDATE personeller SET ad_soyad = ?, tc_kimlik_no = ?, dogum_tarihi = ?, ise_giris_tarihi = ?, pozisyon = ?, departman = ?, e_posta = ?, telefon = ?, adres = ?, notlar = ?, sistem_sifresi = ? WHERE personel_id = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('sssssssssssi', $ad_soyad, $tc_kimlik_no, $dogum_tarihi, $ise_giris_tarihi, $pozisyon, $departman, $e_posta, $telefon, $adres, $notlar, $hashed_password, $personel_id);
            } else {
                $query = "UPDATE personeller SET ad_soyad = ?, tc_kimlik_no = ?, dogum_tarihi = ?, ise_giris_tarihi = ?, pozisyon = ?, departman = ?, e_posta = ?, telefon = ?, adres = ?, notlar = ? WHERE personel_id = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('ssssssssssi', $ad_soyad, $tc_kimlik_no, $dogum_tarihi, $ise_giris_tarihi, $pozisyon, $departman, $e_posta, $telefon, $adres, $notlar, $personel_id);
            }

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Personel başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'delete_employee' && isset($_POST['personel_id'])) {
        $personel_id = (int)$_POST['personel_id'];
        
        // Check if this is the protected user
        $check_stmt = $connection->prepare("SELECT ad_soyad FROM personeller WHERE personel_id = ?");
        $check_stmt->bind_param('i', $personel_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            $user_to_check = $result->fetch_assoc();
            if ($user_to_check['ad_soyad'] === PROTECTED_USER_NAME) {
                $response = ['status' => 'error', 'message' => PROTECTED_USER_NAME . ' kaydı silinemez.'];
                $check_stmt->close();
                echo json_encode($response);
                exit;
            }
        }
        $check_stmt->close();
        
        $query = "DELETE FROM personeller WHERE personel_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $personel_id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Personel başarıyla silindi.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
        }
        $stmt->close();
    }
}

$connection->close();
echo json_encode($response);
?>
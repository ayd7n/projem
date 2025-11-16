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
        try {
            // Don't include password in the response for security
            $query = "SELECT personel_id, ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, adres, notlar FROM personeller WHERE personel_id = $personel_id";
            $result = $connection->query($query);
            
            if ($result) {
                $employee = $result->fetch_assoc();
                if ($employee) {
                    $response = ['status' => 'success', 'data' => $employee];
                } else {
                    $response = ['status' => 'error', 'message' => 'Personel bulunamadı.'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Sorgu hatası: ' . $connection->error];
            }
        } catch (mysqli_sql_exception $e) {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
        }
    }
    elseif ($action == 'get_all_employees') {
        try {
            // Don't include password in the response for security
            $query = "SELECT personel_id, ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, adres, notlar FROM personeller ORDER BY ad_soyad";
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
        } catch (mysqli_sql_exception $e) {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add_employee') {
        $ad_soyad = $connection->real_escape_string($_POST['ad_soyad'] ?? '');
        $tc_kimlik_no = $connection->real_escape_string($_POST['tc_kimlik_no'] ?? '');
        $dogum_tarihi = $connection->real_escape_string($_POST['dogum_tarihi'] ?? '');
        $ise_giris_tarihi = $connection->real_escape_string($_POST['ise_giris_tarihi'] ?? date('Y-m-d'));
        $pozisyon = $connection->real_escape_string($_POST['pozisyon'] ?? '');
        $departman = $connection->real_escape_string($_POST['departman'] ?? '');
        $e_posta = $connection->real_escape_string($_POST['e_posta'] ?? '');
        $telefon = $connection->real_escape_string($_POST['telefon'] ?? '');
        $adres = $connection->real_escape_string($_POST['adres'] ?? '');
        $notlar = $connection->real_escape_string($_POST['notlar'] ?? '');
        $sifre = $_POST['sifre'] ?? '';

        if (empty($ad_soyad)) {
            $response = ['status' => 'error', 'message' => 'Ad soyad boş olamaz.'];
        } elseif (empty($dogum_tarihi)) {
            $response = ['status' => 'error', 'message' => 'Doğum tarihi zorunludur.'];
        } else {
            try {
                $hashed_password = !empty($sifre) ? password_hash($sifre, PASSWORD_DEFAULT) : password_hash('12345', PASSWORD_DEFAULT);
                $escaped_hashed_password = $connection->real_escape_string($hashed_password);

                $query = "INSERT INTO personeller (ad_soyad, tc_kimlik_no, dogum_tarihi, ise_giris_tarihi, pozisyon, departman, e_posta, telefon, adres, notlar, sistem_sifresi) VALUES ('$ad_soyad', '$tc_kimlik_no', '$dogum_tarihi', '$ise_giris_tarihi', '$pozisyon', '$departman', '$e_posta', '$telefon', '$adres', '$notlar', '$escaped_hashed_password')";

                if ($connection->query($query)) {
                    $response = ['status' => 'success', 'message' => 'Personel başarıyla eklendi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
                }
            } catch (mysqli_sql_exception $e) {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
            }
        }
    } elseif ($action == 'update_employee' && isset($_POST['personel_id'])) {
        $personel_id = (int)$_POST['personel_id'];

        // Check if this is the protected user
        $check_query = "SELECT ad_soyad FROM personeller WHERE personel_id = $personel_id";
        $check_result = $connection->query($check_query);
        if ($check_result && $check_result->num_rows > 0) {
            $user_to_check = $check_result->fetch_assoc();
            if ($user_to_check['ad_soyad'] === PROTECTED_USER_NAME) {
                echo json_encode(['status' => 'error', 'message' => PROTECTED_USER_NAME . ' kaydı güncellenemez.']);
                exit;
            }
        }

        $ad_soyad = $connection->real_escape_string($_POST['ad_soyad'] ?? '');
        $tc_kimlik_no = $connection->real_escape_string($_POST['tc_kimlik_no'] ?? '');
        $dogum_tarihi = $connection->real_escape_string($_POST['dogum_tarihi'] ?? '');
        $ise_giris_tarihi = $connection->real_escape_string($_POST['ise_giris_tarihi'] ?? date('Y-m-d'));
        $pozisyon = $connection->real_escape_string($_POST['pozisyon'] ?? '');
        $departman = $connection->real_escape_string($_POST['departman'] ?? '');
        $e_posta = $connection->real_escape_string($_POST['e_posta'] ?? '');
        $telefon = $connection->real_escape_string($_POST['telefon'] ?? '');
        $adres = $connection->real_escape_string($_POST['adres'] ?? '');
        $notlar = $connection->real_escape_string($_POST['notlar'] ?? '');
        $sifre = $_POST['sifre'] ?? '';

        if (empty($ad_soyad)) {
            $response = ['status' => 'error', 'message' => 'Ad soyad boş olamaz.'];
        } elseif (empty($dogum_tarihi)) {
            $response = ['status' => 'error', 'message' => 'Doğum tarihi zorunludur.'];
        } else {
            try {
                $update_fields = "ad_soyad = '$ad_soyad', tc_kimlik_no = '$tc_kimlik_no', dogum_tarihi = '$dogum_tarihi', ise_giris_tarihi = '$ise_giris_tarihi', pozisyon = '$pozisyon', departman = '$departman', e_posta = '$e_posta', telefon = '$telefon', adres = '$adres', notlar = '$notlar'";
                
                if (!empty($sifre)) {
                    $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
                    $escaped_hashed_password = $connection->real_escape_string($hashed_password);
                    $update_fields .= ", sistem_sifresi = '$escaped_hashed_password'";
                }

                $query = "UPDATE personeller SET $update_fields WHERE personel_id = $personel_id";

                if ($connection->query($query)) {
                    $response = ['status' => 'success', 'message' => 'Personel başarıyla güncellendi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
                }
            } catch (mysqli_sql_exception $e) {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
            }
        }
    } elseif ($action == 'delete_employee' && isset($_POST['personel_id'])) {
        $personel_id = (int)$_POST['personel_id'];

        // Check if this is the protected user
        $check_query = "SELECT ad_soyad FROM personeller WHERE personel_id = $personel_id";
        $check_result = $connection->query($check_query);
        if ($check_result && $check_result->num_rows > 0) {
            $user_to_check = $check_result->fetch_assoc();
            if ($user_to_check['ad_soyad'] === PROTECTED_USER_NAME) {
                echo json_encode(['status' => 'error', 'message' => PROTECTED_USER_NAME . ' kaydı silinemez.']);
                exit;
            }
        }

        try {
            $query = "DELETE FROM personeller WHERE personel_id = $personel_id";
            if ($connection->query($query)) {
                $response = ['status' => 'success', 'message' => 'Personel başarıyla silindi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
            }
        } catch (mysqli_sql_exception $e) {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
        }
    }
    elseif ($action == 'update_permissions' && isset($_POST['personel_id'])) {
        // Only the super-admin can change permissions
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            echo json_encode(['status' => 'error', 'message' => 'Bu işlemi yapma yetkiniz yok.']);
            exit;
        }

        $personel_id = (int)$_POST['personel_id'];
        $permissions = $_POST['permissions'] ?? [];

        // Prevent changing admin's permissions
        $check_query = "SELECT e_posta FROM personeller WHERE personel_id = $personel_id";
        $check_result = $connection->query($check_query);
        if ($check_result && $check_result->num_rows > 0) {
            $user_to_check = $check_result->fetch_assoc();
            if ($user_to_check['e_posta'] === 'admin@parfum.com') {
                echo json_encode(['status' => 'error', 'message' => 'Admin kullanıcısının yetkileri değiştirilemez.']);
                exit;
            }
        }

        $connection->begin_transaction();
        try {
            // Delete old permissions
            $delete_stmt = $connection->prepare("DELETE FROM personel_izinleri WHERE personel_id = ?");
            $delete_stmt->bind_param('i', $personel_id);
            $delete_stmt->execute();
            $delete_stmt->close();

            // Insert new permissions
            if (!empty($permissions)) {
                $insert_stmt = $connection->prepare("INSERT INTO personel_izinleri (personel_id, izin_anahtari) VALUES (?, ?)");
                foreach ($permissions as $permission_key) {
                    $insert_stmt->bind_param('is', $personel_id, $permission_key);
                    $insert_stmt->execute();
                }
                $insert_stmt->close();
            }

            $connection->commit();
            $response = ['status' => 'success', 'message' => 'Personel yetkileri başarıyla güncellendi.'];

            // Reload permissions if the updated user is the current user
            if (isset($_SESSION['user_id']) && (int)$personel_id === (int)$_SESSION['user_id']) {
                require_once __DIR__ . '/../includes/auth_functions.php';
                reload_permissions($personel_id, $connection);
            }

        } catch (mysqli_sql_exception $e) {
            $connection->rollback();
            $response = ['status' => 'error', 'message' => 'Yetkiler güncellenirken bir veritabanı hatası oluştu: ' . $e->getMessage()];
        }
    }
}

$connection->close();
echo json_encode($response);
?>

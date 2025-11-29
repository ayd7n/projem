<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config.php';

header('Content-Type: application/json');

// Allow customers to view photos (GET requests), but restrict modifications (POST) to staff only
$is_get_request = $_SERVER['REQUEST_METHOD'] === 'GET';
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

if (!$is_get_request && $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

// GET İşlemleri
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_photos' && isset($_GET['urun_kodu'])) {
        // Allow all logged-in users to view photos (no additional permission check)

        $urun_kodu = (int) $_GET['urun_kodu'];
        $query = "SELECT * FROM urun_fotograflari WHERE urun_kodu = ? ORDER BY sira_no ASC, fotograf_id ASC";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_kodu);
        $stmt->execute();
        $result = $stmt->get_result();

        $photos = [];
        while ($row = $result->fetch_assoc()) {
            $photos[] = $row;
        }
        $stmt->close();

        $response = ['status' => 'success', 'data' => $photos];
    }
}

// POST İşlemleri
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'upload_photo' && isset($_POST['urun_kodu'])) {
        if (!yetkisi_var('action:urunler:edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Fotoğraf yükleme yetkiniz yok.']);
            exit;
        }

        $urun_kodu = (int) $_POST['urun_kodu'];

        // Dosya yükleme kontrolü
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $response = ['status' => 'error', 'message' => 'Dosya yüklenirken bir hata oluştu.'];
        } else {
            $file = $_FILES['photo'];
            $file_name = $file['name'];
            $file_size = $file['size'];
            $file_tmp = $file['tmp_name'];
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Dosya boyutu kontrolü (5MB = 5 * 1024 * 1024 bytes)
            $max_size = 5 * 1024 * 1024;
            if ($file_size > $max_size) {
                $response = ['status' => 'error', 'message' => 'Dosya boyutu 5MB\'dan büyük olamaz.'];
            }
            // Dosya formatı kontrolü
            elseif (!in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $response = ['status' => 'error', 'message' => 'Sadece JPG, JPEG, PNG ve GIF formatları desteklenmektedir.'];
            } else {
                // Benzersiz dosya adı oluştur
                $unique_name = uniqid() . '_' . time() . '.' . $file_type;
                $upload_dir = '../assets/urun_fotograflari/';
                $upload_path = $upload_dir . $unique_name;

                // Klasör yoksa oluştur
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Dosyayı yükle
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Veritabanına kaydet
                    $dosya_yolu = 'assets/urun_fotograflari/' . $unique_name;

                    // Mevcut en yüksek sıra numarasını al
                    $sira_query = "SELECT COALESCE(MAX(sira_no), 0) + 1 as next_sira FROM urun_fotograflari WHERE urun_kodu = ?";
                    $sira_stmt = $connection->prepare($sira_query);
                    $sira_stmt->bind_param('i', $urun_kodu);
                    $sira_stmt->execute();
                    $sira_result = $sira_stmt->get_result();
                    $sira_row = $sira_result->fetch_assoc();
                    $sira_no = $sira_row['next_sira'];
                    $sira_stmt->close();

                    $query = "INSERT INTO urun_fotograflari (urun_kodu, dosya_adi, dosya_yolu, sira_no) VALUES (?, ?, ?, ?)";
                    $stmt = $connection->prepare($query);
                    $stmt->bind_param('issi', $urun_kodu, $file_name, $dosya_yolu, $sira_no);

                    if ($stmt->execute()) {
                        $fotograf_id = $connection->insert_id;

                        // Ürün adını al
                        $urun_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
                        $urun_stmt = $connection->prepare($urun_query);
                        $urun_stmt->bind_param('i', $urun_kodu);
                        $urun_stmt->execute();
                        $urun_result = $urun_stmt->get_result();
                        $urun = $urun_result->fetch_assoc();
                        $urun_ismi = $urun['urun_ismi'] ?? 'Bilinmeyen Ürün';
                        $urun_stmt->close();

                        // Log kaydet
                        log_islem($connection, $_SESSION['kullanici_adi'], "$urun_ismi ürününe fotoğraf eklendi", 'CREATE');

                        $response = [
                            'status' => 'success',
                            'message' => 'Fotoğraf başarıyla yüklendi.',
                            'data' => [
                                'fotograf_id' => $fotograf_id,
                                'dosya_adi' => $file_name,
                                'dosya_yolu' => $dosya_yolu,
                                'sira_no' => $sira_no
                            ]
                        ];
                    } else {
                        // Veritabanı hatası varsa dosyayı sil
                        unlink($upload_path);
                        $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
                    }
                    $stmt->close();
                } else {
                    $response = ['status' => 'error', 'message' => 'Dosya yüklenirken bir hata oluştu.'];
                }
            }
        }
    } elseif ($action == 'delete_photo' && isset($_POST['fotograf_id'])) {
        if (!yetkisi_var('action:urunler:edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Fotoğraf silme yetkiniz yok.']);
            exit;
        }

        $fotograf_id = (int) $_POST['fotograf_id'];

        // Fotoğraf bilgilerini al
        $query = "SELECT uf.*, u.urun_ismi FROM urun_fotograflari uf 
                  LEFT JOIN urunler u ON uf.urun_kodu = u.urun_kodu 
                  WHERE uf.fotograf_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $fotograf_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $photo = $result->fetch_assoc();
        $stmt->close();

        if ($photo) {
            // Dosyayı sil
            $file_path = '../' . $photo['dosya_yolu'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Veritabanından sil
            $delete_query = "DELETE FROM urun_fotograflari WHERE fotograf_id = ?";
            $delete_stmt = $connection->prepare($delete_query);
            $delete_stmt->bind_param('i', $fotograf_id);

            if ($delete_stmt->execute()) {
                $urun_ismi = $photo['urun_ismi'] ?? 'Bilinmeyen Ürün';
                log_islem($connection, $_SESSION['kullanici_adi'], "$urun_ismi ürününden fotoğraf silindi", 'DELETE');
                $response = ['status' => 'success', 'message' => 'Fotoğraf başarıyla silindi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $delete_stmt->error];
            }
            $delete_stmt->close();
        } else {
            $response = ['status' => 'error', 'message' => 'Fotoğraf bulunamadı.'];
        }
    } elseif ($action == 'set_primary_photo' && isset($_POST['fotograf_id'])) {
        if (!yetkisi_var('action:urunler:edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Fotoğraf düzenleme yetkiniz yok.']);
            exit;
        }

        $fotograf_id = (int) $_POST['fotograf_id'];

        // Fotoğraf bilgilerini al
        $query = "SELECT urun_kodu FROM urun_fotograflari WHERE fotograf_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $fotograf_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $photo = $result->fetch_assoc();
        $stmt->close();

        if ($photo) {
            $urun_kodu = $photo['urun_kodu'];

            $connection->begin_transaction();
            try {
                // Önce bu ürünün tüm fotoğraflarını normal yap
                $reset_stmt = $connection->prepare("UPDATE urun_fotograflari SET ana_fotograf = 0 WHERE urun_kodu = ?");
                $reset_stmt->bind_param('i', $urun_kodu);
                $reset_stmt->execute();
                $reset_stmt->close();

                // Seçilen fotoğrafı ana yap
                $set_stmt = $connection->prepare("UPDATE urun_fotograflari SET ana_fotograf = 1 WHERE fotograf_id = ?");
                $set_stmt->bind_param('i', $fotograf_id);
                $set_stmt->execute();
                $set_stmt->close();

                $connection->commit();

                // Ürün adını al ve log kaydet
                $urun_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
                $urun_stmt = $connection->prepare($urun_query);
                $urun_stmt->bind_param('i', $urun_kodu);
                $urun_stmt->execute();
                $urun_result = $urun_stmt->get_result();
                $urun = $urun_result->fetch_assoc();
                $urun_ismi = $urun['urun_ismi'] ?? 'Bilinmeyen Ürün';
                $urun_stmt->close();

                log_islem($connection, $_SESSION['kullanici_adi'], "$urun_ismi ürününün ana fotoğrafı değiştirildi", 'UPDATE');

                $response = ['status' => 'success', 'message' => 'Ana fotoğraf başarıyla ayarlandı.'];
            } catch (Exception $e) {
                $connection->rollback();
                $response = ['status' => 'error', 'message' => 'İşlem sırasında bir hata oluştu: ' . $e->getMessage()];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Fotoğraf bulunamadı.'];
        }
    } elseif ($action == 'update_order' && isset($_POST['photos'])) {
        if (!yetkisi_var('action:urunler:edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Fotoğraf düzenleme yetkiniz yok.']);
            exit;
        }

        $photos = json_decode($_POST['photos'], true);

        if (!is_array($photos)) {
            $response = ['status' => 'error', 'message' => 'Geçersiz veri formatı.'];
        } else {
            $connection->begin_transaction();
            try {
                foreach ($photos as $index => $fotograf_id) {
                    $sira_no = $index + 1;
                    $stmt = $connection->prepare("UPDATE urun_fotograflari SET sira_no = ? WHERE fotograf_id = ?");
                    $stmt->bind_param('ii', $sira_no, $fotograf_id);
                    $stmt->execute();
                    $stmt->close();
                }

                $connection->commit();
                $response = ['status' => 'success', 'message' => 'Fotoğraf sıralaması güncellendi.'];
            } catch (Exception $e) {
                $connection->rollback();
                $response = ['status' => 'error', 'message' => 'İşlem sırasında bir hata oluştu: ' . $e->getMessage()];
            }
        }
    }
}

echo json_encode($response);
$connection->close();
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_contract':
        $id = $_GET['id'] ?? 0;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme ID belirtilmedi.']);
            break;
        }

        $query = "SELECT * FROM cerceve_sozlesmeler WHERE sozlesme_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $contract = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $contract]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme bulunamadı.']);
        }
        $stmt->close();
        break;

    case 'add_contract':
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        $malzeme_kodu = $_POST['malzeme_kodu'] ?? '';
        $birim_fiyat = $_POST['birim_fiyat'] ?? 0;
        $para_birimi = $_POST['para_birimi'] ?? '';
        $sozlesme_turu = $_POST['sozlesme_turu'] ?? '';
        $toplam_anlasilan_miktar = $_POST['toplam_anlasilan_miktar'] ?? 0;
        $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? '';
        $bitis_tarihi = $_POST['bitis_tarihi'] ?? '';
        $pesin_odeme_yapildi_mi = isset($_POST['pesin_odeme_yapildi_mi']) ? 1 : 0;
        $toplam_pesin_odeme_tutari = $_POST['toplam_pesin_odeme_tutari'] ?? 0;
        $durum = $_POST['durum'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';

        // Validation
        if (!$tedarikci_id || !$malzeme_kodu || !$birim_fiyat || !$para_birimi || !$sozlesme_turu || !$toplam_anlasilan_miktar || !$baslangic_tarihi || !$bitis_tarihi || !$durum) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $tedarikci_stmt = $connection->prepare($tedarikci_query);
        $tedarikci_stmt->bind_param('i', $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        if ($tedarikci_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
            $tedarikci_stmt->close();
            break;
        }
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];
        $tedarikci_stmt->close();

        // Get malzeme name
        $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
        $malzeme_stmt = $connection->prepare($malzeme_query);
        $malzeme_stmt->bind_param('i', $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        if ($malzeme_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme.']);
            $malzeme_stmt->close();
            break;
        }
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        $malzeme_stmt->close();

        // Insert contract
        $query = "INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, sozlesme_turu, toplam_anlasilan_miktar, kalan_anlasilan_miktar, baslangic_tarihi, bitis_tarihi, pesin_odeme_yapildi_mi, toplam_pesin_odeme_tutari, durum, olusturan, aciklama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('isssddssssddisss', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim_fiyat, $para_birimi, $sozlesme_turu, $toplam_anlasilan_miktar, $toplam_anlasilan_miktar, $baslangic_tarihi, $bitis_tarihi, $pesin_odeme_yapildi_mi, $toplam_pesin_odeme_tutari, $durum, $_SESSION['kullanici_adi'], $aciklama);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla oluşturuldu.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme oluşturulurken hata oluştu: ' . $connection->error]);
        }
        $stmt->close();
        break;

    case 'update_contract':
        $sozlesme_id = $_POST['sozlesme_id'] ?? 0;
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        $malzeme_kodu = $_POST['malzeme_kodu'] ?? '';
        $birim_fiyat = $_POST['birim_fiyat'] ?? 0;
        $para_birimi = $_POST['para_birimi'] ?? '';
        $sozlesme_turu = $_POST['sozlesme_turu'] ?? '';
        $toplam_anlasilan_miktar = $_POST['toplam_anlasilan_miktar'] ?? 0;
        $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? '';
        $bitis_tarihi = $_POST['bitis_tarihi'] ?? '';
        $pesin_odeme_yapildi_mi = isset($_POST['pesin_odeme_yapildi_mi']) ? 1 : 0;
        $toplam_pesin_odeme_tutari = $_POST['toplam_pesin_odeme_tutari'] ?? 0;
        $durum = $_POST['durum'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';

        // Validation
        if (!$sozlesme_id || !$tedarikci_id || !$malzeme_kodu || !$birim_fiyat || !$para_birimi || !$sozlesme_turu || !$toplam_anlasilan_miktar || !$baslangic_tarihi || !$bitis_tarihi || !$durum) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $tedarikci_stmt = $connection->prepare($tedarikci_query);
        $tedarikci_stmt->bind_param('i', $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        if ($tedarikci_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
            $tedarikci_stmt->close();
            break;
        }
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];
        $tedarikci_stmt->close();

        // Get malzeme name
        $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
        $malzeme_stmt = $connection->prepare($malzeme_query);
        $malzeme_stmt->bind_param('i', $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        if ($malzeme_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme.']);
            $malzeme_stmt->close();
            break;
        }
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        $malzeme_stmt->close();

        // Update contract
        $query = "UPDATE cerceve_sozlesmeler SET tedarikci_id = ?, tedarikci_adi = ?, malzeme_kodu = ?, malzeme_ismi = ?, birim_fiyat = ?, para_birimi = ?, sozlesme_turu = ?, toplam_anlasilan_miktar = ?, kalan_anlasilan_miktar = ?, baslangic_tarihi = ?, bitis_tarihi = ?, pesin_odeme_yapildi_mi = ?, toplam_pesin_odeme_tutari = ?, durum = ?, aciklama = ? WHERE sozlesme_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('isssddssdssdissi', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim_fiyat, $para_birimi, $sozlesme_turu, $toplam_anlasilan_miktar, $toplam_anlasilan_miktar, $baslangic_tarihi, $bitis_tarihi, $pesin_odeme_yapildi_mi, $toplam_pesin_odeme_tutari, $durum, $aciklama, $sozlesme_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla güncellendi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme güncellenirken hata oluştu: ' . $connection->error]);
        }
        $stmt->close();
        break;

    case 'delete_contract':
        $sozlesme_id = $_POST['sozlesme_id'] ?? 0;

        if (!$sozlesme_id) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme ID belirtilmedi.']);
            break;
        }

        $query = "DELETE FROM cerceve_sozlesmeler WHERE sozlesme_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $sozlesme_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla silindi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme silinirken hata oluştu: ' . $connection->error]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}
?>

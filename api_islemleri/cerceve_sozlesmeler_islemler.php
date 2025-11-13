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
        $id = $_GET['id'] ?? $_POST['id'] ?? 0;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme ID belirtilmedi.']);
            break;
        }

        $query = "SELECT * FROM cerceve_sozlesmeler WHERE sozlesme_id = $id";
        $result = $connection->query($query);

        if ($result && $result->num_rows > 0) {
            $contract = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $contract]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme bulunamadı.']);
        }
        break;

    case 'get_all_contracts':
        $query = "SELECT * FROM cerceve_sozlesmeler ORDER BY olusturulma_tarihi DESC";
        $result = $connection->query($query);

        if ($result) {
            $contracts = [];
            while ($row = $result->fetch_assoc()) {
                $contracts[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $contracts]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Veri alınırken hata oluştu: ' . $connection->error]);
        }
        break;

    case 'add_contract':
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        $malzeme_kodu = $_POST['malzeme_kodu'] ?? '';
        $birim_fiyat = $_POST['birim_fiyat'] ?? 0;
        $para_birimi = $_POST['para_birimi'] ?? '';
        $limit_miktar = $_POST['limit_miktar'] ?? 0;
        $toplu_odenen_miktar = $_POST['toplu_odenen_miktar'] ?? 0;
        $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? '';
        $bitis_tarihi = $_POST['bitis_tarihi'] ?? '';
        $oncelik = $_POST['oncelik'] ?? 0;
        $aciklama = $_POST['aciklama'] ?? '';

        // Validation
        if (!$tedarikci_id || !$malzeme_kodu || !$birim_fiyat || !$para_birimi || $limit_miktar === '' || !$baslangic_tarihi || !$bitis_tarihi) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = $tedarikci_id";
        $tedarikci_result = $connection->query($tedarikci_query);
        if (!$tedarikci_result || $tedarikci_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
            break;
        }
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];

        // Get malzeme name
        $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = $malzeme_kodu";
        $malzeme_result = $connection->query($malzeme_query);
        if (!$malzeme_result || $malzeme_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme.']);
            break;
        }
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];

        // Insert contract
        $query = "INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, limit_miktar, toplu_odenen_miktar, baslangic_tarihi, bitis_tarihi, oncelik, aciklama, olusturan) VALUES ($tedarikci_id, '$tedarikci_adi', $malzeme_kodu, '$malzeme_ismi', $birim_fiyat, '$para_birimi', $limit_miktar, $toplu_odenen_miktar, '$baslangic_tarihi', '$bitis_tarihi', $oncelik, '$aciklama', '" . $_SESSION['kullanici_adi'] . "')";

        if ($connection->query($query)) {
            echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla oluşturuldu.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme oluşturulurken hata oluştu: ' . $connection->error]);
        }
        break;

    case 'update_contract':
        $sozlesme_id = $_POST['sozlesme_id'] ?? 0;
        $tedarikci_id = $_POST['tedarikci_id'] ?? '';
        $malzeme_kodu = $_POST['malzeme_kodu'] ?? '';
        $birim_fiyat = $_POST['birim_fiyat'] ?? 0;
        $para_birimi = $_POST['para_birimi'] ?? '';
        $limit_miktar = $_POST['limit_miktar'] ?? 0;
        $toplu_odenen_miktar = $_POST['toplu_odenen_miktar'] ?? 0;
        $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? '';
        $bitis_tarihi = $_POST['bitis_tarihi'] ?? '';
        $oncelik = $_POST['oncelik'] ?? 0;
        $aciklama = $_POST['aciklama'] ?? '';

        // Validation
        if (!$sozlesme_id || !$tedarikci_id || !$malzeme_kodu || !$birim_fiyat || !$para_birimi || $limit_miktar === '' || !$baslangic_tarihi || !$bitis_tarihi) {
            echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
            break;
        }

        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = $tedarikci_id";
        $tedarikci_result = $connection->query($tedarikci_query);
        if (!$tedarikci_result || $tedarikci_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz tedarikçi.']);
            break;
        }
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];

        // Get malzeme name
        $malzeme_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = $malzeme_kodu";
        $malzeme_result = $connection->query($malzeme_query);
        if (!$malzeme_result || $malzeme_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz malzeme.']);
            break;
        }
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];

        // Update contract
        $query = "UPDATE cerceve_sozlesmeler SET tedarikci_id = $tedarikci_id, tedarikci_adi = '$tedarikci_adi', malzeme_kodu = $malzeme_kodu, malzeme_ismi = '$malzeme_ismi', birim_fiyat = $birim_fiyat, para_birimi = '$para_birimi', limit_miktar = $limit_miktar, toplu_odenen_miktar = $toplu_odenen_miktar, baslangic_tarihi = '$baslangic_tarihi', bitis_tarihi = '$bitis_tarihi', oncelik = $oncelik, aciklama = '$aciklama' WHERE sozlesme_id = $sozlesme_id";

        if ($connection->query($query)) {
            echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla güncellendi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme güncellenirken hata oluştu: ' . $connection->error]);
        }
        break;

    case 'delete_contract':
        $sozlesme_id = $_POST['sozlesme_id'] ?? 0;

        if (!$sozlesme_id) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme ID belirtilmedi.']);
            break;
        }

        $query = "DELETE FROM cerceve_sozlesmeler WHERE sozlesme_id = $sozlesme_id";

        if ($connection->query($query)) {
            echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme başarıyla silindi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Çerçeve sözleşme silinirken hata oluştu: ' . $connection->error]);
        }
        break;

    case 'get_contract_movements':
        $sozlesme_id = $_POST['sozlesme_id'] ?? 0;

        if (!$sozlesme_id) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme ID belirtilmedi.']);
            break;
        }

        // Check if contract exists
        $contract_check_query = "SELECT sozlesme_id FROM cerceve_sozlesmeler WHERE sozlesme_id = $sozlesme_id";
        $contract_check_result = $connection->query($contract_check_query);

        if (!$contract_check_result || $contract_check_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme bulunamadı.']);
            break;
        }

        // Get related stock movements
        $movements_query = "SELECT shk.hareket_id, shk.miktar,
                           COALESCE(DATE_FORMAT(shk.tarih, '%d.%m.%Y %H:%i'), '-') as tarih,
                           COALESCE(shk.aciklama, '-') as aciklama
                           FROM stok_hareket_kayitlari shk
                           JOIN stok_hareketleri_sozlesmeler shs ON shk.hareket_id = shs.hareket_id
                           WHERE shs.sozlesme_id = $sozlesme_id AND shk.hareket_turu = 'mal_kabul'
                           ORDER BY shk.tarih DESC, shk.hareket_id DESC";

        $movements_result = $connection->query($movements_query);

        $movements = [];
        if ($movements_result) {
            while ($movement = $movements_result->fetch_assoc()) {
                $movements[] = $movement;
            }
        }

        echo json_encode([
            'status' => 'success',
            'movements' => $movements
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}
?>

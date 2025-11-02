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
        $aciklama = $_POST['aciklama'] ?? '';

        // Validation
        if (!$tedarikci_id || !$malzeme_kodu || !$birim_fiyat || !$para_birimi || $limit_miktar === '' || !$baslangic_tarihi || !$bitis_tarihi) {
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
        $query = "INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, limit_miktar, toplu_odenen_miktar, baslangic_tarihi, bitis_tarihi, aciklama, olusturan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('isssdsssssss', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim_fiyat, $para_birimi, $limit_miktar, $toplu_odenen_miktar, $baslangic_tarihi, $bitis_tarihi, $aciklama, $_SESSION['kullanici_adi']);

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
        $limit_miktar = $_POST['limit_miktar'] ?? 0;
        $toplu_odenen_miktar = $_POST['toplu_odenen_miktar'] ?? 0;
        $baslangic_tarihi = $_POST['baslangic_tarihi'] ?? '';
        $bitis_tarihi = $_POST['bitis_tarihi'] ?? '';
        $aciklama = $_POST['aciklama'] ?? '';

        // Validation
        if (!$sozlesme_id || !$tedarikci_id || !$malzeme_kodu || !$birim_fiyat || !$para_birimi || $limit_miktar === '' || !$baslangic_tarihi || !$bitis_tarihi) {
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
        $query = "UPDATE cerceve_sozlesmeler SET tedarikci_id = ?, tedarikci_adi = ?, malzeme_kodu = ?, malzeme_ismi = ?, birim_fiyat = ?, para_birimi = ?, limit_miktar = ?, toplu_odenen_miktar = ?, baslangic_tarihi = ?, bitis_tarihi = ?, aciklama = ? WHERE sozlesme_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('isssdssssssi', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim_fiyat, $para_birimi, $limit_miktar, $toplu_odenen_miktar, $baslangic_tarihi, $bitis_tarihi, $aciklama, $sozlesme_id);

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

    case 'get_contract_details':
        $sozlesme_id = $_POST['sozlesme_id'] ?? 0;

        if (!$sozlesme_id) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme ID belirtilmedi.']);
            break;
        }

        // Get contract details
        $contract_query = "SELECT c.sozlesme_id, c.tedarikci_id, c.tedarikci_adi, c.malzeme_kodu, c.malzeme_ismi, 
                          c.birim_fiyat, c.para_birimi, c.limit_miktar, c.toplu_odenen_miktar,
                          c.baslangic_tarihi, c.bitis_tarihi, c.olusturan, c.olusturulma_tarihi, 
                          c.aciklama, c.oncelik,
                          COALESCE(shs.toplam_kullanilan, 0) as toplam_mal_kabul_miktari,
                          (c.limit_miktar - COALESCE(shs.toplam_kullanilan, 0)) as kalan_miktar,
                          CASE WHEN c.bitis_tarihi < CURDATE() THEN 0 WHEN COALESCE(shs.toplam_kullanilan, 0) >= c.limit_miktar THEN 0 ELSE 1 END as gecerli_mi,
                          CASE WHEN c.bitis_tarihi < CURDATE() THEN 'Suresi Dolmus' WHEN COALESCE(shs.toplam_kullanilan, 0) >= c.limit_miktar THEN 'Limit Dolmus' ELSE 'Gecerli' END as gecerlilik_durumu
                          FROM cerceve_sozlesmeler c
                          LEFT JOIN (
                              SELECT sozlesme_id, SUM(kullanilan_miktar) as toplam_kullanilan
                              FROM stok_hareketleri_sozlesmeler shs
                              WHERE EXISTS (
                                  SELECT 1 FROM stok_hareket_kayitlari
                                  WHERE stok_hareket_kayitlari.hareket_id = shs.hareket_id
                                  AND stok_hareket_kayitlari.hareket_turu = 'mal_kabul'
                              )
                              GROUP BY sozlesme_id
                          ) shs ON c.sozlesme_id = shs.sozlesme_id
                          WHERE c.sozlesme_id = ?";
        
        $contract_stmt = $connection->prepare($contract_query);
        $contract_stmt->bind_param('i', $sozlesme_id);
        $contract_stmt->execute();
        $contract_result = $contract_stmt->get_result();
        
        if ($contract_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme bulunamadı.']);
            $contract_stmt->close();
            break;
        }
        
        $contract = $contract_result->fetch_assoc();
        $contract_stmt->close();

        // Get related stock movements
        $movements_query = "SELECT shk.hareket_id, shk.miktar, shk.tarih, shk.aciklama
                           FROM stok_hareket_kayitlari shk
                           JOIN stok_hareketleri_sozlesmeler shs ON shk.hareket_id = shs.hareket_id
                           WHERE shs.sozlesme_id = ? AND shk.hareket_turu = 'mal_kabul'
                           ORDER BY shk.tarih DESC, shk.hareket_id DESC";
        
        $movements_stmt = $connection->prepare($movements_query);
        $movements_stmt->bind_param('i', $sozlesme_id);
        $movements_stmt->execute();
        $movements_result = $movements_stmt->get_result();
        
        $movements = [];
        while ($movement = $movements_result->fetch_assoc()) {
            $movements[] = $movement;
        }
        $movements_stmt->close();

        echo json_encode([
            'status' => 'success',
            'data' => $contract,
            'movements' => $movements
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}
?>
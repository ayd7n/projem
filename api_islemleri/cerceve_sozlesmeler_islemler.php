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
        $olusturan = $connection->real_escape_string((string)($_SESSION['kullanici_adi'] ?? ''));
        $query = "INSERT INTO cerceve_sozlesmeler (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim_fiyat, para_birimi, limit_miktar, toplu_odenen_miktar, baslangic_tarihi, bitis_tarihi, oncelik, aciklama, olusturan) VALUES ($tedarikci_id, '$tedarikci_adi', $malzeme_kodu, '$malzeme_ismi', $birim_fiyat, '$para_birimi', $limit_miktar, $toplu_odenen_miktar, '$baslangic_tarihi', '$bitis_tarihi', $oncelik, '$aciklama', '$olusturan')";

        // --- DEBUG LOGGING ---
        $log_file = 'C:/Users/AYDIN/.gemini/tmp/de7b2d039006603d4eef2de07c6180acfffb7c9889e7c7fd347c897d4a766b4a/debug_log.txt';
        $log_content = "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $log_content .= "Session Data: " . print_r($_SESSION, true) . "\n";
        $log_content .= "Generated Query: " . $query . "\n\n";
        file_put_contents($log_file, $log_content, FILE_APPEND);
        // --- END DEBUG LOGGING ---

        if ($connection->query($query)) {
            // Check if there is a pre-payment
            if ($toplu_odenen_miktar > 0) {
                $amount_in_foreign_currency = $toplu_odenen_miktar * $birim_fiyat;
                $gider_tarih = date('Y-m-d');
                $gider_aciklama = "$malzeme_ismi için $toplu_odenen_miktar adet ön ödeme";
                $user_id = $_SESSION['user_id'];

                // Get exchange rates
                $rates_query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
                $rates_result = $connection->query($rates_query);
                $rates_data = [];
                while ($row = $rates_result->fetch_assoc()) {
                    $rates_data[$row['ayar_anahtar']] = $row['ayar_deger'];
                }
                $dolar_kuru = $rates_data['dolar_kuru'] ?? 1.0;
                $euro_kuru = $rates_data['euro_kuru'] ?? 1.0;
                
                $final_tl_amount = $amount_in_foreign_currency;
                $exchange_rate_info = "";

                if ($para_birimi === 'USD') {
                    $final_tl_amount = $amount_in_foreign_currency * $dolar_kuru;
                    $exchange_rate_info = " (" . number_format($amount_in_foreign_currency, 2, ',', '.') . " USD @ " . number_format($dolar_kuru, 4, ',', '.') . ")";
                } elseif ($para_birimi === 'EUR') {
                    $final_tl_amount = $amount_in_foreign_currency * $euro_kuru;
                    $exchange_rate_info = " (" . number_format($amount_in_foreign_currency, 2, ',', '.') . " EUR @ " . number_format($euro_kuru, 4, ',', '.') . ")";
                }
                
                $gider_aciklama .= $exchange_rate_info;

                $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');
                $gider_query = "INSERT INTO gider_yonetimi (tarih, kategori, tutar, odeme_tipi, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_yapilan_firma) VALUES ('$gider_tarih', 'Malzeme Gideri', $final_tl_amount, 'Diğer', '$gider_aciklama', $user_id, '$personel_adi', '$tedarikci_adi')";
                $connection->query($gider_query);
            }

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

    case 'make_payment':
        $sozlesme_id = $_POST['sozlesme_id'] ?? 0;
        $payment_quantity = $_POST['quantity'] ?? 0;

        if (!$sozlesme_id || !$payment_quantity || $payment_quantity <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz parametreler.']);
            break;
        }

        // Get contract details
        $contract_query = "SELECT * FROM cerceve_sozlesmeler WHERE sozlesme_id = $sozlesme_id";
        $contract_result = $connection->query($contract_query);

        if (!$contract_result || $contract_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Sözleşme bulunamadı.']);
            break;
        }

        $contract = $contract_result->fetch_assoc();
        
        // Calculate amount in foreign currency
        $amount_in_foreign_currency = $payment_quantity * $contract['birim_fiyat'];
        $para_birimi = $contract['para_birimi'];
        $tedarikci_adi = $contract['tedarikci_adi'];
        $malzeme_ismi = $contract['malzeme_ismi'];
        
        // Start transaction
        $connection->begin_transaction();

        try {
            // 1. Update contract paid amount
            $update_query = "UPDATE cerceve_sozlesmeler SET toplu_odenen_miktar = toplu_odenen_miktar + $payment_quantity WHERE sozlesme_id = $sozlesme_id";
            if (!$connection->query($update_query)) {
                throw new Exception("Sözleşme güncellenemedi: " . $connection->error);
            }

            // 2. Insert expense record
            $gider_tarih = date('Y-m-d');
            $gider_aciklama = "$malzeme_ismi için $payment_quantity adet ara ödeme";
            $user_id = $_SESSION['user_id'];
            
            // Get exchange rates
            $rates_query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
            $rates_result = $connection->query($rates_query);
            $rates_data = [];
            while ($row = $rates_result->fetch_assoc()) {
                $rates_data[$row['ayar_anahtar']] = $row['ayar_deger'];
            }
            $dolar_kuru = $rates_data['dolar_kuru'] ?? 1.0;
            $euro_kuru = $rates_data['euro_kuru'] ?? 1.0;
            
            $final_tl_amount = $amount_in_foreign_currency;
            $exchange_rate_info = "";

            if ($para_birimi === 'USD') {
                $final_tl_amount = $amount_in_foreign_currency * $dolar_kuru;
                $exchange_rate_info = " (" . number_format($amount_in_foreign_currency, 2, ',', '.') . " USD @ " . number_format($dolar_kuru, 4, ',', '.') . ")";
            } elseif ($para_birimi === 'EUR') {
                $final_tl_amount = $amount_in_foreign_currency * $euro_kuru;
                $exchange_rate_info = " (" . number_format($amount_in_foreign_currency, 2, ',', '.') . " EUR @ " . number_format($euro_kuru, 4, ',', '.') . ")";
            }
            
            $gider_aciklama .= $exchange_rate_info;

            $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');
            $gider_query = "INSERT INTO gider_yonetimi (tarih, kategori, tutar, odeme_tipi, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_yapilan_firma) VALUES ('$gider_tarih', 'Malzeme Gideri', $final_tl_amount, 'Diğer', '$gider_aciklama', $user_id, '$personel_adi', '$tedarikci_adi')";

            if (!$connection->query($gider_query)) {
                throw new Exception("Gider kaydı oluşturulamadı: " . $connection->error);
            }

            $connection->commit();
            echo json_encode(['status' => 'success', 'message' => 'Ödeme başarıyla gerçekleştirildi ve giderlere işlendi.']);

        } catch (Exception $e) {
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
        break;
}
?>

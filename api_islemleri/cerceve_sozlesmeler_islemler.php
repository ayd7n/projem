<?php
include '../config.php';
header('Content-Type: application/json; charset=utf-8');

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

function normalizeContractCurrency($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === 'TRY' || $currency === '') {
        $currency = 'TL';
    }
    return in_array($currency, ['TL', 'USD', 'EUR'], true) ? $currency : 'TL';
}

function getContractRates($connection)
{
    $rates = ['TL' => 1.0, 'USD' => 0.0, 'EUR' => 0.0];
    $rates_result = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
    if ($rates_result) {
        while ($row = $rates_result->fetch_assoc()) {
            if (($row['ayar_anahtar'] ?? '') === 'dolar_kuru') {
                $rates['USD'] = max(0.0, (float) ($row['ayar_deger'] ?? 0));
            } elseif (($row['ayar_anahtar'] ?? '') === 'euro_kuru') {
                $rates['EUR'] = max(0.0, (float) ($row['ayar_deger'] ?? 0));
            }
        }
    }
    return $rates;
}

function convertContractCurrencyAmount($amount, $fromCurrency, $toCurrency, $rates)
{
    $amount = (float) $amount;
    $from = normalizeContractCurrency($fromCurrency);
    $to = normalizeContractCurrency($toCurrency);
    if ($from === $to) {
        return $amount;
    }

    $usdRate = max(0.0, (float) ($rates['USD'] ?? 0));
    $eurRate = max(0.0, (float) ($rates['EUR'] ?? 0));
    if (($from === 'USD' || $to === 'USD') && $usdRate <= 0) {
        throw new Exception('USD kuru tanimli degil veya 0.');
    }
    if (($from === 'EUR' || $to === 'EUR') && $eurRate <= 0) {
        throw new Exception('EUR kuru tanimli degil veya 0.');
    }

    if ($from === 'TL') {
        $tlAmount = $amount;
    } elseif ($from === 'USD') {
        $tlAmount = $amount * $usdRate;
    } else {
        $tlAmount = $amount * $eurRate;
    }

    if ($to === 'TL') {
        return $tlAmount;
    }
    if ($to === 'USD') {
        return $tlAmount / $usdRate;
    }
    return $tlAmount / $eurRate;
}

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
        $query = "SELECT * FROM cerceve_sozlesmeler_gecerlilik ORDER BY sozlesme_id DESC";
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

    case 'get_valid_contracts':
        $query = "SELECT * FROM cerceve_sozlesmeler_gecerlilik WHERE gecerli_mi = 1 ORDER BY sozlesme_id DESC";
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

        if ($connection->query($query)) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$tedarikci_adi tedarikçisine $malzeme_ismi malzemesi için çerçeve sözleşme eklendi", 'CREATE');

            // Check if there is a pre-payment
            if ($toplu_odenen_miktar > 0) {
                $amount_in_foreign_currency = $toplu_odenen_miktar * $birim_fiyat;
                $gider_tarih = date('Y-m-d');
                $gider_aciklama = "$malzeme_ismi için $toplu_odenen_miktar adet ön ödeme";
                $user_id = $_SESSION['user_id'];

                $rates = getContractRates($connection);
                $final_tl_amount = convertContractCurrencyAmount($amount_in_foreign_currency, $para_birimi, 'TL', $rates);
                $exchange_rate_info = "";

                if ($para_birimi === 'USD') {
                    $dolar_kuru = (float) ($rates['USD'] ?? 0);
                    if ($dolar_kuru <= 0) {
                        throw new Exception("USD kuru tanimli degil veya 0.");
                    }
                    $exchange_rate_info = " (" . number_format($amount_in_foreign_currency, 2, ',', '.') . " USD @ " . number_format($dolar_kuru, 4, ',', '.') . ")";
                } elseif ($para_birimi === 'EUR') {
                    $euro_kuru = (float) ($rates['EUR'] ?? 0);
                    if ($euro_kuru <= 0) {
                        throw new Exception("EUR kuru tanimli degil veya 0.");
                    }
                    $exchange_rate_info = " (" . number_format($amount_in_foreign_currency, 2, ',', '.') . " EUR @ " . number_format($euro_kuru, 4, ',', '.') . ")";
                }
                
                $gider_aciklama .= $exchange_rate_info;

                $personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');
                $gider_query = "INSERT INTO gider_yonetimi (tarih, kategori, tutar, odeme_tipi, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_yapilan_firma) VALUES ('$gider_tarih', 'Malzeme Gideri', $final_tl_amount, 'Diğer', '$gider_aciklama', $user_id, '$personel_adi', '$tedarikci_adi')";
                if (!$connection->query($gider_query)) {
                    throw new Exception("Pesin odeme gider kaydi olusturulamadi: " . $connection->error);
                }
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
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$tedarikci_adi tedarikçisine $malzeme_ismi malzemesi için çerçeve sözleşme güncellendi", 'UPDATE');
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

        // Start transaction for atomic deletion
        $connection->begin_transaction();

        try {
            // Get contract info for logging before deleting
            $contract_query = "SELECT tedarikci_adi, malzeme_ismi FROM cerceve_sozlesmeler WHERE sozlesme_id = $sozlesme_id";
            $contract_result = $connection->query($contract_query);
            if (!$contract_result || $contract_result->num_rows === 0) {
                throw new Exception("Silinecek sözleşme bulunamadı.");
            }
            $contract = $contract_result->fetch_assoc();
            $tedarikci_adi = $contract['tedarikci_adi'] ?? 'Bilinmeyen Tedarikçi';
            $malzeme_ismi = $contract['malzeme_ismi'] ?? 'Bilinmeyen Malzeme';

            // 1. Delete related records from stok_hareketleri_sozlesmeler
            $delete_related_query = "DELETE FROM stok_hareketleri_sozlesmeler WHERE sozlesme_id = $sozlesme_id";
            if (!$connection->query($delete_related_query)) {
                throw new Exception("İlişkili stok hareketleri silinirken bir hata oluştu: " . $connection->error);
            }

            // 2. Delete the main contract
            $delete_main_query = "DELETE FROM cerceve_sozlesmeler WHERE sozlesme_id = $sozlesme_id";
            if (!$connection->query($delete_main_query)) {
                throw new Exception("Çerçeve sözleşme silinirken hata oluştu: " . $connection->error);
            }

            // If all queries succeed, commit the transaction
            $connection->commit();

            // Log the operation
            log_islem($connection, $_SESSION['kullanici_adi'], "$tedarikci_adi tedarikçisine ait $malzeme_ismi malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi", 'DELETE');
            
            echo json_encode(['status' => 'success', 'message' => 'Çerçeve sözleşme ve ilişkili tüm kayıtlar başarıyla silindi.']);

        } catch (Exception $e) {
            // If any query fails, roll back the transaction
            $connection->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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
        $movements_query = "SELECT shs.hareket_id, shs.kullanilan_miktar as miktar,
                           COALESCE(DATE_FORMAT(shs.tarih, '%d.%m.%Y %H:%i'), '-') as tarih,
                           CONCAT('Mal Kabul - ', shs.malzeme_kodu, ' (', 
                                  FORMAT(shs.kullanilan_miktar, 2), ' adet x ', 
                                  FORMAT(shs.birim_fiyat, 2), ' ', shs.para_birimi, ')') as aciklama
                           FROM stok_hareketleri_sozlesmeler shs
                           WHERE shs.sozlesme_id = $sozlesme_id
                           ORDER BY shs.tarih DESC, shs.hareket_id DESC";

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
        $kasa_secimi = normalizeContractCurrency($_POST['kasa_secimi'] ?? 'TL');
        $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? 'Havale/EFT');

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
        $para_birimi = normalizeContractCurrency($contract['para_birimi'] ?? 'TL');
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
            $rates = getContractRates($connection);
            $dolar_kuru = (float) ($rates['USD'] ?? 0);
            $euro_kuru = (float) ($rates['EUR'] ?? 0);

            $final_tl_amount = convertContractCurrencyAmount($amount_in_foreign_currency, $para_birimi, 'TL', $rates);
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
            $kasa_secimi_esc = $connection->real_escape_string($kasa_secimi);
            $gider_query = "INSERT INTO gider_yonetimi (tarih, kategori, tutar, odeme_tipi, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_yapilan_firma, kasa_secimi) VALUES ('$gider_tarih', 'Malzeme Gideri', $final_tl_amount, '$odeme_tipi', '$gider_aciklama', $user_id, '$personel_adi', '$tedarikci_adi', '$kasa_secimi_esc')";

            if (!$connection->query($gider_query)) {
                throw new Exception("Gider kaydı oluşturulamadı: " . $connection->error);
            }
            $gider_id = $connection->insert_id;

            // 3. Kasa bakiyesini düşür (kasa para birimine doğru dönüşüm ile)
            $dusulecek_tutar = convertContractCurrencyAmount($amount_in_foreign_currency, $para_birimi, $kasa_secimi, $rates);
            $bakiye_check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$kasa_secimi_esc' LIMIT 1");
            if (!$bakiye_check || $bakiye_check->num_rows === 0) {
                if (!$connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$kasa_secimi_esc', 0)")) {
                    throw new Exception("Kasa satiri olusturulamadi: " . $connection->error);
                }
                $bakiye_check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$kasa_secimi_esc' LIMIT 1");
            }

            $kasa_row = $bakiye_check ? $bakiye_check->fetch_assoc() : null;
            $mevcut_bakiye = (float) ($kasa_row['bakiye'] ?? 0);
            if ($mevcut_bakiye + 0.00001 < $dusulecek_tutar) {
                throw new Exception("Kasada yeterli bakiye yok.");
            }

            if (!$connection->query("UPDATE sirket_kasasi SET bakiye = bakiye - $dusulecek_tutar WHERE para_birimi = '$kasa_secimi_esc'")) {
                throw new Exception("Kasa bakiyesi guncellenemedi: " . $connection->error);
            }

            // 4. Kasa hareketi kaydet
            // Tutar: hareketin kasa para birimindeki gerçek düşüm tutarı
            $kayit_tutar = $dusulecek_tutar;
            
            $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_firma, odeme_tipi)
                VALUES ('$gider_tarih', 'gider_cikisi', '$kasa_secimi_esc', $kayit_tutar, '$kasa_secimi_esc', $final_tl_amount, 'cerceve_sozlesmeler', $sozlesme_id, '$gider_aciklama', '$personel_adi', '$tedarikci_adi', '$odeme_tipi')";
            if (!$connection->query($hareket_sql)) {
                throw new Exception("Kasa hareketi kaydedilemedi: " . $connection->error);
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

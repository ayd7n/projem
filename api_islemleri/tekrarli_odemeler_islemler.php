<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'get_tekrarli_odemeler':
            getTekrarliOdemeler();
            break;
        case 'get_aylik_odeme_durumu':
            getAylikOdemeDurumu();
            break;
        case 'add_tekrarli_odeme':
            addTekrarliOdeme();
            break;
        case 'update_tekrarli_odeme':
            updateTekrarliOdeme();
            break;
        case 'delete_tekrarli_odeme':
            deleteTekrarliOdeme();
            break;
        case 'kaydet_odeme':
            kaydetOdeme();
            break;
        case 'get_odeme_gecmisi':
            getOdemeGecmisi();
            break;
        default:
            echo json_encode($response);
    }
} else {
    echo json_encode($response);
}

function getTekrarliOdemeler()
{
    global $connection;

    try {
        $query = "SELECT * FROM tekrarli_odemeler ORDER BY odeme_gunu, odeme_adi";
        $result = $connection->query($query);

        if ($result) {
            $odemeler = [];
            while ($row = $result->fetch_assoc()) {
                $odemeler[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $odemeler]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ödemeler alınamadı: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function getAylikOdemeDurumu()
{
    global $connection;

    $yil = isset($_GET['yil']) ? (int) $_GET['yil'] : date('Y');
    $ay = isset($_GET['ay']) ? (int) $_GET['ay'] : date('n');
    $bugun = date('Y-m-d');
    $bugun_gun = (int) date('d');

    try {
        // Aktif tekrarlı ödemeleri getir
        $query = "SELECT * FROM tekrarli_odemeler WHERE aktif = 1 ORDER BY odeme_gunu, odeme_adi";
        $result = $connection->query($query);

        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Ödemeler alınamadı: ' . $connection->error]);
            return;
        }

        $odemeler = [];
        $toplam_tutar = 0;
        $odenen_tutar = 0;
        $bekleyen_tutar = 0;

        while ($row = $result->fetch_assoc()) {
            $odeme_id = $row['odeme_id'];

            // Bu ay için ödeme yapılmış mı kontrol et
            $gecmis_query = "SELECT * FROM tekrarli_odeme_gecmisi 
                            WHERE odeme_id = $odeme_id 
                            AND donem_yil = $yil 
                            AND donem_ay = $ay";
            $gecmis_result = $connection->query($gecmis_query);

            $toplam_tutar += $row['tutar'];

            if ($gecmis_result && $gecmis_result->num_rows > 0) {
                $gecmis = $gecmis_result->fetch_assoc();
                $row['odeme_durumu'] = 'odendi';
                $row['odeme_tarihi'] = $gecmis['odeme_tarihi'];
                $row['gecmis_id'] = $gecmis['gecmis_id'];
                $odenen_tutar += $row['tutar'];
            } else {
                // Ödeme günü geçmiş mi kontrol et (sadece bu ay için)
                if ($yil == date('Y') && $ay == date('n')) {
                    if ($bugun_gun > $row['odeme_gunu']) {
                        $row['odeme_durumu'] = 'gecikmiş';
                    } else {
                        $row['odeme_durumu'] = 'bekliyor';
                    }
                } else if ($yil < date('Y') || ($yil == date('Y') && $ay < date('n'))) {
                    // Geçmiş aylar için ödenmemişse gecikmiş
                    $row['odeme_durumu'] = 'gecikmiş';
                } else {
                    // Gelecek aylar için bekliyor
                    $row['odeme_durumu'] = 'bekliyor';
                }
                $bekleyen_tutar += $row['tutar'];
            }

            $odemeler[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $odemeler,
            'ozet' => [
                'toplam_tutar' => $toplam_tutar,
                'odenen_tutar' => $odenen_tutar,
                'bekleyen_tutar' => $bekleyen_tutar,
                'odeme_sayisi' => count($odemeler)
            ]
        ]);
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function addTekrarliOdeme()
{
    global $connection;

    $odeme_adi = $connection->real_escape_string($_POST['odeme_adi'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $odeme_gunu = (int) ($_POST['odeme_gunu'] ?? 1);
    $alici_firma = $connection->real_escape_string($_POST['alici_firma'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $aktif = isset($_POST['aktif']) && $_POST['aktif'] == '1' ? 1 : 0;
    $kaydeden_personel_id = $_SESSION['user_id'];
    $kaydeden_personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');

    if (empty($odeme_adi) || empty($odeme_tipi) || $tutar <= 0 || $odeme_gunu < 1 || $odeme_gunu > 31) {
        echo json_encode(['status' => 'error', 'message' => 'Gerekli alanlar eksik veya hatalı.']);
        return;
    }

    try {
        $query = "INSERT INTO tekrarli_odemeler 
                  (odeme_adi, odeme_tipi, tutar, odeme_gunu, alici_firma, aciklama, aktif, 
                   kaydeden_personel_id, kaydeden_personel_adi) 
                  VALUES 
                  ('$odeme_adi', '$odeme_tipi', $tutar, $odeme_gunu, '$alici_firma', '$aciklama', 
                   $aktif, $kaydeden_personel_id, '$kaydeden_personel_adi')";

        if ($connection->query($query)) {
            log_islem(
                $connection,
                $_SESSION['kullanici_adi'],
                "Yeni tekrarlı ödeme tanımlandı: $odeme_adi ($odeme_tipi) - $tutar TL",
                'CREATE'
            );

            echo json_encode(['status' => 'success', 'message' => 'Tekrarlı ödeme başarıyla tanımlandı.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ödeme tanımlanamadı: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function updateTekrarliOdeme()
{
    global $connection;

    $odeme_id = (int) ($_POST['odeme_id'] ?? 0);
    $odeme_adi = $connection->real_escape_string($_POST['odeme_adi'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $odeme_gunu = (int) ($_POST['odeme_gunu'] ?? 1);
    $alici_firma = $connection->real_escape_string($_POST['alici_firma'] ?? '');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $aktif = isset($_POST['aktif']) && $_POST['aktif'] == '1' ? 1 : 0;

    if ($odeme_id <= 0 || empty($odeme_adi) || empty($odeme_tipi) || $tutar <= 0 || $odeme_gunu < 1 || $odeme_gunu > 31) {
        echo json_encode(['status' => 'error', 'message' => 'Gerekli alanlar eksik veya hatalı.']);
        return;
    }

    try {
        $query = "UPDATE tekrarli_odemeler SET 
                  odeme_adi = '$odeme_adi',
                  odeme_tipi = '$odeme_tipi',
                  tutar = $tutar,
                  odeme_gunu = $odeme_gunu,
                  alici_firma = '$alici_firma',
                  aciklama = '$aciklama',
                  aktif = $aktif
                  WHERE odeme_id = $odeme_id";

        if ($connection->query($query)) {
            log_islem(
                $connection,
                $_SESSION['kullanici_adi'],
                "Tekrarlı ödeme güncellendi: $odeme_adi",
                'UPDATE'
            );

            echo json_encode(['status' => 'success', 'message' => 'Tekrarlı ödeme başarıyla güncellendi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ödeme güncellenemedi: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function deleteTekrarliOdeme()
{
    global $connection;

    $odeme_id = (int) ($_POST['odeme_id'] ?? 0);

    if ($odeme_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz ödeme ID.']);
        return;
    }

    try {
        // Önce ödeme adını alalım log için
        $query = "SELECT odeme_adi FROM tekrarli_odemeler WHERE odeme_id = $odeme_id";
        $result = $connection->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $odeme_adi = $row['odeme_adi'];

            $delete_query = "DELETE FROM tekrarli_odemeler WHERE odeme_id = $odeme_id";

            if ($connection->query($delete_query)) {
                log_islem(
                    $connection,
                    $_SESSION['kullanici_adi'],
                    "Tekrarlı ödeme silindi: $odeme_adi",
                    'DELETE'
                );

                echo json_encode(['status' => 'success', 'message' => 'Tekrarlı ödeme başarıyla silindi.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Ödeme silinemedi: ' . $connection->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ödeme bulunamadı.']);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function kaydetOdeme()
{
    global $connection;

    $odeme_id = (int) ($_POST['odeme_id'] ?? 0);
    $odeme_adi = $connection->real_escape_string($_POST['odeme_adi'] ?? '');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? '');
    $tutar = floatval($_POST['tutar'] ?? 0);
    $donem_yil = (int) ($_POST['donem_yil'] ?? date('Y'));
    $donem_ay = (int) ($_POST['donem_ay'] ?? date('n'));
    $odeme_tarihi = $connection->real_escape_string($_POST['odeme_tarihi'] ?? date('Y-m-d'));
    $odeme_yontemi = $connection->real_escape_string($_POST['odeme_yontemi'] ?? 'Havale');
    $kasa_secimi = $connection->real_escape_string($_POST['kasa_secimi'] ?? 'TL');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $kaydeden_personel_id = $_SESSION['user_id'];
    $kaydeden_personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');
    $alici_firma = $connection->real_escape_string($_POST['alici_firma'] ?? '');

    if ($odeme_id <= 0 || empty($odeme_adi) || $tutar <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gerekli alanlar eksik veya hatalı.']);
        return;
    }

    // Aynı dönem için ödeme yapılmış mı kontrol et
    $check_query = "SELECT gecmis_id FROM tekrarli_odeme_gecmisi 
                    WHERE odeme_id = $odeme_id AND donem_yil = $donem_yil AND donem_ay = $donem_ay";
    $check_result = $connection->query($check_query);

    if ($check_result && $check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Bu ödeme için bu dönemde zaten kayıt yapılmış.']);
        return;
    }

    $connection->begin_transaction();

    try {
        // 1. Gider kaydı oluştur
        $gider_kategori = $odeme_tipi; // Ödeme tipi direkt kategori olarak kullanılacak
        $gider_aciklama = "$odeme_adi - $donem_yil/$donem_ay dönemi. $aciklama";

        $gider_query = "INSERT INTO gider_yonetimi 
                        (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, 
                         odeme_tipi, odeme_yapilan_firma, kasa_secimi) 
                        VALUES 
                        ('$odeme_tarihi', $tutar, '$gider_kategori', '$gider_aciklama', 
                         $kaydeden_personel_id, '$kaydeden_personel_adi', '$odeme_yontemi', 
                         '$alici_firma', '$kasa_secimi')";

        if (!$connection->query($gider_query)) {
            throw new Exception('Gider kaydı oluşturulamadı: ' . $connection->error);
        }

        $gider_kayit_id = $connection->insert_id;

        // 2. Ödeme geçmişi kaydı oluştur
        $gecmis_query = "INSERT INTO tekrarli_odeme_gecmisi 
                        (odeme_id, odeme_adi, odeme_tipi, tutar, donem_yil, donem_ay, odeme_tarihi, 
                         odeme_yontemi, aciklama, kaydeden_personel_id, kaydeden_personel_adi, gider_kayit_id) 
                        VALUES 
                        ($odeme_id, '$odeme_adi', '$odeme_tipi', $tutar, $donem_yil, $donem_ay, 
                         '$odeme_tarihi', '$odeme_yontemi', '$aciklama', $kaydeden_personel_id, 
                         '$kaydeden_personel_adi', $gider_kayit_id)";

        if (!$connection->query($gecmis_query)) {
            throw new Exception('Ödeme geçmişi kaydı oluşturulamadı: ' . $connection->error);
        }

        // Döviz kurlarını çek
        $rates = ['TL' => 1, 'USD' => 1, 'EUR' => 1];
        $rate_query = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
        while ($row = $rate_query->fetch_assoc()) {
            if ($row['ayar_anahtar'] === 'dolar_kuru') $rates['USD'] = floatval($row['ayar_deger']);
            if ($row['ayar_anahtar'] === 'euro_kuru') $rates['EUR'] = floatval($row['ayar_deger']);
        }

        // Ödenecek tutar TL (tutar). Seçilen kasa döviz ise, kasadan düşülecek miktarı hesapla.
        $dusulecek_miktar = $tutar;
        if ($kasa_secimi === 'USD') {
            $dusulecek_miktar = $tutar / $rates['USD'];
        } elseif ($kasa_secimi === 'EUR') {
            $dusulecek_miktar = $tutar / $rates['EUR'];
        }

        // 3. Kasa bakiyesini düşür
        if (in_array($kasa_secimi, ['TL', 'USD', 'EUR'])) {
            $bakiye_check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$kasa_secimi'");
            if ($bakiye_check->num_rows > 0) {
                $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye - $dusulecek_miktar WHERE para_birimi = '$kasa_secimi'");
            }
        }

        // 4. Kasa hareketi kaydet
        $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_firma, odeme_tipi)
            VALUES ('$odeme_tarihi', 'gider_cikisi', '$kasa_secimi', $dusulecek_miktar, '$kasa_secimi', $tutar, 'tekrarli_odeme_gecmisi', $odeme_id, '$gider_aciklama', '$kaydeden_personel_adi', '$alici_firma', '$odeme_yontemi')";
        $connection->query($hareket_sql);

        $connection->commit();

        // Log kaydı
        log_islem(
            $connection,
            $_SESSION['kullanici_adi'],
            "$odeme_adi ödemesi yapıldı ($donem_yil/$donem_ay) - $tutar TL",
            'CREATE'
        );

        echo json_encode(['status' => 'success', 'message' => 'Ödeme başarıyla kaydedildi.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getOdemeGecmisi()
{
    global $connection;

    $odeme_id = isset($_GET['odeme_id']) ? (int) $_GET['odeme_id'] : 0;

    if ($odeme_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz ödeme ID.']);
        return;
    }

    try {
        $query = "SELECT * FROM tekrarli_odeme_gecmisi 
                  WHERE odeme_id = $odeme_id 
                  ORDER BY donem_yil DESC, donem_ay DESC";

        $result = $connection->query($query);

        if ($result) {
            $gecmis = [];
            while ($row = $result->fetch_assoc()) {
                $gecmis[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $gecmis]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ödeme geçmişi alınamadı: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

$connection->close();
?>
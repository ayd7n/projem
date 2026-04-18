<?php
header('Content-Type: application/json');

try {
    include '../config.php';
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sistem baslatilamadi. Veritabani baglantisini kontrol edin.'
    ]);
    exit;
}

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erisim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Gecersiz istek.'];

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    try {
        switch ($action) {
            case 'get_bordrolu_personeller':
                getBordroPersoneller();
                break;
            case 'get_aylik_bordro_ozeti':
                getAylikBordroOzeti();
                break;
            case 'get_personel_odeme_gecmisi':
                getPersonelOdemeGecmisi();
                break;
            case 'get_personel_avanslar':
                getPersonelAvanslar();
                break;
            case 'kaydet_maas_odemesi':
                kaydetMaasOdemesi();
                break;
            case 'kaydet_avans':
                kaydetAvans();
                break;
            case 'get_donem_avanslar':
                getDonemAvanslar();
                break;
            default:
                echo json_encode($response);
        }
    } catch (Throwable $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Islem sirasinda beklenmeyen bir hata olustu.'
        ]);
    }
} else {
    echo json_encode($response);
}

function normalizePayrollCurrency($currency)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === 'TRY' || $currency === '') {
        $currency = 'TL';
    }
    return in_array($currency, ['TL', 'USD', 'EUR'], true) ? $currency : 'TL';
}

function getPayrollRates($connection)
{
    $rates = ['TL' => 1.0, 'USD' => 0.0, 'EUR' => 0.0];
    $rate_query = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
    if ($rate_query) {
        while ($row = $rate_query->fetch_assoc()) {
            if (($row['ayar_anahtar'] ?? '') === 'dolar_kuru') {
                $rates['USD'] = max(0.0, (float) ($row['ayar_deger'] ?? 0));
            }
            if (($row['ayar_anahtar'] ?? '') === 'euro_kuru') {
                $rates['EUR'] = max(0.0, (float) ($row['ayar_deger'] ?? 0));
            }
        }
    }
    return $rates;
}

function convertPayrollTlToCashCurrency($amountTl, $currency, $rates)
{
    $currency = normalizePayrollCurrency($currency);
    $amountTl = (float) $amountTl;
    if ($currency === 'TL') {
        return $amountTl;
    }

    $rate = (float) ($rates[$currency] ?? 0);
    if ($rate <= 0) {
        throw new Exception($currency . ' kuru tanimli degil veya 0.');
    }
    return $amountTl / $rate;
}

function withdrawPayrollCash($connection, $currency, $amountTl)
{
    $currency = normalizePayrollCurrency($currency);
    $rates = getPayrollRates($connection);
    $cashAmount = convertPayrollTlToCashCurrency($amountTl, $currency, $rates);
    $currencyEsc = $connection->real_escape_string($currency);

    $bakiye_check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$currencyEsc' LIMIT 1");
    if (!$bakiye_check || $bakiye_check->num_rows === 0) {
        if (!$connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$currencyEsc', 0)")) {
            throw new Exception('Kasa satiri olusturulamadi: ' . $connection->error);
        }
        $bakiye_check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$currencyEsc' LIMIT 1");
    }

    $kasa_row = $bakiye_check ? $bakiye_check->fetch_assoc() : null;
    $mevcut_bakiye = (float) ($kasa_row['bakiye'] ?? 0);
    if ($mevcut_bakiye + 0.00001 < $cashAmount) {
        throw new Exception('Kasada yeterli bakiye yok.');
    }

    if (!$connection->query("UPDATE sirket_kasasi SET bakiye = bakiye - $cashAmount WHERE para_birimi = '$currencyEsc'")) {
        throw new Exception('Kasa bakiyesi guncellenemedi: ' . $connection->error);
    }

    return [$currency, $cashAmount];
}

function getBordroPersoneller()
{
    global $connection;

    try {
        $query = "SELECT personel_id, ad_soyad, pozisyon, departman, aylik_brut_ucret 
                  FROM personeller 
                  WHERE bordrolu_calisan_mi = 1 
                  ORDER BY ad_soyad";

        $result = $connection->query($query);

        if ($result) {
            $personeller = [];
            while ($row = $result->fetch_assoc()) {
                $personeller[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $personeller]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Personeller alinamadi: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabani hatasi: ' . $e->getMessage()]);
    }
}

function getAylikBordroOzeti()
{
    global $connection;

    $yil = isset($_GET['yil']) ? (int) $_GET['yil'] : date('Y');
    $ay = isset($_GET['ay']) ? (int) $_GET['ay'] : date('n');

    try {
        // Bordrolu personelleri ve aylik avans/odeme ozetlerini getir
        $query = "SELECT
                    p.personel_id,
                    p.ad_soyad,
                    p.pozisyon,
                    p.departman,
                    p.aylik_brut_ucret,
                    COALESCE(a.avans_toplami, 0) as avans_toplami,
                    (p.aylik_brut_ucret - COALESCE(a.avans_toplami, 0)) as net_odenecek,
                    o.odeme_id,
                    COALESCE(o.net_odenen, 0) as net_odenen,
                    COALESCE(o.kullanilan_avans, 0) as kullanilan_avans,
                    o.odeme_tarihi
                  FROM personeller p
                  LEFT JOIN (
                      SELECT
                          personel_id,
                          SUM(avans_tutari) as avans_toplami
                      FROM personel_avanslar
                      WHERE donem_yil = $yil
                        AND donem_ay = $ay
                        AND maas_odemesinde_kullanildi = 0
                      GROUP BY personel_id
                  ) a ON p.personel_id = a.personel_id
                  LEFT JOIN (
                      SELECT
                          personel_id,
                          MAX(odeme_id) as odeme_id,
                          SUM(net_odenen) as net_odenen,
                          SUM(avans_toplami) as kullanilan_avans,
                          MAX(odeme_tarihi) as odeme_tarihi
                      FROM personel_maas_odemeleri
                      WHERE donem_yil = $yil
                        AND donem_ay = $ay
                      GROUP BY personel_id
                  ) o ON p.personel_id = o.personel_id
                  WHERE p.bordrolu_calisan_mi = 1
                  ORDER BY p.ad_soyad";

        $result = $connection->query($query);

        if ($result) {
            $bordro = [];
            $toplam_brut = 0;
            $toplam_odenen = 0;
            $toplam_kalan = 0;

            // Once bu donemde verilen toplam avanslari hesapla (gider olarak kaydedilmis olanlar)
            $avans_query = "SELECT COALESCE(SUM(avans_tutari), 0) as toplam_avans 
                           FROM personel_avanslar 
                           WHERE donem_yil = $yil AND donem_ay = $ay";
            $avans_result = $connection->query($avans_query);
            $toplam_verilen_avans = 0;
            if ($avans_result && $avans_row = $avans_result->fetch_assoc()) {
                $toplam_verilen_avans = floatval($avans_row['toplam_avans']);
            }

            while ($row = $result->fetch_assoc()) {
                $toplam_brut += $row['aylik_brut_ucret'];

                if ($row['odeme_id']) {
                    $row['odeme_durumu'] = 'odendi';
                    // Maas odemesi yapildiginda: net_odenen + kullanilan avans = brut ucret
                    $toplam_odenen += $row['net_odenen'];
                } else {
                    $row['odeme_durumu'] = 'bekliyor';
                    $toplam_kalan += $row['net_odenecek'];
                }

                $bordro[] = $row;
            }

            // Toplam odenen = maas odemeleri + verilen avanslar
            $gercek_toplam_odenen = $toplam_odenen + $toplam_verilen_avans;

            echo json_encode([
                'status' => 'success',
                'data' => $bordro,
                'ozet' => [
                    'toplam_brut' => $toplam_brut,
                    'toplam_odenen' => $gercek_toplam_odenen,
                    'toplam_avans' => $toplam_verilen_avans,
                    'toplam_maas' => $toplam_odenen,
                    'toplam_kalan' => $toplam_kalan,
                    'personel_sayisi' => count($bordro)
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Bordro ozeti alinamadi: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabani hatasi: ' . $e->getMessage()]);
    }
}

function getPersonelOdemeGecmisi()
{
    global $connection;

    $personel_id = isset($_GET['personel_id']) ? (int) $_GET['personel_id'] : 0;

    if ($personel_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gecersiz personel ID.']);
        return;
    }

    try {
        $query = "SELECT * FROM personel_maas_odemeleri 
                  WHERE personel_id = $personel_id 
                  ORDER BY donem_yil DESC, donem_ay DESC";

        $result = $connection->query($query);

        if ($result) {
            $odemeler = [];
            while ($row = $result->fetch_assoc()) {
                $odemeler[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $odemeler]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Odeme gecmisi alinamadi: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabani hatasi: ' . $e->getMessage()]);
    }
}

function getPersonelAvanslar()
{
    global $connection;

    $personel_id = isset($_GET['personel_id']) ? (int) $_GET['personel_id'] : 0;

    if ($personel_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gecersiz personel ID.']);
        return;
    }

    try {
        $query = "SELECT * FROM personel_avanslar 
                  WHERE personel_id = $personel_id 
                  ORDER BY donem_yil DESC, donem_ay DESC, avans_tarihi DESC";

        $result = $connection->query($query);

        if ($result) {
            $avanslar = [];
            while ($row = $result->fetch_assoc()) {
                $avanslar[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $avanslar]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Avanslar alinamadi: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabani hatasi: ' . $e->getMessage()]);
    }
}

function kaydetMaasOdemesi()
{
    global $connection;

    $personel_id = isset($_POST['personel_id']) ? (int) $_POST['personel_id'] : 0;
    $personel_adi = $connection->real_escape_string($_POST['personel_adi'] ?? '');
    $donem_yil = isset($_POST['donem_yil']) ? (int) $_POST['donem_yil'] : date('Y');
    $donem_ay = isset($_POST['donem_ay']) ? (int) $_POST['donem_ay'] : date('n');
    $aylik_brut_ucret = floatval($_POST['aylik_brut_ucret'] ?? 0);
    $avans_toplami = floatval($_POST['avans_toplami'] ?? 0);
    $net_odenen = floatval($_POST['net_odenen'] ?? 0);
    $odeme_tarihi = $connection->real_escape_string($_POST['odeme_tarihi'] ?? date('Y-m-d'));
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? 'Havale');
    $kasa_secimi = normalizePayrollCurrency($_POST['kasa_secimi'] ?? 'TL');
    $kasa_secimi_esc = $connection->real_escape_string($kasa_secimi);
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $kaydeden_personel_id = $_SESSION['user_id'];
    $kaydeden_personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');

    if ($personel_id <= 0 || empty($personel_adi) || $aylik_brut_ucret <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gerekli alanlar eksik veya hatali.']);
        return;
    }

    $check_query = "SELECT odeme_id FROM personel_maas_odemeleri 
                    WHERE personel_id = $personel_id AND donem_yil = $donem_yil AND donem_ay = $donem_ay";
    $check_result = $connection->query($check_query);

    if ($check_result && $check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Bu personel icin bu donemde zaten maas odemesi yapilmis.']);
        return;
    }

    $connection->begin_transaction();

    try {
        $gider_query = "INSERT INTO gider_yonetimi 
                        (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_tipi, odeme_yapilan_firma, kasa_secimi) 
                        VALUES 
                        ('$odeme_tarihi', $net_odenen, 'Personel Gideri', 
                         '$personel_adi - $donem_yil/$donem_ay donemi maas odemesi. $aciklama', 
                         $kaydeden_personel_id, '$kaydeden_personel_adi', '$odeme_tipi', '$personel_adi', '$kasa_secimi_esc')";

        if (!$connection->query($gider_query)) {
            throw new Exception('Gider kaydi olusturulamadi: ' . $connection->error);
        }

        $gider_kayit_id = $connection->insert_id;

        $odeme_query = "INSERT INTO personel_maas_odemeleri 
                        (personel_id, personel_adi, donem_yil, donem_ay, aylik_brut_ucret, avans_toplami, 
                         net_odenen, odeme_tarihi, odeme_tipi, aciklama, kaydeden_personel_id, 
                         kaydeden_personel_adi, gider_kayit_id) 
                        VALUES 
                        ($personel_id, '$personel_adi', $donem_yil, $donem_ay, $aylik_brut_ucret, $avans_toplami, 
                         $net_odenen, '$odeme_tarihi', '$odeme_tipi', '$aciklama', $kaydeden_personel_id, 
                         '$kaydeden_personel_adi', $gider_kayit_id)";

        if (!$connection->query($odeme_query)) {
            throw new Exception('Maas odeme kaydi olusturulamadi: ' . $connection->error);
        }
        $odeme_kayit_id = (int) $connection->insert_id;

        if ($avans_toplami > 0) {
            $avans_update = "UPDATE personel_avanslar 
                            SET maas_odemesinde_kullanildi = 1 
                            WHERE personel_id = $personel_id 
                            AND donem_yil = $donem_yil 
                            AND donem_ay = $donem_ay 
                            AND maas_odemesinde_kullanildi = 0";

            if (!$connection->query($avans_update)) {
                throw new Exception('Avans kayitlari guncellenemedi: ' . $connection->error);
            }
        }

        [$kasa_para_birimi, $dusulecek_miktar] = withdrawPayrollCash($connection, $kasa_secimi, $net_odenen);
        $kasa_secimi_esc = $connection->real_escape_string($kasa_para_birimi);

        $hareket_aciklama = "$personel_adi - $donem_yil/$donem_ay donemi maas odemesi. $aciklama";
        $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_firma, odeme_tipi)
            VALUES ('$odeme_tarihi', 'personel_odemesi', '$kasa_secimi_esc', $dusulecek_miktar, '$kasa_secimi_esc', $net_odenen, 'personel_maas_odemeleri', $odeme_kayit_id, '$hareket_aciklama', '$kaydeden_personel_adi', '$personel_adi', '$odeme_tipi')";
        if (!$connection->query($hareket_sql)) {
            throw new Exception('Kasa hareketi kaydedilemedi: ' . $connection->error);
        }

        $connection->commit();

        log_islem(
            $connection,
            $_SESSION['kullanici_adi'],
            "$personel_adi personeline $donem_yil/$donem_ay donemi icin $net_odenen TL maas odemesi yapildi",
            'CREATE'
        );

        echo json_encode(['status' => 'success', 'message' => 'Maas odemesi basariyla kaydedildi.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
function kaydetAvans()
{
    global $connection;

    $personel_id = isset($_POST['personel_id']) ? (int) $_POST['personel_id'] : 0;
    $personel_adi = $connection->real_escape_string($_POST['personel_adi'] ?? '');
    $avans_tutari = floatval($_POST['avans_tutari'] ?? 0);
    $avans_tarihi = $connection->real_escape_string($_POST['avans_tarihi'] ?? date('Y-m-d'));
    $donem_yil = isset($_POST['donem_yil']) ? (int) $_POST['donem_yil'] : date('Y');
    $donem_ay = isset($_POST['donem_ay']) ? (int) $_POST['donem_ay'] : date('n');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? 'Nakit');
    $kasa_secimi = normalizePayrollCurrency($_POST['kasa_secimi'] ?? 'TL');
    $kasa_secimi_esc = $connection->real_escape_string($kasa_secimi);
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $kaydeden_personel_id = $_SESSION['user_id'];
    $kaydeden_personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');

    if ($personel_id <= 0 || empty($personel_adi) || $avans_tutari <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gerekli alanlar eksik veya hatali.']);
        return;
    }

    $connection->begin_transaction();

    try {
        $gider_aciklama = "$personel_adi - $donem_yil/$donem_ay donemi avans odemesi. $aciklama";
        $gider_query = "INSERT INTO gider_yonetimi 
                        (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_tipi, odeme_yapilan_firma, kasa_secimi) 
                        VALUES 
                        ('$avans_tarihi', $avans_tutari, 'Personel Avansi', '$gider_aciklama', 
                         $kaydeden_personel_id, '$kaydeden_personel_adi', '$odeme_tipi', '$personel_adi', '$kasa_secimi_esc')";

        if (!$connection->query($gider_query)) {
            throw new Exception('Gider kaydi olusturulamadi: ' . $connection->error);
        }

        $query = "INSERT INTO personel_avanslar 
                  (personel_id, personel_adi, avans_tutari, avans_tarihi, donem_yil, donem_ay, 
                   odeme_tipi, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                  VALUES 
                  ($personel_id, '$personel_adi', $avans_tutari, '$avans_tarihi', $donem_yil, $donem_ay, 
                   '$odeme_tipi', '$aciklama', $kaydeden_personel_id, '$kaydeden_personel_adi')";

        if (!$connection->query($query)) {
            throw new Exception('Avans kaydi olusturulamadi: ' . $connection->error);
        }

        $avans_kayit_id = (int) $connection->insert_id;

        [$kasa_para_birimi, $dusulecek_miktar] = withdrawPayrollCash($connection, $kasa_secimi, $avans_tutari);
        $kasa_secimi_esc = $connection->real_escape_string($kasa_para_birimi);

        $hareket_sql = "INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, tutar, para_birimi, tl_karsiligi, kaynak_tablo, kaynak_id, aciklama, kaydeden_personel, ilgili_firma, odeme_tipi)
            VALUES ('$avans_tarihi', 'personel_avansi', '$kasa_secimi_esc', $dusulecek_miktar, '$kasa_secimi_esc', $avans_tutari, 'personel_avanslar', $avans_kayit_id, '$gider_aciklama', '$kaydeden_personel_adi', '$personel_adi', '$odeme_tipi')";
        if (!$connection->query($hareket_sql)) {
            throw new Exception('Kasa hareketi kaydedilemedi: ' . $connection->error);
        }

        $connection->commit();

        log_islem(
            $connection,
            $_SESSION['kullanici_adi'],
            "$personel_adi personeline $avans_tutari TL avans verildi ($donem_yil/$donem_ay)",
            'CREATE'
        );

        echo json_encode(['status' => 'success', 'message' => 'Avans basariyla kaydedildi.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
function getDonemAvanslar()
{
    global $connection;

    $personel_id = isset($_GET['personel_id']) ? (int) $_GET['personel_id'] : 0;
    $yil = isset($_GET['yil']) ? (int) $_GET['yil'] : date('Y');
    $ay = isset($_GET['ay']) ? (int) $_GET['ay'] : date('n');

    if ($personel_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gecersiz personel ID.']);
        return;
    }

    try {
        $query = "SELECT * FROM personel_avanslar 
                  WHERE personel_id = $personel_id 
                  AND donem_yil = $yil 
                  AND donem_ay = $ay 
                  AND maas_odemesinde_kullanildi = 0
                  ORDER BY avans_tarihi DESC";

        $result = $connection->query($query);

        if ($result) {
            $avanslar = [];
            $toplam = 0;
            while ($row = $result->fetch_assoc()) {
                $avanslar[] = $row;
                $toplam += $row['avans_tutari'];
            }
            echo json_encode(['status' => 'success', 'data' => $avanslar, 'toplam' => $toplam]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Avanslar alinamadi: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabani hatasi: ' . $e->getMessage()]);
    }
}

$connection->close();
?>

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
} else {
    echo json_encode($response);
}

function getBordroPersoneller() {
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
            echo json_encode(['status' => 'error', 'message' => 'Personeller alınamadı: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function getAylikBordroOzeti() {
    global $connection;

    $yil = isset($_GET['yil']) ? (int)$_GET['yil'] : date('Y');
    $ay = isset($_GET['ay']) ? (int)$_GET['ay'] : date('n');

    try {
        // Bordrolu personelleri ve avans bilgilerini getir
        $query = "SELECT 
                    p.personel_id,
                    p.ad_soyad,
                    p.pozisyon,
                    p.departman,
                    p.aylik_brut_ucret,
                    COALESCE(SUM(a.avans_tutari), 0) as avans_toplami,
                    (p.aylik_brut_ucret - COALESCE(SUM(a.avans_tutari), 0)) as net_odenecek,
                    o.odeme_id,
                    o.net_odenen,
                    o.odeme_tarihi
                  FROM personeller p
                  LEFT JOIN personel_avanslar a ON p.personel_id = a.personel_id 
                    AND a.donem_yil = $yil 
                    AND a.donem_ay = $ay
                    AND a.maas_odemesinde_kullanildi = 0
                  LEFT JOIN personel_maas_odemeleri o ON p.personel_id = o.personel_id 
                    AND o.donem_yil = $yil 
                    AND o.donem_ay = $ay
                  WHERE p.bordrolu_calisan_mi = 1
                  GROUP BY p.personel_id
                  ORDER BY p.ad_soyad";
        
        $result = $connection->query($query);
        
        if ($result) {
            $bordro = [];
            $toplam_brut = 0;
            $toplam_odenen = 0;
            $toplam_kalan = 0;
            
            while ($row = $result->fetch_assoc()) {
                $toplam_brut += $row['aylik_brut_ucret'];
                
                if ($row['odeme_id']) {
                    $row['odeme_durumu'] = 'odendi';
                    $toplam_odenen += $row['net_odenen'];
                } else {
                    $row['odeme_durumu'] = 'bekliyor';
                    $toplam_kalan += $row['net_odenecek'];
                }
                
                $bordro[] = $row;
            }
            
            echo json_encode([
                'status' => 'success', 
                'data' => $bordro,
                'ozet' => [
                    'toplam_brut' => $toplam_brut,
                    'toplam_odenen' => $toplam_odenen,
                    'toplam_kalan' => $toplam_kalan,
                    'personel_sayisi' => count($bordro)
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Bordro özeti alınamadı: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function getPersonelOdemeGecmisi() {
    global $connection;

    $personel_id = isset($_GET['personel_id']) ? (int)$_GET['personel_id'] : 0;

    if ($personel_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz personel ID.']);
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
            echo json_encode(['status' => 'error', 'message' => 'Ödeme geçmişi alınamadı: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function getPersonelAvanslar() {
    global $connection;

    $personel_id = isset($_GET['personel_id']) ? (int)$_GET['personel_id'] : 0;

    if ($personel_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz personel ID.']);
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
            echo json_encode(['status' => 'error', 'message' => 'Avanslar alınamadı: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function kaydetMaasOdemesi() {
    global $connection;

    $personel_id = isset($_POST['personel_id']) ? (int)$_POST['personel_id'] : 0;
    $personel_adi = $connection->real_escape_string($_POST['personel_adi'] ?? '');
    $donem_yil = isset($_POST['donem_yil']) ? (int)$_POST['donem_yil'] : date('Y');
    $donem_ay = isset($_POST['donem_ay']) ? (int)$_POST['donem_ay'] : date('n');
    $aylik_brut_ucret = floatval($_POST['aylik_brut_ucret'] ?? 0);
    $avans_toplami = floatval($_POST['avans_toplami'] ?? 0);
    $net_odenen = floatval($_POST['net_odenen'] ?? 0);
    $odeme_tarihi = $connection->real_escape_string($_POST['odeme_tarihi'] ?? date('Y-m-d'));
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? 'Havale');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $kaydeden_personel_id = $_SESSION['user_id'];
    $kaydeden_personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');

    if ($personel_id <= 0 || empty($personel_adi) || $aylik_brut_ucret <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gerekli alanlar eksik veya hatalı.']);
        return;
    }

    // Aynı dönem için ödeme yapılmış mı kontrol et
    $check_query = "SELECT odeme_id FROM personel_maas_odemeleri 
                    WHERE personel_id = $personel_id AND donem_yil = $donem_yil AND donem_ay = $donem_ay";
    $check_result = $connection->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Bu personel için bu dönemde zaten maaş ödemesi yapılmış.']);
        return;
    }

    $connection->begin_transaction();

    try {
        // 1. Gider kaydı oluştur
        $gider_query = "INSERT INTO gider_yonetimi 
                        (tarih, tutar, kategori, aciklama, kaydeden_personel_id, kaydeden_personel_ismi, odeme_tipi, odeme_yapilan_firma) 
                        VALUES 
                        ('$odeme_tarihi', $net_odenen, 'Personel Gideri', 
                         '$personel_adi - $donem_yil/$donem_ay dönemi maaş ödemesi. $aciklama', 
                         $kaydeden_personel_id, '$kaydeden_personel_adi', '$odeme_tipi', '$personel_adi')";
        
        if (!$connection->query($gider_query)) {
            throw new Exception('Gider kaydı oluşturulamadı: ' . $connection->error);
        }
        
        $gider_kayit_id = $connection->insert_id;

        // 2. Maaş ödeme kaydı oluştur
        $odeme_query = "INSERT INTO personel_maas_odemeleri 
                        (personel_id, personel_adi, donem_yil, donem_ay, aylik_brut_ucret, avans_toplami, 
                         net_odenen, odeme_tarihi, odeme_tipi, aciklama, kaydeden_personel_id, 
                         kaydeden_personel_adi, gider_kayit_id) 
                        VALUES 
                        ($personel_id, '$personel_adi', $donem_yil, $donem_ay, $aylik_brut_ucret, $avans_toplami, 
                         $net_odenen, '$odeme_tarihi', '$odeme_tipi', '$aciklama', $kaydeden_personel_id, 
                         '$kaydeden_personel_adi', $gider_kayit_id)";
        
        if (!$connection->query($odeme_query)) {
            throw new Exception('Maaş ödeme kaydı oluşturulamadı: ' . $connection->error);
        }

        // 3. Avansları kullanıldı olarak işaretle
        if ($avans_toplami > 0) {
            $avans_update = "UPDATE personel_avanslar 
                            SET maas_odemesinde_kullanildi = 1 
                            WHERE personel_id = $personel_id 
                            AND donem_yil = $donem_yil 
                            AND donem_ay = $donem_ay 
                            AND maas_odemesinde_kullanildi = 0";
            
            if (!$connection->query($avans_update)) {
                throw new Exception('Avanslar güncellenemedi: ' . $connection->error);
            }
        }

        $connection->commit();

        // Log kaydı
        log_islem($connection, $_SESSION['kullanici_adi'], 
                  "$personel_adi personeline $donem_yil/$donem_ay dönemi için $net_odenen TL maaş ödemesi yapıldı", 
                  'CREATE');

        echo json_encode(['status' => 'success', 'message' => 'Maaş ödemesi başarıyla kaydedildi.']);

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function kaydetAvans() {
    global $connection;

    $personel_id = isset($_POST['personel_id']) ? (int)$_POST['personel_id'] : 0;
    $personel_adi = $connection->real_escape_string($_POST['personel_adi'] ?? '');
    $avans_tutari = floatval($_POST['avans_tutari'] ?? 0);
    $avans_tarihi = $connection->real_escape_string($_POST['avans_tarihi'] ?? date('Y-m-d'));
    $donem_yil = isset($_POST['donem_yil']) ? (int)$_POST['donem_yil'] : date('Y');
    $donem_ay = isset($_POST['donem_ay']) ? (int)$_POST['donem_ay'] : date('n');
    $odeme_tipi = $connection->real_escape_string($_POST['odeme_tipi'] ?? 'Nakit');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $kaydeden_personel_id = $_SESSION['user_id'];
    $kaydeden_personel_adi = $connection->real_escape_string($_SESSION['kullanici_adi'] ?? '');

    if ($personel_id <= 0 || empty($personel_adi) || $avans_tutari <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gerekli alanlar eksik veya hatalı.']);
        return;
    }

    try {
        $query = "INSERT INTO personel_avanslar 
                  (personel_id, personel_adi, avans_tutari, avans_tarihi, donem_yil, donem_ay, 
                   odeme_tipi, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                  VALUES 
                  ($personel_id, '$personel_adi', $avans_tutari, '$avans_tarihi', $donem_yil, $donem_ay, 
                   '$odeme_tipi', '$aciklama', $kaydeden_personel_id, '$kaydeden_personel_adi')";
        
        if ($connection->query($query)) {
            // Log kaydı
            log_islem($connection, $_SESSION['kullanici_adi'], 
                      "$personel_adi personeline $avans_tutari TL avans verildi ($donem_yil/$donem_ay)", 
                      'CREATE');

            echo json_encode(['status' => 'success', 'message' => 'Avans başarıyla kaydedildi.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Avans kaydedilemedi: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function getDonemAvanslar() {
    global $connection;

    $personel_id = isset($_GET['personel_id']) ? (int)$_GET['personel_id'] : 0;
    $yil = isset($_GET['yil']) ? (int)$_GET['yil'] : date('Y');
    $ay = isset($_GET['ay']) ? (int)$_GET['ay'] : date('n');

    if ($personel_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz personel ID.']);
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
            echo json_encode(['status' => 'error', 'message' => 'Avanslar alınamadı: ' . $connection->error]);
        }
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

$connection->close();
?>

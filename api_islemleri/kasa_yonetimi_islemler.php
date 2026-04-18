<?php
/**
 * Kasa YÃ¶netimi API Ä°ÅŸlemleri
 * Kasa bakiyeleri, Ã§ek iÅŸlemleri, kasa hareketleri ve istatistikler
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include '../config.php';

header('Content-Type: application/json; charset=utf-8');

// GiriÅŸ ve yetki kontrolÃ¼
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetki yok veya oturum kapalÄ±.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Action eksik.']);
    exit;
}

try {
    switch ($action) {
        // Kasa Ä°ÅŸlemleri
        case 'get_kasa_bakiyeleri': getKasaBakiyeleri(); break;
        case 'get_kasa_hareketleri': getKasaHareketleri(); break;
        case 'add_kasa_islemi': addKasaIslemi(); break;
        case 'delete_kasa_islemi': deleteKasaIslemi(); break;
        
        // Ã‡ek Ä°ÅŸlemleri
        case 'get_cekler': getCekler(); break;
        case 'add_cek': addCek(); break;
        case 'update_cek_durumu': updateCekDurumu(); break;
        case 'delete_cek': deleteCek(); break;
        
        // Ä°statistikler
        case 'get_stok_degerleri': getStokDegerleri(); break;
        case 'get_aylik_kasa_hareketleri': getAylikKasaHareketleri(); break;
        case 'get_tedarikci_odemeleri': getTedarikciOdemeleri(); break;
        case 'get_musteri_alacaklari': getMusteriAlacaklari(); break;
        case 'get_doviz_kurlari': getDovizKurlari(); break;
        case 'get_dashboard_summary': getDashboardSummary(); break;
        
        default: throw new Exception('GeÃ§ersiz action: ' . $action);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

/**
 * DÃ¶viz kurlarÄ±nÄ± ayarlar tablosundan al
 */
function getExchangeRates() {
    global $connection;
    $result = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
    $rates = ['TL' => 1.0, 'USD' => 0.0, 'EUR' => 0.0];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['ayar_anahtar'] === 'dolar_kuru') {
                $rates['USD'] = max(0.0, (float) $row['ayar_deger']);
            }
            if ($row['ayar_anahtar'] === 'euro_kuru') {
                $rates['EUR'] = max(0.0, (float) $row['ayar_deger']);
            }
        }
    }
    return $rates;
}

function normalizeKasaCurrency($currency) {
    $currency = strtoupper(trim((string) $currency));
    if ($currency === 'TRY' || $currency === '') {
        $currency = 'TL';
    }
    return in_array($currency, ['TL', 'USD', 'EUR'], true) ? $currency : 'TL';
}

function getKasaRateOrFail($rates, $currency) {
    $currency = normalizeKasaCurrency($currency);
    if ($currency === 'TL') {
        return 1.0;
    }

    $rate = (float) ($rates[$currency] ?? 0);
    if ($rate <= 0) {
        throw new Exception($currency . ' kuru tanimli degil veya 0.');
    }

    return $rate;
}

function convertKasaCurrencyAmount($amount, $fromCurrency, $toCurrency, $rates) {
    $amount = (float) $amount;
    if ($amount == 0.0) {
        return 0.0;
    }

    $from = normalizeKasaCurrency($fromCurrency);
    $to = normalizeKasaCurrency($toCurrency);
    if ($from === $to) {
        return $amount;
    }

    if ($from === 'TL') {
        $tlAmount = $amount;
    } else {
        $tlAmount = $amount * getKasaRateOrFail($rates, $from);
    }

    if ($to === 'TL') {
        return $tlAmount;
    }
    return $tlAmount / getKasaRateOrFail($rates, $to);
}

function fetchKasaSingleFloatOrFail($connection, $query, $field = 'toplam') {
    $result = $connection->query($query);
    if (!$result) {
        throw new Exception('Sorgu basarisiz: ' . $connection->error);
    }

    $row = $result->fetch_assoc();
    if (!$row) {
        return 0.0;
    }

    return (float) ($row[$field] ?? 0);
}

/**
 * DÃ¶viz kurlarÄ±nÄ± JSON olarak dÃ¶ndÃ¼r
 */
function getDovizKurlari() {
    $rates = getExchangeRates();
    echo json_encode(['status' => 'success', 'data' => $rates]);
}

/**
 * Kasa bakiyelerini getir (TL, USD, EUR + Ã‡ek KasasÄ± Ã¶zeti)
 */
function getKasaBakiyeleri() {
    global $connection;
    
    // Sirket kasasi bakiyeleri
    $result = $connection->query("SELECT para_birimi, bakiye FROM sirket_kasasi");
    $bakiyeler = ['TL' => 0, 'USD' => 0, 'EUR' => 0];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pb = normalizeKasaCurrency($row['para_birimi'] ?? 'TL');
            $bakiyeler[$pb] = floatval($row['bakiye']);
        }
    } else {
        throw new Exception('Kasa bakiyeleri alinamadi: ' . $connection->error);
    }
    
    // Ã‡ek kasasÄ± Ã¶zeti (sadece alindi ve tahsilde durumundaki Ã§ekler)
    $cekResult = $connection->query("
        SELECT 
            cek_para_birimi,
            COUNT(*) as adet,
            SUM(cek_tutari) as toplam
        FROM cek_kasasi 
        WHERE cek_tipi = 'alacak' AND cek_durumu IN ('alindi', 'tahsilde', 'kullanildi')
        GROUP BY cek_para_birimi
    ");
    
    $cekOzeti = ['adet' => 0, 'TL' => 0, 'USD' => 0, 'EUR' => 0];
    if ($cekResult) {
        while ($row = $cekResult->fetch_assoc()) {
            $pb = normalizeKasaCurrency($row['cek_para_birimi'] ?? 'TL');
            $cekOzeti[$pb] += floatval($row['toplam']);
            $cekOzeti['adet'] += intval($row['adet']);
        }
    } else {
        throw new Exception('Cek ozeti alinamadi: ' . $connection->error);
    }
    
    // TL karÅŸÄ±lÄ±ÄŸÄ± hesapla
    $rates = getExchangeRates();
    $cekOzeti['tl_karsiligi'] =
        $cekOzeti['TL']
        + convertKasaCurrencyAmount($cekOzeti['USD'], 'USD', 'TL', $rates)
        + convertKasaCurrencyAmount($cekOzeti['EUR'], 'EUR', 'TL', $rates);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'kasalar' => $bakiyeler,
            'cek_kasasi' => $cekOzeti,
            'kurlar' => $rates
        ]
    ]);
}

/**
 * Kasa hareketlerini listele (filtreleme ve sayfalama destekli)
 */
function getKasaHareketleri() {
    global $connection;
    
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = max(1, min(100, intval($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;
    
    // Filtreler
    $where = ["1=1"];
    $params = [];
    $types = "";
    
    if (!empty($_GET['kasa_adi'])) {
        $where[] = "kasa_adi = ?";
        $params[] = $_GET['kasa_adi'];
        $types .= "s";
    }
    
    if (!empty($_GET['islem_tipi'])) {
        $where[] = "islem_tipi = ?";
        $params[] = $_GET['islem_tipi'];
        $types .= "s";
    }
    
    if (!empty($_GET['kaynak_tablo'])) {
        $where[] = "kaynak_tablo = ?";
        $params[] = $_GET['kaynak_tablo'];
        $types .= "s";
    }
    
    if (!empty($_GET['baslangic_tarihi'])) {
        $where[] = "DATE(tarih) >= ?";
        $params[] = $_GET['baslangic_tarihi'];
        $types .= "s";
    }
    
    if (!empty($_GET['bitis_tarihi'])) {
        $where[] = "DATE(tarih) <= ?";
        $params[] = $_GET['bitis_tarihi'];
        $types .= "s";
    }
    
    if (!empty($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $where[] = "(aciklama LIKE ? OR ilgili_firma LIKE ? OR ilgili_musteri LIKE ? OR kaydeden_personel LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "ssss";
    }
    
    $whereClause = implode(" AND ", $where);
    
    // Toplam sayÄ±
    $countSql = "SELECT COUNT(*) as total FROM kasa_hareketleri WHERE $whereClause";
    $countStmt = $connection->prepare($countSql);
    if ($types) $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    
    // Veriler
    $dataSql = "SELECT * FROM kasa_hareketleri WHERE $whereClause ORDER BY tarih DESC, hareket_id DESC LIMIT ? OFFSET ?";
    $dataParams = array_merge($params, [$perPage, $offset]);
    $dataTypes = $types . "ii";
    
    $dataStmt = $connection->prepare($dataSql);
    $dataStmt->bind_param($dataTypes, ...$dataParams);
    $dataStmt->execute();
    $result = $dataStmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'total' => intval($total),
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage)
    ]);
}

/**
 * Kasa iÅŸlemi ekle (TL/USD/EUR kasasÄ±na ekleme veya Ã§Ä±karma)
 */
function addKasaIslemi() {
    global $connection;
    
    $tarih = $_POST['tarih'] ?? date('Y-m-d H:i:s');
    $tarih = str_replace('T', ' ', $tarih);
    if (strlen($tarih) == 16) $tarih .= ':00';
    
    $islemTipi = $_POST['islem_tipi'] ?? ''; // kasa_ekle, kasa_cikar
    $kasaAdi = normalizeKasaCurrency($_POST['kasa_adi'] ?? 'TL'); // TL, USD, EUR
    $tutar = floatval($_POST['tutar'] ?? 0);
    $aciklama = $_POST['aciklama'] ?? '';
    // Formdaki name="odeme_tipi_detay" ama db'de "odeme_tipi" olarak saklayacaÄŸÄ±z
    $odemeTipi = $_POST['odeme_tipi_detay'] ?? 'Nakit'; 
    $personel = $_SESSION['kullanici_adi'] ?? 'Sistem';
    
    if ($tutar <= 0) throw new Exception('GeÃ§ersiz tutar.');
    if (!in_array($kasaAdi, ['TL', 'USD', 'EUR'], true)) throw new Exception('GeÃ§ersiz kasa.');
    if (!in_array($islemTipi, ['kasa_ekle', 'kasa_cikar'])) throw new Exception('GeÃ§ersiz iÅŸlem tipi.');
    
    $rates = getExchangeRates();
    $dovizKuru = getKasaRateOrFail($rates, $kasaAdi);
    $tlKarsiligi = $tutar * $dovizKuru;
    
    $connection->begin_transaction();
    try {
        // Kasa bakiyesini kontrol et ve gÃ¼ncelle
        $check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$kasaAdi' LIMIT 1");
        if (!$check) {
            throw new Exception('Kasa sorgusu basarisiz: ' . $connection->error);
        }
        if ($check->num_rows == 0) {
            if (!$connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$kasaAdi', 0)")) {
                throw new Exception('Kasa satiri olusturulamadi: ' . $connection->error);
            }
            $bakiye = 0;
        } else {
            $bakiye = floatval($check->fetch_assoc()['bakiye']);
        }
        
        if ($islemTipi === 'kasa_ekle') {
            if (!$connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$kasaAdi'")) {
                throw new Exception('Kasa bakiyesi guncellenemedi: ' . $connection->error);
            }
        } else {
            if ($bakiye < $tutar) throw new Exception("Kasada yeterli bakiye yok! Mevcut: $bakiye $kasaAdi");
            if (!$connection->query("UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$kasaAdi'")) {
                throw new Exception('Kasa bakiyesi guncellenemedi: ' . $connection->error);
            }
        }
        
        // Kasa hareketleri tablosuna kaydet
        $stmt = $connection->prepare("
            INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, tutar, para_birimi, doviz_kuru, tl_karsiligi, aciklama, kaydeden_personel, odeme_tipi)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception('Kasa hareketi hazirlanamadi: ' . $connection->error);
        }
        $stmt->bind_param("sssdsddsss", $tarih, $islemTipi, $kasaAdi, $tutar, $kasaAdi, $dovizKuru, $tlKarsiligi, $aciklama, $personel, $odemeTipi);
        if (!$stmt->execute()) {
            throw new Exception('Kasa hareketi kaydedilemedi: ' . $stmt->error);
        }
        $stmt->close();
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Kasa iÅŸlemi baÅŸarÄ±yla kaydedildi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Kasa iÅŸlemini sil ve bakiyeyi geri al
 */
function deleteKasaIslemi() {
    global $connection;
    
    $hareketId = intval($_POST['hareket_id'] ?? 0);
    if ($hareketId <= 0) throw new Exception('GeÃ§ersiz hareket ID.');
    
    $connection->begin_transaction();
    try {
        // Ä°ÅŸlemi bul
        $result = $connection->query("SELECT * FROM kasa_hareketleri WHERE hareket_id = $hareketId LIMIT 1");
        if (!$result) {
            throw new Exception('Islem bilgisi alinamadi: ' . $connection->error);
        }
        if ($result->num_rows == 0) throw new Exception('Ä°ÅŸlem bulunamadÄ±.');
        
        $row = $result->fetch_assoc();
        $tutar = floatval($row['tutar']);
        $kasaAdi = normalizeKasaCurrency($row['kasa_adi'] ?? 'TL');
        $islemTipi = $row['islem_tipi'];
        
        // Sadece manuel kasa iÅŸlemlerini silebilir
        if (!in_array($islemTipi, ['kasa_ekle', 'kasa_cikar'])) {
            throw new Exception('Bu iÅŸlem tÃ¼rÃ¼ silinemez. Ä°lgili kaynak Ã¼zerinden iÅŸlem yapÄ±n.');
        }
        
        // Ters iÅŸlem yap
        $kasaSonuc = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$kasaAdi' LIMIT 1");
        if (!$kasaSonuc || $kasaSonuc->num_rows === 0) {
            throw new Exception('Kasa bulunamadi.');
        }
        $mevcutBakiye = (float) ($kasaSonuc->fetch_assoc()['bakiye'] ?? 0);

        if ($islemTipi === 'kasa_ekle') {
            if ($mevcutBakiye + 0.00001 < $tutar) {
                throw new Exception('Islem geri alininca kasa eksiye dusecek.');
            }
            if (!$connection->query("UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$kasaAdi'")) {
                throw new Exception('Kasa bakiyesi guncellenemedi: ' . $connection->error);
            }
        } else {
            if (!$connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$kasaAdi'")) {
                throw new Exception('Kasa bakiyesi guncellenemedi: ' . $connection->error);
            }
        }
        
        // KaydÄ± sil
        if (!$connection->query("DELETE FROM kasa_hareketleri WHERE hareket_id = $hareketId")) {
            throw new Exception('Kasa hareketi silinemedi: ' . $connection->error);
        }
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Ä°ÅŸlem geri alÄ±ndÄ± ve silindi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Ã‡ekleri listele
 */
function getCekler() {
    global $connection;
    
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = max(1, min(100, intval($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;
    
    $where = ["1=1"];
    
    if (!empty($_GET['cek_tipi'])) {
        $where[] = "cek_tipi = '" . $connection->real_escape_string($_GET['cek_tipi']) . "'";
    }
    
    if (!empty($_GET['cek_durumu'])) {
        $where[] = "cek_durumu = '" . $connection->real_escape_string($_GET['cek_durumu']) . "'";
    }
    
    if (!empty($_GET['cek_para_birimi'])) {
        $cekPara = normalizeKasaCurrency($_GET['cek_para_birimi']);
        $where[] = "cek_para_birimi = '" . $connection->real_escape_string($cekPara) . "'";
    }
    
    if (!empty($_GET['search'])) {
        $search = $connection->real_escape_string($_GET['search']);
        $where[] = "(cek_no LIKE '%$search%' OR cek_sahibi LIKE '%$search%' OR cek_banka_adi LIKE '%$search%')";
    }
    
    $whereClause = implode(" AND ", $where);
    
    $total = $connection->query("SELECT COUNT(*) as total FROM cek_kasasi WHERE $whereClause")->fetch_assoc()['total'];
    $result = $connection->query("SELECT * FROM cek_kasasi WHERE $whereClause ORDER BY vade_tarihi ASC, cek_id DESC LIMIT $perPage OFFSET $offset");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'total' => intval($total),
        'page' => $page,
        'per_page' => $perPage
    ]);
}

/**
 * Yeni Ã§ek ekle
 */
function addCek() {
    global $connection;
    
    $cekNo = $connection->real_escape_string($_POST['cek_no'] ?? '');
    $cekTutari = floatval($_POST['cek_tutari'] ?? 0);
    $cekParaBirimi = normalizeKasaCurrency($_POST['cek_para_birimi'] ?? 'TL');
    $cekSahibi = $connection->real_escape_string($_POST['cek_sahibi'] ?? '');
    $cekBankaAdi = $connection->real_escape_string($_POST['cek_banka_adi'] ?? '');
    $cekSubesi = $connection->real_escape_string($_POST['cek_subesi'] ?? '');
    $vadeTarihi = $connection->real_escape_string($_POST['vade_tarihi'] ?? date('Y-m-d'));
    $cekTipi = $connection->real_escape_string($_POST['cek_tipi'] ?? 'alacak');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $personel = $_SESSION['kullanici_adi'] ?? 'Sistem';
    
    if (empty($cekNo)) throw new Exception('Ã‡ek numarasÄ± zorunludur.');
    if ($cekTutari <= 0) throw new Exception('GeÃ§ersiz tutar.');
    if (empty($cekSahibi)) throw new Exception('Ã‡ek sahibi zorunludur.');
    if (!in_array($cekParaBirimi, ['TL', 'USD', 'EUR'], true)) throw new Exception('GeÃ§ersiz Ã§ek para birimi.');
    
    $connection->begin_transaction();
    try {
        $stmt = $connection->prepare("
            INSERT INTO cek_kasasi (cek_no, cek_tutari, cek_para_birimi, cek_sahibi, cek_banka_adi, cek_subesi, vade_tarihi, cek_tipi, aciklama, kaydeden_personel)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sdssssssss", $cekNo, $cekTutari, $cekParaBirimi, $cekSahibi, $cekBankaAdi, $cekSubesi, $vadeTarihi, $cekTipi, $aciklama, $personel);
        $stmt->execute();
        $cekId = $connection->insert_id;
        
        // Kasa hareketlerine kaydet
        $rates = getExchangeRates();
        $tlKarsiligi = $cekTutari * getKasaRateOrFail($rates, $cekParaBirimi);
        $islemTipi = $cekTipi === 'alacak' ? 'cek_alma' : 'cek_odeme';
        
        $stmt2 = $connection->prepare("
            INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, aciklama, kaydeden_personel, ilgili_firma)
            VALUES (NOW(), ?, 'cek_kasasi', ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt2->bind_param("sidsdsss", $islemTipi, $cekId, $cekTutari, $cekParaBirimi, $tlKarsiligi, $aciklama, $personel, $cekSahibi);
        $stmt2->execute();
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Ã‡ek baÅŸarÄ±yla eklendi.', 'cek_id' => $cekId]);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Ã‡ek durumunu gÃ¼ncelle
 */
function updateCekDurumu() {
    global $connection;
    
    $cekId = intval($_POST['cek_id'] ?? 0);
    $yeniDurum = $connection->real_escape_string($_POST['yeni_durum'] ?? '');
    $personel = $_SESSION['kullanici_adi'] ?? 'Sistem';
    
    if ($cekId <= 0) throw new Exception('GeÃ§ersiz Ã§ek ID.');
    
    $validDurumlar = ['alindi', 'kullanildi', 'iptal', 'geri_odendi', 'teminat_verildi', 'tahsilde'];
    if (!in_array($yeniDurum, $validDurumlar)) throw new Exception('GeÃ§ersiz durum.');
    
    $connection->begin_transaction();
    try {
        // Ã‡eki bul
        $result = $connection->query("SELECT * FROM cek_kasasi WHERE cek_id = $cekId");
        if ($result->num_rows == 0) throw new Exception('Ã‡ek bulunamadÄ±.');
        
        $cek = $result->fetch_assoc();
        $eskiDurum = $cek['cek_durumu'];
        
        // Durumu gÃ¼ncelle
        $kullanilmaTarihi = ($yeniDurum === 'kullanildi') ? ", cek_kullanim_tarihi = NOW()" : "";
        $connection->query("
            UPDATE cek_kasasi 
            SET cek_durumu = '$yeniDurum', cek_son_durum_tarihi = NOW() $kullanilmaTarihi
            WHERE cek_id = $cekId
        ");
        
        // Ã‡ek tahsil edildiyse ilgili kasaya ekle
        if ($yeniDurum === 'geri_odendi' && $eskiDurum === 'tahsilde') {
            $paraBirimi = normalizeKasaCurrency($cek['cek_para_birimi'] ?? 'TL');
            $tutar = $cek['cek_tutari'];
            $connection->query("INSERT IGNORE INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$paraBirimi', 0)");
            $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$paraBirimi'");
            
            // Kasa hareketine kaydet
            $rates = getExchangeRates();
            $tlKarsiligi = $tutar * getKasaRateOrFail($rates, $paraBirimi);
            $stmt = $connection->prepare("
                INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, aciklama, kaydeden_personel)
                VALUES (NOW(), 'cek_tahsildi', ?, ?, ?, ?, ?, 'Ã‡ek tahsil edildi', ?)
            ");
            $stmt->bind_param("sidsds", $paraBirimi, $cekId, $tutar, $paraBirimi, $tlKarsiligi, $personel);
            $stmt->execute();
        }
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Ã‡ek durumu gÃ¼ncellendi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Ã‡ek sil
 */
function deleteCek() {
    global $connection;
    
    $cekId = intval($_POST['cek_id'] ?? 0);
    if ($cekId <= 0) throw new Exception('GeÃ§ersiz Ã§ek ID.');
    
    $connection->begin_transaction();
    try {
        // Ã‡ek kullanÄ±lmÄ±ÅŸ mÄ± kontrol et
        $result = $connection->query("SELECT * FROM cek_kasasi WHERE cek_id = $cekId");
        if ($result->num_rows == 0) throw new Exception('Ã‡ek bulunamadÄ±.');
        
        $cek = $result->fetch_assoc();
        if ($cek['cek_durumu'] === 'kullanildi') {
            throw new Exception('KullanÄ±lmÄ±ÅŸ Ã§ek silinemez.');
        }
        
        // Ä°lgili kasa hareketlerini sil
        $connection->query("DELETE FROM kasa_hareketleri WHERE cek_id = $cekId");
        
        // Ã‡eki sil
        $connection->query("DELETE FROM cek_kasasi WHERE cek_id = $cekId");
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Ã‡ek silindi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Stok deÄŸerlerini hesapla (ÃœrÃ¼nler, Malzemeler, Esanslar)
 */
function getStokDegerleri() {
    global $connection;
    
    $rates = getExchangeRates();
    $usdRate = getKasaRateOrFail($rates, 'USD');
    $eurRate = getKasaRateOrFail($rates, 'EUR');
    
    // ÃœrÃ¼nlerin toplam deÄŸeri (adet * satÄ±ÅŸ fiyatÄ±, TL'ye Ã§evrilmiÅŸ)
    $urunDeger = fetchKasaSingleFloatOrFail($connection, "
        SELECT 
            SUM(stok_miktari * satis_fiyati * 
                CASE satis_fiyati_para_birimi 
                    WHEN 'USD' THEN {$usdRate}
                    WHEN 'EUR' THEN {$eurRate}
                    ELSE 1 
                END
            ) as toplam
        FROM urunler WHERE stok_miktari > 0
    ");
    
    // Malzemelerin toplam deÄŸeri (adet * alÄ±ÅŸ fiyatÄ±, TL'ye Ã§evrilmiÅŸ)
    $malzemeDeger = fetchKasaSingleFloatOrFail($connection, "
        SELECT 
            SUM(stok_miktari * alis_fiyati * 
                CASE para_birimi 
                    WHEN 'USD' THEN {$usdRate}
                    WHEN 'EUR' THEN {$eurRate}
                    ELSE 1 
                END
            ) as toplam
        FROM malzemeler WHERE stok_miktari > 0
    ");
    
    // EsanslarÄ±n toplam deÄŸeri (stok * v_esans_maliyetleri)
    $esansDeger = fetchKasaSingleFloatOrFail($connection, "
        SELECT 
            SUM(e.stok_miktari * COALESCE(vem.toplam_maliyet, 0)) as toplam
        FROM esanslar e
        LEFT JOIN v_esans_maliyetleri vem ON e.esans_kodu = vem.esans_kodu
        WHERE e.stok_miktari > 0
    ");
    
    $toplamDeger = $urunDeger + $malzemeDeger + $esansDeger;
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'urunler' => round($urunDeger, 2),
            'malzemeler' => round($malzemeDeger, 2),
            'esanslar' => round($esansDeger, 2),
            'toplam' => round($toplamDeger, 2)
        ]
    ]);
}

/**
 * AylÄ±k kasa hareketlerini getir
 */
function getAylikKasaHareketleri() {
    global $connection;
    
    $ay = intval($_GET['ay'] ?? date('m'));
    $yil = intval($_GET['yil'] ?? date('Y'));
    
    $baslangic = "$yil-" . str_pad($ay, 2, '0', STR_PAD_LEFT) . "-01";
    $bitis = date('Y-m-t', strtotime($baslangic));
    
    // Gelirler (para birimine gÃ¶re)
    $gelirResult = $connection->query("
        SELECT 
            para_birimi,
            SUM(tutar) as toplam
        FROM gelir_yonetimi 
        WHERE DATE(tarih) BETWEEN '$baslangic' AND '$bitis'
        GROUP BY para_birimi
    ");
    $gelirler = ['TL' => 0, 'USD' => 0, 'EUR' => 0];
    if ($gelirResult) {
        while ($row = $gelirResult->fetch_assoc()) {
            $pb = normalizeKasaCurrency($row['para_birimi'] ?? 'TL');
            $gelirler[$pb] += floatval($row['toplam']);
        }
    } else {
        throw new Exception('Aylik gelirler alinamadi: ' . $connection->error);
    }
    
    // Giderler (TL olarak)
    $giderler = fetchKasaSingleFloatOrFail($connection, "
        SELECT SUM(tutar) as toplam
        FROM gider_yonetimi 
        WHERE DATE(tarih) BETWEEN '$baslangic' AND '$bitis'
    ");
    
    // Ã‡ek iÅŸlemleri
    $cekGirisResult = $connection->query("
        SELECT COUNT(*) as adet, SUM(cek_tutari) as toplam
        FROM cek_kasasi 
        WHERE cek_tipi = 'alacak' AND DATE(cek_alim_tarihi) BETWEEN '$baslangic' AND '$bitis'
    ");
    if (!$cekGirisResult) {
        throw new Exception('Aylik cek girisleri alinamadi: ' . $connection->error);
    }
    $cekGiris = $cekGirisResult->fetch_assoc() ?: ['adet' => 0, 'toplam' => 0];
    
    $cekCikisResult = $connection->query("
        SELECT COUNT(*) as adet, SUM(cek_tutari) as toplam
        FROM cek_kasasi 
        WHERE cek_tipi = 'verilen' AND DATE(cek_alim_tarihi) BETWEEN '$baslangic' AND '$bitis'
    ");
    if (!$cekCikisResult) {
        throw new Exception('Aylik cek cikislari alinamadi: ' . $connection->error);
    }
    $cekCikis = $cekCikisResult->fetch_assoc() ?: ['adet' => 0, 'toplam' => 0];
    
    // TL karÅŸÄ±lÄ±klarÄ±nÄ± hesapla
    $rates = getExchangeRates();
    $gelirTL = $gelirler['TL']
        + convertKasaCurrencyAmount($gelirler['USD'], 'USD', 'TL', $rates)
        + convertKasaCurrencyAmount($gelirler['EUR'], 'EUR', 'TL', $rates);
    $netDurum = $gelirTL - $giderler;
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'donem' => ['ay' => $ay, 'yil' => $yil],
            'gelirler' => $gelirler,
            'gelir_tl_toplam' => round($gelirTL, 2),
            'giderler' => round($giderler, 2),
            'net_durum' => round($netDurum, 2),
            'cek_giris' => ['adet' => intval($cekGiris['adet'] ?? 0), 'toplam' => floatval($cekGiris['toplam'] ?? 0)],
            'cek_cikis' => ['adet' => intval($cekCikis['adet'] ?? 0), 'toplam' => floatval($cekCikis['toplam'] ?? 0)]
        ]
    ]);
}

/**
 * TedarikÃ§ilere yapÄ±lacak Ã¶demeleri hesapla (Ã‡erÃ§eve sÃ¶zleÅŸmelerden)
 */
function getTedarikciOdemeleri() {
    global $connection;
    
    // Ã‡erÃ§eve sÃ¶zleÅŸmelerdeki Ã¶denmemiÅŸ tutarlarÄ± hesapla
    $result = $connection->query("
        SELECT 
            cs.para_birimi,
            cs.tedarikci_adi,
            cs.malzeme_ismi,
            cs.birim_fiyat,
            COALESCE(shs.toplam_kullanilan, 0) as kullanilan_miktar,
            cs.toplu_odenen_miktar,
            (COALESCE(shs.toplam_kullanilan, 0) - cs.toplu_odenen_miktar) as odenmemis_miktar,
            ((COALESCE(shs.toplam_kullanilan, 0) - cs.toplu_odenen_miktar) * cs.birim_fiyat) as odenmemis_tutar
        FROM cerceve_sozlesmeler cs
        LEFT JOIN (
            SELECT sozlesme_id, SUM(kullanilan_miktar) as toplam_kullanilan
            FROM stok_hareketleri_sozlesmeler
            GROUP BY sozlesme_id
        ) shs ON cs.sozlesme_id = shs.sozlesme_id
        WHERE (COALESCE(shs.toplam_kullanilan, 0) - cs.toplu_odenen_miktar) > 0
    ");
    
    $detaylar = [];
    $toplamlar = ['TL' => 0, 'USD' => 0, 'EUR' => 0];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pb = normalizeKasaCurrency($row['para_birimi'] ?? 'TL');
            $tutar = floatval($row['odenmemis_tutar']);
            $toplamlar[$pb] = ($toplamlar[$pb] ?? 0) + $tutar;
            $detaylar[] = $row;
        }
    } else {
        throw new Exception('Tedarikci odemeleri alinamadi: ' . $connection->error);
    }
    
    // TL karÅŸÄ±lÄ±ÄŸÄ±nÄ± hesapla
    $rates = getExchangeRates();
    $tlToplam = $toplamlar['TL']
        + convertKasaCurrencyAmount($toplamlar['USD'], 'USD', 'TL', $rates)
        + convertKasaCurrencyAmount($toplamlar['EUR'], 'EUR', 'TL', $rates);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'toplamlar' => $toplamlar,
            'tl_toplam' => round($tlToplam, 2),
            'detaylar' => $detaylar
        ]
    ]);
}

/**
 * Dashboard iÃ§in Ã¶zet bilgileri getir (tek seferde tÃ¼m veriler)
 */
function getDashboardSummary() {
    global $connection;
    
    $rates = getExchangeRates();
    $usdRate = getKasaRateOrFail($rates, 'USD');
    $eurRate = getKasaRateOrFail($rates, 'EUR');
    
    // Kasa bakiyeleri
    $kasaResult = $connection->query("SELECT para_birimi, bakiye FROM sirket_kasasi");
    $kasalar = ['TL' => 0, 'USD' => 0, 'EUR' => 0];
    if ($kasaResult) {
        while ($row = $kasaResult->fetch_assoc()) {
            $pb = normalizeKasaCurrency($row['para_birimi'] ?? 'TL');
            $kasalar[$pb] += floatval($row['bakiye']);
        }
    }
    
    // Ã‡ek kasasÄ±
    $cekResult = $connection->query("
        SELECT cek_para_birimi, COUNT(*) as adet, SUM(cek_tutari) as toplam
        FROM cek_kasasi
        WHERE cek_tipi = 'alacak' AND cek_durumu IN ('alindi', 'tahsilde', 'kullanildi')
        GROUP BY cek_para_birimi
    ");
    $cekOzet = ['adet' => 0, 'TL' => 0.0, 'USD' => 0.0, 'EUR' => 0.0, 'toplam' => 0.0, 'tl_karsiligi' => 0.0];
    if ($cekResult) {
        while ($row = $cekResult->fetch_assoc()) {
            $pb = normalizeKasaCurrency($row['cek_para_birimi'] ?? 'TL');
            $tutar = floatval($row['toplam'] ?? 0);
            $cekOzet['adet'] += intval($row['adet'] ?? 0);
            $cekOzet[$pb] += $tutar;
        }
    }
    $cekOzet['tl_karsiligi'] = $cekOzet['TL']
        + convertKasaCurrencyAmount($cekOzet['USD'], 'USD', 'TL', $rates)
        + convertKasaCurrencyAmount($cekOzet['EUR'], 'EUR', 'TL', $rates);
    $cekOzet['toplam'] = $cekOzet['tl_karsiligi'];
    
    // Stok deÄŸerleri
    $urunDeger = fetchKasaSingleFloatOrFail($connection, "
        SELECT SUM(stok_miktari * satis_fiyati * 
            CASE satis_fiyati_para_birimi 
                WHEN 'USD' THEN {$usdRate}
                WHEN 'EUR' THEN {$eurRate}
                ELSE 1 
            END) as toplam
        FROM urunler WHERE stok_miktari > 0
    ");
    
    $malzemeDeger = fetchKasaSingleFloatOrFail($connection, "
        SELECT SUM(stok_miktari * alis_fiyati * 
            CASE para_birimi 
                WHEN 'USD' THEN {$usdRate}
                WHEN 'EUR' THEN {$eurRate}
                ELSE 1 
            END) as toplam
        FROM malzemeler WHERE stok_miktari > 0
    ");
    
    $esansDeger = fetchKasaSingleFloatOrFail($connection, "
        SELECT SUM(e.stok_miktari * COALESCE(vem.toplam_maliyet, 0)) as toplam
        FROM esanslar e
        LEFT JOIN v_esans_maliyetleri vem ON e.esans_kodu = vem.esans_kodu
        WHERE e.stok_miktari > 0
    ");
    
    // Bu ayki gelir/gider
    $baslangic = date('Y-m-01');
    $bitis = date('Y-m-t');
    
    $aylikGelir = fetchKasaSingleFloatOrFail($connection, "
        SELECT SUM(tutar * 
            CASE para_birimi 
                WHEN 'USD' THEN {$usdRate}
                WHEN 'EUR' THEN {$eurRate}
                ELSE 1 
            END) as toplam
        FROM gelir_yonetimi 
        WHERE DATE(tarih) BETWEEN '$baslangic' AND '$bitis'
    ");
    
    $aylikGider = fetchKasaSingleFloatOrFail($connection, "
        SELECT SUM(tutar) as toplam
        FROM gider_yonetimi 
        WHERE DATE(tarih) BETWEEN '$baslangic' AND '$bitis'
    ");
    
    // TedarikÃ§i borÃ§larÄ±
    $borcResult = $connection->query("
        SELECT 
            cs.para_birimi,
            SUM((COALESCE(shs.toplam_kullanilan, 0) - cs.toplu_odenen_miktar) * cs.birim_fiyat) as toplam
        FROM cerceve_sozlesmeler cs
        LEFT JOIN (
            SELECT sozlesme_id, SUM(kullanilan_miktar) as toplam_kullanilan
            FROM stok_hareketleri_sozlesmeler
            GROUP BY sozlesme_id
        ) shs ON cs.sozlesme_id = shs.sozlesme_id
        WHERE (COALESCE(shs.toplam_kullanilan, 0) - cs.toplu_odenen_miktar) > 0
        GROUP BY cs.para_birimi
    ");
    $borclar = ['TL' => 0, 'USD' => 0, 'EUR' => 0];
    if ($borcResult) {
        while ($row = $borcResult->fetch_assoc()) {
            $pb = normalizeKasaCurrency($row['para_birimi'] ?? 'TL');
            $borclar[$pb] += floatval($row['toplam']);
        }
    }
    $borcTL = $borclar['TL']
        + convertKasaCurrencyAmount($borclar['USD'], 'USD', 'TL', $rates)
        + convertKasaCurrencyAmount($borclar['EUR'], 'EUR', 'TL', $rates);
    
    // Bekleyen Personel Ã–demeleri (Bu Ay)
    $yil = date('Y');
    $ay = date('n');
    
    // Bekleyen Personel Ã–demeleri Hesaplama ve Detay Alma
    $bekleyenPersonelListesi = [];
    $bekleyenPersonelOdeme = 0;
    
    // TÃ¼m bordrolu personeli Ã§ek
    $personelQuery = "SELECT 
                        p.personel_id, p.ad_soyad, p.aylik_brut_ucret 
                      FROM personeller p 
                      WHERE p.bordrolu_calisan_mi = 1 
                      ORDER BY p.ad_soyad";
    $personelResult = $connection->query($personelQuery);
    
    if ($personelResult) {
        while ($p = $personelResult->fetch_assoc()) {
            $pId = $p['personel_id'];
            $brutUcret = floatval($p['aylik_brut_ucret']);
            
            // Bu personelin bu ay Ã¶denmiÅŸ maaÅŸÄ± var mÄ±?
            $mOdemeResult = $connection->query("SELECT SUM(net_odenen) as toplam FROM personel_maas_odemeleri WHERE personel_id = $pId AND donem_yil = $yil AND donem_ay = $ay");
            $mOdeme = $mOdemeResult ? $mOdemeResult->fetch_assoc() : null;
            $odenenMaas = floatval($mOdeme['toplam'] ?? 0);
            
            // Bu personelin bu ay aldÄ±ÄŸÄ± tÃ¼m avanslar (kullanÄ±lmÄ±ÅŸ olsun olmasÄ±n)
            $kAvansResult = $connection->query("SELECT SUM(avans_tutari) as toplam FROM personel_avanslar WHERE personel_id = $pId AND donem_yil = $yil AND donem_ay = $ay");
            $kAvans = $kAvansResult ? $kAvansResult->fetch_assoc() : null;
            $toplamAvans = floatval($kAvans['toplam'] ?? 0);
            
            // Kalan Ã¶denecek tahmini tutar
            $kalanOdeme = $brutUcret - ($odenenMaas + $toplamAvans);
            
            // EÄŸer kalan Ã¶deme 0'dan bÃ¼yÃ¼kse listeye ekle
            if ($kalanOdeme > 0.01) {
                $bekleyenPersonelListesi[] = [
                    'ad_soyad' => $p['ad_soyad'],
                    'brut_ucret' => $brutUcret,
                    'avans' => $toplamAvans,
                    'odenen' => $odenenMaas,
                    'kalan_odeme' => round($kalanOdeme, 2)
                ];
                $bekleyenPersonelOdeme += $kalanOdeme;
            }
        }
    }
    
    
    // Bekleyen TekrarlÄ± Ã–demeler Hesaplama ve Detay Alma
    $bugunGun = date('d');
    $bekleyenTekrarliListesi = [];
    
    $tekrarliQuery = "SELECT t.odeme_adi, t.tutar, t.odeme_gunu, t.alici_firma
                      FROM tekrarli_odemeler t 
                      WHERE t.aktif = 1 
                      AND NOT EXISTS (
                          SELECT 1 FROM tekrarli_odeme_gecmisi g 
                          WHERE g.odeme_id = t.odeme_id 
                          AND g.donem_yil = $yil 
                          AND g.donem_ay = $ay
                      )
                      ORDER BY t.odeme_gunu";
    $tekrarliResult = $connection->query($tekrarliQuery);
    
    $bekleyenTekrarliOdeme = 0;
    if ($tekrarliResult) {
        while ($row = $tekrarliResult->fetch_assoc()) {
            $tutar = floatval($row['tutar']);
            $bekleyenTekrarliOdeme += $tutar;
            
            $bekleyenTekrarliListesi[] = [
                'odeme_adi' => $row['odeme_adi'],
                'alici_firma' => $row['alici_firma'],
                'odeme_gunu' => $row['odeme_gunu'],
                'tutar' => $tutar
            ];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'kurlar' => $rates,
            'kasalar' => $kasalar,
            'cek_kasasi' => [
                'adet' => intval($cekOzet['adet'] ?? 0),
                'TL' => round(floatval($cekOzet['TL'] ?? 0), 2),
                'USD' => round(floatval($cekOzet['USD'] ?? 0), 2),
                'EUR' => round(floatval($cekOzet['EUR'] ?? 0), 2),
                'toplam' => round(floatval($cekOzet['toplam'] ?? 0), 2),
                'tl_karsiligi' => round(floatval($cekOzet['tl_karsiligi'] ?? 0), 2)
            ],
            'stok_degerleri' => [
                'urunler' => round(floatval($urunDeger), 2),
                'malzemeler' => round(floatval($malzemeDeger), 2),
                'esanslar' => round(floatval($esansDeger), 2),
                'toplam' => round(floatval($urunDeger) + floatval($malzemeDeger) + floatval($esansDeger), 2)
            ],
            'bekleyen_odemeler' => [
                'personel' => round($bekleyenPersonelOdeme, 2),
                'sabit_giderler' => round($bekleyenTekrarliOdeme, 2),
                'toplam' => round($bekleyenPersonelOdeme + $bekleyenTekrarliOdeme, 2),
                'detaylar' => [
                    'personel' => $bekleyenPersonelListesi,
                    'sabit_giderler' => $bekleyenTekrarliListesi
                ]
            ],
            'tedarikci_borclari' => [
                'detay' => $borclar,
                'tl_toplam' => round($borcTL, 2)
            ],
            'musteri_alacaklari' => getMusteriAlacaklariData($connection, $rates)
        ]
    ]);
}

/**
 * MÃ¼ÅŸterilerden alÄ±nacak Ã¶demeleri hesapla
 */
function getMusteriAlacaklari() {
    global $connection;
    $rates = getExchangeRates();
    echo json_encode([
        'status' => 'success',
        'data' => getMusteriAlacaklariData($connection, $rates)
    ]);
}

/**
 * MÃ¼ÅŸteri alacaklarÄ± verilerini hesapla (dahili fonksiyon)
 */
function getMusteriAlacaklariData($connection, $rates) {
    // Siparis basligini ve kalem toplamlarini ayri hesapla.
    // Bu sayede odenen_tutar siparis basina tek kez ve dogru para biriminde dusulur.
    $siparisResult = $connection->query("
        SELECT 
            s.siparis_id,
            s.musteri_adi,
            s.tarih,
            COALESCE(s.para_birimi, 'TL') as para_birimi,
            COALESCE(s.odenen_tutar, 0) as odenen_tutar
        FROM siparisler s
        WHERE s.durum IN ('onaylandi', 'tamamlandi')
        ORDER BY s.tarih DESC, s.siparis_id DESC
    ");

    $kalemResult = $connection->query("
        SELECT
            siparis_id,
            COALESCE(para_birimi, 'TL') as para_birimi,
            SUM(COALESCE(toplam_tutar, adet * birim_fiyat)) as toplam
        FROM siparis_kalemleri
        GROUP BY siparis_id, para_birimi
    ");

    $kalemToplamlari = [];
    if ($kalemResult) {
        while ($kalem = $kalemResult->fetch_assoc()) {
            $siparisId = (int) ($kalem['siparis_id'] ?? 0);
            if ($siparisId <= 0) {
                continue;
            }

            $kalemPB = normalizeKasaCurrency($kalem['para_birimi'] ?? 'TL');
            if (!isset($kalemToplamlari[$siparisId])) {
                $kalemToplamlari[$siparisId] = [];
            }
            if (!isset($kalemToplamlari[$siparisId][$kalemPB])) {
                $kalemToplamlari[$siparisId][$kalemPB] = 0.0;
            }
            $kalemToplamlari[$siparisId][$kalemPB] += (float) ($kalem['toplam'] ?? 0);
        }
    }

    $toplamlar = ['TL' => 0.0, 'USD' => 0.0, 'EUR' => 0.0];
    $detaylar = [];

    if ($siparisResult) {
        while ($siparis = $siparisResult->fetch_assoc()) {
            $siparisId = (int) ($siparis['siparis_id'] ?? 0);
            $siparisPB = normalizeKasaCurrency($siparis['para_birimi'] ?? 'TL');
            $odenen = max(0.0, (float) ($siparis['odenen_tutar'] ?? 0));

            $siparisTutari = 0.0;
            if (isset($kalemToplamlari[$siparisId])) {
                foreach ($kalemToplamlari[$siparisId] as $kalemPB => $kalemToplam) {
                    $siparisTutari += convertKasaCurrencyAmount($kalemToplam, $kalemPB, $siparisPB, $rates);
                }
            }

            $kalan = max(0.0, $siparisTutari - $odenen);
            if ($kalan <= 0.01) {
                continue;
            }

            $toplamlar[$siparisPB] += $kalan;
            $detaylar[] = [
                'siparis_id' => $siparisId,
                'musteri_adi' => $siparis['musteri_adi'] ?? '',
                'para_birimi' => $siparisPB,
                'tarih' => $siparis['tarih'] ?? null,
                'siparis_tutari' => round($siparisTutari, 2),
                'odenen_tutar' => round($odenen, 2),
                'kalan' => round($kalan, 2)
            ];
        }
    }

    // TL karsiligini hesapla
    $tlToplam = $toplamlar['TL']
        + convertKasaCurrencyAmount($toplamlar['USD'], 'USD', 'TL', $rates)
        + convertKasaCurrencyAmount($toplamlar['EUR'], 'EUR', 'TL', $rates);

    return [
        'detay' => [
            'TL' => round($toplamlar['TL'], 2),
            'USD' => round($toplamlar['USD'], 2),
            'EUR' => round($toplamlar['EUR'], 2)
        ],
        'tl_toplam' => round($tlToplam, 2),
        'siparis_sayisi' => count($detaylar),
        'liste' => $detaylar
    ];
}
?>

<?php
/**
 * Kasa Yönetimi API İşlemleri
 * Kasa bakiyeleri, çek işlemleri, kasa hareketleri ve istatistikler
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include '../config.php';

header('Content-Type: application/json; charset=utf-8');

// Giriş ve yetki kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetki yok veya oturum kapalı.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Action eksik.']);
    exit;
}

try {
    switch ($action) {
        // Kasa İşlemleri
        case 'get_kasa_bakiyeleri': getKasaBakiyeleri(); break;
        case 'get_kasa_hareketleri': getKasaHareketleri(); break;
        case 'add_kasa_islemi': addKasaIslemi(); break;
        case 'delete_kasa_islemi': deleteKasaIslemi(); break;
        
        // Çek İşlemleri
        case 'get_cekler': getCekler(); break;
        case 'add_cek': addCek(); break;
        case 'update_cek_durumu': updateCekDurumu(); break;
        case 'delete_cek': deleteCek(); break;
        
        // İstatistikler
        case 'get_stok_degerleri': getStokDegerleri(); break;
        case 'get_aylik_kasa_hareketleri': getAylikKasaHareketleri(); break;
        case 'get_tedarikci_odemeleri': getTedarikciOdemeleri(); break;
        case 'get_musteri_alacaklari': getMusteriAlacaklari(); break;
        case 'get_doviz_kurlari': getDovizKurlari(); break;
        case 'get_dashboard_summary': getDashboardSummary(); break;
        
        default: throw new Exception('Geçersiz action: ' . $action);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

/**
 * Döviz kurlarını ayarlar tablosundan al
 */
function getExchangeRates() {
    global $connection;
    $result = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
    $rates = ['TL' => 1, 'USD' => 1, 'EUR' => 1];
    while ($row = $result->fetch_assoc()) {
        if ($row['ayar_anahtar'] === 'dolar_kuru') $rates['USD'] = floatval($row['ayar_deger']);
        if ($row['ayar_anahtar'] === 'euro_kuru') $rates['EUR'] = floatval($row['ayar_deger']);
    }
    return $rates;
}

/**
 * Döviz kurlarını JSON olarak döndür
 */
function getDovizKurlari() {
    $rates = getExchangeRates();
    echo json_encode(['status' => 'success', 'data' => $rates]);
}

/**
 * Kasa bakiyelerini getir (TL, USD, EUR + Çek Kasası özeti)
 */
function getKasaBakiyeleri() {
    global $connection;
    
    // Sirket kasasi bakiyeleri
    $result = $connection->query("SELECT para_birimi, bakiye FROM sirket_kasasi");
    $bakiyeler = ['TL' => 0, 'USD' => 0, 'EUR' => 0];
    while ($row = $result->fetch_assoc()) {
        $bakiyeler[$row['para_birimi']] = floatval($row['bakiye']);
    }
    
    // Çek kasası özeti (sadece alindi ve tahsilde durumundaki çekler)
    $cekResult = $connection->query("
        SELECT 
            cek_para_birimi,
            COUNT(*) as adet,
            SUM(cek_tutari) as toplam
        FROM cek_kasasi 
        WHERE cek_tipi = 'alacak' AND cek_durumu IN ('alindi', 'tahsilde')
        GROUP BY cek_para_birimi
    ");
    
    $cekOzeti = ['adet' => 0, 'TL' => 0, 'USD' => 0, 'EUR' => 0];
    while ($row = $cekResult->fetch_assoc()) {
        $cekOzeti[$row['cek_para_birimi']] = floatval($row['toplam']);
        $cekOzeti['adet'] += intval($row['adet']);
    }
    
    // TL karşılığı hesapla
    $rates = getExchangeRates();
    $cekOzeti['tl_karsiligi'] = 
        $cekOzeti['TL'] + 
        ($cekOzeti['USD'] * $rates['USD']) + 
        ($cekOzeti['EUR'] * $rates['EUR']);
    
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
    
    // Toplam sayı
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
 * Kasa işlemi ekle (TL/USD/EUR kasasına ekleme veya çıkarma)
 */
function addKasaIslemi() {
    global $connection;
    
    $tarih = $_POST['tarih'] ?? date('Y-m-d H:i:s');
    $tarih = str_replace('T', ' ', $tarih);
    if (strlen($tarih) == 16) $tarih .= ':00';
    
    $islemTipi = $_POST['islem_tipi'] ?? ''; // kasa_ekle, kasa_cikar
    $kasaAdi = $_POST['kasa_adi'] ?? 'TL';   // TL, USD, EUR
    $tutar = floatval($_POST['tutar'] ?? 0);
    $aciklama = $_POST['aciklama'] ?? '';
    // Formdaki name="odeme_tipi_detay" ama db'de "odeme_tipi" olarak saklayacağız
    $odemeTipi = $_POST['odeme_tipi_detay'] ?? 'Nakit'; 
    $personel = $_SESSION['kullanici_adi'] ?? 'Sistem';
    
    if ($tutar <= 0) throw new Exception('Geçersiz tutar.');
    if (!in_array($kasaAdi, ['TL', 'USD', 'EUR'])) throw new Exception('Geçersiz kasa.');
    if (!in_array($islemTipi, ['kasa_ekle', 'kasa_cikar'])) throw new Exception('Geçersiz işlem tipi.');
    
    $rates = getExchangeRates();
    $tlKarsiligi = $tutar * $rates[$kasaAdi];
    
    $connection->begin_transaction();
    try {
        // Kasa bakiyesini kontrol et ve güncelle
        $check = $connection->query("SELECT bakiye FROM sirket_kasasi WHERE para_birimi = '$kasaAdi'");
        if ($check->num_rows == 0) {
            $connection->query("INSERT INTO sirket_kasasi (para_birimi, bakiye) VALUES ('$kasaAdi', 0)");
            $bakiye = 0;
        } else {
            $bakiye = floatval($check->fetch_assoc()['bakiye']);
        }
        
        if ($islemTipi === 'kasa_ekle') {
            $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$kasaAdi'");
        } else {
            if ($bakiye < $tutar) throw new Exception("Kasada yeterli bakiye yok! Mevcut: $bakiye $kasaAdi");
            $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$kasaAdi'");
        }
        
        // Kasa hareketleri tablosuna kaydet
        $stmt = $connection->prepare("
            INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, tutar, para_birimi, doviz_kuru, tl_karsiligi, aciklama, kaydeden_personel, odeme_tipi)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $dovizKuru = $rates[$kasaAdi];
        $stmt->bind_param("sssdsddsss", $tarih, $islemTipi, $kasaAdi, $tutar, $kasaAdi, $dovizKuru, $tlKarsiligi, $aciklama, $personel, $odemeTipi);
        $stmt->execute();
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Kasa işlemi başarıyla kaydedildi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Kasa işlemini sil ve bakiyeyi geri al
 */
function deleteKasaIslemi() {
    global $connection;
    
    $hareketId = intval($_POST['hareket_id'] ?? 0);
    if ($hareketId <= 0) throw new Exception('Geçersiz hareket ID.');
    
    $connection->begin_transaction();
    try {
        // İşlemi bul
        $result = $connection->query("SELECT * FROM kasa_hareketleri WHERE hareket_id = $hareketId");
        if ($result->num_rows == 0) throw new Exception('İşlem bulunamadı.');
        
        $row = $result->fetch_assoc();
        $tutar = floatval($row['tutar']);
        $kasaAdi = $row['kasa_adi'];
        $islemTipi = $row['islem_tipi'];
        
        // Sadece manuel kasa işlemlerini silebilir
        if (!in_array($islemTipi, ['kasa_ekle', 'kasa_cikar'])) {
            throw new Exception('Bu işlem türü silinemez. İlgili kaynak üzerinden işlem yapın.');
        }
        
        // Ters işlem yap
        if ($islemTipi === 'kasa_ekle') {
            $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye - $tutar WHERE para_birimi = '$kasaAdi'");
        } else {
            $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$kasaAdi'");
        }
        
        // Kaydı sil
        $connection->query("DELETE FROM kasa_hareketleri WHERE hareket_id = $hareketId");
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'İşlem geri alındı ve silindi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Çekleri listele
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
        $where[] = "cek_para_birimi = '" . $connection->real_escape_string($_GET['cek_para_birimi']) . "'";
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
 * Yeni çek ekle
 */
function addCek() {
    global $connection;
    
    $cekNo = $connection->real_escape_string($_POST['cek_no'] ?? '');
    $cekTutari = floatval($_POST['cek_tutari'] ?? 0);
    $cekParaBirimi = $connection->real_escape_string($_POST['cek_para_birimi'] ?? 'TL');
    $cekSahibi = $connection->real_escape_string($_POST['cek_sahibi'] ?? '');
    $cekBankaAdi = $connection->real_escape_string($_POST['cek_banka_adi'] ?? '');
    $cekSubesi = $connection->real_escape_string($_POST['cek_subesi'] ?? '');
    $vadeTarihi = $connection->real_escape_string($_POST['vade_tarihi'] ?? date('Y-m-d'));
    $cekTipi = $connection->real_escape_string($_POST['cek_tipi'] ?? 'alacak');
    $aciklama = $connection->real_escape_string($_POST['aciklama'] ?? '');
    $personel = $_SESSION['kullanici_adi'] ?? 'Sistem';
    
    if (empty($cekNo)) throw new Exception('Çek numarası zorunludur.');
    if ($cekTutari <= 0) throw new Exception('Geçersiz tutar.');
    if (empty($cekSahibi)) throw new Exception('Çek sahibi zorunludur.');
    
    $connection->begin_transaction();
    try {
        $stmt = $connection->prepare("
            INSERT INTO cek_kasasi (cek_no, cek_tutari, cek_para_birimi, cek_sahibi, cek_banka_adi, cek_subesi, vade_tarihi, cek_tipi, aciklama, kaydeden_personel)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sdsssssss", $cekNo, $cekTutari, $cekParaBirimi, $cekSahibi, $cekBankaAdi, $cekSubesi, $vadeTarihi, $cekTipi, $aciklama, $personel);
        $stmt->execute();
        $cekId = $connection->insert_id;
        
        // Kasa hareketlerine kaydet
        $rates = getExchangeRates();
        $tlKarsiligi = $cekTutari * $rates[$cekParaBirimi];
        $islemTipi = $cekTipi === 'alacak' ? 'cek_alma' : 'cek_odeme';
        
        $stmt2 = $connection->prepare("
            INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, aciklama, kaydeden_personel, ilgili_firma)
            VALUES (NOW(), ?, 'cek_kasasi', ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt2->bind_param("sidsdsss", $islemTipi, $cekId, $cekTutari, $cekParaBirimi, $tlKarsiligi, $aciklama, $personel, $cekSahibi);
        $stmt2->execute();
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Çek başarıyla eklendi.', 'cek_id' => $cekId]);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Çek durumunu güncelle
 */
function updateCekDurumu() {
    global $connection;
    
    $cekId = intval($_POST['cek_id'] ?? 0);
    $yeniDurum = $connection->real_escape_string($_POST['yeni_durum'] ?? '');
    $personel = $_SESSION['kullanici_adi'] ?? 'Sistem';
    
    if ($cekId <= 0) throw new Exception('Geçersiz çek ID.');
    
    $validDurumlar = ['alindi', 'kullanildi', 'iptal', 'geri_odendi', 'teminat_verildi', 'tahsilde'];
    if (!in_array($yeniDurum, $validDurumlar)) throw new Exception('Geçersiz durum.');
    
    $connection->begin_transaction();
    try {
        // Çeki bul
        $result = $connection->query("SELECT * FROM cek_kasasi WHERE cek_id = $cekId");
        if ($result->num_rows == 0) throw new Exception('Çek bulunamadı.');
        
        $cek = $result->fetch_assoc();
        $eskiDurum = $cek['cek_durumu'];
        
        // Durumu güncelle
        $kullanilmaTarihi = ($yeniDurum === 'kullanildi') ? ", cek_kullanim_tarihi = NOW()" : "";
        $connection->query("
            UPDATE cek_kasasi 
            SET cek_durumu = '$yeniDurum', cek_son_durum_tarihi = NOW() $kullanilmaTarihi
            WHERE cek_id = $cekId
        ");
        
        // Çek tahsil edildiyse ilgili kasaya ekle
        if ($yeniDurum === 'geri_odendi' && $eskiDurum === 'tahsilde') {
            $paraBirimi = $cek['cek_para_birimi'];
            $tutar = $cek['cek_tutari'];
            $connection->query("UPDATE sirket_kasasi SET bakiye = bakiye + $tutar WHERE para_birimi = '$paraBirimi'");
            
            // Kasa hareketine kaydet
            $rates = getExchangeRates();
            $tlKarsiligi = $tutar * $rates[$paraBirimi];
            $stmt = $connection->prepare("
                INSERT INTO kasa_hareketleri (tarih, islem_tipi, kasa_adi, cek_id, tutar, para_birimi, tl_karsiligi, aciklama, kaydeden_personel)
                VALUES (NOW(), 'cek_tahsildi', ?, ?, ?, ?, ?, 'Çek tahsil edildi', ?)
            ");
            $stmt->bind_param("sidsds", $paraBirimi, $cekId, $tutar, $paraBirimi, $tlKarsiligi, $personel);
            $stmt->execute();
        }
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Çek durumu güncellendi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Çek sil
 */
function deleteCek() {
    global $connection;
    
    $cekId = intval($_POST['cek_id'] ?? 0);
    if ($cekId <= 0) throw new Exception('Geçersiz çek ID.');
    
    $connection->begin_transaction();
    try {
        // Çek kullanılmış mı kontrol et
        $result = $connection->query("SELECT * FROM cek_kasasi WHERE cek_id = $cekId");
        if ($result->num_rows == 0) throw new Exception('Çek bulunamadı.');
        
        $cek = $result->fetch_assoc();
        if ($cek['cek_durumu'] === 'kullanildi') {
            throw new Exception('Kullanılmış çek silinemez.');
        }
        
        // İlgili kasa hareketlerini sil
        $connection->query("DELETE FROM kasa_hareketleri WHERE cek_id = $cekId");
        
        // Çeki sil
        $connection->query("DELETE FROM cek_kasasi WHERE cek_id = $cekId");
        
        $connection->commit();
        echo json_encode(['status' => 'success', 'message' => 'Çek silindi.']);
    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

/**
 * Stok değerlerini hesapla (Ürünler, Malzemeler, Esanslar)
 */
function getStokDegerleri() {
    global $connection;
    
    $rates = getExchangeRates();
    
    // Ürünlerin toplam değeri (adet * satış fiyatı, TL'ye çevrilmiş)
    $urunResult = $connection->query("
        SELECT 
            SUM(stok_miktari * satis_fiyati * 
                CASE satis_fiyati_para_birimi 
                    WHEN 'USD' THEN {$rates['USD']}
                    WHEN 'EUR' THEN {$rates['EUR']}
                    ELSE 1 
                END
            ) as toplam
        FROM urunler WHERE stok_miktari > 0
    ");
    $urunDeger = floatval($urunResult->fetch_assoc()['toplam'] ?? 0);
    
    // Malzemelerin toplam değeri (adet * alış fiyatı, TL'ye çevrilmiş)
    $malzemeResult = $connection->query("
        SELECT 
            SUM(stok_miktari * alis_fiyati * 
                CASE para_birimi 
                    WHEN 'USD' THEN {$rates['USD']}
                    WHEN 'EUR' THEN {$rates['EUR']}
                    ELSE 1 
                END
            ) as toplam
        FROM malzemeler WHERE stok_miktari > 0
    ");
    $malzemeDeger = floatval($malzemeResult->fetch_assoc()['toplam'] ?? 0);
    
    // Esansların toplam değeri (stok * v_esans_maliyetleri)
    $esansResult = $connection->query("
        SELECT 
            SUM(e.stok_miktari * COALESCE(vem.toplam_maliyet, 0)) as toplam
        FROM esanslar e
        LEFT JOIN v_esans_maliyetleri vem ON e.esans_kodu = vem.esans_kodu
        WHERE e.stok_miktari > 0
    ");
    $esansDeger = floatval($esansResult->fetch_assoc()['toplam'] ?? 0);
    
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
 * Aylık kasa hareketlerini getir
 */
function getAylikKasaHareketleri() {
    global $connection;
    
    $ay = intval($_GET['ay'] ?? date('m'));
    $yil = intval($_GET['yil'] ?? date('Y'));
    
    $baslangic = "$yil-" . str_pad($ay, 2, '0', STR_PAD_LEFT) . "-01";
    $bitis = date('Y-m-t', strtotime($baslangic));
    
    // Gelirler (para birimine göre)
    $gelirResult = $connection->query("
        SELECT 
            para_birimi,
            SUM(tutar) as toplam
        FROM gelir_yonetimi 
        WHERE DATE(tarih) BETWEEN '$baslangic' AND '$bitis'
        GROUP BY para_birimi
    ");
    $gelirler = ['TL' => 0, 'USD' => 0, 'EUR' => 0];
    while ($row = $gelirResult->fetch_assoc()) {
        $gelirler[$row['para_birimi']] = floatval($row['toplam']);
    }
    
    // Giderler (TL olarak)
    $giderResult = $connection->query("
        SELECT SUM(tutar) as toplam
        FROM gider_yonetimi 
        WHERE DATE(tarih) BETWEEN '$baslangic' AND '$bitis'
    ");
    $giderler = floatval($giderResult->fetch_assoc()['toplam'] ?? 0);
    
    // Çek işlemleri
    $cekGiris = $connection->query("
        SELECT COUNT(*) as adet, SUM(cek_tutari) as toplam
        FROM cek_kasasi 
        WHERE cek_tipi = 'alacak' AND DATE(cek_alim_tarihi) BETWEEN '$baslangic' AND '$bitis'
    ")->fetch_assoc();
    
    $cekCikis = $connection->query("
        SELECT COUNT(*) as adet, SUM(cek_tutari) as toplam
        FROM cek_kasasi 
        WHERE cek_tipi = 'verilen' AND DATE(cek_alim_tarihi) BETWEEN '$baslangic' AND '$bitis'
    ")->fetch_assoc();
    
    // TL karşılıklarını hesapla
    $rates = getExchangeRates();
    $gelirTL = $gelirler['TL'] + ($gelirler['USD'] * $rates['USD']) + ($gelirler['EUR'] * $rates['EUR']);
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
 * Tedarikçilere yapılacak ödemeleri hesapla (Çerçeve sözleşmelerden)
 */
function getTedarikciOdemeleri() {
    global $connection;
    
    // Çerçeve sözleşmelerdeki ödenmemiş tutarları hesapla
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
    
    while ($row = $result->fetch_assoc()) {
        $pb = $row['para_birimi'];
        $tutar = floatval($row['odenmemis_tutar']);
        $toplamlar[$pb] = ($toplamlar[$pb] ?? 0) + $tutar;
        $detaylar[] = $row;
    }
    
    // TL karşılığını hesapla
    $rates = getExchangeRates();
    $tlToplam = $toplamlar['TL'] + ($toplamlar['USD'] * $rates['USD']) + ($toplamlar['EUR'] * $rates['EUR']);
    
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
 * Dashboard için özet bilgileri getir (tek seferde tüm veriler)
 */
function getDashboardSummary() {
    global $connection;
    
    $rates = getExchangeRates();
    
    // Kasa bakiyeleri
    $kasaResult = $connection->query("SELECT para_birimi, bakiye FROM sirket_kasasi");
    $kasalar = ['TL' => 0, 'USD' => 0, 'EUR' => 0];
    while ($row = $kasaResult->fetch_assoc()) {
        $kasalar[$row['para_birimi']] = floatval($row['bakiye']);
    }
    
    // Çek kasası
    $cekResult = $connection->query("
        SELECT COUNT(*) as adet, SUM(cek_tutari) as toplam
        FROM cek_kasasi 
        WHERE cek_tipi = 'alacak' AND cek_durumu IN ('alindi', 'tahsilde')
    ");
    $cekOzet = $cekResult->fetch_assoc();
    
    // Stok değerleri
    $urunDeger = $connection->query("
        SELECT SUM(stok_miktari * satis_fiyati * 
            CASE satis_fiyati_para_birimi 
                WHEN 'USD' THEN {$rates['USD']}
                WHEN 'EUR' THEN {$rates['EUR']}
                ELSE 1 
            END) as toplam
        FROM urunler WHERE stok_miktari > 0
    ")->fetch_assoc()['toplam'] ?? 0;
    
    $malzemeDeger = $connection->query("
        SELECT SUM(stok_miktari * alis_fiyati * 
            CASE para_birimi 
                WHEN 'USD' THEN {$rates['USD']}
                WHEN 'EUR' THEN {$rates['EUR']}
                ELSE 1 
            END) as toplam
        FROM malzemeler WHERE stok_miktari > 0
    ")->fetch_assoc()['toplam'] ?? 0;
    
    $esansDeger = $connection->query("
        SELECT SUM(e.stok_miktari * COALESCE(vem.toplam_maliyet, 0)) as toplam
        FROM esanslar e
        LEFT JOIN v_esans_maliyetleri vem ON e.esans_kodu = vem.esans_kodu
        WHERE e.stok_miktari > 0
    ")->fetch_assoc()['toplam'] ?? 0;
    
    // Bu ayki gelir/gider
    $baslangic = date('Y-m-01');
    $bitis = date('Y-m-t');
    
    $aylikGelir = $connection->query("
        SELECT SUM(tutar * 
            CASE para_birimi 
                WHEN 'USD' THEN {$rates['USD']}
                WHEN 'EUR' THEN {$rates['EUR']}
                ELSE 1 
            END) as toplam
        FROM gelir_yonetimi 
        WHERE DATE(tarih) BETWEEN '$baslangic' AND '$bitis'
    ")->fetch_assoc()['toplam'] ?? 0;
    
    $aylikGider = $connection->query("
        SELECT SUM(tutar) as toplam
        FROM gider_yonetimi 
        WHERE DATE(tarih) BETWEEN '$baslangic' AND '$bitis'
    ")->fetch_assoc()['toplam'] ?? 0;
    
    // Tedarikçi borçları
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
    while ($row = $borcResult->fetch_assoc()) {
        $borclar[$row['para_birimi']] = floatval($row['toplam']);
    }
    $borcTL = $borclar['TL'] + ($borclar['USD'] * $rates['USD']) + ($borclar['EUR'] * $rates['EUR']);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'kurlar' => $rates,
            'kasalar' => $kasalar,
            'cek_kasasi' => [
                'adet' => intval($cekOzet['adet'] ?? 0),
                'toplam' => floatval($cekOzet['toplam'] ?? 0)
            ],
            'stok_degerleri' => [
                'urunler' => round(floatval($urunDeger), 2),
                'malzemeler' => round(floatval($malzemeDeger), 2),
                'esanslar' => round(floatval($esansDeger), 2),
                'toplam' => round(floatval($urunDeger) + floatval($malzemeDeger) + floatval($esansDeger), 2)
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
 * Müşterilerden alınacak ödemeleri hesapla
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
 * Müşteri alacakları verilerini hesapla (dahili fonksiyon)
 */
function getMusteriAlacaklariData($connection, $rates) {
    // Siparişlerden kalan alacaklar - siparis_kalemleri ile toplam hesapla
    $siparisResult = $connection->query("
        SELECT 
            s.siparis_id,
            s.musteri_adi,
            s.para_birimi,
            s.tarih,
            COALESCE(sk.toplam, 0) as siparis_tutari,
            COALESCE(s.odenen_tutar, 0) as odenen_tutar,
            (COALESCE(sk.toplam, 0) - COALESCE(s.odenen_tutar, 0)) as kalan
        FROM siparisler s
        LEFT JOIN (
            SELECT siparis_id, SUM(adet * birim_fiyat) as toplam
            FROM siparis_kalemleri
            GROUP BY siparis_id
        ) sk ON s.siparis_id = sk.siparis_id
        WHERE s.durum NOT IN ('iptal', 'kapatildi')
        AND (COALESCE(sk.toplam, 0) - COALESCE(s.odenen_tutar, 0)) > 0
        ORDER BY s.tarih DESC
    ");
    
    $toplamlar = ['TL' => 0, 'USD' => 0, 'EUR' => 0];
    $detaylar = [];
    
    if ($siparisResult) {
        while ($row = $siparisResult->fetch_assoc()) {
            $pb = $row['para_birimi'] ?? 'TL';
            $kalan = floatval($row['kalan']);
            $toplamlar[$pb] = ($toplamlar[$pb] ?? 0) + $kalan;
            $detaylar[] = $row;
        }
    }
    
    // TL karşılığını hesapla
    $tlToplam = $toplamlar['TL'] + ($toplamlar['USD'] * $rates['USD']) + ($toplamlar['EUR'] * $rates['EUR']);
    
    return [
        'detay' => $toplamlar,
        'tl_toplam' => round($tlToplam, 2),
        'siparis_sayisi' => count($detaylar),
        'liste' => $detaylar
    ];
}

?>

<?php
include 'config.php'; // Veritabanı bağlantı ayarlarınız

header('Content-Type: application/json; charset=utf-8');

// Ürün kodu parametresini al
$urun_kodu = isset($_GET['urun_kodu']) ? (int)$_GET['urun_kodu'] : null;

if (!$urun_kodu) {
    echo json_encode(['error' => 'Ürün kodu belirtilmemiş'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Ürün bilgileri
$stmt = $connection->prepare("
    SELECT 
        u.urun_kodu,
        u.urun_ismi,
        u.stok_miktari,
        u.kritik_stok_seviyesi,
        u.birim,
        u.satis_fiyati
    FROM 
        urunler u
    WHERE 
        u.urun_kodu = ?
");
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$result = $stmt->get_result();
$urun = $result->fetch_assoc();

if (!$urun) {
    echo json_encode(['error' => 'Ürün bulunamadı'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sipariş miktarları
$stmt = $connection->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN s.durum = 'beklemede' THEN sk.adet ELSE 0 END), 0) AS onay_bekleyen_siparis_miktari,
        COALESCE(SUM(CASE WHEN s.durum = 'onaylandi' THEN sk.adet ELSE 0 END), 0) AS onaylanan_siparis_miktari
    FROM
        siparis_kalemleri sk
        JOIN siparisler s ON (sk.siparis_id = s.siparis_id)
    WHERE
        sk.urun_kodu = ?
    GROUP BY
        sk.urun_kodu
");
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$result = $stmt->get_result();
$siparis_miktarlari = $result->fetch_assoc();

// Eğer sonuç yoksa, varsayılan değerleri ata
if (!$siparis_miktarlari) {
    $siparis_miktarlari = [
        'onay_bekleyen_siparis_miktari' => 0,
        'onaylanan_siparis_miktari' => 0
    ];
}

$onay_bekleyen_siparis_miktari = (float)$siparis_miktarlari['onay_bekleyen_siparis_miktari'];
$onaylanan_siparis_miktari = (float)$siparis_miktarlari['onaylanan_siparis_miktari'];

// Montaj üretimindeki miktar
$stmt = $connection->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN durum = 'uretimde' THEN planlanan_miktar ELSE 0 END), 0) AS montaj_uretimindeki_miktar
    FROM 
        montaj_is_emirleri
    WHERE 
        CAST(urun_kodu AS UNSIGNED) = ?
");
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$result = $stmt->get_result();
$montaj_uretim = $result->fetch_assoc();
$montaj_uretimindeki_miktar = (float)$montaj_uretim['montaj_uretimindeki_miktar'];

// Ürünün bileşenleri
$stmt = $connection->prepare("
    SELECT 
        ua.bilesen_ismi,
        ua.bilesenin_malzeme_turu,
        ua.bilesen_miktari,
        COALESCE(
            CASE 
                WHEN ua.bilesenin_malzeme_turu = 'esans' 
                THEN e.stok_miktari 
                ELSE m.stok_miktari 
            END, 0
        ) AS stok_miktari
    FROM 
        urun_agaci ua
        LEFT JOIN esanslar e ON (ua.bilesen_ismi = e.esans_ismi)
        LEFT JOIN malzemeler m ON (ua.bilesen_ismi = m.malzeme_ismi)
    WHERE 
        ua.urun_kodu = ? 
        AND ua.agac_turu = 'urun'
    ORDER BY 
        ua.bilesen_ismi ASC
");
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$result = $stmt->get_result();
$bilesenler = [];
while ($row = $result->fetch_assoc()) {
    $bilesenler[] = $row;
}

// Her bileşen için ekstra bilgileri al
foreach ($bilesenler as $key => $bilesen) {
    if ($bilesen['bilesenin_malzeme_turu'] != 'esans') {
        // Malzeme ise sipariş verilen miktarı al
        $stmt = $connection->prepare("
            SELECT 
                COALESCE(SUM(miktar), 0) AS siparis_verilen_miktar
            FROM 
                malzeme_siparisler
            WHERE 
                malzeme_ismi = ?
                AND durum = 'siparis_verildi'
        ");
        $stmt->bind_param('s', $bilesen['bilesen_ismi']);
        $stmt->execute();
        $result = $stmt->get_result();
        $siparis_verilen = $result->fetch_assoc();
        
        $bilesenler[$key]['siparis_verilen_miktar'] = (float)$siparis_verilen['siparis_verilen_miktar'];
    } else {
        // Esans ise üretimdeki miktarı ve alt bileşenleri al
        $stmt = $connection->prepare("
            SELECT 
                COALESCE(SUM(planlanan_miktar), 0) AS uretimdeki_miktar
            FROM 
                esans_is_emirleri
            WHERE 
                esans_ismi = ?
                AND durum = 'uretimde'
        ");
        $stmt->bind_param('s', $bilesen['bilesen_ismi']);
        $stmt->execute();
        $result = $stmt->get_result();
        $uretimdeki = $result->fetch_assoc();
        
        $bilesenler[$key]['uretimdeki_miktar'] = (float)$uretimdeki['uretimdeki_miktar'];
        
        // Esansın alt bileşenlerini al
        $stmt = $connection->prepare("
            SELECT 
                e.esans_id
            FROM 
                esanslar e
            WHERE 
                e.esans_ismi = ?
        ");
        $stmt->bind_param('s', $bilesen['bilesen_ismi']);
        $stmt->execute();
        $result = $stmt->get_result();
        $esans_id = $result->fetch_assoc();
        
        if ($esans_id) {
            $stmt = $connection->prepare("
                SELECT 
                    ua.bilesen_ismi AS malzeme_ismi,
                    ua.bilesen_miktari AS malzeme_miktari,
                    COALESCE(m.stok_miktari, 0) AS stok_miktari
                FROM 
                    urun_agaci ua
                    LEFT JOIN malzemeler m ON (ua.bilesen_ismi = m.malzeme_ismi)
                WHERE 
                    ua.urun_kodu = ?
                    AND ua.agac_turu = 'esans'
                ORDER BY 
                    ua.bilesen_ismi ASC
            ");
            $stmt->bind_param('i', $esans_id['esans_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $esans_bilesenleri = [];
            while ($row = $result->fetch_assoc()) {
                $esans_bilesenleri[] = $row;
            }
            
            // Esans alt bileşenleri için sipariş verilen miktarları al
            foreach ($esans_bilesenleri as $ek => $esans_bilesen) {
                $stmt = $connection->prepare("
                    SELECT 
                        COALESCE(SUM(miktar), 0) AS siparis_verilen_miktar
                    FROM 
                        malzeme_siparisler
                    WHERE 
                        malzeme_ismi = ?
                        AND durum = 'siparis_verildi'
                ");
                $stmt->bind_param('s', $esans_bilesen['malzeme_ismi']);
                $stmt->execute();
                $result = $stmt->get_result();
                $siparis_verilen = $result->fetch_assoc();
                
                $esans_bilesenleri[$ek]['siparis_verilen_miktar'] = (float)$siparis_verilen['siparis_verilen_miktar'];
            }
            
            $bilesenler[$key]['esans_bilesenleri'] = $esans_bilesenleri;
        } else {
            $bilesenler[$key]['esans_bilesenleri'] = [];
        }
    }
}

// Üretilebilecek maksimum adet hesaplaması
$uretilebilecek_maksimum_adet = 0;
if (!empty($bilesenler)) {
    $min_adet = PHP_FLOAT_MAX;
    foreach ($bilesenler as $bilesen) {
        if ($bilesen['bilesen_miktari'] > 0) {
            $maksimum_adet = $bilesen['stok_miktari'] / $bilesen['bilesen_miktari'];
            if ($maksimum_adet < $min_adet) {
                $min_adet = $maksimum_adet;
            }
        }
    }
    $uretilebilecek_maksimum_adet = $min_adet == PHP_FLOAT_MAX ? 0 : (int)$min_adet;
}

// İhtiyaç miktarı hesaplaması
$toplam_acik_siparisler = $onay_bekleyen_siparis_miktari + $onaylanan_siparis_miktari;
$mevcut_eldeki_stok = (float)$urun['stok_miktari'] + $montaj_uretimindeki_miktar;

// Stok miktarı = mevcut stok + üretimde olan miktar
// Eğer (Stok miktarı - Toplam açık siparişler) > Kritik stok seviyesi ise ihtiyaç miktarı 0
// Aksi takdirde, ihtiyaç miktarı = Toplam açık siparişler - Stok miktarı
if (($mevcut_eldeki_stok - $toplam_acik_siparisler) > (float)$urun['kritik_stok_seviyesi']) {
    $ihtiyaç_miktari = 0;
} else {
    $ihtiyaç_miktari = max(0, $toplam_acik_siparisler - $mevcut_eldeki_stok);
}

// Kritik stoğu tamamlamak için gereken üretim miktarı
// Hedef: Mevcut eldeki stok + (üretilecek miktar) >= Toplam açık siparişler + Kritik stok seviyesi
$hedeflenen_toplam_stok = $toplam_acik_siparisler + (float)$urun['kritik_stok_seviyesi'];
$uretilmesi_gereken_miktar = max(0, $hedeflenen_toplam_stok - $mevcut_eldeki_stok);

// Analiz detayları
$analiz_detaylari = [
    'hesaplama_adimi_1' => [
        'adim' => 'Mevcut Eldeki Stok Hesabı',
        'aciklama' => 'Mevcut stok + Montaj üretimindeki miktar',
        'formul' => 'Stok Miktarı (' . (float)$urun['stok_miktari'] . ') + Montaj Üretimindeki Miktar (' . $montaj_uretimindeki_miktar . ')',
        'sonuc' => $mevcut_eldeki_stok
    ],
    'hesaplama_adimi_2' => [
        'adim' => 'Toplam Açık Siparişler Hesabı',
        'aciklama' => 'Onay bekleyen + Onaylanan siparişler',
        'formul' => 'Onay Bekleyen (' . $onay_bekleyen_siparis_miktari . ') + Onaylanan (' . $onaylanan_siparis_miktari . ')',
        'sonuc' => $toplam_acik_siparisler
    ],
    'hesaplama_adimi_3' => [
        'adim' => 'Net Stok Durumu Hesabı',
        'aciklama' => 'Mevcut eldeki stok - Toplam açık siparişler',
        'formul' => 'Mevcut Eldeki Stok (' . $mevcut_eldeki_stok . ') - Toplam Açık Siparişler (' . $toplam_acik_siparisler . ')',
        'sonuc' => $mevcut_eldeki_stok - $toplam_acik_siparisler
    ],
    'hesaplama_adimi_4' => [
        'adim' => 'Kritik Stok Seviyesi ile Karşılaştırma',
        'aciklama' => 'Net stok durumu > Kritik stok seviyesi mi?',
        'formul' => 'Net Stok Durumu (' . ($mevcut_eldeki_stok - $toplam_acik_siparisler) . ') > Kritik Stok Seviyesi (' . (float)$urun['kritik_stok_seviyesi'] . ')',
        'sonuc' => ($mevcut_eldeki_stok - $toplam_acik_siparisler) > (float)$urun['kritik_stok_seviyesi'] ? 'Evet' : 'Hayır'
    ],
    'hesaplama_adimi_5' => [
        'adim' => 'İhtiyaç Miktarı Hesabı',
        'aciklama' => 'Koşul sağlandığında: 0, Aksi halde: (Toplam açık siparişler - Mevcut eldeki stok)',
        'formul' => ($mevcut_eldeki_stok - $toplam_acik_siparisler) > (float)$urun['kritik_stok_seviyesi'] ? '0 (Yeterli stok var)' : 'Toplam Açık Siparişler (' . $toplam_acik_siparisler . ') - Mevcut Eldeki Stok (' . $mevcut_eldeki_stok . ')',
        'sonuc' => $ihtiyaç_miktari
    ],
    'hesaplama_adimi_6' => [
        'adim' => 'Hedeflenen Toplam Stok Hesabı',
        'aciklama' => 'Toplam açık siparişler + Kritik stok seviyesi',
        'formul' => 'Toplam Açık Siparişler (' . $toplam_acik_siparisler . ') + Kritik Stok Seviyesi (' . (float)$urun['kritik_stok_seviyesi'] . ')',
        'sonuc' => $hedeflenen_toplam_stok
    ],
    'hesaplama_adimi_7' => [
        'adim' => 'Üretilmesi Gereken Miktar Hesabı',
        'aciklama' => 'Hedeflenen toplam stok - Mevcut eldeki stok',
        'formul' => 'Hedeflenen Toplam Stok (' . $hedeflenen_toplam_stok . ') - Mevcut Eldeki Stok (' . $mevcut_eldeki_stok . ')',
        'sonuc' => $uretilmesi_gereken_miktar
    ]
];

// Sonuçları oluştur
$result = [
    'urun_kodu' => $urun['urun_kodu'],
    'urun_ismi' => $urun['urun_ismi'],
    'stok_miktari' => (float)$urun['stok_miktari'],
    'kritik_stok_seviyesi' => (float)$urun['kritik_stok_seviyesi'],
    'birim' => $urun['birim'],
    'satis_fiyati' => (float)$urun['satis_fiyati'],
    'onay_bekleyen_siparis_miktari' => $onay_bekleyen_siparis_miktari,
    'onaylanan_siparis_miktari' => $onaylanan_siparis_miktari,
    'toplam_acik_siparisler' => $toplam_acik_siparisler,
    'montaj_uretimindeki_miktar' => $montaj_uretimindeki_miktar,
    'mevcut_eldeki_stok' => $mevcut_eldeki_stok,
    'ihtiyaç_miktari' => $ihtiyaç_miktari,
    'uretilmesi_gereken_miktar' => $uretilmesi_gereken_miktar,
    'analiz_detaylari' => $analiz_detaylari,
    'bilesenler_ve_miktarlar' => $bilesenler,
    'eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar' => $uretilebilecek_maksimum_adet
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
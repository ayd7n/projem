<?php
include 'config.php'; // Veritabanı bağlantı ayarlarınız

header('Content-Type: application/json; charset=utf-8');

// Ürün kodu parametresini al
$urun_kodu = isset($_GET['urun_kodu']) ? (int) $_GET['urun_kodu'] : null;

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

$onay_bekleyen_siparis_miktari = (float) $siparis_miktarlari['onay_bekleyen_siparis_miktari'];
$onaylanan_siparis_miktari = (float) $siparis_miktarlari['onaylanan_siparis_miktari'];

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
$montaj_uretimindeki_miktar = (float) $montaj_uretim['montaj_uretimindeki_miktar'];

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

        $bilesenler[$key]['siparis_verilen_miktar'] = (float) $siparis_verilen['siparis_verilen_miktar'];
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

        $bilesenler[$key]['uretimdeki_miktar'] = (float) $uretimdeki['uretimdeki_miktar'];

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

                $esans_bilesenleri[$ek]['siparis_verilen_miktar'] = (float) $siparis_verilen['siparis_verilen_miktar'];
            }

            $bilesenler[$key]['esans_bilesenleri'] = $esans_bilesenleri;
        } else {
            $bilesenler[$key]['esans_bilesenleri'] = [];
        }
    }
}

// Üretilebilecek maksimum adet hesaplaması (Rekürsif - Esans üretimi dahil)
$uretilebilecek_maksimum_adet = 0;
$kisitlayan_bilesen_ismi = '-';
$kapasite_hesaplama_detaylari = []; // Yeni: Adım adım log

if (!empty($bilesenler)) {
    $min_adet = PHP_FLOAT_MAX;
    $adim_no = 1;

    foreach ($bilesenler as $bilesen) {
        if ($bilesen['bilesen_miktari'] > 0) {

            $bilesen_log = [
                'adim' => $adim_no++,
                'bilesen_ismi' => $bilesen['bilesen_ismi'],
                'bilesen_turu' => $bilesen['bilesenin_malzeme_turu'],
                'birim_recete' => $bilesen['bilesen_miktari'],
                'direkt_stok' => (float) $bilesen['stok_miktari'],
                'alt_hesaplamalar' => []
            ];

            // 1. Mevcut direkt stok
            $toplam_potansiyel_kaynak = (float) $bilesen['stok_miktari'];

            // 2. Eğer esans ise, alt bileşenlerinden ne kadar üretilebileceğine bak
            if ($bilesen['bilesenin_malzeme_turu'] == 'esans' && !empty($bilesen['esans_bilesenleri'])) {
                $esans_uretim_kapasitesi = PHP_FLOAT_MAX;

                foreach ($bilesen['esans_bilesenleri'] as $alt) {
                    if ($alt['malzeme_miktari'] > 0) {
                        // Alt bileşenin stoğu ile kaç adet bu esanstan üretilebilir?
                        $alt_kapasite = (float) $alt['stok_miktari'] / $alt['malzeme_miktari'];

                        $bilesen_log['alt_hesaplamalar'][] = [
                            'malzeme' => $alt['malzeme_ismi'],
                            'stok' => (float) $alt['stok_miktari'],
                            'recete' => $alt['malzeme_miktari'],
                            'kapasite' => $alt_kapasite,
                            'formul' => $alt['stok_miktari'] . ' ÷ ' . $alt['malzeme_miktari'] . ' = ' . number_format($alt_kapasite, 2)
                        ];

                        if ($alt_kapasite < $esans_uretim_kapasitesi) {
                            $esans_uretim_kapasitesi = $alt_kapasite;
                        }
                    }
                }

                if ($esans_uretim_kapasitesi != PHP_FLOAT_MAX) {
                    $toplam_potansiyel_kaynak += $esans_uretim_kapasitesi;
                    $bilesen_log['uretilebilir_esans'] = $esans_uretim_kapasitesi;
                } else {
                    $bilesen_log['uretilebilir_esans'] = 0;
                }
            }

            // 3. Ana ürün için bu bileşenden (veya üretilebilir potansiyelden) kaç adet çıkar?
            $bilesen_bazli_max_urun = $toplam_potansiyel_kaynak / $bilesen['bilesen_miktari'];

            $bilesen_log['toplam_kaynak'] = $toplam_potansiyel_kaynak;
            $bilesen_log['max_urun'] = $bilesen_bazli_max_urun;
            $bilesen_log['formul'] = number_format($toplam_potansiyel_kaynak, 2) . ' ÷ ' . $bilesen['bilesen_miktari'] . ' = ' . number_format($bilesen_bazli_max_urun, 2);

            if ($bilesen_bazli_max_urun < $min_adet) {
                $min_adet = $bilesen_bazli_max_urun;
                $kisitlayan_bilesen_ismi = $bilesen['bilesen_ismi'];
                $bilesen_log['kisitlayici_mi'] = true;
            } else {
                $bilesen_log['kisitlayici_mi'] = false;
            }

            $kapasite_hesaplama_detaylari[] = $bilesen_log;
        }
    }
    $uretilebilecek_maksimum_adet = $min_adet == PHP_FLOAT_MAX ? 0 : (int) $min_adet;
}

// İhtiyaç miktarı hesaplaması
$toplam_acik_siparisler = $onay_bekleyen_siparis_miktari + $onaylanan_siparis_miktari;
$mevcut_eldeki_stok = (float) $urun['stok_miktari'] + $montaj_uretimindeki_miktar;

// Stok miktarı = mevcut stok + üretimde olan miktar
// Eğer (Stok miktarı - Toplam açık siparişler) > Kritik stok seviyesi ise ihtiyaç miktarı 0
// Aksi takdirde, ihtiyaç miktarı = Toplam açık siparişler - Stok miktarı
if (($mevcut_eldeki_stok - $toplam_acik_siparisler) > (float) $urun['kritik_stok_seviyesi']) {
    $ihtiyaç_miktari = 0;
} else {
    $ihtiyaç_miktari = max(0, $toplam_acik_siparisler - $mevcut_eldeki_stok);
}

// MRP Hesaplamaları - DÜZENLENMİŞ
// 1. Brüt İhtiyaç (Toplam açık siparişler)
$brut_ihtiyac = $toplam_acik_siparisler;

// 2. Mevcut Eldeki Stok (depodaki + üretimdeki)
$mevcut_eldeki_stok = (float) $urun['stok_miktari'] + $montaj_uretimindeki_miktar;

// 3. Net İhtiyaç Hesaplaması
// Kritik stok seviyesi sadece güvenlik stoğu olarak kullanılır
$hedeflenen_guvenlik_stogu = (float) $urun['kritik_stok_seviyesi'];
$hedef_toplam_stok = $brut_ihtiyac + $hedeflenen_guvenlik_stogu;  // Hedef: siparişler + güvenlik stoğu

// Net üretim ihtiyacı = Hedeflenen toplam stok - mevcut eldeki stok
$net_uretim_ihtiyaci = max(0, $hedef_toplam_stok - $mevcut_eldeki_stok);

// Gerçek üretim emri miktarı hesaplaması
// Ancak bu miktar sadece siparişlere cevap verecek kadar olmalı, fazla güvenlik stoğu üretimi yapılmamalı
$siparislere_cevap_uretim = max(0, $brut_ihtiyac - $mevcut_eldeki_stok);
$uretilmesi_gereken_miktar = $siparislere_cevap_uretim + $hedeflenen_guvenlik_stogu;

// Ancak bu miktar mevcut eldeki stok + üretimdeki stok ile karşılanabiliyorsa üretim emri gerekmez
if ($mevcut_eldeki_stok >= $brut_ihtiyac + $hedeflenen_guvenlik_stogu) {
    $uretilmesi_gereken_miktar = 0;
} else {
    $uretilmesi_gereken_miktar = max(0, ($brut_ihtiyac + $hedeflenen_guvenlik_stogu) - $mevcut_eldeki_stok);
}

// Analiz detayları
$analiz_detaylari = [
    'hesaplama_adimi_1' => [
        'adim' => 'Mevcut Eldeki Stok Hesabı',
        'aciklama' => 'Mevcut stok + Montaj üretimindeki miktar',
        'formul' => 'Stok Miktarı (' . (float) $urun['stok_miktari'] . ') + Montaj Üretimindeki Miktar (' . $montaj_uretimindeki_miktar . ')',
        'sonuc' => $mevcut_eldeki_stok
    ],
    'hesaplama_adimi_2' => [
        'adim' => 'Toplam Açık Siparişler Hesabı',
        'aciklama' => 'Onay bekleyen + Onaylanan siparişler',
        'formul' => 'Onay Bekleyen (' . $onay_bekleyen_siparis_miktari . ') + Onaylanan (' . $onaylanan_siparis_miktari . ')',
        'sonuc' => $toplam_acik_siparisler
    ],
    'hesaplama_adimi_3' => [
        'adim' => 'Brüt İhtiyaç Hesabı',
        'aciklama' => 'Toplam sipariş miktarı',
        'formul' => 'Toplam Açık Siparişler (' . $toplam_acik_siparisler . ')',
        'sonuc' => $brut_ihtiyac
    ],
    'hesaplama_adimi_4' => [
        'adim' => 'Güvenlik Stoğu Hesabı',
        'aciklama' => 'Kritik stok seviyesi',
        'formul' => 'Kritik Stok Seviyesi (' . (float) $urun['kritik_stok_seviyesi'] . ')',
        'sonuc' => $hedeflenen_guvenlik_stogu
    ],
    'hesaplama_adimi_5' => [
        'adim' => 'Hedeflenen Toplam Stok',
        'aciklama' => 'Siparişler + Güvenlik stoğu',
        'formul' => 'Brüt İhtiyaç (' . $brut_ihtiyac . ') + Güvenlik Stoğu (' . $hedeflenen_guvenlik_stogu . ')',
        'sonuc' => $hedef_toplam_stok
    ],
    'hesaplama_adimi_6' => [
        'adim' => 'Net Üretim İhtiyacı',
        'aciklama' => 'Hedeflenen toplam stok - mevcut eldeki stok',
        'formul' => 'Hedeflenen Toplam Stok (' . $hedef_toplam_stok . ') - Mevcut Eldeki Stok (' . $mevcut_eldeki_stok . ')',
        'sonuc' => $uretilmesi_gereken_miktar
    ]
];

// Kaynak Planlaması ve Detaylı İhtiyaç Analizi
$kaynak_planlamasi = [
    'uretilmesi_gereken_urunler' => [],
    'uretilmesi_gereken_esanslar' => [],
    'satin_alinmasi_gereken_malzemeler' => []
];

// =========================================================================================
// GELİŞTİRİLMİŞ MRP (MALZEME İHTİYAÇ PLANLAMASI) MOTORU
// =========================================================================================

// --- SEVİYE 0: ANA ÜRETİM PLANI (MPS) ---
$mps_brut_ihtiyac = $brut_ihtiyac;
$mps_eldeki_stok = $mevcut_eldeki_stok;
$mps_net_uretim_ihtiyaci = $uretilmesi_gereken_miktar; // Yeni hesaplamadan gelen değer

// --- SEVİYE 1: BİLEŞEN İHTİYAÇLARI (BOM EXPLOSION - LEVEL 1) ---
// Ana ürünün (Level 0) net üretim ihtiyacını alt bileşenlere (Level 1) dağıt.

// Global hammadde havuzu (Level 2 ihtiyaçları için)
$global_malzeme_talepleri = []; // Malzeme -> Miktar

if ($mps_net_uretim_ihtiyaci > 0) {
    foreach ($bilesenler as $bilesen) {
        // Brüt Bileşen İhtiyacı = Ana Ürün Net İhtiyaç * Reçete Miktarı
        $level1_brut_ihtiyac = $mps_net_uretim_ihtiyaci * $bilesen['bilesen_miktari'];

        // Esans için detaylı mevcut kaynak hesabı
        if ($bilesen['bilesenin_malzeme_turu'] == 'esans') {
            $level1_eldeki = (float) $bilesen['stok_miktari'];
            $level1_uretimdeki = (float) $bilesen['uretimdeki_miktar'];

            // Üretilebilir esans miktarı hesaplaması - sadece yeterli hammadde varsa üretilebilir
            $level1_uretilebilir = 0;
            if (!empty($bilesen['esans_bilesenleri'])) {
                $esans_uretim_kapasitesi = PHP_FLOAT_MAX;

                foreach ($bilesen['esans_bilesenleri'] as $alt) {
                    if ($alt['malzeme_miktari'] > 0) {
                        // Her alt bileşenin stoğuna göre üretilebilir miktar hesaplanır
                        $alt_kapasite = floor((float) $alt['stok_miktari'] / $alt['malzeme_miktari']);
                        if ($alt_kapasite < $esans_uretim_kapasitesi) {
                            $esans_uretim_kapasitesi = $alt_kapasite;
                        }
                    }
                }

                if ($esans_uretim_kapasitesi != PHP_FLOAT_MAX) {
                    $level1_uretilebilir = $esans_uretim_kapasitesi;
                }
            }

            // Toplam mevcut kaynak (depodaki + üretimdeki + üretilebilir)
            $level1_toplam_kaynak = $level1_eldeki + $level1_uretimdeki + $level1_uretilebilir;

        } else {
            // Malzeme/Ürün için mevcut kaynak hesabı
            $level1_yoldaki = (float) $bilesen['siparis_verilen_miktar'];
            $level1_eldeki = (float) $bilesen['stok_miktari'];
            $level1_uretimdeki = 0;
            $level1_uretilebilir = 0;
            $level1_toplam_kaynak = $level1_eldeki + $level1_yoldaki;
        }

        // Net Bileşen İhtiyacı - sadece eksik olan kısım
        $level1_net_ihtiyac = max(0, $level1_brut_ihtiyac - $level1_toplam_kaynak);

        if ($level1_net_ihtiyac > 0) {
            // Eğer bu bir ESANS ise, üretim planlaması yapılır
            if ($bilesen['bilesenin_malzeme_turu'] == 'esans') {
                // Esans İş Emri Önerisi - YENİ ve DÜZENLENMİŞ
                // Hemen üretilebilecek miktar sadece yeterli hammadde olan miktar kadar olmalı
                $hemen_uretilecek_esans = min($level1_net_ihtiyac, $level1_uretilebilir);
                $bekleyen_uretim_esans = max(0, $level1_net_ihtiyac - $level1_uretilebilir);

                $kaynak_planlamasi['uretilmesi_gereken_esanslar'][] = [
                    'esans_ismi' => $bilesen['bilesen_ismi'],
                    'gerekli_miktar' => $level1_brut_ihtiyac,
                    'uretimdeki_miktar' => $level1_uretimdeki,
                    'depodaki_miktar' => $level1_eldeki,
                    'uretilebilir_miktar' => $level1_uretilebilir,
                    'mevcut_kaynak' => $level1_toplam_kaynak,
                    'uretim_emri_miktari' => $level1_net_ihtiyac,
                    'hemen_uretilebilecek_miktar' => $hemen_uretilecek_esans,
                    'bekleyen_uretim_miktari' => $bekleyen_uretim_esans
                ];

                // --- SEVİYE 2: ESANS ALT BİLEŞENLERİ ---
                if (!empty($bilesen['esans_bilesenleri'])) {
                    foreach ($bilesen['esans_bilesenleri'] as $alt) {
                        // Sadece eksik olan miktar için alt bileşen talebi oluşturulur
                        $level2_brut_ihtiyac = $level1_net_ihtiyac * $alt['malzeme_miktari'];
                        // Ancak zaten mevcut stok düşülmüşse sadece net ihtiyaç kadar alt bileşen gerekir
                        $level2_net_ihtiyac = max(0, $level2_brut_ihtiyac - (float) $alt['stok_miktari']);

                        $malzeme_adi = $alt['malzeme_ismi'];

                        if (!isset($global_malzeme_talepleri[$malzeme_adi])) {
                            $global_malzeme_talepleri[$malzeme_adi] = [
                                'brut_ihtiyac' => 0,
                                'net_ihtiyac' => 0,
                                'stok' => (float) $alt['stok_miktari'],
                                'yolda' => (float) $alt['siparis_verilen_miktar']
                            ];
                        }
                        $global_malzeme_talepleri[$malzeme_adi]['brut_ihtiyac'] += $level2_brut_ihtiyac;
                        $global_malzeme_talepleri[$malzeme_adi]['net_ihtiyac'] += $level2_net_ihtiyac;
                    }
                }
            } elseif ($bilesen['bilesenin_malzeme_turu'] == 'urun') {
                // Ürün için üretim önerisi
                $hemen_uretilecek_urun = min($level1_net_ihtiyac, $level1_uretilebilir);
                $bekleyen_uretim_urun = max(0, $level1_net_ihtiyac - $level1_uretilebilir);

                $kaynak_planlamasi['uretilmesi_gereken_urunler'][] = [
                    'urun_ismi' => $bilesen['bilesen_ismi'],
                    'gerekli_miktar' => $level1_brut_ihtiyac,
                    'mevcut_kaynak' => $level1_toplam_kaynak,
                    'uretim_emri_miktari' => $level1_net_ihtiyac,
                    'hemen_uretilebilecek_miktar' => $hemen_uretilecek_urun,
                    'bekleyen_uretim_miktari' => $bekleyen_uretim_urun
                ];
            } else {
                // Direkt malzeme için talep
                $malzeme_adi = $bilesen['bilesen_ismi'];
                if (!isset($global_malzeme_talepleri[$malzeme_adi])) {
                    $global_malzeme_talepleri[$malzeme_adi] = [
                        'brut_ihtiyac' => 0,
                        'net_ihtiyac' => 0,
                        'stok' => $level1_eldeki,
                        'yolda' => $level1_yoldaki
                    ];
                }
                // Sadece eksik olan miktar eklenir
                $net_malzeme_ihtiyaci = max(0, $level1_brut_ihtiyac - ($level1_eldeki + $level1_yoldaki));
                $global_malzeme_talepleri[$malzeme_adi]['brut_ihtiyac'] += $level1_brut_ihtiyac;
                $global_malzeme_talepleri[$malzeme_adi]['net_ihtiyac'] += $net_malzeme_ihtiyaci;
            }
        }
    }
}

// --- SEVİYE 2 SONU: SATIN ALMA KARARLARI ---
// Tüm talepler toplanır ve mevcut kaynaklar düşülür
foreach ($global_malzeme_talepleri as $malzeme_adi => $veri) {
    $toplam_brut_ihtiyac = $veri['brut_ihtiyac'];
    $mevcut_kaynak = $veri['stok'] + $veri['yolda'];
    $net_satin_alma = max(0, $toplam_brut_ihtiyac - $mevcut_kaynak);

    if ($net_satin_alma > 0) {
        $kaynak_planlamasi['satin_alinmasi_gereken_malzemeler'][] = [
            'malzeme_ismi' => $malzeme_adi,
            'toplam_gereken' => $toplam_brut_ihtiyac,
            'mevcut_kaynak' => $mevcut_kaynak,
            'satin_alma_miktari' => $net_satin_alma,
            'stok_miktari' => $veri['stok'],
            'yolda_miktar' => $veri['yolda']
        ];
    }
}

// Sonuçları oluştur
$result = [
    'urun_kodu' => $urun['urun_kodu'],
    'urun_ismi' => $urun['urun_ismi'],
    'stok_miktari' => (float) $urun['stok_miktari'],
    'kritik_stok_seviyesi' => (float) $urun['kritik_stok_seviyesi'],
    'birim' => $urun['birim'],
    'satis_fiyati' => (float) $urun['satis_fiyati'],
    'onay_bekleyen_siparis_miktari' => $onay_bekleyen_siparis_miktari,
    'onaylanan_siparis_miktari' => $onaylanan_siparis_miktari,
    'toplam_acik_siparisler' => $toplam_acik_siparisler,
    'montaj_uretimindeki_miktar' => $montaj_uretimindeki_miktar,
    'mevcut_eldeki_stok' => $mevcut_eldeki_stok,
    'ihtiyaç_miktari' => $ihtiyaç_miktari,
    'uretilmesi_gereken_miktar' => $uretilmesi_gereken_miktar,
    'analiz_detaylari' => $analiz_detaylari,
    'bilesenler_ve_miktarlar' => $bilesenler,
    'eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar' => $uretilebilecek_maksimum_adet,
    'kisitlayan_bilesen' => $kisitlayan_bilesen_ismi,
    'kapasite_hesaplama_detaylari' => $kapasite_hesaplama_detaylari,
    'kaynak_planlamasi' => $kaynak_planlamasi
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
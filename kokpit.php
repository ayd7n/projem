<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

// Fetch all necessary data for the supply chain dashboard
function getSupplyChainData($connection) {
    $data = [];

    // 0. Bekleyen Satınalma Siparişlerini Malzeme Bazında Al
    $pending_purchase_orders = [];
    $pending_purchase_orders_by_name = [];
    
    $p_orders_query = "SELECT ssk.malzeme_kodu, ssk.malzeme_adi,
                              SUM(ssk.miktar - ssk.teslim_edilen_miktar) as bekleyen,
                              GROUP_CONCAT(DISTINCT ss.siparis_no SEPARATOR ', ') as po_list
                       FROM satinalma_siparisler ss
                       JOIN satinalma_siparis_kalemleri ssk ON ss.siparis_id = ssk.siparis_id
                       WHERE ss.durum IN ('taslak', 'onaylandi', 'olusturuldu', 'gonderildi', 'kismen_teslim', 'yollandi')
                       GROUP BY ssk.malzeme_kodu, ssk.malzeme_adi
                       HAVING bekleyen > 0";
    $p_orders_result = $connection->query($p_orders_query);
    if ($p_orders_result) {
        while ($po = $p_orders_result->fetch_assoc()) {
            if (!empty($po['malzeme_kodu'])) {
                $pending_purchase_orders[$po['malzeme_kodu']] = [
                    'miktar' => floatval($po['bekleyen']),
                    'po_list' => $po['po_list']
                ];
            }
            if (!empty($po['malzeme_adi'])) {
                $pending_purchase_orders_by_name[$po['malzeme_adi']] = [
                    'miktar' => floatval($po['bekleyen']),
                    'po_list' => $po['po_list']
                ];
            }
        }
    }

    // 1. Bileşen Eksiklik Kontrolü
    $products_query = "SELECT urun_kodu, urun_ismi FROM urunler WHERE urun_tipi = 'uretilen' ORDER BY urun_ismi";
    $products_result = $connection->query($products_query);

    $bileşen_eksik_urunler = [];
    $required_types = ['kutu', 'takm', 'etiket', 'paket', 'jelatin', 'esans'];

    while ($product = $products_result->fetch_assoc()) {
        $urun_kodu = $product['urun_kodu'];

        // Get existing BOM components for this product
        $bom_query = "SELECT bilesenin_malzeme_turu
                      FROM urun_agaci
                      WHERE urun_kodu = ? AND agac_turu = 'urun'";
        $bom_stmt = $connection->prepare($bom_query);
        $bom_stmt->bind_param('i', $urun_kodu);
        $bom_stmt->execute();
        $bom_result = $bom_stmt->get_result();

        $existing_types = [];
        while ($bom = $bom_result->fetch_assoc()) {
            $type = strtolower($bom['bilesenin_malzeme_turu']);
            $existing_types[] = $type;
        }
        $bom_stmt->close();

        // Calculate missing types
        $missing_types = array_diff($required_types, $existing_types);

        if (!empty($missing_types)) {
            $missing_labels = [];
            foreach ($missing_types as $type) {
                switch ($type) {
                    case 'kutu': $missing_labels[] = 'Kutu'; break;
                    case 'takm': $missing_labels[] = 'Takım'; break;
                    case 'etiket': $missing_labels[] = 'Etiket'; break;
                    case 'paket': $missing_labels[] = 'Paket'; break;
                    case 'jelatin': $missing_labels[] = 'Jelatin'; break;
                    case 'esans': $missing_labels[] = 'Esans'; break;
                    default: $missing_labels[] = $type; break;
                }
            }
            $bileşen_eksik_urunler[] = [
                'urun_kodu' => $product['urun_kodu'],
                'urun_ismi' => $product['urun_ismi'],
                'eksik_bilesenler' => $missing_labels
            ];
        }
    }
    $data['bileşen_eksik_urunler'] = $bileşen_eksik_urunler;

    // 2. Esans Reçete ve İş Emri Kontrolü
    $esanslar_query = "SELECT esans_kodu, esans_ismi FROM esanslar ORDER BY esans_ismi";
    $esanslar_result = $connection->query($esanslar_query);

    $esans_reçete_eksik = [];
    $esans_is_emri_eksik = [];

    while ($esans = $esanslar_result->fetch_assoc()) {
        // Check if essence has BOM entries in urun_agaci with agac_turu = 'esans'
        $esans_bom_query = "SELECT COUNT(*) as count FROM urun_agaci
                            WHERE agac_turu = 'esans' AND urun_ismi = ?";
        $esans_bom_stmt = $connection->prepare($esans_bom_query);
        $esans_bom_stmt->bind_param('s', $esans['esans_ismi']);
        $esans_bom_stmt->execute();
        $esans_bom_result = $esans_bom_stmt->get_result()->fetch_assoc();
        $esans_bom_count = $esans_bom_result['count'];
        $esans_bom_stmt->close();

        if ($esans_bom_count == 0) {
            $esans_reçete_eksik[] = [
                'esans_kodu' => $esans['esans_kodu'],
                'esans_ismi' => $esans['esans_ismi']
            ];
        }

        // Check if there's a work order for this essence
        $esans_emri_query = "SELECT COUNT(*) as count FROM esans_is_emirleri
                             WHERE esans_kodu = ?";
        $esans_emri_stmt = $connection->prepare($esans_emri_query);
        $esans_emri_stmt->bind_param('s', $esans['esans_kodu']);
        $esans_emri_stmt->execute();
        $esans_emri_result = $esans_emri_stmt->get_result()->fetch_assoc();
        $esans_emri_count = $esans_emri_result['count'];
        $esans_emri_stmt->close();

        if ($esans_emri_count == 0 && $esans_bom_count > 0) {
            $esans_is_emri_eksik[] = [
                'esans_kodu' => $esans['esans_kodu'],
                'esans_ismi' => $esans['esans_ismi']
            ];
        }
    }
    $data['esans_reçete_eksik'] = $esans_reçete_eksik;
    $data['esans_is_emri_eksik'] = $esans_is_emri_eksik;

    // 3. Stok ve Kritik Seviye Kontrolü
    $kritik_urunler = [];
    $kritik_esanslar = [];

    // Check products
    $urun_stok_query = "SELECT urun_kodu, urun_ismi, stok_miktari, kritik_stok_seviyesi
                        FROM urunler
                        WHERE kritik_stok_seviyesi > 0 AND stok_miktari <= kritik_stok_seviyesi
                        ORDER BY (stok_miktari - kritik_stok_seviyesi)";
    $urun_stok_result = $connection->query($urun_stok_query);
    while ($urun = $urun_stok_result->fetch_assoc()) {
        $kritik_urunler[] = [
            'urun_kodu' => $urun['urun_kodu'],
            'urun_ismi' => $urun['urun_ismi'],
            'stok' => $urun['stok_miktari'],
            'kritik' => $urun['kritik_stok_seviyesi']
        ];
    }

    // Check essences
    $esans_stok_query = "SELECT esans_kodu, esans_ismi, stok_miktari FROM esanslar ORDER BY esans_ismi";
    $esans_stok_result = $connection->query($esans_stok_query);
    while ($esans = $esans_stok_result->fetch_assoc()) {
        // Get critical stock level from products that use this essence
        $kritik_query = "SELECT MIN(kritik_stok_seviyesi) as min_kritik
                         FROM urunler u
                         JOIN urun_agaci ua ON u.urun_kodu = ua.urun_kodu
                         WHERE ua.bilesen_ismi = ? AND ua.bilesenin_malzeme_turu = 'esans'";
        $kritik_stmt = $connection->prepare($kritik_query);
        $kritik_stmt->bind_param('s', $esans['esans_ismi']);
        $kritik_stmt->execute();
        $kritik_result = $kritik_stmt->get_result()->fetch_assoc();
        $kritik_seviye = $kritik_result['min_kritik'] ?? 0;
        $kritik_stmt->close();

        if ($kritik_seviye > 0 && $esans['stok_miktari'] <= $kritik_seviye) {
            $kritik_esanslar[] = [
                'esans_kodu' => $esans['esans_kodu'],
                'esans_ismi' => $esans['esans_ismi'],
                'stok' => $esans['stok_miktari']
            ];
        }
    }
    $data['kritik_urunler'] = $kritik_urunler;
    $data['kritik_esanslar'] = $kritik_esanslar;

    // 4. Çerçeve Sözleşme Kontrolü
    $bilesen_sozlesme_eksik = [];
    $bilesenler_query = "SELECT DISTINCT b.bilesen_kodu, b.bilesen_ismi, b.bilesenin_malzeme_turu
                         FROM urun_agaci b
                         WHERE b.agac_turu = 'urun'";
    $bilesenler_result = $connection->query($bilesenler_query);

    while ($bilesen = $bilesenler_result->fetch_assoc()) {
        $sozlesme_query = "SELECT COUNT(*) as count FROM cerceve_sozlesmeler
                           WHERE malzeme_kodu = ?";
        $sozlesme_stmt = $connection->prepare($sozlesme_query);
        $sozlesme_stmt->bind_param('s', $bilesen['bilesen_kodu']);
        $sozlesme_stmt->execute();
        $sozlesme_result = $sozlesme_stmt->get_result()->fetch_assoc();
        $sozlesme_count = $sozlesme_result['count'];
        $sozlesme_stmt->close();

        if ($sozlesme_count == 0) {
            $bilesen_sozlesme_eksik[] = [
                'bilesen_kodu' => $bilesen['bilesen_kodu'],
                'bilesen_ismi' => $bilesen['bilesen_ismi'],
                'tur' => $bilesen['bilesenin_malzeme_turu']
            ];
        }
    }
    $data['bilesen_sozlesme_eksik'] = $bilesen_sozlesme_eksik;

    // 4.5 Gecerli Sozlesmeleri Onbellege Al (Tüm tedarikçiler fiyat sıralı)
    $valid_contracts = [];
    $vc_query = "SELECT cs.malzeme_kodu, t.tedarikci_adi, cs.birim_fiyat, cs.para_birimi
                 FROM cerceve_sozlesmeler cs
                 JOIN cerceve_sozlesmeler_gecerlilik csg ON cs.sozlesme_id = csg.sozlesme_id
                 JOIN tedarikciler t ON cs.tedarikci_id = t.tedarikci_id
                 WHERE csg.gecerli_mi = 1
                 ORDER BY cs.malzeme_kodu, cs.birim_fiyat ASC";
    $vc_result = $connection->query($vc_query);
    if ($vc_result) {
        while ($vc = $vc_result->fetch_assoc()) {
            // Her malzeme için tüm tedarikçileri sakla (fiyat sıralı)
            if (!isset($valid_contracts[$vc['malzeme_kodu']])) {
                $valid_contracts[$vc['malzeme_kodu']] = [
                    'en_uygun' => [
                        'tedarikci_adi' => $vc['tedarikci_adi'],
                        'birim_fiyat' => $vc['birim_fiyat'],
                        'para_birimi' => $vc['para_birimi']
                    ],
                    'tum_tedarikciler' => []
                ];
            }
            // Tüm tedarikçileri listeye ekle
            $valid_contracts[$vc['malzeme_kodu']]['tum_tedarikciler'][] = [
                'adi' => $vc['tedarikci_adi'],
                'fiyat' => $vc['birim_fiyat'],
                'para_birimi' => $vc['para_birimi']
            ];
        }
    }

    // 5. Üretim Kapasitesi Hesabı (from ne_uretsem.php logic)
    $uretilebilir_urunler = [];
    $uretilebilir_esanslar = [];

    // Önce onaylanmış ama sevk edilmemiş (tamamlanmamış) sipariş miktarlarını hesapla
    $siparis_miktarlari = [];
    $siparis_query = "SELECT sk.urun_kodu, SUM(sk.adet) as siparis_miktari
                      FROM siparis_kalemleri sk
                      JOIN siparisler s ON sk.siparis_id = s.siparis_id
                      WHERE s.durum = 'onaylandi'
                      GROUP BY sk.urun_kodu";
    $siparis_result = $connection->query($siparis_query);
    if ($siparis_result) {
        while ($sp = $siparis_result->fetch_assoc()) {
            $siparis_miktarlari[$sp['urun_kodu']] = floatval($sp['siparis_miktari']);
        }
    }

    // Calculate producible products based on ne_uretsem.php logic
    $products_query = "
        SELECT
            u.urun_kodu,
            u.urun_ismi,
            u.stok_miktari,
            u.kritik_stok_seviyesi,
            u.birim,
            u.urun_tipi,
            COALESCE(SUM(CASE WHEN mie.durum IN ('baslatildi', 'uretimde') THEN mie.planlanan_miktar ELSE 0 END), 0) AS uretimde_miktar
        FROM urunler u
        LEFT JOIN montaj_is_emirleri mie ON u.urun_kodu = mie.urun_kodu AND mie.durum IN ('baslatildi', 'uretimde')
        GROUP BY u.urun_kodu
        ORDER BY u.urun_ismi
    ";
    $result = $connection->query($products_query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stok = floatval($row['stok_miktari']);
            $uretimde = floatval($row['uretimde_miktar']);
            $kritik = floatval($row['kritik_stok_seviyesi']);
            $toplam_mevcut = $stok + $uretimde;
            
            // Onaylanmış sipariş miktarını al
            $siparis_miktari = isset($siparis_miktarlari[$row['urun_kodu']]) ? $siparis_miktarlari[$row['urun_kodu']] : 0;
            $row['siparis_miktari'] = $siparis_miktari;

            // HAZIR ALINAN ÜRÜNLER İÇİN ÖZEL MANTIK
            if ($row['urun_tipi'] === 'hazir_alinan') {
                $yoldaki_miktar = 0;
                $po_list = '';
                
                if (isset($pending_purchase_orders_by_name[$row['urun_ismi']])) {
                    $yoldaki_miktar = $pending_purchase_orders_by_name[$row['urun_ismi']]['miktar'];
                    $po_list = $pending_purchase_orders_by_name[$row['urun_ismi']]['po_list'];
                } elseif (isset($pending_purchase_orders[$row['urun_kodu']])) {
                     $yoldaki_miktar = $pending_purchase_orders[$row['urun_kodu']]['miktar'];
                     $po_list = $pending_purchase_orders[$row['urun_kodu']]['po_list'];
                }

                $toplam_mevcut = $stok + $yoldaki_miktar;
                $toplam_hedef = $siparis_miktari + $kritik;
                
                if ($toplam_hedef > 0) {
                    $yuzde_fark = (($toplam_hedef - $toplam_mevcut) / $toplam_hedef) * 100;
                } else {
                    $yuzde_fark = ($toplam_mevcut > 0) ? -100 : 0;
                }
                
                $acik = max(0, $toplam_hedef - $toplam_mevcut);
                $acik_kritik = max(0, $kritik - $toplam_mevcut);
                
                $row['bilesen_detaylari'] = [];
                $row['bilesen_uretilebilir'] = [];
                $row['eksik_bilesenler'] = [];
                $row['esans_agaci_eksik'] = [];
                $row['sozlesme_eksik_malzemeler'] = [];
                $row['esans_uretim_bilgisi'] = [];
                
                $row['toplam_mevcut'] = $toplam_mevcut;
                $row['acik_kritik'] = $acik_kritik;
                $row['acik'] = $acik;
                $row['yuzde_fark'] = $yuzde_fark;
                $row['uretilebilir_miktar'] = PHP_INT_MAX; 
                $row['onerilen_uretim'] = 0;
                $row['onerilen_satinalma'] = $acik;
                $row['yoldaki_miktar'] = $yoldaki_miktar;
                $row['po_list'] = $po_list;
                
                $row['aksiyon_onerisi'] = getAksiyonOnerisi($row);
                
                $uretilebilir_urunler[] = $row;
                continue;
            }

            // Kritik seviye hesabı
            // Kritik seviye hesabı (Eski) yerine Toplam Hedef Bazlı Fark Hesabı
            $toplam_hedef = $siparis_miktari + $kritik;
            
            if ($toplam_hedef > 0) {
                // Hedefin ne kadar altındayız (Pozitif) veya üstündeyiz (Negatif)
                // Örn: Hedef 100, Mevcut 80 -> (100-80)/100 = %20 (Eksik)
                // Örn: Hedef 100, Mevcut 120 -> (100-120)/100 = -%20 (Fazla)
                $yuzde_fark = (($toplam_hedef - $toplam_mevcut) / $toplam_hedef) * 100;
                $acik_kritik = max(0, $kritik - $toplam_mevcut);
            } else {
                // Hedef yoksa
                $acik_kritik = 0;
                if ($toplam_mevcut > 0) {
                    $yuzde_fark = -100; // Hedef yok ama stok var -> %100 Fazla (gibi)
                } else {
                    $yuzde_fark = 0; // Hepsi 0
                }
            }
            
            // Toplam açık = Kritik açık + Sipariş miktarı (stoktan karşılanamayan kısım)
            // Sipariş için gereken: mevcut stoktan karşılanamayan miktar
            $siparis_icin_gereken = max(0, $siparis_miktari - $stok);
            
            // Toplam açık: hem kritik seviyeyi hem de siparişi karşılayacak miktar
            // Eğer stok varsa önce siparişleri karşıla, kalan stokla kritik seviyeyi değerlendir
            $stok_sonrasi = max(0, $stok - $siparis_miktari); // Siparişler karşılandıktan sonra kalan stok
            $uretimle_birlikte = $stok_sonrasi + $uretimde; // Üretimdekilerle birlikte
            
            if ($kritik > 0) {
                $acik = max(0, $kritik - $uretimle_birlikte) + $siparis_icin_gereken;
            } else {
                $acik = $siparis_icin_gereken;
            }

            // Üretilebilir miktar hesapla
            $uretilebilir_miktar = 0;
            $kritik_bilesen_turleri = ['kutu', 'takm', 'esans'];
            $uretilebilir_kritik = PHP_INT_MAX;
            
            // Bileşen durumu için değişkenler
            $required_types = ['esans', 'kutu', 'etiket', 'takm', 'paket', 'jelatin'];
            $existing_types = [];
            $esans_kodu_bilesenler = []; // Esans bileşenlerinin kodları
            
            // Her bileşen türü için üretilebilir miktarlar (JavaScript için)
            $bilesen_uretilebilir = [
                'kutu' => PHP_INT_MAX,
                'etiket' => PHP_INT_MAX,
                'takm' => PHP_INT_MAX,
                'esans' => PHP_INT_MAX,
                'paket' => PHP_INT_MAX,
                'jelatin' => PHP_INT_MAX
            ];

            $bom_query = "SELECT ua.bilesen_miktari, ua.bilesenin_malzeme_turu as bilesen_turu, ua.bilesen_kodu,
                         CASE
                            WHEN m.malzeme_kodu IS NOT NULL THEN m.stok_miktari
                            WHEN ur.urun_kodu IS NOT NULL THEN ur.stok_miktari
                            WHEN e.esans_kodu IS NOT NULL THEN e.stok_miktari
                            ELSE 0
                         END as bilesen_stok,
                         CASE
                            WHEN m.malzeme_kodu IS NOT NULL THEN m.birim
                            WHEN ur.urun_kodu IS NOT NULL THEN ur.birim
                            WHEN e.esans_kodu IS NOT NULL THEN e.birim
                            ELSE ''
                         END as bilesen_birim,
                         CASE
                            WHEN m.malzeme_kodu IS NOT NULL THEN m.malzeme_ismi
                            WHEN ur.urun_kodu IS NOT NULL THEN ur.urun_ismi
                            WHEN e.esans_kodu IS NOT NULL THEN e.esans_ismi
                            ELSE ua.bilesen_kodu
                         END as bilesen_ismi
                         FROM urun_agaci ua
                         LEFT JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                         LEFT JOIN urunler ur ON ua.bilesen_kodu = ur.urun_kodu
                         LEFT JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
                         WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?";
            $bom_stmt = $connection->prepare($bom_query);
            $bom_stmt->bind_param('i', $row['urun_kodu']);
            $bom_stmt->execute();
            $bom_result = $bom_stmt->get_result();
            
            // Bileşen detayları (eksik miktar hesabı için)
            $bilesen_detaylari = [];

            while ($bom_row = $bom_result->fetch_assoc()) {
                $gerekli = floatval($bom_row['bilesen_miktari']);
                $mevcut = floatval($bom_row['bilesen_stok']);
                $bilesen_turu_lower = strtolower($bom_row['bilesen_turu']);
                $bilesen_ismi = $bom_row['bilesen_ismi'];
                
                // Mevcut bileşen türlerini kaydet
                if (!in_array($bilesen_turu_lower, $existing_types)) {
                    $existing_types[] = $bilesen_turu_lower;
                }
                
                // Esans bileşenlerinin kodlarını kaydet
                if ($bilesen_turu_lower === 'esans') {
                    $esans_kodu_bilesenler[] = $bom_row['bilesen_kodu'];
                }

                if ($gerekli > 0) {
                    $bu_bilesenden = max(0, floor($mevcut / $gerekli));
                    
                    // Tedarikçi bilgisini al (en uygun + tüm tedarikçiler)
                    $tedarikci_info = null;
                    $tum_tedarikciler = [];
                    if (isset($valid_contracts[$bom_row['bilesen_kodu']])) {
                        $contract_data = $valid_contracts[$bom_row['bilesen_kodu']];
                        $tedarikci_info = [
                            'adi' => $contract_data['en_uygun']['tedarikci_adi'],
                            'fiyat' => $contract_data['en_uygun']['birim_fiyat'],
                            'para_birimi' => $contract_data['en_uygun']['para_birimi']
                        ];
                        $tum_tedarikciler = $contract_data['tum_tedarikciler'];
                    }
                    
                    // Bileşen detaylarını kaydet
                    $bilesen_detaylari[] = [
                        'isim' => $bilesen_ismi,
                        'kodu' => $bom_row['bilesen_kodu'],
                        'tur' => $bilesen_turu_lower,
                        'birim' => $bom_row['bilesen_birim'],
                        'gerekli_adet' => $gerekli,
                        'mevcut_stok' => $mevcut,
                        'uretilebilir' => $bu_bilesenden,
                        'yoldaki_stok' => $pending_purchase_orders[$bom_row['bilesen_kodu']]['miktar'] ?? 0,
                        'po_list' => $pending_purchase_orders[$bom_row['bilesen_kodu']]['po_list'] ?? '',
                        'sozlesme_var' => isset($valid_contracts[$bom_row['bilesen_kodu']]),
                        'tedarikci' => $tedarikci_info,
                        'tum_tedarikciler' => $tum_tedarikciler
                    ];
                    
                    // Her bileşen türü için ayrı üretilebilir hesapla
                    if (isset($bilesen_uretilebilir[$bilesen_turu_lower])) {
                        $bilesen_uretilebilir[$bilesen_turu_lower] = min($bilesen_uretilebilir[$bilesen_turu_lower], $bu_bilesenden);
                    }
                    
                    if (in_array($bilesen_turu_lower, $kritik_bilesen_turleri)) {
                        $uretilebilir_kritik = min($uretilebilir_kritik, $bu_bilesenden);
                    }
                }
            }
            $bom_stmt->close();
            
            $row['bilesen_detaylari'] = $bilesen_detaylari;
            
            // PHP_INT_MAX olanları 0'a çevir (bileşen yok demek)
            foreach ($bilesen_uretilebilir as $key => $val) {
                if ($val === PHP_INT_MAX) {
                    $bilesen_uretilebilir[$key] = null; // null = bu bileşen yok
                }
            }
            $row['bilesen_uretilebilir'] = $bilesen_uretilebilir;
            
            // Eksik bileşen türlerini hesapla
            $eksik_bilesenler = [];
            foreach ($required_types as $type) {
                if (!in_array($type, $existing_types)) {
                    switch ($type) {
                        case 'esans': $eksik_bilesenler[] = 'Esans'; break;
                        case 'kutu': $eksik_bilesenler[] = 'Kutu'; break;
                        case 'etiket': $eksik_bilesenler[] = 'Etiket'; break;
                        case 'takm': $eksik_bilesenler[] = 'Takım'; break;
                        case 'paket': $eksik_bilesenler[] = 'Paket'; break;
                        case 'jelatin': $eksik_bilesenler[] = 'Jelatin'; break;
                    }
                }
            }
            
            // Esans ağacı kontrolü - ürünün esans bileşenlerinin ağacı var mı?
            $esans_agaci_eksik = [];
            foreach ($esans_kodu_bilesenler as $esans_kodu) {
                // Önce esans ismini al
                $esans_isim_query = "SELECT esans_ismi FROM esanslar WHERE esans_kodu = ?";
                $esans_isim_stmt = $connection->prepare($esans_isim_query);
                $esans_isim_stmt->bind_param('s', $esans_kodu);
                $esans_isim_stmt->execute();
                $esans_isim_result = $esans_isim_stmt->get_result()->fetch_assoc();
                $esans_ismi = $esans_isim_result ? $esans_isim_result['esans_ismi'] : null;
                $esans_isim_stmt->close();
                
                if ($esans_ismi) {
                    // Bu esans ismiyle ürün ağacında esans formülü var mı kontrol et
                    $esans_agac_query = "SELECT COUNT(*) as cnt FROM urun_agaci WHERE agac_turu = 'esans' AND urun_ismi = ?";
                    $esans_agac_stmt = $connection->prepare($esans_agac_query);
                    $esans_agac_stmt->bind_param('s', $esans_ismi);
                    $esans_agac_stmt->execute();
                    $esans_agac_result = $esans_agac_stmt->get_result()->fetch_assoc();
                    
                    if ($esans_agac_result['cnt'] == 0) {
                        $esans_agaci_eksik[] = $esans_ismi;
                    }
                    $esans_agac_stmt->close();
                }
            }
            
            // Sözleşme durumu kontrolü
            $sozlesme_eksik_malzemeler = [];
            $tum_malzeme_kodlari = [];
            
            // 1. Ürün ağacındaki esans harici bileşenlerin malzeme kodlarını al
            $malzeme_query = "SELECT ua.bilesen_kodu, m.malzeme_ismi 
                              FROM urun_agaci ua
                              JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                              WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ? 
                              AND LOWER(ua.bilesenin_malzeme_turu) != 'esans'";
            $malzeme_stmt = $connection->prepare($malzeme_query);
            $malzeme_stmt->bind_param('i', $row['urun_kodu']);
            $malzeme_stmt->execute();
            $malzeme_result = $malzeme_stmt->get_result();
            while ($malzeme_row = $malzeme_result->fetch_assoc()) {
                $tum_malzeme_kodlari[$malzeme_row['bilesen_kodu']] = $malzeme_row['malzeme_ismi'];
            }
            $malzeme_stmt->close();
            
            // 2. Esans bileşenlerinin ağaçlarındaki malzeme kodlarını al
            foreach ($esans_kodu_bilesenler as $esans_kodu) {
                $esans_malz_query = "SELECT ua.bilesen_kodu, m.malzeme_ismi 
                                     FROM urun_agaci ua
                                     JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                                     WHERE ua.agac_turu = 'esans' AND ua.urun_kodu = ?";
                $esans_malz_stmt = $connection->prepare($esans_malz_query);
                $esans_malz_stmt->bind_param('s', $esans_kodu);
                $esans_malz_stmt->execute();
                $esans_malz_result = $esans_malz_stmt->get_result();
                while ($esans_malz_row = $esans_malz_result->fetch_assoc()) {
                    $tum_malzeme_kodlari[$esans_malz_row['bilesen_kodu']] = $esans_malz_row['malzeme_ismi'];
                }
                $esans_malz_stmt->close();
            }
            
            // 3. Her malzeme için kullanılabilir sözleşme var mı kontrol et
            foreach ($tum_malzeme_kodlari as $malzeme_kodu => $malzeme_ismi) {
                $sozlesme_query = "SELECT COUNT(*) as cnt FROM cerceve_sozlesmeler_gecerlilik 
                                   WHERE malzeme_kodu = ? AND gecerli_mi = 1";
                $sozlesme_stmt = $connection->prepare($sozlesme_query);
                $sozlesme_stmt->bind_param('s', $malzeme_kodu);
                $sozlesme_stmt->execute();
                $sozlesme_result = $sozlesme_stmt->get_result()->fetch_assoc();
                
                if ($sozlesme_result['cnt'] == 0) {
                    $sozlesme_eksik_malzemeler[] = $malzeme_ismi;
                }
                $sozlesme_stmt->close();
            }
            
            $row['sozlesme_eksik_malzemeler'] = $sozlesme_eksik_malzemeler;
            
            // 4. Esans üretim bilgisi kontrolü (açık iş emri ve malzeme durumu)
            $esans_uretim_bilgisi = [];
            foreach ($esans_kodu_bilesenler as $esans_kodu) {
                // Esans ismini al
                $esans_isim_query = "SELECT esans_ismi, birim FROM esanslar WHERE esans_kodu = ?";
                $esans_isim_stmt = $connection->prepare($esans_isim_query);
                $esans_isim_stmt->bind_param('s', $esans_kodu);
                $esans_isim_stmt->execute();
                $esans_isim_result = $esans_isim_stmt->get_result()->fetch_assoc();
                $esans_ismi = $esans_isim_result ? $esans_isim_result['esans_ismi'] : $esans_kodu;
                $esans_birim = ($esans_isim_result && !empty($esans_isim_result['birim'])) ? $esans_isim_result['birim'] : 'ml';
                $esans_isim_stmt->close();
                
                // Açık iş emirlerini kontrol et (olusturuldu veya uretimde durumunda)
                $is_emri_query = "SELECT SUM(planlanan_miktar) as toplam_miktar 
                                  FROM esans_is_emirleri 
                                  WHERE esans_kodu = ? AND durum IN ('olusturuldu', 'uretimde')";
                $is_emri_stmt = $connection->prepare($is_emri_query);
                $is_emri_stmt->bind_param('s', $esans_kodu);
                $is_emri_stmt->execute();
                $is_emri_result = $is_emri_stmt->get_result()->fetch_assoc();
                $acik_is_emri_miktar = floatval($is_emri_result['toplam_miktar'] ?? 0);
                $is_emri_stmt->close();
                
                // Esans formülündeki malzemelerin stok durumunu kontrol et
                $esans_malzeme_eksik = [];
                $esans_malzeme_yeterli = true;
                $esans_hammaddeden_uretilebilir = PHP_INT_MAX; // Hammadde ile ne kadar üretilebilir
                $formul_var = false;
                $esans_formul_detaylari = [];
                
                $esans_formul_query = "SELECT ua.bilesen_kodu, ua.bilesen_miktari, 
                                       m.malzeme_ismi, m.stok_miktari, m.birim
                                       FROM urun_agaci ua
                                       JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                                       WHERE ua.agac_turu = 'esans' AND ua.urun_ismi = ?";
                $esans_formul_stmt = $connection->prepare($esans_formul_query);
                $esans_formul_stmt->bind_param('s', $esans_ismi);
                $esans_formul_stmt->execute();
                $esans_formul_result = $esans_formul_stmt->get_result();
                
                while ($formul_row = $esans_formul_result->fetch_assoc()) {
                    $formul_var = true;
                    $gerekli = floatval($formul_row['bilesen_miktari']);
                    $mevcut = floatval($formul_row['stok_miktari']);

                    $esans_formul_detaylari[] = [
                        'malzeme_kodu' => $formul_row['bilesen_kodu'],
                        'malzeme_ismi' => $formul_row['malzeme_ismi'],
                        'recete_miktari' => $gerekli,
                        'mevcut_stok' => $mevcut,
                        'birim' => $formul_row['birim'],
                        'bekleyen_siparis' => $pending_purchase_orders[$formul_row['bilesen_kodu']]['miktar'] ?? 0,
                        'po_list' => $pending_purchase_orders[$formul_row['bilesen_kodu']]['po_list'] ?? ''
                    ];

                    // Basit kontrol: malzeme stoğu 0 ise eksik
                    if ($mevcut <= 0) {
                        $esans_malzeme_eksik[] = $formul_row['malzeme_ismi'];
                        $esans_malzeme_yeterli = false;
                    }

                    // Üretilebilir miktar hesabı
                    if ($gerekli > 0) {
                        $bu_malzemeden = floor($mevcut / $gerekli);
                        $esans_hammaddeden_uretilebilir = min($esans_hammaddeden_uretilebilir, $bu_malzemeden);
                    }
                }
                $esans_formul_stmt->close();
                
                if (!$formul_var) {
                    $esans_hammaddeden_uretilebilir = 0;
                }

                $esans_uretim_bilgisi[$esans_kodu] = [
                    'esans_kodu' => $esans_kodu,
                    'esans_ismi' => $esans_ismi,
                    'birim' => $esans_birim,
                    'acik_is_emri_miktar' => $acik_is_emri_miktar,
                    'malzeme_yeterli' => $esans_malzeme_yeterli,
                    'eksik_malzemeler' => $esans_malzeme_eksik,
                    'uretilebilir_miktar' => ($esans_hammaddeden_uretilebilir === PHP_INT_MAX) ? 0 : $esans_hammaddeden_uretilebilir,
                    'formul_detaylari' => $esans_formul_detaylari
                ];
            }
            $row['esans_uretim_bilgisi'] = $esans_uretim_bilgisi;
            
            $row['eksik_bilesenler'] = $eksik_bilesenler;
            $row['esans_agaci_eksik'] = $esans_agaci_eksik;

            $uretilebilir_miktar = ($uretilebilir_kritik === PHP_INT_MAX) ? 0 : $uretilebilir_kritik;
            
            // Önerilen üretim: toplam açığı karşılayacak, üretilebilir miktarla sınırlı
            $onerilen_uretim = max(0, min($acik, $uretilebilir_miktar));

            $row['toplam_mevcut'] = $toplam_mevcut;
            $row['acik_kritik'] = $acik_kritik; // Sadece kritik için açık
            $row['acik'] = $acik; // Toplam açık (kritik + sipariş)
            $row['yuzde_fark'] = $yuzde_fark;
            $row['uretilebilir_miktar'] = $uretilebilir_miktar;
            $row['onerilen_uretim'] = $onerilen_uretim;

            // Aksiyon önerisi hesapla
            $row['aksiyon_onerisi'] = getAksiyonOnerisi($row);

            $uretilebilir_urunler[] = $row;
        }
    }
    $data['uretilebilir_urunler'] = $uretilebilir_urunler;

    // Calculate producible essences
    $esanslar_result2 = $connection->query("SELECT esans_kodu, esans_ismi FROM esanslar ORDER BY esans_ismi");
    while ($esans = $esanslar_result2->fetch_assoc()) {
        $bom_query = "SELECT ua.bilesen_miktari, ua.bilesen_ismi,
                             COALESCE(m.stok_miktari, e.stok_miktari, u.stok_miktari, 0) as bilesen_stok
                      FROM urun_agaci ua
                      LEFT JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                      LEFT JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
                      LEFT JOIN urunler u ON ua.bilesen_kodu = u.urun_kodu
                      WHERE ua.agac_turu = 'esans' AND ua.urun_ismi = ?";
        $bom_stmt = $connection->prepare($bom_query);
        $bom_stmt->bind_param('s', $esans['esans_ismi']);
        $bom_stmt->execute();
        $bom_result = $bom_stmt->get_result();

        $min_producible = PHP_INT_MAX;
        $has_bom = false;

        while ($bom = $bom_result->fetch_assoc()) {
            $has_bom = true;
            $gerekli = floatval($bom['bilesen_miktari']);
            $mevcut = floatval($bom['bilesen_stok']);

            if ($gerekli > 0) {
                $uretilebilen = floor($mevcut / $gerekli);
                $min_producible = min($min_producible, $uretilebilen);
            }
        }
        $bom_stmt->close();

        if ($has_bom && $min_producible > 0) {
            $uretilebilir_esanslar[] = [
                'esans_kodu' => $esans['esans_kodu'],
                'esans_ismi' => $esans['esans_ismi'],
                'uretilebilir_miktar' => $min_producible
            ];
        }
    }
    $data['uretilebilir_esanslar'] = $uretilebilir_esanslar;

    // 6. Aktif Üretim Emirleri Takibi
    $aktif_urun_emirleri = [];
    $aktif_esans_emirleri = [];

    $urun_emirleri_query = "SELECT mi.is_emri_numarasi, mi.urun_kodu, u.urun_ismi, mi.planlanan_miktar, mi.durum
                            FROM montaj_is_emirleri mi
                            JOIN urunler u ON mi.urun_kodu = u.urun_kodu
                            WHERE mi.durum IN ('baslatildi', 'uretimde')";
    $urun_emirleri_result = $connection->query($urun_emirleri_query);
    while ($emir = $urun_emirleri_result->fetch_assoc()) {
        $aktif_urun_emirleri[] = [
            'is_emri_numarasi' => $emir['is_emri_numarasi'],
            'urun_ismi' => $emir['urun_ismi'],
            'planlanan_miktar' => $emir['planlanan_miktar'],
            'durum' => $emir['durum']
        ];
    }

    $esans_emirleri_query = "SELECT ei.is_emri_numarasi, ei.esans_kodu, e.esans_ismi, ei.planlanan_miktar, ei.durum
                             FROM esans_is_emirleri ei
                             JOIN esanslar e ON ei.esans_kodu = e.esans_kodu
                             WHERE ei.durum = 'uretimde'";
    $esans_emirleri_result = $connection->query($esans_emirleri_query);
    while ($emir = $esans_emirleri_result->fetch_assoc()) {
        $aktif_esans_emirleri[] = [
            'is_emri_numarasi' => $emir['is_emri_numarasi'],
            'esans_ismi' => $emir['esans_ismi'],
            'planlanan_miktar' => $emir['planlanan_miktar'],
            'durum' => $emir['durum']
        ];
    }
    $data['aktif_urun_emirleri'] = $aktif_urun_emirleri;
    $data['aktif_esans_emirleri'] = $aktif_esans_emirleri;

    // 7. Müşteri Siparişleri Takibi
    $eksik_siparis_urunleri = [];
    $siparis_kalemleri_query = "SELECT sk.urun_ismi, SUM(sk.adet) as toplam_adet
                                FROM siparis_kalemleri sk
                                JOIN siparisler s ON sk.siparis_id = s.siparis_id
                                WHERE s.durum = 'onaylandi'
                                GROUP BY sk.urun_ismi";
    $siparis_kalemleri_result = $connection->query($siparis_kalemleri_query);
    while ($kalem = $siparis_kalemleri_result->fetch_assoc()) {
        $urun_query = "SELECT urun_kodu, urun_ismi, stok_miktari FROM urunler WHERE urun_ismi = ?";
        $urun_stmt = $connection->prepare($urun_query);
        $urun_stmt->bind_param('s', $kalem['urun_ismi']);
        $urun_stmt->execute();
        $urun_result = $urun_stmt->get_result();

        if ($urun = $urun_result->fetch_assoc()) {
            $eksik_miktar = $kalem['toplam_adet'] - $urun['stok_miktari'];
            if ($eksik_miktar > 0) {
                $eksik_siparis_urunleri[] = [
                    'urun_ismi' => $urun['urun_ismi'],
                    'toplam_miktar' => $kalem['toplam_adet'],
                    'stok' => $urun['stok_miktari'],
                    'eksik' => $eksik_miktar
                ];
            }
        }
        $urun_stmt->close();
    }
    $data['eksik_siparis_urunleri'] = $eksik_siparis_urunleri;

    // 8. Sipariş ve Malzeme Takibi
    $bekleyen_siparisler = [];
    $siparisler_query = "SELECT ss.siparis_id, ss.siparis_no, ss.tedarikci_adi, ssk.malzeme_adi,
                                ssk.miktar, ssk.teslim_edilen_miktar
                         FROM satinalma_siparisler ss
                         JOIN satinalma_siparis_kalemleri ssk ON ss.siparis_id = ssk.siparis_id
                         WHERE ss.durum IN ('gonderildi', 'kismen_teslim')";
    $siparisler_result = $connection->query($siparisler_query);
    while ($siparis = $siparisler_result->fetch_assoc()) {
        $bekleyen_miktar = $siparis['miktar'] - $siparis['teslim_edilen_miktar'];
        if ($bekleyen_miktar > 0) {
            $bekleyen_siparisler[] = [
                'siparis_no' => $siparis['siparis_no'],
                'tedarikci_adi' => $siparis['tedarikci_adi'],
                'malzeme_adi' => $siparis['malzeme_adi'],
                'bekleyen_miktar' => $bekleyen_miktar
            ];
        }
    }
    $data['bekleyen_siparisler'] = $bekleyen_siparisler;

    // 9. Talep Çakışması Kontrolü
    $talep_cakismalari = [];
    // Check for shared components between products and essences
    $shared_components_query = "SELECT ua1.bilesen_ismi as component_name,
                                       GROUP_CONCAT(DISTINCT ua1.urun_ismi) as products_using,
                                       GROUP_CONCAT(DISTINCT ua2.urun_ismi) as essences_using
                                FROM urun_agaci ua1
                                JOIN urun_agaci ua2 ON ua1.bilesen_ismi = ua2.bilesen_ismi
                                WHERE ua1.agac_turu = 'urun' AND ua2.agac_turu = 'esans'
                                GROUP BY ua1.bilesen_ismi";
    $shared_result = $connection->query($shared_components_query);
    while ($shared = $shared_result->fetch_assoc()) {
        $talep_cakismalari[] = [
            'malzeme_adi' => $shared['component_name'],
            'urunler' => $shared['products_using'],
            'esanslar' => $shared['essences_using']
        ];
    }
    $data['talep_cakismalari'] = $talep_cakismalari;

    // 10. Esans Sipariş ve Üretim Takibi
    $esans_siparis_takibi = [];
    $esans_siparis_query = "SELECT ss.siparis_no, e.esans_ismi, ssk.miktar, ssk.teslim_edilen_miktar
                            FROM satinalma_siparisler ss
                            JOIN satinalma_siparis_kalemleri ssk ON ss.siparis_id = ssk.siparis_id
                            JOIN esanslar e ON ssk.malzeme_adi = e.esans_ismi
                            WHERE ss.durum IN ('gonderildi', 'kismen_teslim')";
    $esans_siparis_result = $connection->query($esans_siparis_query);
    while ($siparis = $esans_siparis_result->fetch_assoc()) {
        $bekleyen_miktar = $siparis['miktar'] - $siparis['teslim_edilen_miktar'];
        if ($bekleyen_miktar > 0) {
            $esans_siparis_takibi[] = [
                'siparis_no' => $siparis['siparis_no'],
                'esans_ismi' => $siparis['esans_ismi'],
                'bekleyen_miktar' => $bekleyen_miktar
            ];
        }
    }
    $data['esans_siparis_takibi'] = $esans_siparis_takibi;

    // 11. Aksiyon Önerileri
    $aksiyon_onerileri = [];

    // Add recommendations based on the data
    if (!empty($data['bileşen_eksik_urunler'])) {
        $aksiyon_onerileri[] = [
            'tip' => 'warning',
            'mesaj' => 'Bazı ürünlerin bileşenleri eksik. Bu ürünler için üretim yapılamaz.',
            'tavsiye' => 'Eksik bileşenleri tanımlayın'
        ];
    }

    if (!empty($data['kritik_urunler'])) {
        $aksiyon_onerileri[] = [
            'tip' => 'danger',
            'mesaj' => 'Bazı ürünler kritik seviyenin altında. Acil üretim gerekebilir.',
            'tavsiye' => 'Üretim planı oluşturun'
        ];
    }

    if (!empty($data['esans_reçete_eksik'])) {
        $aksiyon_onerileri[] = [
            'tip' => 'warning',
            'mesaj' => 'Bazı esansların reçeteleri eksik.',
            'tavsiye' => 'Esans reçetelerini oluşturun'
        ];
    }

    if (!empty($data['bilesen_sozlesme_eksik'])) {
        $aksiyon_onerileri[] = [
            'tip' => 'warning',
            'mesaj' => 'Bazı bileşenler için çerçeve sözleşme eksik.',
            'tavsiye' => 'Çerçeve sözleşme oluşturun'
        ];
    }

    if (!empty($data['talep_cakismalari'])) {
        $aksiyon_onerileri[] = [
            'tip' => 'info',
            'mesaj' => 'Bazı malzemeler hem ürün hem esans üretiminde kullanılıyor.',
            'tavsiye' => 'Kaynak çakışmalarını gözden geçirin'
        ];
    }

    $data['aksiyon_onerileri'] = $aksiyon_onerileri;

    return $data;
}

// Aksiyon Önerisi Hesaplama Fonksiyonu
function getAksiyonOnerisi($p) {
    // ÖZEL: Hazır alınan ürünler için kontrol
    if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan') {
        $acik = floatval($p['acik']);
        $yoldaki = isset($p['yoldaki_miktar']) ? floatval($p['yoldaki_miktar']) : 0;
        
        if ($acik > 0) {
            // Açık zaten yoldaki miktar düşülerek hesaplanmış olabilir, ama burada netleştiriyoruz.
            // Yukarıdaki mantıkta acik = hedef - (stok + yoldaki) idi. Yani açık zaten net ihtiyaçtır.
            // Ancak yoldaki miktar 0 ise ve açık varsa kesin sipariş gerekir.
            
            return [
                'class' => 'badge-aksiyon-uyari',
                'icon' => 'fas fa-cart-plus',
                'mesaj' => 'Ürün Satın Alınmalı',
                'detay' => 'Stok yetersiz. <strong>' . number_format($acik, 0, ',', '.') . ' ' . $p['birim'] . '</strong> alınmalı.',
                'category' => 'order',
                'buton' => [
                    'text' => 'Satın Al',
                    'url' => 'satinalma_siparisler.php',
                    'icon' => 'fas fa-shopping-cart'
                ]
            ];
        } elseif ($yoldaki > 0) {
             return [
                'class' => 'badge-aksiyon-bilgi',
                'icon' => 'fas fa-truck',
                'mesaj' => 'Sipariş Yolda',
                'detay' => 'Bekleyen siparişler var (' . number_format($yoldaki, 0, ',', '.') . ').',
                'category' => 'info',
                'buton' => null
            ];
        }
        
        return [
            'class' => 'badge-aksiyon-basarili',
            'icon' => 'fas fa-check-circle',
            'mesaj' => 'Stok Yeterli',
            'detay' => 'Hazır ürün stoğu yeterli seviyede.',
            'category' => 'ok',
            'buton' => null
        ];
    }

    $acik = floatval($p['acik']);
    $eksik_bilesenler = isset($p['eksik_bilesenler']) ? $p['eksik_bilesenler'] : [];
    $esans_agaci_eksik = isset($p['esans_agaci_eksik']) ? $p['esans_agaci_eksik'] : [];
    $sozlesme_eksik = isset($p['sozlesme_eksik_malzemeler']) ? $p['sozlesme_eksik_malzemeler'] : [];
    $uretilebilir = floatval($p['uretilebilir_miktar']);
    $bilesen_detaylari = isset($p['bilesen_detaylari']) ? $p['bilesen_detaylari'] : [];
    
    // Esans İhtiyaçlarını Belirle (Doğru Hammadde Hesabı İçin)
    $net_esans_ihtiyaclari = []; // [esans_kodu => miktar]
    
    if ($acik > 0) {
        foreach ($bilesen_detaylari as $bilesen) {
            if ($bilesen['tur'] === 'esans') {
                $esans_kodu = $bilesen['kodu'];
                $birim_ihtiyac = floatval($bilesen['gerekli_adet']); // 1 Ürün için gereken esans
                $brut_ihtiyac = $acik * $birim_ihtiyac; // Toplam gereken esans
                
                // Mevcut Esans Stoğunu ve Üretimdekini bul
                $stok = floatval($bilesen['mevcut_stok']);
                $uretimde = 0;
                if (isset($p['esans_uretim_bilgisi'][$esans_kodu])) {
                    $uretimde = floatval($p['esans_uretim_bilgisi'][$esans_kodu]['acik_is_emri_miktar']);
                }
                
                $net_ihtiyac = max(0, $brut_ihtiyac - ($stok + $uretimde));
                
                if ($net_ihtiyac > 0) {
                    if (!isset($net_esans_ihtiyaclari[$esans_kodu])) {
                        $net_esans_ihtiyaclari[$esans_kodu] = 0;
                    }
                    $net_esans_ihtiyaclari[$esans_kodu] += $net_ihtiyac;
                }
            }
        }
    }
    
    // 1. VERİ EKSİKLİĞİ VARSA (En Yüksek Öncelik)
    if (count($eksik_bilesenler) > 0 || count($esans_agaci_eksik) > 0) {
        $detay = [];
        $adim_listesi = [];
        
        if (count($eksik_bilesenler) > 0) {
            $detay[] = '<strong class="text-danger"><i class="fas fa-exclamation-circle"></i> Eksik Bileşenler:</strong><br>' . implode(', ', $eksik_bilesenler);
            $adim_listesi[] = '<div style="margin-bottom: 8px;">
                <strong style="color: #ffc107;"><i class="fas fa-box"></i> Malzemeler İçin:</strong><br>
                <small style="margin-left: 20px;">
                • Eğer malzemeyi <strong>tanımlamadıysanız</strong> → <a href="malzemeler.php" target="_blank" class="text-primary">Buradan tanımlayın <i class="fas fa-external-link-alt fa-xs"></i></a><br>
                • Malzemeyi tanımladıysanız → <a href="urun_agaclari.php" target="_blank" class="text-primary">Ürün ağacına bağlayın <i class="fas fa-external-link-alt fa-xs"></i></a>
                </small>
            </div>';
        }
        
        if (count($esans_agaci_eksik) > 0) {
            $detay[] = '<strong class="text-danger"><i class="fas fa-flask"></i> Formülü Olmayan Esanslar:</strong><br>' . implode(', ', $esans_agaci_eksik);
            $adim_listesi[] = '<div style="margin-bottom: 8px;">
                <strong style="color: #0078d4;"><i class="fas fa-flask"></i> Esanslar İçin:</strong><br>
                <small style="margin-left: 20px;">
                • <a href="urun_agaclari.php" target="_blank" class="text-primary">Ürün Ağaçları sayfasına gidin <i class="fas fa-external-link-alt fa-xs"></i></a><br>
                • <strong>Esans Ağacı</strong> sekmesinden formül oluşturun<br>
                • Formül oluşturduktan sonra ürün ağacına bağlayın
                </small>
            </div>';
        }
        
        // Tek bir "Yapılması Gerekenler" kutusu
        $adimlar = '<div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 8px; border-left: 3px solid #0078d4;">
            <strong><i class="fas fa-info-circle text-primary"></i> Yapılması Gerekenler:</strong><br>
            <div style="margin-top: 6px;">' . implode('', $adim_listesi) . '</div>
        </div>';
        
        return [
            'class' => 'badge-aksiyon-kritik',
            'icon' => 'fas fa-exclamation-triangle',
            'mesaj' => 'Ürün Ağacı ve Esans Formüllerini Tamamlayın',
            'detay' => implode('<br>', $detay) . $adimlar,
            'category' => 'critical',
            'buton' => [
                'text' => 'Ürün Ağacı',
                'url' => 'urun_agaclari.php',
                'icon' => 'fas fa-sitemap'
            ]
        ];
    }
    
    // 2. REÇETE TAMAM AMA AÇIK > 0 VE SÖZLEŞME EKSİKSE
    if ($acik > 0 && count($sozlesme_eksik) > 0) {
        $stok_yeterli = ($uretilebilir >= $acik);
        $detay = '<strong class="text-warning"><i class="fas fa-file-contract"></i> Sözleşmesi Olmayan Malzemeler:</strong><br>' . implode(', ', array_slice($sozlesme_eksik, 0, 5));
        if (count($sozlesme_eksik) > 5) {
            $detay .= '<br><small class="text-muted">+' . (count($sozlesme_eksik) - 5) . ' malzeme daha</small>';
        }
        
        $adimlar = '<div style="background: #fff3cd; padding: 8px; border-radius: 4px; margin-top: 6px; border-left: 3px solid #ffc107;">
            <strong><i class="fas fa-info-circle text-warning"></i> Yapılması Gerekenler:</strong><br>
            <small>
            1️⃣ <a href="cerceve_sozlesmeler.php" target="_blank" class="text-primary">Çerçeve sözleşme sayfasına gidin <i class="fas fa-external-link-alt fa-xs"></i></a><br>
            2️⃣ Yukarıdaki malzemeler için sözleşme oluşturun<br>
            3️⃣ Sözleşme onaylandıktan sonra sipariş verebilirsiniz
            </small>
        </div>';
        
        if ($stok_yeterli) {
            return [
                'class' => 'badge-aksiyon-uyari',
                'icon' => 'fas fa-file-contract',
                'mesaj' => 'Gelecek Siparişler İçin Sözleşme Tamamlayın',
                'detay' => $detay . $adimlar,
                'category' => 'warning',
                'buton' => [
                    'text' => 'Sözleşmeler',
                    'url' => 'cerceve_sozlesmeler.php',
                    'icon' => 'fas fa-file-contract'
                ]
            ];
        } else {
            return [
                'class' => 'badge-aksiyon-kritik',
                'icon' => 'fas fa-ban',
                'mesaj' => 'Sözleşme Eksik - Önce Sözleşme Tamamlayın',
                'detay' => $detay . $adimlar,
                'category' => 'critical',
                'buton' => [
                    'text' => 'Sözleşmeler',
                    'url' => 'cerceve_sozlesmeler.php',
                    'icon' => 'fas fa-file-contract'
                ]
            ];
        }
    }
    
    // 4. SİPARİŞ VERİLMESİ GEREKEN MALZEME VARSA
    if ($acik > 0) {
        $siparis_gereken_var = false;
        $siparis_listesi = [];
        
        foreach ($bilesen_detaylari as $bilesen) {
            if ($bilesen['tur'] !== 'esans') {
                $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                $toplam_ihtiyac = $acik * $birim_ihtiyac;
                $mevcut_ve_yoldaki = $bilesen['mevcut_stok'] + $bilesen['yoldaki_stok'];
                $siparis_gereken = max(0, $toplam_ihtiyac - $mevcut_ve_yoldaki);
                
                if ($siparis_gereken > 0 && !empty($bilesen['sozlesme_var'])) {
                    $siparis_gereken_var = true;
                    $siparis_listesi[] = '<i class="fas fa-box text-primary"></i> ' . $bilesen['isim'] . ': <strong>' . number_format($siparis_gereken, 0, ',', '.') . '</strong> adet';
                }
            }
        }
        
        if ($siparis_gereken_var) {
            $adimlar = '<div style="background: #e7f3ff; padding: 8px; border-radius: 4px; margin-top: 6px; border-left: 3px solid #0078d4;">
                <strong><i class="fas fa-info-circle text-info"></i> Yapılması Gerekenler:</strong><br>
                <small>
                1️⃣ <a href="satinalma_siparisler.php" target="_blank" class="text-primary">Satınalma sipariş sayfasına gidin <i class="fas fa-external-link-alt fa-xs"></i></a><br>
                2️⃣ Yukarıdaki malzemeler için sipariş oluşturun<br>
                3️⃣ Tedarikçiye sipariş gönderin
                </small>
            </div>';
            
            return [
                'class' => 'badge-aksiyon-bilgi',
                'icon' => 'fas fa-shopping-cart',
                'mesaj' => 'Malzeme Siparişi Verin',
                'detay' => implode('<br>', $siparis_listesi) . $adimlar,
                'category' => 'order',
                'buton' => [
                    'text' => 'Sipariş Ver',
                    'url' => 'satinalma_siparisler.php',
                    'icon' => 'fas fa-cart-plus'
                ]
            ];
        }
        
        // 5. ESANS HAMMADDESİ EKSİKSE
        $esans_hammadde_listesi = [];
        if (isset($p['esans_uretim_bilgisi']) && is_array($p['esans_uretim_bilgisi'])) {
            foreach ($p['esans_uretim_bilgisi'] as $esans_kodu => $esans_info) {
                // Sadece net ihtiyacı olan esanslar için hammadde hesapla
                if (isset($net_esans_ihtiyaclari[$esans_kodu])) {
                    $ihtiyac_esans_miktari = $net_esans_ihtiyaclari[$esans_kodu];
                    
                    if (isset($esans_info['formul_detaylari']) && is_array($esans_info['formul_detaylari'])) {
                        foreach ($esans_info['formul_detaylari'] as $hammadde) {
                            $gerekli_hammadde = $ihtiyac_esans_miktari * $hammadde['recete_miktari'];
                            // Hammadde stoğunu ve yoldaki siparişi düş
                            $net_siparis = max(0, $gerekli_hammadde - $hammadde['mevcut_stok'] - $hammadde['bekleyen_siparis']);
                            
                            if ($net_siparis > 0) {
                                $malzeme_adi_goster = isset($hammadde['malzeme_ismi']) ? $hammadde['malzeme_ismi'] : (isset($hammadde['malzeme_adi']) ? $hammadde['malzeme_adi'] : '-');
                                $esans_hammadde_listesi[] = '<i class="fas fa-vial text-purple"></i> ' . $malzeme_adi_goster . ': <strong>' . number_format($net_siparis, 2, ',', '.') . '</strong> ' . $hammadde['birim'];
                            }
                        }
                    }
                }
            }
        }
        
        if (count($esans_hammadde_listesi) > 0) {
            return [
                'class' => 'badge-aksiyon-uyari',
                'icon' => 'fas fa-flask',
                'mesaj' => 'Esans hammaddesi siparişi verin',
                'detay' => implode('<br>', $esans_hammadde_listesi),
                'category' => 'order',
                'buton' => [
                    'text' => 'Sipariş Ver',
                    'url' => 'satinalma_siparisler.php',
                    'icon' => 'fas fa-cart-plus'
                ]
            ];
        }
        
        // 6. ESANS ÜRETİMİ GEREKLİYSE
        $esans_uretim_listesi = [];
        if (isset($p['esans_uretim_bilgisi']) && is_array($p['esans_uretim_bilgisi'])) {
            foreach ($bilesen_detaylari as $bilesen) {
                if ($bilesen['tur'] === 'esans' && isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                    $esans_info = $p['esans_uretim_bilgisi'][$bilesen['kodu']];
                    $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                    $brut_ihtiyac = $acik * $birim_ihtiyac;
                    $stok = floatval($bilesen['mevcut_stok']);
                    $uretimde = floatval($esans_info['acik_is_emri_miktar']);
                    $net_ihtiyac = max(0, $brut_ihtiyac - ($stok + $uretimde));
                    $uretilebilir_esans = floatval($esans_info['uretilebilir_miktar']);
                    
                    if ($net_ihtiyac > 0 && $uretilebilir_esans > 0) {
                        $esans_uretim_listesi[] = $esans_info['esans_ismi'] . ': <strong>' . number_format($net_ihtiyac, 2, ',', '.') . ' ml</strong>';
                    }
                }
            }
        }
        
        if (count($esans_uretim_listesi) > 0) {
            return [
                'class' => 'badge-aksiyon-bilgi',
                'icon' => 'fas fa-flask',
                'mesaj' => 'Esans iş emri oluşturun',
                'detay' => implode('<br>', $esans_uretim_listesi),
                'category' => 'production', 
                'buton' => [
                    'text' => 'İş Emri Aç',
                    'url' => 'esans_is_emirleri.php',
                    'icon' => 'fas fa-plus-circle'
                ]
            ];
        }
        
        // 6.5 SİPARİŞLER YOLDAYSA
        $yolda_detay = [];
        foreach ($bilesen_detaylari as $b) {
            if ($b['yoldaki_stok'] > 0) {
                $yolda_detay[] = '<i class="fas fa-truck text-info"></i> ' . $b['isim'] . ': <strong>' . number_format($b['yoldaki_stok'], 0, ',', '.') . '</strong> adet yolda (' . $b['po_list'] . ')';
            }
        }
        if (isset($p['esans_uretim_bilgisi'])) {
            foreach ($p['esans_uretim_bilgisi'] as $ei) {
                if (isset($ei['formul_detaylari'])) {
                    foreach ($ei['formul_detaylari'] as $h) {
                        if ($h['bekleyen_siparis'] > 0) {
                            $yolda_detay[] = '<i class="fas fa-flask text-purple"></i> ' . ($h['malzeme_ismi'] ?? 'Hammadde') . ': <strong>' . number_format($h['bekleyen_siparis'], 2, ',', '.') . ' ' . $h['birim'] . '</strong> yolda (' . $h['po_list'] . ')';
                        }
                    }
                }
            }
        }

        if (count($yolda_detay) > 0) {
            return [
                'class' => 'badge-aksiyon-bilgi',
                'icon' => 'fas fa-shipping-fast',
                'mesaj' => 'Siparişler Yolda - Teslimat Bekleniyor',
                'detay' => implode('<br>', array_unique($yolda_detay)),
                'category' => 'info',
                'buton' => [
                    'text' => 'Sipariş Takibi',
                    'url' => 'satinalma_siparisler.php',
                    'icon' => 'fas fa-truck'
                ]
            ];
        }
        
        // 7. TÜM MALZEMELER HAZIR, MONTAJ ÜRETİMİ BAŞLATIN
        if ($uretilebilir >= $acik) {
            return [
                'class' => 'badge-aksiyon-bilgi',
                'icon' => 'fas fa-industry',
                'mesaj' => 'Montaj iş emri oluşturun',
                'detay' => '<strong>Üretilecek miktar:</strong> ' . number_format($p['onerilen_uretim'], 0, ',', '.') . ' adet',
                'category' => 'production',
                'buton' => [
                    'text' => 'İş Emri Aç',
                    'url' => 'montaj_is_emirleri.php',
                    'icon' => 'fas fa-plus-circle'
                ]
            ];
        }
    }
    
    // 8. AÇIK YOKSA VE HER ŞEY TAMAM
    if ($acik == 0 && count($sozlesme_eksik) == 0) {
        return [
            'class' => 'badge-aksiyon-basarili',
            'icon' => 'fas fa-check-circle',
            'mesaj' => 'Her şey yolunda',
            'detay' => 'Stok: <strong>' . number_format($p['stok_miktari'], 0, ',', '.') . '</strong> | Kritik: <strong>' . number_format($p['kritik_stok_seviyesi'], 0, ',', '.') . '</strong>',
            'category' => 'ok',
            'buton' => null
        ];
    }
    
    // 9. AÇIK YOKSA AMA SÖZLEŞME EKSİK
    if ($acik == 0 && count($sozlesme_eksik) > 0) {
        $detay = '<strong>Sözleşmesi olmayan:</strong><br>' . implode(', ', array_slice($sozlesme_eksik, 0, 5));
        if (count($sozlesme_eksik) > 5) {
            $detay .= '<br>+' . (count($sozlesme_eksik) - 5) . ' malzeme daha';
        }
        return [
            'class' => 'badge-aksiyon-uyari',
            'icon' => 'fas fa-file-contract',
            'mesaj' => 'Gelecek üretimler için sözleşme tamamlayın',
            'detay' => $detay,
            'category' => 'warning',
            'buton' => [
                'text' => 'Sözleşmeler',
                'url' => 'cerceve_sozlesmeler.php',
                'icon' => 'fas fa-file-contract'
            ]
        ];
    }
    
    // Varsayılan (hiçbir koşul sağlanmazsa)
    return [
        'class' => 'badge-aksiyon-bilgi',
        'icon' => 'fas fa-info-circle',
        'mesaj' => 'Durum değerlendiriliyor...',
        'detay' => '-',
        'category' => 'ok',
        'buton' => null
    ];
}

$supply_chain_data = getSupplyChainData($connection);

// Calculate statistics for the compact view
$kotu_count = 0; $iyi_count = 0; $belirsiz_count = 0;
foreach ($supply_chain_data['uretilebilir_urunler'] as $p) {
    if ($p['kritik_stok_seviyesi'] <= 0 && $p['siparis_miktari'] <= 0) $belirsiz_count++;
    elseif ($p['acik'] > 0) $kotu_count++;
    else $iyi_count++;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tedarik Zinciri Kontrol Paneli - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SheetJS (Excel Export) - More stable CDN -->
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <style>
        /* PROFESYONEL ERP SİSTEMİ - ULTRA TEMİZ TASARIM */
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        :root {
            --primary: #0078d4;
            --success: #107c10;
            --danger: #d13438;
            --warning: #ff8c00;
            --gray-50: #fafafa;
            --gray-100: #f3f3f3;
            --gray-200: #e1e1e1;
            --gray-300: #c8c8c8;
            --gray-400: #a19f9d;
            --gray-500: #605e5c;
            --gray-600: #484644;
            --gray-700: #323130;
            --gray-800: #201f1e;
            --white: #ffffff;
            --border: #edebe9;
        }
        
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', 'Helvetica Neue', sans-serif;
            font-size: 13px;
            background: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .main-content { 
            padding: 20px 24px;
            background: var(--white);
            min-height: 100vh;
        }
        
        /* Stats */
        .stats-inline {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .stat-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 13px;
            font-weight: 400;
        }
        
        .stat-chip i {
            font-size: 16px;
            opacity: 0.7;
        }
        
        .stat-chip .num {
            font-size: 20px;
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .stat-chip.danger { 
            border-left: 3px solid var(--danger);
            background: #fef6f6;
        }
        .stat-chip.danger .num { color: var(--danger); }
        .stat-chip.danger i { color: var(--danger); }
        
        .stat-chip.success { 
            border-left: 3px solid var(--success);
            background: #f3faf3;
        }
        .stat-chip.success .num { color: var(--success); }
        .stat-chip.success i { color: var(--success); }
        
        .stat-chip.muted { 
            border-left: 3px solid var(--gray-300);
            background: var(--gray-50);
        }
        .stat-chip.muted .num { color: var(--gray-500); }
        .stat-chip.muted i { color: var(--gray-500); }
        
        /* Card */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        
        .card-header {
            background: var(--white);
            color: var(--gray-800);
            padding: 14px 20px;
            font-size: 15px;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header i { 
            margin-right: 8px;
            color: var(--primary);
            font-size: 16px;
        }
        
        .card-header span:last-child {
            font-size: 12px;
            font-weight: 400;
            color: var(--gray-500);
        }
        
        /* Table */
        .table-responsive { 
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }
        
        .table th {
            background: var(--gray-50);
            color: var(--gray-600);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 10;
            vertical-align: top;
        }
        
        .table th .header-desc {
            font-size: 9px; 
            font-weight: 400; 
            opacity: 0.7; 
            margin-top: 4px;
            white-space: normal;
            line-height: 1.2;
            text-transform: none;
        }
        
        .table th.text-center { text-align: center; }
        .table th.text-right { text-align: right; }
        
        /* Sticky columns */
        .sticky-col {
            position: sticky;
            background: var(--gray-50);
            z-index: 11;
        }
        .sticky-col-1 {
            left: 0;
        }
        .sticky-col-2 {
            left: 40px;
        }
        /* Header'daki sticky kolonlar hem yukarı hem sola sabit */
        .table thead th.sticky-col {
            z-index: 12;
        }
        .table tbody .sticky-col {
            background: #fff;
        }
        .table tbody tr:hover .sticky-col {
            background: var(--gray-50);
        }
        
        .table td {
            padding: 12px 12px;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: middle;
            color: var(--gray-700);
        }
        
        .table tbody tr {
            transition: background-color 0.15s ease;
        }
        
        .table tbody tr:hover {
            background: var(--gray-50);
        }
        
        .table tbody tr.row-acil {
            background: #fef6f6;
            border-left: 3px solid var(--danger);
        }
        
        .table tbody tr.row-acil:hover {
            background: #fef0f0;
        }
        
        /* Badges */
        .badge-sm {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .badge-kotu { 
            background: var(--white);
            color: var(--danger);
            border: 1px solid #f1aeb5;
        }
        .badge-iyi { 
            background: var(--white);
            color: var(--success);
            border: 1px solid #badbcc;
        }
        .badge-belirsiz { 
            background: var(--white);
            color: var(--gray-500);
            border: 1px solid var(--border);
        }
        
        .badge-aksiyon-kritik {
            background: var(--white);
            color: var(--danger);
            border: 1px solid #f1aeb5;
            padding: 5px 12px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 11px;
        }
        
        .badge-aksiyon-uyari {
            background: var(--white);
            color: var(--warning);
            border: 1px solid #ffe5b4;
            padding: 5px 12px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 11px;
        }
        
        .badge-aksiyon-bilgi {
            background: var(--white);
            color: var(--primary);
            border: 1px solid #b3d7ff;
            padding: 5px 12px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 11px;
        }
        
        .badge-aksiyon-basarili {
            background: var(--white);
            color: var(--success);
            border: 1px solid #badbcc;
            padding: 5px 12px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 11px;
        }
        
        /* Buttons */
        .btn-xs {
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 3px;
            transition: all 0.15s ease;
        }
        
        .btn-dark {
            background: var(--gray-800);
            border: 1px solid var(--gray-700);
            color: var(--white);
        }
        
        .btn-dark:hover {
            background: var(--gray-700);
            border-color: var(--gray-600);
        }
        
        /* Special Columns */
        .th-green {
            background: var(--white) !important;
            color: var(--success) !important;
            font-weight: 700 !important;
            border-left: 3px solid var(--success) !important;
            min-width: 140px !important;
        }
        
        .th-purple {
            background: var(--white) !important;
            color: #6f42c1 !important;
            font-weight: 700 !important;
            border-left: 3px solid #6f42c1 !important;
            min-width: 140px !important;
        }

        /* Filter Chips - Larger */
        .filter-chip {
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid #e0e0e0;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
            color: #666;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            user-select: none;
            height: 28px;
        }
        .filter-chip:hover {
            background-color: #f8f9fa;
            border-color: #d0d0d0;
        }
        .filter-chip.active {
            background: #eef2ff;
            border-color: #7c2a99;
            color: #4a0e63;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(74, 14, 99, 0.1);
        }
        .filter-chip input { display: none; }
        
        /* Input Focus Clean */
        .form-control:focus {
            box-shadow: none !important;
            background-color: #fff !important;
            border-color: #7c2a99 !important;
        }/* Utilities */
        .text-danger { color: var(--danger) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-success { color(--success) !important; }
        .text-muted { color: var(--gray-500) !important; }
        .text-info { color: var(--primary) !important; }
        .font-semibold { font-weight: 600; }
        
        /* Icons */
        .fa, .fas, .far, .fal, .fab {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* ============================================
           AKSİYON KOLONLARI - DETAYLI TASARIM
        ============================================ */
        
        /* Aksiyon Önerisi Kolonu */
        .aksiyon-onerisi-cell {
            min-width: 280px !important;
            max-width: 320px;
        }
        
        .aksiyon-badge-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        
        .aksiyon-priority-indicator {
            width: 4px;
            height: 100%;
            border-radius: 2px;
            flex-shrink: 0;
        }
        
        .aksiyon-priority-indicator.kritik {
            background: var(--danger);
        }
        
        .aksiyon-priority-indicator.uyari {
            background: var(--warning);
        }
        
        .aksiyon-priority-indicator.bilgi {
            background: var(--primary);
        }
        
        .aksiyon-priority-indicator.basarili {
            background: var(--success);
        }
        
        .aksiyon-content {
            flex: 1;
        }
        
        .aksiyon-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-size: 12px;
            margin-bottom: 4px;
        }
        
        .aksiyon-title i {
            font-size: 14px;
        }
        
        /* Aksiyon Detay Kolonu */
        .aksiyon-detay-cell {
            min-width: 350px !important;
            max-width: 400px;
        }
        
        /* Aksiyon İstatistikleri */
        .aksiyon-stats {
            display: flex;
            gap: 8px;
            margin-top: 6px;
            flex-wrap: wrap;
        }
        
        .aksiyon-stat-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 3px;
            font-size: 10px;
            font-weight: 500;
        }
        
        .aksiyon-stat-item i {
            font-size: 9px;
            opacity: 0.7;
        }
        
        .aksiyon-stat-item .stat-value {
            font-weight: 600;
            color: var(--gray-800);
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar { 
            width: 12px; 
            height: 12px; 
        }
        
        ::-webkit-scrollbar-track { 
            background: var(--gray-100);
            border-radius: 0;
        }
        
        ::-webkit-scrollbar-thumb { 
            background: var(--gray-300);
            border-radius: 0;
            border: 2px solid var(--gray-100);
        }
        
        ::-webkit-scrollbar-thumb:hover { 
            background: var(--gray-400);
        }
        
        /* Dropdown checkbox düzeni */
        .dropdown-menu .form-check {
            padding-left: 1.5rem;
            margin-bottom: 0.25rem;
        }
        .dropdown-menu .form-check-input {
            margin-left: -1.25rem;
            margin-top: 0.2rem;
        }
        .dropdown-menu .form-check-label {
            font-size: 12px;
            cursor: pointer;
        }
        
        /* Font Definitions via JS Injection below to ensure loading */
        
        /* Typography Upgrade - Poppins & Open Sans */
        body { font-family: 'Open Sans', sans-serif !important; color: #334155; }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .modal-title, .card-title, .nav-link { font-family: 'Poppins', sans-serif !important; letter-spacing: 0; }
        .font-mono { font-family: 'Open Sans', sans-serif !important; letter-spacing: -0.5px; font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
        .table { font-size: 13px !important; }
        .btn { font-family: 'Poppins', sans-serif !important; font-weight: 500; }
        .modal-content { border-radius: 12px !important; }

        .table th .dropdown {
            position: relative;
        }
        .table th .dropdown .btn {
            padding: 2px 4px;
            line-height: 1;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="navbar-text mr-3"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <!-- Production Planning Section - TEK TABLO -->
        <div class="card mb-3">
            <!-- GELİŞMİŞ TOOLBAR -->
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center flex-wrap" style="min-height: 80px; gap: 20px;">
                
                <!-- GRUP 1: Bileşen Filtreleri (Chips) -->
                <div class="d-flex flex-column">
                    <div class="d-flex flex-wrap" id="bilesenFilters" style="gap: 5px;">
                        <label class="filter-chip active mb-0" onclick="toggleChip(this)">
                            <input class="bilesen-checkbox" type="checkbox" value="kutu" checked> Kutu
                        </label>
                        <label class="filter-chip active mb-0" onclick="toggleChip(this)">
                            <input class="bilesen-checkbox" type="checkbox" value="takm" checked> Takım
                        </label>
                        <label class="filter-chip active mb-0" onclick="toggleChip(this)">
                            <input class="bilesen-checkbox" type="checkbox" value="esans" checked> Esans
                        </label>
                        <label class="filter-chip mb-0" onclick="toggleChip(this)">
                            <input class="bilesen-checkbox" type="checkbox" value="etiket"> Etiket
                        </label>
                        <label class="filter-chip mb-0" onclick="toggleChip(this)">
                            <input class="bilesen-checkbox" type="checkbox" value="paket"> Paket
                        </label>
                        <label class="filter-chip mb-0" onclick="toggleChip(this)">
                            <input class="bilesen-checkbox" type="checkbox" value="jelatin"> Jelatin
                        </label>
                    </div>
                    <div class="d-flex flex-column mt-1">
                        <div class="text-upper font-weight-bold text-dark" style="font-size: 10px; letter-spacing: 0.5px;">Analiz Kapsamı</div>
                        <div class="text-muted" style="font-size: 9px; opacity: 0.8;">Üretilebilir miktar hesabına dahil edilecek bileşenleri seçiniz</div>
                    </div>
                </div>

                <div class="vr mx-2 bg-light d-none d-lg-block" style="width: 1px; height: 35px; border-left: 1px solid #eee;"></div>

                <!-- GRUP 2: Arama -->
                <div class="d-flex flex-column">
                    <div class="position-relative">
                        <i class="fas fa-search text-muted position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 13px;"></i>
                        <input type="text" class="form-control pl-5 bg-light border-0" id="urunAramaInput" placeholder="Ürün Ara..." style="font-size: 13px; width: 200px; height: 36px; border-radius: 8px; transition: all 0.2s;">
                    </div>
                    <div class="text-muted mt-1 ml-1" style="font-size: 9px; opacity: 0.7;">Ürün Adı veya Kodu</div>
                </div>

                <!-- GRUP 3: Filtreler -->
                <div class="d-flex flex-column">
                    <div class="btn-group">
                        <!-- Durum Filtresi -->
                        <div class="dropdown">
                            <button class="btn btn-white border text-secondary dropdown-toggle px-3" type="button" id="dropdownActionFilter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 11px; height: 32px; border-radius: 6px 0 0 6px; font-weight: 500;">
                                <i class="fas fa-tasks mr-2 text-primary"></i> Durum
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 mt-1" aria-labelledby="dropdownActionFilter">
                                <a class="dropdown-item" href="#" onclick="setActionFilter('all', this)"><i class="fas fa-list mr-2 text-muted"></i>Tümü</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="setActionFilter('critical', this)"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Kritik Sorunlar</a>
                                <a class="dropdown-item" href="#" onclick="setActionFilter('warning', this)"><i class="fas fa-file-contract text-warning mr-2"></i>Sözleşme Uyarısı</a>
                                <a class="dropdown-item" href="#" onclick="setActionFilter('order', this)"><i class="fas fa-shopping-cart text-info mr-2"></i>Sipariş Gerekiyor</a>
                                <a class="dropdown-item" href="#" onclick="setActionFilter('production', this)"><i class="fas fa-tools text-primary mr-2"></i>Üretim/Montaj</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="setActionFilter('ok', this)"><i class="fas fa-check-circle text-success mr-2"></i>Sorun Yok</a>
                            </div>
                        </div>
                        <!-- Fark Filtresi -->
                        <button id="btnFarkFiltre" class="btn btn-white border text-secondary px-3" type="button" onclick="toggleFarkFiltre(this)" style="font-size: 11px; height: 32px; border-radius: 0 6px 6px 0; font-weight: 500; border-left: 0;" title="Sadece Yetersiz Stoklu Olanları Göster">
                            <i class="fas fa-filter mr-2 text-warning"></i> Yetersiz Stoklu Ürünler
                        </button>
                    </div>
                    <div class="d-flex flex-column mt-1">
                        <div class="text-upper font-weight-bold text-dark" style="font-size: 10px; letter-spacing: 0.5px;">Fark ve Durum Filtreleri</div>
                        <div class="text-muted" style="font-size: 9px; opacity: 0.8;">Stok açığı olan ürünleri veya durumları listeleyin</div>
                    </div>
                </div>

                <!-- SİPARİŞ & EXPORT GRUBU (Sağa Dayalı) -->
                <div class="d-flex flex-column ml-auto">
                    <div class="d-flex" style="gap: 10px;">
                 <!-- SİPARİŞ BUTONU -->
                         <button onclick="renderSiparisListesi(); $('#siparisListesiModal').modal('show');" class="btn btn-primary d-flex align-items-center justify-content-center shadow-sm text-white" style="height: 32px; padding: 0 14px; border-radius: 6px; background: linear-gradient(135deg, #4a0e63, #7c2a99); border: none;">
                            <i class="fas fa-clipboard-list mr-2 text-white" style="color: #fff !important;"></i> 
                            <span class="text-white" style="font-size: 11px; font-weight: 600; letter-spacing: 0.3px; color: #fff !important;">Sipariş Gerekenler</span>
                         </button>
                         
                         <!-- EXCEL DROPDOWN -->
                         <div class="dropdown">
                            <button class="btn btn-success d-flex align-items-center justify-content-center shadow-sm dropdown-toggle text-white" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="height: 32px; padding: 0 14px; border-radius: 6px; background: linear-gradient(135deg, #28a745, #218838); border: none;">
                                <i class="fas fa-file-excel mr-2 text-white" style="color: #fff !important;"></i> 
                                <span class="text-white" style="font-size: 11px; font-weight: 600; letter-spacing: 0.3px; color: #fff !important;">Excel Export</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0 mt-2" style="min-width: 200px;">
                                <h6 class="dropdown-header text-uppercase text-muted font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Dışa Aktarma Seçenekleri</h6>
                                <a class="dropdown-item py-2 d-flex align-items-center" href="#" onclick="exportSiparisListesi()">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 32px; height: 32px;"><i class="fas fa-clipboard-list text-success"></i></div>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 600;">Sipariş Listesi</div>
                                        <div class="text-muted" style="font-size: 10px;">Birim fiyatlar ve tedarikçiler dahildir</div>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2 d-flex align-items-center" href="#" onclick="exportMainTable()">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 32px; height: 32px;"><i class="fas fa-table text-primary"></i></div>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 600;">Ana Tablo (Tümü)</div>
                                        <div class="text-muted" style="font-size: 10px;">Ekranda görünen mevcut liste</div>
                                    </div>
                                </a>
                            </div>
                         </div>
                    </div>
                    <div class="text-muted mt-1 text-right" style="font-size: 9px; opacity: 0.7; padding-right: 5px;">Raporlama ve Çıktı İşlemleri</div>
                </div>
            </div>
            <style>
                #urunTable thead th { position: sticky; top: 0; z-index: 50; background-color: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
            </style>
            <div class="table-responsive" style="height: calc(100vh - 180px); overflow-y: auto;">
                <table class="table table-hover mb-0" id="urunTable">
                    <thead>
                        <tr>
                            <th class="text-center sticky-col sticky-col-1">#</th>
                            <th class="sticky-col sticky-col-2">
                                <i class="fas fa-box"></i> Ürün
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Ürün adı ve kodu</div>
                            </th>
                            <th class="aksiyon-onerisi-cell">
                                <i class="fas fa-lightbulb"></i> Aksiyon Önerisi
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Öncelikli yapılması gereken</div>
                            </th>
                            <th class="aksiyon-detay-cell">
                                <i class="fas fa-info-circle"></i> Aksiyon Detayı
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Detaylı açıklama ve bilgiler</div>
                            </th>
                            <th class="text-right px-2">
                                <i class="fas fa-warehouse"></i> Stok
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Mevcut stok miktarı</div>
                            </th>
                            <th class="text-right px-2">
                                <i class="fas fa-shopping-cart"></i> Sipariş
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Müşteri siparişleri</div>
                            </th>
                            <th class="text-right px-2">
                                <i class="fas fa-cogs"></i> Üretimde
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Üretim aşamasında</div>
                            </th>
                            <th class="text-right px-2">
                                <i class="fas fa-calculator"></i> Toplam
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Stok + Üretim</div>
                            </th>
                            <th class="text-right px-2">
                                <i class="fas fa-exclamation-triangle"></i> Kritik
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Kritik seviye</div>
                            </th>
                            <th class="text-right px-2">
                                <i class="fas fa-folder-open"></i> Açık
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Net üretim ihtiyacı</div>
                            </th>
                            <th class="text-right px-2" style="cursor: pointer;" onclick="sortTable('fark')">
                                <i class="fas fa-percent"></i> Fark% <i class="fas fa-sort ml-1"></i>
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Karşılama oranı</div>
                            </th>
                            <th class="text-right">
                                <i class="fas fa-industry"></i> Üretilebilir
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Üretilebilir miktar</div>
                            </th>
                            <th class="text-right">
                                <i class="fas fa-clipboard-check"></i> Önerilen
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Önerilen üretim</div>
                            </th>
                            <th class="text-center">
                                <i class="fas fa-traffic-light"></i> Durum
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Genel durum</div>
                            </th>
                            <th class="th-green">
                                <i class="fas fa-database"></i> Veri Bilgisi
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Eksik veri kontrolü</div>
                            </th>
                            <th class="th-green">
                                <i class="fas fa-file-contract"></i> Sözleşme Durumu
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Çerçeve sözleşmeler</div>
                            </th>
                            <th class="text-right th-green">
                                <i class="fas fa-boxes"></i> Malzeme Stok
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Mevcut malzeme</div>
                            </th>
                            <th class="text-right th-green">
                                <i class="fas fa-truck"></i> Yoldaki Malzemeler
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Sipariş edilen</div>
                            </th>
                            <th class="text-right th-green">
                                <i class="fas fa-cart-plus"></i> Sipariş Verilmesi Gereken
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Eksik malzeme</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-flask"></i> Toplam Esans İhtiyacı
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Gerekli esans</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-vial"></i> Esans Stok
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Mevcut esans</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-blender"></i> Esans Üretimde
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Üretilen esans</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-tint"></i> Net Esans İhtiyacı
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Eksik esans</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-flask"></i> Hemen Üretilebilir Esans Miktarı
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Mevcut Hammaddeyle</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-industry"></i> Üretilmesi Gereken
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Hammadde Bekleyen</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-dolly"></i> Sipariş Gereken Hammaddeler
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Eksik hammadde</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-shipping-fast"></i> Yoldaki Hammaddeler
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Sipariş edilmiş</div>
                            </th>
                            <th class="text-right th-purple">
                                <i class="fas fa-clipboard-list"></i> Net Verilecek Esans Siparişi
                                <div style="font-size: 9px; font-weight: 400; opacity: 0.7; margin-top: 2px;">Verilecek esans hammadde siparişi</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // SİPARİŞ LİSTESİ VERİ TOPLAMA (GLOBAL)
                        $global_siparis_listesi = [];
                        $sira = 1; foreach ($supply_chain_data['uretilebilir_urunler'] as $p):
                            $row_class = '';
                            $badge = ['class' => 'badge-belirsiz', 'text' => '-'];
                            $yorum = '';
                            $siparis_miktari = isset($p['siparis_miktari']) ? $p['siparis_miktari'] : 0;
                            $stok = floatval($p['stok_miktari']);
                            $kritik = floatval($p['kritik_stok_seviyesi']);
                            $onerilen = floatval($p['onerilen_uretim']);
                            $uretilebilir = floatval($p['uretilebilir_miktar']);
                            $acik = floatval($p['acik']);
                            
                            // SİPARİŞ LİSTESİ VERİ TOPLAMA
                            if ($acik > 0) {
                                $bilesen_detaylari = isset($p['bilesen_detaylari']) ? $p['bilesen_detaylari'] : [];
                                foreach ($bilesen_detaylari as $bilesen) {
                                    // 1. NORMAL MALZEME HESABI
                                    if ($bilesen['tur'] !== 'esans') {
                                        $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                                        $toplam_ihtiyac = $acik * $birim_ihtiyac;
                                        $mevcut_ve_yoldaki = $bilesen['mevcut_stok'] + $bilesen['yoldaki_stok'];
                                        $net_siparis = max(0, $toplam_ihtiyac - $mevcut_ve_yoldaki);
                                        
                                        if ($net_siparis > 0) {
                                            // En ucuz tedarikçiyi bul
                                            $tedarikci_bilgi = ['adi' => '-', 'fiyat' => 0, 'para_birimi' => ''];
                                            
                                            $malzeme_kodu = '';
                                            if (!empty($bilesen['kodu'])) {
                                                $malzeme_kodu = $bilesen['kodu'];
                                            }
                                            
                                            // Malzeme kodu varsa sorgula
                                            if (!empty($malzeme_kodu)) {
                                                $kodu_safe = $connection->real_escape_string($malzeme_kodu);
                                                $sql_tedarik = "SELECT t.tedarikci_adi, cs.birim_fiyat, cs.para_birimi 
                                                                FROM cerceve_sozlesmeler cs 
                                                                JOIN tedarikciler t ON cs.tedarikci_id = t.tedarikci_id 
                                                                WHERE cs.malzeme_kodu = '$kodu_safe' 
                                                                AND cs.bitis_tarihi >= CURDATE()
                                                                ORDER BY cs.birim_fiyat ASC LIMIT 1";
                                                $t_res = $connection->query($sql_tedarik);
                                                if ($t_res && $t_row = $t_res->fetch_assoc()) {
                                                    $tedarikci_bilgi = [
                                                        'adi' => $t_row['tedarikci_adi'],
                                                        'fiyat' => $t_row['birim_fiyat'],
                                                        'para_birimi' => $t_row['para_birimi']
                                                    ];
                                                }
                                            }

                                            $global_siparis_listesi[] = [
                                                'urun_ismi' => $p['urun_ismi'],
                                                'urun_kodu' => $p['urun_kodu'],
                                                'tip' => ucfirst($bilesen['tur']),
                                                'malzeme_adi' => $bilesen['isim'],
                                                'birim_ihtiyac' => $birim_ihtiyac,
                                                'toplam_ihtiyac' => $toplam_ihtiyac,
                                                'stok' => $bilesen['mevcut_stok'],
                                                'yoldaki' => $bilesen['yoldaki_stok'],
                                                'net_siparis' => $net_siparis,
                                                'birim' => 'Adet',
                                                'acik_miktar' => $acik,
                                                'tedarikci' => $bilesen['tedarikci'] ?? $tedarikci_bilgi,
                                                'tum_tedarikciler' => $bilesen['tum_tedarikciler'] ?? []
                                            ];
                                        }
                                    } 
                                    // 2. ESANS HAMMADDE HESABI
                                    else {
                                        // Esans bulundu
                                        $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                                        $brut_ihtiyac = $acik * $birim_ihtiyac;
                                        $stok_esans = floatval($bilesen['mevcut_stok']);
                                        
                                        // Üretimdeki esans miktarı
                                        $uretimde_esans = 0;
                                        if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                                            $uretimde_esans = floatval($p['esans_uretim_bilgisi'][$bilesen['kodu']]['acik_is_emri_miktar']);
                                        }
                                        
                                        // Net Esans İhtiyacı = Brüt - (Stok + Üretimde)
                                        $net_esans_ihtiyac = max(0, $brut_ihtiyac - ($stok_esans + $uretimde_esans));
                                        
                                        // Eğer net esans ihtiyacı varsa, hammaddeleri hesapla
                                        if ($net_esans_ihtiyac > 0 && isset($p['esans_uretim_bilgisi'][$bilesen['kodu']]['formul_detaylari'])) {
                                            foreach ($p['esans_uretim_bilgisi'][$bilesen['kodu']]['formul_detaylari'] as $h) {
                                                $toplam_gereken_h = $net_esans_ihtiyac * $h['recete_miktari'];
                                                $eksik_h = max(0, $toplam_gereken_h - $h['mevcut_stok']);
                                                
                                                // Net Sipariş = (Gereken - Mevcut Stok) - Yoldaki Sipariş
                                                // Yoldaki sipariş (bekleyen_siparis) hammaddeye ait
                                                $net_h_siparis = max(0, $eksik_h - $h['bekleyen_siparis']);
                                                
                                                if ($net_h_siparis > 0) {
                                                    // En ucuz tedarikçiyi bul (HAMMADDE İÇİN)
                                                    $tedarikci_bilgi = ['adi' => '-', 'fiyat' => 0, 'para_birimi' => ''];
                                                    
                                                    $malzeme_kodu_h = isset($h['malzeme_kodu']) ? $h['malzeme_kodu'] : '';
                                                    $malzeme_adi_h = isset($h['malzeme_ismi']) ? $h['malzeme_ismi'] : (isset($h['isim']) ? $h['isim'] : 'Hammadde');
                                                    
                                                    if (empty($malzeme_kodu_h) && !empty($malzeme_adi_h) && $malzeme_adi_h !== 'Hammadde') {
                                                        // İsimden kod bul
                                                        $isim_safe = $connection->real_escape_string($malzeme_adi_h);
                                                        $m_res = $connection->query("SELECT malzeme_kodu FROM malzemeler WHERE malzeme_ismi = '$isim_safe' LIMIT 1");
                                                        if ($m_res && $m_row = $m_res->fetch_assoc()) {
                                                            $malzeme_kodu_h = $m_row['malzeme_kodu'];
                                                        }
                                                    }

                                                    if (!empty($malzeme_kodu_h)) {
                                                        $kodu_safe_h = $connection->real_escape_string($malzeme_kodu_h);
                                                        $sql_tedarik = "SELECT t.tedarikci_adi, cs.birim_fiyat, cs.para_birimi 
                                                                        FROM cerceve_sozlesmeler cs 
                                                                        JOIN tedarikciler t ON cs.tedarikci_id = t.tedarikci_id 
                                                                        WHERE cs.malzeme_kodu = '$kodu_safe_h' 
                                                                        AND cs.bitis_tarihi >= CURDATE()
                                                                        ORDER BY cs.birim_fiyat ASC LIMIT 1";
                                                        $t_res = $connection->query($sql_tedarik);
                                                        if ($t_res && $t_row = $t_res->fetch_assoc()) {
                                                            $tedarikci_bilgi = [
                                                                'adi' => $t_row['tedarikci_adi'],
                                                                'fiyat' => $t_row['birim_fiyat'],
                                                                'para_birimi' => $t_row['para_birimi']
                                                            ];
                                                        }
                                                    }

                                                    $global_siparis_listesi[] = [
                                                        'urun_ismi' => $p['urun_ismi'],
                                                        'urun_kodu' => $p['urun_kodu'],
                                                        'tip' => 'Esans Hammaddesi',
                                                        'malzeme_adi' => $malzeme_adi_h,
                                                        'birim_ihtiyac' => $h['recete_miktari'],
                                                        'toplam_ihtiyac' => $toplam_gereken_h,
                                                        'stok' => $h['mevcut_stok'],
                                                        'yoldaki' => $h['bekleyen_siparis'],
                                                        'net_siparis' => $net_h_siparis,
                                                        'birim' => $h['birim'],
                                                        'acik_miktar' => $acik,
                                                        'tedarikci' => $tedarikci_bilgi,
                                                        'tum_tedarikciler' => $h['tum_tedarikciler'] ?? []
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Üretim sonrası hesaplamalar
                            $uretim_sonrasi_stok = $stok + $onerilen;
                            $siparis_sonrasi_stok = $uretim_sonrasi_stok - $siparis_miktari;
                            
                            // Sipariş karşılanır mı?
                            $siparis_karsilanir = ($siparis_miktari <= 0) || ($uretim_sonrasi_stok >= $siparis_miktari);
                            // Kritik stok karşılanır mı?
                            $kritik_karsilanir = ($kritik <= 0) || ($siparis_sonrasi_stok >= $kritik);
                            
                            // Eğer önerilen üretim yapılırsa eksik kalan miktarlar
                            $siparis_eksik = max(0, $siparis_miktari - $uretim_sonrasi_stok);
                            $kritik_eksik = ($kritik > 0) ? max(0, $kritik - max(0, $siparis_sonrasi_stok)) : 0;
                            
                            // Durum ve yorum belirleme
                            if ($p['acik'] > 0) {
                                $row_class = 'row-acil';
                                $badge = ['class' => 'badge-kotu', 'text' => 'KÖTÜ'];
                            } else {
                                $badge = ['class' => 'badge-iyi', 'text' => 'İYİ'];
                            }
                            
                            // Yorum ve Detaylar (Durum Hücresi İçin Gerekli Veriler)
                            if ($kritik <= 0 && $siparis_miktari <= 0) {
                                $yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> Stok yeterli.</span>';
                            } elseif ($p['acik'] > 0) {
                                if ($uretilebilir > 0) {
                                    $detaylar = [];
                                    if ($siparis_miktari > 0) {
                                        if ($siparis_karsilanir) {
                                            $detaylar[] = '<span class="text-success">✓ Sipariş (' . number_format($siparis_miktari, 0, ',', '.') . ') karşılanır</span>';
                                        } else {
                                            $detaylar[] = '<span class="text-danger">✗ Sipariş (' . number_format($siparis_miktari, 0, ',', '.') . ') için ' . number_format($siparis_eksik, 0, ',', '.') . ' eksik</span>';
                                        }
                                    }
                                    if ($kritik > 0) {
                                        if ($kritik_karsilanir) {
                                            $detaylar[] = '<span class="text-success">✓ Kritik stok (' . number_format($kritik, 0, ',', '.') . ') karşılanır</span>';
                                        } else {
                                            $detaylar[] = '<span class="text-danger">✗ Kritik stok için ' . number_format($kritik_eksik, 0, ',', '.') . ' eksik kalır</span>';
                                        }
                                    }
                                    $yorum = '<div class="small">' . implode('<br>', $detaylar) . '</div>';
                                } else {
                                    $yorum = '<span class="text-danger"><i class="fas fa-times-circle"></i> Bileşen yetersiz! Malzeme siparişi verin.</span>';
                                }
                            } else {
                                $yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> Stok yeterli.</span>';
                            }
                            
                            // Üretimde varsa bilgi ekle
                            if ($p['uretimde_miktar'] > 0) {
                                $yorum .= ' <small class="text-info d-block mt-1"><i class="fas fa-cog fa-spin"></i> Üretimde: ' . number_format($p['uretimde_miktar'], 0, ',', '.') . '</small>';
                            }
                            
                            // Toplam Mevcut Hesabı (Eksik olduğu için NaN hatası veriyordu)
                            $toplam_mevcut = $stok + floatval($p['uretimde_miktar']);
                        ?>
                        <tr class="<?php echo $row_class; ?>" data-urun-tipi="<?php echo $p['urun_tipi']; ?>">
                            <td class="text-center text-muted sticky-col sticky-col-1"><?php echo $sira++; ?></td>
                            <td class="sticky-col sticky-col-2">
                                <span class="font-semibold"><?php echo htmlspecialchars($p['urun_ismi']); ?></span>
                                <small class="text-muted ml-1">#<?php echo $p['urun_kodu']; ?></small>
                            </td>
                            <td class="aksiyon-onerisi-hucre"
                                data-action-category="<?php echo isset($p['aksiyon_onerisi']['category']) ? $p['aksiyon_onerisi']['category'] : 'ok'; ?>"
                                data-acik="<?php echo $p['acik']; ?>"
                                data-eksik-bilesenler='<?php echo json_encode($p['eksik_bilesenler'] ?? []); ?>'
                                data-esans-agaci-eksik='<?php echo json_encode($p['esans_agaci_eksik'] ?? []); ?>'
                                data-sozlesme-eksik='<?php echo json_encode($p['sozlesme_eksik_malzemeler'] ?? []); ?>'>
                                <?php if (isset($p['aksiyon_onerisi'])): ?>
                                    <div class="badge badge-pill <?php echo $p['aksiyon_onerisi']['class']; ?> p-2 aksiyon-badge" 
                                         style="font-size: 11px; line-height: 1.4; white-space: normal; text-align: left; display: block;"
                                         data-original-class="<?php echo $p['aksiyon_onerisi']['class']; ?>"
                                         data-original-mesaj="<?php echo htmlspecialchars($p['aksiyon_onerisi']['mesaj']); ?>"
                                         data-action-category="<?php echo isset($p['aksiyon_onerisi']['category']) ? $p['aksiyon_onerisi']['category'] : 'ok'; ?>">
                                        <i class="<?php echo $p['aksiyon_onerisi']['icon']; ?> mr-1 aksiyon-icon"></i>
                                        <span class="aksiyon-mesaj"><?php echo $p['aksiyon_onerisi']['mesaj']; ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 11px; line-height: 1.5;">
                                <?php if (isset($p['aksiyon_onerisi']['detay'])): ?>
                                    <?php echo $p['aksiyon_onerisi']['detay']; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right px-2"><?php echo number_format($p['stok_miktari'], 0, ',', '.'); ?></td>
                            <td class="text-right px-2">
                                <?php if ($siparis_miktari > 0): ?>
                                    <span class="font-semibold" style="color: #8b5cf6;"><i class="fas fa-shopping-cart" style="font-size: 9px;"></i> <?php echo number_format($siparis_miktari, 0, ',', '.'); ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right px-2">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                     <?php if (isset($p['yoldaki_miktar']) && $p['yoldaki_miktar'] > 0): ?>
                                        <span class="text-info font-semibold" title="Yoldaki Sipariş"><i class="fas fa-truck text-xs mr-1"></i><?php echo number_format($p['yoldaki_miktar'], 0, ',', '.'); ?></span>
                                     <?php else: ?>-<?php endif; ?>
                                <?php else: ?>
                                     <?php if ($p['uretimde_miktar'] > 0): ?>
                                        <span class="text-info font-semibold"><?php echo number_format($p['uretimde_miktar'], 0, ',', '.'); ?></span>
                                     <?php else: ?>-<?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right font-semibold px-2"><?php echo number_format($p['toplam_mevcut'], 0, ',', '.'); ?></td>
                            <td class="text-right px-2"><?php echo number_format($p['kritik_stok_seviyesi'], 0, ',', '.'); ?></td>
                            <td class="text-right px-2 acik-kolonu"
                                data-bilesen-detaylari='<?php echo json_encode($p["bilesen_detaylari"] ?? []); ?>'
                                data-esans-uretim='<?php echo json_encode($p["esans_uretim_bilgisi"] ?? []); ?>'
                                data-orijinal-acik="<?php echo $p['acik']; ?>"
                                data-kritik-stok="<?php echo $p['kritik_stok_seviyesi']; ?>">
                                <?php if ($p['kritik_stok_seviyesi'] > 0 || $siparis_miktari > 0): ?>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <button type="button" class="btn btn-link btn-sm p-0 mr-1 acik-sifirla-btn" 
                                                style="font-size: 10px; color: #aaa; display: none;"
                                                title="Orijinal değere sıfırla">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <input type="number" 
                                               class="acik-input form-control form-control-sm text-right <?php echo $p['acik'] > 0 ? 'text-danger' : 'text-success'; ?>" 
                                               value="<?php echo max(0, $p['acik']); ?>" 
                                               min="0" 
                                               style="width: 70px; height: 26px; font-size: 12px; font-weight: 600; padding: 2px 6px; border: 1px solid #ddd; border-radius: 4px;"
                                               data-orijinal="<?php echo $p['acik']; ?>">
                                        <button type="button" class="btn btn-link btn-sm p-0 ml-1 acik-detay-btn" 
                                                style="font-size: 10px; color: #aaa;"
                                                data-urun-ismi="<?php echo htmlspecialchars($p['urun_ismi']); ?>"
                                                data-urun-kodu="<?php echo $p['urun_kodu']; ?>"
                                                data-siparis="<?php echo $siparis_miktari; ?>"
                                                data-kritik="<?php echo $p['kritik_stok_seviyesi']; ?>"
                                                data-stok="<?php echo $p['stok_miktari']; ?>"
                                                data-uretimde="<?php echo (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan') ? ($p['yoldaki_miktar'] ?? 0) : $p['uretimde_miktar']; ?>"
                                                data-acik="<?php echo $p['acik']; ?>">
                                            <i class="fas fa-question-circle"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <button type="button" class="btn btn-link btn-sm p-0 mr-1 acik-sifirla-btn" 
                                                style="font-size: 10px; color: #aaa; display: none;"
                                                title="Orijinal değere sıfırla">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <input type="number" 
                                               class="acik-input form-control form-control-sm text-right text-muted" 
                                               value="0" 
                                               min="0" 
                                               style="width: 70px; height: 26px; font-size: 12px; font-weight: 600; padding: 2px 6px; border: 1px solid #ddd; border-radius: 4px;"
                                               data-orijinal="0">
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-right px-2 fark-kolonu" data-sort-fark="<?php echo $p['yuzde_fark']; ?>" data-kritik-stok="<?php echo $p['kritik_stok_seviyesi']; ?>">
                                <?php if ($p['kritik_stok_seviyesi'] > 0): ?>
                                    <?php $gosterilecek_fark = max(0, $p['yuzde_fark']); ?>
                                    <span class="font-semibold fark-deger <?php echo $gosterilecek_fark > 50 ? 'text-danger' : ($gosterilecek_fark > 0 ? 'text-warning' : 'text-success'); ?>">
                                        %<?php echo number_format($gosterilecek_fark, 0); ?>
                                    </span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right uretilebilir-hucre" 
                                data-bilesen-uretilebilir='<?php echo json_encode($p['bilesen_uretilebilir']); ?>'
                                data-acik="<?php echo $p['acik']; ?>">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php elseif ($p['uretilebilir_miktar'] > 0): ?>
                                    <span class="text-success font-semibold uretilebilir-deger"><?php echo number_format($p['uretilebilir_miktar'], 0, ',', '.'); ?></span>
                                    <button class="btn btn-link btn-sm p-0 ml-1 uretilebilir-detay-btn" 
                                        style="font-size: 11px; color: var(--gray-500);"
                                        data-urun-ismi="<?php echo htmlspecialchars($p['urun_ismi']); ?>"
                                        data-urun-kodu="<?php echo $p['urun_kodu']; ?>"
                                        data-uretilebilir="<?php echo $p['uretilebilir_miktar']; ?>"
                                        data-bilesen='<?php echo json_encode($p['bilesen_detaylari'] ?? []); ?>'
                                        title="Detay göster">
                                        <i class="fas fa-question-circle"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted uretilebilir-deger">0</span>
                                    <button class="btn btn-link btn-sm p-0 ml-1 uretilebilir-detay-btn" 
                                        style="font-size: 11px; color: var(--gray-500);"
                                        data-urun-ismi="<?php echo htmlspecialchars($p['urun_ismi']); ?>"
                                        data-urun-kodu="<?php echo $p['urun_kodu']; ?>"
                                        data-uretilebilir="<?php echo $p['uretilebilir_miktar']; ?>"
                                        data-bilesen='<?php echo json_encode($p['bilesen_detaylari'] ?? []); ?>'
                                        title="Detay göster">
                                        <i class="fas fa-question-circle"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php elseif ($p['onerilen_uretim'] > 0): ?>
                                    <span class="font-semibold" style="color: var(--primary);"><?php echo number_format($p['onerilen_uretim'], 0, ',', '.'); ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-center"><span class="badge-sm <?php echo $badge['class']; ?>"><?php echo $badge['text']; ?></span></td>
                            <td>
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php 
                                    $eksik_bilesenler = isset($p['eksik_bilesenler']) ? $p['eksik_bilesenler'] : [];
                                    $esans_agaci_eksik = isset($p['esans_agaci_eksik']) ? $p['esans_agaci_eksik'] : [];
                                    
                                    if (empty($eksik_bilesenler) && empty($esans_agaci_eksik)): ?>
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Ürün ağacı tam</span>
                                    <?php else: ?>
                                        <?php if (!empty($eksik_bilesenler)): ?>
                                            <span class="text-warning" style="font-size: 10px;"><i class="fas fa-sitemap"></i> <?php echo implode(', ', $eksik_bilesenler); ?> ürün ağacında bağlı değil</span>
                                        <?php endif; ?>
                                        <?php if (!empty($esans_agaci_eksik)): ?>
                                            <br><span class="text-danger" style="font-size: 10px;"><i class="fas fa-flask"></i> Esansın formülü yok</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php 
                                    $sozlesme_eksik = isset($p['sozlesme_eksik_malzemeler']) ? $p['sozlesme_eksik_malzemeler'] : [];
                                    
                                    if (empty($sozlesme_eksik)): ?>
                                        <span class="text-success"><i class="fas fa-file-contract"></i> Tanımlı malzemelerin sözleşmesi tam</span>
                                    <?php else: ?>
                                        <span class="text-warning" style="font-size: 10px;"><i class="fas fa-exclamation-triangle"></i> Sözleşme yok: <?php echo implode(', ', array_slice($sozlesme_eksik, 0, 3)); ?><?php if (count($sozlesme_eksik) > 3) echo ' +' . (count($sozlesme_eksik) - 3) . ' diğer'; ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $malzeme_gosterildi = false;
                                    if (!empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] !== 'esans' && !empty($bilesen['sozlesme_var'])) {
                                                $malzeme_gosterildi = true;
                                                echo '<div class="text-nowrap" style="font-size: 11px; line-height: 1.2;">' . 
                                                     htmlspecialchars($bilesen['isim']) . ': <span class="font-weight-bold">' . 
                                                     number_format($bilesen['mevcut_stok'], 0, ',', '.') . '</span></div>';
                                            }
                                        }
                                    }
                                    if (!$malzeme_gosterildi) echo '<span class="text-muted">-</span>';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $malzeme_gosterildi = false;
                                    if (!empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] !== 'esans' && !empty($bilesen['sozlesme_var']) && $bilesen['yoldaki_stok'] > 0) {
                                                $malzeme_gosterildi = true;
                                                echo '<div class="text-nowrap" style="font-size: 11px; line-height: 1.2;">' . 
                                                     htmlspecialchars($bilesen['isim']) . ': <span class="text-info font-weight-bold">' . 
                                                     number_format($bilesen['yoldaki_stok'], 0, ',', '.') . '</span>' .
                                                     ' <span class="text-muted" style="font-size: 9px;">(' . htmlspecialchars($bilesen['po_list']) . ')</span></div>';
                                            }
                                        }
                                    }
                                    if (!$malzeme_gosterildi) echo '<span class="text-muted">0</span>';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $malzeme_gosterildi = false;
                                    // Ürün açığı (Gap) varsa hesapla
                                    if ($p['acik'] > 0 && !empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] !== 'esans' && !empty($bilesen['sozlesme_var'])) {
                                                $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                                                $toplam_ihtiyac = $p['acik'] * $birim_ihtiyac;
                                                $mevcut_ve_yoldaki = $bilesen['mevcut_stok'] + $bilesen['yoldaki_stok'];
                                                $siparis_gereken = max(0, $toplam_ihtiyac - $mevcut_ve_yoldaki);
                                                
                                                if ($siparis_gereken > 0) {
                                                    $malzeme_gosterildi = true;
                                                    $tedarikci_str = '';
                                                    
                                                    // Tüm tedarikçileri listele
                                                    if (!empty($bilesen['tum_tedarikciler'])) {
                                                        $tedarikci_str = '<br>';
                                                        $ilk_tedarikci = true;
                                                        foreach ($bilesen['tum_tedarikciler'] as $ted) {
                                                            $fiyat = number_format($ted['fiyat'], 2, ',', '.');
                                                            $pb = $ted['para_birimi'];
                                                            if ($ilk_tedarikci) {
                                                                // En uygun tedarikçi (yeşil, bold)
                                                                $tedarikci_str .= '<small class="d-block"><i class="fas fa-star text-warning"></i> <span class="text-dark font-weight-bold">' . 
                                                                                mb_substr($ted['adi'], 0, 18) . '</span> - <span class="text-success font-weight-bold">' . $fiyat . ' ' . $pb . '</span></small>';
                                                                $ilk_tedarikci = false;
                                                            } else {
                                                                // Diğer tedarikçiler (gri)
                                                                $tedarikci_str .= '<small class="d-block text-muted"><i class="fas fa-truck"></i> ' . 
                                                                                mb_substr($ted['adi'], 0, 18) . ' - ' . $fiyat . ' ' . $pb . '</small>';
                                                            }
                                                        }
                                                    }
                                                    
                                                    echo '<div style="font-size: 11px; line-height: 1.4; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px dashed #eee;">' . 
                                                         htmlspecialchars($bilesen['isim']) . ': <span class="text-danger font-weight-bold">' . 
                                                         number_format($siparis_gereken, 0, ',', '.') . '</span>' . $tedarikci_str . '</div>';
                                                }
                                            }
                                        }
                                    }
                                    if (!$malzeme_gosterildi) echo '<span class="text-muted">0</span>';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if ($p['acik'] > 0 && !empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                $birim = !empty($bilesen['birim']) ? $bilesen['birim'] : 'ml';
                                                $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                                                $toplam_ihtiyac = $p['acik'] * $birim_ihtiyac;
                                                echo '<div class="text-nowrap">' . number_format($toplam_ihtiyac, 2, ',', '.') . ' <small class="text-muted">' . $birim . '</small></div>';
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if (!empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                $birim = !empty($bilesen['birim']) ? $bilesen['birim'] : 'ml';
                                                echo '<div class="text-nowrap">' . number_format($bilesen['mevcut_stok'], 2, ',', '.') . ' <small class="text-muted">' . $birim . '</small></div>';
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if (!empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                $uretimde = 0;
                                                if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                                                    $uretimde = floatval($p['esans_uretim_bilgisi'][$bilesen['kodu']]['acik_is_emri_miktar']);
                                                }
                                                if ($uretimde > 0) {
                                                     $birim = !empty($bilesen['birim']) ? $bilesen['birim'] : 'ml';
                                                     echo '<div class="text-nowrap text-info">' . number_format($uretimde, 2, ',', '.') . ' <small class="text-muted">' . $birim . '</small></div>';
                                                } else {
                                                     echo '0';
                                                }
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if ($p['acik'] > 0 && !empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                                                $brut_ihtiyac = $p['acik'] * $birim_ihtiyac;
                                                
                                                $stok = floatval($bilesen['mevcut_stok']);
                                                $uretimde = 0;
                                                if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                                                    $uretimde = floatval($p['esans_uretim_bilgisi'][$bilesen['kodu']]['acik_is_emri_miktar']);
                                                }
                                                
                                                $toplam_mevcut_esans = $stok + $uretimde;
                                                $net_ihtiyac = max(0, $brut_ihtiyac - $toplam_mevcut_esans);
                                                
                                                if ($net_ihtiyac > 0) {
                                                    $birim = !empty($bilesen['birim']) ? $bilesen['birim'] : 'ml';
                                                    echo '<div class="text-nowrap"><span class="font-weight-bold" style="color: var(--danger);">' . number_format($net_ihtiyac, 2, ',', '.') . '</span> <small class="text-muted">' . $birim . '</small></div>';
                                                    echo '<div class="small text-muted" style="font-size: 10px;">' . htmlspecialchars($bilesen['isim']) . '</div>';
                                                } else {
                                                     echo '<span class="text-success"><i class="fas fa-check"></i> Yeterli</span>';
                                                     echo '<div class="small text-muted" style="font-size: 10px;">' . htmlspecialchars($bilesen['isim']) . '</div>';
                                                }
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if (!empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                $uretilebilir = 0;
                                                if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                                                    $uretilebilir = floatval($p['esans_uretim_bilgisi'][$bilesen['kodu']]['uretilebilir_miktar']);
                                                }
                                                
                                                if ($uretilebilir > 0) {
                                                    $birim = !empty($bilesen['birim']) ? $bilesen['birim'] : 'ml';
                                                    echo '<div class="text-nowrap text-success">' . number_format($uretilebilir, 2, ',', '.') . ' <small class="text-muted">' . $birim . '</small></div>';
                                                } else {
                                                    echo '<div class="text-nowrap text-danger">0</div>';
                                                }
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if ($p['acik'] > 0 && !empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                
                                                // 1. Net İhtiyaç Hesapla
                                                $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                                                $brut_ihtiyac = $p['acik'] * $birim_ihtiyac;
                                                $stok = floatval($bilesen['mevcut_stok']);
                                                $uretimde = 0;
                                                if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                                                    $uretimde = floatval($p['esans_uretim_bilgisi'][$bilesen['kodu']]['acik_is_emri_miktar']);
                                                }
                                                $toplam_mevcut_esans = $stok + $uretimde;
                                                $net_ihtiyac = max(0, $brut_ihtiyac - $toplam_mevcut_esans);
                                                
                                                // 2. Üretilebilir Hesapla
                                                $uretilebilir = 0;
                                                if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                                                    $uretilebilir = floatval($p['esans_uretim_bilgisi'][$bilesen['kodu']]['uretilebilir_miktar']);
                                                }
                                                
                                                // 3. Sipariş Gereken = Net İhtiyaç - Hammadde ile Üretilebilir
                                                $siparis_gereken = max(0, $net_ihtiyac - $uretilebilir);
                                                
                                                if ($siparis_gereken > 0) {
                                                    $birim = !empty($bilesen['birim']) ? $bilesen['birim'] : 'ml';
                                                    echo '<div class="text-nowrap text-danger font-weight-bold">' . number_format($siparis_gereken, 2, ',', '.') . ' <small>' . $birim . '</small></div>';
                                                    echo '<div class="small text-muted" style="font-size: 10px;">' . htmlspecialchars($bilesen['isim']) . '</div>';
                                                } else {
                                                    echo '0';
                                                }
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if ($p['acik'] > 0 && !empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                
                                                // 1. Net Esans İhtiyacını Al
                                                $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                                                $brut_ihtiyac = $p['acik'] * $birim_ihtiyac;
                                                $stok_esans = floatval($bilesen['mevcut_stok']);
                                                $uretimde_esans = 0;
                                                if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                                                    $uretimde_esans = floatval($p['esans_uretim_bilgisi'][$bilesen['kodu']]['acik_is_emri_miktar']);
                                                }
                                                $net_esans_ihtiyac = max(0, $brut_ihtiyac - ($stok_esans + $uretimde_esans));
                                                
                                                // 2. Eğer net ihtiyaç varsa hammaddeleri hesapla
                                                if ($net_esans_ihtiyac > 0 && isset($p['esans_uretim_bilgisi'][$bilesen['kodu']]['formul_detaylari'])) {
                                                    $eksik_hammaddeler = [];
                                                    foreach ($p['esans_uretim_bilgisi'][$bilesen['kodu']]['formul_detaylari'] as $h) {
                                                        $toplam_gereken_h = $net_esans_ihtiyac * $h['recete_miktari'];
                                                        $eksik_h = max(0, $toplam_gereken_h - $h['mevcut_stok']);
                                                        
                                                        if ($eksik_h > 0) {
                                                            $eksik_hammaddeler[] = '<div class="text-nowrap" style="font-size: 11px; line-height: 1.2;">' . 
                                                                htmlspecialchars($h['malzeme_ismi']) . ': <span class="text-danger font-weight-bold">' . 
                                                                number_format($eksik_h, 2, ',', '.') . '</span> <small>' . $h['birim'] . '</small></div>';
                                                        }
                                                    }
                                                    
                                                    if (!empty($eksik_hammaddeler)) {
                                                        echo implode('', $eksik_hammaddeler);
                                                    } else {
                                                        echo '0';
                                                    }
                                                } else {
                                                    echo '0';
                                                }
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if ($p['acik'] > 0 && !empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                
                                                if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']]['formul_detaylari'])) {
                                                    $mevcut_siparisler = [];
                                                    foreach ($p['esans_uretim_bilgisi'][$bilesen['kodu']]['formul_detaylari'] as $h) {
                                                        if ($h['bekleyen_siparis'] > 0) {
                                                            $mevcut_siparisler[] = '<div class="text-nowrap" style="font-size: 11px; line-height: 1.2;">' . 
                                                                htmlspecialchars($h['malzeme_ismi']) . ': <span class="text-info font-weight-bold">' . 
                                                                number_format($h['bekleyen_siparis'], 2, ',', '.') . '</span> <small>' . $h['birim'] . '</small>' .
                                                                ' <span class="text-muted" style="font-size: 9px;">(' . htmlspecialchars($h['po_list']) . ')</span></div>';
                                                        }
                                                    }
                                                    
                                                    if (!empty($mevcut_siparisler)) {
                                                        echo implode('', $mevcut_siparisler);
                                                    } else {
                                                        echo '0';
                                                    }
                                                } else {
                                                    echo '0';
                                                }
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if (isset($p['urun_tipi']) && $p['urun_tipi'] === 'hazir_alinan'): ?>
                                    <span class="text-muted small">-</span>
                                <?php else: ?>
                                    <?php
                                    $esans_bulundu = false;
                                    if ($p['acik'] > 0 && !empty($p['bilesen_detaylari'])) {
                                        foreach ($p['bilesen_detaylari'] as $bilesen) {
                                            if ($bilesen['tur'] === 'esans') {
                                                $esans_bulundu = true;
                                                
                                                // 1. Net Esans İhtiyacını Al
                                                $birim_ihtiyac = floatval($bilesen['gerekli_adet']);
                                                $brut_ihtiyac = $p['acik'] * $birim_ihtiyac;
                                                $stok_esans = floatval($bilesen['mevcut_stok']);
                                                $uretimde_esans = 0;
                                                if (isset($p['esans_uretim_bilgisi'][$bilesen['kodu']])) {
                                                    $uretimde_esans = floatval($p['esans_uretim_bilgisi'][$bilesen['kodu']]['acik_is_emri_miktar']);
                                                }
                                                $net_esans_ihtiyac = max(0, $brut_ihtiyac - ($stok_esans + $uretimde_esans));
                                                
                                                // 2. Net hammadde sipariş ihtiyacı hesapla
                                                if ($net_esans_ihtiyac > 0 && isset($p['esans_uretim_bilgisi'][$bilesen['kodu']]['formul_detaylari'])) {
                                                    $net_siparis_hammaddeler = [];
                                                    foreach ($p['esans_uretim_bilgisi'][$bilesen['kodu']]['formul_detaylari'] as $h) {
                                                        $toplam_gereken_h = $net_esans_ihtiyac * $h['recete_miktari'];
                                                        $eksik_h = max(0, $toplam_gereken_h - $h['mevcut_stok']);
                                                        
                                                        // Net Sipariş = (Gereken - Mevcut Stok) - Yoldaki Sipariş
                                                        $net_h_siparis = max(0, $eksik_h - $h['bekleyen_siparis']);
                                                        
                                                        if ($net_h_siparis > 0) {
                                                            $net_siparis_hammaddeler[] = '<div class="text-nowrap" style="font-size: 11px; line-height: 1.2;">' . 
                                                                htmlspecialchars($h['malzeme_ismi']) . ': <span class="text-danger font-weight-bold">' . 
                                                                number_format($net_h_siparis, 2, ',', '.') . '</span> <small>' . $h['birim'] . '</small></div>';
                                                        }
                                                    }
                                                    
                                                    if (!empty($net_siparis_hammaddeler)) {
                                                        echo implode('', $net_siparis_hammaddeler);
                                                    } else {
                                                        echo '0';
                                                    }
                                                } else {
                                                    echo '0';
                                                }
                                            }
                                        }
                                    }
                                    if (!$esans_bulundu) echo '0';
                                    ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($supply_chain_data['uretilebilir_urunler'])): ?>
                        <tr><td colspan="15" class="text-center py-4 text-muted">Henüz ürün kaydı bulunmuyor.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Tablo Rehberi & Hesaplama Mantığı -->
            <div class="card-footer bg-white border-top py-3">
                <h6 class="font-weight-bold text-dark mb-3" style="font-size: 13px;"><i class="fas fa-info-circle text-primary mr-2"></i> Detaylı Tablo Rehberi & Hesaplama Mantığı</h6>
                <div class="row" style="font-size: 11px;">
                    <!-- 1. GRUP: Temel Bilgiler & Stok -->
                    <div class="col-md-4">
                        <h6 class="text-dark font-weight-bold border-bottom pb-2 mb-2 bg-light pl-2 rounded">1. Temel Bilgiler & Stok</h6>
                        <ul class="list-unstyled mb-0 pl-1">
                            <li class="mb-2"><strong class="text-dark"># (Sıra):</strong> Listenin sıra numarası.</li>
                            <li class="mb-2"><strong class="text-dark">Ürün:</strong> Ürünün ticari ismi ve benzersiz stok kodu.</li>
                            <li class="mb-2"><strong class="text-dark">Aksiyon Önerisi:</strong> Sistemin hesaplamalar sonucu acil yapmanızı önerdiği işlem (Örn: "Üret", "Sipariş Ver").</li>
                            <li class="mb-2"><strong class="text-dark">Aksiyon Detayı:</strong> Önerinin sebebi ve detayı. (Örn: "Stok yetersiz, 100 adet eksik").</li>
                            <li class="mb-2"><strong class="text-dark">Stok:</strong> Depodaki fiziksel, kullanılabilir bitmiş ürün miktarı.</li>
                            <li class="mb-2"><strong class="text-dark">Sipariş:</strong> Müşteriden gelen, onaylanmış ama henüz sevk edilmemiş sipariş toplamı.</li>
                            <li class="mb-2"><strong class="text-dark">Üretimde:</strong> Montaj hattında olan veya iş emri açılmış miktar. <br><span class="text-muted small">Matematik: Montaj İş Emirleri (Başlatıldı + Üretimde)</span></li>
                            <li class="mb-2"><strong class="text-dark">Toplam:</strong> Gelecekte elimizde olacak toplam miktar. <br><span class="text-muted small">Matematik: Stok + Üretimde</span></li>
                            <li class="mb-2"><strong class="text-dark">Kritik:</strong> Güvenlik stoğu. Bu seviyenin altına düşülmemesi hedeflenir.</li>
                        </ul>
                    </div>

                    <!-- 2. GRUP: Analiz & Planlama -->
                    <div class="col-md-4">
                        <h6 class="text-dark font-weight-bold border-bottom pb-2 mb-2 bg-light pl-2 rounded">2. Analiz & Planlama</h6>
                        <ul class="list-unstyled mb-0 pl-1">
                            <li class="mb-2"><strong class="text-info">Açık (Net İhtiyaç):</strong> Üretilmesi şart olan miktar. <br><span class="text-muted small">Matematik: (Sipariş + Kritik) - (Stok + Üretimde)</span></li>
                            <li class="mb-2"><strong class="text-info">Fark%:</strong> Hedef stoğa ne kadar uzak olunduğunu gösterir. <br><span class="text-muted small">Matematik: ((Hedef - Mevcut) / Hedef) * 100</span></li>
                            <li class="mb-2"><strong class="text-info">Üretilebilir:</strong> Eldeki bileşenlerle (şişe, kapak vb.) yapılabilecek maks. ürün. <br><span class="text-muted small">Matematik: Min(Her Bileşen Stoğu / Reçete Miktarı)</span></li>
                            <li class="mb-2"><strong class="text-success">Önerilen:</strong> Açığı kapatmak için başlatılabilecek gerçekçi üretim. <br><span class="text-muted small">Matematik: Min(Açık, Üretilebilir)</span></li>
                            <li class="mb-2"><strong class="text-dark">Durum:</strong> Genel stok sağlığı (İyi, Kötü, Belirsiz).</li>
                            <li class="mb-2"><strong class="text-dark">Veri Bilgisi:</strong> Ürün ağacı veya reçete eksikliği kontrolü.</li>
                            <li class="mb-2"><strong class="text-dark">Sözleşme Durumu:</strong> Bileşenlerin tedarik sözleşmelerinin varlığı.</li>
                            <li class="mb-2"><strong class="text-dark">Malzeme Stok:</strong> Ürünün bileşenlerinin (kutu, etiket vb.) stokları.</li>
                            <li class="mb-2"><strong class="text-dark">Yoldaki Malzemeler:</strong> Tedarikçiye sipariş edilmiş, gelmesi beklenen malzemeler.</li>
                            <li class="mb-2"><strong class="text-danger">Sipariş Verilmesi Gereken:</strong> Eksik malzeme miktarı. <br><span class="text-muted small">Matematik: (Açık x Reçete) - (Malzeme Stok + Yoldaki)</span></li>
                        </ul>
                    </div>

                    <!-- 3. GRUP: Esans Detayları -->
                    <div class="col-md-4">
                        <h6 class="text-dark font-weight-bold border-bottom pb-2 mb-2 bg-light pl-2 rounded">3. Esans & Hammadde Detayları</h6>
                        <ul class="list-unstyled mb-0 pl-1">
                            <li class="mb-2"><strong class="text-purple">Toplam Esans İhtiyacı:</strong> Üretilmesi gereken (Açık) ürünlerin tamamı için gereken toplam esans miktarı. <br><span class="text-muted small">Matematik: Açık Ürün Adedi x Birim Esans Miktarı</span></li>
                            <li class="mb-2"><strong class="text-purple">Esans Stok:</strong> Depodaki hazır esans miktarı.</li>
                            <li class="mb-2"><strong class="text-purple">Esans Üretimde:</strong> İş emri açılmış, üretimdeki esans miktarı.</li>
                            <li class="mb-2"><strong class="text-purple">Net Esans İhtiyacı:</strong> Hâlâ gereken esans miktarı. <br><span class="text-muted small">Matematik: Toplam İhtiyaç - (Esans Stok + Esans Üretimde)</span></li>
                            <li class="mb-2"><strong class="text-purple">Hemen Üretilebilir Esans:</strong> Eldeki kimyasal esans hammaddeleriyle anında üretilebilecek esans. <br><span class="text-muted small">Matematik: Esans hammadde stoğuna göre hesaplanan kapasite.</span></li>
                            <li class="mb-2"><strong class="text-danger">Üretilmesi Gereken (Hammadde Bekleyen):</strong> Esans ihtiyacının, hammadde yetersizliği yüzünden üretilemeyen kısmı. <br><span class="text-muted small">Matematik: Net İhtiyaç - Hemen Üretilebilir</span></li>
                            <li class="mb-2"><strong class="text-purple">Sipariş Gereken Hammaddeler:</strong> Esans açığını kapatmak için alınması gereken esans hammaddeleri (kimyasallar). <br><span class="text-muted small">Matematik: (Eksik Esans x Formül) - Esans Hammadde Stok</span></li>
                            <li class="mb-2"><strong class="text-purple">Yoldaki Hammaddeler:</strong> Esans üretimi için verilmiş esans hammaddesi siparişleri.</li>
                            <li class="mb-2"><strong class="text-purple">Net Verilecek Esans Siparişi:</strong> Esans hammaddeleri sipariş özeti.</li>
                        </ul>
                    </div>
                </div>
            </div>
                </div>
            </div>
        
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Üretilebilir Detay Modalı -->
    <div class="modal fade" id="uretilebilirDetayModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #4a0e63, #7c2a99); color: white; padding: 10px 15px;">
                    <h6 class="modal-title"><i class="fas fa-industry"></i> Üretilebilir Miktar Detayı</h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="padding: 15px;">
                    <div class="mb-3">
                        <strong id="modalUrunIsmi" class="text-primary"></strong>
                        <small class="text-muted ml-2" id="modalUrunKodu"></small>
                    </div>
                    <div class="alert alert-info py-2 mb-3" style="font-size: 12px;">
                        <i class="fas fa-info-circle"></i>
                        Üretilebilir miktar, seçili bileşen türlerinin stoklarına göre <strong>en düşük</strong> değere sahip olanla sınırlıdır.
                    </div>
                    <div class="mb-3 p-2 rounded" style="background: var(--gray-50);">
                        <span class="font-weight-bold">Toplam Üretilebilir: </span>
                        <span id="modalUretilebilir" class="text-success font-weight-bold" style="font-size: 18px;"></span>
                        <span class="text-muted">adet</span>
                    </div>
                    <h6 class="mb-2"><i class="fas fa-cubes"></i> Bileşen Bazlı Analiz</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                            <thead style="background: var(--gray-100);">
                                <tr>
                                    <th>Bileşen Türü</th>
                                    <th>Bileşen Adı</th>
                                    <th class="text-right">Mevcut Stok</th>
                                    <th class="text-right">1 Ürün İçin Gerekli</th>
                                    <th class="text-right">Üretilebilir</th>
                                    <th class="text-center">Durum</th>
                                </tr>
                            </thead>
                            <tbody id="modalBilesenTablosu">
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 p-2 rounded" style="background: #fff3cd; font-size: 11px;">
                        <i class="fas fa-lightbulb text-warning"></i>
                        <strong>Not:</strong> En düşük üretilebilir değere sahip bileşen, üretimi sınırlayan "darboğaz" bileşendir.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- AÇIK MİKTAR DETAY MODALI -->
    <div class="modal fade" id="acikDetayModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-dark">
                        <i class="fas fa-calculator text-primary mr-1"></i>
                        Net İhtiyaç Hesabı
                    </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-2 pb-2">
                    <div class="mb-2">
                        <small class="text-muted d-block" id="modalAcikUrunIsmi" style="font-weight: 600;"></small>
                    </div>
                    
                    <div class="card bg-light border-0 p-2 mb-2">
                        <div class="d-flex justify-content-between mb-1" style="font-size: 11px;">
                            <span>Sipariş Miktarı:</span>
                            <span class="font-weight-bold" id="modalAcikSiparis"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-warning" style="font-size: 11px;">
                            <span>+ Kritik Stok Hedefi:</span>
                            <span class="font-weight-bold" id="modalAcikKritik"></span>
                        </div>
                        <div class="border-top my-1"></div>
                        <div class="d-flex justify-content-between font-weight-bold" style="font-size: 12px;">
                            <span>TOPLAM İHTİYAÇ:</span>
                            <span id="modalAcikToplamIhtiyac"></span>
                        </div>
                    </div>

                    <div class="text-center text-muted mb-2" style="font-size: 10px;">
                        <div class="d-flex align-items-center justify-content-center">
                            <span style="height: 1px; background: #ddd; width: 30px;"></span>
                            <span class="mx-2"><i class="fas fa-minus"></i></span>
                            <span style="height: 1px; background: #ddd; width: 30px;"></span>
                        </div>
                    </div>

                    <div class="card bg-light border-0 p-2 mb-2">
                        <div class="d-flex justify-content-between mb-1" style="font-size: 11px;">
                            <span>Mevcut Stok:</span>
                            <span class="font-weight-bold" id="modalAcikStok"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-info" style="font-size: 11px;">
                            <span>+ Üretimdeki:</span>
                            <span class="font-weight-bold" id="modalAcikUretimde"></span>
                        </div>
                        <div class="border-top my-1"></div>
                        <div class="d-flex justify-content-between font-weight-bold" style="font-size: 12px;">
                            <span>TOPLAM KAYNAK:</span>
                            <span id="modalAcikToplamKaynak"></span>
                        </div>
                    </div>

                    <div class="text-center text-muted mb-2" style="font-size: 10px;">
                        <i class="fas fa-equals"></i>
                    </div>

                    <div class="alert text-center p-2 mb-0" id="modalAcikAlert" style="font-size: 14px; font-weight: bold;">
                        <span id="modalAcikSonuc"></span>
                        <div style="font-size: 10px; font-weight: normal; margin-top: 2px;" id="modalAcikSonucEtiket"></div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-2">
                    <button type="button" class="btn btn-secondary btn-sm btn-block" data-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sipariş Listesi Modalı -->
    <div class="modal fade" id="siparisListesiModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow-sm" style="border-radius: 8px;">
                <div class="modal-header py-3 px-4 border-bottom bg-white d-flex justify-content-between align-items-center shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mr-3" style="width: 32px; height: 32px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <i class="fas fa-clipboard-list" style="font-size: 14px;"></i>
                        </div>
                        <div>
                            <h6 class="modal-title font-weight-bold text-dark mb-0" style="font-size: 15px; letter-spacing: -0.3px;">Sipariş Verilmesi Gerekenler</h6>
                            <div class="text-muted" style="font-size: 11px;">Tedarik ve üretim ihtiyaçları özeti</div>
                        </div>
                        
                        <!-- Görünüm Switcher -->
                        <div class="btn-group btn-group-sm ml-5 border p-1 rounded bg-light" role="group">
                            <button type="button" class="btn btn-white shadow-sm font-weight-bold px-3 border-0" id="btnViewList" onclick="switchSiparisView('list')" style="font-size: 11px; border-radius: 4px; transition: all 0.2s;">
                                <i class="fas fa-list mr-1"></i> Liste
                            </button>
                            <button type="button" class="btn btn-light text-muted px-3 border-0" id="btnViewSupplier" onclick="switchSiparisView('supplier')" style="font-size: 11px; border-radius: 4px; transition: all 0.2s;">
                                <i class="fas fa-truck mr-1"></i> Tedarikçi Gruplu
                            </button>
                        </div>

                        <button onclick="exportSiparisListesi()" class="btn btn-success ml-4 py-1 px-3 d-flex align-items-center shadow-sm" style="font-size: 11px; border-radius: 20px; font-weight: 600; height: 28px;">
                            <i class="fas fa-file-excel mr-2"></i> Excel'e Aktar
                        </button>
                    </div>
                    <button type="button" class="close text-secondary m-0 p-0" data-dismiss="modal" aria-label="Close" style="outline: none;">
                        <span aria-hidden="true" style="font-size: 24px;">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="max-height: 75vh; overflow-y: auto; background-color: #fff;">
                    <!-- LISTE GÖRÜNÜMÜ -->
                    <div id="viewListContainer">
                        <div class="table-responsive">
                            <style>
                                /* Sticky Header Fix */
                                #siparisListesiTable thead th {
                                    position: sticky;
                                    top: 0;
                                    z-index: 100;
                                    background-color: #f8f9fa;
                                    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                                    color: #555;
                                }
                            </style>
                            <table class="table table-hover mb-0" id="siparisListesiTable">
                                <thead>
                                    <tr style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.6px; font-family: 'Inter', sans-serif;">
                                        <th class="py-3 pl-4 border-bottom border-light font-weight-bold border-top-0">Ürün & Kod</th>
                                        <th class="py-3 border-bottom border-light font-weight-bold border-top-0">Kategori</th>
                                        <th class="py-3 border-bottom border-light font-weight-bold border-top-0">İhtiyaç Duyulan Malzeme</th>
                                        <th class="py-3 text-right border-bottom border-light font-weight-bold border-top-0">Stok / Yoldaki</th>
                                        <th class="py-3 pl-3 border-bottom border-light font-weight-bold border-top-0">Tedarikçi (En Uygun)</th>
                                        <th class="py-3 text-right pr-4 border-bottom border-light font-weight-bold border-top-0 text-danger">Net Sipariş</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <!-- TEDARİKÇİ GÖRÜNÜMÜ -->
                    <div id="viewSupplierContainer" style="display:none;" class="p-4 bg-light"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global JSON Data -->
    <script>
        // Inject Google Fonts
        (function() {
            var link = document.createElement('link');
            link.href = 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap';
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        })();

        window.globalSiparisData = <?php echo json_encode(isset($global_siparis_listesi) ? $global_siparis_listesi : []); ?>;
    </script>
    
    <script>
    // Üretilebilir hesaplama fonksiyonu
    function hesaplaUretilebilir() {
        // Seçili bileşen türlerini al
        var seciliTurler = [];
        document.querySelectorAll('.bilesen-checkbox:checked').forEach(function(chk) {
            seciliTurler.push(chk.value);
        });
        
        // Her üretilebilir hücresini güncelle
        document.querySelectorAll('.uretilebilir-hucre').forEach(function(hucre) {
            var row = hucre.closest('tr');
            var urunTipi = row.getAttribute('data-urun-tipi');

            // EĞER HAZIR ALINAN ÜRÜNSE HESAPLAMAYI ATLA (Sunucu tarafı geçerli kalsın)
            if (urunTipi === 'hazir_alinan') {
                return; 
            }

            var bilesenData = JSON.parse(hucre.getAttribute('data-bilesen-uretilebilir') || '{}');
            var acik = parseFloat(hucre.getAttribute('data-acik') || 0);
            
            // Seçili türlere göre minimum üretilebilir hesapla
            // SEÇİLİ TÜM bileşenler üründe tanımlı olmalı, yoksa 0
            var minUretilebilir = Infinity;
            var tumSeciliTanimli = true;
            var herhangiSecili = seciliTurler.length > 0;
            
            seciliTurler.forEach(function(tur) {
                // null = bu bileşen üründe tanımlı değil
                if (bilesenData[tur] === null || bilesenData[tur] === undefined) {
                    // Seçili bileşen tanımlı değil = üretilemez
                    tumSeciliTanimli = false;
                } else {
                    if (bilesenData[tur] < minUretilebilir) {
                        minUretilebilir = bilesenData[tur];
                    }
                }
            });
            
            // Sonucu güncelle - seçili bileşenlerden biri bile eksikse 0
            var uretilebilir = (herhangiSecili && tumSeciliTanimli) ? minUretilebilir : 0;
            if (uretilebilir === Infinity) uretilebilir = 0;
            
            var span = hucre.querySelector('.uretilebilir-deger');
            if (span) {
                span.textContent = uretilebilir.toLocaleString('tr-TR');
                span.className = uretilebilir > 0 ? 'text-success font-semibold uretilebilir-deger' : 'text-muted uretilebilir-deger';
                
                // Data attribute güncelle (sıralama vb. için)
                hucre.setAttribute('data-uretilebilir', uretilebilir);
                
                // Butondaki data attribute'u da güncelle (modal için referans)
                var btn = hucre.querySelector('.uretilebilir-detay-btn');
                if (btn) {
                    btn.setAttribute('data-uretilebilir', uretilebilir);
                }
            }
            
            // Önerilen üretimi de güncelle (bir sonraki td)
            var onerilen = Math.max(0, Math.min(acik, uretilebilir));
            var onerilenTd = hucre.nextElementSibling;
            if (onerilenTd) {
                if (onerilen > 0) {
                    onerilenTd.innerHTML = '<span class="font-semibold" style="color: var(--primary);">' + onerilen.toLocaleString('tr-TR') + '</span>';
                } else {
                    onerilenTd.innerHTML = '-';
                }
                
                // Durum kolonunu güncelle (İYİ/KÖTÜ) - Bir sonraki kolon
                var durumTd = onerilenTd.nextElementSibling;
                if (durumTd) {
                    var badge = durumTd.querySelector('.badge-sm');
                    if (badge) {
                        // Eğer açık sıfırsa veya üretim açığı kapatabiliyorsa -> İYİ
                        if (acik <= 0 || uretilebilir >= acik) {
                            badge.className = 'badge-sm badge-iyi';
                            badge.textContent = 'İYİ';
                        } else {
                            badge.className = 'badge-sm badge-kotu';
                            badge.textContent = 'KÖTÜ';
                        }
                    }
                }
            }
            
            // Aksiyon Önerisi kolonunu güncelle
            var row = hucre.closest('tr');
            var aksiyonHucre = row.querySelector('.aksiyon-onerisi-hucre');
            if (false) { // JS Guncellemesi Iptal - PHP Hesaplamasi Gecerli
                var badge = aksiyonHucre.querySelector('.aksiyon-badge');
                if (badge) {
                    var eksikBilesenler = JSON.parse(aksiyonHucre.getAttribute('data-eksik-bilesenler') || '[]');
                    var esansAgaciEksik = JSON.parse(aksiyonHucre.getAttribute('data-esans-agaci-eksik') || '[]');
                    var sozlesmeEksik = JSON.parse(aksiyonHucre.getAttribute('data-sozlesme-eksik') || '[]');
                    var originalClass = badge.getAttribute('data-original-class');
                    var originalMesaj = badge.getAttribute('data-original-mesaj');
                    
                    var mesajSpan = badge.querySelector('.aksiyon-mesaj');
                    var iconEl = badge.querySelector('.aksiyon-icon');
                    
                    // Aksiyon durumunu hesapla
                    var newClass = '';
                    var newMesaj = '';
                    var newIcon = '';
                    
                    // Aksiyon durumunu hesapla
                    var newClass = '';
                    var newMesaj = '';
                    var newIcon = '';
                    var category = 'ok';
                    
                    if (eksikBilesenler.length > 0 || esansAgaciEksik.length > 0) {
                        // Veri eksikliği - en yüksek öncelik
                        newClass = 'badge-aksiyon-kritik';
                        newMesaj = 'Ürün Ağacı ve Esans Formüllerini Tamamlayın';
                        newIcon = 'fas fa-exclamation-triangle';
                        category = 'critical';
                    } else if (acik > 0 && sozlesmeEksik.length > 0 && uretilebilir < acik) {
                        // Sözleşme eksik ve üretim yetersiz
                        newClass = 'badge-aksiyon-kritik';
                        newMesaj = 'Sözleşme Eksik - Önce Sözleşme Tamamlayın';
                        newIcon = 'fas fa-ban';
                        category = 'critical';
                    } else if (acik > 0 && sozlesmeEksik.length > 0) {
                        // Sözleşme eksik ama üretim yeterli
                        newClass = 'badge-aksiyon-uyari';
                        newMesaj = 'Gelecek Siparişler İçin Sözleşme Tamamlayın';
                        newIcon = 'fas fa-file-contract';
                        category = 'warning';
                    } else if (acik > 0 && uretilebilir >= acik) {
                        // Üretim yapılabilir
                        newClass = 'badge-aksiyon-bilgi';
                        newMesaj = 'Montaj İş Emri Oluşturun (' + onerilen.toLocaleString('tr-TR') + ' adet)';
                        newIcon = 'fas fa-tools';
                        category = 'production';
                    } else if (acik > 0 && uretilebilir < acik && uretilebilir > 0) {
                        // Kısmi üretim yapılabilir -> Sipariş de gerekli
                        newClass = 'badge-aksiyon-uyari';
                        newMesaj = 'Malzeme Yetersiz (' + uretilebilir.toLocaleString('tr-TR') + '/' + acik.toLocaleString('tr-TR') + ')';
                        newIcon = 'fas fa-exclamation-circle';
                        category = 'order';
                    } else if (acik > 0 && uretilebilir <= 0) {
                        // Üretim yapılamaz
                        newClass = 'badge-aksiyon-kritik';
                        newMesaj = 'Bileşen Yetersiz - Malzeme Siparişi Verin';
                        newIcon = 'fas fa-times-circle';
                        category = 'order';
                    } else if (acik <= 0 && sozlesmeEksik.length > 0) {
                        // Stok yeterli ama sözleşme eksik
                        newClass = 'badge-aksiyon-uyari';
                        newMesaj = 'Gelecek Üretimler İçin Sözleşme Tamamlayın';
                        newIcon = 'fas fa-file-contract';
                        category = 'warning';
                    } else {
                        // Her şey yolunda
                        newClass = 'badge-aksiyon-basarili';
                        newMesaj = 'Her Şey Yolunda';
                        newIcon = 'fas fa-check-circle';
                        category = 'ok';
                    }
                    
                    // Badge'i güncelle
                    badge.className = 'badge badge-pill ' + newClass + ' p-2 aksiyon-badge';
                    if (mesajSpan) mesajSpan.textContent = newMesaj;
                    if (iconEl) iconEl.className = newIcon + ' mr-1 aksiyon-icon';
                    
                    // Category attribute güncelle (Filtreleme için)
                    badge.setAttribute('data-action-category', category);
                    aksiyonHucre.setAttribute('data-action-category', category);
                }
            }
            
            // Yorum kolonunu güncelle (satırın son td'si)
            var row = hucre.closest('tr');
            var yorumTd = row.querySelector('.durum-cell');
            if (yorumTd) {
                var stok = parseFloat(yorumTd.getAttribute('data-stok') || 0);
                var siparis = parseFloat(yorumTd.getAttribute('data-siparis') || 0);
                var kritik = parseFloat(yorumTd.getAttribute('data-kritik') || 0);
                var yuzdeFark = parseFloat(yorumTd.getAttribute('data-yuzde-fark') || 0);
                var uretimde = parseFloat(yorumTd.getAttribute('data-uretimde') || 0);
                
                // Üretim sonrası hesaplamalar
                var uretimSonrasiStok = stok + onerilen;
                var siparisSonrasiStok = uretimSonrasiStok - siparis;
                
                var yorum = '';
                
                // Stok yeterli kontrolü: kritik ve sipariş mevcut stokla karşılanabiliyorsa
                var stokYeterli = (stok >= kritik) && (stok >= siparis);
                
                // Eksik veri kontrolü (BOM veya Esans Reçetesi)
                var yorumTd = row.querySelector('.durum-cell');
                var eksikBilesenler = JSON.parse(yorumTd.getAttribute('data-eksik-bilesenler') || '[]');
                var esansAgaciEksik = JSON.parse(yorumTd.getAttribute('data-esans-agaci-eksik') || '[]');
                
                if (eksikBilesenler.length > 0 || esansAgaciEksik.length > 0) {
                    var eksikVeriMsj = '';
                    
                    // Önce ihtiyaç durumunu yaz (Eğer eksik varsa)
                    if (acik > 0) {
                        // İhtiyaç analizi yap
                        var siparisGereken = Math.max(0, siparis - stok);
                        var stokSonrasi = Math.max(0, stok - siparis);
                        var uretimleBirlikte = stokSonrasi + uretimde;
                        var kritikGereken = (kritik > 0) ? Math.max(0, kritik - uretimleBirlikte) : 0;
                        
                        var kaynaklar = [];
                        if (siparisGereken > 0) kaynaklar.push('<span class="text-nowrap"><b>' + siparisGereken.toLocaleString('tr-TR') + '</b> Sipariş</span>');
                        if (kritikGereken > 0) kaynaklar.push('<span class="text-nowrap"><b>' + kritikGereken.toLocaleString('tr-TR') + '</b> Kritik Stok</span>');
                        
                        eksikVeriMsj += '<div class="mb-2 text-danger" style="line-height:1.2;">';
                        eksikVeriMsj += '<i class="fas fa-arrow-down"></i> ' + kaynaklar.join(' + ') + ' için<br>';
                        eksikVeriMsj += '<span class="font-weight-bold" style="font-size:1.1em; margin-left:18px;">Toplam ' + acik.toLocaleString('tr-TR') + ' adet eksik</span>';
                        eksikVeriMsj += '</div>';
                    } else {
                         // Stok yeterli ama veri eksik olduğu için üretim yapılamaz uyarısı
                        eksikVeriMsj += '<div class="mb-2 font-weight-bold text-success"><i class="fas fa-check-circle"></i> Stok yeterli</div>';
                    }

                    eksikVeriMsj += '<div class="p-2" style="background:#fff3cd; border-radius:4px; border-left:3px solid #ffc107;">';
                    eksikVeriMsj += '<strong class="text-warning"><i class="fas fa-exclamation-triangle"></i> Hesaplama Yapılamadı</strong>';
                    
                    if (eksikBilesenler.length > 0) {
                        eksikVeriMsj += '<br><small class="text-muted">Ürün ağacında eksik:</small><br>' + eksikBilesenler.join(', ');
                    }
                    
                    if (esansAgaciEksik.length > 0) {
                        eksikVeriMsj += '<br><small class="text-danger">Esans reçetesi yok:</small><br>' + esansAgaciEksik.join(', ');
                    }
                     
                    eksikVeriMsj += '</div>';
                    yorum = eksikVeriMsj;
                }
                else if (stokYeterli && siparis <= 0) {
                    yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> Stok yeterli.</span>';
                    
                    // Stok yeterli ama bileşen/üretim imkanı kontrolü
                    if (uretilebilir <= 0) {
                         yorum += '<div class="mt-1 small text-muted"><i class="fas fa-ban"></i> Ancak bileşen stoğu 0, üretim yapılamaz.</div>';
                    }
                } else if (kritik <= 0 && siparis > 0) {
                    yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> Stok yeterli.</span>';
                } else if (kritik <= 0 && siparis > 0) {
                    if (siparis > stok) {
                        var eksik = siparis - stok;
                        if (uretilebilir >= eksik) {
                            yorum = '<span class="text-danger"><i class="fas fa-shopping-cart"></i> ' + siparis.toLocaleString('tr-TR') + ' sipariş var. ' + eksik.toLocaleString('tr-TR') + ' adet üretilmeli.</span>';
                        } else if (uretilebilir > 0) {
                            var kalanEksik = eksik - uretilebilir;
                            yorum = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + siparis.toLocaleString('tr-TR') + ' sipariş var. Max ' + uretilebilir.toLocaleString('tr-TR') + ' üretilebilir, ' + kalanEksik.toLocaleString('tr-TR') + ' eksik kalır!</span>';
                        } else {
                            yorum = '<span class="text-danger"><i class="fas fa-times-circle"></i> ' + siparis.toLocaleString('tr-TR') + ' sipariş var ama bileşen yetersiz! Malzeme siparişi verin.</span>';
                        }
                    } else {
                        yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + siparis.toLocaleString('tr-TR') + ' sipariş stoktan karşılanabilir.</span>';
                    }
                } else if (uretilebilir > 0) {
                    var siparisKarsilanir = (siparis <= 0) || (uretimSonrasiStok >= siparis);
                    var kritikKarsilanir = (kritik <= 0) || (siparisSonrasiStok >= kritik);
                    
                    // Hesaplama mantığı:
                    // 1. Sipariş için eksik: Max(0, Sipariş - (Stok + ÜretimSonrasi)) -> Yanlış, ÜretimSonrasi zaten stok+önerilen
                    // Doğrusu: SiparişGereken = Max(0, Sipariş - (Stok + Önerilen))
                    var siparisIcınEksik = Math.max(0, siparis - uretimSonrasiStok);
                    
                    // 2. Kritik için eksik: Max(0, Kritik - (Stok + Önerilen - Sipariş)) 
                    // Sipariş öncelikli olduğu için, stoktan siparişi düşüp kalana bakıyoruz
                    var kritikIcınEksik = (kritik > 0) ? Math.max(0, kritik - Math.max(0, uretimSonrasiStok - siparis)) : 0;
                    
                    var detaylar = [];
                    
                    // Eğer eksik varsa breakdown göster
                    if (siparisIcınEksik > 0 || kritikIcınEksik > 0) {
                        var kaynaklar = [];
                        if (siparisIcınEksik > 0) kaynaklar.push('<span class="text-nowrap"><b>' + siparisIcınEksik.toLocaleString('tr-TR') + '</b> Sipariş</span>');
                        if (kritikIcınEksik > 0) kaynaklar.push('<span class="text-nowrap"><b>' + kritikIcınEksik.toLocaleString('tr-TR') + '</b> Kritik Stok</span>');
                        
                        var toplamEksik = siparisIcınEksik + kritikIcınEksik;
                        
                        var breakdownHtml = '<div class="mb-2 text-danger" style="line-height:1.2;">';
                        breakdownHtml += '<i class="fas fa-arrow-down"></i> ' + kaynaklar.join(' + ') + ' için<br>';
                        breakdownHtml += '<span class="font-weight-bold" style="font-size:1.1em; margin-left:18px;">Toplam ' + toplamEksik.toLocaleString('tr-TR') + ' adet eksik</span>';
                        breakdownHtml += '</div>';
                        
                        detaylar.push(breakdownHtml);
                    } 
                    // Eksik yoksa başarı mesajları
                    else {
                         if (siparisKarsilanir && siparis > 0) {
                            detaylar.push('<span class="text-success">✓ Sipariş (' + siparis.toLocaleString('tr-TR') + ') karşılanır</span>');
                        }
                        if (kritikKarsilanir && kritik > 0) {
                            detaylar.push('<span class="text-success">✓ Kritik stok karşılanır</span>');
                        }
                    }

                    if (detaylar.length > 0) {
                        yorum = '<div>' + detaylar.join('<br>') + '</div>';
                    }
                    
                    // Hücreleri bul
                    var durumTd = row.querySelector('.durum-cell');
                    var montajTd = row.querySelector('.montaj-cell');
                    var malzemeTd = row.querySelector('.malzeme-cell');
                    var esansTd = row.querySelector('.esans-cell');
                    
                    if (durumTd) durumTd.innerHTML = yorum;
                    
                    // Montaj
                    if (montajTd) {
                         if (onerilen > 0) {
                            montajTd.innerHTML = '<div class="text-center"><div class="badge badge-info p-2 mb-1" style="font-size: 0.85rem; font-weight: 600;"><i class="fas fa-industry mr-1"></i> ' + onerilen.toLocaleString('tr-TR') + ' Adet</div><div class="small text-muted" style="font-size: 0.75rem; line-height: 1.2;">' + onerilen.toLocaleString('tr-TR') + ' adet için<br>iş emri açılmalı</div></div>';
                        } else {
                            montajTd.innerHTML = '<div class="text-center"><span class="text-success small"><i class="fas fa-check-circle"></i></span><div class="small text-muted" style="font-size: 0.7rem;">İş emri<br>gerekmez</div></div>';
                        }
                    }

                    // Eksik Hesaplama
                    var eksikSiparisList = [];
                    var eksikUretimList = [];
                    var esansUretimBilgisi = JSON.parse(yorumTd.getAttribute('data-esans-uretim-bilgisi') || '{}');
                    
                    if (!siparisKarsilanir || !kritikKarsilanir) {
                         var bilesenDetaylari = JSON.parse(yorumTd.getAttribute('data-bilesen-detaylari') || '[]');
                         var gerekliUretim = Math.max(siparis - stok, kritik) - uretilebilir;
                         
                         bilesenDetaylari.forEach(function(bilesen) {
                             var gerekli = bilesen.gerekli_adet * gerekliUretim;
                             var eksik = Math.max(0, gerekli);
                             if (eksik > 0) {
                                 var metin = bilesen.isim + ' (' + Math.ceil(eksik).toLocaleString('tr-TR') + ')';
                                 if (bilesen.tur === 'esans') eksikUretimList.push(metin);
                                 else eksikSiparisList.push(metin);
                             }
                         });
                    }
                    
                    // Malzeme Render
                    if (malzemeTd) {
                        var html = '';
                        if (eksikSiparisList.length > 0) {
                             html = '<div class="font-weight-bold text-warning mb-0" style="font-size:11px; line-height:1.2;">Malzeme Sipariş:</div>';
                             html += '<div class="ml-1 mt-0">';
                             eksikSiparisList.forEach(function(item) {
                                  html += '<div class="text-nowrap" style="line-height:1.1; font-size:11px;">' + item + '</div>';
                             });
                             html += '</div>';
                        } else {
                            html = '<span class="text-muted small">-</span>';
                        }
                        malzemeTd.innerHTML = html;
                    }

                    // Esans Render
                    if (esansTd) {
                        var html = '';
                        if (eksikUretimList.length > 0) {
                             // Toplam esans miktarını hesapla
                             var toplamEsansMiktar = 0;
                             var mevcutIsEmriMiktari = 0;
                             var bilesenDetaylari = JSON.parse(yorumTd.getAttribute('data-bilesen-detaylari') || '[]');
                             var gerekliUretim = Math.max(siparis - stok, kritik);
                             
                             bilesenDetaylari.forEach(function(bilesen) {
                                 if (bilesen.tur === 'esans') {
                                     toplamEsansMiktar += Math.ceil(bilesen.gerekli_adet * gerekliUretim);
                                     
                                     // Bu esans için açık iş emri var mı?
                                     if (esansUretimBilgisi && esansUretimBilgisi[bilesen.kodu]) {
                                         mevcutIsEmriMiktari += parseFloat(esansUretimBilgisi[bilesen.kodu].acik_is_emri_miktar || 0);
                                     }
                                 }
                             });
                             
                             var netIhtiyac = toplamEsansMiktar - mevcutIsEmriMiktari;
                             
                             html = '<div class="text-center">';
                             
                             if (netIhtiyac > 0) {
                                 // İhtiyaç devam ediyor
                                 html += '<div class="badge badge-primary p-2 mb-1" style="font-size: 0.85rem; font-weight: 600;"><i class="fas fa-flask mr-1"></i> ' + netIhtiyac.toLocaleString('tr-TR') + ' Adet</div>';
                                 html += '<div class="small text-muted" style="font-size: 0.75rem; line-height: 1.2;">' + netIhtiyac.toLocaleString('tr-TR') + ' adet için<br>iş emri açılmalı</div>';
                                 
                                 if (mevcutIsEmriMiktari > 0) {
                                    html += '<div class="mt-1 text-info" style="font-size: 0.7rem;">(+' + mevcutIsEmriMiktari.toLocaleString('tr-TR') + ' üretimde)</div>'; 
                                 }
                             } else {
                                 // İhtiyaç karşılanmış
                                 html += '<div class="badge badge-success p-2 mb-1" style="font-size: 0.85rem; font-weight: 600;"><i class="fas fa-check-double mr-1"></i> Yeterli</div>';
                                 html += '<div class="small text-muted" style="font-size: 0.75rem; line-height: 1.2;">' + mevcutIsEmriMiktari.toLocaleString('tr-TR') + ' adet<br>üretimde/sırada</div>';
                             }
                             
                             // Detay bilgi (Hover veya alt kısım)
                             if (Object.keys(esansUretimBilgisi).length > 0) {
                                 html += '<div class="mt-2 pt-2 border-top" style="font-size: 10px;">';
                                 for (var k in esansUretimBilgisi) {
                                     var info = esansUretimBilgisi[k];
                                      // Sadece ilgili esansları göster (basitçe hepsi de olabilir ama filtrelemek daha iyi olurdu, şimdilik hepsi)
                                     
                                     // Malzeme durumu
                                     if (!info.malzeme_yeterli && info.eksik_malzemeler.length > 0) {
                                         html += '<div><span class="badge badge-danger" style="font-size:9px;"><i class="fas fa-times mr-1"></i>Hammade eksik</span></div>';
                                     }
                                 }
                                 html += '</div>';
                             }
                             html += '</div>';
                        } else {
                            html = '<div class="text-center"><span class="text-success"><i class="fas fa-check-circle"></i></span><div class="small text-muted" style="font-size: 0.7rem;">İş emri<br>gerekmez</div></div>';
                        }
                        esansTd.innerHTML = html;
                    }


                } else {
                    // --- Bileşen Yetersiz Bloğu ---
                    
                    var durumTd = row.querySelector('.durum-cell');
                    var montajTd = row.querySelector('.montaj-cell');
                    var malzemeTd = row.querySelector('.malzeme-cell');
                    var esansTd = row.querySelector('.esans-cell');
                    
                    // Durum: Sadece "Bileşen Yetersiz" ve breakdown
                     var yorum = '<div class="p-2" style="background:#fef2f2; border-radius:4px; border-left:3px solid #dc3545;">';
                     yorum += '<strong class="text-danger"><i class="fas fa-times-circle"></i> Bileşen Yetersiz</strong>';
                     // Sipariş/Kritik breakdown ekle
                     var detaylar = [];
                     // (Breakdown logic is generic, but simplified here for "Bileşen Yetersiz" case usually implies we can't meet demand)
                     yorum += '</div>';
                     if (durumTd) durumTd.innerHTML = yorum;
                     
                     if (montajTd) montajTd.innerHTML = '<div class="text-center"><span class="text-danger"><i class="fas fa-times-circle"></i></span><div class="small text-danger" style="font-size: 0.7rem;">Bileşen eksik<br>yapılamaz</div></div>';
                     
                     // Hesapla
                     var bilesenDetaylari = JSON.parse(yorumTd.getAttribute('data-bilesen-detaylari') || '[]');
                     var gerekliUretim = Math.max(siparis - stok, kritik);
                     var eksikSiparisList = [];
                     var eksikUretimList = [];
                     var esansUretimBilgisi = JSON.parse(yorumTd.getAttribute('data-esans-uretim-bilgisi') || '{}');

                     bilesenDetaylari.forEach(function(bilesen) {
                         var gerekli = bilesen.gerekli_adet * gerekliUretim;
                         var eksik = Math.max(0, gerekli - bilesen.mevcut_stok);
                         if (eksik > 0) {
                             var metin = bilesen.isim + ' (' + Math.ceil(eksik).toLocaleString('tr-TR') + ')';
                             if (bilesen.tur === 'esans') eksikUretimList.push(metin);
                             else eksikSiparisList.push(metin);
                         }
                     });
                     
                     // Malzeme Render
                     if (malzemeTd) {
                         var html = '';
                         if (eksikSiparisList.length > 0) {
                             html = '<div class="font-weight-bold text-warning mb-0" style="font-size:11px; line-height:1.2;">Malzeme Sipariş:</div>';
                             html += '<div class="ml-1 mt-0">';
                             eksikSiparisList.forEach(function(item) {
                                  html += '<div class="text-nowrap" style="line-height:1.1; font-size:11px;">' + item + '</div>';
                             });
                             html += '</div>';
                         } else { html = '<span class="text-muted small">-</span>'; }
                         malzemeTd.innerHTML = html;
                     }
                     
                     // Esans Render
                     if (esansTd) {
                         var html = '';
                         if (eksikUretimList.length > 0) {
                             // Toplam esans miktarını hesapla
                             var toplamEsansMiktar = 0;
                             var bilesenDetaylari = JSON.parse(yorumTd.getAttribute('data-bilesen-detaylari') || '[]');
                             var gerekliUretim = Math.max(siparis - stok, kritik);
                             
                             bilesenDetaylari.forEach(function(bilesen) {
                                 if (bilesen.tur === 'esans') {
                                     toplamEsansMiktar += Math.ceil(bilesen.gerekli_adet * gerekliUretim);
                                 }
                             });
                             
                             html = '<div class="text-center">';
                             html += '<div class="badge badge-danger p-2 mb-1" style="font-size: 0.85rem; font-weight: 600;"><i class="fas fa-flask mr-1"></i> ' + toplamEsansMiktar.toLocaleString('tr-TR') + ' Adet</div>';
                             html += '<div class="small text-danger" style="font-size: 0.75rem; line-height: 1.2;">' + toplamEsansMiktar.toLocaleString('tr-TR') + ' adet için<br>iş emri açılmalı</div>';
                             
                             // İş emri ve malzeme durumu (detay bilgi)
                             if (Object.keys(esansUretimBilgisi).length > 0) {
                                 html += '<div class="mt-2 pt-2 border-top" style="font-size: 10px;">';
                                 for (var k in esansUretimBilgisi) {
                                     var info = esansUretimBilgisi[k];
                                     
                                     // İş emri durumu
                                     if (info.acik_is_emri_miktar > 0) {
                                         html += '<div class="mb-1"><span class="badge badge-success" style="font-size:9px;"><i class="fas fa-check mr-1"></i>Açık: ' + info.acik_is_emri_miktar.toLocaleString('tr-TR') + '</span></div>';
                                     } else {
                                         html += '<div class="mb-1"><span class="badge badge-warning" style="font-size:9px;"><i class="fas fa-exclamation mr-1"></i>Bekliyor</span></div>';
                                     }
                                     
                                     // Malzeme durumu
                                     if (!info.malzeme_yeterli && info.eksik_malzemeler.length > 0) {
                                         html += '<div><span class="badge badge-danger" style="font-size:9px;"><i class="fas fa-times mr-1"></i>Malzeme eksik</span></div>';
                                     } else if (info.malzeme_yeterli) {
                                         html += '<div><span class="badge badge-success" style="font-size:9px;"><i class="fas fa-check mr-1"></i>Malzeme hazır</span></div>';
                                     }
                                 }
                                 html += '</div>';
                             }
                             html += '</div>';
                         } else { 
                             html = '<div class="text-center"><span class="text-success"><i class="fas fa-check-circle"></i></span><div class="small text-muted" style="font-size: 0.7rem;">İş emri<br>gerekmez</div></div>'; 
                         }
                         esansTd.innerHTML = html;
                     }


                }
                
                // Üretimde varsa ekle (To Durum cell)
                if (uretimde > 0) {
                     var durumTd = row.querySelector('.durum-cell');
                     if (durumTd) durumTd.innerHTML += ' <small class="text-info d-block mt-1"><i class="fas fa-cog fa-spin"></i> Üretimde: ' + uretimde.toLocaleString('tr-TR') + '</small>';
                }
            }
        });
    }
    
    // Checkbox değişikliklerini dinle
    document.querySelectorAll('.bilesen-checkbox').forEach(function(chk) {
        chk.addEventListener('change', hesaplaUretilebilir);
    });
    
    // Global Filtre State
    var filterState = {
        search: '',
        onlyPositiveDiff: false,
        actionCategory: 'all'
    };

    // Filtre Uygulama Fonksiyonu (Merkezi)
    function applyTableFilters() {
        var rows = document.querySelectorAll('.table tbody tr');
        
        rows.forEach(function(row) {
            var show = true;
            
            // 1. Arama Filtresi
            if (filterState.search !== '') {
                // Sadece 2. sütundaki ÜRÜN İSMİNDE arama yap (kodu hariç)
                var urunSutunu = row.querySelector('td:nth-child(2)');
                var urunIsmiSpan = urunSutunu ? urunSutunu.querySelector('span.font-semibold') : null;
                var text = urunIsmiSpan ? urunIsmiSpan.textContent.toLowerCase() : '';
                
                if (text.indexOf(filterState.search) === -1) {
                    show = false;
                }
            }
            
            // 2. Fark Filtresi
            if (show && filterState.onlyPositiveDiff) {
                var diffCell = row.querySelector('td[data-sort-fark]');
                var diff = diffCell ? parseFloat(diffCell.getAttribute('data-sort-fark')) : 0;
                
                if (diff <= 0) {
                    show = false;
                }
            }

            // 3. Aksiyon Filtresi
            if (show && filterState.actionCategory && filterState.actionCategory !== 'all') {
                var aksiyonHucre = row.querySelector('.aksiyon-onerisi-hucre');
                var cat = aksiyonHucre ? aksiyonHucre.getAttribute('data-action-category') : 'ok';
                
                if (cat !== filterState.actionCategory) {
                    show = false;
                }
            }
            
            row.style.display = show ? '' : 'none';
        });
    }

    // Arama Event Listener
    var searchInput = document.getElementById('urunAramaInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterState.search = this.value.toLowerCase().trim();
            applyTableFilters();
        });
    }
    
    // Aksiyon Filtre Set Fonksiyonu
    window.setActionFilter = function(category, item) {
        filterState.actionCategory = category;
        
        // Dropdown buton metnini güncelle
        var btn = document.getElementById('dropdownActionFilter');
        // Seçilen öğenin ikonunu al (i tagı)
        var iconHtml = item.querySelector('i') ? item.querySelector('i').outerHTML : '';
        // İkon ve metni butona koy (ikon + metin)
        btn.innerHTML = iconHtml + ' ' + item.textContent.trim();
        
        applyTableFilters();
    };

    // Fark Filtre Toggle Fonksiyonu
    
    // Fark Filtre Toggle Fonksiyonu
    window.toggleFarkFiltre = function(btn) {
        filterState.onlyPositiveDiff = !filterState.onlyPositiveDiff;
        
        if (filterState.onlyPositiveDiff) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-secondary');
            btn.classList.add('active'); // Bootstrap active class
            
            // Filtre açıldığında otomatik olarak BÜYÜKTEN KÜÇÜĞE sırala
            var th = document.querySelector('th[onclick="sortTable(\'fark\')"]');
            if (th) {
                // Descending (Büyükten küçüğe) sıralamak için,
                // sortTable fonksiyonu 'isAscending'i th attribute'undan okur.
                // Eğer attribute 'desc' ise isAscending=false olur ve Descending sıralar.
                th.setAttribute('data-order', 'desc');
                sortTable('fark');
            }
        } else {
            btn.classList.remove('btn-secondary');
            btn.classList.remove('active');
            btn.classList.add('btn-outline-secondary');
        }
        
        applyTableFilters();
    };
    
    // Tablo Sıralama Fonksiyonu
    window.sortTable = function(key) {
        var table = document.querySelector('.table');
        var tbody = table.querySelector('tbody');
        var rows = Array.from(tbody.querySelectorAll('tr'));
        var th = table.querySelector('th[onclick="sortTable(\'' + key + '\')"]');
        var icon = th.querySelector('.fa-sort, .fa-sort-up, .fa-sort-down');
        
        // Sıralama yönünü belirle
        var isAscending = th.getAttribute('data-order') === 'asc';
        var newOrder = isAscending ? 'desc' : 'asc';
        th.setAttribute('data-order', newOrder);
        
        // İkonu güncelle
        if (icon) {
            icon.className = isAscending ? 'fas fa-sort-down ml-1' : 'fas fa-sort-up ml-1';
        }
        
        // Sıralama yap
        rows.sort(function(a, b) {
            var cellA = a.querySelector('td[data-sort-' + key + ']');
            var cellB = b.querySelector('td[data-sort-' + key + ']');
            
            var valA = cellA ? (parseFloat(cellA.getAttribute('data-sort-' + key)) || 0) : 0;
            var valB = cellB ? (parseFloat(cellB.getAttribute('data-sort-' + key)) || 0) : 0;
            
            if (valA === valB) return 0;
            
            if (isAscending) {
                return valA - valB; // Küçükten büyüğe
            } else {
                return valB - valA; // Büyükten küçüğe
            }
        });
        
        // Satırları yeniden ekle
        rows.forEach(function(row) {
            tbody.appendChild(row);
        });
    };
    
    // Açık Detay Modalı Handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.acik-detay-btn')) {
            e.preventDefault();
            var btn = e.target.closest('.acik-detay-btn');
            
            var isim = btn.getAttribute('data-urun-ismi');
            var siparis = parseFloat(btn.getAttribute('data-siparis')) || 0;
            var kritik = parseFloat(btn.getAttribute('data-kritik')) || 0;
            var stok = parseFloat(btn.getAttribute('data-stok')) || 0;
            var uretimde = parseFloat(btn.getAttribute('data-uretimde')) || 0;
            var acik = parseFloat(btn.getAttribute('data-acik')) || 0;

            var toplamIhtiyac = siparis + kritik;
            var toplamKaynak = stok + uretimde;

            document.getElementById('modalAcikUrunIsmi').textContent = isim;
            document.getElementById('modalAcikSiparis').textContent = siparis.toLocaleString('tr-TR');
            document.getElementById('modalAcikKritik').textContent = kritik.toLocaleString('tr-TR');
            document.getElementById('modalAcikToplamIhtiyac').textContent = toplamIhtiyac.toLocaleString('tr-TR');
            
            document.getElementById('modalAcikStok').textContent = stok.toLocaleString('tr-TR');
            document.getElementById('modalAcikUretimde').textContent = uretimde.toLocaleString('tr-TR');
            document.getElementById('modalAcikToplamKaynak').textContent = toplamKaynak.toLocaleString('tr-TR');

            var sonucSpan = document.getElementById('modalAcikSonuc');
            var alertDiv = document.getElementById('modalAcikAlert');
            var etiketDiv = document.getElementById('modalAcikSonucEtiket');

            if (acik > 0) {
                alertDiv.className = 'alert alert-danger text-center p-2 mb-0';
                sonucSpan.textContent = acik.toLocaleString('tr-TR') + ' EKSİK';
                etiketDiv.textContent = 'Üretilmesi Gerekiyor';
            } else {
                alertDiv.className = 'alert alert-success text-center p-2 mb-0';
                sonucSpan.textContent = '+' + Math.abs(acik).toLocaleString('tr-TR') + ' FAZLA';
                etiketDiv.textContent = 'İhtiyaç Karşılandı';
            }
            
            var modal = document.getElementById('acikDetayModal');
            modal.removeAttribute('aria-hidden');
            $(modal).modal('show');
        }
    });
    
    // Sipariş Listesi Render (Minimal)
    window.renderSiparisListesi = function() {
        var tbody = document.querySelector('#siparisListesiTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        var data = window.globalSiparisData || [];
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted small">Sipariş ihtiyacı bulunamadı.</td></tr>';
            return;
        }
        
        // Ürün adına göre sırala
        data.sort((a, b) => a.urun_ismi.localeCompare(b.urun_ismi));
        
        data.forEach(function(item, itemIdx) {
            var tr = document.createElement('tr');
            tr.setAttribute('data-item-index', itemIdx);
            
            // Seçili tedarikçi index (varsayılan 0 - en uygun)
            var seciliIdx = item.secili_tedarikci_index || 0;
            
            // Renk kodlaması (Sadece sol kenar çizgisi için veya minimal nokta)
            var tipColor = '#6c757d';
            if (item.tip.includes('Esans') && !item.tip.includes('Hammadde')) tipColor = '#6f42c1';
            else if (item.tip.includes('Kutu')) tipColor = '#17a2b8';
            else if (item.tip.includes('Takım')) tipColor = '#28a745';
            else if (item.tip.includes('Hammadde')) tipColor = '#fd7e14';
            
            var rowHtml = `
                <td class="pl-4 py-2 border-bottom border-light" style="vertical-align: middle;">
                    <div class="d-flex align-items-center">
                        <span style="font-size: 12px; font-weight: 700; color: #333;">${item.urun_ismi}</span>
                        <span class="text-muted ml-2" style="font-size: 11px;">#${item.urun_kodu}</span>
                    </div>
                </td>
                <td class="py-2 border-bottom border-light" style="vertical-align: middle;">
                     <span class="badge badge-pill px-2 py-1" style="font-size: 10px; color: ${tipColor}; background: ${tipColor}15; border: 1px solid ${tipColor}30;">${item.tip || '-'}</span>
                </td>
                <td class="py-2 border-bottom border-light" style="vertical-align: middle;">
                    <div class="text-truncate" style="max-width: 250px; font-size: 11px; color: #495057;" title="${item.malzeme_adi}">${item.malzeme_adi}</div>
                </td>
                <td class="text-right py-2 border-bottom border-light" style="vertical-align: middle;">
                    <span style="font-size: 11px; color: #444; font-weight: 600; font-family: 'Roboto Mono', monospace;">${parseFloat(item.stok).toLocaleString('tr-TR', {maximumFractionDigits: 0})}</span>
                    ${parseFloat(item.yoldaki) > 0 ? `<span class="ml-2 badge badge-info font-weight-normal" style="font-size: 9px;">+${parseFloat(item.yoldaki).toLocaleString('tr-TR', {maximumFractionDigits: 0})} Yolda</span>` : ''}
                </td>
                <td class="py-2 border-bottom border-light pl-3" style="vertical-align: top;">
                    ${item.tum_tedarikciler && item.tum_tedarikciler.length > 0 ? 
                        `<div style="font-size: 10px; line-height: 1.6;">
                            ${item.tum_tedarikciler.map((ted, tedIdx) => {
                                var isSecili = tedIdx === seciliIdx;
                                var radioName = 'tedarikci_' + itemIdx;
                                if (isSecili) {
                                    return `<div class="d-flex align-items-center mb-1 tedarikci-satir" style="background: #f0fff4; padding: 2px 4px; border-radius: 4px; cursor: pointer;" onclick="seciliTedarikciDegistir(${itemIdx}, ${tedIdx})">
                                        <input type="radio" name="${radioName}" value="${tedIdx}" ${isSecili ? 'checked' : ''} class="mr-1 tedarikci-radio" style="cursor: pointer;">
                                        <i class="fas fa-star text-warning mr-1" style="font-size: 9px;"></i>
                                        <span class="text-truncate font-weight-bold text-dark mr-2" style="max-width: 130px;" title="${ted.adi}">${ted.adi}</span>
                                        <span class="text-success font-weight-bold" style="font-family: 'Roboto Mono', monospace;">${parseFloat(ted.fiyat).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 4})} ${ted.para_birimi}</span>
                                    </div>`;
                                } else {
                                    return `<div class="d-flex align-items-center text-muted tedarikci-satir" style="cursor: pointer; padding: 2px 4px;" onclick="seciliTedarikciDegistir(${itemIdx}, ${tedIdx})">
                                        <input type="radio" name="${radioName}" value="${tedIdx}" class="mr-1 tedarikci-radio" style="cursor: pointer;">
                                        <i class="fas fa-truck mr-1" style="font-size: 8px;"></i>
                                        <span class="text-truncate mr-2" style="max-width: 130px;" title="${ted.adi}">${ted.adi}</span>
                                        <span style="font-family: 'Roboto Mono', monospace;">${parseFloat(ted.fiyat).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 4})} ${ted.para_birimi}</span>
                                    </div>`;
                                }
                            }).join('')}
                        </div>`
                        : (item.tedarikci && item.tedarikci.adi !== '-' ? 
                            `<div class="d-flex align-items-center" style="font-size: 11px;">
                                 <span class="text-truncate font-weight-bold text-dark mr-2" style="max-width: 180px;" title="${item.tedarikci.adi}">${item.tedarikci.adi}</span>
                                 <span class="text-success font-weight-bold" style="font-family: 'Roboto Mono', monospace;">${parseFloat(item.tedarikci.fiyat).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 4})} ${item.tedarikci.para_birimi}</span>
                            </div>` 
                            : '<span class="text-muted small">-</span>')}
                </td>
                <td class="text-right py-2 pr-4 border-bottom border-light" style="vertical-align: middle; background-color: #fff5f5;">
                    <span style="font-size: 13px; font-weight: 700; color: #c53030; font-family: 'Roboto Mono', monospace;">
                        ${parseFloat(item.net_siparis).toLocaleString('tr-TR', {maximumFractionDigits: 2})}
                    </span>
                    <span class="text-muted ml-1" style="font-size: 10px; text-transform: uppercase;">${item.birim}</span>
                </td>
            `;
            
            tr.innerHTML = rowHtml;
            tbody.appendChild(tr);
        });
    };

    // Görünüm Değiştirme
    window.currentSiparisView = 'list';
    window.switchSiparisView = function(mode) {
        window.currentSiparisView = mode;
        
        if (mode === 'list') {
            document.getElementById('viewListContainer').style.display = 'block';
            document.getElementById('viewSupplierContainer').style.display = 'none';
            
            $('#btnViewList').removeClass('btn-light text-muted').addClass('btn-white shadow-sm font-weight-bold');
            $('#btnViewSupplier').removeClass('btn-white shadow-sm font-weight-bold').addClass('btn-light text-muted');
            
            renderSiparisListesi();
        } else {
            document.getElementById('viewListContainer').style.display = 'none';
            document.getElementById('viewSupplierContainer').style.display = 'block';
            
            $('#btnViewSupplier').removeClass('btn-light text-muted').addClass('btn-white shadow-sm font-weight-bold');
            $('#btnViewList').removeClass('btn-white shadow-sm font-weight-bold').addClass('btn-light text-muted');
            
            renderTedarikciListesi();
        }
    };
    
    // Seçili Tedarikçi Değiştir
    window.seciliTedarikciDegistir = function(itemIdx, tedIdx) {
        var data = window.globalSiparisData || [];
        if (data[itemIdx] && data[itemIdx].tum_tedarikciler && data[itemIdx].tum_tedarikciler[tedIdx]) {
            // Seçili tedarikçiyi güncelle
            data[itemIdx].secili_tedarikci = data[itemIdx].tum_tedarikciler[tedIdx];
            data[itemIdx].secili_tedarikci_index = tedIdx;
            
            // Liste görünümünü yenile
            renderSiparisListesi();
            
            // Eğer tedarikçi görünümü açıksa onu da yenile
            if (window.currentSiparisView === 'supplier') {
                renderTedarikciListesi();
            }
        }
    };

    // Tedarikçi Listesi Render
    window.renderTedarikciListesi = function() {
        var container = document.getElementById('viewSupplierContainer');
        var data = window.globalSiparisData || [];
        if (data.length === 0) {
            container.innerHTML = '<div class="text-center text-muted p-4">Veri bulunamadı.</div>';
            return;
        }

        // Gruplama (seçili tedarikçiye göre)
        var groups = {};
        data.forEach(function(item) {
            // Seçili tedarikçi varsa onu kullan, yoksa varsayılan tedarikçiyi
            var secili = item.secili_tedarikci || item.tedarikci;
            var tedarikciAdi = (secili && secili.adi && secili.adi !== '-') ? secili.adi : 'Tedarikçisi Belirsiz';
            if (!groups[tedarikciAdi]) {
                groups[tedarikciAdi] = { 
                    items: [], 
                    totalCost: 0, 
                    currency: (secili ? secili.para_birimi : '') 
                };
            }
            groups[tedarikciAdi].items.push(item);
            if (secili && secili.fiyat > 0) {
                groups[tedarikciAdi].totalCost += (parseFloat(item.net_siparis) * parseFloat(secili.fiyat));
            }
        });

        var html = '<div class="row">';
        
        Object.keys(groups).sort().forEach(function(tedarikciName) {
            var group = groups[tedarikciName];
            var isUnknown = tedarikciName === 'Tedarikçisi Belirsiz';
            var totalStr = group.totalCost > 0 ? group.totalCost.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + group.currency : '-';
            
            html += `
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden; font-family: 'Open Sans', sans-serif; transition: transform 0.2s;">
                        <div class="card-header bg-white border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center" style="border-left: 5px solid ${isUnknown ? '#b0b3b8' : '#10b981'}; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                            <div>
                                <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 15px; font-family: 'Poppins', sans-serif;">${tedarikciName}</h6>
                                <div class="text-muted mt-1" style="font-size: 11px;">${group.items.length} Kalem Sipariş</div>
                            </div>
                            ${!isUnknown ? `
                            <div class="text-right">
                                <div class="font-weight-bold text-success" style="font-size: 14px; font-family: 'Open Sans', sans-serif; letter-spacing: -0.5px;">${totalStr}</div>
                                <div class="text-muted" style="font-size: 10px; font-weight: 500;">Tahmini Tutar</div>
                            </div>` : ''}
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead class="bg-light">
                                    <tr style="font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <th class="pl-4 py-2 border-0 font-weight-bold">Ürün Detayı</th>
                                        <th class="py-2 border-0 text-right font-weight-bold">Miktar</th>
                                        <th class="py-2 border-0 text-right pr-4 font-weight-bold">Tutar</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;
            
            group.items.forEach(function(item) {
                var itemTotal = (item.tedarikci && item.tedarikci.fiyat > 0) ? (parseFloat(item.net_siparis) * parseFloat(item.tedarikci.fiyat)) : 0;
                var itemTotalStr = itemTotal > 0 ? itemTotal.toLocaleString('tr-TR', {maximumFractionDigits: 2}) + ' ' + group.currency : '-';
                
                html += `
                    <tr>
                        <td class="pl-4 py-1" style="vertical-align: middle;">
                            <div class="font-weight-600 text-dark" style="font-size: 12px;">${item.urun_ismi}</div>
                            <div class="text-muted" style="font-size: 11px;">${item.malzeme_adi}</div>
                        </td>
                        <td class="text-right py-1" style="vertical-align: middle;">
                            <span class="font-weight-bold text-danger" style="font-size: 13px; font-family: 'Open Sans', sans-serif;">${parseFloat(item.net_siparis).toLocaleString('tr-TR')}</span> 
                            <span class="small text-muted ml-1" style="font-size: 10px;">${item.birim}</span>
                        </td>
                        <td class="text-right py-1 pr-4" style="vertical-align: middle;">
                            <div style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #444; font-weight: 600;">${itemTotalStr}</div>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    };
    
    // Filtreleme fonksiyonu
    window.filterSiparisListesi = function(val) {
        val = val.toLowerCase().trim();
        var rows = document.querySelectorAll('#siparisListesiTable tbody tr');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(val) > -1 ? '' : 'none';
        });
    };
    
    // Excel Export Fonksiyonu
    window.exportSiparisListesi = function() {
        if (typeof XLSX === 'undefined') {
            alert('Excel oluşturucu yüklenemedi. Lütfen sayfayı yenileyin veya internet bağlantınızı kontrol edin.');
            return;
        }

        var data = window.globalSiparisData || [];
        if(data.length === 0) { 
            alert('İndirilecek veri yok.'); 
            return; 
        }
        
        try {
            // Veriyi düzgün formatta hazırla
            var exportData = data.map(function(item) {
                return {
                    'Ürün Adı': item.urun_ismi,
                    'Ürün Kodu': item.urun_kodu,
                    'İhtiyaç Türü': item.tip,
                    'Malzeme/Hammadde': item.malzeme_adi,
                    'Birim İhtiyaç': parseFloat(item.birim_ihtiyac),
                    'Toplam İhtiyaç': parseFloat(item.toplam_ihtiyac),
                    'Stok': parseFloat(item.stok),
                    'Yoldaki': parseFloat(item.yoldaki),
                    'Birim': item.birim,
                    'Net Sipariş Miktarı': parseFloat(item.net_siparis),
                    'En Uygun Tedarikçi': (item.tedarikci && item.tedarikci.adi !== '-' ? item.tedarikci.adi : ''),
                    'Tedarikçi Fiyatı': (item.tedarikci && item.tedarikci.fiyat > 0 ? parseFloat(item.tedarikci.fiyat) : ''),
                    'Tedarikçi Para Birimi': (item.tedarikci ? item.tedarikci.para_birimi : '')
                };
            });
            
            var ws = XLSX.utils.json_to_sheet(exportData);
            
            // Sütun genişliklerini ayarla
            var wscols = [
                {wch: 30}, // Ürün Adı
                {wch: 15}, // Kodu
                {wch: 15}, // Tip
                {wch: 30}, // Malzeme
                {wch: 10}, // Birim İht
                {wch: 12}, // Toplam
                {wch: 10}, // Stok
                {wch: 10}, // Yoldaki
                {wch: 8},  // Birim
                {wch: 15}, // Net Sipariş
                {wch: 25}, // Tedarikçi
                {wch: 10}, // Fiyat
                {wch: 5}   // PB
            ];
            ws['!cols'] = wscols;

            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Siparis Listesi");
            
            var date = new Date().toISOString().slice(0,10);
            XLSX.writeFile(wb, "Siparis_Ihtiyac_Listesi_" + date + ".xlsx");
        } catch (e) {
            console.error('Export Error:', e);
            alert('Excel oluşturulurken hata: ' + e.message);
        }
    };
    
    // Chip Toggle Fonksiyonu
    function toggleChip(label) {
        // Tıklama event'i input change'i tetikler, biz sadece görsel class'ı yönetelim
        // Ancak label'a tıklandığında input zaten değişir. 
        // checkbox checked durumuna göre class ata
        var checkbox = label.querySelector('input[type="checkbox"]');
        setTimeout(function(){
            if(checkbox.checked) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        }, 10);
    }
    
    // Ana Tablo Export Fonksiyonu
    window.exportMainTable = function() {
        if (typeof XLSX === 'undefined') {
            alert('Excel oluşturucu yüklenemedi. Lütfen sayfayı yenileyin veya internet bağlantınızı kontrol edin.');
            return;
        }

        var table = document.getElementById('urunTable');
        if (!table) { alert('Tablo bulunamadı'); return; }

        try {
            // Tabloyu kopyala (Görünümü bozmadan manipüle etmek için)
            var clonedTable = table.cloneNode(true);
            
            // Gizli satırları temizle (Filtreleme varsa excelde görünmesin)
            var origRows = table.querySelectorAll('tbody tr');
            var clonedRows = clonedTable.querySelectorAll('tbody tr');
            
            for (var i = origRows.length - 1; i >= 0; i--) {
                if (origRows[i].style.display === 'none') {
                    if(clonedRows[i]) clonedRows[i].remove();
                }
            }
            
            // "Aksiyon Detayı" sütununu bul ve sil (Excel'de istenmiyor)
            var headers = clonedTable.querySelectorAll('thead th');
            var indicesToRemove = [];
            
            headers.forEach(function(th, index) {
                // Class veya başlık metni ile kontrol
                if (th.classList.contains('aksiyon-detay-cell') || th.innerText.indexOf('Aksiyon Detayı') > -1) {
                    indicesToRemove.push(index);
                }
            });
            
            // Büyükten küçüğe sırala ki silerken index kaymasın
            indicesToRemove.sort(function(a, b) { return b - a; });
            
            indicesToRemove.forEach(function(colIndex) {
                 // Header sil (Varsa)
                 if(clonedTable.tHead && clonedTable.tHead.rows.length > 0) {
                     clonedTable.tHead.rows[0].deleteCell(colIndex);
                 }
                 // Tüm satırlardan ilgili hücreyi sil
                 var rows = clonedTable.querySelectorAll('tbody tr');
                 rows.forEach(function(row) {
                     if(row.cells.length > colIndex) row.deleteCell(colIndex);
                 });
            });

            var wb = XLSX.utils.book_new();
            var ws = XLSX.utils.table_to_sheet(clonedTable);
            XLSX.utils.book_append_sheet(wb, ws, "Uretim Tablosu");
            
            var date = new Date().toISOString().slice(0,10);
            XLSX.writeFile(wb, "Uretim_Kokpit_Tumu_" + date + ".xlsx");
        } catch (e) {
            console.error('Export Error:', e);
            alert('Excel oluşturulurken hata: ' + e.message);
        }
    };

    // Sayfa yüklendiğinde de hesapla
    document.addEventListener('DOMContentLoaded', hesaplaUretilebilir);
    
    // Checkbox değişikliklerini dinle
    document.querySelectorAll('.bilesen-checkbox').forEach(function(chk) {
        chk.addEventListener('change', hesaplaUretilebilir);
    });
    
    // Açık input değişikliklerini dinle
    document.querySelectorAll('.acik-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            hesaplaAcikDegisikligi(this);
            gosterDegisiklikGostergesi(this);
            guncelleGlobalSiparisListesi();
        });
        input.addEventListener('input', function(e) {
            // Input değişince renk güncelle
            var val = parseFloat(this.value) || 0;
            var orijinal = parseFloat(this.getAttribute('data-orijinal')) || 0;
            
            this.classList.remove('text-danger', 'text-success', 'text-muted');
            if (val > 0) {
                this.classList.add('text-danger');
            } else {
                this.classList.add('text-success');
            }
            
            // Değişiklik göstergesi: border rengi değiştir
            if (val !== orijinal) {
                this.style.borderColor = '#9c27b0'; // Mor renk
                this.style.boxShadow = '0 0 3px rgba(156, 39, 176, 0.4)';
            } else {
                this.style.borderColor = '#ddd';
                this.style.boxShadow = 'none';
            }
        });
    });
    
    // Sıfırlama butonlarını dinle
    document.querySelectorAll('.acik-sifirla-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var container = this.closest('.d-flex');
            var input = container.querySelector('.acik-input');
            if (input) {
                var orijinal = parseFloat(input.getAttribute('data-orijinal')) || 0;
                input.value = orijinal;
                input.style.borderColor = '#ddd';
                input.style.boxShadow = 'none';
                this.style.display = 'none';
                
                // Renk güncelle
                input.classList.remove('text-danger', 'text-success', 'text-muted');
                if (orijinal > 0) {
                    input.classList.add('text-danger');
                } else {
                    input.classList.add('text-success');
                }
                
                // Hesaplamaları güncelle
                hesaplaAcikDegisikligi(input);
                guncelleGlobalSiparisListesi();
            }
        });
    });
    
    // Değişiklik göstergesi ve sıfırlama butonu göster
    function gosterDegisiklikGostergesi(inputEl) {
        var orijinal = parseFloat(inputEl.getAttribute('data-orijinal')) || 0;
        var mevcut = parseFloat(inputEl.value) || 0;
        var container = inputEl.closest('.d-flex');
        var sifirlaBtn = container ? container.querySelector('.acik-sifirla-btn') : null;
        
        if (mevcut !== orijinal) {
            inputEl.style.borderColor = '#9c27b0';
            inputEl.style.boxShadow = '0 0 3px rgba(156, 39, 176, 0.4)';
            if (sifirlaBtn) sifirlaBtn.style.display = 'inline-block';
        } else {
            inputEl.style.borderColor = '#ddd';
            inputEl.style.boxShadow = 'none';
            if (sifirlaBtn) sifirlaBtn.style.display = 'none';
        }
    }
    
    // Global sipariş listesini güncelle
    function guncelleGlobalSiparisListesi() {
        var siparisData = [];
        
        document.querySelectorAll('#urunTable tbody tr').forEach(function(row) {
            if (row.style.display === 'none') return; // Gizli satırları atla
            
            var acikKolonu = row.querySelector('.acik-kolonu');
            var acikInput = row.querySelector('.acik-input');
            if (!acikKolonu || !acikInput) return;
            
            var yeniAcik = parseFloat(acikInput.value) || 0;
            if (yeniAcik <= 0) return;
            
            var bilesenDetaylari = JSON.parse(acikKolonu.getAttribute('data-bilesen-detaylari') || '[]');
            var esansUretim = JSON.parse(acikKolonu.getAttribute('data-esans-uretim') || '{}');
            var urunIsmi = row.querySelector('td:nth-child(2)')?.textContent?.trim() || '';
            var urunKodu = row.getAttribute('data-urun-kodu') || '';
            
            // Malzeme siparişleri
            bilesenDetaylari.forEach(function(bilesen) {
                if (bilesen.tur !== 'esans' && bilesen.sozlesme_var) {
                    var birimIhtiyac = parseFloat(bilesen.gerekli_adet) || 0;
                    var toplamIhtiyac = yeniAcik * birimIhtiyac;
                    var mevcutVeYoldaki = (parseFloat(bilesen.mevcut_stok) || 0) + (parseFloat(bilesen.yoldaki_stok) || 0);
                    var siparisGereken = Math.max(0, toplamIhtiyac - mevcutVeYoldaki);
                    
                    if (siparisGereken > 0) {
                        siparisData.push({
                            urun_ismi: urunIsmi,
                            urun_kodu: urunKodu,
                            tip: 'Malzeme',
                            malzeme_adi: bilesen.isim,
                            birim_ihtiyac: birimIhtiyac,
                            toplam_ihtiyac: toplamIhtiyac,
                            stok: bilesen.mevcut_stok || 0,
                            yoldaki: bilesen.yoldaki_stok || 0,
                            birim: bilesen.birim || 'adet',
                            net_siparis: siparisGereken,
                            tedarikci: bilesen.tedarikci || null
                        });
                    }
                }
            });
            
            // Esans hammadde siparişleri
            bilesenDetaylari.forEach(function(bilesen) {
                if (bilesen.tur === 'esans' && esansUretim[bilesen.kodu]) {
                    var esansInfo = esansUretim[bilesen.kodu];
                    var formulDetaylari = esansInfo.formul_detaylari || [];
                    var birimIhtiyac = parseFloat(bilesen.gerekli_adet) || 0;
                    var brutIhtiyac = yeniAcik * birimIhtiyac;
                    var stokEsans = parseFloat(bilesen.mevcut_stok) || 0;
                    var uretimdeEsans = parseFloat(esansInfo.acik_is_emri_miktar) || 0;
                    var netEsansIhtiyac = Math.max(0, brutIhtiyac - (stokEsans + uretimdeEsans));
                    
                    if (netEsansIhtiyac > 0) {
                        formulDetaylari.forEach(function(h) {
                            var toplamGereken = netEsansIhtiyac * (parseFloat(h.recete_miktari) || 0);
                            var mevcut = parseFloat(h.mevcut_stok) || 0;
                            var bekleyen = parseFloat(h.bekleyen_siparis) || 0;
                            var netSiparis = Math.max(0, toplamGereken - mevcut - bekleyen);
                            
                            if (netSiparis > 0) {
                                siparisData.push({
                                    urun_ismi: urunIsmi,
                                    urun_kodu: urunKodu,
                                    tip: 'Esans Hammadde (' + bilesen.isim + ')',
                                    malzeme_adi: h.malzeme_ismi,
                                    birim_ihtiyac: h.recete_miktari || 0,
                                    toplam_ihtiyac: toplamGereken,
                                    stok: mevcut,
                                    yoldaki: bekleyen,
                                    birim: h.birim || '',
                                    net_siparis: netSiparis,
                                    tedarikci: h.tedarikci || null
                                });
                            }
                        });
                    }
                }
            });
        });
        
        // Global değişkeni güncelle
        window.globalSiparisData = siparisData;
    }
    
    // Açık değeri değiştiğinde tüm bağımlı hesaplamaları güncelle
    function hesaplaAcikDegisikligi(inputEl) {
        var row = inputEl.closest('tr');
        var yeniAcik = parseFloat(inputEl.value) || 0;
        var acikKolonu = inputEl.closest('.acik-kolonu');
        
        if (!acikKolonu) return;
        
        var bilesenDetaylari = JSON.parse(acikKolonu.getAttribute('data-bilesen-detaylari') || '[]');
        var esansUretim = JSON.parse(acikKolonu.getAttribute('data-esans-uretim') || '{}');
        
        // 1. Üretilebilir hücresindeki data-acik değerini güncelle
        var uretilebilirHucre = row.querySelector('.uretilebilir-hucre');
        if (uretilebilirHucre) {
            uretilebilirHucre.setAttribute('data-acik', yeniAcik);
        }
        
        // 2. Aksiyon Önerisi hücresindeki data-acik değerini güncelle
        var aksiyonHucre = row.querySelector('.aksiyon-onerisi-hucre');
        if (aksiyonHucre) {
            aksiyonHucre.setAttribute('data-acik', yeniAcik);
        }
        
        // 3. Malzeme Stok kolonlarını yeniden hesapla (Sipariş Verilmesi Gereken)
        var kolonlar = row.querySelectorAll('td');
        
        // Sipariş verilmesi gereken malzemeleri hesapla
        var siparisListesi = [];
        bilesenDetaylari.forEach(function(bilesen) {
            if (bilesen.tur !== 'esans' && bilesen.sozlesme_var) {
                var birimIhtiyac = parseFloat(bilesen.gerekli_adet) || 0;
                var toplamIhtiyac = yeniAcik * birimIhtiyac;
                var mevcutVeYoldaki = (parseFloat(bilesen.mevcut_stok) || 0) + (parseFloat(bilesen.yoldaki_stok) || 0);
                var siparisGereken = Math.max(0, toplamIhtiyac - mevcutVeYoldaki);
                
                if (siparisGereken > 0) {
                    siparisListesi.push({
                        isim: bilesen.isim,
                        miktar: siparisGereken
                    });
                }
            }
        });
        
        // Sipariş Verilmesi Gereken Malzeme (19. kolon, 0-indexed = 18)
        if (kolonlar.length > 18 && bilesenDetaylari.length > 0) {
            var malzemeSiparisKolonu = kolonlar[18];
            var html = '';
            
            siparisListesi.forEach(function(item) {
                html += '<div class="text-nowrap" style="font-size: 11px; line-height: 1.2;">' + 
                        item.isim + ': <span class="text-danger font-weight-bold">' + 
                        item.miktar.toLocaleString('tr-TR') + '</span></div>';
            });
            
            malzemeSiparisKolonu.innerHTML = siparisListesi.length > 0 ? html : '<span class="text-muted">0</span>';
        }
        
        // 4. Esans İhtiyacı kolonlarını güncelle
        // Toplam Esans İhtiyacı (20. kolon, 0-indexed = 19)
        if (kolonlar.length > 19 && bilesenDetaylari.length > 0) {
            var esansIhtiyacKolonu = kolonlar[19];
            var html = '';
            var esansBulundu = false;
            
            bilesenDetaylari.forEach(function(bilesen) {
                if (bilesen.tur === 'esans') {
                    esansBulundu = true;
                    var birim = bilesen.birim || 'ml';
                    var birimIhtiyac = parseFloat(bilesen.gerekli_adet) || 0;
                    var brutIhtiyac = yeniAcik * birimIhtiyac;
                    
                    html += '<div class="text-nowrap" style="font-size: 11px; line-height: 1.2;">' +
                            bilesen.isim + ': <span class="font-weight-bold">' +
                            brutIhtiyac.toLocaleString('tr-TR', {maximumFractionDigits: 2}) + '</span> ' + birim + '</div>';
                }
            });
            
            esansIhtiyacKolonu.innerHTML = esansBulundu ? html : '<span class="text-muted">-</span>';
        }
        
        // 5. Net Esans İhtiyacı (23. kolon, 0-indexed = 22)
        if (kolonlar.length > 22 && bilesenDetaylari.length > 0) {
            var netEsansKolonu = kolonlar[22];
            var html = '';
            var esansBulundu = false;
            
            bilesenDetaylari.forEach(function(bilesen) {
                if (bilesen.tur === 'esans') {
                    esansBulundu = true;
                    var birim = bilesen.birim || 'ml';
                    var birimIhtiyac = parseFloat(bilesen.gerekli_adet) || 0;
                    var brutIhtiyac = yeniAcik * birimIhtiyac;
                    var stokEsans = parseFloat(bilesen.mevcut_stok) || 0;
                    
                    // Üretimdeki esans miktarı
                    var uretimdeEsans = 0;
                    if (esansUretim[bilesen.kodu]) {
                        uretimdeEsans = parseFloat(esansUretim[bilesen.kodu].acik_is_emri_miktar) || 0;
                    }
                    
                    var netIhtiyac = Math.max(0, brutIhtiyac - (stokEsans + uretimdeEsans));
                    
                    if (netIhtiyac > 0) {
                        html += '<div class="text-nowrap" style="font-size: 11px; line-height: 1.2;">' +
                                bilesen.isim + ': <span class="text-danger font-weight-bold">' +
                                netIhtiyac.toLocaleString('tr-TR', {maximumFractionDigits: 2}) + '</span> ' + birim + '</div>';
                    }
                }
            });
            
            netEsansKolonu.innerHTML = esansBulundu && html ? html : '<span class="text-muted">0</span>';
        }
        
        // 6. Esans Hammadde Kolonlarını Güncelle
        bilesenDetaylari.forEach(function(bilesen) {
            if (bilesen.tur === 'esans' && esansUretim[bilesen.kodu]) {
                var esansInfo = esansUretim[bilesen.kodu];
                var formulDetaylari = esansInfo.formul_detaylari || [];
                var birimIhtiyac = parseFloat(bilesen.gerekli_adet) || 0;
                var brutIhtiyac = yeniAcik * birimIhtiyac;
                var stokEsans = parseFloat(bilesen.mevcut_stok) || 0;
                var uretimdeEsans = parseFloat(esansInfo.acik_is_emri_miktar) || 0;
                var netEsansIhtiyac = Math.max(0, brutIhtiyac - (stokEsans + uretimdeEsans));
                
                // 6a. Hemen Üretilebilir Esans Miktarı (24. kolon, 0-indexed = 23)
                if (kolonlar.length > 23) {
                    var hemenUretilebilirKolonu = kolonlar[23];
                    if (formulDetaylari.length > 0) {
                        var minUretilebilir = Infinity;
                        formulDetaylari.forEach(function(h) {
                            var gerekli = parseFloat(h.recete_miktari) || 0;
                            var mevcut = parseFloat(h.mevcut_stok) || 0;
                            if (gerekli > 0) {
                                minUretilebilir = Math.min(minUretilebilir, Math.floor(mevcut / gerekli));
                            }
                        });
                        var uretilebilirMiktar = (minUretilebilir === Infinity) ? 0 : minUretilebilir;
                        var birim = esansInfo.birim || 'ml';
                        hemenUretilebilirKolonu.innerHTML = '<div class="text-nowrap" style="font-size: 11px;">' +
                            bilesen.isim + ': <span class="text-success font-weight-bold">' +
                            uretilebilirMiktar.toLocaleString('tr-TR') + '</span> ' + birim + '</div>';
                    }
                }
                
                // 6b. Üretilmesi Gereken (Hammadde Bekleyen) (25. kolon, 0-indexed = 24)
                if (kolonlar.length > 24 && netEsansIhtiyac > 0) {
                    var uretilmesiGerekenKolonu = kolonlar[24];
                    var minUretilebilir = Infinity;
                    formulDetaylari.forEach(function(h) {
                        var gerekli = parseFloat(h.recete_miktari) || 0;
                        var mevcut = parseFloat(h.mevcut_stok) || 0;
                        if (gerekli > 0) {
                            minUretilebilir = Math.min(minUretilebilir, Math.floor(mevcut / gerekli));
                        }
                    });
                    var uretilebilirMiktar = (minUretilebilir === Infinity) ? 0 : minUretilebilir;
                    var hammaddeBekleyen = Math.max(0, netEsansIhtiyac - uretilebilirMiktar);
                    var birim = esansInfo.birim || 'ml';
                    
                    if (hammaddeBekleyen > 0) {
                        uretilmesiGerekenKolonu.innerHTML = '<div class="text-nowrap" style="font-size: 11px;">' +
                            bilesen.isim + ': <span class="text-warning font-weight-bold">' +
                            hammaddeBekleyen.toLocaleString('tr-TR') + '</span> ' + birim + '</div>';
                    } else {
                        uretilmesiGerekenKolonu.innerHTML = '<span class="text-muted">0</span>';
                    }
                }
                
                // 6c. Sipariş Gereken Hammaddeler (26. kolon, 0-indexed = 25)
                if (kolonlar.length > 25 && netEsansIhtiyac > 0 && formulDetaylari.length > 0) {
                    var siparisGerekenHammaddeKolonu = kolonlar[25];
                    var html = '';
                    formulDetaylari.forEach(function(h) {
                        var toplamGereken = netEsansIhtiyac * (parseFloat(h.recete_miktari) || 0);
                        var mevcut = parseFloat(h.mevcut_stok) || 0;
                        var bekleyen = parseFloat(h.bekleyen_siparis) || 0;
                        var eksik = Math.max(0, toplamGereken - mevcut - bekleyen);
                        
                        if (eksik > 0) {
                            html += '<div class="text-nowrap" style="font-size: 10px; line-height: 1.2;">' +
                                    h.malzeme_ismi + ': <span class="text-danger font-weight-bold">' +
                                    eksik.toLocaleString('tr-TR', {maximumFractionDigits: 2}) + '</span> ' + (h.birim || '') + '</div>';
                        }
                    });
                    siparisGerekenHammaddeKolonu.innerHTML = html || '<span class="text-muted">0</span>';
                }
                
                // 6d. Yoldaki Hammaddeler (27. kolon, 0-indexed = 26)
                if (kolonlar.length > 26 && formulDetaylari.length > 0) {
                    var yoldakiHammaddeKolonu = kolonlar[26];
                    var html = '';
                    formulDetaylari.forEach(function(h) {
                        var bekleyen = parseFloat(h.bekleyen_siparis) || 0;
                        if (bekleyen > 0) {
                            html += '<div class="text-nowrap" style="font-size: 10px; line-height: 1.2;">' +
                                    h.malzeme_ismi + ': <span class="text-info font-weight-bold">' +
                                    bekleyen.toLocaleString('tr-TR', {maximumFractionDigits: 2}) + '</span>' +
                                    (h.po_list ? ' <small class="text-muted">(' + h.po_list + ')</small>' : '') + '</div>';
                        }
                    });
                    yoldakiHammaddeKolonu.innerHTML = html || '<span class="text-muted">0</span>';
                }
                
                // 6e. Net Verilecek Esans Siparişi (28. kolon, 0-indexed = 27)
                if (kolonlar.length > 27 && netEsansIhtiyac > 0 && formulDetaylari.length > 0) {
                    var netSiparisKolonu = kolonlar[27];
                    var html = '';
                    formulDetaylari.forEach(function(h) {
                        var toplamGereken = netEsansIhtiyac * (parseFloat(h.recete_miktari) || 0);
                        var mevcut = parseFloat(h.mevcut_stok) || 0;
                        var eksik = Math.max(0, toplamGereken - mevcut);
                        var bekleyen = parseFloat(h.bekleyen_siparis) || 0;
                        var netSiparis = Math.max(0, eksik - bekleyen);
                        
                        if (netSiparis > 0) {
                            html += '<div class="text-nowrap" style="font-size: 10px; line-height: 1.2;">' +
                                    h.malzeme_ismi + ': <span class="text-danger font-weight-bold">' +
                                    netSiparis.toLocaleString('tr-TR', {maximumFractionDigits: 2}) + '</span> ' + (h.birim || '') + '</div>';
                        }
                    });
                    netSiparisKolonu.innerHTML = html || '<span class="text-muted">0</span>';
                }
            }
        });
        // 7. Fark% kolonunu güncelle
        var farkKolonu = row.querySelector('.fark-kolonu');
        if (farkKolonu) {
            var kritikStok = parseFloat(farkKolonu.getAttribute('data-kritik-stok')) || 0;
            if (kritikStok > 0 && yeniAcik > 0) {
                var farkYuzde = (yeniAcik / kritikStok) * 100;
                var farkDeger = farkKolonu.querySelector('.fark-deger');
                
                if (!farkDeger) {
                    farkDeger = document.createElement('span');
                    farkDeger.className = 'font-semibold fark-deger';
                    farkKolonu.innerHTML = '';
                    farkKolonu.appendChild(farkDeger);
                }
                
                farkDeger.textContent = '%' + Math.round(farkYuzde);
                farkDeger.classList.remove('text-danger', 'text-warning', 'text-success');
                
                if (farkYuzde > 50) {
                    farkDeger.classList.add('text-danger');
                } else if (farkYuzde > 0) {
                    farkDeger.classList.add('text-warning');
                } else {
                    farkDeger.classList.add('text-success');
                }
            } else if (yeniAcik <= 0) {
                var farkDeger = farkKolonu.querySelector('.fark-deger');
                if (farkDeger) {
                    farkDeger.textContent = '%0';
                    farkDeger.classList.remove('text-danger', 'text-warning');
                    farkDeger.classList.add('text-success');
                }
            }
        }
        
        // 8. Üretilebilir hesaplamasını yeniden çağır
        hesaplaUretilebilir();
        
        // 7. Dinamik üretilebilir değerini al
        var dinamikUretilebilir = 0;
        if (uretilebilirHucre) {
            dinamikUretilebilir = parseFloat(uretilebilirHucre.getAttribute('data-uretilebilir')) || 0;
        }
        
        // 8. Aksiyon Önerisi ve Detayı Güncelle
        var aksiyonOnerisiHucre = row.querySelector('.aksiyon-onerisi-hucre');
        var aksiyonDetayHucre = kolonlar[3]; // Aksiyon Detayı 4. kolon (0-indexed = 3)
        
        if (aksiyonOnerisiHucre) {
            var eksikBilesenler = JSON.parse(aksiyonOnerisiHucre.getAttribute('data-eksik-bilesenler') || '[]');
            var esansAgaciEksik = JSON.parse(aksiyonOnerisiHucre.getAttribute('data-esans-agaci-eksik') || '[]');
            var sozlesmeEksik = JSON.parse(aksiyonOnerisiHucre.getAttribute('data-sozlesme-eksik') || '[]');
            
            var aksiyon = hesaplaAksiyonOnerisi(yeniAcik, eksikBilesenler, esansAgaciEksik, sozlesmeEksik, dinamikUretilebilir, siparisListesi, bilesenDetaylari);
            
            // Badge güncelle
            var badge = aksiyonOnerisiHucre.querySelector('.aksiyon-badge');
            if (badge) {
                badge.className = 'badge badge-pill ' + aksiyon.class + ' p-2 aksiyon-badge';
                badge.style.cssText = 'font-size: 11px; line-height: 1.4; white-space: normal; text-align: left; display: block;';
                
                var icon = badge.querySelector('.aksiyon-icon');
                var mesaj = badge.querySelector('.aksiyon-mesaj');
                
                if (icon) icon.className = aksiyon.icon + ' mr-1 aksiyon-icon';
                if (mesaj) mesaj.textContent = aksiyon.mesaj;
                
                // Kategori güncelle
                badge.setAttribute('data-action-category', aksiyon.category);
                aksiyonOnerisiHucre.setAttribute('data-action-category', aksiyon.category);
            }
            
            // Aksiyon Detay güncelle
            if (aksiyonDetayHucre) {
                aksiyonDetayHucre.innerHTML = aksiyon.detay || '<span class="text-muted">-</span>';
            }
        }
        
        // 9. Satır stilini güncelle
        if (yeniAcik > 0) {
            row.classList.add('row-acil');
            // Durum badge'ini güncelle
            var durumBadge = row.querySelector('.badge-sm');
            if (durumBadge) {
                durumBadge.className = 'badge-sm badge-kotu';
                durumBadge.textContent = 'KÖTÜ';
            }
        } else {
            row.classList.remove('row-acil');
            var durumBadge = row.querySelector('.badge-sm');
            if (durumBadge) {
                durumBadge.className = 'badge-sm badge-iyi';
                durumBadge.textContent = 'İYİ';
            }
        }
    }
    
    // Aksiyon önerisi hesaplama (JavaScript versiyonu)
    function hesaplaAksiyonOnerisi(acik, eksikBilesenler, esansAgaciEksik, sozlesmeEksik, uretilebilir, siparisListesi, bilesenDetaylari) {
        // 1. VERİ EKSİKLİĞİ VARSA
        if (eksikBilesenler.length > 0 || esansAgaciEksik.length > 0) {
            var detay = [];
            var adimListesi = [];
            
            if (eksikBilesenler.length > 0) {
                detay.push('<strong class="text-danger"><i class="fas fa-exclamation-circle"></i> Eksik Bileşenler:</strong><br>' + eksikBilesenler.join(', '));
                adimListesi.push('<div style="margin-bottom: 8px;"><strong style="color: #ffc107;"><i class="fas fa-box"></i> Malzemeler İçin:</strong><br><small style="margin-left: 20px;">• Eğer malzemeyi <strong>tanımlamadıysanız</strong> → <a href="malzemeler.php" target="_blank" class="text-primary">Buradan tanımlayın</a><br>• Malzemeyi tanımladıysanız → <a href="urun_agaclari.php" target="_blank" class="text-primary">Ürün ağacına bağlayın</a></small></div>');
            }
            
            if (esansAgaciEksik.length > 0) {
                detay.push('<strong class="text-danger"><i class="fas fa-flask"></i> Formülü Olmayan Esanslar:</strong><br>' + esansAgaciEksik.join(', '));
                adimListesi.push('<div style="margin-bottom: 8px;"><strong style="color: #0078d4;"><i class="fas fa-flask"></i> Esanslar İçin:</strong><br><small style="margin-left: 20px;">• <a href="urun_agaclari.php" target="_blank" class="text-primary">Ürün Ağaçları sayfasına gidin</a><br>• <strong>Esans Ağacı</strong> sekmesinden formül oluşturun</small></div>');
            }
            
            var adimlar = '<div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 8px; border-left: 3px solid #0078d4;"><strong><i class="fas fa-info-circle text-primary"></i> Yapılması Gerekenler:</strong><br><div style="margin-top: 6px;">' + adimListesi.join('') + '</div></div>';
            
            return {
                class: 'badge-aksiyon-kritik',
                icon: 'fas fa-exclamation-triangle',
                mesaj: 'Ürün Ağacı ve Esans Formüllerini Tamamlayın',
                detay: detay.join('<br>') + adimlar,
                category: 'critical'
            };
        }
        
        // 2. AÇIK = 0 İSE
        if (acik <= 0) {
            return {
                class: 'badge-aksiyon-basarili',
                icon: 'fas fa-check-circle',
                mesaj: 'Stok Yeterli',
                detay: '<span class="text-success"><i class="fas fa-check-circle"></i> Mevcut stok ve üretimdeki miktar yeterli düzeyde.</span>',
                category: 'ok'
            };
        }
        
        // 3. SÖZLEŞME EKSİKSE
        if (acik > 0 && sozlesmeEksik.length > 0) {
            var stokYeterli = (uretilebilir >= acik);
            var detay = '<strong class="text-warning"><i class="fas fa-file-contract"></i> Sözleşmesi Olmayan Malzemeler:</strong><br>' + sozlesmeEksik.slice(0, 5).join(', ');
            if (sozlesmeEksik.length > 5) {
                detay += '<br><small class="text-muted">+' + (sozlesmeEksik.length - 5) + ' malzeme daha</small>';
            }
            
            var adimlar = '<div style="background: #fff3cd; padding: 8px; border-radius: 4px; margin-top: 6px; border-left: 3px solid #ffc107;"><strong><i class="fas fa-info-circle text-warning"></i> Yapılması Gerekenler:</strong><br><small>1️⃣ <a href="cerceve_sozlesmeler.php" target="_blank" class="text-primary">Çerçeve sözleşme sayfasına gidin</a><br>2️⃣ Yukarıdaki malzemeler için sözleşme oluşturun</small></div>';
            
            if (stokYeterli) {
                return {
                    class: 'badge-aksiyon-uyari',
                    icon: 'fas fa-file-contract',
                    mesaj: 'Gelecek Siparişler İçin Sözleşme Tamamlayın',
                    detay: detay + adimlar,
                    category: 'warning'
                };
            } else {
                return {
                    class: 'badge-aksiyon-kritik',
                    icon: 'fas fa-ban',
                    mesaj: 'Sözleşme Eksik - Önce Sözleşme Tamamlayın',
                    detay: detay + adimlar,
                    category: 'critical'
                };
            }
        }
        
        // 4. SİPARİŞ GEREKİYORSA
        if (acik > 0 && siparisListesi.length > 0) {
            var siparisHtml = siparisListesi.map(function(item) {
                return '<i class="fas fa-box text-primary"></i> ' + item.isim + ': <strong>' + item.miktar.toLocaleString('tr-TR') + '</strong> adet';
            }).join('<br>');
            
            var adimlar = '<div style="background: #e7f3ff; padding: 8px; border-radius: 4px; margin-top: 6px; border-left: 3px solid #0078d4;"><strong><i class="fas fa-info-circle text-info"></i> Yapılması Gerekenler:</strong><br><small>1️⃣ <a href="satinalma_siparisler.php" target="_blank" class="text-primary">Satınalma sipariş sayfasına gidin</a><br>2️⃣ Yukarıdaki malzemeler için sipariş oluşturun</small></div>';
            
            return {
                class: 'badge-aksiyon-bilgi',
                icon: 'fas fa-shopping-cart',
                mesaj: 'Malzeme Siparişi Verin',
                detay: siparisHtml + adimlar,
                category: 'order'
            };
        }
        
        // 5. ÜRETİLEBİLİR VARSA
        if (acik > 0 && uretilebilir > 0) {
            var uretilebilecek = Math.min(acik, uretilebilir);
            var detay = '<span class="text-success"><i class="fas fa-industry"></i> <strong>' + uretilebilecek.toLocaleString('tr-TR') + '</strong> adet hemen üretilebilir.</span>';
            
            if (uretilebilir < acik) {
                var eksik = acik - uretilebilir;
                detay += '<br><span class="text-warning"><i class="fas fa-exclamation-circle"></i> Kalan ' + eksik.toLocaleString('tr-TR') + ' adet için malzeme siparişi gerekebilir.</span>';
            }
            
            var adimlar = '<div style="background: #d4edda; padding: 8px; border-radius: 4px; margin-top: 6px; border-left: 3px solid #28a745;"><strong><i class="fas fa-info-circle text-success"></i> Yapılması Gerekenler:</strong><br><small>1️⃣ <a href="montaj_is_emirleri.php" target="_blank" class="text-primary">Montaj iş emirleri sayfasına gidin</a><br>2️⃣ Yeni iş emri oluşturun</small></div>';
            
            return {
                class: 'badge-aksiyon-bilgi',
                icon: 'fas fa-play-circle',
                mesaj: 'Üretime Başlayın (' + uretilebilecek.toLocaleString('tr-TR') + ' adet)',
                detay: detay + adimlar,
                category: 'production'
            };
        }
        
        // 6. ÜRETİLEMİYOR (Bileşen yetersiz)
        if (acik > 0 && uretilebilir <= 0) {
            var detay = '<span class="text-danger"><i class="fas fa-times-circle"></i> Yeterli bileşen stoğu yok.</span>';
            
            // Hangi bileşenler eksik?
            var eksikBilesenListesi = [];
            bilesenDetaylari.forEach(function(bilesen) {
                if (bilesen.tur !== 'esans') {
                    var stok = parseFloat(bilesen.mevcut_stok) || 0;
                    if (stok <= 0) {
                        eksikBilesenListesi.push(bilesen.isim);
                    }
                }
            });
            
            if (eksikBilesenListesi.length > 0) {
                detay += '<br><small class="text-muted">Eksik: ' + eksikBilesenListesi.slice(0, 3).join(', ') + '</small>';
            }
            
            var adimlar = '<div style="background: #f8d7da; padding: 8px; border-radius: 4px; margin-top: 6px; border-left: 3px solid #dc3545;"><strong><i class="fas fa-info-circle text-danger"></i> Yapılması Gerekenler:</strong><br><small>1️⃣ <a href="satinalma_siparisler.php" target="_blank" class="text-primary">Malzeme siparişi verin</a><br>2️⃣ Malzeme geldikten sonra üretime başlayın</small></div>';
            
            return {
                class: 'badge-aksiyon-kritik',
                icon: 'fas fa-times-circle',
                mesaj: 'Bileşen Yetersiz - Malzeme Siparişi Verin',
                detay: detay + adimlar,
                category: 'critical'
            };
        }
        
        // Varsayılan
        return {
            class: 'badge-aksiyon-basarili',
            icon: 'fas fa-check-circle',
            mesaj: 'Stok Yeterli',
            detay: '<span class="text-muted">-</span>',
            category: 'ok'
        };
    }
    
    // Üretilebilir detay modalı event handler
    // Üretilebilir detay modalı event handler
    document.querySelectorAll('.uretilebilir-detay-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var urunIsmi = this.getAttribute('data-urun-ismi');
            var urunKodu = this.getAttribute('data-urun-kodu');
            var bilesenlerData = JSON.parse(this.getAttribute('data-bilesen') || '[]');
            // Object ise array'e çevir
            var bilesenler = Array.isArray(bilesenlerData) ? bilesenlerData : Object.values(bilesenlerData);
            
            // Seçili türleri al
            var seciliTurler = [];
            document.querySelectorAll('.bilesen-checkbox:checked').forEach(function(chk) {
                seciliTurler.push(chk.value.toLowerCase());
            });
            
            // Modal içeriğini doldur
            document.getElementById('modalUrunIsmi').textContent = urunIsmi;
            document.getElementById('modalUrunKodu').textContent = '#' + urunKodu;
            
            // Bileşen tablosunu temizle
            var tbody = document.getElementById('modalBilesenTablosu');
            tbody.innerHTML = '';
            
            // En düşük üretilebilir değeri bul (SADECE SEÇİLİ TÜRLER İÇİN)
            var minUretilebilir = Infinity;
            var hesaplananBilesenVar = false;
            
            bilesenler.forEach(function(b) {
                var tur = (b.tur || '').toLowerCase();
                if (seciliTurler.includes(tur)) {
                    hesaplananBilesenVar = true;
                    if (b.uretilebilir < minUretilebilir) {
                        minUretilebilir = b.uretilebilir;
                    }
                }
            });
            
            var dinamikUretilebilir = (hesaplananBilesenVar && minUretilebilir !== Infinity) ? minUretilebilir : 0;
            document.getElementById('modalUretilebilir').textContent = dinamikUretilebilir.toLocaleString('tr-TR');
            
            // Tabloyu doldur
            bilesenler.forEach(function(b) {
                var tur = (b.tur || '').toLowerCase();
                var isSecili = seciliTurler.includes(tur);
                var isDarbogaz = isSecili && (b.uretilebilir === dinamikUretilebilir);
                var stok = b.mevcut_stok || 0;
                var durumBadge = '';
                var rowStyle = '';
                
                if (!isSecili) {
                    durumBadge = '<span class="badge badge-secondary" style="opacity:0.7;">Hesaba Katılmadı</span>';
                    rowStyle = 'style="opacity: 0.6; background: var(--gray-50); color: #999;"';
                } else if (stok <= 0) {
                    durumBadge = '<span class="badge badge-danger">Stok Yok</span>';
                } else if (isDarbogaz) {
                    durumBadge = '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Darboğaz</span>';
                    rowStyle = 'style="background: #fff3cd;"';
                } else {
                    durumBadge = '<span class="badge badge-success">Yeterli</span>';
                }
                
                var row = '<tr ' + rowStyle + '>' +
                    '<td><strong>' + (b.tur || '-').toUpperCase() + '</strong></td>' +
                    '<td>' + (b.isim || '-') + '</td>' +
                    '<td class="text-right">' + stok.toLocaleString('tr-TR') + '</td>' +
                    '<td class="text-right">' + (b.gerekli_adet || 0).toLocaleString('tr-TR') + '</td>' +
                    '<td class="text-right font-weight-bold">' + (b.uretilebilir || 0).toLocaleString('tr-TR') + '</td>' +
                    '<td class="text-center">' + durumBadge + '</td>' +
                    '</tr>';
                tbody.innerHTML += row;
            });
            
            if (bilesenler.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Bileşen bilgisi bulunamadı</td></tr>';
            }
            
            // Modalı aç - aria-hidden uyarısını önlemek için
            var modal = document.getElementById('uretilebilirDetayModal');
            modal.removeAttribute('aria-hidden');
            $(modal).modal('show');
        });
    });
    </script>
</body>
</html>

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

    // 1. Bileşen Eksiklik Kontrolü
    $products_query = "SELECT urun_kodu, urun_ismi FROM urunler ORDER BY urun_ismi";
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

            // Kritik seviye hesabı
            if ($kritik > 0) {
                $acik_kritik = $kritik - $toplam_mevcut;
                $yuzde_fark = (($kritik - $toplam_mevcut) / $kritik) * 100;
            } else {
                $acik_kritik = 0;
                $yuzde_fark = -100;
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
                         END as bilesen_stok
                         FROM urun_agaci ua
                         LEFT JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                         LEFT JOIN urunler ur ON ua.bilesen_kodu = ur.urun_kodu
                         LEFT JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
                         WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?";
            $bom_stmt = $connection->prepare($bom_query);
            $bom_stmt->bind_param('i', $row['urun_kodu']);
            $bom_stmt->execute();
            $bom_result = $bom_stmt->get_result();

            while ($bom_row = $bom_result->fetch_assoc()) {
                $gerekli = floatval($bom_row['bilesen_miktari']);
                $mevcut = floatval($bom_row['bilesen_stok']);
                $bilesen_turu_lower = strtolower($bom_row['bilesen_turu']);
                
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

$supply_chain_data = getSupplyChainData($connection);

// Calculate statistics for the compact view
$acil_count = 0; $uyari_count = 0; $iyi_count = 0; $belirsiz_count = 0;
foreach ($supply_chain_data['uretilebilir_urunler'] as $p) {
    if ($p['kritik_stok_seviyesi'] <= 0) $belirsiz_count++;
    elseif ($p['yuzde_fark'] > 50) $acil_count++;
    elseif ($p['yuzde_fark'] > 0) $uyari_count++;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827;
            --text-secondary: #6b7280;
        }
        body {
            font-family: 'Ubuntu', sans-serif;
            background: var(--bg-color);
            font-size: 12px;
        }
        .main-content { padding: 15px 20px; }

        /* Compact Stats */
        .stats-inline {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #fff;
            border: 1px solid #e5e7eb;
        }
        .stat-chip .num { font-size: 14px; font-weight: 700; }
        .stat-chip.danger { border-color: #fecaca; background: #fef2f2; }
        .stat-chip.danger .num { color: #dc2626; }
        .stat-chip.warning { border-color: #fde68a; background: #fffbeb; }
        .stat-chip.warning .num { color: #d97706; }
        .stat-chip.success { border-color: #bbf7d0; background: #f0fdf4; }
        .stat-chip.success .num { color: #16a34a; }
        .stat-chip.muted { border-color: #e5e7eb; background: #f9fafb; }
        .stat-chip.muted .num { color: #6b7280; }

        /* Compact Info */
        .info-compact {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 11px;
            color: #0369a1;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Card Styles */
        .card { border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: 600;
        }
        .table { margin: 0; font-size: 11px; }
        .table th {
            background: #f9fafb;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            padding: 8px 6px;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }
        .table td { padding: 6px; vertical-align: middle; border-color: #f3f4f6; }
        .table tbody tr:hover { background: #f9fafb; }
        .table tbody tr.row-acil { background: rgba(239,68,68,0.04); }
        .table tbody tr.row-acil:hover { background: rgba(239,68,68,0.08); }

        /* Badges */
        .badge-sm {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-acil { background: #fef2f2; color: #dc2626; }
        .badge-uyari { background: #fffbeb; color: #d97706; }
        .badge-iyi { background: #f0fdf4; color: #16a34a; }
        .badge-belirsiz { background: #f3f4f6; color: #6b7280; }

        .text-danger { color: #dc2626 !important; }
        .text-warning { color: #d97706 !important; }
        .text-success { color: #16a34a !important; }
        .text-muted { color: #9ca3af !important; }
        .font-semibold { font-weight: 600; }

        .btn-xs {
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 4px;
        }
        .btn-dark { background: var(--primary); border-color: var(--primary); }
        .btn-dark:hover { background: var(--secondary); border-color: var(--secondary); }
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
        <!-- Header Compact -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="mb-0" style="color: var(--primary); font-weight: 700;">
                    <i class="fas fa-truck-loading text-info"></i> Tedarik Zinciri Kontrol Paneli
                </h5>
                <small class="text-muted">Tüm tedarik zinciri verileri</small>
            </div>
        </div>

        <!-- Stats Inline -->
        <div class="stats-inline">
            <div class="stat-chip danger">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="num"><?php echo $acil_count; ?></span> Acil
            </div>
            <div class="stat-chip warning">
                <i class="fas fa-exclamation-circle"></i>
                <span class="num"><?php echo $uyari_count; ?></span> Uyarı
            </div>
            <div class="stat-chip success">
                <i class="fas fa-check-circle"></i>
                <span class="num"><?php echo $iyi_count; ?></span> İyi
            </div>
            <div class="stat-chip muted">
                <i class="fas fa-question-circle"></i>
                <span class="num"><?php echo $belirsiz_count; ?></span> Belirsiz
            </div>
        </div>


        <!-- Production Planning Section - TEK TABLO -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-industry"></i> Üretim Planlama</span>
                <span style="font-size: 11px; opacity: 0.8;"><?php echo count($supply_chain_data['uretilebilir_urunler']); ?> ürün</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Ürün</th>
                            <th class="text-right">Stok</th>
                            <th class="text-right">Sipariş</th>
                            <th class="text-right">Üretimde</th>
                            <th class="text-right">Toplam</th>
                            <th class="text-right">Kritik</th>
                            <th class="text-right">Açık</th>
                            <th class="text-right">Fark%</th>
                            <th class="text-right">
                                Üretilebilir 
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-xs btn-light" type="button" id="uretilebilirAyar" data-toggle="dropdown" title="Hesaplama ayarları">
                                        <i class="fas fa-cog" style="font-size: 9px;"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right p-2" style="min-width: 180px;" onclick="event.stopPropagation();">
                                        <div class="small font-weight-bold mb-2"><i class="fas fa-filter"></i> Hesaba Katılacak Türler:</div>
                                        <div class="form-check">
                                            <input class="form-check-input bilesen-checkbox" type="checkbox" value="kutu" id="chk_kutu" checked>
                                            <label class="form-check-label" for="chk_kutu">Kutu</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input bilesen-checkbox" type="checkbox" value="etiket" id="chk_etiket">
                                            <label class="form-check-label" for="chk_etiket">Etiket</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input bilesen-checkbox" type="checkbox" value="takm" id="chk_takm" checked>
                                            <label class="form-check-label" for="chk_takm">Takım</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input bilesen-checkbox" type="checkbox" value="esans" id="chk_esans" checked>
                                            <label class="form-check-label" for="chk_esans">Esans</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input bilesen-checkbox" type="checkbox" value="paket" id="chk_paket">
                                            <label class="form-check-label" for="chk_paket">Paket</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input bilesen-checkbox" type="checkbox" value="jelatin" id="chk_jelatin">
                                            <label class="form-check-label" for="chk_jelatin">Jelatin</label>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="text-right">Önerilen</th>
                            <th class="text-center">Durum</th>
                            <th>Veri Bilgisi</th>
                            <th>Sözleşme Durumu</th>
                            <th style="min-width: 220px;">Karşılanma Durumu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sira = 1; foreach ($supply_chain_data['uretilebilir_urunler'] as $p):
                            $row_class = '';
                            $badge = ['class' => 'badge-belirsiz', 'text' => '-'];
                            $yorum = '';
                            $siparis_miktari = isset($p['siparis_miktari']) ? $p['siparis_miktari'] : 0;
                            $stok = floatval($p['stok_miktari']);
                            $kritik = floatval($p['kritik_stok_seviyesi']);
                            $onerilen = floatval($p['onerilen_uretim']);
                            $uretilebilir = floatval($p['uretilebilir_miktar']);
                            
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
                            if ($kritik <= 0 && $siparis_miktari <= 0) {
                                $badge = ['class' => 'badge-iyi', 'text' => 'İYİ'];
                                $yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> Stok yeterli.</span>';
                            } elseif ($kritik <= 0 && $siparis_miktari > 0) {
                                // Kritik yok ama sipariş var
                                if ($siparis_miktari > $stok) {
                                    $eksik = $siparis_miktari - $stok;
                                    $row_class = 'row-acil';
                                    $badge = ['class' => 'badge-acil', 'text' => 'ACİL'];
                                    if ($uretilebilir >= $eksik) {
                                        $yorum = '<span class="text-danger"><i class="fas fa-shopping-cart"></i> ' . number_format($siparis_miktari, 0, ',', '.') . ' sipariş var. ' . number_format($eksik, 0, ',', '.') . ' adet üretilmeli.</span>';
                                    } else if ($uretilebilir > 0) {
                                        $kalan_eksik = $eksik - $uretilebilir;
                                        $yorum = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' . number_format($siparis_miktari, 0, ',', '.') . ' sipariş var. Max ' . number_format($uretilebilir, 0, ',', '.') . ' üretilebilir, ' . number_format($kalan_eksik, 0, ',', '.') . ' eksik kalır!</span>';
                                    } else {
                                        $yorum = '<span class="text-danger"><i class="fas fa-times-circle"></i> ' . number_format($siparis_miktari, 0, ',', '.') . ' sipariş var ama bileşen yetersiz! Malzeme siparişi verin.</span>';
                                    }
                                } else {
                                    $badge = ['class' => 'badge-iyi', 'text' => 'İYİ'];
                                    $yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> ' . number_format($siparis_miktari, 0, ',', '.') . ' sipariş stoktan karşılanabilir.</span>';
                                }
                            } elseif ($p['yuzde_fark'] > 50 || ($siparis_miktari > 0 && $p['acik'] > 0)) {
                                $row_class = 'row-acil';
                                $badge = ['class' => 'badge-acil', 'text' => 'ACİL'];
                                
                                if ($uretilebilir > 0) {
                                    $detaylar = [];
                                    
                                    // Sipariş durumu
                                    if ($siparis_miktari > 0) {
                                        if ($siparis_karsilanir) {
                                            $detaylar[] = '<span class="text-success">✓ Sipariş (' . number_format($siparis_miktari, 0, ',', '.') . ') karşılanır</span>';
                                        } else {
                                            $detaylar[] = '<span class="text-danger">✗ Sipariş (' . number_format($siparis_miktari, 0, ',', '.') . ') için ' . number_format($siparis_eksik, 0, ',', '.') . ' eksik</span>';
                                        }
                                    }
                                    
                                    // Kritik stok durumu
                                    if ($kritik > 0) {
                                        if ($kritik_karsilanir) {
                                            $detaylar[] = '<span class="text-success">✓ Kritik stok (' . number_format($kritik, 0, ',', '.') . ') karşılanır</span>';
                                        } else {
                                            $detaylar[] = '<span class="text-danger">✗ Kritik stok için ' . number_format($kritik_eksik, 0, ',', '.') . ' eksik kalır</span>';
                                        }
                                    }
                                    
                                    $yorum = '<div class="small">' . implode('<br>', $detaylar) . '</div>';
                                    
                                    if (!$siparis_karsilanir || !$kritik_karsilanir) {
                                        $yorum .= '<small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Daha fazla bileşen gerekli!</small>';
                                    }
                                } else {
                                    $yorum = '<span class="text-danger"><i class="fas fa-times-circle"></i> Bileşen yetersiz! ';
                                    if ($siparis_miktari > 0) {
                                        $yorum .= number_format($siparis_miktari, 0, ',', '.') . ' sipariş ';
                                    }
                                    if ($kritik > 0) {
                                        $yorum .= '+ kritik stok için ';
                                    }
                                    $yorum .= 'malzeme siparişi verin.</span>';
                                }
                            } elseif ($p['yuzde_fark'] > 0) {
                                $badge = ['class' => 'badge-uyari', 'text' => 'UYARI'];
                                if ($uretilebilir > 0) {
                                    $detaylar = [];
                                    
                                    if ($siparis_miktari > 0) {
                                        if ($siparis_karsilanir) {
                                            $detaylar[] = '<span class="text-success">✓ Sipariş (' . number_format($siparis_miktari, 0, ',', '.') . ') karşılanır</span>';
                                        } else {
                                            $detaylar[] = '<span class="text-warning">! Sipariş için ' . number_format($siparis_eksik, 0, ',', '.') . ' eksik</span>';
                                        }
                                    }
                                    
                                    if ($kritik > 0) {
                                        if ($kritik_karsilanir) {
                                            $detaylar[] = '<span class="text-success">✓ Kritik stok karşılanır</span>';
                                        } else {
                                            $detaylar[] = '<span class="text-warning">! Kritik için ' . number_format($kritik_eksik, 0, ',', '.') . ' eksik</span>';
                                        }
                                    }
                                    
                                    $yorum = '<div class="small">' . implode('<br>', $detaylar) . '</div>';
                                } else {
                                    $yorum = '<span class="text-warning"><i class="fas fa-box-open"></i> Bileşen stoku kontrol edilmeli.</span>';
                                }
                            } else {
                                $badge = ['class' => 'badge-iyi', 'text' => 'İYİ'];
                                $mesajlar = [];
                                
                                if ($siparis_miktari > 0) {
                                    $mesajlar[] = 'Sipariş (' . number_format($siparis_miktari, 0, ',', '.') . ') karşılanır';
                                }
                                if ($kritik > 0) {
                                    $mesajlar[] = 'Stok yeterli';
                                }
                                
                                if (!empty($mesajlar)) {
                                    $yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> ' . implode(', ', $mesajlar) . '</span>';
                                } else {
                                    $yorum = '<span class="text-success"><i class="fas fa-check-circle"></i> Stok yeterli.</span>';
                                }
                            }
                            
                            // Üretimde varsa bilgi ekle
                            if ($p['uretimde_miktar'] > 0) {
                                $yorum .= ' <small class="text-info d-block mt-1"><i class="fas fa-cog fa-spin"></i> Üretimde: ' . number_format($p['uretimde_miktar'], 0, ',', '.') . '</small>';
                            }
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td class="text-center text-muted"><?php echo $sira++; ?></td>
                            <td>
                                <span class="font-semibold"><?php echo htmlspecialchars($p['urun_ismi']); ?></span>
                                <small class="text-muted ml-1">#<?php echo $p['urun_kodu']; ?></small>
                            </td>
                            <td class="text-right"><?php echo number_format($p['stok_miktari'], 0, ',', '.'); ?></td>
                            <td class="text-right">
                                <?php if ($siparis_miktari > 0): ?>
                                    <span class="font-semibold" style="color: #8b5cf6;"><i class="fas fa-shopping-cart" style="font-size: 9px;"></i> <?php echo number_format($siparis_miktari, 0, ',', '.'); ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($p['uretimde_miktar'] > 0): ?>
                                    <span class="text-info font-semibold"><?php echo number_format($p['uretimde_miktar'], 0, ',', '.'); ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right font-semibold"><?php echo number_format($p['toplam_mevcut'], 0, ',', '.'); ?></td>
                            <td class="text-right"><?php echo number_format($p['kritik_stok_seviyesi'], 0, ',', '.'); ?></td>
                            <td class="text-right">
                                <?php if ($p['kritik_stok_seviyesi'] > 0 || $siparis_miktari > 0): ?>
                                    <?php if ($p['acik'] > 0): ?>
                                        <span class="text-danger font-semibold"><?php echo number_format($p['acik'], 0, ',', '.'); ?></span>
                                    <?php else: ?>
                                        <span class="text-success">+<?php echo number_format(abs($p['acik']), 0, ',', '.'); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($p['kritik_stok_seviyesi'] > 0): ?>
                                    <?php $gosterilecek_fark = max(0, $p['yuzde_fark']); ?>
                                    <span class="font-semibold <?php echo $gosterilecek_fark > 50 ? 'text-danger' : ($gosterilecek_fark > 0 ? 'text-warning' : 'text-success'); ?>">
                                        %<?php echo number_format($gosterilecek_fark, 0); ?>
                                    </span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right uretilebilir-hucre" 
                                data-bilesen-uretilebilir='<?php echo json_encode($p['bilesen_uretilebilir']); ?>'
                                data-acik="<?php echo $p['acik']; ?>">
                                <?php if ($p['uretilebilir_miktar'] > 0): ?>
                                    <span class="text-success font-semibold uretilebilir-deger"><?php echo number_format($p['uretilebilir_miktar'], 0, ',', '.'); ?></span>
                                <?php else: ?>
                                    <span class="text-muted uretilebilir-deger">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($p['onerilen_uretim'] > 0): ?>
                                    <span class="font-semibold" style="color: var(--primary);"><?php echo number_format($p['onerilen_uretim'], 0, ',', '.'); ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-center"><span class="badge-sm <?php echo $badge['class']; ?>"><?php echo $badge['text']; ?></span></td>
                            <td>
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
                            </td>
                            <td>
                                <?php 
                                $sozlesme_eksik = isset($p['sozlesme_eksik_malzemeler']) ? $p['sozlesme_eksik_malzemeler'] : [];
                                
                                if (empty($sozlesme_eksik)): ?>
                                    <span class="text-success"><i class="fas fa-file-contract"></i> Tanımlı malzemelerin sözleşmesi tam</span>
                                <?php else: ?>
                                    <span class="text-warning" style="font-size: 10px;"><i class="fas fa-exclamation-triangle"></i> Sözleşme yok: <?php echo implode(', ', array_slice($sozlesme_eksik, 0, 3)); ?><?php if (count($sozlesme_eksik) > 3) echo ' +' . (count($sozlesme_eksik) - 3) . ' diğer'; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="yorum-cell"
                                data-stok="<?php echo $stok; ?>"
                                data-siparis="<?php echo $siparis_miktari; ?>"
                                data-kritik="<?php echo $kritik; ?>"
                                data-acik="<?php echo $p['acik']; ?>"
                                data-yuzde-fark="<?php echo $p['yuzde_fark']; ?>"
                                data-uretimde="<?php echo $p['uretimde_miktar']; ?>">
                                <?php echo $yorum; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($supply_chain_data['uretilebilir_urunler'])): ?>
                        <tr><td colspan="15" class="text-center py-4 text-muted">Henüz ürün kaydı bulunmuyor.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
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
            }
            
            // Yorum kolonunu güncelle (satırın son td'si)
            var row = hucre.closest('tr');
            var yorumTd = row.querySelector('.yorum-cell');
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
                
                if (kritik <= 0 && siparis <= 0) {
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
                    var detaylar = [];
                    
                    if (siparis > 0) {
                        if (siparisKarsilanir) {
                            detaylar.push('<span class="text-success">✓ Sipariş (' + siparis.toLocaleString('tr-TR') + ') karşılanır</span>');
                        } else {
                            var siparisEksik = Math.max(0, siparis - uretimSonrasiStok);
                            detaylar.push('<span class="text-danger">✗ Sipariş (' + siparis.toLocaleString('tr-TR') + ') için ' + siparisEksik.toLocaleString('tr-TR') + ' eksik</span>');
                        }
                    }
                    
                    if (kritik > 0) {
                        if (kritikKarsilanir) {
                            detaylar.push('<span class="text-success">✓ Kritik stok karşılanır</span>');
                        } else {
                            var kritikEksik = Math.max(0, kritik - Math.max(0, siparisSonrasiStok));
                            detaylar.push('<span class="text-danger">✗ Kritik için ' + kritikEksik.toLocaleString('tr-TR') + ' eksik</span>');
                        }
                    }
                    
                    if (detaylar.length > 0) {
                        yorum = '<div class="small">' + detaylar.join('<br>') + '</div>';
                    }
                    
                    if (!siparisKarsilanir || !kritikKarsilanir) {
                        yorum += '<small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Daha fazla bileşen gerekli!</small>';
                    }
                } else {
                    yorum = '<span class="text-danger"><i class="fas fa-times-circle"></i> Bileşen yetersiz! ';
                    if (siparis > 0) {
                        yorum += siparis.toLocaleString('tr-TR') + ' sipariş ';
                    }
                    if (kritik > 0) {
                        yorum += '+ kritik stok için ';
                    }
                    yorum += 'malzeme siparişi verin.</span>';
                }
                
                // Üretimde varsa ekle
                if (uretimde > 0) {
                    yorum += ' <small class="text-info d-block mt-1"><i class="fas fa-cog fa-spin"></i> Üretimde: ' + uretimde.toLocaleString('tr-TR') + '</small>';
                }
                
                yorumTd.innerHTML = yorum;
            }
        });
    }
    
    // Checkbox değişikliklerini dinle
    document.querySelectorAll('.bilesen-checkbox').forEach(function(chk) {
        chk.addEventListener('change', hesaplaUretilebilir);
    });
    
    // Sayfa yüklendiğinde de hesapla
    document.addEventListener('DOMContentLoaded', hesaplaUretilebilir);
    </script>
</body>
</html>

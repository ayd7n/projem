<?php
header('Content-Type: application/json');

include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

if (isset($_GET['action']) && isset($_GET['urun_kodu'])) {
    $action = $_GET['action'];
    $urun_kodu = (int) $_GET['urun_kodu'];

    if ($action == 'get_product_card') {
        if (!yetkisi_var('page:view:urunler')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürün görüntüleme yetkiniz yok.']);
            exit;
        }

        try {
            $can_view_cost = yetkisi_var('action:urunler:view_cost');

            // 1. Ürün Bilgilerini Getir
            // First check if the view exists
            $view_check = $connection->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'v_urun_maliyetleri'");
            $view_exists = ($view_check && $view_check->num_rows > 0);

            if ($view_exists && $can_view_cost) {
                // Use the view for cost information if it exists and user has permission
                $product_query = "SELECT u.*, vum.teorik_maliyet
                                FROM urunler u
                                LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu
                                WHERE u.urun_kodu = ?";
            } else {
                // Just get basic product info if view doesn't exist or user doesn't have cost permission
                $product_query = "SELECT u.* FROM urunler u WHERE u.urun_kodu = ?";
            }

            $stmt = $connection->prepare($product_query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $connection->error);
            }

            $stmt->bind_param('i', $urun_kodu);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $product_result = $stmt->get_result();
            if (!$product_result) {
                throw new Exception("Get result failed: " . $stmt->error);
            }

            if ($product_result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Ürün bulunamadı.']);
                exit;
            }

            $product = $product_result->fetch_assoc();
            $stmt->close();

            // If user doesn't have cost permission, ensure teorik_maliyet is not available
            if (!$can_view_cost) {
                unset($product['teorik_maliyet']);
            }

            // 2. Ürün Fotoğraflarını Getir
            $photos_query = "SELECT * FROM urun_fotograflari
                           WHERE urun_kodu = ?
                           ORDER BY sira_no ASC, ana_fotograf DESC";
            $stmt = $connection->prepare($photos_query);
            if (!$stmt) {
                throw new Exception("Photos query prepare failed: " . $connection->error);
            }
            $stmt->bind_param('i', $urun_kodu);
            if (!$stmt->execute()) {
                throw new Exception("Photos query execute failed: " . $stmt->error);
            }
            $photos_result = $stmt->get_result();
            if (!$photos_result) {
                throw new Exception("Photos query get result failed: " . $stmt->error);
            }
            $photos = [];
            while ($row = $photos_result->fetch_assoc()) {
                $photos[] = $row;
            }
            $stmt->close();

            // 3. Stok Hareketlerini Getir
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;

            // Toplam stok hareketi sayısı
            $count_query = "SELECT COUNT(*) as total FROM stok_hareket_kayitlari
                          WHERE stok_turu = 'urun' AND kod = ?";
            $stmt = $connection->prepare($count_query);
            if (!$stmt) {
                throw new Exception("Count query prepare failed: " . $connection->error);
            }
            $stmt->bind_param('i', $urun_kodu);
            if (!$stmt->execute()) {
                throw new Exception("Count query execute failed: " . $stmt->error);
            }
            $count_result = $stmt->get_result();
            if (!$count_result) {
                throw new Exception("Count query get result failed: " . $stmt->error);
            }
            $total_movements = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_movements / $limit);
            $stmt->close();

            // Stok hareketleri
            $movements_query = "SELECT * FROM stok_hareket_kayitlari
                              WHERE stok_turu = 'urun' AND kod = ?
                              ORDER BY tarih DESC, hareket_id DESC
                              LIMIT ? OFFSET ?";
            $stmt = $connection->prepare($movements_query);
            if (!$stmt) {
                throw new Exception("Movements query prepare failed: " . $connection->error);
            }
            $stmt->bind_param('iii', $urun_kodu, $limit, $offset);
            if (!$stmt->execute()) {
                throw new Exception("Movements query execute failed: " . $stmt->error);
            }
            $movements_result = $stmt->get_result();
            if (!$movements_result) {
                throw new Exception("Movements query get result failed: " . $stmt->error);
            }
            $movements = [];
            while ($row = $movements_result->fetch_assoc()) {
                $movements[] = $row;
            }
            $stmt->close();

            // 4. Ürün Ağacı Bileşenlerini Getir
            $bom_query = "SELECT ua.*,
                         ua.bilesen_ismi,
                         ua.bilesenin_malzeme_turu as bilesen_turu,
                         CASE
                            WHEN m.malzeme_kodu IS NOT NULL THEN m.birim
                            WHEN u.urun_kodu IS NOT NULL THEN u.birim
                            WHEN e.esans_kodu IS NOT NULL THEN e.birim
                            ELSE ''
                         END as bilesen_birim,
                         CASE
                            WHEN m.malzeme_kodu IS NOT NULL THEN m.stok_miktari
                            WHEN u.urun_kodu IS NOT NULL THEN u.stok_miktari
                            WHEN e.esans_kodu IS NOT NULL THEN e.stok_miktari
                            ELSE 0
                         END as bilesen_stok
                         FROM urun_agaci ua
                         LEFT JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                         LEFT JOIN urunler u ON ua.bilesen_kodu = u.urun_kodu
                         LEFT JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
                         WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?
                         ORDER BY ua.bilesen_kodu";
            $stmt = $connection->prepare($bom_query);
            if (!$stmt) {
                throw new Exception("BOM query prepare failed: " . $connection->error);
            }
            $stmt->bind_param('i', $urun_kodu);
            if (!$stmt->execute()) {
                throw new Exception("BOM query execute failed: " . $stmt->error);
            }
            $bom_result = $stmt->get_result();
            if (!$bom_result) {
                throw new Exception("BOM query get result failed: " . $stmt->error);
            }
            $bom_components = [];
            while ($row = $bom_result->fetch_assoc()) {
                // Check for planned essence production
                if ($row['bilesen_turu'] === 'esans') {
                    // Calculate total planned quantity from active work orders
                    $esans_query = "SELECT SUM(planlanan_miktar) as total_planned 
                                  FROM esans_is_emirleri 
                                  WHERE esans_kodu = ? 
                                  AND durum IN ('olusturuldu', 'uretimde')";

                    // Create a new statement for the inner query
                    $stmt_esans = $connection->prepare($esans_query);
                    if ($stmt_esans) {
                        $stmt_esans->bind_param('s', $row['bilesen_kodu']);
                        $stmt_esans->execute();
                        $res_esans = $stmt_esans->get_result();
                        $row['planlanan_uretim'] = $res_esans->fetch_assoc()['total_planned'] ?? 0;
                        $stmt_esans->close();
                    } else {
                        $row['planlanan_uretim'] = 0;
                    }
                } else {
                    $row['planlanan_uretim'] = 0;
                }
                $bom_components[] = $row;
            }
            $stmt->close();

            // 5. Sipariş Bilgilerini Getir
            $orders_query = "SELECT sk.*, s.tarih as siparis_tarihi, s.durum as siparis_durum,
                           m.musteri_adi, m.musteri_id
                           FROM siparis_kalemleri sk
                           INNER JOIN siparisler s ON sk.siparis_id = s.siparis_id
                           INNER JOIN musteriler m ON s.musteri_id = m.musteri_id
                           WHERE sk.urun_kodu = ?
                           ORDER BY s.tarih DESC
                           LIMIT 50";
            $stmt = $connection->prepare($orders_query);
            if (!$stmt) {
                throw new Exception("Orders query prepare failed: " . $connection->error);
            }
            $stmt->bind_param('i', $urun_kodu);
            if (!$stmt->execute()) {
                throw new Exception("Orders query execute failed: " . $stmt->error);
            }
            $orders_result = $stmt->get_result();
            if (!$orders_result) {
                throw new Exception("Orders query get result failed: " . $stmt->error);
            }
            $orders = [];
            $total_ordered = 0;
            while ($row = $orders_result->fetch_assoc()) {
                $orders[] = $row;
                // Use 'adet' column which appears to be the correct name based on data returned
                $total_ordered += $row['adet'] ?? $row['miktar'] ?? 0;  // Try 'adet' first, then 'miktar', then default to 0
            }
            $stmt->close();

            // Sipariş özeti
            // Check if miktar column exists first
            $column_check = $connection->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'siparis_kalemleri' AND COLUMN_NAME = 'miktar'");
            if ($column_check && $column_check->num_rows > 0) {
                // Use the miktar column if it exists
                $order_summary_query = "SELECT
                                       COUNT(DISTINCT sk.siparis_id) as toplam_siparis,
                                       SUM(sk.miktar) as toplam_miktar,
                                       COUNT(DISTINCT CASE WHEN s.durum = 'onaylandi' THEN sk.siparis_id END) as onaylanan_siparis,
                                       COUNT(DISTINCT CASE WHEN s.durum = 'hazirlaniyor' THEN sk.siparis_id END) as hazirlanan_siparis,
                                       COUNT(DISTINCT CASE WHEN s.durum = 'tamamlandi' THEN sk.siparis_id END) as tamamlanan_siparis
                                       FROM siparis_kalemleri sk
                                       INNER JOIN siparisler s ON sk.siparis_id = s.siparis_id
                                       WHERE sk.urun_kodu = ?";
            } else {
                // Check for alternative column names (like adet, miktarlar, etc.)
                $column_check2 = $connection->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'siparis_kalemleri' AND COLUMN_NAME IN ('adet', 'miktarlar', 'quantity')");
                if ($column_check2 && $column_check2->num_rows > 0) {
                    $alt_column = $column_check2->fetch_assoc()['COLUMN_NAME'];
                    $order_summary_query = "SELECT
                                           COUNT(DISTINCT sk.siparis_id) as toplam_siparis,
                                           SUM(sk.`{$alt_column}`) as toplam_miktar,
                                           COUNT(DISTINCT CASE WHEN s.durum = 'onaylandi' THEN sk.siparis_id END) as onaylanan_siparis,
                                           COUNT(DISTINCT CASE WHEN s.durum = 'hazirlaniyor' THEN sk.siparis_id END) as hazirlanan_siparis,
                                           COUNT(DISTINCT CASE WHEN s.durum = 'tamamlandi' THEN sk.siparis_id END) as tamamlanan_siparis
                                           FROM siparis_kalemleri sk
                                           INNER JOIN siparisler s ON sk.siparis_id = s.siparis_id
                                           WHERE sk.urun_kodu = ?";
                } else {
                    // If no quantity column is found, create query without SUM
                    $order_summary_query = "SELECT
                                           COUNT(DISTINCT sk.siparis_id) as toplam_siparis,
                                           0 as toplam_miktar,  -- Default to 0 if no quantity column
                                           COUNT(DISTINCT CASE WHEN s.durum = 'onaylandi' THEN sk.siparis_id END) as onaylanan_siparis,
                                           COUNT(DISTINCT CASE WHEN s.durum = 'hazirlaniyor' THEN sk.siparis_id END) as hazirlanan_siparis,
                                           COUNT(DISTINCT CASE WHEN s.durum = 'tamamlandi' THEN sk.siparis_id END) as tamamlanan_siparis
                                           FROM siparis_kalemleri sk
                                           INNER JOIN siparisler s ON sk.siparis_id = s.siparis_id
                                           WHERE sk.urun_kodu = ?";
                }
            }
            $stmt = $connection->prepare($order_summary_query);
            if (!$stmt) {
                throw new Exception("Order summary query prepare failed: " . $connection->error);
            }
            $stmt->bind_param('i', $urun_kodu);
            if (!$stmt->execute()) {
                throw new Exception("Order summary query execute failed: " . $stmt->error);
            }
            $summary_result = $stmt->get_result();
            if (!$summary_result) {
                throw new Exception("Order summary query get result failed: " . $stmt->error);
            }
            $order_summary = $summary_result->fetch_assoc();
            $stmt->close();

            // 6. Üretimdeki Miktarı Getir (Yeni Eklendi)
            // SUM(planlanan_miktar) yerine COUNT(*) istendiği için COUNT kullanıyoruz, 
            // ancak kullanıcı "kaç adet ürün var" dediği için iş emri sayısı mı yoksa üretilecek ürün miktarı mı kastettiği önemli.
            // "Durum alanındaki değeri 'Uretimde' olan kaç adet ürün var" ifadesi genellikle miktar belirtir.
            // Ancak "kaç adet ürün var" derken "kaç tane iş emri var" da kastedilmiş olabilir.
            // Genellikle stok analizinde miktar daha önemlidir. 
            // Kullanıcı "kaç adet ürün var" dediği için toplam planlanan miktarı alalım.
            // DÜZELTME: Kullanıcı "kaç adet ürün var" dedi, bu genellikle miktar (quantity) anlamına gelir.
            // Fakat "Uretimde olanlar demek" dediği için iş emri sayısını da kastediyor olabilir.
            // En garantisi hem iş emri sayısını hem de toplam miktarı almak.
            // Ama basitlik adına toplam miktarı (planlanan - tamamlanan) almak daha mantıklı stok analizi için.
            // Fakat talep "kaç adet ürün var" şeklinde. 
            // Ben toplam miktarı (SUM(planlanan_miktar - tamamlanan_miktar)) alacağım çünkü stok analizi yapıyoruz.
            // Ve ayrıca iş emri sayısını da alalım.

            $production_query = "SELECT 
                                COUNT(*) as is_emri_sayisi, 
                                COALESCE(SUM(planlanan_miktar), 0) as uretimdeki_toplam_planlanan_miktar
                                FROM montaj_is_emirleri 
                                WHERE urun_kodu = ? AND durum = 'uretimde'";

            $stmt = $connection->prepare($production_query);
            if (!$stmt) {
                throw new Exception("Production query prepare failed: " . $connection->error);
            }
            $stmt->bind_param('i', $urun_kodu);
            if (!$stmt->execute()) {
                throw new Exception("Production query execute failed: " . $stmt->error);
            }
            $production_result = $stmt->get_result();
            $production_data = $production_result->fetch_assoc();
            $stmt->close();

            // 7. Durum Özeti Hesaplama (Enhanced)
            $durum_ozeti = [];

            // Stok Durumu - Enhanced
            $stok_miktari = floatval($product['stok_miktari']);
            $kritik_seviye = floatval($product['kritik_stok_seviyesi']);
            $stok_yuzde = $kritik_seviye > 0 ? ($stok_miktari / $kritik_seviye) * 100 : 100;

            $durum_ozeti['stok_durumu'] = [
                'deger' => $stok_miktari,
                'kritik_seviye' => $kritik_seviye,
                'birim' => $product['birim'],
                'yuzde' => round($stok_yuzde, 2),
                'durum' => $stok_miktari >= $kritik_seviye ? 'success' : ($stok_miktari >= $kritik_seviye * 0.5 ? 'warning' : 'danger'),
                'durum_text' => $stok_miktari >= $kritik_seviye ? 'Yeterli' : ($stok_miktari >= $kritik_seviye * 0.5 ? 'Kritik seviyeye yakın' : 'Kritik seviyenin altında')
            ];

            // Stok Açığı - Enhanced
            $stok_acigi = max(0, $kritik_seviye - $stok_miktari);
            $yuzde_eksik = $kritik_seviye > 0 ? ($stok_acigi / $kritik_seviye) * 100 : 0;

            $durum_ozeti['stok_acigi'] = [
                'deger' => $stok_acigi,
                'kritik_seviye' => $kritik_seviye,
                'birim' => $product['birim'],
                'yuzde_eksik' => round($yuzde_eksik, 2),
                'durum' => $stok_acigi > 0 ? ($stok_acigi > $kritik_seviye * 0.5 ? 'danger' : 'warning') : 'success'
            ];

            // Üretilebilir Miktar - Enhanced (bileşenlere göre minimum)
            $uretilebilir = PHP_INT_MAX;
            $sinir_bilesen = '';
            $sinir_bilesen_stok = 0;

            if (!empty($bom_components)) {
                foreach ($bom_components as $component) {
                    $gerekli = floatval($component['bilesen_miktari']);
                    $mevcut = floatval($component['bilesen_stok']);
                    if ($gerekli > 0) {
                        $bu_bilesenden_uretilebilir = floor($mevcut / $gerekli);
                        if ($bu_bilesenden_uretilebilir < $uretilebilir) {
                            $uretilebilir = $bu_bilesenden_uretilebilir;
                            $sinir_bilesen = $component['bilesen_ismi'];
                            $sinir_bilesen_stok = $mevcut;
                        }
                    }
                }
                if ($uretilebilir === PHP_INT_MAX) {
                    $uretilebilir = 0;
                }
            } else {
                $uretilebilir = 0;
            }

            $acik_kapatma_orani = $stok_acigi > 0 ? round(($uretilebilir / $stok_acigi) * 100, 0) : 0;

            $durum_ozeti['uretilebilir'] = [
                'deger' => $uretilebilir,
                'birim' => 'adet',
                'acik_kapatma_orani' => $acik_kapatma_orani,
                'sinir_bilesen' => $sinir_bilesen,
                'sinir_bilesen_stok' => $sinir_bilesen_stok,
                'durum' => $uretilebilir >= $stok_acigi ? 'success' : ($uretilebilir > 0 ? 'warning' : 'danger')
            ];

            // Üretimdeki Miktar - Enhanced
            $uretimdeki = floatval($production_data['uretimdeki_toplam_planlanan_miktar']);
            $is_emri_sayisi = intval($production_data['is_emri_sayisi']);
            $acik_kapatir = $uretimdeki >= $stok_acigi && $stok_acigi > 0;

            $durum_ozeti['uretimde'] = [
                'deger' => $uretimdeki,
                'is_emri_sayisi' => $is_emri_sayisi,
                'birim' => $product['birim'],
                'acik_kapatir' => $acik_kapatir,
                'durum' => $uretimdeki > 0 ? 'info' : 'secondary',
                'durum_text' => $acik_kapatir ? 'Açığı kapatıyor' : ($uretimdeki > 0 ? 'Üretimde' : 'Üretim yok')
            ];

            // Bekleyen Siparişler - Enhanced
            $bekleyen_siparisler = 0;
            $siparis_sayisi = 0;

            foreach ($orders as $order) {
                if (in_array($order['siparis_durum'], ['onaylandi', 'hazirlaniyor'])) {
                    $bekleyen_siparisler += floatval($order['adet'] ?? $order['miktar'] ?? 0);
                    $siparis_sayisi++;
                }
            }

            $toplam_kullanilabilir = $stok_miktari + $uretimdeki;
            $karsilanabilir = $bekleyen_siparisler <= $toplam_kullanilabilir;

            $durum_ozeti['bekleyen_siparisler'] = [
                'deger' => $bekleyen_siparisler,
                'birim' => $product['birim'],
                'siparis_sayisi' => $siparis_sayisi,
                'karsilanabilir' => $karsilanabilir,
                'toplam_kullanilabilir' => $toplam_kullanilabilir,
                'durum' => $bekleyen_siparisler > $toplam_kullanilabilir ? 'warning' : 'info',
                'durum_text' => $karsilanabilir ? 'Karşılanıyor' : 'Yetersiz stok'
            ];

            // Bileşen Durumu - Enhanced
            $eksik_bilesenler = [];
            $toplam_bilesen = count($bom_components);

            foreach ($bom_components as $component) {
                $gerekli = floatval($component['bilesen_miktari']) * $stok_acigi;
                $mevcut = floatval($component['bilesen_stok']);
                if ($gerekli > $mevcut) {
                    $eksik_bilesenler[] = $component['bilesen_ismi'];
                }
            }

            $tamamlik_yuzdesi = $toplam_bilesen > 0 ? round((($toplam_bilesen - count($eksik_bilesenler)) / $toplam_bilesen) * 100, 0) : 100;
            $eksik_ilk_3 = array_slice($eksik_bilesenler, 0, 3);

            $durum_ozeti['bilesen_durumu'] = [
                'eksik_sayi' => count($eksik_bilesenler),
                'toplam' => $toplam_bilesen,
                'tamamlik_yuzdesi' => $tamamlik_yuzdesi,
                'eksik_bilesenler' => $eksik_bilesenler,
                'eksik_ilk_3' => $eksik_ilk_3,
                'durum' => count($eksik_bilesenler) === 0 ? 'success' : (count($eksik_bilesenler) <= $toplam_bilesen / 2 ? 'warning' : 'danger')
            ];

            // 8. Eylem Önerileri
            $eylem_onerileri = [];

            // Öncelik 1: Kritik stok durumu
            if ($stok_miktari < $kritik_seviye) {
                if ($uretilebilir >= $stok_acigi) {
                    $eylem_onerileri[] = [
                        'oncelik' => 'warning',
                        'mesaj' => 'Üretim başlatın: Stok kritik seviyenin altında',
                        'detay' => round($stok_acigi, 2) . ' ' . $product['birim'] . ' eksik, mevcut bileşenlerle ' . $uretilebilir . ' adet üretilebilir',
                        'ikon' => 'fa-industry',
                        'renk' => 'warning'
                    ];
                } else {
                    $eylem_onerileri[] = [
                        'oncelik' => 'critical',
                        'mesaj' => 'Acil aksiyon gerekli: Stok kritik seviyenin altında',
                        'detay' => round($stok_acigi, 2) . ' ' . $product['birim'] . ' eksik, sadece ' . $uretilebilir . ' adet üretilebilir',
                        'ikon' => 'fa-exclamation-triangle',
                        'renk' => 'danger'
                    ];
                }
            }

            // Öncelik 2: Bileşen eksikleri
            if (!empty($eksik_bilesenler)) {
                $bilesen_listesi = implode(', ', array_slice($eksik_bilesenler, 0, 3));
                if (count($eksik_bilesenler) > 3) {
                    $bilesen_listesi .= ' (+' . (count($eksik_bilesenler) - 3) . ' daha)';
                }

                $eylem_onerileri[] = [
                    'oncelik' => count($eksik_bilesenler) > $toplam_bilesen / 2 ? 'critical' : 'warning',
                    'mesaj' => count($eksik_bilesenler) . ' bileşeni satın alın',
                    'detay' => $bilesen_listesi,
                    'ikon' => 'fa-shopping-cart',
                    'renk' => count($eksik_bilesenler) > $toplam_bilesen / 2 ? 'danger' : 'warning'
                ];
            }

            // Öncelik 3: Üretim yetersizliği
            if ($uretimdeki > 0 && $uretimdeki < $stok_acigi) {
                $eksik_uretim = $stok_acigi - $uretimdeki;
                $eylem_onerileri[] = [
                    'oncelik' => 'warning',
                    'mesaj' => 'Üretim planını artırın',
                    'detay' => round($eksik_uretim, 2) . ' ' . $product['birim'] . ' daha üretilmeli',
                    'ikon' => 'fa-chart-line',
                    'renk' => 'warning'
                ];
            }

            // Öncelik 4: Sipariş karşılama kapasitesi
            $toplam_kullanilabilir = $stok_miktari + $uretimdeki;
            if ($bekleyen_siparisler > $toplam_kullanilabilir) {
                $eksik_miktar = $bekleyen_siparisler - $toplam_kullanilabilir;
                $eylem_onerileri[] = [
                    'oncelik' => 'warning',
                    'mesaj' => 'Sipariş karşılama riski',
                    'detay' => round($eksik_miktar, 2) . ' ' . $product['birim'] . ' daha üretilmeli (bekleyen siparişler için)',
                    'ikon' => 'fa-shipping-fast',
                    'renk' => 'warning'
                ];
            }

            // Her şey yolundaysa
            if (empty($eylem_onerileri)) {
                $eylem_onerileri[] = [
                    'oncelik' => 'info',
                    'mesaj' => 'Ürün hazır durumda',
                    'detay' => 'Stok yeterli, bileşenler tamam, acil aksiyon gerekmiyor',
                    'ikon' => 'fa-check-circle',
                    'renk' => 'success'
                ];
            }

            $response = [
                'status' => 'success',
                'data' => [
                    'product' => $product,
                    'photos' => $photos,
                    'movements' => [
                        'data' => $movements,
                        'pagination' => [
                            'current_page' => $page,
                            'total_pages' => $total_pages,
                            'total_records' => $total_movements,
                            'limit' => $limit
                        ]
                    ],
                    'bom_components' => $bom_components,
                    'orders' => [
                        'data' => $orders,
                        'summary' => $order_summary
                    ],
                    'production' => $production_data,
                    'durum_ozeti' => $durum_ozeti,
                    'eylem_onerileri' => $eylem_onerileri
                ]
            ];

        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => 'Bir hata oluştu: ' . $e->getMessage()];
        }
    }
}

echo json_encode($response);

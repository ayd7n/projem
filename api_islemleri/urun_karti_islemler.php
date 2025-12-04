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
                    ]
                ]
            ];

        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => 'Bir hata oluştu: ' . $e->getMessage()];
        }
    }
}

echo json_encode($response);

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_products') {
        if (!yetkisi_var('page:view:urunler')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürünleri görüntüleme yetkiniz yok.']);
            exit;
        }
        try {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
            $urun_tipi = isset($_GET['urun_tipi']) ? $_GET['urun_tipi'] : '';
            $offset = ($page - 1) * $limit;
            $search_term = '%' . $search . '%';

            // Build WHERE conditions
            $where_conditions = [];
            $params = [];
            $param_types = '';

            if (!empty($search)) {
                $where_conditions[] = "(u.urun_ismi LIKE ? OR u.urun_kodu LIKE ?)";
                $params[] = $search_term;
                $params[] = $search_term;
                $param_types .= 'ss';
            }

            if ($filter === 'critical') {
                $where_conditions[] = "u.stok_miktari <= u.kritik_stok_seviyesi AND u.kritik_stok_seviyesi > 0";
            }

            if (!empty($urun_tipi)) {
                $where_conditions[] = "u.urun_tipi = ?";
                $params[] = $urun_tipi;
                $param_types .= 's';
            }

            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

            $count_query = "SELECT COUNT(*) as total FROM urunler u {$where_clause}";
            $stmt_count = $connection->prepare($count_query);
            if (!empty($params)) {
                $stmt_count->bind_param($param_types, ...$params);
            }
            $stmt_count->execute();
            $count_result = $stmt_count->get_result()->fetch_assoc();
            $total_products = $count_result['total'];
            $total_pages = ceil($total_products / $limit);
            $stmt_count->close();

            $can_view_cost = yetkisi_var('action:urunler:view_cost');
            $cost_column = $can_view_cost ? ", vum.teorik_maliyet" : "";
            $query = "
                SELECT u.* {$cost_column}, COUNT(uf.fotograf_id) as foto_sayisi
                FROM urunler u
                " . ($can_view_cost ? "LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu" : "") . "
                LEFT JOIN urun_fotograflari uf ON u.urun_kodu = uf.urun_kodu
                {$where_clause}
                GROUP BY u.urun_kodu
                ORDER BY u.urun_ismi
                LIMIT ? OFFSET ?";

            $params[] = $limit;
            $params[] = $offset;
            $param_types .= 'ii';

            $stmt_data = $connection->prepare($query);
            $stmt_data->bind_param($param_types, ...$params);
            $stmt_data->execute();
            $result = $stmt_data->get_result();

            $products = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (!$can_view_cost) {
                        unset($row['teorik_maliyet']);
                    }
                    $products[] = $row;
                }
            }
            $stmt_data->close();

            // Calculate stats for all products (unfiltered)
            $stats_query = "SELECT COUNT(*) as total FROM urunler";
            $stats_result = $connection->query($stats_query);
            $total_all_products = $stats_result->fetch_assoc()['total'] ?? 0;

            $critical_stats_query = "SELECT COUNT(*) as total FROM urunler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0";
            $critical_stats_result = $connection->query($critical_stats_query);
            $total_critical_products = $critical_stats_result->fetch_assoc()['total'] ?? 0;

            $response = [
                'status' => 'success',
                'data' => $products,
                'pagination' => [
                    'total_pages' => $total_pages,
                    'total_products' => $total_products,
                    'current_page' => $page
                ],
                'stats' => [
                    'total_products' => $total_all_products,
                    'critical_products' => $total_critical_products
                ]
            ];
        } catch (Throwable $t) {
            $response = ['status' => 'error', 'message' => 'Bir hata oluştu: ' . $t->getMessage()];
        }
    } elseif ($action == 'get_product' && isset($_GET['id'])) {
        if (!yetkisi_var('page:view:urunler')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürün görüntüleme yetkiniz yok.']);
            exit;
        }
        $urun_kodu = (int) $_GET['id'];
        $query = "SELECT * FROM urunler WHERE urun_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_kodu);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $response = ['status' => 'success', 'data' => $product];
        } else {
            $response = ['status' => 'error', 'message' => 'Ürün bulunamadı.'];
        }
    } elseif ($action == 'get_depo_list') {
        $query = "SELECT DISTINCT depo_ismi FROM lokasyonlar ORDER BY depo_ismi";
        $result = $connection->query($query);
        $depolar = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $depolar[] = $row;
            }
            $response = ['status' => 'success', 'data' => $depolar];
        } else {
            $response = ['status' => 'error', 'message' => 'Depo listesi alınamadı.'];
        }
    } elseif ($action == 'get_raf_list') {
        $depo = $_GET['depo'] ?? '';
        $query = "SELECT DISTINCT raf FROM lokasyonlar WHERE depo_ismi = ? ORDER BY raf";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('s', $depo);
        $stmt->execute();
        $result = $stmt->get_result();
        $raflar = [];
        while ($row = $result->fetch_assoc()) {
            $raflar[] = $row;
        }
        $stmt->close();
        $response = ['status' => 'success', 'data' => $raflar];
    } elseif ($action == 'get_all_products') {
        $can_view_cost = yetkisi_var('action:urunler:view_cost');
        $cost_column = $can_view_cost ? ", COALESCE(vum.teorik_maliyet, 0) as teorik_maliyet" : "";

        $query = "SELECT u.* {$cost_column} 
                  FROM urunler u 
                  " . ($can_view_cost ? "LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu" : "") . "
                  ORDER BY u.urun_ismi";
        $result = $connection->query($query);

        if ($result) {
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $response = ['status' => 'success', 'data' => $products];
        } else {
            $response = ['status' => 'error', 'message' => 'Ürün listesi alınamadı.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    $urun_ismi = $_POST['urun_ismi'] ?? null;
    $not_bilgisi = $_POST['not_bilgisi'] ?? '';
    $stok_miktari = isset($_POST['stok_miktari']) ? (int) $_POST['stok_miktari'] : 0;
    $birim = $_POST['birim'] ?? 'adet';
    $birim = $_POST['birim'] ?? 'adet';
    $satis_fiyati = isset($_POST['satis_fiyati']) && $_POST['satis_fiyati'] !== '' ? (float) $_POST['satis_fiyati'] : 0.0;
    $alis_fiyati = isset($_POST['alis_fiyati']) && $_POST['alis_fiyati'] !== '' ? (float) $_POST['alis_fiyati'] : 0.0;
    $kritik_stok_seviyesi = isset($_POST['kritik_stok_seviyesi']) ? (int) $_POST['kritik_stok_seviyesi'] : 0;
    $depo = $_POST['depo'] ?? '';
    $raf = $_POST['raf'] ?? '';
    $urun_tipi = $_POST['urun_tipi'] ?? 'uretilen';

    if ($action == 'add_product') {
        if (!yetkisi_var('action:urunler:create')) {
            echo json_encode(['status' => 'error', 'message' => 'Yeni ürün ekleme yetkiniz yok.']);
            exit;
        }
        if (empty($urun_ismi)) {
            $response = ['status' => 'error', 'message' => 'Ürün ismi boş olamaz.'];
        } else {
            // Check if product name already exists
            $check_query = "SELECT urun_kodu FROM urunler WHERE urun_ismi = ?";
            $check_stmt = $connection->prepare($check_query);
            $check_stmt->bind_param('s', $urun_ismi);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_stmt->close();

            if ($check_result->num_rows > 0) {
                $response = ['status' => 'error', 'message' => 'Bu ürün ismi zaten mevcut. Lütfen farklı bir isim kullanın.'];
            } else {
                $query = "INSERT INTO urunler (urun_ismi, not_bilgisi, stok_miktari, birim, satis_fiyati, alis_fiyati, kritik_stok_seviyesi, depo, raf, urun_tipi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('ssisddisss', $urun_ismi, $not_bilgisi, $stok_miktari, $birim, $satis_fiyati, $alis_fiyati, $kritik_stok_seviyesi, $depo, $raf, $urun_tipi);

                if ($stmt->execute()) {
                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "$urun_ismi ürünü sisteme eklendi", 'CREATE');
                    $response = ['status' => 'success', 'message' => 'Ürün başarıyla eklendi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
                }
                $stmt->close();
            }
        }
    } elseif ($action == 'update_product' && isset($_POST['urun_kodu'])) {
        if (!yetkisi_var('action:urunler:edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürün düzenleme yetkiniz yok.']);
            exit;
        }
        $urun_kodu = (int) $_POST['urun_kodu'];
        if (empty($urun_ismi)) {
            $response = ['status' => 'error', 'message' => 'Ürün ismi boş olamaz.'];
        } else {
            // Check if product name already exists (excluding current product)
            $check_query = "SELECT urun_kodu FROM urunler WHERE urun_ismi = ? AND urun_kodu != ?";
            $check_stmt = $connection->prepare($check_query);
            $check_stmt->bind_param('si', $urun_ismi, $urun_kodu);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_stmt->close();

            if ($check_result->num_rows > 0) {
                $response = ['status' => 'error', 'message' => 'Bu ürün ismi zaten mevcut. Lütfen farklı bir isim kullanın.'];
            } else {
                // Eski ürün adını almak için sorgu
                $old_product_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
                $old_stmt = $connection->prepare($old_product_query);
                $old_stmt->bind_param('i', $urun_kodu);
                $old_stmt->execute();
                $old_result = $old_stmt->get_result();
                $old_product = $old_result->fetch_assoc();
                $old_product_name = $old_product['urun_ismi'] ?? 'Bilinmeyen Ürün';
                $old_stmt->close();

                $query = "UPDATE urunler SET urun_ismi = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, satis_fiyati = ?, alis_fiyati = ?, kritik_stok_seviyesi = ?, depo = ?, raf = ?, urun_tipi = ? WHERE urun_kodu = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('ssisddisssi', $urun_ismi, $not_bilgisi, $stok_miktari, $birim, $satis_fiyati, $alis_fiyati, $kritik_stok_seviyesi, $depo, $raf, $urun_tipi, $urun_kodu);

                if ($stmt->execute()) {
                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "$old_product_name ürünü $urun_ismi olarak güncellendi", 'UPDATE');
                    $response = ['status' => 'success', 'message' => 'Ürün başarıyla güncellendi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
                }
                $stmt->close();
            }
        }
    } elseif ($action == 'delete_product' && isset($_POST['urun_kodu'])) {
        if (!yetkisi_var('action:urunler:delete')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürün silme yetkiniz yok.']);
            exit;
        }
        $urun_kodu = (int) $_POST['urun_kodu'];
        // Silinen ürün adını almak için sorgu
        $old_product_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
        $old_stmt = $connection->prepare($old_product_query);
        $old_stmt->bind_param('i', $urun_kodu);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result();
        $old_product = $old_result->fetch_assoc();
        $deleted_product_name = $old_product['urun_ismi'] ?? 'Bilinmeyen Ürün';
        $old_stmt->close();

        // Ürüne ait fotoğrafları al ve fiziksel dosyaları sil
        $photo_query = "SELECT dosya_yolu FROM urun_fotograflari WHERE urun_kodu = ?";
        $photo_stmt = $connection->prepare($photo_query);
        $photo_stmt->bind_param('i', $urun_kodu);
        $photo_stmt->execute();
        $photo_result = $photo_stmt->get_result();

        while ($photo = $photo_result->fetch_assoc()) {
            $file_path = '../' . $photo['dosya_yolu'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $photo_stmt->close();

        // Ürüne ait fotoğraf kayıtlarını veritabanından sil
        $delete_photos_query = "DELETE FROM urun_fotograflari WHERE urun_kodu = ?";
        $delete_photos_stmt = $connection->prepare($delete_photos_query);
        $delete_photos_stmt->bind_param('i', $urun_kodu);
        $delete_photos_stmt->execute();
        $delete_photos_stmt->close();

        $query = "DELETE FROM urunler WHERE urun_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $urun_kodu);
        if ($stmt->execute()) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_product_name ürünü sistemden silindi", 'DELETE');
            $response = ['status' => 'success', 'message' => 'Ürün başarıyla silindi.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
        }
        $stmt->close();
    } elseif ($action == 'count_products') {
        if (!yetkisi_var('page:view:urunler')) {
            echo json_encode(['status' => 'error', 'message' => 'Ürünleri görüntüleme yetkiniz yok.']);
            exit;
        }

        $query = "SELECT COUNT(*) as total FROM urunler";
        $result = $connection->query($query);

        if ($result) {
            $row = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'total' => (int) $row['total']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error]);
        }
        exit; // Exit here to prevent the final response
    }
}

$connection->close();
echo json_encode($response);
?>

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
            $offset = ($page - 1) * $limit;
            $search_term = '%' . $search . '%';

            if ($filter === 'critical') {
                $count_query = "SELECT COUNT(*) as total FROM urunler WHERE (urun_ismi LIKE ? OR urun_kodu LIKE ?) AND stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0";
                $stmt_count = $connection->prepare($count_query);
                $stmt_count->bind_param('ss', $search_term, $search_term);
            } else {
                $count_query = "SELECT COUNT(*) as total FROM urunler WHERE urun_ismi LIKE ? OR urun_kodu LIKE ?";
                $stmt_count = $connection->prepare($count_query);
                $stmt_count->bind_param('ss', $search_term, $search_term);
            }
            $stmt_count->execute();
            $count_result = $stmt_count->get_result()->fetch_assoc();
            $total_products = $count_result['total'];
            $total_pages = ceil($total_products / $limit);
            $stmt_count->close();

            $can_view_cost = yetkisi_var('action:urunler:view_cost');
            $cost_column = $can_view_cost ? ", vum.teorik_maliyet" : "";

            if ($filter === 'critical') {
                $query = "
                    SELECT u.* {$cost_column}
                    FROM urunler u
                    " . ($can_view_cost ? "LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu" : "") . "
                    WHERE (u.urun_ismi LIKE ? OR u.urun_kodu LIKE ?) AND u.stok_miktari <= u.kritik_stok_seviyesi AND u.kritik_stok_seviyesi > 0
                    ORDER BY u.urun_ismi
                    LIMIT ? OFFSET ?";
                $stmt_data = $connection->prepare($query);
                $stmt_data->bind_param('ssii', $search_term, $search_term, $limit, $offset);
            } else {
                $query = "
                    SELECT u.* {$cost_column}
                    FROM urunler u
                    " . ($can_view_cost ? "LEFT JOIN v_urun_maliyetleri vum ON u.urun_kodu = vum.urun_kodu" : "") . "
                    WHERE u.urun_ismi LIKE ? OR u.urun_kodu LIKE ?
                    ORDER BY u.urun_ismi
                    LIMIT ? OFFSET ?";
                $stmt_data = $connection->prepare($query);
                $stmt_data->bind_param('ssii', $search_term, $search_term, $limit, $offset);
            }
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

            $response = [
                'status' => 'success',
                'data' => $products,
                'pagination' => [
                    'total_pages' => $total_pages,
                    'total_products' => $total_products,
                    'current_page' => $page
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
    $satis_fiyati = isset($_POST['satis_fiyati']) ? (float) $_POST['satis_fiyati'] : 0.0;
    $kritik_stok_seviyesi = isset($_POST['kritik_stok_seviyesi']) ? (int) $_POST['kritik_stok_seviyesi'] : 0;
    $depo = $_POST['depo'] ?? '';
    $raf = $_POST['raf'] ?? '';

    if ($action == 'add_product') {
        if (!yetkisi_var('action:urunler:create')) {
            echo json_encode(['status' => 'error', 'message' => 'Yeni ürün ekleme yetkiniz yok.']);
            exit;
        }
        if (empty($urun_ismi)) {
            $response = ['status' => 'error', 'message' => 'Ürün ismi boş olamaz.'];
        } else {
            $query = "INSERT INTO urunler (urun_ismi, not_bilgisi, stok_miktari, birim, satis_fiyati, kritik_stok_seviyesi, depo, raf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('ssisdiss', $urun_ismi, $not_bilgisi, $stok_miktari, $birim, $satis_fiyati, $kritik_stok_seviyesi, $depo, $raf);

            if ($stmt->execute()) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "$urun_ismi ürünü sisteme eklendi", 'CREATE');
                $response = ['status' => 'success', 'message' => 'Ürün başarıyla eklendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
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
            // Eski ürün adını almak için sorgu
            $old_product_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
            $old_stmt = $connection->prepare($old_product_query);
            $old_stmt->bind_param('i', $urun_kodu);
            $old_stmt->execute();
            $old_result = $old_stmt->get_result();
            $old_product = $old_result->fetch_assoc();
            $old_product_name = $old_product['urun_ismi'] ?? 'Bilinmeyen Ürün';
            $old_stmt->close();

            $query = "UPDATE urunler SET urun_ismi = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, satis_fiyati = ?, kritik_stok_seviyesi = ?, depo = ?, raf = ? WHERE urun_kodu = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('ssisdissi', $urun_ismi, $not_bilgisi, $stok_miktari, $birim, $satis_fiyati, $kritik_stok_seviyesi, $depo, $raf, $urun_kodu);

            if ($stmt->execute()) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "$old_product_name ürünü $urun_ismi olarak güncellendi", 'UPDATE');
                $response = ['status' => 'success', 'message' => 'Ürün başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
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
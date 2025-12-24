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
            $depo_filter = isset($_GET['depo']) ? $_GET['depo'] : '';
            $raf_filter = isset($_GET['raf']) ? $_GET['raf'] : '';
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

            if (!empty($depo_filter)) {
                $where_conditions[] = "u.depo = ?";
                $params[] = $depo_filter;
                $param_types .= 's';
            }

            if (!empty($raf_filter)) {
                $where_conditions[] = "u.raf = ?";
                $params[] = $raf_filter;
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

                    // Üretilebilir miktar hesaplama
                    $row['uretilebilir_miktar'] = 0;  // Kritik bileşenlere göre
                    $row['gercek_uretilebilir'] = 0;  // Tüm bileşenlere göre

                    $kritik_bilesen_turleri = ['kutu', 'takim', 'esans'];
                    $uretilebilir_kritik = PHP_INT_MAX;
                    $uretilebilir_gercek = PHP_INT_MAX;

                    // Ürün ağacından bileşenleri al
                    $bom_query = "SELECT ua.bilesen_miktari, ua.bilesenin_malzeme_turu as bilesen_turu,
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
                                 WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?";
                    $bom_stmt = $connection->prepare($bom_query);
                    $bom_stmt->bind_param('i', $row['urun_kodu']);
                    $bom_stmt->execute();
                    $bom_result = $bom_stmt->get_result();

                    while ($bom_row = $bom_result->fetch_assoc()) {
                        $gerekli = floatval($bom_row['bilesen_miktari']);
                        $mevcut = floatval($bom_row['bilesen_stok']);

                        if ($gerekli > 0) {
                            $bu_bilesenden = max(0, floor($mevcut / $gerekli));

                            // Gerçek üretilebilir (tüm bileşenler)
                            $uretilebilir_gercek = min($uretilebilir_gercek, $bu_bilesenden);

                            // Kritik üretilebilir (sadece kritik bileşenler)
                            if (in_array(strtolower($bom_row['bilesen_turu']), $kritik_bilesen_turleri)) {
                                $uretilebilir_kritik = min($uretilebilir_kritik, $bu_bilesenden);
                            }
                        }
                    }
                    $bom_stmt->close();

                    $row['uretilebilir_miktar'] = ($uretilebilir_kritik === PHP_INT_MAX) ? 0 : $uretilebilir_kritik;
                    $row['gercek_uretilebilir'] = ($uretilebilir_gercek === PHP_INT_MAX) ? 0 : $uretilebilir_gercek;

                    $products[] = $row;
                }
            }
            $stmt_data->close();

            // Calculate filtered critical stock count (apply same filters except critical filter itself)
            $critical_where_conditions = $where_conditions;
            $critical_params = [];
            $critical_param_types = '';

            // Remove critical filter from conditions if it was added
            $critical_where_conditions = array_filter($critical_where_conditions, function ($cond) {
                return strpos($cond, 'kritik_stok_seviyesi') === false;
            });

            // Add critical stock condition
            $critical_where_conditions[] = "u.stok_miktari <= u.kritik_stok_seviyesi AND u.kritik_stok_seviyesi > 0";

            // Rebuild params without limit/offset and filter param
            if (!empty($search)) {
                $critical_params[] = $search_term;
                $critical_params[] = $search_term;
                $critical_param_types .= 'ss';
            }
            if (!empty($urun_tipi)) {
                $critical_params[] = $urun_tipi;
                $critical_param_types .= 's';
            }
            if (!empty($depo_filter)) {
                $critical_params[] = $depo_filter;
                $critical_param_types .= 's';
            }
            if (!empty($raf_filter)) {
                $critical_params[] = $raf_filter;
                $critical_param_types .= 's';
            }

            $critical_where_clause = !empty($critical_where_conditions) ? 'WHERE ' . implode(' AND ', $critical_where_conditions) : '';
            $critical_count_query = "SELECT COUNT(*) as total FROM urunler u {$critical_where_clause}";
            $stmt_critical = $connection->prepare($critical_count_query);
            if (!empty($critical_params)) {
                $stmt_critical->bind_param($critical_param_types, ...$critical_params);
            }
            $stmt_critical->execute();
            $filtered_critical = $stmt_critical->get_result()->fetch_assoc()['total'] ?? 0;
            $stmt_critical->close();

            $response = [
                'status' => 'success',
                'data' => $products,
                'pagination' => [
                    'total_pages' => $total_pages,
                    'total_products' => $total_products,
                    'current_page' => $page
                ],
                'stats' => [
                    'total_products' => $total_products,
                    'critical_products' => $filtered_critical
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
    } elseif ($action == 'get_product_depolar') {
        // Sadece ürünlerde kullanılan depolar
        $query = "SELECT DISTINCT depo FROM urunler WHERE depo IS NOT NULL AND depo != '' ORDER BY depo";
        $result = $connection->query($query);
        $depolar = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $depolar[] = ['depo_ismi' => $row['depo']];
            }
            $response = ['status' => 'success', 'data' => $depolar];
        } else {
            $response = ['status' => 'error', 'message' => 'Depo listesi alınamadı.'];
        }
    } elseif ($action == 'get_product_raflar') {
        // Sadece ürünlerde kullanılan raflar (depoya göre filtrelenebilir)
        $depo = $_GET['depo'] ?? '';
        if (!empty($depo)) {
            $query = "SELECT DISTINCT raf FROM urunler WHERE depo = ? AND raf IS NOT NULL AND raf != '' ORDER BY raf";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('s', $depo);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $query = "SELECT DISTINCT raf FROM urunler WHERE raf IS NOT NULL AND raf != '' ORDER BY raf";
            $result = $connection->query($query);
        }
        $raflar = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $raflar[] = $row;
            }
            $response = ['status' => 'success', 'data' => $raflar];
        } else {
            $response = ['status' => 'error', 'message' => 'Raf listesi alınamadı.'];
        }
        if (isset($stmt))
            $stmt->close();
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
    $satis_fiyati_para_birimi = $_POST['satis_fiyati_para_birimi'] ?? 'TRY';
    $alis_fiyati = isset($_POST['alis_fiyati']) && $_POST['alis_fiyati'] !== '' ? (float) $_POST['alis_fiyati'] : 0.0;
    $alis_fiyati_para_birimi = $_POST['alis_fiyati_para_birimi'] ?? 'TRY';
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
                $query = "INSERT INTO urunler (urun_ismi, not_bilgisi, stok_miktari, birim, satis_fiyati, satis_fiyati_para_birimi, alis_fiyati, alis_fiyati_para_birimi, kritik_stok_seviyesi, depo, raf, urun_tipi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('ssisdsdssiss', $urun_ismi, $not_bilgisi, $stok_miktari, $birim, $satis_fiyati, $satis_fiyati_para_birimi, $alis_fiyati, $alis_fiyati_para_birimi, $kritik_stok_seviyesi, $depo, $raf, $urun_tipi);

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

                $query = "UPDATE urunler SET urun_ismi = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, satis_fiyati = ?, satis_fiyati_para_birimi = ?, alis_fiyati = ?, alis_fiyati_para_birimi = ?, kritik_stok_seviyesi = ?, depo = ?, raf = ?, urun_tipi = ? WHERE urun_kodu = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param('ssisdsdssissi', $urun_ismi, $not_bilgisi, $stok_miktari, $birim, $satis_fiyati, $satis_fiyati_para_birimi, $alis_fiyati, $alis_fiyati_para_birimi, $kritik_stok_seviyesi, $depo, $raf, $urun_tipi, $urun_kodu);

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

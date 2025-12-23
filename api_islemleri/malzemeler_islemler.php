<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get_materials') {
        if (!yetkisi_var('page:view:malzemeler')) {
            echo json_encode(['status' => 'error', 'message' => 'Malzemeleri görüntüleme yetkiniz yok.']);
            exit;
        }
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
        $depo_filter = isset($_GET['depo']) ? $_GET['depo'] : '';
        $raf_filter = isset($_GET['raf']) ? $_GET['raf'] : '';
        $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'malzeme_ismi';
        $order_dir = isset($_GET['order_dir']) ? strtoupper($_GET['order_dir']) : 'ASC';
        $offset = ($page - 1) * $limit;

        // Validate order_by and order_dir to prevent SQL injection
        $allowed_columns = ['malzeme_kodu', 'malzeme_ismi', 'malzeme_turu', 'stok_miktari', 'birim', 'alis_fiyati', 'para_birimi', 'termin_suresi', 'depo', 'raf', 'kritik_stok_seviyesi'];
        $allowed_directions = ['ASC', 'DESC'];

        if (!in_array($order_by, $allowed_columns)) {
            $order_by = 'malzeme_ismi';
        }

        if (!in_array($order_dir, $allowed_directions)) {
            $order_dir = 'ASC';
        }

        $search_term = '%' . $search . '%';

        // Build WHERE conditions
        $where_conditions = ["(m.malzeme_ismi LIKE ? OR m.malzeme_kodu LIKE ?)"];
        $params = [$search_term, $search_term];
        $param_types = 'ss';

        if ($filter === 'critical') {
            $where_conditions[] = "m.stok_miktari <= m.kritik_stok_seviyesi AND m.kritik_stok_seviyesi > 0";
        }

        if (!empty($depo_filter)) {
            $where_conditions[] = "m.depo = ?";
            $params[] = $depo_filter;
            $param_types .= 's';
        }

        if (!empty($raf_filter)) {
            $where_conditions[] = "m.raf = ?";
            $params[] = $raf_filter;
            $param_types .= 's';
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

        // Get total count for pagination
        $count_query = "SELECT COUNT(*) as total FROM malzemeler m {$where_clause}";
        $stmt = $connection->prepare($count_query);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $count_result = $stmt->get_result()->fetch_assoc();
        $total_materials = $count_result['total'];
        $total_pages = ceil($total_materials / $limit);
        $stmt->close();

        // Get paginated data
        $query = "SELECT m.*, COUNT(mf.fotograf_id) as foto_sayisi 
                  FROM malzemeler m 
                  LEFT JOIN malzeme_fotograflari mf ON m.malzeme_kodu = mf.malzeme_kodu 
                  {$where_clause}
                  GROUP BY m.malzeme_kodu 
                  ORDER BY {$order_by} {$order_dir} LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;
        $param_types .= 'ii';

        $stmt = $connection->prepare($query);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $materials = [];
        while ($row = $result->fetch_assoc()) {
            $materials[] = $row;
        }
        $stmt->close();

        // Calculate filtered critical materials count
        $critical_where_conditions = ["(m.malzeme_ismi LIKE ? OR m.malzeme_kodu LIKE ?)"];
        $critical_params = [$search_term, $search_term];
        $critical_param_types = 'ss';

        $critical_where_conditions[] = "m.stok_miktari <= m.kritik_stok_seviyesi AND m.kritik_stok_seviyesi > 0";

        if (!empty($depo_filter)) {
            $critical_where_conditions[] = "m.depo = ?";
            $critical_params[] = $depo_filter;
            $critical_param_types .= 's';
        }
        if (!empty($raf_filter)) {
            $critical_where_conditions[] = "m.raf = ?";
            $critical_params[] = $raf_filter;
            $critical_param_types .= 's';
        }

        $critical_where_clause = 'WHERE ' . implode(' AND ', $critical_where_conditions);
        $critical_query = "SELECT COUNT(*) as total FROM malzemeler m {$critical_where_clause}";
        $critical_stmt = $connection->prepare($critical_query);
        $critical_stmt->bind_param($critical_param_types, ...$critical_params);
        $critical_stmt->execute();
        $critical_materials = $critical_stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $critical_stmt->close();

        $response = [
            'status' => 'success',
            'data' => $materials,
            'pagination' => [
                'total_pages' => $total_pages,
                'total_materials' => $total_materials,
                'current_page' => $page,
                'critical_materials' => $critical_materials
            ]
        ];
    } elseif ($action == 'get_material_depolar') {
        // Sadece malzemelerde kullanılan depolar
        $query = "SELECT DISTINCT depo FROM malzemeler WHERE depo IS NOT NULL AND depo != '' ORDER BY depo";
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
    } elseif ($action == 'get_material_raflar') {
        // Sadece malzemelerde kullanılan raflar (depoya göre filtrelenebilir)
        $depo = $_GET['depo'] ?? '';
        if (!empty($depo)) {
            $query = "SELECT DISTINCT raf FROM malzemeler WHERE depo = ? AND raf IS NOT NULL AND raf != '' ORDER BY raf";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('s', $depo);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $query = "SELECT DISTINCT raf FROM malzemeler WHERE raf IS NOT NULL AND raf != '' ORDER BY raf";
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
    } elseif ($action == 'get_material' && isset($_GET['id'])) {
        if (!yetkisi_var('page:view:malzemeler')) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme görüntüleme yetkiniz yok.']);
            exit;
        }
        $malzeme_kodu = (int) $_GET['id'];
        $query = "SELECT * FROM malzemeler WHERE malzeme_kodu = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $malzeme_kodu);
        $stmt->execute();
        $result = $stmt->get_result();
        $material = $result->fetch_assoc();
        $stmt->close();

        if ($material) {
            $response = ['status' => 'success', 'data' => $material];
        } else {
            $response = ['status' => 'error', 'message' => 'Malzeme bulunamadı.'];
        }
    } elseif ($action == 'get_all_materials') {
        $query = "SELECT * FROM malzemeler ORDER BY malzeme_ismi";
        $result = $connection->query($query);

        if ($result) {
            $materials = [];
            while ($row = $result->fetch_assoc()) {
                $materials[] = $row;
            }
            $response = ['status' => 'success', 'data' => $materials];
        } else {
            $response = ['status' => 'error', 'message' => 'Malzeme listesi alınamadı.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Extract data from POST
    $malzeme_ismi = $_POST['malzeme_ismi'] ?? null;
    $malzeme_turu = $_POST['malzeme_turu'] ?? '';
    $not_bilgisi = $_POST['not_bilgisi'] ?? '';
    $stok_miktari = isset($_POST['stok_miktari']) ? (float) $_POST['stok_miktari'] : 0;
    $birim = $_POST['birim'] ?? 'adet';
    $alis_fiyati = isset($_POST['alis_fiyati']) ? (float) $_POST['alis_fiyati'] : 0.00;
    $para_birimi = $_POST['para_birimi'] ?? 'TRY';
    $termin_suresi = isset($_POST['termin_suresi']) ? (int) $_POST['termin_suresi'] : 0;
    $depo = $_POST['depo'] ?? '';
    $raf = $_POST['raf'] ?? '';
    $kritik_stok_seviyesi = isset($_POST['kritik_stok_seviyesi']) ? (int) $_POST['kritik_stok_seviyesi'] : 0;

    if ($action == 'add_material') {
        if (!yetkisi_var('action:malzemeler:create')) {
            echo json_encode(['status' => 'error', 'message' => 'Yeni malzeme ekleme yetkiniz yok.']);
            exit;
        }
        if (empty($malzeme_ismi)) {
            $response = ['status' => 'error', 'message' => 'Malzeme ismi boş olamaz.'];
        } else {
            $query = "INSERT INTO malzemeler (malzeme_ismi, malzeme_turu, not_bilgisi, stok_miktari, birim, alis_fiyati, para_birimi, termin_suresi, depo, raf, kritik_stok_seviyesi) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssdsdsissi', $malzeme_ismi, $malzeme_turu, $not_bilgisi, $stok_miktari, $birim, $alis_fiyati, $para_birimi, $termin_suresi, $depo, $raf, $kritik_stok_seviyesi);

            if ($stmt->execute()) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "$malzeme_ismi malzemesi sisteme eklendi", 'CREATE');
                $response = ['status' => 'success', 'message' => 'Malzeme başarıyla eklendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'update_material' && isset($_POST['malzeme_kodu'])) {
        if (!yetkisi_var('action:malzemeler:edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme düzenleme yetkiniz yok.']);
            exit;
        }
        $malzeme_kodu = (int) $_POST['malzeme_kodu'];

        if (empty($malzeme_ismi)) {
            $response = ['status' => 'error', 'message' => 'Malzeme ismi boş olamaz.'];
        } else {
            // Check if the price was manually changed
            $price_check_stmt = $connection->prepare("SELECT alis_fiyati FROM malzemeler WHERE malzeme_kodu = ?");
            $price_check_stmt->bind_param('i', $malzeme_kodu);
            $price_check_stmt->execute();
            $result = $price_check_stmt->get_result();
            $current_material = $result->fetch_assoc();
            $price_check_stmt->close();

            $maliyet_manuel_girildi = false;
            if ($current_material && (float) $current_material['alis_fiyati'] != (float) $alis_fiyati) {
                $maliyet_manuel_girildi = true;
            }

            $query = "UPDATE malzemeler SET malzeme_ismi = ?, malzeme_turu = ?, not_bilgisi = ?, stok_miktari = ?, birim = ?, alis_fiyati = ?, para_birimi = ?, maliyet_manuel_girildi = ?, termin_suresi = ?, depo = ?, raf = ?, kritik_stok_seviyesi = ? WHERE malzeme_kodu = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sssdsdsisssii', $malzeme_ismi, $malzeme_turu, $not_bilgisi, $stok_miktari, $birim, $alis_fiyati, $para_birimi, $maliyet_manuel_girildi, $termin_suresi, $depo, $raf, $kritik_stok_seviyesi, $malzeme_kodu);

            // Eski malzeme bilgilerini al
            $old_material_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
            $old_stmt = $connection->prepare($old_material_query);
            $old_stmt->bind_param('i', $malzeme_kodu);
            $old_stmt->execute();
            $old_result = $old_stmt->get_result();
            $old_material = $old_result->fetch_assoc();
            $old_name = $old_material['malzeme_ismi'] ?? 'Bilinmeyen Malzeme';
            $old_stmt->close();

            if ($stmt->execute()) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "$old_name malzemesi $malzeme_ismi olarak güncellendi", 'UPDATE');
                $response = ['status' => 'success', 'message' => 'Malzeme başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
            }
            $stmt->close();
        }
    } elseif ($action == 'delete_material' && isset($_POST['malzeme_kodu'])) {
        if (!yetkisi_var('action:malzemeler:delete')) {
            echo json_encode(['status' => 'error', 'message' => 'Malzeme silme yetkiniz yok.']);
            exit;
        }
        $malzeme_kodu = (int) $_POST['malzeme_kodu'];

        // Önce malzemeye ait fotoğrafları al ve fiziksel dosyaları sil
        $photos_query = "SELECT dosya_yolu FROM malzeme_fotograflari WHERE malzeme_kodu = ?";
        $photos_stmt = $connection->prepare($photos_query);
        $photos_stmt->bind_param('i', $malzeme_kodu);
        $photos_stmt->execute();
        $photos_result = $photos_stmt->get_result();

        while ($photo = $photos_result->fetch_assoc()) {
            $file_path = '../' . $photo['dosya_yolu'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $photos_stmt->close();

        $query = "DELETE FROM malzemeler WHERE malzeme_kodu = ?";
        // Silinen malzeme bilgilerini al
        $old_material_query = "SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = ?";
        $old_stmt = $connection->prepare($old_material_query);
        $old_stmt->bind_param('i', $malzeme_kodu);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result();
        $old_material = $old_result->fetch_assoc();
        $deleted_name = $old_material['malzeme_ismi'] ?? 'Bilinmeyen Malzeme';
        $old_stmt->close();

        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $malzeme_kodu);
        if ($stmt->execute()) {
            // Log ekleme
            log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_name malzemesi sistemden silindi", 'DELETE');
            $response = ['status' => 'success', 'message' => 'Malzeme başarıyla silindi.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $stmt->error];
        }
        $stmt->close();
    }
}

$connection->close();
echo json_encode($response);
?>
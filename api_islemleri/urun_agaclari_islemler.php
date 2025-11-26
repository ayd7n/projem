<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

// Determine request method and action
$request_method = $_SERVER['REQUEST_METHOD'];
$action = null;

// Check for action in GET parameters
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} 
// Check for action in POST parameters
elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
} 
// Check for action in JSON input
else {
    $input = file_get_contents('php://input');
    $json = json_decode($input, true);
    if ($json && isset($json['action'])) {
        $action = $json['action'];
    }
}

// Initialize response
$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

// Handle GET requests
if ($request_method === 'GET' && $action) {
    if ($action === 'search_product_trees' && isset($_GET['searchTerm'])) {
        $searchTerm = $connection->real_escape_string($_GET['searchTerm']);
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'urun' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%') ORDER BY urun_kodu, bilesen_kodu";
        $result = $connection->query($query);
        
        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $product_trees
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'search_product_trees_paginated' && isset($_GET['searchTerm'])) {
        $searchTerm = $connection->real_escape_string($_GET['searchTerm']);
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'urun' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%') ORDER BY urun_kodu, bilesen_kodu LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);
        
        // Get total count for pagination
        $total_query = "SELECT COUNT(*) as total FROM urun_agaci WHERE agac_turu = 'urun' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%')";
        $total_result = $connection->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;
        
        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $product_trees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'search_essence_trees_paginated' && isset($_GET['searchTerm'])) {
        $searchTerm = $connection->real_escape_string($_GET['searchTerm']);
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'esans' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%') ORDER BY urun_kodu, bilesen_kodu LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);
        
        // Get total count for pagination
        $total_query = "SELECT COUNT(*) as total FROM urun_agaci WHERE agac_turu = 'esans' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%')";
        $total_result = $connection->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;
        
        if ($result) {
            $essence_trees = [];
            while ($row = $result->fetch_assoc()) {
                $essence_trees[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $essence_trees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'get_all') {
        // Fetch all product trees
        $query = "SELECT * FROM urun_agaci ORDER BY urun_kodu, bilesen_kodu";
        $result = $connection->query($query);
        
        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }
            
            // Calculate total distinct products in product trees
            $total_query = "SELECT COUNT(DISTINCT urun_kodu) as total FROM urun_agaci";
            $total_result = $connection->query($total_query);
            $total_row = $total_result->fetch_assoc();
            $total = $total_row ? $total_row['total'] : 0;
            
            $response = [
                'status' => 'success',
                'data' => $product_trees,
                'total' => $total
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'get_product_tree' && isset($_GET['id'])) {
        $urun_agaci_id = (int)$_GET['id'];
        $query = "SELECT * FROM urun_agaci WHERE urun_agaci_id = $urun_agaci_id";
        $result = $connection->query($query);
        $product_tree = $result->fetch_assoc();

        if ($product_tree) {
            $response = ['status' => 'success', 'data' => $product_tree];
        } else {
            $response = ['status' => 'error', 'message' => 'Ürün ağacı bulunamadı.'];
        }
    } elseif ($action === 'get_products') {
        // Fetch all products
        $query = "SELECT urun_kodu, urun_ismi FROM urunler ORDER BY urun_ismi";
        $result = $connection->query($query);
        
        if ($result) {
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $products
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'get_materials') {
        // Fetch all materials
        $query = "SELECT malzeme_kodu, malzeme_ismi, malzeme_turu FROM malzemeler ORDER BY malzeme_ismi";
        $result = $connection->query($query);
        
        if ($result) {
            $materials = [];
            while ($row = $result->fetch_assoc()) {
                $materials[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $materials
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'get_essences') {
        // Fetch all essences
        $query = "SELECT esans_id, esans_kodu, esans_ismi FROM esanslar ORDER BY esans_ismi";
        $result = $connection->query($query);
        
        if ($result) {
            $essences = [];
            while ($row = $result->fetch_assoc()) {
                $essences[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $essences
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'search_essence_trees' && isset($_GET['searchTerm'])) {
        $searchTerm = $connection->real_escape_string($_GET['searchTerm']);
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'esans' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%') ORDER BY urun_kodu, bilesen_kodu";
        $result = $connection->query($query);
        
        if ($result) {
            $essence_trees = [];
            while ($row = $result->fetch_assoc()) {
                $essence_trees[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $essence_trees
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'get_product_trees_paginated') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // Fetch paginated product trees
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'urun' ORDER BY urun_kodu, bilesen_kodu LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);
        
        // Get total count for pagination
        $total_query = "SELECT COUNT(*) as total FROM urun_agaci WHERE agac_turu = 'urun'";
        $total_result = $connection->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;
        
        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $product_trees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    } elseif ($action === 'get_essence_trees_paginated') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // Fetch paginated essence trees
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'esans' ORDER BY urun_kodu, bilesen_kodu LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);
        
        // Get total count for pagination
        $total_query = "SELECT COUNT(*) as total FROM urun_agaci WHERE agac_turu = 'esans'";
        $total_result = $connection->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;
        
        if ($result) {
            $essence_trees = [];
            while ($row = $result->fetch_assoc()) {
                $essence_trees[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $essence_trees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    }
} 
// Handle POST requests
elseif ($request_method === 'POST') {
    // Extract action from either JSON or form data
    $input = file_get_contents('php://input');
    $json = json_decode($input, true);
    
    if ($json && isset($json['action'])) {
        // JSON request - use the decoded values
        $action = $json['action'];
        $urun_kodu = $json['urun_kodu'] ?? null;
        $urun_ismi = $json['urun_ismi'] ?? '';
        $bilesenin_malzeme_turu = $json['bilesenin_malzeme_turu'] ?? '';
        $bilesen_kodu = $json['bilesen_kodu'] ?? '';
        $bilesen_ismi = $json['bilesen_ismi'] ?? '';
        $bilesen_miktari = isset($json['bilesen_miktari']) ? (float)$json['bilesen_miktari'] : 0;
        $urun_agaci_id = $json['urun_agaci_id'] ?? null;
        $agac_turu = $json['agac_turu'] ?? 'urun'; // Add agac_turu from input
    } elseif (isset($_POST['action'])) {
        // Form data request - use $_POST values
        $action = $_POST['action'];
        $urun_kodu = $_POST['urun_kodu'] ?? null;
        $urun_ismi = $_POST['urun_ismi'] ?? '';
        $bilesenin_malzeme_turu = $_POST['bilesenin_malzeme_turu'] ?? '';
        $bilesen_kodu = $_POST['bilesen_kodu'] ?? '';
        $bilesen_ismi = $_POST['bilesen_ismi'] ?? '';
        $bilesen_miktari = isset($_POST['bilesen_miktari']) ? (float)$_POST['bilesen_miktari'] : 0;
        $urun_agaci_id = $_POST['urun_agaci_id'] ?? null;
        $agac_turu = $_POST['agac_turu'] ?? 'urun'; // Add agac_turu from input
    }

    if ($action === 'add_product_tree') {
        if (empty($urun_kodu) || empty($bilesen_kodu)) {
            $response = ['status' => 'error', 'message' => 'Ürün ve bileşen boş olamaz.'];
        } else {
            $urun_kodu = $connection->real_escape_string($urun_kodu);
            $urun_ismi = $connection->real_escape_string($urun_ismi);
            $bilesenin_malzeme_turu = $connection->real_escape_string($bilesenin_malzeme_turu);
            $bilesen_kodu = $connection->real_escape_string($bilesen_kodu);
            $bilesen_ismi = $connection->real_escape_string($bilesen_ismi);
            $agac_turu = $connection->real_escape_string($agac_turu);
            
            $query = "INSERT INTO urun_agaci (urun_kodu, urun_ismi, bilesenin_malzeme_turu, bilesen_kodu, bilesen_ismi, bilesen_miktari, agac_turu) VALUES ('$urun_kodu', '$urun_ismi', '$bilesenin_malzeme_turu', '$bilesen_kodu', '$bilesen_ismi', '$bilesen_miktari', '$agac_turu')";

            if ($connection->query($query)) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "$urun_ismi ürün ağacına $bilesen_ismi bileşeni eklendi", 'CREATE');
                $response = ['status' => 'success', 'message' => 'Ürün ağacı başarıyla eklendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
            }
        }
    } elseif ($action === 'update_product_tree') {
        // Handle both form data and JSON input for urun_agaci_id
        if (isset($_POST['urun_agaci_id'])) {
            $urun_agaci_id = (int)$_POST['urun_agaci_id'];
        } elseif (isset($json['urun_agaci_id'])) {
            $urun_agaci_id = (int)$json['urun_agaci_id'];
        } else {
            $urun_agaci_id = null;
        }
        
        if ($urun_agaci_id && (empty($urun_kodu) || empty($bilesen_kodu))) {
            $response = ['status' => 'error', 'message' => 'Ürün ve bileşen boş olamaz.'];
        } elseif ($urun_agaci_id) {
            $urun_kodu = $connection->real_escape_string($urun_kodu);
            $urun_ismi = $connection->real_escape_string($urun_ismi);
            $bilesenin_malzeme_turu = $connection->real_escape_string($bilesenin_malzeme_turu);
            $bilesen_kodu = $connection->real_escape_string($bilesen_kodu);
            $bilesen_ismi = $connection->real_escape_string($bilesen_ismi);
            $agac_turu = $connection->real_escape_string($agac_turu);
            
            // Eski ürün ağacı bilgilerini al
            $old_tree_query = "SELECT urun_ismi, bilesen_ismi FROM urun_agaci WHERE urun_agaci_id = $urun_agaci_id";
            $old_tree_result = $connection->query($old_tree_query);
            $old_tree = $old_tree_result->fetch_assoc();
            $old_product_name = $old_tree['urun_ismi'] ?? 'Bilinmeyen Ürün';
            $old_component_name = $old_tree['bilesen_ismi'] ?? 'Bilinmeyen Bileşen';

            $query = "UPDATE urun_agaci SET urun_kodu = '$urun_kodu', urun_ismi = '$urun_ismi', bilesenin_malzeme_turu = '$bilesenin_malzeme_turu', bilesen_kodu = '$bilesen_kodu', bilesen_ismi = '$bilesen_ismi', bilesen_miktari = '$bilesen_miktari', agac_turu = '$agac_turu' WHERE urun_agaci_id = $urun_agaci_id";

            if ($connection->query($query)) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "$old_product_name ürün ağacındaki $old_component_name bileşeni $bilesen_ismi olarak güncellendi", 'UPDATE');
                $response = ['status' => 'success', 'message' => 'Ürün ağacı başarıyla güncellendi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Geçersiz ürün ağacı ID.'];
        }
    } elseif ($action === 'delete_product_tree') {
        // Handle both form data and JSON input for urun_agaci_id
        if (isset($_POST['urun_agaci_id'])) {
            $urun_agaci_id = (int)$_POST['urun_agaci_id'];
        } elseif (isset($json['urun_agaci_id'])) {
            $urun_agaci_id = (int)$json['urun_agaci_id'];
        } else {
            $urun_agaci_id = null;
        }
        
        if ($urun_agaci_id) {
            // Silinen ürün ağacı bilgilerini al
            $deleted_tree_query = "SELECT urun_ismi, bilesen_ismi FROM urun_agaci WHERE urun_agaci_id = $urun_agaci_id";
            $deleted_tree_result = $connection->query($deleted_tree_query);
            $deleted_tree = $deleted_tree_result->fetch_assoc();
            $deleted_product_name = $deleted_tree['urun_ismi'] ?? 'Bilinmeyen Ürün';
            $deleted_component_name = $deleted_tree['bilesen_ismi'] ?? 'Bilinmeyen Bileşen';

            $query = "DELETE FROM urun_agaci WHERE urun_agaci_id = $urun_agaci_id";
            if ($connection->query($query)) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_product_name ürün ağacından $deleted_component_name bileşeni silindi", 'DELETE');
                $response = ['status' => 'success', 'message' => 'Ürün ağacı başarıyla silindi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Geçersiz ürün ağacı ID.'];
        }
    }
}



// NEW: Handle GET requests for specific agac_turu (for Vue.js)
if ($request_method === 'GET' && isset($_GET['agac_turu'])) {
    $agac_turu = $connection->real_escape_string($_GET['agac_turu']);
    
    // Validate agac_turu parameter
    if (!in_array($agac_turu, ['urun', 'esans'])) {
        $response = ['status' => 'error', 'message' => 'Geçersiz agac_turu parametresi.'];
    } else {
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = '$agac_turu' ORDER BY urun_kodu, bilesen_kodu";
        $result = $connection->query($query);
        
        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $product_trees
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $connection->error];
        }
    }
}

$connection->close();
echo json_encode($response);
?>
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

    if ($action === 'get_product_bom_status') {
        // Required component types for a product BOM
        $required_types = ['kutu', 'takm', 'etiket', 'paket', 'jelatin', 'esans'];
        
        // Get all products
        $products_query = "SELECT urun_kodu, urun_ismi FROM urunler ORDER BY urun_ismi";
        $products_result = $connection->query($products_query);
        
        $products = [];
        
        while ($product = $products_result->fetch_assoc()) {
            $urun_kodu = $product['urun_kodu'];
            
            // Get existing BOM components for this product
            $bom_query = "SELECT bilesenin_malzeme_turu, bilesen_kodu, bilesen_ismi 
                          FROM urun_agaci 
                          WHERE urun_kodu = ? AND agac_turu = 'urun'";
            $bom_stmt = $connection->prepare($bom_query);
            $bom_stmt->bind_param('i', $urun_kodu);
            $bom_stmt->execute();
            $bom_result = $bom_stmt->get_result();
            
            $existing_types = [];
            $esans_info = null;
            
            while ($bom = $bom_result->fetch_assoc()) {
                $type = strtolower($bom['bilesenin_malzeme_turu']);
                $existing_types[] = $type;
                
                // Store essence info for later check
                if ($type === 'esans') {
                    $esans_info = [
                        'bilesen_kodu' => $bom['bilesen_kodu'],
                        'bilesen_ismi' => $bom['bilesen_ismi']
                    ];
                }
            }
            $bom_stmt->close();
            
            // Calculate missing types
            $missing_types = array_diff($required_types, $existing_types);
            
            // Check if essence has its own BOM
            $esans_bom_status = null; // null = no essence, true = has BOM, false = no BOM
            
            if ($esans_info) {
                // Check if this essence has BOM entries
                $esans_bom_query = "SELECT COUNT(*) as count FROM urun_agaci 
                                    WHERE agac_turu = 'esans' AND urun_ismi = ?";
                $esans_bom_stmt = $connection->prepare($esans_bom_query);
                $esans_bom_stmt->bind_param('s', $esans_info['bilesen_ismi']);
                $esans_bom_stmt->execute();
                $esans_bom_result = $esans_bom_stmt->get_result()->fetch_assoc();
                $esans_bom_status = ($esans_bom_result['count'] > 0);
                $esans_bom_stmt->close();
            }
            
            $products[] = [
                'urun_kodu' => $product['urun_kodu'],
                'urun_ismi' => $product['urun_ismi'],
                'missing_types' => array_values($missing_types),
                'has_esans' => ($esans_info !== null),
                'esans_ismi' => $esans_info ? $esans_info['bilesen_ismi'] : null,
                'esans_has_bom' => $esans_bom_status
            ];
        }
        
        $response = [
            'status' => 'success',
            'data' => $products
        ];
    }
}

$connection->close();
echo json_encode($response);
?>

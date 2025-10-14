<?php
include '../config.php';

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'musteri') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim!']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'add_to_cart') {
    $urun_kodu = $_POST['urun_kodu'];
    $adet = (int)$_POST['adet'];
    
    // Check if product exists and has enough stock
    $check_query = "SELECT * FROM urunler WHERE urun_kodu = ? AND stok_miktari >= ?";
    $check_stmt = $connection->prepare($check_query);
    $check_stmt->bind_param('ii', $urun_kodu, $adet);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Handle cart in session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
        
        $cart = &$_SESSION['cart'];
        
        // Add to cart
        if (isset($cart[$urun_kodu])) {
            $cart[$urun_kodu] += $adet;
        } else {
            $cart[$urun_kodu] = $adet;
        }
        
        $_SESSION['cart'] = $cart;
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Ürün sepete eklendi!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Yeterli stok bulunmamaktadır!'
        ]);
    }
    
    $check_stmt->close();
} elseif ($action === 'remove_from_cart') {
    $urun_kodu = $_POST['urun_kodu'];
    
    if (isset($_SESSION['cart'][$urun_kodu])) {
        unset($_SESSION['cart'][$urun_kodu]);
        echo json_encode(['status' => 'success', 'message' => 'Ürün sepetten kaldırıldı!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ürün sepette bulunamadı!']);
    }
} elseif ($action === 'get_cart_contents') {
    // Return current cart contents with product information
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
    
    $cart_items = array();

    if (!empty($cart)) {
        foreach ($cart as $urun_kodu => $adet) {
            // Get product information
            $product_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
            $product_stmt = $connection->prepare($product_query);
            $product_stmt->bind_param('i', $urun_kodu);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();

            if ($product_result->num_rows > 0) {
                $product = $product_result->fetch_assoc();
                $cart_items[] = array(
                    'urun_kodu' => $urun_kodu,
                    'urun_ismi' => $product['urun_ismi'],
                    'adet' => $adet
                );
            }
        }
    }

    $total_items = count($cart_items);
    
    echo json_encode([
        'status' => 'success',
        'cart_items' => $cart_items,
        'total_items' => $total_items
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek!']);
}

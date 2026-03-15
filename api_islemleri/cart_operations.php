<?php
include '../config.php';

// Check if user is logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'musteri') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erisim!']);
    exit;
}

function get_cart_product($connection, $urun_kodu)
{
    $query = "SELECT urun_ismi, stok_miktari FROM urunler WHERE urun_kodu = ?";
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $urun_kodu);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $product;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'add_to_cart') {
    $urun_kodu = (int) ($_POST['urun_kodu'] ?? 0);
    $adet = (int) ($_POST['adet'] ?? 0);

    if ($urun_kodu <= 0 || $adet <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Lutfen gecerli bir urun ve pozitif adet girin.'
        ]);
        exit;
    }

    $product = get_cart_product($connection, $urun_kodu);
    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        $cart = &$_SESSION['cart'];
        $mevcut_adet = isset($cart[$urun_kodu]) ? (int) $cart[$urun_kodu] : 0;
        $yeni_toplam_adet = $mevcut_adet + $adet;
        $stok_miktari = (int) ($product['stok_miktari'] ?? 0);

        if ($stok_miktari <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Bu urun stokta kalmamis.'
            ]);
            exit;
        }

        if ($yeni_toplam_adet > $stok_miktari) {
            echo json_encode([
                'status' => 'error',
                'message' => "Sepetteki toplam adet mevcut stogu asamaz. En fazla {$stok_miktari} adet ekleyebilirsiniz."
            ]);
            exit;
        }

        if (isset($cart[$urun_kodu])) {
            $cart[$urun_kodu] += $adet;
        } else {
            $cart[$urun_kodu] = $adet;
        }

        $_SESSION['cart'] = $cart;

        echo json_encode([
            'status' => 'success',
            'message' => 'Urun sepete eklendi!',
            'total_different_products' => count($cart)
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Urun bulunamadi!'
        ]);
    }
} elseif ($action === 'remove_from_cart') {
    $urun_kodu = (int) ($_POST['urun_kodu'] ?? 0);

    if (isset($_SESSION['cart'][$urun_kodu])) {
        unset($_SESSION['cart'][$urun_kodu]);
        echo json_encode(['status' => 'success', 'message' => 'Urun sepetten kaldirildi!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Urun sepette bulunamadi!']);
    }
} elseif ($action === 'get_cart_contents') {
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
    $cart_items = array();

    if (!empty($cart)) {
        foreach ($cart as $urun_kodu => $adet) {
            $adet = (int) $adet;
            if ($adet <= 0) {
                continue;
            }

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

            $product_stmt->close();
        }
    }

    echo json_encode([
        'status' => 'success',
        'cart_items' => $cart_items,
        'total_items' => count($cart_items)
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gecersiz istek!']);
}

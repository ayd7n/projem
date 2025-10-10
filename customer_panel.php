<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only customers can access this page
if ($_SESSION['taraf'] !== 'musteri') {
    header('Location: login.php');
    exit;
}

// Check if customer still has login access
$musteri_id = $_SESSION['id'];
$access_check_query = "SELECT giris_yetkisi FROM musteriler WHERE musteri_id = ?";
$access_check_stmt = $connection->prepare($access_check_query);
$access_check_stmt->bind_param('i', $musteri_id);
$access_check_stmt->execute();
$access_result = $access_check_stmt->get_result();

if ($access_result->num_rows > 0) {
    $customer = $access_result->fetch_assoc();
    if ($customer['giris_yetkisi'] != 1) {
        // Customer's access has been revoked, log them out
        session_destroy();
        header('Location: login.php?error=no_access');
        exit;
    }
} else {
    // Customer record doesn't exist
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get customer info
$musteri_id = $_SESSION['id'];
$musteri_query = "SELECT musteri_adi FROM musteriler WHERE musteri_id = ?";
$musteri_stmt = $connection->prepare($musteri_query);
$musteri_stmt->bind_param('i', $musteri_id);
$musteri_stmt->execute();
$musteri_result = $musteri_stmt->get_result();
$musteri = $musteri_result->fetch_assoc();
$musteri_adi = $musteri ? $musteri['musteri_adi'] : 'Müşteri';

// Get available products (stock > 0)
$products_query = "SELECT * FROM urunler WHERE stok_miktari > 0 ORDER BY urun_ismi";
$products_result = $connection->query($products_query);

// Handle adding to cart
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $urun_kodu = $_POST['urun_kodu'];
    $adet = $_POST['adet'];
    
    // Check if product exists and has enough stock
    $check_query = "SELECT * FROM urunler WHERE urun_kodu = ? AND stok_miktari >= ?";
    $check_stmt = $connection->prepare($check_query);
    $check_stmt->bind_param('ii', $urun_kodu, $adet);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Add to cart
        if (isset($cart[$urun_kodu])) {
            $cart[$urun_kodu] += $adet;
        } else {
            $cart[$urun_kodu] = $adet;
        }
        $_SESSION['cart'] = $cart;
        $message = "Ürün sepete eklendi!";
    } else {
        $error = "Yeterli stok bulunmamaktadır!";
    }
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    if (!empty($cart)) {
        // Start transaction
        $connection->autocommit(FALSE);
        
        try {
            // Insert order
            $musteri_id = $_SESSION['id'];
            $aciklama = isset($_POST['order_description']) ? $_POST['order_description'] : '';
            
            $order_query = "INSERT INTO siparisler (musteri_id, musteri_adi, aciklama, olusturan_musteri) 
                            VALUES (?, ?, ?, ?)";
            $order_stmt = $connection->prepare($order_query);
            $order_stmt->bind_param('isss', $musteri_id, $musteri_adi, $aciklama, $_SESSION['kullanici_adi']);
            $order_stmt->execute();
            $siparis_id = $connection->insert_id;
            
            $toplam_adet = 0;
            
            // Insert order items
            foreach ($cart as $urun_kodu => $adet) {
                $urun_query = "SELECT urun_ismi, birim, satis_fiyati FROM urunler WHERE urun_kodu = ?";
                $urun_stmt = $connection->prepare($urun_query);
                $urun_stmt->bind_param('i', $urun_kodu);
                $urun_stmt->execute();
                $urun_result = $urun_stmt->get_result();
                $urun = $urun_result->fetch_assoc();
                
                if ($urun) {
                    $toplam_tutar = $adet * $urun['satis_fiyati'];
                    
                    $order_item_query = "INSERT INTO siparis_kalemleri 
                                         (siparis_id, urun_kodu, urun_ismi, adet, birim, birim_fiyat, toplam_tutar) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $order_item_stmt = $connection->prepare($order_item_query);
                    $order_item_stmt->bind_param('iiisidi', $siparis_id, $urun_kodu, $urun['urun_ismi'], 
                                                 $adet, $urun['birim'], $urun['satis_fiyati'], $toplam_tutar);
                    $order_item_stmt->execute();
                    
                    $toplam_adet += $adet;
                }
            }
            
            // Update total quantity in order
            $update_order_query = "UPDATE siparisler SET toplam_adet = ? WHERE siparis_id = ?";
            $update_order_stmt = $connection->prepare($update_order_query);
            $update_order_stmt->bind_param('ii', $toplam_adet, $siparis_id);
            $update_order_stmt->execute();
            
            $connection->commit();
            unset($_SESSION['cart']); // Clear cart
            $cart = array(); // Reset cart variable
            $success = "Siparişiniz başarıyla oluşturuldu!";
        } catch (Exception $e) {
            $connection->rollback();
            $error = "Sipariş oluşturulurken bir hata oluştu: " . $e->getMessage();
        }
    } else {
        $error = "Sepetiniz boş!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parfüm ERP Sistemi - Müşteri Paneli</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --sidebar-bg: #ffffff;
            --header-bg: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --card-bg: #ffffff;
            --border: #e9ecef;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            box-shadow: var(--shadow);
            transition: var(--transition);
            z-index: 1000;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: var(--header-bg);
            color: white;
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .user-info .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.5rem;
            margin: 0 auto 10px;
        }

        .user-info h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .user-info p {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-category {
            padding: 15px 20px 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 5px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text-primary);
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        .nav-links a:hover, .nav-links a.active {
            background: rgba(67, 97, 238, 0.1);
            border-left: 4px solid var(--primary);
            color: var(--primary);
        }

        .nav-links a i {
            width: 25px;
            font-size: 1.1rem;
            margin-right: 12px;
        }

        .nav-links a span {
            font-size: 0.95rem;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: var(--transition);
        }

        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h1 {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-dropdown {
            position: relative;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 180px;
            background: linear-gradient(135deg, #4361ee 0%, #4895ef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .product-info {
            padding: 20px;
        }

        .product-info h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .product-details {
            margin: 15px 0;
        }

        .product-details p {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .product-details strong {
            color: var(--text-secondary);
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-align: center;
            margin: 15px 0;
        }

        .add-to-cart {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .quantity-input {
            width: 80px;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            text-align: center;
        }

        .btn {
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success);
        }

        .btn-success:hover {
            background: #37b9e0;
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-danger:hover {
            background: #d32f2f;
        }

        /* Cart Section */
        .cart-section {
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        .cart-items {
            margin-bottom: 25px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-info {
            flex: 1;
        }

        .item-info h4 {
            margin-bottom: 5px;
        }

        .item-quantity {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .cart-total {
            display: flex;
            justify-content: flex-end;
            padding: 20px 0;
            border-top: 2px solid var(--border);
            font-size: 1.3rem;
            font-weight: 700;
        }

        .cart-total span:first-child {
            margin-right: 20px;
            color: var(--text-secondary);
        }

        .order-form {
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 30px;
            border: 1px solid var(--border);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            resize: vertical;
            min-height: 120px;
        }

        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .message i {
            font-size: 1.3rem;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header h2, .sidebar-header p, .nav-category, .nav-links span {
                display: none;
            }
            
            .nav-links a {
                justify-content: center;
                padding: 15px;
            }
            
            .nav-links a i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .topbar {
                padding: 15px;
            }
            
            .content-area {
                padding: 20px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .cart-total {
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
        }

        .logout-btn {
            display: block;
            width: calc(100% - 60px);
            margin: 20px 30px;
            padding: 15px;
            background: linear-gradient(135deg, #e63946 0%, #d00000 100%);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            box-shadow: var(--shadow);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(230, 57, 70, 0.3);
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .no-products i {
            font-size: 3rem;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .no-products p {
            font-size: 1.2rem;
            color: var(--text-secondary);
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
        }

        .empty-cart i {
            font-size: 3rem;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .empty-cart p {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Parfüm ERP</h2>
            <p>Müşteri Paneli</p>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['kullanici_adi'], 0, 1)); ?>
                </div>
                <h3><?php echo htmlspecialchars($musteri_adi); ?></h3>
                <p>Müşteri</p>
            </div>
        </div>

        <div class="nav-menu">
            <div class="nav-category">Menü</div>
            <ul class="nav-links">
                <li><a href="#" class="active"><i class="fas fa-home"></i> <span>Ana Sayfa</span></a></li>
                <li><a href="#"><i class="fas fa-shopping-cart"></i> <span>Siparişlerim</span></a></li>
                <li><a href="#"><i class="fas fa-history"></i> <span>Sipariş Geçmişi</span></a></li>
                <li><a href="#"><i class="fas fa-heart"></i> <span>Favorilerim</span></a></li>
                <li><a href="#"><i class="fas fa-comment"></i> <span>Destek Talebi</span></a></li>
                <li><a href="#"><i class="fas fa-question-circle"></i> <span>Yardım</span></a></li>
            </ul>

            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Çıkış Yap
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1>Parfüm ERP Sistemi - Müşteri Paneli</h1>
            <div class="topbar-actions">
                <div class="user-dropdown">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['kullanici_adi'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php if (isset($message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <h2 class="section-title">Stoktaki Ürünler</h2>

            <div class="products-grid">
                <?php if ($products_result->num_rows > 0): ?>
                    <?php while($product = $products_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <i class="fas fa-perfume"></i>
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['urun_ismi']); ?></h3>
                                
                                <div class="product-details">
                                    <p><strong>Birim:</strong> <span><?php echo htmlspecialchars($product['birim']); ?></span></p>
                                    <p><strong>Stok:</strong> <span><?php echo $product['stok_miktari']; ?></span></p>
                                    <p><strong>Kritik Seviye:</strong> <span><?php echo $product['kritik_stok_seviyesi']; ?></span></p>
                                </div>
                                
                                <div class="product-price">
                                    <?php echo number_format($product['satis_fiyati'], 2); ?> ₺
                                </div>
                                
                                <form method="POST">
                                    <input type="hidden" name="urun_kodu" value="<?php echo $product['urun_kodu']; ?>">
                                    <div class="add-to-cart">
                                        <input type="number" class="quantity-input" id="adet_<?php echo $product['urun_kodu']; ?>" 
                                               name="adet" min="1" max="<?php echo $product['stok_miktari']; ?>" value="1" required>
                                        <button type="submit" name="add_to_cart" class="btn">
                                            <i class="fas fa-cart-plus"></i> Sepete Ekle
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <p>Şu anda stokta ürün bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>

            <h2 class="section-title">Sepetiniz</h2>

            <div class="cart-section">
                <?php if (!empty($cart)): ?>
                    <div class="cart-items">
                        <?php 
                        $total_price = 0;
                        foreach ($cart as $urun_kodu => $adet):
                            $product_query = "SELECT urun_ismi, satis_fiyati FROM urunler WHERE urun_kodu = ?";
                            $product_stmt = $connection->prepare($product_query);
                            $product_stmt->bind_param('i', $urun_kodu);
                            $product_stmt->execute();
                            $product_result = $product_stmt->get_result();
                            $product = $product_result->fetch_assoc();
                            
                            if ($product) {
                                $item_total = $adet * $product['satis_fiyati'];
                                $total_price += $item_total;
                        ?>
                            <div class="cart-item">
                                <div class="item-info">
                                    <h4><?php echo htmlspecialchars($product['urun_ismi']); ?></h4>
                                    <div class="item-quantity"><?php echo $adet; ?> x <?php echo number_format($product['satis_fiyati'], 2); ?> ₺</div>
                                </div>
                                <div class="item-price"><?php echo number_format($item_total, 2); ?> ₺</div>
                            </div>
                        <?php 
                            }
                        endforeach;
                        ?>
                    </div>
                    
                    <div class="cart-total">
                        <span>Toplam:</span>
                        <span><?php echo number_format($total_price, 2); ?> ₺</span>
                    </div>
                <?php else: ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Sepetiniz boş. Lütfen ürünlerden bazılarını sepete ekleyin.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($cart)): ?>
                <div class="order-form">
                    <h2 class="section-title">Siparişi Tamamla</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="order_description">Sipariş Açıklaması (Opsiyonel)</label>
                            <textarea id="order_description" name="order_description" placeholder="Siparişinizle ilgili notlarınızı buraya yazabilirsiniz..."></textarea>
                        </div>
                        
                        <button type="submit" name="submit_order" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Siparişi Oluştur
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                event.target !== menuToggle &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        // Highlight active nav link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href').split('/').pop();
                if (currentPage === linkPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
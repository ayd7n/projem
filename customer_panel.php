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
$musteri_id = $_SESSION['user_id'];
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

// Get all available products (stock > 0)
$products_query = "SELECT urun_kodu, urun_ismi FROM urunler WHERE stok_miktari > 0 ORDER BY urun_ismi";
$products_result = $connection->query($products_query);

// Handle adding to cart
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $urun_kodu = $_POST['urun_kodu'];
    $adet = $_POST['adet'];
    
    // Check if product exists and has enough stock
    $check_query = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ? AND stok_miktari >= ?";
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

// Order submission is now handled via AJAX only (order_operations.php)
// Direct PHP handling removed to prevent conflicts
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Müşteri Paneli - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63; /* Deep Purple */
            --secondary: #7c2a99; /* Lighter Purple */
            --accent: #d4af37; /* Gold */
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #fdf8f5; /* Soft Cream */
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827; /* Dark Gray/Black */
            --text-secondary: #6b7280; /* Medium Gray */
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }
        html {
            font-size: 15px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }
        .main-content {
            padding: 20px;
        }
        .page-header {
            margin-bottom: 25px;
        }
        .page-header h1 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        .page-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        .card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.825rem;
        }
        .btn:hover {
             transform: translateY(-2px);
        }
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--secondary);
            box-shadow: 0 10px 20px rgba(74, 14, 99, 0.2);
        }
        .add-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
        }
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            border-left: 5px solid;
        }
        .alert-danger {
            background-color: #fff5f5;
            color: #c53030;
            border-color: #f56565;
        }
        .alert-success {
            background-color: #f0fff4;
            color: #2f855a;
            border-color: #48bb78;
        }
        .product-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-name {
            font-weight: 500;
            font-size: 1.05rem;
            color: var(--primary);
        }
        .add-to-cart-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .quantity-input {
            width: 70px;
            padding: 0.6rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 0.9rem;
            text-align: center;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .quantity-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        .cart-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .item-info h4 {
            margin-bottom: 5px;
        }
        .item-quantity {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        /* Cart panel that slides from right */
        #sepet {
            position: fixed;
            top: 0;
            right: 0;
            width: 320px;
            height: 100%;
            z-index: 1050;
            border-radius: 0;
            box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        #sepet.show {
            transform: translateX(0);
        }
        
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }
        
        .cart-overlay.show {
            display: block;
        }
        
        .empty-cart {
            text-align: center;
            padding: 30px 0;
            color: var(--text-secondary);
        }
        .empty-cart i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: var(--primary);
            opacity: 0.3;
        }
        .cart-total {
            padding: 20px 20px;
            border-top: 2px solid var(--border-color);
            font-size: 1.3rem;
            font-weight: 700;
            text-align: right;
            display: none; /* Hide total since we're hiding pricing */
        }
        
        .order-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .order-filters .btn {
            padding: 8px 12px;
            font-size: 0.85rem;
            border-radius: 20px;
        }
        
        .table th {
            border-top: none;
            border-bottom: 2px solid var(--border-color);
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .table th i {
            margin-right: 6px;
        }
        
        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
        }
        
        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .actions .btn {
            padding: 6px 10px;
            border-radius: 18px;
        }
        
        .no-orders-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }


        .mobile-menu-btn {
            display: none;
        }

        .pagination-container {
            margin-top: 20px;
        }
        
        .pagination {
            justify-content: center;
        }
        
        html {
            scroll-behavior: smooth;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
        }

        @media (max-width: 991.98px) {
            #sepet.collapse.show {
                position: fixed;
                top: 0;
                right: 0;
                width: 320px;
                height: 100%;
                z-index: 1050; /* Higher than navbar */
                border-radius: 0;
                box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            }
            #sepet .card-body {
                overflow-y: auto;
                height: 100%;
            }
        }
    </style>
</head>
<body>


    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="customer_panel.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item active">
                        <a class="nav-link" href="customer_panel.php">Sipariş Paneli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($musteri_adi); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-light cart-toggle-btn" type="button" id="openCartBtn">
                            <i class="fas fa-shopping-cart text-primary"></i> Sepet
                            <span class="badge badge-danger ml-1"><?php echo count($cart); ?></span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
        
        <div class="page-header">
            <div>
                <h1>Müşteri Paneli</h1>
                <p>Hoş geldiniz! Buradan stoktaki ürünlerimizi inceleyebilir, sepetinize ekleyebilir ve siparişinizi kolayca oluşturabilirsiniz.</p>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="customer_panel.php" class="mb-0">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Ürün adıyla ara..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

                    </div>
                </form>
            </div>
        </div>

        <div id="product-list-container">
            <div class="card">
                <div class="card-header">
                    <h2>Stoktaki Ürünler</h2>
                </div>
                <div class="card-body">
                    <div id="product-items-wrapper">
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while($product = $products_result->fetch_assoc()): ?>
                                <div class="product-item" data-name="<?php echo strtolower(htmlspecialchars($product['urun_ismi'])); ?>">
                                    <div class="product-name"><?php echo htmlspecialchars($product['urun_ismi']); ?></div>
                                    <form method="POST" class="add-to-cart-form">
                                        <input type="hidden" name="urun_kodu" value="<?php echo $product['urun_kodu']; ?>">
                                        <input type="number" class="quantity-input" 
                                               name="adet" min="1" value="1" required>
                                        <button type="submit" class="btn btn-primary add-btn" name="add_to_cart" title="Sepete Ekle">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 no-products-message">
                                <i class="fas fa-box-open" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 20px;"></i>
                                <h4>Şu anda stokta ürün bulunmamaktadır.</h4>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center py-5 no-results-message" style="display: none;">
                        <i class="fas fa-search" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 20px;"></i>
                        <h4>Aramanızla eşleşen ürün bulunamadı.</h4>
                        <p class="text-muted">Farklı bir anahtar kelime ile tekrar deneyin.</p>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div id="pagination-container" class="d-flex justify-content-center mt-4">
                        <nav aria-label="Product Pagination">
                            <ul class="pagination justify-content-center" id="product-pagination">
                                <!-- Pagination will be generated by JS -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay for cart -->
        <div class="cart-overlay" id="cartOverlay"></div>
        
        <!-- Shopping Cart - Slides from right -->
        <div class="card" id="sepet">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="fas fa-shopping-cart"></i> Sepet ve Sipariş</h2>
                <button type="button" class="close" id="closeCartBtn" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($cart)): ?>
                <div class="cart-items-container mb-4">
                    <?php
                    foreach ($cart as $urun_kodu => $adet):
                        $product_query_cart = "SELECT urun_ismi FROM urunler WHERE urun_kodu = ?";
                        $product_stmt_cart = $connection->prepare($product_query_cart);
                        $product_stmt_cart->bind_param('i', $urun_kodu);
                        $product_stmt_cart->execute();
                        $product_result_cart = $product_stmt_cart->get_result();
                        $product_cart = $product_result_cart->fetch_assoc();
                        
                        if ($product_cart) {
                    ?>
                        <div class="cart-item p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1 pr-2">
                                    <h4 class="h6 mb-1"><?php echo htmlspecialchars($product_cart['urun_ismi']); ?></h4>
                                    <div class="item-quantity"><span class="badge badge-primary bg-primary"><?php echo $adet; ?> adet</span></div>
                                </div>
                                <a href="#" class="btn btn-outline-danger btn-sm remove-from-cart-btn" data-urun-kodu="<?php echo $urun_kodu; ?>" title="Sil">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php
                        }
                    endforeach;
                    ?>
                </div>
                <hr/>
                <form method="POST" name="submit_order" class="mt-4">
                    <div class="form-group mb-3">
                        <label for="order_description">Sipariş Açıklaması (Opsiyonel)</label>
                        <textarea class="form-control" id="order_description" name="order_description" placeholder="Siparişinizle ilgili notlarınızı buraya yazabilirsiniz..." rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success" name="submit_order">
                        <i class="fas fa-paper-plane"></i> Siparişi Oluştur
                    </button>
                </form>
                <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart text-muted"></i>
                    <h4>Sepetiniz Boş</h4>
                    <p class="text-muted">Sepetinize ürün eklemek için ürünler kısmından seçim yapabilirsiniz.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Past Orders Section -->
        <div class="card" id="gecmis-siparisler">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-history"></i> Geçmiş Siparişlerim</h2>
                <div class="order-filters">
                    <button class="btn <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'btn-primary' : 'btn-outline-primary'; ?>" onclick="filterOrders('all')">Tümü</button>
                    <button class="btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'beklemede') ? 'btn-warning' : 'btn-outline-warning'; ?>" onclick="filterOrders('beklemede')">Beklemede</button>
                    <button class="btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'onaylandi') ? 'btn-success' : 'btn-outline-success'; ?>" onclick="filterOrders('onaylandi')">Onaylandı</button>
                    <button class="btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'iptal_edildi') ? 'btn-danger' : 'btn-outline-danger'; ?>" onclick="filterOrders('iptal_edildi')">İptal Edildi</button>
                    <button class="btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'tamamlandi') ? 'btn-info' : 'btn-outline-info'; ?>" onclick="filterOrders('tamamlandi')">Tamamlandı</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Sipariş No</th>
                                <th><i class="fas fa-calendar"></i> Tarih</th>
                                <th><i class="fas fa-tag"></i> Durum</th>
                                <th><i class="fas fa-boxes"></i> Toplam Adet</th>
                                <th><i class="fas fa-comment"></i> Açıklama</th>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <!-- Orders will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4" id="noOrdersMessage" style="display: none;">
                    <i class="fas fa-inbox fa-3x mb-3" style="color: var(--text-secondary);"></i>
                    <h4>Herhangi bir siparişiniz bulunmuyor.</h4>
                    <p class="text-muted">Dilerseniz yeni bir sipariş oluşturabilirsiniz.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-shopping-cart"></i> 
                        <span id="orderTitleText">Sipariş Detayı</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="orderDescription"><i class="fas fa-comment"></i> Sipariş Açıklaması</label>
                        <textarea class="form-control" id="orderDescription" name="orderDescription" rows="4" placeholder="Siparişinizle ilgili notlarınızı buraya yazabilirsiniz..." readonly></textarea>
                        <input type="hidden" id="orderId" name="orderId">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> Sipariş Kalemleri</label>
                        <div id="orderItemsList" class="border rounded p-3 bg-light">
                            <!-- Order items will be loaded via AJAX -->
                            <div class="text-center p-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Yükleniyor...</span>
                                </div>
                                <p class="mt-2">Sipariş kalemleri yükleniyor...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        <i class="fas fa-times"></i> Kapat
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- jQuery for AJAX functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
    $(document).ready(function() {
        // Determine initial status from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const initialStatus = urlParams.get('status') || 'all';
        
        // Load orders on page load with initial status
        loadOrders(initialStatus);
        
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const sidebar = document.querySelector('.sidebar');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // Highlight active nav link
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-links a');
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href').split('/').pop();
            if (currentPage === linkPage || (currentPage === '' && linkPage === 'index.php') || (currentPage === 'customer_panel.php' && linkPage === '#')) {
                link.classList.add('active');
            }
        });
        
        // Update filter button active states
        function updateFilterButtons(status) {
            $('.order-filters .btn').removeClass('btn-primary btn-outline-primary btn-warning btn-outline-warning btn-success btn-outline-success btn-danger btn-outline-danger btn-info btn-outline-info');
            
            switch(status) {
                case 'all':
                    $('.order-filters .btn[onclick*="filterOrders(\'all\')"]').addClass('btn-primary');
                    $('.order-filters .btn:not([onclick*="filterOrders(\'all\')"])').addClass('btn-outline-primary');
                    break;
                case 'beklemede':
                    $('.order-filters .btn[onclick*="filterOrders(\'beklemede\')"]').addClass('btn-warning').removeClass('btn-outline-warning');
                    $('.order-filters .btn:not([onclick*="filterOrders(\'beklemede\')"])').addClass('btn-outline-warning').removeClass('btn-warning');
                    break;
                case 'onaylandi':
                    $('.order-filters .btn[onclick*="filterOrders(\'onaylandi\')"]').addClass('btn-success').removeClass('btn-outline-success');
                    $('.order-filters .btn:not([onclick*="filterOrders(\'onaylandi\')"])').addClass('btn-outline-success').removeClass('btn-success');
                    break;
                case 'iptal_edildi':
                    $('.order-filters .btn[onclick*="filterOrders(\'iptal_edildi\')"]').addClass('btn-danger').removeClass('btn-outline-danger');
                    $('.order-filters .btn:not([onclick*="filterOrders(\'iptal_edildi\')"])').addClass('btn-outline-danger').removeClass('btn-danger');
                    break;
                case 'tamamlandi':
                    $('.order-filters .btn[onclick*="filterOrders(\'tamamlandi\')"]').addClass('btn-info').removeClass('btn-outline-info');
                    $('.order-filters .btn:not([onclick*="filterOrders(\'tamamlandi\')"])').addClass('btn-outline-info').removeClass('btn-info');
                    break;
                default:
                    $('.order-filters .btn[onclick*="filterOrders(\'all\')"]').addClass('btn-primary');
                    $('.order-filters .btn:not([onclick*="filterOrders(\'all\')"])').addClass('btn-outline-primary');
            }
        }
        
        // Initialize filter buttons based on initial status
        updateFilterButtons(initialStatus);
        
        // Disable form submission for search (use AJAX instead)
        $('form[method="GET"][action="customer_panel.php"]').on('submit', function(e) {
            e.preventDefault();
        });

        // AJAX for adding to cart
        $('form.add-to-cart-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = form.serialize();
            var button = form.find('button[name="add_to_cart"]');
            var originalText = button.html();
            
            // Show loading state
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Ekleniyor...');
            
            $.ajax({
                url: 'api_islemleri/cart_operations.php',
                type: 'POST',
                data: formData + '&action=add_to_cart',
                dataType: 'json',
                success: function(response) {
                        if (response.status === 'success') {
                            // Count number of different products in cart
                            var differentProductsCount = response.total_different_products || 0;
                            if (differentProductsCount === 0) {
                                // If response doesn't contain the count, calculate from the cart UI
                                differentProductsCount = $('.cart-item').length;
                                // If we're adding to an empty cart, it would be 1
                                if (differentProductsCount === 0) {
                                    differentProductsCount = 1;
                                }
                            }
                            
                            // Show SweetAlert with the number of different products
                            Swal.fire({
                                icon: 'success',
                                title: 'Ürün Sepete Eklendi!',
                                text: `Sepette toplam ${differentProductsCount} farklı ürün bulunmaktadır.`,
                                showConfirmButton: false,
                                timer: 2000
                            });

                            // Update the cart UI without page reload (includes badge update)
                            updateCartUI();
                        } else {
                        showAlert(response.message, 'danger');
                    }
                    // Re-enable button
                    button.prop('disabled', false).html(originalText);
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
                    // Re-enable button
                    button.prop('disabled', false).html(originalText);
                }
            });
        });

        // AJAX for removing from cart
        $(document).on('click', '.remove-from-cart-btn', function(e) {
            e.preventDefault();
            
            var urun_kodu = $(this).data('urun-kodu');
            var button = $(this);
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'api_islemleri/cart_operations.php',
                type: 'POST',
                data: {
                    action: 'remove_from_cart',
                    urun_kodu: urun_kodu
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                        // Update cart UI instead of reloading page
                        updateCartUI();
                    } else {
                        showAlert(response.message, 'danger');
                        button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                    button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                }
            });
        });

        // AJAX for submitting order - using event delegation for dynamic content
        $(document).on('submit', 'form[name="submit_order"]', function(e) {
            e.preventDefault();

            var form = $(this);
            var formData = form.serialize() + '&action=submit_order';
            var button = form.find('button[name="submit_order"]');
            var originalText = button.html();

            // Debug: Check what data is being sent
            console.log('Form data being sent:', formData);

            // Show loading state
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sipariş İşleniyor...');

            $.ajax({
                url: 'api_islemleri/order_operations.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Order submission response:', response);
                    if (response.status === 'success') {
                        showAlert(response.message, 'success');
                        // Close the cart and update the UI
                        closeCart();
                        updateCartUI(); // This will refresh the cart UI and badge
                        // Reload orders to show the new order
                        loadOrders('all');
                        // Refresh the page to see direct PHP processing results if any
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                    // Re-enable button
                    button.prop('disabled', false).html(originalText);
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', xhr.responseText, status, error);
                    showAlert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
                    // Re-enable button
                    button.prop('disabled', false).html(originalText);
                }
            });

            return false; // Prevent any form submission
        });

        // Dynamic Product Search & AJAX Loading
        let searchTimeout;
        const searchInput = $('input[name="search"]');

        function loadProducts(page = 1, search = '') {
            const container = $('#product-list-container');
            const url = `api_islemleri/search_products.php?page=${page}&search=${encodeURIComponent(search)}`;
            
            container.html('<div class="card"><div class="card-body text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Yükleniyor...</span></div><p class="mt-2">Ürünler yükleniyor...</p></div></div>');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    container.html(`<div class="card">${response}</div>`);
                    
                    // Re-attach event handlers for the dynamically loaded content
                    attachEventHandlers();
                },
                error: function() {
                    container.html('<div class="card"><div class="card-body text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p>Ürünler yüklenirken bir hata oluştu.</p></div></div>');
                }
            });
        }

        // Live search (as user types)
        searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val();
            searchTimeout = setTimeout(function() {
                if (searchTerm.trim() === '') {
                    // If search is cleared, show all products with pagination
                    initializePagination();
                } else {
                    loadProductsForSearch(1, searchTerm);
                }
            }, 300); // 300ms delay to reduce API calls
        });
        
        searchInput.closest('form').on('submit', function(e) {
            e.preventDefault();
        });

        // Handle AJAX pagination clicks
        $(document).on('click', '#product-list-container .pagination a', function(e) {
            e.preventDefault();
            const url = new URL($(this).attr('href'), window.location.origin + window.location.pathname);
            const page = url.searchParams.get('page');
            const search = searchInput.val();
            loadProducts(page, search);
        });
        
        // Function to set up event handlers using event delegation
        function setupCartEventHandlers() {
            // Use event delegation to handle both initial and dynamically loaded forms
            $(document).off('submit', 'form.add-to-cart-form').on('submit', 'form.add-to-cart-form', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var formData = form.serialize();
                var button = form.find('button[name="add_to_cart"]');
                var originalText = button.html();
                
                // Prevent multiple clicks during processing
                if (button.prop('disabled')) {
                    return false;
                }
                
                // Show loading state
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Ekleniyor...');
                
                $.ajax({
                    url: 'api_islemleri/cart_operations.php',
                    type: 'POST',
                    data: formData + '&action=add_to_cart',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Update cart count by getting the current count and adding the quantity
                            var quantityAdded = parseInt(form.find('input[name="adet"]').val()) || 1;
                            // Find all cart toggle buttons and update their badge
                            $('.cart-toggle-btn .badge').each(function() {
                                var currentText = $(this).text();
                                var currentCount = parseInt(currentText) || 0;
                                $(this).text(currentCount + quantityAdded);
                            });
                            
                            // Count number of different products in cart
                            var differentProductsCount = response.total_different_products || 0;
                            if (differentProductsCount === 0) {
                                // If response doesn't contain the count, calculate from the cart UI
                                differentProductsCount = $('.cart-item').length;
                                // If we're adding to an empty cart, it would be 1
                                if (differentProductsCount === 0) {
                                    differentProductsCount = 1;
                                }
                            }
                            
                            // Show SweetAlert with the number of different products
                            Swal.fire({
                                icon: 'success',
                                title: 'Ürün Sepete Eklendi!',
                                text: `Sepette toplam ${differentProductsCount} farklı ürün bulunmaktadır.`,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            
                            // Update the cart UI without page reload
                            updateCartUI();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                        // Re-enable button
                        button.prop('disabled', false).html(originalText);
                    },
                    error: function() {
                        showAlert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
                        // Re-enable button
                        button.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            // Use event delegation for remove from cart functionality
            $(document).off('click', '.remove-from-cart-btn').on('click', '.remove-from-cart-btn', function(e) {
                e.preventDefault();
                
                var urun_kodu = $(this).data('urun-kodu');
                var button = $(this);

                $.ajax({
                    url: 'api_islemleri/cart_operations.php',
                    type: 'POST',
                    data: {
                        action: 'remove_from_cart',
                        urun_kodu: urun_kodu
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            // Update cart UI instead of reloading page
                            updateCartUI();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                    }
                });
            });
        }
        
        // Initialize event handlers when the document is ready
        setupCartEventHandlers();


        // Function to show alerts
        function showAlert(message, type) {
            // Remove existing alerts
            $('.alert').remove();

            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            var alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <i class="fas ${icon} fa-2x mr-3"></i>
                    <div>
                        ${message}
                    </div>
                    <button type="button" class="close ml-auto" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

            // Insert alert after page header
            $('.page-header').after(alertHtml);

            // Auto-hide messages after 3 seconds
            setTimeout(function() {
                $('.alert').fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
        
        // Function to update cart UI with current cart contents
        function updateCartUI() {
            $.ajax({
                url: 'api_islemleri/cart_operations.php',
                type: 'POST',
                data: { action: 'get_cart_contents' },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.cart_items) {
                        // Update cart count badge
                        var totalItems = response.total_items || 0;
                        $('.cart-toggle-btn .badge').text(totalItems);
                        
                        // Update cart content if the cart is visible
                        var cartHtml = '<div class="card-body">';
                        
                        if (response.cart_items.length > 0) {
                            cartHtml += '<div class="cart-items-container mb-4">';
                            
                            $.each(response.cart_items, function(index, item) {
                                cartHtml += `
                                    <div class="cart-item p-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1 pr-2">
                                                <h4 class="h6 mb-1">${item.urun_ismi}</h4>
                                                <div class="item-quantity"><span class="badge badge-primary bg-primary">${item.adet} adet</span></div>
                                            </div>
                                            <a href="#" class="btn btn-outline-danger btn-sm remove-from-cart-btn" data-urun-kodu="${item.urun_kodu}" title="Sil">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            cartHtml += '</div><hr/>';
                            
                            // Add order form
                            cartHtml += `
                                <form method="POST" name="submit_order" class="mt-4">
                                    <div class="form-group mb-3">
                                        <label for="order_description">Sipariş Açıklaması (Opsiyonel)</label>
                                        <textarea class="form-control" id="order_description" name="order_description" placeholder="Siparişinizle ilgili notlarınızı buraya yazabilirsiniz..." rows="3"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success submit-order-btn" name="submit_order">
                                        <i class="fas fa-paper-plane"></i> Siparişi Oluştur
                                    </button>
                                </form>
                            `;
                        } else {
                            cartHtml += `
                                <div class="empty-cart">
                                    <i class="fas fa-shopping-cart text-muted"></i>
                                    <h4>Sepetiniz Boş</h4>
                                    <p class="text-muted">Sepetinize ürün eklemek için ürünler kısmından seçim yapabilirsiniz.</p>
                                </div>
                            `;
                        }
                        
                        cartHtml += '</div>';
                        
                        // Update the cart content
                        $('#sepet .card-body').replaceWith(cartHtml);
                        
                        // Re-attach event handlers for the newly added elements
                        setupCartEventHandlers();
                        
                        // Check if cart is currently open and update accordingly
                        if ($('#sepet').hasClass('show')) {
                            // Cart is open, ensure the content is visible
                        }
                    }
                },
                error: function() {
                    console.error('Error fetching cart contents');
                    // At least update the badge count by calculating from existing UI
                    var cartCount = parseInt($('.cart-toggle-btn .badge').text()) || 0;
                    // Keep the existing count as is since we couldn't fetch updated data
                }
            });
        }
        
        // Function to open the cart panel
        function openCart() {
            $('#sepet').addClass('show');
            $('#cartOverlay').addClass('show');
            $('body').css('overflow', 'hidden'); // Prevent background scrolling
        }
        
        // Function to close the cart panel
        function closeCart() {
            $('#sepet').removeClass('show');
            $('#cartOverlay').removeClass('show');
            $('body').css('overflow', 'auto'); // Re-enable scrolling
        }
        
        // Attach cart toggle event handlers when the document is ready
        $(document).ready(function() {
            // Click handlers for cart open buttons
            $(document).on('click', '#openCartBtn', function(e) {
                e.preventDefault();
                openCart();
            });
            
            // Click handler for closing cart
            $(document).on('click', '#closeCartBtn', function(e) {
                e.preventDefault();
                closeCart();
            });
            
            // Click handler for overlay to close cart
            $(document).on('click', '#cartOverlay', function(e) {
                if (e.target === this) {
                    closeCart();
                }
            });
            
            // Keyboard handler for closing cart (ESC key)
            $(document).keydown(function(e) {
                if (e.key === 'Escape' && $('#sepet').hasClass('show')) {
                    closeCart();
                }
            });
        });
        
        // Load orders by status
        function loadOrders(status) {
            // Show loading indicator
            $('#ordersTableBody').html(`
                <tr>
                    <td colspan="6" class="text-center p-4">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Yükleniyor...</span>
                            </div>
                            <span class="ml-2">Siparişler yükleniyor...</span>
                        </div>
                    </td>
                </tr>
            `);
            
            $.ajax({
                url: 'api_islemleri/musteri_siparis_islemler.php?action=get_orders&status=' + status,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var ordersHtml = '';
                        var orders = response.data;
                        
                        if (orders.length > 0) {
                            $.each(orders, function(index, order) {
                                // Set status badge style based on status
                                var statusClass = '';
                                var statusText = '';
                                
                                switch(order.durum) {
                                    case 'beklemede':
                                        statusClass = 'badge-warning text-dark';
                                        statusText = 'Beklemede';
                                        break;
                                    case 'onaylandi':
                                        statusClass = 'badge-success';
                                        statusText = 'Onaylandı';
                                        break;
                                    case 'iptal_edildi':
                                        statusClass = 'badge-danger';
                                        statusText = 'İptal Edildi';
                                        break;
                                    case 'tamamlandi':
                                        statusClass = 'badge-info';
                                        statusText = 'Tamamlandı';
                                        break;
                                    default:
                                        statusClass = 'badge-secondary';
                                        statusText = order.durum;
                                }
                                
                                ordersHtml += `
                                    <tr>
                                        <td>#${order.siparis_id}</td>
                                        <td>${new Date(order.tarih).toLocaleString('tr-TR')}</td>
                                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                                        <td>${order.toplam_adet || 0}</td>
                                        <td>${order.aciklama ? order.aciklama.substring(0, 30) + (order.aciklama.length > 30 ? '...' : '') : '-'}</td>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm view-order-btn" 
                                                    data-id="${order.siparis_id}" 
                                                    data-status="${order.durum}"
                                                    title="Siparişi Görüntüle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            ${order.durum === 'beklemede' ? 
                                                `<button class="btn btn-danger btn-sm cancel-order-btn" 
                                                        data-id="${order.siparis_id}" 
                                                        title="Siparişi İptal Et">
                                                    <i class="fas fa-times"></i>
                                                </button>` : ''}
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            $('#ordersTableBody').html('');
                            $('#noOrdersMessage').show();
                            return;
                        }
                        
                        $('#ordersTableBody').html(ordersHtml);
                        $('#noOrdersMessage').hide();
                        
                        // Add event listeners for view order buttons
                        $('.view-order-btn').on('click', function() {
                            var orderId = $(this).data('id');
                            var status = $(this).data('status');
                            openOrderModal(orderId, status);
                        });
                        
                        // Add event listeners for cancel order buttons
                        $(document).on('click', '.cancel-order-btn', function(e) {
                            e.preventDefault(); // Prevent any default behavior
                            e.stopPropagation(); // Stop event bubbling

                            var $button = $(this);
                            var orderId = $button.data('id');

                            if ($button.prop('disabled')) return; // If already processing, return

                            Swal.fire({
                                title: 'Emin misiniz?',
                                text: 'Siparişi iptal etmek istediğinize emin misiniz?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Evet',
                                cancelButtonText: 'İptal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                                    $.ajax({
                                        url: 'api_islemleri/musteri_siparis_islemler.php',
                                        type: 'POST',
                                        data: {
                                            action: 'cancel_order',
                                            siparis_id: orderId
                                        },
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.status === 'success') {
                                                showAlert(response.message, 'success');
                                                // Reload orders to reflect the change
                                                loadOrders('all');
                                            } else {
                                                showAlert(response.message, 'danger');
                                                $button.prop('disabled', false).html('<i class="fas fa-times"></i>');
                                            }
                                        },
                                        error: function() {
                                            showAlert('Sipariş iptal edilirken bir hata oluştu.', 'danger');
                                            $button.prop('disabled', false).html('<i class="fas fa-times"></i>');
                                        }
                                    });
                                }
                            });
                        });
                    } else {
                        $('#ordersTableBody').html(`
                            <tr>
                                <td colspan="6" class="text-center p-4 text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> ${response.message}
                                </td>
                            </tr>
                        `);
                        $('#noOrdersMessage').hide();
                    }
                },
                error: function() {
                    $('#ordersTableBody').html(`
                        <tr>
                            <td colspan="6" class="text-center p-4 text-danger">
                                <i class="fas fa-exclamation-circle"></i> Siparişler yüklenirken bir hata oluştu.
                            </td>
                        </tr>
                    `);
                    $('#noOrdersMessage').hide();
                }
            });
        }
        
        // Filter orders by status
        window.filterOrders = function(status) {
            loadOrders(status);
            updateFilterButtons(status);
        };
        
        // Open order modal for viewing/editing
        function openOrderModal(orderId, status) {
            // Load order details
            $.ajax({
                url: 'api_islemleri/musteri_siparis_islemler.php?action=get_order&siparis_id=' + orderId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var order = response.data;
                        $('#orderId').val(order.siparis_id);
                        $('#orderDescription').val(order.aciklama || '');
                        
                        // Set appropriate title based on status
                        var statusText = '';
                        switch(status) {
                            case 'beklemede':
                                statusText = 'Beklemede';
                                break;
                            case 'onaylandi':
                                statusText = 'Onaylandı';
                                break;
                            case 'iptal_edildi':
                                statusText = 'İptal Edildi';
                                break;
                            case 'tamamlandi':
                                statusText = 'Tamamlandı';
                                break;
                            default:
                                statusText = status;
                        }
                        
                        $('#orderTitleText').html(`Sipariş #${order.siparis_id} <small class="text-light">(${statusText})</small>`);
                        
                        // Load order items
                        loadOrderItems(orderId);
                        
                        // Show/hide buttons based on status
                        if (status === 'beklemede') {
                            $('#cancelOrderBtn').show();
                            $('#updateOrderBtn').show();
                        } else {
                            $('#cancelOrderBtn').hide();
                            $('#updateOrderBtn').hide();
                        }
                        
                        $('#orderModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Sipariş detayı yüklenirken bir hata oluştu.', 'danger');
                }
            });
        }
        
        // Load order items
        function loadOrderItems(orderId) {
            $.ajax({
                url: 'api_islemleri/musteri_siparis_islemler.php?action=get_order_items&siparis_id=' + orderId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var itemsHtml = '';
                        var items = response.data;
                        
                        if (items.length > 0) {
                            itemsHtml += '<div class="table-responsive"><table class="table table-borderless mb-0"><thead class="bg-light"><tr><th>Ürün</th><th class="text-center">Adet</th><th class="text-center">Birim</th></tr></thead><tbody>';
                            
                            $.each(items, function(index, item) {
                                itemsHtml += `
                                    <tr class="border-bottom">
                                        <td>${item.urun_ismi}</td>
                                        <td class="text-center"><span class="badge badge-primary">${item.adet}</span></td>
                                        <td class="text-center">${item.birim}</td>
                                    </tr>
                                `;
                            });
                            
                            itemsHtml += '</tbody></table></div>';
                        } else {
                            itemsHtml = '<div class="text-center py-3"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><p class="text-muted mb-0">Sipariş kalemi bulunmuyor.</p></div>';
                        }
                        
                        $('#orderItemsList').html(itemsHtml);
                    } else {
                        $('#orderItemsList').html('<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p class="mb-0">Sipariş kalemleri yüklenirken hata oluştu.</p></div>');
                    }
                },
                error: function() {
                    $('#orderItemsList').html('<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p class="mb-0">Sipariş kalemleri yüklenirken hata oluştu.</p></div>');
                }
            });
        }
        
        // Pagination functionality for products
        let productCurrentPage = 1;
        const itemsPerPage = 3; // Show 3 products per page for better visibility
        let allProducts = []; // Store all product elements
        let currentTotalProducts = 0;
        
        // Initialize pagination when page loads
        function initializePagination() {
            // Get all product items
            const productItems = $('.product-item').get();
            allProducts = productItems;
            currentTotalProducts = productItems.length;
            
            if (currentTotalProducts <= itemsPerPage) {
                // If we have fewer products than items per page, hide pagination
                $('#pagination-container').hide();
                // Show all products
                $('.product-item').show();
                return;
            }
            
            // Show pagination controls
            $('#pagination-container').show();
            
            // Calculate total pages
            const totalPages = Math.ceil(currentTotalProducts / itemsPerPage);
            
            // Generate pagination
            generatePagination(totalPages);
            
            // Show first page
            showPage(1);
        }
        
        function generatePagination(totalPages) {
            const paginationEl = $('#product-pagination');
            paginationEl.empty();
            
            // Previous button
            const prevPage = productCurrentPage > 1 ? productCurrentPage - 1 : 1;
            paginationEl.append(`<li class="page-item ${productCurrentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${prevPage}"><i class="fas fa-chevron-left"></i> Önceki</a>
            </li>`);
            
            // First page
            if (productCurrentPage > 3) {
                paginationEl.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
                if (productCurrentPage > 4) {
                    paginationEl.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
            }
            
            // Pages around current page
            for (let i = Math.max(1, productCurrentPage - 2); i <= Math.min(totalPages, productCurrentPage + 2); i++) {
                paginationEl.append(`<li class="page-item ${i === productCurrentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`);
            }
            
            // Last page
            if (productCurrentPage < totalPages - 2) {
                if (productCurrentPage < totalPages - 3) {
                    paginationEl.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
                paginationEl.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
            }
            
            // Next button
            const nextPage = productCurrentPage < totalPages ? productCurrentPage + 1 : totalPages;
            paginationEl.append(`<li class="page-item ${productCurrentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${nextPage}">Sonraki <i class="fas fa-chevron-right"></i></a>
            </li>`);
        }
        
        function showPage(page) {
            productCurrentPage = page;
            
            // Hide all products
            $('.product-item').hide();
            
            // Calculate start and end indices
            const startIndex = (page - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            
            // Show products for current page
            for (let i = startIndex; i < endIndex && i < allProducts.length; i++) {
                $(allProducts[i]).show();
            }
            
            // Update pagination
            generatePagination(Math.ceil(currentTotalProducts / itemsPerPage));
        }
        
        // Handle pagination click event
        $(document).on('click', '#product-pagination .page-link', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (!isNaN(page)) {
                showPage(page);
                // Scroll to top of product list
                $('#product-list-container')[0].scrollIntoView({ behavior: 'smooth' });
            }
        });
        
        // Reinitialize pagination when search happens
        function reinitializePagination() {
            setTimeout(function() {
                initializePagination();
            }, 100); // Small delay to ensure DOM is updated
        }
        
        // Update loadProducts function to include pagination reinitialization
        function loadProductsForSearch(page = 1, search = '') {
            const container = $('#product-list-container');
            const url = `api_islemleri/search_products.php?page=${page}&search=${encodeURIComponent(search)}`;
            
            container.html('<div class="card"><div class="card-body text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Yükleniyor...</span></div><p class="mt-2">Ürünler yükleniyor...</p></div></div>');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    container.html(`<div class="card">${response}</div>`);
                    
                    // Reinitialize pagination for search results
                    reinitializePagination();
                },
                error: function() {
                    container.html('<div class="card"><div class="card-body text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p>Ürünler yüklenirken bir hata oluştu.</p></div></div>');
                }
            });
        }
        
        // Initialize pagination when document is ready
        $(window).on('load', function() {
            initializePagination();
        });

    });
    </script>
</body>
</html>

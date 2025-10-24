<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only staff can access this page
if ($_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

$siparis_id = isset($_GET['siparis_id']) ? (int)$_GET['siparis_id'] : 0;

if ($siparis_id <= 0) {
    header('Location: musteri_siparisleri.php');
    exit;
}

$message = '';
$error = '';

// Fetch order details
$order_query = "SELECT * FROM siparisler WHERE siparis_id = ?";
$stmt = $connection->prepare($order_query);
$stmt->bind_param('i', $siparis_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header('Location: musteri_siparisleri.php');
    exit;
}

// Fetch order items
$items_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param('i', $siparis_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Fetch all products for dropdown
$products_query = "SELECT * FROM urunler WHERE stok_miktari > 0 ORDER BY urun_ismi";
$products_result = $connection->query($products_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Sipariş Detayı - Parfüm ERP</title>
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
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="musteri_siparisleri.php">Sipariş Listesi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
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
                <h1>Sipariş Detayı #<?php echo $siparis_id; ?></h1>
                <p>Sipariş bilgileri ve kalemleri</p>
            </div>
        </div>

        <!-- Messages will be displayed via AJAX -->
        <div id="message-container">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-file-invoice"></i> Sipariş Bilgileri</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><strong>Sipariş ID:</strong></label>
                            <div class="form-control-plaintext"><?php echo $order['siparis_id']; ?></div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Müşteri:</strong></label>
                            <div class="form-control-plaintext"><?php echo htmlspecialchars($order['musteri_adi']); ?></div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Durum:</strong></label>
                            <div class="form-control-plaintext">
                                <span class="status-badge 
                                    <?php 
                                    switch($order['durum']) {
                                        case 'beklemede': echo 'badge-warning bg-warning text-dark'; break;
                                        case 'onaylandi': echo 'badge-success bg-success'; break;
                                        case 'iptal_edildi': echo 'badge-danger bg-danger'; break;
                                        case 'tamamlandi': echo 'badge-info bg-info'; break;
                                        default: echo 'badge-secondary bg-secondary'; break;
                                    }
                                    ?>">
                                    <?php 
                                    switch($order['durum']) {
                                        case 'beklemede': echo '📋 Beklemede'; break;
                                        case 'onaylandi': echo '✅ Onaylandı'; break;
                                        case 'iptal_edildi': echo '❌ İptal Edildi'; break;
                                        case 'tamamlandi': echo '🏁 Tamamlandı'; break;
                                        default: echo $order['durum']; break;
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><strong>Tarih:</strong></label>
                            <div class="form-control-plaintext"><?php echo $order['tarih']; ?></div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Toplam Adet:</strong></label>
                            <div class="form-control-plaintext"><?php echo $order['toplam_adet']; ?></div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Oluşturan:</strong></label>
                            <div class="form-control-plaintext"><?php echo htmlspecialchars($order['olusturan_musteri']); ?></div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><strong>Onaylayan Personel:</strong></label>
                            <div class="form-control-plaintext"><?php echo htmlspecialchars($order['onaylayan_personel_adi']); ?></div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Onay Tarihi:</strong></label>
                            <div class="form-control-plaintext"><?php echo $order['onay_tarihi']; ?></div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Açıklama:</strong></label>
                            <div class="form-control-plaintext"><?php echo htmlspecialchars($order['aciklama']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-list"></i> Sipariş Kalemleri</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="order-items" class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-barcode"></i> Ürün Kodu</th>
                                <th><i class="fas fa-tag"></i> Ürün İsmi</th>
                                <th><i class="fas fa-shopping-bag"></i> Adet</th>
                                <th><i class="fas fa-ruler"></i> Birim</th>
                                <th><i class="fas fa-money-bill-wave"></i> Birim Fiyat</th>
                                <th><i class="fas fa-calculator"></i> Toplam Tutar</th>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $items_result->data_seek(0); // Reset the result pointer to the beginning
                            while ($item = $items_result->fetch_assoc()): ?>
                                <tr id="order-item-<?php echo $item['urun_kodu']; ?>">
                                    <td><?php echo $item['urun_kodu']; ?></td>
                                    <td><?php echo htmlspecialchars($item['urun_ismi']); ?></td>
                                    <td><?php echo $item['adet']; ?></td>
                                    <td><?php echo htmlspecialchars($item['birim']); ?></td>
                                    <td><?php echo number_format($item['birim_fiyat'], 2); ?> TL</td>
                                    <td><?php echo number_format($item['toplam_tutar'], 2); ?> TL</td>
                                    <td class="actions">
                                        <a href="#update-form-<?php echo $item['urun_kodu']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </a>
                                        <form style="display: inline;" class="d-inline">
                                            <input type="hidden" name="item_id" value="<?php echo $item['urun_kodu']; ?>">
                                            <button type="button" class="btn btn-danger btn-sm delete-item-btn">
                                                <i class="fas fa-trash"></i> Sil
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr id="update-form-<?php echo $item['urun_kodu']; ?>" style="display:none;">
                                    <td colspan="7">
                                        <div class="card mt-3">
                                            <div class="card-body">
                                                <h5 class="card-title">Sipariş Kalemi Güncelle</h5>
                                                <form class="update-item-form">
                                                    <input type="hidden" name="item_id" value="<?php echo $item['urun_kodu']; ?>">
                                                    <div class="form-group">
                                                        <label for="urun_kodu_<?php echo $item['urun_kodu']; ?>">Ürün:</label>
                                                        <select class="form-control" id="urun_kodu_<?php echo $item['urun_kodu']; ?>" name="urun_kodu" required>
                                                            <option value="">Ürün Seçin</option>
                                                            <?php 
                                                            $products_result->data_seek(0);
                                                            while($product = $products_result->fetch_assoc()): ?>
                                                                <option value="<?php echo $product['urun_kodu']; ?>" 
                                                                    <?php echo $product['urun_kodu'] == $item['urun_kodu'] ? 'selected' : ''; ?>>
                                                                    <?php echo $product['urun_kodu']; ?> - <?php echo htmlspecialchars($product['urun_ismi']); ?> (Stok: <?php echo $product['stok_miktari']; ?>)
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="adet_<?php echo $item['urun_kodu']; ?>">Adet:</label>
                                                        <input type="number" class="form-control" id="adet_<?php echo $item['urun_kodu']; ?>" name="adet" value="<?php echo $item['adet']; ?>" min="1" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-sync-alt"></i> Güncelle
                                                    </button>
                                                    <button type="button" class="btn btn-secondary" onclick="hideUpdateForm(<?php echo $item['urun_kodu']; ?>)">
                                                        <i class="fas fa-times"></i> İptal
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-plus-circle"></i> Yeni Sipariş Kalemi Ekle</h2>
            </div>
            <div class="card-body">
                <form id="add-item-form">
                    <input type="hidden" id="siparis_id" name="siparis_id" value="<?php echo $siparis_id; ?>">
                    <div class="form-group">
                        <label for="urun_kodu">Ürün:</label>
                        <select class="form-control" id="urun_kodu" name="urun_kodu" required>
                            <option value="">Ürün Seçin</option>
                            <?php 
                            $products_result->data_seek(0);
                            while($product = $products_result->fetch_assoc()): ?>
                                <option value="<?php echo $product['urun_kodu']; ?>" data-urun-ismi="<?php echo htmlspecialchars($product['urun_ismi']); ?>" data-birim="<?php echo htmlspecialchars($product['birim']); ?>" data-satis-fiyati="<?php echo $product['satis_fiyati']; ?>">
                                    <?php echo $product['urun_kodu']; ?> - <?php echo htmlspecialchars($product['urun_ismi']); ?> (Stok: <?php echo $product['stok_miktari']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="adet">Adet:</label>
                        <input type="number" class="form-control" id="adet" name="adet" min="1" required>
                    </div>
                    
                    <button type="submit" id="add-item-btn" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Kalem Ekle
                    </button>
                    <div id="add-item-loading" class="d-none ml-2">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        İşlem yapılıyor...
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="musteri_siparisleri.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Sipariş Listesine Dön
            </a>
        </div>
    </div>

    <!-- jQuery for AJAX functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
    $(document).ready(function() {
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
            if (currentPage === linkPage || (currentPage === '' && linkPage === 'index.php') || (currentPage === 'musteri_siparisleri.php' && linkPage === 'musteri_siparisleri.php')) {
                link.classList.add('active');
            }
        });
    });

    function showUpdateForm(itemId) {
        // Hide all other update forms
        document.querySelectorAll('[id^="update-form-\"]').forEach(function(form) {
            form.style.display = 'none';
        });
        
        // Show the selected update form
        document.getElementById('update-form-' + itemId).style.display = 'table-row';
    }
    
    function hideUpdateForm(itemId) {
        document.getElementById('update-form-' + itemId).style.display = 'none';
    }
    
    // Add click event to all edit buttons
    document.querySelectorAll('a[href^="#update-form-\"]').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            showUpdateForm(targetId.split('-')[2]);
        });
    });
    
    // Function to display messages
    function showMessage(message, type) {
        const messageHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>'}
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        $('#message-container').html(messageHtml);
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    }
    
    // AJAX form submission for adding new order item
    $('#add-item-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Show loading indicator and disable button
        $('#add-item-btn').prop('disabled', true);
        $('#add-item-loading').removeClass('d-none');
        
        $.ajax({
            url: 'api_islemleri/order_item_operations.php',
            type: 'POST',
            data: {
                action: 'add_order_item',
                siparis_id: $('#siparis_id').val(),
                urun_kodu: $('#urun_kodu').val(),
                adet: $('#adet').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Show success message
                    showMessage(response.message, 'success');
                    
                    // Add the new item to the table without page refresh
                    addOrderItemToTable(response.item_data);
                    
                    // Reset the form
                    $('#urun_kodu').val('');
                    $('#adet').val('');
                    
                    // Update the order total
                    updateOrderTotal();
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText, status, error);
                showMessage('Bir hata oluştu: ' + error, 'danger');
            },
            complete: function() {
                // Hide loading indicator and re-enable button
                $('#add-item-btn').prop('disabled', false);
                $('#add-item-loading').addClass('d-none');
            }
        });
    });
    
    // AJAX for deleting order items
    $(document).on('click', '.delete-item-btn', function(e) {
        e.preventDefault();
        
        const form = $(this).closest('form');
        const itemId = form.find('input[name="item_id"]').val();
        
        Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu kalemi silmek istediğinizden emin misiniz?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api_islemleri/order_item_operations.php',
                    type: 'POST',
                    data: {
                        action: 'delete_order_item',
                        siparis_id: <?php echo $siparis_id; ?>,
                        urun_kodu: itemId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showMessage(response.message, 'success');

                            // Remove the row from the table
                            $(`#order-item-${itemId}`).remove();

                            // Update the order total
                            updateOrderTotal();
                        } else {
                            showMessage(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText, status, error);
                        showMessage('Bir hata oluştu: ' + error, 'danger');
                    }
                });
            }
        });
    });
    
    // AJAX for updating order items
    $(document).on('submit', '.update-item-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const itemId = form.find('input[name="item_id"]').val();
        const urunKodu = form.find('select[name="urun_kodu"]').val();
        const adet = form.find('input[name="adet"]').val();
        
        $.ajax({
            url: 'api_islemleri/order_item_operations.php',
            type: 'POST',
            data: {
                action: 'update_order_item',
                siparis_id: <?php echo $siparis_id; ?>,
                old_urun_kodu: itemId,
                urun_kodu: urunKodu,
                adet: adet
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showMessage(response.message, 'success');
                    
                    // Update the row in the table
                    updateOrderItemInTable(response.item_data);
                    
                    // Hide the update form
                    hideUpdateForm(itemId);
                    
                    // Update the order total
                    updateOrderTotal();
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText, status, error);
                showMessage('Bir hata oluştu: ' + error, 'danger');
            }
        });
    });
    
    // Function to add new order item to the table
    function addOrderItemToTable(itemData) {
        const newRow = `
            <tr id="order-item-${itemData.urun_kodu}">
                <td>${itemData.urun_kodu}</td>
                <td>${itemData.urun_ismi}</td>
                <td>${itemData.adet}</td>
                <td>${itemData.birim}</td>
                <td>${itemData.birim_fiyat} TL</td>
                <td>${itemData.toplam_tutar} TL</td>
                <td class="actions">
                    <a href="#update-form-${itemData.urun_kodu}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Düzenle
                    </a>
                    <form style="display: inline;" class="d-inline">
                        <input type="hidden" name="item_id" value="${itemData.urun_kodu}">
                        <button type="button" class="btn btn-danger btn-sm delete-item-btn">
                            <i class="fas fa-trash"></i> Sil
                        </button>
                    </form>
                </td>
            </tr>
            <tr id="update-form-${itemData.urun_kodu}" style="display:none;">
                <td colspan="7">
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Sipariş Kalemi Güncelle</h5>
                            <form class="update-item-form">
                                <input type="hidden" name="item_id" value="${itemData.urun_kodu}">
                                <div class="form-group">
                                    <label for="urun_kodu_${itemData.urun_kodu}">Ürün:</label>
                                    <select class="form-control" id="urun_kodu_${itemData.urun_kodu}" name="urun_kodu" required>
                                        <option value="">Ürün Seçin</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="adet_${itemData.urun_kodu}">Adet:</label>
                                    <input type="number" class="form-control" id="adet_${itemData.urun_kodu}" name="adet" value="${itemData.adet}" min="1" required>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-sync-alt"></i> Güncelle
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="hideUpdateForm(${itemData.urun_kodu})">
                                    <i class="fas fa-times"></i> İptal
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
        `;
        
        // Add the new row to the table body
        $('tbody').append(newRow);
    }
    
    // Function to update an existing order item in the table
    function updateOrderItemInTable(itemData) {
        // Update the main row
        $(`#order-item-${itemData.old_urun_kodu}`).html(`
            <td>${itemData.urun_kodu}</td>
            <td>${itemData.urun_ismi}</td>
            <td>${itemData.adet}</td>
            <td>${itemData.birim}</td>
            <td>${itemData.birim_fiyat} TL</td>
            <td>${itemData.toplam_tutar} TL</td>
            <td class="actions">
                <a href="#update-form-${itemData.urun_kodu}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Düzenle
                </a>
                <form style="display: inline;" class="d-inline">
                    <input type="hidden" name="item_id" value="${itemData.urun_kodu}">
                    <button type="button" class="btn btn-danger btn-sm delete-item-btn">
                        <i class="fas fa-trash"></i> Sil
                    </button>
                </form>
            </td>
        `).attr('id', `order-item-${itemData.urun_kodu}`);
        
        // Update the update form row ID
        $(`#update-form-${itemData.old_urun_kodu}`).attr('id', `update-form-${itemData.urun_kodu}`);
    }
    
    // Function to update the order total display
    function updateOrderTotal() {
        // Update total quantity by counting items in the table
        let totalQuantity = 0;
        const rows = document.querySelectorAll('#order-items tbody tr[id^="order-item-"]');

        rows.forEach(row => {
            const quantityCell = row.cells[2]; // 3rd column (Adet)
            if (quantityCell && quantityCell.textContent) {
                const quantity = parseInt(quantityCell.textContent.trim());
                if (!isNaN(quantity)) {
                    totalQuantity += quantity;
                }
            }
        });

        // Update the toplam adet display in the order info card
        const totalElement = document.querySelector('.card-body .col-md-4 .form-control-plaintext');
        if (totalElement && totalElement.previousElementSibling && totalElement.previousElementSibling.textContent.includes('Toplam Adet:')) {
            totalElement.textContent = totalQuantity;
        }

        // Alternative: Find by class or more specific selector
        const formGroups = document.querySelectorAll('.card-body .form-group');
        formGroups.forEach(group => {
            const label = group.querySelector('label');
            if (label && label.textContent.includes('Toplam Adet:')) {
                const valueDiv = group.querySelector('.form-control-plaintext');
                if (valueDiv) {
                    valueDiv.textContent = totalQuantity;
                }
            }
        });
    }
    </script>
</body>
</html>

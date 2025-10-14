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

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Update order status
        $siparis_id = $_POST['siparis_id'];
        $durum = $_POST['durum'];

        if ($durum === 'onaylandi') {
            // When approving, set the approval details
            $personel_id = $_SESSION['id'];
            $personel_adi = $_SESSION['kullanici_adi'];

            $query = "UPDATE siparisler SET durum = ?, onaylayan_personel_id = ?, onaylayan_personel_adi = ?, onay_tarihi = NOW() WHERE siparis_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('sisi', $durum, $personel_id, $personel_adi, $siparis_id);
        } else {
            $query = "UPDATE siparisler SET durum = ? WHERE siparis_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param('si', $durum, $siparis_id);
        }

        if ($stmt->execute()) {
            $message = "Sipariş başarıyla güncellendi.";

            // If the order is approved, update stock
            if ($durum === 'onaylandi') {
                // Get order items
                $items_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = ?";
                $items_stmt = $connection->prepare($items_query);
                $items_stmt->bind_param('i', $siparis_id);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();

                // Update stock for each item
                while ($item = $items_result->fetch_assoc()) {
                    $update_stock_query = "UPDATE urunler SET stok_miktari = stok_miktari - ? WHERE urun_kodu = ?";
                    $update_stock_stmt = $connection->prepare($update_stock_query);
                    $update_stock_stmt->bind_param('ii', $item['adet'], $item['urun_kodu']);
                    $update_stock_stmt->execute();
                    $update_stock_stmt->close();

                    // Add stock movement record
                    $movement_query = "INSERT INTO stok_hareket_kayitlari (stok_turu, kod, isim, birim, miktar, yon, hareket_turu, ilgili_belge_no, aciklama, kaydeden_personel_id, kaydeden_personel_adi) 
                                      VALUES ('urun', ?, ?, ?, ?, 'cikis', 'cikis', ?, 'Müşteri siparişi', ?, ?)";
                    $movement_stmt = $connection->prepare($movement_query);
                    $movement_stmt->bind_param('ssdissi', $item['urun_kodu'], $item['urun_ismi'], $item['birim'], $item['adet'], $siparis_id, $_SESSION['id'], $_SESSION['kullanici_adi']);
                    $movement_stmt->execute();
                    $movement_stmt->close();
                }

                $items_stmt->close();
            }
        } else {
            $error = "Sipariş güncellenirken hata oluştu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Build search filters
$where_clauses = [];
$params = [];
$param_types = '';

// Status filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'beklemede';
if ($filter !== 'tum') {
    $where_clauses[] = "durum = ?";
    switch ($filter) {
        case 'beklemede': $params[] = 'beklemede'; $param_types .= 's'; break;
        case 'onaylandi': $params[] = 'onaylandi'; $param_types .= 's'; break;
        case 'iptal_edildi': $params[] = 'iptal_edildi'; $param_types .= 's'; break;
        case 'tamamlandi': $params[] = 'tamamlandi'; $param_types .= 's'; break;
    }
}

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $where_clauses[] = "(siparis_id LIKE ? OR musteri_adi LIKE ? OR olusturan_musteri LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= 'sss';
}

// Date range filter
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

if (!empty($date_from) && !empty($date_to)) {
    $where_clauses[] = "DATE(tarih) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $param_types .= 'ss';
} elseif (!empty($date_from)) {
    $where_clauses[] = "DATE(tarih) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
} elseif (!empty($date_to)) {
    $where_clauses[] = "DATE(tarih) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = '';
if (!empty($where_clauses)) {
    $where_clause = "WHERE " . implode(' AND ', $where_clauses);
}

$orders_query = "SELECT * FROM siparisler $where_clause ORDER BY tarih DESC";

if (!empty($params)) {
    $stmt = $connection->prepare($orders_query);
    if ($stmt) {
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $orders_result = $stmt->get_result();
        $stmt->close();
    } else {
        $error = "SQL sorgusu prepare edilemedi: " . $connection->error;
        $orders_result = false;
    }
        } else {
            $orders_result = $connection->query($orders_query);
        }

// Fetch order items for display
$orders_with_items = [];
if ($orders_result && $orders_result->num_rows > 0) {
    while ($order = $orders_result->fetch_assoc()) {
        // Get first few order items for display
        $items_query = "SELECT urun_ismi, adet, birim
                       FROM siparis_kalemleri
                       WHERE siparis_id = ?
                       ORDER BY urun_kodu
                       LIMIT 3";
        $items_stmt = $connection->prepare($items_query);
        $items_stmt->bind_param('i', $order['siparis_id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();

        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = htmlspecialchars($item['urun_ismi']) . ' (' . $item['adet'] . ' ' . htmlspecialchars($item['birim']) . ')';
        }
        $items_stmt->close();

        // Get total items count
        $total_items_query = "SELECT COUNT(*) as total FROM siparis_kalemleri WHERE siparis_id = ?";
        $total_stmt = $connection->prepare($total_items_query);
        $total_stmt->bind_param('i', $order['siparis_id']);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $total_row = $total_result->fetch_assoc();
        $total_items = $total_row['total'];
        $total_stmt->close();

        $order['items_preview'] = $items;
        $order['total_items'] = $total_items;
        $orders_with_items[] = $order;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Müşteri Siparişleri - Parfüm ERP</title>
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
                        <a class="nav-link" href="navigation.php">Ana Sayfa</a>
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
                <h1>Müşteri Siparişleri</h1>
                <p>Bu panel üzerinden müşteri siparişlerini yönetebilir, durumlarını güncelleyebilir ve takip edebilirsiniz.</p>
            </div>
        </div>

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

        <!-- Status Filters -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-filter"></i> Filtreler</h2>
            </div>
            <div class="card-body">
                <div class="order-filters mb-3">
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['filter' => 'beklemede'])); ?>" class="btn <?php echo $filter === 'beklemede' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-clock"></i> Bekleyenler
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['filter' => 'onaylandi'])); ?>" class="btn <?php echo $filter === 'onaylandi' ? 'btn-success' : 'btn-outline-success'; ?>">
                        <i class="fas fa-check"></i> Onaylanmış
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['filter' => 'iptal_edildi'])); ?>" class="btn <?php echo $filter === 'iptal_edildi' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                        <i class="fas fa-times"></i> İptal Edilmiş
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['filter' => 'tamamlandi'])); ?>" class="btn <?php echo $filter === 'tamamlandi' ? 'btn-info' : 'btn-outline-info'; ?>">
                        <i class="fas fa-check-double"></i> Tamamlanmış
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['filter' => 'tum'])); ?>" class="btn <?php echo !$filter || $filter === 'tum' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-list"></i> Tümü
                    </a>
                </div>

                <!-- Search and Date Filters -->
                <form method="GET" class="mb-0">
                    <!-- Keep existing filters in hidden fields -->
                    <?php if ($filter !== 'tum'): ?>
                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <?php endif; ?>

                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Arama (Sipariş ID, Müşteri Adı veya Oluşturan)" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="Başlangıç Tarihi">
                        <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="Bitiş Tarihi">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrele
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-table"></i> Sipariş Listesi</h2>
            </div>
            <div class="card-body">
                <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> Sipariş No</th>
                                    <th><i class="fas fa-user"></i> Müşteri</th>
                                    <th><i class="fas fa-calendar"></i> Tarih</th>
                                    <th><i class="fas fa-tag"></i> Durum</th>
                                    <th><i class="fas fa-boxes"></i> Ürün Kalemleri</th>
                                    <th><i class="fas fa-sort-numeric-up"></i> Toplam Adet</th>
                                    <th><i class="fas fa-user"></i> Oluşturan</th>
                                    <th><i class="fas fa-user-check"></i> Onaylayan</th>
                                    <th><i class="fas fa-comment"></i> Açıklama</th>
                                    <th><i class="fas fa-cogs"></i> İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders_with_items as $order): ?>
                                    <tr>
                                        <td><strong><?php echo $order['siparis_id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($order['musteri_adi']); ?></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($order['tarih'])); ?></td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <?php if (!empty($order['items_preview'])): ?>
                                                <?php foreach ($order['items_preview'] as $index => $item): ?>
                                                    <small class="d-block"><?php echo $item; ?></small>
                                                    <?php if ($index >= 2 && $order['total_items'] > 3): ?>
                                                        <small class="text-primary">+<?php echo $order['total_items'] - 3; ?> daha...</small>
                                                        <?php break; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <small class="text-muted">Kalem yok</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="font-weight-bold"><?php echo $order['toplam_adet']; ?></td>
                                        <td><?php echo htmlspecialchars($order['olusturan_musteri']); ?></td>
                                        <td><?php echo htmlspecialchars($order['onaylayan_personel_adi'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($order['aciklama'] ?: '-'); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="siparis_detay.php?siparis_id=<?php echo $order['siparis_id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Görüntüle
                                                </a>

                                                <?php if ($order['durum'] === 'beklemede'): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Bu siparişi onaylamak istediğinizden emin misiniz? Stok hareketi oluşturulacak.');">
                                                        <input type="hidden" name="siparis_id" value="<?php echo $order['siparis_id']; ?>">
                                                        <input type="hidden" name="durum" value="onaylandi">
                                                        <button type="submit" name="update" class="btn btn-success btn-sm">
                                                            <i class="fas fa-check"></i> Onayla
                                                        </button>
                                                    </form>

                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Bu siparişi iptal etmek istediğinizden emin misiniz?');">
                                                        <input type="hidden" name="siparis_id" value="<?php echo $order['siparis_id']; ?>">
                                                        <input type="hidden" name="durum" value="iptal_edildi">
                                                        <button type="submit" name="update" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-times"></i> İptal
                                                        </button>
                                                    </form>
                                                <?php elseif ($order['durum'] === 'onaylandi'): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Bu siparişi tamamlamak istediğinizden emin misiniz?');">
                                                        <input type="hidden" name="siparis_id" value="<?php echo $order['siparis_id']; ?>">
                                                        <input type="hidden" name="durum" value="tamamlandi">
                                                        <button type="submit" name="update" class="btn btn-info btn-sm">
                                                            <i class="fas fa-check-double"></i> Tamamla
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-orders-container">
                        <i class="fas fa-shopping-cart fa-3x mb-3" style="color: var(--text-secondary);"></i>
                        <h4>Sipariş Bulunamadı</h4>
                        <p class="text-muted">Seçilen kriterlere uygun sipariş bulunamadı. Farklı filtre seçenekleri deneyebilirsiniz.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- jQuery for AJAX functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
            if (currentPage === linkPage || (currentPage === '' && linkPage === 'index.php') || (currentPage === 'navigation.php' && linkPage === 'navigation.php')) {
                link.classList.add('active');
            }
        });
    });
    </script>
</body>
</html>

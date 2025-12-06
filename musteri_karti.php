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

// Page-level permission check
if (!yetkisi_var('page:view:musteriler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Get customer ID from URL parameter
$musteri_id = isset($_GET['musteri_id']) ? (int)$_GET['musteri_id'] : 0;

if ($musteri_id <= 0) {
    // If no customer ID provided, show customer selection
    $customers_query = "SELECT musteri_id, musteri_adi FROM musteriler ORDER BY musteri_adi";
    $customers_result = $connection->query($customers_query);
    $customers = [];
    while ($customer = $customers_result->fetch_assoc()) {
        $customers[] = $customer;
    }
    ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Müşteri Kartı Seçimi - Parfüm ERP</title>
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

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }

        .container {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.825rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-id-card"></i> Müşteri Kartı Seçimi</h2>
            </div>
            <div class="card-body">
                <form action="musteri_karti.php" method="get">
                    <div class="form-group">
                        <label for="musteri_id">Müşteri Seçin:</label>
                        <select name="musteri_id" id="musteri_id" class="form-control" required>
                            <option value="">-- Müşteri Seçin --</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['musteri_id']; ?>"><?php echo htmlspecialchars($customer['musteri_adi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Müşteri Kartını Görüntüle</button>
                    <a href="navigation.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Geri Dön</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}

// Get customer information
$customer_query = "SELECT * FROM musteriler WHERE musteri_id = ?";
$customer_stmt = $connection->prepare($customer_query);
$customer_stmt->bind_param('i', $musteri_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    die('Müşteri bulunamadı.');
}

// Get all orders for this customer (excluding cancelled orders)
$orders_query = "SELECT * FROM siparisler WHERE musteri_id = ? AND durum != 'iptal_edildi' ORDER BY tarih DESC";
$orders_stmt = $connection->prepare($orders_query);
$orders_stmt->bind_param('i', $musteri_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    // Get order items for this order
    $items_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = ?";
    $items_stmt = $connection->prepare($items_query);
    $items_stmt->bind_param('i', $order['siparis_id']);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    $order['items'] = [];
    while ($item = $items_result->fetch_assoc()) {
        $order['items'][] = $item;
    }
    $items_stmt->close();

    $orders[] = $order;
}
$orders_stmt->close();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Müşteri Kartı - <?php echo htmlspecialchars($customer['musteri_adi']); ?> - Parfüm ERP</title>
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

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }

        .print-container {
            padding: 20px;
            max-width: 210mm; /* A4 width */
            margin: 0 auto;
            background-color: white;
            min-height: 297mm; /* A4 height */
        }

        .customer-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid var(--primary);
            margin-bottom: 30px;
        }

        .customer-header h1 {
            font-size: 1.8rem;
            color: var(--primary);
            margin: 0;
        }

        .customer-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary);
        }

        .info-row {
            margin-bottom: 12px;
            display: flex;
        }

        .info-label {
            font-weight: bold;
            width: 150px;
            color: var(--primary);
            flex-shrink: 0;
        }

        .order-card {
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .order-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }

        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-beklemede {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-onaylandi {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-tamamlandi {
            background-color: #d4edda;
            color: #155724;
        }

        .status-iptal_edildi {
            background-color: #f8d7da;
            color: #721c24;
        }

        .order-details {
            margin-bottom: 15px;
        }

        .order-detail-row {
            margin-bottom: 8px;
            display: flex;
        }

        .detail-label {
            font-weight: bold;
            color: var(--text-secondary);
            width: 120px;
            flex-shrink: 0;
        }

        .order-items {
            margin-top: 15px;
        }

        .items-header {
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--border-color);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .items-table th, .items-table td {
            border: 1px solid var(--border-color);
            padding: 8px;
            text-align: center;
        }

        .items-table th {
            background-color: var(--primary);
            color: white;
        }

        .items-table .item-name {
            text-align: left;
            width: 40%;
        }

        .items-table .item-quantity {
            width: 15%;
        }

        .items-table .item-unit {
            width: 15%;
        }

        .items-table .item-price {
            width: 15%;
        }

        .items-table .item-total {
            width: 15%;
        }

        .no-orders {
            text-align: center;
            padding: 30px;
            color: var(--text-secondary);
            font-style: italic;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            font-size: 1.2rem;
        }

        .print-btn:hover {
            background-color: var(--secondary);
            transform: scale(1.05);
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                background-color: white;
            }

            .print-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i>
    </button>
    
    <div class="print-container">
        <div class="customer-header">
            <h1>MÜŞTERİ KARTI</h1>
            <h2><?php echo htmlspecialchars($customer['musteri_adi']); ?></h2>
        </div>
        
        <div class="customer-info">
            <div class="info-row">
                <span class="info-label">Müşteri Adı:</span>
                <span><?php echo htmlspecialchars($customer['musteri_adi']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Vergi/TC No:</span>
                <span><?php echo htmlspecialchars($customer['vergi_no_tc'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefon:</span>
                <span><?php echo htmlspecialchars($customer['telefon'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefon 2:</span>
                <span><?php echo htmlspecialchars($customer['telefon_2'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">E-posta:</span>
                <span><?php echo htmlspecialchars($customer['e_posta'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Adres:</span>
                <span><?php echo htmlspecialchars($customer['adres'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Açıklama:</span>
                <span><?php echo htmlspecialchars($customer['aciklama_notlar'] ?: '-'); ?></span>
            </div>
        </div>
        
        <h3 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            Sipariş Geçmişi
        </h3>
        
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-title">Sipariş No: <?php echo $order['siparis_id']; ?></div>
                        <div class="order-status 
                            <?php 
                            switch($order['durum']) {
                                case 'beklemede': echo 'status-beklemede'; break;
                                case 'onaylandi': echo 'status-onaylandi'; break;
                                case 'tamamlandi': echo 'status-tamamlandi'; break;
                                case 'iptal_edildi': echo 'status-iptal_edildi'; break;
                                default: echo 'status-beklemede';
                            }
                            ?>">
                            <?php
                            switch($order['durum']) {
                                case 'beklemede': echo '📋 Beklemede'; break;
                                case 'onaylandi': echo '✅ Onaylandı'; break;
                                case 'tamamlandi': echo '🏁 Tamamlandı'; break;
                                case 'iptal_edildi': echo '❌ İptal Edildi'; break;
                                default: echo $order['durum'];
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="order-detail-row">
                            <span class="detail-label">Tarih:</span>
                            <span><?php echo date('d.m.Y H:i', strtotime($order['tarih'])); ?></span>
                        </div>
                        <div class="order-detail-row">
                            <span class="detail-label">Oluşturan:</span>
                            <span><?php echo htmlspecialchars($order['olusturan_musteri'] ?: '-'); ?></span>
                        </div>
                        <?php if ($order['onaylayan_personel_adi']): ?>
                        <div class="order-detail-row">
                            <span class="detail-label">Onaylayan:</span>
                            <span><?php echo htmlspecialchars($order['onaylayan_personel_adi']); ?></span>
                        </div>
                        <div class="order-detail-row">
                            <span class="detail-label">Onay Tarihi:</span>
                            <span><?php echo $order['onay_tarihi'] ? date('d.m.Y H:i', strtotime($order['onay_tarihi'])) : '-'; ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="order-detail-row">
                            <span class="detail-label">Toplam Adet:</span>
                            <span><?php echo $order['toplam_adet']; ?></span>
                        </div>
                        <?php if ($order['aciklama']): ?>
                        <div class="order-detail-row">
                            <span class="detail-label">Açıklama:</span>
                            <span><?php echo htmlspecialchars($order['aciklama']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-items">
                        <div class="items-header">Sipariş Kalemleri</div>
                        <?php if (count($order['items']) > 0): ?>
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th class="item-name">Ürün Adı</th>
                                        <th class="item-quantity">Adet</th>
                                        <th class="item-unit">Birim</th>
                                        <th class="item-price">Birim Fiyat</th>
                                        <th class="item-total">Toplam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td class="item-name"><?php echo htmlspecialchars($item['urun_ismi']); ?></td>
                                        <td><?php echo $item['adet']; ?></td>
                                        <td><?php echo htmlspecialchars($item['birim']); ?></td>
                                        <td><?php echo $item['birim_fiyat'] ? number_format($item['birim_fiyat'], 2, ',', '.') : '-'; ?></td>
                                        <td><?php echo $item['toplam_tutar'] ? number_format($item['toplam_tutar'], 2, ',', '.') : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-orders">Bu siparişte ürün kalemi bulunmamaktadır.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-orders">Bu müşteriye ait sipariş bulunmamaktadır.</div>
        <?php endif; ?>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
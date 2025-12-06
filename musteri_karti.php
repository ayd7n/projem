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
$musteri_id = isset($_GET['musteri_id']) ? (int) $_GET['musteri_id'] : 0;

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
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
                rel="stylesheet">
            <style>
                :root {
                    --primary: #7c3aed;
                    --primary-light: #a855f7;
                    --secondary: #6b7280;
                    --success: #10b981;
                    --warning: #f59e0b;
                    --danger: #ef4444;
                    --bg: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
                    --card-bg: #ffffff;
                    --border: #e9d5ff;
                    --text-primary: #1f2937;
                    --text-secondary: #6b7280;
                }

                body {
                    font-family: 'Inter', sans-serif;
                    background: var(--bg);
                    color: var(--text-primary);
                    font-size: 14px;
                }

                .container {
                    max-width: 800px;
                    padding: 30px 15px;
                }

                .card {
                    background: var(--card-bg);
                    border-radius: 8px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                    border: 1px solid var(--border);
                    overflow: hidden;
                }

                .card-header {
                    background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
                    padding: 16px 20px;
                    border: none;
                }

                .card-header h2 {
                    font-size: 18px;
                    font-weight: 600;
                    margin: 0;
                    color: white;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                .card-body {
                    padding: 24px;
                }

                .form-group label {
                    font-weight: 600;
                    font-size: 13px;
                    color: var(--text-primary);
                    margin-bottom: 6px;
                }

                .form-control {
                    padding: 10px 12px;
                    border: 1px solid var(--border);
                    border-radius: 6px;
                    font-size: 14px;
                }

                .form-control:focus {
                    border-color: var(--primary);
                    box-shadow: 0 0 0 3px rgba(107, 70, 193, 0.1);
                }

                .btn {
                    padding: 10px 18px;
                    font-size: 13px;
                    font-weight: 600;
                    border-radius: 6px;
                    transition: all 0.2s;
                }

                .btn-primary {
                    background: var(--primary);
                    border: none;
                    color: white;
                }

                .btn-primary:hover {
                    background: var(--primary-light);
                    transform: translateY(-1px);
                }

                .btn-secondary {
                    background: white;
                    border: 1px solid var(--border);
                    color: var(--text-primary);
                }

                .btn-secondary:hover {
                    background: var(--bg);
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
                                            <option value="<?php echo $customer['musteri_id']; ?>">
                                                <?php echo htmlspecialchars($customer['musteri_adi']); ?>
                                            </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Görüntüle</button>
                            <a href="navigation.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Geri</a>
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
$total_orders = 0;
$total_products = 0;
$last_order_date = null;
$status_beklemede = 0;
$status_onaylandi = 0;
$status_tamamlandi = 0;

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
        $total_products += $item['adet'];
    }
    $items_stmt->close();

    $orders[] = $order;
    $total_orders++;

    // Count order statuses
    switch ($order['durum']) {
        case 'beklemede':
            $status_beklemede++;
            break;
        case 'onaylandi':
            $status_onaylandi++;
            break;
        case 'tamamlandi':
            $status_tamamlandi++;
            break;
    }

    if (!$last_order_date || $order['tarih'] > $last_order_date) {
        $last_order_date = $order['tarih'];
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #7c3aed;
            --primary-light: #a855f7;
            --secondary: #6b7280;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
            --card-bg: #ffffff;
            --border: #e9d5ff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
            color: var(--text-primary);
            font-size: 13px;
            line-height: 1.5;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(107, 70, 193, 0.3);
            transition: all 0.2s;
        }

        .print-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
        }

        .container {
            max-width: 1400px;
            padding: 20px 15px;
        }

        /* Header kompakt */
        .customer-header {
            background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.25);
        }

        .customer-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: white;
            margin: 0 0 4px 0;
            letter-spacing: -0.5px;
        }

        .customer-header .subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
        }

        /* Özet bilgi kartları */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .summary-icon.primary {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .summary-icon.gold {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .summary-icon.success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .summary-icon.warning {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
        }

        .summary-icon.info {
            background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }

        .summary-icon.success-dark {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }

        .summary-content {
            flex: 1;
        }

        .summary-label {
            font-size: 11px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }

        /* Müşteri bilgileri kompakt */
        .customer-info {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 18px 20px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid transparent;
            border-image: linear-gradient(90deg, #7c3aed 0%, #ec4899 100%);
            border-image-slice: 1;
            display: inline-block;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 10px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: var(--bg);
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .info-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 10px rgba(124, 58, 237, 0.2);
        }

        .info-content {
            flex: 1;
            min-width: 0;
        }

        .info-label {
            font-size: 10px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 1px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            word-break: break-word;
        }

        /* Sipariş bölümü */
        .orders-section {
            margin-top: 20px;
        }

        .order-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px 18px;
            margin-bottom: 14px;
            transition: all 0.2s;
        }

        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .order-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .order-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-beklemede {
            background: #fef3c7;
            color: #92400e;
        }

        .status-onaylandi {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-tamamlandi {
            background: #d1fae5;
            color: #065f46;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 8px;
            margin-bottom: 12px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            background: var(--bg);
            border-radius: 4px;
            font-size: 12px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 11px;
        }

        .detail-value {
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Tablo kompakt */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
        }

        .items-table thead {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(236, 72, 153, 0.1));
        }

        .items-table th {
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #7c3aed;
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
        }

        .items-table tr:hover {
            background: var(--bg);
        }

        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
            background: var(--bg);
            border-radius: 8px;
            border: 2px dashed var(--border);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px 10px;
            }

            .summary-cards {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

        @media print {
            .print-btn {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .order-card:hover {
                transform: none;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i>
    </button>

    <div class="container">
        <!-- Header -->
        <div class="customer-header">
            <h1><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($customer['musteri_adi']); ?></h1>
            <div class="subtitle">Müşteri Detay Kartı</div>
        </div>

        <!-- Özet Bilgiler -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-icon primary">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Toplam Sipariş</div>
                    <div class="summary-value"><?php echo $total_orders; ?></div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon gold">
                    <i class="fas fa-box"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Toplam Ürün</div>
                    <div class="summary-value"><?php echo $total_products; ?> Adet</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon success">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Son Sipariş</div>
                    <div class="summary-value">
                        <?php echo $last_order_date ? date('d.m.Y', strtotime($last_order_date)) : '-'; ?>
                    </div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Beklemede</div>
                    <div class="summary-value"><?php echo $status_beklemede; ?></div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon info">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Onaylandı</div>
                    <div class="summary-value"><?php echo $status_onaylandi; ?></div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon success-dark">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Tamamlandı</div>
                    <div class="summary-value"><?php echo $status_tamamlandi; ?></div>
                </div>
            </div>
        </div>

        <!-- Müşteri Bilgileri -->
        <div class="customer-info">
            <div class="section-title"><i class="fas fa-info-circle"></i> Müşteri Bilgileri</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Müşteri Adı</div>
                        <div class="info-value"><?php echo htmlspecialchars($customer['musteri_adi']); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Vergi/TC No</div>
                        <div class="info-value"><?php echo htmlspecialchars($customer['vergi_no_tc'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Telefon</div>
                        <div class="info-value"><?php echo htmlspecialchars($customer['telefon'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Telefon 2</div>
                        <div class="info-value"><?php echo htmlspecialchars($customer['telefon_2'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">E-posta</div>
                        <div class="info-value"><?php echo htmlspecialchars($customer['e_posta'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Adres</div>
                        <div class="info-value"><?php echo htmlspecialchars($customer['adres'] ?: '-'); ?></div>
                    </div>
                </div>
                <?php if ($customer['aciklama_notlar']): ?>
                        <div class="info-item" style="grid-column: 1 / -1;">
                            <div class="info-icon">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Açıklama</div>
                                <div class="info-value"><?php echo htmlspecialchars($customer['aciklama_notlar']); ?></div>
                            </div>
                        </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Siparişler -->
        <div class="orders-section">
            <div class="section-title"><i class="fas fa-history"></i> Sipariş Geçmişi</div>

            <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-title">
                                        <i class="fas fa-file-invoice"></i> Sipariş #<?php echo $order['siparis_id']; ?>
                                    </div>
                                    <div class="order-status 
                                <?php
                                switch ($order['durum']) {
                                    case 'beklemede':
                                        echo 'status-beklemede';
                                        break;
                                    case 'onaylandi':
                                        echo 'status-onaylandi';
                                        break;
                                    case 'tamamlandi':
                                        echo 'status-tamamlandi';
                                        break;
                                    default:
                                        echo 'status-beklemede';
                                }
                                ?>">
                                        <?php
                                        switch ($order['durum']) {
                                            case 'beklemede':
                                                echo '⏳ Beklemede';
                                                break;
                                            case 'onaylandi':
                                                echo '✅ Onaylandı';
                                                break;
                                            case 'tamamlandi':
                                                echo '🎉 Tamamlandı';
                                                break;
                                            default:
                                                echo $order['durum'];
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="order-details">
                                    <div class="detail-item">
                                        <i class="fas fa-calendar" style="color: var(--primary);"></i>
                                        <span class="detail-label">Tarih:</span>
                                        <span class="detail-value"><?php echo date('d.m.Y H:i', strtotime($order['tarih'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-user" style="color: var(--primary);"></i>
                                        <span class="detail-label">Oluşturan:</span>
                                        <span
                                            class="detail-value"><?php echo htmlspecialchars($order['olusturan_musteri'] ?: '-'); ?></span>
                                    </div>
                                    <?php if ($order['onaylayan_personel_adi']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-user-check" style="color: var(--success);"></i>
                                                <span class="detail-label">Onaylayan:</span>
                                                <span
                                                    class="detail-value"><?php echo htmlspecialchars($order['onaylayan_personel_adi']); ?></span>
                                            </div>
                                    <?php endif; ?>
                                    <div class="detail-item">
                                        <i class="fas fa-cubes" style="color: var(--gold);"></i>
                                        <span class="detail-label">Toplam:</span>
                                        <span class="detail-value"><?php echo $order['toplam_adet']; ?> adet</span>
                                    </div>
                                </div>

                                <?php if ($order['aciklama']): ?>
                                        <div class="detail-item" style="margin-top: 8px; width: 100%;">
                                            <i class="fas fa-comment" style="color: var(--info);"></i>
                                            <span class="detail-label">Not:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($order['aciklama']); ?></span>
                                        </div>
                                <?php endif; ?>

                                <?php if (count($order['items']) > 0): ?>
                                        <table class="items-table">
                                            <thead>
                                                <tr>
                                                    <th><i class="fas fa-box"></i> Ürün</th>
                                                    <th style="text-align: center;"><i class="fas fa-hashtag"></i> Adet</th>
                                                    <th><i class="fas fa-balance-scale"></i> Birim</th>
                                                    <th style="text-align: right;"><i class="fas fa-lira-sign"></i> Birim Fiyat</th>
                                                    <th style="text-align: right;"><i class="fas fa-calculator"></i> Toplam</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order['items'] as $item): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($item['urun_ismi']); ?></strong></td>
                                                            <td style="text-align: center; font-weight: 600;"><?php echo $item['adet']; ?></td>
                                                            <td><?php echo htmlspecialchars($item['birim']); ?></td>
                                                            <td style="text-align: right;">
                                                                <?php echo $item['birim_fiyat'] ? number_format($item['birim_fiyat'], 2, ',', '.') . ' ₺' : '-'; ?>
                                                            </td>
                                                            <td style="text-align: right; font-weight: 700; color: var(--primary);">
                                                                <?php echo $item['toplam_tutar'] ? number_format($item['toplam_tutar'], 2, ',', '.') . ' ₺' : '-'; ?>
                                                            </td>
                                                        </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                <?php endif; ?>
                            </div>
                    <?php endforeach; ?>
            <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 10px; opacity: 0.5;"></i>
                        <div style="font-size: 14px; font-weight: 600;">Bu müşteriye ait sipariş bulunmamaktadır.</div>
                    </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
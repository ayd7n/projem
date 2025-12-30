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
        <title>Müşteri Kartı Seçimi - Parfüm ERP</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                background-color: #f0f0f0;
                font-family: 'Roboto', sans-serif;
                margin: 0;
                padding: 0;
            }
            .page {
                background: white;
                width: 500px;
                padding: 30px;
                box-sizing: border-box;
                margin: 40px auto;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
                border-radius: 4px;
            }
            .logo { font-size: 26px; font-weight: bold; color: #4a0e63; text-align: center; margin-bottom: 5px; }
            .logo span { color: #d4af37; }
            .sub-logo { font-size: 11px; color: #888; text-transform: uppercase; text-align: center; letter-spacing: 1px; margin-bottom: 25px; }
            .box-title {
                font-size: 14px;
                font-weight: bold;
                color: #4a0e63;
                text-transform: uppercase;
                border-bottom: 2px solid #4a0e63;
                padding-bottom: 8px;
                margin-bottom: 20px;
            }
            .form-group { margin-bottom: 20px; }
            .form-group label { display: block; font-size: 11px; color: #888; text-transform: uppercase; margin-bottom: 6px; }
            .form-group select {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
                font-family: 'Roboto', sans-serif;
            }
            .form-group select:focus { border-color: #4a0e63; outline: none; }
            .btn-row { display: flex; gap: 10px; margin-top: 25px; }
            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                font-size: 13px;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .btn-primary { background: #4a0e63; color: white; flex: 1; justify-content: center; }
            .btn-primary:hover { background: #3a0b4d; }
            .btn-secondary { background: #f5f5f5; color: #333; border: 1px solid #ddd; }
            .btn-secondary:hover { background: #eee; }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="logo">IDO<span>KOZMETİK</span></div>
            <div class="sub-logo">Müşteri Yönetim Sistemi</div>
            
            <div class="box-title"><i class="fas fa-id-card"></i> Müşteri Kartı Seçimi</div>
            
            <form action="musteri_karti.php" method="get">
                <div class="form-group">
                    <label>Müşteri Seçin</label>
                    <select name="musteri_id" required>
                        <option value="">-- Müşteri Seçin --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['musteri_id']; ?>">
                                <?php echo htmlspecialchars($customer['musteri_adi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="btn-row">
                    <a href="navigation.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Geri</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Görüntüle</button>
                </div>
            </form>
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
// Sort by payment status (unpaid first) then by date
$orders_query = "SELECT s.*, 
                 (SELECT SUM(sk.birim_fiyat * sk.adet) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) as toplam_tutar_hesaplanan
                 FROM siparisler s 
                 WHERE s.musteri_id = ? AND s.durum != 'iptal_edildi' 
                 ORDER BY 
                    CASE WHEN s.odeme_durumu IS NULL OR s.odeme_durumu = 'bekliyor' OR s.odeme_durumu = 'kismi_odendi' THEN 0 ELSE 1 END,
                    s.tarih DESC";
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

// Bakiye hesaplama değişkenleri
$toplam_siparis_tutari = 0;
$toplam_odenen = 0;
$toplam_kalan_bakiye = 0;
$odenmemis_siparis_sayisi = 0;

while ($order = $orders_result->fetch_assoc()) {
    // Get order items for this order
    $items_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = ?";
    $items_stmt = $connection->prepare($items_query);
    $items_stmt->bind_param('i', $order['siparis_id']);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    $order['items'] = [];
    $order_item_total = 0;
    while ($item = $items_result->fetch_assoc()) {
        $order['items'][] = $item;
        $total_products += $item['adet'];
        $order_item_total += floatval($item['birim_fiyat']) * floatval($item['adet']);
    }
    $items_stmt->close();
    
    // Sipariş tutarını hesapla
    $order['hesaplanan_tutar'] = $order_item_total;
    $odenen = floatval($order['odenen_tutar'] ?? 0);
    $order['kalan_tutar'] = $order_item_total - $odenen;
    
    // Sadece onaylanmış veya tamamlanmış siparişler için bakiye hesapla
    if (in_array($order['durum'], ['onaylandi', 'tamamlandi'])) {
        $toplam_siparis_tutari += $order_item_total;
        $toplam_odenen += $odenen;
        
        if ($order['kalan_tutar'] > 0.01) {
            $toplam_kalan_bakiye += $order['kalan_tutar'];
            $odenmemis_siparis_sayisi++;
        }
    }

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

// Function to format date
function formatDate($dateString) {
    if (!$dateString) return '-';
    return date('d.m.Y', strtotime($dateString));
}

// Function to format currency
function formatCurrency($value) {
    return number_format(floatval($value), 2, ',', '.') . ' ₺';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Kartı - <?php echo htmlspecialchars($customer['musteri_adi']); ?> - Parfüm ERP</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f0f0;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }

        #download-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #4a0e63;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #download-btn:hover { background: #3a0b4d; }

        .page {
            background: white;
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
            box-sizing: border-box;
            margin: 20px auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .layout-table td { vertical-align: top; }

        .logo { font-size: 26px; font-weight: bold; color: #4a0e63; }
        .logo span { color: #d4af37; }
        .sub-logo { font-size: 11px; color: #888; text-transform: uppercase; margin-top: 3px; letter-spacing: 1px; }

        .doc-title { text-align: right; font-size: 22px; font-weight: bold; color: #222; }
        .doc-no { text-align: right; font-size: 14px; color: #4a0e63; font-weight: bold; margin-top: 5px; }

        .box-title {
            font-size: 10px;
            font-weight: bold;
            color: #4a0e63;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            margin-bottom: 5px;
            display: block;
        }

        .box-content { font-size: 12px; line-height: 1.4; color: #333; }

        /* Summary Stats */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .stats-table td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #eee;
        }
        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: #4a0e63;
            display: block;
        }
        .stat-label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            margin-top: 3px;
            display: block;
        }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th {
            background: #fdfdfd;
            border-bottom: 2px solid #4a0e63;
            padding: 10px 8px;
            font-size: 11px;
            text-align: left;
            color: #4a0e63;
        }
        .data-table td { padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 13px; color: #333; white-space: nowrap; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Status badges */
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-beklemede { background: #fef3c7; color: #92400e; }
        .status-onaylandi { background: #dbeafe; color: #1e40af; }
        .status-tamamlandi { background: #d1fae5; color: #065f46; }

        /* Order cards */
        .order-section {
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-title {
            font-size: 14px;
            font-weight: bold;
            color: #222;
        }
        .order-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }
        .order-meta-item {
            font-size: 12px;
        }
        .order-meta-item .label {
            color: #888;
            font-size: 10px;
            text-transform: uppercase;
        }
        .order-meta-item .value {
            color: #333;
            font-weight: 500;
        }

        .notes-area { 
            margin-top: 8px; 
            padding: 10px; 
            border: 1px dashed #ccc; 
            font-size: 12px; 
            color: #555; 
            background: #fafafa; 
        }

        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: #888;
            border: 2px dashed #ddd;
            border-radius: 4px;
        }

        .section-divider {
            border: none;
            border-top: 2px solid #4a0e63;
            margin: 25px 0;
        }

        .footer-note {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
            text-align: center;
        }
        .footer-note p {
            font-size: 9px;
            color: #bbb;
            font-style: italic;
            line-height: 1.5;
            letter-spacing: 0.3px;
        }

        @media print {
            #download-btn { display: none !important; }
            body { background: white; }
            .page { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>

    <button id="download-btn" onclick="generatePDF()">
        <i class="fas fa-file-pdf"></i> PDF İNDİR
    </button>

    <div id="invoice-container" class="page">
        
        <!-- Header -->
        <table class="layout-table">
            <tr>
                <td>
                    <div class="logo">IDO<span>KOZMETİK</span></div>
                    <div class="sub-logo">Müşteri Yönetim Sistemi</div>
                </td>
                <td></td>
            </tr>
        </table>

        <div style="height: 8px;"></div>

        <!-- Customer Info -->
        <table class="layout-table">
            <tr>
                <td style="width: 48%;">
                    <span class="box-title"><i class="fas fa-user-tie"></i> MÜŞTERİ BİLGİLERİ</span>
                    <div class="box-content">
                        <strong><?php echo htmlspecialchars($customer['musteri_adi']); ?></strong><br>
                        <?php if ($customer['telefon']): ?>
                            <i class="fas fa-phone" style="color: #4a0e63; width: 16px;"></i> <?php echo htmlspecialchars($customer['telefon']); ?><br>
                        <?php endif; ?>
                        <?php if ($customer['telefon_2']): ?>
                            <i class="fas fa-mobile-alt" style="color: #4a0e63; width: 16px;"></i> <?php echo htmlspecialchars($customer['telefon_2']); ?><br>
                        <?php endif; ?>
                        <?php if ($customer['e_posta']): ?>
                            <i class="fas fa-envelope" style="color: #4a0e63; width: 16px;"></i> <?php echo htmlspecialchars($customer['e_posta']); ?><br>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%;">
                    <span class="box-title"><i class="fas fa-map-marker-alt"></i> ADRES & VERGİ BİLGİLERİ</span>
                    <div class="box-content">
                        <?php echo htmlspecialchars($customer['adres'] ?: 'Adres belirtilmemiş'); ?><br>
                        <strong>Vergi/TC No:</strong> <?php echo htmlspecialchars($customer['vergi_no_tc'] ?: '-'); ?>
                    </div>
                </td>
            </tr>
        </table>

        <div style="height: 8px;"></div>

        <!-- Bakiye ve Ödeme Özeti - Premium Design -->
        <span class="box-title"><i class="fas fa-chart-pie"></i> HESAP ÖZETİ</span>
        <div style="display: flex; gap: 12px; margin-bottom: 12px; flex-wrap: wrap;">
            <!-- Son Sipariş Tarihi -->
            <div style="flex: 1; min-width: 140px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; text-align: center;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #4a0e63 0%, #7c3aed 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 6px;">
                    <i class="fas fa-calendar-alt" style="color: white; font-size: 14px;"></i>
                </div>
                <div style="font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Son Sipariş</div>
                <div style="font-size: 14px; font-weight: 700; color: #4a0e63;"><?php echo $last_order_date ? formatDate($last_order_date) : '-'; ?></div>
            </div>
            
            <!-- Toplam Sipariş Tutarı -->
            <div style="flex: 1; min-width: 140px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; text-align: center;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 6px;">
                    <i class="fas fa-file-invoice-dollar" style="color: white; font-size: 14px;"></i>
                </div>
                <div style="font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Toplam Tutar</div>
                <div style="font-size: 14px; font-weight: 700; color: #1e40af;"><?php echo formatCurrency($toplam_siparis_tutari); ?></div>
            </div>
            
            <!-- Ödenen Tutar -->
            <div style="flex: 1; min-width: 140px; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1px solid #a7f3d0; border-radius: 6px; padding: 12px; text-align: center;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 6px;">
                    <i class="fas fa-check-circle" style="color: white; font-size: 14px;"></i>
                </div>
                <div style="font-size: 8px; color: #047857; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Ödenen</div>
                <div style="font-size: 14px; font-weight: 700; color: #059669;"><?php echo formatCurrency($toplam_odenen); ?></div>
            </div>
            
            <!-- Kalan Bakiye -->
            <div style="flex: 1; min-width: 140px; background: linear-gradient(135deg, <?php echo $toplam_kalan_bakiye > 0 ? '#fef2f2 0%, #fecaca 100%' : '#ecfdf5 0%, #d1fae5 100%'; ?>); border: 1px solid <?php echo $toplam_kalan_bakiye > 0 ? '#fca5a5' : '#a7f3d0'; ?>; border-radius: 6px; padding: 12px; text-align: center;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, <?php echo $toplam_kalan_bakiye > 0 ? '#dc2626 0%, #ef4444 100%' : '#059669 0%, #10b981 100%'; ?>); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 6px;">
                    <i class="fas <?php echo $toplam_kalan_bakiye > 0 ? 'fa-exclamation-circle' : 'fa-check-double'; ?>" style="color: white; font-size: 14px;"></i>
                </div>
                <div style="font-size: 8px; color: <?php echo $toplam_kalan_bakiye > 0 ? '#b91c1c' : '#047857'; ?>; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Kalan Bakiye</div>
                <div style="font-size: 14px; font-weight: 700; color: <?php echo $toplam_kalan_bakiye > 0 ? '#dc2626' : '#059669'; ?>;"><?php echo formatCurrency($toplam_kalan_bakiye); ?></div>
            </div>
        </div>

        <?php if ($toplam_kalan_bakiye > 0.01): ?>
        <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 2px solid #fca5a5; border-left: 4px solid #dc2626; border-radius: 6px; padding: 12px 15px; margin-bottom: 12px; display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-exclamation-triangle" style="color: white; font-size: 18px;"></i>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 700; color: #991b1b; margin-bottom: 2px;"><i class="fas fa-bell"></i> ÖDEME BEKLİYOR</div>
                <div style="font-size: 11px; color: #b91c1c;">Bu müşterinin <strong><?php echo $odenmemis_siparis_sayisi; ?></strong> adet ödenmemiş siparişi bulunmaktadır. Toplam bakiye: <strong style="font-size: 13px;"><?php echo formatCurrency($toplam_kalan_bakiye); ?></strong></div>
            </div>
        </div>
        <?php endif; ?>

        <hr class="section-divider">

        <!-- Orders List -->
        <span class="box-title"><i class="fas fa-history"></i> SİPARİŞ GEÇMİŞİ</span>

        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <?php 
                    $is_unpaid = in_array($order['durum'], ['onaylandi', 'tamamlandi']) && $order['kalan_tutar'] > 0.01;
                    $border_style = $is_unpaid ? 'border: 2px solid #ef4444; background: #fef2f2;' : '';
                ?>
                <div class="order-section" style="<?php echo $border_style; ?>">
                    <div class="order-header">
                        <div class="order-title">
                            <i class="fas fa-file-invoice"></i> Sipariş #<?php echo $order['siparis_id']; ?>
                        </div>
                        <div class="status-badge <?php 
                            switch ($order['durum']) {
                                case 'beklemede': echo 'status-beklemede'; break;
                                case 'onaylandi': echo 'status-onaylandi'; break;
                                case 'tamamlandi': echo 'status-tamamlandi'; break;
                                default: echo 'status-beklemede';
                            }
                        ?>">
                            <?php 
                            switch ($order['durum']) {
                                case 'beklemede': echo '⏳ Beklemede'; break;
                                case 'onaylandi': 
                                    echo '✅ Onaylandı';
                                    if ($order['kalan_tutar'] <= 0.01) {
                                        echo ' <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 8px; font-size: 9px; margin-left: 4px;">ÖDENDİ</span>';
                                    } else {
                                        echo ' <span style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 8px; font-size: 9px; margin-left: 4px;">ÖDENMEDİ</span>';
                                    }
                                    break;
                                case 'tamamlandi': 
                                    echo '✔ Tamamlandı';
                                    if ($order['kalan_tutar'] <= 0.01) {
                                        echo ' <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 8px; font-size: 9px; margin-left: 4px;">ÖDENDİ</span>';
                                    } else {
                                        echo ' <span style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 8px; font-size: 9px; margin-left: 4px;">ÖDENMEDİ</span>';
                                    }
                                    break;
                                default: echo $order['durum'];
                            }
                            ?>
                        </div>
                    </div>

                    <div class="order-meta">
                        <div class="order-meta-item">
                            <div class="label">Tarih</div>
                            <div class="value"><?php echo date('d.m.Y H:i', strtotime($order['tarih'])); ?></div>
                        </div>

                        <div class="order-meta-item">
                            <div class="label">Toplam Adet</div>
                            <div class="value"><?php echo $order['toplam_adet']; ?> adet</div>
                        </div>
                        
                        <div class="order-meta-item">
                            <div class="label">Sipariş Tutarı</div>
                            <div class="value" style="font-weight: bold;"><?php echo formatCurrency($order['hesaplanan_tutar']); ?></div>
                        </div>
                        
                        <?php if (in_array($order['durum'], ['onaylandi', 'tamamlandi'])): ?>
                        <div class="order-meta-item">
                            <div class="label">Ödenen</div>
                            <div class="value" style="color: #10b981;"><?php echo formatCurrency($order['odenen_tutar'] ?? 0); ?></div>
                        </div>
                        
                        <?php if ($order['kalan_tutar'] > 0.01): ?>
                        <div class="order-meta-item">
                            <div class="label">Kalan</div>
                            <div class="value" style="color: #ef4444; font-weight: bold;"><?php echo formatCurrency($order['kalan_tutar']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($order['aciklama']): ?>
                        <div class="notes-area" style="margin-bottom: 10px;">
                            <strong style="color: #4a0e63;">Not:</strong> <?php echo htmlspecialchars($order['aciklama']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($order['items']) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%; text-align: center;">#</th>
                                    <th style="width: 45%;">ÜRÜN</th>
                                    <th style="width: 15%; text-align: center;">ADET</th>
                                    <th style="width: 15%;">BİRİM</th>
                                    <th style="width: 10%; text-align: right;">BİRİM FİYAT</th>
                                    <th style="width: 10%; text-align: right;">TOPLAM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $index => $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['urun_ismi']); ?></strong></td>
                                    <td class="text-center"><?php echo $item['adet']; ?></td>
                                    <td><?php echo htmlspecialchars($item['birim']); ?></td>
                                    <td class="text-right"><?php echo $item['birim_fiyat'] ? formatCurrency($item['birim_fiyat']) : '-'; ?></td>
                                    <td class="text-right"><strong><?php echo $item['toplam_tutar'] ? formatCurrency($item['toplam_tutar']) : '-'; ?></strong></td>
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

        <!-- Footer -->
        <div class="footer-note">
            <p>
                <i class="fas fa-shield-alt"></i> Bu doküman, IDO KOZMETİK ERP Kurumsal Bilgi Yönetim Sistemi altyapısı kullanılarak dijital ortamda güvenli olarak oluşturulmuştur. 
                İşbu belge içeriğindeki tüm veriler, merkez veritabanı kayıtları ile anlık olarak senkronize edilmekte olup sistem tarafından doğruluğu teyit edilmiştir.
                Elektronik ortamda onaylanan bu form, 5070 sayılı Elektronik İmza Kanunu standartlarına uygun olarak üretilmiş olup ıslak imza gerektirmeksizin hukuki geçerliliğini korumaktadır.
                Veri bütünlüğü ve gizliliği uluslararası bilgi güvenliği standartları çerçevesinde korunmaktadır.
                Belge üzerindeki bilgilerin sistem kayıtları dışında manuel olarak değiştirilmesi veya tahrif edilmesi durumunda belge geçersiz sayılacaktır.
                Bu müşteri kartı, IDO KOZMETİK A.Ş. ticari faaliyetleri kapsamında müşteri ilişkileri yönetimi amacıyla hazırlanmış olup, içerdiği bilgiler gizlilik ilkesi çerçevesinde korunmaktadır.
                Belgenin üçüncü şahıslarla paylaşılması, kopyalanması veya çoğaltılması şirket yazılı izni olmaksızın yasaktır.
                6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında, bu belgede yer alan kişisel veriler yasal düzenlemelere uygun şekilde işlenmekte ve saklanmaktadır.
                Müşteri bilgilerinin doğruluğu ve güncelliği, ilgili müşteri tarafından sağlanan beyanlar esas alınarak sisteme kaydedilmiştir.
                IDO KOZMETİK, müşteri memnuniyeti ve kalite standartlarına bağlılığı ile ISO 9001:2015 sertifikasyon gerekliliklerini karşılamaktadır.
                Bu belge, şirketimizin kurumsal arşiv yönetimi politikaları doğrultusunda elektronik ortamda saklanmakta olup, yasal saklama süreleri boyunca erişilebilir durumda tutulmaktadır.
            </p>
        </div>

    </div>

    <script>
        function generatePDF() {
            const element = document.getElementById('invoice-container');
            const button = document.getElementById('download-btn');
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Dosya Hazırlanıyor...';
            button.disabled = true;

            const opt = {
                margin:       0,
                filename:     'Musteri_Karti_<?php echo $customer['musteri_id']; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function(){
                 button.innerHTML = '<i class="fas fa-file-pdf"></i> PDF İNDİR';
                 button.disabled = false;
            });
        }
    </script>
</body>
</html>

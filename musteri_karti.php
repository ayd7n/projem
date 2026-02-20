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

// Get all orders for this customer
// Sort by date DESC (Newest first) as requested
$orders_query = "SELECT s.*, 
                 (SELECT SUM(sk.birim_fiyat * sk.adet) FROM siparis_kalemleri sk WHERE sk.siparis_id = s.siparis_id) as toplam_tutar_hesaplanan
                 FROM siparisler s 
                 WHERE s.musteri_id = ? AND s.durum != 'iptal_edildi' 
                 ORDER BY s.tarih DESC";
$orders_stmt = $connection->prepare($orders_query);
$orders_stmt->bind_param('i', $musteri_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

// --- INSTALLMENT PLAN LOGIC ---
// Fetch active installment plans
$plans_query = "SELECT * FROM taksit_planlari WHERE musteri_id = $musteri_id AND durum != 'iptal' ORDER BY baslangic_tarihi DESC";
$plans_result = $connection->query($plans_query);
$installment_plans = [];
$total_plan_debt = 0;
$order_plan_map = []; // Map siparis_id -> plan details

while($plan = $plans_result->fetch_assoc()) {
    // Calculate paid amount for this plan from details
    $stats = $connection->query("SELECT SUM(odenen_tutar) as paid, SUM(kalan_tutar) as remaining, COUNT(*) as total_installments, SUM(CASE WHEN durum='odendi' THEN 1 ELSE 0 END) as paid_installments FROM taksit_detaylari WHERE plan_id = {$plan['plan_id']}")->fetch_assoc();
    
    $plan['odenen_tutar'] = $stats['paid'] ?? 0;
    $plan['kalan_tutar'] = $stats['remaining'] ?? 0;
    $plan['toplam_taksit'] = $stats['total_installments'] ?? 0;
    $plan['odenen_taksit'] = $stats['paid_installments'] ?? 0;
    
    $installment_plans[] = $plan;
    
    if($plan['durum'] == 'aktif') {
        $total_plan_debt += $plan['kalan_tutar'];
    }

    // Map linked orders to this plan
    $linked_q = $connection->query("SELECT siparis_id FROM taksit_siparis_baglantisi WHERE plan_id = {$plan['plan_id']}");
    while($l = $linked_q->fetch_assoc()) {
        $order_plan_map[$l['siparis_id']] = [
            'plan_status' => $plan['durum'],
            'paid_inst' => $plan['odenen_taksit'],
            'total_inst' => $plan['toplam_taksit'],
            'remaining_plan' => $plan['kalan_tutar']
        ];
    }
}
// -----------------------------

$orders = [];
$total_orders = 0;
$total_products = 0;
$last_order_date = null;
$status_beklemede = 0;
$status_onaylandi = 0;
$status_tamamlandi = 0;

// Bakiye hesaplama değişkenleri (Para birimi bazlı)
$toplamlar = [
    'TRY' => ['siparis' => 0, 'odenen' => 0, 'kalan' => 0],
    'USD' => ['siparis' => 0, 'odenen' => 0, 'kalan' => 0],
    'EUR' => ['siparis' => 0, 'odenen' => 0, 'kalan' => 0]
];
$odenmemis_siparis_sayisi = 0;

// Taksit borçlarını TL olarak varsayıyoruz (Sistemdeki genel işleyişe göre)
$toplam_kalan_bakiye_tl = $total_plan_debt;

while ($order = $orders_result->fetch_assoc()) {
    // ... (item fetching logic remains same)
    $items_query = "SELECT * FROM siparis_kalemleri WHERE siparis_id = ?";
    $items_stmt = $connection->prepare($items_query);
    $items_stmt->bind_param('i', $order['siparis_id']);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    $order['items'] = [];
    $order_item_total = 0;
    $order_currency = 'TRY'; 
    $first_item = true;

    while ($item = $items_result->fetch_assoc()) {
        $order['items'][] = $item;
        if ($first_item) {
            $order_currency = $item['para_birimi'] ?: 'TRY';
            $first_item = false;
        }
        $total_products += $item['adet'];
        $order_item_total += floatval($item['birim_fiyat']) * floatval($item['adet']);
    }
    $items_stmt->close();
    
    $order['para_birimi'] = $order_currency;
    $order['hesaplanan_tutar'] = $order_item_total;
    $odenen = floatval($order['odenen_tutar'] ?? 0);
    $order['kalan_tutar'] = $order_item_total - $odenen;
    
    $is_in_plan = isset($order_plan_map[$order['siparis_id']]);
    $order['is_in_plan'] = $is_in_plan;
    if($is_in_plan) {
        $order['plan_details'] = $order_plan_map[$order['siparis_id']];
    }

    if (in_array($order['durum'], ['onaylandi', 'tamamlandi'])) {
        $pb = $order['para_birimi'];
        if (!isset($toplamlar[$pb])) $toplamlar[$pb] = ['siparis' => 0, 'odenen' => 0, 'kalan' => 0];
        
        $toplamlar[$pb]['siparis'] += $order_item_total;
        $toplamlar[$pb]['odenen'] += $odenen;
        
        if (!$is_in_plan && $order['kalan_tutar'] > 0.01) {
            $toplamlar[$pb]['kalan'] += $order['kalan_tutar'];
            $odenmemis_siparis_sayisi++;
        }
    }

    $orders[] = $order;
    $total_orders++;
    // ... (rest of the loop)

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
function formatCurrency($value, $currency = 'TRY') {
    $symbol = '₺';
    if ($currency === 'USD') $symbol = '$';
    elseif ($currency === 'EUR') $symbol = '€';
    
    return number_format(floatval($value), 2, ',', '.') . ' ' . $symbol;
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


        <?php 
        $active_plan_count = 0;
        foreach($installment_plans as $p) {
            if($p['kalan_tutar'] > 0.01) $active_plan_count++;
        }

        // Toplam borç var mı kontrol et (Dövizli veya Taksitli)
        $has_any_debt = ($total_plan_debt > 0.01);
        foreach($toplamlar as $pb_data) {
            if($pb_data['kalan'] > 0.01) { $has_any_debt = true; break; }
        }

        if ($has_any_debt): 
            $alert_parts = [];
            if($odenmemis_siparis_sayisi > 0) {
                $alert_parts[] = "<strong>$odenmemis_siparis_sayisi</strong> adet ödenmemiş sipariş";
            }
            if($active_plan_count > 0) {
                $alert_parts[] = "<strong>$active_plan_count</strong> adet ödemesi süren taksit planı";
            }
            
            $detail_text = implode(" ve ", $alert_parts);
            if(empty($detail_text)) $detail_text = "Ödenmemiş bakiye";
        ?>
        <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 2px solid #fca5a5; border-left: 4px solid #dc2626; border-radius: 6px; padding: 12px 15px; margin-bottom: 12px; display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-exclamation-triangle" style="color: white; font-size: 18px;"></i>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 700; color: #991b1b; margin-bottom: 2px;"><i class="fas fa-bell"></i> ÖDEME BEKLİYOR</div>
                <div style="font-size: 11px; color: #b91c1c;">
                    Bu müşterinin <?php echo $detail_text; ?> bulunmaktadır. 
                    Toplam bakiye: <strong>
                    <?php 
                    $bakiye_strings = [];
                    foreach($toplamlar as $pb => $d) {
                        if($d['kalan'] > 0.01) $bakiye_strings[] = formatCurrency($d['kalan'], $pb);
                    }
                    if($total_plan_debt > 0.01) $bakiye_strings[] = formatCurrency($total_plan_debt, 'TRY') . " (Taksit)";
                    echo implode(" + ", $bakiye_strings);
                    ?>
                    </strong>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div style="height: 8px;"></div>

        <!-- Genel Özet İstatistikleri -->
        <span class="box-title"><i class="fas fa-chart-line"></i> GENEL ÖZET</span>
        <table class="stats-table">
            <tr>
                <td style="width: 33%;">
                    <div style="font-size: 14px; font-weight: bold; color: #4a0e63;">
                        <?php 
                        foreach($toplamlar as $pb => $degerler) {
                            if($degerler['siparis'] > 0) echo formatCurrency($degerler['siparis'], $pb) . "<br>";
                        }
                        ?>
                    </div>
                    <span class="stat-label">Toplam Sipariş</span>
                </td>
                <td style="width: 33%;">
                    <div style="font-size: 14px; font-weight: bold; color: #059669;">
                        <?php 
                        foreach($toplamlar as $pb => $degerler) {
                            if($degerler['odenen'] > 0) echo formatCurrency($degerler['odenen'], $pb) . "<br>";
                        }
                        ?>
                    </div>
                    <span class="stat-label">Toplam Ödenen</span>
                </td>
                <td style="width: 34%;">
                    <div style="font-size: 14px; font-weight: bold; color: #dc2626;">
                        <?php 
                        $has_balance = false;
                        foreach($toplamlar as $pb => $degerler) {
                            if($degerler['kalan'] > 0.01) {
                                echo formatCurrency($degerler['kalan'], $pb) . "<br>";
                                $has_balance = true;
                            }
                        }
                        if($total_plan_debt > 0.01) {
                            echo formatCurrency($total_plan_debt, 'TRY') . " (Taksit)<br>";
                            $has_balance = true;
                        }
                        if(!$has_balance) echo "0,00 ₺";
                        ?>
                    </div>
                    <span class="stat-label">Kalan Bakiye</span>
                </td>
            </tr>
        </table>

        <div style="height: 15px;"></div>

        <!-- Tablo Bilgilendirme -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #4a0e63; border-radius: 6px; padding: 12px 15px; margin-bottom: 15px; color: #475569; font-size: 11px; line-height: 1.5;">
            <i class="fas fa-info-circle" style="color: #4a0e63; margin-right: 5px;"></i>
            <strong>Tablo Hakkında Bilgi:</strong> Bu tabloda müşteriye ait <em>Beklemede</em>, <em>Onaylandı</em> ve <em>Tamamlandı</em> statüsündeki tüm hareketler listelenmektedir. 
            <strong>Beklemede</strong> olan siparişler gri renkte gösterilir ve cari bakiyeyi etkilemez. 
            Dip toplamlar ve bakiye hesaplaması sadece kesinleşmiş (<strong>Onaylandı</strong> veya <strong>Tamamlandı</strong>) siparişler üzerinden yapılmaktadır.
        </div>

        <!-- Detaylı Hareket Tablosu -->
        <span class="box-title"><i class="fas fa-list-ul"></i> HAREKET DETAY TABLOSU</span>
        <table class="data-table" style="margin-bottom: 25px;">
            <thead>
                <tr>
                    <th style="width: 12%;">Tarih</th>
                    <th style="width: 30%;">Ürün / Açıklama</th>
                    <th style="width: 10%; text-align: center;">Adet</th>
                    <th style="width: 13%; text-align: right;">Birim Fiyat</th>
                    <th style="width: 13%; text-align: right;">Toplam</th>
                    <th style="width: 11%; text-align: right;">Ödenen</th>
                    <th style="width: 11%; text-align: right;">Kalan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (count($orders) > 0):
                    foreach ($orders as $order): 
                        $is_confirmed = in_array($order['durum'], ['onaylandi', 'tamamlandi']);
                        $row_style = !$is_confirmed ? 'opacity: 0.6; font-style: italic; background-color: #f9fafb;' : '';
                        $first_item = true;
                        foreach ($order['items'] as $item):
                ?>
                    <tr style="<?php echo $row_style; ?>">
                        <td><?php echo $first_item ? date('d.m.Y', strtotime($order['tarih'])) : ''; ?></td>
                        <td>
                            <?php echo htmlspecialchars($item['urun_ismi']); ?>
                            <?php if ($first_item && !$is_confirmed): ?>
                                <span class="status-badge status-beklemede" style="font-size: 8px; padding: 2px 5px; margin-left: 5px;">BEKLEMEDE</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo $item['adet']; ?> <?php echo $item['birim']; ?></td>
                        <td class="text-right"><?php echo formatCurrency($item['birim_fiyat'], $item['para_birimi']); ?></td>
                        <td class="text-right"><?php echo formatCurrency($item['toplam_tutar'], $item['para_birimi']); ?></td>
                        <td class="text-right">
                            <?php 
                            if ($first_item && $is_confirmed) {
                                echo formatCurrency($order['odenen_tutar'] ?? 0, $order['para_birimi']);
                            } elseif ($first_item) {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="text-right">
                            <?php 
                            if ($first_item && $is_confirmed) {
                                echo '<strong>' . formatCurrency($order['kalan_tutar'], $order['para_birimi']) . '</strong>';
                            } elseif ($first_item) {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                <?php 
                        $first_item = false;
                        endforeach; 
                    endforeach; 
                else:
                ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 30px; color: #888;">Henüz hareket kaydı bulunmuyor.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (count($orders) > 0): ?>
            <tfoot style="background: #f8fafc; font-weight: bold; border-top: 2px solid #4a0e63;">
                <?php foreach($toplamlar as $pb => $degerler): if($degerler['siparis'] > 0): ?>
                <tr>
                    <td colspan="4" class="text-right">TOPLAM (<?php echo $pb; ?>):</td>
                    <td class="text-right"><?php echo formatCurrency($degerler['siparis'], $pb); ?></td>
                    <td class="text-right" style="color: #059669;"><?php echo formatCurrency($degerler['odenen'], $pb); ?></td>
                    <td class="text-right" style="color: #dc2626;"><?php echo formatCurrency($degerler['kalan'], $pb); ?></td>
                </tr>
                <?php endif; endforeach; ?>
                <?php if($total_plan_debt > 0.01): ?>
                <tr>
                    <td colspan="6" class="text-right">AKTİF TAKSİT PLANI BAKİYESİ:</td>
                    <td class="text-right" style="color: #dc2626;"><?php echo formatCurrency($total_plan_debt, 'TRY'); ?></td>
                </tr>
                <?php endif; ?>
            </tfoot>
            <?php endif; ?>
        </table>


        <!-- Installment Plans Section -->
        <?php if(count($installment_plans) > 0): ?>
            <hr class="section-divider">
            <span class="box-title"><i class="fas fa-calendar-alt"></i> TAKSİT PLANLARI</span>
            <table class="data-table" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 30%;">Plan Açıklaması</th>
                        <th style="width: 15%;">Oluşturma</th>
                        <th style="width: 15%; text-align: right;">Toplam Tutar</th>
                        <th style="width: 15%; text-align: right;">Kalan Tutar</th>
                        <th style="width: 10%; text-align: center;">Taksit</th>
                        <th style="width: 10%; text-align: center;">Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($installment_plans as $idx => $plan): ?>
                        <tr>
                            <td><?php echo $idx + 1; ?></td>
                            <td><?php echo htmlspecialchars($plan['aciklama']); ?></td>
                            <td><?php echo formatDate($plan['olusturma_tarihi']); ?></td>
                            <td class="text-right"><?php echo formatCurrency($plan['toplam_odenecek']); ?></td>
                            <td class="text-right" style="font-weight: bold; color: <?php echo $plan['kalan_tutar'] > 0 ? '#dc2626' : '#059669'; ?>">
                                <?php echo formatCurrency($plan['kalan_tutar']); ?>
                            </td>
                            <td class="text-center">
                                <?php echo $plan['odenen_taksit'] . '/' . $plan['toplam_taksit']; ?>
                            </td>
                            <td class="text-center">
                                <?php if($plan['durum'] == 'aktif'): ?>
                                    <span style="background: #dbeafe; color: #1e40af; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;">AKTİF</span>
                                <?php elseif($plan['durum'] == 'tamamlandi'): ?>
                                    <span style="background: #d1fae5; color: #065f46; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;">TAMAMLANDI</span>
                                <?php else: ?>
                                    <span style="background: #f3f4f6; color: #4b5563; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;"><?php echo strtoupper($plan['durum']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <hr class="section-divider">

        <!-- Orders List -->
        <span class="box-title"><i class="fas fa-history"></i> SİPARİŞ GEÇMİŞİ</span>

        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <?php 
                    $is_unpaid = in_array($order['durum'], ['onaylandi', 'tamamlandi']) && $order['kalan_tutar'] > 0.01 && !$order['is_in_plan'];
                    $border_color = $is_unpaid ? '#fca5a5' : '#e5e7eb';
                    $bg_color = $is_unpaid ? '#fef2f2' : '#ffffff';
                    
                    // Specific logic for plan status
                    $status_badge = '';
                    $status_class = '';
                    
                    if ($order['is_in_plan']) {
                        $pd = $order['plan_details'];
                        $is_finished = $pd['remaining_plan'] <= 0;
                        if($is_finished) {
                            $status_badge = 'TAKSİTLİ - ÖDENDİ';
                            $status_color = '#059669'; // Green
                            $status_bg = '#d1fae5';
                        } else {
                            $status_badge = "TAKSİTLİ ({$pd['paid_inst']}/{$pd['total_inst']} Ödendi)";
                            $status_color = '#d97706'; // Orange/Amber
                            $status_bg = '#fef3c7';
                        }
                    } elseif ($order['kalan_tutar'] <= 0.01 && in_array($order['durum'], ['onaylandi', 'tamamlandi'])) {
                        $status_badge = 'ÖDENDİ';
                        $status_color = '#059669';
                        $status_bg = '#d1fae5';
                    } elseif ($is_unpaid) {
                        $status_badge = 'ÖDEME BEKLİYOR';
                        $status_color = '#dc2626';
                        $status_bg = '#fee2e2';
                    } else {
                        $status_badge = strtoupper($order['durum']);
                        $status_color = '#4b5563';
                        $status_bg = '#f3f4f6';
                    }
                ?>
                <div class="order-section" style="border: 1px solid <?php echo $border_color; ?>; background: <?php echo $bg_color; ?>; border-radius: 4px; margin-bottom: 8px; padding: 10px;">
                    <div class="order-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                             <div style="font-weight: 600; font-size: 12px; color: #1f2937;">
                                #<?php echo $order['siparis_id']; ?>
                             </div>
                             <div style="font-size: 10px; color: #9ca3af;">
                                <?php echo date('d.m.Y', strtotime($order['tarih'])); ?>
                             </div>
                        </div>

                        <div style="background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: 700;">
                            <?php echo $status_badge; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-bottom: 8px; flex-wrap: wrap; align-items: flex-end;">
                        <div>
                            <div style="font-size: 8px; color: #9ca3af; text-transform: uppercase;">Toplam</div>
                            <div style="font-size: 12px; font-weight: 700; color: #111827;"><?php echo formatCurrency($order['hesaplanan_tutar'], $order['para_birimi']); ?></div>
                        </div>

                        <?php if(!$order['is_in_plan']): ?>
                            <div>
                                <div style="font-size: 8px; color: #9ca3af; text-transform: uppercase;">Ödenen</div>
                                <div style="font-size: 12px; font-weight: 600; color: #059669;"><?php echo formatCurrency($order['odenen_tutar'] ?? 0, $order['para_birimi']); ?></div>
                            </div>
                            <?php if($order['kalan_tutar'] > 0.01): ?>
                                <div>
                                    <div style="font-size: 8px; color: #9ca3af; text-transform: uppercase;">Kalan</div>
                                    <div style="font-size: 12px; font-weight: 700; color: #dc2626;"><?php echo formatCurrency($order['kalan_tutar'], $order['para_birimi']); ?></div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div>
                                <div style="font-size: 8px; color: #9ca3af; text-transform: uppercase;">Plan</div>
                                <div style="font-size: 11px; font-weight: 600; color: #3b82f6;">
                                    Aktif
                                    <?php if($order['plan_details']['remaining_plan'] > 0): ?>
                                        (<?php echo formatCurrency($order['plan_details']['remaining_plan']); ?>)
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($order['aciklama']): ?>
                        <div style="background: #f9fafb; border: 1px dashed #e5e7eb; padding: 4px 8px; border-radius: 3px; font-size: 10px; color: #9ca3af; margin-bottom: 8px;">
                            <i class="fas fa-sticky-note" style="margin-right: 3px; opacity: 0.5;"></i> <?php echo htmlspecialchars($order['aciklama']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Ultra Compact Item List -->
                    <div style="background: white; border: 1px solid #f3f4f6; border-radius: 3px; padding: 6px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                            <thead>
                                <tr style="color: #9ca3af; text-transform: uppercase; font-size: 9px;">
                                    <th style="padding: 4px 6px; text-align: left; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Ürün</th>
                                    <th style="padding: 4px 6px; text-align: center; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Adet</th>
                                    <th style="padding: 4px 6px; text-align: right; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Toplam</th>
                                </tr>
                            </thead>
                            <tbody style="color: #6b7280;">
                                <?php foreach ($order['items'] as $item): ?>
                                <tr style="border-bottom: 1px solid #f9fafb;">
                                    <td style="padding: 4px 6px;"><?php echo htmlspecialchars($item['urun_ismi']); ?></td>
                                    <td style="padding: 4px 6px; text-align: center;">
                                        <span style="background: #f3f4f6; color: #6b7280; padding: 1px 4px; border-radius: 2px; font-size: 9px; font-weight: 600;">
                                            <?php echo $item['adet']; ?> <?php echo htmlspecialchars($item['birim']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 4px 6px; text-align: right; font-weight: 600; color: #374151;"><?php echo formatCurrency($item['toplam_tutar'], $item['para_birimi']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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

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

// Page-level permission check (using same permission as work centers list for now)
if (!yetkisi_var('page:view:is_merkezleri')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Get work center ID from URL
$is_merkezi_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($is_merkezi_id <= 0) {
    die('Geçersiz İş Merkezi ID.');
}

// Fetch Work Center Details
$wc_query = "SELECT * FROM is_merkezleri WHERE is_merkezi_id = ?";
$wc_stmt = $connection->prepare($wc_query);
$wc_stmt->bind_param('i', $is_merkezi_id);
$wc_stmt->execute();
$wc_result = $wc_stmt->get_result();
$work_center = $wc_result->fetch_assoc();

if (!$work_center) {
    die('İş merkezi bulunamadı.');
}

// Date Filters
$baslangic_tarihi = isset($_GET['baslangic']) && !empty($_GET['baslangic']) ? $_GET['baslangic'] : null;
$bitis_tarihi = isset($_GET['bitis']) && !empty($_GET['bitis']) ? $_GET['bitis'] : null;

// Base query connection for work orders
$wo_query_base = "FROM montaj_is_emirleri WHERE is_merkezi_id = ?";
$params = [$is_merkezi_id];
$types = "i";

if ($baslangic_tarihi) {
    $wo_query_base .= " AND DATE(olusturulma_tarihi) >= ?";
    $params[] = $baslangic_tarihi;
    $types .= "s";
}
if ($bitis_tarihi) {
    $wo_query_base .= " AND DATE(olusturulma_tarihi) <= ?";
    $params[] = $bitis_tarihi;
    $types .= "s";
}

// 1. Summary Statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN durum = 'uretimde' THEN 1 ELSE 0 END) as active_orders,
    SUM(CASE WHEN durum = 'tamamlandi' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN durum = 'iptal' THEN 1 ELSE 0 END) as cancelled_orders,
    COALESCE(SUM(tamamlanan_miktar), 0) as total_produced_qty
" . $wo_query_base;

$stats_stmt = $connection->prepare($stats_query);
$stats_stmt->bind_param($types, ...$params);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// 2. Product Production Summary (What products are produced here?)
$prod_summary_query = "SELECT 
    urun_kodu, 
    urun_ismi, 
    birim,
    COUNT(*) as order_count,
    SUM(planlanan_miktar) as total_planned,
    SUM(tamamlanan_miktar) as total_completed
" . $wo_query_base . " GROUP BY urun_kodu, urun_ismi, birim ORDER BY total_completed DESC";

$prod_stmt = $connection->prepare($prod_summary_query);
$prod_stmt->bind_param($types, ...$params);
$prod_stmt->execute();
$product_summary_result = $prod_stmt->get_result();

// 3. All Work Orders List (filtered by date)
$orders_query = "SELECT * " . $wo_query_base . " ORDER BY olusturulma_tarihi DESC";
$orders_stmt = $connection->prepare($orders_query);
$orders_stmt->bind_param($types, ...$params);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İş Merkezi Kartı - <?php echo htmlspecialchars($work_center['isim']); ?> - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --bg: #f9fafb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border: #e5e7eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-primary);
            font-size: 14px;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Card Styles */
        .custom-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .custom-card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 16px;
        }

        .custom-card-body {
            padding: 20px;
        }

        /* Header Section */
        .page-header {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin: 0 0 5px 0;
        }

        .header-content p {
            color: var(--text-secondary);
            margin: 0;
        }

        /* Summary Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
        }

        .stat-info h6 {
            margin: 0 0 5px 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .stat-info h3 {
            margin: 0;
            font-weight: 700;
            font-size: 24px;
            color: var(--text-primary);
        }

        /* Table Styles */
        .table thead th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border);
            border-top: none;
        }

        .table td {
            vertical-align: middle;
            border-bottom: 1px solid var(--border);
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-olusturuldu {
            background: #e2e8f0;
            color: #475569;
        }

        .badge-uretimde {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-tamamlandi {
            background: #d1fae5;
            color: #059669;
        }

        .badge-iptal {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-back {
            background: white;
            border: 1px solid var(--border);
            color: var(--text-primary);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #f3f4f6;
            text-decoration: none;
            color: var(--text-primary);
        }

        @media print {

            .btn,
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .main-container {
                max-width: 100%;
                padding: 0 20px !important;
            }
        }
    </style>
</head>

<body>

    <div class="main-container">
        <!-- Header -->
        <div class="page-header d-block">
            <div class="d-flex align-items-center mb-3 text-primary font-weight-bold"
                style="font-size: 1.1rem; opacity: 0.9;">
                <i class="fas fa-flask mr-2"></i> İdo Kozmetik Kurumsal Kaynak Yönetimi Sistemi
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-content d-flex align-items-center">
                    <div class="mr-4 text-center">
                        <i class="fas fa-industry fa-3x" style="color: var(--primary);"></i>
                    </div>
                    <div>
                        <h1><?php echo htmlspecialchars($work_center['isim']); ?></h1>
                        <p>
                            <span class="badge badge-light border mr-2">ID:
                                <?php echo $work_center['is_merkezi_id']; ?></span>
                            <?php echo htmlspecialchars($work_center['aciklama']); ?>
                        </p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="is_merkezleri.php" class="btn-back no-print"><i class="fas fa-arrow-left"></i> Listeye
                        Dön</a>
                    <button onclick="window.print()" class="btn btn-outline-secondary ml-2"><i class="fas fa-print"></i>
                        Yazdır</button>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="custom-card no-print">
            <div class="custom-card-body py-3">
                <form method="GET" class="form-inline align-items-center">
                    <input type="hidden" name="id" value="<?php echo $is_merkezi_id; ?>">

                    <i class="fas fa-filter text-muted mr-2"></i>
                    <span class="font-weight-bold mr-3">Filtrele:</span>

                    <div class="form-group mr-2">
                        <label class="sr-only">Başlangıç</label>
                        <input type="date" name="baslangic" class="form-control form-control-sm"
                            value="<?php echo htmlspecialchars($baslangic_tarihi ?? ''); ?>">
                    </div>
                    <div class="form-group mr-2">
                        <label class="sr-only">Bitiş</label>
                        <input type="date" name="bitis" class="form-control form-control-sm"
                            value="<?php echo htmlspecialchars($bitis_tarihi ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Uygula</button>
                    <?php if ($baslangic_tarihi || $bitis_tarihi): ?>
                        <a href="is_merkezi_karti.php?id=<?php echo $is_merkezi_id; ?>"
                            class="btn btn-link btn-sm text-danger">Temizle</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Report Description -->
        <div class="alert alert-light border mb-4" role="alert">
            <h6 class="alert-heading font-weight-bold"><i class="fas fa-info-circle text-primary mr-2"></i> Rapor
                Kapsamı</h6>
            <p class="mb-0 text-muted">
                <?php
                $wc_name = htmlspecialchars($work_center['isim']);
                if ($baslangic_tarihi && $bitis_tarihi) {
                    echo "Bu rapor, <strong>$wc_name</strong> iş merkezi için <strong>" . date('d.m.Y', strtotime($baslangic_tarihi)) . "</strong> ve <strong>" . date('d.m.Y', strtotime($bitis_tarihi)) . "</strong> tarihleri arasındaki üretim performansını, tamamlanan miktarları ve ilgili iş emirlerini detaylandırmaktadır.";
                } elseif ($baslangic_tarihi) {
                    echo "Bu rapor, <strong>$wc_name</strong> iş merkezi için <strong>" . date('d.m.Y', strtotime($baslangic_tarihi)) . "</strong> tarihinden itibaren gerçekleştirilen tüm üretim faaliyetlerini kapsamaktadır.";
                } elseif ($bitis_tarihi) {
                    echo "Bu rapor, <strong>$wc_name</strong> iş merkezi için <strong>" . date('d.m.Y', strtotime($bitis_tarihi)) . "</strong> tarihine kadar olan üretim geçmişini listelemektedir.";
                } else {
                    echo "Şu anda <strong>$wc_name</strong> iş merkezine ait sistemdeki <strong>tüm zamanların</strong> üretim verileri, istatistikleri ve iş emri geçmişi görüntülenmektedir.";
                }
                ?>
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-info">
                    <h6>Toplam İş Emri</h6>
                    <h3><?php echo number_format($stats['total_orders']); ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-cog fa-spin"></i>
                </div>
                <div class="stat-info">
                    <h6>Aktif Üretim</h6>
                    <h3><?php echo number_format($stats['active_orders']); ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #d1fae5; color: #059669;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h6>Tamamlanan</h6>
                    <h3><?php echo number_format($stats['completed_orders']); ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #f3e8ff; color: #7c3aed;">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-info">
                    <h6>Toplam Üretilen Miktar</h6>
                    <h3><?php echo number_format($stats['total_produced_qty'], 2); ?></h3>
                </div>
            </div>
        </div>

        <!-- Product Summary Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info border-0 bg-light-blue mb-0 p-3"
                    style="border-radius: 8px 8px 0 0; background: #f0f9ff;">
                    <small class="text-primary"><i class="fas fa-info-circle mr-1"></i> Bu alanda, yukarıdaki tarih
                        filtresine uygun olarak üretilen ürünlerin <strong>toplam miktarları</strong>
                        özetlenmektedir.</small>
                </div>
                <div class="custom-card h-100 mt-0" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                    <div class="custom-card-header">
                        <h5><i class="fas fa-chart-pie text-primary mr-2"></i> Üretim Özeti (Tüm Ürünler)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-box text-muted mr-1"></i> Ürün</th>
                                    <th class="text-right"><i class="fas fa-clipboard-list text-muted mr-1"></i> İş Emri
                                    </th>
                                    <th class="text-right"><i class="fas fa-industry text-muted mr-1"></i> Üretilen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($product_summary_result->num_rows > 0): ?>
                                    <?php while ($row = $product_summary_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-500 text-dark">
                                                    <?php echo htmlspecialchars($row['urun_ismi']); ?>
                                                </div>
                                                <div class="small text-muted"><i class="fas fa-barcode mr-1"></i>
                                                    <?php echo htmlspecialchars($row['urun_kodu']); ?>
                                                </div>
                                            </td>
                                            <td class="text-right"><?php echo $row['order_count']; ?></td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($row['total_completed'], 2); ?></strong>
                                                <small class="text-muted"><?php echo $row['birim']; ?></small>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Kayıt bulunamadı.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Work Orders List -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning border-0 bg-light-orange mb-0 p-3"
                    style="border-radius: 8px 8px 0 0; background: #fffbeb;">
                    <small class="text-warning-dark" style="color: #92400e;"><i class="fas fa-info-circle mr-1"></i> Bu
                        alanda, filtreye uygun olan iş emirlerinin <strong>detaylı listesi</strong> yer
                        almaktadır.</small>
                </div>
                <div class="custom-card h-100 mt-0" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                    <div class="custom-card-header">
                        <h5><i class="fas fa-list-alt text-secondary mr-2"></i> İş Emirleri Listesi</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag text-muted mr-1"></i> No / <i
                                            class="fas fa-calendar-alt text-muted mr-1"></i> Tarih</th>
                                    <th><i class="fas fa-box text-muted mr-1"></i> Ürün</th>
                                    <th><i class="fas fa-comment-alt text-muted mr-1"></i> Açıklama</th>
                                    <th><i class="fas fa-info-circle text-muted mr-1"></i> Durum</th>
                                    <th class="text-right"><i class="fas fa-ruler-combined text-muted mr-1"></i> Miktar
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders_result->num_rows > 0): ?>
                                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-primary">
                                                    #<?php echo $order['is_emri_numarasi']; ?></div>
                                                <div class="small text-muted">
                                                    <i class="far fa-clock mr-1"></i>
                                                    <?php echo date('d.m.Y', strtotime($order['olusturulma_tarihi'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;"
                                                    title="<?php echo htmlspecialchars($order['urun_ismi']); ?>">
                                                    <?php echo htmlspecialchars($order['urun_ismi']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-muted small">
                                                    <?php echo htmlspecialchars($order['aciklama'] ?? '-'); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge badge-<?php echo $order['durum']; ?>">
                                                    <?php
                                                    $durumlar = [
                                                        'olusturuldu' => 'Oluşturuldu',
                                                        'uretimde' => 'Üretimde',
                                                        'tamamlandi' => 'Tamamlandı',
                                                        'iptal' => 'İptal'
                                                    ];
                                                    // Status icons
                                                    $statusIcons = [
                                                        'olusturuldu' => '<i class="fas fa-plus-circle mr-1"></i>',
                                                        'uretimde' => '<i class="fas fa-cog fa-spin mr-1"></i>',
                                                        'tamamlandi' => '<i class="fas fa-check-circle mr-1"></i>',
                                                        'iptal' => '<i class="fas fa-times-circle mr-1"></i>'
                                                    ];
                                                    echo ($statusIcons[$order['durum']] ?? '') . ($durumlar[$order['durum']] ?? $order['durum']);
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div><?php echo number_format($order['tamamlanan_miktar'], 2); ?> /
                                                    <?php echo number_format($order['planlanan_miktar'], 2); ?>
                                                </div>
                                                <div class="small text-muted"><?php echo $order['birim']; ?></div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">İş emri bulunamadı.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>

</html>
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
if (!yetkisi_var('page:view:tedarikciler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Get supplier ID from URL parameter
$tedarikci_id = isset($_GET['tedarikci_id']) ? (int) $_GET['tedarikci_id'] : 0;

if ($tedarikci_id <= 0) {
    // If no supplier ID provided, show supplier selection
    $suppliers_query = "SELECT tedarikci_id, tedarikci_adi FROM tedarikciler ORDER BY tedarikci_adi";
    $suppliers_result = $connection->query($suppliers_query);
    $suppliers = [];
    while ($supplier = $suppliers_result->fetch_assoc()) {
        $suppliers[] = $supplier;
    }
    ?>
    <!DOCTYPE html>
    <html lang="tr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Tedarikçi Kartı Seçimi - Parfüm ERP</title>
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
                box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
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
                background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-truck"></i> Tedarikçi Kartı Seçimi</h2>
                </div>
                <div class="card-body">
                    <form action="tedarikci_karti.php" method="get">
                        <div class="form-group">
                            <label for="tedarikci_id">Tedarikçi Seçin:</label>
                            <select name="tedarikci_id" id="tedarikci_id" class="form-control" required>
                                <option value="">-- Tedarikçi Seçin --</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['tedarikci_id']; ?>">
                                        <?php echo htmlspecialchars($supplier['tedarikci_adi']); ?>
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

// Get supplier information
$supplier_query = "SELECT * FROM tedarikciler WHERE tedarikci_id = ?";
$supplier_stmt = $connection->prepare($supplier_query);
$supplier_stmt->bind_param('i', $tedarikci_id);
$supplier_stmt->execute();
$supplier_result = $supplier_stmt->get_result();
$supplier = $supplier_result->fetch_assoc();

if (!$supplier) {
    die('Tedarikçi bulunamadı.');
}

// Get all frame contracts for this supplier
$contracts_query = "SELECT * FROM cerceve_sozlesmeler WHERE tedarikci_id = ? ORDER BY olusturulma_tarihi DESC";
$contracts_stmt = $connection->prepare($contracts_query);
$contracts_stmt->bind_param('i', $tedarikci_id);
$contracts_stmt->execute();
$contracts_result = $contracts_stmt->get_result();

$contracts = [];
$total_contracts = 0;
$active_contracts = 0;
$total_contract_value = 0;

while ($contract = $contracts_result->fetch_assoc()) {
    $contracts[] = $contract;
    $total_contracts++;

    // Check if contract is active
    $today = date('Y-m-d');
    if ($contract['baslangic_tarihi'] <= $today && $contract['bitis_tarihi'] >= $today) {
        $active_contracts++;
    }

    // Calculate total contract value
    $total_contract_value += ($contract['birim_fiyat'] * $contract['limit_miktar']);
}
$contracts_stmt->close();

// Get all expenses related to this supplier
$expenses_query = "SELECT * FROM gider_yonetimi WHERE odeme_yapilan_firma = ? ORDER BY tarih DESC";
$expenses_stmt = $connection->prepare($expenses_query);
$expenses_stmt->bind_param('s', $supplier['tedarikci_adi']);
$expenses_stmt->execute();
$expenses_result = $expenses_stmt->get_result();

$expenses = [];
$total_expenses = 0;
$total_expense_amount = 0;
$last_expense_date = null;

while ($expense = $expenses_result->fetch_assoc()) {
    $expenses[] = $expense;
    $total_expenses++;
    $total_expense_amount += $expense['tutar'];

    if (!$last_expense_date || $expense['tarih'] > $last_expense_date) {
        $last_expense_date = $expense['tarih'];
    }
}
$expenses_stmt->close();

// Get purchase summary for this supplier
$purchase_summary_query = "SELECT COUNT(*) as total_purchases, COALESCE(SUM(shs.kullanilan_miktar * shs.birim_fiyat), 0) as total_purchase_amount
    FROM stok_hareketleri_sozlesmeler shs
    WHERE shs.tedarikci_id = ?";
$purchase_summary_stmt = $connection->prepare($purchase_summary_query);
$purchase_summary_stmt->bind_param('i', $tedarikci_id);
$purchase_summary_stmt->execute();
$purchase_summary_result = $purchase_summary_stmt->get_result();
$purchase_summary = $purchase_summary_result->fetch_assoc();
$summary_total_purchases = $purchase_summary['total_purchases'];
$summary_total_purchase_amount = $purchase_summary['total_purchase_amount'];
$purchase_summary_stmt->close();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Tedarikçi Kartı - <?php echo htmlspecialchars($supplier['tedarikci_adi']); ?> - Parfüm ERP</title>
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
            --primary-dark: #6d28d9;
            --primary-light: #a855f7;
            --accent: #f59e0b;
            --accent-light: #fbbf24;
            --secondary: #6b7280;
            --success: #10b981;
            --success-light: #34d399;
            --warning: #f97316;
            --warning-light: #fb923c;
            --danger: #ef4444;
            --info: #06b6d4;
            --info-light: #22d3ee;
            --bg: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
            --card-bg: #ffffff;
            --border: #e9d5ff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
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
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
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

        /* Tedarikçi bilgileri kompakt */
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
            background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
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

        /* Kart bölümü */
        .contract-card,
        .expense-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px 18px;
            margin-bottom: 14px;
            transition: all 0.2s;
        }

        .contract-card:hover,
        .expense-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .contract-header,
        .expense-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .contract-title,
        .expense-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .contract-details,
        .expense-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 8px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
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

        .no-items {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
            background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
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

            .contract-details,
            .expense-details {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .print-btn {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .contract-card:hover,
            .expense-card:hover {
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
            <h1><i class="fas fa-truck"></i> <?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></h1>
            <div class="subtitle">Tedarikçi Detay Kartı</div>
        </div>

        <!-- Özet Bilgiler -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-icon primary">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Toplam Sözleşme</div>
                    <div class="summary-value"><?php echo $total_contracts; ?></div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Aktif Sözleşme</div>
                    <div class="summary-value"><?php echo $active_contracts; ?></div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon info">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Toplam Alım</div>
                    <div class="summary-value"><?php echo $summary_total_purchases; ?> adet</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon gold">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Toplam Gider</div>
                    <div class="summary-value"><?php echo $total_expenses; ?></div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon warning">
                    <i class="fas fa-lira-sign"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Gider Tutarı</div>
                    <div class="summary-value"><?php echo number_format($total_expense_amount, 0, ',', '.'); ?> ₺</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon info">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Son Gider</div>
                    <div class="summary-value">
                        <?php echo $last_expense_date ? date('d.m.Y', strtotime($last_expense_date)) : '-'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tedarikçi Bilgileri -->
        <div class="customer-info">
            <div class="section-title"><i class="fas fa-info-circle"></i> Tedarikçi Bilgileri</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Tedarikçi Adı</div>
                        <div class="info-value"><?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Vergi/TC No</div>
                        <div class="info-value"><?php echo htmlspecialchars($supplier['vergi_no_tc'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Telefon</div>
                        <div class="info-value"><?php echo htmlspecialchars($supplier['telefon'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Telefon 2</div>
                        <div class="info-value"><?php echo htmlspecialchars($supplier['telefon_2'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">E-posta</div>
                        <div class="info-value"><?php echo htmlspecialchars($supplier['e_posta'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Yetkili Kişi</div>
                        <div class="info-value"><?php echo htmlspecialchars($supplier['yetkili_kisi'] ?: '-'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Adres</div>
                        <div class="info-value"><?php echo htmlspecialchars($supplier['adres'] ?: '-'); ?></div>
                    </div>
                </div>
                <?php if ($supplier['aciklama_notlar']): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-icon">
                            <i class="fas fa-sticky-note"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Açıklama</div>
                            <div class="info-value"><?php echo htmlspecialchars($supplier['aciklama_notlar']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sözleşmeler -->
        <div style="margin-top: 20px;">
            <div class="section-title"><i class="fas fa-file-contract"></i> Çerçeve Sözleşmeler</div>

            <?php if (count($contracts) > 0): ?>
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="contracts-table"
                        style="width: 100%; border-collapse: collapse; background: var(--card-bg); border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); color: white;">
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600;">#</th>
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600;">Malzeme
                                </th>
                                <th style="padding: 12px 16px; text-align: right; font-size: 12px; font-weight: 600;">Birim
                                    Fiyat</th>
                                <th style="padding: 12px 16px; text-align: right; font-size: 12px; font-weight: 600;">Limit
                                </th>
                                <th style="padding: 12px 16px; text-align: right; font-size: 12px; font-weight: 600;">Ödenen
                                </th>
                                <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600;">
                                    Başlangıç</th>
                                <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600;">Bitiş
                                </th>
                                <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600;">
                                    Öncelik</th>
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600;">
                                    Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $index => $contract): ?>
                                <tr
                                    style="border-bottom: 1px solid var(--border); <?php echo $index % 2 == 0 ? '' : 'background: rgba(124, 58, 237, 0.03);'; ?>">
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: var(--primary);">
                                        <?php echo $contract['sozlesme_id']; ?>
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 500;">
                                        <?php echo htmlspecialchars($contract['malzeme_ismi']); ?>
                                    </td>
                                    <td
                                        style="padding: 12px 16px; font-size: 13px; text-align: right; font-weight: 600; color: var(--success);">
                                        <?php echo number_format($contract['birim_fiyat'], 2, ',', '.'); ?>
                                        <?php echo $contract['para_birimi']; ?>
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 13px; text-align: right;">
                                        <?php echo number_format($contract['limit_miktar'], 0, ',', '.'); ?>
                                    </td>
                                    <td
                                        style="padding: 12px 16px; font-size: 13px; text-align: right; font-weight: 600; color: var(--info);">
                                        <?php echo number_format($contract['toplu_odenen_miktar'] ?: 0, 0, ',', '.'); ?>
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 12px; text-align: center;">
                                        <?php echo $contract['baslangic_tarihi'] ? date('d.m.Y', strtotime($contract['baslangic_tarihi'])) : '-'; ?>
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 12px; text-align: center;">
                                        <?php echo $contract['bitis_tarihi'] ? date('d.m.Y', strtotime($contract['bitis_tarihi'])) : '-'; ?>
                                    </td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; 
                                            <?php
                                            if ($contract['oncelik'] == 1)
                                                echo 'background: #fef2f2; color: #dc2626;';
                                            elseif ($contract['oncelik'] == 2)
                                                echo 'background: #fff7ed; color: #ea580c;';
                                            else
                                                echo 'background: #f0fdf4; color: #16a34a;';
                                            ?>">
                                            <?php echo $contract['oncelik']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 12px; color: var(--text-secondary); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        title="<?php echo htmlspecialchars($contract['aciklama'] ?: '-'); ?>">
                                        <?php echo htmlspecialchars($contract['aciklama'] ?: '-'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-items">
                    <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 10px; opacity: 0.5;"></i>
                    <div style="font-size: 14px; font-weight: 600;">Bu tedarikçiye ait sözleşme bulunmamaktadır.</div>
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
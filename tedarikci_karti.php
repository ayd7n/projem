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
                --primary: #374151;
                --border: #e5e7eb;
                --text-primary: #111827;
                --text-secondary: #6b7280;
                --bg: #f9fafb;
            }

            body {
                font-family: 'Inter', sans-serif;
                background: var(--bg);
                color: var(--text-primary);
                font-size: 14px;
            }

            .container {
                max-width: 100%;
                padding: 16px;
            }

            .card {
                background: white;
                border-radius: 4px;
                border: 1px solid var(--border);
            }

            .card-header {
                background: white;
                padding: 12px 16px;
                border-bottom: 1px solid var(--border);
            }

            .card-header h2 {
                font-size: 15px;
                font-weight: 600;
                margin: 0;
                color: var(--text-primary);
            }

            .card-body {
                padding: 16px;
            }

            .form-group label {
                font-weight: 500;
                font-size: 13px;
                color: var(--text-secondary);
                margin-bottom: 8px;
            }

            .form-control {
                padding: 10px 14px;
                border: 1px solid var(--border);
                border-radius: 6px;
                font-size: 14px;
            }

            .form-control:focus {
                border-color: var(--primary);
                box-shadow: none;
                outline: none;
            }

            .btn {
                padding: 10px 20px;
                font-size: 14px;
                font-weight: 500;
                border-radius: 6px;
            }

            .btn-primary {
                background: var(--primary);
                border: none;
                color: white;
            }

            .btn-primary:hover {
                background: #1f2937;
            }

            .btn-secondary {
                background: white;
                border: 1px solid var(--border);
                color: var(--text-primary);
            }

            .btn-secondary:hover {
                background: #f3f4f6;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Tedarikçi Kartı Seçimi</h2>
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
                        <button type="submit" class="btn btn-primary">Görüntüle</button>
                        <a href="navigation.php" class="btn btn-secondary">Geri</a>
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

// Tarih filtresi parametrelerini al
$baslangic_tarihi = isset($_GET['baslangic']) && !empty($_GET['baslangic']) ? $_GET['baslangic'] : null;
$bitis_tarihi = isset($_GET['bitis']) && !empty($_GET['bitis']) ? $_GET['bitis'] : null;

// Get all expenses related to this supplier (with date filter)
$expenses_query = "SELECT * FROM gider_yonetimi WHERE odeme_yapilan_firma = ?";
if ($baslangic_tarihi) {
    $expenses_query .= " AND DATE(tarih) >= ?";
}
if ($bitis_tarihi) {
    $expenses_query .= " AND DATE(tarih) <= ?";
}
$expenses_query .= " ORDER BY tarih DESC";

$expenses_stmt = $connection->prepare($expenses_query);

// Parametreleri bind et
if ($baslangic_tarihi && $bitis_tarihi) {
    $expenses_stmt->bind_param('sss', $supplier['tedarikci_adi'], $baslangic_tarihi, $bitis_tarihi);
} elseif ($baslangic_tarihi) {
    $expenses_stmt->bind_param('ss', $supplier['tedarikci_adi'], $baslangic_tarihi);
} elseif ($bitis_tarihi) {
    $expenses_stmt->bind_param('ss', $supplier['tedarikci_adi'], $bitis_tarihi);
} else {
    $expenses_stmt->bind_param('s', $supplier['tedarikci_adi']);
}

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

// Get exchange rates from ayarlar table
$dolar_kuru = 1;
$euro_kuru = 1;

$kur_query = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
$kur_result = $connection->query($kur_query);
while ($kur_row = $kur_result->fetch_assoc()) {
    if ($kur_row['ayar_anahtar'] == 'dolar_kuru') {
        $dolar_kuru = floatval($kur_row['ayar_deger']);
    } elseif ($kur_row['ayar_anahtar'] == 'euro_kuru') {
        $euro_kuru = floatval($kur_row['ayar_deger']);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Cari Hesap Ekstresi - <?php echo htmlspecialchars($supplier['tedarikci_adi']); ?> - İDO Kozmetik</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --primary-light: #6b2d8a;
            --primary-dark: #3a0b4d;
            --gold: #d4af37;
            --border: #eee;
            --text-primary: #222;
            --text-secondary: #666;
            --text-muted: #888;
            --bg: #f0f0f0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            font-size: 13px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--primary);
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
            font-size: 14px;
        }

        .print-btn:hover {
            background: var(--primary-dark);
        }

        .container {
            max-width: 210mm;
            padding: 15mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        /* Header - Logo Tarzı */
        .customer-header {
            background: white;
            border: none;
            border-radius: 0;
            padding: 0 0 15px 0;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid var(--border);
        }

        .header-left {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 3px;
        }

        .header-left .logo-text {
            font-size: 26px;
            font-weight: bold;
            color: var(--primary);
        }

        .header-left .logo-text span {
            color: var(--gold);
        }

        .header-left .sub-logo {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }

        .customer-header h1 {
            font-size: 22px;
            font-weight: bold;
            color: var(--text-primary);
            margin: 0;
            text-align: right;
        }

        .customer-header .subtitle {
            font-size: 14px;
            font-weight: bold;
            color: var(--primary);
            background: none;
            padding: 0;
            border-radius: 0;
            margin: 0;
        }

        /* Box Title - PDF tarzı */
        .box-title {
            font-size: 11px;
            font-weight: bold;
            color: var(--primary);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
            padding-bottom: 5px;
            margin-bottom: 8px;
            display: block;
        }

        .box-content {
            font-size: 13px;
            line-height: 1.4;
            color: #333;
        }

        /* Bilgi kartı */
        .customer-info {
            background: white;
            border: none;
            border-radius: 0;
            padding: 0;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--border);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 8px 24px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-icon {
            display: none;
        }

        .info-label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 500;
        }

        .info-value {
            font-size: 13px;
            color: var(--text-primary);
            font-weight: 500;
        }

        /* Tablo başlığı - PDF Tarzı */
        .table-header {
            background: #fdfdfd;
            border: none;
            border-radius: 0;
            padding: 12px 0;
            box-shadow: none;
            border-bottom: 2px solid var(--primary);
            margin-top: 25px;
        }

        .table-header h5 {
            font-size: 11px;
            font-weight: bold;
            color: var(--primary);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-header h5 small {
            color: var(--text-muted) !important;
            font-weight: normal;
            text-transform: none;
        }

        .card {
            background: white;
            border: none;
            border-top: none;
            border-radius: 0;
            box-shadow: none;
            overflow: visible;
        }

        .card-body {
            padding: 0;
        }

        /* Data Table - PDF tarzı */
        .table {
            width: 100%;
            margin: 0;
            font-size: 13px;
            border-collapse: collapse;
        }

        .table thead th {
            background: #fdfdfd;
            border-bottom: 2px solid var(--primary);
            padding: 10px 8px;
            font-size: 11px;
            text-align: left;
            color: var(--primary);
            font-weight: bold;
            text-transform: uppercase;
        }

        .table thead th i {
            margin-right: 6px;
            opacity: 0.7;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }

        .table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .table tbody tr:hover {
            background-color: #f5f0f7;
        }

        .table tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--border);
            color: #333;
            vertical-align: middle;
            font-size: 13px;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody td.text-right {
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
        }

        .no-items {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            font-style: italic;
        }

        /* Toplam Kutuları - PDF tarzı */
        .total-box {
            background: #fafafa;
            border: 1px dashed #ccc;
            padding: 12px;
            margin-top: 15px;
        }

        .total-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .total-value {
            font-weight: bold;
            color: var(--primary);
        }

        /* Notes Area */
        .notes-area {
            margin-top: 30px;
            padding: 12px;
            border: 1px dashed #ccc;
            font-size: 12px;
            color: #555;
            background: #fafafa;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
                margin: 10px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .customer-header {
                flex-direction: column;
                gap: 15px;
            }

            .header-right {
                align-items: flex-start;
            }
        }

        @media print {
            .print-btn {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 15mm !important;
            }
        }
    </style>
</head>

<body>
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> YAZDIR
    </button>

    <div class="container">
        <!-- Header -->
        <div class="customer-header">
            <div class="header-left">
                <div class="logo-text">IDO<span>KOZMETİK</span></div>
                <div class="sub-logo">Cari Hesap Ekstresi</div>
            </div>
            <div class="header-right">
                <h1><?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></h1>
                <div class="subtitle">Hesap Özeti</div>
            </div>
        </div>

        <!-- Tedarikçi Bilgileri -->
        <div class="customer-info">
            <div class="section-title"><i class="fas fa-info-circle"></i> Firma Bilgileriniz</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-building"></i> Firma Ünvanı</div>
                    <div class="info-value"><?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-id-card"></i> Vergi/TC No</div>
                    <div class="info-value"><?php echo htmlspecialchars($supplier['vergi_no_tc'] ?: '-'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-phone"></i> Telefon</div>
                    <div class="info-value"><?php echo htmlspecialchars($supplier['telefon'] ?: '-'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-mobile-alt"></i> Telefon 2</div>
                    <div class="info-value"><?php echo htmlspecialchars($supplier['telefon_2'] ?: '-'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-envelope"></i> E-posta</div>
                    <div class="info-value"><?php echo htmlspecialchars($supplier['e_posta'] ?: '-'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user-tie"></i> Yetkili Kişi</div>
                    <div class="info-value"><?php echo htmlspecialchars($supplier['yetkili_kisi'] ?: '-'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-map-marker-alt"></i> Adres</div>
                    <div class="info-value"><?php echo htmlspecialchars($supplier['adres'] ?: '-'); ?></div>
                </div>
                <?php if ($supplier['aciklama_notlar']): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label"><i class="fas fa-sticky-note"></i> Açıklama</div>
                        <div class="info-value"><?php echo htmlspecialchars($supplier['aciklama_notlar']); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tarih Filtresi ve Ayarlar -->
        <div style="padding: 12px 0; margin-bottom: 20px; border-bottom: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                <!-- Tarih Filtresi -->
                <form method="GET" action="tedarikci_karti.php"
                    style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <input type="hidden" name="tedarikci_id" value="<?php echo $tedarikci_id; ?>">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: bold; color: var(--primary); text-transform: uppercase;">Tarih Filtresi:</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 12px; color: var(--text-muted);">Başlangıç:</label>
                        <input type="date" name="baslangic"
                            value="<?php echo htmlspecialchars($baslangic_tarihi ?? ''); ?>"
                            style="padding: 6px 10px; border: 1px solid var(--border); border-radius: 4px; font-size: 13px;">
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 12px; color: var(--text-muted);">Bitiş:</label>
                        <input type="date" name="bitis" value="<?php echo htmlspecialchars($bitis_tarihi ?? ''); ?>"
                            style="padding: 6px 10px; border: 1px solid var(--border); border-radius: 4px; font-size: 13px;">
                    </div>
                    <button type="submit"
                        style="background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 4px; font-size: 13px; font-weight: 500; cursor: pointer;">
                        <i class="fas fa-search"></i> Filtrele
                    </button>
                    <?php if ($baslangic_tarihi || $bitis_tarihi): ?>
                        <a href="tedarikci_karti.php?tedarikci_id=<?php echo $tedarikci_id; ?>"
                            style="background: #fafafa; color: var(--text-secondary); border: 1px dashed #ccc; padding: 8px 16px; border-radius: 4px; font-size: 13px; font-weight: 500; text-decoration: none;">
                            <i class="fas fa-times"></i> Temizle
                        </a>
                    <?php endif; ?>
                </form>

                <!-- Para Birimi Toggle -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Döviz Karşılıkları:</span>
                    <button type="button" id="toggleCurrencyBtn" onclick="toggleCurrencyView()"
                        style="background: var(--primary); color: white; border: none; padding: 6px 14px; border-radius: 4px; font-size: 12px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-eye" id="toggleCurrencyIcon"></i>
                        <span id="toggleCurrencyText">Gösteriliyor</span>
                    </button>
                </div>
            </div>

            <?php if ($baslangic_tarihi || $bitis_tarihi): ?>
                <div style="margin-top: 10px; font-size: 12px; color: var(--primary);">
                    <i class="fas fa-info-circle"></i> Filtre aktif:
                    <?php if ($baslangic_tarihi): ?>
                        <strong><?php echo date('d.m.Y', strtotime($baslangic_tarihi)); ?></strong>
                    <?php endif; ?>
                    <?php if ($baslangic_tarihi && $bitis_tarihi): ?> - <?php endif; ?>
                    <?php if ($bitis_tarihi): ?>
                        <strong><?php echo date('d.m.Y', strtotime($bitis_tarihi)); ?></strong>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mal Kabul Kayıtları -->
        <?php
        // Get acceptance records for this supplier from stok_hareket_kayitlari table (with date filter)
        $acceptance_query = "SELECT
                    shk.*,
                    m.malzeme_ismi
                FROM stok_hareket_kayitlari shk
                LEFT JOIN malzemeler m ON shk.kod = m.malzeme_kodu
                WHERE shk.tedarikci_id = ?";

        if ($baslangic_tarihi) {
            $acceptance_query .= " AND DATE(shk.tarih) >= ?";
        }
        if ($bitis_tarihi) {
            $acceptance_query .= " AND DATE(shk.tarih) <= ?";
        }
        $acceptance_query .= " ORDER BY shk.tarih DESC";

        $acceptance_stmt = $connection->prepare($acceptance_query);

        // Parametreleri bind et
        if ($baslangic_tarihi && $bitis_tarihi) {
            $acceptance_stmt->bind_param('iss', $tedarikci_id, $baslangic_tarihi, $bitis_tarihi);
        } elseif ($baslangic_tarihi) {
            $acceptance_stmt->bind_param('is', $tedarikci_id, $baslangic_tarihi);
        } elseif ($bitis_tarihi) {
            $acceptance_stmt->bind_param('is', $tedarikci_id, $bitis_tarihi);
        } else {
            $acceptance_stmt->bind_param('i', $tedarikci_id);
        }

        $acceptance_stmt->execute();
        $acceptance_result = $acceptance_stmt->get_result();

        $acceptance_rows = [];
        while ($row = $acceptance_result->fetch_assoc()) {
            $acceptance_rows[] = $row;
        }
        $acceptance_stmt->close();

        // Önce toplamları hesapla
        $toplam_tl = 0;
        $toplam_usd = 0;
        $toplam_eur = 0;
        $calculated_rows = [];

        foreach ($acceptance_rows as $row) {
            $sozlesme_id = '';
            if (
                preg_match('/\[Sozlesme ID: (\d+)\]/i', $row['aciklama'], $matches) ||
                preg_match('/\[Sözleşme ID: (\d+)\]/i', $row['aciklama'], $matches)
            ) {
                $sozlesme_id = $matches[1];
            }

            $birim_fiyat = '-';
            $para_birimi = '-';

            if ($sozlesme_id) {
                $contract_query = "SELECT birim_fiyat, para_birimi FROM cerceve_sozlesmeler WHERE sozlesme_id = ?";
                $contract_stmt = $connection->prepare($contract_query);
                $contract_stmt->bind_param('i', $sozlesme_id);
                $contract_stmt->execute();
                $contract_result = $contract_stmt->get_result();

                if ($contract_row = $contract_result->fetch_assoc()) {
                    $birim_fiyat = $contract_row['birim_fiyat'];
                    $para_birimi = $contract_row['para_birimi'];
                }
                $contract_stmt->close();
            }

            $toplam = ($birim_fiyat !== '-' && $row['miktar']) ? $birim_fiyat * $row['miktar'] : '-';

            // Toplama ekle
            if ($toplam !== '-') {
                if (strtoupper($para_birimi) == 'TL' || strtoupper($para_birimi) == 'TRY') {
                    $toplam_tl += $toplam;
                } elseif (strtoupper($para_birimi) == 'USD' || strtoupper($para_birimi) == '$') {
                    $toplam_usd += $toplam;
                } elseif (strtoupper($para_birimi) == 'EUR' || strtoupper($para_birimi) == '€') {
                    $toplam_eur += $toplam;
                }
            }

            // Satır verilerini sakla
            $calculated_rows[] = [
                'row' => $row,
                'sozlesme_id' => $sozlesme_id,
                'birim_fiyat' => $birim_fiyat,
                'para_birimi' => $para_birimi,
                'toplam' => $toplam
            ];
        }

        // Genel toplamları hesapla
        $genel_toplam_tl = $toplam_tl + ($toplam_usd * $dolar_kuru) + ($toplam_eur * $euro_kuru);
        $genel_toplam_usd = $dolar_kuru > 0 ? $genel_toplam_tl / $dolar_kuru : 0;
        $genel_toplam_eur = $euro_kuru > 0 ? $genel_toplam_tl / $euro_kuru : 0;
        ?>

        <?php if (count($acceptance_rows) > 0): ?>
            <!-- Mal Kabul Toplamları -->
            <div style="background: #dcfce7; border: 1px solid #86efac; border-left: 4px solid #22c55e; border-radius: 6px; padding: 16px 20px; margin-top: 25px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <strong style="color: #166534; font-size: 14px;"><i class="fas fa-truck"></i> TOPLAM SATIŞ TUTARINIZ</strong><br>
                        <small style="color: #15803d;">Firmamıza sağladığınız mal ve hizmetlerin toplamı</small>
                    </div>
                    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                        <div style="text-align: right;">
                            <div style="font-size: 10px; color: #15803d; text-transform: uppercase;">Toplam (TL)</div>
                            <div style="font-size: 20px; font-weight: bold; color: #166534;">
                                <?php echo number_format($genel_toplam_tl, 2, ',', '.'); ?> ₺
                            </div>
                        </div>
                        <div style="text-align: right;" class="currency-toggle">
                            <div style="font-size: 10px; color: #15803d; text-transform: uppercase;">Toplam (USD)</div>
                            <div style="font-size: 16px; font-weight: bold; color: #166534;">
                                <?php echo number_format($genel_toplam_usd, 2, ',', '.'); ?> $
                            </div>
                            <small style="font-size: 9px; color: #15803d;">(1 USD = <?php echo number_format($dolar_kuru, 4, ',', '.'); ?> TL)</small>
                        </div>
                        <div style="text-align: right;" class="currency-toggle">
                            <div style="font-size: 10px; color: #15803d; text-transform: uppercase;">Toplam (EUR)</div>
                            <div style="font-size: 16px; font-weight: bold; color: #166534;">
                                <?php echo number_format($genel_toplam_eur, 2, ',', '.'); ?> €
                            </div>
                            <small style="font-size: 9px; color: #15803d;">(1 EUR = <?php echo number_format($euro_kuru, 4, ',', '.'); ?> TL)</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-header">
            <h5>TESLİMAT DETAYLARI <small
                    style="font-size: 12px; color: var(--text-secondary); font-weight: 400;">(<?php echo count($calculated_rows); ?>
                    kayıt)</small></h5>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-light">
                            <tr class="small">
                                <th class="font-weight-normal"><i class="fas fa-calendar-alt"></i> Tarih</th>
                                <th class="font-weight-normal"><i class="fas fa-barcode"></i> Malzeme Kodu</th>
                                <th class="font-weight-normal"><i class="fas fa-cube"></i> Malzeme Adı</th>
                                <th class="font-weight-normal text-right"><i class="fas fa-balance-scale"></i> Miktar
                                </th>
                                <th class="font-weight-normal"><i class="fas fa-ruler"></i> Birim</th>
                                <th class="font-weight-normal text-right"><i class="fas fa-tag"></i> Birim Fiyat</th>
                                <th class="font-weight-normal"><i class="fas fa-coins"></i> Para Birimi</th>
                                <th class="font-weight-normal text-right"><i class="fas fa-calculator"></i> Toplam Tutar
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($calculated_rows) > 0): ?>
                                <?php foreach ($calculated_rows as $item):
                                    $row = $item['row'];
                                    $sozlesme_id = $item['sozlesme_id'];
                                    $birim_fiyat = $item['birim_fiyat'];
                                    $para_birimi = $item['para_birimi'];
                                    $toplam = $item['toplam'];
                                    ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y H:i', strtotime($row['tarih'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['kod']); ?></td>
                                        <td><?php echo htmlspecialchars($row['isim'] ?? '-'); ?></td>
                                        <td class="text-right"><?php echo number_format($row['miktar'], 2, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($row['birim']); ?></td>
                                        <td class="text-right">
                                            <?php echo $birim_fiyat !== '-' ? number_format($birim_fiyat, 2, ',', '.') : '-'; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($para_birimi); ?></td>
                                        <td class="text-right">
                                            <?php echo $toplam !== '-' ? number_format($toplam, 2, ',', '.') . ' ' . $para_birimi : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Bu tedarikçi için mal kabul kaydı
                                        bulunamadı.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ödeme Kayıtları -->
        <?php
        // Ödeme toplamları hesapla (TL olarak alınmış)
        $odeme_toplam_tl = 0;
        foreach ($expenses as $expense) {
            $odeme_toplam_tl += $expense['tutar'];
        }
        $odeme_toplam_usd = $dolar_kuru > 0 ? $odeme_toplam_tl / $dolar_kuru : 0;
        $odeme_toplam_eur = $euro_kuru > 0 ? $odeme_toplam_tl / $euro_kuru : 0;
        ?>

        <?php if (count($expenses) > 0): ?>
            <!-- Ödeme Toplamları -->
            <div style="background: #fee2e2; border: 1px solid #fca5a5; border-left: 4px solid #ef4444; border-radius: 6px; padding: 16px 20px; margin-top: 25px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <strong style="color: #991b1b; font-size: 14px;"><i class="fas fa-hand-holding-usd"></i> TARAFINIZA YAPILAN ÖDEMELER</strong><br>
                        <small style="color: #b91c1c;">Firmamız tarafından tarafınıza yapılan ödemelerin toplamı</small>
                    </div>
                    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                        <div style="text-align: right;">
                            <div style="font-size: 10px; color: #b91c1c; text-transform: uppercase;">Toplam (TL)</div>
                            <div style="font-size: 20px; font-weight: bold; color: #991b1b;">
                                <?php echo number_format($odeme_toplam_tl, 2, ',', '.'); ?> ₺
                            </div>
                        </div>
                        <div style="text-align: right;" class="currency-toggle">
                            <div style="font-size: 10px; color: #b91c1c; text-transform: uppercase;">Toplam (USD)</div>
                            <div style="font-size: 16px; font-weight: bold; color: #991b1b;">
                                <?php echo number_format($odeme_toplam_usd, 2, ',', '.'); ?> $
                            </div>
                            <small style="font-size: 9px; color: #b91c1c;">(1 USD = <?php echo number_format($dolar_kuru, 4, ',', '.'); ?> TL)</small>
                        </div>
                        <div style="text-align: right;" class="currency-toggle">
                            <div style="font-size: 10px; color: #b91c1c; text-transform: uppercase;">Toplam (EUR)</div>
                            <div style="font-size: 16px; font-weight: bold; color: #991b1b;">
                                <?php echo number_format($odeme_toplam_eur, 2, ',', '.'); ?> €
                            </div>
                            <small style="font-size: 9px; color: #b91c1c;">(1 EUR = <?php echo number_format($euro_kuru, 4, ',', '.'); ?> TL)</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-header">
            <h5>ÖDEME DETAYLARI <small
                    style="font-size: 12px; color: var(--text-secondary); font-weight: 400;">(<?php echo count($expenses); ?>
                    kayıt)</small></h5>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-light">
                            <tr class="small">
                                <th class="font-weight-normal"><i class="fas fa-calendar-alt"></i> Tarih</th>
                                <th class="font-weight-normal"><i class="fas fa-file-invoice"></i> Fatura No</th>
                                <th class="font-weight-normal"><i class="fas fa-comment"></i> Açıklama</th>
                                <th class="font-weight-normal text-right"><i class="fas fa-coins"></i> Tutar (TL)
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($expenses) > 0): ?>
                                <?php foreach ($expenses as $expense): ?>
                                    <tr>
                                        <td><?php echo date('d.m.Y', strtotime($expense['tarih'])); ?></td>
                                        <td><?php echo htmlspecialchars($expense['fatura_no'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($expense['aciklama'] ?? '-'); ?></td>
                                        <td class="text-right"><?php echo number_format($expense['tutar'], 2, ',', '.'); ?> ₺
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Bu tedarikçiye ödeme kaydı
                                        bulunamadı.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Alacak/Verecek Bakiye Özeti -->
        <?php
        // Bakiye hesapla: Alım - Ödeme
        // Pozitif bakiye = Tedarikçiye borcumuz var (daha fazla mal aldık, daha az ödedik)
        // Negatif bakiye = Tedarikçiden alacaklıyız (daha fazla ödedik, daha az mal aldık)
        $bakiye_tl = $genel_toplam_tl - $odeme_toplam_tl;
        $bakiye_usd = $dolar_kuru > 0 ? $bakiye_tl / $dolar_kuru : 0;
        $bakiye_eur = $euro_kuru > 0 ? $bakiye_tl / $euro_kuru : 0;

        // Durum belirleme - Tedarikçinin bakış açısından
        if ($bakiye_tl > 0) {
            $bakiye_durum = "Alacak Bakiyeniz";
            $bakiye_renk = "#166534"; // Yeşil - tedarikçinin alacağı var
            $bakiye_bg = "#dcfce7";
            $bakiye_border = "#22c55e";
            $bakiye_ikon = "fa-arrow-up";
        } elseif ($bakiye_tl < 0) {
            $bakiye_durum = "Borç Bakiyeniz";
            $bakiye_renk = "#991b1b"; // Kırmızı - tedarikçinin borcu var
            $bakiye_bg = "#fee2e2";
            $bakiye_border = "#ef4444";
            $bakiye_ikon = "fa-arrow-down";
            $bakiye_tl = abs($bakiye_tl);
            $bakiye_usd = abs($bakiye_usd);
            $bakiye_eur = abs($bakiye_eur);
        } else {
            $bakiye_durum = "Hesabınız Kapalıdır";
            $bakiye_renk = "#6b7280"; // Gri - sıfır
            $bakiye_bg = "#f9fafb";
            $bakiye_border = "#d1d5db";
            $bakiye_ikon = "fa-check-circle";
        }
        ?>

        <div style="background: <?php echo $bakiye_bg; ?>; border: 2px solid <?php echo $bakiye_border; ?>; border-left: 5px solid <?php echo $bakiye_border; ?>; border-radius: 6px; padding: 20px 24px; margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <strong style="color: <?php echo $bakiye_renk; ?>; font-size: 16px;">
                        <i class="fas <?php echo $bakiye_ikon; ?>"></i> BAKİYE DURUMU
                    </strong><br>
                    <span style="font-size: 14px; font-weight: 600; color: <?php echo $bakiye_renk; ?>;"><?php echo $bakiye_durum; ?></span>
                </div>
                <div style="display: flex; gap: 32px; flex-wrap: wrap;">
                    <div style="text-align: center;">
                        <div style="font-size: 10px; color: <?php echo $bakiye_renk; ?>; text-transform: uppercase; opacity: 0.8;">Bakiye (TL)</div>
                        <div style="font-size: 28px; font-weight: bold; color: <?php echo $bakiye_renk; ?>;">
                            <?php echo number_format($bakiye_tl, 2, ',', '.'); ?> ₺
                        </div>
                    </div>
                    <div style="text-align: center;" class="currency-toggle">
                        <div style="font-size: 10px; color: <?php echo $bakiye_renk; ?>; text-transform: uppercase; opacity: 0.8;">Bakiye (USD)</div>
                        <div style="font-size: 18px; font-weight: bold; color: <?php echo $bakiye_renk; ?>;">
                            <?php echo number_format($bakiye_usd, 2, ',', '.'); ?> $
                        </div>
                    </div>
                    <div style="text-align: center;" class="currency-toggle">
                        <div style="font-size: 10px; color: <?php echo $bakiye_renk; ?>; text-transform: uppercase; opacity: 0.8;">Bakiye (EUR)</div>
                        <div style="font-size: 18px; font-weight: bold; color: <?php echo $bakiye_renk; ?>;">
                            <?php echo number_format($bakiye_eur, 2, ',', '.'); ?> €
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed <?php echo $bakiye_border; ?>; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="font-size: 12px; color: <?php echo $bakiye_renk; ?>;">
                    <i class="fas fa-truck"></i> Satış Tutarınız: <strong><?php echo number_format($genel_toplam_tl, 2, ',', '.'); ?> ₺</strong>
                </div>
                <div style="font-size: 12px; color: <?php echo $bakiye_renk; ?>;">
                    <i class="fas fa-hand-holding-usd"></i> Aldığınız Ödeme: <strong><?php echo number_format($odeme_toplam_tl, 2, ',', '.'); ?> ₺</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Para birimi göster/gizle toggle
        let currencyVisible = true;

        function toggleCurrencyView() {
            currencyVisible = !currencyVisible;
            const elements = document.querySelectorAll('.currency-toggle');
            const btn = document.getElementById('toggleCurrencyBtn');
            const icon = document.getElementById('toggleCurrencyIcon');
            const text = document.getElementById('toggleCurrencyText');

            elements.forEach(el => {
                el.style.display = currencyVisible ? '' : 'none';
            });

            if (currencyVisible) {
                btn.style.background = '#4a0e63';
                icon.className = 'fas fa-eye';
                text.textContent = 'Gösteriliyor';
            } else {
                btn.style.background = '#888';
                icon.className = 'fas fa-eye-slash';
                text.textContent = 'Gizlendi';
            }
        }
    </script>
</body>

</html>
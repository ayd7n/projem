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
$tedarikci_id = isset($_GET['tedarikci_id']) ? (int)$_GET['tedarikci_id'] : 0;

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
                <h2><i class="fas fa-id-card"></i> Tedarikçi Kartı Seçimi</h2>
            </div>
            <div class="card-body">
                <form action="tedarikci_karti.php" method="get">
                    <div class="form-group">
                        <label for="tedarikci_id">Tedarikçi Seçin:</label>
                        <select name="tedarikci_id" id="tedarikci_id" class="form-control" required>
                            <option value="">-- Tedarikçi Seçin --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['tedarikci_id']; ?>"><?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tedarikçi Kartını Görüntüle</button>
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
while ($contract = $contracts_result->fetch_assoc()) {
    $contracts[] = $contract;
}
$contracts_stmt->close();

// Get all expenses related to this supplier
$expenses_query = "SELECT * FROM gider_yonetimi WHERE odeme_yapilan_firma = ? ORDER BY tarih DESC";
$expenses_stmt = $connection->prepare($expenses_query);
$expenses_stmt->bind_param('s', $supplier['tedarikci_adi']);
$expenses_stmt->execute();
$expenses_result = $expenses_stmt->get_result();

$expenses = [];
while ($expense = $expenses_result->fetch_assoc()) {
    $expenses[] = $expense;
}
$expenses_stmt->close();
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
        
        .supplier-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid var(--primary);
            margin-bottom: 30px;
        }
        
        .supplier-header h1 {
            font-size: 1.8rem;
            color: var(--primary);
            margin: 0;
        }
        
        .supplier-info {
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
        
        .contract-card, .expense-card {
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        
        .contract-header, .expense-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .contract-title, .expense-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .contract-details, .expense-details {
            margin-bottom: 15px;
        }
        
        .contract-detail-row, .expense-detail-row {
            margin-bottom: 8px;
            display: flex;
        }
        
        .detail-label {
            font-weight: bold;
            color: var(--text-secondary);
            width: 120px;
            flex-shrink: 0;
        }
        
        .contract-items, .expense-items {
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
        
        .items-table .item-quantity, .items-table .item-unit {
            width: 15%;
        }
        
        .items-table .item-price, .items-table .item-total {
            width: 15%;
        }
        
        .no-items {
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
        <div class="supplier-header">
            <h1>TEDARİKÇİ KARTI</h1>
            <h2><?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></h2>
        </div>
        
        <div class="supplier-info">
            <div class="info-row">
                <span class="info-label">Tedarikçi Adı:</span>
                <span><?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Vergi/TC No:</span>
                <span><?php echo htmlspecialchars($supplier['vergi_no_tc'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefon:</span>
                <span><?php echo htmlspecialchars($supplier['telefon'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefon 2:</span>
                <span><?php echo htmlspecialchars($supplier['telefon_2'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">E-posta:</span>
                <span><?php echo htmlspecialchars($supplier['e_posta'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Yetkili Kişi:</span>
                <span><?php echo htmlspecialchars($supplier['yetkili_kisi'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Adres:</span>
                <span><?php echo htmlspecialchars($supplier['adres'] ?: '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Açıklama:</span>
                <span><?php echo htmlspecialchars($supplier['aciklama_notlar'] ?: '-'); ?></span>
            </div>
        </div>
        
        <h3 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            Çerçeve Sözleşmeler
        </h3>
        
        <?php if (count($contracts) > 0): ?>
            <?php foreach ($contracts as $contract): ?>
                <div class="contract-card">
                    <div class="contract-header">
                        <div class="contract-title">Sözleşme No: <?php echo $contract['sozlesme_id']; ?></div>
                    </div>
                    
                    <div class="contract-details">
                        <div class="contract-detail-row">
                            <span class="detail-label">Malzeme:</span>
                            <span><?php echo htmlspecialchars($contract['malzeme_ismi']); ?></span>
                        </div>
                        <div class="contract-detail-row">
                            <span class="detail-label">Birim Fiyat:</span>
                            <span><?php echo number_format($contract['birim_fiyat'], 2, ',', '.'); ?> <?php echo $contract['para_birimi']; ?></span>
                        </div>
                        <div class="contract-detail-row">
                            <span class="detail-label">Limit Miktar:</span>
                            <span><?php echo $contract['limit_miktar']; ?></span>
                        </div>
                        <div class="contract-detail-row">
                            <span class="detail-label">Ödenen Miktar:</span>
                            <span><?php echo $contract['toplu_odenen_miktar'] ?: 0; ?></span>
                        </div>
                        <div class="contract-detail-row">
                            <span class="detail-label">Başlangıç Tarihi:</span>
                            <span><?php echo $contract['baslangic_tarihi'] ? date('d.m.Y', strtotime($contract['baslangic_tarihi'])) : '-'; ?></span>
                        </div>
                        <div class="contract-detail-row">
                            <span class="detail-label">Bitiş Tarihi:</span>
                            <span><?php echo $contract['bitis_tarihi'] ? date('d.m.Y', strtotime($contract['bitis_tarihi'])) : '-'; ?></span>
                        </div>
                        <div class="contract-detail-row">
                            <span class="detail-label">Öncelik:</span>
                            <span><?php echo $contract['oncelik']; ?></span>
                        </div>
                        <?php if ($contract['aciklama']): ?>
                        <div class="contract-detail-row">
                            <span class="detail-label">Açıklama:</span>
                            <span><?php echo htmlspecialchars($contract['aciklama']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-items">Bu tedarikçiye ait çerçeve sözleşme bulunmamaktadır.</div>
        <?php endif; ?>
        
        <h3 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-top: 30px;">
            Gider Geçmişi
        </h3>
        
        <?php if (count($expenses) > 0): ?>
            <?php foreach ($expenses as $expense): ?>
                <div class="expense-card">
                    <div class="expense-header">
                        <div class="expense-title">Gider No: <?php echo $expense['gider_id']; ?></div>
                    </div>
                    
                    <div class="expense-details">
                        <div class="expense-detail-row">
                            <span class="detail-label">Tarih:</span>
                            <span><?php echo date('d.m.Y', strtotime($expense['tarih'])); ?></span>
                        </div>
                        <div class="expense-detail-row">
                            <span class="detail-label">Tutar:</span>
                            <span><?php echo number_format($expense['tutar'], 2, ',', '.'); ?> <?php echo $expense['para_birimi'] ?? 'TRY'; ?></span>
                        </div>
                        <div class="expense-detail-row">
                            <span class="detail-label">Kategori:</span>
                            <span><?php echo htmlspecialchars($expense['kategori']); ?></span>
                        </div>
                        <?php if ($expense['fatura_no']): ?>
                        <div class="expense-detail-row">
                            <span class="detail-label">Fatura No:</span>
                            <span><?php echo htmlspecialchars($expense['fatura_no']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($expense['odeme_tipi']): ?>
                        <div class="expense-detail-row">
                            <span class="detail-label">Ödeme Tipi:</span>
                            <span><?php echo htmlspecialchars($expense['odeme_tipi']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($expense['aciklama']): ?>
                        <div class="expense-detail-row">
                            <span class="detail-label">Açıklama:</span>
                            <span><?php echo htmlspecialchars($expense['aciklama']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-items">Bu tedarikçiye ait gider kaydı bulunmamaktadır.</div>
        <?php endif; ?>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
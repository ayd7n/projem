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
if (!yetkisi_var('page:view:gider_yonetimi')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Calculate total income and expenses for current month
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');

// Get total income by currency for current month
$income_result = $connection->query("SELECT para_birimi, SUM(tutar) as total FROM gelir_yonetimi WHERE tarih >= '$current_month_start' AND tarih <= '$current_month_end' GROUP BY para_birimi");
$monthly_income_by_currency = [];
while ($row = $income_result->fetch_assoc()) {
    $monthly_income_by_currency[$row['para_birimi']] = $row['total'];
}

// Get total expenses for current month
$expense_result = $connection->query("SELECT SUM(tutar) as total FROM gider_yonetimi WHERE tarih >= '$current_month_start' AND tarih <= '$current_month_end'");
$monthly_expenses = $expense_result->fetch_assoc()['total'] ?? 0;

// Get current cash balances
$kasalar_result = $connection->query("SELECT para_birimi, bakiye FROM sirket_kasasi");
$kasa_bakiyeleri = [];
while ($row = $kasalar_result->fetch_assoc()) {
    $kasa_bakiyeleri[$row['para_birimi']] = $row['bakiye'];
}

// Get exchange rates
$exchange_rates_result = $connection->query("SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')");
$exchange_rates = [];
while ($row = $exchange_rates_result->fetch_assoc()) {
    $key = str_replace('_kuru', '', $row['ayar_anahtar']);
    $exchange_rates[strtoupper($key)] = floatval($row['ayar_deger']);
}
$exchange_rates['TL'] = 1; // TL için kuru 1 olarak ayarla

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Şirket Kasası - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sirket_kasasi.css">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --accent-hover: #b39023;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --bg-color: #fdf8f5;
            --card-bg: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            font-size: 0.85rem;
        }

        .main-content {
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><g fill="%23ffffff" fill-opacity="0.05"><path d="M0 0h20L0 20z"/></g></svg>');
            opacity: 0.3;
        }

        .page-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0;
            color: var(--accent);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            margin: 0;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-card {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent);
        }

        .stat-card.blue::before {
            background: var(--info);
        }

        .stat-card.green::before {
            background: var(--success);
        }

        .stat-card.orange::before {
            background: var(--warning);
        }

        .stat-card.red::before {
            background: var(--danger);
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 0;
            opacity: 0.9;
            flex-shrink: 0;
        }

        .stat-content {
            flex-grow: 1;
        }

        .stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
            margin-bottom: 0;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .stat-card.green .stat-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .stat-card.orange .stat-icon {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .stat-card.red .stat-icon {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .content-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .card-header {
            background: white;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.8rem;
        }

        .table th {
            background: #f9fafb;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.02em;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            border-top: none;
        }

        .table td {
            padding: 0.4rem 1rem;
            vertical-align: middle;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background-color: #ffffcd;
        }

        .badge-pill {
            padding: 0.5em 1em;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .btn {
            border-radius: 0.25rem;
            padding: 0.35rem 0.8rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            font-size: 0.8rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-action {
            width: 24px;
            height: 24px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            margin-right: 3px;
            box-shadow: none;
            font-size: 0.7rem;
        }

        .search-group input {
            border-radius: 0.5rem 0 0 0.5rem;
            border: 1px solid var(--border-color);
            border-right: none;
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
        }

        .search-group input:focus {
            box-shadow: none;
            border-color: var(--border-color);
        }

        .search-group .btn {
            border-radius: 0 0.5rem 0.5rem 0;
            border: 1px solid var(--border-color);
            border-left: none;
            color: var(--text-secondary);
        }

        .search-group .btn:hover {
            background-color: transparent;
            color: var(--danger);
        }

        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 1rem 1rem 0 0;
            padding: 0.75rem 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            color: var(--accent);
        }

        .close {
            color: white;
            opacity: 0.8;
            text-shadow: none;
        }

        .close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .tab-content {
            padding: 1rem 0;
        }

        .nav-tabs {
            border-bottom: 1px solid var(--border-color);
        }

        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        .nav-tabs .nav-link.active {
            border-color: var(--border-color) var(--border-color) white;
            background-color: white;
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0.75rem;
            }

            .page-header {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top"
        style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php"><i
                    class="fas fa-spa"></i> IDO KOZMETIK</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="navigation.php">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış
                            Yap</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Modern Header -->
        <div class="page-header">
            <div>
                <h1>Şirket Kasası</h1>
                <p>Tüm parasal hareketleri ve kasa durumunu tek ekranda takip edin.</p>
            </div>
            <button id="kasaIslemBtn" class="btn btn-warning shadow-sm" style="background-color: var(--accent); border: none; color: var(--primary); font-weight: 700; padding: 0.6rem 1.2rem; border-radius: 0.5rem; z-index: 1;">
                <i class="fas fa-plus-circle"></i> Kasa İşlemi Yap
            </button>
        </div>

        <!-- Alerts -->
        <div id="alert-placeholder"></div>

        <!-- Statistics Grid -->
        <div class="stats-container">
            <!-- Kasa Bakiyeleri -->
            <?php foreach ($kasa_bakiyeleri as $para_birimi => $bakiye): ?>
                <div class="stat-card green" data-currency="<?php echo $para_birimi; ?>">
                    <div class="stat-icon">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <?php echo number_format($bakiye, 2, ',', '.'); ?> <?php echo $para_birimi; ?>
                        </div>
                        <div class="stat-label"><?php echo $para_birimi; ?> Kasası</div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Aylık Toplam Gelir -->
            <?php foreach ($monthly_income_by_currency as $para_birimi => $tutar): ?>
                <div class="stat-card blue" data-income-currency="<?php echo $para_birimi; ?>">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            <?php echo number_format($tutar, 2, ',', '.'); ?> <?php echo $para_birimi; ?>
                        </div>
                        <div class="stat-label">Bu Ay Toplam Gelir (<?php echo $para_birimi; ?>)</div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Aylık Toplam Gider -->
            <div class="stat-card red">
                <div class="stat-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="overallTotal"><?php echo number_format($monthly_expenses, 2, ',', '.'); ?> TL</div>
                    <div class="stat-label">Bu Ay Toplam Gider</div>
                </div>
            </div>
        </div>

        <!-- Tabs for Income and Expenses -->
        <ul class="nav nav-tabs" id="kasatabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="gelirler-tab" data-toggle="tab" href="#gelirler" role="tab">Gelirler</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="giderler-tab" data-toggle="tab" href="#giderler" role="tab">Giderler</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="kasa-islemleri-tab" data-toggle="tab" href="#kasa-islemleri" role="tab">Kasa
                    İşlemleri</a>
            </li>
        </ul>

        <div class="tab-content" id="kasatabsContent">
            <!-- Gelirler Tab -->
            <div class="tab-pane fade show active" id="gelirler" role="tabpanel">
                <div class="content-card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0"><i class="fas fa-arrow-down text-success"></i> Gelir Kayıtları</h5>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 15px;">
                            <!-- Search Box -->
                            <div class="input-group search-group" style="width: 300px;">
                                <input type="text" class="form-control" id="incomeSearchInput"
                                    placeholder="Müşteri veya Açıklama ara...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary border-left-0 bg-white" type="button"
                                        id="clearIncomeSearchBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Kategori</th>
                                        <th>Tutar</th>
                                        <th>Para Birimi</th>
                                        <th>Ödeme Tipi</th>
                                        <th>Müşteri</th>
                                        <th>Açıklama</th>
                                        <th>Kaydeden</th>
                                    </tr>
                                </thead>
                                <tbody id="incomesTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center p-5 text-muted">Veriler yükleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top">
                            <div class="records-per-page text-muted">
                                <small id="incomeTableInfo">-- / -- kayıt</small>
                                <small class="ml-2">Sayfa başına: </small>
                                <select class="custom-select custom-select-sm ml-2 form-control-sm d-inline-block"
                                    id="incomePerPageSelect" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm justify-content-center justify-content-md-end mb-0"
                                    id="incomesPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Giderler Tab -->
            <div class="tab-pane fade" id="giderler" role="tabpanel">
                <div class="content-card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0"><i class="fas fa-arrow-up text-danger"></i> Gider Kayıtları</h5>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 15px;">
                            <!-- Search Box -->
                            <div class="input-group search-group" style="width: 300px;">
                                <input type="text" class="form-control" id="expenseSearchInput"
                                    placeholder="Firma veya Açıklama ara...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary border-left-0 bg-white" type="button"
                                        id="clearExpenseSearchBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Kategori</th>
                                        <th>Tutar</th>
                                        <th>Ödeme Tipi</th>
                                        <th>Fatura No</th>
                                        <th>Açıklama</th>
                                        <th>Ödeme Yapılan Firma</th>
                                        <th>Kaydeden</th>
                                    </tr>
                                </thead>
                                <tbody id="expensesTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center p-5 text-muted">Veriler yükleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top">
                            <div class="records-per-page text-muted">
                                <small id="expenseTableInfo">-- / -- kayıt</small>
                                <small class="ml-2">Sayfa başına: </small>
                                <select class="custom-select custom-select-sm ml-2 form-control-sm d-inline-block"
                                    id="expensePerPageSelect" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm justify-content-center justify-content-md-end mb-0"
                                    id="expensesPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kasa İşlemleri Tab -->
            <div class="tab-pane fade" id="kasa-islemleri" role="tabpanel">
                <div class="content-card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0"><i class="fas fa-cash-register text-info"></i> Kasa İşlemleri</h5>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 15px;">
                            <!-- Search Box -->
                            <div class="input-group search-group" style="width: 300px;">
                                <input type="text" class="form-control" id="cashSearchInput"
                                    placeholder="Açıklama veya Personel ara...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary border-left-0 bg-white" type="button"
                                        id="clearCashSearchBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>İşlem Tipi</th>
                                        <th>Tutar</th>
                                        <th>Para Birimi</th>
                                        <th>Kaynak</th>
                                        <th>Açıklama</th>
                                        <th>Kaydeden</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="cashTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center p-5 text-muted">Veriler yükleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top">
                            <div class="records-per-page text-muted">
                                <small id="cashTableInfo">-- / -- kayıt</small>
                                <small class="ml-2">Sayfa başına: </small>
                                <select class="custom-select custom-select-sm ml-2 form-control-sm d-inline-block"
                                    id="cashPerPageSelect" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm justify-content-center justify-content-md-end mb-0"
                                    id="cashPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kasa İşlem Modal -->
    <div class="modal fade" id="kasaIslemModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="kasaIslemForm">
                    <div class="modal-header"
                        style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title" id="kasaIslemModalTitle"><i class="fas fa-cash-register"></i> Kasa İşlemi</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="kasa_islem_tipi">İşlem Tipi *</label>
                                    <select class="form-control" id="kasa_islem_tipi" name="kasa_islem_tipi" required>
                                        <option value="kasa_ekle">Kasaya Para Ekle</option>
                                        <option value="kasa_cikar">Kasadan Para Çıkar</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="kasa_para_birimi">Para Birimi *</label>
                                    <select class="form-control" id="kasa_para_birimi" name="kasa_para_birimi" required>
                                        <option value="TL">Türk Lirası (TL)</option>
                                        <option value="USD">Amerikan Doları (USD)</option>
                                        <option value="EUR">Euro (EUR)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="kasa_tutar">Tutar *</label>
                                    <input type="number" class="form-control" id="kasa_tutar" name="kasa_tutar" step="0.01"
                                        min="0" required placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="kasa_tarih">Tarih *</label>
                                    <input type="datetime-local" class="form-control" id="kasa_tarih" name="kasa_tarih" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="kasa_aciklama">Açıklama</label>
                            <textarea class="form-control" id="kasa_aciklama" name="kasa_aciklama" rows="2"
                                placeholder="İşlem açıklaması..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                class="fas fa-times"></i> İptal</button>
                        <button type="submit" class="btn btn-primary" id="kasaSubmitBtn"><i class="fas fa-save"></i>
                            Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/sirket_kasasi.js"></script>
</body>

</html>

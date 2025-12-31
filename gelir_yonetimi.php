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

// Page-level permission check (assuming same as gider_yonetimi or separate, let's use a new one if possible, or same for now)
// Ideally we should add 'page:view:gelir_yonetimi' to permissions but for now assuming admin has all access or we skip strict check or reuse
if (!yetkisi_var('page:view:gider_yonetimi')) { // Using same permission for simplicity or allow all staff
    // die('Bu sayfayı görüntüleme yetkiniz yok.'); 
}

// Calculate total income for current month
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');
$total_result = $connection->query("SELECT SUM(tutar) as total FROM gelir_yonetimi WHERE tarih >= '$current_month_start' AND tarih <= '$current_month_end'");
$total_income = $total_result->fetch_assoc()['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Gelir Yönetimi - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
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
            /* Reduced base font size */
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
            /* Changed from column to row layout */
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
            /* Very light yellow hover */
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

        /* Input group wrapper focus state check if needed, or just focus ring on input but handle neighbour */
        .search-group input:focus+.input-group-append .btn {
            border-color: var(--border-color);
            border-left: none;
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

        /* Modal Styles */
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
                <h1>Sipariş Tahsilat Yönetimi</h1>
                <p>Müşteri siparişlerine ait ödemeleri ve tahsilat durumlarını buradan yönetebilirsiniz.</p>
            </div>
        </div>

        <!-- Alerts -->
        <div id="alert-placeholder"></div>

        <!-- Statistics Grid -->
        <div class="stats-container">
            <!-- Stat 1: Monthly Income -->
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="overallTotal">
                        <?php echo number_format($total_income, 2, ',', '.'); ?> TL
                    </div>
                    <div class="stat-label">Bu Ay Toplam Tahsilat</div>
                </div>
            </div>

            <!-- Stat 2: Pending Orders -->
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="pendingCount">0</div>
                    <div class="stat-label">Ödeme Bekleyen Sipariş</div>
                </div>
            </div>

            <!-- Stat 3: Total Receivables (Includes Installments) -->
            <div class="stat-card red">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="pendingTotal">0,00 TL</div>
                    <div class="stat-label">Toplam Bekleyen Alacak</div>
                </div>
            </div>
            
            <!-- Stat 4: Active Installment Plans -->
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="activePlansCount">0</div>
                    <div class="stat-label">Aktif Taksit Planı</div>
                </div>
            </div>

            <!-- Stat 5: Overdue Installments -->
            <div class="stat-card red">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="overdueInstallmentsTotal">0,00 TL</div>
                    <div class="stat-label text-danger font-weight-bold" id="overdueInstallmentsLabel">Gecikmiş Taksit (0)</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="incomeTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tahsilat-tab" data-toggle="tab" href="#tahsilatlar" role="tab" aria-controls="tahsilatlar" aria-selected="true">
                    <i class="fas fa-list"></i> Tahsilat Listesi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="taksit-tab" data-toggle="tab" href="#taksitler" role="tab" aria-controls="taksitler" aria-selected="false">
                    <i class="fas fa-calendar-alt"></i> Taksit Planları
                </a>
            </li>
        </ul>

        <div class="tab-content" id="incomeTabsContent">
            <!-- Tahsilatlar Tab -->
            <div class="tab-pane fade show active" id="tahsilatlar" role="tabpanel" aria-labelledby="tahsilat-tab">
                <div class="content-card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <button id="addIncomeBtn" class="btn btn-success">
                                <i class="fas fa-plus"></i> Yeni Tahsilat Ekle
                            </button>
                        </div>

                        <div class="d-flex align-items-center" style="gap: 15px;">
                            <!-- Search Box -->
                            <div class="input-group search-group" style="width: 300px;">
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Müşteri veya Sipariş No ara...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary border-left-0 bg-white" type="button"
                                        id="clearSearchBtn">
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
                                        <th>İşlemler</th>
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
                                        <td colspan="9" class="text-center p-5 text-muted">Veriler yükleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top">
                            <div class="records-per-page text-muted">
                                <small>Sayfa başına: </small>
                                <select class="custom-select custom-select-sm ml-2 form-control-sm d-inline-block"
                                    id="perPageSelect" style="width: auto;">
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

            <!-- Taksitler Tab -->
            <div class="tab-pane fade" id="taksitler" role="tabpanel" aria-labelledby="taksit-tab">
                <div class="content-card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <button id="createPlanBtn" class="btn btn-primary">
                                <i class="fas fa-folder-plus"></i> Yeni Taksit Planı Oluştur
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>İşlemler</th>
                                        <th>Müşteri</th>
                                        <th>Toplam Tutar</th>
                                        <th>Taksit Sayısı</th>
                                        <th>Ödenen</th>
                                        <th>Başlangıç</th>
                                        <th>Durum</th>
                                        <th>Açıklama</th>
                                    </tr>
                                </thead>
                                <tbody id="plansTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center p-5 text-muted">Planlar yükleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Plan Modal -->
    <div class="modal fade" id="createPlanModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-file-invoice-dollar"></i> Taksit Planı Oluştur</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="createPlanForm">
                        <input type="hidden" name="action" value="create_installment_plan">
                        
                        <!-- Step 1: Select Customer -->
                        <div class="form-group">
                            <label class="font-weight-bold">1. Müşteri Seçimi</label>
                            <select class="form-control" id="planCustomerSelect" name="musteri_id" required>
                                <option value="">Yükleniyor...</option>
                            </select>
                            <small class="text-muted">Sadece borçlu siparişi olan müşteriler listelenir.</small>
                        </div>

                        <!-- Step 2: Select Orders -->
                        <div class="form-group" id="orderSelectionArea" style="display:none;">
                            <label class="font-weight-bold">2. Sipariş Seçimi (Bir veya Birden Fazla)</label>
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 30px;"><input type="checkbox" id="selectAllOrders"></th>
                                            <th>Sipariş No</th>
                                            <th>Tarih</th>
                                            <th>Toplam</th>
                                            <th>Kalan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customerOrdersTable"></tbody>
                                </table>
                            </div>
                            <div class="text-right mt-2 font-weight-bold text-danger">
                                Seçilen Toplam Anapara: <span id="selectedPrincipal">0.00</span> TL
                            </div>
                        </div>

                        <!-- Step 3: Plan Config -->
                        <div class="form-row bg-light p-3 rounded border mb-3">
                            <div class="form-group col-md-4">
                                <label>Taksit Sayısı</label>
                                <input type="number" class="form-control" name="taksit_sayisi" id="planInstallmentCount" value="3" min="1" max="60" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Vade Farkı Oranı (%)</label>
                                <input type="number" class="form-control" name="vade_farki_orani" id="planInterestRate" value="0" min="0" step="0.01">
                            </div>
                            <div class="form-group col-md-4">
                                <label>İlk Taksit Tarihi</label>
                                <input type="date" class="form-control" name="baslangic_tarihi" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="alert alert-info mb-0 p-2">
                                    <strong>Hesaplanan Toplam:</strong> <span id="calculatedTotal">0.00</span> TL 
                                    (Aylık Yaklaşık: <span id="monthlyAmount">0.00</span> TL)
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Plan Açıklaması</label>
                            <textarea class="form-control" name="aciklama" rows="2" placeholder="Örn: 2025 Yaz sezonu ödeme planı"></textarea>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Planı Oluştur</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Details Modal -->
    <div class="modal fade" id="planDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Plan Detayları</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="planDetailsContent">Yükleniyor...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Modal -->
    <div class="modal fade" id="incomeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="incomeForm">
                    <div class="modal-header"
                        style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-edit"></i> Gelir Formu</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body pt-0">
                        <input type="hidden" id="gelir_id" name="gelir_id">
                        <input type="hidden" id="action" name="action">
                        <input type="hidden" id="siparis_id" name="siparis_id">
                        <input type="hidden" id="musteri_id" name="musteri_id">

                        <!-- Sipariş Seçimi -->
                        <div class="form-group mb-2">
                            <label for="siparis_secimi" class="small font-weight-bold">Bağlı Sipariş
                                (Opsiyonel)</label>
                            <select class="form-control form-control-sm" id="siparis_secimi" name="siparis_secimi">
                                <option value="">Sipariş Seçiniz...</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <!-- Tarih -->
                            <div class="form-group col-md-3 mb-2">
                                <label for="tarih" class="small font-weight-bold">Tarih</label>
                                <input type="date" class="form-control form-control-sm" id="tarih" name="tarih"
                                    required>
                            </div>
                            <!-- Kategori -->
                            <div class="form-group col-md-3 mb-2">
                                <label for="kategori" class="small font-weight-bold">Kategori</label>
                                <select class="form-control form-control-sm" id="kategori" name="kategori" required>
                                    <option value="">Seçiniz...</option>
                                    <option value="Sipariş Ödemesi">Sipariş Ödemesi</option>
                                    <option value="Perakende Satış">Perakende Satış</option>
                                    <option value="Hizmet Geliri">Hizmet Geliri</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                            </div>
                            <!-- Tutar -->
                            <div class="form-group col-md-3 mb-2">
                                <label for="tutar" class="small font-weight-bold">Tutar</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control" id="tutar"
                                        name="tutar" required placeholder="0.00">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="currency-display">TL</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Ödeme Tipi -->
                            <div class="form-group col-md-3 mb-2">
                                <label for="odeme_tipi" class="small font-weight-bold">Ödeme Tipi</label>
                                <select class="form-control form-control-sm" id="odeme_tipi" name="odeme_tipi" required>
                                    <option value="Nakit">Nakit</option>
                                    <option value="Kredi Kartı">Kredi Kartı</option>
                                    <option value="Havale/EFT">Havale/EFT</option>
                                    <option value="Çek">Çek</option>
                                </select>
                            </div>
                        </div>
                        <!-- Para Birimi (Otomatik - Gizli) -->
                        <input type="hidden" id="para_birimi" name="para_birimi" value="TL">

                        <!-- Müşteri Adı -->
                        <div class="form-group mb-2">
                            <label for="musteri_adi" class="small font-weight-bold">Müşteri Adı / Unvanı</label>
                            <input type="text" class="form-control form-control-sm" id="musteri_adi" name="musteri_adi"
                                placeholder="Opsiyonel">
                        </div>

                        <!-- Açıklama -->
                        <div class="form-group mb-3">
                            <label for="aciklama" class="small font-weight-bold">Açıklama</label>
                            <textarea class="form-control form-control-sm" id="aciklama" name="aciklama" rows="2"
                                required placeholder="Gelir hakkında kısa bilgi..."></textarea>
                        </div>

                        <div class="text-right">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i>
                                Kaydet</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            const api_url = 'api_islemleri/gelir_yonetimi_islemler.php';
            let currentPage = 1;
            let perPage = 10;
            let searchTimeout;

            function loadIncomes(page = 1) {
                const search = $('#searchInput').val();
                $('#incomesTableBody').html('<tr><td colspan="9" class="text-center">Yükleniyor...</td></tr>');

                $.get(api_url, { action: 'get_incomes', page: page, per_page: perPage, search: search }, function (response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        renderTable(res.data);
                        renderPagination(res.page, Math.ceil(res.total / res.per_page));

                        // Handle multiple currencies for overall total
                        let overallHtml = '';
                        if (typeof res.overall_sum === 'object' && res.overall_sum !== null) {
                            let parts = [];
                            for (const [curr, val] of Object.entries(res.overall_sum)) {
                                if (parseFloat(val) > 0) {
                                    parts.push(new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2 }).format(val) + ' ' + curr);
                                }
                            }
                            overallHtml = parts.length > 0 ? parts.join(' <br> ') : '0,00 TL';
                        } else {
                            overallHtml = new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2 }).format(res.overall_sum) + ' TL';
                        }
                        $('#overallTotal').html(overallHtml);
                    } else {
                        $('#incomesTableBody').html('<tr><td colspan="9" class="text-center text-danger">Veri alınamadı: ' + res.message + '</td></tr>');
                    }
                });
            }

            function renderTable(data) {
                let html = '';
                if (data.length === 0) {
                    html = '<tr><td colspan="9" class="text-center">Kayıt bulunamadı.</td></tr>';
                } else {
                    data.forEach(item => {
                        const date = new Date(item.tarih).toLocaleDateString('tr-TR');
                        const amount = new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2 }).format(item.tutar);
                        const currency = item.para_birimi || 'TL';
                        const currencySymbols = { 'TL': '₺', 'USD': '$', 'EUR': '€' };
                        const currencySymbol = currencySymbols[currency] || currency;
                        const currencyBadge = currency === 'TL' ? 'badge-success' : (currency === 'USD' ? 'badge-warning' : 'badge-info');

                        html += `
                            <tr>
                                <td style="width: 120px;">
                                    <div class="d-flex">
                                        <button class="btn btn-warning btn-action edit-btn" data-id="${item.gelir_id}" title="Düzenle"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-action delete-btn" data-id="${item.gelir_id}" title="Sil"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                                <td>${date}</td>
                                <td><span class="badge badge-light" style="font-size: 0.9em;">${item.kategori}</span></td>
                                <td class="font-weight-bold" style="color: var(--success); font-size: 1.1em;">${amount} ${currencySymbol}</td>
                                <td><span class="badge badge-pill ${currencyBadge}">${currency}</span></td>
                                <td><span class="badge badge-pill badge-info">${item.odeme_tipi || '-'}</span></td>
                                <td>${item.musteri_adi || '-'}</td>
                                <td>${item.aciklama}</td>
                                <td><small class="text-muted"><i class="fas fa-user-circle"></i> ${item.kaydeden_personel_ismi || '-'}</small></td>
                            </tr>
                        `;
                    });
                }
                $('#incomesTableBody').html(html);
            }

            function renderPagination(current, total) {
                let html = '';
                if (total > 1) {
                    html += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current - 1}">«</a></li>`;
                    for (let i = 1; i <= total; i++) {
                        html += `<li class="page-item ${current === i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    }
                    html += `<li class="page-item ${current === total ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current + 1}">»</a></li>`;
                }
                $('#incomesPagination').html(html);
            }

            function loadPendingStats() {
                $.get(api_url, { action: 'get_pending_stats' }, function (response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        // 1. Pending Orders Count
                        $('#pendingCount').text(res.data.pending_orders_count);

                        // 2. Total Receivables (Orders + Installments)
                        let receivablesHtml = '';
                        if (res.data.total_receivables && Object.keys(res.data.total_receivables).length > 0) {
                            let parts = [];
                            for (const [curr, val] of Object.entries(res.data.total_receivables)) {
                                if (parseFloat(val) > 0.01) {
                                    parts.push(new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2 }).format(val) + ' ' + curr);
                                }
                            }
                            receivablesHtml = parts.length > 0 ? parts.join(' <br> ') : '0,00 TL';
                        } else {
                            receivablesHtml = '0,00 TL';
                        }
                        $('#pendingTotal').html(receivablesHtml);

                        // 3. Active Plans Count
                        $('#activePlansCount').text(res.data.active_plans_count);

                        // 4. Overdue Installments
                        const overdue = res.data.overdue_installments;
                        $('#overdueInstallmentsLabel').text(`Gecikmiş Taksit (${overdue.count})`);
                        
                        let overdueHtml = '';
                        if (overdue.totals && Object.keys(overdue.totals).length > 0) {
                            let parts = [];
                            for (const [curr, val] of Object.entries(overdue.totals)) {
                                if (parseFloat(val) > 0.01) {
                                    parts.push(new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2 }).format(val) + ' ' + curr);
                                }
                            }
                            overdueHtml = parts.length > 0 ? parts.join(' <br> ') : '0,00 TL';
                        } else {
                            overdueHtml = '0,00 TL';
                        }
                        $('#overdueInstallmentsTotal').html(overdueHtml);
                    }
                });
            }

            // --- Installment Plan Logic ---

            function loadInstallmentPlans(page = 1) {
                $('#plansTableBody').html('<tr><td colspan="8" class="text-center">Yükleniyor...</td></tr>');
                $.get(api_url, { action: 'get_installment_plans', page: page }, function(response) {
                    const res = JSON.parse(response);
                    if(res.status === 'success') {
                        let html = '';
                        if(res.data.length === 0) {
                            html = '<tr><td colspan="8" class="text-center">Henüz plan oluşturulmamış.</td></tr>';
                        } else {
                            res.data.forEach(p => {
                                const statusColors = {'aktif': 'badge-success', 'tamamlandi': 'badge-secondary', 'iptal': 'badge-danger'};
                                const statusLabels = {'aktif': 'Aktif', 'tamamlandi': 'Tamamlandı', 'iptal': 'İptal'};
                                
                                html += `
                                    <tr>
                                        <td>
                                            <button class="btn btn-info btn-sm view-plan-btn" data-id="${p.plan_id}"><i class="fas fa-eye"></i> Detay</button>
                                            ${p.durum === 'aktif' ? `<button class="btn btn-danger btn-sm cancel-plan-btn" data-id="${p.plan_id}"><i class="fas fa-times"></i> İptal</button>` : ''}
                                        </td>
                                        <td>${p.musteri_adi}</td>
                                        <td class="font-weight-bold">${new Intl.NumberFormat('tr-TR', {minimumFractionDigits:2}).format(p.toplam_odenecek)} ${p.para_birimi}</td>
                                        <td>${p.odenen_taksit} / ${p.toplam_taksit_sayisi}</td>
                                        <td>${new Intl.NumberFormat('tr-TR', {minimumFractionDigits:2}).format(p.odenen_tutar || 0)} ${p.para_birimi}</td>
                                        <td>${new Date(p.baslangic_tarihi).toLocaleDateString('tr-TR')}</td>
                                        <td><span class="badge ${statusColors[p.durum]}">${statusLabels[p.durum]}</span></td>
                                        <td>${p.aciklama}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#plansTableBody').html(html);
                    }
                });
            }

            // Tab Switch Listener
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if (e.target.id === 'taksit-tab') {
                    loadInstallmentPlans();
                } else {
                    loadIncomes(); // Reload incomes when switching back
                }
            });

            $('#createPlanBtn').click(function() {
                // Load customers
                $.get(api_url, { action: 'get_customers_with_debt' }, function(response) {
                    const res = JSON.parse(response);
                    let opts = '<option value="">Seçiniz...</option>';
                    if(res.status === 'success') {
                        res.data.forEach(c => {
                            opts += `<option value="${c.musteri_id}">${c.musteri_adi}</option>`;
                        });
                    }
                    $('#planCustomerSelect').html(opts);
                    $('#orderSelectionArea').hide();
                    $('#customerOrdersTable').empty();
                    $('#createPlanForm')[0].reset();
                    $('#createPlanModal').modal('show');
                });
            });

            $('#planCustomerSelect').change(function() {
                const cid = $(this).val();
                if(!cid) {
                    $('#orderSelectionArea').hide();
                    return;
                }
                
                $.get(api_url, { action: 'get_customer_orders_for_plan', musteri_id: cid }, function(response) {
                    const res = JSON.parse(response);
                    let html = '';
                    if(res.status === 'success' && res.data.length > 0) {
                        res.data.forEach(o => {
                            html += `
                                <tr>
                                    <td><input type="checkbox" class="order-check" value="${o.siparis_id}" data-amount="${o.kalan_tutar}"></td>
                                    <td>#${o.siparis_id}</td>
                                    <td>${new Date(o.tarih).toLocaleDateString('tr-TR')}</td>
                                    <td>${new Intl.NumberFormat('tr-TR').format(o.toplam_tutar)} ${o.para_birimi}</td>
                                    <td class="text-danger font-weight-bold">${new Intl.NumberFormat('tr-TR').format(o.kalan_tutar)} ${o.para_birimi}</td>
                                </tr>
                            `;
                        });
                        $('#customerOrdersTable').html(html);
                        $('#orderSelectionArea').show();
                    } else {
                        $('#customerOrdersTable').html('<tr><td colspan="5">Borçlu sipariş bulunamadı.</td></tr>');
                        $('#orderSelectionArea').show();
                    }
                });
            });

            // Calculation Logic
            function calculatePlan() {
                let principal = 0;
                $('.order-check:checked').each(function() {
                    principal += parseFloat($(this).data('amount'));
                });
                $('#selectedPrincipal').text(new Intl.NumberFormat('tr-TR').format(principal));

                const count = parseInt($('#planInstallmentCount').val()) || 1;
                const rate = parseFloat($('#planInterestRate').val()) || 0;
                
                const interest = principal * (rate / 100);
                const total = principal + interest;
                const monthly = total / count;

                $('#calculatedTotal').text(new Intl.NumberFormat('tr-TR').format(total));
                $('#monthlyAmount').text(new Intl.NumberFormat('tr-TR').format(monthly));
            }

            $(document).on('change', '.order-check, #planInstallmentCount, #planInterestRate', calculatePlan);
            
            $('#selectAllOrders').change(function() {
                $('.order-check').prop('checked', $(this).prop('checked'));
                calculatePlan();
            });

            $('#createPlanForm').submit(function(e) {
                e.preventDefault();
                // Check if orders selected
                if($('.order-check:checked').length === 0) {
                    Swal.fire('Hata', 'En az bir sipariş seçmelisiniz.', 'warning');
                    return;
                }
                
                const orderIds = [];
                $('.order-check:checked').each(function() { orderIds.push($(this).val()); });
                
                const formData = $(this).serializeArray();
                formData.push({name: 'siparis_ids[]', value: orderIds}); // Use map if backend expects multiple keys, but simple push works if tailored or use jQuery param properly
                
                // Construct proper data object for arrays
                let data = {
                    action: 'create_installment_plan',
                    musteri_id: $('#planCustomerSelect').val(),
                    siparis_ids: orderIds,
                    taksit_sayisi: $('#planInstallmentCount').val(),
                    vade_farki_orani: $('#planInterestRate').val(),
                    baslangic_tarihi: $('input[name="baslangic_tarihi"]').val(),
                    aciklama: $('textarea[name="aciklama"]').val()
                };

                $.post(api_url, data, function(response) {
                    const res = JSON.parse(response);
                    if(res.status === 'success') {
                        $('#createPlanModal').modal('hide');
                        Swal.fire('Başarılı', res.message, 'success');
                        loadInstallmentPlans();
                        loadPendingStats(); // Anlık kutucuk güncellemesi
                        loadPendingOrders(); // Bekleyen sipariş listesi güncellemesi
                    } else {
                        Swal.fire('Hata', res.message, 'error');
                    }
                });
            });

            $(document).on('click', '.cancel-plan-btn', function() {
                const pid = $(this).data('id');
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: 'Bu taksit planı iptal edilecek. Bağlı siparişler tekrar tahsilat listesinde borçlu olarak görünecek.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, İptal Et',
                    cancelButtonText: 'Hayır'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(api_url, { action: 'cancel_installment_plan', plan_id: pid }, function(response) {
                            const res = JSON.parse(response);
                            if(res.status === 'success') {
                                Swal.fire('İptal Edildi', res.message, 'success');
                                loadInstallmentPlans();
                                loadPendingStats();
                                loadPendingOrders();
                            } else {
                                Swal.fire('Hata', res.message, 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.view-plan-btn', function() {
                const pid = $(this).data('id');
                $('#planDetailsContent').html('Yükleniyor...');
                $('#planDetailsModal').modal('show');
                
                $.get(api_url, { action: 'get_plan_details', plan_id: pid }, function(response) {
                    const res = JSON.parse(response);
                    if(res.status === 'success') {
                        const p = res.plan;
                        let html = `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Müşteri: <b>${p.musteri_adi}</b></h6>
                                    <h6>Toplam Borç: <b>${new Intl.NumberFormat('tr-TR').format(p.toplam_odenecek)} ${p.para_birimi}</b></h6>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span class="badge badge-secondary">Plan #${p.plan_id}</span>
                                </div>
                            </div>
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light"><tr><th>No</th><th>Vade</th><th>Tutar</th><th>Kalan</th><th>Durum</th><th>İşlem</th></tr></thead>
                                <tbody>
                        `;
                        
                        res.installments.forEach(i => {
                            let btn = '';
                            if(i.durum !== 'odendi') {
                                btn = `<button class="btn btn-success btn-sm btn-action pay-installment-btn" data-id="${i.taksit_id}" title="Ödeme Al"><i class="fas fa-check"></i></button>`;
                            } else {
                                btn = `<i class="fas fa-check-circle text-success"></i> ${new Date(i.odeme_tarihi).toLocaleDateString('tr-TR')}`;
                            }
                            
                            // Check overdue
                            let rowClass = '';
                            if(i.durum !== 'odendi' && new Date(i.vade_tarihi) < new Date()) {
                                rowClass = 'table-danger';
                            }
                            
                            html += `
                                <tr class="${rowClass}">
                                    <td>${i.sira_no}</td>
                                    <td>${new Date(i.vade_tarihi).toLocaleDateString('tr-TR')}</td>
                                    <td>${new Intl.NumberFormat('tr-TR').format(i.tutar)}</td>
                                    <td>${new Intl.NumberFormat('tr-TR').format(i.kalan_tutar)}</td>
                                    <td>${i.durum}</td>
                                    <td>${btn}</td>
                                </tr>
                            `;
                        });
                        html += '</tbody></table>';
                        
                        html += '<h6 class="mt-3">Kapsanan Siparişler</h6><ul>';
                        res.orders.forEach(o => {
                            html += `<li>Sipariş #${o.siparis_id} - ${new Intl.NumberFormat('tr-TR').format(o.tutar_katkisi)} katkı</li>`;
                        });
                        html += '</ul>';
                        
                        $('#planDetailsContent').html(html);
                    }
                });
            });

            $(document).on('click', '.pay-installment-btn', function() {
                const tid = $(this).data('id');
                Swal.fire({
                    title: 'Ödeme Al',
                    text: 'Bu taksit için ödeme kaydı oluşturulacak.',
                    input: 'select',
                    inputOptions: {
                        'Nakit': 'Nakit',
                        'Kredi Kartı': 'Kredi Kartı',
                        'Havale/EFT': 'Havale/EFT'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Öde'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(api_url, { action: 'pay_installment', taksit_id: tid, odeme_tipi: result.value }, function(response) {
                            const res = JSON.parse(response);
                            if(res.status === 'success') {
                                Swal.fire('Ödendi', res.message, 'success');
                                $('#planDetailsModal').modal('hide');
                                loadInstallmentPlans();
                                loadPendingStats(); // Anlık kutucuk güncellemesi
                            } else {
                                Swal.fire('Hata', res.message, 'error');
                            }
                        });
                    }
                });
            });

            // Initial load
            loadIncomes();

            loadPendingStats();

            $('#searchInput').on('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => { currentPage = 1; loadIncomes(); }, 500);
            });

            $('#clearSearchBtn').click(function () {
                $('#searchInput').val('');
                currentPage = 1;
                loadIncomes();
            });

            $('#perPageSelect').change(function () {
                perPage = $(this).val();
                currentPage = 1;
                loadIncomes();
            });

            $(document).on('click', '.page-link', function (e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page) { currentPage = page; loadIncomes(page); }
            });

            $('#addIncomeBtn').click(function () {
                $('#incomeForm')[0].reset();
                $('#action').val('add_income');
                $('#gelir_id').val('');
                $('#modalTitle').html('<i class="fas fa-plus"></i> Yeni Tahsilat Ekle');
                $('#tarih').val(new Date().toISOString().split('T')[0]);
                $('#para_birimi').val('TL'); // Set default currency
                $('#currency-display').text('TL');

                // Clear order selection
                $('#siparis_secimi').val('');
                $('#siparis_id').val('');
                $('#musteri_id').val('');

                loadPendingOrders(); // Reload orders
                $('#incomeModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function () {
                const id = $(this).data('id');
                $.get(api_url, { action: 'get_income', id: id }, function (response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        const data = res.data;
                        $('#action').val('update_income');
                        $('#gelir_id').val(data.gelir_id);
                        $('#siparis_id').val(data.siparis_id || '');
                        $('#musteri_id').val(data.musteri_id || '');
                        $('#tarih').val(data.tarih.split(' ')[0]);
                        $('#tutar').val(data.tutar);
                        $('#para_birimi').val(data.para_birimi || 'TL');
                        $('#currency-display').text(data.para_birimi || 'TL');
                        $('#kategori').val(data.kategori);
                        $('#odeme_tipi').val(data.odeme_tipi);
                        $('#musteri_adi').val(data.musteri_adi);
                        $('#aciklama').val(data.aciklama);
                        $('#modalTitle').html('<i class="fas fa-edit"></i> Tahsilat Düzenle');
                        $('#incomeModal').modal('show');
                    }
                });
            });

            $(document).on('click', '.delete-btn', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: "Bu gelir kaydını silmek istediğinize emin misiniz?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Evet, Sil',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(api_url, { action: 'delete_income', gelir_id: id }, function (response) {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire('Silindi!', res.message, 'success');
                                loadIncomes(currentPage);
                                loadPendingStats();
                                loadPendingOrders();
                            } else {
                                Swal.fire('Hata!', res.message, 'error');
                            }
                        });
                    }
                });
            });

            function loadPendingOrders() {
                $.get(api_url, { action: 'get_pending_orders' }, function (response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        let options = '<option value="">Sipariş Seçiniz...</option>';
                        res.data.forEach(order => {
                            const date = new Date(order.tarih).toLocaleDateString('tr-TR');
                            const remaining = new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2 }).format(order.kalan_tutar);
                            const currency = order.para_birimi || 'TL';
                            options += `<option value="${order.siparis_id}" data-customer="${order.musteri_adi}" data-customer-id="${order.musteri_id}" data-remaining="${order.kalan_tutar}" data-currency="${currency}">
                                Sipariş #${order.siparis_id} - ${order.musteri_adi} - Kalan: ${remaining} ${currency} (${date})
                            </option>`;
                        });
                        $('#siparis_secimi').html(options);
                    }
                });
            }

            $('#siparis_secimi').change(function () {
                const selected = $(this).find(':selected');
                const siparis_id = $(this).val();

                if (siparis_id) {
                    const customer = selected.data('customer');
                    const customerId = selected.data('customer-id');
                    const remaining = selected.data('remaining');
                    const currency = selected.data('currency') || 'TL';

                    $('#siparis_id').val(siparis_id);
                    $('#musteri_id').val(customerId);
                    $('#musteri_adi').val(customer);
                    $('#tutar').val(remaining);
                    $('#para_birimi').val(currency);
                    $('#currency-display').text(currency); // Update visible label
                    $('#kategori').val('Sipariş Ödemesi');
                    $('#aciklama').val(`Sipariş No: #${siparis_id} tahsilatı`);

                    // Flash effect to show auto-filled
                    $('#tutar, #kategori, #aciklama, #musteri_adi').addClass('bg-light');
                    setTimeout(() => { $('#tutar, #kategori, #aciklama, #musteri_adi').removeClass('bg-light'); }, 500);
                } else {
                    $('#siparis_id').val('');
                    $('#musteri_id').val('');
                    $('#para_birimi').val('TL'); // Reset to default
                    $('#currency-display').text('TL');
                    // Only clear if user wants? Or keep it?
                    // Better clear to avoid confusion if they unchecked it
                    // $('#musteri_adi').val('');
                    // $('#tutar').val('');
                }
            });

            $('#incomeForm').submit(function (e) {
                e.preventDefault();
                $.post(api_url, $(this).serialize(), function (response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        $('#incomeModal').modal('hide');
                        Swal.fire('Başarılı!', res.message, 'success');
                        loadIncomes(currentPage);
                        // Refresh pending orders too
                        loadPendingOrders();
                        loadPendingStats();
                    } else {
                        Swal.fire('Hata!', res.message, 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>
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
if (!yetkisi_var('page:view:raporlar')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Tedarikçi Ödeme Raporu - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <!-- Apache ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <link rel="stylesheet" href="assets/css/stil.css">
    <style>
        .chart-container {
            width: 100%;
            height: 500px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            margin: 0;
            font-weight: 700;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .table-container {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .currency-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-tl {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-usd {
            background-color: #28a745;
            color: white;
        }
        
        .badge-eur {
            background-color: #ffc107;
            color: #333;
        }
        
        /* Disable word wrap for table cells */
        table th,
        table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Pagination styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding: 0.5rem 0;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .pagination {
            margin: 0;
        }
        
        .page-info {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .chart-container {
                height: 350px;
            }
            
            .stat-card h3 {
                font-size: 1.8rem;
            }
            
            .stat-card p {
                font-size: 0.9rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .page-header p {
                font-size: 0.9rem;
            }
            
            .card-header h2 {
                font-size: 1.2rem;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            table th,
            table td {
                padding: 0.5rem;
            }
            
            .pagination-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .pagination {
                width: 100%;
                justify-content: center;
            }
            
            .page-info {
                width: 100%;
                text-align: center;
                margin-bottom: 0.5rem;
            }
            
            .search-container {
                margin-top: 0.5rem;
            }
            
            .alert {
                font-size: 0.9rem;
            }
            
            .alert h5 {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .chart-container {
                height: 300px;
            }
            
            .stat-card h3 {
                font-size: 1.5rem;
            }
            
            .stat-card {
                padding: 0.75rem;
            }
            
            .page-header h1 {
                font-size: 1.3rem;
            }
            
            table {
                font-size: 0.75rem;
            }
            
            table th,
            table td {
                padding: 0.4rem;
            }
            
            .pagination .page-link {
                padding: 0.4rem 0.6rem;
                font-size: 0.85rem;
            }
            
            .currency-badge {
                font-size: 0.75rem;
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="navigation.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="raporlar.php">Raporlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-money-bill-wave"></i> Tedarikçi Ödeme Raporu</h1>
                <p>Tedarikçilere yapılan ödemeleri TL bazında analiz edin</p>
            </div>
        </div>

        <!-- Rapor Açıklaması -->
        <div class="alert alert-info" role="alert">
            <h5 class="alert-heading"><i class="fas fa-info-circle"></i> Rapor Hakkında</h5>
            <p class="mb-0">
                Bu rapor, çerçeve sözleşmeler kapsamında tedarikçilere yapılan tüm ödemeleri gösterir. 
                Farklı para birimlerindeki (TL, USD, EUR) ödemeler, güncel döviz kurları kullanılarak Türk Lirası'na çevrilir ve tedarikçi bazında toplanır. 
                Böylece hangi tedarikçiye toplam ne kadar ödeme yapıldığını kolayca görebilir ve karşılaştırabilirsiniz.
            </p>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-building" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                        <div>
                            <h3 id="totalSuppliers">-</h3>
                            <p>Toplam Tedarikçi</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-lira-sign" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                        <div>
                            <h3 id="totalPayments">-</h3>
                            <p>Toplam Ödeme (TL)</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-receipt" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                        <div>
                            <h3 id="totalTransactions">-</h3>
                            <p>Toplam İşlem Sayısı</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Rates Info -->
        <div class="alert alert-info" role="alert">
            <h5 class="alert-heading"><i class="fas fa-info-circle"></i> Döviz Kurları</h5>
            <p class="mb-0">
                <strong>USD:</strong> <span id="usdRate">-</span> TL &nbsp;|&nbsp;
                <strong>EUR:</strong> <span id="eurRate">-</span> TL
            </p>
        </div>

        <!-- Chart Section -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-chart-pie"></i> Tedarikçi Bazında Ödeme Dağılımı</h2>
            </div>
            <div class="card-body">
                <div id="paymentsChart" class="chart-container"></div>
            </div>
        </div>

        <!-- Supplier Totals Table -->
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="mb-2 mb-md-0"><i class="fas fa-building"></i> Tedarikçi Bazında Toplam Ödemeler</h2>
                <div class="search-container w-100 w-md-25">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" id="searchSupplier" placeholder="Tedarikçi ara...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Sıra</th>
                                <th><i class="fas fa-building"></i> Tedarikçi Adı</th>
                                <th><i class="fas fa-lira-sign"></i> Toplam Ödeme (TL)</th>
                                <th><i class="fas fa-receipt"></i> İşlem Sayısı</th>
                            </tr>
                        </thead>
                        <tbody id="supplierTotalsTable">
                            <tr>
                                <td colspan="4" class="text-center p-4">
                                    <i class="fas fa-spinner fa-spin"></i> Yükleniyor...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="page-info" id="supplierPageInfo">-</div>
                    <nav>
                        <ul class="pagination" id="supplierPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Detailed Payments Table -->
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="mb-2 mb-md-0"><i class="fas fa-list"></i> Detaylı Ödeme Listesi</h2>
                <div class="search-container w-100 w-md-25">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" id="searchPayment" placeholder="Ara...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-building"></i> Tedarikçi</th>
                                <th><i class="fas fa-box"></i> Malzeme</th>
                                <th><i class="fas fa-coins"></i> Para Birimi</th>
                                <th><i class="fas fa-tag"></i> Birim Fiyat</th>
                                <th><i class="fas fa-cubes"></i> Ödenen Miktar</th>
                                <th><i class="fas fa-money-bill"></i> Toplam (Orijinal)</th>
                                <th><i class="fas fa-lira-sign"></i> Toplam (TL)</th>
                            </tr>
                        </thead>
                        <tbody id="paymentsTable">
                            <tr>
                                <td colspan="7" class="text-center p-4">
                                    <i class="fas fa-spinner fa-spin"></i> Yükleniyor...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="page-info" id="paymentPageInfo">-</div>
                    <nav>
                        <ul class="pagination" id="paymentPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        let allPayments = [];
        let allSupplierTotals = [];
        let filteredSuppliers = [];
        let filteredPayments = [];
        let chart = null;
        
        // Pagination settings
        const ITEMS_PER_PAGE = 10;
        let currentSupplierPage = 1;
        let currentPaymentPage = 1;

        // Load data on page load
        $(document).ready(function() {
            loadData();
            
            // Search functionality for suppliers
            $('#searchSupplier').on('keyup', function() {
                currentSupplierPage = 1;
                filterSupplierTable($(this).val().toLowerCase());
            });
            
            // Search functionality for payments
            $('#searchPayment').on('keyup', function() {
                currentPaymentPage = 1;
                filterPaymentTable($(this).val().toLowerCase());
            });
        });

        function loadData() {
            $.ajax({
                url: 'api_islemleri/tedarikci_odeme_raporu_islemler.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        allPayments = response.data.payments;
                        allSupplierTotals = response.data.supplier_totals;
                        
                        updateStats(response.data);
                        updateExchangeRates(response.data.exchange_rates);
                        renderChart(response.data.supplier_totals);
                        renderSupplierTotalsTable(response.data.supplier_totals);
                        renderPaymentsTable(response.data.payments);
                    } else {
                        showError('Veri yüklenirken hata oluştu: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showError('Sunucu hatası: ' + error);
                }
            });
        }

        function updateStats(data) {
            $('#totalSuppliers').text(data.supplier_totals.length);
            
            let totalPayments = data.supplier_totals.reduce((sum, supplier) => sum + supplier.toplam_tl, 0);
            $('#totalPayments').text(totalPayments.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            let totalTransactions = data.payments.length;
            $('#totalTransactions').text(totalTransactions.toLocaleString('tr-TR'));
        }

        function updateExchangeRates(rates) {
            $('#usdRate').text(parseFloat(rates.usd).toFixed(4));
            $('#eurRate').text(parseFloat(rates.eur).toFixed(4));
        }

        function renderChart(supplierTotals) {
            // Get top 10 suppliers
            const top10 = supplierTotals.slice(0, 10);
            
            // Initialize chart
            const chartDom = document.getElementById('paymentsChart');
            chart = echarts.init(chartDom);
            
            // Define vibrant color palette
            const colorPalette = [
                ['#FF6B6B', '#EE5A6F'],  // Coral Red
                ['#4ECDC4', '#44A08D'],  // Turquoise
                ['#FFD93D', '#F6C90E'],  // Golden Yellow
                ['#A8E6CF', '#56C596'],  // Mint Green
                ['#FF8B94', '#FF6B9D'],  // Pink
                ['#C7CEEA', '#9FA8DA'],  // Lavender
                ['#FFDAC1', '#FFB88C'],  // Peach
                ['#B4F8C8', '#7FD8BE'],  // Light Green
                ['#FBE7C6', '#F4D06F'],  // Cream
                ['#A0E7E5', '#74D3D0']   // Aqua
            ];
            
            const option = {
                backgroundColor: 'transparent',
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow',
                        shadowStyle: {
                            color: 'rgba(74, 14, 99, 0.1)'
                        }
                    },
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#4a0e63',
                    borderWidth: 2,
                    textStyle: {
                        color: '#333',
                        fontSize: 14
                    },
                    formatter: function(params) {
                        const item = params[0];
                        return `<div style="padding: 5px;">
                            <strong style="font-size: 16px; color: #4a0e63;">${item.name}</strong><br/>
                            <div style="margin-top: 8px;">
                                <span style="color: #666;">Toplam Ödeme:</span> 
                                <strong style="color: #4a0e63; font-size: 15px;">${item.value.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ₺</strong>
                            </div>
                            <div style="margin-top: 4px; color: #888; font-size: 12px;">
                                İşlem Sayısı: ${top10[item.dataIndex].odeme_sayisi}
                            </div>
                        </div>`;
                    }
                },
                grid: {
                    left: '3%',
                    right: '8%',
                    bottom: '3%',
                    top: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    name: 'Toplam Ödeme (TL)',
                    nameTextStyle: {
                        fontSize: 13,
                        fontWeight: 'bold',
                        color: '#666'
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#ddd'
                        }
                    },
                    axisLabel: {
                        color: '#666',
                        fontSize: 12,
                        formatter: function(value) {
                            return value.toLocaleString('tr-TR');
                        }
                    },
                    splitLine: {
                        lineStyle: {
                            color: '#f0f0f0',
                            type: 'dashed'
                        }
                    }
                },
                yAxis: {
                    type: 'category',
                    data: top10.map(s => s.tedarikci_adi),
                    inverse: true,
                    axisLine: {
                        show: false
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        interval: 0,
                        color: '#333',
                        fontSize: 13,
                        fontWeight: 500,
                        formatter: function(value) {
                            return value.length > 25 ? value.substring(0, 25) + '...' : value;
                        }
                    }
                },
                series: [{
                    name: 'Ödeme',
                    type: 'bar',
                    data: top10.map((s, index) => ({
                        value: s.toplam_tl,
                        itemStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                                { offset: 0, color: colorPalette[index][0] },
                                { offset: 1, color: colorPalette[index][1] }
                            ]),
                            borderRadius: [0, 8, 8, 0],
                            shadowColor: 'rgba(0, 0, 0, 0.15)',
                            shadowBlur: 10,
                            shadowOffsetX: 3,
                            shadowOffsetY: 3
                        }
                    })),
                    barWidth: '70%',
                    label: {
                        show: true,
                        position: 'right',
                        distance: 10,
                        color: '#333',
                        fontSize: 13,
                        fontWeight: 'bold',
                        formatter: function(params) {
                            return params.value.toLocaleString('tr-TR', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + ' ₺';
                        }
                    },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 20,
                            shadowColor: 'rgba(0, 0, 0, 0.3)'
                        }
                    },
                    animationDuration: 1500,
                    animationEasing: 'elasticOut'
                }]
            };
            
            chart.setOption(option);
            
            // Make chart responsive
            window.addEventListener('resize', function() {
                chart.resize();
            });
        }

        function renderSupplierTotalsTable(supplierTotals) {
            const tbody = $('#supplierTotalsTable');
            tbody.empty();
            
            if (supplierTotals.length === 0) {
                tbody.html('<tr><td colspan="4" class="text-center p-4">Henüz ödeme verisi bulunmuyor.</td></tr>');
                $('#supplierPageInfo').text('-');
                $('#supplierPagination').empty();
                return;
            }
            
            // Calculate pagination
            const totalPages = Math.ceil(supplierTotals.length / ITEMS_PER_PAGE);
            const startIndex = (currentSupplierPage - 1) * ITEMS_PER_PAGE;
            const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, supplierTotals.length);
            const pageData = supplierTotals.slice(startIndex, endIndex);
            
            // Render rows
            pageData.forEach((supplier, index) => {
                const actualIndex = startIndex + index + 1;
                const row = `
                    <tr>
                        <td><strong>${actualIndex}</strong></td>
                        <td><strong>${supplier.tedarikci_adi}</strong></td>
                        <td><strong>${supplier.toplam_tl.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ₺</strong></td>
                        <td>${supplier.odeme_sayisi}</td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            // Update page info
            $('#supplierPageInfo').text(`${startIndex + 1}-${endIndex} / ${supplierTotals.length} kayıt`);
            
            // Render pagination
            renderPagination('supplier', totalPages, currentSupplierPage);
        }

        function renderPaymentsTable(payments) {
            const tbody = $('#paymentsTable');
            tbody.empty();
            
            if (payments.length === 0) {
                tbody.html('<tr><td colspan="7" class="text-center p-4">Henüz ödeme verisi bulunmuyor.</td></tr>');
                $('#paymentPageInfo').text('-');
                $('#paymentPagination').empty();
                return;
            }
            
            // Calculate pagination
            const totalPages = Math.ceil(payments.length / ITEMS_PER_PAGE);
            const startIndex = (currentPaymentPage - 1) * ITEMS_PER_PAGE;
            const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, payments.length);
            const pageData = payments.slice(startIndex, endIndex);
            
            // Render rows
            pageData.forEach((payment) => {
                let currencyBadge = '';
                if (payment.para_birimi === 'TL') {
                    currencyBadge = '<span class="currency-badge badge-tl">₺ TRY</span>';
                } else if (payment.para_birimi === 'USD') {
                    currencyBadge = '<span class="currency-badge badge-usd">$ USD</span>';
                } else if (payment.para_birimi === 'EUR') {
                    currencyBadge = '<span class="currency-badge badge-eur">€ EUR</span>';
                }
                
                const row = `
                    <tr>
                        <td>${payment.tedarikci_adi}</td>
                        <td>${payment.malzeme_kodu} - ${payment.malzeme_ismi || '-'}</td>
                        <td>${currencyBadge}</td>
                        <td>${payment.birim_fiyat.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>${payment.odenen_miktar.toLocaleString('tr-TR')}</td>
                        <td><strong>${payment.toplam_odeme_orijinal.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                        <td><strong>${payment.toplam_odeme_tl.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ₺</strong></td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            // Update page info
            $('#paymentPageInfo').text(`${startIndex + 1}-${endIndex} / ${payments.length} kayıt`);
            
            // Render pagination
            renderPagination('payment', totalPages, currentPaymentPage);
        }
        
        function renderPagination(type, totalPages, currentPage) {
            const paginationId = type === 'supplier' ? '#supplierPagination' : '#paymentPagination';
            const pagination = $(paginationId);
            pagination.empty();
            
            if (totalPages <= 1) return;
            
            // Previous button
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            pagination.append(`
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}" data-type="${type}">Önceki</a>
                </li>
            `);
            
            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }
            
            if (startPage > 1) {
                pagination.append(`
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="1" data-type="${type}">1</a>
                    </li>
                `);
                if (startPage > 2) {
                    pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const active = i === currentPage ? 'active' : '';
                pagination.append(`
                    <li class="page-item ${active}">
                        <a class="page-link" href="#" data-page="${i}" data-type="${type}">${i}</a>
                    </li>
                `);
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
                pagination.append(`
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${totalPages}" data-type="${type}">${totalPages}</a>
                    </li>
                `);
            }
            
            // Next button
            const nextDisabled = currentPage === totalPages ? 'disabled' : '';
            pagination.append(`
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}" data-type="${type}">Sonraki</a>
                </li>
            `);
            
            // Attach click handlers
            pagination.find('a.page-link').on('click', function(e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'));
                const tableType = $(this).data('type');
                
                if (tableType === 'supplier') {
                    currentSupplierPage = page;
                    renderSupplierTotalsTable(filteredSuppliers.length > 0 ? filteredSuppliers : allSupplierTotals);
                } else {
                    currentPaymentPage = page;
                    renderPaymentsTable(filteredPayments.length > 0 ? filteredPayments : allPayments);
                }
            });
        }

        function filterSupplierTable(searchTerm) {
            filteredSuppliers = allSupplierTotals.filter(supplier => 
                supplier.tedarikci_adi.toLowerCase().includes(searchTerm)
            );
            renderSupplierTotalsTable(filteredSuppliers.length > 0 || searchTerm ? filteredSuppliers : allSupplierTotals);
        }

        function filterPaymentTable(searchTerm) {
            filteredPayments = allPayments.filter(payment => 
                payment.tedarikci_adi.toLowerCase().includes(searchTerm) ||
                payment.malzeme_kodu.toLowerCase().includes(searchTerm) ||
                (payment.malzeme_ismi && payment.malzeme_ismi.toLowerCase().includes(searchTerm))
            );
            renderPaymentsTable(filteredPayments.length > 0 || searchTerm ? filteredPayments : allPayments);
        }

        function showError(message) {
            $('#supplierTotalsTable').html(`
                <tr>
                    <td colspan="4" class="text-center p-4 text-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${message}
                    </td>
                </tr>
            `);
            $('#paymentsTable').html(`
                <tr>
                    <td colspan="7" class="text-center p-4 text-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${message}
                    </td>
                </tr>
            `);
        }
    </script>
</body>
</html>

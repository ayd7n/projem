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
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <!-- Apache ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --bg-color: #fdf8f5;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .main-content {
            padding: 1.5rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.5rem;
            margin: 0;
        }

        .exchange-rates {
            font-size: 0.85rem;
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            align-items: center;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card .icon-wrapper {
            background-color: rgba(255, 255, 255, 0.2);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .stat-card h3 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 700;
        }

        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.85rem;
        }

        .card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            background-color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: var(--primary);
        }

        .chart-container {
            width: 100%;
            height: 350px;
        }

        .table th {
            border-top: none;
            border-bottom: 2px solid var(--border-color);
            font-weight: 700;
            color: var(--text-primary);
            font-size: 0.85rem;
            padding: 0.75rem;
        }

        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }

        .badge-currency {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .badge-tl {
            background-color: #e3f2fd;
            color: #0d47a1;
        }

        .badge-usd {
            background-color: #e8f5e9;
            color: #1b5e20;
        }

        .badge-eur {
            background-color: #fff3e0;
            color: #e65100;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.85rem;
            color: var(--primary);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
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
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-content">
        <div class="page-header">
            <h1><i class="fas fa-file-invoice-dollar"></i> Tedarikçi Ödeme Raporu</h1>
            <div class="exchange-rates">
                <i class="fas fa-exchange-alt text-muted mr-2"></i>
                <strong>USD:</strong> <span id="usdRate">-</span> ₺ &nbsp;|&nbsp;
                <strong>EUR:</strong> <span id="eurRate">-</span> ₺
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <h3 id="totalSuppliers">-</h3>
                        <p>Toplam Tedarikçi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <div class="icon-wrapper">
                        <i class="fas fa-lira-sign"></i>
                    </div>
                    <div>
                        <h3 id="totalPayments">-</h3>
                        <p>Toplam Ödeme (TL)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8, #117a8b);">
                    <div class="icon-wrapper">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <h3 id="totalTransactions">-</h3>
                        <p>Toplam İşlem</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- Chart Section -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-pie"></i> Ödeme Dağılımı (İlk 10 Tedarikçi)</h2>
                    </div>
                    <div class="card-body">
                        <div id="paymentsChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <!-- Transaction Count Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><i class="fas fa-receipt"></i> En Çok İşlem Yapılan Tedarikçiler</h2>
                    </div>
                    <div class="card-body">
                        <div id="transactionCountChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><i class="fas fa-coins"></i> Para Birimi Bazlı Ödeme Dağılımı</h2>
                    </div>
                    <div class="card-body">
                        <div id="currencyChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><i class="fas fa-boxes"></i> En Çok Harcama Yapılan Malzemeler (İlk 10)</h2>
                    </div>
                    <div class="card-body">
                        <div id="materialChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Payments Table -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-table"></i> Detaylı Ödeme Listesi</h2>
                <input type="text" id="searchPayment" class="form-control form-control-sm w-25"
                    placeholder="Detaylı Ara...">
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Tedarikçi</th>
                                <th>Malzeme</th>
                                <th>Para Birimi</th>
                                <th>Birim Fiyat</th>
                                <th>Miktar</th>
                                <th>Toplam (Orijinal)</th>
                                <th>Toplam (TL)</th>
                            </tr>
                        </thead>
                        <tbody id="paymentsTable">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div id="paymentPageInfo" class="text-muted"></div>
                    <nav>
                        <ul class="pagination justify-content-end mb-0" id="paymentPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        let allPayments = [];
        let allSupplierTotals = [];
        let filteredPayments = [];
        const ITEMS_PER_PAGE = 10;
        let currentPaymentPage = 1;

        $(document).ready(function () {
            loadData();

            $('#searchPayment').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                filteredPayments = allPayments.filter(item =>
                    item.tedarikci_adi.toLowerCase().includes(value) ||
                    (item.malzeme_ismi && item.malzeme_ismi.toLowerCase().includes(value))
                );
                currentPaymentPage = 1;
                renderPaymentsTable();
            });
        });

        function loadData() {
            $.ajax({
                url: 'api_islemleri/tedarikci_odeme_raporu_islemler.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        allPayments = response.data.payments;
                        allSupplierTotals = response.data.supplier_totals;
                        filteredPayments = [...allPayments];

                        updateStats(response.data);
                        updateExchangeRates(response.data.exchange_rates);
                        renderChart(response.data.supplier_totals);
                        renderTransactionCountChart(response.data.supplier_totals);
                        renderCurrencyChart(response.data.payments);
                        renderMaterialChart(response.data.payments);
                        renderPaymentsTable();
                    } else {
                        alert('Hata: ' + response.message);
                    }
                },
                error: function () {
                    alert('Sunucu hatası.');
                }
            });
        }

        // ... (updateStats, updateExchangeRates, renderChart remain same) ...

        function renderTransactionCountChart(supplierTotals) {
            // Sort by transaction count
            const top10 = [...supplierTotals].sort((a, b) => b.odeme_sayisi - a.odeme_sayisi).slice(0, 10);

            const chartDom = document.getElementById('transactionCountChart');
            const chart = echarts.init(chartDom);

            const option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' },
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: { color: '#333' }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    axisLabel: { formatter: '{value}' },
                    splitLine: { lineStyle: { type: 'dashed', color: '#eee' } }
                },
                yAxis: {
                    type: 'category',
                    data: top10.map(s => s.tedarikci_adi),
                    inverse: true,
                    axisLabel: {
                        color: '#333',
                        fontWeight: 'bold',
                        formatter: function (value) {
                            return value.length > 15 ? value.substring(0, 15) + '...' : value;
                        }
                    },
                    axisLine: { show: false },
                    axisTick: { show: false }
                },
                series: [{
                    name: 'İşlem Sayısı',
                    type: 'bar',
                    data: top10.map(s => s.odeme_sayisi),
                    barWidth: '60%',
                    itemStyle: {
                        borderRadius: [0, 20, 20, 0],
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: '#ff9966' },
                            { offset: 1, color: '#ff5e62' }
                        ]),
                        shadowColor: 'rgba(0, 0, 0, 0.2)',
                        shadowBlur: 10
                    },
                    label: {
                        show: true,
                        position: 'right',
                        color: '#666',
                        fontWeight: 'bold'
                    }
                }]
            };
            chart.setOption(option);
            window.addEventListener('resize', () => chart.resize());
        }

        // ... (existing functions) ...

        function renderCurrencyChart(payments) {
            let currencyTotals = { 'TL': 0, 'USD': 0, 'EUR': 0 };
            payments.forEach(p => {
                if (currencyTotals.hasOwnProperty(p.para_birimi)) {
                    currencyTotals[p.para_birimi] += p.toplam_odeme_tl;
                }
            });

            let chartData = Object.keys(currencyTotals)
                .filter(key => currencyTotals[key] > 0)
                .map(key => ({ value: currencyTotals[key], name: key }));

            const chartDom = document.getElementById('currencyChart');
            const chart = echarts.init(chartDom);

            const option = {
                tooltip: {
                    trigger: 'item',
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: { color: '#333' },
                    formatter: function (params) {
                        return `<strong>${params.name}</strong><br/>
                                Tutar: ${params.value.toLocaleString('tr-TR', { minimumFractionDigits: 2 })} ₺<br/>
                                Oran: ${params.percent}%`;
                    }
                },
                legend: {
                    top: '5%',
                    left: 'center'
                },
                series: [
                    {
                        name: 'Para Birimi',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        itemStyle: {
                            borderRadius: 10,
                            borderColor: '#fff',
                            borderWidth: 2
                        },
                        label: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: 20,
                                fontWeight: 'bold'
                            }
                        },
                        labelLine: {
                            show: false
                        },
                        data: chartData,
                        color: ['#17a2b8', '#28a745', '#ffc107'] // TL (Blue), USD (Green), EUR (Yellow)
                    }
                ]
            };
            chart.setOption(option);
            window.addEventListener('resize', () => chart.resize());
        }

        function renderMaterialChart(payments) {
            let materialTotals = {};
            payments.forEach(p => {
                let name = p.malzeme_ismi || 'Bilinmeyen Malzeme';
                if (!materialTotals[name]) materialTotals[name] = 0;
                materialTotals[name] += p.toplam_odeme_tl;
            });

            let sortedMaterials = Object.keys(materialTotals)
                .map(key => ({ name: key, value: materialTotals[key] }))
                .sort((a, b) => b.value - a.value)
                .slice(0, 10);

            const chartDom = document.getElementById('materialChart');
            const chart = echarts.init(chartDom);

            const option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' },
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: { color: '#333' },
                    formatter: function (params) {
                        let val = params[0].value;
                        return `<strong>${params[0].name}</strong><br/>Toplam: ${val.toLocaleString('tr-TR', { minimumFractionDigits: 2 })} ₺`;
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: function (value) {
                            if (value >= 1000) return (value / 1000).toFixed(0) + 'k ₺';
                            return value + ' ₺';
                        },
                        color: '#666'
                    },
                    splitLine: { lineStyle: { type: 'dashed', color: '#eee' } }
                },
                yAxis: {
                    type: 'category',
                    data: sortedMaterials.map(m => m.name),
                    inverse: true,
                    axisLabel: {
                        color: '#333',
                        fontWeight: 'bold',
                        formatter: function (value) {
                            return value.length > 15 ? value.substring(0, 15) + '...' : value;
                        }
                    },
                    axisLine: { show: false },
                    axisTick: { show: false }
                },
                series: [{
                    name: 'Harcama',
                    type: 'bar',
                    data: sortedMaterials.map(m => m.value),
                    barWidth: '60%',
                    itemStyle: {
                        borderRadius: [0, 20, 20, 0],
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: '#e83e8c' },
                            { offset: 1, color: '#6610f2' }
                        ]),
                        shadowColor: 'rgba(0, 0, 0, 0.2)',
                        shadowBlur: 10
                    },
                    label: {
                        show: true,
                        position: 'right',
                        formatter: function (params) {
                            return params.value.toLocaleString('tr-TR', { maximumFractionDigits: 0 }) + ' ₺';
                        },
                        color: '#666',
                        fontWeight: 'bold'
                    }
                }]
            };
            chart.setOption(option);
            window.addEventListener('resize', () => chart.resize());
        }

        function updateStats(data) {
            $('#totalSuppliers').text(data.supplier_totals.length);
            let totalPayments = data.supplier_totals.reduce((sum, s) => sum + s.toplam_tl, 0);
            $('#totalPayments').text(totalPayments.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺');
            $('#totalTransactions').text(data.payments.length);
        }

        function updateExchangeRates(rates) {
            $('#usdRate').text(parseFloat(rates.usd).toFixed(4));
            $('#eurRate').text(parseFloat(rates.eur).toFixed(4));
        }

        function renderChart(supplierTotals) {
            const top10 = supplierTotals.slice(0, 10);
            const chartDom = document.getElementById('paymentsChart');
            const chart = echarts.init(chartDom);

            const option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' },
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: { color: '#333' },
                    formatter: function (params) {
                        let val = params[0].value;
                        return `<strong>${params[0].name}</strong><br/>Toplam: ${val.toLocaleString('tr-TR', { minimumFractionDigits: 2 })} ₺`;
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: function (value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M ₺';
                            if (value >= 1000) return (value / 1000).toFixed(0) + 'k ₺';
                            return value + ' ₺';
                        },
                        color: '#666'
                    },
                    splitLine: { lineStyle: { type: 'dashed', color: '#eee' } }
                },
                yAxis: {
                    type: 'category',
                    data: top10.map(s => s.tedarikci_adi),
                    inverse: true,
                    axisLabel: {
                        color: '#333',
                        fontWeight: 'bold',
                        formatter: function (value) {
                            return value.length > 15 ? value.substring(0, 15) + '...' : value;
                        }
                    },
                    axisLine: { show: false },
                    axisTick: { show: false }
                },
                series: [{
                    name: 'Ödeme',
                    type: 'bar',
                    data: top10.map(s => s.toplam_tl),
                    barWidth: '60%',
                    itemStyle: {
                        borderRadius: [0, 20, 20, 0],
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: '#4a0e63' },
                            { offset: 1, color: '#7c2a99' }
                        ]),
                        shadowColor: 'rgba(0, 0, 0, 0.2)',
                        shadowBlur: 10
                    },
                    label: {
                        show: true,
                        position: 'right',
                        formatter: function (params) {
                            return params.value.toLocaleString('tr-TR', { maximumFractionDigits: 0 }) + ' ₺';
                        },
                        color: '#666',
                        fontWeight: 'bold'
                    }
                }]
            };
            chart.setOption(option);
            window.addEventListener('resize', () => chart.resize());
        }

        function renderSupplierTotalsTable(data) {
            let html = '';
            data.forEach(item => {
                html += `
                    <tr>
                        <td>${item.tedarikci_adi}</td>
                        <td class="text-right"><strong>${item.toplam_tl.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ₺</strong></td>
                        <td class="text-center"><span class="badge badge-light">${item.odeme_sayisi}</span></td>
                    </tr>
                `;
            });
            $('#supplierTotalsTable').html(html);
        }

        function renderPaymentsTable() {
            const totalPages = Math.ceil(filteredPayments.length / ITEMS_PER_PAGE);
            if (currentPaymentPage > totalPages) currentPaymentPage = totalPages || 1;
            if (currentPaymentPage < 1) currentPaymentPage = 1;

            const start = (currentPaymentPage - 1) * ITEMS_PER_PAGE;
            const end = start + ITEMS_PER_PAGE;
            const pageData = filteredPayments.slice(start, end);

            let html = '';
            if (pageData.length === 0) {
                html = '<tr><td colspan="7" class="text-center text-muted py-3">Kayıt bulunamadı.</td></tr>';
            } else {
                pageData.forEach(item => {
                    let badgeClass = 'badge-tl';
                    if (item.para_birimi === 'USD') badgeClass = 'badge-usd';
                    if (item.para_birimi === 'EUR') badgeClass = 'badge-eur';

                    html += `
                        <tr>
                            <td>${item.tedarikci_adi}</td>
                            <td><small>${item.malzeme_kodu} - ${item.malzeme_ismi || '-'}</small></td>
                            <td><span class="badge-currency ${badgeClass}">${item.para_birimi}</span></td>
                            <td>${item.birim_fiyat.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}</td>
                            <td>${item.odenen_miktar.toLocaleString('tr-TR')}</td>
                            <td>${item.toplam_odeme_orijinal.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}</td>
                            <td><strong>${item.toplam_odeme_tl.toLocaleString('tr-TR', { minimumFractionDigits: 2 })} ₺</strong></td>
                        </tr>
                    `;
                });
            }
            $('#paymentsTable').html(html);

            $('#paymentPageInfo').text(filteredPayments.length > 0 ? `${start + 1}-${Math.min(end, filteredPayments.length)} / ${filteredPayments.length}` : '');
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            let html = '';

            html += `<li class="page-item ${currentPaymentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPaymentPage - 1}); return false;">Önceki</a>
                     </li>`;

            let startPage = Math.max(1, currentPaymentPage - 2);
            let endPage = Math.min(totalPages, currentPaymentPage + 2);

            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1); return false;">1</a></li>`;
                if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === currentPaymentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                         </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a></li>`;
            }

            html += `<li class="page-item ${currentPaymentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPaymentPage + 1}); return false;">Sonraki</a>
                     </li>`;

            $('#paymentPagination').html(html);
        }

        function changePage(page) {
            currentPaymentPage = page;
            renderPaymentsTable();
        }
    </script>
</body>

</html>
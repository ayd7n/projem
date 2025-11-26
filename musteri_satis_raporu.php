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
    <title>Müşteri Satış Raporu - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
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
        }

        .main-content {
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-weight: 700;
            color: var(--primary);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 2rem;
            margin: 0;
            font-weight: 700;
        }

        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .stat-card .icon-wrapper {
            background-color: rgba(255, 255, 255, 0.2);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
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
            background-color: #fff;
        }

        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: var(--primary);
        }

        .chart-container {
            width: 100%;
            height: 400px;
        }

        .table th {
            border-top: none;
            border-bottom: 2px solid var(--border-color);
            font-weight: 700;
            color: var(--text-primary);
        }

        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
        }
        
        .badge-profit {
            background-color: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .badge-loss {
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
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

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Müşteri Satış Raporu</h1>
            <p class="text-muted">Müşteri bazlı satış, ürün dağılımı ve karlılık analizi.</p>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-users" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 id="totalCustomers">-</h3>
                            <p>Toplam Müşteri</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-coins" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 id="totalSales">- ₺</h3>
                            <p>Toplam Satış</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-crown" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 id="topCustomer" style="font-size: 1.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">-</h3>
                            <p>En İyi Müşteri</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8, #117a8b);">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-box-open" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 id="topProduct" style="font-size: 1.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">-</h3>
                            <p>En Çok Satan Ürün</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-bar"></i> Müşteri Bazlı Karlılık (İlk 10)</h2>
                    </div>
                    <div class="card-body">
                        <div id="profitChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-pie"></i> En Çok Gelir Getiren Ürünler (İlk 10)</h2>
                    </div>
                    <div class="card-body">
                        <div id="productRevenueChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><i class="fas fa-sort-amount-up"></i> En Çok Sipariş Veren Müşteriler (İlk 10)</h2>
                    </div>
                    <div class="card-body">
                        <div id="orderCountChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><i class="fas fa-boxes"></i> En Çok Satılan Ürünler (Adet Bazlı)</h2>
                    </div>
                    <div class="card-body">
                        <div id="productQuantityChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-list"></i> Müşteri Satış Özetleri</h2>
                <input type="text" id="searchCustomer" class="form-control w-25" placeholder="Müşteri Ara...">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="customerTable">
                        <thead>
                            <tr>
                                <th>Müşteri</th>
                                <th>Toplam Sipariş</th>
                                <th>Ürün Çeşidi</th>
                                <th>Toplam Adet</th>
                                <th>Toplam Satış</th>
                                <th>Toplam Kar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-file-invoice"></i> Detaylı Satış Listesi (Ürün Bazlı)</h2>
                <input type="text" id="searchDetail" class="form-control w-25" placeholder="Ürün veya Müşteri Ara...">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="detailTable">
                        <thead>
                            <tr>
                                <th>Müşteri</th>
                                <th>Ürün</th>
                                <th>Sipariş Sayısı</th>
                                <th>Satılan Adet</th>
                                <th>Toplam Satış</th>
                                <th>Birim Maliyet</th>
                                <th>Toplam Kar</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div id="paginationInfo" class="text-muted"></div>
                    <nav>
                        <ul class="pagination justify-content-end mb-0" id="paginationControls">
                            <!-- Pagination controls -->
                        </ul>
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
        let allDetailData = [];
        let filteredDetailData = [];
        let currentPage = 1;
        const itemsPerPage = 10;

        $(document).ready(function() {
            loadReportData();

            $('#searchCustomer').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $("#customerTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('#searchDetail').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                filteredDetailData = allDetailData.filter(item => 
                    item.musteri_adi.toLowerCase().includes(value) || 
                    item.urun_ismi.toLowerCase().includes(value) ||
                    item.urun_kodu.toString().includes(value)
                );
                currentPage = 1;
                renderDetailTable();
            });
        });

        function formatCurrency(value) {
            return parseFloat(value).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
        }

        function loadReportData() {
            $.ajax({
                url: 'api_islemleri/musteri_satis_raporu_islemler.php',
                type: 'GET',
                data: { action: 'get_report_data' },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        allDetailData = response.data;
                        filteredDetailData = [...allDetailData];
                        
                        updateStats(response);
                        renderCustomerTable(response.customer_totals);
                        renderDetailTable();
                        renderProfitChart(response.customer_totals);
                        renderProductRevenueChart(response.data);
                        renderOrderCountChart(response.customer_totals);
                        renderProductQuantityChart(response.data);
                    } else {
                        alert('Veri yüklenirken hata oluştu: ' + response.message);
                    }
                },
                error: function() {
                    alert('Sunucu hatası.');
                }
            });
        }

        // ... (existing functions) ...

        function renderOrderCountChart(data) {
            if (!data || data.length === 0) return;

            let sortedData = [...data].sort((a, b) => parseFloat(b.toplam_siparis_sayisi) - parseFloat(a.toplam_siparis_sayisi)).slice(0, 10);
            
            let chartDom = document.getElementById('orderCountChart');
            if (!chartDom) return;
            
            let myChart = echarts.init(chartDom);
            let option = {
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
                    splitLine: {
                        lineStyle: {
                            type: 'dashed',
                            color: '#eee'
                        }
                    }
                },
                yAxis: {
                    type: 'category',
                    data: sortedData.map(item => item.musteri_adi),
                    inverse: true,
                    axisLabel: {
                        color: '#333',
                        fontWeight: 'bold'
                    },
                    axisLine: { show: false },
                    axisTick: { show: false }
                },
                series: [
                    {
                        name: 'Sipariş Sayısı',
                        type: 'bar',
                        data: sortedData.map(item => parseFloat(item.toplam_siparis_sayisi)),
                        barWidth: '60%',
                        itemStyle: {
                            borderRadius: [0, 20, 20, 0],
                            color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                                { offset: 0, color: '#e67e22' },
                                { offset: 1, color: '#f1c40f' }
                            ]),
                            shadowColor: 'rgba(0, 0, 0, 0.2)',
                            shadowBlur: 10
                        },
                        label: {
                            show: true,
                            position: 'right',
                            color: '#666',
                            fontWeight: 'bold'
                        },
                        showBackground: true,
                        backgroundStyle: {
                            color: 'rgba(180, 180, 180, 0.1)',
                            borderRadius: [0, 20, 20, 0]
                        }
                    }
                ]
            };
            myChart.setOption(option);
            
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        }

        function renderProductQuantityChart(data) {
            if (!data || data.length === 0) return;

            // Aggregate quantity by product
            let productQty = {};
            data.forEach(item => {
                if (!productQty[item.urun_kodu]) {
                    productQty[item.urun_kodu] = { name: item.urun_ismi, total: 0 };
                }
                productQty[item.urun_kodu].total += parseFloat(item.toplam_adet);
            });

            let sortedProducts = Object.values(productQty).sort((a, b) => b.total - a.total).slice(0, 10);

            let chartDom = document.getElementById('productQuantityChart');
            if (!chartDom) return;

            let myChart = echarts.init(chartDom);
            let option = {
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
                    splitLine: {
                        lineStyle: {
                            type: 'dashed',
                            color: '#eee'
                        }
                    }
                },
                yAxis: {
                    type: 'category',
                    data: sortedProducts.map(item => item.name),
                    inverse: true,
                    axisLabel: {
                        color: '#333',
                        fontWeight: 'bold',
                        formatter: function(value) {
                            return value.length > 15 ? value.substring(0, 15) + '...' : value;
                        }
                    },
                    axisLine: { show: false },
                    axisTick: { show: false }
                },
                series: [
                    {
                        name: 'Satılan Adet',
                        type: 'bar',
                        data: sortedProducts.map(item => item.total),
                        barWidth: '60%',
                        itemStyle: {
                            borderRadius: [0, 20, 20, 0],
                            color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                                { offset: 0, color: '#16a085' },
                                { offset: 1, color: '#2ecc71' }
                            ]),
                            shadowColor: 'rgba(0, 0, 0, 0.2)',
                            shadowBlur: 10
                        },
                        label: {
                            show: true,
                            position: 'right',
                            color: '#666',
                            fontWeight: 'bold'
                        },
                        showBackground: true,
                        backgroundStyle: {
                            color: 'rgba(180, 180, 180, 0.1)',
                            borderRadius: [0, 20, 20, 0]
                        }
                    }
                ]
            };
            myChart.setOption(option);
            
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        }

        function updateStats(data) {
            $('#totalCustomers').text(data.customer_totals.length);
            
            let totalSales = data.customer_totals.reduce((sum, item) => sum + item.toplam_satis, 0);
            $('#totalSales').text(formatCurrency(totalSales));

            // Find top customer
            let topCustomer = data.customer_totals.reduce((prev, current) => (prev.toplam_satis > current.toplam_satis) ? prev : current, {musteri_adi: '-', toplam_satis: 0});
            $('#topCustomer').text(topCustomer.musteri_adi);
            $('#topCustomer').attr('title', topCustomer.musteri_adi + ' (' + formatCurrency(topCustomer.toplam_satis) + ')');

            // Find top product
            // Need to aggregate by product first since data is per customer-product
            let productTotals = {};
            data.data.forEach(item => {
                if (!productTotals[item.urun_kodu]) {
                    productTotals[item.urun_kodu] = { name: item.urun_ismi, total_sales: 0 };
                }
                productTotals[item.urun_kodu].total_sales += item.toplam_satis;
            });
            
            let topProduct = Object.values(productTotals).reduce((prev, current) => (prev.total_sales > current.total_sales) ? prev : current, {name: '-', total_sales: 0});
            $('#topProduct').text(topProduct.name);
            $('#topProduct').attr('title', topProduct.name + ' (' + formatCurrency(topProduct.total_sales) + ')');
        }

        function renderCustomerTable(data) {
            let html = '';
            data.forEach(item => {
                html += `
                    <tr>
                        <td><strong>${item.musteri_adi}</strong></td>
                        <td>${item.toplam_siparis_sayisi}</td>
                        <td>${item.urun_cesidi}</td>
                        <td>${item.toplam_urun_adedi}</td>
                        <td>${formatCurrency(item.toplam_satis)}</td>
                        <td><span class="${item.toplam_kar >= 0 ? 'badge-profit' : 'badge-loss'}">${formatCurrency(item.toplam_kar)}</span></td>
                    </tr>
                `;
            });
            $('#customerTable tbody').html(html);
        }

        function renderDetailTable() {
            let totalPages = Math.ceil(filteredDetailData.length / itemsPerPage);
            
            // Adjust current page if out of bounds
            if (currentPage > totalPages) currentPage = totalPages || 1;
            if (currentPage < 1) currentPage = 1;

            let start = (currentPage - 1) * itemsPerPage;
            let end = start + itemsPerPage;
            let pageData = filteredDetailData.slice(start, end);

            let html = '';
            if (pageData.length === 0) {
                html = '<tr><td colspan="7" class="text-center">Kayıt bulunamadı.</td></tr>';
            } else {
                pageData.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.musteri_adi}</td>
                            <td>${item.urun_kodu} - ${item.urun_ismi}</td>
                            <td>${item.siparis_sayisi}</td>
                            <td>${item.toplam_adet}</td>
                            <td>${formatCurrency(item.toplam_satis)}</td>
                            <td>${formatCurrency(item.birim_maliyet)}</td>
                            <td><span class="${item.kar >= 0 ? 'badge-profit' : 'badge-loss'}">${formatCurrency(item.kar)}</span></td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);

            // Update pagination info
            if (filteredDetailData.length > 0) {
                $('#paginationInfo').text(`Toplam ${filteredDetailData.length} kayıttan ${start + 1}-${Math.min(end, filteredDetailData.length)} arası gösteriliyor.`);
            } else {
                $('#paginationInfo').text('');
            }

            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            let html = '';
            
            // Previous
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Önceki</a>
                     </li>`;

            // Pages
            // Show limited pages logic can be added here, for now showing simple range
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                 html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1); return false;">1</a></li>`;
                 if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                         </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a></li>`;
            }

            // Next
            html += `<li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Sonraki</a>
                     </li>`;

            $('#paginationControls').html(html);
        }

        function changePage(page) {
            currentPage = page;
            renderDetailTable();
        }

        function renderProfitChart(data) {
            let sortedData = [...data].sort((a, b) => b.toplam_kar - a.toplam_kar).slice(0, 10);
            
            let chartDom = document.getElementById('profitChart');
            let myChart = echarts.init(chartDom);
            let option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' },
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: { color: '#333' },
                    formatter: function(params) {
                        let val = params[0].value;
                        return `<strong>${params[0].name}</strong><br/>Kar: ${val.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺`;
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
                        formatter: function(value) {
                            if (value >= 1000) return (value / 1000).toFixed(1) + 'k ₺';
                            return value + ' ₺';
                        },
                        color: '#666'
                    },
                    splitLine: {
                        lineStyle: {
                            type: 'dashed',
                            color: '#eee'
                        }
                    }
                },
                yAxis: {
                    type: 'category',
                    data: sortedData.map(item => item.musteri_adi),
                    inverse: true,
                    axisLabel: {
                        color: '#333',
                        fontWeight: 'bold'
                    },
                    axisLine: { show: false },
                    axisTick: { show: false }
                },
                series: [
                    {
                        name: 'Kar',
                        type: 'bar',
                        data: sortedData.map(item => item.toplam_kar),
                        barWidth: '60%',
                        itemStyle: {
                            borderRadius: [0, 20, 20, 0],
                            color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                                { offset: 0, color: '#8e44ad' },
                                { offset: 1, color: '#3498db' }
                            ]),
                            shadowColor: 'rgba(0, 0, 0, 0.2)',
                            shadowBlur: 10
                        },
                        label: {
                            show: true,
                            position: 'right',
                            formatter: function(params) {
                                return params.value.toLocaleString('tr-TR', {maximumFractionDigits: 0}) + ' ₺';
                            },
                            color: '#666',
                            fontWeight: 'bold'
                        },
                        showBackground: true,
                        backgroundStyle: {
                            color: 'rgba(180, 180, 180, 0.1)',
                            borderRadius: [0, 20, 20, 0]
                        }
                    }
                ]
            };
            myChart.setOption(option);
            
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        }

        function renderProductRevenueChart(data) {
            // Aggregate sales by product
            let productSales = {};
            data.forEach(item => {
                if (!productSales[item.urun_kodu]) {
                    productSales[item.urun_kodu] = { name: item.urun_ismi, total: 0 };
                }
                productSales[item.urun_kodu].total += item.toplam_satis;
            });

            let sortedProducts = Object.values(productSales).sort((a, b) => b.total - a.total).slice(0, 10);

            let chartDom = document.getElementById('productRevenueChart');
            let myChart = echarts.init(chartDom);
            let option = {
                color: [
                    '#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', 
                    '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc', '#2f4554'
                ],
                tooltip: {
                    trigger: 'item',
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: { color: '#333' },
                    formatter: function(params) {
                        return `<strong>${params.name}</strong><br/>
                                Gelir: ${params.value.toLocaleString('tr-TR', {minimumFractionDigits: 2})} ₺<br/>
                                Oran: ${params.percent}%`;
                    }
                },
                legend: {
                    top: '5%',
                    left: 'center',
                    type: 'scroll'
                },
                series: [
                    {
                        name: 'Gelir',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        center: ['50%', '60%'],
                        avoidLabelOverlap: true,
                        itemStyle: {
                            borderRadius: 10,
                            borderColor: '#fff',
                            borderWidth: 2,
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.2)'
                        },
                        label: {
                            show: true,
                            position: 'outside',
                            formatter: '{b}\n{d}%',
                            color: '#333',
                            fontWeight: '500'
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: 16,
                                fontWeight: 'bold'
                            },
                            itemStyle: {
                                shadowBlur: 20,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        },
                        labelLine: {
                            show: true,
                            length: 15,
                            length2: 10,
                            smooth: true
                        },
                        data: sortedProducts.map(item => ({
                            value: item.total,
                            name: item.name
                        }))
                    }
                ]
            };
            myChart.setOption(option);
            
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        }
    </script>
</body>
</html>

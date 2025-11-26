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
    <title>En Çok Satan Ürünler - Parfüm ERP</title>
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
        
        @media (max-width: 768px) {
            .chart-container {
                height: 400px;
            }
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
        
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .rank-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: white;
        }
        
        .rank-2 {
            background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
            color: white;
        }
        
        .rank-3 {
            background: linear-gradient(135deg, #CD7F32, #B8860B);
            color: white;
        }
        
        .rank-other {
            background: var(--bg-color);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
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
                <h1><i class="fas fa-trophy"></i> En Çok Satan Ürünler</h1>
                <p>Tamamlanmış siparişlere göre en çok satan ürünleri analiz edin</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-box-open" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                        <div>
                            <h3 id="totalProducts">-</h3>
                            <p>Toplam Ürün Çeşidi</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-chart-line" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                        <div>
                            <h3 id="totalSales">-</h3>
                            <p>Toplam Satış Miktarı</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-shopping-cart" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                        <div>
                            <h3 id="totalOrders">-</h3>
                            <p>Toplam Sipariş Sayısı</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-chart-bar"></i> En Çok Satan İlk 10 Ürün</h2>
            </div>
            <div class="card-body">
                <div id="salesChart" class="chart-container"></div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="mb-2 mb-md-0"><i class="fas fa-list"></i> Tüm Ürünler</h2>
                <div class="search-container w-100 w-md-25">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" id="searchInput" placeholder="Ürün ara...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-medal"></i> Sıra</th>
                                <th><i class="fas fa-barcode"></i> Ürün Kodu</th>
                                <th><i class="fas fa-box"></i> Ürün Adı</th>
                                <th><i class="fas fa-balance-scale"></i> Birim</th>
                                <th><i class="fas fa-chart-line"></i> Toplam Satış</th>
                                <th><i class="fas fa-file-invoice"></i> Sipariş Sayısı</th>
                            </tr>
                        </thead>
                        <tbody id="productsTable">
                            <tr>
                                <td colspan="6" class="text-center p-4">
                                    <i class="fas fa-spinner fa-spin"></i> Yükleniyor...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        let allProducts = [];
        let chart = null;

        // Load data on page load
        $(document).ready(function() {
            loadData();
            
            // Search functionality
            $('#searchInput').on('keyup', function() {
                filterTable($(this).val().toLowerCase());
            });
        });

        function loadData() {
            $.ajax({
                url: 'api_islemleri/en_cok_satan_urunler.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        allProducts = response.data;
                        updateStats(response.data);
                        renderChart(response.data);
                        renderTable(response.data);
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
            $('#totalProducts').text(data.length);
            
            let totalSales = data.reduce((sum, product) => sum + product.toplam_satis, 0);
            $('#totalSales').text(totalSales.toLocaleString('tr-TR'));
            
            let totalOrders = data.reduce((sum, product) => sum + product.siparis_sayisi, 0);
            $('#totalOrders').text(totalOrders.toLocaleString('tr-TR'));
        }

        function renderChart(data) {
            // Get top 10 products
            const top10 = data.slice(0, 10);
            
            // Initialize chart
            const chartDom = document.getElementById('salesChart');
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
                        const rank = item.dataIndex + 1;
                        let medal = '';
                        if (rank === 1) medal = '🏆';
                        else if (rank === 2) medal = '🥈';
                        else if (rank === 3) medal = '🥉';
                        
                        return `<div style="padding: 5px;">
                            <strong style="font-size: 16px; color: #4a0e63;">${medal} ${item.name}</strong><br/>
                            <div style="margin-top: 8px;">
                                <span style="color: #666;">Toplam Satış:</span> 
                                <strong style="color: #4a0e63; font-size: 15px;">${item.value.toLocaleString('tr-TR')}</strong> 
                                <span style="color: #888;">${top10[item.dataIndex].birim}</span>
                            </div>
                            <div style="margin-top: 4px; color: #888; font-size: 12px;">
                                Sipariş Sayısı: ${top10[item.dataIndex].siparis_sayisi}
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
                    name: 'Satış Miktarı',
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
                    data: top10.map(p => p.urun_ismi),
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
                            return value.length > 35 ? value.substring(0, 35) + '...' : value;
                        },
                        rich: {
                            name: {
                                fontWeight: 'bold'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Satış',
                    type: 'bar',
                    data: top10.map((p, index) => ({
                        value: p.toplam_satis,
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
                            return params.value.toLocaleString('tr-TR');
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

        function renderTable(data) {
            const tbody = $('#productsTable');
            tbody.empty();
            
            if (data.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center p-4">Henüz satış verisi bulunmuyor.</td></tr>');
                return;
            }
            
            data.forEach((product, index) => {
                const rank = index + 1;
                let rankBadge = '';
                
                if (rank === 1) {
                    rankBadge = `<span class="rank-badge rank-1"><i class="fas fa-trophy"></i></span>`;
                } else if (rank === 2) {
                    rankBadge = `<span class="rank-badge rank-2"><i class="fas fa-medal"></i></span>`;
                } else if (rank === 3) {
                    rankBadge = `<span class="rank-badge rank-3"><i class="fas fa-award"></i></span>`;
                } else {
                    rankBadge = `<span class="rank-badge rank-other">${rank}</span>`;
                }
                
                const row = `
                    <tr>
                        <td>${rankBadge}</td>
                        <td><strong>${product.urun_kodu}</strong></td>
                        <td>${product.urun_ismi}</td>
                        <td>${product.birim}</td>
                        <td><strong>${product.toplam_satis.toLocaleString('tr-TR')}</strong></td>
                        <td>${product.siparis_sayisi.toLocaleString('tr-TR')}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        function filterTable(searchTerm) {
            const filtered = allProducts.filter(product => 
                product.urun_ismi.toLowerCase().includes(searchTerm) ||
                product.urun_kodu.toLowerCase().includes(searchTerm)
            );
            renderTable(filtered);
        }

        function showError(message) {
            $('#productsTable').html(`
                <tr>
                    <td colspan="6" class="text-center p-4 text-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${message}
                    </td>
                </tr>
            `);
        }
    </script>
</body>
</html>

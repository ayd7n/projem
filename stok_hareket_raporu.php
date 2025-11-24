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
if (!yetkisi_var('page:view:stok_hareket_raporu')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Fetch data for charts
function getChartData() {
    global $connection;

    $data = [];

    // Chart 1: Movement Types Distribution
    $stmt = $connection->prepare("
        SELECT hareket_turu, COUNT(*) as count
        FROM stok_hareket_kayitlari
        GROUP BY hareket_turu
    ");
    $stmt->execute();
    $data['movement_types'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Chart 2: Stock Types Distribution
    $stmt = $connection->prepare("
        SELECT stok_turu, COUNT(*) as count
        FROM stok_hareket_kayitlari
        GROUP BY stok_turu
    ");
    $stmt->execute();
    $data['stock_types'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Chart 3: Direction (Giriş/Çıkış) Distribution
    $stmt = $connection->prepare("
        SELECT yon, COUNT(*) as count
        FROM stok_hareket_kayitlari
        GROUP BY yon
    ");
    $stmt->execute();
    $data['directions'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Chart 4: Monthly Movement Trends
    $stmt = $connection->prepare("
        SELECT DATE_FORMAT(tarih, '%Y-%m') as month, COUNT(*) as count
        FROM stok_hareket_kayitlari
        GROUP BY DATE_FORMAT(tarih, '%Y-%m')
        ORDER BY DATE_FORMAT(tarih, '%Y-%m')
    ");
    $stmt->execute();
    $data['monthly_trends'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Chart 5: Stock Movement Volume by Type
    $stmt = $connection->prepare("
        SELECT stok_turu, SUM(miktar) as total_volume
        FROM stok_hareket_kayitlari
        GROUP BY stok_turu
    ");
    $stmt->execute();
    $data['volume_by_type'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Chart 6: Top 10 Products by Movement Count
    $stmt = $connection->prepare("
        SELECT isim, COUNT(*) as count
        FROM stok_hareket_kayitlari
        GROUP BY isim
        ORDER BY COUNT(*) DESC
        LIMIT 10
    ");
    $stmt->execute();
    $data['top_products'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return $data;
}

$chartData = getChartData();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Hareket Raporu - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
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
        .navbar {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            box-shadow: var(--shadow);
        }
        .navbar-brand {
            color: var(--accent, #d4af37) !important;
            font-weight: 700;
        }
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85);
            transition: color 0.3s ease;
        }
        .navbar-nav .nav-link:hover {
            color: white;
        }
        .dropdown-menu {
            border-radius: 0.5rem;
            border: none;
            box-shadow: var(--shadow);
        }
        .dropdown-item {
            color: var(--text-primary);
        }
        .dropdown-item:hover {
            background-color: var(--bg-color);
            color: var(--primary);
        }
        .chart-container {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 400px;
        }
        .chart-title {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        .container-fluid {
            padding-left: 0;
            padding-right: 0;
        }
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        .col-md-6 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="navigation.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION["kullanici_adi"] ?? "Kullanıcı"); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content container-fluid">
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Stok Hareket Raporu</h1>
            <p class="text-muted">Stok hareketlerinizi grafiklerle analiz edin ve takip edin.</p>
        </div>


        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Hareket Türü Dağılımı</div>
                    <div id="movementTypeChart" style="width:100%; height:100%;"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Stok Türü Dağılımı</div>
                    <div id="stockTypeChart" style="width:100%; height:100%;"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Hareket Yönü Dağılımı</div>
                    <div id="directionChart" style="width:100%; height:100%;"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Aylık Hareket Trendleri</div>
                    <div id="monthlyTrendChart" style="width:100%; height:100%;"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row 3 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Stok Türüne Göre Hacim</div>
                    <div id="volumeChart" style="width:100%; height:100%;"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">En Çok Hareket Eden 10 Ürün</div>
                    <div id="topProductsChart" style="width:100%; height:100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Initialize charts after page load
        document.addEventListener('DOMContentLoaded', function() {
            // Chart 1: Movement Type Distribution (Pie Chart)
            var movementTypeChart = echarts.init(document.getElementById('movementTypeChart'));
            var movementTypeOption = {
                title: {
                    text: 'Hareket Türü Dağılımı',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                series: [{
                    name: 'Hareket Türü',
                    type: 'pie',
                    radius: '50%',
                    data: [
                        <?php foreach($chartData['movement_types'] as $item): ?>
                        { value: <?php echo $item['count']; ?>, name: '<?php echo $item['hareket_turu']; ?>' },
                        <?php endforeach; ?>
                    ],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            };
            movementTypeChart.setOption(movementTypeOption);

            // Chart 2: Stock Type Distribution (Pie Chart)
            var stockTypeChart = echarts.init(document.getElementById('stockTypeChart'));
            var stockTypeOption = {
                title: {
                    text: 'Stok Türü Dağılımı',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                series: [{
                    name: 'Stok Türü',
                    type: 'pie',
                    radius: '50%',
                    data: [
                        <?php foreach($chartData['stock_types'] as $item): ?>
                        { value: <?php echo $item['count']; ?>, name: '<?php echo $item['stok_turu']; ?>' },
                        <?php endforeach; ?>
                    ],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            };
            stockTypeChart.setOption(stockTypeOption);

            // Chart 3: Direction Distribution (Pie Chart)
            var directionChart = echarts.init(document.getElementById('directionChart'));
            var directionOption = {
                title: {
                    text: 'Hareket Yönü Dağılımı',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                series: [{
                    name: 'Yön',
                    type: 'pie',
                    radius: '50%',
                    data: [
                        <?php foreach($chartData['directions'] as $item): ?>
                        {
                            value: <?php echo $item['count']; ?>,
                            name: '<?php echo ($item['yon'] === 'giris') ? 'Giriş' : 'Çıkış'; ?>'
                        },
                        <?php endforeach; ?>
                    ],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            };
            directionChart.setOption(directionOption);

            // Chart 4: Monthly Trend (Line Chart)
            var monthlyTrendChart = echarts.init(document.getElementById('monthlyTrendChart'));
            var monthlyTrendOption = {
                title: {
                    text: 'Aylık Hareket Trendleri',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis'
                },
                xAxis: {
                    type: 'category',
                    data: [
                        <?php foreach($chartData['monthly_trends'] as $item): ?>
                        '<?php echo $item['month']; ?>',
                        <?php endforeach; ?>
                    ]
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    data: [
                        <?php foreach($chartData['monthly_trends'] as $item): ?>
                        <?php echo $item['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    type: 'line',
                    smooth: true,
                    itemStyle: {
                        color: '#7c2a99'
                    }
                }]
            };
            monthlyTrendChart.setOption(monthlyTrendOption);

            // Chart 5: Volume by Stock Type (Bar Chart)
            var volumeChart = echarts.init(document.getElementById('volumeChart'));
            var volumeOption = {
                title: {
                    text: 'Stok Türüne Göre Hacim',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                xAxis: {
                    type: 'category',
                    data: [
                        <?php foreach($chartData['volume_by_type'] as $item): ?>
                        '<?php echo $item['stok_turu']; ?>',
                        <?php endforeach; ?>
                    ]
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    data: [
                        <?php foreach($chartData['volume_by_type'] as $item): ?>
                        <?php echo $item['total_volume']; ?>,
                        <?php endforeach; ?>
                    ],
                    type: 'bar',
                    itemStyle: {
                        color: '#4a0e63'
                    }
                }]
            };
            volumeChart.setOption(volumeOption);

            // Chart 6: Top Products (Bar Chart)
            var topProductsChart = echarts.init(document.getElementById('topProductsChart'));
            var topProductsOption = {
                title: {
                    text: 'En Çok Hareket Eden 10 Ürün',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: [
                        <?php foreach($chartData['top_products'] as $item): ?>
                        '<?php echo addslashes($item['isim']); ?>',
                        <?php endforeach; ?>
                    ],
                    axisLabel: {
                        interval: 0,
                        rotate: 45
                    }
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    name: 'Hareket Sayısı',
                    type: 'bar',
                    data: [
                        <?php foreach($chartData['top_products'] as $item): ?>
                        <?php echo $item['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    itemStyle: {
                        color: '#d4af37'
                    }
                }]
            };
            topProductsChart.setOption(topProductsOption);

            // Make charts responsive
            window.addEventListener('resize', function() {
                movementTypeChart.resize();
                stockTypeChart.resize();
                directionChart.resize();
                monthlyTrendChart.resize();
                volumeChart.resize();
                topProductsChart.resize();
            });
        });
    </script>
</body>
</html>

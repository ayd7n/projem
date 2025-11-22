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
if (!yetkisi_var('page:view:montaj_is_emri_raporu')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Fetch data for charts
function getChartData() {
    global $connection;

    $data = [];

    // 1. KPIs
    // Total Orders
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM montaj_is_emirleri");
    $stmt->execute();
    $data['total_orders'] = $stmt->get_result()->fetch_assoc()['count'];

    // Active Production (Uretimde)
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM montaj_is_emirleri WHERE durum = 'uretimde'");
    $stmt->execute();
    $data['active_production'] = $stmt->get_result()->fetch_assoc()['count'];

    // Completed Today
    $today = date('Y-m-d');
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM montaj_is_emirleri WHERE durum = 'tamamlandi' AND gerceklesen_bitis_tarihi = ?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $data['completed_today'] = $stmt->get_result()->fetch_assoc()['count'];

    // Delayed Orders (Planned end date < Today AND Status != 'tamamlandi' AND Status != 'iptal')
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM montaj_is_emirleri WHERE planlanan_bitis_tarihi < ? AND durum NOT IN ('tamamlandi', 'iptal')");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $data['delayed_orders'] = $stmt->get_result()->fetch_assoc()['count'];


    // 2. Charts
    // Status Distribution
    $stmt = $connection->prepare("
        SELECT durum, COUNT(*) as count
        FROM montaj_is_emirleri
        GROUP BY durum
    ");
    $stmt->execute();
    $data['status_distribution'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Production Velocity (Last 30 Days)
    $stmt = $connection->prepare("
        SELECT DATE_FORMAT(gerceklesen_bitis_tarihi, '%Y-%m-%d') as date, COUNT(*) as count
        FROM montaj_is_emirleri
        WHERE durum = 'tamamlandi' 
          AND gerceklesen_bitis_tarihi >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY date
        ORDER BY date ASC
    ");
    $stmt->execute();
    $data['production_velocity'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Top 5 Products by Volume (All time)
    $stmt = $connection->prepare("
        SELECT urun_ismi, SUM(tamamlanan_miktar) as total_produced
        FROM montaj_is_emirleri
        WHERE durum = 'tamamlandi'
        GROUP BY urun_kodu
        ORDER BY total_produced DESC
        LIMIT 5
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
    <title>Montaj İş Emri Raporu - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <link rel="stylesheet" href="assets/css/stil.css">
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
        }
        
        .chart-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            height: 450px;
        }

        .chart-header {
            margin-bottom: 0.5rem;
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin: 0 0 0.5rem 0;
        }

        .chart-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .kpi-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
        }

        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .kpi-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php">
                <i class="fas fa-spa"></i> IDO KOZMETIK
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="navigation.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="montaj_is_emirleri.php">İş Emirleri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
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
                <h1>Montaj İş Emri Raporu</h1>
                <p>Montaj hattı performansı ve iş emri durumlarının gerçek zamanlı analizi</p>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="kpi-value"><?php echo number_format($chartData['total_orders']); ?></div>
                    <div class="kpi-label">Toplam İş Emri</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: rgba(241, 196, 15, 0.1); color: #f1c40f;">
                        <i class="fas fa-cog fa-spin"></i>
                    </div>
                    <div class="kpi-value"><?php echo number_format($chartData['active_production']); ?></div>
                    <div class="kpi-label">Aktif Üretim</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="kpi-value"><?php echo number_format($chartData['completed_today']); ?></div>
                    <div class="kpi-label">Bugün Tamamlanan</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="kpi-value"><?php echo number_format($chartData['delayed_orders']); ?></div>
                    <div class="kpi-label">Geciken Siparişler</div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row">
            <!-- Status Distribution -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">İş Emri Durum Dağılımı</h3>
                        <p class="chart-description text-muted small">
                            Tüm iş emirlerinin mevcut durumlarına göre dağılımını gösterir. Oluşturuldu, Üretimde, Tamamlandı ve İptal durumlarını içerir.
                        </p>
                    </div>
                    <div id="statusChart" style="width: 100%; height: 320px;"></div>
                </div>
            </div>

            <!-- Production Velocity -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Üretim Hızı (Son 30 Gün)</h3>
                        <p class="chart-description text-muted small">
                            Son 30 gün içinde tamamlanan iş emirlerinin günlük dağılımı. Üretim trendlerini ve yoğun günleri gösterir.
                        </p>
                    </div>
                    <div id="velocityChart" style="width: 100%; height: 320px;"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row">
            <!-- Top Products -->
            <div class="col-lg-12">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">En Çok Üretilen Ürünler (Tüm Zamanlar)</h3>
                        <p class="chart-description text-muted small">
                            Sistemde en fazla üretimi yapılan ilk 5 ürün. Toplam tamamlanan miktarlara göre sıralanmıştır.
                        </p>
                    </div>
                    <div id="topProductsChart" style="width: 100%; height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Common ECharts Option
            const commonOption = {
                textStyle: {
                    fontFamily: 'Ubuntu, sans-serif'
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: {
                        color: '#2c3e50'
                    },
                    padding: [10, 15],
                    extraCssText: 'box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px;'
                }
            };

            // 1. Status Chart (Donut)
            const statusChart = echarts.init(document.getElementById('statusChart'));
            const statusData = [
                <?php foreach($chartData['status_distribution'] as $item): ?>
                { 
                    value: <?php echo $item['count']; ?>, 
                    name: '<?php 
                        $statusNames = [
                            'olusturuldu' => 'Oluşturuldu',
                            'uretimde' => 'Üretimde',
                            'tamamlandi' => 'Tamamlandı',
                            'iptal' => 'İptal'
                        ];
                        echo $statusNames[$item['durum']] ?? ucfirst($item['durum']); 
                    ?>',
                    itemStyle: {
                        color: 
                            <?php 
                            switch($item['durum']) {
                                case 'olusturuldu': echo "'#95a5a6'"; break;
                                case 'uretimde': echo "'#f1c40f'"; break;
                                case 'tamamlandi': echo "'#2ecc71'"; break;
                                case 'iptal': echo "'#e74c3c'"; break;
                                default: echo "'#34495e'";
                            }
                            ?>
                    }
                },
                <?php endforeach; ?>
            ];

            statusChart.setOption({
                ...commonOption,
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    bottom: '0%',
                    left: 'center',
                    icon: 'circle'
                },
                series: [{
                    name: 'Durum',
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
                            fontSize: '20',
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: statusData
                }]
            });

            // 2. Velocity Chart (Line)
            const velocityChart = echarts.init(document.getElementById('velocityChart'));
            const dates = [<?php foreach($chartData['production_velocity'] as $item) echo "'" . $item['date'] . "',"; ?>];
            const counts = [<?php foreach($chartData['production_velocity'] as $item) echo $item['count'] . ","; ?>];

            velocityChart.setOption({
                ...commonOption,
                tooltip: {
                    trigger: 'axis'
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: dates,
                    axisLine: { lineStyle: { color: '#bdc3c7' } }
                },
                yAxis: {
                    type: 'value',
                    splitLine: { lineStyle: { type: 'dashed', color: '#ecf0f1' } }
                },
                series: [{
                    data: counts,
                    type: 'line',
                    smooth: true,
                    symbolSize: 8,
                    lineStyle: {
                        width: 4,
                        color: '#d4af37'
                    },
                    itemStyle: {
                        color: '#d4af37',
                        borderWidth: 2,
                        borderColor: '#fff'
                    },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(212, 175, 55, 0.5)' },
                            { offset: 1, color: 'rgba(212, 175, 55, 0.0)' }
                        ])
                    }
                }]
            });

            // 3. Top Products (Horizontal Bar)
            const topProductsChart = echarts.init(document.getElementById('topProductsChart'));
            const prodNames = [<?php foreach($chartData['top_products'] as $item) echo "'" . addslashes($item['urun_ismi']) . "',"; ?>];
            const prodValues = [<?php foreach($chartData['top_products'] as $item) echo $item['total_produced'] . ","; ?>];

            topProductsChart.setOption({
                ...commonOption,
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    splitLine: { show: false }
                },
                yAxis: {
                    type: 'category',
                    data: prodNames,
                    axisLine: { show: false },
                    axisTick: { show: false }
                },
                series: [{
                    data: prodValues,
                    type: 'bar',
                    itemStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: '#7c2a99' },
                            { offset: 1, color: '#4a0e63' }
                        ]),
                        borderRadius: [0, 5, 5, 0]
                    },
                    label: {
                        show: true,
                        position: 'right',
                        color: '#7f8c8d',
                        formatter: '{c} adet'
                    }
                }]
            });

            // Resize charts on window resize
            window.addEventListener('resize', function() {
                statusChart.resize();
                velocityChart.resize();
                topProductsChart.resize();
            });
        });
    </script>
</body>
</html>
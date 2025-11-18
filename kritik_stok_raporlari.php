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

// Calculate critical stock statistics for products
$total_products_result = $connection->query("SELECT COUNT(*) as total FROM urunler");
$total_products = $total_products_result->fetch_assoc()['total'] ?? 0;

$critical_products_result = $connection->query("SELECT COUNT(*) as total FROM urunler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0");
$critical_products = $critical_products_result->fetch_assoc()['total'] ?? 0;

$above_critical_products = max(0, $total_products - $critical_products);
$above_critical_percentage = $total_products > 0 ? round(($above_critical_products / $total_products) * 100) : 0;
$critical_percentage = $total_products > 0 ? round(($critical_products / $total_products) * 100) : 0;

// Calculate critical stock statistics for materials
$total_materials_result = $connection->query("SELECT COUNT(*) as total FROM malzemeler");
$total_materials = $total_materials_result->fetch_assoc()['total'] ?? 0;

$critical_materials_result = $connection->query("SELECT COUNT(*) as total FROM malzemeler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0");
$critical_materials = $critical_materials_result->fetch_assoc()['total'] ?? 0;

$above_critical_materials = max(0, $total_materials - $critical_materials);
$above_critical_materials_percentage = $total_materials > 0 ? round(($above_critical_materials / $total_materials) * 100) : 0;
$critical_materials_percentage = $total_materials > 0 ? round(($critical_materials / $total_materials) * 100) : 0;

// Get critical products
$critical_products_query = $connection->query("SELECT * FROM urunler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0 ORDER BY stok_miktari ASC LIMIT 10");
$critical_products_list = [];
while ($row = $critical_products_query->fetch_assoc()) {
    $critical_products_list[] = $row;
}

// Get critical materials
$critical_materials_query = $connection->query("SELECT * FROM malzemeler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0 ORDER BY stok_miktari ASC LIMIT 10");
$critical_materials_list = [];
while ($row = $critical_materials_query->fetch_assoc()) {
    $critical_materials_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kritik Stok Raporları - Parfüm ERP</title>
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
            --critical: #dc3545;
            --warning: #ffc107;
            --success: #28a745;
        }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
        }
        .main-content {
            padding: 1rem;
        }
        .page-header {
            margin-bottom: 1rem;
        }
        .page-header h1 {
            font-weight: 700;
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
        .stats-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 100%;
        }
        .chart-container {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 300px;
        }
        .chart-wrapper {
            height: 250px;
            position: relative;
        }
        .critical-product, .critical-material {
            background-color: #fff5f5;
            border-left: 4px solid var(--critical);
        }
        .stock-critical {
            color: #dc3545;
            font-weight: bold;
        }
        .stock-warning {
            color: #ffc107;
            font-weight: bold;
        }
        .stock-normal {
            color: #28a745;
            font-weight: bold;
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

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-exclamation-triangle"></i> Kritik Stok Raporları</h1>
            <p class="text-muted">Kritik seviyenin altındaki ürün ve malzeme stoğunu analiz edin</p>
        </div>

        <!-- Critical Stock Stats -->
        <div class="row mb-3">
            <div class="col-md-6 col-lg-3 mb-2">
                <div class="stats-card text-center">
                    <div class="stat-icon" style="display: block; font-size: 1.5rem; color: var(--primary); margin-bottom: 0.15rem;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 style="font-size: 1.2rem; margin: 0.15rem 0;"><?php echo $total_products; ?></h3>
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Toplam Ürün</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-2">
                <div class="stats-card text-center">
                    <div class="stat-icon" style="display: block; font-size: 1.5rem; color: var(--critical); margin-bottom: 0.15rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 style="font-size: 1.2rem; margin: 0.15rem 0;"><?php echo $critical_products; ?></h3>
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Kritik Ürün</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-2">
                <div class="stats-card text-center">
                    <div class="stat-icon" style="display: block; font-size: 1.5rem; color: var(--primary); margin-bottom: 0.15rem;">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <h3 style="font-size: 1.2rem; margin: 0.15rem 0;"><?php echo $total_materials; ?></h3>
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Toplam Malzeme</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-2">
                <div class="stats-card text-center">
                    <div class="stat-icon" style="display: block; font-size: 1.5rem; color: var(--critical); margin-bottom: 0.15rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 style="font-size: 1.2rem; margin: 0.15rem 0;"><?php echo $critical_materials; ?></h3>
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Kritik Malzeme</p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-3">
            <div class="col-lg-6 mb-3">
                <div class="chart-container">
                    <div class="chart-wrapper" id="productChart"></div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="chart-container">
                    <div class="chart-wrapper" id="materialChart"></div>
                </div>
            </div>
        </div>

        <!-- Critical Products Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-cube text-danger"></i> Kritik Seviye Altındaki Ürünler</h3>
            </div>
            <div class="card-body">
                <?php if (count($critical_products_list) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ürün Kodu</th>
                                    <th>Ürün İsmi</th>
                                    <th>Mevcut Stok</th>
                                    <th>Kritik Seviye</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($critical_products_list as $product): ?>
                                <tr class="critical-product">
                                    <td><?php echo htmlspecialchars($product['urun_kodu']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($product['urun_ismi']); ?></strong></td>
                                    <td><?php echo $product['stok_miktari']; ?></td>
                                    <td><?php echo $product['kritik_stok_seviyesi']; ?></td>
                                    <td>
                                        <?php if ($product['stok_miktari'] <= 0): ?>
                                            <span class="stock-critical">Stokta Yok</span>
                                        <?php elseif ($product['stok_miktari'] <= $product['kritik_stok_seviyesi']): ?>
                                            <span class="stock-critical">Kritik Seviye</span>
                                        <?php else: ?>
                                            <span class="stock-normal">Yeterli</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Kritik seviye altında ürün bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Critical Materials Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-cubes text-danger"></i> Kritik Seviye Altındaki Malzemeler</h3>
            </div>
            <div class="card-body">
                <?php if (count($critical_materials_list) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Malzeme Kodu</th>
                                    <th>Malzeme İsmi</th>
                                    <th>Mevcut Stok</th>
                                    <th>Kritik Seviye</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($critical_materials_list as $material): ?>
                                <tr class="critical-material">
                                    <td><?php echo htmlspecialchars($material['malzeme_kodu']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($material['malzeme_ismi']); ?></strong></td>
                                    <td><?php echo $material['stok_miktari']; ?></td>
                                    <td><?php echo $material['kritik_stok_seviyesi']; ?></td>
                                    <td>
                                        <?php if ($material['stok_miktari'] <= 0): ?>
                                            <span class="stock-critical">Stokta Yok</span>
                                        <?php elseif ($material['stok_miktari'] <= $material['kritik_stok_seviyesi']): ?>
                                            <span class="stock-critical">Kritik Seviye</span>
                                        <?php else: ?>
                                            <span class="stock-normal">Yeterli</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Kritik seviye altında malzeme bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ECharts Script -->
    <script>
        // Product Chart
        const productChart = echarts.init(document.getElementById('productChart'));
        const productOption = {
            title: {
                text: 'Ürün Stok Dağılımı',
                left: 'center',
                top: 5,
                textStyle: {
                    fontSize: 13,
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                trigger: 'item',
                formatter: function(params) {
                    const total = <?php echo $total_products; ?>;
                    const percentage = total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
                    return params.name + ': ' + params.value + ' (' + percentage + '%)';
                }
            },
            legend: {
                orient: 'horizontal',
                bottom: 5,
                textStyle: {
                    fontSize: 10
                }
            },
            series: [{
                name: 'Ürün Stok Dağılımı',
                type: 'pie',
                radius: ['30%', '65%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 5,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                label: {
                    show: true,
                    formatter: '{d}%',  // Show percentage instead of count
                    fontSize: 10
                },
                labelLine: {
                    show: true
                },
                legend: {
                    orient: 'horizontal',
                    bottom: 0,
                    textStyle: {
                        fontSize: 9
                    }
                },
                data: [
                    { value: <?php echo $above_critical_products; ?>, name: 'Kritik Seviye Üstü', itemStyle: { color: '#28a745' } },
                    { value: <?php echo $critical_products; ?>, name: 'Kritik Seviye Altı', itemStyle: { color: '#dc3545' } }
                ]
            }]
        };
        productChart.setOption(productOption);

        // Material Chart
        const materialChart = echarts.init(document.getElementById('materialChart'));
        const materialOption = {
            title: {
                text: 'Malzeme Stok Dağılımı',
                left: 'center',
                top: 5,
                textStyle: {
                    fontSize: 13,
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                trigger: 'item',
                formatter: function(params) {
                    const total = <?php echo $total_materials; ?>;
                    const percentage = total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
                    return params.name + ': ' + params.value + ' (' + percentage + '%)';
                }
            },
            legend: {
                orient: 'horizontal',
                bottom: 5,
                textStyle: {
                    fontSize: 10
                }
            },
            series: [{
                name: 'Malzeme Stok Dağılımı',
                type: 'pie',
                radius: ['30%', '65%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 5,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                label: {
                    show: true,
                    formatter: '{d}%',  // Show percentage instead of count
                    fontSize: 10
                },
                labelLine: {
                    show: true
                },
                legend: {
                    orient: 'horizontal',
                    bottom: 0,
                    textStyle: {
                        fontSize: 9
                    }
                },
                data: [
                    { value: <?php echo $above_critical_materials; ?>, name: 'Kritik Seviye Üstü', itemStyle: { color: '#28a745' } },
                    { value: <?php echo $critical_materials; ?>, name: 'Kritik Seviye Altı', itemStyle: { color: '#dc3545' } }
                ]
            }]
        };
        materialChart.setOption(materialOption);

        // Make charts responsive
        window.addEventListener('resize', function() {
            productChart.resize();
            materialChart.resize();
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
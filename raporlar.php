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
    <title>Raporlar - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
        }
        .navbar {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            box-shadow: var(--shadow);
        }
        .navbar-brand {
            color: var(--accent, #d4af37) !important;
            font-weight: 700;
        }
        .main-content {
            padding: 2rem;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-weight: 700;
        }
        .filter-bar {
            background-color: var(--card-bg);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }
        .chart-card {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
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

    <div class="container-fluid main-content">
        <div class="page-header">
            <h1><i class="fas fa-chart-pie"></i> Raporlar</h1>
            <p class="text-muted">Sistem verilerini analiz edin ve raporları görüntüleyin.</p>
        </div>

        <div class="filter-bar">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="yearFilter">Yıl Seçin</label>
                        <select id="yearFilter" class="form-control">
                            <?php
                                $currentYear = date('Y');
                                for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                                    echo "<option value='{$i}'>{$i}</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <?php if (yetkisi_var('action:raporlar:view_sales')): ?>
        <div class="row">
            <div class="col-12">
                <div class="chart-card">
                    <h5 class="mb-3">Aylık Satış Grafiği</h5>
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (yetkisi_var('action:raporlar:view_stock')): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="chart-card">
                    <h5 class="mb-3">Stok Raporu</h5>
                    <p>Stok raporu burada gösterilecek.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (yetkisi_var('action:raporlar:view_cost')): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="chart-card">
                    <h5 class="mb-3">Maliyet Raporu</h5>
                    <p>Maliyet raporu burada gösterilecek.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (yetkisi_var('action:raporlar:view_production')): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="chart-card">
                    <h5 class="mb-3">Üretim Raporu</h5>
                    <p>Üretim raporu burada gösterilecek.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        let monthlySalesChart;
        const yearFilter = $('#yearFilter');

        function renderMonthlySalesChart(year) {
            $.ajax({
                url: 'api_islemleri/rapor_islemler.php',
                type: 'GET',
                data: {
                    action: 'get_monthly_sales',
                    year: year
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
                        
                        if (monthlySalesChart) {
                            monthlySalesChart.destroy();
                        }

                        monthlySalesChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: response.labels,
                                datasets: [{
                                    label: 'Toplam Satış (₺)',
                                    data: response.data,
                                    backgroundColor: 'rgba(124, 42, 153, 0.6)',
                                    borderColor: 'rgba(74, 14, 99, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value, index, values) {
                                                return '₺' + value.toLocaleString('tr-TR');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        alert('Rapor verisi alınamadı: ' + response.message);
                    }
                },
                error: function() {
                    alert('Rapor sunucusuna bağlanırken bir hata oluştu.');
                }
            });
        }

        yearFilter.on('change', function() {
            renderMonthlySalesChart($(this).val());
        });

        // Initial render
        renderMonthlySalesChart(yearFilter.val());
    });
    </script>
</body>
</html>
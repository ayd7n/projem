<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

// Permission check
if (!yetkisi_var('page:view:raporlar')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

$report_data = [];
$query = "
    SELECT
        t.tedarikci_adi,
        c.para_birimi,
        SUM(c.toplam_mal_kabul_miktari - IFNULL(c.toplu_odenen_miktar, 0)) AS yapilacak_odeme_miktari,
        SUM((c.toplam_mal_kabul_miktari - IFNULL(c.toplu_odenen_miktar, 0)) * c.birim_fiyat) AS yapilacak_odeme_tutari
    FROM
        cerceve_sozlesmeler_gecerlilik c
    JOIN
        tedarikciler t ON c.tedarikci_id = t.tedarikci_id
    WHERE
        (c.toplam_mal_kabul_miktari - IFNULL(c.toplu_odenen_miktar, 0)) > 0
    GROUP BY
        t.tedarikci_adi, c.para_birimi
    ORDER BY
        t.tedarikci_adi, c.para_birimi;
";

$result = $connection->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $report_data[] = $row;
    }
}

function format_currency($amount, $currency) {
    $symbol = '';
    switch ($currency) {
        case 'TL': $symbol = '₺'; break;
        case 'USD': $symbol = '$'; break;
        case 'EUR': $symbol = '€'; break;
    }
    return number_format($amount, 2, ',', '.') . ' ' . $symbol;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tedarikçiye Yapılacak Ödemeler Raporu - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css">
    <!-- Apache ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.3.3/dist/echarts.min.js"></script>
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
                    <li class="nav-item"><a class="nav-link" href="navigation.php">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="raporlar.php">Raporlar</a></li>
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
                <h1>Tedarikçiye Yapılacak Ödemeler Raporu</h1>
                <p>Çerçeve sözleşmelere göre tedarikçilere yapılması gereken bekleyen ödemeler.</p>
            </div>
        </div>

        <?php if (!empty($report_data)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h2><i class="fas fa-chart-bar"></i> Ödeme Grafiği</h2>
            </div>
            <div class="card-body">
                <div id="paymentChart" style="width: 100%; height:400px;"></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-table"></i> Ödeme Raporu Detayları</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-building"></i> Tedarikçi</th>
                                <th><i class="fas fa-boxes"></i> Yapılacak Ödeme (Adet)</th>
                                <th><i class="fas fa-money-bill-wave"></i> Yapılacak Ödeme Tutarı</th>
                                <th><i class="fas fa-coins"></i> Para Birimi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['tedarikci_adi']); ?></strong></td>
                                        <td><span class="badge badge-info"><?php echo number_format($row['yapilacak_odeme_miktari'], 2, ',', '.'); ?> Adet</span></td>
                                        <td><strong><span class="badge badge-success"><?php echo format_currency($row['yapilacak_odeme_tutari'], $row['para_birimi']); ?></span></strong></td>
                                        <td>
                                            <?php 
                                            $curr = $row['para_birimi'];
                                            if ($curr === 'TL') echo '<span class="badge badge-primary">₺ TRY</span>';
                                            elseif ($curr === 'USD') echo '<span class="badge badge-success">$ USD</span>';
                                            elseif ($curr === 'EUR') echo '<span class="badge badge-warning">€ EUR</span>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center p-4">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                        <h4>Tebrikler!</h4>
                                        <p class="text-muted">Tedarikçilere yapılacak bekleyen bir ödeme bulunmamaktadır.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <?php if (!empty($report_data)): ?>
    <script type="text/javascript">
        // Gelen veriyi PHP'den JS'e aktar
        var chartData = <?php echo json_encode($report_data); ?>;

        // ECharts için veriyi hazırla
        var suppliers = {};
        chartData.forEach(function(item) {
            var supplier = item.tedarikci_adi;
            var currency = item.para_birimi;
            var amount = parseFloat(item.yapilacak_odeme_tutari);

            if (!suppliers[supplier]) {
                suppliers[supplier] = { 'TL': 0, 'USD': 0, 'EUR': 0 };
            }
            suppliers[supplier][currency] += amount;
        });

        var supplierNames = Object.keys(suppliers);
        var seriesData = [
            {
                name: 'TL',
                type: 'bar',
                stack: 'total',
                label: { show: true, formatter: '{c} ₺' },
                emphasis: { focus: 'series' },
                data: supplierNames.map(function(name) { return suppliers[name]['TL']; }),
                itemStyle: { color: '#5470C6' }
            },
            {
                name: 'USD',
                type: 'bar',
                stack: 'total',
                label: { show: true, formatter: '{c} $' },
                emphasis: { focus: 'series' },
                data: supplierNames.map(function(name) { return suppliers[name]['USD']; }),
                itemStyle: { color: '#91CC75' }
            },
            {
                name: 'EUR',
                type: 'bar',
                stack: 'total',
                label: { show: true, formatter: '{c} €' },
                emphasis: { focus: 'series' },
                data: supplierNames.map(function(name) { return suppliers[name]['EUR']; }),
                itemStyle: { color: '#FAC858' }
            }
        ];

        // Grafiği başlat
        var chartDom = document.getElementById('paymentChart');
        var myChart = echarts.init(chartDom);
        var option;

        // Yeni renk paleti
        var colors = ['#8360c3', '#2ebf91', '#f9d05d']; // Mor-yeşil gradient, altın sarısı

        option = {
            title: {
                text: 'Tedarikçiye Göre Bekleyen Ödeme Tutarları (Gruplandırılmış)',
                left: 'center',
                textStyle: {
                    color: '#333',
                    fontSize: 18,
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                data: ['TL', 'USD', 'EUR'],
                top: 'bottom'
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '10%',
                containLabel: true
            },
            xAxis: [
                {
                    type: 'category',
                    data: supplierNames,
                    axisPointer: {
                        type: 'shadow'
                    },
                    axisLabel: {
                        rotate: 15,
                        interval: 0
                    }
                }
            ],
            yAxis: [
                {
                    type: 'value',
                    name: 'Tutar',
                    axisLabel: {
                        formatter: function (value) {
                             // Değerleri kısalt (örn: 1000 -> 1k)
                            if (value >= 1000) {
                                return (value / 1000) + 'k';
                            }
                            return value;
                        }
                    }
                }
            ],
            series: [
                {
                    name: 'TL',
                    type: 'bar',
                    // stack: 'total', // Gruplamak için stack kaldırıldı
                    label: { show: false },
                    emphasis: { focus: 'series' },
                    data: supplierNames.map(function(name) { return suppliers[name]['TL']; }),
                    itemStyle: { color: colors[0] }
                },
                {
                    name: 'USD',
                    type: 'bar',
                    // stack: 'total',
                    label: { show: false },
                    emphasis: { focus: 'series' },
                    data: supplierNames.map(function(name) { return suppliers[name]['USD']; }),
                    itemStyle: { color: colors[1] }
                },
                {
                    name: 'EUR',
                    type: 'bar',
                    // stack: 'total',
                    label: { show: false },
                    emphasis: { focus: 'series' },
                    data: supplierNames.map(function(name) { return suppliers[name]['EUR']; }),
                    itemStyle: { color: colors[2] }
                }
            ]
        };

        option && myChart.setOption(option);

        // Pencere yeniden boyutlandırıldığında grafiği yeniden boyutlandır
        $(window).on('resize', function() {
            if (myChart != null && myChart != undefined) {
                myChart.resize();
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>

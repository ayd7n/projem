<?php
include 'config.php';

// Oturum ve yetki kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['taraf'] !== 'personel' || !yetkisi_var('page:view:raporlar')) {
    header('Location: login.php'); // Yetkisi yoksa veya personel değilse login'e yönlendir
    exit;
}

/**
 * Rapor için gerekli tüm verileri çeken fonksiyon.
 * @return array Rapor verilerini içeren dizi.
 */
function getMontajReportData() {
    global $connection;
    $data = [];

    // 1. KPI Metrikleri
    $kpi_sql = "
        SELECT
            COUNT(*) AS total_orders,
            SUM(CASE WHEN durum = 'uretimde' THEN 1 ELSE 0 END) AS active_orders,
            SUM(planlanan_miktar) AS total_planned,
            SUM(tamamlanan_miktar) AS total_completed,
            (
                SELECT COUNT(*) 
                FROM montaj_is_emirleri 
                WHERE durum = 'tamamlandi' 
                AND gerceklesen_bitis_tarihi <= planlanan_bitis_tarihi
            ) AS on_time_completed,
            (
                SELECT COUNT(*) 
                FROM montaj_is_emirleri 
                WHERE durum = 'tamamlandi'
            ) AS total_completed_orders,
            (
                SELECT AVG(DATEDIFF(gerceklesen_bitis_tarihi, gerceklesen_baslangic_tarihi)) 
                FROM montaj_is_emirleri 
                WHERE durum = 'tamamlandi' AND gerceklesen_baslangic_tarihi IS NOT NULL AND gerceklesen_bitis_tarihi IS NOT NULL
            ) AS avg_completion_days
        FROM montaj_is_emirleri
    ";
    $kpi_res = $connection->query($kpi_sql);
    $kpi_data = $kpi_res->fetch_assoc();

    $data['kpi'] = [
        'total_orders' => $kpi_data['total_orders'] ?? 0,
        'active_orders' => $kpi_data['active_orders'] ?? 0,
        'efficiency' => ($kpi_data['total_planned'] > 0) ? round(($kpi_data['total_completed'] / $kpi_data['total_planned']) * 100, 1) : 0,
        'on_time_rate' => ($kpi_data['total_completed_orders'] > 0) ? round(($kpi_data['on_time_completed'] / $kpi_data['total_completed_orders']) * 100, 1) : 0,
        'avg_completion_days' => ($kpi_data['avg_completion_days'] > 0) ? round($kpi_data['avg_completion_days'], 1) : 0
    ];
    $kpi_res->close();

    // 2. Grafik: Son 30 Günlük Üretim Performansı
    $trend_sql = "
        SELECT
            DATE(tarih) as production_date,
            SUM(planlanan_miktar) as planned,
            SUM(tamamlanan_miktar) as completed
        FROM (
            SELECT planlanan_baslangic_tarihi as tarih, planlanan_miktar, 0 as tamamlanan_miktar
            FROM montaj_is_emirleri
            WHERE planlanan_baslangic_tarihi >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            SELECT gerceklesen_bitis_tarihi as tarih, 0 as planlanan_miktar, tamamlanan_miktar
            FROM montaj_is_emirleri
            WHERE durum = 'tamamlandi' AND gerceklesen_bitis_tarihi >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ) as daily_data
        WHERE tarih IS NOT NULL
        GROUP BY production_date
        ORDER BY production_date ASC
    ";
    $trend_res = $connection->query($trend_sql);
    $data['production_trend'] = $trend_res->fetch_all(MYSQLI_ASSOC);
    $trend_res->close();

    // 3. Grafik: İş Merkezi Verimliliği
    $wc_eff_sql = "
        SELECT
            im.isim as work_center,
            (SUM(mie.tamamlanan_miktar) / SUM(mie.planlanan_miktar)) * 100 as efficiency
        FROM montaj_is_emirleri mie
        JOIN is_merkezleri im ON mie.is_merkezi_id = im.is_merkezi_id
        WHERE mie.durum = 'tamamlandi' AND mie.planlanan_miktar > 0
        GROUP BY mie.is_merkezi_id
        ORDER BY efficiency DESC
        LIMIT 10
    ";
    $wc_eff_res = $connection->query($wc_eff_sql);
    $data['work_center_efficiency'] = $wc_eff_res->fetch_all(MYSQLI_ASSOC);
    $wc_eff_res->close();

    // 4. Grafik: Durum Dağılımı
    $status_sql = "SELECT durum, COUNT(*) as count FROM montaj_is_emirleri GROUP BY durum";
    $status_res = $connection->query($status_sql);
    $data['status_distribution'] = $status_res->fetch_all(MYSQLI_ASSOC);
    $status_res->close();

    // 5. Tablo: Dikkat Gerektiren İş Emirleri (Gecikmiş)
    $delayed_sql = "
        SELECT
            mie.is_emri_numarasi,
            u.urun_ismi,
            im.isim as work_center,
            mie.planlanan_bitis_tarihi,
            mie.durum
        FROM montaj_is_emirleri mie
        LEFT JOIN urunler u ON mie.urun_kodu = u.urun_kodu
        LEFT JOIN is_merkezleri im ON mie.is_merkezi_id = im.is_merkezi_id
        WHERE mie.durum NOT IN ('tamamlandi', 'iptal') AND mie.planlanan_bitis_tarihi < CURDATE()
        ORDER BY mie.planlanan_bitis_tarihi ASC
    ";
    $delayed_res = $connection->query($delayed_sql);
    $data['delayed_orders'] = $delayed_res->fetch_all(MYSQLI_ASSOC);
    $delayed_res->close();

    return $data;
}

$reportData = getMontajReportData();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gelişmiş Montaj Raporu - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <link rel="stylesheet" href="assets/css/stil.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Ubuntu', sans-serif; }
        .navbar-brand { color: #d4af37 !important; font-weight: 700; }
        .main-header { color: #4a0e63; }
        .kpi-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-left: 5px solid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .kpi-icon { font-size: 2rem; opacity: 0.7; }
        .kpi-value { font-size: 2.25rem; font-weight: 700; color: #333; }
        .kpi-label { font-size: 0.9rem; color: #6c757d; font-weight: 500; }
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            height: 400px;
        }
        .chart-title { font-size: 1.1rem; font-weight: 700; color: #4a0e63; margin-bottom: 1rem; }
        .table-responsive {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="navigation.php">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="raporlar.php">Raporlar</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="logout.php">Çıkış Yap</a></div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">
        <h1 class="h2 font-weight-bold main-header mb-4">Gelişmiş Montaj Analiz Raporu</h1>

        <!-- KPI Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4"><div class="kpi-card" style="border-color: #6f42c1;"><i class="fas fa-industry kpi-icon"></i><div><div class="kpi-value"><?= number_format($reportData['kpi']['total_orders']) ?></div><div class="kpi-label">Toplam İş Emri</div></div></div></div>
            <div class="col-lg-3 col-md-6 mb-4"><div class="kpi-card" style="border-color: #28a745;"><i class="fas fa-bullseye kpi-icon"></i><div><div class="kpi-value"><?= $reportData['kpi']['efficiency'] ?>%</div><div class="kpi-label">Genel Verimlilik</div></div></div></div>
            <div class="col-lg-3 col-md-6 mb-4"><div class="kpi-card" style="border-color: #17a2b8;"><i class="fas fa-calendar-check kpi-icon"></i><div><div class="kpi-value"><?= $reportData['kpi']['on_time_rate'] ?>%</div><div class="kpi-label">Zamanında Tamamlanma</div></div></div></div>
            <div class="col-lg-3 col-md-6 mb-4"><div class="kpi-card" style="border-color: #ffc107;"><i class="fas fa-hourglass-half kpi-icon"></i><div><div class="kpi-value"><?= $reportData['kpi']['avg_completion_days'] ?> gün</div><div class="kpi-label">Ort. Üretim Süresi</div></div></div></div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-lg-8"><div class="chart-card"><h3 class="chart-title">Üretim Performansı (Planlanan vs Tamamlanan - 30 Gün)</h3><div id="trendChart" style="width:100%;height:320px;"></div></div></div>
            <div class="col-lg-4"><div class="chart-card"><h3 class="chart-title">İş Emri Durum Dağılımı</h3><div id="statusChart" style="width:100%;height:320px;"></div></div></div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-12"><div class="chart-card"><h3 class="chart-title">İş Merkezi Verimlilik Analizi</h3><div id="wcEfficiencyChart" style="width:100%;height:320px;"></div></div></div>
        </div>
        
        <!-- Delayed Orders Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-responsive">
                    <h3 class="chart-title">Dikkat Gerektiren İş Emirleri (Gecikmiş)</h3>
                    <p class="text-muted small mt-2 mb-0 text-center font-italic">
                        <i class="fas fa-info-circle mr-1"></i> Tamamlanmamış veya iptal edilmemiş olup planlanan bitiş tarihi geçmiş olan iş emirlerini gösterir.
                    </p>
                    <?php if (!empty($reportData['delayed_orders'])): ?>
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr><th>İş Emri No</th><th>Ürün Adı</th><th>İş Merkezi</th><th>Planlanan Bitiş</th><th>Durum</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($reportData['delayed_orders'] as $order): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($order['is_emri_numarasi']) ?></strong></td>
                                <td><?= htmlspecialchars($order['urun_ismi'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($order['work_center'] ?? 'N/A') ?></td>
                                <td><span class="badge badge-danger"><?= date('d.m.Y', strtotime($order['planlanan_bitis_tarihi'])) ?></span></td>
                                <td><span class="badge badge-warning"><?= htmlspecialchars(ucfirst($order['durum'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-success text-center"><i class="fas fa-check-circle"></i> Gecikmiş iş emri bulunmuyor.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const commonOptions = {
            textStyle: { fontFamily: 'Ubuntu' },
            tooltip: { trigger: 'axis' },
            grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true }
        };

        // Üretim Performans Grafiği
        const trendChart = echarts.init(document.getElementById('trendChart'));
        trendChart.setOption({
            ...commonOptions,
            legend: { data: ['Planlanan', 'Tamamlanan'] },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: <?= json_encode(array_column($reportData['production_trend'], 'production_date')) ?>
            },
            yAxis: { type: 'value' },
            series: [
                { name: 'Planlanan', type: 'line', smooth: true, data: <?= json_encode(array_column($reportData['production_trend'], 'planned')) ?>, lineStyle: { color: '#6c757d' } },
                { name: 'Tamamlanan', type: 'line', smooth: true, data: <?= json_encode(array_column($reportData['production_trend'], 'completed')) ?>, areaStyle: {}, itemStyle: { color: '#4a0e63' }, lineStyle: { color: '#4a0e63' } }
            ]
        });

        // Durum Dağılım Grafiği
        const statusChart = echarts.init(document.getElementById('statusChart'));
        statusChart.setOption({
            tooltip: { trigger: 'item' },
            legend: { orient: 'vertical', left: 'left' },
            series: [{
                type: 'pie',
                radius: ['50%', '70%'],
                avoidLabelOverlap: false,
                label: { show: false },
                emphasis: { label: { show: true, fontSize: 20, fontWeight: 'bold' } },
                data: <?= json_encode(array_map(function($item) {
                    $colors = ['olusturuldu' => '#95a5a6', 'uretimde' => '#f1c40f', 'tamamlandi' => '#2ecc71', 'iptal' => '#e74c3c'];
                    return ['value' => $item['count'], 'name' => ucfirst($item['durum']), 'itemStyle' => ['color' => $colors[$item['durum']] ?? '#343a40']];
                }, $reportData['status_distribution'])) ?>
            }]
        });

        // İş Merkezi Verimlilik Grafiği
        const wcEfficiencyChart = echarts.init(document.getElementById('wcEfficiencyChart'));
        wcEfficiencyChart.setOption({
            ...commonOptions,
            xAxis: { type: 'value', boundaryGap: [0, 0.01] },
            yAxis: { type: 'category', data: <?= json_encode(array_column($reportData['work_center_efficiency'], 'work_center')) ?> },
            series: [{
                type: 'bar',
                data: <?= json_encode(array_map(function($item){ return round($item['efficiency'], 1); }, $reportData['work_center_efficiency'])) ?>,
                itemStyle: { color: '#7c2a99' },
                label: { show: true, position: 'right', formatter: '{c}%' }
            }]
        });

        // Resize all charts on window resize
        window.addEventListener('resize', () => {
            trendChart.resize();
            statusChart.resize();
            wcEfficiencyChart.resize();
        });
    });
    </script>
</body>
</html>
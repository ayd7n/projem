<?php
include 'config.php';
error_reporting(0); // Hataların ekrana basılmasını ve JS'i bozmasını engelle

// Oturum ve yetki kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['taraf'] !== 'personel' || !yetkisi_var('page:view:raporlar')) {
    header('Location: login.php');
    exit;
}

/**
 * Rapor için gerekli tüm verileri çeken fonksiyon.
 * @return array Rapor verilerini içeren dizi.
 */
function getMontajReportData() {
    global $connection;
    $data = [];

    // Varsayılan boş değerler
    $data['kpi'] = [
        'total_orders' => 0, 'active_orders' => 0, 'efficiency' => 0,
        'on_time_rate' => 0, 'avg_completion_days' => 0,
        'completed_today' => 0, 'delayed_orders' => 0
    ];
    $data['production_trend'] = [];
    $data['production_velocity'] = [];
    $data['work_center_efficiency'] = [];
    $data['status_distribution'] = [];
    $data['top_products'] = [];
    $data['delayed_orders_table'] = [];

    try {
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
                ) AS avg_completion_days,
                (
                    SELECT COUNT(*) 
                    FROM montaj_is_emirleri 
                    WHERE durum = 'tamamlandi' AND DATE(gerceklesen_bitis_tarihi) = CURDATE()
                ) AS completed_today,
                (
                    SELECT COUNT(*) 
                    FROM montaj_is_emirleri 
                    WHERE planlanan_bitis_tarihi < CURDATE() AND durum NOT IN ('tamamlandi', 'iptal')
                ) AS delayed_orders
            FROM montaj_is_emirleri
        ";
        $kpi_res = $connection->query($kpi_sql);
        if ($kpi_res) {
            $kpi_data = $kpi_res->fetch_assoc();
            $data['kpi'] = [
                'total_orders' => $kpi_data['total_orders'] ?? 0,
                'active_orders' => $kpi_data['active_orders'] ?? 0,
                'efficiency' => ($kpi_data['total_planned'] > 0) ? round(($kpi_data['total_completed'] / $kpi_data['total_planned']) * 100, 1) : 0,
                'on_time_rate' => ($kpi_data['total_completed_orders'] > 0) ? round(($kpi_data['on_time_completed'] / $kpi_data['total_completed_orders']) * 100, 1) : 0,
                'avg_completion_days' => ($kpi_data['avg_completion_days'] > 0) ? round($kpi_data['avg_completion_days'], 1) : 0,
                'completed_today' => $kpi_data['completed_today'] ?? 0,
                'delayed_orders' => $kpi_data['delayed_orders'] ?? 0
            ];
            $kpi_res->close();
        }

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
        if ($trend_res) {
            $data['production_trend'] = $trend_res->fetch_all(MYSQLI_ASSOC);
            $trend_res->close();
        }

        // 3. Grafik: Üretim Hızı
        $velocity_sql = "
            SELECT DATE_FORMAT(gerceklesen_bitis_tarihi, '%Y-%m-%d') as date, COUNT(*) as count
            FROM montaj_is_emirleri
            WHERE durum = 'tamamlandi' 
              AND gerceklesen_bitis_tarihi >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY date
            ORDER BY date ASC
        ";
        $velocity_res = $connection->query($velocity_sql);
        if ($velocity_res) {
            $data['production_velocity'] = $velocity_res->fetch_all(MYSQLI_ASSOC);
            $velocity_res->close();
        }

        // 4. Grafik: İş Merkezi Verimliliği
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
        if ($wc_eff_res) {
            $data['work_center_efficiency'] = $wc_eff_res->fetch_all(MYSQLI_ASSOC);
            $wc_eff_res->close();
        }

        // 5. Grafik: Durum Dağılımı
        $status_sql = "SELECT durum, COUNT(*) as count FROM montaj_is_emirleri GROUP BY durum";
        $status_res = $connection->query($status_sql);
        if ($status_res) {
            $data['status_distribution'] = $status_res->fetch_all(MYSQLI_ASSOC);
            $status_res->close();
        }

        // 6. Grafik: En Çok Üretilen Ürünler
        $top_products_sql = "
            SELECT urun_ismi, SUM(tamamlanan_miktar) as total_produced
            FROM montaj_is_emirleri
            WHERE durum = 'tamamlandi'
            GROUP BY urun_kodu
            ORDER BY total_produced DESC
            LIMIT 5
        ";
        $top_products_res = $connection->query($top_products_sql);
        if ($top_products_res) {
            $data['top_products'] = $top_products_res->fetch_all(MYSQLI_ASSOC);
            $top_products_res->close();
        }

        // 7. Tablo: Gecikmiş İş Emirleri
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
        if ($delayed_res) {
            $data['delayed_orders_table'] = $delayed_res->fetch_all(MYSQLI_ASSOC);
            $delayed_res->close();
        }

    } catch (Exception $e) {
        // Hata durumunda logla ama kullanıcıya gösterme
        error_log("Montaj Raporu Hatası: " . $e->getMessage());
    }

    return $data;
}

$reportData = getMontajReportData();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Montaj Raporu - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <link rel="stylesheet" href="assets/css/stil.css">
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Ubuntu', sans-serif; 
        }
        .navbar-brand { 
            color: #d4af37 !important; 
            font-weight: 700; 
        }
        .main-header { 
            color: #4a0e63; 
            margin-bottom: 0.5rem;
        }
        .main-subtitle {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 2rem;
        }
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
            transition: transform 0.3s ease;
        }
        .kpi-card:hover {
            transform: translateY(-5px);
        }
        .kpi-icon { 
            font-size: 2rem; 
            opacity: 0.7; 
        }
        .kpi-value { 
            font-size: 2.25rem; 
            font-weight: 700; 
            color: #333; 
        }
        .kpi-label { 
            font-size: 0.9rem; 
            color: #6c757d; 
            font-weight: 500; 
        }
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .chart-card.large {
            height: 450px;
        }
        .chart-card.medium {
            height: 400px;
        }
        .chart-title { 
            font-size: 1.1rem; 
            font-weight: 700; 
            color: #4a0e63; 
            margin-bottom: 0.5rem; 
        }
        .chart-description {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
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
        <h1 class="h2 font-weight-bold main-header">Montaj Raporu</h1>
        <p class="main-subtitle">Montaj hattı performansı, iş emri durumları ve üretim analizinin gerçek zamanlı görünümü</p>

        <!-- KPI Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card" style="border-color: #6f42c1;">
                    <i class="fas fa-clipboard-list kpi-icon" style="color: #6f42c1;"></i>
                    <div>
                        <div class="kpi-value"><?php echo number_format($reportData['kpi']['total_orders']); ?></div>
                        <div class="kpi-label">Toplam İş Emri</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card" style="border-color: #f1c40f;">
                    <i class="fas fa-cog kpi-icon" style="color: #f1c40f;"></i>
                    <div>
                        <div class="kpi-value"><?php echo number_format($reportData['kpi']['active_orders']); ?></div>
                        <div class="kpi-label">Aktif Üretim</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card" style="border-color: #2ecc71;">
                    <i class="fas fa-check-circle kpi-icon" style="color: #2ecc71;"></i>
                    <div>
                        <div class="kpi-value"><?php echo number_format($reportData['kpi']['completed_today']); ?></div>
                        <div class="kpi-label">Bugün Tamamlanan</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card" style="border-color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle kpi-icon" style="color: #e74c3c;"></i>
                    <div>
                        <div class="kpi-value"><?php echo number_format($reportData['kpi']['delayed_orders']); ?></div>
                        <div class="kpi-label">Geciken Siparişler</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="kpi-card" style="border-color: #28a745;">
                    <i class="fas fa-bullseye kpi-icon" style="color: #28a745;"></i>
                    <div>
                        <div class="kpi-value"><?php echo $reportData['kpi']['efficiency']; ?>%</div>
                        <div class="kpi-label">Genel Verimlilik</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="kpi-card" style="border-color: #17a2b8;">
                    <i class="fas fa-calendar-check kpi-icon" style="color: #17a2b8;"></i>
                    <div>
                        <div class="kpi-value"><?php echo $reportData['kpi']['on_time_rate']; ?>%</div>
                        <div class="kpi-label">Zamanında Tamamlanma</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="kpi-card" style="border-color: #ffc107;">
                    <i class="fas fa-hourglass-half kpi-icon" style="color: #ffc107;"></i>
                    <div>
                        <div class="kpi-value"><?php echo $reportData['kpi']['avg_completion_days']; ?> gün</div>
                        <div class="kpi-label">Ort. Üretim Süresi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1: Production Trend -->
        <div class="row">
            <div class="col-12">
                <div class="chart-card large">
                    <h3 class="chart-title">Üretim Performansı Trendi (Son 30 Gün)</h3>
                    <p class="chart-description">Planlanan ve tamamlanan üretim miktarlarının günlük karşılaştırması. Üretim hedeflerine ne kadar yaklaşıldığını gösterir.</p>
                    <div id="trendChart" style="width:100%;height:350px;"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Status & Velocity -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-card medium">
                    <h3 class="chart-title">İş Emri Durum Dağılımı</h3>
                    <p class="chart-description">Tüm iş emirlerinin mevcut durumlarına göre dağılımı. Oluşturuldu, Üretimde, Tamamlandı ve İptal durumlarını içerir.</p>
                    <div id="statusChart" style="width:100%;height:280px;"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card medium">
                    <h3 class="chart-title">Üretim Hızı (Son 30 Gün)</h3>
                    <p class="chart-description">Son 30 gün içinde günlük tamamlanan iş emri sayısı. Üretim trendlerini ve yoğun günleri gösterir.</p>
                    <div id="velocityChart" style="width:100%;height:280px;"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row 3: Work Center Efficiency & Top Products -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-card medium">
                    <h3 class="chart-title">İş Merkezi Verimlilik Analizi</h3>
                    <p class="chart-description">Her iş merkezinin verimlilik yüzdesi. Tamamlanan miktar / planlanan miktar oranı ile hesaplanır.</p>
                    <div id="wcEfficiencyChart" style="width:100%;height:280px;"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card medium">
                    <h3 class="chart-title">En Çok Üretilen Ürünler (Tüm Zamanlar)</h3>
                    <p class="chart-description">Sistemde en fazla üretimi yapılan ilk 5 ürün. Toplam tamamlanan miktarlara göre sıralanmıştır.</p>
                    <div id="topProductsChart" style="width:100%;height:280px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Delayed Orders Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-responsive">
                    <h3 class="chart-title">Dikkat Gerektiren İş Emirleri (Gecikmiş)</h3>
                    <p class="text-muted small mt-2 mb-3 text-center font-italic">
                        <i class="fas fa-info-circle mr-1"></i> Tamamlanmamış veya iptal edilmemiş olup planlanan bitiş tarihi geçmiş olan iş emirlerini gösterir.
                    </p>
                    <?php if (!empty($reportData['delayed_orders_table'])): ?>
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>İş Emri No</th>
                                <th>Ürün Adı</th>
                                <th>İş Merkezi</th>
                                <th>Planlanan Bitiş</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($reportData['delayed_orders_table'] as $order): ?>
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
        // Ortak Tema Ayarları
        const themeColors = {
            primary: ['#8e44ad', '#6c3483'], // Mor Gradient
            secondary: ['#f1c40f', '#d4ac0d'], // Altın Gradient
            success: ['#2ecc71', '#27ae60'], // Yeşil Gradient
            danger: ['#e74c3c', '#c0392b'], // Kırmızı Gradient
            info: ['#3498db', '#2980b9'] // Mavi Gradient
        };

        const commonOptions = {
            textStyle: { fontFamily: 'Inter, "Ubuntu", sans-serif' },
            tooltip: { 
                trigger: 'axis',
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                borderColor: '#f0f0f0',
                borderWidth: 1,
                textStyle: { color: '#2c3e50', fontSize: 13 },
                padding: [12, 16],
                extraCssText: 'box-shadow: 0 8px 24px rgba(0,0,0,0.12); border-radius: 12px; backdrop-filter: blur(5px);'
            },
            grid: { 
                left: '3%', 
                right: '4%', 
                bottom: '3%', 
                top: '15%',
                containLabel: true,
                borderColor: '#f0f0f0',
                show: true,
                borderWidth: 0
            },
            legend: {
                bottom: 0,
                icon: 'circle',
                itemGap: 20,
                textStyle: { color: '#7f8c8d', fontSize: 12 }
            }
        };

        // Helper: Gradient Oluşturucu
        function createGradient(colors) {
            return new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                { offset: 0, color: colors[0] },
                { offset: 1, color: colors[1] }
            ]);
        }
        
        function createHorizontalGradient(colors) {
            return new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                { offset: 0, color: colors[0] },
                { offset: 1, color: colors[1] }
            ]);
        }

        // 1. Üretim Performans Grafiği (Planlanan vs Tamamlanan)
        const trendChart = echarts.init(document.getElementById('trendChart'));
        const trendDates = <?php echo !empty($reportData['production_trend']) ? json_encode(array_column($reportData['production_trend'], 'production_date')) : '[]'; ?>;
        const trendPlanned = <?php echo !empty($reportData['production_trend']) ? json_encode(array_column($reportData['production_trend'], 'planned')) : '[]'; ?>;
        const trendCompleted = <?php echo !empty($reportData['production_trend']) ? json_encode(array_column($reportData['production_trend'], 'completed')) : '[]'; ?>;
        
        trendChart.setOption({
            ...commonOptions,
            legend: { ...commonOptions.legend, data: ['Planlanan', 'Tamamlanan'], top: 0 },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: trendDates,
                axisLine: { lineStyle: { color: '#e0e0e0' } },
                axisLabel: { color: '#7f8c8d', margin: 15 }
            },
            yAxis: { 
                type: 'value',
                splitLine: { lineStyle: { type: 'dashed', color: '#f0f0f0' } },
                axisLabel: { color: '#7f8c8d' }
            },
            series: [
                { 
                    name: 'Planlanan', 
                    type: 'line', 
                    smooth: true, 
                    symbol: 'none',
                    data: trendPlanned, 
                    lineStyle: { color: '#bdc3c7', width: 2, type: 'dashed' },
                    itemStyle: { color: '#bdc3c7' },
                    areaStyle: { opacity: 0.1, color: '#bdc3c7' }
                },
                { 
                    name: 'Tamamlanan', 
                    type: 'line', 
                    smooth: true, 
                    showSymbol: false,
                    symbolSize: 8,
                    data: trendCompleted, 
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(142, 68, 173, 0.6)' },
                            { offset: 1, color: 'rgba(142, 68, 173, 0.0)' }
                        ])
                    }, 
                    itemStyle: { color: '#8e44ad', borderWidth: 2, borderColor: '#fff' }, 
                    lineStyle: { color: '#8e44ad', width: 4, shadowColor: 'rgba(142, 68, 173, 0.3)', shadowBlur: 10 }
                }
            ]
        });

        // 2. Durum Dağılım Grafiği (Donut)
        const statusChart = echarts.init(document.getElementById('statusChart'));
        const statusNames = {
            'olusturuldu': 'Oluşturuldu',
            'uretimde': 'Üretimde',
            'tamamlandi': 'Tamamlandı',
            'iptal': 'İptal'
        };
        const statusColors = {
            'olusturuldu': '#54a0ff', // Canlı Mavi
            'uretimde': '#feca57',    // Sıcak Sarı
            'tamamlandi': '#1dd1a1',  // Canlı Yeşil
            'iptal': '#ff6b6b'        // Pastel Kırmızı
        };

        // PHP'den ham veriyi al
        const rawStatusData = <?php echo !empty($reportData['status_distribution']) ? json_encode($reportData['status_distribution']) : '[]'; ?>;

        // Veriyi işle ve renkleri ata
        const statusData = rawStatusData.map(item => {
            // Durum metnini temizle (küçük harf, boşluksuz)
            const statusKey = item.durum ? item.durum.trim().toLowerCase() : '';
            
            return {
                value: item.count,
                name: statusNames[statusKey] || item.durum,
                itemStyle: {
                    color: statusColors[statusKey] || '#95a5a6' // Eşleşme yoksa varsayılan gri
                }
            };
        });
        
        // Toplam sayıyı hesapla
        const totalStatus = statusData.reduce((sum, item) => sum + parseInt(item.value), 0);

        statusChart.setOption({
            tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
            legend: { bottom: '0%', left: 'center', icon: 'circle' },
            title: {
                text: totalStatus.toString(),
                subtext: 'Toplam',
                left: 'center',
                top: '43%',
                textStyle: { fontSize: 28, fontWeight: 'bold', color: '#2c3e50' },
                subtextStyle: { fontSize: 14, color: '#7f8c8d' }
            },
            series: [{
                type: 'pie',
                radius: ['55%', '75%'],
                center: ['50%', '50%'],
                avoidLabelOverlap: false,
                itemStyle: { borderRadius: 8, borderColor: '#fff', borderWidth: 3 },
                label: { show: false },
                emphasis: { 
                    label: { show: false },
                    scale: true,
                    scaleSize: 10
                },
                data: statusData
            }]
        });

        // 3. Üretim Hızı Grafiği
        const velocityChart = echarts.init(document.getElementById('velocityChart'));
        const velocityDates = <?php echo !empty($reportData['production_velocity']) ? json_encode(array_column($reportData['production_velocity'], 'date')) : '[]'; ?>;
        const velocityCounts = <?php echo !empty($reportData['production_velocity']) ? json_encode(array_column($reportData['production_velocity'], 'count')) : '[]'; ?>;
        
        velocityChart.setOption({
            ...commonOptions,
            tooltip: { trigger: 'axis' },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: velocityDates,
                axisLine: { lineStyle: { color: '#e0e0e0' } },
                axisLabel: { color: '#7f8c8d' }
            },
            yAxis: {
                type: 'value',
                splitLine: { lineStyle: { type: 'dashed', color: '#f0f0f0' } }
            },
            series: [{
                data: velocityCounts,
                type: 'line',
                smooth: true,
                symbolSize: 8,
                lineStyle: { width: 4, color: '#f1c40f', shadowColor: 'rgba(241, 196, 15, 0.3)', shadowBlur: 10 },
                itemStyle: { color: '#f1c40f', borderWidth: 2, borderColor: '#fff' },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(241, 196, 15, 0.5)' },
                        { offset: 1, color: 'rgba(241, 196, 15, 0.0)' }
                    ])
                }
            }]
        });

        // 4. İş Merkezi Verimlilik Grafiği
        const wcEfficiencyChart = echarts.init(document.getElementById('wcEfficiencyChart'));
        const wcNames = <?php echo !empty($reportData['work_center_efficiency']) ? json_encode(array_column($reportData['work_center_efficiency'], 'work_center')) : '[]'; ?>;
        const wcEfficiency = <?php echo !empty($reportData['work_center_efficiency']) ? json_encode(array_map(function($item){ return round($item['efficiency'], 1); }, $reportData['work_center_efficiency'])) : '[]'; ?>;
        
        wcEfficiencyChart.setOption({
            ...commonOptions,
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            grid: { ...commonOptions.grid, left: '3%', right: '10%', bottom: '3%', containLabel: true },
            xAxis: { 
                type: 'value', 
                boundaryGap: [0, 0.01],
                splitLine: { lineStyle: { type: 'dashed', color: '#f0f0f0' } }
            },
            yAxis: { 
                type: 'category', 
                data: wcNames,
                axisLine: { show: false },
                axisTick: { show: false },
                axisLabel: { color: '#2c3e50', fontWeight: 500 }
            },
            series: [{
                type: 'bar',
                barWidth: '20px',
                data: wcEfficiency,
                itemStyle: { 
                    color: createHorizontalGradient(themeColors.primary),
                    borderRadius: [0, 10, 10, 0]
                },
                label: { 
                    show: true, 
                    position: 'right', 
                    formatter: '{c}%',
                    color: '#8e44ad',
                    fontWeight: 'bold'
                }
            }]
        });

        // 5. En Çok Üretilen Ürünler Grafiği
        const topProductsChart = echarts.init(document.getElementById('topProductsChart'));
        const productNames = <?php echo !empty($reportData['top_products']) ? json_encode(array_column($reportData['top_products'], 'urun_ismi')) : '[]'; ?>;
        const productValues = <?php echo !empty($reportData['top_products']) ? json_encode(array_column($reportData['top_products'], 'total_produced')) : '[]'; ?>;
        
        topProductsChart.setOption({
            ...commonOptions,
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            grid: { ...commonOptions.grid, left: '3%', right: '10%', bottom: '3%', containLabel: true },
            xAxis: { 
                type: 'value', 
                splitLine: { show: false } 
            },
            yAxis: { 
                type: 'category', 
                data: productNames,
                axisLine: { show: false },
                axisTick: { show: false },
                axisLabel: { color: '#2c3e50', fontWeight: 500, width: 100, overflow: 'truncate' }
            },
            series: [{
                data: productValues,
                type: 'bar',
                barWidth: '20px',
                itemStyle: {
                    color: createHorizontalGradient(themeColors.secondary),
                    borderRadius: [0, 10, 10, 0]
                },
                label: { 
                    show: true, 
                    position: 'right', 
                    color: '#d4ac0d', 
                    formatter: '{c} adet',
                    fontWeight: 'bold'
                }
            }]
        });

        // Resize all charts on window resize
        window.addEventListener('resize', () => {
            trendChart.resize();
            statusChart.resize();
            velocityChart.resize();
            wcEfficiencyChart.resize();
            topProductsChart.resize();
        });
    });
    </script>

</body>
</html>

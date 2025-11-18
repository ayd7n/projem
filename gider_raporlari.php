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
if (!yetkisi_var('page:view:gider_raporlari')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Fetch gider data from database
$query = "SELECT * FROM `gider_yonetimi` ORDER BY `tarih` ASC";
$result = mysqli_query($connection, $query);
$giderler = array();
while ($row = mysqli_fetch_assoc($result)) {
    $giderler[] = $row;
}

// Prepare data for chart
$aylik_veriler = array();

foreach ($giderler as $gider) {
    $tarih = date('Y-m', strtotime($gider['tarih']));
    if (!isset($aylik_veriler[$tarih])) {
        $aylik_veriler[$tarih] = floatval($gider['tutar']);
    } else {
        $aylik_veriler[$tarih] += floatval($gider['tutar']);
    }
}

// Ayları zaman sırasına göre sırala (eski tarihler başta)
ksort($aylik_veriler);

$aylar = array_keys($aylik_veriler);
$tutarlar = array_values($aylik_veriler);

// Prepare data for category chart
$kategoriler = array();

foreach ($giderler as $gider) {
    $kategori = $gider['kategori'];
    $tutar = floatval($gider['tutar']);

    if (array_key_exists($kategori, $kategoriler)) {
        $kategoriler[$kategori] += $tutar;
    } else {
        $kategoriler[$kategori] = $tutar;
    }
}

// Kategorileri alfabetik sıraya göre sırala
ksort($kategoriler);

// Mevcut ayı al
$currentMonth = date('Y-m');

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gider Raporları - Parfüm ERP</title>
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
        .chart-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            height: 350px;
            display: flex;
            flex-direction: column;
            margin-bottom: 0.75rem;
        }
        .row {
            margin: 0 0.25rem 0.5rem 0.25rem;
        }
        [class*="col-"] {
            padding: 0.25rem;
        }
        .chart-container {
            flex: 1;
            position: relative;
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
            <h1><i class="fas fa-file-invoice-dollar"></i> Gider Raporları</h1>
            <p class="text-muted">Gider yönetimi verilerini analiz edin ve görselleştirin.</p>
        </div>

        <!-- Açıklama metinleri -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-info-circle"></i> Grafikler Hakkında</h6>
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="d-flex align-items-center mr-4 mb-2">
                                <i class="fas fa-chart-line text-primary mr-2"></i>
                                <span><strong>Aylık Gider Trendi:</strong> Zaman içindeki gider değişimi</span>
                            </div>
                            <div class="d-flex align-items-center mr-4 mb-2">
                                <i class="fas fa-chart-pie text-success mr-2"></i>
                                <span><strong>Kategori Dağılımı:</strong> Seçilen tarihe göre gider dağılımı</span>
                            </div>
                            <div class="d-flex align-items-center mr-4 mb-2">
                                <i class="fas fa-layer-group text-warning mr-2"></i>
                                <span><strong>Anormal Artışlar:</strong> Diğer aylara göre fark eden giderler</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                                <span><strong>Anormal:</strong> %2 üzerinde fark gösteren kategoriler</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    <div>
                        <label for="aySecici" class="form-label">Aylar:</label>
                        <select class="form-control form-control-sm" id="aySecici" style="width: auto; display: inline-block;">
                            <option value="all">Tümü</option>
                            <?php
                            foreach ($aylar as $ay) {
                                $selected = ($ay === $currentMonth) ? 'selected' : '';
                                echo "<option value='$ay' $selected>$ay</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="chart-card">
                    <div class="chart-container">
                        <div id="aylikGiderlerChart" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="chart-card">
                    <div class="chart-container">
                        <div id="kategoriGiderlerChart" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="chart-card">
                    <div class="chart-container">
                        <div id="anormalKategorilerChart" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // ECharts için veri hazırlama - giderler array'ini JavaScript'e aktarma
        var giderler = <?php echo json_encode($giderler); ?>;
        var aylar = <?php echo json_encode($aylar); ?>;
        var tutarlar = <?php echo json_encode($tutarlar); ?>;
        var kategoriAdlari = <?php echo json_encode(array_keys($kategoriler)); ?>;
        var kategoriTutarlari = <?php echo json_encode(array_values($kategoriler)); ?>;

        // Tarih filtreleme fonksiyonları
        var currentMonth = '<?php echo $currentMonth; ?>'; // PHP'den gelen değer

        // Aylık Giderler Chart (Bar) - sabit, filtrelemeden etkilenmez
        var aylikGiderlerChart = echarts.init(document.getElementById('aylikGiderlerChart'));

        var aylikOption = {
            title: {
                text: 'Aylık Giderler',
                left: 'center',
                textStyle: {
                    fontSize: 14,
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                trigger: 'axis',
                formatter: function(params) {
                    var param = params[0];
                    return param.name + '<br/>' +
                           param.seriesName + ': <strong>' + param.value.toLocaleString('tr-TR', {style: 'currency', currency: 'TRY'}) + '</strong>';
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
                data: aylar,
                axisTick: {
                    alignWithLabel: true
                }
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: function(value) {
                        return value.toLocaleString('tr-TR', {maximumFractionDigits: 0});
                    }
                }
            },
            series: [{
                name: 'Toplam Gider (₺)',
                type: 'line',
                data: tutarlar,
                itemStyle: {
                    color: '#7c2a99'
                },
                lineStyle: {
                    color: '#7c2a99'
                },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(124, 42, 153, 0.3)' },
                        { offset: 1, color: 'rgba(74, 14, 99, 0.1)' }
                    ])
                },
                smooth: true
            }]
        };

        aylikGiderlerChart.setOption(aylikOption);

        // Kategori Giderler Chart (Donut/Pie) - filtrelemeden etkilenir
        var kategoriGiderlerChart = echarts.init(document.getElementById('kategoriGiderlerChart'));

        // Kategorilere göre veri filtreleme fonksiyonu
        function getKategoriVerileri(seciliAy) {
            var kategoriVerileri = {};

            // Giderleri filtrele
            var filtrelenmisGiderler = seciliAy === 'all' ?
                giderler :
                giderler.filter(gider => gider.tarih.startsWith(seciliAy));

            // Kategorilere göre toplam hesapla
            filtrelenmisGiderler.forEach(function(gider) {
                var kategori = gider.kategori;
                var tutar = parseFloat(gider.tutar);

                if (kategoriVerileri[kategori]) {
                    kategoriVerileri[kategori] += tutar;
                } else {
                    kategoriVerileri[kategori] = tutar;
                }
            });

            return kategoriVerileri;
        }

        // Tüm aylar için anormal kategori artışlarını hesaplayan fonksiyon
        function getAllAnormalKategoriler() {
            var tumAylarKategoriVerileri = {};

            // Her ay için kategori verilerini topla
            aylar.forEach(function(ay) {
                tumAylarKategoriVerileri[ay] = {};

                // Bu aydaki giderleri filtrele
                var ayinGiderleri = giderler.filter(gider => gider.tarih.startsWith(ay));

                // Bu aydaki kategorilere göre toplam hesapla
                ayinGiderleri.forEach(function(gider) {
                    var kategori = gider.kategori;
                    var tutar = parseFloat(gider.tutar);

                    if (tumAylarKategoriVerileri[ay][kategori]) {
                        tumAylarKategoriVerileri[ay][kategori] += tutar;
                    } else {
                        tumAylarKategoriVerileri[ay][kategori] = tutar;
                    }
                });
            });

            // Tüm ayların toplam verileri
            var tumKategoriVerileri = getKategoriVerileri('all');
            var tumAylarToplam = 0;
            for (var kategori in tumKategoriVerileri) {
                tumAylarToplam += tumKategoriVerileri[kategori];
            }

            // Her kategorinin genel ortalama oranını hesapla
            var genelOranlar = {};
            for (var kategori in tumKategoriVerileri) {
                genelOranlar[kategori] = tumAylarToplam > 0 ? (tumKategoriVerileri[kategori] / tumAylarToplam) * 100 : 0;
            }

            // Her ay için anormal kategorileri hesapla
            var tumAylarAnormalKategoriler = {};
            aylar.forEach(function(ay) {
                tumAylarAnormalKategoriler[ay] = {};

                var ayToplam = 0;
                for (var kategori in tumAylarKategoriVerileri[ay]) {
                    ayToplam += tumAylarKategoriVerileri[ay][kategori];
                }

                for (var kategori in tumAylarKategoriVerileri[ay]) {
                    var ayOrani = ayToplam > 0 ? (tumAylarKategoriVerileri[ay][kategori] / ayToplam) * 100 : 0;
                    var genelOran = genelOranlar[kategori] || 0;
                    var fark = ayOrani - genelOran;

                    // Sadece fark pozitifse ve %2'den fazlaysa ekle
                    if (fark > 2) {
                        tumAylarAnormalKategoriler[ay][kategori] = {
                            ayOrani: ayOrani,
                            genelOran: genelOran,
                            fark: fark
                        };
                    }
                }
            });

            return tumAylarAnormalKategoriler;
        }

        // Başlangıçta tüm verilerle pasta grafiğini oluştur
        var tumKategoriVerileri = getKategoriVerileri('all');
        var kategoriOption = {
            title: {
                text: 'Kategori Bazında Giderler',
                left: 'center',
                textStyle: {
                    fontSize: 14,
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b}: {c} ({d}%)'
            },
            legend: {
                orient: 'vertical',
                left: 'right'
            },
            series: [{
                name: 'Gider Dağılımı',
                type: 'pie',
                radius: ['40%', '70%'], // Donut chart için
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 5,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                label: {
                    show: true,
                    formatter: '{b}: {d}%'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: '14',
                        fontWeight: 'bold'
                    }
                },
                data: Object.keys(tumKategoriVerileri).map(kategori => ({
                    value: tumKategoriVerileri[kategori],
                    name: kategori
                }))
            }]
        };

        kategoriGiderlerChart.setOption(kategoriOption);


        // Dropdown filtreleme işlevselliği - pasta grafiğini ve anormal kategoriler grafiğini etkiler
        $('#aySecici').on('change', function() {
            var selectedMonth = $(this).val();

            // Pasta grafiğini güncelle
            var yeniKategoriVerileri = getKategoriVerileri(selectedMonth);
            var yeniKategoriOption = {
                series: [{
                    name: 'Gider Dağılımı',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 5,
                        borderColor: '#fff',
                        borderWidth: 1
                    },
                    label: {
                        show: true,
                        formatter: '{b}: {d}%'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '14',
                            fontWeight: 'bold'
                        }
                    },
                    data: Object.keys(yeniKategoriVerileri).map(kategori => ({
                        value: yeniKategoriVerileri[kategori],
                        name: kategori
                    }))
                }]
            };

            kategoriGiderlerChart.setOption(yeniKategoriOption, true);

        });

        // Anormal Kategoriler Stacked Bar Chart
        var anormalKategorilerChart;
        $(document).ready(function() {
            setTimeout(function() {
                anormalKategorilerChart = echarts.init(document.getElementById('anormalKategorilerChart'));

                // Tüm aylar için anormal kategorileri hesapla
                var tumAylarAnormalKategoriler = getAllAnormalKategoriler();

                // Gerekli veri yapılarını oluştur
                var tumKategoriler = new Set();

                // Tüm kategorileri topla
                for (var ay in tumAylarAnormalKategoriler) {
                    for (var kategori in tumAylarAnormalKategoriler[ay]) {
                        tumKategoriler.add(kategori);
                    }
                }
                var kategoriList = Array.from(tumKategoriler);

                // Seri verilerini oluştur
                var seriesData = kategoriList.map(function(kategori) {
                    return {
                        name: kategori,
                        type: 'bar',
                        stack: 'anormal',
                        data: aylar.map(function(ay) {
                            if (tumAylarAnormalKategoriler[ay] && tumAylarAnormalKategoriler[ay][kategori]) {
                                return tumAylarAnormalKategoriler[ay][kategori].fark;
                            } else {
                                return 0;
                            }
                        })
                    };
                });

                var anormalKategorilerOption = {
                    title: {
                        text: 'Aylık Anormal Kategori Artışları',
                        left: 'center',
                        textStyle: {
                            fontSize: 14,
                            fontWeight: 'bold'
                        }
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        },
                        formatter: function(params) {
                            var result = params[0].axisValue + ' ayı<br/>';
                            params.forEach(function(item) {
                                if (item.value > 0) {
                                    result += item.seriesName + ': <strong>+' + item.value.toFixed(2) + '%</strong><br/>';
                                }
                            });
                            return result;
                        }
                    },
                    legend: {
                        data: kategoriList,
                        type: 'scroll',
                        orient: 'horizontal',
                        bottom: '5%'
                    },
                    grid: {
                        left: '5%',
                        right: '5%',
                        bottom: '20%',
                        top: '10%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: aylar,
                        axisLabel: {
                            rotate: 45,
                            fontSize: 10,
                            interval: 0,
                            margin: 15
                        }
                    },
                    yAxis: {
                        type: 'value',
                        name: 'Fark (%)',
                        axisLabel: {
                            formatter: '{value}%'
                        }
                    },
                    series: seriesData
                };

                anormalKategorilerChart.setOption(anormalKategorilerOption);
            }, 100); // Küçük bir gecikme ile başlat
        });

        // Pencerelerin yeniden boyutlandırılmasına uyum sağlamak için
        $(document).ready(function() {
            window.addEventListener('resize', function() {
                aylikGiderlerChart.resize();
                kategoriGiderlerChart.resize();
                if (anormalKategorilerChart) {
                    anormalKategorilerChart.resize();
                }
            });
        });
    </script>
</body>
</html>
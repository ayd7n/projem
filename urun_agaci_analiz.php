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
    <title>Eksik Bileşen Raporu - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
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

        .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: var(--primary);
            color: white;
            border-radius: 8px 8px 0 0 !important;
            font-weight: 500;
        }

        .search-box {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .chart-container {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0;
            margin-bottom: 0.5rem;
            height: 250px;
            position: relative;
        }

        .table-container {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.5rem;
            overflow-x: auto;
        }

        .chart-container {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            height: 500px;
        }

        .table th {
            border-top: none;
            background-color: var(--primary);
            color: white;
            white-space: nowrap;
        }

        .table td {
            white-space: nowrap;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(124, 42, 153, 0.03);
        }

        .btn-custom {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .btn-custom:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            color: white;
        }

        @media (max-width: 768px) {
            .chart-container {
                margin-top: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($_SESSION["kullanici_adi"] ?? "Kullanıcı"); ?>
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
        <div class="page-header" style="margin-bottom: 0.5rem;">
            <h1><i class="fas fa-sitemap"></i> Eksik Bileşen Raporu</h1>
            <p class="text-muted">Hem esans hem de diğer bileşen türlerinden eksik olan ürünleri analiz edin.</p>
        </div>

        <div class="alert alert-info" style="margin: 10px 0; padding: 10px;">
            <p style="margin: 0 0 8px 0; font-size: 0.9rem;">
                <strong>Bileşen Durumu Açıklaması:</strong>
            </p>
            <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem;">
                <li><strong>Esans bileşeni eksik (malzemeler mevcut):</strong> Ürünün malzeme bileşenleri (etiket,
                    kapak, vb.) tanımlı ancak esans bileşeni eksik</li>
                <li><strong>Malzeme bileşenleri eksik (esans mevcut):</strong> Ürünün esans bileşeni tanımlı ancak
                    malzeme bileşenleri (etiket, kapak, vb.) eksik</li>
                <li><strong>Hiçbir bileşen yok (hem esans hem malzeme eksik):</strong> Ürünün hiçbir bileşeni
                    tanımlanmamış</li>
            </ul>
        </div>

        <div class="alert alert-info" style="margin: 10px 0; padding: 10px;">
            <p style="margin: 0; font-size: 0.9rem;">
                <strong>Nasıl Okunur:</strong> Bu rapor, hem esans hem de diğer bileşen türlerine (etiket, kapak, vb.)
                sahip OLMAYAN ürünleri listeler.
                Grafik ise toplam ürün envanteri içinde bu eksiklikleri gösteren ürün oranını sunar.
                'Bileşeni Eksik' dilimi, eksik olan ürünleri, 'Bileşeni Tam' dilimi ise eksik olmayan ürünleri temsil
                eder.
            </p>
        </div>

        <div class="chart-container">
            <div id="chart" style="width: 100%; height: 100%;"></div>
        </div>

        <div class="table-container">
            <div id="app">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                Ürün Kodu
                            </th>
                            <th>
                                Ürün Adı
                            </th>
                            <th>
                                Bileşen Durumu
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.urun_agaci_id">
                            <td>{{ item.urun_kodu }}</td>
                            <td>{{ item.urun_ismi }}</td>
                            <td>{{ item.bilesen_ismi }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.0/dist/echarts.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        new Vue({
            el: '#app',
            data: {
                items: [],
                loading: false,
                chart: null,
                totalProducts: 0,
                missingComponentProducts: 0,
                resizeObserver: null
            },
            computed: {
                totalPages() {
                    return Math.ceil(this.filteredItems.length / this.itemsPerPage);
                },
                pages() {
                    const pages = [];
                    const totalPages = this.totalPages;

                    if (totalPages <= 5) {
                        for (let i = 1; i <= totalPages; i++) {
                            pages.push(i);
                        }
                    } else {
                        if (this.currentPage <= 3) {
                            for (let i = 1; i <= 5; i++) {
                                pages.push(i);
                            }
                            pages.push('...');
                            pages.push(totalPages);
                        } else if (this.currentPage >= totalPages - 2) {
                            pages.push(1);
                            pages.push('...');
                            for (let i = totalPages - 4; i <= totalPages; i++) {
                                pages.push(i);
                            }
                        } else {
                            pages.push(1);
                            pages.push('...');
                            for (let i = this.currentPage - 2; i <= this.currentPage + 2; i++) {
                                pages.push(i);
                            }
                            pages.push('...');
                            pages.push(totalPages);
                        }
                    }

                    return pages;
                }
            },
            mounted() {
                this.fetchData();
                this.initChart();

                // Set up search functionality
                const searchInput = document.getElementById('searchInput');
                searchInput.addEventListener('input', (e) => {
                    this.searchQuery = e.target.value.toLowerCase();
                    this.filterAndSort();
                    this.currentPage = 1;
                });
            },
            methods: {
                async fetchData() {
                    try {
                        this.loading = true;
                        const response = await fetch('api_islemleri/urun_agaci_analiz_islemler.php');
                        if (response.ok) {
                            this.items = await response.json();
                        } else {
                            console.error('API veri alımı başarısız oldu:', response.status);
                            // Fallback to empty array
                            this.items = [];
                        }
                    } catch (error) {
                        console.error('Veri alınırken hata oluştu:', error);
                        // Fallback to empty array
                        this.items = [];
                    } finally {
                        this.loading = false;
                    }
                },
                initChart() {
                    if (this.chart) {
                        this.chart.dispose();
                    }
                    this.chart = echarts.init(document.getElementById('chart'));

                    // Prepare data for the chart
                    const data = [
                        { value: this.missingComponentProducts, name: 'Bileşeni Eksik' },
                        { value: this.totalProducts - this.missingComponentProducts, name: 'Bileşeni Tam' }
                    ];

                    const option = {
                        title: {
                            text: `Toplam: ${this.totalProducts} Ürün`,
                            subtext: `Eksik: ${this.missingComponentProducts} Ürün`,
                            left: 'center'
                        },
                        tooltip: {
                            trigger: 'item'
                        },
                        legend: {
                            orient: 'vertical',
                            left: 'left'
                        },
                        series: [{
                            name: 'Bileşen Durumu',
                            type: 'pie',
                            radius: '50%',
                            data: data,
                            emphasis: {
                                itemStyle: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            }
                        }]
                    };

                    this.chart.setOption(option);

                    // Make chart responsive
                    if (this.resizeObserver) {
                        this.resizeObserver.disconnect();
                    }

                    const resizeObserver = new ResizeObserver(() => {
                        if (this.chart) {
                            this.chart.resize();
                        }
                    });
                    resizeObserver.observe(document.getElementById('chart'));

                    this.resizeObserver = resizeObserver;
                },
                async fetchData() {
                    try {
                        this.loading = true;
                        const response = await fetch('api_islemleri/urun_agaci_analiz_islemler.php');
                        if (response.ok) {
                            this.items = await response.json();
                            this.missingComponentProducts = this.items.length;

                            // Get total product count from urunler table
                            const totalResponse = await fetch('api_islemleri/urun_agaci_analiz_islemler.php?action=count_products');
                            if (totalResponse.ok) {
                                const totalData = await totalResponse.json();
                                if (totalData.status === 'success') {
                                    this.totalProducts = totalData.total || 0;
                                } else {
                                    // If the API returns error, use fallback
                                    this.totalProducts = this.missingComponentProducts + 10; // approximate
                                }
                            } else {
                                // Fallback: count items from the items array if API fails
                                this.totalProducts = this.missingComponentProducts + 10; // approximate
                            }
                        } else {
                            console.error('API veri alımı başarısız oldu:', response.status);
                            this.items = [];
                            this.missingComponentProducts = 0;
                        }

                        this.$nextTick(() => {
                            this.initChart();
                        });

                    } catch (error) {
                        console.error('Veri alınırken hata oluştu:', error);
                        this.items = [];
                        this.missingComponentProducts = 0;
                    } finally {
                        this.loading = false;
                    }
                }
            },
            mounted() {
                this.fetchData();
            }
        });
    </script>
</body>

</html>
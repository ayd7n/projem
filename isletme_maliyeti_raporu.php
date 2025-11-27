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

// Calculate date range for display
$startDate = date('d.m.Y', strtotime('-1 month'));
$endDate = date('d.m.Y');
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşletme Maliyeti Analizi - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
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
        
        @media (min-width: 768px) {
            .main-content {
                padding: 2rem;
            }
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .stat-card .icon {
            font-size: 1.5rem;
            opacity: 0.8;
        }

        .stat-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        @media (min-width: 768px) {
            .stat-card h3 {
                font-size: 1.5rem;
            }
            .stat-card .icon {
                font-size: 2rem;
            }
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .table td {
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
            font-weight: 600;
            padding: 0.5em 0.8em;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: 600;
            padding: 0.5em 0.8em;
        }
        
        .explanation-list li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top"
        style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700; font-size: 1.2rem;" href="navigation.php"><i
                    class="fas fa-spa"></i> IDO KOZMETIK</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="raporlar.php">Raporlara Dön</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="navigation.php">Ana Sayfa</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="app" class="main-content">
        <div class="page-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
                <div class="mb-3 mb-md-0">
                    <h1><i class="fas fa-calculator"></i> İşletme Maliyeti Analizi</h1>
                    <p class="text-muted mb-0 small">Son 1 aylık verilere göre işletme maliyeti ve ürün kârlılık analizi.</p>
                </div>
                <button @click="refreshData" class="btn btn-sm btn-outline-primary"><i class="fas fa-sync-alt"></i> Yenile</button>
            </div>
            
            <!-- Calculation Logic Info -->
            <div class="alert alert-info border-0 shadow-sm">
                <h6 class="alert-heading font-weight-bold small"><i class="fas fa-info-circle"></i> Hesaplama Mantığı ve Detaylar</h6>
                <hr class="my-2">
                <div class="row small">
                    <div class="col-md-6">
                        <ul class="mb-0 pl-3 explanation-list">
                            <li><strong>Analiz Dönemi (Son 1 Ay):</strong> <span class="text-dark font-weight-bold"><?php echo $startDate; ?> - <?php echo $endDate; ?></span> tarihleri arası.</li>
                            <li><strong>Toplam İşletme Gideri:</strong> Bu tarih aralığındaki, "Malzeme Gideri" kategorisi <u>hariç</u> tüm giderlerin toplamıdır.</li>
                            <li><strong>Toplam Üretim:</strong> Bu tarih aralığındaki <u>montajı tamamlanan (biten)</u> toplam ürün adedidir.</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0 pl-3 explanation-list">
                            <li><strong>Birim İşletme Maliyeti:</strong> (Toplam İşletme Gideri / Toplam Üretim) formülü ile hesaplanır.</li>
                            <li><strong>Toplam Tahmini Maliyet:</strong> Ürünün reçetesindeki malzeme maliyeti (Teorik) + Birim İşletme Maliyeti.</li>
                            <li><strong>Tahmini Kâr/Zarar:</strong> Satış Fiyatı - Toplam Tahmini Maliyet.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small" style="opacity: 0.8;">Toplam İşletme Gideri (Son 1 Ay)
                            </h6>
                            <h3>{{ formatCurrency(metrics.total_expenses) }}</h3>
                        </div>
                        <i class="fas fa-file-invoice-dollar icon"></i>
                    </div>
                    <small style="opacity: 0.7; font-size: 0.75rem;">Malzeme giderleri hariç</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-2" style="background: linear-gradient(135deg, #2193b0, #6dd5ed);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small" style="opacity: 0.8;">Toplam Üretim (Son 1 Ay)</h6>
                            <h3>{{ metrics.total_produced }} <small style="font-size: 0.8rem;">Adet</small></h3>
                        </div>
                        <i class="fas fa-industry icon"></i>
                    </div>
                    <small style="opacity: 0.7; font-size: 0.75rem;">Tamamlanan montaj iş emirleri</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-2" style="background: linear-gradient(135deg, #ff9966, #ff5e62);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small" style="opacity: 0.8;">Birim İşletme Maliyeti</h6>
                            <h3>{{ formatCurrency(metrics.unit_operating_cost) }}</h3>
                        </div>
                        <i class="fas fa-tags icon"></i>
                    </div>
                    <small style="opacity: 0.7; font-size: 0.75rem;">Ürün başına düşen ek maliyet</small>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-list"></i> Ürün Kârlılık Tablosu</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Ürün Kodu</th>
                                <th>Ürün İsmi</th>
                                <th>Satış Fiyatı</th>
                                <th>Teorik Maliyet</th>
                                <th>İşletme Maliyeti Payı</th>
                                <th>Toplam Tahmini Maliyet</th>
                                <th>Tahmini Kâr/Zarar</th>
                                <th>Kâr/Zarar (%)</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="9" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-for="product in products" :key="product.urun_kodu">
                                <td>{{ product.urun_kodu }}</td>
                                <td><strong>{{ product.urun_ismi }}</strong></td>
                                <td>{{ formatCurrency(product.satis_fiyati) }}</td>
                                <td>{{ formatCurrency(product.teorik_maliyet || 0) }}</td>
                                <td>{{ formatCurrency(metrics.unit_operating_cost) }}</td>
                                <td>{{ formatCurrency(calculateTotalCost(product)) }}</td>
                                <td :class="getProfitClass(product)">{{ formatCurrency(calculateProfit(product)) }}</td>
                                <td :class="getProfitClass(product)">{{ calculateProfitMargin(product) }}</td>
                                <td>
                                    <span v-if="calculateProfit(product) > 0" class="badge badge-success">Kârlı</span>
                                    <span v-else class="badge badge-danger">Zarar</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    loading: false,
                    metrics: {
                        total_expenses: 0,
                        total_produced: 0,
                        unit_operating_cost: 0
                    },
                    products: []
                }
            },
            methods: {
                refreshData() {
                    this.loading = true;
                    Promise.all([
                        fetch('api_islemleri/isletme_maliyeti_islemler.php?action=get_metrics').then(r => r.json()),
                        fetch('api_islemleri/urunler_islemler.php?action=get_all_products').then(r => r.json())
                    ]).then(([metricsRes, productsRes]) => {
                        if (metricsRes.status === 'success') {
                            this.metrics = metricsRes.data;
                        }
                        if (productsRes.status === 'success') {
                            this.products = productsRes.data;
                            console.log('Products loaded:', this.products);
                        }
                        this.loading = false;
                    }).catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
                },
                formatCurrency(value) {
                    return parseFloat(value || 0).toLocaleString('tr-TR', {
                        style: 'currency',
                        currency: 'TRY'
                    });
                },
                calculateTotalCost(product) {
                    return parseFloat(product.teorik_maliyet || 0) + parseFloat(this.metrics.unit_operating_cost);
                },
                calculateProfit(product) {
                    const totalCost = this.calculateTotalCost(product);
                    return parseFloat(product.satis_fiyati || 0) - totalCost;
                },
                calculateProfitMargin(product) {
                    const sellingPrice = parseFloat(product.satis_fiyati || 0);
                    if (sellingPrice <= 0) return '-';

                    const profit = this.calculateProfit(product);
                    const margin = (profit / sellingPrice) * 100;
                    return '%' + margin.toFixed(2);
                },
                getProfitClass(product) {
                    return this.calculateProfit(product) > 0 ? 'text-success font-weight-bold' : 'text-danger font-weight-bold';
                }
            },
            mounted() {
                this.refreshData();
            }
        });
        app.mount('#app');
    </script>
</body>

</html>
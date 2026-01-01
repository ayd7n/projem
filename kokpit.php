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
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Kokpit - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
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
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #fdf8f5;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }

        html {
            font-size: 15px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }

        .main-content {
            padding: 20px;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-primary);
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 15px 0 0;
            white-space: nowrap;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.825rem;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .table th {
            border-top: none;
            border-bottom: 2px solid var(--border-color);
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
        }

        .table th i {
            margin-right: 6px;
        }

        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
        }

        table th,
        table td {
            font-size: 0.8rem;
        }

        .badge-missing {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            margin: 2px;
            display: inline-block;
        }

        .badge-ok {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .badge-warning {
            background-color: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .status-complete {
            color: var(--success);
        }

        .status-incomplete {
            color: var(--danger);
        }

        .type-label {
            text-transform: capitalize;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top"
        style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php"><i
                    class="fas fa-spa"></i> IDO KOZMETIK</a>
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
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
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
    <div id="app" class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-tachometer-alt mr-2"></i>Kokpit</h1>
                <p>Ürünlerin bileşen durumlarını kontrol edin</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-start align-items-center py-2 px-3">
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <h2 class="mb-0"><i class="fas fa-list-check mr-2"></i>Ürün Bileşen Durumu</h2>
                    <!-- Arama Kutusu -->
                    <div class="input-group input-group-sm ml-3" style="width: auto; min-width: 200px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="padding: 4px 8px;"><i
                                    class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" v-model="search"
                            placeholder="Ürün ara..." style="font-size: 0.75rem; padding: 4px 8px;">
                    </div>
                    <!-- Filtre -->
                    <div class="input-group input-group-sm" style="width: auto;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"
                                style="background: var(--primary); color: white; border: none; font-size: 0.7rem; padding: 4px 8px;"><i
                                    class="fas fa-filter"></i></span>
                        </div>
                        <select class="form-control form-control-sm" v-model="filter"
                            style="border-radius: 0 6px 6px 0; min-width: 140px; font-size: 0.75rem; padding: 4px 8px;">
                            <option value="all">Tümü</option>
                            <option value="incomplete">Eksik Olanlar</option>
                            <option value="complete">Tam Olanlar</option>
                        </select>
                    </div>
                    <!-- Stat Kartları -->
                    <div class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #4a0e63, #7c2a99); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-boxes mr-1"></i>
                        <span style="font-weight: 600;">{{ totalProducts }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Ürün</span>
                    </div>
                    <div class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #dc3545, #c82333); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span style="font-weight: 600;">{{ incompleteCount }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Eksik</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-barcode"></i> Ürün Kodu</th>
                                <th><i class="fas fa-tag"></i> Ürün İsmi</th>
                                <th><i class="fas fa-info-circle"></i> Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="3" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="filteredProducts.length === 0">
                                <td colspan="3" class="text-center p-4">Kayıt bulunamadı.</td>
                            </tr>
                            <tr v-for="product in filteredProducts" :key="product.urun_kodu">
                                <td>{{ product.urun_kodu }}</td>
                                <td><strong>{{ product.urun_ismi }}</strong></td>
                                <td>
                                    <template v-if="getProductIssues(product).length === 0">
                                        <span class="badge-ok">
                                            <i class="fas fa-check-circle"></i> Tüm bileşenler tanımlı
                                        </span>
                                    </template>
                                    <template v-else>
                                        <span v-for="(issue, index) in getProductIssues(product)" :key="index" class="badge-warning mr-1 mb-1">
                                            <i class="fas fa-exclamation-circle"></i> {{ issue }}
                                        </span>
                                    </template>
                                </td>
                            </tr>
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

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    products: [],
                    loading: false,
                    search: '',
                    filter: 'all'
                }
            },
            computed: {
                filteredProducts() {
                    let result = this.products;

                    // Filter by search term
                    if (this.search.trim()) {
                        const term = this.search.toLowerCase();
                        result = result.filter(p =>
                            p.urun_ismi.toLowerCase().includes(term) ||
                            String(p.urun_kodu).includes(term)
                        );
                    }

                    // Filter by status
                    if (this.filter === 'incomplete') {
                        result = result.filter(p =>
                            p.missing_types.length > 0 ||
                            !p.has_esans ||
                            p.esans_has_bom === false
                        );
                    } else if (this.filter === 'complete') {
                        result = result.filter(p =>
                            p.missing_types.length === 0 &&
                            p.has_esans &&
                            p.esans_has_bom === true
                        );
                    }

                    return result;
                },
                totalProducts() {
                    return this.products.length;
                },
                incompleteCount() {
                    return this.products.filter(p =>
                        p.missing_types.length > 0 ||
                        !p.has_esans ||
                        p.esans_has_bom === false
                    ).length;
                }
            },
            methods: {
                getProductIssues(product) {
                    const issues = [];
                    
                    // Type labels for better readability
                    const typeLabels = {
                        'kutu': 'Kutu',
                        'takm': 'Takım',
                        'etiket': 'Etiket',
                        'paket': 'Paket',
                        'jelatin': 'Jelatin',
                        'esans': 'Esans'
                    };
                    
                    // Missing component types - consolidated message
                    if (product.missing_types.length > 0) {
                        const missingLabels = product.missing_types
                            .map(type => typeLabels[type] || type)
                            .join(', ');
                        issues.push(`Eksik bileşenler: ${missingLabels}`);
                    }
                    
                    // Essence status
                    if (!product.has_esans) {
                        // Already covered in missing_types if esans is missing
                    } else if (product.esans_has_bom === false) {
                        issues.push('Esans reçetesi oluşturulmamış');
                    }
                    
                    return issues;
                },
                loadProducts() {
                    this.loading = true;
                    fetch('api_islemleri/kokpit_islemler.php?action=get_product_bom_status')
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.products = response.data;
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            console.error('Error loading products:', error);
                            this.loading = false;
                        });
                }
            },
            mounted() {
                this.loadProducts();
            }
        });

        app.mount('#app');
    </script>
</body>

</html>

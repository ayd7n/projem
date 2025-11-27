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
if (!yetkisi_var('page:view:urunler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Calculate total products
$total_result = $connection->query("SELECT COUNT(*) as total FROM urunler");
$total_products = $total_result->fetch_assoc()['total'] ?? 0;

// Calculate products below critical stock level
$critical_result = $connection->query("SELECT COUNT(*) as total FROM urunler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0");
$critical_products = $critical_result->fetch_assoc()['total'] ?? 0;

// Calculate products above critical stock level
$above_critical_result = $connection->query("SELECT COUNT(*) as total FROM urunler WHERE stok_miktari > kritik_stok_seviyesi OR kritik_stok_seviyesi = 0");
$above_critical_products = $above_critical_result->fetch_assoc()['total'] ?? 0;

// Calculate percentage of products above critical stock level
$above_critical_percentage = $total_products > 0 ? round(($above_critical_products / $total_products) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Urun Yonetimi - Parfüm ERP</title>
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
            /* Deep Purple */
            --secondary: #7c2a99;
            /* Lighter Purple */
            --accent: #d4af37;
            /* Gold */
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #fdf8f5;
            /* Soft Cream */
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827;
            /* Dark Gray/Black */
            --text-secondary: #6b7280;
            /* Medium Gray */
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
            margin: 0;
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

        .btn-primary:hover {
            background-color: var(--secondary);
            box-shadow: 0 10px 20px rgba(74, 14, 99, 0.2);
        }

        .add-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            border-left: 5px solid;
        }

        .alert-danger {
            background-color: #fff5f5;
            color: #c53030;
            border-color: #f56565;
        }

        .alert-success {
            background-color: #f0fff4;
            color: #2f855a;
            border-color: #48bb78;
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

        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .actions .btn {
            padding: 6px 10px;
            border-radius: 18px;
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
                <h1>Urun Yonetimi</h1>
                <p>Urunleri ekleyin, duzenleyin ve yonetin</p>
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <?php if (yetkisi_var('action:urunler:create')): ?>
                    <button @click="openModal(null)" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Urun
                        Ekle</button>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon"
                                    style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 style="font-size: 1.5rem; margin: 0;">{{ totalProducts }}</h3>
                                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam Urun
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card" @click="toggleCriticalStockFilter"
                            style="cursor: pointer; transition: all 0.3s;">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon"
                                    style="background: var(--danger); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 style="font-size: 1.5rem; margin: 0;">{{ criticalProducts }}</h3>
                                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Kritik Stok
                                        Altı</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="mb-2 mb-md-0"><i class="fas fa-list"></i> Urun Listesi</h2>
                <div class="search-container w-100 w-md-25">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" v-model="search" @input="loadProducts(1)"
                            placeholder="Urun ara...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> Islemler</th>
                                <th><i class="fas fa-barcode"></i> Urun Kodu</th>
                                <th><i class="fas fa-tag"></i> Urun Ismi</th>
                                <th><i class="fas fa-warehouse"></i> Stok</th>
                                <th><i class="fas fa-exclamation-triangle"></i> Kritik Stok</th>
                                <th><i class="fas fa-ruler"></i> Birim</th>
                                <th><i class="fas fa-money-bill-wave"></i> Satis Fiyati</th>
                                <?php if (yetkisi_var('action:urunler:view_cost')): ?>
                                    <th>Teorik Maliyet (₺)</th>

                                <?php endif; ?>
                                <th><i class="fas fa-warehouse"></i> Depo</th>
                                <th><i class="fas fa-cube"></i> Raf</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="9" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="products.length === 0">
                                <td colspan="9" class="text-center p-4">Henuz kayitli urun bulunmuyor.</td>
                            </tr>
                            <tr v-for="product in products" :key="product.urun_kodu">
                                <td class="actions">
                                    <?php if (yetkisi_var('action:urunler:edit')): ?>
                                        <button @click="openModal(product)" class="btn btn-primary btn-sm"><i
                                                class="fas fa-edit"></i></button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:urunler:delete')): ?>
                                        <button @click="deleteProduct(product.urun_kodu)" class="btn btn-danger btn-sm"><i
                                                class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                                <td>{{ product.urun_kodu }}</td>
                                <td><strong>{{ product.urun_ismi }}</strong></td>
                                <td>
                                    <span :class="stockClass(product)">
                                        {{ product.stok_miktari }}
                                    </span>
                                </td>
                                <td>{{ product.kritik_stok_seviyesi }}</td>
                                <td>{{ product.birim }}</td>
                                <td>{{ formatCurrency(product.satis_fiyati) }}</td>
                                <?php if (yetkisi_var('action:urunler:view_cost')): ?>
                                    <td>{{ formatCurrency(product.teorik_maliyet || 0) }}</td>

                                <?php endif; ?>
                                <td>{{ product.depo }}</td>
                                <td>{{ product.raf }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0 w-100 w-md-auto">
                        <label for="recordsPerPage"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                        <select v-model="limit" @change="loadProducts(1)" class="form-control d-inline-block"
                            style="width: auto; margin-left: 8px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="d-flex flex-column flex-md-row align-items-center w-100 w-md-auto mt-2 mt-md-0">
                        <div class="pagination-info mr-0 mr-md-3 mb-2 mb-md-0">
                            <small class="text-muted">{{ paginationInfo }}</small>
                        </div>
                        <nav>
                            <ul class="pagination justify-content-center justify-content-md-end mb-0">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="loadProducts(currentPage - 1)"><i
                                            class="fas fa-chevron-left"></i> Previous</a>
                                </li>
                                <li v-if="currentPage > 3" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadProducts(1)">1</a>
                                </li>
                                <li v-if="currentPage > 4" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item"
                                    :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadProducts(page)">{{ page }}</a>
                                </li>
                                <li v-if="currentPage < totalPages - 3" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-if="currentPage < totalPages - 2" class_="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadProducts(totalPages)">{{
                                        totalPages }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="loadProducts(currentPage + 1)">Next <i
                                            class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form @submit.prevent="saveProduct">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title">{{ modal.title }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" v-model="modal.data.urun_kodu">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Urun Ismi *</label>
                                        <input type="text" class="form-control" v-model="modal.data.urun_ismi" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Stok Miktari</label>
                                        <input type="number" class="form-control" v-model="modal.data.stok_miktari"
                                            min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Birim</label>
                                        <select class="form-control" v-model="modal.data.birim">
                                            <option value="adet">Adet</option>
                                            <option value="kg">Kg</option>
                                            <option value="gr">Gr</option>
                                            <option value="lt">Lt</option>
                                            <option value="ml">Ml</option>
                                            <option value="mt">Mt</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Satis Fiyati (₺)</label>
                                        <input type="number" step="0.01" class="form-control"
                                            v-model="modal.data.satis_fiyati" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Kritik Stok Seviyesi</label>
                                        <input type="number" class="form-control"
                                            v-model="modal.data.kritik_stok_seviyesi" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Depo *</label>
                                        <select class="form-control" v-model="modal.data.depo"
                                            @change="loadRafList(modal.data.depo)" required>
                                            <option value="">Depo Secin</option>
                                            <option v-for="depo in depoList" :value="depo.depo_ismi">{{ depo.depo_ismi
                                                }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Raf *</label>
                                        <select class="form-control" v-model="modal.data.raf" required>
                                            <option value="">Once Depo Secin</option>
                                            <option v-for="raf in rafList" :value="raf.raf">{{ raf.raf }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label>Not Bilgisi</label>
                                <textarea class="form-control" v-model="modal.data.not_bilgisi" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                    class="fas fa-times"></i> Iptal</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    products: [],
                    loading: false,
                    alert: { message: '', type: '' },
                    search: '',
                    currentPage: 1,
                    totalPages: 1,
                    totalProducts: <?php echo $total_products; ?>,
                    criticalProducts: <?php echo $critical_products; ?>,
                    limit: 10,
                    modal: { title: '', data: {} },
                    depoList: [],
                    rafList: [],
                    depoList: [],
                    rafList: [],
                    criticalStockFilterEnabled: false,
                    operatingCostMetrics: null
                }
            },
            computed: {
                paginationInfo() {
                    if (this.totalPages <= 0 || this.totalProducts <= 0) {
                        return 'Gösterilecek kayıt yok';
                    }
                    const startRecord = (this.currentPage - 1) * this.limit + 1;
                    const endRecord = Math.min(this.currentPage * this.limit, this.totalProducts);
                    return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.totalProducts} kayıttan`;
                },
                pageNumbers() {
                    const pages = [];
                    const startPage = Math.max(1, this.currentPage - 2);
                    const endPage = Math.min(this.totalPages, this.currentPage + 2);

                    for (let i = startPage; i <= endPage; i++) {
                        pages.push(i);
                    }
                    return pages;
                }
            },
            methods: {
                showAlert(message, type) {
                    this.alert.message = message;
                    this.alert.type = type;
                    setTimeout(() => { this.alert.message = ''; }, 3000);
                },
                loadProducts(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    let url = `api_islemleri/urunler_islemler.php?action=get_products&page=${this.currentPage}&limit=${this.limit}&search=${this.search}`;
                    if (this.criticalStockFilterEnabled) {
                        url += '&filter=critical';
                    }
                    fetch(url)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.products = response.data;
                                this.totalPages = response.pagination.total_pages;
                                this.totalProducts = response.pagination.total_products;
                            } else {
                                this.showAlert('Urunler yüklenirken hata oluştu.', 'danger');
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            this.showAlert('Urunler yüklenirken bir hata oluştu.', 'danger');
                            this.loading = false;
                        });
                },
                openModal(product) {
                    this.rafList = []; // Clear raf list
                    if (product) {
                        this.modal.title = 'Urunu Duzenle';
                        this.modal.data = { ...product };
                        if (product.depo) {
                            this.loadRafList(product.depo, product.raf);
                        }
                    } else {
                        this.modal.title = 'Yeni Urun Ekle';
                        this.modal.data = { birim: 'adet' };
                    }
                    $('#productModal').modal('show');
                },
                saveProduct() {
                    let action = this.modal.data.urun_kodu ? 'update_product' : 'add_product';
                    let formData = new FormData();
                    for (let key in this.modal.data) {
                        formData.append(key, this.modal.data[key]);
                    }
                    formData.append('action', action);

                    fetch('api_islemleri/urunler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#productModal').modal('hide');
                                this.loadProducts(this.currentPage);
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                        });
                },
                deleteProduct(id) {
                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: "Bu urunu silmek istediğinizden emin misiniz?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Evet, sil!',
                        cancelButtonText: 'İptal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let formData = new FormData();
                            formData.append('action', 'delete_product');
                            formData.append('urun_kodu', id);

                            fetch('api_islemleri/urunler_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(response => {
                                    if (response.status === 'success') {
                                        this.showAlert(response.message, 'success');
                                        this.loadProducts(this.currentPage);
                                    } else {
                                        this.showAlert(response.message, 'danger');
                                    }
                                })
                                .catch(error => {
                                    this.showAlert('Silme işlemi sırasında bir hata oluştu.', 'danger');
                                });
                        }
                    })
                },
                loadDepoList() {
                    fetch('api_islemleri/urunler_islemler.php?action=get_depo_list')
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.depoList = response.data;
                            }
                        });
                },
                loadRafList(depo, selectedRaf = null) {
                    if (!depo) {
                        this.rafList = [];
                        return;
                    }
                    fetch(`api_islemleri/urunler_islemler.php?action=get_raf_list&depo=${encodeURIComponent(depo)}`)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.rafList = response.data;
                                if (selectedRaf) {
                                    this.modal.data.raf = selectedRaf;
                                }
                            }
                        });
                },
                formatCurrency(value) {
                    if (isNaN(value)) return '0,00 ₺';
                    return parseFloat(value).toLocaleString('tr-TR', { style: 'currency', currency: 'TRY' });
                },
                stockClass(product) {
                    const stok = parseFloat(product.stok_miktari);
                    const kritik = parseFloat(product.kritik_stok_seviyesi);
                    if (stok <= 0) return 'stock-low';
                    if (stok <= kritik) return 'stock-critical';
                    return 'stock-normal';
                },
                stockStatus(product) {
                    const stok = parseFloat(product.stok_miktari);
                    const kritik = parseFloat(product.kritik_stok_seviyesi);
                    if (stok == 0) {
                        return '<span style="color: #c62828; font-weight: bold;">Stokta Yok</span>';
                    } else if (stok <= kritik) {
                        return '<span style="color: #f57f17; font-weight: bold;">Kritik Seviye</span>';
                    } else {
                        return '<span style="color: #2e7d32; font-weight: bold;">Yeterli</span>';
                    }
                },
                toggleCriticalStockFilter() {
                    this.criticalStockFilterEnabled = !this.criticalStockFilterEnabled;
                    this.loadProducts(1);
                },
                loadOperatingCostMetrics() {
                    fetch('api_islemleri/isletme_maliyeti_islemler.php?action=get_metrics')
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.operatingCostMetrics = response.data;
                            }
                        });
                },
                calculateProfitability(product) {
                    if (!this.operatingCostMetrics || !product.teorik_maliyet) return '<span class="text-muted">-</span>';

                    const theoreticalCost = parseFloat(product.teorik_maliyet);
                    const operatingCost = parseFloat(this.operatingCostMetrics.unit_operating_cost);
                    const totalCost = theoreticalCost + operatingCost;
                    const sellingPrice = parseFloat(product.satis_fiyati);

                    // Avoid division by zero or invalid data
                    if (sellingPrice <= 0) return '<span class="text-muted">Fiyat Yok</span>';

                    const diff = sellingPrice - totalCost;

                    if (diff > 0) {
                        return `<span class="badge badge-success" title="Tahmini Kâr: ${this.formatCurrency(diff)}">Kârlı</span>`;
                    } else {
                        return `<span class="badge badge-danger" title="Tahmini Zarar: ${this.formatCurrency(Math.abs(diff))}">Zarar</span>`;
                    }
                }
            },
            mounted() {
                this.loadProducts();
                this.loadDepoList();
                this.loadOperatingCostMetrics();
            }
        });
        app.mount('#app');
    </script>
</body>

</html>
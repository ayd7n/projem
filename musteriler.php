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
if (!yetkisi_var('page:view:musteriler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Calculate total customers
$total_result = $connection->query("SELECT COUNT(*) as total FROM musteriler");
$total_customers = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Müşteri Yönetimi - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            white-space: nowrap;
        }

        /* Specific styling for long text fields */
        .table td.address-cell,
        .table td.description-cell {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        .search-container {
            max-width: 300px;
        }

        /* Tablo font boyutu */
        table th,
        table td {
            font-size: 0.8rem;
        }

        /* Pagination font boyutu */
        .pagination,
        .pagination-info,
        .page-link,
        .form-control {
            font-size: 0.8rem !important;
        }
        /* Modal Form Düzenlemeleri - Premium Kompakt Stil */
        #customerModal .modal-content {
            border: none;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        #customerModal .modal-header {
            background: linear-gradient(135deg, #4a0e63, #7c2a99);
            color: white;
            padding: 8px 15px;
            border: none;
        }
        #customerModal .modal-title {
            font-size: 0.85rem;
            font-weight: 600;
        }
        #customerModal .modal-body {
            padding: 10px 12px;
            background: #fafafa;
        }
        #customerModal .form-group {
            margin-bottom: 0.4rem !important;
        }
        #customerModal label {
            font-size: 0.65rem;
            margin-bottom: 2px;
            font-weight: 500;
            color: #555;
            display: block;
        }
        #customerModal .form-control {
            font-size: 0.75rem;
            padding: 4px 8px;
            height: 28px;
            border-radius: 4px;
        }
        #customerModal textarea.form-control {
            height: auto;
        }
        #customerModal .modal-footer {
            background: #f5f5f5;
            border-top: 1px solid #e5e7eb;
            padding: 6px 12px;
        }
        #customerModal .btn {
            padding: 4px 10px;
            font-size: 0.75rem;
            border-radius: 4px;
        }
        #customerModal .close {
            opacity: 1;
            text-shadow: none;
            color: white;
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
                <h1>Müşteri Yönetimi</h1>
                <p>Yeni müşteriler ekleyin ve mevcut müşterileri yönetin.</p>
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-start align-items-center py-2 px-3">
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <!-- Yeni Müşteri Ekle Butonu -->
                    <?php if (yetkisi_var('action:musteriler:create')): ?>
                        <button @click="openModal(null)" class="btn btn-primary btn-sm"
                            style="font-size: 0.75rem; padding: 4px 10px;"><i class="fas fa-plus"></i> Yeni Müşteri</button>
                    <?php endif; ?>
                    <!-- Arama Kutusu -->
                    <div class="input-group input-group-sm" style="width: auto; min-width: 180px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="padding: 4px 8px;"><i
                                    class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" v-model="search"
                            @input="loadCustomers(1)" placeholder="Müşteri ara..."
                            style="font-size: 0.75rem; padding: 4px 8px;">
                    </div>
                    <!-- Stat Kartı - Müşteri Sayısı -->
                    <div class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #4a0e63, #7c2a99); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-users mr-1"></i>
                        <span style="font-weight: 600;">{{ totalCustomers }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Müşteri</span>
                    </div>
                    <!-- Stat Kartı - Toplam Bakiye -->
                    <div v-if="totalBalance > 0" class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #dc2626, #ef4444); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span style="font-weight: 600;">{{ formatCurrency(totalBalance, 'TRY') }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Toplam Bakiye</span>
                    </div>
                    <div v-else class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #059669, #10b981); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-check-circle mr-1"></i>
                        <span style="font-weight: 600;">Bakiye Yok</span>
                    </div>
                    <!-- Stat Kartı - Toplam Ödenmemiş Sipariş -->
                    <div v-if="totalUnpaidOrders > 0" class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #d97706, #f59e0b); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-clock mr-1"></i>
                        <span style="font-weight: 600;">{{ totalUnpaidOrders }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Ödenmemiş Sipariş</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-lock"></i> Giriş Yetkisi</th>
                                <th><i class="fas fa-boxes"></i> Stok Yetkisi</th>
                                <th><i class="fas fa-user"></i> Müşteri Adı</th>
                                <th><i class="fas fa-id-card"></i> Vergi/TC No</th>
                                <th><i class="fas fa-phone"></i> Telefon</th>
                                <th><i class="fas fa-phone"></i> Telefon 2</th>
                                <th><i class="fas fa-envelope"></i> E-posta</th>
                                <th><i class="fas fa-map-marker-alt"></i> Adres</th>
                                <th><i class="fas fa-sticky-note"></i> Açıklama</th>
                                <th><i class="fas fa-file-invoice"></i> Ödenmemiş</th>
                                <th><i class="fas fa-wallet"></i> Bakiye</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="12" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="customers.length === 0">
                                <td colspan="12" class="text-center p-4">Henüz kayıtlı müşteri bulunmuyor.</td>
                            </tr>
                            <tr v-for="customer in customers" :key="customer.musteri_id">
                                <td class="actions">
                                    <?php if (yetkisi_var('action:musteriler:edit')): ?>
                                        <button @click="openModal(customer)" class="btn btn-primary btn-sm"><i
                                                class="fas fa-edit"></i></button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:musteriler:delete')): ?>
                                        <button @click="deleteCustomer(customer.musteri_id)"
                                            class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                    <a :href="'musteri_karti.php?musteri_id=' + customer.musteri_id"
                                        class="btn btn-info btn-sm"><i class="fas fa-id-card"></i></a>
                                </td>
                                <td>
                                    <span v-if="customer.giris_yetkisi == 1"
                                        style="color: green; font-weight: bold;">✓</span>
                                    <span v-else style="color: red; font-weight: bold;">✗</span>
                                </td>
                                <td>
                                    <span v-if="customer.stok_goruntuleme_yetkisi == 1"
                                        style="color: green; font-weight: bold;">✓</span>
                                    <span v-else style="color: red; font-weight: bold;">✗</span>
                                </td>
                                <td><strong>{{ customer.musteri_adi }}</strong></td>
                                <td>{{ customer.vergi_no_tc || '-' }}</td>
                                <td>{{ customer.telefon || '-' }}</td>
                                <td>{{ customer.telefon_2 || '-' }}</td>
                                <td>{{ customer.e_posta || '-' }}</td>
                                <td class="address-cell">{{ customer.adres || '-' }}</td>
                                <td class="description-cell">{{ customer.aciklama_notlar || '-' }}</td>
                                <td>
                                    <span v-if="customer.odenmemis_siparis > 0" 
                                          style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 0.75rem; white-space: nowrap;">
                                        <i class="fas fa-clock"></i> {{ customer.odenmemis_siparis }} sipariş
                                    </span>
                                    <span v-else style="color: #059669; font-size: 0.75rem;">-</span>
                                </td>
                                <td v-html="customer.bakiye_gosterim"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0 w-100 w-md-auto">
                        <label for="recordsPerPage"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                        <select v-model="limit" @change="loadCustomers(1)" class="form-control d-inline-block"
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
                                    <a class="page-link" href="#" @click.prevent="loadCustomers(currentPage - 1)"><i
                                            class="fas fa-chevron-left"></i> Önceki</a>
                                </li>
                                <li v-if="currentPage > 3" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadCustomers(1)">1</a>
                                </li>
                                <li v-if="currentPage > 4" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item"
                                    :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadCustomers(page)">{{ page }}</a>
                                </li>
                                <li v-if="currentPage < totalPages - 3" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-if="currentPage < totalPages - 2" class_="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadCustomers(totalPages)">{{
                                        totalPages }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#"
                                        @click.prevent="loadCustomers(currentPage + 1)">Sonraki
                                        <i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Modal -->
        <div class="modal fade" id="customerModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form @submit.prevent="saveCustomer">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title">{{ modal.title }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" v-model="modal.data.musteri_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Müşteri Adı *</label>
                                        <input type="text" class="form-control" v-model="modal.data.musteri_adi"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Vergi No / TC</label>
                                        <input type="text" class="form-control" v-model="modal.data.vergi_no_tc">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Telefon</label>
                                        <input type="text" class="form-control" v-model="modal.data.telefon">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Telefon 2</label>
                                        <input type="text" class="form-control" v-model="modal.data.telefon_2">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>E-posta</label>
                                        <input type="email" class="form-control" v-model="modal.data.e_posta">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label>Adres</label>
                                <textarea class="form-control" v-model="modal.data.adres" rows="2"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                v-model="modal.data.giris_yetkisi" @change="validateForm">
                                            <label class="form-check-label">Sisteme Giriş Yetkisi</label>
                                        </div>
                                        <small class="form-text text-muted">Müşterinin sistemde oturum açmasına izin
                                            ver</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                v-model="modal.data.stok_goruntuleme_yetkisi">
                                            <label class="form-check-label">Stok Görüntüleme Yetkisi</label>
                                        </div>
                                        <small class="form-text text-muted">Müşterinin ürün stok miktarlarını görmesine
                                            izin ver</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Şifre <span v-if="modal.data.giris_yetkisi">*</span></label>
                                        <input type="password" class="form-control" v-model="modal.data.sifre"
                                            :required="modal.data.giris_yetkisi && !modal.data.musteri_id"
                                            :placeholder="modal.data.musteri_id ? 'Şifrenin değişmesini istemiyorsanız boş bırakın' : 'Şifre giriniz'">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label>Açıklama / Notlar</label>
                                <textarea class="form-control" v-model="modal.data.aciklama_notlar" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                    class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    customers: [],
                    loading: false,
                    alert: {
                        message: '',
                        type: ''
                    },
                    search: '',
                    currentPage: 1,
                    totalPages: 1,
                    totalCustomers: <?php echo $total_customers; ?>,
                    totalBalance: 0,
                    totalUnpaidOrders: 0,
                    limit: 10,
                    modal: {
                        title: '',
                        data: {}
                    }
                }
            },
            computed: {
                paginationInfo() {
                    if (this.totalPages <= 0 || this.totalCustomers <= 0) {
                        return 'Gösterilecek kayıt yok';
                    }
                    const startRecord = (this.currentPage - 1) * this.limit + 1;
                    const endRecord = Math.min(this.currentPage * this.limit, this.totalCustomers);
                    return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.totalCustomers} kayıttan`;
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
                formatCurrency(value, currency = 'TRY') {
                    let symbol = '₺';
                    if (currency === 'USD') symbol = '$';
                    else if (currency === 'EUR') symbol = '€';
                    return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' ' + symbol;
                },
                showAlert(message, type) {
                    this.alert.message = message;
                    this.alert.type = type;
                    setTimeout(() => {
                        this.alert.message = '';
                    }, 3000);
                },
                loadCustomers(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    let url = `api_islemleri/musteriler_islemler.php?action=get_customers&page=${this.currentPage}&limit=${this.limit}&search=${this.search}`;
                    fetch(url)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.customers = response.data;
                                this.totalPages = response.pagination.total_pages;
                                this.totalCustomers = response.pagination.total_customers;
                                
                                // Calculate total balance from all customers on current page
                                // Note: For accurate total, we use API's total_balance if available
                                if (response.pagination.total_balance !== undefined) {
                                    this.totalBalance = response.pagination.total_balance;
                                } else {
                                    // Fallback: calculate from current page data
                                    this.totalBalance = this.customers.reduce((sum, c) => sum + (parseFloat(c.kalan_bakiye) || 0), 0);
                                }
                                
                                // Get total unpaid orders
                                if (response.pagination.total_unpaid_orders !== undefined) {
                                    this.totalUnpaidOrders = response.pagination.total_unpaid_orders;
                                } else {
                                    // Fallback: calculate from current page data
                                    this.totalUnpaidOrders = this.customers.reduce((sum, c) => sum + (parseInt(c.odenmemis_siparis) || 0), 0);
                                }
                            } else {
                                this.showAlert('Müşteriler yüklenirken hata oluştu.', 'danger');
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            this.showAlert('Müşteriler yüklenirken bir hata oluştu.', 'danger');
                            this.loading = false;
                        });
                },
                openModal(customer) {
                    if (customer) {
                        this.modal.title = 'Müşteriyi Düzenle';
                        // Create a copy of customer data and ensure boolean fields are properly set
                        this.modal.data = {
                            ...customer,
                            giris_yetkisi: customer.giris_yetkisi == 1 || customer.giris_yetkisi === true,
                            stok_goruntuleme_yetkisi: customer.stok_goruntuleme_yetkisi == 1 || customer.stok_goruntuleme_yetkisi === true
                        };
                    } else {
                        this.modal.title = 'Yeni Müşteri Ekle';
                        this.modal.data = {
                            stok_goruntuleme_yetkisi: false  // Default to false for new customers
                        };
                    }
                    $('#customerModal').modal('show');
                },
                saveCustomer() {
                    let action = this.modal.data.musteri_id ? 'update_customer' : 'add_customer';

                    // For add operations, if giris_yetkisi is enabled, password is required
                    if (action === 'add_customer') {
                        if (this.modal.data.giris_yetkisi && (!this.modal.data.sifre || this.modal.data.sifre.trim() === '')) {
                            this.showAlert('Yeni müşteri için sistemde giriş yetkisi verildiğinde şifre zorunludur.', 'danger');
                            return;
                        }
                    }

                    let formData = new FormData();
                    for (let key in this.modal.data) {
                        if (this.modal.data[key] !== undefined && this.modal.data[key] !== null) {
                            formData.append(key, this.modal.data[key]);
                        }
                    }
                    formData.append('action', action);

                    fetch('api_islemleri/musteriler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#customerModal').modal('hide');
                                this.loadCustomers(this.currentPage);
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                        });
                },
                validateForm() {
                    // Only show warning for new customer creation
                    if (!this.modal.data.musteri_id && this.modal.data.giris_yetkisi && (!this.modal.data.sifre || this.modal.data.sifre.trim() === '')) {
                        this.showAlert('Yeni müşteri için sistemde giriş yetkisi verildiğinde şifre zorunludur.', 'warning');
                    }
                },
                deleteCustomer(id) {
                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: "Bu müşteriyi silmek istediğinizden emin misiniz?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Evet, sil!',
                        cancelButtonText: 'İptal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let formData = new FormData();
                            formData.append('action', 'delete_customer');
                            formData.append('musteri_id', id);

                            fetch('api_islemleri/musteriler_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(response => {
                                    if (response.status === 'success') {
                                        this.showAlert(response.message, 'success');
                                        this.loadCustomers(this.currentPage);
                                    } else {
                                        this.showAlert(response.message, 'danger');
                                    }
                                })
                                .catch(error => {
                                    this.showAlert('Silme işlemi sırasında bir hata oluştu.', 'danger');
                                });
                        }
                    })
                }
            },
            mounted() {
                this.loadCustomers();
            }
        });
        app.mount('#app');
    </script>
</body>

</html>
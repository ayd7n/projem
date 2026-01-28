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
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;700&display=swap&subset=latin-ext"
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
            font-family: 'Fira Sans', sans-serif;
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

        /* Drag & Drop Styles */
        .dragging {
            opacity: 0.5;
            transform: scale(0.95);
        }

        .drag-over {
            border: 2px dashed var(--primary) !important;
            background: rgba(74, 14, 99, 0.05);
        }

        /* Tablo font boyutu */
        table th,
        table td {
            font-size: 0.8rem;
        }

        /* Modal Form Düzenlemeleri - Premium Kompakt Stil */
        #productModal .modal-dialog {
            max-width: 90%;
            margin: 1.75rem auto;
        }
        #productModal .modal-content {
            border: none;
            border-radius: 12px;
            height: auto;
            max-height: 90vh;
            box-shadow: var(--shadow);
            font-family: 'Fira Sans', sans-serif;
            display: flex;
            flex-direction: column;
        }
        #productModal .modal-header {
            background: linear-gradient(135deg, #4a0e63, #7c2a99);
            color: white;
            padding: 10px 20px;
            flex-shrink: 0;
        }
        #productModal .modal-title {
            font-size: 0.95rem;
            font-weight: 600;
        }
        #productModal .modal-body {
            padding: 20px;
            background: #fcfcfc;
            flex-grow: 1;
            overflow-y: auto;
        }
        #productModal .form-group {
            margin-bottom: 0.8rem !important;
        }
        #productModal label {
            font-size: 0.75rem;
            margin-bottom: 3px;
            font-weight: 600;
            color: #555;
            display: block;
        }
        #productModal .form-control {
            font-size: 0.8rem;
            padding: 5px 10px;
            height: 32px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        #productModal .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.15rem rgba(124, 42, 153, 0.1);
        }
        #productModal textarea.form-control {
            min-height: 60px;
            height: auto;
        }
        
        .process-steps {
            background: #fff;
            border-radius: 6px;
            padding: 2px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .step-item {
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 5px;
            font-size: 0.78rem;
            color: #444;
            display: flex;
            align-items: flex-start;
        }
        
        .step-item i {
            margin-top: 2px;
            font-size: 0.85rem;
        }
        
        .custom-control-label {
            font-size: 0.78rem !important;
            padding-top: 1px;
        }

        #productModal .nav-tabs .nav-link {
            padding: 6px 15px;
            font-size: 0.8rem;
        }
        #productModal .btn {
            padding: 6px 15px;
            font-size: 0.8rem;
        }
        #productModal .modal-footer {
            padding: 10px 15px;
        }
        #productModal .close {
            font-size: 1.2rem;
            padding: 0.8rem;
        }

        /* Modal backdrop color adjustment */
        .modal-backdrop.show {
            background-color: var(--primary);
            opacity: 1;
        }

        /* Pagination area styling */
        .pagination-container {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        .pagination-container label,
        .pagination-container select,
        .pagination-container .pagination-info,
        .pagination-container .page-link {
            font-size: 0.75rem !important;
            font-family: 'Fira Sans', sans-serif !important;
        }
        .pagination-container select.form-control {
            height: 28px !important;
            padding: 2px 5px !important;
            line-height: 1;
        }
        .pagination-container .page-link {
            padding: 4px 10px;
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
        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-start align-items-center py-2 px-3">
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <!-- Yeni Ürün Ekle Butonu -->
                    <?php if (yetkisi_var('action:urunler:create')): ?>
                        <button @click="openModal(null)" class="btn btn-primary btn-sm"
                            style="font-size: 0.75rem; padding: 4px 10px;"><i class="fas fa-plus"></i> Yeni Ürün</button>
                        <button @click="openPurchaseModal" class="btn btn-warning btn-sm"
                            style="font-size: 0.75rem; padding: 4px 10px; color: var(--primary); font-weight: 700;"><i class="fas fa-shopping-cart"></i> Hızlı Satın Al</button>
                    <?php endif; ?>
                    <!-- Depo Filtresi -->
                    <div class="input-group input-group-sm" style="width: auto;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"
                                style="background: var(--primary); color: white; border: none; font-size: 0.7rem; padding: 4px 8px;"><i
                                    class="fas fa-warehouse"></i></span>
                        </div>
                        <select class="form-control form-control-sm" v-model="depoFilter" @change="onDepoFilterChange"
                            style="border-radius: 0 6px 6px 0; min-width: 110px; font-size: 0.75rem; padding: 4px 8px;">
                            <option value="">Tüm Depolar</option>
                            <option v-for="depo in productDepoList" :value="depo.depo_ismi">{{ depo.depo_ismi }}
                            </option>
                        </select>
                    </div>
                    <!-- Raf Filtresi -->
                    <div class="input-group input-group-sm" style="width: auto;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"
                                style="background: var(--primary); color: white; border: none; font-size: 0.7rem; padding: 4px 8px;"><i
                                    class="fas fa-cube"></i></span>
                        </div>
                        <select class="form-control form-control-sm" v-model="rafFilter" @change="loadProducts(1)"
                            style="border-radius: 0 6px 6px 0; min-width: 90px; font-size: 0.75rem; padding: 4px 8px;"
                            :disabled="!depoFilter">
                            <option value="">{{ depoFilter ? 'Tüm Raflar' : 'Depo seçin' }}</option>
                            <option v-for="raf in filterRafList" :value="raf.raf">{{ raf.raf }}</option>
                        </select>
                    </div>
                    <!-- Arama Kutusu -->
                    <div class="input-group input-group-sm" style="width: auto; min-width: 180px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="padding: 4px 8px;"><i
                                    class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" v-model="search" @input="onSearchInput"
                            placeholder="Urun ara..." style="font-size: 0.75rem; padding: 4px 8px;">
                    </div>
                    <!-- Stat Kartları -->
                    <div class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #4a0e63, #7c2a99); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-boxes mr-1"></i>
                        <span style="font-weight: 600;">{{ totalProducts }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Ürün</span>
                    </div>
                    <div class="stat-card-mini" @click="toggleCriticalStockFilter"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #dc3545, #c82333); color: white; display: inline-flex; align-items: center; cursor: pointer; font-size: 0.75rem;"
                        title="Kritik stok altındakileri filtrele">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span style="font-weight: 600;">{{ criticalProducts }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Kritik</span>
                    </div>

                    <!-- Fiyat/Maliyet Bilgisi Butonu -->
                    <button class="btn btn-sm" data-toggle="modal" data-target="#infoModal" 
                        style="font-size: 0.75rem; padding: 4px 12px; border: 1px solid #4a0e63; color: #4a0e63; background: white; font-weight: 600; box-shadow: 0 2px 4px rgba(74, 14, 99, 0.1); transition: all 0.3s;"
                        onmouseover="this.style.background='#4a0e63'; this.style.color='white'; this.style.boxShadow='0 4px 8px rgba(74, 14, 99, 0.2)';"
                        onmouseout="this.style.background='white'; this.style.color='#4a0e63'; this.style.boxShadow='0 2px 4px rgba(74, 14, 99, 0.1)';">
                        <i class="fas fa-book-reader mr-1"></i> Fiyat & Maliyet Rehberi
                    </button>
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
                                <th><i class="fas fa-image"></i> Fotograf</th>
                                <th><i class="fas fa-warehouse"></i> Stok</th>
                                <th><i class="fas fa-exclamation-triangle"></i> Kritik Stok</th>
                                <th><i class="fas fa-ruler"></i> Birim</th>
                                <th><i class="fas fa-money-bill-wave"></i> Satis Fiyati</th>
                                <?php if (yetkisi_var('action:urunler:view_cost')): ?>
                                    <th>Alış Fiyatı</th>
                                    <th>Maliyet</th>

                                <?php endif; ?>
                                <th><i class="fas fa-warehouse"></i> Depo</th>
                                <th><i class="fas fa-cube"></i> Raf</th>
                                <th><i class="fas fa-cogs"></i> Urun Tipi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="11" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="products.length === 0">
                                <td colspan="11" class="text-center p-4">Henuz kayitli urun bulunmuyor.</td>
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
                                <td class="text-center">
                                    <span v-if="product.foto_sayisi > 0" style="color: #28a745; font-size: 1.2rem;"
                                        :title="product.foto_sayisi + ' fotograf'">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <span v-else style="color: #dc3545; font-size: 1.2rem;" title="Fotograf yok">
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                </td>
                                <td>
                                    <span :class="stockClass(product)">
                                        {{ product.stok_miktari }}
                                    </span>
                                </td>
                                <td>{{ product.kritik_stok_seviyesi }}</td>
                                <td>{{ product.birim }}</td>
                                <td>{{ formatPriceWithCurrency(product) }}</td>
                                <?php if (yetkisi_var('action:urunler:view_cost')): ?>
                                    <td>{{ formatAlisFiyati(product) }}</td>
                                    <td>{{ formatTeorikMaliyet(product) }}</td>

                                <?php endif; ?>
                                <td>{{ product.depo }}</td>
                                <td>{{ product.raf }}</td>
                                <td>
                                    <span v-if="product.urun_tipi === 'uretilen'"
                                        class="badge badge-primary">Üretilen</span>
                                    <span v-else-if="product.urun_tipi === 'hazir_alinan'"
                                        class="badge badge-success">Hazır
                                        Alınan</span>
                                    <span v-else class="badge badge-secondary">{{ product.urun_tipi }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 pagination-container">
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
                                            class="fas fa-chevron-left"></i> Önceki</a>
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
                                    <a class="page-link" href="#" @click.prevent="loadProducts(currentPage + 1)">Sonraki
                                        <i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tablo Kılavuzu -->
        <div class="card mt-4 border-0 shadow-sm" style="background: #fdfdfd;">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary" style="font-size: 1rem; font-weight: 700;"><i class="fas fa-question-circle mr-2"></i> Tablo Alanları ve Kılavuz</h5>
            </div>
            <div class="card-body">
                <div class="row" style="font-size: 0.85rem;">
                    <div class="col-md-4 mb-3">
                        <h6 class="font-weight-bold text-dark border-bottom pb-1 mb-2"><i class="fas fa-cogs text-secondary mr-1"></i> Temel Bilgiler</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>İşlemler:</strong> Ürün üzerinde düzenleme, silme ve ürün ağacı görüntüleme aksiyonlarını barındırır.</li>
                            <li class="mb-2"><strong>Ürün Kodu:</strong> Sistemin ürüne atadığı benzersiz kimlik numarasıdır. Barkodlama ve takip için kullanılır.</li>
                            <li class="mb-2"><strong>Ürün İsmi:</strong> Ürünün sistemdeki resmi adıdır. Faturalarda ve raporlarda bu isim görünür.</li>
                            <li class="mb-2"><strong>Fotoğraf:</strong> Ürüne ait görselin varlığını gösterir. <i class="fas fa-check-circle text-success"></i> var, <i class="fas fa-times-circle text-danger"></i> yok demektir.</li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="font-weight-bold text-dark border-bottom pb-1 mb-2"><i class="fas fa-warehouse text-info mr-1"></i> Stok ve Depo</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Stok:</strong> Depodaki mevcut fiziksel adettir. <span class="text-danger">Kırmızı</span> stok bittiğini, <span class="text-warning">Turuncu</span> kritik seviyeyi gösterir.</li>
                            <li class="mb-2"><strong>Kritik Stok:</strong> Bu seviyenin altına inildiğinde sistem uyarı verir ve sipariş oluşturmanızı önerir.</li>
                            <li class="mb-2"><strong>Depo & Raf:</strong> Ürünün fiziksel olarak hangi depo ve rafta bulunduğunu belirtir.</li>
                            <li class="mb-2"><strong>Birim:</strong> Stok takibinin hangi cinsle yapıldığını gösterir (Adet, Kg, Lt vb.).</li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="font-weight-bold text-dark border-bottom pb-1 mb-2"><i class="fas fa-money-bill-wave text-success mr-1"></i> Finansal Veriler</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Satış Fiyatı:</strong> Ürünün müşteriye satıldığı etiket fiyatıdır.</li>
                            <li class="mb-2"><strong>Alış Fiyatı:</strong> Sadece <em>Hazır Alınan</em> ürünlerde görünür. Tedarikçiden alınan son fiyatın veya ortalamanın değeridir.</li>
                            <li class="mb-2"><strong>Maliyet:</strong> Ürünün size toplam geliş bedelidir. Bu değer, ürünün kendi tanımlı para birimi (USD, EUR, TL) üzerinden gösterilir. Üretilen ürünlerde reçetedeki tüm bileşenlerin güncel kurla harmanlanmış toplamıdır.</li>
                            <li class="mb-2"><strong>Ürün Tipi:</strong> <span class="badge badge-primary" style="font-size: 0.7em;">Üretilen</span> (kendi imalatınız) veya <span class="badge badge-success" style="font-size: 0.7em;">Hazır Alınan</span> (al-sat) olduğunu gösterir.</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-light border mt-2 mb-0 py-2 small text-muted">
                    <i class="fas fa-info-circle mr-1"></i> <strong>İpucu:</strong> Üst kısımdaki arama çubuğunu, depo ve raf filtrelerini kullanarak ürünlerinizi kolayca bulabilir ve yönetebilirsiniz.
                </div>
            </div>
        </div>

        <!-- Product Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header"
                        style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title">{{ modal.title }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Modal İçi Alert -->
                        <div v-if="modalAlert.message" :class="'alert alert-' + modalAlert.type" role="alert">
                            {{ modalAlert.message }}
                        </div>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-2" id="productTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">
                                    <i class="fas fa-info-circle"></i> Ürün Bilgileri
                                </a>
                            </li>
                            <li class="nav-item" v-if="modal.data.urun_kodu">
                                <a class="nav-link" id="photos-tab" data-toggle="tab" href="#photos" role="tab"
                                    @click="loadPhotos">
                                    <i class="fas fa-images"></i> Fotoğraflar ({{ productPhotos.length }})
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="productTabsContent">
                            <!-- Ürün Bilgileri Tab -->
                            <div class="tab-pane fade show active" id="info" role="tabpanel">
                                <form @submit.prevent="saveProduct">
                                    <div class="row">
                                        <!-- Sol Kolon: Form (%40 civarı) -->
                                        <div class="col-md-5 border-right">
                                            <input type="hidden" v-model="modal.data.urun_kodu">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label>Urun Ismi *</label>
                                                        <input type="text" class="form-control" v-model="modal.data.urun_ismi" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label>Stok Miktari</label>
                                                        <input type="number" class="form-control" v-model="modal.data.stok_miktari" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
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
                                                    <div class="form-group mb-2">
                                                        <label>Satis Fiyati *</label>
                                                        <input type="number" step="0.01" class="form-control" v-model="modal.data.satis_fiyati" min="0" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label>Para Birimi *</label>
                                                        <select class="form-control" v-model="modal.data.satis_fiyati_para_birimi">
                                                            <option value="TRY">₺ Türk Lirası</option>
                                                            <option value="USD">$ Amerikan Doları</option>
                                                            <option value="EUR">€ Euro</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label>Kritik Stok Seviyesi</label>
                                                        <input type="number" class="form-control" v-model="modal.data.kritik_stok_seviyesi" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label>Depo *</label>
                                                        <select class="form-control" v-model="modal.data.depo" @change="loadRafList(modal.data.depo)" required>
                                                            <option value="">Depo Secin</option>
                                                            <option v-for="depo in depoList" :value="depo.depo_ismi">{{ depo.depo_ismi }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label>Raf *</label>
                                                        <select class="form-control" v-model="modal.data.raf" required>
                                                            <option value="">Once Depo Secin</option>
                                                            <option v-for="raf in rafList" :value="raf.raf">{{ raf.raf }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label>Ürün Tipi *</label>
                                                        <select class="form-control" v-model="modal.data.urun_tipi" @change="onUrunTipiChange" required>
                                                            <option value="uretilen">Üretilen Ürün</option>
                                                            <option value="hazir_alinan">Hazır Alınan Ürün</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" v-if="modal.data.urun_tipi === 'hazir_alinan'">
                                                    <div class="form-group mb-2">
                                                        <label>Alış Fiyatı</label>
                                                        <input type="number" step="0.01" class="form-control" v-model="modal.data.alis_fiyati" min="0">
                                                    </div>
                                                </div>
                                                <div class="col-md-6" v-if="modal.data.urun_tipi === 'hazir_alinan'">
                                                    <div class="form-group mb-2">
                                                        <label>Alış Para Birimi</label>
                                                        <select class="form-control" v-model="modal.data.alis_fiyati_para_birimi">
                                                            <option value="TRY">₺ Türk Lirası</option>
                                                            <option value="USD">$ Amerikan Doları</option>
                                                            <option value="EUR">€ Euro</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-2" v-if="!modal.data.urun_kodu && modal.data.urun_tipi === 'uretilen'">
                                                <label><strong>Otomatik Malzeme Oluştur</strong></label>
                                                <div class="d-flex flex-wrap" style="gap: 10px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: #f9f9f9;">
                                                    <div v-for="tur in malzemeTurleri" :key="tur.id" class="custom-control custom-checkbox" style="min-width: 120px;">
                                                        <input type="checkbox" class="custom-control-input" :id="'tur_' + tur.id" :value="tur" v-model="selectedMaterialTypes">
                                                        <label class="custom-control-label" :for="'tur_' + tur.id" style="font-size: 0.75rem !important; cursor: pointer;">{{ tur.label }}</label>
                                                    </div>
                                                    <div class="custom-control custom-checkbox" style="min-width: 120px;">
                                                        <input type="checkbox" class="custom-control-input" id="check_create_essence" v-model="createEssence">
                                                        <label class="custom-control-label" for="check_create_essence" style="font-size: 0.75rem !important; cursor: pointer; color: var(--primary); font-weight: 600;">Esans (Oto. Tank)</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label>Not Bilgisi</label>
                                                <textarea class="form-control" v-model="modal.data.not_bilgisi" rows="2"></textarea>
                                            </div>
                                        </div>

                                        <!-- Sağ Kolon: Bilgi Paneli (Kalan Alan) -->
                                        <div class="col-md-7">
                                            <div class="card bg-light h-100 border-0">
                                                <div class="card-body p-3">
                                                    <h6 class="card-title text-primary border-bottom pb-2 mb-3"><i class="fas fa-list-ul"></i> Yapılacak İşlemler Rehberi</h6>
                                                    
                                                    <div v-if="!modal.data.urun_kodu" class="process-steps" style="max-height: 450px; overflow-y: auto; padding-right: 5px; background: transparent;">
                                                        <!-- Ürün Oluşturma -->
                                                        <div class="step-item mb-2">
                                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                                            <div>
                                                                <span class="font-weight-bold">{{ modal.data.urun_ismi || 'Yeni Ürün' }}</span> kartı sisteme kaydedilecek.
                                                                <div class="small text-muted">Birim: {{ modal.data.birim }}, Fiyat: {{ modal.data.satis_fiyati }} {{ modal.data.satis_fiyati_para_birimi }}</div>
                                                            </div>
                                                        </div>

                                                        <!-- Seçilen Malzemeler -->
                                                        <div v-if="selectedMaterialTypes.length > 0">
                                                            <div v-for="tur in selectedMaterialTypes" :key="tur.value" class="step-item mb-2 pl-3">
                                                                <i class="fas fa-plus-circle text-primary mr-2"></i>
                                                                <div>
                                                                    <span><strong>{{ tur.label }}</strong> malzemesi oluşturulacak.</span>
                                                                    <div v-if="tur.value !== 'ham_esans'" class="text-success small">
                                                                        <i class="fas fa-link"></i> Bu malzeme doğrudan <strong>Ürün Ağacı</strong>'na bağlanacak.
                                                                    </div>
                                                                    <div v-else-if="createEssence" class="text-info small">
                                                                        <i class="fas fa-link"></i> Bu malzeme yeni <strong>Esans Reçetesi</strong>'ne bağlanacak.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Esans Oluşturma -->
                                                        <div v-if="createEssence" class="step-item mb-2 mt-3 pt-2 border-top">
                                                            <i class="fas fa-flask text-warning mr-2"></i>
                                                            <div>
                                                                <span class="font-weight-bold text-warning">Otomatik Esans Üretimi</span>
                                                                <ul class="list-unstyled mt-1 small text-muted">
                                                                    <li><i class="fas fa-caret-right"></i> İsim: {{ modal.data.urun_ismi }}, Esans</li>
                                                                    <li><i class="fas fa-caret-right"></i> Tank: Sistemdeki ilk uygun/boş tank seçilecek.</li>
                                                                    <li><i class="fas fa-caret-right"></i> Bağlantı: Esans, ana ürünün reçetesine eklenecek.</li>
                                                                </ul>
                                                                
                                                                <!-- Ham Esans Uyarısı -->
                                                                <div v-if="selectedMaterialTypes.some(t => t.value === 'ham_esans')" class="alert alert-warning mt-2 p-2 mb-0" style="font-size: 0.75rem; border-left: 3px solid #ffc107;">
                                                                    <i class="fas fa-magic"></i> <strong>Otomatik Hiyerarşi:</strong> Ham Esans seçimi yaptığınız için, sistem bu ham maddeyi doğrudan esansın reçetesine (Esans Ağacı) işleyecek.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div v-if="selectedMaterialTypes.length === 0 && !createEssence" class="alert alert-light border text-center mt-5">
                                                            <i class="fas fa-mouse-pointer d-block mb-2 fa-2x opacity-50"></i>
                                                            Soldaki seçeneklerden malzeme türü seçerek otomatik işlemler listesini görebilirsiniz.
                                                        </div>
                                                    </div>
                                                    <div v-else class="text-center text-muted mt-5">
                                                        <i class="fas fa-info-circle fa-3x mb-3 opacity-50"></i>
                                                        <p>Düzenleme modunda otomatik malzeme ekleme yapılamaz. Mevcut malzeme bağlarını "Ürün Ağaçları" sayfasından yönetebilirsiniz.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right mt-2">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Iptal</button>
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Ürünü Kaydet</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Fotoğraflar Tab -->
                            <div class="tab-pane fade" id="photos" role="tabpanel">
                                <div class="photo-upload-section mb-4">
                                    <h6><i class="fas fa-cloud-upload-alt"></i> Fotoğraf Yükle</h6>
                                    <div class="upload-area" @dragover.prevent @drop.prevent="handleDrop"
                                        @click="$refs.photoInput.click()"
                                        style="border: 2px dashed var(--border-color); border-radius: 8px; padding: 40px; text-align: center; cursor: pointer; background: var(--bg-color); transition: all 0.3s;">
                                        <i class="fas fa-cloud-upload-alt"
                                            style="font-size: 3rem; color: var(--primary); margin-bottom: 10px;"></i>
                                        <p class="mb-0">Fotoğrafları buraya sürükleyin veya tıklayın</p>
                                        <small class="text-muted">Desteklenen formatlar: JPG, PNG, GIF (Max 5MB)</small>
                                        <input type="file" ref="photoInput" @change="handlePhotoSelect"
                                            accept="image/jpeg,image/png,image/gif" multiple style="display: none;">
                                    </div>
                                    <div v-if="uploadProgress > 0 && uploadProgress < 100" class="progress mt-3"
                                        style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            :style="{width: uploadProgress + '%'}">
                                            {{ uploadProgress }}%
                                        </div>
                                    </div>
                                </div>

                                <div class="photo-grid-section">
                                    <h6><i class="fas fa-images"></i> Yüklenen Fotoğraflar</h6>
                                    <div v-if="loadingPhotos" class="text-center p-4">
                                        <i class="fas fa-spinner fa-spin"></i> Fotoğraflar yükleniyor...
                                    </div>
                                    <div v-else-if="productPhotos.length === 0" class="text-center p-4 text-muted">
                                        <i class="fas fa-image" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2">Henüz fotoğraf yüklenmemiş</p>
                                    </div>
                                    <div v-else class="row">
                                        <div v-for="(photo, index) in productPhotos" :key="photo.fotograf_id"
                                            class="col-md-3 col-sm-6 mb-3" draggable="true"
                                            @dragstart="handleDragStart($event, index)"
                                            @dragover.prevent="handleDragOver($event, index)"
                                            @drop="handleDrop($event, index)" @dragend="handleDragEnd">
                                            <div class="photo-card"
                                                :class="{'dragging': draggedIndex === index, 'drag-over': dragOverIndex === index}"
                                                style="position: relative; border: 1px solid var(--border-color); border-radius: 8px; overflow: visible; background: white; cursor: move; transition: all 0.2s;">

                                                <!-- Ana Fotoğraf Badge -->
                                                <div v-if="photo.ana_fotograf == 1" class="primary-badge"
                                                    style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #FFD700, #FFA500); color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.7rem; font-weight: bold; z-index: 10; box-shadow: 0 2px 8px rgba(255,165,0,0.4);">
                                                    <i class="fas fa-star"></i> ANA FOTOĞRAF
                                                </div>

                                                <img :src="photo.dosya_yolu" :alt="photo.dosya_adi"
                                                    @click="openLightbox(index)"
                                                    style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;">

                                                <!-- Butonlar -->
                                                <div class="photo-overlay"
                                                    style="position: absolute; top: 0; right: 0; padding: 8px; display: flex; gap: 5px;">
                                                    <!-- Yıldız Butonu -->
                                                    <button @click.stop="setPrimaryPhoto(photo.fotograf_id)"
                                                        :class="photo.ana_fotograf == 1 ? 'btn-warning' : 'btn-outline-warning'"
                                                        class="btn btn-sm"
                                                        :title="photo.ana_fotograf == 1 ? 'Ana Fotoğraf' : 'Ana Fotoğraf Yap'"
                                                        style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9);">
                                                        <i :class="photo.ana_fotograf == 1 ? 'fas fa-star' : 'far fa-star'"
                                                            :style="{color: photo.ana_fotograf == 1 ? '#FFD700' : '#6c757d'}"></i>
                                                    </button>

                                                    <!-- Silme Butonu -->
                                                    <button @click.stop="deletePhoto(photo.fotograf_id)"
                                                        class="btn btn-danger btn-sm" title="Sil"
                                                        style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>

                                                <div class="photo-info" style="padding: 10px; background: white;">
                                                    <small class="text-muted"
                                                        style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <i class="fas fa-grip-vertical"
                                                            style="opacity: 0.5; margin-right: 5px;"></i>
                                                        {{ photo.dosya_adi }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Modal -->
        <div class="modal fade" id="purchaseModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.2);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #ffc107, #ff9800); color: var(--primary);">
                        <h5 class="modal-title"><i class="fas fa-shopping-cart mr-2"></i> Hızlı Satın Alma (Spot)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Modal İçi Alert -->
                        <div v-if="modalAlert.message" :class="'alert alert-' + modalAlert.type" role="alert">
                            {{ modalAlert.message }}
                        </div>

                        <form @submit.prevent="submitPurchase">
                            <div class="row">
                                <!-- Sol Kolon: Kompakt Form -->
                                <div class="col-md-5 border-right">
                                    <h6 class="text-secondary border-bottom pb-2 mb-3"><i class="fas fa-edit mr-1"></i> Satın Alma Bilgileri</h6>
                                    
                                    <div class="form-group mb-2">
                                        <label class="small mb-1 font-weight-bold">Tedarikçi *</label>
                                        <select class="form-control form-control-sm" v-model="purchase.tedarikci_id" required>
                                            <option value="">Tedarikçi Seçin</option>
                                            <option v-for="t in supplierList" :value="t.tedarikci_id">{{ t.tedarikci_adi }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label class="small mb-1 font-weight-bold">Ürün *</label>
                                        <select class="form-control form-control-sm" v-model="purchase.urun_kodu" @change="onPurchaseProductChange" required>
                                            <option value="">Ürün Seçin</option>
                                            <option v-for="p in products" :value="p.urun_kodu">{{ p.urun_kodu }} - {{ p.urun_ismi }}</option>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small mb-1 font-weight-bold">Miktar *</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" class="form-control" v-model="purchase.miktar" min="1" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text px-2" style="font-size: 0.7rem;">{{ purchase.birim }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small mb-1 font-weight-bold">Birim Fiyat *</label>
                                                <input type="number" class="form-control form-control-sm" v-model="purchase.birim_fiyat" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small mb-1 font-weight-bold">Para Birimi</label>
                                                <select class="form-control form-control-sm" v-model="purchase.para_birimi">
                                                    <option value="TL">₺ TRY</option>
                                                    <option value="USD">$ USD</option>
                                                    <option value="EUR">€ EUR</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small mb-1 font-weight-bold">Tarih</label>
                                                <input type="date" class="form-control form-control-sm" v-model="purchase.tarih" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="small mb-1 font-weight-bold">Açıklama</label>
                                        <textarea class="form-control form-control-sm" v-model="purchase.aciklama" rows="2" placeholder="İşlem notu..."></textarea>
                                    </div>
                                </div>

                                <!-- Sağ Kolon: İşlem Özeti ve Bilgilendirme -->
                                <div class="col-md-7 bg-light py-3">
                                    <h6 class="text-primary border-bottom border-primary pb-2 mb-3"><i class="fas fa-file-invoice mr-1"></i> İşlem Özeti ve Finansal Etki</h6>
                                    
                                    <div class="card border-warning mb-3 shadow-sm">
                                        <div class="card-body p-3 bg-white">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted small">Hesaplanan Toplam Borç:</span>
                                                <h4 class="mb-0 text-danger font-weight-bold">{{ formatCurrency(purchase.miktar * purchase.birim_fiyat, purchase.para_birimi) }}</h4>
                                            </div>
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 100%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="process-info mt-4">
                                        <div class="d-flex mb-3">
                                            <div class="mr-3 text-center" style="width: 30px;"><i class="fas fa-check-circle text-success fa-lg"></i></div>
                                            <div>
                                                <h6 class="mb-1" style="font-size: 0.85rem; font-weight: 700;">Envanter Güncellemesi</h6>
                                                <p class="small text-muted mb-0">Onayladığınızda <strong>{{ purchase.miktar }} {{ purchase.birim }}</strong> stok girişi yapılacak. Envanter değeriniz güncel alış fiyatı üzerinden artırılacak.</p>
                                            </div>
                                        </div>

                                        <div class="d-flex mb-3">
                                            <div class="mr-3 text-center" style="width: 30px;"><i class="fas fa-calculator text-info fa-lg"></i></div>
                                            <div>
                                                <h6 class="mb-1" style="font-size: 0.85rem; font-weight: 700;">Maliyet Hesaplama (Ortalama)</h6>
                                                <p class="small text-muted mb-0">Sistem, mevcut stok ile bu yeni alımı harmanlayarak <strong>Ağırlıklı Ortalama Maliyeti</strong> otomatik olarak yeniden hesaplayacaktır.</p>
                                            </div>
                                        </div>

                                        <div class="d-flex mb-3">
                                            <div class="mr-3 text-center" style="width: 30px;"><i class="fas fa-university text-danger fa-lg"></i></div>
                                            <div>
                                                <h6 class="mb-1" style="font-size: 0.85rem; font-weight: 700;">Borçlandırma Süreci</h6>
                                                <p class="small text-muted mb-0">Bu tutar kasadan nakit çıkışı <u>yapmaz</u>. Tedarikçi hanesine borç olarak yazılır. Ödeme planı için <strong>Kasa Yönetimi</strong> sayfasını kullanın.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning border-warning mt-4 p-2 small">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Dikkat:</strong> Bu işlem geri alınamaz. Kaydetmeden önce miktarı ve birim fiyatı lütfen kontrol edin.
                                    </div>
                                </div>
                            </div>

                            <div class="text-right border-top pt-3 mt-2">
                                <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">İptal</button>
                                <button type="submit" class="btn btn-warning text-dark font-weight-bold px-4">
                                    <i class="fas fa-check mr-1"></i> Satın Almayı Tamamla ve Borç Kaydı Oluştur
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal -->
        <div v-if="lightbox.show" class="lightbox-overlay" @click="closeLightbox"
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <button @click="closeLightbox" class="lightbox-close"
                style="position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; background: none; border: none; cursor: pointer; z-index: 10000;">
                <i class="fas fa-times"></i>
            </button>

            <button v-if="productPhotos.length > 1" @click.stop="previousPhoto" class="lightbox-nav lightbox-prev"
                style="position: absolute; left: 30px; color: white; font-size: 50px; background: rgba(255,255,255,0.1); border: none; cursor: pointer; padding: 20px; border-radius: 50%; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div @click.stop
                style="max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center;">
                <img :src="productPhotos[lightbox.currentIndex]?.dosya_yolu"
                    :alt="productPhotos[lightbox.currentIndex]?.dosya_adi"
                    style="max-width: 100%; max-height: 85vh; object-fit: contain; border-radius: 8px;">
                <div style="color: white; margin-top: 15px; text-align: center;">
                    <p style="margin: 5px 0; font-size: 16px;">{{ productPhotos[lightbox.currentIndex]?.dosya_adi }}</p>
                    <small style="opacity: 0.7;">{{ lightbox.currentIndex + 1 }} / {{ productPhotos.length }}</small>
                </div>
            </div>

            <button v-if="productPhotos.length > 1" @click.stop="nextPhoto" class="lightbox-nav lightbox-next"
                style="position: absolute; right: 30px; color: white; font-size: 50px; background: rgba(255,255,255,0.1); border: none; cursor: pointer; padding: 20px; border-radius: 50%; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Info Modal -->
        <div class="modal fade" id="infoModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.2);">
                    <div class="modal-header border-bottom-0 pb-1 pt-3 px-3">
                        <h6 class="modal-title font-weight-bold text-primary"><i class="fas fa-calculator mr-2"></i> Fiyat ve Maliyet Hesaplama Rehberi</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3">
                        <!-- Satış Fiyatı -->
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 15px; border-left: 5px solid #d4af37;">
                            <h6 class="text-dark font-weight-bold mb-2"><i class="fas fa-money-bill-wave text-warning mr-2"></i> 1. Satış Fiyatı (Müşteriye Sattığınız Fiyat)</h6>
                            <p class="mb-0 text-secondary" style="font-size: 0.85rem; line-height: 1.5;">
                                Bu, ürünün üzerindeki etikette yazan fiyattır. Müşteriden talep edeceğiniz parayı buraya yazarsınız. <br>
                                <strong>Önemli:</strong> Bu rakamı tamamen siz belirlersiniz. Maliyetiniz artsa bile siz burayı değiştirmediğiniz sürece satış fiyatı aynı kalır. Sistem buraya karışmaz.
                            </p>
                        </div>

                        <!-- Alış Fiyatı -->
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 15px; border-left: 5px solid #28a745;">
                            <h6 class="text-dark font-weight-bold mb-2"><i class="fas fa-shopping-cart text-success mr-2"></i> 2. Alış Fiyatı (Sadece Hazır Ürünler İçin)</h6>
                            <p class="mb-0 text-secondary" style="font-size: 0.85rem; line-height: 1.5;">
                                Bu alanı <strong>sadece</strong> dışarıdan hazır paketli alıp sattığınız ürünler için kullanın. <br>
                                Örneğin: Bir tedarikçiden hazır kolonya alıp satıyorsanız, tedarikçiye ödediğiniz parayı buraya yazın.<br>
                                <strong>Dikkat:</strong> Eğer ürünü fabrikanızda siz üretiyorsanız (şişe, kapak birleştiriyorsanız), bu kutucuğu <u>0 (sıfır)</u> bırakın. Çünkü sizin maliyetiniz aşağıda anlatılan yöntemle hesaplanacak.
                            </p>
                        </div>

                        <!-- Teorik Maliyet -->
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; border-left: 5px solid #17a2b8;">
                            <h6 class="text-dark font-weight-bold mb-2"><i class="fas fa-industry text-info mr-2"></i> 3. Teorik Maliyet (Sistemin Hesapladığı Gerçek Maliyet)</h6>
                            <p class="text-secondary mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Burası, bir ürünü üretmenin size kaça mal olduğunu gösteren kısımdır. Bu rakamı elle yazamazsınız, sistem arka planda otomatik hesaplar. Hesaplama şöyle yapılır:
                            </p>
                            
                            <ul class="list-unstyled mb-0 pl-2" style="font-size: 0.85rem;">
                                <li class="mb-3">
                                    <strong class="text-dark d-block mb-1"><i class="fas fa-calculator text-secondary mr-1"></i> Malzeme Maliyeti (Ağırlıklı Ortalama):</strong>
                                    Sistem, maliyetleri hesaplarken <strong>Çerçeve Sözleşmeler</strong> üzerinden yapılan <strong>Mal Kabulleri</strong> baz alır. Sadece son alış fiyatına değil, depodaki <strong>mevcut stoğun</strong> giriş fiyatlarına göre ağırlıklı ortalamasını hesaplar.
                                    <div class="mt-1 p-2 bg-white border rounded small text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> <em>Örnek: Tedarikçinizle anlaşmanız gereği 10 TL'den aldığınız kapaklar ile yeni anlaşmayla 20 TL'den aldıklarınız depoda karışıktır. Sistem bu stoğun ortalamasını (Örn: 15 TL) gerçek maliyet olarak kabul eder.</em>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <strong class="text-dark d-block mb-1"><i class="fas fa-list-ul text-secondary mr-1"></i> Reçete Toplamı:</strong>
                                    Sistem ürünün reçetesindeki tüm bileşenleri (Esans, Alkol, Şişe vb.) tek tek gezer. Çerçeve sözleşmelerden gelen fiyatlarla hesaplanan malzeme maliyetlerini, reçetedeki kullanım miktarlarıyla çarparak ürünün ana maliyetini oluşturur.
                                </li>
                                <li class="mb-0">
                                    <strong class="text-dark d-block mb-1"><i class="fas fa-dollar-sign text-secondary mr-1"></i> Otomatik Kur Çevrimi:</strong>
                                    Sözleşmeniz Dolar/Euro üzerinden olsa bile, sistem hesaplama anındaki güncel kuru kullanır. "Sözleşme fiyatı 1 Dolar, kur 30 TL ise maliyet 30 TL'dir" der. Kur değiştikçe maliyetiniz de otomatik güncellenir.
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-3">
                        <button type="button" class="btn btn-primary btn-sm px-4" data-dismiss="modal">Tamam, Anladım</button>
                    </div>
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
                        productTypeFilter: '',
                        modal: { title: '', data: {} },
                        modalAlert: { message: '', type: '' },
                        purchase: { tedarikci_id: '', urun_kodu: '', miktar: 1, birim_fiyat: 0, para_birimi: 'TL', tarih: '', aciklama: '', birim: 'adet' },
                        supplierList: [],
                        depoList: [],
                        rafList: [],
                        criticalStockFilterEnabled: false,
                        depoFilter: '',
                        rafFilter: '',
                        filterRafList: [],
                        productDepoList: [],
                        operatingCostMetrics: null,
                        productPhotos: [],
                        loadingPhotos: false,
                        uploadProgress: 0,
                        lightbox: {
                            show: false,
                            currentIndex: 0
                        },
                        draggedIndex: null,
                        dragOverIndex: null,
                        searchTimeout: null,
                        kurlar: { dolar: 1, euro: 1 },
                        malzemeTurleri: [],
                        selectedMaterialTypes: [],
                        createEssence: false
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
                    showModalAlert(message, type) {
                        this.modalAlert.message = message;
                        this.modalAlert.type = type;
                        setTimeout(() => { this.modalAlert.message = ''; }, 3000);
                    },
                    loadSupplierList() {
                        fetch('api_islemleri/tedarikciler_islemler.php?action=get_suppliers')
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    this.supplierList = response.data;
                                }
                            });
                    },
                    openPurchaseModal() {
                        this.modalAlert = { message: '', type: '' }; // Clear modal alert
                        this.purchase = {
                            tedarikci_id: '',
                            urun_kodu: '',
                            miktar: 1,
                            birim_fiyat: 0,
                            para_birimi: 'TL',
                            tarih: new Date().toISOString().split('T')[0],
                            aciklama: '',
                            birim: 'adet'
                        };
                        this.loadSupplierList();
                        $('#purchaseModal').modal('show');
                    },
                    onPurchaseProductChange() {
                        const product = this.products.find(p => p.urun_kodu === this.purchase.urun_kodu);
                        if (product) {
                            this.purchase.birim_fiyat = parseFloat(product.alis_fiyati) || 0;
                            this.purchase.para_birimi = product.alis_fiyati_para_birimi || 'TL';
                            this.purchase.birim = product.birim || 'adet';
                        }
                    },
                    submitPurchase() {
                        console.log('Satın alma işlemi başlatıldı:', this.purchase);
                        
                        if (this.purchase.miktar <= 0) {
                            this.showModalAlert('Miktar 0\'dan büyük olmalıdır.', 'warning');
                            return;
                        }
                        
                        let formData = new FormData();
                        formData.append('action', 'quick_purchase');
                        for (let key in this.purchase) {
                            formData.append(key, this.purchase[key]);
                        }

                        fetch('api_islemleri/urunler_islemler.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(response => {
                            console.log('API Yanıtı:', response);
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success'); // Ana sayfa alerti (başarılı olunca modal kapanıyor)
                                $('#purchaseModal').modal('hide');
                                this.loadProducts(this.currentPage);
                            } else {
                                this.showModalAlert(response.message, 'danger'); // Modal içi alert (hata durumunda)
                            }
                        })
                        .catch(error => {
                            console.error('Hata:', error);
                            this.showModalAlert('İşlem sırasında bir hata oluştu: ' + error, 'danger');
                        });
                    },
                    loadMalzemeTurleri() {
                        fetch('api_islemleri/urunler_islemler.php?action=get_malzeme_turleri')
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    // 'alkol' türünü listeden filtrele
                                    this.malzemeTurleri = response.data.filter(tur => tur.value !== 'alkol');
                                }
                            });
                    },
                    loadProducts(page = 1) {
                        this.loading = true;
                        this.currentPage = page;
                        let url = `api_islemleri/urunler_islemler.php?action=get_products&page=${this.currentPage}&limit=${this.limit}&search=${encodeURIComponent(this.search)}`;
                        if (this.criticalStockFilterEnabled) {
                            url += '&filter=critical';
                        }
                        if (this.productTypeFilter) {
                            url += `&urun_tipi=${encodeURIComponent(this.productTypeFilter)}`;
                        }
                        if (this.depoFilter) {
                            url += `&depo=${encodeURIComponent(this.depoFilter)}`;
                        }
                        if (this.rafFilter) {
                            url += `&raf=${encodeURIComponent(this.rafFilter)}`;
                        }
                        fetch(url)
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    this.products = response.data;
                                    this.totalPages = response.pagination.total_pages;
                                    this.totalProducts = response.pagination.total_products;
                                    // Update stats
                                    if (response.stats) {
                                        this.totalProducts = response.stats.total_products;
                                        this.criticalProducts = response.stats.critical_products;
                                    }
                                } else {
                                    this.showAlert('Ürünler yüklenirken hata oluştu: ' + response.message, 'danger');
                                }
                                this.loading = false;
                            })
                            .catch(error => {
                                this.showAlert('Ürünler yüklenirken bir hata oluştu.', 'danger');
                                this.loading = false;
                            });
                    },
                    setProductTypeFilter(filter) {
                        this.productTypeFilter = filter;
                        this.currentPage = 1; // Reset to first page when filtering
                        this.loadProducts(1);
                    },
                    onSearchInput() {
                        clearTimeout(this.searchTimeout);
                        this.searchTimeout = setTimeout(() => {
                            this.currentPage = 1; // Reset to first page when searching
                            this.loadProducts(1);
                        }, 500); // 500ms debounce
                    },
                    onDepoFilterChange() {
                        this.rafFilter = ''; // Reset raf filter when depo changes
                        if (this.depoFilter) {
                            this.loadFilterRafList(this.depoFilter);
                        } else {
                            this.filterRafList = [];
                        }
                        this.loadProducts(1);
                    },
                    loadFilterRafList(depo) {
                        if (!depo) {
                            this.filterRafList = [];
                            return;
                        }
                        fetch(`api_islemleri/urunler_islemler.php?action=get_product_raflar&depo=${encodeURIComponent(depo)}`)
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    this.filterRafList = response.data;
                                }
                            });
                    },
                    openModal(product) {
                        this.rafList = []; // Clear raf list
                        this.productPhotos = []; // Clear photos
                        this.uploadProgress = 0; // Reset progress
                        this.selectedMaterialTypes = []; // Reset selected types
                        this.createEssence = false; // Reset essence creation
                        this.modalAlert = { message: '', type: '' }; // Clear modal alert

                        if (product) {
                            this.modal.title = 'Urunu Duzenle';
                            this.modal.data = { ...product };
                            if (product.depo) {
                                this.loadRafList(product.depo, product.raf);
                            }
                            // Fotoğrafları yükle
                            this.loadPhotos();
                        } else {
                            this.modal.title = 'Yeni Urun Ekle';
                            this.modal.data = { birim: 'adet', urun_tipi: 'uretilen', satis_fiyati: 0.0, satis_fiyati_para_birimi: 'TRY', alis_fiyati: 0.0, alis_fiyati_para_birimi: 'TRY', depo: '', raf: '' };
                            this.loadMalzemeTurleri(); // Türleri yükle
                        }

                        // Modal'ı kapatıp açmak için kontrol et
                        if ($('#productModal').hasClass('show')) {
                            $('#productModal').modal('hide');
                            setTimeout(() => {
                                $('#productModal').modal('show');
                            }, 300);
                        } else {
                            $('#productModal').modal('show');
                        }
                    },
                    saveProduct() {
                        // Hazır alınan ürünler için alış fiyatı kontrolü
                        if (this.modal.data.urun_tipi === 'hazir_alinan') {
                            const alisFiyati = parseFloat(this.modal.data.alis_fiyati) || 0;
                            if (alisFiyati <= 0) {
                                this.showModalAlert('Hazır alınan ürünler için alış fiyatı 0\'dan büyük olmalıdır.', 'danger');
                                return;
                            }
                        }

                        let action = this.modal.data.urun_kodu ? 'update_product' : 'add_product';
                        let formData = new FormData();
                        for (let key in this.modal.data) {
                            formData.append(key, this.modal.data[key]);
                        }
                        formData.append('action', action);
                        
                        // Seçili malzeme türlerini gönder
                        if (action === 'add_product') {
                            formData.append('selected_material_types', JSON.stringify(this.selectedMaterialTypes));
                            formData.append('create_essence', this.createEssence ? '1' : '0');
                        }

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
                        // Modal için tüm lokasyonlardan depo listesi
                        fetch('api_islemleri/urunler_islemler.php?action=get_depo_list')
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    this.depoList = response.data;
                                }
                            });
                    },
                    loadProductDepolar() {
                        // Filtre için sadece ürünlerde kullanılan depolar
                        fetch('api_islemleri/urunler_islemler.php?action=get_product_depolar')
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    this.productDepoList = response.data;
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
                    formatCurrency(value, currency = 'TRY') {
                        if (isNaN(value)) return '0,00 ₺';
                        const num = parseFloat(value);
                        const currencySymbols = { 'TRY': '₺', 'USD': '$', 'EUR': '€' };
                        const symbol = currencySymbols[currency] || '₺';
                        return num.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + symbol;
                    },
                    formatPriceWithCurrency(product) {
                        const price = parseFloat(product.satis_fiyati) || 0;
                        const currency = product.satis_fiyati_para_birimi || 'TRY';
                        return this.formatCurrency(price, currency);
                    },
                    formatTeorikMaliyet(product) {
                        // Hazır alınan ürün için alış fiyatını göster
                        if (product.urun_tipi === 'hazir_alinan') {
                            return this.formatAlisFiyati(product);
                        }
                        // Üretilen ürün için teorik maliyeti göster (satış fiyatı para birimiyle)
                        const teorikMaliyet = parseFloat(product.teorik_maliyet) || 0;
                        const currency = product.satis_fiyati_para_birimi || 'TRY';
                        return this.formatCurrency(teorikMaliyet, currency);
                    },
                    formatAlisFiyati(product) {
                        const alisFiyati = parseFloat(product.alis_fiyati) || 0;
                        const currency = product.alis_fiyati_para_birimi || 'TRY';
                        return this.formatCurrency(alisFiyati, currency);
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
                    loadPhotos() {
                        if (!this.modal.data.urun_kodu) return;

                        this.loadingPhotos = true;
                        fetch(`api_islemleri/urun_fotograflari_islemler.php?action=get_photos&urun_kodu=${this.modal.data.urun_kodu}`)
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    this.productPhotos = response.data;
                                } else {
                                    this.showAlert('Fotoğraflar yüklenirken hata oluştu.', 'danger');
                                }
                                this.loadingPhotos = false;
                            })
                            .catch(error => {
                                this.showAlert('Fotoğraflar yüklenirken bir hata oluştu.', 'danger');
                                this.loadingPhotos = false;
                            });
                    },
                    handlePhotoSelect(event) {
                        const files = event.target.files;
                        this.uploadPhotos(files);
                    },
                    handleDrop(event) {
                        const files = event.dataTransfer.files;
                        this.uploadPhotos(files);
                    },
                    uploadPhotos(files) {
                        if (!this.modal.data.urun_kodu) {
                            this.showAlert('Önce ürünü kaydetmelisiniz.', 'warning');
                            return;
                        }

                        if (files.length === 0) return;

                        const totalFiles = files.length;
                        let uploadedFiles = 0;

                        Array.from(files).forEach((file, index) => {
                            // Dosya boyutu kontrolü (5MB)
                            if (file.size > 5 * 1024 * 1024) {
                                this.showAlert(`${file.name} dosyası 5MB'dan büyük.`, 'danger');
                                return;
                            }

                            // Dosya formatı kontrolü
                            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                            if (!allowedTypes.includes(file.type)) {
                                this.showAlert(`${file.name} desteklenmeyen bir format.`, 'danger');
                                return;
                            }

                            const formData = new FormData();
                            formData.append('action', 'upload_photo');
                            formData.append('urun_kodu', this.modal.data.urun_kodu);
                            formData.append('photo', file);

                            fetch('api_islemleri/urun_fotograflari_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(response => {
                                    uploadedFiles++;
                                    this.uploadProgress = Math.round((uploadedFiles / totalFiles) * 100);

                                    if (response.status === 'success') {
                                        this.productPhotos.push(response.data);

                                        // Update main product list photo count
                                        const productIndex = this.products.findIndex(p => p.urun_kodu === this.modal.data.urun_kodu);
                                        if (productIndex !== -1) {
                                            // Force reactivity by creating a new object or using Vue.set if needed, 
                                            // but direct assignment usually works if property exists. 
                                            // To be safe with Vue 2 reactivity for numbers:
                                            this.products[productIndex].foto_sayisi = (parseInt(this.products[productIndex].foto_sayisi) || 0) + 1;
                                        }
                                    } else {
                                        this.showAlert(response.message, 'danger');
                                    }

                                    if (uploadedFiles === totalFiles) {
                                        setTimeout(() => {
                                            this.uploadProgress = 0;
                                        }, 1000);
                                    }
                                })
                                .catch(error => {
                                    this.showAlert('Fotoğraf yüklenirken bir hata oluştu.', 'danger');
                                    uploadedFiles++;
                                    this.uploadProgress = Math.round((uploadedFiles / totalFiles) * 100);
                                });
                        });
                    },
                    deletePhoto(fotograf_id) {
                        Swal.fire({
                            title: 'Emin misiniz?',
                            text: "Bu fotoğrafı silmek istediğinizden emin misiniz?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Evet, sil!',
                            cancelButtonText: 'İptal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const formData = new FormData();
                                formData.append('action', 'delete_photo');
                                formData.append('fotograf_id', fotograf_id);

                                fetch('api_islemleri/urun_fotograflari_islemler.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                    .then(response => response.json())
                                    .then(response => {
                                        if (response.status === 'success') {
                                            // Remove photo from array using splice for better reactivity
                                            const index = this.productPhotos.findIndex(p => p.fotograf_id === fotograf_id);
                                            if (index !== -1) {
                                                this.productPhotos.splice(index, 1);
                                            }

                                            // Update main product list photo count
                                            const productIndex = this.products.findIndex(p => p.urun_kodu === this.modal.data.urun_kodu);
                                            if (productIndex !== -1) {
                                                this.products[productIndex].foto_sayisi = Math.max(0, (parseInt(this.products[productIndex].foto_sayisi) || 0) - 1);
                                            }
                                            this.showAlert('Fotoğraf başarıyla silindi.', 'success');
                                        } else {
                                            this.showAlert(response.message, 'danger');
                                        }
                                    })
                                    .catch(error => {
                                        this.showAlert('Fotoğraf silinirken bir hata oluştu.', 'danger');
                                    });
                            }
                        });
                    },
                    openLightbox(index) {
                        this.lightbox.currentIndex = index;
                        this.lightbox.show = true;
                        document.body.style.overflow = 'hidden'; // Prevent scrolling
                        // Add keyboard navigation
                        document.addEventListener('keydown', this.handleLightboxKeyboard);
                    },
                    closeLightbox() {
                        this.lightbox.show = false;
                        document.body.style.overflow = ''; // Restore scrolling
                        document.removeEventListener('keydown', this.handleLightboxKeyboard);
                    },
                    nextPhoto() {
                        this.lightbox.currentIndex = (this.lightbox.currentIndex + 1) % this.productPhotos.length;
                    },
                    previousPhoto() {
                        this.lightbox.currentIndex = (this.lightbox.currentIndex - 1 + this.productPhotos.length) % this.productPhotos.length;
                    },
                    handleLightboxKeyboard(event) {
                        if (!this.lightbox.show) return;

                        if (event.key === 'Escape') {
                            this.closeLightbox();
                        } else if (event.key === 'ArrowRight') {
                            this.nextPhoto();
                        } else if (event.key === 'ArrowLeft') {
                            this.previousPhoto();
                        }
                    },
                    setPrimaryPhoto(fotograf_id) {
                        const formData = new FormData();
                        formData.append('action', 'set_primary_photo');
                        formData.append('fotograf_id', fotograf_id);

                        fetch('api_islemleri/urun_fotograflari_islemler.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    // Tüm fotoğrafları normal yap
                                    this.productPhotos.forEach(p => p.ana_fotograf = 0);
                                    // Seçilen fotoğrafı ana yap
                                    const photo = this.productPhotos.find(p => p.fotograf_id === fotograf_id);
                                    if (photo) {
                                        photo.ana_fotograf = 1;
                                    }
                                    this.showAlert('Ana fotoğraf başarıyla ayarlandı.', 'success');
                                } else {
                                    this.showAlert(response.message, 'danger');
                                }
                            })
                            .catch(error => {
                                this.showAlert('Ana fotoğraf ayarlanırken bir hata oluştu.', 'danger');
                            });
                    },
                    handleDragStart(event, index) {
                        this.draggedIndex = index;
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/html', event.target.innerHTML);
                    },
                    handleDragOver(event, index) {
                        this.dragOverIndex = index;
                    },
                    handleDrop(event, targetIndex) {
                        event.preventDefault();

                        if (this.draggedIndex === null || this.draggedIndex === targetIndex) {
                            this.draggedIndex = null;
                            this.dragOverIndex = null;
                            return;
                        }

                        // Fotoğrafları yeniden sırala
                        const draggedPhoto = this.productPhotos[this.draggedIndex];
                        const newPhotos = [...this.productPhotos];

                        // Sürüklenen öğeyi çıkar
                        newPhotos.splice(this.draggedIndex, 1);
                        // Yeni konuma ekle
                        newPhotos.splice(targetIndex, 0, draggedPhoto);

                        this.productPhotos = newPhotos;

                        // Sıralamayı backend'e gönder
                        this.updatePhotoOrder();

                        this.draggedIndex = null;
                        this.dragOverIndex = null;
                    },
                    handleDragEnd() {
                        this.draggedIndex = null;
                        this.dragOverIndex = null;
                    },
                    updatePhotoOrder() {
                        const photoIds = this.productPhotos.map(p => p.fotograf_id);
                        const formData = new FormData();
                        formData.append('action', 'update_order');
                        formData.append('photos', JSON.stringify(photoIds));

                        fetch('api_islemleri/urun_fotograflari_islemler.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    // Sıra numaralarını güncelle
                                    this.productPhotos.forEach((photo, index) => {
                                        photo.sira_no = index + 1;
                                    });
                                } else {
                                    this.showAlert('Sıralama güncellenirken hata oluştu.', 'danger');
                                }
                            })
                            .catch(error => {
                                this.showAlert('Sıralama güncellenirken bir hata oluştu.', 'danger');
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
                    },
                    loadKurlar() {
                        fetch('api_islemleri/ayarlar_islemler.php?action=get_settings')
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    this.kurlar.dolar = parseFloat(response.data.dolar_kuru) || 1;
                                    this.kurlar.euro = parseFloat(response.data.euro_kuru) || 1;
                                }
                            });
                    },
                    onUrunTipiChange() {
                        // Ürün tipi üretilen olarak değiştiğinde alış fiyatını 0 yap
                        if (this.modal.data.urun_tipi === 'uretilen') {
                            this.modal.data.alis_fiyati = 0.0;
                            this.modal.data.alis_fiyati_para_birimi = 'TRY';
                        }
                    }
                },

                mounted() {
                    this.loadProducts();
                    this.loadDepoList();
                    this.loadProductDepolar();
                    this.loadOperatingCostMetrics();
                    this.loadKurlar();

                    // Modal açıldığında tab'ı Ürün Bilgileri'ne sıfırla
                    $('#productModal').on('shown.bs.modal', () => {
                        $('#info-tab').tab('show');
                    });
                }
            });
            app.mount('#app');
        </script>
</body>

</html>
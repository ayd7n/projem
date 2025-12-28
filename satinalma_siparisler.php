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
if (!yetkisi_var('page:view:satinalma_siparisler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Satınalma Siparişleri - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        table th,
        table td {
            font-size: 0.8rem;
        }

        .pagination,
        .pagination-info,
        .page-link,
        .form-control {
            font-size: 0.8rem !important;
        }

        .stat-card {
            background: linear-gradient(135deg, #4a0e63, #7c2a99);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }

        .stat-card .stat-value {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .badge-taslak {
            background-color: #6c757d;
            color: white;
        }

        .badge-onaylandi {
            background-color: #6c757d;
            color: white;
        }

        .badge-olusturuldu {
            background-color: #6c757d;
            color: white;
        }

        .badge-gonderildi {
            background-color: #ffc107;
            color: #333;
        }

        .badge-kismen_teslim {
            background-color: #ffc107;
            color: #333;
        }

        .badge-tamamlandi {
            background-color: #ffc107;
            color: #333;
        }

        .badge-yollandi {
            background-color: #ffc107;
            color: #333;
        }

        .badge-iptal {
            background-color: #dc3545;
            color: white;
        }

        .badge-kapatildi {
            background-color: #28a745;
            color: white;
        }

        .kalem-row {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
        }

        .kalem-row:hover {
            background: #e9ecef;
        }

        .remove-kalem {
            cursor: pointer;
            color: #dc3545;
        }

        .supplier-summary {
            background: linear-gradient(135deg, #1a5276, #2980b9);
            color: white;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        /* ===== CLEAN MODAL STYLING ===== */

        /* Modal Content */
        .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        /* Order Modal Header */
        #orderModal .modal-header {
            background: #5a3d7a;
            border: none;
            padding: 1rem 1.25rem;
        }

        #orderModal .modal-title {
            font-weight: 600;
            font-size: 1rem;
            color: white;
        }

        #orderModal .modal-body {
            padding: 1.25rem;
            background: #fafafa;
        }

        /* Form Sections */
        .form-section {
            background: white;
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border: 1px solid #e5e5e5;
        }

        .form-section-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #5a3d7a;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 6px;
            padding-bottom: 0.25rem;
            border-bottom: 1px solid #eee;
        }

        /* Form Controls */
        #orderModal .form-group label {
            font-weight: 500;
            color: #555;
            margin-bottom: 0.2rem;
            font-size: 0.75rem;
        }

        #orderModal .form-control {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.35rem 0.5rem;
            font-size: 0.8rem;
            height: 32px;
            display: block;
            width: 100%;
        }

        #orderModal select.form-control {
            padding-top: 2px;
            padding-bottom: 2px;
        }

        #orderModal .form-control:focus {
            border-color: #5a3d7a;
            box-shadow: 0 0 0 2px rgba(90, 61, 122, 0.1);
        }

        /* Kalem Row */
        .kalem-row {
            background: white;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            margin-bottom: 4px;
            border: 1px solid #e5e5e5;
        }

        .kalem-row .row.no-gutters {
            flex-wrap: nowrap !important;
        }

        .kalem-row .input-group {
            flex-wrap: nowrap !important;
        }

        .kalem-row .input-group-text {
            white-space: nowrap !important;
            padding-left: 4px !important;
            padding-right: 4px !important;
            font-size: 0.65rem !important;
        }

        .kalem-row .malzeme-name {
            font-weight: 600;
            color: #333;
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kalem-row .form-control-sm {
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .kalem-total {
            color: #5a3d7a;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .remove-kalem {
            cursor: pointer;
            color: #dc3545;
            font-size: 1rem;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }

        .remove-kalem:hover {
            background: #fee2e2;
        }

        /* Total Bar */
        .order-total-bar {
            background: #5a3d7a;
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.75rem;
        }

        .order-total-bar .total-label {
            font-size: 0.85rem;
        }

        .order-total-bar .total-value {
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Modal Footer */
        #orderModal .modal-footer-custom {
            background: #f5f5f5;
            padding: 0.75rem 1rem;
            border-top: 1px solid #e5e5e5;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        #orderModal .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        #orderModal .btn-cancel:hover {
            background: #5a6268;
        }

        #orderModal .btn-save {
            background: #5a3d7a;
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        #orderModal .btn-save:hover {
            background: #4a2f66;
        }

        #orderModal .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* View Order Modal */
        #viewOrderModal .modal-header {
            background: #2c5282;
            border: none;
            padding: 1rem 1.25rem;
        }

        #viewOrderModal .modal-title {
            font-weight: 600;
            font-size: 1rem;
            color: white;
        }

        #viewOrderModal .modal-body {
            padding: 1.25rem;
            background: #fafafa;
        }

        /* View Order Info */
        .view-info-section {
            background: white;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #e5e5e5;
        }

        .view-info-row {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .view-info-row:last-child {
            margin-bottom: 0;
        }

        .view-info-label {
            font-weight: 600;
            color: #555;
            min-width: 130px;
            font-size: 0.85rem;
        }

        .view-info-value {
            color: #333;
            font-size: 0.85rem;
        }

        /* View Items Table */
        .view-items-table {
            background: white;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e5e5e5;
        }

        .view-items-table thead {
            background: #f5f5f5;
        }

        .view-items-table th {
            font-weight: 600;
            font-size: 0.75rem;
            color: #666;
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid #ddd;
        }

        .view-items-table td {
            font-size: 0.85rem;
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid #eee;
        }

        .view-items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .view-items-table tbody tr:hover {
            background: #fafafa;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge.olusturuldu,
        .status-badge.taslak,
        .status-badge.onaylandi {
            background: #e5e7eb;
            color: #374151;
        }

        .status-badge.yollandi,
        .status-badge.gonderildi {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.kapatildi {
            background: #d1fae5;
            color: #065f46;
        }

        /* Status Update Section */
        .status-update-section {
            background: #f5f5f5;
            padding: 0.75rem 1rem;
            margin-top: 1rem;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
        }

        .status-update-section label {
            font-weight: 600;
            color: #555;
            font-size: 0.85rem;
        }

        /* Material Select */
        .material-select-wrapper select {
            background: #5a3d7a;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
            cursor: pointer;
            width: 100%;
        }

        .material-select-wrapper select option {
            background: white;
            color: #333;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 1.5rem;
            color: #888;
        }

        .empty-state i {
            font-size: 2rem;
            color: #ccc;
            margin-bottom: 0.75rem;
        }

        .empty-state p {
            margin: 0;
            font-size: 0.85rem;
        }

        /* View Modal Footer */
        #viewOrderModal .modal-footer {
            background: #f5f5f5;
            border-top: 1px solid #e5e5e5;
            padding: 0.75rem 1rem;
        }

        #viewOrderModal .modal-footer .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top no-print"
        style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php">
                <i class="fas fa-spa"></i> IDO KOZMETIK
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="navigation.php">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="change_password.php">Parolamı Değiştir</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="app" class="main-content">
        <div class="page-header no-print">
            <div>
                <h1><i class="fas fa-file-invoice"></i> Satınalma Siparişleri</h1>
                <p>Tedarikçilere verilen satınalma siparişlerini yönetin</p>
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="card no-print">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start py-2 px-3">
                <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0" style="gap: 6px;">
                    <?php if (yetkisi_var('action:satinalma_siparisler:create')): ?>
                        <button @click="openModal(null)" class="btn btn-primary btn-sm"
                            style="font-size: 0.75rem; padding: 4px 10px;">
                            <i class="fas fa-plus"></i> Yeni Sipariş
                        </button>
                    <?php endif; ?>

                    <div class="input-group input-group-sm" style="width: auto; min-width: 180px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="padding: 4px 8px;"><i
                                    class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" v-model="search" @input="debounceSearch"
                            placeholder="Ara..." style="font-size: 0.75rem; padding: 4px 8px;">
                    </div>

                    <select class="form-control form-control-sm" v-model="tedarikciFilter" @change="loadOrders(1)"
                        style="width: auto; min-width: 150px; font-size: 0.75rem;">
                        <option value="">Tüm Tedarikçiler</option>
                        <option v-for="t in suppliers" :key="t.tedarikci_id" :value="t.tedarikci_id">{{ t.tedarikci_adi
                            }}</option>
                    </select>

                    <select class="form-control form-control-sm" v-model="durumFilter" @change="loadOrders(1)"
                        style="width: auto; font-size: 0.75rem;">
                        <option value="">Tüm Durumlar</option>
                        <option value="olusturuldu">Oluşturuldu</option>
                        <option value="yollandi">Tedarikçiye Yollandı</option>
                        <option value="kapatildi">Kapatıldı</option>
                    </select>
                </div>

                <div class="d-flex align-items-center" style="gap: 8px;">
                    <div class="stat-card" style="padding: 4px 10px; font-size: 0.75rem;">
                        <i class="fas fa-file-invoice"></i>
                        <span class="stat-value">{{ stats.toplam || 0 }}</span>
                        <span>Sipariş</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-hashtag"></i> Sipariş No</th>
                                <th><i class="fas fa-truck"></i> Tedarikçi</th>
                                <th><i class="fas fa-calendar"></i> Tarih</th>
                                <th><i class="fas fa-calendar-check"></i> Teslim Tarihi</th>
                                <th><i class="fas fa-money-bill"></i> Toplam Tutar</th>
                                <th><i class="fas fa-truck-loading"></i> Teslimat</th>
                                <th><i class="fas fa-info-circle"></i> Durum</th>
                                <th><i class="fas fa-user"></i> Oluşturan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="9" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="orders.length === 0">
                                <td colspan="9" class="text-center p-4">Sipariş bulunamadı.</td>
                            </tr>
                            <tr v-else v-for="order in orders" :key="order.siparis_id">
                                <td class="actions">
                                    <button @click="viewOrder(order)" class="btn btn-info btn-sm mr-1"
                                        title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a :href="'generate_pdf.php?id=' + order.siparis_id" target="_blank" class="btn btn-danger btn-sm mr-1"
                                        title="PDF İndir">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                    <?php if (yetkisi_var('action:satinalma_siparisler:update')): ?>
                                        <button @click="openModal(order)" class="btn btn-warning btn-sm mr-1"
                                            title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:satinalma_siparisler:delete')): ?>
                                        <button @click="deleteOrder(order.siparis_id)" class="btn btn-danger btn-sm"
                                            title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><strong>{{ order.siparis_no }}</strong></td>
                                <td>{{ order.tedarikci_adi }}</td>
                                <td>{{ formatDate(order.siparis_tarihi) }}</td>
                                <td>{{ order.istenen_teslim_tarihi ? formatDate(order.istenen_teslim_tarihi) : '-' }}
                                </td>
                                <td><strong>{{ formatCurrency(order.toplam_tutar, order.para_birimi) }}</strong></td>
                                <td style="width: 100px;">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted mb-1" style="font-size: 0.65rem;">%{{ parseFloat(order.teslimat_yuzdesi || 0).toFixed(1) }}</small>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                :style="{ width: (order.teslimat_yuzdesi || 0) + '%' }" 
                                                :aria-valuenow="order.teslimat_yuzdesi || 0" 
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" :class="'badge-' + order.durum">{{ getDurumText(order.durum)
                                        }}</span>
                                </td>
                                <td>
                                    <small>{{ order.olusturan_adi }}</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0">
                        <label><i class="fas fa-list"></i> Sayfa başına: </label>
                        <select v-model="limit" @change="loadOrders(1)" class="form-control d-inline-block"
                            style="width: auto; margin-left: 8px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <small class="text-muted mr-3">{{ paginationInfo }}</small>
                        <nav>
                            <ul class="pagination mb-0">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="loadOrders(currentPage - 1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item"
                                    :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadOrders(page)">{{ page }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="loadOrders(currentPage + 1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Modal -->
        <div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ modal.title }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                            <span style="font-size: 1.5rem;">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveOrder">
                            <!-- Tedarikçi ve Tarih Bilgileri -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-building"></i> Sipariş Bilgileri
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group mb-0">
                                            <label>Tedarikçi *</label>
                                            <select class="form-control" v-model="modal.data.tedarikci_id"
                                                @change="loadMaterialsForSupplier" required :disabled="modal.isEdit">
                                                <option value="">Tedarikçi Seçin</option>
                                                <option v-for="supplier in suppliers" :key="supplier.tedarikci_id"
                                                    :value="supplier.tedarikci_id">
                                                    {{ supplier.tedarikci_adi }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label>İstenen Teslim Tarihi *</label>
                                            <input type="date" class="form-control"
                                                v-model="modal.data.istenen_teslim_tarihi" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label>Durum</label>
                                            <select class="form-control" v-model="modal.data.mappedDurum">
                                                <option value="olusturuldu">Oluşturuldu</option>
                                                <option value="yollandi">Tedarikçiye Yollandı</option>
                                                <option value="kapatildi">Kapatıldı</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sipariş Kalemleri -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-boxes"></i> Sipariş Kalemleri
                                    <span v-if="modal.data.kalemler.length > 0"
                                        style="background: #5a3d7a; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-left: auto;">
                                        {{ modal.data.kalemler.length }} kalem
                                    </span>
                                </div>

                                <!-- Tedarikçi Seçilmedi Uyarısı -->
                                <div v-if="!modal.data.tedarikci_id" class="empty-state"
                                    style="background: #f5f5f5; border-radius: 6px;">
                                    <i class="fas fa-hand-pointer"></i>
                                    <p>Malzeme eklemek için önce tedarikçi seçin.</p>
                                </div>

                                <div v-else>
                                    <!-- Malzeme Ekleme Dropdown -->
                                    <div class="material-select-wrapper mb-3">
                                        <select v-model="selectedMaterial" @change="addMaterial">
                                            <option value="">+ Malzeme Ekle</option>
                                            <option v-for="m in availableMaterials" :key="m.malzeme_kodu" :value="m">
                                                {{ m.malzeme_adi }} ({{ formatCurrency(m.birim_fiyat, m.para_birimi) }})
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Kalem Listesi -->
                                    <div v-if="modal.data.kalemler.length > 0" class="row no-gutters px-2 mb-1 text-muted d-flex flex-nowrap" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; width: 100%;">
                                        <div :class="modal.isEdit ? 'col-3' : 'col-4'" class="flex-grow-1">Malzeme</div>
                                        <div style="width: 100px;" class="ml-1">Miktar</div>
                                        <div style="width: 110px;" class="ml-1">Birim Fiyat</div>
                                        <div style="width: 100px;" class="ml-1 text-center">Toplam</div>
                                        <div style="width: 90px;" class="ml-1" v-if="modal.isEdit">Teslim</div>
                                        <div style="width: 30px;" class="ml-1"></div>
                                    </div>

                                    <div v-for="(kalem, index) in modal.data.kalemler" :key="index" class="kalem-row py-1 px-2">
                                        <div class="row no-gutters align-items-center flex-nowrap">
                                            <div :class="modal.isEdit ? 'col-3' : 'col-4'" class="flex-grow-1" style="min-width: 0;">
                                                <div class="malzeme-name" :title="kalem.malzeme_adi">{{ kalem.malzeme_adi }}</div>
                                            </div>
                                            <div style="width: 100px;" class="ml-1">
                                                <div class="input-group input-group-sm flex-nowrap">
                                                    <input type="number" class="form-control form-control-sm px-1"
                                                        v-model.number="kalem.miktar"
                                                        @input="calculateKalemTotal(kalem)" min="0.01" step="0.01">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text px-1">{{ kalem.birim.substring(0,3) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="width: 110px;" class="ml-1">
                                                <div class="input-group input-group-sm flex-nowrap">
                                                    <input type="number" class="form-control form-control-sm px-1"
                                                        v-model.number="kalem.birim_fiyat"
                                                        @input="calculateKalemTotal(kalem)" min="0" step="0.01">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text px-1">{{ kalem.para_birimi }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="width: 100px;" class="ml-1 text-center">
                                                <div class="kalem-total" style="font-size: 0.75rem; white-space: nowrap;">{{ formatCurrency(kalem.toplam_fiyat, kalem.para_birimi) }}</div>
                                            </div>
                                            <div style="width: 90px;" class="ml-1" v-if="modal.isEdit">
                                                <input type="number" class="form-control form-control-sm px-1"
                                                    v-model.number="kalem.teslim_edilen_miktar" min="0" step="0.01"
                                                    placeholder="0">
                                            </div>
                                            <div style="width: 30px;" class="ml-1 text-right">
                                                <div class="remove-kalem ml-auto" @click="removeKalem(index)" style="width: 22px; height: 22px; font-size: 0.8rem;">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Boş Liste Uyarısı -->
                                    <div v-if="modal.data.kalemler.length === 0" class="empty-state"
                                        style="background: #f5f5f5; border-radius: 6px;">
                                        <i class="fas fa-box-open"></i>
                                        <p>Henüz sipariş kalemi eklenmedi. Yukarıdan malzeme seçerek ekleyin.</p>
                                    </div>

                                    <!-- Toplam Bar -->
                                    <div class="order-total-bar" v-if="modal.data.kalemler.length > 0">
                                        <div class="d-flex align-items-center" style="gap: 15px; width: 100%;">
                                            <div class="total-label" style="white-space: nowrap;">
                                                <i class="fas fa-calculator mr-2"></i> Sipariş Toplamı
                                            </div>
                                            <div class="total-value" style="margin-right: auto;">{{ formatCurrency(calculateTotal(), modal.data.para_birimi || 'TRY') }}</div>
                                            
                                            <!-- Delivery Progress -->
                                            <div v-if="modal.isEdit" class="d-flex align-items-center" style="gap: 10px; background: rgba(255,255,255,0.15); padding: 4px 10px; border-radius: 4px;">
                                                <div class="small text-white" style="white-space: nowrap;">
                                                    <i class="fas fa-truck-loading"></i> Teslimat: 
                                                    <strong>%{{ calculateDeliveryPercentage() }}</strong>
                                                </div>
                                                <div class="progress" style="width: 100px; height: 8px; background-color: rgba(255,255,255,0.3);">
                                                    <div class="progress-bar bg-warning" role="progressbar" 
                                                        :style="{ width: calculateDeliveryPercentage() + '%' }" 
                                                        :aria-valuenow="calculateDeliveryPercentage()" 
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Açıklama -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-comment-alt"></i> Notlar
                                </div>
                                <div class="form-group mb-0">
                                    <textarea class="form-control" v-model="modal.data.aciklama" rows="2"
                                        placeholder="Sipariş ile ilgili notlarınızı buraya yazabilirsiniz..."></textarea>
                                </div>
                            </div>

                            <!-- Butonlar -->
                            <div class="modal-footer-custom">
                                <button type="button" class="btn btn-cancel" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i> İptal
                                </button>
                                <button type="submit" class="btn btn-save" :disabled="modal.data.kalemler.length === 0">
                                    <i class="fas fa-save mr-1"></i> Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>



        <!-- View Order Modal -->
        <div class="modal fade" id="viewOrderModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sipariş Detayı: {{ viewData.siparis_no }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Sipariş Bilgileri -->
                        <div class="view-info-section">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="view-info-row">
                                        <span class="view-info-label">Tedarikçi:</span>
                                        <span class="view-info-value">{{ viewData.tedarikci_adi }}</span>
                                    </div>
                                    <div class="view-info-row">
                                        <span class="view-info-label">Sipariş Tarihi:</span>
                                        <span class="view-info-value">{{ formatDate(viewData.siparis_tarihi) }}</span>
                                    </div>
                                    <div class="view-info-row">
                                        <span class="view-info-label">Teslim Tarihi:</span>
                                        <span class="view-info-value">{{ viewData.istenen_teslim_tarihi ?
                                            formatDate(viewData.istenen_teslim_tarihi) : '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="view-info-row">
                                        <span class="view-info-label">Durum:</span>
                                        <span class="view-info-value">
                                            <span class="status-badge" :class="viewData.durum">{{
                                                getDurumText(viewData.durum) }}</span>
                                        </span>
                                    </div>
                                    <div class="view-info-row">
                                        <span class="view-info-label">Toplam Tutar:</span>
                                        <span class="view-info-value" style="font-weight: 600; color: #2c5282;">{{
                                            formatCurrency(viewData.toplam_tutar, viewData.para_birimi) }}</span>
                                    </div>
                                    <div class="view-info-row">
                                        <span class="view-info-label">Oluşturan:</span>
                                        <span class="view-info-value">{{ viewData.olusturan_adi }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sipariş Kalemleri -->
                        <div class="view-info-section">
                            <h6 style="font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 0.75rem;">
                                Sipariş Kalemleri
                                <span v-if="viewData.kalemler"
                                    style="background: #2c5282; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; margin-left: 6px;">
                                    {{ viewData.kalemler.length }}
                                </span>
                            </h6>
                            <table class="table view-items-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Malzeme</th>
                                        <th>Miktar</th>
                                        <th>Birim Fiyat</th>
                                        <th>Toplam</th>
                                        <th>Teslim</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="kalem in viewData.kalemler" :key="kalem.kalem_id">
                                        <td>{{ kalem.malzeme_adi }}</td>
                                        <td>{{ kalem.miktar }} {{ kalem.birim }}</td>
                                        <td>{{ formatCurrency(kalem.birim_fiyat, kalem.para_birimi) }}</td>
                                        <td style="font-weight: 600;">{{ formatCurrency(kalem.toplam_fiyat,
                                            kalem.para_birimi) }}</td>
                                        <td>{{ kalem.teslim_edilen_miktar || 0 }} / {{ kalem.miktar }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Açıklama -->
                        <div v-if="viewData.aciklama" class="view-info-section">
                            <h6 style="font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 0.5rem;">
                                Açıklama</h6>
                            <p style="font-size: 0.85rem; color: #333; margin: 0;">{{ viewData.aciklama }}</p>
                        </div>

                        <!-- Durum Güncelleme -->
                        <?php if (yetkisi_var('action:satinalma_siparisler:update')): ?>
                            <div class="status-update-section">
                                <div class="form-inline">
                                    <label class="mr-2">Durumu Güncelle:</label>
                                    <select class="form-control form-control-sm mr-2" v-model="newStatus"
                                        style="border-radius: 4px;">
                                        <option value="olusturuldu">Oluşturuldu</option>
                                        <option value="yollandi">Tedarikçiye Yollandı</option>
                                        <option value="kapatildi">Kapatıldı</option>
                                    </select>
                                    <button class="btn btn-sm btn-success" @click="updateStatus(viewData.siparis_id)"
                                        style="border-radius: 4px;">
                                        <i class="fas fa-check"></i> Güncelle
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                    </div>
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
                    orders: [],
                    suppliers: [],
                    supplierMaterials: [],
                    loading: false,
                    alert: { message: '', type: '' },
                    search: '',
                    durumFilter: '',
                    tedarikciFilter: '',
                    currentPage: 1,
                    totalPages: 1,
                    totalOrders: 0,
                    limit: 10,
                    stats: {},
                    modal: {
                        title: '',
                        isEdit: false,
                        data: {
                            siparis_id: '',
                            tedarikci_id: '',
                            istenen_teslim_tarihi: '',
                            durum: 'olusturuldu',
                            aciklama: '',
                            para_birimi: 'TRY',
                            kalemler: []
                        }
                    },
                    viewData: {},
                    newStatus: '',
                    selectedMaterial: '',
                    searchTimeout: null
                }
            },
            computed: {
                paginationInfo() {
                    if (this.totalOrders <= 0) return 'Kayıt yok';
                    const start = (this.currentPage - 1) * this.limit + 1;
                    const end = Math.min(this.currentPage * this.limit, this.totalOrders);
                    return `${start}-${end} / ${this.totalOrders}`;
                },
                pageNumbers() {
                    const pages = [];
                    const start = Math.max(1, this.currentPage - 2);
                    const end = Math.min(this.totalPages, this.currentPage + 2);
                    for (let i = start; i <= end; i++) pages.push(i);
                    return pages;
                },
                availableMaterials() {
                    const usedCodes = this.modal.data.kalemler.map(k => k.malzeme_kodu);
                    return this.supplierMaterials.filter(m => !usedCodes.includes(m.malzeme_kodu));
                }
            },
            methods: {
                showAlert(message, type) {
                    this.alert = { message, type };
                    setTimeout(() => { this.alert.message = ''; }, 4000);
                },
                debounceSearch() {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => this.loadOrders(1), 300);
                },
                loadOrders(page = 1) {
                    this.loading = true;
                    this.currentPage = page;

                    let url = `api_islemleri/satinalma_siparisler_islemler.php?action=get_all_orders&page=${page}&limit=${this.limit}`;
                    if (this.search) url += `&search=${encodeURIComponent(this.search)}`;
                    if (this.durumFilter) url += `&durum=${this.durumFilter}`;
                    if (this.tedarikciFilter) url += `&tedarikci_id=${this.tedarikciFilter}`;

                    fetch(url)
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.orders = data.data;
                                this.totalOrders = data.total;
                                this.totalPages = data.pages;
                            } else {
                                this.showAlert(data.message || 'Hata oluştu', 'danger');
                            }
                            this.loading = false;
                        })
                        .catch(() => {
                            this.showAlert('Siparişler yüklenirken hata oluştu', 'danger');
                            this.loading = false;
                        });
                },
                loadSuppliers() {
                    fetch('api_islemleri/satinalma_siparisler_islemler.php?action=get_suppliers')
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') this.suppliers = data.data || [];
                        });
                },
                loadStats() {
                    fetch('api_islemleri/satinalma_siparisler_islemler.php?action=get_stats')
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') this.stats = data.data || {};
                        });
                },
                loadMaterialsForSupplier() {
                    if (!this.modal.data.tedarikci_id) {
                        this.supplierMaterials = [];
                        return;
                    }

                    fetch(`api_islemleri/satinalma_siparisler_islemler.php?action=get_materials_for_supplier&tedarikci_id=${this.modal.data.tedarikci_id}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') this.supplierMaterials = data.data || [];
                        });
                },
                openModal(order) {
                    if (order) {
                        // Edit mode - fetch full order details
                        fetch(`api_islemleri/satinalma_siparisler_islemler.php?action=get_order&id=${order.siparis_id}`)
                            .then(r => r.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    const o = data.data;
                                    this.modal.data = {
                                        siparis_id: o.siparis_id,
                                        tedarikci_id: o.tedarikci_id,
                                        istenen_teslim_tarihi: o.istenen_teslim_tarihi || '',
                                        durum: o.durum, // Keep original status
                                        mappedDurum: this.getMappedStatus(o.durum), // Use mapped status for dropdown
                                        aciklama: o.aciklama || '',
                                        para_birimi: o.para_birimi || 'TRY',
                                        kalemler: (o.kalemler || []).map(k => ({
                                            malzeme_kodu: k.malzeme_kodu,
                                            malzeme_adi: k.malzeme_adi,
                                            miktar: parseFloat(k.miktar),
                                            birim: k.birim,
                                            birim_fiyat: parseFloat(k.birim_fiyat),
                                            para_birimi: k.para_birimi,
                                            toplam_fiyat: parseFloat(k.toplam_fiyat),
                                            teslim_edilen_miktar: parseFloat(k.teslim_edilen_miktar || 0),
                                            aciklama: k.aciklama || ''
                                        }))
                                    };
                                    this.modal.title = 'Sipariş Düzenle: ' + o.siparis_no;
                                    this.modal.isEdit = true;
                                    this.loadMaterialsForSupplier();
                                    $('#orderModal').modal('show');
                                }
                            });
                    } else {
                        // New order
                        this.modal.data = {
                            siparis_id: '',
                            tedarikci_id: '',
                            istenen_teslim_tarihi: '',
                            durum: 'olusturuldu',
                            mappedDurum: 'olusturuldu',
                            aciklama: '',
                            para_birimi: 'TRY',
                            kalemler: []
                        };
                        this.modal.title = 'Yeni Satınalma Siparişi';
                        this.modal.isEdit = false;
                        this.supplierMaterials = [];
                        $('#orderModal').modal('show');
                    }
                },
                addMaterial() {
                    if (!this.selectedMaterial) return;

                    // Set order currency from first item
                    if (this.modal.data.kalemler.length === 0) {
                        this.modal.data.para_birimi = this.selectedMaterial.para_birimi || 'TRY';
                    }

                    this.modal.data.kalemler.push({
                        malzeme_kodu: this.selectedMaterial.malzeme_kodu,
                        malzeme_adi: this.selectedMaterial.malzeme_adi,
                        miktar: 1,
                        birim: this.selectedMaterial.birim || 'adet',
                        birim_fiyat: parseFloat(this.selectedMaterial.birim_fiyat) || 0,
                        para_birimi: this.modal.data.para_birimi, // Use order currency for all items
                        toplam_fiyat: parseFloat(this.selectedMaterial.birim_fiyat) || 0,
                        teslim_edilen_miktar: 0,
                        aciklama: ''
                    });

                    this.selectedMaterial = '';
                },
                removeKalem(index) {
                    this.modal.data.kalemler.splice(index, 1);
                },
                calculateKalemTotal(kalem) {
                    kalem.toplam_fiyat = (kalem.miktar || 0) * (kalem.birim_fiyat || 0);
                },
                calculateTotal() {
                    return this.modal.data.kalemler.reduce((sum, k) => sum + (k.toplam_fiyat || 0), 0);
                },
                calculateDeliveryPercentage() {
                    if (!this.modal.data.kalemler || this.modal.data.kalemler.length === 0) return 0;
                    
                    let totalQty = 0;
                    let totalDelivered = 0;
                    
                    this.modal.data.kalemler.forEach(k => {
                        totalQty += parseFloat(k.miktar) || 0;
                        totalDelivered += parseFloat(k.teslim_edilen_miktar) || 0;
                    });
                    
                    if (totalQty === 0) return 0;
                    
                    const percent = (totalDelivered / totalQty) * 100;
                    return Math.min(100, Math.max(0, percent)).toFixed(1);
                },
                saveOrder() {
                    const formData = new FormData();
                    formData.append('action', this.modal.isEdit ? 'update_order' : 'add_order');

                    if (this.modal.isEdit) {
                        formData.append('siparis_id', this.modal.data.siparis_id);
                    }

                    formData.append('tedarikci_id', this.modal.data.tedarikci_id);
                    formData.append('siparis_tarihi', new Date().toISOString().split('T')[0]);
                    formData.append('istenen_teslim_tarihi', this.modal.data.istenen_teslim_tarihi);
                    // Map the selected status back to appropriate database value
                    let saveStatus = this.getRealStatusFromMapped(this.modal.data.mappedDurum);
                    formData.append('durum', saveStatus);
                    formData.append('aciklama', this.modal.data.aciklama);
                    formData.append('para_birimi', this.modal.data.para_birimi);
                    formData.append('kalemler', JSON.stringify(this.modal.data.kalemler));

                    fetch('api_islemleri/satinalma_siparisler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.showAlert(data.message, 'success');
                                $('#orderModal').modal('hide');
                                this.loadOrders(this.currentPage);
                                this.loadStats();
                            } else {
                                this.showAlert(data.message || 'Hata oluştu', 'danger');
                            }
                        })
                        .catch(() => this.showAlert('İşlem sırasında hata oluştu', 'danger'));
                },
                viewOrder(order) {
                    fetch(`api_islemleri/satinalma_siparisler_islemler.php?action=get_order&id=${order.siparis_id}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.viewData = data.data;
                                this.newStatus = this.getMappedStatus(data.data.durum);
                                $('#viewOrderModal').modal('show');
                            }
                        });
                },

                updateStatus(siparisId) {
                    const formData = new FormData();
                    formData.append('action', 'update_status');
                    formData.append('siparis_id', siparisId);
                    formData.append('durum', this.getRealStatusFromMapped(this.newStatus));

                    fetch('api_islemleri/satinalma_siparisler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.showAlert(data.message, 'success');
                                this.viewData.durum = this.getRealStatusFromMapped(this.newStatus);
                                this.loadOrders(this.currentPage);
                                this.loadStats();
                            } else {
                                this.showAlert(data.message || 'Hata oluştu', 'danger');
                            }
                        });
                },
                deleteOrder(id) {
                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: 'Bu siparişi silmek istediğinizden emin misiniz?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Evet, sil!',
                        cancelButtonText: 'İptal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData();
                            formData.append('action', 'delete_order');
                            formData.append('siparis_id', id);

                            fetch('api_islemleri/satinalma_siparisler_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        this.showAlert(data.message, 'success');
                                        this.loadOrders(this.currentPage);
                                        this.loadStats();
                                    } else {
                                        this.showAlert(data.message || 'Hata oluştu', 'danger');
                                    }
                                });
                        }
                    });
                },
                getDurumText(durum) {
                    const map = {
                        'taslak': 'Oluşturuldu',
                        'onaylandi': 'Oluşturuldu',
                        'gonderildi': 'Tedarikçiye Yollandı',
                        'kismen_teslim': 'Tedarikçiye Yollandı',
                        'tamamlandi': 'Tedarikçiye Yollandı',
                        'iptal': 'İptal',
                        'olusturuldu': 'Oluşturuldu',
                        'yollandi': 'Tedarikçiye Yollandı',
                        'kapatildi': 'Kapatıldı'
                    };
                    return map[durum] || durum;
                },
                getMappedStatus(durum) {
                    // Map old status values to new dropdown options
                    if (['taslak', 'onaylandi'].includes(durum)) {
                        return 'olusturuldu';
                    } else if (['gonderildi', 'kismen_teslim', 'tamamlandi'].includes(durum)) {
                        return 'yollandi';
                    } else if (durum === 'kapatildi') {
                        return 'kapatildi';
                    }
                    // Return the original value if it's already a new value
                    return durum;
                },
                getRealStatusFromMapped(mappedDurum) {
                    // Map mapped status values back to real database values
                    if (mappedDurum === 'olusturuldu') {
                        return 'taslak';
                    } else if (mappedDurum === 'yollandi') {
                        return 'gonderildi';
                    } else if (mappedDurum === 'kapatildi') {
                        return 'kapatildi';
                    }
                    // Return the original value if it's already a real value
                    return mappedDurum;
                },
                formatDate(dateString) {
                    if (!dateString) return '-';
                    return new Date(dateString).toLocaleDateString('tr-TR');
                },
                formatCurrency(value, currency = 'TRY') {
                    const num = parseFloat(value) || 0;
                    const symbols = { 'TRY': '₺', 'TL': '₺', 'USD': '$', 'EUR': '€' };
                    return `${num.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${symbols[currency] || currency}`;
                }
            },
            mounted() {
                this.loadSuppliers();
                this.loadOrders();
                this.loadStats();
            }
        });

        app.mount('#app');
    </script>
</body>

</html>

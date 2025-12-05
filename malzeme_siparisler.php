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
if (!yetkisi_var('page:view:malzeme_siparisler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}


?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Malzeme Siparişleri - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
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
                <h1>Malzeme Siparişleri</h1>
                <p>Tedarikçiye verilen malzeme siparişlerini takip edin</p>
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <?php if (yetkisi_var('action:malzeme_siparisler:create')): ?>
                    <button @click="openModal(null)" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Sipariş
                        Ekle</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="mb-2 mb-md-0 mr-md-3"><i class="fas fa-list"></i> Sipariş Listesi</h2>
                <div class="search-container w-100 w-md-25">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" v-model="search" @input="loadOrders(1)"
                            placeholder="Ara...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-box"></i> Malzeme</th>
                                <th><i class="fas fa-truck"></i> Tedarikçi</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Miktar</th>
                                <th><i class="fas fa-calendar"></i> Sipariş Tarihi</th>
                                <th><i class="fas fa-calendar-check"></i> Teslim Edileceği Bildirilen Tarih</th>
                                <th><i class="fas fa-info-circle"></i> Durum</th>
                                <th><i class="fas fa-user"></i> Kaydeden</th>
                                <th><i class="fas fa-comment"></i> Açıklama</th>
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
                                    <?php if (yetkisi_var('action:malzeme_siparisler:delete')): ?>
                                        <button @click="deleteOrder(order.siparis_id)" class="btn btn-danger btn-sm"><i
                                                class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                                <td><strong>#{{ order.siparis_id }}</strong></td>
                                <td><strong>{{ order.malzeme_ismi }}</strong></td>
                                <td>{{ order.tedarikci_ismi }}</td>
                                <td>{{ order.miktar }}</td>
                                <td>{{ formatDate(order.siparis_tarihi) }}</td>
                                <td>{{ order.teslim_tarihi ? formatDate(order.teslim_tarihi) : '-' }}</td>
                                <td>
                                    <span class="badge" :class="getDurumClass(order.durum)">
                                        {{ getDurumText(order.durum) }}
                                    </span>

                                </td>
                                <td>
                                    <div>{{ order.kaydeden_personel_adi }}</div>
                                    <small class="text-muted">#{{ order.kaydeden_personel_id }}</small>
                                </td>
                                <td>{{ order.aciklama }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0 w-100 w-md-auto">
                        <label for="recordsPerPage"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                        <select v-model="limit" @change="loadOrders(1)" class="form-control d-inline-block"
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
                                    <a class="page-link" href="#" @click.prevent="loadOrders(currentPage - 1)"><i
                                            class="fas fa-chevron-left"></i> Önceki</a>
                                </li>
                                <li v-if="currentPage > 3" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadOrders(1)">1</a>
                                </li>
                                <li v-if="currentPage > 4" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item"
                                    :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadOrders(page)">{{ page }}</a>
                                </li>
                                <li v-if="currentPage < totalPages - 3" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-if="currentPage < totalPages - 2" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadOrders(totalPages)">{{ totalPages
                                        }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="loadOrders(currentPage + 1)">Sonraki
                                        <i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Modal -->
        <div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header"
                        style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title">{{ modal.title }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveOrder">
                            <input type="hidden" v-model="modal.data.siparis_id">
                            <div class="form-group mb-3">
                                <label>Malzeme *</label>
                                <select class="form-control" v-model="modal.data.malzeme_kodu" @change="loadSuppliers"
                                    required>
                                    <option value="">Malzeme Seçin</option>
                                    <option v-for="material in materials" :value="material.malzeme_kodu">
                                        {{ material.malzeme_kodu }} - {{ material.malzeme_ismi }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Tedarikçi *</label>
                                <select class="form-control" v-model="modal.data.tedarikci_id" required
                                    :disabled="!modal.data.malzeme_kodu">
                                    <option value="">Tedarikçi Seçin</option>
                                    <option v-for="supplier in suppliers" :value="supplier.tedarikci_id">
                                        {{ supplier.tedarikci_ismi }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Miktar *</label>
                                <input type="number" class="form-control" v-model="modal.data.miktar" required
                                    min="0.01" step="0.01">
                            </div>
                            <input type="hidden" v-model="modal.data.durum" value="siparis_verildi">
                            <div class="form-group mb-3">
                                <label>Teslim Edileceği Bildirilen Tarih *</label>
                                <input type="date" class="form-control" v-model="modal.data.teslim_tarihi" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Açıklama</label>
                                <textarea class="form-control" v-model="modal.data.aciklama" rows="3"></textarea>
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                        class="fas fa-times"></i> İptal</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                                    Kaydet</button>
                            </div>
                        </form>
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
                    orders: [],
                    materials: [],
                    suppliers: [],
                    loading: false,
                    alert: { message: '', type: '' },
                    search: '',
                    durumFilter: '',
                    currentPage: 1,
                    totalPages: 1,
                    totalOrders: 0,
                    limit: 10,
                    modal: { title: '', data: {} }
                }
            },
            computed: {
                paginationInfo() {
                    if (this.totalPages <= 0 || this.totalOrders <= 0) {
                        return 'Gösterilecek kayıt yok';
                    }
                    const startRecord = (this.currentPage - 1) * this.limit + 1;
                    const endRecord = Math.min(this.currentPage * this.limit, this.totalOrders);
                    return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.totalOrders} kayıttan`;
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
                loadOrders(page = 1) {
                    this.loading = true;
                    this.currentPage = page;

                    let url = `api_islemleri/malzeme_siparisler_islemler.php?action=get_all_orders&page=${page}&limit=${this.limit}`;
                    if (this.search) url += `&search=${encodeURIComponent(this.search)}`;
                    if (this.durumFilter) url += `&durum=${this.durumFilter}`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.orders = data.data;
                                this.totalOrders = data.total;
                                this.totalPages = data.pages;
                            } else {
                                this.showAlert(data.message || 'Siparişler yüklenirken hata oluştu', 'danger');
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            this.showAlert('Siparişler yüklenirken hata oluştu', 'danger');
                            this.loading = false;
                        });
                },
                loadMaterials() {
                    fetch('api_islemleri/malzeme_siparisler_islemler.php?action=get_materials')
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.materials = data.data || [];
                            }
                        });
                },
                loadSuppliers() {
                    if (!this.modal.data.malzeme_kodu) {
                        this.suppliers = [];
                        this.modal.data.tedarikci_id = '';
                        return;
                    }

                    fetch(`api_islemleri/malzeme_siparisler_islemler.php?action=get_suppliers_for_material&material_code=${this.modal.data.malzeme_kodu}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.suppliers = data.data || [];
                            }
                        });
                },
                openModal(order) {
                    if (order) {
                        // Ensure dates are in YYYY-MM-DD format for date input fields
                        this.modal.data = {
                            ...order,
                            teslim_tarihi: order.teslim_tarihi ? order.teslim_tarihi.split(' ')[0] : ''
                        };
                    } else {
                        this.modal.data = {
                            siparis_id: '',
                            malzeme_kodu: '',
                            tedarikci_id: '',
                            miktar: '',
                            teslim_tarihi: '',
                            durum: 'siparis_verildi',
                            aciklama: ''
                        };
                    }
                    this.modal.title = order ? 'Sipariş Düzenle' : 'Yeni Sipariş Ekle';
                    this.loadMaterials();
                    if (order && order.malzeme_kodu) {
                        this.loadSuppliers();
                    }
                    $('#orderModal').modal('show');
                },
                saveOrder() {
                    const formData = new FormData();
                    Object.keys(this.modal.data).forEach(key => {
                        formData.append(key, this.modal.data[key]);
                    });

                    formData.append('action', this.modal.data.siparis_id ? 'update_order' : 'add_order');

                    fetch('api_islemleri/malzeme_siparisler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.showAlert(data.message, 'success');
                                $('#orderModal').modal('hide');
                                this.loadOrders(this.currentPage);
                            } else {
                                this.showAlert(data.message || 'İşlem sırasında hata oluştu', 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('İşlem sırasında hata oluştu', 'danger');
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

                            fetch('api_islemleri/malzeme_siparisler_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        this.showAlert(data.message, 'success');
                                        this.loadOrders(this.currentPage);
                                    } else {
                                        this.showAlert(data.message || 'Silme sırasında hata oluştu', 'danger');
                                    }
                                })
                                .catch(error => {
                                    this.showAlert('Silme sırasında hata oluştu', 'danger');
                                });
                        }
                    });
                },
                toggleDurumFilter(durum) {
                    this.durumFilter = this.durumFilter === durum ? '' : durum;
                    this.loadOrders(1);
                },
                getDurumClass(durum) {
                    switch (durum) {
                        case 'olusturuldu': return 'badge-info';
                        case 'siparis_verildi': return 'badge-warning';
                        case 'iptal_edildi': return 'badge-danger';
                        case 'teslim_edildi': return 'badge-success';
                        default: return 'badge-secondary';
                    }
                },
                getDurumText(durum) {
                    switch (durum) {
                        case 'olusturuldu': return 'Oluşturuldu';
                        case 'siparis_verildi': return 'Sipariş Verildi';
                        case 'iptal_edildi': return 'İptal Edildi';
                        case 'teslim_edildi': return 'Teslim Edildi';
                        default: return durum;
                    }
                },
                formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('tr-TR');
                }
            },
            mounted() {
                this.loadOrders();
                this.loadMaterials();
            }
        });

        app.mount('#app');
    </script>
</body>

</html>
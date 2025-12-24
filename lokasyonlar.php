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
if (!yetkisi_var('page:view:lokasyonlar')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Calculate total locations
$total_result = $connection->query("SELECT COUNT(*) as total FROM lokasyonlar");
$total_locations = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Lokasyonlar Yönetimi - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <!-- Vue.js 3 CDN -->
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
            padding: 10px;
        }

        .page-header {
            margin-bottom: 15px;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2px;
            color: var(--text-primary);
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .card {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 15px;
            overflow: hidden;
        }

        .card-header {
            padding: 12px 15px;
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
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 700;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
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
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
            border-left: 4px solid;
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
            text-align: left;
        }

        .table th i {
            margin-right: 6px;
        }

        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
            text-align: left;
        }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .actions .btn {
            padding: 6px 10px;
            border-radius: 18px;
        }

        .form-grid,
        .form-group,
        .form-actions,
        .table-wrapper,
        .stat-card {
            /* These are replaced by Bootstrap classes and Vue.js templates */
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
            <div class="d-flex flex-column align-items-start">
                <h1>Lokasyonlar Yönetimi</h1>
                <p>Depo ve raf tanımlamaları</p>
            </div>
        </div>

        <div class="alert alert-info shadow-sm border-0 d-flex align-items-center" role="alert"
            style="background-color: #e3f2fd; color: #0d47a1;">
            <i class="fas fa-info-circle mr-3" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Bilgi:</strong> Burada tanımladığınız lokasyonlar, <a href="urunler.php" class="alert-link"
                    style="text-decoration: underline;">Ürünler</a> ve <a href="malzemeler.php" class="alert-link"
                    style="text-decoration: underline;">Malzemeler</a> sayfalarında depo/raf bilgisi olarak
                kullanılacaktır.
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert" style="text-align: left;">
            {{ alert.message }}
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-start align-items-center py-2 px-3">
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <!-- Yeni Lokasyon Ekle Butonu -->
                    <?php if (yetkisi_var('action:lokasyonlar:create')): ?>
                        <button @click="openModal(null)" class="btn btn-primary btn-sm"
                            style="font-size: 0.75rem; padding: 4px 10px;"><i class="fas fa-plus"></i> Yeni
                            Lokasyon</button>
                    <?php endif; ?>
                    <!-- Arama Kutusu -->
                    <div class="input-group input-group-sm" style="width: auto; min-width: 180px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="padding: 4px 8px;"><i
                                    class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" v-model="search"
                            @input="loadLocations(1)" placeholder="Lokasyon ara..."
                            style="font-size: 0.75rem; padding: 4px 8px;">
                    </div>
                    <!-- Stat Kartı -->
                    <div class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #4a0e63, #7c2a99); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-warehouse mr-1"></i>
                        <span style="font-weight: 600;">{{ totalLocations }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Lokasyon</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-warehouse"></i> Depo İsmi</th>
                                <th><i class="fas fa-boxes"></i> Raf</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="3" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="locations.length === 0">
                                <td colspan="3" class="text-center p-4">Henüz kayıtlı lokasyon bulunmuyor.</td>
                            </tr>
                            <tr v-for="location in locations" :key="location.lokasyon_id">
                                <td class="actions">
                                    <?php if (yetkisi_var('action:lokasyonlar:edit')): ?>
                                        <button @click="openModal(location)" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:lokasyonlar:delete')): ?>
                                        <button @click="deleteLocation(location.lokasyon_id)" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><strong>{{ location.depo_ismi }}</strong></td>
                                <td>{{ location.raf }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0 w-100 w-md-auto">
                        <label for="recordsPerPage"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                        <select v-model="limit" @change="loadLocations(1)" class="form-control d-inline-block"
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
                                    <a class="page-link" href="#" @click.prevent="loadLocations(currentPage - 1)"><i
                                            class="fas fa-chevron-left"></i> Önceki</a>
                                </li>
                                <li v-if="currentPage > 3" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadLocations(1)">1</a>
                                </li>
                                <li v-if="currentPage > 4" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item"
                                    :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadLocations(page)">{{ page }}</a>
                                </li>
                                <li v-if="currentPage < totalPages - 3" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-if="currentPage < totalPages - 2" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadLocations(totalPages)">{{
                                        totalPages }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#"
                                        @click.prevent="loadLocations(currentPage + 1)">Sonraki <i
                                            class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Modal -->
        <div class="modal fade" id="locationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form @submit.prevent="saveLocation">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title">{{ modal.title }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" v-model="modal.data.lokasyon_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>Depo İsmi *</label>
                                        <input type="text" class="form-control" v-model="modal.data.depo_ismi" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label>Raf *</label>
                                        <input type="text" class="form-control" v-model="modal.data.raf" required>
                                    </div>
                                </div>
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
                    locations: [],
                    loading: false,
                    alert: {
                        message: '',
                        type: ''
                    },
                    search: '',
                    currentPage: 1,
                    totalPages: 1,
                    totalLocations: 0,
                    limit: 10,
                    modal: {
                        title: '',
                        data: {}
                    }
                }
            },
            computed: {
                paginationInfo() {
                    if (this.totalPages <= 0 || this.totalLocations <= 0) {
                        return 'Gösterilecek kayıt yok';
                    }
                    const startRecord = (this.currentPage - 1) * this.limit + 1;
                    const endRecord = Math.min(this.currentPage * this.limit, this.totalLocations);
                    return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.totalLocations} kayıttan`;
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
                    setTimeout(() => {
                        this.alert.message = '';
                    }, 3000);
                },
                loadLocations(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    let url = `api_islemleri/lokasyonlar_islemler.php?action=get_locations&page=${this.currentPage}&limit=${this.limit}&search=${this.search}`;
                    fetch(url)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.locations = response.data.sort((a, b) => {
                                    // First sort by depo_ismi
                                    if (a.depo_ismi < b.depo_ismi) return -1;
                                    if (a.depo_ismi > b.depo_ismi) return 1;
                                    // If depo_ismi is the same, then sort by raf
                                    if (a.raf < b.raf) return -1;
                                    if (a.raf > b.raf) return 1;
                                    return 0;
                                });
                                this.totalPages = response.pagination.total_pages;
                                this.totalLocations = response.pagination.total_locations;
                            } else {
                                this.showAlert('Lokasyonlar yüklenirken hata oluştu.', 'danger');
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            this.showAlert('Lokasyonlar yüklenirken bir hata oluştu.', 'danger');
                            this.loading = false;
                        });
                },
                openModal(location) {
                    if (location) {
                        this.modal.title = 'Lokasyonu Düzenle';
                        this.modal.data = { ...location };
                    } else {
                        this.modal.title = 'Yeni Lokasyon Ekle';
                        this.modal.data = {};
                    }
                    $('#locationModal').modal('show');
                },
                saveLocation() {
                    let action = this.modal.data.lokasyon_id ? 'update_location' : 'add_location';
                    let formData = new FormData();
                    for (let key in this.modal.data) {
                        if (this.modal.data[key] !== undefined && this.modal.data[key] !== null) {
                            formData.append(key, this.modal.data[key]);
                        }
                    }
                    formData.append('action', action);

                    fetch('api_islemleri/lokasyonlar_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#locationModal').modal('hide');
                                this.loadLocations(this.currentPage);
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                        });
                },
                deleteLocation(id) {
                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: "Bu lokasyonu silmek istediğinizden emin misiniz?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Evet, sil!',
                        cancelButtonText: 'İptal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let formData = new FormData();
                            formData.append('action', 'delete_location');
                            formData.append('lokasyon_id', id);

                            fetch('api_islemleri/lokasyonlar_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(response => {
                                    if (response.status === 'success') {
                                        this.showAlert(response.message, 'success');
                                        this.loadLocations(this.currentPage);
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
                this.loadLocations();
            }
        });
        app.mount('#app');
    </script>
</body>

</html>
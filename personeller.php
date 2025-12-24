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
if (!yetkisi_var('page:view:personeller')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Calculate total employees (excluding admin users)
$total_result = $connection->query("SELECT COUNT(*) as total FROM personeller WHERE e_posta NOT IN ('admin@parfum.com', 'admin2@parfum.com')");
$total_employees = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Personel Yönetimi - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="assets/css/stil.css">
    <style>
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
            <div>
                <h1>Personel Yönetimi</h1>
                <p>Personelleri ekleyin, düzenleyin ve yönetin</p>
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-start align-items-center py-2 px-3">
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <!-- Yeni Personel Ekle Butonu -->
                    <?php if (yetkisi_var('action:personeller:create')): ?>
                        <button @click="openModal(null)" class="btn btn-primary btn-sm"
                            style="font-size: 0.75rem; padding: 4px 10px;"><i class="fas fa-plus"></i> Yeni
                            Personel</button>
                    <?php endif; ?>
                    <!-- Arama Kutusu -->
                    <div class="input-group input-group-sm" style="width: auto; min-width: 180px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="padding: 4px 8px;"><i
                                    class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" v-model="search"
                            @input="loadEmployees(1)" placeholder="Personel ara..."
                            style="font-size: 0.75rem; padding: 4px 8px;">
                    </div>
                    <!-- Stat Kartı -->
                    <div class="stat-card-mini"
                        style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #4a0e63, #7c2a99); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                        <i class="fas fa-users mr-1"></i>
                        <span style="font-weight: 600;">{{ overallTotalEmployees }}</span>
                        <span class="ml-1" style="opacity: 0.9;">Personel</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-user"></i> Ad Soyad</th>
                                <th><i class="fas fa-briefcase"></i> Pozisyon</th>
                                <th><i class="fas fa-building"></i> Departman</th>
                                <th><i class="fas fa-phone"></i> Telefon</th>
                                <th><i class="fas fa-phone"></i> Telefon 2</th>
                                <th><i class="fas fa-envelope"></i> E-posta</th>
                                <th><i class="fas fa-id-card"></i> TC Kimlik No</th>
                                <th><i class="fas fa-birthday-cake"></i> Doğum Tarihi</th>
                                <th><i class="fas fa-calendar-plus"></i> İşe Giriş</th>
                                <th><i class="fas fa-wallet"></i> Bordrolu Mu?</th>
                                <th><i class="fas fa-money-bill-wave"></i> Aylık Brüt Ücret</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="11" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="employees.length === 0">
                                <td colspan="11" class="text-center p-4">Henüz kayıtlı personel bulunmuyor.</td>
                            </tr>
                            <tr v-for="employee in employees" :key="employee.personel_id"
                                :class="employee.ad_soyad === 'Admin User' ? 'disabled-row' : ''">
                                <td class="actions">
                                    <?php if (yetkisi_var('action:personeller:edit')): ?>
                                        <button @click="openModal(employee)" class="btn btn-primary btn-sm"
                                            :class="{'disabled': employee.ad_soyad === 'Admin User'}"
                                            :disabled="employee.ad_soyad === 'Admin User'"
                                            :title="employee.ad_soyad === 'Admin User' ? 'Bu kullanıcı düzenlenemez' : ''">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:personeller:permissions')): ?>
                                        <a :href="'personel_yetki.php?id=' + employee.personel_id"
                                            class="btn btn-info btn-sm"
                                            :class="{'disabled': employee.ad_soyad === 'Admin User'}"
                                            :disabled="employee.ad_soyad === 'Admin User'"
                                            :title="employee.ad_soyad === 'Admin User' ? 'Bu kullanıcı düzenlenemez' : 'Yetkileri Düzenle'">
                                            <i class="fas fa-shield-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:personeller:delete')): ?>
                                        <button @click="deleteEmployee(employee.personel_id)" class="btn btn-danger btn-sm"
                                            :class="{'disabled': employee.ad_soyad === 'Admin User'}"
                                            :disabled="employee.ad_soyad === 'Admin User'"
                                            :title="employee.ad_soyad === 'Admin User' ? 'Bu kullanıcı silinemez' : ''">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><strong>{{ employee.ad_soyad }}</strong></td>
                                <td>{{ employee.pozisyon || '-' }}</td>
                                <td>{{ employee.departman || '-' }}</td>
                                <td>{{ employee.telefon || '-' }}</td>
                                <td>{{ employee.telefon_2 || '-' }}</td>
                                <td>{{ employee.e_posta || '-' }}</td>
                                <td>{{ employee.tc_kimlik_no || '-' }}</td>
                                <td>{{ formatDate(employee.dogum_tarihi) }}</td>
                                <td>{{ formatDate(employee.ise_giris_tarihi) }}</td>
                                <td>
                                    <span v-if="employee.bordrolu_calisan_mi == 1"
                                        class="badge badge-success">Evet</span>
                                    <span v-else class="badge badge-danger">Hayır</span>
                                </td>
                                <td>{{ employee.aylik_brut_ucret ? '₺' +
                                    parseFloat(employee.aylik_brut_ucret).toFixed(2) : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0 w-100 w-md-auto">
                        <label for="recordsPerPage"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                        <select v-model="limit" @change="loadEmployees(1)" class="form-control d-inline-block"
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
                                    <a class="page-link" href="#" @click.prevent="loadEmployees(currentPage - 1)"><i
                                            class="fas fa-chevron-left"></i> Önceki</a>
                                </li>
                                <li v-if="currentPage > 3" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadEmployees(1)">1</a>
                                </li>
                                <li v-if="currentPage > 4" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item"
                                    :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadEmployees(page)">{{ page }}</a>
                                </li>
                                <li v-if="currentPage < totalPages - 3" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-if="currentPage < totalPages - 2" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadEmployees(totalPages)">{{
                                        totalPages }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#"
                                        @click.prevent="loadEmployees(currentPage + 1)">Sonraki
                                        <i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Modal -->
        <div class="modal fade" id="employeeModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form @submit.prevent="saveEmployee">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title">{{ modal.title }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" v-model="modal.data.personel_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-user"></i> Ad Soyad *</label>
                                        <input type="text" class="form-control" v-model="modal.data.ad_soyad" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-briefcase"></i> Pozisyon</label>
                                        <input type="text" class="form-control" v-model="modal.data.pozisyon">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-building"></i> Departman</label>
                                        <input type="text" class="form-control" v-model="modal.data.departman">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-calendar-plus"></i> İşe Giriş Tarihi</label>
                                        <input type="date" class="form-control" v-model="modal.data.ise_giris_tarihi">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                v-model="modal.data.bordrolu_calisan_mi" id="bordrolu_calisan_mi"
                                                true-value="1" false-value="0">
                                            <label class="form-check-label" for="bordrolu_calisan_mi"><i
                                                    class="fas fa-wallet"></i> Bordrolu Çalışan
                                                Mı?</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-money-bill-wave"></i> Aylık Brüt Ücret (₺)</label>
                                        <input type="number" step="0.01" class="form-control"
                                            v-model="modal.data.aylik_brut_ucret" placeholder="0.00"
                                            :disabled="!modal.data.bordrolu_calisan_mi">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-phone"></i> Telefon</label>
                                        <input type="text" class="form-control" v-model="modal.data.telefon">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-phone"></i> Telefon 2</label>
                                        <input type="text" class="form-control" v-model="modal.data.telefon_2">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-envelope"></i> E-posta</label>
                                        <input type="email" class="form-control" v-model="modal.data.e_posta">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-id-card"></i> TC Kimlik No</label>
                                        <input type="text" class="form-control" v-model="modal.data.tc_kimlik_no">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-birthday-cake"></i> Doğum Tarihi *</label>
                                        <input type="date" class="form-control" v-model="modal.data.dogum_tarihi"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label><i class="fas fa-map-marker-alt"></i> Adres</label>
                                <textarea class="form-control" v-model="modal.data.adres" rows="2"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label><i class="fas fa-sticky-note"></i> Notlar</label>
                                <textarea class="form-control" v-model="modal.data.notlar" rows="2"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label><i class="fas fa-lock"></i> Şifre</label>
                                <input type="password" class="form-control" v-model="modal.data.sifre"
                                    placeholder="Yeni şifre (boş bırakırsanız değişmez)">
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
                    employees: [],
                    loading: false,
                    alert: {
                        message: '',
                        type: ''
                    },
                    search: '',
                    currentPage: 1,
                    totalPages: 1,
                    totalEmployees: 0,
                    overallTotalEmployees: <?php echo $total_employees; ?>,
                    limit: 10,
                    modal: {
                        title: '',
                        data: {}
                    }
                }
            },
            computed: {
                paginationInfo() {
                    if (this.totalPages <= 0 || this.totalEmployees <= 0) {
                        return 'Gösterilecek kayıt yok';
                    }
                    const startRecord = (this.currentPage - 1) * this.limit + 1;
                    const endRecord = Math.min(this.currentPage * this.limit, this.totalEmployees);
                    return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.totalEmployees} kayıttan`;
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
                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}.${month}.${year}`;
                },
                loadEmployees(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    let url = `api_islemleri/get_employees_ajax.php?page=${this.currentPage}&limit=${this.limit}&search=${this.search}`;
                    fetch(url)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.employees = response.data;
                                this.totalPages = response.pagination.total_pages;
                                this.totalEmployees = response.pagination.total_employees;
                                this.overallTotalEmployees = response.pagination.overall_total_employees;
                            } else {
                                this.showAlert('Personeller yüklenirken hata oluştu.', 'danger');
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            this.showAlert('Personeller yüklenirken bir hata oluştu.', 'danger');
                            this.loading = false;
                        });
                },
                openModal(employee) {
                    if (employee) {
                        if (employee.ad_soyad === 'Admin User') {
                            this.showAlert('Bu kullanıcı düzenlenemez.', 'warning');
                            return;
                        }
                        this.modal.title = 'Personeli Düzenle';
                        this.modal.data = { ...employee };
                    } else {
                        this.modal.title = 'Yeni Personel Ekle';
                        this.modal.data = {};
                    }
                    $('#employeeModal').modal('show');
                },
                deleteEmployee(id) {
                    const employee = this.employees.find(emp => emp.personel_id === id);
                    if (employee && employee.ad_soyad === 'Admin User') {
                        this.showAlert('Bu kullanıcı silinemez.', 'warning');
                        return;
                    }

                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: "Bu personeli silmek istediğinizden emin misiniz?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Evet, sil!',
                        cancelButtonText: 'İptal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let formData = new FormData();
                            formData.append('action', 'delete_employee');
                            formData.append('personel_id', id);

                            fetch('api_islemleri/personeller_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(response => {
                                    if (response.status === 'success') {
                                        this.showAlert(response.message, 'success');
                                        this.loadEmployees(this.currentPage);
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
                saveEmployee() {
                    // Validate payroll fields before saving
                    if (this.modal.data.bordrolu_calisan_mi) {
                        const salary = parseFloat(this.modal.data.aylik_brut_ucret);
                        if (isNaN(salary) || salary <= 0) {
                            Swal.fire({
                                title: 'Hata!',
                                text: 'Bordrolu çalışan için aylık brüt ücret 0\'dan büyük olmalıdır.',
                                icon: 'error',
                                confirmButtonText: 'Tamam'
                            });
                            return;
                        }
                    } else {
                        this.modal.data.aylik_brut_ucret = '0.00';
                    }

                    let action = this.modal.data.personel_id ? 'update_employee' : 'add_employee';
                    let formData = new FormData();
                    for (let key in this.modal.data) {
                        if (this.modal.data[key] !== undefined && this.modal.data[key] !== null) {
                            formData.append(key, this.modal.data[key]);
                        }
                    }
                    formData.append('action', action);

                    fetch('api_islemleri/personeller_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#employeeModal').modal('hide');
                                this.loadEmployees(this.currentPage);
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                        });
                }
            },
            mounted() {
                this.loadEmployees();
            },
            watch: {
                'modal.data.bordrolu_calisan_mi'(newVal) {
                    if (!newVal || newVal == '0') {
                        this.modal.data.bordrolu_calisan_mi = 0;
                        this.modal.data.aylik_brut_ucret = '0.00';
                    } else if (newVal == '1') {
                        const salary = parseFloat(this.modal.data.aylik_brut_ucret);
                        if (isNaN(salary) || salary <= 0) {
                            Swal.fire({
                                title: 'Uyarı!',
                                text: 'Bordrolu çalışan için aylık brüt ücret 0\'dan büyük olmalıdır.',
                                icon: 'warning',
                                confirmButtonText: 'Tamam'
                            });
                        }
                    }
                }
            }
        });
        app.mount('#app');
    </script>
</body>

</html>
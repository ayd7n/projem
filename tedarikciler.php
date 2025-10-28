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

// Calculate total suppliers
$total_result = $connection->query("SELECT COUNT(*) as total FROM tedarikciler");
$total_suppliers = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Tedarikçiler - Parfüm ERP Sistemi</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        :root {
            --primary: #4a0e63; /* Deep Purple */
            --secondary: #7c2a99; /* Lighter Purple */
            --accent: #d4af37; /* Gold */
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #fdf8f5; /* Soft Cream */
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827; /* Dark Gray/Black */
            --text-secondary: #6b7280; /* Medium Gray */
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }
        html {
            font-size: 15px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
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
                <h1>Tedarikçiler Yönetimi</h1>
                <p>Tedarikçi bilgilerini yönetin</p>
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="row">
            <div class="col-md-8">
                <button @click="openModal(null)" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Tedarikçi Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon" style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.5rem; margin: 0;"><?php echo $total_suppliers; ?></h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam Tedarikçi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="mb-2 mb-md-0"><i class="fas fa-list"></i> Tedarikçi Listesi</h2>
                <div class="search-container w-100 w-md-25">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" v-model="search" @input="loadSuppliers(1)" placeholder="Tedarikçi ara...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-user"></i> Tedarikçi Adı</th>
                                <th><i class="fas fa-id-card"></i> Vergi/TC No</th>
                                <th><i class="fas fa-phone"></i> Telefon</th>
                                <th><i class="fas fa-envelope"></i> E-posta</th>
                                <th><i class="fas fa-user-tie"></i> Yetkili Kişi</th>
                                <th><i class="fas fa-sticky-note"></i> Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="7" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="suppliers.length === 0">
                                <td colspan="7" class="text-center p-4">Henüz kayıtlı tedarikçi bulunmuyor.</td>
                            </tr>
                            <tr v-for="supplier in suppliers" :key="supplier.tedarikci_id">
                                <td class="actions">
                                    <button @click="openModal(supplier)" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                    <button @click="deleteSupplier(supplier.tedarikci_id)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </td>
                                <td><strong>{{ supplier.tedarikci_adi }}</strong></td>
                                <td>{{ supplier.vergi_no_tc || '-' }}</td>
                                <td>{{ supplier.telefon || '-' }}</td>
                                <td>{{ supplier.e_posta || '-' }}</td>
                                <td>{{ supplier.yetkili_kisi || '-' }}</td>
                                <td>{{ supplier.aciklama_notlar ? (supplier.aciklama_notlar.length > 20 ? supplier.aciklama_notlar.substring(0, 20) + '...' : supplier.aciklama_notlar) : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0 w-100 w-md-auto">
                        <label for="recordsPerPage"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                        <select v-model="limit" @change="loadSuppliers(1)" class="form-control d-inline-block" style="width: auto; margin-left: 8px;">
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
                                    <a class="page-link" href="#" @click.prevent="loadSuppliers(currentPage - 1)"><i class="fas fa-chevron-left"></i> Önceki</a>
                                </li>
                                <li v-if="currentPage > 3" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadSuppliers(1)">1</a>
                                </li>
                                <li v-if="currentPage > 4" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item" :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadSuppliers(page)">{{ page }}</a>
                                </li>
                                <li v-if="currentPage < totalPages - 3" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-if="currentPage < totalPages - 2" class_="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadSuppliers(totalPages)">{{ totalPages }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="loadSuppliers(currentPage + 1)">Sonraki <i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supplier Modal -->
        <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form @submit.prevent="saveSupplier">
                        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title">{{ modal.title }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" v-model="modal.data.tedarikci_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Tedarikçi Adı *</label>
                                        <input type="text" class="form-control" v-model="modal.data.tedarikci_adi" required>
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
                                        <label>E-posta</label>
                                        <input type="email" class="form-control" v-model="modal.data.e_posta">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label>Yetkili Kişi</label>
                                <input type="text" class="form-control" v-model="modal.data.yetkili_kisi">
                            </div>
                            <div class="form-group mb-3">
                                <label>Adres</label>
                                <textarea class="form-control" v-model="modal.data.adres" rows="2"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label>Açıklama / Notlar</label>
                                <textarea class="form-control" v-model="modal.data.aciklama_notlar" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> İptal</button>
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
                    suppliers: [],
                    loading: false,
                    alert: {
                        message: '',
                        type: ''
                    },
                    search: '',
                    currentPage: 1,
                    totalPages: 1,
                    totalSuppliers: 0,
                    limit: 10,
                    modal: {
                        title: '',
                        data: {}
                    }
                }
            },
            computed: {
                paginationInfo() {
                    if (this.totalPages <= 0 || this.totalSuppliers <= 0) {
                        return 'Gösterilecek kayıt yok';
                    }
                    const startRecord = (this.currentPage - 1) * this.limit + 1;
                    const endRecord = Math.min(this.currentPage * this.limit, this.totalSuppliers);
                    return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.totalSuppliers} kayıttan`;
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
                loadSuppliers(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    let url = `api_islemleri/tedarikciler_islemler.php?action=get_suppliers&page=${this.currentPage}&limit=${this.limit}&search=${this.search}`;
                    fetch(url)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.suppliers = response.data;
                                this.totalPages = response.pagination.total_pages;
                                this.totalSuppliers = response.pagination.total_suppliers;
                            } else {
                                this.showAlert('Tedarikçiler yüklenirken hata oluştu.', 'danger');
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            this.showAlert('Tedarikçiler yüklenirken bir hata oluştu.', 'danger');
                            this.loading = false;
                        });
                },
                openModal(supplier) {
                    if (supplier) {
                        this.modal.title = 'Tedarikçiyi Düzenle';
                        this.modal.data = { ...supplier };
                    } else {
                        this.modal.title = 'Yeni Tedarikçi Ekle';
                        this.modal.data = {};
                    }
                    $('#supplierModal').modal('show');
                },
                saveSupplier() {
                    let action = this.modal.data.tedarikci_id ? 'update_supplier' : 'add_supplier';
                    let formData = new FormData();
                    for (let key in this.modal.data) {
                        formData.append(key, this.modal.data[key]);
                    }
                    formData.append('action', action);

                    fetch('api_islemleri/tedarikciler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(response => {
                        if (response.status === 'success') {
                            this.showAlert(response.message, 'success');
                            $('#supplierModal').modal('hide');
                            this.loadSuppliers(this.currentPage);
                        } else {
                            this.showAlert(response.message, 'danger');
                        }
                    })
                    .catch(error => {
                        this.showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                    });
                },
                deleteSupplier(id) {
                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: "Bu tedarikçiyi silmek istediğinizden emin misiniz?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Evet, sil!',
                        cancelButtonText: 'İptal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let formData = new FormData();
                            formData.append('action', 'delete_supplier');
                            formData.append('tedarikci_id', id);

                            fetch('api_islemleri/tedarikciler_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(response => {
                                if (response.status === 'success') {
                                    this.showAlert(response.message, 'success');
                                    this.loadSuppliers(this.currentPage);
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
                this.loadSuppliers();
            }
        });
        app.mount('#app');
    </script>
</body>
</html>

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

// Calculate total materials
$total_result = $connection->query("SELECT COUNT(*) as total FROM malzemeler");
$total_materials = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Malzeme Yönetimi - Parfüm ERP</title>
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
                <h1>Malzeme Yönetimi</h1>
                <p>Malzemeleri ekleyin, düzenleyin ve yönetin</p>
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="row">
            <div class="col-md-8">
                <button @click="openModal(null)" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Malzeme Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon" style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.5rem; margin: 0;">{{ totalMaterials }}</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam Malzeme</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="mb-2 mb-md-0"><i class="fas fa-list"></i> Malzeme Listesi</h2>
                <div class="search-container w-100 w-md-25">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" v-model="search" @input="loadMaterials(1)" placeholder="Malzeme ara...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-barcode"></i> Kod</th>
                                <th><i class="fas fa-tag"></i> İsim</th>
                                <th><i class="fas fa-box"></i> Tür</th>
                                <th><i class="fas fa-sticky-note"></i> Not</th>
                                <th><i class="fas fa-warehouse"></i> Stok</th>
                                <th><i class="fas fa-ruler"></i> Birim</th>
                                <th><i class="fas fa-clock"></i> Termin</th>
                                <th><i class="fas fa-warehouse"></i> Depo</th>
                                <th><i class="fas fa-cube"></i> Raf</th>
                                <th><i class="fas fa-exclamation-triangle"></i> Kritik Seviye</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="11" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="materials.length === 0">
                                <td colspan="11" class="text-center p-4">Aramanızla eşleşen malzeme bulunamadı.</td>
                            </tr>
                            <tr v-for="material in materials" :key="material.malzeme_kodu">
                                <td class="actions">
                                    <button @click="openModal(material)" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                    <button @click="deleteMaterial(material.malzeme_kodu)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </td>
                                <td>{{ material.malzeme_kodu }}</td>
                                <td><strong>{{ material.malzeme_ismi }}</strong></td>
                                <td>{{ material.malzeme_turu }}</td>
                                <td>{{ material.not_bilgisi }}</td>
                                <td>
                                    <span :class="stockClass(material)">
                                        {{ material.stok_miktari }}
                                    </span>
                                </td>
                                <td>{{ formatUnit(material.birim) }}</td>
                                <td>{{ material.termin_suresi }}</td>
                                <td>{{ material.depo }}</td>
                                <td>{{ material.raf }}</td>
                                <td>{{ material.kritik_stok_seviyesi }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="records-per-page mb-2 mb-md-0 w-100 w-md-auto">
                        <label for="recordsPerPage"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                        <select v-model="limit" @change="loadMaterials(1)" class="form-control d-inline-block" style="width: auto; margin-left: 8px;">
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
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(currentPage - 1)"><i class="fas fa-chevron-left"></i> Önceki</a>
                                </li>
                                <li v-if="currentPage > 3" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(1)">1</a>
                                </li>
                                <li v-if="currentPage > 4" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item" :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(page)">{{ page }}</a>
                                </li>
                                <li v-if="currentPage < totalPages - 3" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-if="currentPage < totalPages - 2" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(totalPages)">{{ totalPages }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(currentPage + 1)">Sonraki <i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

    <!-- Material Modal -->
    <div class="modal fade" id="materialModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form @submit.prevent="saveMaterial">
                    <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title">{{ modal.title }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" v-model="modal.data.malzeme_kodu">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Malzeme İsmi *</label>
                                    <input type="text" class="form-control" v-model="modal.data.malzeme_ismi" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Malzeme Türü *</label>
                                    <select class="form-control" v-model="modal.data.malzeme_turu" required>
                                        <option value="">Tür Seçin</option>
                                        <option value="sise">Şişe</option>
                                        <option value="kutu">Kutu</option>
                                        <option value="etiket">Etiket</option>
                                        <option value="pompa">Pompa</option>
                                        <option value="ic_ambalaj">İç Ambalaj</option>
                                        <option value="numune_sisesi">Numune Şişesi</option>
                                        <option value="kapak">Kapak</option>
                                        <option value="karton_ara_bolme">Karton Ara Bölme</option>
                                        <option value="diger">Diğer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Stok Miktarı</label>
                                    <input type="number" step="0.01" class="form-control" v-model="modal.data.stok_miktari" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Birim *</label>
                                    <select class="form-control" v-model="modal.data.birim" required>
                                        <option value="">Birim Seçin</option>
                                        <option value="adet">Adet</option>
                                        <option value="kg">Kg</option>
                                        <option value="gr">Gr</option>
                                        <option value="lt">Lt</option>
                                        <option value="ml">Ml</option>
                                        <option value="m">Mt</option>
                                        <option value="cm">Cm</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Termin Süresi (Gün)</label>
                                    <input type="number" class="form-control" v-model="modal.data.termin_suresi" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Depo *</label>
                                    <select class="form-control" v-model="modal.data.depo" @change="loadRafList(modal.data.depo)" required>
                                        <option value="">Depo Seçin</option>
                                        <option v-for="depo in depoList" :value="depo.depo_ismi">{{ depo.depo_ismi }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Raf *</label>
                                    <select class="form-control" v-model="modal.data.raf" required>
                                        <option value="">Raf Seçin</option>
                                        <option v-for="raf in rafList" :value="raf.raf">{{ raf.raf }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Kritik Stok Seviyesi</label>
                                    <input type="number" class="form-control" v-model="modal.data.kritik_stok_seviyesi" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label>Not Bilgisi</label>
                            <textarea class="form-control" v-model="modal.data.not_bilgisi" rows="3"></textarea>
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

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
    const app = Vue.createApp({
        data() {
            return {
                materials: [],
                loading: false,
                alert: { message: '', type: '' },
                search: '',
                currentPage: 1,
                totalPages: 1,
                totalMaterials: <?php echo $total_materials; ?>,
                limit: 10,
                modal: { title: '', data: {} },
                depoList: [],
                rafList: []
            }
        },
        computed: {
            paginationInfo() {
                if (this.totalPages <= 0 || this.totalMaterials <= 0) {
                    return 'Gösterilecek kayıt yok';
                }
                const startRecord = (this.currentPage - 1) * this.limit + 1;
                const endRecord = Math.min(this.currentPage * this.limit, this.totalMaterials);
                return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.totalMaterials} kayıttan`;
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
            loadMaterials(page = 1) {
                this.loading = true;
                this.currentPage = page;
                let url = `api_islemleri/malzemeler_islemler.php?action=get_materials&page=${this.currentPage}&limit=${this.limit}&search=${this.search}&order_by=malzeme_kodu&order_dir=desc`;
                fetch(url)
                    .then(response => response.json())
                    .then(response => {
                        if (response.status === 'success') {
                            this.materials = response.data;
                            this.totalPages = response.pagination.total_pages;
                            this.totalMaterials = response.pagination.total_materials;
                        } else {
                            this.showAlert('Malzemeler yüklenirken hata oluştu.', 'danger');
                        }
                        this.loading = false;
                    })
                    .catch(error => {
                        this.showAlert('Malzemeler yüklenirken bir hata oluştu.', 'danger');
                        this.loading = false;
                    });
            },
            openModal(material) {
                this.rafList = []; // Clear raf list
                if (material) {
                    this.modal.title = 'Malzemeyi Düzenle';
                    this.modal.data = { ...material };
                    if(material.depo) {
                        this.loadRafList(material.depo, material.raf);
                    }
                } else {
                    this.modal.title = 'Yeni Malzeme Ekle';
                    this.modal.data = { 
                        birim: 'adet',
                        malzeme_turu: 'diger',
                        depo: '',
                        raf: ''
                    };
                }
                this.$nextTick(() => {
                    $('#materialModal').modal('show');
                });
            },
            saveMaterial() {
                let action = this.modal.data.malzeme_kodu ? 'update_material' : 'add_material';
                let formData = new FormData();
                for (let key in this.modal.data) {
                    formData.append(key, this.modal.data[key]);
                }
                formData.append('action', action);

                fetch('api_islemleri/malzemeler_islemler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(response => {
                    if (response.status === 'success') {
                        this.showAlert(response.message, 'success');
                        $('#materialModal').modal('hide');
                        this.loadMaterials(this.currentPage);
                    } else {
                        this.showAlert(response.message, 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                });
            },
            deleteMaterial(id) {
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: "Bu malzemeyi silmek istediğinizden emin misiniz?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Evet, sil!',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let formData = new FormData();
                        formData.append('action', 'delete_material');
                        formData.append('malzeme_kodu', id);

                        fetch('api_islemleri/malzemeler_islemler.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                this.loadMaterials(this.currentPage);
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
                            if(selectedRaf) {
                                this.modal.data.raf = selectedRaf;
                            }
                        }
                    });
            },
            formatUnit(unit) {
                switch(unit) {
                    case 'adet': return 'Adet';
                    case 'kg': return 'Kg';
                    case 'gr': return 'Gr';
                    case 'lt': return 'Lt';
                    case 'ml': return 'Ml';
                    case 'm': return 'Mt';
                    case 'cm': return 'Cm';
                    case '1': return 'Adet'; // If enum index 1
                    case '2': return 'Kg';   // If enum index 2
                    case '3': return 'Gr';   // If enum index 3
                    case '4': return 'Lt';   // If enum index 4
                    case '5': return 'Ml';   // If enum index 5
                    case '6': return 'Mt';   // If enum index 6
                    case '7': return 'Cm';   // If enum index 7
                    default: return unit;
                }
            },
            stockClass(material) {
                const stok = parseFloat(material.stok_miktari);
                const kritik = parseFloat(material.kritik_stok_seviyesi);
                if (stok <= 0) return 'stock-low';
                if (stok <= kritik) return 'stock-critical';
                return 'stock-normal';
            }
        },
        mounted() {
            this.loadMaterials();
            this.loadDepoList();
        }
    });
    app.mount('#app');
    </script>
</body>
</html>

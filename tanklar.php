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

// Kullanıcı adını JavaScript'e aktarmak için
$user_name = addslashes($_SESSION['kullanici_adi'] ?? 'Kullanıcı');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Tanklar - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/tanklar.css">
</head>
<body>
    <div id="app">
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
                                <i class="fas fa-user-circle"></i>
                                {{ user_name || 'Kullanıcı' }}
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
        <div class="main-content">
            <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
            
            <div class="page-header">
                <div>
                    <h1>Tanklar Yönetimi</h1>
                    <p>Tank bilgilerini yönetin</p>
                </div>
            </div>

            <div id="alert-placeholder">
                <div v-if="alert.message" :class="['alert', 'alert-' + alert.type, 'alert-dismissible', 'fade', 'show']" role="alert">
                    <i class="fas" :class="alert.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
                    {{ alert.message }}
                    <button type="button" class="close" @click="clearAlert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <button class="btn btn-primary mb-3" @click="openTankModal()"><i class="fas fa-plus"></i> Yeni Tank Ekle</button>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon" style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="stat-info">
                                <h3 style="font-size: 1.5rem; margin: 0;">{{ total_tanks }}</h3>
                                <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam Tank</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <h2 class="mb-2 mb-md-0"><i class="fas fa-list"></i> Tank Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-cogs"></i> İşlemler</th>
                                    <th><i class="fas fa-barcode"></i> Tank Kodu</th>
                                    <th><i class="fas fa-tag"></i> Tank İsmi</th>
                                    <th><i class="fas fa-water"></i> Kapasite (Litre)</th>
                                    <th><i class="fas fa-sticky-note"></i> Not Bilgisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="isLoading">
                                    <td colspan="5" class="text-center p-4">Yukleniyor...</td>
                                </tr>
                                <tr v-else-if="filtered_tanks.length === 0">
                                    <td colspan="5" class="text-center p-4">Kayit bulunamadi.</td>
                                </tr>
                                <tr v-else v-for="tank in paginatedTanks" :key="tank.tank_id">
                                    <td class="actions">
                                        <button class="btn btn-primary btn-sm" @click="editTank(tank)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" @click="deleteTank(tank.tank_id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                    <td><strong>{{ tank.tank_kodu }}</strong></td>
                                    <td>{{ tank.tank_ismi }}</td>
                                    <td>{{ tank.kapasite }} L</td>
                                    <td>{{ tank.not_bilgisi || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="filtered_tanks.length > 0" class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                        <div class="d-flex flex-column flex-md-row align-items-center w-100 w-md-auto mt-2 mt-md-0">
                            <div class="records-per-page mr-0 mr-md-3 mb-2 mb-md-0">
                                <label for="tank-page-size"><i class="fas fa-list"></i> Sayfa başına kayıt: </label>
                                <select id="tank-page-size" class="form-control d-inline-block ml-2" style="width: auto;" v-model.number="limit" @change="handleLimitChange">
                                    <option :value="10">10</option>
                                    <option :value="25">25</option>
                                    <option :value="50">50</option>
                                </select>
                            </div>
                            <small class="text-muted ml-0 ml-md-3 mb-2 mb-md-0">{{ paginationInfo }}</small>
                        </div>
                        <nav aria-label="Tanklar sayfalama">
                            <ul class="pagination pagination-sm justify-content-center justify-content-md-end mb-0">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Onceki</a>
                                </li>
                                <li class="page-item" v-for="page in pageNumbers" :key="'page-' + page" :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages || totalPages === 0 }">
                                    <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tank Modal -->
        <div class="modal fade" :class="{ show: tankModalVisible }" 
             :style="{ display: tankModalVisible ? 'block' : 'none' }" 
             style="z-index: 1050" 
             @click="closeTankModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <form @submit.prevent="saveTank">
                        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title">{{ tankModalTitle }}</h5>
                            <button type="button" class="close text-white" @click="closeTankModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" v-model="tankForm.tank_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tank_kodu">Tank Kodu *</label>
                                        <input type="text" class="form-control" v-model="tankForm.tank_kodu" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tank_ismi">Tank İsmi *</label>
                                        <input type="text" class="form-control" v-model="tankForm.tank_ismi" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="kapasite">Kapasite (Litre) *</label>
                                        <input type="number" class="form-control" v-model.number="tankForm.kapasite" min="0" step="0.01" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="not_bilgisi">Not Bilgisi</label>
                                <textarea class="form-control" v-model="tankForm.not_bilgisi" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeTankModal"><i class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <i v-else class="fas fa-save"></i>
                                {{ submitButtonText }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Backdrop -->
        <div v-if="tankModalVisible" class="modal-backdrop fade show" style="z-index: 1040;"></div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Vue.js 2 -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    
    <script>
        // PHP'den gelen kullanıcı adını JavaScript değişkenine atayalım
        var session_kullanici_adi = <?php echo json_encode($user_name); ?>;
    </script>
    
    <script src="assets/js/tanklar.js"></script>
</body>
</html>

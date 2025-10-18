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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Esans Yönetimi - Parfüm ERP</title>
    
    <!-- Vue2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <!-- Axios CDN -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/esanslar.css">
    
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
    </style>
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
                                <i class="fas fa-user-circle"></i> {{ kullaniciAdi }}
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
                    <h1>Esans Yönetimi</h1>
                    <p>Esansları ekleyin, düzenleyin ve yönetin</p>
                </div>
            </div>

            <div id="alert-placeholder">
                <div v-if="alertMessage" :class="['alert', 'alert-' + alertType, 'alert-dismissible', 'fade', 'show']" role="alert">
                    <i :class="['fas', alertType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle']"></i>
                    {{ alertMessage }}
                    <button type="button" class="close" @click="closeAlert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <button @click="acYeniEsansModal" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Esans Ekle</button>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon" style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                                <i class="fas fa-flask"></i>
                            </div>
                            <div class="stat-info">
                                <h3 style="font-size: 1.5rem; margin: 0;">{{ toplamEsans }}</h3>
                                <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam Esans</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2><i class="fas fa-list"></i> Esans Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-cogs"></i> İşlemler</th>
                                    <th><i class="fas fa-barcode"></i> Esans Kodu</th>
                                    <th><i class="fas fa-tag"></i> Esans İsmi</th>
                                    <th><i class="fas fa-database"></i> Tank Kodu</th>
                                    <th><i class="fas fa-water"></i> Tank</th>
                                    <th><i class="fas fa-warehouse"></i> Stok</th>
                                    <th><i class="fas fa-ruler"></i> Birim</th>
                                    <th><i class="fas fa-clock"></i> Demlenme Süresi (Gün)</th>
                                    <th><i class="fas fa-sticky-note"></i> Not</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="esans in esansListesi" :key="esans.esans_id">
                                    <td class="actions">
                                        <button @click="acDuzenleModal(esans)" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                        <button @click="silEsans(esans.esans_id)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </td>
                                    <td>{{ esans.esans_kodu }}</td>
                                    <td><strong>{{ esans.esans_ismi }}</strong></td>
                                    <td>{{ esans.tank_kodu || '-' }}</td>
                                    <td>{{ esans.tank_ismi || '-' }}</td>
                                    <td>{{ esans.stok_miktari }}</td>
                                    <td>{{ esans.birim }}</td>
                                    <td>{{ esans.demlenme_suresi_gun }}</td>
                                    <td>{{ esans.not_bilgisi }}</td>
                                </tr>
                                <tr v-if="esansListesi.length === 0">
                                    <td colspan="9" class="text-center p-4">Henüz kayıtlı esans bulunmuyor.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Essence Modal -->
        <div class="modal fade" :class="{show: modalAcik}" v-if="modalAcik" style="display: block; background-color: rgba(0,0,0,0.5);" @click="kapatModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <form @submit.prevent="kaydetEsans">
                        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title"><i class="fas fa-vial"></i> {{ modalBaslik }}</h5>
                            <button type="button" class="close text-white" @click="kapatModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" v-model="seciliEsans.esans_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="esans_kodu">Esans Kodu *</label>
                                        <input type="text" class="form-control" id="esans_kodu" v-model="seciliEsans.esans_kodu" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="esans_ismi">Esans İsmi *</label>
                                        <input type="text" class="form-control" id="esans_ismi" v-model="seciliEsans.esans_ismi" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tank_kodu">Tank *</label>
                                        <select class="form-control" id="tank_kodu" v-model="seciliEsans.tank_kodu" @change="tankSecildi">
                                            <option value="">Seçiniz</option>
                                            <option v-for="tank in tanklarListesi" :value="tank.tank_kodu" :key="tank.tank_id">
                                                {{ tank.tank_kodu }} - {{ tank.tank_ismi }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tank_ismi">Tank İsmi</label>
                                        <input type="text" class="form-control" id="tank_ismi" v-model="seciliEsans.tank_ismi" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="stok_miktari">Stok Miktarı</label>
                                        <input type="number" step="0.01" class="form-control" id="stok_miktari" v-model="seciliEsans.stok_miktari" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="birim">Birim</label>
                                        <select class="form-control" id="birim" v-model="seciliEsans.birim">
                                            <option value="lt">Litre</option>
                                            <option value="ml">Mililitre</option>
                                            <option value="gr">Gram</option>
                                            <option value="kg">Kilogram</option>
                                            <option value="adet">Adet</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="demlenme_suresi_gun">Demlenme Süresi (Gün)</label>
                                        <input type="number" class="form-control" id="demlenme_suresi_gun" v-model="seciliEsans.demlenme_suresi_gun" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="not_bilgisi">Not Bilgisi</label>
                                <textarea class="form-control" id="not_bilgisi" v-model="seciliEsans.not_bilgisi" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="kapatModal"><i class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary" :class="{'btn-success': seciliEsans.esans_id}"><i class="fas fa-save"></i> {{ submitButonMetni }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Set user data from PHP -->
    <script>
        // Define the user data as a global variable before Vue app loads
        window.kullaniciBilgisi = {
            kullaniciAdi: '<?php echo htmlspecialchars($_SESSION["kullanici_adi"] ?? "Kullanıcı"); ?>'
        };
    </script>
    
    <!-- Vue2 Application JS -->
    <script src="assets/js/esanslar.js"></script>
</body>
</html>
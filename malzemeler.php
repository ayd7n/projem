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
if (!yetkisi_var('page:view:malzemeler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Calculate total materials
$total_result = $connection->query("SELECT COUNT(*) as total FROM malzemeler");
$total_materials = $total_result->fetch_assoc()['total'] ?? 0;

// Calculate materials below critical stock level
$critical_result = $connection->query("SELECT COUNT(*) as total FROM malzemeler WHERE stok_miktari <= kritik_stok_seviyesi AND kritik_stok_seviyesi > 0");
$critical_materials = $critical_result->fetch_assoc()['total'] ?? 0;
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
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
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
                <h1>Malzeme Yönetimi</h1>
                <p>Malzemeleri ekleyin, düzenleyin ve yönetin</p>
            </div>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <?php if (yetkisi_var('action:malzemeler:create')): ?>
                    <button @click="openModal(null)" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Malzeme
                        Ekle</button>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon"
                                    style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 style="font-size: 1.5rem; margin: 0;">{{ totalMaterials }}</h3>
                                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam
                                        Malzeme</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card" @click="toggleCriticalStockFilter"
                            style="cursor: pointer; transition: all 0.3s;">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon"
                                    style="background: var(--danger); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3 style="font-size: 1.5rem; margin: 0;">{{ criticalMaterials }}</h3>
                                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Kritik Stok
                                        Altı</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="mb-2 mb-md-0 mr-md-3"><i class="fas fa-list"></i> Malzeme Listesi</h2>
                <div class="search-container w-100 w-md-25">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" v-model="search" @input="loadMaterials(1)"
                            placeholder="Malzeme ara...">
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
                                <th><i class="fas fa-image"></i> Fotoğraf</th>
                                <th><i class="fas fa-box"></i> Tür</th>
                                <th><i class="fas fa-sticky-note"></i> Not</th>
                                <th><i class="fas fa-warehouse"></i> Stok</th>
                                <th><i class="fas fa-ruler"></i> Birim</th>
                                <th><i class="fas fa-lira-sign"></i> Alış Fiyatı</th>
                                <th><i class="fas fa-coins"></i> Para Birimi</th>
                                <th><i class="fas fa-clock"></i> Termin</th>
                                <th><i class="fas fa-warehouse"></i> Depo</th>
                                <th><i class="fas fa-cube"></i> Raf</th>
                                <th><i class="fas fa-exclamation-triangle"></i> Kritik Seviye</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="14" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="materials.length === 0">
                                <td colspan="14" class="text-center p-4">Aramanızla eşleşen malzeme bulunamadı.</td>
                            </tr>
                            <tr v-for="material in materials" :key="material.malzeme_kodu">
                                <td class="actions">
                                    <?php if (yetkisi_var('action:malzemeler:edit')): ?>
                                        <button @click="openModal(material)" class="btn btn-primary btn-sm"><i
                                                class="fas fa-edit"></i></button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:malzemeler:delete')): ?>
                                        <button @click="deleteMaterial(material.malzeme_kodu)"
                                            class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                                <td>{{ material.malzeme_kodu }}</td>
                                <td><strong>{{ material.malzeme_ismi }}</strong></td>
                                <td class="text-center">
                                    <span v-if="material.foto_sayisi > 0" style="color: #28a745; font-size: 1.2rem;"
                                        :title="material.foto_sayisi + ' fotoğraf'">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <span v-else style="color: #dc3545; font-size: 1.2rem;" title="Fotoğraf yok">
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                </td>
                                <td>{{ material.malzeme_turu }}</td>
                                <td>{{ material.not_bilgisi }}</td>
                                <td>
                                    <span :class="stockClass(material)">
                                        {{ material.stok_miktari }}
                                    </span>
                                </td>
                                <td>{{ formatUnit(material.birim) }}</td>
                                <td>{{ material.alis_fiyati }}</td>
                                <td>{{ material.para_birimi }}</td>
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
                        <select v-model="limit" @change="loadMaterials(1)" class="form-control d-inline-block"
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
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(currentPage - 1)"><i
                                            class="fas fa-chevron-left"></i> Önceki</a>
                                </li>
                                <li v-if="currentPage > 3" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(1)">1</a>
                                </li>
                                <li v-if="currentPage > 4" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-for="page in pageNumbers" :key="page" class="page-item"
                                    :class="{ active: page === currentPage }">
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(page)">{{ page }}</a>
                                </li>
                                <li v-if="currentPage < totalPages - 3" class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <li v-if="currentPage < totalPages - 2" class="page-item">
                                    <a class="page-link" href="#" @click.prevent="loadMaterials(totalPages)">{{
                                        totalPages }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <a class="page-link" href="#"
                                        @click.prevent="loadMaterials(currentPage + 1)">Sonraki
                                        <i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material Modal -->
        <div class="modal fade" id="materialModal" tabindex="-1" role="dialog">
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
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" id="materialTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">
                                    <i class="fas fa-info-circle"></i> Malzeme Bilgileri
                                </a>
                            </li>
                            <li class="nav-item" v-if="modal.data.malzeme_kodu">
                                <a class="nav-link" id="photos-tab" data-toggle="tab" href="#photos" role="tab"
                                    @click="loadPhotos">
                                    <i class="fas fa-images"></i> Fotoğraflar ({{ materialPhotos.length }})
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="materialTabsContent">
                            <!-- Malzeme Bilgileri Tab -->
                            <div class="tab-pane fade show active" id="info" role="tabpanel">
                                <form @submit.prevent="saveMaterial">
                                    <input type="hidden" v-model="modal.data.malzeme_kodu">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Malzeme İsmi *</label>
                                                <input type="text" class="form-control"
                                                    v-model="modal.data.malzeme_ismi" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Malzeme Türü * <button type="button" @click="openTurModal"
                                                        class="btn btn-sm btn-outline-primary ml-2"
                                                        title="Türleri Düzenle"><i
                                                            class="fas fa-cog"></i></button></label>
                                                <select class="form-control" v-model="modal.data.malzeme_turu" required>
                                                    <option value="">Tür Seçin</option>
                                                    <option v-for="tur in malzemeTurleri" :key="tur.value"
                                                        :value="tur.value">{{ tur.label }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Stok Miktarı</label>
                                                <input type="number" step="0.01" class="form-control"
                                                    v-model="modal.data.stok_miktari" min="0">
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
                                                <label>Alış Fiyatı</label>
                                                <input type="number" step="0.01" class="form-control"
                                                    v-model="modal.data.alis_fiyati" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Para Birimi</label>
                                                <select class="form-control" v-model="modal.data.para_birimi">
                                                    <option value="TRY">TRY</option>
                                                    <option value="USD">USD</option>
                                                    <option value="EUR">EUR</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Termin Süresi (Gün)</label>
                                                <input type="number" class="form-control"
                                                    v-model="modal.data.termin_suresi" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Depo *</label>
                                                <select class="form-control" v-model="modal.data.depo"
                                                    @change="loadRafList(modal.data.depo)" required>
                                                    <option value="">Depo Seçin</option>
                                                    <option v-for="depo in depoList" :value="depo.depo_ismi">{{
                                                        depo.depo_ismi
                                                        }}</option>
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
                                                    <option v-for="raf in rafList" :value="raf.raf">{{ raf.raf }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Kritik Stok Seviyesi</label>
                                                <input type="number" class="form-control"
                                                    v-model="modal.data.kritik_stok_seviyesi" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Not Bilgisi</label>
                                        <textarea class="form-control" v-model="modal.data.not_bilgisi"
                                            rows="3"></textarea>
                                    </div>
                                    <div class="text-right">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                                class="fas fa-times"></i> İptal</button>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                                            Kaydet</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Fotoğraflar Tab -->
                            <div class="tab-pane fade" id="photos" role="tabpanel">
                                <div class="photo-upload-section mb-4">
                                    <h6><i class="fas fa-cloud-upload-alt"></i> Fotoğraf Yükle</h6>
                                    <div class="upload-area" @dragover.prevent @drop.prevent="handlePhotoDrop"
                                        @click="$refs.materialPhotoInput.click()"
                                        style="border: 2px dashed var(--border-color); border-radius: 8px; padding: 40px; text-align: center; cursor: pointer; background: var(--bg-color); transition: all 0.3s;">
                                        <i class="fas fa-cloud-upload-alt"
                                            style="font-size: 3rem; color: var(--primary); margin-bottom: 10px;"></i>
                                        <p class="mb-0">Fotoğrafları buraya sürükleyin veya tıklayın</p>
                                        <small class="text-muted">Desteklenen formatlar: JPG, PNG, GIF (Max 5MB)</small>
                                        <input type="file" ref="materialPhotoInput" @change="handlePhotoSelect"
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
                                    <div v-else-if="materialPhotos.length === 0" class="text-center p-4 text-muted">
                                        <i class="fas fa-image" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2">Henüz fotoğraf yüklenmemiş</p>
                                    </div>
                                    <div v-else class="row">
                                        <div v-for="(photo, index) in materialPhotos" :key="photo.fotograf_id"
                                            class="col-md-3 col-sm-6 mb-3" draggable="true"
                                            @dragstart="handleDragStart($event, index)"
                                            @dragover.prevent="handleDragOver($event, index)"
                                            @drop="handlePhotoDrop($event, index)" @dragend="handleDragEnd">
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

        <!-- Malzeme Türü Düzenleme Modal -->
        <div class="modal fade" id="turModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header"
                        style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title"><i class="fas fa-tags"></i> Malzeme Türlerini Düzenle</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label><strong>Yeni Tür Ekle</strong></label>
                            <div class="input-group">
                                <input type="text" class="form-control" v-model="yeniTurValue"
                                    placeholder="Tür değeri (ör: plastik_kap)">
                                <input type="text" class="form-control" v-model="yeniTurLabel"
                                    placeholder="Görünen isim (ör: Plastik Kap)">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" @click="addTur"
                                        :disabled="!yeniTurValue || !yeniTurLabel">
                                        <i class="fas fa-plus"></i> Ekle
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Tür değeri küçük harf ve alt çizgi kullanmalıdır.</small>
                        </div>
                        <hr>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Değer</th>
                                        <th>Görünen İsim</th>
                                        <th class="text-center">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(tur, index) in malzemeTurleri" :key="tur.value">
                                        <td><code>{{ tur.value }}</code></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" v-model="tur.label"
                                                @blur="updateTur(tur)">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm"
                                                @click="deleteTur(index)" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                class="fas fa-times"></i> Kapat</button>
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

            <button v-if="materialPhotos.length > 1" @click.stop="previousPhoto" class="lightbox-nav lightbox-prev"
                style="position: absolute; left: 30px; color: white; font-size: 50px; background: rgba(255,255,255,0.1); border: none; cursor: pointer; padding: 20px; border-radius: 50%; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div @click.stop
                style="max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center;">
                <img :src="materialPhotos[lightbox.currentIndex]?.dosya_yolu"
                    :alt="materialPhotos[lightbox.currentIndex]?.dosya_adi"
                    style="max-width: 100%; max-height: 85vh; object-fit: contain; border-radius: 8px;">
                <div style="color: white; margin-top: 15px; text-align: center;">
                    <p style="margin: 5px 0; font-size: 16px;">{{ materialPhotos[lightbox.currentIndex]?.dosya_adi }}
                    </p>
                    <small style="opacity: 0.7;">{{ lightbox.currentIndex + 1 }} / {{ materialPhotos.length }}</small>
                </div>
            </div>

            <button v-if="materialPhotos.length > 1" @click.stop="nextPhoto" class="lightbox-nav lightbox-next"
                style="position: absolute; right: 30px; color: white; font-size: 50px; background: rgba(255,255,255,0.1); border: none; cursor: pointer; padding: 20px; border-radius: 50%; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-right"></i>
            </button>
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
                    criticalMaterials: <?php echo $critical_materials; ?>,
                    limit: 10,
                    modal: { title: '', data: {} },
                    depoList: [],
                    rafList: [],
                    criticalStockFilterEnabled: false,
                    materialPhotos: [],
                    loadingPhotos: false,
                    uploadProgress: 0,
                    lightbox: { show: false, currentIndex: 0 },
                    draggedIndex: null,
                    dragOverIndex: null,
                    malzemeTurleri: [
                        { value: 'sise', label: 'Şişe' },
                        { value: 'kutu', label: 'Kutu' },
                        { value: 'etiket', label: 'Etiket' },
                        { value: 'pompa', label: 'Pompa' },
                        { value: 'ic_ambalaj', label: 'İç Ambalaj' },
                        { value: 'numune_sisesi', label: 'Numune Şişesi' },
                        { value: 'kapak', label: 'Kapak' },
                        { value: 'kimyasal_madde', label: 'Kimyasal Madde' },
                        { value: 'alkol', label: 'Alkol' },
                        { value: 'saf_su', label: 'Saf Su' },
                        { value: 'esans', label: 'Esans' },
                        { value: 'renklendirici', label: 'Renklendirici' },
                        { value: 'cozucu', label: 'Çözücü' },
                        { value: 'koruyucu', label: 'Koruyucu' },
                        { value: 'karton_ara_bolme', label: 'Karton Ara Bölme' },
                        { value: 'diger', label: 'Diğer' }
                    ],
                    yeniTurValue: '',
                    yeniTurLabel: ''
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
                    if (this.criticalStockFilterEnabled) {
                        url += '&filter=critical';
                    }
                    fetch(url)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.materials = response.data;
                                this.totalPages = response.pagination.total_pages;
                                this.totalMaterials = response.pagination.total_materials;
                                this.criticalMaterials = response.pagination.critical_materials;
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
                    this.rafList = [];
                    this.materialPhotos = [];
                    this.uploadProgress = 0;

                    if (material) {
                        this.modal.title = 'Malzemeyi Düzenle';
                        this.modal.data = { ...material };
                        if (material.depo) {
                            this.loadRafList(material.depo, material.raf);
                        }
                        this.loadPhotos();
                    } else {
                        this.modal.title = 'Yeni Malzeme Ekle';
                        this.modal.data = {
                            birim: 'adet',
                            malzeme_turu: 'diger',
                            alis_fiyati: 0.00,
                            para_birimi: 'TRY',
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
                                if (selectedRaf) {
                                    this.modal.data.raf = selectedRaf;
                                }
                            }
                        });
                },
                formatUnit(unit) {
                    switch (unit) {
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
                },
                loadPhotos() {
                    if (!this.modal.data.malzeme_kodu) return;

                    this.loadingPhotos = true;
                    fetch(`api_islemleri/malzeme_fotograflari_islemler.php?action=get_photos&malzeme_kodu=${this.modal.data.malzeme_kodu}`)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.materialPhotos = response.data;
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
                handlePhotoDrop(event) {
                    if (event.dataTransfer && event.dataTransfer.files) {
                        const files = event.dataTransfer.files;
                        this.uploadPhotos(files);
                    }
                },
                uploadPhotos(files) {
                    if (!this.modal.data.malzeme_kodu) {
                        this.showAlert('Önce malzemeyi kaydetmelisiniz.', 'warning');
                        return;
                    }

                    if (files.length === 0) return;

                    const totalFiles = files.length;
                    let uploadedFiles = 0;

                    Array.from(files).forEach((file, index) => {
                        if (file.size > 5 * 1024 * 1024) {
                            this.showAlert(`${file.name} dosyası 5MB'dan büyük.`, 'danger');
                            return;
                        }

                        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!allowedTypes.includes(file.type)) {
                            this.showAlert(`${file.name} desteklenmeyen bir format.`, 'danger');
                            return;
                        }

                        const formData = new FormData();
                        formData.append('action', 'upload_photo');
                        formData.append('malzeme_kodu', this.modal.data.malzeme_kodu);
                        formData.append('photo', file);

                        fetch('api_islemleri/malzeme_fotograflari_islemler.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(response => {
                                uploadedFiles++;
                                this.uploadProgress = Math.round((uploadedFiles / totalFiles) * 100);

                                if (response.status === 'success') {
                                    this.materialPhotos.push(response.data);

                                    // Update main material list photo count
                                    const materialIndex = this.materials.findIndex(m => m.malzeme_kodu === this.modal.data.malzeme_kodu);
                                    if (materialIndex !== -1) {
                                        this.materials[materialIndex].foto_sayisi = (parseInt(this.materials[materialIndex].foto_sayisi) || 0) + 1;
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

                            fetch('api_islemleri/malzeme_fotograflari_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(response => {
                                    if (response.status === 'success') {
                                        // Remove photo from array using splice for better reactivity
                                        const index = this.materialPhotos.findIndex(p => p.fotograf_id === fotograf_id);
                                        if (index !== -1) {
                                            this.materialPhotos.splice(index, 1);
                                        }

                                        // Update main material list photo count
                                        const materialIndex = this.materials.findIndex(m => m.malzeme_kodu === this.modal.data.malzeme_kodu);
                                        if (materialIndex !== -1) {
                                            this.materials[materialIndex].foto_sayisi = Math.max(0, (parseInt(this.materials[materialIndex].foto_sayisi) || 0) - 1);
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
                setPrimaryPhoto(fotograf_id) {
                    const formData = new FormData();
                    formData.append('action', 'set_primary_photo');
                    formData.append('fotograf_id', fotograf_id);

                    fetch('api_islemleri/malzeme_fotograflari_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.materialPhotos.forEach(p => p.ana_fotograf = 0);
                                const photo = this.materialPhotos.find(p => p.fotograf_id === fotograf_id);
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

                    const draggedPhoto = this.materialPhotos[this.draggedIndex];
                    const newPhotos = [...this.materialPhotos];

                    newPhotos.splice(this.draggedIndex, 1);
                    newPhotos.splice(targetIndex, 0, draggedPhoto);

                    this.materialPhotos = newPhotos;
                    this.updatePhotoOrder();

                    this.draggedIndex = null;
                    this.dragOverIndex = null;
                },
                handleDragEnd() {
                    this.draggedIndex = null;
                    this.dragOverIndex = null;
                },
                updatePhotoOrder() {
                    const photoIds = this.materialPhotos.map(p => p.fotograf_id);
                    const formData = new FormData();
                    formData.append('action', 'update_order');
                    formData.append('photos', JSON.stringify(photoIds));

                    fetch('api_islemleri/malzeme_fotograflari_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.materialPhotos.forEach((photo, index) => {
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
                openLightbox(index) {
                    this.lightbox.currentIndex = index;
                    this.lightbox.show = true;
                    document.body.style.overflow = 'hidden';
                    document.addEventListener('keydown', this.handleLightboxKeyboard);
                },
                closeLightbox() {
                    this.lightbox.show = false;
                    document.body.style.overflow = '';
                    document.removeEventListener('keydown', this.handleLightboxKeyboard);
                },
                nextPhoto() {
                    this.lightbox.currentIndex = (this.lightbox.currentIndex + 1) % this.materialPhotos.length;
                },
                previousPhoto() {
                    this.lightbox.currentIndex = (this.lightbox.currentIndex - 1 + this.materialPhotos.length) % this.materialPhotos.length;
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
                toggleCriticalStockFilter() {
                    this.criticalStockFilterEnabled = !this.criticalStockFilterEnabled;
                    this.loadMaterials(1);
                },
                openTurModal() {
                    this.loadTurler();
                    $('#turModal').modal('show');
                },
                loadTurler() {
                    fetch('api_islemleri/malzeme_turleri_islemler.php?action=get_turler')
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.malzemeTurleri = response.data;
                            }
                        })
                        .catch(error => {
                            console.error('Türler yüklenirken hata:', error);
                        });
                },
                updateTur(tur) {
                    const formData = new FormData();
                    formData.append('action', 'update_tur');
                    formData.append('id', tur.id);
                    formData.append('label', tur.label);

                    fetch('api_islemleri/malzeme_turleri_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert('Tür güncellendi.', 'success');
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        });
                },
                addTur() {
                    if (!this.yeniTurValue || !this.yeniTurLabel) return;

                    const formData = new FormData();
                    formData.append('action', 'add_tur');
                    formData.append('value', this.yeniTurValue);
                    formData.append('label', this.yeniTurLabel);

                    fetch('api_islemleri/malzeme_turleri_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.malzemeTurleri.push(response.data);
                                this.yeniTurValue = '';
                                this.yeniTurLabel = '';
                                this.showAlert('Yeni tür eklendi.', 'success');
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('Tür eklenirken hata oluştu.', 'danger');
                        });
                },
                deleteTur(index) {
                    const tur = this.malzemeTurleri[index];
                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: `"${tur.label}" türünü silmek istediğinizden emin misiniz?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Evet, sil!',
                        cancelButtonText: 'İptal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData();
                            formData.append('action', 'delete_tur');
                            formData.append('id', tur.id);

                            fetch('api_islemleri/malzeme_turleri_islemler.php', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(response => {
                                    if (response.status === 'success') {
                                        this.malzemeTurleri.splice(index, 1);
                                        this.showAlert('Tür silindi.', 'success');
                                    } else {
                                        this.showAlert(response.message, 'danger');
                                    }
                                });
                        }
                    });
                }
            },
            mounted() {
                this.loadMaterials();
                this.loadDepoList();
                this.loadTurler();
            }
        });
        app.mount('#app');
    </script>
</body>

</html>
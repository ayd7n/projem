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

// Fetch all product and essence trees
$product_trees_query = "SELECT * FROM urun_agaci ORDER BY agac_turu, urun_kodu, bilesen_kodu";
$product_trees_result = $connection->query($product_trees_query);

// Fetch all products, materials, and essences for dropdowns
$products_query = "SELECT urun_kodu, urun_ismi FROM urunler ORDER BY urun_ismi";
$products_result = $connection->query($products_query);

$materials_query = "SELECT malzeme_kodu, malzeme_ismi, malzeme_turu FROM malzemeler ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);

$essences_query = "SELECT esans_kodu, esans_ismi FROM esanslar ORDER BY esans_ismi";
$essences_result = $connection->query($essences_query);

// Calculate total product trees (distinct products in product tree)
$total_result = $connection->query("SELECT COUNT(DISTINCT urun_kodu) as total FROM urun_agaci");
$total_product_trees = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ürün Ağaçları - Parfüm ERP</title>
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
    <link rel="stylesheet" href="assets/css/urun_agaclari.css">
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
                    <h1>Ürün Ağacı Yönetimi</h1>
                    <p>Ürün ağaçlarını ekleyin, düzenleyin ve yönetin</p>
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
                <div class="col-md-12">
                    <button @click="openAddModal" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Ürün Ağacı Ekle</button>
                    <button @click="openEssenceAddModal" class="btn btn-success mb-3 ml-2"><i class="fas fa-plus"></i> Yeni Esans Ağacı Ekle</button>
                </div>
            </div>

            <!-- Tabs for Product Trees and Essence Trees -->
            <div id="treeTabs">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="product-tab" data-toggle="tab" href="#product" role="tab" @click="switchTab('product')">
                            <i class="fas fa-sitemap"></i> Ürün Ağaçları ({{ productTrees.length }})
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="essence-tab" data-toggle="tab" href="#essence" role="tab" @click="switchTab('essence')">
                            <i class="fas fa-perfume"></i> Esans Ağaçları ({{ essenceTrees.length }})
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <!-- Product Trees Tab -->
                    <div class="tab-pane fade show active" id="product" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Ürün ağaçlarında arama yapın..." v-model="productTreeSearchTerm" @input="searchProductTrees">
                                </div>
                                <!-- Pagination controls for Product Trees -->
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-inline">
                                            <label>Sayfa başına:&nbsp;</label>
                                            <select class="form-control" v-model="productTreesPerPage" @change="changeProductTreesPerPage">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-outline-secondary" @click="changeProductTreesPage(1)" :disabled="productTreesCurrentPage === 1">
                                                <i class="fas fa-angle-double-left"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" @click="changeProductTreesPage(productTreesCurrentPage - 1)" :disabled="productTreesCurrentPage === 1">
                                                <i class="fas fa-angle-left"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary disabled">
                                                {{ productTreesCurrentPage }} / {{ productTreesTotalPages }}
                                            </button>
                                            <button class="btn btn-outline-secondary" @click="changeProductTreesPage(productTreesCurrentPage + 1)" :disabled="productTreesCurrentPage === productTreesTotalPages">
                                                <i class="fas fa-angle-right"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" @click="changeProductTreesPage(productTreesTotalPages)" :disabled="productTreesCurrentPage === productTreesTotalPages">
                                                <i class="fas fa-angle-double-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-wrapper">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                                <th><i class="fas fa-barcode"></i> Ürün Kodu</th>
                                                <th><i class="fas fa-tag"></i> Ürün İsmi</th>
                                                <th><i class="fas fa-barcode"></i> Bileşen Kodu</th>
                                                <th><i class="fas fa-tag"></i> Bileşen İsmi</th>
                                                <th><i class="fas fa-weight-hanging"></i> Bileşen Miktarı</th>
                                                <th><i class="fas fa-box"></i> Bileşen Türü</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="pt in productTrees" :key="pt.urun_agaci_id">
                                                <td class="actions">
                                                    <button @click="openEditModal(pt.urun_agaci_id)" class="btn btn-primary btn-sm" title="Düzenle"><i class="fas fa-edit"></i></button>
                                                    <button @click="deleteProductTree(pt.urun_agaci_id)" class="btn btn-danger btn-sm" title="Sil"><i class="fas fa-trash"></i></button>
                                                </td>
                                                <td>{{ pt.urun_kodu }}</td>
                                                <td><strong>{{ pt.urun_ismi }}</strong></td>
                                                <td>{{ pt.bilesen_kodu }}</td>
                                                <td>{{ pt.bilesen_ismi }}</td>
                                                <td>{{ pt.bilesen_miktari }}</td>
                                                <td>{{ pt.bilesenin_malzeme_turu }}</td>
                                            </tr>
                                            <tr v-if="productTrees.length === 0">
                                                <td colspan="7" class="text-center p-4">Henüz kayıtlı ürün ağacı bulunmuyor.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Essence Trees Tab -->
                    <div class="tab-pane fade" id="essence" role="tabpanel">
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Esans ağaçlarında arama yapın..." v-model="essenceTreeSearchTerm" @input="searchEssenceTrees">
                                </div>
                                <!-- Pagination controls for Essence Trees -->
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-inline">
                                            <label>Sayfa başına:&nbsp;</label>
                                            <select class="form-control" v-model="essenceTreesPerPage" @change="changeEssenceTreesPerPage">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-outline-secondary" @click="changeEssenceTreesPage(1)" :disabled="essenceTreesCurrentPage === 1">
                                                <i class="fas fa-angle-double-left"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" @click="changeEssenceTreesPage(essenceTreesCurrentPage - 1)" :disabled="essenceTreesCurrentPage === 1">
                                                <i class="fas fa-angle-left"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary disabled">
                                                {{ essenceTreesCurrentPage }} / {{ essenceTreesTotalPages }}
                                            </button>
                                            <button class="btn btn-outline-secondary" @click="changeEssenceTreesPage(essenceTreesCurrentPage + 1)" :disabled="essenceTreesCurrentPage === essenceTreesTotalPages">
                                                <i class="fas fa-angle-right"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" @click="changeEssenceTreesPage(essenceTreesTotalPages)" :disabled="essenceTreesCurrentPage === essenceTreesTotalPages">
                                                <i class="fas fa-angle-double-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-wrapper">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                                <th><i class="fas fa-barcode"></i> Esans Kodu</th>
                                                <th><i class="fas fa-tag"></i> Esans İsmi</th>
                                                <th><i class="fas fa-barcode"></i> Bileşen Kodu</th>
                                                <th><i class="fas fa-tag"></i> Bileşen İsmi</th>
                                                <th><i class="fas fa-weight-hanging"></i> Bileşen Miktarı</th>
                                                <th><i class="fas fa-box"></i> Bileşen Türü</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="et in essenceTrees" :key="et.urun_agaci_id">
                                                <td class="actions">
                                                    <button @click="openEssenceEditModal(et.urun_agaci_id)" class="btn btn-primary btn-sm" title="Düzenle"><i class="fas fa-edit"></i></button>
                                                    <button @click="deleteEssenceTree(et.urun_agaci_id)" class="btn btn-danger btn-sm" title="Sil"><i class="fas fa-trash"></i></button>
                                                </td>
                                                <td>{{ et.urun_kodu }}</td>
                                                <td><strong>{{ et.urun_ismi }}</strong></td>
                                                <td>{{ et.bilesen_kodu }}</td>
                                                <td>{{ et.bilesen_ismi }}</td>
                                                <td>{{ et.bilesen_miktari }}</td>
                                                <td>{{ et.bilesenin_malzeme_turu }}</td>
                                            </tr>
                                            <tr v-if="essenceTrees.length === 0">
                                                <td colspan="7" class="text-center p-4">Henüz kayıtlı esans ağacı bulunmuyor.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Tree Modal -->
        <div class="modal fade" :class="{show: showModal}" v-if="showModal" style="display: block; background-color: rgba(0,0,0,0.5);" @click="closeModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <form @submit.prevent="saveProductTree">
                        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title">{{ modalTitle }}</h5>
                            <button type="button" class="close text-white" @click="closeModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="urun_kodu">Ürün *</label>
                                    <select class="form-control" id="urun_kodu" v-model="selectedProductTree.urun_kodu" @change="updateProductName" required>
                                        <option value="">Ürün Seçin</option>
                                        <option v-for="product in products" :key="product.urun_kodu" :value="product.urun_kodu">
                                            {{ product.urun_kodu }} - {{ product.urun_ismi }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="bilesen_kodu">Bileşen *</label>
                                    <select class="form-control" id="bilesen_kodu" v-model="selectedProductTree.bilesen_kodu" @change="updateBilesenInfo" required>
                                        <option value="">Bileşen Seçin</option>
                                        <optgroup label="Esanslar">
                                            <option v-for="essence in essences" :key="essence.esans_kodu" :value="essence.esans_kodu">
                                                {{ essence.esans_kodu }} - {{ essence.esans_ismi }}
                                            </option>
                                        </optgroup>
                                        <optgroup label="Malzemeler">
                                            <option v-for="material in materials" :key="material.malzeme_kodu" :value="material.malzeme_kodu">
                                                {{ material.malzeme_kodu }} - {{ material.malzeme_ismi }}
                                            </option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="bilesen_miktari">Bileşen Miktarı *</label>
                                    <input type="number" step="0.01" class="form-control" id="bilesen_miktari" v-model.number="selectedProductTree.bilesen_miktari" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeModal"><i class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary" :class="{'btn-success': selectedProductTree.urun_agaci_id}"><i class="fas fa-save"></i> {{ submitButtonText }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Essence Tree Modal -->
        <div class="modal fade" :class="{show: showEssenceModal}" v-if="showEssenceModal" style="display: block; background-color: rgba(0,0,0,0.5);" @click="closeEssenceModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <form @submit.prevent="saveEssenceTree">
                        <div class="modal-header" style="background: linear-gradient(135deg, #4a0e63, #7c2a99); color: white;">
                            <h5 class="modal-title">{{ modalTitle }}</h5>
                            <button type="button" class="close text-white" @click="closeEssenceModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="essence_urun_kodu">Esans *</label>
                                    <select class="form-control" id="essence_urun_kodu" v-model="selectedEssenceTree.urun_kodu" @change="updateEssenceName" required>
                                        <option value="">Esans Seçin</option>
                                        <option v-for="essence in essences" :key="essence.esans_kodu" :value="essence.esans_kodu">
                                            {{ essence.esans_kodu }} - {{ essence.esans_ismi }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="essence_bilesen_kodu">Bileşen (Sadece Malzeme) *</label>
                                    <select class="form-control" id="essence_bilesen_kodu" v-model="selectedEssenceTree.bilesen_kodu" @change="updateEssenceBilesenInfo" required>
                                        <option value="">Malzeme Seçin</option>
                                        <option v-for="material in materials" :key="material.malzeme_kodu" :value="material.malzeme_kodu">
                                            {{ material.malzeme_kodu }} - {{ material.malzeme_ismi }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="essence_bilesen_miktari">Bileşen Miktarı *</label>
                                    <input type="number" step="0.01" class="form-control" id="essence_bilesen_miktari" v-model.number="selectedEssenceTree.bilesen_miktari" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeEssenceModal"><i class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary" :class="{'btn-success': selectedEssenceTree.urun_agaci_id}"><i class="fas fa-save"></i> {{ submitButtonText }}</button>
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
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- Set user data from PHP -->
    <script>
        // Define the user data as a global variable before Vue app loads
        window.kullaniciBilgisi = {
            kullaniciAdi: '<?php echo htmlspecialchars($_SESSION["kullanici_adi"] ?? "Kullanıcı"); ?>'
        };
    </script>
    
    <!-- Vue2 Application JS -->
    <script src="assets/js/urun_agaclari.js?v=<?php echo time(); ?>"></script>
</body>
</html>

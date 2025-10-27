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
    <title>Manuel Stok Hareket - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="css/stil.css">
    <link rel="stylesheet" href="css/manuel_stok_hareket.css">
</head>
<body>
    <div id="app" data-username="<?php echo addslashes($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>">
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
                    <h1>Manuel Stok Hareket Yönetimi</h1>
                    <p>Manuel olarak stok hareketlerini kaydedin ve yönetin</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="btn-group" role="group" aria-label="Stok Hareketleri Butonları">
                        <button class="btn btn-success" @click="openSayimFazlasiModal"><i class="fas fa-plus-circle"></i> Sayım
                            Fazlası</button>
                        <button class="btn btn-danger" @click="openFireSayimEksigiModal"><i class="fas fa-minus-circle"></i> Fire / Sayım
                            Eksigi</button>
                        <button class="btn btn-primary" @click="openTransferModal"><i class="fas fa-exchange-alt"></i> Yeni Stok
                            Transferi</button>
                        <button class="btn btn-info" @click="openMalKabulModal"><i class="fas fa-check-circle"></i> Mal Kabul</button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2><i class="fas fa-table"></i> Stok Hareketleri Listesi</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                        <div class="d-flex align-items-center mb-2 mb-lg-0">
                            <span class="text-muted small mr-2" style="white-space: nowrap;">Sayfa başına</span>
                            <select class="custom-select custom-select-sm" style="min-width: 90px;" v-model.number="itemsPerPage">
                                <option v-for="option in itemsPerPageOptions" :key="'per-page-' + option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center">
                            <small class="text-muted mr-3">
                                <span v-if="total_movements === 0">Toplam 0 kayıt</span>
                                <span v-else>{{ pageRangeStart }}-{{ pageRangeEnd }} / {{ total_movements }} kayıt</span>
                            </small>
                            <nav v-if="totalPages > 1" aria-label="Stok hareketleri sayfalama">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                        <a class="page-link" href="#" @click.prevent="changePage(-1)" aria-label="Önceki">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item"
                                        v-for="(page, index) in paginationPages"
                                        :key="'page-' + index"
                                        :class="{ active: currentPage === page, disabled: page === '...' }">
                                        <a v-if="page !== '...'" class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
                                        <span v-else class="page-link">...</span>
                                    </li>
                                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                        <a class="page-link" href="#" @click.prevent="changePage(1)" aria-label="Sonraki">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="table-wrapper" style="max-height: 60vh; overflow-y: auto;">
                        <table class="table table-hover table-align-top">
                            <thead style="position: sticky; top: 0; background-color: white; z-index: 10;">
                                <tr>
                                    <th class="actions"><i class="fas fa-cogs"></i> İşlemler</th>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-calendar"></i> Tarih</th>
                                    <th><i class="fas fa-tag"></i> Stok Türü</th>
                                    <th><i class="fas fa-barcode"></i> Kod</th>
                                    <th><i class="fas fa-tag"></i> İsim</th>
                                    <th><i class="fas fa-ruler-vertical"></i> Birim</th>
                                    <th><i class="fas fa-sort-numeric-up"></i> Miktar</th>
                                    <th><i class="fas fa-exchange-alt"></i> Yön</th>
                                    <th><i class="fas fa-tasks"></i> Hareket Türü</th>
                                    <th><i class="fas fa-warehouse"></i> Depo</th>
                                    <th><i class="fas fa-cubes"></i> Raf</th>
                                    <th><i class="fas fa-oil-can"></i> Tank Kodu</th>
                                    <th><i class="fas fa-file-invoice"></i> Belge No</th>
                                    <th><i class="fas fa-industry"></i> İş Emri No</th>
                                    <th><i class="fas fa-user"></i> Müşteri ID</th>
                                    <th><i class="fas fa-user"></i> Müşteri Adı</th>
                                    <th class="long-text" data-column="aciklama"><i class="fas fa-comment"></i> Açıklama</th>
                                    <th><i class="fas fa-user"></i> Kaydeden ID</th>
                                    <th><i class="fas fa-user"></i> Kaydeden Adı</th>
                                    <th><i class="fas fa-truck"></i> Tedarikçi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="movements.length === 0">
                                    <td colspan="21" class="text-center p-4">
                                        <i class="fas fa-exchange-alt fa-3x mb-3" style="color: var(--text-secondary);"></i>
                                        <h4>Henüz Kayıtlı Stok Hareketi Bulunmuyor</h4>
                                        <p class="text-muted">Henüz hiç stok hareketi kaydedilmemiş.</p>
                                    </td>
                                </tr>
                                <tr v-else v-for="movement in paginatedMovements" :key="movement.hareket_id">
                                    <td class="actions">
                                        <button class="btn btn-danger btn-sm" @click="deleteMovement(movement.hareket_id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                    <td><strong>{{ movement.hareket_id }}</strong></td>
                                    <td>{{ formatDate(movement.tarih) }}</td>
                                    <td>
                                        <span class="badge" 
                                            :class="{
                                                'badge-primary': movement.stok_turu === 'malzeme',
                                                'badge-success': movement.stok_turu === 'esans',
                                                'badge-info': movement.stok_turu === 'urun',
                                                'badge-secondary': movement.stok_turu !== 'malzeme' && movement.stok_turu !== 'esans' && movement.stok_turu !== 'urun'
                                            }">
                                            {{ movement.stok_turu }}
                                        </span>
                                    </td>
                                    <td><strong>{{ movement.kod }}</strong></td>
                                    <td>{{ movement.isim }}</td>
                                    <td>{{ movement.birim }}</td>
                                    <td class="font-weight-bold">{{ formatNumber(movement.miktar) }}</td>
                                    <td>
                                        <span class="badge" 
                                            :class="movement.yon === 'giris' ? 'badge-success' : 'badge-danger'">
                                            {{ movement.yon === 'giris' ? 'Giriş' : 'Çıkış' }}
                                        </span>
                                    </td>
                                    <td>{{ movement.hareket_turu }}</td>
                                    <td>{{ movement.depo || '-' }}</td>
                                    <td>{{ movement.raf || '-' }}</td>
                                    <td>{{ movement.tank_kodu || '-' }}</td>
                                    <td>{{ movement.ilgili_belge_no }}</td>
                                    <td>{{ movement.is_emri_numarasi || '-' }}</td>
                                    <td>{{ movement.musteri_id || '-' }}</td>
                                    <td>{{ movement.musteri_adi || '-' }}</td>
                                    <td class="long-text" data-column="aciklama">{{ movement.aciklama }}</td>
                                    <td>{{ movement.kaydeden_personel_id || '-' }}</td>
                                    <td>{{ movement.kaydeden_personel_adi || '-' }}</td>
                                    <td>{{ movement.tedarikci_ismi || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fire / Sayım Eksigi Modal -->
        <div class="modal fade" :class="{ show: fireSayimEksigiModalVisible }" 
             :style="{ display: fireSayimEksigiModalVisible ? 'block' : 'none' }" 
             style="z-index: 1050" 
             @click="closeFireSayimEksigiModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <form @submit.prevent="saveFireSayimEksigi">
                        <div class="modal-header">
                            <h5 class="modal-title">Fire / Sayım Eksigi</h5>
                            <button type="button" class="close" @click="closeFireSayimEksigiModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Fire / Sayım Eksigi:</strong> Stok sayımı sırasında eksik olan ürünleri veya fireyi kaydetmek için kullanılır.
                            </div>
                            <input type="hidden" v-model="fireSayimEksigiForm.hareket_id">
                            <!-- Yön alanı kaldırıldı, otomatik olarak çıkış -->
                            <input type="hidden" v-model="fireSayimEksigiForm.yon" value="cikis">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fire_sayim_eksigi_stok_turu">Stok Türü *</label>
                                        <select class="form-control" v-model="fireSayimEksigiForm.stok_turu" @change="loadFireSayimEksigiStockItems" required>
                                            <option value="">Seçiniz</option>
                                            <option value="malzeme">Malzeme</option>
                                            <option value="urun">Ürün</option>
                                            <option value="esans">Esans</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fire_sayim_eksigi_kod">Kod Seçin *</label>
                                        <select class="form-control" v-model="fireSayimEksigiForm.kod" @change="getFireSayimEksigiLocation" required>
                                            <option value="">Kod Seçin</option>
                                            <option v-for="item in fireSayimEksigiStockItems" :key="item.kod" :value="item.kod">
                                                {{ item.kod }} - {{ item.isim }} (Stok: {{ item.stok }})
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fire_sayim_eksigi_hareket_turu">Hareket Türü *</label>
                                        <select class="form-control" v-model="fireSayimEksigiForm.hareket_turu" required>
                                            <option value="">Seçiniz</option>
                                            <option v-for="type in fireSayimEksigiMovementTypes" :key="type.value" :value="type.value">
                                                {{ type.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fire_sayim_eksigi_miktar">Miktar *</label>
                                        <input type="number" class="form-control" v-model.number="fireSayimEksigiForm.miktar" min="0.01"
                                            step="0.01" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="fire_sayim_eksigi_aciklama">Açıklama *</label>
                                <textarea class="form-control" v-model="fireSayimEksigiForm.aciklama" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeFireSayimEksigiModal"><i
                                    class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary" :class="{ 'loading': isSubmitting }" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <i v-else class="fas fa-save"></i>
                                {{ fireSayimEksigiSubmitButtonText }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sayım Fazlası Modal -->
        <div class="modal fade" :class="{ show: sayimFazlasiModalVisible }" 
             :style="{ display: sayimFazlasiModalVisible ? 'block' : 'none' }" 
             style="z-index: 1051" 
             @click="closeSayimFazlasiModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <form @submit.prevent="saveSayimFazlasi">
                        <div class="modal-header">
                            <h5 class="modal-title">Sayım Fazlası</h5>
                            <button type="button" class="close" @click="closeSayimFazlasiModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Sayım Fazlası:</strong> Stok sayımı sırasında fazla olan ürünleri kaydetmek için kullanılır.
                            </div>
                            <input type="hidden" v-model="sayimFazlasiForm.hareket_id">
                            <!-- Yön ve Hareket Türü alanları kaldırıldı, otomatik olarak ayarlanacak -->
                            <input type="hidden" v-model="sayimFazlasiForm.yon" value="giris">
                            <input type="hidden" v-model="sayimFazlasiForm.hareket_turu" value="sayim_fazlasi">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sayim_fazlasi_stok_turu">Stok Türü *</label>
                                        <select class="form-control" v-model="sayimFazlasiForm.stok_turu" @change="loadSayimFazlasiStockItems" required>
                                            <option value="">Seçiniz</option>
                                            <option value="malzeme">Malzeme</option>
                                            <option value="urun">Ürün</option>
                                            <option value="esans">Esans</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sayim_fazlasi_kod">Kod Seçin *</label>
                                        <select class="form-control" v-model="sayimFazlasiForm.kod" @change="getSayimFazlasiStockLocation" required>
                                            <option value="">Kod Seçin</option>
                                            <option v-for="item in sayimFazlasiStockItems" :key="item.kod" :value="item.kod">
                                                {{ item.kod }} - {{ item.isim }} (Stok: {{ item.stok }})
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sayim_fazlasi_miktar">Miktar *</label>
                                        <input type="number" class="form-control" v-model.number="sayimFazlasiForm.miktar" min="0.01"
                                            step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sayim_fazlasi_ilgili_belge_no">İlgili Belge No</label>
                                        <input type="text" class="form-control" v-model="sayimFazlasiForm.ilgili_belge_no">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="sayim_fazlasi_aciklama">Açıklama *</label>
                                <textarea class="form-control" v-model="sayimFazlasiForm.aciklama" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeSayimFazlasiModal"><i
                                    class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-success" :class="{ 'loading': isSubmitting }" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <i v-else class="fas fa-plus-circle"></i>
                                Sayım Fazlasını Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Stock Transfer Modal -->
        <div class="modal fade" :class="{ show: transferModalVisible }" 
             :style="{ display: transferModalVisible ? 'block' : 'none' }" 
             style="z-index: 1050" 
             @click="closeTransferModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <form @submit.prevent="saveTransfer">
                        <div class="modal-header">
                            <h5 class="modal-title">Yeni Stok Transferi</h5>
                            <button type="button" class="close" @click="closeTransferModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transfer_stok_turu">Stok Türü *</label>
                                        <select class="form-control" v-model="transferForm.stok_turu" @change="loadTransferStockItems" required>
                                            <option value="">Seçiniz</option>
                                            <option value="malzeme">Malzeme</option>
                                            <option value="urun">Ürün</option>
                                            <option value="esans">Esans</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transfer_kod">Transfer Edilecek Ürün *</label>
                                        <select class="form-control" v-model="transferForm.kod" @change="getTransferStockLocation" required>
                                            <option value="">Ürün Seçin</option>
                                            <option v-for="item in transferStockItems" :key="item.kod" :value="item.kod">
                                                {{ item.kod }} - {{ item.isim }} (Stok: {{ item.stok }})
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transfer_belge_no">Belge No</label>
                                        <input type="text" class="form-control" v-model="transferForm.ilgili_belge_no">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="transfer_aciklama">Açıklama *</label>
                                <textarea class="form-control" v-model="transferForm.aciklama" rows="3"
                                    required>Stok transferi</textarea>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <h5 v-if="(transferForm.stok_turu !== 'esans' && transferForm.kod) || (transferForm.stok_turu === 'esans' && transferForm.kod)">Kaynak Konum</h5>
                                    <div class="form-group" v-if="transferForm.stok_turu !== 'esans' && transferForm.kod">
                                        <label for="kaynak_depo">Kaynak Depo *</label>
                                        <input type="text" class="form-control" v-model="transferForm.kaynak_depo" readonly required>
                                    </div>
                                    <div class="form-group" v-if="transferForm.stok_turu !== 'esans' && transferForm.kod">
                                        <label for="kaynak_raf">Kaynak Raf *</label>
                                        <input type="text" class="form-control" v-model="transferForm.kaynak_raf" readonly required>
                                    </div>
                                    <div class="form-group" v-if="transferForm.stok_turu === 'esans' && transferForm.kod">
                                        <label for="tank_kodu">Kaynak Tank</label>
                                        <input type="text" class="form-control" v-model="transferForm.tank_kodu" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 v-if="(transferForm.stok_turu !== 'esans' && transferForm.kod) || (transferForm.stok_turu === 'esans' && transferForm.kod)">Hedef Konum</h5>
                                    <div class="form-group" v-if="transferForm.stok_turu !== 'esans' && transferForm.kod">
                                        <label for="hedef_depo">Hedef Depo *</label>
                                        <select class="form-control" v-model="transferForm.hedef_depo" @change="updateHedefRaflar" required>
                                            <option value="">Depo Seçin</option>
                                            <option v-for="location in locations" :key="location.depo_ismi" :value="location.depo_ismi">
                                                {{ location.depo_ismi }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group" v-if="transferForm.stok_turu !== 'esans' && transferForm.kod">
                                        <label for="hedef_raf">Hedef Raf *</label>
                                        <select class="form-control" v-model="transferForm.hedef_raf" required>
                                            <option value="">Raf Seçin</option>
                                            <option v-for="raf in hedefRaflar" :key="raf" :value="raf">
                                                {{ raf }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group" v-if="transferForm.stok_turu === 'esans' && transferForm.kod">
                                        <label for="hedef_tank_kodu">Hedef Tank *</label>
                                        <select class="form-control" v-model="transferForm.hedef_tank_kodu" required>
                                            <option value="">Tank Seçin</option>
                                            <option v-for="tank in tanks" :key="tank.tank_id" :value="tank.tank_kodu">
                                                {{ tank.tank_kodu }} - {{ tank.tank_ismi }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeTransferModal"><i
                                    class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary" :class="{ 'loading': isSubmitting }" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <i v-else class="fas fa-exchange-alt"></i> Transfer Et
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mal Kabul Modal -->
        <div class="modal fade" :class="{ show: malKabulModalVisible }" 
             :style="{ display: malKabulModalVisible ? 'block' : 'none' }" 
             style="z-index: 1050" 
             @click="closeMalKabulModal">
            <div class="modal-dialog modal-lg" @click.stop>
                <div class="modal-content">
                    <form @submit.prevent="saveMalKabul">
                        <div class="modal-header">
                            <h5 class="modal-title">Mal Kabul</h5>
                            <button type="button" class="close" @click="closeMalKabulModal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Mal Kabul:</strong> Alınan malzemeleri kabul etmek ve stoklara eklemek için kullanılır.
                            </div>
                            <input type="hidden" v-model="malKabulForm.hareket_id">
                            <!-- Yön ve Hareket Türü alanları kaldırıldı, otomatik olarak ayarlanacak -->
                            <input type="hidden" v-model="malKabulForm.yon" value="giris">
                            <input type="hidden" v-model="malKabulForm.hareket_turu" value="mal_kabul">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mal_kabul_kod">Malzeme Seçin *</label>
                                        <select class="form-control" v-model="malKabulForm.kod" @change="loadMalKabulSuppliers" required>
                                            <option value="">Malzeme Seçin</option>
                                            <option v-for="item in malKabulStockItems" :key="item.kod" :value="item.kod">
                                                {{ item.kod }} - {{ item.isim }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mal_kabul_miktar">Miktar *</label>
                                        <input type="number" class="form-control" v-model.number="malKabulForm.miktar" min="0.01"
                                            step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mal_kabul_ilgili_belge_no">İlgili Belge No</label>
                                        <input type="text" class="form-control" v-model="malKabulForm.ilgili_belge_no">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="mal_kabul_aciklama">Açıklama *</label>
                                <textarea class="form-control" v-model="malKabulForm.aciklama" rows="3" required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="mal_kabul_tedarikci">Tedarikçi *</label>
                                <select class="form-control" v-model="malKabulForm.tedarikci" required>
                                    <option value="">Tedarikçi Seçin</option>
                                    <option v-for="supplier in malKabulSuppliers" :key="supplier.tedarikci_id" :value="supplier.tedarikci_id">
                                        {{ supplier.tedarikci_ismi }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeMalKabulModal"><i
                                    class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-success" :class="{ 'loading': isSubmitting }" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <i v-else class="fas fa-check-circle"></i>
                                Mal Kabul Et
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Backdrop -->
        <div v-if="transferModalVisible || sayimFazlasiModalVisible || fireSayimEksigiModalVisible || malKabulModalVisible" class="modal-backdrop fade show" style="z-index: 1040;"></div>
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
    
    <script src="js/manuel_stok_hareket.js?t=<?php echo time(); ?>"></script>
</body>
</html>

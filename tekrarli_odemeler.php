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
if (!yetkisi_var('page:view:tekrarli_odemeler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Get current month and year
$current_year = date('Y');
$current_month = date('n');
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Tekrarlı Ödemeler - Parfüm ERP</title>
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
                <h1>Tekrarlı Ödeme Yönetimi</h1>
                <p>Kira, fatura ve aylık ödemeleri tanımlayın ve takip edin</p>
            </div>
            <?php if (yetkisi_var('action:tekrarli_odemeler:create')): ?>
                <button @click="openOdemeModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Ödeme Tanımla
                </button>
            <?php endif; ?>
        </div>

        <div v-if="alert.message" :class="'alert alert-' + alert.type" role="alert">
            {{ alert.message }}
        </div>

        <!-- Dönem Seçici ve Özet Kartları -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <label><i class="fas fa-calendar"></i> Dönem Seçin</label>
                        <div class="row">
                            <div class="col-6">
                                <select v-model="selectedYear" @change="loadOdemeDurumu"
                                    class="form-control form-control-sm">
                                    <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select v-model="selectedMonth" @change="loadOdemeDurumu"
                                    class="form-control form-control-sm">
                                    <option v-for="(month, index) in months" :key="index" :value="index + 1">{{ month }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"
                            style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.5rem; margin: 0;">{{ ozet.odeme_sayisi }}</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Tanımlı Ödeme</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"
                            style="background: var(--info); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items-center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.2rem; margin: 0;">{{ formatCurrency(ozet.toplam_tutar) }}</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.85rem;">Toplam</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"
                            style="background: var(--success); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items-center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.2rem; margin: 0;">{{ formatCurrency(ozet.odenen_tutar) }}</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.85rem;">Ödenen</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"
                            style="background: var(--warning); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.2rem; margin: 0;">{{ formatCurrency(ozet.bekleyen_tutar) }}</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.85rem;">Bekleyen</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ödemeler Listesi -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> {{ months[selectedMonth - 1] }} {{ selectedYear }} Ödeme Durumu</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-file-invoice"></i> Ödeme Adı</th>
                                <th><i class="fas fa-tag"></i> Tip</th>
                                <th><i class="fas fa-money-bill-wave"></i> Tutar</th>
                                <th><i class="fas fa-calendar-day"></i> Ödeme Günü</th>
                                <th><i class="fas fa-building"></i> Alıcı Firma</th>
                                <th><i class="fas fa-info-circle"></i> Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="7" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="odemeler.length === 0">
                                <td colspan="7" class="text-center p-4">Henüz tanımlı ödeme bulunmuyor.</td>
                            </tr>
                            <tr v-for="item in odemeler" :key="item.odeme_id">
                                <td class="actions">
                                    <?php if (yetkisi_var('action:tekrarli_odemeler:odeme_yap')): ?>
                                        <button v-if="item.odeme_durumu !== 'odendi'" @click="openOdemeYapModal(item)"
                                            class="btn btn-success btn-sm" title="Ödeme Yap">
                                            <i class="fas fa-money-check-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:tekrarli_odemeler:edit')): ?>
                                        <button @click="openOdemeModal(item)" class="btn btn-primary btn-sm"
                                            title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:tekrarli_odemeler:gecmis_goruntule')): ?>
                                        <button @click="openGecmisModal(item)" class="btn btn-info btn-sm" title="Geçmiş">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:tekrarli_odemeler:delete')): ?>
                                        <button @click="deleteOdeme(item)" class="btn btn-danger btn-sm" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td><strong>{{ item.odeme_adi }}</strong></td>
                                <td><span class="badge badge-secondary">{{ item.odeme_tipi }}</span></td>
                                <td>₺{{ formatCurrency(item.tutar) }}</td>
                                <td>Her ayın {{ item.odeme_gunu }}. günü</td>
                                <td>{{ item.alici_firma || '-' }}</td>
                                <td>
                                    <span v-if="item.odeme_durumu === 'odendi'" class="badge badge-success">
                                        <i class="fas fa-check"></i> Ödendi ({{ formatDate(item.odeme_tarihi) }})
                                    </span>
                                    <span v-else-if="item.odeme_durumu === 'gecikmiş'" class="badge badge-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Gecikmiş
                                    </span>
                                    <span v-else class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Bekliyor
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ödeme Tanımlama/Düzenleme Modal -->
        <div class="modal fade" id="odemeModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form @submit.prevent="saveOdeme">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title"><i class="fas fa-calendar-check"></i> {{ odemeData.odeme_id ? 'Ödeme Düzenle' : 'Yeni Ödeme Tanımla' }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Ödeme Adı *</label>
                                <input type="text" class="form-control" v-model="odemeData.odeme_adi" required
                                    placeholder="Örn: Ofis Kirası">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ödeme Tipi *</label>
                                        <select class="form-control" v-model="odemeData.odeme_tipi" required>
                                            <option value="">Seçiniz</option>
                                            <option value="Kira">Kira</option>
                                            <option value="Elektrik">Elektrik</option>
                                            <option value="Su">Su</option>
                                            <option value="Doğalgaz">Doğalgaz</option>
                                            <option value="İnternet">İnternet</option>
                                            <option value="Telefon">Telefon</option>
                                            <option value="Vergi">Vergi</option>
                                            <option value="Sigorta">Sigorta</option>
                                            <option value="Diğer">Diğer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tutar (₺) *</label>
                                        <input type="number" step="0.01" class="form-control" v-model="odemeData.tutar"
                                            required min="0.01">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ödeme Günü *</label>
                                        <select class="form-control" v-model="odemeData.odeme_gunu" required>
                                            <option v-for="day in 31" :key="day" :value="day">{{ day }}</option>
                                        </select>
                                        <small class="form-text text-muted">Her ayın kaçında ödenecek</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Durum</label>
                                        <select class="form-control" v-model="odemeData.aktif">
                                            <option value="1">Aktif</option>
                                            <option value="0">Pasif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Alıcı Firma</label>
                                <input type="text" class="form-control" v-model="odemeData.alici_firma"
                                    placeholder="Ödeme yapılacak firma">
                            </div>
                            <div class="form-group">
                                <label>Açıklama</label>
                                <textarea class="form-control" v-model="odemeData.aciklama" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                    class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Ödeme Yapma Modal -->
        <div class="modal fade" id="odemeYapModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form @submit.prevent="kaydetOdeme">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title"><i class="fas fa-money-check-alt"></i> Ödeme Yap</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Ödeme</label>
                                <input type="text" class="form-control" :value="odemeYapData.odeme_adi" readonly>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tip</label>
                                        <input type="text" class="form-control" :value="odemeYapData.odeme_tipi"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tutar</label>
                                        <input type="text" class="form-control"
                                            :value="'₺' + formatCurrency(odemeYapData.tutar)" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ödeme Tarihi *</label>
                                        <input type="date" class="form-control" v-model="odemeYapData.odeme_tarihi"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ödeme Yöntemi *</label>
                                        <select class="form-control" v-model="odemeYapData.odeme_yontemi" required>
                                            <option value="Havale">Havale/EFT</option>
                                            <option value="Nakit">Nakit</option>
                                            <option value="Çek">Çek</option>
                                            <option value="Kredi Kartı">Kredi Kartı</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Açıklama</label>
                                <textarea class="form-control" v-model="odemeYapData.aciklama" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                    class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Ödemeyi
                                Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Geçmiş Modal -->
        <div class="modal fade" id="gecmisModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header"
                        style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title"><i class="fas fa-history"></i> Ödeme Geçmişi - {{ gecmisData.odeme_adi
                            }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Dönem</th>
                                        <th>Tutar</th>
                                        <th>Ödeme Tarihi</th>
                                        <th>Yöntem</th>
                                        <th>Açıklama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="gecmisData.odemeler.length === 0">
                                        <td colspan="5" class="text-center">Henüz ödeme kaydı yok</td>
                                    </tr>
                                    <tr v-for="odeme in gecmisData.odemeler" :key="odeme.gecmis_id">
                                        <td>{{ odeme.donem_ay }}/{{ odeme.donem_yil }}</td>
                                        <td><strong>₺{{ formatCurrency(odeme.tutar) }}</strong></td>
                                        <td>{{ formatDate(odeme.odeme_tarihi) }}</td>
                                        <td>{{ odeme.odeme_yontemi }}</td>
                                        <td>{{ odeme.aciklama || '-' }}</td>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    loading: false,
                    alert: {
                        message: '',
                        type: ''
                    },
                    selectedYear: <?php echo $current_year; ?>,
                    selectedMonth: <?php echo $current_month; ?>,
                    years: [],
                    months: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                    odemeler: [],
                    ozet: {
                        toplam_tutar: 0,
                        odenen_tutar: 0,
                        bekleyen_tutar: 0,
                        odeme_sayisi: 0
                    },
                    odemeData: {
                        odeme_id: 0,
                        odeme_adi: '',
                        odeme_tipi: '',
                        tutar: 0,
                        odeme_gunu: 1,
                        alici_firma: '',
                        aciklama: '',
                        aktif: '1'
                    },
                    odemeYapData: {
                        odeme_id: 0,
                        odeme_adi: '',
                        odeme_tipi: '',
                        tutar: 0,
                        alici_firma: '',
                        odeme_tarihi: new Date().toISOString().split('T')[0],
                        odeme_yontemi: 'Havale',
                        aciklama: ''
                    },
                    gecmisData: {
                        odeme_id: 0,
                        odeme_adi: '',
                        odemeler: []
                    }
                }
            },
            methods: {
                showAlert(message, type) {
                    this.alert.message = message;
                    this.alert.type = type;
                    setTimeout(() => {
                        this.alert.message = '';
                    }, 5000);
                },
                formatCurrency(value) {
                    const number = Number(value);
                    if (!Number.isFinite(number)) {
                        return '0,00';
                    }
                    return number.toLocaleString('tr-TR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}.${month}.${year}`;
                },
                generateYears() {
                    const currentYear = new Date().getFullYear();
                    for (let i = currentYear - 2; i <= currentYear + 1; i++) {
                        this.years.push(i);
                    }
                },
                loadOdemeDurumu() {
                    this.loading = true;
                    fetch(`api_islemleri/tekrarli_odemeler_islemler.php?action=get_aylik_odeme_durumu&yil=${this.selectedYear}&ay=${this.selectedMonth}`)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.odemeler = response.data;
                                this.ozet = response.ozet;
                            } else {
                                this.showAlert('Ödeme durumu yüklenirken hata oluştu.', 'danger');
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            this.showAlert('Ödeme durumu yüklenirken bir hata oluştu.', 'danger');
                            this.loading = false;
                        });
                },
                openOdemeModal(item = null) {
                    if (item) {
                        this.odemeData = {
                            odeme_id: item.odeme_id,
                            odeme_adi: item.odeme_adi,
                            odeme_tipi: item.odeme_tipi,
                            tutar: item.tutar,
                            odeme_gunu: item.odeme_gunu,
                            alici_firma: item.alici_firma || '',
                            aciklama: item.aciklama || '',
                            aktif: item.aktif ? '1' : '0'
                        };
                    } else {
                        this.odemeData = {
                            odeme_id: 0,
                            odeme_adi: '',
                            odeme_tipi: '',
                            tutar: 0,
                            odeme_gunu: 1,
                            alici_firma: '',
                            aciklama: '',
                            aktif: '1'
                        };
                    }
                    $('#odemeModal').modal('show');
                },
                saveOdeme() {
                    const action = this.odemeData.odeme_id ? 'update_tekrarli_odeme' : 'add_tekrarli_odeme';
                    let formData = new FormData();
                    formData.append('action', action);
                    if (this.odemeData.odeme_id) {
                        formData.append('odeme_id', this.odemeData.odeme_id);
                    }
                    formData.append('odeme_adi', this.odemeData.odeme_adi);
                    formData.append('odeme_tipi', this.odemeData.odeme_tipi);
                    formData.append('tutar', this.odemeData.tutar);
                    formData.append('odeme_gunu', this.odemeData.odeme_gunu);
                    formData.append('alici_firma', this.odemeData.alici_firma);
                    formData.append('aciklama', this.odemeData.aciklama);
                    formData.append('aktif', this.odemeData.aktif);

                    fetch('api_islemleri/tekrarli_odemeler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#odemeModal').modal('hide');
                                this.loadOdemeDurumu();
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('Ödeme kaydedilirken bir hata oluştu.', 'danger');
                        });
                },
                deleteOdeme(item) {
                    if (!confirm(`"${item.odeme_adi}" ödemesini silmek istediğinizden emin misiniz?`)) {
                        return;
                    }

                    let formData = new FormData();
                    formData.append('action', 'delete_tekrarli_odeme');
                    formData.append('odeme_id', item.odeme_id);

                    fetch('api_islemleri/tekrarli_odemeler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                this.loadOdemeDurumu();
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('Ödeme silinirken bir hata oluştu.', 'danger');
                        });
                },
                openOdemeYapModal(item) {
                    this.odemeYapData = {
                        odeme_id: item.odeme_id,
                        odeme_adi: item.odeme_adi,
                        odeme_tipi: item.odeme_tipi,
                        tutar: item.tutar,
                        alici_firma: item.alici_firma || '',
                        odeme_tarihi: new Date().toISOString().split('T')[0],
                        odeme_yontemi: 'Havale',
                        aciklama: ''
                    };
                    $('#odemeYapModal').modal('show');
                },
                kaydetOdeme() {
                    let formData = new FormData();
                    formData.append('action', 'kaydet_odeme');
                    formData.append('odeme_id', this.odemeYapData.odeme_id);
                    formData.append('odeme_adi', this.odemeYapData.odeme_adi);
                    formData.append('odeme_tipi', this.odemeYapData.odeme_tipi);
                    formData.append('tutar', this.odemeYapData.tutar);
                    formData.append('donem_yil', this.selectedYear);
                    formData.append('donem_ay', this.selectedMonth);
                    formData.append('odeme_tarihi', this.odemeYapData.odeme_tarihi);
                    formData.append('odeme_yontemi', this.odemeYapData.odeme_yontemi);
                    formData.append('aciklama', this.odemeYapData.aciklama);
                    formData.append('alici_firma', this.odemeYapData.alici_firma);

                    fetch('api_islemleri/tekrarli_odemeler_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#odemeYapModal').modal('hide');
                                this.loadOdemeDurumu();
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('Ödeme kaydedilirken bir hata oluştu.', 'danger');
                        });
                },
                openGecmisModal(item) {
                    this.gecmisData.odeme_id = item.odeme_id;
                    this.gecmisData.odeme_adi = item.odeme_adi;
                    this.gecmisData.odemeler = [];

                    fetch(`api_islemleri/tekrarli_odemeler_islemler.php?action=get_odeme_gecmisi&odeme_id=${item.odeme_id}`)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.gecmisData.odemeler = response.data;
                            }
                        });

                    $('#gecmisModal').modal('show');
                }
            },
            mounted() {
                this.generateYears();
                this.loadOdemeDurumu();
            }
        });
        app.mount('#app');
    </script>
</body>

</html>

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
if (!yetkisi_var('page:view:personel_bordro')) {
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
    <title>Personel Bordro - Parfüm ERP</title>
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
                <h1>Personel Bordro Yönetimi</h1>
                <p>Bordrolu personellerin maaş ödemelerini ve avanslarını yönetin</p>
            </div>
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
                                <select v-model="selectedYear" @change="loadBordroOzeti"
                                    class="form-control form-control-sm">
                                    <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select v-model="selectedMonth" @change="loadBordroOzeti"
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
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.5rem; margin: 0;">{{ ozet.personel_sayisi }}</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Bordrolu Personel</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"
                            style="background: var(--info); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.2rem; margin: 0;">{{ formatCurrency(ozet.toplam_brut) }}</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.85rem;">Toplam Brüt</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"
                            style="background: var(--success); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.2rem; margin: 0;">{{ formatCurrency(ozet.toplam_odenen) }}</h3>
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
                            <h3 style="font-size: 1.2rem; margin: 0;">{{ formatCurrency(ozet.toplam_kalan) }}</h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.85rem;">Kalan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bordro Listesi -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> {{ months[selectedMonth - 1] }} {{ selectedYear }} Bordro Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-user"></i> Personel Adı</th>
                                <th><i class="fas fa-briefcase"></i> Pozisyon</th>
                                <th><i class="fas fa-building"></i> Departman</th>
                                <th><i class="fas fa-money-bill-wave"></i> Brüt Ücret</th>
                                <th><i class="fas fa-hand-holding-usd"></i> Avans</th>
                                <th><i class="fas fa-calculator"></i> Net Ödenecek</th>
                                <th><i class="fas fa-info-circle"></i> Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="8" class="text-center p-4"><i class="fas fa-spinner fa-spin"></i>
                                    Yükleniyor...</td>
                            </tr>
                            <tr v-else-if="bordroList.length === 0">
                                <td colspan="8" class="text-center p-4">Bu dönem için bordrolu personel bulunamadı.</td>
                            </tr>
                            <tr v-for="item in bordroList" :key="item.personel_id">
                                <td class="actions">
                                    <?php if (yetkisi_var('action:personel_bordro:odeme_yap')): ?>
                                        <button v-if="item.odeme_durumu === 'bekliyor'" @click="openMaasOdemeModal(item)"
                                            class="btn btn-success btn-sm" title="Maaş Öde">
                                            <i class="fas fa-money-check-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:personel_bordro:avans_ver')): ?>
                                        <button @click="openAvansModal(item)" class="btn btn-primary btn-sm"
                                            title="Avans Ver">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (yetkisi_var('action:personel_bordro:gecmis_goruntule')): ?>
                                        <button @click="openGecmisModal(item)" class="btn btn-info btn-sm" title="Geçmiş">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a :href="'generate_bordro_pdf.php?personel_id=' + item.personel_id" 
                                       target="_blank" class="btn btn-danger btn-sm" title="Bordro PDF">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </td>
                                <td><strong>{{ item.ad_soyad }}</strong></td>
                                <td>{{ item.pozisyon || '-' }}</td>
                                <td>{{ item.departman || '-' }}</td>
                                <td>₺{{ formatCurrency(item.aylik_brut_ucret) }}</td>
                                <td>₺{{ formatCurrency(item.avans_toplami) }}</td>
                                <td><strong>₺{{ formatCurrency(item.net_odenecek) }}</strong></td>
                                <td>
                                    <span v-if="item.odeme_durumu === 'odendi'"
                                        class="badge badge-success">Ödendi</span>
                                    <span v-else class="badge badge-warning">Bekliyor</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Maaş Ödeme Modal -->
        <div class="modal fade" id="maasOdemeModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form @submit.prevent="kaydetMaasOdemesi">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title"><i class="fas fa-money-check-alt"></i> Maaş Ödemesi</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Personel</label>
                                <input type="text" class="form-control" :value="maasOdemeData.personel_adi" readonly>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Brüt Ücret</label>
                                        <input type="text" class="form-control"
                                            :value="'₺' + formatCurrency(maasOdemeData.aylik_brut_ucret)" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Avans Toplamı</label>
                                        <input type="text" class="form-control"
                                            :value="'₺' + formatCurrency(maasOdemeData.avans_toplami)" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong>Net Ödenecek</strong></label>
                                <input type="text" class="form-control"
                                    :value="'₺' + formatCurrency(maasOdemeData.net_odenen)" readonly
                                    style="font-weight: bold; font-size: 1.2rem;">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ödeme Tarihi *</label>
                                        <input type="date" class="form-control" v-model="maasOdemeData.odeme_tarihi"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ödeme Tipi *</label>
                                        <select class="form-control" v-model="maasOdemeData.odeme_tipi" required>
                                            <option value="Havale">Havale/EFT</option>
                                            <option value="Nakit">Nakit</option>
                                            <option value="Çek">Çek</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Kasa Seçimi *</label>
                                <select class="form-control" v-model="maasOdemeData.kasa_secimi" required>
                                    <option value="TL">TL Kasası</option>
                                    <option value="USD">USD Kasası</option>
                                    <option value="EUR">EUR Kasası</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Açıklama</label>
                                <textarea class="form-control" v-model="maasOdemeData.aciklama" rows="2"></textarea>
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

        <!-- Avans Modal -->
        <div class="modal fade" id="avansModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form @submit.prevent="kaydetAvans">
                        <div class="modal-header"
                            style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                            <h5 class="modal-title"><i class="fas fa-hand-holding-usd"></i> Avans Ver</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Personel</label>
                                <input type="text" class="form-control" :value="avansData.personel_adi" readonly>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Avans Tutarı (₺) *</label>
                                        <input type="number" step="0.01" class="form-control"
                                            v-model="avansData.avans_tutari" required min="0.01">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Avans Tarihi *</label>
                                        <input type="date" class="form-control" v-model="avansData.avans_tarihi"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ödeme Tipi *</label>
                                        <select class="form-control" v-model="avansData.odeme_tipi" required>
                                            <option value="Nakit">Nakit</option>
                                            <option value="Havale">Havale/EFT</option>
                                            <option value="Çek">Çek</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kasa Seçimi *</label>
                                        <select class="form-control" v-model="avansData.kasa_secimi" required>
                                            <option value="TL">TL Kasası</option>
                                            <option value="USD">USD Kasası</option>
                                            <option value="EUR">EUR Kasası</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Açıklama</label>
                                <textarea class="form-control" v-model="avansData.aciklama" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i
                                    class="fas fa-times"></i> İptal</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Avansı
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
                        <h5 class="modal-title"><i class="fas fa-history"></i> Ödeme Geçmişi - {{
                            gecmisData.personel_adi }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#maasOdemeleri" role="tab">Maaş
                                    Ödemeleri</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#avanslar" role="tab">Avanslar</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="maasOdemeleri" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Dönem</th>
                                                <th>Brüt</th>
                                                <th>Avans</th>
                                                <th>Net Ödenen</th>
                                                <th>Tarih</th>
                                                <th>Tip</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="gecmisData.odemeler.length === 0">
                                                <td colspan="6" class="text-center">Henüz ödeme kaydı yok</td>
                                            </tr>
                                            <tr v-for="odeme in gecmisData.odemeler" :key="odeme.odeme_id">
                                                <td>{{ odeme.donem_ay }}/{{ odeme.donem_yil }}</td>
                                                <td>₺{{ formatCurrency(odeme.aylik_brut_ucret) }}</td>
                                                <td>₺{{ formatCurrency(odeme.avans_toplami) }}</td>
                                                <td><strong>₺{{ formatCurrency(odeme.net_odenen) }}</strong></td>
                                                <td>{{ formatDate(odeme.odeme_tarihi) }}</td>
                                                <td>{{ odeme.odeme_tipi }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="avanslar" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Dönem</th>
                                                <th>Tutar</th>
                                                <th>Tarih</th>
                                                <th>Tip</th>
                                                <th>Durum</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="gecmisData.avanslar.length === 0">
                                                <td colspan="5" class="text-center">Henüz avans kaydı yok</td>
                                            </tr>
                                            <tr v-for="avans in gecmisData.avanslar" :key="avans.avans_id">
                                                <td>{{ avans.donem_ay }}/{{ avans.donem_yil }}</td>
                                                <td><strong>₺{{ formatCurrency(avans.avans_tutari) }}</strong></td>
                                                <td>{{ formatDate(avans.avans_tarihi) }}</td>
                                                <td>{{ avans.odeme_tipi }}</td>
                                                <td>
                                                    <span v-if="avans.maas_odemesinde_kullanildi"
                                                        class="badge badge-success">Kullanıldı</span>
                                                    <span v-else class="badge badge-warning">Bekliyor</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
                    bordroList: [],
                    ozet: {
                        toplam_brut: 0,
                        toplam_odenen: 0,
                        toplam_kalan: 0,
                        personel_sayisi: 0
                    },
                    maasOdemeData: {
                        personel_id: 0,
                        personel_adi: '',
                        aylik_brut_ucret: 0,
                        avans_toplami: 0,
                        net_odenen: 0,
                        odeme_tarihi: new Date().toISOString().split('T')[0],
                        odeme_tipi: 'Havale',
                        kasa_secimi: 'TL',
                        aciklama: ''
                    },
                    avansData: {
                        personel_id: 0,
                        personel_adi: '',
                        avans_tutari: 0,
                        avans_tarihi: new Date().toISOString().split('T')[0],
                        odeme_tipi: 'Nakit',
                        kasa_secimi: 'TL',
                        aciklama: ''
                    },
                    gecmisData: {
                        personel_id: 0,
                        personel_adi: '',
                        odemeler: [],
                        avanslar: []
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
                    for (let i = currentYear - 5; i <= currentYear + 1; i++) {
                        this.years.push(i);
                    }
                },
                async loadBordroOzeti(retryAttempt = 0) {
                    this.loading = true;
                    try {
                        const response = await fetch(`api_islemleri/personel_bordro_islemler.php?action=get_aylik_bordro_ozeti&yil=${this.selectedYear}&ay=${this.selectedMonth}`);
                        const rawText = await response.text();
                        let payload = null;

                        try {
                            payload = JSON.parse(rawText);
                        } catch (e) {
                            payload = null;
                        }

                        if (!response.ok) {
                            // Ilk acilista gecici 500 hatalari icin kisa retry uygula
                            if (response.status >= 500 && retryAttempt < 2) {
                                await new Promise((resolve) => setTimeout(resolve, 600));
                                return this.loadBordroOzeti(retryAttempt + 1);
                            }

                            const serverMsg = payload && payload.message ? payload.message : (rawText || 'Sunucudan detay alinamadi.');
                            this.showAlert(`Bordro ozeti yuklenemedi (HTTP ${response.status}): ${serverMsg}`, 'danger');
                            return;
                        }

                        if (payload && payload.status === 'success') {
                            this.bordroList = payload.data || [];
                            this.ozet = payload.ozet || this.ozet;
                        } else {
                            const appMsg = payload && payload.message ? payload.message : 'Bordro ozeti yuklenirken hata olustu.';
                            this.showAlert(appMsg, 'danger');
                        }
                    } catch (error) {
                        if (retryAttempt < 2) {
                            await new Promise((resolve) => setTimeout(resolve, 600));
                            return this.loadBordroOzeti(retryAttempt + 1);
                        }
                        this.showAlert('Bordro ozeti yuklenirken baglanti hatasi olustu.', 'danger');
                    } finally {
                        this.loading = false;
                    }
                },
                openMaasOdemeModal(item) {
                    this.maasOdemeData = {
                        personel_id: item.personel_id,
                        personel_adi: item.ad_soyad,
                        aylik_brut_ucret: item.aylik_brut_ucret,
                        avans_toplami: item.avans_toplami,
                        net_odenen: item.net_odenecek,
                        odeme_tarihi: new Date().toISOString().split('T')[0],
                        odeme_tipi: 'Havale',
                        kasa_secimi: 'TL',
                        aciklama: ''
                    };
                    $('#maasOdemeModal').modal('show');
                },
                kaydetMaasOdemesi() {
                    let formData = new FormData();
                    formData.append('action', 'kaydet_maas_odemesi');
                    formData.append('personel_id', this.maasOdemeData.personel_id);
                    formData.append('personel_adi', this.maasOdemeData.personel_adi);
                    formData.append('donem_yil', this.selectedYear);
                    formData.append('donem_ay', this.selectedMonth);
                    formData.append('aylik_brut_ucret', this.maasOdemeData.aylik_brut_ucret);
                    formData.append('avans_toplami', this.maasOdemeData.avans_toplami);
                    formData.append('net_odenen', this.maasOdemeData.net_odenen);
                    formData.append('odeme_tarihi', this.maasOdemeData.odeme_tarihi);
                    formData.append('odeme_tipi', this.maasOdemeData.odeme_tipi);
                    formData.append('kasa_secimi', this.maasOdemeData.kasa_secimi);
                    formData.append('aciklama', this.maasOdemeData.aciklama);

                    fetch('api_islemleri/personel_bordro_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#maasOdemeModal').modal('hide');
                                this.loadBordroOzeti();
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('Maaş ödemesi kaydedilirken bir hata oluştu.', 'danger');
                        });
                },
                openAvansModal(item) {
                    this.avansData = {
                        personel_id: item.personel_id,
                        personel_adi: item.ad_soyad,
                        avans_tutari: 0,
                        avans_tarihi: new Date().toISOString().split('T')[0],
                        odeme_tipi: 'Nakit',
                        kasa_secimi: 'TL',
                        aciklama: ''
                    };
                    $('#avansModal').modal('show');
                },
                kaydetAvans() {
                    let formData = new FormData();
                    formData.append('action', 'kaydet_avans');
                    formData.append('personel_id', this.avansData.personel_id);
                    formData.append('personel_adi', this.avansData.personel_adi);
                    formData.append('avans_tutari', this.avansData.avans_tutari);
                    formData.append('avans_tarihi', this.avansData.avans_tarihi);
                    formData.append('donem_yil', this.selectedYear);
                    formData.append('donem_ay', this.selectedMonth);
                    formData.append('odeme_tipi', this.avansData.odeme_tipi);
                    formData.append('kasa_secimi', this.avansData.kasa_secimi);
                    formData.append('aciklama', this.avansData.aciklama);

                    fetch('api_islemleri/personel_bordro_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#maasOdemeModal').modal('hide');
                                this.loadBordroOzeti();
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('Maaş ödemesi kaydedilirken bir hata oluştu.', 'danger');
                        });
                },
                openAvansModal(item) {
                    this.avansData = {
                        personel_id: item.personel_id,
                        personel_adi: item.ad_soyad,
                        avans_tutari: 0,
                        avans_tarihi: new Date().toISOString().split('T')[0],
                        odeme_tipi: 'Nakit',
                        aciklama: ''
                    };
                    $('#avansModal').modal('show');
                },
                kaydetAvans() {
                    let formData = new FormData();
                    formData.append('action', 'kaydet_avans');
                    formData.append('personel_id', this.avansData.personel_id);
                    formData.append('personel_adi', this.avansData.personel_adi);
                    formData.append('avans_tutari', this.avansData.avans_tutari);
                    formData.append('avans_tarihi', this.avansData.avans_tarihi);
                    formData.append('donem_yil', this.selectedYear);
                    formData.append('donem_ay', this.selectedMonth);
                    formData.append('odeme_tipi', this.avansData.odeme_tipi);
                    formData.append('aciklama', this.avansData.aciklama);

                    fetch('api_islemleri/personel_bordro_islemler.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.showAlert(response.message, 'success');
                                $('#avansModal').modal('hide');
                                this.loadBordroOzeti();
                            } else {
                                this.showAlert(response.message, 'danger');
                            }
                        })
                        .catch(error => {
                            this.showAlert('Avans kaydedilirken bir hata oluştu.', 'danger');
                        });
                },
                openGecmisModal(item) {
                    this.gecmisData.personel_id = item.personel_id;
                    this.gecmisData.personel_adi = item.ad_soyad;
                    this.gecmisData.odemeler = [];
                    this.gecmisData.avanslar = [];

                    // Maaş ödemelerini yükle
                    fetch(`api_islemleri/personel_bordro_islemler.php?action=get_personel_odeme_gecmisi&personel_id=${item.personel_id}`)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.gecmisData.odemeler = response.data;
                            }
                        });

                    // Avansları yükle
                    fetch(`api_islemleri/personel_bordro_islemler.php?action=get_personel_avanslar&personel_id=${item.personel_id}`)
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                this.gecmisData.avanslar = response.data;
                            }
                        });

                    $('#gecmisModal').modal('show');
                }
            },
            mounted() {
                this.generateYears();
                this.loadBordroOzeti();
            }
        });
        app.mount('#app');
    </script>
</body>

</html>

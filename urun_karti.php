<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only staff can access this page
if (!isset($_SESSION['taraf']) || $_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

// Page-level permission check
if (!yetkisi_var('page:view:urunler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Get product code from URL
$urun_kodu = isset($_GET['urun_kodu']) ? (int) $_GET['urun_kodu'] : 0;

if ($urun_kodu == 0) {
    header('Location: urunler.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ürün Kartı - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="assets/css/urun_karti.css">
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
    <div id="app" class="main-content" v-cloak>
        <div class="page-header">
            <a href="urunler.php" class="back-btn" title="Geri Dön">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1><i class="fas fa-id-card"></i> Ürün Kartı</h1>
            </div>
        </div>

        <div v-if="loading" class="text-center p-5">
            <i class="fas fa-spinner fa-spin fa-3x" style="color: var(--primary);"></i>
            <p class="mt-3">Yükleniyor...</p>
        </div>

        <div v-else-if="error" class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {{ error }}
        </div>

        <div v-else-if="productData">
            <!-- Ürün Fotoğrafları -->
            <div class="card" v-if="productData.photos.length > 0">
                <div class="card-header">
                    <h2><i class="fas fa-images"></i> Ürün Fotoğrafları</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Bu ürüne ait {{ productData.photos.length }} adet fotoğraf
                        bulunmaktadır. Ana fotoğraf yıldız işareti ile belirtilmiştir.
                    </p>
                    <div class="photo-gallery">
                        <div v-for="(photo, index) in productData.photos" :key="photo.fotograf_id" class="photo-item">
                            <div v-if="photo.ana_fotograf == 1" class="primary-badge">
                                <i class="fas fa-star"></i> ANA FOTOĞRAF
                            </div>
                            <a :href="photo.dosya_yolu" data-fancybox="product-photos" :data-caption="photo.dosya_adi">
                                <img :src="photo.dosya_yolu" :alt="photo.dosya_adi" style="cursor: pointer;"
                                    title="Fotoğrafı büyütmek için tıklayın">
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ürün Bilgileri -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Ürün Bilgileri</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Ürünün temel bilgileri, fiyatlandırma ve stok konumu
                        detayları aşağıda gösterilmektedir.
                    </p>
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-barcode info-icon"
                                    style="background: linear-gradient(135deg, #0891b2, #06b6d4);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Ürün Kodu</small>
                                    <p class="mb-0 info-value">{{ productData.product.urun_kodu }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-tag info-icon"
                                    style="background: linear-gradient(135deg, #14b8a6, #0891b2);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Ürün İsmi</small>
                                    <p class="mb-0 info-value">{{ productData.product.urun_ismi }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-ruler info-icon"
                                    style="background: linear-gradient(135deg, #0ea5e9, #0284c7);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Birim</small>
                                    <p class="mb-0 info-value">{{ productData.product.birim }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-money-bill-wave info-icon"
                                    style="background: linear-gradient(135deg, #22c55e, #16a34a);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Satış Fiyatı</small>
                                    <p class="mb-0 info-value">{{ formatCurrency(productData.product.satis_fiyati) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3"
                            v-if="productData.product.teorik_maliyet !== undefined">
                            <div class="info-item">
                                <i class="fas fa-calculator info-icon"
                                    style="background: linear-gradient(135deg, #f59e0b, #d97706);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Teorik Maliyet</small>
                                    <p class="mb-0 info-value">{{ formatCurrency(productData.product.teorik_maliyet) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-warehouse info-icon"
                                    style="background: linear-gradient(135deg, #0ea5e9, #0284c7);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Depo</small>
                                    <p class="mb-0 info-value">{{ productData.product.depo }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-boxes info-icon"
                                    style="background: linear-gradient(135deg, #22c55e, #16a34a);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Mevcut Stok</small>
                                    <p class="mb-0 info-value">{{ productData.product.stok_miktari }} {{
                                        productData.product.birim }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-cube info-icon"
                                    style="background: linear-gradient(135deg, #f59e0b, #d97706);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Raf</small>
                                    <p class="mb-0 info-value">{{ productData.product.raf }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-exclamation-triangle info-icon"
                                    style="background: linear-gradient(135deg, #ef4444, #dc2626);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Kritik Stok</small>
                                    <p class="mb-0 info-value">{{ productData.product.kritik_stok_seviyesi }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3" v-if="productData.product.not_bilgisi">
                            <div class="info-item">
                                <i class="fas fa-sticky-note info-icon"
                                    style="background: linear-gradient(135deg, #0891b2, #06b6d4);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Not</small>
                                    <p class="mb-0 info-value">{{ productData.product.not_bilgisi }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stok Analizi -->
            <div class="card" v-if="stockGap">
                <div class="card-header">
                    <h2><i class="fas fa-chart-line"></i> Stok Analizi</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Mevcut stok durumu ve kritik seviye karşılaştırması. Stok
                        açığı varsa üretilebilir miktar ve eksik bileşenler gösterilir.
                    </p>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert" :class="stockGap.hasGap ? 'alert-warning' : 'alert-success'">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <strong>Kritik Stok Seviyesi:</strong> {{ productData.product.kritik_stok_seviyesi
                                    }} {{
                                    productData.product.birim }} |
                                    <strong>Mevcut Stok:</strong> {{ productData.product.stok_miktari }} {{
                                    productData.product.birim }}
                                    <span v-if="stockGap.hasGap">
                                        | <strong>Stok Açığı:</strong> {{ stockGap.gap }} {{ productData.product.birim
                                        }}
                                        ({{ stockGap.gapPercentage }}%)
                                    </span>
                                    <span v-else>
                                        | <strong>Stok yeterli!</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" v-if="stockGap.hasGap">
                            <div class="stat-card" style="background: linear-gradient(135deg, #0891b2, #06b6d4);">
                                <i class="fas fa-calculator"></i>
                                <h3>{{ stockGap.producibleForGap }}</h3>
                                <p>Üretilebilir Adet</p>
                            </div>
                        </div>
                        <div class="col-md-6" v-if="stockGap.hasGap && !stockGap.canCoverGap">
                            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h3>{{ stockGap.gap - stockGap.producibleForGap }}</h3>
                                <p>Ek Eksiklik</p>
                            </div>
                        </div>
                        <div class="col-md-6" v-else-if="!stockGap.hasGap">
                            <div class="stat-card" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                                <i class="fas fa-check-circle"></i>
                                <h3>{{ productData.product.stok_miktari }}</h3>
                                <p>Yeterli Stok</p>
                            </div>
                        </div>

                        <!-- Üretimdeki Miktar Gösterimi (Gelişmiş) -->
                        <div class="col-md-6 mt-3"
                            v-if="productData.production && productData.production.uretimdeki_toplam_planlanan_miktar > 0">
                            <div class="stat-card" :style="stockGap && stockGap.hasGap && parseFloat(productData.production.uretimdeki_toplam_planlanan_miktar) >= stockGap.gap 
                                         ? 'background: linear-gradient(135deg, #7c3aed, #6d28d9); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);' 
                                         : 'background: linear-gradient(135deg, #8b5cf6, #7c3aed);'">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="fas fa-industry mr-2" style="font-size: 1.8rem;"></i>
                                    <i v-if="stockGap && stockGap.hasGap && parseFloat(productData.production.uretimdeki_toplam_planlanan_miktar) >= stockGap.gap"
                                        class="fas fa-check-circle text-white ml-2"
                                        title="Bu üretim stok açığını kapatacak"
                                        style="font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></i>
                                </div>
                                <h3 class="mb-1">{{
                                    parseFloat(productData.production.uretimdeki_toplam_planlanan_miktar).toFixed(2) }}
                                </h3>
                                <p class="mb-1 font-weight-bold">Üretimde (Planlanan)</p>
                                <small class="d-block opacity-75 mb-2">{{ productData.production.is_emri_sayisi }} adet
                                    aktif iş emri</small>

                                <div v-if="stockGap && stockGap.hasGap && parseFloat(productData.production.uretimdeki_toplam_planlanan_miktar) >= stockGap.gap"
                                    class="mt-2 px-3 py-1 rounded"
                                    style="background: rgba(255,255,255,0.2); font-size: 0.8rem; border: 1px solid rgba(255,255,255,0.3);">
                                    <i class="fas fa-shield-alt mr-1"></i> Kritik stok açığı kapanıyor
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-3" v-if="stockGap.hasGap && stockGap.canCoverGap">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Harika!</strong>
                            Mevcut bileşenlerle stok açığını kapatabilirsiniz. {{ stockGap.gap }} adet ürün üretip
                            kritik seviyeye ulaşabilirsiniz.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stok Hareketleri -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-exchange-alt"></i> Son Stok Hareketleri</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Bu ürüne ait en son {{ recentMovements.length }} stok
                    hareketi gösterilmektedir. Tüm hareketler için stok hareketleri sayfasını ziyaret edebilirsiniz.
                </p>
                <div v-if="recentMovements.length === 0" class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Henüz stok hareketi bulunmuyor.</p>
                </div>
                <div v-else class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar"></i> Tarih</th>
                                <th><i class="fas fa-arrows-alt-v"></i> Yön</th>
                                <th><i class="fas fa-tag"></i> Hareket Türü</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Miktar</th>
                                <th><i class="fas fa-warehouse"></i> Depo</th>
                                <th><i class="fas fa-cube"></i> Raf</th>
                                <th><i class="fas fa-comment"></i> Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="movement in recentMovements" :key="movement.hareket_id">
                                <td><span class="text-nowrap">{{ formatDate(movement.tarih) }}</span></td>
                                <td>
                                    <span :class="movement.yon === 'giris' ? 'badge-in' : 'badge-out'">
                                        <i
                                            :class="movement.yon === 'giris' ? 'fas fa-arrow-down' : 'fas fa-arrow-up'"></i>
                                        {{ movement.yon === 'giris' ? 'Giriş' : 'Çıkış' }}
                                    </span>
                                </td>
                                <td>{{ movement.hareket_turu }}</td>
                                <td><strong>{{ movement.miktar }} {{ movement.birim }}</strong></td>
                                <td>{{ movement.depo || '-' }}</td>
                                <td>{{ movement.raf || '-' }}</td>
                                <td class="text-truncate" :title="movement.aciklama || '-'">{{ movement.aciklama ||
                                    '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <small class="text-muted">Tüm stok hareketleri için <a href="manuel_stok_hareket.php">stok
                                hareketleri sayfasını</a> ziyaret edin</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ürün Ağacı Bileşenleri -->
        <div class="card" v-if="productData && productData.bom_components && productData.bom_components.length > 0">
            <div class="card-header">
                <h2><i class="fas fa-sitemap"></i> Ürün Ağacı Bileşenleri</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Bu ürünü üretmek için gereken {{
                    productData.bom_components.length }} adet bileşen ve mevcut stoklarla üretilebilir miktar
                    bilgileri aşağıda gösterilmektedir.
                </p>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Bu ürün ağacına göre mevcut stoklarla üretilebilir:</strong>
                        <span class="font-weight-bold ml-2">{{ producibleQuantity }} adet</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-layer-group"></i> Bileşen Türü</th>
                                <th><i class="fas fa-barcode"></i> Kod</th>
                                <th><i class="fas fa-tag"></i> İsim</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Gerekli Miktar</th>
                                <th><i class="fas fa-warehouse"></i> Mevcut Stok</th>
                                <th><i class="fas fa-industry"></i> Planlanan Üretim</th>
                                <th><i class="fas fa-calculator"></i> Üretilebilir</th>
                                <!-- Yeni Kolon: Kritik Stok İçin Eksik -->
                                <th v-if="stockGap && stockGap.hasGap"><i
                                        class="fas fa-exclamation-triangle text-warning"></i> Kritik Stok İçin Eksik
                                </th>
                                <th v-if="contractsLoaded"><i class="fas fa-file-contract"></i> Çerçeve Sözleşmesi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="component in productData.bom_components" :key="component.id">
                                <td>
                                    <span class="badge badge-info">{{ component.bilesen_turu }}</span>
                                </td>
                                <td>{{ component.bilesen_kodu }}</td>
                                <td><strong>{{ component.bilesen_ismi }}</strong></td>
                                <td>{{ component.bilesen_miktari }} {{ component.bilesen_birim }}</td>
                                <td>{{ component.bilesen_stok }} {{ component.bilesen_birim }}</td>
                                <td>
                                    <span v-if="component.bilesen_turu === 'esans' && component.planlanan_uretim > 0"
                                        class="badge badge-warning">
                                        <i class="fas fa-clock mr-1"></i> {{
                                        parseFloat(component.planlanan_uretim).toFixed(2) }} {{ component.bilesen_birim
                                        }}
                                    </span>
                                    <span v-else class="text-muted">-</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold" :class="{
                                                  'text-success': canProduceEnough(component),
                                                  'text-danger': !canProduceEnough(component)
                                              }">
                                        {{ component.bilesen_miktari > 0 ? Math.floor(component.bilesen_stok /
                                        component.bilesen_miktari) : 0 }} adet
                                    </span>
                                </td>
                                <!-- Eksik Miktar Gösterimi -->
                                <td v-if="stockGap && stockGap.hasGap">
                                    <span v-if="getShortfallForComponent(component.bilesen_kodu) > 0"
                                        class="text-danger font-weight-bold">
                                        <i class="fas fa-arrow-down mr-1"></i>
                                        {{ Math.ceil(getShortfallForComponent(component.bilesen_kodu)) }} {{
                                        component.bilesen_birim }}
                                    </span>
                                    <span v-else class="text-success">
                                        <i class="fas fa-check mr-1"></i> Yeterli
                                    </span>
                                </td>
                                <td v-if="contractsLoaded">
                                    <div
                                        v-if="component.bilesen_turu !== 'esans' && getContractForComponent(component.bilesen_kodu)">
                                        <span class="badge badge-success">
                                            {{ getContractForComponent(component.bilesen_kodu).sozlesme_id }} - {{
                                            getContractForComponent(component.bilesen_kodu).tedarikci_adi }}
                                        </span>
                                        <div class="mt-1">
                                            <small class="text-muted">Kalan: {{
                                                getContractForComponent(component.bilesen_kodu).kalan_miktar }} {{
                                                component.bilesen_birim }}</small>
                                        </div>
                                    </div>
                                    <div v-else-if="component.bilesen_turu !== 'esans'">
                                        <span class="badge badge-warning">Yok</span>
                                    </div>
                                    <div v-else>
                                        <span class="text-muted">-</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sipariş Bilgileri -->
        <div class="card" v-if="productData && productData.orders">
            <div class="card-header">
                <h2><i class="fas fa-shopping-cart"></i> Sipariş Bilgileri</h2>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Bu ürüne ait aktif ve tamamlanmış sipariş özeti ile detaylı
                    sipariş listesi aşağıda gösterilmektedir.
                </p>
                <!-- Sipariş Özeti -->
                <div class="row mb-3">
                    <div class="col-lg-3 col-md-6 col-6 mb-2">
                        <div class="stat-summary-card" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                            <i class="fas fa-shopping-bag"></i>
                            <div>
                                <h4 class="mb-0">{{ activeOrders.length || 0 }}</h4>
                                <small class="text-light">Aktif Sipariş</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6 mb-2">
                        <div class="stat-summary-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="fas fa-sort-numeric-up"></i>
                            <div>
                                <h4 class="mb-0">{{ calculateActiveOrdersTotal() || 0 }}</h4>
                                <small class="text-light">Aktif Miktar</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6 mb-2">
                        <div class="stat-summary-card" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4 class="mb-0">{{ (productData.orders.summary &&
                                    productData.orders.summary.tamamlanan_siparis) || 0 }}</h4>
                                <small class="text-light">Tamamlanan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6 mb-2">
                        <div class="stat-summary-card" style="background: linear-gradient(135deg, #14b8a6, #0891b2);">
                            <i class="fas fa-tools"></i>
                            <div>
                                <h4 class="mb-0">{{ (productData.orders.summary &&
                                    productData.orders.summary.hazirlanan_siparis) || 0 }}</h4>
                                <small class="text-light">Hazırlanan</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sipariş Listesi -->
                <div v-if="activeOrders.length === 0" class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Henüz aktif sipariş bulunmuyor.</p>
                </div>
                <div v-else class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Sipariş No</th>
                                <th><i class="fas fa-user"></i> Müşteri</th>
                                <th><i class="fas fa-calendar"></i> Tarih</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Miktar</th>
                                <th><i class="fas fa-info-circle"></i> Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in activeOrders" :key="order.kalem_id">
                                <td><strong>#{{ order.siparis_id }}</strong></td>
                                <td>{{ order.musteri_adi }}</td>
                                <td><span class="text-nowrap">{{ formatDate(order.siparis_tarihi) }}</span></td>
                                <td>{{ order.adet }} {{ productData.product.birim }}</td>
                                <td>
                                    <span :class="getStatusBadgeClass(order.siparis_durum)">
                                        {{ order.siparis_durum }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Fancybox CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>

    <script>
        window.urunKodu = <?php echo $urun_kodu; ?>;
    </script>
    <script src="assets/js/urun_karti.js"></script>
</body>

</html>
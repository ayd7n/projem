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
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

    :root {
      --primary: #0891b2;
      --primary-light: #06b6d4;
      --primary-dark: #0e7490;
      --secondary: #14b8a6;
      --accent: #f59e0b;
      --success: #22c55e;
      --danger: #ef4444;
      --warning: #f59e0b;
      --info: #0ea5e9;
      --bg-main: #fafbfc;
      --bg-secondary: #f3f4f6;
      --card-bg: #ffffff;
      --border-light: #e5e7eb;
      --border-medium: #d1d5db;
      --text-dark: #111827;
      --text-medium: #374151;
      --text-light: #6b7280;
      --text-lighter: #9ca3af;
      --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.06);
      --shadow-md: 0 4px 8px rgba(0, 0, 0, 0.08);
      --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1);
      --transition-base: 250ms cubic-bezier(0.4, 0, 0.2, 1);
      --radius-md: 12px;
      --radius-lg: 16px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Poppins", "Ubuntu", -apple-system, sans-serif;
      background: var(--bg-main);
      color: var(--text-dark);
      min-height: 100vh;
      line-height: 1.6;
    }

    .main-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      background: white;
      padding: 20px;
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
    }

    .product-info {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .product-avatar {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 24px;
    }

    .product-details h1 {
      font-size: 1.8rem;
      font-weight: 600;
      margin: 0;
      color: var(--text-dark);
    }

    .product-details .product-meta {
      display: flex;
      gap: 16px;
      margin-top: 8px;
    }

    .product-details .meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.9rem;
      color: var(--text-medium);
      padding: 4px 8px;
      background: rgba(14, 165, 233, 0.05);
      border-radius: 6px;
      border: 1px solid rgba(14, 165, 233, 0.1);
    }

    .product-details .meta-item i {
      color: var(--primary);
    }

    .back-btn {
      background: var(--bg-secondary);
      color: var(--text-medium);
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      transition: var(--transition-base);
      border: 1px solid var(--border-light);
    }

    .back-btn:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-2px);
    }

    .status-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(72px, 1fr));
      gap: 6px;
      margin-bottom: 24px;
    }

    .status-card {
      background: white;
      border-radius: var(--radius-md);
      padding: 8px;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-light);
      transition: var(--transition-base);
    }

    .status-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .status-card.danger {
      border-left: 4px solid var(--danger);
    }

    .status-card.warning {
      border-left: 4px solid var(--warning);
    }

    .status-card.success {
      border-left: 4px solid var(--success);
    }

    .status-card.info {
      border-left: 4px solid var(--info);
    }

    .status-card-icon {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 6px;
      font-size: 14px;
    }

    .status-card.danger .status-card-icon {
      background: rgba(239, 68, 68, 0.1);
      color: var(--danger);
    }

    .status-card.warning .status-card-icon {
      background: rgba(245, 158, 11, 0.1);
      color: var(--warning);
    }

    .status-card.success .status-card-icon {
      background: rgba(34, 197, 94, 0.1);
      color: var(--success);
    }

    .status-card.info .status-card-icon {
      background: rgba(14, 165, 233, 0.1);
      color: var(--info);
    }

    .status-card-title {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--text-medium);
      margin-bottom: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-card-value {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 2px;
    }

    .status-card-subtitle {
      font-size: 0.7rem;
      color: var(--text-light);
    }

    .action-section {
      background: white;
      border-radius: var(--radius-lg);
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-light);
    }

    .action-section h3 {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 16px;
      color: var(--text-dark);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .action-item {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      padding: 16px;
      border-radius: 12px;
      margin-bottom: 12px;
      transition: var(--transition-base);
    }

    .action-item.critical {
      background: rgba(239, 68, 68, 0.05);
      border: 1px solid rgba(239, 68, 68, 0.1);
    }

    .action-item.warning {
      background: rgba(245, 158, 11, 0.05);
      border: 1px solid rgba(245, 158, 11, 0.1);
    }

    .action-item.info {
      background: rgba(14, 165, 233, 0.05);
      border: 1px solid rgba(14, 165, 233, 0.1);
    }

    .action-item:hover {
      transform: translateX(4px);
    }

    .action-item-icon {
      width: 40px;
      padding-top: 4px;
      flex-shrink: 0;
    }

    .action-item.critical .action-item-icon {
      color: var(--danger);
    }

    .action-item.warning .action-item-icon {
      color: var(--warning);
    }

    .action-item.info .action-item-icon {
      color: var(--info);
    }

    .action-item-content h4 {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 4px;
      color: var(--text-dark);
    }

    .action-item-content p {
      font-size: 0.9rem;
      color: var(--text-medium);
      margin: 0;
    }

    .details-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
      margin-bottom: 24px;
    }

    .detail-card {
      background: white;
      border-radius: var(--radius-lg);
      padding: 24px;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-light);
    }

    .detail-card h4 {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 16px;
      color: var(--text-dark);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 0;
      border-bottom: 1px solid var(--border-light);
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      font-weight: 500;
      color: var(--text-medium);
    }

    .info-value {
      font-weight: 600;
      color: var(--text-dark);
    }

    .photo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 12px;
    }

    .photo-item {
      aspect-ratio: 1;
      border-radius: 12px;
      overflow: hidden;
      cursor: pointer;
      transition: var(--transition-base);
      border: 1px solid var(--border-light);
    }

    .photo-item:hover {
      transform: scale(1.05);
    }

    .photo-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .loading-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-light);
    }

    .spinner {
      width: 40px;
      height: 40px;
      border: 4px solid var(--border-light);
      border-top: 4px solid var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 16px;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    [v-cloak] {
      display: none !important;
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 16px;
      }

      .page-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
      }

      .product-info {
        flex-direction: column;
        gap: 12px;
      }

      .status-grid {
        grid-template-columns: 1fr;
      }

      .details-grid {
        grid-template-columns: 1fr;
      }
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
  <div id="app" class="main-content" v-cloak>
    <!-- Page Header -->
    <div class="page-header">
      <div class="product-info">
        <div class="product-avatar">
          <i class="fas fa-box"></i>
        </div>
        <div class="product-details" v-if="productData">
          <h1>{{ productData.product.urun_ismi }}</h1>
          <div class="product-meta">
            <span class="meta-item">
              <i class="fas fa-hashtag"></i>
              <strong>Kod:</strong> {{ productData.product.urun_kodu }}
            </span>
            <span class="meta-item">
              <i class="fas fa-balance-scale"></i>
              <strong>Birim:</strong> {{ productData.product.birim }}
            </span>
          </div>
        </div>
      </div>
      <a href="urunler.php" class="back-btn" title="Ürünlere Dön">
        <i class="fas fa-arrow-left"></i>
      </a>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Yükleniyor...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="loading-state">
      <i class="fas fa-exclamation-triangle fa-3x" style="color: var(--danger); margin-bottom: 16px;"></i>
      <p>{{ error }}</p>
    </div>

    <!-- Main Content -->
    <div v-else-if="productData">
      <!-- Status Dashboard -->
      <div class="status-grid" v-if="productData.durum_ozeti">
        <!-- Stok Durumu -->
        <div class="status-card" :class="{
                    'danger': productData.durum_ozeti.stok_durumu.durum === 'kritik',
                    'warning': productData.durum_ozeti.stok_durumu.durum === 'uyari',
                    'success': productData.durum_ozeti.stok_durumu.durum === 'yeterli'
                }">
          <div class="status-card-icon">
            <i class="fas fa-boxes"></i>
          </div>
          <div class="status-card-title">Mevcut Stok</div>
          <div class="status-card-value">{{ formatNumber(productData.durum_ozeti.stok_durumu.deger) }} <small>{{
              productData.durum_ozeti.stok_durumu.birim }}</small></div>
          <div class="status-card-subtitle">Kritik: {{ formatNumber(productData.durum_ozeti.stok_durumu.kritik_seviye)
            }}</div>
        </div>

        <!-- Üretilebilir -->
        <div class="status-card" :class="productData.durum_ozeti.uretilebilir.deger > 0 ? 'success' : 'danger'">
          <div class="status-card-icon">
            <i class="fas fa-calculator"></i>
          </div>
          <div class="status-card-title">Üretilebilir</div>
          <div class="status-card-value">{{ formatNumber(productData.durum_ozeti.uretilebilir.deger) }}
            <small>adet</small></div>
          <div class="status-card-subtitle" v-if="productData.durum_ozeti.uretilebilir.sinir_bilesen">
            {{ productData.durum_ozeti.uretilebilir.sinir_bilesen }} sınırlıyor
          </div>
          <div class="status-card-subtitle" v-else-if="productData.durum_ozeti.uretilebilir.deger === 0">Bileşen
            yetersiz</div>
          <div class="status-card-subtitle" v-else>Tüm bileşenler yeterli</div>
        </div>

        <!-- Üretimde -->
        <div class="status-card info" v-if="productData.durum_ozeti.uretimde.deger > 0">
          <div class="status-card-icon">
            <i class="fas fa-industry"></i>
          </div>
          <div class="status-card-title">Üretimde</div>
          <div class="status-card-value">{{ formatNumber(productData.durum_ozeti.uretimde.deger) }} <small>{{
              productData.durum_ozeti.uretimde.birim }}</small></div>
          <div class="status-card-subtitle">{{ productData.durum_ozeti.uretimde.is_emri_sayisi }} aktif iş emri</div>
        </div>

        <!-- Aktif Siparişler -->
        <div class="status-card info" v-if="activeOrders.length > 0">
          <div class="status-card-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="status-card-title">Aktif Siparişler</div>
          <div class="status-card-value">{{ formatNumber(calculateActiveOrdersTotal()) }} <small>{{
              productData.product.birim }}</small></div>
          <div class="status-card-subtitle">{{ activeOrders.length }} aktif sipariş</div>
        </div>
      </div>

      <!-- Action Recommendations -->
      <div class="action-section" v-if="productData.eylem_onerileri && productData.eylem_onerileri.length > 0">
        <h3><i class="fas fa-lightbulb"></i> Önerilen Aksiyonlar</h3>
        <div v-for="(oneri, index) in productData.eylem_onerileri" :key="index" class="action-item"
          :class="oneri.oncelik">
          <div class="action-item-icon">
            <i :class="'fas ' + oneri.ikon"></i>
          </div>
          <div class="action-item-content">
            <h4>{{ oneri.mesaj }}</h4>
            <p>{{ oneri.detay }}</p>
            <small class="text-muted" style="font-size: 0.75rem; display: block; margin-top: 8px;">
              <template v-if="oneri.mesaj.includes('kritik seviyenin altında')">
                Algoritma: Stok_açığı = kritik_seviye - mevcut_stok. Üretilebilir = min(bileşen_stok / gerekli_miktar).
                Neden acil: Sipariş karşılanamayabilir.
              </template>
              <template v-else-if="oneri.mesaj.includes('bileşeni satın alın')">
                Algoritma: Gerekli = stok_açığı × bileşen_miktarı. Eksik: mevcut_stok < gerekli. Neden acil: Üretim
                  durabilir. </template>
                  <template v-else-if="oneri.mesaj.includes('Üretim planını artırın')">
                    Algoritma: Eksik_üretim = stok_açığı - mevcut_üretim. Neden acil: Müşteri siparişleri risk altında.
                  </template>
                  <template v-else-if="oneri.mesaj.includes('Sipariş karşılama riski')">
                    Algoritma: Kullanılabilir = mevcut_stok + üretim. Risk: bekleyen > kullanılabilir. Neden acil:
                    Teslimat gecikecek.
                  </template>
                  <template v-else-if="oneri.mesaj.includes('Ürün hazır durumda')">
                    Algoritma: Stok ≥ kritik, bileşenler yeterli, siparişler karşılanıyor. Neden olumlu: Operasyon
                    normal.
                  </template>
            </small>
          </div>
        </div>
      </div>

      <!-- Active Orders List -->
      <div class="detail-card"
        v-if="productData.orders && productData.orders.data.filter(o => o.siparis_durum !== 'iptal_edildi' && o.siparis_durum !== 'tamamlandi').length > 0"
        style="margin-bottom: 24px;">
        <h4><i class="fas fa-clock"></i> Aktif Siparişler</h4>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th><i class="fas fa-hashtag"></i> Sipariş No</th>
                <th><i class="fas fa-user"></i> Müşteri</th>
                <th><i class="fas fa-sort-numeric-up"></i> Miktar</th>
                <th><i class="fas fa-info-circle"></i> Durum</th>
                <th><i class="fas fa-calendar"></i> Tarih</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="order in productData.orders.data.filter(o => o.siparis_durum !== 'iptal_edildi' && o.siparis_durum !== 'tamamlandi')"
                :key="order.kalem_id">
                <td><strong>#{{ order.siparis_id }}</strong></td>
                <td>{{ order.musteri_adi }}</td>
                <td>{{ order.adet }} {{ productData.product.birim }}</td>
                <td>
                  <span :class="getStatusBadgeClass(order.siparis_durum)">
                    {{ order.siparis_durum }}
                  </span>
                </td>
                <td><span class="text-nowrap">{{ formatDate(order.siparis_tarihi) }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Details Grid -->
      <div class="details-grid">
        <!-- Product Information -->
        <div class="detail-card">
          <h4><i class="fas fa-info-circle"></i> Ürün Bilgileri</h4>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-hashtag"></i> Ürün Kodu</span>
            <span class="info-value">{{ productData.product.urun_kodu }}</span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-tag"></i> Ürün Adı</span>
            <span class="info-value">{{ productData.product.urun_ismi }}</span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-balance-scale"></i> Birim</span>
            <span class="info-value">{{ productData.product.birim }}</span>
          </div>
          <div class="info-row" v-if="productData.product.satis_fiyati">
            <span class="info-label"><i class="fas fa-dollar-sign"></i> Satış Fiyatı</span>
            <span class="info-value">{{ formatPriceWithCurrency(productData.product) }}</span>
          </div>
          <div class="info-row" v-if="productData.product.teorik_maliyet">
            <span class="info-label"><i class="fas fa-calculator"></i> Teorik Maliyet</span>
            <span class="info-value">{{ formatTeorikMaliyet(productData.product) }}</span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-exclamation-triangle"></i> Kritik Stok</span>
            <span class="info-value">{{ productData.product.kritik_stok_seviyesi }}</span>
          </div>
          <div class="info-row">
            <span class="info-label"><i class="fas fa-boxes"></i> Mevcut Stok</span>
            <span class="info-value">{{ productData.product.stok_miktari }}</span>
          </div>
          <div class="info-row" v-if="productData.product.not_bilgisi">
            <span class="info-label"><i class="fas fa-sticky-note"></i> Not</span>
            <span class="info-value">{{ (productData.product.not_bilgisi && productData.product.not_bilgisi !== 'null')
              ? productData.product.not_bilgisi : 'Not yok' }}</span>
          </div>
        </div>

        <!-- Photos -->
        <div class="detail-card" v-if="productData.photos.length > 0">
          <h4><i class="fas fa-images"></i> Ürün Fotoğrafları ({{ productData.photos.length }})</h4>
          <div class="photo-grid">
            <div v-for="(photo, index) in productData.photos.slice(0, 6)" :key="photo.fotograf_id" class="photo-item">
              <a :href="photo.dosya_yolu" data-fancybox="product-photos" :data-caption="photo.dosya_adi">
                <img :src="photo.dosya_yolu" :alt="photo.dosya_adi" style="cursor: pointer;"
                  title="Fotoğrafı büyütmek için tıklayın">
              </a>
            </div>
          </div>
          <p v-if="productData.photos.length > 6" class="text-center mt-3">
            <small class="text-muted">+{{ productData.photos.length - 6 }} daha...</small>
          </p>
        </div>

        <!-- Orders Summary -->
        <div class="detail-card" v-if="productData.orders">
          <h4><i class="fas fa-shopping-cart"></i> Sipariş Detayları</h4>
          <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div
              style="text-align: center; padding: 12px; background: rgba(14, 165, 233, 0.05); border-radius: 8px; border: 1px solid rgba(14, 165, 233, 0.1);">
              <div style="font-size: 1.5rem; font-weight: 700; color: var(--info);">{{ (productData.orders.summary &&
                productData.orders.summary.toplam_siparis) || 0 }}</div>
              <div style="font-size: 0.8rem; color: var(--text-medium);">Toplam Sipariş</div>
            </div>
            <div
              style="text-align: center; padding: 12px; background: rgba(34, 197, 94, 0.05); border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.1);">
              <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ activeOrders.length || 0 }}
              </div>
              <div style="font-size: 0.8rem; color: var(--text-medium);">Aktif Sipariş</div>
            </div>
            <div
              style="text-align: center; padding: 12px; background: rgba(245, 158, 11, 0.05); border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.1);">
              <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">{{ calculateActiveOrdersTotal()
                || 0 }}</div>
              <div style="font-size: 0.8rem; color: var(--text-medium);">Aktif Miktar</div>
            </div>
            <div
              style="text-align: center; padding: 12px; background: rgba(239, 68, 68, 0.05); border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.1);">
              <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger);">{{ (productData.orders.summary &&
                productData.orders.summary.tamamlanan_siparis) || 0 }}</div>
              <div style="font-size: 0.8rem; color: var(--text-medium);">Tamamlanan</div>
            </div>
          </div>
        </div>

        <!-- BOM Components -->
        <div class="detail-card" v-if="productData.bom_components && productData.bom_components.length > 0"
          style="grid-column: 1 / -1;">
          <h4><i class="fas fa-sitemap"></i> Ürün Ağacı ({{ productData.bom_components.length }} bileşen)</h4>
          <div
            style="margin-bottom: 20px; padding: 16px; background: rgba(34, 197, 94, 0.05); border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <div style="font-size: 1.2rem; font-weight: 600; color: var(--success);">Üretilebilir: {{
                producibleQuantity }} adet</div>
              <div style="font-size: 0.8rem; color: var(--text-medium);"
                v-if="productData.durum_ozeti.uretilebilir.sinir_bilesen">
                Sınırlayan: {{ productData.durum_ozeti.uretilebilir.sinir_bilesen }}
              </div>
            </div>
            <div style="font-size: 0.85rem; color: var(--text-light);">
              <template v-if="productData.durum_ozeti.uretilebilir.sinir_bilesen">
                <strong>Darboğaz Analizi:</strong> Üretim {{ productData.durum_ozeti.uretilebilir.sinir_bilesen }}
                bileşeni tarafından sınırlanıyor (kritik bileşen).<br>
                <strong>Hesaplama:</strong> {{ productData.durum_ozeti.uretilebilir.sinir_bilesen }}: {{
                productData.durum_ozeti.uretilebilir.sinir_bilesen_stok }} stok ÷ {{
                productData.durum_ozeti.uretilebilir.sinir_bilesen_miktari }} gerekli = {{ producibleQuantity }} adet
                üretilebilir.<br>
                <strong>Diğer bileşenler:</strong> Darboğaz dışındaki bileşenler yeterli stokta ({{
                productData.durum_ozeti.uretilebilir.sinir_bilesen }} hariç tüm bileşenler {{ producibleQuantity }}
                adetten fazla üretim kapasitesine sahip).
              </template>
              <template v-else>
                <strong>Üretim Durumu:</strong> Tüm bileşenler yeterli miktarda stokta mevcut. Darboğaz bileşen yok -
                maksimum üretim kapasitesi mevcut.<br>
                <strong>Analiz:</strong> Her bileşen için yeterli stok var, üretim herhangi bir kısıtlama olmadan devam
                edebilir.
              </template>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th><i class="fas fa-tag"></i> Bileşen Adı</th>
                  <th><i class="fas fa-layer-group"></i> Bileşen Türü</th>
                  <th><i class="fas fa-boxes"></i> Bileşim Oranı (Stok / Gerekli)</th>
                  <th><i class="fas fa-info-circle"></i> Tedarik Durumu</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="component in productData.bom_components" :key="component.id">
                  <td><strong>{{ component.bilesen_ismi }}</strong></td>
                  <td><span class="badge badge-info">{{ component.bilesen_turu }}</span></td>
                  <td>{{ component.bilesen_stok }} / {{ component.bilesen_miktari }}</td>
                  <td>
                    <div v-if="component.bilesen_turu === 'esans' && component.planlanan_uretim > 0">
                      <small class="text-warning">
                        <i class="fas fa-clock"></i> Üretimde: {{ parseFloat(component.planlanan_uretim).toFixed(2) }}
                        {{ component.bilesen_birim }}
                      </small>
                    </div>
                    <div
                      v-else-if="component.bilesen_turu !== 'esans' && getContractForComponent(component.bilesen_kodu)">
                      <small class="text-success">
                        <i class="fas fa-file-contract"></i> {{
                        getContractForComponent(component.bilesen_kodu).sozlesme_id }} - {{
                        getContractForComponent(component.bilesen_kodu).tedarikci_adi }}
                      </small>
                      <div style="margin-top: 1px;">
                        <small class="text-muted">Kalan: {{ getContractForComponent(component.bilesen_kodu).kalan_miktar
                          }} {{ component.bilesen_birim }}</small>
                      </div>
                    </div>
                    <div v-else-if="component.bilesen_turu !== 'esans'">
                      <small class="text-muted">
                        <i class="fas fa-exclamation-triangle"></i> Sözleşme yok
                      </small>
                    </div>
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

  <script>window.urunKodu = <?php echo $urun_kodu; ?>;</script>
  <script src="assets/js/urun_karti.js"></script>
</body>

</html>
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
  /* Teal & Cyan Color Palette */
  --primary: #0891b2;
  --primary-light: #06b6d4;
  --primary-dark: #0e7490;
  --secondary: #14b8a6;
  --accent: #f59e0b;
  --success: #22c55e;
  --danger: #ef4444;
  --warning: #f59e0b;
  --info: #0ea5e9;

  /* Neutral Palette */
  --bg-main: #fafbfc;
  --bg-secondary: #f3f4f6;
  --card-bg: #ffffff;
  --border-light: #e5e7eb;
  --border-medium: #d1d5db;

  /* Text Colors */
  --text-dark: #111827;
  --text-medium: #374151;
  --text-light: #6b7280;
  --text-lighter: #9ca3af;

  /* Shadows */
  --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 8px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 12px 24px rgba(0, 0, 0, 0.12);

  /* Transitions */
  --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-base: 250ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);

  /* Spacing */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --spacing-unit: 20px;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html {
  font-size: 15px;
}

body {
  font-family: "Poppins", "Ubuntu", -apple-system, sans-serif;
  background: var(--bg-main);
  color: var(--text-dark);
  min-height: 100vh;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

.main-content {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.page-header h1 {
  font-size: 1.75rem;
  font-weight: 600;
  margin: 0;
  color: var(--text-dark);
  display: flex;
  align-items: center;
  gap: 12px;
  letter-spacing: -0.025em;
}

.page-header h1 i {
  color: var(--primary);
}


.back-btn {
  background: white;
  color: var(--text-medium);
  width: 42px;
  height: 42px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: var(--transition-base);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
}

.back-btn:hover {
  background: var(--primary);
  color: white;
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary);
}

.card {
  background: var(--card-bg);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
  margin-bottom: var(--spacing-unit);
  overflow: hidden;
  transition: var(--transition-base);
}

.card:hover {
  box-shadow: var(--shadow-md);
  border-color: var(--border-medium);
}

.card-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-light);
  background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
}

.card-header h2 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
  color: var(--text-dark);
  display: flex;
  align-items: center;
  gap: 10px;
  letter-spacing: -0.01em;
}

.card-header h2 i {
  color: var(--primary);
  font-size: 1.1rem;
}

.card-body {
  padding: 20px;
}

.stat-card {
  background: linear-gradient(
    135deg,
    var(--primary) 0%,
    var(--primary-light) 100%
  );
  color: white;
  border-radius: var(--radius-md);
  padding: 20px;
  text-align: center;
  height: 100%;
  box-shadow: var(--shadow-sm);
  transition: var(--transition-base);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: none;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-lg);
}

.stat-card h3 {
  font-size: 2rem;
  margin: 8px 0;
  font-weight: 600;
  letter-spacing: -0.02em;
}

.stat-card p {
  margin: 0;
  opacity: 0.95;
  font-size: 0.85rem;
  font-weight: 400;
}

.stat-card i {
  font-size: 2rem;
  opacity: 0.9;
  margin-bottom: 8px;
}

.table {
  margin-bottom: 0;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem;
  width: 100%;
}

.table th {
  border-top: none;
  border-bottom: 2px solid var(--border-light);
  font-weight: 600;
  color: var(--text-medium);
  background: var(--bg-secondary);
  padding: 12px 14px;
  font-size: 0.85rem;
  text-align: left;
}

.table td {
  vertical-align: middle;
  color: var(--text-light);
  padding: 12px 14px;
  border-top: 1px solid var(--border-light);
}

.table tr:first-child td {
  border-top: none;
}

.table tbody tr {
  transition: var(--transition-fast);
}

.table tbody tr:hover {
  background: #fafbfc;
}

.badge-in {
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  color: white;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  box-shadow: var(--shadow-xs);
}

.badge-out {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  box-shadow: var(--shadow-xs);
}

.empty-state {
  text-align: center;
  padding: 48px 24px;
  color: var(--text-lighter);
  background: var(--bg-secondary);
  border-radius: var(--radius-md);
  margin: 16px 0;
  border: 2px dashed var(--border-medium);
}

.empty-state i {
  font-size: 3rem;
  opacity: 0.4;
  margin-bottom: 12px;
  color: var(--text-lighter);
}

.empty-state p {
  font-size: 0.95rem;
  margin: 0;
  font-weight: 400;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 10px 0;
  border-bottom: 1px solid var(--border-light);
}

.info-item:last-child {
  border-bottom: none;
}

.info-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 16px;
  flex-shrink: 0;
  box-shadow: var(--shadow-sm);
}

.info-value {
  font-weight: 500;
  color: var(--text-dark);
  font-size: 0.95rem;
}

.stat-summary-card {
  border-radius: var(--radius-md);
  padding: 16px 18px;
  color: white;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: var(--transition-base);
  height: 100%;
  box-shadow: var(--shadow-sm);
  border: none;
}

.stat-summary-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.stat-summary-card i {
  font-size: 1.6rem;
  flex-shrink: 0;
}

.stat-summary-card h4 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  letter-spacing: -0.02em;
}

.stat-summary-card small {
  opacity: 0.9;
  font-size: 0.75rem;
  font-weight: 400;
  margin: 0;
}

.alert {
  border-radius: var(--radius-md);
  border: 1px solid;
  padding: 14px 16px;
  font-size: 0.9rem;
  margin-bottom: 16px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.alert i {
  margin-top: 2px;
}

.alert-warning {
  background-color: #fffbeb;
  border-color: #fde68a;
  color: #92400e;
}

.alert-success {
  background-color: #f0fdf4;
  border-color: #bbf7d0;
  color: #166534;
}

.alert-info {
  background-color: #eff6ff;
  border-color: #bfdbfe;
  color: #1e40af;
}

.alert-danger {
  background-color: #fef2f2;
  border-color: #fecaca;
  color: #991b1b;
}

.badge {
  border-radius: 20px;
  padding: 5px 10px;
  font-size: 0.75rem;
  font-weight: 500;
  display: inline-block;
}

.badge-success {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  box-shadow: var(--shadow-xs);
}

.badge-warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  box-shadow: var(--shadow-xs);
}

.badge-info {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  color: white;
  box-shadow: var(--shadow-xs);
}

.badge-secondary {
  background: linear-gradient(135deg, #6b7280, #4b5563);
  color: white;
  box-shadow: var(--shadow-xs);
}

.badge-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  box-shadow: var(--shadow-xs);
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--bg-secondary);
}

::-webkit-scrollbar-thumb {
  background: var(--border-medium);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--text-lighter);
}

[v-cloak] {
  display: none !important;
}

.photo-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
}

.photo-item {
  position: relative;
  border-radius: var(--radius-md);
  overflow: hidden;
  cursor: pointer;
  transition: var(--transition-base);
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-xs);
  background: white;
}

.photo-item:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary-light);
}

.photo-item img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  transition: var(--transition-slow);
}

.photo-item:hover img {
  transform: scale(1.08);
}

.primary-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
  padding: 5px 10px;
  border-radius: var(--radius-sm);
  font-size: 0.7rem;
  font-weight: 600;
  z-index: 10;
  box-shadow: var(--shadow-md);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* SORUNLU BÖLÜMLER İÇİN YENİ, BASİT STİLLER */
#app .durum-ozeti-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    padding-top: 1rem;
}
#app .durum-kart {
    background-color: #ffffff;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    transition: all 0.2s ease-in-out;
}
#app .durum-kart:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
#app .durum-kart-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #6c757d;
    order: -1;
}
#app .durum-kart-ikon {
    font-size: 1.8rem;
    opacity: 0.8;
    align-self: flex-start;
}
#app .durum-kart-ana-deger {
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1.2;
    color: #343a40;
}
#app .durum-kart-alt-bilgi {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: auto;
}
#app .durum-kart-progress {
    background-color: #e9ecef;
    border-radius: 99px;
    height: 6px;
    width: 100%;
    overflow: hidden;
    margin-top: 0.5rem;
}
#app .durum-kart-progress .progress-bar {
    height: 100%;
    border-radius: 99px;
}

#app .durum-kart.danger .durum-kart-ikon, #app .durum-kart.danger .durum-kart-ana-deger { color: #dc3545; }
#app .durum-kart.danger .progress-bar { background-color: #dc3545; }

#app .durum-kart.warning .durum-kart-ikon, #app .durum-kart.warning .durum-kart-ana-deger { color: #ffc107; }
#app .durum-kart.warning .progress-bar { background-color: #ffc107; }

#app .durum-kart.success .durum-kart-ikon, #app .durum-kart.success .durum-kart-ana-deger { color: #28a745; }
#app .durum-kart.success .progress-bar { background-color: #28a745; }

#app .durum-kart.info .durum-kart-ikon, #app .durum-kart.info .durum-kart-ana-deger { color: #17a2b8; }
#app .durum-kart.info .progress-bar { background-color: #17a2b8; }

#app .eylem-onerileri-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
#app .eylem-kart {
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    padding: 1.25rem;
    border-radius: 12px;
    border: 1px solid #dee2e6;
    background-color: #ffffff;
}
#app .eylem-kart.critical { border-left: 5px solid #721c24; background-color: #f8d7da; }
#app .eylem-kart.warning { border-left: 5px solid #856404; background-color: #fff3cd; }
#app .eylem-kart.info { border-left: 5px solid #0c5460; background-color: #d1ecf1; }

#app .eylem-kart-ikon {
    font-size: 1.75rem;
    width: 40px;
    padding-top: 5px;
}
#app .eylem-kart.critical .eylem-kart-ikon { color: #721c24; }
#app .eylem-kart.warning .eylem-kart-ikon { color: #856404; }
#app .eylem-kart.info .eylem-kart-ikon { color: #0c5460; }

#app .eylem-kart-mesaj {
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}
#app .eylem-kart-detay {
    font-size: 0.9rem;
    color: #495057;
    margin: 0;
}
#app .eylem-kart-oncelik-badge{
    text-transform: uppercase;
}


@media (max-width: 768px) {
  .main-content {
    padding: 16px;
  }
  .page-header h1 {
    font-size: 1.4rem;
  }
  .photo-gallery {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
  }
  .photo-item img {
    height: 140px;
  }
  .stat-card h3 {
    font-size: 1.6rem;
  }
  .stat-card i {
    font-size: 1.6rem;
  }
  .info-item {
    flex-direction: row;
    gap: 10px;
  }
  .info-icon {
    width: 36px;
    height: 36px;
    font-size: 14px;
  }
  .stat-summary-card {
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    gap: 8px;
    padding: 14px;
  }
  .stat-summary-card i {
    font-size: 1.4rem;
  }
  .page-header {
    flex-direction: row;
    align-items: center;
    gap: 12px;
  }
  .card-body {
    padding: 16px;
  }
}

@media (max-width: 576px) {
  .photo-gallery {
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  .stat-summary-card {
    padding: 12px;
  }
  .info-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
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
            <!-- Durum Özeti Dashboard -->
            <div class="card" v-if="productData.durum_ozeti">
                <div class="card-header">
                    <h2><i class="fas fa-chart-pie"></i> Ürün Durum Özeti</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Ürünün mevcut durumu hakkında hızlı bilgiler ve kritik metrikler.
                    </p>
                    <div class="durum-ozeti-container">
                        <!-- Stok Durumu -->
                        <div class="durum-kart" :class="{
                            'danger': productData.durum_ozeti.stok_durumu.durum === 'kritik',
                            'warning': productData.durum_ozeti.stok_durumu.durum === 'uyari',
                            'success': productData.durum_ozeti.stok_durumu.durum === 'yeterli',
                            'info': productData.durum_ozeti.stok_durumu.durum === 'bilgi'
                        }">
                            <div class="durum-kart-ikon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="durum-kart-label">Mevcut Stok</div>
                            <div class="durum-kart-ana-deger">
                                {{ formatNumber(productData.durum_ozeti.stok_durumu.deger) }}
                                <span class="durum-kart-birim-inline">{{ productData.durum_ozeti.stok_durumu.birim }}</span>
                            </div>
                            <div class="durum-kart-yuzde">{{ productData.durum_ozeti.stok_durumu.yuzde }}% Dolu</div>
                            <div class="durum-kart-progress">
                                <div class="progress-bar" :style="{ width: Math.min(productData.durum_ozeti.stok_durumu.yuzde, 100) + '%' }"></div>
                            </div>
                            <div class="durum-kart-alt-bilgi">
                                Kritik Seviye: {{ formatNumber(productData.durum_ozeti.stok_durumu.kritik_seviye) }}
                            </div>
                            <small class="text-muted" style="font-size: 0.65rem;">Hesaplama: Mevcut stok / kritik seviye × 100</small>
                        </div>

                        <!-- Stok Açığı -->
                        <div class="durum-kart danger" v-if="productData.durum_ozeti.stok_acigi.deger > 0">
                            <div class="durum-kart-ikon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="durum-kart-label">Stok Açığı</div>
                            <div class="durum-kart-ana-deger">
                                {{ formatNumber(productData.durum_ozeti.stok_acigi.deger) }}
                                <span class="durum-kart-birim-inline">{{ productData.durum_ozeti.stok_acigi.birim }}</span>
                            </div>
                             <div class="durum-kart-yuzde">{{ productData.durum_ozeti.stok_acigi.yuzde_eksik }}% Eksik</div>
                            <div class="durum-kart-alt-bilgi">
                                Kritik: {{ formatNumber(productData.durum_ozeti.stok_acigi.kritik_seviye) }}
                            </div>
                            <small class="text-muted" style="font-size: 0.65rem;">Hesaplama: Kritik seviye - mevcut stok</small>
                        </div>

                        <!-- Üretimdeki -->
                        <div class="durum-kart info" v-if="productData.durum_ozeti.uretimde.deger > 0">
                            <div class="durum-kart-ikon">
                                <i class="fas fa-industry"></i>
                            </div>
                            <div class="durum-kart-label">Üretimdeki</div>
                            <div class="durum-kart-ana-deger">
                                {{ formatNumber(productData.durum_ozeti.uretimde.deger) }}
                                <span class="durum-kart-birim-inline">{{ productData.durum_ozeti.uretimde.birim }}</span>
                            </div>
                            <div class="durum-kart-yuzde">{{ productData.durum_ozeti.uretimde.is_emri_sayisi }} İş Emri</div>
                            <div class="durum-kart-alt-bilgi">
                                <i v-if="productData.durum_ozeti.uretimde.acik_kapatir" class="fas fa-check-circle mr-1"></i>
                                {{ productData.durum_ozeti.uretimde.durum_text }}
                            </div>
                            <small class="text-muted" style="font-size: 0.65rem;">Hesaplama: Aktif iş emirlerinin toplamı</small>
                        </div>

                        <!-- Üretilebilir -->
                        <div class="durum-kart success">
                             <div class="durum-kart-ikon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="durum-kart-label">Üretilebilir</div>
                            <div class="durum-kart-ana-deger">
                                {{ formatNumber(productData.durum_ozeti.uretilebilir.deger) }}
                                <span class="durum-kart-birim-inline">{{ productData.durum_ozeti.uretilebilir.birim }}</span>
                            </div>
                            <div class="durum-kart-yuzde" v-if="productData.durum_ozeti.stok_acigi.deger > 0 && productData.durum_ozeti.uretilebilir.acik_kapatma_orani > 0">
                                Açığın %{{ productData.durum_ozeti.uretilebilir.acik_kapatma_orani }}'ı
                            </div>
                            <div class="durum-kart-alt-bilgi" v-if="productData.durum_ozeti.uretilebilir.sinir_bilesen">
                                {{ productData.durum_ozeti.uretilebilir.sinir_bilesen }} sınırlıyor
                            </div>
                            <div class="durum-kart-alt-bilgi" v-else>Tüm bileşenler yeterli</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Hesaplama: En az stoklu bileşene göre</small>
                        </div>

                        <!-- Bekleyen Siparişler -->
                        <div class="durum-kart warning" v-if="productData.durum_ozeti.bekleyen_siparisler.deger > 0">
                            <div class="durum-kart-ikon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="durum-kart-label">Bekleyen Sipariş</div>
                            <div class="durum-kart-ana-deger">
                                {{ formatNumber(productData.durum_ozeti.bekleyen_siparisler.deger) }}
                                <span class="durum-kart-birim-inline">{{ productData.durum_ozeti.bekleyen_siparisler.birim }}</span>
                            </div>
                            <div class="durum-kart-yuzde">{{ productData.durum_ozeti.bekleyen_siparisler.siparis_sayisi }} Aktif Sipariş</div>
                            <div class="durum-kart-alt-bilgi">
                                <i v-if="productData.durum_ozeti.bekleyen_siparisler.karsilanabilir" class="fas fa-check-circle mr-1"></i>
                                <i v-else class="fas fa-exclamation-triangle mr-1"></i>
                                {{ productData.durum_ozeti.bekleyen_siparisler.durum_text }}
                            </div>
                            <small class="text-muted" style="font-size: 0.65rem;">Hesaplama: Aktif siparişlerin toplamı</small>
                        </div>


                    </div>
                </div>
            </div>

            <!-- Eylem Önerileri -->
            <div class="card" v-if="productData.eylem_onerileri && productData.eylem_onerileri.length > 0">
                <div class="card-header">
                    <h2><i class="fas fa-lightbulb"></i> Eylem Önerileri</h2>
                </div>
                <div class="card-body">
                     <p class="text-muted mb-4" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Mevcut duruma göre önerilen aksiyonlar ve öncelikler.
                    </p>
                    <div class="eylem-onerileri-container">
                        <div v-for="(oneri, index) in productData.eylem_onerileri" :key="index" class="eylem-kart" :class="{
                            'critical': oneri.oncelik === 'critical',
                            'warning': oneri.oncelik === 'warning',
                            'info': oneri.oncelik === 'info'
                        }">
                            <div class="eylem-kart-ikon">
                                <i :class="'fas ' + oneri.ikon"></i>
                            </div>
                            <div class="eylem-kart-icerik">
                                <div class="eylem-kart-mesaj">{{ oneri.mesaj }}</div>
                                <p class="eylem-kart-detay">{{ oneri.detay }}</p>
                                <small class="text-muted" style="font-size: 0.65rem;">
                                    <template v-if="oneri.mesaj.includes('kritik seviyenin altında')">
                                        Algoritma: 1) Stok açığı = kritik seviye - mevcut stok. 2) Üretilebilir miktar = min(bileşen_stok / bileşen_gerekli_miktar). 3) Eğer üretilebilir ≥ stok_açığı ise üretim öner, değilse acil aksiyon (tedarikçi riski). Neden acil: Stok kritik seviyenin altında olduğu için sipariş karşılanamayabilir, müşteri kaybı ve itibar zedelenmesi riski var. Mevcut: {{ productData.durum_ozeti.stok_acigi.deger }} {{ productData.product.birim }} açık, {{ productData.durum_ozeti.uretilebilir.deger }} adet üretilebilir.
                                    </template>
                                    <template v-else-if="oneri.mesaj.includes('bileşeni satın alın')">
                                        Algoritma: 1) Kritik stok için gereken = stok_açığı × bileşen_miktarı. 2) Her bileşen için eğer mevcut_stok < gereken_miktar ise eksik. 3) Eksik bileşen sayısı ve önem sırasına göre öncelik belirle. Neden acil: Üretim durabilir, teslimat gecikebilir, sözleşme cezaları uygulanabilir. Stok açığı nedeniyle üretim kapasitesi sınırlı.
                                    </template>
                                    <template v-else-if="oneri.mesaj.includes('Üretim planını artırın')">
                                        Algoritma: 1) Mevcut üretim kapasitesi = aktif_iş_emirleri_toplam. 2) Eksik kapasite = stok_açığı - mevcut_üretim. 3) Eğer eksik > 0 ise üretim artır. Neden acil: Mevcut üretim stok açığını kapatmaya yetmiyor, müşteri siparişleri risk altında. {{ productData.durum_ozeti.stok_acigi.deger - productData.durum_ozeti.uretimde.deger }} {{ productData.product.birim }} daha üretilmeli.
                                    </template>
                                    <template v-else-if="oneri.mesaj.includes('Sipariş karşılama riski')">
                                        Algoritma: 1) Kullanılabilir toplam = mevcut_stok + üretimdeki_miktar. 2) Eğer bekleyen_siparişler > kullanılabilir_toplam ise risk var. 3) Öncelik sırası: üretim artır → bileşen satın al → müşteri bilgilendir. Neden acil: Sipariş teslimatı gecikecek, müşteri memnuniyeti düşecek, tekrar sipariş alma ihtimali azalacak. {{ productData.durum_ozeti.bekleyen_siparisler.deger }} {{ productData.product.birim }} bekliyor, {{ productData.durum_ozeti.stok_durumu.deger + productData.durum_ozeti.uretimde.deger }} {{ productData.product.birim }} kullanılabilir.
                                    </template>
                                    <template v-else-if="oneri.mesaj.includes('Ürün hazır durumda')">
                                        Algoritma: 1) Stok kontrolü: mevcut ≥ kritik_seviye. 2) Bileşen kontrolü: tüm bileşenler yeterli. 3) Sipariş kontrolü: bekleyen_siparişler ≤ kullanılabilir_toplam. 4) Hepsi true ise hazır durum. Neden olumlu: Üretim devam edebilir, siparişler zamanında teslim edilebilir, operasyonel risk minimum.
                                    </template>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

    <script>  window.urunKodu = <?php echo $urun_kodu; ?>;
    </script>
    <script src="assets/js/urun_karti.js"></script>
</body>

</html>

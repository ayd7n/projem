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
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 16px;
            --card-padding: 20px;
        }

        html {
            font-size: 14px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .main-content {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
            border: none;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
            color: white;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.8);
            margin-bottom: 18px;
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .card-header {
            padding: 14px var(--card-padding);
            border-bottom: none;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }

        .card-header h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: var(--card-padding);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 14px;
            padding: 18px;
            text-align: center;
            height: 100%;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.2);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 12px 28px rgba(99, 102, 241, 0.3);
        }

        .stat-card:hover::before {
            top: -30%;
            right: -30%;
        }

        .stat-card h3 {
            font-size: 1.8rem;
            margin: 8px 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .stat-card p {
            margin: 0;
            opacity: 0.95;
            font-size: 0.85rem;
            position: relative;
            z-index: 1;
        }

        .stat-card i {
            font-size: 2rem;
            opacity: 0.95;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .stat-content {
            text-align: center;
            margin-top: 8px;
        }

        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .photo-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid var(--border-color);
            box-shadow: var(--shadow);
            background: white;
        }

        .photo-item:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .photo-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            transition: var(--transition);
        }

        .photo-item:hover img {
            transform: scale(1.08);
        }

        .primary-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, var(--warning), var(--accent));
            color: white;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
        }

        .table th {
            border-top: none;
            border-bottom: 2px solid var(--border-color);
            font-weight: 700;
            color: var(--text-primary);
            background: linear-gradient(to bottom, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
            padding: 10px 12px;
            font-size: 0.85rem;
        }

        .table td {
            vertical-align: middle;
            color: var(--text-secondary);
            padding: 10px 12px;
            border-top: 1px solid var(--border-color);
        }

        .table tr:first-child td {
            border-top: none;
        }

        .table tr:hover {
            background: linear-gradient(to right, rgba(99, 102, 241, 0.03), rgba(139, 92, 246, 0.03));
        }

        .table-header {
            background: linear-gradient(to bottom, rgba(99, 102, 241, 0.08), rgba(139, 92, 246, 0.08)) !important;
        }

        .table-row {
            transition: var(--transition);
        }

        .badge-in {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        }

        .badge-out {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: var(--text-secondary);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.03), rgba(139, 92, 246, 0.03));
            border-radius: 12px;
            margin: 15px 0;
            border: 2px dashed var(--border-color);
        }

        .empty-state i {
            font-size: 2.8rem;
            opacity: 0.3;
            margin-bottom: 12px;
            color: var(--text-secondary);
        }

        .empty-state p {
            font-size: 1rem;
            margin: 0;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 8px 0;
        }

        .info-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .info-value {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .stat-summary-card {
            border-radius: 12px;
            padding: 14px 16px;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            height: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
        }

        .stat-summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            transform: translate(30%, -30%);
        }

        .stat-summary-card:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .stat-summary-card i {
            font-size: 1.6rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .stat-summary-card h4,
        .stat-summary-card small {
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .stat-summary-card h4 {
            font-size: 1.4rem;
        }

        .stat-summary-card small {
            opacity: 0.9;
            font-size: 0.75rem;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 12px 16px;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(251, 191, 36, 0.15));
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(52, 211, 153, 0.15));
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(96, 165, 250, 0.15));
            color: #1e3a8a;
            border-left: 4px solid #3b82f6;
        }

        .badge {
            border-radius: 8px;
            padding: 5px 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-success {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.2);
        }

        .badge-warning {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: white;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.2);
        }

        .badge-info {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.2);
        }

        .badge-secondary {
            background: linear-gradient(135deg, #64748b, #94a3b8);
            color: white;
            box-shadow: 0 2px 6px rgba(100, 116, 139, 0.2);
        }

        .badge-danger {
            background: linear-gradient(135deg, #ef4444, #f87171);
            color: white;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.2);
        }


        @media (max-width: 768px) {
            .photo-gallery {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 12px;
            }

            .photo-item img {
                height: 140px;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .stat-card i {
                font-size: 1.8rem;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .info-icon {
                width: 32px;
                height: 32px;
                font-size: 13px;
            }

            .stat-summary-card {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
                gap: 8px;
                padding: 12px;
            }

            .stat-summary-card i {
                font-size: 1.4rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .main-content {
                padding: 12px;
            }

            .card-body {
                padding: 16px;
            }
        }

        @media (max-width: 576px) {
            .photo-gallery {
                grid-template-columns: 1fr 1fr;
            }

            .stat-summary-card {
                padding: 12px;
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
    <div id="app" class="main-content">
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
            <!-- Ürün Bilgileri -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Ürün Bilgileri</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-barcode info-icon"
                                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Ürün Kodu</small>
                                    <p class="mb-0 info-value">{{ productData.product.urun_kodu }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-tag info-icon"
                                    style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Ürün İsmi</small>
                                    <p class="mb-0 info-value">{{ productData.product.urun_ismi }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-ruler info-icon"
                                    style="background: linear-gradient(135deg, #3b82f6, #60a5fa);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Birim</small>
                                    <p class="mb-0 info-value">{{ productData.product.birim }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-money-bill-wave info-icon"
                                    style="background: linear-gradient(135deg, #10b981, #34d399);"></i>
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
                                    style="background: linear-gradient(135deg, #f59e0b, #fbbf24);"></i>
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
                                    style="background: linear-gradient(135deg, #3b82f6, #60a5fa);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Depo</small>
                                    <p class="mb-0 info-value">{{ productData.product.depo }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-boxes info-icon"
                                    style="background: linear-gradient(135deg, #10b981, #34d399);"></i>
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
                                    style="background: linear-gradient(135deg, #f59e0b, #fbbf24);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Raf</small>
                                    <p class="mb-0 info-value">{{ productData.product.raf }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6 mb-3">
                            <div class="info-item">
                                <i class="fas fa-exclamation-triangle info-icon"
                                    style="background: linear-gradient(135deg, #ef4444, #f87171);"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Kritik Stok</small>
                                    <p class="mb-0 info-value">{{ productData.product.kritik_stok_seviyesi }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3" v-if="productData.product.not_bilgisi">
                            <div class="info-item">
                                <i class="fas fa-sticky-note info-icon"
                                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6);"></i>
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
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert" :class="stockGap.hasGap ? 'alert-warning' : 'alert-success'">
                                <i class="fas fa-info-circle"></i>
                                <strong>Kritik Stok Seviyesi:</strong> {{ productData.product.kritik_stok_seviyesi }} {{
                                productData.product.birim }} |
                                <strong>Mevcut Stok:</strong> {{ productData.product.stok_miktari }} {{
                                productData.product.birim }}
                                <span v-if="stockGap.hasGap">
                                    | <strong>Stok Açığı:</strong> {{ stockGap.gap }} {{ productData.product.birim }}
                                    ({{ stockGap.gapPercentage }}%)
                                </span>
                                <span v-else>
                                    | <strong>Stok yeterli!</strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6" v-if="stockGap.hasGap">
                            <div class="stat-card" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                <i class="fas fa-calculator"></i>
                                <div class="stat-content">
                                    <h3>{{ stockGap.producibleForGap }}</h3>
                                    <p>Üretilebilir Adet</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" v-if="stockGap.hasGap && !stockGap.canCoverGap">
                            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #f87171);">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div class="stat-content">
                                    <h3>{{ stockGap.gap - stockGap.producibleForGap }}</h3>
                                    <p>Ek Eksiklik</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" v-else-if="!stockGap.hasGap">
                            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #34d399);">
                                <i class="fas fa-check-circle"></i>
                                <div class="stat-content">
                                    <h3>{{ productData.product.stok_miktari }}</h3>
                                    <p>Yeterli Stok</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3" v-if="stockGap.hasGap && stockGap.gapDetails.length > 0">
                        <h4><i class="fas fa-exclamation-triangle text-warning"></i> Eksik Bileşenler</h4>
                        <p>Stok seviyesini kritik seviyeye çıkarmak için aşağıdaki bileşenlerden talep/giderilmelidir:
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-layer-group"></i> Bileşen</th>
                                        <th><i class="fas fa-barcode"></i> Kod</th>
                                        <th><i class="fas fa-sort-numeric-up"></i> Gerekli</th>
                                        <th><i class="fas fa-warehouse"></i> Mevcut</th>
                                        <th><i class="fas fa-minus-circle"></i> Eksik</th>
                                        <th><i class="fas fa-ruler"></i> Birim</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="detail in stockGap.gapDetails" :key="detail.code">
                                        <td>
                                            <span class="badge badge-info">{{ detail.type }}</span>
                                            {{ detail.name }}
                                        </td>
                                        <td>{{ detail.code }}</td>
                                        <td>{{ Math.ceil(detail.needed) }} {{ detail.unit }}</td>
                                        <td>{{ detail.available }} {{ detail.unit }}</td>
                                        <td class="text-danger font-weight-bold">{{ Math.ceil(detail.shortfall) }} {{
                                            detail.unit }}</td>
                                        <td>{{ detail.unit }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3" v-else-if="stockGap.hasGap && stockGap.canCoverGap">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <strong>Harika!</strong>
                            Mevcut bileşenlerle stok açığını kapatabilirsiniz. {{ stockGap.gap }} adet ürün üretip
                            kritik seviyeye ulaşabilirsiniz.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ürün Fotoğrafları -->
            <div class="card" v-if="productData.photos.length > 0">
                <div class="card-header">
                    <h2><i class="fas fa-images"></i> Ürün Fotoğrafları ({{ productData.photos.length }})</h2>
                </div>
                <div class="card-body">
                    <div class="photo-gallery">
                        <div v-for="photo in productData.photos" :key="photo.fotograf_id" class="photo-item">
                            <div v-if="photo.ana_fotograf == 1" class="primary-badge">
                                <i class="fas fa-star"></i> ANA FOTOĞRAF
                            </div>
                            <img :src="photo.dosya_yolu" :alt="photo.dosya_adi">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stok Hareketleri -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-exchange-alt"></i> Son Stok Hareketleri ({{ recentMovements.length }})</h2>
                </div>
                <div class="card-body">
                    <div v-if="recentMovements.length === 0" class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Henüz stok hareketi bulunmuyor.</p>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr class="table-header">
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
                                <tr v-for="movement in recentMovements" :key="movement.hareket_id" class="table-row">
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
            <div class="card" v-if="productData.bom_components.length > 0">
                <div class="card-header">
                    <h2><i class="fas fa-sitemap"></i> Ürün Ağacı Bileşenleri ({{ productData.bom_components.length }})
                    </h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i> <strong>Bu ürün ağacına göre mevcut stoklarla
                            üretilebilir:</strong>
                        <span class="font-weight-bold ml-2">{{ producibleQuantity }} adet</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr class="table-header">
                                    <th><i class="fas fa-layer-group"></i> Bileşen Türü</th>
                                    <th><i class="fas fa-barcode"></i> Kod</th>
                                    <th><i class="fas fa-tag"></i> İsim</th>
                                    <th><i class="fas fa-sort-numeric-up"></i> Gerekli Miktar</th>
                                    <th><i class="fas fa-warehouse"></i> Mevcut Stok</th>
                                    <th><i class="fas fa-calculator"></i> Üretilebilir</th>
                                    <th v-if="contractsLoaded"><i class="fas fa-file-contract"></i> Çerçeve Sözleşmesi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="component in productData.bom_components" :key="component.id"
                                    class="table-row">
                                    <td>
                                        <span class="badge badge-info">{{ component.bilesen_turu }}</span>
                                    </td>
                                    <td>{{ component.bilesen_kodu }}</td>
                                    <td><strong>{{ component.bilesen_ismi }}</strong></td>
                                    <td>{{ component.bilesen_miktari }} {{ component.bilesen_birim }}</td>
                                    <td>{{ component.bilesen_stok }} {{ component.bilesen_birim }}</td>
                                    <td>
                                        <span class="font-weight-bold" :class="{
                                                  'text-success': canProduceEnough(component),
                                                  'text-danger': !canProduceEnough(component)
                                              }">
                                            {{ component.bilesen_miktari > 0 ? Math.floor(component.bilesen_stok /
                                            component.bilesen_miktari) : 0 }} adet
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
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-shopping-cart"></i> Sipariş Bilgileri</h2>
                </div>
                <div class="card-body">
                    <!-- Sipariş Özeti -->
                    <div class="row mb-3">
                        <div class="col-lg-3 col-md-6 col-6 mb-2">
                            <div class="stat-summary-card"
                                style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                                <i class="fas fa-shopping-bag"></i>
                                <div>
                                    <h4 class="mb-0">{{ activeOrders.length || 0 }}</h4>
                                    <small class="text-light">Aktif Sipariş</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6 mb-2">
                            <div class="stat-summary-card"
                                style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                                <i class="fas fa-sort-numeric-up"></i>
                                <div>
                                    <h4 class="mb-0">{{ calculateActiveOrdersTotal() || 0 }}</h4>
                                    <small class="text-light">Aktif Miktar</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6 mb-2">
                            <div class="stat-summary-card"
                                style="background: linear-gradient(135deg, #10b981, #34d399);">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h4 class="mb-0">{{ productData.orders.summary.tamamlanan_siparis || 0 }}</h4>
                                    <small class="text-light">Tamamlanan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6 mb-2">
                            <div class="stat-summary-card"
                                style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                                <i class="fas fa-tools"></i>
                                <div>
                                    <h4 class="mb-0">{{ productData.orders.summary.hazirlanan_siparis || 0 }}</h4>
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
                                <tr class="table-header">
                                    <th><i class="fas fa-hashtag"></i> Sipariş No</th>
                                    <th><i class="fas fa-user"></i> Müşteri</th>
                                    <th><i class="fas fa-calendar"></i> Tarih</th>
                                    <th><i class="fas fa-sort-numeric-up"></i> Miktar</th>
                                    <th><i class="fas fa-info-circle"></i> Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in activeOrders" :key="order.kalem_id" class="table-row">
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

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    productData: null,
                    loading: true,
                    error: null,
                    urunKodu: <?php echo $urun_kodu; ?>,
                    frameContracts: [], // Store available frame contracts
                    contractsLoaded: false // Track if contracts have been loaded
                }
            },
            computed: {
                activeOrders() {
                    if (!this.productData || !this.productData.orders || !this.productData.orders.data) {
                        return [];
                    }
                    // Filter out orders with status "iptal_edildi"
                    return this.productData.orders.data.filter(order => order.siparis_durum !== 'iptal_edildi');
                },
                recentMovements() {
                    if (!this.productData || !this.productData.movements || !this.productData.movements.data) {
                        return [];
                    }
                    // Return only the last 10 movements
                    return this.productData.movements.data.slice(-10);
                },
                producibleQuantity() {
                    if (!this.productData || !this.productData.bom_components || this.productData.bom_components.length === 0) {
                        return 0;
                    }

                    // Calculate maximum producible quantity based on available components
                    let maxProducible = Infinity;

                    for (const component of this.productData.bom_components) {
                        const requiredAmount = parseFloat(component.bilesen_miktari) || 0;
                        const availableAmount = parseFloat(component.bilesen_stok) || 0;

                        if (requiredAmount > 0) {
                            const producibleFromThisComponent = Math.floor(availableAmount / requiredAmount);
                            maxProducible = Math.min(maxProducible, producibleFromThisComponent);
                        }
                    }

                    return maxProducible === Infinity ? 0 : maxProducible;
                },
                stockGap() {
                    if (!this.productData) return null;

                    const currentStock = parseFloat(this.productData.product.stok_miktari) || 0;
                    const criticalStock = parseFloat(this.productData.product.kritik_stok_seviyesi) || 0;
                    const gap = criticalStock - currentStock;

                    if (gap <= 0) {
                        return {
                            hasGap: false,
                            gap: 0,
                            gapPercentage: 0,
                            producibleForGap: 0,
                            canCoverGap: true,
                            gapDetails: []
                        };
                    }

                    // Calculate if we can produce enough to cover the gap
                    const producibleForGap = Math.min(this.producibleQuantity, gap);
                    const canCoverGap = producibleForGap >= gap;

                    // Calculate what's needed to produce the missing amount
                    let gapDetails = [];
                    if (!canCoverGap && this.productData.bom_components) {
                        const missingToProduce = gap - producibleForGap;

                        for (const component of this.productData.bom_components) {
                            const requiredAmount = parseFloat(component.bilesen_miktari) || 0;
                            const neededForGap = missingToProduce * requiredAmount;
                            const availableAmount = parseFloat(component.bilesen_stok) || 0;
                            const shortfall = Math.max(0, neededForGap - availableAmount);

                            if (shortfall > 0) {
                                gapDetails.push({
                                    name: component.bilesen_ismi,
                                    code: component.bilesen_kodu,
                                    type: component.bilesen_turu,
                                    needed: neededForGap,
                                    available: availableAmount,
                                    shortfall: shortfall,
                                    unit: component.bilesen_birim
                                });
                            }
                        }
                    }

                    return {
                        hasGap: true,
                        gap: gap,
                        gapPercentage: currentStock > 0 ? Math.round((gap / currentStock) * 100) : 0,
                        producibleForGap: producibleForGap,
                        canCoverGap: canCoverGap,
                        gapDetails: gapDetails
                    };
                }
            },
            mounted() {
                this.loadProductCard();
            },
            methods: {
                loadProductCard() {
                    this.loading = true;
                    this.error = null;

                    // Create a timeout promise to handle potential network delays
                    const timeoutPromise = new Promise((_, reject) => {
                        setTimeout(() => {
                            reject(new Error('İstek zaman aşımına uğradı. Lütfen daha sonra tekrar deneyin.'));
                        }, 10000); // 10 second timeout
                    });

                    Promise.race([
                        fetch(`api_islemleri/urun_karti_islemler.php?action=get_product_card&urun_kodu=${this.urunKodu}`),
                        timeoutPromise
                    ])
                        .then(response => {
                            if (response.status === 404) {
                                throw new Error('API endpoint bulunamadı. Lütfen geliştirici ile iletişime geçin.');
                            }
                            return response.json();
                        })
                        .then(response => {
                            if (response.status === 'success') {
                                this.productData = response.data;
                                // Load frame contracts after product data is loaded
                                this.loadFrameContracts();
                            } else {
                                this.error = response.message || 'Ürün bilgileri yüklenirken hata oluştu.';
                            }
                            this.loading = false;
                        })
                        .catch(error => {
                            if (error.message.includes('zaman aşımı')) {
                                this.error = error.message;
                            } else if (error.message.includes('endpoint bulunamadı')) {
                                this.error = error.message;
                            } else {
                                this.error = 'Bir ağ hatası oluştu: ' + error.message;
                            }
                            this.loading = false;
                        });
                },
                formatCurrency(value) {
                    if (value === null || value === undefined) return '0.00 ₺';
                    return parseFloat(value).toFixed(2) + ' ₺';
                },
                formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('tr-TR', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },
                getStatusBadgeClass(status) {
                    const statusMap = {
                        'onaylandi': 'badge badge-success',
                        'hazirlaniyor': 'badge badge-warning',
                        'tamamlandi': 'badge badge-info',
                        'iptal': 'badge badge-danger'
                    };
                    return statusMap[status] || 'badge badge-secondary';
                },
                calculateActiveOrdersTotal() {
                    if (!this.activeOrders || this.activeOrders.length === 0) {
                        return 0;
                    }
                    return this.activeOrders.reduce((total, order) => {
                        return total + (parseFloat(order.adet) || 0);
                    }, 0);
                },
                canProduceEnough(component) {
                    if (!component || !this.productData) return false;
                    const requiredAmount = parseFloat(component.bilesen_miktari) || 0;
                    const availableAmount = parseFloat(component.bilesen_stok) || 0;
                    const producibleFromThisComponent = requiredAmount > 0 ? Math.floor(availableAmount / requiredAmount) : 0;
                    return producibleFromThisComponent >= this.producibleQuantity;
                },
                loadFrameContracts() {
                    // Load valid frame contracts directly
                    fetch('api_islemleri/cerceve_sozlesmeler_islemler.php?action=get_valid_contracts')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('HTTP error ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data && data.status === 'success') {
                                // Use the contracts directly as they are already valid
                                this.frameContracts = data.data.filter(contract =>
                                    parseFloat(contract.kalan_miktar) > 0
                                );
                            } else {
                                console.error('Frame contracts could not be loaded:', data?.message || 'Unknown error');
                                // Even if there are issues, ensure the property is initialized
                                this.frameContracts = [];
                            }
                            // Mark contracts as loaded regardless of success or failure
                            this.contractsLoaded = true;
                        })
                        .catch(error => {
                            console.error('Error loading frame contracts:', error);
                            // Initialize as an empty array in case of error
                            this.frameContracts = [];
                            // Still mark as loaded even if there was an error
                            this.contractsLoaded = true;
                        });
                },
                getAvailableContract(componentCode) {
                    // Find the best available contract for a component code (malzeme_kodu or esans_kodu)
                    if (!this.frameContracts || this.frameContracts.length === 0) return null;

                    // Filter contracts that match the component code
                    const matchingContracts = this.frameContracts.filter(contract =>
                        contract.malzeme_kodu === componentCode &&
                        contract.gecerli_mi == 1 &&  // Using == to handle string comparison
                        parseFloat(contract.kalan_miktar) > 0
                    );

                    if (matchingContracts.length === 0) return null;

                    // Return the contract with highest priority (lowest oncelik number)
                    return matchingContracts.reduce((prev, current) =>
                        (prev.oncelik < current.oncelik) ? prev : current
                    );
                },
                getContractForComponent(componentCode) {
                    // Find the best available contract for a specific component code
                    return this.getAvailableContract(componentCode);
                },
                getRelevantContracts() {
                    // Get all frame contracts that are relevant to this product's components
                    if (!this.frameContracts || !this.productData || !this.productData.bom_components) {
                        return [];
                    }

                    // Get all component codes used in this product
                    const componentCodes = this.productData.bom_components.map(comp => comp.bilesen_kodu);

                    // Find contracts that match any of these component codes
                    return this.frameContracts.filter(contract =>
                        componentCodes.includes(contract.malzeme_kodu) &&
                        contract.gecerli_mi == 1  // Using == to handle string comparison
                    );
                },
                getAvailableContractsCount() {
                    // Count how many valid contracts are available for this product
                    return this.getRelevantContracts().length;
                }
            }
        });

        app.mount('#app');
    </script>
</body>

</html>
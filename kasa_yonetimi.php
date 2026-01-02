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
if (!yetkisi_var('page:view:gider_yonetimi')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Kasa Yönetimi - Parfüm ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --accent-hover: #b39023;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --bg-color: #fdf8f5;
            --card-bg: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            font-size: 0.85rem;
        }

        .main-content {
            padding: 1.5rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0;
            color: var(--accent);
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            margin: 0;
        }

        /* Stok Değerleri Grid */
        .stok-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .stok-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
        }

        .stok-card.urun { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .stok-card.malzeme { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stok-card.esans { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .stok-card.toplam { background: linear-gradient(135deg, #667eea, #764ba2); }

        .stok-card .value {
            font-size: 1rem;
            font-weight: 700;
        }

        .stok-card .label {
            font-size: 0.65rem;
            text-transform: uppercase;
            opacity: 0.9;
        }

        /* Kasa Bakiyeleri Grid */
        .kasa-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.4rem;
            margin-bottom: 0.5rem;
        }

        .kasa-card {
            background: white;
            padding: 0.4rem 0.6rem;
            border-radius: 0.4rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kasa-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            border-radius: 0.4rem 0 0 0.4rem;
        }

        .kasa-card.tl::before { background: var(--success); }
        .kasa-card.usd::before { background: var(--info); }
        .kasa-card.eur::before { background: var(--warning); }
        .kasa-card.cek::before { background: var(--primary); }

        .kasa-card .icon {
            width: 24px;
            height: 24px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .kasa-card.tl .icon { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .kasa-card.usd .icon { background: rgba(59, 130, 246, 0.1); color: var(--info); }
        .kasa-card.eur .icon { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .kasa-card.cek .icon { background: rgba(74, 14, 99, 0.1); color: var(--primary); }

        .kasa-card .kasa-info {
            flex: 1;
            min-width: 0;
        }

        .kasa-card .value {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.1;
        }

        .kasa-card .label {
            font-size: 0.6rem;
            color: var(--text-secondary);
            text-transform: uppercase;
        }

        .kasa-card .actions {
            display: flex;
            gap: 0.15rem;
            flex-shrink: 0;
        }

        .kasa-card .actions .btn {
            padding: 0.1rem 0.3rem;
            font-size: 0.6rem;
        }

        /* Info Grids */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .info-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .info-card .card-header {
            background: #f9fafb;
            padding: 0.4rem 0.75rem;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 0.75rem;
        }

        .info-card .card-body {
            padding: 0.5rem 0.75rem;
        }

        .borc-item {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
            border-bottom: 1px dashed var(--border-color);
            font-size: 0.8rem;
        }

        .borc-item:last-child {
            border-bottom: none;
            font-weight: 700;
        }

        /* Content Card */
        .content-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .content-card .card-header {
            background: white;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.8rem;
        }

        .table th {
            background: #f9fafb;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            border-top: none;
        }

        .table td {
            padding: 0.4rem 1rem;
            vertical-align: middle;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background-color: #ffffcd;
        }

        .badge-islem {
            padding: 0.3em 0.6em;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 0.25rem;
        }

        .badge-gelir { background: rgba(16, 185, 129, 0.15); color: #059669; }
        .badge-gider { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
        .badge-cek { background: rgba(74, 14, 99, 0.15); color: var(--primary); }
        .badge-transfer { background: rgba(59, 130, 246, 0.15); color: var(--info); }

        .btn {
            border-radius: 0.25rem;
            padding: 0.35rem 0.8rem;
            font-weight: 600;
            font-size: 0.8rem;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
        }

        .btn-action {
            width: 24px;
            height: 24px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .search-group input {
            border-radius: 0.5rem 0 0 0.5rem;
            border: 1px solid var(--border-color);
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
        }

        .search-group .btn {
            border-radius: 0 0.5rem 0.5rem 0;
            border: 1px solid var(--border-color);
            border-left: none;
        }

        .modal-content {
            border-radius: 1rem;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 1rem 1rem 0 0;
            padding: 0.75rem 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            color: var(--accent);
        }

        .close {
            color: white;
            opacity: 0.8;
        }

        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-radius: 0.5rem 0.5rem 0 0;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            border-color: var(--border-color) var(--border-color) white;
            background-color: white;
            color: var(--primary);
            font-weight: 600;
        }

        .cek-durum {
            padding: 0.25em 0.5em;
            font-size: 0.7rem;
            border-radius: 0.25rem;
            font-weight: 600;
        }

        .cek-durum-alindi { background: rgba(16, 185, 129, 0.15); color: #059669; }
        .cek-durum-tahsilde { background: rgba(59, 130, 246, 0.15); color: var(--info); }
        .cek-durum-kullanildi { background: rgba(107, 114, 128, 0.15); color: #4b5563; }
        .cek-durum-iptal { background: rgba(239, 68, 68, 0.15); color: #dc2626; }

        @media (max-width: 992px) {
            .stok-grid, .kasa-grid { grid-template-columns: repeat(2, 1fr); }
            .info-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 576px) {
            .stok-grid, .kasa-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top"
        style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php">
                <i class="fas fa-spa"></i> IDO KOZMETIK
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="navigation.php">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-wallet"></i> Kasa Yönetimi</h1>
                <p>Çoklu para birimi, çek kasası ve tüm finansal hareketleri tek ekrandan yönetin.</p>
            </div>
            <div class="d-flex gap-2">
                <button id="kasaIslemBtn" class="btn btn-warning mr-2" style="background-color: var(--accent); border: none; color: var(--primary); font-weight: 700;">
                    <i class="fas fa-plus-circle"></i> Kasa İşlemi
                </button>
                <button id="cekEkleBtn" class="btn btn-light" style="font-weight: 600;">
                    <i class="fas fa-money-check"></i> Çek Ekle
                </button>
            </div>
        </div>

        <!-- Alerts -->
        <div id="alert-placeholder"></div>

        <!-- Stok Değerleri -->
        <div class="stok-grid">
            <div class="stok-card urun">
                <div class="label"><i class="fas fa-box"></i> Ürünler</div>
                <div class="value" id="stokUrunler">0,00 ₺</div>
            </div>
            <div class="stok-card malzeme">
                <div class="label"><i class="fas fa-cubes"></i> Malzemeler</div>
                <div class="value" id="stokMalzemeler">0,00 ₺</div>
            </div>
            <div class="stok-card esans">
                <div class="label"><i class="fas fa-flask"></i> Esanslar</div>
                <div class="value" id="stokEsanslar">0,00 ₺</div>
            </div>
            <div class="stok-card toplam">
                <div class="label"><i class="fas fa-chart-pie"></i> Toplam Duran Varlık</div>
                <div class="value" id="stokToplam">0,00 ₺</div>
            </div>
        </div>

        <!-- Kasa Bakiyeleri -->
        <div class="kasa-grid">
            <div class="kasa-card tl">
                <div class="icon"><i class="fas fa-lira-sign"></i></div>
                <div class="kasa-info">
                    <div class="value" id="kasaTL">0,00 ₺</div>
                    <div class="label">TL Kasası</div>
                </div>
                <div class="actions">
                    <button class="btn btn-sm btn-success kasa-ekle-btn" data-kasa="TL"><i class="fas fa-plus"></i></button>
                    <button class="btn btn-sm btn-danger kasa-cikar-btn" data-kasa="TL"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="kasa-card usd">
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="kasa-info">
                    <div class="value" id="kasaUSD">0,00 $</div>
                    <div class="label">USD Kasası</div>
                </div>
                <div class="actions">
                    <button class="btn btn-sm btn-success kasa-ekle-btn" data-kasa="USD"><i class="fas fa-plus"></i></button>
                    <button class="btn btn-sm btn-danger kasa-cikar-btn" data-kasa="USD"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="kasa-card eur">
                <div class="icon"><i class="fas fa-euro-sign"></i></div>
                <div class="kasa-info">
                    <div class="value" id="kasaEUR">0,00 €</div>
                    <div class="label">EUR Kasası</div>
                </div>
                <div class="actions">
                    <button class="btn btn-sm btn-success kasa-ekle-btn" data-kasa="EUR"><i class="fas fa-plus"></i></button>
                    <button class="btn btn-sm btn-danger kasa-cikar-btn" data-kasa="EUR"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="kasa-card cek">
                <div class="icon"><i class="fas fa-money-check-alt"></i></div>
                <div class="kasa-info">
                    <div class="value" id="kasaCek">0 adet</div>
                    <div class="label">Çek <small id="cekTLKarsiligi">(~0 ₺)</small></div>
                </div>
                <div class="actions">
                    <button class="btn btn-sm btn-primary" id="cekDetayBtn"><i class="fas fa-list"></i></button>
                </div>
            </div>
        </div>

        <!-- Bekleyen Ödemeler Grid (Yeni) -->
        <div class="kasa-grid" style="grid-template-columns: repeat(2, 1fr);">
            <div class="kasa-card" style="background: linear-gradient(135deg, #FF9966, #FF5E62); color: white; border: none; position: relative;">
                <button class="btn btn-sm btn-light btn-circle" id="personelDetayBtn" style="position: absolute; top: 10px; right: 10px; width: 30px; height: 30px; padding: 0; line-height: 30px; border-radius: 50%; color: #FF5E62;">
                    <i class="fas fa-list"></i>
                </button>
                <div class="icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-users"></i></div>
                <div class="kasa-info">
                    <div class="value" id="bekleyenPersonel">0,00 ₺</div>
                    <div class="label" style="color: rgba(255,255,255,0.9);">Personel Maaşları (Bekleyen)</div>
                </div>
            </div>
            <div class="kasa-card" style="background: linear-gradient(135deg, #56CCF2, #2F80ED); color: white; border: none; position: relative;">
                <button class="btn btn-sm btn-light btn-circle" id="sabitGiderDetayBtn" style="position: absolute; top: 10px; right: 10px; width: 30px; height: 30px; padding: 0; line-height: 30px; border-radius: 50%; color: #2F80ED;">
                    <i class="fas fa-list"></i>
                </button>
                <div class="icon" style="background: rgba(255,255,255,0.2); color: white;"><i class="fas fa-redo-alt"></i></div>
                <div class="kasa-info">
                    <div class="value" id="bekleyenSabit">0,00 ₺</div>
                    <div class="label" style="color: rgba(255,255,255,0.9);">Sabit Giderler (Bekleyen)</div>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="info-grid">
            <!-- Tedarikçi Borçları -->
            <div class="info-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-invoice-dollar text-danger"></i> Tedarikçilere Yapılacak Ödemeler</span>
                    <button class="btn btn-sm btn-outline-danger" id="tedarikciDetayBtn" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">
                        <i class="fas fa-list"></i> Detay
                    </button>
                </div>
                <div class="card-body" id="tedarikciBorc">
                    <div class="borc-item">
                        <span>USD Borçlar:</span>
                        <span id="borcUSD">0,00 $</span>
                    </div>
                    <div class="borc-item">
                        <span>EUR Borçlar:</span>
                        <span id="borcEUR">0,00 €</span>
                    </div>
                    <div class="borc-item">
                        <span>TL Borçlar:</span>
                        <span id="borcTL">0,00 ₺</span>
                    </div>
                    <div class="borc-item">
                        <span><strong>TL Karşılığı Toplam:</strong></span>
                        <span id="borcToplam" class="text-danger font-weight-bold">0,00 ₺</span>
                    </div>
                </div>
            </div>
            <!-- Müşteri Alacakları -->
            <div class="info-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-hand-holding-usd text-success"></i> Müşteriden Alınacak Ödemeler</span>
                    <button class="btn btn-sm btn-outline-success" id="musteriDetayBtn" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">
                        <i class="fas fa-list"></i> Detay
                    </button>
                </div>
                <div class="card-body" id="musteriAlacak">
                    <div class="borc-item">
                        <span>USD Alacaklar:</span>
                        <span id="alacakUSD">0,00 $</span>
                    </div>
                    <div class="borc-item">
                        <span>EUR Alacaklar:</span>
                        <span id="alacakEUR">0,00 €</span>
                    </div>
                    <div class="borc-item">
                        <span>TL Alacaklar:</span>
                        <span id="alacakTL">0,00 ₺</span>
                    </div>
                    <div class="borc-item">
                        <span><strong>TL Karşılığı Toplam:</strong></span>
                        <span id="alacakToplam" class="text-success font-weight-bold">0,00 ₺</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="kasaTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="hareketler-tab" data-toggle="tab" href="#hareketler" role="tab">
                    <i class="fas fa-exchange-alt"></i> Kasa Hareketleri
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="cekler-tab" data-toggle="tab" href="#cekler" role="tab">
                    <i class="fas fa-money-check"></i> Çek Kasası
                </a>
            </li>
        </ul>

        <div class="tab-content" id="kasaTabsContent" style="padding-top: 1rem;">
            <!-- Kasa Hareketleri Tab -->
            <div class="tab-pane fade show active" id="hareketler" role="tabpanel">
                <div class="content-card">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0"><i class="fas fa-list"></i> Kasa Hareketleri</h6>
                            <select class="form-control form-control-sm ml-2" id="filterKasa" style="width: 120px;">
                                <option value="">Tüm Kasalar</option>
                                <option value="TL">TL</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="cek_kasasi">Çek</option>
                            </select>
                            <select class="form-control form-control-sm ml-2" id="filterIslemTipi" style="width: 140px;">
                                <option value="">Tüm İşlemler</option>
                                <option value="kasa_ekle">Kasa Ekle</option>
                                <option value="kasa_cikar">Kasa Çıkar</option>
                                <option value="gelir_girisi">Gelir Girişi</option>
                                <option value="gider_cikisi">Gider Çıkışı</option>
                                <option value="cek_alma">Çek Alma</option>
                                <option value="cek_odeme">Çek Ödeme</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="date" class="form-control form-control-sm" id="filterBaslangic" style="width: 140px;">
                            <span class="text-muted">-</span>
                            <input type="date" class="form-control form-control-sm" id="filterBitis" style="width: 140px;">
                            <div class="input-group search-group ml-2" style="width: 200px;">
                                <input type="text" class="form-control form-control-sm" id="hareketSearch" placeholder="Ara...">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-outline-secondary" id="clearHareketSearch"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-success ml-2" id="exportExcelBtn">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>İşlem Tipi</th>
                                        <th>Kasa</th>
                                        <th>Tutar</th>
                                        <th>TL Karşılığı</th>
                                        <th>Kaynak</th>
                                        <th>Açıklama</th>
                                        <th>Personel</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody id="hareketlerTableBody">
                                    <tr><td colspan="9" class="text-center p-5 text-muted">Veriler yükleniyor...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="text-muted">
                                <small id="hareketTableInfo">-- kayıt</small>
                                <select class="custom-select custom-select-sm ml-2" id="hareketPerPage" style="width: 70px;">
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="hareketPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Çekler Tab -->
            <div class="tab-pane fade" id="cekler" role="tabpanel">
                <div class="content-card">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0"><i class="fas fa-money-check-alt"></i> Çek Listesi</h6>
                            <select class="form-control form-control-sm ml-2" id="filterCekTipi" style="width: 120px;">
                                <option value="">Tüm Tipler</option>
                                <option value="alacak">Alacak Çek</option>
                                <option value="verilen">Verilen Çek</option>
                            </select>
                            <select class="form-control form-control-sm ml-2" id="filterCekDurum" style="width: 120px;">
                                <option value="">Tüm Durumlar</option>
                                <option value="alindi">Alındı</option>
                                <option value="tahsilde">Tahsilde</option>
                                <option value="kullanildi">Kullanıldı</option>
                                <option value="iptal">İptal</option>
                            </select>
                        </div>
                        <div class="input-group search-group" style="width: 200px;">
                            <input type="text" class="form-control form-control-sm" id="cekSearch" placeholder="Çek ara...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-outline-secondary" id="clearCekSearch"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Çek No</th>
                                        <th>Sahibi</th>
                                        <th>Banka</th>
                                        <th>Tutar</th>
                                        <th>Vade Tarihi</th>
                                        <th>Tip</th>
                                        <th>Durum</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody id="ceklerTableBody">
                                    <tr><td colspan="8" class="text-center p-5 text-muted">Veriler yükleniyor...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="text-muted">
                                <small id="cekTableInfo">-- kayıt</small>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="cekPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kasa İşlem Modal -->
    <div class="modal fade" id="kasaIslemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="kasaIslemForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-cash-register"></i> Kasa İşlemi</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="kasaIslemTipi" name="islem_tipi" value="kasa_ekle">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Para Birimi</label>
                                    <select class="form-control" id="kasaParaBirimi" name="kasa_adi" required>
                                        <option value="TL">TL</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tutar *</label>
                                    <input type="number" class="form-control" id="kasaTutar" name="tutar" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>İşlem Yöntemi</label>
                            <select class="form-control" id="kasaOdemeTipi" name="odeme_tipi_detay">
                                <option value="Nakit">Nakit</option>
                                <option value="Havale/EFT">Havale/EFT</option>
                                <option value="Kredi Kartı">Kredi Kartı</option>
                                <option value="Diğer">Diğer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tarih</label>
                            <input type="datetime-local" class="form-control" id="kasaTarih" name="tarih">
                        </div>
                        <div class="form-group">
                            <label>Açıklama</label>
                            <textarea class="form-control" id="kasaAciklama" name="aciklama" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary" id="kasaSubmitBtn">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Çek Ekle Modal -->
    <div class="modal fade" id="cekEkleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="cekEkleForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-money-check"></i> Yeni Çek Ekle</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Çek No *</label>
                                    <input type="text" class="form-control" id="cekNo" name="cek_no" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tutar *</label>
                                    <input type="number" class="form-control" id="cekTutari" name="cek_tutari" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Para Birimi</label>
                                    <select class="form-control" id="cekParaBirimi" name="cek_para_birimi">
                                        <option value="TL">TL</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Çek Sahibi *</label>
                                    <input type="text" class="form-control" id="cekSahibi" name="cek_sahibi" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vade Tarihi *</label>
                                    <input type="date" class="form-control" id="cekVade" name="vade_tarihi" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Banka Adı</label>
                                    <input type="text" class="form-control" id="cekBanka" name="cek_banka_adi">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Şube</label>
                                    <input type="text" class="form-control" id="cekSube" name="cek_subesi">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Çek Tipi</label>
                                    <select class="form-control" id="cekTipi" name="cek_tipi">
                                        <option value="alacak">Alacak Çek (Bize Verilen)</option>
                                        <option value="verilen">Verilen Çek (Bizim Verdiğimiz)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Açıklama</label>
                                    <input type="text" class="form-control" id="cekAciklama" name="aciklama">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tedarikçi Borçları Detay Modal -->
    <div class="modal fade" id="tedarikciDetayModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <h5 class="modal-title" style="color: #fff;"><i class="fas fa-file-invoice-dollar"></i> Tedarikçilere Yapılacak Ödemeler</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tedarikçi</th>
                                    <th>Miktar</th>
                                    <th>Birim Fiyat</th>
                                    <th>Toplam Borç</th>
                                </tr>
                            </thead>
                            <tbody id="tedarikciDetayTableBody">
                                <tr><td colspan="4" class="text-center p-4">Yükleniyor...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Müşteri Alacakları Detay Modal -->
    <div class="modal fade" id="musteriDetayModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <h5 class="modal-title" style="color: #fff;"><i class="fas fa-hand-holding-usd"></i> Müşteriden Alınacak Ödemeler</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Sipariş No</th>
                                    <th>Müşteri</th>
                                    <th>Tarih</th>
                                    <th>Sipariş Tutarı</th>
                                    <th>Ödenen</th>
                                    <th>Kalan Alacak</th>
                                </tr>
                            </thead>
                            <tbody id="musteriDetayTableBody">
                                <tr><td colspan="6" class="text-center p-4">Yükleniyor...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Personel Maaşları Detay Modal -->
    <div class="modal fade" id="personelMaasDetayModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #FF9966, #FF5E62);">
                    <h5 class="modal-title" style="color: #fff;"><i class="fas fa-users"></i> Personel Maaşları Detayı</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Personel</th>
                                    <th>Brüt Ücret</th>
                                    <th>Düşülmemiş Avans</th>
                                    <th>Ödenen Maaş</th>
                                    <th>Tahmini Ödenecek</th>
                                </tr>
                            </thead>
                            <tbody id="personelDetayTableBody">
                                <tr><td colspan="5" class="text-center p-4">Yükleniyor...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                    <a href="personel_bordro.php" class="btn btn-primary">Bordro Sayfasına Git</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sabit Giderler Detay Modal -->
    <div class="modal fade" id="sabitGiderDetayModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #56CCF2, #2F80ED);">
                    <h5 class="modal-title" style="color: #fff;"><i class="fas fa-redo-alt"></i> Sabit Giderler Detayı</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Vade Günü</th>
                                    <th>Ödeme Adı</th>
                                    <th>Alıcı Firma</th>
                                    <th>Tutar</th>
                                </tr>
                            </thead>
                            <tbody id="sabitGiderDetayTableBody">
                                <tr><td colspan="4" class="text-center p-4">Yükleniyor...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                    <a href="tekrarli_odemeler.php" class="btn btn-primary">Ödemeler Sayfasına Git</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/kasa_yonetimi.js"></script>
</body>
</html>

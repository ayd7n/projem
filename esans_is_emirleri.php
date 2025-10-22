<?php
require_once 'config.php';

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

// Fetch all essence work orders
$work_orders_query = "SELECT * FROM esans_is_emirleri ORDER BY olusturulma_tarihi DESC";
$work_orders_result = $connection->query($work_orders_query);

// Calculate total work orders
$total_result = $connection->query("SELECT COUNT(*) as total FROM esans_is_emirleri");
$total_work_orders = $total_result->fetch_assoc()['total'] ?? 0;

// Fetch all essences for dropdown
$essences_query = "SELECT esans_kodu, esans_ismi, birim, demlenme_suresi_gun FROM esanslar ORDER BY esans_ismi";
$essences_result = $connection->query($essences_query);

// Fetch all tanks
$tanks_query = "SELECT DISTINCT tank_kodu, tank_ismi FROM tanklar ORDER BY tank_ismi";
$tanks_result = $connection->query($tanks_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Esans Is Emirleri - Parfum ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63; /* Deep Purple */
            --secondary: #7c2a99; /* Lighter Purple */
            --accent: #d4af37; /* Gold */
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --bg-color: #fdf8f5; /* Soft Cream */
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #111827; /* Dark Gray/Black */
            --text-secondary: #6b7280; /* Medium Gray */
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }
        html {
            font-size: 15px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        
        .no-records-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .form-control:disabled {
            background-color: #f8f9fa;
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .mobile-menu-btn {
            display: none;
        }

        html {
            scroll-behavior: smooth;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
        }

        @media (max-width: 991.98px) {
            #workOrderModal {
                position: fixed;
                top: 0;
                right: 0;
                width: 320px;
                height: 100%;
                z-index: 1050; /* Higher than navbar */
                border-radius: 0;
                box-shadow: -5px 0 20px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
                overflow-y: auto;
            }
            #workOrderModal.show {
                transform: translateX(0);
            }
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }

        @media (min-width: 1200px) {
            #workOrderModal .modal-xl {
                max-width: 95%;
            }
        }
    </style>
</head>
<body>
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
                        <a class="nav-link" href="change_password.php">Parolami Degistir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanici'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cikis Yap</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Information Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(45deg, var(--primary), var(--secondary)); color: white;">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> Esans İş Emirleri Sistemi Bilgileri</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <!-- System Overview -->
                        <div class="section">
                            <h4 class="section-title"><i class="fas fa-project-diagram text-primary"></i> Sistem Genel Yapısı</h4>
                            <p>Esans iş emirleri sayfası, parfüm üretiminde kullanılan esans üretimi iş emirlerinin oluşturulduğu, takip edildiği ve yönetildiği entegre bir sistemdir. Sistem, iş emri durumlarına göre farklı işlemlere izin vererek üretim sürecinin doğru şekilde yürütülmesini sağlar.</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-cogs text-info"></i> İş Akışı</h5>
                                    <ol class="ml-4">
                                        <li><strong>İş Emri Oluşturma:</strong> "olusturuldu" durumu</li>
                                        <li><strong>Üretime Başlama:</strong> "uretimde" durumu</li>
                                        <li><strong>Üretim Tamamlama:</strong> "tamamlandi" durumu</li>
                                        <li><strong>İptal veya Geri Alma:</strong> "iptal" durumu</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-shield-alt text-success"></i> Güvenlik Kontrolleri</h5>
                                    <ul class="list-unstyled ml-3">
                                        <li><i class="fas fa-lock text-warning"></i> Her durum sadece yetkili işlemleri kabul eder</li>
                                        <li><i class="fas fa-lock text-warning"></i> Geçersiz durum değişiklikleri engellenir</li>
                                        <li><i class="fas fa-lock text-warning"></i> Stok kontrolleri ile negatif stok önlenir</li>
                                        <li><i class="fas fa-lock text-warning"></i> İşlem geçmişinin izlenebilirliği korunur</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Production Process -->
                        <div class="section mt-4">
                            <h4 class="section-title"><i class="fas fa-flask text-success"></i> Esans Üretim Süreci</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-list-ol text-primary"></i> Üretim Adımları</h5>
                                    <div class="process-steps">
                                        <div class="step">
                                            <div class="step-number">1</div>
                                            <div class="step-content">
                                                <h6>Esans Seçimi</h6>
                                                <p>Üretilecek esans türünü seçin</p>
                                            </div>
                                        </div>
                                        <div class="step">
                                            <div class="step-number">2</div>
                                            <div class="step-content">
                                                <h6>Miktar Belirleme</h6>
                                                <p>Üretilecek miktarı girin</p>
                                            </div>
                                        </div>
                                        <div class="step">
                                            <div class="step-number">3</div>
                                            <div class="step-content">
                                                <h6>Bileşen Hesaplama</h6>
                                                <p>Gerekli malzemeler otomatik hesaplanır</p>
                                            </div>
                                        </div>
                                        <div class="step">
                                            <div class="step-number">4</div>
                                            <div class="step-content">
                                                <h6>Tank ve Tarih Planlama</h6>
                                                <p>Üretim tankı ve tarihleri belirleyin</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-lightbulb text-warning"></i> Örnek Senaryo</h5>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6><i class="fas fa-vial"></i> 100 Litre Gül Esansı Üretimi</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-arrow-right"></i> <strong>Gerekli Bileşenler:</strong></li>
                                                <li class="ml-4"><i class="fas fa-leaf"></i> Yasemin Çiçeği: 50 kg</li>
                                                <li class="ml-4"><i class="fas fa-wine-bottle"></i> Alkol: 30 lt</li>
                                                <li class="ml-4"><i class="fas fa-tint"></i> Saf Su: 20 lt</li>
                                                <li><i class="fas fa-arrow-right"></i> <strong>Üretim Süresi:</strong> 30 gün</li>
                                                <li><i class="fas fa-arrow-right"></i> <strong>Tank Kullanımı:</strong> Tank 1</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Work Order Status -->
                        <div class="section mt-4">
                            <h4 class="section-title"><i class="fas fa-tasks text-info"></i> İş Emri Durumları ve İşlemler</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Durum</th>
                                            <th>Açıklama</th>
                                            <th>Mevcut İşlemler</th>
                                            <th>Veritabanı Etkisi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge badge-secondary">olusturuldu</span></td>
                                            <td>İş emri oluşturuldu, henüz başlatılmadı</td>
                                            <td>
                                                <span class="badge badge-success">Başlat</span>
                                                <span class="badge badge-primary">Düzenle</span>
                                                <span class="badge badge-danger">Sil</span>
                                            </td>
                                            <td>Kayıt oluşturuldu, stok etkileşimi yok</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-warning">uretimde</span></td>
                                            <td>İş emri başlatıldı, üretim devam ediyor</td>
                                            <td>
                                                <span class="badge badge-success">Tamamla</span>
                                                <span class="badge badge-warning">Geri Al</span>
                                                <span class="badge badge-primary">Düzenle</span>
                                            </td>
                                            <td>Malzeme stokları düşürüldü</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-success">tamamlandi</span></td>
                                            <td>İş emri başarıyla tamamlandı</td>
                                            <td>
                                                <span class="badge badge-warning">Geri Al</span>
                                            </td>
                                            <td>Esans stokları artırıldı</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge badge-danger">iptal</span></td>
                                            <td>İş emri iptal edildi</td>
                                            <td><span class="badge badge-secondary">İşlem Yok</span></td>
                                            <td>Manuel iptal durumu</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Buttons and Functions -->
                        <div class="section mt-4">
                            <h4 class="section-title"><i class="fas fa-mouse-pointer text-primary"></i> Buton İşlevleri ve Veritabanı Etkileri</h4>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title m-0"><i class="fas fa-plus"></i> Yeni Esans Is Emri Olustur</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text"><small class="text-muted">Yeni bir essans iş emri oluşturmak için modal formu açar</small></p>
                                            <div class="alert alert-info p-2">
                                                <h6 class="alert-heading"><i class="fas fa-database"></i> Veritabanı Etkisi:</h6>
                                                <ul class="mb-0">
                                                    <li><code>esans_is_emirleri</code> tablosuna yeni kayıt</li>
                                                    <li><code>esans_is_emri_malzeme_listesi</code> tablosuna bileşen kayıtları</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title m-0"><i class="fas fa-play"></i> Is Emrini Baslat</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text"><small class="text-muted">Sadece "olusturuldu" durumunda olan iş emirleri için görünür</small></p>
                                            <div class="alert alert-info p-2">
                                                <h6 class="alert-heading"><i class="fas fa-database"></i> Veritabanı Etkisi:</h6>
                                                <ul class="mb-0">
                                                    <li><code>durum</code> alanı "uretimde" olarak güncellenir</li>
                                                    <li><code>gerceklesen_baslangic_tarihi</code> alanı doldurulur</li>
                                                    <li>Malzeme stokları düşülür</li>
                                                    <li>"Üretime Çıkış" stok hareket kaydı oluşturulur</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="card-title m-0"><i class="fas fa-undo"></i> Uretimi Durdur/Geri Al</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text"><small class="text-muted">Sadece "uretimde" durumunda olan iş emirleri için görünür</small></p>
                                            <div class="alert alert-info p-2">
                                                <h6 class="alert-heading"><i class="fas fa-database"></i> Veritabanı Etkisi:</h6>
                                                <ul class="mb-0">
                                                    <li><code>durum</code> alanı "olusturuldu" olarak güncellenir</li>
                                                    <li><code>gerceklesen_baslangic_tarihi</code> alanı NULL yapılır</li>
                                                    <li>Malzeme stokları iade edilir</li>
                                                    <li>"Üretimden İade" stok hareket kaydı oluşturulur</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title m-0"><i class="fas fa-check-square"></i> Is Emrini Tamamla</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text"><small class="text-muted">Sadece "uretimde" durumunda olan iş emirleri için görünür</small></p>
                                            <div class="alert alert-info p-2">
                                                <h6 class="alert-heading"><i class="fas fa-database"></i> Veritabanı Etkisi:</h6>
                                                <ul class="mb-0">
                                                    <li><code>durum</code> alanı "tamamlandi" olarak güncellenir</li>
                                                    <li><code>tamamlanan_miktar</code> alanı doldurulur</li>
                                                    <li><code>gerceklesen_bitis_tarihi</code> alanı doldurulur</li>
                                                    <li>Esans stoğu artırılır</li>
                                                    <li>"Üretimden Giriş" stok hareket kaydı oluşturulur</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="card-title m-0"><i class="fas fa-history"></i> Tamamlamayi Geri Al</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text"><small class="text-muted">Sadece "tamamlandi" durumunda olan iş emirleri için görünür</small></p>
                                            <div class="alert alert-info p-2">
                                                <h6 class="alert-heading"><i class="fas fa-database"></i> Veritabanı Etkisi:</h6>
                                                <ul class="mb-0">
                                                    <li><code>durum</code> alanı "uretimde" olarak güncellenir</li>
                                                    <li><code>tamamlanan_miktar</code> alanı sıfırlanır</li>
                                                    <li><code>gerceklesen_bitis_tarihi</code> alanı NULL yapılır</li>
                                                    <li>Esans stoğu düşülür</li>
                                                    <li>"Üretim İptal" stok hareket kaydı oluşturulur</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title m-0"><i class="fas fa-edit"></i> Duzenle</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text"><small class="text-muted">"tamamlandi" veya "iptal" durumunda olmayan iş emirleri için görünür</small></p>
                                            <div class="alert alert-info p-2">
                                                <h6 class="alert-heading"><i class="fas fa-database"></i> Veritabanı Etkisi:</h6>
                                                <ul class="mb-0">
                                                    <li>İş emri temel bilgileri güncellenir</li>
                                                    <li>Bileşen listesi yeniden hesaplanır ve güncellenir</li>
                                                    <li><code>esans_is_emri_malzeme_listesi</code> tablosu yeniden oluşturulur</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-danger">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="card-title m-0"><i class="fas fa-trash"></i> Sil</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text"><small class="text-muted">"tamamlandi" durumunda olmayan iş emirleri için görünür</small></p>
                                            <div class="alert alert-info p-2">
                                                <h6 class="alert-heading"><i class="fas fa-database"></i> Veritabanı Etkisi:</h6>
                                                <ul class="mb-0">
                                                    <li><code>esans_is_emri_malzeme_listesi</code> tablosundan ilgili kayıtlar silinir</li>
                                                    <li><code>esans_is_emirleri</code> tablosundan iş emri kaydı silinir</li>
                                                    <li>İlgili stok hareket kayıtları korunur (denetim amaçlı)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Database Structure -->
                        <div class="section mt-4">
                            <h4 class="section-title"><i class="fas fa-database text-primary"></i> Veritabanı Yapısı ve Tablo İlişkileri</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-table text-info"></i> Ana Tablolar</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Tablo Adı</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>esans_is_emirleri</code></td>
                                                    <td>İş emri temel bilgileri</td>
                                                </tr>
                                                <tr>
                                                    <td><code>esans_is_emri_malzeme_listesi</code></td>
                                                    <td>İş emrinin bileşenleri</td>
                                                </tr>
                                                <tr>
                                                    <td><code>esanslar</code></td>
                                                    <td>Esans bilgileri ve stoğu</td>
                                                </tr>
                                                <tr>
                                                    <td><code>malzemeler</code></td>
                                                    <td>Malzeme bilgileri ve stoğu</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-table text-success"></i> Yardımcı Tablolar</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Tablo Adı</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>urun_agaci</code></td>
                                                    <td>Esans bileşen tanımları</td>
                                                </tr>
                                                <tr>
                                                    <td><code>tanklar</code></td>
                                                    <td>Tank bilgileri</td>
                                                </tr>
                                                <tr>
                                                    <td><code>stok_hareket_kayitlari</code></td>
                                                    <td>Stok hareket geçmişi</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="mt-3"><i class="fas fa-link text-warning"></i> Tablo İlişkileri</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-arrow-right"></i> <code>esans_is_emri_malzeme_listesi</code> → <code>esans_is_emirleri</code> (<code>is_emri_numarasi</code> ile)</li>
                                <li><i class="fas fa-arrow-right"></i> Bileşen hesaplaması için <code>urun_agaci</code> tablosu kullanılır (<code>urun_kodu</code> → <code>esans_id</code>)</li>
                                <li><i class="fas fa-arrow-right"></i> Stok hareketleri <code>esans</code> veya <code>malzeme</code> olarak <code>stok_hareket_kayitlari</code> tablosuna kayıt altına alınır</li>
                            </ul>
                            
                            <h5 class="mt-3"><i class="fas fa-code text-purple"></i> API İşlemleri</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-cog text-info"></i> <code>calculate_components</code>: Esans bileşenlerini hesaplar</li>
                                        <li><i class="fas fa-cog text-info"></i> <code>start_work_order</code>: İş emrini başlatır, malzeme stoklarını düşer</li>
                                        <li><i class="fas fa-cog text-info"></i> <code>complete_work_order</code>: İş emrini tamamlar, esans stoğunu artırır</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-cog text-info"></i> <code>revert_work_order</code>: İş emrini geri alır, malzeme stoğunu iade eder</li>
                                        <li><i class="fas fa-cog text-info"></i> <code>revert_completion</code>: Tamamlamayı geri alır, esans stoğunu düşer</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Transactions -->
                        <div class="section mt-4">
                            <h4 class="section-title"><i class="fas fa-exchange-alt text-success"></i> Stok Hareketleri ve Kayıt Türleri</h4>
                            <p>Sistemde gerçekleşen tüm işlemler detaylı şekilde kayıt altına alınır. Her stok hareketi kullanıcı bilgisi ve tarih damgasıyla birlikte saklanır:</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-plus-circle text-success"></i> Stok Arttıran Hareketler</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <h6 class="card-title m-0"><i class="fas fa-arrow-up text-success"></i> Üretimden Giriş</h6>
                                                    <p class="card-text mb-1"><small>İş emri tamamlanınca üretilen esans stoğa eklenir</small></p>
                                                    <span class="badge badge-success">Giriş</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-minus-circle text-danger"></i> Stok Azaltan Hareketler</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <h6 class="card-title m-0"><i class="fas fa-arrow-down text-danger"></i> Üretime Çıkış</h6>
                                                    <p class="card-text mb-1"><small>İş emri başlatılınca gerekli malzemeler stoktan düşülür</small></p>
                                                    <span class="badge badge-danger">Çıkış</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <h6 class="card-title m-0"><i class="fas fa-arrow-down text-danger"></i> Üretim İptal</h6>
                                                    <p class="card-text mb-1"><small>Tamamlama işlemi geri alınca esans stoğu düşülür</small></p>
                                                    <span class="badge badge-danger">Çıkış</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <h5 class="mt-3"><i class="fas fa-undo text-warning"></i> Stok Geri Yükleyen Hareketler</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <h6 class="card-title m-0"><i class="fas fa-sync text-warning"></i> Üretimden İade</h6>
                                                    <p class="card-text mb-1"><small>İş emri geri alınca malzemeler stoğa iade edilir</small></p>
                                                    <span class="badge badge-warning">Giriş</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <h5 class="mt-3"><i class="fas fa-database text-primary"></i> Veritabanı Kayıt İşlemleri</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-plus-circle text-success"></i> Kayıt Oluşturma</h6>
                                    <ul class="list-unstyled ml-3">
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>Yeni İş Emri:</strong> <code>esans_is_emirleri</code> ve <code>esans_is_emri_malzeme_listesi</code> tablolarına kayıt</li>
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>İş Emri Başlatma:</strong> <code>stok_hareket_kayitlari</code> tablosuna "Üretime Çıkış" kaydı</li>
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>İş Emri Tamamlama:</strong> <code>stok_hareket_kayitlari</code> tablosuna "Üretimden Giriş" kaydı</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-sync-alt text-warning"></i> Kayıt Güncelleme</h6>
                                    <ul class="list-unstyled ml-3">
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>Durum Değişikliği:</strong> <code>esans_is_emirleri</code> tablosunda <code>durum</code>, tarih alanları</li>
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>Stok Güncelleme:</strong> <code>malzemeler</code> ve <code>esanslar</code> tablolarında <code>stok_miktari</code></li>
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>İş Emri Düzenleme:</strong> Mevcut kayıtların güncellenmesi</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-undo text-primary"></i> Kayıt Geri Alma</h6>
                                    <ul class="list-unstyled ml-3">
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>Üretimi Geri Alma:</strong> <code>stok_hareket_kayitlari</code> tablosuna "Üretimden İade" kaydı</li>
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>Tamamlamayı Geri Alma:</strong> <code>stok_hareket_kayitlari</code> tablosuna "Üretim İptal" kaydı</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-trash-alt text-danger"></i> Kayıt Silme</h6>
                                    <ul class="list-unstyled ml-3">
                                        <li><i class="fas fa-arrow-right text-info"></i> <strong>İş Emri Silme:</strong> <code>esans_is_emri_malzeme_listesi</code> ve <code>esans_is_emirleri</code> tablolarından kayıt silme</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
        
        <div class="page-header">
            <div>
                <h1><i class="fas fa-flask"></i> Esans Is Emirleri</h1>
                <p>Esans uretim is emirlerini olusturun, duzenleyin ve takip edin</p>
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-12">
                <button id="addWorkOrderBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Esans Is Emri Olustur</button>
                <button id="infoButton" class="btn btn-info mb-3 ml-2"><i class="fas fa-info-circle"></i> Bilgi</button>
            </div>
        </div>



        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-list"></i> Esans Is Emirleri Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> Islemler</th>
                                <th><i class="fas fa-hashtag"></i> Is Emri No</th>
                                <th><i class="fas fa-info-circle"></i> Durum</th>
                                <th><i class="fas fa-flask"></i> Esans</th>
                                <th><i class="fas fa-tint"></i> Tank</th>
                                <th><i class="fas fa-weight"></i> Planlanan Miktar</th>
                                <th><i class="fas fa-check"></i> Tamamlanan Miktar</th>
                                <th><i class="fas fa-exclamation-triangle"></i> Eksik Miktar</th>
                                <th><i class="fas fa-ruler"></i> Birim</th>
                                <th><i class="fas fa-calendar-alt"></i> Olusturulma Tarihi</th>
                                <th><i class="fas fa-user"></i> Olusturan</th>
                                <th><i class="fas fa-hourglass-start"></i> Planlanan Baslangic</th>
                                <th><i class="fas fa-hourglass-end"></i> Planlanan Bitis</th>
                                <th><i class="fas fa-play-circle"></i> Gerceklese Baslangic</th>
                                <th><i class="fas fa-check-circle"></i> Gerceklese Bitis</th>
                                <th><i class="fas fa-hourglass-half"></i> Demlenme Suresi (Gun)</th>
                                <th><i class="fas fa-comment"></i> Aciklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($work_orders_result && $work_orders_result->num_rows > 0): ?>
                                <?php while ($work_order = $work_orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="actions">
                                            <?php if ($work_order['durum'] === 'olusturuldu'): ?>
                                                <button class="btn btn-success btn-sm start-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>" title="Is Emrini Baslat">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php elseif ($work_order['durum'] === 'uretimde'): ?>
                                                <button class="btn btn-warning btn-sm revert-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>" title="Uretimi Durdur/Geri Al">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button class="btn btn-success btn-sm complete-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>" title="Is Emrini Tamamla">
                                                    <i class="fas fa-check-square"></i>
                                                </button>
                                            <?php elseif ($work_order['durum'] === 'tamamlandi'): ?>
                                                <button class="btn btn-warning btn-sm revert-completion-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>" title="Tamamlamayi Geri Al">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($work_order['durum'] !== 'tamamlandi' && $work_order['durum'] !== 'iptal'): ?>
                                                <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>" title="Duzenle">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>

                                            <button class="btn btn-info btn-sm details-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>" title="Detaylar">
                                                <i class="fas fa-list"></i>
                                            </button>

                                            <?php if ($work_order['durum'] !== 'tamamlandi'): ?>
                                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>" title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $work_order['is_emri_numarasi']; ?></td>
                                        <td>
                                            <span class="status-badge badge-<?php echo $work_order['durum'] === 'olusturuldu' ? 'secondary' : ($work_order['durum'] === 'uretimde' ? 'warning' : ($work_order['durum'] === 'tamamlandi' ? 'success' : 'danger')); ?>">
                                                <?php 
                                                if($work_order['durum'] === 'olusturuldu') echo 'Olusturuldu';
                                                elseif($work_order['durum'] === 'uretimde') echo 'Uretimde';
                                                elseif($work_order['durum'] === 'tamamlandi') echo 'Tamamlandi';
                                                else echo 'Iptal';
                                                ?>
                                            </span>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($work_order['esans_kodu'] . ' - ' . $work_order['esans_ismi']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($work_order['tank_kodu'] . ' - ' . $work_order['tank_ismi']); ?></td>
                                        <td><?php echo number_format($work_order['planlanan_miktar'], 2); ?></td>
                                        <td><?php echo number_format($work_order['tamamlanan_miktar'], 2); ?></td>
                                        <td><?php echo number_format($work_order['eksik_miktar_toplami'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($work_order['birim']); ?></td>
                                        <td><?php echo $work_order['olusturulma_tarihi']; ?></td>
                                        <td><?php echo htmlspecialchars($work_order['olusturan']); ?></td>
                                        <td><?php echo $work_order['planlanan_baslangic_tarihi']; ?></td>
                                        <td><?php echo $work_order['planlanan_bitis_tarihi']; ?></td>
                                        <td><?php echo $work_order['gerceklesen_baslangic_tarihi']; ?></td>
                                        <td><?php echo $work_order['gerceklesen_bitis_tarihi']; ?></td>
                                        <td><?php echo htmlspecialchars($work_order['demlenme_suresi_gun']); ?></td>
                                        <td><?php echo htmlspecialchars($work_order['aciklama']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="17" class="text-center p-4">Henüz kayitli esans is emri bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Order Modal -->
    <div class="modal fade" id="workOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form id="workOrderForm">
                    <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-bolt"></i> Esans Is Emri Formu</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-7">
                                <input type="hidden" id="is_emri_numarasi" name="is_emri_numarasi">
                                <input type="hidden" id="action" name="action">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="esans_kodu">Esans *</label>
                                            <select class="form-control" id="esans_kodu" name="esans_kodu" required>
                                                <option value="">Esans Secin</option>
                                                <?php while($essence = $essences_result->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($essence['esans_kodu']); ?>" data-unit="<?php echo htmlspecialchars($essence['birim']); ?>" data-fermentation="<?php echo htmlspecialchars($essence['demlenme_suresi_gun']); ?>">
                                                        <?php echo htmlspecialchars($essence['esans_kodu'] . ' - ' . $essence['esans_ismi']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="planlanan_miktar">Planlanan Miktar *</label>
                                            <input type="number" step="0.01" class="form-control" id="planlanan_miktar" name="planlanan_miktar" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="birim">Birim</label>
                                            <input type="text" class="form-control" id="birim" name="birim" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="tank_kodu">Tank *</label>
                                            <select class="form-control" id="tank_kodu" name="tank_kodu" required>
                                                <option value="">Tank Secin</option>
                                                <?php 
                                                // Reset the result pointer for tanks
                                                $tanks_result->data_seek(0);
                                                while($tank = $tanks_result->fetch_assoc()): 
                                                ?>
                                                    <option value="<?php echo htmlspecialchars($tank['tank_kodu']); ?>">
                                                        <?php echo htmlspecialchars($tank['tank_kodu'] . ' - ' . $tank['tank_ismi']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="planlanan_baslangic_tarihi">Planlanan Baslangic Tarihi *</label>
                                            <input type="date" class="form-control" id="planlanan_baslangic_tarihi" name="planlanan_baslangic_tarihi" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="demlenme_suresi_gun">Demlenme Suresi (Gun)</label>
                                            <input type="number" class="form-control" id="demlenme_suresi_gun" name="demlenme_suresi_gun">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="planlanan_bitis_tarihi">Planlanan Bitis Tarihi</label>
                                            <input type="date" class="form-control" id="planlanan_bitis_tarihi" name="planlanan_bitis_tarihi" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="durum">Durum</label>
                                            <select class="form-control" id="durum" name="durum">
                                                <option value="olusturuldu">Olusturuldu</option>
                                                <option value="uretimde">Uretimde</option>
                                                <option value="tamamlandi">Tamamlandi</option>
                                                <option value="iptal">Iptal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="aciklama">Aciklama</label>
                                    <textarea class="form-control" id="aciklama" name="aciklama" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <!-- Calculated Components Section -->
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5><i class="fas fa-cubes"></i> Hesaplanan Bilesenler</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Malzeme Kodu</th>
                                                        <th>Malzeme Ismi</th>
                                                        <th>Malzeme Turu</th>
                                                        <th>Gerekli Miktar</th>
                                                        <th>Bilesim Orani</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="componentsTableBody">
                                                    <!-- Components will be populated here via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="componentsPlaceholder" class="text-center py-3 text-muted">
                                            Esans ve miktar secildiginde bilesenler gosterilecektir.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Iptal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-save"></i> Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Materials Details Modal -->
    <div class="modal fade" id="materialsDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                    <h5 class="modal-title"><i class="fas fa-cubes"></i> <span id="materialsModalTitle">Malzeme Detaylari</span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="materialsDetailsContent">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Malzeme Kodu</th>
                                        <th>Malzeme Ismi</th>
                                        <th>Malzeme Turu</th>
                                        <th>Gerekli Miktar</th>
                                        <th>Birim</th>
                                    </tr>
                                </thead>
                                <tbody id="materialsDetailsTableBody">
                                    <!-- Materials will be populated here -->
                                </tbody>
                            </table>
                        </div>
                        <div id="materialsDetailsPlaceholder" class="text-center py-3 text-muted">
                            Malzeme detaylari yukleniyor...
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Work Order Modal -->
    <div class="modal fade" id="completeWorkOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="completeWorkOrderForm">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-check-square"></i> Is Emrini Tamamla</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="complete_is_emri_numarasi" name="is_emri_numarasi">
                        <h5>Is Emri Bilgileri</h5>
                        <p><strong>Is Emri No:</strong> <span id="complete_wo_id"></span></p>
                        <p><strong>Esans:</strong> <span id="complete_wo_essence"></span></p>
                        <p><strong>Planlanan Miktar:</strong> <span id="complete_wo_planned_qty"></span></p>
                        <hr>
                        <div class="form-group">
                            <label for="tamamlanan_miktar"><strong>Gerceklese Miktar *</strong></label>
                            <input type="number" step="0.01" class="form-control" id="tamamlanan_miktar" name="tamamlanan_miktar" required>
                        </div>
                        <div class="form-group">
                            <label for="tamamlama_aciklama">Aciklama</label>
                            <textarea class="form-control" id="tamamlama_aciklama" name="aciklama" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Iptal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Uretimi Tamamla ve Stoklari Guncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
        // Reset the essence result for JavaScript use
        <?php $essences_result->data_seek(0); ?>
        
        // Refresh the essence data for JavaScript
        var essences = [
            <?php while($essence = $essences_result->fetch_assoc()): ?>
            {
                code: '<?php echo $essence['esans_kodu']; ?>',
                unit: '<?php echo $essence['birim']; ?>',
                fermentation: <?php echo floatval($essence['demlenme_suresi_gun']); ?>
            },
            <?php endwhile; ?>
        ];
        
        function showAlert(message, type) {
            $('#alert-placeholder').html(
                `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`
            );
        }

        // Open modal for adding a new work order
        $('#addWorkOrderBtn').on('click', function() {
            $('#workOrderForm')[0].reset();
            $('#modalTitle').text('Yeni Esans Is Emri Olustur');
            $('#action').val('create_work_order');
            $('#submitBtn').text('Olustur').removeClass('btn-success').addClass('btn-primary');
            $('#durum').val('olusturuldu').prop('disabled', true); // Default to 'olusturuldu' and disable for creation
            
            // Set default date to today
            var today = new Date().toISOString().split('T')[0];
            $('#planlanan_baslangic_tarihi').val(today);
            
            // Clear components table
            $('#componentsTableBody').empty();
            $('#componentsPlaceholder').show();
            
            $('#workOrderModal').modal('show');
        });

        // Handle essence selection to update unit and fermentation time
        $('#esans_kodu').on('change', function() {
            var selectedCode = $(this).val();
            var selectedOption = $(this).find('option:selected');
            
            if (selectedCode) {
                // Populate unit field
                var unit = selectedOption.data('unit');
                $('#birim').val(unit);
                
                // Populate fermentation time
                var fermentation = selectedOption.data('fermentation');
                $('#demlenme_suresi_gun').val(fermentation);
                
                // Calculate end date if start date is already selected
                var startDate = $('#planlanan_baslangic_tarihi').val();
                if (startDate && fermentation) {
                    var endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + parseInt(fermentation));
                    var endDateStr = endDate.toISOString().split('T')[0];
                    $('#planlanan_bitis_tarihi').val(endDateStr);
                }
                
                // If quantity is entered, calculate components
                var quantity = $('#planlanan_miktar').val();
                if (quantity && quantity > 0) {
                    calculateComponents(selectedCode, quantity);
                }
            } else {
                $('#birim').val('');
                $('#demlenme_suresi_gun').val('');
                $('#planlanan_bitis_tarihi').val('');
            }
        });

        // Handle quantity input to calculate components
        $('#planlanan_miktar').on('input', function() {
            var quantity = $(this).val();
            var essenceCode = $('#esans_kodu').val();
            
            if (quantity && quantity > 0 && essenceCode) {
                calculateComponents(essenceCode, quantity);
            } else {
                // Clear components if quantity is not valid
                $('#componentsTableBody').empty();
                $('#componentsPlaceholder').show();
            }
        });

        // Handle start date change to recalculate end date
        $('#planlanan_baslangic_tarihi').on('change', function() {
            var startDate = $(this).val();
            var fermentation = $('#demlenme_suresi_gun').val();
            
            if (startDate && fermentation) {
                var endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + parseInt(fermentation));
                var endDateStr = endDate.toISOString().split('T')[0];
                $('#planlanan_bitis_tarihi').val(endDateStr);
            }
        });

        // Handle fermentation time change to recalculate end date
        $('#demlenme_suresi_gun').on('input', function() {
            var fermentation = $(this).val();
            var startDate = $('#planlanan_baslangic_tarihi').val();
            
            if (startDate && fermentation) {
                var endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + parseInt(fermentation));
                var endDateStr = endDate.toISOString().split('T')[0];
                $('#planlanan_bitis_tarihi').val(endDateStr);
            }
        });

        // Calculate components based on essence and quantity
        function calculateComponents(essenceCode, quantity) {
            $.ajax({
                url: 'api_islemleri/esans_is_emirleri_islemler.php?action=calculate_components',
                type: 'POST',
                data: {
                    essence_code: essenceCode,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var components = response.data;
                        if (components.length > 0) {
                            var tbody = $('#componentsTableBody');
                            tbody.empty();
                            
                            $.each(components, function(index, component) {
                                // Calculate required amount: original_component_amount * user_quantity
                                var requiredAmount = parseFloat(component.bilesen_miktari) * parseFloat(quantity);
                                
                                var row = `
                                    <tr>
                                        <td>${component.bilesen_kodu}</td>
                                        <td>${component.bilesen_ismi}</td>
                                        <td>${component.bilesenin_malzeme_turu}</td>
                                        <td>${requiredAmount.toFixed(2)}</td>
                                        <td>${component.bilesen_miktari}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                            
                            $('#componentsPlaceholder').hide();
                        } else {
                            $('#componentsTableBody').empty();
                            $('#componentsPlaceholder').html('Bu esans icin urun agacinda bilesen tanimlanmamis. <br> Lutfen <a href="urun_agaclari.php" target="_blank">Urun Agaclari</a> sayfasindan ilgili esans icin bilesenleri ekleyin.').show();
                        }
                    } else {
                        $('#componentsTableBody').empty();
                        $('#componentsPlaceholder').text('Bilesenler getirilirken bir hata olustu: ' + response.message).show();
                    }
                },
                error: function() {
                    $('#componentsTableBody').empty();
                    $('#componentsPlaceholder').text('Bilesenler getirilirken bir hata olustu.').show();
                }
            });
        }

        // Open modal for editing a work order
        $('.edit-btn').on('click', function() {
            var workOrderId = $(this).data('id');
            
            $.ajax({
                url: 'api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order&id=' + workOrderId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var workOrder = response.data;
                        $('#workOrderForm')[0].reset();
                        $('#modalTitle').text('Esans Is Emrini Duzenle');
                        $('#action').val('update_work_order');
                        $('#is_emri_numarasi').val(workOrder.is_emri_numarasi);
                        $('#esans_kodu').val(workOrder.esans_kodu);
                        $('#planlanan_miktar').val(workOrder.planlanan_miktar);
                        $('#birim').val(workOrder.birim);
                        $('#tank_kodu').val(workOrder.tank_kodu);
                        $('#planlanan_baslangic_tarihi').val(workOrder.planlanan_baslangic_tarihi);
                        $('#demlenme_suresi_gun').val(workOrder.demlenme_suresi_gun);
                        $('#planlanan_bitis_tarihi').val(workOrder.planlanan_bitis_tarihi);
                        $('#durum').val(workOrder.durum);
                        $('#aciklama').val(workOrder.aciklama);
                        
                        // Calculate components for the selected essence and quantity
                        if(workOrder.esans_kodu && workOrder.planlanan_miktar) {
                            calculateComponents(workOrder.esans_kodu, workOrder.planlanan_miktar);
                        }
                        
                        $('#durum').prop('disabled', false); // Enable status dropdown for editing
                        $('#submitBtn').text('Guncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#workOrderModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Esans is emri bilgileri alinirken bir hata olustu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#workOrderForm').on('submit', function(e) {
            e.preventDefault();
            
            // Determine if creating or updating
            var isCreating = ($('#action').val() === 'add_work_order');
            
            // Create form data object
            var formData = {
                action: $('#action').val(),
                work_order: {
                    is_emri_numarasi: $('#is_emri_numarasi').val(),
                    olusturulma_tarihi: new Date().toISOString().split('T')[0], // Use current date
                    olusturan: '<?php echo $_SESSION["kullanici_adi"] ?? "Sistem"; ?>',
                    esans_kodu: $('#esans_kodu').val(),
                    esans_ismi: $('#esans_kodu option:selected').text().split(' - ')[1] || $('#esans_kodu option:selected').text(),
                    tank_kodu: $('#tank_kodu').val(),
                    tank_ismi: $('#tank_kodu option:selected').text().split(' - ')[1],
                    planlanan_miktar: $('#planlanan_miktar').val(),
                    birim: $('#birim').val(),
                    planlanan_baslangic_tarihi: $('#planlanan_baslangic_tarihi').val(),
                    demlenme_suresi_gun: $('#demlenme_suresi_gun').val(),
                    planlanan_bitis_tarihi: $('#planlanan_bitis_tarihi').val(),
                    aciklama: $('#aciklama').val(),
                    durum: isCreating ? 'olusturuldu' : $('#durum').val(), // Always use 'olusturuldu' for new work orders
                    tamamlanan_miktar: 0, // Default value
                    eksik_miktar_toplami: 0 // Default value
                }
            };
            
            // Get the calculated components data
            var components = [];
            $('#componentsTableBody tr').each(function() {
                var $row = $(this);
                components.push({
                    malzeme_kodu: $row.find('td:eq(0)').text(),
                    malzeme_ismi: $row.find('td:eq(1)').text(),
                    malzeme_turu: $row.find('td:eq(2)').text(),
                    miktar: $row.find('td:eq(3)').text(),
                    birim: $row.find('td:eq(4)').text()
                });
            });
            
            formData.components = components;

            $.ajax({
                url: 'api_islemleri/esans_is_emirleri_islemler.php',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#workOrderModal').modal('hide');
                        showAlert(response.message, 'success');
                        // Reload page to see changes
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Islem sirasinda bir hata olustu.', 'danger');
                }
            });
        });

        // Handle work order deletion
        $(document).on('click', '.delete-btn', function() {
            var workOrderId = $(this).data('id');
            
            if (confirm('Bu esans is emrini silmek istediginizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/esans_is_emirleri_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_work_order',
                        id: workOrderId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Silme islemi sirasinda bir hata olustu.', 'danger');
                    }
                });
            }
        });

        // Handle start work order
        $(document).on('click', '.start-btn', function() {
            var workOrderId = $(this).data('id');

            // First, get the list of components to show in the confirmation dialog
            $.ajax({
                url: 'api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order_components&id=' + workOrderId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var components = response.data;
                        var confirmationMessage = 'Bu is emrini baslatmak istediginizden emin misiniz?\n\n';

                        if (components && components.length > 0) {
                            confirmationMessage += 'Asagidaki malzemeler stoktan dusulecektir:\n';
                            $.each(components, function(index, component) {
                                confirmationMessage += ` - ${component.malzeme_ismi}: ${parseFloat(component.miktar).toFixed(2)} ${component.birim}\n`;
                            });
                        } else {
                            confirmationMessage += 'Bu is emri icin stoktan dusulecek malzeme bulunmuyor.';
                        }

                        if (confirm(confirmationMessage)) {
                            // If confirmed, proceed to start the work order
                            $.ajax({
                                url: 'api_islemleri/esans_is_emirleri_islemler.php',
                                type: 'POST',
                                data: {
                                    action: 'start_work_order',
                                    id: workOrderId
                                },
                                dataType: 'json',
                                success: function(startResponse) {
                                    if (startResponse.status === 'success') {
                                        showAlert(startResponse.message, 'success');
                                        setTimeout(function() {
                                            location.reload();
                                        }, 1000);
                                    } else {
                                        showAlert(startResponse.message, 'danger');
                                    }
                                },
                                error: function() {
                                    showAlert('Is emri baslatilirken bir hata olustu.', 'danger');
                                }
                            });
                        }
                    } else {
                        showAlert('Onay icin bilesen listesi alinamadi: ' + response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Onay icin bilesen listesi alinirken bir sunucu hatasi olustu.', 'danger');
                }
            });
        });

        $(document).on('click', '.revert-btn', function() {
            var workOrderId = $(this).data('id');
            
            if (confirm('Bu is emrini durdurup "Olusturuldu" durumuna geri almak istediginizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/esans_is_emirleri_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'revert_work_order',
                        id: workOrderId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Islem sirasinda bir hata olustu.', 'danger');
                    }
                });
            }
        });

        // Handle complete button click to open modal
        $(document).on('click', '.complete-btn', function() {
            var workOrderId = $(this).data('id');

            $.ajax({
                url: 'api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order&id=' + workOrderId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var workOrder = response.data;
                        $('#complete_wo_id').text(workOrder.is_emri_numarasi);
                        $('#complete_wo_essence').text(workOrder.esans_kodu + ' - ' + workOrder.esans_ismi);
                        $('#complete_wo_planned_qty').text(parseFloat(workOrder.planlanan_miktar).toFixed(2) + ' ' + workOrder.birim);
                        
                        $('#complete_is_emri_numarasi').val(workOrder.is_emri_numarasi);
                        $('#tamamlanan_miktar').val(parseFloat(workOrder.planlanan_miktar).toFixed(2));

                        $('#completeWorkOrderModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Is emri detaylari alinirken bir hata olustu.', 'danger');
                }
            });
        });

        // Handle the submission of the complete work order form
        $('#completeWorkOrderForm').on('submit', function(e) {
            e.preventDefault();

            var formData = {
                action: 'complete_work_order',
                is_emri_numarasi: $('#complete_is_emri_numarasi').val(),
                tamamlanan_miktar: $('#tamamlanan_miktar').val(),
                aciklama: $('#tamamlama_aciklama').val()
            };

            $.ajax({
                url: 'api_islemleri/esans_is_emirleri_islemler.php',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#completeWorkOrderModal').modal('hide');
                        showAlert(response.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        // Show error inside the modal
                        alert('Hata: ' + response.message);
                    }
                },
                error: function() {
                    alert('Islem sirasinda sunucu taraflı bir hata olustu.');
                }
            });
        });

        // Handle revert completion
        $(document).on('click', '.revert-completion-btn', function() {
            var workOrderId = $(this).data('id');
            
            if (confirm('Bu is emrinin tamamlanma durumunu geri almak istediginizden emin misiniz? Bu islem ilgili stok hareketlerini tersine cevirecektir.')) {
                $.ajax({
                    url: 'api_islemleri/esans_is_emirleri_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'revert_completion',
                        id: workOrderId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Geri alma islemi sirasinda bir hata olustu.', 'danger');
                    }
                });
            }
        });
        
        // Handle info button click
        $('#infoButton').on('click', function() {
            $('#infoModal').modal('show');
        });
    });
    </script>
</body>
</html>

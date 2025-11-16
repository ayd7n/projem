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

// Page-level permission check
if (!yetkisi_var('page:view:esans_is_emirleri')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
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
    <link rel="stylesheet" href="assets/css/stil.css?v=1.2">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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

    <div id="app">
        <div v-if="loading" class="d-flex justify-content-center align-items-center" style="height: 50vh;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Yükleniyor...</span>
                </div>
                <p class="mt-3 h5">Veriler yükleniyor...</p>
            </div>
        </div>
        <div v-else-if="!showContent && !loading" class="d-flex justify-content-center align-items-center" style="height: 50vh;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Hazırlanıyor...</span>
                </div>
                <p class="mt-3 h5">Sayfa hazırlanıyor...</p>
            </div>
        </div>
        <div v-else>

    <!-- Information Modal -->
    <div class="modal fade" :class="{show: showInfoModal}" v-if="showInfoModal" style="display: block; background-color: rgba(0,0,0,0.5);" @click="showInfoModal = false">
        <div class="modal-dialog modal-xl" @click.stop role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(45deg, var(--primary), var(--secondary)); color: white;">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> Esans İş Emirleri Sistemi Bilgileri</h5>
                    <button type="button" class="close text-white" @click="showInfoModal = false" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
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
                    <button type="button" class="btn btn-secondary" @click="showInfoModal = false"><i class="fas fa-times"></i> Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-flask"></i> Esans Is Emirleri</h1>
                <p>Esans uretim is emirlerini olusturun, duzenleyin ve takip edin</p>
            </div>
        </div>

        <div v-if="alertMessage" :class="`alert alert-${alertType} alert-dismissible fade show`" role="alert">
            <i :class="alertType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
            {{ alertMessage }}
            <button type="button" class="close" @click="closeAlert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php if (yetkisi_var('action:esans_is_emirleri:create')): ?>
                <button @click="openAddModal" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Esans Is Emri Olustur</button>
                <?php endif; ?>
                <button @click="showInfoModal = true" class="btn btn-info mb-3 ml-2"><i class="fas fa-info-circle"></i> Bilgi</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
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
                            <tr v-if="loading">
                                <td colspan="17" class="text-center p-4">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="spinner-border text-primary" role="status" style="width: 1.5rem; height: 1.5rem;">
                                            <span class="sr-only">Yükleniyor...</span>
                                        </div>
                                        <span class="ml-2">Veriler yükleniyor...</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else v-for="workOrder in workOrders" :key="workOrder.is_emri_numarasi">
                                <td class="actions">
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle btn-gradient" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-cogs"></i> Islemler
                                        </button>
                                        <div class="dropdown-menu">
                                            <?php if (yetkisi_var('action:esans_is_emirleri:start')): ?>
                                            <button 
                                                v-if="workOrder && workOrder.durum === 'olusturuldu'" 
                                                @click="startWorkOrder(workOrder.is_emri_numarasi)" 
                                                class="dropdown-item" 
                                                title="Is Emrini Baslat">
                                                <i class="fas fa-play text-success"></i> Is Emrini Baslat
                                            </button>
                                            <?php endif; ?>
                                            <?php if (yetkisi_var('action:esans_is_emirleri:complete')): ?>
                                            <button 
                                                v-if="workOrder && workOrder.durum === 'uretimde'" 
                                                @click="openCompleteModal(workOrder.is_emri_numarasi)" 
                                                class="dropdown-item" 
                                                title="Is Emrini Tamamla">
                                                <i class="fas fa-check-square text-success"></i> Is Emrini Tamamla
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if (yetkisi_var('action:esans_is_emirleri:edit')): ?>
                                            <button 
                                                @click="openEditModal(workOrder.is_emri_numarasi)" 
                                                v-if="workOrder && workOrder.durum !== 'tamamlandi' && workOrder.durum !== 'iptal'" 
                                                class="dropdown-item" 
                                                title="Duzenle">
                                                <i class="fas fa-edit text-primary"></i> Duzenle
                                            </button>
                                            <?php endif; ?>
                                            <button 
                                                @click="showWorkOrderDetails(workOrder.is_emri_numarasi)" 
                                                class="dropdown-item" 
                                                title="Detaylar">
                                                <i class="fas fa-list text-info"></i> Detaylar
                                            </button>
                                            <button 
                                                @click="printWorkOrder(workOrder.is_emri_numarasi)" 
                                                class="dropdown-item" 
                                                title="Yazdır">
                                                <i class="fas fa-print text-secondary"></i> Yazdir
                                            </button>
                                            <?php if (yetkisi_var('action:esans_is_emirleri:edit')): ?>
                                            <button 
                                                @click="revertWorkOrder(workOrder.is_emri_numarasi)" 
                                                v-if="workOrder && workOrder.durum === 'uretimde'" 
                                                class="dropdown-item" 
                                                title="Uretimi Durdur/Geri Al">
                                                <i class="fas fa-undo text-warning"></i> Uretimi Durdur/Geri Al
                                            </button>
                                            <?php endif; ?>
                                            <?php if (yetkisi_var('action:esans_is_emirleri:delete')): ?>
                                            <button 
                                                @click="deleteWorkOrder(workOrder.is_emri_numarasi)" 
                                                v-if="workOrder && workOrder.durum !== 'tamamlandi'" 
                                                class="dropdown-item text-danger" 
                                                title="Sil">
                                                <i class="fas fa-trash"></i> Sil
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                <td><strong>{{ workOrder.is_emri_numarasi }}</strong></td>
                                <td>
                                    <span :class="`badge badge-${workOrder.durum === 'olusturuldu' ? 'secondary' : (workOrder.durum === 'uretimde' ? 'warning' : (workOrder.durum === 'tamamlandi' ? 'success' : 'danger'))}`">
                                        {{ workOrder.durum === 'olusturuldu' ? 'Olusturuldu' : (workOrder.durum === 'uretimde' ? 'Uretimde' : (workOrder.durum === 'tamamlandi' ? 'Tamamlandi' : 'Iptal')) }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap;"><div><strong>{{ workOrder.esans_kodu }} - {{ workOrder.esans_ismi }}</strong></div></td>
                                <td style="white-space: nowrap;">{{ workOrder.tank_kodu }} - {{ workOrder.tank_ismi }}</td>
                                <td>{{ parseFloat(workOrder.planlanan_miktar).toFixed(2) }}</td>
                                <td>{{ parseFloat(workOrder.tamamlanan_miktar).toFixed(2) }}</td>
                                <td>{{ parseFloat(workOrder.eksik_miktar_toplami).toFixed(2) }}</td>
                                <td>{{ workOrder.birim }}</td>
                                <td>{{ workOrder.olusturulma_tarihi }}</td>
                                <td>{{ workOrder.olusturan }}</td>
                                <td>{{ workOrder.planlanan_baslangic_tarihi }}</td>
                                <td>{{ workOrder.planlanan_bitis_tarihi }}</td>
                                <td>{{ workOrder.gerceklesen_baslangic_tarihi }}</td>
                                <td>{{ workOrder.gerceklesen_bitis_tarihi }}</td>
                                <td>{{ workOrder.demlenme_suresi_gun }}</td>
                                <td style="white-space: nowrap;">{{ workOrder.aciklama }}</td>
                            </tr>
                            <tr v-if="!loading && workOrders.length === 0">
                                <td colspan="17" class="text-center p-4">Henüz kayitli esans is emri bulunmuyor.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Controls -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                    <div class="pagination-info mb-2 mb-md-0">
                        <small>
                            Sayfa {{ pagination.current_page }} / {{ pagination.total_pages }} 
                            (Toplam {{ pagination.total }} kayıt)
                        </small>
                    </div>
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <label for="per-page-select" class="mr-2 mb-0" style="white-space: nowrap;">Sayfa başına:</label>
                        <select id="per-page-select" class="form-control" 
                                v-model="pagination.per_page" @change="changePerPage">
                            <option :value="1">1</option>
                            <option :value="2">2</option>
                            <option :value="5">5</option>
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{disabled: pagination.current_page <= 1}">
                                <button class="page-link" @click="goToFirstPage()" :disabled="pagination.current_page <= 1">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                            </li>
                            <li class="page-item" :class="{disabled: pagination.current_page <= 1}">
                                <button class="page-link" @click="goToPreviousPage()" :disabled="pagination.current_page <= 1">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                            </li>
                            <li class="page-item active">
                                <span class="page-link">
                                    {{ pagination.current_page }}
                                </span>
                            </li>
                            <li class="page-item" :class="{disabled: pagination.current_page >= pagination.total_pages}">
                                <button class="page-link" @click="goToNextPage()" :disabled="pagination.current_page >= pagination.total_pages">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                            </li>
                            <li class="page-item" :class="{disabled: pagination.current_page >= pagination.total_pages}">
                                <button class="page-link" @click="goToLastPage()" :disabled="pagination.current_page >= pagination.total_pages">
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Order Modal -->
    <div class="modal fade" :class="{show: showModal}" v-if="showModal" style="display: block; background-color: rgba(0,0,0,0.5);" @click="closeModal">
        <div class="modal-dialog modal-xl" @click.stop>
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                    <h5 class="modal-title"><i class="fas fa-bolt"></i> {{ modalTitle }}</h5>
                    <button type="button" class="close text-white" @click="closeModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="esans_kodu">Esans *</label>
                                        <select class="form-control" v-model="selectedWorkOrder.esans_kodu" @change="updateEssenceDetails" required>
                                            <option value="">Esans Secin</option>
                                            <option v-for="essence in essences" :value="essence.esans_kodu" :key="essence.esans_kodu">
                                                {{ essence.esans_kodu }} - {{ essence.esans_ismi }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="planlanan_miktar">Planlanan Miktar *</label>
                                        <input type="number" step="0.01" class="form-control" v-model.number="selectedWorkOrder.planlanan_miktar" @input="calculateComponents" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="birim">Birim</label>
                                        <input type="text" class="form-control" v-model="selectedWorkOrder.birim" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tank_kodu">Tank *</label>
                                        <select class="form-control" v-model="selectedWorkOrder.tank_kodu" @change="updateTankName" required>
                                            <option value="">Tank Secin</option>
                                            <option v-for="tank in tanks" :value="tank.tank_kodu" :key="tank.tank_kodu">
                                                {{ tank.tank_kodu }} - {{ tank.tank_ismi }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="planlanan_baslangic_tarihi">Planlanan Baslangic Tarihi *</label>
                                        <input type="date" class="form-control" v-model="selectedWorkOrder.planlanan_baslangic_tarihi" @change="updateEndDate" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="demlenme_suresi_gun">Demlenme Suresi (Gun)</label>
                                        <input type="number" class="form-control" v-model.number="selectedWorkOrder.demlenme_suresi_gun" @input="updateEndDate">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="planlanan_bitis_tarihi">Planlanan Bitis Tarihi</label>
                                        <input type="date" class="form-control" v-model="selectedWorkOrder.planlanan_bitis_tarihi" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="durum">Durum</label>
                                        <select class="form-control" v-model="selectedWorkOrder.durum" :disabled="selectedWorkOrder.is_emri_numarasi">
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
                                <textarea class="form-control" v-model="selectedWorkOrder.aciklama" rows="2"></textarea>
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
                                            <tbody>
                                                <tr v-for="(component, index) in calculatedComponents" :key="index">
                                                    <td>{{ component.malzeme_kodu }}</td>
                                                    <td>{{ component.malzeme_ismi }}</td>
                                                    <td>{{ component.malzeme_turu }}</td>
                                                    <td>{{ component.miktar }}</td>
                                                    <td>{{ component.birim }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div v-if="calculatedComponents.length === 0" class="text-center py-3 text-muted">
                                        Esans ve miktar secildiginde bilesenler gosterilecektir.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeModal"><i class="fas fa-times"></i> Iptal</button>
                    <button type="button" class="btn btn-primary" @click="saveWorkOrder"><i class="fas fa-save"></i> {{ submitButtonText }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Materials Details Modal -->
    <div class="modal fade" :class="{show: showDetailsModal}" v-if="showDetailsModal" style="display: block; background-color: rgba(0,0,0,0.5);" @click="showDetailsModal = false">
        <div class="modal-dialog modal-lg" @click.stop>
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                    <h5 class="modal-title"><i class="fas fa-cubes"></i> İş Emri No: {{ selectedWorkOrderId }} Malzeme Detaylari</h5>
                    <button type="button" class="close text-white" @click="showDetailsModal = false" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
                            <tbody>
                                <tr v-for="(component, index) in workOrderComponents" :key="index">
                                    <td>{{ component.malzeme_kodu }}</td>
                                    <td>{{ component.malzeme_ismi }}</td>
                                    <td>{{ component.malzeme_turu }}</td>
                                    <td>{{ parseFloat(component.miktar).toFixed(2) }}</td>
                                    <td>{{ component.birim }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="workOrderComponents.length === 0" class="text-center py-3 text-muted">
                        Malzeme detaylari yukleniyor...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showDetailsModal = false"><i class="fas fa-times"></i> Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Work Order Modal -->
    <div class="modal fade" :class="{show: showCompleteModal}" v-if="showCompleteModal" style="display: block; background-color: rgba(0,0,0,0.5);" @click="showCompleteModal = false">
        <div class="modal-dialog" @click.stop>
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-square"></i> Is Emrini Tamamla</h5>
                    <button type="button" class="close text-white" @click="showCompleteModal = false" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Is Emri Bilgileri</h5>
                    <p><strong>Is Emri No:</strong> {{ selectedWorkOrder.is_emri_numarasi }}</p>
                    <p><strong>Esans:</strong> {{ selectedWorkOrder.esans_kodu }} - {{ selectedWorkOrder.esans_ismi }}</p>
                    <p><strong>Planlanan Miktar:</strong> {{ parseFloat(selectedWorkOrder.planlanan_miktar).toFixed(2) }} {{ selectedWorkOrder.birim }}</p>
                    <hr>
                    <div class="form-group">
                        <label for="tamamlanan_miktar"><strong>Gerceklese Miktar *</strong></label>
                        <input type="number" step="0.01" class="form-control" v-model.number="selectedWorkOrder.tamamlanan_miktar" @input="calculateMissingAmount" required>
                    </div>
                    <div class="form-group">
                        <label for="eksik_miktar"><strong>Kalan (Eksik) Miktar</strong></label>
                        <input type="number" step="0.01" class="form-control" :value="selectedWorkOrder.eksik_miktar_toplami.toFixed(2)" readonly>
                        <small class="form-text text-muted">Planlanan miktardan gerçekleşen miktar çıkarılarak hesaplanmıştır</small>
                    </div>
                    <div class="form-group">
                        <label for="aciklama">Aciklama</label>
                        <textarea class="form-control" v-model="selectedWorkOrder.aciklama" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showCompleteModal = false"><i class="fas fa-times"></i> Iptal</button>
                    <button type="button" class="btn btn-success" @click="completeWorkOrder"><i class="fas fa-save"></i> Uretimi Tamamla ve Stoklari Guncelle</button>
                </div>
            </div>
        </div>
    </div>

    </div> <!-- Close the v-else div -->
    </div> <!-- Close the #app div -->

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Vue.js and Axios -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Include the Vue.js application -->
    <script src="assets/js/esans_is_emirleri.js"></script>
    
    <script>
        // Define user info for the Vue app
        window.kullaniciBilgisi = {
            kullaniciAdi: '<?php echo $_SESSION["kullanici_adi"] ?? "Sistem"; ?>'
        };
    </script>
</body>
</html>

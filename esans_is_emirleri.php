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
    <title>Esans İş Emirleri - Parfüm ERP</title>
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
                        <a class="nav-link" href="change_password.php">Parolamı Değiştir</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
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
                <h1><i class="fas fa-flask"></i> Esans İş Emirleri</h1>
                <p>Esans üretim iş emirlerini oluşturun, düzenleyin ve takip edin</p>
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addWorkOrderBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Esans İş Emri Oluştur</button>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon" style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.5rem; margin: 0;"><?php echo $total_work_orders; ?></h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam İş Emri</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title"><i class="fas fa-info-circle text-primary"></i> Esans İş Emri Tanımı</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-flask text-success"></i> Esans Üretimi</h5>
                        <ul class="list-unstyled ml-3">
                            <li><i class="fas fa-check-circle text-success"></i> Esans üretimi için iş emri oluşturun</li>
                            <li><i class="fas fa-check-circle text-success"></i> Esans bileşenleri otomatik hesaplanır</li>
                            <li><i class="fas fa-check-circle text-success"></i> Planlama tarihlerini belirleyin</li>
                            <li><i class="fas fa-lightbulb text-warning"></i> <strong>Örnek:</strong> 100 litre gül esansı için bileşenleri otomatik hesapla</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-cogs text-info"></i> İşlem Akışı</h5>
                        <ul class="list-unstyled ml-3">
                            <li><i class="fas fa-check-circle text-success"></i> Esans seçimi ile başlar</li>
                            <li><i class="fas fa-check-circle text-success"></i> Üretim miktarını girin</li>
                            <li><i class="fas fa-check-circle text-success"></i> Gerekli malzemeler otomatik hesaplanır</li>
                            <li><i class="fas fa-check-circle text-success"></i> Tank ve tarih planlaması yapın</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-list"></i> Esans İş Emirleri Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-hashtag"></i> İş Emri No</th>
                                <th><i class="fas fa-calendar"></i> Oluşturulma Tarihi</th>
                                <th><i class="fas fa-flask"></i> Esans</th>
                                <th><i class="fas fa-weight"></i> Planlanan Miktar</th>
                                <th><i class="fas fa-ruler"></i> Birim</th>
                                <th><i class="fas fa-hourglass-half"></i> Başlangıç Tarihi</th>
                                <th><i class="fas fa-flag-checkered"></i> Bitiş Tarihi</th>
                                <th><i class="fas fa-tint"></i> Tank</th>
                                <th><i class="fas fa-info-circle"></i> Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($work_orders_result && $work_orders_result->num_rows > 0): ?>
                                <?php while ($work_order = $work_orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-info btn-sm details-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>">
                                                <i class="fas fa-list"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $work_order['is_emri_numarasi']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                        <td><?php echo $work_order['is_emri_numarasi']; ?></td>
                                        <td><?php echo $work_order['olusturulma_tarihi']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($work_order['esans_kodu'] . ' - ' . $work_order['esans_ismi']); ?></strong></td>
                                        <td><?php echo number_format($work_order['planlanan_miktar'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($work_order['birim']); ?></td>
                                        <td><?php echo $work_order['planlanan_baslangic_tarihi']; ?></td>
                                        <td><?php echo $work_order['planlanan_bitis_tarihi']; ?></td>
                                        <td><?php echo htmlspecialchars($work_order['tank_kodu'] . ' - ' . $work_order['tank_ismi']); ?></td>
                                        <td>
                                            <span class="status-badge badge-<?php echo $work_order['durum'] === 'olusturuldu' ? 'secondary' : ($work_order['durum'] === 'uretimde' ? 'warning' : ($work_order['durum'] === 'tamamlandi' ? 'success' : 'danger')); ?>">
                                                <?php 
                                                if($work_order['durum'] === 'olusturuldu') echo 'Oluşturuldu';
                                                elseif($work_order['durum'] === 'uretimde') echo 'Üretimde';
                                                elseif($work_order['durum'] === 'tamamlandi') echo 'Tamamlandı';
                                                else echo 'İptal';
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center p-4">Henüz kayıtlı esans iş emri bulunmuyor.</td>
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
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-bolt"></i> Esans İş Emri Formu</h5>
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
                                                <option value="">Esans Seçin</option>
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
                                                <option value="">Tank Seçin</option>
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
                                            <label for="planlanan_baslangic_tarihi">Planlanan Başlangıç Tarihi *</label>
                                            <input type="date" class="form-control" id="planlanan_baslangic_tarihi" name="planlanan_baslangic_tarihi" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="demlenme_suresi_gun">Demlenme Süresi (Gün)</label>
                                            <input type="number" class="form-control" id="demlenme_suresi_gun" name="demlenme_suresi_gun">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="planlanan_bitis_tarihi">Planlanan Bitiş Tarihi</label>
                                            <input type="date" class="form-control" id="planlanan_bitis_tarihi" name="planlanan_bitis_tarihi" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="durum">Durum</label>
                                            <select class="form-control" id="durum" name="durum">
                                                <option value="olusturuldu">Oluşturuldu</option>
                                                <option value="uretimde">Üretimde</option>
                                                <option value="tamamlandi">Tamamlandı</option>
                                                <option value="iptal">İptal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="aciklama">Açıklama</label>
                                    <textarea class="form-control" id="aciklama" name="aciklama" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <!-- Calculated Components Section -->
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5><i class="fas fa-cubes"></i> Hesaplanan Bileşenler</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Malzeme Kodu</th>
                                                        <th>Malzeme İsmi</th>
                                                        <th>Malzeme Türü</th>
                                                        <th>Gerekli Miktar</th>
                                                        <th>Bileşim Oranı</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="componentsTableBody">
                                                    <!-- Components will be populated here via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="componentsPlaceholder" class="text-center py-3 text-muted">
                                            Esans ve miktar seçildiğinde bileşenler gösterilecektir.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> İptal</button>
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
                    <h5 class="modal-title"><i class="fas fa-cubes"></i> <span id="materialsModalTitle">Malzeme Detayları</span></h5>
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
                                        <th>Malzeme İsmi</th>
                                        <th>Malzeme Türü</th>
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
                            Malzeme detayları yükleniyor...
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Kapat</button>
                </div>
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
            $('#modalTitle').text('Yeni Esans İş Emri Oluştur');
            $('#action').val('create_work_order');
            $('#submitBtn').text('Oluştur').removeClass('btn-success').addClass('btn-primary');
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
                            $('#componentsPlaceholder').html('Bu esans için ürün ağacında bileşen tanımlanmamış. <br> Lütfen <a href="urun_agaclari.php" target="_blank">Ürün Ağaçları</a> sayfasından ilgili esans için bileşenleri ekleyin.').show();
                        }
                    } else {
                        $('#componentsTableBody').empty();
                        $('#componentsPlaceholder').text('Bileşenler getirilirken bir hata oluştu: ' + response.message).show();
                    }
                },
                error: function() {
                    $('#componentsTableBody').empty();
                    $('#componentsPlaceholder').text('Bileşenler getirilirken bir hata oluştu.').show();
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
                        $('#modalTitle').text('Esans İş Emrini Düzenle');
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
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#workOrderModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Esans iş emri bilgileri alınırken bir hata oluştu.', 'danger');
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
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle work order deletion
        $('.delete-btn').on('click', function() {
            var workOrderId = $(this).data('id');
            
            if (confirm('Bu esans iş emrini silmek istediğinizden emin misiniz?')) {
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
                        showAlert('Silme işlemi sırasında bir hata oluştu.', 'danger');
                    }
                });
            }
        });
        
        // Handle materials details button click
        $(document).on('click', '.details-btn', function() {
            var workOrderId = $(this).data('id');
            
            // Show the modal and set loading state
            $('#materialsModalTitle').text('Malzeme Detayları - İş Emri #' + workOrderId);
            $('#materialsDetailsTableBody').empty();
            $('#materialsDetailsPlaceholder').text('Malzeme detayları yükleniyor...').show();
            $('#materialsDetailsModal').modal('show');
            
            // Fetch materials details for this work order
            $.ajax({
                url: 'api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order_components&id=' + workOrderId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var components = response.data;
                        if (components && components.length > 0) {
                            var tbody = $('#materialsDetailsTableBody');
                            tbody.empty();
                            
                            $.each(components, function(index, component) {
                                var row = `
                                    <tr>
                                        <td>${component.malzeme_kodu}</td>
                                        <td>${component.malzeme_ismi}</td>
                                        <td>${component.malzeme_turu}</td>
                                        <td>${component.miktar}</td>
                                        <td>${component.birim}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                            
                            $('#materialsDetailsPlaceholder').hide();
                        } else {
                            $('#materialsDetailsTableBody').empty();
                            $('#materialsDetailsPlaceholder').text('Bu iş emri için tanimli malzeme bulunmamaktadır.').show();
                        }
                    } else {
                        $('#materialsDetailsTableBody').empty();
                        $('#materialsDetailsPlaceholder').text('Malzeme detayları alınırken bir hata oluştu: ' + response.message).show();
                    }
                },
                error: function() {
                    $('#materialsDetailsTableBody').empty();
                    $('#materialsDetailsPlaceholder').text('Malzeme detayları alınırken bir hata oluştu.').show();
                }
            });
        });
    });
    </script>
</body>
</html>
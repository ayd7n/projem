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

// Fetch all stock movements for display
$movement_query = "SELECT * FROM stok_hareket_kayitlari ORDER BY tarih DESC LIMIT 100";
$movement_result = $connection->query($movement_query);

// Calculate total movements
$total_result = $connection->query("SELECT COUNT(*) as total FROM stok_hareket_kayitlari");
$total_movements = $total_result->fetch_assoc()['total'] ?? 0;

// Fetch all locations for dropdown
$locations_query = "SELECT * FROM lokasyonlar ORDER BY depo_ismi, raf";
$locations_result = $connection->query($locations_query);

// Fetch all tanks for dropdown
$tanks_query = "SELECT * FROM tanklar ORDER BY tank_ismi";
$tanks_result = $connection->query($tanks_query);

// Fetch all materials for dropdown
$materials_query = "SELECT * FROM malzemeler ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);

// Fetch all essences for dropdown
$essences_query = "SELECT * FROM esanslar ORDER BY esans_ismi";
$essences_result = $connection->query($essences_query);

// Fetch all products for dropdown
$products_query = "SELECT * FROM urunler ORDER BY urun_ismi";
$products_result = $connection->query($products_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Manuel Stok Hareket - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="css/stil.css">
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
                <h1>Manuel Stok Hareket Yönetimi</h1>
                <p>Manuel olarak stok hareketlerini kaydedin ve yönetin</p>
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <!-- "Yeni Stok Hareketi Ekle" butonu kaldırıldı -->
                <button class="btn btn-primary" id="transferButton"><i class="fas fa-exchange-alt"></i> Yeni Stok Transferi</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_movements; ?></h3>
                        <p>Toplam Stok Hareketi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-table"></i> Stok Hareketleri Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-calendar"></i> Tarih</th>
                                <th><i class="fas fa-tag"></i> Stok Türü</th>
                                <th><i class="fas fa-barcode"></i> Kod</th>
                                <th><i class="fas fa-tag"></i> İsim</th>
                                <th><i class="fas fa-ruler-vertical"></i> Birim</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Miktar</th>
                                <th><i class="fas fa-exchange-alt"></i> Yön</th>
                                <th><i class="fas fa-tasks"></i> Hareket Türü</th>
                                <th><i class="fas fa-warehouse"></i> Depo</th>
                                <th><i class="fas fa-cubes"></i> Raf</th>
                                <th><i class="fas fa-oil-can"></i> Tank Kodu</th>
                                <th><i class="fas fa-file-invoice"></i> Belge No</th>
                                <th><i class="fas fa-industry"></i> İş Emri No</th>
                                <th><i class="fas fa-user"></i> Müşteri ID</th>
                                <th><i class="fas fa-user"></i> Müşteri Adı</th>
                                <th><i class="fas fa-comment"></i> Açıklama</th>
                                <th><i class="fas fa-user"></i> Kaydeden ID</th>
                                <th><i class="fas fa-user"></i> Kaydeden Adı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($movement_result && $movement_result->num_rows > 0): ?>
                                <?php while ($movement = $movement_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $movement['hareket_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $movement['hareket_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                        <td><strong><?php echo $movement['hareket_id']; ?></strong></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($movement['tarih'])); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                switch($movement['stok_turu']) {
                                                    case 'malzeme': echo 'badge-primary'; break;
                                                    case 'esans': echo 'badge-success'; break;
                                                    case 'urun': echo 'badge-info'; break;
                                                    default: echo 'badge-secondary'; break;
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($movement['stok_turu']); ?>
                                            </span>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($movement['kod']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($movement['isim']); ?></td>
                                        <td><?php echo htmlspecialchars($movement['birim']); ?></td>
                                        <td class="font-weight-bold"><?php echo number_format($movement['miktar'], 2); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                echo $movement['yon'] === 'giris' ? 'badge-success' : 'badge-danger';
                                                ?>">
                                                <?php 
                                                echo htmlspecialchars($movement['yon'] === 'giris' ? 'Giriş' : 'Çıkış');
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($movement['hareket_turu']); ?></td>
                                        <td><?php echo htmlspecialchars($movement['depo'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($movement['raf'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($movement['tank_kodu'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($movement['ilgili_belge_no']); ?></td>
                                        <td><?php echo htmlspecialchars($movement['is_emri_numarasi'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($movement['musteri_id'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($movement['musteri_adi'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($movement['aciklama']); ?></td>
                                        <td><?php echo htmlspecialchars($movement['kaydeden_personel_id'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($movement['kaydeden_personel_adi'] ?? '-'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="20" class="text-center p-4">
                                        <i class="fas fa-exchange-alt fa-3x mb-3" style="color: var(--text-secondary);"></i>
                                        <h4>Henüz Kayıtlı Stok Hareketi Bulunmuyor</h4>
                                        <p class="text-muted">Henüz hiç stok hareketi kaydedilmemiş.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement Modal -->
    <div class="modal fade" id="movementModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="movementForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Stok Hareketi Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="hareket_id" name="hareket_id">
                        <input type="hidden" id="action" name="action">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stok_turu">Stok Türü *</label>
                                    <select class="form-control" id="stok_turu" name="stok_turu" required>
                                        <option value="">Seçiniz</option>
                                        <option value="malzeme">Malzeme</option>
                                        <option value="urun">Ürün</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kod">Kod Seçin *</label>
                                    <select class="form-control" id="kod" name="kod" required>
                                        <option value="">Kod Seçin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="yon">Yön *</label>
                                    <select class="form-control" id="yon" name="yon" required>
                                        <option value="">Seçiniz</option>
                                        <option value="giris">Giriş</option>
                                        <option value="cikis">Çıkış</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hareket_turu">Hareket Türü *</label>
                                    <select class="form-control" id="hareket_turu" name="hareket_turu" required>
                                        <option value="">Seçiniz</option>
                                        <option value="stok_giris">Stok Girişi</option>
                                        <option value="stok_cikis">Stok Çıkışı</option>
                                        <option value="uretim">Üretim</option>
                                        <option value="uretimde_kullanim">Üretimde Kullanım</option>
                                        <option value="fire">Fire</option>
                                        <option value="sayim_farki">Sayım Farkı</option>
                                        <option value="stok_duzeltme">Stok Düzeltme</option>
                                        <option value="iade_girisi">İade Girişi</option>
                                        <option value="tedarikciye_iade">Tedarikçiye İade</option>
                                        <option value="numune_cikisi">Numune Çıkışı</option>
                                        <option value="montaj">Montaj</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="miktar">Miktar *</label>
                                    <input type="number" class="form-control" id="miktar" name="miktar" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ilgili_belge_no">İlgili Belge No</label>
                                    <input type="text" class="form-control" id="ilgili_belge_no" name="ilgili_belge_no">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="aciklama">Açıklama *</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3" required></textarea>
                        </div>
                        
                        <div id="location-fields">
                            <!-- Location fields will be shown/hidden based on stock type -->
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

    <!-- Stock Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="transferForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Stok Transferi Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="transfer_action" name="action" value="transfer_stock">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="transfer_stok_turu">Stok Türü *</label>
                                    <select class="form-control" id="transfer_stok_turu" name="stok_turu" required>
                                        <option value="">Seçiniz</option>
                                        <option value="malzeme">Malzeme</option>
                                        <option value="urun">Ürün</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="transfer_kod">Transfer Edilecek Ürün *</label>
                                    <select class="form-control" id="transfer_kod" name="kod" required>
                                        <option value="">Ürün Seçin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="transfer_miktar">Transfer Miktarı *</label>
                                    <input type="number" class="form-control" id="transfer_miktar" name="miktar" min="0.01" step="0.01" required readonly>
                                    <small class="form-text text-muted">Otomatik olarak mevcut stok miktarı atanacaktır</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="transfer_belge_no">Belge No</label>
                                    <input type="text" class="form-control" id="transfer_belge_no" name="ilgili_belge_no">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="transfer_aciklama">Açıklama *</label>
                            <textarea class="form-control" id="transfer_aciklama" name="aciklama" rows="3" required>Stok transferi</textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-md-6">
                                <h5>Kaynak Konum</h5>
                                <div id="source-location-fields">
                                    <!-- Source location fields will be shown/hidden based on stock type -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Hedef Konum</h5>
                                <div id="destination-location-fields">
                                    <!-- Destination location fields will be shown/hidden based on stock type -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> İptal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-exchange-alt"></i> Transfer Et</button>
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
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                // Toggle mobile menu functionality can be added here if needed
            });
        }

        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            $('#alert-placeholder').html(alertHtml);
            
            // Auto-hide success alerts after 5 seconds
            if (type === 'success') {
                setTimeout(function() {
                    $('.alert').fadeOut();
                }, 5000);
            }
        }

        // Load stock items based on type
        function loadStockItems(stockType) {
            $.ajax({
                url: 'api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=' + stockType,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var kodSelect = $('#kod');
                        kodSelect.empty();
                        kodSelect.append('<option value="">Kod Seçin</option>');

                        response.data.forEach(function(item) {
                            kodSelect.append(`<option value="${item.kod}">${item.kod} - ${item.isim} (Stok: ${item.stok})</option>`);
                        });
                    }
                },
                error: function() {
                    showAlert('Stok ürünleri yüklenirken bir hata oluştu.', 'danger');
                }
            });
        }

        // Update location fields based on stock type
        function updateLocationFields(stockType) {
            var locationDiv = $('#location-fields');

            if (stockType === 'malzeme' || stockType === 'urun') {
                locationDiv.html(`
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="depo">Depo *</label>
                                <select class="form-control" id="depo" name="depo" required>
                                    <option value="">Depo Seçin</option>
                                    <?php
                                    $locations_result->data_seek(0);
                                    while($location = $locations_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($location['depo_ismi']); ?>">
                                            <?php echo htmlspecialchars($location['depo_ismi']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="raf">Raf *</label>
                                <input type="text" class="form-control" id="raf" name="raf" required>
                            </div>
                        </div>
                    </div>
                `);
            } else {
                locationDiv.html('');
            }
        }

        // Stock type change handler
        $('#stok_turu').on('change', function() {
            var stockType = $(this).val();
            if (stockType) {
                loadStockItems(stockType);
                updateLocationFields(stockType);
            } else {
                $('#kod').empty().append('<option value="">Kod Seçin</option>');
                $('#location-fields').html('');
            }
        });

        // Update movement types based on direction
        function updateMovementTypes() {
            var direction = $('#yon').val();
            var movementTypeSelect = $('#hareket_turu');
            
            movementTypeSelect.empty();
            movementTypeSelect.append('<option value="">Seçiniz</option>');
            
            if (direction === 'giris') {
                movementTypeSelect.append('<option value="stok_giris">Stok Girişi</option>');
                movementTypeSelect.append('<option value="uretim">Üretim</option>');
                movementTypeSelect.append('<option value="iade_girisi">İade Girişi</option>');
                movementTypeSelect.append('<option value="sayim_farki">Sayım Farkı (Artış)</option>');
                movementTypeSelect.append('<option value="stok_duzeltme">Stok Düzeltme (Artış)</option>');
                movementTypeSelect.append('<option value="transfer">Transfer (Giriş)</option>');
            } else if (direction === 'cikis') {
                movementTypeSelect.append('<option value="stok_cikis">Stok Çıkışı</option>');
                movementTypeSelect.append('<option value="uretimde_kullanim">Üretimde Kullanım</option>');
                movementTypeSelect.append('<option value="fire">Fire</option>');
                movementTypeSelect.append('<option value="numune_cikisi">Numune Çıkışı</option>');
                movementTypeSelect.append('<option value="tedarikciye_iade">Tedarikçiye İade</option>');
                movementTypeSelect.append('<option value="montaj">Montaj</option>');
                movementTypeSelect.append('<option value="sayim_farki">Sayım Farkı (Azalış)</option>');
                movementTypeSelect.append('<option value="stok_duzeltme">Stok Düzeltme (Azalış)</option>');
                movementTypeSelect.append('<option value="transfer">Transfer (Çıkış)</option>');
            }
        }

        // Direction change handler
        $('#yon').on('change', function() {
            updateMovementTypes();
        });

        // Open modal for editing a movement
        $('.edit-btn').on('click', function() {
            var movementId = $(this).data('id');

            $.ajax({
                url: 'api_islemleri/stok_hareket_islemler.php?action=get_movement&id=' + movementId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var movement = response.data;
                        $('#movementForm')[0].reset();
                        $('#modalTitle').text('Stok Hareketini Düzenle');
                        $('#action').val('update_movement');
                        $('#hareket_id').val(movement.hareket_id);
                        $('#stok_turu').val(movement.stok_turu);
                        $('#kod').val(movement.kod);
                        $('#yon').val(movement.yon);
                        $('#hareket_turu').val(movement.hareket_turu);
                        $('#miktar').val(movement.miktar);
                        $('#ilgili_belge_no').val(movement.ilgili_belge_no);
                        $('#aciklama').val(movement.aciklama);

                        // Update location fields and load stock items
                        updateLocationFields(movement.stok_turu);
                        loadStockItems(movement.stok_turu);

                        // Set location values after a short delay to ensure fields are created
                        setTimeout(function() {
                            if (movement.depo) {
                                $('#depo').val(movement.depo);
                                $('#raf').val(movement.raf);
                            }
                            // Also update movement types for the loaded direction
                            updateMovementTypes();
                            // Set the correct movement type value after the options are loaded
                            $('#hareket_turu').val(movement.hareket_turu);
                        }, 100);

                        $('#submitBtn').html('<i class="fas fa-sync-alt"></i> Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#movementModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Stok hareketi bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#movementForm').on('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = $('#submitBtn');
            const originalText = submitBtn.html();
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> İşleniyor...').prop('disabled', true);

            var formData = $(this).serialize();

            $.ajax({
                url: 'api_islemleri/stok_hareket_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#movementModal').modal('hide');
                        showAlert(response.message, 'success');
                        // Reload page to see changes
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                },
                complete: function() {
                    // Restore button state
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Handle movement deletion
        $(document).on('click', '.delete-btn', function() {
            var movementId = $(this).data('id');

            if (confirm('Bu stok hareketini silmek istediğinizden emin misiniz?')) {
                // Show loading state on button
                const deleteBtn = $(this);
                const originalHtml = deleteBtn.html();
                deleteBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);

                $.ajax({
                    url: 'api_islemleri/stok_hareket_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_movement',
                        hareket_id: movementId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert(response.message, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Silme işlemi sırasında bir hata oluştu.', 'danger');
                    },
                    complete: function() {
                        // Restore button state
                        deleteBtn.html(originalHtml).prop('disabled', false);
                    }
                });
            }
        });
        
        // Transfer button click handler
        $('#transferButton').on('click', function() {
            // Reset form
            $('#transferForm')[0].reset();
            $('#transfer_aciklama').val('Stok transferi');
            
            // Load stock items based on type
            loadTransferStockItems();
            
            // Update location fields based on stock type
            updateTransferLocationFields();
            
            $('#transferModal').modal('show');
        });
        
        // Load transfer stock items based on type
        function loadTransferStockItems() {
            var stockType = $('#transfer_stok_turu').val();
            if (!stockType) return;
            
            $.ajax({
                url: 'api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=' + stockType,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var kodSelect = $('#transfer_kod');
                        kodSelect.empty();
                        kodSelect.append('<option value="">Ürün Seçin</option>');

                        response.data.forEach(function(item) {
                            kodSelect.append(`<option value="${item.kod}" data-stok="${item.stok}">${item.kod} - ${item.isim} (Stok: ${item.stok})</option>`);
                        });
                    }
                },
                error: function() {
                    showAlert('Transfer ürünleri yüklenirken bir hata oluştu.', 'danger');
                }
            });
        }
        
        // Get current location for selected item
        function getCurrentLocationForItem(stockType, itemCode) {
            console.log('Getting location for:', stockType, itemCode);

            // Check if elements exist before making AJAX call
            console.log('Checking elements before AJAX:');
            console.log('kaynak_tank exists:', $('#kaynak_tank').length > 0);
            console.log('kaynak_depo exists:', $('#kaynak_depo').length > 0);
            console.log('kaynak_raf exists:', $('#kaynak_raf').length > 0);

            $.ajax({
                url: 'api_islemleri/stok_hareket_islemler.php?action=get_current_location&stock_type=' + stockType + '&item_code=' + itemCode,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('API Response:', response);

                    if (response.status === 'success') {
                        var location = response.data;
                        var currentStock = location.stok_miktari;

                        console.log('Location data:', location);

                        // Set the transfer amount to the current stock amount
                        $('#transfer_miktar').val(currentStock);

                        // Set the source location based on stock type with existence check
                        if (stockType === 'esans') {
                            var tankElement = $('#kaynak_tank');
                            if (tankElement.length === 0) {
                                // Ensure location fields are created first
                                updateTransferLocationFields();
                                tankElement = $('#kaynak_tank');
                            }
                            if (tankElement.length > 0) {
                                tankElement.val(location.konum || '');
                                console.log('Set tank value:', location.konum);
                            }
                        } else {
                            var depoElement = $('#kaynak_depo');
                            var rafElement = $('#kaynak_raf');

                            if (depoElement.length === 0 || rafElement.length === 0) {
                                // Ensure location fields are created first
                                updateTransferLocationFields();
                                depoElement = $('#kaynak_depo');
                                rafElement = $('#kaynak_raf');
                            }

                            if (depoElement.length > 0) {
                                depoElement.val(location.depo || '');
                                console.log('Set depo value:', location.depo);
                            }

                            if (rafElement.length > 0) {
                                rafElement.val(location.raf || '');
                                console.log('Set raf value:', location.raf);
                            }
                        }

                        showAlert('Kaynak konum ve miktar otomatik olarak dolduruldu: ' + currentStock, 'info');
                    } else {
                        showAlert(response.message, 'warning');
                        console.log('API Error:', response.message);
                        // Clear the fields if no location found
                        $('#transfer_miktar').val('');
                        if (stockType === 'esans') {
                            if ($('#kaynak_tank').length > 0) {
                                $('#kaynak_tank').val('');
                            }
                        } else {
                            if ($('#kaynak_depo').length > 0) {
                                $('#kaynak_depo').val('');
                            }
                            if ($('#kaynak_raf').length > 0) {
                                $('#kaynak_raf').val('');
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('Kaynak konum bilgisi alınırken bir hata oluştu: ' + error, 'danger');
                    console.log('AJAX Error:', error);
                    console.log('Response:', xhr.responseText);
                    console.log('Status:', status);
                }
            });
        }
        
        // Modified version of loadShelvesForDepot to set a specific shelf
        // Modified version of loadShelvesForDepot to work with both select and input elements
        function loadShelvesForDepot(depo, rafElementId, selectedShelf = null) {
            if (!depo) {
                // Clear the shelf selection/input if no depot is selected
                var rafElement = $('#' + rafElementId);
                // Check if it's a select element or input text element
                if (rafElement.is('select')) {
                    rafElement.empty().append('<option value="">Raf Seçin</option>');
                } else {
                    rafElement.val('');
                }
                return;
            }

            // Check if it's a select element or input text element
            var rafElement = $('#' + rafElementId);
            if (rafElement.is('select')) {
                // Fetch all locations again to get shelves for the selected depot
                $.ajax({
                    url: 'api_islemleri/stok_hareket_islemler.php?action=get_locations',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var rafSelect = $('#' + rafElementId);
                            rafSelect.empty();
                            rafSelect.append('<option value="">Raf Seçin</option>');
                            
                            var depoShelves = response.data.filter(function(location) {
                                return location.depo_ismi === depo;
                            });
                            
                            // Get unique shelves for the selected depot
                            var uniqueShelves = [];
                            depoShelves.forEach(function(location) {
                                if (location.raf && uniqueShelves.indexOf(location.raf) === -1) {
                                    uniqueShelves.push(location.raf);
                                }
                            });
                            
                            uniqueShelves.forEach(function(raf) {
                                var option = $(`<option value="${raf}">${raf}</option>`);
                                if (raf === selectedShelf) {
                                    option.attr('selected', 'selected');
                                }
                                rafSelect.append(option);
                            });
                            
                            if (uniqueShelves.length === 0) {
                                rafSelect.append('<option value="">Bu depoda raf bulunmuyor</option>');
                            } else if (selectedShelf && !uniqueShelves.includes(selectedShelf)) {
                                rafSelect.append(`<option value="${selectedShelf}" selected>${selectedShelf}</option>`);
                            }
                        } else {
                            showAlert('Raf bilgileri yüklenirken bir hata oluştu.', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Raf bilgileri yüklenirken bir hata oluştu.', 'danger');
                    }
                });
            } else {
                // If it's a text input, just set the value
                if (selectedShelf) {
                    rafElement.val(selectedShelf);
                }
            }
        }
        
        // Update transfer location fields based on stock type
        function updateTransferLocationFields() {
            var stockType = $('#transfer_stok_turu').val();
            
            if (stockType === 'malzeme' || stockType === 'urun') {
                // Source location fields - Read only, will be filled by system
                var sourceHtml = `
                    <div class="form-group">
                        <label for="kaynak_depo">Kaynak Depo *</label>
                        <input type="text" class="form-control" id="kaynak_depo" name="kaynak_depo" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="kaynak_raf">Kaynak Raf *</label>
                        <input type="text" class="form-control" id="kaynak_raf" name="kaynak_raf" required readonly>
                    </div>
                `;
                
                // Destination location fields
                var destHtml = `
                    <div class="form-group">
                        <label for="hedef_depo">Hedef Depo *</label>
                        <select class="form-control" id="hedef_depo" name="hedef_depo" required>
                            <option value="">Depo Seçin</option>
                            <?php
                            $locations_result->data_seek(0);
                            while($location = $locations_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($location['depo_ismi']); ?>">
                                    <?php echo htmlspecialchars($location['depo_ismi']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="hedef_raf">Hedef Raf *</label>
                        <select class="form-control" id="hedef_raf" name="hedef_raf" required>
                            <option value="">Raf Seçin</option>
                        </select>
                    </div>
                `;
                
                $('#source-location-fields').html(sourceHtml);
                $('#destination-location-fields').html(destHtml);
                
                // Event handlers for depot changes to load corresponding shelves for destination only
                $('#hedef_depo').on('change', function() {
                    loadShelvesForDepot($(this).val(), 'hedef_raf');
                });
            } else {
                $('#source-location-fields').html('');
                $('#destination-location-fields').html('');
            }
        }
        
        // Transfer stock type change handler
        $('#transfer_stok_turu').on('change', function() {
            var stockType = $(this).val();
            if (stockType) {
                loadTransferStockItems();
                updateTransferLocationFields();
                // Clear the item selection when stock type changes
                $('#transfer_kod').empty().append('<option value="">Ürün Seçin</option>');
            } else {
                $('#transfer_kod').empty().append('<option value="">Ürün Seçin</option>');
                $('#source-location-fields').html('');
                $('#destination-location-fields').html('');
                $('#transfer_miktar').val('');
            }
        });
        
        // Transfer item change handler (when user selects an item)
        $('#transfer_kod').on('change', function() {
            var stockType = $('#transfer_stok_turu').val();
            var itemCode = $(this).val();

            console.log('Transfer kod changed:', stockType, itemCode);

            if (stockType && itemCode) {
                // Get the stored stock value from the option
                var selectedOption = $('#transfer_kod option:selected');
                var stockValue = selectedOption.data('stok');

                console.log('Selected stock value:', stockValue);

                // Set the transfer amount to the current stock amount
                $('#transfer_miktar').val(stockValue);

                // Ensure location fields are created before getting current location
                updateTransferLocationFields();

                // Wait a bit longer for DOM to be updated, then get current location
                setTimeout(function() {
                    console.log('About to call getCurrentLocationForItem');
                    console.log('Source location fields HTML:', $('#source-location-fields').html());
                    getCurrentLocationForItem(stockType, itemCode);
                }, 300);
            } else {
                $('#transfer_miktar').val('');
                // Clear source location fields
                if (stockType === 'esans') {
                    if ($('#kaynak_tank').length > 0) {
                        $('#kaynak_tank').val('');
                    }
                } else {
                    if ($('#kaynak_depo').length > 0) {
                        $('#kaynak_depo').val('');
                    }
                    if ($('#kaynak_raf').length > 0) {
                        $('#kaynak_raf').val('');
                    }
                }
            }
        });
        
        // Load shelves for selected depot
        function loadShelvesForDepot(depo, rafSelectId) {
            if (!depo) {
                // Clear the shelf selection if no depot is selected
                $('#' + rafSelectId).empty().append('<option value="">Raf Seçin</option>');
                return;
            }
            
            // Fetch all locations again to get shelves for the selected depot
            $.ajax({
                url: 'api_islemleri/stok_hareket_islemler.php?action=get_locations',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var rafSelect = $('#' + rafSelectId);
                        rafSelect.empty();
                        rafSelect.append('<option value="">Raf Seçin</option>');
                        
                        var depoShelves = response.data.filter(function(location) {
                            return location.depo_ismi === depo;
                        });
                        
                        // Get unique shelves for the selected depot
                        var uniqueShelves = [];
                        depoShelves.forEach(function(location) {
                            if (location.raf && uniqueShelves.indexOf(location.raf) === -1) {
                                uniqueShelves.push(location.raf);
                            }
                        });
                        
                        uniqueShelves.forEach(function(raf) {
                            rafSelect.append(`<option value="${raf}">${raf}</option>`);
                        });
                        
                        if (uniqueShelves.length === 0) {
                            rafSelect.append('<option value="">Bu depoda raf bulunmuyor</option>');
                        }
                    } else {
                        showAlert('Raf bilgileri yüklenirken bir hata oluştu.', 'danger');
                    }
                },
                error: function() {
                    showAlert('Raf bilgileri yüklenirken bir hata oluştu.', 'danger');
                }
            });
        }
        
        // Handle transfer form submission
        $('#transferForm').on('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = $('#transferForm button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Transfer Ediliyor...').prop('disabled', true);

            var formData = $(this).serialize();

            $.ajax({
                url: 'api_islemleri/stok_hareket_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#transferModal').modal('hide');
                        showAlert(response.message, 'success');
                        // Reload page to see changes
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Transfer işlemi sırasında bir hata oluştu.', 'danger');
                },
                complete: function() {
                    // Restore button state
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>

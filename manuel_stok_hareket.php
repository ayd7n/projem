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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #1abc9c;
            --danger: #e74c3c;
            --warning: #f1c40f;
            --info: #3498db;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --bg-color: #f5f7fb;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #2c3e50;
            --text-secondary: #8492a6;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }
        .main-content { padding: 30px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .page-header p { color: var(--text-secondary); font-size: 1rem; }
        .card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); margin-bottom: 30px; overflow: hidden; }
        .card-header { padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
        .card-header h2 { font-size: 1.2rem; font-weight: 600; }
        .card-body { padding: 20px; }
        .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--secondary); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-danger { background-color: var(--danger); color: white; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; }
        th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); }
        tbody tr:hover { background-color: #f5f7fb; }
        .actions { display: flex; gap: 10px; }
        .actions button { padding: 8px 12px; }
        .stat-card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); padding: 25px; display: flex; align-items: center; }
        .stat-icon { font-size: 2rem; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; color: white; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; }
        .stat-info p { color: var(--text-secondary); }
        .modal-body .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .modal-body .form-group { display: flex; flex-direction: column; }
        .modal-body .form-group label { font-weight: 500; margin-bottom: 8px; font-size: 0.9rem; }
        .modal-body .form-group input, .modal-body .form-group select, .modal-body .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 0.95rem; }
        
        select {
            padding: 0 !important;
        }
        .disabled-row { opacity: 0.6; }
        .disabled-row .actions button { pointer-events: none; opacity: 0.5; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Manuel Stok Hareket Yönetimi</h1>
            <p>Manuel olarak stok hareketlerini kaydedin ve yönetin</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addMovementBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Stok Hareketi Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-exchange-alt"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_movements; ?></h3>
                        <p>Toplam Stok Hareketi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Stok Hareketleri Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>İşlemler</th>
                                <th>ID</th>
                                <th>Tarih</th>
                                <th>Stok Türü</th>
                                <th>Kod</th>
                                <th>İsim</th>
                                <th>Miktar</th>
                                <th>Yön</th>
                                <th>Hareket Türü</th>
                                <th>Konum</th>
                                <th>Belge No</th>
                                <th>Açıklama</th>
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
                                        <td><?php echo $movement['hareket_id']; ?></td>
                                        <td><?php echo $movement['tarih']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $movement['stok_turu'] === 'malzeme' ? 'primary' : ($movement['stok_turu'] === 'esans' ? 'success' : 'info'); ?>">
                                                <?php echo htmlspecialchars($movement['stok_turu']); ?>
                                            </span>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($movement['kod']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($movement['isim']); ?></td>
                                        <td><?php echo number_format($movement['miktar'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $movement['yon'] === 'giris' ? 'success' : 'danger'; ?>">
                                                <?php echo htmlspecialchars($movement['yon']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($movement['hareket_turu']); ?></td>
                                        <td>
                                            <?php
                                            if (!empty($movement['depo'])) {
                                                echo htmlspecialchars($movement['depo']);
                                                if (!empty($movement['raf'])) {
                                                    echo ' / ' . htmlspecialchars($movement['raf']);
                                                }
                                            } elseif (!empty($movement['tank_kodu'])) {
                                                echo 'Tank: ' . htmlspecialchars($movement['tank_kodu']);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($movement['ilgili_belge_no']); ?></td>
                                        <td><?php echo htmlspecialchars($movement['aciklama']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center p-4">Henüz kayıtlı stok hareketi bulunmuyor.</td>
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
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="stok_turu">Stok Türü *</label>
                                <select class="form-control" id="stok_turu" name="stok_turu" required>
                                    <option value="malzeme">Malzeme</option>
                                    <option value="esans">Esans</option>
                                    <option value="urun">Ürün</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="kod">Kod Seçin *</label>
                                <select class="form-control" id="kod" name="kod" required>
                                    <option value="">Kod Seçin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="yon">Yön *</label>
                                <select class="form-control" id="yon" name="yon" required>
                                    <option value="giris">Giriş</option>
                                    <option value="cikis">Çıkış</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="hareket_turu">Hareket Türü *</label>
                                <select class="form-control" id="hareket_turu" name="hareket_turu" required>
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
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="miktar">Miktar *</label>
                                <input type="number" class="form-control" id="miktar" name="miktar" min="0.01" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="ilgili_belge_no">İlgili Belge No</label>
                                <input type="text" class="form-control" id="ilgili_belge_no" name="ilgili_belge_no">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="aciklama">Açıklama *</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3" required></textarea>
                            </div>
                        </div>
                        <div id="location-fields">
                            <!-- Location fields will be shown/hidden based on stock type -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Kaydet</button>
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

        function showAlert(message, type) {
            $('#alert-placeholder').html(
                `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`
            );
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
                }
            });
        }

        // Update location fields based on stock type
        function updateLocationFields(stockType) {
            var locationDiv = $('#location-fields');

            if (stockType === 'malzeme' || stockType === 'urun') {
                locationDiv.html(`
                    <div class="form-grid">
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
                        <div class="form-group">
                            <label for="raf">Raf *</label>
                            <input type="text" class="form-control" id="raf" name="raf" required>
                        </div>
                    </div>
                `);
            } else if (stockType === 'esans') {
                locationDiv.html(`
                    <div class="form-group">
                        <label for="tank_kodu">Tank Kodu *</label>
                        <select class="form-control" id="tank_kodu" name="tank_kodu" required>
                            <option value="">Tank Seçin</option>
                            <?php
                            $tanks_result->data_seek(0);
                            while($tank = $tanks_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($tank['tank_kodu']); ?>">
                                    <?php echo htmlspecialchars($tank['tank_kodu']); ?> - <?php echo htmlspecialchars($tank['tank_ismi']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
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
            
            movementTypeSelect.empty(); // önceki seçenekleri temizle
            
            if (direction === 'giris') {
                // Sadece giriş yönü için uygun türleri ekle
                movementTypeSelect.append('<option value="stok_giris">Stok Girişi</option>');
                movementTypeSelect.append('<option value="uretim">Üretim</option>');
                movementTypeSelect.append('<option value="iade_girisi">İade Girişi</option>');
                movementTypeSelect.append('<option value="sayim_farki">Sayım Farkı (Artış)</option>');
                movementTypeSelect.append('<option value="stok_duzeltme">Stok Düzeltme (Artış)</option>');
                movementTypeSelect.append('<option value="transfer">Transfer (Giriş)</option>');
            } else if (direction === 'cikis') {
                // Sadece çıkış yönü için uygun türleri ekle
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

        // Open modal for adding a new movement
        $('#addMovementBtn').on('click', function() {
            $('#movementForm')[0].reset();
            $('#modalTitle').text('Yeni Stok Hareketi Ekle');
            $('#action').val('add_movement');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#movementModal').modal('show');
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
                            } else if (movement.tank_kodu) {
                                $('#tank_kodu').val(movement.tank_kodu);
                            }
                            // Also update movement types for the loaded direction
                            updateMovementTypes();
                            // Set the correct movement type value after the options are loaded
                            $('#hareket_turu').val(movement.hareket_turu);
                        }, 100);

                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
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

        // Initialize movement types based on default direction when modal opens for adding
        $('#addMovementBtn').on('click', function() {
            // Set a default direction and update movement types
            $('#yon').val('giris'); // Default olarak giriş seç
            updateMovementTypes(); // Hareket türlerini güncelle
        });

        // Handle form submission
        $('#movementForm').on('submit', function(e) {
            e.preventDefault();
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

        // Handle movement deletion
        $('.delete-btn').on('click', function() {
            var movementId = $(this).data('id');

            if (confirm('Bu stok hareketini silmek istediğinizden emin misiniz?')) {
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
    });
    </script>
</body>
</html>

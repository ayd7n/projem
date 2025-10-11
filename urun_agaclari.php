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

// Fetch all product trees
$product_trees_query = "SELECT * FROM urun_agaci ORDER BY urun_ismi, bilesen_ismi";
$product_trees_result = $connection->query($product_trees_query);

// Fetch all products, materials, and essences for dropdowns
$products_query = "SELECT urun_kodu, urun_ismi FROM urunler ORDER BY urun_ismi";
$products_result = $connection->query($products_query);

$materials_query = "SELECT malzeme_kodu, malzeme_ismi, malzeme_turu FROM malzemeler ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);

$essences_query = "SELECT esans_kodu, esans_ismi FROM esanslar ORDER BY esans_ismi";
$essences_result = $connection->query($essences_query);

// Calculate total product trees (distinct products in product tree)
$total_result = $connection->query("SELECT COUNT(DISTINCT urun_kodu) as total FROM urun_agaci");
$total_product_trees = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ürün Ağaçları - Parfüm ERP</title>
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
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Ürün Ağacı Yönetimi</h1>
            <p>Ürün ağaçlarını ekleyin, düzenleyin ve yönetin</p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Bilgilendirme:</strong> Her biri ürünün yapımında kullanılan bileşenleri tanımlayın.
                Bir üründe tipik olarak bir tane esans (koku maddesi) ve birden çok malzeme bulunabilir.
                Örnek: Bir parfüm ürünü için bir adet gül esansı ve şişe, kapak gibi malzemeler.
                Giriş yaparken dikkat edin, her ürün için esans ve malzemeleri ayrı ayrı ekleyin.
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addProductTreeBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Ürün Ağacı Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-sitemap"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_product_trees; ?></h3>
                        <p>Toplam Ürün Ağacı</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Ürün Ağacı Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>İşlemler</th>
                                <th>Ürün Kodu</th>
                                <th>Ürün İsmi</th>
                                <th>Bileşen Kodu</th>
                                <th>Bileşen İsmi</th>
                                <th>Bileşen Miktarı</th>
                                <th>Bileşen Türü</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($product_trees_result && $product_trees_result->num_rows > 0): ?>
                                <?php while ($pt = $product_trees_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $pt['urun_agaci_id']; ?>"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $pt['urun_agaci_id']; ?>"><i class="fas fa-trash"></i></button>
                                        </td>
                                        <td><?php echo htmlspecialchars($pt['urun_kodu']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($pt['urun_ismi']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pt['bilesen_kodu']); ?></td>
                                        <td><?php echo htmlspecialchars($pt['bilesen_ismi']); ?></td>
                                        <td><?php echo $pt['bilesen_miktari']; ?></td>
                                        <td><?php echo htmlspecialchars($pt['bilesenin_malzeme_turu']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center p-4">Henüz kayıtlı ürün ağacı bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tree Modal -->
    <div class="modal fade" id="productTreeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="productTreeForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ürün Ağacı Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="urun_agaci_id" name="urun_agaci_id">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="urun_kodu">Ürün *</label>
                                <select class="form-control" id="urun_kodu" name="urun_kodu" required>
                                    <option value="">Ürün Seçin</option>
                                    <?php 
                                    $products_result->data_seek(0);
                                    while($product = $products_result->fetch_assoc()): ?>
                                        <option value="<?php echo $product['urun_kodu']; ?>" data-name="<?php echo htmlspecialchars($product['urun_ismi']); ?>">
                                            <?php echo $product['urun_kodu']; ?> - <?php echo htmlspecialchars($product['urun_ismi']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <input type="hidden" id="urun_ismi" name="urun_ismi">
                            </div>
                            <div class="form-group">
                                <label for="bilesen_kodu">Bileşen *</label>
                                <select class="form-control" id="bilesen_kodu" name="bilesen_kodu" required>
                                    <option value="">Bileşen Seçin</option>
                                    <optgroup label="Esanslar">
                                        <?php 
                                        $essences_result->data_seek(0);
                                        while($essence = $essences_result->fetch_assoc()): ?>
                                            <option value="<?php echo $essence['esans_kodu']; ?>" data-type="esans" data-name="<?php echo htmlspecialchars($essence['esans_ismi']); ?>">
                                                <?php echo htmlspecialchars($essence['esans_kodu']); ?> - <?php echo htmlspecialchars($essence['esans_ismi']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </optgroup>
                                    <optgroup label="Malzemeler">
                                        <?php 
                                        $materials_result->data_seek(0);
                                        while($material = $materials_result->fetch_assoc()): ?>
                                            <option value="<?php echo $material['malzeme_kodu']; ?>" data-type="<?php echo $material['malzeme_turu']; ?>" data-name="<?php echo htmlspecialchars($material['malzeme_ismi']); ?>">
                                                <?php echo $material['malzeme_kodu']; ?> - <?php echo htmlspecialchars($material['malzeme_ismi']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </optgroup>
                                </select>
                                <input type="hidden" id="bilesen_ismi" name="bilesen_ismi">
                                <input type="hidden" id="bilesenin_malzeme_turu" name="bilesenin_malzeme_turu">
                            </div>
                            <div class="form-group">
                                <label for="bilesen_miktari">Bileşen Miktarı *</label>
                                <input type="number" step="0.01" class="form-control" id="bilesen_miktari" name="bilesen_miktari" min="0" required>
                            </div>
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

        // Set hidden fields on dropdown change
        $('#urun_kodu').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            $('#urun_ismi').val(selectedOption.data('name'));
        });

        $('#bilesen_kodu').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            $('#bilesen_ismi').val(selectedOption.data('name'));
            $('#bilesenin_malzeme_turu').val(selectedOption.data('type'));
        });

        // Open modal for adding a new product tree
        $('#addProductTreeBtn').on('click', function() {
            $('#productTreeForm')[0].reset();
            $('#modalTitle').text('Yeni Ürün Ağacı Ekle');
            $('#action').val('add_product_tree');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#productTreeModal').modal('show');
        });

        // Open modal for editing a product tree
        $('.edit-btn').on('click', function() {
            var ptId = $(this).data('id');
            $.ajax({
                url: 'api_islemleri/urun_agaclari_islemler.php?action=get_product_tree&id=' + ptId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var pt = response.data;
                        $('#productTreeForm')[0].reset();
                        $('#modalTitle').text('Ürün Ağacını Düzenle');
                        $('#action').val('update_product_tree');
                        $('#urun_agaci_id').val(pt.urun_agaci_id);
                        $('#urun_kodu').val(pt.urun_kodu);
                        $('#urun_ismi').val(pt.urun_ismi);
                        $('#bilesen_kodu').val(pt.bilesen_kodu);
                        $('#bilesen_ismi').val(pt.bilesen_ismi);
                        $('#bilesenin_malzeme_turu').val(pt.bilesenin_malzeme_turu);
                        $('#bilesen_miktari').val(pt.bilesen_miktari);
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#productTreeModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Ürün ağacı bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#productTreeForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                url: 'api_islemleri/urun_agaclari_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#productTreeModal').modal('hide');
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

        // Handle product tree deletion
        $('.delete-btn').on('click', function() {
            var ptId = $(this).data('id');
            if (confirm('Bu ürün ağacını silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/urun_agaclari_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_product_tree',
                        urun_agaci_id: ptId
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

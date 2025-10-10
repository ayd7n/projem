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

// Fetch all products
$products_query = "SELECT * FROM urunler ORDER BY urun_ismi";
$products_result = $connection->query($products_query);

// Calculate total products
$total_result = $connection->query("SELECT COUNT(*) as total FROM urunler");
$total_products = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ürünler - Parfüm ERP</title>
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
        .stock-info { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .stock-normal { background: #e8f5e9; color: #2e7d32; }
        .stock-critical { background: #fff8e1; color: #f57f17; }
        .stock-low { background: #ffebee; color: #c62828; }
        .modal-body .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .modal-body .form-group { display: flex; flex-direction: column; }
        .modal-body .form-group label { font-weight: 500; margin-bottom: 8px; font-size: 0.9rem; }
        .modal-body .form-group input, .modal-body .form-group select, .modal-body .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Ürün Yönetimi</h1>
            <p>Ürünleri ekleyin, düzenleyin ve yönetin</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addProductBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Ürün Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-boxes"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_products; ?></h3>
                        <p>Toplam Ürün</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Ürün Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>İşlemler</th>
                                <th>Ürün Kodu</th>
                                <th>Ürün İsmi</th>
                                <th>Stok</th>
                                <th>Birim</th>
                                <th>Satış Fiyatı</th>
                                <th>Depo</th>
                                <th>Raf</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($products_result && $products_result->num_rows > 0): ?>
                                <?php while ($product = $products_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $product['urun_kodu']; ?>"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $product['urun_kodu']; ?>"><i class="fas fa-trash"></i></button>
                                        </td>
                                        <td><?php echo $product['urun_kodu']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($product['urun_ismi']); ?></strong></td>
                                        <td>
                                            <span class="stock-info <?php echo $product['stok_miktari'] <= 0 ? 'stock-low' : ($product['stok_miktari'] <= $product['kritik_stok_seviyesi'] ? 'stock-critical' : 'stock-normal'); ?>">
                                                <?php echo $product['stok_miktari']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['birim']); ?></td>
                                        <td><?php echo number_format($product['satis_fiyati'], 2, ',', '.'); ?> ₺</td>
                                        <td><?php echo htmlspecialchars($product['depo']); ?></td>
                                        <td><?php echo htmlspecialchars($product['raf']); ?></td>
                                        <td>
                                            <?php 
                                            if ($product['stok_miktari'] == 0) {
                                                echo '<span style="color: #c62828; font-weight: bold;">Stokta Yok</span>';
                                            } elseif ($product['stok_miktari'] <= $product['kritik_stok_seviyesi']) {
                                                echo '<span style="color: #f57f17; font-weight: bold;">Kritik Seviye</span>';
                                            } else {
                                                echo '<span style="color: #2e7d32; font-weight: bold;">Yeterli</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center p-4">Henüz kayıtlı ürün bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="productForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ürün Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="urun_kodu" name="urun_kodu">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="urun_ismi">Ürün İsmi *</label>
                                <input type="text" class="form-control" id="urun_ismi" name="urun_ismi" required>
                            </div>
                            <div class="form-group">
                                <label for="stok_miktari">Stok Miktarı</label>
                                <input type="number" class="form-control" id="stok_miktari" name="stok_miktari" min="0">
                            </div>
                            <div class="form-group">
                                <label for="birim">Birim</label>
                                <select class="form-control" id="birim" name="birim">
                                    <option value="adet">Adet</option>
                                    <option value="kg">Kg</option>
                                    <option value="gr">Gr</option>
                                    <option value="lt">Lt</option>
                                    <option value="ml">Ml</option>
                                    <option value="mt">Mt</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="satis_fiyati">Satış Fiyatı (₺)</label>
                                <input type="number" step="0.01" class="form-control" id="satis_fiyati" name="satis_fiyati" min="0">
                            </div>
                            <div class="form-group">
                                <label for="kritik_stok_seviyesi">Kritik Stok Seviyesi</label>
                                <input type="number" class="form-control" id="kritik_stok_seviyesi" name="kritik_stok_seviyesi" min="0">
                            </div>
                            <div class="form-group">
                                <label for="depo">Depo</label>
                                <input type="text" class="form-control" id="depo" name="depo">
                            </div>
                            <div class="form-group">
                                <label for="raf">Raf</label>
                                <input type="text" class="form-control" id="raf" name="raf">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="not_bilgisi">Not Bilgisi</label>
                                <textarea class="form-control" id="not_bilgisi" name="not_bilgisi" rows="3"></textarea>
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

        // Open modal for adding a new product
        $('#addProductBtn').on('click', function() {
            $('#productForm')[0].reset();
            $('#modalTitle').text('Yeni Ürün Ekle');
            $('#action').val('add_product');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#productModal').modal('show');
        });

        // Open modal for editing a product
        $('.edit-btn').on('click', function() {
            var productId = $(this).data('id');
            $.ajax({
                url: 'urunler_islemler.php?action=get_product&id=' + productId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var product = response.data;
                        $('#productForm')[0].reset();
                        $('#modalTitle').text('Ürünü Düzenle');
                        $('#action').val('update_product');
                        $('#urun_kodu').val(product.urun_kodu);
                        $('#urun_ismi').val(product.urun_ismi);
                        $('#stok_miktari').val(product.stok_miktari);
                        $('#birim').val(product.birim);
                        $('#satis_fiyati').val(product.satis_fiyati);
                        $('#kritik_stok_seviyesi').val(product.kritik_stok_seviyesi);
                        $('#depo').val(product.depo);
                        $('#raf').val(product.raf);
                        $('#not_bilgisi').val(product.not_bilgisi);
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#productModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Ürün bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                url: 'urunler_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#productModal').modal('hide');
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

        // Handle product deletion
        $('.delete-btn').on('click', function() {
            var productId = $(this).data('id');
            if (confirm('Bu ürünü silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'urunler_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_product',
                        urun_kodu: productId
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
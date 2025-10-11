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

// Fetch all materials from the actual table (not the view)
$materials_query = "SELECT * FROM malzemeler ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);

// Calculate total materials
$total_result = $connection->query("SELECT COUNT(*) as total FROM malzemeler");
$total_materials = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Malzemeler - Parfüm ERP</title>
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
            <h1>Malzeme Yönetimi</h1>
            <p>Malzemeleri ekleyin, düzenleyin ve yönetin</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addMaterialBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Malzeme Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-boxes"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_materials; ?></h3>
                        <p>Toplam Malzeme</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Malzeme Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>İşlemler</th>
                                <th>Kod</th>
                                <th>İsim</th>
                                <th>Tür</th>
                                <th>Not</th>
                                <th>Stok</th>
                                <th>Birim</th>
                                <th>Termin</th>
                                <th>Depo</th>
                                <th>Raf</th>
                                <th>Kritik Seviye</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($materials_result && $materials_result->num_rows > 0): ?>
                                <?php while ($material = $materials_result->fetch_assoc()): 
                                    $stock_class = $material['stok_miktari'] <= 0 ? 'stock-low' : 
                                                  ($material['stok_miktari'] <= $material['kritik_stok_seviyesi'] ? 'stock-critical' : 'stock-normal');
                                    $status_text = $material['stok_miktari'] == 0 ? 'Stokta Yok' : 
                                                 ($material['stok_miktari'] <= $material['kritik_stok_seviyesi'] ? 'Kritik Seviye' : 'Yeterli');
                                    $status_color = $material['stok_miktari'] == 0 ? '#c62828' : 
                                                  ($material['stok_miktari'] <= $material['kritik_stok_seviyesi'] ? '#f57f17' : '#2e7d32');
                                ?>
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $material['malzeme_kodu']; ?>"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $material['malzeme_kodu']; ?>"><i class="fas fa-trash"></i></button>
                                        </td>
                                        <td><?php echo $material['malzeme_kodu']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($material['malzeme_ismi']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($material['malzeme_turu']); ?></td>
                                        <td><?php echo htmlspecialchars($material['not_bilgisi']); ?></td>
                                        <td>
                                            <span class="stock-info <?php echo $stock_class; ?>">
                                                <?php echo $material['stok_miktari']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            // Normalize birim value to display properly
                                            $birim = $material['birim'];
                                            switch($birim) {
                                                case 'adet': echo 'Adet'; break;
                                                case 'kg': echo 'Kg'; break;
                                                case 'gr': echo 'Gr'; break;
                                                case 'lt': echo 'Lt'; break;
                                                case 'ml': echo 'Ml'; break;
                                                case 'm': echo 'Mt'; break;
                                                case 'cm': echo 'Cm'; break;
                                                case '1': echo 'Adet'; break; // If enum index 1
                                                case '2': echo 'Kg'; break;   // If enum index 2
                                                case '3': echo 'Gr'; break;   // If enum index 3
                                                case '4': echo 'Lt'; break;   // If enum index 4
                                                case '5': echo 'Ml'; break;   // If enum index 5
                                                case '6': echo 'Mt'; break;   // If enum index 6
                                                case '7': echo 'Cm'; break;   // If enum index 7
                                                default: echo htmlspecialchars($birim); break;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $material['termin_suresi']; ?></td>
                                        <td><?php echo htmlspecialchars($material['depo']); ?></td>
                                        <td><?php echo htmlspecialchars($material['raf']); ?></td>
                                        <td><?php echo $material['kritik_stok_seviyesi']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center p-4">Henüz kayıtlı malzeme bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Material Modal -->
    <div class="modal fade" id="materialModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="materialForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Malzeme Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="malzeme_kodu" name="malzeme_kodu">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="malzeme_ismi">Malzeme İsmi *</label>
                                <input type="text" class="form-control" id="malzeme_ismi" name="malzeme_ismi" required>
                            </div>
                            <div class="form-group">
                                <label for="malzeme_turu">Malzeme Türü</label>
                                <select class="form-control" id="malzeme_turu" name="malzeme_turu">
                                    <option value="sise">Şişe</option>
                                    <option value="kutu">Kutu</option>
                                    <option value="etiket">Etiket</option>
                                    <option value="pompa">Pompa</option>
                                    <option value="ic_ambalaj">İç Ambalaj</option>
                                    <option value="numune_sisesi">Numune Şişesi</option>
                                    <option value="kapak">Kapak</option>
                                    <option value="karton_ara_bolme">Karton Ara Bölme</option>
                                    <option value="diger">Diğer</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="stok_miktari">Stok Miktarı</label>
                                <input type="number" step="0.01" class="form-control" id="stok_miktari" name="stok_miktari" min="0">
                            </div>
                            <div class="form-group">
                                <label for="birim">Birim</label>
                                <select class="form-control" id="birim" name="birim">
                                    <option value="adet" selected>Adet</option>
                                    <option value="kg">Kg</option>
                                    <option value="gr">Gr</option>
                                    <option value="lt">Lt</option>
                                    <option value="ml">Ml</option>
                                    <option value="m">Mt</option>
                                    <option value="cm">Cm</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="termin_suresi">Termin Süresi (Gün)</label>
                                <input type="number" class="form-control" id="termin_suresi" name="termin_suresi" min="0">
                            </div>
                            <div class="form-group">
                                <label for="depo">Depo</label>
                                <select class="form-control" id="depo" name="depo">
                                    <option value="">Depo Seçin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="raf">Raf</label>
                                <select class="form-control" id="raf" name="raf">
                                    <option value="">Önce Depo Seçin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="kritik_stok_seviyesi">Kritik Stok Seviyesi</label>
                                <input type="number" class="form-control" id="kritik_stok_seviyesi" name="kritik_stok_seviyesi" min="0">
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

        // Load depo list on page load and modal open
        loadDepoList();

        // Function to load depo list
        function loadDepoList() {
            $.ajax({
                url: 'api_islemleri/urunler_islemler.php?action=get_depo_list',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var depoSelect = $('#depo');
                        depoSelect.empty();
                        depoSelect.append('<option value="">Depo Seçin</option>');
                        $.each(response.data, function(index, depo) {
                            depoSelect.append('<option value="' + depo.depo_ismi + '">' + depo.depo_ismi + '</option>');
                        });
                    }
                },
                error: function() {
                    console.log('Depo listesi yüklenirken bir hata oluştu.');
                }
            });
        }

        // When depo is selected, load corresponding raf list
        $('#depo').on('change', function() {
            var selectedDepo = $(this).val();
            if (selectedDepo) {
                loadRafList(selectedDepo);
            } else {
                $('#raf').empty().append('<option value="">Önce Depo Seçin</option>');
            }
        });

        // Function to load raf list based on selected depo
        function loadRafList(depo) {
            $.ajax({
                url: 'api_islemleri/urunler_islemler.php?action=get_raf_list&depo=' + encodeURIComponent(depo),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var rafSelect = $('#raf');
                        rafSelect.empty();
                        rafSelect.append('<option value="">Raf Seçin</option>');
                        $.each(response.data, function(index, raf) {
                            rafSelect.append('<option value="' + raf.raf + '">' + raf.raf + '</option>');
                        });
                    }
                },
                error: function() {
                    console.log('Raf listesi yüklenirken bir hata oluştu.');
                }
            });
        }

        // Open modal for adding a new material
        $('#addMaterialBtn').on('click', function() {
            $('#materialForm')[0].reset();
            $('#modalTitle').text('Yeni Malzeme Ekle');
            $('#action').val('add_material');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#materialModal').modal('show');
        });

        // Open modal for editing a material
        $('.edit-btn').on('click', function() {
            var materialId = $(this).data('id');
            $.ajax({
                url: 'api_islemleri/malzemeler_islemler.php?action=get_material&id=' + materialId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var material = response.data;
                        $('#materialForm')[0].reset();
                        $('#modalTitle').text('Malzemeyi Düzenle');
                        $('#action').val('update_material');
                        $('#malzeme_kodu').val(material.malzeme_kodu);
                        $('#malzeme_ismi').val(material.malzeme_ismi);
                        $('#malzeme_turu').val(material.malzeme_turu);
                        $('#stok_miktari').val(material.stok_miktari);
                        // Normalize birim value for form
                        let birimValue = material.birim;
                        // Handle possible enum index values
                        switch(material.birim) {
                            case '1': birimValue = 'adet'; break;
                            case '2': birimValue = 'kg'; break;
                            case '3': birimValue = 'gr'; break;
                            case '4': birimValue = 'lt'; break;
                            case '5': birimValue = 'ml'; break;
                            case '6': birimValue = 'm'; break;
                            case '7': birimValue = 'cm'; break;
                        }
                        $('#birim').val(birimValue);
                        $('#termin_suresi').val(material.termin_suresi);
                        $('#kritik_stok_seviyesi').val(material.kritik_stok_seviyesi);
                        // Set depo and initialize raf list
                        $('#depo').val(material.depo);

                        // Manually load raf list for the selected depo
                        loadRafList(material.depo);

                        // Wait for raf list to load before setting raf value
                        setTimeout(function() {
                            $('#raf').val(material.raf);
                        }, 200);
                        $('#not_bilgisi').val(material.not_bilgisi);
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#materialModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Malzeme bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#materialForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                url: 'api_islemleri/malzemeler_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#materialModal').modal('hide');
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

        // Handle material deletion
        $('.delete-btn').on('click', function() {
            var materialId = $(this).data('id');
            if (confirm('Bu malzemeyi silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/malzemeler_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_material',
                        malzeme_kodu: materialId
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

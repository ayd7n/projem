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

// Fetch all framework contracts for display
$contracts_query = "SELECT * FROM cerceve_sozlesmeler ORDER BY olusturulma_tarihi DESC";
$contracts_result = $connection->query($contracts_query);

// Calculate total contracts
$total_result = $connection->query("SELECT COUNT(*) as total FROM cerceve_sozlesmeler");
$total_contracts = $total_result->fetch_assoc()['total'] ?? 0;

// Calculate active contracts
$active_result = $connection->query("SELECT COUNT(*) as total FROM cerceve_sozlesmeler WHERE durum = 'aktif'");
$active_contracts = $active_result->fetch_assoc()['total'] ?? 0;

// Fetch all suppliers for dropdown
$suppliers_query = "SELECT * FROM tedarikciler ORDER BY tedarikci_adi";
$suppliers_result = $connection->query($suppliers_query);

// Fetch all materials for dropdown
$materials_query = "SELECT * FROM malzemeler ORDER BY malzeme_ismi";
$materials_result = $connection->query($materials_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Çerçeve Sözleşmeler - Parfüm ERP</title>
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
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-primary { background-color: #007bff; color: white; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Çerçeve Sözleşmeler Yönetimi</h1>
            <p>Tedarikçilerle yapılan çerçeve sözleşmeleri yönetin</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-6">
                <button id="addContractBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Sözleşme Ekle</button>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat-card mb-3">
                            <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-file-contract"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $total_contracts; ?></h3>
                                <p>Toplam Sözleşme</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card mb-3">
                            <div class="stat-icon" style="background: var(--success)"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $active_contracts; ?></h3>
                                <p>Aktif Sözleşme</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Sözleşme Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>İşlemler</th>
                                <th>ID</th>
                                <th>Tedarikçi</th>
                                <th>Malzeme</th>
                                <th>Birim Fiyat</th>
                                <th>Para Birimi</th>
                                <th>Sözleşme Türü</th>
                                <th>Toplam Miktar</th>
                                <th>Kalan Miktar</th>
                                <th>Başlangıç</th>
                                <th>Bitiş</th>
                                <th>Durum</th>
                                <th>Oluşturulma</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($contracts_result && $contracts_result->num_rows > 0): ?>
                                <?php while ($contract = $contracts_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $contract['sozlesme_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $contract['sozlesme_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                        <td><?php echo $contract['sozlesme_id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($contract['tedarikci_adi']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($contract['malzeme_ismi']); ?></td>
                                        <td><?php echo number_format($contract['birim_fiyat'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($contract['para_birimi']); ?></td>
                                        <td>
                                            <?php
                                            switch($contract['sozlesme_turu']) {
                                                case 'pesin_odemeli_adet_anlasmasi': echo 'Peşin Ödemeli'; break;
                                                case 'sureli_birim_fiyatli': echo 'Süreli Birim Fiyatlı'; break;
                                                case 'suresiz_birim_fiyatli': echo 'Süresiz Birim Fiyatlı'; break;
                                                default: echo htmlspecialchars($contract['sozlesme_turu']); break;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo number_format($contract['toplam_anlasilan_miktar'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $contract['kalan_anlasilan_miktar'] > 0 ? 'primary' : 'secondary'; ?>">
                                                <?php echo number_format($contract['kalan_anlasilan_miktar'], 2); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date("d.m.Y", strtotime($contract['baslangic_tarihi'])); ?></td>
                                        <td><?php echo date("d.m.Y", strtotime($contract['bitis_tarihi'])); ?></td>
                                        <td>
                                            <?php
                                            switch($contract['durum']) {
                                                case 'aktif':
                                                    echo '<span class="badge badge-success">Aktif</span>';
                                                    break;
                                                case 'tamamlandi':
                                                    echo '<span class="badge badge-info">Tamamlandı</span>';
                                                    break;
                                                case 'iptal_edildi':
                                                    echo '<span class="badge badge-danger">İptal Edildi</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge badge-secondary">' . htmlspecialchars($contract['durum']) . '</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date("d.m.Y H:i", strtotime($contract['olusturulma_tarihi'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="13" class="text-center p-4">Henüz kayıtlı sözleşme bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Modal -->
    <div class="modal fade" id="contractModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="contractForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Sözleşme Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="sozlesme_id" name="sozlesme_id">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tedarikci_id">Tedarikçi *</label>
                                <select class="form-control" id="tedarikci_id" name="tedarikci_id" required>
                                    <option value="">Tedarikçi Seçin</option>
                                    <?php
                                    $suppliers_result->data_seek(0);
                                    while($supplier = $suppliers_result->fetch_assoc()): ?>
                                        <option value="<?php echo $supplier['tedarikci_id']; ?>">
                                            <?php echo htmlspecialchars($supplier['tedarikci_adi']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="malzeme_kodu">Malzeme *</label>
                                <select class="form-control" id="malzeme_kodu" name="malzeme_kodu" required>
                                    <option value="">Malzeme Seçin</option>
                                    <?php
                                    $materials_result->data_seek(0);
                                    while($material = $materials_result->fetch_assoc()): ?>
                                        <option value="<?php echo $material['malzeme_kodu']; ?>">
                                            <?php echo $material['malzeme_kodu']; ?> - <?php echo htmlspecialchars($material['malzeme_ismi']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="birim_fiyat">Birim Fiyat *</label>
                                <input type="number" class="form-control" id="birim_fiyat" name="birim_fiyat" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="para_birimi">Para Birimi *</label>
                                <select class="form-control" id="para_birimi" name="para_birimi" required>
                                    <option value="TL">TL</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sozlesme_turu">Sözleşme Türü *</label>
                                <select class="form-control" id="sozlesme_turu" name="sozlesme_turu" required>
                                    <option value="pesin_odemeli_adet_anlasmasi">Peşin Ödemeli Adet Anlaşması</option>
                                    <option value="sureli_birim_fiyatli">Süreli Birim Fiyatlı</option>
                                    <option value="suresiz_birim_fiyatli">Süresiz Birim Fiyatlı</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="toplam_anlasilan_miktar">Toplam Anlaşilan Miktar *</label>
                                <input type="number" class="form-control" id="toplam_anlasilan_miktar" name="toplam_anlasilan_miktar" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="baslangic_tarihi">Başlangıç Tarihi *</label>
                                <input type="date" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi" required>
                            </div>
                            <div class="form-group">
                                <label for="bitis_tarihi">Bitiş Tarihi *</label>
                                <input type="date" class="form-control" id="bitis_tarihi" name="bitis_tarihi" required>
                            </div>
                            <div class="form-group">
                                <label for="durum">Durum *</label>
                                <select class="form-control" id="durum" name="durum" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="tamamlandi">Tamamlandı</option>
                                    <option value="iptal_edildi">İptal Edildi</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pesin_odeme_yapildi_mi" name="pesin_odeme_yapildi_mi">
                                    <label class="form-check-label" for="pesin_odeme_yapildi_mi">
                                        Peşin Ödeme Yapıldı mı?
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="toplam_pesin_odeme_tutari">Toplam Peşin Ödeme Tutarı</label>
                                <input type="number" class="form-control" id="toplam_pesin_odeme_tutari" name="toplam_pesin_odeme_tutari" step="0.01" min="0">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="aciklama">Açıklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea>
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

        // Open modal for adding a new contract
        $('#addContractBtn').on('click', function() {
            $('#contractForm')[0].reset();
            $('#modalTitle').text('Yeni Sözleşme Ekle');
            $('#action').val('add_contract');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#contractModal').modal('show');
        });

        // Open modal for editing a contract
        $('.edit-btn').on('click', function() {
            var contractId = $(this).data('id');

            $.ajax({
                url: 'api_islemleri/sozlesme_islemler.php?action=get_contract&id=' + contractId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var contract = response.data;
                        $('#contractForm')[0].reset();
                        $('#modalTitle').text('Sözleşmeyi Düzenle');
                        $('#action').val('update_contract');
                        $('#sozlesme_id').val(contract.sozlesme_id);
                        $('#tedarikci_id').val(contract.tedarikci_id);
                        $('#malzeme_kodu').val(contract.malzeme_kodu);
                        $('#birim_fiyat').val(contract.birim_fiyat);
                        $('#para_birimi').val(contract.para_birimi);
                        $('#sozlesme_turu').val(contract.sozlesme_turu);
                        $('#toplam_anlasilan_miktar').val(contract.toplam_anlasilan_miktar);
                        $('#baslangic_tarihi').val(contract.baslangic_tarihi);
                        $('#bitis_tarihi').val(contract.bitis_tarihi);
                        $('#pesin_odeme_yapildi_mi').prop('checked', contract.pesin_odeme_yapildi_mi == 1);
                        $('#toplam_pesin_odeme_tutari').val(contract.toplam_pesin_odeme_tutari);
                        $('#durum').val(contract.durum);
                        $('#aciklama').val(contract.aciklama);
                        $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#contractModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Sözleşme bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#contractForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'api_islemleri/sozlesme_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#contractModal').modal('hide');
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

        // Handle contract deletion
        $('.delete-btn').on('click', function() {
            var contractId = $(this).data('id');

            if (confirm('Bu sözleşmeyi silmek istediğinizden emin misiniz?')) {
                $.ajax({
                    url: 'api_islemleri/sozlesme_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'delete_contract',
                        sozlesme_id: contractId
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

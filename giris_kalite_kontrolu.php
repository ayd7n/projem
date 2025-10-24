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

$message = '';
$error = '';

// Handle form submissions (for non-JS fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        // Create new incoming quality control record
        $tedarikci_id = $_POST['tedarikci_id'];
        $malzeme_kodu = $_POST['malzeme_kodu'];
        $red_edilen_miktar = $_POST['red_edilen_miktar'];
        $red_nedeni = $_POST['red_nedeni'];
        $ilgili_belge_no = $_POST['ilgili_belge_no'];
        $aciklama = $_POST['aciklama'];
        
        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $tedarikci_stmt = $connection->prepare($tedarikci_query);
        $tedarikci_stmt->bind_param('i', $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];
        
        // Get malzeme details
        $malzeme_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
        $malzeme_stmt = $connection->prepare($malzeme_query);
        $malzeme_stmt->bind_param('i', $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        $birim = $malzeme['birim'];
        
        $query = "INSERT INTO giris_kalite_kontrolu (tedarikci_id, tedarikci_adi, malzeme_kodu, malzeme_ismi, birim, reddedilen_miktar, red_nedeni, kontrol_eden_personel_id, kontrol_eden_personel_adi, ilgili_belge_no, aciklama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('issssdssiss', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim, $red_edilen_miktar, $red_nedeni, $_SESSION['id'], $_SESSION['kullanici_adi'], $ilgili_belge_no, $aciklama);
        
        if ($stmt->execute()) {
            $message = "Giris kalite kontrolu kaydi basariyla olusturuldu.";
        } else {
            $error = "Giris kalite kontrolu kaydi olusturulurken hata olustu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['update'])) {
        // Update incoming quality control record
        $kontrol_id = $_POST['kontrol_id'];
        $tedarikci_id = $_POST['tedarikci_id'];
        $malzeme_kodu = $_POST['malzeme_kodu'];
        $red_edilen_miktar = $_POST['red_edilen_miktar'];
        $red_nedeni = $_POST['red_nedeni'];
        $ilgili_belge_no = $_POST['ilgili_belge_no'];
        $aciklama = $_POST['aciklama'];
        
        // Get tedarikci name
        $tedarikci_query = "SELECT tedarikci_adi FROM tedarikciler WHERE tedarikci_id = ?";
        $tedarikci_stmt = $connection->prepare($tedarikci_query);
        $tedarikci_stmt->bind_param('i', $tedarikci_id);
        $tedarikci_stmt->execute();
        $tedarikci_result = $tedarikci_stmt->get_result();
        $tedarikci = $tedarikci_result->fetch_assoc();
        $tedarikci_adi = $tedarikci['tedarikci_adi'];
        
        // Get malzeme details
        $malzeme_query = "SELECT malzeme_ismi, birim FROM malzemeler WHERE malzeme_kodu = ?";
        $malzeme_stmt = $connection->prepare($malzeme_query);
        $malzeme_stmt->bind_param('i', $malzeme_kodu);
        $malzeme_stmt->execute();
        $malzeme_result = $malzeme_stmt->get_result();
        $malzeme = $malzeme_result->fetch_assoc();
        $malzeme_ismi = $malzeme['malzeme_ismi'];
        $birim = $malzeme['birim'];
        
        $query = "UPDATE giris_kalite_kontrolu SET tedarikci_id = ?, tedarikci_adi = ?, malzeme_kodu = ?, malzeme_ismi = ?, birim = ?, reddedilen_miktar = ?, red_nedeni = ?, ilgili_belge_no = ?, aciklama = ? WHERE kontrol_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('issssdsssi', $tedarikci_id, $tedarikci_adi, $malzeme_kodu, $malzeme_ismi, $birim, $red_edilen_miktar, $red_nedeni, $ilgili_belge_no, $aciklama, $kontrol_id);
        
        if ($stmt->execute()) {
            $message = "Giris kalite kontrolu kaydi basariyla guncellendi.";
        } else {
            $error = "Giris kalite kontrolu kaydi guncellenirken hata olustu: " . $connection->error;
        }
        $stmt->close();
    } 
    elseif (isset($_POST['delete'])) {
        // Delete incoming quality control record
        $kontrol_id = $_POST['kontrol_id'];
        
        $query = "DELETE FROM giris_kalite_kontrolu WHERE kontrol_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('i', $kontrol_id);
        
        if ($stmt->execute()) {
            $message = "Giris kalite kontrolu kaydi basariyla silindi.";
        } else {
            $error = "Giris kalite kontrolu kaydi silinirken hata olustu: " . $connection->error;
        }
        $stmt->close();
    }
}

// Fetch all quality control records
$controls_query = "SELECT * FROM giris_kalite_kontrolu ORDER BY tarih DESC";
$controls_result = $connection->query($controls_query);

// Calculate total quality control records
$total_result = $connection->query("SELECT COUNT(*) as total FROM giris_kalite_kontrolu");
$total_controls = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Giris Kalite Kontrolu - Parfüm ERP</title>
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
        
        @media (max-width: 768px) {
            .main-content { padding: 0; }
        }
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
        .disabled-row { opacity: 0.6; }
        .disabled-row .actions button { pointer-events: none; opacity: 0.5; }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        .status-eksik { background-color: #ffc107; color: #000; }
        .status-gedikli { background-color: #fd7e14; }
        .status-kirlenmis { background-color: #6f42c1; }
        .status-yanlis-malzeme { background-color: #e83e8c; }
        .status-diger { background-color: #6c757d; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Giris Kalite Kontrolu</h1>
            <p>Tedarik edilen malzemelerin kalite kontrolunu yonetin</p>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addControlBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Kalite Kontrol Kaydi Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-clipboard-check"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_controls; ?></h3>
                        <p>Toplam Kontrol Kaydi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Kalite Kontrol Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Islemler</th>
                                <th>ID</th>
                                <th>Tarih</th>
                                <th>Tedarikci</th>
                                <th>Malzeme</th>
                                <th>Birim</th>
                                <th>Red Miktari</th>
                                <th>Red Nedeni</th>
                                <th>Belge No</th>
                                <th>Kontrol Eden</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($controls_result && $controls_result->num_rows > 0): ?>
                                <?php while ($control = $controls_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm edit-btn" 
                                                    data-id="<?php echo $control['kontrol_id']; ?>" 
                                                    title="Duzenle">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-btn" 
                                                    data-id="<?php echo $control['kontrol_id']; ?>" 
                                                    title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                        <td><?php echo $control['kontrol_id']; ?></td>
                                        <td><?php echo $control['tarih']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($control['tedarikci_adi']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($control['malzeme_ismi']); ?></td>
                                        <td><?php echo htmlspecialchars($control['birim']); ?></td>
                                        <td><?php echo $control['reddedilen_miktar']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo str_replace('_', '-', $control['red_nedeni']); ?>">
                                                <?php 
                                                switch($control['red_nedeni']) {
                                                    case 'eksik': echo 'Eksik'; break;
                                                    case 'gedikli': echo 'Gedikli/Bozuk'; break;
                                                    case 'kirlenmis': echo 'Kirli/Kirlenmis'; break;
                                                    case 'yanlis_malzeme': echo 'Yanlis Malzeme'; break;
                                                    case 'diger': echo 'Diger'; break;
                                                    default: echo htmlspecialchars($control['red_nedeni']); break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($control['ilgili_belge_no']); ?></td>
                                        <td><?php echo htmlspecialchars($control['kontrol_eden_personel_adi']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center p-4">Henuz kalite kontrol kaydi bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Control Modal -->
    <div class="modal fade" id="controlModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="controlForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Kalite Kontrol Formu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="kontrol_id" name="kontrol_id">
                        <input type="hidden" id="action" name="action">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tedarikci_id">Tedarikci *</label>
                                <select class="form-control" id="tedarikci_id" name="tedarikci_id" required>
                                    <option value="">Tedarikci Secin</option>
                                    <?php
                                    $suppliers_query = "SELECT * FROM tedarikciler ORDER BY tedarikci_adi";
                                    $suppliers_result = $connection->query($suppliers_query);
                                    while($supplier = $suppliers_result->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $supplier['tedarikci_id']; ?>">
                                            <?php echo htmlspecialchars($supplier['tedarikci_adi']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="malzeme_kodu">Malzeme *</label>
                                <select class="form-control" id="malzeme_kodu" name="malzeme_kodu" required>
                                    <option value="">Malzeme Secin</option>
                                    <?php
                                    $materials_query = "SELECT * FROM malzemeler ORDER BY malzeme_ismi";
                                    $materials_result = $connection->query($materials_query);
                                    while($material = $materials_result->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $material['malzeme_kodu']; ?>">
                                            <?php echo $material['malzeme_kodu']; ?> - <?php echo htmlspecialchars($material['malzeme_ismi']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="red_edilen_miktar">Red Edilen Miktar *</label>
                                <input type="number" class="form-control" id="red_edilen_miktar" name="red_edilen_miktar" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="red_nedeni">Red Nedeni *</label>
                                <select class="form-control" id="red_nedeni" name="red_nedeni" required>
                                    <option value="">Red Nedeni Secin</option>
                                    <option value="eksik">Eksik</option>
                                    <option value="gedikli">Gedikli/Bozuk</option>
                                    <option value="kirlenmis">Kirli/Kirlenmis</option>
                                    <option value="yanlis_malzeme">Yanlis Malzeme</option>
                                    <option value="diger">Diger</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ilgili_belge_no">Ilgili Belge No</label>
                                <input type="text" class="form-control" id="ilgili_belge_no" name="ilgili_belge_no">
                            </div>
                            <div class="form-group">
                                <label for="aciklama">Aciklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Iptal</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

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

        // Open modal for adding a new quality control record
        $('#addControlBtn').on('click', function() {
            $('#controlForm')[0].reset();
            $('#modalTitle').text('Yeni Kalite Kontrol Kaydi Ekle');
            $('#action').val('add_control');
            $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
            $('#controlModal').modal('show');
        });

        // Open modal for editing a quality control record
        $('.edit-btn').on('click', function() {
            var kontrolId = $(this).data('id');
            
            $.ajax({
                url: 'api_islemleri/giris_kalite_kontrolu_islemler.php?action=get_control&id=' + kontrolId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var control = response.data;
                        $('#controlForm')[0].reset();
                        $('#modalTitle').text('Kalite Kontrol Kaydini Duzenle');
                        $('#action').val('update_control');
                        $('#kontrol_id').val(control.kontrol_id);
                        $('#tedarikci_id').val(control.tedarikci_id);
                        $('#malzeme_kodu').val(control.malzeme_kodu);
                        $('#red_edilen_miktar').val(control.reddedilen_miktar);
                        $('#red_nedeni').val(control.red_nedeni);
                        $('#ilgili_belge_no').val(control.ilgili_belge_no);
                        $('#aciklama').val(control.aciklama);
                        $('#submitBtn').text('Guncelle').removeClass('btn-primary').addClass('btn-success');
                        $('#controlModal').modal('show');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Kalite kontrol bilgileri alinirken bir hata olustu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#controlForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            
            $.ajax({
                url: 'api_islemleri/giris_kalite_kontrolu_islemler.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#controlModal').modal('hide');
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

        // Handle quality control deletion
        $('.delete-btn').on('click', function() {
            var kontrolId = $(this).data('id');

            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu kalite kontrol kaydini silmek istediginizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api_islemleri/giris_kalite_kontrolu_islemler.php',
                        type: 'POST',
                        data: {
                            action: 'delete_control',
                            kontrol_id: kontrolId
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
        });
    });
    </script>
    
    <!-- Navigation button -->
    <div class="main-content">
        <div class="text-center">
            <a href="navigation.php" class="btn btn-secondary" style="background-color: var(--dark); margin-top: 20px;">Ana Sayfaya Don</a>
        </div>
    </div>
    
</body>
</html>

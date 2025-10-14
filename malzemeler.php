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
    <title>Malzeme Yönetimi - Parfüm ERP</title>
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
        .product-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-name {
            font-weight: 500;
            font-size: 1.05rem;
            color: var(--primary);
        }
        .add-to-cart-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .quantity-input {
            width: 70px;
            padding: 0.6rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 0.9rem;
            text-align: center;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .quantity-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        .cart-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .item-info h4 {
            margin-bottom: 5px;
        }
        .item-quantity {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        /* Cart panel that slides from right */
        #sepet {
            position: fixed;
            top: 0;
            right: 0;
            width: 320px;
            height: 100%;
            z-index: 1050;
            border-radius: 0;
            box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        #sepet.show {
            transform: translateX(0);
        }
        
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }
        
        .cart-overlay.show {
            display: block;
        }
        
        .empty-cart {
            text-align: center;
            padding: 30px 0;
            color: var(--text-secondary);
        }
        .empty-cart i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: var(--primary);
            opacity: 0.3;
        }
        .cart-total {
            padding: 20px 20px;
            border-top: 2px solid var(--border-color);
            font-size: 1.3rem;
            font-weight: 700;
            text-align: right;
            display: none; /* Hide total since we're hiding pricing */
        }
        
        .order-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .order-filters .btn {
            padding: 8px 12px;
            font-size: 0.85rem;
            border-radius: 20px;
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
        
        .no-orders-container {
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
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-item:last-child {
            border-bottom: none;
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
            #sepet.collapse.show {
                position: fixed;
                top: 0;
                right: 0;
                width: 320px;
                height: 100%;
                z-index: 1050; /* Higher than navbar */
                border-radius: 0;
                box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            }
            #sepet .card-body {
                overflow-y: auto;
                height: 100%;
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
                <h1>Malzeme Yönetimi</h1>
                <p>Malzemeleri ekleyin, düzenleyin ve yönetin</p>
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addMaterialBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Malzeme Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon" style="background: var(--primary); font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white;">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3 style="font-size: 1.5rem; margin: 0;"><?php echo $total_materials; ?></h3>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Toplam Malzeme</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-list"></i> Malzeme Listesi</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-barcode"></i> Kod</th>
                                <th><i class="fas fa-tag"></i> İsim</th>
                                <th><i class="fas fa-box"></i> Tür</th>
                                <th><i class="fas fa-sticky-note"></i> Not</th>
                                <th><i class="fas fa-warehouse"></i> Stok</th>
                                <th><i class="fas fa-ruler"></i> Birim</th>
                                <th><i class="fas fa-clock"></i> Termin</th>
                                <th><i class="fas fa-warehouse"></i> Depo</th>
                                <th><i class="fas fa-cube"></i> Raf</th>
                                <th><i class="fas fa-exclamation-triangle"></i> Kritik Seviye</th>
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
                    <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                        <h5 class="modal-title" id="modalTitle"><i class="fas fa-box-open"></i> Malzeme Formu</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="malzeme_kodu" name="malzeme_kodu">
                        <input type="hidden" id="action" name="action">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="malzeme_ismi">Malzeme İsmi *</label>
                                    <input type="text" class="form-control" id="malzeme_ismi" name="malzeme_ismi" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
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
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="stok_miktari">Stok Miktarı</label>
                                    <input type="number" step="0.01" class="form-control" id="stok_miktari" name="stok_miktari" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
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
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="termin_suresi">Termin Süresi (Gün)</label>
                                    <input type="number" class="form-control" id="termin_suresi" name="termin_suresi" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="depo">Depo</label>
                                    <select class="form-control" id="depo" name="depo">
                                        <option value="">Depo Seçin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="raf">Raf</label>
                                    <select class="form-control" id="raf" name="raf">
                                        <option value="">Önce Depo Seçin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="kritik_stok_seviyesi">Kritik Stok Seviyesi</label>
                                    <input type="number" class="form-control" id="kritik_stok_seviyesi" name="kritik_stok_seviyesi" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="not_bilgisi">Not Bilgisi</label>
                            <textarea class="form-control" id="not_bilgisi" name="not_bilgisi" rows="3"></textarea>
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

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
        
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

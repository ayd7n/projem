<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

// Page-level permission check
if (!yetkisi_var('page:view:cerceve_sozlesmeler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

$contracts_query = "SELECT * FROM cerceve_sozlesmeler_gecerlilik ORDER BY sozlesme_id DESC";
$contracts_result = $connection->query($contracts_query);

// Calculate total contracts
$total_result = $connection->query("SELECT COUNT(*) as total FROM cerceve_sozlesmeler");
$total_contracts = $total_result->fetch_assoc()['total'] ?? 0;

// Calculate pending payment contracts
$pending_payment_query = "SELECT COUNT(*) as total FROM cerceve_sozlesmeler_gecerlilik WHERE toplam_mal_kabul_miktari > IFNULL(toplu_odenen_miktar, 0)";
$pending_payment_result = $connection->query($pending_payment_query);
$pending_payment_count = $pending_payment_result->fetch_assoc()['total'] ?? 0;

$suppliers_result = $connection->query("SELECT tedarikci_id, tedarikci_adi FROM tedarikciler ORDER BY tedarikci_adi");
$materials_result = $connection->query("SELECT malzeme_kodu, malzeme_ismi FROM malzemeler ORDER BY malzeme_ismi");

function display_date($date_string)
{
    if (empty($date_string) || $date_string === '0000-00-00' || $date_string === null) {
        return '-';
    }
    try {
        return date_format(date_create($date_string), 'd.m.Y');
    } catch (Exception $e) {
        return '-';
    }
}
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
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top"
        style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" style="color: var(--accent, #d4af37); font-weight: 700;" href="navigation.php"><i
                    class="fas fa-spa"></i> IDO KOZMETIK</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['kullanici_adi'] ?? 'Kullanıcı'); ?>
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
                <h1>Çerçeve Sözleşmeler</h1>
                <p>Çerçeve sözleşmeleri yönetin ve izleyin</p>
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="card">
            <div class="card-header d-flex justify-content-start align-items-center py-2 px-3" style="gap: 8px;">
                <!-- Butonlar -->
                <?php if (yetkisi_var('action:cerceve_sozlesmeler:create')): ?>
                        <button id="addContractBtn" class="btn btn-primary btn-sm"
                            style="font-size: 0.75rem; padding: 4px 10px;"><i class="fas fa-plus"></i> Yeni Sözleşme</button>
                <?php endif; ?>
                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#bilgiModal"
                    style="font-size: 0.75rem; padding: 4px 10px;"><i class="fas fa-info-circle"></i> Nasıl
                    Çalışır?</button>

                <!-- Arama Kutusu -->
                <div class="input-group input-group-sm" style="width: auto; min-width: 250px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text" style="padding: 4px 8px;"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" class="form-control form-control-sm" id="contractSearch"
                        placeholder="Sözleşme ara (Tedarikçi, Malzeme, ID...)"
                        style="font-size: 0.75rem; padding: 4px 8px;">
                </div>
                <!-- Stat Kartları -->
                <div class="stat-card-mini"
                    style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #4a0e63, #7c2a99); color: white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                    <i class="fas fa-file-contract mr-1"></i>
                    <span id="visibleContractCount" style="font-weight: 600;"><?php echo $total_contracts; ?></span>
                    <span class="ml-1" style="opacity: 0.9;">Sözleşme</span>
                </div>
                <div class="stat-card-mini" id="filterPendingPayment"
                    style="padding: 4px 10px; border-radius: 6px; background: linear-gradient(135deg, #f6ad55, #ed8936); color: white; display: inline-flex; align-items: center; cursor: pointer; font-size: 0.75rem;"
                    title="Ödeme bekleyenleri filtrele">
                    <i class="fas fa-hourglass-half mr-1"></i>
                    <span id="visiblePendingCount"
                        style="font-weight: 600;"><?php echo $pending_payment_count; ?></span>
                    <span class="ml-1" style="opacity: 0.9;">Ödeme Bekleyen</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cogs"></i> İşlemler</th>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-building"></i> Tedarikçi</th>
                                <th><i class="fas fa-box"></i> Malzeme</th>
                                <th><i class="fas fa-tag"></i> Fiyat</th>
                                <th><i class="fas fa-coins"></i> Birim</th>
                                <th><i class="fas fa-chart-bar"></i> Limit</th>
                                <th><i class="fas fa-check"></i> Ödenen</th>
                                <th><i class="fas fa-exclamation-circle"></i> Yapılacak Ödeme</th>
                                <th><i class="fas fa-calendar-plus"></i> Başlangıç</th>
                                <th><i class="fas fa-calendar-times"></i> Bitiş</th>
                                <th><i class="fas fa-user"></i> Oluşturan</th>
                                <th><i class="fas fa-sort-numeric-up"></i> Öncelik</th>
                                <th><i class="fas fa-box-open"></i> Toplam Mal Kabul</th>
                                <th><i class="fas fa-chart-line"></i> Kalan Miktar</th>
                                <th><i class="fas fa-info-circle"></i> Geçerlilik Durumu</th>
                                <th><i class="fas fa-check-circle"></i> Kullanılabilirlik</th>
                                <th><i class="fas fa-comment"></i> Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($contracts_result && $contracts_result->num_rows > 0): ?>
                                    <?php while ($contract = $contracts_result->fetch_assoc()):
                                        $odenen = $contract['toplu_odenen_miktar'] ?? 0;
                                        $kabul = $contract['toplam_mal_kabul_miktari'] ?? 0;
                                        $fark = $kabul - $odenen;
                                        $has_pending_payment = $fark > 0;

                                        $row_class = $contract['gecerli_mi'] ? 'table-success' : 'table-danger';
                                        $payment_class = $has_pending_payment ? 'has-pending-payment' : '';
                                        ?>
                                            <tr class="<?php echo $row_class; ?> <?php echo $payment_class; ?>">
                                                <td class="actions">
                                                    <?php if (yetkisi_var('action:cerceve_sozlesmeler:edit')): ?>
                                                            <button class="btn btn-primary btn-sm edit-btn"
                                                                data-id="<?php echo $contract['sozlesme_id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                    <?php endif; ?>
                                                    <?php if (yetkisi_var('action:cerceve_sozlesmeler:delete')): ?>
                                                            <button class="btn btn-danger btn-sm delete-btn"
                                                                data-id="<?php echo $contract['sozlesme_id']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-info btn-sm detail-btn"
                                                        data-id="<?php echo $contract['sozlesme_id']; ?>">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                </td>
                                                <td><strong>#<?php echo $contract['sozlesme_id']; ?></strong></td>
                                                <td><strong><?php echo htmlspecialchars($contract['tedarikci_adi']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($contract['malzeme_ismi']); ?></td>
                                                <td><strong><?php echo number_format($contract['birim_fiyat'], 2); ?></strong></td>
                                                <td>
                                                    <?php
                                                    $curr = $contract['para_birimi'];
                                                    if ($curr === 'TL')
                                                        echo '<span class="badge badge-info">₺ TRY</span>';
                                                    elseif ($curr === 'USD')
                                                        echo '<span class="badge badge-success">$ USD</span>';
                                                    elseif ($curr === 'EUR')
                                                        echo '<span class="badge badge-warning">€ EUR</span>';
                                                    ?>
                                                </td>
                                                <td><?php echo $contract['limit_miktar']; ?></td>
                                                <td><?php echo $contract['toplu_odenen_miktar'] ?? 0; ?></td>
                                                <td>
                                                    <?php
                                                    if ($has_pending_payment) {
                                                        echo '<button class="btn btn-primary btn-sm make-payment-btn" 
                                                            data-id="' . $contract['sozlesme_id'] . '" 
                                                            data-quantity="' . $fark . '"
                                                            data-price="' . $contract['birim_fiyat'] . '"
                                                            data-currency="' . $contract['para_birimi'] . '">
                                                            <i class="fas fa-money-bill-wave"></i> ' . $fark . ' Adet Ödeme Yap
                                                    </button>';
                                                    } else {
                                                        echo '<span class="text-success"><i class="fas fa-check"></i> Tamamlandı</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo display_date($contract['baslangic_tarihi']); ?></td>
                                                <td><?php echo display_date($contract['bitis_tarihi']); ?></td>
                                                <td><?php echo htmlspecialchars($contract['olusturan']); ?></td>
                                                <td>
                                                    <span class="badge badge-info"><?php echo $contract['oncelik']; ?></span>
                                                </td>
                                                <td><?php echo $contract['toplam_mal_kabul_miktari']; ?></td>
                                                <td><?php echo $contract['kalan_miktar']; ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = $contract['gecerlilik_durumu'] === 'Gecerli' ? 'badge-success' : 'badge-danger';
                                                    echo '<span class="badge ' . $status_class . '">' . $contract['gecerlilik_durumu'] . '</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $usage_class = $contract['gecerli_mi'] ? 'badge-success' : 'badge-danger';
                                                    $usage_text = $contract['gecerli_mi'] ? 'Kullanilabilir' : 'Kullanilamaz';
                                                    echo '<span class="badge ' . $usage_class . '">' . $usage_text . '</span>';
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($contract['aciklama']); ?></td>
                                            </tr>
                                    <?php endwhile; ?>
                            <?php else: ?>
                                    <tr class="no-contracts-row">
                                        <td colspan="18" class="text-center p-4">
                                            <i class="fas fa-file-contract fa-3x mb-3"
                                                style="color: var(--text-secondary);"></i>
                                            <h4>Henüz Kayıtlı Sözleşme Bulunmuyor</h4>
                                            <p class="text-muted">Yeni bir sözleşme eklemek için yukarıdaki "Yeni Sözleşme Ekle"
                                                butonunu kullanabilirsiniz.</p>
                                        </td>
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
        <div class="modal-dialog modal-xl" role="document">
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

                        <div class="form-row">
                            <div class="form-group col-12 col-lg-4">
                                <label for="tedarikci_id"><i class="fas fa-building"></i> Tedarikçi</label>
                                <select class="form-control" id="tedarikci_id" name="tedarikci_id" required>
                                    <option value="">Seçin</option>
                                    <?php
                                    mysqli_data_seek($suppliers_result, 0);
                                    while ($supplier = $suppliers_result->fetch_assoc()): ?>
                                            <option value="<?php echo $supplier['tedarikci_id']; ?>">
                                                <?php echo htmlspecialchars($supplier['tedarikci_adi']); ?>
                                            </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="malzeme_kodu"><i class="fas fa-box"></i> Malzeme</label>
                                <select class="form-control" id="malzeme_kodu" name="malzeme_kodu" required>
                                    <option value="">Seçin</option>
                                    <?php
                                    mysqli_data_seek($materials_result, 0);
                                    while ($material = $materials_result->fetch_assoc()): ?>
                                            <option value="<?php echo $material['malzeme_kodu']; ?>">
                                                <?php echo $material['malzeme_kodu'] . ' - ' . htmlspecialchars($material['malzeme_ismi']); ?>
                                            </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="birim_fiyat"><i class="fas fa-tag"></i> Birim Fiyat</label>
                                <input type="number" class="form-control" id="birim_fiyat" name="birim_fiyat"
                                    step="0.01" min="0" required placeholder="0.00">
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="para_birimi"><i class="fas fa-coins"></i> Para Birimi</label>
                                <select class="form-control" id="para_birimi" name="para_birimi" required>
                                    <option value="TL">₺ TRY</option>
                                    <option value="USD">$ USD</option>
                                    <option value="EUR">€ EUR</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="limit_miktar"><i class="fas fa-chart-bar"></i> Limit</label>
                                <input type="number" class="form-control" id="limit_miktar" name="limit_miktar" step="1"
                                    min="0" required placeholder="0">
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="toplu_odenen_miktar"><i class="fas fa-check"></i> Ödenen Miktar</label>
                                <input type="number" class="form-control" id="toplu_odenen_miktar"
                                    name="toplu_odenen_miktar" step="1" min="0" value="0" placeholder="0">
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="baslangic_tarihi"><i class="fas fa-calendar-plus"></i> Başlangıç</label>
                                <input type="date" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi">
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="bitis_tarihi"><i class="fas fa-calendar-times"></i> Bitiş</label>
                                <input type="date" class="form-control" id="bitis_tarihi" name="bitis_tarihi">
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="oncelik"><i class="fas fa-sort-numeric-up"></i> Öncelik</label>
                                <select class="form-control" id="oncelik" name="oncelik" required>
                                    <option value="1">1 - En Yüksek</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5 - En Düşük</option>
                                </select>
                            </div>
                            <div class="form-group col-12" style="grid-column: 1 / -1;">
                                <label for="aciklama"><i class="fas fa-comment"></i> Açıklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="2"></textarea>
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
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Simple Custom Styles for SweetAlert -->
    <style>
        /* Disable word wrap for all table cells */
        table th,
        table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.8rem;
        }

        .stat-card {
            transition: all 0.2s ease-in-out;
        }

        .stat-card .stat-info p {
            /* Target the specific paragraph */
            white-space: nowrap;
        }

        .stat-card.active-filter {
            border: 2px solid #4a0e63;
            box-shadow: 0 0 15px rgba(74, 14, 99, 0.4);
            transform: translateY(-3px);
        }

        /* SweetAlert2 stilleri kaldırıldı, artık Bootstrap modal kullanılıyor */
    </style>

    <!-- Custom Styles for SweetAlert -->
    <style>
        .swal2-popup {
            max-width: 90vw !important;
            border-radius: 12px !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25) !important;
            font-family: 'Ubuntu', sans-serif !important;
        }

        .swal2-title {
            font-size: 1.3rem !important;
            font-weight: 700 !important;
            color: #2c3e50 !important;
            padding: 15px 25px 5px 25px !important;
            text-align: center !important;
            border-bottom: 2px solid #f0f0f0 !important;
            margin-bottom: 15px !important;
        }

        .swal2-content {
            max-height: 80vh !important;
            overflow-y: hidden !important;
            padding: 0 25px 15px 25px !important;
        }

        .my-swal-container .swal2-popup {
            max-height: 90vh !important;
        }

        .my-swal-content {
            max-height: 75vh !important;
            overflow-y: auto !important;
        }

        /* Table styles inside SweetAlert */
        .swal2-popup table {
            border-collapse: collapse;
            margin-bottom: 0;
            width: 100%;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
        }

        .swal2-popup table th,
        .swal2-popup table td {
            border: 1px solid #e0e0e0;
            padding: 0.8rem;
            vertical-align: middle;
            font-size: 0.95rem;
            text-align: center;
        }

        .swal2-popup table th {
            background: linear-gradient(135deg, #4a0e63 0%, #7c2a99 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .swal2-popup table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .swal2-popup table tbody tr:hover {
            background-color: #f0e6f7;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        .swal2-popup table thead th {
            position: sticky;
            top: 0;
            background: linear-gradient(135deg, #4a0e63 0%, #7c2a99 100%);
            color: white;
            z-index: 10;
            font-weight: 600;
            padding: 1rem;
        }

        /* Scrollbar styling */
        .swal2-popup ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .swal2-popup ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        .swal2-popup ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 5px;
        }

        .swal2-popup ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        /* Confirm button styling */
        .swal2-confirm {
            background: linear-gradient(135deg, #4a0e63 0%, #7c2a99 100%) !important;
            border-radius: 30px !important;
            padding: 12px 35px !important;
            font-weight: 600 !important;
            font-size: 1rem !important;
            letter-spacing: 0.5px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 15px rgba(74, 14, 99, 0.3) !important;
            border: none !important;
            margin: 0 10px !important;
        }

        .swal2-confirm:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 20px rgba(74, 14, 99, 0.4) !important;
        }

        .swal2-confirm:active {
            transform: translateY(-1px) !important;
        }

        /* Cancel button styling */
        .swal2-cancel {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
            border-radius: 30px !important;
            padding: 12px 35px !important;
            font-weight: 600 !important;
            font-size: 1rem !important;
            letter-spacing: 0.5px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3) !important;
            border: none !important;
            margin: 0 10px !important;
            color: white !important;
        }

        .swal2-cancel:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4) !important;
            background: linear-gradient(135deg, #5a6268 0%, #343a40 100%) !important;
        }

        .swal2-cancel:active {
            transform: translateY(-1px) !important;
        }

        /* Actions container for buttons */
        .swal2-actions {
            margin-top: 20px !important;
            display: flex !important;
            justify-content: center !important;
            gap: 10px !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .swal2-popup {
                max-width: 95vw !important;
                margin: 15px;
            }

            .swal2-popup table th,
            .swal2-popup table td {
                padding: 0.6rem;
                font-size: 0.9rem;
            }

            .swal2-title {
                font-size: 1.1rem !important;
            }

            .swal2-confirm {
                padding: 10px 30px !important;
                font-size: 0.95rem !important;
            }
        }

        @media (max-width: 480px) {

            .swal2-popup table th,
            .swal2-popup table td {
                padding: 0.5rem;
                font-size: 0.85rem;
            }

            .swal2-popup table thead th {
                padding: 0.7rem;
                font-size: 0.8rem;
            }
        }

        /* Loading popup style */
        .swal2-loading-popup {
            border-radius: 15px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2) !important;
        }

        .swal2-loading-popup .swal2-title {
            border-bottom: none !important;
            margin-bottom: 0 !important;
            padding-bottom: 5px !important;
        }

        /* Animation effects */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .swal2-popup {
            animation: fadeIn 0.3s ease-out;
        }

        /* Ensure proper backdrop handling */
        .swal2-container {
            z-index: 9999 !important;
        }

        .swal2-backdrop {
            background-color: rgba(0, 0, 0, 0.4) !important;
        }

        /* Ensure swal2-hide properly hides elements and removes backdrop */
        .swal2-container {
            z-index: 9999 !important;
        }

        .swal2-backdrop-show {
            background: rgba(0, 0, 0, 0.4) !important;
        }

        .swal2-backdrop-hide {
            background: transparent !important;
        }
    </style>

    <script>
        $(document).ready(function () {

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

            // Client-side search/filter functionality
            $('#contractSearch').on('keyup', function () {
                var searchText = $(this).val().toLowerCase().trim();
                var visibleCount = 0;
                var pendingCount = 0;

                $('table tbody tr').each(function () {
                    // Skip the "no contracts" row
                    if ($(this).hasClass('no-contracts-row')) return;

                    var rowText = $(this).text().toLowerCase();

                    if (searchText === '' || rowText.indexOf(searchText) !== -1) {
                        $(this).show();
                        visibleCount++;
                        // Check if this row has pending payment
                        if ($(this).hasClass('has-pending-payment')) {
                            pendingCount++;
                        }
                    } else {
                        $(this).hide();
                    }
                });

                // Update stat counts
                $('#visibleContractCount').text(visibleCount);
                $('#visiblePendingCount').text(pendingCount);
            });

            // Open modal for adding a new contract
            $('#addContractBtn').on('click', function () {
                $('#contractForm')[0].reset();
                $('#modalTitle').text('Yeni Sözleşme Ekle');
                $('#action').val('add_contract');
                $('#submitBtn').text('Ekle').removeClass('btn-success').addClass('btn-primary');
                $('#contractModal').modal('show');
            });

            // Open modal for editing a contract
            $('.edit-btn').on('click', function () {
                var contractId = $(this).data('id');

                $.ajax({
                    url: 'api_islemleri/cerceve_sozlesmeler_islemler.php',
                    type: 'POST',
                    data: { action: 'get_contract', id: contractId },
                    dataType: 'json',
                    success: function (response) {
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
                            $('#limit_miktar').val(contract.limit_miktar);
                            $('#toplu_odenen_miktar').val(contract.toplu_odenen_miktar || 0);
                            $('#baslangic_tarihi').val(contract.baslangic_tarihi);
                            $('#bitis_tarihi').val(contract.bitis_tarihi);
                            $('#aciklama').val(contract.aciklama);
                            $('#submitBtn').text('Güncelle').removeClass('btn-primary').addClass('btn-success');
                            $('#contractModal').modal('show');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function () {
                        showAlert('Sözleşme bilgileri alınırken bir hata oluştu.', 'danger');
                    }
                });
            });

            // Handle form submission
            $('#contractForm').on('submit', function (e) {
                e.preventDefault();
                var btn = $('#submitBtn');
                btn.prop('disabled', true).text('Kaydediliyor...').addClass('btn-secondary').removeClass('btn-primary btn-success');

                var formData = $(this).serialize();

                $.ajax({
                    url: 'api_islemleri/cerceve_sozlesmeler_islemler.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#contractModal').modal('hide');
                            showAlert(response.message, 'success');
                            // Reload page to see changes
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message, 'danger');
                            btn.prop('disabled', false).text('Kaydet').addClass('btn-primary');
                        }
                    },
                    error: function () {
                        showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                        btn.prop('disabled', false).text('Kaydet').addClass('btn-primary');
                    }
                });
            });

            // Handle contract deletion
            $('.delete-btn').on('click', function () {
                var contractId = $(this).data('id');

                Swal.fire({
                    title: 'Emin misiniz?',
                    text: 'Bu sözleşmeyi silmek istediğinizden emin misiniz?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Evet, sil!',
                    cancelButtonText: 'İptal',
                    allowOutsideClick: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'api_islemleri/cerceve_sozlesmeler_islemler.php',
                            type: 'POST',
                            data: {
                                action: 'delete_contract',
                                sozlesme_id: contractId
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.status === 'success') {
                                    showAlert(response.message, 'success');
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000);
                                } else {
                                    showAlert(response.message, 'danger');
                                }
                            },
                            error: function () {
                                showAlert('Silme işlemi sırasında bir hata oluştu.', 'danger');
                            }
                        });
                    } else if (result.dismiss === Swal.DismissReason.cancel || result.dismiss === Swal.DismissReason.backdrop) {
                        // Ensure complete cleanup when cancelled or clicked outside
                        setTimeout(() => {
                            $('body').removeClass('swal2-no-backdrop swal2-shown');
                            $('.swal2-container').remove();
                        }, 100);
                    } else if (result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.escape) {
                        // Additional cleanup for other dismissal methods
                        setTimeout(() => {
                            $('body').removeClass('swal2-no-backdrop swal2-shown');
                            $('.swal2-container').remove();
                        }, 100);
                    }
                });
            });

            // Handle contract detail view with Bootstrap modal
            $(document).on('click', '.detail-btn', function () {
                var contractId = $(this).data('id');

                // Show loading in modal
                $('#detailModal .modal-title').html('Mal Kabul Geçmişi - Sözleşme ID: ' + contractId);
                $('#detailModal .modal-body').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Yükleniyor...</span></div><p class="mt-2">Mal Kabul geçmişi alınıyor...</p></div>');
                $('#detailModal').modal('show');

                $.ajax({
                    url: 'api_islemleri/cerceve_sozlesmeler_islemler.php',
                    type: 'POST',
                    data: {
                        action: 'get_contract_movements',
                        sozlesme_id: contractId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var tableHtml = '';

                            if (response.movements && response.movements.length > 0) {
                                tableHtml = `
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Hareket ID</th>
                                                <th>Miktar</th>
                                                <th>Tarih</th>
                                                <th>Açıklama</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${response.movements.map(m => `
                                                <tr>
                                                    <td>#${m.hareket_id}</td>
                                                    <td><span class="badge badge-primary">${m.miktar}</span></td>
                                                    <td>${m.tarih}</td>
                                                    <td>${m.aciklama}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>`;
                            } else {
                                tableHtml = '<div class="alert alert-info">Bu sözleşmeye ait mal kabul hareketi bulunmamaktadır.</div>';
                            }

                            $('#detailModal .modal-body').html(tableHtml);
                        } else {
                            $('#detailModal .modal-body').html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                    },
                    error: function () {
                        $('#detailModal .modal-body').html('<div class="alert alert-danger">Veriler alınırken bir hata oluştu.</div>');
                    }
                });
            });

            // Handle Make Payment Button
            $(document).on('click', '.make-payment-btn', function () {
                var contractId = $(this).data('id');
                var maxQuantity = $(this).data('quantity');
                var price = $(this).data('price');
                var currency = $(this).data('currency');

                var currencySymbol = '₺';
                if (currency === 'USD') currencySymbol = '$';
                else if (currency === 'EUR') currencySymbol = '€';

                Swal.fire({
                    title: 'Ödeme Yap',
                    html: `
                    <div class="text-left">
                        <div class="form-group">
                            <label>Ödenecek Miktar (Adet)</label>
                            <input type="number" id="payment-amount" class="form-control" value="${maxQuantity}" max="${maxQuantity}" min="1">
                            <small class="text-muted">Maksimum ödenebilir: ${maxQuantity} Adet</small>
                        </div>
                        <div class="form-group">
                            <label>Kasa Seçimi</label>
                            <select id="payment-kasa" class="form-control">
                                <option value="TL">TL Kasası</option>
                                <option value="USD">USD Kasası</option>
                                <option value="EUR">EUR Kasası</option>
                            </select>
                        </div>
                        <p><strong>Birim Fiyat:</strong> ${price} ${currencySymbol}</p>
                        <p class="h5 mt-3"><strong>Toplam Tutar:</strong> <span id="total-amount" class="text-success">${(maxQuantity * price).toFixed(2)} ${currencySymbol}</span></p>
                        <p class="text-muted mt-2 small">Bu işlem onaylandığında Giderler tablosuna "Ara Ödeme" olarak kaydedilecektir.</p>
                    </div>
                `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ödeme Yap',
                    cancelButtonText: 'İptal',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        const input = Swal.getPopup().querySelector('#payment-amount');
                        const totalSpan = Swal.getPopup().querySelector('#total-amount');

                        input.oninput = () => {
                            let val = parseFloat(input.value) || 0;
                            if (val > maxQuantity) {
                                val = maxQuantity;
                                input.value = maxQuantity;
                            }
                            if (val < 0) {
                                val = 0;
                                input.value = 0;
                            }
                            totalSpan.textContent = (val * price).toFixed(2) + ' ' + currencySymbol;
                        };
                    },
                    preConfirm: () => {
                        const quantity = Swal.getPopup().querySelector('#payment-amount').value;
                        const kasa = Swal.getPopup().querySelector('#payment-kasa').value;
                        if (!quantity || quantity <= 0) {
                            Swal.showValidationMessage('Lütfen geçerli bir miktar giriniz');
                        }
                        return { quantity: quantity, kasa: kasa };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const quantity = result.value.quantity;
                        const kasa = result.value.kasa;

                        $.ajax({
                            url: 'api_islemleri/cerceve_sozlesmeler_islemler.php',
                            type: 'POST',
                            data: {
                                action: 'make_payment',
                                sozlesme_id: contractId,
                                quantity: quantity,
                                kasa_secimi: kasa
                            },
                            dataType: 'json',
                            success: function (response) {
                                if (response.status === 'success') {
                                    Swal.fire('Başarılı!', response.message, 'success')
                                        .then(() => {
                                            location.reload();
                                        });
                                } else {
                                    Swal.fire('Hata!', response.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Hata!', 'İşlem sırasında bir hata oluştu.', 'error');
                            }
                        });
                    } else if (result.dismiss === Swal.DismissReason.cancel || result.dismiss === Swal.DismissReason.backdrop) {
                        // Ensure complete cleanup when cancelled or clicked outside
                        setTimeout(() => {
                            $('body').removeClass('swal2-no-backdrop swal2-shown');
                            $('.swal2-container').remove();
                        }, 100);
                    } else if (result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.escape) {
                        // Additional cleanup for other dismissal methods
                        setTimeout(() => {
                            $('body').removeClass('swal2-no-backdrop swal2-shown');
                            $('.swal2-container').remove();
                        }, 100);
                    }
                });
            });

            var isFiltered = false;
            $('#filterPendingPayment').on('click', function () {
                var $this = $(this);
                isFiltered = !isFiltered;

                var $rows = $('tbody tr');

                $('.no-filter-results').remove();

                if (isFiltered) {
                    $this.addClass('active-filter');
                    $rows.hide();
                    var $filteredRows = $rows.filter('.has-pending-payment');
                    $filteredRows.show();

                    if ($filteredRows.length === 0 && $('.no-contracts-row').length === 0) {
                        $('tbody').append('<tr class="no-filter-results"><td colspan="18" class="text-center p-4">Filtreyle eşleşen ödeme bekleyen sözleşme bulunamadı.</td></tr>');
                    }
                } else {
                    $this.removeClass('active-filter');
                    $rows.show();
                }
            });

        });
    </script>

    <!-- Bilgi Modal -->
    <div class="modal fade" id="bilgiModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Çerçeve Sözleşme Sistemi - Nasıl Çalışır?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-exclamation-circle"></i> Sistem Özeti</h6>
                        <p>Çerçeve sözleşme sistemi, tedarikçinizle yaptığınız anlaşmaları takip etmenizi ve Mal Kabul
                            işlemlerinde bu anlaşmaları otomatik olarak kullanmanızı sağlar.</p>
                    </div>

                    <!-- Temel Kavramlar -->
                    <h6><i class="fas fa-book"></i> Temel Kavramlar</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card border-primary h-100">
                                <div class="card-body p-2">
                                    <h6 class="card-title text-primary mb-1"><i class="fas fa-chart-bar"></i> Limit</h6>
                                    <p class="card-text small mb-0">Sözleşmede belirlenen <strong>toplam
                                            miktar</strong>dır. Tedarikçiyle yapılan anlaşmanın üst sınırını gösterir.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-warning h-100">
                                <div class="card-body p-2">
                                    <h6 class="card-title text-warning mb-1"><i class="fas fa-money-bill-wave"></i>
                                        Ödenen</h6>
                                    <p class="card-text small mb-0">Limitin <strong>peşin ödeme yapılan</strong>
                                        kısmıdır. Kalan miktar için ödeme beklenmektedir.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success h-100">
                                <div class="card-body p-2">
                                    <h6 class="card-title text-success mb-1"><i class="fas fa-calendar-check"></i>
                                        Geçerlilik</h6>
                                    <p class="card-text small mb-0"><strong>Limite ulaşılana</strong> veya <strong>süre
                                            dolana</strong> kadar geçerlidir.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6><i class="fas fa-star"></i> Öncelik Sistemi</h6>
                    <p>Her çerçeve sözleşmesine 1-5 arasında bir öncelik seviyesi verilir:</p>
                    <ul>
                        <li><strong>1 - En Yüksek Öncelik</strong>: Bu sözleşmeler Mal Kabul sırasında ilk olarak
                            kullanılır</li>
                        <li><strong>2-5 - Daha Düşük Öncelik</strong>: Daha yüksek öncelikli sözleşmeler tükenince
                            kullanılır</li>
                    </ul>

                    <h6><i class="fas fa-exchange-alt"></i> Bölünmüş Mal Kabul</h6>
                    <p>Sistem, tek bir Mal Kabul işlemini birden fazla sözleşme kullanarak bölerek tamamlayabilir:</p>
                    <ul>
                        <li>Kullanıcı 1000 birim Mal Kabul yapmak isterse</li>
                        <li>Sistem önce öncelik 1 olan sözleşme ile 300 birim kullanır</li>
                        <li>Kalan 700 birimi öncelik 2 olan sözleşme ile tamamlar</li>
                        <li>Bu sayede tüm Mal Kabul işlemleri takip edilebilir olur</li>
                    </ul>

                    <h6><i class="fas fa-search"></i> Takip ve Raporlama</h6>
                    <p>Sistem her Mal Kabul işlemini hangi sözleşmeyle yaptığını kaydeder:</p>
                    <ul>
                        <li><strong>Kalan Miktar:</strong> Her sözleşme için kalan limit doğru şekilde hesaplanır</li>
                        <li><strong>Görsel Gösterim:</strong> Geçerli, Limit Dolmuş, Süresi Dolmuş durumları farklı
                            renklerle gösterilir</li>
                        <li><strong>Sipariş Takibi:</strong> Mal Kabul sırasında hangi sözleşmeyle işlem yapıldığı
                            bilgisi saklanır</li>
                    </ul>

                    <h6><i class="fas fa-check-circle"></i> Mal Kabul İşlemi</h6>
                    <p>Mal Kabul sırasında sistem şu adımları uygular:</p>
                    <ol>
                        <li>Kullanıcının seçtiği tedarikçi ve malzeme için geçerli tüm sözleşmeleri bulur</li>
                        <li>Bulunan sözleşmeleri öncelik sırasına göre sıralar</li>
                        <li>Kullanıcının girdiği miktarı bu sıraya göre dağıtır</li>
                        <li>Her dağıtım için ayrı bir stok hareketi oluşturur</li>
                        <li>Her hareketi hangi sözleşmeyle yapıldığına göre kaydeder</li>
                    </ol>

                    <h6><i class="fas fa-bell"></i> Geçerlilik Kontrolleri</h6>
                    <p>Sistem Mal Kabul sırasında şu kontrolleri yapar:</p>
                    <ul>
                        <li>Sözleşmenin bitiş tarihi geçmiş mi?</li>
                        <li>Sözleşmenin limiti dolmuş mu?</li>
                        <li>Toplam kalan limit kullanıcı miktarını karşılayabiliyor mu?</li>
                        <li>Geçerli sözleşme var mı?</li>
                    </ul>

                    <div class="alert alert-success mt-3">
                        <h6><i class="fas fa-lightbulb"></i> Uygulama Önerisi</h6>
                        <p>En çok kullandığınız tedarikçi anlaşmalarına daha yüksek (düşük sayı) öncelik vererek
                            sistemden en iyi şekilde yararlanabilirsiniz.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detay Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sözleşme Detayı</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Dinamik içerik buraya yüklenecek -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
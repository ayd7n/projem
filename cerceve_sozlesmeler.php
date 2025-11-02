<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

$contracts_query = "SELECT * FROM cerceve_sozlesmeler_gecerlilik ORDER BY olusturulma_tarihi DESC";
$contracts_result = $connection->query($contracts_query);

// Calculate total contracts
$total_result = $connection->query("SELECT COUNT(*) as total FROM cerceve_sozlesmeler");
$total_contracts = $total_result->fetch_assoc()['total'] ?? 0;

$suppliers_result = $connection->query("SELECT tedarikci_id, tedarikci_adi FROM tedarikciler ORDER BY tedarikci_adi");
$materials_result = $connection->query("SELECT malzeme_kodu, malzeme_ismi FROM malzemeler ORDER BY malzeme_ismi");

function display_date($date_string) {
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
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css">
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
                <h1>Çerçeve Sözleşmeler</h1>
                <p>Çerçeve sözleşmeleri yönetin ve izleyin</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), #667eea); width: 50px; height: 50px; min-width: 50px;">
                                <i class="fas fa-file-contract" style="color: white;"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0" style="color: var(--dark);">Çerçeve Sözleşme Bilgisi</h5>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb);">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start">
                                            <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center mr-2" 
                                                 style="background: var(--primary); width: 35px; height: 35px; flex-shrink: 0;">
                                                <i class="fas fa-boxes" style="color: white; font-size: 0.9rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="card-title font-weight-bold mb-1" style="color: var(--primary);">Limit</h6>
                                                <p class="card-text text-muted small mb-0">
                                                    Sözleşmede belirlenen toplam adet miktarıdır. Örneğin 100 adet limit belirlenmişse, 
                                                    tedarikçiyle 100 adet malzeme temini için anlaşma yapıldığını gösterir.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #fff8e1, #ffecb3);">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start">
                                            <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center mr-2" 
                                                 style="background: var(--warning); width: 35px; height: 35px; flex-shrink: 0;">
                                                <i class="fas fa-money-bill-wave" style="color: white; font-size: 0.9rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="card-title font-weight-bold mb-1" style="color: var(--warning);">Ödenen</h6>
                                                <p class="card-text text-muted small mb-0">
                                                    Bu 100 adetlik sınırın içinde peşin olarak ödemesi yapılan adedi belirtir. 
                                                    Yani 100 adet için yapılan anlaşmada 60 adedi ödenmişse, anlaşmanın başından itibaren 
                                                    60 adedinin parası peşin olarak ödendiği, kalan 40 adet için ise ödeme henüz yapılmadığı anlamına gelir.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9);">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start">
                                            <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center mr-2" 
                                                 style="background: var(--success); width: 35px; height: 35px; flex-shrink: 0;">
                                                <i class="fas fa-calendar-check" style="color: white; font-size: 0.9rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="card-title font-weight-bold mb-1" style="color: var(--success);">Sözleşme Geçerliliği</h6>
                                                <p class="card-text text-muted small mb-0">
                                                    Sözleşme, toplam limite ulaşılana kadar veya bitiş tarihi dolana kadar geçerli kalır.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="alert-placeholder"></div>

        <div class="row">
            <div class="col-md-8">
                <button id="addContractBtn" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Yeni Sözleşme Ekle</button>
            </div>
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon" style="background: var(--primary)"><i class="fas fa-file-contract"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_contracts; ?></h3>
                        <p>Toplam Sözleşme</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-table"></i> Sözleşme Listesi</h2>
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
                                <th><i class="fas fa-calendar-plus"></i> Başlangıç</th>
                                <th><i class="fas fa-calendar-times"></i> Bitiş</th>
                                <th><i class="fas fa-user"></i> Oluşturan</th>
                                <th><i class="fas fa-box-open"></i> Toplam Mal Kabul</th>
                                <th><i class="fas fa-chart-line"></i> Kalan Miktar</th>
                                <th><i class="fas fa-info-circle"></i> Geçerlilik Durumu</th>
                                <th><i class="fas fa-check-circle"></i> Kullanılabilirlik</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($contracts_result && $contracts_result->num_rows > 0): ?>
                                <?php while ($contract = $contracts_result->fetch_assoc()): ?>
                                <tr class="<?php echo $contract['gecerli_mi'] ? 'table-success' : 'table-danger'; ?>">
                                    <td class="actions">
                                        <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $contract['sozlesme_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $contract['sozlesme_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                    <td><strong>#<?php echo $contract['sozlesme_id']; ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($contract['tedarikci_adi']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($contract['malzeme_ismi']); ?></td>
                                    <td><strong><?php echo number_format($contract['birim_fiyat'], 2); ?></strong></td>
                                    <td>
                                        <?php 
                                        $curr = $contract['para_birimi'];
                                        if ($curr === 'TL') echo '<span class="badge badge-info">₺ TRY</span>';
                                        elseif ($curr === 'USD') echo '<span class="badge badge-success">$ USD</span>';
                                        elseif ($curr === 'EUR') echo '<span class="badge badge-warning">€ EUR</span>';
                                        ?>
                                    </td>
                                    <td><?php echo $contract['limit_miktar']; ?></td>
                                    <td><?php echo $contract['toplu_odenen_miktar'] ?? 0; ?></td>
                                    <td><?php echo display_date($contract['baslangic_tarihi']); ?></td>
                                    <td><?php echo display_date($contract['bitis_tarihi']); ?></td>
                                    <td><?php echo htmlspecialchars($contract['olusturan']); ?></td>
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
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center p-4">
                                        <i class="fas fa-file-contract fa-3x mb-3" style="color: var(--text-secondary);"></i>
                                        <h4>Henüz Kayıtlı Sözleşme Bulunmuyor</h4>
                                        <p class="text-muted">Yeni bir sözleşme eklemek için yukarıdaki "Yeni Sözleşme Ekle" butonunu kullanabilirsiniz.</p>
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
                                    while($supplier = $suppliers_result->fetch_assoc()): ?>
                                        <option value="<?php echo $supplier['tedarikci_id']; ?>"><?php echo htmlspecialchars($supplier['tedarikci_adi']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="malzeme_kodu"><i class="fas fa-box"></i> Malzeme</label>
                                <select class="form-control" id="malzeme_kodu" name="malzeme_kodu" required>
                                    <option value="">Seçin</option>
                                    <?php 
                                    mysqli_data_seek($materials_result, 0);
                                    while($material = $materials_result->fetch_assoc()): ?>
                                        <option value="<?php echo $material['malzeme_kodu']; ?>"><?php echo $material['malzeme_kodu'] . ' - ' . htmlspecialchars($material['malzeme_ismi']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="birim_fiyat"><i class="fas fa-tag"></i> Birim Fiyat</label>
                                <input type="number" class="form-control" id="birim_fiyat" name="birim_fiyat" step="0.01" min="0" required placeholder="0.00">
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
                                <input type="number" class="form-control" id="limit_miktar" name="limit_miktar" step="1" min="0" required placeholder="0">
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="toplu_odenen_miktar"><i class="fas fa-check"></i> Ödenen Miktar</label>
                                <input type="number" class="form-control" id="toplu_odenen_miktar" name="toplu_odenen_miktar" step="1" min="0" value="0" placeholder="0">
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="baslangic_tarihi"><i class="fas fa-calendar-plus"></i> Başlangıç</label>
                                <input type="date" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi">
                            </div>
                            <div class="form-group col-12 col-lg-4">
                                <label for="bitis_tarihi"><i class="fas fa-calendar-times"></i> Bitiş</label>
                                <input type="date" class="form-control" id="bitis_tarihi" name="bitis_tarihi">
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
                url: 'api_islemleri/cerceve_sozlesmeler_islemler.php',
                type: 'POST',
                data: { action: 'get_contract', id: contractId },
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
                error: function() {
                    showAlert('Sözleşme bilgileri alınırken bir hata oluştu.', 'danger');
                }
            });
        });

        // Handle form submission
        $('#contractForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $('#submitBtn');
            btn.prop('disabled', true).text('Kaydediliyor...').addClass('btn-secondary').removeClass('btn-primary btn-success');
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: 'api_islemleri/cerceve_sozlesmeler_islemler.php',
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
                        btn.prop('disabled', false).text('Kaydet').addClass('btn-primary');
                    }
                },
                error: function() {
                    showAlert('İşlem sırasında bir hata oluştu.', 'danger');
                    btn.prop('disabled', false).text('Kaydet').addClass('btn-primary');
                }
            });
        });

        // Handle contract deletion
        $('.delete-btn').on('click', function() {
            var contractId = $(this).data('id');

            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu sözleşmeyi silmek istediğinizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
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
    });
    </script>
</body>
</html>

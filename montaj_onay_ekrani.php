<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['taraf'] !== 'personel') {
    header('Location: login.php');
    exit;
}

if (!yetkisi_var('page:view:montaj_onay_ekrani')) {
    die('Bu sayfayi goruntuleme yetkiniz yok.');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Montaj Onay Ekrani - Parfum ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/montaj_is_emirleri.css?v=1.2">
    <style>
        .status-onay-bekliyor {
            background: #e8f4ff;
            color: #0b63b6;
            border: 1px solid #b7daf9;
        }
        .selection-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="navbar-text mr-3"><i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cikis Yap</a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="approvalApp" class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-clipboard-check"></i> Montaj Onay Ekrani</h1>
            <p>Onay bekleyen montaj is emirlerini onaylayin veya reddedin.</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-hourglass-half"></i> Onay Bekleyen Is Emirleri</h2>
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary mr-2" @click="fetchPendingWorkOrders(1)">
                        <i class="fas fa-sync-alt"></i> Yenile
                    </button>
                    <a href="montaj_is_emirleri.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Montaj Listesi
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="selection-bar">
                    <div>
                        <strong>Secili Kayit:</strong> {{ selectedIds.length }}
                    </div>
                    <div class="d-flex">
                        <button class="btn btn-success mr-2" :disabled="selectedIds.length === 0" @click="bulkApprove">
                            <i class="fas fa-check"></i> Toplu Onay
                        </button>
                        <button class="btn btn-danger" :disabled="selectedIds.length === 0" @click="bulkReject">
                            <i class="fas fa-times"></i> Toplu Red
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 35px;">
                                    <input type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                                </th>
                                <th>Is Emri No</th>
                                <th>Durum</th>
                                <th>Urun</th>
                                <th>Planlanan</th>
                                <th>Tamamlanan</th>
                                <th>Eksik</th>
                                <th>Birim</th>
                                <th>Onaya Gonderen</th>
                                <th>Onaya Gonderme Tarihi</th>
                                <th>Islemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="11" class="text-center p-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2">Veriler yukleniyor...</div>
                                </td>
                            </tr>
                            <tr v-else-if="workOrders.length === 0">
                                <td colspan="11" class="text-center p-4">Onay bekleyen montaj is emri bulunmuyor.</td>
                            </tr>
                            <tr v-else v-for="workOrder in workOrders" :key="workOrder.is_emri_numarasi">
                                <td>
                                    <input type="checkbox" :value="workOrder.is_emri_numarasi" v-model="selectedIds">
                                </td>
                                <td><strong>{{ workOrder.is_emri_numarasi }}</strong></td>
                                <td>
                                    <span class="status-badge status-onay-bekliyor">Onay Bekliyor</span>
                                </td>
                                <td><strong>{{ workOrder.urun_kodu }} - {{ workOrder.urun_ismi }}</strong></td>
                                <td>{{ formatNumber(workOrder.planlanan_miktar) }}</td>
                                <td>{{ formatNumber(workOrder.tamamlanan_miktar) }}</td>
                                <td>{{ formatNumber(workOrder.eksik_miktar_toplami) }}</td>
                                <td>{{ workOrder.birim }}</td>
                                <td>{{ workOrder.onaya_gonderen_personel_adi || '-' }}</td>
                                <td>{{ workOrder.onaya_gonderme_tarihi || '-' }}</td>
                                <td>
                                    <button class="btn btn-success btn-sm mr-2" @click="openApproveModal(workOrder)">
                                        <i class="fas fa-check"></i> Onayla
                                    </button>
                                    <button class="btn btn-danger btn-sm" @click="rejectSingle(workOrder.is_emri_numarasi)">
                                        <i class="fas fa-times"></i> Reddet
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                    <small>
                        Sayfa {{ pagination.current_page }} / {{ pagination.total_pages }}
                        (Toplam {{ pagination.total }} kayit)
                    </small>
                    <div>
                        <button class="btn btn-sm btn-secondary mr-2"
                            :disabled="pagination.current_page <= 1"
                            @click="fetchPendingWorkOrders(pagination.current_page - 1)">
                            <i class="fas fa-chevron-left"></i> Onceki
                        </button>
                        <button class="btn btn-sm btn-secondary"
                            :disabled="pagination.current_page >= pagination.total_pages"
                            @click="fetchPendingWorkOrders(pagination.current_page + 1)">
                            Sonraki <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" :class="{show: showApproveModal}" v-if="showApproveModal"
            style="display: block; background-color: rgba(0,0,0,0.5);" @click="showApproveModal = false">
            <div class="modal-dialog" @click.stop>
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-check-square"></i> Is Emri Onayla</h5>
                        <button type="button" class="close text-white" @click="showApproveModal = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Is Emri No:</strong> {{ selectedWorkOrder.is_emri_numarasi }}</p>
                        <p><strong>Urun:</strong> {{ selectedWorkOrder.urun_kodu }} - {{ selectedWorkOrder.urun_ismi }}</p>
                        <p><strong>Planlanan Miktar:</strong> {{ formatNumber(selectedWorkOrder.planlanan_miktar) }} {{ selectedWorkOrder.birim }}</p>
                        <div class="form-group">
                            <label><strong>Tamamlanan Miktar</strong></label>
                            <input type="number" step="0.01" class="form-control" v-model.number="approveForm.tamamlanan_miktar">
                        </div>
                        <div class="form-group">
                            <label><strong>Eksik Miktar</strong></label>
                            <input type="text" class="form-control" :value="calculatedMissing" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>Onay Notu (Opsiyonel)</strong></label>
                            <textarea class="form-control" rows="3" v-model="approveForm.note"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showApproveModal = false">Iptal</button>
                        <button type="button" class="btn btn-success" @click="approveSingle">
                            <i class="fas fa-check"></i> Onayla ve Stoga Al
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        window.kullaniciBilgisi = {
            kullaniciAdi: '<?php echo $_SESSION["kullanici_adi"] ?? "Sistem"; ?>'
        };
    </script>
    <script src="assets/js/montaj_onay_ekrani.js?v=<?php echo time(); ?>"></script>
</body>
</html>

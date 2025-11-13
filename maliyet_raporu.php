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

$maliyetler = [];
$query = "SELECT malzeme_kodu, malzeme_ismi, agirlikli_ortalama_maliyet, son_hesaplama_tarihi FROM malzeme_maliyetleri ORDER BY malzeme_ismi ASC";
$result = $connection->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $maliyetler[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Malzeme Maliyet Raporu - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css?v=<?php echo time(); ?>">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-fluid {
            padding-top: 20px;
        }
        .card {
            margin-top: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #6f42c1; /* Bootstrap purple */
            color: white;
            font-weight: bold;
        }
        .table thead th {
            background-color: #e9ecef;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .table-wrapper {
            max-height: 70vh;
            overflow-y: auto;
        }
        .no-records {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .no-records i {
            font-size: 3em;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Navbar (assuming navigation.php or similar is included for consistency) -->
        <?php include 'navigation.php'; // Or copy relevant navbar HTML here ?>

        <div class="container-fluid">
            <div class="page-header">
                <div>
                    <h1>Malzeme Maliyet Raporu</h1>
                    <p>Malzemelerin ağırlıklı ortalama birim maliyetlerini görüntüleyin</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i> Malzeme Maliyetleri
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Malzeme Kodu</th>
                                    <th>Malzeme Adı</th>
                                    <th>Ağırlıklı Ortalama Maliyet</th>
                                    <th>Son Hesaplama Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($maliyetler)): ?>
                                    <tr>
                                        <td colspan="4" class="no-records">
                                            <i class="fas fa-box-open"></i>
                                            <h4>Henüz maliyet kaydı bulunmuyor.</h4>
                                            <p>Mal kabul işlemleri yapıldıkça maliyetler burada görünecektir.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($maliyetler as $maliyet): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($maliyet['malzeme_kodu']); ?></td>
                                            <td><?php echo htmlspecialchars($maliyet['malzeme_ismi']); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($maliyet['agirlikli_ortalama_maliyet'], 2) . ' TL'); ?></td>
                                            <td><?php echo htmlspecialchars($maliyet['son_hesaplama_tarihi']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

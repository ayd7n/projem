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

// İş merkezleri verilerini ekleme
$work_centers = [
    ['isim' => 'Ana Uretim Alani', 'aciklama' => 'Ana uretim bolumu'],
    ['isim' => 'Kimyasal Hazirlama', 'aciklama' => 'Kimyasal karisimlarin hazirlandigi alan'],
    ['isim' => 'Paketleme Bolumu', 'aciklama' => 'Urunlerin paketlendigi bolum'],
    ['isim' => 'Kalite Kontrol', 'aciklama' => 'Urun kalitesinin kontrol edildigi alan'],
    ['isim' => 'Hammadde Deposu', 'aciklama' => 'Hammadde stoklarinin tutuldugu depo'],
    ['isim' => 'Mamul Deposu', 'aciklama' => 'Hazir urunlerin tutuldugu depo'],
    ['isim' => 'Karistirma Odasi', 'aciklama' => 'Esans karisimlarinin yapildigi oda'],
    ['isim' => 'Test Laboratuari', 'aciklama' => 'Urun testlerinin yapildigi laboratuar'],
    ['isim' => 'Ambalajlama Alani', 'aciklama' => 'Urun ambalajlama islemlerinin yapildigi alan'],
    ['isim' => 'Sevkiyat Bolumu', 'aciklama' => 'Hazirlanan siparislerin sevkiyata hazirlandigi bolum']
];

$message = '';
$error = '';

foreach ($work_centers as $work_center) {
    $isim = $work_center['isim'];
    $aciklama = $work_center['aciklama'];

    // Önce aynı isimde kayıt var mı kontrol et
    $check_query = "SELECT COUNT(*) as count FROM is_merkezleri WHERE isim = ?";
    $check_stmt = $connection->prepare($check_query);
    $check_stmt->bind_param('s', $isim);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $exists = $check_result->fetch_assoc()['count'] > 0;
    $check_stmt->close();

    if (!$exists) {
        $query = "INSERT INTO is_merkezleri (isim, aciklama) VALUES (?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ss', $isim, $aciklama);

        if ($stmt->execute()) {
            $message .= "İş merkezi '$isim' başarıyla eklendi.<br>";
        } else {
            $error .= "İş merkezi '$isim' eklenirken hata oluştu: " . $connection->error . "<br>";
        }
        $stmt->close();
    } else {
        $error .= "İş merkezi '$isim' zaten mevcut.<br>";
    }
}

// Mevcut iş merkezlerini göster
$work_centers_query = "SELECT * FROM is_merkezleri ORDER BY isim";
$work_centers_result = $connection->query($work_centers_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İş Merkezleri Ekle - Parfüm ERP Sistemi</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: #2c3e50;
            padding: 30px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            margin-bottom: 30px;
        }
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }
        .card-body { padding: 20px; }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid transparent;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8492a6;
            background-color: #f8f9fa;
        }
        tbody tr:hover { background-color: #f5f7fb; }
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        .btn-primary {
            background-color: #4361ee;
            color: white;
        }
        .btn-primary:hover {
            background-color: #3f37c9;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-industry mr-2"></i>İş Merkezleri Veri Ekleme</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle mr-2"></i>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <h4>Eklenen İş Merkezleri:</h4>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>İş Merkezi Adı</th>
                                            <th>Açıklama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($work_centers as $work_center): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($work_center['isim']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($work_center['aciklama']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h4>Mevcut İş Merkezleri:</h4>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>İş Merkezi Adı</th>
                                            <th>Açıklama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($work_centers_result && $work_centers_result->num_rows > 0): ?>
                                            <?php while ($work_center = $work_centers_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $work_center['is_merkezi_id']; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($work_center['isim']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($work_center['aciklama']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center p-4">Henüz kayıtlı iş merkezi bulunmuyor.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="is_merkezleri.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-2"></i>İş Merkezleri Sayfasına Dön
                            </a>
                            <a href="navigation.php" class="btn btn-secondary">
                                <i class="fas fa-home mr-2"></i>Ana Sayfaya Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

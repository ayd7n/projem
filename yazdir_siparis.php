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

// Page-level permission check
if (!yetkisi_var('page:view:satinalma_siparisler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Get order ID from URL
$siparis_id = (int) ($_GET['id'] ?? 0);

if (!$siparis_id) {
    die('Sipariş ID belirtilmedi.');
}

// Get order details with supplier info
$result = $connection->query("SELECT s.*, t.adres as tedarikci_adres, t.telefon as tedarikci_telefon, t.e_posta as tedarikci_email
    FROM satinalma_siparisler s
    LEFT JOIN tedarikciler t ON s.tedarikci_id = t.tedarikci_id
    WHERE s.siparis_id = $siparis_id");

if (!$result || $result->num_rows === 0) {
    die('Sipariş bulunamadı.');
}

$order = $result->fetch_assoc();

// Get order items
$items_result = $connection->query("SELECT * FROM satinalma_siparis_kalemleri WHERE siparis_id = $siparis_id");

$items = [];
if ($items_result) {
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
}

$order['kalemler'] = $items;

// Function to format currency
function formatCurrency($value, $currency = 'TRY') {
    $num = floatval($value);
    $symbols = ['TRY' => '₺', 'TL' => '₺', 'USD' => '$', 'EUR' => '€'];
    return number_format($num, 2, ',', '.') . ' ' . ($symbols[$currency] ?? $currency);
}

// Function to format date
function formatDate($dateString) {
    if (!$dateString) return '-';
    return date('d/m/Y', strtotime($dateString));
}

// Function to get status text
function getDurumText($durum) {
    $map = [
        'taslak' => 'Taslak',
        'onaylandi' => 'Onaylandı',
        'gonderildi' => 'Gönderildi',
        'kismen_teslim' => 'Kısmen Teslim',
        'tamamlandi' => 'Tamamlandı',
        'iptal' => 'İptal'
    ];
    return $map[$durum] ?? $durum;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satınalma Siparişi Yazdır - <?php echo htmlspecialchars($order['siparis_no']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #4a0e63;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-box {
            width: 48%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 12px;
        }
        th {
            background: #4a0e63;
            color: white;
        }
        .total-row {
            font-size: 16px;
            background: #f8f9fa;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <button class="btn btn-primary print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Yazdır
    </button>

    <div class="header">
        <h1>SATINALMA SİPARİŞİ</h1>
        <p><strong><?php echo htmlspecialchars($order['siparis_no']); ?></strong></p>
    </div>

    <div class="info-row">
        <div class="info-box">
            <h3>Firma Bilgileri</h3>
            <p><strong>IDO KOZMETİK</strong></p>
            <p>Tel: -</p>
        </div>
        <div class="info-box">
            <h3>Tedarikçi Bilgileri</h3>
            <p><strong><?php echo htmlspecialchars($order['tedarikci_adi']); ?></strong></p>
            <p><?php echo htmlspecialchars($order['tedarikci_adres'] ?? ''); ?></p>
            <p><?php echo htmlspecialchars($order['tedarikci_telefon'] ?? ''); ?></p>
        </div>
    </div>

    <div class="info-row">
        <div><strong>Sipariş Tarihi:</strong> <?php echo formatDate($order['siparis_tarihi']); ?></div>
        <div><strong>İstenen Teslim:</strong> <?php echo $order['istenen_teslim_tarihi'] ? formatDate($order['istenen_teslim_tarihi']) : '-'; ?></div>
        <div><strong>Durum:</strong> <?php echo getDurumText($order['durum']); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Malzeme Adı</th>
                <th style="width:100px">Miktar</th>
                <th style="width:100px">Birim Fiyat</th>
                <th style="width:100px">Toplam</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['kalemler'] as $index => $kalem): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo htmlspecialchars($kalem['malzeme_adi']); ?></td>
                <td style="text-align:center"><?php echo $kalem['miktar']; ?> <?php echo htmlspecialchars($kalem['birim']); ?></td>
                <td style="text-align:right"><?php echo formatCurrency($kalem['birim_fiyat'], $kalem['para_birimi']); ?></td>
                <td style="text-align:right"><strong><?php echo formatCurrency($kalem['toplam_fiyat'], $kalem['para_birimi']); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align:right"><strong>GENEL TOPLAM:</strong></td>
                <td style="text-align:right"><strong><?php echo formatCurrency($order['toplam_tutar'], $order['para_birimi']); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($order['aciklama'])): ?>
    <p style="margin-top:20px"><strong>Açıklama:</strong> <?php echo htmlspecialchars($order['aciklama']); ?></p>
    <?php endif; ?>

    <div class="footer">
        <div class="signature">
            <div class="signature-line">Sipariş Veren</div>
        </div>
        <div class="signature">
            <div class="signature-line">Onaylayan</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
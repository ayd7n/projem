<?php
// HTML tabanlı PDF oluşturma sayfası
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

// Set headers for PDF download
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="satinalma_siparişi_' . $order['siparis_no'] . '.html"');

// HTML tabanlı PDF içeriği
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satınalma Siparişi - <?php echo htmlspecialchars($order['siparis_no']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: white;
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
            font-size: 24px;
        }
        .header p {
            font-size: 16px;
            margin: 5px 0;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-box {
            display: table-cell;
            width: 48%;
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            color: #4a0e63;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 12px;
        }
        th {
            background: #4a0e63;
            color: white;
            font-weight: bold;
        }
        .total-row {
            font-size: 14px;
            background: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature {
            display: table-cell;
            text-align: center;
            width: 40%;
            padding: 20px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 12px;
        }
        @media print {
            body {
                margin: 0;
                background: white !important;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SATINALMA SİPARİŞİ</h1>
        <p><strong><?php echo htmlspecialchars($order['siparis_no']); ?></strong></p>
    </div>

    <div class="info-row">
        <div class="info-box">
            <h3>FİRMA BİLGİLERİ</h3>
            <p><strong>IDO KOZMETİK</strong></p>
            <p>Adres: [Firma Adresi]</p>
            <p>Tel: [Telefon]</p>
            <p>Email: [Email]</p>
        </div>
        <div class="info-box">
            <h3>TEDARİKÇİ BİLGİLERİ</h3>
            <p><strong><?php echo htmlspecialchars($order['tedarikci_adi']); ?></strong></p>
            <p><?php echo htmlspecialchars($order['tedarikci_adres'] ?? ''); ?></p>
            <p>Tel: <?php echo htmlspecialchars($order['tedarikci_telefon'] ?? ''); ?></p>
            <p>Email: <?php echo htmlspecialchars($order['tedarikci_email'] ?? ''); ?></p>
        </div>
    </div>

    <table style="border: none; margin-bottom: 20px;">
        <tr>
            <td style="border: none; padding: 5px;"><strong>Sipariş Tarihi:</strong> <?php echo formatDate($order['siparis_tarihi']); ?></td>
            <td style="border: none; padding: 5px;"><strong>İstenen Teslim:</strong> <?php echo $order['istenen_teslim_tarihi'] ? formatDate($order['istenen_teslim_tarihi']) : '-'; ?></td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px;"><strong>Durum:</strong> <?php echo getDurumText($order['durum']); ?></td>
            <td style="border: none; padding: 5px;"><strong>Oluşturan:</strong> <?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?></td>
        </tr>
    </table>

    <h3 style="color: #4a0e63; margin-top: 30px;">SİPARİŞ KALEMLERİ</h3>
    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Malzeme Adı</th>
                <th style="width:100px">Miktar</th>
                <th style="width:120px">Birim Fiyat</th>
                <th style="width:120px">Toplam</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['kalemler'] as $index => $kalem): ?>
            <tr>
                <td style="text-align:center"><?php echo $index + 1; ?></td>
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
            <strong>Sipariş Veren</strong><br><br><br>
            <div class="signature-line"><?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?><br><?php echo date('d/m/Y H:i'); ?></div>
        </div>
        <div style="display: table-cell; width: 20%;"></div>
        <div class="signature">
            <strong>Onaylayan</strong><br><br><br>
            <div class="signature-line"></div>
        </div>
    </div>

    <script>
        // Sayfa yüklendiğinde otomatik yazdırma
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
<?php exit; ?>

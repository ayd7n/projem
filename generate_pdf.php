<?php
// HTML tabanlı PDF oluşturma sayfası - Client Side PDF Generation
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
$result = $connection->query("SELECT s.*, t.adres as tedarikci_adres, t.telefon as tedarikci_telefon, t.e_posta as tedarikci_email, t.vergi_no_tc, t.yetkili_kisi
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
    return date('d.m.Y', strtotime($dateString));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş <?php echo htmlspecialchars($order['siparis_no']); ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f0f0;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }

        #download-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #4a0e63;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #download-btn:hover { background: #3a0b4d; }

        .page {
            background: white;
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            box-sizing: border-box;
            margin: 20px auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .layout-table td { vertical-align: top; }

        .logo { font-size: 26px; font-weight: bold; color: #4a0e63; }
        .logo span { color: #d4af37; }
        .sub-logo { font-size: 11px; color: #888; text-transform: uppercase; margin-top: 3px; letter-spacing: 1px; }

        .doc-title { text-align: right; font-size: 22px; font-weight: bold; color: #222; }
        .doc-no { text-align: right; font-size: 14px; color: #4a0e63; font-weight: bold; margin-top: 5px; }

        .box-title {
            font-size: 11px;
            font-weight: bold;
            color: #4a0e63;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 8px;
            display: block;
        }

        .box-content { font-size: 13px; line-height: 1.4; color: #333; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th {
            background: #fdfdfd;
            border-bottom: 2px solid #4a0e63;
            padding: 10px 8px;
            font-size: 11px;
            text-align: left;
            color: #4a0e63;
        }
        .data-table td { padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 13px; color: #333; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-table { width: 100%; margin-top: 15px; border-collapse: collapse; }
        .total-table td { padding: 5px 8px; font-size: 13px; text-align: right; }
        .total-label { color: #666; font-weight: 500; }
        .total-value { font-weight: bold; width: 130px; }
        .grand-total td { padding-top: 10px; border-top: 1px solid #ddd; }
        .grand-total .total-label { font-size: 16px; color: #4a0e63; font-weight: bold; }
        .grand-total .total-value { font-size: 18px; color: #4a0e63; font-weight: bold; }

        .notes-area { margin-top: 30px; padding: 12px; border: 1px dashed #ccc; font-size: 12px; color: #555; background: #fafafa; }

        .footer-table { width: 100%; margin-top: 60px; }
        .sign-box { text-align: center; font-size: 12px; }
        .sign-line { border-top: 1px solid #222; margin-top: 45px; width: 80%; margin-left: auto; margin-right: auto; padding-top: 8px; font-weight: bold; }
    </style>
</head>
<body>

    <button id="download-btn" onclick="generatePDF()">
        <i class="fas fa-file-pdf"></i> PDF İNDİR
    </button>

    <div id="invoice-container" class="page">
        
        <table class="layout-table">
            <tr>
                <td>
                    <div class="logo">IDO<span>KOZMETİK</span></div>
                    <div class="sub-logo">Tedarik Yönetim Sistemi</div>
                </td>
                <td>
                    <div class="doc-title">SATINALMA SİPARİŞİ</div>
                    <div class="doc-no">Sipariş No: #<?php echo htmlspecialchars($order['siparis_no']); ?></div>
                </td>
            </tr>
        </table>

        <div style="height: 15px;"></div>

        <table class="layout-table">
            <tr>
                <td style="width: 48%;">
                    <span class="box-title">ALICI FİRMA</span>
                    <div class="box-content">
                        <strong>IDO KOZMETİK</strong><br>
                        İstanbul, Türkiye
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%;">
                    <span class="box-title">TEDARİKÇİ</span>
                    <div class="box-content">
                        <strong><?php echo htmlspecialchars($order['tedarikci_adi']); ?></strong><br>
                        <?php echo htmlspecialchars($order['tedarikci_adres'] ?? 'Adres belirtilmemiş'); ?><br>
                        Vergi/TC No: <?php echo htmlspecialchars($order['vergi_no_tc'] ?? '-'); ?>
                    </div>
                </td>
            </tr>
        </table>

        <div style="height: 15px;"></div>

        <table class="layout-table" style="width: 60%;">
            <tr>
                <td>
                    <span style="font-size: 10px; color: #888; text-transform: uppercase;">Sipariş Tarihi</span><br>
                    <span style="font-size: 13px; font-weight: bold;"><?php echo formatDate($order['siparis_tarihi']); ?></span>
                </td>
                <td>
                    <span style="font-size: 10px; color: #888; text-transform: uppercase;">İstenen Teslim Tarihi</span><br>
                    <span style="font-size: 13px; font-weight: bold;"><?php echo $order['istenen_teslim_tarihi'] ? formatDate($order['istenen_teslim_tarihi']) : '-'; ?></span>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 50%;">MALZEME AÇIKLAMASI</th>
                    <th style="width: 15%; text-align: center;">MİKTAR</th>
                    <th style="width: 15%; text-align: right;">BİRİM FİYAT</th>
                    <th style="width: 15%; text-align: right;">TOPLAM</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['kalemler'] as $index => $kalem): ?>
                <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($kalem['malzeme_adi']); ?></strong>
                        <?php if (!empty($kalem['aciklama'])): ?>
                            <br><small style="color: #666;"><?php echo htmlspecialchars($kalem['aciklama']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo floatval($kalem['miktar']); ?> <?php echo htmlspecialchars($kalem['birim']); ?></td>
                    <td class="text-right"><?php echo formatCurrency($kalem['birim_fiyat'], $kalem['para_birimi']); ?></td>
                    <td class="text-right"><strong><?php echo formatCurrency($kalem['toplam_fiyat'], $kalem['para_birimi']); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table class="total-table">
            <tr>
                <td></td>
                <td class="total-label">Ara Toplam:</td>
                <td class="total-value"><?php echo formatCurrency($order['toplam_tutar'], $order['para_birimi']); ?></td>
            </tr>
            <tr class="grand-total">
                <td></td>
                <td class="total-label">GENEL TOPLAM:</td>
                <td class="total-value"><?php echo formatCurrency($order['toplam_tutar'], $order['para_birimi']); ?></td>
            </tr>
        </table>

        <?php if (!empty($order['aciklama'])): ?>
        <div class="notes-area">
            <strong style="color: #4a0e63;">NOTLAR:</strong><br>
            <?php echo nl2br(htmlspecialchars($order['aciklama'])); ?>
        </div>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid #f0f0f0; text-align: center;">
            <p style="font-size: 9px; color: #bbb; font-style: italic; line-height: 1.5; letter-spacing: 0.3px;">
                <i class="fas fa-shield-alt"></i> Bu doküman, IDO KOZMETİK ERP Kurumsal Bilgi Yönetim Sistemi altyapısı kullanılarak dijital ortamda güvenli olarak oluşturulmuştur. 
                İşbu belge içeriğindeki tüm veriler, merkez veritabanı kayıtları ile anlık olarak senkronize edilmekte olup sistem tarafından doğruluğu teyit edilmiştir. 
                Elektronik ortamda onaylanan bu form, 5070 sayılı Elektronik İmza Kanunu standartlarına uygun olarak üretilmiş olup ıslak imza gerektirmeksizin hukuki geçerliliğini korumaktadır. 
                Veri bütünlüğü ve gizliliği uluslararası bilgi güvenliği standartları çerçevesinde korunmaktadır. 
                Belge üzerindeki bilgilerin sistem kayıtları dışında manuel olarak değiştirilmesi veya tahrif edilmesi durumunda belge geçersiz sayılacaktır.
            </p>
        </div>

    </div>

    <script>
        function generatePDF() {
            const element = document.getElementById('invoice-container');
            const button = document.getElementById('download-btn');
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Dosya Hazırlanıyor...';
            button.disabled = true;

            const opt = {
                margin:       0,
                filename:     'Siparis_<?php echo $order['siparis_no']; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function(){
                 button.innerHTML = '<i class="fas fa-file-pdf"></i> PDF İNDİR';
                 button.disabled = false;
            });
        }
    </script>
</body>
</html>
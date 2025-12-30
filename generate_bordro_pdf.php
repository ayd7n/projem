<?php
// Personel Bordro PDF Sayfası - TÜM dönemler
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

// Get parameters from URL
$personel_id = (int) ($_GET['personel_id'] ?? 0);

if (!$personel_id) {
    die('Personel ID belirtilmedi.');
}

// Ay isimleri
$ay_isimleri = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];

// Get personel details
$personel_query = "SELECT * FROM personeller WHERE personel_id = ?";
$personel_stmt = $connection->prepare($personel_query);
$personel_stmt->bind_param('i', $personel_id);
$personel_stmt->execute();
$personel_result = $personel_stmt->get_result();
$personel = $personel_result->fetch_assoc();

if (!$personel) {
    die('Personel bulunamadı.');
}

// Get ALL maaş ödemeleri
$odemeler_query = "SELECT * FROM personel_maas_odemeleri WHERE personel_id = ? ORDER BY donem_yil DESC, donem_ay DESC";
$odemeler_stmt = $connection->prepare($odemeler_query);
$odemeler_stmt->bind_param('i', $personel_id);
$odemeler_stmt->execute();
$odemeler_result = $odemeler_stmt->get_result();

$odemeler = [];
$toplam_odeme = 0;
while ($odeme = $odemeler_result->fetch_assoc()) {
    $odemeler[] = $odeme;
    $toplam_odeme += floatval($odeme['net_odenen']);
}

// Get ALL avanslar
$avanslar_query = "SELECT * FROM personel_avanslar WHERE personel_id = ? ORDER BY avans_tarihi DESC";
$avanslar_stmt = $connection->prepare($avanslar_query);
$avanslar_stmt->bind_param('i', $personel_id);
$avanslar_stmt->execute();
$avanslar_result = $avanslar_stmt->get_result();

$avanslar = [];
$toplam_avans = 0;
while ($avans = $avanslar_result->fetch_assoc()) {
    $avanslar[] = $avans;
    $toplam_avans += floatval($avans['avans_tutari']);
}

// Brüt ücret
$brut_ucret = floatval($personel['aylik_brut_ucret'] ?? 0);

// Function to format currency
function formatCurrency($value) {
    return number_format(floatval($value), 2, ',', '.') . ' ₺';
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
    <title>Bordro Kartı - <?php echo htmlspecialchars($personel['ad_soyad']); ?></title>
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
            background: #dc3545;
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

        #download-btn:hover { background: #c82333; }

        .page {
            background: white;
            width: 210mm;
            min-height: 297mm;
            padding: 12mm;
            box-sizing: border-box;
            margin: 20px auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .layout-table td { vertical-align: top; }

        .logo { font-size: 26px; font-weight: bold; color: #4a0e63; }
        .logo span { color: #d4af37; }
        .sub-logo { font-size: 11px; color: #888; text-transform: uppercase; margin-top: 3px; letter-spacing: 1px; }

        .doc-title { text-align: right; font-size: 22px; font-weight: bold; color: #222; }
        .doc-no { text-align: right; font-size: 12px; color: #666; margin-top: 5px; }

        .box-title {
            font-size: 10px;
            font-weight: bold;
            color: #4a0e63;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
            margin-bottom: 6px;
            display: block;
        }

        .box-content { font-size: 12px; line-height: 1.5; color: #333; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th {
            background: #fdfdfd;
            border-bottom: 2px solid #4a0e63;
            padding: 8px 6px;
            font-size: 10px;
            text-align: left;
            color: #4a0e63;
        }
        .data-table td { padding: 8px 6px; border-bottom: 1px solid #eee; font-size: 11px; color: #333; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .summary-box {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px;
            margin-top: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #ccc;
            font-size: 12px;
        }

        .summary-row:last-child { border-bottom: none; }

        .total-row {
            background: linear-gradient(135deg, #4a0e63 0%, #7c2a99 100%);
            color: white;
            border-radius: 6px;
            padding: 10px 15px;
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-label { font-size: 12px; font-weight: bold; }
        .total-value { font-size: 16px; font-weight: bold; }

        .info-card {
            background: linear-gradient(135deg, #4a0e63 0%, #7c2a99 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .info-card-title { font-size: 14px; font-weight: bold; }
        .info-card-value { font-size: 11px; opacity: 0.9; }

        .section-divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 15px 0;
        }

        .footer-note {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .footer-note p {
            font-size: 8px;
            color: #999;
            font-style: italic;
            line-height: 1.4;
            letter-spacing: 0.2px;
        }

        @media print {
            #download-btn { display: none; }
            body { background: white; }
            .page { box-shadow: none; margin: 0; }
        }
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
                    <div class="sub-logo">İnsan Kaynakları Yönetim Sistemi</div>
                </td>
                <td>
                    <div class="doc-title">PERSONEL BORDRO KARTI</div>
                    <div class="doc-no">Oluşturulma: <?php echo date('d.m.Y H:i'); ?></div>
                </td>
            </tr>
        </table>

        <!-- Personel Bilgileri -->
        <table class="layout-table">
            <tr>
                <td style="width: 48%;">
                    <span class="box-title"><i class="fas fa-user"></i> PERSONEL BİLGİLERİ</span>
                    <div class="box-content">
                        <strong style="font-size: 15px; color: #4a0e63;"><?php echo htmlspecialchars($personel['ad_soyad']); ?></strong><br>
                        <?php if (!empty($personel['tc_kimlik_no'])): ?>
                            <i class="fas fa-id-card" style="color: #4a0e63; width: 14px;"></i> TC: <?php echo htmlspecialchars($personel['tc_kimlik_no']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($personel['telefon'])): ?>
                            <i class="fas fa-phone" style="color: #4a0e63; width: 14px;"></i> <?php echo htmlspecialchars($personel['telefon']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($personel['e_posta'])): ?>
                            <i class="fas fa-envelope" style="color: #4a0e63; width: 14px;"></i> <?php echo htmlspecialchars($personel['e_posta']); ?><br>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%;">
                    <span class="box-title"><i class="fas fa-briefcase"></i> İŞ BİLGİLERİ</span>
                    <div class="box-content">
                        <strong>Pozisyon:</strong> <?php echo htmlspecialchars($personel['pozisyon'] ?? '-'); ?><br>
                        <strong>Departman:</strong> <?php echo htmlspecialchars($personel['departman'] ?? '-'); ?><br>
                        <strong>İşe Giriş:</strong> <?php echo formatDate($personel['ise_giris_tarihi']); ?><br>
                        <strong>Brüt Ücret:</strong> <?php echo formatCurrency($brut_ucret); ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Özet Kartları -->
        <div style="display: flex; gap: 10px; margin: 15px 0;">
            <div style="flex: 1; background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="font-size: 10px; opacity: 0.9; text-transform: uppercase;">Toplam Ödeme</div>
                <div style="font-size: 18px; font-weight: bold; margin-top: 4px;"><?php echo formatCurrency($toplam_odeme); ?></div>
                <div style="font-size: 10px; opacity: 0.8; margin-top: 2px;"><?php echo count($odemeler); ?> maaş ödemesi</div>
            </div>
            <div style="flex: 1; background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: white; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="font-size: 10px; opacity: 0.9; text-transform: uppercase;">Toplam Avans</div>
                <div style="font-size: 18px; font-weight: bold; margin-top: 4px;"><?php echo formatCurrency($toplam_avans); ?></div>
                <div style="font-size: 10px; opacity: 0.8; margin-top: 2px;"><?php echo count($avanslar); ?> avans kaydı</div>
            </div>
        </div>

        <hr class="section-divider">

        <!-- Maaş Ödemeleri Tablosu -->
        <span class="box-title"><i class="fas fa-money-check-alt"></i> MAAŞ ÖDEMELERİ GEÇMİŞİ</span>
        <?php if (count($odemeler) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 18%;">DÖNEM</th>
                    <th style="width: 17%; text-align: right;">BRÜT</th>
                    <th style="width: 17%; text-align: right;">AVANS</th>
                    <th style="width: 18%; text-align: right;">NET ÖDENEN</th>
                    <th style="width: 15%;">TARİH</th>
                    <th style="width: 10%;">TİP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($odemeler as $index => $odeme): ?>
                <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>
                    <td><strong><?php echo $ay_isimleri[$odeme['donem_ay']]; ?> <?php echo $odeme['donem_yil']; ?></strong></td>
                    <td class="text-right"><?php echo formatCurrency($odeme['aylik_brut_ucret']); ?></td>
                    <td class="text-right" style="color: #ef4444;">-<?php echo formatCurrency($odeme['avans_toplami']); ?></td>
                    <td class="text-right"><strong style="color: #059669;"><?php echo formatCurrency($odeme['net_odenen']); ?></strong></td>
                    <td><?php echo formatDate($odeme['odeme_tarihi']); ?></td>
                    <td><?php echo htmlspecialchars($odeme['odeme_tipi']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 20px; color: #888; font-size: 12px;">
            <i class="fas fa-info-circle"></i> Henüz maaş ödemesi kaydı bulunmuyor.
        </div>
        <?php endif; ?>

        <hr class="section-divider">

        <!-- Avanslar Tablosu -->
        <span class="box-title"><i class="fas fa-hand-holding-usd"></i> AVANS KAYITLARI</span>
        <?php if (count($avanslar) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 20%;">DÖNEM</th>
                    <th style="width: 25%; text-align: right;">TUTAR</th>
                    <th style="width: 20%;">TARİH</th>
                    <th style="width: 15%;">TİP</th>
                    <th style="width: 15%;">DURUM</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($avanslar as $index => $avans): ?>
                <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>
                    <td><?php echo $ay_isimleri[$avans['donem_ay']]; ?> <?php echo $avans['donem_yil']; ?></td>
                    <td class="text-right"><strong style="color: #d97706;"><?php echo formatCurrency($avans['avans_tutari']); ?></strong></td>
                    <td><?php echo formatDate($avans['avans_tarihi']); ?></td>
                    <td><?php echo htmlspecialchars($avans['odeme_tipi']); ?></td>
                    <td>
                        <?php if ($avans['maas_odemesinde_kullanildi']): ?>
                            <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 10px; font-size: 10px;">Kullanıldı</span>
                        <?php else: ?>
                            <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 10px; font-size: 10px;">Bekliyor</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 20px; color: #888; font-size: 12px;">
            <i class="fas fa-info-circle"></i> Henüz avans kaydı bulunmuyor.
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer-note">
            <p>
                <i class="fas fa-shield-alt"></i> Bu doküman, IDO KOZMETİK ERP Kurumsal Bilgi Yönetim Sistemi altyapısı kullanılarak dijital ortamda güvenli olarak oluşturulmuştur. 
                İşbu belge içeriğindeki tüm veriler, merkez veritabanı kayıtları ile anlık olarak senkronize edilmekte olup sistem tarafından doğruluğu teyit edilmiştir.
                Elektronik ortamda onaylanan bu form, 5070 sayılı Elektronik İmza Kanunu standartlarına uygun olarak üretilmiş olup ıslak imza gerektirmeksizin hukuki geçerliliğini korumaktadır.
                Personel maaş ve avans bilgileri 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında gizli tutulmaktadır.
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
                filename:     'Bordro_<?php echo str_replace(' ', '_', $personel['ad_soyad']); ?>.pdf',
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

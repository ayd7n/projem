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

if (!yetkisi_var('page:view:urunler')) {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

// Ürünleri çek
$products = [];
$query = "
    SELECT 
        u.urun_kodu,
        u.urun_ismi,
        u.stok_miktari,
        u.kritik_stok_seviyesi,
        u.birim,
        u.urun_tipi,
        COALESCE(SUM(CASE WHEN mie.durum IN ('baslatildi', 'uretimde') THEN mie.planlanan_miktar ELSE 0 END), 0) AS uretimde_miktar
    FROM urunler u
    LEFT JOIN montaj_is_emirleri mie ON u.urun_kodu = mie.urun_kodu AND mie.durum IN ('baslatildi', 'uretimde')
    GROUP BY u.urun_kodu
    ORDER BY u.urun_ismi
";
$result = $connection->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stok = floatval($row['stok_miktari']);
        $uretimde = floatval($row['uretimde_miktar']);
        $kritik = floatval($row['kritik_stok_seviyesi']);
        $toplam_mevcut = $stok + $uretimde;
        
        if ($kritik > 0) {
            $acik = $kritik - $toplam_mevcut;
            $yuzde_fark = (($kritik - $toplam_mevcut) / $kritik) * 100;
        } else {
            $acik = 0;
            $yuzde_fark = -100;
        }
        
        // Üretilebilir miktar hesapla
        $uretilebilir_miktar = 0;
        $kritik_bilesen_turleri = ['kutu', 'takim', 'esans'];
        $uretilebilir_kritik = PHP_INT_MAX;
        
        $bom_query = "SELECT ua.bilesen_miktari, ua.bilesenin_malzeme_turu as bilesen_turu,
                     CASE
                        WHEN m.malzeme_kodu IS NOT NULL THEN m.stok_miktari
                        WHEN ur.urun_kodu IS NOT NULL THEN ur.stok_miktari
                        WHEN e.esans_kodu IS NOT NULL THEN e.stok_miktari
                        ELSE 0
                     END as bilesen_stok
                     FROM urun_agaci ua
                     LEFT JOIN malzemeler m ON ua.bilesen_kodu = m.malzeme_kodu
                     LEFT JOIN urunler ur ON ua.bilesen_kodu = ur.urun_kodu
                     LEFT JOIN esanslar e ON ua.bilesen_kodu = e.esans_kodu
                     WHERE ua.agac_turu = 'urun' AND ua.urun_kodu = ?";
        $bom_stmt = $connection->prepare($bom_query);
        $bom_stmt->bind_param('i', $row['urun_kodu']);
        $bom_stmt->execute();
        $bom_result = $bom_stmt->get_result();
        
        while ($bom_row = $bom_result->fetch_assoc()) {
            $gerekli = floatval($bom_row['bilesen_miktari']);
            $mevcut = floatval($bom_row['bilesen_stok']);
            
            if ($gerekli > 0) {
                $bu_bilesenden = max(0, floor($mevcut / $gerekli));
                if (in_array(strtolower($bom_row['bilesen_turu']), $kritik_bilesen_turleri)) {
                    $uretilebilir_kritik = min($uretilebilir_kritik, $bu_bilesenden);
                }
            }
        }
        $bom_stmt->close();
        
        $uretilebilir_miktar = ($uretilebilir_kritik === PHP_INT_MAX) ? 0 : $uretilebilir_kritik;
        $onerilen_uretim = max(0, min($acik, $uretilebilir_miktar));
        
        $row['toplam_mevcut'] = $toplam_mevcut;
        $row['acik'] = $acik;
        $row['yuzde_fark'] = $yuzde_fark;
        $row['uretilebilir_miktar'] = $uretilebilir_miktar;
        $row['onerilen_uretim'] = $onerilen_uretim;
        
        $products[] = $row;
    }
}

usort($products, function($a, $b) {
    return $b['yuzde_fark'] <=> $a['yuzde_fark'];
});

// İstatistikler
$acil_count = 0; $uyari_count = 0; $iyi_count = 0; $belirsiz_count = 0;
foreach ($products as $p) {
    if ($p['kritik_stok_seviyesi'] <= 0) $belirsiz_count++;
    elseif ($p['yuzde_fark'] > 50) $acil_count++;
    elseif ($p['yuzde_fark'] > 0) $uyari_count++;
    else $iyi_count++;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ne Üretsem? - IDO Kozmetik</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
        }
        body {
            font-family: 'Ubuntu', sans-serif;
            background: #f8f9fa;
            font-size: 12px;
        }
        .main-content { padding: 15px 20px; }
        
        /* Stats - Inline Compact */
        .stats-inline {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #fff;
            border: 1px solid #e5e7eb;
        }
        .stat-chip .num { font-size: 14px; font-weight: 700; }
        .stat-chip.danger { border-color: #fecaca; background: #fef2f2; }
        .stat-chip.danger .num { color: #dc2626; }
        .stat-chip.warning { border-color: #fde68a; background: #fffbeb; }
        .stat-chip.warning .num { color: #d97706; }
        .stat-chip.success { border-color: #bbf7d0; background: #f0fdf4; }
        .stat-chip.success .num { color: #16a34a; }
        .stat-chip.muted { border-color: #e5e7eb; background: #f9fafb; }
        .stat-chip.muted .num { color: #6b7280; }
        
        /* Info Compact */
        .info-compact {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 11px;
            color: #0369a1;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .info-compact code {
            background: rgba(0,0,0,0.06);
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 10px;
        }
        .legend-inline { display: flex; gap: 12px; margin-left: auto; }
        .legend-inline span { display: flex; align-items: center; gap: 4px; font-size: 10px; }
        .legend-dot { width: 8px; height: 8px; border-radius: 2px; }
        
        /* Table */
        .card { border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card-header { 
            background: linear-gradient(135deg, var(--primary), var(--secondary)); 
            color: #fff; 
            padding: 10px 15px;
            font-size: 13px;
            font-weight: 600;
        }
        .table { margin: 0; font-size: 11px; }
        .table th {
            background: #f9fafb;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            padding: 8px 6px;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }
        .table td { padding: 6px; vertical-align: middle; border-color: #f3f4f6; }
        .table tbody tr:hover { background: #f9fafb; }
        .table tbody tr.row-acil { background: rgba(239,68,68,0.04); }
        .table tbody tr.row-acil:hover { background: rgba(239,68,68,0.08); }
        
        /* Badges */
        .badge-sm {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-acil { background: #fef2f2; color: #dc2626; }
        .badge-uyari { background: #fffbeb; color: #d97706; }
        .badge-iyi { background: #f0fdf4; color: #16a34a; }
        .badge-belirsiz { background: #f3f4f6; color: #6b7280; }
        
        .text-danger { color: #dc2626 !important; }
        .text-warning { color: #d97706 !important; }
        .text-success { color: #16a34a !important; }
        .text-muted { color: #9ca3af !important; }
        .font-semibold { font-weight: 600; }
        
        .btn-xs {
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 4px;
        }
        .btn-dark { background: var(--primary); border-color: var(--primary); }
        .btn-dark:hover { background: var(--secondary); border-color: var(--secondary); }
        
        .card-footer { background: #f9fafb; font-size: 10px; color: #6b7280; padding: 8px 15px; }
        
        @media print {
            .navbar, .btn, .no-print { display: none !important; }
            .main-content { padding: 0; }
        }
        @media (max-width: 768px) {
            .legend-inline { margin-left: 0; margin-top: 5px; width: 100%; }
        }
    </style>
</head>
<body>
    <!-- Navbar (diğer sayfalarla aynı) -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="navbar-text mr-3"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="main-content">
        <!-- Header Compact -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="mb-0" style="color: var(--primary); font-weight: 700;">
                    <i class="fas fa-lightbulb text-warning"></i> Ne Üretsem?
                </h5>
                <small class="text-muted">Üretim önceliği en yüksek ürünler</small>
            </div>
            <button class="btn btn-outline-secondary btn-sm no-print" onclick="window.print()">
                <i class="fas fa-print"></i> Yazdır
            </button>
        </div>
        
        <!-- Stats Inline -->
        <div class="stats-inline">
            <div class="stat-chip danger">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="num"><?php echo $acil_count; ?></span> Acil
            </div>
            <div class="stat-chip warning">
                <i class="fas fa-exclamation-circle"></i>
                <span class="num"><?php echo $uyari_count; ?></span> Uyarı
            </div>
            <div class="stat-chip success">
                <i class="fas fa-check-circle"></i>
                <span class="num"><?php echo $iyi_count; ?></span> İyi
            </div>
            <div class="stat-chip muted">
                <i class="fas fa-question-circle"></i>
                <span class="num"><?php echo $belirsiz_count; ?></span> Belirsiz
            </div>
        </div>
        
        <!-- Info Compact -->
        <div class="info-compact">
            <i class="fas fa-info-circle"></i>
            <span><strong>Formül:</strong> <code>Fark% = (Kritik - Toplam) / Kritik × 100</code></span>
            <div class="legend-inline">
                <span><span class="legend-dot" style="background:#dc2626;"></span> &gt;50%</span>
                <span><span class="legend-dot" style="background:#d97706;"></span> 0-50%</span>
                <span><span class="legend-dot" style="background:#16a34a;"></span> &lt;0%</span>
                <span><span class="legend-dot" style="background:#9ca3af;"></span> Belirsiz</span>
            </div>
        </div>
        
        <!-- Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list"></i> Üretim Öneri Listesi</span>
                <span style="font-size: 11px; opacity: 0.8;"><?php echo count($products); ?> ürün</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Ürün</th>
                            <th class="text-right">Stok</th>
                            <th class="text-right">Üretimde</th>
                            <th class="text-right">Toplam</th>
                            <th class="text-right">Kritik</th>
                            <th class="text-right">Açık</th>
                            <th class="text-right">Fark%</th>
                            <th class="text-right">Üretilebilir</th>
                            <th class="text-right">Önerilen</th>
                            <th class="text-center">Durum</th>
                            <th class="text-center no-print">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sira = 1; foreach ($products as $p): 
                            $row_class = '';
                            $badge = ['class' => 'badge-belirsiz', 'text' => '-'];
                            if ($p['kritik_stok_seviyesi'] <= 0) {
                                $badge = ['class' => 'badge-belirsiz', 'text' => '-'];
                            } elseif ($p['yuzde_fark'] > 50) {
                                $row_class = 'row-acil';
                                $badge = ['class' => 'badge-acil', 'text' => 'ACİL'];
                            } elseif ($p['yuzde_fark'] > 0) {
                                $badge = ['class' => 'badge-uyari', 'text' => 'UYARI'];
                            } else {
                                $badge = ['class' => 'badge-iyi', 'text' => 'İYİ'];
                            }
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td class="text-center text-muted"><?php echo $sira++; ?></td>
                            <td>
                                <span class="font-semibold"><?php echo htmlspecialchars($p['urun_ismi']); ?></span>
                                <small class="text-muted ml-1">#<?php echo $p['urun_kodu']; ?></small>
                            </td>
                            <td class="text-right"><?php echo number_format($p['stok_miktari'], 0, ',', '.'); ?></td>
                            <td class="text-right">
                                <?php if ($p['uretimde_miktar'] > 0): ?>
                                    <span class="text-info font-semibold"><?php echo number_format($p['uretimde_miktar'], 0, ',', '.'); ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right font-semibold"><?php echo number_format($p['toplam_mevcut'], 0, ',', '.'); ?></td>
                            <td class="text-right"><?php echo number_format($p['kritik_stok_seviyesi'], 0, ',', '.'); ?></td>
                            <td class="text-right">
                                <?php if ($p['kritik_stok_seviyesi'] > 0): ?>
                                    <?php if ($p['acik'] > 0): ?>
                                        <span class="text-danger font-semibold"><?php echo number_format($p['acik'], 0, ',', '.'); ?></span>
                                    <?php else: ?>
                                        <span class="text-success">+<?php echo number_format(abs($p['acik']), 0, ',', '.'); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($p['kritik_stok_seviyesi'] > 0): ?>
                                    <span class="font-semibold <?php echo $p['yuzde_fark'] > 50 ? 'text-danger' : ($p['yuzde_fark'] > 0 ? 'text-warning' : 'text-success'); ?>">
                                        %<?php echo number_format($p['yuzde_fark'], 0); ?>
                                    </span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($p['uretilebilir_miktar'] > 0): ?>
                                    <span class="text-success font-semibold"><?php echo number_format($p['uretilebilir_miktar'], 0, ',', '.'); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($p['onerilen_uretim'] > 0): ?>
                                    <span class="font-semibold" style="color: var(--primary);"><?php echo number_format($p['onerilen_uretim'], 0, ',', '.'); ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-center"><span class="badge-sm <?php echo $badge['class']; ?>"><?php echo $badge['text']; ?></span></td>
                            <td class="text-center no-print">
                                <?php if ($p['onerilen_uretim'] > 0 && $p['uretilebilir_miktar'] > 0): ?>
                                    <a href="montaj_is_emirleri.php?urun_kodu=<?php echo $p['urun_kodu']; ?>&miktar=<?php echo $p['onerilen_uretim']; ?>" 
                                       class="btn btn-dark btn-xs" title="İş Emri Oluştur">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                <?php elseif ($p['uretilebilir_miktar'] <= 0 && $p['acik'] > 0): ?>
                                    <span class="text-muted" title="Malzeme yetersiz"><i class="fas fa-ban"></i></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                        <tr><td colspan="12" class="text-center py-4 text-muted">Henüz ürün kaydı bulunmuyor.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <i class="fas fa-info-circle"></i> Üretilebilir miktar kritik bileşenlere (kutu, takım, esans) göre hesaplanır.
            </div>
        </div>
        
        <!-- Detaylı Açıklamalar -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header" style="background: #374151; padding: 8px 12px; font-size: 12px;">
                        <i class="fas fa-columns"></i> Kolon Açıklamaları
                    </div>
                    <div class="card-body" style="padding: 12px; font-size: 11px;">
                        <table class="table table-sm mb-0" style="font-size: 11px;">
                            <tr><td style="width: 100px;"><strong>Stok</strong></td><td>Depodaki anlık stok miktarı</td></tr>
                            <tr><td><strong>Üretimde</strong></td><td>Aktif montaj iş emirlerindeki toplam planlanan miktar (başlatıldı/üretimde)</td></tr>
                            <tr><td><strong>Toplam</strong></td><td>Stok + Üretimde = Gerçek kullanılabilir miktar</td></tr>
                            <tr><td><strong>Kritik</strong></td><td>Hedeflenen minimum stok seviyesi</td></tr>
                            <tr><td><strong>Açık</strong></td><td>Kritik - Toplam. Pozitif = eksik, Negatif = fazla</td></tr>
                            <tr><td><strong>Fark%</strong></td><td>Kritik stok hedefine göre eksiklik yüzdesi. Yüksek = acil üretim</td></tr>
                            <tr><td><strong>Üretilebilir</strong></td><td>Mevcut malzeme stoklarıyla üretilebilecek max adet (kritik bileşen bazlı)</td></tr>
                            <tr><td><strong>Önerilen</strong></td><td>min(Açık, Üretilebilir) = Gerçekçi üretim önerisi</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header" style="background: #374151; padding: 8px 12px; font-size: 12px;">
                        <i class="fas fa-question-circle"></i> Nasıl Kullanılır?
                    </div>
                    <div class="card-body" style="padding: 12px; font-size: 11px;">
                        <ol class="mb-0 pl-3" style="line-height: 1.8;">
                            <li><strong>ACİL</strong> durumundaki ürünlere öncelik verin - bunlar kritik stokun %50'sinden fazla altında</li>
                            <li><strong>Üretilebilir</strong> sütunu 0 ise malzeme eksiktir, önce malzeme temin edin</li>
                            <li><strong>Önerilen</strong> miktar, hem ihtiyacı hem de kapasitenizi dikkate alır</li>
                            <li><i class="fas fa-plus"></i> butonuna tıklayarak doğrudan montaj iş emri sayfasına gidin</li>
                            <li><i class="fas fa-ban text-muted"></i> işareti malzeme yetersizliğini gösterir</li>
                            <li><strong>Belirsiz</strong> durumundaki ürünlerin kritik stok seviyesi tanımlı değil</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ek Notlar -->
        <div class="card mt-3">
            <div class="card-header" style="background: #1e40af; padding: 8px 12px; font-size: 12px;">
                <i class="fas fa-lightbulb"></i> Kritik Bileşen Mantığı
            </div>
            <div class="card-body" style="padding: 12px; font-size: 11px;">
                <p class="mb-2">
                    <strong>Üretilebilir miktar</strong> hesaplamasında sadece <strong>kritik bileşenler</strong> dikkate alınır:
                </p>
                <ul class="mb-2">
                    <li><strong>Kutu</strong> - Ürünün ambalaj kutusu</li>
                    <li><strong>Takım</strong> - Şişe, kapak, spreyleme seti vb.</li>
                    <li><strong>Esans</strong> - Ürünün ana içeriği olan koku esansı</li>
                </ul>
                <p class="mb-0 text-muted">
                    <i class="fas fa-info-circle"></i> Etiket, jelatin gibi diğer malzemeler kritik kabul edilmez çünkü genellikle daha kolay temin edilir.
                    Her bileşen için <code>floor(stok / gerekli_miktar)</code> hesaplanır ve en düşük değer üretilebilir miktarı belirler.
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

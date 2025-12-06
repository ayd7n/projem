<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Kritik seviyeleri al
$kritik_seviyeler = [];
$result = $conn->query("SELECT urun_kodu, kritik_stok_seviyesi FROM urunler");
while ($row = $result->fetch_assoc()) {
    $kritik_seviyeler[$row['urun_kodu']] = $row['kritik_stok_seviyesi'];
}

// Toplam sipariş adetleri (beklemede + onaylandı)
$siparisler = [];
$result = $conn->query("
    SELECT sk.urun_kodu, SUM(sk.adet) as toplam_adet
    FROM siparisler s
    JOIN siparis_kalemleri sk ON s.siparis_id = sk.siparis_id
    WHERE s.durum IN ('beklemede', 'onaylandi')
    GROUP BY sk.urun_kodu
");
while ($row = $result->fetch_assoc()) {
    $siparisler[$row['urun_kodu']] = $row['toplam_adet'];
}

// Mevcut stoklar
$stoklar = [];
$result = $conn->query("SELECT urun_kodu, stok_miktari FROM urunler");
while ($row = $result->fetch_assoc()) {
    $stoklar[$row['urun_kodu']] = $row['stok_miktari'];
}

// Mevcut iş emirlerinden gelecek üretim
$gelecek_urun = [];
$result = $conn->query("SELECT urun_kodu, planlanan_miktar FROM montaj_is_emirleri WHERE durum = 'uretimde'");
while ($row = $result->fetch_assoc()) {
    $gelecek_urun[$row['urun_kodu']] = ($gelecek_urun[$row['urun_kodu']] ?? 0) + $row['planlanan_miktar'];
}

// Mevcut iş emirlerinden gelecek esans
$gelecek_esans = [];
$result = $conn->query("SELECT esans_kodu, planlanan_miktar FROM esans_is_emirleri WHERE durum = 'uretimde'");
while ($row = $result->fetch_assoc()) {
    $gelecek_esans[$row['esans_kodu']] = ($gelecek_esans[$row['esans_kodu']] ?? 0) + $row['planlanan_miktar'];
}

// Esans stokları
$esans_stoklar = [];
$result = $conn->query("SELECT esans_kodu, stok_miktari FROM esanslar");
while ($row = $result->fetch_assoc()) {
    $esans_stoklar[$row['esans_kodu']] = $row['stok_miktari'];
}

// Malzeme stokları
$malzeme_stoklar = [];
$result = $conn->query("SELECT malzeme_kodu, stok_miktari FROM malzemeler");
while ($row = $result->fetch_assoc()) {
    $malzeme_stoklar[$row['malzeme_kodu']] = $row['stok_miktari'];
}

// Mevcut malzeme siparişleri
$malzeme_siparisler = [];
$result = $conn->query("SELECT malzeme_kodu, SUM(miktar) as toplam_miktar FROM malzeme_siparisler WHERE durum = 'siparis_verildi' GROUP BY malzeme_kodu");
while ($row = $result->fetch_assoc()) {
    $malzeme_siparisler[$row['malzeme_kodu']] = $row['toplam_miktar'];
}

// Ürün ağacı
$urun_agaci = [];
$result = $conn->query("SELECT urun_kodu, bilesenin_malzeme_turu, bilesen_kodu, bilesen_miktari FROM urun_agaci");
while ($row = $result->fetch_assoc()) {
    $urun_agaci[$row['urun_kodu']][] = $row;
}

// Esans ağacı
$esans_agaci = [];
$result = $conn->query("SELECT ua.urun_kodu as esans_id, ua.bilesen_kodu, ua.bilesen_miktari FROM urun_agaci ua JOIN esanslar e ON ua.urun_kodu = e.esans_id WHERE ua.agac_turu = 'esans'");
while ($row = $result->fetch_assoc()) {
    $esans_agaci[$row['esans_id']][] = $row;
}

// Hesaplamalar
$uretim_ihtiyaclari = [];
foreach ($siparisler as $urun_kodu => $toplam_siparis) {
    $mevcut_stok = $stoklar[$urun_kodu] ?? 0;
    $gelecek = $gelecek_urun[$urun_kodu] ?? 0;
    $kritik = $kritik_seviyeler[$urun_kodu] ?? 0;
    $uretilecek = max(0, $toplam_siparis + $kritik - ($mevcut_stok + $gelecek));
    $uretim_ihtiyaclari[$urun_kodu] = [
        'toplam_siparis' => $toplam_siparis,
        'mevcut_stok' => $mevcut_stok,
        'gelecek' => $gelecek,
        'kritik' => $kritik,
        'uretilecek' => $uretilecek
    ];
}

// Esans ihtiyaçları
$esans_ihtiyaclari = [];
foreach ($uretim_ihtiyaclari as $urun_kodu => $data) {
    $uretilecek_urun = $data['uretilecek'];
    if (isset($urun_agaci[$urun_kodu])) {
        foreach ($urun_agaci[$urun_kodu] as $bilesen) {
            if ($bilesen['bilesenin_malzeme_turu'] == 'esans') {
                $esans_kodu = $bilesen['bilesen_kodu'];
                $miktar = $bilesen['bilesen_miktari'] * $uretilecek_urun;
                $esans_ihtiyaclari[$esans_kodu] = ($esans_ihtiyaclari[$esans_kodu] ?? 0) + $miktar;
            }
        }
    }
}

// Esans üretilecek
$esans_uretilecek = [];
foreach ($esans_ihtiyaclari as $esans_kodu => $toplam_ihtiyac) {
    $mevcut_stok = $esans_stoklar[$esans_kodu] ?? 0;
    $gelecek = $gelecek_esans[$esans_kodu] ?? 0;
    $uretilecek = max(0, $toplam_ihtiyac - $mevcut_stok - $gelecek);
    $esans_uretilecek[$esans_kodu] = $uretilecek;
}

// Malzeme ihtiyaçları
$malzeme_ihtiyaclari = [];
foreach ($esans_uretilecek as $esans_kodu => $uretilecek_esans) {
    $esans_id = null;
    $result = $conn->query("SELECT esans_id FROM esanslar WHERE esans_kodu = '$esans_kodu'");
    if ($row = $result->fetch_assoc()) {
        $esans_id = $row['esans_id'];
    }
    if ($esans_id && isset($esans_agaci[$esans_id])) {
        foreach ($esans_agaci[$esans_id] as $bilesen) {
            $malzeme_kodu = $bilesen['bilesen_kodu'];
            $miktar = $bilesen['bilesen_miktari'] * $uretilecek_esans;
            $malzeme_ihtiyaclari[$malzeme_kodu] = ($malzeme_ihtiyaclari[$malzeme_kodu] ?? 0) + $miktar;
        }
    }
}

// Etiket ihtiyaçları (ürün ağacından)
$etiket_ihtiyaclari = [];
foreach ($uretim_ihtiyaclari as $urun_kodu => $data) {
    $uretilecek_urun = $data['uretilecek'];
    if (isset($urun_agaci[$urun_kodu])) {
        foreach ($urun_agaci[$urun_kodu] as $bilesen) {
            if ($bilesen['bilesenin_malzeme_turu'] == 'etiket') {
                $etiket_kodu = $bilesen['bilesen_kodu'];
                $miktar = $bilesen['bilesen_miktari'] * $uretilecek_urun;
                $etiket_ihtiyaclari[$etiket_kodu] = ($etiket_ihtiyaclari[$etiket_kodu] ?? 0) + $miktar;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dinamik Üretim Analizi - Parfüm ERP</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stil.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #8b5cf6;
            --accent: #f59e0b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;

            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;

            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);

            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);

            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Ubuntu', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-color);
            color: var(--text-primary);
            margin: 0;
            padding-top: 0;
            min-height: 100vh;
            line-height: 1.5;
            font-size: 13px;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #ddd;
        }

        ::-webkit-scrollbar-thumb {
            background: #999;
            border-radius: 0;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #666;
        }

        /* Main Content */
        .main-content {
            padding: 1.5rem 0;
        }







        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0.75rem 1rem;
            box-sizing: border-box;
        }

        h1 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 1.25rem;
        }

        h2 {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.1rem;
        }

        h3 {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        th,
        td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        ul {
            padding-left: 1.5rem;
            margin: 0.5rem 0;
        }

        li {
            margin-bottom: 0.3rem;
        }

        /* Card Styles */
        .card {
            background: linear-gradient(135deg, var(--card-bg) 0%, rgba(255,255,255,0.95) 100%);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem 1.25rem;
            border-bottom: none;
            position: relative;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .card-header h2 i {
            font-size: 1rem;
            opacity: 0.9;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-description {
            margin-bottom: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
            font-weight: 500;
        }

        .sub-card {
            background: linear-gradient(135deg, #f8fafc 0%, rgba(255,255,255,0.8) 100%);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sub-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .sub-card-title {
            color: var(--text-primary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sub-card-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, var(--primary) 0%, transparent 100%);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--card-bg) 0%, rgba(255,255,255,0.95) 100%);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .summary-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .card-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: white;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .card-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            border-radius: 50%;
            transform: scale(1.2);
            opacity: 0.3;
        }

        .card-icon i {
            position: relative;
            z-index: 1;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }

        .card-content {
            padding: 1.25rem;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .card-content h3 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.2;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .card-content p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.9;
        }

        /* Animasyon için */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .summary-card {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .summary-card:nth-child(1) { animation-delay: 0.1s; }
        .summary-card:nth-child(2) { animation-delay: 0.2s; }
        .summary-card:nth-child(3) { animation-delay: 0.3s; }
        .summary-card:nth-child(4) { animation-delay: 0.4s; }

        .bg-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .bg-secondary {
            background: linear-gradient(135deg, var(--secondary) 0%, #a78bfa 100%);
            color: white;
        }

        .bg-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
        }

        .bg-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .bg-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            color: white;
        }

        .bg-info {
            background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
            color: white;
        }

        .badge {
            display: inline-block;
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
            border-radius: var(--radius);
            font-weight: 600;
            color: white;
            text-transform: uppercase;
        }

        .badge-success {
            background: var(--success);
        }

        .badge-danger {
            background: var(--danger);
        }

        .badge-warning {
            background: var(--warning);
            color: #fff;
        }

        .badge-primary {
            background: var(--primary);
        }

        .badge-info {
            background: var(--info);
        }

        .status-beklemede {
            background: var(--warning);
            color: #fff;
        }

        .status-onaylandi {
            background: var(--success);
            color: #fff;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        /* Guide Steps */
        .guide-steps {
            margin: 1.5rem 0;
        }

        .step {
            display: flex;
            margin-bottom: 1rem;
            align-items: flex-start;
            background: linear-gradient(135deg, #f8fafc 0%, rgba(255,255,255,0.8) 100%);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-left: 4px solid var(--primary);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .step:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow);
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
            margin-right: 1rem;
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
            position: relative;
            z-index: 1;
        }

        .step-content {
            flex: 1;
        }

        .step-content h4 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.4;
        }

        .step-content h4 i {
            color: var(--primary);
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .step-content p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        /* Alert Styles */
        .alert {
            border-radius: var(--radius-md);
            padding: 1rem 1.25rem;
            margin: 1rem 0;
            border: 1px solid;
            font-size: 0.9rem;
            line-height: 1.5;
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: currentColor;
            opacity: 0.6;
        }

        .alert strong {
            font-weight: 700;
            color: inherit;
        }

        .alert-info {
            background: linear-gradient(135deg, #e3f2fd 0%, rgba(227, 242, 253, 0.8) 100%);
            border-color: #90caf9;
            color: #1565c0;
            box-shadow: var(--shadow-sm);
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff3e0 0%, rgba(255, 243, 224, 0.8) 100%);
            border-color: #ffb74d;
            color: #e65100;
            box-shadow: var(--shadow-sm);
        }

        .alert-success {
            background: linear-gradient(135deg, #e8f5e9 0%, rgba(232, 245, 233, 0.8) 100%);
            border-color: #81c784;
            color: #2e7d32;
            box-shadow: var(--shadow-sm);
        }

        /* List Group */
        .list-group {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
            background: var(--card-bg);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .list-group-item {
            padding: 1rem 1.25rem;
            border: none;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            background: transparent;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .list-group-item:hover {
            background: rgba(99, 102, 241, 0.03);
            transform: translateX(4px);
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .list-group-item:first-child {
            border-top-left-radius: var(--radius-md);
            border-top-right-radius: var(--radius-md);
        }

        .list-group-item:last-child {
            border-bottom-left-radius: var(--radius-md);
            border-bottom-right-radius: var(--radius-md);
        }

        .list-group-flush .list-group-item {
            border-left: none;
            border-right: none;
            border-radius: 0;
        }

        .list-group-flush .list-group-item:first-child {
            border-top: none;
        }

        .list-group-flush .list-group-item:last-child {
            border-bottom: none;
        }

        .list-unstyled {
            padding-left: 0;
            list-style: none;
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            border-radius: var(--radius);
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid;
            transition: var(--transition);
            text-decoration: none;
            cursor: pointer;
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            background: white;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }

        .btn-outline-info {
            color: var(--info);
            border-color: var(--info);
            background: white;
        }

        .btn-outline-info:hover {
            background: var(--info);
            color: white;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.7rem;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .page-header h1 {
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .page-header h1 i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .page-header p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.8rem;
        }

        /* Row & Column */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -0.75rem;
            margin-right: -0.75rem;
        }

        .col-md-6,
        .col-lg-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        .table {
            width: 100%;
            margin-bottom: 0;
            background: transparent;
            border-collapse: collapse;
        }

        .table th {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: none;
            padding: 1rem 0.75rem;
            white-space: nowrap;
            position: relative;
        }

        .table th::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 1px;
            background: rgba(255,255,255,0.3);
        }

        .table th:last-child::after {
            display: none;
        }

        .table td {
            padding: 0.75rem;
            border: none;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            color: var(--text-primary);
            font-size: 0.85rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table tbody tr {
            background: var(--card-bg);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table-striped tbody tr:nth-of-type(even) {
            background: rgba(99, 102, 241, 0.02);
        }

        .table-striped tbody tr:hover {
            background: rgba(99, 102, 241, 0.04);
            transform: translateX(2px);
        }

        /* Utility Classes */
        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .text-primary {
            color: var(--primary) !important;
        }

        .text-secondary {
            color: var(--text-secondary) !important;
        }

        .text-success {
            color: var(--success) !important;
        }

        .text-danger {
            color: var(--danger) !important;
        }

        .text-warning {
            color: var(--warning) !important;
        }

        .text-info {
            color: var(--info) !important;
        }

        .mt-1 {
            margin-top: 0.25rem;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .mb-1 {
            margin-bottom: 0.25rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mobile-bottom-nav {
            display: none;
        }

        @media (max-width: 992px) {
            body {
                padding-top: 70px;
            }

            .container {
                padding: 1rem;
            }

            .top-bar-wrapper {
                padding: 0.75rem 1rem;
            }

            .logo h1 {
                font-size: 1.25rem;
            }

            .user-controls {
                gap: 0.5rem;
            }

            .user-info {
                display: none;
            }

            .user-controls a .link-text {
                display: none;
            }

            .page-header {
                margin-bottom: 2rem;
                padding: 1rem 0;
            }

            .page-header h1 {
                font-size: 1.75rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            .page-header p {
                font-size: 0.9375rem;
            }

            .col-md-6,
            .col-lg-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .summary-cards {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin-bottom: 2rem;
            }

            .card {
                margin-bottom: 1.5rem;
            }

            .card-header {
                padding: 1.25rem 1.5rem;
            }

            .card-header h2 {
                font-size: 1.125rem;
            }

            .card-body {
                padding: 1.5rem;
            }

            .table-responsive {
                margin: 0 -1rem;
                width: calc(100% + 2rem);
                border-radius: 0;
            }

            .table th,
            .table td {
                padding: 0.75rem 1rem;
            }

            .step {
                padding: 1.25rem;
            }

            .step-number {
                width: 36px;
                height: 36px;
                font-size: 1rem;
                margin-right: 1rem;
            }

            .mobile-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.98);
                border-top: 1px solid var(--border-color);
                padding: 0.75rem 0;
                z-index: 1000;
                box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                justify-content: space-around;
                align-items: center;
            }

            .nav-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-decoration: none;
                color: var(--text-muted);
                transition: var(--transition-fast);
                padding: 0.375rem 0.5rem;
                border-radius: var(--radius);
                width: 19%;
                min-width: 55px;
                text-align: center;
            }

            .nav-item i {
                font-size: 1.125rem;
                margin-bottom: 0.25rem;
            }

            .nav-item .nav-text {
                font-size: 0.6875rem;
                font-weight: 500;
            }

            .nav-item:hover,
            .nav-item.active {
                color: var(--primary);
                background: rgba(99, 102, 241, 0.08);
            }
        }
    </style>
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

        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-chart-line"></i> Dinamik Üretim Analizi</h1>
                <p class="text-muted">Siparişlerin karşılanması için gerekli üretim planlaması ve malzeme ihtiyaç
                    analizi
                </p>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> <strong>Bilgi:</strong> Bu sayfadaki tüm değerler gerçek zamanlı olarak veritabanından çekilen verilere göre dinamik olarak hesaplanmaktadır. Sayfa her yenilendiğinde güncel stok durumları, sipariş bilgileri ve üretim ihtiyaçları otomatik olarak güncellenir.
                </div>
            </div>

            <div class="summary-cards">
                <div class="card summary-card">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-content">
                        <h3><?php echo array_sum(array_column($uretim_ihtiyaclari, 'toplam_siparis')); ?></h3>
                        <p>Toplam Sipariş</p>
                    </div>
                </div>
                <div class="card summary-card">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="card-content">
                        <h3><?php echo array_sum(array_column($uretim_ihtiyaclari, 'uretilecek')); ?></h3>
                        <p>Üretilecek Ürün</p>
                    </div>
                </div>
                <div class="card summary-card">
                    <div class="card-icon bg-success">
                        <i class="fas fa-flask"></i>
                    </div>
                    <div class="card-content">
                        <h3><?php echo number_format(array_sum($esans_uretilecek), 2); ?></h3>
                        <p>Üretilecek Esans</p>
                    </div>
                </div>
                <div class="card summary-card">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="card-content">
                        <h3><?php echo count($malzeme_ihtiyaclari); ?></h3>
                        <p>Eksik Malzeme</p>
                    </div>
                </div>
            </div>

            <!-- Production Guide Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-clipboard-list"></i> Net Kılavuz: Yapılacak İşlemler</h2>
                </div>
                <div class="card-body">

                    <div class="guide-steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4><i class="fas fa-shopping-cart text-success"></i> Eksik Malzemeler İçin Yeni Sipariş
                                    Ver
                                    (Paralel)</h4>
                                <p>Toplam ihtiyaçlara göre yeni siparişler ver (mevcut siparişleri beklemeden):</p>
                                <ul>
                                    <?php foreach ($malzeme_ihtiyaclari as $malzeme_kodu => $ihtiyac) {
                                        $mevcut_siparis = $malzeme_siparisler[$malzeme_kodu] ?? 0;
                                        $mevcut_stok = $malzeme_stoklar[$malzeme_kodu] ?? 0;
                                        $yeni_siparis = max(0, $ihtiyac - $mevcut_siparis - $mevcut_stok);
                                        if ($yeni_siparis > 0) {
                                            echo "<li><strong>Malzeme $malzeme_kodu</strong>: $yeni_siparis birim (toplam ihtiyaç " . number_format($ihtiyac, 2) . " - mevcut sipariş $mevcut_siparis - stok $mevcut_stok = $yeni_siparis).</li>";
                                        }
                                    } ?>
                                    <?php foreach ($etiket_ihtiyaclari as $etiket_kodu => $ihtiyac) {
                                        $mevcut_siparis = $malzeme_siparisler[$etiket_kodu] ?? 0;
                                        $mevcut_stok = $malzeme_stoklar[$etiket_kodu] ?? 0;
                                        $yeni_siparis = max(0, $ihtiyac - $mevcut_siparis - $mevcut_stok);
                                        if ($yeni_siparis > 0) {
                                            echo "<li><strong>Etiket $etiket_kodu</strong>: $yeni_siparis birim (toplam ihtiyaç " . number_format($ihtiyac, 2) . " - mevcut sipariş $mevcut_siparis - stok $mevcut_stok = $yeni_siparis).</li>";
                                        }
                                    } ?>
                                </ul>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4><i class="fas fa-truck text-warning"></i> Mevcut Siparişlerin Teslimini Bekle</h4>
                                <ul>
                                    <?php
                                    $siparis_var = false;
                                    foreach ($malzeme_siparisler as $malzeme_kodu => $miktar) {
                                        if ($miktar > 0) {
                                            $siparis_var = true;
                                            echo "<li>Malzeme $malzeme_kodu: $miktar birim.</li>";
                                        }
                                    }
                                    if (!$siparis_var) {
                                        echo "<li class='text-muted'>Henüz bekleyen malzeme siparişi yok.</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4><i class="fas fa-cogs text-info"></i> Mevcut İş Emirlerini Tamamla</h4>
                                <ul>
                                    <?php
                                    $emir_var = false;
                                    foreach ($gelecek_esans as $esans_kodu => $miktar) {
                                        if ($miktar > 0) {
                                            $emir_var = true;
                                            echo "<li><strong>Esans $esans_kodu</strong>: $miktar birim üretimi tamamlanmalı.</li>";
                                        }
                                    } ?>
                                    <?php foreach ($gelecek_urun as $urun_kodu => $miktar) {
                                        if ($miktar > 0) {
                                            $emir_var = true;
                                            echo "<li><strong>Ürün $urun_kodu</strong>: $miktar adet montajı tamamlanmalı.</li>";
                                        }
                                    }
                                    if (!$emir_var) {
                                        echo "<li class='text-muted'>Tamamlanması gereken mevcut iş emri yok.</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4><i class="fas fa-calendar-check text-primary"></i> Yeni Siparişlerin Teslimini Bekle
                                </h4>
                                <p>Yeni verilen siparişlerin teslim tarihlerini kontrol et ve malzemeler ulaştıktan
                                    sonra
                                    devam et.</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h4><i class="fas fa-plus-circle text-success"></i> Ek Üretim İş Emirleri Aç</h4>
                                <p>Yeni malzemeler ulaştıktan sonra, eksik kalan ihtiyaçlar için yeni iş emirleri aç:
                                </p>
                                <ul>
                                    <?php foreach ($esans_uretilecek as $esans_kodu => $miktar) {
                                        if ($miktar > 0) {
                                            echo "<li><strong>Esans $esans_kodu</strong>: " . number_format($miktar, 2) . " birim.</li>";
                                        }
                                    } ?>
                                    <?php foreach ($uretim_ihtiyaclari as $urun_kodu => $data) {
                                        if ($data['uretilecek'] > 0) {
                                            echo "<li><strong>Ürün $urun_kodu</strong>: " . $data['uretilecek'] . " adet.</li>";
                                        }
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Uyarı:</strong> Adım 1 paralel olarak başlatılabilir. Diğer adımlar sıralı olmalı.
                        Malzeme
                        teslim tarihleri kritik.
                    </div>
                </div>
            </div>



            <!-- Production Needs Section -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-calculator"></i> Üretim İhtiyaçları</h2>
                </div>
                <div class="card-body">
                    <p class="card-description">Siparişler karşılandıktan sonra kritik stok seviyesinin altında
                        kalmaması
                        için üretim hesaplanmıştır.</p>

                    <div class="row">
                        <?php foreach ($uretim_ihtiyaclari as $urun_kodu => $data): ?>
                            <?php if ($data['uretilecek'] > 0): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="sub-card border rounded p-3 shadow-sm" style="background-color: #f8f9fa;">
                                    <h3 class="sub-card-title mb-3 pb-2 border-bottom">
                                        <?php echo $urun_kodu; ?> (<?php
                                            $result = $conn->query("SELECT urun_ismi FROM urunler WHERE urun_kodu = '$urun_kodu'");
                                            $row = $result->fetch_assoc();
                                            echo $row ? htmlspecialchars($row['urun_ismi']) : $urun_kodu;
                                        ?>)
                                    </h3>
                                    <ul class="list-unstyled mb-3">
                                        <li class="mb-2 d-flex justify-content-between">
                                            <span><strong><i class="fas fa-shopping-cart text-success me-2"></i>Toplam Sipariş Adeti:</strong></span>
                                            <span><?php echo $data['toplam_siparis']; ?></span>
                                        </li>
                                        <li class="mb-2 d-flex justify-content-between">
                                            <span><strong><i class="fas fa-box-open text-info me-2"></i>Mevcut Stok:</strong></span>
                                            <span><?php echo $data['mevcut_stok']; ?></span>
                                        </li>
                                        <li class="mb-2 d-flex justify-content-between">
                                            <span><strong><i class="fas fa-clock text-warning me-2"></i>Gelecek (İş Emri):</strong></span>
                                            <span><?php echo $data['gelecek']; ?> adet</span>
                                        </li>
                                        <li class="mb-2 d-flex justify-content-between">
                                            <span><strong><i class="fas fa-exclamation-triangle text-danger me-2"></i>Kritik Stok Seviyesi:</strong></span>
                                            <span><?php echo $data['kritik']; ?></span>
                                        </li>
                                        <li class="mb-3 pb-2 border-bottom d-flex justify-content-between">
                                            <span><strong><i class="fas fa-industry text-primary me-2"></i>Üretilecek Adet:</strong></span>
                                            <span class="text-danger font-weight-bold">
                                                <?php echo $data['uretilecek']; ?>
                                            </span>
                                        </li>
                                    </ul>

                                    <div class="mt-3">
                                        <h4 class="h6"><i class="fas fa-cubes text-primary me-2"></i>Bileşen İhtiyaçları</h4>
                                        <?php
                                        $esans_toplam = 0;
                                        $malzeme_toplam = [];
                                        $urun_bilesenler = [];
                                        $result = $conn->query("SELECT bilesenin_malzeme_turu, bilesen_kodu, bilesen_miktari FROM urun_agaci WHERE urun_kodu = '$urun_kodu'");
                                        while ($row = $result->fetch_assoc()) {
                                            $urun_bilesenler[] = $row;
                                            if ($row['bilesenin_malzeme_turu'] == 'esans') {
                                                $esans_toplam += $row['bilesen_miktari'] * $data['uretilecek'];
                                            } elseif ($row['bilesenin_malzeme_turu'] == 'malzeme') {
                                                $malzeme_toplam[$row['bilesen_kodu']] = ($malzeme_toplam[$row['bilesen_kodu']] ?? 0) + $row['bilesen_miktari'] * $data['uretilecek'];
                                            }
                                        }
                                        if ($esans_toplam > 0): ?>
                                            <div class="alert alert-light border p-2 mb-2">
                                                <strong>Esans toplam:</strong> <?php echo number_format($esans_toplam, 2); ?> birim
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($malzeme_toplam)): ?>
                                            <h5 class="h6 mt-3"><i class="fas fa-tools text-success me-2"></i>Gerekli Malzemeler</h5>
                                            <div class="mt-2">
                                                <?php foreach ($malzeme_toplam as $malzeme_kodu => $miktar): ?>
                                                    <div class="alert alert-light border p-2 mb-2">
                                                        <strong>Malzeme <?php echo $malzeme_kodu; ?>:</strong>
                                                        <?php echo number_format($miktar, 2); ?> birim
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Production Orders Section -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-file-alt"></i> Mevcut Üretim İş Emirleri</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3><i class="fas fa-flask text-warning"></i> Esans Üretim İş Emirleri (Üretimde Olan)</h3>
                            <ul class="list-group list-group-flush">
                                <?php
                                $esans_emri_var = false;
                                foreach ($gelecek_esans as $esans_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $esans_emri_var = true;
                                        ?>
                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <strong><?php echo $esans_kodu; ?></strong><br>
                                                    <small class="text-muted">Planlanan: <?php echo $miktar; ?> birim</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-warning">Üretimde</span><br>
                                                    <small class="text-muted">Tamamlanan: 0 birim</small>
                                                </div>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                endforeach;
                                if (!$esans_emri_var): ?>
                                    <li class="list-group-item text-center text-muted">
                                        <i class="fas fa-check-circle text-success"></i> Tamamlanması gereken esans iş emri
                                        yok
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h3><i class="fas fa-industry text-info"></i> Montaj Üretim İş Emirleri (Üretimde Olan)</h3>
                            <ul class="list-group list-group-flush">
                                <?php
                                $urun_emri_var = false;
                                foreach ($gelecek_urun as $urun_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $urun_emri_var = true;
                                        ?>
                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <strong><?php echo $urun_kodu; ?></strong><br>
                                                    <small class="text-muted">Planlanan: <?php echo $miktar; ?> adet</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-info">Üretimde</span><br>
                                                    <small class="text-muted">Tamamlanan: 0 adet</small>
                                                </div>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                endforeach;
                                if (!$urun_emri_var): ?>
                                    <li class="list-group-item text-center text-muted">
                                        <i class="fas fa-check-circle text-success"></i> Tamamlanması gereken montaj iş emri
                                        yok
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Material Orders Section -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-clipboard-list"></i> Malzeme Sipariş Durumları</h2>
                </div>
                <div class="card-body">
                    <p class="card-description">Eksik malzemeler için mevcut siparişler:</p>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Malzeme Kodu</th>
                                    <th>Malzeme İsmi</th>
                                    <th>Sipariş Miktarı</th>
                                    <th>Durum</th>
                                    <th class="text-right">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $malzeme_sayisi = 0;
                                foreach ($malzeme_siparisler as $malzeme_kodu => $miktar):
                                    // Get material name
                                    $result = $conn->query("SELECT malzeme_ismi FROM malzemeler WHERE malzeme_kodu = '$malzeme_kodu'");
                                    $row = $result->fetch_assoc();
                                    $malzeme_ismi = $row['malzeme_ismi'] ?? $malzeme_kodu;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $malzeme_kodu; ?></strong></td>
                                        <td><?php echo $malzeme_ismi; ?></td>
                                        <td><span class="font-weight-bold"><?php echo number_format($miktar, 2); ?></span>
                                            birim
                                        </td>
                                        <td><span class="badge badge-primary">Sipariş Verildi</span></td>
                                        <td class="text-right">
                                            <a href="malzeme_siparisler.php?filter=<?php echo $malzeme_kodu; ?>"
                                                class="btn btn-sm btn-outline-primary">Takip Et</a>
                                        </td>
                                    </tr>
                                    <?php
                                    $malzeme_sayisi++;
                                endforeach;
                                if ($malzeme_sayisi == 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="fas fa-check-circle text-success"></i> Eksik malzeme için bekleyen
                                            sipariş
                                            yok
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Production Guide Section -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-wrench"></i> Üretim Kılavuzu</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3><i class="fas fa-check-circle text-success"></i> Mevcut İş Emirleri Tamamlanması</h3>
                            <ul class="list-group">
                                <?php
                                $emir_tamamlanacak = false;
                                foreach ($gelecek_esans as $esans_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $emir_tamamlanacak = true;
                                        ?>
                                        <li class="list-group-item">
                                            <i class="fas fa-flask text-warning"></i> <?php echo $miktar; ?> birim
                                            <?php echo $esans_kodu; ?> esansı üretimi tamamlanmalı
                                        </li>
                                        <?php
                                    }
                                endforeach; ?>
                                <?php foreach ($gelecek_urun as $urun_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $emir_tamamlanacak = true;
                                        ?>
                                        <li class="list-group-item">
                                            <i class="fas fa-industry text-info"></i> <?php echo $miktar; ?> adet Ürün
                                            <?php echo $urun_kodu; ?> montajı tamamlanmalı
                                        </li>
                                        <?php
                                    }
                                endforeach;
                                if (!$emir_tamamlanacak): ?>
                                    <li class="list-group-item text-center text-muted">
                                        <i class="fas fa-check-circle text-success"></i> Tamamlanması gereken iş emri yok
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h3><i class="fas fa-plus-circle text-primary"></i> Ek Üretim İş Emirleri Açılması</h3>
                            <p>Mevcut iş emirleri tamamlandıktan sonra, eksik kalan ihtiyaçlar için yeni iş emirleri
                                açılmalı:</p>
                            <ul class="list-group">
                                <?php
                                $yeni_emir_var = false;
                                foreach ($esans_uretilecek as $esans_kodu => $miktar):
                                    if ($miktar > 0) {
                                        $yeni_emir_var = true;
                                        ?>
                                        <li class="list-group-item">
                                            <i class="fas fa-flask text-warning"></i> <?php echo $esans_kodu; ?> için
                                            <?php echo number_format($miktar, 2); ?> birim. Yeni esans iş emri açılmalı.
                                        </li>
                                        <?php
                                    }
                                endforeach; ?>
                                <?php foreach ($uretim_ihtiyaclari as $urun_kodu => $data):
                                    if ($data['uretilecek'] > 0) {
                                        $yeni_emir_var = true;
                                        ?>
                                        <li class="list-group-item">
                                            <i class="fas fa-industry text-info"></i> Ürün <?php echo $urun_kodu; ?> için
                                            <?php echo $data['uretilecek']; ?> adet. Yeni montaj iş emri açılmalı.
                                        </li>
                                        <?php
                                    }
                                endforeach;
                                if (!$yeni_emir_var): ?>
                                    <li class="list-group-item text-center text-muted">
                                        <i class="fas fa-check-circle text-success"></i> Yeni açılacak iş emri yok
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-clipboard-check"></i> Özet</h2>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <div class="list-group-item border-0 px-0 py-2">
                            <i class="fas fa-chart-line text-primary me-3"></i>
                            <strong>Kritik stok seviyelerine göre üretim planlandı.</strong>
                        </div>
                        <?php foreach ($uretim_ihtiyaclari as $urun_kodu => $data): ?>
                            <?php if ($data['uretilecek'] > 0): ?>
                                <div class="list-group-item border-0 px-0 py-2">
                                    <i class="fas fa-box text-info me-3"></i>
                                    Toplam üretim ihtiyacı: Ürün <strong><?php echo $urun_kodu; ?></strong> için
                                    <span class="badge badge-primary"><?php echo $data['uretilecek']; ?> adet</span>
                                    (sipariş sonrası kritik seviye için).
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (array_sum($esans_uretilecek) > 0): ?>
                            <div class="list-group-item border-0 px-0 py-2">
                                <i class="fas fa-flask text-warning me-3"></i>
                                Esans üretimi:
                                <?php
                                $esans_list = [];
                                foreach ($esans_uretilecek as $esans_kodu => $miktar) {
                                    if ($miktar > 0) {
                                        $esans_list[] = "<strong>" . $esans_kodu . "</strong> için <span class='badge badge-warning'>" . number_format($miktar, 2) . " birim</span>";
                                    }
                                }
                                echo implode(", ", $esans_list);
                                ?>, bekleyen.
                            </div>
                        <?php endif; ?>
                        <div class="list-group-item border-0 px-0 py-2">
                            <i class="fas fa-tasks text-success me-3"></i>
                            Mevcut iş emirleri tamamlanmalı, ardından eksik kalan için yeni iş emirleri açılmalı.
                        </div>
                        <div class="list-group-item border-0 px-0 py-2">
                            <i class="fas fa-shopping-cart text-danger me-3"></i>
                            Malzeme siparişleri artırılmalı veya yeni siparişler verilmeli.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Bottom Navigation -->
        <nav class="mobile-bottom-nav">
            <a href="navigation.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span class="nav-text">Ana Sayfa</span>
            </a>
            <a href="#operations" class="nav-item">
                <i class="fas fa-cogs"></i>
                <span class="nav-text">İşlemler</span>
            </a>
            <a href="#reports" class="nav-item">
                <i class="fas fa-chart-pie"></i>
                <span class="nav-text">Raporlar</span>
            </a>
            <a href="ayarlar.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span class="nav-text">Ayarlar</span>
            </a>
            <a href="logout.php" class="nav-item" title="Çıkış Yap">
                <i class="fas fa-sign-out-alt"></i>
                <span class="nav-text">Çıkış</span>
            </a>
        </nav>
    </div><!-- container -->
    </div><!-- main-content -->

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <?php
    $conn->close();
    ?>
</body>

</html>

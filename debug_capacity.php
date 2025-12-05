<?php
include 'config.php';
header('Content-Type: application/json; charset=utf-8');

$urun_kodu = 30;
$log = [];

// 1. Get Components
$stmt = $connection->prepare("
    SELECT 
        ua.bilesen_ismi,
        ua.bilesenin_malzeme_turu,
        ua.bilesen_miktari,
        COALESCE(
            CASE 
                WHEN ua.bilesenin_malzeme_turu = 'esans' THEN e.stok_miktari 
                ELSE m.stok_miktari 
            END, 0
        ) AS stok_miktari
    FROM urun_agaci ua
    LEFT JOIN esanslar e ON (ua.bilesen_ismi = e.esans_ismi)
    LEFT JOIN malzemeler m ON (ua.bilesen_ismi = m.malzeme_ismi)
    WHERE ua.urun_kodu = ? AND ua.agac_turu = 'urun'
");
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$res = $stmt->get_result();

$bilesenler = [];
while ($row = $res->fetch_assoc()) {
    $bilesenler[] = $row;
}

$uretilebilecek = PHP_FLOAT_MAX;

foreach ($bilesenler as $key => $bilesen) {
    $item_log = [
        'name' => $bilesen['bilesen_ismi'],
        'type' => $bilesen['bilesenin_malzeme_turu'],
        'req' => $bilesen['bilesen_miktari'],
        'stock' => $bilesen['stok_miktari'],
        'potential_sub' => 0
    ];

    $potansiyel_kaynak = (float) $bilesen['stok_miktari'];

    if ($bilesen['bilesenin_malzeme_turu'] == 'esans') {
        $stmt_esans = $connection->prepare("SELECT esans_id FROM esanslar WHERE esans_ismi = ?");
        $stmt_esans->bind_param('s', $bilesen['bilesen_ismi']);
        $stmt_esans->execute();
        $esans_id_row = $stmt_esans->get_result()->fetch_assoc();

        if ($esans_id_row) {
            $esans_id = $esans_id_row['esans_id'];
            $esans_uretim_kapasitesi = PHP_FLOAT_MAX;

            // Fetch inputs
            $stmt_alt = $connection->prepare("
                 SELECT 
                     ua.bilesen_ismi, 
                     ua.bilesen_miktari,
                     COALESCE(m.stok_miktari, 0) AS stok_miktari
                 FROM urun_agaci ua 
                 LEFT JOIN malzemeler m ON ua.bilesen_ismi = m.malzeme_ismi
                 WHERE ua.urun_kodu = ? AND ua.agac_turu = 'esans'
             ");
            $stmt_alt->bind_param('i', $esans_id);
            $stmt_alt->execute();
            $res_alt = $stmt_alt->get_result();

            $sub_comps = [];
            while ($alt = $res_alt->fetch_assoc()) {
                $alt_cap = ($alt['bilesen_miktari'] > 0) ? ((float) $alt['stok_miktari'] / $alt['bilesen_miktari']) : 999999;
                $sub_comps[] = ['name' => $alt['bilesen_ismi'], 'stock' => $alt['stok_miktari'], 'req' => $alt['bilesen_miktari'], 'cap' => $alt_cap];
                if ($alt_cap < $esans_uretim_kapasitesi) {
                    $esans_uretim_kapasitesi = $alt_cap;
                }
            }
            $item_log['sub_components'] = $sub_comps;

            if ($esans_uretim_kapasitesi != PHP_FLOAT_MAX) {
                $potansiyel_kaynak += $esans_uretim_kapasitesi;
                $item_log['potential_sub'] = $esans_uretim_kapasitesi;
            }
        }
    }

    $bu_bilesenle_max = ($bilesen['bilesen_miktari'] > 0) ? ($potansiyel_kaynak / $bilesen['bilesen_miktari']) : 999999;
    $item_log['total_resource'] = $potansiyel_kaynak;
    $item_log['max_products'] = $bu_bilesenle_max;

    $log[] = $item_log;

    if ($bu_bilesenle_max < $uretilebilecek) {
        $uretilebilecek = $bu_bilesenle_max;
    }
}

echo json_encode(['final_result' => (int) $uretilebilecek, 'details' => $log], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
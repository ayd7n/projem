<?php
include 'config.php'; // Veritabanı bağlantı ayarlarınız

header('Content-Type: application/json; charset=utf-8');
require_staff(true);
require_permission('page:view:urunler', true);

// Ürün kodu parametresini al
$urun_kodu = isset($_GET['urun_kodu']) ? (int)$_GET['urun_kodu'] : null;

if (!$urun_kodu) {
    echo json_encode(['error' => 'Ürün kodu belirtilmemiş'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Ürün bilgileri
$stmt = $connection->prepare("
    SELECT
        u.urun_kodu,
        u.urun_ismi,
        u.stok_miktari,
        u.kritik_stok_seviyesi,
        u.birim,
        u.satis_fiyati
    FROM
        urunler u
    WHERE
        u.urun_kodu = ?
");
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$result = $stmt->get_result();
$urun = $result->fetch_assoc();

if (!$urun) {
    echo json_encode(['error' => 'Ürün bulunamadı'], JSON_UNESCAPED_UNICODE);
    exit;
}

function getBekleyenSatinalmaMiktari($connection, $malzeme_kodu, $malzeme_adi) {
    $kod = is_numeric($malzeme_kodu) ? (int) $malzeme_kodu : 0;
    $adi = trim((string) $malzeme_adi);

    $stmt = $connection->prepare("
        SELECT COALESCE(SUM(GREATEST(ssk.miktar - COALESCE(ssk.teslim_edilen_miktar, 0), 0)), 0) AS siparis_verilen_miktar
        FROM satinalma_siparis_kalemleri ssk
        JOIN satinalma_siparisler ss ON ss.siparis_id = ssk.siparis_id
        WHERE ss.durum IN ('onaylandi', 'gonderildi', 'kismen_teslim')
          AND (ssk.malzeme_kodu = ? OR ssk.malzeme_adi = ?)
    ");
    $stmt->bind_param('is', $kod, $adi);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (float) ($result['siparis_verilen_miktar'] ?? 0);
}

// Ürünün bileşenleri
$stmt = $connection->prepare("
    SELECT
        ua.bilesen_kodu,
        ua.bilesen_ismi,
        ua.bilesenin_malzeme_turu,
        ua.bilesen_miktari,
        COALESCE(
            CASE
                WHEN ua.bilesenin_malzeme_turu = 'esans'
                THEN e.stok_miktari
                ELSE m.stok_miktari
            END, 0
        ) AS stok_miktari
    FROM
        urun_agaci ua
        LEFT JOIN esanslar e ON (ua.bilesen_ismi = e.esans_ismi)
        LEFT JOIN malzemeler m ON (ua.bilesen_ismi = m.malzeme_ismi)
    WHERE
        ua.urun_kodu = ?
        AND ua.agac_turu = 'urun'
    ORDER BY
        ua.bilesen_ismi ASC
");
$stmt->bind_param('i', $urun_kodu);
$stmt->execute();
$result = $stmt->get_result();
$bilesenler = [];
while ($row = $result->fetch_assoc()) {
    $bilesenler[] = $row;
}

// Her bileşen için ekstra bilgileri al
foreach ($bilesenler as $key => $bilesen) {
    if ($bilesen['bilesenin_malzeme_turu'] != 'esans') {
        $bilesenler[$key]['siparis_verilen_miktar'] = getBekleyenSatinalmaMiktari($connection, $bilesen['bilesen_kodu'], $bilesen['bilesen_ismi']);
    } else {
        // Esans ise üretimdeki miktarı ve alt bileşenleri al
        $stmt = $connection->prepare("
            SELECT
                COALESCE(SUM(planlanan_miktar), 0) AS uretimdeki_miktar
            FROM
                esans_is_emirleri
            WHERE
                esans_ismi = ?
                AND durum = 'uretimde'
        ");
        $stmt->bind_param('s', $bilesen['bilesen_ismi']);
        $stmt->execute();
        $result = $stmt->get_result();
        $uretimdeki = $result->fetch_assoc();

        $bilesenler[$key]['uretimdeki_miktar'] = (float)$uretimdeki['uretimdeki_miktar'];

        // Esansın alt bileşenlerini al
        $stmt = $connection->prepare("
            SELECT
                e.esans_id
            FROM
                esanslar e
            WHERE
                e.esans_ismi = ?
        ");
        $stmt->bind_param('s', $bilesen['bilesen_ismi']);
        $stmt->execute();
        $result = $stmt->get_result();
        $esans_id = $result->fetch_assoc();

        if ($esans_id) {
            $stmt = $connection->prepare("
                SELECT
                    ua.bilesen_kodu AS malzeme_kodu,
                    ua.bilesen_ismi AS malzeme_ismi,
                    ua.bilesen_miktari AS malzeme_miktari,
                    COALESCE(m.stok_miktari, 0) AS stok_miktari
                FROM
                    urun_agaci ua
                    LEFT JOIN malzemeler m ON (ua.bilesen_ismi = m.malzeme_ismi)
                WHERE
                    ua.urun_kodu = ?
                    AND ua.agac_turu = 'esans'
                ORDER BY
                    ua.bilesen_ismi ASC
            ");
            $stmt->bind_param('i', $esans_id['esans_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $esans_bilesenleri = [];
            while ($row = $result->fetch_assoc()) {
                $esans_bilesenleri[] = $row;
            }

            foreach ($esans_bilesenleri as $ek => $esans_bilesen) {
                $esans_bilesenleri[$ek]['siparis_verilen_miktar'] = getBekleyenSatinalmaMiktari($connection, $esans_bilesen['malzeme_kodu'], $esans_bilesen['malzeme_ismi']);
            }

            $bilesenler[$key]['esans_bilesenleri'] = $esans_bilesenleri;
        } else {
            $bilesenler[$key]['esans_bilesenleri'] = [];
        }
    }
}

// Sonuçları oluştur
$result = [
    'urun_bilgileri' => $urun,
    'bilesenler_ve_miktarlar' => $bilesenler
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>

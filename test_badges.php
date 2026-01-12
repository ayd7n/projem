<?php
include 'config.php';

// Function to count records in a table
function get_record_count($connection, $table_name, $condition = '') {
    $query = "SELECT COUNT(*) as total FROM " . $table_name;
    if (!empty($condition)) {
        $query .= " WHERE " . $condition;
    }
    $result = $connection->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

// Get counts for each module
$musteri_count = get_record_count($connection, 'musteriler');
$personel_count = get_record_count($connection, 'personeller', "e_posta NOT IN ('admin@parfum.com', 'admin2@parfum.com')");
$tedarikci_count = get_record_count($connection, 'tedarikciler');
$urun_count = get_record_count($connection, 'urunler');
$esans_count = get_record_count($connection, 'esanslar');
$malzeme_count = get_record_count($connection, 'malzemeler');
$siparis_count = get_record_count($connection, 'siparisler');
$esans_is_emri_count = get_record_count($connection, 'esans_is_emirleri');
$montaj_is_emri_count = get_record_count($connection, 'montaj_is_emirleri');
$lokasyon_count = get_record_count($connection, 'lokasyonlar');
$tank_count = get_record_count($connection, 'tanklar');
$is_merkezi_count = get_record_count($connection, 'is_merkezleri');
$rapor_count = 1; // Assuming there's 1 main reports page
$ayar_count = 1; // Assuming there's 1 settings page
$gelir_count = get_record_count($connection, 'gelirler');
$gider_count = get_record_count($connection, 'giderler');
$kasa_count = get_record_count($connection, 'kasa_hareketleri');
$sozlesme_count = get_record_count($connection, 'cerceve_sozlesmeler');
$personel_bordro_count = get_record_count($connection, 'personel_odemeleri');
$tekrarli_odeme_count = get_record_count($connection, 'tekrarli_odemeler');
$rehber_count = 1; // Assuming there's 1 guide page
$sss_count = 1; // Assuming there's 1 FAQ page

echo "<h1>Badge Counts Test</h1>";
echo "<ul>";
echo "<li>Müşteriler: " . $musteri_count . "</li>";
echo "<li>Personeller: " . $personel_count . "</li>";
echo "<li>Tedarikçiler: " . $tedarikci_count . "</li>";
echo "<li>Ürünler: " . $urun_count . "</li>";
echo "<li>Esanslar: " . $esans_count . "</li>";
echo "<li>Malzemeler: " . $malzeme_count . "</li>";
echo "<li>Siparişler: " . $siparis_count . "</li>";
echo "<li>Esans İş Emirleri: " . $esans_is_emri_count . "</li>";
echo "<li>Montaj İş Emirleri: " . $montaj_is_emri_count . "</li>";
echo "<li>Lokasyonlar: " . $lokasyon_count . "</li>";
echo "<li>Tanklar: " . $tank_count . "</li>";
echo "<li>İş Merkezleri: " . $is_merkezi_count . "</li>";
echo "<li>Gelir Yönetimi: " . $gelir_count . "</li>";
echo "<li>Gider Yönetimi: " . $gider_count . "</li>";
echo "<li>Kasa Yönetimi: " . $kasa_count . "</li>";
echo "<li>Sözleşmeler: " . $sozlesme_count . "</li>";
echo "</ul>";
?>
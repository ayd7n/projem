<?php
include 'config.php';

header('Content-Type: application/json; charset=utf-8');

// Sistem varlıklarının genel özetini al
$result = [];

// Ana Varlıklar
$result['musteri_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM musteriler")->fetch_assoc()['count'];
$result['urun_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM urunler")->fetch_assoc()['count'];
$result['personel_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM personeller WHERE aktif = 1")->fetch_assoc()['count'];
$result['tedarikci_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM tedarikciler WHERE aktif = 1")->fetch_assoc()['count'];

// Üretim Varlıkları
$result['esans_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM esanslar WHERE aktif = 1")->fetch_assoc()['count'];
$result['malzeme_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM malzemeler WHERE aktif = 1")->fetch_assoc()['count'];
$result['tank_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM tanklar WHERE aktif = 1")->fetch_assoc()['count'];
$result['is_merkezi_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM is_merkezleri WHERE aktif = 1")->fetch_assoc()['count'];

// İşlem Varlıkları
$result['aktif_siparis_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM siparisler WHERE durum IN ('beklemede', 'onaylandi')")->fetch_assoc()['count'];
$result['log_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM log_tablosu")->fetch_assoc()['count'];
$result['lokasyon_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM lokasyonlar WHERE aktif = 1")->fetch_assoc()['count'];
$result['stok_hareket_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM stok_hareket_kayitlari")->fetch_assoc()['count'];

// Yoldaki İş Emirleri
$result['yoldaki_montaj_is_emri'] = $connection->query("SELECT COUNT(*) as count FROM montaj_is_emirleri WHERE durum = 'uretimde'")->fetch_assoc()['count'];
$result['yoldaki_esans_is_emri'] = $connection->query("SELECT COUNT(*) as count FROM esans_is_emirleri WHERE durum = 'uretimde'")->fetch_assoc()['count'];

// Ürün Ağacı Bilgileri
$result['urun_agaci_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM urun_agaci WHERE agac_turu = 'urun'")->fetch_assoc()['count'];
$result['esans_agaci_sayisi'] = $connection->query("SELECT COUNT(*) as count FROM urun_agaci WHERE agac_turu = 'esans'")->fetch_assoc()['count'];

// Sistem Durumu
$result['sistem_durumu'] = [
    'veritabani' => 'çalışıyor',
    'mrp_modulu' => 'aktif',
    'otomatik_yedekleme' => 'etkin'
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>

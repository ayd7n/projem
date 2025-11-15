<?php
// File: includes/permissions_list.php

/**
 * Defines and returns a structured list of all controllable permissions in the system.
 * The keys are the permission strings (e.g., 'page:view:musteriler') and the values are user-friendly labels.
 *
 * @return array A structured array of all system permissions.
 */
function get_all_permissions() {
    return [
        'Genel Sayfa Erişimi' => [
            'page:view:navigation' => 'Ana Sayfa Paneli',
            'page:view:musteriler' => 'Müşteriler Sayfası',
            'page:view:personeller' => 'Personeller Sayfası',
            'page:view:tedarikciler' => 'Tedarikçiler Sayfası',
            'page:view:urunler' => 'Ürünler Sayfası',
            'page:view:esanslar' => 'Esanslar Sayfası',
            'page:view:malzemeler' => 'Malzemeler Sayfası',
            'page:view:urun_agaclari' => 'Ürün Ağaçları Sayfası',
            'page:view:musteri_siparisleri' => 'Müşteri Siparişleri Sayfası',
            'page:view:esans_is_emirleri' => 'Esans İş Emirleri Sayfası',
            'page:view:montaj_is_emirleri' => 'Montaj İş Emirleri Sayfası',
            'page:view:manuel_stok_hareket' => 'Manuel Stok Hareketi Sayfası',
            'page:view:lokasyonlar' => 'Lokasyonlar Sayfası',
            'page:view:tanklar' => 'Tanklar Sayfası',
            'page:view:is_merkezleri' => 'İş Merkezleri Sayfası',
            'page:view:raporlar' => 'Raporlar Sayfası',
            'page:view:ayarlar' => 'Ayarlar Sayfası',
            'page:view:gider_yonetimi' => 'Gider Yönetimi Sayfası',
            'page:view:cerceve_sozlesmeler' => 'Sözleşmeler Sayfası',
            'page:view:change_password' => 'Şifre Değiştirme Sayfası',
            'page:view:doviz_kurlari' => 'Döviz Kurları Sayfası',
            'page:view:yedekleme' => 'Yedekleme Sayfası',
            'page:view:excele_aktar' => 'Excel\'e Aktarma Sayfası',
        ],
        'Müşteriler' => [
            'action:musteriler:create' => 'Yeni Müşteri Ekleme',
            'action:musteriler:edit' => 'Müşteri Bilgilerini Düzenleme',
            'action:musteriler:delete' => 'Müşteri Silme',
        ],
        'Personeller' => [
            'action:personeller:create' => 'Yeni Personel Ekleme',
            'action:personeller:edit' => 'Personel Bilgilerini Düzenleme',
            'action:personeller:delete' => 'Personel Silme',
            'action:personeller:permissions' => 'Personel Yetkilerini Düzenleme',
        ],
        'Ürünler' => [
            'action:urunler:create' => 'Yeni Ürün Ekleme',
            'action:urunler:edit' => 'Ürün Bilgilerini Düzenleme',
            'action:urunler:delete' => 'Ürün Silme',
            'action:urunler:view_cost' => 'Ürün Maliyetini Görme',
        ],
        'Manuel Stok Hareketi' => [
            'action:manuel_stok_hareket:sayim_fazlasi' => 'Sayım Fazlası Ekleme',
            'action:manuel_stok_hareket:fire_sayim_eksigi' => 'Fire / Sayım Eksiği Ekleme',
            'action:manuel_stok_hareket:transfer' => 'Stok Transferi Yapma',
            'action:manuel_stok_hareket:mal_kabul' => 'Mal Kabul Yapma',
            'action:manuel_stok_hareket:delete' => 'Stok Hareketi Silme',
        ],
        'Ayarlar' => [
            'action:ayarlar:maintenance_mode' => 'Bakım Modunu Değiştirme',
            'action:ayarlar:currency' => 'Döviz Kurlarını Değiştirme',
            'action:ayarlar:backup' => 'Yedekleme İşlemleri Yapma',
            'action:ayarlar:export' => 'Excel\'e Aktarma İşlemleri Yapma',
        ],
        'Tedarikçiler' => [
            'action:tedarikciler:create' => 'Yeni Tedarikçi Ekleme',
            'action:tedarikciler:edit' => 'Tedarikçi Bilgilerini Düzenleme',
            'action:tedarikciler:delete' => 'Tedarikçi Silme',
        ],
        'Ürünler' => [
            'action:urunler:create' => 'Yeni Ürün Ekleme',
            'action:urunler:edit' => 'Ürün Bilgilerini Düzenleme',
            'action:urunler:delete' => 'Ürün Silme',
            'action:urunler:view_cost' => 'Ürün Maliyetini Görme',
        ],
        'Esanslar' => [
            'action:esanslar:create' => 'Yeni Esans Ekleme',
            'action:esanslar:edit' => 'Esans Bilgilerini Düzenleme',
            'action:esanslar:delete' => 'Esans Silme',
        ],
        'Malzemeler' => [
            'action:malzemeler:create' => 'Yeni Malzeme Ekleme',
            'action:malzemeler:edit' => 'Malzeme Bilgilerini Düzenleme',
            'action:malzemeler:delete' => 'Malzeme Silme',
        ],
        'Ürün Ağaçları' => [
            'action:urun_agaclari:create' => 'Yeni Ürün Ağacı Ekleme',
            'action:urun_agaclari:edit' => 'Ürün Ağacı Bilgilerini Düzenleme',
            'action:urun_agaclari:delete' => 'Ürün Ağacı Silme',
        ],
        'Müşteri Siparişleri' => [
            'action:musteri_siparisleri:create' => 'Yeni Müşteri Siparişi Ekleme',
            'action:musteri_siparisleri:edit' => 'Müşteri Siparişi Bilgilerini Düzenleme',
            'action:musteri_siparisleri:delete' => 'Müşteri Siparişi Silme',
            'action:musteri_siparisleri:view' => 'Müşteri Siparişi Görüntüleme',
            'action:musteri_siparisleri:approve' => 'Müşteri Siparişi Onaylama',
            'action:musteri_siparisleri:cancel' => 'Müşteri Siparişi İptal Etme',
            'action:musteri_siparisleri:complete' => 'Müşteri Siparişi Tamamlama',
            'action:musteri_siparisleri:revert_to_pending' => 'Müşteri Siparişini Beklemeye Alma',
            'action:musteri_siparisleri:revert_to_approved' => 'Tamamlanmış Siparişi Onaylanana Geri Alma',
        ],
        'Tedarikçiler' => [
            'action:tedarikciler:create' => 'Yeni Tedarikçi Ekleme',
            'action:tedarikciler:edit' => 'Tedarikçi Bilgilerini Düzenleme',
            'action:tedarikciler:delete' => 'Tedarikçi Silme',
        ],
        'Ürünler' => [
            'action:urunler:create' => 'Yeni Ürün Ekleme',
            'action:urunler:edit' => 'Ürün Bilgilerini Düzenleme',
            'action:urunler:delete' => 'Ürün Silme',
            'action:urunler:view_cost' => 'Ürün Maliyetini Görüntüleme',
        ],
        'Malzemeler' => [
            'action:malzemeler:create' => 'Yeni Malzeme Ekleme',
            'action:malzemeler:edit' => 'Malzeme Bilgilerini Düzenleme',
            'action:malzemeler:delete' => 'Malzeme Silme',
        ],
        'Esanslar' => [
            'action:esanslar:create' => 'Yeni Esans Ekleme',
            'action:esanslar:edit' => 'Esans Bilgilerini Düzenleme',
            'action:esanslar:delete' => 'Esans Silme',
        ],
        // Add other modules with specific actions here...
        // e.g., etc.
    ];
}
?>
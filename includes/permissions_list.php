<?php
// File: includes/permissions_list.php

/**
 * Defines and returns a structured list of all controllable permissions in the system.
 * The keys are the permission strings (e.g., 'page:view:musteriler') and the values are user-friendly labels.
 *
 * @return array A structured array of all system permissions.
 */
function get_all_permissions()
{
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
            'page:view:personel_bordro' => 'Personel Bordro Sayfası',
            'page:view:tekrarli_odemeler' => 'Tekrarlı Ödemeler Sayfası',
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
        'Personel Bordro' => [
            'action:personel_bordro:odeme_yap' => 'Maaş Ödemesi Yapma',
            'action:personel_bordro:avans_ver' => 'Avans Verme',
            'action:personel_bordro:gecmis_goruntule' => 'Ödeme Geçmişini Görüntüleme',
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
        'Esans İş Emirleri' => [
            'action:esans_is_emirleri:create' => 'Yeni Esans İş Emri Oluşturma',
            'action:esans_is_emirleri:edit' => 'Esans İş Emri Düzenleme',
            'action:esans_is_emirleri:delete' => 'Esans İş Emri Silme',
            'action:esans_is_emirleri:start' => 'Esans İş Emrine Başlama',
            'action:esans_is_emirleri:complete' => 'Esans İş Emrini Tamamlama',
        ],
        'Montaj İş Emirleri' => [
            'action:montaj_is_emirleri:create' => 'Yeni Montaj İş Emri Oluşturma',
            'action:montaj_is_emirleri:edit' => 'Montaj İş Emri Düzenleme',
            'action:montaj_is_emirleri:delete' => 'Montaj İş Emri Silme',
            'action:montaj_is_emirleri:start' => 'Montaj İş Emrine Başlama',
            'action:montaj_is_emirleri:complete' => 'Montaj İş Emrini Tamamlama',
        ],
        'Manuel Stok Hareketi' => [
            'action:manuel_stok_hareket:sayim_fazlasi' => 'Sayım Fazlası Ekleme',
            'action:manuel_stok_hareket:fire_sayim_eksigi' => 'Fire / Sayım Eksiği Ekleme',
            'action:manuel_stok_hareket:transfer' => 'Stok Transferi Yapma',
            'action:manuel_stok_hareket:mal_kabul' => 'Mal Kabul Yapma',
            'action:manuel_stok_hareket:delete' => 'Stok Hareketi Silme',
        ],
        'Lokasyonlar' => [
            'action:lokasyonlar:create' => 'Yeni Lokasyon Ekleme',
            'action:lokasyonlar:edit' => 'Lokasyon Düzenleme',
            'action:lokasyonlar:delete' => 'Lokasyon Silme',
        ],
        'Tanklar' => [
            'action:tanklar:create' => 'Yeni Tank Ekleme',
            'action:tanklar:edit' => 'Tank Düzenleme',
            'action:tanklar:delete' => 'Tank Silme',
        ],
        'İş Merkezleri' => [
            'action:is_merkezleri:create' => 'Yeni İş Merkezi Ekleme',
            'action:is_merkezleri:edit' => 'İş Merkezi Düzenleme',
            'action:is_merkezleri:delete' => 'İş Merkezi Silme',
        ],
        'Gider Yönetimi' => [
            'action:gider_yonetimi:create' => 'Yeni Gider Ekleme',
            'action:gider_yonetimi:edit' => 'Gider Düzenleme',
            'action:gider_yonetimi:delete' => 'Gider Silme',
            'action:gider_yonetimi:approve' => 'Gider Onaylama',
        ],
        'Çerçeve Sözleşmeler' => [
            'action:cerceve_sozlesmeler:create' => 'Yeni Sözleşme Ekleme',
            'action:cerceve_sozlesmeler:edit' => 'Sözleşme Düzenleme',
            'action:cerceve_sozlesmeler:delete' => 'Sözleşme Silme',
        ],
        'Tekrarlı Ödemeler' => [
            'action:tekrarli_odemeler:create' => 'Yeni Tekrarlı Ödeme Tanımlama',
            'action:tekrarli_odemeler:edit' => 'Tekrarlı Ödeme Düzenleme',
            'action:tekrarli_odemeler:delete' => 'Tekrarlı Ödeme Silme',
            'action:tekrarli_odemeler:odeme_yap' => 'Ödeme Yapma',
            'action:tekrarli_odemeler:gecmis_goruntule' => 'Ödeme Geçmişini Görüntüleme',
        ],
        'Raporlar' => [
            'page:view:gider_raporlari' => 'Gider Raporları',
            'page:view:kritik_stok_raporlari' => 'Kritik Stok Seviyeleri',
            'page:view:urun_agaci_analiz' => 'Eksik Bileşen Raporu',
            'page:view:bileseni_eksik_esanslar' => 'Ağaçları Olmayan Esanslar',
            'page:view:stok_hareket_raporu' => 'Stok Hareket Analizi',
            'page:view:montaj_raporu' => 'Montaj Raporu',
            'page:view:en_cok_satan_urunler' => 'En Çok Satan Ürünler',
            'page:view:musteri_satis_raporu' => 'Müşteri Satış Raporu',
            'page:view:tedarikci_odeme_raporu' => 'Tedarikçi Ödeme Raporu',
            'page:view:tedarikciye_yapilacak_odemeler_raporu' => 'Tedarikçiye Yapılacak Ödemeler',
            'page:view:log_raporlari' => 'Sistem İşlem Logları',
            'page:view:sayisal_ozet' => 'Sayısal Özet',
            'page:view:isletme_maliyeti_raporu' => 'İşletme Maliyeti Analizi',
        ],
        'Ayarlar' => [
            'action:ayarlar:maintenance_mode' => 'Bakım Modunu Değiştirme',
            'action:ayarlar:currency' => 'Döviz Kurlarını Değiştirme',
            'action:ayarlar:backup' => 'Yedekleme İşlemleri Yapma',
            'action:ayarlar:export' => 'Excel\'e Aktarma İşlemleri Yapma',
            'action:ayarlar:telegram' => 'Telegram Bildirim Ayarları',
        ],
    ];
}
?>
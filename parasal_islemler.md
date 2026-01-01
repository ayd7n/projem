# Parasal İşlemler Özeti

Bu belge, Parfum ERP uygulamasında yer alan tüm parasal işlemler, ödeme süreçleri ve finansal yönetimle ilgili sayfaları ve veritabanı yapılarını özetlemektedir.

## 1. Genel Parasal İşlemler

Parfum ERP uygulamasında parasal işlemler aşağıdaki ana kategorilerde toplanmıştır:

- Sipariş takibi ve ödeme yönetimi
- Gelir ve gider yönetimi
- Çerçeve sözleşmeler ve tedarikçi ödemeleri
- Personel maaş ve avans ödemeleri
- Ürün ve esans maliyet hesaplamaları
- Taksit planlama ve ödeme takibi
- Kasa işlemleri ve şirket kasası yönetimi
- Satınalma işlemleri ve tedarikçi ödemeleri
- Stok hareketleri ve maliyet hesaplamaları

## 2. Sipariş ve Ödeme Yönetimi

### 2.1. Ana Sipariş Sayfaları
- `customer_orders.php` - Müşteri siparişlerini görüntüleme ve yönetme
  - Kullanıcı arayüzü: Müşterilerin geçmiş siparişlerini görüntülemesi
  - API: `api_islemleri/musteri_siparis_islemler.php` (action=get_orders)
  
- `navigation.php` - Ana navigasyon sayfası
  - Kullanıcı arayüzü: "İşlemler" modülünden "Sipariş Oluştur" seçeneği
  - API: `api_islemleri/admin_order_operations.php` (sipariş oluşturma)

### 2.2. Siparişle İlgili Veritabanı Tabloları
- `siparisler` - Siparişlerin genel bilgilerini tutar
  - `odeme_durumu` - Ödeme durumu (bekliyor, ödendi, kısmi ödendi)
  - `odenen_tutar` - Ödenen tutar
  - `para_birimi` - Siparişin para birimi
- `siparis_kalemleri` - Sipariş kalemlerini tutar
  - `birim_fiyat` - Birim fiyat
  - `toplam_tutar` - Toplam tutar
  - `para_birimi` - Kalemin para birimi

### 2.3. Ödeme ve Tahsilat İşlemleri
- `gelir_yonetimi.php` - Gelir ve ödeme yönetimi arayüzü
  - Kullanıcı arayüzü: Tahsilat ekleme, listeleme, taksit planlama
  - API: `api_islemleri/gelir_yonetimi_islemler.php` (tahsilat ekleme, güncelleme, silme)
- `taksit_planlari` ve `taksit_detaylari` tabloları - Taksitli ödeme planları
- `taksit_siparis_baglantisi` - Taksit planları ile siparişlerin bağlantısı

## 3. Gelir ve Gider Yönetimi

### 3.1. Gelir Yönetimi
- `gelir_yonetimi.php` - Gelir ve ödeme yönetimi
  - Kullanıcı arayüzü: Tahsilat ekleme, listeleme, taksit planlama
  - API: `api_islemleri/gelir_yonetimi_islemler.php` (gelir ekleme, güncelleme, silme)
- `gelir_yonetimi` tablosu:
  - `tutar` - Gelir tutarı
  - `para_birimi` - Para birimi
  - `kategori` - Gelir kategorisi
  - `odeme_tipi` - Ödeme tipi (Nakit, Kredi Kartı, Havale vb.)
  - `musteri_id` ve `musteri_adi` - Müşteri bilgileri
  - `siparis_id` - Bağlı olduğu sipariş

### 3.2. Gider Yönetimi
- `gider_yonetimi.php` - Gider yönetimi arayüzü
  - Kullanıcı arayüzü: Gider ekleme, listeleme, düzenleme
  - API: `api_islemleri/gider_yonetimi_islemler.php` (gider ekleme, güncelleme, silme)
- `gider_yonetimi` tablosu:
  - `tutar` - Gider tutarı
  - `kategori` - Gider kategorisi
  - `fatura_no` - Fatura numarası
  - `odeme_tipi` - Ödeme tipi
  - `odeme_yapilan_firma` - Ödeme yapılan firma

## 4. Çerçeve Sözleşmeler ve Tedarikçi Ödemeleri

### 4.1. Sözleşme Yönetimi
- `cerceve_sozlesmeler.php` - Çerçeve sözleşmeleri yönetimi
  - Kullanıcı arayüzü: Yeni sözleşme ekleme, ödeme yapma, sözleşme detayları
  - API: `api_islemleri/cerceve_sozlesmeler_islemler.php` (sözleşme işlemleri)
  - Ödeme yap butonu: "Ara Ödeme" veya "Ön Ödeme" için `gider_yonetimi` tablosuna otomatik kayıt oluşturur

### 4.2. Sözleşmelerle İlgili Veritabanı Yapıları
- `cerceve_sozlesmeler` tablosu:
  - `birim_fiyat` - Birim fiyat
  - `para_birimi` - Para birimi
  - `limit_miktar` - Sözleşme limiti
  - `toplu_odenen_miktar` - Ödenen miktar
- `cerceve_sozlesmeler_gecerlilik` view'ı - Sözleşmelerin geçerlilik durumunu ve ödeme durumunu gösterir

## 5. Personel Maaş ve Avans Ödemeleri

### 5.1. Personel Yönetimi
- `personeller.php` - Personel bilgileri ve maaş yönetimi
  - Kullanıcı arayüzü: Personel ekleme, düzenleme, maaş bilgileri
  - API: `api_islemleri/personeller_islemler.php` (personel işlemleri)
  - Maaş bilgileri: `aylik_brut_ucret` alanı

### 5.2. Maaş ve Avans İşlemleri
- `personel_yetki.php` - Personel yetki yönetimi
  - Kullanıcı arayüzü: Personel yetkileri düzenleme
  - API: `api_islemleri/personeller_islemler.php` (yetki işlemleri)
  
- Personel maaş ve avans ödemeleri doğrudan `gider_yonetimi` sayfasından yapılır:
  - Kategori: "Personel Gideri"
  - Açıklama: Personel adı ve ödeme türü (maaş/avans)
  - `personel_maas_odemeleri` ve `personel_avanslar` tabloları personel ödemelerini takip eder

## 6. Ürün ve Esans Maliyet Hesaplamaları

### 6.1. Ürün ve Esans Maliyetleri
- `urunler` tablosu:
  - `satis_fiyati` - Satış fiyatı
  - `satis_fiyati_para_birimi` - Satış fiyatının para birimi
  - `alis_fiyati` - Alış fiyatı
  - `alis_fiyati_para_birimi` - Alış fiyatının para birimi
  - `son_maliyet` - Son hesaplanan maliyet
- `v_urun_maliyetleri` view'ı - Ürün maliyetlerini hesaplar
- `v_esans_maliyetleri` view'ı - Esans maliyetlerini hesaplar
- `maliyet_raporu.php` - Malzeme maliyet raporu
- `maliyet_hesaplama.php` - Ürün maliyet hesaplama servisi

## 7. Kasa İşlemleri

### 7.1. Kasa Yönetimi
- `sirket_kasasi.php` - Şirket kasası ve tüm parasal hareketler
  - Kullanıcı arayüzü: Gelir, gider ve kasa işlemleri listesi
  - API: `api_islemleri/kasa_islemleri.php` (varsayılan olarak tanımladım)
- `kasa_islemleri` tablosu:
  - `islem_tipi` - İşlem tipi (giriş/çıkış)
  - `tutar` - İşlem tutarı
  - `para_birimi` - Para birimi
- `sirket_kasasi` tablosu:
  - `para_birimi` - Para birimi
  - `bakiye` - Bakiye

## 8. Döviz Kuru ve Para Birimi Yönetimi

### 8.1. Ayarlar ve Döviz Kurları
- `ayarlar` tablosu:
  - `dolar_kuru` - Dolar kuru
  - `euro_kuru` - Euro kuru
- `doviz_kurlari.php` - Döviz kuru yönetimi

## 9. Müşteri ve Tedarikçi Yönetimi

### 9.1. Müşteri Yönetimi
- `musteriler.php` - Müşteri bilgileri ve ödeme durumu
  - Kullanıcı arayüzü: Müşteri ekleme, düzenleme, ödeme durumu takibi
  - API: `api_islemleri/musteri_islemler.php` (varsayılan olarak tanımladım)
  - Ödeme durumu: `kalan_bakiye` alanı ile takip edilir
- `musteri_karti.php` - Müşteri kartı ve ödeme detayları
  - Kullanıcı arayüzü: Müşteriye ait tüm ödeme ve sipariş detayları
  - Taksit planları: Müşteriye ait aktif taksit planları gösterilir

### 9.2. Tedarikçi Yönetimi
- `tedarikciler.php` - Tedarikçi bilgileri ve ödeme durumu
  - Kullanıcı arayüzü: Tedarikçi ekleme, düzenleme, ödeme durumu takibi
  - API: `api_islemleri/tedarikci_islemler.php` (varsayılan olarak tanımladım)
  - Ödeme durumu: `balance` alanı ile takip edilir
- `tedarikci_karti.php` - Tedarikçi kartı ve ödeme detayları
  - Kullanıcı arayüzü: Tedarikçiye ait tüm ödeme ve mal kabul detayları
  - Mal kabul kayıtları: Tedarikçiden alınan malzeme detayları

## 10. Satınalma ve Tedarikçi Ödemeleri

### 10.1. Satınalma Siparişleri
- `satinalma_siparisler.php` - Satınalma siparişleri
  - Kullanıcı arayüzü: Tedarikçilere verilen satınalma siparişlerini yönetme
  - API: `api_islemleri/satinalma_siparis_islemler.php` (varsayılan olarak tanımladım)
  - Toplam tutar: `toplam_tutar` alanı ile hesaplanır
  - Para birimi: `para_birimi` alanı ile belirlenir

### 10.2. Satınalma ile İlgili Veritabanı Tabloları
- `satinalma_siparisler` - Satınalma siparişlerini tutar
  - `toplam_tutar` - Siparişin toplam tutarı
  - `para_birimi` - Siparişin para birimi
  - `durum` - Siparişin durumu
- `satinalma_siparis_kalemleri` - Satınalma siparişi kalemlerini tutar
  - `birim_fiyat` - Kalemin birim fiyatı
  - `toplam_tutar` - Kalemin toplam tutarı

## 11. Stok Hareketleri ve Maliyet Hesaplamaları

### 11.1. Stok Hareketleri
- `manuel_stok_hareket.php` - Manuel stok hareketleri
  - Kullanıcı arayüzü: Sayım fazlası, fire/sayım eksigi, transfer, mal kabul gibi işlemler
  - API: `api_islemleri/stok_hareket_islemler.php` (varsayılan olarak tanımladım)
  - Fire kayıtları: "Fire / Sayım Eksigi" işlemi ile maliyet olarak `gider_yonetimi` tablosuna aktarılır

### 11.2. Stok Hareketleri ile İlgili Veritabanı Tabloları
- `stok_hareket_kayitlari` - Stok hareketlerini tutar
  - `miktar` - Hareket miktarı
  - `birim_fiyat` - Hareketin birim fiyatı
  - `tutar` - Hareketin toplam tutarı
- `stok_hareketleri_sozlesmeler` - Stok hareketlerinin sözleşme bazlı detaylarını tutar
  - `birim_fiyat` - Sözleşmeden gelen birim fiyat
  - `toplam_maliyet` - Hareketin toplam maliyeti

## 12. Ürün ve Hizmet Kartları

### 12.1. Ürün Kartları
- `urunler.php` - Ürün listesi ve maliyet bilgileri
  - Kullanıcı arayüzü: Ürün ekleme, düzenleme, maliyet ve satış fiyatı takibi
  - API: `api_islemleri/urun_islemler.php` (varsayılan olarak tanımladım)
  - Satış fiyatı: `satis_fiyati` ve `satis_fiyati_para_birimi` alanları
  - Alış fiyatı: `alis_fiyati` ve `alis_fiyati_para_birimi` alanları
- `urun_karti.php` - Ürün kartı ve detaylı bilgiler
  - Kullanıcı arayüzü: Ürünün detaylı bilgileri, maliyet hesaplamaları ve sipariş durumu

## 13. Ödeme Yöntemleri ve Türleri

Uygulamada kullanılan ödeme türleri:
- Nakit
- Kredi Kartı
- Banka Havalesi
- EFT
- Çek
- Senet
- Taksitli ödeme

## 14. Raporlama ve Analiz

### 14.1. Finansal Raporlar
- `en_cok_satan_urunler.php` - En çok satan ürünler raporu
- `gelir_yonetimi.php` - Gelir gider durumu raporu
- `sirket_kasasi.php` - Kasa durumu raporu
- `taksit_takip.php` - Taksit takip raporu
- `maliyet_raporu.php` - Malzeme maliyet raporu

## 15. Günlük İşlemler ve Loglama

- `log_tablosu` - Tüm parasal işlemlerle ilgili loglar tutulur
- `kasa_islemleri` - Kasa hareketleri loglanır

## 16. Entegrasyonlar

- Telegram bildirimleri: Sipariş oluşturulduğunda veya ödeme alındığında
- Döviz kuru güncellemeleri: Harici kaynaklardan otomatik güncelleme

## 17. Finansal Sistem Eleştirel Değerlendirmesi

Aşağıda, sistemde tespit edilen finansal yapı ve süreçlerle ilgili sorunlar yer almaktadır:

### 17.1. Maliyet Hesaplama Sorunları
- Ürün maliyetleri sadece son mal kabul fiyatına göre hesaplanıyor. Bu, esans ve malzeme fiyatlarında dalgalanmalar olduğu zaman gerçek maliyeti yansıtmayabilir.
- Ağırlıklı ortalama maliyet yöntemi kullanılmıyor, bu da maliyet hesaplamalarının gerçek maliyeti yansıtmamasına neden olabilir.

### 17.2. Para Birimi Yönetimi Eksiklikleri
- Farklı para birimlerinde yapılan işlemler TL'ye çevrilmeden kaydediliyor, bu da döviz kuru değişikliklerinde maliyet ve gelirlerin doğru hesaplanmasını engelleyebilir.
- Döviz kuru güncellemeleri manuel yapıldığı için gerçek zamanlı olmayabilir.

### 17.3. Tedarikçi Ödemeleri ve Gider Takibi
- Çerçeve sözleşmelerde yapılan ödemeler "Malzeme Gideri" olarak genel gider tablosuna aktarılıyor ama bu ödemeler stok girişi ile ilişkilendirilmediği için maliyet hesaplamalarında eksiklik olabilir.
- Satınalma siparişlerinde yapılan ödemeler doğrudan gider olarak kaydedilmiyor, sadece stok girişiyle takip ediliyor.

### 17.4. Fire ve Sayım Eksiklikleri
- Fire kayıtları maliyet olarak giderlere aktarılıyor ama bu kayıpların neden kaynaklandığı analiz edilmiyor, bu da maliyetleri artıran faktörlerin tespiti zorlaşıyor.

### 17.5. Taksit Planlama ve Tahsilat Takibi
- Taksit planları ile siparişler arasında bağlantı var ama bu taksitlerin banka entegrasyonu yok, bu da tahsilat risklerini artırabilir.
- Gecikmiş taksitler için otomatik faiz veya gecikme cezası uygulaması yok.

### 17.6. Kasa ve Nakit Yönetimi
- Kasa hareketleri ile banka hareketleri arasında entegrasyon yok, bu da nakit akışı analizini zorlaştırabilir.
- Kasa bakiyesi ile gerçek banka bakiyesi arasında fark oluştuğunda izleme zor olabilir.

### 17.7. Personel Maaş ve Avans Yönetimi
- Personel avansları doğrudan maaşlardan kesiliyor ama bu kesintilerin takibi ayrı bir sistemde değil, bu da maliyet hesaplamalarında eksikliklere neden olabilir.

### 17.8. Raporlama ve Analiz Eksiklikleri
- Kar/zarar durumu ürün bazında değil, sadece genel seviyede takip ediliyor.
- İşletme maliyetleri kategorilere ayrılmadan toplu şekilde izleniyor, bu da maliyet analizini zorlaştırıyor.

### 17.9. Fatura ve Belgelendirme
- Sistemde fatura kesme ve takip modülü eksik, bu da vergi uyumunda sorunlara yol açabilir.

### 17.10. Güvenlik ve Yetki Yönetimi
- Finansal verilere erişim için ayrılmış kullanıcı rolleri yeterince detaylı değil, bu da iç denetim açısından risk oluşturabilir.

Bu belge, Parfum ERP uygulamasındaki tüm parasal işlemler, ödeme süreçleri ve finansal yönetimle ilgili yapıları kapsamaktadır. Sistemde yapılacak finansal değişiklikler veya yeni özellikler eklenirken bu belgede yer alan yapılar dikkate alınmalıdır.

## 18. Şirketin Parasal Akışı ve Borçlanma Süreçleri

### 18.1. Şirkete Para Girişi (Gelir Kaynakları)
Aşağıda, bu ERP sistemini kullanan şirketin para giriş kaynakları ve bu paraların hangi olaylarla oluştuğu detaylandırılmaktadır:

- **Müşteri Siparişleri ve Tahsilatlar**
  - Müşterilerden gelen siparişlerle satış geliri elde edilir
  - Siparişlerde `odeme_durumu` alanı ile ödeme durumu takip edilir
  - `gelir_yonetimi.php` üzerinden tahsilat yapılır ve `gelir_yonetimi` tablosuna kayıt eklenir
  - Taksitli satışlarda `taksit_detaylari` tablosu üzerinden zamanla tahsilat yapılır
  - Ödeme alındığında `siparisler` tablosundaki `odenen_tutar` ve `odeme_durumu` güncellenir

- **Diğer Gelir Kaynakları**
  - `gelir_yonetimi.php` üzerinden "Diğer Gelir" kategorisi ile başka gelirler de eklenebilir
  - Kasa girişleri `kasa_islemleri` tablosuna `islem_tipi: giriş` olarak kaydedilir

### 18.2. Şirketten Para Çıkışı (Giderler)
Aşağıda, şirketin para çıkış kaynakları ve bu ödemelerin hangi olaylarla oluştuğu detaylandırılmaktadır:

- **Tedarikçi Ödemeleri**
  - Çerçeve sözleşmeler kapsamında yapılan ödemeler `cerceve_sozlesmeler.php` üzerinden yapılır
  - Bu ödemeler `gider_yonetimi` tablosuna "Malzeme Gideri" kategorisiyle eklenir
  - Satınalma siparişlerinde stok girişi yapıldıktan sonra ödeme yapılır

- **Personel Maaş ve Avans Ödemeleri**
  - Personel maaş ödemeleri `gider_yonetimi.php` üzerinden "Personel Gideri" kategorisiyle yapılır
  - Personel avans ödemeleri de aynı şekilde "Personel Gideri" kategorisiyle yapılır
  - `personel_avanslar` ve `personel_maas_odemeleri` tabloları ile takip edilir

- **Diğer Giderler**
  - Kira, elektrik, su, internet gibi sabit giderler "İşletme Gideri" kategorisiyle `gider_yonetimi` tablosuna eklenir
  - Fire ve sayım eksiklikleri "Fire Gideri" kategorisiyle gider olarak kaydedilir
  - Kasa çıkışları `kasa_islemleri` tablosuna `islem_tipi: çıkış` olarak kaydedilir

### 18.3. Borçlanma Süreçleri
Aşağıda, şirketin borçlandığı durumlar ve borçlanma nedenleri detaylandırılmaktadır:

- **Tedarikçiye Borçlanma**
  - Çerçeve sözleşme limiti aşıldığında veya ödeme yapılmadığında tedarikçiye borçlanılır
  - `cerceve_sozlesmeler` tablosunda `toplu_odenen_miktar` ile `limit_miktar` karşılaştırılarak borç durumu anlaşılır
  - `tedarikciler.php` üzerinden tedarikçi bakiyesi takip edilir, negatif bakiye borç anlamına gelir

- **Müşteriden Alacak**
  - Sipariş verilip ödeme alınmadığında müşteriye alacak durumu oluşur
  - `musteriler.php` üzerinden müşteri bakiyesi takip edilir, pozitif bakiye alacak anlamına gelir
  - `musteri_karti.php` üzerinden müşteriye ait taksit planları ve tahsilat takibi yapılır

- **Personel Avans Borcu**
  - Personellere verilen avanslar `personel_avanslar` tablosunda takip edilir
  - Bu avanslar ilgili ayın maaşından kesilerek tahsil edilir

### 18.4. Borç Ödeme Süreçleri
Aşağıda, şirketin borçlarını nasıl ödediği ve bu ödemelerin hangi olaylarla gerçekleştiği detaylandırılmaktadır:

- **Tedarikçi Borç Ödemesi**
  - `gider_yonetimi.php` üzerinden "Tedarikçi Ödemesi" kategorisiyle ödeme yapılır
  - Ödeme yapıldığında `tedarikciler` tablosundaki bakiye güncellenir
  - Çerçeve sözleşme ödemeleri `cerceve_sozlesmeler.php` üzerinden yapılır

- **Müşteri Tahsilatı**
  - `gelir_yonetimi.php` üzerinden müşteri ödemeleri alınır
  - Ödeme alındığında `musteriler` tablosundaki bakiye güncellenir
  - Taksitli ödemelerde `taksit_detaylari` tablosundaki ödeme durumu güncellenir

- **Personel Maaş ve Avans Tahsilatı**
  - Personel avansları doğrudan maaşlardan otomatik olarak kesilir
  - Kesintiler `personel_avanslar` tablosu ile takip edilir
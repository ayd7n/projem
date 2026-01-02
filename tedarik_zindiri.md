Prompt:

Aşağıdaki PHP dosyaları incelenerek kokpit.php sayfasını bir tedarik zinciri kontrol paneline dönüştürmeni istiyorum:

tek sayfa sekme olmayacak tek cümlelik özet olacak.

**İNCELENECEK PHP DOSYALARI:**

- `kokpit.php`: Mevcut kontrol paneli sayfası
- `ne_uretsem.php`: Üretim kapasitesi ve önceliklendirme sayfası
- `esans_is_emirleri.php`: Esans üretim iş emirleri sayfası
- `montaj_is_emirleri.php`: Ürün montaj iş emirleri sayfası
- `cerceve_sozlesmeler.php`: Çerçeve sözleşmeleri sayfası
- `satinalma_siparisler.php`: Satınalma siparişleri sayfası
- `api_islemleri/kokpit_islemler.php`: Kokpit API işlemleri
- `api_islemleri/esans_is_emirleri_islemler.php`: Esans iş emri API işlemleri
- `api_islemleri/montaj_is_emirleri_islemler.php`: Montaj iş emri API işlemleri
- `api_islemleri/cerceve_sozlesmeler_islemler.php`: Çerçeve sözleşme API işlemleri
- `api_islemleri/satinalma_siparisler_islemler.php`: Satınalma siparişi API işlemleri
- `assets/js/kokpit.js`: Kokpit JavaScript uygulaması
- `assets/js/esans_is_emirleri.js`: Esans iş emri JavaScript uygulaması
- `assets/js/montaj_is_emirleri.js`: Montaj iş emri JavaScript uygulaması

Bu sayfa, kullanıcıya ürün üretimi için gerekli tüm bilgileri tek bir yerde sunmalı ve "Ne yapmam gerekiyor?" sorusunun cevabını net şekilde vermelidir.

Sayfa şu mantıkla çalışmalı:

**VERİ TABANI YAPISI VE TABLOLAR:**
Sistem aşağıdaki MySQL veritabanı tablolarını (parfum_erp) kullanarak çalışmalı:

- `urunler`: Ürün bilgileri, stok miktarları, kritik seviye tanımları
- `urun_agaci`: Ürün bileşenleri (kutu, takm, etiket, paket, jelatin, esans) ve miktarları
- `esanslar`: Esans bilgileri ve stok miktarları
- `esans_agaci`: Esans reçeteleri (esansların kendi bileşenleri - aroma, alkol, vb.)
- `esans_is_emirleri`: Esans üretim iş emirleri (durum: olusturuldu, uretimde, tamamlandi, iptal)
- `esans_is_emri_malzeme_listesi`: Esans üretim iş emri bileşenleri (is_emri_numarasi, malzeme_kodu, miktar, birim)
- `malzemeler`: Malzeme bilgileri ve stok miktarları
- `cerceve_sozlesmeler`: Çerçeve sözleşmeleri ve tedarikçi bilgileri
- `satinalma_siparisler`: Satınalma siparişleri
- `satinalma_siparis_kalemleri`: Satınalma siparişi kalemleri
- `montaj_is_emirleri`: Aktif üretim emirleri
- `montaj_is_emri_malzeme_listesi`: Montaj iş emri bileşenleri (is_emri_numarasi, malzeme_kodu, miktar, birim)
- `siparisler`: Müşteri siparişleri
- `siparis_kalemleri`: Müşteri siparişi kalemleri

**İŞLEVSEL AKIŞ:**

1. **Bileşen Eksiklik Kontrolü (urun_agaci tablosu):** Sistem her ürün için urun_agaci tablosundaki bileşen tanımlarını kontrol etmeli. Eksik bileşen varsa kullanıcıya şu şekilde bilgi verilmeli: "Şu ürünler için bileşen eksik: [ürün adı] - [bileşen türü] (kutu, takm, etiket, paket, jelatin, esans)". Bu kontrol urun_agaci.urun_kodu ile urunler.urun_kodu eşleşmesi üzerinden yapılmalı.

2. **Esans Reçete ve İş Emri Kontrolü (esans_agaci ve esans_is_emirleri tablolaru):** Esansların kendi üretim reçeteleri esans_agaci tablosunda tanımlı olmalı. Ayrıca, esans üretimi için esans_is_emirleri tablosunda iş emirleri olmalı. Eğer bir esansın reçetesi eksikse şu mesaj verilmeli: "Şu esanslar için reçete eksik: [esans adı]". Eğer bir esans için üretim planlanmış ama iş emri oluşturulmamışsa: "Şu esanslar için üretim iş emri oluşturulmalı: [esans adı]". Bu kontrol esans_agaci.urun_kodu ile esanslar.esans_kodu eşleşmesi ve esans_is_emirleri.esans_kodu ile esanslar.esans_kodu eşleşmesi üzerinden yapılmalı.

3. **Stok ve Kritik Seviye Kontrolü (urunler ve esanslar tablolaru):** Sistem urunler ve esanslar tablolarındaki stok_miktari ile kritik_stok_seviyesi alanlarını karşılaştırarak şu mesajı vermelidir: "Şu ürünler kritik seviyenin altında: [ürün adı] (stok: X, kritik: Y)". Benzer şekilde esanslar için de: "Şu esanslar kritik seviyenin altında: [esans adı]".

4. **Çerçeve Sözleşme Kontrolü (cerceve_sozlesmeler tablosu):** Sistem cerceve_sozlesmeler tablosunda her bileşen (malzeme_kodu) için sözleşme olup olmadığını kontrol etmeli. Eğer bir bileşen için çerçeve sözleşmesi yoksa şu mesaj verilmeli: "Şu bileşenler için çerçeve sözleşme eksik: [bileşen adı]". Bu kontrol cerceve_sozlesmeler.malzeme_kodu ile urun_agaci.bilesen_kodu eşleşmesi üzerinden yapılmalı.

5. **Üretim Kapasitesi Hesabı (urun_agaci, esans_agaci ve stok verileri):** Sistem ne_uretsem.php sayfasındaki mantıkla şu sorguları çalıştırmalı:

   - Her ürün için urun_agaci'deki bileşen miktarlarını ve mevcut stok miktarlarını karşılaştır
   - Esanslar için esans_agaci'deki reçete verilerini ve mevcut esans stok miktarlarını karşılaştır
   - Aktif üretim emirlerini (montaj_is_emirleri ve esans_is_emirleri) dikkate al - bu emirlerde kullanılan malzemeleri mevcut stoktan düş
   - Esans üretim iş emirlerinde kullanılan hammaddelerin (esans_is_emri_malzeme_listesi) diğer üretimler için mevcut malzeme miktarlarını etkileyip etkilemediğini kontrol et
   - Montaj iş emirlerinde kullanılan bileşenlerin (montaj_is_emri_malzeme_listesi) diğer üretimler için mevcut malzeme miktarlarını etkileyip etkilemediğini kontrol et
   - En az stok miktarına göre üretilebilecek maksimum ürün sayısını hesapla
   - Kullanıcıya şu şekilde bilgi ver: "Şu ürünler şu miktarlarda üretilebilir: [ürün adı] - [miktar adet]", "Şu esanslar şu miktarlarda üretilebilir: [esans adı] - [miktar adet]"
   - Bu hesaplama sırasında esans_agaci'deki esans reçeteleri, esans_is_emirleri durumları ve mevcut malzeme stokları da dikkate alınmalı

6. **Aktif Üretim Emirleri Takibi (montaj_is_emirleri ve esans_is_emirleri tabloları):** Sistem montaj_is_emirleri tablosundaki durumu 'baslatildi' veya 'uretimde' olan kayıtları ve esans_is_emirleri tablosundaki durumu 'uretimde' olan kayıtları kontrol etmeli ve kullanıcıya şu bilgiyi vermelidir: "Şu ürünler için üretim emri devam ediyor: [ürün adı] - [planlanan_miktar] adet", "Şu esanslar için üretim emri devam ediyor: [esans adı] - [planlanan_miktar] adet". Bu veri montaj_is_emirleri.urun_kodu ile urunler.urun_kodu ve esans_is_emirleri.esans_kodu ile esanslar.esans_kodu eşleşmeleri üzerinden alınmalı. Ayrıca, aktif esans üretim emirlerinde kullanılan hammaddelerin (esans_is_emri_malzeme_listesi) ve montaj iş emirlerinde kullanılan bileşenlerin (montaj_is_emri_malzeme_listesi) diğer üretimler için mevcut malzeme miktarlarını etkileyip etkilemediği de kontrol edilmeli. İş emri başlatıldığında ilgili malzemelerin stoktan düşmüş olması dikkate alınmalı.

7. **Müşteri Siparişleri Takibi (siparisler ve siparis_kalemleri tabloları):** Sistem siparis_kalemleri tablosundaki miktarları ve siparisler tablosundaki durumu 'onaylandi' olan kayıtları toplayarak kullanıcıya şu bilgiyi vermelidir: "Şu müşteri siparişleri için ürün eksik: [ürün adı] - [toplam miktar] adet". Bu hesaplama siparis_kalemleri.urun_kodu ile urunler.urun_kodu eşleşmesi üzerinden yapılmalı.

8. **Sipariş ve Malzeme Takibi (satinalma_siparisler ve satinalma_siparis_kalemleri tabloları):** Eğer bir ürünün üretimi veya esans üretimi için gerekli malzeme stokta yoksa, sistem satinalma_siparisler ve satinalma_siparis_kalemleri tablolarını kontrol etmeli:

   - Malzeme için sipariş var mı? (satinalma_siparis_kalemleri.malzeme_kodu ile urun_agaci.bilesen_kodu veya esans_agaci.bilesen_kodu eşleşmesi)
   - Toplam sipariş miktarı nedir? (satinalma_siparis_kalemleri.miktar)
   - Teslim edilen miktar nedir? (satinalma_siparis_kalemleri.teslim_edilen_miktar)
   - Beklenen miktar = miktar - teslim_edilen_miktar
   - Aktif esans üretim iş emirlerinde (esans_is_emri_malzeme_listesi) bu malzeme için rezerve edilmiş miktar var mı? Bu miktarı da dikkate al
   - Aktif montaj iş emirlerinde (montaj_is_emri_malzeme_listesi) bu malzeme için rezerve edilmiş miktar var mı? Bu miktarı da dikkate al

   Eğer beklenen miktar üretim için yeterli değilse: "Dikkat! [malzeme adı] için [sipariş_no] nolu sipariş yeterli değil, [fark] adet eksik. Acil eylem gerekiyor."

   Eğer beklenen miktar yeterliyse: "[malzeme adı] için [sipariş_no] nolu siparişten [bekleyen_miktar] adet teslimi bekleniyor. Malzeme geldikten sonra üretim yapılabilir."

9. **Esans Sipariş ve Üretim Takibi:** Esanslar için satinalma_siparis_kalemleri ve esans_is_emirleri tabloları kontrol edilmeli. Eğer bir esans eksikse ve sipariş verilmişse, teslim durumu takip edilmeli: "[esans_adı] için [sipariş_no] nolu siparişten [bekleyen_miktar] adet teslimi bekleniyor." Ayrıca, esans üretimi için oluşturulan iş emirlerinin durumu da takip edilmeli: "[esans_adı] için [is_emri_no] nolu iş emri [durum] durumunda, [planlanan_miktar] adet üretim planlanmış." Bu esans üretimi için gerekli hammaddelerin mevcut stoklara ve diğer üretim planlarına etkisi de hesaplanmalı. Esans üretim iş emirleri başlatıldığında, kullanılan bileşenlerin (esans_is_emri_malzeme_listesi) stoktan düşmesi dikkate alınmalı. Esans üretimi tamamlandığında, üretilen esansın stoğa eklenmesi sağlanmalı.

10. **Bileşen Hesaplama ve Stok Takibi:** Ürün ve esans üretimi için gerekli bileşenlerin miktarları urun_agaci ve esans_agaci tablolarındaki reçetelere göre hesaplanmalı. Her bileşen için gereken miktar = (ürün/esans reçetesindeki miktar) \* (üretilecek miktar) formülü kullanılmalı. Bu hesaplamalar sırasında mevcut stok miktarları, aktif iş emirlerinde rezerve edilen miktarlar ve beklenen siparişler dikkate alınmalı.

11. **Kaynak Çakışması ve Talep Yönetimi:** Ürün ve esans üretimi için ortak kullanılan hammaddeler (örneğin alkol, aroma maddeleri) için talep çakışmaları kontrol edilmeli. Sistem şu analizi yapmalı: "Şu malzeme hem ürün hem esans üretimi için talep görüyor: [malzeme_adı] - ürün üretimi için [miktar1], esans üretimi için [miktar2], toplam talep [toplam] > mevcut stok [stok_miktari]". Bu durumda kullanıcıya şu bilgi verilmeli: "Dikkat! [malzeme_adı] için talep çakışması var: [ürün_adı] ve [esans_adı] üretimi için aynı malzeme gerekli."

12. **Aksiyon Önerileri:** Her durumda kullanıcıya net aksiyon önerileri sunulmalı:

- "Yeni çerçeve sözleşme oluştur: [bileşen_adı]"
- "Eksik bileşen tanımla: [ürün_adı] - [bileşen_türü]"
- "Malzeme siparişi ver: [malzeme_adı]"
- "Esans reçetesi oluştur: [esans_adı]"
- "Üretim emri başlat: [ürün_adı]"
- "Esans üretim emri oluştur: [esans_adı]"
- "Tedarikçiyle iletişime geç: [tedarikçi_adı]"
- "Kaynak çakışması için üretim planını gözden geçir: [malzeme_adı] hem ürün hem esans üretiminde gerekli"
- "Üretim emri tamamla: [ürün_adı] - [miktar] adet üretildi"
- "Esans üretim emri tamamla: [esans_adı] - [miktar] adet üretildi"

13. **Görsel ve Renk Kodlaması:** Kullanıcı dostu bir arayüz için durumlara göre renk kodlaması yapılmalı:

- Kırmızı: Acil aksiyon gerektiren durumlar (kritik eksiklik, sipariş geç kalmış)
- Sarı: Uyarı durumları (düşük stok, bekleyen sipariş)
- Yeşil: Güvenli durumlar (yeterli stok ve sözleşme)
- Mavi: Bilgilendirme amaçlı durumlar (planlanan üretim)

14. **Stok Hareketleri ve Etkileri:** Sistem, üretim süreçlerinin stoklara etkisini takip etmeli:

- İş emri başlatıldığında kullanılan malzeme miktarı stoktan düşmeli
- İş emri tamamlandığında üretilen ürün/esans miktarı stoğa eklenmeli
- İş emri geri alındığında stok hareketleri tersine dönmeli
- Tüm stok hareketleri (giriş/çıkış) detaylı şekilde kaydedilmeli

15. **Tüm Bilgiyi Tek Noktada Sunma:** Kullanıcı bu sayfaya baktığında aşağıdaki tüm bilgileri net şekilde görmelidir:

- Hangi ürünlerin bileşenleri eksik?
- Hangi ürünler kritik seviyenin altında?
- Hangi ürünler üretilebilir ve ne kadar?
- Hangi ürünler için üretim emri devam ediyor?
- Hangi esanslar üretilebilir ve ne kadar?
- Hangi esanslar için üretim emri devam ediyor?
- Hangi ürünler için müşteri siparişi bekliyor?
- Hangi malzemeler için çerçeve sözleşme eksik?
- Hangi malzemeler için sipariş verilmiş ve teslim durumu nedir?
- Hangi esanslar için reçete eksik?
- Hangi hammaddeler hem ürün hem de esans üretimi için gerekli ve talep çakışması var mı?
- Hangi aksiyonların acil olarak yapılması gerekiyor?

Bu sayede kullanıcı, tedarik zinciri yönetimi konusunda tüm kararları bu tek sayfada alabilecek ve sistemin sunduğu bilgiler doğrultusunda hareket edebilecek. Kullanıcı sadece bu sayfayı takip ederek, üretim planlaması yapabilir, eksiklikleri görebilir ve gerekli aksiyonları zamanında alabilir.

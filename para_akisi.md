# IDO KOZMETIK ERP SİSTEMİ PARA AKIŞI KILAVUZU

## Genel Tanıtım

IDO KOZMETIK ERP sistemi, kozmetik üretimi yapan bir işletmenin finansal akışlarını takip etmek için tasarlanmış kapsamlı bir sistemdir. Sistem, hem iç hem de dış para akışlarını detaylı şekilde takip etmeyi sağlar.

## Para Girişi (Gelir) Noktaları

### 1. Müşteri Sipariş Tahsilatları
- **Nerede Gerçekleşir:** `gelir_yonetimi.php` sayfası
- **Ne Zaman Gerçekleşir:** 
  - Müşteri siparişi tamamlandığında
  - Sipariş için ödeme alındığında
- **Nasıl Gerçekleşir:**
  - Sipariş detaylarından "Tahsilat Yap" butonu ile
  - Sipariş durumu "Tamamlandı" olarak işaretlendiğinde
  - Manüel olarak gelir yönetimi sayfasından ekleme ile
- **Veritabanı Tablosu:** `gelir_yonetimi`
- **Alanlar:** 
  - `tutar`: Tahsil edilen miktar
  - `para_birimi`: TL, USD, EUR
  - `kategori`: "Sipariş Ödemesi"
  - `siparis_id`: İlgili sipariş
  - `musteri_id`: Tahsil edilen müşteri

### 2. Taksit Planları
- **Nerede Gerçekleşir:** `gelir_yonetimi.php` sayfasının taksit sekmesi
- **Ne Zaman Gerçekleşir:**
  - Müşteri ile taksitli ödeme anlaşması yapıldığında
  - Her taksit vadesi geldiğinde
- **Nasıl Gerçekleşir:**
  - Taksit planı oluşturulur (`gelir_taksit_planlari` tablosu)
  - Sistem otomatik olarak vade tarihlerine göre taksitleri oluşturur (`gelir_taksitleri` tablosu)
  - Her taksit ödendiğinde `odenen_tutar` alanı güncellenir
- **Veritabanı Tabloları:**
  - `gelir_taksit_planlari`: Plan bilgileri
  - `gelir_taksitleri`: Bireysel taksit kayıtları

### 3. Diğer Gelirler
- **Nerede Gerçekleşir:** `gelir_yonetimi.php` sayfası
- **Ne Zaman Gerçekleşir:**
  - Sistemde tanımlanmayan diğer gelir kalemleri oluştuğunda
  - Perakende satışlardan gelir elde edildiğinde
  - Hizmet geliri elde edildiğinde
- **Nasıl Gerçekleşir:**
  - "Yeni Tahsilat Ekle" butonu ile açılan modal formda:
    - Kategori "Diğer", "Perakende Satış" veya "Hizmet Geliri" olarak seçilir
    - Tarih, tutar, para birimi, ödeme tipi ve açıklama girilir
    - Müşteri adı opsiyonel olarak belirtilebilir
    - Sipariş bağlantısı opsiyonel olarak yapılabilir
  - İşlem `api_islemleri/gelir_yonetimi_islemler.php` dosyası üzerinden `addIncome()` fonksiyonu ile veritabanına işlenir
- **Veritabanı Tablosu:** `gelir_yonetimi`
- **Alanlar:**
  - `tarih`: İşlem tarihi
  - `tutar`: Gelir miktarı
  - `para_birimi`: TL, USD, EUR
  - `kategori`: "Diğer", "Perakende Satış", "Hizmet Geliri" veya "Sipariş Ödemesi"
  - `odeme_tipi`: Nakit, Kredi Kartı, Havale/EFT, Çek
  - `aciklama`: Gelir açıklaması
  - `musteri_adi`: İlgili müşteri (opsiyonel)
  - `siparis_id`: Bağlı sipariş (opsiyonel)
  - `musteri_id`: Bağlı müşteri (opsiyonel)

## Para Çıkışı (Gider) Noktaları

### 1. Çerçeve Sözleşme Ödemeleri
- **Nerede Gerçekleşir:** `cerceve_sozlesmeler.php` sayfası
- **Ne Zaman Gerçekleşir:**
  - Tedarikçiye ön ödeme yapıldığında (sözleşme oluşturulurken)
  - Malzeme teslim alındığında ara ödeme yapıldığında
- **Nasıl Gerçekleşir:**
  - Sipariş oluşturulurken "toplu_odenen_miktar" alanı doluysa otomatik gider kaydı oluşur
  - Çerçeve sözleşme detaylarından "Ödeme Yap" butonu ile manuel ödeme yapılır
  - İşlem `api_islemleri/cerceve_sozlesmeler_islemler.php` dosyası üzerinden `make_payment` aksiyonu ile işlenir
  - Sistem otomatik olarak `gider_yonetimi` tablosuna "Malzeme Gideri" olarak kaydeder
  - Ödeme miktarı `toplu_odenen_miktar` alanına eklenir
- **Veritabanı Tablosu:** `cerceve_sozlesmeler` ve `gider_yonetimi`
- **Alanlar:**
  - `toplu_odenen_miktar`: Ödenen toplam miktar
  - `limit_miktar`: Sözleşme limiti
  - `gider_yonetimi.tutar`: TL cinsinden ödeme tutarı (dövizli işlemlerde kur dönüşümüyle hesaplanır)
  - `gider_yonetimi.aciklama`: Ödeme detayları ve döviz kur bilgisi

### 2. Malzeme Giderleri
- **Nerede Gerçekleşir:** `gider_yonetimi.php` sayfası ve `cerceve_sozlesmeler.php` sayfası (otomatik oluşum)
- **Ne Zaman Gerçekleşir:**
  - Çerçeve sözleşme ödemeleri yapıldığında (otomatik olarak)
  - Malzeme alımında doğrudan ödeme yapıldığında (manuel olarak)
- **Nasıl Gerçekleşir:**
  - Çerçeve sözleşme ödemeleri sırasında otomatik olarak
  - Manüel gider ekleme ile "Yeni Gider" butonu kullanılarak
  - Kategori "Malzeme Gideri" olarak seçilir
  - İşlem `api_islemleri/gider_yonetimi_islemler.php` dosyası üzerinden `addExpense()` fonksiyonu ile işlenir
- **Veritabanı Tablosu:** `gider_yonetimi`
- **Alanlar:**
  - `tarih`: İşlem tarihi
  - `tutar`: Gider miktarı (TL cinsinden)
  - `kategori`: "Malzeme Gideri"
  - `aciklama`: Ödeme detayları
  - `odeme_yapilan_firma`: Tedarikçi adı
  - `odeme_tipi`: Nakit, Kredi Kartı, Havale/EFT, Diğer
  - `fatura_no`: Fatura numarası (opsiyonel)

### 3. Personel Giderleri
- **Nerede Gerçekleşir:** `gider_yonetimi.php` sayfası
- **Ne Zaman Gerçekleşir:**
  - Personel maaş ödemesi yapıldığında
  - Personel prim, sigorta gibi ödemeler yapıldığında
- **Nasıl Gerçekleşir:**
  - Kategori "Personel Gideri" seçilerek manüel ekleme ile
  - "Yeni Gider" butonu kullanılarak
  - İşlem `api_islemleri/gider_yonetimi_islemler.php` dosyası üzerinden `addExpense()` fonksiyonu ile işlenir
- **Veritabanı Tablosu:** `gider_yonetimi`
- **Alanlar:**
  - `tarih`: İşlem tarihi
  - `tutar`: Gider miktarı
  - `kategori`: "Personel Gideri"
  - `aciklama`: Ödeme detayları
  - `odeme_yapilan_firma`: Personel adı veya ödeme yapılan kurum
  - `odeme_tipi`: Nakit, Havale/EFT, Diğer
  - `fatura_no`: Fatura numarası (opsiyonel)

### 4. İşletme Giderleri
- **Nerede Gerçekleşir:** `gider_yonetimi.php` sayfası
- **Ne Zaman Gerçekleşir:**
  - Kira, elektrik, su, doğalgaz gibi sabit giderler ödendiğinde
  - Ofis sarf malzemeleri alındığında
- **Nasıl Gerçekleşir:**
  - Kategori "İşletme Gideri" seçilerek manüel ekleme ile
  - "Yeni Gider" butonu kullanılarak
  - İşlem `api_islemleri/gider_yonetimi_islemler.php` dosyası üzerinden `addExpense()` fonksiyonu ile işlenir
- **Veritabanı Tablosu:** `gider_yonetimi`
- **Alanlar:**
  - `tarih`: İşlem tarihi
  - `tutar`: Gider miktarı
  - `kategori`: "İşletme Gideri"
  - `aciklama`: Ödeme detayları
  - `odeme_yapilan_firma`: Hizmet sağlayıcı firma
  - `odeme_tipi`: Nakit, Havale/EFT, Çek, Diğer
  - `fatura_no`: Fatura numarası (opsiyonel)

### 5. Fire Giderleri
- **Nerede Gerçekleşir:** `manuel_stok_hareket.php` sayfası
- **Ne Zaman Gerçekleşir:**
  - Malzeme, esans veya ürün stoktan "Fire / Sayım Eksigi" olarak düşüldüğünde
- **Nasıl Gerçekleşir:**
  - "Yeni Stok Hareketi" formunda "Fire / Sayım Eksigi" işlem türü seçilerek
  - Sistem otomatik olarak fire edilen ürünün teorik maliyetini hesaplayıp "Fire Gideri" olarak gider yönetimine kaydeder
  - İşlem manuel olarak yapıldığında otomatik gider kaydı oluşturulur
- **Veritabanı Tablosu:** `gider_yonetimi`
- **Alanlar:**
  - `tarih`: İşlem tarihi
  - `tutar`: Hesaplanan maliyet miktarı
  - `kategori`: "Fire Gideri"
  - `aciklama`: Fire detayları
  - `odeme_yapilan_firma`: Genellikle boş kalır
  - `odeme_tipi`: Genellikle "Diğer" olarak kaydedilir

## Bekleyen Alacaklar (Tahsil Edilecek Tutarlar)

### 1. Sipariş Bazlı Bekleyen Alacaklar
- **Nerede Görünür:**
  - `gelir_yonetimi.php` sayfasında "Ödeme Bekleyen Sipariş" istatistiği
  - `gelir_yonetimi.php` sayfasında "Toplam Bekleyen Alacak" istatistiği
  - Sipariş detaylarında kalan ödeme miktarı
- **Ne Zaman Görünür:**
  - Sipariş verildiğinde ve kısmi ödeme yapıldığında
  - Sipariş tamamlandığında tam ödeme yapılmamışsa
- **Nasıl Hesaplanır:**
  - Sipariş kalemlerinin toplam tutarından alınan ödemeler çıkarılır
  - Kalan miktar "Bekleyen Alacak" olarak sistemde takip edilir
- **Veritabanı Tablosu:** `siparisler` ve `gelir_yonetimi`
- **Alanlar:**
  - `siparisler.odeme_durumu`: 'bekliyor', 'odendi', 'kismi_odendi'
  - `siparisler.odenen_tutar`: Ödenen miktar
  - `siparisler.para_birimi`: Ödeme birimi
  - `siparisler.toplam_adet`: Toplam sipariş adedi
  - `gelir_yonetimi.siparis_id`: Bağlı gelir kayıtları

### 2. Taksitli Ödemelerde Bekleyen Tutarlar
- **Nerede Görünür:**
  - `gelir_yonetimi.php` sayfasında "Toplam Bekleyen Alacak" istatistiği
  - "Taksit Planları" sekmesinde
- **Ne Zaman Görünür:**
  - Taksit planı oluşturulduğunda
  - Vadesi gelen ama ödenmeyen taksitlerde
- **Veritabanı Tablosu:** `gelir_taksitleri`
- **Alanlar:**
  - `durum`: 'bekliyor', 'kismi_odendi', 'odendi', 'gecikmis', 'iptal'
  - `tutar`: Planlanan taksit tutarı
  - `odenen_tutar`: Ödenen miktar

## Bekleyen Verecekler (Ödenecek Tutarlar)

### 1. Çerçeve Sözleşmelerde Bekleyen Ödemeler
- **Nerede Görünür:**
  - `cerceve_sozlesmeler.php` sayfasında "Ödeme Bekleyen" filtresi
  - "Yapılacak Ödeme" sütunu
- **Ne Zaman Görünür:**
  - Malzeme teslim alındığında ancak ödeme yapılmadığında
  - Sözleşme limiti aşıldığında
- **Veritabanı Tablosu:** `cerceve_sozlesmeler`
- **Hesaplama:** `toplu_odenen_miktar` < `toplam_mal_kabul_miktari` (bu alan view'da hesaplanır)

### 2. Tedarikçi Ödemeleri
- **Nerede Görünür:**
  - `tedarikciye_yapilacak_odemeler_raporu.php` sayfası
- **Ne Zaman Görünür:**
  - Çerçeve sözleşme ödemeleri planlandığında
- **Veritabanı Tablosu:** `cerceve_sozlesmeler`

## Kasa Takibi

### 1. Şirket Kasası
- **Nerede Görünür:** `sirket_kasasi.php` sayfası
- **Veritabanı Tablosu:** `sirket_kasasi`
- **Alanlar:**
  - `para_birimi`: TL, USD, EUR
  - `bakiye`: Mevcut bakiye
- **Nasıl Güncellenir:**
  - Tüm gelirler kasa girişi olarak eklenir
  - Tüm giderler kasa çıkışı olarak düşülür

### 2. Kasa İşlemleri
- **Nerede Gerçekleşir:** `sirket_kasasi.php` sayfası
- **Veritabanı Tablosu:** `kasa_islemleri`
- **Türleri:**
  - `kasa_ekle`: Kasa girişi
  - `kasa_cikar`: Kasa çıkışı
- **Alanlar:**
  - `islem_tipi`: İşlem türü
  - `tutar`: İşlem miktarı
  - `para_birimi`: Para birimi
  - `aciklama`: İşlem açıklaması

## Döviz Kuru ve Çevrimler

### 1. Döviz Kuru Ayarları
- **Nerede Tutulur:** `ayarlar` tablosu
- **Kur Bilgileri:**
  - `dolar_kuru`: USD/TRY kuru
  - `euro_kuru`: EUR/TRY kuru

### 2. Dövizli İşlemler
- **Nasıl İşlenir:**
  - USD ve EUR cinsinden yapılan işlemler kasa takibinde kendi para birimlerinde gösterilir
  - Raporlamalarda TL cinsinden gösterim için kuru ile çarpılır

## Raporlama ve Takip

### 1. Gelir-Gider Dengesi
- **Nerede Görüntülenir:** `sirket_kasasi.php` sayfası
- **Nasıl Hesaplanır:**
  - Toplam Gelirler - Toplam Giderler = Net Para Akışı

### 2. Aylık Takip
- **Nasıl Gerçekleştirilir:**
  - Her ay için ayrı istatistikler hesaplanır
  - `gelir_yonetimi` ve `gider_yonetimi` tablolarında tarih filtresi ile

### 3. Bekleyen Tutarlar Raporu
- **Nerede Bulunur:**
  - `gelir_yonetimi.php`: Bekleyen alacaklar
  - `cerceve_sozlesmeler.php`: Bekleyen ödemeler
  - `tedarikci_odeme_raporu.php`: Tedarikçilere yapılacak ödemeler

## Örnek Senaryolar

### Örnek 1: Sipariş Bazlı Para Akışı
1. Müşteri sipariş verir (ürün fiyatı 1000 USD)
2. 300 USD avans alır
3. Sipariş tamamlanır, 700 USD kalanı tahsil edilir
4. Veritabanında:
   - `siparisler` tablosunda `odenen_tutar` alanı güncellenir
   - `gelir_yonetimi` tablosuna 2 kayıt (300 USD ve 700 USD)
   - `odeme_durumu` alanı 'kismi_odendi' veya 'odendi' olur

### Örnek 2: Çerçeve Sözleşme Para Akışı
1. Tedarikçiyle 1000 adet malzeme için sözleşme yapılır (1 USD/adet)
2. 500 adet teslim alınır, ödeme yapılır
3. 500 adet daha teslim alınır, ödeme yapılmaz
4. Veritabanında:
   - `cerceve_sozlesmeler` tablosunda `toplu_odenen_miktar` alanı güncellenir
   - `gider_yonetimi` tablosuna gider kaydı
   - Kalan ödeme miktarı "bekleyen ödeme" olarak takip edilir

### Örnek 3: Fire Kaydı ve Gider Oluşumu
1. 10 lt esans stoğundan 2 lt fire kaydı yapılır
2. Esansın maliyeti 50 USD/lt olsun
3. Veritabanında:
   - 2 lt × 50 USD = 100 USD fire gideri `gider_yonetimi` tablosuna eklenir
   - Kategori "Fire Gideri" olarak belirlenir

## Önemli Notlar

1. **Para Birimi Dikkati:** Sistemde TL, USD ve EUR para birimleri desteklenmektedir. Raporlamalarda dikkatli olunmalıdır.

2. **Kur Takibi:** Dövizli işlemler sistemde kendi para birimlerinde takip edilir. TL karşılıkları için kuru ile çarpım yapılır.

3. **Tahsilat Takibi:** Sipariş bazlı tahsilatlar `siparisler` tablosundaki `odeme_durumu` ve `odenen_tutar` alanları ile takip edilir.

4. **Vade Takibi:** Taksitli ödemelerde `gelir_taksitleri` tablosundaki `vade_tarihi` ve `durum` alanları ile takip yapılır.

5. **Fire Yönetimi:** Fire kayıtları doğrudan gider oluşturduğundan dikkatli girilmelidir.

6. **Kasa Dengesi:** `sirket_kasasi` tablosundaki bakiyeler, tüm gelir ve gider işlemlerinden sonra otomatik olarak güncellenir.
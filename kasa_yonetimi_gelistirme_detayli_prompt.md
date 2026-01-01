# Kasa Yönetimi Sistemi Geliştirme - Detaylı ve Sistemli Geliştirme Rehberi

## 1. Genel Sistem Mimarisi ve Hedefler

### 1.1. Amaç ve Kapsam

- Mevcut `sirket_kasasi.php` sayfası kaldırılacak ve yerine yeni "Kasa Yönetimi" sayfası oluşturulacak
- Sistemde tekil para birimi değil, çoklu para birimi (TL, USD, EUR) ve çek kasası yönetimi entegre edilecek
- Tüm gelir ve gider işlemlerinin kasa etkileşimleri detaylı şekilde takip edilebilecek
- Stoktaki ürünlerin, malzemelerin ve esansların toplam değerleri kasa yönetimi sayfasında gösterilecek

### 1.2. Mevcut Sistem Bileşenleri

- `sirket_kasasi.php`: Mevcut kasa yönetimi sayfası
- `sirket_kasasi_islemler.php`: API işlemleri
- `gider_yonetimi.php`: Gider takibi
- `cerceve_sozlesmeler.php`: Çerçeve sözleşme ve ödeme işlemleri
- `tekrarli_odemeler.php`: Tekrarlı ödeme işlemleri
- `personel_bordro.php`: Personel maaş ve avans işlemleri
- `gelir_yonetimi.php`: Gelir takibi

## 2. Veritabanı Değişiklikleri

### 2.0. Geçiş Planı ve Mevcut Verilerin İşlenmesi

- Mevcut `kasa_islemleri` tablosundaki veriler, yeni `kasa_hareketleri` tablosuna dönüştürülecektir
- `kasa_islemleri` tablosundaki `islem_tipi` değerleri yeni sistemdeki karşılıklarına çevrilecektir:
  - 'gelir' → 'gelir_girisi'
  - 'gider' → 'gider_cikisi'
  - 'transfer_giris' → 'transfer_giris'
  - 'transfer_cikis' → 'transfer_cikis'
- Geçiş sonrası `kasa_islemleri` tablosu okuma amaçlı kullanılabilir veya yeni sistem tamamen entegre edildikten sonra kaldırılabilir
- Mevcut `sirket_kasasi` tablosundaki bakiyeler yeni sistemle uyumlu hale getirilecektir

### 2.1. Mevcut Tablolar

- `sirket_kasasi`: Para birimine göre kasa bakiyeleri (TL, USD, EUR)
- `kasa_islemleri`: Mevcut kasa işlemlerinin detayları (geçiş sürecinde, yeni sistemde `kasa_hareketleri` kullanılacak)
- `gider_yonetimi`: Gider kayıtları
- `gelir_yonetimi`: Gelir kayıtları
- `ayarlar`: Döviz kurları (dolar_kuru, euro_kuru)

### 2.2. Yeni Tablo: `cek_kasasi`

```sql
CREATE TABLE `cek_kasasi` (
  `cek_id` int(11) NOT NULL AUTO_INCREMENT,
  `cek_no` varchar(50) NOT NULL COMMENT 'Çek numarası veya referans numarası',
  `cek_tutari` decimal(15,2) NOT NULL COMMENT 'Çek tutarı',
  `cek_para_birimi` varchar(3) NOT NULL DEFAULT 'TL' COMMENT 'Çekin para birimi (TL, USD, EUR)',
  `cek_sahibi` varchar(255) NOT NULL COMMENT 'Çekin sahibi veya veren kişi',
  `cek_banka_adi` varchar(255) NULL COMMENT 'Çekin ait olduğu banka',
  `cek_subesi` varchar(255) NULL COMMENT 'Çekin ait olduğu şube',
  `vade_tarihi` date NOT NULL COMMENT 'Çekin vade tarihi',
  `cek_tipi` enum('alacak', 'verilen') NOT NULL DEFAULT 'alacak' COMMENT 'Çekin türü: alacak (firma çekti) veya verilen (firmaya verildi)',
  `cek_durumu` enum('alindi', 'kullanildi', 'iptal', 'geri_odendi', 'teminat_verildi', 'tahsilde') NOT NULL DEFAULT 'alindi' COMMENT 'Çekin durumu',
  `cek_alim_tarihi` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Çekin alındığı tarih',
  `cek_kullanim_tarihi` datetime NULL COMMENT 'Çekin kullanıldığı tarih',
  `cek_son_durum_tarihi` datetime NULL COMMENT 'Çekin son durum değişikliği tarihi',
  `aciklama` text DEFAULT NULL COMMENT 'Çek açıklaması',
  `kaydeden_personel` varchar(100) DEFAULT NULL COMMENT 'Çeki kaydeden personel',
  `ilgili_tablo` varchar(50) DEFAULT NULL COMMENT 'Çekin ilişkili olduğu tablo (gider_yonetimi, gelir_yonetimi, cerceve_sozlesmeler)',
  `ilgili_id` int(11) DEFAULT NULL COMMENT 'Çekin ilişkili olduğu kayıt ID',
  PRIMARY KEY (`cek_id`),
  KEY `vade_tarihi` (`vade_tarihi`),
  KEY `cek_durumu` (`cek_durumu`),
  KEY `cek_para_birimi` (`cek_para_birimi`),
  KEY `cek_no` (`cek_no`),
  KEY `cek_tipi` (`cek_tipi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
```

> **Not:** `cek_tipi` alanı, çekin alacak (firma çekti) veya verilen (firmaya verildi) olduğunu belirtir. Bu, kasa etkileşimlerini doğru şekilde hesaplamak için önemlidir. Alacak çekler kasa artışı sağlarken, verilen çekler kasa azalışına neden olur.

### 2.3. Mevcut Tabloların Güncellenmesi

#### 2.3.1. `gider_yonetimi` Tablosu

```sql
ALTER TABLE `gider_yonetimi`
ADD COLUMN `kasa_secimi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
ADD COLUMN `cek_secimi` int(11) NULL COMMENT 'cek_kasasi tablosundaki cek_id';
```

> **Not:** `cek_secimi` alanı dolu olduğunda, `odeme_tipi` alanının değeri 'Çek' olarak güncellenmelidir. Bu, mevcut veri tutarlılığını korumak için önemlidir.

#### 2.3.2. `gelir_yonetimi` Tablosu

```sql
ALTER TABLE `gelir_yonetimi`
ADD COLUMN `kasa_secimi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
ADD COLUMN `cek_secimi` int(11) NULL COMMENT 'cek_kasasi tablosundaki cek_id';
```

> **Not:** `cek_secimi` alanı dolu olduğunda, `odeme_tipi` alanının değeri 'Çek' olarak güncellenmelidir. Bu, mevcut veri tutarlılığını korumak için önemlidir.

#### 2.3.3. `cerceve_sozlesmeler` Tablosu

```sql
ALTER TABLE `cerceve_sozlesmeler`
ADD COLUMN `odeme_kasasi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
ADD COLUMN `odenen_cek_id` int(11) NULL COMMENT 'cek_kasasi tablosundaki cek_id';
```

> **Not:** Mevcut tüm kayıtlar için `kasa_secimi` ve `odeme_kasasi` alanları 'TL' olarak atanacaktır. Geçmiş işlemleri doğru şekilde yansıtmak için bu alanlar daha sonra manuel olarak güncellenebilir.

## 3. Yeni Kasa Yönetimi Sayfası (kasa_yonetimi.php)

### 3.1. Sayfa Bölümleri

#### 3.1.1. Üst Panel

- Elimdeki Stoklar (3 kutucuk)
  - Ürünlerin toplam değeri (adet \* satış fiyatı, TL'ye çevrilmiş)
  - Malzemelerin toplam değeri (adet \* alış fiyatı, TL'ye çevrilmiş)
  - Esansların toplam değeri (adet \* teorik maliyet fiyatı, TL'ye çevrilmiş)
  - Toplam duran varlıklar (üçünün toplamı)

#### 3.1.2. Kasa Bakiyeleri Paneli

- TL Kasası bakiyesi
- USD Kasası bakiyesi
- EUR Kasası bakiyesi
- Çek Kasası (toplam çek sayısı ve TL cinsinden yaklaşık değer)

#### 3.1.3. Tedarikçilere Yapılacak Ödemeler Paneli

- Çerçeve sözleşme verilerinden toplam ödemeler
- Dolar cinsinden toplam
- Euro cinsinden toplam
- TL cinsinden toplam
- TL bazında toplam (döviz kurlarıyla çevrilmiş)

#### 3.1.4. Aylık Kasa Hareketleri Paneli

- Bu ay yapılan toplam gelirler (kasa bazında ve para birimine göre)
- Bu ay yapılan toplam giderler (kasa bazında ve para birimine göre)
- Bu ay yapılan toplam çek girişi/çıkışı

### 3.2. Kasa İşlem Fonksiyonları

- TL, USD ve EUR kasalarına ekleme/çıkarma işlemleri yapılabilir
- Yeni kasa oluşturulamaz veya mevcut kasalar silinemez (kasa türleri sabittir: TL, USD, EUR, çek_kasasi)
- Çek işlemleri sadece gelir/gider/çerçeve_sözleşmeler sayfaları aracılığıyla yapılır
- Çek detayları (banka, vade tarihi, veren kişi, durum)

## 4. Gider Yönetimi Sayfası Güncellemeleri

### 4.1. "Yeni Gider Ekle" Modalı

- Yeni bir alan: "Kasa Seçimi" (TL, USD, EUR, Çek Kasası)
- Eğer "Çek Kasası" seçilirse, "Çek Seçimi" dropdown'ı açılır
- Çek kasası seçildiğinde, mevcut çekler listelenir ve biri seçilir
- Seçilen çek ID'si `cek_secimi` alanına yazılır
- Aynı zamanda `odeme_tipi` alanı 'Çek' olarak güncellenir
- Kasa bakiyesi seçilen para birimine göre eksilir (çek kasası için bakiye etkisi farklıdır)
- Çek kullanıldığında durumu "kullanildi" olarak güncellenir

### 4.2. Gider Düzenleme ve Silme

- Gider silindiğinde kasa bakiyesi tersine döner
- Çek kullanılmışsa, çek durumu "alindi" olarak geri döner
- Gider düzenlendiğinde eski kasa bakiyesi geri eklenir, yeni kasa bakiyesi düşürülür

## 5. Çerçeve Sözleşmeler Sayfası Güncellemeleri

### 5.1. Ödeme Yap Butonu

- Ödeme yap butonuna basıldığında yeni modal açılır
- Modalda "Ödeme Yapılacak Kasa" seçimi (TL, USD, EUR, Çek Kasası)
- Para birimi seçimi yapıldığında, o kasadaki bakiye gösterilir
- Eğer Çek Kasası seçilirse, mevcut çekler listelenir ve biri seçilir
- Seçilen çek ID'si `odenen_cek_id` alanına yazılır
- Aynı zamanda `odeme_tipi` alanı 'Çek' olarak güncellenir
- Döviz kuru dönüşümü (ayarlar tablosundaki kur değerleri kullanılır)
- Ödeme yapıldığında gider yönetimi sayfasına kayıt atılır
- Kasa bakiyesi düşer veya çek kullanılır

## 6. Gelir Yönetimi Sayfası Güncellemeleri

### 6.1. "Yeni Gelir Ekle" Modalı

- Yeni bir alan: "Kasa Seçimi" (TL, USD, EUR, Çek Kasası)
- Eğer "Çek Kasası" seçilirse, "Çek Seçimi" dropdown'ı açılır
- Çek ile ödeme alındığında, çek kasasına yeni çek eklenir
- Seçilen çek ID'si `cek_secimi` alanına yazılır
- Aynı zamanda `odeme_tipi` alanı 'Çek' olarak güncellenir
- Kasa bakiyesi seçilen para birimine göre artar

## 7. Tekrarlı Ödemeler ve Personel Bordro Sayfaları

### 7.1. Ortak Özellikler

- Her ödeme için "Kasa Seçimi" seçeneği
- Kasa bakiyesi seçilen para birimine göre eksilir
- Gider yönetimi sayfasına detaylı kayıt atılır
- Kasa kayıtlarına detaylı işlem kaydı eklenir

## 8. API ve Backend İşlemleri

### 8.1. Yeni API Dosyası: `api_islemleri/kasa_yonetimi_islemler.php`

- Kasa bakiyelerini getirme
- Kasa işlemi ekleme/çıkarma
- Çek ekleme/çıkarma
- Kasa hareketlerini listeleme
- Kasa istatistiklerini getirme

### 8.2. Mevcut API Dosyalarının Güncellenmesi

- `gider_yonetimi_islemler.php`: Yeni alanlarla uyumlu hale getirilecek
- `cerceve_sozlesmeler_islemler.php`: Ödeme işlemleri kasa seçimi ile uyumlu hale getirilecek
- `gelir_yonetimi_islemler.php`: Yeni alanlarla uyumlu hale getirilecek

## 9. Kasa Hareketleri Takibi

### 9.1. Yeni Kasa İşlemleri Takip Tablosu: `kasa_hareketleri`

Tüm kasa işlemleri için ayrıntılı bir takip tablosu oluşturulacak. Bu tablo, sistemde yapılan her kasa işlemi için detaylı bilgi sağlayacaktır.

```sql
CREATE TABLE `kasa_hareketleri` (
  `hareket_id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime NOT NULL DEFAULT current_timestamp(),
  `islem_tipi` varchar(50) NOT NULL COMMENT 'kasa_ekle, kasa_cikar, cek_alma, cek_odeme, cek_kullanimi, cek_tahsile_gonderme, cek_tahsildi, gelir_girisi, gider_cikisi, transfer_giris, transfer_cikis',
  `kasa_adi` varchar(20) NOT NULL COMMENT 'TL, USD, EUR, cek_kasasi',
  `cek_id` int(11) NULL COMMENT 'İşlem çekle ilgili ise cek_kasasi tablosundaki ID',
  `tutar` decimal(15,2) NOT NULL COMMENT 'İşlem tutarı',
  `para_birimi` varchar(3) NOT NULL DEFAULT 'TL' COMMENT 'Tutarın para birimi',
  `doviz_kuru` decimal(10,4) NULL COMMENT 'İşlem dövizliyse kullanılan kur',
  `tl_karsiligi` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tutarın TL karşılığı',
  `kaynak_tablo` varchar(50) NULL COMMENT 'İşlemin kaynağı (gider_yonetimi, gelir_yonetimi, cerceve_sozlesmeler, tekrarli_odemeler, personel_bordro)',
  `kaynak_id` int(11) NULL COMMENT 'Kaynak tablodaki kayıt ID',
  `aciklama` text NULL COMMENT 'İşlem açıklaması',
  `kaydeden_personel` varchar(100) NULL COMMENT 'İşlemi yapan personel',
  `ilgili_firma` varchar(255) NULL COMMENT 'İşlemin ilgili olduğu firma',
  `ilgili_musteri` varchar(255) NULL COMMENT 'İşlemin ilgili olduğu müşteri',
  `fatura_no` varchar(100) NULL COMMENT 'İşlemin bağlı olduğu fatura numarası',
  `odeme_tipi` varchar(50) NULL COMMENT 'Nakit, Kredi Kartı, Havale, Çek vs.',
  `proje_kodu` varchar(50) NULL COMMENT 'İşlemin ait olduğu proje',
  `is_merkezi` varchar(100) NULL COMMENT 'İş merkezi bilgisi',
  `ekstra_veri` json NULL COMMENT 'İşleme özel ekstra veriler',
  PRIMARY KEY (`hareket_id`),
  KEY `tarih` (`tarih`),
  KEY `islem_tipi` (`islem_tipi`),
  KEY `kasa_adi` (`kasa_adi`),
  KEY `kaynak_tablo` (`kaynak_tablo`),
  KEY `kaynak_id` (`kaynak_id`),
  KEY `para_birimi` (`para_birimi`),
  KEY `cek_id` (`cek_id`),
  KEY `tutar` (`tutar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
```

> **Not:** `tl_karsiligi` alanı, dövizli işlemler için TL karşılığını saklar. Döviz kuru bilgisi `doviz_kuru` alanında saklanır. Bu alan, raporlamalar ve analizler için önemlidir. Bu tablo, mevcut `kasa_islemleri` tablosunun yerini alacaktır.

### 9.2. Kasa Hareketleri Kayıt İşlemleri

Her kasa işlemi yapıldığında, aşağıdaki adımlarla `kasa_hareketleri` tablosuna kayıt atılacaktır:

#### 9.2.1. Kasa Ekleme/Çıkarma İşlemleri

- Kullanıcı "Kasa İşlemi Yap" modalından işlem yapar
- Sistem işlemi doğrular ve aşağıdaki bilgileri toplar:
  - `tarih`: İşlem tarihi ve saati
  - `islem_tipi`: "kasa_ekle" veya "kasa_cikar"
  - `kasa_adi`: Seçilen kasa (TL, USD, EUR)
  - `tutar`: İşlem tutarı
  - `para_birimi`: Seçilen para birimi
  - `doviz_kuru`: Gerekirse kullanılan döviz kuru
  - `tl_karsiligi`: Tutarın TL karşılığı
  - `aciklama`: Kullanıcının girdiği açıklama
  - `kaydeden_personel`: İşlemi yapan personel
- Kayıt `kasa_hareketleri` tablosuna eklenir

#### 9.2.2. Çek İşlemleri

- Çek alındığında (alacak çek): `islem_tipi` = "cek_alma", `kasa_adi` = "cek_kasasi", `cek_id` = ilgili çek ID, `cek_tipi` = "alacak"
- Çek verildiğinde (verilen çek): `islem_tipi` = "cek_odeme", `kasa_adi` = "cek_kasasi", `cek_id` = ilgili çek ID, `cek_tipi` = "verilen"
- Çek kullanıldığında (verilen çek): `islem_tipi` = "cek_kullanimi", `cek_id` = kullanılmakta olan çek ID
- Çek tahsile gönderildiğinde: `islem_tipi` = "cek_tahsile_gonderme", `cek_id` = tahsile gönderilen çek ID
- Çek tahsil edildiğinde: `islem_tipi` = "cek_tahsildi", `cek_id` = tahsil edilen çek ID
- Çek ile gider yapıldığında: `islem_tipi` = "gider_cikisi", `kaynak_tablo` = "gider_yonetimi", `kaynak_id` = gider ID, `cek_id` = kullanılan çek ID
- Çek ile gelir alındığında: `islem_tipi` = "gelir_girisi", `kaynak_tablo` = "gelir_yonetimi", `kaynak_id` = gelir ID, `cek_id` = alınan çek ID

#### 9.2.3. Gider ve Gelir İşlemleri

- Gider eklendiğinde: `islem_tipi` = "gider_cikisi", `kaynak_tablo` = "gider_yonetimi", `kaynak_id` = gider ID
- Gelir eklendiğinde: `islem_tipi` = "gelir_girisi", `kaynak_tablo` = "gelir_yonetimi", `kaynak_id` = gelir ID
- Diğer kaynaklar (cerceve_sozlesmeler, tekrarli_odemeler, personel_bordro) için benzer şekilde

#### 9.2.4. Çerçeve Sözleşme Ödeme İşlemleri

- Ödeme yapıldığında: `islem_tipi` = "gider_cikisi", `kaynak_tablo` = "cerceve_sozlesmeler", `kaynak_id` = sözleşme ID
- Eğer çek kullanıldıysa: `cek_id` = kullanılan çek ID

### 9.3. Kasa Bakiyesi Hesaplama

- Her işlemde ilgili kasa bakiyesi güncellenir
- Çek işlemleri için `cek_kasasi` tablosu güncellenir
- Döviz işlemleri için `ayarlar` tablosundaki kur değerleri kullanılır
- Tüm işlemler `kasa_hareketleri` tablosuna detaylı şekilde kaydedilir

### 9.4. Yeni Kasa Yönetimi Sayfasından Erişim

- Yeni `kasa_yonetimi.php` sayfasında "Kasa Hareketleri" sekmesi olacak
- Bu sekmede tüm kasa hareketleri tarih, kasa, işlem tipi, para birimi gibi filtrelerle listelenebilecek
- Her hareketin detayları (açıklama, ilgili firma/müşteri, fatura no, ödeme tipi vs.) görüntülenebilecek
- Excel'e aktarım özelliği olacak
- Arama ve sıralama işlemleri desteklenecek

## 10. Döviz Kuru Yönetimi

### 10.1. Kur Dönüşümleri

- `ayarlar` tablosu, sistem genelinde kullanılan ayarları ve sabit değerleri içerir
- Mevcut tablo yapısında `ayar_anahtar` ve `ayar_deger` alanları bulunur
- Döviz kurları şu şekilde saklanır:
  - `ayar_anahtar` = 'dolar_kuru', `ayar_deger` = '42.8500' (örnek değer)
  - `ayar_anahtar` = 'euro_kuru', `ayar_deger` = '50.5070' (örnek değer)
- Farklı para birimleri arasında dönüşüm gerektiğinde bu ayarlar tablosundaki değerler kullanılır
- Ödeme TL kasasından yapılacak ama borç dolar ise, sistem ayarlar tablosundan dolar kuru alınarak TL karşılığı hesaplanır
- Kur dönüşümü hesaplamaları:
  - Dolar tutarı \* dolar_kuru = TL karşılığı
  - Euro tutarı \* euro_kuru = TL karşılığı
- Kasa hareketleri tablosunda `doviz_kuru` ve `tl_karsiligi` alanları bu dönüşümleri kaydeder
- Kur değerleri manuel olarak ayarlar sayfasından güncellenebilir veya otomatik olarak döviz kurları API'si üzerinden çekilebilir

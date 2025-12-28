# Şirket Kasası Sayfası Geliştirme Planı

## Proje Hakkında
Mevcut gelir ve gider takip sayfalarını birleştirerek, şirketin tüm parasal hareketlerini tek bir ekranda görebileceğimiz yeni bir "Şirket Kasası" sayfası oluşturacağız. Bu sayede hem gelirleri hem de giderleri aynı yerden takip edebileceğiz.

## Mevcut Sistem
Şu anda iki ayrı sayfada tutulan veriler var:
- **Gelir sayfası**: Şirkete giren paraları takip ediyoruz
- **Gider sayfası**: Şirketten çıkan paraları takip ediyoruz

## Yeni Sistemde Neler Olacak?

### Ana Özellikler (Sadece Görüntüleme)
1. **Tek Ekranda Her Şey**: Gelir ve giderleri aynı sayfada görebileceğiz
2. **Farklı Para Birimleri**: TL, USD ve EURO cinsinden işlemleri görüntüleyebileceğiz
3. **Anlık İstatistikler**: Toplam gelir, gider ve kâr/zarar durumunu anlık görebileceğiz
4. **Kolay Filtreleme**: Tarih, kategori veya tutar aralığına göre arama yapabileceğiz
5. **Sipariş Takibi**: Bekleyen ödemeleri kolayca görebileceğiz
6. **Kasa Durumu**: Farklı para birimlerinin kasa bakiyelerini görüntüleyebileceğiz

## Geliştirme Planı

### 1. Yeni Sayfa Oluşturulacak (`sirket_kasasi.php`)
- [x] Yeni sayfanın temel yapısı oluşturulacak
- [x] Eski sayfalardan stil ve tasarım alınacak
- [x] Menü ve başlık bölümleri eklenecek
- [x] İçerik bölümleri ayrılacak (gelirler, giderler, istatistikler)

### 2. İstatistikler Bölümü
- [x] Aylık toplam gelir gösterilecek
- [x] Aylık toplam gider gösterilecek
- [x] Net kâr/zarar hesaplanacak
- [x] Bekleyen alacaklar listelenecek

### 3. Gelir Yönetimi Bölümü (Sadece Görüntüleme)
- [x] Gelir tablosu entegre edilecek
- [x] Arama ve filtreleme özellikleri korunacak
- [x] Detay görüntüleme özelliği eklenecek

### 4. Gider Yönetimi Bölümü (Sadece Görüntüleme)
- [x] Gider tablosu entegre edilecek
- [x] Arama ve filtreleme özellikleri korunacak
- [x] Detay görüntüleme özelliği eklenecek

### 5. Merkezi Görüntüleme Yönetimi (`sirket_kasasi_islemler.php`)
- [x] Gelir ve gider verilerini tek merkezden çekeceğiz
- [x] Genel istatistik fonksiyonları eklenecek
- [x] Filtreleme ve arama özellikleri geliştirilecek

### 6. Görüntüleme İşlemleri
- [x] Tek dosyada tüm görüntüleme işlemlerini yönetecek sistem kurulacak
- [x] Otomatik veri güncellemesi yapılacak
- [x] Filtreleme ve arama işlemleri optimize edilecek

### 7. Kullanıcı Dostu Tasarım
- [x] Mobil uyumlu tasarım (tablet ve telefonlarda da çalışacak)
- [x] Sekmeler sistemi (gelirler ve giderler için ayrı sekmeler)
- [x] Görsel istatistik kartları
- [x] İşlem sırasında bekleme ve hata mesajları

### 8. Güvenlik
- [x] Mevcut güvenlik önlemleri korunacak
- [x] İşlem kayıtları tutulacak
- [x] Kullanıcı oturum kontrolleri sağlanacak

### 9. Döviz Yönetimi
- [x] TL, USD ve EURO için ayrı kasa hesapları (dönüştürme olmadan)
- [x] Kasa bakiyeleri görüntülenebilecek
- [x] Para birimleri arasında transfer yapılabilecek (manuel dönüştürme ile)
- [x] Güncel döviz kuru entegrasyonu (sadece bilgi amaçlı)
- [x] Tüm kasa hareket geçmişi tutulacak (orijinal para biriminde)

### 10. Test ve Kontrol
- [x] Tüm işlemler test edilecek
- [x] Hız ve performans kontrolü yapılacak
- [x] Hata durumları için çözüm planları hazırlanacak
- [x] Farklı tarayıcılarda uyumluluk testi yapılacak

## Yeni Dosyalar
```
sirket_kasasi.php                 # Ana sayfa
api_islemleri/
├── sirket_kasasi_islemler.php    # Merkezi görüntüleme dosyası
assets/
├── css/
│   └── sirket_kasasi.css         # Tasarım dosyası
└── js/
    └── sirket_kasasi.js          # Görüntüleme işlemleri dosyası
```

## Veri Akışı (Sadece Görüntüleme)
1. Sayfa açıldığında genel istatistikler otomatik olarak yüklenecek
2. Gelir ve gider tabloları ayrı ayrı gösterilecek
3. Arama ve filtreleme işlemleri gerçekleştirilebilecek
4. Detay görüntüleme özellikleri kullanılabilecek

## Dikkat Edilmesi Gerekenler
- **Veri Uyumu**: Tüm işlemler güvenli şekilde yapılacak
- **Hız**: Büyük veri setlerinde sistem hızlı çalışacak
- **Kullanım Kolaylığı**: Sekmelerle bölümler daha anlaşılır olacak

## Veritabanı Değişiklikleri
- **Yeni Tablolar**:
  - `sirket_kasasi`: Şirketin TL, USD ve EURO cinsinden kasalarını tutar
  - `kasa_islemleri`: Tüm para giriş ve çıkışlarını kaydeder
- **Gelir Eklendiğinde**: Seçilen para birimindeki kasa bakiyesi artar
- **Gider Eklendiğinde**: Seçilen para birimindeki kasa bakiyesi azalır
- **Para Transferi**: Farklı para birimleri arasında transfer yapılabilecek

## Ödeme Yöntemlerinin Etkisi
- **Nakit Ödemeler**: Seçilen para birimindeki nakit kasaya etki eder
- **Banka Havalesi/EFT**: Belirlenen banka hesabına göre ilgili kasaya etki eder
- **Kredi Kartı**: Kredi kartı harcamaları için ayrı takip, ödeme yapıldığında ilgili kasadan düşer
- **Çek/Senet**: Gerçekleştiğinde ilgili kasaya etki eder
- **Kripto Para**: Daha sonradan eklenecek özel para birimi olarak yönetilecek

## Mevcut Sistemdeki Ödeme Yöntemleri
- **Gelir Yönetimi (gelir_yonetimi)**:
  - `gelir_id` (anahtar): Kayıt numarası
  - `tarih` alanı: datetime formatında
  - `tutar` alanı: TL cinsinden
  - `kategori` alanı: "Sipariş Ödemesi" gibi gelir türünü belirtir
  - `aciklama` alanı: Gelirle ilgili detay
  - `odeme_tipi` alanı: "Nakit", "Kredi Kartı", "Havale/EFT", "Çek"
  - `musteri_adi` alanı: Gelirin geldiği müşteriyi belirtir
  - `siparis_id` alanı: Bağlı olduğu siparişi belirtir
  - `kaydeden_personel_ismi` alanı: Kaydı yapan personel

- **Gider Yönetimi (gider_yonetimi)**:
  - `gider_id` (anahtar): Kayıt numarası
  - `tarih` alanı: date formatında
  - `tutar` alanı: TL cinsinden
  - `kategori` alanı: "Personel Gideri", "Malzeme Gideri", "Kira", vb.
  - `aciklama` alanı: Giderle ilgili detay
  - `odeme_tipi` alanı: "Nakit", "Kredi Kartı", "Havale", "Diğer"
  - `odeme_yapilan_firma` alanı: Giderin yapıldığı firmayı belirtir
  - `fatura_no` alanı: Giderle ilişkili fatura numarası
  - `kaydeden_personel_ismi` alanı: Kaydı yapan personel

## Kasa Hareketleri
- **Gelir Eklendiğinde**: Seçilen ödeme tipine göre ilgili kasa (nakit, banka, kredi kartı) artar
- **Gider Eklendiğinde**: Seçilen ödeme tipine göre ilgili kasa azalır
- **Kasa Bakiyeleri**: Gerçek zamanlı olarak takip edilir
- **Farklı Para Birimleri**: TL, USD ve EURO cinsinden ayrı kasa hesapları

## Mevcut Otomatik İşlemler
- **Sipariş Tahsilatları**: Gelir eklendiğinde bağlı siparişin ödeme durumu otomatik güncellenir
- **Çerçeve Sözleşme Ödemeleri**: Tedarikçilere yapılan ödemeler gider olarak kaydedilir
- **Fire Kayıtları**: Stok hareketlerinde fire kaydı oluşturulduğunda gider olarak eklenir
- **Personel Ödemeleri**: Maaş ve avans ödemeleri gider olarak otomatik kaydedilir
- **Tekrarlı Ödemeler**: Belirlenen ödemeler zamanı geldiğinde gider olarak eklenir

## Mevcut Döviz Kuru Sistemi
- **Ayarlar Tablosu**: `dolar_kuru` ve `euro_kuru` alanları ile TL cinsinden kuru tutar
- **Giderlerde Döviz**: Çerçeve sözleşmelerde USD cinsinden ödemeler TL'ye dönüştürülerek gider kaydına eklenir
- **Kur Takibi**: Sistemde USD ve EURO cinsinden işlemler TL'ye çevrilerek kaydedilir

# Parfüm ERP Sistemi

Bu proje, parfüm üretim süreçlerini yönetmek için geliştirilmiş kapsamlı bir ERP sistemidir.

## Özellikler

### Kullanıcı Yönetimi
- Personel ve müşteri kullanıcıları
- Roller ve yetkilendirme sistemi
- Oturum yönetimi

### Üretim Yönetimi
- Esans üretim iş emirleri
- Montaj iş emirleri
- Ürün ağaçları
- Stok yönetimi
- Kalite kontrol süreçleri

### Stok Yönetimi
- Malzeme, esans ve ürün stok takibi
- Otomatik stok güncellemesi
- Kritik stok seviyesi uyarıları
- Stok hareket kayıtları

### Tedarik Zinciri Yönetimi
- Tedarikçi yönetimi
- Çerçeve sözleşmeler
- Gider yönetimi

### Müşteri İlişkileri Yönetimi
- Müşteri sipariş yönetimi
- Geri bildirim yönetimi
- Davranış analizi

## Kurulum

### Gereksinimler
- XAMPP (Apache, MySQL, PHP)
- Web tarayıcısı

### Kurulum Adımları

1. XAMPP'i kurun ve başlatın
2. Apache ve MySQL servislerini başlatın
3. Proje dosyalarını `C:\xampp\htdocs\projem` dizinine kopyalayın
4. Tarayıcıdan `http://localhost/projem` adresine gidin

### Veritabanı Kurulumu

1. Tarayıcıdan `http://localhost/projem/setup_database_simple_views.php` adresine giderek veritabanını oluşturun
2. Varsayılan yönetici hesabı:
   - Kullanıcı adı: `admin`
   - Şifre: `admin123`

## Kullanım

### Giriş
- Yönetim paneline erişmek için `http://localhost/projem/login.php` adresine gidin
- Varsayılan yönetici hesabı ile giriş yapın

### Ana Menü
- Sol menüden farklı modüllere erişebilirsiniz:
  - Lokasyonlar
  - Malzemeler
  - Ürün Ağaçları
  - Tanklar
  - Personeller
  - Sistem Kullanıcıları
  - Müşteriler
  - Esanslar
  - Gider Yönetimi
  - Tedarikçiler
  - İş Merkezleri
  - Müşteri Geri Bildirimleri

### Müşteri Paneli
- Müşteriler `http://localhost/projem/customer_panel.php` adresinden giriş yapabilir
- Stoktaki ürünleri görüntüleyebilir ve sipariş oluşturabilirler

## Geliştirme

### Dosya Yapısı
```
projem/
├── config.php              # Veritabanı bağlantı ayarları
├── login.php               # Giriş sayfası
├── navigation.php          # Personel navigasyon menüsü
├── customer_panel.php      # Müşteri paneli
├── setup_database*.php     # Veritabanı kurulum betikleri
├── *.php                   # Modül sayfaları
└── README.md               # Bu dosya
```

### Modüller

1. **Lokasyonlar** - Depo ve raf tanımlamaları
2. **Malzemeler** - Hammaddenin yönetimi
3. **Ürün Ağaçları** - Ürün bileşen tanımları
4. **Tanklar** - Esans üretim tankları
5. **Personeller** - Personel bilgileri
6. **Sistem Kullanıcıları** - Kullanıcı hesabı yönetimi
7. **Müşteriler** - Müşteri bilgileri
8. **Esanslar** - Esans tanımları
9. **Gider Yönetimi** - Gider takibi
10. **Tedarikçiler** - Tedarikçi bilgileri
11. **İş Merkezleri** - Üretim hatları
12. **Müşteri Geri Bildirimleri** - Müşteri memnuniyeti
13. **Esans İş Emirleri** - Esans üretim emirleri
14. **Montaj İş Emirleri** - Ürün montaj emirleri
15. **Stok Hareketleri** - Stok giriş/çıkış kayıtları
16. **Siparişler** - Müşteri sipariş yönetimi
17. **Çerçeve Sözleşmeler** - Tedarikçi sözleşmeleri
18. **Giriş Kalite Kontrolü** - Malzeme kalite kontrolleri
19. **Manuel Stok Hareketleri** - El ile stok işlemleri

## Güvenlik

- Kullanıcı şifreleri güvenli şekilde hash'lenmiştir
- Oturum yönetimi ile kullanıcı izleme
- Rol bazlı erişim kontrolü

## Lisans

Bu proje eğitim amaçlı geliştirilmiştir.
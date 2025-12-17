Bu kılavuzda bahsedilen 4 tablodaki veriler temizlenerek başlanır. Tablo isimleri: net_urun_ihtiyaclari, malzeme_ihtiyaclari, esans_ihtiyaclari, net_esans_ihtiyaclari

# Aksiyon Planı

Bu belge, proje yönetimi ve geliştirme süreciyle ilgili aksiyon planlarını ve açıklamaları içerir. Burada birlikte çalışarak detayları belirleyeceğiz.

## Başlangıç Açıklaması
- Proje kapsamı ve hedefleri belirlenir.
- Görevler ve sorumluluklar atanır.
- Zaman çizelgesi oluşturulur.

Daha fazla detay eklenecek.

## Net Ürün İhtiyaç Tespiti

Bu özellik, sistemde bulunan tüm ürünlerin stok durumunu analiz ederek gerçek ihtiyaçları belirlemek için kullanılır. Hesaplama süreci aşağıdaki adımlarla gerçekleştirilir:

### 1. Temel Hesaplama Formülü
Her ürün için aşağıdaki formül uygulanır:
```
Net İhtiyaç = Stok Miktarı - Kritik Stok Seviyesi - Müşteri Siparişi Miktarı
```

- **Pozitif Sonuç**: Stok yeterli, sorun yok.
- **Negatif Sonuç**: Belirtilen miktarda net ihtiyaç var (mutlak değer alınır).

### 2. Montaj İş Emri Düşümü
Net ihtiyaç hesaplandıktan sonra, ilgili ürüne ait aktif montaj iş emirleri kontrol edilir. Üretim aşamasındaki iş emirlerinin miktarı net ihtiyaçtan düşülür:

- Montaj iş emirleri tablosundan (`montaj_is_emirleri`) durum 'uretimde' olan kayıtlar aranır.
- Üretimdeki miktar = Planlanan Miktar (çünkü üretimde olan iş emri miktarı planlanan miktardır)
- Gerçek İhtiyaç = Net İhtiyaç - Üretimdeki Miktar

Bu yaklaşım, halihazırda üretimde olan ürünleri tekrar sipariş etmeyi önler ve iş emirlerinin takip edilmesini sağlar.

### 3. Veritabanı Tabloları ve Yapısı

#### Ürünler Tablosu (`urunler`)
- `urun_kodu` (int, primary key): Ürün kodu
- `urun_ismi` (varchar): Ürün adı
- `stok_miktari` (int): Mevcut stok miktarı
- `kritik_stok_seviyesi` (int): Kritik stok seviyesi
- `birim` (varchar): Ölçü birimi (adet, kg, lt vb.)

#### Sipariş Kalemleri Tablosu (`siparis_kalemleri`)
- `siparis_id` (int): Sipariş ID'si
- `urun_kodu` (int): Ürün kodu
- `adet` (int): Sipariş edilen miktar
- `birim` (varchar): Ölçü birimi

Müşteri siparişi miktarı için: `SUM(adet) WHERE urun_kodu = ?` sorgusu kullanılır.

#### Montaj İş Emirleri Tablosu (`montaj_is_emirleri`)
- `is_emri_numarasi` (int, primary key): İş emri numarası
- `urun_kodu` (varchar): Ürün kodu (not: varchar, ürün kodu string olabilir)
- `planlanan_miktar` (decimal): Planlanan üretim miktarı
- `tamamlanan_miktar` (decimal): Tamamlanan miktar
- `durum` (varchar): İş emri durumu ('olusturuldu', 'uretimde', 'tamamlandi' vb.)
- `is_merkezi_id` (int): İş merkezi ID'si

Üretimdeki miktar için: `SUM(planlanan_miktar) WHERE urun_kodu = ? AND durum = 'uretimde'`

### 4. Net Ürün İhtiyaçları Tablosu
Hesaplanan gerçek ihtiyaçlar `net_urun_ihtiyaclari` tablosuna kaydedilir. (Bu tablo henüz mevcut değilse oluşturulacaktır.)

Önerilen tablo yapısı:
- `ihtiyac_id` (int, primary key, auto_increment)
- `urun_kodu` (int/varchar): Ürün kodu
- `urun_ismi` (varchar): Ürün adı
- `stok_miktari` (int): Mevcut stok
- `kritik_stok` (int): Kritik stok seviyesi
- `siparis_miktari` (int): Bekleyen sipariş miktarı
- `net_ihtiyac` (decimal): Hesaplanan net ihtiyaç
- `uretimdeki_miktar` (decimal): Üretimdeki miktar
- `gercek_ihtiyac` (decimal): Nihai gerçek ihtiyaç
- `hesaplama_tarihi` (datetime): Hesaplama tarihi
- `durum` (varchar): Durum ('aktif', 'kapatildi' vb.)

### 5. Uygulama Adımları
1. Tüm ürünleri `urunler` tablosundan çek.
2. Her ürün için müşteri siparişi miktarını `siparis_kalemleri` tablosundan hesapla.
3. Net ihtiyaç hesapla: `stok_miktari - kritik_stok_seviyesi - siparis_miktari`
4. Üretimdeki miktarı `montaj_is_emirleri` tablosundan hesapla.
5. Gerçek ihtiyaç: `net_ihtiyac - uretimdeki_miktar` (negatifse 0)
6. Sonuçları `net_urun_ihtiyaclari` tablosuna kaydet.
7. Raporlama ve bildirim işlemleri gerçekleştir.

## Oluşturulan Net Ürün İhtiyaçları Tablosu

Veritabanında `net_urun_ihtiyaclari` tablosu başarıyla oluşturulmuştur. Bu tablo, hesaplanan ürün ihtiyaçlarını saklamak için kullanılır ve aşağıdaki alanlardan oluşur:

### Tablo Yapısı
- `ihtiyac_id` (int, primary key, auto_increment): İhtiyaç kaydının benzersiz kimliği
- `urun_kodu` (int, not null): İlgili ürünün kodu (urunler tablosundan referans)
- `urun_ismi` (varchar(255), not null): Ürün adı
- `stok_miktari` (int, default 0): Hesaplama anındaki mevcut stok miktarı
- `kritik_stok` (int, default 0): Ürünün kritik stok seviyesi
- `siparis_miktari` (int, default 0): Bekleyen müşteri siparişlerinin toplam miktarı
- `net_ihtiyac` (decimal(10,2), default 0.00): Temel formülle hesaplanan net ihtiyaç (stok - kritik - sipariş)
- `uretimdeki_miktar` (decimal(10,2), default 0.00): Üretim aşamasındaki montaj iş emirlerinin toplam planlanan miktarı
- `gercek_ihtiyac` (decimal(10,2), default 0.00): Nihai gerçek ihtiyaç miktarı (net_ihtiyac - uretimdeki_miktar)
- `hesaplama_tarihi` (datetime, default current_timestamp): Hesaplamanın yapıldığı tarih ve saat
- `durum` (varchar(50), default 'aktif'): Kaydın durumu ('aktif', 'kapatildi', vb.)

### Kullanım Amacı
Bu tablo, stok yönetimi ve üretim planlaması için kritik verileri depolar:
- Gerçek ihtiyaç miktarları üzerinden üretim veya tedarik kararları alınabilir
- Tarihsel verilerle stok trendleri analiz edilebilir
- Durum alanı ile aktif ihtiyaçlar takip edilebilir
- Hesaplama tarihleri ile periyodik güncellemeler yapılabilir

Tablo, MRP (Material Requirements Planning) sistemiyle entegre çalışarak stok optimizasyonuna katkı sağlar.

## Malzeme İhtiyaçları Tespiti

Net ürün ihtiyaçları belirlendikten sonra, sistem ürün ağaçlarını kullanarak gerekli malzeme ihtiyaçlarını hesaplar. Bu süreç, üretim için gereken hammaddelerin direk olarak belirlenmesini sağlar (stok kontrolü olmadan).

### Hesaplama Mantığı
1. `net_urun_ihtiyaclari` tablosundaki aktif kayıtlar alınır (durum = 'aktif').
2. Her ürün için `urun_agaci` tablosundan ilgili bileşenler çekilir:
   - `agac_turu` = 'urun' olan kayıtlar
   - `urun_kodu` eşleşen kayıtlar
3. Bileşenlerden `bilesenin_malzeme_turu` != 'esans' olanlar seçilir (malzemeler).
4. Gerekli miktar: `gercek_ihtiyac` × `bilesen_miktari`
5. Sonuçlar direk olarak `malzeme_ihtiyaclari` tablosuna kaydedilir.

### İlgili Tablolar

#### Ürün Ağacı Tablosu (`urun_agaci`)
- `urun_agaci_id` (int, primary key): Ağaç kaydının kimliği
- `urun_kodu` (int): Ana ürün kodu
- `urun_ismi` (varchar): Ana ürün adı
- `bilesenin_malzeme_turu` (varchar): Bileşen türü ('esans', 'malzeme' vb.)
- `bilesen_kodu` (varchar): Bileşen kodu
- `bilesen_ismi` (varchar): Bileşen adı
- `bilesen_miktari` (decimal): Birim ürün için gereken bileşen miktarı
- `agac_turu` (varchar, default 'urun'): Ağaç türü

#### Malzeme İhtiyaçları Tablosu (`malzeme_ihtiyaclari`)
Bu tablo, hesaplanan malzeme ihtiyaçlarını saklar:
- `ihtiyac_id` (int, primary key, auto_increment): İhtiyaç kaydının kimliği
- `urun_kodu` (int, not null): İlgili ürünün kodu
- `bilesen_kodu` (varchar(50), not null): Bileşen (malzeme) kodu
- `bilesen_ismi` (varchar(255), not null): Bileşen adı
- `gereken_miktar` (decimal(10,2), not null): Toplam gereken miktar (gercek_ihtiyac × bilesen_miktari)
- `hesaplama_tarihi` (datetime, default current_timestamp): Hesaplama tarihi
- `durum` (varchar(50), default 'aktif'): Kayıt durumu

### Uygulama Adımları
1. `net_urun_ihtiyaclari` tablosundan aktif ürün ihtiyaçlarını çek.
2. Her ürün için `urun_agaci` tablosundan malzeme bileşenlerini al (esans hariç).
3. Gerekli miktarı hesapla: `gercek_ihtiyac * bilesen_miktari`.
4. Sonuçları `malzeme_ihtiyaclari` tablosuna kaydet.
5. Tedarik ve sipariş süreçleri için raporlar oluştur.

Bu yaklaşım, üretim planlamasında hızlı karar verme sağlar ve malzemelerin zamanında temin edilmesini kolaylaştırır.

## Esans İhtiyaçları Tespiti

Net ürün ihtiyaçları belirlendikten sonra, sistem ürün ağaçlarını kullanarak gerekli esans ihtiyaçlarını hesaplar. Bu süreç, üretim için gereken esansların direk olarak belirlenmesini sağlar.

### Hesaplama Mantığı
1. `net_urun_ihtiyaclari` tablosundaki aktif kayıtlar alınır (durum = 'aktif').
2. Her ürün için `urun_agaci` tablosundan ilgili bileşenler çekilir:
   - `agac_turu` = 'urun' olan kayıtlar
   - `urun_kodu` eşleşen kayıtlar
3. Bileşenlerden `bilesenin_malzeme_turu` = 'esans' olanlar seçilir.
4. Gerekli miktar: `gercek_ihtiyac` × `bilesen_miktari`
5. Sonuçlar direk olarak `esans_ihtiyaclari` tablosuna kaydedilir.

### İlgili Tablolar

#### Ürün Ağacı Tablosu (`urun_agaci`)
- `urun_agaci_id` (int, primary key): Ağaç kaydının kimliği
- `urun_kodu` (int): Ana ürün kodu
- `urun_ismi` (varchar): Ana ürün adı
- `bilesenin_malzeme_turu` (varchar): Bileşen türü ('esans', 'malzeme' vb.)
- `bilesen_kodu` (varchar): Bileşen kodu
- `bilesen_ismi` (varchar): Bileşen adı
- `bilesen_miktari` (decimal): Birim ürün için gereken bileşen miktarı
- `agac_turu` (varchar, default 'urun'): Ağaç türü

#### Esans İhtiyaçları Tablosu (`esans_ihtiyaclari`)
Bu tablo, hesaplanan esans ihtiyaçlarını saklar:
- `ihtiyac_id` (int, primary key, auto_increment): İhtiyaç kaydının kimliği
- `urun_kodu` (int, not null): İlgili ürünün kodu
- `bilesen_kodu` (varchar(50), not null): Bileşen (esans) kodu
- `bilesen_ismi` (varchar(255), not null): Bileşen adı
- `gereken_miktar` (decimal(10,2), not null): Toplam gereken miktar (gercek_ihtiyac × bilesen_miktari)
- `hesaplama_tarihi` (datetime, default current_timestamp): Hesaplama tarihi
- `durum` (varchar(50), default 'aktif'): Kayıt durumu

### Uygulama Adımları
1. `net_urun_ihtiyaclari` tablosundan aktif ürün ihtiyaçlarını çek.
2. Her ürün için `urun_agaci` tablosundan esans bileşenlerini al.
3. Gerekli miktarı hesapla: `gercek_ihtiyac * bilesen_miktari`.
4. Sonuçları `esans_ihtiyaclari` tablosuna kaydet.
5. Üretim ve tedarik süreçleri için raporlar oluştur.

Bu yaklaşım, esans temininde zamanında planlama sağlar ve üretim kalitesini garanti eder.

## Net Esans İhtiyaç Tespiti

Esans ihtiyaçları belirlendikten sonra, sistem her esans için stok durumunu kontrol ederek gerçek net ihtiyacı hesaplar. Bu süreç, ürün ihtiyaçlarından türetilen esans taleplerini dikkate alarak stok optimizasyonu sağlar.

### Hesaplama Mantığı
1. `esans_ihtiyaclari` tablosundaki aktif kayıtlar alınır (durum = 'aktif').
2. Benzersiz esans kodları belirlenir.
3. Her esans için aşağıdaki hesaplamalar yapılır:
   - Stok miktarı: `esanslar` tablosundan alınır.
   - Kritik stok seviyesi: 0 (varsayılan, esanslar tablosunda kritik seviye alanı yok).
   - Bekleyen sipariş miktarı: `esans_ihtiyaclari` tablosundan `SUM(gereken_miktar) WHERE bilesen_kodu = esans_kodu`.
   - Üretimdeki miktar: `esans_is_emirleri` tablosundan `SUM(planlanan_miktar) WHERE esans_kodu = ? AND durum = 'uretimde'`.
4. Net ihtiyaç: `stok_miktari - kritik_stok - siparis_miktari`
5. Gerçek ihtiyaç: `net_ihtiyac - uretimdeki_miktar` (negatifse 0)
6. Sonuçlar `net_esans_ihtiyaclari` tablosuna kaydedilir.

### İlgili Tablolar

#### Esanslar Tablosu (`esanslar`)
- `esans_id` (int, primary key): Esans kimliği
- `esans_kodu` (varchar(50), unique): Esans kodu
- `esans_ismi` (varchar(255)): Esans adı
- `stok_miktari` (decimal(10,2)): Mevcut stok miktarı
- `birim` (varchar): Ölçü birimi

#### Esans İş Emirleri Tablosu (`esans_is_emirleri`)
- `is_emri_numarasi` (int, primary key): İş emri numarası
- `esans_kodu` (varchar(50)): Esans kodu
- `esans_ismi` (varchar(255)): Esans adı
- `planlanan_miktar` (decimal(10,2)): Planlanan üretim miktarı
- `durum` (varchar): İş emri durumu
- `tamamlanan_miktar` (decimal(10,2)): Tamamlanan miktar

Üretimdeki miktar için: `SUM(planlanan_miktar) WHERE esans_kodu = ? AND durum = 'uretimde'`

#### Net Esans İhtiyaçları Tablosu (`net_esans_ihtiyaclari`)
Bu tablo, hesaplanan net esans ihtiyaçlarını saklar:
- `ihtiyac_id` (int, primary key, auto_increment): İhtiyaç kaydının kimliği
- `esans_kodu` (varchar(50), not null): Esans kodu
- `esans_ismi` (varchar(255), not null): Esans adı
- `stok_miktari` (decimal(10,2), default 0.00): Mevcut stok miktarı
- `kritik_stok` (decimal(10,2), default 0.00): Kritik stok seviyesi (varsayılan 0)
- `siparis_miktari` (decimal(10,2), default 0.00): Bekleyen sipariş/ihiyaç miktarı
- `net_ihtiyac` (decimal(10,2), default 0.00): Hesaplanan net ihtiyaç
- `uretimdeki_miktar` (decimal(10,2), default 0.00): Üretim aşamasındaki miktar
- `gercek_ihtiyac` (decimal(10,2), default 0.00): Nihai gerçek ihtiyaç
- `hesaplama_tarihi` (datetime, default current_timestamp): Hesaplama tarihi
- `durum` (varchar(50), default 'aktif'): Kayıt durumu

### Uygulama Adımları
1. `esans_ihtiyaclari` tablosundan aktif esans ihtiyaçlarını çek ve benzersiz esansları belirle.
2. Her esans için stok bilgilerini `esanslar` tablosundan al.
3. Bekleyen ihtiyaç miktarını `esans_ihtiyaclari` tablosundan hesapla.
4. Üretimdeki miktarı `esans_is_emirleri` tablosundan hesapla.
5. Net ve gerçek ihtiyaçları hesapla.
6. Sonuçları `net_esans_ihtiyaclari` tablosuna kaydet.
7. Esans temini ve üretim planlaması için raporlar oluştur.

Bu yaklaşım, esans stoklarının üretim ihtiyaçlarına göre dengelenmesini sağlar ve gereksiz stok birikimini önler.

## Net Esanslardan Malzeme İhtiyaçları Tespiti

Net esans ihtiyaçları belirlendikten sonra, sistem esansların kendi malzemelerini hesaplar. Esanslar da malzemelerden üretildiği için, esans üretiminde gereken hammaddeler belirlenir ve `malzeme_ihtiyaclari` tablosuna eklenir.

### Hesaplama Mantığı
1. `net_esans_ihtiyaclari` tablosundaki aktif kayıtlar alınır (durum = 'aktif').
2. Her esans için `urun_agaci` tablosundan ilgili bileşenler çekilir:
   - `agac_turu` = 'esans' olan kayıtlar
   - `urun_kodu` = esans_id (int, esanslar tablosundaki esans_id ile eşleşir)
3. Bileşenlerden `bilesenin_malzeme_turu` != 'esans' olanlar seçilir (malzemeler, çünkü esanslar alt bileşen olarak esans içermez).
4. Gerekli miktar: `gercek_ihtiyac` × `bilesen_miktari`
5. Sonuçlar `malzeme_ihtiyaclari` tablosuna kaydedilir (urun_kodu yerine esans_kodu, urun_ismi yerine esans_ismi olarak işaretlenir).

### İlgili Tablolar

#### Ürün Ağacı Tablosu (`urun_agaci`)
- `urun_agaci_id` (int, primary key): Ağaç kaydının kimliği
- `urun_kodu` (int): Ana ürün/esans kodu (esans için esans_id ile eşleşir)
- `urun_ismi` (varchar): Ana ürün/esans adı
- `bilesenin_malzeme_turu` (varchar): Bileşen türü ('esans', 'malzeme' vb.)
- `bilesen_kodu` (varchar): Bileşen kodu
- `bilesen_ismi` (varchar): Bileşen adı
- `bilesen_miktari` (decimal): Birim için gereken bileşen miktarı
- `agac_turu` (varchar, default 'urun'): Ağaç türü ('urun' veya 'esans')

Esans malzemeleri için: `agac_turu = 'esans'` ve `urun_kodu = esans_id`

#### Malzeme İhtiyaçları Tablosu (`malzeme_ihtiyaclari`)
Bu tablo, hem ürünlerden hem esanslardan gelen malzeme ihtiyaçlarını birleştirir:
- `ihtiyac_id` (int, primary key, auto_increment): İhtiyaç kaydının kimliği
- `urun_kodu` (int, not null): İlgili ürün/esans kodu (ürün için urun_kodu, esans için esans_kodu olarak kaydedilir)
- `bilesen_kodu` (varchar(50), not null): Bileşen (malzeme) kodu
- `bilesen_ismi` (varchar(255), not null): Bileşen adı
- `gereken_miktar` (decimal(10,2), not null): Toplam gereken miktar
- `hesaplama_tarihi` (datetime, default current_timestamp): Hesaplama tarihi
- `durum` (varchar(50), default 'aktif'): Kayıt durumu

### Uygulama Adımları
1. `net_esans_ihtiyaclari` tablosundan aktif esans ihtiyaçlarını çek.
2. Her esans için `urun_agaci` tablosundan malzeme bileşenlerini al (`agac_turu = 'esans'`).
3. Gerekli miktarı hesapla: `gercek_ihtiyac * bilesen_miktari`.
4. Sonuçları `malzeme_ihtiyaclari` tablosuna kaydet (kaynak esans olarak işaretle).
5. Malzeme temini ve üretim planlaması için raporlar oluştur.

Bu yaklaşım, tedarik zincirinde esans üretiminin de malzemelerini kapsar ve tam malzeme görünürlüğü sağlar.
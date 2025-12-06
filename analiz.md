# Sipariş ve Üretim Analizi

## Net Kılavuz: Yapılacak İşlemler (Sıralı Adımlar)

**Not**: Bu plan, siparişler karşılandıktan sonra ürünlerin kritik stok seviyesinde kalmasını sağlar. Kritik stok seviyeleri: 30 (400), 47 (25), 60 (20), 61 (15), 67 (10).

### Adım 1: Eksik Malzemeler İçin Yeni Sipariş Ver (Paralel)
Toplam ihtiyaçlara göre yeni siparişler ver (mevcut siparişleri beklemeden):
- **Malzeme 48 (Esans Hammaddesi)**: 27782 birim (toplam ihtiyaç 27924 - mevcut sipariş 22 - stok 120 = 27782).
- **Malzeme 37 (Ambalaj Malzemesi 2 - Etiket)**: 1692 birim (toplam ihtiyaç 1970 - mevcut sipariş 123 - stok 155 = 1692).

### Adım 2: Mevcut Siparişlerin Teslimini Bekle
- Malzeme 48 (Esans Hammaddesi): Mevcut sipariş 22 birim, teslim tarihi 2025-12-21.
- Malzeme 37 (Ambalaj Malzemesi 2 - Etiket): Mevcut sipariş 123 birim, teslim tarihi 2026-01-04.

### Adım 3: Mevcut İş Emirlerini Tamamla
- **Esans İş Emri No: 4**: 400 birim ES010 (Bergamot Essence - Bergamot Esansı) üretimi tamamlanmalı.
- **Montaj İş Emri No: 1**: 430 adet Ürün 30 (10 evo) montajı tamamlanmalı.

### Adım 4: Yeni Siparişlerin Teslimini Bekle
Yeni verilen siparişlerin teslim tarihlerini kontrol et ve malzemeler ulaştıktan sonra devam et.

### Adım 5: Ek Üretim İş Emirleri Aç
Yeni malzemeler ulaştıktan sonra, eksik kalan ihtiyaçlar için yeni iş emirleri aç:
- **Esans İş Emri**: ES010 (Bergamot Essence - Bergamot Esansı) için 9308 birim.
- **Montaj İş Emri**: Ürün 30 (10 evo) için 985 adet.

**Uyarı**: Adım 1 paralel olarak başlatılabilir. Diğer adımlar sıralı olmalı. Malzeme teslim tarihleri kritik.

## Bekleyen ve Onaylanmış Siparişler

Aşağıdaki tabloda bekleyen ve onaylanmış siparişlerde bulunan ürünler ve toplam adetler listelenmiştir:

| Durum      | Ürün Kodu | Ürün İsmi         | Toplam Adet |
|------------|-----------|-------------------|-------------|
| beklemede | 30       | 10 evo           | 1105       |
| beklemede | 47       | Deniz Esintisi   | 1          |
| beklemede | 60       | Çilek ve Vanilya | 3          |
| beklemede | 61       | Ağustos Böceği   | 22         |
| beklemede | 67       | Beyaz Çikolata   | 1          |
| onaylandi | 30       | 10 evo           | 1          |
| onaylandi | 47       | Deniz Esintisi   | 1          |
| onaylandi | 60       | Çilek ve Vanilya | 1          |
| onaylandi | 61       | Ağustos Böceği   | 1          |
| onaylandi | 67       | Beyaz Çikolata   | 1          |

## Stok Durumları ve Kritik Seviyeler

Ürünlerin mevcut stok miktarları ve kritik stok seviyeleri:

| Ürün Kodu | Ürün İsmi         | Stok Miktarı | Kritik Seviye | Toplam Sipariş | Üretilecek Adet |
|-----------|-------------------|--------------|---------------|----------------|-----------------|
| 30       | 10 evo           | 91          | 400          | 1106          | 985            |
| 47       | Deniz Esintisi   | 160         | 25           | 2             | 0              |
| 60       | Çilek ve Vanilya | 155         | 20           | 4             | 0              |
| 61       | Ağustos Böceği   | 74          | 15           | 23            | 0              |
| 67       | Beyaz Çikolata   | 90          | 10           | 2             | 0              |

**Hesaplama Notu**: Üretilecek adet = toplam sipariş + kritik seviye - mevcut stok (sipariş sonrası kalan stok kritik seviye olsun diye).

## Üretim İhtiyaçları

Siparişler karşılandıktan sonra kritik stok seviyesinin altında kalmaması için üretim hesaplanmıştır.

### Ürün 30 (10 evo)
- Toplam Sipariş Adeti: 1106
- Mevcut Stok: 91
- Mevcut İş Emirlerinden Gelecek: 430 adet (iş emri no 1)
- Kritik Stok Seviyesi: 400
- Üretilecek Adet: 985 (sipariş sonrası kalan stok kritik seviye olsun diye)

#### Bileşen İhtiyaçları
- **Esans ES010 (Bergamot Essence)**: 9850 birim (1 ürün için 10 birim)
  - Mevcut Stok: 142
  - Mevcut İş Emirlerinden Gelecek: 400 birim (iş emri no 4)
  - Üretilecek Esans: 9308 birim

- **Etiket 37 (Ambalaj Malzemesi 2)**: 1970 birim (1 ürün için 2 birim)
  - Mevcut Stok: 155
  - Satın Alınacak/Sipariş Edilecek: 1692 birim

#### Esans Üretimi İçin Malzeme İhtiyaçları
- **Malzeme 48 (Esans Hammaddesi)**: 27924 birim (1 esans için 3 birim, 9308 * 3)
  - Mevcut Stok: 120
  - Satın Alınacak/Sipariş Edilecek: 27782 birim

### Diğer Ürünler
Ürünler 47, 60, 61 ve 67 için stok yeterli olduğundan üretim ihtiyacı yoktur.

## Mevcut Üretim İş Emirleri

### Esans Üretim İş Emirleri (Üretimde Olan)
- **İş Emri No: 4**
  - Esans: ES010 (Bergamot Essence)
  - Planlanan Miktar: 400 birim
  - Tamamlanan Miktar: 0 birim
  - Durum: Üretimde
  - Kullanılan Malzemeler:
    - Malzeme 31 (Medium Box): 400 birim
    - Malzeme 37 (Ambalaj Malzemesi 2): 400 birim

### Montaj Üretim İş Emirleri (Üretimde Olan)
- **İş Emri No: 1**
  - Ürün: 30 (10 evo)
  - Planlanan Miktar: 430 adet
  - Tamamlanan Miktar: 0 adet
  - Durum: Üretimde

## Malzeme Sipariş Durumları

Eksik malzemeler için mevcut siparişler:

- **Malzeme 37 (Ambalaj Malzemesi 2)**:
  - Sipariş Miktarı: 123 birim
  - Durum: Sipariş Verildi
  - Teslim Tarihi: 2026-01-04
  - **Not:** İhtiyaç 1970 birim, mevcut sipariş yetersiz.

- **Malzeme 48 (Esans Hammaddesi)**:
  - Sipariş Miktarı: 22 birim
  - Durum: Sipariş Verildi
  - Teslim Tarihi: 2025-12-21
  - **Not:** İhtiyaç 27924 birim, mevcut sipariş yetersiz.

## Üretim Kılavuzu

### Mevcut İş Emirleri Tamamlanması
- **Esans İş Emri No: 4**: 400 birim ES010 esansı üretimi tamamlanmalı.
- **Montaj İş Emri No: 1**: 430 adet Ürün 30 montajı tamamlanmalı.

### Ek Üretim İş Emirleri Açılması
Mevcut iş emirleri tamamlandıktan sonra, eksik kalan ihtiyaçlar için yeni iş emirleri açılmalı:

- **Esans Üretimi**: ES010 için 9308 birim (mevcut iş emri 400 birim tamamlandıktan sonra).
  - Yeni esans iş emri açılmalı.
- **Ürün Montajı**: Ürün 30 için 985 adet (mevcut iş emri 430 adet tamamlandıktan sonra).
  - Yeni montaj iş emri açılmalı.

### Malzeme Siparişleri
- Mevcut siparişler yetersiz, aşağıdaki ek siparişler verilmeli:
  - Malzeme 48 (Esans Hammaddesi): 27782 birim (mevcut sipariş 22 birim yetersiz).
  - Malzeme 37 (Ambalaj Malzemesi 2): 1692 birim (mevcut sipariş 123 birim yetersiz).

### Özet
- Kritik stok seviyelerine göre üretim planlandı.
- Toplam üretim ihtiyacı: Ürün 30 için 985 adet (sipariş sonrası kritik seviye için).
- Esans üretimi: ES010 için 9308 birim.
- Mevcut iş emirleri tamamlanmalı, ardından eksik kalan için yeni iş emirleri açılmalı.
- Malzeme siparişleri artırılmalı veya yeni siparişler verilmeli.

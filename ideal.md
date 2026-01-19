# İdeal Ürün Tanımı

Bu belge, Parfüm ERP sisteminde **ideal bir ürün** olarak kabul edilebilmesi için bir ürünün sahip olması gereken tüm özellikleri ve koşulları açıklamaktadır.

> [!IMPORTANT]
> Kokpit sayfası, ürünlerin durumunu analiz ederken aşağıdaki kontrolleri sırasıyla yapar. Bir ürün ancak **tüm** bu kontrolleri geçtiğinde "Her şey yolunda" ✅ statüsü alır.

---

## 1. Ürün Tanımı

| Alan                       | Açıklama                                          | Zorunlu |
| -------------------------- | ------------------------------------------------- | ------- |
| `urun_kodu`                | Benzersiz ürün kodu (otomatik verilir)            | ✅      |
| `urun_ismi`                | Ürünün açıklayıcı adı                             | ✅      |
| `stok_miktari`             | Mevcut stok miktarı                               | ✅      |
| `kritik_stok_seviyesi`     | Stok bu seviyenin altına düştüğünde uyarı verilir | ✅      |
| `birim`                    | Ölçü birimi (adet, kg, gr, lt, ml, mt)            | ✅      |
| `satis_fiyati`             | Satış fiyatı                                      | ✅      |
| `satis_fiyati_para_birimi` | Para birimi (TRY, USD, EUR)                       | ✅      |
| `depo`                     | Ürünün bulunduğu depo                             | ✅      |
| `raf`                      | Depodaki raf konumu                               | ✅      |
| `urun_tipi`                | `uretilen` veya `hazir_alinan`                    | ✅      |
| `not_bilgisi`              | İsteğe bağlı notlar                               | ❌      |

---

## 2. Ürün Ağacı (BOM - Bill of Materials)

İdeal bir **üretilen** ürün için aşağıdaki **6 bileşen türü** mutlaka tanımlı olmalıdır:

| Bileşen Türü | Açıklama                      |
| ------------ | ----------------------------- |
| **Kutu**     | Ürünün ambalaj kutusu         |
| **Takım**    | Spreyli kapak + şişe seti vb. |
| **Etiket**   | Ürün etiketi                  |
| **Paket**    | Dış paketleme malzemesi       |
| **Jelatin**  | Koruyucu jelatin ambalaj      |
| **Esans**    | Parfüm esansı                 |

> [!WARNING]
> Eksik bileşen türü varsa:
>
> - Kokpit'te **"Ürün Ağacı ve Esans Formüllerini Tamamlayın"** uyarısı görünür
> - Üretim yapılamaz
> - Maliyet hesaplaması eksik kalır

### Ürün Ağacı Tanımlama

1. **Malzemeleri Tanımla** → `malzemeler.php`
2. **Esansı Tanımla** → `esanslar.php`
3. **Ürün Ağacını Oluştur** → `urun_agaclari.php`
   - Her bileşen için **doğru miktar** girilmeli
   - 1 adet ürün için gereken gerçek miktarlar yazılmalı

---

## 3. Esans ve Esans Formülü

Esans, diğer bileşenlerden (kutu, etiket vb.) farklıdır: **hazır alınmaz, üretilir**.

### Esans Üretim Hiyerarşisi

```
ÜRÜN
└── Ürün Ağacı (agac_turu = 'urun')
    └── Esans bileşeni (örn: 50 ml)
        │
        └── ESANS
            └── Esans Ağacı (agac_turu = 'esans')
                ├── Hammadde 1 (örn: Gül Yağı)
                ├── Hammadde 2 (örn: Misk)
                └── Hammadde 3 (örn: Etil Alkol)
```

### Süreç

1. **Hammaddeler** tedarikçiden alınır (malzemeler tablosu)
2. **Esans İş Emri** açılır → hammaddeler karıştırılarak esans üretilir
3. Üretilen esans **tank**'ta depolanır ve demlenmeye bırakılır
4. **Montaj İş Emri** açılır → esans + diğer bileşenler ile ürün üretilir

### Esans İçin Gereksinimler

| Gereksinim      | Açıklama                                                            |
| --------------- | ------------------------------------------------------------------- |
| Esans tanımlı   | `esanslar.php` sayfasında esans kaydı var                           |
| Formül tanımlı  | `urun_agaclari.php` → Esans Ağaçları sekmesinde hammaddeler tanımlı |
| Tank atanmış    | Esansa bir tank atanmış olmalı                                      |
| Demlenme süresi | Esansın demlenme süresi (gün) belirlenmiş                           |

> [!CAUTION]
> Esans formülü eksikse:
>
> - Esans üretimi yapılamaz
> - Esans maliyeti hesaplanamaz
> - Ana ürün için **"Formülü Olmayan Esanslar"** uyarısı görünür

---

## 4. Çerçeve Sözleşmeler

Her malzeme için geçerli bir **çerçeve sözleşmesi** olmalıdır.

### Sözleşme Gereksinimleri

| Alan               | Açıklama                                              |
| ------------------ | ----------------------------------------------------- |
| `tedarikci_id`     | Tedarikçi belirlenmiş                                 |
| `malzeme_kodu`     | Hangi malzeme için                                    |
| `birim_fiyat`      | Anlaşılan fiyat                                       |
| `para_birimi`      | Fiyat para birimi                                     |
| `limit_miktar`     | Sözleşme limiti (toplam sipariş verilebilecek miktar) |
| `baslangic_tarihi` | Sözleşme başlangıç tarihi                             |
| `bitis_tarihi`     | Sözleşme bitiş tarihi (isteğe bağlı)                  |
| `oncelik`          | Öncelik sırası (1-5)                                  |

### Geçerlilik Koşulları

Bir sözleşmenin **kullanılabilir** olması için:

1. ✅ Başlangıç tarihi geçmiş olmalı
2. ✅ Bitiş tarihi henüz gelmemiş olmalı (veya bitiş tarihi yok)
3. ✅ Kalan miktar > 0 olmalı

> [!WARNING]
> Sözleşme eksikse:
>
> - Satınalma siparişi verilemez
> - Kokpit'te **"Sözleşmesi Olmayan Malzemeler"** uyarısı görünür

---

## 5. Kritik Stok Yönetimi

### İdeal Stok Durumu

```
stok_miktari > kritik_stok_seviyesi
```

### Açık (Karşılanması Gereken Miktar) Hesabı

```
Sipariş İçin Gereken = max(0, siparis_miktari - stok)
Kritik İçin Gereken = max(0, kritik_seviye - (stok_sonrasi + uretimde))
Toplam Açık = Sipariş İçin Gereken + Kritik İçin Gereken
```

| Durum                                | Değerlendirme         |
| ------------------------------------ | --------------------- |
| `acik = 0`                           | ✅ Stok yeterli       |
| `acik > 0` ve `uretilebilir >= acik` | 🔵 Üretim yapılabilir |
| `acik > 0` ve `uretilebilir < acik`  | 🔴 Malzeme yetersiz   |

---

## 6. Üretilebilirlik Kontrolü

Bir ürünün kaç adet üretilebileceği, **kritik bileşenlerin** stoklarına göre hesaplanır.

### Kritik Bileşenler

- Kutu
- Takım
- Esans

```
uretilebilir_miktar = MIN(
    kutu_stok / kutu_gerekli,
    takim_stok / takim_gerekli,
    esans_stok / esans_gerekli
)
```

### Her Bileşen İçin Kontrol

Sistem, her bileşen için ayrıca şunları kontrol eder:

| Kontrol        | Açıklama                                   |
| -------------- | ------------------------------------------ |
| `mevcut_stok`  | Bileşenin mevcut stok miktarı              |
| `yoldaki_stok` | Bekleyen satınalma siparişlerindeki miktar |
| `sozlesme_var` | Bileşen için geçerli sözleşme var mı       |

---

## 7. Aksiyon Önerileri Hiyerarşisi

Kokpit sayfası, ürünler için aşağıdaki öncelik sırasına göre aksiyon önerir:

| Öncelik | Durum                               | Aksiyon                           |
| ------- | ----------------------------------- | --------------------------------- |
| 1       | Ürün ağacı veya esans formülü eksik | 🔴 Ürün Ağacını Tamamlayın        |
| 2       | Sözleşme eksik                      | 🟠 Çerçeve Sözleşme Oluşturun     |
| 3       | Malzeme siparişi gerekli            | 🔵 Satınalma Siparişi Verin       |
| 4       | Esans hammaddesi eksik              | 🟡 Esans Hammaddesi Sipariş Verin |
| 5       | Esans üretimi gerekli               | 🔵 Esans İş Emri Oluşturun        |
| 6       | Tüm malzemeler hazır                | 🔵 Montaj İş Emri Oluşturun       |
| 7       | Her şey yolunda                     | ✅ Aksiyon gerekmiyor             |

---

## 8. İdeal Ürün Kontrol Listesi

Bir ürünün **ideal** durumda olması için:

### ✅ Temel Bilgiler

- [ ] Ürün kodu ve ismi tanımlı
- [ ] Doğru birim ve fiyat girilmiş
- [ ] Depo ve raf konumu belirlenmiş
- [ ] Kritik stok seviyesi ayarlanmış (> 0)
- [ ] Ürün tipi seçilmiş (`uretilen` veya `hazir_alinan`)

### ✅ Ürün Ağacı (Üretilen Ürünler İçin)

- [ ] 6 bileşen türü tanımlı (kutu, takım, etiket, paket, jelatin, esans)
- [ ] Her bileşen için doğru miktar girilmiş
- [ ] Tüm bileşenler malzeme/esans tablosunda mevcut

### ✅ Esans Yönetimi

- [ ] Esans tanımlı
- [ ] Esans formülü oluşturulmuş (hammaddeler tanımlı)
- [ ] Tank atanmış
- [ ] Demlenme süresi belirlenmiş

### ✅ Sözleşmeler

- [ ] Tüm malzemeler için geçerli çerçeve sözleşmesi var
- [ ] Esans hammaddeleri için de sözleşmeler tanımlı
- [ ] Sözleşme tarihleri güncel

### ✅ Stok Durumu

- [ ] `stok_miktari >= kritik_stok_seviyesi`
- [ ] Veya açık olan miktar üretilebilir durumda

---

## 9. Örnek: İdeal Bir Parfüm Ürünü

```
📦 ÜRÜN: Elegant Rose 50ml
├── Ürün Kodu: URN-001
├── Stok: 150 adet
├── Kritik Seviye: 50 adet
├── Satış Fiyatı: 250 TL
├── Depo: Ana Depo > Raf: A-01
│
├── 🏗️ ÜRÜN AĞACI (1 adet için)
│   ├── 📦 Kutu (1 adet) → MAL-K001
│   ├── 🔧 Takım (1 adet) → MAL-T001 (Şişe 50ml + Sprey Kapak)
│   ├── 🏷️ Etiket (2 adet) → MAL-E001
│   ├── 📦 Paket (1 adet) → MAL-P001
│   ├── 🎁 Jelatin (1 adet) → MAL-J001
│   └── 🧪 Esans (50 ml) → ESN-001 "Rose Garden"
│
├── 🧪 ESANS FORMÜLÜ (1 litre için)
│   ├── Gül Yağı: 150 ml
│   ├── Misk: 100 ml
│   ├── Bergamot: 50 ml
│   └── Etil Alkol: 700 ml
│
└── 📋 SÖZLEŞMELER
    ├── Kutu → Tedarikçi A (0.80 USD/adet)
    ├── Takım → Tedarikçi B (1.20 USD/adet)
    ├── Gül Yağı → Tedarikçi C (450 TL/lt)
    └── ...
```

---

## Sonuç

Bir ürün bu belgedeki tüm koşulları sağladığında:

1. ✅ Kokpit'te **yeşil "Her şey yolunda"** görünür
2. ✅ Üretim yapılabilir
3. ✅ Maliyet doğru hesaplanır
4. ✅ Satınalma siparişleri verilebilir
5. ✅ Stok takibi düzgün çalışır

Eksik olan herhangi bir bileşen, sözleşme veya formül, sistemin ilgili uyarıyı göstermesine ve bazı işlemlerin yapılamamasına neden olur.

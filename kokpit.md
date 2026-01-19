# Kokpit Aksiyon Detayı Algoritması (Teknik Doküman)

Bu doküman, `kokpit.php` içerisindeki **Aksiyon Önerisi** ve **Aksiyon Detayı** kolonlarını oluşturan `getAksiyonOnerisi($p)` fonksiyonunun detaylı teknik analizidir.

## 1. Giriş Parametreleri (`$p` Dizisi)
Fonksiyon, `getSupplyChainData` fonksiyonundan gelen ve işlenmiş ürün verisini içeren `$p` dizisini parametre olarak alır.

Önemli anahtarlar:
- `acik`: Kritik stok ve siparişleri karşılamak için gereken toplam eksik miktar.
- `eksik_bilesenler`: Ürün ağacında türü eksik olan bileşenler dizisi.
- `esans_agaci_eksik`: Formülü tanımlanmamış esansların isim dizisi.
- `sozlesme_eksik_malzemeler`: Çerçeve sözleşmesi olmayan bileşenlerin isim dizisi.
- `uretilebilir_miktar`: Eldeki tüm bileşen stoklarıyla üretilebilecek maksimum ürün adedi.
- `bilesen_detaylari`: Her bileşenin stok, ihtiyaç ve yoldaki miktar detayları.
- `esans_uretim_bilgisi`: Üründeki esansların hammadde ve üretim durumu detayları.

## 2. Algoritma Akışı ve Öncelik Sırası

Fonksiyon, aşağıdaki sırayla `if-else` kontrolleri yapar. İlk eşleşen koşulda işlem durur ve sonuç döndürülür.

### Adım 1: Veri Tutarlılığı (Kritik Engel)
Sistemin çalışmasını engelleyen eksik tanımlamaları yakalar.

*   **Koşul:** `count($eksik_bilesenler) > 0` **VEYA** `count($esans_agaci_eksik) > 0`
*   **UI Çıktısı:**
    *   **Class:** `badge-aksiyon-kritik` (Kırmızı)
    *   **Mesaj:** "Ürün Ağacı ve Esans Formüllerini Tamamlayın"
    *   **Detay:** Eksik bileşen/esans listesi ve `urun_agaclari.php` veya `esanslar.php`'ye yönlendiren HTML linkleri.

### Adım 2: Çerçeve Sözleşme Kontrolü (Üretim İhtiyacı Varken)
Üretim yapılması gerekiyor (`acik > 0`) ancak tedarik için yasal altyapı (sözleşme) yok.

*   **Koşul:** `$acik > 0` **VE** `count($sozlesme_eksik) > 0`
*   **Alt Kontrol:**
    *   **Durum A:** Eğer stoklar yine de üretimi karşılıyorsa (`$uretilebilir >= $acik`):
        *   **Class:** `badge-aksiyon-uyari` (Sarı)
        *   **Mesaj:** "Gelecek Siparişler İçin Sözleşme Tamamlayın"
    *   **Durum B:** Stoklar yetersizse:
        *   **Class:** `badge-aksiyon-kritik` (Kırmızı)
        *   **Mesaj:** "Sözleşme Eksik - Önce Sözleşme Tamamlayın"
*   **Detay:** İlk 5 eksik malzemenin ismi ve `cerceve_sozlesmeler.php` linki.

### Adım 3: Malzeme Siparişi (Satınalma)
Ürün açığını kapatmak için dışarıdan alınması gereken (esans olmayan) malzemeler.

*   **Koşul:** `$acik > 0`
*   **Hesaplama (Döngü):** Her bileşen (kutu, şişe, kapak vb.) için:
    1.  `Toplam İhtiyaç = Açık Miktar * Birim Kullanım`
    2.  `Sipariş Gereken = Toplam İhtiyaç - (Mevcut Stok + Yoldaki Stok)`
    3.  Eğer `Sipariş Gereken > 0` ise listeye ekle.
*   **Kritik Kontrol:** Sadece sözleşmesi olan malzemeler bu listeye girer. Sözleşmesi yoksa Adım 2'de yakalanır.
*   **UI Çıktısı:**
    *   **Class:** `badge-aksiyon-bilgi` (Mavi)
    *   **Mesaj:** "Malzeme Siparişi Verin"
    *   **Detay:** Malzeme isimleri ve gereken adetler. `satinalma_siparisler.php` linki.

### Adım 4: Esans Hammaddesi Siparişi
Esans üretimi gerekiyor ancak esansın hammaddeleri (kimyasallar) eksik.

*   **Koşul:** `$acik > 0` (ve önceki adımlarda takılmadıysa)
*   **Hesaplama:** `esans_uretim_bilgisi` içindeki `formul_detaylari` taranır.
    1.  `Net Sipariş = (Hammadde Reçete Miktarı * Açık) - (Hammadde Stok + Bekleyen Sipariş)`
    2.  `Net Sipariş > 0` ise listeye ekle.
*   **UI Çıktısı:**
    *   **Class:** `badge-aksiyon-uyari` (Sarı/Turuncu)
    *   **Mesaj:** "Esans hammaddesi siparişi verin"
    *   **Detay:** Eksik kimyasallar ve miktarları (kg/gr). `satinalma_siparisler.php` linki.

### Adım 5: Esans Üretimi (İş Emri)
Hammaddeler stokta var, ancak esans (yarı mamul) stokta yok/yetersiz.

*   **Koşul:** `$acik > 0`
*   **Hesaplama:**
    1.  `Brüt İhtiyaç = Açık * Birim Esans Kullanımı`
    2.  `Net İhtiyaç = Brüt İhtiyaç - (Esans Stoğu + Üretimdeki Esans)`
    3.  Eğer `Net İhtiyaç > 0` **VE** `Uretilebilir Esans Miktarı > 0` (hammadde var demek).
*   **UI Çıktısı:**
    *   **Class:** `badge-aksiyon-bilgi` (Mavi)
    *   **Mesaj:** "Esans iş emri oluşturun"
    *   **Detay:** Üretilecek esans miktarı (ml). `esans_is_emirleri.php` linki.

### Adım 6: Montaj Üretimi (Son Aşama)
Tüm malzemeler (esans dahil) stokta mevcut, üretim başlatılabilir.

*   **Koşul:** `$uretilebilir_miktar >= $acik`
*   **UI Çıktısı:**
    *   **Class:** `badge-aksiyon-bilgi` (Mavi/Endüstriyel)
    *   **Mesaj:** "Montaj iş emri oluşturun"
    *   **Detay:** "Üretilecek miktar: X adet". `montaj_is_emirleri.php` linki.

### Adım 7: İdeal Durum (Açık Yok)
Stoklar yeterli, sipariş ve kritik seviye sorunu yok.

*   **Koşul:** `$acik == 0` **VE** `count($sozlesme_eksik) == 0`
*   **UI Çıktısı:**
    *   **Class:** `badge-aksiyon-basarili` (Yeşil)
    *   **Mesaj:** "Her şey yolunda"
    *   **Detay:** Mevcut stok ve kritik seviye bilgisi. Buton yok.

### Adım 8: Gelecek İçin Hazırlık (Açık Yok, Sözleşme Yok)
Acil bir durum yok ama gelecekte sipariş gelirse sözleşme olmadığı için sorun çıkabilir.

*   **Koşul:** `$acik == 0` **VE** `count($sozlesme_eksik) > 0`
*   **UI Çıktısı:**
    *   **Class:** `badge-aksiyon-uyari` (Sarı)
    *   **Mesaj:** "Gelecek üretimler için sözleşme tamamlayın"
    *   **Detay:** Sözleşmesi olmayan malzemeler.

## 3. Yardımcı Fonksiyonlar ve Veri Yapıları

### `getSupplyChainData`
Tüm bu verileri hazırlayan ana fonksiyondur.
1.  **Bekleyen Siparişler:** `satinalma_siparisler` tablosundan `yoldaki_stok` verisini çeker.
2.  **Ürün Ağacı:** Her ürün için bileşenleri tarar, eksik türleri bulur.
3.  **Üretilebilir Miktar Hesabı:** `ne_uretsem.php` mantığıyla, eldeki stokların (stok + üretimde) siparişleri karşılayıp karşılamadığına bakar.
    *   `Kritik Açık = Kritik Seviye - (Stok + Üretimde)`
    *   `Sipariş Açığı = Sipariş Miktarı - Stok`
    *   `Toplam Açık = Kritik Açık + Sipariş Açığı`

### Aksiyon Öncelik Matrisi
| Öncelik | Durum | Aksiyon | Renk |
|---------|-------|---------|------|
| 1 (Yüksek) | Veri/Tanım Eksik | Tanımlama Yap | Kırmızı |
| 2 | Sözleşme Eksik | Sözleşme Yap | Sarı/Kırmızı |
| 3 | Malzeme Eksik | Satınalma | Mavi |
| 4 | Hammadde Eksik | Satınalma | Sarı |
| 5 | Esans Eksik (Hammadde Var) | Esans Üretimi | Mavi |
| 6 | Her Şey Var | Montaj Üretimi | Mavi |
| 7 (Düşük) | Her Şey Tamam | - | Yeşil |
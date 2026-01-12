# Aksiyon Önerisi Sütunu İçin Plan (Tedarik Zinciri Müdürü Perspektifiyle - Veri Kaynakları Dahil)

## Genel Hedef
Kokpit sayfasındaki "Ürün" sütununun sağına "Aksiyon Önerisi" adında yeni bir sütun ekleyeceğiz. Bu sütun, tedarik zinciri yönetimi açısından hangi adımları atmamız gerektiğini öncelik sırasına göre gösterecek.

## Öneri Sıralaması ve Veri Kaynakları (Tedarik Zinciri Müdürü Olarak)

### 1. **Veri Eksikliği Varsa (En Yüksek Öncelik)**:
- **Kontrol Edilecek Veri**: `$p['eksik_bilesenler']` ve `$p['esans_agaci_eksik']` dizileri
- **Koşul**: `count($p['eksik_bilesenler']) > 0` veya `count($p['esans_agaci_eksik']) > 0`
- **Aksiyon Önerisi**: "ÜRÜN VERİSİ EKSİK - Ürün ağacı ve esans formüllerini tamamlayın"
- **Neden?**: Sistemdeki temel veri eksikliği nedeniyle üretim planlaması yapılamaz.

### 2. **Reçete Tamam Ama "Açık" Kolonundaki Miktar 0'dan Büyük ve Sözleşme Eksikse**:
- **Kontrol Edilecek Veri**: "Açık" sütunundaki miktar ve "Sözleşme Durumu" sütunundaki veriler
- **Koşul**: `$p['acik'] > 0` ve bazı malzemeler için sözleşme eksikse
- **Aksiyon Önerisi**: "REÇETE TAMAM AMA SÖZLEŞME EKSİK - Sipariş verilemez, üretim yapılamaz"
- **Neden?**: Reçete eksiksiz olabilir ama sözleşme eksikse, tedarik zinciri bloke edilir. Sipariş vermeden üretim yapılamaz.

### 3. **Reçete Tamam, "Açık" Miktarı Pozitif, Malzemeler Yeterli Ama Sözleşme Eksikse**:
- **Kontrol Edilecek Veri**: "Açık" sütunundaki miktar, "Malzeme Stok (Mevcut)" ve "Sözleşme Durumu" sütunları
- **Koşul**: `$p['acik'] > 0`, mevcut stok yeterli ama bazı malzemeler için sözleşme eksikse
- **Aksiyon Önerisi**: "MEVCUT STOK YETERLİ, ÜRETİM YAPILABİLİR AMA SÖZLEŞME EKSİK - Gelecek siparişler için sözleşme tamamlayın"
- **Neden?**: Mevcut stokla üretim yapılabilir ama gelecek talepler için sözleşme eksikliği risk oluşturur.

### 4. **Reçete Tamam, "Açık" Miktarı Pozitif, Sipariş Verilmesi Gereken Malzeme Varsa**:
- **Kontrol Edilecek Veri**: "Açık" sütunundaki miktar, "Sözleşme Durumu" ve "Sipariş Verilmesi Gereken" sütunundaki veriler
- **Koşul**: `$p['acik'] > 0`, sözleşme tamam ama sipariş verilmesi gereken malzeme varsa
- **Aksiyon Önerisi**: "REÇETE VE SÖZLEŞME TAMAM, SİPARİŞ VERİN - Malzemeleri yolcu alın"
- **Neden?**: Sözleşmeler hazır, üretim için sipariş verme zamanı geldi. Malzemelerin zamanında gelmesi üretim planını etkiler.

### 5. **Reçete Tamam, "Açık" Miktarı Pozitif, Malzemeler Yolda Ama Esans Hammaddesi Eksikse**:
- **Kontrol Edilecek Veri**: "Açık" sütunundaki miktar, "Sipariş Verilmesi Gereken" ve "Net Sipariş Verilecek Esans Hammaddeleri" sütunları
- **Koşul**: `$p['acik'] > 0`, malzeme siparişleri verilmiş ama esans hammaddesi eksikse
- **Aksiyon Önerisi**: "MALZEMELER YOLDA AMA ESANS HAMMADDESİ EKSİK - Esans hammaddesi siparişi verin"
- **Neden?**: Ana malzemeler yolda olsa da, esans hammaddesi eksikse, üretim yine aksamaya uğrar.

### 6. **Reçete Tamam, "Açık" Miktarı Pozitif, Malzemeler Yolda Ama Esans Üretimi Gerekliyse**:
- **Kontrol Edilecek Veri**: "Açık" sütunundaki miktar ve "Esans İş Emri Açılması Gereken Miktar" sütunu
- **Koşul**: `$p['acik'] > 0`, malzemeler yolda ama esans üretimi gerekiyorsa
- **Aksiyon Önerisi**: "MALZEMELER YOLDA AMA ESANS ÜRETİMİ GEREKLİ - Esans iş emri oluşturun"
- **Neden?**: Esans hammaddeleri hazırken üretimi başlatmak, üretim sürecinin sürekliliğini sağlar.

### 7. **Reçete Tamam, "Açık" Miktarı Pozitif, Tüm Malzemeler Hazır Ama Montaj Üretimi Gerekliyse**:
- **Kontrol Edilecek Veri**: "Açık" sütunundaki miktar ve "Önerilen" sütunundaki veriler
- **Koşul**: `$p['acik'] > 0`, tüm malzemeler hazır ama montaj üretimi gerekiyorsa
- **Aksiyon Önerisi**: "TÜM MALZEMELER HAZIR, MONTAJ ÜRETİMİ BAŞLATIN - Montaj iş emri oluşturun"
- **Neden?**: Tüm bileşenler hazırken montaj üretimini başlatmak, teslim süresini optimize eder.

### 8. **"Açık" Miktarı 0 Ve Tüm Adımlar Tamamlandıysa**:
- **Kontrol Edilecek Veri**: "Açık" sütunundaki miktar ve tüm diğer sütunlar
- **Koşul**: `$p['acik'] == 0` ve diğer tüm eksiklikler giderilmişse
- **Aksiyon Önerisi**: "TEDARİK ZİNCİRİ DENGELİ - Her şey planlandığı gibi ilerliyor"
- **Neden?**: Tedarik zinciri dengesi, maliyet optimizasyonu ve müşteri memnuniyeti açısından ideal durumdadır.

### 9. **"Açık" Miktarı 0 Ama Sözleşme Eksikse**:
- **Kontrol Edilecek Veri**: "Açık" sütunundaki miktar ve "Sözleşme Durumu" sütunu
- **Koşul**: `$p['acik'] == 0` ama bazı malzemeler için sözleşme eksikse
- **Aksiyon Önerisi**: "ÜRÜN STOĞU YETERLİ AMA SÖZLEŞME EKSİK - Gelecek üretimler için sözleşme tamamlayın"
- **Neden?**: Stok yeterli olsa da, sözleşme eksikliği gelecekte üretim riski oluşturur.

## Veri Kaynakları ve Hesaplamalar

### PHP Tarafında Kullanılacak Veriler:
- `$p['urun_ismi']` - Ürün adı
- `$p['acik']` - Açığın toplam miktarı (kritik + sipariş)
- `$p['eksik_bilesenler']` - Eksik bileşenler listesi
- `$p['esans_agaci_eksik']` - Esans formülü olmayan esanslar
- `$p['sozlesme_eksik_malzemeler']` - Sözleşmesi olmayan malzemeler
- `$p['bilesen_detaylari']` - Bileşen detayları (stok, gereken miktar vs.)
- `$p['esans_uretim_bilgisi']` - Esans üretim bilgileri
- `$p['onerilen_uretim']` - Önerilen üretim miktarı

### JavaScript Tarafında Kullanılacak Veriler:
- Tablodaki "Sipariş Verilmesi Gereken", "Sipariş Gereken Esans Hammaddeleri" gibi sütunlardaki veriler

## Nasıl Çalışır? (Operasyonel Açıklama)
- Sistem PHP tarafında `$p` dizisinden gerekli verileri alır
- Öncelik sırasına göre kontrol eder
- İlk pozitif koşulu sağlayan öneriyi kullanıcıya gösterir
- Kullanıcı eksiklikleri giderdikçe veriler güncellenir ve öneriler otomatik olarak değişir

## Görsel Sunum (Operasyonel Perspektif)
- Kritik eksiklikler kırmızı etiketle (veri eksikliği, stok yetersizliği)
- Orta düzey eksiklikler turuncu etiketle (sözleşme eksikliği)
- Düşük öncelikli öneriler mavi etiketle (sipariş verme, iş emri oluşturma)
- Her şey yolundaysa yeşil etiketle ("Her şey yolunda")

## Amaç (Stratejik Perspektif)
Tedarik zinciri müdürü olarak, bu sütun sayesinde:
- Hangi ürün için hangi işlemi yapmam gerektiğini tek bakışta görebileceğim
- Öncelik sırasına göre işlerimi planlayabileceğim
- Talep karşılamada oluşabilecek aksamaları önceden görebileceğim
- Ekip arkadaşlarımın hangi adımları yapması gerektiğini net bir şekilde anlayabilecekleri şekilde görebileceğim
- Sistemsel eksiklikleri (veri eksikliği gibi) öncelikle gidererek süreçlerin sağlıklı işlemesini sağlayabileceğim
tekrarli_odemeler.php sayfası kaldırılacak yerine Kasa yönetim diye bir sayfa yapılacak.

dbdeki sirket_kasasi isimli tabloya cek_kasasi da eklenecek,

kasa yönetimi sayfasından seçilen kasalara manuel para eklenip çıkarılabilecek, çek kasasına da çek eklenip çıkarılacak, çek kısmı paradan farklı gibi çünkü eklenen çekin bankası vade tarihi kim tarafından verildiği vs kayıtları da olmalı.

gider_yonetimi.php sayfasındaki "Yeni Gider Ekle" modalından bu giderin kaydedileceği kasa bilgisi de seçilsin. kasadaki para eksilsin, dolar , euro , tl veya çek kasasından çek seçilebilsin. editleme ve silme işlemleri de bu doğrultuda güncellensin. mesela silme yapınca o gider kaydı silinsin kasa bakiyesi tersine dönsün gibi.

cerceve_sozlesmeler.php sayfasından ödeme yap kısmına basınca hangi kasadaki bakiyeden (sirket_kasasi tablosundaki) hangi ödeme yöntemiyle (nakit , kredi kartı , havale eft) yöntemi ile ödeme yapılacağı seçilsin. Eğer cek_kasası seçildiyse oradaki hangi çek verilecek oda seçilsin. para birimi de olsun mesela dolar kasası kullanılırsa dolar, euro kasası kullanılacaksa euro tl kasası kullanılacak ise tl biriminde ödeme yapılsın yada çek kasası kullanılacak ise kasadaki çeklerden ödeme yapılsın. mesela borç dolar ve ödeme tl kasasından yapılacak ise "ayarlar" ismindeki tablodaki kur dönüşümü yapılsın kurun kaç lira olacağı da yazsın. gider yönetimine de kayıt atsın.

Kasa yönetimi sayfasında tedarikçilere yapılacak ödemeler diye bir kısım olsun. Burada cerceve_sozlesmeler.php sayfasındaki verilerin toplamı yazsın.  dolar euro ve tl olarak ayrı ayrı ve en son hepsinin tl bazında toplamı da yazsın. 

kasa yönetimi sayfasının üstünde elimdeki stoklar diye 3 tane kutucuk olacak

urunler.php sayfasındaki tüm ürünler olacak adet*satış fiyatı'nı Tl'ye çevirip yazacak,

malzemeler.php sayfasındaki tüm malzemeleri de adet*alış fiyatını tl'ye çevirip yazacak,

esanslar.php sayfasındaki tüm esanlsarı da adet*teorik maliyet fiyatını çarlıp tlye çevirip yazacak,

sonra bu üçünün toplamı da toplam duran varlıklar diye yazacak.

kasa yönetimi sayfasında dolar , euro , çek ve tl kasasının içinde ne kadar bakiye var yazacak.

gider_yonetimi.php sayfasındaki kasanın üstüne de bu ay hangi kasadan ne kadar hangi türde para çıktı o şekilde yazsın.

tekrarli_odemeler.php sayfası ve personel_bordro.php sayfasında da paranın hangi kasadan çıkacağı seçilsin aynı şekilde gider yönetimine ve kasa kayıtlarına detaylı kayıt atsın.

yukarıdakiler giderlerin kaydı yönetimi vs ile ilgiliydi şimdi de gelirlerle ilgili olan kısmı yazalım.

sipariş gelirleri bilindiği üzere gelir_yonetimi.php sayfasından yapılıyor bu kısma da hangi kasadan hangi ödeme yöntemi ile kasaya giriş yapılacağı seçilmeli. çek ile de ödeme alınabilmeli alınan çek daha sonra yukarıdaki giderlerde para yerine kullanılabilir. o yüzden alınan çek öyle kaydedilsin ki ödeme yapılacağı zaman listeden seçilebilir olmalı.

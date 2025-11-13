-- Gerekli yeni sütunlar için SQL komutları
-- Bu komutlar yeni alanları oluşturur

-- Yeni alanlar (mevcut veritabanında zaten oluşturulmuş olabilir)
ALTER TABLE stok_hareketleri_sozlesmeler ADD COLUMN birim_fiyat DECIMAL(10,2);
ALTER TABLE stok_hareketleri_sozlesmeler ADD COLUMN para_birimi VARCHAR(10);
ALTER TABLE stok_hareketleri_sozlesmeler ADD COLUMN tedarikci_adi VARCHAR(255);
ALTER TABLE stok_hareketleri_sozlesmeler ADD COLUMN tedarikci_id INT;
ALTER TABLE stok_hareketleri_sozlesmeler ADD COLUMN baslangic_tarihi DATE;
ALTER TABLE stok_hareketleri_sozlesmeler ADD COLUMN bitis_tarihi DATE;

-- Ürünler tablosuna son maliyet alanı
ALTER TABLE urunler ADD COLUMN son_maliyet DECIMAL(10,2);
-- Malzeme siparişler tablosundaki durum enum'una 'olusturuldu' değeri ekleme
ALTER TABLE malzeme_siparisler 
MODIFY COLUMN durum ENUM('olusturuldu', 'siparis_verildi', 'iptal_edildi', 'teslim_edildi') DEFAULT 'olusturuldu';

@echo off
echo Siparisler Tablosundan Veri Cekiyorum...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; SELECT * FROM siparisler LIMIT 10;"

echo.
echo Siparis Kalemleri Tablosundan Veri Cekiyorum...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; SELECT * FROM siparis_kalemleri LIMIT 10;"

echo.
echo Siparisler ve Siparis Kalemleri Tablosunun Yapisi:
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; DESCRIBE siparisler;"
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; DESCRIBE siparis_kalemleri;"

echo.
echo Siparisler ile Musteriler Tablosunu Birlestirerek Siparisleri Cekiyorum...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; SELECT s.siparis_id, s.musteri_adi, s.tarih, s.durum, s.toplam_adet, s.aciklama FROM siparisler s LIMIT 10;"

echo.
echo En Son 5 Siparis ve Ilgili Kalemler:
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; SELECT s.siparis_id, s.musteri_adi, s.tarih, s.durum, sk.urun_ismi, sk.adet, sk.toplam_tutar FROM siparisler s JOIN siparis_kalemleri sk ON s.siparis_id = sk.siparis_id ORDER BY s.tarih DESC LIMIT 5;"
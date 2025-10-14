@echo off
echo Siparisler Tablosunun Yapisi:
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; DESCRIBE siparisler;"

echo.
echo Siparisler Tablosu Hakkinda Detayli Bilgi:
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; SHOW CREATE TABLE siparisler;"

echo.
echo Siparis Kalemleri Tablosunun Yapisi:
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; DESCRIBE siparis_kalemleri;"

echo.
echo Siparis Kalemleri Tablosu Hakkinda Detayli Bilgi:
"C:\xampp\mysql\bin\mysql.exe" -u root -e "USE parfum_erp; SHOW CREATE TABLE siparis_kalemleri;"
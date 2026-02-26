-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: parfum_erp
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ayarlar`
--

DROP TABLE IF EXISTS `ayarlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ayarlar` (
  `ayar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ayar_anahtar` varchar(255) NOT NULL,
  `ayar_deger` varchar(255) NOT NULL,
  PRIMARY KEY (`ayar_id`),
  UNIQUE KEY `ayar_anahtar` (`ayar_anahtar`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

LOCK TABLES `ayarlar` WRITE;
/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','43.8390'),(2,'euro_kuru','51.6600'),(3,'son_otomatik_yedek_tarihi','2026-02-25 01:05:23'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170');
/*!40000 ALTER TABLE `ayarlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cek_kasasi`
--

DROP TABLE IF EXISTS `cek_kasasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cek_kasasi` (
  `cek_id` int(11) NOT NULL AUTO_INCREMENT,
  `cek_no` varchar(50) NOT NULL COMMENT 'Cek numarasi veya referans numarasi',
  `cek_tutari` decimal(15,2) NOT NULL COMMENT 'Cek tutari',
  `cek_para_birimi` varchar(3) NOT NULL DEFAULT 'TL' COMMENT 'Cekin para birimi (TL, USD, EUR)',
  `cek_sahibi` varchar(255) NOT NULL COMMENT 'Cekin sahibi veya veren kisi',
  `cek_banka_adi` varchar(255) DEFAULT NULL COMMENT 'Cekin ait oldugu banka',
  `cek_subesi` varchar(255) DEFAULT NULL COMMENT 'Cekin ait oldugu sube',
  `vade_tarihi` date NOT NULL COMMENT 'Cekin vade tarihi',
  `cek_tipi` enum('alacak','verilen') NOT NULL DEFAULT 'alacak' COMMENT 'Cekin turu: alacak veya verilen',
  `cek_durumu` enum('alindi','kullanildi','iptal','geri_odendi','teminat_verildi','tahsilde') NOT NULL DEFAULT 'alindi' COMMENT 'Cekin durumu',
  `cek_alim_tarihi` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Cekin alindigi tarih',
  `cek_kullanim_tarihi` datetime DEFAULT NULL COMMENT 'Cekin kullanildigi tarih',
  `cek_son_durum_tarihi` datetime DEFAULT NULL COMMENT 'Cekin son durum degisikligi tarihi',
  `aciklama` text DEFAULT NULL COMMENT 'Cek aciklamasi',
  `kaydeden_personel` varchar(100) DEFAULT NULL COMMENT 'Ceki kaydeden personel',
  `ilgili_tablo` varchar(50) DEFAULT NULL COMMENT 'Cekin iliskili oldugu tablo',
  `ilgili_id` int(11) DEFAULT NULL COMMENT 'Cekin iliskili oldugu kayit ID',
  PRIMARY KEY (`cek_id`),
  KEY `vade_tarihi` (`vade_tarihi`),
  KEY `cek_durumu` (`cek_durumu`),
  KEY `cek_para_birimi` (`cek_para_birimi`),
  KEY `cek_no` (`cek_no`),
  KEY `cek_tipi` (`cek_tipi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cek_kasasi`
--

LOCK TABLES `cek_kasasi` WRITE;
/*!40000 ALTER TABLE `cek_kasasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `cek_kasasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cerceve_sozlesmeler`
--

DROP TABLE IF EXISTS `cerceve_sozlesmeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cerceve_sozlesmeler` (
  `sozlesme_id` int(11) NOT NULL AUTO_INCREMENT,
  `tedarikci_id` int(11) NOT NULL,
  `tedarikci_adi` varchar(255) NOT NULL,
  `malzeme_kodu` int(11) NOT NULL,
  `malzeme_ismi` varchar(255) NOT NULL,
  `birim_fiyat` decimal(10,2) NOT NULL,
  `para_birimi` varchar(10) NOT NULL,
  `limit_miktar` int(11) NOT NULL,
  `toplu_odenen_miktar` int(11) DEFAULT 0,
  `baslangic_tarihi` date DEFAULT NULL,
  `bitis_tarihi` date DEFAULT NULL,
  `olusturan` varchar(255) NOT NULL,
  `olusturulma_tarihi` datetime DEFAULT current_timestamp(),
  `aciklama` text DEFAULT NULL,
  `oncelik` int(11) DEFAULT 0,
  `odeme_kasasi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
  `odenen_cek_id` int(11) DEFAULT NULL COMMENT 'cek_kasasi tablosundaki cek_id',
  PRIMARY KEY (`sozlesme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cerceve_sozlesmeler`
--

LOCK TABLES `cerceve_sozlesmeler` WRITE;
/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (1,2,'Aydınel',1,'Birinci Ürün, etiket',10.00,'USD',999999999,0,'2026-02-03','2026-03-01','Admin User','2026-02-23 21:14:17','',1,'TL',NULL),(2,1,'Barış Günhan',3,'Birinci Ürün, ham esans',10.00,'TL',999999999,0,'2026-02-02','2026-02-27','Admin User','2026-02-23 21:14:45','',1,'TL',NULL),(3,2,'Aydınel',5,'Birinci Ürün, jelatin',14.00,'TL',99999999,0,'2026-02-06','2026-02-28','Admin User','2026-02-23 21:15:10','',1,'TL',NULL),(4,2,'Aydınel',2,'Birinci Ürün, kutu',12.00,'TL',99999999,0,'2026-02-06','2026-03-06','Admin User','2026-02-23 21:15:57','',1,'TL',NULL),(5,2,'Aydınel',4,'Birinci Ürün, paket',22.00,'TL',2147483647,0,'2026-01-26','2026-02-27','Admin User','2026-02-23 21:16:23','',1,'TL',NULL),(6,2,'Aydınel',6,'Birinci Ürün, takım',13.00,'TL',2147483647,0,'2026-02-09','2026-03-01','Admin User','2026-02-23 21:17:06','',1,'TL',NULL),(7,1,'Barış Günhan',2,'Birinci Ürün, kutu',1.00,'TL',99999999,0,'2026-02-16','2026-02-28','Admin User','2026-02-24 00:48:15','',1,'TL',NULL);
/*!40000 ALTER TABLE `cerceve_sozlesmeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `cerceve_sozlesmeler_gecerlilik`
--

DROP TABLE IF EXISTS `cerceve_sozlesmeler_gecerlilik`;
/*!50001 DROP VIEW IF EXISTS `cerceve_sozlesmeler_gecerlilik`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `cerceve_sozlesmeler_gecerlilik` AS SELECT
 1 AS `sozlesme_id`,
  1 AS `tedarikci_id`,
  1 AS `tedarikci_adi`,
  1 AS `malzeme_kodu`,
  1 AS `malzeme_ismi`,
  1 AS `birim_fiyat`,
  1 AS `para_birimi`,
  1 AS `limit_miktar`,
  1 AS `toplu_odenen_miktar`,
  1 AS `baslangic_tarihi`,
  1 AS `bitis_tarihi`,
  1 AS `olusturan`,
  1 AS `olusturulma_tarihi`,
  1 AS `aciklama`,
  1 AS `oncelik`,
  1 AS `toplam_mal_kabul_miktari`,
  1 AS `kalan_miktar`,
  1 AS `gecerli_mi`,
  1 AS `gecerlilik_durumu` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `esans_ihtiyaclari`
--

DROP TABLE IF EXISTS `esans_ihtiyaclari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esans_ihtiyaclari` (
  `ihtiyac_id` int(11) NOT NULL AUTO_INCREMENT,
  `urun_kodu` int(11) NOT NULL,
  `bilesen_kodu` varchar(50) NOT NULL,
  `bilesen_ismi` varchar(255) NOT NULL,
  `gereken_miktar` decimal(10,2) NOT NULL,
  `hesaplama_tarihi` datetime DEFAULT current_timestamp(),
  `durum` varchar(50) DEFAULT 'aktif',
  PRIMARY KEY (`ihtiyac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esans_ihtiyaclari`
--

LOCK TABLES `esans_ihtiyaclari` WRITE;
/*!40000 ALTER TABLE `esans_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `esans_ihtiyaclari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esans_is_emirleri`
--

DROP TABLE IF EXISTS `esans_is_emirleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esans_is_emirleri` (
  `is_emri_numarasi` int(11) NOT NULL AUTO_INCREMENT,
  `olusturulma_tarihi` date DEFAULT curdate(),
  `olusturan` varchar(255) NOT NULL,
  `esans_kodu` varchar(50) NOT NULL,
  `esans_ismi` varchar(255) NOT NULL,
  `tank_kodu` varchar(50) NOT NULL,
  `tank_ismi` varchar(255) NOT NULL,
  `planlanan_miktar` decimal(10,2) NOT NULL,
  `birim` varchar(50) DEFAULT NULL,
  `planlanan_baslangic_tarihi` date DEFAULT NULL,
  `demlenme_suresi_gun` int(11) DEFAULT NULL,
  `planlanan_bitis_tarihi` date DEFAULT NULL,
  `gerceklesen_baslangic_tarihi` date DEFAULT NULL,
  `gerceklesen_bitis_tarihi` date DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `durum` varchar(50) DEFAULT 'olusturuldu',
  `tamamlanan_miktar` decimal(10,2) DEFAULT 0.00,
  `eksik_miktar_toplami` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`is_emri_numarasi`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esans_is_emirleri`
--

LOCK TABLES `esans_is_emirleri` WRITE;
/*!40000 ALTER TABLE `esans_is_emirleri` DISABLE KEYS */;
INSERT INTO `esans_is_emirleri` VALUES (1,'2026-02-24','Admin User','ES-260223-276','Birinci Ürün, Esans','Tank3','Tank 3',100.00,'lt','2026-02-24',20,'2026-03-16','2026-02-24','2026-02-24',' ','tamamlandi',100.00,0.00),(2,'2026-02-24','Admin User','ES-260223-276','Birinci Ürün, Esans','Tank6','Tank 6',10.00,'lt','2026-02-24',20,'2026-03-16','2026-02-24','2026-02-24',' ','tamamlandi',10.00,0.00);
/*!40000 ALTER TABLE `esans_is_emirleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esans_is_emri_malzeme_listesi`
--

DROP TABLE IF EXISTS `esans_is_emri_malzeme_listesi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esans_is_emri_malzeme_listesi` (
  `is_emri_numarasi` int(11) NOT NULL,
  `malzeme_kodu` int(11) NOT NULL,
  `malzeme_ismi` varchar(255) NOT NULL,
  `malzeme_turu` varchar(100) NOT NULL,
  `miktar` decimal(10,2) NOT NULL,
  `birim` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esans_is_emri_malzeme_listesi`
--

LOCK TABLES `esans_is_emri_malzeme_listesi` WRITE;
/*!40000 ALTER TABLE `esans_is_emri_malzeme_listesi` DISABLE KEYS */;
INSERT INTO `esans_is_emri_malzeme_listesi` VALUES (1,3,'Birinci Ürün, ham esans','malzeme',80.00,'lt'),(1,7,'Alkol','malzeme',15.00,'lt'),(1,8,'Saf Su','malzeme',5.00,'lt'),(2,3,'Birinci Ürün, ham esans','malzeme',8.00,'lt'),(2,7,'Alkol','malzeme',1.50,'lt'),(2,8,'Saf Su','malzeme',0.50,'lt');
/*!40000 ALTER TABLE `esans_is_emri_malzeme_listesi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esanslar`
--

DROP TABLE IF EXISTS `esanslar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esanslar` (
  `esans_id` int(11) NOT NULL AUTO_INCREMENT,
  `esans_kodu` varchar(50) NOT NULL,
  `esans_ismi` varchar(255) NOT NULL,
  `not_bilgisi` text DEFAULT NULL,
  `stok_miktari` decimal(10,2) DEFAULT 0.00,
  `birim` varchar(50) DEFAULT 'adet',
  `demlenme_suresi_gun` decimal(5,2) DEFAULT 0.00,
  `tank_kodu` varchar(50) DEFAULT NULL,
  `tank_ismi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`esans_id`),
  UNIQUE KEY `esans_kodu` (`esans_kodu`),
  UNIQUE KEY `esans_id` (`esans_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esanslar`
--

LOCK TABLES `esanslar` WRITE;
/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (1,'ES-260223-276','Birinci Ürün, Esans','',130.00,'lt',20.00,'Tank1','Birinci Tankımız');
/*!40000 ALTER TABLE `esanslar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gelir_taksit_planlari`
--

DROP TABLE IF EXISTS `gelir_taksit_planlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gelir_taksit_planlari` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `musteri_id` int(11) DEFAULT NULL,
  `musteri_adi` varchar(255) NOT NULL,
  `toplam_tutar` decimal(10,2) NOT NULL,
  `para_birimi` varchar(3) NOT NULL DEFAULT 'TL',
  `taksit_sayisi` int(11) NOT NULL,
  `baslangic_tarihi` date NOT NULL,
  `aralik_gun` int(11) DEFAULT 30,
  `aciklama` text DEFAULT NULL,
  `durum` enum('aktif','tamamlandi','iptal') DEFAULT 'aktif',
  `olusturan` varchar(100) DEFAULT NULL,
  `olusturma_tarihi` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gelir_taksit_planlari`
--

LOCK TABLES `gelir_taksit_planlari` WRITE;
/*!40000 ALTER TABLE `gelir_taksit_planlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `gelir_taksit_planlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gelir_taksitleri`
--

DROP TABLE IF EXISTS `gelir_taksitleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gelir_taksitleri` (
  `taksit_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `sira_no` int(11) NOT NULL,
  `vade_tarihi` date NOT NULL,
  `tutar` decimal(10,2) NOT NULL,
  `odenen_tutar` decimal(10,2) DEFAULT 0.00,
  `durum` enum('bekliyor','kismi_odendi','odendi','gecikmis','iptal') DEFAULT 'bekliyor',
  `odeme_tarihi` datetime DEFAULT NULL,
  PRIMARY KEY (`taksit_id`),
  KEY `plan_id` (`plan_id`),
  KEY `durum` (`durum`),
  CONSTRAINT `fk_plan` FOREIGN KEY (`plan_id`) REFERENCES `gelir_taksit_planlari` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gelir_taksitleri`
--

LOCK TABLES `gelir_taksitleri` WRITE;
/*!40000 ALTER TABLE `gelir_taksitleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `gelir_taksitleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gelir_yonetimi`
--

DROP TABLE IF EXISTS `gelir_yonetimi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gelir_yonetimi` (
  `gelir_id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime NOT NULL DEFAULT current_timestamp(),
  `tutar` decimal(10,2) NOT NULL,
  `para_birimi` varchar(3) NOT NULL DEFAULT 'TL',
  `kategori` varchar(100) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_ismi` varchar(100) DEFAULT NULL,
  `siparis_id` int(11) DEFAULT NULL,
  `odeme_tipi` varchar(50) DEFAULT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `musteri_adi` varchar(255) DEFAULT NULL,
  `taksit_id` int(11) DEFAULT NULL,
  `kasa_secimi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
  `cek_secimi` int(11) DEFAULT NULL COMMENT 'cek_kasasi tablosundaki cek_id',
  PRIMARY KEY (`gelir_id`),
  KEY `siparis_id` (`siparis_id`),
  KEY `musteri_id` (`musteri_id`),
  KEY `idx_para_birimi` (`para_birimi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gelir_yonetimi`
--

LOCK TABLES `gelir_yonetimi` WRITE;
/*!40000 ALTER TABLE `gelir_yonetimi` DISABLE KEYS */;
/*!40000 ALTER TABLE `gelir_yonetimi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gider_yonetimi`
--

DROP TABLE IF EXISTS `gider_yonetimi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gider_yonetimi` (
  `gider_id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` date NOT NULL,
  `tutar` decimal(10,2) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_ismi` varchar(255) DEFAULT NULL,
  `fatura_no` varchar(100) DEFAULT NULL,
  `odeme_tipi` varchar(50) DEFAULT NULL,
  `odeme_yapilan_firma` varchar(255) DEFAULT NULL,
  `kasa_secimi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
  `cek_secimi` int(11) DEFAULT NULL COMMENT 'cek_kasasi tablosundaki cek_id',
  PRIMARY KEY (`gider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gider_yonetimi`
--

LOCK TABLES `gider_yonetimi` WRITE;
/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (2,'2026-02-24',10000.00,'Personel Avansı','Ahmet Yılmaz - 2026/2 dönemi avans ödemesi. ',1,'Admin User',NULL,'Nakit','Ahmet Yılmaz','TL',NULL),(3,'2026-02-24',40000.00,'Personel Gideri','Ahmet Yılmaz - 2026/2 dönemi maaş ödemesi. ',1,'Admin User',NULL,'Havale','Ahmet Yılmaz','TL',NULL),(4,'2026-02-24',20000.00,'Kira','Ofis Kirası - 2026/2 dönemi. ',1,'Admin User',NULL,'Nakit','','TL',NULL),(5,'2026-02-24',20000.00,'Kira','Kira - 2026/2 dönemi. ',1,'Admin User',NULL,'Havale','','TL',NULL);
/*!40000 ALTER TABLE `gider_yonetimi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `is_merkezleri`
--

DROP TABLE IF EXISTS `is_merkezleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `is_merkezleri` (
  `is_merkezi_id` int(11) NOT NULL AUTO_INCREMENT,
  `isim` varchar(255) NOT NULL,
  `aciklama` text DEFAULT NULL,
  PRIMARY KEY (`is_merkezi_id`),
  UNIQUE KEY `isim` (`isim`),
  UNIQUE KEY `is_merkezi_id` (`is_merkezi_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `is_merkezleri`
--

LOCK TABLES `is_merkezleri` WRITE;
/*!40000 ALTER TABLE `is_merkezleri` DISABLE KEYS */;
INSERT INTO `is_merkezleri` VALUES (2,'Is Merkezi 1','Büyük masanın olduğu yer.'),(3,'Is Merkezi 2',''),(4,'Is Merkezi 3','');
/*!40000 ALTER TABLE `is_merkezleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kasa_hareketleri`
--

DROP TABLE IF EXISTS `kasa_hareketleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kasa_hareketleri` (
  `hareket_id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime NOT NULL DEFAULT current_timestamp(),
  `islem_tipi` varchar(50) NOT NULL COMMENT 'kasa_ekle, kasa_cikar, cek_alma, cek_odeme, cek_kullanimi, cek_tahsile_gonderme, cek_tahsildi, gelir_girisi, gider_cikisi, transfer_giris, transfer_cikis',
  `kasa_adi` varchar(20) NOT NULL COMMENT 'TL, USD, EUR, cek_kasasi',
  `cek_id` int(11) DEFAULT NULL COMMENT 'Islem cekle ilgili ise cek_kasasi tablosundaki ID',
  `tutar` decimal(15,2) NOT NULL COMMENT 'Islem tutari',
  `para_birimi` varchar(3) NOT NULL DEFAULT 'TL' COMMENT 'Tutarin para birimi',
  `doviz_kuru` decimal(10,4) DEFAULT NULL COMMENT 'Islem dovizliyse kullanilan kur',
  `tl_karsiligi` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tutarin TL karsiligi',
  `kaynak_tablo` varchar(50) DEFAULT NULL COMMENT 'Islemin kaynagi (gider_yonetimi, gelir_yonetimi, cerceve_sozlesmeler, tekrarli_odemeler, personel_bordro)',
  `kaynak_id` int(11) DEFAULT NULL COMMENT 'Kaynak tablodaki kayit ID',
  `aciklama` text DEFAULT NULL COMMENT 'Islem aciklamasi',
  `kaydeden_personel` varchar(100) DEFAULT NULL COMMENT 'Islemi yapan personel',
  `ilgili_firma` varchar(255) DEFAULT NULL COMMENT 'Islemin ilgili oldugu firma',
  `ilgili_musteri` varchar(255) DEFAULT NULL COMMENT 'Islemin ilgili oldugu musteri',
  `fatura_no` varchar(100) DEFAULT NULL COMMENT 'Islemin bagli oldugu fatura numarasi',
  `odeme_tipi` varchar(50) DEFAULT NULL COMMENT 'Nakit, Kredi Karti, Havale, Cek vs.',
  `proje_kodu` varchar(50) DEFAULT NULL COMMENT 'Islemin ait oldugu proje',
  `is_merkezi` varchar(100) DEFAULT NULL COMMENT 'Is merkezi bilgisi',
  `ekstra_veri` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Isleme ozel ekstra veriler' CHECK (json_valid(`ekstra_veri`)),
  PRIMARY KEY (`hareket_id`),
  KEY `tarih` (`tarih`),
  KEY `islem_tipi` (`islem_tipi`),
  KEY `kasa_adi` (`kasa_adi`),
  KEY `kaynak_tablo` (`kaynak_tablo`),
  KEY `kaynak_id` (`kaynak_id`),
  KEY `para_birimi` (`para_birimi`),
  KEY `cek_id` (`cek_id`),
  KEY `tutar` (`tutar`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasa_hareketleri`
--

LOCK TABLES `kasa_hareketleri` WRITE;
/*!40000 ALTER TABLE `kasa_hareketleri` DISABLE KEYS */;
INSERT INTO `kasa_hareketleri` VALUES (1,'2026-02-24 00:00:00','personel_avansi','TL',NULL,10000.00,'TL',NULL,10000.00,'personel_avanslar',0,'Ahmet Yılmaz - 2026/2 dönemi avans ödemesi. ','Admin User','Ahmet Yılmaz',NULL,NULL,'Nakit',NULL,NULL,NULL),(2,'2026-02-24 00:00:00','personel_odemesi','TL',NULL,40000.00,'TL',NULL,40000.00,'personel_maas_odemeleri',0,'Ahmet Yılmaz - 2026/2 dönemi maaş ödemesi. ','Admin User','Ahmet Yılmaz',NULL,NULL,'Havale',NULL,NULL,NULL),(3,'2026-02-24 11:59:00','kasa_ekle','TL',NULL,100000.00,'TL',1.0000,100000.00,NULL,NULL,'','Admin User',NULL,NULL,NULL,'Nakit',NULL,NULL,NULL),(4,'2026-02-24 00:00:00','gider_cikisi','TL',NULL,20000.00,'TL',NULL,20000.00,'tekrarli_odeme_gecmisi',2,'Kira - 2026/2 dönemi. ','Admin User','',NULL,NULL,'Havale',NULL,NULL,NULL);
/*!40000 ALTER TABLE `kasa_hareketleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kasa_islemleri`
--

DROP TABLE IF EXISTS `kasa_islemleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kasa_islemleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime NOT NULL DEFAULT current_timestamp(),
  `islem_tipi` varchar(50) NOT NULL COMMENT 'gelir, gider, transfer_giris, transfer_cikis',
  `tutar` decimal(15,2) NOT NULL,
  `para_birimi` varchar(3) NOT NULL,
  `kaynak_tablo` varchar(50) DEFAULT NULL COMMENT 'gelir_yonetimi, gider_yonetimi, transfer',
  `kaynak_id` int(11) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tarih` (`tarih`),
  KEY `islem_tipi` (`islem_tipi`),
  KEY `para_birimi` (`para_birimi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasa_islemleri`
--

LOCK TABLES `kasa_islemleri` WRITE;
/*!40000 ALTER TABLE `kasa_islemleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `kasa_islemleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_tablosu`
--

DROP TABLE IF EXISTS `log_tablosu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_tablosu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime DEFAULT current_timestamp(),
  `kullanici_adi` varchar(255) NOT NULL,
  `log_metni` text NOT NULL,
  `islem_turu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tablosu`
--

LOCK TABLES `log_tablosu` WRITE;
/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2026-02-23 20:09:46','Admin User','esanslar, log_tablosu, lokasyonlar, malzemeler, urun_agaci, urunler tabloları temizlendi','DELETE','2026-02-23 17:09:46'),(2,'2026-02-23 20:15:53','Admin User','Merkez Depo deposuna Raf 1 rafı eklendi','CREATE','2026-02-23 17:15:53'),(3,'2026-02-23 20:16:00','Admin User','Merkez Depo deposuna Raf 2 rafı eklendi','CREATE','2026-02-23 17:16:00'),(4,'2026-02-23 20:16:09','Admin User','Merkez Depo deposuna Raf 3 rafı eklendi','CREATE','2026-02-23 17:16:09'),(5,'2026-02-23 20:16:18','Admin User','Giriş Depo deposuna A rafı rafı eklendi','CREATE','2026-02-23 17:16:18'),(6,'2026-02-23 20:16:26','Admin User','Giriş Depo deposuna B Rafı rafı eklendi','CREATE','2026-02-23 17:16:26'),(7,'2026-02-23 20:17:23','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-02-23 17:17:23'),(8,'2026-02-23 20:43:19','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-23 17:43:19'),(9,'2026-02-23 20:47:02','Admin User','deneme ürünü sisteme eklendi','CREATE','2026-02-23 17:47:02'),(10,'2026-02-23 20:47:13','Admin User','deneme ürününe fotoğraf eklendi','CREATE','2026-02-23 17:47:13'),(11,'2026-02-23 20:47:18','Admin User','deneme ürününe fotoğraf eklendi','CREATE','2026-02-23 17:47:18'),(12,'2026-02-23 20:47:30','Admin User','deneme ürününün ana fotoğrafı değiştirildi','UPDATE','2026-02-23 17:47:30'),(13,'2026-02-23 20:47:39','Admin User','deneme ürünü sistemden silindi','DELETE','2026-02-23 17:47:39'),(14,'2026-02-23 20:53:15','Admin User','Birinci Ürün ürünü sisteme eklendi','CREATE','2026-02-23 17:53:15'),(15,'2026-02-23 20:53:17','Admin User','Otomatik esans oluşturuldu: Birinci Ürün, Esans (Tank: Tank1)','CREATE','2026-02-23 17:53:17'),(16,'2026-02-23 20:53:18','Admin User','Otomatik ham esans esans ağacına eklendi: Birinci Ürün, ham esans','CREATE','2026-02-23 17:53:18'),(17,'2026-02-23 20:53:51','Admin User','Birinci Ürün ürününe fotoğraf eklendi','CREATE','2026-02-23 17:53:51'),(18,'2026-02-23 20:53:56','Admin User','Birinci Ürün ürününe fotoğraf eklendi','CREATE','2026-02-23 17:53:56'),(19,'2026-02-23 20:54:09','Admin User','Birinci Ürün ürününün ana fotoğrafı değiştirildi','UPDATE','2026-02-23 17:54:09'),(20,'2026-02-23 20:54:54','Admin User','Birinci Ürün, takım malzemesi Birinci Ürün, takım olarak güncellendi','UPDATE','2026-02-23 17:54:54'),(21,'2026-02-23 20:55:08','Admin User','Birinci Ürün, takım malzemesi Birinci Ürün, takım olarak güncellendi','UPDATE','2026-02-23 17:55:08'),(22,'2026-02-23 20:55:46','Admin User','Birinci Ürün, takım malzemesi Birinci Ürün, takım olarak güncellendi','UPDATE','2026-02-23 17:55:46'),(23,'2026-02-23 20:55:56','Admin User','Birinci Ürün, jelatin malzemesi Birinci Ürün, jelatin olarak güncellendi','UPDATE','2026-02-23 17:55:56'),(24,'2026-02-23 20:57:24','Admin User','Birinci Ürün, Esans esansı güncellendi','UPDATE','2026-02-23 17:57:24'),(25,'2026-02-23 20:58:47','Admin User','Birinci Ürün ürün ağacındaki Birinci Ürün, Esans bileşeni Birinci Ürün, Esans olarak güncellendi','UPDATE','2026-02-23 17:58:47'),(26,'2026-02-23 20:59:52','Admin User','Birinci Ürün, ham esans malzemesi Birinci Ürün, ham esans olarak güncellendi','UPDATE','2026-02-23 17:59:52'),(27,'2026-02-23 21:00:32','Admin User','Birinci Ürün, Esans ürün ağacındaki Birinci Ürün, ham esans bileşeni Birinci Ürün, ham esans olarak güncellendi','UPDATE','2026-02-23 18:00:32'),(28,'2026-02-23 21:01:47','Admin User','Alkol malzemesi sisteme eklendi','CREATE','2026-02-23 18:01:47'),(29,'2026-02-23 21:02:29','Admin User','Birinci Ürün, Esans ürün ağacına Alkol bileşeni eklendi','CREATE','2026-02-23 18:02:29'),(30,'2026-02-23 21:02:43','Admin User','Birinci Ürün, Esans ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2026-02-23 18:02:43'),(31,'2026-02-23 21:02:52','Admin User','Birinci Ürün, Esans ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2026-02-23 18:02:52'),(32,'2026-02-23 21:03:23','Admin User','Yeni malzeme türü eklendi: su (su)','CREATE','2026-02-23 18:03:23'),(33,'2026-02-23 21:03:51','Admin User','Saf Su malzemesi sisteme eklendi','CREATE','2026-02-23 18:03:51'),(34,'2026-02-23 21:04:34','Admin User','Birinci Ürün, Esans ürün ağacına Saf Su bileşeni eklendi','CREATE','2026-02-23 18:04:34'),(35,'2026-02-23 21:12:05','Admin User','Barış Günhan tedarikçisi sisteme eklendi','CREATE','2026-02-23 18:12:05'),(36,'2026-02-23 21:12:33','Admin User','Aydınel tedarikçisi sisteme eklendi','CREATE','2026-02-23 18:12:33'),(37,'2026-02-23 21:14:17','Admin User','Aydınel tedarikçisine Birinci Ürün, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-23 18:14:17'),(38,'2026-02-23 21:14:45','Admin User','Barış Günhan tedarikçisine Birinci Ürün, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-23 18:14:45'),(39,'2026-02-23 21:15:10','Admin User','Aydınel tedarikçisine Birinci Ürün, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-23 18:15:10'),(40,'2026-02-23 21:15:57','Admin User','Aydınel tedarikçisine Birinci Ürün, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-23 18:15:57'),(41,'2026-02-23 21:16:23','Admin User','Aydınel tedarikçisine Birinci Ürün, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-23 18:16:23'),(42,'2026-02-23 21:17:06','Admin User','Aydınel tedarikçisine Birinci Ürün, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-23 18:17:06'),(43,'2026-02-24 00:17:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-23 21:17:14'),(44,'2026-02-24 00:48:15','Admin User','Barış Günhan tedarikçisine Birinci Ürün, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-23 21:48:15'),(45,'2026-02-24 00:49:26','Admin User','Aydınel tedarikçisine PO-2026-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-02-23 21:49:26'),(46,'2026-02-24 00:50:06','Admin User','Satınalma siparişi #1 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-02-23 21:50:06'),(47,'2026-02-24 00:50:27','Admin User','Satınalma siparişi #1 güncellendi','UPDATE','2026-02-23 21:50:27'),(48,'2026-02-24 00:51:47','Admin User','Aydınel tedarikçisine PO-2026-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-02-23 21:51:47'),(49,'2026-02-24 00:52:15','Admin User','PO-2026-00002 no\'lu Aydınel siparişi silindi','DELETE','2026-02-23 21:52:15'),(50,'2026-02-24 01:07:57','Admin User','Barış Günhan tedarikçisine PO-2026-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-02-23 22:07:57'),(51,'2026-02-24 01:08:15','Admin User','PO-2026-00002 no\'lu Barış Günhan siparişi silindi','DELETE','2026-02-23 22:08:15'),(52,'2026-02-24 01:08:19','Admin User','PO-2026-00001 no\'lu Aydınel siparişi silindi','DELETE','2026-02-23 22:08:19'),(53,'2026-02-24 01:21:27','Admin User','Aydınel tedarikçisine PO-2026-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-02-23 22:21:27'),(54,'2026-02-24 01:21:55','Admin User','Satınalma siparişi #4 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-02-23 22:21:55'),(55,'2026-02-24 01:22:12','Admin User','Satınalma siparişi #4 güncellendi','UPDATE','2026-02-23 22:22:12'),(56,'2026-02-24 01:23:16','Admin User','Aydınel tedarikçisine PO-2026-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-02-23 22:23:16'),(57,'2026-02-24 01:23:26','Admin User','Satınalma siparişi #5 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-02-23 22:23:26'),(58,'2026-02-24 01:25:40','Admin User','Satınalma siparişi #5 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-02-23 22:25:40'),(59,'2026-02-24 01:26:57','Admin User','Satınalma siparişi #4 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-02-23 22:26:57'),(60,'2026-02-24 01:27:46','Admin User','Satınalma siparişi #4 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-02-23 22:27:46'),(61,'2026-02-24 02:15:01','Admin User','Birinci Ürün, etiket için 10 adet stok hareketi eklendi','CREATE','2026-02-23 23:15:01'),(62,'2026-02-24 02:15:37','Admin User','Birinci Ürün, etiket için 30 adet stok hareketi ve ilgili gider kaydı eklendi','CREATE','2026-02-23 23:15:37'),(63,'2026-02-24 02:18:24','Admin User','Birinci Ürün, etiket malzemesi Birinci Ürün, etiket olarak güncellendi','UPDATE','2026-02-23 23:18:24'),(64,'2026-02-24 02:20:59','Admin User','Birinci Ürün ürünü için montaj iş emri oluşturuldu','CREATE','2026-02-23 23:20:59'),(65,'2026-02-24 03:04:20','Admin User','Barış Günhan tedarikçisine PO-2026-00003 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-02-24 00:04:20'),(66,'2026-02-24 03:04:29','Admin User','Satınalma siparişi #6 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-02-24 00:04:29'),(67,'2026-02-24 03:07:09','Admin User','Birinci Ürün, ham esans ürünü için stok hareketi silindi (ID: 17)','DELETE','2026-02-24 00:07:09'),(68,'2026-02-24 03:09:30','Admin User','Barış Günhan tedarikçisine PO-2026-00004 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-02-24 00:09:30'),(69,'2026-02-24 03:09:35','Admin User','PO-2026-00003 no\'lu Barış Günhan siparişi silindi','DELETE','2026-02-24 00:09:35'),(70,'2026-02-24 03:09:40','Admin User','Satınalma siparişi #7 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-02-24 00:09:40'),(71,'2026-02-24 03:10:30','Admin User','Birinci Ürün, Esans esansı için iş emri oluşturuldu','CREATE','2026-02-24 00:10:30'),(72,'2026-02-24 03:17:30','Admin User','Birinci Ürün, Esans esansı sistemden silindi','DELETE','2026-02-24 00:17:30'),(73,'2026-02-24 10:06:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-24 07:06:14'),(74,'2026-02-24 10:06:36','Admin User','Fire Gideri kategorisindeki 300.00 TL tutarlı gider silindi','DELETE','2026-02-24 07:06:36'),(75,'2026-02-24 10:07:55','Admin User','Alkol için 15 lt stok hareketi eklendi','CREATE','2026-02-24 07:07:55'),(76,'2026-02-24 10:08:44','Admin User','Alkol için 10 lt stok hareketi eklendi','CREATE','2026-02-24 07:08:44'),(77,'2026-02-24 10:10:02','Admin User','Alkol için 20 lt stok hareketi eklendi','CREATE','2026-02-24 07:10:02'),(78,'2026-02-24 10:16:05','Admin User','Birinci Ürün, Esans esansı için iş emri oluşturuldu','CREATE','2026-02-24 07:16:05'),(79,'2026-02-24 10:27:33','Admin User','Birinci Ürün ürünü için montaj iş emri oluşturuldu','CREATE','2026-02-24 07:27:33'),(80,'2026-02-24 10:52:32','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-24 07:52:32'),(81,'2026-02-24 11:01:24','Admin User','Deneme personeli sisteme eklendi','CREATE','2026-02-24 08:01:24'),(82,'2026-02-24 11:01:38','Admin User','Deneme personeli sistemden silindi','DELETE','2026-02-24 08:01:38'),(83,'2026-02-24 11:01:56','Admin User','Aydin Seyrek personeli sisteme eklendi','CREATE','2026-02-24 08:01:56'),(84,'2026-02-24 11:22:41','Admin User','Aydin Seyrek personeli sistemden silindi','DELETE','2026-02-24 08:22:41'),(85,'2026-02-24 11:23:21','Admin User','Selam personeli sisteme eklendi','CREATE','2026-02-24 08:23:21'),(86,'2026-02-24 11:44:43','Admin User','Selam personeli sistemden silindi','DELETE','2026-02-24 08:44:43'),(87,'2026-02-24 11:45:40','Admin User','Ahmet Yılmaz personeli sisteme eklendi','CREATE','2026-02-24 08:45:40'),(88,'2026-02-24 11:46:10','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-02-24 08:46:10'),(89,'2026-02-24 11:46:21','Ahmet Yılmaz','Personel giriş yaptı (E-posta/Telefon: 05515555555)','Giriş Yapıldı','2026-02-24 08:46:21'),(90,'2026-02-24 11:46:47','Ahmet Yılmaz','Müşteri1 müşterisi sisteme eklendi','CREATE','2026-02-24 08:46:47'),(91,'2026-02-24 11:46:58','Ahmet Yılmaz','personel oturumu kapattı (ID: 303)','Çıkış Yapıldı','2026-02-24 08:46:58'),(92,'2026-02-24 11:47:18','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-24 08:47:18'),(93,'2026-02-24 11:47:39','Admin User','Ahmet Yılmaz personeline 10000 TL avans verildi (2026/2)','CREATE','2026-02-24 08:47:39'),(94,'2026-02-24 11:48:21','Admin User','Ahmet Yılmaz personeline 2026/2 dönemi için 40000 TL maaş ödemesi yapıldı','CREATE','2026-02-24 08:48:21'),(95,'2026-02-24 11:49:20','Admin User','Yeni tekrarlı ödeme tanımlandı: Ofis Kirası (Kira) - 20000 TL','CREATE','2026-02-24 08:49:20'),(96,'2026-02-24 11:49:47','Admin User','Ofis Kirası ödemesi yapıldı (2026/2) - 20000 TL','CREATE','2026-02-24 08:49:47'),(97,'2026-02-24 11:59:33','Admin User','Tekrarlı ödeme silindi: Ofis Kirası','DELETE','2026-02-24 08:59:33'),(98,'2026-02-24 11:59:50','Admin User','Yeni tekrarlı ödeme tanımlandı: Kira (Kira) - 20000 TL','CREATE','2026-02-24 08:59:50'),(99,'2026-02-24 12:00:27','Admin User','Kira ödemesi yapıldı (2026/2) - 20000 TL','CREATE','2026-02-24 09:00:27'),(100,'2026-02-24 12:02:45','Admin User','Müşteri1 müşterisi Alper Bey olarak güncellendi','UPDATE','2026-02-24 09:02:45'),(101,'2026-02-24 12:02:49','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-02-24 09:02:49'),(102,'2026-02-24 12:03:13','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-24 09:03:13'),(103,'2026-02-24 12:03:36','Admin User','Alper Bey müşterisi Alper Bey olarak güncellendi','UPDATE','2026-02-24 09:03:36'),(104,'2026-02-24 12:03:41','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-02-24 09:03:41'),(105,'2026-02-24 12:03:47','Alper Bey','Müşteri giriş yaptı (E-posta/Telefon: 05416144274)','Giriş Yapıldı','2026-02-24 09:03:47'),(106,'2026-02-24 12:04:29','Alper Bey','musteri oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-02-24 09:04:29'),(107,'2026-02-24 12:04:40','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-24 09:04:40'),(108,'2026-02-24 12:17:51','Admin User','Dolar ve Euro kuru ayarları güncellendi','UPDATE','2026-02-24 09:17:51'),(109,'2026-02-24 12:18:20','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-02-24 09:18:20'),(110,'2026-02-24 15:16:20','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-24 12:16:20'),(111,'2026-02-24 15:50:10','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-24 12:50:10');
/*!40000 ALTER TABLE `log_tablosu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lokasyonlar`
--

DROP TABLE IF EXISTS `lokasyonlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lokasyonlar` (
  `lokasyon_id` int(11) NOT NULL AUTO_INCREMENT,
  `depo_ismi` varchar(255) NOT NULL,
  `raf` varchar(100) NOT NULL,
  PRIMARY KEY (`lokasyon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lokasyonlar`
--

LOCK TABLES `lokasyonlar` WRITE;
/*!40000 ALTER TABLE `lokasyonlar` DISABLE KEYS */;
INSERT INTO `lokasyonlar` VALUES (1,'Merkez Depo','Raf 1'),(2,'Merkez Depo','Raf 2'),(3,'Merkez Depo','Raf 3'),(4,'Giriş Depo','A rafı'),(5,'Giriş Depo','B Rafı');
/*!40000 ALTER TABLE `lokasyonlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `malzeme_fotograflari`
--

DROP TABLE IF EXISTS `malzeme_fotograflari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `malzeme_fotograflari` (
  `fotograf_id` int(11) NOT NULL AUTO_INCREMENT,
  `malzeme_kodu` int(11) NOT NULL,
  `dosya_adi` varchar(255) NOT NULL,
  `dosya_yolu` varchar(500) NOT NULL,
  `sira_no` int(11) DEFAULT 0,
  `ana_fotograf` tinyint(1) DEFAULT 0,
  `yuklenme_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`fotograf_id`),
  KEY `idx_malzeme_kodu` (`malzeme_kodu`),
  CONSTRAINT `malzeme_fotograflari_ibfk_1` FOREIGN KEY (`malzeme_kodu`) REFERENCES `malzemeler` (`malzeme_kodu`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_fotograflari`
--

LOCK TABLES `malzeme_fotograflari` WRITE;
/*!40000 ALTER TABLE `malzeme_fotograflari` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_fotograflari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `malzeme_ihtiyaclari`
--

DROP TABLE IF EXISTS `malzeme_ihtiyaclari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `malzeme_ihtiyaclari` (
  `ihtiyac_id` int(11) NOT NULL AUTO_INCREMENT,
  `urun_kodu` int(11) NOT NULL,
  `bilesen_kodu` varchar(50) NOT NULL,
  `bilesen_ismi` varchar(255) NOT NULL,
  `gereken_miktar` decimal(10,2) NOT NULL,
  `hesaplama_tarihi` datetime DEFAULT current_timestamp(),
  `durum` varchar(50) DEFAULT 'aktif',
  PRIMARY KEY (`ihtiyac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_ihtiyaclari`
--

LOCK TABLES `malzeme_ihtiyaclari` WRITE;
/*!40000 ALTER TABLE `malzeme_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_ihtiyaclari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `malzeme_maliyetleri`
--

DROP TABLE IF EXISTS `malzeme_maliyetleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `malzeme_maliyetleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `malzeme_kodu` int(11) NOT NULL,
  `malzeme_ismi` varchar(255) NOT NULL,
  `agirlikli_ortalama_maliyet` decimal(10,2) NOT NULL,
  `son_hesaplama_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `malzeme_kodu` (`malzeme_kodu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_maliyetleri`
--

LOCK TABLES `malzeme_maliyetleri` WRITE;
/*!40000 ALTER TABLE `malzeme_maliyetleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_maliyetleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `malzeme_siparisler`
--

DROP TABLE IF EXISTS `malzeme_siparisler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `malzeme_siparisler` (
  `siparis_id` int(11) NOT NULL AUTO_INCREMENT,
  `malzeme_kodu` int(11) NOT NULL,
  `malzeme_ismi` varchar(255) DEFAULT NULL,
  `tedarikci_id` int(11) NOT NULL,
  `tedarikci_ismi` varchar(255) DEFAULT NULL,
  `miktar` decimal(10,2) NOT NULL,
  `siparis_tarihi` date NOT NULL,
  `teslim_tarihi` date DEFAULT NULL,
  `durum` varchar(50) DEFAULT 'siparis_verildi',
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_adi` varchar(255) DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `guncelleme_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`siparis_id`),
  KEY `idx_malzeme_kodu` (`malzeme_kodu`),
  KEY `idx_tedarikci_id` (`tedarikci_id`),
  KEY `idx_durum` (`durum`),
  KEY `idx_siparis_tarihi` (`siparis_tarihi`),
  CONSTRAINT `malzeme_siparisler_ibfk_1` FOREIGN KEY (`malzeme_kodu`) REFERENCES `malzemeler` (`malzeme_kodu`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_siparisler`
--

LOCK TABLES `malzeme_siparisler` WRITE;
/*!40000 ALTER TABLE `malzeme_siparisler` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_siparisler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `malzeme_turleri`
--

DROP TABLE IF EXISTS `malzeme_turleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `malzeme_turleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `sira` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_turleri`
--

LOCK TABLES `malzeme_turleri` WRITE;
/*!40000 ALTER TABLE `malzeme_turleri` DISABLE KEYS */;
INSERT INTO `malzeme_turleri` VALUES (3,'kutu','kutu',1,'2025-12-25 08:21:49'),(4,'etiket','etiket',2,'2025-12-25 08:22:01'),(5,'takm','takım',3,'2025-12-25 08:22:10'),(6,'ham_esans','ham esans',4,'2025-12-25 08:22:38'),(7,'alkol','alkol',5,'2025-12-25 08:22:52'),(8,'paket','paket',6,'2025-12-25 08:23:11'),(9,'jelatin','jelatin',7,'2025-12-25 08:23:23'),(10,'su','su',8,'2026-02-23 18:03:23');
/*!40000 ALTER TABLE `malzeme_turleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `malzemeler`
--

DROP TABLE IF EXISTS `malzemeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `malzemeler` (
  `malzeme_kodu` int(11) NOT NULL AUTO_INCREMENT,
  `malzeme_ismi` varchar(255) NOT NULL,
  `malzeme_turu` varchar(100) NOT NULL,
  `not_bilgisi` text DEFAULT NULL,
  `stok_miktari` decimal(10,2) DEFAULT 0.00,
  `birim` varchar(50) DEFAULT 'adet',
  `alis_fiyati` decimal(10,2) DEFAULT 0.00,
  `para_birimi` varchar(3) NOT NULL DEFAULT 'TRY',
  `maliyet_manuel_girildi` tinyint(1) NOT NULL DEFAULT 0,
  `termin_suresi` int(11) DEFAULT 0,
  `depo` varchar(255) DEFAULT NULL,
  `raf` varchar(100) DEFAULT NULL,
  `kritik_stok_seviyesi` int(11) DEFAULT 0,
  PRIMARY KEY (`malzeme_kodu`),
  UNIQUE KEY `malzeme_ismi` (`malzeme_ismi`),
  UNIQUE KEY `malzeme_kodu` (`malzeme_kodu`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzemeler`
--

LOCK TABLES `malzemeler` WRITE;
/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (1,'Birinci Ürün, etiket','etiket','null',180.00,'adet',10.00,'USD',0,0,'Giriş Depo','B Rafı',0),(2,'Birinci Ürün, kutu','kutu',NULL,190.00,'adet',12.00,'TL',0,0,'Giriş Depo','A rafı',0),(3,'Birinci Ürün, ham esans','ham_esans','null',12.00,'lt',10.00,'TL',0,0,'Giriş Depo','A rafı',0),(4,'Birinci Ürün, paket','paket',NULL,13.00,'adet',22.00,'TL',0,0,'Giriş Depo','A rafı',0),(5,'Birinci Ürün, jelatin','jelatin','null',90.00,'adet',14.00,'TL',0,0,'Giriş Depo','A rafı',0),(6,'Birinci Ürün, takım','takm','null',0.00,'adet',13.00,'TL',0,0,'Giriş Depo','B Rafı',300),(7,'Alkol','alkol','',68.50,'lt',0.00,'TRY',0,15,'Giriş Depo','A rafı',500),(8,'Saf Su','su','',9994.50,'lt',0.00,'TRY',0,1,'Merkez Depo','Raf 1',100);
/*!40000 ALTER TABLE `malzemeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `montaj_is_emirleri`
--

DROP TABLE IF EXISTS `montaj_is_emirleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `montaj_is_emirleri` (
  `is_emri_numarasi` int(11) NOT NULL AUTO_INCREMENT,
  `olusturulma_tarihi` date DEFAULT curdate(),
  `olusturan` varchar(255) NOT NULL,
  `urun_kodu` varchar(50) NOT NULL,
  `urun_ismi` varchar(255) NOT NULL,
  `planlanan_miktar` decimal(10,2) NOT NULL,
  `birim` varchar(50) DEFAULT NULL,
  `planlanan_baslangic_tarihi` date DEFAULT NULL,
  `planlanan_bitis_tarihi` date DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `durum` varchar(50) DEFAULT 'olusturuldu',
  `tamamlanan_miktar` decimal(10,2) DEFAULT 0.00,
  `eksik_miktar_toplami` decimal(10,2) DEFAULT 0.00,
  `is_merkezi_id` int(11) DEFAULT NULL,
  `gerceklesen_baslangic_tarihi` date DEFAULT NULL,
  `gerceklesen_bitis_tarihi` date DEFAULT NULL,
  PRIMARY KEY (`is_emri_numarasi`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `montaj_is_emirleri`
--

LOCK TABLES `montaj_is_emirleri` WRITE;
/*!40000 ALTER TABLE `montaj_is_emirleri` DISABLE KEYS */;
INSERT INTO `montaj_is_emirleri` VALUES (1,'2026-02-23','Admin User','2','Birinci Ürün',190.00,'adet','2026-02-24','2026-02-24',' ','tamamlandi',190.00,0.00,2,'2026-02-24','2026-02-24'),(2,'2026-02-24','Admin User','2','Birinci Ürün',10.00,'adet','2026-02-24','2026-02-24',' ','tamamlandi',10.00,0.00,2,'2026-02-24','2026-02-24');
/*!40000 ALTER TABLE `montaj_is_emirleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `montaj_is_emri_malzeme_listesi`
--

DROP TABLE IF EXISTS `montaj_is_emri_malzeme_listesi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `montaj_is_emri_malzeme_listesi` (
  `is_emri_numarasi` int(11) NOT NULL,
  `malzeme_kodu` varchar(50) NOT NULL,
  `malzeme_ismi` varchar(255) NOT NULL,
  `malzeme_turu` varchar(100) NOT NULL,
  `miktar` decimal(10,2) NOT NULL,
  `birim` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `montaj_is_emri_malzeme_listesi`
--

LOCK TABLES `montaj_is_emri_malzeme_listesi` WRITE;
/*!40000 ALTER TABLE `montaj_is_emri_malzeme_listesi` DISABLE KEYS */;
INSERT INTO `montaj_is_emri_malzeme_listesi` VALUES (1,'1','Birinci Ürün, etiket','etiket',190.00,'adet'),(1,'2','Birinci Ürün, kutu','kutu',190.00,'adet'),(1,'4','Birinci Ürün, paket','paket',190.00,'adet'),(1,'5','Birinci Ürün, jelatin','jelatin',190.00,'adet'),(1,'6','Birinci Ürün, takım','takm',190.00,'adet'),(1,'ES-260223-276','Birinci Ürün, Esans','esans',19.00,'adet'),(2,'1','Birinci Ürün, etiket','etiket',10.00,'adet'),(2,'2','Birinci Ürün, kutu','kutu',10.00,'adet'),(2,'4','Birinci Ürün, paket','paket',10.00,'adet'),(2,'5','Birinci Ürün, jelatin','jelatin',10.00,'adet'),(2,'6','Birinci Ürün, takım','takm',10.00,'adet'),(2,'ES-260223-276','Birinci Ürün, Esans','esans',1.00,'adet');
/*!40000 ALTER TABLE `montaj_is_emri_malzeme_listesi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mrp_ayarlar`
--

DROP TABLE IF EXISTS `mrp_ayarlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrp_ayarlar` (
  `ayar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ayar_anahtar` varchar(100) NOT NULL,
  `ayar_deger` text DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  PRIMARY KEY (`ayar_id`),
  UNIQUE KEY `ayar_anahtar` (`ayar_anahtar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrp_ayarlar`
--

LOCK TABLES `mrp_ayarlar` WRITE;
/*!40000 ALTER TABLE `mrp_ayarlar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_ayarlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mrp_ihtiyaclar`
--

DROP TABLE IF EXISTS `mrp_ihtiyaclar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrp_ihtiyaclar` (
  `ihtiyac_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `malzeme_kodu` varchar(50) NOT NULL,
  `gerekli_miktar` decimal(10,2) NOT NULL,
  `stokta_mevcut` decimal(10,2) DEFAULT 0.00,
  `siparis_verilen` decimal(10,2) DEFAULT 0.00,
  `net_ihtiyaç` decimal(10,2) NOT NULL,
  `teslim_tarihi` date DEFAULT NULL,
  `durum` enum('yetersiz','planlandi','siparis_verildi','teslim_alindi') DEFAULT 'yetersiz',
  PRIMARY KEY (`ihtiyac_id`),
  KEY `plan_id` (`plan_id`),
  KEY `idx_malzeme_kodu` (`malzeme_kodu`),
  KEY `idx_durum` (`durum`),
  CONSTRAINT `mrp_ihtiyaclar_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `mrp_planlama` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrp_ihtiyaclar`
--

LOCK TABLES `mrp_ihtiyaclar` WRITE;
/*!40000 ALTER TABLE `mrp_ihtiyaclar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_ihtiyaclar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mrp_planlama`
--

DROP TABLE IF EXISTS `mrp_planlama`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrp_planlama` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `siparis_id` int(11) DEFAULT NULL,
  `urun_kodu` varchar(50) NOT NULL,
  `planlanan_miktar` decimal(10,2) NOT NULL,
  `ihtiyaclar` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ihtiyaclar`)),
  `baslangic_tarihi` date NOT NULL,
  `bitis_tarihi` date NOT NULL,
  `durum` enum('beklemede','planlandi','uretimde','tamamlandi','iptal') DEFAULT 'beklemede',
  `olusturulma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `guncellenme_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`plan_id`),
  KEY `idx_urun_kodu` (`urun_kodu`),
  KEY `idx_durum` (`durum`),
  KEY `idx_baslangic_tarihi` (`baslangic_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrp_planlama`
--

LOCK TABLES `mrp_planlama` WRITE;
/*!40000 ALTER TABLE `mrp_planlama` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_planlama` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mrp_raporlar`
--

DROP TABLE IF EXISTS `mrp_raporlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrp_raporlar` (
  `rapor_id` int(11) NOT NULL AUTO_INCREMENT,
  `rapor_adi` varchar(255) NOT NULL,
  `rapor_turu` enum('ihtiyaclar','takvim','uretim','teslimat') NOT NULL,
  `rapor_verisi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rapor_verisi`)),
  `olusturulma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `olusturan_kullanici_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`rapor_id`),
  KEY `idx_rapor_turu` (`rapor_turu`),
  KEY `idx_olusturulma_tarihi` (`olusturulma_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrp_raporlar`
--

LOCK TABLES `mrp_raporlar` WRITE;
/*!40000 ALTER TABLE `mrp_raporlar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_raporlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mrp_teslim_takvimi`
--

DROP TABLE IF EXISTS `mrp_teslim_takvimi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrp_teslim_takvimi` (
  `takvim_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `aktivite_turu` enum('malzeme_siparisi','uretim_baslangic','uretim_bitis','musteri_teslim') NOT NULL,
  `aktivite_adi` varchar(255) NOT NULL,
  `planlanan_tarih` date NOT NULL,
  `gerceklesen_tarih` date DEFAULT NULL,
  `durum` enum('beklemede','tamamlandi','gecti','iptal') DEFAULT 'beklemede',
  PRIMARY KEY (`takvim_id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_planlanan_tarih` (`planlanan_tarih`),
  CONSTRAINT `mrp_teslim_takvimi_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `mrp_planlama` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrp_teslim_takvimi`
--

LOCK TABLES `mrp_teslim_takvimi` WRITE;
/*!40000 ALTER TABLE `mrp_teslim_takvimi` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_teslim_takvimi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `musteri_geri_bildirimleri`
--

DROP TABLE IF EXISTS `musteri_geri_bildirimleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `musteri_geri_bildirimleri` (
  `geri_bildirim_id` int(11) NOT NULL AUTO_INCREMENT,
  `musteri_id` int(11) NOT NULL,
  `musteri_adi` varchar(255) NOT NULL,
  `siparis_id` int(11) DEFAULT NULL,
  `urun_kodu` int(11) DEFAULT NULL,
  `urun_ismi` varchar(255) DEFAULT NULL,
  `puanlama` int(11) DEFAULT NULL,
  `baslik` varchar(255) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `geri_bildirim_turu` varchar(20) DEFAULT NULL,
  `gizlilik_durumu` tinyint(1) DEFAULT 0,
  `cevaplandi_mi` tinyint(1) DEFAULT 0,
  `cevap_metni` text DEFAULT NULL,
  `olusturma_tarihi` datetime DEFAULT current_timestamp(),
  `guncelleme_tarihi` datetime DEFAULT NULL,
  PRIMARY KEY (`geri_bildirim_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteri_geri_bildirimleri`
--

LOCK TABLES `musteri_geri_bildirimleri` WRITE;
/*!40000 ALTER TABLE `musteri_geri_bildirimleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `musteri_geri_bildirimleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `musteriler`
--

DROP TABLE IF EXISTS `musteriler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `musteriler` (
  `musteri_id` int(11) NOT NULL AUTO_INCREMENT,
  `musteri_adi` varchar(255) NOT NULL,
  `vergi_no_tc` varchar(20) DEFAULT NULL,
  `adres` text DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `e_posta` varchar(255) DEFAULT NULL,
  `sistem_sifresi` varchar(255) DEFAULT NULL,
  `aciklama_notlar` text DEFAULT NULL,
  `giris_yetkisi` tinyint(1) DEFAULT 0,
  `telefon_2` varchar(20) DEFAULT NULL,
  `stok_goruntuleme_yetkisi` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`musteri_id`),
  UNIQUE KEY `musteri_id` (`musteri_id`),
  UNIQUE KEY `musteri_adi` (`musteri_adi`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteriler`
--

LOCK TABLES `musteriler` WRITE;
/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
INSERT INTO `musteriler` VALUES (1,'Alper Bey','','','05416144274','','$2y$10$Tt276wa8uaEkWczxX.GJE.AYUadNpdP8POwHwL7OyjQE1V448lmwW','',1,'',1);
/*!40000 ALTER TABLE `musteriler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `net_esans_ihtiyaclari`
--

DROP TABLE IF EXISTS `net_esans_ihtiyaclari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `net_esans_ihtiyaclari` (
  `ihtiyac_id` int(11) NOT NULL AUTO_INCREMENT,
  `esans_kodu` varchar(50) NOT NULL,
  `esans_ismi` varchar(255) NOT NULL,
  `stok_miktari` decimal(10,2) DEFAULT 0.00,
  `kritik_stok` decimal(10,2) DEFAULT 0.00,
  `siparis_miktari` decimal(10,2) DEFAULT 0.00,
  `net_ihtiyac` decimal(10,2) DEFAULT 0.00,
  `uretimdeki_miktar` decimal(10,2) DEFAULT 0.00,
  `gercek_ihtiyac` decimal(10,2) DEFAULT 0.00,
  `hesaplama_tarihi` datetime DEFAULT current_timestamp(),
  `durum` varchar(50) DEFAULT 'aktif',
  PRIMARY KEY (`ihtiyac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `net_esans_ihtiyaclari`
--

LOCK TABLES `net_esans_ihtiyaclari` WRITE;
/*!40000 ALTER TABLE `net_esans_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `net_esans_ihtiyaclari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `net_urun_ihtiyaclari`
--

DROP TABLE IF EXISTS `net_urun_ihtiyaclari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `net_urun_ihtiyaclari` (
  `ihtiyac_id` int(11) NOT NULL AUTO_INCREMENT,
  `urun_kodu` int(11) NOT NULL,
  `urun_ismi` varchar(255) NOT NULL,
  `stok_miktari` int(11) DEFAULT 0,
  `kritik_stok` int(11) DEFAULT 0,
  `siparis_miktari` int(11) DEFAULT 0,
  `net_ihtiyac` decimal(10,2) DEFAULT 0.00,
  `uretimdeki_miktar` decimal(10,2) DEFAULT 0.00,
  `gercek_ihtiyac` decimal(10,2) DEFAULT 0.00,
  `hesaplama_tarihi` datetime DEFAULT current_timestamp(),
  `durum` varchar(50) DEFAULT 'aktif',
  PRIMARY KEY (`ihtiyac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `net_urun_ihtiyaclari`
--

LOCK TABLES `net_urun_ihtiyaclari` WRITE;
/*!40000 ALTER TABLE `net_urun_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `net_urun_ihtiyaclari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personel_avanslar`
--

DROP TABLE IF EXISTS `personel_avanslar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personel_avanslar` (
  `avans_id` int(11) NOT NULL AUTO_INCREMENT,
  `personel_id` int(11) NOT NULL,
  `personel_adi` varchar(255) NOT NULL,
  `avans_tutari` decimal(10,2) NOT NULL,
  `avans_tarihi` date NOT NULL,
  `donem_yil` int(4) NOT NULL,
  `donem_ay` int(2) NOT NULL,
  `odeme_tipi` varchar(50) DEFAULT 'Nakit',
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_adi` varchar(255) DEFAULT NULL,
  `kayit_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `maas_odemesinde_kullanildi` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`avans_id`),
  KEY `idx_personel_id` (`personel_id`),
  KEY `idx_donem` (`donem_yil`,`donem_ay`),
  CONSTRAINT `personel_avanslar_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personeller` (`personel_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_avanslar`
--

LOCK TABLES `personel_avanslar` WRITE;
/*!40000 ALTER TABLE `personel_avanslar` DISABLE KEYS */;
INSERT INTO `personel_avanslar` VALUES (1,303,'Ahmet Yılmaz',10000.00,'2026-02-24',2026,2,'Nakit','',1,'Admin User','2026-02-24 08:47:39',1);
/*!40000 ALTER TABLE `personel_avanslar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personel_izinleri`
--

DROP TABLE IF EXISTS `personel_izinleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personel_izinleri` (
  `izin_id` int(11) NOT NULL AUTO_INCREMENT,
  `personel_id` int(11) NOT NULL,
  `izin_anahtari` varchar(255) NOT NULL,
  PRIMARY KEY (`izin_id`),
  UNIQUE KEY `personel_izin_unique` (`personel_id`,`izin_anahtari`),
  CONSTRAINT `personel_izinleri_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personeller` (`personel_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_izinleri`
--

LOCK TABLES `personel_izinleri` WRITE;
/*!40000 ALTER TABLE `personel_izinleri` DISABLE KEYS */;
INSERT INTO `personel_izinleri` VALUES (2,303,'action:musteriler:create'),(3,303,'action:musteriler:edit'),(1,303,'page:view:musteriler');
/*!40000 ALTER TABLE `personel_izinleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personel_maas_odemeleri`
--

DROP TABLE IF EXISTS `personel_maas_odemeleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personel_maas_odemeleri` (
  `odeme_id` int(11) NOT NULL AUTO_INCREMENT,
  `personel_id` int(11) NOT NULL,
  `personel_adi` varchar(255) NOT NULL,
  `donem_yil` int(4) NOT NULL,
  `donem_ay` int(2) NOT NULL,
  `aylik_brut_ucret` decimal(10,2) NOT NULL,
  `avans_toplami` decimal(10,2) DEFAULT 0.00,
  `net_odenen` decimal(10,2) NOT NULL,
  `odeme_tarihi` date NOT NULL,
  `odeme_tipi` varchar(50) DEFAULT 'Havale',
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_adi` varchar(255) DEFAULT NULL,
  `kayit_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `gider_kayit_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`odeme_id`),
  KEY `idx_personel_id` (`personel_id`),
  KEY `idx_donem` (`donem_yil`,`donem_ay`),
  CONSTRAINT `personel_maas_odemeleri_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personeller` (`personel_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_maas_odemeleri`
--

LOCK TABLES `personel_maas_odemeleri` WRITE;
/*!40000 ALTER TABLE `personel_maas_odemeleri` DISABLE KEYS */;
INSERT INTO `personel_maas_odemeleri` VALUES (2,303,'Ahmet Yılmaz',2026,2,50000.00,10000.00,40000.00,'2026-02-24','Havale','',1,'Admin User','2026-02-24 08:48:21',3);
/*!40000 ALTER TABLE `personel_maas_odemeleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personeller`
--

DROP TABLE IF EXISTS `personeller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personeller` (
  `personel_id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_soyad` varchar(255) NOT NULL,
  `tc_kimlik_no` varchar(11) NOT NULL,
  `dogum_tarihi` date DEFAULT NULL,
  `ise_giris_tarihi` date DEFAULT NULL,
  `pozisyon` varchar(100) DEFAULT NULL,
  `departman` varchar(100) DEFAULT NULL,
  `e_posta` varchar(255) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `adres` text DEFAULT NULL,
  `notlar` text DEFAULT NULL,
  `sistem_sifresi` varchar(255) DEFAULT NULL,
  `telefon_2` varchar(20) DEFAULT NULL,
  `bordrolu_calisan_mi` tinyint(1) DEFAULT 0,
  `aylik_brut_ucret` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`personel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=304 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personeller`
--

LOCK TABLES `personeller` WRITE;
/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Yönetim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$z56pgRUputjO7M5.Pp0W1eHOgVJ16GX3OKYtPi4VGenFweT8xUidK',NULL,0,0.00),(303,'Ahmet Yılmaz','','1980-05-10','2026-01-14','Depo Müdürü','Depo Yönetimi','','05515555555','','','$2y$10$3qTE/LleOldQc64kkU/7sujH/azAbuUJQPTKMK/UJpkWDFLxpRCpG','',1,50000.00);
/*!40000 ALTER TABLE `personeller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satinalma_siparis_kalemleri`
--

DROP TABLE IF EXISTS `satinalma_siparis_kalemleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satinalma_siparis_kalemleri` (
  `kalem_id` int(11) NOT NULL AUTO_INCREMENT,
  `siparis_id` int(11) NOT NULL,
  `malzeme_kodu` int(11) NOT NULL,
  `malzeme_adi` varchar(255) NOT NULL,
  `miktar` decimal(10,2) NOT NULL,
  `birim` varchar(50) DEFAULT 'adet',
  `birim_fiyat` decimal(10,2) DEFAULT 0.00,
  `para_birimi` varchar(10) DEFAULT 'TRY',
  `toplam_fiyat` decimal(15,2) DEFAULT 0.00,
  `teslim_edilen_miktar` decimal(10,2) DEFAULT 0.00,
  `aciklama` text DEFAULT NULL,
  PRIMARY KEY (`kalem_id`),
  KEY `idx_siparis` (`siparis_id`),
  KEY `idx_malzeme` (`malzeme_kodu`),
  CONSTRAINT `fk_satinalma_siparis` FOREIGN KEY (`siparis_id`) REFERENCES `satinalma_siparisler` (`siparis_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satinalma_siparis_kalemleri`
--

LOCK TABLES `satinalma_siparis_kalemleri` WRITE;
/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `satinalma_siparis_kalemleri` VALUES (18,4,1,'Birinci Ürün, etiket',190.00,'Adet',10.00,'USD',1900.00,190.00,''),(19,4,4,'Birinci Ürün, paket',190.00,'Adet',22.00,'TL',4180.00,190.00,''),(20,4,5,'Birinci Ürün, jelatin',160.00,'Adet',14.00,'TL',2240.00,160.00,''),(21,4,6,'Birinci Ürün, takım',150.00,'Adet',13.00,'TL',1950.00,150.00,''),(22,5,2,'Birinci Ürün, kutu',190.00,'adet',12.00,'TL',2280.00,190.00,''),(24,7,3,'Birinci Ürün, ham esans',100.00,'lt',10.00,'TL',1000.00,100.00,'');
/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satinalma_siparisler`
--

DROP TABLE IF EXISTS `satinalma_siparisler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satinalma_siparisler` (
  `siparis_id` int(11) NOT NULL AUTO_INCREMENT,
  `siparis_no` varchar(20) NOT NULL,
  `tedarikci_id` int(11) NOT NULL,
  `tedarikci_adi` varchar(255) NOT NULL,
  `siparis_tarihi` date NOT NULL,
  `istenen_teslim_tarihi` date DEFAULT NULL,
  `durum` enum('taslak','onaylandi','gonderildi','kismen_teslim','tamamlandi','iptal','kapatildi') DEFAULT 'taslak',
  `odeme_durumu` enum('bekliyor','kismi_odeme','odendi','iptal') DEFAULT 'bekliyor',
  `odeme_yontemi` varchar(50) DEFAULT NULL,
  `toplam_tutar` decimal(15,2) DEFAULT 0.00,
  `para_birimi` varchar(10) DEFAULT 'TRY',
  `aciklama` text DEFAULT NULL,
  `olusturan_id` int(11) DEFAULT NULL,
  `olusturan_adi` varchar(255) DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `guncelleme_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`siparis_id`),
  UNIQUE KEY `siparis_no` (`siparis_no`),
  KEY `idx_tedarikci` (`tedarikci_id`),
  KEY `idx_durum` (`durum`),
  KEY `idx_tarih` (`siparis_tarihi`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satinalma_siparisler`
--

LOCK TABLES `satinalma_siparisler` WRITE;
/*!40000 ALTER TABLE `satinalma_siparisler` DISABLE KEYS */;
INSERT INTO `satinalma_siparisler` VALUES (4,'PO-2026-00001',2,'Aydınel','2026-02-23','2026-02-28','tamamlandi','bekliyor',NULL,10270.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-02-23 22:21:27','2026-02-23 22:29:08'),(5,'PO-2026-00002',2,'Aydınel','2026-02-23','2026-02-28','kapatildi','bekliyor',NULL,2280.00,'TL','',1,'Admin User','2026-02-23 22:23:16','2026-02-23 22:25:40'),(7,'PO-2026-00004',1,'Barış Günhan','2026-02-24','2026-02-26','tamamlandi','bekliyor',NULL,1000.00,'TL','',1,'Admin User','2026-02-24 00:09:29','2026-02-24 00:10:03');
/*!40000 ALTER TABLE `satinalma_siparisler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siparis_kalemleri`
--

DROP TABLE IF EXISTS `siparis_kalemleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siparis_kalemleri` (
  `siparis_id` int(11) NOT NULL,
  `urun_kodu` int(11) NOT NULL,
  `urun_ismi` varchar(255) NOT NULL,
  `adet` int(11) NOT NULL,
  `birim` varchar(50) DEFAULT NULL,
  `birim_fiyat` decimal(10,2) DEFAULT NULL,
  `toplam_tutar` decimal(10,2) DEFAULT NULL,
  `para_birimi` varchar(10) DEFAULT 'TRY'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparis_kalemleri`
--

LOCK TABLES `siparis_kalemleri` WRITE;
/*!40000 ALTER TABLE `siparis_kalemleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `siparis_kalemleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siparisler`
--

DROP TABLE IF EXISTS `siparisler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siparisler` (
  `siparis_id` int(11) NOT NULL AUTO_INCREMENT,
  `musteri_id` int(11) NOT NULL,
  `musteri_adi` varchar(255) NOT NULL,
  `tarih` datetime DEFAULT current_timestamp(),
  `durum` varchar(20) DEFAULT 'beklemede',
  `toplam_adet` int(11) DEFAULT 0,
  `olusturan_musteri` varchar(255) DEFAULT NULL,
  `onaylayan_personel_id` int(11) DEFAULT NULL,
  `onaylayan_personel_adi` varchar(255) DEFAULT NULL,
  `onay_tarihi` datetime DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `odeme_durumu` enum('bekliyor','odendi','kismi_odendi') DEFAULT 'bekliyor',
  `odeme_yontemi` varchar(50) DEFAULT NULL,
  `odenen_tutar` decimal(10,2) DEFAULT 0.00,
  `para_birimi` varchar(3) NOT NULL DEFAULT 'TL',
  PRIMARY KEY (`siparis_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparisler`
--

LOCK TABLES `siparisler` WRITE;
/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
/*!40000 ALTER TABLE `siparisler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sirket_kasasi`
--

DROP TABLE IF EXISTS `sirket_kasasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sirket_kasasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `para_birimi` varchar(3) NOT NULL,
  `bakiye` decimal(15,2) NOT NULL DEFAULT 0.00,
  `guncelleme_tarihi` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `para_birimi` (`para_birimi`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sirket_kasasi`
--

LOCK TABLES `sirket_kasasi` WRITE;
/*!40000 ALTER TABLE `sirket_kasasi` DISABLE KEYS */;
INSERT INTO `sirket_kasasi` VALUES (1,'TL',80000.00,'2026-02-24 12:00:27');
/*!40000 ALTER TABLE `sirket_kasasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sistem_kullanicilari`
--

DROP TABLE IF EXISTS `sistem_kullanicilari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sistem_kullanicilari` (
  `kullanici_id` int(11) NOT NULL AUTO_INCREMENT,
  `taraf` varchar(50) NOT NULL,
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(100) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `sifre` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`kullanici_id`),
  UNIQUE KEY `kullanici_adi` (`kullanici_adi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sistem_kullanicilari`
--

LOCK TABLES `sistem_kullanicilari` WRITE;
/*!40000 ALTER TABLE `sistem_kullanicilari` DISABLE KEYS */;
/*!40000 ALTER TABLE `sistem_kullanicilari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_hareket_kayitlari`
--

DROP TABLE IF EXISTS `stok_hareket_kayitlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_hareket_kayitlari` (
  `hareket_id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime DEFAULT current_timestamp(),
  `stok_turu` varchar(50) NOT NULL,
  `kod` varchar(50) NOT NULL,
  `isim` varchar(255) NOT NULL,
  `birim` varchar(50) NOT NULL,
  `miktar` decimal(10,2) NOT NULL,
  `yon` varchar(10) NOT NULL,
  `hareket_turu` varchar(50) NOT NULL,
  `depo` varchar(255) DEFAULT NULL,
  `raf` varchar(100) DEFAULT NULL,
  `tank_kodu` varchar(50) DEFAULT NULL,
  `ilgili_belge_no` varchar(100) DEFAULT NULL,
  `is_emri_numarasi` int(11) DEFAULT NULL,
  `musteri_id` int(11) DEFAULT NULL,
  `musteri_adi` varchar(255) DEFAULT NULL,
  `aciklama` text NOT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_adi` varchar(255) DEFAULT NULL,
  `tedarikci_ismi` varchar(255) DEFAULT NULL,
  `tedarikci_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`hareket_id`),
  KEY `idx_tedarikci_ismi` (`tedarikci_ismi`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareket_kayitlari`
--

LOCK TABLES `stok_hareket_kayitlari` WRITE;
/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
INSERT INTO `stok_hareket_kayitlari` VALUES (1,'2026-02-24 01:25:18','malzeme','2','Birinci Ürün, kutu','adet',190.00,'giris','mal_kabul','Giriş Depo','A rafı',NULL,'',NULL,NULL,NULL,'Malı aldık... [Sozlesme ID: 4] [Sipariş: PO-2026-00002]',1,'Admin User','Aydınel',2),(2,'2026-02-24 01:26:19','malzeme','1','Birinci Ürün, etiket','adet',190.00,'giris','mal_kabul','Giriş Depo','A rafı',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 1] [Sipariş: PO-2026-00001]',1,'Admin User','Aydınel',2),(3,'2026-02-24 01:28:03','malzeme','4','Birinci Ürün, paket','adet',190.00,'giris','mal_kabul','Giriş Depo','A rafı',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 5] [Sipariş: PO-2026-00001]',1,'Admin User','Aydınel',2),(4,'2026-02-24 01:28:37','malzeme','5','Birinci Ürün, jelatin','adet',160.00,'giris','mal_kabul','Giriş Depo','A rafı',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 3] [Sipariş: PO-2026-00001]',1,'Admin User','Aydınel',2),(5,'2026-02-24 01:29:08','malzeme','6','Birinci Ürün, takım','adet',150.00,'giris','mal_kabul','Giriş Depo','B Rafı',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 6] [Sipariş: PO-2026-00001]',1,'Admin User','Aydınel',2),(6,'2026-02-24 02:15:01','malzeme','1','Birinci Ürün, etiket','adet',10.00,'giris','sayim_fazlasi','Giriş Depo','A rafı','','',NULL,NULL,NULL,'stokta fazla varmış.',1,'Admin User','',NULL),(7,'2026-02-24 02:15:37','malzeme','1','Birinci Ürün, etiket','adet',30.00,'cikis','fire','Giriş Depo','A rafı','','',NULL,NULL,NULL,'Fire çıktı...',1,'Admin User','',NULL),(8,'2026-02-24 02:16:18','malzeme','1','Birinci Ürün, etiket','adet',170.00,'cikis','transfer','Giriş Depo','A rafı','','',NULL,NULL,NULL,'Stok transferi - Kaynak: Giriş Depo/A rafı -> Hedef: Giriş Depo/B Rafı',1,'Admin User',NULL,NULL),(9,'2026-02-24 02:16:18','malzeme','1','Birinci Ürün, etiket','adet',170.00,'giris','transfer','Giriş Depo','B Rafı','','',NULL,NULL,NULL,'Stok transferi - Kaynak: Giriş Depo/A rafı -> Hedef: Giriş Depo/B Rafı',1,'Admin User',NULL,NULL),(10,'2026-02-24 02:21:29','etiket','1','Birinci Ürün, etiket','adet',190.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(11,'2026-02-24 02:21:29','kutu','2','Birinci Ürün, kutu','adet',190.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(12,'2026-02-24 02:21:29','paket','4','Birinci Ürün, paket','adet',190.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(13,'2026-02-24 02:21:29','jelatin','5','Birinci Ürün, jelatin','adet',190.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(14,'2026-02-24 02:21:29','takm','6','Birinci Ürün, takım','adet',190.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(15,'2026-02-24 02:21:29','esans','ES-260223-276','Birinci Ürün, Esans','adet',19.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(16,'2026-02-24 02:21:59','Ürün','2','Birinci Ürün','adet',190.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(18,'2026-02-24 03:10:03','malzeme','3','Birinci Ürün, ham esans','lt',100.00,'giris','mal_kabul','Giriş Depo','A rafı',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 2] [Sipariş: PO-2026-00004]',1,'Admin User','Barış Günhan',1),(19,'2026-02-24 03:10:53','Bileşen','3','Birinci Ürün, ham esans','lt',80.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(20,'2026-02-24 03:10:53','Bileşen','7','Alkol','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(21,'2026-02-24 03:10:53','Bileşen','8','Saf Su','lt',5.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(22,'2026-02-24 03:11:11','Esans','ES-260223-276','Birinci Ürün, Esans','lt',100.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(23,'2026-02-24 10:07:55','malzeme','7','Alkol','lt',15.00,'giris','sayim_fazlasi','Giriş Depo','A rafı','','',NULL,NULL,NULL,'Fazlalık çıktı...',1,'Admin User','',NULL),(24,'2026-02-24 10:08:44','malzeme','7','Alkol','lt',10.00,'cikis','fire','Giriş Depo','A rafı','','',NULL,NULL,NULL,'Üretimde fire çıktı..',1,'Admin User','',NULL),(25,'2026-02-24 10:10:02','malzeme','7','Alkol','lt',20.00,'cikis','sayim_eksigi','Giriş Depo','A rafı','','',NULL,NULL,NULL,'Sayım eksiği...',1,'Admin User','',NULL),(26,'2026-02-24 10:10:42','malzeme','8','Saf Su','lt',9995.00,'cikis','transfer','Giriş Depo','A rafı','','',NULL,NULL,NULL,'Stok transferi - Kaynak: Giriş Depo/A rafı -> Hedef: Merkez Depo/Raf 1',1,'Admin User',NULL,NULL),(27,'2026-02-24 10:10:42','malzeme','8','Saf Su','lt',9995.00,'giris','transfer','Merkez Depo','Raf 1','','',NULL,NULL,NULL,'Stok transferi - Kaynak: Giriş Depo/A rafı -> Hedef: Merkez Depo/Raf 1',1,'Admin User',NULL,NULL),(28,'2026-02-24 10:16:44','Bileşen','3','Birinci Ürün, ham esans','lt',8.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(29,'2026-02-24 10:16:44','Bileşen','7','Alkol','lt',1.50,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(30,'2026-02-24 10:16:44','Bileşen','8','Saf Su','lt',0.50,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(31,'2026-02-24 10:17:03','Esans','ES-260223-276','Birinci Ürün, Esans','lt',10.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(32,'2026-02-24 10:18:35','malzeme','1','Birinci Ürün, etiket','adet',190.00,'giris','mal_kabul','Giriş Depo','B Rafı',NULL,'',NULL,NULL,NULL,'. [Sozlesme ID: 1]',1,'Admin User','Aydınel',2),(33,'2026-02-24 10:18:52','malzeme','5','Birinci Ürün, jelatin','adet',100.00,'giris','mal_kabul','Giriş Depo','A rafı',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 3]',1,'Admin User','Aydınel',2),(34,'2026-02-24 10:19:14','malzeme','2','Birinci Ürün, kutu','adet',200.00,'giris','mal_kabul','Giriş Depo','A rafı',NULL,'',NULL,NULL,NULL,'. [Sozlesme ID: 4]',1,'Admin User','Aydınel',2),(35,'2026-02-24 10:19:30','malzeme','4','Birinci Ürün, paket','adet',23.00,'giris','mal_kabul','Giriş Depo','A rafı',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 5]',1,'Admin User','Aydınel',2),(36,'2026-02-24 10:27:03','malzeme','6','Birinci Ürün, takım','adet',10.00,'giris','mal_kabul','Giriş Depo','B Rafı',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 6]',1,'Admin User','Aydınel',2),(37,'2026-02-24 10:28:14','etiket','1','Birinci Ürün, etiket','adet',10.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(38,'2026-02-24 10:28:14','kutu','2','Birinci Ürün, kutu','adet',10.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(39,'2026-02-24 10:28:14','paket','4','Birinci Ürün, paket','adet',10.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(40,'2026-02-24 10:28:14','jelatin','5','Birinci Ürün, jelatin','adet',10.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(41,'2026-02-24 10:28:14','takm','6','Birinci Ürün, takım','adet',10.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(42,'2026-02-24 10:28:14','esans','ES-260223-276','Birinci Ürün, Esans','adet',1.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(43,'2026-02-24 10:28:25','Ürün','2','Birinci Ürün','adet',10.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL);
/*!40000 ALTER TABLE `stok_hareket_kayitlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_hareketleri_sozlesmeler`
--

DROP TABLE IF EXISTS `stok_hareketleri_sozlesmeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_hareketleri_sozlesmeler` (
  `hareket_id` int(11) NOT NULL,
  `sozlesme_id` int(11) NOT NULL,
  `malzeme_kodu` int(11) DEFAULT NULL,
  `kullanilan_miktar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tarih` timestamp NOT NULL DEFAULT current_timestamp(),
  `birim_fiyat` decimal(10,2) DEFAULT NULL,
  `para_birimi` varchar(10) DEFAULT NULL,
  `tedarikci_adi` varchar(255) DEFAULT NULL,
  `tedarikci_id` int(11) DEFAULT NULL,
  `baslangic_tarihi` date DEFAULT NULL,
  `bitis_tarihi` date DEFAULT NULL,
  KEY `idx_hareket_id` (`hareket_id`),
  KEY `idx_sozlesme_id` (`sozlesme_id`),
  CONSTRAINT `fk_stokhareketleri_sozlesmeler_hareket_id` FOREIGN KEY (`hareket_id`) REFERENCES `stok_hareket_kayitlari` (`hareket_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stokhareketleri_sozlesmeler_sozlesme_id` FOREIGN KEY (`sozlesme_id`) REFERENCES `cerceve_sozlesmeler` (`sozlesme_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareketleri_sozlesmeler`
--

LOCK TABLES `stok_hareketleri_sozlesmeler` WRITE;
/*!40000 ALTER TABLE `stok_hareketleri_sozlesmeler` DISABLE KEYS */;
INSERT INTO `stok_hareketleri_sozlesmeler` VALUES (1,4,2,190.00,'2026-02-23 22:25:18',12.00,'TL','Aydınel',2,'2026-02-06','2026-03-06'),(2,1,1,190.00,'2026-02-23 22:26:19',10.00,'USD','Aydınel',2,'2026-02-03','2026-03-01'),(3,5,4,190.00,'2026-02-23 22:28:03',22.00,'TL','Aydınel',2,'2026-01-26','2026-02-27'),(4,3,5,160.00,'2026-02-23 22:28:37',14.00,'TL','Aydınel',2,'2026-02-06','2026-02-28'),(5,6,6,150.00,'2026-02-23 22:29:08',13.00,'TL','Aydınel',2,'2026-02-09','2026-03-01'),(18,2,3,100.00,'2026-02-24 00:10:03',10.00,'TL','Barış Günhan',1,'2026-02-02','2026-02-27'),(32,1,1,190.00,'2026-02-24 07:18:35',10.00,'USD','Aydınel',2,'2026-02-03','2026-03-01'),(33,3,5,100.00,'2026-02-24 07:18:52',14.00,'TL','Aydınel',2,'2026-02-06','2026-02-28'),(34,4,2,200.00,'2026-02-24 07:19:14',12.00,'TL','Aydınel',2,'2026-02-06','2026-03-06'),(35,5,4,23.00,'2026-02-24 07:19:30',22.00,'TL','Aydınel',2,'2026-01-26','2026-02-27'),(36,6,6,10.00,'2026-02-24 07:27:03',13.00,'TL','Aydınel',2,'2026-02-09','2026-03-01');
/*!40000 ALTER TABLE `stok_hareketleri_sozlesmeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taksit_detaylari`
--

DROP TABLE IF EXISTS `taksit_detaylari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taksit_detaylari` (
  `taksit_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `sira_no` int(11) NOT NULL,
  `vade_tarihi` date NOT NULL,
  `tutar` decimal(15,2) NOT NULL,
  `kalan_tutar` decimal(15,2) NOT NULL,
  `odenen_tutar` decimal(15,2) DEFAULT 0.00,
  `durum` enum('bekliyor','kismi_odendi','odendi','gecikmede') DEFAULT 'bekliyor',
  `odeme_tarihi` datetime DEFAULT NULL,
  PRIMARY KEY (`taksit_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `taksit_detaylari_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `taksit_planlari` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taksit_detaylari`
--

LOCK TABLES `taksit_detaylari` WRITE;
/*!40000 ALTER TABLE `taksit_detaylari` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_detaylari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taksit_planlari`
--

DROP TABLE IF EXISTS `taksit_planlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taksit_planlari` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `musteri_id` int(11) NOT NULL,
  `musteri_adi` varchar(255) DEFAULT NULL,
  `toplam_anapara` decimal(15,2) DEFAULT 0.00,
  `vade_farki_orani` decimal(5,2) DEFAULT 0.00 COMMENT 'Percentage',
  `vade_farki_tutari` decimal(15,2) DEFAULT 0.00 COMMENT 'Fixed amount or calculated',
  `toplam_odenecek` decimal(15,2) DEFAULT 0.00,
  `para_birimi` varchar(10) DEFAULT 'TL',
  `taksit_sayisi` int(11) NOT NULL,
  `olusturma_tarihi` datetime DEFAULT current_timestamp(),
  `baslangic_tarihi` date DEFAULT NULL,
  `durum` enum('aktif','tamamlandi','iptal') DEFAULT 'aktif',
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taksit_planlari`
--

LOCK TABLES `taksit_planlari` WRITE;
/*!40000 ALTER TABLE `taksit_planlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_planlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taksit_siparis_baglantisi`
--

DROP TABLE IF EXISTS `taksit_siparis_baglantisi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taksit_siparis_baglantisi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `siparis_id` int(11) NOT NULL,
  `tutar_katkisi` decimal(15,2) NOT NULL COMMENT 'How much of this order is covered by this plan',
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `taksit_siparis_baglantisi_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `taksit_planlari` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taksit_siparis_baglantisi`
--

LOCK TABLES `taksit_siparis_baglantisi` WRITE;
/*!40000 ALTER TABLE `taksit_siparis_baglantisi` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_siparis_baglantisi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tanklar`
--

DROP TABLE IF EXISTS `tanklar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tanklar` (
  `tank_id` int(11) NOT NULL AUTO_INCREMENT,
  `tank_kodu` varchar(50) NOT NULL,
  `tank_ismi` varchar(255) NOT NULL,
  `not_bilgisi` text DEFAULT NULL,
  `kapasite` decimal(10,2) NOT NULL,
  PRIMARY KEY (`tank_id`),
  UNIQUE KEY `tank_kodu` (`tank_kodu`),
  UNIQUE KEY `tank_id` (`tank_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tanklar`
--

LOCK TABLES `tanklar` WRITE;
/*!40000 ALTER TABLE `tanklar` DISABLE KEYS */;
INSERT INTO `tanklar` VALUES (3,'Tank1','Birinci Tankımız','Deneme...',100.00),(4,'Tank2','Üçyüzlük Tank','',300.00),(5,'Tank3','Tank 3','',120.00),(6,'Tank4','Tank 4','',60.00),(7,'Tank5','Tank5','',600.00),(8,'Tank6','Tank 6','',530.00),(9,'Tank7','Tank 7','',702.00);
/*!40000 ALTER TABLE `tanklar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tedarikciler`
--

DROP TABLE IF EXISTS `tedarikciler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tedarikciler` (
  `tedarikci_id` int(11) NOT NULL AUTO_INCREMENT,
  `tedarikci_adi` varchar(255) NOT NULL,
  `sektor` varchar(100) DEFAULT NULL,
  `vergi_no_tc` varchar(20) DEFAULT NULL,
  `adres` text DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `e_posta` varchar(255) DEFAULT NULL,
  `yetkili_kisi` varchar(255) DEFAULT NULL,
  `aciklama_notlar` text DEFAULT NULL,
  `telefon_2` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`tedarikci_id`),
  UNIQUE KEY `tedarikci_id` (`tedarikci_id`),
  UNIQUE KEY `tedarikci_adi` (`tedarikci_adi`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tedarikciler`
--

LOCK TABLES `tedarikciler` WRITE;
/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (1,'Barış Günhan','Kimya','','','','','Barış Bey','Önce parayı alıp sonra malı verin.',''),(2,'Aydınel','Genel','','','','','Özhan Aydın','','');
/*!40000 ALTER TABLE `tedarikciler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tekrarli_odeme_gecmisi`
--

DROP TABLE IF EXISTS `tekrarli_odeme_gecmisi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tekrarli_odeme_gecmisi` (
  `gecmis_id` int(11) NOT NULL AUTO_INCREMENT,
  `odeme_id` int(11) NOT NULL,
  `odeme_adi` varchar(255) NOT NULL,
  `odeme_tipi` varchar(100) NOT NULL,
  `tutar` decimal(10,2) NOT NULL,
  `donem_yil` int(4) NOT NULL,
  `donem_ay` int(2) NOT NULL,
  `odeme_tarihi` date NOT NULL,
  `odeme_yontemi` varchar(50) DEFAULT 'Havale',
  `aciklama` text DEFAULT NULL,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_adi` varchar(255) DEFAULT NULL,
  `kayit_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `gider_kayit_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`gecmis_id`),
  KEY `idx_odeme_id` (`odeme_id`),
  KEY `idx_donem` (`donem_yil`,`donem_ay`),
  CONSTRAINT `tekrarli_odeme_gecmisi_ibfk_1` FOREIGN KEY (`odeme_id`) REFERENCES `tekrarli_odemeler` (`odeme_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tekrarli_odeme_gecmisi`
--

LOCK TABLES `tekrarli_odeme_gecmisi` WRITE;
/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` DISABLE KEYS */;
INSERT INTO `tekrarli_odeme_gecmisi` VALUES (2,2,'Kira','Kira',20000.00,2026,2,'2026-02-24','Havale','',1,'Admin User','2026-02-24 09:00:27',5);
/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tekrarli_odemeler`
--

DROP TABLE IF EXISTS `tekrarli_odemeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tekrarli_odemeler` (
  `odeme_id` int(11) NOT NULL AUTO_INCREMENT,
  `odeme_adi` varchar(255) NOT NULL,
  `odeme_tipi` varchar(100) NOT NULL COMMENT 'Kira, Elektrik, Su, Do??algaz, ??nternet, Vergi, Sigorta, vb.',
  `tutar` decimal(10,2) NOT NULL,
  `odeme_gunu` int(2) NOT NULL COMMENT 'Ay??n ka????nda ??deme yap??lacak (1-31)',
  `alici_firma` varchar(255) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `kaydeden_personel_id` int(11) DEFAULT NULL,
  `kaydeden_personel_adi` varchar(255) DEFAULT NULL,
  `kayit_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`odeme_id`),
  KEY `idx_aktif` (`aktif`),
  KEY `idx_odeme_gunu` (`odeme_gunu`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tekrarli_odemeler`
--

LOCK TABLES `tekrarli_odemeler` WRITE;
/*!40000 ALTER TABLE `tekrarli_odemeler` DISABLE KEYS */;
INSERT INTO `tekrarli_odemeler` VALUES (2,'Kira','Kira',20000.00,1,'','',1,1,'Admin User','2026-02-24 08:59:50');
/*!40000 ALTER TABLE `tekrarli_odemeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urun_agaci`
--

DROP TABLE IF EXISTS `urun_agaci`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urun_agaci` (
  `urun_agaci_id` int(11) NOT NULL AUTO_INCREMENT,
  `urun_kodu` int(11) NOT NULL,
  `urun_ismi` varchar(255) NOT NULL,
  `bilesenin_malzeme_turu` varchar(100) NOT NULL,
  `bilesen_kodu` varchar(50) NOT NULL,
  `bilesen_ismi` varchar(255) NOT NULL,
  `bilesen_miktari` decimal(10,2) NOT NULL,
  `agac_turu` varchar(10) DEFAULT 'urun',
  PRIMARY KEY (`urun_agaci_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_agaci`
--

LOCK TABLES `urun_agaci` WRITE;
/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (1,2,'Birinci Ürün','etiket','1','Birinci Ürün, etiket',1.00,'urun'),(2,2,'Birinci Ürün','kutu','2','Birinci Ürün, kutu',1.00,'urun'),(3,2,'Birinci Ürün','paket','4','Birinci Ürün, paket',1.00,'urun'),(4,2,'Birinci Ürün','jelatin','5','Birinci Ürün, jelatin',1.00,'urun'),(5,2,'Birinci Ürün','takm','6','Birinci Ürün, takım',1.00,'urun'),(6,2,'Birinci Ürün','esans','ES-260223-276','Birinci Ürün, Esans',0.10,'urun'),(7,1,'Birinci Ürün, Esans','malzeme','3','Birinci Ürün, ham esans',0.80,'esans'),(8,1,'Birinci Ürün, Esans','malzeme','7','Alkol',0.15,'esans'),(9,1,'Birinci Ürün, Esans','malzeme','8','Saf Su',0.05,'esans');
/*!40000 ALTER TABLE `urun_agaci` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urun_fotograflari`
--

DROP TABLE IF EXISTS `urun_fotograflari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urun_fotograflari` (
  `fotograf_id` int(11) NOT NULL AUTO_INCREMENT,
  `urun_kodu` int(11) NOT NULL,
  `dosya_adi` varchar(255) NOT NULL,
  `dosya_yolu` varchar(500) NOT NULL,
  `sira_no` int(11) DEFAULT 0,
  `ana_fotograf` tinyint(1) DEFAULT 0,
  `yuklenme_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`fotograf_id`),
  KEY `idx_urun_kodu` (`urun_kodu`),
  CONSTRAINT `urun_fotograflari_ibfk_1` FOREIGN KEY (`urun_kodu`) REFERENCES `urunler` (`urun_kodu`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_fotograflari`
--

LOCK TABLES `urun_fotograflari` WRITE;
/*!40000 ALTER TABLE `urun_fotograflari` DISABLE KEYS */;
INSERT INTO `urun_fotograflari` VALUES (3,2,'istockphoto-1048707234-170667a.jpg','assets/urun_fotograflari/699c942f3260b_1771869231.jpg',1,0,'2026-02-23 17:53:51'),(4,2,'28a3866949fd98d548dff8a19c94f7a6.jpg','assets/urun_fotograflari/699c943436fe8_1771869236.jpg',2,1,'2026-02-23 17:53:56');
/*!40000 ALTER TABLE `urun_fotograflari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urunler`
--

DROP TABLE IF EXISTS `urunler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urunler` (
  `urun_kodu` int(11) NOT NULL AUTO_INCREMENT,
  `urun_ismi` varchar(255) NOT NULL,
  `not_bilgisi` text DEFAULT NULL,
  `stok_miktari` int(11) DEFAULT 0,
  `birim` varchar(50) DEFAULT 'adet',
  `satis_fiyati` decimal(10,2) NOT NULL,
  `satis_fiyati_para_birimi` varchar(3) DEFAULT 'TRY',
  `alis_fiyati` decimal(10,2) DEFAULT 0.00,
  `alis_fiyati_para_birimi` varchar(3) DEFAULT 'TRY',
  `kritik_stok_seviyesi` int(11) DEFAULT 0,
  `depo` varchar(255) DEFAULT NULL,
  `raf` varchar(100) DEFAULT NULL,
  `urun_tipi` enum('uretilen','hazir_alinan') DEFAULT 'uretilen',
  `son_maliyet` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`urun_kodu`),
  UNIQUE KEY `unique_urun_ismi` (`urun_ismi`),
  UNIQUE KEY `urun_kodu` (`urun_kodu`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

LOCK TABLES `urunler` WRITE;
/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (2,'Birinci Ürün','',210,'adet',100.00,'TRY',0.00,'TRY',200,'Giriş Depo','A rafı','uretilen',NULL);
/*!40000 ALTER TABLE `urunler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_esans_maliyetleri`
--

DROP TABLE IF EXISTS `v_esans_maliyetleri`;
/*!50001 DROP VIEW IF EXISTS `v_esans_maliyetleri`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_esans_maliyetleri` AS SELECT
 1 AS `esans_kodu`,
  1 AS `toplam_maliyet` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_urun_maliyetleri`
--

DROP TABLE IF EXISTS `v_urun_maliyetleri`;
/*!50001 DROP VIEW IF EXISTS `v_urun_maliyetleri`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_urun_maliyetleri` AS SELECT
 1 AS `urun_kodu`,
  1 AS `teorik_maliyet` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `cerceve_sozlesmeler_gecerlilik`
--

/*!50001 DROP VIEW IF EXISTS `cerceve_sozlesmeler_gecerlilik`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `cerceve_sozlesmeler_gecerlilik` AS select `cs`.`sozlesme_id` AS `sozlesme_id`,`cs`.`tedarikci_id` AS `tedarikci_id`,`cs`.`tedarikci_adi` AS `tedarikci_adi`,`cs`.`malzeme_kodu` AS `malzeme_kodu`,`cs`.`malzeme_ismi` AS `malzeme_ismi`,`cs`.`birim_fiyat` AS `birim_fiyat`,`cs`.`para_birimi` AS `para_birimi`,`cs`.`limit_miktar` AS `limit_miktar`,`cs`.`toplu_odenen_miktar` AS `toplu_odenen_miktar`,`cs`.`baslangic_tarihi` AS `baslangic_tarihi`,`cs`.`bitis_tarihi` AS `bitis_tarihi`,`cs`.`olusturan` AS `olusturan`,`cs`.`olusturulma_tarihi` AS `olusturulma_tarihi`,`cs`.`aciklama` AS `aciklama`,`cs`.`oncelik` AS `oncelik`,coalesce(`shs`.`toplam_kullanilan`,0) AS `toplam_mal_kabul_miktari`,`cs`.`limit_miktar` - coalesce(`shs`.`toplam_kullanilan`,0) AS `kalan_miktar`,case when `cs`.`bitis_tarihi` < curdate() then 0 when coalesce(`shs`.`toplam_kullanilan`,0) >= `cs`.`limit_miktar` then 0 else 1 end AS `gecerli_mi`,case when `cs`.`bitis_tarihi` < curdate() then 'Suresi Dolmus' when coalesce(`shs`.`toplam_kullanilan`,0) >= `cs`.`limit_miktar` then 'Limit Dolmus' else 'Gecerli' end AS `gecerlilik_durumu` from (`cerceve_sozlesmeler` `cs` left join (select `stok_hareketleri_sozlesmeler`.`sozlesme_id` AS `sozlesme_id`,sum(`stok_hareketleri_sozlesmeler`.`kullanilan_miktar`) AS `toplam_kullanilan` from `stok_hareketleri_sozlesmeler` where exists(select 1 from `stok_hareket_kayitlari` where `stok_hareket_kayitlari`.`hareket_id` = `stok_hareketleri_sozlesmeler`.`hareket_id` and `stok_hareket_kayitlari`.`hareket_turu` = 'mal_kabul' limit 1) group by `stok_hareketleri_sozlesmeler`.`sozlesme_id`) `shs` on(`cs`.`sozlesme_id` = `shs`.`sozlesme_id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_esans_maliyetleri`
--

/*!50001 DROP VIEW IF EXISTS `v_esans_maliyetleri`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_esans_maliyetleri` AS select `e`.`esans_kodu` AS `esans_kodu`,coalesce(sum(`ua`.`bilesen_miktari` * case `m`.`para_birimi` when 'USD' then `m`.`alis_fiyati` * (select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'dolar_kuru') when 'EUR' then `m`.`alis_fiyati` * (select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'euro_kuru') else `m`.`alis_fiyati` end),0) AS `toplam_maliyet` from ((`esanslar` `e` left join `urun_agaci` `ua` on((`ua`.`urun_kodu` = `e`.`esans_kodu` or `ua`.`urun_kodu` = cast(`e`.`esans_id` as char charset latin1)) and `ua`.`agac_turu` = 'esans')) left join `malzemeler` `m` on(trim(`ua`.`bilesen_kodu`) = trim(`m`.`malzeme_kodu`))) group by `e`.`esans_kodu` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_urun_maliyetleri`
--

/*!50001 DROP VIEW IF EXISTS `v_urun_maliyetleri`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_urun_maliyetleri` AS select `u`.`urun_kodu` AS `urun_kodu`,case when `u`.`urun_tipi` = 'hazir_alinan' then coalesce(`u`.`alis_fiyati`,0) else coalesce(sum(`ua`.`bilesen_miktari` * case when `m`.`alis_fiyati` is not null then case `m`.`para_birimi` when 'USD' then coalesce(`m`.`alis_fiyati`,0) * (select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'dolar_kuru') when 'EUR' then coalesce(`m`.`alis_fiyati`,0) * (select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'euro_kuru') else coalesce(`m`.`alis_fiyati`,0) end when `vem`.`toplam_maliyet` is not null then coalesce(`vem`.`toplam_maliyet`,0) else 0 end),0) end AS `teorik_maliyet` from (((`urunler` `u` left join `urun_agaci` `ua` on(`u`.`urun_kodu` = `ua`.`urun_kodu` and `ua`.`agac_turu` = 'urun')) left join `malzemeler` `m` on(`ua`.`bilesen_kodu` = `m`.`malzeme_kodu`)) left join `v_esans_maliyetleri` `vem` on(`ua`.`bilesen_kodu` = `vem`.`esans_kodu`)) group by `u`.`urun_kodu` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-26 22:01:54

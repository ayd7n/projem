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
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

LOCK TABLES `ayarlar` WRITE;
/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.8500'),(2,'euro_kuru','50.5070'),(3,'son_otomatik_yedek_tarihi','2025-12-29 03:02:29'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170');
/*!40000 ALTER TABLE `ayarlar` ENABLE KEYS */;
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
  PRIMARY KEY (`sozlesme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cerceve_sozlesmeler`
--

LOCK TABLES `cerceve_sozlesmeler` WRITE;
/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (9,10,'GÖKHAN ',96,'CHANEL NO5 JELATİN',180.00,'TL',1000,0,'2025-12-22','2025-12-30','Admin User','2025-12-22 11:46:42','',5),(10,10,'GÖKHAN ',77,'DİOR SAVAGE JELATİN',150.00,'TL',1000,0,'2025-12-22','2025-12-30','Admin User','2025-12-22 11:48:24','',5),(11,10,'GÖKHAN ',83,'KİLİAN , ANGELS SHARE jelatin',140.00,'TL',1000,0,'2025-12-22','2025-12-30','Admin User','2025-12-22 11:49:16','',5),(12,10,'GÖKHAN ',103,'SAVAGE ELİXER JELATİN',150.00,'TL',1000,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 12:06:11','',1),(13,10,'GÖKHAN ',90,'ysl y jilatin',180.00,'TL',1000,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 12:07:29','',1),(14,2,'MEHMET KIRMIZIGÜL',95,'CHANEL NO5 TAKIM',2.20,'USD',1000,950,'2025-12-12','2025-12-30','Admin User','2025-12-22 12:10:39','',1),(15,2,'MEHMET KIRMIZIGÜL',76,'DİOR SAVAGE TAKIM',1.90,'USD',1000,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 12:16:15','',1),(16,2,'MEHMET KIRMIZIGÜL',82,'KİLİAN , ANGELS SHARE takım',2.80,'USD',1000,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 13:54:55','',1),(17,2,'MEHMET KIRMIZIGÜL',102,'SAVAGE ELİXER TAKIM',2.70,'USD',1000,1000,'2025-12-12','2025-12-30','Admin User','2025-12-22 13:55:38','',1),(18,2,'MEHMET KIRMIZIGÜL',88,'ysl y takım',1.90,'USD',10000,2250,'2025-12-12','2025-12-30','Admin User','2025-12-22 13:56:27','',1),(19,5,'SARI ETİKET',94,'CHANEL NO5 TİKET',2.00,'TL',5000,1000,'2025-12-12','2025-12-30','Admin User','2025-12-22 13:58:06','',1),(20,5,'SARI ETİKET',72,'DİOR SAVAGE ETİKET',5.00,'TL',5000,5000,'2025-12-12','2025-12-30','Admin User','2025-12-22 13:59:36','',1),(21,5,'SARI ETİKET',81,'KİLİAN , ANGELS SHARE etiket',6.00,'TL',5000,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 14:00:32','',1),(22,5,'SARI ETİKET',101,'SAVAGE ELİXER ETİKET',8.00,'TL',5000,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 14:01:38','',1),(23,5,'SARI ETİKET',87,'ysl y etiket',4.00,'TL',25000,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 14:02:24','',1),(24,5,'SARI ETİKET',108,'DİOR SAVAGE HAM ESANS',120.00,'USD',15,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 18:13:41','',1),(25,4,'LUZKİM',108,'DİOR SAVAGE HAM ESANS',110.00,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-22 19:20:50','',1);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esans_is_emirleri`
--

LOCK TABLES `esans_is_emirleri` WRITE;
/*!40000 ALTER TABLE `esans_is_emirleri` DISABLE KEYS */;
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
INSERT INTO `esans_is_emri_malzeme_listesi` VALUES (3,31,'Medium Box','malzeme',6.00,'ml'),(3,37,'Ambalaj Malzemesi 2','malzeme',6.00,'ml'),(4,31,'Medium Box','malzeme',400.00,'ml'),(4,37,'Ambalaj Malzemesi 2','malzeme',400.00,'ml');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esanslar`
--

LOCK TABLES `esanslar` WRITE;
/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (9,'001','DİOR , SAVAGE','',0.00,'lt',3.00,'005','E 121'),(10,'002','chanel no 5','',0.00,'lt',3.00,'003','E 181'),(11,'004','bacarat red','',0.00,'lt',0.00,'',''),(12,'003','ysl y','',0.00,'lt',0.00,'',''),(13,'005','savage elixer','',0.00,'lt',0.00,'','');
/*!40000 ALTER TABLE `esanslar` ENABLE KEYS */;
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
  PRIMARY KEY (`gelir_id`),
  KEY `siparis_id` (`siparis_id`),
  KEY `musteri_id` (`musteri_id`),
  KEY `idx_para_birimi` (`para_birimi`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gelir_yonetimi`
--

LOCK TABLES `gelir_yonetimi` WRITE;
/*!40000 ALTER TABLE `gelir_yonetimi` DISABLE KEYS */;
INSERT INTO `gelir_yonetimi` VALUES (6,'2025-12-28 00:00:00',17.00,'USD','Sipariş Ödemesi','Sipariş No: #8 tahsilatı',1,'Admin User',8,'Nakit',1,'MEHMET FATİH GÜZEN');
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
  PRIMARY KEY (`gider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gider_yonetimi`
--

LOCK TABLES `gider_yonetimi` WRITE;
/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (27,'2025-12-09',100.00,'Malzeme Gideri','Saf Su için 50 adet ön ödeme',283,'Yedek Admin',NULL,'Diğer','Ahmet Kozmetik'),(29,'2025-12-08',200.00,'Diğer','Personele lahmacun ısmarladık.',283,'Yedek Admin','','Kredi Kartı','Hacıoğlu Lahmacun'),(34,'2025-12-09',10000.00,'Personel Avansı','Ahmet Yıldırım - 2025/12 dönemi avans ödemesi. ',283,'Yedek Admin',NULL,'Nakit','Ahmet Yıldırım'),(35,'2025-12-09',50000.00,'Personel Gideri','Ahmet Yıldırım - 2025/12 dönemi maaş ödemesi. ',283,'Yedek Admin',NULL,'Havale','Ahmet Yıldırım'),(36,'2025-12-09',30000.00,'Kira','Ofis Kirası - 2025/12 dönemi. ',283,'Yedek Admin',NULL,'Havale','Lila Gayrimenkul'),(37,'2025-12-09',12.50,'Fire Gideri','Fire kaydı - 212 Man Etiketi - 10 adet (53)',283,'Yedek Admin','Fire_Kaydi_1','Diğer','İç Gider'),(38,'2025-12-09',1900.00,'Malzeme Gideri','Saf Su için 950 adet ara ödeme',283,'Yedek Admin',NULL,'Diğer','Ahmet Kozmetik'),(39,'2025-12-17',2053466.80,'Malzeme Gideri','DİOR SAVAGE KUTU için 22000 adet ön ödeme (48.400,00 USD @ 42,4270)',1,'Admin User',NULL,'Diğer','MEHMET KIRMIZIGÜL'),(40,'2025-12-22',25000.00,'Malzeme Gideri','DİOR SAVAGE ETİKET için 5000 adet ara ödeme',1,'Admin User',NULL,'Diğer','SARI ETİKET'),(41,'2025-12-22',1900.00,'Malzeme Gideri','CHANEL NO5 TİKET için 950 adet ara ödeme',1,'Admin User',NULL,'Diğer','SARI ETİKET'),(42,'2025-12-22',40.00,'Fire Gideri','Fire kaydı - dior savage - 4 adet (44)',1,'Admin User','Fire_Kaydi_32','Diğer','İç Gider'),(43,'2025-12-22',88672.43,'Malzeme Gideri','CHANEL NO5 TAKIM için 950 adet ara ödeme (2.090,00 USD @ 42,4270)',1,'Admin User',NULL,'Diğer','MEHMET KIRMIZIGÜL'),(44,'2025-12-22',100.00,'Malzeme Gideri','CHANEL NO5 TİKET için 50 adet ara ödeme',1,'Admin User',NULL,'Diğer','SARI ETİKET'),(45,'2025-12-22',181375.43,'Malzeme Gideri','ysl y takım için 2250 adet ara ödeme (4.275,00 USD @ 42,4270)',1,'Admin User',NULL,'Diğer','MEHMET KIRMIZIGÜL'),(46,'2025-12-22',114552.90,'Malzeme Gideri','SAVAGE ELİXER TAKIM için 1000 adet ara ödeme (2.700,00 USD @ 42,4270)',1,'Admin User',NULL,'Diğer','MEHMET KIRMIZIGÜL');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `is_merkezleri`
--

LOCK TABLES `is_merkezleri` WRITE;
/*!40000 ALTER TABLE `is_merkezleri` DISABLE KEYS */;
INSERT INTO `is_merkezleri` VALUES (5,'AHMET ERSİN GÜZEN','ATÖLYE A'),(6,'ABUBEKİR ÖNEL','ATÖLYE B');
/*!40000 ALTER TABLE `is_merkezleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=994 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tablosu`
--

LOCK TABLES `log_tablosu` WRITE;
/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2025-11-26 09:44:47','test_kullanici','Bu bir test log kaydıdır.','CREATE','2025-11-26 06:44:47'),(2,'2025-11-26 09:45:40','test_kullanici','Test Ürün sisteme eklendi','CREATE','2025-11-26 06:45:40'),(3,'2025-11-26 09:45:40','test_kullanici','Test Ürün güncellendi','UPDATE','2025-11-26 06:45:40'),(4,'2025-11-26 09:45:40','test_kullanici','Test Ürün sistemden silindi','DELETE','2025-11-26 06:45:40'),(5,'2025-11-26 09:46:02','sistem','Melisa Rose Parfümü ürünü sisteme eklendi','CREATE','2025-11-26 06:46:02'),(6,'2025-11-26 09:46:02','sistem','Melisa Rose Parfümü ürünü Kır Çiçeği Parfümü olarak güncellendi','UPDATE','2025-11-26 06:46:02'),(7,'2025-11-26 09:46:02','sistem','Kır Çiçeği Parfümü ürünü sistemden silindi','DELETE','2025-11-26 06:46:02'),(8,'2025-11-26 09:46:02','sistem','Ahmet Yılmaz müşterisi sisteme eklendi','CREATE','2025-11-26 06:46:02'),(9,'2025-11-26 09:46:02','sistem','ABC Tedarik tedarikçisi sisteme eklendi','CREATE','2025-11-26 06:46:02'),(10,'2025-11-26 11:03:18','Admin User','Deneme Tankı adlı tank sisteme eklendi','CREATE','2025-11-26 08:03:18'),(11,'2025-11-26 11:06:21','Admin User','Güncellenmiş Tank adlı tank Güncellenmiş Tank olarak güncellendi','UPDATE','2025-11-26 08:06:21'),(12,'2025-11-26 11:06:35','Admin User','Bilinmeyen Tank adlı tank silindi','DELETE','2025-11-26 08:06:35'),(13,'2025-11-26 11:10:03','Admin User','Test Ürün ürün ağacına Test Malzeme bileşeni eklendi','CREATE','2025-11-26 08:10:03'),(14,'2025-11-26 11:10:21','Admin User','Test Ürün ürün ağacındaki Test Malzeme bileşeni Güncellenmiş Malzeme olarak güncellendi','UPDATE','2025-11-26 08:10:21'),(15,'2025-11-26 11:10:32','Admin User','Test Ürün ürün ağacından Güncellenmiş Malzeme bileşeni silindi','DELETE','2025-11-26 08:10:32'),(16,'2025-11-26 14:47:21','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:47:21'),(17,'2025-11-26 14:47:28','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:47:28'),(18,'2025-11-26 14:47:32','unknown','unknown oturumu kapattı (ID: unknown)','','2025-11-26 11:47:32'),(19,'2025-11-26 14:48:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:48:04'),(20,'2025-11-26 14:48:10','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 11:48:10'),(21,'2025-11-26 14:48:36','Ayse Kaya','Müşteri giriş yaptı (E-posta/Telefon: ayse.kaya@parfum.com)','','2025-11-26 11:48:36'),(22,'2025-11-26 14:56:59','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','','2025-11-26 11:56:59'),(23,'2025-11-26 14:58:03','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 3)','CREATE','2025-11-26 11:58:03'),(24,'2025-11-26 14:58:22','Ali Can','musteri oturumu kapattı (ID: 8)','','2025-11-26 11:58:22'),(25,'2025-11-26 16:20:13','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:20:13'),(26,'2025-11-26 16:20:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:20:14'),(27,'2025-11-26 16:21:21','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:21:21'),(28,'2025-11-26 16:21:22','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:21:22'),(29,'2025-11-26 16:45:37','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:45:37'),(30,'2025-11-26 16:45:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:45:39'),(31,'2025-11-26 16:47:07','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 13:47:07'),(32,'2025-11-26 16:47:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 13:47:09'),(33,'2025-11-26 17:05:50','test_kullanici','Bu bir test logudur','TEST','2025-11-26 14:05:50'),(34,'2025-11-26 21:12:54','Admin User','Telegram ayarları güncellendi','UPDATE','2025-11-26 18:12:54'),(35,'2025-11-26 21:15:28','Admin User','Telegram ayarları güncellendi','UPDATE','2025-11-26 18:15:28'),(36,'2025-11-26 21:15:37','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:15:37'),(37,'2025-11-26 21:15:43','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:15:43'),(38,'2025-11-26 21:16:18','Admin User','Tedarikci Isim10 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:18'),(39,'2025-11-26 21:16:29','Admin User','Tedarikci Isim100 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:29'),(40,'2025-11-26 21:16:41','Admin User','Tedarikci Isim16 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:41'),(41,'2025-11-26 21:16:58','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:16:58'),(42,'2025-11-26 21:17:05','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-26 18:17:05'),(43,'2025-11-26 21:17:43','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 4)','CREATE','2025-11-26 18:17:43'),(44,'2025-11-26 21:20:24','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 5)','CREATE','2025-11-26 18:20:24'),(45,'2025-11-26 21:20:49','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 6)','CREATE','2025-11-26 18:20:49'),(46,'2025-11-26 21:22:27','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 7)','CREATE','2025-11-26 18:22:27'),(47,'2025-11-26 21:23:37','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 8)','CREATE','2025-11-26 18:23:37'),(48,'2025-11-26 21:23:53','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-26 18:23:53'),(49,'2025-11-26 21:23:58','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:23:58'),(50,'2025-11-26 21:26:10','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:26:10'),(51,'2025-11-26 21:26:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:26:14'),(52,'2025-11-26 21:27:33','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:27:33'),(53,'2025-11-26 21:27:37','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:27:37'),(54,'2025-11-26 21:32:33','Admin User','Ali Can müşterisine ait 6 nolu sipariş iptal_edildi durumundan iptal_edildi durumuna güncellendi','UPDATE','2025-11-26 18:32:33'),(55,'2025-11-26 21:32:48','Admin User','Ali Can müşterisine ait 5 nolu sipariş onaylandi durumundan onaylandi durumuna güncellendi','UPDATE','2025-11-26 18:32:48'),(56,'2025-11-26 21:34:22','Admin User','Ali Can müşterisine ait 5 nolu siparişin durumu onaylandi oldu','UPDATE','2025-11-26 18:34:22'),(57,'2025-11-26 21:35:33','Admin User','Ali Can müşterisine ait 7 nolu sipariş silindi','DELETE','2025-11-26 18:35:33'),(58,'2025-11-26 21:35:42','Admin User','Ali Can müşterisine ait 8 nolu siparişin yeni durumu: Beklemede','UPDATE','2025-11-26 18:35:42'),(59,'2025-11-26 21:35:50','Admin User','Ali Can müşterisine ait 3 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-11-26 18:35:50'),(60,'2025-11-26 21:38:01','Admin User','Tedarikci Isim13 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:38:01'),(61,'2025-11-26 22:15:40','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 19:15:40'),(62,'2025-11-26 22:15:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 19:15:44'),(63,'2025-11-26 22:55:48','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-11-26 19:55:48'),(64,'2025-11-26 23:01:39','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için çerçeve sözleşme eklendi','CREATE','2025-11-26 20:01:39'),(65,'2025-11-26 23:22:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 20:22:36'),(66,'2025-11-27 08:16:34','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 05:16:34'),(67,'2025-11-27 08:16:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 05:16:39'),(68,'2025-11-27 09:45:06','SISTEM','Acil durum kullanıcısı ile en son yedekten geri yükleme yapıldı.','Kritik Eylem','2025-11-27 06:45:06'),(69,'2025-11-27 09:45:12','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 06:45:12'),(70,'2025-11-27 09:51:49','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 06:51:49'),(71,'2025-11-27 09:51:52','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 06:51:52'),(72,'2025-11-27 11:09:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 08:09:36'),(73,'2025-11-27 11:11:48','Admin User','10 evo ürünü için montaj iş emri oluşturuldu','CREATE','2025-11-27 08:11:48'),(74,'2025-11-27 11:21:00','Admin User','Perfume 2 ürünü için montaj iş emri oluşturuldu','CREATE','2025-11-27 08:21:00'),(75,'2025-11-27 11:25:02','Admin User','10 evo ürünü için montaj iş emri oluşturuldu','CREATE','2025-11-27 08:25:02'),(76,'2025-11-27 14:34:42','Admin User','Malzeme Gideri kategorisindeki 1200.00 TL tutarlı gider silindi','DELETE','2025-11-27 11:34:42'),(77,'2025-11-27 14:35:09','Admin User','Sarf Malzeme Gideri kategorisinde 56000 TL tutarında gider eklendi','CREATE','2025-11-27 11:35:09'),(78,'2025-11-27 15:07:08','Admin User','Sarf Malzeme Gideri kategorisindeki 56000.00 TL tutarlı gider güncellendi','UPDATE','2025-11-27 12:07:08'),(79,'2025-11-27 15:07:29','Admin User','Sarf Malzeme Gideri kategorisindeki 560.00 TL tutarlı gider güncellendi','UPDATE','2025-11-27 12:07:29'),(80,'2025-11-27 15:21:12','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 12:21:12'),(81,'2025-11-27 15:34:41','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 12:34:41'),(82,'2025-11-27 15:35:01','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 12:35:01'),(83,'2025-11-27 15:38:06','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 12:38:06'),(84,'2025-11-27 15:39:00','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 12:39:00'),(85,'2025-11-27 16:20:25','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 13:20:25'),(86,'2025-11-27 21:12:40','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 18:12:40'),(87,'2025-11-29 03:35:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 00:35:09'),(88,'2025-11-29 16:42:56','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 13:42:56'),(89,'2025-11-29 16:58:40','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 13:58:40'),(90,'2025-11-29 17:01:58','Admin User','10 evo ürününe fotoğraf eklendi','CREATE','2025-11-29 14:01:58'),(91,'2025-11-29 17:02:36','Admin User','10 evo ürününe fotoğraf eklendi','CREATE','2025-11-29 14:02:36'),(92,'2025-11-29 17:13:04','Admin User','10 evo ürününün ana fotoğrafı değiştirildi','UPDATE','2025-11-29 14:13:04'),(93,'2025-11-29 17:13:15','Admin User','10 evo ürününe fotoğraf eklendi','CREATE','2025-11-29 14:13:15'),(94,'2025-11-29 18:00:23','Admin User','Ambalaj Malzemesi 2 ürününe fotoğraf eklendi','CREATE','2025-11-29 15:00:23'),(95,'2025-11-29 18:08:58','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 15:08:58'),(96,'2025-11-29 18:09:01','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 15:09:01'),(97,'2025-11-29 18:09:27','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 15:09:27'),(98,'2025-11-29 18:09:34','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 15:09:34'),(99,'2025-11-29 18:33:28','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 15:33:28'),(100,'2025-11-29 18:33:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 15:33:36'),(101,'2025-11-29 18:36:13','Admin User','10 evo ürününden fotoğraf silindi','DELETE','2025-11-29 15:36:13'),(102,'2025-11-29 18:36:34','Admin User','Ambalaj Malzemesi 2 ürününden fotoğraf silindi','DELETE','2025-11-29 15:36:34'),(103,'2025-11-29 18:36:50','Admin User','Ambalaj Malzemesi 2 ürününe fotoğraf eklendi','CREATE','2025-11-29 15:36:50'),(104,'2025-11-29 18:41:06','Admin User','Ambalaj Malzemesi 2 ürününden fotoğraf silindi','DELETE','2025-11-29 15:41:06'),(105,'2025-11-29 18:41:18','Admin User','Ambalaj Malzemesi 2 ürününe fotoğraf eklendi','CREATE','2025-11-29 15:41:18'),(106,'2025-11-29 18:41:31','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 15:41:31'),(107,'2025-11-29 18:41:34','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 15:41:34'),(108,'2025-11-29 18:41:47','Admin User','10 evo ürününe fotoğraf eklendi','CREATE','2025-11-29 15:41:47'),(109,'2025-11-29 18:42:07','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 15:42:07'),(110,'2025-11-29 18:42:13','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 15:42:13'),(111,'2025-11-29 21:45:31','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 18:45:31'),(112,'2025-11-29 21:46:09','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 18:46:09'),(113,'2025-11-29 21:51:19','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 18:51:19'),(114,'2025-11-29 21:51:23','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 18:51:23'),(115,'2025-11-29 21:52:42','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 18:52:42'),(116,'2025-11-29 21:52:44','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 18:52:44'),(117,'2025-11-29 21:58:22','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 18:58:22'),(118,'2025-11-29 21:58:25','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 18:58:25'),(119,'2025-11-29 22:00:34','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 9)','CREATE','2025-11-29 19:00:34'),(120,'2025-11-29 22:00:53','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 10)','CREATE','2025-11-29 19:00:53'),(121,'2025-11-29 22:03:12','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 11)','CREATE','2025-11-29 19:03:12'),(122,'2025-11-29 22:04:38','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 12)','CREATE','2025-11-29 19:04:38'),(123,'2025-11-29 22:04:54','Ali Can','Sipariş iptal edildi (ID: 8)','UPDATE','2025-11-29 19:04:54'),(124,'2025-11-29 22:04:59','Ali Can','Sipariş iptal edildi (ID: 9)','UPDATE','2025-11-29 19:04:59'),(125,'2025-11-29 22:05:04','Ali Can','Sipariş iptal edildi (ID: 10)','UPDATE','2025-11-29 19:05:04'),(126,'2025-11-30 02:42:57','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 13)','CREATE','2025-11-29 23:42:57'),(127,'2025-11-30 02:43:14','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 23:43:14'),(128,'2025-11-30 02:43:29','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 23:43:29'),(129,'2025-11-30 02:58:18','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 23:58:18'),(130,'2025-11-30 03:31:13','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-30 00:31:13'),(131,'2025-11-30 03:31:26','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-30 00:31:26'),(132,'2025-11-30 03:32:29','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-30 00:32:29'),(133,'2025-11-30 03:35:59','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-30 00:35:59'),(134,'2025-11-30 03:40:16','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-30 00:40:16'),(135,'2025-12-03 07:31:10','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 04:31:10'),(136,'2025-12-03 07:31:31','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 04:31:31'),(137,'2025-12-03 07:31:36','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-03 04:31:36'),(138,'2025-12-03 07:35:21','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 14)','CREATE','2025-12-03 04:35:21'),(139,'2025-12-03 07:37:14','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-12-03 04:37:14'),(140,'2025-12-03 07:37:20','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 04:37:20'),(141,'2025-12-03 08:03:14','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 05:03:14'),(142,'2025-12-03 08:03:19','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-03 05:03:19'),(143,'2025-12-03 08:04:51','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 15)','CREATE','2025-12-03 05:04:51'),(144,'2025-12-03 08:04:59','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-12-03 05:04:59'),(145,'2025-12-03 08:05:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 05:05:04'),(146,'2025-12-03 10:52:45','Admin User','Tedarikçi B tedarikçisi Tedarikçi B olarak güncellendi','UPDATE','2025-12-03 07:52:45'),(147,'2025-12-03 10:53:01','Admin User','aaabbb tedarikçisi sisteme eklendi','CREATE','2025-12-03 07:53:01'),(148,'2025-12-03 10:53:20','Admin User','aaabbb tedarikçisi sistemden silindi','DELETE','2025-12-03 07:53:20'),(149,'2025-12-03 10:53:31','Admin User','a tedarikçisi sisteme eklendi','CREATE','2025-12-03 07:53:31'),(150,'2025-12-03 10:58:29','Admin User','a tedarikçisi sistemden silindi','DELETE','2025-12-03 07:58:29'),(151,'2025-12-03 10:58:40','Admin User','A tedarikçisi sisteme eklendi','CREATE','2025-12-03 07:58:40'),(152,'2025-12-03 10:58:51','Admin User','A tedarikçisi A olarak güncellendi','UPDATE','2025-12-03 07:58:51'),(153,'2025-12-03 10:58:56','Admin User','A tedarikçisi sistemden silindi','DELETE','2025-12-03 07:58:56'),(154,'2025-12-03 10:59:32','Admin User','Ali Can personelinin bilgileri güncellendi','UPDATE','2025-12-03 07:59:32'),(155,'2025-12-03 10:59:44','Admin User','Ali Can personeli sistemden silindi','DELETE','2025-12-03 07:59:44'),(156,'2025-12-03 11:00:08','Admin User','a personeli sisteme eklendi','CREATE','2025-12-03 08:00:08'),(157,'2025-12-03 11:00:15','Admin User','a personeli sistemden silindi','DELETE','2025-12-03 08:00:15'),(158,'2025-12-03 11:08:43','Admin User','Ayse Kaya müşterisi Ayse Kaya olarak güncellendi','UPDATE','2025-12-03 08:08:43'),(159,'2025-12-03 11:28:14','Admin User','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-03 08:28:14'),(160,'2025-12-03 11:28:22','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 08:28:22'),(161,'2025-12-03 11:28:28','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-03 08:28:28'),(162,'2025-12-03 11:28:38','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-12-03 08:28:38'),(163,'2025-12-03 11:28:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 08:28:44'),(164,'2025-12-03 11:28:56','Admin User','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-03 08:28:56'),(165,'2025-12-03 11:29:02','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 08:29:02'),(166,'2025-12-03 11:29:11','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-03 08:29:11'),(167,'2025-12-03 11:29:31','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 08:29:31'),(168,'2025-12-03 11:29:44','Admin User','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-03 08:29:44'),(169,'2025-12-03 11:36:03','Admin User','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-03 08:36:03'),(170,'2025-12-03 11:37:13','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 08:37:13'),(171,'2025-12-04 08:00:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 05:00:44'),(172,'2025-12-04 08:18:02','Admin User','Ahmet Yılmaz personelinin bilgileri güncellendi','UPDATE','2025-12-04 05:18:02'),(173,'2025-12-04 08:24:08','Admin User','Ahmet Yılmaz personelinin bilgileri güncellendi','UPDATE','2025-12-04 05:24:08'),(174,'2025-12-04 08:55:58','Admin User','Ahmet Yılmaz personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-04 05:55:58'),(175,'2025-12-04 09:44:17','Admin User','Yeni tekrarlı ödeme tanımlandı: ofis kirası (Kira) - 400 TL','CREATE','2025-12-04 06:44:17'),(176,'2025-12-04 09:44:37','Admin User','ofis kirası ödemesi yapıldı (2025/12) - 400 TL','CREATE','2025-12-04 06:44:37'),(177,'2025-12-04 10:23:32','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 07:23:32'),(178,'2025-12-04 10:40:28','Admin User','10 evo ürünü 10 evo olarak güncellendi','UPDATE','2025-12-04 07:40:28'),(179,'2025-12-04 11:25:26','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için çerçeve sözleşme güncellendi','UPDATE','2025-12-04 08:25:26'),(180,'2025-12-04 11:30:52','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 08:30:52'),(181,'2025-12-04 20:17:47','Ahmet Yılmaz','Personel giriş yaptı (E-posta/Telefon: ahmet.yilmaz@parfum.com)','Giriş Yapıldı','2025-12-04 17:17:47'),(182,'2025-12-04 20:17:57','Ahmet Yılmaz','personel oturumu kapattı (ID: 253)','Çıkış Yapıldı','2025-12-04 17:17:57'),(183,'2025-12-04 20:18:03','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 17:18:03'),(184,'2025-12-04 20:42:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 17:42:39'),(185,'2025-12-04 20:42:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 17:42:44'),(186,'2025-12-04 20:46:54','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 17:46:54'),(187,'2025-12-04 22:05:36','Admin User','10 evo ürünü için montaj iş emri güncellendi','UPDATE','2025-12-04 19:05:36'),(188,'2025-12-04 22:47:52','Admin User','Bergamot Essence esansı için iş emri güncellendi','UPDATE','2025-12-04 19:47:52'),(189,'2025-12-04 23:45:30','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 20:45:30'),(190,'2025-12-05 11:26:29','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:26:29'),(191,'2025-12-05 11:27:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:27:09'),(192,'2025-12-05 11:27:53','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:27:53'),(193,'2025-12-05 11:28:03','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:28:03'),(194,'2025-12-05 11:29:05','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:29:05'),(195,'2025-12-05 18:27:29','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:27:29'),(196,'2025-12-05 18:35:13','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:35:13'),(197,'2025-12-05 18:35:28','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:35:28'),(198,'2025-12-05 18:39:29','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:39:29'),(199,'2025-12-05 18:39:36','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:39:36'),(200,'2025-12-05 18:39:40','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:39:40'),(201,'2025-12-05 18:39:53','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:39:53'),(202,'2025-12-05 18:41:05','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:41:05'),(203,'2025-12-05 18:41:09','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:41:09'),(204,'2025-12-05 18:42:02','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:42:02'),(205,'2025-12-05 18:52:41','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:52:41'),(206,'2025-12-05 18:54:08','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:54:08'),(207,'2025-12-05 18:55:04','Admin User','0 tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:55:04'),(208,'2025-12-05 18:55:08','Admin User','0 tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:55:08'),(209,'2025-12-05 18:55:42','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:55:42'),(210,'2025-12-05 18:57:33','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:57:33'),(211,'2025-12-05 19:00:33','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 16:00:33'),(212,'2025-12-05 19:00:40','Admin User','0 tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:00:40'),(213,'2025-12-05 19:00:43','Admin User','0 tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:00:43'),(214,'2025-12-05 19:04:44','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 16:04:44'),(215,'2025-12-05 19:04:50','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:04:50'),(216,'2025-12-05 19:07:56','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 16:07:56'),(217,'2025-12-05 19:08:07','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:08:07'),(218,'2025-12-05 19:08:10','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:08:10'),(219,'2025-12-05 19:08:22','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 16:08:22'),(220,'2025-12-05 19:33:51','Admin User','Perfume 2 malzemesi sistemden silindi','DELETE','2025-12-05 16:33:51'),(221,'2025-12-05 19:33:54','Admin User','Perfume 8 malzemesi sistemden silindi','DELETE','2025-12-05 16:33:54'),(222,'2025-12-05 19:33:57','Admin User','Perfume 7 malzemesi sistemden silindi','DELETE','2025-12-05 16:33:57'),(223,'2025-12-05 19:34:00','Admin User','Perfume 5 malzemesi sistemden silindi','DELETE','2025-12-05 16:34:00'),(224,'2025-12-05 19:34:03','Admin User','Perfume 4 malzemesi sistemden silindi','DELETE','2025-12-05 16:34:03'),(225,'2025-12-05 19:34:07','Admin User','Perfume 6 malzemesi sistemden silindi','DELETE','2025-12-05 16:34:07'),(226,'2025-12-05 19:34:09','Admin User','Perfume 3 malzemesi sistemden silindi','DELETE','2025-12-05 16:34:09'),(227,'2025-12-05 19:34:25','Admin User','Medium Box malzemesi Medium Box olarak güncellendi','UPDATE','2025-12-05 16:34:25'),(228,'2025-12-05 19:37:53','Admin User','Esans Hammaddesi malzemesi sisteme eklendi','CREATE','2025-12-05 16:37:53'),(229,'2025-12-05 19:38:15','Admin User','Bergamot Essence ürün ağacından Ambalaj Malzemesi 2 bileşeni silindi','DELETE','2025-12-05 16:38:15'),(230,'2025-12-05 19:38:30','Admin User','Bergamot Essence ürün ağacındaki Medium Box bileşeni Esans Hammaddesi olarak güncellendi','UPDATE','2025-12-05 16:38:30'),(231,'2025-12-05 21:11:05','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 18:11:05'),(232,'2025-12-05 21:11:51','Admin User','Tedarikçi Avv tedarikçisine Esans Hammaddesi malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-05 18:11:51'),(233,'2025-12-05 21:12:15','Admin User','Tedarikçi Avv tedarikçisine Esans Hammaddesi malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 18:12:15'),(234,'2025-12-06 01:45:16','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-05 22:45:16'),(235,'2025-12-06 01:45:20','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 22:45:20'),(236,'2025-12-06 02:24:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 23:24:45'),(237,'2025-12-06 03:26:50','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:26:50'),(238,'2025-12-06 03:30:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:30:45'),(239,'2025-12-06 03:31:03','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:31:03'),(240,'2025-12-06 03:31:19','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:31:19'),(241,'2025-12-06 03:31:54','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:31:54'),(242,'2025-12-06 03:35:19','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:35:19'),(243,'2025-12-06 03:40:58','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:40:58'),(244,'2025-12-06 04:27:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 01:27:39'),(245,'2025-12-06 04:27:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 01:27:45'),(246,'2025-12-06 04:59:12','Admin User','Ayse Kaya müşterisi için yeni sipariş oluşturuldu (ID: 16)','CREATE','2025-12-06 01:59:12'),(247,'2025-12-06 05:00:30','Admin User','Ayse Kaya müşterisine ait 16 nolu siparişin yeni durumu: İptal Edildi','UPDATE','2025-12-06 02:00:30'),(248,'2025-12-06 14:02:47','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 11:02:47'),(249,'2025-12-06 14:03:57','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-06 11:03:57'),(250,'2025-12-06 14:04:01','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 11:04:01'),(251,'2025-12-06 14:28:15','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 11:28:15'),(252,'2025-12-06 23:52:59','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 20:52:59'),(253,'2025-12-07 18:03:33','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-07 15:03:33'),(254,'2025-12-07 18:24:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-07 15:24:04'),(255,'2025-12-07 18:47:09','Admin User','Mehmet Kaya personeli sistemden silindi','DELETE','2025-12-07 15:47:09'),(256,'2025-12-08 00:56:16','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-07 21:56:16'),(257,'2025-12-08 02:53:57','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-07 23:53:57'),(258,'2025-12-08 02:54:36','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-07 23:54:36'),(259,'2025-12-08 15:07:19','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-08 12:07:19'),(260,'2025-12-08 15:16:15','SISTEM','Acil durum kullanıcısı ile en son yedekten geri yükleme yapıldı.','Kritik Eylem','2025-12-08 12:16:15'),(261,'2025-12-08 15:16:21','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-08 12:16:21'),(262,'2025-12-08 15:26:30','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-08 12:26:30'),(263,'2025-12-08 15:30:28','Admin Kullanýcý 2','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 12:30:28'),(264,'2025-12-08 15:45:59','Admin Kullanýcý 2','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-08 12:45:59'),(265,'2025-12-08 15:46:03','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 12:46:03'),(266,'2025-12-08 15:46:22','Yedek Admin','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-08 12:46:22'),(267,'2025-12-08 15:49:33','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 12:49:33'),(268,'2025-12-08 16:25:18','Yedek Admin','personeller tabloları temizlendi','DELETE','2025-12-08 13:25:18'),(269,'2025-12-08 16:38:57','Yedek Admin','apo personeli sisteme eklendi','CREATE','2025-12-08 13:38:57'),(270,'2025-12-08 16:41:51','Yedek Admin','apo personeli sistemden silindi','DELETE','2025-12-08 13:41:51'),(271,'2025-12-08 16:46:09','Yedek Admin','apo personeli sisteme eklendi','CREATE','2025-12-08 13:46:09'),(272,'2025-12-08 16:46:15','Yedek Admin','apo personeli sistemden silindi','DELETE','2025-12-08 13:46:15'),(273,'2025-12-08 17:00:30','Yedek Admin','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-08 14:00:30'),(274,'2025-12-08 17:00:33','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 14:00:33'),(275,'2025-12-08 17:09:22','Yedek Admin','Ahmet Yıldız personeli sisteme eklendi','CREATE','2025-12-08 14:09:22'),(276,'2025-12-08 17:10:54','Yedek Admin','Mehmet Bulut personeli sisteme eklendi','CREATE','2025-12-08 14:10:54'),(277,'2025-12-08 17:11:29','Yedek Admin','Ahmet Yıldız personeli sistemden silindi','DELETE','2025-12-08 14:11:29'),(278,'2025-12-08 17:11:33','Yedek Admin','Mehmet Bulut personeli sistemden silindi','DELETE','2025-12-08 14:11:33'),(279,'2025-12-08 17:14:50','Yedek Admin','apo personeli sisteme eklendi','CREATE','2025-12-08 14:14:50'),(280,'2025-12-08 17:14:58','Yedek Admin','apo personeli sistemden silindi','DELETE','2025-12-08 14:14:58'),(281,'2025-12-08 17:15:24','Yedek Admin','apo personeli sisteme eklendi','CREATE','2025-12-08 14:15:24'),(282,'2025-12-08 17:15:59','Yedek Admin','apo personeli sistemden silindi','DELETE','2025-12-08 14:15:59'),(283,'2025-12-08 17:23:34','Yedek Admin','Ahmet Yılmaz personeli sisteme eklendi','CREATE','2025-12-08 14:23:34'),(284,'2025-12-08 17:24:32','Yedek Admin','Ahmet Yılmaz personelinin bilgileri güncellendi','UPDATE','2025-12-08 14:24:32'),(285,'2025-12-08 17:27:06','Ahmet Yılmaz','Personel giriş yaptı (E-posta/Telefon: 05515515151)','Giriş Yapıldı','2025-12-08 14:27:06'),(286,'2025-12-08 17:27:59','Ahmet Yılmaz','dsa tedarikçisi sisteme eklendi','CREATE','2025-12-08 14:27:59'),(287,'2025-12-08 17:28:32','Ahmet Yılmaz','personel oturumu kapattı (ID: 293)','Çıkış Yapıldı','2025-12-08 14:28:32'),(288,'2025-12-08 17:28:38','Ahmet Yılmaz','Personel giriş yaptı (E-posta/Telefon: 05515515151)','Giriş Yapıldı','2025-12-08 14:28:38'),(289,'2025-12-08 17:28:52','Ahmet Yılmaz','personel oturumu kapattı (ID: 293)','Çıkış Yapıldı','2025-12-08 14:28:52'),(290,'2025-12-08 17:29:03','Yedek Admin','Ahmet Yılmaz personeli sistemden silindi','DELETE','2025-12-08 14:29:03'),(291,'2025-12-08 17:31:36','Yedek Admin','Ahmet Yıldırım personeli sisteme eklendi','CREATE','2025-12-08 14:31:36'),(292,'2025-12-08 17:32:40','Yedek Admin','Ahmet Yıldırım personelinin bilgileri güncellendi','UPDATE','2025-12-08 14:32:40'),(293,'2025-12-08 17:35:09','Ahmet Yıldırım','Personel giriş yaptı (E-posta/Telefon: 05515515151)','Giriş Yapıldı','2025-12-08 14:35:09'),(294,'2025-12-08 17:36:04','Ahmet Yıldırım','personel oturumu kapattı (ID: 294)','Çıkış Yapıldı','2025-12-08 14:36:04'),(295,'2025-12-08 17:42:54','Yedek Admin','dsa tedarikçisi sistemden silindi','DELETE','2025-12-08 14:42:54'),(296,'2025-12-08 17:43:21','Yedek Admin','Ebru Gunes müşterisi sistemden silindi','DELETE','2025-12-08 14:43:21'),(297,'2025-12-08 17:43:26','Yedek Admin','Eflatun Kozmetik müşterisi sistemden silindi','DELETE','2025-12-08 14:43:26'),(298,'2025-12-08 17:49:08','Yedek Admin','Özhan Aydın müşterisi sisteme eklendi','CREATE','2025-12-08 14:49:08'),(299,'2025-12-08 17:49:45','Yedek Admin','Özhan Aydın müşterisi sistemden silindi','DELETE','2025-12-08 14:49:45'),(300,'2025-12-08 17:50:30','Yedek Admin','Özhan Aydın müşterisi sisteme eklendi','CREATE','2025-12-08 14:50:30'),(301,'2025-12-08 17:52:12','Yedek Admin','Özhan Aydın müşterisi sistemden silindi','DELETE','2025-12-08 14:52:12'),(302,'2025-12-08 17:52:57','Yedek Admin','Özhan Aydın müşterisi sisteme eklendi','CREATE','2025-12-08 14:52:57'),(303,'2025-12-08 17:54:03','Yedek Admin','ÖZhan müşterisi sisteme eklendi','CREATE','2025-12-08 14:54:03'),(304,'2025-12-08 17:54:11','Yedek Admin','Özhan Aydın müşterisi sistemden silindi','DELETE','2025-12-08 14:54:11'),(305,'2025-12-08 17:54:15','Yedek Admin','ÖZhan müşterisi sistemden silindi','DELETE','2025-12-08 14:54:15'),(306,'2025-12-08 17:56:00','Yedek Admin','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-08 14:56:00'),(307,'2025-12-08 17:57:08','Yedek Admin','Özhan Aydın müşterisi sisteme eklendi','CREATE','2025-12-08 14:57:08'),(308,'2025-12-08 17:57:53','Özhan Aydın','Müşteri giriş yaptı (E-posta/Telefon: 05003001010)','Giriş Yapıldı','2025-12-08 14:57:53'),(309,'2025-12-08 17:58:39','Yedek Admin','Özhan Aydın müşterisi Özhan Aydın olarak güncellendi','UPDATE','2025-12-08 14:58:39'),(310,'2025-12-08 17:58:57','Özhan Aydın','musteri oturumu kapattı (ID: 160)','Çıkış Yapıldı','2025-12-08 14:58:57'),(311,'2025-12-08 18:05:02','Yedek Admin','tedarikciler tabloları temizlendi','DELETE','2025-12-08 15:05:02'),(312,'2025-12-08 18:05:15','Yedek Admin','cerceve_sozlesmeler tabloları temizlendi','DELETE','2025-12-08 15:05:15'),(313,'2025-12-08 18:07:16','Yedek Admin','Ahmet Kozmetik tedarikçisi sisteme eklendi','CREATE','2025-12-08 15:07:16'),(314,'2025-12-08 18:10:27','Yedek Admin','lokasyonlar tabloları temizlendi','DELETE','2025-12-08 15:10:27'),(315,'2025-12-08 18:11:13','Yedek Admin','tanklar tabloları temizlendi','DELETE','2025-12-08 15:11:13'),(316,'2025-12-08 18:11:32','Yedek Admin','is_merkezleri tabloları temizlendi','DELETE','2025-12-08 15:11:32'),(317,'2025-12-08 18:35:33','Yedek Admin','Merkez Depo deposuna A1 rafı eklendi','CREATE','2025-12-08 15:35:33'),(318,'2025-12-08 18:35:56','Yedek Admin','Merkez Depo deposuna A2 rafı eklendi','CREATE','2025-12-08 15:35:56'),(319,'2025-12-08 18:36:10','Yedek Admin','Merkez Depo deposuna A3 rafı eklendi','CREATE','2025-12-08 15:36:10'),(320,'2025-12-08 18:36:29','Yedek Admin','Satış Depo deposuna 1 rafı eklendi','CREATE','2025-12-08 15:36:29'),(321,'2025-12-08 18:36:42','Yedek Admin','Satış Depo deposuna 2 rafı eklendi','CREATE','2025-12-08 15:36:42'),(322,'2025-12-08 18:38:18','Yedek Admin','Birinci Tankımız adlı tank sisteme eklendi','CREATE','2025-12-08 15:38:18'),(323,'2025-12-08 18:38:44','Yedek Admin','Eski Tank adlı tank sisteme eklendi','CREATE','2025-12-08 15:38:44'),(324,'2025-12-08 18:40:00','Yedek Admin','Montaj Masası 1 iş merkezi eklendi','CREATE','2025-12-08 15:40:00'),(325,'2025-12-08 18:40:19','Yedek Admin','Montaj Masası 2 iş merkezi eklendi','CREATE','2025-12-08 15:40:19'),(326,'2025-12-08 18:40:46','Yedek Admin','Montajcı Abdullah iş merkezi eklendi','CREATE','2025-12-08 15:40:46'),(327,'2025-12-08 18:41:19','Yedek Admin','Montajcı Abdullah iş merkezi silindi','DELETE','2025-12-08 15:41:19'),(328,'2025-12-08 18:41:23','Yedek Admin','Montaj Masası 2 iş merkezi silindi','DELETE','2025-12-08 15:41:23'),(329,'2025-12-08 18:53:02','Yedek Admin','Satış Depo deposuna Kasa Arkası Rafı rafı eklendi','CREATE','2025-12-08 15:53:02'),(330,'2025-12-08 18:53:24','Yedek Admin','Satış Depo deposundaki 2 rafı silindi','DELETE','2025-12-08 15:53:24'),(331,'2025-12-08 18:54:30','Yedek Admin','Beşliyüz Tank adlı tank sisteme eklendi','CREATE','2025-12-08 15:54:30'),(332,'2025-12-08 18:55:51','Yedek Admin','Abdullah Usta iş merkezi eklendi','CREATE','2025-12-08 15:55:51'),(333,'2025-12-08 19:02:54','Yedek Admin','Ambalaj Malzemesi 2 malzemesi sistemden silindi','DELETE','2025-12-08 16:02:54'),(334,'2025-12-08 19:02:58','Yedek Admin','Esans Hammaddesi malzemesi sistemden silindi','DELETE','2025-12-08 16:02:58'),(335,'2025-12-08 19:03:02','Yedek Admin','Medium Box malzemesi sistemden silindi','DELETE','2025-12-08 16:03:02'),(336,'2025-12-08 19:04:01','Yedek Admin','urunler tabloları temizlendi','DELETE','2025-12-08 16:04:01'),(337,'2025-12-08 19:04:13','Yedek Admin','esanslar tabloları temizlendi','DELETE','2025-12-08 16:04:13'),(338,'2025-12-08 19:04:25','Yedek Admin','urun_agaci tabloları temizlendi','DELETE','2025-12-08 16:04:25'),(339,'2025-12-08 19:05:28','Yedek Admin','AA malzemesi sisteme eklendi','CREATE','2025-12-08 16:05:28'),(340,'2025-12-08 19:09:56','Yedek Admin','sadsa malzemesi sisteme eklendi','CREATE','2025-12-08 16:09:56'),(341,'2025-12-08 19:10:01','Yedek Admin','sadsa malzemesi sistemden silindi','DELETE','2025-12-08 16:10:01'),(342,'2025-12-08 19:10:05','Yedek Admin','AA malzemesi sistemden silindi','DELETE','2025-12-08 16:10:05'),(343,'2025-12-08 19:10:25','Yedek Admin','dsa malzemesi sisteme eklendi','CREATE','2025-12-08 16:10:25'),(344,'2025-12-08 19:10:36','Yedek Admin','dsa ürününe fotoğraf eklendi','CREATE','2025-12-08 16:10:36'),(345,'2025-12-08 19:10:46','Yedek Admin','dsa malzemesi sistemden silindi','DELETE','2025-12-08 16:10:46'),(346,'2025-12-08 19:14:12','Yedek Admin','Dolge Gabbana Dış Kutu malzemesi sisteme eklendi','CREATE','2025-12-08 16:14:12'),(347,'2025-12-08 19:14:53','Yedek Admin','Dolge Gabbana Dış Kutu ürününe fotoğraf eklendi','CREATE','2025-12-08 16:14:53'),(348,'2025-12-08 19:15:06','Yedek Admin','Dolge Gabbana Dış Kutu ürününden fotoğraf silindi','DELETE','2025-12-08 16:15:06'),(349,'2025-12-08 19:15:59','Yedek Admin','Dolge Gabbana Dış Kutu ürününe fotoğraf eklendi','CREATE','2025-12-08 16:15:59'),(350,'2025-12-08 19:16:03','Yedek Admin','Dolge Gabbana Dış Kutu ürününden fotoğraf silindi','DELETE','2025-12-08 16:16:03'),(351,'2025-12-08 19:16:08','Yedek Admin','Dolge Gabbana Dış Kutu malzemesi sistemden silindi','DELETE','2025-12-08 16:16:08'),(352,'2025-12-08 19:18:00','Yedek Admin','212 Man Etiketi malzemesi sisteme eklendi','CREATE','2025-12-08 16:18:00'),(353,'2025-12-08 19:18:49','Yedek Admin','212 Man Etiketi ürününe fotoğraf eklendi','CREATE','2025-12-08 16:18:49'),(354,'2025-12-08 19:40:05','Yedek Admin','ahmet ürünü sisteme eklendi','CREATE','2025-12-08 16:40:05'),(355,'2025-12-08 19:44:08','Yedek Admin','ahmet ürünü ahmet olarak güncellendi','UPDATE','2025-12-08 16:44:08'),(356,'2025-12-08 19:47:58','Yedek Admin','ahmet ürünü sistemden silindi','DELETE','2025-12-08 16:47:58'),(357,'2025-12-08 19:48:17','Yedek Admin','aaa ürünü sisteme eklendi','CREATE','2025-12-08 16:48:17'),(358,'2025-12-08 19:48:25','Yedek Admin','aaa ürünü aaa olarak güncellendi','UPDATE','2025-12-08 16:48:25'),(359,'2025-12-08 20:01:07','Yedek Admin','sda1 esansı sisteme eklendi','CREATE','2025-12-08 17:01:07'),(360,'2025-12-08 20:01:12','Yedek Admin','sda1 esansı sistemden silindi','DELETE','2025-12-08 17:01:12'),(361,'2025-12-08 20:01:21','Yedek Admin','aaa ürünü sistemden silindi','DELETE','2025-12-08 17:01:21'),(362,'2025-12-08 20:03:59','Yedek Admin','sda ürünü sisteme eklendi','CREATE','2025-12-08 17:03:59'),(363,'2025-12-08 20:04:27','Yedek Admin','sda ürünü sistemden silindi','DELETE','2025-12-08 17:04:27'),(364,'2025-12-08 20:05:12','Yedek Admin','Bergamot Essence esansı sisteme eklendi','CREATE','2025-12-08 17:05:12'),(365,'2025-12-08 20:05:18','Yedek Admin','Bergamot Essence esansı sistemden silindi','DELETE','2025-12-08 17:05:18'),(366,'2025-12-08 20:07:10','Yedek Admin','212 Men ürünü sisteme eklendi','CREATE','2025-12-08 17:07:10'),(367,'2025-12-08 20:08:28','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:08:28'),(368,'2025-12-08 20:08:28','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:08:28'),(369,'2025-12-08 20:08:29','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:08:29'),(370,'2025-12-08 20:08:34','Yedek Admin','212 Men ürününden fotoğraf silindi','DELETE','2025-12-08 17:08:34'),(371,'2025-12-08 20:08:36','Yedek Admin','212 Men ürününden fotoğraf silindi','DELETE','2025-12-08 17:08:36'),(372,'2025-12-08 20:08:39','Yedek Admin','212 Men ürününden fotoğraf silindi','DELETE','2025-12-08 17:08:39'),(373,'2025-12-08 20:08:44','Yedek Admin','212 Men ürünü sistemden silindi','DELETE','2025-12-08 17:08:44'),(374,'2025-12-08 20:17:23','Yedek Admin','212 Men ürünü sisteme eklendi','CREATE','2025-12-08 17:17:23'),(375,'2025-12-08 20:17:53','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:17:53'),(376,'2025-12-08 20:17:54','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:17:54'),(377,'2025-12-08 20:17:54','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:17:54'),(378,'2025-12-08 20:18:01','Yedek Admin','212 Men ürününün ana fotoğrafı değiştirildi','UPDATE','2025-12-08 17:18:01'),(379,'2025-12-08 20:19:47','Yedek Admin','212 Men ürünü sistemden silindi','DELETE','2025-12-08 17:19:47'),(380,'2025-12-08 20:20:04','Yedek Admin','sada ürünü sisteme eklendi','CREATE','2025-12-08 17:20:04'),(381,'2025-12-08 20:20:50','Yedek Admin','sada ürününe fotoğraf eklendi','CREATE','2025-12-08 17:20:50'),(382,'2025-12-08 20:20:54','Yedek Admin','sada ürününe fotoğraf eklendi','CREATE','2025-12-08 17:20:54'),(383,'2025-12-08 20:20:58','Yedek Admin','sada ürününe fotoğraf eklendi','CREATE','2025-12-08 17:20:58'),(384,'2025-12-08 20:22:24','Yedek Admin','sada ürünü sistemden silindi','DELETE','2025-12-08 17:22:24'),(385,'2025-12-08 20:22:53','Yedek Admin','sd ürünü sisteme eklendi','CREATE','2025-12-08 17:22:53'),(386,'2025-12-08 20:23:05','Yedek Admin','sd ürününe fotoğraf eklendi','CREATE','2025-12-08 17:23:05'),(387,'2025-12-08 20:23:08','Yedek Admin','sd ürününe fotoğraf eklendi','CREATE','2025-12-08 17:23:08'),(388,'2025-12-08 20:23:11','Yedek Admin','sd ürününe fotoğraf eklendi','CREATE','2025-12-08 17:23:11'),(389,'2025-12-08 20:24:46','Yedek Admin','sd ürünü sistemden silindi','DELETE','2025-12-08 17:24:46'),(390,'2025-12-08 20:25:07','Yedek Admin','abc ürünü sisteme eklendi','CREATE','2025-12-08 17:25:07'),(391,'2025-12-08 20:25:16','Yedek Admin','abc ürününe fotoğraf eklendi','CREATE','2025-12-08 17:25:16'),(392,'2025-12-08 20:25:18','Yedek Admin','abc ürününe fotoğraf eklendi','CREATE','2025-12-08 17:25:18'),(393,'2025-12-08 20:25:21','Yedek Admin','abc ürününe fotoğraf eklendi','CREATE','2025-12-08 17:25:21'),(394,'2025-12-08 20:25:34','Yedek Admin','abc ürünü sistemden silindi','DELETE','2025-12-08 17:25:34'),(395,'2025-12-08 20:25:56','Yedek Admin','kljl esansı sisteme eklendi','CREATE','2025-12-08 17:25:56'),(396,'2025-12-08 20:26:02','Yedek Admin','kljl esansı sistemden silindi','DELETE','2025-12-08 17:26:02'),(397,'2025-12-08 20:33:41','Yedek Admin','212 Men ürünü sisteme eklendi','CREATE','2025-12-08 17:33:41'),(398,'2025-12-08 20:34:24','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:34:24'),(399,'2025-12-08 20:34:28','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:34:28'),(400,'2025-12-08 20:34:32','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:34:32'),(401,'2025-12-08 20:34:36','Yedek Admin','212 Men ürününün ana fotoğrafı değiştirildi','UPDATE','2025-12-08 17:34:36'),(402,'2025-12-08 20:36:12','Yedek Admin','Issey Miyaki ürünü sisteme eklendi','CREATE','2025-12-08 17:36:12'),(403,'2025-12-08 20:42:01','Yedek Admin','Gül Kokusu Esansı esansı sisteme eklendi','CREATE','2025-12-08 17:42:01'),(404,'2025-12-08 20:44:27','Yedek Admin','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-08 17:44:27'),(405,'2025-12-08 23:15:23','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 20:15:23'),(406,'2025-12-08 23:16:22','Yedek Admin','212 Man Etiketi malzemesi 212 Man Etiketi olarak güncellendi','UPDATE','2025-12-08 20:16:22'),(407,'2025-12-08 23:17:18','Yedek Admin','A1 Dış Kutu malzemesi sisteme eklendi','CREATE','2025-12-08 20:17:18'),(408,'2025-12-08 23:19:56','Yedek Admin','A1 Dış Kutu malzemesi A1 Dış Kutu olarak güncellendi','UPDATE','2025-12-08 20:19:56'),(409,'2025-12-08 23:20:31','Yedek Admin','212 Men Kapat malzemesi sisteme eklendi','CREATE','2025-12-08 20:20:31'),(410,'2025-12-08 23:21:59','Yedek Admin','212 Men Kapat malzemesi 212 Men Kapat olarak güncellendi','UPDATE','2025-12-08 20:21:59'),(411,'2025-12-08 23:22:28','Yedek Admin','212 Men Şişesi malzemesi sisteme eklendi','CREATE','2025-12-08 20:22:28'),(412,'2025-12-08 23:27:53','Yedek Admin','Saf Su malzemesi sisteme eklendi','CREATE','2025-12-08 20:27:53'),(413,'2025-12-08 23:28:30','Yedek Admin','Alkol malzemesi sisteme eklendi','CREATE','2025-12-08 20:28:30'),(414,'2025-12-08 23:34:41','Yedek Admin','Gül Kokusu Esansı ürün ağacına Alkol bileşeni eklendi','CREATE','2025-12-08 20:34:41'),(415,'2025-12-08 23:35:41','Yedek Admin','Gül Kokusu Esansı ürün ağacına Saf Su bileşeni eklendi','CREATE','2025-12-08 20:35:41'),(416,'2025-12-08 23:35:59','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2025-12-08 20:35:59'),(417,'2025-12-08 23:56:22','Yedek Admin','Gül Kokusu Esansı ürün ağacından Saf Su bileşeni silindi','DELETE','2025-12-08 20:56:22'),(418,'2025-12-08 23:56:28','Yedek Admin','Gül Kokusu Esansı ürün ağacından Alkol bileşeni silindi','DELETE','2025-12-08 20:56:28'),(419,'2025-12-09 00:41:10','Yedek Admin','Gül Kokusu Esansı esansı sistemden silindi','DELETE','2025-12-08 21:41:10'),(420,'2025-12-09 00:42:57','Yedek Admin','Gül Kokusu Esansı esansı sisteme eklendi','CREATE','2025-12-08 21:42:57'),(421,'2025-12-09 00:44:08','Yedek Admin','Gül Kokusu Esansı ürün ağacına Alkol bileşeni eklendi','CREATE','2025-12-08 21:44:08'),(422,'2025-12-09 00:44:45','Yedek Admin','Gül Kokusu Esansı ürün ağacına Alkol bileşeni eklendi','CREATE','2025-12-08 21:44:45'),(423,'2025-12-09 00:45:03','Yedek Admin','Gül Kokusu Esansı ürün ağacından Alkol bileşeni silindi','DELETE','2025-12-08 21:45:03'),(424,'2025-12-09 00:45:08','Yedek Admin','Gül Kokusu Esansı ürün ağacından Alkol bileşeni silindi','DELETE','2025-12-08 21:45:08'),(425,'2025-12-09 00:48:58','Yedek Admin','Gül Kokusu Esansı ürün ağacına Alkol bileşeni eklendi','CREATE','2025-12-08 21:48:58'),(426,'2025-12-09 00:49:37','Yedek Admin','Gül Kokusu Esansı ürün ağacına Saf Su bileşeni eklendi','CREATE','2025-12-08 21:49:37'),(427,'2025-12-09 00:52:01','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Saf Su bileşeni Saf Su olarak güncellendi','UPDATE','2025-12-08 21:52:01'),(428,'2025-12-09 00:52:11','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2025-12-08 21:52:11'),(429,'2025-12-09 00:54:10','Yedek Admin','212 Men ürün ağacına Gül Kokusu Esansı bileşeni eklendi','CREATE','2025-12-08 21:54:10'),(430,'2025-12-09 00:54:24','Yedek Admin','212 Men ürün ağacına 212 Man Etiketi bileşeni eklendi','CREATE','2025-12-08 21:54:24'),(431,'2025-12-09 00:54:37','Yedek Admin','212 Men ürün ağacına 212 Men Kapat bileşeni eklendi','CREATE','2025-12-08 21:54:37'),(432,'2025-12-09 00:54:52','Yedek Admin','212 Men ürün ağacına 212 Men Şişesi bileşeni eklendi','CREATE','2025-12-08 21:54:52'),(433,'2025-12-09 01:01:49','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2025-12-08 22:01:49'),(434,'2025-12-09 01:01:59','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Saf Su bileşeni Saf Su olarak güncellendi','UPDATE','2025-12-08 22:01:59'),(435,'2025-12-09 01:03:43','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Saf Su bileşeni Saf Su olarak güncellendi','UPDATE','2025-12-08 22:03:43'),(436,'2025-12-09 01:03:52','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2025-12-08 22:03:52'),(437,'2025-12-09 01:05:28','Yedek Admin','212 Men ürün ağacına A1 Dış Kutu bileşeni eklendi','CREATE','2025-12-08 22:05:28'),(438,'2025-12-09 02:34:40','Yedek Admin','Ahmet Kozmetik tedarikçisine Saf Su malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-08 23:34:40'),(439,'2025-12-09 02:37:14','Yedek Admin','Kira kategorisindeki 400.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:14'),(440,'2025-12-09 02:37:19','Yedek Admin','Malzeme Gideri kategorisindeki 210.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:19'),(441,'2025-12-09 02:37:24','Yedek Admin','Sarf Malzeme Gideri kategorisindeki 50.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:24'),(442,'2025-12-09 02:37:28','Yedek Admin','Fire Gideri kategorisindeki 88.80 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:28'),(443,'2025-12-09 02:37:32','Yedek Admin','Fire Gideri kategorisindeki 21.88 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:32'),(444,'2025-12-09 02:37:36','Yedek Admin','Fire Gideri kategorisindeki 236.56 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:36'),(445,'2025-12-09 02:37:41','Yedek Admin','Malzeme Gideri kategorisindeki 300.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:41'),(446,'2025-12-09 02:37:48','Yedek Admin','Personel Gideri kategorisindeki 500.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:48'),(447,'2025-12-09 02:38:22','Yedek Admin','212 Men Kapat için 12 adet stok hareketi ve ilgili gider kaydı eklendi','CREATE','2025-12-08 23:38:22'),(448,'2025-12-09 02:38:33','Yedek Admin','Fire Gideri kategorisindeki 252.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:38:33'),(449,'2025-12-09 02:44:33','Yedek Admin','Diğer kategorisinde 200 TL tutarında gider eklendi','CREATE','2025-12-08 23:44:33'),(450,'2025-12-09 02:47:48','Yedek Admin','Ahmet Yıldırım personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-08 23:47:48'),(451,'2025-12-09 02:48:41','Yedek Admin','Ahmet Yıldırım personeline 2025/12 dönemi için 50000 TL maaş ödemesi yapıldı','CREATE','2025-12-08 23:48:41'),(452,'2025-12-09 02:48:53','Yedek Admin','Personel Gideri kategorisindeki 50000.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:48:53'),(453,'2025-12-09 02:55:00','Yedek Admin','Ahmet Yıldırım personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-08 23:55:00'),(454,'2025-12-09 02:55:44','Yedek Admin','Ahmet Yıldırım personeline 2025/12 dönemi için 50000 TL maaş ödemesi yapıldı','CREATE','2025-12-08 23:55:44'),(455,'2025-12-09 02:57:14','Yedek Admin','personel_maas_odemeleri tabloları temizlendi','DELETE','2025-12-08 23:57:14'),(456,'2025-12-09 02:57:40','Yedek Admin','Personel Avansı kategorisindeki 10000.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:57:40'),(457,'2025-12-09 03:00:38','Yedek Admin','personel_avanslar tabloları temizlendi','DELETE','2025-12-09 00:00:38'),(458,'2025-12-09 03:01:30','Yedek Admin','Ahmet Yıldırım personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-09 00:01:30'),(459,'2025-12-09 03:01:53','Yedek Admin','Personel Gideri kategorisindeki 50000.00 TL tutarlı gider silindi','DELETE','2025-12-09 00:01:53'),(460,'2025-12-09 03:02:02','Yedek Admin','Personel Avansı kategorisindeki 10000.00 TL tutarlı gider silindi','DELETE','2025-12-09 00:02:02'),(461,'2025-12-09 03:02:27','Yedek Admin','personel_avanslar, personel_maas_odemeleri tabloları temizlendi','DELETE','2025-12-09 00:02:27'),(462,'2025-12-09 03:03:23','Yedek Admin','Ahmet Yıldırım personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-09 00:03:23'),(463,'2025-12-09 03:03:53','Yedek Admin','Ahmet Yıldırım personeline 2025/12 dönemi için 50000 TL maaş ödemesi yapıldı','CREATE','2025-12-09 00:03:53'),(464,'2025-12-09 03:05:42','Yedek Admin','Tekrarlı ödeme silindi: ofis kirası','DELETE','2025-12-09 00:05:42'),(465,'2025-12-09 03:37:20','Yedek Admin','Yeni tekrarlı ödeme tanımlandı: Ofis Kirası (Kira) - 30000 TL','CREATE','2025-12-09 00:37:20'),(466,'2025-12-09 03:38:00','Yedek Admin','Yeni tekrarlı ödeme tanımlandı: Elektrik Faturamız (Elektrik) - 20000 TL','CREATE','2025-12-09 00:38:00'),(467,'2025-12-09 03:38:21','Yedek Admin','Ofis Kirası ödemesi yapıldı (2025/12) - 30000 TL','CREATE','2025-12-09 00:38:21'),(468,'2025-12-09 04:03:55','Yedek Admin','siparis_kalemleri, siparisler tabloları temizlendi','DELETE','2025-12-09 01:03:55'),(469,'2025-12-09 04:07:02','Yedek Admin','Ali Can müşterisi için yeni sipariş oluşturuldu (ID: 1)','CREATE','2025-12-09 01:07:02'),(470,'2025-12-09 04:08:03','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-09 01:08:03'),(471,'2025-12-09 04:08:18','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: Beklemede','UPDATE','2025-12-09 01:08:18'),(472,'2025-12-09 04:08:43','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-09 01:08:43'),(473,'2025-12-09 04:09:10','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: Beklemede','UPDATE','2025-12-09 01:09:10'),(474,'2025-12-09 04:09:17','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: İptal Edildi','UPDATE','2025-12-09 01:09:17'),(475,'2025-12-09 04:09:49','Yedek Admin','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-09 01:09:49'),(476,'2025-12-09 04:10:05','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-09 01:10:05'),(477,'2025-12-09 04:11:07','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 2)','CREATE','2025-12-09 01:11:07'),(478,'2025-12-09 04:11:54','Yedek Admin','Ali Can müşterisine ait 2 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-09 01:11:54'),(479,'2025-12-09 04:18:29','Yedek Admin','stok_hareket_kayitlari tabloları temizlendi','DELETE','2025-12-09 01:18:29'),(480,'2025-12-09 04:18:46','Yedek Admin','stok_hareketleri_sozlesmeler tabloları temizlendi','DELETE','2025-12-09 01:18:46'),(481,'2025-12-09 04:23:03','Yedek Admin','212 Man Etiketi için 10 adet stok hareketi ve ilgili gider kaydı eklendi','CREATE','2025-12-09 01:23:03'),(482,'2025-12-09 04:24:21','Yedek Admin','212 Men Şişesi için 10 adet stok hareketi eklendi','CREATE','2025-12-09 01:24:21'),(483,'2025-12-09 04:24:57','Yedek Admin','212 Men için 20 adet stok hareketi eklendi','CREATE','2025-12-09 01:24:57'),(484,'2025-12-09 04:37:51','Yedek Admin','Ahmet Kozmetik tedarikçisine Saf Su malzemesi için sipariş oluşturuldu','CREATE','2025-12-09 01:37:51'),(485,'2025-12-09 04:40:01','Yedek Admin','esans_is_emirleri, montaj_is_emirleri tabloları temizlendi','DELETE','2025-12-09 01:40:01'),(486,'2025-12-09 05:15:05','Yedek Admin','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-09 02:15:05'),(487,'2025-12-09 13:39:53','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-09 10:39:53'),(488,'2025-12-09 13:42:29','Admin User','mehmet mutlu personeli sisteme eklendi','CREATE','2025-12-09 10:42:29'),(489,'2025-12-09 13:44:33','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-09 10:44:33'),(490,'2025-12-09 22:42:47','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-09 19:42:47'),(491,'2025-12-09 22:42:52','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-12-09 19:42:52'),(492,'2025-12-09 22:51:33','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-09 19:51:33'),(493,'2025-12-10 01:43:57','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-09 22:43:57'),(494,'2025-12-10 01:44:18','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-09 22:44:18'),(495,'2025-12-11 13:33:29','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-11 10:33:29'),(496,'2025-12-12 17:03:27','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-12 14:03:27'),(497,'2025-12-12 17:28:31','Admin User','Ahmet Yıldırım personeli sistemden silindi','DELETE','2025-12-12 14:28:31'),(498,'2025-12-12 17:29:34','Admin User','burhan  personeli sisteme eklendi','CREATE','2025-12-12 14:29:34'),(499,'2025-12-12 17:30:14','Admin User','bünyamin  personeli sisteme eklendi','CREATE','2025-12-12 14:30:14'),(500,'2025-12-12 17:31:16','Admin User','Alkol malzemesi sistemden silindi','DELETE','2025-12-12 14:31:16'),(501,'2025-12-12 17:31:21','Admin User','212 Men Şişesi malzemesi sistemden silindi','DELETE','2025-12-12 14:31:21'),(502,'2025-12-12 17:31:25','Admin User','Saf Su malzemesi sistemden silindi','DELETE','2025-12-12 14:31:25'),(503,'2025-12-12 17:31:28','Admin User','212 Men Kapat malzemesi sistemden silindi','DELETE','2025-12-12 14:31:28'),(504,'2025-12-12 17:31:31','Admin User','A1 Dış Kutu malzemesi sistemden silindi','DELETE','2025-12-12 14:31:31'),(505,'2025-12-12 17:31:34','Admin User','212 Man Etiketi malzemesi sistemden silindi','DELETE','2025-12-12 14:31:34'),(506,'2025-12-12 21:29:16','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-12 18:29:16'),(507,'2025-12-12 21:34:55','Admin User','idris personeli sisteme eklendi','CREATE','2025-12-12 18:34:55'),(508,'2025-12-12 21:35:17','idris','Personel giriş yaptı (E-posta/Telefon: 05539204551)','Giriş Yapıldı','2025-12-12 18:35:17'),(509,'2025-12-12 21:39:21','idris','personel oturumu kapattı (ID: 298)','Çıkış Yapıldı','2025-12-12 18:39:21'),(510,'2025-12-12 21:39:34','idris','Personel giriş yaptı (E-posta/Telefon: 05539204551)','Giriş Yapıldı','2025-12-12 18:39:34'),(511,'2025-12-16 15:16:21','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-16 12:16:21'),(512,'2025-12-16 15:21:15','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-16 12:21:15'),(513,'2025-12-16 15:53:05','Admin User','Issey Miyaki ürününe fotoğraf eklendi','CREATE','2025-12-16 12:53:05'),(514,'2025-12-16 15:53:13','Admin User','Issey Miyaki ürününden fotoğraf silindi','DELETE','2025-12-16 12:53:13'),(515,'2025-12-16 15:56:45','Admin User','Issey Miyaki ürününe fotoğraf eklendi','CREATE','2025-12-16 12:56:45'),(516,'2025-12-16 15:56:50','Admin User','Issey Miyaki ürününden fotoğraf silindi','DELETE','2025-12-16 12:56:50'),(517,'2025-12-16 16:00:38','Admin User','Issey Miyaki ürününe fotoğraf eklendi','CREATE','2025-12-16 13:00:38'),(518,'2025-12-16 16:00:46','Admin User','Issey Miyaki ürününe fotoğraf eklendi','CREATE','2025-12-16 13:00:46'),(519,'2025-12-16 16:01:22','Admin User','Issey Miyaki ürününden fotoğraf silindi','DELETE','2025-12-16 13:01:22'),(520,'2025-12-16 16:01:24','Admin User','Issey Miyaki ürününden fotoğraf silindi','DELETE','2025-12-16 13:01:24'),(521,'2025-12-16 16:12:26','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-16 13:12:26'),(522,'2025-12-16 17:09:11','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-16 14:09:11'),(523,'2025-12-16 20:26:32','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-16 17:26:32'),(524,'2025-12-16 20:27:33','Admin User','musteriler tabloları temizlendi','DELETE','2025-12-16 17:27:33'),(525,'2025-12-16 20:30:12','Admin User','MEHMET FATİH GÜZEN müşterisi sisteme eklendi','CREATE','2025-12-16 17:30:12'),(526,'2025-12-16 20:31:30','Admin User','İDRİS GÜZEN müşterisi sisteme eklendi','CREATE','2025-12-16 17:31:30'),(527,'2025-12-16 20:32:04','Admin User','İDRİS GÜZEN müşterisi İDRİS GÜZEN olarak güncellendi','UPDATE','2025-12-16 17:32:04'),(528,'2025-12-16 20:33:22','Admin User','mehmet mutlu personelinin bilgileri güncellendi','UPDATE','2025-12-16 17:33:22'),(529,'2025-12-16 20:52:52','Admin User','Abdullah Usta iş merkezi silindi','DELETE','2025-12-16 17:52:52'),(530,'2025-12-16 20:52:55','Admin User','Montaj Masası 1 iş merkezi silindi','DELETE','2025-12-16 17:52:55'),(531,'2025-12-16 20:53:15','Admin User','AHMET ERSİN GÜZEN iş merkezi eklendi','CREATE','2025-12-16 17:53:15'),(532,'2025-12-16 20:53:39','Admin User','ABUBEKİR ÖNEL iş merkezi eklendi','CREATE','2025-12-16 17:53:39'),(533,'2025-12-16 20:56:56','Admin User','212 Men ürünü sistemden silindi','DELETE','2025-12-16 17:56:56'),(534,'2025-12-16 20:56:59','Admin User','Issey Miyaki ürünü sistemden silindi','DELETE','2025-12-16 17:56:59'),(535,'2025-12-16 21:01:05','Admin User','Merkez Depo deposundaki A1 rafı silindi','DELETE','2025-12-16 18:01:05'),(536,'2025-12-16 21:01:08','Admin User','Merkez Depo deposundaki A2 rafı silindi','DELETE','2025-12-16 18:01:08'),(537,'2025-12-16 21:01:11','Admin User','Merkez Depo deposundaki A3 rafı silindi','DELETE','2025-12-16 18:01:11'),(538,'2025-12-16 21:01:15','Admin User','Satış Depo deposundaki 1 rafı silindi','DELETE','2025-12-16 18:01:15'),(539,'2025-12-16 21:01:18','Admin User','Satış Depo deposundaki Kasa Arkası Rafı rafı silindi','DELETE','2025-12-16 18:01:18'),(540,'2025-12-16 21:01:48','Admin User','DEPO A deposuna A 1 rafı eklendi','CREATE','2025-12-16 18:01:48'),(541,'2025-12-16 21:02:06','Admin User','DEPO B deposuna B 1 rafı eklendi','CREATE','2025-12-16 18:02:06'),(542,'2025-12-16 21:02:21','Admin User','DEPO C deposuna C 1 rafı eklendi','CREATE','2025-12-16 18:02:21'),(543,'2025-12-16 21:03:58','Admin User','DEPO ETİKET deposuna E 1 rafı eklendi','CREATE','2025-12-16 18:03:58'),(544,'2025-12-16 21:09:16','Admin User','DİOR , SAVAGE ürünü sisteme eklendi','CREATE','2025-12-16 18:09:16'),(545,'2025-12-16 21:15:34','Admin User','DİOR , SAVAGE ürününe fotoğraf eklendi','CREATE','2025-12-16 18:15:34'),(546,'2025-12-16 21:18:36','Admin User','MAISON  KURDİJAN , BACARAT  RED  ürünü sisteme eklendi','CREATE','2025-12-16 18:18:36'),(547,'2025-12-16 21:19:23','Admin User','MAISON  KURDİJAN , BACARAT  RED  ürününe fotoğraf eklendi','CREATE','2025-12-16 18:19:23'),(548,'2025-12-16 21:21:48','Admin User','PACO RABANNA , İNVEKTUS EDT  ürünü sisteme eklendi','CREATE','2025-12-16 18:21:48'),(549,'2025-12-16 21:22:46','Admin User','PACO RABANNA , İNVEKTUS EDT  ürününe fotoğraf eklendi','CREATE','2025-12-16 18:22:46'),(550,'2025-12-16 21:27:52','Admin User','KİLİAN , ANGELS SHARE ürünü sisteme eklendi','CREATE','2025-12-16 18:27:52'),(551,'2025-12-16 21:28:49','Admin User','KİLİAN , ANGELS SHARE ürününe fotoğraf eklendi','CREATE','2025-12-16 18:28:49'),(552,'2025-12-16 21:30:34','Admin User','VIKTORIA  SECRET , BOM ŞHEL ürünü sisteme eklendi','CREATE','2025-12-16 18:30:34'),(553,'2025-12-16 21:31:20','Admin User','VIKTORIA  SECRET , BOM ŞHEL ürününe fotoğraf eklendi','CREATE','2025-12-16 18:31:20'),(554,'2025-12-16 21:34:23','Admin User','idris personelinin bilgileri güncellendi','UPDATE','2025-12-16 18:34:23'),(555,'2025-12-16 21:35:05','Admin User','Ahmet Kozmetik tedarikçisi sistemden silindi','DELETE','2025-12-16 18:35:05'),(556,'2025-12-16 21:36:31','Admin User','MEHMET KIRMIZIGÜL tedarikçisi sisteme eklendi','CREATE','2025-12-16 18:36:31'),(557,'2025-12-16 21:37:20','Admin User','ŞENER BULUŞ tedarikçisi sisteme eklendi','CREATE','2025-12-16 18:37:20'),(558,'2025-12-16 21:38:22','Admin User','LUZKİM tedarikçisi sisteme eklendi','CREATE','2025-12-16 18:38:22'),(559,'2025-12-16 21:38:46','Admin User','SARI ETİKET tedarikçisi sisteme eklendi','CREATE','2025-12-16 18:38:46'),(560,'2025-12-16 21:39:31','Admin User','RAMAZAN BOZTEPE tedarikçisi sisteme eklendi','CREATE','2025-12-16 18:39:31'),(561,'2025-12-16 21:40:15','Admin User','ALKOLCU tedarikçisi sisteme eklendi','CREATE','2025-12-16 18:40:15'),(562,'2025-12-16 21:41:57','Admin User','Gül Kokusu Esansı esansı sistemden silindi','DELETE','2025-12-16 18:41:57'),(563,'2025-12-16 21:42:57','Admin User','Bilinmeyen Tank adlı tank silindi','DELETE','2025-12-16 18:42:57'),(564,'2025-12-16 21:43:02','Admin User','Bilinmeyen Tank adlı tank silindi','DELETE','2025-12-16 18:43:02'),(565,'2025-12-16 21:43:06','Admin User','Bilinmeyen Tank adlı tank silindi','DELETE','2025-12-16 18:43:06'),(566,'2025-12-16 21:43:37','Admin User','W 528 adlı tank sisteme eklendi','CREATE','2025-12-16 18:43:37'),(567,'2025-12-16 21:44:00','Admin User','W 520 adlı tank sisteme eklendi','CREATE','2025-12-16 18:44:00'),(568,'2025-12-16 21:44:20','Admin User','E 181 adlı tank sisteme eklendi','CREATE','2025-12-16 18:44:20'),(569,'2025-12-16 21:44:40','Admin User','E 100 adlı tank sisteme eklendi','CREATE','2025-12-16 18:44:40'),(570,'2025-12-16 21:45:04','Admin User','E 121 adlı tank sisteme eklendi','CREATE','2025-12-16 18:45:04'),(571,'2025-12-16 21:47:42','Admin User','DİOR , SAVAGE esansı sisteme eklendi','CREATE','2025-12-16 18:47:42'),(572,'2025-12-16 21:49:54','Admin User','KİLİAN , ANGELS SHARE esansı sisteme eklendi','CREATE','2025-12-16 18:49:54'),(573,'2025-12-16 21:51:04','Admin User','KİLİAN , ANGELS SHARE esansı güncellendi','UPDATE','2025-12-16 18:51:04'),(574,'2025-12-16 21:52:55','Admin User','VIKTORIA SECRET , BOM ŞHEL esansı sisteme eklendi','CREATE','2025-12-16 18:52:55'),(575,'2025-12-16 22:11:10','Admin User','DEPO TAKIM deposuna TAKIM rafı eklendi','CREATE','2025-12-16 19:11:10'),(576,'2025-12-16 22:12:28','Admin User','DİOR SAVAE  malzemesi sisteme eklendi','CREATE','2025-12-16 19:12:28'),(577,'2025-12-16 22:12:56','Admin User','DİOR SAVAE  ürününe fotoğraf eklendi','CREATE','2025-12-16 19:12:56'),(578,'2025-12-16 22:21:00','Admin User','DİOR SAVAGE malzemesi sisteme eklendi','CREATE','2025-12-16 19:21:00'),(579,'2025-12-16 22:23:10','Admin User','VIKTORIA SECRET , BOM ŞHEL malzemesi sisteme eklendi','CREATE','2025-12-16 19:23:10'),(580,'2025-12-16 22:29:50','Admin User','VIKTORIA SECRET , BOM ŞHEL malzemesi sistemden silindi','DELETE','2025-12-16 19:29:50'),(581,'2025-12-16 22:29:53','Admin User','DİOR SAVAGE malzemesi sistemden silindi','DELETE','2025-12-16 19:29:53'),(582,'2025-12-16 22:33:11','Admin User','DİOR SAVAE  malzemesi DİOR SAVAGE TAKIM olarak güncellendi','UPDATE','2025-12-16 19:33:11'),(583,'2025-12-16 22:34:03','Admin User','DİOR SAVAGE KUTU malzemesi sisteme eklendi','CREATE','2025-12-16 19:34:03'),(584,'2025-12-16 22:35:30','Admin User','KİLİAN , ANGELS SHARE TAKIM malzemesi sisteme eklendi','CREATE','2025-12-16 19:35:30'),(585,'2025-12-16 22:36:44','Admin User','KİLİAN , ANGELS SHARE KUTU malzemesi sisteme eklendi','CREATE','2025-12-16 19:36:44'),(586,'2025-12-16 22:37:57','Admin User','PACO RABANNA , İNVEKTUS EDT TAKIM malzemesi sisteme eklendi','CREATE','2025-12-16 19:37:57'),(587,'2025-12-16 22:38:38','Admin User','PACO RABANNA , İNVEKTUS EDT KUTU malzemesi sisteme eklendi','CREATE','2025-12-16 19:38:38'),(588,'2025-12-16 22:39:23','Admin User','212 Men ürün ağacından 212 Man Etiketi bileşeni silindi','DELETE','2025-12-16 19:39:23'),(589,'2025-12-16 22:39:26','Admin User','212 Men ürün ağacından A1 Dış Kutu bileşeni silindi','DELETE','2025-12-16 19:39:26'),(590,'2025-12-16 22:39:29','Admin User','212 Men ürün ağacından 212 Men Kapat bileşeni silindi','DELETE','2025-12-16 19:39:29'),(591,'2025-12-16 22:39:32','Admin User','212 Men ürün ağacından 212 Men Şişesi bileşeni silindi','DELETE','2025-12-16 19:39:32'),(592,'2025-12-16 22:39:35','Admin User','212 Men ürün ağacından Gül Kokusu Esansı bileşeni silindi','DELETE','2025-12-16 19:39:35'),(593,'2025-12-16 23:06:40','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-16 20:06:40'),(594,'2025-12-17 10:01:17','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-17 07:01:17'),(595,'2025-12-17 10:11:51','Admin User','DİOR , SAVAGE ürün ağacına DİOR SAVAGE KUTU bileşeni eklendi','CREATE','2025-12-17 07:11:51'),(596,'2025-12-17 10:18:40','Admin User','Gül Kokusu Esansı ürün ağacından Saf Su bileşeni silindi','DELETE','2025-12-17 07:18:40'),(597,'2025-12-17 10:18:44','Admin User','Gül Kokusu Esansı ürün ağacından Alkol bileşeni silindi','DELETE','2025-12-17 07:18:44'),(598,'2025-12-17 10:23:04','Admin User','DİOR SAVAGE ALKOL malzemesi sisteme eklendi','CREATE','2025-12-17 07:23:04'),(599,'2025-12-17 10:31:45','Admin User','ALKOLCU tedarikçisi ALKOLCU olarak güncellendi','UPDATE','2025-12-17 07:31:45'),(600,'2025-12-17 16:10:30','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-17 13:10:30'),(601,'2025-12-17 17:21:18','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-17 14:21:18'),(602,'2025-12-17 17:24:48','Admin User','VİP YAZILIM tedarikçisi sisteme eklendi','CREATE','2025-12-17 14:24:48'),(603,'2025-12-17 17:25:59','Admin User','GÖKHAN  tedarikçisi sisteme eklendi','CREATE','2025-12-17 14:25:59'),(604,'2025-12-17 17:26:32','Admin User','ALKOLCU tedarikçisi ALKOLCU olarak güncellendi','UPDATE','2025-12-17 14:26:32'),(605,'2025-12-17 17:26:41','Admin User','MEHMET KIRMIZIGÜL tedarikçisi MEHMET KIRMIZIGÜL olarak güncellendi','UPDATE','2025-12-17 14:26:41'),(606,'2025-12-17 17:26:54','Admin User','RAMAZAN BOZTEPE tedarikçisi RAMAZAN BOZTEPE olarak güncellendi','UPDATE','2025-12-17 14:26:54'),(607,'2025-12-17 17:27:04','Admin User','SARI ETİKET tedarikçisi SARI ETİKET olarak güncellendi','UPDATE','2025-12-17 14:27:04'),(608,'2025-12-17 17:27:16','Admin User','ŞENER BULUŞ tedarikçisi ŞENER BULUŞ olarak güncellendi','UPDATE','2025-12-17 14:27:16'),(609,'2025-12-17 17:27:25','Admin User','VİP YAZILIM tedarikçisi VİP YAZILIM olarak güncellendi','UPDATE','2025-12-17 14:27:25'),(610,'2025-12-17 17:28:38','Admin User','ALKOLCU tedarikçisi ZÜLKÜF KIRMIZIGÜL olarak güncellendi','UPDATE','2025-12-17 14:28:38'),(611,'2025-12-17 17:29:09','Admin User','LUZKİM tedarikçisi LUZKİM olarak güncellendi','UPDATE','2025-12-17 14:29:09'),(612,'2025-12-17 17:30:22','Admin User','DİOR SAVAGE ALKOL malzemesi sistemden silindi','DELETE','2025-12-17 14:30:22'),(613,'2025-12-17 17:30:25','Admin User','PACO RABANNA , İNVEKTUS EDT KUTU malzemesi sistemden silindi','DELETE','2025-12-17 14:30:25'),(614,'2025-12-17 17:30:27','Admin User','PACO RABANNA , İNVEKTUS EDT TAKIM malzemesi sistemden silindi','DELETE','2025-12-17 14:30:27'),(615,'2025-12-17 17:30:30','Admin User','KİLİAN , ANGELS SHARE KUTU malzemesi sistemden silindi','DELETE','2025-12-17 14:30:30'),(616,'2025-12-17 17:30:33','Admin User','KİLİAN , ANGELS SHARE TAKIM malzemesi sistemden silindi','DELETE','2025-12-17 14:30:33'),(617,'2025-12-17 17:30:36','Admin User','DİOR SAVAGE KUTU malzemesi sistemden silindi','DELETE','2025-12-17 14:30:36'),(618,'2025-12-17 17:30:40','Admin User','DİOR SAVAGE TAKIM malzemesi sistemden silindi','DELETE','2025-12-17 14:30:40'),(619,'2025-12-17 17:31:29','Admin User','DİOR SAVAGE KUTU malzemesi sisteme eklendi','CREATE','2025-12-17 14:31:29'),(620,'2025-12-17 17:36:01','Admin User','MEHMET KIRMIZIGÜL tedarikçisine DİOR SAVAGE KUTU malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-17 14:36:01'),(621,'2025-12-18 01:30:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-17 22:30:14'),(622,'2025-12-18 02:21:31','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-17 23:21:31'),(623,'2025-12-18 03:01:17','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-18 00:01:17'),(624,'2025-12-18 03:09:20','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-18 00:09:20'),(625,'2025-12-18 03:09:39','Admin User','DİOR , SAVAGE ürünü DİOR , SAVAGE olarak güncellendi','UPDATE','2025-12-18 00:09:39'),(626,'2025-12-18 03:09:46','Admin User','DİOR , SAVAGE ürünü DİOR , SAVAGE olarak güncellendi','UPDATE','2025-12-18 00:09:46'),(627,'2025-12-18 03:12:07','Admin User','DİOR , SAVAGE ürününe fotoğraf eklendi','CREATE','2025-12-18 00:12:07'),(628,'2025-12-18 03:12:22','Admin User','DİOR , SAVAGE ürününden fotoğraf silindi','DELETE','2025-12-18 00:12:22'),(629,'2025-12-19 13:16:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 10:16:36'),(630,'2025-12-19 13:17:34','Admin User','Malzeme türü silindi: Şişe (sise)','DELETE','2025-12-19 10:17:34'),(631,'2025-12-19 13:17:37','Admin User','Malzeme türü silindi: Kutu (kutu)','DELETE','2025-12-19 10:17:37'),(632,'2025-12-19 13:17:41','Admin User','Malzeme türü silindi: Pompa (pompa)','DELETE','2025-12-19 10:17:41'),(633,'2025-12-19 13:17:45','Admin User','Malzeme türü silindi: İç Ambalaj (ic_ambalaj)','DELETE','2025-12-19 10:17:45'),(634,'2025-12-19 13:17:47','Admin User','Malzeme türü silindi: Numune Þiþesi (numune_sisesi)','DELETE','2025-12-19 10:17:47'),(635,'2025-12-19 13:17:50','Admin User','Malzeme türü silindi: Kapak (kapak)','DELETE','2025-12-19 10:17:50'),(636,'2025-12-19 13:17:56','Admin User','Malzeme türü silindi: Renklendirici (renklendirici)','DELETE','2025-12-19 10:17:56'),(637,'2025-12-19 13:17:59','Admin User','Malzeme türü silindi: Esans (esans)','DELETE','2025-12-19 10:17:59'),(638,'2025-12-19 13:18:02','Admin User','Malzeme türü silindi: Kimyasal Madde (kimyasal_madde)','DELETE','2025-12-19 10:18:02'),(639,'2025-12-19 13:18:07','Admin User','Malzeme türü silindi: Alkol (alkol)','DELETE','2025-12-19 10:18:07'),(640,'2025-12-19 13:18:10','Admin User','Malzeme türü silindi: Saf Su (saf_su)','DELETE','2025-12-19 10:18:10'),(641,'2025-12-19 13:18:13','Admin User','Malzeme türü silindi: Çözücü (cozucu)','DELETE','2025-12-19 10:18:13'),(642,'2025-12-19 13:18:16','Admin User','Malzeme türü silindi: Koruyucu (koruyucu)','DELETE','2025-12-19 10:18:16'),(643,'2025-12-19 13:18:19','Admin User','Malzeme türü silindi: Karton Ara Bölme (karton_ara_bolme)','DELETE','2025-12-19 10:18:19'),(644,'2025-12-19 13:18:22','Admin User','Malzeme türü silindi: Diğer (diger)','DELETE','2025-12-19 10:18:22'),(645,'2025-12-19 13:18:25','Admin User','Malzeme türü silindi: Takım (takim)','DELETE','2025-12-19 10:18:25'),(646,'2025-12-19 13:18:41','Admin User','Yeni malzeme türü eklendi: Alkol (lkol)','CREATE','2025-12-19 10:18:41'),(647,'2025-12-19 13:18:49','Admin User','Yeni malzeme türü eklendi: Etiket (tiket)','CREATE','2025-12-19 10:18:49'),(648,'2025-12-19 13:18:54','Admin User','Malzeme türü silindi: Etiket (tiket)','DELETE','2025-12-19 10:18:54'),(649,'2025-12-19 13:19:00','Admin User','Malzeme türü silindi: Alkol (lkol)','DELETE','2025-12-19 10:19:00'),(650,'2025-12-19 13:19:08','Admin User','Yeni malzeme türü eklendi: alkol (alkol)','CREATE','2025-12-19 10:19:08'),(651,'2025-12-19 13:19:52','Admin User','Yeni malzeme türü eklendi: Takım (takim)','CREATE','2025-12-19 10:19:52'),(652,'2025-12-19 13:28:01','Admin User','Yeni malzeme türü eklendi: Jelatin (jelatin)','CREATE','2025-12-19 10:28:01'),(653,'2025-12-19 13:28:17','Admin User','Yeni malzeme türü eklendi: Dış Kutu (dis_kutu)','CREATE','2025-12-19 10:28:17'),(654,'2025-12-19 13:28:34','Admin User','Yeni malzeme türü eklendi: Yazıcı (yazici)','CREATE','2025-12-19 10:28:34'),(655,'2025-12-19 13:28:49','Admin User','Yeni malzeme türü eklendi: Koli (koli)','CREATE','2025-12-19 10:28:49'),(656,'2025-12-19 13:29:00','Admin User','Yeni malzeme türü eklendi: Koli Bantı (koli_banti)','CREATE','2025-12-19 10:29:00'),(657,'2025-12-19 13:29:12','Admin User','Yeni malzeme türü eklendi: Kutu (kutu)','CREATE','2025-12-19 10:29:12'),(658,'2025-12-19 15:38:05','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 12:38:05'),(659,'2025-12-19 15:42:59','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 12:42:59'),(660,'2025-12-19 15:43:18','Admin User','Saf Su ürünü için stok hareketi silindi (ID: 6)','DELETE','2025-12-19 12:43:18'),(661,'2025-12-19 15:43:31','Admin User','212 Men ürünü için stok hareketi silindi (ID: 4)','DELETE','2025-12-19 12:43:31'),(662,'2025-12-19 15:49:05','Admin User','DİOR SAVAGE malzemesi sisteme eklendi','CREATE','2025-12-19 12:49:05'),(663,'2025-12-19 15:52:50','Admin User','xx malzemesi sisteme eklendi','CREATE','2025-12-19 12:52:50'),(664,'2025-12-19 15:52:57','Admin User','xx malzemesi sistemden silindi','DELETE','2025-12-19 12:52:57'),(665,'2025-12-19 15:53:24','Admin User','DİOR SAVAGE TAKIM malzemesi sisteme eklendi','CREATE','2025-12-19 12:53:24'),(666,'2025-12-19 15:53:46','Admin User','DİOR SAVAGE malzemesi DİOR SAVAGE ETİKET olarak güncellendi','UPDATE','2025-12-19 12:53:46'),(667,'2025-12-19 15:56:03','Admin User','DİOR SAVAGE JELATİN malzemesi sisteme eklendi','CREATE','2025-12-19 12:56:03'),(668,'2025-12-19 15:57:28','Admin User','DİOR SAVAGE DIŞ KUTU malzemesi sisteme eklendi','CREATE','2025-12-19 12:57:28'),(669,'2025-12-19 15:58:08','Admin User','DİOR SAVAGE KUTU malzemesi DİOR SAVAGE KUTU olarak güncellendi','UPDATE','2025-12-19 12:58:08'),(670,'2025-12-19 16:02:28','Admin User','DİOR  malzemesi sisteme eklendi','CREATE','2025-12-19 13:02:28'),(671,'2025-12-19 16:05:38','Admin User','KİLİAN , ANGELS SHARE esansı sistemden silindi','DELETE','2025-12-19 13:05:38'),(672,'2025-12-19 16:05:41','Admin User','VIKTORIA SECRET , BOM ŞHEL esansı sistemden silindi','DELETE','2025-12-19 13:05:41'),(673,'2025-12-19 16:07:54','Admin User','MEHMET KIRMIZIGÜL tedarikçisine ait DİOR SAVAGE KUTU malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-19 13:07:54'),(674,'2025-12-19 16:08:01','Admin User','Ahmet Kozmetik tedarikçisine ait Saf Su malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-19 13:08:01'),(675,'2025-12-19 16:09:04','Admin User','MEHMET KIRMIZIGÜL tedarikçisine DİOR SAVAGE TAKIM malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-19 13:09:04'),(676,'2025-12-19 16:11:21','Admin User','GÖKHAN  tedarikçisine DİOR SAVAGE JELATİN malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-19 13:11:21'),(677,'2025-12-19 16:12:04','Admin User','ŞENER BULUŞ tedarikçisine DİOR SAVAGE KUTU malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-19 13:12:04'),(678,'2025-12-19 16:13:06','Admin User','SARI ETİKET tedarikçisine DİOR SAVAGE ETİKET malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-19 13:13:06'),(679,'2025-12-19 16:14:14','Admin User','RAMAZAN BOZTEPE tedarikçisine DİOR SAVAGE DIŞ KUTU malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-19 13:14:14'),(680,'2025-12-19 16:16:20','Admin User','DİOR , SAVAGE ürün ağacına DİOR SAVAGE DIŞ KUTU bileşeni eklendi','CREATE','2025-12-19 13:16:20'),(681,'2025-12-19 16:16:34','Admin User','DİOR , SAVAGE ürün ağacına DİOR SAVAGE KUTU bileşeni eklendi','CREATE','2025-12-19 13:16:34'),(682,'2025-12-19 16:16:50','Admin User','DİOR , SAVAGE ürün ağacına DİOR SAVAGE TAKIM bileşeni eklendi','CREATE','2025-12-19 13:16:50'),(683,'2025-12-19 16:18:03','Admin User','DİOR , SAVAGE ürün ağacındaki DİOR SAVAGE KUTU bileşeni DİOR SAVAGE KUTU olarak güncellendi','UPDATE','2025-12-19 13:18:03'),(684,'2025-12-19 16:18:14','Admin User','DİOR , SAVAGE ürün ağacındaki DİOR SAVAGE TAKIM bileşeni DİOR SAVAGE TAKIM olarak güncellendi','UPDATE','2025-12-19 13:18:14'),(685,'2025-12-19 16:18:25','Admin User','DİOR , SAVAGE ürün ağacındaki DİOR SAVAGE DIŞ KUTU bileşeni DİOR SAVAGE DIŞ KUTU olarak güncellendi','UPDATE','2025-12-19 13:18:25'),(686,'2025-12-19 16:19:04','Admin User','DİOR , SAVAGE ürün ağacına DİOR SAVAGE JELATİN bileşeni eklendi','CREATE','2025-12-19 13:19:04'),(687,'2025-12-19 16:21:53','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-19 13:21:53'),(688,'2025-12-19 16:22:02','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 13:22:02'),(689,'2025-12-19 16:22:21','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-19 13:22:21'),(690,'2025-12-19 16:23:11','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 13:23:11'),(691,'2025-12-19 16:25:36','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-19 13:25:36'),(692,'2025-12-19 20:37:17','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 17:37:17'),(693,'2025-12-19 20:41:43','Admin User','DİOR SAVAGE  ürünü sisteme eklendi','CREATE','2025-12-19 17:41:43'),(694,'2025-12-19 20:45:12','Admin User','DİOR SAVAGE  ürün ağacına DİOR SAVAGE ETİKET bileşeni eklendi','CREATE','2025-12-19 17:45:12'),(695,'2025-12-19 20:55:50','Admin User','OSMAN GÜZEN müşterisi sisteme eklendi','CREATE','2025-12-19 17:55:50'),(696,'2025-12-19 21:12:39','Admin User','DİOR , SAVAGE ürünü için montaj iş emri oluşturuldu','CREATE','2025-12-19 18:12:39'),(697,'2025-12-19 21:14:58','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-19 18:14:58'),(698,'2025-12-19 21:15:25','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 18:15:25'),(699,'2025-12-19 21:16:52','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-19 18:16:52'),(700,'2025-12-19 21:20:30','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 18:20:30'),(701,'2025-12-19 21:23:40','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-19 18:23:40'),(702,'2025-12-19 21:26:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-19 18:26:36'),(703,'2025-12-19 21:30:00','Admin User','bünyamin  personelinin bilgileri güncellendi','UPDATE','2025-12-19 18:30:00'),(704,'2025-12-19 21:31:32','Admin User','Depo 4 deposuna Giriş sağ  rafı eklendi','CREATE','2025-12-19 18:31:32'),(705,'2025-12-19 21:39:53','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-19 18:39:53'),(706,'2025-12-20 10:05:34','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-20 07:05:34'),(707,'2025-12-20 10:06:53','Admin User','KİLİAN , ANGELS SHARE ürünü KİLİAN , ANGELS SHARE olarak güncellendi','UPDATE','2025-12-20 07:06:53'),(708,'2025-12-20 10:10:12','Admin User','RAMAZAN BOZTEPE tedarikçisine DİOR SAVAGE DIŞ KUTU malzemesi için çerçeve sözleşme güncellendi','UPDATE','2025-12-20 07:10:12'),(709,'2025-12-20 10:13:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-20 07:13:14'),(710,'2025-12-20 11:10:47','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-20 08:10:47'),(711,'2025-12-20 11:16:33','Admin User','kutu deposuna raf 1 rafı eklendi','CREATE','2025-12-20 08:16:33'),(712,'2025-12-20 11:17:38','Admin User','KİLİAN , ANGELS SHARE kutu malzemesi sisteme eklendi','CREATE','2025-12-20 08:17:38'),(713,'2025-12-20 11:18:24','Admin User','KİLİAN , ANGELS SHARE etiket malzemesi sisteme eklendi','CREATE','2025-12-20 08:18:24'),(714,'2025-12-20 11:19:18','Admin User','KİLİAN , ANGELS SHARE takım malzemesi sisteme eklendi','CREATE','2025-12-20 08:19:18'),(715,'2025-12-20 11:20:37','Admin User','jelatin deposuna 1 rafı eklendi','CREATE','2025-12-20 08:20:37'),(716,'2025-12-20 11:21:11','Admin User','KİLİAN , ANGELS SHARE jelatin malzemesi sisteme eklendi','CREATE','2025-12-20 08:21:11'),(717,'2025-12-20 11:22:45','Admin User','dış kutu deposuna 1 rafı eklendi','CREATE','2025-12-20 08:22:45'),(718,'2025-12-20 11:23:21','Admin User','KİLİAN , ANGELS SHARE dış kutu malzemesi sisteme eklendi','CREATE','2025-12-20 08:23:21'),(719,'2025-12-20 11:25:24','Admin User','RAMAZAN BOZTEPE tedarikçisine DİOR SAVAGE DIŞ KUTU malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-20 08:25:24'),(720,'2025-12-20 11:30:27','Admin User','DİOR , SAVAGE ürünü sistemden silindi','DELETE','2025-12-20 08:30:27'),(721,'2025-12-20 11:30:30','Admin User','DİOR SAVAGE  ürünü sistemden silindi','DELETE','2025-12-20 08:30:30'),(722,'2025-12-20 11:30:34','Admin User','KİLİAN , ANGELS SHARE ürünü sistemden silindi','DELETE','2025-12-20 08:30:34'),(723,'2025-12-20 11:30:39','Admin User','MAISON  KURDİJAN , BACARAT  RED  ürünü sistemden silindi','DELETE','2025-12-20 08:30:39'),(724,'2025-12-20 11:30:43','Admin User','PACO RABANNA , İNVEKTUS EDT  ürünü sistemden silindi','DELETE','2025-12-20 08:30:43'),(725,'2025-12-20 11:30:51','Admin User','VIKTORIA  SECRET , BOM ŞHEL ürünü sistemden silindi','DELETE','2025-12-20 08:30:51'),(726,'2025-12-20 11:32:22','Admin User','dior savage ürünü sisteme eklendi','CREATE','2025-12-20 08:32:22'),(727,'2025-12-20 11:33:15','Admin User','chanel no 5 ürünü sisteme eklendi','CREATE','2025-12-20 08:33:15'),(728,'2025-12-20 11:33:36','Admin User','chanel no 5 ürünü chanel no 5 olarak güncellendi','UPDATE','2025-12-20 08:33:36'),(729,'2025-12-20 11:34:53','Admin User','bacarat ret ürünü sisteme eklendi','CREATE','2025-12-20 08:34:53'),(730,'2025-12-20 11:36:02','Admin User','savage elixer ürünü sisteme eklendi','CREATE','2025-12-20 08:36:02'),(731,'2025-12-20 11:36:53','Admin User','ysl y ürünü sisteme eklendi','CREATE','2025-12-20 08:36:53'),(732,'2025-12-20 11:38:13','Admin User','bacarat ret ürünü bacarat ret olarak güncellendi','UPDATE','2025-12-20 08:38:13'),(733,'2025-12-20 11:39:40','Admin User','DİOR , SAVAGE esansı sistemden silindi','DELETE','2025-12-20 08:39:40'),(734,'2025-12-20 11:40:47','Admin User','DİOR , SAVAGE esansı sisteme eklendi','CREATE','2025-12-20 08:40:47'),(735,'2025-12-20 11:41:43','Admin User','chanel no 5 esansı sisteme eklendi','CREATE','2025-12-20 08:41:43'),(736,'2025-12-20 11:41:55','Admin User','DİOR , SAVAGE esansı güncellendi','UPDATE','2025-12-20 08:41:55'),(737,'2025-12-20 11:59:07','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-20 08:59:07'),(738,'2025-12-20 13:46:06','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-20 10:46:06'),(739,'2025-12-20 13:46:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-20 10:46:39'),(740,'2025-12-20 17:23:50','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-20 14:23:50'),(741,'2025-12-22 09:46:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-22 06:46:14'),(742,'2025-12-22 09:49:27','Admin User','chanel no 5 esansı güncellendi','UPDATE','2025-12-22 06:49:27'),(743,'2025-12-22 09:49:44','Admin User','DİOR , SAVAGE esansı güncellendi','UPDATE','2025-12-22 06:49:44'),(744,'2025-12-22 09:51:10','Admin User','bacarat red esansı sisteme eklendi','CREATE','2025-12-22 06:51:10'),(745,'2025-12-22 09:51:54','Admin User','ysl y esansı sisteme eklendi','CREATE','2025-12-22 06:51:54'),(746,'2025-12-22 09:52:26','Admin User','savage elixer esansı sisteme eklendi','CREATE','2025-12-22 06:52:26'),(747,'2025-12-22 09:53:17','Admin User','DİOR SAVAGE DIŞ KUTU malzemesi DİOR SAVAGE DIŞ KUTU olarak güncellendi','UPDATE','2025-12-22 06:53:17'),(748,'2025-12-22 09:55:19','Admin User','ysl y dış kutu malzemesi sisteme eklendi','CREATE','2025-12-22 06:55:19'),(749,'2025-12-22 09:55:51','Admin User','ysl y kutu malzemesi sisteme eklendi','CREATE','2025-12-22 06:55:51'),(750,'2025-12-22 09:56:34','Admin User','ysl y etiket malzemesi sisteme eklendi','CREATE','2025-12-22 06:56:34'),(751,'2025-12-22 09:57:02','Admin User','ysl y takım malzemesi sisteme eklendi','CREATE','2025-12-22 06:57:02'),(752,'2025-12-22 09:58:07','Admin User','yazıcı deposuna 1 rafı eklendi','CREATE','2025-12-22 06:58:07'),(753,'2025-12-22 09:59:14','Admin User','ysl y yazıcı malzemesi sisteme eklendi','CREATE','2025-12-22 06:59:14'),(754,'2025-12-22 10:00:15','Admin User','ysl y jilatin malzemesi sisteme eklendi','CREATE','2025-12-22 07:00:15'),(755,'2025-12-22 10:12:29','Admin User','KİLİAN , ANGELS SHARE YAZICI malzemesi sisteme eklendi','CREATE','2025-12-22 07:12:29'),(756,'2025-12-22 10:15:57','Admin User','CHANEL NO5 TİKET malzemesi sisteme eklendi','CREATE','2025-12-22 07:15:57'),(757,'2025-12-22 10:16:22','Admin User','CHANEL NO5 TAKIM malzemesi sisteme eklendi','CREATE','2025-12-22 07:16:22'),(758,'2025-12-22 10:16:58','Admin User','CHANEL NO5 JELATİN malzemesi sisteme eklendi','CREATE','2025-12-22 07:16:58'),(759,'2025-12-22 10:17:22','Admin User','CHANEL NO5 DIŞ KUTU malzemesi sisteme eklendi','CREATE','2025-12-22 07:17:22'),(760,'2025-12-22 10:17:53','Admin User','CHANEL NO5 YAZICI malzemesi sisteme eklendi','CREATE','2025-12-22 07:17:53'),(761,'2025-12-22 10:18:23','Admin User','CHANEL NO5 KUTU malzemesi sisteme eklendi','CREATE','2025-12-22 07:18:23'),(762,'2025-12-22 10:22:59','Admin User','DİOR  malzemesi sistemden silindi','DELETE','2025-12-22 07:22:59'),(763,'2025-12-22 10:23:26','Admin User','DİOR SAVAGE DIŞ KUTU malzemesi DİOR SAVAGE DIŞ KUTU olarak güncellendi','UPDATE','2025-12-22 07:23:26'),(764,'2025-12-22 10:23:39','Admin User','DİOR SAVAGE JELATİN malzemesi DİOR SAVAGE JELATİN olarak güncellendi','UPDATE','2025-12-22 07:23:39'),(765,'2025-12-22 10:23:51','Admin User','DİOR SAVAGE TAKIM malzemesi DİOR SAVAGE TAKIM olarak güncellendi','UPDATE','2025-12-22 07:23:51'),(766,'2025-12-22 10:24:02','Admin User','DİOR SAVAGE JELATİN malzemesi DİOR SAVAGE JELATİN olarak güncellendi','UPDATE','2025-12-22 07:24:02'),(767,'2025-12-22 10:24:14','Admin User','DİOR SAVAGE ETİKET malzemesi DİOR SAVAGE ETİKET olarak güncellendi','UPDATE','2025-12-22 07:24:14'),(768,'2025-12-22 10:24:23','Admin User','DİOR SAVAGE ETİKET malzemesi DİOR SAVAGE ETİKET olarak güncellendi','UPDATE','2025-12-22 07:24:23'),(769,'2025-12-22 10:25:54','Admin User','DİOR SAVAGE YAZICI malzemesi sisteme eklendi','CREATE','2025-12-22 07:25:54'),(770,'2025-12-22 10:27:16','Admin User','SAVAGE ELİXER ETİKET malzemesi sisteme eklendi','CREATE','2025-12-22 07:27:16'),(771,'2025-12-22 10:27:40','Admin User','SAVAGE ELİXER TAKIM malzemesi sisteme eklendi','CREATE','2025-12-22 07:27:40'),(772,'2025-12-22 10:28:03','Admin User','SAVAGE ELİXER JELATİN malzemesi sisteme eklendi','CREATE','2025-12-22 07:28:03'),(773,'2025-12-22 10:28:53','Admin User','SAVAGE ELİXER DIŞ KUTU malzemesi sisteme eklendi','CREATE','2025-12-22 07:28:53'),(774,'2025-12-22 10:29:35','Admin User','SAVAGE ELİXER YAZICI malzemesi sisteme eklendi','CREATE','2025-12-22 07:29:35'),(775,'2025-12-22 10:30:10','Admin User','SAVAGE ELİXER KUTU malzemesi sisteme eklendi','CREATE','2025-12-22 07:30:10'),(776,'2025-12-22 10:31:51','Admin User','CHANEL NO5 KUTU malzemesi CHANEL NO5 KUTU olarak güncellendi','UPDATE','2025-12-22 07:31:51'),(777,'2025-12-22 10:34:30','Admin User','DİOR , SAVAGE ürün ağacından DİOR SAVAGE DIŞ KUTU bileşeni silindi','DELETE','2025-12-22 07:34:30'),(778,'2025-12-22 10:37:14','Admin User','savage elixer ürün ağacına SAVAGE ELİXER DIŞ KUTU bileşeni eklendi','CREATE','2025-12-22 07:37:14'),(779,'2025-12-22 10:37:52','Admin User','savage elixer ürün ağacına SAVAGE ELİXER ETİKET bileşeni eklendi','CREATE','2025-12-22 07:37:52'),(780,'2025-12-22 10:38:19','Admin User','savage elixer ürün ağacına SAVAGE ELİXER TAKIM bileşeni eklendi','CREATE','2025-12-22 07:38:19'),(781,'2025-12-22 10:38:45','Admin User','savage elixer ürün ağacına SAVAGE ELİXER TAKIM bileşeni eklendi','CREATE','2025-12-22 07:38:45'),(782,'2025-12-22 10:40:50','Admin User','ysl y ürün ağacına ysl y etiket bileşeni eklendi','CREATE','2025-12-22 07:40:50'),(783,'2025-12-22 10:41:08','Admin User','ysl y ürün ağacına ysl y kutu bileşeni eklendi','CREATE','2025-12-22 07:41:08'),(784,'2025-12-22 10:41:32','Admin User','ysl y ürün ağacına ysl y takım bileşeni eklendi','CREATE','2025-12-22 07:41:32'),(785,'2025-12-22 10:41:58','Admin User','savage elixer ürün ağacından SAVAGE ELİXER TAKIM bileşeni silindi','DELETE','2025-12-22 07:41:58'),(786,'2025-12-22 10:42:18','Admin User','savage elixer ürün ağacından SAVAGE ELİXER ETİKET bileşeni silindi','DELETE','2025-12-22 07:42:18'),(787,'2025-12-22 10:42:52','Admin User','savage elixer ürün ağacından SAVAGE ELİXER DIŞ KUTU bileşeni silindi','DELETE','2025-12-22 07:42:52'),(788,'2025-12-22 10:43:17','Admin User','DİOR , SAVAGE ürün ağacından DİOR SAVAGE JELATİN bileşeni silindi','DELETE','2025-12-22 07:43:17'),(789,'2025-12-22 10:44:20','Admin User','savage elixer ürün ağacına ysl y etiket bileşeni eklendi','CREATE','2025-12-22 07:44:20'),(790,'2025-12-22 10:45:10','Admin User','savage elixer ürün ağacına SAVAGE ELİXER KUTU bileşeni eklendi','CREATE','2025-12-22 07:45:10'),(791,'2025-12-22 10:45:45','Admin User','DİOR SAVAGE  ürün ağacındaki DİOR SAVAGE ETİKET bileşeni DİOR SAVAGE ETİKET olarak güncellendi','UPDATE','2025-12-22 07:45:45'),(792,'2025-12-22 10:46:14','Admin User','dior savage ürün ağacındaki DİOR SAVAGE ETİKET bileşeni DİOR SAVAGE ETİKET olarak güncellendi','UPDATE','2025-12-22 07:46:14'),(793,'2025-12-22 10:47:18','Admin User','chanel no 5 ürün ağacına CHANEL NO5 KUTU bileşeni eklendi','CREATE','2025-12-22 07:47:18'),(794,'2025-12-22 10:47:37','Admin User','chanel no 5 ürün ağacına CHANEL NO5 TAKIM bileşeni eklendi','CREATE','2025-12-22 07:47:37'),(795,'2025-12-22 10:48:49','Admin User','chanel no 5 ürün ağacından CHANEL NO5 KUTU bileşeni silindi','DELETE','2025-12-22 07:48:49'),(796,'2025-12-22 10:48:53','Admin User','chanel no 5 ürün ağacından CHANEL NO5 TAKIM bileşeni silindi','DELETE','2025-12-22 07:48:53'),(797,'2025-12-22 11:43:01','Admin User','MEHMET KIRMIZIGÜL tedarikçisine ait DİOR SAVAGE TAKIM malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-22 08:43:01'),(798,'2025-12-22 11:43:18','Admin User','RAMAZAN BOZTEPE tedarikçisine ait DİOR SAVAGE DIŞ KUTU malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-22 08:43:18'),(799,'2025-12-22 11:43:32','Admin User','RAMAZAN BOZTEPE tedarikçisine ait DİOR SAVAGE DIŞ KUTU malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-22 08:43:32'),(800,'2025-12-22 11:43:38','Admin User','SARI ETİKET tedarikçisine ait DİOR SAVAGE ETİKET malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-22 08:43:38'),(801,'2025-12-22 11:43:42','Admin User','ŞENER BULUŞ tedarikçisine ait DİOR SAVAGE KUTU malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-22 08:43:42'),(802,'2025-12-22 11:43:47','Admin User','GÖKHAN  tedarikçisine ait DİOR SAVAGE JELATİN malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-22 08:43:47'),(803,'2025-12-22 11:46:42','Admin User','GÖKHAN  tedarikçisine CHANEL NO5 JELATİN malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 08:46:42'),(804,'2025-12-22 11:48:24','Admin User','GÖKHAN  tedarikçisine DİOR SAVAGE JELATİN malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 08:48:24'),(805,'2025-12-22 11:49:16','Admin User','GÖKHAN  tedarikçisine KİLİAN , ANGELS SHARE jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 08:49:16'),(806,'2025-12-22 12:06:11','Admin User','GÖKHAN  tedarikçisine SAVAGE ELİXER JELATİN malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 09:06:11'),(807,'2025-12-22 12:07:29','Admin User','GÖKHAN  tedarikçisine ysl y jilatin malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 09:07:29'),(808,'2025-12-22 12:10:39','Admin User','MEHMET KIRMIZIGÜL tedarikçisine CHANEL NO5 TAKIM malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 09:10:39'),(809,'2025-12-22 12:10:47','Admin User','MEHMET KIRMIZIGÜL tedarikçisine CHANEL NO5 TAKIM malzemesi için çerçeve sözleşme güncellendi','UPDATE','2025-12-22 09:10:47'),(810,'2025-12-22 12:16:15','Admin User','MEHMET KIRMIZIGÜL tedarikçisine DİOR SAVAGE TAKIM malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 09:16:15'),(811,'2025-12-22 13:51:55','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-22 10:51:55'),(812,'2025-12-22 13:54:55','Admin User','MEHMET KIRMIZIGÜL tedarikçisine KİLİAN , ANGELS SHARE takım malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 10:54:55'),(813,'2025-12-22 13:55:38','Admin User','MEHMET KIRMIZIGÜL tedarikçisine SAVAGE ELİXER TAKIM malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 10:55:38'),(814,'2025-12-22 13:56:27','Admin User','MEHMET KIRMIZIGÜL tedarikçisine ysl y takım malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 10:56:27'),(815,'2025-12-22 13:58:06','Admin User','SARI ETİKET tedarikçisine CHANEL NO5 TİKET malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 10:58:06'),(816,'2025-12-22 13:59:36','Admin User','SARI ETİKET tedarikçisine DİOR SAVAGE ETİKET malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 10:59:36'),(817,'2025-12-22 14:00:32','Admin User','SARI ETİKET tedarikçisine KİLİAN , ANGELS SHARE etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 11:00:32'),(818,'2025-12-22 14:01:38','Admin User','SARI ETİKET tedarikçisine SAVAGE ELİXER ETİKET malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 11:01:38'),(819,'2025-12-22 14:02:24','Admin User','SARI ETİKET tedarikçisine ysl y etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 11:02:24'),(820,'2025-12-22 14:48:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-22 11:48:44'),(821,'2025-12-22 16:43:22','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-22 13:43:22'),(822,'2025-12-22 16:45:51','Admin User','DİOR , SAVAGE ürün ağacındaki DİOR SAVAGE KUTU bileşeni DİOR SAVAGE KUTU olarak güncellendi','UPDATE','2025-12-22 13:45:51'),(823,'2025-12-22 16:45:59','Admin User','DİOR , SAVAGE ürün ağacındaki DİOR SAVAGE TAKIM bileşeni DİOR SAVAGE TAKIM olarak güncellendi','UPDATE','2025-12-22 13:45:59'),(824,'2025-12-22 16:47:06','Admin User','Ali Can müşterisine ait 2 nolu siparişin yeni durumu: Beklemede','UPDATE','2025-12-22 13:47:06'),(825,'2025-12-22 16:47:13','Admin User','Ali Can müşterisine ait 2 nolu siparişin yeni durumu: İptal Edildi','UPDATE','2025-12-22 13:47:13'),(826,'2025-12-22 16:55:12','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-22 13:55:12'),(827,'2025-12-22 17:37:49','Admin User','MEHMET KIRMIZIGÜL tedarikçisine ysl y takım malzemesi için çerçeve sözleşme güncellendi','UPDATE','2025-12-22 14:37:49'),(828,'2025-12-22 17:51:34','Admin User','dior savage ürün ağacındaki DİOR SAVAGE KUTU bileşeni DİOR SAVAGE KUTU olarak güncellendi','UPDATE','2025-12-22 14:51:34'),(829,'2025-12-22 17:51:50','Admin User','dior savage ürün ağacındaki DİOR SAVAGE ETİKET bileşeni DİOR SAVAGE ETİKET olarak güncellendi','UPDATE','2025-12-22 14:51:50'),(830,'2025-12-22 17:52:14','Admin User','dior savage ürün ağacındaki DİOR SAVAGE ETİKET bileşeni DİOR SAVAGE ETİKET olarak güncellendi','UPDATE','2025-12-22 14:52:14'),(831,'2025-12-22 17:53:47','Admin User','dior savage ürün ağacındaki DİOR SAVAGE TAKIM bileşeni DİOR SAVAGE TAKIM olarak güncellendi','UPDATE','2025-12-22 14:53:47'),(832,'2025-12-22 17:53:56','Admin User','savage elixer ürün ağacındaki SAVAGE ELİXER TAKIM bileşeni SAVAGE ELİXER TAKIM olarak güncellendi','UPDATE','2025-12-22 14:53:56'),(833,'2025-12-22 17:54:03','Admin User','savage elixer ürün ağacındaki SAVAGE ELİXER KUTU bileşeni SAVAGE ELİXER KUTU olarak güncellendi','UPDATE','2025-12-22 14:54:03'),(834,'2025-12-22 17:54:11','Admin User','savage elixer ürün ağacındaki ysl y etiket bileşeni ysl y etiket olarak güncellendi','UPDATE','2025-12-22 14:54:11'),(835,'2025-12-22 17:54:19','Admin User','ysl y ürün ağacındaki ysl y kutu bileşeni ysl y kutu olarak güncellendi','UPDATE','2025-12-22 14:54:19'),(836,'2025-12-22 17:54:29','Admin User','ysl y ürün ağacındaki ysl y etiket bileşeni ysl y etiket olarak güncellendi','UPDATE','2025-12-22 14:54:29'),(837,'2025-12-22 17:54:36','Admin User','ysl y ürün ağacındaki ysl y takım bileşeni ysl y takım olarak güncellendi','UPDATE','2025-12-22 14:54:36'),(838,'2025-12-22 18:00:03','Admin User','ALKOL deposuna 1 rafı eklendi','CREATE','2025-12-22 15:00:03'),(839,'2025-12-22 18:01:35','Admin User','DİOR SAVAGE ALKOL malzemesi sisteme eklendi','CREATE','2025-12-22 15:01:35'),(840,'2025-12-22 18:03:32','Admin User','DİOR , SAVAGE ürün ağacına DİOR SAVAGE ALKOL bileşeni eklendi','CREATE','2025-12-22 15:03:32'),(841,'2025-12-22 18:03:52','Admin User','DİOR , SAVAGE ürün ağacından DİOR SAVAGE KUTU bileşeni silindi','DELETE','2025-12-22 15:03:52'),(842,'2025-12-22 18:10:09','Admin User','Yeni malzeme türü eklendi: Ham Esans (ham_esans)','CREATE','2025-12-22 15:10:09'),(843,'2025-12-22 18:11:11','Admin User','DİOR SAVAGE HAM ESANS malzemesi sisteme eklendi','CREATE','2025-12-22 15:11:11'),(844,'2025-12-22 18:12:22','Admin User','DİOR , SAVAGE ürün ağacına DİOR SAVAGE HAM ESANS bileşeni eklendi','CREATE','2025-12-22 15:12:22'),(845,'2025-12-22 18:13:41','Admin User','LUZKİM tedarikçisine DİOR SAVAGE HAM ESANS malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 15:13:41'),(846,'2025-12-22 18:20:20','Admin User','dior savage ürün ağacına DİOR , SAVAGE bileşeni eklendi','CREATE','2025-12-22 15:20:20'),(847,'2025-12-22 18:22:55','Admin User','dior savage ürünü için montaj iş emri oluşturuldu','CREATE','2025-12-22 15:22:55'),(848,'2025-12-22 18:29:15','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-22 15:29:15'),(849,'2025-12-22 18:35:46','Admin User','dior savage için 15 adet stok hareketi eklendi','CREATE','2025-12-22 15:35:46'),(850,'2025-12-22 18:37:53','Admin User','dior savage için 4 adet stok hareketi ve ilgili gider kaydı eklendi','CREATE','2025-12-22 15:37:53'),(851,'2025-12-22 19:07:28','Admin User','kutu deposundaki raf 1 rafı KUTU deposuna ve raf 1 olarak güncellendi','UPDATE','2025-12-22 16:07:28'),(852,'2025-12-22 19:07:45','Admin User','dış kutu deposundaki 1 rafı DIŞ KUTU deposuna ve 1 olarak güncellendi','UPDATE','2025-12-22 16:07:45'),(853,'2025-12-22 19:07:59','Admin User','jelatin deposundaki 1 rafı JELATİN deposuna ve 1 olarak güncellendi','UPDATE','2025-12-22 16:07:59'),(854,'2025-12-22 19:08:26','Admin User','Depo 4 deposundaki Giriş sağ  rafı DEPO 4 deposuna ve Giriş sağ  olarak güncellendi','UPDATE','2025-12-22 16:08:26'),(855,'2025-12-22 19:08:55','Admin User','HAM ESANS deposuna 1 rafı eklendi','CREATE','2025-12-22 16:08:55'),(856,'2025-12-22 19:10:02','Admin User','DEPO 4 deposundaki Giriş sağ  rafı DEPO E deposuna ve Giriş sağ  olarak güncellendi','UPDATE','2025-12-22 16:10:02'),(857,'2025-12-22 19:10:45','Admin User','KUTU deposuna 1 rafı eklendi','CREATE','2025-12-22 16:10:45'),(858,'2025-12-22 19:11:57','Admin User','KUTU deposundaki 1 rafı silindi','DELETE','2025-12-22 16:11:57'),(859,'2025-12-22 19:13:29','Admin User','ADEM TAKIM tedarikçisi sisteme eklendi','CREATE','2025-12-22 16:13:29'),(860,'2025-12-22 19:16:18','Admin User','MEHMET KIRMIZIGÜL tedarikçisine CHANEL NO5 TAKIM malzemesi için sipariş oluşturuldu','CREATE','2025-12-22 16:16:18'),(861,'2025-12-22 19:20:50','Admin User','LUZKİM tedarikçisine DİOR SAVAGE HAM ESANS malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-22 16:20:50'),(862,'2025-12-22 22:44:18','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-22 19:44:18'),(863,'2025-12-22 23:33:08','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-22 20:33:08'),(864,'2025-12-23 10:09:38','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-23 07:09:38'),(865,'2025-12-24 17:46:18','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-24 14:46:18'),(866,'2025-12-24 17:55:52','Admin User','savage elixer ürün ağacına CHANEL NO5 JELATİN bileşeni eklendi','CREATE','2025-12-24 14:55:52'),(867,'2025-12-24 18:34:21','Admin User','DİOR SAVAGE DIŞ KUTU malzemesi DİOR SAVAGE DIŞ KUTU olarak güncellendi','UPDATE','2025-12-24 15:34:21'),(868,'2025-12-24 18:34:29','Admin User','DİOR SAVAGE JELATİN malzemesi DİOR SAVAGE JELATİN olarak güncellendi','UPDATE','2025-12-24 15:34:29'),(869,'2025-12-24 18:34:36','Admin User','DİOR SAVAGE TAKIM malzemesi DİOR SAVAGE TAKIM olarak güncellendi','UPDATE','2025-12-24 15:34:36'),(870,'2025-12-24 18:34:46','Admin User','DİOR SAVAGE KUTU malzemesi DİOR SAVAGE KUTU olarak güncellendi','UPDATE','2025-12-24 15:34:46'),(871,'2025-12-24 18:43:27','Admin User','ysl y jilatin malzemesi ysl y jilatin olarak güncellendi','UPDATE','2025-12-24 15:43:27'),(872,'2025-12-24 18:43:36','Admin User','ysl y yazıcı malzemesi ysl y yazıcı olarak güncellendi','UPDATE','2025-12-24 15:43:36'),(873,'2025-12-24 18:43:44','Admin User','ysl y etiket malzemesi ysl y etiket olarak güncellendi','UPDATE','2025-12-24 15:43:44'),(874,'2025-12-24 18:43:58','Admin User','ysl y kutu malzemesi ysl y kutu olarak güncellendi','UPDATE','2025-12-24 15:43:58'),(875,'2025-12-24 18:44:09','Admin User','ysl y dış kutu malzemesi ysl y dış kutu olarak güncellendi','UPDATE','2025-12-24 15:44:09'),(876,'2025-12-24 18:48:22','Admin User','ysl y kutu malzemesi ysl y kutu olarak güncellendi','UPDATE','2025-12-24 15:48:22'),(877,'2025-12-24 18:53:07','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 15:53:07'),(878,'2025-12-24 19:04:36','Admin User','savage elixer ürünü savage elixer olarak güncellendi','UPDATE','2025-12-24 16:04:36'),(879,'2025-12-24 19:05:29','Admin User','savage elixer ürünü savage elixer olarak güncellendi','UPDATE','2025-12-24 16:05:29'),(880,'2025-12-24 19:06:15','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 16:06:15'),(881,'2025-12-24 19:09:06','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 16:09:06'),(882,'2025-12-24 19:11:02','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 16:11:02'),(883,'2025-12-24 19:11:23','Admin User','chanel no 5 ürünü chanel no 5 olarak güncellendi','UPDATE','2025-12-24 16:11:23'),(884,'2025-12-24 19:12:59','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 16:12:59'),(885,'2025-12-24 19:13:15','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 16:13:15'),(886,'2025-12-24 19:14:13','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 16:14:13'),(887,'2025-12-24 19:15:51','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 16:15:51'),(888,'2025-12-24 19:16:02','Admin User','dior savage ürünü dior savage olarak güncellendi','UPDATE','2025-12-24 16:16:02'),(889,'2025-12-24 19:17:37','Admin User','ysl y ürünü ysl y olarak güncellendi','UPDATE','2025-12-24 16:17:37'),(890,'2025-12-24 19:17:47','Admin User','bacarat ret ürünü bacarat ret olarak güncellendi','UPDATE','2025-12-24 16:17:47'),(891,'2025-12-24 19:18:01','Admin User','savage elixer ürünü savage elixer olarak güncellendi','UPDATE','2025-12-24 16:18:01'),(892,'2025-12-24 19:32:51','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 3)','CREATE','2025-12-24 16:32:51'),(893,'2025-12-24 19:32:57','Admin User','MEHMET FATİH GÜZEN müşterisine ait 3 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-24 16:32:57'),(894,'2025-12-24 19:33:02','Admin User','MEHMET FATİH GÜZEN müşterisine ait 3 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-24 16:33:02'),(895,'2025-12-24 19:34:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-24 16:34:45'),(896,'2025-12-27 04:14:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-27 01:14:44'),(897,'2025-12-28 02:11:24','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-27 23:11:24'),(898,'2025-12-28 02:34:21','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-27 23:34:21'),(899,'2025-12-28 02:40:15','Admin User','GÖKHAN  tedarikçisine PO-2025-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-27 23:40:15'),(900,'2025-12-28 02:40:17','Admin User','GÖKHAN  tedarikçisine PO-2025-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-27 23:40:17'),(901,'2025-12-28 02:40:39','Admin User','Satınalma siparişi #2 durumu \'onaylandi\' olarak güncellendi','UPDATE','2025-12-27 23:40:39'),(902,'2025-12-28 02:40:50','Admin User','Satınalma siparişi #2 durumu \'gonderildi\' olarak güncellendi','UPDATE','2025-12-27 23:40:50'),(903,'2025-12-28 02:41:07','Admin User','Satınalma siparişi #2 durumu \'iptal\' olarak güncellendi','UPDATE','2025-12-27 23:41:07'),(904,'2025-12-28 02:41:28','Admin User','PO-2025-00001 no\'lu GÖKHAN  siparişi silindi','DELETE','2025-12-27 23:41:28'),(905,'2025-12-28 02:42:27','Admin User','LUZKİM tedarikçisine PO-2025-00003 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-27 23:42:27'),(906,'2025-12-28 02:42:52','Admin User','Satınalma siparişi #3 güncellendi','UPDATE','2025-12-27 23:42:52'),(907,'2025-12-28 02:43:37','Admin User','MEHMET KIRMIZIGÜL tedarikçisine PO-2025-00004 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-27 23:43:37'),(908,'2025-12-28 02:48:07','Admin User','PO-2025-00003 no\'lu LUZKİM siparişi silindi','DELETE','2025-12-27 23:48:07'),(909,'2025-12-28 03:26:46','Admin User','Satınalma siparişi #4 durumu \'tamamlandi\' olarak güncellendi','UPDATE','2025-12-28 00:26:46'),(910,'2025-12-28 03:26:53','Admin User','Satınalma siparişi #4 durumu \'onaylandi\' olarak güncellendi','UPDATE','2025-12-28 00:26:53'),(911,'2025-12-28 03:27:12','Admin User','Satınalma siparişi #4 durumu \'kismen_teslim\' olarak güncellendi','UPDATE','2025-12-28 00:27:12'),(912,'2025-12-28 03:28:32','Admin User','SARI ETİKET tedarikçisine PO-2025-00005 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-28 00:28:32'),(913,'2025-12-28 03:30:06','Admin User','PO-2025-00002 no\'lu GÖKHAN  siparişi silindi','DELETE','2025-12-28 00:30:06'),(914,'2025-12-28 03:30:48','Admin User','Satınalma siparişi #5 durumu \'kismen_teslim\' olarak güncellendi','UPDATE','2025-12-28 00:30:48'),(915,'2025-12-28 03:32:35','Admin User','PO-2025-00005 no\'lu SARI ETİKET siparişi silindi','DELETE','2025-12-28 00:32:35'),(916,'2025-12-28 03:34:24','Admin User','Satınalma siparişi #4 güncellendi','UPDATE','2025-12-28 00:34:24'),(917,'2025-12-28 03:36:06','Admin User','Satınalma siparişi #4 güncellendi','UPDATE','2025-12-28 00:36:06'),(918,'2025-12-28 03:38:32','Admin User','Satınalma siparişi #4 güncellendi','UPDATE','2025-12-28 00:38:32'),(919,'2025-12-28 03:44:51','Admin User','Satınalma siparişi #4 güncellendi','UPDATE','2025-12-28 00:44:51'),(920,'2025-12-28 03:44:59','Admin User','Satınalma siparişi #4 durumu \'gonderildi\' olarak güncellendi','UPDATE','2025-12-28 00:44:59'),(921,'2025-12-28 04:00:00','Admin User','Satınalma siparişi #4 güncellendi','UPDATE','2025-12-28 01:00:00'),(922,'2025-12-28 04:02:20','Admin User','Satınalma siparişi #4 güncellendi','UPDATE','2025-12-28 01:02:20'),(923,'2025-12-28 04:27:00','Admin User','Satınalma siparişi #4 durumu \'kapatildi\' olarak güncellendi','UPDATE','2025-12-28 01:27:00'),(924,'2025-12-28 04:43:38','Admin User','PO-2025-00004 no\'lu MEHMET KIRMIZIGÜL siparişi silindi','DELETE','2025-12-28 01:43:38'),(925,'2025-12-28 04:44:18','Admin User','LUZKİM tedarikçisine PO-2025-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-28 01:44:18'),(926,'2025-12-28 04:45:23','Admin User','PO-2025-00001 no\'lu LUZKİM siparişi silindi','DELETE','2025-12-28 01:45:23'),(927,'2025-12-28 04:45:40','Admin User','LUZKİM tedarikçisine PO-2025-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-28 01:45:40'),(928,'2025-12-28 04:45:51','Admin User','Satınalma siparişi #7 durumu \'gonderildi\' olarak güncellendi','UPDATE','2025-12-28 01:45:51'),(929,'2025-12-28 04:46:04','Admin User','PO-2025-00001 no\'lu LUZKİM siparişi silindi','DELETE','2025-12-28 01:46:04'),(930,'2025-12-28 04:46:14','Admin User','LUZKİM tedarikçisine PO-2025-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-28 01:46:14'),(931,'2025-12-28 04:47:27','Admin User','PO-2025-00001 no\'lu LUZKİM siparişi silindi','DELETE','2025-12-28 01:47:27'),(932,'2025-12-28 04:47:52','Admin User','LUZKİM tedarikçisine PO-2025-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-28 01:47:52'),(933,'2025-12-28 04:49:10','Admin User','LUZKİM tedarikçisine PO-2025-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-28 01:49:10'),(934,'2025-12-28 04:49:16','Admin User','PO-2025-00001 no\'lu LUZKİM siparişi silindi','DELETE','2025-12-28 01:49:16'),(935,'2025-12-28 16:51:18','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-28 13:51:18'),(936,'2025-12-28 16:55:26','Admin User','Dolar ve Euro kuru ayarları güncellendi','UPDATE','2025-12-28 13:55:26'),(937,'2025-12-28 17:37:36','Admin User','PO-2025-00002 no\'lu LUZKİM siparişi silindi','DELETE','2025-12-28 14:37:36'),(938,'2025-12-28 17:38:02','Admin User','MEHMET KIRMIZIGÜL tedarikçisine PO-2025-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-28 14:38:02'),(939,'2025-12-28 17:41:00','Admin User','Satınalma siparişi #11 güncellendi','UPDATE','2025-12-28 14:41:00'),(940,'2025-12-28 18:32:48','Admin User','Deneme ürünü sisteme eklendi','CREATE','2025-12-28 15:32:48'),(941,'2025-12-28 18:32:59','Admin User','Deneme, Etiket malzemesi sistemden silindi','DELETE','2025-12-28 15:32:59'),(942,'2025-12-28 18:33:02','Admin User','Deneme, Dış Kutu malzemesi sistemden silindi','DELETE','2025-12-28 15:33:02'),(943,'2025-12-28 18:33:06','Admin User','Deneme, alkol malzemesi sistemden silindi','DELETE','2025-12-28 15:33:06'),(944,'2025-12-28 18:34:26','Admin User','Deneme2 ürünü sisteme eklendi','CREATE','2025-12-28 15:34:26'),(945,'2025-12-28 18:34:40','Admin User','Deneme2, Etiket malzemesi sistemden silindi','DELETE','2025-12-28 15:34:40'),(946,'2025-12-28 18:34:45','Admin User','Deneme2, Dış Kutu malzemesi sistemden silindi','DELETE','2025-12-28 15:34:45'),(947,'2025-12-28 18:40:28','Admin User','Apo ürünü sisteme eklendi','CREATE','2025-12-28 15:40:28'),(948,'2025-12-28 18:41:17','Admin User','Apo ürünü sistemden silindi','DELETE','2025-12-28 15:41:17'),(949,'2025-12-28 18:41:42','Admin User','Apo, Ham Esans malzemesi sistemden silindi','DELETE','2025-12-28 15:41:42'),(950,'2025-12-28 18:41:47','Admin User','Apo, Etiket malzemesi sistemden silindi','DELETE','2025-12-28 15:41:47'),(951,'2025-12-28 18:41:51','Admin User','Apo, Dış Kutu malzemesi sistemden silindi','DELETE','2025-12-28 15:41:51'),(952,'2025-12-28 19:13:03','Admin User','dior savage ürün ağacındaki DİOR SAVAGE KUTU bileşeni DİOR SAVAGE KUTU olarak güncellendi','UPDATE','2025-12-28 16:13:03'),(953,'2025-12-28 19:24:11','Admin User','PO-2025-00001 no\'lu MEHMET KIRMIZIGÜL siparişi silindi','DELETE','2025-12-28 16:24:11'),(954,'2025-12-28 19:37:32','Admin User','MEHMET KIRMIZIGÜL tedarikçisine PO-2025-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2025-12-28 16:37:32'),(955,'2025-12-28 19:48:25','Admin User','Satınalma siparişi #12 güncellendi','UPDATE','2025-12-28 16:48:25'),(956,'2025-12-28 19:48:46','Admin User','Satınalma siparişi #12 durumu \'gonderildi\' olarak güncellendi','UPDATE','2025-12-28 16:48:46'),(957,'2025-12-28 19:55:39','Admin User','SARI ETİKET tedarikçisine DİOR SAVAGE HAM ESANS malzemesi için çerçeve sözleşme güncellendi','UPDATE','2025-12-28 16:55:39'),(958,'2025-12-28 21:03:32','Admin User','MEHMET FATİH GÜZEN müşterisine ait 3 nolu siparişin ödeme durumu güncellendi: odendi','UPDATE','2025-12-28 18:03:32'),(959,'2025-12-28 21:03:52','Admin User','Sipariş Ödemesi kategorisindeki 100.00 TL tutarlı gelir silindi','DELETE','2025-12-28 18:03:52'),(960,'2025-12-28 21:08:30','Admin User','Ali Can müşterisine ait 2 nolu sipariş silindi','DELETE','2025-12-28 18:08:30'),(961,'2025-12-28 21:10:17','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 4)','CREATE','2025-12-28 18:10:17'),(962,'2025-12-28 21:10:23','Admin User','MEHMET FATİH GÜZEN müşterisine ait 4 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-28 18:10:23'),(963,'2025-12-28 21:10:30','Admin User','MEHMET FATİH GÜZEN müşterisine ait 4 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-28 18:10:30'),(964,'2025-12-28 21:13:36','Admin User','Sipariş Ödemesi kategorisinde 6100 TL tutarında gelir eklendi','CREATE','2025-12-28 18:13:36'),(965,'2025-12-28 21:22:42','Admin User','Sipariş Ödemesi kategorisindeki 6100.00 TL tutarlı gelir silindi','DELETE','2025-12-28 18:22:42'),(966,'2025-12-28 21:23:38','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 5)','CREATE','2025-12-28 18:23:38'),(967,'2025-12-28 21:23:44','Admin User','MEHMET FATİH GÜZEN müşterisine ait 5 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-28 18:23:44'),(968,'2025-12-28 21:23:50','Admin User','MEHMET FATİH GÜZEN müşterisine ait 5 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-28 18:23:50'),(969,'2025-12-28 21:24:07','Admin User','Sipariş Ödemesi kategorisinde 5000 TL tutarında gelir eklendi','CREATE','2025-12-28 18:24:07'),(970,'2025-12-28 21:27:42','Admin User','Sipariş Ödemesi kategorisindeki 5000.00 TL tutarlı gelir silindi','DELETE','2025-12-28 18:27:42'),(971,'2025-12-28 21:28:57','Admin User','Sipariş Ödemesi kategorisinde 5000 TL tutarında gelir eklendi','CREATE','2025-12-28 18:28:57'),(972,'2025-12-28 21:30:44','Admin User','MEHMET FATİH GÜZEN müşterisine ait 5 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-28 18:30:44'),(973,'2025-12-28 22:10:51','Admin User','Sipariş Ödemesi kategorisindeki 5000.00 TL tutarlı gelir silindi','DELETE','2025-12-28 19:10:51'),(974,'2025-12-28 22:43:48','Admin User','Sipariş Ödemesi kategorisinde 5000 TL tutarında gelir eklendi','CREATE','2025-12-28 19:43:48'),(975,'2025-12-28 22:44:43','Admin User','Sipariş Ödemesi kategorisindeki 5000.00 TL tutarlı gelir silindi','DELETE','2025-12-28 19:44:43'),(976,'2025-12-29 00:11:24','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 6)','CREATE','2025-12-28 21:11:24'),(977,'2025-12-29 00:12:32','Admin User','MEHMET FATİH GÜZEN müşterisine ait 6 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-28 21:12:32'),(978,'2025-12-29 00:12:40','Admin User','MEHMET FATİH GÜZEN müşterisine ait 6 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-28 21:12:40'),(979,'2025-12-29 00:29:44','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 7)','CREATE','2025-12-28 21:29:44'),(980,'2025-12-29 00:30:22','Admin User','MEHMET FATİH GÜZEN müşterisine ait 7 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-28 21:30:22'),(981,'2025-12-29 00:40:40','Admin User','Bilinmeyen müşteri müşterisine ait Bilinmeyen numara nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-28 21:40:40'),(982,'2025-12-29 00:41:16','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 8)','CREATE','2025-12-28 21:41:16'),(983,'2025-12-29 00:41:40','Admin User','MEHMET FATİH GÜZEN müşterisine ait 8 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-28 21:41:40'),(984,'2025-12-29 00:41:49','Admin User','MEHMET FATİH GÜZEN müşterisine ait 8 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-28 21:41:49'),(985,'2025-12-29 00:42:05','Admin User','Sipariş Ödemesi kategorisinde 17 USD tutarında gelir eklendi','CREATE','2025-12-28 21:42:05'),(986,'2025-12-29 00:42:29','Admin User','OSMAN GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 9)','CREATE','2025-12-28 21:42:29'),(987,'2025-12-29 00:42:37','Admin User','OSMAN GÜZEN müşterisine ait 9 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-28 21:42:37'),(988,'2025-12-29 00:42:45','Admin User','OSMAN GÜZEN müşterisine ait 9 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-28 21:42:45'),(989,'2025-12-29 00:43:25','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 10)','CREATE','2025-12-28 21:43:25'),(990,'2025-12-29 00:43:34','Admin User','MEHMET FATİH GÜZEN müşterisine ait 10 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-28 21:43:34'),(991,'2025-12-29 00:43:43','Admin User','MEHMET FATİH GÜZEN müşterisine ait 10 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-28 21:43:43'),(992,'2025-12-29 03:16:51','Admin User','bacarat ret ürünü bacarat ret olarak güncellendi','UPDATE','2025-12-29 00:16:51'),(993,'2025-12-29 03:17:00','Admin User','Deneme2 ürünü Deneme2 olarak güncellendi','UPDATE','2025-12-29 00:17:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lokasyonlar`
--

LOCK TABLES `lokasyonlar` WRITE;
/*!40000 ALTER TABLE `lokasyonlar` DISABLE KEYS */;
INSERT INTO `lokasyonlar` VALUES (7,'DEPO A','A 1'),(8,'DEPO B','B 1'),(9,'DEPO C','C 1'),(10,'DEPO ETİKET','E 1'),(11,'DEPO TAKIM','TAKIM'),(12,'DEPO E','Giriş sağ '),(13,'KUTU','raf 1'),(14,'JELATİN','1'),(15,'DIŞ KUTU','1'),(16,'yazıcı','1'),(17,'ALKOL','1'),(18,'HAM ESANS','1');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_maliyetleri`
--

LOCK TABLES `malzeme_maliyetleri` WRITE;
/*!40000 ALTER TABLE `malzeme_maliyetleri` DISABLE KEYS */;
INSERT INTO `malzeme_maliyetleri` VALUES (1,36,'Ambalaj Malzemesi 1',10.00,'2025-11-11 15:06:25');
/*!40000 ALTER TABLE `malzeme_maliyetleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_turleri`
--

LOCK TABLES `malzeme_turleri` WRITE;
/*!40000 ALTER TABLE `malzeme_turleri` DISABLE KEYS */;
INSERT INTO `malzeme_turleri` VALUES (3,'etiket','Etiket',3,'2025-12-17 22:16:27'),(20,'alkol','alkol',4,'2025-12-19 10:19:08'),(21,'takim','Takım',5,'2025-12-19 10:19:52'),(22,'jelatin','Jelatin',6,'2025-12-19 10:28:01'),(23,'dis_kutu','Dış Kutu',7,'2025-12-19 10:28:17'),(24,'yazici','Yazıcı',8,'2025-12-19 10:28:34'),(25,'koli','Koli',9,'2025-12-19 10:28:49'),(26,'koli_banti','Koli Bantı',10,'2025-12-19 10:29:00'),(27,'kutu','Kutu',11,'2025-12-19 10:29:12'),(28,'ham_esans','Ham Esans',12,'2025-12-22 15:10:09');
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
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzemeler`
--

LOCK TABLES `malzemeler` WRITE;
/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (71,'DİOR SAVAGE KUTU','kutu','',0.00,'adet',0.50,'USD',0,0,'DEPO TAKIM','TAKIM',0),(72,'DİOR SAVAGE ETİKET','etiket','',2000.00,'adet',5.00,'TL',0,0,'DEPO ETİKET','E 1',0),(76,'DİOR SAVAGE TAKIM','takim','',0.00,'adet',0.00,'USD',0,0,'DEPO TAKIM','TAKIM',500),(77,'DİOR SAVAGE JELATİN','jelatin','',0.00,'adet',0.00,'TRY',0,0,'DEPO ETİKET','E 1',0),(78,'DİOR SAVAGE DIŞ KUTU','dis_kutu','',0.00,'adet',0.00,'TL',0,0,'DEPO ETİKET','E 1',0),(80,'KİLİAN , ANGELS SHARE kutu','kutu','',0.00,'adet',10.00,'TRY',0,7,'kutu','raf 1',0),(81,'KİLİAN , ANGELS SHARE etiket','etiket','',0.00,'adet',1.00,'TRY',0,7,'DEPO ETİKET','E 1',0),(82,'KİLİAN , ANGELS SHARE takım','takim','',1000.00,'adet',2.80,'USD',0,7,'DEPO TAKIM','TAKIM',0),(83,'KİLİAN , ANGELS SHARE jelatin','jelatin','',0.00,'adet',0.00,'TRY',0,0,'jelatin','1',0),(84,'KİLİAN , ANGELS SHARE dış kutu','dis_kutu','',0.00,'adet',0.00,'TRY',0,0,'dış kutu','1',0),(85,'ysl y dış kutu','kutu','',200.00,'adet',0.00,'TRY',0,0,'JELATİN','1',0),(86,'ysl y kutu','kutu','',300.00,'adet',0.00,'TRY',0,0,'JELATİN','1',0),(87,'ysl y etiket','etiket','',100.00,'adet',0.00,'TRY',0,0,'DEPO ETİKET','E 1',0),(88,'ysl y takım','takim','',2250.00,'adet',1.90,'USD',0,0,'DEPO TAKIM','TAKIM',0),(89,'ysl y yazıcı','yazici','',100.00,'adet',0.00,'TRY',0,0,'yazıcı','1',0),(90,'ysl y jilatin','jelatin','',100.00,'adet',0.00,'TRY',0,0,'KUTU','raf 1',0),(91,'KİLİAN , ANGELS SHARE YAZICI','yazici','',0.00,'adet',0.00,'TRY',0,0,'yazıcı','1',0),(94,'CHANEL NO5 TİKET','etiket','',1000.00,'adet',2.00,'TL',0,0,'DEPO ETİKET','E 1',0),(95,'CHANEL NO5 TAKIM','takim','',950.00,'adet',2.20,'USD',0,0,'DEPO TAKIM','TAKIM',0),(96,'CHANEL NO5 JELATİN','jelatin','',0.00,'adet',0.00,'TRY',0,0,'jelatin','1',0),(97,'CHANEL NO5 DIŞ KUTU','dis_kutu','',0.00,'adet',0.00,'TRY',0,0,'dış kutu','1',0),(98,'CHANEL NO5 YAZICI','yazici','',0.00,'adet',0.00,'TRY',0,0,'yazıcı','1',0),(99,'CHANEL NO5 KUTU','kutu','',0.00,'adet',0.00,'TRY',0,0,'kutu','raf 1',0),(100,'DİOR SAVAGE YAZICI','yazici','',0.00,'adet',0.00,'TRY',0,0,'yazıcı','1',0),(101,'SAVAGE ELİXER ETİKET','etiket','',0.00,'adet',0.00,'TRY',0,0,'DEPO ETİKET','E 1',0),(102,'SAVAGE ELİXER TAKIM','takim','',1000.00,'adet',2.70,'USD',0,0,'DEPO TAKIM','TAKIM',0),(103,'SAVAGE ELİXER JELATİN','jelatin','',0.00,'adet',0.00,'TRY',0,0,'jelatin','1',0),(104,'SAVAGE ELİXER DIŞ KUTU','dis_kutu','',0.00,'adet',0.00,'TRY',0,0,'dış kutu','1',0),(105,'SAVAGE ELİXER YAZICI','yazici','',0.00,'adet',0.00,'TRY',0,0,'yazıcı','1',0),(106,'SAVAGE ELİXER KUTU','kutu','',0.00,'adet',0.00,'TRY',0,0,'kutu','raf 1',0),(107,'DİOR SAVAGE ALKOL','alkol','',0.00,'lt',0.00,'TRY',0,7,'ALKOL','1',100),(108,'DİOR SAVAGE HAM ESANS','ham_esans','',0.00,'lt',0.00,'USD',0,7,'ALKOL','1',0);
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
INSERT INTO `montaj_is_emirleri` VALUES (1,'2025-12-19','Admin User','38','DİOR , SAVAGE',1000.00,'adet','2025-12-19','2025-12-19',' 4 adet örünümüz firedir şişeleri kırık','tamamlandi',996.00,4.00,5,'2025-12-22','2025-12-22'),(2,'2025-12-22','Admin User','44','dior savage',1000.00,'adet','2025-12-23','2025-12-23',' kalan 80 adet için etiketler eksik kaldı','tamamlandi',920.00,80.00,6,'2025-12-22','2025-12-22');
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
INSERT INTO `montaj_is_emri_malzeme_listesi` VALUES (2,'ES002','Lavender Essence','esans',495.00,'adet'),(2,'26','White Label','etiket',22.00,'adet'),(2,'27','Plastic Cap','kapak',11.00,'adet'),(3,'ES010','Bergamot Essence','esans',50.00,'adet'),(3,'37','Ambalaj Malzemesi 2','etiket',10.00,'adet'),(1,'ES010','Bergamot Essence','esans',4300.00,'adet'),(1,'37','Ambalaj Malzemesi 2','etiket',860.00,'adet'),(1,'78','DİOR SAVAGE DIŞ KUTU','dis_kutu',1000000.00,'adet'),(1,'71','DİOR SAVAGE KUTU','kutu',1000000.00,'adet'),(1,'76','DİOR SAVAGE TAKIM','takim',1000000.00,'adet'),(1,'77','DİOR SAVAGE JELATİN','jelatin',1000000.00,'adet'),(2,'71','DİOR SAVAGE KUTU','kutu',1000.00,'adet'),(2,'76','DİOR SAVAGE TAKIM','takim',1000.00,'adet'),(2,'72','DİOR SAVAGE ETİKET','etiket',3000.00,'adet'),(2,'001','DİOR , SAVAGE','esans',150.00,'adet');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrp_ayarlar`
--

LOCK TABLES `mrp_ayarlar` WRITE;
/*!40000 ALTER TABLE `mrp_ayarlar` DISABLE KEYS */;
INSERT INTO `mrp_ayarlar` VALUES (1,'mrp_gun_on_takvimi','30','MRP planlaması kaç gün öncesine kadar yapılsın'),(2,'mrp_guvenlik_stogu_katsayisi','1.1','Güvenlik stoğu için katsayı (1.1 = %10 fazla)'),(3,'mrp_otomatik_planlama','1','Otomatik planlama açık mı? (1=evet, 0=hayır)'),(4,'mrp_iptal_edilebilir_gun','7','Planlama tarihinden kaç gün önce iptal yapılabilir');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteriler`
--

LOCK TABLES `musteriler` WRITE;
/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
INSERT INTO `musteriler` VALUES (1,'MEHMET FATİH GÜZEN','','BEYAZIT FATİH','05415992163','','$2y$10$IgfLl84jMWrXjjIYbKfS6OkAtZ5tLmvwOfPZyEh.Hyf9YNPeNuvya','',1,'',1),(2,'İDRİS GÜZEN','','BEYZAIT FATİH','05539204551','','$2y$10$x74vfCE7fo0Z8bnd1SEW2OtrO1z6nsWi4stfqqzVVlWZuD3enwPSi','',1,'',1),(3,'OSMAN GÜZEN','','BAĞCILAR','05325083018','','$2y$10$rXvL18HqkvaO3KRVgSOyYu5rcYcySYOtFps.BhQUCv8lRjK9o9ayC','DİKKAT',1,'',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_izinleri`
--

LOCK TABLES `personel_izinleri` WRITE;
/*!40000 ALTER TABLE `personel_izinleri` DISABLE KEYS */;
INSERT INTO `personel_izinleri` VALUES (60,1,'page:view:mrp_planlama'),(59,160,'action:ayarlar:backup'),(58,160,'action:ayarlar:currency'),(57,160,'action:musteriler:edit'),(54,160,'page:view:ayarlar'),(56,160,'page:view:excele_aktar'),(52,160,'page:view:musteriler'),(51,160,'page:view:navigation'),(53,160,'page:view:personeller'),(55,160,'page:view:yedekleme'),(95,298,'page:view:ayarlar'),(97,298,'page:view:cerceve_sozlesmeler'),(100,298,'page:view:change_password'),(101,298,'page:view:doviz_kurlari'),(88,298,'page:view:esans_is_emirleri'),(84,298,'page:view:esanslar'),(103,298,'page:view:excele_aktar'),(96,298,'page:view:gider_yonetimi'),(93,298,'page:view:is_merkezleri'),(91,298,'page:view:lokasyonlar'),(85,298,'page:view:malzemeler'),(90,298,'page:view:manuel_stok_hareket'),(89,298,'page:view:montaj_is_emirleri'),(87,298,'page:view:musteri_siparisleri'),(80,298,'page:view:musteriler'),(79,298,'page:view:navigation'),(98,298,'page:view:personel_bordro'),(81,298,'page:view:personeller'),(94,298,'page:view:raporlar'),(92,298,'page:view:tanklar'),(82,298,'page:view:tedarikciler'),(99,298,'page:view:tekrarli_odemeler'),(86,298,'page:view:urun_agaclari'),(83,298,'page:view:urunler'),(102,298,'page:view:yedekleme');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_maas_odemeleri`
--

LOCK TABLES `personel_maas_odemeleri` WRITE;
/*!40000 ALTER TABLE `personel_maas_odemeleri` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=299 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personeller`
--

LOCK TABLES `personeller` WRITE;
/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Yönetim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$z56pgRUputjO7M5.Pp0W1eHOgVJ16GX3OKYtPi4VGenFweT8xUidK',NULL,0,0.00),(295,'mehmet mutlu','','1985-09-01','2025-01-01','müdür','genel takip','','05384191740','ESENLER','','$2y$10$WPoZmMw/nj29zwprL6gJWumfhWGXS7MI/mGnLocHGb9CYFvstQSby','',0,0.00),(296,'burhan ','','2001-12-01','2025-12-12','','','','','','','$2y$10$pviR4qYXDVBOxuXrOobPpuF0rhkXEUbnrI.eW3OVsDWAhoiaAHIT2','',1,25000.00),(297,'BÜNYAMİN ARGUN','','2002-12-01','2025-12-12','Mağaza depo sorumlusu ','','','+90 (546) 659 59 13','','','$2y$10$dUQpD/DPlOGnlcoOyoTQkuswPTdJWW9hw78H6CQWzFAIcLOFwAC2y','',1,25000.00),(298,'idris','','1998-01-01','2025-12-12','kardeş','müşteri','','05539204551','','','$2y$10$U5cEkuWzuKotwZoxGDl7xOFnGvDZksCYwGnvLGbWr9/lnEdgrm3ca','',0,0.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satinalma_siparis_kalemleri`
--

LOCK TABLES `satinalma_siparis_kalemleri` WRITE;
/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `satinalma_siparis_kalemleri` VALUES (28,12,102,'SAVAGE ELİXER TAKIM',100.00,'adet',2.70,'USD',270.00,0.00,'');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satinalma_siparisler`
--

LOCK TABLES `satinalma_siparisler` WRITE;
/*!40000 ALTER TABLE `satinalma_siparisler` DISABLE KEYS */;
INSERT INTO `satinalma_siparisler` VALUES (12,'PO-2025-00001',2,'MEHMET KIRMIZIGÜL','2025-12-28','2025-12-31','gonderildi','bekliyor',NULL,270.00,'USD','',1,'Admin User','2025-12-28 16:37:32','2025-12-28 16:48:46');
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
INSERT INTO `siparis_kalemleri` VALUES (1,36,'212 Men',10,'adet',200.00,2000.00,'TRY'),(1,37,'Issey Miyaki',15,'adet',500.00,7500.00,'TRY'),(3,47,'savage elixer',1,'adet',50.00,50.00,'USD'),(4,47,'savage elixer',122,'adet',50.00,6100.00,'USD'),(5,47,'savage elixer',100,'adet',50.00,5000.00,'USD'),(6,46,'bacarat ret',1,'adet',17.00,17.00,'USD'),(7,46,'bacarat ret',11,'adet',17.00,187.00,'USD'),(8,46,'bacarat ret',1,'adet',17.00,17.00,'USD'),(9,45,'chanel no 5',11,'adet',20.00,220.00,'TRY'),(10,46,'bacarat ret',1,'adet',17.00,17.00,'USD');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparisler`
--

LOCK TABLES `siparisler` WRITE;
/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
INSERT INTO `siparisler` VALUES (8,1,'MEHMET FATİH GÜZEN','2025-12-29 00:41:16','tamamlandi',1,'Personel: Admin User',1,'Admin User','2025-12-29 00:41:40','','odendi',NULL,17.00,'USD'),(9,3,'OSMAN GÜZEN','2025-12-29 00:42:29','tamamlandi',11,'Personel: Admin User',1,'Admin User','2025-12-29 00:42:37','','bekliyor',NULL,0.00,'TRY'),(10,1,'MEHMET FATİH GÜZEN','2025-12-29 00:43:25','tamamlandi',1,'Personel: Admin User',1,'Admin User','2025-12-29 00:43:34','','bekliyor',NULL,0.00,'USD');
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sirket_kasasi`
--

LOCK TABLES `sirket_kasasi` WRITE;
/*!40000 ALTER TABLE `sirket_kasasi` DISABLE KEYS */;
INSERT INTO `sirket_kasasi` VALUES (1,'TL',0.00,'2025-12-29 03:48:24'),(2,'USD',17.00,'2025-12-29 01:04:59'),(3,'EUR',0.00,'2025-12-29 00:54:20');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sistem_kullanicilari`
--

LOCK TABLES `sistem_kullanicilari` WRITE;
/*!40000 ALTER TABLE `sistem_kullanicilari` DISABLE KEYS */;
INSERT INTO `sistem_kullanicilari` VALUES (1,'personel',1,'admin','05551234567','$2y$10$J6IxP0xai3Ub7tbv7gqIQeccclWp6RGCePJ7deOtnVfkBK1e5JGFy','admin',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareket_kayitlari`
--

LOCK TABLES `stok_hareket_kayitlari` WRITE;
/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
INSERT INTO `stok_hareket_kayitlari` VALUES (1,'2025-12-09 04:23:03','malzeme','53','212 Man Etiketi','adet',10.00,'cikis','fire','Merkez Depo','A1','','',NULL,NULL,NULL,'Üretimde fire oldu.',283,'Yedek Admin','',NULL),(2,'2025-12-09 04:24:21','malzeme','56','212 Men Şişesi','adet',10.00,'cikis','sayim_eksigi','Merkez Depo','A1','','',NULL,NULL,NULL,'Sayımda eksik çıktı.',283,'Yedek Admin','',NULL),(3,'2025-12-09 04:24:57','urun','36','212 Men','adet',20.00,'giris','sayim_fazlasi','Merkez Depo','A1','','',NULL,NULL,NULL,'Merkez depoyu sayarken arkalarda bir kutu bulduk.',283,'Yedek Admin','',NULL),(5,'2025-12-09 04:26:07','urun','36','212 Men','adet',140.00,'giris','transfer','Merkez Depo','A2','','',NULL,NULL,NULL,'Stok transferi - Kaynak: Merkez Depo/A1 -> Hedef: Merkez Depo/A2',283,'Yedek Admin',NULL,NULL),(7,'2025-12-19 20:48:52','malzeme','78','DİOR SAVAGE DIŞ KUTU','adet',1000.00,'giris','mal_kabul','DEPO ETİKET','E 1',NULL,'20261',NULL,NULL,NULL,'1000 ADET DIŞ KUTU ALINDI [Sozlesme ID: 7]',1,'Admin User','RAMAZAN BOZTEPE',6),(8,'2025-12-19 20:52:02','malzeme','76','DİOR SAVAGE TAKIM','adet',850.00,'giris','mal_kabul','DEPO TAKIM','TAKIM',NULL,'20262',NULL,NULL,NULL,'KIRMIZI GÜLDEN FATİH BİZE 150 KAPAK VERECEK [Sozlesme ID: 3]',1,'Admin User','MEHMET KIRMIZIGÜL',2),(9,'2025-12-22 17:04:26','malzeme','94','CHANEL NO5 TİKET','adet',1000.00,'giris','mal_kabul','DEPO ETİKET','E 1',NULL,'',NULL,NULL,NULL,'ETİKETLER TESLİM ALINDI  [Sozlesme ID: 19]',1,'Admin User','SARI ETİKET',5),(10,'2025-12-22 17:12:50','malzeme','72','DİOR SAVAGE ETİKET','adet',5000.00,'giris','mal_kabul','DEPO ETİKET','E 1',NULL,'',NULL,NULL,NULL,'ÖRÜNLER TESLİM ALINDI MUTLU [Sozlesme ID: 20]',1,'Admin User','SARI ETİKET',5),(11,'2025-12-22 17:32:43','malzeme','95','CHANEL NO5 TAKIM','adet',950.00,'giris','mal_kabul','DEPO TAKIM','TAKIM',NULL,'1',NULL,NULL,NULL,'İŞLKJHGFDSA [Sozlesme ID: 14]',1,'Admin User','MEHMET KIRMIZIGÜL',2),(12,'2025-12-22 17:33:29','malzeme','82','KİLİAN , ANGELS SHARE takım','adet',1000.00,'giris','mal_kabul','DEPO TAKIM','TAKIM',NULL,'',NULL,NULL,NULL,'111 [Sozlesme ID: 16]',1,'Admin User','MEHMET KIRMIZIGÜL',2),(13,'2025-12-22 17:33:57','malzeme','88','ysl y takım','adet',1000.00,'giris','mal_kabul','DEPO TAKIM','TAKIM',NULL,'',NULL,NULL,NULL,', [Sozlesme ID: 18]',1,'Admin User','MEHMET KIRMIZIGÜL',2),(14,'2025-12-22 17:34:42','malzeme','102','SAVAGE ELİXER TAKIM','adet',1000.00,'giris','mal_kabul','DEPO TAKIM','TAKIM',NULL,'',NULL,NULL,NULL,', [Sozlesme ID: 17]',1,'Admin User','MEHMET KIRMIZIGÜL',2),(15,'2025-12-22 17:38:30','malzeme','88','ysl y takım','adet',1250.00,'giris','mal_kabul','DEPO TAKIM','TAKIM',NULL,'3',NULL,NULL,NULL,', [Sozlesme ID: 18]',1,'Admin User','MEHMET KIRMIZIGÜL',2),(16,'2025-12-22 18:23:56','esans','ES002','Lavender Essence','adet',495.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(17,'2025-12-22 18:23:56','etiket','26','White Label','adet',22.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(18,'2025-12-22 18:23:56','kapak','27','Plastic Cap','adet',11.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(19,'2025-12-22 18:23:56','kutu','71','DİOR SAVAGE KUTU','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(20,'2025-12-22 18:23:56','takim','76','DİOR SAVAGE TAKIM','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(21,'2025-12-22 18:23:56','etiket','72','DİOR SAVAGE ETİKET','adet',3000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(22,'2025-12-22 18:23:56','esans','001','DİOR , SAVAGE','adet',150.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(23,'2025-12-22 18:24:03','esans','ES010','Bergamot Essence','adet',4300.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(24,'2025-12-22 18:24:03','etiket','37','Ambalaj Malzemesi 2','adet',860.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(25,'2025-12-22 18:24:03','dis_kutu','78','DİOR SAVAGE DIŞ KUTU','adet',1000000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(26,'2025-12-22 18:24:03','kutu','71','DİOR SAVAGE KUTU','adet',1000000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(27,'2025-12-22 18:24:03','takim','76','DİOR SAVAGE TAKIM','adet',1000000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(28,'2025-12-22 18:24:03','jelatin','77','DİOR SAVAGE JELATİN','adet',1000000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(29,'2025-12-22 18:27:16','Ürün','44','dior savage','adet',920.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(30,'2025-12-22 18:28:03','Ürün','38','DİOR , SAVAGE','adet',996.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(31,'2025-12-22 18:35:46','urun','44','dior savage','adet',15.00,'giris','sayim_fazlasi','DEPO A','A 1','','',NULL,NULL,NULL,'sayımdan fazla',1,'Admin User','',NULL),(32,'2025-12-22 18:37:53','urun','44','dior savage','adet',4.00,'cikis','fire','DEPO A','A 1','','',NULL,NULL,NULL,'abubekir önelin öretim firesi',1,'Admin User','',NULL),(33,'2025-12-24 19:33:03','urun','47','savage elixer','adet',1.00,'cikis','cikis',NULL,NULL,NULL,'3',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(34,'2025-12-28 21:10:30','urun','47','savage elixer','adet',122.00,'cikis','cikis',NULL,NULL,NULL,'4',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(35,'2025-12-28 21:23:51','urun','47','savage elixer','adet',100.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(36,'2025-12-28 21:30:56','urun','47','savage elixer','adet',100.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(37,'2025-12-29 00:12:42','urun','46','bacarat ret','adet',1.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(38,'2025-12-29 00:41:50','urun','46','bacarat ret','adet',1.00,'cikis','cikis',NULL,NULL,NULL,'8',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(39,'2025-12-29 00:42:47','urun','45','chanel no 5','adet',11.00,'cikis','cikis',NULL,NULL,NULL,'9',NULL,3,'OSMAN GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(40,'2025-12-29 00:43:44','urun','46','bacarat ret','adet',1.00,'cikis','cikis',NULL,NULL,NULL,'10',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL);
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
INSERT INTO `stok_hareketleri_sozlesmeler` VALUES (9,19,94,1000.00,'2025-12-22 14:04:26',2.00,'TL','SARI ETİKET',5,'2025-12-12','2025-12-30'),(10,20,72,5000.00,'2025-12-22 14:12:50',5.00,'TL','SARI ETİKET',5,'2025-12-12','2025-12-30'),(11,14,95,950.00,'2025-12-22 14:32:43',2.20,'USD','MEHMET KIRMIZIGÜL',2,'2025-12-12','2025-12-30'),(12,16,82,1000.00,'2025-12-22 14:33:29',2.80,'USD','MEHMET KIRMIZIGÜL',2,'2025-12-12','2025-12-30'),(13,18,88,1000.00,'2025-12-22 14:33:57',1.90,'USD','MEHMET KIRMIZIGÜL',2,'2025-12-12','2025-12-30'),(14,17,102,1000.00,'2025-12-22 14:34:42',2.70,'USD','MEHMET KIRMIZIGÜL',2,'2025-12-12','2025-12-30'),(15,18,88,1250.00,'2025-12-22 14:38:30',1.90,'USD','MEHMET KIRMIZIGÜL',2,'2025-12-12','2025-12-30');
/*!40000 ALTER TABLE `stok_hareketleri_sozlesmeler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tanklar`
--

LOCK TABLES `tanklar` WRITE;
/*!40000 ALTER TABLE `tanklar` DISABLE KEYS */;
INSERT INTO `tanklar` VALUES (4,'001','W 528','',250.00),(5,'002','W 520','',250.00),(6,'003','E 181','',250.00),(7,'004','E 100','',250.00),(8,'005','E 121','',250.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tedarikciler`
--

LOCK TABLES `tedarikciler` WRITE;
/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (2,'MEHMET KIRMIZIGÜL','','İKİ TELLİ','','','MEHMET ','TAKIM',''),(3,'ŞENER BULUŞ','','BAYRAMPAŞA','','','GÜNER','KUTU',''),(4,'LUZKİM','','BAĞCILAR','','','SAMET','ESANS\r\n',''),(5,'SARI ETİKET','','BAYRAMPAŞA','','','EMİNE','ETİKET',''),(6,'RAMAZAN BOZTEPE','','','','','RAMAZAN','DIŞ KUTU',''),(7,'ZÜLKÜF KIRMIZIGÜL','','','','','ZÜLKÜF','ALKOL',''),(8,'VİP YAZILIM','','ARNAVUT KÖY','','','AZİZ','YAZICI',''),(10,'GÖKHAN ','','BAYRAMPAŞA','','','GÖKHAN','JİLATİNCİ\r\n',''),(11,'ADEM TAKIM','','BAĞCILAR','','','MUAMED','','');
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
INSERT INTO `tekrarli_odeme_gecmisi` VALUES (2,2,'Ofis Kirası','Kira',30000.00,2025,12,'2025-12-09','Havale','',283,'Yedek Admin','2025-12-09 00:38:21',36);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tekrarli_odemeler`
--

LOCK TABLES `tekrarli_odemeler` WRITE;
/*!40000 ALTER TABLE `tekrarli_odemeler` DISABLE KEYS */;
INSERT INTO `tekrarli_odemeler` VALUES (2,'Ofis Kirası','Kira',30000.00,5,'Lila Gayrimenkul','',1,283,'Yedek Admin','2025-12-09 00:37:20'),(3,'Elektrik Faturamız','Elektrik',20000.00,15,'Sepaş','',1,283,'Yedek Admin','2025-12-09 00:38:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_agaci`
--

LOCK TABLES `urun_agaci` WRITE;
/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (14,44,'dior savage','kutu','71','DİOR SAVAGE KUTU',1.00,'urun'),(15,44,'dior savage','takim','76','DİOR SAVAGE TAKIM',1.00,'urun'),(17,44,'dior savage','etiket','72','DİOR SAVAGE ETİKET',3.00,'urun'),(21,47,'savage elixer','takim','102','SAVAGE ELİXER TAKIM',1.00,'urun'),(22,48,'ysl y','etiket','87','ysl y etiket',1.00,'urun'),(23,48,'ysl y','kutu','86','ysl y kutu',1.00,'urun'),(24,48,'ysl y','takim','88','ysl y takım',1.00,'urun'),(25,47,'savage elixer','etiket','87','ysl y etiket',1.00,'urun'),(26,47,'savage elixer','kutu','106','SAVAGE ELİXER KUTU',1.00,'urun'),(29,9,'DİOR , SAVAGE','malzeme','107','DİOR SAVAGE ALKOL',0.80,'esans'),(30,9,'DİOR , SAVAGE','malzeme','108','DİOR SAVAGE HAM ESANS',0.15,'esans'),(31,44,'dior savage','esans','001','DİOR , SAVAGE',0.15,'urun'),(32,47,'savage elixer','jelatin','96','CHANEL NO5 JELATİN',1.00,'urun');
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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_fotograflari`
--

LOCK TABLES `urun_fotograflari` WRITE;
/*!40000 ALTER TABLE `urun_fotograflari` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

LOCK TABLES `urunler` WRITE;
/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (44,'dior savage','',1181,'adet',0.00,'USD',10.00,'TRY',0,'DEPO A','A 1','hazir_alinan',NULL),(45,'chanel no 5','',239,'adet',20.00,'TRY',0.00,'TRY',0,'0','A 1','uretilen',NULL),(46,'bacarat ret','',247,'adet',17.00,'USD',0.00,'TRY',600,'DEPO B','B 1','uretilen',NULL),(47,'savage elixer','',-73,'adet',50.00,'USD',0.00,'TRY',0,'JELATİN','1','uretilen',NULL),(48,'ysl y','',250,'adet',20.00,'USD',0.00,'TRY',0,'yazıcı','1','uretilen',NULL),(49,'Deneme','Deneme',100,'adet',10.00,'TRY',0.00,'TRY',100,'ALKOL','1','uretilen',NULL),(50,'Deneme2','',10,'adet',100.00,'TRY',0.00,'TRY',800,'DEPO A','A 1','uretilen',NULL);
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

-- Dump completed on 2025-12-29  3:48:44

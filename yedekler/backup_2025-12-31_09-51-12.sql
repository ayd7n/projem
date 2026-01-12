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
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

LOCK TABLES `ayarlar` WRITE;
/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.8500'),(2,'euro_kuru','50.5070'),(3,'son_otomatik_yedek_tarihi','2025-12-30 15:42:37'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cerceve_sozlesmeler`
--

LOCK TABLES `cerceve_sozlesmeler` WRITE;
/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (1,1,'kırmızıgül ',2,'CHANEL COCO TAKIM',2.20,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:42:26','',1),(2,3,'luzi',1,'CHANEL COCO HAM ESANS',120.00,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:43:18','',1),(3,4,'paket',5,'CHANEL COCO paket',1.00,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:43:58','',1),(4,2,'şener şimşek',3,'CHANEL COCO kutu',0.50,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:46:44','',1),(5,5,'jilatin ',4,'CHANEL COCO jelatin',1.00,'TL',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:47:47','',1),(7,8,'alkol',7,'alkol',1.50,'USD',9999,250,'2025-12-12','2025-12-30','Admin User','2025-12-25 12:11:14','',1),(8,3,'luzi',1,'CHANEL COCO HAM ESANS',110.00,'USD',9999,15,'2025-12-12','2026-12-30','Admin User','2025-12-26 14:37:52','',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esans_is_emirleri`
--

LOCK TABLES `esans_is_emirleri` WRITE;
/*!40000 ALTER TABLE `esans_is_emirleri` DISABLE KEYS */;
INSERT INTO `esans_is_emirleri` VALUES (1,'2025-12-25','Admin User','001','DİOR , SAVAGE','004','E 100',100.00,'lt','2025-12-25',30,'2026-01-24','2025-12-25','2025-12-25','karıştırılmasına dikkat günlük 20 dk karıştırılsın karıştırılmasına dikkat günlük 20 dk karıştırılsın\nkarıştırma burhan tarafından yapıldı','tamamlandi',100.00,0.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esanslar`
--

LOCK TABLES `esanslar` WRITE;
/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (1,'009','chanel coco','coco',250.00,'lt',30.00,'001','w 501'),(2,'001','DİOR , SAVAGE','',0.00,'lt',25.00,'',''),(3,'004','bacarat red','',0.00,'lt',25.00,'',''),(4,'005','VIKTORIA SECRET , BOM ŞHEL','',0.00,'lt',20.00,'',''),(5,'ES-251230-738','memoş','',0.00,'kg',1.00,NULL,NULL),(6,'ES-251230-696','aaasda','',0.00,'lt',1.00,NULL,NULL),(7,'ES-251230-448','XXX, Esans','',0.00,'lt',1.00,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gelir_yonetimi`
--

LOCK TABLES `gelir_yonetimi` WRITE;
/*!40000 ALTER TABLE `gelir_yonetimi` DISABLE KEYS */;
INSERT INTO `gelir_yonetimi` VALUES (7,'2025-12-30 00:00:00',1950.00,'USD','Sipariş Ödemesi','Sipariş No: #5 tahsilatı',1,'Admin User',5,'Nakit',1,'MEHMET FATİH GÜZEN'),(8,'2025-12-30 00:00:00',1740.00,'USD','Sipariş Ödemesi','Sipariş No: #6 tahsilatı',1,'Admin User',6,'Nakit',3,'OSMAN GÜZEN');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gider_yonetimi`
--

LOCK TABLES `gider_yonetimi` WRITE;
/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (1,'2025-12-26',70702.50,'Malzeme Gideri','CHANEL COCO HAM ESANS için 15 adet ara ödeme (1.650,00 USD @ 42,8500)',1,'Admin User',NULL,'Diğer','luzi'),(2,'2025-12-26',16068.75,'Malzeme Gideri','alkol için 250 adet ara ödeme (375,00 USD @ 42,8500)',1,'Admin User',NULL,'Diğer','alkol');
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
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tablosu`
--

LOCK TABLES `log_tablosu` WRITE;
/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2025-12-25 11:17:20','Admin User','log_tablosu tabloları temizlendi','DELETE','2025-12-25 08:17:20'),(2,'2025-12-25 11:21:25','Admin User','Malzeme türü silindi: HAM ESANS (_)','DELETE','2025-12-25 08:21:25'),(3,'2025-12-25 11:21:28','Admin User','Malzeme türü silindi: TAKIM ()','DELETE','2025-12-25 08:21:28'),(4,'2025-12-25 11:21:49','Admin User','Yeni malzeme türü eklendi: kutu (kutu)','CREATE','2025-12-25 08:21:49'),(5,'2025-12-25 11:22:01','Admin User','Yeni malzeme türü eklendi: etiket (etiket)','CREATE','2025-12-25 08:22:01'),(6,'2025-12-25 11:22:10','Admin User','Yeni malzeme türü eklendi: takım (takm)','CREATE','2025-12-25 08:22:10'),(7,'2025-12-25 11:22:38','Admin User','Yeni malzeme türü eklendi: ham esans (ham_esans)','CREATE','2025-12-25 08:22:38'),(8,'2025-12-25 11:22:52','Admin User','Yeni malzeme türü eklendi: alkol (alkol)','CREATE','2025-12-25 08:22:52'),(9,'2025-12-25 11:23:11','Admin User','Yeni malzeme türü eklendi: paket (paket)','CREATE','2025-12-25 08:23:11'),(10,'2025-12-25 11:23:23','Admin User','Yeni malzeme türü eklendi: jelatin (jelatin)','CREATE','2025-12-25 08:23:23'),(11,'2025-12-25 11:24:17','Admin User','CHANEL COCO TAKIM malzemesi CHANEL COCO TAKIM olarak güncellendi','UPDATE','2025-12-25 08:24:17'),(12,'2025-12-25 11:24:27','Admin User','CHANEL COCO HAM ESANS malzemesi CHANEL COCO HAM ESANS olarak güncellendi','UPDATE','2025-12-25 08:24:27'),(13,'2025-12-25 11:24:54','Admin User','CHANEL COCO TAKIM malzemesi CHANEL COCO TAKIM olarak güncellendi','UPDATE','2025-12-25 08:24:54'),(14,'2025-12-25 11:25:02','Admin User','CHANEL COCO HAM ESANS malzemesi CHANEL COCO HAM ESANS olarak güncellendi','UPDATE','2025-12-25 08:25:02'),(15,'2025-12-25 11:26:10','Admin User','CHANEL COCO kutu malzemesi sisteme eklendi','CREATE','2025-12-25 08:26:10'),(16,'2025-12-25 11:27:19','Admin User','CHANEL COCO jelatin malzemesi sisteme eklendi','CREATE','2025-12-25 08:27:19'),(17,'2025-12-25 11:27:57','Admin User','CHANEL COCO paket malzemesi sisteme eklendi','CREATE','2025-12-25 08:27:57'),(18,'2025-12-25 11:29:04','Admin User','CHANEL COCO  malzemesi sisteme eklendi','CREATE','2025-12-25 08:29:04'),(19,'2025-12-25 11:30:19','Admin User','chanel coco ürün ağacına CHANEL COCO HAM ESANS bileşeni eklendi','CREATE','2025-12-25 08:30:19'),(20,'2025-12-25 11:30:42','Admin User','chanel coco ürün ağacına CHANEL COCO jelatin bileşeni eklendi','CREATE','2025-12-25 08:30:42'),(21,'2025-12-25 11:30:55','Admin User','chanel coco ürün ağacına CHANEL COCO kutu bileşeni eklendi','CREATE','2025-12-25 08:30:55'),(22,'2025-12-25 11:31:42','Admin User','chanel coco ürün ağacına CHANEL COCO TAKIM bileşeni eklendi','CREATE','2025-12-25 08:31:42'),(23,'2025-12-25 11:34:10','Admin User','w 501 adlı tank sisteme eklendi','CREATE','2025-12-25 08:34:10'),(24,'2025-12-25 11:35:17','Admin User','chanel coco esansı sisteme eklendi','CREATE','2025-12-25 08:35:17'),(25,'2025-12-25 11:37:06','Admin User','chanel coco ürün ağacına CHANEL COCO HAM ESANS bileşeni eklendi','CREATE','2025-12-25 08:37:06'),(26,'2025-12-25 11:38:53','Admin User','kırmızıgül  tedarikçisi sisteme eklendi','CREATE','2025-12-25 08:38:53'),(27,'2025-12-25 11:39:16','Admin User','kırmızıgül  tedarikçisi kırmızıgül  olarak güncellendi','UPDATE','2025-12-25 08:39:16'),(28,'2025-12-25 11:39:37','Admin User','şener şimşek tedarikçisi sisteme eklendi','CREATE','2025-12-25 08:39:37'),(29,'2025-12-25 11:40:02','Admin User','luzi tedarikçisi sisteme eklendi','CREATE','2025-12-25 08:40:02'),(30,'2025-12-25 11:40:21','Admin User','paket tedarikçisi sisteme eklendi','CREATE','2025-12-25 08:40:21'),(31,'2025-12-25 11:40:39','Admin User','jilatin  tedarikçisi sisteme eklendi','CREATE','2025-12-25 08:40:39'),(32,'2025-12-25 11:41:12','Admin User','alkol tedarikçisi sisteme eklendi','CREATE','2025-12-25 08:41:12'),(33,'2025-12-25 11:42:26','Admin User','kırmızıgül  tedarikçisine CHANEL COCO TAKIM malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-25 08:42:26'),(34,'2025-12-25 11:43:18','Admin User','luzi tedarikçisine CHANEL COCO HAM ESANS malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-25 08:43:18'),(35,'2025-12-25 11:43:58','Admin User','paket tedarikçisine CHANEL COCO paket malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-25 08:43:58'),(36,'2025-12-25 11:46:44','Admin User','şener şimşek tedarikçisine CHANEL COCO kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-25 08:46:44'),(37,'2025-12-25 11:47:47','Admin User','jilatin  tedarikçisine CHANEL COCO jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-25 08:47:47'),(38,'2025-12-25 11:48:46','Admin User','alkol tedarikçisine CHANEL COCO  malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-25 08:48:46'),(39,'2025-12-25 11:50:47','Admin User','CHANEL COCO  malzemesi CHANEL COCO  olarak güncellendi','UPDATE','2025-12-25 08:50:47'),(40,'2025-12-25 11:54:14','Admin User','CHANEL COCO  malzemesi CHANEL COCO alkol olarak güncellendi','UPDATE','2025-12-25 08:54:14'),(41,'2025-12-25 11:54:59','Admin User','CHANEL COCO alkol malzemesi CHANEL COCO alkol olarak güncellendi','UPDATE','2025-12-25 08:54:59'),(42,'2025-12-25 11:56:15','Admin User','alkol tedarikçisine CHANEL COCO alkol malzemesi için çerçeve sözleşme güncellendi','UPDATE','2025-12-25 08:56:15'),(43,'2025-12-25 11:57:46','Admin User','alkol tedarikçisine ait CHANEL COCO alkol malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-12-25 08:57:46'),(44,'2025-12-25 11:59:02','Admin User','alkol tedarikçisi osman  olarak güncellendi','UPDATE','2025-12-25 08:59:02'),(45,'2025-12-25 11:59:33','Admin User','kırmızıgül  tedarikçisi takım olarak güncellendi','UPDATE','2025-12-25 08:59:33'),(46,'2025-12-25 11:59:51','Admin User','luzi tedarikçisi luzi olarak güncellendi','UPDATE','2025-12-25 08:59:51'),(47,'2025-12-25 12:04:04','Admin User','osman  tedarikçisi sistemden silindi','DELETE','2025-12-25 09:04:04'),(48,'2025-12-25 12:04:53','Admin User','gökan  tedarikçisi sisteme eklendi','CREATE','2025-12-25 09:04:53'),(49,'2025-12-25 12:05:01','Admin User','gökan  tedarikçisi sistemden silindi','DELETE','2025-12-25 09:05:01'),(50,'2025-12-25 12:05:36','Admin User','alkol tedarikçisi sisteme eklendi','CREATE','2025-12-25 09:05:36'),(51,'2025-12-25 12:07:13','Admin User','şener şimşek tedarikçisi kutu olarak güncellendi','UPDATE','2025-12-25 09:07:13'),(52,'2025-12-25 12:09:44','Admin User','alkol malzemesi sisteme eklendi','CREATE','2025-12-25 09:09:44'),(53,'2025-12-25 12:11:14','Admin User','alkol tedarikçisine alkol malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-25 09:11:14'),(54,'2025-12-25 12:15:17','Admin User','CHANEL COCO alkol malzemesi sistemden silindi','DELETE','2025-12-25 09:15:17'),(55,'2025-12-25 12:18:12','Admin User','chanel coco ürünü için montaj iş emri oluşturuldu','CREATE','2025-12-25 09:18:12'),(56,'2025-12-25 12:21:32','Admin User','chanel coco ürünü için montaj iş emri oluşturuldu','CREATE','2025-12-25 09:21:32'),(57,'2025-12-25 12:27:27','Admin User','alkol tedarikçisi zülkür  olarak güncellendi','UPDATE','2025-12-25 09:27:27'),(58,'2025-12-25 12:27:57','Admin User','takım tedarikçisi mehmet kırmızıgül olarak güncellendi','UPDATE','2025-12-25 09:27:57'),(59,'2025-12-25 12:28:17','Admin User','paket tedarikçisi ramazan olarak güncellendi','UPDATE','2025-12-25 09:28:17'),(60,'2025-12-25 12:28:37','Admin User','jilatin  tedarikçisi gökan olarak güncellendi','UPDATE','2025-12-25 09:28:37'),(61,'2025-12-25 12:28:54','Admin User','kutu tedarikçisi şener olarak güncellendi','UPDATE','2025-12-25 09:28:54'),(62,'2025-12-25 12:30:14','Admin User','LUZKİM tedarikçisine ait CHANEL NO5 HAM ESANS malzemesi siparişi silindi','DELETE','2025-12-25 09:30:14'),(63,'2025-12-25 12:30:17','Admin User','LUZKİM tedarikçisine ait bacarat ret HAM ESANS malzemesi siparişi silindi','DELETE','2025-12-25 09:30:17'),(64,'2025-12-25 12:32:33','Admin User','kahve ürünü sisteme eklendi','CREATE','2025-12-25 09:32:33'),(65,'2025-12-25 12:33:49','Admin User','kahve kutu malzemesi sisteme eklendi','CREATE','2025-12-25 09:33:49'),(66,'2025-12-25 12:53:10','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-25 09:53:10'),(67,'2025-12-25 13:01:55','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-25 10:01:55'),(68,'2025-12-25 13:04:28','Admin User','luzi tedarikçisine CHANEL COCO HAM ESANS malzemesi için sipariş oluşturuldu','CREATE','2025-12-25 10:04:28'),(69,'2025-12-25 13:35:34','Admin User','OSMAN GÜZEN personeli sisteme eklendi','CREATE','2025-12-25 10:35:34'),(70,'2025-12-25 14:14:50','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-25 11:14:50'),(71,'2025-12-25 14:15:20','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-25 11:15:20'),(72,'2025-12-25 14:17:03','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-25 11:17:03'),(73,'2025-12-25 14:17:11','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-25 11:17:11'),(74,'2025-12-25 14:17:44','OSMAN GÜZEN','Müşteri giriş yaptı (E-posta/Telefon: 05325083018)','Giriş Yapıldı','2025-12-25 11:17:44'),(75,'2025-12-25 14:18:11','OSMAN GÜZEN','OSMAN GÜZEN müşterisi tarafından sipariş oluşturuldu (ID: 1)','CREATE','2025-12-25 11:18:11'),(76,'2025-12-25 14:18:52','Admin User','OSMAN GÜZEN müşterisine ait 1 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-25 11:18:52'),(77,'2025-12-25 14:19:53','Admin User','OSMAN GÜZEN müşterisine ait 1 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-25 11:19:53'),(78,'2025-12-25 14:21:14','OSMAN GÜZEN','Müşteri giriş yaptı (E-posta/Telefon: 05325083018)','Giriş Yapıldı','2025-12-25 11:21:14'),(79,'2025-12-25 14:22:01','OSMAN GÜZEN','OSMAN GÜZEN müşterisi tarafından sipariş oluşturuldu (ID: 2)','CREATE','2025-12-25 11:22:01'),(80,'2025-12-25 14:22:18','Admin User','OSMAN GÜZEN müşterisine ait 2 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-25 11:22:18'),(81,'2025-12-25 14:22:26','Admin User','OSMAN GÜZEN müşterisine ait 2 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-25 11:22:26'),(82,'2025-12-25 14:30:09','Admin User','DİOR SAVAGE ürünü sisteme eklendi','CREATE','2025-12-25 11:30:09'),(83,'2025-12-25 14:35:30','Admin User','armani intense ürününe fotoğraf eklendi','CREATE','2025-12-25 11:35:30'),(84,'2025-12-25 15:06:26','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-25 12:06:26'),(85,'2025-12-25 15:08:57','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-25 12:08:57'),(86,'2025-12-26 09:38:40','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-26 06:38:40'),(87,'2025-12-26 10:05:43','Admin User','OSMAN GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 3)','CREATE','2025-12-26 07:05:43'),(88,'2025-12-26 10:05:54','Admin User','OSMAN GÜZEN müşterisine ait 3 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-26 07:05:54'),(89,'2025-12-26 10:06:04','Admin User','OSMAN GÜZEN müşterisine ait 3 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-26 07:06:04'),(90,'2025-12-26 10:37:41','Admin User','DİOR , SAVAGE esansı sisteme eklendi','CREATE','2025-12-26 07:37:41'),(91,'2025-12-26 10:38:44','Admin User','bacarat red esansı sisteme eklendi','CREATE','2025-12-26 07:38:44'),(92,'2025-12-26 10:39:51','Admin User','VIKTORIA SECRET , BOM ŞHEL esansı sisteme eklendi','CREATE','2025-12-26 07:39:51'),(93,'2025-12-26 10:55:08','Admin User','İDRİS GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 4)','CREATE','2025-12-26 07:55:08'),(94,'2025-12-26 10:55:18','Admin User','İDRİS GÜZEN müşterisine ait 4 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-26 07:55:18'),(95,'2025-12-26 10:55:28','Admin User','İDRİS GÜZEN müşterisine ait 4 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-26 07:55:28'),(96,'2025-12-26 11:48:03','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-26 08:48:03'),(97,'2025-12-26 12:06:42','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-26 09:06:42'),(98,'2025-12-26 13:33:06','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-26 10:33:06'),(99,'2025-12-26 13:51:01','mehmet mutlu','Personel giriş yaptı (E-posta/Telefon: 05384191740)','Giriş Yapıldı','2025-12-26 10:51:01'),(100,'2025-12-26 13:51:41','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 5)','CREATE','2025-12-26 10:51:41'),(101,'2025-12-26 13:51:54','Admin User','MEHMET FATİH GÜZEN müşterisine ait 5 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-26 10:51:54'),(102,'2025-12-26 13:53:54','mehmet mutlu','Personel giriş yaptı (E-posta/Telefon: 05384191740)','Giriş Yapıldı','2025-12-26 10:53:54'),(103,'2025-12-26 13:55:31','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-26 10:55:31'),(104,'2025-12-26 13:55:38','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-26 10:55:38'),(105,'2025-12-26 13:55:59','mehmet mutlu','personel oturumu kapattı (ID: 295)','Çıkış Yapıldı','2025-12-26 10:55:59'),(106,'2025-12-26 13:56:09','mehmet mutlu','Personel giriş yaptı (E-posta/Telefon: 05384191740)','Giriş Yapıldı','2025-12-26 10:56:09'),(107,'2025-12-26 14:02:20','Admin User','OSMAN GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 6)','CREATE','2025-12-26 11:02:20'),(108,'2025-12-26 14:04:08','Admin User','Sipariş kalemi güncellendi: chanel coco ürünü chanel coco olarak değiştirildi (ID: 6)','UPDATE','2025-12-26 11:04:08'),(109,'2025-12-26 14:05:32','Admin User','OSMAN GÜZEN müşterisine ait 6 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-26 11:05:32'),(110,'2025-12-26 14:05:59','Admin User','OSMAN GÜZEN müşterisine ait 6 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-26 11:05:59'),(111,'2025-12-26 14:06:07','Admin User','MEHMET FATİH GÜZEN müşterisine ait 5 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2025-12-26 11:06:07'),(112,'2025-12-26 14:11:16','Admin User','w 501 adlı tank w 501 olarak güncellendi','UPDATE','2025-12-26 11:11:16'),(113,'2025-12-26 14:21:19','Admin User','chanel coco ürünü için montaj iş emri oluşturuldu','CREATE','2025-12-26 11:21:19'),(114,'2025-12-26 14:37:52','Admin User','luzi tedarikçisine CHANEL COCO HAM ESANS malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-26 11:37:52'),(115,'2025-12-26 15:37:16','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-26 12:37:16'),(116,'2025-12-26 15:39:15','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-26 12:39:15'),(117,'2025-12-27 11:49:34','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-27 08:49:34'),(118,'2025-12-27 13:13:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-27 10:13:04'),(119,'2025-12-27 13:14:29','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-27 10:14:29'),(120,'2025-12-27 17:09:37','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-27 14:09:37'),(121,'2025-12-29 04:30:09','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-29 01:30:09'),(122,'2025-12-29 18:07:15','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-29 15:07:15'),(123,'2025-12-30 15:42:46','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-30 12:42:46'),(124,'2025-12-30 15:59:56','Admin User','Sipariş Ödemesi kategorisinde 1950 USD tutarında gelir eklendi','CREATE','2025-12-30 12:59:56'),(125,'2025-12-30 16:00:45','Admin User','Sipariş Ödemesi kategorisindeki 17.00 TL tutarlı gelir silindi','DELETE','2025-12-30 13:00:45'),(126,'2025-12-30 16:04:27','Admin User','Sipariş Ödemesi kategorisinde 1740 USD tutarında gelir eklendi','CREATE','2025-12-30 13:04:27'),(127,'2025-12-30 16:57:25','Admin User','gökan tedarikçisi gökan olarak güncellendi','UPDATE','2025-12-30 13:57:25'),(128,'2025-12-30 17:17:15','Admin User','gökan tedarikçisi gökan olarak güncellendi','UPDATE','2025-12-30 14:17:15'),(129,'2025-12-30 17:19:59','Admin User','Deneme ürünü sisteme eklendi','CREATE','2025-12-30 14:19:59'),(130,'2025-12-30 17:27:17','Admin User','Barış ürünü sisteme eklendi','CREATE','2025-12-30 14:27:17'),(131,'2025-12-30 17:31:11','Admin User','vvv ürünü sisteme eklendi','CREATE','2025-12-30 14:31:11'),(132,'2025-12-30 17:33:45','Admin User','memoş ürünü sisteme eklendi','CREATE','2025-12-30 14:33:45'),(133,'2025-12-30 17:33:45','Admin User','Otomatik esans oluşturuldu: memoş (Tank: )','CREATE','2025-12-30 14:33:45'),(134,'2025-12-30 17:35:08','Admin User','aaasda ürünü sisteme eklendi','CREATE','2025-12-30 14:35:08'),(135,'2025-12-30 17:35:09','Admin User','Otomatik esans oluşturuldu: aaasda (Tank: )','CREATE','2025-12-30 14:35:09'),(136,'2025-12-30 17:36:21','Admin User','XXX ürünü sisteme eklendi','CREATE','2025-12-30 14:36:21'),(137,'2025-12-30 17:36:22','Admin User','Otomatik esans oluşturuldu: XXX, Esans (Tank: )','CREATE','2025-12-30 14:36:22');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_siparisler`
--

LOCK TABLES `malzeme_siparisler` WRITE;
/*!40000 ALTER TABLE `malzeme_siparisler` DISABLE KEYS */;
INSERT INTO `malzeme_siparisler` VALUES (19,1,'CHANEL COCO HAM ESANS',3,'luzi',25.00,'2025-12-25','2025-12-26','siparis_verildi','',1,'Admin User','2025-12-25 10:04:28','2025-12-25 10:04:28');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_turleri`
--

LOCK TABLES `malzeme_turleri` WRITE;
/*!40000 ALTER TABLE `malzeme_turleri` DISABLE KEYS */;
INSERT INTO `malzeme_turleri` VALUES (3,'kutu','kutu',1,'2025-12-25 08:21:49'),(4,'etiket','etiket',2,'2025-12-25 08:22:01'),(5,'takm','takım',3,'2025-12-25 08:22:10'),(6,'ham_esans','ham esans',4,'2025-12-25 08:22:38'),(7,'alkol','alkol',5,'2025-12-25 08:22:52'),(8,'paket','paket',6,'2025-12-25 08:23:11'),(9,'jelatin','jelatin',7,'2025-12-25 08:23:23');
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzemeler`
--

LOCK TABLES `malzemeler` WRITE;
/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (1,'CHANEL COCO HAM ESANS','ham_esans','',906.35,'kg',119.83,'USD',0,5,'KUTU','raf 1',0),(2,'CHANEL COCO TAKIM','takm','',9.00,'adet',2.20,'USD',0,5,'yazıcı','1',0),(3,'CHANEL COCO kutu','kutu','',9.00,'adet',0.50,'USD',0,7,'KUTU','raf 1',0),(4,'CHANEL COCO jelatin','jelatin','',992.09,'kg',1.00,'TL',0,5,'JELATİN','1',0),(5,'CHANEL COCO paket','paket','',1000.00,'adet',1.00,'USD',0,5,'DIŞ KUTU','1',0),(7,'alkol','alkol','',560.00,'lt',1.50,'USD',0,5,'ALKOL','1',0),(8,'kahve kutu','kutu','',0.00,'adet',0.00,'USD',0,5,'DEPO A','A 1',0),(9,'Deneme, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(10,'Deneme, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(11,'Barış, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(12,'Barış, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(13,'vvv, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(14,'vvv, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(15,'memoş, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(16,'memoş, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(17,'aaasda, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(18,'aaasda, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(19,'XXX, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(20,'XXX, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `montaj_is_emirleri`
--

LOCK TABLES `montaj_is_emirleri` WRITE;
/*!40000 ALTER TABLE `montaj_is_emirleri` DISABLE KEYS */;
INSERT INTO `montaj_is_emirleri` VALUES (1,'2025-12-25','Admin User','1','chanel coco',500.00,'adet','2025-12-25','2025-12-25',' kırık','tamamlandi',498.00,2.00,5,'2025-12-25','2025-12-25'),(2,'2025-12-25','Admin User','1','chanel coco',490.00,'adet','2025-12-25','2025-12-25',' ','tamamlandi',470.00,20.00,6,'2025-12-25','2025-12-26'),(3,'2025-12-26','Admin User','1','chanel coco',1.00,'adet','2025-12-26','2025-12-26',' ','tamamlandi',0.00,1.00,5,'2025-12-26','2025-12-26');
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
INSERT INTO `montaj_is_emri_malzeme_listesi` VALUES (1,'1','CHANEL COCO HAM ESANS','ham_esans',75.00,'adet'),(1,'4','CHANEL COCO jelatin','jelatin',5.00,'adet'),(1,'3','CHANEL COCO kutu','kutu',500.00,'adet'),(1,'2','CHANEL COCO TAKIM','takm',500.00,'adet'),(2,'1','CHANEL COCO HAM ESANS','ham_esans',73.50,'adet'),(2,'4','CHANEL COCO jelatin','jelatin',4.90,'adet'),(2,'3','CHANEL COCO kutu','kutu',490.00,'adet'),(2,'2','CHANEL COCO TAKIM','takm',490.00,'adet'),(3,'1','CHANEL COCO HAM ESANS','ham_esans',0.15,'adet'),(3,'4','CHANEL COCO jelatin','jelatin',0.01,'adet'),(3,'3','CHANEL COCO kutu','kutu',1.00,'adet'),(3,'2','CHANEL COCO TAKIM','takm',1.00,'adet');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_izinleri`
--

LOCK TABLES `personel_izinleri` WRITE;
/*!40000 ALTER TABLE `personel_izinleri` DISABLE KEYS */;
INSERT INTO `personel_izinleri` VALUES (18,295,'page:view:malzemeler'),(21,295,'page:view:manuel_stok_hareket'),(20,295,'page:view:montaj_is_emirleri'),(19,295,'page:view:musteri_siparisleri'),(15,295,'page:view:musteriler'),(16,295,'page:view:tedarikciler'),(17,295,'page:view:urunler'),(5,299,'page:view:esans_is_emirleri'),(6,299,'page:view:excele_aktar'),(4,299,'page:view:malzemeler'),(1,299,'page:view:musteriler'),(2,299,'page:view:tedarikciler'),(3,299,'page:view:urunler');
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
) ENGINE=InnoDB AUTO_INCREMENT=300 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personeller`
--

LOCK TABLES `personeller` WRITE;
/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Yönetim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$z56pgRUputjO7M5.Pp0W1eHOgVJ16GX3OKYtPi4VGenFweT8xUidK',NULL,0,0.00),(295,'mehmet mutlu','','1985-09-01','2025-01-01','müdür','genel takip','','05384191740','ESENLER','','$2y$10$WPoZmMw/nj29zwprL6gJWumfhWGXS7MI/mGnLocHGb9CYFvstQSby','',0,0.00),(296,'burhan ','','2001-12-01','2025-12-12','','','','','','','$2y$10$pviR4qYXDVBOxuXrOobPpuF0rhkXEUbnrI.eW3OVsDWAhoiaAHIT2','',1,25000.00),(297,'BÜNYAMİN ARGUN','','2002-12-01','2025-07-25','Mağaza depo sorumlusu ','','','+90 (546) 659 59 13','','','$2y$10$dUQpD/DPlOGnlcoOyoTQkuswPTdJWW9hw78H6CQWzFAIcLOFwAC2y','',1,25000.00),(298,'idris','','1998-01-01','2025-12-12','','müşteri','','05539204551','','','$2y$10$U5cEkuWzuKotwZoxGDl7xOFnGvDZksCYwGnvLGbWr9/lnEdgrm3ca','',0,0.00),(299,'OSMAN GÜZEN','','2001-10-01','2025-12-25','MAL TOPLAMA','','','','','','$2y$10$URsQGwAc39ZcfFMF9ekxB.wFpScNGarZzPG83Y/7565XOrLj5YdRy','',0,0.00);
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
INSERT INTO `siparis_kalemleri` VALUES (1,1,'chanel coco',12,'adet',10.00,120.00,'USD'),(2,1,'chanel coco',88,'adet',10.00,880.00,'USD'),(3,1,'chanel coco',142,'adet',10.00,1420.00,'USD'),(3,7,'DİOR SAVAGE',18,'adet',12.00,216.00,'USD'),(4,7,'DİOR SAVAGE',20,'adet',12.00,240.00,'USD'),(4,1,'chanel coco',14,'adet',10.00,140.00,'USD'),(5,1,'chanel coco',15,'adet',10.00,150.00,'USD'),(5,7,'DİOR SAVAGE',150,'adet',12.00,1800.00,'USD'),(6,1,'chanel coco',30,'0',10.00,300.00,'USD'),(6,7,'DİOR SAVAGE',120,'adet',12.00,1440.00,'USD');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparisler`
--

LOCK TABLES `siparisler` WRITE;
/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
INSERT INTO `siparisler` VALUES (1,3,'OSMAN GÜZEN','2025-12-25 14:18:11','tamamlandi',12,'OSMAN GÜZEN',1,'Admin User','2025-12-25 14:18:52','','bekliyor',NULL,0.00,'USD'),(2,3,'OSMAN GÜZEN','2025-12-25 14:22:00','tamamlandi',88,'OSMAN GÜZEN',1,'Admin User','2025-12-25 14:22:18','','bekliyor',NULL,0.00,'USD'),(3,3,'OSMAN GÜZEN','2025-12-26 10:05:43','tamamlandi',160,'Personel: Admin User',1,'Admin User','2025-12-26 10:05:54','habersiz sipariş','bekliyor',NULL,0.00,'USD'),(4,2,'İDRİS GÜZEN','2025-12-26 10:55:08','tamamlandi',34,'Personel: Admin User',1,'Admin User','2025-12-26 10:55:18','','bekliyor',NULL,0.00,'USD'),(5,1,'MEHMET FATİH GÜZEN','2025-12-26 13:51:41','tamamlandi',165,'Personel: Admin User',1,'Admin User','2025-12-26 13:51:54','','odendi',NULL,1950.00,'USD'),(6,3,'OSMAN GÜZEN','2025-12-26 14:02:20','tamamlandi',150,'Personel: Admin User',1,'Admin User','2025-12-26 14:05:32','','odendi',NULL,1740.00,'USD');
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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareket_kayitlari`
--

LOCK TABLES `stok_hareket_kayitlari` WRITE;
/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
INSERT INTO `stok_hareket_kayitlari` VALUES (1,'2025-12-25 11:49:32','malzeme','1','CHANEL COCO HAM ESANS','kg',1000.00,'giris','mal_kabul','HAM ESANS','1',NULL,'1',NULL,NULL,NULL,'... [Sozlesme ID: 2]',1,'Admin User','luzi',3),(2,'2025-12-25 11:51:41','malzeme','4','CHANEL COCO jelatin','kg',2.00,'giris','mal_kabul','JELATİN','1',NULL,'1',NULL,NULL,NULL,'.. [Sozlesme ID: 5]',1,'Admin User','jilatin ',5),(3,'2025-12-25 11:52:02','malzeme','3','CHANEL COCO kutu','adet',1000.00,'giris','mal_kabul','KUTU','raf 1',NULL,'1',NULL,NULL,NULL,'... [Sozlesme ID: 4]',1,'Admin User','şener şimşek',2),(4,'2025-12-25 11:52:26','malzeme','2','CHANEL COCO TAKIM','adet',1000.00,'giris','mal_kabul','DEPO TAKIM','TAKIM',NULL,'1',NULL,NULL,NULL,'möç [Sozlesme ID: 1]',1,'Admin User','kırmızıgül ',1),(5,'2025-12-25 12:11:44','malzeme','7','alkol','lt',560.00,'giris','mal_kabul','ALKOL','1',NULL,'',NULL,NULL,NULL,'.. [Sozlesme ID: 7]',1,'Admin User','alkol',8),(6,'2025-12-25 12:14:08','malzeme','5','CHANEL COCO paket','adet',1000.00,'giris','mal_kabul','DIŞ KUTU','1',NULL,'01',NULL,NULL,NULL,'... [Sozlesme ID: 3]',1,'Admin User','paket',4),(7,'2025-12-25 12:17:42','malzeme','4','CHANEL COCO jelatin','kg',1000.00,'giris','mal_kabul','JELATİN','1',NULL,'02',NULL,NULL,NULL,'nnghhgh [Sozlesme ID: 5]',1,'Admin User','jilatin ',5),(8,'2025-12-25 12:18:48','ham_esans','1','CHANEL COCO HAM ESANS','adet',75.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(9,'2025-12-25 12:18:48','jelatin','4','CHANEL COCO jelatin','adet',5.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(10,'2025-12-25 12:18:48','kutu','3','CHANEL COCO kutu','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(11,'2025-12-25 12:18:48','takm','2','CHANEL COCO TAKIM','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(12,'2025-12-25 12:19:21','Ürün','1','chanel coco','adet',498.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(13,'2025-12-25 14:19:53','urun','1','chanel coco','adet',12.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,3,'OSMAN GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(14,'2025-12-25 14:22:26','urun','1','chanel coco','adet',88.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,3,'OSMAN GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(15,'2025-12-25 14:41:58','ham_esans','1','CHANEL COCO HAM ESANS','adet',73.50,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(16,'2025-12-25 14:41:58','jelatin','4','CHANEL COCO jelatin','adet',4.90,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(17,'2025-12-25 14:41:58','kutu','3','CHANEL COCO kutu','adet',490.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(18,'2025-12-25 14:41:58','takm','2','CHANEL COCO TAKIM','adet',490.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(19,'2025-12-25 15:32:36','malzeme','1','CHANEL COCO HAM ESANS','kg',25.00,'giris','mal_kabul','HAM ESANS','1',NULL,'',NULL,NULL,NULL,'. [Sozlesme ID: 2]',1,'Admin User','luzi',3),(20,'2025-12-26 10:06:05','urun','1','chanel coco','adet',142.00,'cikis','cikis',NULL,NULL,NULL,'3',NULL,3,'OSMAN GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(21,'2025-12-26 10:06:05','urun','7','DİOR SAVAGE','adet',18.00,'cikis','cikis',NULL,NULL,NULL,'3',NULL,3,'OSMAN GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(22,'2025-12-26 10:55:28','urun','7','DİOR SAVAGE','adet',20.00,'cikis','cikis',NULL,NULL,NULL,'4',NULL,2,'İDRİS GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(23,'2025-12-26 10:55:28','urun','1','chanel coco','adet',14.00,'cikis','cikis',NULL,NULL,NULL,'4',NULL,2,'İDRİS GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(24,'2025-12-26 14:05:59','urun','1','chanel coco','0',30.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,3,'OSMAN GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(25,'2025-12-26 14:05:59','urun','7','DİOR SAVAGE','adet',120.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,3,'OSMAN GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(26,'2025-12-26 14:06:08','urun','1','chanel coco','adet',15.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(27,'2025-12-26 14:06:08','urun','7','DİOR SAVAGE','adet',150.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,1,'MEHMET FATİH GÜZEN','Müşteri siparişi',1,'Admin User',NULL,NULL),(28,'2025-12-26 14:13:40','malzeme','1','CHANEL COCO HAM ESANS','kg',15.00,'giris','mal_kabul','HAM ESANS','1',NULL,'',NULL,NULL,NULL,'öç. [Sozlesme ID: 2]',1,'Admin User','luzi',3),(29,'2025-12-26 14:22:06','ham_esans','1','CHANEL COCO HAM ESANS','adet',0.15,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(30,'2025-12-26 14:22:06','jelatin','4','CHANEL COCO jelatin','adet',0.01,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(31,'2025-12-26 14:22:06','kutu','3','CHANEL COCO kutu','adet',1.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(32,'2025-12-26 14:22:06','takm','2','CHANEL COCO TAKIM','adet',1.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(33,'2025-12-26 14:22:41','Ürün','1','chanel coco','adet',0.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(34,'2025-12-26 14:23:04','Ürün','1','chanel coco','adet',470.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(35,'2025-12-26 14:38:29','malzeme','1','CHANEL COCO HAM ESANS','kg',15.00,'giris','mal_kabul','HAM ESANS','1',NULL,'1',NULL,NULL,NULL,'mehmet mutlu [Sozlesme ID: 8]',1,'Admin User','luzi',3),(36,'2025-12-30 17:39:17','malzeme','1','CHANEL COCO HAM ESANS','kg',906.35,'cikis','transfer','HAM ESANS','1','','sa',NULL,NULL,NULL,'Stok transferisadas - Kaynak: HAM ESANS/1 -> Hedef: KUTU/raf 1',1,'Admin User',NULL,NULL),(37,'2025-12-30 17:39:17','malzeme','1','CHANEL COCO HAM ESANS','kg',906.35,'giris','transfer','KUTU','raf 1','','sa',NULL,NULL,NULL,'Stok transferisadas - Kaynak: HAM ESANS/1 -> Hedef: KUTU/raf 1',1,'Admin User',NULL,NULL),(38,'2025-12-30 17:44:01','malzeme','2','CHANEL COCO TAKIM','adet',9.00,'cikis','transfer','DEPO TAKIM','TAKIM','','dd',NULL,NULL,NULL,'Stok transferi - Kaynak: DEPO TAKIM/TAKIM -> Hedef: yazıcı/1',1,'Admin User',NULL,NULL),(39,'2025-12-30 17:44:01','malzeme','2','CHANEL COCO TAKIM','adet',9.00,'giris','transfer','yazıcı','1','','dd',NULL,NULL,NULL,'Stok transferi - Kaynak: DEPO TAKIM/TAKIM -> Hedef: yazıcı/1',1,'Admin User',NULL,NULL);
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
INSERT INTO `stok_hareketleri_sozlesmeler` VALUES (1,2,1,1000.00,'2025-12-25 08:49:32',120.00,'USD','luzi',3,'2025-12-12','2025-12-30'),(2,5,4,2.00,'2025-12-25 08:51:41',1.00,'TL','jilatin ',5,'2025-12-12','2025-12-30'),(3,4,3,1000.00,'2025-12-25 08:52:02',0.50,'USD','şener şimşek',2,'2025-12-12','2025-12-30'),(4,1,2,1000.00,'2025-12-25 08:52:26',2.20,'USD','kırmızıgül ',1,'2025-12-12','2025-12-30'),(5,7,7,560.00,'2025-12-25 09:11:44',1.50,'USD','alkol',8,'2025-12-12','2025-12-30'),(6,3,5,1000.00,'2025-12-25 09:14:08',1.00,'USD','paket',4,'2025-12-12','2025-12-30'),(7,5,4,1000.00,'2025-12-25 09:17:42',1.00,'TL','jilatin ',5,'2025-12-12','2025-12-30'),(19,2,1,25.00,'2025-12-25 12:32:36',120.00,'USD','luzi',3,'2025-12-12','2025-12-30'),(28,2,1,15.00,'2025-12-26 11:13:40',120.00,'USD','luzi',3,'2025-12-12','2025-12-30'),(35,8,1,15.00,'2025-12-26 11:38:29',110.00,'USD','luzi',3,'2025-12-12','2026-12-30');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tanklar`
--

LOCK TABLES `tanklar` WRITE;
/*!40000 ALTER TABLE `tanklar` DISABLE KEYS */;
INSERT INTO `tanklar` VALUES (1,'001','w 501','ağzınnı açma\r\n',250.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tedarikciler`
--

LOCK TABLES `tedarikciler` WRITE;
/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (1,'mehmet kırmızıgül',NULL,'','iki telli','','','takım','',''),(2,'şener',NULL,'','','','','kutu','',''),(3,'luzi',NULL,'','esans ','','','samet','',''),(4,'ramazan',NULL,'','','','','paket','',''),(5,'gökan','Kutu','','','','','jilatin','Kartoncu',''),(8,'zülkür ',NULL,'','','','','alkol','','');
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_agaci`
--

LOCK TABLES `urun_agaci` WRITE;
/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (1,1,'chanel coco','ham_esans','1','CHANEL COCO HAM ESANS',0.15,'urun'),(2,1,'chanel coco','jelatin','4','CHANEL COCO jelatin',0.01,'urun'),(3,1,'chanel coco','kutu','3','CHANEL COCO kutu',1.00,'urun'),(4,1,'chanel coco','takm','2','CHANEL COCO TAKIM',1.00,'urun'),(5,1,'chanel coco','malzeme','1','CHANEL COCO HAM ESANS',0.15,'esans'),(6,8,'Deneme','etiket','9','Deneme, etiket',1.00,'urun'),(7,8,'Deneme','ham esans','10','Deneme, ham esans',1.00,'urun'),(8,9,'Barış','etiket','11','Barış, etiket',1.00,'urun'),(9,9,'Barış','ham esans','12','Barış, ham esans',1.00,'urun'),(10,10,'vvv','etiket','13','vvv, etiket',1.00,'urun'),(11,10,'vvv','ham esans','14','vvv, ham esans',1.00,'urun'),(12,11,'memoş','etiket','15','memoş, etiket',1.00,'urun'),(13,11,'memoş','ham esans','16','memoş, ham esans',1.00,'urun'),(14,11,'memoş','esans','ES-251230-738','memoş',1.00,'urun'),(15,12,'aaasda','etiket','17','aaasda, etiket',1.00,'urun'),(16,12,'aaasda','ham esans','18','aaasda, ham esans',1.00,'urun'),(17,12,'aaasda','esans','ES-251230-696','aaasda',1.00,'urun'),(18,13,'XXX','etiket','19','XXX, etiket',1.00,'urun'),(19,13,'XXX','ham esans','20','XXX, ham esans',1.00,'urun'),(20,13,'XXX','esans','ES-251230-448','XXX, Esans',1.00,'urun');
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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_fotograflari`
--

LOCK TABLES `urun_fotograflari` WRITE;
/*!40000 ALTER TABLE `urun_fotograflari` DISABLE KEYS */;
INSERT INTO `urun_fotograflari` VALUES (33,5,'KÜRDİJAN.jpg','assets/urun_fotograflari/694d218248636_1766662530.jpg',1,0,'2025-12-25 11:35:30');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

LOCK TABLES `urunler` WRITE;
/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (1,'chanel coco','',667,'adet',10.00,'USD',0.00,'TRY',100,'DEPO A','A 1','uretilen',NULL),(2,'invektus','',0,'adet',12.00,'USD',0.00,'TRY',0,'DEPO B','B 1','uretilen',NULL),(3,'lavi bel','',0,'adet',15.00,'TRY',0.00,'TRY',80,'DEPO A','A 1','uretilen',NULL),(4,'armani you','',0,'adet',25.00,'USD',0.00,'TRY',100,'DEPO A','A 1','uretilen',NULL),(5,'armani intense','',0,'adet',0.00,'USD',0.00,'TRY',9,'DEPO C','C 1','uretilen',NULL),(6,'kahve','',0,'adet',5.00,'USD',0.00,'TRY',100,'DEPO A','A 1','uretilen',NULL),(7,'DİOR SAVAGE','',192,'adet',12.00,'USD',0.00,'TRY',10,'DEPO C','C 1','hazir_alinan',NULL),(8,'Deneme','',12,'adet',0.00,'TRY',0.00,'TRY',111,'ALKOL','1','uretilen',NULL),(9,'Barış','',0,'adet',0.00,'TRY',0.00,'TRY',0,'ALKOL','1','uretilen',NULL),(10,'vvv','',0,'adet',0.00,'TRY',0.00,'TRY',0,'ALKOL','1','uretilen',NULL),(11,'memoş','',0,'adet',0.00,'TRY',0.00,'TRY',0,'ALKOL','1','uretilen',NULL),(12,'aaasda','',0,'adet',0.00,'TRY',0.00,'TRY',0,'ALKOL','1','uretilen',NULL),(13,'XXX','',123,'adet',0.00,'TRY',0.00,'TRY',0,'DEPO A','A 1','uretilen',NULL);
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

-- Dump completed on 2025-12-31  9:51:15

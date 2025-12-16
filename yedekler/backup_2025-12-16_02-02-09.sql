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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

LOCK TABLES `ayarlar` WRITE;
/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.4270'),(2,'euro_kuru','49.0070'),(3,'son_otomatik_yedek_tarihi','2025-12-15 21:03:31'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cerceve_sozlesmeler`
--

LOCK TABLES `cerceve_sozlesmeler` WRITE;
/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (1,1,'Ahmet Kozmetik',57,'Saf Su',2.00,'TL',10000,1000,'2025-12-02','2025-12-21','Yedek Admin','2025-12-09 02:34:40','',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esanslar`
--

LOCK TABLES `esanslar` WRITE;
/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (5,'ES010','Gül Kokusu Esansı','',100.00,'lt',20.00,'Tank1','Birinci Tankımız');
/*!40000 ALTER TABLE `esanslar` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gider_yonetimi`
--

LOCK TABLES `gider_yonetimi` WRITE;
/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (27,'2025-12-09',100.00,'Malzeme Gideri','Saf Su için 50 adet ön ödeme',283,'Yedek Admin',NULL,'Diğer','Ahmet Kozmetik'),(29,'2025-12-08',200.00,'Diğer','Personele lahmacun ısmarladık.',283,'Yedek Admin','','Kredi Kartı','Hacıoğlu Lahmacun'),(34,'2025-12-09',10000.00,'Personel Avansı','Ahmet Yıldırım - 2025/12 dönemi avans ödemesi. ',283,'Yedek Admin',NULL,'Nakit','Ahmet Yıldırım'),(35,'2025-12-09',50000.00,'Personel Gideri','Ahmet Yıldırım - 2025/12 dönemi maaş ödemesi. ',283,'Yedek Admin',NULL,'Havale','Ahmet Yıldırım'),(36,'2025-12-09',30000.00,'Kira','Ofis Kirası - 2025/12 dönemi. ',283,'Yedek Admin',NULL,'Havale','Lila Gayrimenkul'),(37,'2025-12-09',12.50,'Fire Gideri','Fire kaydı - 212 Man Etiketi - 10 adet (53)',283,'Yedek Admin','Fire_Kaydi_1','Diğer','İç Gider'),(38,'2025-12-09',1900.00,'Malzeme Gideri','Saf Su için 950 adet ara ödeme',283,'Yedek Admin',NULL,'Diğer','Ahmet Kozmetik');
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
INSERT INTO `is_merkezleri` VALUES (1,'Montaj Masası 1','Kendi atölyemizdeki birinci montaj masası.'),(4,'Abdullah Usta','Fason olarak Abdullah Bey\'e iş yaptıracağımız zaman burada yapılır montaj.');
/*!40000 ALTER TABLE `is_merkezleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=489 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tablosu`
--

LOCK TABLES `log_tablosu` WRITE;
/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2025-11-26 09:44:47','test_kullanici','Bu bir test log kaydıdır.','CREATE','2025-11-26 06:44:47'),(2,'2025-11-26 09:45:40','test_kullanici','Test Ürün sisteme eklendi','CREATE','2025-11-26 06:45:40'),(3,'2025-11-26 09:45:40','test_kullanici','Test Ürün güncellendi','UPDATE','2025-11-26 06:45:40'),(4,'2025-11-26 09:45:40','test_kullanici','Test Ürün sistemden silindi','DELETE','2025-11-26 06:45:40'),(5,'2025-11-26 09:46:02','sistem','Melisa Rose Parfümü ürünü sisteme eklendi','CREATE','2025-11-26 06:46:02'),(6,'2025-11-26 09:46:02','sistem','Melisa Rose Parfümü ürünü Kır Çiçeği Parfümü olarak güncellendi','UPDATE','2025-11-26 06:46:02'),(7,'2025-11-26 09:46:02','sistem','Kır Çiçeği Parfümü ürünü sistemden silindi','DELETE','2025-11-26 06:46:02'),(8,'2025-11-26 09:46:02','sistem','Ahmet Yılmaz müşterisi sisteme eklendi','CREATE','2025-11-26 06:46:02'),(9,'2025-11-26 09:46:02','sistem','ABC Tedarik tedarikçisi sisteme eklendi','CREATE','2025-11-26 06:46:02'),(10,'2025-11-26 11:03:18','Admin User','Deneme Tankı adlı tank sisteme eklendi','CREATE','2025-11-26 08:03:18'),(11,'2025-11-26 11:06:21','Admin User','Güncellenmiş Tank adlı tank Güncellenmiş Tank olarak güncellendi','UPDATE','2025-11-26 08:06:21'),(12,'2025-11-26 11:06:35','Admin User','Bilinmeyen Tank adlı tank silindi','DELETE','2025-11-26 08:06:35'),(13,'2025-11-26 11:10:03','Admin User','Test Ürün ürün ağacına Test Malzeme bileşeni eklendi','CREATE','2025-11-26 08:10:03'),(14,'2025-11-26 11:10:21','Admin User','Test Ürün ürün ağacındaki Test Malzeme bileşeni Güncellenmiş Malzeme olarak güncellendi','UPDATE','2025-11-26 08:10:21'),(15,'2025-11-26 11:10:32','Admin User','Test Ürün ürün ağacından Güncellenmiş Malzeme bileşeni silindi','DELETE','2025-11-26 08:10:32'),(16,'2025-11-26 14:47:21','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:47:21'),(17,'2025-11-26 14:47:28','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:47:28'),(18,'2025-11-26 14:47:32','unknown','unknown oturumu kapattı (ID: unknown)','','2025-11-26 11:47:32'),(19,'2025-11-26 14:48:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:48:04'),(20,'2025-11-26 14:48:10','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 11:48:10'),(21,'2025-11-26 14:48:36','Ayse Kaya','Müşteri giriş yaptı (E-posta/Telefon: ayse.kaya@parfum.com)','','2025-11-26 11:48:36'),(22,'2025-11-26 14:56:59','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','','2025-11-26 11:56:59'),(23,'2025-11-26 14:58:03','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 3)','CREATE','2025-11-26 11:58:03'),(24,'2025-11-26 14:58:22','Ali Can','musteri oturumu kapattı (ID: 8)','','2025-11-26 11:58:22'),(25,'2025-11-26 16:20:13','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:20:13'),(26,'2025-11-26 16:20:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:20:14'),(27,'2025-11-26 16:21:21','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:21:21'),(28,'2025-11-26 16:21:22','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:21:22'),(29,'2025-11-26 16:45:37','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:45:37'),(30,'2025-11-26 16:45:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:45:39'),(31,'2025-11-26 16:47:07','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 13:47:07'),(32,'2025-11-26 16:47:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 13:47:09'),(33,'2025-11-26 17:05:50','test_kullanici','Bu bir test logudur','TEST','2025-11-26 14:05:50'),(34,'2025-11-26 21:12:54','Admin User','Telegram ayarları güncellendi','UPDATE','2025-11-26 18:12:54'),(35,'2025-11-26 21:15:28','Admin User','Telegram ayarları güncellendi','UPDATE','2025-11-26 18:15:28'),(36,'2025-11-26 21:15:37','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:15:37'),(37,'2025-11-26 21:15:43','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:15:43'),(38,'2025-11-26 21:16:18','Admin User','Tedarikci Isim10 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:18'),(39,'2025-11-26 21:16:29','Admin User','Tedarikci Isim100 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:29'),(40,'2025-11-26 21:16:41','Admin User','Tedarikci Isim16 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:41'),(41,'2025-11-26 21:16:58','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:16:58'),(42,'2025-11-26 21:17:05','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-26 18:17:05'),(43,'2025-11-26 21:17:43','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 4)','CREATE','2025-11-26 18:17:43'),(44,'2025-11-26 21:20:24','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 5)','CREATE','2025-11-26 18:20:24'),(45,'2025-11-26 21:20:49','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 6)','CREATE','2025-11-26 18:20:49'),(46,'2025-11-26 21:22:27','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 7)','CREATE','2025-11-26 18:22:27'),(47,'2025-11-26 21:23:37','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 8)','CREATE','2025-11-26 18:23:37'),(48,'2025-11-26 21:23:53','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-26 18:23:53'),(49,'2025-11-26 21:23:58','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:23:58'),(50,'2025-11-26 21:26:10','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:26:10'),(51,'2025-11-26 21:26:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:26:14'),(52,'2025-11-26 21:27:33','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:27:33'),(53,'2025-11-26 21:27:37','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:27:37'),(54,'2025-11-26 21:32:33','Admin User','Ali Can müşterisine ait 6 nolu sipariş iptal_edildi durumundan iptal_edildi durumuna güncellendi','UPDATE','2025-11-26 18:32:33'),(55,'2025-11-26 21:32:48','Admin User','Ali Can müşterisine ait 5 nolu sipariş onaylandi durumundan onaylandi durumuna güncellendi','UPDATE','2025-11-26 18:32:48'),(56,'2025-11-26 21:34:22','Admin User','Ali Can müşterisine ait 5 nolu siparişin durumu onaylandi oldu','UPDATE','2025-11-26 18:34:22'),(57,'2025-11-26 21:35:33','Admin User','Ali Can müşterisine ait 7 nolu sipariş silindi','DELETE','2025-11-26 18:35:33'),(58,'2025-11-26 21:35:42','Admin User','Ali Can müşterisine ait 8 nolu siparişin yeni durumu: Beklemede','UPDATE','2025-11-26 18:35:42'),(59,'2025-11-26 21:35:50','Admin User','Ali Can müşterisine ait 3 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-11-26 18:35:50'),(60,'2025-11-26 21:38:01','Admin User','Tedarikci Isim13 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:38:01'),(61,'2025-11-26 22:15:40','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 19:15:40'),(62,'2025-11-26 22:15:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 19:15:44'),(63,'2025-11-26 22:55:48','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-11-26 19:55:48'),(64,'2025-11-26 23:01:39','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için çerçeve sözleşme eklendi','CREATE','2025-11-26 20:01:39'),(65,'2025-11-26 23:22:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 20:22:36'),(66,'2025-11-27 08:16:34','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 05:16:34'),(67,'2025-11-27 08:16:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 05:16:39'),(68,'2025-11-27 09:45:06','SISTEM','Acil durum kullanıcısı ile en son yedekten geri yükleme yapıldı.','Kritik Eylem','2025-11-27 06:45:06'),(69,'2025-11-27 09:45:12','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 06:45:12'),(70,'2025-11-27 09:51:49','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 06:51:49'),(71,'2025-11-27 09:51:52','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 06:51:52'),(72,'2025-11-27 11:09:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 08:09:36'),(73,'2025-11-27 11:11:48','Admin User','10 evo ürünü için montaj iş emri oluşturuldu','CREATE','2025-11-27 08:11:48'),(74,'2025-11-27 11:21:00','Admin User','Perfume 2 ürünü için montaj iş emri oluşturuldu','CREATE','2025-11-27 08:21:00'),(75,'2025-11-27 11:25:02','Admin User','10 evo ürünü için montaj iş emri oluşturuldu','CREATE','2025-11-27 08:25:02'),(76,'2025-11-27 14:34:42','Admin User','Malzeme Gideri kategorisindeki 1200.00 TL tutarlı gider silindi','DELETE','2025-11-27 11:34:42'),(77,'2025-11-27 14:35:09','Admin User','Sarf Malzeme Gideri kategorisinde 56000 TL tutarında gider eklendi','CREATE','2025-11-27 11:35:09'),(78,'2025-11-27 15:07:08','Admin User','Sarf Malzeme Gideri kategorisindeki 56000.00 TL tutarlı gider güncellendi','UPDATE','2025-11-27 12:07:08'),(79,'2025-11-27 15:07:29','Admin User','Sarf Malzeme Gideri kategorisindeki 560.00 TL tutarlı gider güncellendi','UPDATE','2025-11-27 12:07:29'),(80,'2025-11-27 15:21:12','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 12:21:12'),(81,'2025-11-27 15:34:41','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 12:34:41'),(82,'2025-11-27 15:35:01','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 12:35:01'),(83,'2025-11-27 15:38:06','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 12:38:06'),(84,'2025-11-27 15:39:00','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 12:39:00'),(85,'2025-11-27 16:20:25','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 13:20:25'),(86,'2025-11-27 21:12:40','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 18:12:40'),(87,'2025-11-29 03:35:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 00:35:09'),(88,'2025-11-29 16:42:56','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 13:42:56'),(89,'2025-11-29 16:58:40','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 13:58:40'),(90,'2025-11-29 17:01:58','Admin User','10 evo ürününe fotoğraf eklendi','CREATE','2025-11-29 14:01:58'),(91,'2025-11-29 17:02:36','Admin User','10 evo ürününe fotoğraf eklendi','CREATE','2025-11-29 14:02:36'),(92,'2025-11-29 17:13:04','Admin User','10 evo ürününün ana fotoğrafı değiştirildi','UPDATE','2025-11-29 14:13:04'),(93,'2025-11-29 17:13:15','Admin User','10 evo ürününe fotoğraf eklendi','CREATE','2025-11-29 14:13:15'),(94,'2025-11-29 18:00:23','Admin User','Ambalaj Malzemesi 2 ürününe fotoğraf eklendi','CREATE','2025-11-29 15:00:23'),(95,'2025-11-29 18:08:58','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 15:08:58'),(96,'2025-11-29 18:09:01','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 15:09:01'),(97,'2025-11-29 18:09:27','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 15:09:27'),(98,'2025-11-29 18:09:34','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 15:09:34'),(99,'2025-11-29 18:33:28','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 15:33:28'),(100,'2025-11-29 18:33:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 15:33:36'),(101,'2025-11-29 18:36:13','Admin User','10 evo ürününden fotoğraf silindi','DELETE','2025-11-29 15:36:13'),(102,'2025-11-29 18:36:34','Admin User','Ambalaj Malzemesi 2 ürününden fotoğraf silindi','DELETE','2025-11-29 15:36:34'),(103,'2025-11-29 18:36:50','Admin User','Ambalaj Malzemesi 2 ürününe fotoğraf eklendi','CREATE','2025-11-29 15:36:50'),(104,'2025-11-29 18:41:06','Admin User','Ambalaj Malzemesi 2 ürününden fotoğraf silindi','DELETE','2025-11-29 15:41:06'),(105,'2025-11-29 18:41:18','Admin User','Ambalaj Malzemesi 2 ürününe fotoğraf eklendi','CREATE','2025-11-29 15:41:18'),(106,'2025-11-29 18:41:31','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 15:41:31'),(107,'2025-11-29 18:41:34','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 15:41:34'),(108,'2025-11-29 18:41:47','Admin User','10 evo ürününe fotoğraf eklendi','CREATE','2025-11-29 15:41:47'),(109,'2025-11-29 18:42:07','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 15:42:07'),(110,'2025-11-29 18:42:13','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 15:42:13'),(111,'2025-11-29 21:45:31','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 18:45:31'),(112,'2025-11-29 21:46:09','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 18:46:09'),(113,'2025-11-29 21:51:19','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 18:51:19'),(114,'2025-11-29 21:51:23','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 18:51:23'),(115,'2025-11-29 21:52:42','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 18:52:42'),(116,'2025-11-29 21:52:44','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 18:52:44'),(117,'2025-11-29 21:58:22','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 18:58:22'),(118,'2025-11-29 21:58:25','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-29 18:58:25'),(119,'2025-11-29 22:00:34','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 9)','CREATE','2025-11-29 19:00:34'),(120,'2025-11-29 22:00:53','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 10)','CREATE','2025-11-29 19:00:53'),(121,'2025-11-29 22:03:12','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 11)','CREATE','2025-11-29 19:03:12'),(122,'2025-11-29 22:04:38','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 12)','CREATE','2025-11-29 19:04:38'),(123,'2025-11-29 22:04:54','Ali Can','Sipariş iptal edildi (ID: 8)','UPDATE','2025-11-29 19:04:54'),(124,'2025-11-29 22:04:59','Ali Can','Sipariş iptal edildi (ID: 9)','UPDATE','2025-11-29 19:04:59'),(125,'2025-11-29 22:05:04','Ali Can','Sipariş iptal edildi (ID: 10)','UPDATE','2025-11-29 19:05:04'),(126,'2025-11-30 02:42:57','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 13)','CREATE','2025-11-29 23:42:57'),(127,'2025-11-30 02:43:14','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-29 23:43:14'),(128,'2025-11-30 02:43:29','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-29 23:43:29'),(129,'2025-11-30 02:58:18','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-29 23:58:18'),(130,'2025-11-30 03:31:13','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-30 00:31:13'),(131,'2025-11-30 03:31:26','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-30 00:31:26'),(132,'2025-11-30 03:32:29','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-30 00:32:29'),(133,'2025-11-30 03:35:59','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-30 00:35:59'),(134,'2025-11-30 03:40:16','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-30 00:40:16'),(135,'2025-12-03 07:31:10','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 04:31:10'),(136,'2025-12-03 07:31:31','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 04:31:31'),(137,'2025-12-03 07:31:36','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-03 04:31:36'),(138,'2025-12-03 07:35:21','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 14)','CREATE','2025-12-03 04:35:21'),(139,'2025-12-03 07:37:14','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-12-03 04:37:14'),(140,'2025-12-03 07:37:20','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 04:37:20'),(141,'2025-12-03 08:03:14','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 05:03:14'),(142,'2025-12-03 08:03:19','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-03 05:03:19'),(143,'2025-12-03 08:04:51','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 15)','CREATE','2025-12-03 05:04:51'),(144,'2025-12-03 08:04:59','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-12-03 05:04:59'),(145,'2025-12-03 08:05:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 05:05:04'),(146,'2025-12-03 10:52:45','Admin User','Tedarikçi B tedarikçisi Tedarikçi B olarak güncellendi','UPDATE','2025-12-03 07:52:45'),(147,'2025-12-03 10:53:01','Admin User','aaabbb tedarikçisi sisteme eklendi','CREATE','2025-12-03 07:53:01'),(148,'2025-12-03 10:53:20','Admin User','aaabbb tedarikçisi sistemden silindi','DELETE','2025-12-03 07:53:20'),(149,'2025-12-03 10:53:31','Admin User','a tedarikçisi sisteme eklendi','CREATE','2025-12-03 07:53:31'),(150,'2025-12-03 10:58:29','Admin User','a tedarikçisi sistemden silindi','DELETE','2025-12-03 07:58:29'),(151,'2025-12-03 10:58:40','Admin User','A tedarikçisi sisteme eklendi','CREATE','2025-12-03 07:58:40'),(152,'2025-12-03 10:58:51','Admin User','A tedarikçisi A olarak güncellendi','UPDATE','2025-12-03 07:58:51'),(153,'2025-12-03 10:58:56','Admin User','A tedarikçisi sistemden silindi','DELETE','2025-12-03 07:58:56'),(154,'2025-12-03 10:59:32','Admin User','Ali Can personelinin bilgileri güncellendi','UPDATE','2025-12-03 07:59:32'),(155,'2025-12-03 10:59:44','Admin User','Ali Can personeli sistemden silindi','DELETE','2025-12-03 07:59:44'),(156,'2025-12-03 11:00:08','Admin User','a personeli sisteme eklendi','CREATE','2025-12-03 08:00:08'),(157,'2025-12-03 11:00:15','Admin User','a personeli sistemden silindi','DELETE','2025-12-03 08:00:15'),(158,'2025-12-03 11:08:43','Admin User','Ayse Kaya müşterisi Ayse Kaya olarak güncellendi','UPDATE','2025-12-03 08:08:43'),(159,'2025-12-03 11:28:14','Admin User','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-03 08:28:14'),(160,'2025-12-03 11:28:22','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 08:28:22'),(161,'2025-12-03 11:28:28','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-03 08:28:28'),(162,'2025-12-03 11:28:38','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-12-03 08:28:38'),(163,'2025-12-03 11:28:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 08:28:44'),(164,'2025-12-03 11:28:56','Admin User','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-03 08:28:56'),(165,'2025-12-03 11:29:02','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 08:29:02'),(166,'2025-12-03 11:29:11','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-03 08:29:11'),(167,'2025-12-03 11:29:31','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-03 08:29:31'),(168,'2025-12-03 11:29:44','Admin User','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-03 08:29:44'),(169,'2025-12-03 11:36:03','Admin User','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-03 08:36:03'),(170,'2025-12-03 11:37:13','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-03 08:37:13'),(171,'2025-12-04 08:00:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 05:00:44'),(172,'2025-12-04 08:18:02','Admin User','Ahmet Yılmaz personelinin bilgileri güncellendi','UPDATE','2025-12-04 05:18:02'),(173,'2025-12-04 08:24:08','Admin User','Ahmet Yılmaz personelinin bilgileri güncellendi','UPDATE','2025-12-04 05:24:08'),(174,'2025-12-04 08:55:58','Admin User','Ahmet Yılmaz personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-04 05:55:58'),(175,'2025-12-04 09:44:17','Admin User','Yeni tekrarlı ödeme tanımlandı: ofis kirası (Kira) - 400 TL','CREATE','2025-12-04 06:44:17'),(176,'2025-12-04 09:44:37','Admin User','ofis kirası ödemesi yapıldı (2025/12) - 400 TL','CREATE','2025-12-04 06:44:37'),(177,'2025-12-04 10:23:32','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 07:23:32'),(178,'2025-12-04 10:40:28','Admin User','10 evo ürünü 10 evo olarak güncellendi','UPDATE','2025-12-04 07:40:28'),(179,'2025-12-04 11:25:26','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için çerçeve sözleşme güncellendi','UPDATE','2025-12-04 08:25:26'),(180,'2025-12-04 11:30:52','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 08:30:52'),(181,'2025-12-04 20:17:47','Ahmet Yılmaz','Personel giriş yaptı (E-posta/Telefon: ahmet.yilmaz@parfum.com)','Giriş Yapıldı','2025-12-04 17:17:47'),(182,'2025-12-04 20:17:57','Ahmet Yılmaz','personel oturumu kapattı (ID: 253)','Çıkış Yapıldı','2025-12-04 17:17:57'),(183,'2025-12-04 20:18:03','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 17:18:03'),(184,'2025-12-04 20:42:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 17:42:39'),(185,'2025-12-04 20:42:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 17:42:44'),(186,'2025-12-04 20:46:54','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 17:46:54'),(187,'2025-12-04 22:05:36','Admin User','10 evo ürünü için montaj iş emri güncellendi','UPDATE','2025-12-04 19:05:36'),(188,'2025-12-04 22:47:52','Admin User','Bergamot Essence esansı için iş emri güncellendi','UPDATE','2025-12-04 19:47:52'),(189,'2025-12-04 23:45:30','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-04 20:45:30'),(190,'2025-12-05 11:26:29','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:26:29'),(191,'2025-12-05 11:27:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:27:09'),(192,'2025-12-05 11:27:53','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:27:53'),(193,'2025-12-05 11:28:03','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:28:03'),(194,'2025-12-05 11:29:05','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 08:29:05'),(195,'2025-12-05 18:27:29','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:27:29'),(196,'2025-12-05 18:35:13','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:35:13'),(197,'2025-12-05 18:35:28','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:35:28'),(198,'2025-12-05 18:39:29','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:39:29'),(199,'2025-12-05 18:39:36','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:39:36'),(200,'2025-12-05 18:39:40','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:39:40'),(201,'2025-12-05 18:39:53','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:39:53'),(202,'2025-12-05 18:41:05','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:41:05'),(203,'2025-12-05 18:41:09','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:41:09'),(204,'2025-12-05 18:42:02','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:42:02'),(205,'2025-12-05 18:52:41','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:52:41'),(206,'2025-12-05 18:54:08','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:54:08'),(207,'2025-12-05 18:55:04','Admin User','0 tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:55:04'),(208,'2025-12-05 18:55:08','Admin User','0 tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 15:55:08'),(209,'2025-12-05 18:55:42','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:55:42'),(210,'2025-12-05 18:57:33','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 15:57:33'),(211,'2025-12-05 19:00:33','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 16:00:33'),(212,'2025-12-05 19:00:40','Admin User','0 tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:00:40'),(213,'2025-12-05 19:00:43','Admin User','0 tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:00:43'),(214,'2025-12-05 19:04:44','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 16:04:44'),(215,'2025-12-05 19:04:50','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:04:50'),(216,'2025-12-05 19:07:56','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 16:07:56'),(217,'2025-12-05 19:08:07','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:08:07'),(218,'2025-12-05 19:08:10','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi siparişi silindi','DELETE','2025-12-05 16:08:10'),(219,'2025-12-05 19:08:22','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 16:08:22'),(220,'2025-12-05 19:33:51','Admin User','Perfume 2 malzemesi sistemden silindi','DELETE','2025-12-05 16:33:51'),(221,'2025-12-05 19:33:54','Admin User','Perfume 8 malzemesi sistemden silindi','DELETE','2025-12-05 16:33:54'),(222,'2025-12-05 19:33:57','Admin User','Perfume 7 malzemesi sistemden silindi','DELETE','2025-12-05 16:33:57'),(223,'2025-12-05 19:34:00','Admin User','Perfume 5 malzemesi sistemden silindi','DELETE','2025-12-05 16:34:00'),(224,'2025-12-05 19:34:03','Admin User','Perfume 4 malzemesi sistemden silindi','DELETE','2025-12-05 16:34:03'),(225,'2025-12-05 19:34:07','Admin User','Perfume 6 malzemesi sistemden silindi','DELETE','2025-12-05 16:34:07'),(226,'2025-12-05 19:34:09','Admin User','Perfume 3 malzemesi sistemden silindi','DELETE','2025-12-05 16:34:09'),(227,'2025-12-05 19:34:25','Admin User','Medium Box malzemesi Medium Box olarak güncellendi','UPDATE','2025-12-05 16:34:25'),(228,'2025-12-05 19:37:53','Admin User','Esans Hammaddesi malzemesi sisteme eklendi','CREATE','2025-12-05 16:37:53'),(229,'2025-12-05 19:38:15','Admin User','Bergamot Essence ürün ağacından Ambalaj Malzemesi 2 bileşeni silindi','DELETE','2025-12-05 16:38:15'),(230,'2025-12-05 19:38:30','Admin User','Bergamot Essence ürün ağacındaki Medium Box bileşeni Esans Hammaddesi olarak güncellendi','UPDATE','2025-12-05 16:38:30'),(231,'2025-12-05 21:11:05','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 18:11:05'),(232,'2025-12-05 21:11:51','Admin User','Tedarikçi Avv tedarikçisine Esans Hammaddesi malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-05 18:11:51'),(233,'2025-12-05 21:12:15','Admin User','Tedarikçi Avv tedarikçisine Esans Hammaddesi malzemesi için sipariş oluşturuldu','CREATE','2025-12-05 18:12:15'),(234,'2025-12-06 01:45:16','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-05 22:45:16'),(235,'2025-12-06 01:45:20','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 22:45:20'),(236,'2025-12-06 02:24:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-05 23:24:45'),(237,'2025-12-06 03:26:50','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:26:50'),(238,'2025-12-06 03:30:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:30:45'),(239,'2025-12-06 03:31:03','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:31:03'),(240,'2025-12-06 03:31:19','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:31:19'),(241,'2025-12-06 03:31:54','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:31:54'),(242,'2025-12-06 03:35:19','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:35:19'),(243,'2025-12-06 03:40:58','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 00:40:58'),(244,'2025-12-06 04:27:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 01:27:39'),(245,'2025-12-06 04:27:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 01:27:45'),(246,'2025-12-06 04:59:12','Admin User','Ayse Kaya müşterisi için yeni sipariş oluşturuldu (ID: 16)','CREATE','2025-12-06 01:59:12'),(247,'2025-12-06 05:00:30','Admin User','Ayse Kaya müşterisine ait 16 nolu siparişin yeni durumu: İptal Edildi','UPDATE','2025-12-06 02:00:30'),(248,'2025-12-06 14:02:47','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 11:02:47'),(249,'2025-12-06 14:03:57','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-06 11:03:57'),(250,'2025-12-06 14:04:01','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 11:04:01'),(251,'2025-12-06 14:28:15','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 11:28:15'),(252,'2025-12-06 23:52:59','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-06 20:52:59'),(253,'2025-12-07 18:03:33','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-07 15:03:33'),(254,'2025-12-07 18:24:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-07 15:24:04'),(255,'2025-12-07 18:47:09','Admin User','Mehmet Kaya personeli sistemden silindi','DELETE','2025-12-07 15:47:09'),(256,'2025-12-08 00:56:16','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-07 21:56:16'),(257,'2025-12-08 02:53:57','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-07 23:53:57'),(258,'2025-12-08 02:54:36','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-07 23:54:36'),(259,'2025-12-08 15:07:19','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-08 12:07:19'),(260,'2025-12-08 15:16:15','SISTEM','Acil durum kullanıcısı ile en son yedekten geri yükleme yapıldı.','Kritik Eylem','2025-12-08 12:16:15'),(261,'2025-12-08 15:16:21','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-08 12:16:21'),(262,'2025-12-08 15:26:30','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-08 12:26:30'),(263,'2025-12-08 15:30:28','Admin Kullanýcý 2','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 12:30:28'),(264,'2025-12-08 15:45:59','Admin Kullanýcý 2','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-08 12:45:59'),(265,'2025-12-08 15:46:03','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 12:46:03'),(266,'2025-12-08 15:46:22','Yedek Admin','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-08 12:46:22'),(267,'2025-12-08 15:49:33','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 12:49:33'),(268,'2025-12-08 16:25:18','Yedek Admin','personeller tabloları temizlendi','DELETE','2025-12-08 13:25:18'),(269,'2025-12-08 16:38:57','Yedek Admin','apo personeli sisteme eklendi','CREATE','2025-12-08 13:38:57'),(270,'2025-12-08 16:41:51','Yedek Admin','apo personeli sistemden silindi','DELETE','2025-12-08 13:41:51'),(271,'2025-12-08 16:46:09','Yedek Admin','apo personeli sisteme eklendi','CREATE','2025-12-08 13:46:09'),(272,'2025-12-08 16:46:15','Yedek Admin','apo personeli sistemden silindi','DELETE','2025-12-08 13:46:15'),(273,'2025-12-08 17:00:30','Yedek Admin','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-08 14:00:30'),(274,'2025-12-08 17:00:33','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 14:00:33'),(275,'2025-12-08 17:09:22','Yedek Admin','Ahmet Yıldız personeli sisteme eklendi','CREATE','2025-12-08 14:09:22'),(276,'2025-12-08 17:10:54','Yedek Admin','Mehmet Bulut personeli sisteme eklendi','CREATE','2025-12-08 14:10:54'),(277,'2025-12-08 17:11:29','Yedek Admin','Ahmet Yıldız personeli sistemden silindi','DELETE','2025-12-08 14:11:29'),(278,'2025-12-08 17:11:33','Yedek Admin','Mehmet Bulut personeli sistemden silindi','DELETE','2025-12-08 14:11:33'),(279,'2025-12-08 17:14:50','Yedek Admin','apo personeli sisteme eklendi','CREATE','2025-12-08 14:14:50'),(280,'2025-12-08 17:14:58','Yedek Admin','apo personeli sistemden silindi','DELETE','2025-12-08 14:14:58'),(281,'2025-12-08 17:15:24','Yedek Admin','apo personeli sisteme eklendi','CREATE','2025-12-08 14:15:24'),(282,'2025-12-08 17:15:59','Yedek Admin','apo personeli sistemden silindi','DELETE','2025-12-08 14:15:59'),(283,'2025-12-08 17:23:34','Yedek Admin','Ahmet Yılmaz personeli sisteme eklendi','CREATE','2025-12-08 14:23:34'),(284,'2025-12-08 17:24:32','Yedek Admin','Ahmet Yılmaz personelinin bilgileri güncellendi','UPDATE','2025-12-08 14:24:32'),(285,'2025-12-08 17:27:06','Ahmet Yılmaz','Personel giriş yaptı (E-posta/Telefon: 05515515151)','Giriş Yapıldı','2025-12-08 14:27:06'),(286,'2025-12-08 17:27:59','Ahmet Yılmaz','dsa tedarikçisi sisteme eklendi','CREATE','2025-12-08 14:27:59'),(287,'2025-12-08 17:28:32','Ahmet Yılmaz','personel oturumu kapattı (ID: 293)','Çıkış Yapıldı','2025-12-08 14:28:32'),(288,'2025-12-08 17:28:38','Ahmet Yılmaz','Personel giriş yaptı (E-posta/Telefon: 05515515151)','Giriş Yapıldı','2025-12-08 14:28:38'),(289,'2025-12-08 17:28:52','Ahmet Yılmaz','personel oturumu kapattı (ID: 293)','Çıkış Yapıldı','2025-12-08 14:28:52'),(290,'2025-12-08 17:29:03','Yedek Admin','Ahmet Yılmaz personeli sistemden silindi','DELETE','2025-12-08 14:29:03'),(291,'2025-12-08 17:31:36','Yedek Admin','Ahmet Yıldırım personeli sisteme eklendi','CREATE','2025-12-08 14:31:36'),(292,'2025-12-08 17:32:40','Yedek Admin','Ahmet Yıldırım personelinin bilgileri güncellendi','UPDATE','2025-12-08 14:32:40'),(293,'2025-12-08 17:35:09','Ahmet Yıldırım','Personel giriş yaptı (E-posta/Telefon: 05515515151)','Giriş Yapıldı','2025-12-08 14:35:09'),(294,'2025-12-08 17:36:04','Ahmet Yıldırım','personel oturumu kapattı (ID: 294)','Çıkış Yapıldı','2025-12-08 14:36:04'),(295,'2025-12-08 17:42:54','Yedek Admin','dsa tedarikçisi sistemden silindi','DELETE','2025-12-08 14:42:54'),(296,'2025-12-08 17:43:21','Yedek Admin','Ebru Gunes müşterisi sistemden silindi','DELETE','2025-12-08 14:43:21'),(297,'2025-12-08 17:43:26','Yedek Admin','Eflatun Kozmetik müşterisi sistemden silindi','DELETE','2025-12-08 14:43:26'),(298,'2025-12-08 17:49:08','Yedek Admin','Özhan Aydın müşterisi sisteme eklendi','CREATE','2025-12-08 14:49:08'),(299,'2025-12-08 17:49:45','Yedek Admin','Özhan Aydın müşterisi sistemden silindi','DELETE','2025-12-08 14:49:45'),(300,'2025-12-08 17:50:30','Yedek Admin','Özhan Aydın müşterisi sisteme eklendi','CREATE','2025-12-08 14:50:30'),(301,'2025-12-08 17:52:12','Yedek Admin','Özhan Aydın müşterisi sistemden silindi','DELETE','2025-12-08 14:52:12'),(302,'2025-12-08 17:52:57','Yedek Admin','Özhan Aydın müşterisi sisteme eklendi','CREATE','2025-12-08 14:52:57'),(303,'2025-12-08 17:54:03','Yedek Admin','ÖZhan müşterisi sisteme eklendi','CREATE','2025-12-08 14:54:03'),(304,'2025-12-08 17:54:11','Yedek Admin','Özhan Aydın müşterisi sistemden silindi','DELETE','2025-12-08 14:54:11'),(305,'2025-12-08 17:54:15','Yedek Admin','ÖZhan müşterisi sistemden silindi','DELETE','2025-12-08 14:54:15'),(306,'2025-12-08 17:56:00','Yedek Admin','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-08 14:56:00'),(307,'2025-12-08 17:57:08','Yedek Admin','Özhan Aydın müşterisi sisteme eklendi','CREATE','2025-12-08 14:57:08'),(308,'2025-12-08 17:57:53','Özhan Aydın','Müşteri giriş yaptı (E-posta/Telefon: 05003001010)','Giriş Yapıldı','2025-12-08 14:57:53'),(309,'2025-12-08 17:58:39','Yedek Admin','Özhan Aydın müşterisi Özhan Aydın olarak güncellendi','UPDATE','2025-12-08 14:58:39'),(310,'2025-12-08 17:58:57','Özhan Aydın','musteri oturumu kapattı (ID: 160)','Çıkış Yapıldı','2025-12-08 14:58:57'),(311,'2025-12-08 18:05:02','Yedek Admin','tedarikciler tabloları temizlendi','DELETE','2025-12-08 15:05:02'),(312,'2025-12-08 18:05:15','Yedek Admin','cerceve_sozlesmeler tabloları temizlendi','DELETE','2025-12-08 15:05:15'),(313,'2025-12-08 18:07:16','Yedek Admin','Ahmet Kozmetik tedarikçisi sisteme eklendi','CREATE','2025-12-08 15:07:16'),(314,'2025-12-08 18:10:27','Yedek Admin','lokasyonlar tabloları temizlendi','DELETE','2025-12-08 15:10:27'),(315,'2025-12-08 18:11:13','Yedek Admin','tanklar tabloları temizlendi','DELETE','2025-12-08 15:11:13'),(316,'2025-12-08 18:11:32','Yedek Admin','is_merkezleri tabloları temizlendi','DELETE','2025-12-08 15:11:32'),(317,'2025-12-08 18:35:33','Yedek Admin','Merkez Depo deposuna A1 rafı eklendi','CREATE','2025-12-08 15:35:33'),(318,'2025-12-08 18:35:56','Yedek Admin','Merkez Depo deposuna A2 rafı eklendi','CREATE','2025-12-08 15:35:56'),(319,'2025-12-08 18:36:10','Yedek Admin','Merkez Depo deposuna A3 rafı eklendi','CREATE','2025-12-08 15:36:10'),(320,'2025-12-08 18:36:29','Yedek Admin','Satış Depo deposuna 1 rafı eklendi','CREATE','2025-12-08 15:36:29'),(321,'2025-12-08 18:36:42','Yedek Admin','Satış Depo deposuna 2 rafı eklendi','CREATE','2025-12-08 15:36:42'),(322,'2025-12-08 18:38:18','Yedek Admin','Birinci Tankımız adlı tank sisteme eklendi','CREATE','2025-12-08 15:38:18'),(323,'2025-12-08 18:38:44','Yedek Admin','Eski Tank adlı tank sisteme eklendi','CREATE','2025-12-08 15:38:44'),(324,'2025-12-08 18:40:00','Yedek Admin','Montaj Masası 1 iş merkezi eklendi','CREATE','2025-12-08 15:40:00'),(325,'2025-12-08 18:40:19','Yedek Admin','Montaj Masası 2 iş merkezi eklendi','CREATE','2025-12-08 15:40:19'),(326,'2025-12-08 18:40:46','Yedek Admin','Montajcı Abdullah iş merkezi eklendi','CREATE','2025-12-08 15:40:46'),(327,'2025-12-08 18:41:19','Yedek Admin','Montajcı Abdullah iş merkezi silindi','DELETE','2025-12-08 15:41:19'),(328,'2025-12-08 18:41:23','Yedek Admin','Montaj Masası 2 iş merkezi silindi','DELETE','2025-12-08 15:41:23'),(329,'2025-12-08 18:53:02','Yedek Admin','Satış Depo deposuna Kasa Arkası Rafı rafı eklendi','CREATE','2025-12-08 15:53:02'),(330,'2025-12-08 18:53:24','Yedek Admin','Satış Depo deposundaki 2 rafı silindi','DELETE','2025-12-08 15:53:24'),(331,'2025-12-08 18:54:30','Yedek Admin','Beşliyüz Tank adlı tank sisteme eklendi','CREATE','2025-12-08 15:54:30'),(332,'2025-12-08 18:55:51','Yedek Admin','Abdullah Usta iş merkezi eklendi','CREATE','2025-12-08 15:55:51'),(333,'2025-12-08 19:02:54','Yedek Admin','Ambalaj Malzemesi 2 malzemesi sistemden silindi','DELETE','2025-12-08 16:02:54'),(334,'2025-12-08 19:02:58','Yedek Admin','Esans Hammaddesi malzemesi sistemden silindi','DELETE','2025-12-08 16:02:58'),(335,'2025-12-08 19:03:02','Yedek Admin','Medium Box malzemesi sistemden silindi','DELETE','2025-12-08 16:03:02'),(336,'2025-12-08 19:04:01','Yedek Admin','urunler tabloları temizlendi','DELETE','2025-12-08 16:04:01'),(337,'2025-12-08 19:04:13','Yedek Admin','esanslar tabloları temizlendi','DELETE','2025-12-08 16:04:13'),(338,'2025-12-08 19:04:25','Yedek Admin','urun_agaci tabloları temizlendi','DELETE','2025-12-08 16:04:25'),(339,'2025-12-08 19:05:28','Yedek Admin','AA malzemesi sisteme eklendi','CREATE','2025-12-08 16:05:28'),(340,'2025-12-08 19:09:56','Yedek Admin','sadsa malzemesi sisteme eklendi','CREATE','2025-12-08 16:09:56'),(341,'2025-12-08 19:10:01','Yedek Admin','sadsa malzemesi sistemden silindi','DELETE','2025-12-08 16:10:01'),(342,'2025-12-08 19:10:05','Yedek Admin','AA malzemesi sistemden silindi','DELETE','2025-12-08 16:10:05'),(343,'2025-12-08 19:10:25','Yedek Admin','dsa malzemesi sisteme eklendi','CREATE','2025-12-08 16:10:25'),(344,'2025-12-08 19:10:36','Yedek Admin','dsa ürününe fotoğraf eklendi','CREATE','2025-12-08 16:10:36'),(345,'2025-12-08 19:10:46','Yedek Admin','dsa malzemesi sistemden silindi','DELETE','2025-12-08 16:10:46'),(346,'2025-12-08 19:14:12','Yedek Admin','Dolge Gabbana Dış Kutu malzemesi sisteme eklendi','CREATE','2025-12-08 16:14:12'),(347,'2025-12-08 19:14:53','Yedek Admin','Dolge Gabbana Dış Kutu ürününe fotoğraf eklendi','CREATE','2025-12-08 16:14:53'),(348,'2025-12-08 19:15:06','Yedek Admin','Dolge Gabbana Dış Kutu ürününden fotoğraf silindi','DELETE','2025-12-08 16:15:06'),(349,'2025-12-08 19:15:59','Yedek Admin','Dolge Gabbana Dış Kutu ürününe fotoğraf eklendi','CREATE','2025-12-08 16:15:59'),(350,'2025-12-08 19:16:03','Yedek Admin','Dolge Gabbana Dış Kutu ürününden fotoğraf silindi','DELETE','2025-12-08 16:16:03'),(351,'2025-12-08 19:16:08','Yedek Admin','Dolge Gabbana Dış Kutu malzemesi sistemden silindi','DELETE','2025-12-08 16:16:08'),(352,'2025-12-08 19:18:00','Yedek Admin','212 Man Etiketi malzemesi sisteme eklendi','CREATE','2025-12-08 16:18:00'),(353,'2025-12-08 19:18:49','Yedek Admin','212 Man Etiketi ürününe fotoğraf eklendi','CREATE','2025-12-08 16:18:49'),(354,'2025-12-08 19:40:05','Yedek Admin','ahmet ürünü sisteme eklendi','CREATE','2025-12-08 16:40:05'),(355,'2025-12-08 19:44:08','Yedek Admin','ahmet ürünü ahmet olarak güncellendi','UPDATE','2025-12-08 16:44:08'),(356,'2025-12-08 19:47:58','Yedek Admin','ahmet ürünü sistemden silindi','DELETE','2025-12-08 16:47:58'),(357,'2025-12-08 19:48:17','Yedek Admin','aaa ürünü sisteme eklendi','CREATE','2025-12-08 16:48:17'),(358,'2025-12-08 19:48:25','Yedek Admin','aaa ürünü aaa olarak güncellendi','UPDATE','2025-12-08 16:48:25'),(359,'2025-12-08 20:01:07','Yedek Admin','sda1 esansı sisteme eklendi','CREATE','2025-12-08 17:01:07'),(360,'2025-12-08 20:01:12','Yedek Admin','sda1 esansı sistemden silindi','DELETE','2025-12-08 17:01:12'),(361,'2025-12-08 20:01:21','Yedek Admin','aaa ürünü sistemden silindi','DELETE','2025-12-08 17:01:21'),(362,'2025-12-08 20:03:59','Yedek Admin','sda ürünü sisteme eklendi','CREATE','2025-12-08 17:03:59'),(363,'2025-12-08 20:04:27','Yedek Admin','sda ürünü sistemden silindi','DELETE','2025-12-08 17:04:27'),(364,'2025-12-08 20:05:12','Yedek Admin','Bergamot Essence esansı sisteme eklendi','CREATE','2025-12-08 17:05:12'),(365,'2025-12-08 20:05:18','Yedek Admin','Bergamot Essence esansı sistemden silindi','DELETE','2025-12-08 17:05:18'),(366,'2025-12-08 20:07:10','Yedek Admin','212 Men ürünü sisteme eklendi','CREATE','2025-12-08 17:07:10'),(367,'2025-12-08 20:08:28','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:08:28'),(368,'2025-12-08 20:08:28','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:08:28'),(369,'2025-12-08 20:08:29','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:08:29'),(370,'2025-12-08 20:08:34','Yedek Admin','212 Men ürününden fotoğraf silindi','DELETE','2025-12-08 17:08:34'),(371,'2025-12-08 20:08:36','Yedek Admin','212 Men ürününden fotoğraf silindi','DELETE','2025-12-08 17:08:36'),(372,'2025-12-08 20:08:39','Yedek Admin','212 Men ürününden fotoğraf silindi','DELETE','2025-12-08 17:08:39'),(373,'2025-12-08 20:08:44','Yedek Admin','212 Men ürünü sistemden silindi','DELETE','2025-12-08 17:08:44'),(374,'2025-12-08 20:17:23','Yedek Admin','212 Men ürünü sisteme eklendi','CREATE','2025-12-08 17:17:23'),(375,'2025-12-08 20:17:53','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:17:53'),(376,'2025-12-08 20:17:54','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:17:54'),(377,'2025-12-08 20:17:54','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:17:54'),(378,'2025-12-08 20:18:01','Yedek Admin','212 Men ürününün ana fotoğrafı değiştirildi','UPDATE','2025-12-08 17:18:01'),(379,'2025-12-08 20:19:47','Yedek Admin','212 Men ürünü sistemden silindi','DELETE','2025-12-08 17:19:47'),(380,'2025-12-08 20:20:04','Yedek Admin','sada ürünü sisteme eklendi','CREATE','2025-12-08 17:20:04'),(381,'2025-12-08 20:20:50','Yedek Admin','sada ürününe fotoğraf eklendi','CREATE','2025-12-08 17:20:50'),(382,'2025-12-08 20:20:54','Yedek Admin','sada ürününe fotoğraf eklendi','CREATE','2025-12-08 17:20:54'),(383,'2025-12-08 20:20:58','Yedek Admin','sada ürününe fotoğraf eklendi','CREATE','2025-12-08 17:20:58'),(384,'2025-12-08 20:22:24','Yedek Admin','sada ürünü sistemden silindi','DELETE','2025-12-08 17:22:24'),(385,'2025-12-08 20:22:53','Yedek Admin','sd ürünü sisteme eklendi','CREATE','2025-12-08 17:22:53'),(386,'2025-12-08 20:23:05','Yedek Admin','sd ürününe fotoğraf eklendi','CREATE','2025-12-08 17:23:05'),(387,'2025-12-08 20:23:08','Yedek Admin','sd ürününe fotoğraf eklendi','CREATE','2025-12-08 17:23:08'),(388,'2025-12-08 20:23:11','Yedek Admin','sd ürününe fotoğraf eklendi','CREATE','2025-12-08 17:23:11'),(389,'2025-12-08 20:24:46','Yedek Admin','sd ürünü sistemden silindi','DELETE','2025-12-08 17:24:46'),(390,'2025-12-08 20:25:07','Yedek Admin','abc ürünü sisteme eklendi','CREATE','2025-12-08 17:25:07'),(391,'2025-12-08 20:25:16','Yedek Admin','abc ürününe fotoğraf eklendi','CREATE','2025-12-08 17:25:16'),(392,'2025-12-08 20:25:18','Yedek Admin','abc ürününe fotoğraf eklendi','CREATE','2025-12-08 17:25:18'),(393,'2025-12-08 20:25:21','Yedek Admin','abc ürününe fotoğraf eklendi','CREATE','2025-12-08 17:25:21'),(394,'2025-12-08 20:25:34','Yedek Admin','abc ürünü sistemden silindi','DELETE','2025-12-08 17:25:34'),(395,'2025-12-08 20:25:56','Yedek Admin','kljl esansı sisteme eklendi','CREATE','2025-12-08 17:25:56'),(396,'2025-12-08 20:26:02','Yedek Admin','kljl esansı sistemden silindi','DELETE','2025-12-08 17:26:02'),(397,'2025-12-08 20:33:41','Yedek Admin','212 Men ürünü sisteme eklendi','CREATE','2025-12-08 17:33:41'),(398,'2025-12-08 20:34:24','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:34:24'),(399,'2025-12-08 20:34:28','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:34:28'),(400,'2025-12-08 20:34:32','Yedek Admin','212 Men ürününe fotoğraf eklendi','CREATE','2025-12-08 17:34:32'),(401,'2025-12-08 20:34:36','Yedek Admin','212 Men ürününün ana fotoğrafı değiştirildi','UPDATE','2025-12-08 17:34:36'),(402,'2025-12-08 20:36:12','Yedek Admin','Issey Miyaki ürünü sisteme eklendi','CREATE','2025-12-08 17:36:12'),(403,'2025-12-08 20:42:01','Yedek Admin','Gül Kokusu Esansı esansı sisteme eklendi','CREATE','2025-12-08 17:42:01'),(404,'2025-12-08 20:44:27','Yedek Admin','personel oturumu kapattı (ID: 283)','Çıkış Yapıldı','2025-12-08 17:44:27'),(405,'2025-12-08 23:15:23','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-08 20:15:23'),(406,'2025-12-08 23:16:22','Yedek Admin','212 Man Etiketi malzemesi 212 Man Etiketi olarak güncellendi','UPDATE','2025-12-08 20:16:22'),(407,'2025-12-08 23:17:18','Yedek Admin','A1 Dış Kutu malzemesi sisteme eklendi','CREATE','2025-12-08 20:17:18'),(408,'2025-12-08 23:19:56','Yedek Admin','A1 Dış Kutu malzemesi A1 Dış Kutu olarak güncellendi','UPDATE','2025-12-08 20:19:56'),(409,'2025-12-08 23:20:31','Yedek Admin','212 Men Kapat malzemesi sisteme eklendi','CREATE','2025-12-08 20:20:31'),(410,'2025-12-08 23:21:59','Yedek Admin','212 Men Kapat malzemesi 212 Men Kapat olarak güncellendi','UPDATE','2025-12-08 20:21:59'),(411,'2025-12-08 23:22:28','Yedek Admin','212 Men Şişesi malzemesi sisteme eklendi','CREATE','2025-12-08 20:22:28'),(412,'2025-12-08 23:27:53','Yedek Admin','Saf Su malzemesi sisteme eklendi','CREATE','2025-12-08 20:27:53'),(413,'2025-12-08 23:28:30','Yedek Admin','Alkol malzemesi sisteme eklendi','CREATE','2025-12-08 20:28:30'),(414,'2025-12-08 23:34:41','Yedek Admin','Gül Kokusu Esansı ürün ağacına Alkol bileşeni eklendi','CREATE','2025-12-08 20:34:41'),(415,'2025-12-08 23:35:41','Yedek Admin','Gül Kokusu Esansı ürün ağacına Saf Su bileşeni eklendi','CREATE','2025-12-08 20:35:41'),(416,'2025-12-08 23:35:59','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2025-12-08 20:35:59'),(417,'2025-12-08 23:56:22','Yedek Admin','Gül Kokusu Esansı ürün ağacından Saf Su bileşeni silindi','DELETE','2025-12-08 20:56:22'),(418,'2025-12-08 23:56:28','Yedek Admin','Gül Kokusu Esansı ürün ağacından Alkol bileşeni silindi','DELETE','2025-12-08 20:56:28'),(419,'2025-12-09 00:41:10','Yedek Admin','Gül Kokusu Esansı esansı sistemden silindi','DELETE','2025-12-08 21:41:10'),(420,'2025-12-09 00:42:57','Yedek Admin','Gül Kokusu Esansı esansı sisteme eklendi','CREATE','2025-12-08 21:42:57'),(421,'2025-12-09 00:44:08','Yedek Admin','Gül Kokusu Esansı ürün ağacına Alkol bileşeni eklendi','CREATE','2025-12-08 21:44:08'),(422,'2025-12-09 00:44:45','Yedek Admin','Gül Kokusu Esansı ürün ağacına Alkol bileşeni eklendi','CREATE','2025-12-08 21:44:45'),(423,'2025-12-09 00:45:03','Yedek Admin','Gül Kokusu Esansı ürün ağacından Alkol bileşeni silindi','DELETE','2025-12-08 21:45:03'),(424,'2025-12-09 00:45:08','Yedek Admin','Gül Kokusu Esansı ürün ağacından Alkol bileşeni silindi','DELETE','2025-12-08 21:45:08'),(425,'2025-12-09 00:48:58','Yedek Admin','Gül Kokusu Esansı ürün ağacına Alkol bileşeni eklendi','CREATE','2025-12-08 21:48:58'),(426,'2025-12-09 00:49:37','Yedek Admin','Gül Kokusu Esansı ürün ağacına Saf Su bileşeni eklendi','CREATE','2025-12-08 21:49:37'),(427,'2025-12-09 00:52:01','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Saf Su bileşeni Saf Su olarak güncellendi','UPDATE','2025-12-08 21:52:01'),(428,'2025-12-09 00:52:11','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2025-12-08 21:52:11'),(429,'2025-12-09 00:54:10','Yedek Admin','212 Men ürün ağacına Gül Kokusu Esansı bileşeni eklendi','CREATE','2025-12-08 21:54:10'),(430,'2025-12-09 00:54:24','Yedek Admin','212 Men ürün ağacına 212 Man Etiketi bileşeni eklendi','CREATE','2025-12-08 21:54:24'),(431,'2025-12-09 00:54:37','Yedek Admin','212 Men ürün ağacına 212 Men Kapat bileşeni eklendi','CREATE','2025-12-08 21:54:37'),(432,'2025-12-09 00:54:52','Yedek Admin','212 Men ürün ağacına 212 Men Şişesi bileşeni eklendi','CREATE','2025-12-08 21:54:52'),(433,'2025-12-09 01:01:49','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2025-12-08 22:01:49'),(434,'2025-12-09 01:01:59','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Saf Su bileşeni Saf Su olarak güncellendi','UPDATE','2025-12-08 22:01:59'),(435,'2025-12-09 01:03:43','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Saf Su bileşeni Saf Su olarak güncellendi','UPDATE','2025-12-08 22:03:43'),(436,'2025-12-09 01:03:52','Yedek Admin','Gül Kokusu Esansı ürün ağacındaki Alkol bileşeni Alkol olarak güncellendi','UPDATE','2025-12-08 22:03:52'),(437,'2025-12-09 01:05:28','Yedek Admin','212 Men ürün ağacına A1 Dış Kutu bileşeni eklendi','CREATE','2025-12-08 22:05:28'),(438,'2025-12-09 02:34:40','Yedek Admin','Ahmet Kozmetik tedarikçisine Saf Su malzemesi için çerçeve sözleşme eklendi','CREATE','2025-12-08 23:34:40'),(439,'2025-12-09 02:37:14','Yedek Admin','Kira kategorisindeki 400.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:14'),(440,'2025-12-09 02:37:19','Yedek Admin','Malzeme Gideri kategorisindeki 210.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:19'),(441,'2025-12-09 02:37:24','Yedek Admin','Sarf Malzeme Gideri kategorisindeki 50.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:24'),(442,'2025-12-09 02:37:28','Yedek Admin','Fire Gideri kategorisindeki 88.80 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:28'),(443,'2025-12-09 02:37:32','Yedek Admin','Fire Gideri kategorisindeki 21.88 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:32'),(444,'2025-12-09 02:37:36','Yedek Admin','Fire Gideri kategorisindeki 236.56 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:36'),(445,'2025-12-09 02:37:41','Yedek Admin','Malzeme Gideri kategorisindeki 300.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:41'),(446,'2025-12-09 02:37:48','Yedek Admin','Personel Gideri kategorisindeki 500.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:37:48'),(447,'2025-12-09 02:38:22','Yedek Admin','212 Men Kapat için 12 adet stok hareketi ve ilgili gider kaydı eklendi','CREATE','2025-12-08 23:38:22'),(448,'2025-12-09 02:38:33','Yedek Admin','Fire Gideri kategorisindeki 252.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:38:33'),(449,'2025-12-09 02:44:33','Yedek Admin','Diğer kategorisinde 200 TL tutarında gider eklendi','CREATE','2025-12-08 23:44:33'),(450,'2025-12-09 02:47:48','Yedek Admin','Ahmet Yıldırım personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-08 23:47:48'),(451,'2025-12-09 02:48:41','Yedek Admin','Ahmet Yıldırım personeline 2025/12 dönemi için 50000 TL maaş ödemesi yapıldı','CREATE','2025-12-08 23:48:41'),(452,'2025-12-09 02:48:53','Yedek Admin','Personel Gideri kategorisindeki 50000.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:48:53'),(453,'2025-12-09 02:55:00','Yedek Admin','Ahmet Yıldırım personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-08 23:55:00'),(454,'2025-12-09 02:55:44','Yedek Admin','Ahmet Yıldırım personeline 2025/12 dönemi için 50000 TL maaş ödemesi yapıldı','CREATE','2025-12-08 23:55:44'),(455,'2025-12-09 02:57:14','Yedek Admin','personel_maas_odemeleri tabloları temizlendi','DELETE','2025-12-08 23:57:14'),(456,'2025-12-09 02:57:40','Yedek Admin','Personel Avansı kategorisindeki 10000.00 TL tutarlı gider silindi','DELETE','2025-12-08 23:57:40'),(457,'2025-12-09 03:00:38','Yedek Admin','personel_avanslar tabloları temizlendi','DELETE','2025-12-09 00:00:38'),(458,'2025-12-09 03:01:30','Yedek Admin','Ahmet Yıldırım personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-09 00:01:30'),(459,'2025-12-09 03:01:53','Yedek Admin','Personel Gideri kategorisindeki 50000.00 TL tutarlı gider silindi','DELETE','2025-12-09 00:01:53'),(460,'2025-12-09 03:02:02','Yedek Admin','Personel Avansı kategorisindeki 10000.00 TL tutarlı gider silindi','DELETE','2025-12-09 00:02:02'),(461,'2025-12-09 03:02:27','Yedek Admin','personel_avanslar, personel_maas_odemeleri tabloları temizlendi','DELETE','2025-12-09 00:02:27'),(462,'2025-12-09 03:03:23','Yedek Admin','Ahmet Yıldırım personeline 10000 TL avans verildi (2025/12)','CREATE','2025-12-09 00:03:23'),(463,'2025-12-09 03:03:53','Yedek Admin','Ahmet Yıldırım personeline 2025/12 dönemi için 50000 TL maaş ödemesi yapıldı','CREATE','2025-12-09 00:03:53'),(464,'2025-12-09 03:05:42','Yedek Admin','Tekrarlı ödeme silindi: ofis kirası','DELETE','2025-12-09 00:05:42'),(465,'2025-12-09 03:37:20','Yedek Admin','Yeni tekrarlı ödeme tanımlandı: Ofis Kirası (Kira) - 30000 TL','CREATE','2025-12-09 00:37:20'),(466,'2025-12-09 03:38:00','Yedek Admin','Yeni tekrarlı ödeme tanımlandı: Elektrik Faturamız (Elektrik) - 20000 TL','CREATE','2025-12-09 00:38:00'),(467,'2025-12-09 03:38:21','Yedek Admin','Ofis Kirası ödemesi yapıldı (2025/12) - 30000 TL','CREATE','2025-12-09 00:38:21'),(468,'2025-12-09 04:03:55','Yedek Admin','siparis_kalemleri, siparisler tabloları temizlendi','DELETE','2025-12-09 01:03:55'),(469,'2025-12-09 04:07:02','Yedek Admin','Ali Can müşterisi için yeni sipariş oluşturuldu (ID: 1)','CREATE','2025-12-09 01:07:02'),(470,'2025-12-09 04:08:03','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-09 01:08:03'),(471,'2025-12-09 04:08:18','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: Beklemede','UPDATE','2025-12-09 01:08:18'),(472,'2025-12-09 04:08:43','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-09 01:08:43'),(473,'2025-12-09 04:09:10','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: Beklemede','UPDATE','2025-12-09 01:09:10'),(474,'2025-12-09 04:09:17','Yedek Admin','Ali Can müşterisine ait 1 nolu siparişin yeni durumu: İptal Edildi','UPDATE','2025-12-09 01:09:17'),(475,'2025-12-09 04:09:49','Yedek Admin','Ali Can müşterisi Ali Can olarak güncellendi','UPDATE','2025-12-09 01:09:49'),(476,'2025-12-09 04:10:05','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-12-09 01:10:05'),(477,'2025-12-09 04:11:07','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 2)','CREATE','2025-12-09 01:11:07'),(478,'2025-12-09 04:11:54','Yedek Admin','Ali Can müşterisine ait 2 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-12-09 01:11:54'),(479,'2025-12-09 04:18:29','Yedek Admin','stok_hareket_kayitlari tabloları temizlendi','DELETE','2025-12-09 01:18:29'),(480,'2025-12-09 04:18:46','Yedek Admin','stok_hareketleri_sozlesmeler tabloları temizlendi','DELETE','2025-12-09 01:18:46'),(481,'2025-12-09 04:23:03','Yedek Admin','212 Man Etiketi için 10 adet stok hareketi ve ilgili gider kaydı eklendi','CREATE','2025-12-09 01:23:03'),(482,'2025-12-09 04:24:21','Yedek Admin','212 Men Şişesi için 10 adet stok hareketi eklendi','CREATE','2025-12-09 01:24:21'),(483,'2025-12-09 04:24:57','Yedek Admin','212 Men için 20 adet stok hareketi eklendi','CREATE','2025-12-09 01:24:57'),(484,'2025-12-09 04:37:51','Yedek Admin','Ahmet Kozmetik tedarikçisine Saf Su malzemesi için sipariş oluşturuldu','CREATE','2025-12-09 01:37:51'),(485,'2025-12-09 04:40:01','Yedek Admin','esans_is_emirleri, montaj_is_emirleri tabloları temizlendi','DELETE','2025-12-09 01:40:01'),(486,'2025-12-15 21:04:07','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-12-15 18:04:07'),(487,'2025-12-15 21:04:13','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-12-15 18:04:13'),(488,'2025-12-15 21:07:55','Yedek Admin','Personel giriş yaptı (E-posta/Telefon: admin2@parfum.com)','Giriş Yapıldı','2025-12-15 18:07:55');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lokasyonlar`
--

LOCK TABLES `lokasyonlar` WRITE;
/*!40000 ALTER TABLE `lokasyonlar` DISABLE KEYS */;
INSERT INTO `lokasyonlar` VALUES (1,'Merkez Depo','A1'),(2,'Merkez Depo','A2'),(3,'Merkez Depo','A3'),(4,'Satış Depo','1'),(6,'Satış Depo','Kasa Arkası Rafı');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_fotograflari`
--

LOCK TABLES `malzeme_fotograflari` WRITE;
/*!40000 ALTER TABLE `malzeme_fotograflari` DISABLE KEYS */;
INSERT INTO `malzeme_fotograflari` VALUES (7,53,'Ekran görüntüsü 2025-11-29 175954.png','assets/malzeme_fotograflari/6936fa69cd8d4_1765210729.png',1,0,'2025-12-08 16:18:49');
/*!40000 ALTER TABLE `malzeme_fotograflari` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_siparisler`
--

LOCK TABLES `malzeme_siparisler` WRITE;
/*!40000 ALTER TABLE `malzeme_siparisler` DISABLE KEYS */;
INSERT INTO `malzeme_siparisler` VALUES (15,57,'Saf Su',1,'Ahmet Kozmetik',1000.00,'2025-12-09','2025-12-28','siparis_verildi','Elimizdeki mal azaldığı için sipariş girdim.',283,'Yedek Admin','2025-12-09 01:37:51','2025-12-09 01:37:51');
/*!40000 ALTER TABLE `malzeme_siparisler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzemeler`
--

LOCK TABLES `malzemeler` WRITE;
/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (53,'212 Man Etiketi','etiket','Etiketleri dikkatli kullanalım.',90.00,'adet',1.25,'TRY',0,12,'Merkez Depo','A1',140),(54,'A1 Dış Kutu','kutu','',21.00,'adet',10.00,'TRY',0,14,'Merkez Depo','A2',21),(55,'212 Men Kapat','kapak','',1222.00,'adet',21.00,'TRY',0,30,'Merkez Depo','A2',333),(56,'212 Men Şişesi','sise','',323.00,'adet',12.00,'TRY',0,45,'Merkez Depo','A1',666),(57,'Saf Su','saf_su','',2000.00,'lt',1.50,'TL',0,2,'Merkez Depo','A1',100),(58,'Alkol','alkol','',3000.00,'lt',10.00,'USD',0,20,'Merkez Depo','A1',10000);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `montaj_is_emirleri`
--

LOCK TABLES `montaj_is_emirleri` WRITE;
/*!40000 ALTER TABLE `montaj_is_emirleri` DISABLE KEYS */;
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
INSERT INTO `montaj_is_emri_malzeme_listesi` VALUES (2,'ES002','Lavender Essence','esans',495.00,'adet'),(2,'26','White Label','etiket',22.00,'adet'),(2,'27','Plastic Cap','kapak',11.00,'adet'),(3,'ES010','Bergamot Essence','esans',50.00,'adet'),(3,'37','Ambalaj Malzemesi 2','etiket',10.00,'adet'),(1,'ES010','Bergamot Essence','esans',4300.00,'adet'),(1,'37','Ambalaj Malzemesi 2','etiket',860.00,'adet');
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
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteriler`
--

LOCK TABLES `musteriler` WRITE;
/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
INSERT INTO `musteriler` VALUES (2,'Müşteri B','0987654321','Ankara','03124445566','b@musteri.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','Standart müşteri',1,NULL,0),(5,'Ayse Kaya','23456789012','Istanbul, Kadikoy','05332345678','ayse.kaya@parfum.com','$2y$10$ZnzogQNk2dsIN6GqH7LMp.cpFLe3MqlCqXmjl3XSq.lZPdpDatWd.','VIP customer',1,'',0),(6,'Mehmet Demir','34567890123','Istanbul, Pendik','05343456789','mehmet.demir@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','New customer',1,NULL,0),(7,'Fatma Ozturk','45678901234','Istanbul, Uskudar','05354567890','fatma.ozturk@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','Frequent buyer',1,NULL,0),(8,'Ali Can','56789012345','Istanbul, Tuzla','05365678901','ali.can@parfum.com','$2y$10$NhJhyRgSMWebKp6OQn5nzeaAHXGsLUQEjMbjiJJTTjnby8Wg6wpVO','Corporate client',1,'',0),(9,'Zeynep Sahin','67890123456','Istanbul, Sisli','05376789012','zeynep.sahin@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','Premium member',1,NULL,0),(12,'Kemal Baskan','90123456789','Istanbul, Atasehir','05409012345','kemal.baskan@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','High value customer',1,NULL,0),(13,'Gulsah Arslan','01234567890','Istanbul, Bomonti','05410123456','gulsah.arslan@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','New prospect',1,NULL,0),(20,'ss','sd','sda','22','sda@f.c','$2y$10$Y3k0RehHQMq1zHEOuARJe.Z91d1mz2BkmKSluy8.8JSLBA6B2wUhO','',0,NULL,0),(21,'Musteri Isim1','22222222201','Adres 1','5553334401','musteri1@example.com','12345','Notlar 1',1,NULL,0),(22,'Musteri Isim2','22222222202','Adres 2','5553334402','musteri2@example.com','12345','Notlar 2',0,NULL,0),(23,'Musteri Isim3','22222222203','Adres 3','5553334403','musteri3@example.com','12345','Notlar 3',1,NULL,0),(24,'Musteri Isim4','22222222204','Adres 4','5553334404','musteri4@example.com','12345','Notlar 4',0,NULL,0),(25,'Musteri Isim5','22222222205','Adres 5','5553334405','musteri5@example.com','12345','Notlar 5',1,NULL,0),(26,'Musteri Isim6','22222222206','Adres 6','5553334406','musteri6@example.com','12345','Notlar 6',0,NULL,0),(27,'Musteri Isim7','22222222207','Adres 7','5553334407','musteri7@example.com','12345','Notlar 7',1,NULL,0),(28,'Musteri Isim8','22222222208','Adres 8','5553334408','musteri8@example.com','12345','Notlar 8',0,NULL,0),(29,'Musteri Isim9','22222222209','Adres 9','5553334409','musteri9@example.com','12345','Notlar 9',1,NULL,0),(30,'Musteri Isim10','22222222210','Adres 10','5553334410','musteri10@example.com','12345','Notlar 10',0,NULL,0),(31,'Musteri Isim11','22222222211','Adres 11','5553334411','musteri11@example.com','12345','Notlar 11',1,NULL,0),(32,'Musteri Isim12','22222222212','Adres 12','5553334412','musteri12@example.com','12345','Notlar 12',0,NULL,0),(33,'Musteri Isim13','22222222213','Adres 13','5553334413','musteri13@example.com','12345','Notlar 13',1,NULL,0),(34,'Musteri Isim14','22222222214','Adres 14','5553334414','musteri14@example.com','12345','Notlar 14',0,NULL,0),(35,'Musteri Isim15','22222222215','Adres 15','5553334415','musteri15@example.com','12345','Notlar 15',1,NULL,0),(36,'Musteri Isim16','22222222216','Adres 16','5553334416','musteri16@example.com','12345','Notlar 16',0,NULL,0),(37,'Musteri Isim17','22222222217','Adres 17','5553334417','musteri17@example.com','12345','Notlar 17',1,NULL,0),(38,'Musteri Isim18','22222222218','Adres 18','5553334418','musteri18@example.com','12345','Notlar 18',0,NULL,0),(39,'Musteri Isim19','22222222219','Adres 19','5553334419','musteri19@example.com','12345','Notlar 19',1,NULL,0),(40,'Musteri Isim20','22222222220','Adres 20','5553334420','musteri20@example.com','12345','Notlar 20',0,NULL,0),(41,'Musteri Isim21','22222222221','Adres 21','5553334421','musteri21@example.com','12345','Notlar 21',1,NULL,0),(42,'Musteri Isim22','22222222222','Adres 22','5553334422','musteri22@example.com','12345','Notlar 22',0,NULL,0),(43,'Musteri Isim23','22222222223','Adres 23','5553334423','musteri23@example.com','12345','Notlar 23',1,NULL,0),(44,'Musteri Isim24','22222222224','Adres 24','5553334424','musteri24@example.com','12345','Notlar 24',0,NULL,0),(45,'Musteri Isim25','22222222225','Adres 25','5553334425','musteri25@example.com','12345','Notlar 25',1,NULL,0),(46,'Musteri Isim26','22222222226','Adres 26','5553334426','musteri26@example.com','12345','Notlar 26',0,NULL,0),(47,'Musteri Isim27','22222222227','Adres 27','5553334427','musteri27@example.com','12345','Notlar 27',1,NULL,0),(48,'Musteri Isim28','22222222228','Adres 28','5553334428','musteri28@example.com','12345','Notlar 28',0,NULL,0),(49,'Musteri Isim29','22222222229','Adres 29','5553334429','musteri29@example.com','12345','Notlar 29',1,NULL,0),(50,'Musteri Isim30','22222222230','Adres 30','5553334430','musteri30@example.com','12345','Notlar 30',0,NULL,0),(51,'Musteri Isim31','22222222231','Adres 31','5553334431','musteri31@example.com','12345','Notlar 31',1,NULL,0),(52,'Musteri Isim32','22222222232','Adres 32','5553334432','musteri32@example.com','12345','Notlar 32',0,NULL,0),(53,'Musteri Isim33','22222222233','Adres 33','5553334433','musteri33@example.com','12345','Notlar 33',1,NULL,0),(54,'Musteri Isim34','22222222234','Adres 34','5553334434','musteri34@example.com','12345','Notlar 34',0,NULL,0),(55,'Musteri Isim35','22222222235','Adres 35','5553334435','musteri35@example.com','12345','Notlar 35',1,NULL,0),(56,'Musteri Isim36','22222222236','Adres 36','5553334436','musteri36@example.com','12345','Notlar 36',0,NULL,0),(57,'Musteri Isim37','22222222237','Adres 37','5553334437','musteri37@example.com','12345','Notlar 37',1,NULL,0),(58,'Musteri Isim38','22222222238','Adres 38','5553334438','musteri38@example.com','12345','Notlar 38',0,NULL,0),(59,'Musteri Isim39','22222222239','Adres 39','5553334439','musteri39@example.com','12345','Notlar 39',1,NULL,0),(60,'Musteri Isim40','22222222240','Adres 40','5553334440','musteri40@example.com','12345','Notlar 40',0,NULL,0),(61,'Musteri Isim41','22222222241','Adres 41','5553334441','musteri41@example.com','12345','Notlar 41',1,NULL,0),(62,'Musteri Isim42','22222222242','Adres 42','5553334442','musteri42@example.com','12345','Notlar 42',0,NULL,0),(63,'Musteri Isim43','22222222243','Adres 43','5553334443','musteri43@example.com','12345','Notlar 43',1,NULL,0),(64,'Musteri Isim44','22222222244','Adres 44','5553334444','musteri44@example.com','12345','Notlar 44',0,NULL,0),(65,'Musteri Isim45','22222222245','Adres 45','5553334445','musteri45@example.com','12345','Notlar 45',1,NULL,0),(66,'Musteri Isim46','22222222246','Adres 46','5553334446','musteri46@example.com','12345','Notlar 46',0,NULL,0),(67,'Musteri Isim47','22222222247','Adres 47','5553334447','musteri47@example.com','12345','Notlar 47',1,NULL,0),(68,'Musteri Isim48','22222222248','Adres 48','5553334448','musteri48@example.com','12345','Notlar 48',0,NULL,0),(69,'Musteri Isim49','22222222249','Adres 49','5553334449','musteri49@example.com','12345','Notlar 49',1,NULL,0),(70,'Musteri Isim50','22222222250','Adres 50','5553334450','musteri50@example.com','12345','Notlar 50',0,NULL,0),(71,'Musteri Isim51','22222222251','Adres 51','5553334451','musteri51@example.com','12345','Notlar 51',1,NULL,0),(72,'Musteri Isim52','22222222252','Adres 52','5553334452','musteri52@example.com','12345','Notlar 52',0,NULL,0),(73,'Musteri Isim53','22222222253','Adres 53','5553334453','musteri53@example.com','12345','Notlar 53',1,NULL,0),(74,'Musteri Isim54','22222222254','Adres 54','5553334454','musteri54@example.com','12345','Notlar 54',0,NULL,0),(75,'Musteri Isim55','22222222255','Adres 55','5553334455','musteri55@example.com','12345','Notlar 55',1,NULL,0),(76,'Musteri Isim56','22222222256','Adres 56','5553334456','musteri56@example.com','12345','Notlar 56',0,NULL,0),(77,'Musteri Isim57','22222222257','Adres 57','5553334457','musteri57@example.com','12345','Notlar 57',1,NULL,0),(78,'Musteri Isim58','22222222258','Adres 58','5553334458','musteri58@example.com','12345','Notlar 58',0,NULL,0),(79,'Musteri Isim59','22222222259','Adres 59','5553334459','musteri59@example.com','12345','Notlar 59',1,NULL,0),(80,'Musteri Isim60','22222222260','Adres 60','5553334460','musteri60@example.com','12345','Notlar 60',0,NULL,0),(81,'Musteri Isim61','22222222261','Adres 61','5553334461','musteri61@example.com','12345','Notlar 61',1,NULL,0),(82,'Musteri Isim62','22222222262','Adres 62','5553334462','musteri62@example.com','12345','Notlar 62',0,NULL,0),(83,'Musteri Isim63','22222222263','Adres 63','5553334463','musteri63@example.com','12345','Notlar 63',1,NULL,0),(84,'Musteri Isim64','22222222264','Adres 64','5553334464','musteri64@example.com','12345','Notlar 64',0,NULL,0),(85,'Musteri Isim65','22222222265','Adres 65','5553334465','musteri65@example.com','12345','Notlar 65',1,NULL,0),(86,'Musteri Isim66','22222222266','Adres 66','5553334466','musteri66@example.com','12345','Notlar 66',0,NULL,0),(87,'Musteri Isim67','22222222267','Adres 67','5553334467','musteri67@example.com','12345','Notlar 67',1,NULL,0),(88,'Musteri Isim68','22222222268','Adres 68','5553334468','musteri68@example.com','12345','Notlar 68',0,NULL,0),(89,'Musteri Isim69','22222222269','Adres 69','5553334469','musteri69@example.com','12345','Notlar 69',1,NULL,0),(90,'Musteri Isim70','22222222270','Adres 70','5553334470','musteri70@example.com','12345','Notlar 70',0,NULL,0),(91,'Musteri Isim71','22222222271','Adres 71','5553334471','musteri71@example.com','12345','Notlar 71',1,NULL,0),(92,'Musteri Isim72','22222222272','Adres 72','5553334472','musteri72@example.com','12345','Notlar 72',0,NULL,0),(93,'Musteri Isim73','22222222273','Adres 73','5553334473','musteri73@example.com','12345','Notlar 73',1,NULL,0),(94,'Musteri Isim74','22222222274','Adres 74','5553334474','musteri74@example.com','12345','Notlar 74',0,NULL,0),(95,'Musteri Isim75','22222222275','Adres 75','5553334475','musteri75@example.com','12345','Notlar 75',1,NULL,0),(96,'Musteri Isim76','22222222276','Adres 76','5553334476','musteri76@example.com','12345','Notlar 76',0,NULL,0),(97,'Musteri Isim77','22222222277','Adres 77','5553334477','musteri77@example.com','12345','Notlar 77',1,NULL,0),(98,'Musteri Isim78','22222222278','Adres 78','5553334478','musteri78@example.com','12345','Notlar 78',0,NULL,0),(99,'Musteri Isim79','22222222279','Adres 79','5553334479','musteri79@example.com','12345','Notlar 79',1,NULL,0),(100,'Musteri Isim80','22222222280','Adres 80','5553334480','musteri80@example.com','12345','Notlar 80',0,NULL,0),(101,'Musteri Isim81','22222222281','Adres 81','5553334481','musteri81@example.com','12345','Notlar 81',1,NULL,0),(102,'Musteri Isim82','22222222282','Adres 82','5553334482','musteri82@example.com','12345','Notlar 82',0,NULL,0),(103,'Musteri Isim83','22222222283','Adres 83','5553334483','musteri83@example.com','12345','Notlar 83',1,NULL,0),(104,'Musteri Isim84','22222222284','Adres 84','5553334484','musteri84@example.com','12345','Notlar 84',0,NULL,0),(105,'Musteri Isim85','22222222285','Adres 85','5553334485','musteri85@example.com','12345','Notlar 85',1,NULL,0),(106,'Musteri Isim86','22222222286','Adres 86','5553334486','musteri86@example.com','12345','Notlar 86',0,NULL,0),(107,'Musteri Isim87','22222222287','Adres 87','5553334487','musteri87@example.com','12345','Notlar 87',1,NULL,0),(108,'Musteri Isim88','22222222288','Adres 88','5553334488','musteri88@example.com','12345','Notlar 88',0,NULL,0),(109,'Musteri Isim89','22222222289','Adres 89','5553334489','musteri89@example.com','12345','Notlar 89',1,NULL,0),(110,'Musteri Isim90','22222222290','Adres 90','5553334490','musteri90@example.com','12345','Notlar 90',0,NULL,0),(111,'Musteri Isim91','22222222291','Adres 91','5553334491','musteri91@example.com','12345','Notlar 91',1,NULL,0),(112,'Musteri Isim92','22222222292','Adres 92','5553334492','musteri92@example.com','12345','Notlar 92',0,NULL,0),(113,'Musteri Isim93','22222222293','Adres 93','5553334493','musteri93@example.com','12345','Notlar 93',1,NULL,0),(114,'Musteri Isim94','22222222294','Adres 94','5553334494','musteri94@example.com','12345','Notlar 94',0,NULL,0),(115,'Musteri Isim95','22222222295','Adres 95','5553334495','musteri95@example.com','12345','Notlar 95',1,NULL,0),(116,'Musteri Isim96','22222222296','Adres 96','5553334496','musteri96@example.com','12345','Notlar 96',0,NULL,0),(117,'Musteri Isim97','22222222297','Adres 97','5553334497','musteri97@example.com','12345','Notlar 97',1,NULL,0),(118,'Musteri Isim98','22222222298','Adres 98','5553334498','musteri98@example.com','12345','Notlar 98',0,NULL,0),(119,'Musteri Isim99','22222222299','Adres 99','5553334499','musteri99@example.com','12345','Notlar 99',1,NULL,0),(120,'Musteri Isim100','22222222300','Adres 100','5553334400','musteri100@example.com','12345','Notlar 100',0,NULL,0),(124,'İDO KOZMETİK A.Ş.','12345678901','İkitelli Organize Sanayi Bölgesi, Başakşehir/İstanbul','+90 212 123 4567','info@idokozmetik.com','$2y$10$asiDxiJGBplBhCrCmhWWSehX8oChLQa19UquTNtJADmd7D9j3YcdW','Ana üretim ortağı',1,NULL,0),(125,'Parfüm Dünyası','23456789012','Bağdat Caddesi No:123, Kadıköy/İstanbul','+90 216 234 5678','bilgi@parfumdunyasi.com','$2y$10$GltAfPH6qdAvDrUhajdCoug3QEOKNCYNCqZ9PWs/oi/Bch7FO8GIq','Bayilik',1,NULL,0),(126,'Gül Esans A.Ş.','34567890123','Atasehir Organize Sanayi, Ataşehir/İstanbul','+90 212 345 6789','iletisim@gulesans.com','','Ham madde sağlayıcısı',0,NULL,0),(127,'Koku A.Ş.','45678901234','Levent Mahallesi No:45, Beşiktaş/İstanbul','+90 212 456 7890','musteri@koku.com','$2y$10$JYzr1qA9.YogVbe0jfduleQQPsCJeHTH36tH/7CJlIvlezvzE4l8q','Perakende mağaza zinciri',1,NULL,0),(128,'Lavanta Kozmetik','56789012345','Alsancak Mahallesi No:67, Konak/İzmir','+90 232 567 8901','destek@lavantakozmetik.com','','E-ticaret müşterisi',0,NULL,0),(129,'Rüzgar Kozmetik','67890123456','Etiler Mahallesi No:89, Beşiktaş/İstanbul','+90 212 678 9012','iletisim@ruzgarkozmetik.com','$2y$10$6A4tw3DiquIPkNger3NldeLg0sYTKtrOb.5Jjze0q3Mrv4cSeBt56','Bayi ortağı',1,NULL,0),(130,'Sedef Kozmetik','78901234567','Ankara Cad. No:101, Çankaya/Ankara','+90 312 789 0123','yönetim@sedefkozmetik.com','','Orta ölçekli dağıtım',0,NULL,0),(131,'Zeytin Yaprağı Kozmetik','89012345678','Alsancak Meydanı No:23, Alsancak/İzmir','+90 232 890 1234','bilgi@zeytin.com','$2y$10$ESuksYJgQpZH9MSb18Ku4OUEkjzR.REeD4pSsaF2VjRIRoX.1W.Ae','Doğal ürünler bayii',1,NULL,0),(132,'Nil Kozmetik','90123456789','Gaziosmanpaşa Mah. No:45, Samsun','+90 362 901 2345','musteri@nilkozmetik.com','','Yeni başlayan bayi',0,NULL,0),(133,'Lale Kozmetik','01234567890','Mithatpaşa Cad. No:67, Maltepe/İstanbul','+90 216 012 3456','iletisim@lalekozmetik.com','$2y$10$Lr99K7EwdQU7CrpuLxKqn.120DYuP7UvZyrYMciEDZ4Y1iHKxaQ/.','Kurumsal satış',1,NULL,0),(134,'Menekşe Kozmetik','12345678902','Alsancak Mah. No:89, Bornova/İzmir','+90 232 123 4567','siparis@menekse.com','','Toptan satış',0,NULL,0),(135,'Papatya Kozmetik','23456789013','Atatürk Bulvarı No:101, Alsancak/İzmir','+90 232 234 5678','info@papatyakozmetik.com','$2y$10$O6Jcz2FUOCYN7AtKYihFq.D2763RvxnL0uOJgqHH0of7XuP74MWLS','Online platform',1,NULL,0),(136,'Gülün Adı','34567890124','Kordonboyu No:23, Alsancak/İzmir','+90 232 345 6789','iletisim@gulunadi.com','','Kozmetik mağazası',0,NULL,0),(137,'Karanfil Kozmetik','45678901235','Cumhuriyet Cad. No:45, Meram/Konya','+90 332 456 7890','destek@karanfikozmetik.com','$2y$10$emz1dP4IpJlCeuHTLMa/p.kBpJ50a28fpda5LJfv0KKBhvK5Zp0k6','Yerel bayi',1,NULL,0),(138,'Beyaz Zambak','56789012346','Barbarossa Cad. No:67, Bornova/İzmir','+90 232 567 8901','yönetim@beyazzambak.com','','Doğal ürünler',0,NULL,0),(139,'Sarı Menekşe','67890123457','Kocatepe Mah. No:89, Kocaeli','+90 262 678 9012','iletisim@sarimenekse.com','$2y$10$xnJaKwrxzNtBWVrIWu0Uf.6/ADpcqepuGGfOVGY18VKPxkdx7/Ysq','Organik ürünler',1,NULL,0),(140,'Mor Menekşe','78901234568','Cumhuriyet Cad. No:101, Balçova/İzmir','+90 232 789 0123','bilgi@mormenekse.com','','Özel üretim',0,NULL,0),(141,'Çiçek Kozmetik','89012345679','Atatürk Mah. No:23, Bursa','+90 224 890 1234','musteri@cicekkozmetik.com','$2y$10$aofGZMDvCoaQSASiJnXOA.kH/iao0BcGOKrs4hqmbd3/LybV6NrAy','Bayi ortağı',1,NULL,0),(143,'Misket Kozmetik','01234567891','Gazi Mah. No:67, Samsun','+90 362 012 3456','info@misketkozmetik.com','$2y$10$PyxwQux3ZVexC.Sb65MOHOK/XCA1Eo8qpFS/YI5PupS21GHfOp9ei','Perakende satış',1,NULL,0),(144,'Nergis Kozmetik','12345678903','Baraj Yolu No:89, Adana','+90 322 123 4567','destek@nergiskozmetik.com','','Doğal ürünler',0,NULL,0),(145,'Zambak Kozmetik','23456789014','Anadolu Cad. No:101, Kocaeli','+90 262 234 5678','iletisim@zambakkozmetik.com','$2y$10$QUlwoxYWtbOob4j7SY8Ob.5bA/zYDfMHcvlbs4QOGNFGW9fvsseLy','Kurumsal satış',1,NULL,0),(146,'Lale Bahçesi','34567890125','Merkez Mah. No:23, Antalya','+90 242 345 6789','bilgi@lalebahcesi.com','','Turistik bölge',0,NULL,0),(147,'Gül Şehri','45678901236','Kültür Mah. No:45, Bursa','+90 224 456 7890','yönetim@gulsehri.com','$2y$10$gwUYPfWFxzuDmQixMCWP3.dSFDoMrlyMZDhLAIM6yScqhKw.xdHM.','Yerel üretim',1,NULL,0),(148,'Kırlangıç Kozmetik','56789012347','Sakarya Mah. No:67, Kocaeli','+90 262 567 8901','info@kirlangickozmetik.com','','Yeni bayi',0,NULL,0),(149,'Kartal Kozmetik','67890123458','Atatürk Mah. No:89, Kartal/İstanbul','+90 216 678 9012','iletisim@kartalkozmetik.com','$2y$10$GhsAp4XOs5TpEJp8KQgndeqbfhY/tHFsyxdhzDgkIh8ptWUXUw5xm','Bölgesel satış',1,NULL,0),(150,'Kumru Kozmetik','78901234569','Gazi Mah. No:101, Ankara','+90 312 789 0123','destek@kumrukozmetik.com','','Toptan satış',0,NULL,0),(151,'Mavi Kozmetik','89012345670','Baraj Yolu No:23, Mersin','+90 324 890 1234','iletisim@makozmetik.com','$2y$10$YSO74ctoKZBGcbvloHDBlO8X/GaYN0ymR09EysCixUutKjltXD1fO','Online satış',1,NULL,0),(152,'Kırmızı Gül','90123456781','Atakent Mah. No:45, Gebze/Kocaeli','+90 262 901 2345','bilgi@kirmizigul.com','','Marka ortağı',0,NULL,0),(153,'Sarı Lale','01234567892','Cumhuriyet Mah. No:67, Adana','+90 322 012 3456','yönetim@sarilale.com','$2y$10$2mH7F2MDP/mp0RLMCiAun.UoJ0M/Lkh91y/Y/KyC4uDP5cdQUMIgC','Yurt dışı satış',1,NULL,0),(160,'Özhan Aydın','3218391293','Kocaeli Fani Sokak','05003001010','ozhan.aydin@gmail.com','$2y$10$y/A62g0nQMwzjOHzt7U2OOzX2jIymfYLR.k0cQ5Fl2rfhBPhSmGhu','Tanıdık bildik birisidir.',1,'05003001012',0);
/*!40000 ALTER TABLE `musteriler` ENABLE KEYS */;
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
INSERT INTO `personel_avanslar` VALUES (1,294,'Ahmet Yıldırım',10000.00,'2025-12-09',2025,12,'Nakit','',283,'Yedek Admin','2025-12-09 00:03:23',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_izinleri`
--

LOCK TABLES `personel_izinleri` WRITE;
/*!40000 ALTER TABLE `personel_izinleri` DISABLE KEYS */;
INSERT INTO `personel_izinleri` VALUES (60,1,'page:view:mrp_planlama'),(59,160,'action:ayarlar:backup'),(58,160,'action:ayarlar:currency'),(57,160,'action:musteriler:edit'),(54,160,'page:view:ayarlar'),(56,160,'page:view:excele_aktar'),(52,160,'page:view:musteriler'),(51,160,'page:view:navigation'),(53,160,'page:view:personeller'),(55,160,'page:view:yedekleme'),(68,294,'action:tedarikciler:create'),(66,294,'page:view:navigation'),(67,294,'page:view:tedarikciler');
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
INSERT INTO `personel_maas_odemeleri` VALUES (1,294,'Ahmet Yıldırım',2025,12,60000.00,10000.00,50000.00,'2025-12-09','Havale','',283,'Yedek Admin','2025-12-09 00:03:53',35);
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
) ENGINE=InnoDB AUTO_INCREMENT=295 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personeller`
--

LOCK TABLES `personeller` WRITE;
/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Yönetim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(294,'Ahmet Yıldırım','','1980-01-01','2001-01-01','Müdür Yardımcısı','Yönetim Departmanı','ahmet.yildirim@gmail.com','05515515151','','','$2y$10$3dY.S5AAYD5Mt1elJTkVO.uFEyiYA1MAuA1gl7iSbSvpfSC4SSpMa','',1,60000.00);
/*!40000 ALTER TABLE `personeller` ENABLE KEYS */;
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
  `toplam_tutar` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparis_kalemleri`
--

LOCK TABLES `siparis_kalemleri` WRITE;
/*!40000 ALTER TABLE `siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `siparis_kalemleri` VALUES (1,36,'212 Men',10,'adet',200.00,2000.00),(1,37,'Issey Miyaki',15,'adet',500.00,7500.00),(2,36,'212 Men',100,'adet',200.00,20000.00),(2,37,'Issey Miyaki',40,'adet',500.00,20000.00);
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
  PRIMARY KEY (`siparis_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparisler`
--

LOCK TABLES `siparisler` WRITE;
/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
INSERT INTO `siparisler` VALUES (1,8,'Ali Can','2025-12-09 04:07:02','iptal_edildi',25,'Personel: Yedek Admin',NULL,NULL,NULL,'Çok acil bir sipariş , erkenden teslim edelim.'),(2,8,'Ali Can','2025-12-09 04:11:07','onaylandi',140,'Ali Can',283,'Yedek Admin','2025-12-09 04:11:54','Abi sipariş acil, erkenden çıkarsan sevinirim.');
/*!40000 ALTER TABLE `siparisler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareket_kayitlari`
--

LOCK TABLES `stok_hareket_kayitlari` WRITE;
/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
INSERT INTO `stok_hareket_kayitlari` VALUES (1,'2025-12-09 04:23:03','malzeme','53','212 Man Etiketi','adet',10.00,'cikis','fire','Merkez Depo','A1','','',NULL,NULL,NULL,'Üretimde fire oldu.',283,'Yedek Admin','',NULL),(2,'2025-12-09 04:24:21','malzeme','56','212 Men Şişesi','adet',10.00,'cikis','sayim_eksigi','Merkez Depo','A1','','',NULL,NULL,NULL,'Sayımda eksik çıktı.',283,'Yedek Admin','',NULL),(3,'2025-12-09 04:24:57','urun','36','212 Men','adet',20.00,'giris','sayim_fazlasi','Merkez Depo','A1','','',NULL,NULL,NULL,'Merkez depoyu sayarken arkalarda bir kutu bulduk.',283,'Yedek Admin','',NULL),(4,'2025-12-09 04:26:07','urun','36','212 Men','adet',140.00,'cikis','transfer','Merkez Depo','A1','','',NULL,NULL,NULL,'Stok transferi - Kaynak: Merkez Depo/A1 -> Hedef: Merkez Depo/A2',283,'Yedek Admin',NULL,NULL),(5,'2025-12-09 04:26:07','urun','36','212 Men','adet',140.00,'giris','transfer','Merkez Depo','A2','','',NULL,NULL,NULL,'Stok transferi - Kaynak: Merkez Depo/A1 -> Hedef: Merkez Depo/A2',283,'Yedek Admin',NULL,NULL),(6,'2025-12-09 04:28:56','malzeme','57','Saf Su','lt',1000.00,'giris','mal_kabul','Merkez Depo','A1',NULL,'',NULL,NULL,NULL,'Mal kabul yaptık. [Sozlesme ID: 1]',283,'Yedek Admin','Ahmet Kozmetik',1);
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
INSERT INTO `stok_hareketleri_sozlesmeler` VALUES (6,1,57,1000.00,'2025-12-09 01:28:56',2.00,'TL','Ahmet Kozmetik',1,'2025-12-02','2025-12-21');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tanklar`
--

LOCK TABLES `tanklar` WRITE;
/*!40000 ALTER TABLE `tanklar` DISABLE KEYS */;
INSERT INTO `tanklar` VALUES (1,'Tank1','Birinci Tankımız','Arızalıdır, bakıma girecektir. Dikkatli olunuz.',100.00),(2,'Tank2','Eski Tank','',200.00),(3,'Tank3','Beşliyüz Tank','Yeni aldığımız tank.',500.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tedarikciler`
--

LOCK TABLES `tedarikciler` WRITE;
/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (1,'Ahmet Kozmetik','','Kocaeli başiskele','05416144274','ahmet.kozmetik@gmail.com','Ahmet Yıldırım','','');
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_agaci`
--

LOCK TABLES `urun_agaci` WRITE;
/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (5,5,'Gül Kokusu Esansı','malzeme','58','Alkol',0.40,'esans'),(6,5,'Gül Kokusu Esansı','malzeme','57','Saf Su',0.60,'esans'),(7,36,'212 Men','esans','ES010','Gül Kokusu Esansı',1.00,'urun'),(8,36,'212 Men','etiket','53','212 Man Etiketi',2.00,'urun'),(9,36,'212 Men','kapak','55','212 Men Kapat',1.00,'urun'),(10,36,'212 Men','sise','56','212 Men Şişesi',1.00,'urun'),(11,36,'212 Men','kutu','54','A1 Dış Kutu',1.00,'urun');
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_fotograflari`
--

LOCK TABLES `urun_fotograflari` WRITE;
/*!40000 ALTER TABLE `urun_fotograflari` DISABLE KEYS */;
INSERT INTO `urun_fotograflari` VALUES (20,36,'Ekran görüntüsü 2025-11-29 170053.png','assets/urun_fotograflari/69370c20ae6b6_1765215264.png',1,1,'2025-12-08 17:34:24'),(21,36,'Ekran görüntüsü 2025-11-29 170116.png','assets/urun_fotograflari/69370c2458c12_1765215268.png',3,0,'2025-12-08 17:34:28'),(22,36,'Ekran görüntüsü 2025-11-29 170142.png','assets/urun_fotograflari/69370c2829868_1765215272.png',2,0,'2025-12-08 17:34:32');
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
  `alis_fiyati` decimal(10,2) DEFAULT 0.00,
  `kritik_stok_seviyesi` int(11) DEFAULT 0,
  `depo` varchar(255) DEFAULT NULL,
  `raf` varchar(100) DEFAULT NULL,
  `urun_tipi` enum('uretilen','hazir_alinan') DEFAULT 'uretilen',
  `son_maliyet` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`urun_kodu`),
  UNIQUE KEY `unique_urun_ismi` (`urun_ismi`),
  UNIQUE KEY `urun_kodu` (`urun_kodu`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

LOCK TABLES `urunler` WRITE;
/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (36,'212 Men','Keskin bir kokusu vardır...',140,'adet',200.00,0.00,300,'Merkez Depo','A2','uretilen',NULL),(37,'Issey Miyaki','',400,'adet',500.00,300.00,130,'Satış Depo','Kasa Arkası Rafı','hazir_alinan',NULL);
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

-- Dump completed on 2025-12-16  2:02:09

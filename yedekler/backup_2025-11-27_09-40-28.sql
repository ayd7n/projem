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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

LOCK TABLES `ayarlar` WRITE;
/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.4270'),(2,'euro_kuru','49.0070'),(3,'son_otomatik_yedek_tarihi','2025-11-27 08:16:54'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170');
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cerceve_sozlesmeler`
--

LOCK TABLES `cerceve_sozlesmeler` WRITE;
/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (33,31,'Tedarikci Isim25',23,'Perfume 3',489.45,'USD',7462,0,'2025-01-20','2025-08-18','Sistem','2025-11-21 02:38:14','Otomatik oluşturulan test sözleşmesi #3',5),(34,31,'Tedarikci Isim25',31,'Medium Box',818.99,'TL',3699,257,'2025-01-31','2025-11-19','Sistem','2025-11-21 02:38:14','Otomatik oluşturulan test sözleşmesi #4',3),(35,26,'Tedarikci Isim20',24,'Perfume 4',592.16,'USD',5589,0,'2025-02-04','2025-11-12','Sistem','2025-11-21 02:38:14','Otomatik oluşturulan test sözleşmesi #5',5),(37,63,'Tedarikci Isim57',23,'Perfume 3',858.19,'EUR',2713,232,'2025-02-14','2025-12-19','Sistem','2025-11-21 02:38:14','Otomatik oluşturulan test sözleşmesi #7',3),(38,23,'Tedarikci Isim17',28,'Perfume 8',837.80,'USD',8984,0,'2025-04-17','2026-02-28','Sistem','2025-11-21 02:38:14','Otomatik oluşturulan test sözleşmesi #8',3),(39,60,'Tedarikci Isim54',24,'Perfume 4',521.30,'EUR',9764,0,'2025-04-17','2025-10-18','Sistem','2025-11-21 02:38:14','Otomatik oluşturulan test sözleşmesi #9',2),(44,106,'Tedarikci Isim100',31,'Medium Box',1.00,'EUR',100,20,'2025-11-01','2025-11-30','Admin User','2025-11-21 10:26:39','',1),(45,2,'Tedarikçi B',22,'Perfume 2',15.00,'TL',500,0,'2025-11-01','2026-11-01','Admin User','2025-11-21 10:30:50','CURL BUG TEST 1',3),(47,7,'Tedarikci Isim1',31,'Medium Box',10.00,'TL',100,30,'2025-11-09','2025-11-30','Admin User','2025-11-21 10:46:29','',1),(48,3,'Tedarikçi Abdi',37,'Ambalaj Malzemesi 2',30.00,'TL',200,40,'2025-11-18','2025-11-30','Admin User','2025-11-26 23:01:39','',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esans_is_emirleri`
--

LOCK TABLES `esans_is_emirleri` WRITE;
/*!40000 ALTER TABLE `esans_is_emirleri` DISABLE KEYS */;
INSERT INTO `esans_is_emirleri` VALUES (3,'2025-11-24','Admin User','ES010','Bergamot Essence','TK005','C1 - Alcohol Tank',6.00,'ml','2025-11-24',30,'2025-12-24','2025-11-24','2025-11-24',' ','tamamlandi',3.00,3.00),(4,'2025-11-24','Admin User','ES010','Bergamot Essence','TK002','A2 - Essential Oil Tank',3.00,'ml','2025-11-24',30,'2025-12-24',NULL,NULL,'','olusturuldu',0.00,0.00);
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
INSERT INTO `esans_is_emri_malzeme_listesi` VALUES (3,31,'Medium Box','malzeme',6.00,'ml'),(3,37,'Ambalaj Malzemesi 2','malzeme',6.00,'ml'),(4,31,'Medium Box','malzeme',3.00,'ml'),(4,37,'Ambalaj Malzemesi 2','malzeme',3.00,'ml');
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esanslar`
--

LOCK TABLES `esanslar` WRITE;
/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (15,'ES001','Form Test','Form test',209.50,'lt',30.00,'TANK001','Esans Tankı 1'),(16,'ES002','Lavender Essence','Lavender scented natural essence',80.25,'lt',25.50,'TANK001','Esans Tankı 1'),(17,'ES003','Vanilla Essence','Vanilla scented natural essence',75.00,'lt',20.00,'TANK001','Esans Tankı 1'),(18,'ES004','Orange Essence','Orange scented natural essence',90.00,'ml',15.75,'TANK001','Esans Tankı 1'),(19,'ES005','Musk Essence','Musk scented natural essence',120.00,'lt',35.00,'TANK001','Esans Tankı 1'),(20,'ES006','Flower Essence','Flower scented natural essence',85.50,'lt',28.00,'TK001','A1 - Aromatic Tank'),(24,'ES010','Bergamot Essence','Bergamot scented natural essence',142.00,'ml',30.00,'TANK001','Esans Tankı 1'),(27,'ES015','Test Essence with Tank Updated','Test essence with tank updated',75.00,'ml',20.00,'TANK002','Esans Tankı 2');
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gider_yonetimi`
--

LOCK TABLES `gider_yonetimi` WRITE;
/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (1,'2025-11-21',500.00,'Personel Gideri','Deneme...',1,'Admin User','A120','Nakit','Seyrek İK'),(6,'2025-02-21',300.00,'Malzeme Gideri','Medium Box için 30 adet ön ödeme',1,'Admin User','','Diğer','Tedarikci Isim1'),(17,'2025-11-21',236.56,'Fire Gideri','Fire kaydı - 10 evo - 1 adet (30)',1,'Admin User','Fire_Kaydi_327','Diğer','İç Gider'),(19,'2025-11-21',21.88,'Fire Gideri','Fire kaydı - Bergamot Essence - 1 ml (ES010)',1,'Admin User','Fire_Kaydi_329','Diğer','İç Gider'),(22,'2025-11-21',88.80,'Fire Gideri','Fire kaydı - Ambalaj Malzemesi 2 - 10 gr (37)',1,'Admin User','Fire_Kaydi_334','Diğer','İç Gider'),(23,'2025-11-26',1200.00,'Malzeme Gideri','Ambalaj Malzemesi 2 için 40 adet ön ödeme',1,'Admin User',NULL,'Diğer','Tedarikçi Abdi');
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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `is_merkezleri`
--

LOCK TABLES `is_merkezleri` WRITE;
/*!40000 ALTER TABLE `is_merkezleri` DISABLE KEYS */;
INSERT INTO `is_merkezleri` VALUES (11,'Is Merkezi 1','Ana uretim bolumüdür'),(12,'Is Merkezi 2','Kimyasal karisimlarin hazirlandigi alan'),(13,'Is Merkezi 3','Urunlerin paketlendigi bolum'),(14,'Is Merkezi 4','Urun kalitesinin kontrol edildigi alan'),(15,'Is Merkezi 5','Hammadde stoklarinin tutuldugu depo'),(16,'Is Merkezi 6','Hazir urunlerin tutuldugu depo'),(17,'Is Merkezi 7','Esans karisimlarinin yapildigi oda'),(18,'Is Merkezi 8','Urun testlerinin yapildigi laboratuar'),(19,'Is Merkezi 9','Urun ambalajlama islemlerinin yapildigi alan'),(22,'Ana Uretim Alani','Ana uretim bolumu'),(23,'Kimyasal Hazirlama','Kimyasal karisimlarin hazirlandigi alan'),(24,'Paketleme Bolumu','Urunlerin paketlendigi bolum'),(25,'Kalite Kontrol','Urun kalitesinin kontrol edildigi alan'),(26,'Hammadde Deposu','Hammadde stoklarinin tutuldugu depo'),(27,'Mamul Deposu','Hazir urunlerin tutuldugu depo'),(28,'Karistirma Odasi','Esans karisimlarinin yapildigi oda'),(29,'Test Laboratuari','Urun testlerinin yapildigi laboratuar'),(30,'Ambalajlama Alani','Urun ambalajlama islemlerinin yapildigi alan'),(31,'Sevkiyat Bolumu','Hazirlanan siparislerin sevkiyata hazirlandigi bolum');
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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tablosu`
--

LOCK TABLES `log_tablosu` WRITE;
/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2025-11-26 09:44:47','test_kullanici','Bu bir test log kaydıdır.','CREATE','2025-11-26 06:44:47'),(2,'2025-11-26 09:45:40','test_kullanici','Test Ürün sisteme eklendi','CREATE','2025-11-26 06:45:40'),(3,'2025-11-26 09:45:40','test_kullanici','Test Ürün güncellendi','UPDATE','2025-11-26 06:45:40'),(4,'2025-11-26 09:45:40','test_kullanici','Test Ürün sistemden silindi','DELETE','2025-11-26 06:45:40'),(5,'2025-11-26 09:46:02','sistem','Melisa Rose Parfümü ürünü sisteme eklendi','CREATE','2025-11-26 06:46:02'),(6,'2025-11-26 09:46:02','sistem','Melisa Rose Parfümü ürünü Kır Çiçeği Parfümü olarak güncellendi','UPDATE','2025-11-26 06:46:02'),(7,'2025-11-26 09:46:02','sistem','Kır Çiçeği Parfümü ürünü sistemden silindi','DELETE','2025-11-26 06:46:02'),(8,'2025-11-26 09:46:02','sistem','Ahmet Yılmaz müşterisi sisteme eklendi','CREATE','2025-11-26 06:46:02'),(9,'2025-11-26 09:46:02','sistem','ABC Tedarik tedarikçisi sisteme eklendi','CREATE','2025-11-26 06:46:02'),(10,'2025-11-26 11:03:18','Admin User','Deneme Tankı adlı tank sisteme eklendi','CREATE','2025-11-26 08:03:18'),(11,'2025-11-26 11:06:21','Admin User','Güncellenmiş Tank adlı tank Güncellenmiş Tank olarak güncellendi','UPDATE','2025-11-26 08:06:21'),(12,'2025-11-26 11:06:35','Admin User','Bilinmeyen Tank adlı tank silindi','DELETE','2025-11-26 08:06:35'),(13,'2025-11-26 11:10:03','Admin User','Test Ürün ürün ağacına Test Malzeme bileşeni eklendi','CREATE','2025-11-26 08:10:03'),(14,'2025-11-26 11:10:21','Admin User','Test Ürün ürün ağacındaki Test Malzeme bileşeni Güncellenmiş Malzeme olarak güncellendi','UPDATE','2025-11-26 08:10:21'),(15,'2025-11-26 11:10:32','Admin User','Test Ürün ürün ağacından Güncellenmiş Malzeme bileşeni silindi','DELETE','2025-11-26 08:10:32'),(16,'2025-11-26 14:47:21','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:47:21'),(17,'2025-11-26 14:47:28','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:47:28'),(18,'2025-11-26 14:47:32','unknown','unknown oturumu kapattı (ID: unknown)','','2025-11-26 11:47:32'),(19,'2025-11-26 14:48:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 11:48:04'),(20,'2025-11-26 14:48:10','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 11:48:10'),(21,'2025-11-26 14:48:36','Ayse Kaya','Müşteri giriş yaptı (E-posta/Telefon: ayse.kaya@parfum.com)','','2025-11-26 11:48:36'),(22,'2025-11-26 14:56:59','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','','2025-11-26 11:56:59'),(23,'2025-11-26 14:58:03','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 3)','CREATE','2025-11-26 11:58:03'),(24,'2025-11-26 14:58:22','Ali Can','musteri oturumu kapattı (ID: 8)','','2025-11-26 11:58:22'),(25,'2025-11-26 16:20:13','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:20:13'),(26,'2025-11-26 16:20:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:20:14'),(27,'2025-11-26 16:21:21','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:21:21'),(28,'2025-11-26 16:21:22','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:21:22'),(29,'2025-11-26 16:45:37','Admin User','personel oturumu kapattı (ID: 1)','','2025-11-26 13:45:37'),(30,'2025-11-26 16:45:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','','2025-11-26 13:45:39'),(31,'2025-11-26 16:47:07','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 13:47:07'),(32,'2025-11-26 16:47:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 13:47:09'),(33,'2025-11-26 17:05:50','test_kullanici','Bu bir test logudur','TEST','2025-11-26 14:05:50'),(34,'2025-11-26 21:12:54','Admin User','Telegram ayarları güncellendi','UPDATE','2025-11-26 18:12:54'),(35,'2025-11-26 21:15:28','Admin User','Telegram ayarları güncellendi','UPDATE','2025-11-26 18:15:28'),(36,'2025-11-26 21:15:37','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:15:37'),(37,'2025-11-26 21:15:43','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:15:43'),(38,'2025-11-26 21:16:18','Admin User','Tedarikci Isim10 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:18'),(39,'2025-11-26 21:16:29','Admin User','Tedarikci Isim100 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:29'),(40,'2025-11-26 21:16:41','Admin User','Tedarikci Isim16 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:16:41'),(41,'2025-11-26 21:16:58','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:16:58'),(42,'2025-11-26 21:17:05','Ali Can','Müşteri giriş yaptı (E-posta/Telefon: ali.can@parfum.com)','Giriş Yapıldı','2025-11-26 18:17:05'),(43,'2025-11-26 21:17:43','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 4)','CREATE','2025-11-26 18:17:43'),(44,'2025-11-26 21:20:24','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 5)','CREATE','2025-11-26 18:20:24'),(45,'2025-11-26 21:20:49','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 6)','CREATE','2025-11-26 18:20:49'),(46,'2025-11-26 21:22:27','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 7)','CREATE','2025-11-26 18:22:27'),(47,'2025-11-26 21:23:37','Ali Can','Ali Can müşterisi tarafından sipariş oluşturuldu (ID: 8)','CREATE','2025-11-26 18:23:37'),(48,'2025-11-26 21:23:53','Ali Can','musteri oturumu kapattı (ID: 8)','Çıkış Yapıldı','2025-11-26 18:23:53'),(49,'2025-11-26 21:23:58','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:23:58'),(50,'2025-11-26 21:26:10','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:26:10'),(51,'2025-11-26 21:26:14','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:26:14'),(52,'2025-11-26 21:27:33','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 18:27:33'),(53,'2025-11-26 21:27:37','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 18:27:37'),(54,'2025-11-26 21:32:33','Admin User','Ali Can müşterisine ait 6 nolu sipariş iptal_edildi durumundan iptal_edildi durumuna güncellendi','UPDATE','2025-11-26 18:32:33'),(55,'2025-11-26 21:32:48','Admin User','Ali Can müşterisine ait 5 nolu sipariş onaylandi durumundan onaylandi durumuna güncellendi','UPDATE','2025-11-26 18:32:48'),(56,'2025-11-26 21:34:22','Admin User','Ali Can müşterisine ait 5 nolu siparişin durumu onaylandi oldu','UPDATE','2025-11-26 18:34:22'),(57,'2025-11-26 21:35:33','Admin User','Ali Can müşterisine ait 7 nolu sipariş silindi','DELETE','2025-11-26 18:35:33'),(58,'2025-11-26 21:35:42','Admin User','Ali Can müşterisine ait 8 nolu siparişin yeni durumu: Beklemede','UPDATE','2025-11-26 18:35:42'),(59,'2025-11-26 21:35:50','Admin User','Ali Can müşterisine ait 3 nolu siparişin yeni durumu: Onaylandı','UPDATE','2025-11-26 18:35:50'),(60,'2025-11-26 21:38:01','Admin User','Tedarikci Isim13 tedarikçisi sistemden silindi','DELETE','2025-11-26 18:38:01'),(61,'2025-11-26 22:15:40','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-26 19:15:40'),(62,'2025-11-26 22:15:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 19:15:44'),(63,'2025-11-26 22:55:48','Admin User','Tedarikçi Abdi tedarikçisine ait Ambalaj Malzemesi 2 malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2025-11-26 19:55:48'),(64,'2025-11-26 23:01:39','Admin User','Tedarikçi Abdi tedarikçisine Ambalaj Malzemesi 2 malzemesi için çerçeve sözleşme eklendi','CREATE','2025-11-26 20:01:39'),(65,'2025-11-26 23:22:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-26 20:22:36'),(66,'2025-11-27 08:16:34','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2025-11-27 05:16:34'),(67,'2025-11-27 08:16:39','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2025-11-27 05:16:39');
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lokasyonlar`
--

LOCK TABLES `lokasyonlar` WRITE;
/*!40000 ALTER TABLE `lokasyonlar` DISABLE KEYS */;
INSERT INTO `lokasyonlar` VALUES (2,'Ana Depo','R001'),(3,'Main Warehouse','R002'),(4,'Main Warehouse','R003'),(5,'Main Warehouse','R004'),(6,'Main Warehouse','R005'),(7,'Main Warehouse','R006'),(8,'Main Warehouse','R007'),(9,'Main Warehouse','R008'),(11,'Main Warehouse','R010'),(20,'Main Warehouse','R009'),(22,'Yeni Depo','Raf 3'),(23,'Marmara Depo','K8');
/*!40000 ALTER TABLE `lokasyonlar` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzemeler`
--

LOCK TABLES `malzemeler` WRITE;
/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (22,'Perfume 2','diger',NULL,481.00,'adet',27.47,'TRY',0,0,'Ana Depo','R001',25),(23,'Perfume 3','diger',NULL,485.00,'adet',140.13,'TRY',0,0,'Ana Depo','R001',32),(24,'Perfume 4','diger',NULL,307.00,'adet',146.51,'TRY',0,0,'Ana Depo','R001',26),(25,'Perfume 5','diger',NULL,379.00,'adet',69.39,'TRY',0,0,'Ana Depo','R001',17),(26,'Perfume 6','diger',NULL,55.00,'adet',61.35,'TRY',0,0,'Ana Depo','R001',32),(27,'Perfume 7','diger',NULL,145.00,'adet',69.17,'TRY',0,0,'Ana Depo','R001',33),(28,'Perfume 8','diger',NULL,163.00,'adet',125.87,'TRY',0,0,'Ana Depo','R001',38),(31,'Medium Box','diger','null',-6.00,'adet',0.00,'TL',0,0,'Ana Depo','R001',0),(37,'Ambalaj Malzemesi 2','etiket','Etiket',162.00,'gr',21.79,'TL',0,1,'Main Warehouse','R009',100);
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
/*!40000 ALTER TABLE `montaj_is_emri_malzeme_listesi` ENABLE KEYS */;
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
  PRIMARY KEY (`musteri_id`),
  UNIQUE KEY `musteri_id` (`musteri_id`),
  UNIQUE KEY `musteri_adi` (`musteri_adi`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteriler`
--

LOCK TABLES `musteriler` WRITE;
/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
INSERT INTO `musteriler` VALUES (2,'Müşteri B','0987654321','Ankara','03124445566','b@musteri.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','Standart müşteri',1),(5,'Ayse Kaya','23456789012','Istanbul, Kadikoy','05332345678','ayse.kaya@parfum.com','$2y$10$ZnzogQNk2dsIN6GqH7LMp.cpFLe3MqlCqXmjl3XSq.lZPdpDatWd.','VIP customer',1),(6,'Mehmet Demir','34567890123','Istanbul, Pendik','05343456789','mehmet.demir@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','New customer',1),(7,'Fatma Ozturk','45678901234','Istanbul, Uskudar','05354567890','fatma.ozturk@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','Frequent buyer',1),(8,'Ali Can','56789012345','Istanbul, Tuzla','05365678901','ali.can@parfum.com','$2y$10$NhJhyRgSMWebKp6OQn5nzeaAHXGsLUQEjMbjiJJTTjnby8Wg6wpVO','Corporate client',1),(9,'Zeynep Sahin','67890123456','Istanbul, Sisli','05376789012','zeynep.sahin@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','Premium member',1),(11,'Ebru Gunes','89012345678','Istanbul, Maslak','05398901234','ebru.gunes@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','Online customer',1),(12,'Kemal Baskan','90123456789','Istanbul, Atasehir','05409012345','kemal.baskan@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','High value customer',1),(13,'Gulsah Arslan','01234567890','Istanbul, Bomonti','05410123456','gulsah.arslan@parfum.com','$2y$10$rDzR3MAzf7UBv6WV3J/mI.5g2uOAo0itjEdmBhsW1BvSLBrC.BWRK','New prospect',1),(20,'ss','sd','sda','22','sda@f.c','$2y$10$Y3k0RehHQMq1zHEOuARJe.Z91d1mz2BkmKSluy8.8JSLBA6B2wUhO','',0),(21,'Musteri Isim1','22222222201','Adres 1','5553334401','musteri1@example.com','12345','Notlar 1',1),(22,'Musteri Isim2','22222222202','Adres 2','5553334402','musteri2@example.com','12345','Notlar 2',0),(23,'Musteri Isim3','22222222203','Adres 3','5553334403','musteri3@example.com','12345','Notlar 3',1),(24,'Musteri Isim4','22222222204','Adres 4','5553334404','musteri4@example.com','12345','Notlar 4',0),(25,'Musteri Isim5','22222222205','Adres 5','5553334405','musteri5@example.com','12345','Notlar 5',1),(26,'Musteri Isim6','22222222206','Adres 6','5553334406','musteri6@example.com','12345','Notlar 6',0),(27,'Musteri Isim7','22222222207','Adres 7','5553334407','musteri7@example.com','12345','Notlar 7',1),(28,'Musteri Isim8','22222222208','Adres 8','5553334408','musteri8@example.com','12345','Notlar 8',0),(29,'Musteri Isim9','22222222209','Adres 9','5553334409','musteri9@example.com','12345','Notlar 9',1),(30,'Musteri Isim10','22222222210','Adres 10','5553334410','musteri10@example.com','12345','Notlar 10',0),(31,'Musteri Isim11','22222222211','Adres 11','5553334411','musteri11@example.com','12345','Notlar 11',1),(32,'Musteri Isim12','22222222212','Adres 12','5553334412','musteri12@example.com','12345','Notlar 12',0),(33,'Musteri Isim13','22222222213','Adres 13','5553334413','musteri13@example.com','12345','Notlar 13',1),(34,'Musteri Isim14','22222222214','Adres 14','5553334414','musteri14@example.com','12345','Notlar 14',0),(35,'Musteri Isim15','22222222215','Adres 15','5553334415','musteri15@example.com','12345','Notlar 15',1),(36,'Musteri Isim16','22222222216','Adres 16','5553334416','musteri16@example.com','12345','Notlar 16',0),(37,'Musteri Isim17','22222222217','Adres 17','5553334417','musteri17@example.com','12345','Notlar 17',1),(38,'Musteri Isim18','22222222218','Adres 18','5553334418','musteri18@example.com','12345','Notlar 18',0),(39,'Musteri Isim19','22222222219','Adres 19','5553334419','musteri19@example.com','12345','Notlar 19',1),(40,'Musteri Isim20','22222222220','Adres 20','5553334420','musteri20@example.com','12345','Notlar 20',0),(41,'Musteri Isim21','22222222221','Adres 21','5553334421','musteri21@example.com','12345','Notlar 21',1),(42,'Musteri Isim22','22222222222','Adres 22','5553334422','musteri22@example.com','12345','Notlar 22',0),(43,'Musteri Isim23','22222222223','Adres 23','5553334423','musteri23@example.com','12345','Notlar 23',1),(44,'Musteri Isim24','22222222224','Adres 24','5553334424','musteri24@example.com','12345','Notlar 24',0),(45,'Musteri Isim25','22222222225','Adres 25','5553334425','musteri25@example.com','12345','Notlar 25',1),(46,'Musteri Isim26','22222222226','Adres 26','5553334426','musteri26@example.com','12345','Notlar 26',0),(47,'Musteri Isim27','22222222227','Adres 27','5553334427','musteri27@example.com','12345','Notlar 27',1),(48,'Musteri Isim28','22222222228','Adres 28','5553334428','musteri28@example.com','12345','Notlar 28',0),(49,'Musteri Isim29','22222222229','Adres 29','5553334429','musteri29@example.com','12345','Notlar 29',1),(50,'Musteri Isim30','22222222230','Adres 30','5553334430','musteri30@example.com','12345','Notlar 30',0),(51,'Musteri Isim31','22222222231','Adres 31','5553334431','musteri31@example.com','12345','Notlar 31',1),(52,'Musteri Isim32','22222222232','Adres 32','5553334432','musteri32@example.com','12345','Notlar 32',0),(53,'Musteri Isim33','22222222233','Adres 33','5553334433','musteri33@example.com','12345','Notlar 33',1),(54,'Musteri Isim34','22222222234','Adres 34','5553334434','musteri34@example.com','12345','Notlar 34',0),(55,'Musteri Isim35','22222222235','Adres 35','5553334435','musteri35@example.com','12345','Notlar 35',1),(56,'Musteri Isim36','22222222236','Adres 36','5553334436','musteri36@example.com','12345','Notlar 36',0),(57,'Musteri Isim37','22222222237','Adres 37','5553334437','musteri37@example.com','12345','Notlar 37',1),(58,'Musteri Isim38','22222222238','Adres 38','5553334438','musteri38@example.com','12345','Notlar 38',0),(59,'Musteri Isim39','22222222239','Adres 39','5553334439','musteri39@example.com','12345','Notlar 39',1),(60,'Musteri Isim40','22222222240','Adres 40','5553334440','musteri40@example.com','12345','Notlar 40',0),(61,'Musteri Isim41','22222222241','Adres 41','5553334441','musteri41@example.com','12345','Notlar 41',1),(62,'Musteri Isim42','22222222242','Adres 42','5553334442','musteri42@example.com','12345','Notlar 42',0),(63,'Musteri Isim43','22222222243','Adres 43','5553334443','musteri43@example.com','12345','Notlar 43',1),(64,'Musteri Isim44','22222222244','Adres 44','5553334444','musteri44@example.com','12345','Notlar 44',0),(65,'Musteri Isim45','22222222245','Adres 45','5553334445','musteri45@example.com','12345','Notlar 45',1),(66,'Musteri Isim46','22222222246','Adres 46','5553334446','musteri46@example.com','12345','Notlar 46',0),(67,'Musteri Isim47','22222222247','Adres 47','5553334447','musteri47@example.com','12345','Notlar 47',1),(68,'Musteri Isim48','22222222248','Adres 48','5553334448','musteri48@example.com','12345','Notlar 48',0),(69,'Musteri Isim49','22222222249','Adres 49','5553334449','musteri49@example.com','12345','Notlar 49',1),(70,'Musteri Isim50','22222222250','Adres 50','5553334450','musteri50@example.com','12345','Notlar 50',0),(71,'Musteri Isim51','22222222251','Adres 51','5553334451','musteri51@example.com','12345','Notlar 51',1),(72,'Musteri Isim52','22222222252','Adres 52','5553334452','musteri52@example.com','12345','Notlar 52',0),(73,'Musteri Isim53','22222222253','Adres 53','5553334453','musteri53@example.com','12345','Notlar 53',1),(74,'Musteri Isim54','22222222254','Adres 54','5553334454','musteri54@example.com','12345','Notlar 54',0),(75,'Musteri Isim55','22222222255','Adres 55','5553334455','musteri55@example.com','12345','Notlar 55',1),(76,'Musteri Isim56','22222222256','Adres 56','5553334456','musteri56@example.com','12345','Notlar 56',0),(77,'Musteri Isim57','22222222257','Adres 57','5553334457','musteri57@example.com','12345','Notlar 57',1),(78,'Musteri Isim58','22222222258','Adres 58','5553334458','musteri58@example.com','12345','Notlar 58',0),(79,'Musteri Isim59','22222222259','Adres 59','5553334459','musteri59@example.com','12345','Notlar 59',1),(80,'Musteri Isim60','22222222260','Adres 60','5553334460','musteri60@example.com','12345','Notlar 60',0),(81,'Musteri Isim61','22222222261','Adres 61','5553334461','musteri61@example.com','12345','Notlar 61',1),(82,'Musteri Isim62','22222222262','Adres 62','5553334462','musteri62@example.com','12345','Notlar 62',0),(83,'Musteri Isim63','22222222263','Adres 63','5553334463','musteri63@example.com','12345','Notlar 63',1),(84,'Musteri Isim64','22222222264','Adres 64','5553334464','musteri64@example.com','12345','Notlar 64',0),(85,'Musteri Isim65','22222222265','Adres 65','5553334465','musteri65@example.com','12345','Notlar 65',1),(86,'Musteri Isim66','22222222266','Adres 66','5553334466','musteri66@example.com','12345','Notlar 66',0),(87,'Musteri Isim67','22222222267','Adres 67','5553334467','musteri67@example.com','12345','Notlar 67',1),(88,'Musteri Isim68','22222222268','Adres 68','5553334468','musteri68@example.com','12345','Notlar 68',0),(89,'Musteri Isim69','22222222269','Adres 69','5553334469','musteri69@example.com','12345','Notlar 69',1),(90,'Musteri Isim70','22222222270','Adres 70','5553334470','musteri70@example.com','12345','Notlar 70',0),(91,'Musteri Isim71','22222222271','Adres 71','5553334471','musteri71@example.com','12345','Notlar 71',1),(92,'Musteri Isim72','22222222272','Adres 72','5553334472','musteri72@example.com','12345','Notlar 72',0),(93,'Musteri Isim73','22222222273','Adres 73','5553334473','musteri73@example.com','12345','Notlar 73',1),(94,'Musteri Isim74','22222222274','Adres 74','5553334474','musteri74@example.com','12345','Notlar 74',0),(95,'Musteri Isim75','22222222275','Adres 75','5553334475','musteri75@example.com','12345','Notlar 75',1),(96,'Musteri Isim76','22222222276','Adres 76','5553334476','musteri76@example.com','12345','Notlar 76',0),(97,'Musteri Isim77','22222222277','Adres 77','5553334477','musteri77@example.com','12345','Notlar 77',1),(98,'Musteri Isim78','22222222278','Adres 78','5553334478','musteri78@example.com','12345','Notlar 78',0),(99,'Musteri Isim79','22222222279','Adres 79','5553334479','musteri79@example.com','12345','Notlar 79',1),(100,'Musteri Isim80','22222222280','Adres 80','5553334480','musteri80@example.com','12345','Notlar 80',0),(101,'Musteri Isim81','22222222281','Adres 81','5553334481','musteri81@example.com','12345','Notlar 81',1),(102,'Musteri Isim82','22222222282','Adres 82','5553334482','musteri82@example.com','12345','Notlar 82',0),(103,'Musteri Isim83','22222222283','Adres 83','5553334483','musteri83@example.com','12345','Notlar 83',1),(104,'Musteri Isim84','22222222284','Adres 84','5553334484','musteri84@example.com','12345','Notlar 84',0),(105,'Musteri Isim85','22222222285','Adres 85','5553334485','musteri85@example.com','12345','Notlar 85',1),(106,'Musteri Isim86','22222222286','Adres 86','5553334486','musteri86@example.com','12345','Notlar 86',0),(107,'Musteri Isim87','22222222287','Adres 87','5553334487','musteri87@example.com','12345','Notlar 87',1),(108,'Musteri Isim88','22222222288','Adres 88','5553334488','musteri88@example.com','12345','Notlar 88',0),(109,'Musteri Isim89','22222222289','Adres 89','5553334489','musteri89@example.com','12345','Notlar 89',1),(110,'Musteri Isim90','22222222290','Adres 90','5553334490','musteri90@example.com','12345','Notlar 90',0),(111,'Musteri Isim91','22222222291','Adres 91','5553334491','musteri91@example.com','12345','Notlar 91',1),(112,'Musteri Isim92','22222222292','Adres 92','5553334492','musteri92@example.com','12345','Notlar 92',0),(113,'Musteri Isim93','22222222293','Adres 93','5553334493','musteri93@example.com','12345','Notlar 93',1),(114,'Musteri Isim94','22222222294','Adres 94','5553334494','musteri94@example.com','12345','Notlar 94',0),(115,'Musteri Isim95','22222222295','Adres 95','5553334495','musteri95@example.com','12345','Notlar 95',1),(116,'Musteri Isim96','22222222296','Adres 96','5553334496','musteri96@example.com','12345','Notlar 96',0),(117,'Musteri Isim97','22222222297','Adres 97','5553334497','musteri97@example.com','12345','Notlar 97',1),(118,'Musteri Isim98','22222222298','Adres 98','5553334498','musteri98@example.com','12345','Notlar 98',0),(119,'Musteri Isim99','22222222299','Adres 99','5553334499','musteri99@example.com','12345','Notlar 99',1),(120,'Musteri Isim100','22222222300','Adres 100','5553334400','musteri100@example.com','12345','Notlar 100',0),(124,'İDO KOZMETİK A.Ş.','12345678901','İkitelli Organize Sanayi Bölgesi, Başakşehir/İstanbul','+90 212 123 4567','info@idokozmetik.com','$2y$10$asiDxiJGBplBhCrCmhWWSehX8oChLQa19UquTNtJADmd7D9j3YcdW','Ana üretim ortağı',1),(125,'Parfüm Dünyası','23456789012','Bağdat Caddesi No:123, Kadıköy/İstanbul','+90 216 234 5678','bilgi@parfumdunyasi.com','$2y$10$GltAfPH6qdAvDrUhajdCoug3QEOKNCYNCqZ9PWs/oi/Bch7FO8GIq','Bayilik',1),(126,'Gül Esans A.Ş.','34567890123','Atasehir Organize Sanayi, Ataşehir/İstanbul','+90 212 345 6789','iletisim@gulesans.com','','Ham madde sağlayıcısı',0),(127,'Koku A.Ş.','45678901234','Levent Mahallesi No:45, Beşiktaş/İstanbul','+90 212 456 7890','musteri@koku.com','$2y$10$JYzr1qA9.YogVbe0jfduleQQPsCJeHTH36tH/7CJlIvlezvzE4l8q','Perakende mağaza zinciri',1),(128,'Lavanta Kozmetik','56789012345','Alsancak Mahallesi No:67, Konak/İzmir','+90 232 567 8901','destek@lavantakozmetik.com','','E-ticaret müşterisi',0),(129,'Rüzgar Kozmetik','67890123456','Etiler Mahallesi No:89, Beşiktaş/İstanbul','+90 212 678 9012','iletisim@ruzgarkozmetik.com','$2y$10$6A4tw3DiquIPkNger3NldeLg0sYTKtrOb.5Jjze0q3Mrv4cSeBt56','Bayi ortağı',1),(130,'Sedef Kozmetik','78901234567','Ankara Cad. No:101, Çankaya/Ankara','+90 312 789 0123','yönetim@sedefkozmetik.com','','Orta ölçekli dağıtım',0),(131,'Zeytin Yaprağı Kozmetik','89012345678','Alsancak Meydanı No:23, Alsancak/İzmir','+90 232 890 1234','bilgi@zeytin.com','$2y$10$ESuksYJgQpZH9MSb18Ku4OUEkjzR.REeD4pSsaF2VjRIRoX.1W.Ae','Doğal ürünler bayii',1),(132,'Nil Kozmetik','90123456789','Gaziosmanpaşa Mah. No:45, Samsun','+90 362 901 2345','musteri@nilkozmetik.com','','Yeni başlayan bayi',0),(133,'Lale Kozmetik','01234567890','Mithatpaşa Cad. No:67, Maltepe/İstanbul','+90 216 012 3456','iletisim@lalekozmetik.com','$2y$10$Lr99K7EwdQU7CrpuLxKqn.120DYuP7UvZyrYMciEDZ4Y1iHKxaQ/.','Kurumsal satış',1),(134,'Menekşe Kozmetik','12345678902','Alsancak Mah. No:89, Bornova/İzmir','+90 232 123 4567','siparis@menekse.com','','Toptan satış',0),(135,'Papatya Kozmetik','23456789013','Atatürk Bulvarı No:101, Alsancak/İzmir','+90 232 234 5678','info@papatyakozmetik.com','$2y$10$O6Jcz2FUOCYN7AtKYihFq.D2763RvxnL0uOJgqHH0of7XuP74MWLS','Online platform',1),(136,'Gülün Adı','34567890124','Kordonboyu No:23, Alsancak/İzmir','+90 232 345 6789','iletisim@gulunadi.com','','Kozmetik mağazası',0),(137,'Karanfil Kozmetik','45678901235','Cumhuriyet Cad. No:45, Meram/Konya','+90 332 456 7890','destek@karanfikozmetik.com','$2y$10$emz1dP4IpJlCeuHTLMa/p.kBpJ50a28fpda5LJfv0KKBhvK5Zp0k6','Yerel bayi',1),(138,'Beyaz Zambak','56789012346','Barbarossa Cad. No:67, Bornova/İzmir','+90 232 567 8901','yönetim@beyazzambak.com','','Doğal ürünler',0),(139,'Sarı Menekşe','67890123457','Kocatepe Mah. No:89, Kocaeli','+90 262 678 9012','iletisim@sarimenekse.com','$2y$10$xnJaKwrxzNtBWVrIWu0Uf.6/ADpcqepuGGfOVGY18VKPxkdx7/Ysq','Organik ürünler',1),(140,'Mor Menekşe','78901234568','Cumhuriyet Cad. No:101, Balçova/İzmir','+90 232 789 0123','bilgi@mormenekse.com','','Özel üretim',0),(141,'Çiçek Kozmetik','89012345679','Atatürk Mah. No:23, Bursa','+90 224 890 1234','musteri@cicekkozmetik.com','$2y$10$aofGZMDvCoaQSASiJnXOA.kH/iao0BcGOKrs4hqmbd3/LybV6NrAy','Bayi ortağı',1),(142,'Eflatun Kozmetik','90123456780','Fatih Mah. No:45, Trabzon','+90 462 901 2345','iletisim@eflatunkozmetik.com','','Bölgesel dağıtım',0),(143,'Misket Kozmetik','01234567891','Gazi Mah. No:67, Samsun','+90 362 012 3456','info@misketkozmetik.com','$2y$10$PyxwQux3ZVexC.Sb65MOHOK/XCA1Eo8qpFS/YI5PupS21GHfOp9ei','Perakende satış',1),(144,'Nergis Kozmetik','12345678903','Baraj Yolu No:89, Adana','+90 322 123 4567','destek@nergiskozmetik.com','','Doğal ürünler',0),(145,'Zambak Kozmetik','23456789014','Anadolu Cad. No:101, Kocaeli','+90 262 234 5678','iletisim@zambakkozmetik.com','$2y$10$QUlwoxYWtbOob4j7SY8Ob.5bA/zYDfMHcvlbs4QOGNFGW9fvsseLy','Kurumsal satış',1),(146,'Lale Bahçesi','34567890125','Merkez Mah. No:23, Antalya','+90 242 345 6789','bilgi@lalebahcesi.com','','Turistik bölge',0),(147,'Gül Şehri','45678901236','Kültür Mah. No:45, Bursa','+90 224 456 7890','yönetim@gulsehri.com','$2y$10$gwUYPfWFxzuDmQixMCWP3.dSFDoMrlyMZDhLAIM6yScqhKw.xdHM.','Yerel üretim',1),(148,'Kırlangıç Kozmetik','56789012347','Sakarya Mah. No:67, Kocaeli','+90 262 567 8901','info@kirlangickozmetik.com','','Yeni bayi',0),(149,'Kartal Kozmetik','67890123458','Atatürk Mah. No:89, Kartal/İstanbul','+90 216 678 9012','iletisim@kartalkozmetik.com','$2y$10$GhsAp4XOs5TpEJp8KQgndeqbfhY/tHFsyxdhzDgkIh8ptWUXUw5xm','Bölgesel satış',1),(150,'Kumru Kozmetik','78901234569','Gazi Mah. No:101, Ankara','+90 312 789 0123','destek@kumrukozmetik.com','','Toptan satış',0),(151,'Mavi Kozmetik','89012345670','Baraj Yolu No:23, Mersin','+90 324 890 1234','iletisim@makozmetik.com','$2y$10$YSO74ctoKZBGcbvloHDBlO8X/GaYN0ymR09EysCixUutKjltXD1fO','Online satış',1),(152,'Kırmızı Gül','90123456781','Atakent Mah. No:45, Gebze/Kocaeli','+90 262 901 2345','bilgi@kirmizigul.com','','Marka ortağı',0),(153,'Sarı Lale','01234567892','Cumhuriyet Mah. No:67, Adana','+90 322 012 3456','yönetim@sarilale.com','$2y$10$2mH7F2MDP/mp0RLMCiAun.UoJ0M/Lkh91y/Y/KyC4uDP5cdQUMIgC','Yurt dışı satış',1);
/*!40000 ALTER TABLE `musteriler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_izinleri`
--

LOCK TABLES `personel_izinleri` WRITE;
/*!40000 ALTER TABLE `personel_izinleri` DISABLE KEYS */;
INSERT INTO `personel_izinleri` VALUES (59,160,'action:ayarlar:backup'),(58,160,'action:ayarlar:currency'),(57,160,'action:musteriler:edit'),(54,160,'page:view:ayarlar'),(56,160,'page:view:excele_aktar'),(52,160,'page:view:musteriler'),(51,160,'page:view:navigation'),(53,160,'page:view:personeller'),(55,160,'page:view:yedekleme');
/*!40000 ALTER TABLE `personel_izinleri` ENABLE KEYS */;
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
  PRIMARY KEY (`personel_id`),
  UNIQUE KEY `tc_kimlik_no` (`tc_kimlik_no`)
) ENGINE=InnoDB AUTO_INCREMENT=282 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personeller`
--

LOCK TABLES `personeller` WRITE;
/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q'),(151,'Isim Soyisim2','11111111112','1995-05-20','2024-01-15','Pozisyon 2','Departman 2','personel2@example.com','5554443302','Adres no 2','Test personeli 2','12345'),(152,'Isim Soyisim3','11111111113','1995-05-20','2024-01-15','Pozisyon 3','Departman 3','personel3@example.com','5554443303','Adres no 3','Test personeli 3','12345'),(153,'Isim Soyisim4','11111111114','1995-05-20','2024-01-15','Pozisyon 4','Departman 4','personel4@example.com','5554443304','Adres no 4','Test personeli 4','12345'),(154,'Isim Soyisim5','11111111115','1995-05-20','2024-01-15','Pozisyon 5','Departman 5','personel5@example.com','5554443305','Adres no 5','Test personeli 5','12345'),(155,'Isim Soyisim6','11111111116','1995-05-20','2024-01-15','Pozisyon 6','Departman 6','personel6@example.com','5554443306','Adres no 6','Test personeli 6','12345'),(156,'Isim Soyisim7','11111111117','1995-05-20','2024-01-15','Pozisyon 7','Departman 7','personel7@example.com','5554443307','Adres no 7','Test personeli 7','12345'),(157,'Isim Soyisim8','11111111118','1995-05-20','2024-01-15','Pozisyon 8','Departman 8','personel8@example.com','5554443308','Adres no 8','Test personeli 8','12345'),(158,'Isim Soyisim9','11111111119','1995-05-20','2024-01-15','Pozisyon 9','Departman 9','personel9@example.com','5554443309','Adres no 9','Test personeli 9','12345'),(160,'Isim Soyisim11','11111111121','1995-05-20','2024-01-15','Pozisyon 11','Departman 11','personel11@example.com','5554443311','Adres no 11','Test personeli 11','$2y$10$r4S//h2dXvZ2Pcm5cJY5sOuYxmui4CnIwFv1Hk1gdh.cN9B36oX3G'),(161,'Isim Soyisim12','11111111122','1995-05-20','2024-01-15','Pozisyon 12','Departman 12','personel12@example.com','5554443312','Adres no 12','Test personeli 12','12345'),(162,'Isim Soyisim13','11111111123','1995-05-20','2024-01-15','Pozisyon 13','Departman 13','personel13@example.com','5554443313','Adres no 13','Test personeli 13','12345'),(163,'Isim Soyisim14','11111111124','1995-05-20','2024-01-15','Pozisyon 14','Departman 14','personel14@example.com','5554443314','Adres no 14','Test personeli 14','12345'),(164,'Isim Soyisim15','11111111125','1995-05-20','2024-01-15','Pozisyon 15','Departman 15','personel15@example.com','5554443315','Adres no 15','Test personeli 15','12345'),(165,'Isim Soyisim16','11111111126','1995-05-20','2024-01-15','Pozisyon 16','Departman 16','personel16@example.com','5554443316','Adres no 16','Test personeli 16','12345'),(166,'Isim Soyisim17','11111111127','1995-05-20','2024-01-15','Pozisyon 17','Departman 17','personel17@example.com','5554443317','Adres no 17','Test personeli 17','12345'),(167,'Isim Soyisim18','11111111128','1995-05-20','2024-01-15','Pozisyon 18','Departman 18','personel18@example.com','5554443318','Adres no 18','Test personeli 18','12345'),(168,'Isim Soyisim19','11111111129','1995-05-20','2024-01-15','Pozisyon 19','Departman 19','personel19@example.com','5554443319','Adres no 19','Test personeli 19','12345'),(169,'Isim Soyisim20','11111111130','1995-05-20','2024-01-15','Pozisyon 20','Departman 20','personel20@example.com','5554443320','Adres no 20','Test personeli 20','12345'),(170,'Isim Soyisim21','11111111131','1995-05-20','2024-01-15','Pozisyon 21','Departman 21','personel21@example.com','5554443321','Adres no 21','Test personeli 21','12345'),(171,'Isim Soyisim22','11111111132','1995-05-20','2024-01-15','Pozisyon 22','Departman 22','personel22@example.com','5554443322','Adres no 22','Test personeli 22','12345'),(172,'Isim Soyisim23','11111111133','1995-05-20','2024-01-15','Pozisyon 23','Departman 23','personel23@example.com','5554443323','Adres no 23','Test personeli 23','12345'),(173,'Isim Soyisim24','11111111134','1995-05-20','2024-01-15','Pozisyon 24','Departman 24','personel24@example.com','5554443324','Adres no 24','Test personeli 24','12345'),(174,'Isim Soyisim25','11111111135','1995-05-20','2024-01-15','Pozisyon 25','Departman 25','personel25@example.com','5554443325','Adres no 25','Test personeli 25','12345'),(175,'Isim Soyisim26','11111111136','1995-05-20','2024-01-15','Pozisyon 26','Departman 26','personel26@example.com','5554443326','Adres no 26','Test personeli 26','12345'),(176,'Isim Soyisim27','11111111137','1995-05-20','2024-01-15','Pozisyon 27','Departman 27','personel27@example.com','5554443327','Adres no 27','Test personeli 27','12345'),(177,'Isim Soyisim28','11111111138','1995-05-20','2024-01-15','Pozisyon 28','Departman 28','personel28@example.com','5554443328','Adres no 28','Test personeli 28','12345'),(178,'Isim Soyisim29','11111111139','1995-05-20','2024-01-15','Pozisyon 29','Departman 29','personel29@example.com','5554443329','Adres no 29','Test personeli 29','12345'),(179,'Isim Soyisim30','11111111140','1995-05-20','2024-01-15','Pozisyon 30','Departman 30','personel30@example.com','5554443330','Adres no 30','Test personeli 30','12345'),(180,'Isim Soyisim31','11111111141','1995-05-20','2024-01-15','Pozisyon 31','Departman 31','personel31@example.com','5554443331','Adres no 31','Test personeli 31','12345'),(181,'Isim Soyisim32','11111111142','1995-05-20','2024-01-15','Pozisyon 32','Departman 32','personel32@example.com','5554443332','Adres no 32','Test personeli 32','12345'),(182,'Isim Soyisim33','11111111143','1995-05-20','2024-01-15','Pozisyon 33','Departman 33','personel33@example.com','5554443333','Adres no 33','Test personeli 33','12345'),(183,'Isim Soyisim34','11111111144','1995-05-20','2024-01-15','Pozisyon 34','Departman 34','personel34@example.com','5554443334','Adres no 34','Test personeli 34','12345'),(184,'Isim Soyisim35','11111111145','1995-05-20','2024-01-15','Pozisyon 35','Departman 35','personel35@example.com','5554443335','Adres no 35','Test personeli 35','12345'),(185,'Isim Soyisim36','11111111146','1995-05-20','2024-01-15','Pozisyon 36','Departman 36','personel36@example.com','5554443336','Adres no 36','Test personeli 36','12345'),(186,'Isim Soyisim37','11111111147','1995-05-20','2024-01-15','Pozisyon 37','Departman 37','personel37@example.com','5554443337','Adres no 37','Test personeli 37','12345'),(187,'Isim Soyisim38','11111111148','1995-05-20','2024-01-15','Pozisyon 38','Departman 38','personel38@example.com','5554443338','Adres no 38','Test personeli 38','12345'),(188,'Isim Soyisim39','11111111149','1995-05-20','2024-01-15','Pozisyon 39','Departman 39','personel39@example.com','5554443339','Adres no 39','Test personeli 39','12345'),(189,'Isim Soyisim40','11111111150','1995-05-20','2024-01-15','Pozisyon 40','Departman 40','personel40@example.com','5554443340','Adres no 40','Test personeli 40','12345'),(190,'Isim Soyisim41','11111111151','1995-05-20','2024-01-15','Pozisyon 41','Departman 41','personel41@example.com','5554443341','Adres no 41','Test personeli 41','12345'),(191,'Isim Soyisim42','11111111152','1995-05-20','2024-01-15','Pozisyon 42','Departman 42','personel42@example.com','5554443342','Adres no 42','Test personeli 42','12345'),(192,'Isim Soyisim43','11111111153','1995-05-20','2024-01-15','Pozisyon 43','Departman 43','personel43@example.com','5554443343','Adres no 43','Test personeli 43','12345'),(193,'Isim Soyisim44','11111111154','1995-05-20','2024-01-15','Pozisyon 44','Departman 44','personel44@example.com','5554443344','Adres no 44','Test personeli 44','12345'),(194,'Isim Soyisim45','11111111155','1995-05-20','2024-01-15','Pozisyon 45','Departman 45','personel45@example.com','5554443345','Adres no 45','Test personeli 45','12345'),(195,'Isim Soyisim46','11111111156','1995-05-20','2024-01-15','Pozisyon 46','Departman 46','personel46@example.com','5554443346','Adres no 46','Test personeli 46','12345'),(196,'Isim Soyisim47','11111111157','1995-05-20','2024-01-15','Pozisyon 47','Departman 47','personel47@example.com','5554443347','Adres no 47','Test personeli 47','12345'),(197,'Isim Soyisim48','11111111158','1995-05-20','2024-01-15','Pozisyon 48','Departman 48','personel48@example.com','5554443348','Adres no 48','Test personeli 48','12345'),(198,'Isim Soyisim49','11111111159','1995-05-20','2024-01-15','Pozisyon 49','Departman 49','personel49@example.com','5554443349','Adres no 49','Test personeli 49','12345'),(199,'Isim Soyisim50','11111111160','1995-05-20','2024-01-15','Pozisyon 50','Departman 50','personel50@example.com','5554443350','Adres no 50','Test personeli 50','12345'),(200,'Isim Soyisim51','11111111161','1995-05-20','2024-01-15','Pozisyon 51','Departman 51','personel51@example.com','5554443351','Adres no 51','Test personeli 51','12345'),(201,'Isim Soyisim52','11111111162','1995-05-20','2024-01-15','Pozisyon 52','Departman 52','personel52@example.com','5554443352','Adres no 52','Test personeli 52','12345'),(202,'Isim Soyisim53','11111111163','1995-05-20','2024-01-15','Pozisyon 53','Departman 53','personel53@example.com','5554443353','Adres no 53','Test personeli 53','12345'),(203,'Isim Soyisim54','11111111164','1995-05-20','2024-01-15','Pozisyon 54','Departman 54','personel54@example.com','5554443354','Adres no 54','Test personeli 54','12345'),(204,'Isim Soyisim55','11111111165','1995-05-20','2024-01-15','Pozisyon 55','Departman 55','personel55@example.com','5554443355','Adres no 55','Test personeli 55','12345'),(205,'Isim Soyisim56','11111111166','1995-05-20','2024-01-15','Pozisyon 56','Departman 56','personel56@example.com','5554443356','Adres no 56','Test personeli 56','12345'),(206,'Isim Soyisim57','11111111167','1995-05-20','2024-01-15','Pozisyon 57','Departman 57','personel57@example.com','5554443357','Adres no 57','Test personeli 57','12345'),(207,'Isim Soyisim58','11111111168','1995-05-20','2024-01-15','Pozisyon 58','Departman 58','personel58@example.com','5554443358','Adres no 58','Test personeli 58','12345'),(208,'Isim Soyisim59','11111111169','1995-05-20','2024-01-15','Pozisyon 59','Departman 59','personel59@example.com','5554443359','Adres no 59','Test personeli 59','12345'),(209,'Isim Soyisim60','11111111170','1995-05-20','2024-01-15','Pozisyon 60','Departman 60','personel60@example.com','5554443360','Adres no 60','Test personeli 60','12345'),(210,'Isim Soyisim61','11111111171','1995-05-20','2024-01-15','Pozisyon 61','Departman 61','personel61@example.com','5554443361','Adres no 61','Test personeli 61','12345'),(211,'Isim Soyisim62','11111111172','1995-05-20','2024-01-15','Pozisyon 62','Departman 62','personel62@example.com','5554443362','Adres no 62','Test personeli 62','12345'),(212,'Isim Soyisim63','11111111173','1995-05-20','2024-01-15','Pozisyon 63','Departman 63','personel63@example.com','5554443363','Adres no 63','Test personeli 63','12345'),(213,'Isim Soyisim64','11111111174','1995-05-20','2024-01-15','Pozisyon 64','Departman 64','personel64@example.com','5554443364','Adres no 64','Test personeli 64','12345'),(214,'Isim Soyisim65','11111111175','1995-05-20','2024-01-15','Pozisyon 65','Departman 65','personel65@example.com','5554443365','Adres no 65','Test personeli 65','12345'),(215,'Isim Soyisim66','11111111176','1995-05-20','2024-01-15','Pozisyon 66','Departman 66','personel66@example.com','5554443366','Adres no 66','Test personeli 66','12345'),(216,'Isim Soyisim67','11111111177','1995-05-20','2024-01-15','Pozisyon 67','Departman 67','personel67@example.com','5554443367','Adres no 67','Test personeli 67','12345'),(217,'Isim Soyisim68','11111111178','1995-05-20','2024-01-15','Pozisyon 68','Departman 68','personel68@example.com','5554443368','Adres no 68','Test personeli 68','12345'),(218,'Isim Soyisim69','11111111179','1995-05-20','2024-01-15','Pozisyon 69','Departman 69','personel69@example.com','5554443369','Adres no 69','Test personeli 69','12345'),(219,'Isim Soyisim70','11111111180','1995-05-20','2024-01-15','Pozisyon 70','Departman 70','personel70@example.com','5554443370','Adres no 70','Test personeli 70','12345'),(220,'Isim Soyisim71','11111111181','1995-05-20','2024-01-15','Pozisyon 71','Departman 71','personel71@example.com','5554443371','Adres no 71','Test personeli 71','12345'),(221,'Isim Soyisim72','11111111182','1995-05-20','2024-01-15','Pozisyon 72','Departman 72','personel72@example.com','5554443372','Adres no 72','Test personeli 72','12345'),(222,'Isim Soyisim73','11111111183','1995-05-20','2024-01-15','Pozisyon 73','Departman 73','personel73@example.com','5554443373','Adres no 73','Test personeli 73','12345'),(223,'Isim Soyisim74','11111111184','1995-05-20','2024-01-15','Pozisyon 74','Departman 74','personel74@example.com','5554443374','Adres no 74','Test personeli 74','12345'),(224,'Isim Soyisim75','11111111185','1995-05-20','2024-01-15','Pozisyon 75','Departman 75','personel75@example.com','5554443375','Adres no 75','Test personeli 75','12345'),(225,'Isim Soyisim76','11111111186','1995-05-20','2024-01-15','Pozisyon 76','Departman 76','personel76@example.com','5554443376','Adres no 76','Test personeli 76','12345'),(226,'Isim Soyisim77','11111111187','1995-05-20','2024-01-15','Pozisyon 77','Departman 77','personel77@example.com','5554443377','Adres no 77','Test personeli 77','12345'),(227,'Isim Soyisim78','11111111188','1995-05-20','2024-01-15','Pozisyon 78','Departman 78','personel78@example.com','5554443378','Adres no 78','Test personeli 78','12345'),(228,'Isim Soyisim79','11111111189','1995-05-20','2024-01-15','Pozisyon 79','Departman 79','personel79@example.com','5554443379','Adres no 79','Test personeli 79','12345'),(229,'Isim Soyisim80','11111111190','1995-05-20','2024-01-15','Pozisyon 80','Departman 80','personel80@example.com','5554443380','Adres no 80','Test personeli 80','12345'),(230,'Isim Soyisim81','11111111191','1995-05-20','2024-01-15','Pozisyon 81','Departman 81','personel81@example.com','5554443381','Adres no 81','Test personeli 81','12345'),(231,'Isim Soyisim82','11111111192','1995-05-20','2024-01-15','Pozisyon 82','Departman 82','personel82@example.com','5554443382','Adres no 82','Test personeli 82','12345'),(232,'Isim Soyisim83','11111111193','1995-05-20','2024-01-15','Pozisyon 83','Departman 83','personel83@example.com','5554443383','Adres no 83','Test personeli 83','12345'),(233,'Isim Soyisim84','11111111194','1995-05-20','2024-01-15','Pozisyon 84','Departman 84','personel84@example.com','5554443384','Adres no 84','Test personeli 84','12345'),(234,'Isim Soyisim85','11111111195','1995-05-20','2024-01-15','Pozisyon 85','Departman 85','personel85@example.com','5554443385','Adres no 85','Test personeli 85','12345'),(235,'Isim Soyisim86','11111111196','1995-05-20','2024-01-15','Pozisyon 86','Departman 86','personel86@example.com','5554443386','Adres no 86','Test personeli 86','12345'),(236,'Isim Soyisim87','11111111197','1995-05-20','2024-01-15','Pozisyon 87','Departman 87','personel87@example.com','5554443387','Adres no 87','Test personeli 87','12345'),(237,'Isim Soyisim88','11111111198','1995-05-20','2024-01-15','Pozisyon 88','Departman 88','personel88@example.com','5554443388','Adres no 88','Test personeli 88','12345'),(238,'Isim Soyisim89','11111111199','1995-05-20','2024-01-15','Pozisyon 89','Departman 89','personel89@example.com','5554443389','Adres no 89','Test personeli 89','12345'),(239,'Isim Soyisim90','11111111200','1995-05-20','2024-01-15','Pozisyon 90','Departman 90','personel90@example.com','5554443390','Adres no 90','Test personeli 90','12345'),(240,'Isim Soyisim91','11111111201','1995-05-20','2024-01-15','Pozisyon 91','Departman 91','personel91@example.com','5554443391','Adres no 91','Test personeli 91','12345'),(241,'Isim Soyisim92','11111111202','1995-05-20','2024-01-15','Pozisyon 92','Departman 92','personel92@example.com','5554443392','Adres no 92','Test personeli 92','12345'),(242,'Isim Soyisim93','11111111203','1995-05-20','2024-01-15','Pozisyon 93','Departman 93','personel93@example.com','5554443393','Adres no 93','Test personeli 93','12345'),(243,'Isim Soyisim94','11111111204','1995-05-20','2024-01-15','Pozisyon 94','Departman 94','personel94@example.com','5554443394','Adres no 94','Test personeli 94','12345'),(244,'Isim Soyisim95','11111111205','1995-05-20','2024-01-15','Pozisyon 95','Departman 95','personel95@example.com','5554443395','Adres no 95','Test personeli 95','12345'),(245,'Isim Soyisim96','11111111206','1995-05-20','2024-01-15','Pozisyon 96','Departman 96','personel96@example.com','5554443396','Adres no 96','Test personeli 96','12345'),(246,'Isim Soyisim97','11111111207','1995-05-20','2024-01-15','Pozisyon 97','Departman 97','personel97@example.com','5554443397','Adres no 97','Test personeli 97','12345'),(247,'Isim Soyisim98','11111111208','1995-05-20','2024-01-15','Pozisyon 98','Departman 98','personel98@example.com','5554443398','Adres no 98','Test personeli 98','12345'),(248,'Isim Soyisim99','11111111209','1995-05-20','2024-01-15','Pozisyon 99','Departman 99','personel99@example.com','5554443399','Adres no 99','Test personeli 99','12345'),(252,'Admin User','12345678901','1980-01-01','2020-01-01','Yönetici','Yönetim','admin@parfum.com','+90 532 123 4567','Merkez Ofis, İstanbul','Sistem yöneticisi','$2y$10$.A3o63YO/PBh77beZvBuy..IjwqHpY4J5cI6WwJCHuyiy3auEHfxS'),(253,'Ahmet Yılmaz','23456789012','1985-05-15','2021-03-15','Satış Müdürü','Satış','ahmet.yilmaz@parfum.com','+90 532 234 5678','Kadıköy, İstanbul','Satış ve pazarlama','$2y$10$RdPuQHVevcJSGAJJc/0fRebxlb0.M7RvxyT5HhRlRmrPh.OF4IzwW'),(254,'Mehmet Kaya','34567890123','1982-11-22','2020-07-10','Üretim Müdürü','Üretim','mehmet.kaya@parfum.com','+90 532 345 6789','Ataşehir, İstanbul','Üretim planlama','$2y$10$WLG/K1YUmfPnc862HUxkI.VM4BT4GDWoYBcyq3hip80IHhJ7jT9xK'),(255,'Ayşe Demir','45678901234','1988-08-30','2019-11-05','Finans Müdürü','Finans','ayse.demir@parfum.com','+90 532 456 7890','Beşiktaş, İstanbul','Finansal analiz','$2y$10$tY4cNd8lOusrPqknay6WXuhqWTG6/jorCJTZKux5.HfiyrsRIbN1K'),(256,'Fatma Şahin','56789012345','1984-02-18','2020-02-20','İnsan Kaynakları Müdürü','İnsan Kaynakları','fatma.sahin@parfum.com','+90 532 567 8901','Kartal, İstanbul','Personel yönetimi','$2y$10$jvqPc1akAhLVHzXvQMJpa.nxipRFTRvEfJALNJIKUbg6uLWZ2PR1W'),(257,'Ali Can','67890123456','1986-09-12','2021-06-03','Pazarlama Müdürü','Pazarlama','ali.can@parfum.com','+90 532 678 9012','Kadıköy, İstanbul','Marka yönetimi','$2y$10$U4HFXmDL5JDdZuRsCvaZ8edKd2Gb.dzciVDwP2e/G.XeAh7zeV.e.'),(258,'Zeynep Öztürk','78901234567','1987-04-25','2019-09-15','Ar-Ge Müdürü','Ar-Ge','zeynep.ozturk@parfum.com','+90 532 789 0123','Maslak, İstanbul','Ürün geliştirme','$2y$10$ESjnddrJryGFIWBTtcwGle6g2G.oZC5y6iz4/iAiVAKguew9tBqfa'),(259,'Caner Aktaş','89012345678','1983-12-07','2020-04-22','Kalite Kontrol Müdürü','Kalite','caner.aktas@parfum.com','+90 532 890 1234','Güngören, İstanbul','Kalite yönetimi','$2y$10$khkSjkULQqykTiFCeprhG.SrJVRs/TrVnIt7l6Kesbec.6OVNQBsW'),(260,'Ebru Güneş','90123456789','1989-06-30','2021-01-14','Lojistik Müdürü','Lojistik','ebru.gunes@parfum.com','+90 532 901 2345','Pendik, İstanbul','Tedarik zinciri','$2y$10$FL3Qdsv098QzkkctfHDnpuI.Cm5O/9DWjV50LpSGg2lLIJyjRQi0K'),(261,'Murat Çelik','01234567890','1981-03-17','2018-10-08','Depo Sorumlusu','Depo','murat.celik@parfum.com','+90 532 012 3456','Tuzla, İstanbul','Depo takibi','$2y$10$1/QiEK5ATs.1J7ycBzvRpOGYxCc9y0VTRFP1/s20Zp/IlVoVfCVEC'),(262,'Gizem Koç','12345678903','1985-07-13','2020-05-19','Muhasebe Uzmanı','Finans','gizem.koc@parfum.com','+90 532 123 4456','Üsküdar, İstanbul','Muhasebe işlemleri','$2y$10$xPfNPwdCaIJp1.9JTLyezeRVUCtN.9eOH0JGqCNJfXraBJFrZ5KqK'),(263,'Emre Yıldız','23456789015','1986-11-09','2019-12-02','Bilgi İşlem Sorumlusu','BT','emre.yildiz@parfum.com','+90 532 234 5567','Kozyatağı, İstanbul','Sistem yönetimi','$2y$10$0/iCvYI.uwew3s..jXXPautXOvd8cBA.AY/EsIyHdWe7wm6MiPvT.'),(264,'Seda Aksoy','34567890126','1990-01-24','2021-08-23','Müşteri Temsilcisi','Satış','seda.aksoy@parfum.com','+90 532 345 6678','Beyoğlu, İstanbul','Müşteri hizmetleri','$2y$10$D9e1eFGmmp40hv9rcGM.qOhkmQMd7YSNTE32Zzujc5GAp8ZIRywY.'),(265,'Turgay Kılıç','45678901237','1987-05-30','2020-11-11','Pazarlama Uzmanı','Pazarlama','turgay.kilic@parfum.com','+90 532 456 7789','Esenler, İstanbul','Pazar araştırması','$2y$10$nmThkG2oGvEbQ6TEqBiUleIjmv6tpBn2xtl2OvE5jZoFPFdKf2gWW'),(266,'Derya Başbakan','56789012348','1988-09-16','2021-02-28','Ar-Ge Uzmanı','Ar-Ge','derya.basbakan@parfum.com','+90 532 567 8890','Ataşehir, İstanbul','Ürün formülasyonu','$2y$10$UfBkOTWy8w/.TbNKI8DDx.BlP4pHDhm/p0CLum.6kKQ.dwd/dWoxy'),(267,'Kemal Doğan','67890123459','1984-12-04','2019-08-15','Satış Temsilcisi','Satış','kemal.dogan@parfum.com','+90 532 678 9901','Bakırköy, İstanbul','Bayi takibi','$2y$10$Q/o4T0SZAtvuY.aA57Z3.eflMrkFh3n4bcPDZWa7YEIejnpfSk6Ee'),(268,'Nurcan Aslan','78901234560','1983-10-21','2020-09-07','Muhasebe Sorumlusu','Finans','nurcan.aslan@parfum.com','+90 532 789 0012','Kartal, İstanbul','Gelir-gider analizi','$2y$10$LVqI.RXQuxGVIVuHsqFxE.JcfJW/UBTYTAReHHUkfXvXIhoTNsUiu'),(269,'Cem Uysal','89012345671','1989-04-14','2021-04-12','Tedarikçi İlişkileri Uzmanı','Tedarik','cem.uysal@parfum.com','+90 532 890 1123','Maltepe, İstanbul','Tedarikçi yönetimi','$2y$10$bsR718uM6mrhg1Wjfz96b.zAut0jg4R88KihaCDAbs24fRFZgI28C'),(270,'Tuğba Yılmaz','90123456782','1986-08-08','2020-03-20','İnsan Kaynakları Uz.','İnsan Kaynakları','tugba.yilmaz@parfum.com','+90 532 901 2234','Kadıköy, İstanbul','İşe alım süreçleri','$2y$10$SSpiLhH3V2N4WjZ080Wz6.ijEbSeDVg/XhBQAJZC4l978vMZKEu7u'),(271,'Barış Karadeniz','01234567893','1985-06-26','2019-07-30','Üretim Planlama Uz.','Üretim','baris.karadeniz@parfum.com','+90 532 012 3345','Göztepe, İstanbul','Üretim planlama','$2y$10$TMf2nQ.w12APxY/eU/0FkectXOjVg52ouC2PAG6j//OeNz466/vnq'),(272,'Elif Yavuz','12345678904','1991-02-12','2021-07-18','Satış Danışmanı','Satış','elif.yavuz@parfum.com','+90 532 123 4456','Kozyatağı, İstanbul','Satış desteği','$2y$10$2BW7gc3KvilOhqRU1xtWWuM6Z/ghHNttwvCUx42vT7XvgUAeJArTS'),(273,'Oğuzhan Gök','23456789016','1987-11-05','2020-10-25','Lojistik Uzmanı','Lojistik','oguzhan.gok@parfum.com','+90 532 234 5567','Pendik, İstanbul','Sevkiyat takibi','$2y$10$0I095d/T1iZoiL6cHNP8TeyLrq.iv68nk/qROrfqeItyjT.aKStAW'),(274,'Melike Aktaş','34567890127','1988-03-19','2021-01-09','Marka Yönetimi Uz.','Pazarlama','melike.aktas@parfum.com','+90 532 345 6678','Beşiktaş, İstanbul','Marka stratejisi','$2y$10$r0Woo.79xhi9UaB4Jxb3Hu8lYs1iaNPaNjWzykwKymwmm53XYeoFe'),(275,'Volkan Arslan','45678901238','1982-07-23','2019-04-16','Tedarikçi Müdürü','Tedarik','volkan.arslan@parfum.com','+90 532 456 7789','Ataşehir, İstanbul','Tedarikçi koordinasyonu','$2y$10$sIlzfEIAS4xPEYFj/hEkxOpQxfC4BSKVTmP143fBgGikWI4PGcNwq'),(276,'Zeynep Bayrak','56789012349','1990-12-01','2020-06-04','Müşteri İlişkileri Uz.','Satış','zeynep.bayrak@parfum.com','+90 532 567 8890','Kartal, İstanbul','Müşteri memnuniyeti','$2y$10$wacbX2HTKXtJRqnUfF05X.Sf5AgmE4ZV2/WgocrgBe0DUwDwnDnxK'),(277,'Mertcan Kılıç','67890123450','1985-10-15','2021-03-30','Finans Uzmanı','Finans','mertcan.kilic@parfum.com','+90 532 678 9901','Bakırköy, İstanbul','Faturalandırma','$2y$10$lgmGbWMz7QuNnLU8E5Z8d.I1YzA4nC4iAOeibufUEipjjU.637IdS'),(278,'Ayşe Gül','78901234561','1989-05-28','2020-11-18','Depo Görevlisi','Depo','ayse.gul@parfum.com','+90 532 789 0012','Tuzla, İstanbul','Stok takibi','$2y$10$YYEil5bo5tEm5MM6yKgz3.8BhFBnULITJs0ymSlwCiNliKkbuH3Ky'),(279,'Emin Şahin','89012345672','1986-01-09','2021-05-02','Üretim Görevlisi','Üretim','emin.sahin@parfum.com','+90 532 890 1123','Güngören, İstanbul','Üretim','$2y$10$p86m.ymN67WFMDtP8XnQAesbiOILPWRnAvd30uiBegukwjFuGpx3W'),(280,'Gülşen Karadağ','90123456783','1988-08-22','2020-08-20','Kalite Kontrol Görevlisi','Kalite','gulsen.karadag@parfum.com','+90 532 901 2234','Sultanbeyli, İstanbul','Kalite testleri','$2y$10$aitE8OtmI2BOmTGSwXY0YOtU6IwyCnBGGD3bI56ApANzsTNR90QvW'),(281,'Hakan Polat','01234567894','1984-12-06','2019-06-14','Bilgi İşlem Uzmanı','BT','hakan.polat@parfum.com','+90 532 012 3345','Kadıköy, İstanbul','Sistem desteği','$2y$10$ghGcrjlR/.p5VM6yZZyBF.ZGC0zuTIK9b7KtbP7ZZh44SUhxnam/u');
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
INSERT INTO `siparis_kalemleri` VALUES (1,38,'Lavanta Koku',20,'adet',95.00,1900.00),(1,37,'Roz Koku',10,'adet',125.00,1250.00),(2,30,'10 evo',1,'adet',150.00,150.00),(2,61,'Ağustosböceği',21,'0',130.00,2730.00),(2,79,'Kırmızı Gül ve Portakal',33,'adet',155.00,5115.00),(3,30,'10 evo',1,'adet',150.00,150.00),(3,61,'Ağustosböceği',1,'adet',130.00,130.00),(4,30,'10 evo',1,'adet',150.00,150.00),(4,60,'Çilek ve Vanilya',1,'adet',125.00,125.00),(4,47,'Deniz Esintisi',1,'adet',90.00,90.00),(5,60,'Çilek ve Vanilya',1,'adet',125.00,125.00),(5,47,'Deniz Esintisi',1,'adet',90.00,90.00),(5,67,'Beyaz Çikolata',1,'adet',145.00,145.00),(6,30,'10 evo',1,'adet',150.00,150.00),(6,61,'Ağustosböceği',1,'adet',130.00,130.00),(8,61,'Ağustosböceği',1,'adet',130.00,130.00),(8,67,'Beyaz Çikolata',1,'adet',145.00,145.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparisler`
--

LOCK TABLES `siparisler` WRITE;
/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
INSERT INTO `siparisler` VALUES (1,5,'Ayse Kaya','2025-11-24 05:31:48','tamamlandi',30,'Personel: Admin User',1,'Admin User','2025-11-24 05:45:05',''),(2,8,'Ali Can','2025-11-24 18:46:07','tamamlandi',55,'Ali Can',1,'Admin User','2025-11-24 18:47:20',''),(3,8,'Ali Can','2025-11-26 14:58:03','onaylandi',2,'Ali Can',1,'Admin User','2025-11-26 21:35:50',''),(4,8,'Ali Can','2025-11-26 21:17:43','beklemede',3,'Ali Can',NULL,NULL,NULL,''),(5,8,'Ali Can','2025-11-26 21:20:24','onaylandi',3,'Ali Can',1,'Admin User','2025-11-26 21:34:22',''),(6,8,'Ali Can','2025-11-26 21:20:49','iptal_edildi',2,'Ali Can',NULL,NULL,NULL,''),(8,8,'Ali Can','2025-11-26 21:23:37','beklemede',2,'Ali Can',NULL,NULL,NULL,'');
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareket_kayitlari`
--

LOCK TABLES `stok_hareket_kayitlari` WRITE;
/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
INSERT INTO `stok_hareket_kayitlari` VALUES (1,'2025-11-24 05:37:01','urun','38','Lavanta Koku','adet',20.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Müşteri siparişi',1,'Admin User',NULL,NULL),(2,'2025-11-24 05:37:01','urun','37','Roz Koku','adet',10.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Müşteri siparişi',1,'Admin User',NULL,NULL),(3,'2025-11-24 05:37:55','urun','38','Lavanta Koku','adet',20.00,'giriş','iptal_cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Satış İptali - Müşteri siparişi',1,'Admin User',NULL,NULL),(4,'2025-11-24 05:37:55','urun','37','Roz Koku','adet',10.00,'giriş','iptal_cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Satış İptali - Müşteri siparişi',1,'Admin User',NULL,NULL),(5,'2025-11-24 05:44:51','urun','38','Lavanta Koku','adet',20.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Müşteri siparişi',1,'Admin User',NULL,NULL),(6,'2025-11-24 05:44:51','urun','37','Roz Koku','adet',10.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Müşteri siparişi',1,'Admin User',NULL,NULL),(7,'2025-11-24 05:45:05','urun','38','Lavanta Koku','adet',20.00,'giris','iptal_cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Satış İptali - Müşteri siparişi',1,'Admin User',NULL,NULL),(8,'2025-11-24 05:45:05','urun','37','Roz Koku','adet',10.00,'giris','iptal_cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Satış İptali - Müşteri siparişi',1,'Admin User',NULL,NULL),(9,'2025-11-26 08:38:35','urun','30','10 evo','adet',1.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,8,'Ali Can','Müşteri siparişi',1,'Admin User',NULL,NULL),(10,'2025-11-26 08:38:35','urun','61','Ağustosböceği','0',21.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,8,'Ali Can','Müşteri siparişi',1,'Admin User',NULL,NULL),(11,'2025-11-26 08:38:35','urun','79','Kırmızı Gül ve Portakal','adet',33.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,8,'Ali Can','Müşteri siparişi',1,'Admin User',NULL,NULL),(12,'2025-11-26 08:38:40','urun','38','Lavanta Koku','adet',20.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Müşteri siparişi',1,'Admin User',NULL,NULL),(13,'2025-11-26 08:38:40','urun','37','Roz Koku','adet',10.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,5,'Ayse Kaya','Müşteri siparişi',1,'Admin User',NULL,NULL),(14,'2025-11-26 22:16:35','malzeme','37','Ambalaj Malzemesi 2','gr',50.00,'giris','mal_kabul','Main Warehouse','R009',NULL,'s',NULL,NULL,NULL,'ss [Sozlesme ID: 41]',1,'Admin User','Tedarikçi Abdi',3),(15,'2025-11-26 23:02:05','malzeme','37','Ambalaj Malzemesi 2','gr',40.00,'giris','mal_kabul','Main Warehouse','R009',NULL,'',NULL,NULL,NULL,'selamlarr [Sozlesme ID: 48]',1,'Admin User','Tedarikçi Abdi',3),(16,'2025-11-26 23:02:35','malzeme','37','Ambalaj Malzemesi 2','gr',56.00,'giris','mal_kabul','Main Warehouse','R009',NULL,'',NULL,NULL,NULL,'selamm [Sozlesme ID: 48]',1,'Admin User','Tedarikçi Abdi',3),(17,'2025-11-26 23:03:11','malzeme','31','Medium Box','adet',55.00,'giris','mal_kabul','Ana Depo','R001',NULL,'',NULL,NULL,NULL,'dd [Sozlesme ID: 47]',1,'Admin User','Tedarikci Isim1',7);
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
INSERT INTO `stok_hareketleri_sozlesmeler` VALUES (15,48,37,40.00,'2025-11-26 20:02:05',30.00,'TL','Tedarikçi Abdi',3,'2025-11-18','2025-11-30'),(16,48,37,56.00,'2025-11-26 20:02:35',30.00,'TL','Tedarikçi Abdi',3,'2025-11-18','2025-11-30'),(17,47,31,55.00,'2025-11-26 20:03:11',10.00,'TL','Tedarikci Isim1',7,'2025-11-09','2025-11-30');
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tanklar`
--

LOCK TABLES `tanklar` WRITE;
/*!40000 ALTER TABLE `tanklar` DISABLE KEYS */;
INSERT INTO `tanklar` VALUES (1,'TANK001','Esans Tankı 1','Ana üretim tankı',1000.00),(2,'TANK002','Esans Tankı 2','Rezerv tankı',500.00),(8,'TK001','A1 - Aromatic Tank','Used for aromatic oils storage',5000.00),(9,'TK002','A2 - Essential Oil Tank','Premium essential oils storage',3500.00),(10,'TK003','B1 - Base Oil Tank','Base oils for perfume production',7500.00),(11,'TK004','B2 - Fragrance Tank','Fragrance compounds storage',4200.00),(12,'TK005','C1 - Alcohol Tank','Alcohol solvent storage',6000.00),(13,'TK006','C2 - Diluent Tank','Diluent materials storage',4500.00),(14,'TK007','D1 - Perfume Base Tank','Perfume base mixture storage',3800.00),(15,'TK008','E1 - Extraction Tank','Essential oil extraction',3200.00),(16,'TK009','F1 - Blending Tank','Final blending operations',5500.00),(17,'TK010','G1 - Quality Control Tank','Quality testing samples',2500.00),(18,'TK011','H1 - Packaging Tank','Ready for packaging materials',4000.00),(19,'TK012','I1 - Rose Oil Tank','Specialty rose oil storage',2000.00),(20,'TK013','J1 - Lavender Tank','Premium lavender oil',1800.00),(21,'TK014','K1 - Citrus Tank','Citrus-based fragrances',2200.00),(22,'TK015','L1 - Woody Notes Tank','Woody fragrance components',2800.00);
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
  PRIMARY KEY (`tedarikci_id`),
  UNIQUE KEY `tedarikci_id` (`tedarikci_id`),
  UNIQUE KEY `tedarikci_adi` (`tedarikci_adi`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tedarikciler`
--

LOCK TABLES `tedarikciler` WRITE;
/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (2,'Tedarikçi B','0987654321','Ankara','03121234567','b@tedarikci.com','Mehmet Kaya','İkinci tedarikçi'),(3,'Tedarikçi Abdi','1234567890','İstanbul','02121234567','a@tedarikci.com','Ahmet Yılmaz','Temel tedarikçi'),(5,'Tedarikçi Avv','1234567890','İstanbul','02121234567','a@tedarikci.com','Ahmet Yılmaz','Temel tedarikçi'),(7,'Tedarikci Isim1','11111111101','Adres 1','5551112201','tedarikci1@example.com','Yetkili Kisi 1','Notlar 1'),(8,'Tedarikci Isim2','11111111102','Adres 2','5551112202','tedarikci2@example.com','Yetkili Kisi 2','Notlar 2'),(9,'Tedarikci Isim3','11111111103','Adres 3','5551112203','tedarikci3@example.com','Yetkili Kisi 3','Notlar 3'),(10,'Tedarikci Isim4','11111111104','Adres 4','5551112204','tedarikci4@example.com','Yetkili Kisi 4','Notlar 4'),(11,'Tedarikci Isim5','11111111105','Adres 5','5551112205','tedarikci5@example.com','Yetkili Kisi 5','Notlar 5'),(12,'Tedarikci Isim6','11111111106','Adres 6','5551112206','tedarikci6@example.com','Yetkili Kisi 6','Notlar 6'),(13,'Tedarikci Isim7','11111111107','Adres 7','5551112207','tedarikci7@example.com','Yetkili Kisi 7','Notlar 7'),(14,'Tedarikci Isim8','11111111108','Adres 8','5551112208','tedarikci8@example.com','Yetkili Kisi 8','Notlar 8'),(15,'Tedarikci Isim9','11111111109','Adres 9','5551112209','tedarikci9@example.com','Yetkili Kisi 9','Notlar 9'),(17,'Tedarikci Isim11','11111111111','Adres 11','5551112211','tedarikci11@example.com','Yetkili Kisi 11','Notlar 11'),(18,'Tedarikci Isim12','11111111112','Adres 12','5551112212','tedarikci12@example.com','Yetkili Kisi 12','Notlar 12'),(20,'Tedarikci Isim14','11111111114','Adres 14','5551112214','tedarikci14@example.com','Yetkili Kisi 14','Notlar 14'),(21,'Tedarikci Isim15','11111111115','Adres 15','5551112215','tedarikci15@example.com','Yetkili Kisi 15','Notlar 15'),(23,'Tedarikci Isim17','11111111117','Adres 17','5551112217','tedarikci17@example.com','Yetkili Kisi 17','Notlar 17'),(24,'Tedarikci Isim18','11111111118','Adres 18','5551112218','tedarikci18@example.com','Yetkili Kisi 18','Notlar 18'),(25,'Tedarikci Isim19','11111111119','Adres 19','5551112219','tedarikci19@example.com','Yetkili Kisi 19','Notlar 19'),(26,'Tedarikci Isim20','11111111120','Adres 20','5551112220','tedarikci20@example.com','Yetkili Kisi 20','Notlar 20'),(27,'Tedarikci Isim21','11111111121','Adres 21','5551112221','tedarikci21@example.com','Yetkili Kisi 21','Notlar 21'),(28,'Tedarikci Isim22','11111111122','Adres 22','5551112222','tedarikci22@example.com','Yetkili Kisi 22','Notlar 22'),(29,'Tedarikci Isim23','11111111123','Adres 23','5551112223','tedarikci23@example.com','Yetkili Kisi 23','Notlar 23'),(30,'Tedarikci Isim24','11111111124','Adres 24','5551112224','tedarikci24@example.com','Yetkili Kisi 24','Notlar 24'),(31,'Tedarikci Isim25','11111111125','Adres 25','5551112225','tedarikci25@example.com','Yetkili Kisi 25','Notlar 25'),(32,'Tedarikci Isim26','11111111126','Adres 26','5551112226','tedarikci26@example.com','Yetkili Kisi 26','Notlar 26'),(33,'Tedarikci Isim27','11111111127','Adres 27','5551112227','tedarikci27@example.com','Yetkili Kisi 27','Notlar 27'),(34,'Tedarikci Isim28','11111111128','Adres 28','5551112228','tedarikci28@example.com','Yetkili Kisi 28','Notlar 28'),(35,'Tedarikci Isim29','11111111129','Adres 29','5551112229','tedarikci29@example.com','Yetkili Kisi 29','Notlar 29'),(36,'Tedarikci Isim30','11111111130','Adres 30','5551112230','tedarikci30@example.com','Yetkili Kisi 30','Notlar 30'),(37,'Tedarikci Isim31','11111111131','Adres 31','5551112231','tedarikci31@example.com','Yetkili Kisi 31','Notlar 31'),(38,'Tedarikci Isim32','11111111132','Adres 32','5551112232','tedarikci32@example.com','Yetkili Kisi 32','Notlar 32'),(39,'Tedarikci Isim33','11111111133','Adres 33','5551112233','tedarikci33@example.com','Yetkili Kisi 33','Notlar 33'),(40,'Tedarikci Isim34','11111111134','Adres 34','5551112234','tedarikci34@example.com','Yetkili Kisi 34','Notlar 34'),(41,'Tedarikci Isim35','11111111135','Adres 35','5551112235','tedarikci35@example.com','Yetkili Kisi 35','Notlar 35'),(42,'Tedarikci Isim36','11111111136','Adres 36','5551112236','tedarikci36@example.com','Yetkili Kisi 36','Notlar 36'),(43,'Tedarikci Isim37','11111111137','Adres 37','5551112237','tedarikci37@example.com','Yetkili Kisi 37','Notlar 37'),(44,'Tedarikci Isim38','11111111138','Adres 38','5551112238','tedarikci38@example.com','Yetkili Kisi 38','Notlar 38'),(45,'Tedarikci Isim39','11111111139','Adres 39','5551112239','tedarikci39@example.com','Yetkili Kisi 39','Notlar 39'),(46,'Tedarikci Isim40','11111111140','Adres 40','5551112240','tedarikci40@example.com','Yetkili Kisi 40','Notlar 40'),(47,'Tedarikci Isim41','11111111141','Adres 41','5551112241','tedarikci41@example.com','Yetkili Kisi 41','Notlar 41'),(48,'Tedarikci Isim42','11111111142','Adres 42','5551112242','tedarikci42@example.com','Yetkili Kisi 42','Notlar 42'),(49,'Tedarikci Isim43','11111111143','Adres 43','5551112243','tedarikci43@example.com','Yetkili Kisi 43','Notlar 43'),(50,'Tedarikci Isim44','11111111144','Adres 44','5551112244','tedarikci44@example.com','Yetkili Kisi 44','Notlar 44'),(51,'Tedarikci Isim45','11111111145','Adres 45','5551112245','tedarikci45@example.com','Yetkili Kisi 45','Notlar 45'),(52,'Tedarikci Isim46','11111111146','Adres 46','5551112246','tedarikci46@example.com','Yetkili Kisi 46','Notlar 46'),(53,'Tedarikci Isim47','11111111147','Adres 47','5551112247','tedarikci47@example.com','Yetkili Kisi 47','Notlar 47'),(54,'Tedarikci Isim48','11111111148','Adres 48','5551112248','tedarikci48@example.com','Yetkili Kisi 48','Notlar 48'),(55,'Tedarikci Isim49','11111111149','Adres 49','5551112249','tedarikci49@example.com','Yetkili Kisi 49','Notlar 49'),(56,'Tedarikci Isim50','11111111150','Adres 50','5551112250','tedarikci50@example.com','Yetkili Kisi 50','Notlar 50'),(57,'Tedarikci Isim51','11111111151','Adres 51','5551112251','tedarikci51@example.com','Yetkili Kisi 51','Notlar 51'),(58,'Tedarikci Isim52','11111111152','Adres 52','5551112252','tedarikci52@example.com','Yetkili Kisi 52','Notlar 52'),(59,'Tedarikci Isim53','11111111153','Adres 53','5551112253','tedarikci53@example.com','Yetkili Kisi 53','Notlar 53'),(60,'Tedarikci Isim54','11111111154','Adres 54','5551112254','tedarikci54@example.com','Yetkili Kisi 54','Notlar 54'),(61,'Tedarikci Isim55','11111111155','Adres 55','5551112255','tedarikci55@example.com','Yetkili Kisi 55','Notlar 55'),(62,'Tedarikci Isim56','11111111156','Adres 56','5551112256','tedarikci56@example.com','Yetkili Kisi 56','Notlar 56'),(63,'Tedarikci Isim57','11111111157','Adres 57','5551112257','tedarikci57@example.com','Yetkili Kisi 57','Notlar 57'),(64,'Tedarikci Isim58','11111111158','Adres 58','5551112258','tedarikci58@example.com','Yetkili Kisi 58','Notlar 58'),(65,'Tedarikci Isim59','11111111159','Adres 59','5551112259','tedarikci59@example.com','Yetkili Kisi 59','Notlar 59'),(66,'Tedarikci Isim60','11111111160','Adres 60','5551112260','tedarikci60@example.com','Yetkili Kisi 60','Notlar 60'),(67,'Tedarikci Isim61','11111111161','Adres 61','5551112261','tedarikci61@example.com','Yetkili Kisi 61','Notlar 61'),(68,'Tedarikci Isim62','11111111162','Adres 62','5551112262','tedarikci62@example.com','Yetkili Kisi 62','Notlar 62'),(69,'Tedarikci Isim63','11111111163','Adres 63','5551112263','tedarikci63@example.com','Yetkili Kisi 63','Notlar 63'),(70,'Tedarikci Isim64','11111111164','Adres 64','5551112264','tedarikci64@example.com','Yetkili Kisi 64','Notlar 64'),(71,'Tedarikci Isim65','11111111165','Adres 65','5551112265','tedarikci65@example.com','Yetkili Kisi 65','Notlar 65'),(72,'Tedarikci Isim66','11111111166','Adres 66','5551112266','tedarikci66@example.com','Yetkili Kisi 66','Notlar 66'),(73,'Tedarikci Isim67','11111111167','Adres 67','5551112267','tedarikci67@example.com','Yetkili Kisi 67','Notlar 67'),(74,'Tedarikci Isim68','11111111168','Adres 68','5551112268','tedarikci68@example.com','Yetkili Kisi 68','Notlar 68'),(75,'Tedarikci Isim69','11111111169','Adres 69','5551112269','tedarikci69@example.com','Yetkili Kisi 69','Notlar 69'),(76,'Tedarikci Isim70','11111111170','Adres 70','5551112270','tedarikci70@example.com','Yetkili Kisi 70','Notlar 70'),(77,'Tedarikci Isim71','11111111171','Adres 71','5551112271','tedarikci71@example.com','Yetkili Kisi 71','Notlar 71'),(78,'Tedarikci Isim72','11111111172','Adres 72','5551112272','tedarikci72@example.com','Yetkili Kisi 72','Notlar 72'),(79,'Tedarikci Isim73','11111111173','Adres 73','5551112273','tedarikci73@example.com','Yetkili Kisi 73','Notlar 73'),(80,'Tedarikci Isim74','11111111174','Adres 74','5551112274','tedarikci74@example.com','Yetkili Kisi 74','Notlar 74'),(81,'Tedarikci Isim75','11111111175','Adres 75','5551112275','tedarikci75@example.com','Yetkili Kisi 75','Notlar 75'),(82,'Tedarikci Isim76','11111111176','Adres 76','5551112276','tedarikci76@example.com','Yetkili Kisi 76','Notlar 76'),(83,'Tedarikci Isim77','11111111177','Adres 77','5551112277','tedarikci77@example.com','Yetkili Kisi 77','Notlar 77'),(84,'Tedarikci Isim78','11111111178','Adres 78','5551112278','tedarikci78@example.com','Yetkili Kisi 78','Notlar 78'),(85,'Tedarikci Isim79','11111111179','Adres 79','5551112279','tedarikci79@example.com','Yetkili Kisi 79','Notlar 79'),(86,'Tedarikci Isim80','11111111180','Adres 80','5551112280','tedarikci80@example.com','Yetkili Kisi 80','Notlar 80'),(87,'Tedarikci Isim81','11111111181','Adres 81','5551112281','tedarikci81@example.com','Yetkili Kisi 81','Notlar 81'),(88,'Tedarikci Isim82','11111111182','Adres 82','5551112282','tedarikci82@example.com','Yetkili Kisi 82','Notlar 82'),(89,'Tedarikci Isim83','11111111183','Adres 83','5551112283','tedarikci83@example.com','Yetkili Kisi 83','Notlar 83'),(90,'Tedarikci Isim84','11111111184','Adres 84','5551112284','tedarikci84@example.com','Yetkili Kisi 84','Notlar 84'),(91,'Tedarikci Isim85','11111111185','Adres 85','5551112285','tedarikci85@example.com','Yetkili Kisi 85','Notlar 85'),(92,'Tedarikci Isim86','11111111186','Adres 86','5551112286','tedarikci86@example.com','Yetkili Kisi 86','Notlar 86'),(93,'Tedarikci Isim87','11111111187','Adres 87','5551112287','tedarikci87@example.com','Yetkili Kisi 87','Notlar 87'),(94,'Tedarikci Isim88','11111111188','Adres 88','5551112288','tedarikci88@example.com','Yetkili Kisi 88','Notlar 88'),(95,'Tedarikci Isim89','11111111189','Adres 89','5551112289','tedarikci89@example.com','Yetkili Kisi 89','Notlar 89'),(96,'Tedarikci Isim90','11111111190','Adres 90','5551112290','tedarikci90@example.com','Yetkili Kisi 90','Notlar 90'),(97,'Tedarikci Isim91','11111111191','Adres 91','5551112291','tedarikci91@example.com','Yetkili Kisi 91','Notlar 91'),(98,'Tedarikci Isim92','11111111192','Adres 92','5551112292','tedarikci92@example.com','Yetkili Kisi 92','Notlar 92'),(99,'Tedarikci Isim93','11111111193','Adres 93','5551112293','tedarikci93@example.com','Yetkili Kisi 93','Notlar 93'),(100,'Tedarikci Isim94','11111111194','Adres 94','5551112294','tedarikci94@example.com','Yetkili Kisi 94','Notlar 94'),(101,'Tedarikci Isim95','11111111195','Adres 95','5551112295','tedarikci95@example.com','Yetkili Kisi 95','Notlar 95'),(102,'Tedarikci Isim96','11111111196','Adres 96','5551112296','tedarikci96@example.com','Yetkili Kisi 96','Notlar 96'),(103,'Tedarikci Isim97','11111111197','Adres 97','5551112297','tedarikci97@example.com','Yetkili Kisi 97','Notlar 97'),(104,'Tedarikci Isim98','11111111198','Adres 98','5551112298','tedarikci98@example.com','Yetkili Kisi 98','Notlar 98'),(105,'Tedarikci Isim99','11111111199','Adres 99','5551112299','tedarikci99@example.com','Yetkili Kisi 99','Notlar 99'),(108,'Test Tedarikçi','12345678901','Test Adresi','05551234567','test@tedarikci.com',NULL,NULL);
/*!40000 ALTER TABLE `tedarikciler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_agaci`
--

LOCK TABLES `urun_agaci` WRITE;
/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (13,22,'Perfume 2','esans','ES002','Lavender Essence',45.00,'urun'),(14,22,'Perfume 2','etiket','26','White Label',2.00,'urun'),(15,22,'Perfume 2','kapak','27','Plastic Cap',1.00,'urun'),(24,24,'Bergamot Essence','malzeme','31','Medium Box',1.00,'esans'),(25,30,'10 evo','esans','ES010','Bergamot Essence',10.00,'urun'),(26,30,'10 evo','etiket','37','Ambalaj Malzemesi 2',2.00,'urun'),(27,24,'Bergamot Essence','malzeme','37','Ambalaj Malzemesi 2',1.00,'esans');
/*!40000 ALTER TABLE `urun_agaci` ENABLE KEYS */;
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
  `kritik_stok_seviyesi` int(11) DEFAULT 0,
  `depo` varchar(255) DEFAULT NULL,
  `raf` varchar(100) DEFAULT NULL,
  `son_maliyet` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`urun_kodu`),
  UNIQUE KEY `unique_urun_ismi` (`urun_ismi`),
  UNIQUE KEY `urun_kodu` (`urun_kodu`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

LOCK TABLES `urunler` WRITE;
/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (22,'Perfume 2',NULL,97,'adet',150.00,10,'Main Warehouse','R002',NULL),(23,'Perfume 3',NULL,98,'adet',150.00,10,'Main Warehouse','R003',NULL),(24,'Perfume 4','null',78,'adet',150.00,10,'Main Warehouse','R004',NULL),(25,'Perfume 5',NULL,100,'adet',150.00,10,'Main Warehouse','R005',NULL),(26,'Perfume 6',NULL,78,'adet',150.00,10,'Main Warehouse','R006',NULL),(27,'Perfume 7',NULL,100,'adet',150.00,10,'Main Warehouse','R007',NULL),(28,'Perfume 8',NULL,-54,'adet',150.00,10,'Main Warehouse','R008',NULL),(30,'10 evo','null',91,'adet',150.00,10,'Yeni Depo','Raf 3',0.00),(37,'Roz Koku','Premium parfüm',140,'adet',125.00,20,'Ana Depo','A-1',NULL),(38,'Lavanta Koku','Organik içerik',180,'adet',95.00,25,'Ana Depo','A-2',NULL),(39,'Misk Koku','Uzun süreli koku',85,'adet',145.00,15,'Ana Depo','A-3',NULL),(40,'Portakal Koku','Fırça uygulamalı',120,'adet',85.00,20,'Ana Depo','A-4',NULL),(41,'Sandal Ahşabı','Lüks parfüm',65,'adet',165.00,10,'Ana Depo','A-5',NULL),(42,'Papatya Baharı','Yazlık koku',220,'adet',75.00,30,'Ana Depo','B-1',NULL),(43,'Zambak Gece','Gecelik koku',90,'adet',135.00,20,'Ana Depo','B-2',NULL),(44,'Yeşil Çay','Fresko koku',180,'adet',80.00,25,'Ana Depo','B-3',NULL),(45,'Muh','Doğu aroması',70,'adet',155.00,15,'Ana Depo','B-4',NULL),(46,'Vanilya Düş','Tatlı ve yumuşak',140,'adet',105.00,20,'Ana Depo','B-5',NULL),(47,'Deniz Esintisi','Yazlık deniz kokusu',160,'adet',90.00,25,'Ana Depo','C-1',NULL),(48,'Karanfil Baharı','Yeni sezon',110,'adet',120.00,15,'Ana Depo','C-2',NULL),(49,'Kahve ve Kakule','Yeni koleksiyon',45,'adet',175.00,10,'Ana Depo','C-3',NULL),(50,'Yaban Mersini','Meyveli',130,'adet',100.00,20,'Ana Depo','C-4',NULL),(51,'Papatya ve Bal','Organik',100,'adet',115.00,15,'Ana Depo','C-5',NULL),(52,'Deniz Tuzu','Tuz kokulu',190,'adet',88.00,30,'Ana Depo','D-1',NULL),(53,'Sarı Zambak','Yazlık',75,'adet',140.00,15,'Ana Depo','D-2',NULL),(54,'Portakal Çiçeği','Turuncu',210,'adet',92.00,25,'Ana Depo','D-3',NULL),(55,'Kestane','Kışlık',55,'adet',150.00,10,'Ana Depo','D-4',NULL),(56,'Misk ve Sandal','Lüks',80,'adet',160.00,15,'Ana Depo','D-5',NULL),(57,'Lavanta ve Bal','Organik',175,'adet',108.00,20,'Ana Depo','E-1',NULL),(58,'Fıstık Ezmesi','Yeni',125,'adet',118.00,15,'Ana Depo','E-2',NULL),(59,'Karamelli Kahve','Premium',60,'adet',185.00,8,'Ana Depo','E-3',NULL),(60,'Çilek ve Vanilya','Meyveli',155,'adet',125.00,20,'Ana Depo','E-4',NULL),(61,'Ağustosböceği','Yazlık',74,'adet',130.00,15,'Ana Depo','E-5',NULL),(62,'Gümüşlük','Deniz',145,'adet',98.00,20,'Ana Depo','F-1',NULL),(63,'Sarımsak','Yeni',30,'adet',75.00,5,'Ana Depo','F-2',NULL),(64,'Köpürme','Yeni',200,'adet',85.00,30,'Ana Depo','F-3',NULL),(65,'Sıcak Çikolata','Kışlık',115,'adet',135.00,15,'Ana Depo','F-4',NULL),(66,'Zencefil','Baharat',135,'adet',110.00,20,'Ana Depo','F-5',NULL),(67,'Beyaz Çikolata','Tatlı',90,'adet',145.00,10,'Ana Depo','G-1',NULL),(68,'Kırmızı Gül','Lüks',70,'adet',165.00,15,'Ana Depo','G-2',NULL),(69,'Yeşil Çay ve Limon','Fresko',180,'adet',95.00,25,'Ana Depo','G-3',NULL),(70,'Sarı Portakal','Meyve',105,'adet',115.00,20,'Ana Depo','G-4',NULL),(71,'Siyah Biber','Baharat',50,'adet',170.00,10,'Ana Depo','G-5',NULL),(72,'Lavanta ve Misk','Organik',140,'adet',125.00,20,'Ana Depo','H-1',NULL),(73,'Kırmızı Biber','Baharat',40,'adet',150.00,5,'Ana Depo','H-2',NULL),(74,'Mavi Yıldız','Yeni',160,'adet',105.00,25,'Ana Depo','H-3',NULL),(75,'Sarı Zambak ve Misk','Organik',85,'adet',135.00,15,'Ana Depo','H-4',NULL),(76,'Kırmızı Elma','Meyve',125,'adet',90.00,20,'Ana Depo','H-5',NULL),(77,'Fıstık ve Vanilya','Tatlı',100,'adet',140.00,15,'Ana Depo','I-1',NULL),(78,'Mavi Deniz','Deniz',190,'adet',85.00,30,'Ana Depo','I-2',NULL),(79,'Kırmızı Gül ve Portakal','Lüks',42,'adet',155.00,10,'Ana Depo','I-3',NULL),(80,'Sarı Portakal ve Zencefil','Meyve ve baharat',110,'adet',125.00,20,'Ana Depo','I-4',NULL),(81,'Sarı Zambak ve Bal','Organik',95,'adet',130.00,15,'Ana Depo','I-5',NULL),(82,'Fıstık ve Çilek','Tatlı',135,'adet',118.00,20,'Ana Depo','J-1',NULL),(83,'Mavi Yıldız ve Lavanta','Yeni',155,'adet',100.00,25,'Ana Depo','J-2',NULL),(84,'Kırmızı Elma ve Bal','Meyve',115,'adet',120.00,20,'Ana Depo','J-3',NULL),(85,'Sarı Portakal ve Vanilya','Tatlı',80,'adet',140.00,15,'Ana Depo','J-4',NULL),(86,'Mavi Deniz ve Misk','Deniz',145,'adet',115.00,20,'Ana Depo','J-5',NULL),(87,'Test Ürün',NULL,50,'adet',25.99,5,'Ankara Depo','A1',NULL);
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
/*!50001 VIEW `v_urun_maliyetleri` AS select `ua`.`urun_kodu` AS `urun_kodu`,sum(`ua`.`bilesen_miktari` * case when `m`.`alis_fiyati` is not null then case `m`.`para_birimi` when 'USD' then coalesce(`m`.`alis_fiyati`,0) * (select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'dolar_kuru') when 'EUR' then coalesce(`m`.`alis_fiyati`,0) * (select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'euro_kuru') else coalesce(`m`.`alis_fiyati`,0) end when `vem`.`toplam_maliyet` is not null then coalesce(`vem`.`toplam_maliyet`,0) else 0 end) AS `teorik_maliyet` from ((`urun_agaci` `ua` left join `malzemeler` `m` on(`ua`.`bilesen_kodu` = `m`.`malzeme_kodu`)) left join `v_esans_maliyetleri` `vem` on(`ua`.`bilesen_kodu` = `vem`.`esans_kodu`)) where `ua`.`agac_turu` = 'urun' group by `ua`.`urun_kodu` */;
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

-- Dump completed on 2025-11-27  9:40:29

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.22-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: parfum_erp
-- ------------------------------------------------------
-- Server version	10.6.22-MariaDB-0ubuntu0.22.04.1

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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ayarlar` (
  `ayar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ayar_anahtar` varchar(255) NOT NULL,
  `ayar_deger` varchar(255) NOT NULL,
  PRIMARY KEY (`ayar_id`),
  UNIQUE KEY `ayar_anahtar` (`ayar_anahtar`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

LOCK TABLES `ayarlar` WRITE;
/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.8500'),(2,'euro_kuru','50.5070'),(3,'son_otomatik_yedek_tarihi','2026-02-26 00:14:06'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170');
/*!40000 ALTER TABLE `ayarlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cek_kasasi`
--

DROP TABLE IF EXISTS `cek_kasasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cerceve_sozlesmeler`
--

LOCK TABLES `cerceve_sozlesmeler` WRITE;
/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (1,7,'ADEM ',16,'	CHANEL TENDER, takım',1.90,'USD',999999999,0,'2026-02-25','2026-04-25','Admin User','2026-02-25 01:36:06','',1,'TL',NULL),(2,6,'KIRMIZIGÜL',16,'	CHANEL TENDER, takım',2.60,'USD',999999999,0,'2026-02-25','2026-03-03','Admin User','2026-02-25 01:38:59','',1,'TL',NULL),(3,6,'KIRMIZIGÜL',6,'CHANEL BLU, takım',2.50,'USD',999999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 01:39:42','',1,'TL',NULL),(4,6,'KIRMIZIGÜL',22,'DİOR SAVAGE, takım',2.70,'USD',99999999,0,'2026-02-25','2026-04-22','Admin User','2026-02-25 01:40:26','',1,'TL',NULL),(5,6,'KIRMIZIGÜL',38,'LİBRE KLASİK, takım',2.60,'USD',99999999,0,'2026-02-25','2026-03-27','Admin User','2026-02-25 01:40:54','',1,'TL',NULL),(6,6,'KIRMIZIGÜL',30,'DİOR SAVAGE ELİXSER, takım',3.00,'USD',9999999,0,'2026-02-25','2026-02-19','Admin User','2026-02-25 01:42:02','',1,'TL',NULL),(7,7,'ADEM ',6,'CHANEL BLU, takım',2.20,'USD',9999999,0,'2026-02-25','2026-03-11','Admin User','2026-02-25 01:43:14','',1,'TL',NULL),(8,7,'ADEM ',38,'LİBRE KLASİK, takım',2.10,'USD',999999999,0,'2026-02-25','2026-03-09','Admin User','2026-02-25 01:43:45','',1,'TL',NULL),(9,7,'ADEM ',30,'DİOR SAVAGE ELİXSER, takım',2.30,'USD',999999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 01:44:40','',1,'TL',NULL),(10,7,'ADEM ',22,'DİOR SAVAGE, takım',2.50,'USD',9999999,0,'2026-02-25','2026-03-18','Admin User','2026-02-25 01:46:03','',1,'TL',NULL),(11,8,'LUZİ ',14,'	CHANEL TENDER, ham esans',100.00,'USD',99999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 01:49:36','',1,'TL',NULL),(12,8,'LUZİ ',7,'CHANEL BLU, ham esans',150.00,'USD',999999999,0,'2026-02-25','2026-04-24','Admin User','2026-02-25 02:03:25','',1,'TL',NULL),(13,8,'LUZİ ',23,'DİOR SAVAGE, ham esans',160.00,'USD',999999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:04:50','',1,'TL',NULL),(14,8,'LUZİ ',23,'DİOR SAVAGE, ham esans',160.00,'USD',9999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:06:12','',1,'TL',NULL),(15,8,'LUZİ ',39,'LİBRE KLASİK, ham esans',190.00,'USD',999999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:06:54','',1,'TL',NULL),(16,4,'SARI ETİKET',9,'	CHANEL TENDER, etiket',2.00,'TL',999999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:07:31','',1,'TL',NULL),(17,4,'SARI ETİKET',1,'CHANEL BLU, etiket',2.20,'TL',99999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:07:57','',1,'TL',NULL),(18,4,'SARI ETİKET',25,'DİOR SAVAGE ELİXSER, etiket',2.10,'TL',999999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:08:35','',1,'TL',NULL),(19,4,'SARI ETİKET',17,'DİOR SAVAGE, etiket',1.90,'TL',999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:09:07','',1,'TL',NULL),(20,4,'SARI ETİKET',33,'LİBRE KLASİK, etiket',2.40,'TL',999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:09:38','',1,'TL',NULL),(21,10,'TEVFİK ',14,'	CHANEL TENDER, ham esans',75.00,'EUR',9999999,0,'2026-02-26','2026-03-25','Admin User','2026-02-25 02:11:24','',1,'TL',NULL),(22,10,'TEVFİK ',7,'CHANEL BLU, ham esans',80.00,'EUR',999999999,0,'2026-02-25','2026-03-12','Admin User','2026-02-25 02:11:53','',1,'TL',NULL),(23,10,'TEVFİK ',31,'DİOR SAVAGE ELİXSER, ham esans',68.00,'EUR',999999999,0,'2026-02-25','2026-03-26','Admin User','2026-02-25 02:12:24','',1,'TL',NULL),(24,10,'TEVFİK ',23,'DİOR SAVAGE, ham esans',62.00,'EUR',999999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:12:52','',1,'TL',NULL),(25,10,'TEVFİK ',39,'LİBRE KLASİK, ham esans',59.00,'EUR',999999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:13:30','',1,'TL',NULL),(26,13,'ZÜLKÜF ',41,'ALKOL',1.60,'USD',99999999,0,'2026-02-25','2026-03-20','Admin User','2026-02-25 02:16:22','',1,'TL',NULL),(27,1,'ŞENER ',10,'	CHANEL TENDER, kutu',0.40,'USD',9999999,0,'2026-02-25','2026-03-25','Admin User','2026-02-25 02:17:23','',1,'TL',NULL),(28,7,'ADEM ',47,'ARAF, takım',2.20,'USD',99999999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:18:54','',1,'TL',NULL),(29,2,'BEKİR ',45,'ARAF, kutu',0.50,'USD',999999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:19:41','',1,'TL',NULL),(30,3,'ESENGÜL ',45,'ARAF, kutu',0.40,'USD',999999999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:20:50','',1,'TL',NULL),(31,8,'LUZİ ',44,'ARAF, ham esans',95.00,'USD',99999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:21:51','',1,'TL',NULL),(32,9,'SELPA ',44,'ARAF, ham esans',85.00,'USD',9999999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:22:38','',1,'TL',NULL),(33,1,'ŞENER ',45,'ARAF, kutu',0.42,'USD',999999999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:23:15','',1,'TL',NULL),(34,12,'RAMAZAN ',43,'ARAF, paket',8.00,'TL',999999999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:24:12','',1,'TL',NULL),(35,11,'GÖKHAN',1,'CHANEL BLU, etiket',1.00,'TL',999999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:24:52','',1,'TL',NULL),(36,4,'SARI ETİKET',42,'ARAF, etiket',1.00,'TL',9999999,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:25:56','',1,'TL',NULL),(37,11,'GÖKHAN',46,'ARAF, jelatin',1.20,'TL',9999,0,'2026-02-26','2026-03-28','Admin User','2026-02-26 22:27:16','',1,'TL',NULL),(38,6,'KIRMIZIGÜL',47,'ARAF, takım',2.10,'USD',9999899,0,'2026-02-26','2026-03-26','Admin User','2026-02-26 22:34:53','',1,'TL',NULL);
/*!40000 ALTER TABLE `cerceve_sozlesmeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `cerceve_sozlesmeler_gecerlilik`
--

DROP TABLE IF EXISTS `cerceve_sozlesmeler_gecerlilik`;
/*!50001 DROP VIEW IF EXISTS `cerceve_sozlesmeler_gecerlilik`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esanslar`
--

LOCK TABLES `esanslar` WRITE;
/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (1,'ES-260225-258','CHANEL BLU, Esans','',0.00,'lt',1.00,'E 101','DİOR SAVAGE'),(2,'ES-260225-564','	CHANEL TENDER, Esans','',0.00,'lt',1.00,'E 102','DİOR SAVAGE ELİXSER'),(3,'ES-260225-127','DİOR SAVAGE, Esans','',0.00,'lt',1.00,'W 501','CHANEL BLU'),(4,'ES-260225-305','DİOR SAVAGE ELİXSER, Esans','',0.00,'lt',1.00,'W 502','CHANEL TENDER'),(5,'ES-260225-941','LİBRE KLASİK, Esans','',0.00,'lt',1.00,'W 503','LİBRE KLASİK'),(6,'ES-260226-636','ARAF, Esans','',0.00,'lt',1.00,NULL,NULL);
/*!40000 ALTER TABLE `esanslar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gelir_taksit_planlari`
--

DROP TABLE IF EXISTS `gelir_taksit_planlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gider_yonetimi`
--

LOCK TABLES `gider_yonetimi` WRITE;
/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (1,'2026-02-13',17140.00,'Malzeme Gideri','ARMAF A, kutu için 1000 adet ara ödeme (400,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','ŞENER','USD',NULL),(2,'2026-02-13',10000.00,'Malzeme Gideri','SU için 10000 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','MERKEZ ŞEBEKE','TL',NULL),(3,'2026-02-13',1000.00,'Malzeme Gideri','ARMAF A, jelatin için 1000 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','Gökhan ','TL',NULL);
/*!40000 ALTER TABLE `gider_yonetimi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `is_merkezleri`
--

DROP TABLE IF EXISTS `is_merkezleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `is_merkezleri` (
  `is_merkezi_id` int(11) NOT NULL AUTO_INCREMENT,
  `isim` varchar(255) NOT NULL,
  `aciklama` text DEFAULT NULL,
  PRIMARY KEY (`is_merkezi_id`),
  UNIQUE KEY `isim` (`isim`),
  UNIQUE KEY `is_merkezi_id` (`is_merkezi_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `is_merkezleri`
--

LOCK TABLES `is_merkezleri` WRITE;
/*!40000 ALTER TABLE `is_merkezleri` DISABLE KEYS */;
INSERT INTO `is_merkezleri` VALUES (1,'ABUBEKİR ÖNEL','ORTAK'),(2,'AHMET ERSİN','EMEKTAR');
/*!40000 ALTER TABLE `is_merkezleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kasa_hareketleri`
--

DROP TABLE IF EXISTS `kasa_hareketleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasa_hareketleri`
--

LOCK TABLES `kasa_hareketleri` WRITE;
/*!40000 ALTER TABLE `kasa_hareketleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `kasa_hareketleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kasa_islemleri`
--

DROP TABLE IF EXISTS `kasa_islemleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_tablosu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime DEFAULT current_timestamp(),
  `kullanici_adi` varchar(255) NOT NULL,
  `log_metni` text NOT NULL,
  `islem_turu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tablosu`
--

LOCK TABLES `log_tablosu` WRITE;
/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2026-02-25 01:19:12','Admin User','cek_kasasi, cerceve_sozlesmeler, esans_is_emirleri, esans_is_emri_malzeme_listesi, esanslar, gelir_yonetimi, kasa_hareketleri, kasa_islemleri, log_tablosu, lokasyonlar, malzemeler, musteriler, satinalma_siparis_kalemleri, satinalma_siparisler, sirket_kasasi, stok_hareket_kayitlari, stok_hareketleri_sozlesmeler, tanklar, tedarikciler, urunler tabloları temizlendi','DELETE','2026-02-24 22:19:12'),(2,'2026-02-25 01:19:32','Admin User','DEPO A deposuna A rafı eklendi','CREATE','2026-02-24 22:19:32'),(3,'2026-02-25 01:19:42','Admin User','DEPO B deposuna A rafı eklendi','CREATE','2026-02-24 22:19:42'),(4,'2026-02-25 01:19:53','Admin User','DEPO C deposuna A rafı eklendi','CREATE','2026-02-24 22:19:53'),(5,'2026-02-25 01:20:09','Admin User','DEPO TAKIM deposuna A rafı eklendi','CREATE','2026-02-24 22:20:09'),(6,'2026-02-25 01:20:21','Admin User','DEPO ETİKET deposuna A rafı eklendi','CREATE','2026-02-24 22:20:21'),(7,'2026-02-25 01:20:34','Admin User','MERKEZ ESANS deposuna A rafı eklendi','CREATE','2026-02-24 22:20:34'),(8,'2026-02-25 01:21:17','Admin User','DİOR SAVAGE adlı tank sisteme eklendi','CREATE','2026-02-24 22:21:17'),(9,'2026-02-25 01:22:05','Admin User','DİOR SAVAGE ELİXSER adlı tank sisteme eklendi','CREATE','2026-02-24 22:22:05'),(10,'2026-02-25 01:22:29','Admin User','CHANEL BLU adlı tank sisteme eklendi','CREATE','2026-02-24 22:22:29'),(11,'2026-02-25 01:22:55','Admin User','CHANEL TENDER adlı tank sisteme eklendi','CREATE','2026-02-24 22:22:55'),(12,'2026-02-25 01:23:31','Admin User','LİBRE KLASİK adlı tank sisteme eklendi','CREATE','2026-02-24 22:23:31'),(13,'2026-02-25 01:23:56','Admin User','ABUBEKİR ÖNEL iş merkezi eklendi','CREATE','2026-02-24 22:23:56'),(14,'2026-02-25 01:24:13','Admin User','AHMET ERSİN iş merkezi eklendi','CREATE','2026-02-24 22:24:13'),(15,'2026-02-25 01:25:20','Admin User','CHANEL BLU ürünü sisteme eklendi','CREATE','2026-02-24 22:25:20'),(16,'2026-02-25 01:25:20','Admin User','Otomatik esans oluşturuldu: CHANEL BLU, Esans (Tank: E 101)','CREATE','2026-02-24 22:25:20'),(17,'2026-02-25 01:26:47','Admin User','	CHANEL TENDER ürünü sisteme eklendi','CREATE','2026-02-24 22:26:47'),(18,'2026-02-25 01:26:47','Admin User','Otomatik esans oluşturuldu: 	CHANEL TENDER, Esans (Tank: E 102)','CREATE','2026-02-24 22:26:47'),(19,'2026-02-25 01:28:13','Admin User','DİOR SAVAGE ürünü sisteme eklendi','CREATE','2026-02-24 22:28:13'),(20,'2026-02-25 01:28:14','Admin User','Otomatik esans oluşturuldu: DİOR SAVAGE, Esans (Tank: W 501)','CREATE','2026-02-24 22:28:14'),(21,'2026-02-25 01:29:16','Admin User','DİOR SAVAGE ELİXSER ürünü sisteme eklendi','CREATE','2026-02-24 22:29:16'),(22,'2026-02-25 01:29:16','Admin User','Otomatik esans oluşturuldu: DİOR SAVAGE ELİXSER, Esans (Tank: W 502)','CREATE','2026-02-24 22:29:16'),(23,'2026-02-25 01:30:19','Admin User','LİBRE KLASİK ürünü sisteme eklendi','CREATE','2026-02-24 22:30:19'),(24,'2026-02-25 01:30:19','Admin User','Otomatik esans oluşturuldu: LİBRE KLASİK, Esans (Tank: W 503)','CREATE','2026-02-24 22:30:19'),(25,'2026-02-25 01:30:45','Admin User','ŞENER  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:30:45'),(26,'2026-02-25 01:30:58','Admin User','BEKİR  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:30:58'),(27,'2026-02-25 01:31:13','Admin User','ESENGÜL  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:31:13'),(28,'2026-02-25 01:31:33','Admin User','SARI ETİKET tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:31:33'),(29,'2026-02-25 01:31:49','Admin User','ÖZGÜR  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:31:49'),(30,'2026-02-25 01:32:03','Admin User','KIRMIZIGÜL tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:32:03'),(31,'2026-02-25 01:32:23','Admin User','ADEM  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:32:23'),(32,'2026-02-25 01:32:42','Admin User','LUZİ  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:32:42'),(33,'2026-02-25 01:32:56','Admin User','SELPA  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:32:56'),(34,'2026-02-25 01:33:07','Admin User','TEVFİK  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:33:07'),(35,'2026-02-25 01:33:43','Admin User','GÖKHAN tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:33:43'),(36,'2026-02-25 01:33:57','Admin User','RAMAZAN  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:33:57'),(37,'2026-02-25 01:34:45','Admin User','ZÜLKÜR  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:34:45'),(38,'2026-02-25 01:34:55','Admin User','ALİ  tedarikçisi sisteme eklendi','CREATE','2026-02-24 22:34:55'),(39,'2026-02-25 01:36:06','Admin User','ADEM  tedarikçisine 	CHANEL TENDER, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:36:06'),(40,'2026-02-25 01:38:59','Admin User','KIRMIZIGÜL tedarikçisine 	CHANEL TENDER, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:38:59'),(41,'2026-02-25 01:39:42','Admin User','KIRMIZIGÜL tedarikçisine CHANEL BLU, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:39:42'),(42,'2026-02-25 01:40:26','Admin User','KIRMIZIGÜL tedarikçisine DİOR SAVAGE, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:40:26'),(43,'2026-02-25 01:40:54','Admin User','KIRMIZIGÜL tedarikçisine LİBRE KLASİK, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:40:54'),(44,'2026-02-25 01:42:02','Admin User','KIRMIZIGÜL tedarikçisine DİOR SAVAGE ELİXSER, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:42:02'),(45,'2026-02-25 01:43:14','Admin User','ADEM  tedarikçisine CHANEL BLU, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:43:14'),(46,'2026-02-25 01:43:45','Admin User','ADEM  tedarikçisine LİBRE KLASİK, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:43:45'),(47,'2026-02-25 01:44:40','Admin User','ADEM  tedarikçisine DİOR SAVAGE ELİXSER, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:44:40'),(48,'2026-02-25 01:46:04','Admin User','ADEM  tedarikçisine DİOR SAVAGE, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:46:04'),(49,'2026-02-25 01:49:36','Admin User','LUZİ  tedarikçisine 	CHANEL TENDER, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 22:49:36'),(50,'2026-02-25 02:02:06','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-24 23:02:06'),(51,'2026-02-25 02:03:25','Admin User','LUZİ  tedarikçisine CHANEL BLU, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:03:25'),(52,'2026-02-25 02:04:50','Admin User','LUZİ  tedarikçisine DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:04:50'),(53,'2026-02-25 02:06:12','Admin User','LUZİ  tedarikçisine DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:06:12'),(54,'2026-02-25 02:06:54','Admin User','LUZİ  tedarikçisine LİBRE KLASİK, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:06:54'),(55,'2026-02-25 02:07:31','Admin User','SARI ETİKET tedarikçisine 	CHANEL TENDER, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:07:31'),(56,'2026-02-25 02:07:57','Admin User','SARI ETİKET tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:07:57'),(57,'2026-02-25 02:08:35','Admin User','SARI ETİKET tedarikçisine DİOR SAVAGE ELİXSER, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:08:35'),(58,'2026-02-25 02:09:07','Admin User','SARI ETİKET tedarikçisine DİOR SAVAGE, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:09:07'),(59,'2026-02-25 02:09:38','Admin User','SARI ETİKET tedarikçisine LİBRE KLASİK, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:09:38'),(60,'2026-02-25 02:11:24','Admin User','TEVFİK  tedarikçisine 	CHANEL TENDER, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:11:24'),(61,'2026-02-25 02:11:53','Admin User','TEVFİK  tedarikçisine CHANEL BLU, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:11:53'),(62,'2026-02-25 02:12:24','Admin User','TEVFİK  tedarikçisine DİOR SAVAGE ELİXSER, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:12:24'),(63,'2026-02-25 02:12:52','Admin User','TEVFİK  tedarikçisine DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:12:52'),(64,'2026-02-25 02:13:30','Admin User','TEVFİK  tedarikçisine LİBRE KLASİK, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:13:30'),(65,'2026-02-25 02:14:22','Admin User','ZÜLKÜR  tedarikçisi ZÜLKÜF  olarak güncellendi','UPDATE','2026-02-24 23:14:22'),(66,'2026-02-25 02:15:34','Admin User','ALKOL malzemesi sisteme eklendi','CREATE','2026-02-24 23:15:34'),(67,'2026-02-25 02:16:22','Admin User','ZÜLKÜF  tedarikçisine ALKOL malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:16:22'),(68,'2026-02-25 02:17:23','Admin User','ŞENER  tedarikçisine 	CHANEL TENDER, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-24 23:17:23'),(69,'2026-02-25 02:18:08','Admin User','OSMAN GÜZEN müşterisi sisteme eklendi','CREATE','2026-02-24 23:18:08'),(70,'2026-02-25 02:18:36','Admin User','HAMZA  müşterisi sisteme eklendi','CREATE','2026-02-24 23:18:36'),(71,'2026-02-25 02:20:09','Admin User','urun_agaci tabloları temizlendi','DELETE','2026-02-24 23:20:09'),(72,'2026-02-25 02:21:29','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-02-24 23:21:29'),(73,'2026-02-26 16:04:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-26 13:04:04'),(74,'2026-02-26 16:05:16','Admin User','	CHANEL TENDER, Esans ürün ağacına 	CHANEL TENDER, su bileşeni eklendi','CREATE','2026-02-26 13:05:16'),(75,'2026-02-26 16:05:53','Admin User','	CHANEL TENDER, Esans ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-02-26 13:05:53'),(76,'2026-02-26 16:06:06','Admin User','	CHANEL TENDER, Esans ürün ağacındaki 	CHANEL TENDER, su bileşeni 	CHANEL TENDER, su olarak güncellendi','UPDATE','2026-02-26 13:06:06'),(77,'2026-02-26 16:06:33','Admin User','	CHANEL TENDER, Esans ürün ağacına 	CHANEL TENDER, ham esans bileşeni eklendi','CREATE','2026-02-26 13:06:33'),(78,'2026-02-26 16:07:29','Admin User','CHANEL BLU ürün ağacına 	CHANEL TENDER, su bileşeni eklendi','CREATE','2026-02-26 13:07:29'),(79,'2026-02-26 16:07:50','Admin User','CHANEL BLU ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-02-26 13:07:50'),(80,'2026-02-26 16:08:09','Admin User','CHANEL BLU ürün ağacına CHANEL BLU, ham esans bileşeni eklendi','CREATE','2026-02-26 13:08:09'),(81,'2026-02-26 21:18:25','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-26 18:18:25'),(82,'2026-02-26 21:22:46','Admin User','	CHANEL TENDER ürün ağacına 	CHANEL TENDER, ham esans bileşeni eklendi','CREATE','2026-02-26 18:22:46'),(83,'2026-02-26 21:23:11','Admin User','	CHANEL TENDER ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-02-26 18:23:11'),(84,'2026-02-26 21:23:43','Admin User','	CHANEL TENDER ürün ağacına 	CHANEL TENDER, su bileşeni eklendi','CREATE','2026-02-26 18:23:43'),(85,'2026-02-26 21:24:24','Admin User','DİOR SAVAGE ELİXSER, Esans ürün ağacına DİOR SAVAGE ELİXSER, ham esans bileşeni eklendi','CREATE','2026-02-26 18:24:24'),(86,'2026-02-26 21:25:03','Admin User','DİOR SAVAGE ELİXSER, Esans ürün ağacına DİOR SAVAGE ELİXSER, su bileşeni eklendi','CREATE','2026-02-26 18:25:03'),(87,'2026-02-26 21:27:10','Admin User','DİOR SAVAGE ELİXSER, Esans ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-02-26 18:27:10'),(88,'2026-02-26 21:27:41','Admin User','DİOR SAVAGE, Esans ürün ağacına DİOR SAVAGE, ham esans bileşeni eklendi','CREATE','2026-02-26 18:27:41'),(89,'2026-02-26 21:28:05','Admin User','DİOR SAVAGE, Esans ürün ağacına DİOR SAVAGE, su bileşeni eklendi','CREATE','2026-02-26 18:28:05'),(90,'2026-02-26 21:29:25','Admin User','DİOR SAVAGE, Esans ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-02-26 18:29:25'),(91,'2026-02-26 21:29:41','Admin User','DİOR SAVAGE, Esans ürün ağacındaki ALKOL bileşeni ALKOL olarak güncellendi','UPDATE','2026-02-26 18:29:41'),(92,'2026-02-26 21:33:20','Admin User','CHANEL BLU ürün ağacından 	CHANEL TENDER, su bileşeni silindi','DELETE','2026-02-26 18:33:20'),(93,'2026-02-26 21:33:28','Admin User','CHANEL BLU ürün ağacından ALKOL bileşeni silindi','DELETE','2026-02-26 18:33:28'),(94,'2026-02-26 21:33:32','Admin User','CHANEL BLU ürün ağacından CHANEL BLU, ham esans bileşeni silindi','DELETE','2026-02-26 18:33:32'),(95,'2026-02-26 21:33:37','Admin User','	CHANEL TENDER ürün ağacından 	CHANEL TENDER, su bileşeni silindi','DELETE','2026-02-26 18:33:37'),(96,'2026-02-26 21:33:44','Admin User','	CHANEL TENDER ürün ağacından 	CHANEL TENDER, ham esans bileşeni silindi','DELETE','2026-02-26 18:33:44'),(97,'2026-02-26 21:33:48','Admin User','	CHANEL TENDER ürün ağacından ALKOL bileşeni silindi','DELETE','2026-02-26 18:33:48'),(98,'2026-02-26 21:43:31','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-26 18:43:31'),(99,'2026-02-26 21:51:32','Admin User','KIRMIZIGÜL tedarikçisine PO-2026-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-02-26 18:51:32'),(100,'2026-02-26 22:04:37','Admin User','	CHANEL TENDER ürünü 	CHANEL TENDER olarak güncellendi','UPDATE','2026-02-26 19:04:37'),(101,'2026-02-26 22:07:47','Admin User','ARAF ürünü sisteme eklendi','CREATE','2026-02-26 19:07:47'),(102,'2026-02-26 22:07:48','Admin User','Otomatik esans oluşturuldu: ARAF, Esans (Tank: )','CREATE','2026-02-26 19:07:48'),(103,'2026-02-26 22:11:17','Admin User','ARAF, Esans ürün ağacına ARAF, ham esans bileşeni eklendi','CREATE','2026-02-26 19:11:17'),(104,'2026-02-26 22:11:36','Admin User','ARAF, Esans ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-02-26 19:11:36'),(105,'2026-02-26 22:13:11','Admin User','SU malzemesi sisteme eklendi','CREATE','2026-02-26 19:13:11'),(106,'2026-02-26 22:13:47','Admin User','ARAF, Esans ürün ağacına SU bileşeni eklendi','CREATE','2026-02-26 19:13:47'),(107,'2026-02-26 22:14:44','Admin User','ARAF ürün ağacındaki ARAF, Esans bileşeni ARAF, Esans olarak güncellendi','UPDATE','2026-02-26 19:14:44'),(108,'2026-02-26 22:16:59','Admin User','ARAF ürün ağacındaki ARAF, Esans bileşeni ARAF, Esans olarak güncellendi','UPDATE','2026-02-26 19:16:59'),(109,'2026-02-26 22:18:54','Admin User','ADEM  tedarikçisine ARAF, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:18:54'),(110,'2026-02-26 22:19:41','Admin User','BEKİR  tedarikçisine ARAF, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:19:41'),(111,'2026-02-26 22:20:50','Admin User','ESENGÜL  tedarikçisine ARAF, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:20:50'),(112,'2026-02-26 22:21:51','Admin User','LUZİ  tedarikçisine ARAF, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:21:51'),(113,'2026-02-26 22:22:38','Admin User','SELPA  tedarikçisine ARAF, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:22:38'),(114,'2026-02-26 22:23:15','Admin User','ŞENER  tedarikçisine ARAF, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:23:15'),(115,'2026-02-26 22:24:12','Admin User','RAMAZAN  tedarikçisine ARAF, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:24:12'),(116,'2026-02-26 22:24:52','Admin User','GÖKHAN tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:24:52'),(117,'2026-02-26 22:25:56','Admin User','SARI ETİKET tedarikçisine ARAF, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:25:56'),(118,'2026-02-26 22:27:16','Admin User','GÖKHAN tedarikçisine ARAF, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:27:16'),(119,'2026-02-26 22:34:53','Admin User','KIRMIZIGÜL tedarikçisine ARAF, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-02-26 19:34:53'),(120,'2026-02-26 22:35:41','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-02-26 19:35:41'),(121,'2026-02-26 22:38:02','Admin User','ARAF ürünü ARAF olarak güncellendi','UPDATE','2026-02-26 19:38:02'),(122,'2026-02-26 22:38:25','Admin User','ARAF ürünü ARAF olarak güncellendi','UPDATE','2026-02-26 19:38:25');
/*!40000 ALTER TABLE `log_tablosu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lokasyonlar`
--

DROP TABLE IF EXISTS `lokasyonlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
INSERT INTO `lokasyonlar` VALUES (1,'DEPO A','A'),(2,'DEPO B','A'),(3,'DEPO C','A'),(4,'DEPO TAKIM','A'),(5,'DEPO ETİKET','A'),(6,'MERKEZ ESANS','A');
/*!40000 ALTER TABLE `lokasyonlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `malzeme_fotograflari`
--

DROP TABLE IF EXISTS `malzeme_fotograflari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `malzeme_turleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `sira` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzeme_turleri`
--

LOCK TABLES `malzeme_turleri` WRITE;
/*!40000 ALTER TABLE `malzeme_turleri` DISABLE KEYS */;
INSERT INTO `malzeme_turleri` VALUES (3,'kutu','kutu',1,'2025-12-25 08:21:49'),(4,'etiket','etiket',2,'2025-12-25 08:22:01'),(5,'takm','takım',3,'2025-12-25 08:22:10'),(6,'ham_esans','ham esans',4,'2025-12-25 08:22:38'),(7,'alkol','alkol',5,'2025-12-25 08:22:52'),(8,'paket','paket',6,'2025-12-25 08:23:11'),(9,'jelatin','jelatin',7,'2025-12-25 08:23:23'),(10,'fiksator','fiksator',8,'2026-01-31 09:16:40'),(11,'su','su',9,'2026-01-31 09:21:03');
/*!40000 ALTER TABLE `malzeme_turleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `malzemeler`
--

DROP TABLE IF EXISTS `malzemeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzemeler`
--

LOCK TABLES `malzemeler` WRITE;
/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (1,'CHANEL BLU, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(2,'CHANEL BLU, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(3,'CHANEL BLU, su','su',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(4,'CHANEL BLU, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(5,'CHANEL BLU, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(6,'CHANEL BLU, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(7,'CHANEL BLU, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(8,'CHANEL BLU, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(9,'	CHANEL TENDER, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(10,'	CHANEL TENDER, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(11,'	CHANEL TENDER, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(12,'	CHANEL TENDER, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(13,'	CHANEL TENDER, su','su',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(14,'	CHANEL TENDER, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(15,'	CHANEL TENDER, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(16,'	CHANEL TENDER, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(17,'DİOR SAVAGE, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(18,'DİOR SAVAGE, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(19,'DİOR SAVAGE, su','su',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(20,'DİOR SAVAGE, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(21,'DİOR SAVAGE, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(22,'DİOR SAVAGE, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(23,'DİOR SAVAGE, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(24,'DİOR SAVAGE, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(25,'DİOR SAVAGE ELİXSER, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(26,'DİOR SAVAGE ELİXSER, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(27,'DİOR SAVAGE ELİXSER, su','su',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(28,'DİOR SAVAGE ELİXSER, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(29,'DİOR SAVAGE ELİXSER, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(30,'DİOR SAVAGE ELİXSER, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(31,'DİOR SAVAGE ELİXSER, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(32,'DİOR SAVAGE ELİXSER, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO C','A',0),(33,'LİBRE KLASİK, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(34,'LİBRE KLASİK, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(35,'LİBRE KLASİK, su','su',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(36,'LİBRE KLASİK, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(37,'LİBRE KLASİK, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(38,'LİBRE KLASİK, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(39,'LİBRE KLASİK, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(40,'LİBRE KLASİK, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO B','A',0),(41,'ALKOL','alkol','',0.00,'adet',0.00,'TRY',0,0,'MERKEZ ESANS','A',0),(42,'ARAF, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(43,'ARAF, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(44,'ARAF, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(45,'ARAF, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(46,'ARAF, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(47,'ARAF, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A',0),(48,'SU','su','',2000.00,'lt',1.00,'TRY',0,1,'MERKEZ ESANS','A',0);
/*!40000 ALTER TABLE `malzemeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `montaj_is_emirleri`
--

DROP TABLE IF EXISTS `montaj_is_emirleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `mrp_ayarlar`
--

DROP TABLE IF EXISTS `mrp_ayarlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteriler`
--

LOCK TABLES `musteriler` WRITE;
/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
INSERT INTO `musteriler` VALUES (1,'OSMAN GÜZEN','','','','','$2y$10$Af3BcSmqa58mp/6nv2mIAeAZpP0SFQEk/vwu3Xqqc6.c4qSn8DPJy','',1,'05325083018',1),(2,'HAMZA ','','','','','$2y$10$HhSP78LltWx4bGu3.aCNCehDWJXmy/.U4c8iJwgmz3udHRQPQT50O','',1,'05321327675',1);
/*!40000 ALTER TABLE `musteriler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `net_esans_ihtiyaclari`
--

DROP TABLE IF EXISTS `net_esans_ihtiyaclari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personel_izinleri` (
  `izin_id` int(11) NOT NULL AUTO_INCREMENT,
  `personel_id` int(11) NOT NULL,
  `izin_anahtari` varchar(255) NOT NULL,
  PRIMARY KEY (`izin_id`),
  UNIQUE KEY `personel_izin_unique` (`personel_id`,`izin_anahtari`),
  CONSTRAINT `personel_izinleri_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personeller` (`personel_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personel_izinleri`
--

LOCK TABLES `personel_izinleri` WRITE;
/*!40000 ALTER TABLE `personel_izinleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `personel_izinleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personel_maas_odemeleri`
--

DROP TABLE IF EXISTS `personel_maas_odemeleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=302 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personeller`
--

LOCK TABLES `personeller` WRITE;
/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Yönetim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$z56pgRUputjO7M5.Pp0W1eHOgVJ16GX3OKYtPi4VGenFweT8xUidK',NULL,0,0.00);
/*!40000 ALTER TABLE `personeller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satinalma_siparis_kalemleri`
--

DROP TABLE IF EXISTS `satinalma_siparis_kalemleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satinalma_siparis_kalemleri`
--

LOCK TABLES `satinalma_siparis_kalemleri` WRITE;
/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `satinalma_siparis_kalemleri` VALUES (1,1,16,'CHANEL TENDER, takım',1250.00,'adet',2.60,'USD',3250.00,0.00,'');
/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satinalma_siparisler`
--

DROP TABLE IF EXISTS `satinalma_siparisler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satinalma_siparisler`
--

LOCK TABLES `satinalma_siparisler` WRITE;
/*!40000 ALTER TABLE `satinalma_siparisler` DISABLE KEYS */;
INSERT INTO `satinalma_siparisler` VALUES (1,'PO-2026-00001',6,'KIRMIZIGÜL','2026-02-26','2026-02-26','taslak','bekliyor',NULL,3250.00,'USD','',1,'Admin User','2026-02-26 18:51:32','2026-02-26 18:51:32');
/*!40000 ALTER TABLE `satinalma_siparisler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siparis_kalemleri`
--

DROP TABLE IF EXISTS `siparis_kalemleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sirket_kasasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `para_birimi` varchar(3) NOT NULL,
  `bakiye` decimal(15,2) NOT NULL DEFAULT 0.00,
  `guncelleme_tarihi` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `para_birimi` (`para_birimi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sirket_kasasi`
--

LOCK TABLES `sirket_kasasi` WRITE;
/*!40000 ALTER TABLE `sirket_kasasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `sirket_kasasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sistem_kullanicilari`
--

DROP TABLE IF EXISTS `sistem_kullanicilari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareket_kayitlari`
--

LOCK TABLES `stok_hareket_kayitlari` WRITE;
/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `stok_hareket_kayitlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_hareketleri_sozlesmeler`
--

DROP TABLE IF EXISTS `stok_hareketleri_sozlesmeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40000 ALTER TABLE `stok_hareketleri_sozlesmeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taksit_detaylari`
--

DROP TABLE IF EXISTS `taksit_detaylari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tanklar` (
  `tank_id` int(11) NOT NULL AUTO_INCREMENT,
  `tank_kodu` varchar(50) NOT NULL,
  `tank_ismi` varchar(255) NOT NULL,
  `not_bilgisi` text DEFAULT NULL,
  `kapasite` decimal(10,2) NOT NULL,
  PRIMARY KEY (`tank_id`),
  UNIQUE KEY `tank_kodu` (`tank_kodu`),
  UNIQUE KEY `tank_id` (`tank_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tanklar`
--

LOCK TABLES `tanklar` WRITE;
/*!40000 ALTER TABLE `tanklar` DISABLE KEYS */;
INSERT INTO `tanklar` VALUES (1,'E 101','DİOR SAVAGE','',250.00),(2,'E 102','DİOR SAVAGE ELİXSER','',250.00),(3,'W 501','CHANEL BLU','',250.00),(4,'W 502','CHANEL TENDER','',250.00),(5,'W 503','LİBRE KLASİK','',250.00);
/*!40000 ALTER TABLE `tanklar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tedarikciler`
--

DROP TABLE IF EXISTS `tedarikciler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tedarikciler`
--

LOCK TABLES `tedarikciler` WRITE;
/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (1,'ŞENER ','KUTU','','','','','','',''),(2,'BEKİR ','KUTU','','','','','','',''),(3,'ESENGÜL ','KUTU','','','','','','',''),(4,'SARI ETİKET','ETİKET','','','','','','',''),(5,'ÖZGÜR ','ETİKET','','','','','','',''),(6,'KIRMIZIGÜL','TAKIM','','','','','','',''),(7,'ADEM ','TAKIM','','','','','','',''),(8,'LUZİ ','ESANS','','','','','','',''),(9,'SELPA ','ESANS','','','','','','',''),(10,'TEVFİK ','ESANS','','','','','','',''),(11,'GÖKHAN','JİLATİN','','','','','','',''),(12,'RAMAZAN ','PAKET','','','','','','',''),(13,'ZÜLKÜF ','ALKOL','','','','','','',''),(14,'ALİ ','ALKOL','','','','','','','');
/*!40000 ALTER TABLE `tedarikciler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tekrarli_odeme_gecmisi`
--

DROP TABLE IF EXISTS `tekrarli_odeme_gecmisi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tekrarli_odeme_gecmisi`
--

LOCK TABLES `tekrarli_odeme_gecmisi` WRITE;
/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tekrarli_odemeler`
--

DROP TABLE IF EXISTS `tekrarli_odemeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tekrarli_odemeler`
--

LOCK TABLES `tekrarli_odemeler` WRITE;
/*!40000 ALTER TABLE `tekrarli_odemeler` DISABLE KEYS */;
/*!40000 ALTER TABLE `tekrarli_odemeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urun_agaci`
--

DROP TABLE IF EXISTS `urun_agaci`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_agaci`
--

LOCK TABLES `urun_agaci` WRITE;
/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (1,2,'	CHANEL TENDER, Esans','malzeme','13','	CHANEL TENDER, su',5.00,'esans'),(2,2,'	CHANEL TENDER, Esans','malzeme','41','ALKOL',83.00,'esans'),(3,2,'	CHANEL TENDER, Esans','malzeme','14','	CHANEL TENDER, ham esans',12.00,'esans'),(10,4,'DİOR SAVAGE ELİXSER, Esans','malzeme','31','DİOR SAVAGE ELİXSER, ham esans',0.15,'esans'),(11,4,'DİOR SAVAGE ELİXSER, Esans','malzeme','27','DİOR SAVAGE ELİXSER, su',0.05,'esans'),(12,4,'DİOR SAVAGE ELİXSER, Esans','malzeme','41','ALKOL',0.80,'esans'),(13,3,'DİOR SAVAGE, Esans','malzeme','23','DİOR SAVAGE, ham esans',0.15,'esans'),(14,3,'DİOR SAVAGE, Esans','malzeme','19','DİOR SAVAGE, su',0.05,'esans'),(15,3,'DİOR SAVAGE, Esans','malzeme','41','ALKOL',0.80,'esans'),(16,6,'ARAF','etiket','42','ARAF, etiket',1.00,'urun'),(17,6,'ARAF','paket','43','ARAF, paket',1.00,'urun'),(18,6,'ARAF','kutu','45','ARAF, kutu',1.00,'urun'),(19,6,'ARAF','jelatin','46','ARAF, jelatin',1.00,'urun'),(20,6,'ARAF','takm','47','ARAF, takım',1.00,'urun'),(21,6,'ARAF','esans','ES-260226-636','ARAF, Esans',0.15,'urun'),(22,6,'ARAF, Esans','malzeme','44','ARAF, ham esans',0.15,'esans'),(23,6,'ARAF, Esans','malzeme','41','ALKOL',0.80,'esans'),(24,6,'ARAF, Esans','malzeme','48','SU',0.05,'esans');
/*!40000 ALTER TABLE `urun_agaci` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urun_fotograflari`
--

DROP TABLE IF EXISTS `urun_fotograflari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

LOCK TABLES `urunler` WRITE;
/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (1,'CHANEL BLU','',200,'adet',8.00,'USD',0.00,'TRY',0,'DEPO A','A','uretilen',NULL),(2,'	CHANEL TENDER','',0,'adet',7.25,'USD',0.00,'TRY',125,'DEPO B','A','uretilen',NULL),(3,'DİOR SAVAGE','',300,'adet',8.00,'USD',0.00,'TRY',250,'DEPO C','A','uretilen',NULL),(4,'DİOR SAVAGE ELİXSER','',100,'adet',7.80,'USD',0.00,'TRY',200,'DEPO C','A','uretilen',NULL),(5,'LİBRE KLASİK','',200,'adet',7.90,'USD',0.00,'TRY',190,'DEPO B','A','uretilen',NULL),(6,'ARAF','',0,'adet',8.00,'USD',0.00,'TRY',0,'DEPO A','A','uretilen',NULL);
/*!40000 ALTER TABLE `urunler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_esans_maliyetleri`
--

DROP TABLE IF EXISTS `v_esans_maliyetleri`;
/*!50001 DROP VIEW IF EXISTS `v_esans_maliyetleri`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
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
SET character_set_client = utf8mb4;
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

-- Dump completed on 2026-02-26 22:38:38

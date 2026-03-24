/*M!999999\- enable the sandbox mode */ 

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
DROP TABLE IF EXISTS `ayarlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ayarlar` (
  `ayar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ayar_anahtar` varchar(255) NOT NULL,
  `ayar_deger` varchar(255) NOT NULL,
  PRIMARY KEY (`ayar_id`),
  UNIQUE KEY `ayar_anahtar` (`ayar_anahtar`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.8500'),(2,'euro_kuru','50.5070'),(3,'son_otomatik_yedek_tarihi','2026-03-24 00:52:50'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170\n6356317802');
/*!40000 ALTER TABLE `ayarlar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `cek_kasasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `cek_kasasi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (1,1,'şener şimşek ',9,'	DİOR SAVAGE, kutu',0.40,'USD',99999,0,'2026-03-23','2026-04-23','Admin User','2026-03-23 14:42:34','',1,'TL',NULL),(2,3,'esengül ',9,'	DİOR SAVAGE, kutu',0.41,'USD',9999,0,'2026-03-23','2026-05-23','Admin User','2026-03-23 14:43:08','',1,'TL',NULL),(3,2,'bekir ',9,'	DİOR SAVAGE, kutu',0.50,'USD',9999,0,'2026-03-23','2026-04-20','Admin User','2026-03-23 14:43:41','',1,'TL',NULL),(4,4,'sarı etiket ',1,'CHANEL BLU, etiket',1.20,'TL',9999,0,'2026-03-23','2026-04-23','Admin User','2026-03-23 14:44:55','',4,'TL',NULL),(10,12,'zülfikar ',30,'ALKOL',1.60,'USD',999999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:13:22','',3,'TL',NULL),(11,10,'uğur',17,'giorgi armani you, takım',2.30,'USD',9999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:14:13','',1,'TL',NULL),(12,10,'uğur',5,'CHANEL BLU, takım',2.20,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:14:47','',1,'TL',NULL),(13,10,'uğur',24,'	mark jakops çanta, takım',2.00,'USD',999999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:15:24','',1,'TL',NULL),(14,10,'uğur',13,'	DİOR SAVAGE, takım',2.10,'USD',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:15:56','',1,'TL',NULL),(15,10,'uğur',24,'	mark jakops çanta, takım',2.20,'USD',99999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:16:54','',1,'TL',NULL),(16,15,'tuba ',14,'	DİOR SAVAGE, jelatin',1.20,'TL',999999999,0,'2026-03-24','2026-06-24','Admin User','2026-03-24 09:23:46','',1,'TL',NULL),(17,15,'tuba ',23,'	mark jakops çanta, jelatin',1.30,'TL',999999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:24:23','',1,'TL',NULL),(19,15,'tuba ',7,'CHANEL BLU, jelatin',1.10,'TL',999999,0,'2026-03-24','2026-06-24','Admin User','2026-03-24 09:30:21','',1,'TL',NULL),(20,15,'tuba ',16,'giorgi armani you, jelatin',1.30,'TL',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:31:34','',1,'TL',NULL),(21,15,'tuba ',16,'giorgi armani you, jelatin',1.00,'TL',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:32:08','',1,'TL',NULL),(22,6,'tevfik',12,'	DİOR SAVAGE, ham esans',70.00,'EUR',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:33:06','',1,'TL',NULL),(23,6,'tevfik',28,'	mark jakops çanta, ham esans',65.00,'EUR',99999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:33:34','',1,'TL',NULL),(25,6,'tevfik',6,'CHANEL BLU, ham esans',68.00,'EUR',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:36:43','',1,'TL',NULL),(26,6,'tevfik',20,'giorgi armani you, ham esans',70.00,'EUR',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:37:36','',1,'TL',NULL),(27,18,'sezai ',11,'	DİOR SAVAGE, paket',2.00,'TL',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:47:47','',1,'TL',NULL),(28,18,'sezai ',27,'	mark jakops çanta, paket',3.00,'TL',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:48:23','',1,'TL',NULL),(29,18,'sezai ',3,'CHANEL BLU, paket',2.70,'TL',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:48:54','',1,'TL',NULL),(30,18,'sezai ',21,'giorgi armani you, paket',2.30,'TL',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:49:35','',1,'TL',NULL),(31,1,'şener şimşek ',26,'	mark jakops çanta, kutu',0.40,'USD',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:50:31','',1,'TL',NULL),(32,1,'şener şimşek ',2,'CHANEL BLU, kutu',0.40,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:51:06','',1,'TL',NULL),(33,1,'şener şimşek ',18,'giorgi armani you, kutu',0.40,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:53:56','',1,'TL',NULL),(34,8,'selüz ',12,'	DİOR SAVAGE, ham esans',110.00,'USD',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:54:47','',1,'TL',NULL),(35,8,'selüz ',28,'	mark jakops çanta, ham esans',120.00,'USD',99999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:55:20','',1,'TL',NULL),(36,8,'selüz ',6,'CHANEL BLU, ham esans',150.00,'USD',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:55:50','',1,'TL',NULL),(37,8,'selüz ',20,'giorgi armani you, ham esans',150.00,'USD',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 09:56:18','',1,'TL',NULL),(38,4,'sarı etiket ',22,'	mark jakops çanta, etiket',3.00,'TL',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:57:55','',1,'TL',NULL),(39,4,'sarı etiket ',8,'	DİOR SAVAGE, etiket',3.00,'TL',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 09:59:27','',1,'TL',NULL),(40,4,'sarı etiket ',15,'giorgi armani you, etiket',2.30,'TL',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 10:00:20','',1,'TL',NULL),(41,5,'mavi etiket',8,'	DİOR SAVAGE, etiket',2.20,'TL',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:01:45','',1,'TL',NULL),(42,5,'mavi etiket',22,'	mark jakops çanta, etiket',3.00,'TL',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:02:10','',1,'TL',NULL),(43,5,'mavi etiket',1,'CHANEL BLU, etiket',2.20,'TL',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:02:39','',1,'TL',NULL),(44,5,'mavi etiket',15,'giorgi armani you, etiket',2.70,'TL',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:04:52','',1,'TL',NULL),(45,7,'luzi',12,'	DİOR SAVAGE, ham esans',120.00,'USD',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:06:02','',1,'TL',NULL),(46,7,'luzi',28,'	mark jakops çanta, ham esans',130.00,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:07:33','',1,'TL',NULL),(47,7,'luzi',6,'CHANEL BLU, ham esans',120.00,'USD',99999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 10:09:17','',1,'TL',NULL),(48,7,'luzi',20,'giorgi armani you, ham esans',115.00,'USD',9999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 10:09:55','',1,'TL',NULL),(49,11,'adem ',13,'	DİOR SAVAGE, takım',2.20,'USD',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 10:24:21','',1,'TL',NULL),(50,11,'adem ',24,'	mark jakops çanta, takım',1.90,'USD',99999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 10:25:05','',1,'TL',NULL),(51,11,'adem ',5,'CHANEL BLU, takım',2.00,'USD',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 10:25:39','',1,'TL',NULL),(52,11,'adem ',17,'giorgi armani you, takım',2.10,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:26:24','',1,'TL',NULL),(53,2,'bekir ',9,'	DİOR SAVAGE, kutu',0.65,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:28:09','',1,'TL',NULL),(54,2,'bekir ',26,'	mark jakops çanta, kutu',0.50,'USD',999999,0,'2026-03-24','2026-05-23','Admin User','2026-03-24 10:29:21','',1,'TL',NULL),(55,2,'bekir ',2,'CHANEL BLU, kutu',0.55,'USD',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:30:09','',1,'TL',NULL),(56,9,'kımızıgül',13,'	DİOR SAVAGE, takım',2.50,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 10:32:51','',1,'TL',NULL),(58,9,'kımızıgül',24,'	mark jakops çanta, takım',2.60,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 12:02:41','',1,'TL',NULL),(59,9,'kımızıgül',5,'CHANEL BLU, takım',2.30,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 12:03:43','',1,'TL',NULL),(60,9,'kımızıgül',17,'giorgi armani you, takım',2.40,'USD',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 12:04:16','',1,'TL',NULL),(61,3,'esengül ',26,'	mark jakops çanta, kutu',0.60,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 12:06:11','',1,'TL',NULL),(62,3,'esengül ',2,'CHANEL BLU, kutu',0.60,'USD',99999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 12:06:46','',1,'TL',NULL),(64,3,'esengül ',18,'giorgi armani you, kutu',0.50,'USD',999999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 12:08:39','',1,'TL',NULL),(65,14,'gükhan ',14,'	DİOR SAVAGE, jelatin',1.40,'TL',99999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 14:06:56','',1,'TL',NULL),(66,14,'gükhan ',23,'	mark jakops çanta, jelatin',1.30,'TL',9999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 14:07:31','',1,'TL',NULL),(67,14,'gükhan ',7,'CHANEL BLU, jelatin',1.20,'TL',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 14:08:08','',1,'TL',NULL),(68,14,'gükhan ',16,'giorgi armani you, jelatin',1.50,'TL',999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 14:08:46','',1,'TL',NULL),(69,19,'HAMZA GÜZEN',29,'SU',1.00,'TL',999999999,0,'2026-03-24','2026-05-24','Admin User','2026-03-24 14:11:50','',1,'TL',NULL),(70,16,'mehmet',10,'	DİOR SAVAGE, fiksator',35.00,'USD',9999,0,'2026-03-24','2026-04-24','Admin User','2026-03-24 14:27:33','',1,'TL',NULL);
/*!40000 ALTER TABLE `cerceve_sozlesmeler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `esans_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `esans_ihtiyaclari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `esans_is_emirleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `esans_is_emirleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `esans_is_emri_malzeme_listesi` DISABLE KEYS */;
/*!40000 ALTER TABLE `esans_is_emri_malzeme_listesi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (1,'ES-260323-769','CHANEL BLU, Esans','',0.00,'lt',1.00,'W 501','CHANEL BLU'),(2,'ES-260323-206','	DİOR SAVAGE, Esans','',0.00,'lt',1.00,'E 101','DİOR SAVAGE'),(3,'ES-260323-692','giorgi armani you, Esans','',0.00,'lt',1.00,'E 102','mark jakops çanta'),(4,'ES-260323-784','	mark jakops çanta, Esans','',0.00,'lt',1.00,'w 504','giorgi armani you');
/*!40000 ALTER TABLE `esanslar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `gelir_taksit_planlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `gelir_taksit_planlari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `gelir_taksitleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `gelir_taksitleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `gelir_yonetimi` DISABLE KEYS */;
/*!40000 ALTER TABLE `gelir_yonetimi` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
/*!40000 ALTER TABLE `gider_yonetimi` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `is_merkezleri` DISABLE KEYS */;
INSERT INTO `is_merkezleri` VALUES (1,'AHMET ERSİN',''),(2,'ABUBEKİR ÖNEL','');
/*!40000 ALTER TABLE `is_merkezleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `kasa_hareketleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `kasa_hareketleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `kasa_islemleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `kasa_islemleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2026-03-16 02:42:15','Admin User','cerceve_sozlesmeler, esans_is_emirleri, esans_is_emri_malzeme_listesi, esanslar, gelir_yonetimi, gider_yonetimi, is_merkezleri, kasa_hareketleri, log_tablosu, lokasyonlar, malzemeler, montaj_is_emirleri, montaj_is_emri_malzeme_listesi, musteriler, satinalma_siparis_kalemleri, satinalma_siparisler, siparis_kalemleri, siparisler, sirket_kasasi, stok_hareket_kayitlari, stok_hareketleri_sozlesmeler, tanklar, tedarikciler, urun_agaci, urunler tabloları temizlendi','DELETE','2026-03-15 23:42:15'),(2,'2026-03-16 02:43:13','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-15 23:43:13'),(3,'2026-03-16 02:43:21','unknown','unknown oturumu kapattı (ID: unknown)','Çıkış Yapıldı','2026-03-15 23:43:21'),(4,'2026-03-20 20:39:26','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-20 17:39:26'),(5,'2026-03-20 20:40:17','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-20 17:40:17'),(6,'2026-03-22 20:53:52','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-22 17:53:52'),(7,'2026-03-22 20:54:37','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-22 17:54:37'),(8,'2026-03-23 14:18:41','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-23 11:18:41'),(9,'2026-03-23 14:20:46','Admin User','depo esans  deposuna a rafı eklendi','CREATE','2026-03-23 11:20:46'),(10,'2026-03-23 14:20:59','Admin User','depo a deposuna a rafı eklendi','CREATE','2026-03-23 11:20:59'),(11,'2026-03-23 14:21:13','Admin User','depo b deposuna a rafı eklendi','CREATE','2026-03-23 11:21:13'),(12,'2026-03-23 14:21:23','Admin User','depo c deposuna a rafı eklendi','CREATE','2026-03-23 11:21:23'),(13,'2026-03-23 14:21:33','Admin User','depo takım deposuna a rafı eklendi','CREATE','2026-03-23 11:21:33'),(14,'2026-03-23 14:21:47','Admin User','depo kutu deposuna a rafı eklendi','CREATE','2026-03-23 11:21:47'),(15,'2026-03-23 14:24:10','Admin User','AHMET ERSİN iş merkezi eklendi','CREATE','2026-03-23 11:24:10'),(16,'2026-03-23 14:24:16','Admin User','ABUBEKİR ÖNEL iş merkezi eklendi','CREATE','2026-03-23 11:24:16'),(17,'2026-03-23 14:25:04','Admin User','DİOR SAVAGE adlı tank sisteme eklendi','CREATE','2026-03-23 11:25:04'),(18,'2026-03-23 14:25:34','Admin User','CHANEL BLU adlı tank sisteme eklendi','CREATE','2026-03-23 11:25:34'),(19,'2026-03-23 14:26:43','Admin User','mark jakops çanta adlı tank sisteme eklendi','CREATE','2026-03-23 11:26:43'),(20,'2026-03-23 14:27:34','Admin User','giorgi armani you adlı tank sisteme eklendi','CREATE','2026-03-23 11:27:34'),(21,'2026-03-23 14:28:34','Admin User','CHANEL BLU ürünü sisteme eklendi','CREATE','2026-03-23 11:28:34'),(22,'2026-03-23 14:28:35','Admin User','Otomatik esans oluşturuldu: CHANEL BLU, Esans (Tank: W 501)','CREATE','2026-03-23 11:28:35'),(23,'2026-03-23 14:30:04','Admin User','	DİOR SAVAGE ürünü sisteme eklendi','CREATE','2026-03-23 11:30:04'),(24,'2026-03-23 14:30:05','Admin User','Otomatik esans oluşturuldu: 	DİOR SAVAGE, Esans (Tank: E 101)','CREATE','2026-03-23 11:30:05'),(25,'2026-03-23 14:33:42','Admin User','şener şimşek  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:33:42'),(26,'2026-03-23 14:34:27','Admin User','bekir  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:34:27'),(27,'2026-03-23 14:35:02','Admin User','esengül  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:35:02'),(28,'2026-03-23 14:35:23','Admin User','sarı etiket  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:35:23'),(29,'2026-03-23 14:35:39','Admin User','mavi etiket tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:35:39'),(30,'2026-03-23 14:36:21','Admin User','tevfik tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:36:21'),(31,'2026-03-23 14:36:30','Admin User','luzi tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:36:30'),(32,'2026-03-23 14:36:42','Admin User','selüz  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:36:42'),(33,'2026-03-23 14:37:09','Admin User','kımızıgül tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:37:09'),(34,'2026-03-23 14:37:22','Admin User','uğur tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:37:22'),(35,'2026-03-23 14:37:32','Admin User','adem  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:37:32'),(36,'2026-03-23 14:38:20','Admin User','zülfikar  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:38:20'),(37,'2026-03-23 14:38:31','Admin User','cebrail  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:38:31'),(38,'2026-03-23 14:39:14','Admin User','gükhan  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:39:14'),(39,'2026-03-23 14:39:27','Admin User','tuba  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:39:27'),(40,'2026-03-23 14:40:01','Admin User','mehmet tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:40:01'),(41,'2026-03-23 14:40:44','Admin User','ramazan  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:40:44'),(42,'2026-03-23 14:40:54','Admin User','sezai  tedarikçisi sisteme eklendi','CREATE','2026-03-23 11:40:54'),(43,'2026-03-23 14:42:34','Admin User','şener şimşek  tedarikçisine 	DİOR SAVAGE, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 11:42:34'),(44,'2026-03-23 14:43:08','Admin User','esengül  tedarikçisine 	DİOR SAVAGE, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 11:43:08'),(45,'2026-03-23 14:43:41','Admin User','bekir  tedarikçisine 	DİOR SAVAGE, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 11:43:41'),(46,'2026-03-23 14:43:59','Admin User','bekir  tedarikçisine 	DİOR SAVAGE, kutu malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-03-23 11:43:59'),(47,'2026-03-23 14:44:55','Admin User','sarı etiket  tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 11:44:55'),(48,'2026-03-23 14:45:09','Admin User','sarı etiket  tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-03-23 11:45:09'),(49,'2026-03-23 14:45:53','Admin User','mavi etiket tedarikçisine 	DİOR SAVAGE, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 11:45:53'),(50,'2026-03-23 14:47:26','Admin User','mavi etiket tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 11:47:26'),(51,'2026-03-23 14:48:23','Admin User','mavi etiket tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 11:48:23'),(52,'2026-03-23 14:49:24','Admin User','mavi etiket tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-03-23 11:49:24'),(53,'2026-03-23 14:49:49','Admin User','mavi etiket tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-03-23 11:49:49'),(54,'2026-03-23 14:50:16','Admin User','mavi etiket tedarikçisine ait CHANEL BLU, etiket malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-23 11:50:16'),(55,'2026-03-23 14:57:41','Admin User','giorgi armani you ürünü sisteme eklendi','CREATE','2026-03-23 11:57:41'),(56,'2026-03-23 14:57:42','Admin User','Otomatik esans oluşturuldu: giorgi armani you, Esans (Tank: E 102)','CREATE','2026-03-23 11:57:42'),(57,'2026-03-23 14:59:24','Admin User','	mark jakops çanta ürünü sisteme eklendi','CREATE','2026-03-23 11:59:24'),(58,'2026-03-23 14:59:25','Admin User','Otomatik esans oluşturuldu: 	mark jakops çanta, Esans (Tank: w 504)','CREATE','2026-03-23 11:59:25'),(59,'2026-03-23 15:01:04','Admin User','CHANEL BLU ürünü CHANEL BLU olarak güncellendi','UPDATE','2026-03-23 12:01:04'),(60,'2026-03-23 15:01:28','Admin User','	DİOR SAVAGE ürünü 	DİOR SAVAGE olarak güncellendi','UPDATE','2026-03-23 12:01:28'),(61,'2026-03-23 16:10:15','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-23 13:10:15'),(62,'2026-03-23 16:11:54','Admin User','mavi etiket tedarikçisine 	DİOR SAVAGE, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 13:11:54'),(63,'2026-03-23 16:13:09','Admin User','mavi etiket tedarikçisine 	mark jakops çanta, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-23 13:13:09'),(64,'2026-03-24 09:04:28','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 06:04:28'),(65,'2026-03-24 09:09:58','Admin User','SU malzemesi sisteme eklendi','CREATE','2026-03-24 06:09:58'),(66,'2026-03-24 09:10:20','Admin User','ALKOL malzemesi sisteme eklendi','CREATE','2026-03-24 06:10:20'),(67,'2026-03-24 09:11:21','Admin User','HAMZA GÜZEN tedarikçisi sisteme eklendi','CREATE','2026-03-24 06:11:21'),(68,'2026-03-24 09:13:22','Admin User','zülfikar  tedarikçisine ALKOL malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:13:22'),(69,'2026-03-24 09:14:13','Admin User','uğur tedarikçisine giorgi armani you, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:14:13'),(70,'2026-03-24 09:14:47','Admin User','uğur tedarikçisine CHANEL BLU, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:14:47'),(71,'2026-03-24 09:15:24','Admin User','uğur tedarikçisine 	mark jakops çanta, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:15:24'),(72,'2026-03-24 09:15:56','Admin User','uğur tedarikçisine 	DİOR SAVAGE, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:15:56'),(73,'2026-03-24 09:16:54','Admin User','uğur tedarikçisine 	mark jakops çanta, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:16:54'),(74,'2026-03-24 09:23:46','Admin User','tuba  tedarikçisine 	DİOR SAVAGE, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:23:46'),(75,'2026-03-24 09:24:23','Admin User','tuba  tedarikçisine 	mark jakops çanta, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:24:23'),(76,'2026-03-24 09:29:38','Admin User','tuba  tedarikçisine CHANEL BLU, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:29:38'),(77,'2026-03-24 09:30:21','Admin User','tuba  tedarikçisine CHANEL BLU, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:30:21'),(78,'2026-03-24 09:30:50','Admin User','tuba  tedarikçisine ait CHANEL BLU, jelatin malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-24 06:30:50'),(79,'2026-03-24 09:31:34','Admin User','tuba  tedarikçisine giorgi armani you, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:31:34'),(80,'2026-03-24 09:32:08','Admin User','tuba  tedarikçisine giorgi armani you, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:32:08'),(81,'2026-03-24 09:33:06','Admin User','tevfik tedarikçisine 	DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:33:06'),(82,'2026-03-24 09:33:34','Admin User','tevfik tedarikçisine 	mark jakops çanta, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:33:34'),(83,'2026-03-24 09:35:19','Admin User','tevfik tedarikçisine CHANEL BLU, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:35:19'),(84,'2026-03-24 09:35:34','Admin User','tevfik tedarikçisine 	mark jakops çanta, ham esans malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-03-24 06:35:34'),(85,'2026-03-24 09:35:45','Admin User','tevfik tedarikçisine 	DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-03-24 06:35:45'),(86,'2026-03-24 09:36:43','Admin User','tevfik tedarikçisine CHANEL BLU, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:36:43'),(87,'2026-03-24 09:36:56','Admin User','tevfik tedarikçisine ait CHANEL BLU, ham esans malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-24 06:36:56'),(88,'2026-03-24 09:37:36','Admin User','tevfik tedarikçisine giorgi armani you, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:37:36'),(89,'2026-03-24 09:47:47','Admin User','sezai  tedarikçisine 	DİOR SAVAGE, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:47:47'),(90,'2026-03-24 09:48:23','Admin User','sezai  tedarikçisine 	mark jakops çanta, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:48:23'),(91,'2026-03-24 09:48:54','Admin User','sezai  tedarikçisine CHANEL BLU, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:48:54'),(92,'2026-03-24 09:49:35','Admin User','sezai  tedarikçisine giorgi armani you, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:49:35'),(93,'2026-03-24 09:50:31','Admin User','şener şimşek  tedarikçisine 	mark jakops çanta, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:50:31'),(94,'2026-03-24 09:51:06','Admin User','şener şimşek  tedarikçisine CHANEL BLU, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:51:06'),(95,'2026-03-24 09:53:56','Admin User','şener şimşek  tedarikçisine giorgi armani you, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:53:56'),(96,'2026-03-24 09:54:47','Admin User','selüz  tedarikçisine 	DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:54:47'),(97,'2026-03-24 09:55:20','Admin User','selüz  tedarikçisine 	mark jakops çanta, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:55:20'),(98,'2026-03-24 09:55:50','Admin User','selüz  tedarikçisine CHANEL BLU, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:55:50'),(99,'2026-03-24 09:56:18','Admin User','selüz  tedarikçisine giorgi armani you, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:56:18'),(100,'2026-03-24 09:57:55','Admin User','sarı etiket  tedarikçisine 	mark jakops çanta, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:57:55'),(101,'2026-03-24 09:59:27','Admin User','sarı etiket  tedarikçisine 	DİOR SAVAGE, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 06:59:27'),(102,'2026-03-24 10:00:20','Admin User','sarı etiket  tedarikçisine giorgi armani you, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:00:20'),(103,'2026-03-24 10:01:45','Admin User','mavi etiket tedarikçisine 	DİOR SAVAGE, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:01:45'),(104,'2026-03-24 10:02:10','Admin User','mavi etiket tedarikçisine 	mark jakops çanta, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:02:10'),(105,'2026-03-24 10:02:39','Admin User','mavi etiket tedarikçisine CHANEL BLU, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:02:39'),(106,'2026-03-24 10:03:31','Admin User','mavi etiket tedarikçisine ait CHANEL BLU, etiket malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-24 07:03:31'),(107,'2026-03-24 10:03:44','Admin User','mavi etiket tedarikçisine ait 	DİOR SAVAGE, etiket malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-24 07:03:44'),(108,'2026-03-24 10:03:57','Admin User','mavi etiket tedarikçisine ait 	DİOR SAVAGE, etiket malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-24 07:03:57'),(109,'2026-03-24 10:04:19','Admin User','mavi etiket tedarikçisine ait 	mark jakops çanta, etiket malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-24 07:04:19'),(110,'2026-03-24 10:04:52','Admin User','mavi etiket tedarikçisine giorgi armani you, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:04:52'),(111,'2026-03-24 10:06:02','Admin User','luzi tedarikçisine 	DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:06:02'),(112,'2026-03-24 10:06:50','Admin User','luzi tedarikçisine 	DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-03-24 07:06:50'),(113,'2026-03-24 10:07:33','Admin User','luzi tedarikçisine 	mark jakops çanta, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:07:33'),(114,'2026-03-24 10:09:17','Admin User','luzi tedarikçisine CHANEL BLU, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:09:17'),(115,'2026-03-24 10:09:55','Admin User','luzi tedarikçisine giorgi armani you, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:09:55'),(116,'2026-03-24 10:24:21','Admin User','adem  tedarikçisine 	DİOR SAVAGE, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:24:21'),(117,'2026-03-24 10:25:05','Admin User','adem  tedarikçisine 	mark jakops çanta, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:25:05'),(118,'2026-03-24 10:25:39','Admin User','adem  tedarikçisine CHANEL BLU, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:25:39'),(119,'2026-03-24 10:26:24','Admin User','adem  tedarikçisine giorgi armani you, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:26:24'),(120,'2026-03-24 10:28:09','Admin User','bekir  tedarikçisine 	DİOR SAVAGE, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:28:09'),(121,'2026-03-24 10:29:21','Admin User','bekir  tedarikçisine 	mark jakops çanta, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:29:21'),(122,'2026-03-24 10:30:09','Admin User','bekir  tedarikçisine CHANEL BLU, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:30:09'),(123,'2026-03-24 10:32:51','Admin User','kımızıgül tedarikçisine 	DİOR SAVAGE, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 07:32:51'),(124,'2026-03-24 11:10:17','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 08:10:17'),(125,'2026-03-24 11:57:47','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 08:57:47'),(126,'2026-03-24 12:02:05','Admin User','kımızıgül tedarikçisine 	DİOR SAVAGE, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 09:02:05'),(127,'2026-03-24 12:02:41','Admin User','kımızıgül tedarikçisine 	mark jakops çanta, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 09:02:41'),(128,'2026-03-24 12:03:43','Admin User','kımızıgül tedarikçisine CHANEL BLU, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 09:03:43'),(129,'2026-03-24 12:04:16','Admin User','kımızıgül tedarikçisine giorgi armani you, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 09:04:16'),(130,'2026-03-24 12:04:58','Admin User','kımızıgül tedarikçisine ait 	DİOR SAVAGE, takım malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-24 09:04:58'),(131,'2026-03-24 12:06:11','Admin User','esengül  tedarikçisine 	mark jakops çanta, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 09:06:11'),(132,'2026-03-24 12:06:46','Admin User','esengül  tedarikçisine CHANEL BLU, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 09:06:46'),(133,'2026-03-24 12:07:38','Admin User','esengül  tedarikçisine CHANEL BLU, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 09:07:38'),(134,'2026-03-24 12:08:10','Admin User','esengül  tedarikçisine ait CHANEL BLU, kutu malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-03-24 09:08:10'),(135,'2026-03-24 12:08:39','Admin User','esengül  tedarikçisine giorgi armani you, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 09:08:39'),(136,'2026-03-24 12:10:25','Admin User','	DİOR SAVAGE, Esans urun agacina 	DİOR SAVAGE, ham esans bileseni eklendi','CREATE','2026-03-24 09:10:25'),(137,'2026-03-24 14:04:58','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 11:04:58'),(138,'2026-03-24 14:06:56','Admin User','gükhan  tedarikçisine 	DİOR SAVAGE, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 11:06:56'),(139,'2026-03-24 14:07:31','Admin User','gükhan  tedarikçisine 	mark jakops çanta, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 11:07:31'),(140,'2026-03-24 14:08:08','Admin User','gükhan  tedarikçisine CHANEL BLU, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 11:08:08'),(141,'2026-03-24 14:08:46','Admin User','gükhan  tedarikçisine giorgi armani you, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 11:08:46'),(142,'2026-03-24 14:11:50','Admin User','HAMZA GÜZEN tedarikçisine SU malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 11:11:50'),(143,'2026-03-24 14:15:34','Admin User','	DİOR SAVAGE, Esans urun agacindaki 	DİOR SAVAGE, ham esans bileseni 	DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-03-24 11:15:34'),(144,'2026-03-24 14:15:51','Admin User','	DİOR SAVAGE, Esans urun agacindan 	DİOR SAVAGE, ham esans bileseni silindi','DELETE','2026-03-24 11:15:51'),(145,'2026-03-24 14:16:04','Admin User','Bilinmeyen Urun urun agacindan Bilinmeyen Bilesen bileseni silindi','DELETE','2026-03-24 11:16:04'),(146,'2026-03-24 14:16:28','Admin User','	DİOR SAVAGE, Esans urun agacina 	DİOR SAVAGE, ham esans bileseni eklendi','CREATE','2026-03-24 11:16:28'),(147,'2026-03-24 14:16:33','Admin User','	DİOR SAVAGE, Esans urun agacina 	DİOR SAVAGE, ham esans bileseni eklendi','CREATE','2026-03-24 11:16:33'),(148,'2026-03-24 14:16:34','Admin User','	DİOR SAVAGE, Esans urun agacina 	DİOR SAVAGE, ham esans bileseni eklendi','CREATE','2026-03-24 11:16:34'),(149,'2026-03-24 14:16:54','Admin User','	DİOR SAVAGE, Esans urun agacindan 	DİOR SAVAGE, ham esans bileseni silindi','DELETE','2026-03-24 11:16:54'),(150,'2026-03-24 14:16:56','Admin User','	DİOR SAVAGE, Esans urun agacindan 	DİOR SAVAGE, ham esans bileseni silindi','DELETE','2026-03-24 11:16:56'),(151,'2026-03-24 14:17:42','Admin User','	DİOR SAVAGE, Esans urun agacina ALKOL bileseni eklendi','CREATE','2026-03-24 11:17:42'),(152,'2026-03-24 14:17:45','Admin User','	DİOR SAVAGE, Esans urun agacina ALKOL bileseni eklendi','CREATE','2026-03-24 11:17:45'),(153,'2026-03-24 14:17:59','Admin User','	DİOR SAVAGE, Esans urun agacindan ALKOL bileseni silindi','DELETE','2026-03-24 11:17:59'),(154,'2026-03-24 14:18:40','Admin User','	DİOR SAVAGE, Esans urun agacina SU bileseni eklendi','CREATE','2026-03-24 11:18:40'),(155,'2026-03-24 14:18:45','Admin User','	DİOR SAVAGE, Esans urun agacina SU bileseni eklendi','CREATE','2026-03-24 11:18:45'),(156,'2026-03-24 14:19:00','Admin User','	DİOR SAVAGE, Esans urun agacindan SU bileseni silindi','DELETE','2026-03-24 11:19:00'),(157,'2026-03-24 14:27:33','Admin User','mehmet tedarikçisine 	DİOR SAVAGE, fiksator malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-24 11:27:33'),(158,'2026-03-24 14:43:57','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-24 11:43:57'),(159,'2026-03-24 15:10:56','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 12:10:56'),(160,'2026-03-24 15:12:39','Admin User','	DİOR SAVAGE, Esans urun agacindaki 	DİOR SAVAGE, ham esans bileseni 	DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-03-24 12:12:39'),(161,'2026-03-24 15:14:53','Admin User','sezai  tedarikçisine PO-2026-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:14:53'),(162,'2026-03-24 15:15:02','Admin User','tuba  tedarikçisine PO-2026-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:15:02'),(163,'2026-03-24 15:15:12','Admin User','sarı etiket  tedarikçisine PO-2026-00003 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:15:12'),(164,'2026-03-24 15:16:09','Admin User','mavi etiket tedarikçisine PO-2026-00004 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:16:09'),(165,'2026-03-24 15:16:29','Admin User','adem  tedarikçisine PO-2026-00005 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:16:29'),(166,'2026-03-24 15:16:40','Admin User','uğur tedarikçisine PO-2026-00006 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:16:40'),(167,'2026-03-24 15:16:47','Admin User','şener şimşek  tedarikçisine PO-2026-00007 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:16:47'),(168,'2026-03-24 15:16:59','Admin User','gükhan  tedarikçisine PO-2026-00008 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:16:59'),(169,'2026-03-24 15:17:07','Admin User','mehmet tedarikçisine PO-2026-00009 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-24 12:17:07'),(170,'2026-03-24 15:18:47','Admin User','Satınalma siparişi #8 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:18:47'),(171,'2026-03-24 15:20:59','Admin User','Satınalma siparişi #7 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:20:59'),(172,'2026-03-24 15:21:03','Admin User','Satınalma siparişi #7 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:21:03'),(173,'2026-03-24 15:21:06','Admin User','Satınalma siparişi #7 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:21:06'),(174,'2026-03-24 15:21:40','Admin User','Satınalma siparişi #7 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:21:40'),(175,'2026-03-24 15:22:00','Admin User','Satınalma siparişi #6 durumu \'taslak\' olarak güncellendi','UPDATE','2026-03-24 12:22:00'),(176,'2026-03-24 15:22:03','Admin User','Satınalma siparişi #6 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:22:03'),(177,'2026-03-24 15:22:24','Admin User','Satınalma siparişi #5 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:22:24'),(178,'2026-03-24 15:22:27','Admin User','Satınalma siparişi #5 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:22:27'),(179,'2026-03-24 15:22:44','Admin User','Satınalma siparişi #4 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:22:44'),(180,'2026-03-24 15:23:15','Admin User','Satınalma siparişi #1 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:23:15'),(181,'2026-03-24 15:23:32','Admin User','Satınalma siparişi #2 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:23:32'),(182,'2026-03-24 15:23:39','Admin User','Satınalma siparişi #2 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:23:39'),(183,'2026-03-24 15:23:47','Admin User','Satınalma siparişi #3 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-24 12:23:47'),(184,'2026-03-24 16:17:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 13:17:09'),(185,'2026-03-24 16:20:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 13:20:36'),(186,'2026-03-24 16:46:26','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 13:46:26'),(187,'2026-03-24 17:09:28','Admin User','	DİOR SAVAGE, Esans urun agacindan 	DİOR SAVAGE, ham esans bileseni silindi','DELETE','2026-03-24 14:09:28'),(188,'2026-03-24 17:09:31','Admin User','	DİOR SAVAGE, Esans urun agacindan SU bileseni silindi','DELETE','2026-03-24 14:09:31'),(189,'2026-03-24 17:09:36','Admin User','	DİOR SAVAGE, Esans urun agacindan ALKOL bileseni silindi','DELETE','2026-03-24 14:09:36'),(190,'2026-03-24 17:12:12','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-24 14:12:12'),(191,'2026-03-24 17:13:39','Admin User','	DİOR SAVAGE, Esans urun agacina 	DİOR SAVAGE, ham esans bileseni eklendi','CREATE','2026-03-24 14:13:39'),(192,'2026-03-24 17:14:16','Admin User','	DİOR SAVAGE, Esans urun agacina ALKOL bileseni eklendi','CREATE','2026-03-24 14:14:16'),(193,'2026-03-24 17:15:52','Admin User','	DİOR SAVAGE, Esans urun agacina SU bileseni eklendi','CREATE','2026-03-24 14:15:52'),(194,'2026-03-24 17:16:04','Admin User','	DİOR SAVAGE, Esans urun agacindaki SU bileseni SU olarak guncellendi','UPDATE','2026-03-24 14:16:04'),(195,'2026-03-24 17:16:47','Admin User','	DİOR SAVAGE, Esans urun agacindaki ALKOL bileseni ALKOL olarak guncellendi','UPDATE','2026-03-24 14:16:47'),(196,'2026-03-24 17:17:01','Admin User','	DİOR SAVAGE, Esans urun agacina 	DİOR SAVAGE, fiksator bileseni eklendi','CREATE','2026-03-24 14:17:01'),(197,'2026-03-24 17:19:17','Admin User','	DİOR SAVAGE, Esans urun agacindaki 	DİOR SAVAGE, fiksator bileseni 	DİOR SAVAGE, fiksator olarak guncellendi','UPDATE','2026-03-24 14:19:17');
/*!40000 ALTER TABLE `log_tablosu` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `lokasyonlar` DISABLE KEYS */;
INSERT INTO `lokasyonlar` VALUES (1,'depo esans ','a'),(2,'depo a','a'),(3,'depo b','a'),(4,'depo c','a'),(5,'depo takım','a'),(6,'depo kutu','a');
/*!40000 ALTER TABLE `lokasyonlar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_fotograflari` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_fotograflari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_ihtiyaclari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_maliyetleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_maliyetleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_siparisler` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_siparisler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_turleri` DISABLE KEYS */;
INSERT INTO `malzeme_turleri` VALUES (3,'kutu','kutu',1,'2025-12-25 08:21:49'),(4,'etiket','etiket',2,'2025-12-25 08:22:01'),(5,'takm','takım',3,'2025-12-25 08:22:10'),(6,'ham_esans','ham esans',4,'2025-12-25 08:22:38'),(7,'alkol','alkol',5,'2025-12-25 08:22:52'),(8,'paket','paket',6,'2025-12-25 08:23:11'),(9,'jelatin','jelatin',7,'2025-12-25 08:23:23'),(10,'fiksator','fiksator',8,'2026-01-31 09:16:40'),(11,'su','su',9,'2026-01-31 09:21:03');
/*!40000 ALTER TABLE `malzeme_turleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (1,'CHANEL BLU, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(2,'CHANEL BLU, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(3,'CHANEL BLU, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(4,'CHANEL BLU, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(5,'CHANEL BLU, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(6,'CHANEL BLU, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(7,'CHANEL BLU, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(8,'	DİOR SAVAGE, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo b','a',0),(9,'	DİOR SAVAGE, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'depo b','a',0),(10,'	DİOR SAVAGE, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'depo b','a',0),(11,'	DİOR SAVAGE, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo b','a',0),(12,'	DİOR SAVAGE, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'depo b','a',0),(13,'	DİOR SAVAGE, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'depo b','a',0),(14,'	DİOR SAVAGE, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'depo b','a',0),(15,'giorgi armani you, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(16,'giorgi armani you, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(17,'giorgi armani you, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(18,'giorgi armani you, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(19,'giorgi armani you, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(20,'giorgi armani you, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(21,'giorgi armani you, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(22,'	mark jakops çanta, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(23,'	mark jakops çanta, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(24,'	mark jakops çanta, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(25,'	mark jakops çanta, fiksator','fiksator',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(26,'	mark jakops çanta, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(27,'	mark jakops çanta, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(28,'	mark jakops çanta, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(29,'SU','su','',0.00,'adet',0.00,'TRY',0,0,'depo esans ','a',0),(30,'ALKOL','alkol','',0.00,'adet',0.00,'TRY',0,0,'depo esans ','a',0);
/*!40000 ALTER TABLE `malzemeler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `montaj_is_emirleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `montaj_is_emirleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `montaj_is_emri_malzeme_listesi` DISABLE KEYS */;
/*!40000 ALTER TABLE `montaj_is_emri_malzeme_listesi` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `mrp_ayarlar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_ayarlar` ENABLE KEYS */;
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
  `net_ihtiyaö` decimal(10,2) NOT NULL,
  `teslim_tarihi` date DEFAULT NULL,
  `durum` enum('yetersiz','planlandi','siparis_verildi','teslim_alindi') DEFAULT 'yetersiz',
  PRIMARY KEY (`ihtiyac_id`),
  KEY `plan_id` (`plan_id`),
  KEY `idx_malzeme_kodu` (`malzeme_kodu`),
  KEY `idx_durum` (`durum`),
  CONSTRAINT `mrp_ihtiyaclar_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `mrp_planlama` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `mrp_ihtiyaclar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_ihtiyaclar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `mrp_planlama` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_planlama` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `mrp_raporlar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_raporlar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `mrp_teslim_takvimi` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_teslim_takvimi` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `musteri_geri_bildirimleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `musteri_geri_bildirimleri` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
/*!40000 ALTER TABLE `musteriler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `net_esans_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `net_esans_ihtiyaclari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `net_urun_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `net_urun_ihtiyaclari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `personel_avanslar` DISABLE KEYS */;
/*!40000 ALTER TABLE `personel_avanslar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `personel_izinleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `personel_izinleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `personel_maas_odemeleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `personel_maas_odemeleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Yönetim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$z56pgRUputjO7M5.Pp0W1eHOgVJ16GX3OKYtPi4VGenFweT8xUidK',NULL,0,0.00);
/*!40000 ALTER TABLE `personeller` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `satinalma_siparis_kalemleri` VALUES (1,1,3,'CHANEL BLU, paket',1000.00,'adet',2.70,'TL',2700.00,0.00,''),(2,1,11,'DİOR SAVAGE, paket',1000.00,'adet',2.00,'TL',2000.00,0.00,''),(3,1,21,'giorgi armani you, paket',1000.00,'adet',2.30,'TL',2300.00,0.00,''),(4,1,27,'mark jakops çanta, paket',1000.00,'adet',3.00,'TL',3000.00,0.00,''),(5,2,7,'CHANEL BLU, jelatin',1000.00,'adet',1.10,'TL',1100.00,0.00,''),(6,2,14,'DİOR SAVAGE, jelatin',1000.00,'adet',1.20,'TL',1200.00,0.00,''),(7,2,16,'giorgi armani you, jelatin',1000.00,'adet',1.00,'TL',1000.00,0.00,''),(8,3,1,'CHANEL BLU, etiket',1000.00,'adet',1.20,'TL',1200.00,0.00,''),(9,3,15,'giorgi armani you, etiket',1000.00,'adet',2.30,'TL',2300.00,0.00,''),(10,3,22,'mark jakops çanta, etiket',1000.00,'adet',3.00,'TL',3000.00,0.00,''),(11,4,8,'DİOR SAVAGE, etiket',1000.00,'adet',2.20,'TL',2200.00,0.00,''),(12,5,5,'CHANEL BLU, takım',1000.00,'adet',2.00,'USD',2000.00,0.00,''),(13,5,17,'giorgi armani you, takım',1000.00,'adet',2.10,'USD',2100.00,0.00,''),(14,5,24,'mark jakops çanta, takım',1000.00,'adet',1.90,'USD',1900.00,0.00,''),(15,6,13,'DİOR SAVAGE, takım',1000.00,'adet',2.10,'USD',2100.00,0.00,''),(16,7,2,'CHANEL BLU, kutu',1000.00,'adet',0.40,'USD',400.00,0.00,''),(17,7,9,'DİOR SAVAGE, kutu',1000.00,'adet',0.40,'USD',400.00,0.00,''),(18,7,18,'giorgi armani you, kutu',1000.00,'adet',0.40,'USD',400.00,0.00,''),(19,7,26,'mark jakops çanta, kutu',1000.00,'adet',0.40,'USD',400.00,0.00,''),(20,8,23,'mark jakops çanta, jelatin',1000.00,'adet',1.30,'TL',1300.00,0.00,''),(21,9,10,'DİOR SAVAGE, fiksator',1000.00,'adet',35.00,'USD',35000.00,0.00,'');
/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `satinalma_siparisler` DISABLE KEYS */;
INSERT INTO `satinalma_siparisler` VALUES (1,'PO-2026-00001',18,'sezai ','2026-03-24',NULL,'gonderildi','bekliyor',NULL,10000.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:14:53','2026-03-24 12:23:15'),(2,'PO-2026-00002',15,'tuba ','2026-03-24',NULL,'gonderildi','bekliyor',NULL,3300.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:15:02','2026-03-24 12:23:32'),(3,'PO-2026-00003',4,'sarı etiket ','2026-03-24',NULL,'gonderildi','bekliyor',NULL,6500.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:15:12','2026-03-24 12:23:47'),(4,'PO-2026-00004',5,'mavi etiket','2026-03-24',NULL,'gonderildi','bekliyor',NULL,2200.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:16:09','2026-03-24 12:22:44'),(5,'PO-2026-00005',11,'adem ','2026-03-24',NULL,'gonderildi','bekliyor',NULL,6000.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:16:29','2026-03-24 12:22:24'),(6,'PO-2026-00006',10,'uğur','2026-03-24',NULL,'gonderildi','bekliyor',NULL,2100.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:16:40','2026-03-24 12:22:03'),(7,'PO-2026-00007',1,'şener şimşek ','2026-03-24',NULL,'gonderildi','bekliyor',NULL,1600.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:16:47','2026-03-24 12:20:59'),(8,'PO-2026-00008',14,'gükhan ','2026-03-24',NULL,'gonderildi','bekliyor',NULL,1300.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:16:59','2026-03-24 12:18:47'),(9,'PO-2026-00009',16,'mehmet','2026-03-24',NULL,'taslak','bekliyor',NULL,35000.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-24 12:17:07','2026-03-24 12:17:07');
/*!40000 ALTER TABLE `satinalma_siparisler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `siparis_kalemleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `siparis_kalemleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
/*!40000 ALTER TABLE `siparisler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `sirket_kasasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `sirket_kasasi` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `sistem_kullanicilari` DISABLE KEYS */;
/*!40000 ALTER TABLE `sistem_kullanicilari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `stok_hareket_kayitlari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `stok_hareketleri_sozlesmeler` DISABLE KEYS */;
/*!40000 ALTER TABLE `stok_hareketleri_sozlesmeler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `taksit_detaylari` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_detaylari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `taksit_planlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_planlari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `taksit_siparis_baglantisi` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_siparis_baglantisi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tanklar` DISABLE KEYS */;
INSERT INTO `tanklar` VALUES (1,'E 101','DİOR SAVAGE','',250.00),(2,'W 501','CHANEL BLU','',250.00),(3,'E 102 ','mark jakops çanta','',250.00),(4,'w 504','giorgi armani you','',250.00);
/*!40000 ALTER TABLE `tanklar` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (1,'şener şimşek ','kutu','','','','','','',''),(2,'bekir ','kutu','','','','','','',''),(3,'esengül ','kutu','','','','','','',''),(4,'sarı etiket ','etiket','','','','','','',''),(5,'mavi etiket','etiket','','','','','','',''),(6,'tevfik','esans','','','','','','',''),(7,'luzi','esans','','','','','','',''),(8,'selüz ','esans','','','','','','',''),(9,'kımızıgül','takım','','','','','','',''),(10,'uğur','takım','','','','','','',''),(11,'adem ','takım','','','','','','',''),(12,'zülfikar ','alkol','','','','','','',''),(13,'cebrail ','alkol','','','','','','',''),(14,'gükhan ','jilatin ','','','','','','',''),(15,'tuba ','jilatin','','','','','','',''),(16,'mehmet','fiksatör','','','','','','',''),(17,'ramazan ','paket','','','','','','',''),(18,'sezai ','paket','','','','','','',''),(19,'HAMZA GÜZEN','SU','','','','','','','');
/*!40000 ALTER TABLE `tedarikciler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` ENABLE KEYS */;
DROP TABLE IF EXISTS `tekrarli_odemeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tekrarli_odemeler` (
  `odeme_id` int(11) NOT NULL AUTO_INCREMENT,
  `odeme_adi` varchar(255) NOT NULL,
  `odeme_tipi` varchar(100) NOT NULL COMMENT 'Kira, Elektrik, Su, Doöalgaz, önternet, Vergi, Sigorta, vb.',
  `tutar` decimal(10,2) NOT NULL,
  `odeme_gunu` int(2) NOT NULL COMMENT 'Ayön kaöönda ödeme yapölacak (1-31)',
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

/*!40000 ALTER TABLE `tekrarli_odemeler` DISABLE KEYS */;
/*!40000 ALTER TABLE `tekrarli_odemeler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (1,1,'CHANEL BLU','etiket','1','CHANEL BLU, etiket',1.00,'urun'),(2,1,'CHANEL BLU','kutu','2','CHANEL BLU, kutu',1.00,'urun'),(3,1,'CHANEL BLU','paket','3','CHANEL BLU, paket',1.00,'urun'),(4,1,'CHANEL BLU','fiksator','4','CHANEL BLU, fiksator',1.00,'urun'),(5,1,'CHANEL BLU','takm','5','CHANEL BLU, takım',1.00,'urun'),(6,1,'CHANEL BLU','jelatin','7','CHANEL BLU, jelatin',1.00,'urun'),(7,1,'CHANEL BLU','esans','ES-260323-769','CHANEL BLU, Esans',1.00,'urun'),(8,2,'	DİOR SAVAGE','etiket','8','	DİOR SAVAGE, etiket',1.00,'urun'),(9,2,'	DİOR SAVAGE','kutu','9','	DİOR SAVAGE, kutu',1.00,'urun'),(10,2,'	DİOR SAVAGE','fiksator','10','	DİOR SAVAGE, fiksator',1.00,'urun'),(11,2,'	DİOR SAVAGE','paket','11','	DİOR SAVAGE, paket',1.00,'urun'),(12,2,'	DİOR SAVAGE','takm','13','	DİOR SAVAGE, takım',1.00,'urun'),(13,2,'	DİOR SAVAGE','jelatin','14','	DİOR SAVAGE, jelatin',1.00,'urun'),(14,2,'	DİOR SAVAGE','esans','ES-260323-206','	DİOR SAVAGE, Esans',1.00,'urun'),(15,3,'giorgi armani you','etiket','15','giorgi armani you, etiket',1.00,'urun'),(16,3,'giorgi armani you','jelatin','16','giorgi armani you, jelatin',1.00,'urun'),(17,3,'giorgi armani you','takm','17','giorgi armani you, takım',1.00,'urun'),(18,3,'giorgi armani you','kutu','18','giorgi armani you, kutu',1.00,'urun'),(19,3,'giorgi armani you','fiksator','19','giorgi armani you, fiksator',1.00,'urun'),(20,3,'giorgi armani you','paket','21','giorgi armani you, paket',1.00,'urun'),(21,3,'giorgi armani you','esans','ES-260323-692','giorgi armani you, Esans',1.00,'urun'),(22,4,'	mark jakops çanta','etiket','22','	mark jakops çanta, etiket',1.00,'urun'),(23,4,'	mark jakops çanta','jelatin','23','	mark jakops çanta, jelatin',1.00,'urun'),(24,4,'	mark jakops çanta','takm','24','	mark jakops çanta, takım',1.00,'urun'),(25,4,'	mark jakops çanta','fiksator','25','	mark jakops çanta, fiksator',1.00,'urun'),(26,4,'	mark jakops çanta','kutu','26','	mark jakops çanta, kutu',1.00,'urun'),(27,4,'	mark jakops çanta','paket','27','	mark jakops çanta, paket',1.00,'urun'),(28,4,'	mark jakops çanta','esans','ES-260323-784','	mark jakops çanta, Esans',1.00,'urun'),(37,2,'	DİOR SAVAGE, Esans','malzeme','12','	DİOR SAVAGE, ham esans',0.15,'esans'),(38,2,'	DİOR SAVAGE, Esans','malzeme','30','ALKOL',0.76,'esans'),(39,2,'	DİOR SAVAGE, Esans','malzeme','29','SU',0.08,'esans'),(40,2,'	DİOR SAVAGE, Esans','malzeme','10','	DİOR SAVAGE, fiksator',0.01,'esans');
/*!40000 ALTER TABLE `urun_agaci` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `urun_fotograflari` DISABLE KEYS */;
/*!40000 ALTER TABLE `urun_fotograflari` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (1,'CHANEL BLU','',150,'adet',8.00,'USD',0.00,'TRY',25,'depo a','a','uretilen',NULL),(2,'	DİOR SAVAGE','',75,'adet',8.10,'USD',0.00,'TRY',32,'depo b','a','uretilen',NULL),(3,'giorgi armani you','',75,'adet',7.80,'USD',0.00,'TRY',100,'depo a','a','uretilen',NULL),(4,'	mark jakops çanta','',150,'adet',9.00,'USD',0.00,'TRY',120,'depo a','a','uretilen',NULL);
/*!40000 ALTER TABLE `urunler` ENABLE KEYS */;
DROP TABLE IF EXISTS `v_esans_maliyetleri`;
/*!50001 DROP VIEW IF EXISTS `v_esans_maliyetleri`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_esans_maliyetleri` AS SELECT
 1 AS `esans_kodu`,
  1 AS `toplam_maliyet` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_urun_maliyetleri`;
/*!50001 DROP VIEW IF EXISTS `v_urun_maliyetleri`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_urun_maliyetleri` AS SELECT
 1 AS `urun_kodu`,
  1 AS `teorik_maliyet` */;
SET character_set_client = @saved_cs_client;
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


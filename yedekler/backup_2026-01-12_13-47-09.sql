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
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

LOCK TABLES `ayarlar` WRITE;
/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.8500'),(2,'euro_kuru','50.5070'),(3,'son_otomatik_yedek_tarihi','2026-01-05 10:22:25'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cerceve_sozlesmeler`
--

LOCK TABLES `cerceve_sozlesmeler` WRITE;
/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (1,1,'kırmızıgül ',2,'CHANEL COCO TAKIM',2.20,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:42:26','',1,'TL',NULL),(2,3,'luzi',1,'CHANEL COCO HAM ESANS',120.00,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:43:18','',1,'TL',NULL),(3,4,'paket',5,'CHANEL COCO paket',1.00,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:43:58','',1,'TL',NULL),(4,2,'şener şimşek',3,'CHANEL COCO kutu',0.50,'USD',9999,0,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:46:44','',1,'TL',NULL),(5,5,'jilatin ',4,'CHANEL COCO jelatin',1.00,'TL',9999,1002,'2025-12-12','2025-12-30','Admin User','2025-12-25 11:47:47','',1,'TL',NULL),(7,8,'zülkür ',7,'alkol',1.50,'USD',9999,560,'2025-12-12','2026-01-10','Admin User','2025-12-25 12:11:14','',1,'TL',NULL),(8,3,'luzi',1,'CHANEL COCO HAM ESANS',110.00,'USD',9999,15,'2025-12-12','2026-12-30','Admin User','2025-12-26 14:37:52','',1,'TL',NULL),(9,5,'gökan',49,'Urun4, etiket',1.00,'TL',9999999,0,'2026-01-01','2026-01-18','Admin User','2026-01-03 04:28:44','',1,'TL',NULL),(10,5,'gökan',50,'Urun4, ham esans',12.00,'TL',1213,0,'2026-01-01','2026-01-11','Admin User','2026-01-03 04:29:25','',1,'TL',NULL),(11,5,'gökan',51,'Urun4, jelatin',1.00,'TL',999,0,'2026-01-02','2026-01-11','Admin User','2026-01-03 04:29:49','',1,'TL',NULL),(12,5,'gökan',52,'Urun4, kutu',1.00,'TL',12,0,'2026-01-02','2026-01-11','Admin User','2026-01-03 04:30:22','',1,'TL',NULL),(13,5,'gökan',53,'Urun4, paket',1.00,'TL',123,0,'2026-01-02','2026-01-11','Admin User','2026-01-03 04:30:44','',1,'TL',NULL),(14,5,'gökan',54,'Urun4, takım',12.00,'TL',123,0,'2026-01-02','2026-01-11','Admin User','2026-01-03 04:31:10','',1,'TL',NULL);
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
INSERT INTO `esans_is_emirleri` VALUES (1,'2025-12-25','Admin User','001','DİOR , SAVAGE','004','E 100',100.00,'lt','2025-12-25',30,'2026-01-24','2025-12-25','2025-12-25','karıştırılmasına dikkat günlük 20 dk karıştırılsın karıştırılmasına dikkat günlük 20 dk karıştırılsın\nkarıştırma burhan tarafından yapıldı','tamamlandi',100.00,0.00),(2,'2026-01-05','Admin User','ES-251231-749','Urun4, Esans','001','w 501',10.00,'lt','2026-01-05',1,'2026-01-06',NULL,NULL,'','olusturuldu',0.00,0.00);
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
INSERT INTO `esans_is_emri_malzeme_listesi` VALUES (2,7,'alkol','malzeme',100.00,'lt');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esanslar`
--

LOCK TABLES `esanslar` WRITE;
/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (2,'001','DİOR , SAVAGE','',0.00,'lt',25.00,'',''),(3,'004','bacarat red','',0.00,'lt',25.00,'',''),(4,'005','VIKTORIA SECRET , BOM ŞHEL','',0.00,'lt',20.00,'',''),(9,'ES010','Guzel Urun Esansi','',100.00,'lt',46.00,'001','w 501'),(10,'ES-251231-686','Urun 2, Esans','',0.00,'lt',1.00,NULL,NULL),(11,'ES-251231-837','URUN3, Esans','',0.00,'lt',1.00,NULL,NULL),(12,'ES-251231-749','Urun4, Esans','',20.00,'lt',1.00,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gelir_yonetimi`
--

LOCK TABLES `gelir_yonetimi` WRITE;
/*!40000 ALTER TABLE `gelir_yonetimi` DISABLE KEYS */;
INSERT INTO `gelir_yonetimi` VALUES (7,'2025-12-30 00:00:00',1950.00,'USD','Sipariş Ödemesi','Sipariş No: #5 tahsilatı',1,'Admin User',5,'Nakit',1,'MEHMET FATİH GÜZEN',NULL,'TL',NULL),(8,'2025-12-30 00:00:00',1740.00,'USD','Sipariş Ödemesi','Sipariş No: #6 tahsilatı',1,'Admin User',6,'Nakit',3,'OSMAN GÜZEN',NULL,'TL',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gider_yonetimi`
--

LOCK TABLES `gider_yonetimi` WRITE;
/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (1,'2025-12-26',70702.50,'Malzeme Gideri','CHANEL COCO HAM ESANS için 15 adet ara ödeme (1.650,00 USD @ 42,8500)',1,'Admin User',NULL,'Diğer','luzi','TL',NULL),(2,'2025-12-26',16068.75,'Malzeme Gideri','alkol için 250 adet ara ödeme (375,00 USD @ 42,8500)',1,'Admin User',NULL,'Diğer','alkol','TL',NULL),(3,'2026-01-02',19925.25,'Malzeme Gideri','alkol için 310 adet ara ödeme (465,00 USD @ 42,8500)',1,'Admin User',NULL,'Diğer','alkol','TL',NULL),(4,'2026-01-02',1002.00,'Malzeme Gideri','CHANEL COCO jelatin için 1002 adet ara ödeme',1,'Admin User',NULL,'Kredi Kartı','jilatin ','TL',NULL),(5,'2026-01-02',100.00,'İşletme Gideri','cc',1,'Admin User','','Nakit','','USD',NULL),(6,'2026-01-02',100.00,'Enerji','dd',1,'Admin User','','Nakit','','EUR',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasa_hareketleri`
--

LOCK TABLES `kasa_hareketleri` WRITE;
/*!40000 ALTER TABLE `kasa_hareketleri` DISABLE KEYS */;
INSERT INTO `kasa_hareketleri` VALUES (5,'2026-01-02 02:08:00','kasa_ekle','TL',NULL,700.00,'TL',1.0000,700.00,NULL,NULL,'','Admin User',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,'2026-01-02 00:00:00','gider_cikisi','TL',NULL,19925.25,'TL',NULL,19925.25,'cerceve_sozlesmeler',7,'alkol için 310 adet ara ödeme (465,00 USD @ 42,8500)','Admin User','alkol',NULL,NULL,'Diğer',NULL,NULL,NULL),(7,'2026-01-02 00:00:00','gider_cikisi','TL',NULL,1002.00,'TL',NULL,1002.00,'cerceve_sozlesmeler',5,'CHANEL COCO jelatin için 1002 adet ara ödeme','Admin User','jilatin ',NULL,NULL,'Kredi Kartı',NULL,NULL,NULL),(8,'2026-01-02 00:00:00','gider_cikisi','USD',NULL,100.00,'USD',NULL,100.00,'gider_yonetimi',5,'cc','Admin User','',NULL,NULL,'Nakit',NULL,NULL,NULL),(9,'2026-01-02 00:00:00','gider_cikisi','EUR',NULL,100.00,'EUR',NULL,5050.70,'gider_yonetimi',6,'dd','Admin User','',NULL,NULL,'Nakit',NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasa_islemleri`
--

LOCK TABLES `kasa_islemleri` WRITE;
/*!40000 ALTER TABLE `kasa_islemleri` DISABLE KEYS */;
INSERT INTO `kasa_islemleri` VALUES (8,'2025-12-31 09:57:00','kasa_ekle',1000.00,'TL',NULL,NULL,'','Admin User'),(9,'2025-12-31 15:55:00','kasa_ekle',500.00,'TL',NULL,NULL,'','Admin User');
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
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tablosu`
--

LOCK TABLES `log_tablosu` WRITE;
/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (208,'2026-01-01 18:39:11','Admin User','Taksit Planı #3 iptal edildi.','UPDATE','2026-01-01 15:39:11'),(209,'2026-01-02 02:01:46','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-01 23:01:46'),(210,'2026-01-02 02:06:51','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-01 23:06:51'),(211,'2026-01-02 04:23:00','Admin User','İşletme Gideri kategorisinde 100 TL tutarında gider eklendi','CREATE','2026-01-02 01:23:00'),(212,'2026-01-02 04:28:21','Admin User','Enerji kategorisinde 100 TL tutarında gider eklendi','CREATE','2026-01-02 01:28:21'),(213,'2026-01-02 05:18:08','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-02 02:18:08'),(214,'2026-01-02 19:24:06','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-02 16:24:06'),(215,'2026-01-03 01:02:46','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-02 22:02:46'),(216,'2026-01-03 01:17:11','Admin User','armani you ürünü armani you olarak güncellendi','UPDATE','2026-01-02 22:17:11'),(217,'2026-01-03 01:19:30','Admin User','MEHMET FATİH GÜZEN müşterisi için yeni sipariş oluşturuldu (ID: 7)','CREATE','2026-01-02 22:19:30'),(218,'2026-01-03 01:19:45','Admin User','MEHMET FATİH GÜZEN müşterisine ait 7 nolu siparişin yeni durumu: Onaylandı','UPDATE','2026-01-02 22:19:45'),(219,'2026-01-03 01:20:37','Admin User','armani you ürünü armani you olarak güncellendi','UPDATE','2026-01-02 22:20:37'),(220,'2026-01-03 04:28:44','Admin User','gökan tedarikçisine Urun4, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-01-03 01:28:44'),(221,'2026-01-03 04:29:25','Admin User','gökan tedarikçisine Urun4, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-01-03 01:29:25'),(222,'2026-01-03 04:29:49','Admin User','gökan tedarikçisine Urun4, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-01-03 01:29:49'),(223,'2026-01-03 04:30:22','Admin User','gökan tedarikçisine Urun4, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-01-03 01:30:22'),(224,'2026-01-03 04:30:44','Admin User','gökan tedarikçisine Urun4, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-01-03 01:30:44'),(225,'2026-01-03 04:31:10','Admin User','gökan tedarikçisine Urun4, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-01-03 01:31:10'),(226,'2026-01-03 04:31:42','Admin User','Urun4 ürünü Urun4 olarak güncellendi','UPDATE','2026-01-03 01:31:42'),(227,'2026-01-03 04:45:05','Admin User','Urun4, takım malzemesi Urun4, takım olarak güncellendi','UPDATE','2026-01-03 01:45:05'),(228,'2026-01-03 04:45:11','Admin User','Urun4, paket malzemesi Urun4, paket olarak güncellendi','UPDATE','2026-01-03 01:45:11'),(229,'2026-01-03 04:45:21','Admin User','Urun4, kutu malzemesi Urun4, kutu olarak güncellendi','UPDATE','2026-01-03 01:45:21'),(230,'2026-01-03 04:45:31','Admin User','Urun4, jelatin malzemesi Urun4, jelatin olarak güncellendi','UPDATE','2026-01-03 01:45:31'),(231,'2026-01-03 04:45:41','Admin User','Urun4, ham esans malzemesi Urun4, ham esans olarak güncellendi','UPDATE','2026-01-03 01:45:41'),(232,'2026-01-03 04:45:51','Admin User','Urun4, etiket malzemesi Urun4, etiket olarak güncellendi','UPDATE','2026-01-03 01:45:51'),(233,'2026-01-03 04:46:31','Admin User','Urun4, Esans esansı güncellendi','UPDATE','2026-01-03 01:46:31'),(234,'2026-01-03 05:30:34','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-01-03 02:30:34'),(235,'2026-01-03 05:31:30','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-03 02:31:30'),(236,'2026-01-03 05:31:34','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-01-03 02:31:34'),(237,'2026-01-03 05:45:21','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-03 02:45:21'),(238,'2026-01-03 05:45:24','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-01-03 02:45:24'),(239,'2026-01-05 10:22:35','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-05 07:22:35'),(240,'2026-01-05 10:40:53','Admin User','gökan tedarikçisine PO-2026-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-01-05 07:40:53'),(241,'2026-01-05 13:30:38','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-01-05 10:30:38'),(242,'2026-01-05 17:08:52','Admin User','Urun4, Esans esansı için iş emri oluşturuldu','CREATE','2026-01-05 14:08:52'),(243,'2026-01-05 17:51:05','Admin User','zülkür  tedarikçisine alkol malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-01-05 14:51:05'),(244,'2026-01-05 17:51:23','Admin User','zülkür  tedarikçisine PO-2026-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-01-05 14:51:23'),(245,'2026-01-05 18:18:46','Admin User','Urun4 ürünü için montaj iş emri oluşturuldu','CREATE','2026-01-05 15:18:46');
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
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `malzemeler`
--

LOCK TABLES `malzemeler` WRITE;
/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (1,'CHANEL COCO HAM ESANS','ham_esans','',906.35,'kg',119.83,'USD',0,5,'KUTU','raf 1',0),(2,'CHANEL COCO TAKIM','takm','',9.00,'adet',2.20,'USD',0,5,'yazıcı','1',0),(3,'CHANEL COCO kutu','kutu','',9.00,'adet',0.50,'USD',0,7,'KUTU','raf 1',0),(4,'CHANEL COCO jelatin','jelatin','',992.09,'kg',1.00,'TL',0,5,'JELATİN','1',0),(5,'CHANEL COCO paket','paket','',1000.00,'adet',1.00,'USD',0,5,'DIŞ KUTU','1',0),(7,'alkol','alkol','',560.00,'lt',1.50,'USD',0,5,'ALKOL','1',0),(8,'kahve kutu','kutu','',0.00,'adet',0.00,'USD',0,5,'DEPO A','A 1',0),(26,'m','ham_esans','',0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(29,'abc, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(30,'abc, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(31,'Guzel Urun, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(32,'Guzel Urun, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(33,'Guzel Urun, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(34,'Guzel Urun, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(35,'Guzel Urun, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(36,'Guzel Urun, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'ALKOL','1',0),(37,'Urun 2, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(38,'Urun 2, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(39,'Urun 2, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(40,'Urun 2, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(41,'Urun 2, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(42,'Urun 2, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(43,'URUN3, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(44,'URUN3, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(45,'URUN3, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(46,'URUN3, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(47,'URUN3, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(48,'URUN3, takım','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(49,'Urun4, etiket','etiket','null',20.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(50,'Urun4, ham esans','ham_esans','null',20.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(51,'Urun4, jelatin','jelatin','null',20.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(52,'Urun4, kutu','kutu','null',20.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(53,'Urun4, paket','paket','null',20.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0),(54,'Urun4, takım','takm','null',20.00,'adet',0.00,'TRY',0,0,'DEPO A','A 1',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `montaj_is_emirleri`
--

LOCK TABLES `montaj_is_emirleri` WRITE;
/*!40000 ALTER TABLE `montaj_is_emirleri` DISABLE KEYS */;
INSERT INTO `montaj_is_emirleri` VALUES (1,'2025-12-25','Admin User','1','chanel coco',500.00,'adet','2025-12-25','2025-12-25',' kırık','tamamlandi',498.00,2.00,5,'2025-12-25','2025-12-25'),(2,'2025-12-25','Admin User','1','chanel coco',490.00,'adet','2025-12-25','2025-12-25',' ','tamamlandi',470.00,20.00,6,'2025-12-25','2025-12-26'),(3,'2025-12-26','Admin User','1','chanel coco',1.00,'adet','2025-12-26','2025-12-26',' ','tamamlandi',0.00,1.00,5,'2025-12-26','2025-12-26'),(4,'2026-01-05','Admin User','21','Urun4',10.00,'adet','2026-01-05','2026-01-05','','olusturuldu',0.00,10.00,6,NULL,NULL);
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
INSERT INTO `montaj_is_emri_malzeme_listesi` VALUES (1,'1','CHANEL COCO HAM ESANS','ham_esans',75.00,'adet'),(1,'4','CHANEL COCO jelatin','jelatin',5.00,'adet'),(1,'3','CHANEL COCO kutu','kutu',500.00,'adet'),(1,'2','CHANEL COCO TAKIM','takm',500.00,'adet'),(2,'1','CHANEL COCO HAM ESANS','ham_esans',73.50,'adet'),(2,'4','CHANEL COCO jelatin','jelatin',4.90,'adet'),(2,'3','CHANEL COCO kutu','kutu',490.00,'adet'),(2,'2','CHANEL COCO TAKIM','takm',490.00,'adet'),(3,'1','CHANEL COCO HAM ESANS','ham_esans',0.15,'adet'),(3,'4','CHANEL COCO jelatin','jelatin',0.01,'adet'),(3,'3','CHANEL COCO kutu','kutu',1.00,'adet'),(3,'2','CHANEL COCO TAKIM','takm',1.00,'adet'),(4,'49','Urun4, etiket','etiket',10.00,'adet'),(4,'51','Urun4, jelatin','jelatin',10.00,'adet'),(4,'52','Urun4, kutu','kutu',10.00,'adet'),(4,'53','Urun4, paket','paket',10.00,'adet'),(4,'54','Urun4, takım','takm',10.00,'adet'),(4,'ES-251231-749','Urun4, Esans','esans',10.00,'adet');
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satinalma_siparis_kalemleri`
--

LOCK TABLES `satinalma_siparis_kalemleri` WRITE;
/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `satinalma_siparis_kalemleri` VALUES (28,12,102,'SAVAGE ELİXER TAKIM',100.00,'adet',2.70,'USD',270.00,0.00,''),(29,13,49,'Urun4, etiket',200.00,'adet',1.00,'TL',200.00,0.00,''),(30,14,7,'alkol',10.00,'lt',1.50,'USD',15.00,0.00,'');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satinalma_siparisler`
--

LOCK TABLES `satinalma_siparisler` WRITE;
/*!40000 ALTER TABLE `satinalma_siparisler` DISABLE KEYS */;
INSERT INTO `satinalma_siparisler` VALUES (12,'PO-2025-00001',2,'MEHMET KIRMIZIGÜL','2025-12-28','2025-12-31','gonderildi','bekliyor',NULL,270.00,'USD','',1,'Admin User','2025-12-28 16:37:32','2025-12-28 16:48:46'),(13,'PO-2026-00001',5,'gökan','2026-01-05','2026-01-10','taslak','bekliyor',NULL,200.00,'TL','',1,'Admin User','2026-01-05 07:40:53','2026-01-05 07:40:53'),(14,'PO-2026-00002',8,'zülkür ','2026-01-05','2026-01-08','taslak','bekliyor',NULL,15.00,'USD','',1,'Admin User','2026-01-05 14:51:23','2026-01-05 14:51:23');
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
INSERT INTO `siparis_kalemleri` VALUES (1,1,'chanel coco',12,'adet',10.00,120.00,'USD'),(2,1,'chanel coco',88,'adet',10.00,880.00,'USD'),(3,1,'chanel coco',142,'adet',10.00,1420.00,'USD'),(3,7,'DİOR SAVAGE',18,'adet',12.00,216.00,'USD'),(4,7,'DİOR SAVAGE',20,'adet',12.00,240.00,'USD'),(4,1,'chanel coco',14,'adet',10.00,140.00,'USD'),(5,1,'chanel coco',15,'adet',10.00,150.00,'USD'),(5,7,'DİOR SAVAGE',150,'adet',12.00,1800.00,'USD'),(6,1,'chanel coco',30,'0',10.00,300.00,'USD'),(6,7,'DİOR SAVAGE',120,'adet',12.00,1440.00,'USD'),(7,4,'armani you',100,'adet',25.00,2500.00,'USD');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siparisler`
--

LOCK TABLES `siparisler` WRITE;
/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
INSERT INTO `siparisler` VALUES (1,3,'OSMAN GÜZEN','2025-12-25 14:18:11','tamamlandi',12,'OSMAN GÜZEN',1,'Admin User','2025-12-25 14:18:52','','bekliyor',NULL,0.00,'USD'),(2,3,'OSMAN GÜZEN','2025-12-25 14:22:00','tamamlandi',88,'OSMAN GÜZEN',1,'Admin User','2025-12-25 14:22:18','','bekliyor',NULL,0.00,'USD'),(3,3,'OSMAN GÜZEN','2025-12-26 10:05:43','tamamlandi',160,'Personel: Admin User',1,'Admin User','2025-12-26 10:05:54','habersiz sipariş','bekliyor',NULL,0.00,'USD'),(4,2,'İDRİS GÜZEN','2025-12-26 10:55:08','tamamlandi',34,'Personel: Admin User',1,'Admin User','2025-12-26 10:55:18','','odendi',NULL,380.00,'USD'),(5,1,'MEHMET FATİH GÜZEN','2025-12-26 13:51:41','tamamlandi',165,'Personel: Admin User',1,'Admin User','2025-12-26 13:51:54','','odendi',NULL,1950.00,'USD'),(6,3,'OSMAN GÜZEN','2025-12-26 14:02:20','tamamlandi',150,'Personel: Admin User',1,'Admin User','2025-12-26 14:05:32','','odendi',NULL,1740.00,'USD'),(7,1,'MEHMET FATİH GÜZEN','2026-01-03 01:19:30','onaylandi',100,'Personel: Admin User',1,'Admin User','2026-01-03 01:19:45','','bekliyor',NULL,0.00,'USD');
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
INSERT INTO `sirket_kasasi` VALUES (1,'TL',-20227.25,'2026-01-02 04:21:24'),(2,'USD',-83.00,'2026-01-02 04:23:00'),(3,'EUR',-100.00,'2026-01-02 04:28:21');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taksit_detaylari`
--

LOCK TABLES `taksit_detaylari` WRITE;
/*!40000 ALTER TABLE `taksit_detaylari` DISABLE KEYS */;
INSERT INTO `taksit_detaylari` VALUES (1,1,1,'2025-12-31',126.67,0.00,126.67,'odendi','2025-12-31 13:38:36'),(2,1,2,'2026-01-31',126.67,0.00,126.67,'odendi','2025-12-31 15:07:03'),(3,1,3,'2026-03-03',126.66,0.00,126.66,'odendi','2025-12-31 15:21:41'),(4,2,1,'2025-12-31',585.33,585.33,0.00,'bekliyor',NULL),(5,2,2,'2026-01-31',585.33,585.33,0.00,'bekliyor',NULL),(6,2,3,'2026-03-03',585.34,585.34,0.00,'bekliyor',NULL),(7,3,1,'2025-12-31',40.00,40.00,0.00,'bekliyor',NULL),(8,3,2,'2026-01-31',40.00,40.00,0.00,'bekliyor',NULL),(9,3,3,'2026-03-03',40.00,40.00,0.00,'bekliyor',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taksit_planlari`
--

LOCK TABLES `taksit_planlari` WRITE;
/*!40000 ALTER TABLE `taksit_planlari` DISABLE KEYS */;
INSERT INTO `taksit_planlari` VALUES (1,2,'İDRİS GÜZEN',380.00,0.00,0.00,380.00,'USD',3,'2025-12-31 13:38:15','2025-12-31','tamamlandi','',1),(2,3,'OSMAN GÜZEN',1756.00,0.00,0.00,1756.00,'USD',3,'2025-12-31 15:24:10','2025-12-31','iptal','',1),(3,3,'OSMAN GÜZEN',120.00,0.00,0.00,120.00,'USD',3,'2025-12-31 15:27:16','2025-12-31','iptal','',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taksit_siparis_baglantisi`
--

LOCK TABLES `taksit_siparis_baglantisi` WRITE;
/*!40000 ALTER TABLE `taksit_siparis_baglantisi` DISABLE KEYS */;
INSERT INTO `taksit_siparis_baglantisi` VALUES (1,1,4,380.00),(2,2,3,1636.00),(3,2,1,120.00),(4,3,1,120.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urun_agaci`
--

LOCK TABLES `urun_agaci` WRITE;
/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (2,1,'chanel coco','jelatin','4','CHANEL COCO jelatin',0.01,'urun'),(3,1,'chanel coco','kutu','3','CHANEL COCO kutu',1.00,'urun'),(4,1,'chanel coco','takm','2','CHANEL COCO TAKIM',1.00,'urun'),(29,18,'Guzel Urun','etiket','31','Guzel Urun, etiket',1.00,'urun'),(30,18,'Guzel Urun','ham_esans','32','Guzel Urun, ham esans',1.00,'urun'),(31,18,'Guzel Urun','jelatin','33','Guzel Urun, jelatin',1.00,'urun'),(32,18,'Guzel Urun','kutu','34','Guzel Urun, kutu',1.00,'urun'),(33,18,'Guzel Urun','paket','35','Guzel Urun, paket',1.00,'urun'),(34,18,'Guzel Urun','takm','36','Guzel Urun, takım',1.00,'urun'),(37,19,'Urun 2','etiket','37','Urun 2, etiket',1.00,'urun'),(38,19,'Urun 2','jelatin','39','Urun 2, jelatin',1.00,'urun'),(39,19,'Urun 2','kutu','40','Urun 2, kutu',1.00,'urun'),(40,19,'Urun 2','takm','41','Urun 2, takım',1.00,'urun'),(41,19,'Urun 2','paket','42','Urun 2, paket',1.00,'urun'),(42,20,'URUN3','etiket','43','URUN3, etiket',1.00,'urun'),(43,20,'URUN3','jelatin','45','URUN3, jelatin',1.00,'urun'),(44,20,'URUN3','kutu','46','URUN3, kutu',1.00,'urun'),(45,20,'URUN3','paket','47','URUN3, paket',1.00,'urun'),(46,20,'URUN3','takm','48','URUN3, takım',1.00,'urun'),(47,21,'Urun4','etiket','49','Urun4, etiket',1.00,'urun'),(48,21,'Urun4','jelatin','51','Urun4, jelatin',1.00,'urun'),(49,21,'Urun4','kutu','52','Urun4, kutu',1.00,'urun'),(50,21,'Urun4','paket','53','Urun4, paket',1.00,'urun'),(51,21,'Urun4','takm','54','Urun4, takım',1.00,'urun'),(52,21,'Urun4','esans','ES-251231-749','Urun4, Esans',1.00,'urun'),(53,12,'Urun4, Esans','malzeme','7','alkol',10.00,'esans'),(56,4,'armani you','esans','005','VIKTORIA SECRET , BOM ŞHEL',10.00,'urun');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

LOCK TABLES `urunler` WRITE;
/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (1,'chanel coco','',667,'adet',10.00,'USD',0.00,'TRY',100,'DEPO A','A 1','uretilen',NULL),(2,'invektus','',0,'adet',12.00,'USD',0.00,'TRY',0,'DEPO B','B 1','uretilen',NULL),(3,'lavi bel','',0,'adet',15.00,'TRY',0.00,'TRY',80,'DEPO A','A 1','uretilen',NULL),(4,'armani you','',1,'adet',25.00,'USD',0.00,'TRY',10,'DEPO A','A 1','uretilen',NULL),(6,'kahve','',0,'adet',5.00,'USD',0.00,'TRY',100,'DEPO A','A 1','uretilen',NULL),(7,'DİOR SAVAGE','',192,'adet',12.00,'USD',0.00,'TRY',10,'DEPO C','C 1','hazir_alinan',NULL),(8,'Deneme','',12,'adet',0.00,'TRY',0.00,'TRY',111,'ALKOL','1','uretilen',NULL),(9,'Barış','',0,'adet',0.00,'TRY',0.00,'TRY',0,'ALKOL','1','uretilen',NULL),(18,'Guzel Urun','',100,'adet',0.00,'TRY',0.00,'TRY',0,'ALKOL','1','uretilen',NULL),(19,'Urun 2','',100,'adet',0.00,'TRY',0.00,'TRY',0,'DEPO A','A 1','uretilen',NULL),(20,'URUN3','',11,'adet',0.00,'TRY',0.00,'TRY',0,'DEPO A','A 1','uretilen',NULL),(21,'Urun4','',0,'adet',0.00,'TRY',0.00,'TRY',444,'DEPO A','A 1','uretilen',NULL);
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

-- Dump completed on 2026-01-12 13:47:10

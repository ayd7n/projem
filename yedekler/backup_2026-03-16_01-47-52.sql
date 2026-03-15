
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ayarlar` (
  `ayar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ayar_anahtar` varchar(255) NOT NULL,
  `ayar_deger` varchar(255) NOT NULL,
  PRIMARY KEY (`ayar_id`),
  UNIQUE KEY `ayar_anahtar` (`ayar_anahtar`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.8500'),(2,'euro_kuru','50.5070'),(3,'son_otomatik_yedek_tarihi','2026-03-16 01:45:46'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170\n6356317802');
/*!40000 ALTER TABLE `ayarlar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `cek_kasasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `cek_kasasi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (1,10,'ADEM ',15,'	dior savage, tak??m',2.10,'USD',999999,1000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:07:34','',1,'TL',NULL),(2,5,'EBUBEK??R ',10,'	dior savage, kutu',0.40,'USD',99999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:08:07','',1,'TL',NULL),(3,6,'ESENG??L ',10,'	dior savage, kutu',0.45,'TL',999999,1000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:08:49','',1,'TL',NULL),(4,12,'G??KHAN ',16,'	dior savage, jelatin',1.00,'TL',999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:09:15','',1,'TL',NULL),(5,9,'KIRMIZIG??L',15,'	dior savage, tak??m',2.50,'USD',9999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:09:47','',1,'TL',NULL),(6,14,'KIRMIZIG??L ALKOL',18,'ALKOL',1.60,'USD',9999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:10:15','',1,'TL',NULL),(7,2,'LUZKIM',14,'	dior savage, ham esans',120.00,'USD',99999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:10:55','',1,'TL',NULL),(8,8,'MAV?? ET??KET',9,'	dior savage, etiket',0.09,'USD',999999,990,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:11:42','',1,'TL',NULL),(9,13,'RAMAZAN ',12,'	dior savage, paket',15.00,'TL',999999,3000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:12:15','',1,'TL',NULL),(10,7,'SARI ET??KET',9,'	dior savage, etiket',2.00,'TL',999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:12:51','',1,'TL',NULL),(11,3,'SELUZ',14,'	dior savage, ham esans',115.00,'USD',99999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:13:22','',1,'TL',NULL),(12,4,'??ENER',10,'	dior savage, kutu',0.40,'USD',99999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:13:51','',1,'TL',NULL),(13,1,'TEVF??K BEY',14,'	dior savage, ham esans',85.00,'EUR',9999999,15,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:14:19','',1,'TL',NULL),(14,11,'U??UR TAKIM',15,'	dior savage, tak??m',2.30,'EUR',99999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:14:55','',1,'TL',NULL),(15,11,'U??UR TAKIM',6,'chanel blu, tak??m',2.40,'USD',9999999,300,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:15:22','',1,'TL',NULL),(16,1,'TEVF??K BEY',7,'chanel blu, ham esans',82.00,'EUR',99999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:15:54','',1,'TL',NULL),(17,4,'??ENER',5,'chanel blu, kutu',0.40,'USD',99999999,1000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:16:41','',1,'TL',NULL),(18,3,'SELUZ',7,'chanel blu, ham esans',120.00,'USD',999999999,45,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:17:12','',1,'TL',NULL),(19,7,'SARI ET??KET',1,'chanel blu, etiket',2.00,'TL',99999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:22:00','',1,'TL',NULL),(20,13,'RAMAZAN ',8,'chanel blu, paket',16.00,'TL',999999999,2000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:22:27','',1,'TL',NULL),(21,8,'MAV?? ET??KET',1,'chanel blu, etiket',1.75,'TL',9999999,750,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:23:39','',1,'TL',NULL),(22,12,'G??KHAN ',2,'chanel blu, jelatin',1.00,'TL',999999999,500,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:24:28','',1,'TL',NULL),(23,10,'ADEM ',24,'155 a, tak??m',2.10,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:52:03','',1,'TL',NULL),(24,5,'EBUBEK??R ',23,'155 a, kutu',0.50,'USD',9999,0,'2026-03-08','2026-04-12','Admin User','2026-03-08 22:52:36','',1,'TL',NULL),(25,6,'ESENG??L ',23,'155 a, kutu',0.55,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:53:11','',1,'TL',NULL),(26,12,'G??KHAN ',20,'155 a, jelatin',1.00,'TL',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:53:41','',1,'TL',NULL),(27,9,'KIRMIZIG??L',24,'155 a, tak??m',2.09,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:54:26','',1,'TL',NULL),(28,2,'LUZKIM',25,'155 a, ham esans',100.00,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:54:53','',1,'TL',NULL),(29,8,'MAV?? ET??KET',19,'155 a, etiket',0.90,'TL',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:55:27','',1,'TL',NULL),(30,13,'RAMAZAN ',26,'155 a, paket',12.00,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:56:30','',1,'TL',NULL),(31,7,'SARI ET??KET',19,'155 a, etiket',1.20,'TL',999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:56:59','',1,'TL',NULL),(32,3,'SELUZ',25,'155 a, ham esans',90.00,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:57:31','',1,'TL',NULL),(33,4,'??ENER',23,'155 a, kutu',0.40,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:58:07','',1,'TL',NULL),(34,1,'TEVF??K BEY',25,'155 a, ham esans',62.00,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:58:38','',1,'TL',NULL),(35,11,'U??UR TAKIM',24,'155 a, tak??m',1.30,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:59:14','',1,'TL',NULL),(36,15,'MERKES ??EBEKE',17,'SU',1.00,'TL',99999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 23:03:46','',1,'TL',NULL);
/*!40000 ALTER TABLE `cerceve_sozlesmeler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `esans_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `esans_ihtiyaclari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `esans_is_emirleri` DISABLE KEYS */;
INSERT INTO `esans_is_emirleri` VALUES (1,'2026-03-02','Admin User','ES-260302-603','chanel blu, Esans','e 101','chanel blu',100.00,'lt','2026-03-02',1,'2026-03-03','2026-03-02','2026-03-02',' BE?? L??TRE ESANS EKS??K KALDI','tamamlandi',95.00,5.00),(2,'2026-03-02','Admin User','ES-260302-669','	dior savage, Esans','w 500','dior savage',100.00,'lt','2026-03-02',1,'2026-03-03','2026-03-02','2026-03-02',' ','tamamlandi',100.00,0.00),(3,'2026-03-02','Admin User','ES-260302-603','chanel blu, Esans','e 101','chanel blu',100.00,'lt','2026-03-02',1,'2026-03-03','2026-03-02','2026-03-02',' ','tamamlandi',100.00,0.00),(4,'2026-03-02','Admin User','ES-260302-603','chanel blu, Esans','e 101','chanel blu',1000.00,'lt','2026-03-02',1,'2026-03-03','2026-03-02','2026-03-02',' ','tamamlandi',1000.00,0.00);
/*!40000 ALTER TABLE `esans_is_emirleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `esans_is_emri_malzeme_listesi` DISABLE KEYS */;
INSERT INTO `esans_is_emri_malzeme_listesi` VALUES (1,17,'SU','malzeme',8.00,'lt'),(1,18,'ALKOL','malzeme',80.00,'lt'),(1,7,'chanel blu, ham esans','malzeme',15.00,'lt'),(2,18,'ALKOL','malzeme',80.00,'lt'),(2,17,'SU','malzeme',8.00,'lt'),(2,14,'	dior savage, ham esans','malzeme',15.00,'lt'),(3,17,'SU','malzeme',8.00,'lt'),(3,18,'ALKOL','malzeme',80.00,'lt'),(3,7,'chanel blu, ham esans','malzeme',15.00,'lt'),(4,17,'SU','malzeme',80.00,'lt'),(4,18,'ALKOL','malzeme',800.00,'lt'),(4,7,'chanel blu, ham esans','malzeme',150.00,'lt');
/*!40000 ALTER TABLE `esans_is_emri_malzeme_listesi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `esanslar` DISABLE KEYS */;
INSERT INTO `esanslar` VALUES (1,'ES-260302-603','chanel blu, Esans','',1195.00,'lt',1.00,'w 500','dior savage'),(2,'ES-260302-669','	dior savage, Esans','',100.00,'lt',1.00,'e 101','chanel blu'),(3,'ES-260308-259','155 a, Esans','',0.00,'lt',1.00,NULL,NULL);
/*!40000 ALTER TABLE `esanslar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `gelir_taksit_planlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `gelir_taksit_planlari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `gelir_taksitleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `gelir_taksitleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `gelir_yonetimi` DISABLE KEYS */;
INSERT INTO `gelir_yonetimi` VALUES (1,'2026-03-02 00:00:00',4250.00,'USD','Sipari?? ??demesi','Sipari?? No: #2 tahsilat??',1,'Admin User',2,'Nakit',1,'OSMAN ',NULL,'USD',NULL),(2,'2026-03-02 00:00:00',3150.00,'USD','Sipari?? ??demesi','Sipari?? No: #1 tahsilat??',1,'Admin User',1,'??ek',2,'HAMZA',NULL,'TL',NULL),(3,'2026-03-02 00:00:00',2430.00,'USD','Sipari?? ??demesi','Sipari?? No: #2 tahsilat??',1,'Admin User',2,'Kredi Kart??',1,'OSMAN ',NULL,'USD',NULL),(4,'2026-03-02 00:00:00',2028.00,'USD','Sipari?? ??demesi','Sipari?? No: #1 tahsilat??',1,'Admin User',1,'Nakit',2,'HAMZA',NULL,'USD',NULL);
/*!40000 ALTER TABLE `gelir_yonetimi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (1,'2026-03-02',500.00,'Malzeme Gideri','chanel blu, jelatin i??in 500 adet ara ??deme',1,'Admin User',NULL,'Havale/EFT','G??KHAN ','TL',NULL),(2,'2026-03-02',1312.50,'Malzeme Gideri','chanel blu, etiket i??in 750 adet ara ??deme',1,'Admin User',NULL,'Havale/EFT','MAV?? ET??KET','TL',NULL),(3,'2026-03-02',32000.00,'Malzeme Gideri','chanel blu, paket i??in 2000 adet ara ??deme',1,'Admin User',NULL,'Havale/EFT','RAMAZAN ','TL',NULL),(4,'2026-03-02',231390.00,'Malzeme Gideri','chanel blu, ham esans i??in 45 adet ara ??deme (5.400,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','SELUZ','TL',NULL),(5,'2026-03-02',8570.00,'Malzeme Gideri','chanel blu, kutu i??in 500 adet ara ??deme (200,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','??ENER','TL',NULL),(6,'2026-03-02',30852.00,'Malzeme Gideri','chanel blu, tak??m i??in 300 adet ara ??deme (720,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','U??UR TAKIM','TL',NULL),(7,'2026-03-02',64396.43,'Malzeme Gideri','	dior savage, ham esans i??in 15 adet ara ??deme (1.275,00 EUR @ 50,5070)',1,'Admin User',NULL,'Havale/EFT','TEVF??K BEY','TL',NULL),(8,'2026-03-02',3428.00,'Malzeme Gideri','chanel blu, kutu i??in 200 adet ara ??deme (80,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','??ENER','USD',NULL),(9,'2026-03-02',5142.00,'Malzeme Gideri','chanel blu, kutu i??in 300 adet ara ??deme (120,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','??ENER','TL',NULL),(10,'2026-03-02',450.00,'Malzeme Gideri','	dior savage, kutu i??in 1000 adet ara ??deme',1,'Admin User',NULL,'Havale/EFT','ESENG??L ','TL',NULL),(11,'2026-03-02',45000.00,'Malzeme Gideri','	dior savage, paket i??in 3000 adet ara ??deme',1,'Admin User',NULL,'Havale/EFT','RAMAZAN ','TL',NULL),(12,'2026-03-02',3470.85,'Malzeme Gideri','	dior savage, etiket i??in 900 adet ara ??deme (81,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','MAV?? ET??KET','TL',NULL),(13,'2026-03-02',347.09,'Malzeme Gideri','	dior savage, etiket i??in 90 adet ara ??deme (8,10 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','MAV?? ET??KET','TL',NULL),(14,'2026-03-02',89985.00,'Malzeme Gideri','	dior savage, tak??m i??in 1000 adet ara ??deme (2.100,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','ADEM ','TL',NULL);
/*!40000 ALTER TABLE `gider_yonetimi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `is_merkezleri` DISABLE KEYS */;
INSERT INTO `is_merkezleri` VALUES (1,'ABUBEK??R ??NEL',''),(2,'AHMET ERS??N','');
/*!40000 ALTER TABLE `is_merkezleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `kasa_hareketleri` DISABLE KEYS */;
INSERT INTO `kasa_hareketleri` VALUES (1,'2026-03-02 00:00:00','gelir_girisi','USD',NULL,4250.00,'USD',NULL,182112.50,'gelir_yonetimi',1,'Sipari?? No: #2 tahsilat??','Admin User',NULL,'OSMAN ',NULL,'Nakit',NULL,NULL,NULL),(2,'2026-03-02 00:00:00','gelir_girisi','TL',NULL,3150.00,'USD',NULL,134977.50,'gelir_yonetimi',2,'Sipari?? No: #1 tahsilat??','Admin User',NULL,'HAMZA',NULL,'??ek',NULL,NULL,NULL),(3,'2026-03-02 00:00:00','gelir_girisi','USD',NULL,2430.00,'USD',NULL,104125.50,'gelir_yonetimi',3,'Sipari?? No: #2 tahsilat??','Admin User',NULL,'OSMAN ',NULL,'Kredi Kart??',NULL,NULL,NULL),(4,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,500.00,'TL',NULL,500.00,'cerceve_sozlesmeler',22,'chanel blu, jelatin i??in 500 adet ara ??deme','Admin User','G??KHAN ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(5,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,1312.50,'TL',NULL,1312.50,'cerceve_sozlesmeler',21,'chanel blu, etiket i??in 750 adet ara ??deme','Admin User','MAV?? ET??KET',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(6,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,32000.00,'TL',NULL,32000.00,'cerceve_sozlesmeler',20,'chanel blu, paket i??in 2000 adet ara ??deme','Admin User','RAMAZAN ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(7,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,231390.00,'TL',NULL,231390.00,'cerceve_sozlesmeler',18,'chanel blu, ham esans i??in 45 adet ara ??deme (5.400,00 USD @ 42,8500)','Admin User','SELUZ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(8,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,8570.00,'TL',NULL,8570.00,'cerceve_sozlesmeler',17,'chanel blu, kutu i??in 500 adet ara ??deme (200,00 USD @ 42,8500)','Admin User','??ENER',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(9,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,30852.00,'TL',NULL,30852.00,'cerceve_sozlesmeler',15,'chanel blu, tak??m i??in 300 adet ara ??deme (720,00 USD @ 42,8500)','Admin User','U??UR TAKIM',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(10,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,64396.43,'TL',NULL,64396.43,'cerceve_sozlesmeler',13,'	dior savage, ham esans i??in 15 adet ara ??deme (1.275,00 EUR @ 50,5070)','Admin User','TEVF??K BEY',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(11,'2026-03-02 00:00:00','gider_cikisi','USD',NULL,80.00,'USD',NULL,3428.00,'cerceve_sozlesmeler',17,'chanel blu, kutu i??in 200 adet ara ??deme (80,00 USD @ 42,8500)','Admin User','??ENER',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(12,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,5142.00,'TL',NULL,5142.00,'cerceve_sozlesmeler',17,'chanel blu, kutu i??in 300 adet ara ??deme (120,00 USD @ 42,8500)','Admin User','??ENER',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(13,'2026-03-02 00:00:00','gelir_girisi','USD',NULL,2028.00,'USD',NULL,86899.80,'gelir_yonetimi',4,'Sipari?? No: #1 tahsilat??','Admin User',NULL,'HAMZA',NULL,'Nakit',NULL,NULL,NULL),(14,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,450.00,'TL',NULL,450.00,'cerceve_sozlesmeler',3,'	dior savage, kutu i??in 1000 adet ara ??deme','Admin User','ESENG??L ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(15,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,45000.00,'TL',NULL,45000.00,'cerceve_sozlesmeler',9,'	dior savage, paket i??in 3000 adet ara ??deme','Admin User','RAMAZAN ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(16,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,3470.85,'TL',NULL,3470.85,'cerceve_sozlesmeler',8,'	dior savage, etiket i??in 900 adet ara ??deme (81,00 USD @ 42,8500)','Admin User','MAV?? ET??KET',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(17,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,347.09,'TL',NULL,347.09,'cerceve_sozlesmeler',8,'	dior savage, etiket i??in 90 adet ara ??deme (8,10 USD @ 42,8500)','Admin User','MAV?? ET??KET',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(18,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,89985.00,'TL',NULL,89985.00,'cerceve_sozlesmeler',1,'	dior savage, tak??m i??in 1000 adet ara ??deme (2.100,00 USD @ 42,8500)','Admin User','ADEM ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL);
/*!40000 ALTER TABLE `kasa_hareketleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `kasa_islemleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `kasa_islemleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2026-03-02 10:42:26','Admin User','cerceve_sozlesmeler, esans_is_emirleri, esans_is_emri_malzeme_listesi, esanslar, gelir_yonetimi, gider_yonetimi, is_merkezleri, kasa_hareketleri, log_tablosu, lokasyonlar, malzemeler, montaj_is_emirleri, montaj_is_emri_malzeme_listesi, musteriler, satinalma_siparis_kalemleri, satinalma_siparisler, siparis_kalemleri, siparisler, sirket_kasasi, stok_hareket_kayitlari, stok_hareketleri_sozlesmeler, tanklar, tedarikciler, urun_agaci, urun_fotograflari, urunler tablolar?? temizlendi','DELETE','2026-03-02 07:42:26'),(2,'2026-03-02 10:43:35','Admin User','depo esans deposuna a raf?? eklendi','CREATE','2026-03-02 07:43:35'),(3,'2026-03-02 10:43:46','Admin User','depo a deposuna a raf?? eklendi','CREATE','2026-03-02 07:43:46'),(4,'2026-03-02 10:44:04','Admin User','depo b deposuna a raf?? eklendi','CREATE','2026-03-02 07:44:04'),(5,'2026-03-02 10:44:20','Admin User','depo c deposuna depo c raf?? eklendi','CREATE','2026-03-02 07:44:20'),(6,'2026-03-02 10:44:39','Admin User','depo kutu deposuna a raf?? eklendi','CREATE','2026-03-02 07:44:39'),(7,'2026-03-02 10:45:01','Admin User','depo tak??m deposuna a raf?? eklendi','CREATE','2026-03-02 07:45:01'),(8,'2026-03-02 10:45:44','Admin User','dior savage adl?? tank sisteme eklendi','CREATE','2026-03-02 07:45:44'),(9,'2026-03-02 10:46:08','Admin User','chanel blu adl?? tank sisteme eklendi','CREATE','2026-03-02 07:46:08'),(10,'2026-03-02 10:46:31','Admin User','ABUBEK??R ??NEL i?? merkezi eklendi','CREATE','2026-03-02 07:46:31'),(11,'2026-03-02 10:46:37','Admin User','AHMET ERS??N i?? merkezi eklendi','CREATE','2026-03-02 07:46:37'),(12,'2026-03-02 10:47:59','Admin User','chanel blu ??r??n?? sisteme eklendi','CREATE','2026-03-02 07:47:59'),(13,'2026-03-02 10:48:00','Admin User','Otomatik esans olu??turuldu: chanel blu, Esans (Tank: w 500)','CREATE','2026-03-02 07:48:00'),(14,'2026-03-02 10:49:11','Admin User','	dior savage ??r??n?? sisteme eklendi','CREATE','2026-03-02 07:49:11'),(15,'2026-03-02 10:49:11','Admin User','Otomatik esans olu??turuldu: 	dior savage, Esans (Tank: e 101)','CREATE','2026-03-02 07:49:11'),(16,'2026-03-02 10:52:15','Admin User','chanel blu ??r??n a??ac??ndan chanel blu, su bile??eni silindi','DELETE','2026-03-02 07:52:15'),(17,'2026-03-02 10:52:23','Admin User','chanel blu ??r??n a??ac??ndan chanel blu, fiksator bile??eni silindi','DELETE','2026-03-02 07:52:23'),(18,'2026-03-02 10:54:38','Admin User','SU malzemesi sisteme eklendi','CREATE','2026-03-02 07:54:38'),(19,'2026-03-02 10:55:18','Admin User','ALKOL malzemesi sisteme eklendi','CREATE','2026-03-02 07:55:18'),(20,'2026-03-02 10:55:52','Admin User','	dior savage, Esans ??r??n a??ac??na ALKOL bile??eni eklendi','CREATE','2026-03-02 07:55:52'),(21,'2026-03-02 10:56:14','Admin User','	dior savage, Esans ??r??n a??ac??na SU bile??eni eklendi','CREATE','2026-03-02 07:56:14'),(22,'2026-03-02 10:57:54','Admin User','	dior savage, Esans ??r??n a??ac??na 	dior savage, ham esans bile??eni eklendi','CREATE','2026-03-02 07:57:54'),(23,'2026-03-02 10:58:27','Admin User','chanel blu, Esans ??r??n a??ac??na SU bile??eni eklendi','CREATE','2026-03-02 07:58:27'),(24,'2026-03-02 10:58:54','Admin User','chanel blu, Esans ??r??n a??ac??na ALKOL bile??eni eklendi','CREATE','2026-03-02 07:58:54'),(25,'2026-03-02 10:59:11','Admin User','chanel blu, Esans ??r??n a??ac??na chanel blu, ham esans bile??eni eklendi','CREATE','2026-03-02 07:59:11'),(26,'2026-03-02 10:59:58','Admin User','TEVF??K BEY tedarik??isi sisteme eklendi','CREATE','2026-03-02 07:59:58'),(27,'2026-03-02 11:00:19','Admin User','LUZKIM tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:00:19'),(28,'2026-03-02 11:00:39','Admin User','SELUZ tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:00:39'),(29,'2026-03-02 11:00:58','Admin User','??ENER tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:00:58'),(30,'2026-03-02 11:01:12','Admin User','EBUBEK??R  tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:01:12'),(31,'2026-03-02 11:01:49','Admin User','ESENG??L  tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:01:49'),(32,'2026-03-02 11:02:12','Admin User','SARI ET??KET tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:02:12'),(33,'2026-03-02 11:02:27','Admin User','MAV?? ET??KET tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:02:27'),(34,'2026-03-02 11:02:41','Admin User','KIRMIZIG??L tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:02:41'),(35,'2026-03-02 11:02:52','Admin User','ADEM  tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:02:52'),(36,'2026-03-02 11:03:10','Admin User','U??UR TAKIM tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:03:10'),(37,'2026-03-02 11:03:29','Admin User','G??KHAN  tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:03:29'),(38,'2026-03-02 11:03:44','Admin User','RAMAZAN  tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:03:44'),(39,'2026-03-02 11:04:26','Admin User','KIRMIZIG??L ALKOL tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:04:26'),(40,'2026-03-02 11:04:53','Admin User','U??UR TAKIM tedarik??isi U??UR TAKIM olarak g??ncellendi','UPDATE','2026-03-02 08:04:53'),(41,'2026-03-02 11:04:54','Admin User','U??UR TAKIM tedarik??isi U??UR TAKIM olarak g??ncellendi','UPDATE','2026-03-02 08:04:54'),(42,'2026-03-02 11:07:34','Admin User','ADEM  tedarik??isine 	dior savage, tak??m malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:07:34'),(43,'2026-03-02 11:08:07','Admin User','EBUBEK??R  tedarik??isine 	dior savage, kutu malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:08:07'),(44,'2026-03-02 11:08:50','Admin User','ESENG??L  tedarik??isine 	dior savage, kutu malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:08:50'),(45,'2026-03-02 11:09:15','Admin User','G??KHAN  tedarik??isine 	dior savage, jelatin malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:09:15'),(46,'2026-03-02 11:09:47','Admin User','KIRMIZIG??L tedarik??isine 	dior savage, tak??m malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:09:47'),(47,'2026-03-02 11:10:15','Admin User','KIRMIZIG??L ALKOL tedarik??isine ALKOL malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:10:15'),(48,'2026-03-02 11:10:55','Admin User','LUZKIM tedarik??isine 	dior savage, ham esans malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:10:55'),(49,'2026-03-02 11:11:42','Admin User','MAV?? ET??KET tedarik??isine 	dior savage, etiket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:11:42'),(50,'2026-03-02 11:12:15','Admin User','RAMAZAN  tedarik??isine 	dior savage, paket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:12:15'),(51,'2026-03-02 11:12:51','Admin User','SARI ET??KET tedarik??isine 	dior savage, etiket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:12:51'),(52,'2026-03-02 11:13:22','Admin User','SELUZ tedarik??isine 	dior savage, ham esans malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:13:22'),(53,'2026-03-02 11:13:51','Admin User','??ENER tedarik??isine 	dior savage, kutu malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:13:51'),(54,'2026-03-02 11:14:19','Admin User','TEVF??K BEY tedarik??isine 	dior savage, ham esans malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:14:19'),(55,'2026-03-02 11:14:55','Admin User','U??UR TAKIM tedarik??isine 	dior savage, tak??m malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:14:55'),(56,'2026-03-02 11:15:22','Admin User','U??UR TAKIM tedarik??isine chanel blu, tak??m malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:15:22'),(57,'2026-03-02 11:15:54','Admin User','TEVF??K BEY tedarik??isine chanel blu, ham esans malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:15:54'),(58,'2026-03-02 11:16:41','Admin User','??ENER tedarik??isine chanel blu, kutu malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:16:41'),(59,'2026-03-02 11:17:12','Admin User','SELUZ tedarik??isine chanel blu, ham esans malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:17:12'),(60,'2026-03-02 11:18:39','Admin User','chanel blu, fiksator malzemesi sistemden silindi','DELETE','2026-03-02 08:18:39'),(61,'2026-03-02 11:18:43','Admin User','	dior savage, fiksator malzemesi sistemden silindi','DELETE','2026-03-02 08:18:43'),(62,'2026-03-02 11:19:35','Admin User','MERKES ??EBEKE tedarik??isi sisteme eklendi','CREATE','2026-03-02 08:19:35'),(63,'2026-03-02 11:22:00','Admin User','SARI ET??KET tedarik??isine chanel blu, etiket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:22:00'),(64,'2026-03-02 11:22:27','Admin User','RAMAZAN  tedarik??isine chanel blu, paket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:22:27'),(65,'2026-03-02 11:23:39','Admin User','MAV?? ET??KET tedarik??isine chanel blu, etiket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:23:39'),(66,'2026-03-02 11:24:28','Admin User','G??KHAN  tedarik??isine chanel blu, jelatin malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-02 08:24:28'),(67,'2026-03-02 11:27:10','Admin User','	dior savage ??r??n a??ac??ndaki 	dior savage, su bile??eni 	dior savage, su olarak g??ncellendi','UPDATE','2026-03-02 08:27:10'),(68,'2026-03-02 11:31:48','Admin User','	dior savage ??r??n a??ac??ndaki 	dior savage, su bile??eni 	dior savage, su olarak g??ncellendi','UPDATE','2026-03-02 08:31:48'),(69,'2026-03-02 11:34:33','Admin User','	dior savage ??r??n a??ac??ndan 	dior savage, su bile??eni silindi','DELETE','2026-03-02 08:34:33'),(70,'2026-03-02 11:37:19','Admin User','chanel blu ??r??n a??ac??ndaki chanel blu, Esans bile??eni chanel blu, Esans olarak g??ncellendi','UPDATE','2026-03-02 08:37:19'),(71,'2026-03-02 11:37:45','Admin User','	dior savage ??r??n a??ac??ndaki 	dior savage, Esans bile??eni 	dior savage, Esans olarak g??ncellendi','UPDATE','2026-03-02 08:37:45'),(72,'2026-03-02 11:38:09','Admin User','	dior savage ??r??n a??ac??ndaki 	dior savage, paket bile??eni 	dior savage, paket olarak g??ncellendi','UPDATE','2026-03-02 08:38:09'),(73,'2026-03-02 11:38:20','Admin User','chanel blu ??r??n a??ac??ndaki chanel blu, paket bile??eni chanel blu, paket olarak g??ncellendi','UPDATE','2026-03-02 08:38:20'),(74,'2026-03-02 11:39:49','Admin User','chanel blu ??r??n a??ac??ndaki chanel blu, paket bile??eni chanel blu, paket olarak g??ncellendi','UPDATE','2026-03-02 08:39:49'),(75,'2026-03-02 11:40:00','Admin User','	dior savage ??r??n a??ac??ndaki 	dior savage, paket bile??eni 	dior savage, paket olarak g??ncellendi','UPDATE','2026-03-02 08:40:00'),(76,'2026-03-02 11:42:32','Admin User','ADEM  tedarik??isine PO-2026-00001 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-02 08:42:32'),(77,'2026-03-02 11:42:37','Admin User','G??KHAN  tedarik??isine PO-2026-00002 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-02 08:42:37'),(78,'2026-03-02 11:42:43','Admin User','RAMAZAN  tedarik??isine PO-2026-00003 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-02 08:42:43'),(79,'2026-03-02 11:42:48','Admin User','U??UR TAKIM tedarik??isine PO-2026-00004 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-02 08:42:48'),(80,'2026-03-02 11:42:53','Admin User','ESENG??L  tedarik??isine PO-2026-00005 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-02 08:42:53'),(81,'2026-03-02 11:43:02','Admin User','MAV?? ET??KET tedarik??isine PO-2026-00006 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-02 08:43:02'),(82,'2026-03-02 11:43:29','Admin User','??ENER tedarik??isine PO-2026-00007 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-02 08:43:29'),(83,'2026-03-02 11:46:03','Admin User','Sat??nalma sipari??i #7 durumu \'gonderildi\' olarak g??ncellendi','UPDATE','2026-03-02 08:46:03'),(84,'2026-03-02 11:46:44','Admin User','Sat??nalma sipari??i #6 durumu \'gonderildi\' olarak g??ncellendi','UPDATE','2026-03-02 08:46:44'),(85,'2026-03-02 11:47:25','Admin User','Sat??nalma sipari??i #5 durumu \'gonderildi\' olarak g??ncellendi','UPDATE','2026-03-02 08:47:25'),(86,'2026-03-02 11:47:41','Admin User','Sat??nalma sipari??i #4 durumu \'gonderildi\' olarak g??ncellendi','UPDATE','2026-03-02 08:47:41'),(87,'2026-03-02 11:47:57','Admin User','Sat??nalma sipari??i #3 durumu \'gonderildi\' olarak g??ncellendi','UPDATE','2026-03-02 08:47:57'),(88,'2026-03-02 11:48:08','Admin User','Sat??nalma sipari??i #2 durumu \'gonderildi\' olarak g??ncellendi','UPDATE','2026-03-02 08:48:08'),(89,'2026-03-02 11:48:15','Admin User','Sat??nalma sipari??i #1 durumu \'taslak\' olarak g??ncellendi','UPDATE','2026-03-02 08:48:15'),(90,'2026-03-02 11:48:20','Admin User','Sat??nalma sipari??i #1 durumu \'gonderildi\' olarak g??ncellendi','UPDATE','2026-03-02 08:48:20'),(91,'2026-03-02 11:49:05','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 08:49:05'),(92,'2026-03-02 12:28:32','Admin User','chanel blu, ham esans malzemesi chanel blu, ham esans olarak g??ncellendi','UPDATE','2026-03-02 09:28:32'),(93,'2026-03-02 12:29:26','Admin User','chanel blu, Esans esans?? i??in i?? emri olu??turuldu','CREATE','2026-03-02 09:29:26'),(94,'2026-03-02 12:29:41','Admin User','	dior savage, Esans esans?? i??in i?? emri olu??turuldu','CREATE','2026-03-02 09:29:41'),(95,'2026-03-02 12:34:03','Admin User','chanel blu ??r??n a??ac??ndaki chanel blu, Esans bile??eni chanel blu, Esans olarak g??ncellendi','UPDATE','2026-03-02 09:34:03'),(96,'2026-03-02 12:35:44','Admin User','	dior savage, Esans ??r??n a??ac??ndaki 	dior savage, ham esans bile??eni 	dior savage, ham esans olarak g??ncellendi','UPDATE','2026-03-02 09:35:44'),(97,'2026-03-02 12:36:24','Admin User','	dior savage, Esans ??r??n a??ac??ndaki 	dior savage, ham esans bile??eni 	dior savage, ham esans olarak g??ncellendi','UPDATE','2026-03-02 09:36:24'),(98,'2026-03-02 12:37:46','Admin User','	dior savage ??r??n?? i??in montaj i?? emri olu??turuldu','CREATE','2026-03-02 09:37:46'),(99,'2026-03-02 12:44:10','Admin User','RAMAZAN  tedarik??isine PO-2026-00008 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-02 09:44:10'),(100,'2026-03-02 12:44:33','Admin User','Sat??nalma sipari??i #8 durumu \'gonderildi\' olarak g??ncellendi','UPDATE','2026-03-02 09:44:33'),(101,'2026-03-02 12:49:58','Admin User','	dior savage, ham esans malzemesi 	dior savage, ham esans olarak g??ncellendi','UPDATE','2026-03-02 09:49:58'),(102,'2026-03-02 12:57:36','Admin User','chanel blu, Esans esans?? i??in i?? emri olu??turuldu','CREATE','2026-03-02 09:57:36'),(103,'2026-03-02 13:01:10','Admin User','chanel blu, Esans esans?? i??in i?? emri olu??turuldu','CREATE','2026-03-02 10:01:10'),(104,'2026-03-02 13:08:58','Admin User','	dior savage, Esans ??r??n a??ac??ndaki 	dior savage, ham esans bile??eni 	dior savage, ham esans olarak g??ncellendi','UPDATE','2026-03-02 10:08:58'),(105,'2026-03-02 13:09:22','Admin User','chanel blu, Esans ??r??n a??ac??ndaki chanel blu, ham esans bile??eni chanel blu, ham esans olarak g??ncellendi','UPDATE','2026-03-02 10:09:22'),(106,'2026-03-02 13:10:40','Admin User','chanel blu ??r??n a??ac??ndaki chanel blu, Esans bile??eni chanel blu, Esans olarak g??ncellendi','UPDATE','2026-03-02 10:10:40'),(107,'2026-03-02 13:13:42','Admin User','chanel blu ??r??n?? i??in montaj i?? emri olu??turuldu','CREATE','2026-03-02 10:13:42'),(108,'2026-03-02 13:13:43','Admin User','chanel blu ??r??n?? i??in montaj i?? emri olu??turuldu','CREATE','2026-03-02 10:13:43'),(109,'2026-03-02 13:13:44','Admin User','chanel blu ??r??n?? i??in montaj i?? emri olu??turuldu','CREATE','2026-03-02 10:13:44'),(110,'2026-03-02 13:15:39','Admin User','OSMAN  m????terisi sisteme eklendi','CREATE','2026-03-02 10:15:39'),(111,'2026-03-02 13:16:07','Admin User','HAMZA m????terisi sisteme eklendi','CREATE','2026-03-02 10:16:07'),(112,'2026-03-02 13:16:42','Admin User','OSMAN  m????terisi OSMAN  olarak g??ncellendi','UPDATE','2026-03-02 10:16:42'),(113,'2026-03-02 13:20:23','Admin User','	dior savage ??r??n?? 	dior savage olarak g??ncellendi','UPDATE','2026-03-02 10:20:23'),(114,'2026-03-02 13:21:24','Admin User','HAMZA m????terisi i??in yeni sipari?? olu??turuldu (ID: 1)','CREATE','2026-03-02 10:21:24'),(115,'2026-03-02 13:21:37','Admin User','HAMZA m????terisine ait 1 nolu sipari??in yeni durumu: Onayland??','UPDATE','2026-03-02 10:21:37'),(116,'2026-03-02 13:21:46','Admin User','HAMZA m????terisine ait 1 nolu sipari??in yeni durumu: Tamamland??','UPDATE','2026-03-02 10:21:46'),(117,'2026-03-02 13:22:22','Admin User','OSMAN  m????terisi i??in yeni sipari?? olu??turuldu (ID: 2)','CREATE','2026-03-02 10:22:22'),(118,'2026-03-02 13:22:30','Admin User','OSMAN  m????terisine ait 2 nolu sipari??in yeni durumu: Onayland??','UPDATE','2026-03-02 10:22:30'),(119,'2026-03-02 13:22:40','Admin User','OSMAN  m????terisine ait 2 nolu sipari??in yeni durumu: Tamamland??','UPDATE','2026-03-02 10:22:40'),(120,'2026-03-02 13:24:03','Admin User','Sipari?? ??demesi kategorisinde 4250 USD tutar??nda gelir eklendi','CREATE','2026-03-02 10:24:03'),(121,'2026-03-02 13:24:27','Admin User','Sipari?? ??demesi kategorisinde 3150 USD tutar??nda gelir eklendi','CREATE','2026-03-02 10:24:27'),(122,'2026-03-02 13:24:47','Admin User','Sipari?? ??demesi kategorisinde 2430 USD tutar??nda gelir eklendi','CREATE','2026-03-02 10:24:47'),(123,'2026-03-02 13:36:11','Admin User','personel oturumu kapatt?? (ID: 1)','????k???? Yap??ld??','2026-03-02 10:36:11'),(124,'2026-03-02 13:46:35','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 10:46:35'),(125,'2026-03-02 13:59:02','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 10:59:02'),(126,'2026-03-02 14:01:32','Admin User','Sipari?? ??demesi kategorisinde 2028 USD tutar??nda gelir eklendi','CREATE','2026-03-02 11:01:32'),(127,'2026-03-02 16:13:57','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 13:13:57'),(128,'2026-03-02 16:23:10','Admin User','	dior savage ??r??n?? i??in montaj i?? emri olu??turuldu','CREATE','2026-03-02 13:23:10'),(129,'2026-03-02 16:25:21','Admin User','OSMAN  m????terisi i??in yeni sipari?? olu??turuldu (ID: 3)','CREATE','2026-03-02 13:25:21'),(130,'2026-03-02 16:26:03','Admin User','OSMAN  m????terisine ait 3 nolu sipari??in yeni durumu: Onayland??','UPDATE','2026-03-02 13:26:03'),(131,'2026-03-02 16:30:25','Admin User','personel oturumu kapatt?? (ID: 1)','????k???? Yap??ld??','2026-03-02 13:30:25'),(132,'2026-03-02 17:10:19','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 14:10:19'),(133,'2026-03-02 17:11:04','Admin User','personel oturumu kapatt?? (ID: 1)','????k???? Yap??ld??','2026-03-02 14:11:04'),(134,'2026-03-02 21:06:04','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 18:06:04'),(135,'2026-03-02 21:09:22','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 18:09:22'),(136,'2026-03-02 21:28:23','Admin User','personel oturumu kapatt?? (ID: 1)','????k???? Yap??ld??','2026-03-02 18:28:23'),(137,'2026-03-02 21:28:36','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 18:28:36'),(138,'2026-03-02 22:02:13','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-02 19:02:13'),(139,'2026-03-02 22:04:26','Admin User','HAMZA m????terisi i??in yeni sipari?? olu??turuldu (ID: 4)','CREATE','2026-03-02 19:04:26'),(140,'2026-03-04 09:12:33','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-04 06:12:33'),(141,'2026-03-04 09:13:29','Admin User','OSMAN  m????terisine ait 3 nolu sipari??in yeni durumu: Tamamland??','UPDATE','2026-03-04 06:13:29'),(142,'2026-03-04 09:26:52','Admin User','HAMZA m????terisine ait 4 nolu sipari??in yeni durumu: Onayland??','UPDATE','2026-03-04 06:26:52'),(143,'2026-03-04 09:27:38','Admin User','HAMZA m????terisine ait 4 nolu sipari??in yeni durumu: Tamamland??','UPDATE','2026-03-04 06:27:38'),(144,'2026-03-04 09:32:33','Admin User','personel oturumu kapatt?? (ID: 1)','????k???? Yap??ld??','2026-03-04 06:32:33'),(145,'2026-03-07 00:02:38','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-06 21:02:38'),(146,'2026-03-08 22:44:08','Admin User','Personel giri?? yapt?? (E-posta/Telefon: admin@parfum.com)','Giri?? Yap??ld??','2026-03-08 19:44:08'),(147,'2026-03-08 22:47:55','Admin User','Sat??nalma sipari??i #8 durumu \'kapatildi\' olarak g??ncellendi','UPDATE','2026-03-08 19:47:55'),(148,'2026-03-08 22:48:20','Admin User','Sat??nalma sipari??i #7 durumu \'kapatildi\' olarak g??ncellendi','UPDATE','2026-03-08 19:48:20'),(149,'2026-03-08 22:48:34','Admin User','Sat??nalma sipari??i #6 durumu \'kapatildi\' olarak g??ncellendi','UPDATE','2026-03-08 19:48:34'),(150,'2026-03-08 22:48:51','Admin User','Sat??nalma sipari??i #1 durumu \'kapatildi\' olarak g??ncellendi','UPDATE','2026-03-08 19:48:51'),(151,'2026-03-08 22:48:57','Admin User','Sat??nalma sipari??i #5 durumu \'kapatildi\' olarak g??ncellendi','UPDATE','2026-03-08 19:48:57'),(152,'2026-03-08 22:49:03','Admin User','Sat??nalma sipari??i #2 durumu \'kapatildi\' olarak g??ncellendi','UPDATE','2026-03-08 19:49:03'),(153,'2026-03-08 22:49:15','Admin User','Sat??nalma sipari??i #3 durumu \'kapatildi\' olarak g??ncellendi','UPDATE','2026-03-08 19:49:15'),(154,'2026-03-08 22:49:28','Admin User','Sat??nalma sipari??i #4 durumu \'kapatildi\' olarak g??ncellendi','UPDATE','2026-03-08 19:49:28'),(155,'2026-03-08 22:50:17','Admin User','155 a ??r??n?? sisteme eklendi','CREATE','2026-03-08 19:50:17'),(156,'2026-03-08 22:50:18','Admin User','Otomatik esans olu??turuldu: 155 a, Esans (Tank: )','CREATE','2026-03-08 19:50:18'),(157,'2026-03-08 22:52:03','Admin User','ADEM  tedarik??isine 155 a, tak??m malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:52:03'),(158,'2026-03-08 22:52:36','Admin User','EBUBEK??R  tedarik??isine 155 a, kutu malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:52:36'),(159,'2026-03-08 22:53:11','Admin User','ESENG??L  tedarik??isine 155 a, kutu malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:53:11'),(160,'2026-03-08 22:53:41','Admin User','G??KHAN  tedarik??isine 155 a, jelatin malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:53:41'),(161,'2026-03-08 22:54:26','Admin User','KIRMIZIG??L tedarik??isine 155 a, tak??m malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:54:26'),(162,'2026-03-08 22:54:53','Admin User','LUZKIM tedarik??isine 155 a, ham esans malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:54:53'),(163,'2026-03-08 22:55:27','Admin User','MAV?? ET??KET tedarik??isine 155 a, etiket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:55:27'),(164,'2026-03-08 22:56:30','Admin User','RAMAZAN  tedarik??isine 155 a, paket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:56:30'),(165,'2026-03-08 22:56:59','Admin User','SARI ET??KET tedarik??isine 155 a, etiket malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:56:59'),(166,'2026-03-08 22:57:31','Admin User','SELUZ tedarik??isine 155 a, ham esans malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:57:31'),(167,'2026-03-08 22:58:07','Admin User','??ENER tedarik??isine 155 a, kutu malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:58:07'),(168,'2026-03-08 22:58:38','Admin User','TEVF??K BEY tedarik??isine 155 a, ham esans malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:58:38'),(169,'2026-03-08 22:59:14','Admin User','U??UR TAKIM tedarik??isine 155 a, tak??m malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 19:59:14'),(170,'2026-03-08 22:59:55','Admin User','155 a, Esans ??r??n a??ac??na 	dior savage, ham esans bile??eni eklendi','CREATE','2026-03-08 19:59:55'),(171,'2026-03-08 23:00:10','Admin User','155 a, Esans ??r??n a??ac??na ALKOL bile??eni eklendi','CREATE','2026-03-08 20:00:10'),(172,'2026-03-08 23:00:31','Admin User','155 a, Esans ??r??n a??ac??na SU bile??eni eklendi','CREATE','2026-03-08 20:00:31'),(173,'2026-03-08 23:03:46','Admin User','MERKES ??EBEKE tedarik??isine SU malzemesi i??in ??er??eve s??zle??me eklendi','CREATE','2026-03-08 20:03:46'),(174,'2026-03-08 23:06:00','Admin User','155 a, fiksator malzemesi sistemden silindi','DELETE','2026-03-08 20:06:00'),(175,'2026-03-08 23:07:06','Admin User','155 a, su malzemesi sistemden silindi','DELETE','2026-03-08 20:07:06'),(176,'2026-03-08 23:07:13','Admin User','	dior savage, su malzemesi sistemden silindi','DELETE','2026-03-08 20:07:13'),(177,'2026-03-08 23:07:17','Admin User','chanel blu, su malzemesi sistemden silindi','DELETE','2026-03-08 20:07:17'),(178,'2026-03-08 23:07:59','Admin User','G??KHAN  tedarik??isine PO-2026-00009 no\'lu sat??nalma sipari??i olu??turuldu','CREATE','2026-03-08 20:07:59'),(179,'2026-03-16 01:45:48','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-15 22:45:48'),(180,'2026-03-16 01:46:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-15 22:46:45'),(181,'2026-03-16 01:47:07','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-15 22:47:07');
/*!40000 ALTER TABLE `log_tablosu` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `lokasyonlar` DISABLE KEYS */;
INSERT INTO `lokasyonlar` VALUES (1,'depo esans','a'),(2,'depo a','a'),(3,'depo b','a'),(4,'depo c','depo c'),(5,'depo kutu','a'),(6,'depo tak??m','a');
/*!40000 ALTER TABLE `lokasyonlar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_fotograflari` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_fotograflari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_ihtiyaclari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_maliyetleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_maliyetleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `malzeme_siparisler` DISABLE KEYS */;
/*!40000 ALTER TABLE `malzeme_siparisler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `malzeme_turleri` DISABLE KEYS */;
INSERT INTO `malzeme_turleri` VALUES (3,'kutu','kutu',1,'2025-12-25 08:21:49'),(4,'etiket','etiket',2,'2025-12-25 08:22:01'),(5,'takm','tak??m',3,'2025-12-25 08:22:10'),(6,'ham_esans','ham esans',4,'2025-12-25 08:22:38'),(7,'alkol','alkol',5,'2025-12-25 08:22:52'),(8,'paket','paket',6,'2025-12-25 08:23:11'),(9,'jelatin','jelatin',7,'2025-12-25 08:23:23'),(10,'fiksator','fiksator',8,'2026-01-31 09:16:40'),(11,'su','su',9,'2026-01-31 09:21:03');
/*!40000 ALTER TABLE `malzeme_turleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (1,'chanel blu, etiket','etiket',NULL,-2000.00,'adet',1.75,'TL',0,0,'depo a','a',0),(2,'chanel blu, jelatin','jelatin',NULL,-2000.00,'adet',1.00,'TL',0,0,'depo a','a',0),(5,'chanel blu, kutu','kutu',NULL,-2000.00,'adet',0.40,'USD',0,0,'depo a','a',0),(6,'chanel blu, tak??m','takm',NULL,-2000.00,'adet',2.40,'USD',0,0,'depo a','a',0),(7,'chanel blu, ham esans','ham_esans','null',-135.00,'lt',120.00,'USD',0,0,'depo a','a',0),(8,'chanel blu, paket','paket',NULL,1200.00,'adet',16.00,'TL',0,0,'depo a','a',0),(9,'	dior savage, etiket','etiket',NULL,0.00,'adet',0.09,'USD',0,0,'depo b','a',0),(10,'	dior savage, kutu','kutu',NULL,0.00,'adet',0.45,'TL',0,0,'depo b','a',0),(12,'	dior savage, paket','paket',NULL,2700.00,'adet',15.00,'TL',0,0,'depo b','a',0),(14,'	dior savage, ham esans','ham_esans','null',0.00,'lt',85.00,'EUR',0,0,'depo b','a',0),(15,'	dior savage, tak??m','takm',NULL,0.00,'adet',2.10,'USD',0,0,'depo b','a',0),(16,'	dior savage, jelatin','jelatin',NULL,0.00,'adet',1.00,'TL',0,0,'depo b','a',0),(17,'SU','su','',896.00,'lt',1.00,'TRY',0,1,'depo esans','a',0),(18,'ALKOL','alkol','',-40.00,'lt',0.00,'TRY',0,1,'depo esans','a',0),(19,'155 a, etiket','etiket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(20,'155 a, jelatin','jelatin',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(23,'155 a, kutu','kutu',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(24,'155 a, tak??m','takm',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(25,'155 a, ham esans','ham_esans',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0),(26,'155 a, paket','paket',NULL,0.00,'adet',0.00,'TRY',0,0,'depo a','a',0);
/*!40000 ALTER TABLE `malzemeler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `montaj_is_emirleri` DISABLE KEYS */;
INSERT INTO `montaj_is_emirleri` VALUES (1,'2026-03-02','Admin User','2','	dior savage',500.00,'adet','2026-03-02','2026-03-02',' KIRIK VAR','tamamlandi',490.00,10.00,1,'2026-03-02','2026-03-02'),(2,'2026-03-02','Admin User','1','chanel blu',1000.00,'adet','2026-03-02','2026-03-02',' KUTU EKS??K','tamamlandi',999.00,1.00,2,'2026-03-02','2026-03-02'),(3,'2026-03-02','Admin User','1','chanel blu',1000.00,'adet','2026-03-02','2026-03-02',' ','tamamlandi',1000.00,0.00,2,'2026-03-02','2026-03-02'),(4,'2026-03-02','Admin User','1','chanel blu',1000.00,'adet','2026-03-02','2026-03-02',' KIRIK VAR','tamamlandi',991.00,9.00,2,'2026-03-02','2026-03-02'),(5,'2026-03-02','Admin User','2','	dior savage',500.00,'adet','2026-03-02','2026-03-02',' FFFF','tamamlandi',499.00,1.00,1,'2026-03-02','2026-03-02');
/*!40000 ALTER TABLE `montaj_is_emirleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `montaj_is_emri_malzeme_listesi` DISABLE KEYS */;
INSERT INTO `montaj_is_emri_malzeme_listesi` VALUES (1,'9','	dior savage, etiket','etiket',500.00,'adet'),(1,'10','	dior savage, kutu','kutu',500.00,'adet'),(1,'12','	dior savage, paket','paket',300.00,'adet'),(1,'15','	dior savage, tak??m','takm',500.00,'adet'),(1,'16','	dior savage, jelatin','jelatin',500.00,'adet'),(1,'ES-260302-669','	dior savage, Esans','esans',75.00,'adet'),(2,'1','chanel blu, etiket','etiket',1000.00,'adet'),(2,'2','chanel blu, jelatin','jelatin',1000.00,'adet'),(2,'5','chanel blu, kutu','kutu',1000.00,'adet'),(2,'6','chanel blu, tak??m','takm',1000.00,'adet'),(2,'8','chanel blu, paket','paket',600.00,'adet'),(2,'ES-260302-603','chanel blu, Esans','esans',150.00,'adet'),(3,'1','chanel blu, etiket','etiket',1000.00,'adet'),(3,'2','chanel blu, jelatin','jelatin',1000.00,'adet'),(3,'5','chanel blu, kutu','kutu',1000.00,'adet'),(3,'6','chanel blu, tak??m','takm',1000.00,'adet'),(3,'8','chanel blu, paket','paket',600.00,'adet'),(3,'ES-260302-603','chanel blu, Esans','esans',150.00,'adet'),(4,'1','chanel blu, etiket','etiket',1000.00,'adet'),(4,'2','chanel blu, jelatin','jelatin',1000.00,'adet'),(4,'5','chanel blu, kutu','kutu',1000.00,'adet'),(4,'6','chanel blu, tak??m','takm',1000.00,'adet'),(4,'8','chanel blu, paket','paket',600.00,'adet'),(4,'ES-260302-603','chanel blu, Esans','esans',150.00,'adet'),(5,'9','	dior savage, etiket','etiket',500.00,'adet'),(5,'10','	dior savage, kutu','kutu',500.00,'adet'),(5,'12','	dior savage, paket','paket',300.00,'adet'),(5,'15','	dior savage, tak??m','takm',500.00,'adet'),(5,'16','	dior savage, jelatin','jelatin',500.00,'adet'),(5,'ES-260302-669','	dior savage, Esans','esans',75.00,'adet');
/*!40000 ALTER TABLE `montaj_is_emri_malzeme_listesi` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `mrp_ayarlar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_ayarlar` ENABLE KEYS */;
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
  `net_ihtiya??` decimal(10,2) NOT NULL,
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

/*!40000 ALTER TABLE `mrp_planlama` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_planlama` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `mrp_raporlar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_raporlar` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `mrp_teslim_takvimi` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrp_teslim_takvimi` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `musteri_geri_bildirimleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `musteri_geri_bildirimleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
INSERT INTO `musteriler` VALUES (1,'OSMAN ','','','05325083018','','$2y$10$1Hl1rXN1pNLGjuBvMcaezOtGs9AEFMd2TUqZNHshAaT6xh2F6SMIG','',1,'',1),(2,'HAMZA','','','05321327675','','$2y$10$gYAIdsCoCXgCMSCngseBh.Chq9irKamqXa5qB6gmf7pqaBrYRCEmG','',1,'',1);
/*!40000 ALTER TABLE `musteriler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `net_esans_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `net_esans_ihtiyaclari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `net_urun_ihtiyaclari` DISABLE KEYS */;
/*!40000 ALTER TABLE `net_urun_ihtiyaclari` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `personel_avanslar` DISABLE KEYS */;
/*!40000 ALTER TABLE `personel_avanslar` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `personel_izinleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `personel_izinleri` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `personel_maas_odemeleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `personel_maas_odemeleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=302 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Y??netim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$z56pgRUputjO7M5.Pp0W1eHOgVJ16GX3OKYtPi4VGenFweT8xUidK',NULL,0,0.00);
/*!40000 ALTER TABLE `personeller` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `satinalma_siparis_kalemleri` VALUES (1,1,15,'dior savage, tak??m',1000.00,'adet',2.10,'USD',2100.00,1000.00,''),(2,2,2,'chanel blu, jelatin',1000.00,'adet',1.00,'TL',1000.00,1000.00,''),(3,2,16,'dior savage, jelatin',1000.00,'adet',1.00,'TL',1000.00,1000.00,''),(4,3,8,'chanel blu, paket',600.00,'adet',16.00,'TL',9600.00,600.00,''),(5,3,12,'dior savage, paket',600.00,'adet',15.00,'TL',9000.00,600.00,''),(6,4,6,'chanel blu, tak??m',1000.00,'adet',2.40,'USD',2400.00,1000.00,''),(7,5,10,'dior savage, kutu',1000.00,'adet',0.45,'TL',450.00,1000.00,''),(8,6,1,'chanel blu, etiket',1000.00,'adet',1.75,'TL',1750.00,1000.00,''),(9,6,9,'dior savage, etiket',1000.00,'adet',0.09,'USD',90.00,1000.00,''),(10,7,5,'chanel blu, kutu',1000.00,'adet',0.40,'USD',400.00,1000.00,''),(11,8,8,'chanel blu, paket',2400.00,'adet',16.00,'TL',38400.00,2400.00,''),(12,8,12,'dior savage, paket',2700.00,'adet',15.00,'TL',40500.00,2700.00,''),(13,9,20,'155 a, jelatin',1000.00,'adet',1.00,'TL',1000.00,0.00,''),(14,9,2,'chanel blu, jelatin',4172.00,'adet',1.00,'TL',4172.00,0.00,'');
/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `satinalma_siparisler` DISABLE KEYS */;
INSERT INTO `satinalma_siparisler` VALUES (1,'PO-2026-00001',10,'ADEM ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,2100.00,'USD','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-02 08:42:32','2026-03-08 19:48:51'),(2,'PO-2026-00002',12,'G??KHAN ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,2000.00,'TL','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-02 08:42:37','2026-03-08 19:49:03'),(3,'PO-2026-00003',13,'RAMAZAN ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,18600.00,'TL','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-02 08:42:43','2026-03-08 19:49:15'),(4,'PO-2026-00004',11,'U??UR TAKIM','2026-03-02',NULL,'kapatildi','bekliyor',NULL,2400.00,'USD','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-02 08:42:48','2026-03-08 19:49:28'),(5,'PO-2026-00005',6,'ESENG??L ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,450.00,'TL','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-02 08:42:53','2026-03-08 19:48:57'),(6,'PO-2026-00006',8,'MAV?? ET??KET','2026-03-02',NULL,'kapatildi','bekliyor',NULL,1840.00,'TL','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-02 08:43:02','2026-03-08 19:48:34'),(7,'PO-2026-00007',4,'??ENER','2026-03-02',NULL,'kapatildi','bekliyor',NULL,400.00,'USD','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-02 08:43:29','2026-03-08 19:48:20'),(8,'PO-2026-00008',13,'RAMAZAN ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,78900.00,'TL','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-02 09:44:10','2026-03-08 19:47:55'),(9,'PO-2026-00009',12,'G??KHAN ','2026-03-08',NULL,'taslak','bekliyor',NULL,5172.00,'TL','Kokpit ekran??ndan otomatik olu??turuldu',1,'Admin User','2026-03-08 20:07:59','2026-03-08 20:07:59');
/*!40000 ALTER TABLE `satinalma_siparisler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `siparis_kalemleri` VALUES (1,2,'	dior savage',290,'adet',9.00,2610.00,'USD'),(1,1,'chanel blu',321,'adet',8.00,2568.00,'USD'),(2,2,'	dior savage',520,'adet',9.00,4680.00,'USD'),(2,1,'chanel blu',250,'adet',8.00,2000.00,'USD'),(3,1,'chanel blu',1990,'adet',8.00,15920.00,'USD'),(4,1,'chanel blu',2401,'adet',8.00,19208.00,'USD');
/*!40000 ALTER TABLE `siparis_kalemleri` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
INSERT INTO `siparisler` VALUES (1,2,'HAMZA','2026-03-02 13:21:24','tamamlandi',611,'Personel: Admin User',1,'Admin User','2026-03-02 13:21:37','','odendi',NULL,5178.00,'USD'),(2,1,'OSMAN ','2026-03-02 13:22:22','tamamlandi',770,'Personel: Admin User',1,'Admin User','2026-03-02 13:22:30','','odendi',NULL,6680.00,'USD'),(3,1,'OSMAN ','2026-03-02 16:25:21','tamamlandi',1990,'Personel: Admin User',1,'Admin User','2026-03-02 16:26:03','','bekliyor',NULL,0.00,'USD'),(4,2,'HAMZA','2026-03-02 22:04:26','tamamlandi',2401,'Personel: Admin User',1,'Admin User','2026-03-04 09:26:52','','bekliyor',NULL,0.00,'USD');
/*!40000 ALTER TABLE `siparisler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `sirket_kasasi` DISABLE KEYS */;
INSERT INTO `sirket_kasasi` VALUES (1,'USD',8628.00,'2026-03-02 14:01:32'),(2,'TL',-510265.87,'2026-03-02 14:04:07');
/*!40000 ALTER TABLE `sirket_kasasi` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `sistem_kullanicilari` DISABLE KEYS */;
/*!40000 ALTER TABLE `sistem_kullanicilari` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
INSERT INTO `stok_hareket_kayitlari` VALUES (1,'2026-03-02 12:03:54','malzeme','9','	dior savage, etiket','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'ADAA [Sozlesme ID: 8] [Sipari??: PO-2026-00006]',1,'Admin User','MAV?? ET??KET',8),(2,'2026-03-02 12:04:27','malzeme','14','	dior savage, ham esans','adet',15.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 13]',1,'Admin User','TEVF??K BEY',1),(3,'2026-03-02 12:04:42','malzeme','16','	dior savage, jelatin','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 4] [Sipari??: PO-2026-00002]',1,'Admin User','G??KHAN ',12),(4,'2026-03-02 12:05:03','malzeme','12','	dior savage, paket','adet',600.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 9] [Sipari??: PO-2026-00003]',1,'Admin User','RAMAZAN ',13),(5,'2026-03-02 12:05:29','malzeme','15','	dior savage, tak??m','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'AA [Sozlesme ID: 1] [Sipari??: PO-2026-00001]',1,'Admin User','ADEM ',10),(6,'2026-03-02 12:06:00','malzeme','7','chanel blu, ham esans','adet',15.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 18]',1,'Admin User','SELUZ',3),(7,'2026-03-02 12:06:37','malzeme','1','chanel blu, etiket','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 21] [Sipari??: PO-2026-00006]',1,'Admin User','MAV?? ET??KET',8),(8,'2026-03-02 12:29:48','Bile??en','18','ALKOL','lt',80.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(9,'2026-03-02 12:29:48','Bile??en','17','SU','lt',8.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(10,'2026-03-02 12:29:48','Bile??en','14','	dior savage, ham esans','lt',15.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(11,'2026-03-02 12:29:55','Bile??en','17','SU','lt',8.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(12,'2026-03-02 12:29:55','Bile??en','18','ALKOL','lt',80.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(13,'2026-03-02 12:29:55','Bile??en','7','chanel blu, ham esans','lt',15.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(14,'2026-03-02 12:31:47','malzeme','10','	dior savage, kutu','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'EE [Sozlesme ID: 3] [Sipari??: PO-2026-00005]',1,'Admin User','ESENG??L ',6),(15,'2026-03-02 12:32:16','Esans','ES-260302-669','	dior savage, Esans','lt',100.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(16,'2026-03-02 12:32:42','Esans','ES-260302-603','chanel blu, Esans','lt',95.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(17,'2026-03-02 12:39:40','etiket','9','	dior savage, etiket','adet',500.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(18,'2026-03-02 12:39:40','kutu','10','	dior savage, kutu','adet',500.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(19,'2026-03-02 12:39:40','paket','12','	dior savage, paket','adet',300.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(20,'2026-03-02 12:39:40','takm','15','	dior savage, tak??m','adet',500.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(21,'2026-03-02 12:39:40','jelatin','16','	dior savage, jelatin','adet',500.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(22,'2026-03-02 12:39:40','esans','ES-260302-669','	dior savage, Esans','adet',75.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(23,'2026-03-02 12:45:11','malzeme','12','	dior savage, paket','adet',2700.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'12 [Sozlesme ID: 9] [Sipari??: PO-2026-00008]',1,'Admin User','RAMAZAN ',13),(24,'2026-03-02 12:45:39','malzeme','8','chanel blu, paket','adet',2400.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'33\r\n [Sozlesme ID: 20] [Sipari??: PO-2026-00008]',1,'Admin User','RAMAZAN ',13),(25,'2026-03-02 12:46:01','malzeme','6','chanel blu, tak??m','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'WW [Sozlesme ID: 15] [Sipari??: PO-2026-00004]',1,'Admin User','U??UR TAKIM',11),(26,'2026-03-02 12:46:15','malzeme','2','chanel blu, jelatin','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'WWW [Sozlesme ID: 22] [Sipari??: PO-2026-00002]',1,'Admin User','G??KHAN ',12),(27,'2026-03-02 12:53:02','??r??n','2','	dior savage','adet',490.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,1,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(28,'2026-03-02 12:54:21','malzeme','7','chanel blu, ham esans','lt',30.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'123\r\n [Sozlesme ID: 18]',1,'Admin User','SELUZ',3),(29,'2026-03-02 12:57:44','Bile??en','17','SU','lt',8.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(30,'2026-03-02 12:57:44','Bile??en','18','ALKOL','lt',80.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(31,'2026-03-02 12:57:44','Bile??en','7','chanel blu, ham esans','lt',15.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(32,'2026-03-02 12:58:00','Esans','ES-260302-603','chanel blu, Esans','lt',100.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(33,'2026-03-02 13:01:28','Bile??en','17','SU','lt',80.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(34,'2026-03-02 13:01:28','Bile??en','18','ALKOL','lt',800.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(35,'2026-03-02 13:01:28','Bile??en','7','chanel blu, ham esans','lt',150.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(36,'2026-03-02 13:01:51','Esans','ES-260302-603','chanel blu, Esans','lt',1000.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(37,'2026-03-02 13:12:00','malzeme','5','chanel blu, kutu','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 17] [Sipari??: PO-2026-00007]',1,'Admin User','??ENER',4),(38,'2026-03-02 13:12:20','malzeme','8','chanel blu, paket','adet',600.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'123\r\n [Sozlesme ID: 20] [Sipari??: PO-2026-00003]',1,'Admin User','RAMAZAN ',13),(39,'2026-03-02 13:13:58','etiket','1','chanel blu, etiket','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(40,'2026-03-02 13:13:58','jelatin','2','chanel blu, jelatin','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(41,'2026-03-02 13:13:58','kutu','5','chanel blu, kutu','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(42,'2026-03-02 13:13:58','takm','6','chanel blu, tak??m','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(43,'2026-03-02 13:13:58','paket','8','chanel blu, paket','adet',600.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(44,'2026-03-02 13:13:58','esans','ES-260302-603','chanel blu, Esans','adet',150.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(45,'2026-03-02 13:14:06','etiket','1','chanel blu, etiket','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(46,'2026-03-02 13:14:06','jelatin','2','chanel blu, jelatin','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(47,'2026-03-02 13:14:06','kutu','5','chanel blu, kutu','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(48,'2026-03-02 13:14:06','takm','6','chanel blu, tak??m','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(49,'2026-03-02 13:14:06','paket','8','chanel blu, paket','adet',600.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(50,'2026-03-02 13:14:06','esans','ES-260302-603','chanel blu, Esans','adet',150.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(51,'2026-03-02 13:14:12','etiket','1','chanel blu, etiket','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(52,'2026-03-02 13:14:12','jelatin','2','chanel blu, jelatin','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(53,'2026-03-02 13:14:12','kutu','5','chanel blu, kutu','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(54,'2026-03-02 13:14:12','takm','6','chanel blu, tak??m','adet',1000.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(55,'2026-03-02 13:14:12','paket','8','chanel blu, paket','adet',600.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(56,'2026-03-02 13:14:12','esans','ES-260302-603','chanel blu, Esans','adet',150.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(57,'2026-03-02 13:14:41','??r??n','1','chanel blu','adet',999.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,2,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(58,'2026-03-02 13:14:53','??r??n','1','chanel blu','adet',1000.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,3,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(59,'2026-03-02 13:15:19','??r??n','1','chanel blu','adet',991.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,4,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(60,'2026-03-02 13:21:47','urun','2','	dior savage','adet',290.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,2,'HAMZA','M????teri sipari??i',1,'Admin User',NULL,NULL),(61,'2026-03-02 13:21:47','urun','1','chanel blu','adet',321.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,2,'HAMZA','M????teri sipari??i',1,'Admin User',NULL,NULL),(62,'2026-03-02 13:22:41','urun','2','	dior savage','adet',520.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,1,'OSMAN ','M????teri sipari??i',1,'Admin User',NULL,NULL),(63,'2026-03-02 13:22:41','urun','1','chanel blu','adet',250.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,1,'OSMAN ','M????teri sipari??i',1,'Admin User',NULL,NULL),(64,'2026-03-02 16:23:40','etiket','9','	dior savage, etiket','adet',500.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,5,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(65,'2026-03-02 16:23:40','kutu','10','	dior savage, kutu','adet',500.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,5,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(66,'2026-03-02 16:23:40','paket','12','	dior savage, paket','adet',300.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,5,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(67,'2026-03-02 16:23:40','takm','15','	dior savage, tak??m','adet',500.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,5,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(68,'2026-03-02 16:23:40','jelatin','16','	dior savage, jelatin','adet',500.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,5,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(69,'2026-03-02 16:23:40','esans','ES-260302-669','	dior savage, Esans','adet',75.00,'????k????','??retime ????k????',NULL,NULL,NULL,NULL,5,NULL,NULL,'???? emri ba??lat??ld??',1,'Admin User',NULL,NULL),(70,'2026-03-02 16:24:16','??r??n','2','	dior savage','adet',499.00,'Giri??','??retimden Giri??',NULL,NULL,NULL,NULL,5,NULL,NULL,'???? emri tamamlama',1,'Admin User',NULL,NULL),(71,'2026-03-04 09:13:30','urun','1','chanel blu','adet',1990.00,'cikis','cikis',NULL,NULL,NULL,'3',NULL,1,'OSMAN ','M????teri sipari??i',1,'Admin User',NULL,NULL),(72,'2026-03-04 09:27:39','urun','1','chanel blu','adet',2401.00,'cikis','cikis',NULL,NULL,NULL,'4',NULL,2,'HAMZA','M????teri sipari??i',1,'Admin User',NULL,NULL);
/*!40000 ALTER TABLE `stok_hareket_kayitlari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `stok_hareketleri_sozlesmeler` DISABLE KEYS */;
INSERT INTO `stok_hareketleri_sozlesmeler` VALUES (1,8,9,1000.00,'2026-03-02 09:03:54',0.09,'USD','MAV?? ET??KET',8,'2026-03-02','2026-04-02'),(2,13,14,15.00,'2026-03-02 09:04:27',85.00,'EUR','TEVF??K BEY',1,'2026-03-02','2026-04-02'),(3,4,16,1000.00,'2026-03-02 09:04:42',1.00,'TL','G??KHAN ',12,'2026-03-02','2026-04-02'),(4,9,12,600.00,'2026-03-02 09:05:03',15.00,'TL','RAMAZAN ',13,'2026-03-02','2026-04-02'),(5,1,15,1000.00,'2026-03-02 09:05:29',2.10,'USD','ADEM ',10,'2026-03-02','2026-04-02'),(6,18,7,15.00,'2026-03-02 09:06:00',120.00,'USD','SELUZ',3,'2026-03-02','2026-04-02'),(7,21,1,1000.00,'2026-03-02 09:06:37',1.75,'TL','MAV?? ET??KET',8,'2026-03-02','2026-04-02'),(14,3,10,1000.00,'2026-03-02 09:31:47',0.45,'TL','ESENG??L ',6,'2026-03-02','2026-04-02'),(23,9,12,2700.00,'2026-03-02 09:45:11',15.00,'TL','RAMAZAN ',13,'2026-03-02','2026-04-02'),(24,20,8,2400.00,'2026-03-02 09:45:39',16.00,'TL','RAMAZAN ',13,'2026-03-02','2026-04-02'),(25,15,6,1000.00,'2026-03-02 09:46:01',2.40,'USD','U??UR TAKIM',11,'2026-03-02','2026-04-02'),(26,22,2,1000.00,'2026-03-02 09:46:15',1.00,'TL','G??KHAN ',12,'2026-03-02','2026-04-02'),(28,18,7,30.00,'2026-03-02 09:54:21',120.00,'USD','SELUZ',3,'2026-03-02','2026-04-02'),(37,17,5,1000.00,'2026-03-02 10:12:00',0.40,'USD','??ENER',4,'2026-03-02','2026-04-02'),(38,20,8,600.00,'2026-03-02 10:12:20',16.00,'TL','RAMAZAN ',13,'2026-03-02','2026-04-02');
/*!40000 ALTER TABLE `stok_hareketleri_sozlesmeler` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `taksit_detaylari` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_detaylari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `taksit_planlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_planlari` ENABLE KEYS */;
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

/*!40000 ALTER TABLE `taksit_siparis_baglantisi` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksit_siparis_baglantisi` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tanklar` DISABLE KEYS */;
INSERT INTO `tanklar` VALUES (1,'w 500','dior savage','',250.00),(2,'e 101','chanel blu','',250.00);
/*!40000 ALTER TABLE `tanklar` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (1,'TEVF??K BEY','ESANS ','','','','','','',''),(2,'LUZKIM','ESANS','','','','','','',''),(3,'SELUZ','ESANS','','','','','','',''),(4,'??ENER','KUTU','','','','','','',''),(5,'EBUBEK??R ','KUTU','','','','','','',''),(6,'ESENG??L ','KUTU','','','','','','',''),(7,'SARI ET??KET','ET??KET','','','','','','',''),(8,'MAV?? ET??KET','ET??KET','','','','','','',''),(9,'KIRMIZIG??L','TAKIM','','','','','','',''),(10,'ADEM ','TAKIM','','','','','','',''),(11,'U??UR TAKIM','TAKIM','','','','','','',''),(12,'G??KHAN ','JELAT??N','','','','','','',''),(13,'RAMAZAN ','PAKET','','','','','','',''),(14,'KIRMIZIG??L ALKOL','ALKOL','','','','','','',''),(15,'MERKES ??EBEKE','SU','','','','','','','');
/*!40000 ALTER TABLE `tedarikciler` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tekrarli_odemeler` DISABLE KEYS */;
/*!40000 ALTER TABLE `tekrarli_odemeler` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (1,1,'chanel blu','etiket','1','chanel blu, etiket',1.00,'urun'),(2,1,'chanel blu','jelatin','2','chanel blu, jelatin',1.00,'urun'),(5,1,'chanel blu','kutu','5','chanel blu, kutu',1.00,'urun'),(6,1,'chanel blu','takm','6','chanel blu, tak??m',1.00,'urun'),(7,1,'chanel blu','paket','8','chanel blu, paket',0.60,'urun'),(8,1,'chanel blu','esans','ES-260302-603','chanel blu, Esans',0.15,'urun'),(9,2,'	dior savage','etiket','9','	dior savage, etiket',1.00,'urun'),(10,2,'	dior savage','kutu','10','	dior savage, kutu',1.00,'urun'),(12,2,'	dior savage','paket','12','	dior savage, paket',0.60,'urun'),(14,2,'	dior savage','takm','15','	dior savage, tak??m',1.00,'urun'),(15,2,'	dior savage','jelatin','16','	dior savage, jelatin',1.00,'urun'),(16,2,'	dior savage','esans','ES-260302-669','	dior savage, Esans',0.15,'urun'),(17,2,'	dior savage, Esans','malzeme','18','ALKOL',0.80,'esans'),(18,2,'	dior savage, Esans','malzeme','17','SU',0.08,'esans'),(19,2,'	dior savage, Esans','malzeme','14','	dior savage, ham esans',0.15,'esans'),(20,1,'chanel blu, Esans','malzeme','17','SU',0.08,'esans'),(21,1,'chanel blu, Esans','malzeme','18','ALKOL',0.80,'esans'),(22,1,'chanel blu, Esans','malzeme','7','chanel blu, ham esans',0.15,'esans'),(23,3,'155 a','etiket','19','155 a, etiket',1.00,'urun'),(24,3,'155 a','jelatin','20','155 a, jelatin',1.00,'urun'),(27,3,'155 a','kutu','23','155 a, kutu',1.00,'urun'),(28,3,'155 a','takm','24','155 a, tak??m',1.00,'urun'),(29,3,'155 a','paket','26','155 a, paket',1.00,'urun'),(30,3,'155 a','esans','ES-260308-259','155 a, Esans',1.00,'urun'),(31,3,'155 a, Esans','malzeme','14','	dior savage, ham esans',0.15,'esans'),(32,3,'155 a, Esans','malzeme','18','ALKOL',0.80,'esans'),(33,3,'155 a, Esans','malzeme','17','SU',0.05,'esans');
/*!40000 ALTER TABLE `urun_agaci` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `urun_fotograflari` DISABLE KEYS */;
/*!40000 ALTER TABLE `urun_fotograflari` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (1,'chanel blu','',-1972,'adet',8.00,'USD',0.00,'TRY',200,'depo a','a','uretilen',NULL),(2,'	dior savage','',379,'adet',9.00,'USD',0.00,'TRY',0,'depo b','a','uretilen',NULL),(3,'155 a','',250,'adet',9.00,'USD',0.00,'TRY',0,'depo a','a','uretilen',NULL);
/*!40000 ALTER TABLE `urunler` ENABLE KEYS */;
DROP TABLE IF EXISTS `v_esans_maliyetleri`;
/*!50001 DROP VIEW IF EXISTS `v_esans_maliyetleri`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_esans_maliyetleri` AS SELECT
 1 AS `esans_kodu`,
  1 AS `toplam_maliyet` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_urun_maliyetleri`;
/*!50001 DROP VIEW IF EXISTS `v_urun_maliyetleri`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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


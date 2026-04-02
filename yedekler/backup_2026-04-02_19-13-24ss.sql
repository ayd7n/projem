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
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','44.4950'),(2,'euro_kuru','51.2800'),(3,'son_otomatik_yedek_tarihi','2026-04-02 00:05:39'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170\n6356317802');
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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cerceve_sozlesmeler` DISABLE KEYS */;
INSERT INTO `cerceve_sozlesmeler` VALUES (1,5,'GÖKHAN',12,'DİOR SAVAGE ELİXER, jelatin',2.00,'TL',99999,0,'2026-03-31','2026-05-30','Admin User','2026-03-31 11:14:19','',1,'TL',NULL),(2,4,'KIRMIZIGÜL',11,'DİOR SAVAGE ELİXER, takım',2.40,'USD',999999,0,'2026-03-31','2026-04-30','Admin User','2026-03-31 11:14:46','',1,'TL',NULL),(3,6,'RAMAZAN ',10,'DİOR SAVAGE ELİXER, paket',12.00,'TL',999999,0,'2026-03-31','2026-05-30','Admin User','2026-03-31 11:15:23','',1,'TL',NULL),(4,1,'TEVFİK',13,'DİOR SAVAGE ELİXER HAM ESANS',60.00,'USD',999999,0,'2026-03-31','2026-04-30','Admin User','2026-03-31 11:37:56','',1,'TL',NULL),(5,3,'SARI ATİKET',8,'DİOR SAVAGE ELİXER, etiket',2.00,'TL',99999,0,'2026-03-31','2026-04-30','Admin User','2026-03-31 12:19:23','',1,'TL',NULL),(6,2,'ŞENER ',9,'DİOR SAVAGE ELİXER, kutu',0.40,'USD',999999,0,'2026-03-31','2026-04-30','Admin User','2026-03-31 12:20:11','',1,'TL',NULL),(7,8,'ZÜLFÜKAR KIRMIZIGÜL',15,'ALKOL',1.60,'USD',9999999,0,'2026-03-31','2026-04-30','Admin User','2026-03-31 12:22:19','',1,'TL',NULL),(8,15,'ADEM TAKIM',5,'DİOR SAVAGE, takım',2.20,'EUR',9999999,0,'2026-03-31','2026-05-30','Admin User','2026-03-31 16:15:07','',1,'TL',NULL),(9,13,'MAVİ ETİKET ',1,'DİOR SAVAGE, etiket',3.00,'TL',9999999,0,'2026-03-31','2026-05-30','Admin User','2026-03-31 16:15:43','',1,'TL',NULL),(10,15,'ADEM TAKIM',11,'DİOR SAVAGE ELİXER, takım',2.60,'USD',9999999,0,'2026-03-31','2026-05-30','Admin User','2026-03-31 16:16:57','',1,'TL',NULL),(11,4,'KIRMIZIGÜL',5,'DİOR SAVAGE, takım',2.30,'USD',9999999,0,'2026-03-30','2026-05-30','Admin User','2026-03-31 16:17:47','',1,'TL',NULL),(12,10,'BEKİR ',9,'DİOR SAVAGE ELİXER, kutu',0.45,'USD',9999999,0,'2026-03-31','2026-05-30','Admin User','2026-03-31 16:18:34','',1,'TL',NULL),(13,11,'LUZİ ',6,'DİOR SAVAGE, ham esans',150.00,'USD',999999,0,'2026-03-31','2026-05-30','Admin User','2026-03-31 18:23:46','',1,'TL',NULL),(14,16,'esengül ',2,'DİOR SAVAGE, kutu',0.50,'USD',9999999,2500,'2026-04-01','2026-05-01','Admin User','2026-04-01 11:18:46','',1,'TL',NULL),(15,11,'LUZİ ',6,'DİOR SAVAGE, ham esans',160.00,'USD',99999999,40,'2026-04-01','2026-06-01','Admin User','2026-04-01 11:19:15','',1,'TL',NULL),(16,6,'RAMAZAN ',3,'DİOR SAVAGE, paket',20.00,'TL',9999999,2500,'2026-04-01','2026-06-01','Admin User','2026-04-01 11:20:18','',1,'TL',NULL),(17,5,'GÖKHAN',7,'DİOR SAVAGE, jelatin',4.00,'TL',9999999,2500,'2026-04-01','2026-06-01','Admin User','2026-04-01 11:21:03','',1,'TL',NULL),(18,1,'TEVFİK',20,'g armani , ham esans',80.00,'EUR',99999999,32,'2026-04-01','2026-06-26','Admin User','2026-04-01 14:58:55','',1,'TL',NULL),(20,15,'ADEM TAKIM',18,'g armani , takım',1.90,'USD',99999999,0,'2026-04-01','2026-06-18','Admin User','2026-04-01 15:06:13','',1,'TL',NULL),(21,16,'esengül ',19,'g armani , kutu',0.55,'USD',9999999,0,'2026-04-01','2026-06-01','Admin User','2026-04-01 15:06:48','',1,'TL',NULL),(22,5,'GÖKHAN',17,'g armani , jelatin',5.00,'TL',9999999,0,'2026-04-01','2026-06-01','Admin User','2026-04-01 15:07:16','',1,'TL',NULL),(23,11,'LUZİ ',20,'g armani , ham esans',140.00,'USD',999999999,0,'2026-04-01','2026-06-11','Admin User','2026-04-01 15:07:57','',1,'TL',NULL),(24,13,'MAVİ ETİKET ',16,'g armani , etiket',3.30,'TL',999999,0,'2026-04-01','2026-06-09','Admin User','2026-04-01 15:08:34','',1,'TL',NULL),(25,2,'ŞENER ',19,'g armani , kutu',0.40,'USD',999999,0,'2026-04-01','2026-07-23','Admin User','2026-04-01 15:09:34','',1,'TL',NULL),(26,6,'RAMAZAN ',21,'g armani , paket',18.00,'TL',9999999,0,'2026-04-01','2026-06-18','Admin User','2026-04-01 15:10:28','',1,'TL',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `esans_is_emirleri` DISABLE KEYS */;
INSERT INTO `esans_is_emirleri` VALUES (1,'2026-03-31','Admin User','ES-260331-914','DİOR SAVAGE ELİXER, Esans','002','DİOR SAVAGE ELİXSER',100.00,'lt','2026-03-31',1,'2026-04-01','2026-03-31','2026-03-31',' 123','tamamlandi',100.00,0.00),(2,'2026-03-31','Admin User','ES-260331-914','DİOR SAVAGE ELİXER, Esans','002','DİOR SAVAGE ELİXSER',230.00,'lt','2026-03-31',1,'2026-04-01','2026-03-31','2026-03-31',' ','tamamlandi',230.00,0.00),(3,'2026-03-31','Admin User','ES-260331-936','DİOR SAVAGE, Esans','01','DİOR SAVAGE ',100.00,'lt','2026-03-31',1,'2026-04-01','2026-04-01','2026-04-01',' döküldü','tamamlandi',99.00,1.00),(4,'2026-04-01','Admin User','ES-260331-936','DİOR SAVAGE, Esans','01','DİOR SAVAGE ',100.00,'lt','2026-04-01',1,'2026-04-02','2026-04-01','2026-04-01',' ','tamamlandi',100.00,0.00),(5,'2026-04-01','Admin User','ES-260331-936','DİOR SAVAGE, Esans','01','DİOR SAVAGE ',66.00,'lt','2026-04-01',1,'2026-04-02','2026-04-01','2026-04-01',' vbnmöç','tamamlandi',65.00,1.00),(7,'2026-04-01','Admin User','ES-260401-762','g armani , Esans','003','g armani',210.00,'lt','2026-04-01',1,'2026-04-02','2026-04-01','2026-04-01',' şşş','tamamlandi',208.00,2.00),(8,'2026-04-02','Admin User','ES-260331-936','DİOR SAVAGE, Esans','01','DİOR SAVAGE ',100.00,'lt','2026-04-02',1,'2026-04-03','2026-04-02','2026-04-02',' ','tamamlandi',100.00,0.00);
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
INSERT INTO `esans_is_emri_malzeme_listesi` VALUES (1,15,'ALKOL','malzeme',77.00,'lt'),(1,14,'SU','malzeme',8.00,'lt'),(1,13,'DİOR SAVAGE ELİXER HAM ESANS','malzeme',15.00,'lt'),(2,15,'ALKOL','malzeme',177.10,'lt'),(2,14,'SU','malzeme',18.40,'lt'),(2,13,'DİOR SAVAGE ELİXER HAM ESANS','malzeme',34.50,'lt'),(3,6,'DİOR SAVAGE, ham esans','malzeme',15.00,'lt'),(3,14,'SU','malzeme',8.00,'lt'),(3,15,'ALKOL','malzeme',77.00,'lt'),(4,6,'DİOR SAVAGE, ham esans','malzeme',15.00,'lt'),(4,14,'SU','malzeme',8.00,'lt'),(4,15,'ALKOL','malzeme',77.00,'lt'),(5,6,'DİOR SAVAGE, ham esans','malzeme',9.90,'lt'),(5,14,'SU','malzeme',5.28,'lt'),(5,15,'ALKOL','malzeme',50.82,'lt'),(7,15,'ALKOL','malzeme',161.70,'lt'),(7,14,'SU','malzeme',10.50,'lt'),(7,20,'g armani , ham esans','malzeme',31.50,'lt'),(8,6,'DİOR SAVAGE, ham esans','malzeme',15.00,'lt'),(8,14,'SU','malzeme',8.00,'lt'),(8,15,'ALKOL','malzeme',77.00,'lt');
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
INSERT INTO `esanslar` VALUES (2,'ES-260331-936','DİOR SAVAGE, Esans','',2.50,'lt',1.00,'01','DİOR SAVAGE '),(3,'ES-260331-914','DİOR SAVAGE ELİXER, Esans','',231.00,'lt',1.00,'002','DİOR SAVAGE ELİXSER'),(4,'ES-260401-762','g armani , Esans','',58.00,'lt',1.00,'003','g armani');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `gelir_yonetimi` DISABLE KEYS */;
INSERT INTO `gelir_yonetimi` VALUES (1,'2026-04-01 00:00:00',3100.00,'USD','Sipariş Ödemesi','Sipariş No: #1 tahsilatı',1,'Admin User',1,'Nakit',3,'EMMOĞLU',NULL,'EUR',NULL),(2,'2026-04-01 00:00:00',2100.00,'USD','Sipariş Ödemesi','Sipariş No: #2 tahsilatı',1,'Admin User',2,'Nakit',2,'İDO',NULL,'TL',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `gider_yonetimi` DISABLE KEYS */;
INSERT INTO `gider_yonetimi` VALUES (1,'2026-04-01',129297.92,'Malzeme Gideri','g armani , ham esans için 32 adet ara ödeme (2.560,00 EUR @ 50,5070)',1,'Admin User',NULL,'Havale/EFT','TEVFİK','TL',NULL),(2,'2026-04-01',10000.00,'Malzeme Gideri','DİOR SAVAGE, jelatin için 2500 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','GÖKHAN','TL',NULL),(3,'2026-04-01',50000.00,'Malzeme Gideri','DİOR SAVAGE, paket için 2500 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','RAMAZAN ','TL',NULL),(4,'2026-04-01',274240.00,'Malzeme Gideri','DİOR SAVAGE, ham esans için 40 adet ara ödeme (6.400,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','LUZİ ','TL',NULL),(5,'2026-04-01',53562.50,'Malzeme Gideri','DİOR SAVAGE, kutu için 2500 adet ara ödeme (1.250,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','esengül ','TL',NULL),(6,'2026-04-02',70000.00,'Personel Avansı','Hamza Güzen - 2026/4 dönemi avans ödemesi. vbnmö',1,'Admin User',NULL,'Nakit','Hamza Güzen','TL',NULL),(7,'2026-04-02',45000.00,'Personel Avansı','mehmet mutlu - 2026/4 dönemi avans ödemesi. vbnmö',1,'Admin User',NULL,'Nakit','mehmet mutlu','TL',NULL),(8,'2026-04-02',-10000.00,'Personel Gideri','Hamza Güzen - 2026/4 dönemi maaş ödemesi. ',1,'Admin User',NULL,'Havale','Hamza Güzen','TL',NULL),(9,'2026-04-02',5000.00,'Personel Gideri','mehmet mutlu - 2026/4 dönemi maaş ödemesi. ',1,'Admin User',NULL,'Havale','mehmet mutlu','TL',NULL),(10,'2026-04-02',25000.00,'Personel Avansı','mehmet mutlu - 2026/4 dönemi avans ödemesi. şçşçşç',1,'Admin User',NULL,'Nakit','mehmet mutlu','TL',NULL),(11,'2026-04-02',25000.00,'Kira','depo a kirası - 2026/1 dönemi. ',1,'Admin User',NULL,'Havale','sdfghjklş','TL',NULL),(12,'2026-04-02',12000.00,'Kira','depo b kirası - 2026/1 dönemi. ',1,'Admin User',NULL,'Havale','dfghjkl','TL',NULL),(13,'2026-04-02',12000.00,'Kira','depo takım  - 2026/1 dönemi. ',1,'Admin User',NULL,'Havale','sdfghj','TL',NULL),(14,'2026-04-02',25000.00,'Kira','depo a kirası - 2026/4 dönemi. ',1,'Admin User',NULL,'Havale','sdfghjklş','TL',NULL),(15,'2026-04-02',12000.00,'Kira','depo b kirası - 2026/4 dönemi. ',1,'Admin User',NULL,'Havale','dfghjkl','TL',NULL),(16,'2026-04-02',15000.00,'Kira','depo c - 2026/4 dönemi. ',1,'Admin User',NULL,'Havale','sdfghjk','TL',NULL),(17,'2026-04-02',65000.00,'Kira','depo esans - 2026/4 dönemi. ',1,'Admin User',NULL,'Havale','asdfghj','TL',NULL),(18,'2026-04-02',12000.00,'Kira','depo takım  - 2026/4 dönemi. ',1,'Admin User',NULL,'Havale','sdfghj','TL',NULL);
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
INSERT INTO `is_merkezleri` VALUES (1,'AHMET ERSİN',''),(2,'A BEKİR ÖNEL','');
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `kasa_hareketleri` DISABLE KEYS */;
INSERT INTO `kasa_hareketleri` VALUES (1,'2026-04-01 00:00:00','gider_cikisi','TL',NULL,129297.92,'TL',NULL,129297.92,'cerceve_sozlesmeler',18,'g armani , ham esans için 32 adet ara ödeme (2.560,00 EUR @ 50,5070)','Admin User','TEVFİK',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(2,'2026-04-01 00:00:00','gider_cikisi','TL',NULL,10000.00,'TL',NULL,10000.00,'cerceve_sozlesmeler',17,'DİOR SAVAGE, jelatin için 2500 adet ara ödeme','Admin User','GÖKHAN',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(3,'2026-04-01 00:00:00','gider_cikisi','TL',NULL,50000.00,'TL',NULL,50000.00,'cerceve_sozlesmeler',16,'DİOR SAVAGE, paket için 2500 adet ara ödeme','Admin User','RAMAZAN ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(4,'2026-04-01 00:00:00','gider_cikisi','TL',NULL,274240.00,'TL',NULL,274240.00,'cerceve_sozlesmeler',15,'DİOR SAVAGE, ham esans için 40 adet ara ödeme (6.400,00 USD @ 42,8500)','Admin User','LUZİ ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(5,'2026-04-01 00:00:00','gider_cikisi','TL',NULL,53562.50,'TL',NULL,53562.50,'cerceve_sozlesmeler',14,'DİOR SAVAGE, kutu için 2500 adet ara ödeme (1.250,00 USD @ 42,8500)','Admin User','esengül ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(6,'2026-04-01 00:00:00','gelir_girisi','EUR',NULL,3100.00,'USD',NULL,132835.00,'gelir_yonetimi',1,'Sipariş No: #1 tahsilatı','Admin User',NULL,'EMMOĞLU',NULL,'Nakit',NULL,NULL,NULL),(7,'2026-04-01 00:00:00','gelir_girisi','TL',NULL,2100.00,'USD',NULL,89985.00,'gelir_yonetimi',2,'Sipariş No: #2 tahsilatı','Admin User',NULL,'İDO',NULL,'Nakit',NULL,NULL,NULL),(8,'2026-04-02 00:00:00','personel_avansi','TL',NULL,70000.00,'TL',NULL,70000.00,'personel_avanslar',0,'Hamza Güzen - 2026/4 dönemi avans ödemesi. vbnmö','Admin User','Hamza Güzen',NULL,NULL,'Nakit',NULL,NULL,NULL),(9,'2026-04-02 00:00:00','personel_avansi','TL',NULL,45000.00,'TL',NULL,45000.00,'personel_avanslar',0,'mehmet mutlu - 2026/4 dönemi avans ödemesi. vbnmö','Admin User','mehmet mutlu',NULL,NULL,'Nakit',NULL,NULL,NULL),(10,'2026-04-02 00:00:00','personel_odemesi','TL',NULL,-10000.00,'TL',NULL,-10000.00,'personel_maas_odemeleri',0,'Hamza Güzen - 2026/4 dönemi maaş ödemesi. ','Admin User','Hamza Güzen',NULL,NULL,'Havale',NULL,NULL,NULL),(11,'2026-04-02 00:00:00','personel_odemesi','TL',NULL,5000.00,'TL',NULL,5000.00,'personel_maas_odemeleri',0,'mehmet mutlu - 2026/4 dönemi maaş ödemesi. ','Admin User','mehmet mutlu',NULL,NULL,'Havale',NULL,NULL,NULL),(12,'2026-04-02 00:00:00','personel_avansi','TL',NULL,25000.00,'TL',NULL,25000.00,'personel_avanslar',0,'mehmet mutlu - 2026/4 dönemi avans ödemesi. şçşçşç','Admin User','mehmet mutlu',NULL,NULL,'Nakit',NULL,NULL,NULL),(13,'2026-04-02 00:00:00','gider_cikisi','TL',NULL,25000.00,'TL',NULL,25000.00,'tekrarli_odeme_gecmisi',1,'depo a kirası - 2026/1 dönemi. ','Admin User','sdfghjklş',NULL,NULL,'Havale',NULL,NULL,NULL),(14,'2026-04-02 00:00:00','gider_cikisi','TL',NULL,12000.00,'TL',NULL,12000.00,'tekrarli_odeme_gecmisi',2,'depo b kirası - 2026/1 dönemi. ','Admin User','dfghjkl',NULL,NULL,'Havale',NULL,NULL,NULL),(15,'2026-04-02 00:00:00','gider_cikisi','TL',NULL,12000.00,'TL',NULL,12000.00,'tekrarli_odeme_gecmisi',3,'depo takım  - 2026/1 dönemi. ','Admin User','sdfghj',NULL,NULL,'Havale',NULL,NULL,NULL),(16,'2026-04-02 00:00:00','gider_cikisi','TL',NULL,25000.00,'TL',NULL,25000.00,'tekrarli_odeme_gecmisi',1,'depo a kirası - 2026/4 dönemi. ','Admin User','sdfghjklş',NULL,NULL,'Havale',NULL,NULL,NULL),(17,'2026-04-02 00:00:00','gider_cikisi','TL',NULL,12000.00,'TL',NULL,12000.00,'tekrarli_odeme_gecmisi',2,'depo b kirası - 2026/4 dönemi. ','Admin User','dfghjkl',NULL,NULL,'Havale',NULL,NULL,NULL),(18,'2026-04-02 00:00:00','gider_cikisi','TL',NULL,15000.00,'TL',NULL,15000.00,'tekrarli_odeme_gecmisi',5,'depo c - 2026/4 dönemi. ','Admin User','sdfghjk',NULL,NULL,'Havale',NULL,NULL,NULL),(19,'2026-04-02 00:00:00','gider_cikisi','TL',NULL,65000.00,'TL',NULL,65000.00,'tekrarli_odeme_gecmisi',4,'depo esans - 2026/4 dönemi. ','Admin User','asdfghj',NULL,NULL,'Havale',NULL,NULL,NULL),(20,'2026-04-02 00:00:00','gider_cikisi','TL',NULL,12000.00,'TL',NULL,12000.00,'tekrarli_odeme_gecmisi',3,'depo takım  - 2026/4 dönemi. ','Admin User','sdfghj',NULL,NULL,'Havale',NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=294 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `log_tablosu` DISABLE KEYS */;
INSERT INTO `log_tablosu` VALUES (1,'2026-03-31 10:00:07','Admin User','log_tablosu, lokasyonlar, malzemeler, musteriler, tanklar tabloları temizlendi','DELETE','2026-03-31 07:00:07'),(2,'2026-03-31 10:02:03','Admin User','DİOR SAVAGE  adlı tank sisteme eklendi','CREATE','2026-03-31 07:02:03'),(3,'2026-03-31 10:02:32','Admin User','DİOR SAVAGE ELİXSER adlı tank sisteme eklendi','CREATE','2026-03-31 07:02:32'),(4,'2026-03-31 10:03:10','Admin User','ESANS  deposuna A rafı eklendi','CREATE','2026-03-31 07:03:10'),(5,'2026-03-31 10:03:21','Admin User','A DEPEO deposuna A rafı eklendi','CREATE','2026-03-31 07:03:21'),(6,'2026-03-31 10:03:30','Admin User','B DEPO deposuna A rafı eklendi','CREATE','2026-03-31 07:03:30'),(7,'2026-03-31 10:03:44','Admin User','MALZEME DEPOSU deposuna A rafı eklendi','CREATE','2026-03-31 07:03:44'),(8,'2026-03-31 10:03:54','Admin User','TAKIM DEPOSU  deposuna A rafı eklendi','CREATE','2026-03-31 07:03:54'),(9,'2026-03-31 10:04:22','Admin User','AHMET ERSİN iş merkezi eklendi','CREATE','2026-03-31 07:04:22'),(10,'2026-03-31 10:04:31','Admin User','A BEKİR ÖNEL iş merkezi eklendi','CREATE','2026-03-31 07:04:31'),(11,'2026-03-31 10:05:36','Admin User','DİOR SAVAGE ürünü sisteme eklendi','CREATE','2026-03-31 07:05:36'),(12,'2026-03-31 10:05:37','Admin User','Otomatik esans oluşturuldu: DİOR SAVAGE, Esans (Tank: 01)','CREATE','2026-03-31 07:05:37'),(13,'2026-03-31 10:07:57','Admin User','DİOR SAVAGE ELİXER ürünü sisteme eklendi','CREATE','2026-03-31 07:07:57'),(14,'2026-03-31 10:07:58','Admin User','Otomatik esans oluşturuldu: DİOR SAVAGE ELİXER, Esans (Tank: 002)','CREATE','2026-03-31 07:07:58'),(15,'2026-03-31 10:08:20','Admin User','DİOR SAVAGE urun agacindan DİOR SAVAGE, fiksator bileseni silindi','DELETE','2026-03-31 07:08:20'),(16,'2026-03-31 10:11:36','Admin User','DİOR SAVAGE, fiksator malzemesi sistemden silindi','DELETE','2026-03-31 07:11:36'),(17,'2026-03-31 10:13:12','Admin User','DİOR SAVAGE ELİXER malzemesi sisteme eklendi','CREATE','2026-03-31 07:13:12'),(18,'2026-03-31 10:16:08','Admin User','dior savage, Esans esansı sistemden silindi','DELETE','2026-03-31 07:16:08'),(19,'2026-03-31 10:31:26','Admin User','SU malzemesi sisteme eklendi','CREATE','2026-03-31 07:31:26'),(20,'2026-03-31 11:06:50','Admin User','DİOR SAVAGE, Esans urun agacina DİOR SAVAGE, ham esans bileseni eklendi','CREATE','2026-03-31 08:06:50'),(21,'2026-03-31 11:07:11','Admin User','DİOR SAVAGE, Esans urun agacina SU bileseni eklendi','CREATE','2026-03-31 08:07:11'),(22,'2026-03-31 11:07:53','Admin User','ALKOL malzemesi sisteme eklendi','CREATE','2026-03-31 08:07:53'),(23,'2026-03-31 11:08:33','Admin User','DİOR SAVAGE, Esans urun agacina ALKOL bileseni eklendi','CREATE','2026-03-31 08:08:33'),(24,'2026-03-31 11:10:34','Admin User','DİOR SAVAGE ELİXER malzemesi DİOR SAVAGE ELİXER HAM ESANS olarak güncellendi','UPDATE','2026-03-31 08:10:34'),(25,'2026-03-31 11:11:17','Admin User','DİOR SAVAGE ELİXER urun agacina DİOR SAVAGE ELİXER, Esans bileseni eklendi','CREATE','2026-03-31 08:11:17'),(26,'2026-03-31 11:11:40','Admin User','DİOR SAVAGE ELİXER, Esans urun agacina ALKOL bileseni eklendi','CREATE','2026-03-31 08:11:40'),(27,'2026-03-31 11:12:01','Admin User','DİOR SAVAGE ELİXER, Esans urun agacina SU bileseni eklendi','CREATE','2026-03-31 08:12:01'),(28,'2026-03-31 11:12:27','Admin User','DİOR SAVAGE ELİXER, Esans urun agacina DİOR SAVAGE ELİXER HAM ESANS bileseni eklendi','CREATE','2026-03-31 08:12:27'),(29,'2026-03-31 11:14:19','Admin User','GÖKHAN tedarikçisine DİOR SAVAGE ELİXER, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 08:14:19'),(30,'2026-03-31 11:14:46','Admin User','KIRMIZIGÜL tedarikçisine DİOR SAVAGE ELİXER, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 08:14:46'),(31,'2026-03-31 11:15:23','Admin User','RAMAZAN  tedarikçisine DİOR SAVAGE ELİXER, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 08:15:23'),(32,'2026-03-31 11:16:31','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER, paket bileseni DİOR SAVAGE ELİXER, paket olarak guncellendi','UPDATE','2026-03-31 08:16:31'),(33,'2026-03-31 11:18:10','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER, paket bileseni DİOR SAVAGE ELİXER, paket olarak guncellendi','UPDATE','2026-03-31 08:18:10'),(34,'2026-03-31 11:37:56','Admin User','TEVFİK tedarikçisine DİOR SAVAGE ELİXER HAM ESANS malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 08:37:56'),(35,'2026-03-31 12:17:43','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-31 09:17:43'),(36,'2026-03-31 12:19:23','Admin User','SARI ATİKET tedarikçisine DİOR SAVAGE ELİXER, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 09:19:23'),(37,'2026-03-31 12:20:11','Admin User','ŞENER  tedarikçisine DİOR SAVAGE ELİXER, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 09:20:11'),(38,'2026-03-31 12:21:15','Admin User','HAMZA  tedarikçisi sisteme eklendi','CREATE','2026-03-31 09:21:15'),(39,'2026-03-31 12:21:38','Admin User','ZÜLFÜKAR KIRMIZIGÜL tedarikçisi sisteme eklendi','CREATE','2026-03-31 09:21:38'),(40,'2026-03-31 12:22:19','Admin User','ZÜLFÜKAR KIRMIZIGÜL tedarikçisine ALKOL malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 09:22:19'),(41,'2026-03-31 12:26:32','Admin User','ŞENER  tedarikçisine PO-2026-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-31 09:26:32'),(42,'2026-03-31 12:26:39','Admin User','SARI ATİKET tedarikçisine PO-2026-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-31 09:26:39'),(43,'2026-03-31 12:26:44','Admin User','KIRMIZIGÜL tedarikçisine PO-2026-00003 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-31 09:26:44'),(44,'2026-03-31 12:26:50','Admin User','GÖKHAN tedarikçisine PO-2026-00004 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-31 09:26:50'),(45,'2026-03-31 12:26:55','Admin User','RAMAZAN  tedarikçisine PO-2026-00005 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-31 09:26:55'),(46,'2026-03-31 12:27:56','Admin User','Satınalma siparişi #5 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-31 09:27:56'),(47,'2026-03-31 12:28:01','Admin User','Satınalma siparişi #5 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-31 09:28:01'),(48,'2026-03-31 12:28:14','Admin User','Satınalma siparişi #4 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-31 09:28:14'),(49,'2026-03-31 12:28:30','Admin User','Satınalma siparişi #3 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-31 09:28:30'),(50,'2026-03-31 12:28:45','Admin User','Satınalma siparişi #2 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-31 09:28:45'),(51,'2026-03-31 12:28:57','Admin User','Satınalma siparişi #1 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-31 09:28:57'),(52,'2026-03-31 12:33:36','Admin User','DİOR SAVAGE ELİXER, Esans esansı için iş emri oluşturuldu','CREATE','2026-03-31 09:33:36'),(53,'2026-03-31 12:35:43','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER, Esans bileseni DİOR SAVAGE ELİXER, Esans olarak guncellendi','UPDATE','2026-03-31 09:35:43'),(54,'2026-03-31 12:36:04','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER, Esans bileseni DİOR SAVAGE ELİXER, Esans olarak guncellendi','UPDATE','2026-03-31 09:36:04'),(55,'2026-03-31 12:36:21','Admin User','DİOR SAVAGE ELİXER urun agacindan DİOR SAVAGE ELİXER, Esans bileseni silindi','DELETE','2026-03-31 09:36:21'),(56,'2026-03-31 12:38:51','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER, paket bileseni DİOR SAVAGE ELİXER, paket olarak guncellendi','UPDATE','2026-03-31 09:38:51'),(57,'2026-03-31 12:40:42','Admin User','DİOR SAVAGE ELİXER HAM ESANS malzemesi DİOR SAVAGE ELİXER HAM ESANS olarak güncellendi','UPDATE','2026-03-31 09:40:42'),(58,'2026-03-31 14:00:13','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-31 11:00:13'),(59,'2026-03-31 14:03:45','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER, Esans bileseni DİOR SAVAGE ELİXER, Esans olarak guncellendi','UPDATE','2026-03-31 11:03:45'),(60,'2026-03-31 14:08:27','Admin User','DİOR SAVAGE ELİXER ürünü için montaj iş emri oluşturuldu','CREATE','2026-03-31 11:08:27'),(61,'2026-03-31 14:08:40','Admin User','DİOR SAVAGE ELİXER ürünü için montaj iş emri oluşturuldu','CREATE','2026-03-31 11:08:40'),(62,'2026-03-31 14:12:29','Admin User','DİOR SAVAGE ELİXER, Esans esansı için iş emri oluşturuldu','CREATE','2026-03-31 11:12:29'),(63,'2026-03-31 14:41:14','Admin User','DİOR SAVAGE ELİXER ürünü DİOR SAVAGE ELİXER olarak güncellendi','UPDATE','2026-03-31 11:41:14'),(64,'2026-03-31 14:42:54','Admin User','BEKİR  tedarikçisi sisteme eklendi','CREATE','2026-03-31 11:42:54'),(65,'2026-03-31 14:43:06','Admin User','LUZİ  tedarikçisi sisteme eklendi','CREATE','2026-03-31 11:43:06'),(66,'2026-03-31 14:43:22','Admin User','SELÜZ ESANS tedarikçisi sisteme eklendi','CREATE','2026-03-31 11:43:22'),(67,'2026-03-31 14:43:46','Admin User','MAVİ ETİKET  tedarikçisi sisteme eklendi','CREATE','2026-03-31 11:43:46'),(68,'2026-03-31 14:44:00','Admin User','VEYSİ SİNANLI  tedarikçisi sisteme eklendi','CREATE','2026-03-31 11:44:00'),(69,'2026-03-31 14:44:12','Admin User','ADEM TAKIM tedarikçisi sisteme eklendi','CREATE','2026-03-31 11:44:12'),(70,'2026-03-31 14:45:25','Admin User','FATİH  müşterisi sisteme eklendi','CREATE','2026-03-31 11:45:25'),(71,'2026-03-31 14:45:32','Admin User','İDO müşterisi sisteme eklendi','CREATE','2026-03-31 11:45:32'),(72,'2026-03-31 14:45:49','Admin User','EMMOĞLU müşterisi sisteme eklendi','CREATE','2026-03-31 11:45:49'),(73,'2026-03-31 14:46:43','Admin User','EMMOĞLU müşterisi EMMOĞLU olarak güncellendi','UPDATE','2026-03-31 11:46:43'),(74,'2026-03-31 14:46:48','Admin User','EMMOĞLU müşterisi EMMOĞLU olarak güncellendi','UPDATE','2026-03-31 11:46:48'),(75,'2026-03-31 14:46:59','Admin User','FATİH  müşterisi FATİH  olarak güncellendi','UPDATE','2026-03-31 11:46:59'),(76,'2026-03-31 14:47:05','Admin User','İDO müşterisi İDO olarak güncellendi','UPDATE','2026-03-31 11:47:05'),(77,'2026-03-31 14:47:38','Admin User','EMMOĞLU müşterisi için yeni sipariş oluşturuldu (ID: 1)','CREATE','2026-03-31 11:47:38'),(78,'2026-03-31 14:48:28','Admin User','EMMOĞLU musterisine ait 1 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-03-31 11:48:28'),(79,'2026-03-31 14:48:46','Admin User','EMMOĞLU musterisine ait 1 nolu siparisin yeni durumu: Tamamlandı','UPDATE','2026-03-31 11:48:46'),(80,'2026-03-31 14:55:16','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-31 11:55:16'),(81,'2026-03-31 16:01:42','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-31 13:01:42'),(82,'2026-03-31 16:06:36','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-31 13:06:36'),(83,'2026-03-31 16:06:37','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-31 13:06:37'),(84,'2026-03-31 16:07:09','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-31 13:07:09'),(85,'2026-03-31 16:12:45','Admin User','mehmet can  müşterisi sisteme eklendi','CREATE','2026-03-31 13:12:45'),(86,'2026-03-31 16:13:01','Admin User','ahmet pak müşterisi sisteme eklendi','CREATE','2026-03-31 13:13:01'),(87,'2026-03-31 16:13:11','Admin User','ahmet pak müşterisi ahmet pak olarak güncellendi','UPDATE','2026-03-31 13:13:11'),(88,'2026-03-31 16:13:23','Admin User','mehmet can  müşterisi mehmet can  olarak güncellendi','UPDATE','2026-03-31 13:13:23'),(89,'2026-03-31 16:13:42','Admin User','ahmet canlı müşterisi sisteme eklendi','CREATE','2026-03-31 13:13:42'),(90,'2026-03-31 16:15:07','Admin User','ADEM TAKIM tedarikçisine DİOR SAVAGE, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 13:15:07'),(91,'2026-03-31 16:15:43','Admin User','MAVİ ETİKET  tedarikçisine DİOR SAVAGE, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 13:15:43'),(92,'2026-03-31 16:16:57','Admin User','ADEM TAKIM tedarikçisine DİOR SAVAGE ELİXER, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 13:16:57'),(93,'2026-03-31 16:17:47','Admin User','KIRMIZIGÜL tedarikçisine DİOR SAVAGE, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 13:17:47'),(94,'2026-03-31 16:18:34','Admin User','BEKİR  tedarikçisine DİOR SAVAGE ELİXER, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 13:18:34'),(95,'2026-03-31 16:19:03','Admin User','esengül  tedarikçisi sisteme eklendi','CREATE','2026-03-31 13:19:03'),(96,'2026-03-31 17:41:37','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-31 14:41:37'),(97,'2026-03-31 18:21:28','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER, paket bileseni DİOR SAVAGE ELİXER HAM ESANS olarak guncellendi','UPDATE','2026-03-31 15:21:28'),(98,'2026-03-31 18:23:46','Admin User','LUZİ  tedarikçisine DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-31 15:23:46'),(99,'2026-03-31 18:24:31','Admin User','DİOR SAVAGE, Esans esansı için iş emri oluşturuldu','CREATE','2026-03-31 15:24:31'),(100,'2026-03-31 18:26:36','Admin User','DİOR SAVAGE, Esans urun agacindaki DİOR SAVAGE, ham esans bileseni DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-03-31 15:26:36'),(101,'2026-03-31 18:34:56','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-31 15:34:56'),(102,'2026-04-01 11:05:32','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 08:05:32'),(103,'2026-04-01 11:06:38','Admin User','DİOR SAVAGE, Esans urun agacindaki DİOR SAVAGE, ham esans bileseni DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-04-01 08:06:38'),(104,'2026-04-01 11:06:41','Admin User','DİOR SAVAGE, Esans urun agacindaki DİOR SAVAGE, ham esans bileseni DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-04-01 08:06:41'),(105,'2026-04-01 11:06:42','Admin User','DİOR SAVAGE, Esans urun agacindaki DİOR SAVAGE, ham esans bileseni DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-04-01 08:06:42'),(106,'2026-04-01 11:06:43','Admin User','DİOR SAVAGE, Esans urun agacindaki DİOR SAVAGE, ham esans bileseni DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-04-01 08:06:43'),(107,'2026-04-01 11:06:44','Admin User','DİOR SAVAGE, Esans urun agacindaki DİOR SAVAGE, ham esans bileseni DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-04-01 08:06:44'),(108,'2026-04-01 11:06:45','Admin User','DİOR SAVAGE, Esans urun agacindaki DİOR SAVAGE, ham esans bileseni DİOR SAVAGE, ham esans olarak guncellendi','UPDATE','2026-04-01 08:06:45'),(109,'2026-04-01 11:13:15','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-04-01 08:13:15'),(110,'2026-04-01 11:14:43','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 08:14:43'),(111,'2026-04-01 11:18:46','Admin User','esengül  tedarikçisine DİOR SAVAGE, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 08:18:46'),(112,'2026-04-01 11:19:15','Admin User','LUZİ  tedarikçisine DİOR SAVAGE, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 08:19:15'),(113,'2026-04-01 11:20:18','Admin User','RAMAZAN  tedarikçisine DİOR SAVAGE, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 08:20:18'),(114,'2026-04-01 11:21:03','Admin User','GÖKHAN tedarikçisine DİOR SAVAGE, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 08:21:03'),(115,'2026-04-01 11:21:56','Admin User','ADEM TAKIM tedarikçisine PO-2026-00006 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 08:21:56'),(116,'2026-04-01 11:22:02','Admin User','GÖKHAN tedarikçisine PO-2026-00007 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 08:22:02'),(117,'2026-04-01 11:22:06','Admin User','MAVİ ETİKET  tedarikçisine PO-2026-00008 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 08:22:06'),(118,'2026-04-01 11:22:11','Admin User','RAMAZAN  tedarikçisine PO-2026-00009 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 08:22:11'),(119,'2026-04-01 11:22:16','Admin User','TEVFİK tedarikçisine PO-2026-00010 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 08:22:16'),(120,'2026-04-01 11:23:05','Admin User','esengül  tedarikçisine PO-2026-00011 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 08:23:05'),(121,'2026-04-01 11:23:34','Admin User','Satınalma siparişi #11 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 08:23:34'),(122,'2026-04-01 11:23:46','Admin User','Satınalma siparişi #10 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 08:23:46'),(123,'2026-04-01 11:23:58','Admin User','Satınalma siparişi #9 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 08:23:58'),(124,'2026-04-01 11:24:11','Admin User','Satınalma siparişi #7 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 08:24:11'),(125,'2026-04-01 11:24:27','Admin User','Satınalma siparişi #8 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 08:24:27'),(126,'2026-04-01 11:24:29','Admin User','Satınalma siparişi #8 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 08:24:29'),(127,'2026-04-01 11:24:39','Admin User','Satınalma siparişi #5 durumu \'tamamlandi\' olarak güncellendi','UPDATE','2026-04-01 08:24:39'),(128,'2026-04-01 11:24:52','Admin User','Satınalma siparişi #6 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 08:24:52'),(129,'2026-04-01 11:28:36','Admin User','LUZİ  tedarikçisine PO-2026-00012 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 08:28:36'),(130,'2026-04-01 11:29:36','Admin User','DİOR SAVAGE ELİXER HAM ESANS malzemesi DİOR SAVAGE ELİXER esans olarak güncellendi','UPDATE','2026-04-01 08:29:36'),(131,'2026-04-01 11:33:24','Admin User','DİOR SAVAGE, Esans esansı için iş emri oluşturuldu','CREATE','2026-04-01 08:33:24'),(132,'2026-04-01 11:35:56','Admin User','DİOR SAVAGE, Esans esansı için iş emri oluşturuldu','CREATE','2026-04-01 08:35:56'),(133,'2026-04-01 11:44:39','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-04-01 08:44:39'),(134,'2026-04-01 11:44:45','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 08:44:45'),(135,'2026-04-01 11:49:39','Admin User','DİOR SAVAGE urun agacindaki DİOR SAVAGE, Esans bileseni DİOR SAVAGE, Esans olarak guncellendi','UPDATE','2026-04-01 08:49:39'),(136,'2026-04-01 11:50:28','Admin User','DİOR SAVAGE ürünü için montaj iş emri oluşturuldu','CREATE','2026-04-01 08:50:28'),(137,'2026-04-01 11:50:52','Admin User','DİOR SAVAGE ürünü için montaj iş emri oluşturuldu','CREATE','2026-04-01 08:50:52'),(138,'2026-04-01 11:53:13','Admin User','DİOR SAVAGE ELİXER, paket malzemesi DİOR SAVAGE ELİXER, paket olarak güncellendi','UPDATE','2026-04-01 08:53:13'),(139,'2026-04-01 11:54:59','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-04-01 08:54:59'),(140,'2026-04-01 14:13:41','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 11:13:41'),(141,'2026-04-01 14:18:04','Admin User','DİOR SAVAGE, Esans esansı için iş emri oluşturuldu','CREATE','2026-04-01 11:18:04'),(142,'2026-04-01 14:35:22','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 11:35:22'),(143,'2026-04-01 14:37:37','Admin User','g armani adlı tank sisteme eklendi','CREATE','2026-04-01 11:37:37'),(144,'2026-04-01 14:39:15','Admin User','g armani  ürünü sisteme eklendi','CREATE','2026-04-01 11:39:15'),(145,'2026-04-01 14:39:16','Admin User','Otomatik esans oluşturuldu: g armani , Esans (Tank: 003)','CREATE','2026-04-01 11:39:16'),(146,'2026-04-01 14:48:14','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-04-01 11:48:14'),(147,'2026-04-01 14:56:11','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 11:56:11'),(148,'2026-04-01 14:56:44','Admin User','g armani , Esans urun agacina ALKOL bileseni eklendi','CREATE','2026-04-01 11:56:44'),(149,'2026-04-01 14:57:05','Admin User','g armani , Esans urun agacina SU bileseni eklendi','CREATE','2026-04-01 11:57:05'),(150,'2026-04-01 14:57:28','Admin User','g armani , Esans urun agacina g armani , ham esans bileseni eklendi','CREATE','2026-04-01 11:57:28'),(151,'2026-04-01 14:58:55','Admin User','TEVFİK tedarikçisine g armani , ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 11:58:55'),(152,'2026-04-01 15:01:48','Admin User','g armani  urun agacindaki g armani , Esans bileseni g armani , Esans olarak guncellendi','UPDATE','2026-04-01 12:01:48'),(153,'2026-04-01 15:02:40','Admin User','g armani , Esans urun agacindaki g armani , ham esans bileseni g armani , ham esans olarak guncellendi','UPDATE','2026-04-01 12:02:40'),(154,'2026-04-01 15:03:27','Admin User','g armani , Esans esansı için iş emri oluşturuldu','CREATE','2026-04-01 12:03:27'),(155,'2026-04-01 15:05:17','Admin User','ADEM TAKIM tedarikçisine g armani , etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 12:05:17'),(156,'2026-04-01 15:05:45','Admin User','ADEM TAKIM tedarikçisine g armani , etiket malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-04-01 12:05:45'),(157,'2026-04-01 15:06:13','Admin User','ADEM TAKIM tedarikçisine g armani , takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 12:06:13'),(158,'2026-04-01 15:06:48','Admin User','esengül  tedarikçisine g armani , kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 12:06:48'),(159,'2026-04-01 15:07:16','Admin User','GÖKHAN tedarikçisine g armani , jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 12:07:16'),(160,'2026-04-01 15:07:57','Admin User','LUZİ  tedarikçisine g armani , ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 12:07:57'),(161,'2026-04-01 15:08:34','Admin User','MAVİ ETİKET  tedarikçisine g armani , etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 12:08:34'),(162,'2026-04-01 15:09:34','Admin User','ŞENER  tedarikçisine g armani , kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 12:09:34'),(163,'2026-04-01 15:10:28','Admin User','RAMAZAN  tedarikçisine g armani , paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-04-01 12:10:28'),(164,'2026-04-01 15:11:39','Admin User','ADEM TAKIM tedarikçisine PO-2026-00013 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:11:39'),(165,'2026-04-01 15:11:45','Admin User','MAVİ ETİKET  tedarikçisine PO-2026-00014 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:11:45'),(166,'2026-04-01 15:11:51','Admin User','GÖKHAN tedarikçisine PO-2026-00015 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:11:51'),(167,'2026-04-01 15:11:56','Admin User','RAMAZAN  tedarikçisine PO-2026-00016 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:11:56'),(168,'2026-04-01 15:12:01','Admin User','ŞENER  tedarikçisine PO-2026-00017 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:12:01'),(169,'2026-04-01 15:12:19','Admin User','ADEM TAKIM tedarikçisine PO-2026-00018 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:12:19'),(170,'2026-04-01 15:12:23','Admin User','MAVİ ETİKET  tedarikçisine PO-2026-00019 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:12:23'),(171,'2026-04-01 15:12:27','Admin User','GÖKHAN tedarikçisine PO-2026-00020 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:12:27'),(172,'2026-04-01 15:12:32','Admin User','RAMAZAN  tedarikçisine PO-2026-00021 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:12:32'),(173,'2026-04-01 15:12:35','Admin User','ŞENER  tedarikçisine PO-2026-00022 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-01 12:12:35'),(174,'2026-04-01 15:12:58','Admin User','Satınalma siparişi #22 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:12:58'),(175,'2026-04-01 15:13:10','Admin User','Satınalma siparişi #21 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:13:10'),(176,'2026-04-01 15:13:22','Admin User','Satınalma siparişi #20 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:13:22'),(177,'2026-04-01 15:13:31','Admin User','Satınalma siparişi #18 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:13:31'),(178,'2026-04-01 15:13:39','Admin User','Satınalma siparişi #19 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:13:39'),(179,'2026-04-01 15:14:54','Admin User','PO-2026-00017 no\'lu ŞENER  siparişi silindi','DELETE','2026-04-01 12:14:54'),(180,'2026-04-01 15:15:35','Admin User','RAMAZAN  tedarikçisine g armani , paket malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-04-01 12:15:35'),(181,'2026-04-01 15:16:03','Admin User','Satınalma siparişi #16 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:16:03'),(182,'2026-04-01 15:16:14','Admin User','Satınalma siparişi #15 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:16:14'),(183,'2026-04-01 15:16:57','Admin User','Satınalma siparişi #14 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:16:57'),(184,'2026-04-01 15:17:04','Admin User','Satınalma siparişi #13 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-01 12:17:04'),(185,'2026-04-01 15:18:58','Admin User','ADEM TAKIM tedarikçisine ait g armani , etiket malzemesi için çerçeve sözleşme ve ilişkili stok hareketleri silindi','DELETE','2026-04-01 12:18:58'),(186,'2026-04-01 15:46:58','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 12:46:58'),(187,'2026-04-01 15:50:59','Admin User','g armani  urun agacindaki g armani , Esans bileseni g armani , Esans olarak guncellendi','UPDATE','2026-04-01 12:50:59'),(188,'2026-04-01 15:52:08','Admin User','g armani  urun agacindaki g armani , Esans bileseni g armani , ham esans olarak guncellendi','UPDATE','2026-04-01 12:52:08'),(189,'2026-04-01 15:52:33','Admin User','g armani  urun agacindaki g armani , ham esans bileseni g armani , ham esans olarak guncellendi','UPDATE','2026-04-01 12:52:33'),(190,'2026-04-01 15:55:38','Admin User','g armani  urun agacindan g armani , ham esans bileseni silindi','DELETE','2026-04-01 12:55:38'),(191,'2026-04-01 15:56:02','Admin User','g armani  ürünü için montaj iş emri oluşturuldu','CREATE','2026-04-01 12:56:02'),(192,'2026-04-01 16:01:32','Admin User','DİOR SAVAGE ELİXER urun agacina DİOR SAVAGE ELİXER, paket bileseni eklendi','CREATE','2026-04-01 13:01:32'),(193,'2026-04-01 16:03:00','Admin User','g armani  urun agacina g armani , Esans bileseni eklendi','CREATE','2026-04-01 13:03:00'),(194,'2026-04-01 16:03:55','Admin User','g armani  ürünü için montaj iş emri oluşturuldu','CREATE','2026-04-01 13:03:55'),(195,'2026-04-01 16:04:24','Admin User','g armani  ürünü için montaj iş emri oluşturuldu','CREATE','2026-04-01 13:04:24'),(196,'2026-04-01 16:10:05','Admin User','GÖKHAN tedarikçisine g armani , jelatin malzemesi için çerçeve sözleşme güncellendi','UPDATE','2026-04-01 13:10:05'),(197,'2026-04-01 17:28:24','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 14:28:24'),(198,'2026-04-01 17:46:11','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-04-01 14:46:11'),(199,'2026-04-01 19:48:43','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 16:48:43'),(200,'2026-04-01 19:52:54','Admin User','DİOR SAVAGE ELİXER, Esans urun agacindaki DİOR SAVAGE ELİXER HAM ESANS bileseni DİOR SAVAGE ELİXER HAM ESANS olarak guncellendi','UPDATE','2026-04-01 16:52:54'),(201,'2026-04-01 19:53:36','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER, paket bileseni DİOR SAVAGE ELİXER, paket olarak guncellendi','UPDATE','2026-04-01 16:53:36'),(202,'2026-04-01 19:53:54','Admin User','DİOR SAVAGE ELİXER urun agacindaki DİOR SAVAGE ELİXER HAM ESANS bileseni DİOR SAVAGE ELİXER HAM ESANS olarak guncellendi','UPDATE','2026-04-01 16:53:54'),(203,'2026-04-01 19:54:39','Admin User','DİOR SAVAGE ELİXER urun agacindan DİOR SAVAGE ELİXER HAM ESANS bileseni silindi','DELETE','2026-04-01 16:54:39'),(204,'2026-04-01 19:55:36','Admin User','DİOR SAVAGE ELİXER ürünü için montaj iş emri oluşturuldu','CREATE','2026-04-01 16:55:36'),(205,'2026-04-01 20:07:18','Admin User','mustafa karakeçi tedarikçisi sisteme eklendi','CREATE','2026-04-01 17:07:18'),(206,'2026-04-01 20:09:04','Admin User','versace eros ürünü sisteme eklendi','CREATE','2026-04-01 17:09:04'),(207,'2026-04-01 20:09:27','Admin User','versace eros ürünü versace eros olarak güncellendi','UPDATE','2026-04-01 17:09:27'),(208,'2026-04-01 20:10:29','Admin User','ysl kirke ürünü sisteme eklendi','CREATE','2026-04-01 17:10:29'),(209,'2026-04-01 20:11:12','Admin User','ysl kirke ürünü ysl kirke olarak güncellendi','UPDATE','2026-04-01 17:11:12'),(210,'2026-04-01 20:14:40','Admin User','ddddd ürünü sisteme eklendi','CREATE','2026-04-01 17:14:40'),(211,'2026-04-01 20:15:20','Admin User','versace eros ürünü versace eros olarak güncellendi','UPDATE','2026-04-01 17:15:20'),(212,'2026-04-01 20:31:51','Admin User','İDO müşterisi için yeni sipariş oluşturuldu (ID: 2)','CREATE','2026-04-01 17:31:51'),(213,'2026-04-01 20:32:01','Admin User','İDO musterisine ait 2 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-04-01 17:32:01'),(214,'2026-04-01 21:02:56','Admin User','İDO musterisine ait 2 nolu siparisin yeni durumu: Tamamlandı','UPDATE','2026-04-01 18:02:56'),(215,'2026-04-01 21:07:27','Admin User','Sipariş Ödemesi kategorisinde 3100 USD tutarında gelir eklendi','CREATE','2026-04-01 18:07:27'),(216,'2026-04-01 21:08:07','Admin User','Sipariş Ödemesi kategorisinde 2100 USD tutarında gelir eklendi','CREATE','2026-04-01 18:08:07'),(217,'2026-04-01 21:12:37','Admin User','versace eros ürünü versace eros olarak güncellendi','UPDATE','2026-04-01 18:12:37'),(218,'2026-04-01 21:13:42','Admin User','EMMOĞLU müşterisi için yeni sipariş oluşturuldu (ID: 4)','CREATE','2026-04-01 18:13:42'),(219,'2026-04-01 21:14:47','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-04-01 18:14:47'),(220,'2026-04-01 21:15:29','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: Beklemede','UPDATE','2026-04-01 18:15:29'),(221,'2026-04-01 21:16:56','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-04-01 18:16:56'),(222,'2026-04-01 21:27:42','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 18:27:42'),(223,'2026-04-01 21:28:05','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-04-01 18:28:05'),(224,'2026-04-01 21:40:48','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-01 18:40:48'),(225,'2026-04-01 21:40:51','Admin User','Admin girisi sonrasi doviz kurlari otomatik guncellendi. USD: 44.4670, EUR: 51.6040','AUTO_UPDATE','2026-04-01 18:40:51'),(226,'2026-04-02 09:23:57','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-02 06:23:57'),(227,'2026-04-02 09:23:59','Admin User','Admin girisi sonrasi doviz kurlari otomatik guncellendi. USD: 44.4670, EUR: 51.6040','AUTO_UPDATE','2026-04-02 06:23:59'),(228,'2026-04-02 10:21:57','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-02 07:21:57'),(229,'2026-04-02 10:22:00','Admin User','Admin girisi sonrasi doviz kurlari otomatik guncellendi. USD: 44.4670, EUR: 51.6040','AUTO_UPDATE','2026-04-02 07:22:00'),(230,'2026-04-02 10:23:37','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-04-02 07:23:37'),(231,'2026-04-02 10:24:52','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: Beklemede','UPDATE','2026-04-02 07:24:52'),(232,'2026-04-02 10:25:41','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-04-02 07:25:41'),(233,'2026-04-02 10:26:02','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: Beklemede','UPDATE','2026-04-02 07:26:02'),(234,'2026-04-02 10:26:27','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-04-02 07:26:27'),(235,'2026-04-02 10:46:53','Admin User','ARMAF	CLUP NUİT İNTENS MEN  adlı tank sisteme eklendi','CREATE','2026-04-02 07:46:53'),(236,'2026-04-02 14:51:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-02 11:51:44'),(237,'2026-04-02 14:51:46','Admin User','Admin girisi sonrasi doviz kurlari otomatik guncellendi. USD: 44.4670, EUR: 51.6040','AUTO_UPDATE','2026-04-02 11:51:46'),(238,'2026-04-02 14:52:19','Admin User','ddddd ürünü ddddd olarak güncellendi','UPDATE','2026-04-02 11:52:19'),(239,'2026-04-02 14:52:37','Admin User','ysl kirke ürünü ysl kirke olarak güncellendi','UPDATE','2026-04-02 11:52:37'),(240,'2026-04-02 15:05:58','Admin User','ahmet canlı müşterisi için yeni sipariş oluşturuldu (ID: 5)','CREATE','2026-04-02 12:05:58'),(241,'2026-04-02 15:07:28','Admin User','FATİH  müşterisi için yeni sipariş oluşturuldu (ID: 6)','CREATE','2026-04-02 12:07:28'),(242,'2026-04-02 15:07:59','Admin User','FATİH  musterisine ait 6 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-04-02 12:07:59'),(243,'2026-04-02 15:08:06','Admin User','ahmet canlı musterisine ait 5 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-04-02 12:08:06'),(244,'2026-04-02 15:09:00','Admin User','FATİH  musterisine ait 6 nolu siparisin yeni durumu: Tamamlandı','UPDATE','2026-04-02 12:09:00'),(245,'2026-04-02 15:09:03','Admin User','ahmet canlı musterisine ait 5 nolu siparisin yeni durumu: Tamamlandı','UPDATE','2026-04-02 12:09:03'),(246,'2026-04-02 15:09:29','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: Beklemede','UPDATE','2026-04-02 12:09:29'),(247,'2026-04-02 15:09:41','Admin User','EMMOĞLU musterisine ait 4 nolu siparisin yeni durumu: İptal Edildi','UPDATE','2026-04-02 12:09:41'),(248,'2026-04-02 15:12:07','Admin User','DİOR SAVAGE ürünü DİOR SAVAGE olarak güncellendi','UPDATE','2026-04-02 12:12:07'),(249,'2026-04-02 15:13:12','Admin User','EMMOĞLU müşterisi için yeni sipariş oluşturuldu (ID: 7)','CREATE','2026-04-02 12:13:12'),(250,'2026-04-02 15:13:32','Admin User','EMMOĞLU musterisine ait 7 nolu siparisin yeni durumu: Onaylandı','UPDATE','2026-04-02 12:13:32'),(251,'2026-04-02 15:15:15','Admin User','DİOR SAVAGE, Esans esansı için iş emri oluşturuldu','CREATE','2026-04-02 12:15:15'),(252,'2026-04-02 15:16:05','Admin User','DİOR SAVAGE, Esans esansı için iş emri silindi','DELETE','2026-04-02 12:16:05'),(253,'2026-04-02 15:16:52','Admin User','DİOR SAVAGE ürünü için montaj iş emri oluşturuldu','CREATE','2026-04-02 12:16:52'),(254,'2026-04-02 15:18:40','Admin User','GÖKHAN tedarikçisine PO-2026-00023 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-02 12:18:40'),(255,'2026-04-02 15:18:45','Admin User','KIRMIZIGÜL tedarikçisine PO-2026-00024 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-02 12:18:45'),(256,'2026-04-02 15:18:49','Admin User','RAMAZAN  tedarikçisine PO-2026-00025 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-02 12:18:49'),(257,'2026-04-02 15:18:54','Admin User','SARI ATİKET tedarikçisine PO-2026-00026 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-02 12:18:54'),(258,'2026-04-02 15:18:59','Admin User','ŞENER  tedarikçisine PO-2026-00027 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-04-02 12:18:59'),(259,'2026-04-02 15:58:11','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-02 12:58:11'),(260,'2026-04-02 15:58:14','Admin User','Admin girisi sonrasi doviz kurlari otomatik guncellendi. USD: 44.4670, EUR: 51.6040','AUTO_UPDATE','2026-04-02 12:58:14'),(261,'2026-04-02 16:00:26','Admin User','Hamza Güzen personeli sisteme eklendi','CREATE','2026-04-02 13:00:26'),(262,'2026-04-02 16:01:26','Admin User','mehmet mutlu personeli sisteme eklendi','CREATE','2026-04-02 13:01:26'),(263,'2026-04-02 16:02:01','Admin User','mehmet mutlu personelinin bilgileri güncellendi','UPDATE','2026-04-02 13:02:01'),(264,'2026-04-02 16:03:18','Admin User','Hamza Güzen personeline 70000 TL avans verildi (2026/4)','CREATE','2026-04-02 13:03:18'),(265,'2026-04-02 16:03:44','Admin User','mehmet mutlu personeline 45000 TL avans verildi (2026/4)','CREATE','2026-04-02 13:03:44'),(266,'2026-04-02 16:04:26','Admin User','Hamza Güzen personeline 2026/4 dönemi için -10000 TL maaş ödemesi yapıldı','CREATE','2026-04-02 13:04:26'),(267,'2026-04-02 16:04:40','Admin User','mehmet mutlu personeline 2026/4 dönemi için 5000 TL maaş ödemesi yapıldı','CREATE','2026-04-02 13:04:40'),(268,'2026-04-02 16:04:57','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-02 13:04:57'),(269,'2026-04-02 16:04:59','Admin User','Admin girisi sonrasi doviz kurlari otomatik guncellendi. USD: 44.4670, EUR: 51.6040','AUTO_UPDATE','2026-04-02 13:04:59'),(270,'2026-04-02 16:06:02','Admin User','mehmet mutlu personeline 25000 TL avans verildi (2026/4)','CREATE','2026-04-02 13:06:02'),(271,'2026-04-02 16:08:33','Admin User','Yeni tekrarlı ödeme tanımlandı: depo a kirası (Kira) - 25000 TL','CREATE','2026-04-02 13:08:33'),(272,'2026-04-02 16:09:01','Admin User','Yeni tekrarlı ödeme tanımlandı: depo b kirası (Kira) - 12000 TL','CREATE','2026-04-02 13:09:01'),(273,'2026-04-02 16:09:36','Admin User','Yeni tekrarlı ödeme tanımlandı: depo takım  (Kira) - 12000 TL','CREATE','2026-04-02 13:09:36'),(274,'2026-04-02 16:09:51','Admin User','depo a kirası ödemesi yapıldı (2026/1) - 25000 TL','CREATE','2026-04-02 13:09:51'),(275,'2026-04-02 16:09:58','Admin User','depo b kirası ödemesi yapıldı (2026/1) - 12000 TL','CREATE','2026-04-02 13:09:58'),(276,'2026-04-02 16:10:02','Admin User','depo takım  ödemesi yapıldı (2026/1) - 12000 TL','CREATE','2026-04-02 13:10:02'),(277,'2026-04-02 16:10:39','Admin User','Yeni tekrarlı ödeme tanımlandı: depo esans (Kira) - 65000 TL','CREATE','2026-04-02 13:10:39'),(278,'2026-04-02 16:11:14','Admin User','Yeni tekrarlı ödeme tanımlandı: depo c (Kira) - 15000 TL','CREATE','2026-04-02 13:11:14'),(279,'2026-04-02 16:13:45','Admin User','EMMOĞLU musterisine ait 7 nolu siparisin yeni durumu: Tamamlandı','UPDATE','2026-04-02 13:13:45'),(280,'2026-04-02 16:19:13','Admin User','Satınalma siparişi #23 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-02 13:19:13'),(281,'2026-04-02 16:19:26','Admin User','Satınalma siparişi #24 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-02 13:19:26'),(282,'2026-04-02 16:19:35','Admin User','Satınalma siparişi #24 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-02 13:19:35'),(283,'2026-04-02 16:19:43','Admin User','Satınalma siparişi #25 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-02 13:19:43'),(284,'2026-04-02 16:19:50','Admin User','Satınalma siparişi #26 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-02 13:19:50'),(285,'2026-04-02 16:20:00','Admin User','Satınalma siparişi #27 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-04-02 13:20:00'),(286,'2026-04-02 16:53:09','Admin User','burhan türk personeli sisteme eklendi','CREATE','2026-04-02 13:53:09'),(287,'2026-04-02 16:53:36','Admin User','depo a kirası ödemesi yapıldı (2026/4) - 25000 TL','CREATE','2026-04-02 13:53:36'),(288,'2026-04-02 16:53:41','Admin User','depo b kirası ödemesi yapıldı (2026/4) - 12000 TL','CREATE','2026-04-02 13:53:41'),(289,'2026-04-02 16:53:44','Admin User','depo c ödemesi yapıldı (2026/4) - 15000 TL','CREATE','2026-04-02 13:53:44'),(290,'2026-04-02 16:53:50','Admin User','depo esans ödemesi yapıldı (2026/4) - 65000 TL','CREATE','2026-04-02 13:53:50'),(291,'2026-04-02 16:53:55','Admin User','depo takım  ödemesi yapıldı (2026/4) - 12000 TL','CREATE','2026-04-02 13:53:55'),(292,'2026-04-02 19:12:29','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-04-02 16:12:29'),(293,'2026-04-02 19:12:31','Admin User','Admin girisi sonrasi doviz kurlari otomatik guncellendi. USD: 44.4950, EUR: 51.2800','AUTO_UPDATE','2026-04-02 16:12:31');
/*!40000 ALTER TABLE `log_tablosu` ENABLE KEYS */;
DROP TABLE IF EXISTS `lokasyonlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lokasyonlar` (
  `lokasyon_id` int(11) NOT NULL AUTO_INCREMENT,
  `depo_ismi` varchar(255) NOT NULL,
  `raf` varchar(100) NOT NULL,
  PRIMARY KEY (`lokasyon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `lokasyonlar` DISABLE KEYS */;
INSERT INTO `lokasyonlar` VALUES (1,'ESANS ','A'),(2,'A DEPEO','A'),(3,'B DEPO','A'),(4,'MALZEME DEPOSU','A'),(5,'TAKIM DEPOSU ','A');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `malzemeler` DISABLE KEYS */;
INSERT INTO `malzemeler` VALUES (1,'DİOR SAVAGE, etiket','etiket',NULL,90.00,'adet',3.00,'TRY',0,0,'A DEPEO','A',0),(2,'DİOR SAVAGE, kutu','kutu',NULL,90.00,'adet',21.43,'TRY',0,0,'A DEPEO','A',0),(3,'DİOR SAVAGE, paket','paket',NULL,90.00,'adet',20.00,'TRY',0,0,'A DEPEO','A',0),(5,'DİOR SAVAGE, takım','takm',NULL,90.00,'adet',111.12,'TRY',0,0,'A DEPEO','A',0),(6,'DİOR SAVAGE, ham esans','ham_esans',NULL,14.95,'adet',7113.86,'TRY',0,0,'A DEPEO','A',0),(7,'DİOR SAVAGE, jelatin','jelatin',NULL,90.00,'adet',4.00,'TRY',0,0,'A DEPEO','A',0),(8,'DİOR SAVAGE ELİXER, etiket','etiket',NULL,10.00,'adet',2.00,'TRY',0,0,'B DEPO','A',0),(9,'DİOR SAVAGE ELİXER, kutu','kutu',NULL,10.00,'adet',17.14,'TRY',0,0,'B DEPO','A',0),(10,'DİOR SAVAGE ELİXER, paket','paket','null',30.30,'adet',12.00,'TRY',0,0,'B DEPO','A',0),(11,'DİOR SAVAGE ELİXER, takım','takm',NULL,10.00,'adet',102.84,'TRY',0,0,'B DEPO','A',0),(12,'DİOR SAVAGE ELİXER, jelatin','jelatin',NULL,10.00,'adet',2.00,'TRY',0,0,'B DEPO','A',0),(13,'DİOR SAVAGE ELİXER esans','ham_esans','',15.50,'lt',2695.40,'TRY',1,0,'ESANS ','A',0),(14,'SU','su','',9933.74,'adet',1.00,'TRY',0,0,'ESANS ','A',0),(15,'ALKOL','alkol','',9301.61,'lt',68.56,'TRY',0,0,'ESANS ','A',0),(16,'g armani , etiket','etiket',NULL,1000.00,'adet',3.30,'TRY',0,0,'A DEPEO','A',0),(17,'g armani , jelatin','jelatin',NULL,1000.00,'adet',214.25,'TRY',0,0,'A DEPEO','A',0),(18,'g armani , takım','takm',NULL,1000.00,'adet',81.42,'TRY',0,0,'A DEPEO','A',0),(19,'g armani , kutu','kutu',NULL,1000.00,'adet',17.14,'TRY',0,0,'A DEPEO','A',0),(20,'g armani , ham esans','ham_esans',NULL,0.50,'adet',4040.56,'TRY',0,0,'A DEPEO','A',0),(21,'g armani , paket','paket',NULL,1000.00,'adet',18.00,'TRY',0,0,'A DEPEO','A',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `montaj_is_emirleri` DISABLE KEYS */;
INSERT INTO `montaj_is_emirleri` VALUES (1,'2026-03-31','Admin User','3','DİOR SAVAGE ELİXER',250.00,'adet','2026-03-31','2026-03-31',' 1 ADET ŞİŞE EKSİK','tamamlandi',249.00,1.00,2,'2026-03-31','2026-03-31'),(2,'2026-03-31','Admin User','3','DİOR SAVAGE ELİXER',300.00,'adet','2026-03-31','2026-03-31',' 5 ADET KIRIK VAR','tamamlandi',295.00,5.00,1,'2026-03-31','2026-03-31'),(3,'2026-04-01','Admin User','2','DİOR SAVAGE',750.00,'adet','2026-04-01','2026-04-01',' bnjmkö','tamamlandi',745.00,5.00,2,'2026-04-01','2026-04-01'),(4,'2026-04-01','Admin User','2','DİOR SAVAGE',760.00,'adet','2026-04-01','2026-04-01',' fghj','tamamlandi',760.00,0.00,1,'2026-04-01','2026-04-01'),(5,'2026-04-01','Admin User','4','g armani ',1000.00,'adet','2026-04-01','2026-04-01',' cvbnmö','tamamlandi',990.00,10.00,2,'2026-04-01','2026-04-01'),(6,'2026-04-01','Admin User','4','g armani ',1000.00,'adet','2026-04-01','2026-04-01','','olusturuldu',0.00,1000.00,1,NULL,NULL),(7,'2026-04-01','Admin User','4','g armani ',1000.00,'adet','2026-04-01','2026-04-01',' ','tamamlandi',992.00,8.00,2,'2026-04-01','2026-04-01'),(8,'2026-04-01','Admin User','3','DİOR SAVAGE ELİXER',440.00,'adet','2026-04-01','2026-04-01',' vbnm','tamamlandi',432.00,8.00,2,'2026-04-01','2026-04-01'),(9,'2026-04-02','Admin User','2','DİOR SAVAGE',900.00,'adet','2026-04-02','2026-04-02',' şşş','tamamlandi',900.00,0.00,1,'2026-04-02','2026-04-02');
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
INSERT INTO `montaj_is_emri_malzeme_listesi` VALUES (1,'8','DİOR SAVAGE ELİXER, etiket','etiket',250.00,'adet'),(1,'9','DİOR SAVAGE ELİXER, kutu','kutu',250.00,'adet'),(1,'10','DİOR SAVAGE ELİXER, paket','paket',37.50,'adet'),(1,'11','DİOR SAVAGE ELİXER, takım','takm',250.00,'adet'),(1,'12','DİOR SAVAGE ELİXER, jelatin','jelatin',250.00,'adet'),(1,'ES-260331-914','DİOR SAVAGE ELİXER, Esans','esans',25.00,'adet'),(2,'8','DİOR SAVAGE ELİXER, etiket','etiket',300.00,'adet'),(2,'9','DİOR SAVAGE ELİXER, kutu','kutu',300.00,'adet'),(2,'10','DİOR SAVAGE ELİXER, paket','paket',45.00,'adet'),(2,'11','DİOR SAVAGE ELİXER, takım','takm',300.00,'adet'),(2,'12','DİOR SAVAGE ELİXER, jelatin','jelatin',300.00,'adet'),(2,'ES-260331-914','DİOR SAVAGE ELİXER, Esans','esans',30.00,'adet'),(3,'1','DİOR SAVAGE, etiket','etiket',750.00,'adet'),(3,'2','DİOR SAVAGE, kutu','kutu',750.00,'adet'),(3,'3','DİOR SAVAGE, paket','paket',750.00,'adet'),(3,'5','DİOR SAVAGE, takım','takm',750.00,'adet'),(3,'7','DİOR SAVAGE, jelatin','jelatin',750.00,'adet'),(3,'ES-260331-936','DİOR SAVAGE, Esans','esans',112.50,'adet'),(4,'1','DİOR SAVAGE, etiket','etiket',760.00,'adet'),(4,'2','DİOR SAVAGE, kutu','kutu',760.00,'adet'),(4,'3','DİOR SAVAGE, paket','paket',760.00,'adet'),(4,'5','DİOR SAVAGE, takım','takm',760.00,'adet'),(4,'7','DİOR SAVAGE, jelatin','jelatin',760.00,'adet'),(4,'ES-260331-936','DİOR SAVAGE, Esans','esans',114.00,'adet'),(5,'16','g armani , etiket','etiket',1000.00,'adet'),(5,'17','g armani , jelatin','jelatin',1000.00,'adet'),(5,'18','g armani , takım','takm',1000.00,'adet'),(5,'19','g armani , kutu','kutu',1000.00,'adet'),(5,'21','g armani , paket','paket',1000.00,'adet'),(6,'16','g armani , etiket','etiket',1000.00,'adet'),(6,'17','g armani , jelatin','jelatin',1000.00,'adet'),(6,'18','g armani , takım','takm',1000.00,'adet'),(6,'19','g armani , kutu','kutu',1000.00,'adet'),(6,'21','g armani , paket','paket',1000.00,'adet'),(6,'ES-260401-762','g armani , Esans','esans',150.00,'adet'),(7,'16','g armani , etiket','etiket',1000.00,'adet'),(7,'17','g armani , jelatin','jelatin',1000.00,'adet'),(7,'18','g armani , takım','takm',1000.00,'adet'),(7,'19','g armani , kutu','kutu',1000.00,'adet'),(7,'21','g armani , paket','paket',1000.00,'adet'),(7,'ES-260401-762','g armani , Esans','esans',150.00,'adet'),(8,'8','DİOR SAVAGE ELİXER, etiket','etiket',440.00,'adet'),(8,'9','DİOR SAVAGE ELİXER, kutu','kutu',440.00,'adet'),(8,'11','DİOR SAVAGE ELİXER, takım','takm',440.00,'adet'),(8,'12','DİOR SAVAGE ELİXER, jelatin','jelatin',440.00,'adet'),(8,'ES-260331-914','DİOR SAVAGE ELİXER, Esans','esans',44.00,'adet'),(8,'10','DİOR SAVAGE ELİXER, paket','paket',57.20,'adet'),(9,'1','DİOR SAVAGE, etiket','etiket',900.00,'adet'),(9,'2','DİOR SAVAGE, kutu','kutu',900.00,'adet'),(9,'3','DİOR SAVAGE, paket','paket',900.00,'adet'),(9,'5','DİOR SAVAGE, takım','takm',900.00,'adet'),(9,'7','DİOR SAVAGE, jelatin','jelatin',900.00,'adet'),(9,'ES-260331-936','DİOR SAVAGE, Esans','esans',135.00,'adet');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
INSERT INTO `musteriler` VALUES (1,'FATİH ','','','','','','',1,'',1),(2,'İDO','','','','','','',1,'',1),(3,'EMMOĞLU','','','','','','',1,'05325083018',1),(4,'mehmet can ','','','','','','',1,'',1),(5,'ahmet pak','','','','','','',1,'',1),(6,'ahmet canlı','','','','','$2y$10$nSSGZ0bPJU0tL7UNO26LR.RCjdG/da89sEpc/Zi1kcp62fyrzAaIu','',1,'',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `personel_avanslar` DISABLE KEYS */;
INSERT INTO `personel_avanslar` VALUES (1,302,'Hamza Güzen',70000.00,'2026-04-02',2026,4,'Nakit','vbnmö',1,'Admin User','2026-04-02 13:03:18',1),(2,303,'mehmet mutlu',45000.00,'2026-04-02',2026,4,'Nakit','vbnmö',1,'Admin User','2026-04-02 13:03:44',1),(3,303,'mehmet mutlu',25000.00,'2026-04-02',2026,4,'Nakit','şçşçşç',1,'Admin User','2026-04-02 13:06:02',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `personel_maas_odemeleri` DISABLE KEYS */;
INSERT INTO `personel_maas_odemeleri` VALUES (2,302,'Hamza Güzen',2026,4,60000.00,70000.00,-10000.00,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:04:26',8),(3,303,'mehmet mutlu',2026,4,50000.00,45000.00,5000.00,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:04:40',9);
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
) ENGINE=InnoDB AUTO_INCREMENT=305 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `personeller` DISABLE KEYS */;
INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Yönetim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$z56pgRUputjO7M5.Pp0W1eHOgVJ16GX3OKYtPi4VGenFweT8xUidK',NULL,0,0.00),(302,'Hamza Güzen','2557572842','1985-09-13','2015-09-13','müdür','öretim','','05321327675','Göztepe\r\nMaslak Cd. No:29','','$2y$10$m3uKzFIHEBq.Oxb7vbS3q.AShlE2vcyRuQyM.OmURE8ucAYHMWH5G','',1,60000.00),(303,'mehmet mutlu','25577582884','1991-01-01','2026-04-02','','','','05543344521','','','$2y$10$O88G9AYAa9ZZVUnAyLk1neoulHGx.6A8.RQNnnbr7g3/oBbXX4EPC','',1,50000.00),(304,'burhan türk','','1995-01-01','2026-04-02','tsşıyıcı','taşıyıcı','','','','','$2y$10$hxiLKPuoaHHdttnj017JveBGubupvJkJD9rZggp9qtGRb6o6.J6DG','',1,35000.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `satinalma_siparis_kalemleri` DISABLE KEYS */;
INSERT INTO `satinalma_siparis_kalemleri` VALUES (1,1,9,'DİOR SAVAGE ELİXER, kutu',1000.00,'adet',0.40,'USD',400.00,1000.00,''),(2,2,8,'DİOR SAVAGE ELİXER, etiket',1000.00,'adet',2.00,'TL',2000.00,1000.00,''),(3,3,11,'DİOR SAVAGE ELİXER, takım',1000.00,'adet',2.40,'USD',2400.00,1000.00,''),(4,4,12,'DİOR SAVAGE ELİXER, jelatin',1000.00,'adet',2.00,'TL',2000.00,1000.00,''),(5,5,10,'DİOR SAVAGE ELİXER, paket',170.00,'adet',12.00,'TL',2040.00,170.00,''),(6,6,5,'DİOR SAVAGE, takım',2500.00,'adet',2.20,'EUR',5500.00,2500.00,''),(7,7,7,'DİOR SAVAGE, jelatin',2500.00,'adet',4.00,'TL',10000.00,2500.00,''),(8,8,1,'DİOR SAVAGE, etiket',2500.00,'adet',3.00,'TL',7500.00,2500.00,''),(9,9,3,'DİOR SAVAGE, paket',2500.00,'adet',20.00,'TL',50000.00,2500.00,''),(10,10,13,'DİOR SAVAGE ELİXER HAM ESANS',15.40,'kg',60.00,'USD',924.00,15.00,''),(11,11,2,'DİOR SAVAGE, kutu',200.00,'Adet',0.50,'USD',100.00,200.00,''),(12,12,6,'DİOR SAVAGE, ham esans',40.00,'adet',150.00,'USD',6000.00,40.00,''),(13,13,18,'g armani , takım',3000.00,'adet',1.90,'USD',5700.00,0.00,''),(14,14,16,'g armani , etiket',3000.00,'adet',3.30,'TL',9900.00,0.00,''),(15,15,17,'g armani , jelatin',3000.00,'adet',5.00,'USD',15000.00,0.00,''),(16,16,21,'g armani , paket',3000.00,'adet',18.00,'USD',54000.00,0.00,''),(18,18,18,'g armani , takım',3000.00,'adet',1.90,'USD',5700.00,0.00,''),(19,19,16,'g armani , etiket',3000.00,'adet',3.30,'TL',9900.00,0.00,''),(20,20,17,'g armani , jelatin',3000.00,'adet',5.00,'USD',15000.00,0.00,''),(21,21,21,'g armani , paket',3000.00,'adet',18.00,'USD',54000.00,0.00,''),(22,22,19,'g armani , kutu',3000.00,'adet',0.40,'USD',1200.00,3000.00,''),(23,23,12,'DİOR SAVAGE ELİXER, jelatin',990.00,'adet',2.00,'TL',1980.00,0.00,''),(24,24,11,'DİOR SAVAGE ELİXER, takım',990.00,'adet',2.40,'USD',2376.00,0.00,''),(25,25,10,'DİOR SAVAGE ELİXER, paket',99.70,'adet',12.00,'TL',1196.40,0.00,''),(26,26,8,'DİOR SAVAGE ELİXER, etiket',990.00,'adet',2.00,'TL',1980.00,0.00,''),(27,27,9,'DİOR SAVAGE ELİXER, kutu',990.00,'adet',0.40,'USD',396.00,0.00,'');
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `satinalma_siparisler` DISABLE KEYS */;
INSERT INTO `satinalma_siparisler` VALUES (1,'PO-2026-00001',2,'ŞENER ','2026-03-31',NULL,'tamamlandi','bekliyor',NULL,400.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-31 09:26:32','2026-03-31 09:30:46'),(2,'PO-2026-00002',3,'SARI ATİKET','2026-03-31',NULL,'tamamlandi','bekliyor',NULL,2000.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-31 09:26:39','2026-03-31 09:29:29'),(3,'PO-2026-00003',4,'KIRMIZIGÜL','2026-03-31',NULL,'tamamlandi','bekliyor',NULL,2400.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-31 09:26:44','2026-03-31 09:30:01'),(4,'PO-2026-00004',5,'GÖKHAN','2026-03-31',NULL,'tamamlandi','bekliyor',NULL,2000.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-31 09:26:50','2026-03-31 09:29:48'),(5,'PO-2026-00005',6,'RAMAZAN ','2026-03-31',NULL,'tamamlandi','bekliyor',NULL,2040.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-31 09:26:55','2026-03-31 09:30:30'),(6,'PO-2026-00006',15,'ADEM TAKIM','2026-04-01',NULL,'tamamlandi','bekliyor',NULL,5500.00,'EUR','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 08:21:56','2026-04-01 08:31:43'),(7,'PO-2026-00007',5,'GÖKHAN','2026-04-01',NULL,'tamamlandi','bekliyor',NULL,10000.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 08:22:02','2026-04-01 08:32:16'),(8,'PO-2026-00008',13,'MAVİ ETİKET ','2026-04-01',NULL,'tamamlandi','bekliyor',NULL,7500.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 08:22:06','2026-04-01 08:32:25'),(9,'PO-2026-00009',6,'RAMAZAN ','2026-04-01',NULL,'tamamlandi','bekliyor',NULL,50000.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 08:22:11','2026-04-01 08:31:56'),(10,'PO-2026-00010',1,'TEVFİK','2026-04-01',NULL,'kismen_teslim','bekliyor',NULL,924.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 08:22:16','2026-04-01 08:31:08'),(11,'PO-2026-00011',16,'esengül ','2026-04-01',NULL,'tamamlandi','bekliyor',NULL,100.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 08:23:05','2026-04-01 08:32:06'),(12,'PO-2026-00012',11,'LUZİ ','2026-04-01','2026-04-01','tamamlandi','bekliyor',NULL,6000.00,'USD','',1,'Admin User','2026-04-01 08:28:36','2026-04-01 08:32:38'),(13,'PO-2026-00013',15,'ADEM TAKIM','2026-04-01',NULL,'gonderildi','bekliyor',NULL,5700.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:11:39','2026-04-01 12:17:04'),(14,'PO-2026-00014',13,'MAVİ ETİKET ','2026-04-01',NULL,'gonderildi','bekliyor',NULL,9900.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:11:45','2026-04-01 12:16:57'),(15,'PO-2026-00015',5,'GÖKHAN','2026-04-01',NULL,'gonderildi','bekliyor',NULL,15000.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:11:51','2026-04-01 12:16:14'),(16,'PO-2026-00016',6,'RAMAZAN ','2026-04-01',NULL,'gonderildi','bekliyor',NULL,54000.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:11:56','2026-04-01 12:16:03'),(18,'PO-2026-00018',15,'ADEM TAKIM','2026-04-01',NULL,'gonderildi','bekliyor',NULL,5700.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:12:19','2026-04-01 12:13:31'),(19,'PO-2026-00019',13,'MAVİ ETİKET ','2026-04-01',NULL,'gonderildi','bekliyor',NULL,9900.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:12:23','2026-04-01 12:13:39'),(20,'PO-2026-00020',5,'GÖKHAN','2026-04-01',NULL,'gonderildi','bekliyor',NULL,15000.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:12:27','2026-04-01 12:13:22'),(21,'PO-2026-00021',6,'RAMAZAN ','2026-04-01',NULL,'gonderildi','bekliyor',NULL,54000.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:12:32','2026-04-01 12:13:10'),(22,'PO-2026-00022',2,'ŞENER ','2026-04-01',NULL,'tamamlandi','bekliyor',NULL,1200.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-01 12:12:35','2026-04-01 12:20:17'),(23,'PO-2026-00023',5,'GÖKHAN','2026-04-02',NULL,'gonderildi','bekliyor',NULL,1980.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-02 12:18:40','2026-04-02 13:19:13'),(24,'PO-2026-00024',4,'KIRMIZIGÜL','2026-04-02',NULL,'gonderildi','bekliyor',NULL,2376.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-02 12:18:45','2026-04-02 13:19:26'),(25,'PO-2026-00025',6,'RAMAZAN ','2026-04-02',NULL,'gonderildi','bekliyor',NULL,1196.40,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-02 12:18:49','2026-04-02 13:19:43'),(26,'PO-2026-00026',3,'SARI ATİKET','2026-04-02',NULL,'gonderildi','bekliyor',NULL,1980.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-02 12:18:54','2026-04-02 13:19:50'),(27,'PO-2026-00027',2,'ŞENER ','2026-04-02',NULL,'gonderildi','bekliyor',NULL,396.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-04-02 12:18:59','2026-04-02 13:20:00');
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
INSERT INTO `siparis_kalemleri` VALUES (1,3,'DİOR SAVAGE ELİXER',500,'adet',6.20,3100.00,'USD'),(2,2,'DİOR SAVAGE',722,'adet',8.00,5776.00,'USD'),(4,5,'versace eros',300,'adet',8.00,2400.00,'USD'),(4,6,'ysl kirke',20,'adet',6.50,130.00,'USD'),(4,2,'DİOR SAVAGE',790,'adet',8.00,6320.00,'USD'),(5,7,'ddddd',250,'adet',8.00,2000.00,'USD'),(5,2,'DİOR SAVAGE',200,'adet',8.00,1600.00,'USD'),(5,3,'DİOR SAVAGE ELİXER',400,'adet',6.20,2480.00,'USD'),(5,5,'versace eros',400,'adet',8.00,3200.00,'USD'),(5,6,'ysl kirke',375,'adet',6.50,2437.50,'USD'),(6,2,'DİOR SAVAGE',83,'adet',8.00,664.00,'USD'),(6,3,'DİOR SAVAGE ELİXER',76,'adet',6.20,471.20,'USD'),(6,4,'g armani ',82,'adet',6.50,533.00,'USD'),(6,5,'versace eros',100,'adet',8.00,800.00,'USD'),(6,6,'ysl kirke',250,'adet',6.50,1625.00,'USD'),(7,2,'DİOR SAVAGE',450,'adet',8.00,3600.00,'USD');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `siparisler` DISABLE KEYS */;
INSERT INTO `siparisler` VALUES (1,3,'EMMOĞLU','2026-03-31 14:47:38','tamamlandi',500,'Personel: Admin User',1,'Admin User','2026-03-31 14:48:28','','odendi',NULL,3100.00,'USD'),(2,2,'İDO','2026-04-01 20:31:51','tamamlandi',722,'Personel: Admin User',1,'Admin User','2026-04-01 20:32:01','','kismi_odendi',NULL,2100.00,'USD'),(4,3,'EMMOĞLU','2026-04-01 21:13:42','iptal_edildi',1110,'Personel: Admin User',NULL,NULL,NULL,'','bekliyor',NULL,0.00,'USD'),(5,6,'ahmet canlı','2026-04-02 15:05:58','tamamlandi',1625,'Personel: Admin User',1,'Admin User','2026-04-02 15:08:06','','bekliyor',NULL,0.00,'USD'),(6,1,'FATİH ','2026-04-02 15:07:28','tamamlandi',591,'Personel: Admin User',1,'Admin User','2026-04-02 15:07:59','','bekliyor',NULL,0.00,'USD'),(7,3,'EMMOĞLU','2026-04-02 15:13:12','tamamlandi',450,'Personel: Admin User',1,'Admin User','2026-04-02 15:13:32','','bekliyor',NULL,0.00,'USD');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `sirket_kasasi` DISABLE KEYS */;
INSERT INTO `sirket_kasasi` VALUES (1,'EUR',3100.00,'2026-04-01 21:07:27'),(2,'TL',-310900.00,'2026-04-02 16:53:55');
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
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `stok_hareket_kayitlari` DISABLE KEYS */;
INSERT INTO `stok_hareket_kayitlari` VALUES (1,'2026-03-31 12:29:29','malzeme','8','DİOR SAVAGE ELİXER, etiket','adet',1000.00,'giris','mal_kabul','B DEPO','A',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 5] [Siparis: PO-2026-00002]',1,'Admin User','SARI ATİKET',3),(2,'2026-03-31 12:29:48','malzeme','12','DİOR SAVAGE ELİXER, jelatin','adet',1000.00,'giris','mal_kabul','B DEPO','A',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 1] [Siparis: PO-2026-00004]',1,'Admin User','GÖKHAN',5),(3,'2026-03-31 12:30:01','malzeme','11','DİOR SAVAGE ELİXER, takım','adet',1000.00,'giris','mal_kabul','B DEPO','A',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 2] [Siparis: PO-2026-00003]',1,'Admin User','KIRMIZIGÜL',4),(4,'2026-03-31 12:30:30','malzeme','10','DİOR SAVAGE ELİXER, paket','adet',170.00,'giris','mal_kabul','B DEPO','A',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 3] [Siparis: PO-2026-00005]',1,'Admin User','RAMAZAN ',6),(5,'2026-03-31 12:30:46','malzeme','9','DİOR SAVAGE ELİXER, kutu','adet',1000.00,'giris','mal_kabul','B DEPO','A',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 6] [Siparis: PO-2026-00001]',1,'Admin User','ŞENER ',2),(6,'2026-03-31 12:31:48','malzeme','13','DİOR SAVAGE ELİXER HAM ESANS','adet',15.00,'giris','mal_kabul','ESANS ','A',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 4]',1,'Admin User','TEVFİK',1),(7,'2026-03-31 12:33:12','malzeme','15','ALKOL','lt',10000.00,'giris','mal_kabul','ESANS ','A',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 7]',1,'Admin User','ZÜLFÜKAR KIRMIZIGÜL',8),(8,'2026-03-31 12:33:46','Bileşen','15','ALKOL','lt',77.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(9,'2026-03-31 12:33:46','Bileşen','14','SU','lt',8.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(10,'2026-03-31 12:33:46','Bileşen','13','DİOR SAVAGE ELİXER HAM ESANS','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(11,'2026-03-31 12:33:59','Esans','ES-260331-914','DİOR SAVAGE ELİXER, Esans','lt',100.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(12,'2026-03-31 14:08:48','etiket','8','DİOR SAVAGE ELİXER, etiket','adet',300.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(13,'2026-03-31 14:08:48','kutu','9','DİOR SAVAGE ELİXER, kutu','adet',300.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(14,'2026-03-31 14:08:48','paket','10','DİOR SAVAGE ELİXER, paket','adet',45.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(15,'2026-03-31 14:08:48','takm','11','DİOR SAVAGE ELİXER, takım','adet',300.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(16,'2026-03-31 14:08:48','jelatin','12','DİOR SAVAGE ELİXER, jelatin','adet',300.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(17,'2026-03-31 14:08:48','esans','ES-260331-914','DİOR SAVAGE ELİXER, Esans','adet',30.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(18,'2026-03-31 14:08:55','etiket','8','DİOR SAVAGE ELİXER, etiket','adet',250.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(19,'2026-03-31 14:08:55','kutu','9','DİOR SAVAGE ELİXER, kutu','adet',250.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(20,'2026-03-31 14:08:55','paket','10','DİOR SAVAGE ELİXER, paket','adet',37.50,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(21,'2026-03-31 14:08:55','takm','11','DİOR SAVAGE ELİXER, takım','adet',250.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(22,'2026-03-31 14:08:55','jelatin','12','DİOR SAVAGE ELİXER, jelatin','adet',250.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(23,'2026-03-31 14:08:55','esans','ES-260331-914','DİOR SAVAGE ELİXER, Esans','adet',25.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(24,'2026-03-31 14:09:14','Ürün','3','DİOR SAVAGE ELİXER','adet',295.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(25,'2026-03-31 14:09:40','Ürün','3','DİOR SAVAGE ELİXER','adet',249.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(26,'2026-03-31 14:11:42','malzeme','13','DİOR SAVAGE ELİXER HAM ESANS','kg',35.00,'giris','mal_kabul','ESANS ','A',NULL,'',NULL,NULL,NULL,'123\r\n [Sozlesme ID: 4]',1,'Admin User','TEVFİK',1),(27,'2026-03-31 14:12:36','Bileşen','15','ALKOL','lt',177.10,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(28,'2026-03-31 14:12:36','Bileşen','14','SU','lt',18.40,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(29,'2026-03-31 14:12:36','Bileşen','13','DİOR SAVAGE ELİXER HAM ESANS','lt',34.50,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(30,'2026-03-31 14:12:54','Esans','ES-260331-914','DİOR SAVAGE ELİXER, Esans','lt',230.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(31,'2026-03-31 14:48:46','urun','3','DİOR SAVAGE ELİXER','adet',500.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,3,'EMMOĞLU','Musteri siparisi',1,'Admin User',NULL,NULL),(33,'2026-04-01 11:31:08','malzeme','13','DİOR SAVAGE ELİXER esans','lt',15.00,'giris','mal_kabul','ESANS ','A',NULL,'',NULL,NULL,NULL,'124 [Sozlesme ID: 4] [Siparis: PO-2026-00010]',1,'Admin User','TEVFİK',1),(34,'2026-04-01 11:31:43','malzeme','5','DİOR SAVAGE, takım','adet',2500.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'1 [Sozlesme ID: 8] [Siparis: PO-2026-00006]',1,'Admin User','ADEM TAKIM',15),(35,'2026-04-01 11:31:56','malzeme','3','DİOR SAVAGE, paket','adet',2500.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'1 [Sozlesme ID: 16] [Siparis: PO-2026-00009]',1,'Admin User','RAMAZAN ',6),(36,'2026-04-01 11:32:06','malzeme','2','DİOR SAVAGE, kutu','adet',200.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'1 [Sozlesme ID: 14] [Siparis: PO-2026-00011]',1,'Admin User','esengül ',16),(37,'2026-04-01 11:32:16','malzeme','7','DİOR SAVAGE, jelatin','adet',2500.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'1 [Sozlesme ID: 17] [Siparis: PO-2026-00007]',1,'Admin User','GÖKHAN',5),(38,'2026-04-01 11:32:25','malzeme','1','DİOR SAVAGE, etiket','adet',2500.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'1 [Sozlesme ID: 9] [Siparis: PO-2026-00008]',1,'Admin User','MAVİ ETİKET ',13),(39,'2026-04-01 11:32:38','malzeme','6','DİOR SAVAGE, ham esans','adet',40.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'1 [Sozlesme ID: 15] [Siparis: PO-2026-00012]',1,'Admin User','LUZİ ',11),(40,'2026-04-01 11:33:31','Bileşen','6','DİOR SAVAGE, ham esans','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(41,'2026-04-01 11:33:31','Bileşen','14','SU','lt',8.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(42,'2026-04-01 11:33:31','Bileşen','15','ALKOL','lt',77.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(43,'2026-04-01 11:33:39','Bileşen','6','DİOR SAVAGE, ham esans','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(44,'2026-04-01 11:33:39','Bileşen','14','SU','lt',8.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(45,'2026-04-01 11:33:39','Bileşen','15','ALKOL','lt',77.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(46,'2026-04-01 11:33:51','Esans','ES-260331-936','DİOR SAVAGE, Esans','lt',100.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(47,'2026-04-01 11:34:09','Esans','ES-260331-936','DİOR SAVAGE, Esans','lt',99.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(48,'2026-04-01 11:36:02','Bileşen','6','DİOR SAVAGE, ham esans','lt',9.90,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(49,'2026-04-01 11:36:02','Bileşen','14','SU','lt',5.28,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(50,'2026-04-01 11:36:02','Bileşen','15','ALKOL','lt',50.82,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(51,'2026-04-01 11:36:19','Esans','ES-260331-936','DİOR SAVAGE, Esans','lt',65.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(52,'2026-04-01 11:38:13','malzeme','2','DİOR SAVAGE, kutu','adet',2300.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'111 [Sozlesme ID: 14]',1,'Admin User','esengül ',16),(53,'2026-04-01 11:51:07','etiket','1','DİOR SAVAGE, etiket','adet',760.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(54,'2026-04-01 11:51:07','kutu','2','DİOR SAVAGE, kutu','adet',760.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(55,'2026-04-01 11:51:07','paket','3','DİOR SAVAGE, paket','adet',760.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(56,'2026-04-01 11:51:07','takm','5','DİOR SAVAGE, takım','adet',760.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(57,'2026-04-01 11:51:07','jelatin','7','DİOR SAVAGE, jelatin','adet',760.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(58,'2026-04-01 11:51:07','esans','ES-260331-936','DİOR SAVAGE, Esans','adet',114.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(59,'2026-04-01 11:51:13','etiket','1','DİOR SAVAGE, etiket','adet',750.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(60,'2026-04-01 11:51:13','kutu','2','DİOR SAVAGE, kutu','adet',750.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(61,'2026-04-01 11:51:13','paket','3','DİOR SAVAGE, paket','adet',750.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(62,'2026-04-01 11:51:13','takm','5','DİOR SAVAGE, takım','adet',750.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(63,'2026-04-01 11:51:13','jelatin','7','DİOR SAVAGE, jelatin','adet',750.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(64,'2026-04-01 11:51:13','esans','ES-260331-936','DİOR SAVAGE, Esans','adet',112.50,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(65,'2026-04-01 11:51:31','Ürün','2','DİOR SAVAGE','adet',760.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(66,'2026-04-01 11:51:44','Ürün','2','DİOR SAVAGE','adet',745.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(67,'2026-04-01 14:59:40','malzeme','20','g armani , ham esans','adet',32.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'aa [Sozlesme ID: 18]',1,'Admin User','TEVFİK',1),(68,'2026-04-01 15:19:34','malzeme','16','g armani , etiket','adet',3000.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'xcvbnm [Sozlesme ID: 24]',1,'Admin User','MAVİ ETİKET ',13),(69,'2026-04-01 15:19:55','malzeme','17','g armani , jelatin','adet',3000.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'cvbnmö [Sozlesme ID: 22]',1,'Admin User','GÖKHAN',5),(70,'2026-04-01 15:20:17','malzeme','19','g armani , kutu','adet',3000.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'ghggtrh [Sozlesme ID: 25] [Siparis: PO-2026-00022]',1,'Admin User','ŞENER ',2),(71,'2026-04-01 15:20:38','malzeme','21','g armani , paket','adet',3000.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'.i [Sozlesme ID: 26]',1,'Admin User','RAMAZAN ',6),(72,'2026-04-01 15:20:57','malzeme','18','g armani , takım','adet',3000.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'iş [Sozlesme ID: 20]',1,'Admin User','ADEM TAKIM',15),(73,'2026-04-01 15:21:52','Bileşen','15','ALKOL','lt',161.70,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(74,'2026-04-01 15:21:52','Bileşen','14','SU','lt',10.50,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(75,'2026-04-01 15:21:52','Bileşen','20','g armani , ham esans','lt',31.50,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(76,'2026-04-01 15:22:13','Esans','ES-260401-762','g armani , Esans','lt',208.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(77,'2026-04-01 15:56:10','etiket','16','g armani , etiket','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(78,'2026-04-01 15:56:10','jelatin','17','g armani , jelatin','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(79,'2026-04-01 15:56:10','takm','18','g armani , takım','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(80,'2026-04-01 15:56:10','kutu','19','g armani , kutu','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(81,'2026-04-01 15:56:10','paket','21','g armani , paket','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(82,'2026-04-01 15:56:35','Ürün','4','g armani ','adet',990.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(83,'2026-04-01 19:55:44','etiket','8','DİOR SAVAGE ELİXER, etiket','adet',440.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(84,'2026-04-01 19:55:44','kutu','9','DİOR SAVAGE ELİXER, kutu','adet',440.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(85,'2026-04-01 19:55:44','takm','11','DİOR SAVAGE ELİXER, takım','adet',440.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(86,'2026-04-01 19:55:44','jelatin','12','DİOR SAVAGE ELİXER, jelatin','adet',440.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(87,'2026-04-01 19:55:44','esans','ES-260331-914','DİOR SAVAGE ELİXER, Esans','adet',44.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(88,'2026-04-01 19:55:44','paket','10','DİOR SAVAGE ELİXER, paket','adet',57.20,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(89,'2026-04-01 19:55:52','etiket','16','g armani , etiket','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(90,'2026-04-01 19:55:52','jelatin','17','g armani , jelatin','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(91,'2026-04-01 19:55:52','takm','18','g armani , takım','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(92,'2026-04-01 19:55:52','kutu','19','g armani , kutu','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(93,'2026-04-01 19:55:52','paket','21','g armani , paket','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(94,'2026-04-01 19:55:52','esans','ES-260401-762','g armani , Esans','adet',150.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(105,'2026-04-01 19:57:05','Ürün','3','DİOR SAVAGE ELİXER','adet',432.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(106,'2026-04-01 19:57:52','Ürün','4','g armani ','adet',992.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,7,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(107,'2026-04-01 21:02:56','urun','2','DİOR SAVAGE','adet',722.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,2,'İDO','Musteri siparisi',1,'Admin User',NULL,NULL),(112,'2026-04-02 15:09:00','urun','2','DİOR SAVAGE','adet',83.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,1,'FATİH ','Musteri siparisi',1,'Admin User',NULL,NULL),(113,'2026-04-02 15:09:00','urun','3','DİOR SAVAGE ELİXER','adet',76.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,1,'FATİH ','Musteri siparisi',1,'Admin User',NULL,NULL),(114,'2026-04-02 15:09:00','urun','4','g armani ','adet',82.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,1,'FATİH ','Musteri siparisi',1,'Admin User',NULL,NULL),(115,'2026-04-02 15:09:00','urun','5','versace eros','adet',100.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,1,'FATİH ','Musteri siparisi',1,'Admin User',NULL,NULL),(116,'2026-04-02 15:09:00','urun','6','ysl kirke','adet',250.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,1,'FATİH ','Musteri siparisi',1,'Admin User',NULL,NULL),(117,'2026-04-02 15:09:03','urun','7','ddddd','adet',250.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,6,'ahmet canlı','Musteri siparisi',1,'Admin User',NULL,NULL),(118,'2026-04-02 15:09:03','urun','2','DİOR SAVAGE','adet',200.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,6,'ahmet canlı','Musteri siparisi',1,'Admin User',NULL,NULL),(119,'2026-04-02 15:09:03','urun','3','DİOR SAVAGE ELİXER','adet',400.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,6,'ahmet canlı','Musteri siparisi',1,'Admin User',NULL,NULL),(120,'2026-04-02 15:09:03','urun','5','versace eros','adet',400.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,6,'ahmet canlı','Musteri siparisi',1,'Admin User',NULL,NULL),(121,'2026-04-02 15:09:03','urun','6','ysl kirke','adet',375.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,6,'ahmet canlı','Musteri siparisi',1,'Admin User',NULL,NULL),(122,'2026-04-02 15:14:37','malzeme','6','DİOR SAVAGE, ham esans','adet',30.00,'giris','mal_kabul','A DEPEO','A',NULL,'',NULL,NULL,NULL,'mnnn [Sozlesme ID: 15]',1,'Admin User','LUZİ ',11),(123,'2026-04-02 15:15:22','Bileşen','6','DİOR SAVAGE, ham esans','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(124,'2026-04-02 15:15:22','Bileşen','14','SU','lt',8.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(125,'2026-04-02 15:15:22','Bileşen','15','ALKOL','lt',77.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(126,'2026-04-02 15:15:34','Esans','ES-260331-936','DİOR SAVAGE, Esans','lt',100.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,8,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(127,'2026-04-02 15:15:46','Bileşen','6','DİOR SAVAGE, ham esans','lt',0.15,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(128,'2026-04-02 15:15:46','Bileşen','14','SU','lt',0.08,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(129,'2026-04-02 15:15:46','Bileşen','15','ALKOL','lt',0.77,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(130,'2026-04-02 15:17:01','etiket','1','DİOR SAVAGE, etiket','adet',900.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,9,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(131,'2026-04-02 15:17:01','kutu','2','DİOR SAVAGE, kutu','adet',900.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,9,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(132,'2026-04-02 15:17:01','paket','3','DİOR SAVAGE, paket','adet',900.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,9,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(133,'2026-04-02 15:17:01','takm','5','DİOR SAVAGE, takım','adet',900.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,9,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(134,'2026-04-02 15:17:01','jelatin','7','DİOR SAVAGE, jelatin','adet',900.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,9,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(135,'2026-04-02 15:17:01','esans','ES-260331-936','DİOR SAVAGE, Esans','adet',135.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,9,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(136,'2026-04-02 15:17:18','Ürün','2','DİOR SAVAGE','adet',900.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,9,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(142,'2026-04-02 16:13:45','urun','2','DİOR SAVAGE','adet',450.00,'cikis','cikis',NULL,NULL,NULL,'7',NULL,3,'EMMOĞLU','Musteri siparisi',1,'Admin User',NULL,NULL);
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
INSERT INTO `stok_hareketleri_sozlesmeler` VALUES (1,5,8,1000.00,'2026-03-31 09:29:29',2.00,'TL','SARI ATİKET',3,'2026-03-31','2026-04-30'),(2,1,12,1000.00,'2026-03-31 09:29:48',2.00,'TL','GÖKHAN',5,'2026-03-31','2026-05-30'),(3,2,11,1000.00,'2026-03-31 09:30:01',2.40,'USD','KIRMIZIGÜL',4,'2026-03-31','2026-04-30'),(4,3,10,170.00,'2026-03-31 09:30:30',12.00,'TL','RAMAZAN ',6,'2026-03-31','2026-05-30'),(5,6,9,1000.00,'2026-03-31 09:30:46',0.40,'USD','ŞENER ',2,'2026-03-31','2026-04-30'),(6,4,13,15.00,'2026-03-31 09:31:48',60.00,'USD','TEVFİK',1,'2026-03-31','2026-04-30'),(7,7,15,10000.00,'2026-03-31 09:33:12',1.60,'USD','ZÜLFÜKAR KIRMIZIGÜL',8,'2026-03-31','2026-04-30'),(26,4,13,35.00,'2026-03-31 11:11:42',60.00,'USD','TEVFİK',1,'2026-03-31','2026-04-30'),(33,4,13,15.00,'2026-04-01 08:31:08',60.00,'USD','TEVFİK',1,'2026-03-31','2026-04-30'),(34,8,5,2500.00,'2026-04-01 08:31:43',2.20,'EUR','ADEM TAKIM',15,'2026-03-31','2026-05-30'),(35,16,3,2500.00,'2026-04-01 08:31:56',20.00,'TL','RAMAZAN ',6,'2026-04-01','2026-06-01'),(36,14,2,200.00,'2026-04-01 08:32:06',0.50,'USD','esengül ',16,'2026-04-01','2026-05-01'),(37,17,7,2500.00,'2026-04-01 08:32:16',4.00,'TL','GÖKHAN',5,'2026-04-01','2026-06-01'),(38,9,1,2500.00,'2026-04-01 08:32:25',3.00,'TL','MAVİ ETİKET ',13,'2026-03-31','2026-05-30'),(39,15,6,40.00,'2026-04-01 08:32:38',160.00,'USD','LUZİ ',11,'2026-04-01','2026-06-01'),(52,14,2,2300.00,'2026-04-01 08:38:13',0.50,'USD','esengül ',16,'2026-04-01','2026-05-01'),(67,18,20,32.00,'2026-04-01 11:59:40',80.00,'EUR','TEVFİK',1,'2026-04-01','2026-06-26'),(68,24,16,3000.00,'2026-04-01 12:19:34',3.30,'TL','MAVİ ETİKET ',13,'2026-04-01','2026-06-09'),(69,22,17,3000.00,'2026-04-01 12:19:55',5.00,'USD','GÖKHAN',5,'2026-04-01','2026-06-01'),(70,25,19,3000.00,'2026-04-01 12:20:17',0.40,'USD','ŞENER ',2,'2026-04-01','2026-07-23'),(71,26,21,3000.00,'2026-04-01 12:20:38',18.00,'TL','RAMAZAN ',6,'2026-04-01','2026-06-18'),(72,20,18,3000.00,'2026-04-01 12:20:57',1.90,'USD','ADEM TAKIM',15,'2026-04-01','2026-06-18'),(122,15,6,30.00,'2026-04-02 12:14:37',160.00,'USD','LUZİ ',11,'2026-04-01','2026-06-01');
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
INSERT INTO `tanklar` VALUES (1,'01','DİOR SAVAGE ','',250.00),(2,'002','DİOR SAVAGE ELİXSER','',250.00),(3,'003','g armani','',250.00),(4,'004','ARMAF	CLUP NUİT İNTENS MEN ','',250.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tedarikciler` DISABLE KEYS */;
INSERT INTO `tedarikciler` VALUES (1,'TEVFİK','ESANS','','','','','','',''),(2,'ŞENER ','KUTU','','','','','','',''),(3,'SARI ATİKET','ETİKET','','','','','','',''),(4,'KIRMIZIGÜL','TAKIM','','','','','','',''),(5,'GÖKHAN','JELATİN','','','','','','',''),(6,'RAMAZAN ','PAKET','','','','','','',''),(7,'HAMZA ','SU','','','','','','',''),(8,'ZÜLFÜKAR KIRMIZIGÜL','ALKOL','','','','','','',''),(10,'BEKİR ','KUTUCU','','','','','','',''),(11,'LUZİ ','ESANS','','','','','','',''),(12,'SELÜZ ESANS','ESANS','','','','','','',''),(13,'MAVİ ETİKET ','ETİKET','','','','','','',''),(14,'VEYSİ SİNANLI ','TAKIM','','','','','','',''),(15,'ADEM TAKIM','TAKIM','','','','','','',''),(16,'esengül ','kutu','','','','','','',''),(17,'mustafa karakeçi','kozmetik','','','','','','','');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tekrarli_odeme_gecmisi` DISABLE KEYS */;
INSERT INTO `tekrarli_odeme_gecmisi` VALUES (1,1,'depo a kirası','Kira',25000.00,2026,1,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:09:51',11),(2,2,'depo b kirası','Kira',12000.00,2026,1,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:09:58',12),(3,3,'depo takım ','Kira',12000.00,2026,1,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:10:02',13),(4,1,'depo a kirası','Kira',25000.00,2026,4,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:53:36',14),(5,2,'depo b kirası','Kira',12000.00,2026,4,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:53:41',15),(6,5,'depo c','Kira',15000.00,2026,4,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:53:44',16),(7,4,'depo esans','Kira',65000.00,2026,4,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:53:50',17),(8,3,'depo takım ','Kira',12000.00,2026,4,'2026-04-02','Havale','',1,'Admin User','2026-04-02 13:53:55',18);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `tekrarli_odemeler` DISABLE KEYS */;
INSERT INTO `tekrarli_odemeler` VALUES (1,'depo a kirası','Kira',25000.00,1,'sdfghjklş','dfghjkl',1,1,'Admin User','2026-04-02 13:08:33'),(2,'depo b kirası','Kira',12000.00,1,'dfghjkl','sdfghklşi',1,1,'Admin User','2026-04-02 13:09:01'),(3,'depo takım ','Kira',12000.00,10,'sdfghj','asdfgl',1,1,'Admin User','2026-04-02 13:09:36'),(4,'depo esans','Kira',65000.00,5,'asdfghj','asdfg',1,1,'Admin User','2026-04-02 13:10:39'),(5,'depo c','Kira',15000.00,1,'sdfghjk','asdfghjk',1,1,'Admin User','2026-04-02 13:11:14');
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `urun_agaci` DISABLE KEYS */;
INSERT INTO `urun_agaci` VALUES (12,2,'DİOR SAVAGE','etiket','1','DİOR SAVAGE, etiket',1.00,'urun'),(13,2,'DİOR SAVAGE','kutu','2','DİOR SAVAGE, kutu',1.00,'urun'),(14,2,'DİOR SAVAGE','paket','3','DİOR SAVAGE, paket',1.00,'urun'),(16,2,'DİOR SAVAGE','takm','5','DİOR SAVAGE, takım',1.00,'urun'),(17,2,'DİOR SAVAGE','jelatin','7','DİOR SAVAGE, jelatin',1.00,'urun'),(18,2,'DİOR SAVAGE','esans','ES-260331-936','DİOR SAVAGE, Esans',0.15,'urun'),(19,3,'DİOR SAVAGE ELİXER','etiket','8','DİOR SAVAGE ELİXER, etiket',1.00,'urun'),(20,3,'DİOR SAVAGE ELİXER','kutu','9','DİOR SAVAGE ELİXER, kutu',1.00,'urun'),(22,3,'DİOR SAVAGE ELİXER','takm','11','DİOR SAVAGE ELİXER, takım',1.00,'urun'),(23,3,'DİOR SAVAGE ELİXER','jelatin','12','DİOR SAVAGE ELİXER, jelatin',1.00,'urun'),(24,3,'DİOR SAVAGE ELİXER','esans','ES-260331-914','DİOR SAVAGE ELİXER, Esans',0.10,'urun'),(25,2,'DİOR SAVAGE, Esans','malzeme','6','DİOR SAVAGE, ham esans',0.15,'esans'),(26,2,'DİOR SAVAGE, Esans','malzeme','14','SU',0.08,'esans'),(27,2,'DİOR SAVAGE, Esans','malzeme','15','ALKOL',0.77,'esans'),(29,3,'DİOR SAVAGE ELİXER, Esans','malzeme','15','ALKOL',0.77,'esans'),(30,3,'DİOR SAVAGE ELİXER, Esans','malzeme','14','SU',0.08,'esans'),(31,3,'DİOR SAVAGE ELİXER, Esans','malzeme','13','DİOR SAVAGE ELİXER HAM ESANS',0.15,'esans'),(32,4,'g armani ','etiket','16','g armani , etiket',1.00,'urun'),(33,4,'g armani ','jelatin','17','g armani , jelatin',1.00,'urun'),(34,4,'g armani ','takm','18','g armani , takım',1.00,'urun'),(35,4,'g armani ','kutu','19','g armani , kutu',1.00,'urun'),(36,4,'g armani ','paket','21','g armani , paket',1.00,'urun'),(38,4,'g armani , Esans','malzeme','15','ALKOL',0.77,'esans'),(39,4,'g armani , Esans','malzeme','14','SU',0.05,'esans'),(40,4,'g armani , Esans','malzeme','20','g armani , ham esans',0.15,'esans'),(41,3,'DİOR SAVAGE ELİXER','paket','10','DİOR SAVAGE ELİXER, paket',0.13,'urun'),(42,4,'g armani ','esans','ES-260401-762','g armani , Esans',0.15,'urun');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
INSERT INTO `urunler` VALUES (2,'DİOR SAVAGE','',950,'adet',8.00,'USD',0.00,'TRY',500,'A DEPEO','A','uretilen',NULL),(3,'DİOR SAVAGE ELİXER','',0,'adet',6.20,'USD',0.00,'TRY',150,'B DEPO','A','uretilen',NULL),(4,'g armani ','',1900,'adet',6.50,'USD',0.00,'TRY',200,'A DEPEO','A','uretilen',NULL),(5,'versace eros','',0,'adet',8.00,'USD',0.18,'USD',100,'A DEPEO','A','hazir_alinan',NULL),(6,'ysl kirke','',625,'adet',6.50,'USD',6.80,'USD',100,'A DEPEO','A','hazir_alinan',NULL),(7,'ddddd','',1250,'adet',8.00,'USD',7.00,'USD',10,'B DEPO','A','hazir_alinan',NULL);
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


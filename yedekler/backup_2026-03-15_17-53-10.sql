-- PHP Native Backup
-- Generated: 2026-03-15 17:53:10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



CREATE TABLE `ayarlar` (
  `ayar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ayar_anahtar` varchar(255) NOT NULL,
  `ayar_deger` varchar(255) NOT NULL,
  PRIMARY KEY (`ayar_id`),
  UNIQUE KEY `ayar_anahtar` (`ayar_anahtar`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ayarlar` VALUES (1,'dolar_kuru','42.8500'),(2,'euro_kuru','50.5070'),(3,'son_otomatik_yedek_tarihi','2026-03-15 00:04:46'),(4,'maintenance_mode','off'),(5,'telegram_bot_token','8410150322:AAFQb9IprNJi0_UTgOB1EGrOLuG7uXlePqw'),(6,'telegram_chat_id','5615404170\n6356317802');







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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cerceve_sozlesmeler` VALUES (1,10,'ADEM ',15,'	dior savage, takım',2.10,'USD',999999,1000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:07:34','',1,'TL',NULL),(2,5,'EBUBEKİR ',10,'	dior savage, kutu',0.40,'USD',99999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:08:07','',1,'TL',NULL),(3,6,'ESENGÜL ',10,'	dior savage, kutu',0.45,'TL',999999,1000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:08:49','',1,'TL',NULL),(4,12,'GÖKHAN ',16,'	dior savage, jelatin',1.00,'TL',999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:09:15','',1,'TL',NULL),(5,9,'KIRMIZIGÜL',15,'	dior savage, takım',2.50,'USD',9999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:09:47','',1,'TL',NULL),(6,14,'KIRMIZIGÜL ALKOL',18,'ALKOL',1.60,'USD',9999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:10:15','',1,'TL',NULL),(7,2,'LUZKIM',14,'	dior savage, ham esans',120.00,'USD',99999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:10:55','',1,'TL',NULL),(8,8,'MAVİ ETİKET',9,'	dior savage, etiket',0.09,'USD',999999,990,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:11:42','',1,'TL',NULL),(9,13,'RAMAZAN ',12,'	dior savage, paket',15.00,'TL',999999,3000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:12:15','',1,'TL',NULL),(10,7,'SARI ETİKET',9,'	dior savage, etiket',2.00,'TL',999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:12:51','',1,'TL',NULL),(11,3,'SELUZ',14,'	dior savage, ham esans',115.00,'USD',99999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:13:22','',1,'TL',NULL),(12,4,'ŞENER',10,'	dior savage, kutu',0.40,'USD',99999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:13:51','',1,'TL',NULL),(13,1,'TEVFİK BEY',14,'	dior savage, ham esans',85.00,'EUR',9999999,15,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:14:19','',1,'TL',NULL),(14,11,'UĞUR TAKIM',15,'	dior savage, takım',2.30,'EUR',99999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:14:55','',1,'TL',NULL),(15,11,'UĞUR TAKIM',6,'chanel blu, takım',2.40,'USD',9999999,300,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:15:22','',1,'TL',NULL),(16,1,'TEVFİK BEY',7,'chanel blu, ham esans',82.00,'EUR',99999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:15:54','',1,'TL',NULL),(17,4,'ŞENER',5,'chanel blu, kutu',0.40,'USD',99999999,1000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:16:41','',1,'TL',NULL),(18,3,'SELUZ',7,'chanel blu, ham esans',120.00,'USD',999999999,45,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:17:12','',1,'TL',NULL),(19,7,'SARI ETİKET',1,'chanel blu, etiket',2.00,'TL',99999999,0,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:22:00','',1,'TL',NULL),(20,13,'RAMAZAN ',8,'chanel blu, paket',16.00,'TL',999999999,2000,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:22:27','',1,'TL',NULL),(21,8,'MAVİ ETİKET',1,'chanel blu, etiket',1.75,'TL',9999999,750,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:23:39','',1,'TL',NULL),(22,12,'GÖKHAN ',2,'chanel blu, jelatin',1.00,'TL',999999999,500,'2026-03-02','2026-04-02','Admin User','2026-03-02 11:24:28','',1,'TL',NULL),(23,10,'ADEM ',24,'155 a, takım',2.10,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:52:03','',1,'TL',NULL),(24,5,'EBUBEKİR ',23,'155 a, kutu',0.50,'USD',9999,0,'2026-03-08','2026-04-12','Admin User','2026-03-08 22:52:36','',1,'TL',NULL),(25,6,'ESENGÜL ',23,'155 a, kutu',0.55,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:53:11','',1,'TL',NULL),(26,12,'GÖKHAN ',20,'155 a, jelatin',1.00,'TL',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:53:41','',1,'TL',NULL),(27,9,'KIRMIZIGÜL',24,'155 a, takım',2.09,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:54:26','',1,'TL',NULL),(28,2,'LUZKIM',25,'155 a, ham esans',100.00,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:54:53','',1,'TL',NULL),(29,8,'MAVİ ETİKET',19,'155 a, etiket',0.90,'TL',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:55:27','',1,'TL',NULL),(30,13,'RAMAZAN ',26,'155 a, paket',12.00,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:56:30','',1,'TL',NULL),(31,7,'SARI ETİKET',19,'155 a, etiket',1.20,'TL',999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:56:59','',1,'TL',NULL),(32,3,'SELUZ',25,'155 a, ham esans',90.00,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:57:31','',1,'TL',NULL),(33,4,'ŞENER',23,'155 a, kutu',0.40,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:58:07','',1,'TL',NULL),(34,1,'TEVFİK BEY',25,'155 a, ham esans',62.00,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:58:38','',1,'TL',NULL),(35,11,'UĞUR TAKIM',24,'155 a, takım',1.30,'USD',9999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 22:59:14','',1,'TL',NULL),(36,15,'MERKES ŞEBEKE',17,'SU',1.00,'TL',99999,0,'2026-03-08','2026-04-08','Admin User','2026-03-08 23:03:46','',1,'TL',NULL);







CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `cerceve_sozlesmeler_gecerlilik` AS select `cs`.`sozlesme_id` AS `sozlesme_id`,`cs`.`tedarikci_id` AS `tedarikci_id`,`cs`.`tedarikci_adi` AS `tedarikci_adi`,`cs`.`malzeme_kodu` AS `malzeme_kodu`,`cs`.`malzeme_ismi` AS `malzeme_ismi`,`cs`.`birim_fiyat` AS `birim_fiyat`,`cs`.`para_birimi` AS `para_birimi`,`cs`.`limit_miktar` AS `limit_miktar`,`cs`.`toplu_odenen_miktar` AS `toplu_odenen_miktar`,`cs`.`baslangic_tarihi` AS `baslangic_tarihi`,`cs`.`bitis_tarihi` AS `bitis_tarihi`,`cs`.`olusturan` AS `olusturan`,`cs`.`olusturulma_tarihi` AS `olusturulma_tarihi`,`cs`.`aciklama` AS `aciklama`,`cs`.`oncelik` AS `oncelik`,coalesce(`shs`.`toplam_kullanilan`,0) AS `toplam_mal_kabul_miktari`,`cs`.`limit_miktar` - coalesce(`shs`.`toplam_kullanilan`,0) AS `kalan_miktar`,case when `cs`.`bitis_tarihi` < curdate() then 0 when coalesce(`shs`.`toplam_kullanilan`,0) >= `cs`.`limit_miktar` then 0 else 1 end AS `gecerli_mi`,case when `cs`.`bitis_tarihi` < curdate() then 'Suresi Dolmus' when coalesce(`shs`.`toplam_kullanilan`,0) >= `cs`.`limit_miktar` then 'Limit Dolmus' else 'Gecerli' end AS `gecerlilik_durumu` from (`cerceve_sozlesmeler` `cs` left join (select `stok_hareketleri_sozlesmeler`.`sozlesme_id` AS `sozlesme_id`,sum(`stok_hareketleri_sozlesmeler`.`kullanilan_miktar`) AS `toplam_kullanilan` from `stok_hareketleri_sozlesmeler` where exists(select 1 from `stok_hareket_kayitlari` where `stok_hareket_kayitlari`.`hareket_id` = `stok_hareketleri_sozlesmeler`.`hareket_id` and `stok_hareket_kayitlari`.`hareket_turu` = 'mal_kabul' limit 1) group by `stok_hareketleri_sozlesmeler`.`sozlesme_id`) `shs` on(`cs`.`sozlesme_id` = `shs`.`sozlesme_id`));






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

INSERT INTO `esans_is_emirleri` VALUES (1,'2026-03-02','Admin User','ES-260302-603','chanel blu, Esans','e 101','chanel blu',100.00,'lt','2026-03-02',1,'2026-03-03','2026-03-02','2026-03-02',' BEŞ LİTRE ESANS EKSİK KALDI','tamamlandi',95.00,5.00),(2,'2026-03-02','Admin User','ES-260302-669','	dior savage, Esans','w 500','dior savage',100.00,'lt','2026-03-02',1,'2026-03-03','2026-03-02','2026-03-02',' ','tamamlandi',100.00,0.00),(3,'2026-03-02','Admin User','ES-260302-603','chanel blu, Esans','e 101','chanel blu',100.00,'lt','2026-03-02',1,'2026-03-03','2026-03-02','2026-03-02',' ','tamamlandi',100.00,0.00),(4,'2026-03-02','Admin User','ES-260302-603','chanel blu, Esans','e 101','chanel blu',1000.00,'lt','2026-03-02',1,'2026-03-03','2026-03-02','2026-03-02',' ','tamamlandi',1000.00,0.00),(5,'2026-03-08','Admin User','ES-260308-259','155 a, Esans','e 155 a','155 a',100.00,'lt','2026-03-08',1,'2026-03-09','2026-03-09','2026-03-09',' ','tamamlandi',100.00,0.00);







CREATE TABLE `esans_is_emri_malzeme_listesi` (
  `is_emri_numarasi` int(11) NOT NULL,
  `malzeme_kodu` int(11) NOT NULL,
  `malzeme_ismi` varchar(255) NOT NULL,
  `malzeme_turu` varchar(100) NOT NULL,
  `miktar` decimal(10,2) NOT NULL,
  `birim` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `esans_is_emri_malzeme_listesi` VALUES (1,17,'SU','malzeme',8.00,'lt'),(1,18,'ALKOL','malzeme',80.00,'lt'),(1,7,'chanel blu, ham esans','malzeme',15.00,'lt'),(2,18,'ALKOL','malzeme',80.00,'lt'),(2,17,'SU','malzeme',8.00,'lt'),(2,14,'	dior savage, ham esans','malzeme',15.00,'lt'),(3,17,'SU','malzeme',8.00,'lt'),(3,18,'ALKOL','malzeme',80.00,'lt'),(3,7,'chanel blu, ham esans','malzeme',15.00,'lt'),(4,17,'SU','malzeme',80.00,'lt'),(4,18,'ALKOL','malzeme',800.00,'lt'),(4,7,'chanel blu, ham esans','malzeme',150.00,'lt'),(5,14,'	dior savage, ham esans','malzeme',15.00,'lt'),(5,18,'ALKOL','malzeme',80.00,'lt'),(5,17,'SU','malzeme',5.00,'lt');







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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `esanslar` VALUES (1,'ES-260302-603','chanel blu, Esans','',1195.00,'lt',1.00,'w 500','dior savage'),(2,'ES-260302-669','	dior savage, Esans','',100.00,'lt',1.00,'e 101','chanel blu'),(3,'ES-260308-259','155 a, Esans','',100.00,'lt',1.00,NULL,NULL);







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

INSERT INTO `gelir_yonetimi` VALUES (1,'2026-03-02 00:00:00',4250.00,'USD','Sipariş Ödemesi','Sipariş No: #2 tahsilatı',1,'Admin User',2,'Nakit',1,'OSMAN ',NULL,'USD',NULL),(2,'2026-03-02 00:00:00',3150.00,'USD','Sipariş Ödemesi','Sipariş No: #1 tahsilatı',1,'Admin User',1,'Çek',2,'HAMZA',NULL,'TL',NULL),(3,'2026-03-02 00:00:00',2430.00,'USD','Sipariş Ödemesi','Sipariş No: #2 tahsilatı',1,'Admin User',2,'Kredi Kartı',1,'OSMAN ',NULL,'USD',NULL),(4,'2026-03-02 00:00:00',2028.00,'USD','Sipariş Ödemesi','Sipariş No: #1 tahsilatı',1,'Admin User',1,'Nakit',2,'HAMZA',NULL,'USD',NULL),(5,'2026-03-08 00:00:00',5000.00,'TL','Sipariş Ödemesi','000',1,'Admin User',NULL,'Havale/EFT',NULL,'OSMAN ',NULL,'USD',NULL),(6,'2026-03-08 00:00:00',32500.00,'TL','Sipariş Ödemesi','Sipariş No: #5 tahsilatı',1,'Admin User',NULL,'Nakit',NULL,'OSMAN ',NULL,'EUR',NULL);







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

INSERT INTO `gider_yonetimi` VALUES (1,'2026-03-02',500.00,'Malzeme Gideri','chanel blu, jelatin için 500 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','GÖKHAN ','TL',NULL),(2,'2026-03-02',1312.50,'Malzeme Gideri','chanel blu, etiket için 750 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','MAVİ ETİKET','TL',NULL),(3,'2026-03-02',32000.00,'Malzeme Gideri','chanel blu, paket için 2000 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','RAMAZAN ','TL',NULL),(4,'2026-03-02',231390.00,'Malzeme Gideri','chanel blu, ham esans için 45 adet ara ödeme (5.400,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','SELUZ','TL',NULL),(5,'2026-03-02',8570.00,'Malzeme Gideri','chanel blu, kutu için 500 adet ara ödeme (200,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','ŞENER','TL',NULL),(6,'2026-03-02',30852.00,'Malzeme Gideri','chanel blu, takım için 300 adet ara ödeme (720,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','UĞUR TAKIM','TL',NULL),(7,'2026-03-02',64396.43,'Malzeme Gideri','	dior savage, ham esans için 15 adet ara ödeme (1.275,00 EUR @ 50,5070)',1,'Admin User',NULL,'Havale/EFT','TEVFİK BEY','TL',NULL),(8,'2026-03-02',3428.00,'Malzeme Gideri','chanel blu, kutu için 200 adet ara ödeme (80,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','ŞENER','USD',NULL),(9,'2026-03-02',5142.00,'Malzeme Gideri','chanel blu, kutu için 300 adet ara ödeme (120,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','ŞENER','TL',NULL),(10,'2026-03-02',450.00,'Malzeme Gideri','	dior savage, kutu için 1000 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','ESENGÜL ','TL',NULL),(11,'2026-03-02',45000.00,'Malzeme Gideri','	dior savage, paket için 3000 adet ara ödeme',1,'Admin User',NULL,'Havale/EFT','RAMAZAN ','TL',NULL),(12,'2026-03-02',3470.85,'Malzeme Gideri','	dior savage, etiket için 900 adet ara ödeme (81,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','MAVİ ETİKET','TL',NULL),(13,'2026-03-02',347.09,'Malzeme Gideri','	dior savage, etiket için 90 adet ara ödeme (8,10 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','MAVİ ETİKET','TL',NULL),(14,'2026-03-02',89985.00,'Malzeme Gideri','	dior savage, takım için 1000 adet ara ödeme (2.100,00 USD @ 42,8500)',1,'Admin User',NULL,'Havale/EFT','ADEM ','TL',NULL);







CREATE TABLE `is_merkezleri` (
  `is_merkezi_id` int(11) NOT NULL AUTO_INCREMENT,
  `isim` varchar(255) NOT NULL,
  `aciklama` text DEFAULT NULL,
  PRIMARY KEY (`is_merkezi_id`),
  UNIQUE KEY `isim` (`isim`),
  UNIQUE KEY `is_merkezi_id` (`is_merkezi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `is_merkezleri` VALUES (1,'ABUBEKİR ÖNEL',''),(2,'AHMET ERSİN','');







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

INSERT INTO `kasa_hareketleri` VALUES (1,'2026-03-02 00:00:00','gelir_girisi','USD',NULL,4250.00,'USD',NULL,182112.50,'gelir_yonetimi',1,'Sipariş No: #2 tahsilatı','Admin User',NULL,'OSMAN ',NULL,'Nakit',NULL,NULL,NULL),(2,'2026-03-02 00:00:00','gelir_girisi','TL',NULL,3150.00,'USD',NULL,134977.50,'gelir_yonetimi',2,'Sipariş No: #1 tahsilatı','Admin User',NULL,'HAMZA',NULL,'Çek',NULL,NULL,NULL),(3,'2026-03-02 00:00:00','gelir_girisi','USD',NULL,2430.00,'USD',NULL,104125.50,'gelir_yonetimi',3,'Sipariş No: #2 tahsilatı','Admin User',NULL,'OSMAN ',NULL,'Kredi Kartı',NULL,NULL,NULL),(4,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,500.00,'TL',NULL,500.00,'cerceve_sozlesmeler',22,'chanel blu, jelatin için 500 adet ara ödeme','Admin User','GÖKHAN ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(5,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,1312.50,'TL',NULL,1312.50,'cerceve_sozlesmeler',21,'chanel blu, etiket için 750 adet ara ödeme','Admin User','MAVİ ETİKET',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(6,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,32000.00,'TL',NULL,32000.00,'cerceve_sozlesmeler',20,'chanel blu, paket için 2000 adet ara ödeme','Admin User','RAMAZAN ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(7,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,231390.00,'TL',NULL,231390.00,'cerceve_sozlesmeler',18,'chanel blu, ham esans için 45 adet ara ödeme (5.400,00 USD @ 42,8500)','Admin User','SELUZ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(8,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,8570.00,'TL',NULL,8570.00,'cerceve_sozlesmeler',17,'chanel blu, kutu için 500 adet ara ödeme (200,00 USD @ 42,8500)','Admin User','ŞENER',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(9,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,30852.00,'TL',NULL,30852.00,'cerceve_sozlesmeler',15,'chanel blu, takım için 300 adet ara ödeme (720,00 USD @ 42,8500)','Admin User','UĞUR TAKIM',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(10,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,64396.43,'TL',NULL,64396.43,'cerceve_sozlesmeler',13,'	dior savage, ham esans için 15 adet ara ödeme (1.275,00 EUR @ 50,5070)','Admin User','TEVFİK BEY',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(11,'2026-03-02 00:00:00','gider_cikisi','USD',NULL,80.00,'USD',NULL,3428.00,'cerceve_sozlesmeler',17,'chanel blu, kutu için 200 adet ara ödeme (80,00 USD @ 42,8500)','Admin User','ŞENER',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(12,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,5142.00,'TL',NULL,5142.00,'cerceve_sozlesmeler',17,'chanel blu, kutu için 300 adet ara ödeme (120,00 USD @ 42,8500)','Admin User','ŞENER',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(13,'2026-03-02 00:00:00','gelir_girisi','USD',NULL,2028.00,'USD',NULL,86899.80,'gelir_yonetimi',4,'Sipariş No: #1 tahsilatı','Admin User',NULL,'HAMZA',NULL,'Nakit',NULL,NULL,NULL),(14,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,450.00,'TL',NULL,450.00,'cerceve_sozlesmeler',3,'	dior savage, kutu için 1000 adet ara ödeme','Admin User','ESENGÜL ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(15,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,45000.00,'TL',NULL,45000.00,'cerceve_sozlesmeler',9,'	dior savage, paket için 3000 adet ara ödeme','Admin User','RAMAZAN ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(16,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,3470.85,'TL',NULL,3470.85,'cerceve_sozlesmeler',8,'	dior savage, etiket için 900 adet ara ödeme (81,00 USD @ 42,8500)','Admin User','MAVİ ETİKET',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(17,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,347.09,'TL',NULL,347.09,'cerceve_sozlesmeler',8,'	dior savage, etiket için 90 adet ara ödeme (8,10 USD @ 42,8500)','Admin User','MAVİ ETİKET',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(18,'2026-03-02 00:00:00','gider_cikisi','TL',NULL,89985.00,'TL',NULL,89985.00,'cerceve_sozlesmeler',1,'	dior savage, takım için 1000 adet ara ödeme (2.100,00 USD @ 42,8500)','Admin User','ADEM ',NULL,NULL,'Havale/EFT',NULL,NULL,NULL),(19,'2026-03-08 00:00:00','gelir_girisi','USD',NULL,5000.00,'TL',NULL,5000.00,'gelir_yonetimi',5,'000','Admin User',NULL,'OSMAN ',NULL,'Havale/EFT',NULL,NULL,NULL),(20,'2026-03-08 00:00:00','gelir_girisi','EUR',NULL,32500.00,'TL',NULL,32500.00,'gelir_yonetimi',6,'Sipariş No: #5 tahsilatı','Admin User',NULL,'OSMAN ',NULL,'Nakit',NULL,NULL,NULL);







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






CREATE TABLE `log_tablosu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tarih` datetime DEFAULT current_timestamp(),
  `kullanici_adi` varchar(255) NOT NULL,
  `log_metni` text NOT NULL,
  `islem_turu` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `log_tablosu` VALUES (1,'2026-03-02 10:42:26','Admin User','cerceve_sozlesmeler, esans_is_emirleri, esans_is_emri_malzeme_listesi, esanslar, gelir_yonetimi, gider_yonetimi, is_merkezleri, kasa_hareketleri, log_tablosu, lokasyonlar, malzemeler, montaj_is_emirleri, montaj_is_emri_malzeme_listesi, musteriler, satinalma_siparis_kalemleri, satinalma_siparisler, siparis_kalemleri, siparisler, sirket_kasasi, stok_hareket_kayitlari, stok_hareketleri_sozlesmeler, tanklar, tedarikciler, urun_agaci, urun_fotograflari, urunler tabloları temizlendi','DELETE','2026-03-02 07:42:26'),(2,'2026-03-02 10:43:35','Admin User','depo esans deposuna a rafı eklendi','CREATE','2026-03-02 07:43:35'),(3,'2026-03-02 10:43:46','Admin User','depo a deposuna a rafı eklendi','CREATE','2026-03-02 07:43:46'),(4,'2026-03-02 10:44:04','Admin User','depo b deposuna a rafı eklendi','CREATE','2026-03-02 07:44:04'),(5,'2026-03-02 10:44:20','Admin User','depo c deposuna depo c rafı eklendi','CREATE','2026-03-02 07:44:20'),(6,'2026-03-02 10:44:39','Admin User','depo kutu deposuna a rafı eklendi','CREATE','2026-03-02 07:44:39'),(7,'2026-03-02 10:45:01','Admin User','depo takım deposuna a rafı eklendi','CREATE','2026-03-02 07:45:01'),(8,'2026-03-02 10:45:44','Admin User','dior savage adlı tank sisteme eklendi','CREATE','2026-03-02 07:45:44'),(9,'2026-03-02 10:46:08','Admin User','chanel blu adlı tank sisteme eklendi','CREATE','2026-03-02 07:46:08'),(10,'2026-03-02 10:46:31','Admin User','ABUBEKİR ÖNEL iş merkezi eklendi','CREATE','2026-03-02 07:46:31'),(11,'2026-03-02 10:46:37','Admin User','AHMET ERSİN iş merkezi eklendi','CREATE','2026-03-02 07:46:37'),(12,'2026-03-02 10:47:59','Admin User','chanel blu ürünü sisteme eklendi','CREATE','2026-03-02 07:47:59'),(13,'2026-03-02 10:48:00','Admin User','Otomatik esans oluşturuldu: chanel blu, Esans (Tank: w 500)','CREATE','2026-03-02 07:48:00'),(14,'2026-03-02 10:49:11','Admin User','	dior savage ürünü sisteme eklendi','CREATE','2026-03-02 07:49:11'),(15,'2026-03-02 10:49:11','Admin User','Otomatik esans oluşturuldu: 	dior savage, Esans (Tank: e 101)','CREATE','2026-03-02 07:49:11'),(16,'2026-03-02 10:52:15','Admin User','chanel blu ürün ağacından chanel blu, su bileşeni silindi','DELETE','2026-03-02 07:52:15'),(17,'2026-03-02 10:52:23','Admin User','chanel blu ürün ağacından chanel blu, fiksator bileşeni silindi','DELETE','2026-03-02 07:52:23'),(18,'2026-03-02 10:54:38','Admin User','SU malzemesi sisteme eklendi','CREATE','2026-03-02 07:54:38'),(19,'2026-03-02 10:55:18','Admin User','ALKOL malzemesi sisteme eklendi','CREATE','2026-03-02 07:55:18'),(20,'2026-03-02 10:55:52','Admin User','	dior savage, Esans ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-03-02 07:55:52'),(21,'2026-03-02 10:56:14','Admin User','	dior savage, Esans ürün ağacına SU bileşeni eklendi','CREATE','2026-03-02 07:56:14'),(22,'2026-03-02 10:57:54','Admin User','	dior savage, Esans ürün ağacına 	dior savage, ham esans bileşeni eklendi','CREATE','2026-03-02 07:57:54'),(23,'2026-03-02 10:58:27','Admin User','chanel blu, Esans ürün ağacına SU bileşeni eklendi','CREATE','2026-03-02 07:58:27'),(24,'2026-03-02 10:58:54','Admin User','chanel blu, Esans ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-03-02 07:58:54'),(25,'2026-03-02 10:59:11','Admin User','chanel blu, Esans ürün ağacına chanel blu, ham esans bileşeni eklendi','CREATE','2026-03-02 07:59:11'),(26,'2026-03-02 10:59:58','Admin User','TEVFİK BEY tedarikçisi sisteme eklendi','CREATE','2026-03-02 07:59:58'),(27,'2026-03-02 11:00:19','Admin User','LUZKIM tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:00:19'),(28,'2026-03-02 11:00:39','Admin User','SELUZ tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:00:39'),(29,'2026-03-02 11:00:58','Admin User','ŞENER tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:00:58'),(30,'2026-03-02 11:01:12','Admin User','EBUBEKİR  tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:01:12'),(31,'2026-03-02 11:01:49','Admin User','ESENGÜL  tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:01:49'),(32,'2026-03-02 11:02:12','Admin User','SARI ETİKET tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:02:12'),(33,'2026-03-02 11:02:27','Admin User','MAVİ ETİKET tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:02:27'),(34,'2026-03-02 11:02:41','Admin User','KIRMIZIGÜL tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:02:41'),(35,'2026-03-02 11:02:52','Admin User','ADEM  tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:02:52'),(36,'2026-03-02 11:03:10','Admin User','UĞUR TAKIM tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:03:10'),(37,'2026-03-02 11:03:29','Admin User','GÖKHAN  tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:03:29'),(38,'2026-03-02 11:03:44','Admin User','RAMAZAN  tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:03:44'),(39,'2026-03-02 11:04:26','Admin User','KIRMIZIGÜL ALKOL tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:04:26'),(40,'2026-03-02 11:04:53','Admin User','UĞUR TAKIM tedarikçisi UĞUR TAKIM olarak güncellendi','UPDATE','2026-03-02 08:04:53'),(41,'2026-03-02 11:04:54','Admin User','UĞUR TAKIM tedarikçisi UĞUR TAKIM olarak güncellendi','UPDATE','2026-03-02 08:04:54'),(42,'2026-03-02 11:07:34','Admin User','ADEM  tedarikçisine 	dior savage, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:07:34'),(43,'2026-03-02 11:08:07','Admin User','EBUBEKİR  tedarikçisine 	dior savage, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:08:07'),(44,'2026-03-02 11:08:50','Admin User','ESENGÜL  tedarikçisine 	dior savage, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:08:50'),(45,'2026-03-02 11:09:15','Admin User','GÖKHAN  tedarikçisine 	dior savage, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:09:15'),(46,'2026-03-02 11:09:47','Admin User','KIRMIZIGÜL tedarikçisine 	dior savage, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:09:47'),(47,'2026-03-02 11:10:15','Admin User','KIRMIZIGÜL ALKOL tedarikçisine ALKOL malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:10:15'),(48,'2026-03-02 11:10:55','Admin User','LUZKIM tedarikçisine 	dior savage, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:10:55'),(49,'2026-03-02 11:11:42','Admin User','MAVİ ETİKET tedarikçisine 	dior savage, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:11:42'),(50,'2026-03-02 11:12:15','Admin User','RAMAZAN  tedarikçisine 	dior savage, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:12:15'),(51,'2026-03-02 11:12:51','Admin User','SARI ETİKET tedarikçisine 	dior savage, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:12:51'),(52,'2026-03-02 11:13:22','Admin User','SELUZ tedarikçisine 	dior savage, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:13:22'),(53,'2026-03-02 11:13:51','Admin User','ŞENER tedarikçisine 	dior savage, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:13:51'),(54,'2026-03-02 11:14:19','Admin User','TEVFİK BEY tedarikçisine 	dior savage, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:14:19'),(55,'2026-03-02 11:14:55','Admin User','UĞUR TAKIM tedarikçisine 	dior savage, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:14:55'),(56,'2026-03-02 11:15:22','Admin User','UĞUR TAKIM tedarikçisine chanel blu, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:15:22'),(57,'2026-03-02 11:15:54','Admin User','TEVFİK BEY tedarikçisine chanel blu, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:15:54'),(58,'2026-03-02 11:16:41','Admin User','ŞENER tedarikçisine chanel blu, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:16:41'),(59,'2026-03-02 11:17:12','Admin User','SELUZ tedarikçisine chanel blu, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:17:12'),(60,'2026-03-02 11:18:39','Admin User','chanel blu, fiksator malzemesi sistemden silindi','DELETE','2026-03-02 08:18:39'),(61,'2026-03-02 11:18:43','Admin User','	dior savage, fiksator malzemesi sistemden silindi','DELETE','2026-03-02 08:18:43'),(62,'2026-03-02 11:19:35','Admin User','MERKES ŞEBEKE tedarikçisi sisteme eklendi','CREATE','2026-03-02 08:19:35'),(63,'2026-03-02 11:22:00','Admin User','SARI ETİKET tedarikçisine chanel blu, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:22:00'),(64,'2026-03-02 11:22:27','Admin User','RAMAZAN  tedarikçisine chanel blu, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:22:27'),(65,'2026-03-02 11:23:39','Admin User','MAVİ ETİKET tedarikçisine chanel blu, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:23:39'),(66,'2026-03-02 11:24:28','Admin User','GÖKHAN  tedarikçisine chanel blu, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-02 08:24:28'),(67,'2026-03-02 11:27:10','Admin User','	dior savage ürün ağacındaki 	dior savage, su bileşeni 	dior savage, su olarak güncellendi','UPDATE','2026-03-02 08:27:10'),(68,'2026-03-02 11:31:48','Admin User','	dior savage ürün ağacındaki 	dior savage, su bileşeni 	dior savage, su olarak güncellendi','UPDATE','2026-03-02 08:31:48'),(69,'2026-03-02 11:34:33','Admin User','	dior savage ürün ağacından 	dior savage, su bileşeni silindi','DELETE','2026-03-02 08:34:33'),(70,'2026-03-02 11:37:19','Admin User','chanel blu ürün ağacındaki chanel blu, Esans bileşeni chanel blu, Esans olarak güncellendi','UPDATE','2026-03-02 08:37:19'),(71,'2026-03-02 11:37:45','Admin User','	dior savage ürün ağacındaki 	dior savage, Esans bileşeni 	dior savage, Esans olarak güncellendi','UPDATE','2026-03-02 08:37:45'),(72,'2026-03-02 11:38:09','Admin User','	dior savage ürün ağacındaki 	dior savage, paket bileşeni 	dior savage, paket olarak güncellendi','UPDATE','2026-03-02 08:38:09'),(73,'2026-03-02 11:38:20','Admin User','chanel blu ürün ağacındaki chanel blu, paket bileşeni chanel blu, paket olarak güncellendi','UPDATE','2026-03-02 08:38:20'),(74,'2026-03-02 11:39:49','Admin User','chanel blu ürün ağacındaki chanel blu, paket bileşeni chanel blu, paket olarak güncellendi','UPDATE','2026-03-02 08:39:49'),(75,'2026-03-02 11:40:00','Admin User','	dior savage ürün ağacındaki 	dior savage, paket bileşeni 	dior savage, paket olarak güncellendi','UPDATE','2026-03-02 08:40:00'),(76,'2026-03-02 11:42:32','Admin User','ADEM  tedarikçisine PO-2026-00001 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-02 08:42:32'),(77,'2026-03-02 11:42:37','Admin User','GÖKHAN  tedarikçisine PO-2026-00002 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-02 08:42:37'),(78,'2026-03-02 11:42:43','Admin User','RAMAZAN  tedarikçisine PO-2026-00003 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-02 08:42:43'),(79,'2026-03-02 11:42:48','Admin User','UĞUR TAKIM tedarikçisine PO-2026-00004 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-02 08:42:48'),(80,'2026-03-02 11:42:53','Admin User','ESENGÜL  tedarikçisine PO-2026-00005 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-02 08:42:53'),(81,'2026-03-02 11:43:02','Admin User','MAVİ ETİKET tedarikçisine PO-2026-00006 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-02 08:43:02'),(82,'2026-03-02 11:43:29','Admin User','ŞENER tedarikçisine PO-2026-00007 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-02 08:43:29'),(83,'2026-03-02 11:46:03','Admin User','Satınalma siparişi #7 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-02 08:46:03'),(84,'2026-03-02 11:46:44','Admin User','Satınalma siparişi #6 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-02 08:46:44'),(85,'2026-03-02 11:47:25','Admin User','Satınalma siparişi #5 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-02 08:47:25'),(86,'2026-03-02 11:47:41','Admin User','Satınalma siparişi #4 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-02 08:47:41'),(87,'2026-03-02 11:47:57','Admin User','Satınalma siparişi #3 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-02 08:47:57'),(88,'2026-03-02 11:48:08','Admin User','Satınalma siparişi #2 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-02 08:48:08'),(89,'2026-03-02 11:48:15','Admin User','Satınalma siparişi #1 durumu \'taslak\' olarak güncellendi','UPDATE','2026-03-02 08:48:15'),(90,'2026-03-02 11:48:20','Admin User','Satınalma siparişi #1 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-02 08:48:20'),(91,'2026-03-02 11:49:05','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 08:49:05'),(92,'2026-03-02 12:28:32','Admin User','chanel blu, ham esans malzemesi chanel blu, ham esans olarak güncellendi','UPDATE','2026-03-02 09:28:32'),(93,'2026-03-02 12:29:26','Admin User','chanel blu, Esans esansı için iş emri oluşturuldu','CREATE','2026-03-02 09:29:26'),(94,'2026-03-02 12:29:41','Admin User','	dior savage, Esans esansı için iş emri oluşturuldu','CREATE','2026-03-02 09:29:41'),(95,'2026-03-02 12:34:03','Admin User','chanel blu ürün ağacındaki chanel blu, Esans bileşeni chanel blu, Esans olarak güncellendi','UPDATE','2026-03-02 09:34:03'),(96,'2026-03-02 12:35:44','Admin User','	dior savage, Esans ürün ağacındaki 	dior savage, ham esans bileşeni 	dior savage, ham esans olarak güncellendi','UPDATE','2026-03-02 09:35:44'),(97,'2026-03-02 12:36:24','Admin User','	dior savage, Esans ürün ağacındaki 	dior savage, ham esans bileşeni 	dior savage, ham esans olarak güncellendi','UPDATE','2026-03-02 09:36:24'),(98,'2026-03-02 12:37:46','Admin User','	dior savage ürünü için montaj iş emri oluşturuldu','CREATE','2026-03-02 09:37:46'),(99,'2026-03-02 12:44:10','Admin User','RAMAZAN  tedarikçisine PO-2026-00008 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-02 09:44:10'),(100,'2026-03-02 12:44:33','Admin User','Satınalma siparişi #8 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-02 09:44:33'),(101,'2026-03-02 12:49:58','Admin User','	dior savage, ham esans malzemesi 	dior savage, ham esans olarak güncellendi','UPDATE','2026-03-02 09:49:58'),(102,'2026-03-02 12:57:36','Admin User','chanel blu, Esans esansı için iş emri oluşturuldu','CREATE','2026-03-02 09:57:36'),(103,'2026-03-02 13:01:10','Admin User','chanel blu, Esans esansı için iş emri oluşturuldu','CREATE','2026-03-02 10:01:10'),(104,'2026-03-02 13:08:58','Admin User','	dior savage, Esans ürün ağacındaki 	dior savage, ham esans bileşeni 	dior savage, ham esans olarak güncellendi','UPDATE','2026-03-02 10:08:58'),(105,'2026-03-02 13:09:22','Admin User','chanel blu, Esans ürün ağacındaki chanel blu, ham esans bileşeni chanel blu, ham esans olarak güncellendi','UPDATE','2026-03-02 10:09:22'),(106,'2026-03-02 13:10:40','Admin User','chanel blu ürün ağacındaki chanel blu, Esans bileşeni chanel blu, Esans olarak güncellendi','UPDATE','2026-03-02 10:10:40'),(107,'2026-03-02 13:13:42','Admin User','chanel blu ürünü için montaj iş emri oluşturuldu','CREATE','2026-03-02 10:13:42'),(108,'2026-03-02 13:13:43','Admin User','chanel blu ürünü için montaj iş emri oluşturuldu','CREATE','2026-03-02 10:13:43'),(109,'2026-03-02 13:13:44','Admin User','chanel blu ürünü için montaj iş emri oluşturuldu','CREATE','2026-03-02 10:13:44'),(110,'2026-03-02 13:15:39','Admin User','OSMAN  müşterisi sisteme eklendi','CREATE','2026-03-02 10:15:39'),(111,'2026-03-02 13:16:07','Admin User','HAMZA müşterisi sisteme eklendi','CREATE','2026-03-02 10:16:07'),(112,'2026-03-02 13:16:42','Admin User','OSMAN  müşterisi OSMAN  olarak güncellendi','UPDATE','2026-03-02 10:16:42'),(113,'2026-03-02 13:20:23','Admin User','	dior savage ürünü 	dior savage olarak güncellendi','UPDATE','2026-03-02 10:20:23'),(114,'2026-03-02 13:21:24','Admin User','HAMZA müşterisi için yeni sipariş oluşturuldu (ID: 1)','CREATE','2026-03-02 10:21:24'),(115,'2026-03-02 13:21:37','Admin User','HAMZA müşterisine ait 1 nolu siparişin yeni durumu: Onaylandı','UPDATE','2026-03-02 10:21:37'),(116,'2026-03-02 13:21:46','Admin User','HAMZA müşterisine ait 1 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2026-03-02 10:21:46'),(117,'2026-03-02 13:22:22','Admin User','OSMAN  müşterisi için yeni sipariş oluşturuldu (ID: 2)','CREATE','2026-03-02 10:22:22'),(118,'2026-03-02 13:22:30','Admin User','OSMAN  müşterisine ait 2 nolu siparişin yeni durumu: Onaylandı','UPDATE','2026-03-02 10:22:30'),(119,'2026-03-02 13:22:40','Admin User','OSMAN  müşterisine ait 2 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2026-03-02 10:22:40'),(120,'2026-03-02 13:24:03','Admin User','Sipariş Ödemesi kategorisinde 4250 USD tutarında gelir eklendi','CREATE','2026-03-02 10:24:03'),(121,'2026-03-02 13:24:27','Admin User','Sipariş Ödemesi kategorisinde 3150 USD tutarında gelir eklendi','CREATE','2026-03-02 10:24:27'),(122,'2026-03-02 13:24:47','Admin User','Sipariş Ödemesi kategorisinde 2430 USD tutarında gelir eklendi','CREATE','2026-03-02 10:24:47'),(123,'2026-03-02 13:36:11','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-02 10:36:11'),(124,'2026-03-02 13:46:35','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 10:46:35'),(125,'2026-03-02 13:59:02','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 10:59:02'),(126,'2026-03-02 14:01:32','Admin User','Sipariş Ödemesi kategorisinde 2028 USD tutarında gelir eklendi','CREATE','2026-03-02 11:01:32'),(127,'2026-03-02 16:13:57','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 13:13:57'),(128,'2026-03-02 16:23:10','Admin User','	dior savage ürünü için montaj iş emri oluşturuldu','CREATE','2026-03-02 13:23:10'),(129,'2026-03-02 16:25:21','Admin User','OSMAN  müşterisi için yeni sipariş oluşturuldu (ID: 3)','CREATE','2026-03-02 13:25:21'),(130,'2026-03-02 16:26:03','Admin User','OSMAN  müşterisine ait 3 nolu siparişin yeni durumu: Onaylandı','UPDATE','2026-03-02 13:26:03'),(131,'2026-03-02 16:30:25','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-02 13:30:25'),(132,'2026-03-02 17:10:19','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 14:10:19'),(133,'2026-03-02 17:11:04','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-02 14:11:04'),(134,'2026-03-02 21:06:04','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 18:06:04'),(135,'2026-03-02 21:09:22','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 18:09:22'),(136,'2026-03-02 21:28:23','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-02 18:28:23'),(137,'2026-03-02 21:28:36','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 18:28:36'),(138,'2026-03-02 22:02:13','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-02 19:02:13'),(139,'2026-03-02 22:04:26','Admin User','HAMZA müşterisi için yeni sipariş oluşturuldu (ID: 4)','CREATE','2026-03-02 19:04:26'),(140,'2026-03-04 09:12:33','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-04 06:12:33'),(141,'2026-03-04 09:13:29','Admin User','OSMAN  müşterisine ait 3 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2026-03-04 06:13:29'),(142,'2026-03-04 09:26:52','Admin User','HAMZA müşterisine ait 4 nolu siparişin yeni durumu: Onaylandı','UPDATE','2026-03-04 06:26:52'),(143,'2026-03-04 09:27:38','Admin User','HAMZA müşterisine ait 4 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2026-03-04 06:27:38'),(144,'2026-03-04 09:32:33','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-04 06:32:33'),(145,'2026-03-07 00:02:38','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-06 21:02:38'),(146,'2026-03-08 22:44:08','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-08 19:44:08'),(147,'2026-03-08 22:47:55','Admin User','Satınalma siparişi #8 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-03-08 19:47:55'),(148,'2026-03-08 22:48:20','Admin User','Satınalma siparişi #7 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-03-08 19:48:20'),(149,'2026-03-08 22:48:34','Admin User','Satınalma siparişi #6 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-03-08 19:48:34'),(150,'2026-03-08 22:48:51','Admin User','Satınalma siparişi #1 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-03-08 19:48:51'),(151,'2026-03-08 22:48:57','Admin User','Satınalma siparişi #5 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-03-08 19:48:57'),(152,'2026-03-08 22:49:03','Admin User','Satınalma siparişi #2 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-03-08 19:49:03'),(153,'2026-03-08 22:49:15','Admin User','Satınalma siparişi #3 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-03-08 19:49:15'),(154,'2026-03-08 22:49:28','Admin User','Satınalma siparişi #4 durumu \'kapatildi\' olarak güncellendi','UPDATE','2026-03-08 19:49:28'),(155,'2026-03-08 22:50:17','Admin User','155 a ürünü sisteme eklendi','CREATE','2026-03-08 19:50:17'),(156,'2026-03-08 22:50:18','Admin User','Otomatik esans oluşturuldu: 155 a, Esans (Tank: )','CREATE','2026-03-08 19:50:18'),(157,'2026-03-08 22:52:03','Admin User','ADEM  tedarikçisine 155 a, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:52:03'),(158,'2026-03-08 22:52:36','Admin User','EBUBEKİR  tedarikçisine 155 a, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:52:36'),(159,'2026-03-08 22:53:11','Admin User','ESENGÜL  tedarikçisine 155 a, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:53:11'),(160,'2026-03-08 22:53:41','Admin User','GÖKHAN  tedarikçisine 155 a, jelatin malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:53:41'),(161,'2026-03-08 22:54:26','Admin User','KIRMIZIGÜL tedarikçisine 155 a, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:54:26'),(162,'2026-03-08 22:54:53','Admin User','LUZKIM tedarikçisine 155 a, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:54:53'),(163,'2026-03-08 22:55:27','Admin User','MAVİ ETİKET tedarikçisine 155 a, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:55:27'),(164,'2026-03-08 22:56:30','Admin User','RAMAZAN  tedarikçisine 155 a, paket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:56:30'),(165,'2026-03-08 22:56:59','Admin User','SARI ETİKET tedarikçisine 155 a, etiket malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:56:59'),(166,'2026-03-08 22:57:31','Admin User','SELUZ tedarikçisine 155 a, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:57:31'),(167,'2026-03-08 22:58:07','Admin User','ŞENER tedarikçisine 155 a, kutu malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:58:07'),(168,'2026-03-08 22:58:38','Admin User','TEVFİK BEY tedarikçisine 155 a, ham esans malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:58:38'),(169,'2026-03-08 22:59:14','Admin User','UĞUR TAKIM tedarikçisine 155 a, takım malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 19:59:14'),(170,'2026-03-08 22:59:55','Admin User','155 a, Esans ürün ağacına 	dior savage, ham esans bileşeni eklendi','CREATE','2026-03-08 19:59:55'),(171,'2026-03-08 23:00:10','Admin User','155 a, Esans ürün ağacına ALKOL bileşeni eklendi','CREATE','2026-03-08 20:00:10'),(172,'2026-03-08 23:00:31','Admin User','155 a, Esans ürün ağacına SU bileşeni eklendi','CREATE','2026-03-08 20:00:31'),(173,'2026-03-08 23:03:46','Admin User','MERKES ŞEBEKE tedarikçisine SU malzemesi için çerçeve sözleşme eklendi','CREATE','2026-03-08 20:03:46'),(174,'2026-03-08 23:06:00','Admin User','155 a, fiksator malzemesi sistemden silindi','DELETE','2026-03-08 20:06:00'),(175,'2026-03-08 23:07:06','Admin User','155 a, su malzemesi sistemden silindi','DELETE','2026-03-08 20:07:06'),(176,'2026-03-08 23:07:13','Admin User','	dior savage, su malzemesi sistemden silindi','DELETE','2026-03-08 20:07:13'),(177,'2026-03-08 23:07:17','Admin User','chanel blu, su malzemesi sistemden silindi','DELETE','2026-03-08 20:07:17'),(178,'2026-03-08 23:07:59','Admin User','GÖKHAN  tedarikçisine PO-2026-00009 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-08 20:07:59'),(179,'2026-03-09 00:12:44','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-08 21:12:44'),(180,'2026-03-09 00:13:25','Admin User','PO-2026-00009 no\'lu GÖKHAN  siparişi silindi','DELETE','2026-03-08 21:13:25'),(181,'2026-03-09 00:23:26','Admin User','GÖKHAN  tedarikçisine PO-2026-00009 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-08 21:23:26'),(182,'2026-03-09 00:23:30','Admin User','RAMAZAN  tedarikçisine PO-2026-00010 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-08 21:23:30'),(183,'2026-03-09 00:23:35','Admin User','SELUZ tedarikçisine PO-2026-00011 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-08 21:23:35'),(184,'2026-03-09 00:23:40','Admin User','UĞUR TAKIM tedarikçisine PO-2026-00012 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-08 21:23:40'),(185,'2026-03-09 00:23:48','Admin User','MAVİ ETİKET tedarikçisine PO-2026-00013 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-08 21:23:48'),(186,'2026-03-09 00:23:53','Admin User','SARI ETİKET tedarikçisine PO-2026-00014 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-08 21:23:53'),(187,'2026-03-09 00:24:02','Admin User','ŞENER tedarikçisine PO-2026-00015 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-08 21:24:02'),(188,'2026-03-09 00:43:32','Admin User','155 a adlı tank sisteme eklendi','CREATE','2026-03-08 21:43:32'),(189,'2026-03-09 00:44:44','Admin User','155 a, Esans esansı için iş emri oluşturuldu','CREATE','2026-03-08 21:44:44'),(190,'2026-03-09 00:51:36','Admin User','155 a, Esans ürün ağacındaki 	dior savage, ham esans bileşeni 155 a, ham esans olarak güncellendi','UPDATE','2026-03-08 21:51:36'),(191,'2026-03-09 00:51:53','Admin User','155 a adlı tank 155 a olarak güncellendi','UPDATE','2026-03-08 21:51:53'),(192,'2026-03-09 00:53:04','Admin User','155 a, Esans ürün ağacındaki 155 a, ham esans bileşeni 155 a, ham esans olarak güncellendi','UPDATE','2026-03-08 21:53:04'),(193,'2026-03-09 00:54:26','Admin User','155 a, ham esans malzemesi 155 a, ham esans olarak güncellendi','UPDATE','2026-03-08 21:54:26'),(194,'2026-03-09 00:55:04','Admin User','155 a ürünü için montaj iş emri oluşturuldu','CREATE','2026-03-08 21:55:04'),(195,'2026-03-09 00:56:34','Admin User','OSMAN  müşterisi için yeni sipariş oluşturuldu (ID: 5)','CREATE','2026-03-08 21:56:34'),(196,'2026-03-09 00:56:53','Admin User','Sipariş kalemi güncellendi: 155 a ürünü 155 a olarak değiştirildi (ID: 5)','UPDATE','2026-03-08 21:56:53'),(197,'2026-03-09 00:57:06','Admin User','OSMAN  müşterisine ait 5 nolu siparişin yeni durumu: Onaylandı','UPDATE','2026-03-08 21:57:06'),(198,'2026-03-09 00:57:14','Admin User','OSMAN  müşterisine ait 5 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2026-03-08 21:57:14'),(199,'2026-03-09 01:00:31','Admin User','Sipariş Ödemesi kategorisinde 5000 TL tutarında gelir eklendi','CREATE','2026-03-08 22:00:31'),(200,'2026-03-09 01:02:01','Admin User','Sipariş Ödemesi kategorisinde 32500 TL tutarında gelir eklendi','CREATE','2026-03-08 22:02:01'),(201,'2026-03-09 01:02:57','Admin User','Sipariş Ödemesi kategorisindeki 32500.00 TL tutarlı gelir güncellendi','UPDATE','2026-03-08 22:02:57'),(202,'2026-03-09 01:18:25','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-08 22:18:25'),(203,'2026-03-11 13:47:07','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-11 10:47:07'),(204,'2026-03-11 14:02:59','Admin User','HAMZA müşterisi için yeni sipariş oluşturuldu (ID: 6)','CREATE','2026-03-11 11:02:59'),(205,'2026-03-11 14:03:54','Admin User','HAMZA müşterisine ait 6 nolu siparişin yeni durumu: Onaylandı','UPDATE','2026-03-11 11:03:54'),(206,'2026-03-11 14:04:38','Admin User','HAMZA müşterisine ait 6 nolu siparişin yeni durumu: Tamamlandı','UPDATE','2026-03-11 11:04:38'),(207,'2026-03-11 14:14:53','Admin User','GÖKHAN  tedarikçisine PO-2026-00016 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-11 11:14:53'),(208,'2026-03-11 14:14:59','Admin User','RAMAZAN  tedarikçisine PO-2026-00017 no\'lu satınalma siparişi oluşturuldu','CREATE','2026-03-11 11:14:59'),(209,'2026-03-11 14:15:36','Admin User','Satınalma siparişi #18 durumu \'gonderildi\' olarak güncellendi','UPDATE','2026-03-11 11:15:36'),(210,'2026-03-11 14:16:31','Admin User','personel oturumu kapattı (ID: 1)','Çıkış Yapıldı','2026-03-11 11:16:31'),(211,'2026-03-11 17:13:37','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-11 14:13:37'),(212,'2026-03-11 18:38:02','unknown','unknown oturumu kapattı (ID: unknown)','Çıkış Yapıldı','2026-03-11 15:38:02'),(213,'2026-03-14 18:29:18','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-14 15:29:18'),(214,'2026-03-14 21:28:27','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-14 18:28:27'),(215,'2026-03-14 21:29:09','Admin User','Deneme Eko ürünü sisteme eklendi','CREATE','2026-03-14 18:29:09'),(216,'2026-03-15 17:54:22','Admin User','Personel giriş yaptı (E-posta/Telefon: admin@parfum.com)','Giriş Yapıldı','2026-03-15 14:54:22');







CREATE TABLE `lokasyonlar` (
  `lokasyon_id` int(11) NOT NULL AUTO_INCREMENT,
  `depo_ismi` varchar(255) NOT NULL,
  `raf` varchar(100) NOT NULL,
  PRIMARY KEY (`lokasyon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `lokasyonlar` VALUES (1,'depo esans','a'),(2,'depo a','a'),(3,'depo b','a'),(4,'depo c','depo c'),(5,'depo kutu','a'),(6,'depo takım','a');







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






CREATE TABLE `malzeme_maliyetleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `malzeme_kodu` int(11) NOT NULL,
  `malzeme_ismi` varchar(255) NOT NULL,
  `agirlikli_ortalama_maliyet` decimal(10,2) NOT NULL,
  `son_hesaplama_tarihi` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `malzeme_kodu` (`malzeme_kodu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






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






CREATE TABLE `malzeme_turleri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `sira` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `malzeme_turleri` VALUES (3,'kutu','kutu',1,'2025-12-25 08:21:49'),(4,'etiket','etiket',2,'2025-12-25 08:22:01'),(5,'takm','takım',3,'2025-12-25 08:22:10'),(6,'ham_esans','ham esans',4,'2025-12-25 08:22:38'),(7,'alkol','alkol',5,'2025-12-25 08:22:52'),(8,'paket','paket',6,'2025-12-25 08:23:11'),(9,'jelatin','jelatin',7,'2025-12-25 08:23:23'),(10,'fiksator','fiksator',8,'2026-01-31 09:16:40'),(11,'su','su',9,'2026-01-31 09:21:03');







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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `malzemeler` VALUES (1,'chanel blu, etiket','etiket',NULL,-2000.00,'adet',1.75,'TL',0,0,'depo a','a',0),(2,'chanel blu, jelatin','jelatin',NULL,-2000.00,'adet',1.00,'TL',0,0,'depo a','a',0),(5,'chanel blu, kutu','kutu',NULL,-2000.00,'adet',0.40,'USD',0,0,'depo a','a',0),(6,'chanel blu, takım','takm',NULL,-2000.00,'adet',2.40,'USD',0,0,'depo a','a',0),(7,'chanel blu, ham esans','ham_esans','null',-135.00,'lt',120.00,'USD',0,0,'depo a','a',0),(8,'chanel blu, paket','paket',NULL,1200.00,'adet',16.00,'TL',0,0,'depo a','a',0),(9,'	dior savage, etiket','etiket',NULL,1000.00,'adet',2.00,'TL',0,0,'depo b','a',0),(10,'	dior savage, kutu','kutu',NULL,0.00,'adet',0.45,'TL',0,0,'depo b','a',0),(12,'	dior savage, paket','paket',NULL,2700.00,'adet',15.00,'TL',0,0,'depo b','a',0),(14,'	dior savage, ham esans','ham_esans','null',-15.00,'lt',85.00,'EUR',0,0,'depo b','a',0),(15,'	dior savage, takım','takm',NULL,0.00,'adet',2.10,'USD',0,0,'depo b','a',0),(16,'	dior savage, jelatin','jelatin',NULL,0.00,'adet',1.00,'TL',0,0,'depo b','a',0),(17,'SU','su','',891.00,'lt',1.00,'TRY',0,1,'depo esans','a',0),(18,'ALKOL','alkol','',4880.00,'lt',1.64,'USD',0,1,'depo esans','a',0),(19,'155 a, etiket','etiket',NULL,900.00,'adet',0.90,'TL',0,0,'depo a','a',0),(20,'155 a, jelatin','jelatin',NULL,900.00,'adet',1.00,'TL',0,0,'depo a','a',0),(23,'155 a, kutu','kutu',NULL,900.00,'adet',0.40,'USD',0,0,'depo a','a',0),(24,'155 a, takım','takm',NULL,900.00,'adet',1.30,'USD',0,0,'depo a','a',0),(25,'155 a, ham esans','ham_esans','null',150.00,'adet',90.00,'USD',0,0,'depo a','a',0),(26,'155 a, paket','paket',NULL,900.00,'adet',12.00,'USD',0,0,'depo a','a',0);







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

INSERT INTO `montaj_is_emirleri` VALUES (1,'2026-03-02','Admin User','2','	dior savage',500.00,'adet','2026-03-02','2026-03-02',' KIRIK VAR','tamamlandi',490.00,10.00,1,'2026-03-02','2026-03-02'),(2,'2026-03-02','Admin User','1','chanel blu',1000.00,'adet','2026-03-02','2026-03-02',' KUTU EKSİK','tamamlandi',999.00,1.00,2,'2026-03-02','2026-03-02'),(3,'2026-03-02','Admin User','1','chanel blu',1000.00,'adet','2026-03-02','2026-03-02',' ','tamamlandi',1000.00,0.00,2,'2026-03-02','2026-03-02'),(4,'2026-03-02','Admin User','1','chanel blu',1000.00,'adet','2026-03-02','2026-03-02',' KIRIK VAR','tamamlandi',991.00,9.00,2,'2026-03-02','2026-03-02'),(5,'2026-03-02','Admin User','2','	dior savage',500.00,'adet','2026-03-02','2026-03-02',' FFFF','tamamlandi',499.00,1.00,1,'2026-03-02','2026-03-02'),(6,'2026-03-08','Admin User','3','155 a',100.00,'adet','2026-03-08','2026-03-08',' ','tamamlandi',100.00,0.00,2,'2026-03-09','2026-03-09');







CREATE TABLE `montaj_is_emri_malzeme_listesi` (
  `is_emri_numarasi` int(11) NOT NULL,
  `malzeme_kodu` varchar(50) NOT NULL,
  `malzeme_ismi` varchar(255) NOT NULL,
  `malzeme_turu` varchar(100) NOT NULL,
  `miktar` decimal(10,2) NOT NULL,
  `birim` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `montaj_is_emri_malzeme_listesi` VALUES (1,'9','	dior savage, etiket','etiket',500.00,'adet'),(1,'10','	dior savage, kutu','kutu',500.00,'adet'),(1,'12','	dior savage, paket','paket',300.00,'adet'),(1,'15','	dior savage, takım','takm',500.00,'adet'),(1,'16','	dior savage, jelatin','jelatin',500.00,'adet'),(1,'ES-260302-669','	dior savage, Esans','esans',75.00,'adet'),(2,'1','chanel blu, etiket','etiket',1000.00,'adet'),(2,'2','chanel blu, jelatin','jelatin',1000.00,'adet'),(2,'5','chanel blu, kutu','kutu',1000.00,'adet'),(2,'6','chanel blu, takım','takm',1000.00,'adet'),(2,'8','chanel blu, paket','paket',600.00,'adet'),(2,'ES-260302-603','chanel blu, Esans','esans',150.00,'adet'),(3,'1','chanel blu, etiket','etiket',1000.00,'adet'),(3,'2','chanel blu, jelatin','jelatin',1000.00,'adet'),(3,'5','chanel blu, kutu','kutu',1000.00,'adet'),(3,'6','chanel blu, takım','takm',1000.00,'adet'),(3,'8','chanel blu, paket','paket',600.00,'adet'),(3,'ES-260302-603','chanel blu, Esans','esans',150.00,'adet'),(4,'1','chanel blu, etiket','etiket',1000.00,'adet'),(4,'2','chanel blu, jelatin','jelatin',1000.00,'adet'),(4,'5','chanel blu, kutu','kutu',1000.00,'adet'),(4,'6','chanel blu, takım','takm',1000.00,'adet'),(4,'8','chanel blu, paket','paket',600.00,'adet'),(4,'ES-260302-603','chanel blu, Esans','esans',150.00,'adet'),(5,'9','	dior savage, etiket','etiket',500.00,'adet'),(5,'10','	dior savage, kutu','kutu',500.00,'adet'),(5,'12','	dior savage, paket','paket',300.00,'adet'),(5,'15','	dior savage, takım','takm',500.00,'adet'),(5,'16','	dior savage, jelatin','jelatin',500.00,'adet'),(5,'ES-260302-669','	dior savage, Esans','esans',75.00,'adet'),(6,'19','155 a, etiket','etiket',100.00,'adet'),(6,'20','155 a, jelatin','jelatin',100.00,'adet'),(6,'23','155 a, kutu','kutu',100.00,'adet'),(6,'24','155 a, takım','takm',100.00,'adet'),(6,'26','155 a, paket','paket',100.00,'adet'),(6,'ES-260308-259','155 a, Esans','esans',100.00,'adet');







CREATE TABLE `mrp_ayarlar` (
  `ayar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ayar_anahtar` varchar(100) NOT NULL,
  `ayar_deger` text DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  PRIMARY KEY (`ayar_id`),
  UNIQUE KEY `ayar_anahtar` (`ayar_anahtar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






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

INSERT INTO `musteriler` VALUES (1,'OSMAN ','','','05325083018','','$2y$10$1Hl1rXN1pNLGjuBvMcaezOtGs9AEFMd2TUqZNHshAaT6xh2F6SMIG','',1,'',1),(2,'HAMZA','','','05321327675','','$2y$10$gYAIdsCoCXgCMSCngseBh.Chq9irKamqXa5qB6gmf7pqaBrYRCEmG','',1,'',1);







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






CREATE TABLE `personel_izinleri` (
  `izin_id` int(11) NOT NULL AUTO_INCREMENT,
  `personel_id` int(11) NOT NULL,
  `izin_anahtari` varchar(255) NOT NULL,
  PRIMARY KEY (`izin_id`),
  UNIQUE KEY `personel_izin_unique` (`personel_id`,`izin_anahtari`),
  CONSTRAINT `personel_izinleri_ibfk_1` FOREIGN KEY (`personel_id`) REFERENCES `personeller` (`personel_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






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

INSERT INTO `personeller` VALUES (1,'Admin User','12345678900',NULL,NULL,NULL,NULL,'admin@parfum.com','05551234567',NULL,NULL,'$2y$10$zaDE9ZOm7Qym4tkH3ZpzM.zGMkmCNhSJZQ1bylCpgNX3rpucgbq9q',NULL,0,0.00),(283,'Yedek Admin','',NULL,NULL,'Administrator','Yönetim','admin2@parfum.com',NULL,NULL,NULL,'$2y$10$z56pgRUputjO7M5.Pp0W1eHOgVJ16GX3OKYtPi4VGenFweT8xUidK',NULL,0,0.00);







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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `satinalma_siparis_kalemleri` VALUES (1,1,15,'dior savage, takım',1000.00,'adet',2.10,'USD',2100.00,1000.00,''),(2,2,2,'chanel blu, jelatin',1000.00,'adet',1.00,'TL',1000.00,1000.00,''),(3,2,16,'dior savage, jelatin',1000.00,'adet',1.00,'TL',1000.00,1000.00,''),(4,3,8,'chanel blu, paket',600.00,'adet',16.00,'TL',9600.00,600.00,''),(5,3,12,'dior savage, paket',600.00,'adet',15.00,'TL',9000.00,600.00,''),(6,4,6,'chanel blu, takım',1000.00,'adet',2.40,'USD',2400.00,1000.00,''),(7,5,10,'dior savage, kutu',1000.00,'adet',0.45,'TL',450.00,1000.00,''),(8,6,1,'chanel blu, etiket',1000.00,'adet',1.75,'TL',1750.00,1000.00,''),(9,6,9,'dior savage, etiket',1000.00,'adet',0.09,'USD',90.00,1000.00,''),(10,7,5,'chanel blu, kutu',1000.00,'adet',0.40,'USD',400.00,1000.00,''),(11,8,8,'chanel blu, paket',2400.00,'adet',16.00,'TL',38400.00,2400.00,''),(12,8,12,'dior savage, paket',2700.00,'adet',15.00,'TL',40500.00,2700.00,''),(15,10,20,'155 a, jelatin',1000.00,'adet',1.00,'TL',1000.00,1000.00,''),(16,10,2,'chanel blu, jelatin',4172.00,'adet',1.00,'TL',4172.00,0.00,''),(17,11,26,'155 a, paket',1000.00,'adet',12.00,'USD',12000.00,1000.00,''),(18,11,8,'chanel blu, paket',103.20,'adet',16.00,'TL',1651.20,0.00,''),(19,12,14,'dior savage, ham esans',150.00,'lt',115.00,'USD',17250.00,0.00,''),(20,13,24,'155 a, takım',1000.00,'adet',1.30,'USD',1300.00,1000.00,''),(21,13,6,'chanel blu, takım',4172.00,'adet',2.40,'USD',10012.80,0.00,''),(22,14,19,'155 a, etiket',1000.00,'adet',0.90,'TL',900.00,1000.00,''),(23,15,1,'chanel blu, etiket',4172.00,'adet',2.00,'TL',8344.00,0.00,''),(24,16,23,'155 a, kutu',1000.00,'adet',0.40,'USD',400.00,1000.00,''),(25,16,5,'chanel blu, kutu',4172.00,'adet',0.40,'USD',1668.80,0.00,''),(26,17,20,'155 a, jelatin',1100.00,'adet',1.00,'TL',1100.00,0.00,''),(27,18,26,'155 a, paket',1100.00,'adet',12.00,'USD',13200.00,0.00,'');







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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `satinalma_siparisler` VALUES (1,'PO-2026-00001',10,'ADEM ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,2100.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-02 08:42:32','2026-03-08 19:48:51'),(2,'PO-2026-00002',12,'GÖKHAN ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,2000.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-02 08:42:37','2026-03-08 19:49:03'),(3,'PO-2026-00003',13,'RAMAZAN ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,18600.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-02 08:42:43','2026-03-08 19:49:15'),(4,'PO-2026-00004',11,'UĞUR TAKIM','2026-03-02',NULL,'kapatildi','bekliyor',NULL,2400.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-02 08:42:48','2026-03-08 19:49:28'),(5,'PO-2026-00005',6,'ESENGÜL ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,450.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-02 08:42:53','2026-03-08 19:48:57'),(6,'PO-2026-00006',8,'MAVİ ETİKET','2026-03-02',NULL,'kapatildi','bekliyor',NULL,1840.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-02 08:43:02','2026-03-08 19:48:34'),(7,'PO-2026-00007',4,'ŞENER','2026-03-02',NULL,'kapatildi','bekliyor',NULL,400.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-02 08:43:29','2026-03-08 19:48:20'),(8,'PO-2026-00008',13,'RAMAZAN ','2026-03-02',NULL,'kapatildi','bekliyor',NULL,78900.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-02 09:44:10','2026-03-08 19:47:55'),(10,'PO-2026-00009',12,'GÖKHAN ','2026-03-08',NULL,'kismen_teslim','bekliyor',NULL,5172.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-08 21:23:26','2026-03-08 21:27:44'),(11,'PO-2026-00010',13,'RAMAZAN ','2026-03-08',NULL,'kismen_teslim','bekliyor',NULL,13651.20,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-08 21:23:30','2026-03-08 21:27:06'),(12,'PO-2026-00011',3,'SELUZ','2026-03-08',NULL,'taslak','bekliyor',NULL,17250.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-08 21:23:35','2026-03-08 21:23:35'),(13,'PO-2026-00012',11,'UĞUR TAKIM','2026-03-08',NULL,'kismen_teslim','bekliyor',NULL,11312.80,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-08 21:23:40','2026-03-08 21:27:28'),(14,'PO-2026-00013',8,'MAVİ ETİKET','2026-03-08',NULL,'tamamlandi','bekliyor',NULL,900.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-08 21:23:48','2026-03-08 21:26:16'),(15,'PO-2026-00014',7,'SARI ETİKET','2026-03-08',NULL,'taslak','bekliyor',NULL,8344.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-08 21:23:53','2026-03-08 21:23:53'),(16,'PO-2026-00015',4,'ŞENER','2026-03-08',NULL,'kismen_teslim','bekliyor',NULL,2068.80,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-08 21:24:02','2026-03-08 21:26:50'),(17,'PO-2026-00016',12,'GÖKHAN ','2026-03-11',NULL,'taslak','bekliyor',NULL,1100.00,'TL','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-11 11:14:53','2026-03-11 11:14:53'),(18,'PO-2026-00017',13,'RAMAZAN ','2026-03-11',NULL,'gonderildi','bekliyor',NULL,13200.00,'USD','Kokpit ekranından otomatik oluşturuldu',1,'Admin User','2026-03-11 11:14:59','2026-03-11 11:15:36');







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

INSERT INTO `siparis_kalemleri` VALUES (1,2,'	dior savage',290,'adet',9.00,2610.00,'USD'),(1,1,'chanel blu',321,'adet',8.00,2568.00,'USD'),(2,2,'	dior savage',520,'adet',9.00,4680.00,'USD'),(2,1,'chanel blu',250,'adet',8.00,2000.00,'USD'),(3,1,'chanel blu',1990,'adet',8.00,15920.00,'USD'),(4,1,'chanel blu',2401,'adet',8.00,19208.00,'USD'),(5,3,'155 a',340,'0',9.00,3060.00,'USD'),(6,2,'	dior savage',500,'adet',9.00,4500.00,'USD'),(6,3,'155 a',400,'adet',9.00,3600.00,'USD');







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

INSERT INTO `siparisler` VALUES (1,2,'HAMZA','2026-03-02 13:21:24','tamamlandi',611,'Personel: Admin User',1,'Admin User','2026-03-02 13:21:37','','odendi',NULL,5178.00,'USD'),(2,1,'OSMAN ','2026-03-02 13:22:22','tamamlandi',770,'Personel: Admin User',1,'Admin User','2026-03-02 13:22:30','','odendi',NULL,6680.00,'USD'),(3,1,'OSMAN ','2026-03-02 16:25:21','tamamlandi',1990,'Personel: Admin User',1,'Admin User','2026-03-02 16:26:03','','bekliyor',NULL,0.00,'USD'),(4,2,'HAMZA','2026-03-02 22:04:26','tamamlandi',2401,'Personel: Admin User',1,'Admin User','2026-03-04 09:26:52','','bekliyor',NULL,0.00,'USD'),(5,1,'OSMAN ','2026-03-09 00:56:34','tamamlandi',340,'Personel: Admin User',1,'Admin User','2026-03-09 00:57:06','','bekliyor',NULL,0.00,'USD'),(6,2,'HAMZA','2026-03-11 14:02:59','tamamlandi',900,'Personel: Admin User',1,'Admin User','2026-03-11 14:03:54','','bekliyor',NULL,0.00,'USD');







CREATE TABLE `sirket_kasasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `para_birimi` varchar(3) NOT NULL,
  `bakiye` decimal(15,2) NOT NULL DEFAULT 0.00,
  `guncelleme_tarihi` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `para_birimi` (`para_birimi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

INSERT INTO `sirket_kasasi` VALUES (1,'USD',13628.00,'2026-03-09 01:00:31'),(2,'TL',-510265.87,'2026-03-02 14:04:07'),(3,'EUR',32500.00,'2026-03-09 01:02:01');







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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `stok_hareket_kayitlari` VALUES (1,'2026-03-02 12:03:54','malzeme','9','	dior savage, etiket','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'ADAA [Sozlesme ID: 8] [Sipariş: PO-2026-00006]',1,'Admin User','MAVİ ETİKET',8),(2,'2026-03-02 12:04:27','malzeme','14','	dior savage, ham esans','adet',15.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 13]',1,'Admin User','TEVFİK BEY',1),(3,'2026-03-02 12:04:42','malzeme','16','	dior savage, jelatin','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 4] [Sipariş: PO-2026-00002]',1,'Admin User','GÖKHAN ',12),(4,'2026-03-02 12:05:03','malzeme','12','	dior savage, paket','adet',600.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 9] [Sipariş: PO-2026-00003]',1,'Admin User','RAMAZAN ',13),(5,'2026-03-02 12:05:29','malzeme','15','	dior savage, takım','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'AA [Sozlesme ID: 1] [Sipariş: PO-2026-00001]',1,'Admin User','ADEM ',10),(6,'2026-03-02 12:06:00','malzeme','7','chanel blu, ham esans','adet',15.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 18]',1,'Admin User','SELUZ',3),(7,'2026-03-02 12:06:37','malzeme','1','chanel blu, etiket','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'AAA [Sozlesme ID: 21] [Sipariş: PO-2026-00006]',1,'Admin User','MAVİ ETİKET',8),(8,'2026-03-02 12:29:48','Bileşen','18','ALKOL','lt',80.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(9,'2026-03-02 12:29:48','Bileşen','17','SU','lt',8.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(10,'2026-03-02 12:29:48','Bileşen','14','	dior savage, ham esans','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(11,'2026-03-02 12:29:55','Bileşen','17','SU','lt',8.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(12,'2026-03-02 12:29:55','Bileşen','18','ALKOL','lt',80.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(13,'2026-03-02 12:29:55','Bileşen','7','chanel blu, ham esans','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(14,'2026-03-02 12:31:47','malzeme','10','	dior savage, kutu','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'EE [Sozlesme ID: 3] [Sipariş: PO-2026-00005]',1,'Admin User','ESENGÜL ',6),(15,'2026-03-02 12:32:16','Esans','ES-260302-669','	dior savage, Esans','lt',100.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(16,'2026-03-02 12:32:42','Esans','ES-260302-603','chanel blu, Esans','lt',95.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(17,'2026-03-02 12:39:40','etiket','9','	dior savage, etiket','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(18,'2026-03-02 12:39:40','kutu','10','	dior savage, kutu','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(19,'2026-03-02 12:39:40','paket','12','	dior savage, paket','adet',300.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(20,'2026-03-02 12:39:40','takm','15','	dior savage, takım','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(21,'2026-03-02 12:39:40','jelatin','16','	dior savage, jelatin','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(22,'2026-03-02 12:39:40','esans','ES-260302-669','	dior savage, Esans','adet',75.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(23,'2026-03-02 12:45:11','malzeme','12','	dior savage, paket','adet',2700.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'12 [Sozlesme ID: 9] [Sipariş: PO-2026-00008]',1,'Admin User','RAMAZAN ',13),(24,'2026-03-02 12:45:39','malzeme','8','chanel blu, paket','adet',2400.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'33\r\n [Sozlesme ID: 20] [Sipariş: PO-2026-00008]',1,'Admin User','RAMAZAN ',13),(25,'2026-03-02 12:46:01','malzeme','6','chanel blu, takım','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'WW [Sozlesme ID: 15] [Sipariş: PO-2026-00004]',1,'Admin User','UĞUR TAKIM',11),(26,'2026-03-02 12:46:15','malzeme','2','chanel blu, jelatin','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'WWW [Sozlesme ID: 22] [Sipariş: PO-2026-00002]',1,'Admin User','GÖKHAN ',12),(27,'2026-03-02 12:53:02','Ürün','2','	dior savage','adet',490.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,1,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(28,'2026-03-02 12:54:21','malzeme','7','chanel blu, ham esans','lt',30.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'123\r\n [Sozlesme ID: 18]',1,'Admin User','SELUZ',3),(29,'2026-03-02 12:57:44','Bileşen','17','SU','lt',8.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(30,'2026-03-02 12:57:44','Bileşen','18','ALKOL','lt',80.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(31,'2026-03-02 12:57:44','Bileşen','7','chanel blu, ham esans','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(32,'2026-03-02 12:58:00','Esans','ES-260302-603','chanel blu, Esans','lt',100.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(33,'2026-03-02 13:01:28','Bileşen','17','SU','lt',80.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(34,'2026-03-02 13:01:28','Bileşen','18','ALKOL','lt',800.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(35,'2026-03-02 13:01:28','Bileşen','7','chanel blu, ham esans','lt',150.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(36,'2026-03-02 13:01:51','Esans','ES-260302-603','chanel blu, Esans','lt',1000.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(37,'2026-03-02 13:12:00','malzeme','5','chanel blu, kutu','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'123 [Sozlesme ID: 17] [Sipariş: PO-2026-00007]',1,'Admin User','ŞENER',4),(38,'2026-03-02 13:12:20','malzeme','8','chanel blu, paket','adet',600.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'123\r\n [Sozlesme ID: 20] [Sipariş: PO-2026-00003]',1,'Admin User','RAMAZAN ',13),(39,'2026-03-02 13:13:58','etiket','1','chanel blu, etiket','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(40,'2026-03-02 13:13:58','jelatin','2','chanel blu, jelatin','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(41,'2026-03-02 13:13:58','kutu','5','chanel blu, kutu','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(42,'2026-03-02 13:13:58','takm','6','chanel blu, takım','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(43,'2026-03-02 13:13:58','paket','8','chanel blu, paket','adet',600.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(44,'2026-03-02 13:13:58','esans','ES-260302-603','chanel blu, Esans','adet',150.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(45,'2026-03-02 13:14:06','etiket','1','chanel blu, etiket','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(46,'2026-03-02 13:14:06','jelatin','2','chanel blu, jelatin','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(47,'2026-03-02 13:14:06','kutu','5','chanel blu, kutu','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(48,'2026-03-02 13:14:06','takm','6','chanel blu, takım','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(49,'2026-03-02 13:14:06','paket','8','chanel blu, paket','adet',600.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(50,'2026-03-02 13:14:06','esans','ES-260302-603','chanel blu, Esans','adet',150.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(51,'2026-03-02 13:14:12','etiket','1','chanel blu, etiket','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(52,'2026-03-02 13:14:12','jelatin','2','chanel blu, jelatin','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(53,'2026-03-02 13:14:12','kutu','5','chanel blu, kutu','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(54,'2026-03-02 13:14:12','takm','6','chanel blu, takım','adet',1000.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(55,'2026-03-02 13:14:12','paket','8','chanel blu, paket','adet',600.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(56,'2026-03-02 13:14:12','esans','ES-260302-603','chanel blu, Esans','adet',150.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(57,'2026-03-02 13:14:41','Ürün','1','chanel blu','adet',999.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,2,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(58,'2026-03-02 13:14:53','Ürün','1','chanel blu','adet',1000.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,3,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(59,'2026-03-02 13:15:19','Ürün','1','chanel blu','adet',991.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,4,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(60,'2026-03-02 13:21:47','urun','2','	dior savage','adet',290.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,2,'HAMZA','Müşteri siparişi',1,'Admin User',NULL,NULL),(61,'2026-03-02 13:21:47','urun','1','chanel blu','adet',321.00,'cikis','cikis',NULL,NULL,NULL,'1',NULL,2,'HAMZA','Müşteri siparişi',1,'Admin User',NULL,NULL),(62,'2026-03-02 13:22:41','urun','2','	dior savage','adet',520.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,1,'OSMAN ','Müşteri siparişi',1,'Admin User',NULL,NULL),(63,'2026-03-02 13:22:41','urun','1','chanel blu','adet',250.00,'cikis','cikis',NULL,NULL,NULL,'2',NULL,1,'OSMAN ','Müşteri siparişi',1,'Admin User',NULL,NULL),(64,'2026-03-02 16:23:40','etiket','9','	dior savage, etiket','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(65,'2026-03-02 16:23:40','kutu','10','	dior savage, kutu','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(66,'2026-03-02 16:23:40','paket','12','	dior savage, paket','adet',300.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(67,'2026-03-02 16:23:40','takm','15','	dior savage, takım','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(68,'2026-03-02 16:23:40','jelatin','16','	dior savage, jelatin','adet',500.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(69,'2026-03-02 16:23:40','esans','ES-260302-669','	dior savage, Esans','adet',75.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(70,'2026-03-02 16:24:16','Ürün','2','	dior savage','adet',499.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(71,'2026-03-04 09:13:30','urun','1','chanel blu','adet',1990.00,'cikis','cikis',NULL,NULL,NULL,'3',NULL,1,'OSMAN ','Müşteri siparişi',1,'Admin User',NULL,NULL),(72,'2026-03-04 09:27:39','urun','1','chanel blu','adet',2401.00,'cikis','cikis',NULL,NULL,NULL,'4',NULL,2,'HAMZA','Müşteri siparişi',1,'Admin User',NULL,NULL),(73,'2026-03-09 00:26:16','malzeme','19','155 a, etiket','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'mjk [Sozlesme ID: 29] [Sipariş: PO-2026-00013]',1,'Admin User','MAVİ ETİKET',8),(74,'2026-03-09 00:26:50','malzeme','23','155 a, kutu','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'çç\r\n [Sozlesme ID: 33] [Sipariş: PO-2026-00015]',1,'Admin User','ŞENER',4),(75,'2026-03-09 00:27:06','malzeme','26','155 a, paket','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'... [Sozlesme ID: 30] [Sipariş: PO-2026-00010]',1,'Admin User','RAMAZAN ',13),(76,'2026-03-09 00:27:28','malzeme','24','155 a, takım','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'..\r\n [Sozlesme ID: 35] [Sipariş: PO-2026-00012]',1,'Admin User','UĞUR TAKIM',11),(77,'2026-03-09 00:27:44','malzeme','20','155 a, jelatin','adet',1000.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'ööö [Sozlesme ID: 26] [Sipariş: PO-2026-00009]',1,'Admin User','GÖKHAN ',12),(78,'2026-03-09 00:28:23','malzeme','25','155 a, ham esans','adet',15.00,'giris','mal_kabul','depo a','a',NULL,'',NULL,NULL,NULL,'vbnm [Sozlesme ID: 32]',1,'Admin User','SELUZ',3),(79,'2026-03-09 00:45:00','Bileşen','14','	dior savage, ham esans','lt',15.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(80,'2026-03-09 00:45:00','Bileşen','18','ALKOL','lt',80.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(81,'2026-03-09 00:45:00','Bileşen','17','SU','lt',5.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(82,'2026-03-09 00:45:13','Esans','ES-260308-259','155 a, Esans','lt',100.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,5,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(83,'2026-03-09 00:47:44','malzeme','18','ALKOL','lt',5000.00,'giris','mal_kabul','depo esans','a',NULL,'',NULL,NULL,NULL,'ghjk [Sozlesme ID: 6]',1,'Admin User','KIRMIZIGÜL ALKOL',14),(84,'2026-03-09 00:55:13','etiket','19','155 a, etiket','adet',100.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(85,'2026-03-09 00:55:13','jelatin','20','155 a, jelatin','adet',100.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(86,'2026-03-09 00:55:13','kutu','23','155 a, kutu','adet',100.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(87,'2026-03-09 00:55:13','takm','24','155 a, takım','adet',100.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(88,'2026-03-09 00:55:13','paket','26','155 a, paket','adet',100.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(89,'2026-03-09 00:55:13','esans','ES-260308-259','155 a, Esans','adet',100.00,'Çıkış','Üretime Çıkış',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri başlatıldı',1,'Admin User',NULL,NULL),(90,'2026-03-09 00:55:26','Ürün','3','155 a','adet',100.00,'Giriş','Üretimden Giriş',NULL,NULL,NULL,NULL,6,NULL,NULL,'İş emri tamamlama',1,'Admin User',NULL,NULL),(91,'2026-03-09 00:57:15','urun','3','155 a','0',340.00,'cikis','cikis',NULL,NULL,NULL,'5',NULL,1,'OSMAN ','Müşteri siparişi',1,'Admin User',NULL,NULL),(92,'2026-03-11 13:50:57','malzeme','9','	dior savage, etiket','adet',1000.00,'giris','mal_kabul','depo b','a',NULL,'',NULL,NULL,NULL,'1215 [Sozlesme ID: 10]',1,'Admin User','SARI ETİKET',7),(93,'2026-03-11 14:04:39','urun','2','	dior savage','adet',500.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,2,'HAMZA','Müşteri siparişi',1,'Admin User',NULL,NULL),(94,'2026-03-11 14:04:39','urun','3','155 a','adet',400.00,'cikis','cikis',NULL,NULL,NULL,'6',NULL,2,'HAMZA','Müşteri siparişi',1,'Admin User',NULL,NULL);







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

INSERT INTO `stok_hareketleri_sozlesmeler` VALUES (1,8,9,1000.00,'2026-03-02 09:03:54',0.09,'USD','MAVİ ETİKET',8,'2026-03-02','2026-04-02'),(2,13,14,15.00,'2026-03-02 09:04:27',85.00,'EUR','TEVFİK BEY',1,'2026-03-02','2026-04-02'),(3,4,16,1000.00,'2026-03-02 09:04:42',1.00,'TL','GÖKHAN ',12,'2026-03-02','2026-04-02'),(4,9,12,600.00,'2026-03-02 09:05:03',15.00,'TL','RAMAZAN ',13,'2026-03-02','2026-04-02'),(5,1,15,1000.00,'2026-03-02 09:05:29',2.10,'USD','ADEM ',10,'2026-03-02','2026-04-02'),(6,18,7,15.00,'2026-03-02 09:06:00',120.00,'USD','SELUZ',3,'2026-03-02','2026-04-02'),(7,21,1,1000.00,'2026-03-02 09:06:37',1.75,'TL','MAVİ ETİKET',8,'2026-03-02','2026-04-02'),(14,3,10,1000.00,'2026-03-02 09:31:47',0.45,'TL','ESENGÜL ',6,'2026-03-02','2026-04-02'),(23,9,12,2700.00,'2026-03-02 09:45:11',15.00,'TL','RAMAZAN ',13,'2026-03-02','2026-04-02'),(24,20,8,2400.00,'2026-03-02 09:45:39',16.00,'TL','RAMAZAN ',13,'2026-03-02','2026-04-02'),(25,15,6,1000.00,'2026-03-02 09:46:01',2.40,'USD','UĞUR TAKIM',11,'2026-03-02','2026-04-02'),(26,22,2,1000.00,'2026-03-02 09:46:15',1.00,'TL','GÖKHAN ',12,'2026-03-02','2026-04-02'),(28,18,7,30.00,'2026-03-02 09:54:21',120.00,'USD','SELUZ',3,'2026-03-02','2026-04-02'),(37,17,5,1000.00,'2026-03-02 10:12:00',0.40,'USD','ŞENER',4,'2026-03-02','2026-04-02'),(38,20,8,600.00,'2026-03-02 10:12:20',16.00,'TL','RAMAZAN ',13,'2026-03-02','2026-04-02'),(73,29,19,1000.00,'2026-03-08 21:26:16',0.90,'TL','MAVİ ETİKET',8,'2026-03-08','2026-04-08'),(74,33,23,1000.00,'2026-03-08 21:26:50',0.40,'USD','ŞENER',4,'2026-03-08','2026-04-08'),(75,30,26,1000.00,'2026-03-08 21:27:06',12.00,'USD','RAMAZAN ',13,'2026-03-08','2026-04-08'),(76,35,24,1000.00,'2026-03-08 21:27:28',1.30,'USD','UĞUR TAKIM',11,'2026-03-08','2026-04-08'),(77,26,20,1000.00,'2026-03-08 21:27:44',1.00,'TL','GÖKHAN ',12,'2026-03-08','2026-04-08'),(78,32,25,15.00,'2026-03-08 21:28:23',90.00,'USD','SELUZ',3,'2026-03-08','2026-04-08'),(83,6,18,5000.00,'2026-03-08 21:47:44',1.60,'USD','KIRMIZIGÜL ALKOL',14,'2026-03-02','2026-04-02'),(92,10,9,1000.00,'2026-03-11 10:50:57',2.00,'TL','SARI ETİKET',7,'2026-03-02','2026-04-02');







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






CREATE TABLE `taksit_siparis_baglantisi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `siparis_id` int(11) NOT NULL,
  `tutar_katkisi` decimal(15,2) NOT NULL COMMENT 'How much of this order is covered by this plan',
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `taksit_siparis_baglantisi_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `taksit_planlari` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;






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

INSERT INTO `tanklar` VALUES (1,'w 500','dior savage','',250.00),(2,'e 101','chanel blu','',250.00),(3,'e 155 a','155 a','',250.00);







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

INSERT INTO `tedarikciler` VALUES (1,'TEVFİK BEY','ESANS ','','','','','','',''),(2,'LUZKIM','ESANS','','','','','','',''),(3,'SELUZ','ESANS','','','','','','',''),(4,'ŞENER','KUTU','','','','','','',''),(5,'EBUBEKİR ','KUTU','','','','','','',''),(6,'ESENGÜL ','KUTU','','','','','','',''),(7,'SARI ETİKET','ETİKET','','','','','','',''),(8,'MAVİ ETİKET','ETİKET','','','','','','',''),(9,'KIRMIZIGÜL','TAKIM','','','','','','',''),(10,'ADEM ','TAKIM','','','','','','',''),(11,'UĞUR TAKIM','TAKIM','','','','','','',''),(12,'GÖKHAN ','JELATİN','','','','','','',''),(13,'RAMAZAN ','PAKET','','','','','','',''),(14,'KIRMIZIGÜL ALKOL','ALKOL','','','','','','',''),(15,'MERKES ŞEBEKE','SU','','','','','','','');







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

INSERT INTO `urun_agaci` VALUES (1,1,'chanel blu','etiket','1','chanel blu, etiket',1.00,'urun'),(2,1,'chanel blu','jelatin','2','chanel blu, jelatin',1.00,'urun'),(5,1,'chanel blu','kutu','5','chanel blu, kutu',1.00,'urun'),(6,1,'chanel blu','takm','6','chanel blu, takım',1.00,'urun'),(7,1,'chanel blu','paket','8','chanel blu, paket',0.60,'urun'),(8,1,'chanel blu','esans','ES-260302-603','chanel blu, Esans',0.15,'urun'),(9,2,'	dior savage','etiket','9','	dior savage, etiket',1.00,'urun'),(10,2,'	dior savage','kutu','10','	dior savage, kutu',1.00,'urun'),(12,2,'	dior savage','paket','12','	dior savage, paket',0.60,'urun'),(14,2,'	dior savage','takm','15','	dior savage, takım',1.00,'urun'),(15,2,'	dior savage','jelatin','16','	dior savage, jelatin',1.00,'urun'),(16,2,'	dior savage','esans','ES-260302-669','	dior savage, Esans',0.15,'urun'),(17,2,'	dior savage, Esans','malzeme','18','ALKOL',0.80,'esans'),(18,2,'	dior savage, Esans','malzeme','17','SU',0.08,'esans'),(19,2,'	dior savage, Esans','malzeme','14','	dior savage, ham esans',0.15,'esans'),(20,1,'chanel blu, Esans','malzeme','17','SU',0.08,'esans'),(21,1,'chanel blu, Esans','malzeme','18','ALKOL',0.80,'esans'),(22,1,'chanel blu, Esans','malzeme','7','chanel blu, ham esans',0.15,'esans'),(23,3,'155 a','etiket','19','155 a, etiket',1.00,'urun'),(24,3,'155 a','jelatin','20','155 a, jelatin',1.00,'urun'),(27,3,'155 a','kutu','23','155 a, kutu',1.00,'urun'),(28,3,'155 a','takm','24','155 a, takım',1.00,'urun'),(29,3,'155 a','paket','26','155 a, paket',1.00,'urun'),(30,3,'155 a','esans','ES-260308-259','155 a, Esans',1.00,'urun'),(31,3,'155 a, Esans','malzeme','25','155 a, ham esans',0.15,'esans'),(32,3,'155 a, Esans','malzeme','18','ALKOL',0.80,'esans'),(33,3,'155 a, Esans','malzeme','17','SU',0.05,'esans');







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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `urunler` VALUES (1,'chanel blu','',-1972,'adet',8.00,'USD',0.00,'TRY',200,'depo a','a','uretilen',NULL),(2,'	dior savage','',-121,'adet',9.00,'USD',0.00,'TRY',0,'depo b','a','uretilen',NULL),(3,'155 a','',-390,'adet',9.00,'USD',0.00,'TRY',0,'depo a','a','uretilen',NULL),(4,'Deneme Eko','',10,'adet',10.00,'TRY',0.00,'TRY',200,'depo c','depo c','uretilen',NULL);







CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_esans_maliyetleri` AS select `e`.`esans_kodu` AS `esans_kodu`,coalesce(sum(`ua`.`bilesen_miktari` * case `m`.`para_birimi` when 'USD' then `m`.`alis_fiyati` * (select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'dolar_kuru') when 'EUR' then `m`.`alis_fiyati` * (select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'euro_kuru') else `m`.`alis_fiyati` end),0) AS `toplam_maliyet` from ((`esanslar` `e` left join `urun_agaci` `ua` on((`ua`.`urun_kodu` = `e`.`esans_kodu` or `ua`.`urun_kodu` = cast(`e`.`esans_id` as char charset latin1)) and `ua`.`agac_turu` = 'esans')) left join `malzemeler` `m` on(trim(`ua`.`bilesen_kodu`) = trim(`m`.`malzeme_kodu`))) group by `e`.`esans_kodu`;






CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_urun_maliyetleri` AS select `u`.`urun_kodu` AS `urun_kodu`,case when `u`.`urun_tipi` = 'hazir_alinan' then coalesce(case when ucase(coalesce(`u`.`alis_fiyati_para_birimi`,'TRY')) = 'USD' then coalesce(`u`.`alis_fiyati`,0) * coalesce((select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'dolar_kuru' limit 1),1) when ucase(coalesce(`u`.`alis_fiyati_para_birimi`,'TRY')) = 'EUR' then coalesce(`u`.`alis_fiyati`,0) * coalesce((select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'euro_kuru' limit 1),1) else coalesce(`u`.`alis_fiyati`,0) end,0) else coalesce(sum(`ua`.`bilesen_miktari` * case when `m`.`alis_fiyati` is not null then case when ucase(coalesce(`m`.`para_birimi`,'TRY')) = 'USD' then coalesce(`m`.`alis_fiyati`,0) * coalesce((select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'dolar_kuru' limit 1),1) when ucase(coalesce(`m`.`para_birimi`,'TRY')) = 'EUR' then coalesce(`m`.`alis_fiyati`,0) * coalesce((select `ayarlar`.`ayar_deger` from `ayarlar` where `ayarlar`.`ayar_anahtar` = 'euro_kuru' limit 1),1) else coalesce(`m`.`alis_fiyati`,0) end when `vem`.`toplam_maliyet` is not null then coalesce(`vem`.`toplam_maliyet`,0) else 0 end),0) end AS `teorik_maliyet` from (((`urunler` `u` left join `urun_agaci` `ua` on(`u`.`urun_kodu` = `ua`.`urun_kodu` and `ua`.`agac_turu` = 'urun')) left join `malzemeler` `m` on(`ua`.`bilesen_kodu` = `m`.`malzeme_kodu`)) left join `v_esans_maliyetleri` `vem` on(`ua`.`bilesen_kodu` = `vem`.`esans_kodu`)) group by `u`.`urun_kodu`;





-- Gider Yonetimi tablosuna kasa secimi alanlari ekle
ALTER TABLE `gider_yonetimi`
ADD COLUMN IF NOT EXISTS `kasa_secimi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
ADD COLUMN IF NOT EXISTS `cek_secimi` int(11) NULL COMMENT 'cek_kasasi tablosundaki cek_id';

-- Gelir Yonetimi tablosuna kasa secimi alanlari ekle
ALTER TABLE `gelir_yonetimi`
ADD COLUMN IF NOT EXISTS `kasa_secimi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
ADD COLUMN IF NOT EXISTS `cek_secimi` int(11) NULL COMMENT 'cek_kasasi tablosundaki cek_id';

-- Cerceve Sozlesmeler tablosuna odeme kasasi alanlari ekle
ALTER TABLE `cerceve_sozlesmeler`
ADD COLUMN IF NOT EXISTS `odeme_kasasi` varchar(20) NOT NULL DEFAULT 'TL' COMMENT 'TL, USD, EUR, cek_kasasi',
ADD COLUMN IF NOT EXISTS `odenen_cek_id` int(11) NULL COMMENT 'cek_kasasi tablosundaki cek_id';

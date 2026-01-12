-- Mevcut kasa_islemleri verilerini kasa_hareketleri tablosuna tasi
INSERT INTO `kasa_hareketleri` (
  `tarih`, 
  `islem_tipi`, 
  `kasa_adi`, 
  `tutar`, 
  `para_birimi`, 
  `tl_karsiligi`,
  `kaynak_tablo`,
  `kaynak_id`,
  `aciklama`, 
  `kaydeden_personel`
)
SELECT 
  `tarih`,
  CASE 
    WHEN `islem_tipi` = 'gelir' THEN 'gelir_girisi'
    WHEN `islem_tipi` = 'gider' THEN 'gider_cikisi'
    WHEN `islem_tipi` = 'transfer_giris' THEN 'transfer_giris'
    WHEN `islem_tipi` = 'transfer_cikis' THEN 'transfer_cikis'
    WHEN `islem_tipi` = 'kasa_ekle' THEN 'kasa_ekle'
    WHEN `islem_tipi` = 'kasa_cikar' THEN 'kasa_cikar'
    ELSE `islem_tipi`
  END as islem_tipi,
  `para_birimi` as kasa_adi,
  `tutar`,
  `para_birimi`,
  CASE 
    WHEN `para_birimi` = 'TL' THEN `tutar`
    ELSE `tutar`
  END as tl_karsiligi,
  `kaynak_tablo`,
  `kaynak_id`,
  `aciklama`,
  `kaydeden_personel`
FROM `kasa_islemleri`
WHERE NOT EXISTS (
  SELECT 1 FROM `kasa_hareketleri` WHERE `kasa_hareketleri`.`kaynak_id` = `kasa_islemleri`.`id` AND `kasa_hareketleri`.`kaynak_tablo` = 'kasa_islemleri'
);

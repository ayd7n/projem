CREATE OR REPLACE VIEW v_urun_maliyetleri AS
SELECT 
    ua.urun_kodu,
    SUM(
        CASE 
            WHEN ua.bilesenin_malzeme_turu = 'esans' THEN 
                (SELECT IFNULL(maliyet, 0) FROM v_esans_maliyetleri WHERE esans_kodu = ua.bilesen_kodu) * ua.bilesen_miktari
            ELSE 
                (SELECT IFNULL(birim_fiyat, 0) FROM malzemeler WHERE malzeme_kodu = ua.bilesen_kodu) * ua.bilesen_miktari
        END
    ) AS teorik_maliyet
FROM urun_agaci ua
WHERE ua.agac_turu = 'urun'
GROUP BY ua.urun_kodu;

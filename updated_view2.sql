DROP VIEW IF EXISTS urun_siparis_durumu_view;

CREATE VIEW urun_siparis_durumu_view AS
SELECT 
    u.urun_kodu,
    u.urun_ismi,
    u.stok_miktari,
    u.kritik_stok_seviyesi,
    u.birim,
    u.satis_fiyati,
    COALESCE(sm.onay_bekleyen_siparis_miktari, 0) AS onay_bekleyen_siparis_miktari,
    COALESCE(sm.onaylanan_siparis_miktari, 0) AS onaylanan_siparis_miktari,
    COALESCE(sm.onay_bekleyen_siparis_miktari, 0) + COALESCE(sm.onaylanan_siparis_miktari, 0) AS toplam_acik_siparisler,
    COALESCE(SUM(CASE WHEN me.durum = 'uretimde' THEN me.planlanan_miktar ELSE 0 END), 0) AS montaj_uretimindeki_miktar,
    (
        SELECT 
            CASE 
                WHEN COUNT(0) = 0 THEN '[]' 
                ELSE CONCAT(
                    '[',
                    GROUP_CONCAT(
                        CONCAT(
                            '{"bilesen_ismi":"', ua2.bilesen_ismi,
                            '","bilesen_turu":"', ua2.bilesenin_malzeme_turu,
                            '","bilesen_miktari":', ua2.bilesen_miktari,
                            ',"stok_miktari":', COALESCE(
                                CASE 
                                    WHEN ua2.bilesenin_malzeme_turu = 'esans' 
                                    THEN e2.stok_miktari 
                                    ELSE m2.stok_miktari 
                                END, 0
                            ),
                            CASE 
                                WHEN ua2.bilesenin_malzeme_turu != 'esans' 
                                THEN CONCAT(',"siparis_verilen_miktar":', 
                                    COALESCE(
                                        (SELECT SUM(ms.miktar) 
                                         FROM malzeme_siparisler ms 
                                         WHERE ms.malzeme_ismi = ua2.bilesen_ismi 
                                         AND ms.durum = 'siparis_verildi'
                                        ), 0
                                    )
                                )
                                ELSE ''
                            END,
                            '}'
                        ) 
                        ORDER BY ua2.bilesen_ismi ASC 
                        SEPARATOR ', '
                    ),
                    ']'
                ) 
            END
        FROM 
            urun_agaci ua2
            LEFT JOIN esanslar e2 ON (ua2.bilesen_ismi = e2.esans_ismi)
            LEFT JOIN malzemeler m2 ON (ua2.bilesen_ismi = m2.malzeme_ismi)
        WHERE 
            ua2.urun_kodu = u.urun_kodu 
            AND ua2.agac_turu = 'urun'
    ) AS bilesenler_ve_miktarlar,
    COALESCE(umv.uretilebilecek_maksimum_adet, 0) AS eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar
FROM 
    urunler u
    LEFT JOIN siparis_miktarlari_view sm ON (u.urun_kodu = sm.urun_kodu)
    LEFT JOIN montaj_is_emirleri me ON (u.urun_kodu = CAST(me.urun_kodu AS UNSIGNED))
    LEFT JOIN uretilebilecek_miktarlar_view umv ON (u.urun_kodu = umv.urun_kodu)
GROUP BY 
    u.urun_kodu, u.urun_ismi, u.stok_miktari, u.kritik_stok_seviyesi, u.birim, u.satis_fiyati
ORDER BY 
    u.urun_ismi;
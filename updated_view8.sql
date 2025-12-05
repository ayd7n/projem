-- Create the view with nested esans components JSON
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
                ELSE (
                    SELECT CONCAT('[', GROUP_CONCAT(component_data ORDER BY component_name ASC SEPARATOR ', '), ']')
                    FROM (
                        SELECT 
                            CONCAT(
                                '{"bilesen_ismi":"', ua2.bilesen_ismi,
                                '","bilesen_turu":"', ua2.bilesenin_malzeme_turu,
                                '","bilesen_miktari":', ua2.bilesen_miktari,
                                ',"stok_miktari":', 
                                CASE 
                                    WHEN ua2.bilesenin_malzeme_turu = 'esans' 
                                    THEN COALESCE(e2.stok_miktari, 0)
                                    ELSE COALESCE(m2.stok_miktari, 0)
                                END,
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
                                    ELSE (
                                        SELECT CONCAT(
                                            ',"uretimdeki_miktar":',
                                            COALESCE(
                                                (SELECT SUM(eie.planlanan_miktar) 
                                                 FROM esans_is_emirleri eie 
                                                 WHERE eie.esans_ismi = ua2.bilesen_ismi 
                                                 AND eie.durum = 'uretimde'
                                                ), 0
                                            ),
                                            ',"esans_bilesenleri":',
                                            COALESCE(
                                                (SELECT CONCAT(
                                                    '[', 
                                                    GROUP_CONCAT(
                                                        CONCAT(
                                                            '{"malzeme_ismi":"', sub_eua.bilesen_ismi,
                                                            '","malzeme_miktari":', sub_eua.bilesen_miktari,
                                                            ',"stok_miktari":', COALESCE(sub_m.stok_miktari, 0),
                                                            ',"siparis_verilen_miktar":',
                                                            COALESCE(
                                                                (SELECT SUM(sub_ms.miktar)
                                                                 FROM malzeme_siparisler sub_ms
                                                                 WHERE sub_ms.malzeme_ismi = sub_eua.bilesen_ismi
                                                                 AND sub_ms.durum = 'siparis_verildi'
                                                                ), 0
                                                            )
                                                            '}'
                                                        ) 
                                                        ORDER BY sub_eua.bilesen_ismi ASC
                                                        SEPARATOR ', '
                                                    ),
                                                    ']'
                                                )
                                                FROM urun_agaci sub_eua
                                                LEFT JOIN malzemeler sub_m ON (sub_eua.bilesen_ismi = sub_m.malzeme_ismi)
                                                WHERE sub_eua.urun_kodu = e2.esans_id AND sub_eua.agac_turu = 'esans'
                                                ), '[]'
                                            )
                                        )
                                    )
                                END,
                                '}'
                            ) AS component_data,
                            ua2.bilesen_ismi AS component_name
                        FROM 
                            urun_agaci ua2
                            LEFT JOIN esanslar e2 ON (ua2.bilesen_ismi = e2.esans_ismi)
                            LEFT JOIN malzemeler m2 ON (ua2.bilesen_ismi = m2.malzeme_ismi)
                        WHERE 
                            ua2.urun_kodu = u.urun_kodu 
                            AND ua2.agac_turu = 'urun'
                    ) AS components
                )
            END
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
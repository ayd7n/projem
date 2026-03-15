-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-03-14_align_product_cost_base.sql
--
-- Purpose:
-- v_urun_maliyetleri.teorik_maliyet always stays in TRY base.
-- App layer converts this value to product sales currency for display.

DROP VIEW IF EXISTS v_urun_maliyetleri;

CREATE ALGORITHM=UNDEFINED VIEW v_urun_maliyetleri AS
SELECT
    u.urun_kodu AS urun_kodu,
    CASE
        WHEN u.urun_tipi = 'hazir_alinan' THEN
            COALESCE(
                CASE
                    WHEN UPPER(COALESCE(u.alis_fiyati_para_birimi, 'TRY')) = 'USD' THEN
                        COALESCE(u.alis_fiyati, 0) * COALESCE((SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = 'dolar_kuru' LIMIT 1), 1)
                    WHEN UPPER(COALESCE(u.alis_fiyati_para_birimi, 'TRY')) = 'EUR' THEN
                        COALESCE(u.alis_fiyati, 0) * COALESCE((SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = 'euro_kuru' LIMIT 1), 1)
                    ELSE
                        COALESCE(u.alis_fiyati, 0)
                END,
                0
            )
        ELSE
            COALESCE(
                SUM(
                    ua.bilesen_miktari * CASE
                        WHEN m.alis_fiyati IS NOT NULL THEN
                            CASE
                                WHEN UPPER(COALESCE(m.para_birimi, 'TRY')) = 'USD' THEN
                                    COALESCE(m.alis_fiyati, 0) * COALESCE((SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = 'dolar_kuru' LIMIT 1), 1)
                                WHEN UPPER(COALESCE(m.para_birimi, 'TRY')) = 'EUR' THEN
                                    COALESCE(m.alis_fiyati, 0) * COALESCE((SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = 'euro_kuru' LIMIT 1), 1)
                                ELSE
                                    COALESCE(m.alis_fiyati, 0)
                            END
                        WHEN vem.toplam_maliyet IS NOT NULL THEN
                            COALESCE(vem.toplam_maliyet, 0)
                        ELSE
                            0
                    END
                ),
                0
            )
    END AS teorik_maliyet
FROM urunler u
LEFT JOIN urun_agaci ua
    ON u.urun_kodu = ua.urun_kodu
    AND ua.agac_turu = 'urun'
LEFT JOIN malzemeler m
    ON ua.bilesen_kodu = m.malzeme_kodu
LEFT JOIN v_esans_maliyetleri vem
    ON ua.bilesen_kodu = vem.esans_kodu
GROUP BY u.urun_kodu;

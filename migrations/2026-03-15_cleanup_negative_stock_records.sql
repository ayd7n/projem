-- Remove the currently known negative-stock product and material records.
-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-03-15_cleanup_negative_stock_records.sql

START TRANSACTION;

DELETE uae
FROM urun_agaci AS uae
JOIN (
    SELECT DISTINCT
        CAST(e.esans_id AS CHAR) AS esans_id_char,
        e.esans_kodu
    FROM urun_agaci AS ua
    JOIN esanslar AS e
        ON ua.bilesen_kodu = e.esans_kodu
    WHERE ua.agac_turu = 'urun'
      AND ua.urun_kodu IN (1, 2, 3)
      AND LOWER(TRIM(ua.bilesenin_malzeme_turu)) = 'esans'
) AS es
    ON uae.agac_turu = 'esans'
   AND (uae.urun_kodu = es.esans_kodu OR uae.urun_kodu = es.esans_id_char);

DELETE FROM urun_agaci
WHERE agac_turu = 'urun'
  AND urun_kodu IN (1, 2, 3);

DELETE FROM urun_agaci
WHERE bilesen_kodu IN ('1', '2', '5', '6', '7', '14');

DELETE FROM malzemeler
WHERE malzeme_kodu IN (1, 2, 5, 6, 7, 14)
  AND stok_miktari < 0;

DELETE FROM urunler
WHERE urun_kodu IN (1, 2, 3)
  AND stok_miktari < 0;

COMMIT;

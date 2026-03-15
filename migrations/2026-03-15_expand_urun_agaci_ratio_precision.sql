-- Increase BOM ratio precision from 2 decimals to 4 decimals.
-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-03-15_expand_urun_agaci_ratio_precision.sql

ALTER TABLE urun_agaci
    MODIFY COLUMN bilesen_miktari DECIMAL(10,4) NOT NULL DEFAULT 0.0000;

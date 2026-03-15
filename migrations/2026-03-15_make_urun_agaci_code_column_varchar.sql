-- Allow product-tree rows to store either numeric product ids or essence codes.
-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-03-15_make_urun_agaci_code_column_varchar.sql

ALTER TABLE urun_agaci
    MODIFY COLUMN urun_kodu VARCHAR(50) NOT NULL;

-- Prevent the same material from appearing multiple times in one purchase order.
-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-03-15_enforce_purchase_order_material_uniqueness.sql

ALTER TABLE satinalma_siparis_kalemleri
    ADD CONSTRAINT uq_satinalma_siparis_kalemleri_order_material
    UNIQUE KEY (siparis_id, malzeme_kodu);

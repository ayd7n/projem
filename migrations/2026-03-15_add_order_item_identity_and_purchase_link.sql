-- Add a stable identity to customer order items and keep purchase receipts linked to their PO.
-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-03-15_add_order_item_identity_and_purchase_link.sql

ALTER TABLE siparis_kalemleri
    ADD COLUMN kalem_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD KEY idx_siparis_kalemleri_siparis_id (siparis_id),
    ADD KEY idx_siparis_kalemleri_urun_kodu (urun_kodu);

ALTER TABLE stok_hareket_kayitlari
    ADD COLUMN satinalma_siparis_id INT(11) DEFAULT NULL AFTER ilgili_belge_no,
    ADD KEY idx_stok_hareket_satinalma_siparis_id (satinalma_siparis_id);

-- Add assembly approval queue data model.
-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-04-05_add_montaj_approval_queue.sql

ALTER TABLE montaj_is_emirleri
    ADD COLUMN onaya_gonderen_personel_id INT NULL AFTER gerceklesen_bitis_tarihi,
    ADD COLUMN onaya_gonderen_personel_adi VARCHAR(255) NULL AFTER onaya_gonderen_personel_id,
    ADD COLUMN onaya_gonderme_tarihi DATETIME NULL AFTER onaya_gonderen_personel_adi,
    ADD COLUMN onaylayan_personel_id INT NULL AFTER onaya_gonderme_tarihi,
    ADD COLUMN onaylayan_personel_adi VARCHAR(255) NULL AFTER onaylayan_personel_id,
    ADD COLUMN onay_tarihi DATETIME NULL AFTER onaylayan_personel_adi,
    ADD COLUMN son_onay_notu TEXT NULL AFTER onay_tarihi;

ALTER TABLE montaj_is_emirleri
    ADD INDEX idx_montaj_durum (durum),
    ADD INDEX idx_montaj_onay_tarihi (onay_tarihi);

CREATE TABLE montaj_is_emri_onay_loglari (
    onay_log_id INT NOT NULL AUTO_INCREMENT,
    is_emri_numarasi INT NOT NULL,
    aksiyon VARCHAR(50) NOT NULL,
    onceki_durum VARCHAR(50) NULL,
    yeni_durum VARCHAR(50) NULL,
    onceki_tamamlanan_miktar DECIMAL(10,2) NULL,
    yeni_tamamlanan_miktar DECIMAL(10,2) NULL,
    not_metni TEXT NULL,
    yapan_personel_id INT NULL,
    yapan_personel_adi VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (onay_log_id),
    KEY idx_mieol_is_emri_numarasi (is_emri_numarasi),
    KEY idx_mieol_aksiyon (aksiyon),
    KEY idx_mieol_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

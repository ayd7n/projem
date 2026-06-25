-- Prevent paying the same employee twice for the same period (race condition guard).
-- The application pre-checks for an existing payment, but two concurrent requests can
-- both pass that check before either inserts. This UNIQUE key closes that gap at the DB level.
-- The application now translates duplicate-key errors (1062) into a friendly message.
--
-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-06-26_unique_personel_maas_odemesi.sql
--
-- NOTE: If duplicate rows already exist, this ALTER will fail. Remove the older
-- duplicate(s) first, e.g.:
--   DELETE m1 FROM personel_maas_odemeleri m1
--   JOIN personel_maas_odemeleri m2
--     ON m1.personel_id = m2.personel_id
--    AND m1.donem_yil  = m2.donem_yil
--    AND m1.donem_ay   = m2.donem_ay
--    AND m1.odeme_id   < m2.odeme_id;

ALTER TABLE personel_maas_odemeleri
    ADD CONSTRAINT uq_personel_maas_odemesi_donem
    UNIQUE KEY (personel_id, donem_yil, donem_ay);

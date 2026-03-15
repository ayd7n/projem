-- Prevent negative stock values at database level.
-- Apply with:
-- mysql -h localhost -u root parfum_erp < migrations/2026-03-14_prevent_negative_stock.sql

DROP TRIGGER IF EXISTS trg_malzemeler_no_negative_insert;
DROP TRIGGER IF EXISTS trg_malzemeler_no_negative_update;
DROP TRIGGER IF EXISTS trg_urunler_no_negative_insert;
DROP TRIGGER IF EXISTS trg_urunler_no_negative_update;
DROP TRIGGER IF EXISTS trg_esanslar_no_negative_insert;
DROP TRIGGER IF EXISTS trg_esanslar_no_negative_update;

DELIMITER $$

CREATE TRIGGER trg_malzemeler_no_negative_insert
BEFORE INSERT ON malzemeler
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: malzemeler.stok_miktari';
    END IF;
END$$

CREATE TRIGGER trg_malzemeler_no_negative_update
BEFORE UPDATE ON malzemeler
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: malzemeler.stok_miktari';
    END IF;
END$$

CREATE TRIGGER trg_urunler_no_negative_insert
BEFORE INSERT ON urunler
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: urunler.stok_miktari';
    END IF;
END$$

CREATE TRIGGER trg_urunler_no_negative_update
BEFORE UPDATE ON urunler
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: urunler.stok_miktari';
    END IF;
END$$

CREATE TRIGGER trg_esanslar_no_negative_insert
BEFORE INSERT ON esanslar
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: esanslar.stok_miktari';
    END IF;
END$$

CREATE TRIGGER trg_esanslar_no_negative_update
BEFORE UPDATE ON esanslar
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: esanslar.stok_miktari';
    END IF;
END$$

DELIMITER ;

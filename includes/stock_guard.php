<?php

if (!function_exists('stock_guard_lowercase')) {
    function stock_guard_lowercase($value)
    {
        $value = trim((string) $value);
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }
}

if (!function_exists('get_negative_stock_trigger_definitions')) {
    function get_negative_stock_trigger_definitions()
    {
        return [
            'trg_malzemeler_no_negative_insert' => "CREATE TRIGGER trg_malzemeler_no_negative_insert
BEFORE INSERT ON malzemeler
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: malzemeler.stok_miktari';
    END IF;
END",
            'trg_malzemeler_no_negative_update' => "CREATE TRIGGER trg_malzemeler_no_negative_update
BEFORE UPDATE ON malzemeler
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: malzemeler.stok_miktari';
    END IF;
END",
            'trg_urunler_no_negative_insert' => "CREATE TRIGGER trg_urunler_no_negative_insert
BEFORE INSERT ON urunler
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: urunler.stok_miktari';
    END IF;
END",
            'trg_urunler_no_negative_update' => "CREATE TRIGGER trg_urunler_no_negative_update
BEFORE UPDATE ON urunler
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: urunler.stok_miktari';
    END IF;
END",
            'trg_esanslar_no_negative_insert' => "CREATE TRIGGER trg_esanslar_no_negative_insert
BEFORE INSERT ON esanslar
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: esanslar.stok_miktari';
    END IF;
END",
            'trg_esanslar_no_negative_update' => "CREATE TRIGGER trg_esanslar_no_negative_update
BEFORE UPDATE ON esanslar
FOR EACH ROW
BEGIN
    IF NEW.stok_miktari < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Negatif stok engellendi: esanslar.stok_miktari';
    END IF;
END",
        ];
    }
}

if (!function_exists('ensure_negative_stock_triggers')) {
    function ensure_negative_stock_triggers($connection)
    {
        static $checked = false;

        if ($checked || !($connection instanceof mysqli)) {
            return;
        }

        $checked = true;

        $definitions = get_negative_stock_trigger_definitions();
        $expected_names = array_keys($definitions);
        $quoted_names = array_map(function ($name) use ($connection) {
            return "'" . $connection->real_escape_string($name) . "'";
        }, $expected_names);

        $query = "SELECT TRIGGER_NAME
                  FROM information_schema.TRIGGERS
                  WHERE TRIGGER_SCHEMA = DATABASE()
                    AND TRIGGER_NAME IN (" . implode(', ', $quoted_names) . ")";
        $result = $connection->query($query);
        if (!$result) {
            throw new RuntimeException('Trigger durumu okunamadi: ' . $connection->error);
        }

        $existing_names = [];
        while ($row = $result->fetch_assoc()) {
            $existing_names[] = $row['TRIGGER_NAME'];
        }
        $result->free();

        $missing_names = array_diff($expected_names, $existing_names);
        if (empty($missing_names)) {
            return;
        }

        foreach ($expected_names as $name) {
            if (!$connection->query("DROP TRIGGER IF EXISTS {$name}")) {
                throw new RuntimeException('Trigger kaldirilamadi: ' . $connection->error);
            }
        }

        foreach ($definitions as $definition) {
            if (!$connection->query($definition)) {
                throw new RuntimeException('Trigger olusturulamadi: ' . $connection->error);
            }
        }
    }
}

if (!function_exists('validate_non_negative_stock_value')) {
    function validate_non_negative_stock_value($stock_value, $label = 'Stok miktari')
    {
        if ((float) $stock_value < 0) {
            return $label . " 0'dan kucuk olamaz.";
        }

        return null;
    }
}

if (!function_exists('is_negative_stock_guard_error')) {
    function is_negative_stock_guard_error($message)
    {
        $normalized = stock_guard_lowercase($message);

        return strpos($normalized, 'negatif stok engellendi') !== false
            || strpos($normalized, 'stok_miktari') !== false && strpos($normalized, 'sqlstate[45000]') !== false;
    }
}

if (!function_exists('normalize_negative_stock_error_message')) {
    function normalize_negative_stock_error_message($message, $fallback = null)
    {
        $message = trim((string) $message);
        if ($message === '') {
            return $fallback ?: 'Stok islemi tamamlanamadi.';
        }

        if (is_negative_stock_guard_error($message)) {
            return "Negatif stok engellendi. Stok miktari 0'in altina dusurulemez.";
        }

        $normalized = stock_guard_lowercase($message);
        if (strpos($normalized, 'yetersiz stok') !== false) {
            return 'Yetersiz stok.';
        }

        return $fallback ?: $message;
    }
}

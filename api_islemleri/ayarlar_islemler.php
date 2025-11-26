<?php
include '../config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Geçersiz işlem.'];

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'get_settings') {
        $sql = "SELECT ayar_anahtar, ayar_deger FROM ayarlar WHERE ayar_anahtar IN ('dolar_kuru', 'euro_kuru')";
        $result = $connection->query($sql);
        
        // Default values
        $settings = [
            'dolar_kuru' => '0.0',
            'euro_kuru' => '0.0'
        ];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['ayar_anahtar']] = $row['ayar_deger'];
            }
            $response = ['status' => 'success', 'data' => $settings];
        } else {
            $response['message'] = 'Ayarlar alınırken bir hata oluştu: ' . $connection->error;
        }
    }
}

if (isset($_POST['action'])) {
    // Ensure user is logged in for any POST action
    if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
        $response = ['status' => 'error', 'message' => 'Yetkisiz erişim.'];
        echo json_encode($response);
        exit;
    }

    if ($_POST['action'] == 'update_settings') {
        if (isset($_POST['dolar_kuru']) && isset($_POST['euro_kuru'])) {
            $dolar_kuru = $_POST['dolar_kuru'];
            $euro_kuru = $_POST['euro_kuru'];

            // Update Dolar Kuru
            $stmt_dolar = $connection->prepare("UPDATE ayarlar SET ayar_deger = ? WHERE ayar_anahtar = 'dolar_kuru'");
            $stmt_dolar->bind_param('s', $dolar_kuru);
            $dolar_success = $stmt_dolar->execute();
            $stmt_dolar->close();

            // Update Euro Kuru
            $stmt_euro = $connection->prepare("UPDATE ayarlar SET ayar_deger = ? WHERE ayar_anahtar = 'euro_kuru'");
            $stmt_euro->bind_param('s', $euro_kuru);
            $euro_success = $stmt_euro->execute();
            $stmt_euro->close();

            if ($dolar_success && $euro_success) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "Dolar ve Euro kuru ayarları güncellendi", 'UPDATE');
                $response = ['status' => 'success', 'message' => 'Ayarlar başarıyla güncellendi.'];
            } else {
                $response['message'] = 'Ayarlar güncellenirken bir hata oluştu.';
            }
        } else {
            $response['message'] = 'Eksik parametreler.';
        }
    }

    if ($_POST['action'] == 'update_maintenance_mode') {
        if (isset($_POST['mode'])) {
            $mode = $_POST['mode'];
            // Validate the input
            if ($mode === 'on' || $mode === 'off') {
                // Use the shared update_setting function
                if (update_setting($connection, 'maintenance_mode', $mode)) {
                    $status_text = $mode === 'on' ? 'aktif' : 'devre dışı';
                    // Log ekleme
                    log_islem($connection, $_SESSION['kullanici_adi'], "Bakım modu $status_text olarak değiştirildi", 'UPDATE');
                    $response = ['status' => 'success', 'message' => "Bakım modu başarıyla {$status_text} bırakıldı."];
                } else {
                    $response = ['status' => 'error', 'message' => 'Bakım modu ayarı güncellenirken bir veritabanı hatası oluştu.'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Geçersiz mod değeri.'];
            }
        } else {
            $response['message'] = 'Eksik parametreler.';
        }
    }

    if ($_POST['action'] == 'update_telegram_settings') {
        if (isset($_POST['telegram_bot_token']) && isset($_POST['telegram_chat_id'])) {
            $bot_token = $_POST['telegram_bot_token'];
            $chat_id = $_POST['telegram_chat_id'];

            // Telegram Bot Token ve Chat ID'yi ayarlar tablosuna kaydet
            $stmt_token = $connection->prepare("INSERT INTO ayarlar (ayar_anahtar, ayar_deger) VALUES ('telegram_bot_token', ?) ON DUPLICATE KEY UPDATE ayar_deger = VALUES(ayar_deger)");
            $stmt_token->bind_param('s', $bot_token);
            $token_success = $stmt_token->execute();
            $stmt_token->close();

            $stmt_chat = $connection->prepare("INSERT INTO ayarlar (ayar_anahtar, ayar_deger) VALUES ('telegram_chat_id', ?) ON DUPLICATE KEY UPDATE ayar_deger = VALUES(ayar_deger)");
            $stmt_chat->bind_param('s', $chat_id);
            $chat_success = $stmt_chat->execute();
            $stmt_chat->close();

            if ($token_success && $chat_success) {
                // Log ekleme
                log_islem($connection, $_SESSION['kullanici_adi'], "Telegram ayarları güncellendi", 'UPDATE');
                $response = ['status' => 'success', 'message' => 'Telegram ayarları başarıyla güncellendi.'];
            } else {
                $response['message'] = 'Telegram ayarları güncellenirken bir hata oluştu.';
            }
        } else {
            $response['message'] = 'Eksik parametreler.';
        }
    }
}

echo json_encode($response);
?>

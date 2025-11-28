<?php
include '../config.php';
require_once '../includes/backup_functions.php'; // Include the new shared functions
require_once '../includes/telegram_functions.php'; // For sending notifications

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim.']);
    exit;
}

$backup_dir = '../yedekler/';
$response = ['status' => 'error', 'message' => 'Geçersiz işlem.'];

// Ensure the backup directory exists
if (!is_dir($backup_dir)) {
    // Only create it or error out if a write action is attempted
    if (isset($_POST['action']) && in_array($_POST['action'], ['create_backup', 'upload_and_restore'])) {
        if (!mkdir($backup_dir, 0777, true)) {
            echo json_encode(['status' => 'error', 'message' => 'Yedekleme dizini oluşturulamadı.']);
            exit;
        }
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'list_backups') {
        if (!is_dir($backup_dir)) {
            $response = ['status' => 'success', 'backups' => []]; // Return empty if dir doesn't exist
        } else {
            $files = scandir($backup_dir, SCANDIR_SORT_DESCENDING);
            $backups = [];
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $filepath = $backup_dir . $file;
                    $backups[] = [
                        'filename' => $file,
                        'size' => filesize($filepath),
                        'created_at' => date('Y-m-d H:i:s', filemtime($filepath))
                    ];
                }
            }
            $response = ['status' => 'success', 'backups' => $backups];
        }
    }
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'create_backup') {
        // The function from backup_functions.php now handles the backup and settings update
        $result = perform_automatic_backup($connection);
        // We adjust the message for manual action
        if ($result['status'] === 'success') {
            $result['message'] = 'Manuel veritabanı yedeği başarıyla oluşturuldu ve Telegram\'a gönderildi.';
        }
        $response = $result;
    }

    if ($action == 'send_telegram') {
        if (isset($_POST['filename'])) {
            $filename = basename($_POST['filename']);
            $filepath = $backup_dir . $filename;

            if (file_exists($filepath)) {
                $telegram_settings = get_telegram_settings($connection);
                if (!empty($telegram_settings['bot_token']) && !empty($telegram_settings['chat_id'])) {
                    $caption = "Manuel olarak gönderilen veritabanı yedeği: " . $filename;
                    if (sendTelegramFile($filepath, $caption, $telegram_settings['bot_token'], $telegram_settings['chat_id'])) {
                        $response = ['status' => 'success', 'message' => 'Yedek dosyası başarıyla Telegram\'a gönderildi.'];
                    } else {
                        $response = ['status' => 'error', 'message' => 'Yedek dosyası Telegram\'a gönderilirken bir hata oluştu.'];
                    }
                } else {
                    $response = ['status' => 'error', 'message' => 'Telegram ayarları eksik. Lütfen bot token ve chat ID\'yi kontrol edin.'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Yedek dosyası bulunamadı.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Dosya adı belirtilmedi.'];
        }
    }

    if ($action == 'upload_and_restore') {
        if (isset($_FILES['backupFile'])) {
            $file = $_FILES['backupFile'];

            // Basic validation
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $response = ['status' => 'error', 'message' => 'Dosya yüklenirken bir hata oluştu. Hata kodu: ' . $file['error']];
            } elseif (pathinfo($file['name'], PATHINFO_EXTENSION) != 'sql') {
                $response = ['status' => 'error', 'message' => 'Geçersiz dosya türü. Lütfen bir .sql dosyası yükleyin.'];
            } else {
                $filepath = $file['tmp_name'];

                if (restore_database($connection, $filepath)) {
                    $response = ['status' => 'success', 'message' => 'Veritabanı yüklenen dosyadan başarıyla geri yüklendi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Geri yükleme sırasında bir hata oluştu.'];
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Yedek dosyası yüklenmedi.'];
        }
    }

    if ($action == 'delete_backup') {
        if (isset($_POST['filename'])) {
            $file_to_delete = basename($_POST['filename']); // Sanitize
            $filepath = $backup_dir . $file_to_delete;

            if (file_exists($filepath) && is_file($filepath)) {
                if (unlink($filepath)) {
                    $response = ['status' => 'success', 'message' => 'Yedek dosyası başarıyla silindi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Yedek dosyası silinirken bir hata oluştu.'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Silinecek yedek dosyası bulunamadı.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Dosya adı belirtilmedi.'];
        }
    }

    if ($action == 'restore_backup') {
        if (isset($_POST['filename'])) {
            $file_to_restore = basename($_POST['filename']); // Sanitize to prevent directory traversal
            $filepath = $backup_dir . $file_to_restore;

            if (file_exists($filepath) && is_file($filepath)) {
                if (restore_database($connection, $filepath)) {
                    $response = ['status' => 'success', 'message' => 'Veritabanı başarıyla geri yüklendi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Geri yükleme sırasında bir hata oluştu.'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Yedek dosyası bulunamadı.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Dosya adı belirtilmedi.'];
        }
    }
}

echo json_encode($response);
?>
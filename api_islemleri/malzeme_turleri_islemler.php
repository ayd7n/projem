<?php
include '../config.php';

header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_turler':
        getTurler();
        break;
    case 'add_tur':
        addTur();
        break;
    case 'update_tur':
        updateTur();
        break;
    case 'delete_tur':
        deleteTur();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
}

function getTurler()
{
    global $connection;

    $result = $connection->query("SELECT id, value, label, sira FROM malzeme_turleri ORDER BY sira ASC, label ASC");
    $turler = [];

    while ($row = $result->fetch_assoc()) {
        $turler[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $turler]);
}

function addTur()
{
    global $connection;

    $value = trim($_POST['value'] ?? '');
    $label = trim($_POST['label'] ?? '');

    if (empty($value) || empty($label)) {
        echo json_encode(['status' => 'error', 'message' => 'Tür değeri ve ismi zorunludur.']);
        return;
    }

    // Değeri normalize et
    $value = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $value)));

    // Aynı değer var mı kontrol et
    $check = $connection->prepare("SELECT id FROM malzeme_turleri WHERE value = ?");
    $check->bind_param('s', $value);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Bu tür değeri zaten mevcut!']);
        return;
    }

    // Son sıra numarasını al
    $siraResult = $connection->query("SELECT MAX(sira) as max_sira FROM malzeme_turleri");
    $maxSira = $siraResult->fetch_assoc()['max_sira'] ?? 0;
    $sira = $maxSira + 1;

    $stmt = $connection->prepare("INSERT INTO malzeme_turleri (value, label, sira) VALUES (?, ?, ?)");
    $stmt->bind_param('ssi', $value, $label, $sira);

    if ($stmt->execute()) {
        $newId = $connection->insert_id;
        echo json_encode([
            'status' => 'success',
            'message' => 'Yeni tür eklendi.',
            'data' => ['id' => $newId, 'value' => $value, 'label' => $label, 'sira' => $sira]
        ]);

        // Log
        if (function_exists('log_islem')) {
            log_islem($connection, $_SESSION['kullanici_adi'] ?? 'Sistem', "Yeni malzeme türü eklendi: $label ($value)", 'CREATE');
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tür eklenirken hata oluştu.']);
    }
}

function updateTur()
{
    global $connection;

    $id = intval($_POST['id'] ?? 0);
    $label = trim($_POST['label'] ?? '');

    if ($id <= 0 || empty($label)) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz parametreler.']);
        return;
    }

    $stmt = $connection->prepare("UPDATE malzeme_turleri SET label = ? WHERE id = ?");
    $stmt->bind_param('si', $label, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Tür güncellendi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tür güncellenirken hata oluştu.']);
    }
}

function deleteTur()
{
    global $connection;

    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz tür ID.']);
        return;
    }

    // Önce türün bilgisini al (log için)
    $infoStmt = $connection->prepare("SELECT value, label FROM malzeme_turleri WHERE id = ?");
    $infoStmt->bind_param('i', $id);
    $infoStmt->execute();
    $info = $infoStmt->get_result()->fetch_assoc();

    $stmt = $connection->prepare("DELETE FROM malzeme_turleri WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Tür silindi.']);

        // Log
        if ($info && function_exists('log_islem')) {
            log_islem($connection, $_SESSION['kullanici_adi'] ?? 'Sistem', "Malzeme türü silindi: {$info['label']} ({$info['value']})", 'DELETE');
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tür silinirken hata oluştu.']);
    }
}

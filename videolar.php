<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['taraf'] ?? '') !== 'personel') {
    header('Location: login.php');
    exit;
}

$videoFolderName = 'videolar';
$videoFolderPath = __DIR__ . DIRECTORY_SEPARATOR . $videoFolderName;

if (!is_dir($videoFolderPath)) {
    mkdir($videoFolderPath, 0755, true);
}

$allowedExtensions = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];
$videos = [];

$entries = @scandir($videoFolderPath) ?: [];
foreach ($entries as $entry) {
    if ($entry === '.' || $entry === '..') {
        continue;
    }

    $fullPath = $videoFolderPath . DIRECTORY_SEPARATOR . $entry;
    if (!is_file($fullPath)) {
        continue;
    }

    $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        continue;
    }

    $createdTs = filectime($fullPath);
    if ($createdTs === false) {
        $createdTs = filemtime($fullPath);
    }

    $videos[] = [
        'name' => $entry,
        'url' => $videoFolderName . '/' . rawurlencode($entry),
        'size_mb' => round(filesize($fullPath) / 1024 / 1024, 2),
        'created_ts' => (int) $createdTs,
        'created_at' => date('d.m.Y H:i', $createdTs),
        'modified_ts' => (int) filemtime($fullPath),
        'modified_at' => date('d.m.Y H:i', filemtime($fullPath)),
    ];
}

usort($videos, static function (array $a, array $b): int {
    if ($a['created_ts'] === $b['created_ts']) {
        if ($a['modified_ts'] === $b['modified_ts']) {
            return strnatcasecmp($a['name'], $b['name']);
        }

        return $a['modified_ts'] <=> $b['modified_ts'];
    }

    return $a['created_ts'] <=> $b['created_ts'];
});
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videolar - Parfum ERP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --bg: #f8f7fb;
        }

        body {
            background: var(--bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand {
            color: var(--accent) !important;
            font-weight: 700;
        }

        .panel {
            border: 1px solid #e6e0ef;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(74, 14, 99, 0.08);
        }

        .video-list {
            max-height: 65vh;
            overflow: auto;
        }

        .video-item {
            cursor: pointer;
            border-bottom: 1px solid #f1ecf7;
            padding: 10px 12px;
        }

        .video-item:last-child {
            border-bottom: 0;
        }

        .video-item.active {
            background: #f3ebfb;
            border-left: 4px solid var(--secondary);
            padding-left: 8px;
        }

        .video-meta {
            color: #6b7280;
            font-size: 12px;
        }

        video {
            width: 100%;
            border-radius: 10px;
            background: #000;
            max-height: 70vh;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: linear-gradient(45deg, #4a0e63, #7c2a99);">
        <div class="container-fluid">
            <a class="navbar-brand" href="navigation.php"><i class="fas fa-spa"></i> IDO KOZMETIK</a>
            <div class="navbar-nav ml-auto">
                <a class="nav-link text-white" href="navigation.php"><i class="fas fa-home"></i> Ana Sayfa</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Video Merkezi</h4>
                <small class="text-muted">Klasor: <code><?php echo htmlspecialchars($videoFolderName, ENT_QUOTES, 'UTF-8'); ?></code></small>
            </div>
            <span class="badge badge-secondary p-2"><?php echo count($videos); ?> video</span>
        </div>

        <?php if (empty($videos)): ?>
            <div class="alert alert-warning">
                <strong>Video bulunamadi.</strong> Izlemek icin <code><?php echo htmlspecialchars($videoFolderPath, ENT_QUOTES, 'UTF-8'); ?></code> klasorune
                <code>.mp4</code>, <code>.webm</code>, <code>.ogg</code>, <code>.mov</code> veya <code>.m4v</code> dosyasi ekleyin.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="panel">
                        <div class="p-2 border-bottom font-weight-bold">
                            Videolar
                        </div>
                        <div class="video-list" id="videoList"></div>
                    </div>
                </div>
                <div class="col-lg-8 mb-3">
                    <div class="panel p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div id="selectedVideoName" class="font-weight-bold"></div>
                            <small id="selectedVideoMeta" class="text-muted"></small>
                        </div>
                        <video id="videoPlayer" controls preload="metadata"></video>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        (function () {
            const videos = <?php echo json_encode($videos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
            const listElement = document.getElementById('videoList');
            const player = document.getElementById('videoPlayer');
            const selectedName = document.getElementById('selectedVideoName');
            const selectedMeta = document.getElementById('selectedVideoMeta');

            if (!videos.length || !listElement || !player || !selectedName || !selectedMeta) {
                return;
            }

            function selectVideo(index) {
                const video = videos[index];
                if (!video) {
                    return;
                }

                player.src = video.url;
                selectedName.textContent = video.name;
                selectedMeta.textContent = video.size_mb + ' MB | Olusturma: ' + video.created_at;

                Array.from(listElement.querySelectorAll('.video-item')).forEach((node) => {
                    node.classList.remove('active');
                });

                const activeNode = listElement.querySelector('[data-index="' + index + '"]');
                if (activeNode) {
                    activeNode.classList.add('active');
                }
            }

            videos.forEach((video, index) => {
                const item = document.createElement('div');
                item.className = 'video-item';
                item.dataset.index = String(index);

                const nameNode = document.createElement('div');
                nameNode.className = 'font-weight-bold text-truncate';
                nameNode.title = video.name;
                nameNode.textContent = video.name;

                const metaNode = document.createElement('div');
                metaNode.className = 'video-meta';
                metaNode.textContent = video.size_mb + ' MB | Olusturma: ' + video.created_at;

                item.appendChild(nameNode);
                item.appendChild(metaNode);
                item.addEventListener('click', () => selectVideo(index));
                listElement.appendChild(item);
            });

            selectVideo(0);
        })();
    </script>
</body>
</html>

<?php
include '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['taraf'] !== 'personel') {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz eriÅŸim.']);
    exit;
}

// Determine request method and action
$request_method = $_SERVER['REQUEST_METHOD'];
$action = null;

// Check for action in GET parameters
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}
// Check for action in POST parameters
elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}
// Check for action in JSON input
else {
    $input = file_get_contents('php://input');
    $json = json_decode($input, true);
    if ($json && isset($json['action'])) {
        $action = $json['action'];
    }
}

// Initialize response
$response = ['status' => 'error', 'message' => 'GeÃ§ersiz istek.'];

// Handle GET requests
if ($request_method === 'GET' && $action) {
    if ($action === 'search_product_trees' && isset($_GET['searchTerm'])) {
        $searchTerm = $connection->real_escape_string($_GET['searchTerm']);
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'urun' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%') ORDER BY urun_kodu, bilesen_kodu";
        $result = $connection->query($query);

        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $product_trees
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'search_product_trees_paginated' && isset($_GET['searchTerm'])) {
        $searchTerm = $connection->real_escape_string($_GET['searchTerm']);
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        if ($limit > 200) $limit = 200;
        $offset = ($page - 1) * $limit;

        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'urun' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%') ORDER BY urun_kodu, bilesen_kodu LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);

        // Get total count for pagination
        $total_query = "SELECT COUNT(*) as total FROM urun_agaci WHERE agac_turu = 'urun' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%')";
        $total_result = $connection->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;

        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $product_trees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'search_essence_trees_paginated' && isset($_GET['searchTerm'])) {
        $searchTerm = $connection->real_escape_string($_GET['searchTerm']);
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        if ($limit > 200) $limit = 200;
        $offset = ($page - 1) * $limit;

        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'esans' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%') ORDER BY urun_kodu, bilesen_kodu LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);

        // Get total count for pagination
        $total_query = "SELECT COUNT(*) as total FROM urun_agaci WHERE agac_turu = 'esans' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%')";
        $total_result = $connection->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;

        if ($result) {
            $essence_trees = [];
            while ($row = $result->fetch_assoc()) {
                $essence_trees[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $essence_trees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'get_all') {
        // Fetch all product trees
        $query = "SELECT * FROM urun_agaci ORDER BY urun_kodu, bilesen_kodu";
        $result = $connection->query($query);

        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }

            // Calculate total distinct products in product trees
            $total_query = "SELECT COUNT(DISTINCT urun_kodu) as total FROM urun_agaci";
            $total_result = $connection->query($total_query);
            $total_row = $total_result->fetch_assoc();
            $total = $total_row ? $total_row['total'] : 0;

            $response = [
                'status' => 'success',
                'data' => $product_trees,
                'total' => $total
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'get_product_tree' && isset($_GET['id'])) {
        $urun_agaci_id = (int) $_GET['id'];
        $query = "SELECT * FROM urun_agaci WHERE urun_agaci_id = $urun_agaci_id";
        $result = $connection->query($query);
        $product_tree = $result->fetch_assoc();

        if ($product_tree) {
            $response = ['status' => 'success', 'data' => $product_tree];
        } else {
            $response = ['status' => 'error', 'message' => 'ÃœrÃ¼n aÄŸacÄ± bulunamadÄ±.'];
        }
    } elseif ($action === 'get_products') {
        // Fetch all products
        $query = "SELECT urun_kodu, urun_ismi FROM urunler ORDER BY urun_ismi";
        $result = $connection->query($query);

        if ($result) {
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $products
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'get_materials') {
        // Fetch all materials
        $query = "SELECT malzeme_kodu, malzeme_ismi, malzeme_turu, birim FROM malzemeler ORDER BY malzeme_ismi";
        $result = $connection->query($query);

        if ($result) {
            $materials = [];
            while ($row = $result->fetch_assoc()) {
                $materials[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $materials
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'get_essences') {
        // Fetch all essences
        $query = "SELECT esans_id, esans_kodu, esans_ismi, birim FROM esanslar ORDER BY esans_ismi";
        $result = $connection->query($query);

        if ($result) {
            $essences = [];
            while ($row = $result->fetch_assoc()) {
                $essences[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $essences
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'search_essence_trees' && isset($_GET['searchTerm'])) {
        $searchTerm = $connection->real_escape_string($_GET['searchTerm']);
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'esans' AND (urun_kodu LIKE '%$searchTerm%' OR urun_ismi LIKE '%$searchTerm%' OR bilesen_kodu LIKE '%$searchTerm%' OR bilesen_ismi LIKE '%$searchTerm%') ORDER BY urun_kodu, bilesen_kodu";
        $result = $connection->query($query);

        if ($result) {
            $essence_trees = [];
            while ($row = $result->fetch_assoc()) {
                $essence_trees[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $essence_trees
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'get_product_trees_paginated') {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        if ($limit > 200) $limit = 200;
        $offset = ($page - 1) * $limit;

        // Fetch paginated product trees
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'urun' ORDER BY urun_kodu, bilesen_kodu LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);

        // Get total count for pagination
        $total_query = "SELECT COUNT(*) as total FROM urun_agaci WHERE agac_turu = 'urun'";
        $total_result = $connection->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;

        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $product_trees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'get_essence_trees_paginated') {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        if ($limit > 200) $limit = 200;
        $offset = ($page - 1) * $limit;

        // Fetch paginated essence trees
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = 'esans' ORDER BY urun_kodu, bilesen_kodu LIMIT $limit OFFSET $offset";
        $result = $connection->query($query);

        // Get total count for pagination
        $total_query = "SELECT COUNT(*) as total FROM urun_agaci WHERE agac_turu = 'esans'";
        $total_result = $connection->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;

        if ($result) {
            $essence_trees = [];
            while ($row = $result->fetch_assoc()) {
                $essence_trees[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $essence_trees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    } elseif ($action === 'get_product_tree_hierarchy' && isset($_GET['urun_kodu'])) {
        // ÃœrÃ¼n koduna gÃ¶re hiyerarÅŸik aÄŸaÃ§ yapÄ±sÄ±nÄ± getir
        $urun_kodu = (int) $_GET['urun_kodu'];

        // Ã–nce Ã¼rÃ¼n bilgilerini al
        $urun_query = "SELECT urun_kodu, urun_ismi FROM urunler WHERE urun_kodu = $urun_kodu";
        $urun_result = $connection->query($urun_query);
        $urun = $urun_result->fetch_assoc();

        if (!$urun) {
            $response = ['status' => 'error', 'message' => 'ÃœrÃ¼n bulunamadÄ±.'];
        } else {
            // ÃœrÃ¼nÃ¼n doÄŸrudan bileÅŸenlerini getir (agac_turu = 'urun')
            $bilesen_query = "SELECT * FROM urun_agaci WHERE urun_kodu = $urun_kodu AND agac_turu = 'urun' ORDER BY bilesenin_malzeme_turu, bilesen_ismi";
            $bilesen_result = $connection->query($bilesen_query);

            $children = [];

            while ($bilesen = $bilesen_result->fetch_assoc()) {
                $child = [
                    'name' => $bilesen['bilesen_ismi'],
                    'type' => $bilesen['bilesenin_malzeme_turu'],
                    'code' => $bilesen['bilesen_kodu'],
                    'quantity' => floatval($bilesen['bilesen_miktari']),
                    'children' => []
                ];

                // EÄŸer bileÅŸen bir esans ise, esansÄ±n alt bileÅŸenlerini de getir
                if (strtolower($bilesen['bilesenin_malzeme_turu']) === 'esans') {
                    // Esans koduna gÃ¶re esans aÄŸacÄ±nÄ± getir
                    $esans_kodu = $bilesen['bilesen_kodu'];

                    // Ã–nce esans_id'yi bul (esans_kodu veya esans_id ile eÅŸleÅŸebilir)
                    $esans_query = "SELECT esans_id, esans_kodu FROM esanslar WHERE esans_kodu = '" . $connection->real_escape_string($esans_kodu) . "'";
                    $esans_result = $connection->query($esans_query);
                    $esans = $esans_result->fetch_assoc();

                    if ($esans) {
                        // EsansÄ±n bileÅŸenlerini getir (agac_turu = 'esans')
                        // urun_kodu esans_id veya esans_kodu olabilir
                        $esans_bilesen_query = "SELECT * FROM urun_agaci WHERE (urun_kodu = '" . $esans['esans_id'] . "' OR urun_kodu = '" . $connection->real_escape_string($esans_kodu) . "') AND agac_turu = 'esans' ORDER BY bilesen_ismi";
                        $esans_bilesen_result = $connection->query($esans_bilesen_query);

                        while ($esans_bilesen = $esans_bilesen_result->fetch_assoc()) {
                            $child['children'][] = [
                                'name' => $esans_bilesen['bilesen_ismi'],
                                'type' => $esans_bilesen['bilesenin_malzeme_turu'],
                                'code' => $esans_bilesen['bilesen_kodu'],
                                'quantity' => floatval($esans_bilesen['bilesen_miktari']),
                                'children' => []
                            ];
                        }
                    }
                }

                $children[] = $child;
            }

            $tree_data = [
                'name' => $urun['urun_ismi'],
                'type' => 'urun',
                'code' => $urun['urun_kodu'],
                'quantity' => 1,
                'children' => $children
            ];

            $response = [
                'status' => 'success',
                'data' => $tree_data
            ];
        }
    }
}
// Handle POST requests
elseif ($request_method === 'POST') {
    // Extract action from either JSON or form data
    $input = file_get_contents('php://input');
    $json = json_decode($input, true);

    $roundTo4 = function ($value) {
        return round((float) $value, 4);
    };

    $parseDecimalText = function ($value) {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', trim((string) $value));
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    };

    $parsePercentText = function ($value) use ($roundTo4, $parseDecimalText) {
        $rawText = trim((string) $value);
        if ($rawText === '' || strpos($rawText, '%') === false) {
            return null;
        }

        $percentText = str_replace('%', '', $rawText);
        $parsedPercent = $parseDecimalText($percentText);
        if ($parsedPercent === null || $parsedPercent <= 0) {
            return null;
        }

        return $roundTo4($parsedPercent / 100);
    };

    $parseBilesenMiktari = function ($rawMiktar, $rawCoverage, &$errorMessage) use ($roundTo4, $parseDecimalText, $parsePercentText) {
        $errorMessage = '';

        if ($rawCoverage !== null && $rawCoverage !== '') {
            $coverageRaw = trim((string) $rawCoverage);
            if (!preg_match('/^\d+$/', $coverageRaw)) {
                $errorMessage = 'Bilesen miktari gecersiz. Ornek: 6 yazin (1 bilesen 6 urune yetiyorsa).';
                return null;
            }

            $coverage = (int) $coverageRaw;
            if ($coverage <= 0) {
                $errorMessage = 'Bilesen miktari gecersiz. Ornek: 6 yazin (1 bilesen 6 urune yetiyorsa).';
                return null;
            }

            return $roundTo4(1 / $coverage);
        }

        if ($rawMiktar === null || $rawMiktar === '') {
            $errorMessage = 'Bilesen miktari bos olamaz.';
            return null;
        }

        $rawText = trim((string) $rawMiktar);
        $parsedPercent = $parsePercentText($rawText);
        if ($parsedPercent !== null) {
            return $parsedPercent;
        }

        if (strpos($rawText, '/') !== false) {
            $parts = explode('/', $rawText);
            if (count($parts) !== 2) {
                $errorMessage = 'Bilesen miktari gecersiz. Ornek: 1/6, 0,25 veya 0.25';
                return null;
            }

            $pay = $parseDecimalText($parts[0]);
            $payda = $parseDecimalText($parts[1]);
            if ($pay === null || $payda === null || $pay <= 0 || $payda <= 0) {
                $errorMessage = 'Bilesen miktari gecersiz. Ornek: 1/6, 0,25 veya 0.25';
                return null;
            }

            return $roundTo4($pay / $payda);
        }

        $parsedValue = $parseDecimalText($rawMiktar);
        if ($parsedValue === null || $parsedValue <= 0) {
            $errorMessage = 'Bilesen miktari gecersiz. Ornek: 1/6, 0,25 veya 0.25';
            return null;
        }

        return $roundTo4($parsedValue);
    };

    $urun_kodu = null;
    $urun_ismi = '';
    $bilesenin_malzeme_turu = '';
    $bilesen_kodu = '';
    $bilesen_ismi = '';
    $bilesen_miktari_raw = null;
    $kapsadigi_urun_adedi = null;
    $urun_agaci_id = null;
    $agac_turu = 'urun';

    if ($json && isset($json['action'])) {
        // JSON request - use the decoded values
        $action = $json['action'];
        $urun_kodu = $json['urun_kodu'] ?? null;
        $urun_ismi = $json['urun_ismi'] ?? '';
        $bilesenin_malzeme_turu = $json['bilesenin_malzeme_turu'] ?? '';
        $bilesen_kodu = $json['bilesen_kodu'] ?? '';
        $bilesen_ismi = $json['bilesen_ismi'] ?? '';
        $bilesen_miktari_raw = $json['bilesen_miktari'] ?? null;
        $kapsadigi_urun_adedi = $json['kapsadigi_urun_adedi'] ?? null;
        $urun_agaci_id = $json['urun_agaci_id'] ?? null;
        $agac_turu = $json['agac_turu'] ?? 'urun';
    } elseif (isset($_POST['action'])) {
        // Form data request - use $_POST values
        $action = $_POST['action'];
        $urun_kodu = $_POST['urun_kodu'] ?? null;
        $urun_ismi = $_POST['urun_ismi'] ?? '';
        $bilesenin_malzeme_turu = $_POST['bilesenin_malzeme_turu'] ?? '';
        $bilesen_kodu = $_POST['bilesen_kodu'] ?? '';
        $bilesen_ismi = $_POST['bilesen_ismi'] ?? '';
        $bilesen_miktari_raw = $_POST['bilesen_miktari'] ?? null;
        $kapsadigi_urun_adedi = $_POST['kapsadigi_urun_adedi'] ?? null;
        $urun_agaci_id = $_POST['urun_agaci_id'] ?? null;
        $agac_turu = $_POST['agac_turu'] ?? 'urun';
    }

    if ($action === 'add_product_tree') {
        if (empty($urun_kodu) || empty($bilesen_kodu)) {
            $response = ['status' => 'error', 'message' => 'Urun ve bilesen bos olamaz.'];
        } else {
            $parseError = '';
            $bilesen_miktari = $parseBilesenMiktari($bilesen_miktari_raw, $kapsadigi_urun_adedi, $parseError);
            if ($bilesen_miktari === null) {
                $response = ['status' => 'error', 'message' => $parseError];
            } else {
                $urun_kodu = $connection->real_escape_string($urun_kodu);
                $urun_ismi = $connection->real_escape_string($urun_ismi);
                $bilesenin_malzeme_turu = $connection->real_escape_string($bilesenin_malzeme_turu);
                $bilesen_kodu = $connection->real_escape_string($bilesen_kodu);
                $bilesen_ismi = $connection->real_escape_string($bilesen_ismi);
                $agac_turu = $connection->real_escape_string($agac_turu);
                $bilesen_miktari_sql = number_format($bilesen_miktari, 4, '.', '');

                $query = "INSERT INTO urun_agaci (urun_kodu, urun_ismi, bilesenin_malzeme_turu, bilesen_kodu, bilesen_ismi, bilesen_miktari, agac_turu) VALUES ('$urun_kodu', '$urun_ismi', '$bilesenin_malzeme_turu', '$bilesen_kodu', '$bilesen_ismi', '$bilesen_miktari_sql', '$agac_turu')";

                if ($connection->query($query)) {
                    log_islem($connection, $_SESSION['kullanici_adi'], "$urun_ismi urun agacina $bilesen_ismi bileseni eklendi", 'CREATE');
                    $response = ['status' => 'success', 'message' => 'Urun agaci basariyla eklendi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Veritabani hatasi: ' . $connection->error];
                }
            }
        }
    } elseif ($action === 'update_product_tree') {
        if (isset($_POST['urun_agaci_id'])) {
            $urun_agaci_id = (int) $_POST['urun_agaci_id'];
        } elseif (isset($json['urun_agaci_id'])) {
            $urun_agaci_id = (int) $json['urun_agaci_id'];
        } else {
            $urun_agaci_id = null;
        }

        if ($urun_agaci_id && (empty($urun_kodu) || empty($bilesen_kodu))) {
            $response = ['status' => 'error', 'message' => 'Urun ve bilesen bos olamaz.'];
        } elseif ($urun_agaci_id) {
            $parseError = '';
            $bilesen_miktari = $parseBilesenMiktari($bilesen_miktari_raw, $kapsadigi_urun_adedi, $parseError);
            if ($bilesen_miktari === null) {
                $response = ['status' => 'error', 'message' => $parseError];
            } else {
                $urun_kodu = $connection->real_escape_string($urun_kodu);
                $urun_ismi = $connection->real_escape_string($urun_ismi);
                $bilesenin_malzeme_turu = $connection->real_escape_string($bilesenin_malzeme_turu);
                $bilesen_kodu = $connection->real_escape_string($bilesen_kodu);
                $bilesen_ismi = $connection->real_escape_string($bilesen_ismi);
                $agac_turu = $connection->real_escape_string($agac_turu);
                $bilesen_miktari_sql = number_format($bilesen_miktari, 4, '.', '');

                $old_tree_query = "SELECT urun_ismi, bilesen_ismi FROM urun_agaci WHERE urun_agaci_id = $urun_agaci_id";
                $old_tree_result = $connection->query($old_tree_query);
                $old_tree = $old_tree_result->fetch_assoc();
                $old_product_name = $old_tree['urun_ismi'] ?? 'Bilinmeyen Urun';
                $old_component_name = $old_tree['bilesen_ismi'] ?? 'Bilinmeyen Bilesen';

                $query = "UPDATE urun_agaci SET urun_kodu = '$urun_kodu', urun_ismi = '$urun_ismi', bilesenin_malzeme_turu = '$bilesenin_malzeme_turu', bilesen_kodu = '$bilesen_kodu', bilesen_ismi = '$bilesen_ismi', bilesen_miktari = '$bilesen_miktari_sql', agac_turu = '$agac_turu' WHERE urun_agaci_id = $urun_agaci_id";

                if ($connection->query($query)) {
                    log_islem($connection, $_SESSION['kullanici_adi'], "$old_product_name urun agacindaki $old_component_name bileseni $bilesen_ismi olarak guncellendi", 'UPDATE');
                    $response = ['status' => 'success', 'message' => 'Urun agaci basariyla guncellendi.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Veritabani hatasi: ' . $connection->error];
                }
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Gecersiz urun agaci ID.'];
        }
    } elseif ($action === 'delete_product_tree') {
        if (isset($_POST['urun_agaci_id'])) {
            $urun_agaci_id = (int) $_POST['urun_agaci_id'];
        } elseif (isset($json['urun_agaci_id'])) {
            $urun_agaci_id = (int) $json['urun_agaci_id'];
        } else {
            $urun_agaci_id = null;
        }

        if ($urun_agaci_id) {
            $deleted_tree_query = "SELECT urun_ismi, bilesen_ismi FROM urun_agaci WHERE urun_agaci_id = $urun_agaci_id";
            $deleted_tree_result = $connection->query($deleted_tree_query);
            $deleted_tree = $deleted_tree_result->fetch_assoc();
            $deleted_product_name = $deleted_tree['urun_ismi'] ?? 'Bilinmeyen Urun';
            $deleted_component_name = $deleted_tree['bilesen_ismi'] ?? 'Bilinmeyen Bilesen';

            $query = "DELETE FROM urun_agaci WHERE urun_agaci_id = $urun_agaci_id";
            if ($connection->query($query)) {
                log_islem($connection, $_SESSION['kullanici_adi'], "$deleted_product_name urun agacindan $deleted_component_name bileseni silindi", 'DELETE');
                $response = ['status' => 'success', 'message' => 'Urun agaci basariyla silindi.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Veritabani hatasi: ' . $connection->error];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Gecersiz urun agaci ID.'];
        }
    }
}


// NEW: Handle GET requests for specific agac_turu (for Vue.js)
if ($request_method === 'GET' && isset($_GET['agac_turu'])) {
    $agac_turu = $connection->real_escape_string($_GET['agac_turu']);

    // Validate agac_turu parameter
    if (!in_array($agac_turu, ['urun', 'esans'])) {
        $response = ['status' => 'error', 'message' => 'GeÃ§ersiz agac_turu parametresi.'];
    } else {
        $query = "SELECT * FROM urun_agaci WHERE agac_turu = '$agac_turu' ORDER BY urun_kodu, bilesen_kodu";
        $result = $connection->query($query);

        if ($result) {
            $product_trees = [];
            while ($row = $result->fetch_assoc()) {
                $product_trees[] = $row;
            }

            $response = [
                'status' => 'success',
                'data' => $product_trees
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'VeritabanÄ± hatasÄ±: ' . $connection->error];
        }
    }
}

$connection->close();
echo json_encode($response);
?>

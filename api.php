<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/shalom.php';

$pdo = get_db();
$action = $_GET['action'] ?? '';

// Prevent PHP notices/warnings from breaking AJAX JSON parsing
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

$api_key = get_setting('SHALOM_API_KEY');

// --- STATUS & VERIFICATION ---
if ($action === 'status') {
    $has_key = !empty($api_key);
    $status_data = [
        'configured' => $has_key,
        'valid' => false,
        'message' => $has_key ? 'Clave configurada.' : 'No hay clave configurada.',
        'data' => null,
        'settings' => get_all_settings(),
        'db_stats' => [
            'tracking_count' => (int)$pdo->query("SELECT COUNT(*) FROM tracking_history")->fetchColumn(),
            'agencies_count' => (int)$pdo->query("SELECT COUNT(*) FROM agencies")->fetchColumn(),
            'dni_cache_count' => (int)$pdo->query("SELECT COUNT(*) FROM dni_cache")->fetchColumn()
        ]
    ];

    if ($has_key) {
        $res = make_api_request('/account', 'GET', null, $api_key);
        if ($res['http_code'] === 200 && !empty($res['data']['success'])) {
            $status_data['valid'] = true;
            $status_data['message'] = 'API Key válida y activa.';
            $status_data['data'] = $res['data']['data'] ?? [];
        } else {
            $status_data['message'] = 'API Key inválida o expirada.';
        }
    }

    echo json_encode($status_data);
    exit;
}

// --- SAVE SETTINGS ---
if ($action === 'save_settings') {
    $input = json_decode(file_get_contents('php://input'), true);
    $apiKey = trim($input['apiKey'] ?? '');
    $instanceId = trim($input['instanceId'] ?? '');
    $defaultOrigin = trim($input['defaultOrigin'] ?? '');

    if ($apiKey !== '') {
        $test = make_api_request('/account', 'GET', null, $apiKey);
        if ($test['http_code'] !== 200 || empty($test['data']['success'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'API Key inválida: ' . ($test['data']['message'] ?? 'Verifique su clave en shalom-api.lat')
            ]);
            exit;
        }
        save_setting('SHALOM_API_KEY', $apiKey);
    }

    save_setting('SHALOM_INSTANCE_ID', $instanceId);
    save_setting('DEFAULT_ORIGIN', $defaultOrigin);

    echo json_encode([
        'success' => true,
        'message' => 'Configuración guardada correctamente.'
    ]);
    exit;
}

// --- GET SETTINGS ---
if ($action === 'get_settings') {
    echo json_encode([
        'success' => true,
        'settings' => get_all_settings()
    ]);
    exit;
}

// --- CLEAR HISTORY ---
if ($action === 'clear_history') {
    $pdo->exec("DELETE FROM tracking_history");
    echo json_encode(['success' => true, 'message' => 'Historial de guías vaciado']);
    exit;
}

// --- TRACK BY OSE_ID (QR CODE SCAN) ---
if ($action === 'track_by_ose') {
    $ose_id = preg_replace('/\D/', '', $_GET['ose_id'] ?? '');
    if (!$ose_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere el identificador de orden (ose_id).']);
        exit;
    }

    // 1. Check if we already have this ose_id in tracking_history
    $stmt = $pdo->prepare("SELECT * FROM tracking_history WHERE raw_search_json LIKE :ose LIMIT 1");
    $stmt->execute([':ose' => '%"ose_id":' . $ose_id . '%']);
    $cached = $stmt->fetch();
    if ($cached) {
        echo json_encode([
            'success' => true,
            'from_cache' => true,
            'orderNumber' => $cached['order_number'],
            'orderCode' => $cached['order_code']
        ]);
        exit;
    }

    // 2. Query Shalom API /track/by-ose
    if (!$api_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Debes configurar tu SHALOM_API_KEY para resolver códigos QR.']);
        exit;
    }

    $res = make_api_request('/track/by-ose?ose_id=' . $ose_id, 'GET', null, $api_key);
    if ($res['http_code'] === 200 && !empty($res['data']['success'])) {
        $data = $res['data']['data'] ?? [];
        $orderNumber = $data['orderNumber'] ?? '';
        $orderCode = $data['orderCode'] ?? '';

        if ($orderNumber && $orderCode) {
            echo json_encode([
                'success' => true,
                'orderNumber' => $orderNumber,
                'orderCode' => $orderCode,
                'ose_data' => $data
            ]);
            exit;
        }
    }

    http_response_code($res['http_code'] ?: 404);
    echo json_encode([
        'error' => $res['data']['message'] ?? 'No se pudo encontrar la guía asociada a este código QR.'
    ]);
    exit;
}

// --- TRACKING WITH CACHE & FORCED REFRESH ---
if ($action === 'track') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $orderNumber = trim($input['orderNumber'] ?? $_POST['orderNumber'] ?? $_GET['order_number'] ?? $_GET['orderNumber'] ?? '');
    $orderCode = strtoupper(trim($input['orderCode'] ?? $_POST['orderCode'] ?? $_GET['order_code'] ?? $_GET['orderCode'] ?? ''));
    $force = !empty($input['force']) || !empty($_POST['force']) || !empty($_GET['force']);

    if (!$orderNumber || !$orderCode) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere número de orden y código de seguridad.']);
        exit;
    }

    // 1. Si no es forzada, verificar si ya está guardada
    if (!$force) {
        $stmt = $pdo->prepare("SELECT * FROM tracking_history WHERE order_number = :num AND order_code = :code LIMIT 1");
        $stmt->execute([':num' => $orderNumber, ':code' => $orderCode]);
        $cached = $stmt->fetch();

        if ($cached) {
            $search = json_decode($cached['raw_search_json'], true) ?: [];
            if (!isset($search['success']) && isset($search['data'])) {
                $search['success'] = true;
            }
            echo json_encode([
                'from_cache' => true,
                'cached_at' => $cached['updated_at'],
                'search' => $search,
                'statuses' => json_decode($cached['raw_statuses_json'], true) ?: []
            ]);
            exit;
        }
    }

    // 2. Si no está en caché o es forzada, consultar a la API de Shalom
    if (!$api_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Debes configurar tu SHALOM_API_KEY para consultar la API de Shalom.']);
        exit;
    }

    $res = make_api_request('/track', 'POST', [
        'orderNumber' => $orderNumber,
        'orderCode' => $orderCode,
    ], $api_key);

    if ($res['http_code'] === 200 && !empty($res['data']['search']['success'])) {
        $info = $res['data']['search']['data'] ?? [];
        $statuses = $res['data']['statuses'] ?? [];

        $stmt = $pdo->prepare("
            INSERT INTO tracking_history (
                order_number, order_code, status_message, content, origin_name,
                destination_name, sender_name, receiver_name, amount, invoice,
                raw_search_json, raw_statuses_json, updated_at
            ) VALUES (
                :num, :code, :status, :content, :origin,
                :dest, :sender, :receiver, :amount, :invoice,
                :raw_search, :raw_statuses, CURRENT_TIMESTAMP
            ) ON CONFLICT(order_number, order_code) DO UPDATE SET
                status_message = excluded.status_message,
                content = excluded.content,
                origin_name = excluded.origin_name,
                destination_name = excluded.destination_name,
                sender_name = excluded.sender_name,
                receiver_name = excluded.receiver_name,
                amount = excluded.amount,
                invoice = excluded.invoice,
                raw_search_json = excluded.raw_search_json,
                raw_statuses_json = excluded.raw_statuses_json,
                updated_at = CURRENT_TIMESTAMP
        ");

        $origin_val = is_array($info['origen'] ?? null) ? ($info['origen']['nombre'] ?? '') : ($info['origen'] ?? '');
        $dest_val = is_array($info['destino'] ?? null) ? ($info['destino']['nombre'] ?? '') : ($info['destino'] ?? '');
        $sender_val = is_array($info['remitente'] ?? null) ? ($info['remitente']['nombre'] ?? '') : ($info['remitente'] ?? '');
        $receiver_val = is_array($info['destinatario'] ?? null) ? ($info['destinatario']['nombre'] ?? '') : ($info['destinatario'] ?? '');

        $invoice_val = '';
        if (is_array($info['comprobante'] ?? null)) {
            $comp = $info['comprobante'];
            $s = trim($comp['serie'] ?? '');
            $n = trim($comp['numero'] ?? '');
            if ($s || $n) {
                $invoice_val = trim("{$s}-{$n}", "-");
            } elseif (!empty($comp['tipo'])) {
                $invoice_val = (string)$comp['tipo'];
            }
        } elseif (is_string($info['comprobante'] ?? null)) {
            $invoice_val = $info['comprobante'];
        } elseif (!empty($info['ticket'])) {
            $invoice_val = is_string($info['ticket']) ? $info['ticket'] : '';
        }

        $amount_val = '';
        if (isset($info['monto'])) {
            $amount_val = is_array($info['monto']) ? '' : (string)$info['monto'];
        } elseif (isset($info['pago'])) {
            $amount_val = is_array($info['pago']) ? '' : (string)$info['pago'];
        }

        $stmt->execute([
            ':num' => $orderNumber,
            ':code' => $orderCode,
            ':status' => $statuses['message'] ?? (!empty($info['entregado']) ? 'Entregado' : 'Registrado'),
            ':content' => $info['contenido'] ?? '',
            ':origin' => $origin_val,
            ':dest' => $dest_val,
            ':sender' => $sender_val,
            ':receiver' => $receiver_val,
            ':amount' => $amount_val,
            ':invoice' => $invoice_val,
            ':raw_search' => json_encode($res['data']['search']),
            ':raw_statuses' => json_encode($res['data']['statuses'] ?? [])
        ]);

        $res['data']['from_cache'] = false;
        $res['data']['cached_at'] = date('Y-m-d H:i:s');
    }

    http_response_code($res['http_code']);
    echo json_encode($res['data']);
    exit;
}

// --- RECENT TRACKING HISTORY ---
if ($action === 'recent_tracks') {
    $stmt = $pdo->query("SELECT id, order_number, order_code, status_message, content, origin_name, destination_name, sender_name, receiver_name, amount, raw_statuses_json, raw_search_json, updated_at FROM tracking_history ORDER BY updated_at DESC LIMIT 25");
    $rows = $stmt->fetchAll();
    $results = [];
    foreach ($rows as $row) {
        $status_date = '';
        $st = json_decode($row['raw_statuses_json'] ?? '', true);
        $st_data = is_array($st) ? ($st['data'] ?? []) : [];
        if (is_array($st_data)) {
            foreach (['entregado', 'destino', 'transito', 'demora', 'origen', 'registrado'] as $k) {
                if (!empty($st_data[$k]['fecha'])) {
                    $status_date = $st_data[$k]['fecha'];
                    break;
                }
            }
        }
        if (!$status_date) {
            $sr = json_decode($row['raw_search_json'] ?? '', true);
            $status_date = $sr['data']['fecha_traslado'] ?? $sr['data']['fecha_emision'] ?? $row['updated_at'];
        }

        $results[] = [
            'id' => (int)$row['id'],
            'order_number' => $row['order_number'],
            'order_code' => $row['order_code'],
            'status_message' => $row['status_message'] ?: 'Registrado',
            'status_date' => $status_date ?: $row['updated_at'],
            'receiver_name' => $row['receiver_name'] ?: '-',
            'content' => $row['content'] ?: '-',
            'amount' => $row['amount'] ?: '',
            'origin_name' => $row['origin_name'] ?: '',
            'destination_name' => $row['destination_name'] ?: '',
            'updated_at' => $row['updated_at']
        ];
    }
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}

// --- DELETE TRACK ---
if ($action === 'delete_track') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM tracking_history WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
    exit;
}

// --- SYNC AGENCIES ---
if ($action === 'sync_agencies') {
    $res = sync_agencies_to_db($pdo, $api_key);
    if ($res['success']) {
        echo json_encode(['success' => true, 'message' => "Se sincronizaron {$res['count']} agencias correctamente.", 'count' => $res['count']]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $res['error']]);
    }
    exit;
}

// --- AGENCIES CATALOG & NEARBY QUERY ---
if ($action === 'agencies') {
    $count = (int)$pdo->query("SELECT COUNT(*) FROM agencies")->fetchColumn();
    if ($count === 0) {
        sync_agencies_to_db($pdo, $api_key);
    }

    $q = trim($_GET['q'] ?? '');
    $departamento = trim($_GET['departamento'] ?? '');
    $aereo = !empty($_GET['aereo']) && ($_GET['aereo'] === 'true' || $_GET['aereo'] === '1');
    $near = trim($_GET['near'] ?? '');
    $per_page = min(100, max(1, (int)($_GET['per_page'] ?? 30)));

    $select = "SELECT ter_id, ter_abrebiatura, nombre, direccion, departamento, provincia, zona, ter_zona, latitud, longitud, telefono, hora_atencion, estado_agencia, ter_aereo";
    $params = [];

    $user_lat = null;
    $user_lng = null;
    if ($near && strpos($near, ',') !== false) {
        list($ulat, $ulng) = explode(',', $near, 2);
        if (is_numeric($ulat) && is_numeric($ulng)) {
            $user_lat = (float)$ulat;
            $user_lng = (float)$ulng;
            $select .= ", ROUND(haversine(:ulat, :ulng, latitud, longitud), 2) AS distancia_km";
            $params[':ulat'] = $user_lat;
            $params[':ulng'] = $user_lng;
        }
    }

    $sql = "{$select} FROM agencies WHERE 1=1";

    if ($q !== '') {
        $sql .= " AND (nombre LIKE :q OR direccion LIKE :q OR departamento LIKE :q OR provincia LIKE :q OR zona LIKE :q)";
        $params[':q'] = "%{$q}%";
    }
    if ($departamento !== '') {
        $sql .= " AND departamento LIKE :dep";
        $params[':dep'] = "%{$departamento}%";
    }
    if ($aereo) {
        $sql .= " AND ter_aereo = 1";
    }

    if ($user_lat !== null) {
        $sql .= " ORDER BY distancia_km ASC LIMIT :limit";
    } else {
        $sql .= " ORDER BY departamento ASC, provincia ASC, nombre ASC LIMIT :limit";
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $agenciesList = $stmt->fetchAll();

    $last_synced = $pdo->query("SELECT value FROM system_meta WHERE key='agencies_last_synced'")->fetchColumn() ?: null;

    echo json_encode([
        'success' => true,
        'from_cache' => true,
        'last_synced' => $last_synced,
        'total' => count($agenciesList),
        'data' => $agenciesList
    ]);
    exit;
}

// --- QUOTE ---
if ($action === 'quote') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $origin = !empty($input['origin']) ? $input['origin'] : get_setting('DEFAULT_ORIGIN');
    $destination = $input['destination'] ?? '';

    if (empty($origin) || empty($destination)) {
        http_response_code(400);
        echo json_encode(['error' => 'Debes indicar origen y destino.']);
        exit;
    }

    if (!$api_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Debes configurar tu SHALOM_API_KEY para cotizar envíos.']);
        exit;
    }

    $payload = [
        'origin' => is_numeric($origin) ? (int)$origin : $origin,
        'destination' => is_numeric($destination) ? (int)$destination : $destination,
    ];

    $instId = !empty($input['instanceId']) ? $input['instanceId'] : get_setting('SHALOM_INSTANCE_ID');
    if (!empty($instId)) {
        $payload['instanceId'] = $instId;
    }

    if (!empty($input['width']) && !empty($input['height']) && !empty($input['length']) && !empty($input['weight'])) {
        $payload['width'] = (float)$input['width'];
        $payload['height'] = (float)$input['height'];
        $payload['length'] = (float)$input['length'];
        $payload['weight'] = (float)$input['weight'];
    }

    $res = make_api_request('/account/quote', 'POST', $payload, $api_key);
    http_response_code($res['http_code']);
    echo json_encode($res['data']);
    exit;
}

// --- DNI ---
if ($action === 'dni') {
    $dni = preg_replace('/\D/', '', $_GET['dni'] ?? '');
    $force = !empty($_GET['force']) && $_GET['force'] === '1';

    if (strlen($dni) !== 8) {
        http_response_code(400);
        echo json_encode(['error' => 'El DNI debe tener exactamente 8 dígitos.']);
        exit;
    }

    if (!$force) {
        $stmt = $pdo->prepare("SELECT * FROM dni_cache WHERE dni = :dni LIMIT 1");
        $stmt->execute([':dni' => $dni]);
        $cached = $stmt->fetch();
        if ($cached) {
            echo json_encode([
                'success' => true,
                'from_cache' => true,
                'cached_at' => $cached['updated_at'],
                'data' => json_decode($cached['raw_json'], true)
            ]);
            exit;
        }
    }

    if (!$api_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Debes configurar tu SHALOM_API_KEY para consultar DNI.']);
        exit;
    }

    $res = make_api_request('/account/dni/' . $dni, 'GET', null, $api_key);
    if ($res['http_code'] === 200 && !empty($res['data']['success'])) {
        $person = $res['data']['data'] ?? [];
        $nombres = $person['nombres'] ?? '';
        $pat = $person['apellidoPaterno'] ?? '';
        $mat = $person['apellidoMaterno'] ?? '';
        $full = trim("{$nombres} {$pat} {$mat}");

        $stmt = $pdo->prepare("INSERT INTO dni_cache (dni, nombres, apellido_paterno, apellido_materno, full_name, raw_json, updated_at) VALUES (:dni, :nombres, :pat, :mat, :full, :raw, CURRENT_TIMESTAMP) ON CONFLICT(dni) DO UPDATE SET nombres=excluded.nombres, apellido_paterno=excluded.apellido_paterno, apellido_materno=excluded.apellido_materno, full_name=excluded.full_name, raw_json=excluded.raw_json, updated_at=CURRENT_TIMESTAMP");
        $stmt->execute([
            ':dni' => $dni,
            ':nombres' => $nombres,
            ':pat' => $pat,
            ':mat' => $mat,
            ':full' => $full,
            ':raw' => json_encode($person)
        ]);

        $res['data']['from_cache'] = false;
    }

    http_response_code($res['http_code']);
    echo json_encode($res['data']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Acción no encontrada']);
exit;

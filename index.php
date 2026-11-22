<?php
session_start();

if (empty($_GET) && !empty($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $_GET);
}

// --- Environment Variables Helpers ---
function get_env_var($key, $default = null) {
    if (!empty($_SESSION[$key])) {
        return $_SESSION[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    if (!empty($_ENV[$key])) {
        return $_ENV[$key];
    }
    $env_file = __DIR__ . '/.env';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($k, $v) = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v, " \t\n\r\0\x0B\"'");
                if ($k === $key) {
                    return $v;
                }
            }
        }
    }
    return $default;
}

function save_env_var($key, $value) {
    $_SESSION[$key] = $value;
    $env_file = __DIR__ . '/.env';
    $lines = file_exists($env_file) ? file($env_file, FILE_IGNORE_NEW_LINES) : [];
    $found = false;
    $new_lines = [];
    foreach ($lines as $line) {
        if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=/i', $line)) {
            $new_lines[] = "{$key}={$value}";
            $found = true;
        } else {
            $new_lines[] = $line;
        }
    }
    if (!$found) {
        $new_lines[] = "{$key}={$value}";
    }
    file_put_contents($env_file, implode(PHP_EOL, $new_lines) . PHP_EOL);
}

// --- SQLite Database Connection & Helpers ---
function get_db() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $db_file = $dir . '/shalom.sqlite';
    $is_new = !file_exists($db_file);

    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode = WAL;');

    // Haversine formula registered inside SQLite for fast nearby geo-queries
    $pdo->sqliteCreateFunction('haversine', function($lat1, $lon1, $lat2, $lon2) {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) return 999999;
        $lat1 = (float)$lat1;
        $lon1 = (float)$lon1;
        $lat2 = (float)$lat2;
        $lon2 = (float)$lon2;
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }, 4);

    init_db_schema($pdo);
    return $pdo;
}

function init_db_schema($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tracking_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number TEXT NOT NULL,
            order_code TEXT NOT NULL,
            status_message TEXT,
            content TEXT,
            origin_name TEXT,
            destination_name TEXT,
            sender_name TEXT,
            receiver_name TEXT,
            amount TEXT,
            invoice TEXT,
            raw_search_json TEXT,
            raw_statuses_json TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(order_number, order_code)
        );

        CREATE TABLE IF NOT EXISTS agencies (
            ter_id INTEGER PRIMARY KEY,
            ter_abrebiatura TEXT,
            nombre TEXT,
            direccion TEXT,
            departamento TEXT,
            provincia TEXT,
            zona TEXT,
            ter_zona TEXT,
            latitud REAL,
            longitud REAL,
            telefono TEXT,
            hora_atencion TEXT,
            estado_agencia TEXT,
            ter_aereo INTEGER DEFAULT 0,
            raw_json TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS dni_cache (
            dni TEXT PRIMARY KEY,
            nombres TEXT,
            apellido_paterno TEXT,
            apellido_materno TEXT,
            full_name TEXT,
            raw_json TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS system_meta (
            key TEXT PRIMARY KEY,
            value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

function get_setting($key, $default = null) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $val = $stmt->fetchColumn();
        if ($val !== false && $val !== null && $val !== '') {
            return $val;
        }
    } catch (Exception $e) {}
    return get_env_var($key, $default);
}

function save_setting($key, $value) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("
            INSERT INTO settings (key, value, updated_at) 
            VALUES (:key, :val, CURRENT_TIMESTAMP)
            ON CONFLICT(key) DO UPDATE SET 
                value = excluded.value, 
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([':key' => $key, ':val' => $value]);
    } catch (Exception $e) {}
    save_env_var($key, $value);
}

function get_all_settings() {
    $settings = [
        'SHALOM_API_KEY' => '',
        'SHALOM_INSTANCE_ID' => '',
        'DEFAULT_ORIGIN' => ''
    ];
    try {
        $pdo = get_db();
        $rows = $pdo->query("SELECT key, value FROM settings")->fetchAll();
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
    } catch (Exception $e) {}

    if (empty($settings['SHALOM_API_KEY'])) {
        $settings['SHALOM_API_KEY'] = get_env_var('SHALOM_API_KEY', '');
    }
    return $settings;
}

function sync_agencies_to_db($pdo, $api_key = null) {
    $endpoint = $api_key ? '/agencies' : '/public/agencies';
    $res = make_api_request($endpoint, 'GET', null, $api_key);

    if ($res['http_code'] !== 200 || empty($res['data']['data'])) {
        return ['success' => false, 'error' => $res['data']['message'] ?? 'No se pudo obtener el catálogo de la API'];
    }

    $agencies = $res['data']['data'];
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO agencies (
            ter_id, ter_abrebiatura, nombre, direccion, departamento, provincia,
            zona, ter_zona, latitud, longitud, telefono, hora_atencion, estado_agencia,
            ter_aereo, raw_json, updated_at
        ) VALUES (
            :ter_id, :ter_abrebiatura, :nombre, :direccion, :departamento, :provincia,
            :zona, :ter_zona, :latitud, :longitud, :telefono, :hora_atencion, :estado_agencia,
            :ter_aereo, :raw_json, CURRENT_TIMESTAMP
        ) ON CONFLICT(ter_id) DO UPDATE SET
            ter_abrebiatura = excluded.ter_abrebiatura,
            nombre = excluded.nombre,
            direccion = excluded.direccion,
            departamento = excluded.departamento,
            provincia = excluded.provincia,
            zona = excluded.zona,
            ter_zona = excluded.ter_zona,
            latitud = excluded.latitud,
            longitud = excluded.longitud,
            telefono = excluded.telefono,
            hora_atencion = excluded.hora_atencion,
            estado_agencia = excluded.estado_agencia,
            ter_aereo = excluded.ter_aereo,
            raw_json = excluded.raw_json,
            updated_at = CURRENT_TIMESTAMP
    ");

    foreach ($agencies as $ag) {
        $stmt->execute([
            ':ter_id' => (int)$ag['ter_id'],
            ':ter_abrebiatura' => $ag['ter_abrebiatura'] ?? '',
            ':nombre' => $ag['nombre'] ?? ($ag['lugar_over'] ?? ''),
            ':direccion' => $ag['direccion'] ?? '',
            ':departamento' => $ag['departamento'] ?? '',
            ':provincia' => $ag['provincia'] ?? '',
            ':zona' => $ag['zona'] ?? '',
            ':ter_zona' => $ag['ter_zona'] ?? '',
            ':latitud' => !empty($ag['latitud']) ? (float)$ag['latitud'] : null,
            ':longitud' => !empty($ag['longitud']) ? (float)$ag['longitud'] : null,
            ':telefono' => $ag['telefono'] ?? '',
            ':hora_atencion' => $ag['hora_atencion'] ?? '',
            ':estado_agencia' => $ag['estadoAgencia'] ?? '',
            ':ter_aereo' => !empty($ag['ter_aereo']) ? (int)$ag['ter_aereo'] : 0,
            ':raw_json' => json_encode($ag)
        ]);
    }

    $stmtMeta = $pdo->prepare("INSERT INTO system_meta (key, value, updated_at) VALUES ('agencies_last_synced', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ON CONFLICT(key) DO UPDATE SET value=CURRENT_TIMESTAMP, updated_at=CURRENT_TIMESTAMP");
    $stmtMeta->execute();

    $pdo->commit();
    return ['success' => true, 'count' => count($agencies)];
}

// --- API Request Helper ---
function make_api_request($endpoint, $method = 'GET', $data = null, $api_key = null) {
    $base_url = 'https://api.shalom-api.lat';
    $url = $base_url . $endpoint;
    
    $ch = curl_init();
    $headers = [
        'Accept: application/json',
    ];
    if ($api_key) {
        $headers[] = 'x-api-key: ' . $api_key;
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($data) ? $data : json_encode($data));
        }
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($data) ? $data : json_encode($data));
        }
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        return ['http_code' => 500, 'data' => ['error' => 'cURL Error: ' . $curl_error]];
    }

    $json = json_decode($response, true);
    return [
        'http_code' => $http_code,
        'data' => $json !== null ? $json : ['raw' => $response]
    ];
}

// --- API Router for AJAX Requests ---
if (isset($_GET['action'])) {
    // Suppress raw HTML PHP errors in AJAX JSON mode
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];
    $api_key = get_setting('SHALOM_API_KEY');
    $pdo = get_db();

    if ($action === 'status') {
        $db_file = __DIR__ . '/data/shalom.sqlite';
        $db_size = file_exists($db_file) ? filesize($db_file) : 0;

        $stats = [
            'tracking_count' => (int)$pdo->query("SELECT count(*) FROM tracking_history")->fetchColumn(),
            'agencies_count' => (int)$pdo->query("SELECT count(*) FROM agencies")->fetchColumn(),
            'dni_count' => (int)$pdo->query("SELECT count(*) FROM dni_cache")->fetchColumn(),
            'settings_count' => (int)$pdo->query("SELECT count(*) FROM settings")->fetchColumn(),
            'last_synced' => $pdo->query("SELECT value FROM system_meta WHERE key='agencies_last_synced'")->fetchColumn() ?: null,
            'db_size_formatted' => round($db_size / 1024, 1) . ' KB'
        ];

        $settings = get_all_settings();

        if (!$api_key) {
            echo json_encode(['configured' => false, 'message' => 'No hay API key configurada', 'db_stats' => $stats, 'settings' => $settings]);
            exit;
        }
        $res = make_api_request('/validate', 'GET', null, $api_key);
        if ($res['http_code'] === 200 && !empty($res['data']['valid'])) {
            echo json_encode([
                'configured' => true,
                'valid' => true,
                'data' => $res['data'],
                'masked_key' => substr($api_key, 0, 7) . '...' . substr($api_key, -4),
                'db_stats' => $stats,
                'settings' => $settings
            ]);
        } else {
            echo json_encode([
                'configured' => true,
                'valid' => false,
                'message' => $res['data']['message'] ?? 'API key inválida',
                'data' => $res['data'],
                'db_stats' => $stats,
                'settings' => $settings
            ]);
        }
        exit;
    }

    if ($action === 'get_settings') {
        $settings = get_all_settings();
        $key = $settings['SHALOM_API_KEY'] ?? '';
        echo json_encode([
            'success' => true,
            'settings' => $settings,
            'masked_key' => $key ? (substr($key, 0, 7) . '...' . substr($key, -4)) : ''
        ]);
        exit;
    }

    if ($action === 'save_settings') {
        $input = json_decode(file_get_contents('php://input'), true);
        $new_key = trim($input['apiKey'] ?? '');
        $instance_id = trim($input['instanceId'] ?? '');
        $default_origin = trim($input['defaultOrigin'] ?? '');

        $current_key = get_setting('SHALOM_API_KEY');
        if ($new_key !== '') {
            if ($new_key !== $current_key) {
                $test = make_api_request('/validate', 'GET', null, $new_key);
                if ($test['http_code'] !== 200 || empty($test['data']['valid'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'API Key inválida: ' . ($test['data']['message'] ?? 'Verifique su clave en shalom-api.lat')]);
                    exit;
                }
            }
            save_setting('SHALOM_API_KEY', $new_key);
        }

        save_setting('SHALOM_INSTANCE_ID', $instance_id);
        save_setting('DEFAULT_ORIGIN', $default_origin);

        echo json_encode([
            'success' => true,
            'message' => 'Configuración guardada en SQLite con éxito.',
            'settings' => get_all_settings()
        ]);
        exit;
    }

    if ($action === 'clear_history') {
        $pdo->exec("DELETE FROM tracking_history");
        echo json_encode(['success' => true, 'message' => 'Historial de guías vaciado en SQLite']);
        exit;
    }

    if ($action === 'save_key') {
        $input = json_decode(file_get_contents('php://input'), true);
        $new_key = trim($input['apiKey'] ?? '');
        if (!$new_key) {
            http_response_code(400);
            echo json_encode(['error' => 'La API key no puede estar vacía.']);
            exit;
        }
        $test = make_api_request('/validate', 'GET', null, $new_key);
        if ($test['http_code'] === 200 && !empty($test['data']['valid'])) {
            save_env_var('SHALOM_API_KEY', $new_key);
            echo json_encode([
                'success' => true,
                'message' => 'API Key verificada y guardada correctamente.',
                'data' => $test['data']
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'error' => 'API Key inválida: ' . ($test['data']['message'] ?? 'Verifique su clave en shalom-api.lat')
            ]);
        }
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

    // --- TRACKING WITH SQLITE CACHE & FORCED REFRESH ---
    if ($action === 'track') {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderNumber = trim($input['orderNumber'] ?? '');
        $orderCode = strtoupper(trim($input['orderCode'] ?? ''));
        $force = !empty($input['force']);

        if (!$orderNumber || !$orderCode) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requiere número de orden y código de seguridad.']);
            exit;
        }

        // 1. Si no es forzada, verificar si ya está guardada en SQLite
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

            // Guardar o actualizar en SQLite
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
        $stmt = $pdo->query("SELECT id, order_number, order_code, status_message, content, origin_name, destination_name, updated_at FROM tracking_history ORDER BY updated_at DESC LIMIT 15");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'delete_track') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM tracking_history WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // --- AGENCIES WITH SQLITE CACHE & FORCED SYNC ---
    if ($action === 'sync_agencies') {
        $res = sync_agencies_to_db($pdo, $api_key);
        if ($res['success']) {
            echo json_encode(['success' => true, 'message' => "Se sincronizaron {$res['count']} agencias correctamente en SQLite.", 'count' => $res['count']]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $res['error']]);
        }
        exit;
    }

    if ($action === 'agencies') {
        $count = (int)$pdo->query("SELECT count(*) FROM agencies")->fetchColumn();
        $force = !empty($_GET['force']) && $_GET['force'] === '1';

        // Si la tabla está vacía o se forzó, sincronizar desde API
        if ($count === 0 || $force) {
            $syncRes = sync_agencies_to_db($pdo, $api_key);
            if (!$syncRes['success'] && $count === 0) {
                http_response_code(500);
                echo json_encode(['error' => 'No se pudo sincronizar el catálogo de agencias: ' . ($syncRes['error'] ?? '')]);
                exit;
            }
        }

        // Consultar directamente desde SQLite
        $q = trim($_GET['q'] ?? '');
        $departamento = trim($_GET['departamento'] ?? '');
        $aereo = isset($_GET['aereo']) && ($_GET['aereo'] === 'true' || $_GET['aereo'] === '1');
        $near = trim($_GET['near'] ?? '');
        $per_page = min(max((int)($_GET['per_page'] ?? 100), 1), 500);

        $params = [];
        $select = "SELECT ter_id, ter_abrebiatura, nombre, direccion, departamento, provincia, zona, ter_zona, latitud, longitud, telefono, hora_atencion, estado_agencia, ter_aereo";

        $user_lat = null;
        $user_lng = null;
        if ($near && strpos($near, ',') !== false) {
            list($user_lat, $user_lng) = explode(',', $near, 2);
            $user_lat = (float)trim($user_lat);
            $user_lng = (float)trim($user_lng);
            $select .= ", haversine(:user_lat, :user_lng, latitud, longitud) AS distancia_km";
            $params[':user_lat'] = $user_lat;
            $params[':user_lng'] = $user_lng;
        } else {
            $select .= ", NULL AS distancia_km";
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
        $input = json_decode(file_get_contents('php://input'), true);
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

    // --- DNI WITH SQLITE CACHE ---
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
}

$has_key = !empty(get_env_var('SHALOM_API_KEY'));

// Load all agencies for Select2 dropdowns
$agencies_by_dept = [];
try {
    $db = get_db();
    $stmt = $db->query("SELECT ter_id, nombre, departamento, provincia, direccion FROM agencies ORDER BY departamento ASC, nombre ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dept = !empty($row['departamento']) ? strtoupper(trim($row['departamento'])) : 'OTRAS SEDES';
        $agencies_by_dept[$dept][] = $row;
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shalom API — Portal de Envíos Perú</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery & Select2 CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- HTML5 QR Code Scanner CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                            950: '#4c0519',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #0f172a; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Select2 Dark Custom Styling */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            background-color: #020617 !important;
            border: 1px solid #334155 !important;
            border-radius: 0.75rem !important;
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.2s ease !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #f43f5e !important;
            box-shadow: 0 0 0 1px #f43f5e !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f8fafc !important;
            font-size: 0.75rem !important;
            font-family: "JetBrains Mono", monospace, sans-serif !important;
            line-height: 40px !important;
            padding-left: 0.85rem !important;
            padding-right: 2.2rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #64748b !important;
            font-family: "Plus Jakarta Sans", sans-serif !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 10px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #94a3b8 transparent transparent transparent !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #94a3b8 transparent !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            color: #ef4444 !important;
            font-size: 1.1rem !important;
            line-height: 40px !important;
            margin-right: 18px !important;
        }
        .select2-dropdown {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6) !important;
            overflow: hidden !important;
            z-index: 9999 !important;
        }
        .select2-container--default .select2-search--dropdown {
            padding: 8px !important;
            background-color: #0f172a !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #020617 !important;
            border: 1px solid #334155 !important;
            border-radius: 0.5rem !important;
            color: #f8fafc !important;
            font-size: 0.75rem !important;
            padding: 8px 12px !important;
            outline: none !important;
            font-family: "Plus Jakarta Sans", sans-serif !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #f43f5e !important;
        }
        .select2-results__options {
            max-height: 250px !important;
            overflow-y: auto !important;
        }
        .select2-results__options::-webkit-scrollbar { width: 6px; }
        .select2-results__options::-webkit-scrollbar-track { background: #0f172a; }
        .select2-results__options::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        .select2-results__option {
            font-size: 0.75rem !important;
            padding: 8px 12px !important;
            color: #cbd5e1 !important;
            line-height: 1.35 !important;
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #e11d48 !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option--selected {
            background-color: #4c0519 !important;
            color: #fda4af !important;
            font-weight: 600 !important;
        }
        .select2-container--default .select2-results__group {
            background-color: #1e293b !important;
            color: #fb7185 !important;
            font-weight: 700 !important;
            font-size: 0.65rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 6px 12px !important;
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans antialiased selection:bg-brand-600 selection:text-white">

    <!-- Header / Navbar -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-rose-500 flex items-center justify-center shadow-lg shadow-brand-900/40 border border-brand-500/30">
                    <i data-lucide="package-search" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-lg tracking-tight text-white">SHALOM <span class="text-rose-500 font-medium text-sm">API</span></span>
                        <span class="px-2 py-0.5 text-[10px] uppercase font-semibold bg-rose-950 text-rose-300 border border-rose-800/60 rounded-full flex items-center gap-1">
                            <i data-lucide="package" class="w-2.5 h-2.5"></i> Portal de Envíos
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400">Tracking, Agencias, Cotizador & DNI</p>
                </div>
            </div>

            <!-- API Status & Key Action -->
            <div class="flex items-center gap-3">
                <div id="keyBadge" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs border font-medium transition-all">
                    <span class="w-2 h-2 rounded-full animate-pulse"></span>
                    <span id="keyStatusText">Verificando...</span>
                </div>
                <button onclick="openKeyModal()" class="flex items-center gap-1.5 text-xs bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 px-3 py-2 rounded-lg transition-all shadow-sm">
                    <i data-lucide="settings-2" class="w-4 h-4 text-rose-400"></i>
                    <span class="hidden md:inline">Configuración</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <div class="border-b border-slate-800/80 bg-slate-900/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex gap-2 sm:gap-4 overflow-x-auto py-2.5 custom-scrollbar text-sm" id="tabsNav">
                <button onclick="switchTab('tracking')" id="tab-btn-tracking" class="tab-button active flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-white bg-brand-700/80 shadow-md shadow-brand-900/20 border border-brand-600/50">
                    <i data-lucide="truck" class="w-4 h-4"></i> Rastrear Guía
                </button>
                <button onclick="switchTab('agencies')" id="tab-btn-agencies" class="tab-button flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="map-pin" class="w-4 h-4"></i> Agencias & Sedes
                </button>
                <button onclick="switchTab('quote')" id="tab-btn-quote" class="tab-button flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="calculator" class="w-4 h-4"></i> Cotizador
                </button>
                <button onclick="switchTab('dni')" id="tab-btn-dni" class="tab-button flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="user-check" class="w-4 h-4"></i> Consultar DNI
                </button>
                <button onclick="switchTab('pro')" id="tab-btn-pro" class="tab-button flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i> Shalom Pro Info
                </button>
            </nav>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">

        <!-- ================= TAB: TRACKING ================= -->
        <section id="tab-tracking" class="tab-content space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Search Box -->
                <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2 text-rose-400 text-sm font-semibold">
                                <i data-lucide="search" class="w-4 h-4"></i>
                                <span>Búsqueda de Envíos</span>
                            </div>
                            <button type="button" onclick="openQrModal()"
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-950/80 hover:bg-rose-900 border border-rose-700/60 text-rose-300 hover:text-white rounded-lg text-xs font-semibold transition-all shadow-sm">
                                <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> Escanear QR
                            </button>
                        </div>
                        <h2 class="text-xl font-bold text-white mb-2">Rastreo de Guía</h2>
                        <p class="text-slate-400 text-xs leading-relaxed mb-6">
                            Ingresa el número de orden (8-12 dígitos) y el código de seguridad (4 caracteres) del ticket de tu envío.
                        </p>

                        <form id="trackingForm" onsubmit="handleTrack(event)" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1.5">Número de Orden</label>
                                <div class="relative">
                                    <input type="text" id="trackOrderNumber" required placeholder="Ej: 66479331" maxlength="12" pattern="\d+"
                                        class="w-full bg-slate-950 border border-slate-700 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded-xl px-3.5 py-2.5 text-sm font-mono text-white placeholder-slate-500 transition-all">
                                    <span class="absolute right-3 top-2.5 text-xs text-slate-500">8-12 dig</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1.5">Código de Seguridad</label>
                                <div class="relative">
                                    <input type="text" id="trackOrderCode" required placeholder="Ej: 3KTH" maxlength="4"
                                        class="w-full bg-slate-950 border border-slate-700 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded-xl px-3.5 py-2.5 text-sm font-mono uppercase text-white placeholder-slate-500 transition-all">
                                    <span class="absolute right-3 top-2.5 text-xs text-slate-500">4 car</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <input type="checkbox" id="trackForce" class="rounded text-rose-600 focus:ring-rose-500 bg-slate-950 border-slate-700">
                                <label for="trackForce" class="text-xs text-slate-300 select-none cursor-pointer">
                                    Actualizar estado en vivo
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <button type="submit" id="btnTrackSubmit"
                                    class="bg-gradient-to-r from-brand-700 to-rose-600 hover:from-brand-600 hover:to-rose-500 text-white font-medium text-xs py-2.5 px-3 rounded-xl shadow-lg shadow-brand-900/30 flex items-center justify-center gap-1.5 transition-all">
                                    <i data-lucide="search" class="w-4 h-4"></i> Consultar
                                </button>
                                <button type="button" onclick="openQrModal()"
                                    class="bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium text-xs py-2.5 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all">
                                    <i data-lucide="qr-code" class="w-4 h-4 text-rose-400"></i> Escanear QR
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tracking Display Area -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl min-h-[420px] flex flex-col justify-center" id="trackResultContainer">
                    <div id="trackEmptyState" class="text-center py-16">
                        <div class="w-16 h-16 rounded-2xl bg-slate-800/60 border border-slate-700 flex items-center justify-center mx-auto mb-4 text-slate-500">
                            <i data-lucide="package" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-semibold text-slate-300">Sin consulta activa</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                            Ingresa un número y código para consultar el estado y la línea de tiempo del envío.
                        </p>
                    </div>

                    <div id="trackLoadingState" class="hidden text-center py-16">
                        <div class="w-12 h-12 border-4 border-rose-500/20 border-t-rose-500 rounded-full animate-spin mx-auto mb-4"></div>
                        <p class="text-sm font-medium text-slate-300">Consultando envío...</p>
                        <p class="text-xs text-slate-500 mt-1">Obteniendo estado oficial de Shalom</p>
                    </div>

                    <div id="trackDataState" class="hidden space-y-6">
                        <!-- Top status card & Cache Source Badge -->
                        <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl bg-slate-950 border border-slate-800">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Estado del Envío</span>
                                    <span id="trackSourceBadge" class="px-2 py-0.5 rounded text-[10px] font-semibold border flex items-center gap-1"></span>
                                </div>
                                <div class="flex items-center gap-2.5 mt-1">
                                    <span id="trackStatusBadge" class="px-3 py-1 text-xs font-bold rounded-full bg-blue-900/60 text-blue-300 border border-blue-700/50">
                                        En tránsito
                                    </span>
                                    <span id="trackOrderBadge" class="text-sm font-mono text-slate-300"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="forceRefreshCurrentTrack()" id="btnForceCurrent"
                                    title="Forzar actualización en vivo desde Shalom API"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs rounded-lg border border-slate-700 transition-all">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-rose-400"></i> Forzar API
                                </button>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4">Línea de Tiempo del Courier</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3" id="timelineCards">
                                <!-- Generated via JS -->
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2 text-xs">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Origen:</span>
                                    <span id="trackOrigin" class="font-semibold text-white"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Destino:</span>
                                    <span id="trackDestination" class="font-semibold text-white"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Contenido:</span>
                                    <span id="trackContent" class="font-semibold text-white"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Remitente:</span>
                                    <span id="trackSender" class="font-semibold text-slate-200"></span>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2 text-xs">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Destinatario:</span>
                                    <span id="trackReceiver" class="font-semibold text-slate-200"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Monto / Cobro:</span>
                                    <span id="trackAmount" class="font-semibold text-emerald-400"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Comprobante:</span>
                                    <span id="trackInvoice" class="font-mono text-slate-300"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Fecha de Consulta:</span>
                                    <span id="trackSavedAt" class="font-mono text-slate-400"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Raw JSON toggle -->
                        <details class="text-xs text-slate-400 bg-slate-950/40 border border-slate-800/80 rounded-xl p-3">
                            <summary class="cursor-pointer hover:text-white font-medium select-none">Ver Respuesta JSON Completa</summary>
                            <pre id="trackRawJson" class="mt-3 p-3 bg-slate-950 rounded-lg font-mono text-[11px] text-slate-300 overflow-x-auto max-h-56 custom-scrollbar"></pre>
                        </details>
                    </div>
                </div>
            </div>

            <!-- Recent Tracking History -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-rose-400"></i>
                        <h3 class="text-sm font-bold text-white">Consultas Recientes (<span id="historyCount">0</span>)</h3>
                    </div>
                    <button onclick="loadRecentTracks()" class="text-xs text-slate-400 hover:text-white flex items-center gap-1">
                        <i data-lucide="refresh-cw" class="w-3 h-3"></i> Refrescar lista
                    </button>
                </div>

                <div id="historyTableContainer" class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs text-slate-300 border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-[11px] text-slate-500 uppercase tracking-wider">
                                <th class="py-2.5 px-3">Orden / Código</th>
                                <th class="py-2.5 px-3">Estado</th>
                                <th class="py-2.5 px-3">Ruta</th>
                                <th class="py-2.5 px-3">Contenido</th>
                                <th class="py-2.5 px-3">Última Consulta</th>
                                <th class="py-2.5 px-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">No hay consultas recientes aún.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ================= TAB: AGENCIES ================= -->
        <section id="tab-agencies" class="tab-content hidden space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <i data-lucide="building-2" class="w-5 h-5 text-rose-500"></i> Catálogo de Agencias Shalom
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Consulta direcciones, teléfonos, horarios de atención y cobertura de agencias a nivel nacional.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button onclick="syncAgencies(true)" id="btnSyncAgencies"
                            class="inline-flex items-center gap-2 bg-rose-700/80 hover:bg-rose-600 text-white px-3.5 py-2 rounded-xl text-xs font-semibold border border-rose-600 transition-all">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualizar Catálogo
                        </button>
                        <button onclick="getUserLocation()" id="btnNearby"
                            class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-700 transition-all">
                            <i data-lucide="crosshair" class="w-4 h-4 text-rose-400"></i> Cerca de Mí (GPS)
                        </button>
                    </div>
                </div>

                <!-- Filters Form -->
                <form id="agenciesForm" onsubmit="handleAgenciesSearch(event)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                    <div class="relative">
                        <input type="text" id="agencyQ" placeholder="Ciudad, distrito o sede (ej: Miraflores)"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                    </div>
                    <div>
                        <input type="text" id="agencyDept" placeholder="Departamento (ej: Lima, Piura, Cusco)"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                    </div>
                    <div class="flex items-center gap-2 bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2">
                        <input type="checkbox" id="agencyAereo" class="rounded text-rose-600 focus:ring-rose-500 bg-slate-900 border-slate-700">
                        <label for="agencyAereo" class="text-xs text-slate-300 select-none">Solo Servicio Aéreo</label>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-medium text-xs py-2 px-4 rounded-xl flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filtrar Agencias
                        </button>
                    </div>
                </form>

                <!-- Status indicator -->
                <div class="flex items-center justify-between text-xs text-slate-400 mb-4 pb-2 border-b border-slate-800">
                    <span id="agenciesCount">Cargando catálogo de agencias...</span>
                    <span id="agenciesLastSynced" class="text-[11px] text-slate-500"></span>
                </div>

                <!-- Agency Cards Grid -->
                <div id="agenciesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="col-span-full text-center py-12 text-slate-500 text-xs">Cargando agencias...</div>
                </div>
            </div>
        </section>

        <!-- ================= TAB: QUOTE ================= -->
        <section id="tab-quote" class="tab-content hidden space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quote Form -->
                <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <div class="flex items-center gap-2 text-rose-400 text-sm font-semibold mb-2">
                        <i data-lucide="scale" class="w-4 h-4"></i>
                        <span>Cotización de Flete</span>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-2">Cotizar Envío</h2>
                    <p class="text-slate-400 text-xs leading-relaxed mb-6">
                        Calcula el costo del envío entre dos sedes de Shalom. Opcionalmente añade medidas para cotizar cajas especiales por volumen.
                    </p>

                    <form id="quoteForm" onsubmit="handleQuote(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Agencia Origen</label>
                            <select id="quoteOrigin" class="select2-agency w-full" data-placeholder="Buscar o seleccionar agencia de origen...">
                                <option value=""></option>
                                <?php foreach ($agencies_by_dept as $dept => $list): ?>
                                    <optgroup label="<?= htmlspecialchars($dept) ?>">
                                        <?php foreach ($list as $ag): ?>
                                            <option value="<?= htmlspecialchars($ag['ter_id']) ?>">
                                                <?= htmlspecialchars($ag['nombre']) ?> — ID: <?= $ag['ter_id'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-[10px] text-slate-500">Escribe el nombre, distrito o ID para filtrar</span>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Agencia Destino</label>
                            <select id="quoteDest" class="select2-agency w-full" data-placeholder="Buscar o seleccionar agencia de destino...">
                                <option value=""></option>
                                <?php foreach ($agencies_by_dept as $dept => $list): ?>
                                    <optgroup label="<?= htmlspecialchars($dept) ?>">
                                        <?php foreach ($list as $ag): ?>
                                            <option value="<?= htmlspecialchars($ag['ter_id']) ?>">
                                                <?= htmlspecialchars($ag['nombre']) ?> — ID: <?= $ag['ter_id'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-[10px] text-slate-500">Escribe el nombre, distrito o ID para filtrar</span>
                        </div>

                        <div class="pt-2 border-t border-slate-800">
                            <span class="block text-xs font-semibold text-slate-300 mb-2">Medidas Volumétricas (Opcional - "Otra Medida")</span>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <label class="text-[10px] text-slate-400">Ancho (m, máx 1.5)</label>
                                    <input type="number" step="0.01" id="quoteWidth" placeholder="Ej: 0.30" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400">Alto (m, máx 1.5)</label>
                                    <input type="number" step="0.01" id="quoteHeight" placeholder="Ej: 0.20" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400">Largo (m, máx 1.5)</label>
                                    <input type="number" step="0.01" id="quoteLength" placeholder="Ej: 0.40" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400">Peso (kg, máx 500)</label>
                                    <input type="number" step="0.1" id="quoteWeight" placeholder="Ej: 3.5" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white">
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="btnQuoteSubmit"
                            class="w-full mt-3 bg-gradient-to-r from-brand-700 to-rose-600 hover:from-brand-600 hover:to-rose-500 text-white font-medium text-xs py-2.5 px-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                            <i data-lucide="calculator" class="w-4 h-4"></i> Calcular Tarifa
                        </button>
                    </form>
                </div>

                <!-- Quote Results -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-center" id="quoteResultContainer">
                    <div id="quoteEmptyState" class="text-center py-16 text-slate-500 text-xs">
                        <i data-lucide="coins" class="w-10 h-10 mx-auto mb-2 text-slate-600"></i>
                        Ingresa origen y destino para calcular las tarifas.
                    </div>

                    <div id="quoteDataState" class="hidden space-y-6">
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400">Modo de Cotización</span>
                                <p id="quoteModeBadge" class="text-sm font-semibold text-rose-400 capitalize">Estándar</p>
                            </div>
                            <div id="quoteVolumeCard" class="hidden text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400">Precio Otra Medida</span>
                                <p id="quoteVolumePrice" class="text-xl font-bold text-emerald-400">S/ 0.00</p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Tarifas por Tipo de Paquete Fijo</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="tariffCards">
                                <!-- Generated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= TAB: DNI ================= -->
        <section id="tab-dni" class="tab-content hidden space-y-6">
            <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
                <div class="flex items-center gap-2 text-rose-400 text-sm font-semibold mb-2">
                    <i data-lucide="id-card" class="w-4 h-4"></i>
                    <span>Verificación de Identidad</span>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Consulta de DNI</h2>
                <p class="text-slate-400 text-xs leading-relaxed mb-6">
                    Consulta y valida el nombre oficial registrado antes de emitir o recepcionar tu envío.
                </p>

                <form id="dniForm" onsubmit="handleDni(event)" class="space-y-3 mb-6">
                    <div class="flex gap-2">
                        <input type="text" id="dniInput" required placeholder="Ingresa 8 dígitos de DNI" maxlength="8" pattern="\d{8}"
                            class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm font-mono text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                        <button type="submit" id="btnDniSubmit"
                            class="bg-rose-600 hover:bg-rose-500 text-white font-medium text-xs px-5 py-2.5 rounded-xl shadow-lg transition-all flex items-center gap-2">
                            <i data-lucide="search" class="w-4 h-4"></i> Consultar
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="dniForce" class="rounded text-rose-600 focus:ring-rose-500 bg-slate-950 border-slate-700">
                        <label for="dniForce" class="text-xs text-slate-400 cursor-pointer">Actualizar datos en línea</label>
                    </div>
                </form>

                <!-- DNI Result -->
                <div id="dniResult" class="hidden p-6 rounded-xl bg-slate-950 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-rose-400">
                                <i data-lucide="user" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400">Nombre Completo</span>
                                <h3 id="dniFullName" class="text-base font-bold text-white"></h3>
                            </div>
                        </div>
                        <span id="dniSourceBadge" class="text-[10px] font-semibold px-2.5 py-1 rounded-full border"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 pt-3 border-t border-slate-800 text-xs">
                        <div>
                            <span class="text-slate-500 block text-[10px]">DNI</span>
                            <span id="dniNumber" class="font-mono font-semibold text-slate-200"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px]">Nombres</span>
                            <span id="dniNames" class="font-semibold text-slate-200"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px]">Apellidos</span>
                            <span id="dniSurnames" class="font-semibold text-slate-200"></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= TAB: SHALOM PRO INFO ================= -->
        <section id="tab-pro" class="tab-content hidden space-y-6">
            <div class="max-w-3xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl space-y-6">
                <div>
                    <span class="text-rose-400 text-xs font-semibold uppercase tracking-wider">Integración Avanzada</span>
                    <h2 class="text-xl font-bold text-white mt-1">Shalom Pro & Creación de Guías</h2>
                    <p class="text-slate-400 text-xs mt-1">
                        Para crear guías reales, anular pendientes o descargar rótulos se requiere una cuenta de Shalom Pro conectada mediante una instancia.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                        <h3 class="text-sm font-semibold text-white flex items-center gap-2 mb-2">
                            <i data-lucide="server" class="w-4 h-4 text-rose-400"></i> ¿Cómo funciona la Instancia?
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Creas una instancia con <code class="text-rose-300 font-mono text-[11px]">POST /instances</code> e inicias sesión una sola vez con <code class="text-rose-300 font-mono text-[11px]">POST /instances/login</code>. La API mantiene y reconecta la sesión automáticamente.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                        <h3 class="text-sm font-semibold text-white flex items-center gap-2 mb-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-emerald-400"></i> Rótulo y Comprobantes
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Con tu <code class="text-emerald-300 font-mono text-[11px]">instanceId</code> puedes descargar los rótulos oficiales en PDF (<code class="text-emerald-300 font-mono text-[11px]">GET /track/label</code>) y los tickets (<code class="text-emerald-300 font-mono text-[11px]">GET /track/voucher</code>).
                        </p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-brand-950/40 border border-brand-800/50 text-xs text-rose-200 space-y-2">
                    <div class="font-semibold flex items-center gap-1.5 text-rose-300">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i> Reglas Críticas para Desarrolladores:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-slate-300">
                        <li><strong>No uses polling:</strong> Usa Webhooks (<code class="font-mono text-rose-300">PUT /webhooks</code>) con verificación HMAC-SHA256 en vez de loops periódicos.</li>
                        <li><strong>Plan gratuito:</strong> Permite tracking y catálogo de agencias (5 ops/mes), pero no creación de guías.</li>
                        <li><strong>Catálogo optimizado:</strong> El catálogo de agencias se sincroniza periódicamente para permitir búsquedas y cálculo de cercanía por GPS al instante.</li>
                    </ul>
                </div>
            </div>
        </section>

    </main>

    <!-- Modal: QR Code Scanner -->
    <div id="qrModal" class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-600/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Lector de Código QR</h3>
                        <p class="text-[11px] text-slate-400">Ticket y rótulos de Shalom</p>
                    </div>
                </div>
                <button type="button" onclick="closeQrModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Tab Buttons for Camera / File Upload -->
            <div class="flex bg-slate-950 p-1 rounded-xl border border-slate-800 text-xs">
                <button type="button" id="qrTabCamera" onclick="switchQrMode('camera')"
                    class="flex-1 py-1.5 rounded-lg font-medium text-center transition-all bg-rose-600 text-white">
                    <i data-lucide="camera" class="w-3.5 h-3.5 inline mr-1"></i> Cámara en Vivo
                </button>
                <button type="button" id="qrTabFile" onclick="switchQrMode('file')"
                    class="flex-1 py-1.5 rounded-lg font-medium text-center transition-all text-slate-400 hover:text-white">
                    <i data-lucide="upload" class="w-3.5 h-3.5 inline mr-1"></i> Subir Imagen / Foto
                </button>
            </div>

            <!-- Camera View Area -->
            <div id="qrCameraSection" class="space-y-2">
                <div id="qrReader" class="overflow-hidden rounded-xl border border-slate-700 bg-black min-h-[260px] flex items-center justify-center relative">
                    <div id="qrCameraPlaceholder" class="text-center p-6 text-slate-500 text-xs">
                        <i data-lucide="camera" class="w-8 h-8 mx-auto mb-2 text-slate-600"></i>
                        Iniciando cámara...
                    </div>
                </div>
                <p class="text-[11px] text-center text-slate-400">
                    Apunta la cámara al código QR impreso en tu ticket o paquete.
                </p>
            </div>

            <!-- File Upload Area -->
            <div id="qrFileSection" class="hidden space-y-3">
                <div onclick="document.getElementById('qrFileInput').click()"
                    class="border-2 border-dashed border-slate-700 hover:border-rose-500 rounded-xl p-8 text-center cursor-pointer bg-slate-950/50 hover:bg-slate-950 transition-all group">
                    <i data-lucide="image" class="w-10 h-10 text-slate-500 group-hover:text-rose-400 mx-auto mb-2 transition-colors"></i>
                    <span class="block text-xs font-semibold text-slate-200">Haz clic para seleccionar foto o captura del QR</span>
                    <span class="block text-[11px] text-slate-500 mt-1">Formatos JPG, PNG, WEBP</span>
                </div>
                <input type="file" id="qrFileInput" accept="image/*" class="hidden" onchange="handleQrFileSelected(event)">
                <div id="qrFileStatus" class="hidden text-xs text-center text-slate-300"></div>
            </div>

            <!-- Hint / Format info -->
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl text-[11px] text-slate-400 flex items-start gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5"></i>
                <div>
                    <strong>Detección inteligente:</strong> Lee códigos QR Shalom como <code class="text-rose-300 font-mono text-[10px]">107869605/document/1/</code> y resuelve automáticamente el número de orden y código de seguridad.
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Settings -->
    <div id="keyModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-600/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
                        <i data-lucide="settings-2" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Configuración de Shalom API</h3>
                        <p class="text-[11px] text-slate-400">Credenciales y preferencias de tu cuenta</p>
                    </div>
                </div>
                <button onclick="closeKeyModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="settingsForm" onsubmit="handleSaveSettings(event)" class="space-y-4">
                <!-- API Key -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-medium text-slate-300">Shalom API Key</label>
                        <span id="keyValidateStatus" class="text-[10px] text-slate-500 font-mono">sk_...</span>
                    </div>
                    <div class="relative">
                        <input type="password" id="apiKeyInput" placeholder="sk_live_..."
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-white placeholder-slate-600 focus:border-rose-500 focus:outline-none">
                        <button type="button" onclick="toggleKeyVisibility()" class="absolute right-3 top-2.5 text-slate-500 hover:text-slate-300">
                            <i data-lucide="eye" id="toggleKeyIcon" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Obtenida desde el panel de <a href="https://shalom-api.lat" target="_blank" class="text-rose-400 underline">shalom-api.lat</a>.</p>
                </div>

                <!-- Instance ID -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">ID de Instancia Shalom Pro <span class="text-slate-500 font-normal">(Opcional)</span></label>
                    <input type="text" id="instanceIdInput" placeholder="Ej: 2e656a02-7e37-4573-9d68-e76740d337dc"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs font-mono text-white placeholder-slate-600 focus:border-rose-500 focus:outline-none">
                    <p class="text-[10px] text-slate-500 mt-1">Aplica tus tarifas corporativas al cotizar y permite descargar rótulos/tickets.</p>
                </div>

                <!-- Default Origin -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">Agencia de Origen Predeterminada <span class="text-slate-500 font-normal">(Opcional)</span></label>
                    <input type="text" id="defaultOriginInput" placeholder="Ej: 7 o LIMA"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs font-mono text-white placeholder-slate-600 focus:border-rose-500 focus:outline-none">
                    <p class="text-[10px] text-slate-500 mt-1">Pre-cargada automáticamente en el cotizador.</p>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs text-slate-400">
                    <span>Historial de consultas:</span>
                    <button type="button" onclick="clearDbHistory()" class="text-rose-400 hover:text-rose-300 text-xs underline">
                        Vaciar historial
                    </button>
                </div>

                <div id="keyTestResult" class="hidden text-xs p-3 rounded-xl border"></div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" onclick="closeKeyModal()" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white">
                        Cancelar
                    </button>
                    <button type="submit" id="btnSaveKey"
                        class="px-5 py-2.5 text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white rounded-xl shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification container -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 transform translate-y-24 opacity-0 transition-all duration-300 max-w-sm w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3">
        <i id="toastIcon" data-lucide="info" class="w-5 h-5 text-rose-400 shrink-0"></i>
        <div class="text-xs flex-1" id="toastMessage"></div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-slate-900 bg-slate-950 py-4 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>Shalom API • Portal de Servicios y Envíos Perú</span>
            <div class="flex items-center gap-4 text-[11px]">
                <a href="https://docs.shalom-api.lat" target="_blank" class="hover:text-rose-400 transition-colors">Documentación Oficial</a>
                <span>•</span>
                <a href="https://shalom-api.lat" target="_blank" class="hover:text-rose-400 transition-colors">Panel Shalom API</a>
            </div>
        </div>
    </footer>

    <!-- App JavaScript -->
    <script>
        // Base API URL helper (handles both /nova/shalom and /nova/shalom/ seamlessly)
        const API_URL = (function() {
            let p = window.location.pathname;
            if (!p.endsWith('/') && !p.endsWith('.php')) {
                p += '/';
            }
            return p.endsWith('.php') ? p : p + 'index.php';
        })();

        // Initialize Lucide icons
        lucide.createIcons();

        let currentActiveOrder = null;
        let currentActiveCode = null;

        // Notification Helper
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');

            toastMsg.textContent = message;
            if (type === 'success') {
                toastIcon.setAttribute('data-lucide', 'check-circle');
                toastIcon.className = 'w-5 h-5 text-emerald-400 shrink-0';
            } else if (type === 'error') {
                toastIcon.setAttribute('data-lucide', 'alert-circle');
                toastIcon.className = 'w-5 h-5 text-rose-500 shrink-0';
            } else {
                toastIcon.setAttribute('data-lucide', 'info');
                toastIcon.className = 'w-5 h-5 text-blue-400 shrink-0';
            }
            lucide.createIcons();

            toast.classList.remove('translate-y-24', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 3500);
        }

        // Tab Navigation
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('text-white', 'bg-brand-700/80', 'border-brand-600/50', 'shadow-md');
                btn.classList.add('text-slate-400');
            });

            const activeContent = document.getElementById('tab-' + tabId);
            const activeBtn = document.getElementById('tab-btn-' + tabId);
            if (activeContent) activeContent.classList.remove('hidden');
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-400');
                activeBtn.classList.add('text-white', 'bg-brand-700/80', 'border-brand-600/50', 'shadow-md');
            }

            if (tabId === 'quote') {
                setTimeout(() => {
                    $('#quoteOrigin, #quoteDest').select2({ width: '100%' });
                }, 10);
            }
            if (tabId === 'agencies' && (!window.agenciesLoaded)) {
                loadAgencies();
            }
            if (tabId === 'tracking') {
                loadRecentTracks();
            }
        }

        // Modal & Settings Controls
        async function openKeyModal() {
            document.getElementById('keyModal').classList.remove('hidden');
            document.getElementById('keyTestResult').classList.add('hidden');
            try {
                const res = await fetch(`${API_URL}?action=get_settings`);
                const data = await res.json();
                if (data.success && data.settings) {
                    if (data.settings.SHALOM_API_KEY) {
                        document.getElementById('apiKeyInput').value = data.settings.SHALOM_API_KEY;
                        document.getElementById('keyValidateStatus').textContent = data.masked_key || 'sk_...';
                    }
                    if (data.settings.SHALOM_INSTANCE_ID) {
                        document.getElementById('instanceIdInput').value = data.settings.SHALOM_INSTANCE_ID;
                    }
                    if (data.settings.DEFAULT_ORIGIN) {
                        document.getElementById('defaultOriginInput').value = data.settings.DEFAULT_ORIGIN;
                    }
                }
            } catch (err) {
                console.error('Error cargando configuración:', err);
            }
        }

        function closeKeyModal() {
            document.getElementById('keyModal').classList.add('hidden');
            document.getElementById('keyTestResult').classList.add('hidden');
        }

        function toggleKeyVisibility() {
            const input = document.getElementById('apiKeyInput');
            const icon = document.getElementById('toggleKeyIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        async function clearDbHistory() {
            if (!confirm('¿Deseas vaciar todo el historial de consultas recientes?')) return;
            try {
                const res = await fetch(`${API_URL}?action=clear_history`);
                const data = await res.json();
                if (data.success) {
                    showToast('Historial de consultas vaciado', 'info');
                    loadRecentTracks();
                    checkStatus();
                }
            } catch (err) {
                showToast('Error al vaciar historial', 'error');
            }
        }

        // Check API & DB Status on Load
        async function checkStatus() {
            try {
                const res = await fetch(`${API_URL}?action=status`);
                const data = await res.json();
                const badge = document.getElementById('keyBadge');
                const text = document.getElementById('keyStatusText');

                if (data.configured && data.valid) {
                    badge.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs border font-medium bg-emerald-950/60 border-emerald-800 text-emerald-300';
                    const remaining = data.data.remaining !== null ? ` (Cuota: ${data.data.remaining})` : '';
                    text.textContent = `API Key Activa${remaining}`;
                } else if (data.configured && !data.valid) {
                    badge.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs border font-medium bg-rose-950/60 border-rose-800 text-rose-300';
                    text.textContent = 'API Key Inválida';
                } else {
                    badge.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs border font-medium bg-amber-950/50 border-amber-800 text-amber-300';
                    text.textContent = 'Modo Público (Sin Key)';
                }
            } catch (err) {
                console.error('Error al comprobar estado:', err);
            }
        }

        // Save Settings in Database Handler
        async function handleSaveSettings(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveKey');
            const apiKey = document.getElementById('apiKeyInput').value.trim();
            const instanceId = document.getElementById('instanceIdInput').value.trim();
            const defaultOrigin = document.getElementById('defaultOriginInput').value.trim();
            const resultBox = document.getElementById('keyTestResult');

            btn.disabled = true;
            btn.innerHTML = `<div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div> Guardando...`;

            try {
                const res = await fetch(`${API_URL}?action=save_settings`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ apiKey, instanceId, defaultOrigin })
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    resultBox.className = 'text-xs p-3 rounded-xl border bg-emerald-950/50 border-emerald-800 text-emerald-200 block';
                    resultBox.textContent = '¡Configuración guardada correctamente!';
                    showToast('Configuración guardada correctamente', 'success');
                    setTimeout(() => {
                        closeKeyModal();
                        checkStatus();
                    }, 1000);
                } else {
                    resultBox.className = 'text-xs p-3 rounded-xl border bg-rose-950/50 border-rose-800 text-rose-200 block';
                    resultBox.textContent = data.error || 'Error al guardar la configuración.';
                }
            } catch (err) {
                resultBox.className = 'text-xs p-3 rounded-xl border bg-rose-950/50 border-rose-800 text-rose-200 block';
                resultBox.textContent = 'Error de conexión con el servidor.';
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> Guardar Configuración`;
                lucide.createIcons();
            }
        }

        // --- TRACKING LOGIC ---
        function loadTrackExample(order, code) {
            document.getElementById('trackOrderNumber').value = order;
            document.getElementById('trackOrderCode').value = code;
            document.getElementById('trackForce').checked = false;
            handleTrack(new Event('submit'));
        }

        function forceRefreshCurrentTrack() {
            if (!currentActiveOrder || !currentActiveCode) return;
            document.getElementById('trackOrderNumber').value = currentActiveOrder;
            document.getElementById('trackOrderCode').value = currentActiveCode;
            document.getElementById('trackForce').checked = true;
            handleTrack(new Event('submit'));
        }

        async function handleTrack(e) {
            if (e) e.preventDefault();
            const orderNumber = document.getElementById('trackOrderNumber').value.trim();
            const orderCode = document.getElementById('trackOrderCode').value.trim().toUpperCase();
            const force = document.getElementById('trackForce').checked;

            const emptyState = document.getElementById('trackEmptyState');
            const loadingState = document.getElementById('trackLoadingState');
            const dataState = document.getElementById('trackDataState');
            const btnSubmit = document.getElementById('btnTrackSubmit');

            emptyState.classList.add('hidden');
            dataState.classList.add('hidden');
            loadingState.classList.remove('hidden');
            btnSubmit.disabled = true;

            try {
                const res = await fetch(`${API_URL}?action=track`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ orderNumber, orderCode, force })
                });

                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseErr) {
                    console.error("Respuesta no es JSON:", text);
                    showToast('Error del servidor: ' + text.replace(/<[^>]*>?/gm, '').substring(0, 100), 'error');
                    emptyState.classList.remove('hidden');
                    loadingState.classList.add('hidden');
                    return;
                }

                if (!res.ok) {
                    showToast(data.error || 'No se pudo consultar el envío.', 'error');
                    emptyState.classList.remove('hidden');
                    loadingState.classList.add('hidden');
                    return;
                }

                if (data.search && data.search.success === false) {
                    showToast(data.search.message || 'Guía no encontrada. Revisa los datos.', 'error');
                    emptyState.classList.remove('hidden');
                    loadingState.classList.add('hidden');
                    return;
                }

                currentActiveOrder = orderNumber;
                currentActiveCode = orderCode;

                // Render Result
                renderTrackData(data);
                loadingState.classList.add('hidden');
                dataState.classList.remove('hidden');

                if (data.from_cache) {
                    showToast('Información cargada correctamente', 'success');
                } else {
                    showToast('Actualizado en vivo desde Shalom API', 'success');
                }

                // Refresh history & stats
                try { loadRecentTracks(); } catch (e) {}
                try { checkStatus(); } catch (e) {}

            } catch (err) {
                showToast(err.message || 'No se pudo conectar con el servicio de rastreo.', 'error');
                emptyState.classList.remove('hidden');
                loadingState.classList.add('hidden');
            } finally {
                btnSubmit.disabled = false;
            }
        }

        function renderTrackData(res) {
            const info = res.search?.data || {};
            const statuses = res.statuses || {};

            const sourceBadge = document.getElementById('trackSourceBadge');
            sourceBadge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold border bg-slate-800/80 border-slate-700 text-slate-300 flex items-center gap-1';
            sourceBadge.innerHTML = `<i data-lucide="clock" class="w-3 h-3 text-rose-400"></i> ${res.cached_at || 'Actualizado'}`;

            document.getElementById('trackStatusBadge').textContent = statuses.message || (info.entregado ? 'Entregado' : 'En proceso');
            document.getElementById('trackOrderBadge').textContent = `#${info.numero_orden || ''} • ${info.codigo_orden || ''}`;
            document.getElementById('trackContent').textContent = info.contenido || 'Paquete Shalom';

            document.getElementById('trackOrigin').textContent = info.origen?.nombre || info.origen || 'No especificado';
            document.getElementById('trackDestination').textContent = info.destino?.nombre || info.destino || 'No especificado';
            document.getElementById('trackSender').textContent = info.remitente?.nombre || info.remitente || '-';
            document.getElementById('trackReceiver').textContent = info.destinatario?.nombre || info.destinatario || '-';
            document.getElementById('trackAmount').textContent = info.monto ? `S/ ${info.monto}` : (info.pago || 'Pagado');
            
            // Safe Invoice rendering
            let invoiceText = 'Sin comprobante';
            if (info.comprobante && typeof info.comprobante === 'object') {
                const comp = info.comprobante;
                const num = (comp.serie && comp.numero) ? `${comp.serie}-${comp.numero}` : (comp.serie || comp.numero || comp.tipo || '');
                if (num) invoiceText = num;
            } else if (info.comprobante && typeof info.comprobante === 'string') {
                invoiceText = info.comprobante;
            } else if (info.ticket) {
                invoiceText = typeof info.ticket === 'string' ? info.ticket : 'Ticket emitido';
            }
            document.getElementById('trackInvoice').textContent = invoiceText;
            document.getElementById('trackSavedAt').textContent = res.cached_at || 'Reciente';

            // Build Timeline
            const timelineContainer = document.getElementById('timelineCards');
            timelineContainer.innerHTML = '';

            const stages = [
                { key: 'registrado', label: 'Registrado' },
                { key: 'origen', label: 'En Origen' },
                { key: 'transito', label: 'En Tránsito' },
                { key: 'destino', label: 'En Destino' },
                { key: 'entregado', label: 'Entregado' }
            ];

            const stData = statuses.data || {};

            stages.forEach(stage => {
                const item = stData[stage.key];
                const isPassed = item && item.fecha;

                const card = document.createElement('div');
                card.className = `p-3 rounded-xl border text-center transition-all ${
                    isPassed 
                        ? 'bg-rose-950/40 border-rose-800 text-rose-200' 
                        : 'bg-slate-950 border-slate-800 text-slate-500'
                }`;
                card.innerHTML = `
                    <div class="w-6 h-6 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-bold ${
                        isPassed ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-500'
                    }">
                        ${isPassed ? '✓' : '•'}
                    </div>
                    <span class="block text-xs font-semibold ${isPassed ? 'text-white' : 'text-slate-400'}">${stage.label}</span>
                    <span class="block text-[10px] mt-1 font-mono text-slate-400">${isPassed ? item.fecha.split(' ')[0] : 'Pendiente'}</span>
                    <span class="block text-[9px] text-slate-500">${isPassed ? (item.fecha.split(' ')[1] || '') : ''}</span>
                `;
                timelineContainer.appendChild(card);
            });

            // Raw JSON
            document.getElementById('trackRawJson').textContent = JSON.stringify(res, null, 2);
            lucide.createIcons();
        }

        // --- RECENT TRACKS ---
        async function loadRecentTracks() {
            try {
                const res = await fetch(`${API_URL}?action=recent_tracks`);
                const data = await res.json();
                const tbody = document.getElementById('historyTableBody');
                const countBadge = document.getElementById('historyCount');

                if (!res.ok || !data.data || data.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="py-6 text-center text-slate-500">No hay consultas recientes aún.</td></tr>`;
                    countBadge.textContent = '0';
                    return;
                }

                countBadge.textContent = data.data.length;
                tbody.innerHTML = '';

                data.data.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b border-slate-800/60 hover:bg-slate-800/40 transition-colors';
                    tr.innerHTML = `
                        <td class="py-2.5 px-3 font-mono font-medium text-white">${item.order_number} / <span class="text-rose-400">${item.order_code}</span></td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-200 border border-slate-700">
                                ${item.status_message || 'Desconocido'}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-slate-400">${item.origin_name || '-'} → ${item.destination_name || '-'}</td>
                        <td class="py-2.5 px-3 text-slate-400 truncate max-w-[150px]">${item.content || '-'}</td>
                        <td class="py-2.5 px-3 font-mono text-slate-500 text-[11px]">${item.updated_at || '-'}</td>
                        <td class="py-2.5 px-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick="quickViewTrack('${item.order_number}', '${item.order_code}')" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-medium transition-all flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3 h-3"></i> Ver
                                </button>
                                <button onclick="deleteTrackItem(${item.id})" class="p-1 text-slate-500 hover:text-rose-400 rounded transition-colors" title="Eliminar del historial">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                lucide.createIcons();
            } catch (err) {
                console.error('Error al cargar historial:', err);
            }
        }

        function quickViewTrack(order, code) {
            switchTab('tracking');
            document.getElementById('trackOrderNumber').value = order;
            document.getElementById('trackOrderCode').value = code;
            document.getElementById('trackForce').checked = false;
            handleTrack(new Event('submit'));
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function deleteTrackItem(id) {
            if (!confirm('¿Deseas eliminar este registro del historial?')) return;
            try {
                await fetch(`${API_URL}?action=delete_track&id=${id}`);
                showToast('Registro eliminado del historial', 'info');
                loadRecentTracks();
                checkStatus();
            } catch (err) {
                showToast('Error al eliminar', 'error');
            }
        }

        // --- AGENCIES LOGIC ---
        async function loadAgencies(params = {}) {
            const grid = document.getElementById('agenciesGrid');
            const countText = document.getElementById('agenciesCount');
            const syncText = document.getElementById('agenciesLastSynced');

            grid.innerHTML = `<div class="col-span-full text-center py-12 text-slate-400 text-xs flex items-center justify-center gap-2">
                <div class="w-4 h-4 border-2 border-rose-500/30 border-t-rose-500 rounded-full animate-spin"></div>
                Cargando agencias...
            </div>`;

            try {
                const qs = new URLSearchParams(params).toString();
                const res = await fetch(`${API_URL}?action=agencies&${qs}`);
                const data = await res.json();

                if (!res.ok || !data.data) {
                    grid.innerHTML = `<div class="col-span-full text-center py-8 text-rose-400 text-xs">${data.error || 'No se pudieron cargar agencias'}</div>`;
                    return;
                }

                const agencies = data.data;
                window.agenciesLoaded = true;
                countText.textContent = `Mostrando ${agencies.length} agencias`;
                if (data.last_synced) {
                    syncText.textContent = `Última sincro API: ${data.last_synced}`;
                }

                if (agencies.length === 0) {
                    grid.innerHTML = `<div class="col-span-full text-center py-8 text-slate-400 text-xs">No se encontraron agencias con ese criterio.</div>`;
                    return;
                }

                grid.innerHTML = '';
                agencies.forEach(agency => {
                    const card = document.createElement('div');
                    card.className = 'bg-slate-950 border border-slate-800 hover:border-slate-700 rounded-xl p-4 transition-all flex flex-col justify-between group';
                    
                    const isAereo = agency.ter_aereo == 1;
                    const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${agency.latitud},${agency.longitud}`;
                    const distBadge = agency.distancia_km !== null && agency.distancia_km !== undefined 
                        ? `<span class="px-2 py-0.5 rounded text-[10px] bg-rose-950 text-rose-300 font-mono font-bold">${parseFloat(agency.distancia_km).toFixed(1)} km</span>` 
                        : '';

                    card.innerHTML = `
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <span class="font-mono text-xs text-rose-400 font-bold">ID: ${agency.ter_id}</span>
                                <div class="flex items-center gap-1.5">
                                    ${distBadge}
                                    ${isAereo ? '<span class="px-1.5 py-0.5 text-[9px] uppercase font-bold bg-sky-950 text-sky-300 border border-sky-800 rounded">Aéreo</span>' : ''}
                                </div>
                            </div>
                            <h4 class="text-xs font-bold text-white leading-snug mb-1 group-hover:text-rose-400 transition-colors">${agency.nombre || agency.lugar_over || 'Agencia Shalom'}</h4>
                            <p class="text-[11px] text-slate-400 mb-2">${agency.direccion || 'Sin dirección'}</p>
                            
                            <div class="space-y-1 text-[10px] text-slate-500 mb-3">
                                <div><strong class="text-slate-400">Horario:</strong> ${agency.hora_atencion || 'No especificado'}</div>
                                <div><strong class="text-slate-400">Tel:</strong> ${agency.telefono || '(01) 500 7878'}</div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-900 flex items-center justify-between gap-2 text-xs">
                            <a href="${mapsUrl}" target="_blank" class="text-slate-400 hover:text-white flex items-center gap-1 text-[11px]">
                                <i data-lucide="map" class="w-3.5 h-3.5 text-rose-400"></i> Mapa
                            </a>
                            <div class="flex items-center gap-1">
                                <button onclick="selectForQuote('origin', ${agency.ter_id}, '${escapeQuoteName(agency.nombre)}')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-[10px] text-slate-200">
                                    Origen
                                </button>
                                <button onclick="selectForQuote('destination', ${agency.ter_id}, '${escapeQuoteName(agency.nombre)}')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-[10px] text-slate-200">
                                    Destino
                                </button>
                            </div>
                        </div>
                    `;
                    grid.appendChild(card);
                });
                lucide.createIcons();
            } catch (err) {
                grid.innerHTML = `<div class="col-span-full text-center py-8 text-rose-400 text-xs">Error al cargar agencias.</div>`;
            }
        }

        async function syncAgencies(forced = false) {
            const btn = document.getElementById('btnSyncAgencies');
            btn.disabled = true;
            btn.innerHTML = `<div class="w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full animate-spin"></div> Sincronizando...`;

            try {
                const res = await fetch(`${API_URL}?action=sync_agencies`);
                const data = await res.json();
                if (res.ok && data.success) {
                    showToast('Catálogo de agencias actualizado', 'success');
                    loadAgencies();
                    checkStatus();
                } else {
                    showToast(data.error || 'Error al sincronizar catálogo', 'error');
                }
            } catch (err) {
                showToast('Error de conexión al sincronizar', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualizar Catálogo`;
                lucide.createIcons();
            }
        }

        function escapeQuoteName(name) {
            return (name || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
        }

        function handleAgenciesSearch(e) {
            e.preventDefault();
            const q = document.getElementById('agencyQ').value.trim();
            const departamento = document.getElementById('agencyDept').value.trim();
            const aereo = document.getElementById('agencyAereo').checked;

            const params = {};
            if (q) params.q = q;
            if (departamento) params.departamento = departamento;
            if (aereo) params.aereo = 'true';

            loadAgencies(params);
        }

        function getUserLocation() {
            if (!navigator.geolocation) {
                showToast('Tu navegador no soporta geolocalización.', 'error');
                return;
            }
            const btn = document.getElementById('btnNearby');
            btn.innerHTML = `<div class="w-3.5 h-3.5 border-2 border-slate-400 border-t-white rounded-full animate-spin"></div> Calculando distancia...`;

            navigator.geolocation.getCurrentPosition(
                pos => {
                    const lat = pos.coords.latitude.toFixed(6);
                    const lng = pos.coords.longitude.toFixed(6);
                    btn.innerHTML = `<i data-lucide="crosshair" class="w-4 h-4 text-rose-400"></i> Cerca de Mí (GPS)`;
                    lucide.createIcons();
                    showToast('Agencias ordenadas por cercanía a tu ubicación', 'success');
                    loadAgencies({ near: `${lat},${lng}`, per_page: 25 });
                },
                err => {
                    btn.innerHTML = `<i data-lucide="crosshair" class="w-4 h-4 text-rose-400"></i> Cerca de Mí (GPS)`;
                    lucide.createIcons();
                    showToast('Permiso de ubicación denegado.', 'error');
                }
            );
        }

        function selectForQuote(target, id, name) {
            if (target === 'origin') {
                $('#quoteOrigin').val(id).trigger('change');
                showToast(`Agencia seleccionada como Origen`, 'info');
            } else {
                $('#quoteDest').val(id).trigger('change');
                showToast(`Agencia seleccionada como Destino`, 'info');
            }
            switchTab('quote');
        }

        // --- QUOTE LOGIC ---
        async function handleQuote(e) {
            e.preventDefault();
            const origin = ($('#quoteOrigin').val() || '').trim();
            const destination = ($('#quoteDest').val() || '').trim();

            if (!origin || !destination) {
                showToast('Por favor selecciona la agencia de origen y destino.', 'error');
                return;
            }
            const width = document.getElementById('quoteWidth').value.trim();
            const height = document.getElementById('quoteHeight').value.trim();
            const length = document.getElementById('quoteLength').value.trim();
            const weight = document.getElementById('quoteWeight').value.trim();

            const btn = document.getElementById('btnQuoteSubmit');
            const emptyState = document.getElementById('quoteEmptyState');
            const dataState = document.getElementById('quoteDataState');

            btn.disabled = true;
            btn.innerHTML = `<div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div> Calculando...`;

            try {
                const payload = { origin, destination };
                if (width && height && length && weight) {
                    payload.width = width;
                    payload.height = height;
                    payload.length = length;
                    payload.weight = weight;
                }

                const res = await fetch(`${API_URL}?action=quote`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (!res.ok) {
                    showToast(data.error || 'No se pudo cotizar.', 'error');
                    return;
                }

                // Render Tariffs
                renderQuoteData(data);
                emptyState.classList.add('hidden');
                dataState.classList.remove('hidden');
                showToast('Tarifa calculada con éxito', 'success');

            } catch (err) {
                showToast('Error al conectar con el cotizador.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="calculator" class="w-4 h-4"></i> Calcular Tarifa`;
                lucide.createIcons();
            }
        }

        function renderQuoteData(res) {
            const data = res.data || {};
            const tariff = data.tariff || {};

            document.getElementById('quoteModeBadge').textContent = res.modo || 'estándar';

            const volCard = document.getElementById('quoteVolumeCard');
            if (data.price) {
                volCard.classList.remove('hidden');
                document.getElementById('quoteVolumePrice').textContent = `S/ ${data.price.toFixed(2)}`;
            } else {
                volCard.classList.add('hidden');
            }

            const cardsContainer = document.getElementById('tariffCards');
            cardsContainer.innerHTML = '';

            const packageNames = {
                'sobre': 'Sobre',
                'cajapaquetexxs': 'Paquete XXS',
                'cajapaquetexs': 'Paquete XS',
                'cajapaquetes': 'Paquete S',
                'cajapaquetem': 'Paquete M',
                'cajapaquetel': 'Paquete L'
            };

            for (const [key, price] of Object.entries(tariff)) {
                const card = document.createElement('div');
                card.className = 'p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between';
                card.innerHTML = `
                    <span class="text-xs text-slate-300 font-medium">${packageNames[key] || key}</span>
                    <span class="text-sm font-bold text-rose-400">S/ ${price}</span>
                `;
                cardsContainer.appendChild(card);
            }
        }

        // --- DNI LOGIC ---
        async function handleDni(e) {
            e.preventDefault();
            const dni = document.getElementById('dniInput').value.trim();
            const force = document.getElementById('dniForce').checked;
            const btn = document.getElementById('btnDniSubmit');
            const resultBox = document.getElementById('dniResult');
            const sourceBadge = document.getElementById('dniSourceBadge');

            btn.disabled = true;
            btn.innerHTML = `<div class="w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>`;

            try {
                const res = await fetch(`${API_URL}?action=dni&dni=${dni}&force=${force ? '1' : '0'}`);
                const data = await res.json();

                if (!res.ok) {
                    showToast(data.error || 'DNI no encontrado o error en el servicio.', 'error');
                    resultBox.classList.add('hidden');
                    return;
                }

                const person = data.data || {};
                const fullName = `${person.nombres || ''} ${person.apellidoPaterno || ''} ${person.apellidoMaterno || ''}`.trim();
                
                document.getElementById('dniFullName').textContent = fullName || 'Persona encontrada';
                document.getElementById('dniNumber').textContent = person.dni || dni;
                document.getElementById('dniNames').textContent = person.nombres || '-';
                document.getElementById('dniSurnames').textContent = `${person.apellidoPaterno || ''} ${person.apellidoMaterno || ''}`.trim() || '-';

                sourceBadge.className = 'text-[10px] font-semibold px-2.5 py-1 rounded-full border bg-emerald-950/60 border-emerald-800 text-emerald-300 flex items-center gap-1';
                sourceBadge.innerHTML = `<i data-lucide="check-circle" class="w-3 h-3"></i> Verificado`;

                resultBox.classList.remove('hidden');
                showToast('Datos del DNI consultados con éxito', 'success');
                checkStatus();

            } catch (err) {
                showToast('Error de red al consultar DNI.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="search" class="w-4 h-4"></i> Consultar`;
                lucide.createIcons();
            }
        }

                // Initialize Select2 Agency Pickers
        $(document).ready(function() {
            $('.select2-agency').select2({
                width: '100%',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron agencias";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
        });

        
        // --- QR SCANNER LOGIC ---
        let html5QrScanner = null;
        let qrCurrentMode = 'camera';

        function openQrModal() {
            document.getElementById('qrModal').classList.remove('hidden');
            switchQrMode('camera');
            lucide.createIcons();
        }

        function closeQrModal() {
            stopQrCamera();
            document.getElementById('qrModal').classList.add('hidden');
        }

        function switchQrMode(mode) {
            qrCurrentMode = mode;
            const btnCam = document.getElementById('qrTabCamera');
            const btnFile = document.getElementById('qrTabFile');
            const secCam = document.getElementById('qrCameraSection');
            const secFile = document.getElementById('qrFileSection');

            if (mode === 'camera') {
                btnCam.className = 'flex-1 py-1.5 rounded-lg font-medium text-center transition-all bg-rose-600 text-white';
                btnFile.className = 'flex-1 py-1.5 rounded-lg font-medium text-center transition-all text-slate-400 hover:text-white';
                secCam.classList.remove('hidden');
                secFile.classList.add('hidden');
                startQrCamera();
            } else {
                btnFile.className = 'flex-1 py-1.5 rounded-lg font-medium text-center transition-all bg-rose-600 text-white';
                btnCam.className = 'flex-1 py-1.5 rounded-lg font-medium text-center transition-all text-slate-400 hover:text-white';
                secFile.classList.remove('hidden');
                secCam.classList.add('hidden');
                stopQrCamera();
            }
            lucide.createIcons();
        }

        function startQrCamera() {
            const qrBox = document.getElementById('qrReader');
            if (!qrBox) return;

            if (html5QrScanner) {
                stopQrCamera();
            }

            try {
                html5QrScanner = new Html5Qrcode("qrReader");
                const config = {
                    fps: 10,
                    qrbox: function(viewfinderWidth, viewfinderHeight) {
                        const edgeSize = Math.floor(Math.min(viewfinderWidth, viewfinderHeight) * 0.75);
                        return { width: edgeSize, height: edgeSize };
                    }
                };

                html5QrScanner.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => {
                        stopQrCamera();
                        processScannedQr(decodedText);
                    },
                    () => {
                        // ignore frame tick
                    }
                ).catch(err => {
                    console.warn("No se pudo iniciar la cámara:", err);
                    const placeholder = document.getElementById('qrCameraPlaceholder');
                    if (placeholder) {
                        placeholder.innerHTML = `
                            <i data-lucide="camera-off" class="w-8 h-8 mx-auto mb-2 text-rose-500"></i>
                            <span class="text-rose-300 font-semibold block mb-1">Cámara no disponible</span>
                            <span class="text-slate-400 text-[11px] block">Puedes usar la pestaña <strong>Subir Imagen</strong> para leer una foto del ticket.</span>
                        `;
                        lucide.createIcons();
                    }
                });
            } catch (e) {
                console.error("Html5Qrcode error:", e);
            }
        }

        function stopQrCamera() {
            if (html5QrScanner) {
                try {
                    html5QrScanner.stop().catch(() => {}).finally(() => {
                        html5QrScanner = null;
                    });
                } catch (e) {
                    html5QrScanner = null;
                }
            }
        }

        async function handleQrFileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            const statusBox = document.getElementById('qrFileStatus');
            statusBox.className = 'text-xs text-center text-slate-300 block';
            statusBox.innerHTML = '<div class="w-4 h-4 border-2 border-rose-500/30 border-t-rose-500 rounded-full animate-spin inline-block mr-1"></div> Analizando imagen...';

            try {
                const scanner = new Html5Qrcode("qrReader");
                const decodedText = await scanner.scanFile(file, true);
                statusBox.classList.add('hidden');
                processScannedQr(decodedText);
            } catch (err) {
                statusBox.className = 'text-xs text-center text-rose-400 block';
                statusBox.textContent = 'No se detectó un código QR válido en la imagen. Intenta con una foto más clara.';
                showToast('No se encontró un código QR en la imagen.', 'error');
            } finally {
                event.target.value = '';
            }
        }

        function processScannedQr(decodedText) {
            decodedText = (decodedText || '').trim();
            console.log("QR decodificado:", decodedText);

            // 1. Direct Pattern: "104425275/H733" or "104425275 - H733"
            const directMatch = decodedText.match(/^(\d{8,12})\s*[\/\-]\s*([a-zA-Z0-9]{4})$/);
            if (directMatch) {
                document.getElementById('trackOrderNumber').value = directMatch[1];
                document.getElementById('trackOrderCode').value = directMatch[2].toUpperCase();
                closeQrModal();
                showToast(`QR detectado: Guía #${directMatch[1]}`, 'success');
                handleTrack(new Event('submit'));
                return;
            }

            // 2. Shalom Standard QR: "107869605/document/1/" or "107869605/document" or "107869605"
            const oseMatch = decodedText.match(/^(\d{7,15})(?:\/document(?:\/\d*)?)?/i);
            if (oseMatch) {
                const oseId = oseMatch[1];
                resolveOseAndTrack(oseId);
                return;
            }

            // 3. Fallback: Any 7-15 digit number in string
            const digitsMatch = decodedText.match(/\b\d{7,15}\b/);
            if (digitsMatch) {
                resolveOseAndTrack(digitsMatch[0]);
                return;
            }

            showToast('Formato QR no reconocido: ' + decodedText.substring(0, 30), 'error');
        }

        async function resolveOseAndTrack(oseId) {
            showToast(`Resolviendo código QR (${oseId})...`, 'info');
            closeQrModal();

            try {
                const res = await fetch(`${API_URL}?action=track_by_ose&ose_id=${oseId}`);
                const data = await res.json();

                if (!res.ok || !data.success) {
                    showToast(data.error || 'No se pudo resolver el envío con el código QR.', 'error');
                    return;
                }

                document.getElementById('trackOrderNumber').value = data.orderNumber;
                document.getElementById('trackOrderCode').value = data.orderCode;
                showToast(`QR resuelto: Orden #${data.orderNumber} • ${data.orderCode}`, 'success');

                // Execute track immediately
                handleTrack(new Event('submit'));

            } catch (err) {
                console.error("Error al resolver OSE:", err);
                showToast('Error de conexión al resolver el código QR.', 'error');
            }
        }

        // Init
        checkStatus();
        loadRecentTracks();
    </script>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

<?php
require_once __DIR__ . '/config.php';

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

// --- Sync Agencies to SQLite ---
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

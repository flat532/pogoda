<?php
// inpost/api.php — pośrednik do publicznego API punktów InPost (bez klucza).
// action=point&code=GLI01BAPP          → dane paczkomatu z odczytem jakości powietrza
// action=nearby&lat=..&lon=..          → najbliższe paczkomaty z odczytem (do 3 km)
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

const INPOST_API = 'https://api-pl-points.easypack24.net/v1/points';
const CACHE_TTL = 300; // 5 minut — czujniki i tak nie raportują częściej

function fail(int $code, string $message): void {
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// Pobranie z pamięcią podręczną w katalogu tymczasowym
function fetchJson(string $url): ?array {
    $file = sys_get_temp_dir() . '/inpost_' . md5($url) . '.json';
    if (is_file($file) && time() - filemtime($file) < CACHE_TTL) {
        return json_decode(file_get_contents($file), true);
    }
    $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true, 'header' => "Accept: application/json\r\n"]]);
    $raw = @file_get_contents($url, false, $ctx);
    $data = $raw ? json_decode($raw, true) : null;
    if (is_array($data)) {
        @file_put_contents($file, $raw);
    }
    return $data;
}

function pointView(array $p): array {
    return [
        'name' => $p['name'] ?? null,
        'status' => $p['status'] ?? null,
        'air_index_level' => $p['air_index_level'] ?? null,
        'address' => $p['address'] ?? null,
        'city' => $p['address_details']['city'] ?? null,
        'location' => $p['location'] ?? null,
        'location_description' => $p['location_description'] ?? null,
        'location_type' => $p['location_type'] ?? null,
        'opening_hours' => $p['opening_hours'] ?? null,
        'image_url' => $p['image_url'] ?? null,
        'distance' => $p['distance'] ?? null,
        'location_date' => $p['location_date'] ?? null,
        'agency' => $p['agency'] ?? null,
    ];
}

$action = $_GET['action'] ?? 'point';

if ($action === 'point') {
    $code = strtoupper(trim($_GET['code'] ?? ''));
    if (!preg_match('/^[A-Z0-9-]{3,20}$/', $code)) {
        fail(400, 'Nieprawidłowy kod paczkomatu');
    }
    $data = fetchJson(INPOST_API . '/' . rawurlencode($code));
    if (!$data) fail(502, 'Brak odpowiedzi z API InPost');
    if (($data['status'] ?? null) === 404 || empty($data['name'])) fail(404, 'Nie znaleziono paczkomatu');
    echo json_encode(pointView($data), JSON_UNESCAPED_UNICODE);
}

elseif ($action === 'nearby') {
    $lat = filter_var($_GET['lat'] ?? null, FILTER_VALIDATE_FLOAT);
    $lon = filter_var($_GET['lon'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($lat === false || $lon === false || abs($lat) > 90 || abs($lon) > 180) {
        fail(400, 'Nieprawidłowe współrzędne');
    }
    $query = http_build_query([
        'relative_point' => sprintf('%.5f,%.5f', $lat, $lon),
        'max_distance' => 3000,
        'per_page' => 100,
        'fields' => 'name,status,air_index_level,address,distance,location',
    ]);
    $data = fetchJson(INPOST_API . '?' . $query);
    if (!$data || !isset($data['items'])) fail(502, 'Brak odpowiedzi z API InPost');

    $items = array_values(array_filter($data['items'], fn($p) => !empty($p['air_index_level'])));
    usort($items, fn($a, $b) => ($a['distance'] ?? 0) <=> ($b['distance'] ?? 0));
    echo json_encode(array_map('pointView', array_slice($items, 0, 8)), JSON_UNESCAPED_UNICODE);
}

else {
    fail(400, 'Nieznana akcja');
}

<?php
// Tylko z crona (CLI) — blokada wywołań przez WWW
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

chdir(__DIR__);

// Wczytanie konfiguracji
$config = require 'config.php';
$apiConfig = $config['weather_api'];
$db = $config['db'];

$apiKey = $apiConfig['key'];

// Format daty — wspólny dla wszystkich miast z jednego przebiegu
$date = date('Y-m-d-H');
$datetime = date('Y-m-d H:00:00');

$sql = "INSERT INTO weather_data (
            location, measurement_datetime, temperature, pressure, humidity,
            wind_speed, wind_direction, rainfall, snowfall, visibility,
            weather_main, weather_description, weather_icon, cloudiness,
            feels_like, sea_level_pressure, ground_level_pressure, raw_json
        ) VALUES (
            :location, :measurement_datetime, :temperature, :pressure, :humidity,
            :wind_speed, :wind_direction, :rainfall, :snowfall, :visibility,
            :weather_main, :weather_description, :weather_icon, :cloudiness,
            :feels_like, :sea_level_pressure, :ground_level_pressure, :raw_json
        ) ON DUPLICATE KEY UPDATE
            temperature = VALUES(temperature),
            pressure = VALUES(pressure),
            humidity = VALUES(humidity),
            wind_speed = VALUES(wind_speed),
            wind_direction = VALUES(wind_direction),
            rainfall = VALUES(rainfall),
            snowfall = VALUES(snowfall),
            visibility = VALUES(visibility),
            weather_main = VALUES(weather_main),
            weather_description = VALUES(weather_description),
            weather_icon = VALUES(weather_icon),
            cloudiness = VALUES(cloudiness),
            feels_like = VALUES(feels_like),
            sea_level_pressure = VALUES(sea_level_pressure),
            ground_level_pressure = VALUES(ground_level_pressure),
            raw_json = VALUES(raw_json),
            updated_at = CURRENT_TIMESTAMP";

$stmt = null;

// Błąd jednego miasta nie przerywa pobierania pozostałych
foreach ($config['locations'] as $location => $cityId) {
    $url = "{$apiConfig['url_base']}?id={$cityId}&appid={$apiKey}&units=metric";
    $data = file_get_contents($url);

    if (empty($data) || $data === 'null') {
        echo "❌ No data for {$location} on {$date}<br>";
        continue;
    }

    // Upewnij się, że katalog istnieje
    if (!is_dir('archive')) { mkdir('archive', 0755, true); }

    $filename = "archive/{$location}-{$date}.json";
    // Przy zmianie na czas zimowy godzina 02:00 występuje dwa razy — nie nadpisuj pierwszego pliku
    if (file_exists($filename)) {
        $filename = "archive/{$location}-{$date}-" . date('T') . ".json";
    }
    file_put_contents($filename, $data);
    echo "✅ Saved data to file: {$filename}<br>";

    $decoded = json_decode($data, true);

    if (!$decoded || !isset($decoded['main']['temp'])) {
        echo "❌ Invalid JSON format for {$location}<br>";
        continue;
    }

    try {
        if (!$stmt) {
            $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $stmt = $pdo->prepare($sql);
        }

        $stmt->execute([
            'location' => $location,
            'measurement_datetime' => $datetime,
            'temperature' => $decoded['main']['temp'] ?? null,
            'pressure' => $decoded['main']['pressure'] ?? null,
            'humidity' => $decoded['main']['humidity'] ?? null,
            'wind_speed' => $decoded['wind']['speed'] ?? 0,
            'wind_direction' => $decoded['wind']['deg'] ?? null,
            'rainfall' => $decoded['rain']['1h'] ?? 0,
            'snowfall' => $decoded['snow']['1h'] ?? 0,
            'visibility' => $decoded['visibility'] ?? null,
            'weather_main' => $decoded['weather'][0]['main'] ?? null,
            'weather_description' => $decoded['weather'][0]['description'] ?? null,
            'weather_icon' => $decoded['weather'][0]['icon'] ?? null,
            'cloudiness' => $decoded['clouds']['all'] ?? null,
            'feels_like' => $decoded['main']['feels_like'] ?? null,
            'sea_level_pressure' => $decoded['main']['sea_level'] ?? null,
            'ground_level_pressure' => $decoded['main']['grnd_level'] ?? null,
            'raw_json' => json_encode($decoded)
        ]);

        echo "✅ Data inserted/updated in DB for {$location} {$datetime}<br>";

    } catch (PDOException $e) {
        echo "❌ DB Error ({$location}): " . $e->getMessage() . "<br>";
    }
}

// Jakość powietrza z paczkomatów InPost (publiczne API punktów, bez klucza)
$airStmt = null;
$httpContext = stream_context_create(['http' => ['timeout' => 15, 'header' => "Accept: application/json\r\n"]]);

foreach ($config['inpost_air'] ?? [] as $location => $point) {
    $raw = @file_get_contents('https://api-pl-points.easypack24.net/v1/points/' . rawurlencode($point), false, $httpContext);
    $decoded = $raw ? json_decode($raw, true) : null;

    if (!$decoded || ($decoded['name'] ?? null) !== $point) {
        echo "❌ No InPost data for {$point} ({$location})<br>";
        continue;
    }

    try {
        if (!$airStmt) {
            $pdo ??= new PDO("mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}", $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $airStmt = $pdo->prepare("INSERT INTO air_quality (location, measurement_datetime, source, point, air_index_level, raw_json)
                                      VALUES (:location, :measurement_datetime, 'inpost', :point, :level, :raw_json)
                                      ON DUPLICATE KEY UPDATE point = VALUES(point), air_index_level = VALUES(air_index_level), raw_json = VALUES(raw_json)");
        }

        $airStmt->execute([
            'location' => $location,
            'measurement_datetime' => $datetime,
            'point' => $point,
            'level' => $decoded['air_index_level'] ?? null,
            'raw_json' => json_encode([
                'name' => $decoded['name'],
                'status' => $decoded['status'] ?? null,
                'air_index_level' => $decoded['air_index_level'] ?? null,
                'address' => $decoded['address'] ?? null,
                'location' => $decoded['location'] ?? null,
            ], JSON_UNESCAPED_UNICODE)
        ]);

        echo "✅ Air quality {$point}: " . ($decoded['air_index_level'] ?? 'brak') . "<br>";
    } catch (PDOException $e) {
        echo "❌ DB Error (air {$location}): " . $e->getMessage() . "<br>";
    }
}
?>

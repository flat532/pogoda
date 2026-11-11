<?php
// Wspólny zapis odczytów OpenWeatherMap Air Pollution (update_data.php i jednorazowe uzupełnianie historii)

const POLLUTION_UPSERT = "INSERT INTO air_pollution (location, measurement_datetime, aqi, pm2_5, pm10, no2, o3, so2, co, no, nh3)
    VALUES (:location, :measurement_datetime, :aqi, :pm2_5, :pm10, :no2, :o3, :so2, :co, :no, :nh3)
    ON DUPLICATE KEY UPDATE aqi = VALUES(aqi), pm2_5 = VALUES(pm2_5), pm10 = VALUES(pm10), no2 = VALUES(no2),
        o3 = VALUES(o3), so2 = VALUES(so2), co = VALUES(co), no = VALUES(no), nh3 = VALUES(nh3)";

// Element listy z odpowiedzi API → parametry zapytania
function pollutionRow(string $location, string $datetime, array $item): array {
    $c = $item['components'] ?? [];
    return [
        'location' => $location,
        'measurement_datetime' => $datetime,
        'aqi' => $item['main']['aqi'] ?? null,
        'pm2_5' => $c['pm2_5'] ?? null,
        'pm10' => $c['pm10'] ?? null,
        'no2' => $c['no2'] ?? null,
        'o3' => $c['o3'] ?? null,
        'so2' => $c['so2'] ?? null,
        'co' => $c['co'] ?? null,
        'no' => $c['no'] ?? null,
        'nh3' => $c['nh3'] ?? null,
    ];
}

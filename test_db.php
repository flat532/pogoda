<?php
// Skopiuj tu swoje configi DB
$dbConfig = [
    'host' => 'localhost',
    'database' => 'srv55800_weather_gliwice',
    'username' => 'srv55800_weather_gliwice',
    'password' => 'and1010101Drz$' // Pamiętaj o nowym haśle jeśli zmieniłeś!
];

try {
    $pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4", $dbConfig['username'], $dbConfig['password']);
    
    // Pobierz 5 ostatnich wpisów
    $stmt = $pdo->query("SELECT measurement_datetime, temperature, pressure FROM weather_data ORDER BY measurement_datetime DESC LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        echo "<h1>✅ Sukces! Mamy dane w bazie:</h1>";
        echo "<pre>" . print_r($results, true) . "</pre>";
    } else {
        echo "<h1>⚠️ Tabela jest pusta. Sprawdź logi lub strukturę tabeli.</h1>";
    }

} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>
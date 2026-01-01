<?php
// check_2024.php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnostyka danych pogodowych</h1>";

try {
    // 1. Wczytanie konfiguracji (bezpieczne połączenie)
    if (!file_exists('config.php')) {
        die("❌ Błąd: Brak pliku config.php!");
    }
    $config = require 'config.php';
    $db = $config['db'];
    
    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Połączono z bazą danych.<br><hr>";

    // 2. Sprawdzanie roku 2024
    $targetYear = 2024;
    
    echo "<h3>Sprawdzanie roku $targetYear:</h3>";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM weather_data WHERE YEAR(measurement_datetime) = :year");
    $stmt->execute(['year' => $targetYear]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "<h4 style='color: green;'>✅ Znaleziono $count rekordów z roku $targetYear!</h4>";
        
        // Pobierz zakres dat
        $stmtRange = $pdo->prepare("SELECT MIN(measurement_datetime) as first, MAX(measurement_datetime) as last FROM weather_data WHERE YEAR(measurement_datetime) = :year");
        $stmtRange->execute(['year' => $targetYear]);
        $range = $stmtRange->fetch(PDO::FETCH_ASSOC);
        
        echo "📅 Zakres danych: od <b>{$range['first']}</b> do <b>{$range['last']}</b><br>";
        
        // Pokaż kilka przykładowych wpisów
        echo "<br><i>5 przykładowych wpisów:</i><br>";
        $stmtSamples = $pdo->prepare("SELECT measurement_datetime, temperature FROM weather_data WHERE YEAR(measurement_datetime) = :year LIMIT 5");
        $stmtSamples->execute(['year' => $targetYear]);
        $samples = $stmtSamples->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($samples, true) . "</pre>";

    } else {
        echo "<h4 style='color: red;'>⚠️ Brak danych dla roku $targetYear.</h4>";
    }

    // 3. Sprawdźmy jakie W OGÓLE mamy lata w bazie
    echo "<hr><h3>📊 Dostępne lata w bazie danych:</h3>";
    $stmtYears = $pdo->query("SELECT DISTINCT YEAR(measurement_datetime) as rok, COUNT(*) as ilosc FROM weather_data GROUP BY rok ORDER BY rok DESC");
    $yearsData = $stmtYears->fetchAll(PDO::FETCH_ASSOC);

    if ($yearsData) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'><tr><th>Rok</th><th>Liczba pomiarów</th></tr>";
        foreach ($yearsData as $row) {
            echo "<tr><td><strong>{$row['rok']}</strong></td><td>{$row['ilosc']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Tabela jest pusta.";
    }

} catch (PDOException $e) {
    echo "❌ Błąd bazy danych: " . $e->getMessage();
}
?>

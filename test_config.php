<?php
// test_config.php
// Włączamy pełne raportowanie błędów, żeby widzieć wszystko
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnostyka Konfiguracji</h1>";

$envPath = __DIR__ . '/.env';

// 1. Sprawdzenie pliku .env
echo "<h3>1. Sprawdzanie pliku .env</h3>";
if (file_exists($envPath)) {
    echo "✅ Plik .env istnieje w: $envPath<br>";
    
    if (is_readable($envPath)) {
        echo "✅ Plik .env jest czytelny dla PHP.<br>";
        
        $env = parse_ini_file($envPath);
        if ($env === false) {
            echo "❌ Błąd: Funkcja parse_ini_file() nie mogła przetworzyć pliku. Sprawdź składnię w .env (np. cudzysłowy, spacje).<br>";
        } else {
            echo "✅ Plik .env przetworzony poprawnie. Znalezione klucze:<br>";
            echo "<pre>";
            // Wypisujemy tylko klucze, ukrywamy wartości dla bezpieczeństwa
            print_r(array_keys($env));
            echo "</pre>";
        }
    } else {
        echo "❌ Błąd: Plik .env istnieje, ale PHP nie ma uprawnień do jego odczytu (chmod).<br>";
    }
} else {
    echo "❌ Błąd: Nie znaleziono pliku .env w katalogu: " . __DIR__ . "<br>";
}

// 2. Sprawdzenie config.php
echo "<h3>2. Sprawdzanie config.php</h3>";
if (file_exists('config.php')) {
    try {
        $config = require 'config.php';
        echo "✅ config.php załadowany.<br>";
        
        if (isset($config['db']['host']) && !empty($config['db']['host'])) {
             echo "✅ Konfiguracja bazy danych wygląda na wypełnioną.<br>";
        } else {
             echo "❌ Błąd: Tablica konfiguracyjna bazy danych jest pusta lub niekompletna.<br>";
        }
    } catch (Exception $e) {
        echo "❌ Błąd podczas ładowania config.php: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Błąd: Brak pliku config.php.<br>";
}

// 3. Test połączenia z bazą
echo "<h3>3. Test połączenia z bazą danych</h3>";
if (isset($config)) {
    $db = $config['db'];
    try {
        $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
        $pdo = new PDO($dsn, $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ SUKCES: Połączono z bazą danych!<br>";
        
        // Próbne zapytanie
        $stmt = $pdo->query("SELECT count(*) FROM weather_data");
        $count = $stmt->fetchColumn();
        echo "ℹ️ Liczba rekordów w tabeli 'weather_data': $count<br>";
        
    } catch (PDOException $e) {
        echo "❌ BŁĄD POŁĄCZENIA: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ Pominięto test bazy z powodu wcześniejszych błędów.<br>";
}
?>
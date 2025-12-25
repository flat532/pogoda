<?php
// api.php
header('Content-Type: application/json');
error_reporting(0);

// Wczytanie konfiguracji
$config = require 'config.php';
$db = $config['db'];

try {
    // Użycie danych z konfiguracji
    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    
    $action = $_GET['action'] ?? 'chart_data';
    $date = $_GET['date'] ?? date('Y-m-d');

    // 1. DANE DZIENNE
    if ($action === 'chart_data') {
        $stmt = $pdo->prepare("SELECT * FROM weather_data WHERE DATE(measurement_datetime) = :selectedDate ORDER BY measurement_datetime ASC");
        $stmt->execute(['selectedDate' => $date]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 2. REKORDY ROCZNE
    elseif ($action === 'year_stats') {
        $stmt = $pdo->query("
            SELECT 
                MAX(temperature) as max_temp, MIN(temperature) as min_temp,
                (SELECT measurement_datetime FROM weather_data WHERE temperature = (SELECT MAX(temperature) FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 1 YEAR)) LIMIT 1) as max_temp_date,
                (SELECT measurement_datetime FROM weather_data WHERE temperature = (SELECT MIN(temperature) FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 1 YEAR)) LIMIT 1) as min_temp_date,
                MAX(pressure) as max_press, MIN(pressure) as min_press,
                (SELECT measurement_datetime FROM weather_data WHERE pressure = (SELECT MAX(pressure) FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 1 YEAR)) LIMIT 1) as max_press_date,
                (SELECT measurement_datetime FROM weather_data WHERE pressure = (SELECT MIN(pressure) FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 1 YEAR)) LIMIT 1) as min_press_date
            FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }

    // 3. TREND ROCZNY
    elseif ($action === 'yearly_trend') {
        $stmt = $pdo->query("SELECT DATE(measurement_datetime) as date, MAX(temperature) as max_temp, MIN(temperature) as min_temp FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 1 YEAR) GROUP BY DATE(measurement_datetime) ORDER BY date ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 4. TABELA MIESIĘCZNA
    elseif ($action === 'monthly_stats') {
        $stmt = $pdo->query("SELECT DATE_FORMAT(measurement_datetime, '%Y-%m') as month_id, MAX(temperature) as max_temp, MIN(temperature) as min_temp FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 1 YEAR) GROUP BY month_id ORDER BY month_id DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 5. AKTUALNE WARUNKI
    elseif ($action === 'current') {
        $stmt = $pdo->query("SELECT * FROM weather_data ORDER BY measurement_datetime DESC LIMIT 1");
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
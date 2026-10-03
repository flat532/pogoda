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

    // 1. DANE DZIENNE (To zostało usunięte, a jest konieczne jako pierwszy 'if')
    if ($action === 'chart_data') {
        $stmt = $pdo->prepare("SELECT * FROM weather_data WHERE measurement_datetime >= :selectedDate AND measurement_datetime < :selectedDate + INTERVAL 1 DAY ORDER BY measurement_datetime ASC");
        $stmt->execute(['selectedDate' => $date]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 2. REKORDY ROCZNE (Twoja nowa sekcja)
    elseif ($action === 'year_stats') {
        // Pobierz rok z parametru GET lub użyj bieżącego
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        // Zakres dat zamiast YEAR(...), żeby zapytania korzystały z indeksu na measurement_datetime
        $range = ['start' => "$year-01-01", 'end' => ($year + 1) . "-01-01"];
        $inYear = "measurement_datetime >= :start AND measurement_datetime < :end";

        // Jeden przebieg po roku dla wartości skrajnych
        $stmt = $pdo->prepare("SELECT MAX(temperature) AS max_temp, MIN(temperature) AS min_temp,
                                      MAX(pressure) AS max_press, MIN(pressure) AS min_press
                               FROM weather_data WHERE $inYear");
        $stmt->execute($range);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Data pierwszego wystąpienia każdej wartości skrajnej
        $columns = ['max_temp' => 'temperature', 'min_temp' => 'temperature', 'max_press' => 'pressure', 'min_press' => 'pressure'];
        $dateStmts = [];
        foreach ($columns as $key => $column) {
            $stats[$key . '_date'] = null;
            if ($stats[$key] === null) continue;
            $dateStmts[$column] ??= $pdo->prepare("SELECT measurement_datetime FROM weather_data
                                                   WHERE $inYear AND $column = :val
                                                   ORDER BY measurement_datetime ASC LIMIT 1");
            $dateStmts[$column]->execute($range + ['val' => $stats[$key]]);
            $stats[$key . '_date'] = $dateStmts[$column]->fetchColumn() ?: null;
        }

        echo json_encode([
            'max_temp'       => $stats['max_temp'],
            'min_temp'       => $stats['min_temp'],
            'max_temp_date'  => $stats['max_temp_date'],
            'min_temp_date'  => $stats['min_temp_date'],
            'max_press'      => $stats['max_press'],
            'min_press'      => $stats['min_press'],
            'max_press_date' => $stats['max_press_date'],
            'min_press_date' => $stats['min_press_date'],
        ]);
    }

    // 3. TREND ROCZNY
    elseif ($action === 'yearly_trend') {
        $stmt = $pdo->query("SELECT DATE(measurement_datetime) as date, MAX(temperature) as max_temp, MIN(temperature) as min_temp FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 1 YEAR) GROUP BY DATE(measurement_datetime) ORDER BY date ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 4. ŚREDNIE TEMPERATURY (NOWE)
    elseif ($action === 'avg_stats') {
        $range = $_GET['range'] ?? '30days';
        
        $sql = "";
        $params = [];

        if ($range === 'today') {
            // Grupuj po godzinie dla dzisiejszego dnia
            $sql = "SELECT DATE_FORMAT(measurement_datetime, '%Y-%m-%d %H:00') as date_label, AVG(temperature) as avg_temp 
                    FROM weather_data 
                    WHERE measurement_datetime >= CURDATE() AND measurement_datetime < CURDATE() + INTERVAL 1 DAY 
                    GROUP BY date_label 
                    ORDER BY date_label ASC";
        } elseif ($range === '7days') {
            $sql = "SELECT DATE(measurement_datetime) as date_label, AVG(temperature) as avg_temp 
                    FROM weather_data 
                    WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 7 DAY) 
                    GROUP BY date_label 
                    ORDER BY date_label ASC";
        } elseif ($range === '30days') {
            $sql = "SELECT DATE(measurement_datetime) as date_label, AVG(temperature) as avg_temp 
                    FROM weather_data 
                    WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    GROUP BY date_label 
                    ORDER BY date_label ASC";
        } elseif ($range === 'month') {
            // Ten miesiąc
            $sql = "SELECT DATE(measurement_datetime) as date_label, AVG(temperature) as avg_temp 
                    FROM weather_data 
                    WHERE measurement_datetime >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND measurement_datetime < DATE_FORMAT(CURDATE(), '%Y-%m-01') + INTERVAL 1 MONTH
                    GROUP BY date_label 
                    ORDER BY date_label ASC";
        } elseif ($range === 'year' || $range === 'current_year') {
            // Ten rok (grupowanie po miesiącach dla czytelności wykresu rocznego, lub dniach - decyzja: MIESIĄCE dla 'Year')
            // EDIT: User want "Daily" detail usually, but for "This Year" average, monthly is clearer. 
            // Let's stick to Daily for consistency with tabs like "2024" if they want detail? 
            // Actually, for "Average Temp" over a FULL YEAR, monthly bars/line is standard. Daily is too noisy for "Average".
            // Let's do Monthly averages for year views.
            $sql = "SELECT DATE_FORMAT(measurement_datetime, '%Y-%m') as date_label, AVG(temperature) as avg_temp 
                    FROM weather_data 
                    WHERE measurement_datetime >= MAKEDATE(YEAR(CURDATE()), 1) AND measurement_datetime < MAKEDATE(YEAR(CURDATE()) + 1, 1)
                    GROUP BY date_label 
                    ORDER BY date_label ASC";
        } elseif (is_numeric($range)) {
             // Konkretny rok (np. 2025, 2024) - też miesięcznie
             $year = intval($range);
             $sql = "SELECT DATE_FORMAT(measurement_datetime, '%Y-%m') as date_label, AVG(temperature) as avg_temp 
                    FROM weather_data 
                    WHERE measurement_datetime >= :start AND measurement_datetime < :end
                    GROUP BY date_label 
                    ORDER BY date_label ASC";
             $params = ['start' => "$year-01-01", 'end' => ($year + 1) . "-01-01"];
        } else {
            // Default 30 days
             $sql = "SELECT DATE(measurement_datetime) as date_label, AVG(temperature) as avg_temp 
                    FROM weather_data 
                    WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    GROUP BY date_label 
                    ORDER BY date_label ASC";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatowanie etykiet (opcjonalne, można robić w JS, ale tu też OK)
        // Zostawiamy surowe dane, JS sformatuje
        echo json_encode($data);
    }

    // 5. TABELA MIESIĘCZNA
    elseif ($action === 'monthly_stats') {
        $stmt = $pdo->query("SELECT DATE_FORMAT(measurement_datetime, '%Y-%m') as month_id, MAX(temperature) as max_temp, MIN(temperature) as min_temp FROM weather_data WHERE measurement_datetime > DATE_SUB(NOW(), INTERVAL 14 MONTH) GROUP BY month_id ORDER BY month_id DESC LIMIT 14");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 5. AKTUALNE WARUNKI
    elseif ($action === 'current') {
        // Pobierz najnowszy pomiar
        $stmt = $pdo->query("SELECT * FROM weather_data ORDER BY measurement_datetime DESC LIMIT 1");
        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($currentData) {
            // Logika porównania z rokiem ubiegłym
            $currentDate = new DateTime($currentData['measurement_datetime']);
            $pastDateTarget = (clone $currentDate)->modify('-1 year');
            
            // Szukamy pomiaru sprzed roku (tolerancja +/- 1 godzina)
            $pastStart = (clone $pastDateTarget)->modify('-1 hour')->format('Y-m-d H:i:s');
            $pastEnd   = (clone $pastDateTarget)->modify('+1 hour')->format('Y-m-d H:i:s');

            $stmtPast = $pdo->prepare("
                SELECT temperature, measurement_datetime 
                FROM weather_data 
                WHERE measurement_datetime BETWEEN :start AND :end 
                ORDER BY ABS(TIMESTAMPDIFF(SECOND, measurement_datetime, :target)) ASC 
                LIMIT 1
            ");
            
            $stmtPast->execute([
                'start' => $pastStart,
                'end' => $pastEnd,
                'target' => $pastDateTarget->format('Y-m-d H:i:s')
            ]);
            
            $pastData = $stmtPast->fetch(PDO::FETCH_ASSOC);

            if ($pastData) {
                $diff = floatval($currentData['temperature']) - floatval($pastData['temperature']);
                $currentData['historical_comparison'] = [
                    'available' => true,
                    'diff' => $diff,
                    'past_temp' => floatval($pastData['temperature']),
                    'past_date' => $pastData['measurement_datetime']
                ];
            } else {
                $currentData['historical_comparison'] = ['available' => false];
            }
        }

        echo json_encode($currentData);
    }

} catch (PDOException $e) {
    // Szczegóły tylko do logu serwera — nie ujawniamy ich klientowi
    error_log('api.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Błąd bazy danych']);
}
?>
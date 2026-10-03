# 🌤️ Weather Station - Gliwice

A robust web application designed to monitor, archive, and visualize weather data for the city of Gliwice. The system automatically fetches data from OpenWeatherMap, stores it in a MySQL database, optimizes storage via automated JSON archiving, and presents detailed statistics through interactive charts.

## 🚀 Key Features

* **Real-time Conditions:** Live monitoring of temperature, humidity, pressure, and last update time (auto-refreshed every 5 minutes).
* **Year-over-Year Comparison:** Shows how the current temperature differs from the same hour one year ago.
* **Daily Charts:** Detailed temperature and pressure trends for any selected date, with previous/next day navigation (powered by **Chart.js**).
* **Calendar Year Records:**
    * Interactive **Tabbed Interface** for viewing records by year (current and two previous years; tabs without data are hidden).
    * Tracks Max/Min temperature and pressure for each calendar year.
    * Localized date formatting (e.g., "18 stycznia").
* **Monthly Table:** Max/Min temperature for each of the last 14 months.
* **Trend Analysis:** Yearly trend chart visualizing daily Min/Max temperatures over the last 12 months.
* **Average Temperatures:** Average temperature chart for today (hourly), last 7/30 days, current month, and current/previous years (monthly).
* **DST Countdown:** Countdown to the next EU daylight saving time change (last Sunday of March/October, 01:00 UTC).
* **Automated Archiving:** Bash script that packs raw JSON files older than 24h into a tar archive.
* **Responsive Design:** Dark-themed interface optimized for both mobile and desktop devices (built with **Bootstrap 5**).

## 🛠️ Tech Stack

* **Backend:** PHP 7.4+ (PDO, vanilla PHP without frameworks)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, Bootstrap 5, Chart.js, Vanilla JS
* **Automation:** Bash (archiving scripts), CRON (scheduling)

## 📁 Project Structure

| File | Purpose |
|---|---|
| `index.html` | Single-page frontend (HTML, CSS and JS in one file) |
| `api.php` | JSON API used by the frontend |
| `update_data.php` | Fetches current weather and stores it (CLI/cron only) |
| `config.php` | Loads `.env` and returns the configuration array |
| `arch.sh` | Archives raw JSON files older than 24h into `archive/old/old.tar` |
| `.htaccess` | Blocks web access to `update_data.php`, `config.php` and `arch.sh` |
| `archive/` | Raw OpenWeatherMap responses, one file per hour (`Gliwice-YYYY-MM-DD-HH.json`) |

## ⚙️ Installation & Configuration

### 1. Prerequisites
* Web Server (Apache/Nginx/LiteSpeed) with PHP support.
* MySQL / MariaDB Database.
* Access to Cron (Task Scheduler).
* OpenWeatherMap API Key.

### 2. Database Setup
Create the `weather_data` table using the following SQL schema. The indexes on `measurement_datetime`, `temperature` and `(measurement_datetime, temperature)` are used by the API queries.

```sql
CREATE TABLE weather_data (
    id INT NOT NULL AUTO_INCREMENT,
    location VARCHAR(100) NOT NULL DEFAULT 'Gliwice',
    measurement_datetime DATETIME NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    pressure DECIMAL(7,2) NOT NULL,
    humidity INT NOT NULL,
    wind_speed DECIMAL(5,2) NOT NULL,
    wind_direction INT DEFAULT NULL,
    rainfall DECIMAL(6,2) DEFAULT 0.00,
    snowfall DECIMAL(6,2) DEFAULT 0.00,
    visibility INT DEFAULT NULL,
    weather_main VARCHAR(100) DEFAULT NULL,
    weather_description VARCHAR(255) DEFAULT NULL,
    weather_icon VARCHAR(10) DEFAULT NULL,
    cloudiness INT DEFAULT NULL,
    feels_like DECIMAL(5,2) DEFAULT NULL,
    sea_level_pressure DECIMAL(7,2) DEFAULT NULL,
    ground_level_pressure DECIMAL(7,2) DEFAULT NULL,
    raw_json LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_measurement (location, measurement_datetime),
    KEY idx_datetime (measurement_datetime),
    KEY idx_temperature (temperature),
    KEY idx_datetime_temp (measurement_datetime, temperature)
);
```

### 3. Environment Configuration
Create a `.env` file in the project root (it is listed in `.gitignore`):

```ini
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_user
DB_PASS=your_password
DB_CHARSET=utf8mb4

WEATHER_API_KEY=your_openweathermap_key
WEATHER_LOCATION=Gliwice
WEATHER_API_URL=http://api.openweathermap.org/data/2.5/weather
```

Make sure `.env` is not served by the web server (the hosting setup returns 403 for dotfiles; otherwise add a deny rule to `.htaccess`).

### 4. Cron Jobs
Fetch data at the start of every hour and archive old JSON files once an hour. Adjust paths to your installation and set the directories in `BASE_DIRS` inside `arch.sh`.

```cron
0 * * * * php /path/to/project/update_data.php >/dev/null 2>&1
59 * * * * /path/to/project/arch.sh >/dev/null 2>&1
```

`update_data.php` refuses to run outside the CLI, so it cannot be triggered through the browser.

## 🔌 API Endpoints

All endpoints are served by `api.php?action=...` and return JSON. On a database error the API responds with HTTP 500 and `{"error": "Błąd bazy danych"}` (details go to the server error log).

| Action | Parameters | Returns |
|---|---|---|
| `current` | – | Latest measurement plus `historical_comparison` with the same hour one year ago |
| `chart_data` | `date` (`YYYY-MM-DD`, default today) | All measurements for the given day |
| `year_stats` | `year` (default current) | Max/Min temperature and pressure for the year, with the date of their first occurrence |
| `yearly_trend` | – | Daily Max/Min temperature for the last 12 months |
| `monthly_stats` | – | Monthly Max/Min temperature for the last 14 months |
| `avg_stats` | `range`: `today`, `7days`, `30days`, `month`, `year` or a four-digit year | Average temperature per hour (`today`), day, or month (`year` and specific years) |

## ⚠️ Known Limitations

* Timestamps are stored in server local time (Europe/Warsaw). On the switch to winter time the hour 02:00 occurs twice, so the database keeps only one of the two measurements; both raw JSON files are kept in `archive/` (the second one with a `-CET` suffix).

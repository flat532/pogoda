# 🌤️ Weather Station - Gliwice

A robust web application designed to monitor, archive, and visualize weather data for the city of Gliwice. The system automatically fetches data from OpenWeatherMap, stores it in a MySQL database, optimizes storage via automated JSON archiving, and presents detailed statistics through interactive charts.

## 🚀 Key Features

* **Real-time Conditions:** Live monitoring of temperature, humidity, pressure, and last update time.
* **Daily Charts:** Detailed temperature and pressure trends for any selected date (powered by **Chart.js**).
* **History & Statistics:**
    * Annual records (Min/Max temperature and pressure).
    * Monthly averages and extremes.
    * Yearly trend analysis.
* **Automated Archiving:** Bash scripts to compress old JSON files, ensuring efficient storage management.
* **Responsive Design:** Fully optimized interface for both mobile and desktop devices (built with **Bootstrap 5**).

## 🛠️ Tech Stack

* **Backend:** PHP (PDO, vanilla PHP without frameworks)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, Bootstrap 5, Chart.js, Vanilla JS
* **Automation:** Bash (archiving scripts), CRON (scheduling)

## ⚙️ Installation & Configuration

### 1. Prerequisites
* Web Server (Apache/Nginx) with PHP support.
* MySQL Database.
* Access to Cron (Task Scheduler).
* OpenWeatherMap API Key.

### 2. Database Setup
Create the `weather_data` table in your database using the following SQL schema:

```sql
CREATE TABLE weather_data (
    location VARCHAR(255),
    measurement_datetime DATETIME,
    temperature DECIMAL(5,2),
    pressure INT,
    humidity INT,
    wind_speed DECIMAL(5,2),
    wind_direction INT,
    rainfall DECIMAL(5,2),
    snowfall DECIMAL(5,2),
    visibility INT,
    weather_main VARCHAR(50),
    weather_description VARCHAR(100),
    weather_icon VARCHAR(10),
    cloudiness INT,
    feels_like DECIMAL(5,2),
    sea_level_pressure INT,
    ground_level_pressure INT,
    raw_json TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_measurement (location, measurement_datetime)
);

```
### This project uses a .env file to store sensitive credentials securely. Create a .env file in the root directory and populate it with your details:

```shell
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4

WEATHER_API_KEY=your_openweathermap_api_key
WEATHER_LOCATION=Gliwice
WEATHER_API_URL=[http://api.openweathermap.org/data/2.5/weather](http://api.openweathermap.org/data/2.5/weather)
```
### Security Note 
The .env file is excluded from version control via .gitignore to prevent credential leakage.

To enable automatic data fetching and archiving, add the following lines to your crontab (crontab -e):

```shell
# Fetch weather data every hour
0 * * * * php /path/to/project/update_data.php >/dev/null 2>&1

# Archive old JSON files (at the 59th minute of every hour)
59 * * * * /path/to/project/arch.sh >/dev/null 2>&1
```

### File Structure
index.html - Main user interface and dashboard.
api.php - REST API endpoint serving JSON data from the database.
update_data.php - Script for fetching API data and updating the database.
config.php - Configuration loader (handles .env parsing).
arch.sh - Bash script for archiving and compressing raw JSON data.
archive/ - Directory for storing raw JSON responses (auto-generated).

### Security
Sensitive data (passwords, API keys) are strictly decoupled from the source code using the .env file. Ensure that .env and the archive/ directory are not publicly accessible via the web server.
--------
© 2025 Weather Station Project
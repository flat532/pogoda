# 🌤️ Weather Station - Gliwice

A robust web application designed to monitor, archive, and visualize weather data for the city of Gliwice. The system automatically fetches data from OpenWeatherMap, stores it in a MySQL database, optimizes storage via automated JSON archiving, and presents detailed statistics through interactive charts.

## 🚀 Key Features

* **Real-time Conditions:** Live monitoring of temperature, humidity, pressure, and last update time.
* **Daily Charts:** Detailed temperature and pressure trends for any selected date (powered by **Chart.js**).
* **Calendar Year Records:** * Interactive **Tabbed Interface** for viewing records by specific years (Current, Previous, Past).
    * Tracks Max/Min temperature and pressure for each calendar year.
    * Localized date formatting (e.g., "18 stycznia").
* **Trend Analysis:** Yearly trend chart visualizing Min/Max temperatures over the last 12 months.
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
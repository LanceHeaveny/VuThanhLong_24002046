USE `wheather`;

CREATE TABLE IF NOT EXISTS `weather_forecast_day_queue` (
    `location_key` VARCHAR(100) NOT NULL,
    `queue_position` TINYINT UNSIGNED NOT NULL,
    `forecast_time` DATETIME NOT NULL,
    `temperature` DECIMAL(5,1) NOT NULL,
    `humidity` TINYINT UNSIGNED NOT NULL,
    `precipitation_probability` TINYINT UNSIGNED NULL,
    `weather_type` VARCHAR(30) NOT NULL,
    `snapshot_at` DATETIME NOT NULL,
    PRIMARY KEY (`location_key`, `queue_position`),
    KEY `idx_weather_forecast_day_snapshot` (`location_key`, `snapshot_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `weather_forecast_week_queue` (
    `location_key` VARCHAR(100) NOT NULL,
    `queue_position` TINYINT UNSIGNED NOT NULL,
    `forecast_date` DATE NOT NULL,
    `temperature_max` DECIMAL(5,1) NOT NULL,
    `temperature_min` DECIMAL(5,1) NOT NULL,
    `precipitation_probability` TINYINT UNSIGNED NULL,
    `weather_type` VARCHAR(30) NOT NULL,
    `snapshot_at` DATETIME NOT NULL,
    PRIMARY KEY (`location_key`, `queue_position`),
    KEY `idx_weather_forecast_week_snapshot` (`location_key`, `snapshot_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS `wheather`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `wheather`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_users_address` (`address`),
    UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `weather_locations` (
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `weather_locations` (`name`) VALUES
    ('An Giang'), ('Bắc Ninh'), ('Cà Mau'), ('Cần Thơ'), ('Cao Bằng'),
    ('Đà Nẵng'), ('Đắk Lắk'), ('Điện Biên'), ('Đồng Nai'), ('Đồng Tháp'),
    ('Gia Lai'), ('Hà Nội'), ('Hà Tĩnh'), ('Hải Phòng'), ('Hồ Chí Minh'),
    ('Huế'), ('Hưng Yên'), ('Khánh Hòa'), ('Lai Châu'), ('Lâm Đồng'),
    ('Lạng Sơn'), ('Lào Cai'), ('Nghệ An'), ('Ninh Bình'), ('Phú Thọ'),
    ('Quảng Ngãi'), ('Quảng Ninh'), ('Quảng Trị'), ('Sơn La'), ('Tây Ninh'),
    ('Thái Nguyên'), ('Thanh Hóa'), ('Tuyên Quang'), ('Vĩnh Long');

INSERT INTO `users` (`username`, `password`, `address`)
VALUES (
    'ADMIN',
    '$2y$10$q1.vuTD3ICHETCT27O.P1eEGM1zOcoMlx7Ls3QcJMMv65483m/OWi',
    'Hà Nội'
)
ON DUPLICATE KEY UPDATE
    `username` = VALUES(`username`);

CREATE TABLE IF NOT EXISTS `temperature` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `sensor_id` VARCHAR(100) NOT NULL,
    `value` FLOAT NOT NULL,
    `unit` VARCHAR(10) NOT NULL DEFAULT '°C',
    `recorded_at` TIMESTAMP NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_temperature_location_recorded_at` (`location`, `recorded_at`),
    CONSTRAINT `fk_temperature_user_address`
        FOREIGN KEY (`location`) REFERENCES `weather_locations` (`name`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `humidity` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `sensor_id` VARCHAR(100) NOT NULL,
    `value` FLOAT NOT NULL,
    `unit` VARCHAR(10) NOT NULL DEFAULT '%',
    `recorded_at` TIMESTAMP NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_humidity_location_recorded_at` (`location`, `recorded_at`),
    CONSTRAINT `fk_humidity_user_address`
        FOREIGN KEY (`location`) REFERENCES `weather_locations` (`name`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `weather` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `sensor_id` VARCHAR(100) NOT NULL,
    `weather_type` VARCHAR(30) NOT NULL,
    `recorded_at` TIMESTAMP NOT NULL,
    `location` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_weather_location_recorded_at` (`location`, `recorded_at`),
    CONSTRAINT `fk_weather_user_address`
        FOREIGN KEY (`location`) REFERENCES `weather_locations` (`name`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Legacy time-series table retained for compatibility with the earlier dashboard feed.
CREATE TABLE IF NOT EXISTS `sensor_readings` (
    `location` VARCHAR(255) NOT NULL,
    `bucket_date` DATE NOT NULL,
    `recorded_at` DATETIME(3) NOT NULL,
    `sensor_id` VARCHAR(100) NOT NULL,
    `temperature` DECIMAL(5,2) NOT NULL,
    `humidity` DECIMAL(5,2) NOT NULL,
    PRIMARY KEY (`location`, `bucket_date`, `recorded_at`, `sensor_id`),
    KEY `idx_sensor_readings_recent` (`location`, `recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rolling per-province queue: one minute slot per city, retain the newest 720 rows.
-- Run app/generate_weather_data.php once a minute with Windows Task Scheduler;
-- dashboard requests also catch up missing minute slots while the site is open.
-- Slots contain live weather snapshots; missed minutes reuse the last saved snapshot.
CREATE TABLE IF NOT EXISTS `weather_minute_queue` (
    `location` VARCHAR(100) NOT NULL,
    `recorded_at` DATETIME NOT NULL,
    `sensor_id` VARCHAR(100) NOT NULL,
    `temperature` DECIMAL(5,2) NOT NULL,
    `humidity` DECIMAL(5,2) NOT NULL,
    `temperature_amplitude` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `weather_type` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`location`, `recorded_at`),
    KEY `idx_weather_minute_queue_time` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One snapshot for each whole hour; the queue keeps only the newest 12 per city.
CREATE TABLE IF NOT EXISTS `weather_hourly_history` (
    `location` VARCHAR(100) NOT NULL,
    `recorded_hour` DATETIME NOT NULL,
    `temperature` DECIMAL(5,2) NOT NULL,
    `humidity` DECIMAL(5,2) NOT NULL,
    `temperature_amplitude` DECIMAL(5,2) NOT NULL,
    `weather_type` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`location`, `recorded_hour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wet` (
    `humidity_id` INT NOT NULL,
    `temperature_id` INT NOT NULL,
    `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`humidity_id`, `temperature_id`),
    KEY `idx_wet_temperature_id` (`temperature_id`),
    CONSTRAINT `fk_wet_humidity`
        FOREIGN KEY (`humidity_id`) REFERENCES `humidity` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_wet_temperature`
        FOREIGN KEY (`temperature_id`) REFERENCES `temperature` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `dry` (
    `humidity_id` INT NOT NULL,
    `temperature_id` INT NOT NULL,
    `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`humidity_id`, `temperature_id`),
    KEY `idx_dry_temperature_id` (`temperature_id`),
    CONSTRAINT `fk_dry_humidity`
        FOREIGN KEY (`humidity_id`) REFERENCES `humidity` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_dry_temperature`
        FOREIGN KEY (`temperature_id`) REFERENCES `temperature` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;
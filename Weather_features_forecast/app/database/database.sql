CREATE DATABASE IF NOT EXISTS `wheather`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `wheather`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`address`),
    UNIQUE KEY `uq_users_id` (`id`),
    UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB;

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
        FOREIGN KEY (`location`) REFERENCES `users` (`address`)
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
        FOREIGN KEY (`location`) REFERENCES `users` (`address`)
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
        FOREIGN KEY (`location`) REFERENCES `users` (`address`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
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
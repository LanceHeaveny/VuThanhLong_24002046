USE `wheather`;

-- One-time migration for databases created before user IDs became the primary key.
CREATE TABLE IF NOT EXISTS `weather_locations` (
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `weather_locations` (`name`) VALUES
    ('Tuyên Quang'), ('Cao Bằng'), ('Lai Châu'), ('Lào Cai'), ('Thái Nguyên'),
    ('Điện Biên'), ('Lạng Sơn'), ('Sơn La'), ('Phú Thọ'), ('Bắc Ninh'),
    ('Quảng Ninh'), ('Hà Nội'), ('Hải Phòng'), ('Hưng Yên'), ('Ninh Bình'),
    ('Thanh Hóa'), ('Nghệ An'), ('Hà Tĩnh'), ('Quảng Trị'), ('Huế'),
    ('Đà Nẵng'), ('Quảng Ngãi'), ('Gia Lai'), ('Đắk Lắk'), ('Khánh Hòa'),
    ('Lâm Đồng'), ('Đồng Nai'), ('Tây Ninh'), ('TP. Hồ Chí Minh'), ('Đồng Tháp'),
    ('Vĩnh Long'), ('Cần Thơ'), ('An Giang'), ('Cà Mau');

ALTER TABLE `temperature`
    DROP FOREIGN KEY `fk_temperature_user_address`;
ALTER TABLE `humidity`
    DROP FOREIGN KEY `fk_humidity_user_address`;
ALTER TABLE `weather`
    DROP FOREIGN KEY `fk_weather_user_address`;

ALTER TABLE `users`
    DROP PRIMARY KEY,
    DROP INDEX `uq_users_id`,
    ADD PRIMARY KEY (`id`),
    ADD KEY `idx_users_address` (`address`);

ALTER TABLE `temperature`
    ADD CONSTRAINT `fk_temperature_location`
        FOREIGN KEY (`location`) REFERENCES `weather_locations` (`name`)
        ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `humidity`
    ADD CONSTRAINT `fk_humidity_location`
        FOREIGN KEY (`location`) REFERENCES `weather_locations` (`name`)
        ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `weather`
    ADD CONSTRAINT `fk_weather_location`
        FOREIGN KEY (`location`) REFERENCES `weather_locations` (`name`)
        ON UPDATE CASCADE ON DELETE RESTRICT;

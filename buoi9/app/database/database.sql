-- 1. Tạo cơ sở dữ liệu (nếu chưa có) và chọn CSDL
CREATE DATABASE IF NOT EXISTS store_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE store_db;

-- 2. Xóa bảng nếu đã tồn tại
DROP TABLE IF EXISTS products;

-- 3. Tạo bảng products có thêm cột image
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price INT NOT NULL CHECK (price > 0),
    stock INT NOT NULL CHECK (stock >= 0),
    image VARCHAR(255) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Chèn dữ liệu mẫu (tổng 12 sản phẩm để phân trang 5 / 5 / 2)
INSERT INTO products (name, price, stock, image) VALUES
('Bút bi', 5000, 20, NULL),
('Vở ô ly', 12000, 15, NULL),
('Thước kẻ', 8000, 0, NULL),
('Bút chì 2B', 4000, 35, NULL),
('Tẩy trắng', 6000, 25, NULL),
('Gọt bút chì', 10000, 18, NULL),
('Compa học sinh', 22000, 12, NULL),
('Bìa hồ sơ A4', 7000, 40, NULL),
('Giấy note 3x3', 9000, 30, NULL),
('Bút highlight', 14000, 16, NULL),
('Kéo văn phòng', 25000, 10, NULL),
('Băng keo trong', 11000, 28, NULL);


-- 1. Tạo cơ sở dữ liệu (nếu chưa có) và chọn CSDL
CREATE DATABASE IF NOT EXISTS store_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE store_db;

-- 2. Xóa bảng nếu đã tồn tại
DROP TABLE IF EXISTS products;

-- 3. Tạo bảng products theo cấu trúc yêu cầu
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price INT NOT NULL CHECK (price > 0),
    stock INT NOT NULL CHECK (stock >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Chèn 3 dòng dữ liệu mẫu
INSERT INTO products (name, price, stock) VALUES
('Bút bi', 5000, 20),
('Vở ô ly', 12000, 15),
('Thước kẻ', 8000, 0);
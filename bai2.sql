-- tạo database
CREATE DATABASE movie;
-- tạo bảng
use movie;
CREATE TABLE movies(id INT AUTO_INCREMENT PRIMARY KEY,
                   tile varchar(100) NOT NULL,
                   price decimal(10,2) NOT NULL,
                   total_seats int NOT NULL,
                   available_seats int NOT NULL);
-- thêm 5 bộ phim
INSERT INTO movies (id, tile, price, total_seats, available_seats) VALUES ('1', 'Đằng sau hoa hồng là nước mắt', '200000', '150', '34'), ('2', "Chiếc bóng phía xa nơi chân trời", '150000','120','4'), ('3', "ước nguyện mưa sao băng", '150000', '99', '9'), ('4', "Lời hứa bồ công anh", '120000', '85', '12'), ('5', "Chúng ta là quá khứ hay tương lai", '70000', '150', '77');

-- hiển thị toàn bộ các bộ phim:
SELECT * FROM movies;
-- hiển thị bộ phim giá trên 100000
SELECT * FROM movies WHERE price > 100000;
-- hiển thị bộ phim còn hơn 50 ghế
SELECT * FROM movies WHERE available_seats > 50;
-- sắp xếp giảm  dần theo giá
SELECT * FROM movies ORDER by price DESC;
-- cập nhật giá 1 bộ phim
UPDATE movies
	SET available_seats = 2 WHERE tile like "Ước nguyện mưa sao băng";
-- xóa 1 bộ phim
DELETE FROM movies WHERE tile like "Chúng ta là quá khư hay tương lai";
-- hiển thị tên phim, giá vé, số lượng ghế, số lượng ghế trống, doanh thu
SELECT tile AS "Tên phim" , price AS "Giá vé", total_seats AS "Số lượng ghế", available_seats AS "Số ghế trống", (price*(total_seats - available_seats)) AS "Doanh thu" FROM movies; 
-- tính tổng doanh thu
SELECT "Tổng doanh thu: ", SUM(price * (total_seats - available_seats)) FROM movies;
-- tìm phim có số vé bán chạy nhất
SELECT * FROM movies WHERE (total_seats - available_seats) = (SELECT MAX(total_seats - available_seats));
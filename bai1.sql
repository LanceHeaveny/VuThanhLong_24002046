
-- tạo database
CREATE DATABASE shopping_cart;
-- tạo bảng
use shopping_cart;
CREATE TABLE  cart_items(id INT AUTO_INCREMENT PRIMARY KEY,
                   name varchar(100) NOT NULL,
                   price decimal(10,2) NOT NULL,
                   quantity int NOT NULL);
-- nhập 5 sản phẩm 
INSERT INTO cart_items(id, name, price, quantity) VALUES ('1', "Cua tuyết", '600000', '2'), ('2', "Thịt ba chỉ", '130000', '2'), ('3', "Cá chép", '60000', '1'), ('4', "Kem", '8000', '8'), ('5', "Trà", '20000', '3');

-- hiển thị sản phẩm:
SELECT * FROM cart_items;
-- hiển thị sản phẩm giá trên 100000
SELECT * FROM cart_items WHERE price > 100000;
-- hiển thị sản phẩm có hơn 5 số lượng
SELECT * FROM cart_items WHERE quantity > 5;
-- sắp xếp giảm  dần theo giá
SELECT * FROM cart_items ORDER by price DESC;
-- cập nhật giá 1 sản phẩm
UPDATE cart_items
	SET quantity = 1 WHERE name like "Cua tuyết";
-- xóa 1 sản phẩm 
DELETE FROM cart_items WHERE name like "Cá chép";
-- hiển thị tên sản phẩm, giá, số lượng, thành tiền
SELECT name AS "Tên sản phẩm" , price AS " Giá", quantity AS "Số lượng", (price*quantity) AS " Thành tiền" FROM cart_items; 
-- tính tổng tiền giỏ hàng
SELECT "Tổng tiền giỏ hàng: ", SUM(price * quantity) FROM cart_items;
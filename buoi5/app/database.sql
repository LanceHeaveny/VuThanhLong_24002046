
-- tạo database
CREATE DATABASE shopping_cart;
-- tạo bảng
use shopping_cart;
CREATE TABLE  cart_items(id INT AUTO_INCREMENT PRIMARY KEY,
                   name varchar(100) NOT NULL,
                   price decimal(10,2) NOT NULL,
                   quantity int NOT NULL);
-- nhập 5 sản phẩm 
INSERT INTO cart_items(id, name, price, quantity) VALUES ('1', "Cua tuyết", '600000', '2'), ('2', "Thịt ba chỉ", '130000', '2'), ('3', "Cá chép", '60000', '1'), ('4', "Kem", '8000', '8'), ('5', "Trà", '20000', '3'),('6',"Snack",'6000','10');


use shopping_cart;
CREATE TABLE users(id int AUTO_INCREMENT PRIMARY KEY,
                  username varchar(50) not null UNIQUE,
                  password varchar(255) not null);
INSERT INTO users (id,username, password) VALUES ('1','admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); 
-- Lưu ý: Chuỗi trên tương ứng với mật khẩu gốc là 'password' đã qua hàm password_hash()
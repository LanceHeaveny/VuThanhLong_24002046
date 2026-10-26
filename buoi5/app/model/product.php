<?php
    // Tạo đối tượng sản phẩm
    class Item {
        public int $id;
        public string $name;
        public float $price;
        public int $quantity;

        public function __construct(int $id, string $name, float $price, int $quantity){
            $this->id = $id;
            $this->name = $name;
            $this->price = $price;
            $this->quantity = $quantity;
        }
    }

    // Tạo đối tượng giỏ hàng / quản lý sản phẩm
    class ShoppingCart {
        public array $products = [];

        public function __construct(array $products = []){
            $this->products = $products;
        }

        public function getAllProducts(): array {
            return $this->products;  
        }

        // Đổi tên thành getIndexById để phản ánh đúng việc trả về vị trí (index) trong mảng
        public function getIndexById(int $id): int {
            foreach ($this->products as $index => $product) {
                if ($product->id == $id) {
                    return $index;  
                }
            }
            return -1;  
        }

        // Hàm kiểm tra thuộc tính hợp lệ (Trả về true nếu hợp lệ, false nếu sai)
        public function isValidation(string $name, float $price, int $quantity): bool {
            if (empty(trim($name)) || $price <= 0 || $quantity < 0) {
                return false;
            }
            return true;
        }

        // Hàm tìm vật phẩm theo id
        public function getProductById(int $id) {
            $index = $this->getIndexById($id);
            if ($index != -1){
                return $this->products[$index];
            }
            return null; 
        }

        // Hàm thêm vật phẩm vào xe hàng
        public function addProduct(string $name, float $price, int $quantity): bool {
            if (!$this->isValidation($name, $price, $quantity)) {
                return false;
            }

            // Tự động tạo ID tăng dần an toàn (tránh trùng lặp khi xóa sản phẩm)
            $newId = 1;
            if (!empty($this->products)) {
                $maxId = max(array_map(fn($p) => $p->id, $this->products));
                $newId = $maxId + 1;
            }

            $this->products[] = new Item($newId, $name, $price, $quantity);
            return true;
        }

        // Hàm xóa vật phẩm theo id
        public function deleteProduct(int $id): bool {
            $index = $this->getIndexById($id);
            if ($index != -1) {
                array_splice($this->products, $index, 1);
                return true;
            }
            return false;
        }

        // Hàm cập nhật thuộc tính vật phẩm
        public function updateProduct(int $id, string $name, float $price, int $quantity): bool {
            $index = $this->getIndexById($id);
            
            if ($index == -1) {
                return false; // Không tìm thấy sản phẩm
            }

            if (!$this->isValidation($name, $price, $quantity)) {
                return false; // Dữ liệu không hợp lệ
            }

            // Tiến hành cập nhật
            $this->products[$index]->name = $name;
            $this->products[$index]->price = $price;
            $this->products[$index]->quantity = $quantity;
            return true;
        }

        // Hàm trả về thành tiền tổng cộng
        public function totalPriceProduct(): float {
            $total = 0;
            foreach ($this->products as $product) {
                $total += $product->price * $product->quantity;
            }
            return $total;
        }     
    }
?>

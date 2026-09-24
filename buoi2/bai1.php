<?php

    class CartItem {
        public string $name;
        public float $price;
        public int $quantity;

        // hàm dựng:
        public function __construct($name, $price, $quantity) {
            $this->name = $name;
            $this->price = $price;
            $this->quantity = $quantity;
        }
        

        // hàm tính tiền:
        public function getTotal(){
            return $this->price * $this->quantity;
        }
    }

    class ShoppingCart{
        public array $items;
       
        // hàm dựng không đối số:
        public function __construct() {
            $this->items = [];
        }

        // hàm thêm sản phẩm:

        public function addItem(CartItem $item): void {
            // check sản phẩm có hợp lệ không:
            if ($item->price <= 0 || $item->quantity <= 0) {
                echo "Sản phẩm <b><u>$item->name</u></b> không hợp lệ (giá hoặc số lượng phải > 0)! <br>";
                return;
            } 

            // thêm sản phẩm hợp lệ:

            $this->items[] = $item;
            echo "Đã thêm <b>{$item->name}</b> vào giỏ hàng! <br>";
        }

        // hàm lấy sản phẩm đã có trong giỏ hàng:

        public function addExistItem(CartItem $item): void {
            foreach($this->items as $exisItem){
                if ($exisItem->name == $item->name){
                    $exisItem->quantity += $item->quantity;
                    echo "Đã lấy thêm <b>$item->quantity</b> <b>$item->name</b> vào giỏ hàng! <br>";
                }
            }
        }

        // hàm xóa sản phẩm (theo tên):
        public function removeItem($name){
            if (strlen($name) == 0 || empty($name) ){
                return;
            }
            if (count($this->items) == 0){
                echo "Giỏ hàng rỗng! <br>";
                return;
            }
            for ($i =0; $i < count($this->items); $i++){
                if($this->items[$i]->name == $name){
                    unset($this->items[$i]);
                    // tạo lại mảng do sau unset thì xóa dữ liệu nên $i tại đó có dữ liệu NULL chứ không xóa $i
                    $this->items = array_values($this->items);
                    echo "Đã bỏ món <b>$name</b> khỏi giỏ hàng! <br><br>";
                    return;
                }
            }
        }

        // hàm bỏ bớt sản phẩm (bỏ 1 phần số lượng):
        public function reduceItem(CartItem $item):void {
            for($i=0; $i< count($this->items); $i++){
                if ($this->items[$i]->name == $item->name){
                    if ($this->items[$i]->quantity < $item->quantity){
                        echo "không thể bỏ quá số lượng ".($this->items[$i]->quantity)." ".($this->items[$i]->name)." lấy vào nên bỏ hết phần đã lấy trước đó ra rồi nhé<br><br>";
                        $this->removeItem($item->name);
                        return;
                        }   
                    $this->items[$i]->quantity -= $item->quantity;
                    echo "Đã bỏ bớt đi <b>$item->quantity</b> <b>$item->name</b> khỏi giỏ hàng! <br>";
                    return;
                }
            }
        }

        //hàm tính tiền giỏ hàng:
        public function caculateTotal(){
            if (count($this->items) == 0){
                return 0;
            }
            $total = 0;
            foreach ($this->items as $item){
                $total += $item->getTotal();
            }
            return $total;
        }

        // hàm displayCart, hiển thị danh sách sản phẩm:
        public function displayCart(){
            if (count($this->items) == 0){
                echo "Giỏ hàng rỗng, không có sản phẩm! <br>";
                return;
            }
            // in danh sách
            foreach ($this->items as $item){
                echo "<b> Tên sản phẩm:</b> ".$item->name." | <b> Đơn giá:</b> ".$item->price." |<b> Số lượng: </b>".$item->quantity." |<b> Thành tiền: </b>".$item->getTotal();
                echo "<br>";
            }
            echo "<br>";
            //in ra tổng tiền giỏ hàng
            echo "<b>Tổng tiền: </b>".$this->caculateTotal()."<br><br>";
        }
       
    }

    //testcase

    function main() {
        echo "Bắt đầu shopping! <br><br>";
        $ShoppingCart = new ShoppingCart();
        echo "Đã lấy giỏ hàng<br><br>";
        
        // lấy sản phẩm hợp lệ:

        echo "Lấy ản phẩm <b><u>hợp lệ</u></b>:<br><br>";
        $ShoppingCart->addItem(new CartItem("Cua tuyết", 600000, 2));
        $ShoppingCart->addItem(new CartItem("Táo Mỹ", 75000, 2));
        $ShoppingCart->addItem(new CartItem("Rau củ", 30000, 1));
        $ShoppingCart->addItem(new CartItem("Kẹo dẻo", 51000, 2));
        $ShoppingCart->addItem(new CartItem("Kem", 8000, 4));
        $ShoppingCart->addItem(new CartItem("Thịt ba chỉ",120000 , 1));
        $ShoppingCart->addItem(new CartItem("Cá chép", 60000, 1));
        $ShoppingCart->addItem(new CartItem("Thịt bò", 250000, 1));
        
        // lấy sản phẩm không hợp lệ:

        echo "<br>lấy sản phẩm <b><u>không hợp lệ</u></b>: <br><br>";
        echo "thêm món <b>Trứng khủng long</b> với giá -1000000 và mua 3 quả<br>";
        $ShoppingCart->addItem(new CartItem("Trứng khủng long", -1000000, 3));
        echo "thêm món <b>Cá ngừ vây vàng</b> với giá 800000 và mua -2kg<br>";
        $ShoppingCart->addItem(new CartItem("Cá ngừ vây vàng", 800000, -2));

        echo "<br> Thống kê xe hàng: <br><br>";
        $ShoppingCart->displayCart();

        // lấy sản phẩm có rồi:

        echo "<br> lấy sản phẩm <b><u>đã có trên giỏ hàng</u></b>: <br><br>";
        echo "lấy thêm <b>4 que kem</b><br>";
        $ShoppingCart->addExistItem(new CartItem("Kem", 8000, 4));
        echo "<br> Thống kê xe hàng: <br><br>";
        $ShoppingCart->displayCart();
        
        // bỏ sản phẩm: 

        echo "Bỏ món hàng <b>Cá chép</b> <br><br> ";
        $ShoppingCart->removeItem("Cá chép");
        echo "Thống kê xe hàng: <br><br>";
        $ShoppingCart->displayCart();

        // bỏ bớt sản phẩm:
        echo "<br>Bỏ bớt 1kg món hàng <b>cua tuyết</b> <br> ";
        $ShoppingCart->reduceItem(new CartItem("Cua tuyết", 600000, 1));
        echo "<br>Bỏ bớt <b>16 que kem</b> <br> ";
        $ShoppingCart->reduceItem(new CartItem("Kem", 8000, 16));
        echo "Thống kê xe hàng: <br><br>";
        $ShoppingCart->displayCart();
    }


    main();
?>

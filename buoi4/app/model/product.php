<?php
    class Item{
        public int $id;
        public string $name;
        public float $price;
        public int $quantity;
        function __construct(int $id, string $name, float $price, int $quantity){
            $this->id = $id;
            $this->name = $name;
            $this->price = $price;
            $this->quantity = $quantity;
        }
    }
    class ShoppingCart{
        public array $products = [];
        function __construct(array $products = []){
            $this->products = $products;
        }
        public function getAllProducts(): array 
        {
            return $this->products;  
        }
        public function getId(int $id){
            if ($id < 0 || count($this->products) == 0) return -1;
            else {
                for ($i =0; $i < count($this->products); $i++){
                    if ($this->products[$i]->id == $id){
                    return $i;  
                    }
                }
            }
            return -1;  
        }
        public function isValidation(string $name, float $price, int $quantity){
            if (empty(trim($name)) || $price <= 0 || $quantity <= 0) {
                return -1;
            }
            return;
        }
        public function getProductById(int $id){
            $check = $this->getId($id);
            if ($check != -1){
            for ($i =0; $i < count($this->products); $i++){
                if ($this->products[$i]->id == $check){
                    return $this->products[$i];
                    }
                }
            }
            return -1 ;
        }
        public function addProduct(string $name, float $price, int $quantity){
            $check = $this->isValidation($name, $price, $quantity);
            if ($check != -1){
                for ($i = 0; $i < count($this->products); $i ++){
                    if ($this->products[$i]->name == $name && $this->products[$i]->price == $price && $this->products[$i]->quantity == $quantity){
                        $this->products[$i]->quantity += $quantity;
                    }
                    else{
                        $this->products[] = new Item(count($this->products)+1, $name, $price, $quantity);
                        break;
                    }
                    return;
                }
            }
            return;
        }
        public function deleteProduct(int $id){
            $check = $this->getId($id);
            if ($check != -1){
                for ($i = 0; $i < count($this->products); $i++){
                    if ($this->products[$i]->id == $check){
                        array_splice($this->products, $check, 1);
                    }
                    return;
                }
            }
            return;
        }
        public function updateProduct(int $id, string $name, float $price, int $quantity){
            $idCheck = $this->getId($id);
            $validCheck = $this->isValidation($name, $price, $quantity);
            if ($idCheck != -1){
                if ($validCheck != -1){
                    for ($i =0; $i < count($this->products); $i++){
                        if ($this->products[$i]->id == $idCheck){
                            $this->products[$i]->name = $name;
                            $this->products[$i]->price = $price;
                            $this->products[$i]->quantity = $quantity;
                        }
                    }
                }
            }
            return;
        }
        public function totalPriceProduct():float {
            $total = 0;
            for ($i = 0; $i < count($this->products); $i++ ){
                $total += $this->products[$i]->price * $this->products[$i]->quantity;
            }
            return $total;
        }    
    }
?>
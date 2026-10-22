<?php


// 1. Hàm tính điểm trung bình
function calculateTotal($products) {
    if (empty($products)) {
        return 0;
    }
    
    $total = 0;
    foreach ($products as $p) {
        $total += $p["price"];
    }
    
    return $total;
}

// 2. Hàm trả về xếp loại theo điểm
function findMax($products){
    if(empty($products)){
        return 0;
    }
    $max = $products[0]["price"];
    foreach($products as $p){
        if($p["price"] > $max){
            $max = $p["price"];
        }
    }
    return $max;
}
// 3. Hàm hiển thị thông tin sinh viên 
function displayProduct($products) {
    echo "tên sản phẩm: " . $products["name"] .  
         " | Giá sản phẩm: " . $products["price"]."<br>";
}

// main
$products = [
    [
        "name" => "Kem",
        "price" => 8500
    ],
    [
        "name" => "Thịt ba chỉ",
        "price" => 120000
    ],
    [
        "name" => "Cá chép",
        "price" => 65000
    ],
    [
        "name" => "Việt Quất",
        "price" => 195000
    ],
    [
        "name" => "Cherry",
        "price" => 450000
    ]
];

echo " DANH SÁCH SẢN PHẨM <br>";

// Hiển thị từng sinh viên
foreach ($products as $p) {
    displayProduct($p);
}

// Tính và hiển thị tổng giá tiền
$total = calculateTotal($products);
echo "<br>";
echo "tổng giá tiền:  " . number_format($total, 2) . "<br>";
$max = findMax($products);
echo "Giá tiền cao nhất: " . number_format($max,2) . "<br>";

?>
<?php
require_once __DIR__ . '/../common/dbConnect.php';

// 1. Lấy tất cả sản phẩm từ database
function getAllProducts() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 2. Lấy sản phẩm theo ID
function getProductById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 3. Thêm sản phẩm mới
function addProduct($name, $price, $quantity) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (name, price, quantity) VALUES (:name, :price, :quantity)");
    return $stmt->execute([
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity
    ]);
}

// 4. Cập nhật sản phẩm
function updateProduct($id, $name, $price, $quantity) {
    global $conn;
    $stmt = $conn->prepare("UPDATE products SET name = :name, price = :price, quantity = :quantity WHERE id = :id");
    return $stmt->execute([
        'id' => $id,
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity
    ]);
}

// 5. Xóa sản phẩm theo ID
function deleteProduct($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    return $stmt->execute(['id' => $id]);
}

// Hàm hỗ trợ kiểm tra dữ liệu đầu vào (đáp ứng yêu cầu các bài trước)
function isValidation($name, $price, $quantity) {
    if (empty(trim($name)) || $price <= 0 || $quantity < 0) {
        return false;
    }
    return true;
}
?>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shopping_cart";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Đã kết nối thành công, không echo ra màn hình
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa dựa vào session
if (isset($_SESSION['user_id'])) {
    // Nếu đã đăng nhập, chuyển đến trang danh sách sản phẩm / mua sắm
    header("Location: product_list.php");
    exit();
} else {
    // Nếu chưa đăng nhập, chuyển về trang đăng nhập mặc định
    header("Location: login.php");
    exit();
}
?>
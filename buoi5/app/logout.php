<?php
require_once 'common/dbConnect.php';
require_once 'model/user.php';

// Gọi hàm logout trong model User đã xây dựng
$userObj = new User($conn);
$userObj->logout();

// Chuyển hướng người dùng về trang login.php sau khi đăng xuất
header("Location: login.php");
exit();
?>
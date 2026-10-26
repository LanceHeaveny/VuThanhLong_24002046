<?php
session_start();
require_once 'common/dbConnect.php';
require_once 'model/user.php';

$errors = [];
$username = '';

// Hiển thị thông báo nếu vừa đăng ký thành công
$registerSuccess = '';
if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $registerSuccess = "Đăng ký thành công! Vui lòng đăng nhập.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $passwd = $_POST['passwd'] ?? '';

    $userObj = new User($conn);
    $user = $userObj->login($username, $passwd);

    if ($user !== false) {
        // Đăng nhập thành công, lưu thông tin vào Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Chuyển hướng sang file mua sắm / quản lý sản phẩm
        header("Location: product_list.php");
        exit();
    } else {
        $errors[] = "Tên đăng nhập hoặc mật khẩu không chính xác.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
</head>
<body>
    <h3>Đăng nhập</h3>

    <?php if (!empty($registerSuccess)): ?>
        <div style="color: green;"><?php echo htmlspecialchars($registerSuccess); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div>
            <label for="username">Tên đăng nhập:</label><br>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
        </div>
        <br>
        <div>
            <label for="passwd">Mật khẩu:</label><br>
            <input type="password" id="passwd" name="passwd">
        </div>
        <br>
        <div>
            <button type="submit">Đăng nhập</button>
            <!-- Liên kết chuyển sang trang đăng ký nếu chưa có tài khoản -->
            <a href="register.php" style="margin-left: 10px;">Chưa có tài khoản? Đăng ký ngay</a>
        </div>
    </form>

<?php 
// Nhúng footer vào cuối trang
include 'view/footer.php'; 
?>

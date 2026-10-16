<?php
require_once 'common/dbConnect.php'; // File kết nối PDO của bạn
require_once 'model/user.php';

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Khởi tạo đối tượng User với kết nối $conn
    $userObj = new User($conn);

    // Kiểm tra thông tin cơ bản
    if ($userObj->checkinfo($username, $password) === -1) {
        $errors[] = "Tên đăng nhập và mật khẩu không được để trống.";
    } else {
        // Gọi hàm register trong model
        $result = $userObj->register($username, $password);
        
        if ($result === true) {
            // Đăng ký thành công, chuyển hướng sang trang đăng nhập
            header("Location: login.php?registered=success");
            exit();
        } else {
            // Nhận thông báo lỗi từ model (ví dụ: trùng username)
            $errors[] = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
</head>
<body>
    <h3>Đăng ký tài khoản</h3>

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
            <label for="password">Mật khẩu:</label><br>
            <input type="password" id="password" name="password">
        </div>
        <br>
        <div>
            <button type="submit">Đăng ký</button>
            <a href="login.php">Đã có tài khoản? Đăng nhập</a>
        </div>
    </form>
</body>
</html>
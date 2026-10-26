<?php
session_start();
require_once 'common/dbConnect.php';
require_once 'model/product.php';

// Kiểm soát truy cập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Sử dụng hàm getProductById từ model
$product = getProductById($id);

// Nếu sản phẩm không tồn tại
if (!$product) {
    include 'view/header.php';
    echo "<h3>Sản phẩm không tồn tại!</h3>";
    echo "<p><a href='product_list.php'>Quay lại danh sách</a></p>";
    include 'view/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($confirm_password)) {
        $errors[] = "Vui lòng nhập mật khẩu xác nhận để xóa.";
    } else {
        $stmtUser = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmtUser->execute(['username' => $_SESSION['username']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($confirm_password, $user['password'])) {
            $errors[] = "Mật khẩu xác nhận không chính xác.";
        }
    }

    // Nếu hợp lệ, gọi hàm deleteProduct từ model
    if (empty($errors)) {
        deleteProduct($id);
        header("Location: product_list.php");
        exit();
    }
}

include 'view/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; background: #f4f4f4; padding: 10px; margin-bottom: 20px;">
    <div>
        <span>Xin chào, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</span>
    </div>
    <div>
        <a href="logout.php" style="background: red; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px;" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');">Đăng xuất</a>
    </div>
</div>

<h3>Xác nhận xóa sản phẩm</h3>
<p>Bạn có chắc chắn muốn xóa sản phẩm <strong><?= htmlspecialchars($product['name']) ?></strong> (ID: <?= $product['id'] ?>) không?</p>

<?php if (!empty($errors)): ?>
    <div style="color: red; margin-bottom: 15px;">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="product_delete.php?id=<?= $id ?>" method="POST">
    <div style="margin-bottom: 15px;">
        <label>Nhập mật khẩu xác nhận:</label><br>
        <input type="password" name="confirm_password" required>
    </div>
    <button type="submit" style="background: #dc3545; color: white;">Đồng ý xóa</button>
    <a href="product_list.php">Hủy</a>
</form>

<?php include 'view/footer.php'; ?>

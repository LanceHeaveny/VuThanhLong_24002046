<?php
session_start();
require_once 'common/dbConnect.php';
require_once 'model/product.php';

// 1. Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$errors = [];

// Lấy id từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Tìm sản phẩm trong Database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu sản phẩm không tồn tại, hiển thị thông báo
if (!$product) {
    include 'view/header.php';
    echo "<h3>Sản phẩm không tồn tại!</h3>";
    echo "<p><a href='product_list.php'>Quay lại danh sách</a></p>";
    include 'view/footer.php';
    exit();
}

// Khi người dùng bấm nút xác nhận xóa (Gửi POST kèm mật khẩu)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($confirm_password)) {
        $errors[] = "Vui lòng nhập mật khẩu xác nhận để xóa sản phẩm.";
    } else {
        // Lấy thông tin user đang đăng nhập để kiểm tra mật khẩu
        $stmtUser = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmtUser->execute(['username' => $_SESSION['username']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errors[] = "Không tìm thấy thông tin tài khoản.";
        } else {
            // Kiểm tra mật khẩu (hỗ trợ cả mật khẩu băm password_hash hoặc chuỗi thông thường)
            $isPasswordCorrect = false;
            if (password_get_info($user['password'])['algo'] != 0) {
                $isPasswordCorrect = password_verify($confirm_password, $user['password']);
            } else {
                $isPasswordCorrect = ($user['password'] === $confirm_password);
            }

            if (!$isPasswordCorrect) {
                $errors[] = "Mật khẩu xác nhận không chính xác.";
            }
        }
    }

    // Nếu mật khẩu đúng và không có lỗi, tiến hành xóa
    if (empty($errors)) {
        $stmtDelete = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmtDelete->execute(['id' => $id]);

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
        <label>Nhập mật khẩu tài khoản (<strong><?= htmlspecialchars($_SESSION['username']) ?></strong>) để xác nhận xóa:</label><br>
        <input type="password" name="confirm_password" required style="padding: 5px; width: 250px;">
    </div>
    <button type="submit" style="background: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 3px; cursor: pointer;">Đồng ý xóa</button>
    <a href="product_list.php" style="margin-left: 10px;">Hủy</a>
</form>

<?php include 'view/footer.php'; ?>

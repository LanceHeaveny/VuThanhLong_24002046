<?php
session_start();
require_once 'common/dbConnect.php';
require_once 'model/product.php';

// 1. Kiểm tra đăng nhập (Khắc phục lỗi không kiểm tra đăng nhập)
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$name = '';
$price = '';
$quantity = '';
$confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $confirm_password = $_POST['confirm_password'] ?? '';

    $cart = new ShoppingCart();
    
    // Kiểm tra tính hợp lệ của dữ liệu sản phẩm
    if (!$cart->isValidation($name, $price, $quantity)) {
        $errors[] = "Dữ liệu không hợp lệ (Tên không được rỗng, Giá > 0, Số lượng >= 0).";
    }

    // 2. Xác nhận mật khẩu của user đang đăng nhập
    if (empty($confirm_password)) {
        $errors[] = "Vui lòng nhập mật khẩu xác nhận.";
    } else {
        // Truy vấn lấy thông tin user từ database (giả sử bảng chứa user tên là 'users', cột là 'username' và 'password')
        $stmtUser = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmtUser->execute(['username' => $_SESSION['username']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errors[] = "Không tìm thấy thông tin tài khoản đăng nhập.";
        } else {
            // Kiểm tra mật khẩu (nếu bạn mã hóa password bằng password_hash, dùng password_verify. 
            // Nếu lưu dạng chuỗi thông thường thì đổi thành: $user['password'] !== $confirm_password)
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

    // Nếu không có lỗi, tiến hành thêm sản phẩm vào Database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, quantity) VALUES (:name, :price, :quantity)");
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity
        ]);

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

<h3>Thêm sản phẩm mới</h3>

<?php if (!empty($errors)): ?>
    <div style="color: red;">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="product_add.php" method="POST">
    <div>
        <label>Tên sản phẩm:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
    </div><br>
    <div>
        <label>Giá:</label><br>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars((string)$price) ?>">
    </div><br>
    <div>
        <label>Số lượng:</label><br>
        <input type="number" name="quantity" value="<?= htmlspecialchars((string)$quantity) ?>">
    </div><br>
    <div>
        <label>Xác nhận mật khẩu (của tài khoản hiện tại):</label><br>
        <input type="password" name="confirm_password" required>
    </div><br>
    <button type="submit">Thêm mới</button>
    <a href="product_list.php">Hủy</a>
</form>

<?php include 'view/footer.php'; ?>

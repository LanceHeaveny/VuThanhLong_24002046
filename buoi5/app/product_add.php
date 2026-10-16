<?php
require_once 'common/dbConnect.php';
require_once 'model/product.php';

$errors = [];
$name = '';
$price = '';
$quantity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);

    $cart = new ShoppingCart();
    
    // Sử dụng hàm isValidation() của ShoppingCart
    if ($cart->isValidation($name, $price, $quantity) === -1) {
        $errors[] = "Dữ liệu không hợp lệ (Tên không được rỗng, Giá > 0, Số lượng > 0).";
    }

    if (empty($errors)) {
        // Lưu sản phẩm vào Database MySQL
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
    <button type="submit">Thêm mới</button>
    <a href="product_list.php">Hủy</a>
</form>

<?php include 'view/footer.php'; ?>
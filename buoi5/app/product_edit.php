<?php
require_once 'common/dbConnect.php';
require_once 'model/product.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin sản phẩm từ DB
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$productData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$productData) {
    echo "Sản phẩm không tồn tại!";
    exit();
}

$errors = [];
$name = $productData['name'];
$price = $productData['price'];
$quantity = $productData['quantity'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);

    $cart = new ShoppingCart();
    if ($cart->isValidation($name, $price, $quantity) === -1) {
        $errors[] = "Dữ liệu không hợp lệ (Tên không rỗng, Giá > 0, Số lượng > 0).";
    }

    if (empty($errors)) {
        // Cập nhật sản phẩm vào DB
        $stmt = $conn->prepare("UPDATE products SET name = :name, price = :price, quantity = :quantity WHERE id = :id");
        $stmt->execute([
            'id' => $id,
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

<h3>Sửa sản phẩm</h3>

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
        <label for="name">Tên sản phẩm:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
    </div>
    <br>
    <div>
        <label for="price">Giá:</label><br>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>">
    </div>
    <br>
    <div>
        <label for="quantity">Số lượng:</label><br>
        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>">
    </div>
    <br>
    <div>
        <button type="submit">Cập nhật</button>
        <a href="product_list.php">Quay lại</a>
    </div>
</form>

<?php
include 'view/footer.php'; // Hoặc file footer nếu có
?>
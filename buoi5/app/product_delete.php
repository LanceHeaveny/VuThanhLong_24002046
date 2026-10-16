<?php
require_once 'common/dbConnect.php';
require_once 'model/product.php';

// Lấy id từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Tìm sản phẩm trong Database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu sản phẩm không tồn tại, hiển thị thông báo theo yêu cầu đề bài
if (!$product) {
    include 'view/header.php';
    echo "<h3>Sản phẩm không tồn tại!</h3>";
    echo "<p><a href='product_list.php'>Quay lại danh sách</a></p>";
    include 'view/footer.php';
    exit();
}

// Khi người dùng bấm nút xác nhận xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: product_list.php");
    exit();
}

include 'view/header.php';
?>

<h3>Xác nhận xóa sản phẩm</h3>
<p>Bạn có chắc chắn muốn xóa sản phẩm <strong><?= htmlspecialchars($product['name']) ?></strong> (ID: <?= $product['id'] ?>) không?</p>

<form action="product_delete.php?id=<?= $id ?>" method="POST">
    <button type="submit">Đồng ý xóa</button>
    <a href="product_list.php">Hủy</a>
</form>

<?php include 'view/footer.php'; ?>
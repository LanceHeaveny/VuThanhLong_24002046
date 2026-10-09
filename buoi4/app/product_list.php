<?php
require_once 'common/dbConnect.php';
require_once 'model/product.php';

// 1. Lấy dữ liệu từ DB truyền vào class ShoppingCart
$stmt = $conn->prepare("SELECT * FROM products ORDER BY id ASC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$itemsList = [];
foreach ($rows as $row) {
    $itemsList[] = new Item((int)$row['id'], $row['name'], (float)$row['price'], (int)$row['quantity']);
}

$cart = new ShoppingCart($itemsList);
$products = $cart->getAllProducts();

include 'view/header.php';
?>

<h3 style="text-align: center;">Danh sách sản phẩm</h3>
<p><a href="product_add.php">+ Thêm sản phẩm mới</a></p>

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Giá (VNĐ)</th>
            <th>Số lượng</th>
            <th>Chức năng</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>
                <tr>
                    <td><center><?= $item->id ?></center></td>
                    <td><center><?= htmlspecialchars($item->name) ?></center></td>
                    <td><center><?= number_format($item->price, 0, ',', '.') ?></center></td>
                    <td><center><?= $item->quantity ?></center></td>
                    <td>
                        <a href="product_edit.php?id=<?= $item->id ?>">Sửa</a> | 
                        <a href="product_delete.php?id=<?= $item->id ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">Không có sản phẩm nào.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Gọi phương thức tính tổng tiền từ class $cart -->
<p><strong>Thành tiền: </strong><?= number_format($cart->totalPriceProduct(), 0, ',', '.') ?> VNĐ</p>

<?php include 'view/footer.php'; ?>
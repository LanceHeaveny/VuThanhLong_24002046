<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ. Vui lòng dùng POST.'
    ]);
    exit;
}

require_once __DIR__ . '/../common/DBconnect.php';

$db = new DBconnect();
$conn = $db->getConnection();

// 1. Kiểm tra mã sản phẩm hợp lệ
$productId = isset($_POST['product_id'])
    ? filter_var($_POST['product_id'], FILTER_VALIDATE_INT)
    : false;

if (!$productId || $productId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Mã sản phẩm không hợp lệ.'
    ]);
    exit;
}

// 2. Kiểm tra file upload tồn tại
if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng chọn file ảnh để upload.'
    ]);
    exit;
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi tải file lên máy chủ (mã lỗi: ' . $file['error'] . ').'
    ]);
    exit;
}

// 3. Kiểm tra dung lượng tối đa 2 MB
$maxBytes = 2 * 1024 * 1024;
if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dung lượng ảnh phải lớn hơn 0 và tối đa 2 MB.'
    ]);
    exit;
}

// 4. Kiểm tra sản phẩm tồn tại trong CSDL
$stmt = $conn->prepare('SELECT id, image FROM products WHERE id = :id');
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Sản phẩm không tồn tại trong hệ thống.'
    ]);
    exit;
}

// 5. Kiểm tra loại ảnh thực tế (MIME qua finfo + xác thực ảnh qua getimagesize)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$imageInfo = @getimagesize($file['tmp_name']);

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/pjpeg' => 'jpg',
    'image/png'  => 'png',
];

if (!isset($allowedMimes[$mimeType]) || $imageInfo === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ chấp nhận file ảnh JPG, JPEG hoặc PNG hợp lệ.'
    ]);
    exit;
}

// 6. Tạo tên file duy nhất và lưu vào thư mục buoi8/app/uploads/
// (Vì index.php nằm ở buoi8/app/, đường dẫn tương đối 'uploads/...' sẽ khớp với <img :src="item.image">)
$extension = $allowedMimes[$mimeType];
$uploadDir = __DIR__ . '/../uploads';

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không thể tạo thư mục uploads.'
    ]);
    exit;
}

$uniqueFileName = 'product_' . $productId . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
$destinationPath = $uploadDir . '/' . $uniqueFileName;
$dbImagePath = 'uploads/' . $uniqueFileName;

if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lưu file ảnh thất bại.'
    ]);
    exit;
}

// 7. Cập nhật đường dẫn vào cột image của bảng products
try {
    $updateStmt = $conn->prepare('UPDATE products SET image = :image WHERE id = :id');
    $updateStmt->execute([
        ':image' => $dbImagePath,
        ':id'    => $productId,
    ]);

    // Xóa ảnh cũ nếu có và khác file mới
    if (!empty($product['image'])) {
        $oldFullPath = __DIR__ . '/../' . ltrim($product['image'], '/');
        if (is_file($oldFullPath) && realpath($oldFullPath) !== realpath($destinationPath)) {
            @unlink($oldFullPath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Upload ảnh sản phẩm thành công.',
        'image'   => $dbImagePath,
        'data'    => [
            'id'    => $productId,
            'image' => $dbImagePath,
        ],
    ]);
} catch (PDOException $e) {
    // Nếu cập nhật DB lỗi thì xóa file vừa lưu để tránh file mồ côi
    if (is_file($destinationPath)) {
        @unlink($destinationPath);
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cập nhật ảnh vào CSDL: ' . $e->getMessage(),
    ]);
}
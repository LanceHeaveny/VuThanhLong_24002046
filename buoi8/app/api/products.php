<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Sửa lỗi cú pháp require_once_once tại đây:
require_once __DIR__ . "/../common/DBconnect.php";

$database = new DBconnect();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

function validate_input($data) {
    if (!isset($data['name']) || !isset($data['price']) || !isset($data['stock'])) {
        return "Vui lòng nhập đầy đủ tên, đơn giá và số lượng tồn.";
    }

    $name = trim($data['name']);
    $price = $data['price'];
    $stock = $data['stock'];

    if (mb_strlen($name) < 1 || mb_strlen($name) > 100) {
        return "Tên sản phẩm phải có độ dài từ 1 đến 100 ký tự.";
    }

    if (!filter_var($price, FILTER_VALIDATE_INT) || (int)$price <= 0) {
        return "Đơn giá phải là số nguyên lớn hơn 0.";
    }

    if (!filter_var($stock, FILTER_VALIDATE_INT, array("options" => array("min_range" => 0))) && $stock !== 0 && $stock !== "0") {
        return "Số lượng tồn phải là số nguyên không âm (>= 0).";
    }

    return null;
}

switch ($method) {
    case 'GET':
        $stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
        $products = $stmt->fetchAll();
        echo json_encode(["success" => true, "data" => $products]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        $error = validate_input($input);
        if ($error !== null) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => $error]);
            break;
        }

        $stmt = $db->prepare("INSERT INTO products (name, price, stock) VALUES (:name, :price, :stock)");
        $stmt->execute([
            ':name' => trim($input['name']),
            ':price' => (int)$input['price'],
            ':stock' => (int)$input['stock']
        ]);
        echo json_encode(["success" => true, "message" => "Thêm sản phẩm thành công!"]);
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Thiếu tham số id."]);
            break;
        }
        $id = (int)$_GET['id'];
        $input = json_decode(file_get_contents("php://input"), true);
        $error = validate_input($input);
        if ($error !== null) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => $error]);
            break;
        }

        $check = $db->prepare("SELECT id FROM products WHERE id = :id");
        $check->execute([':id' => $id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Sản phẩm không tồn tại."]);
            break;
        }

        $stmt = $db->prepare("UPDATE products SET name = :name, price = :price, stock = :stock WHERE id = :id");
        $stmt->execute([
            ':name' => trim($input['name']),
            ':price' => (int)$input['price'],
            ':stock' => (int)$input['stock'],
            ':id' => $id
        ]);
        echo json_encode(["success" => true, "message" => "Cập nhật sản phẩm thành công!"]);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Thiếu tham số id."]);
            break;
        }
        $id = (int)$_GET['id'];

        $check = $db->prepare("SELECT id FROM products WHERE id = :id");
        $check->execute([':id' => $id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Sản phẩm không tồn tại."]);
            break;
        }

        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(["success" => true, "message" => "Xóa sản phẩm thành công!"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Phương thức không được hỗ trợ."]);
        break;
}
?>
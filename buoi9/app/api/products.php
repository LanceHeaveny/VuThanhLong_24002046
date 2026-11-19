<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
        $page = 1;
        $limit = 5;

        if (isset($_GET['page'])) {
            $rawPage = filter_var($_GET['page'], FILTER_VALIDATE_INT);
            if ($rawPage === false || $rawPage < 1) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Tham số page phải là số nguyên dương (>= 1)."
                ]);
                break;
            }
            $page = $rawPage;
        }

        if (isset($_GET['limit'])) {
            $rawLimit = filter_var($_GET['limit'], FILTER_VALIDATE_INT);
            if ($rawLimit === false || $rawLimit !== 5) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Tham số limit phải bằng 5."
                ]);
                break;
            }
            $limit = $rawLimit;
        }

        $total = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $limit));

        // Nếu page vượt trang cuối, trả về dữ liệu của trang cuối cùng và page thực tế
        $actualPage = min($page, $totalPages);
        $offset = ($actualPage - 1) * $limit;

        $stmt = $db->prepare("SELECT id, name, price, stock, image FROM products ORDER BY id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        echo json_encode([
            "success" => true,
            "data" => $products,
            "pagination" => [
                "page" => $actualPage,
                "limit" => $limit,
                "total" => $total,
                "total_pages" => $totalPages
            ]
        ]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Dữ liệu JSON không hợp lệ."]);
            break;
        }

        $error = validate_input($input);
        if ($error !== null) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => $error]);
            break;
        }

        $name = trim($input['name']);
        $price = (int)$input['price'];
        $stock = (int)$input['stock'];

        $stmt = $db->prepare("INSERT INTO products (name, price, stock, image) VALUES (:name, :price, :stock, NULL)");
        $stmt->execute([
            ':name' => $name,
            ':price' => $price,
            ':stock' => $stock
        ]);

        $newId = (int)$db->lastInsertId();

        echo json_encode([
            "success" => true,
            "message" => "Thêm sản phẩm thành công!",
            "id" => $newId,
            "data" => [
                "id" => $newId,
                "name" => $name,
                "price" => $price,
                "stock" => $stock,
                "image" => null
            ]
        ]);
        break;

    case 'PUT':
        if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT) || (int)$_GET['id'] <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Tham số id không hợp lệ."]);
            break;
        }
        $id = (int)$_GET['id'];

        $input = json_decode(file_get_contents("php://input"), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Dữ liệu JSON không hợp lệ."]);
            break;
        }

        $error = validate_input($input);
        if ($error !== null) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => $error]);
            break;
        }

        $check = $db->prepare("SELECT id, image FROM products WHERE id = :id");
        $check->execute([':id' => $id]);
        $existing = $check->fetch();
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Sản phẩm không tồn tại."]);
            break;
        }

        // Không đụng vào cột image ở PUT; ảnh chỉ đổi khi upload thành công qua api/upload.php
        $stmt = $db->prepare("UPDATE products SET name = :name, price = :price, stock = :stock WHERE id = :id");
        $stmt->execute([
            ':name' => trim($input['name']),
            ':price' => (int)$input['price'],
            ':stock' => (int)$input['stock'],
            ':id' => $id
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Cập nhật sản phẩm thành công!",
            "data" => [
                "id" => $id,
                "image" => $existing['image']
            ]
        ]);
        break;

    case 'DELETE':
        if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT) || (int)$_GET['id'] <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Tham số id không hợp lệ."]);
            break;
        }
        $id = (int)$_GET['id'];

        $check = $db->prepare("SELECT id, image FROM products WHERE id = :id");
        $check->execute([':id' => $id]);
        $existing = $check->fetch();
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Sản phẩm không tồn tại."]);
            break;
        }

        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if (!empty($existing['image'])) {
            $oldPath = __DIR__ . '/../' . ltrim($existing['image'], '/');
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        echo json_encode(["success" => true, "message" => "Xóa sản phẩm thành công!"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Phương thức không được hỗ trợ."]);
        break;
}
?>
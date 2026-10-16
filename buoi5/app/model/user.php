<?php
class user {
    public int $id;
    public string $username;
    public string $password;
    private $conn;

    public function __construct($conn, int $id = 0, string $username = '', string $password = '') {
        $this->conn = $conn;
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
    }

    public function checkinfo(string $username, string $password) {
        if (trim($username) == '' || trim($password) == '') {
            return -1;
        }
        return 0;
    }

    public function register(string $username, string $password) {
        $c = $this->checkinfo($username, $password);

        // Kiểm tra xem username đã tồn tại chưa
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            return "Tên đăng nhập đã tồn tại";
        }

        if ($c != -1) {
            $hashedpassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Sửa cột password thành pass_w ở đây
            $stmt = $this->conn->prepare("INSERT INTO users(username, pass_w) VALUES (:username, :password)");
            $result = $stmt->execute([
                'username' => $username, 
                'password' => $hashedpassword
            ]);
            
            return true; 
        } else {
            return "Thông tin bị bỏ trống, vui lòng thử lại";
        }
    }

    public function login(string $username, string $password) {
        $c = $this->checkinfo($username, $password);

        if ($c != -1) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Sử dụng $user['pass_w'] vì tên cột trong DB của bạn là pass_w
            if ($user && password_verify($password, $user['pass_w'])) {
                $this->id = $user['id'];
                $this->username = $user['username'];
                return $user; 
            }
        }
        return false;
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        return true;
    }
}
?>
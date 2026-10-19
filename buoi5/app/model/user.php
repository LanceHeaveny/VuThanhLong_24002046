<?php
class user {
    public int $id;
    public string $username;
    public string $passwd;
    private $conn;

    public function __construct($conn, int $id = 0, string $username = '', string $passwd = '') {
        $this->conn = $conn;
        $this->id = $id;
        $this->username = $username;
        $this->passwd = $passwd;
    }

    public function checkinfo(string $username, string $passwd) {
        if (trim($username) == '' || trim($passwd) == '') {
            return -1;
        }
        return 0;
    }

    public function register(string $username, string $passwd) {
        $c = $this->checkinfo($username, $passwd);

        // Kiểm tra xem username đã tồn tại chưa
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            return "Tên đăng nhập đã tồn tại";
        }

        if ($c != -1) {
            $hashedpassword = password_hash($passwd, PASSWORD_DEFAULT);
            
            // Sửa cột password thành pass_w ở đây
            $stmt = $this->conn->prepare("INSERT INTO users(username, password) VALUES (:username, :passwd)");
            $result = $stmt->execute([
                'username' => $username, 
                'passwd' => $hashedpassword
            ]);
            
            return true; 
        } else {
            return "Thông tin bị bỏ trống, vui lòng thử lại";
        }
    }

    public function login(string $username, string $passwd) {
        $c = $this->checkinfo($username, $passwd);

        if ($c != -1) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($passwd, $user['password'])) {
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

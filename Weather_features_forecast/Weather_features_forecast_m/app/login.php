<?php
require_once __DIR__ . '/common/functions.php';

if (isset($_SESSION['user']['address'])) {
    header('Location: home.php');
    exit;
}

$pageTitle = 'Đăng nhập';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errorMessage = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang và thử lại.';
    } else {
        $usernameInput = $_POST['username'] ?? '';
        $passwordInput = $_POST['password'] ?? '';

        if (!is_string($usernameInput) || !is_string($passwordInput)) {
            $errorMessage = 'Thông tin đăng nhập không hợp lệ.';
        } elseif (trim($usernameInput) === '' || $passwordInput === '') {
            $errorMessage = 'Vui lòng nhập tên đăng nhập và mật khẩu.';
        } else {
            $username = trim($usernameInput);
            $password = $passwordInput;
            try {
                $statement = getDatabaseConnection()->prepare(
                    'SELECT id, username, password, address FROM users WHERE username = :username LIMIT 1'
                );
                $statement->execute([':username' => $username]);
                $user = $statement->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'username' => $user['username'],
                        'address' => $user['address'],
                    ];
                    unset($_SESSION['csrf_token']);
                    setFlashMessage('Đăng nhập thành công.');
                    header('Location: home.php');
                    exit;
                }

                $errorMessage = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
            } catch (PDOException $exception) {
                error_log($exception->getMessage());
                $errorMessage = 'Không thể kết nối database. Hãy kiểm tra cấu hình và import database.sql.';
            }
        }
    }
}

require __DIR__ . '/view/site_header.php';
?>
    <section class="auth-card">
        <p class="eyebrow">CHÀO MỪNG TRỞ LẠI</p>
        <h1>Đăng nhập</h1>
        <p class="form-intro">Đăng nhập để truy cập bảng điều khiển cảm biến.</p>
        <?php if ($errorMessage !== ''): ?>
            <div class="notice notice-error" role="alert"><?= escapeHtml($errorMessage) ?></div>
        <?php endif; ?>
        <form class="site-form" action="login.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <label for="username">Tên đăng nhập</label>
            <input id="username" name="username" type="text" maxlength="100" autocomplete="username" required value="<?= escapeHtml(is_string($_POST['username'] ?? null) ? $_POST['username'] : '') ?>">
            <label for="password">Mật khẩu</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button class="button button-primary button-full" type="submit">Đăng nhập</button>
        </form>
        <p class="form-switch">Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
    </section>
<?php require __DIR__ . '/view/site_footer.php'; ?>
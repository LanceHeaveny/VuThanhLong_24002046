<?php
require_once __DIR__ . '/common/functions.php';

if (isset($_SESSION['user']['address'])) {
    header('Location: home.php');
    exit;
}

$pageTitle = 'Đăng ký';
$errorMessage = '';
$locations = require __DIR__ . '/common/weather_locations.php';
$supportedProvinces = [];
foreach ($locations as $location) {
    $supportedProvinces[] = $location['name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errorMessage = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang và thử lại.';
    } else {
        $usernameInput = $_POST['username'] ?? '';
        $addressInput = $_POST['address'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $passwordConfirmationInput = $_POST['password_confirmation'] ?? '';

        if (
            !is_string($usernameInput)
            || !is_string($addressInput)
            || !is_string($passwordInput)
            || !is_string($passwordConfirmationInput)
        ) {
            $errorMessage = 'Thông tin đăng ký không hợp lệ.';
        } elseif (
            trim($usernameInput) === ''
            || trim($addressInput) === ''
            || $passwordInput === ''
            || $passwordConfirmationInput === ''
        ) {
            $errorMessage = 'Vui lòng điền đầy đủ các trường.';
        } else {
            $username = trim($usernameInput);
            $address = trim($addressInput);
            $password = $passwordInput;
            $passwordConfirmation = $passwordConfirmationInput;
            $normalizeProvince = static fn (string $province): string => function_exists('mb_strtolower')
                ? mb_strtolower(trim($province), 'UTF-8')
                : strtolower(trim($province));
            $supportedProvince = null;
            foreach ($supportedProvinces as $province) {
                if ($normalizeProvince($address) === $normalizeProvince($province)) {
                    $supportedProvince = $province;
                    break;
                }
            }

            if (strlen($username) > 100 || strlen($address) > 255) {
                $errorMessage = 'Tên đăng nhập tối đa 100 ký tự và địa chỉ tối đa 255 ký tự.';
            } elseif ($supportedProvince === null) {
                $errorMessage = 'Chưa được hỗ trợ ở đây. Vui lòng chọn một trong 34 tỉnh, thành phố.';
            } elseif (strlen($password) < 8) {
                $errorMessage = 'Mật khẩu phải có ít nhất 8 ký tự.';
            } elseif ($password !== $passwordConfirmation) {
                $errorMessage = 'Mật khẩu xác nhận không khớp.';
            } else {
                try {
                    $address = $supportedProvince;
                    $db = getDatabaseConnection();
                    $ensureLocationStatement = $db->prepare(
                        'INSERT IGNORE INTO weather_locations (name) VALUES (:name)'
                    );
                    foreach ($supportedProvinces as $province) {
                        $ensureLocationStatement->execute([':name' => $province]);
                    }

                    require_once __DIR__ . '/model/user.php';
                    $user = new User($db, $username, $password, $address);
                    $userId = $user->create();

                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => $userId,
                        'username' => $user->username,
                        'address' => $user->address,
                    ];
                    unset($_SESSION['csrf_token']);
                    setFlashMessage('Tạo tài khoản thành công.');
                    header('Location: home.php');
                    exit;
                } catch (InvalidArgumentException $exception) {
                    $errorMessage = $exception->getMessage();
                } catch (PDOException $exception) {
                    error_log($exception->getMessage());
                    if ($exception->getCode() === '23000') {
                        $errorMessage = 'Tên đăng nhập đã được sử dụng.';
                    } else {
                        $errorMessage = 'Không thể lưu tài khoản. Hãy kiểm tra database và thử lại.';
                    }
                }
            }
        }
    }
}

require __DIR__ . '/view/site_header.php';
?>
    <section class="auth-card">
        <p class="eyebrow">BẮT ĐẦU THEO DÕI</p>
        <h1>Tạo tài khoản</h1>
        <p class="form-intro">Chọn một trong 34 tỉnh, thành phố; nhiều tài khoản có thể dùng cùng một tỉnh/thành.</p>
        <?php if ($errorMessage !== ''): ?>
            <div class="notice notice-error" role="alert"><?= escapeHtml($errorMessage) ?></div>
        <?php endif; ?>
        <form class="site-form" action="register.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <label for="username">Tên đăng nhập</label>
            <input id="username" name="username" type="text" maxlength="100" autocomplete="username" required value="<?= escapeHtml(is_string($_POST['username'] ?? null) ? $_POST['username'] : '') ?>">
            <label for="address">Tỉnh/thành phố</label>
            <input id="address" name="address" type="text" maxlength="255" list="supported-provinces" autocomplete="address-level1" aria-describedby="address-help" required value="<?= escapeHtml(is_string($_POST['address'] ?? null) ? $_POST['address'] : '') ?>">
            <datalist id="supported-provinces">
                <?php foreach ($supportedProvinces as $province): ?>
                    <option value="<?= escapeHtml($province) ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <small class="field-help" id="address-help">Hệ thống hỗ trợ 34 tỉnh, thành phố.</small>
            <label for="password">Mật khẩu <span>(ít nhất 8 ký tự)</span></label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
            <button class="button button-primary button-full" type="submit">Đăng ký</button>
        </form>
        <p class="form-switch">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </section>
<?php require __DIR__ . '/view/site_footer.php'; ?>
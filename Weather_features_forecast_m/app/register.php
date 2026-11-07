<?php
require_once __DIR__ . '/common/functions.php';
require_once __DIR__ . '/common/administrative_locations.php';

if (isset($_SESSION['user']['address'])) {
    header('Location: home.php');
    exit;
}

$pageTitle = 'Đăng ký';
$errorMessage = '';
$locations = require __DIR__ . '/common/weather_locations.php';
$wards = getAdministrativeWards();
$submittedProvinceKey = is_string($_POST['province_key'] ?? null) ? $_POST['province_key'] : '';
$submittedWardKey = is_string($_POST['ward_key'] ?? null) ? $_POST['ward_key'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errorMessage = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang và thử lại.';
    } else {
        $usernameInput = $_POST['username'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $passwordConfirmationInput = $_POST['password_confirmation'] ?? '';

        if (
            !is_string($usernameInput)
            || !is_string($passwordInput)
            || !is_string($passwordConfirmationInput)
        ) {
            $errorMessage = 'Thông tin đăng ký không hợp lệ.';
        } elseif (
            trim($usernameInput) === ''
            || $passwordInput === ''
            || $passwordConfirmationInput === ''
        ) {
            $errorMessage = 'Vui lòng điền đầy đủ các trường.';
        } else {
            $username = trim($usernameInput);
            $password = $passwordInput;
            $passwordConfirmation = $passwordConfirmationInput;
            $province = $locations[$submittedProvinceKey] ?? null;
            $ward = $wards[$submittedWardKey] ?? null;
            $address = $ward['address'] ?? '';

            if (strlen($username) > 100 || strlen($address) > 255) {
                $errorMessage = 'Tên đăng nhập tối đa 100 ký tự và địa chỉ tối đa 255 ký tự.';
            } elseif ($province === null || $ward === null
                || normalizeLocationName($ward['province']) !== normalizeLocationName($province['name'])
            ) {
                $errorMessage = 'Vui lòng chọn xã/phường thuộc tỉnh/thành phố đã chọn.';
            } elseif (strlen($password) < 8) {
                $errorMessage = 'Mật khẩu phải có ít nhất 8 ký tự.';
            } elseif ($password !== $passwordConfirmation) {
                $errorMessage = 'Mật khẩu xác nhận không khớp.';
            } else {
                try {
                    $db = getDatabaseConnection();
                    $ensureLocationStatement = $db->prepare(
                        'INSERT IGNORE INTO weather_locations (name) VALUES (:name)'
                    );
                    foreach ($locations as $location) {
                        $ensureLocationStatement->execute([':name' => $location['name']]);
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
        <p class="form-intro">Chọn tỉnh/thành phố trước, sau đó chọn xã/phường thuộc địa chỉ đó.</p>
        <?php if ($errorMessage !== ''): ?>
            <div class="notice notice-error" role="alert"><?= escapeHtml($errorMessage) ?></div>
        <?php endif; ?>
        <form class="site-form" action="register.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <label for="username">Tên đăng nhập</label>
            <input id="username" name="username" type="text" maxlength="100" autocomplete="username" required value="<?= escapeHtml(is_string($_POST['username'] ?? null) ? $_POST['username'] : '') ?>">
            <label for="province-key">Tỉnh/thành phố</label>
            <select id="province-key" name="province_key" autocomplete="address-level1" required>
                <option value="">Chọn tỉnh/thành phố</option>
                <?php foreach ($locations as $key => $location): ?>
                    <option value="<?= escapeHtml($key) ?>" <?= $submittedProvinceKey === $key ? 'selected' : '' ?>>
                        <?= escapeHtml($location['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="ward-key">Phường/xã</label>
            <select id="ward-key" name="ward_key" autocomplete="address-level2" aria-describedby="address-help" required disabled>
                <option value="">Chọn phường/xã</option>
                <?php
                $wardsByProvince = [];
                $provinceKeysByName = [];
                foreach ($locations as $locationKey => $location) {
                    $provinceKeysByName[normalizeLocationName($location['name'])] = $locationKey;
                }
                foreach ($wards as $key => $ward) {
                    $provinceKey = $provinceKeysByName[normalizeLocationName($ward['province'])] ?? null;
                    if ($provinceKey === null) {
                        throw new RuntimeException('Không tìm thấy tỉnh/thành phố của một xã, phường.');
                    }
                    $wardsByProvince[$provinceKey][$key] = $ward;
                }
                foreach ($wardsByProvince as $provinceKey => $provinceWards):
                ?>
                    <optgroup label="<?= escapeHtml($locations[$provinceKey]['name']) ?>" data-province-key="<?= escapeHtml($provinceKey) ?>">
                        <?php foreach ($provinceWards as $key => $ward): ?>
                            <option value="<?= escapeHtml($key) ?>" <?= $submittedWardKey === $key ? 'selected' : '' ?>>
                                <?= escapeHtml($ward['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <small class="field-help" id="address-help">Chọn từ danh sách để lưu địa chỉ xã/phường và tỉnh/thành phố đồng bộ.</small>
            <label for="password">Mật khẩu <span>(ít nhất 8 ký tự)</span></label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
            <button class="button button-primary button-full" type="submit">Đăng ký</button>
        </form>
        <p class="form-switch">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </section>
    <script>
        const provinceSelect = document.getElementById('province-key');
        const wardSelect = document.getElementById('ward-key');
        const wardGroups = Array.from(wardSelect.querySelectorAll('optgroup'));
        const selectedWardKey = <?= json_encode($submittedWardKey, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;

        function showWardsForProvince(provinceKey, wardKey = '') {
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = provinceKey ? 'Chọn phường/xã' : 'Trước tiên chọn tỉnh/thành phố';
            wardSelect.replaceChildren(placeholder);

            const wardGroup = wardGroups.find((group) => group.dataset.provinceKey === provinceKey);
            if (!wardGroup) {
                wardSelect.disabled = true;
                return;
            }

            wardSelect.append(wardGroup.cloneNode(true));
            wardSelect.disabled = false;
            wardSelect.value = wardKey;
        }

        provinceSelect.addEventListener('change', () => showWardsForProvince(provinceSelect.value));
        showWardsForProvince(provinceSelect.value, selectedWardKey);
    </script>
<?php require __DIR__ . '/view/site_footer.php'; ?>
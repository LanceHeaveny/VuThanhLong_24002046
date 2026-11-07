<?php
require_once __DIR__ . '/common/functions.php';
require_once __DIR__ . '/common/weather_queue.php';
require_once __DIR__ . '/common/administrative_locations.php';
requireLogin();

$pageTitle = 'Lịch sử 12 giờ';
$weatherIcons = [
    'trời quang' => '☀️',
    'ít mây' => '🌤️',
    'mây rải rác' => '⛅',
    'nhiều mây' => '☁️',
    'sương mù' => '🌫️',
    'mưa phùn' => '🌦️',
    'mưa' => '🌧️',
    'mưa lớn' => '🌧️',
    'mưa rào' => '🌦️',
    'mưa rào lớn' => '🌧️',
    'tuyết' => '❄️',
    'dông' => '⛈️',
];
$user = $_SESSION['user'];
$locations = require __DIR__ . '/common/weather_locations.php';
$wards = getAdministrativeWards();
$requestedWardAddress = $_GET['ward'] ?? null;
$selectedWard = null;
$invalidRequestedWard = isset($_GET['ward']) && !is_string($_GET['ward']);

if (is_string($requestedWardAddress) && trim($requestedWardAddress) !== '') {
    $normalizedWardAddress = normalizeLocationName($requestedWardAddress);
    foreach ($wards as $ward) {
        if (normalizeLocationName($ward['address']) === $normalizedWardAddress) {
            $selectedWard = $ward;
            break;
        }
    }
    $invalidRequestedWard = $selectedWard === null;
} elseif (!isset($_GET['location'])) {
    $normalizedUserAddress = normalizeLocationName($user['address']);
    foreach ($wards as $ward) {
        if (normalizeLocationName($ward['address']) === $normalizedUserAddress) {
            $selectedWard = $ward;
            break;
        }
    }
}

$selectedLocationKey = $_GET['location'] ?? null;

if ($selectedWard !== null) {
    $nearest = findNearestWeatherLocation(
        $selectedWard['latitude'],
        $selectedWard['longitude'],
        $locations
    );
    $selectedLocationKey = $nearest['key'];
} elseif (!is_string($selectedLocationKey) || !isset($locations[$selectedLocationKey])) {
    $selectedLocationKey = null;
    foreach ($locations as $key => $location) {
        if ($location['name'] === $user['address']) {
            $selectedLocationKey = $key;
            break;
        }
    }

    $selectedLocationKey = $selectedLocationKey ?? 'ha-noi';
}

$selectedLocation = $locations[$selectedLocationKey];
$displayLocationName = $selectedWard === null
    ? $selectedLocation['name']
    : $selectedWard['address'];
$hourlyReadings = [];
$warningMessage = '';
$errorMessage = $invalidRequestedWard
    ? 'Chọn một xã/phường trong danh sách.'
    : '';

if (!$invalidRequestedWard) {
    try {
        $db = getDatabaseConnection();
        try {
            ensureWeatherQueues($db, $locations);
        } catch (WeatherProviderException) {
            $warningMessage = 'Dịch vụ thời tiết trực tuyến đang tạm gián đoạn. Đang hiển thị lịch sử đã lưu.';
        }

        $statement = $db->prepare(
            'SELECT recorded_hour, temperature, humidity, temperature_amplitude, weather_type
             FROM weather_hourly_history
             WHERE location = :location
             ORDER BY recorded_hour ASC
             LIMIT 12'
        );
        $statement->execute([':location' => $selectedLocation['name']]);
        $hourlyReadings = $statement->fetchAll();
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        $errorMessage = 'Không thể tải lịch sử. Hãy kiểm tra kết nối database và chạy database.sql.';
    }
}

require __DIR__ . '/view/site_header.php';
?>
    <section class="page-heading">
        <div>
            <p class="eyebrow">HÀNG ĐỢI DỮ LIỆU 12 GIỜ</p>
            <h1>Lịch sử theo giờ tròn</h1>
            <p class="heading-subtitle">
                12 mốc gần nhất tại <?= escapeHtml($displayLocationName) ?>.
                <?php if ($selectedWard !== null): ?>
                    Dùng điểm thời tiết gần nhất: <?= escapeHtml($selectedLocation['name']) ?>
                    (<?= escapeHtml((string) $nearest['distance_km']) ?> km).
                <?php endif; ?>
                Biên độ nhiệt là chênh lệch cao nhất–thấp nhất trong giờ đó.
            </p>
        </div>
        <form class="history-location-form" method="get">
            <label for="history-location">Điểm thời tiết</label>
            <select id="history-location" name="location" onchange="document.getElementById('history-ward').value = ''; this.form.submit()">
                <?php foreach ($locations as $key => $location): ?>
                    <option value="<?= escapeHtml($key) ?>" <?= $key === $selectedLocationKey ? 'selected' : '' ?>>
                        <?= escapeHtml($location['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="history-ward">Hoặc tìm xã/phường</label>
            <input id="history-ward" name="ward" type="search" list="history-wards" autocomplete="off" onchange="this.form.submit()" value="<?= escapeHtml($selectedWard['address'] ?? '') ?>">
            <datalist id="history-wards">
                <?php foreach ($wards as $ward): ?>
                    <option value="<?= escapeHtml($ward['address']) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </form>
    </section>

    <?php if ($warningMessage !== ''): ?>
        <div class="notice notice-warning" role="status"><?= escapeHtml($warningMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <div class="notice notice-error" role="alert"><?= escapeHtml($errorMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage === '' && $hourlyReadings === []): ?>
        <section class="content-card">
            <div class="empty-state">
                <strong>Chưa có dữ liệu theo giờ</strong>
                <p>Mở dashboard để tạo các số đo mô phỏng theo phút.</p>
                <a href="dashboard_display.HTML">Mở dashboard</a>
            </div>
        </section>
    <?php elseif ($errorMessage === ''): ?>
        <section class="content-card history-card hourly-history-card" aria-label="Lịch sử thời tiết theo giờ">
            <div class="table-scroll">
                <table class="data-table hourly-history-table">
                    <thead>
                        <tr>
                            <th scope="col">Chỉ số</th>
                            <?php foreach ($hourlyReadings as $reading): ?>
                                <?php $hour = new DateTimeImmutable($reading['recorded_hour']); ?>
                                <th scope="col">
                                    <time datetime="<?= escapeHtml($hour->format(DateTimeInterface::ATOM)) ?>">
                                        <?= escapeHtml($hour->format('H:i')) ?>
                                    </time>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">Nhiệt độ</th>
                            <?php foreach ($hourlyReadings as $reading): ?>
                                <td><?= escapeHtml(number_format((float) $reading['temperature'], 1)) ?> °C</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th scope="row">Độ ẩm</th>
                            <?php foreach ($hourlyReadings as $reading): ?>
                                <td><?= escapeHtml(number_format((float) $reading['humidity'], 1)) ?>%</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th scope="row">Biên độ nhiệt</th>
                            <?php foreach ($hourlyReadings as $reading): ?>
                                <td><?= escapeHtml(number_format((float) $reading['temperature_amplitude'], 1)) ?> °C</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th scope="row">Kiểu thời tiết</th>
                            <?php foreach ($hourlyReadings as $reading): ?>
                                <?php
                                $weatherType = $reading['weather_type'];
                                $weatherIcon = $weatherIcons[$weatherType] ?? '◷';
                                ?>
                                <td class="hourly-weather-cell">
                                    <span class="hourly-weather-icon" aria-hidden="true"><?= escapeHtml($weatherIcon) ?></span>
                                    <span><?= escapeHtml(ucfirst($weatherType)) ?></span>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="table-note">
                Mỗi cột là một giờ tròn; biên độ nhiệt tính từ các số đo phút trong giờ đó.
                Hàng đợi lưu tối đa 720 số đo phút cho mỗi điểm thời tiết.
            </p>
        </section>
    <?php endif; ?>
<?php require __DIR__ . '/view/site_footer.php'; ?>

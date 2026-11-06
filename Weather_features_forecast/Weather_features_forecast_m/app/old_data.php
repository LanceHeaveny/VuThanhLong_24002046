<?php
require_once __DIR__ . '/common/functions.php';
require_once __DIR__ . '/common/weather_queue.php';
requireLogin();

$pageTitle = 'Lịch sử 12 giờ';
$user = $_SESSION['user'];
$locations = require __DIR__ . '/common/weather_locations.php';
$selectedLocationKey = $_GET['location'] ?? null;

if (!is_string($selectedLocationKey) || !isset($locations[$selectedLocationKey])) {
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
$hourlyReadings = [];
$errorMessage = '';

try {
    $db = getDatabaseConnection();
    ensureWeatherQueues($db, $locations);

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

require __DIR__ . '/view/site_header.php';
?>
    <section class="page-heading">
        <div>
            <p class="eyebrow">HÀNG ĐỢI DỮ LIỆU 12 GIỜ</p>
            <h1>Lịch sử theo giờ tròn</h1>
            <p class="heading-subtitle">
                12 mốc gần nhất tại <?= escapeHtml($selectedLocation['name']) ?>.
                Biên độ nhiệt là chênh lệch cao nhất–thấp nhất trong giờ đó.
            </p>
        </div>
        <form class="history-location-form" method="get">
            <label for="history-location">Tỉnh, thành phố</label>
            <select id="history-location" name="location" onchange="this.form.submit()">
                <?php foreach ($locations as $key => $location): ?>
                    <option value="<?= escapeHtml($key) ?>" <?= $key === $selectedLocationKey ? 'selected' : '' ?>>
                        <?= escapeHtml($location['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <?php if ($errorMessage !== ''): ?>
        <div class="notice notice-error" role="alert"><?= escapeHtml($errorMessage) ?></div>
    <?php elseif ($hourlyReadings === []): ?>
        <section class="content-card">
            <div class="empty-state">
                <strong>Chưa có dữ liệu theo giờ</strong>
                <p>Mở dashboard để tạo các số đo mô phỏng theo phút.</p>
                <a href="dashboard_display.HTML">Mở dashboard</a>
            </div>
        </section>
    <?php else: ?>
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
                                <td><?= escapeHtml(ucfirst($reading['weather_type'])) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="table-note">
                Mỗi cột là một giờ tròn; biên độ nhiệt tính từ các số đo phút trong giờ đó.
                Hàng đợi lưu tối đa 720 số đo phút cho mỗi tỉnh/thành.
            </p>
        </section>
    <?php endif; ?>
<?php require __DIR__ . '/view/site_footer.php'; ?>

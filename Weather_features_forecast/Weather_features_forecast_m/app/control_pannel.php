<?php
require_once __DIR__ . '/common/functions.php';
require_once __DIR__ . '/model/temperature.php';
require_once __DIR__ . '/model/humidity.php';
require_once __DIR__ . '/model/weather.php';
require_once __DIR__ . '/model/wet_dry.php';
requireLogin();

$pageTitle = 'Điều khiển cảm biến';
$user = $_SESSION['user'];
$errorMessage = '';
$temperature = null;
$humidity = null;
$fahrenheit = null;
$temperatureChange = null;
$humidityChange = null;
$absoluteHumidity = null;
$weatherRecord = null;
$inferredWeather = null;
$syncMoistureClassification = static function (PDO $db, string $location): void {
    $statement = $db->prepare(
        'SELECT id, value FROM humidity WHERE location = :location
         ORDER BY recorded_at DESC, id DESC LIMIT 1'
    );
    $statement->execute([':location' => $location]);
    $humidity = $statement->fetch();

    $statement = $db->prepare(
        'SELECT id, value, unit, recorded_at FROM temperature WHERE location = :location
         ORDER BY recorded_at DESC, id DESC LIMIT 1'
    );
    $statement->execute([':location' => $location]);
    $temperature = $statement->fetch();

    if (!$humidity || !$temperature) {
        return;
    }

    $temperatureCelsius = (float) $temperature['value'];
    if (in_array($temperature['unit'], ['°F', 'F'], true)) {
        $temperatureCelsius = ($temperatureCelsius - 32) * 5 / 9;
    }

    $humidityId = (int) $humidity['id'];
    $temperatureId = (int) $temperature['id'];
    $wet = new Wet(
        (float) $humidity['value'],
        $temperatureCelsius,
        $humidityId,
        $temperatureId,
        $temperature['recorded_at']
    );

    if ($wet->isWet()) {
        $db->prepare('DELETE FROM dry WHERE humidity_id = :humidity_id AND temperature_id = :temperature_id')
            ->execute([':humidity_id' => $humidityId, ':temperature_id' => $temperatureId]);
        $db->prepare(
            'INSERT INTO wet (humidity_id, temperature_id)
             VALUES (:humidity_id, :temperature_id)
             ON DUPLICATE KEY UPDATE recorded_at = CURRENT_TIMESTAMP'
        )->execute([':humidity_id' => $humidityId, ':temperature_id' => $temperatureId]);
        return;
    }

    $dry = new Dry(
        (float) $humidity['value'],
        $temperatureCelsius,
        $humidityId,
        $temperatureId,
        $temperature['recorded_at']
    );
    if ($dry->isDry()) {
        $db->prepare('DELETE FROM wet WHERE humidity_id = :humidity_id AND temperature_id = :temperature_id')
            ->execute([':humidity_id' => $humidityId, ':temperature_id' => $temperatureId]);
        $db->prepare(
            'INSERT INTO dry (humidity_id, temperature_id)
             VALUES (:humidity_id, :temperature_id)
             ON DUPLICATE KEY UPDATE recorded_at = CURRENT_TIMESTAMP'
        )->execute([':humidity_id' => $humidityId, ':temperature_id' => $temperatureId]);
        return;
    }

    $db->prepare('DELETE FROM wet WHERE humidity_id = :humidity_id AND temperature_id = :temperature_id')
        ->execute([':humidity_id' => $humidityId, ':temperature_id' => $temperatureId]);
    $db->prepare('DELETE FROM dry WHERE humidity_id = :humidity_id AND temperature_id = :temperature_id')
        ->execute([':humidity_id' => $humidityId, ':temperature_id' => $temperatureId]);
};

try {
    $db = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken()) {
            $errorMessage = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
        } else {
            $sensorIdInput = $_POST['sensor_id'] ?? '';
            $valueInput = $_POST['value'] ?? '';
            $measurementType = $_POST['measurement_type'] ?? '';

            if (
                !is_string($sensorIdInput)
                || !is_string($measurementType)
            ) {
                $errorMessage = 'Thông tin cảm biến không hợp lệ.';
            } else {
                $sensorId = trim($sensorIdInput);
                $recordedAt = date('Y-m-d H:i:s');
                $requiresNumericValue = in_array($measurementType, ['temperature', 'humidity'], true);
                $numericValueValid = is_string($valueInput) && is_numeric($valueInput);
                $value = $numericValueValid ? (float) $valueInput : 0.0;

                if ($sensorId === '' || strlen($sensorId) > 100) {
                    $errorMessage = 'Mã cảm biến không hợp lệ.';
                } elseif ($requiresNumericValue && (!$numericValueValid || !is_finite($value))) {
                    $errorMessage = 'Giá trị đo phải là một số hợp lệ.';
                } elseif ($measurementType === 'temperature') {
                    $unit = $_POST['unit'] ?? '°C';
                    if (!is_string($unit) || !in_array($unit, ['°C', '°F'], true)) {
                        $errorMessage = 'Đơn vị nhiệt độ không hợp lệ.';
                    } else {
                        new Temperature(0, $sensorId, $value, $unit, $recordedAt, $user['address']);
                        $db->beginTransaction();
                        $statement = $db->prepare(
                            'INSERT INTO temperature (sensor_id, value, unit, recorded_at, location)
                             VALUES (:sensor_id, :value, :unit, CURRENT_TIMESTAMP, :location)'
                        );
                        $statement->execute([
                            ':sensor_id' => $sensorId,
                            ':value' => $value,
                            ':unit' => $unit,
                            ':location' => $user['address'],
                        ]);
                    }
                } elseif ($measurementType === 'humidity') {
                    new Humidity(0, $sensorId, $value, '%', $recordedAt, $user['address']);
                    $db->beginTransaction();
                    $statement = $db->prepare(
                        'INSERT INTO humidity (sensor_id, value, unit, recorded_at, location)
                         VALUES (:sensor_id, :value, :unit, CURRENT_TIMESTAMP, :location)'
                    );
                    $statement->execute([
                        ':sensor_id' => $sensorId,
                        ':value' => $value,
                        ':unit' => '%',
                        ':location' => $user['address'],
                    ]);
                } elseif ($measurementType === 'weather') {
                    $weatherType = $_POST['weather_type'] ?? '';
                    if (!is_string($weatherType)) {
                        $errorMessage = 'Kiểu thời tiết không hợp lệ.';
                    } else {
                        $weatherModel = new Weather(0, $sensorId, $weatherType, $recordedAt, $user['address']);
                        $statement = $db->prepare(
                            'INSERT INTO weather (sensor_id, weather_type, recorded_at, location)
                             VALUES (:sensor_id, :weather_type, CURRENT_TIMESTAMP, :location)'
                        );
                        $statement->execute([
                            ':sensor_id' => $sensorId,
                            ':weather_type' => $weatherModel->weather_type,
                            ':location' => $user['address'],
                        ]);
                    }
                } else {
                    $errorMessage = 'Loại dữ liệu không hợp lệ.';
                }

                if ($errorMessage === '') {
                    if (in_array($measurementType, ['temperature', 'humidity'], true)) {
                        $syncMoistureClassification($db, $user['address']);
                        $db->commit();
                    }
                    $successMessages = [
                        'temperature' => 'Đã lưu bản ghi nhiệt độ.',
                        'humidity' => 'Đã lưu bản ghi độ ẩm.',
                        'weather' => 'Đã lưu kiểu thời tiết.',
                    ];
                    setFlashMessage($successMessages[$measurementType]);
                    header('Location: control_pannel.php');
                    exit;
                }
            }
        }
    }

    $statement = $db->prepare(
        'SELECT id, sensor_id, value, unit, recorded_at
         FROM temperature WHERE location = :location
         ORDER BY recorded_at DESC, id DESC LIMIT 2'
    );
    $statement->execute([':location' => $user['address']]);
    $temperatureRows = $statement->fetchAll();
    $temperature = $temperatureRows[0] ?? null;

    $statement = $db->prepare(
        'SELECT id, sensor_id, value, unit, recorded_at
         FROM humidity WHERE location = :location
         ORDER BY recorded_at DESC, id DESC LIMIT 2'
    );
    $statement->execute([':location' => $user['address']]);
    $humidityRows = $statement->fetchAll();
    $humidity = $humidityRows[0] ?? null;

    $statement = $db->prepare(
        'SELECT id, sensor_id, weather_type, recorded_at
         FROM weather WHERE location = :location
         ORDER BY recorded_at DESC, id DESC LIMIT 1'
    );
    $statement->execute([':location' => $user['address']]);
    $weatherRecord = $statement->fetch() ?: null;

    if ($temperature !== null) {
        $previousTemperature = $temperatureRows[1] ?? null;
        $previousValue = null;

        if ($previousTemperature !== null && $previousTemperature['sensor_id'] === $temperature['sensor_id']) {
            $previousValue = (float) $previousTemperature['value'];
            $previousUnit = $previousTemperature['unit'];
            $currentUnit = $temperature['unit'];

            if (in_array($previousUnit, ['°F', 'F'], true) && in_array($currentUnit, ['°C', 'C'], true)) {
                $previousValue = ($previousValue - 32) * 5 / 9;
            } elseif (in_array($previousUnit, ['°C', 'C'], true) && in_array($currentUnit, ['°F', 'F'], true)) {
                $previousValue = ($previousValue * 9 / 5) + 32;
            }
        }

        $temperatureModel = new Temperature(
            (int) $temperature['id'],
            $temperature['sensor_id'],
            (float) $temperature['value'],
            $temperature['unit'],
            $temperature['recorded_at'],
            $user['address'],
            $previousValue
        );
        $fahrenheit = $temperatureModel->fahrenheit;
        $temperatureChange = $temperatureModel->temperature_change;
    }

    if ($humidity !== null) {
        $previousHumidity = $humidityRows[1] ?? null;
        $previousValue = $previousHumidity !== null && $previousHumidity['sensor_id'] === $humidity['sensor_id']
            ? (float) $previousHumidity['value']
            : null;
        $temperatureCelsius = null;

        if ($temperature !== null) {
            $temperatureCelsius = (float) $temperature['value'];
            if (in_array($temperature['unit'], ['°F', 'F'], true)) {
                $temperatureCelsius = ($temperatureCelsius - 32) * 5 / 9;
            }
        }

        $humidityModel = new Humidity(
            (int) $humidity['id'],
            $humidity['sensor_id'],
            (float) $humidity['value'],
            $humidity['unit'],
            $humidity['recorded_at'],
            $user['address'],
            $temperatureCelsius,
            $previousValue
        );
        $humidityChange = $humidityModel->humidity_change;
        $absoluteHumidity = $humidityModel->absolute_humidity;
    }

    if ($temperature !== null && $humidity !== null) {
        $temperatureCelsius = (float) $temperature['value'];
        if (in_array($temperature['unit'], ['°F', 'F'], true)) {
            $temperatureCelsius = ($temperatureCelsius - 32) * 5 / 9;
        }
        $inferredWeather = Weather::inferFromConditions((float) $humidity['value'], $temperatureCelsius);
    }
} catch (InvalidArgumentException $exception) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }
    $errorMessage = $exception->getMessage();
} catch (PDOException $exception) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log($exception->getMessage());
    $errorMessage = 'Không thể truy cập database. Hãy import database.sql và kiểm tra cấu hình kết nối.';
}

require __DIR__ . '/view/site_header.php';
?>
    <section class="page-heading">
        <div>
            <p class="eyebrow">BẢNG ĐIỀU KHIỂN</p>
            <h1>Điều khiển cảm biến</h1>
            <p class="heading-subtitle">Vị trí lưu dữ liệu: <?= escapeHtml($user['address']) ?></p>
        </div>
        <a class="button button-secondary" href="old_data.php">Xem dữ liệu cũ</a>
    </section>

    <?php if ($errorMessage !== ''): ?>
        <div class="notice notice-error" role="alert"><?= escapeHtml($errorMessage) ?></div>
    <?php endif; ?>

    <section class="reading-grid" aria-label="Số đo mới nhất">
        <article class="reading-card">
            <span class="reading-label">NHIỆT ĐỘ MỚI NHẤT</span>
            <strong><?= $temperature ? escapeHtml((string) $temperature['value'] . ' ' . $temperature['unit']) : 'Chưa có dữ liệu' ?></strong>
            <small>
                <?php if ($temperature): ?>
                    <?= $fahrenheit !== null ? escapeHtml(number_format($fahrenheit, 1) . ' °F · ') : '' ?>
                    <?= $temperatureChange === null ? 'Chưa có số đo trước' : escapeHtml(($temperatureChange > 0 ? 'Tăng +' : ($temperatureChange < 0 ? 'Giảm ' : 'Không đổi ')) . number_format($temperatureChange, 1) . ' ' . $temperature['unit']) ?>
                <?php else: ?>
                    Thêm số đo đầu tiên bên dưới.
                <?php endif; ?>
            </small>
            <?php if ($temperature): ?>
                <small class="reading-time"><?= escapeHtml($temperature['recorded_at']) ?></small>
            <?php endif; ?>
        </article>
        <article class="reading-card">
            <span class="reading-label">ĐỘ ẨM MỚI NHẤT</span>
            <strong><?= $humidity ? escapeHtml((string) $humidity['value'] . ' ' . $humidity['unit']) : 'Chưa có dữ liệu' ?></strong>
            <small>
                <?php if ($humidity): ?>
                    <?= $humidityChange === null ? 'Chưa có số đo trước' : escapeHtml(($humidityChange > 0 ? 'Tăng +' : ($humidityChange < 0 ? 'Giảm ' : 'Không đổi ')) . number_format($humidityChange, 1) . ' điểm %') ?>
                    <?= $absoluteHumidity !== null ? escapeHtml(' · Độ ẩm tuyệt đối ' . number_format($absoluteHumidity, 2) . ' g/m³') : '' ?>
                <?php else: ?>
                    Thêm số đo đầu tiên bên dưới.
                <?php endif; ?>
            </small>
            <?php if ($humidity): ?>
                <small class="reading-time"><?= escapeHtml($humidity['recorded_at']) ?></small>
            <?php endif; ?>
        </article>
        <article class="reading-card weather-reading-card">
            <span class="reading-label">KIỂU THỜI TIẾT</span>
            <strong><?= $weatherRecord ? escapeHtml(ucfirst($weatherRecord['weather_type'])) : ($inferredWeather !== null ? escapeHtml(ucfirst($inferredWeather)) : 'Chưa xác định') ?></strong>
            <small>
                <?= $weatherRecord
                    ? escapeHtml('Ghi nhận bởi cảm biến ' . $weatherRecord['sensor_id'] . ' · ' . $weatherRecord['recorded_at'])
                    : ($inferredWeather !== null ? 'Ước tính từ nhiệt độ và độ ẩm mới nhất.' : 'Chưa đủ điều kiện để ước tính từ số đo hiện có.') ?>
            </small>
        </article>
    </section>

    <section class="control-grid">
        <article class="content-card">
            <h2>Cập nhật nhiệt độ</h2>
            <p>Thêm một bản ghi mới vào lịch sử cảm biến.</p>
            <form class="site-form" action="control_pannel.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <input type="hidden" name="measurement_type" value="temperature">
                <label for="temperature-sensor">Mã cảm biến</label>
                <input id="temperature-sensor" name="sensor_id" type="text" maxlength="100" required>
                <label for="temperature-value">Nhiệt độ</label>
                <div class="input-unit-row">
                    <input id="temperature-value" name="value" type="number" step="any" required>
                    <select name="unit" aria-label="Đơn vị nhiệt độ">
                        <option value="°C">°C</option>
                        <option value="°F">°F</option>
                    </select>
                </div>
                <button class="button button-primary" type="submit">Lưu nhiệt độ</button>
            </form>
        </article>
        <article class="content-card">
            <h2>Cập nhật độ ẩm</h2>
            <p>Giá trị độ ẩm tương đối phải nằm trong khoảng 0–100%.</p>
            <form class="site-form" action="control_pannel.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <input type="hidden" name="measurement_type" value="humidity">
                <label for="humidity-sensor">Mã cảm biến</label>
                <input id="humidity-sensor" name="sensor_id" type="text" maxlength="100" required>
                <label for="humidity-value">Độ ẩm (%)</label>
                <input id="humidity-value" name="value" type="number" min="0" max="100" step="any" required>
                <button class="button button-primary" type="submit">Lưu độ ẩm</button>
            </form>
        </article>
        <article class="content-card">
            <h2>Cập nhật kiểu thời tiết</h2>
            <p>Chọn trạng thái được cảm biến hoặc người dùng ghi nhận.</p>
            <form class="site-form" action="control_pannel.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <input type="hidden" name="measurement_type" value="weather">
                <label for="weather-sensor">Mã cảm biến</label>
                <input id="weather-sensor" name="sensor_id" type="text" maxlength="100" required>
                <label for="weather-type">Kiểu thời tiết</label>
                <select id="weather-type" name="weather_type" required>
                    <option value="nắng">Nắng</option>
                    <option value="mưa">Mưa</option>
                    <option value="sương mù">Sương mù</option>
                    <option value="tuyết rơi">Tuyết rơi</option>
                    <option value="sấm sét">Sấm sét</option>
                </select>
                <button class="button button-primary" type="submit">Lưu kiểu thời tiết</button>
            </form>
        </article>
    </section>
<?php require __DIR__ . '/view/site_footer.php'; ?>
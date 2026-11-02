<?php
require_once __DIR__ . '/common/functions.php';
requireLogin();

$pageTitle = 'Lịch sử dữ liệu';
$user = $_SESSION['user'];
$records = [];
$errorMessage = '';

try {
    $statement = getDatabaseConnection()->prepare(
        'SELECT id, sensor_id, metric, value, unit, recorded_at
         FROM (
             SELECT id, sensor_id, \'Nhiệt độ\' AS metric, value, unit, recorded_at
             FROM temperature WHERE location = :temperature_location
             UNION ALL
             SELECT id, sensor_id, \'Độ ẩm\' AS metric, value, unit, recorded_at
             FROM humidity WHERE location = :humidity_location
             UNION ALL
             SELECT id, sensor_id, \'Thời tiết\' AS metric, weather_type AS value, \'\' AS unit, recorded_at
             FROM weather WHERE location = :weather_location
         ) AS measurements
         ORDER BY recorded_at DESC, id DESC
         LIMIT 200'
    );
    $statement->execute([
        ':temperature_location' => $user['address'],
        ':humidity_location' => $user['address'],
        ':weather_location' => $user['address'],
    ]);
    $records = $statement->fetchAll();
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $errorMessage = 'Không thể tải dữ liệu. Hãy import database.sql và kiểm tra kết nối database.';
}

require __DIR__ . '/view/site_header.php';
?>
    <section class="page-heading">
        <div>
            <p class="eyebrow">LỊCH SỬ CẢM BIẾN</p>
            <h1>Dữ liệu đã ghi nhận</h1>
            <p class="heading-subtitle">Các bản ghi tại <?= escapeHtml($user['address']) ?>, mới nhất trước.</p>
        </div>
        <a class="button button-primary" href="control_pannel.php">Thêm số đo</a>
    </section>

    <?php if ($errorMessage !== ''): ?>
        <div class="notice notice-error" role="alert"><?= escapeHtml($errorMessage) ?></div>
    <?php else: ?>
        <section class="content-card history-card">
            <?php if ($records === []): ?>
                <div class="empty-state">
                    <strong>Chưa có dữ liệu cũ</strong>
                    <p>Các số đo được thêm từ trang điều khiển sẽ xuất hiện tại đây.</p>
                    <a href="control_pannel.php">Đi tới điều khiển cảm biến</a>
                </div>
            <?php else: ?>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Thời điểm</th>
                                <th>Loại dữ liệu</th>
                                <th>Giá trị</th>
                                <th>Mã cảm biến</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <?php
                                $displayValue = $record['metric'] === 'Thời tiết'
                                    ? ucfirst($record['value'])
                                    : $record['value'] . ' ' . $record['unit'];
                                ?>
                                <tr>
                                    <td><?= escapeHtml($record['recorded_at']) ?></td>
                                    <td><?= escapeHtml($record['metric']) ?></td>
                                    <td><strong><?= escapeHtml((string) $displayValue) ?></strong></td>
                                    <td><?= escapeHtml($record['sensor_id']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="table-note">Hiển thị tối đa 200 bản ghi gần nhất.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
<?php require __DIR__ . '/view/site_footer.php'; ?>
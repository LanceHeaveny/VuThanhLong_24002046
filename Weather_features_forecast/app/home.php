<?php
require_once __DIR__ . '/common/functions.php';

$pageTitle = 'Trang chủ';
$user = $_SESSION['user'] ?? null;
require __DIR__ . '/view/site_header.php';
?>
    <section class="home-hero">
        <p class="eyebrow">WEATHERBOARD</p>
        <?php if (is_array($user)): ?>
            <h1>Xin chào, <?= escapeHtml($user['username']) ?>!</h1>
            <p class="hero-copy">Theo dõi các chỉ số nhiệt độ và độ ẩm tại vị trí của bạn.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="control_pannel.php">Mở bảng điều khiển</a>
                <a class="button button-secondary" href="old_data.php">Xem dữ liệu cũ</a>
                <a class="button button-secondary" href="dashboard_display.HTML">Xem dashboard</a>
            </div>
            <div class="profile-card">
                <span class="profile-avatar"><?= escapeHtml(strtoupper(substr($user['username'], 0, 1))) ?></span>
                <span><strong><?= escapeHtml($user['username']) ?></strong><small>Vị trí: <?= escapeHtml($user['address']) ?></small></span>
            </div>
        <?php else: ?>
            <h1>Thời tiết rõ ràng, mỗi ngày.</h1>
            <p class="hero-copy">Đăng nhập để quản lý cảm biến, cập nhật số đo và xem lịch sử thời tiết theo vị trí của bạn.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="login.php">Đăng nhập</a>
                <a class="button button-secondary" href="register.php">Tạo tài khoản</a>
            </div>
            <div class="home-features">
                <article><span aria-hidden="true">🌡</span><strong>Nhiệt độ</strong><p>Ghi nhận số đo và theo dõi thay đổi.</p></article>
                <article><span aria-hidden="true">◉</span><strong>Độ ẩm</strong><p>Cập nhật độ ẩm tại vị trí cảm biến.</p></article>
                <article><span aria-hidden="true">◷</span><strong>Lịch sử</strong><p>Xem lại các bản ghi đã lưu.</p></article>
            </div>
        <?php endif; ?>
    </section>
<?php require __DIR__ . '/view/site_footer.php'; ?>
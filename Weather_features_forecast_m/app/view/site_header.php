<?php
$pageTitle = $pageTitle ?? 'Weather Forecast';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$flashMessage = consumeFlashMessage();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#075878">
    <title><?= escapeHtml($pageTitle) ?> | Weather Forecast</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="site-page">
    <header class="site-header">
        <a class="brand" href="home.php" aria-label="Weather Forecast - Trang chủ">
            <span class="brand-mark" aria-hidden="true">☼</span>
            <span class="brand-name">Weather<span>Board</span></span>
        </a>
        <nav class="site-nav" aria-label="Điều hướng">
            <a class="<?= $currentPage === 'home.php' ? 'active' : '' ?>" href="home.php">Trang chủ</a>
            <a class="<?= $currentPage === 'forecast.php' ? 'active' : '' ?>" href="forecast.php">Dự báo</a>
            <?php if (isset($_SESSION['user']['address'])): ?>
                <a href="dashboard_display.HTML">Dashboard thời tiết</a>
                <a class="<?= $currentPage === 'old_data.php' ? 'active' : '' ?>" href="old_data.php">Lịch sử</a>
                <form action="logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                    <button class="site-nav-button" type="submit">Đăng xuất</button>
                </form>
            <?php else: ?>
                <a class="<?= $currentPage === 'login.php' ? 'active' : '' ?>" href="login.php">Đăng nhập</a>
                <a class="site-register-link" href="register.php">Tạo tài khoản</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="site-main">
        <?php if ($flashMessage !== null): ?>
            <div class="notice notice-<?= escapeHtml($flashMessage['type']) ?>" role="status">
                <?= escapeHtml($flashMessage['message']) ?>
            </div>
        <?php endif; ?>

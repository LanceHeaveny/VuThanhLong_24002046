<?php
require_once __DIR__ . '/../common/functions.php';

$username = $_SESSION['user']['username'] ?? $_SESSION['username'] ?? 'Người dùng';
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
$avatarInitial = htmlspecialchars(strtoupper(substr($username, 0, 1)), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#075878">
    <title>Weather Forecast | Theo dõi thời tiết</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="weather-app">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="home.php" aria-label="Weather Forecast - Trang chủ">
                <span class="brand-mark" aria-hidden="true">☼</span>
                <span class="brand-name">Weather<span>Board</span></span>
            </a>

            <nav class="side-nav" aria-label="Điều hướng chính">
                <a class="nav-link" href="home.php"><span class="nav-icon" aria-hidden="true">⌂</span><span>Trang chủ</span></a>
                <a class="nav-link active" href="dashboard_display.HTML" aria-current="page"><span class="nav-icon" aria-hidden="true">▦</span><span>Bảng điều khiển</span></a>
                <a class="nav-link" href="forecast.php"><span class="nav-icon" aria-hidden="true">☀</span><span>Dự báo thời tiết</span></a>
                <a class="nav-link" href="old_data.php"><span class="nav-icon" aria-hidden="true">◷</span><span>Lịch sử dữ liệu</span></a>
            </nav>

            <p class="side-section-title">VỊ TRÍ DỰ BÁO</p>
            <div class="location-list" aria-label="Các vị trí">
                <button class="location-link selected" type="button" data-location="hanoi" aria-pressed="true">
                    <span class="location-dot"></span><span>Hà Nội</span><span class="location-temp">25°</span>
                </button>
                <button class="location-link" type="button" data-location="danang" aria-pressed="false">
                    <span class="location-dot"></span><span>Đà Nẵng</span><span class="location-temp">29°</span>
                </button>
                <button class="location-link" type="button" data-location="hcm" aria-pressed="false">
                    <span class="location-dot"></span><span>TP. Hồ Chí Minh</span><span class="location-temp">31°</span>
                </button>
            </div>

            <div class="sidebar-bottom">
                <div class="sidebar-note">
                    <span class="note-icon" aria-hidden="true">✦</span>
                    <strong>Dữ liệu minh họa</strong>
                    <span>Số liệu sẽ được lấy từ database khi kết nối backend.</span>
                </div>
                <div class="user-profile">
                    <span class="avatar" aria-hidden="true"><?= $avatarInitial ?></span>
                    <span class="user-name"><?= $safeUsername ?><small>Người dùng</small></span>
                    <?php if (isset($_SESSION['user']['address'])): ?>
                        <form action="logout.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                            <button class="profile-menu logout-button" type="submit" aria-label="Đăng xuất">↗</button>
                        </form>
                    <?php else: ?>
                        <a class="profile-menu logout-button" href="login.php" aria-label="Đăng nhập">→</a>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <main class="main-content" id="overview">
            <header class="topbar">
                <button class="menu-button" id="menu-button" type="button" aria-label="Mở menu" aria-expanded="false">☰</button>
                <div class="breadcrumb"><span>Bảng điều khiển</span><span class="crumb-separator">›</span><strong>Thời tiết hiện tại</strong></div>
                <div class="topbar-actions">
                    <span class="connection-status"><i></i>Dữ liệu mẫu</span>
                    <button class="topbar-icon" type="button" aria-label="Thông báo">♧<i></i></button>
                    <span class="topbar-avatar" aria-hidden="true"><?= $avatarInitial ?></span>
                </div>
            </header>

            <div class="dashboard-content">
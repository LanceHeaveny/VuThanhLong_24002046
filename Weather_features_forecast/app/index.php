<?php
if (!isset($_GET['embedded'])) {
    header('Location: home.php');
    exit;
}

require_once __DIR__ . '/view/header.php';
?>
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">THEO DÕI THỜI TIẾT</p>
                        <h1>Tổng quan cảm biến</h1>
                        <p class="heading-subtitle">Nhiệt độ và độ ẩm theo vị trí dự báo.</p>
                    </div>
                    <label class="location-select-wrap" for="location-select">
                        <span class="select-pin" aria-hidden="true">⌖</span>
                        <select id="location-select" aria-label="Chọn vị trí dự báo">
                            <option value="hanoi">Hà Nội, Việt Nam</option>
                            <option value="danang">Đà Nẵng, Việt Nam</option>
                            <option value="hcm">TP. Hồ Chí Minh, Việt Nam</option>
                        </select>
                        <span class="select-chevron" aria-hidden="true">⌄</span>
                    </label>
                </section>

                <section class="dashboard-grid" aria-label="Dữ liệu thời tiết">
                    <div class="metrics-column">
                        <article class="metric-card temperature-card">
                            <div class="metric-heading">
                                <span class="metric-icon temperature-icon" aria-hidden="true">♨</span>
                                <span><strong>Nhiệt độ</strong><small>Cập nhật vừa xong</small></span>
                            </div>
                            <div class="metric-value temperature-value">25 <span>°C</span></div>
                            <div class="metric-caption"><span class="status-dot"></span>Nhiệt độ hiện tại</div>
                        </article>
                        <article class="metric-card humidity-card">
                            <div class="metric-heading">
                                <span class="metric-icon humidity-icon" aria-hidden="true">◉</span>
                                <span><strong>Độ ẩm</strong><small>Cập nhật vừa xong</small></span>
                            </div>
                            <div class="metric-value humidity-value">42 <span>%</span></div>
                            <div class="metric-caption"><span class="status-dot"></span>Độ ẩm hiện tại</div>
                        </article>
                    </div>

                    <article class="panel chart-panel" id="history">
                        <div class="panel-heading">
                            <div>
                                <h2>Lịch sử nhiệt độ và độ ẩm</h2>
                                <p class="chart-location">Hà Nội, Việt Nam · 24 giờ qua</p>
                            </div>
                            <button class="period-button" type="button" aria-label="Khoảng thời gian">24 giờ <span aria-hidden="true">⌄</span></button>
                        </div>
                        <div class="chart-legend" aria-label="Chú giải biểu đồ">
                            <span><i class="legend-temperature"></i>Nhiệt độ</span>
                            <span><i class="legend-humidity"></i>Độ ẩm</span>
                        </div>
                        <div class="chart-scroll">
                            <svg class="weather-chart" viewBox="0 0 820 360" role="img" aria-label="Biểu đồ lịch sử nhiệt độ và độ ẩm">
                                <g class="chart-grid">
                                    <line x1="62" y1="30" x2="770" y2="30"></line>
                                    <line x1="62" y1="78" x2="770" y2="78"></line>
                                    <line x1="62" y1="126" x2="770" y2="126"></line>
                                    <line x1="62" y1="174" x2="770" y2="174"></line>
                                    <line x1="62" y1="222" x2="770" y2="222"></line>
                                    <line x1="62" y1="270" x2="770" y2="270"></line>
                                    <line x1="62" y1="30" x2="62" y2="270"></line>
                                    <line x1="180" y1="30" x2="180" y2="270"></line>
                                    <line x1="298" y1="30" x2="298" y2="270"></line>
                                    <line x1="416" y1="30" x2="416" y2="270"></line>
                                    <line x1="534" y1="30" x2="534" y2="270"></line>
                                    <line x1="652" y1="30" x2="652" y2="270"></line>
                                    <line x1="770" y1="30" x2="770" y2="270"></line>
                                </g>
                                <g class="chart-y-labels">
                                    <text x="12" y="34">32°C</text><text x="12" y="82">29°C</text>
                                    <text x="12" y="130">26°C</text><text x="12" y="178">23°C</text>
                                    <text x="12" y="226">20°C</text><text x="12" y="274">17°C</text>
                                </g>
                                <g class="chart-right-labels">
                                    <text x="780" y="34">60%</text><text x="780" y="82">55%</text>
                                    <text x="780" y="130">50%</text><text x="780" y="178">45%</text>
                                    <text x="780" y="226">40%</text><text x="780" y="274">35%</text>
                                </g>
                                <text class="axis-title axis-left-title" x="14" y="16">Nhiệt độ</text>
                                <text class="axis-title axis-right-title" x="769" y="16">Độ ẩm</text>
                                <path class="temperature-line" id="temperature-line"></path>
                                <path class="humidity-line" id="humidity-line"></path>
                                <g class="chart-x-labels">
                                    <text x="62" y="300">00:00</text><text x="180" y="300">04:00</text>
                                    <text x="298" y="300">08:00</text><text x="416" y="300">12:00</text>
                                    <text x="534" y="300">16:00</text><text x="652" y="300">20:00</text>
                                    <text x="770" y="300">23:00</text>
                                </g>
                            </svg>
                        </div>
                        <div class="chart-footer">
                            <span><i class="legend-temperature"></i>Nhiệt độ: <strong class="latest-temperature">25°C</strong></span>
                            <span><i class="legend-humidity"></i>Độ ẩm: <strong class="latest-humidity">42%</strong></span>
                        </div>
                    </article>
                </section>

<?php require_once __DIR__ . '/view/footer.php'; ?>

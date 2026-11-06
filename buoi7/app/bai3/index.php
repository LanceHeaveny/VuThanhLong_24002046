<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 3 - Giỏ hàng</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main id="app" class="container">
        <a class="back-link" href="../index.php">&larr; Trang chủ</a>
        <header class="page-header">
            <div>
                <p class="eyebrow">Vue 3 · Bài thực hành 3</p>
                <h1>Giỏ hàng</h1>
                <p class="muted">Component con gửi sự kiện, component cha quản lý dữ liệu.</p>
            </div>
            <span class="count-badge">{{ tongSoLuong }} sản phẩm</span>
        </header>

        <p v-if="loiCapNhat" class="message message-error" role="alert">{{ loiCapNhat }}</p>
        <section v-if="gioHang.length > 0" class="cart-layout">
            <div class="cart-items">
                <dong-gio-hang
                    v-for="sanPham in gioHang"
                    :key="sanPham.id"
                    :san-pham="sanPham"
                    @doi-so-luong="doiSoLuong"
                    @yeu-cau-xoa="xoaSanPham">
                </dong-gio-hang>
            </div>
            <aside class="summary-card">
                <h2>Tóm tắt đơn hàng</h2>
                <p><span>Tổng số lượng</span><strong>{{ tongSoLuong }}</strong></p>
                <p><span>Tổng tiền</span><strong class="total-price">{{ dinhDangTien(tongTien) }}</strong></p>
                <p class="summary-note">Số lượng mỗi sản phẩm từ 1 đến 99.</p>
            </aside>
        </section>
        <section v-else class="empty-cart">
            <div class="empty-icon" aria-hidden="true">🛒</div>
            <h2>Giỏ hàng trống</h2>
            <p class="muted">Hãy thêm sản phẩm vào giỏ để tiếp tục.</p>
        </section>
    </main>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="components/DongGioHang.js"></script>
    <script src="app.js"></script>
</body>
</html>

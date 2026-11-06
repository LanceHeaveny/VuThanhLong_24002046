<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 2 - Danh mục sản phẩm</title>
    <link rel="stylesheet" href="style.css?v=4">
</head>
<body>
    <main id="app" class="container">
        <a class="back-link" href="../index.php">&larr; Trang chủ</a>
        <header class="page-header">
            <div>
                <p class="eyebrow">Vue 3 · Bài thực hành 2</p>
                <h1>Trang sức</h1>
                <p class="muted">Khám phá bộ sưu tập dây chuyền, nhẫn và khuyên tai.</p>
            </div>
            <span class="count-badge">{{ danhSachSanPham.length }} sản phẩm</span>
        </header>

        <p v-if="thongBao" class="message" role="status">{{ thongBao }}</p>
        <section
            v-for="nhom in nhomSanPham"
            :key="nhom.danhMuc"
            class="category-section"
            :aria-label="nhom.danhMuc">
            <div class="category-heading">
                <h2>{{ nhom.danhMuc }}</h2>
                <span>{{ nhom.sanPham.length }} sản phẩm</span>
            </div>
            <div class="product-list">
                <the-san-pham
                    v-for="sanPham in nhom.sanPham"
                    :key="sanPham.ten"
                    :ten="sanPham.ten"
                    :gia="sanPham.gia"
                    :anh="sanPham.anh"
                    :mo-ta="sanPham.moTa"
                    :so-luong-ton="sanPham.soLuongTon"
                    @mua-san-pham="muaSanPham">
                </the-san-pham>
            </div>
        </section>
    </main>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="components/TheSanPham.js"></script>
    <script src="app.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 1 - Quản lý sản phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main id="app" class="container">
        <a class="back-link" href="../index.php">&larr; Trang chủ</a>
        <header class="page-header">
            <div>
                <p class="eyebrow">Vue 3 · Bài thực hành 1</p>
                <h1>Quản lý sản phẩm</h1>
                <p class="muted">Tìm kiếm, lọc, sắp xếp và theo dõi tồn kho.</p>
            </div>
            <span class="ready-badge">{{ trangThai }}</span>
        </header>

        <section class="panel">
            <h2>Thêm sản phẩm</h2>
            <form class="product-form" @submit.prevent="themSanPham">
                <label>
                    Tên sản phẩm
                    <input v-model.trim="form.ten" type="text" placeholder="Ví dụ: Bàn phím" required>
                </label>
                <label>
                    Giá (đồng)
                    <input v-model.number="form.gia" type="number" min="1" step="1" placeholder="250000" required>
                </label>
                <label>
                    Danh mục
                    <input v-model.trim="form.danhMuc" type="text" placeholder="Phụ kiện" required>
                </label>
                <label>
                    Số lượng tồn
                    <input v-model.number="form.soLuongTon" type="number" min="0" step="1" placeholder="10" required>
                </label>
                <button class="button button-primary form-button" type="submit">Thêm sản phẩm</button>
            </form>
            <p v-if="loiForm" class="message message-error" role="alert">{{ loiForm }}</p>
        </section>

        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2>Danh sách sản phẩm</h2>
                    <p class="muted">Tìm kiếm, lọc và sắp xếp có thể kết hợp.</p>
                </div>
                <span class="count-badge">{{ sanPhamDangHienThi.length }} / {{ danhSachSanPham.length }}</span>
            </div>

            <div class="filters">
                <label class="search-field">
                    Tìm theo tên
                    <input v-model.trim="tuKhoa" type="search" placeholder="Nhập tên sản phẩm...">
                </label>
                <label>
                    Danh mục
                    <select v-model="danhMucDangChon">
                        <option value="">Tất cả danh mục</option>
                        <option v-for="danhMuc in danhSachDanhMuc" :key="danhMuc" :value="danhMuc">
                            {{ danhMuc }}
                        </option>
                    </select>
                </label>
                <label>
                    Sắp xếp theo giá
                    <select v-model="kieuSapXep">
                        <option value="mac-dinh">Mặc định</option>
                        <option value="tang">Tăng dần</option>
                        <option value="giam">Giảm dần</option>
                    </select>
                </label>
            </div>
            <p v-if="thongBao" class="message message-info" role="status">{{ thongBao }}</p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th class="number-cell">Giá</th>
                            <th class="number-cell">Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sanPham in sanPhamDangHienThi" :key="sanPham.id">
                            <td>{{ sanPham.id }}</td>
                            <td class="product-name">{{ sanPham.ten }}</td>
                            <td>{{ sanPham.danhMuc }}</td>
                            <td class="number-cell">{{ dinhDangTien(sanPham.gia) }}</td>
                            <td class="number-cell">{{ sanPham.soLuongTon }}</td>
                            <td>
                                <span :class="['stock-badge', sanPham.soLuongTon > 0 ? 'in-stock' : 'out-of-stock']">
                                    {{ sanPham.soLuongTon > 0 ? 'Còn hàng' : 'Hết hàng' }}
                                </span>
                            </td>
                            <td>
                                <button class="button button-danger button-small" type="button" @click="xoaSanPham(sanPham.id)">
                                    Xóa
                                </button>
                            </td>
                        </tr>
                        <tr v-if="sanPhamDangHienThi.length === 0">
                            <td class="empty-state" colspan="7">Không tìm thấy sản phẩm phù hợp.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="summary">
                <p>Số sản phẩm đang hiển thị: <strong>{{ sanPhamDangHienThi.length }} / {{ danhSachSanPham.length }}</strong></p>
                <p>Tổng giá trị tồn kho đang hiển thị: <strong>{{ dinhDangTien(tongGiaTriTonKho) }}</strong></p>
            </div>
        </section>
    </main>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="app.js"></script>
</body>
</html>

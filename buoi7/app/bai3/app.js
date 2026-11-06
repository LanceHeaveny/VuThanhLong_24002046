const { createApp } = Vue;

createApp({
    data() {
        return {
            loiCapNhat: '',
            gioHang: [
                { id: 1, ten: 'Bút bi', donGia: 5000, soLuong: 2 },
                { id: 2, ten: 'Quyển vở', donGia: 12000, soLuong: 3 }
            ]
        };
    },
    computed: {
        tongSoLuong() {
            return this.gioHang.reduce((tong, sanPham) => tong + sanPham.soLuong, 0);
        },
        tongTien() {
            return this.gioHang.reduce(
                (tong, sanPham) => tong + sanPham.donGia * sanPham.soLuong,
                0
            );
        }
    },
    methods: {
        dinhDangTien(giaTri) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND',
                maximumFractionDigits: 0
            }).format(giaTri);
        },
        doiSoLuong(id, soLuongMoi) {
            const sanPham = this.gioHang.find((item) => item.id === id);
            if (!sanPham || !Number.isInteger(soLuongMoi) || soLuongMoi < 1 || soLuongMoi > 99) {
                this.loiCapNhat = 'Không thể cập nhật: số lượng phải là số nguyên từ 1 đến 99.';
                return;
            }
            sanPham.soLuong = soLuongMoi;
            this.loiCapNhat = '';
        },
        xoaSanPham(id) {
            const coSanPham = this.gioHang.some((sanPham) => sanPham.id === id);
            if (!coSanPham) {
                this.loiCapNhat = 'Không tìm thấy sản phẩm cần xóa.';
                return;
            }
            this.gioHang = this.gioHang.filter((sanPham) => sanPham.id !== id);
            this.loiCapNhat = '';
        }
    }
})
    .component('dong-gio-hang', DongGioHang)
    .mount('#app');

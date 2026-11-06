const { createApp } = Vue;

createApp({
    data() {
        return {
            trangThai: 'Đang khởi tạo...',
            thongBao: '',
            loiForm: '',
            tuKhoa: '',
            danhMucDangChon: '',
            kieuSapXep: 'mac-dinh',
            form: {
                ten: '',
                gia: null,
                danhMuc: '',
                soLuongTon: 0
            },
            danhSachSanPham: [
                { id: 1, ten: 'Bàn phím cơ', gia: 850000, danhMuc: 'Phụ kiện', soLuongTon: 12 },
                { id: 2, ten: 'Chuột không dây', gia: 420000, danhMuc: 'Phụ kiện', soLuongTon: 0 },
                { id: 3, ten: 'Màn hình 24 inch', gia: 3290000, danhMuc: 'Màn hình', soLuongTon: 7 },
                { id: 4, ten: 'Màn hình 27 inch', gia: 5190000, danhMuc: 'Màn hình', soLuongTon: 3 },
                { id: 5, ten: 'Laptop văn phòng', gia: 15990000, danhMuc: 'Máy tính', soLuongTon: 5 },
                { id: 6, ten: 'Laptop gaming', gia: 28990000, danhMuc: 'Máy tính', soLuongTon: 0 }
            ]
        };
    },
    computed: {
        danhSachDanhMuc() {
            return [...new Set(this.danhSachSanPham.map((sanPham) => sanPham.danhMuc))].sort();
        },
        sanPhamDangHienThi() {
            const tuKhoaChuanHoa = this.tuKhoa.trim().toLocaleLowerCase('vi');
            const ketQua = this.danhSachSanPham.filter((sanPham) => {
                const khopTen = sanPham.ten.toLocaleLowerCase('vi').includes(tuKhoaChuanHoa);
                const khopDanhMuc = !this.danhMucDangChon || sanPham.danhMuc === this.danhMucDangChon;
                return khopTen && khopDanhMuc;
            });

            if (this.kieuSapXep === 'tang') {
                return [...ketQua].sort((a, b) => a.gia - b.gia);
            }
            if (this.kieuSapXep === 'giam') {
                return [...ketQua].sort((a, b) => b.gia - a.gia);
            }
            return ketQua;
        },
        tongGiaTriTonKho() {
            return this.sanPhamDangHienThi.reduce(
                (tong, sanPham) => tong + sanPham.gia * sanPham.soLuongTon,
                0
            );
        }
    },
    watch: {
        tuKhoa(giaTriMoi) {
            this.thongBao = giaTriMoi.trim().length === 1
                ? 'Từ khóa có một ký tự; hãy nhập thêm để tìm kiếm chính xác hơn.'
                : '';
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
        themSanPham() {
            const ten = this.form.ten.trim();
            const danhMuc = this.form.danhMuc.trim();
            const giaHopLe = Number.isFinite(this.form.gia) && this.form.gia > 0;
            const tonKhoHopLe = Number.isInteger(this.form.soLuongTon) && this.form.soLuongTon >= 0;

            if (!ten || !danhMuc || !giaHopLe || !tonKhoHopLe) {
                this.loiForm = 'Vui lòng nhập tên và danh mục, giá lớn hơn 0, tồn kho là số nguyên không âm.';
                return;
            }

            const idMoi = this.danhSachSanPham.reduce((maxId, sanPham) => Math.max(maxId, sanPham.id), 0) + 1;
            this.danhSachSanPham.push({
                id: idMoi,
                ten,
                gia: this.form.gia,
                danhMuc,
                soLuongTon: this.form.soLuongTon
            });
            this.form = { ten: '', gia: null, danhMuc: '', soLuongTon: 0 };
            this.loiForm = '';
        },
        xoaSanPham(id) {
            const sanPham = this.danhSachSanPham.find((item) => item.id === id);
            if (!sanPham || !window.confirm(`Bạn có chắc muốn xóa "${sanPham.ten}" không?`)) {
                return;
            }
            this.danhSachSanPham = this.danhSachSanPham.filter((item) => item.id !== id);
        }
    },
    mounted() {
        this.trangThai = 'Sẵn sàng';
    }
}).mount('#app');

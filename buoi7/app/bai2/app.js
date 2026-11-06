const { createApp } = Vue;

createApp({
    data() {
        return {
            thongBao: '',
            danhSachSanPham: [
                {
                    ten: 'Dây chuyền thạch anh đỏ',
                    danhMuc: 'Dây chuyền',
                    gia: 4250000,
                    anh: 'https://i.pinimg.com/1200x/ad/cd/5c/adcd5ccf7a3018c49b4b0abb1e6a57c4.jpg',
                    moTa: 'Dây chuyền thanh lịch điểm xuyết viên thạch anh đỏ nổi bật, thích hợp làm quà tặng.',
                    soLuongTon: 8
                },
                {
                    ten: 'Dây chuyền thạch anh tím',
                    danhMuc: 'Dây chuyền',
                    gia: 2480000,
                    anh: 'https://i.pinimg.com/736x/f2/96/e9/f296e90bd49f270f026f86aee7987995.jpg',
                    moTa: 'Mặt dây thạch anh tím tạo điểm nhấn dịu dàng, dễ phối cùng trang phục hằng ngày.',
                    soLuongTon: 5
                },
                {
                    ten: 'Dây chuyền vàng',
                    danhMuc: 'Dây chuyền',
                    gia: 12900000,
                    anh: 'https://i.pinimg.com/736x/35/02/46/35024672df940935756b9c41fe61199f.jpg',
                    moTa: 'Dây chuyền sắc vàng cổ điển với kiểu dáng tinh tế, phù hợp nhiều dịp.',
                    soLuongTon: 3
                },
                {
                    ten: 'Nhẫn ngọc lục bảo',
                    danhMuc: 'Nhẫn',
                    gia: 20200000,
                    anh: 'https://i.pinimg.com/736x/5e/6f/cf/5e6fcf08a34418cd43b3355ffbe9cf42.jpg',
                    moTa: 'Viên đá xanh lục làm điểm nhấn cho thiết kế nhẫn thanh lịch và sang trọng.',
                    soLuongTon: 2
                },
                {
                    ten: 'Nhẫn vàng',
                    danhMuc: 'Nhẫn',
                    gia: 5460000,
                    anh: 'https://i.pinimg.com/736x/30/bd/e7/30bde7d94ab323c14e5114ab65e9f663.jpg',
                    moTa: 'Nhẫn vàng kiểu dáng tối giản, dễ kết hợp khi đeo riêng hoặc phối cùng nhẫn khác.',
                    soLuongTon: 6
                },
                {
                    ten: 'Nhẫn kim cương',
                    danhMuc: 'Nhẫn',
                    gia: 28000000,
                    anh: 'https://i.pinimg.com/736x/4f/bd/f3/4fbdf318f5b6bba4951fbbdb2d9af956.jpg',
                    moTa: 'Thiết kế đính đá lấp lánh, tạo điểm nhấn nổi bật cho những dịp đặc biệt.',
                    soLuongTon: 0
                },
                {
                    ten: 'Khuyên tai vàng hình lá phong',
                    danhMuc: 'Khuyên tai',
                    gia: 3850000,
                    anh: 'https://i.pinimg.com/736x/14/c6/2b/14c62b99a44f7d8b1afc698b9387f96e.jpg',
                    moTa: 'Đôi khuyên tai vàng tạo hình lá phong nhỏ nhắn, mang nét duyên dáng và độc đáo.',
                    soLuongTon: 7
                },
                {
                    ten: 'Khuyên tai ngọc lam',
                    danhMuc: 'Khuyên tai',
                    gia: 2150000,
                    anh: 'https://i.pinimg.com/1200x/07/c5/5d/07c55d2bb3b27cbe34134d9cce020473.jpg',
                    moTa: 'Sắc xanh ngọc lam tươi sáng giúp bộ trang sức thêm nổi bật.',
                    soLuongTon: 4
                },
                {
                    ten: 'Khuyên tai hoa hồng',
                    danhMuc: 'Khuyên tai',
                    gia: 1690000,
                    anh: 'https://i.pinimg.com/736x/b5/0b/2d/b50b2d867600b7f8c03fc2525e472215.jpg',
                    moTa: 'Thiết kế lấy cảm hứng từ hoa hồng, nhẹ nhàng và nữ tính.',
                    soLuongTon: 0
                }
            ]
        };
    },
    computed: {
        nhomSanPham() {
            return ['Dây chuyền', 'Nhẫn', 'Khuyên tai'].map((danhMuc) => ({
                danhMuc,
                sanPham: this.danhSachSanPham.filter((item) => item.danhMuc === danhMuc)
            }));
        }
    },
    methods: {
        muaSanPham(tenSanPham) {
            const sanPham = this.danhSachSanPham.find((item) => item.ten === tenSanPham);
            if (!sanPham || sanPham.soLuongTon <= 0) {
                this.thongBao = 'Sản phẩm hiện đã hết hàng.';
                return;
            }
            sanPham.soLuongTon -= 1;
            this.thongBao = `Đã mua 1 sản phẩm "${sanPham.ten}". Còn lại ${sanPham.soLuongTon} sản phẩm.`;
        }
    }
})
    .component('the-san-pham', TheSanPham)
    .mount('#app');

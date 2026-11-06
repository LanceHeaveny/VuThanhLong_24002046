const DongGioHang = {
    props: {
        sanPham: {
            type: Object,
            required: true,
            validator(sanPham) {
                return Number.isInteger(sanPham.id)
                    && typeof sanPham.ten === 'string'
                    && Number.isFinite(sanPham.donGia)
                    && Number.isInteger(sanPham.soLuong);
            }
        }
    },
    emits: {
        'doi-so-luong': (id, soLuongMoi) => Number.isInteger(id) && Number.isInteger(soLuongMoi),
        'yeu-cau-xoa': (id) => Number.isInteger(id)
    },
    methods: {
        dinhDangTien(giaTri) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND',
                maximumFractionDigits: 0
            }).format(giaTri);
        }
    },
    template: `
        <article class="cart-item">
            <div class="item-info">
                <h2>{{ sanPham.ten }}</h2>
                <p>{{ dinhDangTien(sanPham.donGia) }} / sản phẩm</p>
                <strong class="item-total">{{ dinhDangTien(sanPham.donGia * sanPham.soLuong) }}</strong>
            </div>
            <div class="item-actions">
                <div class="quantity-control" :aria-label="'Số lượng ' + sanPham.ten">
                    <button
                        type="button"
                        :disabled="sanPham.soLuong <= 1"
                        :aria-label="'Giảm số lượng ' + sanPham.ten"
                        @click="$emit('doi-so-luong', sanPham.id, sanPham.soLuong - 1)">
                        &minus;
                    </button>
                    <span>{{ sanPham.soLuong }}</span>
                    <button
                        type="button"
                        :disabled="sanPham.soLuong >= 99"
                        :aria-label="'Tăng số lượng ' + sanPham.ten"
                        @click="$emit('doi-so-luong', sanPham.id, sanPham.soLuong + 1)">
                        +
                    </button>
                </div>
                <button class="remove-button" type="button" @click="$emit('yeu-cau-xoa', sanPham.id)">
                    Xóa
                </button>
            </div>
        </article>
    `
};

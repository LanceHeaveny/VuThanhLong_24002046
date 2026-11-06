const TheSanPham = {
    props: {
        ten: { type: String, required: true },
        gia: { type: Number, required: true },
        anh: { type: String, default: '' },
        moTa: { type: String, default: 'Chưa có mô tả cho sản phẩm này.' },
        soLuongTon: { type: Number, required: true }
    },
    emits: ['mua-san-pham'],
    data() {
        return {
            hienMoTa: false
        };
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
        <article class="product-card">
            <button
                class="image-wrap image-trigger"
                type="button"
                :aria-expanded="hienMoTa"
                :aria-label="hienMoTa ? 'Ẩn mô tả ' + ten : 'Xem mô tả ' + ten"
                @click="hienMoTa = !hienMoTa">
                <img v-if="anh" :src="anh" :alt="ten" loading="lazy">
                <span v-else class="image-placeholder">Sản phẩm</span>
                <span :class="['stock-badge', soLuongTon > 0 ? 'in-stock' : 'out-of-stock']">
                    {{ soLuongTon > 0 ? 'Còn hàng' : 'Hết hàng' }}
                </span>
                <span class="image-hint">{{ hienMoTa ? 'Chạm vào ảnh để ẩn mô tả' : 'Chạm vào ảnh để xem mô tả' }}</span>
            </button>
            <div class="product-content">
                <h2>{{ ten }}</h2>
                <p class="price">{{ dinhDangTien(gia) }}</p>
                <p class="quantity">Còn {{ soLuongTon }} sản phẩm</p>
                <p v-if="hienMoTa" class="description">{{ moTa }}</p>
                <button
                    class="buy-button"
                    type="button"
                    :disabled="soLuongTon === 0"
                    @click="$emit('mua-san-pham', ten)">
                    {{ soLuongTon === 0 ? 'Tạm hết hàng' : 'Mua' }}
                </button>
            </div>
        </article>
    `
};

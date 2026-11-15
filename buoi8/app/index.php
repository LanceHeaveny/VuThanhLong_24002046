<?php
// Nhúng header từ thư mục view (lùi 1 cấp: ../view/header.php)
// Lưu ý: Bạn cần điều chỉnh lại đường dẫn trong header.php nếu nó dùng chung
include_once __DIR__ . "/view/header.php";
?>

<div class="container">
    <h2 class="title">{{ editId ? 'Cập Nhật Sản Phẩm' : 'Thêm Sản Phẩm Mới' }}</h2>
    
    <!-- Biểu mẫu thêm / sửa sản phẩm -->
    <form @submit.prevent="saveProduct" class="product-form">
        <div class="form-group">
            <label>Tên sản phẩm:</label>
            <input type="text" v-model="form.name" placeholder="Nhập tên sản phẩm..." required>
        </div>
        <div class="form-group">
            <label>Đơn giá (đồng):</label>
            <input type="number" v-model.number="form.price" placeholder="Nhập đơn giá..." required min="1">
        </div>
        <div class="form-group">
            <label>Số lượng tồn:</label>
            <input type="number" v-model.number="form.stock" placeholder="Nhập số lượng tồn..." required min="0">
        </div>
        <div class="button-group">
            <button type="submit" class="btn btn-primary">{{ editId ? 'Lưu (Cập nhật)' : 'Lưu (Thêm mới)' }}</button>
            <button type="button" class="btn btn-secondary" v-if="editId" @click="resetForm">Hủy</button>
        </div>
    </form>

    <!-- Thông báo lỗi / thành công -->
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

    <hr class="divider">

    <h2 class="title">Danh Sách Sản Phẩm</h2>
    
    <!-- Bảng danh sách sản phẩm -->
    <table class="product-table">
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên sản phẩm</th>
                <th>Đơn giá (đồng)</th>
                <th>Số lượng tồn</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in products" :key="item.id">
                <td style="text-align: center;">{{ index + 1 }}</td>
                <td><strong>{{ item.name }}</strong></td>
                <td>{{ Number(item.price).toLocaleString('vi-VN') }} đ</td>
                <td style="text-align: center;">
                    <span :class="{'out-of-stock': item.stock == 0}">{{ item.stock }}</span>
                </td>
                <td style="text-align: center;">
                    <button class="btn btn-edit" @click="editProduct(item)">Sửa</button>
                    <button class="btn btn-delete" @click="deleteProduct(item.id)">Xóa</button>
                </td>
            </tr>
            <tr v-if="products.length === 0">
                <td colspan="5" style="text-align: center;">Chưa có sản phẩm nào trong cơ sở dữ liệu.</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    const { createApp, ref, onMounted } = Vue;

    createApp({
        setup() {
            const products = ref([]);
            const form = ref({ name: '', price: '', stock: '' });
            const editId = ref(null);
            const errorMessage = ref('');
            const successMessage = ref('');

            // Đường dẫn API từ thư mục app/ là gọi trực tiếp đến api/products.php
            const apiUrl = 'api/products.php';

            const fetchProducts = async () => {
                try {
                    const res = await fetch(apiUrl);
                    const result = await res.json();
                    if (result.success) {
                        products.value = result.data;
                    } else {
                        errorMessage.value = result.message;
                    }
                } catch (err) {
                    errorMessage.value = 'Không thể kết nối đến API máy chủ.';
                }
            };

            const resetForm = () => {
                form.value = { name: '', price: '', stock: '' };
                editId.value = null;
            };

            const saveProduct = async () => {
                errorMessage.value = '';
                successMessage.value = '';

                const method = editId.value ? 'PUT' : 'POST';
                const url = editId.value ? `${apiUrl}?id=${editId.value}` : apiUrl;

                try {
                    const res = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(form.value)
                    });

                    const result = await res.json();

                    if (result.success) {
                        successMessage.value = result.message;
                        resetForm();
                        await fetchProducts();
                    } else {
                        errorMessage.value = result.message;
                    }
                } catch (err) {
                    errorMessage.value = 'Có lỗi xảy ra khi truyền nhận dữ liệu.';
                }
            };

            const editProduct = (item) => {
                editId.value = item.id;
                form.value = {
                    name: item.name,
                    price: item.price,
                    stock: item.stock
                };
                errorMessage.value = '';
                successMessage.value = '';
            };

            const deleteProduct = async (id) => {
                if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                    return;
                }

                errorMessage.value = '';
                successMessage.value = '';

                try {
                    const res = await fetch(`${apiUrl}?id=${id}`, {
                        method: 'DELETE'
                    });
                    const result = await res.json();

                    if (result.success) {
                        successMessage.value = result.message;
                        await fetchProducts();
                    } else {
                        errorMessage.value = result.message;
                    }
                } catch (err) {
                    errorMessage.value = 'Có lỗi xảy ra khi xóa sản phẩm.';
                }
            };

            onMounted(() => {
                fetchProducts();
            });

            return {
                products,
                form,
                editId,
                errorMessage,
                successMessage,
                saveProduct,
                editProduct,
                deleteProduct,
                resetForm
            };
        }
    }).mount('#app');
</script>

<?php
// Nhúng footer từ thư mục view
include_once __DIR__ . "/view/footer.php";
?>
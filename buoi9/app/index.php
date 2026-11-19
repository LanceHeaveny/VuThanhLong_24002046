<?php
// Nhúng header từ thư mục view (lùi 1 cấp: ../view/header.php)
include_once __DIR__ . "/view/header.php";
?>

<div class="container">
    <h2 class="title">{{ editId ? 'Cập Nhật Sản Phẩm' : 'Thêm Sản Phẩm Mới' }}</h2>

    <!-- Biểu mẫu thêm / sửa sản phẩm -->
    <form @submit.prevent="saveProduct" class="product-form">
        <div class="form-group">
            <label>Tên sản phẩm:</label>
            <input type="text" v-model.trim="form.name" placeholder="Nhập tên sản phẩm..." required>
        </div>
        <div class="form-group">
            <label>Đơn giá (đồng):</label>
            <input type="number" v-model.number="form.price" placeholder="Nhập đơn giá..." required min="1">
        </div>
        <div class="form-group">
            <label>Số lượng tồn:</label>
            <input type="number" v-model.number="form.stock" placeholder="Nhập số lượng tồn..." required min="0">
        </div>
        <div class="form-group">
            <label>Ảnh sản phẩm (JPG, JPEG, PNG — tối đa 2 MB, không bắt buộc):</label>
            <input
                ref="fileInput"
                type="file"
                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                @change="onFileChange"
            >

            <!-- Xem trước ảnh mới chọn -->
            <div v-if="previewUrl" style="margin-top: 8px;">
                <small>Xem trước ảnh mới:</small><br>
                <img
                    :src="previewUrl"
                    alt="Xem trước ảnh sản phẩm"
                    style="max-width: 150px; max-height: 150px; object-fit: cover; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px;"
                >
            </div>
            <!-- Hoặc hiển thị ảnh hiện tại khi đang sửa và chưa chọn ảnh mới -->
            <div v-else-if="editId && currentImage" style="margin-top: 8px;">
                <small>Ảnh hiện tại:</small><br>
                <img
                    :src="currentImage"
                    alt="Ảnh hiện tại"
                    style="max-width: 150px; max-height: 150px; object-fit: cover; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px;"
                >
            </div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary" :disabled="submitting">
                {{ submitting ? 'Đang lưu...' : (editId ? 'Lưu (Cập nhật)' : 'Lưu (Thêm mới)') }}
            </button>

            <!-- Cho phép thử upload lại nếu thông tin đã lưu nhưng upload ảnh thất bại -->
            <button
                v-if="pendingUploadProductId && selectedFile"
                type="button"
                class="btn btn-secondary"
                :disabled="submitting"
                @click="retryUpload"
            >
                Thử upload lại ảnh
            </button>

            <button
                type="button"
                class="btn btn-secondary"
                v-if="editId || pendingUploadProductId"
                @click="resetForm"
            >
                Hủy
            </button>
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
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Đơn giá (đồng)</th>
                <th>Số lượng tồn</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in products" :key="item.id">
                <td style="text-align: center;">{{ (pagination.page - 1) * pagination.limit + index + 1 }}</td>
                <td style="text-align: center;">
                    <img
                        v-if="item.image"
                        :src="item.image"
                        :alt="item.name"
                        style="width: 56px; height: 56px; object-fit: cover; border-radius: 4px;"
                    >
                    <span v-else>Chưa có ảnh</span>
                </td>
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
                <td colspan="6" style="text-align: center;">Chưa có sản phẩm</td>
            </tr>
        </tbody>
    </table>

    <!-- Thanh phân trang -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px; gap: 12px;">
        <button
            class="btn btn-secondary"
            :disabled="pagination.total === 0 || pagination.page <= 1"
            @click="fetchProducts(pagination.page - 1)"
        >
            Trước
        </button>

        <span>
            Trang {{ pagination.total === 0 ? 1 : pagination.page }} / {{ pagination.total === 0 ? 1 : pagination.total_pages }} — Tổng {{ pagination.total }} sản phẩm
        </span>

        <button
            class="btn btn-secondary"
            :disabled="pagination.total === 0 || pagination.page >= pagination.total_pages"
            @click="fetchProducts(pagination.page + 1)"
        >
            Sau
        </button>
    </div>
</div>

<script>
    const { createApp, ref, reactive, onMounted } = Vue;

    createApp({
        setup() {
            const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB
            const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png'];

            const products = ref([]);
            const pagination = reactive({
                page: 1,
                limit: 5,
                total: 0,
                total_pages: 1
            });

            const form = ref({ name: '', price: '', stock: '' });
            const editId = ref(null);
            const currentImage = ref(null);
            const selectedFile = ref(null);
            const previewUrl = ref(null);
            const pendingUploadProductId = ref(null);
            const fileInput = ref(null);

            const submitting = ref(false);
            const errorMessage = ref('');
            const successMessage = ref('');

            // Đường dẫn API từ thư mục app/
            const apiUrl = 'api/products.php';
            const uploadUrl = 'api/upload.php';

            const clearPreview = () => {
                if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
                    URL.revokeObjectURL(previewUrl.value);
                }
                previewUrl.value = null;
            };

            const resetForm = () => {
                form.value = { name: '', price: '', stock: '' };
                editId.value = null;
                currentImage.value = null;
                selectedFile.value = null;
                pendingUploadProductId.value = null;
                clearPreview();
                if (fileInput.value) {
                    fileInput.value.value = '';
                }
            };

            const validateImageFile = (file) => {
                if (!ALLOWED_MIME_TYPES.includes(file.type)) {
                    return 'Chỉ chấp nhận file ảnh JPG, JPEG hoặc PNG.';
                }
                if (file.size > MAX_FILE_SIZE) {
                    return 'Dung lượng ảnh không được vượt quá 2 MB.';
                }
                return '';
            };

            const onFileChange = (event) => {
                errorMessage.value = '';
                const file = event.target.files?.[0];
                if (!file) {
                    selectedFile.value = null;
                    clearPreview();
                    return;
                }

                const validationError = validateImageFile(file);
                if (validationError) {
                    errorMessage.value = validationError;
                    selectedFile.value = null;
                    clearPreview();
                    event.target.value = '';
                    return;
                }

                selectedFile.value = file;
                clearPreview();
                previewUrl.value = URL.createObjectURL(file);
            };

            const fetchProducts = async (targetPage = pagination.page) => {
                try {
                    const res = await fetch(`${apiUrl}?page=${targetPage}&limit=5`);
                    const result = await res.json();
                    if (result.success) {
                        products.value = result.data || [];
                        if (result.pagination) {
                            pagination.page = result.pagination.page;
                            pagination.limit = result.pagination.limit;
                            pagination.total = result.pagination.total;
                            pagination.total_pages = result.pagination.total_pages;
                        }
                    } else {
                        errorMessage.value = result.message || 'Không thể tải danh sách sản phẩm.';
                    }
                } catch (err) {
                    errorMessage.value = 'Không thể kết nối đến API máy chủ.';
                }
            };

            const uploadProductImage = async (productId, file) => {
                const formData = new FormData();
                formData.append('product_id', String(productId));
                formData.append('image', file);

                const res = await fetch(uploadUrl, {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (!res.ok || !result.success) {
                    throw new Error(result.message || 'Upload ảnh thất bại.');
                }
                return result;
            };

            const saveProduct = async () => {
                errorMessage.value = '';
                successMessage.value = '';

                if (selectedFile.value) {
                    const validationError = validateImageFile(selectedFile.value);
                    if (validationError) {
                        errorMessage.value = validationError;
                        return;
                    }
                }

                submitting.value = true;
                const isEditing = Boolean(editId.value);
                const currentEditId = editId.value;

                try {
                    const method = isEditing ? 'PUT' : 'POST';
                    const url = isEditing ? `${apiUrl}?id=${currentEditId}` : apiUrl;

                    const res = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(form.value)
                    });

                    const result = await res.json();

                    if (!result.success) {
                        errorMessage.value = result.message || 'Lưu sản phẩm thất bại.';
                        return;
                    }

                    const savedId = isEditing ? currentEditId : (result.id ?? result.data?.id);

                    if (selectedFile.value) {
                        try {
                            await uploadProductImage(savedId, selectedFile.value);
                            successMessage.value = isEditing
                                ? 'Cập nhật sản phẩm và ảnh thành công.'
                                : 'Thêm sản phẩm và ảnh thành công.';
                            resetForm();
                            await fetchProducts(isEditing ? pagination.page : 1);
                        } catch (uploadErr) {
                            // Giữ sản phẩm đã lưu, giữ file để thử lại mà không mất dữ liệu
                            pendingUploadProductId.value = savedId;
                            editId.value = savedId;
                            errorMessage.value = `Sản phẩm đã được lưu, nhưng upload ảnh lỗi: ${uploadErr.message}. Bạn có thể bấm "Thử upload lại ảnh".`;
                            await fetchProducts(isEditing ? pagination.page : 1);
                        }
                    } else {
                        successMessage.value = result.message || 'Lưu sản phẩm thành công.';
                        resetForm();
                        await fetchProducts(isEditing ? pagination.page : 1);
                    }
                } catch (err) {
                    errorMessage.value = 'Có lỗi xảy ra khi truyền nhận dữ liệu.';
                } finally {
                    submitting.value = false;
                }
            };

            const retryUpload = async () => {
                if (!pendingUploadProductId.value || !selectedFile.value) return;

                errorMessage.value = '';
                successMessage.value = '';
                submitting.value = true;

                try {
                    await uploadProductImage(pendingUploadProductId.value, selectedFile.value);
                    successMessage.value = 'Upload ảnh lại thành công.';
                    const targetPage = pagination.page;
                    resetForm();
                    await fetchProducts(targetPage);
                } catch (err) {
                    errorMessage.value = `Upload lại thất bại: ${err.message}`;
                } finally {
                    submitting.value = false;
                }
            };

            const editProduct = (item) => {
                editId.value = item.id;
                pendingUploadProductId.value = null;
                currentImage.value = item.image || null;
                form.value = {
                    name: item.name,
                    price: item.price,
                    stock: item.stock
                };
                selectedFile.value = null;
                clearPreview();
                if (fileInput.value) {
                    fileInput.value.value = '';
                }
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
                        await fetchProducts(pagination.page);
                    } else {
                        errorMessage.value = result.message;
                    }
                } catch (err) {
                    errorMessage.value = 'Có lỗi xảy ra khi xóa sản phẩm.';
                }
            };

            onMounted(() => {
                fetchProducts(1);
            });

            return {
                products,
                pagination,
                form,
                editId,
                currentImage,
                selectedFile,
                previewUrl,
                pendingUploadProductId,
                fileInput,
                submitting,
                errorMessage,
                successMessage,
                onFileChange,
                fetchProducts,
                saveProduct,
                retryUpload,
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
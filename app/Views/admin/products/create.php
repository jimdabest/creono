<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- CSS chuyên biệt cho trang Quản lý Sản phẩm -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/admin-products.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="admin-header mb-4">
        <nav class="breadcrumb mb-2">
            <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
            <a href="<?php echo URLROOT; ?>/adminProductController/index">Quản lý Sản phẩm</a> &nbsp;&rsaquo;&nbsp;
            <span class="text-muted">Thêm mới</span>
        </nav>
        <h1 class="admin-title">Thêm Sản phẩm mới</h1>
        <p class="admin-subtitle">Tạo sản phẩm mới và gán cho cửa hàng trên hệ thống</p>
    </div>

    <!-- Form thêm sản phẩm -->
    <div class="admin-card ap-form-card">
        <form action="<?php echo URLROOT; ?>/adminProductController/store" method="POST" class="ap-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- Tiêu đề -->
            <div class="ap-form-group <?php echo !empty($data['errors']['title_err']) ? 'ap-form-group--error' : ''; ?>">
                <label for="title" class="ap-form-label">Tiêu đề sản phẩm <span class="ap-required">*</span></label>
                <input type="text"
                       id="title"
                       name="title"
                       class="ap-form-input"
                       value="<?php echo htmlspecialchars($data['product_title']); ?>"
                       placeholder="VD: Source Code Quản lý Nhà hàng PHP"
                       required>
                <?php if (!empty($data['errors']['title_err'])) : ?>
                    <span class="ap-form-error"><?php echo $data['errors']['title_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Mô tả -->
            <div class="ap-form-group">
                <label for="description" class="ap-form-label">Mô tả sản phẩm</label>
                <textarea id="description"
                          name="description"
                          class="ap-form-textarea"
                          rows="4"
                          placeholder="Mô tả chi tiết về sản phẩm..."><?php echo htmlspecialchars($data['description']); ?></textarea>
            </div>

            <!-- 2 cột: Giá & Danh mục -->
            <div class="ap-form-row">
                <!-- Giá -->
                <div class="ap-form-group <?php echo !empty($data['errors']['price_err']) ? 'ap-form-group--error' : ''; ?>">
                    <label for="price" class="ap-form-label">Giá (VNĐ) <span class="ap-required">*</span></label>
                    <input type="number"
                           id="price"
                           name="price"
                           class="ap-form-input"
                           value="<?php echo htmlspecialchars((string) $data['price']); ?>"
                           placeholder="VD: 200000"
                           min="0"
                           step="1000"
                           required>
                    <?php if (!empty($data['errors']['price_err'])) : ?>
                        <span class="ap-form-error"><?php echo $data['errors']['price_err']; ?></span>
                    <?php endif; ?>
                </div>

                <!-- Danh mục -->
                <div class="ap-form-group">
                    <label for="category_id" class="ap-form-label">Danh mục</label>
                    <select id="category_id" name="category_id" class="ap-form-select">
                        <option value="">-- Chưa phân loại --</option>
                        <?php foreach ($data['categories'] as $cat) : ?>
                            <option value="<?php echo $cat->id; ?>"
                                <?php echo $data['category_id'] == $cat->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- 2 cột: Cửa hàng & Trạng thái -->
            <div class="ap-form-row">
                <!-- Cửa hàng -->
                <div class="ap-form-group <?php echo !empty($data['errors']['store_err']) ? 'ap-form-group--error' : ''; ?>">
                    <label for="store_id" class="ap-form-label">Cửa hàng <span class="ap-required">*</span></label>
                    <select id="store_id" name="store_id" class="ap-form-select" required>
                        <option value="">-- Chọn cửa hàng --</option>
                        <?php foreach ($data['stores'] as $store) : ?>
                            <option value="<?php echo $store->id; ?>"
                                <?php echo $data['store_id'] == $store->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($store->name); ?> (<?php echo htmlspecialchars($store->owner_name); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($data['errors']['store_err'])) : ?>
                        <span class="ap-form-error"><?php echo $data['errors']['store_err']; ?></span>
                    <?php endif; ?>
                </div>

                <!-- Trạng thái -->
                <div class="ap-form-group <?php echo !empty($data['errors']['status_err']) ? 'ap-form-group--error' : ''; ?>">
                    <label for="status" class="ap-form-label">Trạng thái</label>
                    <select id="status" name="status" class="ap-form-select">
                        <option value="1" <?php echo $data['status'] == 1 ? 'selected' : ''; ?>>Chờ duyệt (Pending)</option>
                        <option value="2" <?php echo $data['status'] == 2 ? 'selected' : ''; ?>>Đã duyệt (Approved)</option>
                        <option value="3" <?php echo $data['status'] == 3 ? 'selected' : ''; ?>>Từ chối (Rejected)</option>
                    </select>
                    <?php if (!empty($data['errors']['status_err'])) : ?>
                        <span class="ap-form-error"><?php echo $data['errors']['status_err']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nút hành động -->
            <div class="ap-form-actions">
                <a href="<?php echo URLROOT; ?>/adminProductController/index" class="btn btn-outline">Hủy bỏ</a>
                <button type="submit" class="btn btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                    Tạo sản phẩm
                </button>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

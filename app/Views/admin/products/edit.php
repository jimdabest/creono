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
            <span class="text-muted">Chỉnh sửa</span>
        </nav>
        <h1 class="admin-title">Chỉnh sửa Sản phẩm</h1>
        <p class="admin-subtitle">Cập nhật thông tin sản phẩm #<?php echo $data['product']->id; ?></p>
    </div>

    <!-- Form chỉnh sửa sản phẩm -->
    <div class="admin-card ap-form-card">
        <!-- Thông tin hiện tại -->
        <div class="ap-current-info mb-3">
            <div class="ap-info-badge">
                <span class="ap-info-label">ID:</span>
                <span class="ap-info-value">#<?php echo $data['product']->id; ?></span>
            </div>
            <div class="ap-info-badge">
                <span class="ap-info-label">Cửa hàng:</span>
                <span class="ap-info-value"><?php echo htmlspecialchars($data['product']->store_name); ?></span>
            </div>
            <div class="ap-info-badge">
                <span class="ap-info-label">Người bán:</span>
                <span class="ap-info-value"><?php echo htmlspecialchars($data['product']->seller_name); ?></span>
            </div>
            <div class="ap-info-badge">
                <span class="ap-info-label">Trạng thái:</span>
                <?php
                $statusLabels = [1 => '⏳ Chờ duyệt', 2 => '✅ Đã duyệt', 3 => '❌ Từ chối'];
                $statusClasses = [1 => 'text-warning', 2 => 'text-green', 3 => 'text-danger'];
                ?>
                <span class="ap-info-value <?php echo $statusClasses[$data['product']->status] ?? ''; ?>">
                    <?php echo $statusLabels[$data['product']->status] ?? 'N/A'; ?>
                </span>
            </div>
            <?php if (!empty($data['product']->ai_label_name)) : ?>
                <div class="ap-info-badge">
                    <span class="ap-info-label">AI Label:</span>
                    <span class="ap-info-value"><?php echo htmlspecialchars($data['product']->ai_label_name); ?>
                        (<?php echo number_format((float) ($data['product']->ai_score ?? 0), 1); ?>%)
                    </span>
                </div>
            <?php endif; ?>
            <div class="ap-info-badge">
                <span class="ap-info-label">Ngày tạo:</span>
                <span class="ap-info-value"><?php echo date('d/m/Y H:i', strtotime($data['product']->created_at)); ?></span>
            </div>
        </div>

        <form action="<?php echo URLROOT; ?>/adminProductController/update/<?php echo $data['product']->id; ?>" method="POST" class="ap-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- Tiêu đề -->
            <div class="ap-form-group <?php echo !empty($data['errors']['title_err']) ? 'ap-form-group--error' : ''; ?>">
                <label for="title" class="ap-form-label">Tiêu đề sản phẩm <span class="ap-required">*</span></label>
                <input type="text"
                       id="title"
                       name="title"
                       class="ap-form-input"
                       value="<?php echo htmlspecialchars($data['product_title']); ?>"
                       placeholder="Tiêu đề sản phẩm"
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
                          placeholder="Mô tả chi tiết..."><?php echo htmlspecialchars($data['description']); ?></textarea>
            </div>

            <!-- 2 cột: Giá & Danh mục -->
            <div class="ap-form-row">
                <div class="ap-form-group <?php echo !empty($data['errors']['price_err']) ? 'ap-form-group--error' : ''; ?>">
                    <label for="price" class="ap-form-label">Giá (VNĐ) <span class="ap-required">*</span></label>
                    <input type="number"
                           id="price"
                           name="price"
                           class="ap-form-input"
                           value="<?php echo htmlspecialchars((string) $data['price']); ?>"
                           min="0"
                           step="1000"
                           required>
                    <?php if (!empty($data['errors']['price_err'])) : ?>
                        <span class="ap-form-error"><?php echo $data['errors']['price_err']; ?></span>
                    <?php endif; ?>
                </div>

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

            <!-- Trạng thái kiểm duyệt -->
            <div class="ap-form-group <?php echo !empty($data['errors']['status_err']) ? 'ap-form-group--error' : ''; ?>">
                <label for="status" class="ap-form-label">Trạng thái kiểm duyệt</label>
                <select id="status" name="status" class="ap-form-select">
                    <option value="1" <?php echo $data['status'] == 1 ? 'selected' : ''; ?>>⏳ Chờ duyệt (Pending)</option>
                    <option value="2" <?php echo $data['status'] == 2 ? 'selected' : ''; ?>>✅ Đã duyệt (Approved)</option>
                    <option value="3" <?php echo $data['status'] == 3 ? 'selected' : ''; ?>>❌ Từ chối (Rejected)</option>
                </select>
                <?php if (!empty($data['errors']['status_err'])) : ?>
                    <span class="ap-form-error"><?php echo $data['errors']['status_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Nút hành động -->
            <div class="ap-form-actions">
                <a href="<?php echo URLROOT; ?>/adminProductController/index" class="btn btn-outline">Hủy bỏ</a>
                <button type="submit" class="btn btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
.tm-form-card {
    max-width: 680px;
    margin: 0 auto;
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}
.tm-form-group {
    margin-bottom: 20px;
}
.tm-form-label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    color: var(--apple-black);
}
.tm-required {
    color: #ff3b30;
}
.tm-form-input,
.tm-form-select,
.tm-form-textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #d2d2d7;
    border-radius: 10px;
    font-size: 14px;
    color: var(--apple-black);
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
    box-sizing: border-box;
}
.tm-form-input:focus,
.tm-form-select:focus,
.tm-form-textarea:focus {
    border-color: var(--apple-blue);
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.15);
}
.tm-form-error {
    display: block;
    color: #ff3b30;
    font-size: 12px;
    margin-top: 5px;
}
.tm-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f5;
}
.tm-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    user-select: none;
}
.tm-checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
.tm-form-hint {
    font-size: 12px;
    color: var(--apple-gray);
    margin-top: 4px;
}
</style>

<div class="container mt-4 mb-5">
    <div class="admin-header mb-4" style="max-width: 680px; margin: 0 auto 24px auto;">
        <nav class="breadcrumb mb-2">
            <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
            <a href="<?php echo URLROOT; ?>/testimonialController/index">Quản lý Testimonials</a> &nbsp;&rsaquo;&nbsp;
            <span class="text-muted">Thêm mới</span>
        </nav>
        <h1 class="admin-title">Thêm Testimonial mới</h1>
        <p class="admin-subtitle">Tạo cảm nhận, đánh giá khách hàng để hiển thị trên trang chủ và trang giới thiệu</p>
    </div>

    <div class="tm-form-card">
        <form action="<?php echo URLROOT; ?>/testimonialController/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">

            <!-- Người dùng -->
            <div class="tm-form-group">
                <label for="user_id" class="tm-form-label">Người dùng đánh giá <span class="tm-required">*</span></label>
                <select name="user_id" id="user_id" class="tm-form-select" required>
                    <option value="">-- Chọn tài khoản người dùng --</option>
                    <?php if (!empty($data['users'])) : ?>
                        <?php foreach ($data['users'] as $u) : ?>
                            <option value="<?php echo $u->id; ?>" <?php echo ((int)$data['user_id'] === (int)$u->id) ? 'selected' : ''; ?>>
                                #<?php echo $u->id; ?> - <?php echo htmlspecialchars($u->name); ?> (<?php echo htmlspecialchars($u->email); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (!empty($data['errors']['user_id_err'])) : ?>
                    <span class="tm-form-error"><?php echo $data['errors']['user_id_err']; ?></span>
                <?php endif; ?>
                <p class="tm-form-hint">Cảm nhận sẽ tự động hiển thị tên và ảnh đại diện của tài khoản này.</p>
            </div>

            <!-- Đánh giá số sao -->
            <div class="tm-form-group">
                <label for="rating" class="tm-form-label">Đánh giá sao <span class="tm-required">*</span></label>
                <select name="rating" id="rating" class="tm-form-select" required>
                    <option value="5" <?php echo ((int)$data['rating'] === 5) ? 'selected' : ''; ?>>★★★★★ (5 sao - Tuyệt vời)</option>
                    <option value="4" <?php echo ((int)$data['rating'] === 4) ? 'selected' : ''; ?>>★★★★☆ (4 sao - Rất tốt)</option>
                    <option value="3" <?php echo ((int)$data['rating'] === 3) ? 'selected' : ''; ?>>★★★☆☆ (3 sao - Tốt)</option>
                    <option value="2" <?php echo ((int)$data['rating'] === 2) ? 'selected' : ''; ?>>★★☆☆☆ (2 sao - Tạm được)</option>
                    <option value="1" <?php echo ((int)$data['rating'] === 1) ? 'selected' : ''; ?>>★☆☆☆☆ (1 sao - Chưa hài lòng)</option>
                </select>
                <?php if (!empty($data['errors']['rating_err'])) : ?>
                    <span class="tm-form-error"><?php echo $data['errors']['rating_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Nội dung đánh giá -->
            <div class="tm-form-group">
                <label for="content" class="tm-form-label">Nội dung cảm nhận / Đánh giá <span class="tm-required">*</span></label>
                <textarea name="content" 
                          id="content" 
                          class="tm-form-textarea" 
                          rows="4" 
                          placeholder="Chia sẻ trải nghiệm sử dụng nền tảng Creono..." 
                          required><?php echo htmlspecialchars($data['content']); ?></textarea>
                <?php if (!empty($data['errors']['content_err'])) : ?>
                    <span class="tm-form-error"><?php echo $data['errors']['content_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Thứ tự sắp xếp -->
            <div class="tm-form-group">
                <label for="sort_order" class="tm-form-label">Thứ tự hiển thị</label>
                <input type="number" 
                       name="sort_order" 
                       id="sort_order" 
                       class="tm-form-input" 
                       value="<?php echo (int)$data['sort_order']; ?>" 
                       min="0" 
                       step="1">
                <p class="tm-form-hint">Số nhỏ hơn sẽ được ưu tiên hiển thị trước (mặc định: 0).</p>
                <?php if (!empty($data['errors']['sort_order_err'])) : ?>
                    <span class="tm-form-error"><?php echo $data['errors']['sort_order_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Trạng thái nổi bật -->
            <div class="tm-form-group">
                <label class="tm-checkbox-label">
                    <input type="checkbox" name="is_featured" value="1" <?php echo !empty($data['is_featured']) ? 'checked' : ''; ?>>
                    <span><strong>Duyệt hiển thị nổi bật</strong> (Ưu tiên hiển thị trên trang chủ & trang giới thiệu)</span>
                </label>
            </div>

            <!-- Nút hành động -->
            <div class="tm-form-actions">
                <a href="<?php echo URLROOT; ?>/testimonialController/index" class="btn btn-outline">Hủy bỏ</a>
                <button type="submit" class="btn btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Lưu Testimonial
                </button>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- CSS chuyên biệt cho trang Quản lý Testimonials -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/admin-testimonials.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <div class="admin-header mb-4 tm-header-centered">
        <nav class="breadcrumb mb-2">
            <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
            <a href="<?php echo URLROOT; ?>/testimonialController/index">Quản lý Testimonials</a> &nbsp;&rsaquo;&nbsp;
            <span class="text-muted">Chỉnh sửa #<?php echo $data['testimonial']->id; ?></span>
        </nav>
        <h1 class="admin-title">Chỉnh sửa Testimonial</h1>
        <p class="admin-subtitle">Cập nhật nội dung cảm nhận hoặc quyền hiển thị nổi bật</p>
    </div>

    <div class="tm-form-card">
        <form action="<?php echo URLROOT; ?>/testimonialController/update/<?php echo $data['testimonial']->id; ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">

            <!-- Người dùng -->
            <div class="tm-form-group">
                <label for="user_id" class="tm-form-label">Người dùng đánh giá <span class="tm-required">*</span></label>
                <select name="user_id" id="user_id" class="tm-form-select" required>
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
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="tm-btn-icon">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Cập nhật thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

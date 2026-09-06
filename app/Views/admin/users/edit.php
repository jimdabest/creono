<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- CSS chuyên biệt cho trang Quản lý User -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/admin-users.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="admin-header mb-4">
        <nav class="breadcrumb mb-2">
            <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
            <a href="<?php echo URLROOT; ?>/adminUserController/index">Quản lý Người dùng</a> &nbsp;&rsaquo;&nbsp;
            <span class="text-muted">Chỉnh sửa</span>
        </nav>
        <h1 class="admin-title">Chỉnh sửa Người dùng</h1>
        <p class="admin-subtitle">Cập nhật thông tin tài khoản #<?php echo $data['user']->id; ?></p>
    </div>

    <!-- Form chỉnh sửa user -->
    <div class="admin-card au-form-card">
        <form action="<?php echo URLROOT; ?>/adminUserController/update/<?php echo $data['user']->id; ?>" method="POST" class="au-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- Thông tin hiện tại -->
            <div class="au-current-info mb-3">
                <div class="au-info-badge">
                    <span class="au-info-label">ID:</span>
                    <span class="au-info-value">#<?php echo $data['user']->id; ?></span>
                </div>
                <div class="au-info-badge">
                    <span class="au-info-label">Trạng thái:</span>
                    <span class="au-info-value <?php echo $data['user']->is_locked ? 'text-danger' : 'text-green'; ?>">
                        <?php echo $data['user']->is_locked ? '🔒 Đã khóa' : '✅ Hoạt động'; ?>
                    </span>
                </div>
                <div class="au-info-badge">
                    <span class="au-info-label">Ngày tạo:</span>
                    <span class="au-info-value"><?php echo date('d/m/Y H:i', strtotime($data['user']->created_at)); ?></span>
                </div>
            </div>

            <!-- Tên người dùng -->
            <div class="au-form-group <?php echo !empty($data['errors']['name_err']) ? 'au-form-group--error' : ''; ?>">
                <label for="name" class="au-form-label">Tên người dùng <span class="au-required">*</span></label>
                <input type="text"
                       id="name"
                       name="name"
                       class="au-form-input"
                       value="<?php echo htmlspecialchars($data['name']); ?>"
                       placeholder="Nhập tên hiển thị"
                       required>
                <?php if (!empty($data['errors']['name_err'])) : ?>
                    <span class="au-form-error"><?php echo $data['errors']['name_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="au-form-group <?php echo !empty($data['errors']['email_err']) ? 'au-form-group--error' : ''; ?>">
                <label for="email" class="au-form-label">Email <span class="au-required">*</span></label>
                <input type="email"
                       id="email"
                       name="email"
                       class="au-form-input"
                       value="<?php echo htmlspecialchars($data['email']); ?>"
                       placeholder="example@email.com"
                       required>
                <?php if (!empty($data['errors']['email_err'])) : ?>
                    <span class="au-form-error"><?php echo $data['errors']['email_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Vai trò -->
            <div class="au-form-group <?php echo !empty($data['errors']['role_err']) ? 'au-form-group--error' : ''; ?>">
                <label for="role" class="au-form-label">Vai trò <span class="au-required">*</span></label>
                <select id="role" name="role" class="au-form-select" required>
                    <option value="1" <?php echo $data['role'] == 1 ? 'selected' : ''; ?>>Buyer (Người mua)</option>
                    <option value="2" <?php echo $data['role'] == 2 ? 'selected' : ''; ?>>Seller (Người bán)</option>
                    <option value="4" <?php echo $data['role'] == 4 ? 'selected' : ''; ?>>Censor (Kiểm duyệt viên)</option>
                </select>
                <?php if (!empty($data['errors']['role_err'])) : ?>
                    <span class="au-form-error"><?php echo $data['errors']['role_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Nút hành động -->
            <div class="au-form-actions">
                <a href="<?php echo URLROOT; ?>/adminUserController/index" class="btn btn-outline">Hủy bỏ</a>
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

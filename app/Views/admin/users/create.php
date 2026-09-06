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
            <span class="text-muted">Thêm mới</span>
        </nav>
        <h1 class="admin-title">Thêm Người dùng mới</h1>
        <p class="admin-subtitle">Tạo tài khoản Buyer, Seller hoặc Censor cho hệ thống</p>
    </div>

    <!-- Form thêm user -->
    <div class="admin-card au-form-card">
        <form action="<?php echo URLROOT; ?>/adminUserController/store" method="POST" class="au-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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

            <!-- Mật khẩu -->
            <div class="au-form-group <?php echo !empty($data['errors']['password_err']) ? 'au-form-group--error' : ''; ?>">
                <label for="password" class="au-form-label">Mật khẩu <span class="au-required">*</span></label>
                <input type="password"
                       id="password"
                       name="password"
                       class="au-form-input"
                       placeholder="Tối thiểu 6 ký tự"
                       required>
                <?php if (!empty($data['errors']['password_err'])) : ?>
                    <span class="au-form-error"><?php echo $data['errors']['password_err']; ?></span>
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
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    Tạo tài khoản
                </button>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

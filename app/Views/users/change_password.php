<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="row justify-content-center">
    <!-- Tận dụng class card có sẵn -->
    <div class="card" style="max-width: 500px; width: 100%; margin-top: 30px;">
        <h2>Đổi mật khẩu bảo mật</h2>
        
        <form action="<?php echo URLROOT; ?>/users/changePassword" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? generateCsrfToken()); ?>">
            
            <!-- Nhập mật khẩu cũ -->
            <div class="form-group">
                <label for="old_password">Mật khẩu hiện tại: *</label>
                <input type="password" name="old_password" 
                       class="<?php echo (!empty($data['old_password_err'])) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo htmlspecialchars($data['old_password']); ?>">
                <?php if(!empty($data['old_password_err'])) : ?>
                    <span class="error-text"><?php echo $data['old_password_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Nhập mật khẩu mới -->
            <div class="form-group">
                <label for="new_password">Mật khẩu mới: *</label>
                <input type="password" name="new_password" 
                       class="<?php echo (!empty($data['new_password_err'])) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo htmlspecialchars($data['new_password']); ?>">
                <?php if(!empty($data['new_password_err'])) : ?>
                    <span class="error-text"><?php echo $data['new_password_err']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Xác nhận mật khẩu mới -->
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới: *</label>
                <input type="password" name="confirm_password" 
                       class="<?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo htmlspecialchars($data['confirm_password']); ?>">
                <?php if(!empty($data['confirm_password_err'])) : ?>
                    <span class="error-text"><?php echo $data['confirm_password_err']; ?></span>
                <?php endif; ?>
            </div>

            <input type="submit" value="Xác nhận thay đổi" class="btn">
            <a href="<?php echo URLROOT; ?>/users/profile" class="btn btn-light mt-2" style="display: block; text-align: center; text-decoration: none;">Hủy & Quay lại Hồ sơ</a>
        </form>
    </div>
</div>

<!-- Nạp Footer -->
<?php require APPROOT . '/Views/inc/footer.php'; ?>
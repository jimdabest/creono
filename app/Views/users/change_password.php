<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="row justify-content-center">
    <div class="card" style="max-width: 500px; width: 100%; margin-top: 30px;">
        <h2>Đổi mật khẩu bảo mật</h2>
        
        <!-- Thêm data-ajax -->
        <form action="<?php echo URLROOT; ?>/users/changePassword" method="POST" id="changePasswordForm" data-ajax>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? generateCsrfToken()); ?>">
            
            <!-- Nhập mật khẩu cũ -->
            <div class="form-group">
                <label for="old_password">Mật khẩu hiện tại: *</label>
                <input type="password" name="old_password" id="old_password" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($data['old_password'] ?? ''); ?>"
                       placeholder="Nhập mật khẩu hiện tại"
                       required>
                <span class="error-text" id="old_password_err"></span>
            </div>

            <!-- Nhập mật khẩu mới -->
            <div class="form-group">
                <label for="new_password">Mật khẩu mới: *</label>
                <input type="password" name="new_password" id="new_password" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($data['new_password'] ?? ''); ?>"
                       placeholder="Tối thiểu 6 ký tự"
                       required minlength="6">
                <span class="error-text" id="new_password_err"></span>
            </div>

            <!-- Xác nhận mật khẩu mới -->
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới: *</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($data['confirm_password'] ?? ''); ?>"
                       placeholder="Nhập lại mật khẩu mới"
                       required>
                <span class="error-text" id="confirm_password_err"></span>
            </div>

            <button type="submit" class="btn" id="changePasswordBtn">Xác nhận thay đổi</button>
            <a href="<?php echo URLROOT; ?>/users/profile" class="btn btn-light mt-2" style="display: block; text-align: center; text-decoration: none;">← Quay lại Hồ sơ</a>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
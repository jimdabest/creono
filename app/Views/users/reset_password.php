<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="auth-page">
    <div class="glass-card">
        <h2>Đặt lại mật khẩu</h2>
        <p class="subtitle">Nhập mật khẩu mới cho tài khoản của bạn.</p>

        <form action="<?php echo URLROOT; ?>/users/resetPassword/<?php echo htmlspecialchars($data['token']); ?>" method="POST" id="resetPasswordForm" data-ajax>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">

            <div class="form-group">
                <label for="password">Mật khẩu mới</label>
                <input type="password" name="password" id="password" class="form-control <?php echo isset($data['errors']['password_err']) ? 'is-invalid' : ''; ?>" placeholder="Tối thiểu 6 ký tự" required minlength="6">
                <span class="error-text" id="password_err"><?php echo $data['errors']['password_err'] ?? ''; ?></span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo isset($data['errors']['confirm_password_err']) ? 'is-invalid' : ''; ?>" placeholder="Nhập lại mật khẩu" required>
                <span class="error-text" id="confirm_password_err"><?php echo $data['errors']['confirm_password_err'] ?? ''; ?></span>
            </div>

            <button type="submit" class="btn btn-submit">Xác nhận đặt lại</button>

            <a href="<?php echo URLROOT; ?>/users/login" class="auth-link">
                Quay lại đăng nhập
            </a>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
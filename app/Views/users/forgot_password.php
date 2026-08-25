<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="auth-page">
    <div class="glass-card">
        <h2>Quên mật khẩu</h2>
        <p class="subtitle">Nhập email của bạn để nhận hướng dẫn đặt lại mật khẩu.</p>

        <form action="<?php echo URLROOT; ?>/users/forgotPassword" method="POST" id="forgotPasswordForm" data-ajax>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" placeholder="name@example.com" required>
                <span class="error-text" id="email_err"></span>
            </div>

            <button type="submit" class="btn btn-submit">Gửi yêu cầu</button>

            <a href="<?php echo URLROOT; ?>/users/login" class="auth-link">
                Quay lại đăng nhập
            </a>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
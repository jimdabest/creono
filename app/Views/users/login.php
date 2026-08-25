<?php

/** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="auth-page">
    <div class="glass-card">
        <h2>Đăng nhập</h2>
        <p class="subtitle">Mừng bạn quay trở lại Creono.</p>

        <form action="<?php echo URLROOT; ?>/users/login" method="POST" id="loginForm" data-ajax>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? generateCsrfToken()); ?>">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email"
                    class="form-control"
                    value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                    placeholder="name@example.com"
                    required>
                <span class="error-text" id="email_err"></span>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" name="password" id="password"
                    class="form-control"
                    value="<?php echo htmlspecialchars($data['password'] ?? ''); ?>"
                    placeholder="Nhập mật khẩu"
                    required>
                <span class="error-text" id="password_err"></span>
            </div>

            <button type="submit" class="btn btn-submit" id="loginBtn">Đăng nhập</button>
            <div style="text-align: center; margin-top: 12px;">
                <a href="<?php echo URLROOT; ?>/users/forgotPassword" style="font-size: 14px; color: var(--apple-gray); text-decoration: none;">
                    Quên mật khẩu?
                </a>
            </div>
            <a href="<?php echo URLROOT; ?>/users/register" class="auth-link">
                Chưa có tài khoản? <span style="color: var(--apple-blue); font-weight: 600;">Đăng ký ngay</span>
            </a>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
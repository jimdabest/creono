<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="auth-page">
    <div class="glass-card">
        <h2>Tạo tài khoản</h2>
        <p class="subtitle">Bắt đầu hành trình sáng tạo cùng Creono.</p>
        
        <form action="<?php echo URLROOT; ?>/users/register" method="POST" id="registerForm" data-ajax>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? generateCsrfToken()); ?>">
            
            <div class="form-group">
                <label for="name">Họ và tên</label>
                <input type="text" name="name" id="name" 
                       class="form-control" 
                       value="<?php echo isset($data['name']) ? htmlspecialchars($data['name']) : ''; ?>"
                       placeholder="Nguyễn Văn A"
                       required>
                <span class="error-text" id="name_err"></span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" 
                       class="form-control" 
                       value="<?php echo isset($data['email']) ? htmlspecialchars($data['email']) : ''; ?>"
                       placeholder="name@example.com"
                       required>
                <span class="error-text" id="email_err"></span>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" name="password" id="password" 
                       class="form-control" 
                       value="<?php echo isset($data['password']) ? htmlspecialchars($data['password']) : ''; ?>"
                       placeholder="Tối thiểu 6 ký tự"
                       required minlength="6">
                <span class="error-text" id="password_err"></span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       class="form-control" 
                       value="<?php echo isset($data['confirm_password']) ? htmlspecialchars($data['confirm_password']) : ''; ?>"
                       placeholder="Nhập lại mật khẩu"
                       required>
                <span class="error-text" id="confirm_password_err"></span>
            </div>

            <button type="submit" class="btn btn-submit" id="registerBtn">Đăng ký</button>
            
            <a href="<?php echo URLROOT; ?>/users/login" class="auth-link">
                Đã có tài khoản? <span style="color: var(--apple-blue); font-weight: 600;">Đăng nhập</span>
            </a>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
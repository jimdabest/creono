<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="card">
    <h2>Đăng nhập Creono</h2>
    
    <!-- Thêm data-ajax để xử lý bằng AJAX -->
    <form action="<?php echo URLROOT; ?>/users/login" method="POST" id="loginForm" data-ajax>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? generateCsrfToken()); ?>">
        
        <!-- Input Email -->
        <div class="form-group">
            <label for="email">Email: *</label>
            <input type="email" name="email" id="email" 
                   class="form-control" 
                   value="<?php echo $data['email']; ?>"
                   placeholder="example@email.com"
                   required>
            <span class="error-text" id="email_err"></span>
        </div>

        <!-- Input Mật khẩu -->
        <div class="form-group">
            <label for="password">Mật khẩu: *</label>
            <input type="password" name="password" id="password" 
                   class="form-control" 
                   value="<?php echo $data['password']; ?>"
                   placeholder="Nhập mật khẩu"
                   required>
            <span class="error-text" id="password_err"></span>
        </div>

        <!-- Buttons -->
        <button type="submit" class="btn" id="loginBtn">Đăng nhập</button>
        <a href="<?php echo URLROOT; ?>/users/register" class="btn btn-light mt-2" style="display: block; text-align: center; text-decoration: none;">Chưa có tài khoản? Đăng ký ngay</a>
    </form>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
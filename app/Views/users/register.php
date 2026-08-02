<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="card">
    <h2>Tạo tài khoản Creono</h2>
    <p class="text-center" style="color: #7f8c8d; margin-bottom: 20px;">Vui lòng điền thông tin để tham gia nền tảng</p>
    
    <!-- Thêm data-ajax để xử lý bằng AJAX -->
    <form action="<?php echo URLROOT; ?>/users/register" method="POST" id="registerForm" data-ajax>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? generateCsrfToken()); ?>">
        
        <!-- Khối nhập Họ và Tên -->
        <div class="form-group">
            <label for="name">Họ và Tên: *</label>
            <input type="text" name="name" id="name" 
                   class="form-control" 
                   value="<?php echo isset($data['name']) ? $data['name'] : ''; ?>"
                   placeholder="Nhập họ và tên"
                   required>
            <span class="error-text" id="name_err"></span>
        </div>

        <!-- Khối nhập Email -->
        <div class="form-group">
            <label for="email">Email: *</label>
            <input type="email" name="email" id="email" 
                   class="form-control" 
                   value="<?php echo isset($data['email']) ? $data['email'] : ''; ?>"
                   placeholder="example@email.com"
                   required>
            <span class="error-text" id="email_err"></span>
        </div>

        <!-- Khối nhập Mật khẩu -->
        <div class="form-group">
            <label for="password">Mật khẩu: *</label>
            <input type="password" name="password" id="password" 
                   class="form-control" 
                   value="<?php echo isset($data['password']) ? $data['password'] : ''; ?>"
                   placeholder="Tối thiểu 6 ký tự"
                   required minlength="6">
            <span class="error-text" id="password_err"></span>
        </div>

        <!-- Khối Nhập lại mật khẩu -->
        <div class="form-group">
            <label for="confirm_password">Xác nhận mật khẩu: *</label>
            <input type="password" name="confirm_password" id="confirm_password" 
                   class="form-control" 
                   value="<?php echo isset($data['confirm_password']) ? $data['confirm_password'] : ''; ?>"
                   placeholder="Nhập lại mật khẩu"
                   required>
            <span class="error-text" id="confirm_password_err"></span>
        </div>

        <!-- Nút Submit và Link -->
        <button type="submit" class="btn" id="registerBtn">Đăng ký ngay</button>
        <a href="<?php echo URLROOT; ?>/users/login" class="btn btn-light mt-2" style="display: block; text-align: center; text-decoration: none;">Bạn đã có tài khoản? Đăng nhập</a>
    </form>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
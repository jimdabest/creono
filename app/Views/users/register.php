<?php /** @var array $data */ ?>
<!-- Gọi Header -->
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="card">
    <h2>Tạo tài khoản Creono</h2>
    <p class="text-center" style="color: #7f8c8d; margin-bottom: 20px;">Vui lòng điền thông tin để tham gia nền tảng</p>
    
    <!-- Gửi dữ liệu về lại hàm register trong Controller Users -->
    <form action="<?php echo URLROOT; ?>/users/register" method="POST">
        
        <!-- Khối nhập Email -->
        <div class="form-group">
            <label for="email">Email: *</label>
            <input type="email" name="email" 
                   class="<?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" 
                   value="<?php echo $data['email']; ?>">
            
            <?php if(!empty($data['email_err'])) : ?>
                <span class="error-text"><?php echo $data['email_err']; ?></span>
            <?php endif; ?>
        </div>

        <!-- Khối nhập Mật khẩu -->
        <div class="form-group">
            <label for="password">Mật khẩu: *</label>
            <input type="password" name="password" 
                   class="<?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" 
                   value="<?php echo $data['password']; ?>">
            
            <?php if(!empty($data['password_err'])) : ?>
                <span class="error-text"><?php echo $data['password_err']; ?></span>
            <?php endif; ?>
        </div>

        <!-- Khối Nhập lại mật khẩu -->
        <div class="form-group">
            <label for="confirm_password">Xác nhận mật khẩu: *</label>
            <input type="password" name="confirm_password" 
                   class="<?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" 
                   value="<?php echo $data['confirm_password']; ?>">
            
            <?php if(!empty($data['confirm_password_err'])) : ?>
                <span class="error-text"><?php echo $data['confirm_password_err']; ?></span>
            <?php endif; ?>
        </div>

        <!-- Nút Submit và Link -->
        <input type="submit" value="Đăng ký ngay" class="btn">
        <a href="<?php echo URLROOT; ?>/users/login" class="btn btn-light mt-2">Bạn đã có tài khoản? Đăng nhập</a>
    </form>
</div>

<!-- Gọi Footer -->
<?php require APPROOT . '/Views/inc/footer.php'; ?>
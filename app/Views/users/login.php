<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="card">
    <h2>Đăng nhập Creono</h2>
    
    <form action="<?php echo URLROOT; ?>/users/login" method="POST">
        <!-- Input Email -->
        <div class="form-group">
            <label for="email">Email: *</label>
            <input type="email" name="email" 
                   class="<?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" 
                   value="<?php echo $data['email']; ?>">
            
            <?php if(!empty($data['email_err'])) : ?>
                <span class="error-text"><?php echo $data['email_err']; ?></span>
            <?php endif; ?>
        </div>

        <!-- Input Mật khẩu -->
        <div class="form-group">
            <label for="password">Mật khẩu: *</label>
            <input type="password" name="password" 
                   class="<?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" 
                   value="<?php echo $data['password']; ?>">
            
            <?php if(!empty($data['password_err'])) : ?>
                <span class="error-text"><?php echo $data['password_err']; ?></span>
            <?php endif; ?>
        </div>

        <!-- Buttons -->
        <input type="submit" value="Đăng nhập" class="btn">
        <a href="<?php echo URLROOT; ?>/users/register" class="btn btn-light mt-2">Chưa có tài khoản? Đăng ký ngay</a>
    </form>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
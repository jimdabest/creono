<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="max-width: 650px; margin-top: 40px;">
    <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 8px;">Chỉnh sửa cửa hàng</h1>
    <p style="color: #86868b; margin-bottom: 32px;">Cập nhật thông tin nhận diện cửa hàng của bạn.</p>

    <div class="card" style="padding: 32px;">
        <form action="<?php echo URLROOT; ?>/stores/update" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">

            <div class="form-group">
                <label for="name">Tên cửa hàng <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control <?php echo isset($data['errors']['name_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['store']->name ?? ''); ?>" required>
                <span class="error-text"><?php echo $data['errors']['name_err'] ?? ''; ?></span>
            </div>

            <div class="form-group">
                <label for="description">Mô tả cửa hàng</label>
                <textarea name="description" id="description" rows="4" class="form-control"><?php echo htmlspecialchars($data['store']->description ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="logo">Logo cửa hàng</label>
                <?php if (!empty($data['store']->logo_url)): ?>
                    <div style="margin-bottom: 12px;">
                        <img src="<?php echo URLROOT . htmlspecialchars($data['store']->logo_url); ?>" alt="Logo Cửa Hàng" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 1px solid #e1e1e1;">
                    </div>
                <?php endif; ?>
                <input type="file" name="logo" id="logo" class="form-control <?php echo isset($data['errors']['logo_err']) ? 'is-invalid' : ''; ?>" accept="image/jpeg, image/png, image/gif, image/webp">
                <span class="error-text"><?php echo $data['errors']['logo_err'] ?? ''; ?></span>
                <small class="form-hint">Tải lên file ảnh mới để thay thế logo hiện tại (Tối đa 2MB).</small>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($data['store']->phone ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <input type="text" name="address" id="address" class="form-control" value="<?php echo htmlspecialchars($data['store']->address ?? ''); ?>">
            </div>

            <hr style="border: 0; border-top: 1px solid #e1e1e1; margin: 24px 0;">
            <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 16px;">Thông tin ngân hàng</h3>

            <div class="form-group">
                <label for="bank_name">Tên ngân hàng</label>
                <input type="text" name="bank_name" id="bank_name" class="form-control" value="<?php echo htmlspecialchars($data['store']->bank_name ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="bank_account_number">Số tài khoản</label>
                <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" value="<?php echo htmlspecialchars($data['store']->bank_account_number ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="bank_account_name">Tên chủ tài khoản</label>
                <input type="text" name="bank_account_name" id="bank_account_name" class="form-control" value="<?php echo htmlspecialchars($data['store']->bank_account_name ?? ''); ?>">
            </div>

            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">Lưu thay đổi</button>
                <a href="<?php echo URLROOT; ?>/seller/dashboard" class="btn btn-secondary" style="flex: 0.4; text-align: center; background: #f5f5f7; color: #1d1d1f; text-decoration: none; padding: 12px;">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
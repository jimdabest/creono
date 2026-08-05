<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<?php $user = $data['user'] ?? null; ?>

<div class="container mt-4">
    <div class="card profile-card">
        <h2 class="text-center">Hồ sơ cá nhân</h2>

        <?php if ($user) : ?>
            <div class="profile-summary">
                <div class="profile-info">
                    <p><strong>Họ và tên:</strong> <?php echo htmlspecialchars($user->full_name ?? $user->name ?? ''); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user->email ?? ''); ?></p>
                    <p><strong>Giới thiệu:</strong> <?php echo nl2br(htmlspecialchars($user->bio ?? 'Chưa cập nhật')); ?></p>
                </div>
                <?php if (!empty($user->avatar_url)) : ?>
                    <div class="profile-avatar">
                        <img src="<?php echo URLROOT . htmlspecialchars($user->avatar_url); ?>" alt="Avatar" id="avatarPreview">
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo URLROOT; ?>/users/updateProfile" method="POST" enctype="multipart/form-data" id="profileForm" data-ajax>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

            <div class="form-group">
                <label for="full_name">Họ và tên hiển thị</label>
                <input type="text" name="full_name" id="full_name" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($user->full_name ?? $user->name ?? ''); ?>">
                <span class="error-text" id="full_name_err"></span>
            </div>

            <div class="form-group">
                <label for="bio">Giới thiệu</label>
                <textarea name="bio" id="bio" class="form-control" rows="5"><?php echo htmlspecialchars($user->bio ?? ''); ?></textarea>
                <span class="error-text" id="bio_err"></span>
            </div>

            <div class="form-group">
                <label for="avatar">Ảnh đại diện</label>
                <input type="file" name="avatar" id="avatar" accept="image/*" class="form-control">
                <span class="error-text" id="avatar_err"></span>
                <div id="avatarPreviewContainer" style="margin-top: 10px;"></div>
            </div>

            <button type="submit" class="btn" id="updateProfileBtn">Cập nhật hồ sơ</button>
            <a href="<?php echo URLROOT; ?>/users/changePassword" class="btn btn-light mt-2" style="display: block; text-align: center; text-decoration: none;">Đổi mật khẩu</a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar');
    const previewContainer = document.getElementById('avatarPreviewContainer');
    
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" 
                             style="max-width: 150px; border-radius: 8px; border: 2px solid #d2d2d7; padding: 4px;">
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<?php $user = $data['user'] ?? null; ?>

<div class="row justify-content-center">
    <div class="card" style="max-width: 760px; width: 100%; margin-top: 30px;">
        <h2>Hồ sơ cá nhân</h2>

        <?php if ($user) : ?>
            <div class="profile-summary" style="margin-bottom: 24px;">
                <p><strong>Họ và tên:</strong> <?php echo htmlspecialchars($user->full_name ?? $user->name ?? ''); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user->email ?? ''); ?></p>
                <p><strong>Giới thiệu:</strong> <?php echo nl2br(htmlspecialchars($user->bio ?? 'Chưa cập nhật')); ?></p>
                <?php if (!empty($user->avatar_url)) : ?>
                    <p><strong>Ảnh đại diện:</strong></p>
                    <img src="<?php echo URLROOT . htmlspecialchars($user->avatar_url); ?>" alt="Avatar" style="max-width: 120px; border-radius: 12px;">
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo URLROOT; ?>/users/updateProfile" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

            <div class="form-group">
                <label for="full_name">Họ và tên hiển thị</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user->full_name ?? $user->name ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="bio">Giới thiệu</label>
                <textarea name="bio" rows="5"><?php echo htmlspecialchars($user->bio ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="avatar">Ảnh đại diện</label>
                <input type="file" name="avatar" accept="image/*">
            </div>

            <input type="submit" value="Cập nhật hồ sơ" class="btn">
            <a href="<?php echo URLROOT; ?>/users/changePassword" class="btn btn-light mt-2">Đổi mật khẩu</a>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
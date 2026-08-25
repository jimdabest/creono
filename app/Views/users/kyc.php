<?php
/** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>
<div class="container" style="max-width: 480px; margin-top: 40px;">
    <div class="card">
        <h2>Xác minh danh tính</h2>
        <p class="subtitle">Vui lòng tải lên ảnh mặt trước của CMND/CCCD.</p>
        <form action="<?= URLROOT ?>/users/kyc" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token']) ?>">
            <div class="form-group">
                <label for="front_image">Ảnh mặt trước (CMND/CCCD)</label>
                <input type="file" name="front_image" id="front_image" class="form-control" accept="image/*" required>
                <small class="form-hint">Chỉ chấp nhận JPG, PNG, GIF.</small>
            </div>
            <button type="submit" class="btn btn-primary">Gửi yêu cầu</button>
            <a href="<?= URLROOT ?>/users/profile" class="btn btn-secondary" style="display: block; margin-top: 8px;">Quay lại hồ sơ</a>
        </form>
    </div>
</div>
<?php require APPROOT . '/Views/inc/footer.php'; ?>
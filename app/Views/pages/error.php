<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="card" style="text-align: center; max-width: 500px;">
    <div style="font-size: 48px; margin-bottom: 16px; color: var(--apple-gray);">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v4M12 16h.01"/>
        </svg>
    </div>
    <h2>Đã xảy ra lỗi</h2>
    <p style="color: var(--apple-gray);">Hệ thống không thể hoàn thành yêu cầu lúc này. Vui lòng thử lại sau.</p>
    <a href="<?php echo URLROOT; ?>" class="btn" style="width: auto; margin-top: 16px;">Về trang chủ</a>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
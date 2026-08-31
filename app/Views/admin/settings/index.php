<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Cấu hình</span>
            </nav>
            <h1 class="admin-title">Cấu hình Hệ thống</h1>
            <p class="admin-subtitle">Quản lý tỷ lệ hoa hồng và các tham số cốt lõi (UC45)</p>
        </div>
    </div>

    <div class="admin-card">
        <!-- Form tự động bắt AJAX nhờ data-ajax="true" -->
        <form action="<?php echo URLROOT; ?>/admin/updateSettings" method="POST" data-ajax="true" class="admin-form">
            <!-- Rule 3.3: Bắt buộc chèn token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">

            <div class="form-group mb-4">
                <label for="commission_rate">Tỷ lệ phí nền tảng (Commission Rate %) <span class="text-danger">*</span></label>
                <input type="number" 
                       id="commission_rate" 
                       name="commission_rate" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($data['commission_rate']); ?>" 
                       step="0.1" min="0" max="100" 
                       required>
                <small class="form-hint text-muted">Nhập tỷ lệ % hệ thống sẽ thu từ mỗi giao dịch bán tài liệu thành công.</small>
                <span class="error-text" id="commission_rate_err"></span>
            </div>

            <div class="form-actions flex-end mt-4">
                <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
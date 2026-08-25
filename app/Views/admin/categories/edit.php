<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <a href="<?php echo URLROOT; ?>/admin/categories">Quản lý Danh mục</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Chỉnh sửa #<?php echo $data['id']; ?></span>
            </nav>
            <h1 class="admin-title">Chỉnh Sửa Danh Mục</h1>
            <p class="admin-subtitle">Cập nhật thông tin danh mục #<?php echo $data['id']; ?> - <?php echo htmlspecialchars($data['category']->name ?? ''); ?></p>
        </div>
        <div>
            <a href="<?php echo URLROOT; ?>/admin/categories" class="btn btn-secondary">
                &larr; Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="card form-card-lg">
        <form action="<?php echo URLROOT; ?>/admin/categoryEdit/<?php echo $data['id']; ?>" method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div class="form-group mb-3">
                <label for="name">Tên danh mục <span class="text-danger">*</span></label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control <?php echo isset($data['errors']['name_err']) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>" 
                       required>
                <?php if (isset($data['errors']['name_err'])) : ?>
                    <span class="error-text"><?php echo $data['errors']['name_err']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group mb-3">
                <label for="slug">URL Slug <span class="text-danger">*</span></label>
                <input type="text" 
                       id="slug" 
                       name="slug" 
                       class="form-control <?php echo isset($data['errors']['slug_err']) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo htmlspecialchars($data['slug'] ?? ''); ?>" 
                       required>
                <small class="form-hint">Chuỗi đường dẫn URL duy nhất dành cho danh mục này.</small>
                <?php if (isset($data['errors']['slug_err'])) : ?>
                    <span class="error-text"><?php echo $data['errors']['slug_err']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group mb-3">
                <label for="sort_order">Thứ tự sắp xếp (Sort Order)</label>
                <input type="number" 
                       id="sort_order" 
                       name="sort_order" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($data['sort_order'] ?? 0); ?>" 
                       min="0">
            </div>

            <div class="form-group mb-4">
                <label for="description">Mô tả danh mục</label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          class="form-control" 
                          placeholder="Mô tả chi tiết về danh mục..."><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions flex-end gap-2">
                <a href="<?php echo URLROOT; ?>/admin/categories" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-success" style="width: auto; min-width: 140px;">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Responsive cho form chỉnh sửa danh mục (giống create) */
@media (max-width: 768px) {
    .admin-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 12px !important;
    }
    .admin-title {
        font-size: 26px !important;
    }
    .admin-subtitle {
        font-size: 14px !important;
    }
    .form-card-lg {
        padding: 20px !important;
    }
    .form-actions {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
    }
    .form-actions .btn {
        width: 100% !important;
        justify-content: center !important;
    }
}
@media (max-width: 480px) {
    .admin-title {
        font-size: 22px !important;
    }
    .admin-subtitle {
        font-size: 13px !important;
    }
    .form-card-lg {
        padding: 16px !important;
    }
    .form-group label {
        font-size: 14px !important;
    }
    .form-control {
        font-size: 14px !important;
        padding: 8px 12px !important;
    }
}
</style>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
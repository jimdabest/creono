<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <a href="<?php echo URLROOT; ?>/admin/categories">Quản lý Danh mục</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Thêm mới</span>
            </nav>
            <h1 class="admin-title">Thêm Danh Mục Mới</h1>
            <p class="admin-subtitle">Tạo danh mục phân loại sản phẩm mới cho hệ thống Creono</p>
        </div>
        <div>
            <a href="<?php echo URLROOT; ?>/admin/categories" class="btn btn-secondary">
                &larr; Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="card form-card-lg">
        <form action="<?php echo URLROOT; ?>/admin/categoryCreate" method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div class="form-group mb-3">
                <label for="name">Tên danh mục <span class="text-danger">*</span></label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control <?php echo isset($data['errors']['name_err']) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>" 
                       placeholder="Ví dụ: Thiết kế UI/UX, Lập trình Mobile..." 
                       required>
                <?php if (isset($data['errors']['name_err'])) : ?>
                    <span class="error-text"><?php echo $data['errors']['name_err']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group mb-3">
                <label for="slug">URL Slug (Đường dẫn thân thiện)</label>
                <input type="text" 
                       id="slug" 
                       name="slug" 
                       class="form-control <?php echo isset($data['errors']['slug_err']) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo htmlspecialchars($data['slug'] ?? ''); ?>" 
                       placeholder="Ví dụ: thiet-ke-ui-ux (Để trống hệ thống sẽ tự động tạo từ tên)">
                <small class="form-hint">Nếu để trống, URL slug sẽ được tự động tạo từ Tên danh mục.</small>
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
                <small class="form-hint">Số nhỏ hơn sẽ hiển thị trước ở trang chủ và danh sách lọc.</small>
            </div>

            <div class="form-group mb-4">
                <label for="description">Mô tả danh mục</label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          class="form-control" 
                          placeholder="Mô tả chi tiết về loại tài liệu trong danh mục này..."><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions flex-end gap-2">
                <a href="<?php echo URLROOT; ?>/admin/categories" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-success" style="width: auto; min-width: 140px;">
                    Tạo danh mục
                </button>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

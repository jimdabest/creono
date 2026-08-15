<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Quản lý Danh mục</span>
            </nav>
            <h1 class="admin-title">Danh sách Danh mục</h1>
            <p class="admin-subtitle">Quản lý các ngành hàng và nhóm tài liệu số trên Creono (UC42)</p>
        </div>
        <div class="admin-actions">
            <a href="<?php echo URLROOT; ?>/admin/categoryCreate" class="btn btn-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm danh mục mới
            </a>
        </div>
    </div>

    <!-- Category List Card Table -->
    <div class="admin-card">
        <div class="card-header flex-between">
            <h3>Tất cả danh mục (<?php echo count($data['categories']); ?>)</h3>
            <span class="badge badge-light">Sắp xếp theo thứ tự hiển thị</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Số sản phẩm</th>
                        <th style="width: 100px;">Thứ tự</th>
                        <th>Mô tả</th>
                        <th style="width: 150px;" class="text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['categories'])) : ?>
                        <?php foreach ($data['categories'] as $cat) : ?>
                            <tr>
                                <td><span class="text-muted">#<?php echo $cat->id; ?></span></td>
                                <td>
                                    <strong class="font-medium text-dark"><?php echo htmlspecialchars($cat->name); ?></strong>
                                </td>
                                <td><code class="code-badge"><?php echo htmlspecialchars($cat->slug); ?></code></td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo number_format($cat->product_count ?? 0); ?> sản phẩm
                                    </span>
                                </td>
                                <td>
                                    <span class="sort-badge"><?php echo (int)($cat->sort_order ?? 0); ?></span>
                                </td>
                                <td class="text-muted font-sm">
                                    <?php echo !empty($cat->description) ? htmlspecialchars(mb_strimwidth($cat->description, 0, 45, '...')) : '<em>Không có mô tả</em>'; ?>
                                </td>
                                <td class="text-right action-buttons">
                                    <a href="<?php echo URLROOT; ?>/admin/categoryEdit/<?php echo $cat->id; ?>" class="btn-action btn-action-edit" title="Chỉnh sửa">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        Sửa
                                    </a>

                                    <form action="<?php echo URLROOT; ?>/admin/categoryDelete/<?php echo $cat->id; ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục \'<?php echo htmlspecialchars($cat->name, ENT_QUOTES); ?>\'?\nCác sản phẩm thuộc danh mục này sẽ giữ nguyên và chuyển về chưa phân loại.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <button type="submit" class="btn-action btn-action-delete" title="Xóa danh mục">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có danh mục nào. Hãy thêm danh mục đầu tiên!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

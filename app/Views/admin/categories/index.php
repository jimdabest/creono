<?php /** @var array $data */ ?>
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
            <p class="admin-subtitle">Quản lý các ngành hàng và nhóm tài liệu số trên Creono</p>
        </div>
        <div class="admin-actions">
            <a href="<?php echo URLROOT; ?>/admin/categoryCreate" class="btn btn-success btn-add-category">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm danh mục mới
            </a>
        </div>
    </div>

    <!-- Category List Card -->
    <div class="admin-card">
        <div class="card-header flex-between">
            <h3>Tất cả danh mục (<?php echo count($data['categories']); ?>)</h3>
            <span class="badge badge-light">Sắp xếp theo thứ tự hiển thị</span>
        </div>

        <?php if (!empty($data['categories'])) : ?>
            <!-- 1. BẢNG DÀNH CHO MÀN HÌNH DESKTOP (min-width: 769px) -->
            <div class="category-table-wrapper d-none-mobile">
                <div class="table-responsive">
                    <table class="admin-table category-desktop-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Tên danh mục</th>
                                <th>Slug</th>
                                <th>Số sản phẩm</th>
                                <th style="width: 90px; text-align: center;">Thứ tự</th>
                                <th>Mô tả</th>
                                <th style="width: 160px;" class="text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['categories'] as $cat) : ?>
                                <tr>
                                    <td><span class="text-muted font-sm">#<?php echo $cat->id; ?></span></td>
                                    <td>
                                        <strong class="font-medium text-dark"><?php echo htmlspecialchars($cat->name); ?></strong>
                                    </td>
                                    <td><code class="code-badge"><?php echo htmlspecialchars($cat->slug); ?></code></td>
                                    <td>
                                        <span class="badge badge-primary badge-product-count">
                                            <?php echo number_format($cat->product_count ?? 0); ?> sản phẩm
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="sort-badge"><?php echo (int)($cat->sort_order ?? 0); ?></span>
                                    </td>
                                    <td class="text-muted font-sm desc-col">
                                        <?php echo !empty($cat->description) ? htmlspecialchars(mb_strimwidth($cat->description, 0, 55, '...')) : '<em>Không có mô tả</em>'; ?>
                                    </td>
                                    <td class="text-right action-buttons">
                                        <a href="<?php echo URLROOT; ?>/admin/categoryEdit/<?php echo $cat->id; ?>" class="btn-action btn-action-edit" title="Chỉnh sửa">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            Sửa
                                        </a>

                                        <form action="<?php echo URLROOT; ?>/admin/categoryDelete/<?php echo $cat->id; ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục \'<?php echo htmlspecialchars($cat->name, ENT_QUOTES); ?>\'?\nCác sản phẩm thuộc danh mục này sẽ giữ nguyên và chuyển về chưa phân loại.');">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                            <button type="submit" class="btn-action btn-action-delete" title="Xóa danh mục">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                                Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. DANH SÁCH THẺ DÀNH CHO MÀN HÌNH MOBILE (max-width: 768px) -->
            <div class="category-mobile-cards d-none-desktop">
                <?php foreach ($data['categories'] as $cat) : ?>
                    <div class="category-mobile-card">
                        <!-- Hàng 1: ID, Tên danh mục, Số sản phẩm & Thứ tự -->
                        <div class="card-row-top">
                            <div class="cat-title-group">
                                <span class="cat-id-badge">#<?php echo $cat->id; ?></span>
                                <h4 class="cat-name"><?php echo htmlspecialchars($cat->name); ?></h4>
                            </div>
                            <div class="cat-meta-right">
                                <span class="badge badge-primary badge-product-count">
                                    <?php echo number_format($cat->product_count ?? 0); ?> SP
                                </span>
                                <span class="sort-badge" title="Thứ tự hiển thị">STT: <?php echo (int)($cat->sort_order ?? 0); ?></span>
                            </div>
                        </div>

                        <!-- Hàng 2: Slug & Mô tả vắn tắt -->
                        <div class="card-row-mid">
                            <div class="cat-slug-box">
                                <span class="cat-label">Slug:</span>
                                <code class="code-badge"><?php echo htmlspecialchars($cat->slug); ?></code>
                            </div>
                            <?php if (!empty($cat->description)) : ?>
                                <p class="cat-desc"><?php echo htmlspecialchars($cat->description); ?></p>
                            <?php else : ?>
                                <p class="cat-desc text-muted"><em>Chưa có mô tả</em></p>
                            <?php endif; ?>
                        </div>

                        <!-- Hàng 3: Nút [Sửa] và [Xóa] dạng Button lớn cảm ứng (Touch-friendly) -->
                        <div class="card-row-actions">
                            <a href="<?php echo URLROOT; ?>/admin/categoryEdit/<?php echo $cat->id; ?>" class="btn-mobile-action btn-mobile-edit">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Chỉnh sửa
                            </a>

                            <form action="<?php echo URLROOT; ?>/admin/categoryDelete/<?php echo $cat->id; ?>" method="POST" class="form-mobile-delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục \'<?php echo htmlspecialchars($cat->name, ENT_QUOTES); ?>\'?\nCác sản phẩm thuộc danh mục này sẽ giữ nguyên và chuyển về chưa phân loại.');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <button type="submit" class="btn-mobile-action btn-mobile-delete">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="text-center text-muted py-5 empty-category-state">
                <p style="font-size: 36px; margin-bottom: 8px;">📂</p>
                <p class="font-medium">Chưa có danh mục nào.</p>
                <p class="font-sm text-muted">Hãy bấm nút "Thêm danh mục mới" ở trên để tạo danh mục đầu tiên!</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php require APPROOT . '/Views/inc/footer.php'; ?>
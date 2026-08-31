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

<style>
/* =======================================================
   CSS TỐI ƯU UI/UX CHO TRANG QUẢN LÝ DANH MỤC (ADMIN)
   ======================================================= */

/* Badge số lượng sản phẩm gọn gàng, không làm lệch line-height */
.badge-product-count {
    padding: 3px 8px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
    border-radius: 6px !important;
    display: inline-flex !important;
    align-items: center !important;
    font-weight: 500 !important;
}

/* Badge thứ tự */
.sort-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

/* Slug code badge */
.code-badge {
    background: rgba(0, 113, 227, 0.08);
    color: var(--apple-blue, #0071e3);
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Desktop Table Styling */
.category-desktop-table th,
.category-desktop-table td {
    padding: 12px 14px;
    vertical-align: middle;
}

.category-desktop-table th {
    white-space: nowrap;
    background: var(--apple-gray-bg, #f5f5f7);
    color: var(--apple-gray, #86868b);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.category-desktop-table td {
    white-space: nowrap;
}

.category-desktop-table td.desc-col {
    white-space: normal;
    max-width: 260px;
    line-height: 1.4;
}

/* =======================================================
   RESPONSIVE DESIGN (MOBILE & TABLET <= 768px)
   ======================================================= */

.d-none-mobile {
    display: block;
}

.d-none-desktop {
    display: none;
}

@media (max-width: 768px) {
    /* Ẩn bảng truyền thống, hiển thị danh sách thẻ trên mobile */
    .d-none-mobile {
        display: none !important;
    }

    .d-none-desktop {
        display: flex !important;
        flex-direction: column;
        gap: 14px;
    }

    /* Header & Nút thêm mới full-width */
    .admin-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 14px !important;
    }

    .admin-title {
        font-size: 24px !important;
        line-height: 1.2 !important;
    }

    .admin-subtitle {
        font-size: 13px !important;
    }

    .admin-actions {
        width: 100% !important;
    }

    .btn-add-category {
        width: 100% !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        padding: 12px 16px !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(52, 199, 89, 0.25) !important;
    }

    /* Container thẻ Card trên Mobile */
    .admin-card {
        padding: 16px !important;
        border-radius: 16px !important;
    }

    .card-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 6px !important;
        margin-bottom: 14px !important;
        padding-bottom: 12px !important;
    }

    .card-header h3 {
        font-size: 17px !important;
    }

    /* Chi tiết từng thẻ Card danh mục */
    .category-mobile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        gap: 12px;
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .category-mobile-card:active {
        transform: scale(0.99);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }

    /* Hàng 1: Tiêu đề & meta */
    .card-row-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }

    .cat-title-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }

    .cat-id-badge {
        font-size: 11px;
        color: #64748b;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .cat-name {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        line-height: 1.3;
    }

    .cat-meta-right {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }

    /* Hàng 2: Slug & mô tả */
    .card-row-mid {
        display: flex;
        flex-direction: column;
        gap: 6px;
        background: #f8fafc;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #f1f5f9;
    }

    .cat-slug-box {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cat-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
    }

    .cat-desc {
        font-size: 13px;
        color: #475569;
        line-height: 1.45;
        margin: 0;
    }

    /* Hàng 3: Nút bấm cảm ứng lớn */
    .card-row-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 2px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }

    .form-mobile-delete {
        margin: 0;
        width: 100%;
    }

    .btn-mobile-action {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: 0.15s;
        box-sizing: border-box;
    }

    .btn-mobile-edit {
        background: #f0f7ff;
        color: #0071e3;
        border: 1px solid #cce4ff;
    }

    .btn-mobile-edit:hover, .btn-mobile-edit:active {
        background: #e0efff;
    }

    .btn-mobile-delete {
        background: #fff5f5;
        color: #e53e3e;
        border: 1px solid #fed7d7;
    }

    .btn-mobile-delete:hover, .btn-mobile-delete:active {
        background: #fee2e2;
    }
}
</style>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
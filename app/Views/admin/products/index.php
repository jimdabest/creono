<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- CSS chuyên biệt cho trang Quản lý Sản phẩm -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/admin-products.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Quản lý Sản phẩm</span>
            </nav>
            <h1 class="admin-title">Quản lý Sản phẩm</h1>
            <p class="admin-subtitle">Kiểm duyệt, quản lý và giám sát tất cả tài liệu số trên Creono</p>
        </div>
        <div class="admin-actions">
            <a href="<?php echo URLROOT; ?>/adminProductController/create" class="btn btn-success" id="btn-add-product">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm sản phẩm
            </a>
        </div>
    </div>

    <!-- Thống kê nhanh -->
    <div class="ap-stats-row mb-4">
        <div class="ap-stat-chip">
            <span class="ap-stat-icon ap-stat-icon--total">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </span>
            <span class="ap-stat-value"><?php echo number_format($data['total_products']); ?></span>
            <span class="ap-stat-label">Tổng sản phẩm</span>
        </div>
        <div class="ap-stat-chip">
            <span class="ap-stat-icon ap-stat-icon--pending">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </span>
            <span class="ap-stat-value"><?php echo number_format($data['pending_count']); ?></span>
            <span class="ap-stat-label">Chờ duyệt</span>
        </div>
        <div class="ap-stat-chip">
            <span class="ap-stat-icon ap-stat-icon--approved">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </span>
            <span class="ap-stat-value"><?php echo number_format($data['approved_count']); ?></span>
            <span class="ap-stat-label">Đã duyệt</span>
        </div>
        <div class="ap-stat-chip">
            <span class="ap-stat-icon ap-stat-icon--rejected">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </span>
            <span class="ap-stat-value"><?php echo number_format($data['rejected_count']); ?></span>
            <span class="ap-stat-label">Từ chối</span>
        </div>
    </div>

    <!-- Thanh tìm kiếm & lọc -->
    <div class="ap-toolbar mb-3">
        <div class="ap-search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="ap-search-input" placeholder="Tìm theo tiêu đề, cửa hàng..." autocomplete="off">
        </div>
        <div class="ap-filter-group">
            <select id="ap-filter-status" class="ap-select">
                <option value="">Tất cả trạng thái</option>
                <option value="1">Chờ duyệt</option>
                <option value="2">Đã duyệt</option>
                <option value="3">Từ chối</option>
            </select>
        </div>
    </div>

    <!-- Bảng danh sách sản phẩm -->
    <div class="admin-card">
        <div class="card-header flex-between">
            <h3>Danh sách sản phẩm (<span id="ap-visible-count"><?php echo count($data['products']); ?></span>)</h3>
            <span class="badge badge-light">Sắp xếp: Mới nhất</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="ap-products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Cửa hàng</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody id="ap-products-tbody">
                    <?php if (!empty($data['products'])) : ?>
                        <?php foreach ($data['products'] as $prod) : ?>
                            <tr id="ap-row-<?php echo $prod->id; ?>"
                                data-title="<?php echo htmlspecialchars(strtolower($prod->title)); ?>"
                                data-store="<?php echo htmlspecialchars(strtolower($prod->store_name)); ?>"
                                data-status="<?php echo $prod->status; ?>">
                                <td><span class="text-muted">#<?php echo $prod->id; ?></span></td>
                                <td>
                                    <div class="ap-product-cell">
                                        <?php if (!empty($prod->preview_url)) : ?>
                                            <img src="<?php echo URLROOT . '/' . htmlspecialchars($prod->preview_url); ?>"
                                                 alt="Preview" class="ap-product-thumb">
                                        <?php else : ?>
                                            <div class="ap-product-thumb ap-thumb-placeholder">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                    <polyline points="14 2 14 8 20 8"></polyline>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div class="ap-product-info">
                                            <strong><?php echo htmlspecialchars($prod->title); ?></strong>
                                            <small class="text-muted">
                                                ⭐ <?php echo number_format($prod->rating, 1); ?>
                                                (<?php echo $prod->review_count; ?>)
                                                · <?php echo $prod->download_count; ?> lượt tải
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?php echo htmlspecialchars($prod->store_name); ?></span>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($prod->seller_name); ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-light"><?php echo htmlspecialchars($prod->category_name ?? 'Chưa phân loại'); ?></span>
                                </td>
                                <td>
                                    <strong class="text-green"><?php echo number_format($prod->price, 0, ',', '.'); ?>đ</strong>
                                </td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        1 => ['Chờ duyệt', 'ap-status--pending'],
                                        2 => ['Đã duyệt', 'ap-status--approved'],
                                        3 => ['Từ chối', 'ap-status--rejected']
                                    ];
                                    $statusInfo = $statusMap[$prod->status] ?? ['Unknown', 'ap-status--pending'];
                                    ?>
                                    <span class="ap-status-badge <?php echo $statusInfo[1]; ?>"
                                          id="ap-status-<?php echo $prod->id; ?>">
                                        <?php echo $statusInfo[0]; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted font-sm"><?php echo date('d/m/Y', strtotime($prod->created_at)); ?></span>
                                </td>
                                <td class="text-right">
                                    <div class="ap-action-group">
                                        <!-- Nút chỉnh sửa -->
                                        <a href="<?php echo URLROOT; ?>/adminProductController/edit/<?php echo $prod->id; ?>"
                                           class="btn-action btn-action-edit" title="Chỉnh sửa">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            Sửa
                                        </a>

                                        <!-- Nút duyệt (chỉ khi Pending hoặc Rejected) -->
                                        <?php if ($prod->status != 2) : ?>
                                            <button type="button"
                                                    class="btn-action ap-btn-approve"
                                                    data-product-id="<?php echo $prod->id; ?>"
                                                    data-product-title="<?php echo htmlspecialchars($prod->title, ENT_QUOTES); ?>"
                                                    title="Phê duyệt">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                                Duyệt
                                            </button>
                                        <?php endif; ?>

                                        <!-- Nút từ chối (chỉ khi Pending hoặc Approved) -->
                                        <?php if ($prod->status != 3) : ?>
                                            <button type="button"
                                                    class="btn-action ap-btn-reject"
                                                    data-product-id="<?php echo $prod->id; ?>"
                                                    data-product-title="<?php echo htmlspecialchars($prod->title, ENT_QUOTES); ?>"
                                                    title="Từ chối">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                                Từ chối
                                            </button>
                                        <?php endif; ?>

                                        <!-- Nút xóa -->
                                        <button type="button"
                                                class="btn-action btn-action-delete ap-btn-delete"
                                                data-product-id="<?php echo $prod->id; ?>"
                                                data-product-title="<?php echo htmlspecialchars($prod->title, ENT_QUOTES); ?>"
                                                title="Xóa sản phẩm">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Chưa có sản phẩm nào trong hệ thống.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal từ chối (nhập lý do) -->
<div class="ap-modal-overlay" id="ap-reject-modal">
    <div class="ap-modal">
        <div class="ap-modal-header">
            <h3>Từ chối sản phẩm</h3>
            <button type="button" class="ap-modal-close" id="ap-reject-modal-close">&times;</button>
        </div>
        <div class="ap-modal-body">
            <div class="ap-modal-icon ap-modal-icon--warning">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <p>Bạn đang từ chối sản phẩm:</p>
            <p class="ap-modal-product-name" id="ap-reject-product-name"></p>
            <div class="ap-form-group">
                <label class="ap-form-label">Lý do từ chối <span class="ap-required">*</span></label>
                <textarea id="ap-reject-note" class="ap-form-textarea" rows="3" placeholder="Nhập lý do từ chối đăng tải sản phẩm này..." required></textarea>
            </div>
            <input type="hidden" id="ap-reject-product-id" value="">
        </div>
        <div class="ap-modal-footer">
            <button type="button" class="btn btn-outline" id="ap-reject-cancel-btn">Hủy bỏ</button>
            <button type="button" class="btn ap-btn-reject-confirm" id="ap-reject-confirm-btn">Xác nhận từ chối</button>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="ap-modal-overlay" id="ap-delete-modal">
    <div class="ap-modal">
        <div class="ap-modal-header">
            <h3>Xác nhận xóa sản phẩm</h3>
            <button type="button" class="ap-modal-close" id="ap-delete-modal-close">&times;</button>
        </div>
        <div class="ap-modal-body">
            <div class="ap-modal-icon ap-modal-icon--danger">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            </div>
            <p>Bạn có chắc chắn muốn <strong>xóa</strong> sản phẩm</p>
            <p class="ap-modal-product-name" id="ap-delete-product-name"></p>
            <p class="ap-modal-warning">Sản phẩm sẽ bị xóa mềm và không hiển thị trên chợ.</p>
            <input type="hidden" id="ap-delete-product-id" value="">
        </div>
        <div class="ap-modal-footer">
            <button type="button" class="btn btn-outline" id="ap-delete-cancel-btn">Hủy bỏ</button>
            <button type="button" class="btn btn-danger" id="ap-delete-confirm-btn">Xóa sản phẩm</button>
        </div>
    </div>
</div>

<!-- CSRF Token ẩn cho JS AJAX -->
<input type="hidden" id="ap-csrf-token" value="<?php echo $_SESSION['csrf_token']; ?>">

<!-- JS Module xử lý AJAX -->
<script src="<?php echo URLROOT; ?>/js/modules/admin-products.js?v=<?php echo time(); ?>"></script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

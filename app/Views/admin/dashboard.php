<?php

/** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <!-- Header Admin Dashboard -->
    <div class="admin-header flex-between mb-4">
        <div>
            <span class="badge badge-primary mb-2">System Administrator</span>
            <h1 class="admin-title">Dashboard Quản Trị</h1>
            <p class="admin-subtitle">Tổng quan chỉ số hoạt động và hiệu suất hệ thống Creono</p>
        </div>
        <div class="admin-actions">
            <a href="<?php echo URLROOT; ?>/admin/categories" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                Quản lý Danh mục
            </a>
        </div>
    </div>

    <!-- 4 Thẻ Thống Kê (Bento Metric Cards) -->
    <div class="admin-stats-grid mb-5">
        <div class="stat-card bento-box bento-stat">
            <div class="stat-icon-wrapper bg-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Tổng người dùng</span>
                <span class="stat-number"><?php echo number_format($data['total_users']); ?></span>
                <span class="stat-desc">Buyer, Seller & Admin</span>
            </div>
        </div>

        <div class="stat-card bento-box bento-stat">
            <div class="stat-icon-wrapper bg-purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Tổng sản phẩm</span>
                <span class="stat-number"><?php echo number_format($data['total_products']); ?></span>
                <span class="stat-desc">Đã kiểm duyệt thành công</span>
            </div>
        </div>

        <div class="stat-card bento-box bento-stat">
            <div class="stat-icon-wrapper bg-amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Đơn hàng hoàn tất</span>
                <span class="stat-number"><?php echo number_format($data['total_orders']); ?></span>
                <span class="stat-desc">Giao dịch mua bán số</span>
            </div>
        </div>

        <div class="stat-card bento-box bento-stat">
            <div class="stat-icon-wrapper bg-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Tổng doanh thu</span>
                <span class="stat-number text-green"><?php echo number_format($data['total_revenue'], 0, ',', '.'); ?>đ</span>
                <span class="stat-desc">Doanh số toàn hệ thống</span>
            </div>
        </div>
    </div>

    <!-- Các Bảng Thống Kê Chi Tiết (Bento Grid Columns) -->
    <div class="row grid-2-col mb-5">
        <!-- Top Sản phẩm bán chạy -->
        <div class="admin-card">
            <div class="card-header flex-between">
                <h3>Top Sản phẩm bán chạy</h3>
                <span class="badge badge-light">Theo lượt tải & đánh giá</span>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Cửa hàng</th>
                            <th>Giá</th>
                            <th>Lượt tải</th>
                            <th>Đánh giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['top_products'])) : ?>
                            <?php foreach ($data['top_products'] as $prod) : ?>
                                <tr>
                                    <td class="font-medium"><?php echo htmlspecialchars($prod->title); ?></td>
                                    <td><span class="badge badge-secondary"><?php echo htmlspecialchars($prod->store_name); ?></span></td>
                                    <td class="font-semibold"><?php echo number_format($prod->price, 0, ',', '.'); ?>đ</td>
                                    <td><span class="badge badge-success"><?php echo number_format($prod->download_count); ?></span></td>
                                    <td>⭐ <?php echo number_format($prod->rating, 1); ?> (<?php echo $prod->review_count; ?>)</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Chưa có sản phẩm nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Thống kê doanh thu Người bán -->
        <div class="admin-card">
            <div class="card-header flex-between">
                <h3>Doanh thu theo Cửa hàng</h3>
                <span class="badge badge-light">Top Cửa hàng nổi bật</span>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Cửa hàng</th>
                            <th>Đơn hàng</th>
                            <th>SP</th>
                            <th>Doanh thu</th>
                            <th>Phí sàn</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['seller_revenues'])) : ?>
                            <?php foreach ($data['seller_revenues'] as $seller) : ?>
                                <tr>
                                    <td class="font-medium"><?php echo htmlspecialchars($seller->store_name); ?></td>
                                    <td><?php echo number_format($seller->total_orders); ?></td>
                                    <td><?php echo number_format($seller->total_products); ?></td>
                                    <td class="font-semibold text-green"><?php echo number_format($seller->total_revenue, 0, ',', '.'); ?>đ</td>
                                    <td class="text-muted"><?php echo number_format($seller->total_fee, 0, ',', '.'); ?>đ</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Chưa có dữ liệu doanh thu</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Thẻ Truy Cập Nhanh Các Chức Năng Admin -->
    <div class="admin-card mb-4">
        <div class="card-header">
            <h3>Chức năng Quản trị Hệ thống</h3>
        </div>
        <div class="admin-quick-links">
            <a href="<?php echo URLROOT; ?>/admin/categories" class="quick-link-card">
                <div class="link-icon">📁</div>
                <div class="link-info">
                    <h4>Quản lý Danh mục</h4>
                    <p>Thêm, sửa, xóa các danh mục sản phẩm</p>
                </div>
                <span class="link-arrow">&rarr;</span>
            </a>

            <div class="quick-link-card disabled">
                <div class="link-icon">👥</div>
                <div class="link-info">
                    <h4>Quản lý Người dùng</h4>
                    <p>Quản lý tài khoản Buyer, Seller & Phân quyền</p>
                </div>
                <span class="link-badge">Sắp ra mắt</span>
            </div>

            <a href="<?php echo URLROOT; ?>/admin/approvals" class="quick-link-card">
                <div class="link-icon">✅</div>
                <div class="link-info">
                    <h4>Duyệt tài liệu sản phẩm</h4>
                    <p>Phê duyệt hoặc từ chối tài liệu sản phẩm</p>
                </div>
                <?php if (!empty($data['pending_approvals_count'])) : ?>
                    <span class="badge badge-warning" style="margin-left: auto; font-size: 13px; padding: 4px 10px;"><?php echo $data['pending_approvals_count']; ?> chờ duyệt</span>
                <?php else : ?>
                    <span class="link-arrow">&rarr;</span>
                <?php endif; ?>
            </a>

            <a href="<?php echo URLROOT; ?>/admin/reports" class="quick-link-card">
                <div class="link-icon">🚩</div>
                <div class="link-info">
                    <h4>Quản lý Báo cáo vi phạm</h4>
                    <p>Xử lý báo cáo vi phạm & khiếu nại dán nhãn AI</p>
                </div>
                <?php if (!empty($data['pending_reports_count'])) : ?>
                    <span class="badge badge-danger" style="margin-left: auto; font-size: 13px; padding: 4px 10px;"><?php echo $data['pending_reports_count']; ?> chưa xử lý</span>
                <?php else : ?>
                    <span class="link-arrow">&rarr;</span>
                <?php endif; ?>
            </a>

            <!-- Nút 1: Quản lý Rút tiền (UC12) -->
            <a href="<?php echo URLROOT; ?>/admin/withdrawals" class="action-item">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--apple-blue, #0071e3)" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                    <span>Phê duyệt rút tiền</span>
                </div>
            </a>

            <a href="<?php echo URLROOT; ?>/admin/settings" class="action-item">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--apple-gray, #86868b)" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Cấu hình hệ thống</span>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- STYLES RESPONSIVE BỔ SUNG CHO ADMIN DASHBOARD -->
<style>
    /* === RESPONSIVE: TABLET & MOBILE === */

    /* Tablet (max-width: 768px) */
    @media (max-width: 768px) {
        .admin-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 16px !important;
        }

        .admin-actions {
            width: 100% !important;
        }

        .admin-actions .btn-outline {
            width: 100% !important;
            justify-content: center !important;
        }

        .admin-title {
            font-size: 26px !important;
        }

        .admin-subtitle {
            font-size: 14px !important;
        }

        .bento-stat {
            padding: 18px !important;
        }

        .stat-info .stat-number {
            font-size: 24px !important;
        }

        .stat-icon-wrapper {
            width: 44px !important;
            height: 44px !important;
        }

        .admin-card {
            padding: 18px !important;
        }

        .card-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 8px !important;
        }

        .card-header h3 {
            font-size: 18px !important;
        }

        .admin-table th,
        .admin-table td {
            padding: 10px 12px !important;
            font-size: 13px !important;
        }

        .admin-table th {
            font-size: 11px !important;
        }

        .quick-link-card {
            padding: 16px !important;
            gap: 12px !important;
        }

        .link-icon {
            width: 36px !important;
            height: 36px !important;
            font-size: 20px !important;
        }

        .link-info h4 {
            font-size: 14px !important;
        }

        .link-info p {
            font-size: 12px !important;
        }

        .badge {
            font-size: 11px !important;
            padding: 3px 8px !important;
        }
    }

    /* Điện thoại nhỏ (max-width: 480px) */
    @media (max-width: 480px) {
        .container {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .admin-title {
            font-size: 22px !important;
        }

        .admin-subtitle {
            font-size: 13px !important;
        }

        .bento-stat {
            padding: 14px !important;
            gap: 12px !important;
        }

        .stat-info .stat-number {
            font-size: 20px !important;
        }

        .stat-info .stat-label {
            font-size: 12px !important;
        }

        .stat-info .stat-desc {
            font-size: 11px !important;
        }

        .stat-icon-wrapper {
            width: 38px !important;
            height: 38px !important;
        }

        .stat-icon-wrapper svg {
            width: 18px !important;
            height: 18px !important;
        }

        .admin-card {
            padding: 14px !important;
            border-radius: 16px !important;
        }

        .card-header h3 {
            font-size: 16px !important;
        }

        .admin-table th,
        .admin-table td {
            padding: 8px 10px !important;
            font-size: 12px !important;
        }

        .admin-table th {
            font-size: 10px !important;
        }

        .quick-link-card {
            padding: 14px !important;
            gap: 10px !important;
            flex-wrap: wrap !important;
        }

        .link-icon {
            width: 32px !important;
            height: 32px !important;
            font-size: 18px !important;
        }

        .link-info h4 {
            font-size: 13px !important;
        }

        .link-info p {
            font-size: 11px !important;
        }

        .link-arrow,
        .link-badge,
        .badge {
            font-size: 11px !important;
            padding: 2px 6px !important;
        }

        .btn-outline {
            font-size: 13px !important;
            padding: 8px 12px !important;
        }

        .btn-outline svg {
            width: 14px !important;
            height: 14px !important;
        }
    }
</style>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
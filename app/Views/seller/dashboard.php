<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="seller-dashboard">
    <div class="container">
        <!-- ====== DASHBOARD HEADER ====== -->
        <div class="dashboard-header-section">
            <div class="dashboard-title-group">
                <h1 class="dashboard-title">Tổng quan cửa hàng</h1>
                <p class="dashboard-subtitle">Quản lý sản phẩm, theo dõi doanh thu và phát triển kinh doanh của bạn.</p>
            </div>
            <a href="#" class="btn btn-primary btn-create">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Thêm sản phẩm
            </a>
        </div>

        <!-- ====== STATS GRID ====== -->
        <div class="stats-grid-apple">
            <div class="stat-card-apple">
                <div class="stat-icon" style="background: rgba(0, 113, 227, 0.1); color: var(--apple-blue);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20 7h-4.5A2.5 2.5 0 0 0 13 9.5v5a2.5 2.5 0 0 0 2.5 2.5H20"/>
                        <path d="M4 7h4.5A2.5 2.5 0 0 1 11 9.5v5a2.5 2.5 0 0 1-2.5 2.5H4"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Sản phẩm</span>
                    <span class="stat-value"><?php echo $data['total_products'] ?? 0; ?></span>
                </div>
            </div>

            <div class="stat-card-apple">
                <div class="stat-icon" style="background: rgba(52, 199, 89, 0.1); color: var(--apple-green);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 2v4M12 22v-4M4 12H2M6 12H4M20 12h-2M22 12h-2M19.07 4.93l-2.83 2.83M4.93 19.07l2.83-2.83M19.07 19.07l-2.83-2.83M4.93 4.93l2.83 2.83"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Doanh thu</span>
                    <span class="stat-value"><?php echo number_format($data['total_revenue'] ?? 0, 0, ',', '.'); ?>đ</span>
                </div>
            </div>

            <div class="stat-card-apple">
                <div class="stat-icon" style="background: rgba(255, 149, 0, 0.1); color: var(--apple-orange);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Đánh giá</span>
                    <span class="stat-value">⭐ <?php echo number_format($data['avg_rating'] ?? 0, 1); ?></span>
                    <span class="stat-change">(<?php echo $data['total_reviews'] ?? 0; ?> đánh giá)</span>
                </div>
            </div>

            <div class="stat-card-apple">
                <div class="stat-icon" style="background: rgba(255, 59, 48, 0.1); color: var(--apple-red);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M12 8v4M12 16h.01"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Đơn hàng chờ</span>
                    <span class="stat-value"><?php echo $data['pending_orders'] ?? 0; ?></span>
                    <span class="stat-change">cần xử lý</span>
                </div>
            </div>
        </div>

        <!-- ====== RECENT ACTIVITY ====== -->
        <div class="dashboard-grid">
            <!-- Recent Orders -->
            <div class="dashboard-card-apple">
                <div class="card-header">
                    <h3>Đơn hàng gần đây</h3>
                    <a href="#" class="card-link">Xem tất cả →</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['recent_orders'])) : ?>
                        <div class="order-list">
                            <?php foreach ($data['recent_orders'] as $order) : ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <span class="order-id">#<?php echo $order->id; ?></span>
                                        <span class="order-product"><?php echo htmlspecialchars($order->product_title); ?></span>
                                    </div>
                                    <div class="order-meta">
                                        <span class="order-amount"><?php echo number_format($order->amount, 0, ',', '.'); ?>đ</span>
                                        <span class="order-status status-<?php echo $order->status; ?>">
                                            <?php echo $order->status_text; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="empty-state-mini">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--apple-gray-light)" stroke-width="1.5">
                                <rect x="2" y="3" width="20" height="18" rx="2"/>
                                <path d="M8 21V5M16 21V5"/>
                            </svg>
                            <p>Chưa có đơn hàng nào</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Products -->
            <div class="dashboard-card-apple">
                <div class="card-header">
                    <h3>Sản phẩm bán chạy</h3>
                    <a href="#" class="card-link">Xem tất cả →</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['top_products'])) : ?>
                        <div class="product-list">
                            <?php foreach ($data['top_products'] as $index => $product) : ?>
                                <div class="product-item">
                                    <div class="product-rank">#<?php echo $index + 1; ?></div>
                                    <div class="product-info">
                                        <span class="product-name"><?php echo htmlspecialchars($product->title); ?></span>
                                        <span class="product-sales"><?php echo $product->sales_count ?? 0; ?> lượt tải</span>
                                    </div>
                                    <span class="product-price"><?php echo number_format($product->price, 0, ',', '.'); ?>đ</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="empty-state-mini">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--apple-gray-light)" stroke-width="1.5">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                            <p>Chưa có sản phẩm nào</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ====== QUICK ACTIONS ====== -->
        <div class="quick-actions">
            <h3>Tiện ích nhanh</h3>
            <div class="action-grid">
                <a href="#" class="action-card">
                    <div class="action-icon" style="background: rgba(0, 113, 227, 0.08);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--apple-blue)" stroke-width="1.5">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                    </div>
                    <span>Đăng sản phẩm mới</span>
                </a>
                <a href="#" class="action-card">
                    <div class="action-icon" style="background: rgba(52, 199, 89, 0.08);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--apple-green)" stroke-width="1.5">
                            <path d="M12 2v4M12 22v-4M4 12H2M6 12H4M20 12h-2M22 12h-2M19.07 4.93l-2.83 2.83M4.93 19.07l2.83-2.83M19.07 19.07l-2.83-2.83M4.93 4.93l2.83 2.83"/>
                        </svg>
                    </div>
                    <span>Quản lý sản phẩm</span>
                </a>
                <a href="#" class="action-card">
                    <div class="action-icon" style="background: rgba(255, 149, 0, 0.08);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--apple-orange)" stroke-width="1.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <span>Hồ sơ cửa hàng</span>
                </a>
                <a href="#" class="action-card">
                    <div class="action-icon" style="background: rgba(255, 59, 48, 0.08);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--apple-red)" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                    </div>
                    <span>Thống kê chi tiết</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
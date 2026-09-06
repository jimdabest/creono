<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="seller-dashboard">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
            <h1 style="font-size: 28px; font-weight: 700;">Thống kê chi tiết</h1>
            <a href="<?php echo URLROOT; ?>/seller/dashboard" class="btn btn-secondary" style="width: auto; padding: 8px 20px;">Quay lại Dashboard</a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid-apple" style="margin-bottom: 40px;">
            <div class="stat-card-apple">
                <div class="stat-icon" style="background: rgba(0,113,227,0.1); color: var(--apple-blue);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 7h-4.5A2.5 2.5 0 0 0 13 9.5v5a2.5 2.5 0 0 0 2.5 2.5H20"/><path d="M4 7h4.5A2.5 2.5 0 0 1 11 9.5v5a2.5 2.5 0 0 1-2.5 2.5H4"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Sản phẩm</span>
                    <span class="stat-value"><?php echo $data['total_products']; ?></span>
                </div>
            </div>
            <div class="stat-card-apple">
                <div class="stat-icon" style="background: rgba(52,199,89,0.1); color: var(--apple-green);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v4M12 22v-4M4 12H2M6 12H4M20 12h-2M22 12h-2M19.07 4.93l-2.83 2.83M4.93 19.07l2.83-2.83M19.07 19.07l-2.83-2.83M4.93 4.93l2.83 2.83"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Doanh thu</span>
                    <span class="stat-value"><?php echo number_format($data['total_revenue'], 0, ',', '.'); ?>đ</span>
                </div>
            </div>
            <div class="stat-card-apple">
                <div class="stat-icon" style="background: rgba(255,149,0,0.1); color: var(--apple-orange);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Đánh giá</span>
                    <span class="stat-value">⭐ <?php echo number_format($data['avg_rating'], 1); ?></span>
                    <span class="stat-change">(<?php echo $data['total_reviews']; ?> đánh giá)</span>
                </div>
            </div>
            <div class="stat-card-apple">
                <div class="stat-icon" style="background: rgba(255,59,48,0.1); color: var(--apple-red);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4M12 16h.01"/></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Đơn hàng chờ</span>
                    <span class="stat-value"><?php echo $data['pending_orders']; ?></span>
                </div>
            </div>
        </div>

        <!-- Top Products Table -->
        <div style="background: #fff; border-radius: 16px; border: 1px solid rgba(0,0,0,0.08); padding: 24px; margin-bottom: 32px;">
            <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 16px;">Sản phẩm bán chạy</h3>
            <?php if (!empty($data['top_products'])): ?>
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead style="background: #f5f5f7;">
                            <tr>
                                <th style="padding: 10px 12px; text-align: left;">#</th>
                                <th style="padding: 10px 12px; text-align: left;">Tên sản phẩm</th>
                                <th style="padding: 10px 12px; text-align: left;">Giá</th>
                                <th style="padding: 10px 12px; text-align: left;">Lượt tải</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['top_products'] as $index => $product): ?>
                                <tr style="border-bottom: 1px solid rgba(0,0,0,0.04);">
                                    <td style="padding: 10px 12px;"><?php echo $index + 1; ?></td>
                                    <td style="padding: 10px 12px;"><?php echo htmlspecialchars($product->title); ?></td>
                                    <td style="padding: 10px 12px;"><?php echo number_format($product->price, 0, ',', '.'); ?>đ</td>
                                    <td style="padding: 10px 12px;"><?php echo $product->sales_count ?? 0; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #86868b;">Chưa có sản phẩm nào được bán.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
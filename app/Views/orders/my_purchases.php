<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px;">

    <h1 style="font-size: 36px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: #1d1d1f;">
        Kho tài liệu của tôi
    </h1>
    <p style="font-size: 15px; color: #86868b; margin-bottom: 32px;">
        Danh sách tất cả các tài liệu bạn đã thanh toán thành công. Bạn có thể tải xuống sử dụng bất cứ lúc nào.
    </p>

    <?php if (!empty($data['purchases'])) : ?>
        <div class="product-grid">
            <?php foreach ($data['purchases'] as $item) : ?>
                <div class="product-card interactive-hover" style="border-radius: 24px; display: flex; flex-direction: column;">
                    
                    <div class="product-image-wrapper" style="border-radius: 24px 24px 0 0;">
                        <div class="product-placeholder">Đã mua</div>
                        <span class="product-badge" style="background: #27ae60; color: #fff;">✅ Thành công</span>
                    </div>
                    
                    <div class="product-content" style="flex-grow: 1;">
                        <h3 class="product-title" style="margin-bottom: 6px;">
                            <?php echo htmlspecialchars($item->title); ?>
                        </h3>
                        <div style="font-size: 13px; color: #86868b; margin-bottom: 4px;">
                            🏪 Tác giả: <strong><?php echo htmlspecialchars($item->store_name); ?></strong>
                        </div>
                        <div style="font-size: 12px; color: #86868b;">
                            📅 Ngày mua: <?php echo date('d/m/Y H:i', strtotime($item->purchased_at)); ?>
                        </div>
                    </div>

                    <div class="product-footer" style="padding: 16px; border-top: 1px solid rgba(0,0,0,0.06); display: flex; gap: 8px;">
                        <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $item->product_id; ?>" 
                           class="btn btn-secondary" 
                           style="flex: 1; text-align: center; padding: 10px; font-size: 13px; border-radius: 12px;">
                            Xem chi tiết
                        </a>
                        
                        <a href="<?php echo URLROOT; ?>/downloads/file/<?php echo $item->product_id; ?>" 
                           class="btn btn-primary" 
                           style="flex: 1; text-align: center; padding: 10px; font-size: 13px; font-weight: 600; background: #0071e3; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Tải file
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="empty-state" style="text-align: center; padding: 80px 24px; background: #f9f9fb; border-radius: 24px; border: 1px dashed rgba(0,0,0,0.1);">
            <div style="font-size: 56px; margin-bottom: 16px; opacity: 0.5;">📚</div>
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 8px;">Kho tài liệu trống</h3>
            <p style="font-size: 15px; color: #86868b; margin-bottom: 24px;">Bạn chưa mua tài liệu nào trên hệ thống.</p>
            <a href="<?php echo URLROOT; ?>/products/index" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 14px; background: var(--apple-blue, #0071e3); color: #fff; text-decoration: none; display: inline-block;">
                Đến chợ tài liệu
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
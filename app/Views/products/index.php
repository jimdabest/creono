<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container">
    <div class="page-header">
        <h2>Khám phá Tài liệu nổi bật</h2>
        <div class="search-bar">
            <input type="text" placeholder="Tìm kiếm tài liệu, mã nguồn...">
            <button class="btn-search">🔍</button>
        </div>
    </div>

    <div class="product-grid">
        <?php if(!empty($data['products'])) : ?>
            <?php foreach($data['products'] as $product) : ?>
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <!-- Tạm thời dùng div trống làm placeholder, sau này anh thay bằng thẻ <img> -->
                        <div class="product-placeholder">Ảnh Preview</div>
                        <span class="product-badge"><?php echo htmlspecialchars($product->store_name); ?></span>
                    </div>
                    
                    <div class="product-content">
                        <h3 class="product-title"><?php echo htmlspecialchars($product->title); ?></h3>
                        <p class="product-desc"><?php echo htmlspecialchars($product->description ?? 'Chưa có mô tả'); ?></p>
                    </div>

                    <div class="product-footer">
                        <span class="product-price"><?php echo number_format($product->price, 0, ',', '.'); ?> ₫</span>
                        <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" class="btn btn-outline">Xem chi tiết</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="empty-state">
                <p>Hiện chưa có tài liệu nào được đăng tải lên hệ thống.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
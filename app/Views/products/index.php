<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container" style="margin-top: 30px; min-height: 60vh;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Khám phá Tài liệu nổi bật</h2>
        <!-- Thanh tìm kiếm nhanh -->
        <input type="text" placeholder="Tìm kiếm tài liệu, mã nguồn..." style="padding: 8px 15px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <!-- Lưới hiển thị sản phẩm (CSS Grid) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
        
        <?php if(!empty($data['products'])) : ?>
            <?php foreach($data['products'] as $product) : ?>
                <div class="card" style="margin: 0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="background: #f4f4f4; height: 140px; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 15px; color: #888;">
                            <span>[Ảnh Preview]</span>
                        </div>
                        <span style="font-size: 12px; color: #666; background: #e9ecef; padding: 3px 8px; border-radius: 3px;"><?php echo htmlspecialchars($product->store_name); ?></span>
                        <h3 style="font-size: 18px; margin: 10px 0;"><?php echo htmlspecialchars($product->title); ?></h3>
                        <p style="font-size: 14px; color: #555; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($product->description); ?></p>
                    </div>

                    <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 18px; font-weight: bold; color: var(--primary-color);">
                            <?php echo number_format($product->price, 0, ',', '.'); ?> đ
                        </span>
                        <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" class="btn" style="background: #007bff; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 14px;">Xem chi tiết</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Hiện chưa có tài liệu nào được đăng tải lên hệ thống.</p>
        <?php endif; ?>

    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
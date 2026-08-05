<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- ============================== -->
<!-- HERO SECTION (Apple Product Launch Style) -->
<!-- ============================== -->
<section class="hero-section text-center">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">
                Tài nguyên số.<br>
                <span class="text-gradient">Nâng tầm dự án của bạn.</span>
            </h1>
            <p class="hero-desc">
                Khám phá hàng ngàn mã nguồn, thiết kế và tài liệu chất lượng cao từ cộng đồng sáng tạo. Mua bán an toàn, tải xuống tức thì.
            </p>
            
            <div class="hero-buttons">
                <a href="<?php echo URLROOT; ?>/products/index" class="btn btn-hero">Khám phá chợ tài liệu</a>
                <a href="<?php echo URLROOT; ?>/users/register" class="btn btn-outline btn-hero" style="border-color: transparent; background: rgba(0,0,0,0.05); color: var(--apple-black);">Trở thành người bán <span style="font-size: 14px;">↗</span></a>
            </div>
        </div>
    </div>
</section>

<!-- ============================== -->
<!-- BENTO STATS & HIGHLIGHTS -->
<!-- ============================== -->
<section class="bento-section">
    <div class="container">
        <div class="bento-grid">
            <!-- Box 1: Tổng sản phẩm (To) -->
            <div class="bento-box bento-large bento-gradient-1">
                <div class="bento-content">
                    <h3 class="bento-title">Kho tài liệu khổng lồ</h3>
                    <p class="bento-text">Hơn <strong><?php echo number_format($data['stats']['products']); ?></strong> tài liệu kỹ thuật số, từ mã nguồn, UI/UX đến báo cáo nghiên cứu.</p>
                </div>
            </div>
            
            <!-- Box 2: User -->
            <div class="bento-box bento-medium">
                <div class="bento-content text-center">
                    <span class="stat-number"><?php echo number_format($data['stats']['users']); ?>+</span>
                    <span class="stat-label">Người dùng tin tưởng</span>
                </div>
            </div>

            <!-- Box 3: Sellers -->
            <div class="bento-box bento-medium">
                <div class="bento-content text-center">
                    <span class="stat-number"><?php echo number_format($data['stats']['sellers']); ?></span>
                    <span class="stat-label">Nhà sáng tạo nội dung</span>
                </div>
            </div>
            
            <!-- Box 4: Đánh giá -->
            <div class="bento-box bento-wide bento-dark">
                <div class="bento-content">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 class="bento-title" style="color: #fff;">Chất lượng được kiểm chứng</h3>
                            <p class="bento-text" style="color: rgba(255,255,255,0.7);">Điểm đánh giá trung bình toàn hệ thống</p>
                        </div>
                        <div class="stat-number" style="color: #fff; font-size: 48px;">
                            <?php echo $data['stats']['rating']; ?> <span style="font-size: 24px; color: #ffb800;">★</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================== -->
<!-- TÀI LIỆU NỔI BẬT (Featured Products) -->
<!-- ============================== -->
<?php if(!empty($data['featured_products'])) : ?>
<section class="featured-section">
    <div class="container">
        <div class="section-header">
            <h2>Được lựa chọn cho bạn.</h2>
            <a href="<?php echo URLROOT; ?>/products/index" class="view-all">Xem tất cả tài liệu <span style="font-size: 12px;">↗</span></a>
        </div>
        
        <div class="product-grid-home">
            <?php 
            $count = 0;
            foreach($data['featured_products'] as $product) : 
                if($count++ >= 4) break;
            ?>
                <div class="product-card interactive-hover">
                    <div class="product-image-wrapper">
                        <div class="product-placeholder">Preview</div>
                        <span class="product-badge"><?php echo htmlspecialchars($product->store_name); ?></span>
                        <?php if($product->rating > 0) : ?>
                            <span class="product-rating">
                                ★ <?php echo number_format($product->rating, 1); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-content">
                        <h3 class="product-title"><?php echo htmlspecialchars($product->title); ?></h3>
                        <p class="product-desc"><?php echo htmlspecialchars($product->description ?? 'Tài liệu số chất lượng cao được kiểm duyệt trên Creono.'); ?></p>
                    </div>

                    <div class="product-footer">
                        <span class="product-price"><?php echo number_format($product->price, 0, ',', '.'); ?> ₫</span>
                        <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" class="btn btn-outline" style="border: none; background: var(--apple-gray-bg); color: var(--apple-black); font-weight: 500;">Chi tiết</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================== -->
<!-- DANH MỤC (Categories) -->
<!-- ============================== -->
<?php if(!empty($data['categories'])) : ?>
<section class="categories-section" style="background: #fff; padding: 80px 0;">
    <div class="container">
        <div class="section-header" style="text-align: left; margin-bottom: 32px;">
            <h2>Khám phá theo chủ đề.</h2>
        </div>
        
        <div class="categories-scroll">
            <div class="categories-flex">
                <?php foreach($data['categories'] as $category) : ?>
                    <a href="<?php echo URLROOT; ?>/products/index?category=<?php echo $category->slug; ?>" class="category-pill">
                        <span class="category-name"><?php echo htmlspecialchars($category->name); ?></span>
                        <span class="category-count"><?php echo $category->product_count; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================== -->
<!-- CTA SECTION (Đã tối ưu kiểu Premium Card) -->
<!-- ============================== -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Biến kiến thức thành thu nhập.</h2>
            <p>Trở thành người bán trên Creono ngay hôm nay. Bắt đầu chia sẻ mã nguồn, đồ án, tài liệu và nhận thanh toán an toàn.</p>
            <a href="<?php echo URLROOT; ?>/users/register" class="btn btn-large">Mở cửa hàng miễn phí</a>
        </div>
    </div>
</section>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
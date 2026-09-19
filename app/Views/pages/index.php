<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- ============================== -->
<!-- HERO SECTION (Apple Product Launch Style) - GIỮ NGUYÊN -->
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
<!-- BENTO STATS & HIGHLIGHTS - GIỮ NGUYÊN -->
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
<!-- FEATURED PRODUCTS - APPLE STYLE GRID (TOP 10) -->
<!-- ============================== -->
<?php if(!empty($data['featured_products'])) : ?>
<?php
// Lấy top 10 sản phẩm
$topProducts = array_slice($data['featured_products'], 0, 10);
// Tách 2 sản phẩm đầu cho hàng trên (ô lớn), 8 sản phẩm còn lại cho hàng dưới (ô nhỏ)
$heroProducts = array_slice($topProducts, 0, 2);
$gridProducts = array_slice($topProducts, 2, 8);
?>
<section class="apple-showcase-section">
    <div class="container">
        
        <!-- Tiêu đề section -->
        <div class="apple-showcase-header">
            <h2>Được lựa chọn cho bạn.</h2>
            <p class="apple-showcase-subtitle">Khám phá những tài liệu số nổi bật nhất trên Creono.</p>
        </div>

        <!-- Hàng trên: 2 ô lớn -->
        <?php if (!empty($heroProducts)) : ?>
        <div class="apple-grid-large">
            <?php foreach ($heroProducts as $product) : ?>
                <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" class="apple-card apple-card-large">
                    <div class="apple-card-bg" style="background-image: url('<?php echo !empty($product->preview_url) ? URLROOT . htmlspecialchars($product->preview_url) : ''; ?>');">
                        <?php if (empty($product->preview_url)): ?>
                            <div class="apple-card-placeholder">Preview</div>
                        <?php endif; ?>
                    </div>
                    <div class="apple-card-overlay"></div>
                    <div class="apple-card-content">
                        <span class="apple-card-store"><?php echo htmlspecialchars($product->store_name); ?></span>
                        <h3 class="apple-card-title"><?php echo htmlspecialchars($product->title); ?></h3>
                        <p class="apple-card-desc"><?php echo htmlspecialchars(mb_substr($product->description ?? 'Tài liệu số chất lượng cao.', 0, 100)); ?>...</p>
                        <div class="apple-card-footer">
                            <span class="apple-card-price"><?php echo number_format($product->price, 0, ',', '.'); ?> ₫</span>
                            <?php if($product->rating > 0) : ?>
                                <span class="apple-card-rating">★ <?php echo number_format($product->rating, 1); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Hàng dưới: các ô nhỏ -->
        <?php if (!empty($gridProducts)) : ?>
        <div class="apple-grid-small">
            <?php foreach ($gridProducts as $product) : ?>
                <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" class="apple-card apple-card-small">
                    <div class="apple-card-bg" style="background-image: url('<?php echo !empty($product->preview_url) ? URLROOT . htmlspecialchars($product->preview_url) : ''; ?>');">
                        <?php if (empty($product->preview_url)): ?>
                            <div class="apple-card-placeholder">Preview</div>
                        <?php endif; ?>
                    </div>
                    <div class="apple-card-overlay"></div>
                    <div class="apple-card-content">
                        <span class="apple-card-store"><?php echo htmlspecialchars($product->store_name); ?></span>
                        <h4 class="apple-card-title"><?php echo htmlspecialchars($product->title); ?></h4>
                        <div class="apple-card-footer">
                            <span class="apple-card-price"><?php echo number_format($product->price, 0, ',', '.'); ?> ₫</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>
<?php endif; ?>
<!-- ============================== -->
<!-- DANH MỤC (Categories) - GIỮ NGUYÊN DẠNG PILL -->
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
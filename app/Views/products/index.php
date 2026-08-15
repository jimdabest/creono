<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 60px;">
    <!-- ============================== -->
    <!-- MARKET HEADER & SPOTLIGHT SEARCH -->
    <!-- ============================== -->
    <div class="market-header text-center" style="margin-bottom: 40px; max-width: 700px; margin-left: auto; margin-right: auto;">
        <h2 style="font-size: 44px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 24px;">Khám phá kho tài liệu.</h2>
        
        <div class="spotlight-search">
            <svg class="search-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" placeholder="Tìm kiếm mã nguồn, đồ án, template...">
            <button class="btn-search-clear" aria-label="Xóa">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- ============================== -->
    <!-- CATEGORY PILL FILTERS -->
    <!-- ============================== -->
    <div class="categories-scroll" style="margin-bottom: 48px;">
        <div class="categories-flex" style="justify-content: center;">
            <a href="#" class="category-pill" style="background: var(--apple-black); color: #fff; border-color: var(--apple-black);">
                <span class="category-name" style="color: #fff;">Tất cả</span>
            </a>
            <a href="#" class="category-pill">
                <span class="category-name">Lập trình</span>
            </a>
            <a href="#" class="category-pill">
                <span class="category-name">Thiết kế UI/UX</span>
            </a>
            <a href="#" class="category-pill">
                <span class="category-name">Đồ án Đại học</span>
            </a>
            <a href="#" class="category-pill">
                <span class="category-name">Khóa học</span>
            </a>
        </div>
    </div>

    <!-- ============================== -->
    <!-- PRODUCT GRID -->
    <!-- ============================== -->
    <div class="product-grid">
        <?php if(!empty($data['products'])) : ?>
            <?php foreach($data['products'] as $product) : ?>
                <div class="product-card interactive-hover" style="border-radius: 24px; display: flex; flex-direction: column; overflow: hidden;">
                    
                    <!-- Nhấp vào thân thẻ để xem chi tiết sản phẩm -->
                    <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; flex-grow: 1;">
                        <div class="product-image-wrapper" style="border-radius: 24px 24px 0 0;">
                            <div class="product-placeholder">Preview</div>
                            <span class="product-badge"><?php echo htmlspecialchars($product->store_name); ?></span>
                            <?php if($product->rating > 0) : ?>
                                <span class="product-rating">
                                    ★ <?php echo number_format($product->rating, 1); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-content" style="flex-grow: 1;">
                            <h3 class="product-title"><?php echo htmlspecialchars($product->title); ?></h3>
                            <p class="product-desc"><?php echo htmlspecialchars($product->description ?? 'Tài liệu số chất lượng cao được kiểm duyệt trên Creono.'); ?></p>
                        </div>
                    </a>

                    <!-- Khu vực giá & Nút Mua ngay ôm sát chữ -->
                    <div class="product-footer" style="padding: 12px 18px; border-top: 1px solid rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span class="product-price" style="font-size: 15px; font-weight: 700; color: #2c3e50; white-space: nowrap; flex-shrink: 0;">
                            <?php echo number_format($product->price, 0, ',', '.'); ?>&nbsp;₫
                        </span>
                        
                        <a href="<?php echo URLROOT; ?>/orders/checkout/<?php echo $product->id; ?>" 
                           class="btn btn-primary" 
                           style="width: fit-content; min-width: auto; padding: 4px 10px; font-size: 12px; font-weight: 600; text-decoration: none; border-radius: 980px; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; flex-shrink: 0; line-height: 1.2;">
                            Mua ngay
                        </a>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="empty-state" style="border-radius: 24px; padding: 64px 24px;">
                <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.4;">📦</div>
                <h3 style="margin-bottom: 8px;">Chưa có tài liệu</h3>
                <p>Hiện chưa có tài liệu nào được đăng tải lên hệ thống.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
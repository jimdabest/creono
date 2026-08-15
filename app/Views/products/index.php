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
                <?php $isFav = isset($data['favorite_ids']) && in_array((int)$product->id, $data['favorite_ids']); ?>
                <div class="product-card interactive-hover" style="border-radius: 24px; display: flex; flex-direction: column; overflow: hidden; position: relative;">
                    
                    <!-- Favorite Heart Button (UC17) -->
                    <button type="button" class="btn-fav-toggle" data-product-id="<?php echo $product->id; ?>" style="position: absolute; top: 12px; right: 12px; z-index: 10; background: rgba(255,255,255,0.85); border: none; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s ease;" title="<?php echo $isFav ? 'Bỏ yêu thích' : 'Yêu thích'; ?>">
                        <svg class="fav-heart-icon" width="18" height="18" viewBox="0 0 24 24" fill="<?php echo $isFav ? '#ff3b30' : 'none'; ?>" stroke="<?php echo $isFav ? '#ff3b30' : '#666'; ?>" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>

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

                    <!-- Khu vực giá & Các nút hành động -->
                    <div class="product-footer" style="padding: 12px 18px; border-top: 1px solid rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span class="product-price" style="font-size: 15px; font-weight: 700; color: #2c3e50; white-space: nowrap; flex-shrink: 0;">
                            <?php echo number_format($product->price, 0, ',', '.'); ?>&nbsp;₫
                        </span>
                        
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <!-- Add to Cart Mini Button (UC18) -->
                            <button type="button" class="btn-cart-add-mini" data-product-id="<?php echo $product->id; ?>" style="background: var(--apple-blue, #0071e3); border: none; color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s ease;" title="Thêm vào giỏ hàng">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            </button>

                            <!-- Nút Mua ngay ôm sát chữ -->
                            <a href="<?php echo URLROOT; ?>/orders/checkout/<?php echo $product->id; ?>" 
                               class="btn btn-primary" 
                               style="width: fit-content; min-width: auto; padding: 6px 12px; font-size: 12px; font-weight: 600; text-decoration: none; border-radius: 980px; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; flex-shrink: 0; line-height: 1.2;">
                                Mua ngay
                            </a>
                        </div>
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

<!-- AJAX: Favorite Toggle & Add to Cart on Product Listing -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Favorite Toggle on cards
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-fav-toggle');
        if (!btn) return;

        const productId = btn.getAttribute('data-product-id');
        const icon = btn.querySelector('.fav-heart-icon');

        btn.disabled = true;
        btn.style.transform = 'scale(1.2)';

        const formData = new FormData();
        formData.append('product_id', productId);

        fetch('<?php echo URLROOT; ?>/favorites/toggle', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            setTimeout(function() { btn.style.transform = ''; }, 200);

            if (data.success) {
                if (data.is_favorited) {
                    icon.setAttribute('fill', '#ff3b30');
                    icon.setAttribute('stroke', '#ff3b30');
                    btn.title = 'Bỏ yêu thích';
                } else {
                    icon.setAttribute('fill', 'none');
                    icon.setAttribute('stroke', '#666');
                    btn.title = 'Yêu thích';
                }
                if (typeof FlashModule !== 'undefined') {
                    FlashModule.show('success', data.message);
                }
            } else {
                if (data.require_login) {
                    window.location.href = '<?php echo URLROOT; ?>/users/login';
                    return;
                }
                alert(data.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.style.transform = '';
            console.error('Favorite error:', err);
        });
    });

    // Add to Cart mini button on cards
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-cart-add-mini');
        if (!btn) return;

        const productId = btn.getAttribute('data-product-id');
        btn.disabled = true;
        btn.style.transform = 'scale(0.9)';

        const formData = new FormData();
        formData.append('product_id', productId);

        fetch('<?php echo URLROOT; ?>/carts/add', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            setTimeout(function() { btn.style.transform = ''; }, 200);

            if (data.success) {
                // Change to checkmark briefly
                btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
                btn.style.background = '#34c759';

                setTimeout(function() {
                    btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';
                    btn.style.background = 'var(--apple-blue, #0071e3)';
                }, 1500);

                // Update navbar badge
                const badges = document.querySelectorAll('#nav-cart-badge');
                badges.forEach(b => {
                    b.textContent = data.cart_count;
                    b.style.display = 'flex';
                });

                if (typeof FlashModule !== 'undefined') {
                    FlashModule.show('success', data.message);
                }
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.style.transform = '';
            console.error('Add cart error:', err);
        });
    });
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
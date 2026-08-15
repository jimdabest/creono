<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px;">

    <h1 style="font-size: 36px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: #1d1d1f;">
        Sản phẩm yêu thích
    </h1>
    <p style="font-size: 15px; color: #86868b; margin-bottom: 32px;">
        Danh sách các tài liệu bạn đã lưu lại để tham khảo hoặc mua sau
    </p>

    <?php if (!empty($data['favorites'])) : ?>
        <div class="product-grid" id="favoritesGrid">
            <?php foreach ($data['favorites'] as $product) : ?>
                <div class="product-card interactive-hover" id="fav-card-<?php echo $product->id; ?>" style="border-radius: 24px; position: relative;">
                    
                    <!-- Remove from Favorites Button -->
                    <button class="btn-remove-fav" data-product-id="<?php echo $product->id; ?>" style="position: absolute; top: 12px; right: 12px; z-index: 10; background: rgba(255,255,255,0.9); border: none; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #ff3b30; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s ease;" title="Bỏ yêu thích">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#ff3b30" stroke="#ff3b30" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>

                    <div class="product-image-wrapper" style="border-radius: 24px 24px 0 0;">
                        <div class="product-placeholder">Preview</div>
                        <span class="product-badge"><?php echo htmlspecialchars($product->store_name); ?></span>
                        <?php if ($product->rating > 0) : ?>
                            <span class="product-rating">
                                ★ <?php echo number_format($product->rating, 1); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-content">
                        <h3 class="product-title"><?php echo htmlspecialchars($product->title); ?></h3>
                        <p class="product-desc"><?php echo htmlspecialchars($product->description ?? 'Tài liệu số chất lượng cao trên Creono.'); ?></p>
                    </div>

                    <div class="product-footer">
                        <span class="product-price"><?php echo number_format($product->price, 0, ',', '.'); ?> ₫</span>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-primary btn-add-cart-mini" data-product-id="<?php echo $product->id; ?>" style="padding: 8px 14px; font-size: 13px; border-radius: 10px; background: var(--apple-blue, #0071e3); color: #fff; border: none; cursor: pointer;" title="Thêm vào giỏ hàng">
                                🛒
                            </button>
                            <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">Chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="empty-state" style="text-align: center; padding: 80px 24px; background: #f9f9fb; border-radius: 24px; border: 1px dashed rgba(0,0,0,0.1);">
            <div style="font-size: 56px; margin-bottom: 16px; opacity: 0.5;">❤️</div>
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 8px;">Chưa có sản phẩm yêu thích</h3>
            <p style="font-size: 15px; color: #86868b; margin-bottom: 24px;">Bạn có thể bấm biểu tượng trái tim trên các sản phẩm để lưu lại tại đây.</p>
            <a href="<?php echo URLROOT; ?>/products/index" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 14px; background: var(--apple-blue, #0071e3); color: #fff; text-decoration: none; display: inline-block;">
                Khám phá tài liệu
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Remove favorite via AJAX
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-fav');
        if (!btn) return;

        const productId = btn.getAttribute('data-product-id');
        const card = document.getElementById('fav-card-' + productId);

        btn.disabled = true;

        const formData = new FormData();
        formData.append('product_id', productId);

        fetch('<?php echo URLROOT; ?>/favorites/toggle', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (card) {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(function() { card.remove(); }, 300);
                }

                if (typeof FlashModule !== 'undefined') {
                    FlashModule.show('success', data.message);
                }

                if (data.count === 0) {
                    setTimeout(function() { location.reload(); }, 500);
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            console.error('Favorite remove error:', err);
        });
    });

    // Add to cart from favorite page
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-add-cart-mini');
        if (!btn) return;

        const productId = btn.getAttribute('data-product-id');
        btn.disabled = true;
        btn.textContent = '...';

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
            btn.textContent = '🛒';

            if (data.success) {
                if (typeof FlashModule !== 'undefined') {
                    FlashModule.show('success', data.message);
                } else {
                    alert(data.message);
                }
                const badge = document.getElementById('nav-cart-badge');
                if (badge && data.cart_count) {
                    badge.textContent = data.cart_count;
                    badge.style.display = 'flex';
                }
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = '🛒';
            console.error('Add cart error:', err);
        });
    });
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

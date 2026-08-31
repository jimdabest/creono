<?php

/** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px; max-width: 900px;">

    <h1 style="font-size: 36px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: #1d1d1f;">
        Giỏ hàng của bạn
    </h1>
    <p style="font-size: 15px; color: #86868b; margin-bottom: 32px;">
        Bạn có <strong id="cart-item-count"><?php echo $data['cart_count']; ?></strong> sản phẩm trong giỏ hàng
    </p>

    <?php if (!empty($data['items'])) : ?>
        <div class="cart-layout" style="display: grid; grid-template-columns: 1fr 320px; gap: 32px; align-items: start;">

            <!-- LEFT: Cart Items List -->
            <div class="cart-items-list" id="cartItemsList" style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($data['items'] as $item) : ?>
                    <div class="cart-item-card" id="cart-item-<?php echo $item->product_id; ?>" style="background: #fff; border: 1px solid rgba(0,0,0,0.08); border-radius: 20px; padding: 20px; display: flex; gap: 16px; align-items: center; transition: all 0.3s ease;">
                        <!-- Preview -->
                        <div class="cart-item-preview" style="width: 80px; height: 80px; background: #f5f5f7; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #86868b; font-size: 13px; flex-shrink: 0;">
                            <?php if (!empty($item->preview_url)) : ?>
                                <img src="<?php echo URLROOT . htmlspecialchars($item->preview_url); ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 14px;">
                            <?php else : ?>
                                Preview
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="cart-item-info" style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 4px; color: #1d1d1f; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $item->product_id; ?>" style="color: inherit; text-decoration: none;">
                                    <?php echo htmlspecialchars($item->title); ?>
                                </a>
                            </h3>
                            <div style="font-size: 13px; color: #86868b; display: flex; gap: 12px; align-items: center;">
                                <span>🏪 <?php echo htmlspecialchars($item->store_name); ?></span>
                                <?php if ($item->rating > 0) : ?>
                                    <span style="color: #ffb800;">★ <?php echo number_format($item->rating, 1); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Price & Actions -->
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px; flex-shrink: 0;">
                            <div style="font-size: 18px; font-weight: 700; color: var(--apple-blue, #0071e3);">
                                <?php echo number_format($item->price, 0, ',', '.'); ?> ₫
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <!-- Nút Thanh toán lẻ -->
                                <a href="<?php echo URLROOT; ?>/orders/checkout/<?php echo $item->product_id; ?>"
                                    style="background: #27ae60; color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; padding: 6px 12px; border-radius: 8px; transition: background 0.2s;">
                                    Thanh toán
                                </a>
                                <!-- Nút Xóa (Được thiết kế lại cho đồng bộ) -->
                                <button class="btn-remove-cart-item" data-product-id="<?php echo $item->product_id; ?>" data-price="<?php echo $item->price; ?>" style="background: rgba(255, 59, 48, 0.1); border: none; color: #ff3b30; cursor: pointer; padding: 6px 10px; border-radius: 8px; transition: background 0.2s;" title="Xóa khỏi giỏ hàng">
                                    Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- RIGHT: Order Summary Card -->
            <div class="cart-summary-card" style="background: #fff; border: 1px solid rgba(0,0,0,0.08); border-radius: 20px; padding: 28px; box-shadow: 0 4px 24px rgba(0,0,0,0.04); position: sticky; top: 90px;">
                <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #1d1d1f;">Tóm tắt đơn hàng</h3>

                <div class="summary-row" style="display: flex; justify-content: space-between; font-size: 15px; color: #555; margin-bottom: 12px;">
                    <span>Số lượng sản phẩm</span>
                    <span id="summary-count"><?php echo $data['cart_count']; ?></span>
                </div>

                <div class="summary-row" style="display: flex; justify-content: space-between; font-size: 15px; color: #555; margin-bottom: 12px;">
                    <span>Phí nền tảng</span>
                    <span style="color: var(--apple-green, #34c759);">Miễn phí</span>
                </div>

                <div style="border-top: 1px solid rgba(0,0,0,0.08); padding-top: 16px; margin-top: 16px;">
                    <div class="summary-row" style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; color: #1d1d1f;">
                        <span>Tổng cộng</span>
                        <span id="summary-total" style="color: var(--apple-blue, #0071e3);"><?php echo number_format($data['total'], 0, ',', '.'); ?> ₫</span>
                    </div>
                </div>

                <!-- Nút Thanh toán tổng -->
                <?php if ($data['cart_count'] === 1): ?>
                    <!-- Nếu chỉ có 1 sản phẩm -> Trỏ thẳng link sang trang Checkout chi tiết -->
                    <a href="<?php echo URLROOT; ?>/orders/checkout/<?php echo $data['items'][0]->product_id; ?>"
                        class="btn btn-primary"
                        style="display: block; text-align: center; width: 100%; padding: 14px; font-size: 16px; font-weight: 600; border-radius: 14px; background: var(--apple-blue, #0071e3); border: none; color: #fff; text-decoration: none; margin-top: 20px; transition: all 0.2s ease;">
                        Tiến hành thanh toán
                    </a>
                <?php else: ?>
                    <!-- Form thanh toán toàn bộ giỏ hàng -->
                    <form action="<?php echo URLROOT; ?>/orders/processCart" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">
                        <button type="submit"
                            onclick="return confirm('Bạn có chắc chắn muốn thanh toán tổng cộng <?php echo number_format($data['total'], 0, ',', '.'); ?>đ cho toàn bộ giỏ hàng?');"
                            class="btn btn-primary"
                            style="width: 100%; padding: 14px; font-size: 16px; font-weight: 600; border-radius: 14px; background: var(--apple-blue, #0071e3); border: none; color: #fff; cursor: pointer; margin-top: 20px; transition: all 0.2s ease;">
                            Thanh toán tất cả (<?php echo $data['cart_count']; ?>)
                        </button>
                    </form>
                <?php endif; ?>

                <a href="<?php echo URLROOT; ?>/products/index" style="display: block; text-align: center; margin-top: 12px; font-size: 14px; color: var(--apple-blue, #0071e3); text-decoration: none;">
                    ← Tiếp tục mua sắm
                </a>
            </div>
        </div>
    <?php else : ?>
        <!-- Empty Cart -->
        <div class="empty-cart" style="text-align: center; padding: 80px 24px; background: #f9f9fb; border-radius: 24px; border: 1px dashed rgba(0,0,0,0.1);">
            <div style="font-size: 56px; margin-bottom: 16px; opacity: 0.5;">🛒</div>
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 8px;">Giỏ hàng trống</h3>
            <p style="font-size: 15px; color: #86868b; margin-bottom: 24px;">Bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
            <a href="<?php echo URLROOT; ?>/products/index" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 14px; background: var(--apple-blue, #0071e3); color: #fff; text-decoration: none; display: inline-block;">
                Khám phá tài liệu
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- STYLES RESPONSIVE -->
<style>
    /* === RESPONSIVE: TABLET & MOBILE === */

    /* Tablet (max-width: 768px) */
    @media (max-width: 768px) {
        .cart-layout {
            grid-template-columns: 1fr !important;
            gap: 24px !important;
        }

        .cart-summary-card {
            position: static !important;
            top: auto !important;
            padding: 24px !important;
        }

        .cart-item-card {
            padding: 16px !important;
            gap: 12px !important;
        }

        .cart-item-preview {
            width: 60px !important;
            height: 60px !important;
        }

        .cart-item-info h3 {
            font-size: 14px !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: unset !important;
        }

        .cart-item-info div {
            font-size: 12px !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
        }

        .cart-item-info div span:last-child {
            margin-left: 0 !important;
        }

        .cart-item-card>div:last-child {
            font-size: 16px !important;
        }

        .btn-remove-cart-item {
            padding: 6px !important;
        }

        .cart-summary-card h3 {
            font-size: 16px !important;
        }

        .summary-row {
            font-size: 14px !important;
        }

        .summary-row:last-child {
            font-size: 18px !important;
        }

        .btn-primary {
            font-size: 15px !important;
            padding: 12px !important;
        }

        h1 {
            font-size: 28px !important;
        }

        .page-container>p {
            font-size: 14px !important;
        }
    }

    /* Điện thoại nhỏ (max-width: 480px) */
    @media (max-width: 480px) {
        .page-container {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        h1 {
            font-size: 24px !important;
        }

        .cart-layout {
            gap: 16px !important;
        }

        .cart-item-card {
            flex-wrap: wrap !important;
            padding: 14px !important;
            gap: 10px !important;
            border-radius: 16px !important;
        }

        .cart-item-preview {
            width: 56px !important;
            height: 56px !important;
            border-radius: 10px !important;
        }

        .cart-item-info {
            flex: 1 1 100% !important;
            order: 3 !important;
        }

        .cart-item-info h3 {
            font-size: 14px !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: unset !important;
        }

        .cart-item-info div {
            font-size: 12px !important;
            flex-wrap: wrap !important;
        }

        .cart-item-card>div:last-child {
            font-size: 16px !important;
            text-align: left !important;
            flex: 1 !important;
        }

        .btn-remove-cart-item {
            order: 4 !important;
            align-self: flex-start !important;
            padding: 4px !important;
        }

        .cart-summary-card {
            padding: 18px !important;
            border-radius: 16px !important;
        }

        .cart-summary-card h3 {
            font-size: 15px !important;
            margin-bottom: 16px !important;
        }

        .summary-row {
            font-size: 13px !important;
            margin-bottom: 8px !important;
        }

        .summary-row:last-child {
            font-size: 17px !important;
            padding-top: 12px !important;
            margin-top: 12px !important;
        }

        .btn-primary {
            font-size: 14px !important;
            padding: 12px !important;
            border-radius: 12px !important;
        }

        .empty-cart {
            padding: 48px 16px !important;
        }

        .empty-cart h3 {
            font-size: 18px !important;
        }

        .empty-cart p {
            font-size: 14px !important;
        }

        .empty-cart .btn-primary {
            font-size: 14px !important;
            padding: 10px 20px !important;
        }

        a[href*="products/index"] {
            font-size: 13px !important;
        }
    }
</style>

<!-- AJAX: Xóa item khỏi giỏ hàng -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove-cart-item');
            if (!btn) return;

            const productId = btn.getAttribute('data-product-id');
            const itemCard = document.getElementById('cart-item-' + productId);

            if (!confirm('Bạn muốn xóa sản phẩm này khỏi giỏ hàng?')) return;

            btn.disabled = true;

            const formData = new FormData();
            formData.append('product_id', productId);

            fetch('<?php echo URLROOT; ?>/carts/remove', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Animate remove
                        if (itemCard) {
                            itemCard.style.opacity = '0';
                            itemCard.style.transform = 'translateX(-20px)';
                            setTimeout(function() {
                                itemCard.remove();
                            }, 300);
                        }

                        // Update totals
                        const summaryCount = document.getElementById('summary-count');
                        const summaryTotal = document.getElementById('summary-total');
                        const itemCount = document.getElementById('cart-item-count');

                        if (summaryCount) summaryCount.textContent = data.cart_count;
                        if (summaryTotal) summaryTotal.textContent = data.formatted_total;
                        if (itemCount) itemCount.textContent = data.cart_count;

                        // Update navbar badge
                        updateNavCartBadge(data.cart_count);

                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show('success', data.message);
                        }

                        // If cart is empty, reload page to show empty state
                        if (data.cart_count === 0) {
                            setTimeout(function() {
                                location.reload();
                            }, 600);
                        }
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    console.error('Remove cart item error:', err);
                });
        });

        function updateNavCartBadge(count) {
            const badge = document.getElementById('nav-cart-badge');
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        }
    });
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
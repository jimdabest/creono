<?php

/** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px;">
    <nav class="breadcrumb" style="margin-bottom: 24px; font-size: 14px; color: var(--apple-text-secondary, #86868b);">
        <a href="<?php echo URLROOT; ?>" style="color: var(--apple-blue, #0071e3); text-decoration: none;">Trang chủ</a>
        <span style="margin: 0 8px;">/</span>
        <span style="color: var(--apple-text-primary, #1d1d1f); font-weight: 500;">Cửa hàng</span>
        <span style="margin: 0 8px;">/</span>
        <span style="color: var(--apple-text-primary, #1d1d1f); font-weight: 500;"><?php echo htmlspecialchars($data['store']->name); ?></span>
    </nav>

    <!-- Header Cửa Hàng -->
    <div class="store-header" style="background: var(--apple-gray-bg, #f5f5f7); border-radius: 24px; padding: 32px; margin-bottom: 40px; display: flex; gap: 28px; align-items: center; flex-wrap: wrap;">
        <div class="store-logo" style="flex-shrink: 0;">
            <?php if (!empty($data['store']->logo_url)): ?>
                <img src="<?php echo URLROOT . htmlspecialchars($data['store']->logo_url); ?>" alt="<?php echo htmlspecialchars($data['store']->name); ?>" style="width: 100px; height: 100px; border-radius: 16px; object-fit: cover; border: 1px solid rgba(0,0,0,0.08);">
            <?php else: ?>
                <div style="width: 100px; height: 100px; border-radius: 16px; background: var(--apple-blue, #0071e3); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: 700; border: 1px solid rgba(0,0,0,0.08);">
                    <?php echo mb_substr($data['store']->name, 0, 1); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="store-info" style="flex: 1;">
            <h1 style="font-size: 32px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: #1d1d1f;"><?php echo htmlspecialchars($data['store']->name); ?></h1>
            <?php if (!empty($data['store']->description)): ?>
                <p style="font-size: 16px; color: var(--apple-gray, #86868b); max-width: 600px; margin-bottom: 16px;"><?php echo nl2br(htmlspecialchars($data['store']->description)); ?></p>
            <?php endif; ?>
            <div class="store-stats" style="display: flex; gap: 24px; font-size: 15px; color: var(--apple-gray, #86868b); flex-wrap: wrap;">
                <span><strong><?php echo $data['total_products']; ?></strong> sản phẩm</span>
                <span>⭐ <strong><?php echo number_format($data['avg_rating'], 1); ?></strong></span>
                <span>⬇️ <strong><?php echo number_format($data['total_downloads']); ?></strong> lượt tải</span>
            </div>
            <div style="margin-top: 16px; display: flex; gap: 12px; flex-wrap: wrap;">
                <?php if ($data['is_owner']): ?>
                    <a href="<?php echo URLROOT; ?>/stores/edit" class="btn btn-secondary" style="padding: 8px 20px; font-size: 14px; border-radius: 980px;">Quản lý cửa hàng</a>
                <?php else: ?>
                    <a href="<?php echo URLROOT; ?>/reports/create?target_type=STORE&target_id=<?php echo $data['store']->id; ?>" class="btn btn-outline" style="padding: 8px 20px; font-size: 14px; border-radius: 980px;">🚩 Báo cáo cửa hàng</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bộ Lọc -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <!-- Pill Tất cả -->
            <a href="<?php echo URLROOT; ?>/storefront/<?php echo $data['store']->slug; ?>?page=1" class="category-pill <?php echo empty($data['current_category']) ? 'active' : ''; ?>" style="text-decoration: none; background: <?php echo empty($data['current_category']) ? 'var(--apple-black)' : 'var(--apple-gray-bg)'; ?>; color: <?php echo empty($data['current_category']) ? '#fff' : 'var(--apple-black)'; ?>; padding: 8px 16px; border-radius: 20px; font-size: 14px;">Tất cả</a>

            <!-- Các pill danh mục -->
            <?php foreach ($data['categories'] as $cat): ?>
                <a href="<?php echo URLROOT; ?>/storefront/<?php echo $data['store']->slug; ?>?category=<?php echo $cat->slug; ?>&sort=<?php echo $data['current_sort']; ?>&page=1" class="category-pill <?php echo !empty($data['current_category']) && $data['current_category'] == $cat->slug ? 'active' : ''; ?>" style="text-decoration: none; background: <?php echo !empty($data['current_category']) && $data['current_category'] == $cat->slug ? 'var(--apple-black)' : 'var(--apple-gray-bg)'; ?>; color: <?php echo !empty($data['current_category']) && $data['current_category'] == $cat->slug ? '#fff' : 'var(--apple-black)'; ?>; padding: 8px 16px; border-radius: 20px; font-size: 14px;">
                    <?php echo htmlspecialchars($cat->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div>
            <select id="sortSelect" style="padding: 8px 16px; border-radius: 8px; border: 1px solid var(--apple-gray-border); background: #fff; font-size: 14px;">
                <option value="newest" <?php echo $data['current_sort'] === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                <option value="price_asc" <?php echo $data['current_sort'] === 'price_asc' ? 'selected' : ''; ?>>Giá thấp → cao</option>
                <option value="price_desc" <?php echo $data['current_sort'] === 'price_desc' ? 'selected' : ''; ?>>Giá cao → thấp</option>
                <option value="rating" <?php echo $data['current_sort'] === 'rating' ? 'selected' : ''; ?>>Đánh giá cao nhất</option>
                <option value="popular" <?php echo $data['current_sort'] === 'popular' ? 'selected' : ''; ?>>Bán chạy nhất</option>
            </select>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <?php if (!empty($data['products'])): ?>
        <div class="product-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <?php foreach ($data['products'] as $product): ?>
                <div class="product-card interactive-hover" style="border-radius: 16px; overflow: hidden; border: 1px solid rgba(0,0,0,0.08); background: #fff; display: flex; flex-direction: column;">
                    <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" style="text-decoration: none; color: inherit; flex-grow: 1;">
                        <div class="product-image-wrapper" style="height: 160px; background: #f5f5f7; display: flex; align-items: center; justify-content: center; position: relative;">
                            <?php if (!empty($product->preview_url)): ?>
                                <img src="<?php echo URLROOT . htmlspecialchars($product->preview_url); ?>" alt="<?php echo htmlspecialchars($product->title); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div class="product-placeholder" style="color: #86868b; font-size: 13px;">Preview</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-content" style="padding: 14px 16px 8px;">
                            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 4px;"><?php echo htmlspecialchars($product->title); ?></h3>
                        </div>
                    </a>
                    <div class="product-footer" style="padding: 12px 16px; border-top: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 17px; font-weight: 700; color: #0071e3;"><?php echo number_format($product->price, 0, ',', '.'); ?> ₫</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state" style="text-align: center; padding: 80px 24px; background: #f9f9fb; border-radius: 24px; border: 1px dashed rgba(0,0,0,0.1);">
            <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;">🏪</div>
            <h3 style="font-size: 20px; font-weight: 600;">Chưa có sản phẩm nào</h3>
        </div>
    <?php endif; ?>
</div>

<script>
    document.getElementById('sortSelect').addEventListener('change', function() {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('sort', this.value);
        currentUrl.searchParams.set('page', '1');
        window.location.href = currentUrl.toString();
    });
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
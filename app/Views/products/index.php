<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<?php
/**
 * Build URL giữ nguyên các filter hiện tại khi thay đổi 1 param
 */
function buildFilterUrl(array $overrides = []): string
{
    $params = array_merge([
        'q' => $_GET['q'] ?? '',
        'category' => $_GET['category'] ?? '',
        'sort' => $_GET['sort'] ?? '',
    ], $overrides);

    $params = array_filter($params, function ($v, $k) {
        if ($v === '' || $v === null)
            return false;
        if ($k === 'sort' && $v === 'newest')
            return false;
        return true;
    }, ARRAY_FILTER_USE_BOTH);

    return URLROOT . '/products/index' . ($params ? '?' . http_build_query($params) : '');
}

$currentKeyword = $data['current_keyword'] ?? '';
$currentCategory = (int) ($data['current_category'] ?? 0);
$currentSort = $data['current_sort'] ?? 'newest';
$totalCount = (int) ($data['total_count'] ?? count($data['products'] ?? []));
$hasFilter = $currentKeyword !== '' || $currentCategory > 0;
?>

<div class="market-page">

    <!-- ===== HERO + SEARCH ===== -->
    <div class="market-hero">
        <h1 class="market-hero__title">Khám phá kho tài liệu</h1>
        <p class="market-hero__subtitle">
            Hàng ngàn mã nguồn, đồ án, template chất lượng cao từ cộng đồng sáng tạo
        </p>

        <form action="<?php echo URLROOT; ?>/products/index" method="GET" class="search-spotlight">
            <?php if ($currentCategory > 0): ?>
                <input type="hidden" name="category" value="<?php echo $currentCategory; ?>">
            <?php endif; ?>
            <?php if ($currentSort !== 'newest'): ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($currentSort); ?>">
            <?php endif; ?>

            <svg class="search-spotlight__icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M21 21l-4.35-4.35"></path>
            </svg>

            <input type="text" name="q" class="search-spotlight__input"
                value="<?php echo htmlspecialchars($currentKeyword); ?>" placeholder="Tìm mã nguồn, đồ án, template..."
                autocomplete="off">

            <?php if ($currentKeyword !== ''): ?>
                <a href="<?php echo buildFilterUrl(['q' => '']); ?>" class="search-spotlight__clear"
                    aria-label="Xóa từ khóa" title="Xóa từ khóa">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </a>
            <?php endif; ?>
        </form>

        <?php if ($hasFilter): ?>
            <p class="search-summary">
                <?php if ($currentKeyword !== ''): ?>
                    Kết quả cho "<strong><?php echo htmlspecialchars($currentKeyword); ?></strong>"
                <?php endif; ?>
                <?php if ($currentCategory > 0 && !empty($data['categories'])): ?>
                    <?php
                    $activeCat = null;
                    foreach ($data['categories'] as $c) {
                        if ((int) $c->id === $currentCategory) {
                            $activeCat = $c;
                            break;
                        }
                    }
                    ?>
                    <?php if ($activeCat): ?>
                        <?php echo $currentKeyword !== '' ? 'trong' : 'Danh mục'; ?>
                        "<strong><?php echo htmlspecialchars($activeCat->name); ?></strong>"
                    <?php endif; ?>
                <?php endif; ?>
                — <strong><?php echo $totalCount; ?></strong> tài liệu
                ·
                <a href="<?php echo URLROOT; ?>/products/index">Xóa bộ lọc</a>
            </p>
        <?php endif; ?>
    </div>

    <!-- ===== TOOLBAR: CATEGORY + SORT ===== -->
    <div class="market-toolbar">
        <div class="category-scroll">
            <div class="category-pills">
                <a href="<?php echo buildFilterUrl(['category' => '']); ?>"
                    class="category-pill <?php echo $currentCategory === 0 ? 'is-active' : ''; ?>">
                    Tất cả
                </a>

                <?php if (!empty($data['categories'])): ?>
                    <?php foreach ($data['categories'] as $cat): ?>
                        <a href="<?php echo buildFilterUrl(['category' => (int) $cat->id]); ?>"
                            class="category-pill <?php echo $currentCategory === (int) $cat->id ? 'is-active' : ''; ?>">
                            <?php echo htmlspecialchars($cat->name); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <form action="<?php echo URLROOT; ?>/products/index" method="GET" class="sort-control" id="sortForm">
            <?php if ($currentKeyword !== ''): ?>
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($currentKeyword); ?>">
            <?php endif; ?>
            <?php if ($currentCategory > 0): ?>
                <input type="hidden" name="category" value="<?php echo $currentCategory; ?>">
            <?php endif; ?>

            <!-- 1. Select ẩn — giữ giá trị sort thật để submit form -->
            <select name="sort" id="realSortSelect" class="sort-control__native">
                <option value="newest" <?php echo $currentSort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                <option value="popular" <?php echo $currentSort === 'popular' ? 'selected' : ''; ?>>Bán chạy nhất</option>
                <option value="rating" <?php echo $currentSort === 'rating' ? 'selected' : ''; ?>>Đánh giá cao</option>
                <option value="price_asc" <?php echo $currentSort === 'price_asc' ? 'selected' : ''; ?>>Giá thấp → cao</option>
                <option value="price_desc" <?php echo $currentSort === 'price_desc' ? 'selected' : ''; ?>>Giá cao → thấp</option>
            </select>

            <!-- 2. Custom Dropdown UI -->
            <?php
            $sortLabels = [
                'newest' => 'Mới nhất',
                'popular' => 'Bán chạy nhất',
                'rating' => 'Đánh giá cao',
                'price_asc' => 'Giá thấp → cao',
                'price_desc' => 'Giá cao → thấp'
            ];
            $currentLabel = $sortLabels[$currentSort] ?? 'Mới nhất';
            ?>
            <div class="custom-sort-dropdown" id="customSortDropdown">
                <div class="custom-sort-trigger" id="customSortTrigger">
                    <span id="customSortLabel"><?php echo $currentLabel; ?></span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="custom-sort-menu" id="customSortMenu">
                    <?php foreach ($sortLabels as $val => $label): ?>
                        <div class="custom-sort-option <?php echo $currentSort === $val ? 'is-active' : ''; ?>"
                            data-value="<?php echo $val; ?>">
                            <?php echo $label; ?>
                            <?php if ($currentSort === $val): ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- ===== PRODUCT GRID ===== -->
    <div class="product-grid">
        <?php if (!empty($data['products'])): ?>
            <?php foreach ($data['products'] as $product): ?>
                <?php $isFav = isset($data['favorite_ids']) && in_array((int) $product->id, $data['favorite_ids']); ?>

                <article class="product-item">
                    <button type="button" class="product-item__fav btn-fav-toggle" data-product-id="<?php echo $product->id; ?>"
                        title="<?php echo $isFav ? 'Bỏ yêu thích' : 'Yêu thích'; ?>"
                        aria-label="<?php echo $isFav ? 'Bỏ yêu thích' : 'Yêu thích'; ?>">
                        <svg class="fav-heart-icon" width="17" height="17" viewBox="0 0 24 24"
                            fill="<?php echo $isFav ? '#ff3b30' : 'none'; ?>"
                            stroke="<?php echo $isFav ? '#ff3b30' : '#1d1d1f'; ?>" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                            </path>
                        </svg>
                    </button>

                    <a href="<?php echo URLROOT; ?>/products/detail/<?php echo $product->id; ?>" class="product-item__link">
                        <div class="product-item__thumb">
                            <?php if (!empty($product->preview_url)): ?>
                                <img src="<?php echo URLROOT . htmlspecialchars($product->preview_url); ?>"
                                    alt="<?php echo htmlspecialchars($product->title); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="product-item__placeholder">Preview</div>
                            <?php endif; ?>

                            <span class="product-item__store">
                                <?php echo htmlspecialchars($product->store_name); ?>
                            </span>

                            <?php if (isset($product->rating) && $product->rating > 0): ?>
                                <span class="product-item__rating">
                                    <span class="product-item__rating-star">★</span>
                                    <?php echo number_format($product->rating, 1); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="product-item__content">
                            <h3 class="product-item__title"><?php echo htmlspecialchars($product->title); ?></h3>
                            <p class="product-item__desc">
                                <?php echo htmlspecialchars($product->description ?? 'Tài liệu số chất lượng cao được kiểm duyệt trên Creono.'); ?>
                            </p>
                        </div>
                    </a>

                    <div class="product-item__footer">
                        <span class="product-item__price">
                            <?php echo number_format($product->price, 0, ',', '.'); ?><span
                                class="product-item__price-unit">đ</span>
                        </span>

                        <div class="product-item__actions">
                            <button type="button" class="btn-mini-cart btn-cart-add-mini"
                                data-product-id="<?php echo $product->id; ?>" title="Thêm vào giỏ hàng"
                                aria-label="Thêm vào giỏ hàng">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </button>

                            <a href="<?php echo URLROOT; ?>/orders/checkout/<?php echo $product->id; ?>" class="btn-buy">
                                Mua ngay
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-market">
                <div class="empty-market__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                </div>
                <h3 class="empty-market__title">
                    <?php echo $currentKeyword !== '' ? 'Không tìm thấy tài liệu' : 'Chưa có tài liệu'; ?>
                </h3>
                <p class="empty-market__desc">
                    <?php if ($currentKeyword !== ''): ?>
                        Không có kết quả nào cho từ khóa "<strong><?php echo htmlspecialchars($currentKeyword); ?></strong>".
                        Hãy thử từ khóa khác hoặc xóa bộ lọc.
                    <?php else: ?>
                        Hiện chưa có tài liệu nào được đăng tải trong danh mục này.
                    <?php endif; ?>
                </p>
                <a href="<?php echo URLROOT; ?>/products/index" class="btn-primary">
                    Xem tất cả tài liệu
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- AJAX: Favorite Toggle & Add to Cart -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        // ===== FAVORITE TOGGLE =====
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-fav-toggle');
            if (!btn) return;

            const productId = btn.getAttribute('data-product-id');
            const icon = btn.querySelector('.fav-heart-icon');
            btn.disabled = true;
            btn.style.transform = 'scale(1.2)';

            const formData = new FormData();
            formData.append('product_id', productId);

            fetch('<?php echo URLROOT; ?>/favorites/toggle', {
                method: 'POST', body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    setTimeout(() => { btn.style.transform = ''; }, 200);

                    if (data.success) {
                        if (data.is_favorited) {
                            icon.setAttribute('fill', '#ff3b30');
                            icon.setAttribute('stroke', '#ff3b30');
                            btn.title = 'Bỏ yêu thích';
                        } else {
                            icon.setAttribute('fill', 'none');
                            icon.setAttribute('stroke', '#1d1d1f');
                            btn.title = 'Yêu thích';
                        }
                        if (typeof FlashModule !== 'undefined') FlashModule.show('success', data.message);
                    } else {
                        if (data.require_login) { window.location.href = '<?php echo URLROOT; ?>/users/login'; return; }
                        alert(data.message);
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.style.transform = '';
                    console.error(err);
                });
        });

        // ===== ADD TO CART =====
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-cart-add-mini');
            if (!btn) return;

            const productId = btn.getAttribute('data-product-id');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.style.transform = 'scale(0.9)';

            const formData = new FormData();
            formData.append('product_id', productId);

            fetch('<?php echo URLROOT; ?>/carts/add', {
                method: 'POST', body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    setTimeout(() => { btn.style.transform = ''; }, 200);

                    if (data.success) {
                        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
                        btn.classList.add('is-success');

                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                            btn.classList.remove('is-success');
                        }, 1500);

                        document.querySelectorAll('#nav-cart-badge').forEach(b => {
                            b.textContent = data.cart_count;
                            b.style.display = 'flex';
                        });

                        if (typeof FlashModule !== 'undefined') FlashModule.show('success', data.message);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.style.transform = '';
                    console.error(err);
                });
        });
    });
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
<script src="<?php echo URLROOT; ?>/js/modules/marketplace.js"></script>
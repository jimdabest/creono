<?php

/** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" style="margin-bottom: 24px; font-size: 14px; color: var(--apple-text-secondary, #86868b);">
        <a href="<?php echo URLROOT; ?>/products/index" style="color: var(--apple-blue, #0071e3); text-decoration: none;">Chợ tài liệu</a>
        <span style="margin: 0 8px;">/</span>
        <?php if (!empty($data['product']->category_name)) : ?>
            <span><?php echo htmlspecialchars($data['product']->category_name); ?></span>
            <span style="margin: 0 8px;">/</span>
        <?php endif; ?>
        <span style="color: var(--apple-text-primary, #1d1d1f); font-weight: 500;"><?php echo htmlspecialchars($data['product']->title); ?></span>
    </nav>

    <!-- PRODUCT HERO SECTION -->
    <div class="product-hero-grid" style="display: grid; grid-template-columns: 1fr 380px; gap: 40px; align-items: start;">

        <!-- Left: Product Showcase & Description -->
        <div class="product-main-card" style="background: var(--apple-card-bg, #fff); border-radius: 24px; padding: 36px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
            <div class="product-header-badge" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <span class="store-badge" style="background: rgba(0, 113, 227, 0.08); color: var(--apple-blue, #0071e3); font-size: 13px; font-weight: 600; padding: 6px 14px; border-radius: 20px;">
                    🏪 <?php echo htmlspecialchars($data['product']->store_name); ?>
                </span>
                <span class="category-tag" style="background: rgba(0,0,0,0.05); font-size: 13px; padding: 6px 14px; border-radius: 20px; color: #666;">
                    📂 <?php echo htmlspecialchars($data['product']->category_name ?? 'Chung'); ?>
                </span>
            </div>

            <h1 class="product-detail-title" style="font-size: 32px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 16px; color: var(--apple-text-primary, #1d1d1f);">
                <?php echo htmlspecialchars($data['product']->title); ?>
            </h1>

            <!-- Rating Summary Bar -->
            <div class="product-meta-row" style="display: flex; align-items: center; gap: 20px; margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid rgba(0,0,0,0.06);">
                <div class="rating-display" style="display: flex; align-items: center; gap: 6px;">
                    <span class="stars-gold" style="color: #ffb800; font-size: 20px; font-weight: 700;">
                        ★ <?php echo number_format($data['product']->rating, 1); ?>
                    </span>
                    <span class="review-count-text" style="color: #86868b; font-size: 14px;" id="hero-review-count">
                        (<?php echo $data['product']->review_count; ?> đánh giá)
                    </span>
                </div>
                <div class="download-stats" style="color: #86868b; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <span><?php echo number_format($data['product']->download_count); ?> lượt tải</span>
                </div>
                <div class="created-date" style="color: #86868b; font-size: 14px;">
                    📅 <?php echo date('d/m/Y', strtotime($data['product']->created_at)); ?>
                </div>
            </div>

            <!-- Description -->
            <div class="product-description-box" style="line-height: 1.7; color: #333; font-size: 16px;">
                <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 12px; color: #1d1d1f;">Mô tả tài liệu</h3>
                <div class="description-content" style="white-space: pre-line; background: #f9f9fb; padding: 20px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04);">
                    <?php echo nl2br(htmlspecialchars($data['product']->description ?? 'Chưa có mô tả chi tiết cho sản phẩm này.')); ?>
                </div>
            </div>
        </div>

        <!-- Right: Action Card / Pricing Panel -->
        <div class="product-action-card" style="background: var(--apple-card-bg, #fff); border-radius: 24px; padding: 32px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 24px rgba(0,0,0,0.04); position: sticky; top: 90px;">
            <div class="price-header" style="margin-bottom: 24px;">
                <span style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #86868b; font-weight: 600; display: block; margin-bottom: 4px;">Giá bán chính thức</span>
                <span class="price-amount" style="font-size: 36px; font-weight: 800; color: var(--apple-blue, #0071e3);">
                    <?php echo number_format($data['product']->price, 0, ',', '.'); ?> ₫
                </span>
            </div>

            <div class="action-buttons" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                <!-- Mua ngay (UC29) -->
                <a href="<?= URLROOT; ?>/orders/checkout/<?= $data['product']->id; ?>"
                    class="btn btn-primary btn-block"
                    style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px; font-size: 16px; font-weight: 600; text-decoration: none; border-radius: 14px; background: #27ae60; color: #fff; transition: all 0.2s ease;">
                    <span>⚡ Mua ngay</span>
                </a>

                <!-- Add to Cart (UC18) -->
                <button type="button" id="btnAddToCart" class="btn btn-primary btn-block" data-product-id="<?php echo $data['product']->id; ?>" style="padding: 14px; font-size: 16px; font-weight: 600; border-radius: 14px; background: var(--apple-blue, #0071e3); border: none; color: #fff; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span><?php echo (!empty($data['in_cart'])) ? '✓ Đã có trong giỏ' : 'Thêm vào giỏ hàng'; ?></span>
                </button>

                <!-- Toggle Favorite (UC17) -->
                <button type="button" id="btnToggleFavorite" class="btn btn-secondary btn-block" data-product-id="<?php echo $data['product']->id; ?>" style="padding: 14px; font-size: 16px; font-weight: 600; border-radius: 14px; background: #f5f5f7; border: 1px solid rgba(0,0,0,0.08); color: <?php echo (!empty($data['is_favorited'])) ? '#ff3b30' : '#1d1d1f'; ?>; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg class="fav-icon" width="20" height="20" viewBox="0 0 24 24" fill="<?php echo (!empty($data['is_favorited'])) ? '#ff3b30' : 'none'; ?>" stroke="<?php echo (!empty($data['is_favorited'])) ? '#ff3b30' : 'currentColor'; ?>" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span id="favText"><?php echo (!empty($data['is_favorited'])) ? 'Đã yêu thích' : 'Yêu thích'; ?></span>
                </button>

                <!-- View Cart Link -->
                <a href="<?php echo URLROOT; ?>/carts/index" class="btn-view-cart-link" style="display: block; text-align: center; font-size: 14px; color: var(--apple-blue, #0071e3); text-decoration: none; padding: 4px 0;">
                    Xem giỏ hàng →
                </a>
            </div>

            <div class="seller-info-mini" style="padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.06); display: flex; align-items: center; gap: 12px;">
                <div class="seller-avatar" style="width: 44px; height: 44px; border-radius: 50%; background: #0071e3; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px;">
                    <?php echo mb_substr($data['product']->seller_name ?? 'S', 0, 1); ?>
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 15px; color: #1d1d1f;"><?php echo htmlspecialchars($data['product']->seller_name); ?></div>
                    <div style="font-size: 13px; color: #86868b;"><?php echo htmlspecialchars($data['product']->store_name); ?></div>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================================== -->
    <!-- REVIEWS & RATINGS SECTION (UC34 & UC35) -->
    <!-- ============================================================================== -->
    <div class="reviews-section" id="reviews-section" style="margin-top: 56px;">
        <div class="reviews-card" style="background: var(--apple-card-bg, #fff); border-radius: 24px; padding: 40px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 24px rgba(0,0,0,0.04);">

            <h2 style="font-size: 26px; font-weight: 700; letter-spacing: -0.01em; margin-bottom: 32px; color: #1d1d1f;">
                Đánh giá & Bình luận khách hàng
            </h2>

            <!-- RATING OVERVIEW GRID -->
            <div class="rating-overview-grid" style="display: grid; grid-template-columns: 220px 1fr; gap: 40px; padding: 28px; background: #f9f9fb; border-radius: 20px; margin-bottom: 40px; align-items: center;">

                <!-- Left: Big Rating Number -->
                <div class="rating-score-box text-center" style="border-right: 1px solid rgba(0,0,0,0.08); padding-right: 20px;">
                    <div class="big-score" id="stats-avg-score" style="font-size: 52px; font-weight: 800; color: #1d1d1f; line-height: 1;">
                        <?php echo number_format($data['rating_stats']['average'], 1); ?>
                    </div>
                    <div class="stars-rating-big" style="color: #ffb800; font-size: 22px; margin: 8px 0;">
                        <?php
                        $avg = round($data['rating_stats']['average']);
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $avg ? '★' : '☆';
                        }
                        ?>
                    </div>
                    <div style="font-size: 14px; color: #86868b;" id="stats-total-count">
                        Dựa trên <?php echo $data['rating_stats']['total']; ?> đánh giá
                    </div>
                </div>

                <!-- Right: Rating Bars (5 Stars to 1 Star) -->
                <div class="rating-bars-box" style="display: flex; flex-direction: column; gap: 8px;">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                        <?php
                        $count = $data['rating_stats'][(string)$star] ?? 0;
                        $pct = $data['rating_stats']['total'] > 0 ? round(($count / $data['rating_stats']['total']) * 100) : 0;
                        ?>
                        <div class="rating-bar-row" style="display: flex; align-items: center; gap: 12px; font-size: 14px;">
                            <span style="width: 50px; font-weight: 500; color: #555; text-align: right;"><?php echo $star; ?> sao</span>
                            <div class="progress-bar-bg" style="flex: 1; height: 8px; background: rgba(0,0,0,0.08); border-radius: 4px; overflow: hidden;">
                                <div class="progress-bar-fill" id="bar-fill-<?php echo $star; ?>" style="width: <?php echo $pct; ?>%; height: 100%; background: #ffb800; border-radius: 4px; transition: width 0.4s ease;"></div>
                            </div>
                            <span style="width: 40px; color: #86868b; font-size: 13px;" id="bar-count-<?php echo $star; ?>"><?php echo $count; ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- FORM ĐÁNH GIÁ SẢN PHẨM (UC34) -->
            <div class="review-form-container" style="margin-bottom: 48px;">
                <?php if (!isset($_SESSION['user_id'])) : ?>
                    <!-- Guest message -->
                    <div class="login-prompt" style="text-align: center; padding: 28px; background: rgba(0,113,227,0.04); border: 1px dashed rgba(0,113,227,0.3); border-radius: 16px;">
                        <p style="margin-bottom: 12px; font-weight: 500; color: #333;">Bạn cần đăng nhập để viết đánh giá cho sản phẩm này.</p>
                        <a href="<?php echo URLROOT; ?>/users/login" class="btn btn-primary" style="padding: 10px 24px; border-radius: 12px; background: var(--apple-blue, #0071e3); color: #fff; text-decoration: none; display: inline-block; font-weight: 500;">
                            Đăng nhập ngay
                        </a>
                    </div>
                <?php elseif ($data['is_seller']) : ?>
                    <!-- Seller message -->
                    <div class="seller-notice" style="padding: 16px 20px; background: #fff8e6; border: 1px solid #ffe58f; border-radius: 14px; color: #8a6d3b; font-size: 14px;">
                        💡 Bạn là người bán sản phẩm này. Bạn không thể tự đánh giá, nhưng có thể phản hồi bình luận của khách hàng bên dưới.
                    </div>
                <?php elseif ($data['has_reviewed']) : ?>
                    <!-- Already reviewed message -->
                    <div class="reviewed-notice" id="already-reviewed-msg" style="padding: 16px 20px; background: #e6f7ff; border: 1px solid #91d5ff; border-radius: 14px; color: #0050b3; font-size: 14px;">
                        ✅ Bạn đã gửi đánh giá cho sản phẩm này. Cảm ơn phản hồi của bạn!
                    </div>
                <?php else : ?>
                    <!-- Interactive Star Rating Form -->
                    <form id="reviewForm" action="<?php echo URLROOT; ?>/reviews/store" method="POST" data-ajax="true" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.1); border-radius: 20px; padding: 28px; transition: border-color 0.2s;">
                        <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $data['product']->id; ?>">
                        <input type="hidden" name="rating" id="ratingInput" value="0">

                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #1d1d1f;">
                            Viết đánh giá của bạn
                        </h3>

                        <!-- Interactive Star Picker -->
                        <div class="star-rating-picker" style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #555; margin-bottom: 8px;">
                                Chọn số sao đánh giá: <span id="ratingLabel" style="color: #ffb800; font-weight: 700;"></span>
                            </label>
                            <div class="star-interactive-group" id="starPicker" style="display: flex; gap: 8px; font-size: 32px; cursor: pointer; user-select: none;">
                                <span class="star-item" data-value="1" title="1 sao - Rất kém">★</span>
                                <span class="star-item" data-value="2" title="2 sao - Kém">★</span>
                                <span class="star-item" data-value="3" title="3 sao - Bình thường">★</span>
                                <span class="star-item" data-value="4" title="4 sao - Tốt">★</span>
                                <span class="star-item" data-value="5" title="5 sao - Rất tốt">★</span>
                            </div>
                            <span class="invalid-feedback" id="rating_err" style="color: #ff3b30; font-size: 13px; display: block; margin-top: 4px;"></span>
                        </div>

                        <!-- Comment Textarea -->
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="reviewComment" style="display: block; font-size: 14px; font-weight: 500; color: #555; margin-bottom: 8px;">
                                Nhận xét chi tiết:
                            </label>
                            <textarea name="comment" id="reviewComment" rows="4" placeholder="Chia sẻ cảm nhận của bạn về chất lượng tài liệu này..." style="width: 100%; border: 1px solid rgba(0,0,0,0.15); border-radius: 12px; padding: 14px; font-size: 15px; font-family: inherit; resize: vertical; box-sizing: border-box; outline: none; transition: border-color 0.2s;"></textarea>
                            <span class="invalid-feedback" id="comment_err" style="color: #ff3b30; font-size: 13px; display: block; margin-top: 4px;"></span>
                        </div>

                        <!-- Submit Button -->
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary" id="btnSubmitReview" style="padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 12px; background: var(--apple-blue, #0071e3); color: #fff; border: none; cursor: pointer;">
                                Gửi đánh giá
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <!-- DANH SÁCH BÌNH LUẬN & ĐÁNH GIÁ (REVIEWS LIST) -->
            <div class="reviews-list-container">
                <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 24px; color: #1d1d1f; display: flex; align-items: center; gap: 8px;">
                    <span>Tất cả nhận xét</span>
                    <span style="font-size: 15px; font-weight: 400; color: #86868b;" id="reviews-total-badge">(<?php echo count($data['reviews']); ?>)</span>
                </h3>

                <div id="reviewsList" style="display: flex; flex-direction: column; gap: 24px;">
                    <?php if (!empty($data['reviews'])) : ?>
                        <?php foreach ($data['reviews'] as $review) : ?>
                            <div class="review-item-card" id="review-<?php echo $review->id; ?>" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.06); border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.02);">

                                <!-- User & Rating Header -->
                                <div class="review-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <div class="review-user-info" style="display: flex; align-items: center; gap: 12px;">
                                        <div class="user-avatar-circle" style="width: 40px; height: 40px; border-radius: 50%; background: #0071e3; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; text-transform: uppercase;">
                                            <?php if (!empty($review->user_avatar)) : ?>
                                                <img src="<?php echo URLROOT . htmlspecialchars($review->user_avatar); ?>" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                            <?php else : ?>
                                                <?php echo mb_substr($review->user_name, 0, 1); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; font-size: 15px; color: #1d1d1f;">
                                                <?php echo htmlspecialchars($review->user_name); ?>
                                            </div>
                                            <div style="font-size: 12px; color: #86868b;">
                                                <?php echo date('d/m/Y H:i', strtotime($review->created_at)); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stars for this review -->
                                    <?php if ($review->rating) : ?>
                                        <div class="review-stars" style="color: #ffb800; font-size: 16px;">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $review->rating ? '★' : '☆';
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Review Comment Content -->
                                <div class="review-comment-body" style="font-size: 15px; color: #333; line-height: 1.6; margin-bottom: 16px;">
                                    <?php echo nl2br(htmlspecialchars($review->comment)); ?>
                                </div>

                                <!-- Reply Action Button -->
                                <?php if (isset($_SESSION['user_id'])) : ?>
                                    <div class="review-actions" style="margin-bottom: 12px;">
                                        <button type="button" class="btn-toggle-reply" data-parent-id="<?php echo $review->id; ?>" style="background: none; border: none; color: var(--apple-blue, #0071e3); font-size: 13px; font-weight: 600; cursor: pointer; padding: 0; display: flex; align-items: center; gap: 4px;">
                                            💬 Phản hồi
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <!-- Reply Input Form (Hidden by default, shown on click) (UC35) -->
                                <?php if (isset($_SESSION['user_id'])) : ?>
                                    <div class="reply-form-box" id="reply-form-<?php echo $review->id; ?>" style="display: none; margin-top: 12px; padding: 16px; background: #f9f9fb; border-radius: 12px;">
                                        <form class="form-reply-ajax" action="<?php echo URLROOT; ?>/reviews/reply" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">
                                            <input type="hidden" name="product_id" value="<?php echo $data['product']->id; ?>">
                                            <input type="hidden" name="parent_id" value="<?php echo $review->id; ?>">

                                            <div style="margin-bottom: 10px;">
                                                <textarea name="comment" rows="2" placeholder="Nhập câu trả lời/phản hồi..." style="width: 100%; border: 1px solid rgba(0,0,0,0.12); border-radius: 10px; padding: 10px; font-size: 14px; font-family: inherit; box-sizing: border-box; resize: vertical; outline: none;"></textarea>
                                            </div>
                                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                                <button type="button" class="btn-cancel-reply" data-parent-id="<?php echo $review->id; ?>" style="padding: 6px 14px; font-size: 13px; border-radius: 8px; background: #e5e5ea; border: none; cursor: pointer;">Hủy</button>
                                                <button type="submit" class="btn-submit-reply" style="padding: 6px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; background: var(--apple-blue, #0071e3); color: #fff; border: none; cursor: pointer;">Gửi trả lời</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <!-- Child Replies Container (UC35) -->
                                <div class="replies-list" id="replies-container-<?php echo $review->id; ?>" style="margin-top: 16px; display: flex; flex-direction: column; gap: 12px;">
                                    <?php if (!empty($review->replies)) : ?>
                                        <?php foreach ($review->replies as $reply) : ?>
                                            <div class="reply-item" style="margin-left: 36px; padding: 12px 16px; background: #f9f9fb; border-left: 3px solid var(--apple-blue, #0071e3); border-radius: 0 12px 12px 0;">
                                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                                    <span style="font-weight: 600; font-size: 14px; color: #1d1d1f;">
                                                        <?php echo htmlspecialchars($reply->user_name); ?>
                                                        <?php if (isset($data['product']->seller_id) && (int)$reply->user_id === (int)$data['product']->seller_id) : ?>
                                                            <span style="background: rgba(0,113,227,0.1); color: var(--apple-blue, #0071e3); font-size: 11px; padding: 2px 8px; border-radius: 10px; margin-left: 6px;">Người bán</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span style="font-size: 11px; color: #86868b;">
                                                        <?php echo date('d/m/Y H:i', strtotime($reply->created_at)); ?>
                                                    </span>
                                                </div>
                                                <div style="font-size: 14px; color: #444; line-height: 1.5;">
                                                    <?php echo nl2br(htmlspecialchars($reply->comment)); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <!-- Empty Reviews State -->
                        <div class="empty-reviews-state" id="empty-reviews-msg" style="text-align: center; padding: 48px 24px; background: #f9f9fb; border-radius: 16px; border: 1px dashed rgba(0,0,0,0.1);">
                            <div style="font-size: 36px; margin-bottom: 12px; opacity: 0.5;">⭐</div>
                            <h4 style="margin-bottom: 6px; font-size: 16px; font-weight: 600; color: #333;">Chưa có đánh giá nào</h4>
                            <p style="font-size: 14px; color: #86868b; margin: 0;">Hãy là người đầu tiên trải nghiệm và để lại nhận xét cho sản phẩm này!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- STYLES FOR STAR RATING & REVIEWS -->
<style>
    .star-item {
        color: #d2d2d7;
        transition: transform 0.15s ease, color 0.15s ease;
    }

    .star-item:hover,
    .star-item.active,
    .star-item.hovered {
        color: #ffb800;
        transform: scale(1.15);
    }

    #reviewComment:focus {
        border-color: var(--apple-blue, #0071e3) !important;
        box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.1) !important;
    }

    .btn-toggle-reply:hover {
        text-decoration: underline;
    }
</style>

<!-- JAVASCRIPT FOR STAR INTERACTION & AJAX REVIEW SUBMISSION (UC34, UC35) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // 1. STAR RATING INTERACTIVE LOGIC (UC34)
        const starPicker = document.getElementById('starPicker');
        const ratingInput = document.getElementById('ratingInput');
        const ratingLabel = document.getElementById('ratingLabel');
        const ratingErr = document.getElementById('rating_err');

        const ratingTexts = {
            1: '1 sao - Rất kém',
            2: '2 sao - Kém',
            3: '3 sao - Bình thường',
            4: '4 sao - Tốt',
            5: '5 sao - Rất tốt'
        };

        if (starPicker) {
            const stars = starPicker.querySelectorAll('.star-item');

            stars.forEach(function(star) {
                // Hover effect
                star.addEventListener('mouseenter', function() {
                    const val = parseInt(this.getAttribute('data-value'));
                    highlightStars(val);
                    if (ratingLabel) ratingLabel.textContent = '(' + ratingTexts[val] + ')';
                });

                // Mouse leave -> restore selected rating
                starPicker.addEventListener('mouseleave', function() {
                    const currentVal = parseInt(ratingInput.value) || 0;
                    highlightStars(currentVal);
                    if (ratingLabel) {
                        ratingLabel.textContent = currentVal > 0 ? '(' + ratingTexts[currentVal] + ')' : '';
                    }
                });

                // Click -> select rating
                star.addEventListener('click', function() {
                    const val = parseInt(this.getAttribute('data-value'));
                    ratingInput.value = val;
                    highlightStars(val);
                    if (ratingLabel) ratingLabel.textContent = '(' + ratingTexts[val] + ')';
                    if (ratingErr) ratingErr.textContent = '';
                });
            });

            function highlightStars(count) {
                stars.forEach(function(s) {
                    const sVal = parseInt(s.getAttribute('data-value'));
                    if (sVal <= count) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            }
        }

        // 2. AJAX SUBMIT FOR MAIN REVIEW FORM (UC34)
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Client Validation
                const selectedRating = parseInt(ratingInput.value) || 0;
                const commentVal = document.getElementById('reviewComment').value.trim();
                let hasErr = false;

                if (ratingErr) ratingErr.textContent = '';
                const commentErr = document.getElementById('comment_err');
                if (commentErr) commentErr.textContent = '';

                if (selectedRating <= 0) {
                    if (ratingErr) ratingErr.textContent = 'Vui lòng chọn số sao đánh giá';
                    hasErr = true;
                }

                if (!commentVal) {
                    if (commentErr) commentErr.textContent = 'Vui lòng nhập nhận xét';
                    hasErr = true;
                }

                if (hasErr) return;

                const submitBtn = document.getElementById('btnSubmitReview');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang gửi...';

                const formData = new FormData(reviewForm);

                fetch(reviewForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;

                        if (data.success) {
                            // Hiển thị flash thành công
                            if (typeof FlashModule !== 'undefined') {
                                FlashModule.show('success', data.message);
                            } else {
                                alert(data.message);
                            }

                            // Thay thế form bằng thông báo đã đánh giá
                            const parentContainer = reviewForm.parentElement;
                            parentContainer.innerHTML = '<div class="reviewed-notice" style="padding: 16px 20px; background: #e6f7ff; border: 1px solid #91d5ff; border-radius: 14px; color: #0050b3; font-size: 14px;">✅ Bạn đã gửi đánh giá cho sản phẩm này. Cảm ơn phản hồi của bạn!</div>';

                            // Dynamic UI update: Thêm review mới vào DOM ngay lập tức
                            if (data.review) {
                                appendNewReviewToDOM(data.review, selectedRating);
                            }

                            // Dynamic UI update: Cập nhật stats thanh phần trăm & điểm trung bình
                            if (data.rating_stats) {
                                updateRatingStatsUI(data.rating_stats);
                            }
                        } else {
                            if (data.require_login) {
                                window.location.href = '<?php echo URLROOT; ?>/users/login';
                                return;
                            }
                            if (typeof FlashModule !== 'undefined') {
                                FlashModule.show('error', data.message);
                            } else {
                                alert(data.message);
                            }
                        }
                    })
                    .catch(err => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        console.error('Error submitting review:', err);
                    });
            });
        }

        // 3. TOGGLE REPLY FORM & AJAX SUBMIT REPLY (UC35)
        document.addEventListener('click', function(e) {
            // Toggle reply form
            if (e.target.classList.contains('btn-toggle-reply')) {
                const parentId = e.target.getAttribute('data-parent-id');
                const replyBox = document.getElementById('reply-form-' + parentId);
                if (replyBox) {
                    replyBox.style.display = replyBox.style.display === 'none' ? 'block' : 'none';
                }
            }

            // Cancel reply
            if (e.target.classList.contains('btn-cancel-reply')) {
                const parentId = e.target.getAttribute('data-parent-id');
                const replyBox = document.getElementById('reply-form-' + parentId);
                if (replyBox) {
                    replyBox.style.display = 'none';
                }
            }
        });

        // AJAX Submit Reply (UC35)
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('form-reply-ajax')) {
                e.preventDefault();

                const form = e.target;
                const commentInput = form.querySelector('textarea[name="comment"]');
                const commentVal = commentInput.value.trim();

                if (!commentVal) {
                    alert('Vui lòng nhập nội dung phản hồi');
                    return;
                }

                const submitBtn = form.querySelector('.btn-submit-reply');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang gửi...';

                const formData = new FormData(form);

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Gửi trả lời';

                        if (data.success) {
                            if (typeof FlashModule !== 'undefined') {
                                FlashModule.show('success', data.message);
                            }

                            // Reset form & hide
                            commentInput.value = '';
                            const parentId = form.querySelector('input[name="parent_id"]').value;
                            const replyBox = document.getElementById('reply-form-' + parentId);
                            if (replyBox) replyBox.style.display = 'none';

                            // Thêm reply vào danh sách ngay lập tức
                            if (data.reply) {
                                appendReplyToDOM(parentId, data.reply);
                            }
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(err => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Gửi trả lời';
                        console.error('Reply error:', err);
                    });
            }
        });

        // 4. ADD TO CART (UC18) AJAX
        const btnAddToCart = document.getElementById('btnAddToCart');
        if (btnAddToCart) {
            btnAddToCart.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const originalHtml = this.innerHTML;

                this.disabled = true;
                this.querySelector('span').textContent = 'Đang thêm...';

                const formData = new FormData();
                formData.append('product_id', productId);

                fetch('<?php echo URLROOT; ?>/carts/add', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.disabled = false;
                    if (data.success) {
                        this.querySelector('span').textContent = '✓ Đã có trong giỏ';
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show('success', data.message);
                        } else {
                            alert(data.message);
                        }
                        // Update navbar badge
                        const badges = document.querySelectorAll('#nav-cart-badge');
                        badges.forEach(b => {
                            b.textContent = data.cart_count;
                            b.style.display = 'flex';
                        });
                    } else {
                        this.innerHTML = originalHtml;
                        alert(data.message);
                    }
                })
                .catch(err => {
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                    console.error('Add cart error:', err);
                });
            });
        }

        // 5. TOGGLE FAVORITE (UC17) AJAX
        const btnToggleFavorite = document.getElementById('btnToggleFavorite');
        if (btnToggleFavorite) {
            btnToggleFavorite.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const favIcon = this.querySelector('.fav-icon');
                const favText = document.getElementById('favText');

                this.disabled = true;

                const formData = new FormData();
                formData.append('product_id', productId);

                fetch('<?php echo URLROOT; ?>/favorites/toggle', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.disabled = false;
                    if (data.success) {
                        if (data.is_favorited) {
                            this.style.color = '#ff3b30';
                            if (favIcon) {
                                favIcon.setAttribute('fill', '#ff3b30');
                                favIcon.setAttribute('stroke', '#ff3b30');
                            }
                            if (favText) favText.textContent = 'Đã yêu thích';
                        } else {
                            this.style.color = '#1d1d1f';
                            if (favIcon) {
                                favIcon.setAttribute('fill', 'none');
                                favIcon.setAttribute('stroke', 'currentColor');
                            }
                            if (favText) favText.textContent = 'Yêu thích';
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
                    this.disabled = false;
                    console.error('Toggle favorite error:', err);
                });
            });
        }

        // HELPER: Append new review to DOM
        function appendNewReviewToDOM(reviewData, ratingVal) {
            const emptyMsg = document.getElementById('empty-reviews-msg');
            if (emptyMsg) emptyMsg.remove();

            const reviewsList = document.getElementById('reviewsList');
            if (!reviewsList) return;

            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                starsHtml += i <= ratingVal ? '★' : '☆';
            }

            const initial = reviewData.user_name ? reviewData.user_name.charAt(0).toUpperCase() : 'U';

            const reviewCardHtml = `
            <div class="review-item-card" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.06); border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.02); animation: fadeIn 0.4s ease;">
                <div class="review-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div class="review-user-info" style="display: flex; align-items: center; gap: 12px;">
                        <div class="user-avatar-circle" style="width: 40px; height: 40px; border-radius: 50%; background: #0071e3; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px;">
                            ${initial}
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 15px; color: #1d1d1f;">${escapeHtml(reviewData.user_name)}</div>
                            <div style="font-size: 12px; color: #86868b;">Vừa xong</div>
                        </div>
                    </div>
                    <div class="review-stars" style="color: #ffb800; font-size: 16px;">${starsHtml}</div>
                </div>
                <div class="review-comment-body" style="font-size: 15px; color: #333; line-height: 1.6; margin-bottom: 16px;">
                    ${escapeHtml(reviewData.comment).replace(/\n/g, '<br>')}
                </div>
            </div>
        `;

            reviewsList.insertAdjacentHTML('afterbegin', reviewCardHtml);
        }

        // HELPER: Append new reply to DOM
        function appendReplyToDOM(parentId, replyData) {
            const repliesContainer = document.getElementById('replies-container-' + parentId);
            if (!repliesContainer) return;

            const replyHtml = `
            <div class="reply-item" style="margin-left: 36px; padding: 12px 16px; background: #f9f9fb; border-left: 3px solid var(--apple-blue, #0071e3); border-radius: 0 12px 12px 0; animation: fadeIn 0.3s ease;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-weight: 600; font-size: 14px; color: #1d1d1f;">${escapeHtml(replyData.user_name)}</span>
                    <span style="font-size: 11px; color: #86868b;">Vừa xong</span>
                </div>
                <div style="font-size: 14px; color: #444; line-height: 1.5;">${escapeHtml(replyData.comment).replace(/\n/g, '<br>')}</div>
            </div>
        `;

            repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
        }

        // HELPER: Update Rating Stats UI
        function updateRatingStatsUI(stats) {
            if (!stats) return;

            const avgScore = document.getElementById('stats-avg-score');
            if (avgScore) avgScore.textContent = parseFloat(stats.average).toFixed(1);

            const totalCount = document.getElementById('stats-total-count');
            if (totalCount) totalCount.textContent = 'Dựa trên ' + stats.total + ' đánh giá';

            const heroCount = document.getElementById('hero-review-count');
            if (heroCount) heroCount.textContent = '(' + stats.total + ' đánh giá)';

            for (let star = 1; star <= 5; star++) {
                const count = stats[star] || 0;
                const pct = stats.total > 0 ? Math.round((count / stats.total) * 100) : 0;

                const barFill = document.getElementById('bar-fill-' + star);
                if (barFill) barFill.style.width = pct + '%';

                const barCount = document.getElementById('bar-count-' + star);
                if (barCount) barCount.textContent = count;
            }
        }

        function escapeHtml(str) {
            return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
    });
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
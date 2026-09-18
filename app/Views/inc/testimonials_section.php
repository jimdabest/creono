<?php
/**
 * Testimonials Section Partial
 * Hiển thị cảm nhận & đánh giá của khách hàng theo phong cách Apple / Bento Grid cao cấp
 * @var array $testimonials
 */
$testimonials = $testimonials ?? ($data['testimonials'] ?? []);
?>

<section class="testimonials-section">
    <div class="container">
        <!-- Section Header -->
        <div class="tm-section-header">
            <span class="tm-section-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                Cộng đồng & Niềm tin
            </span>
            <h2 class="tm-section-title">Khách hàng nói gì về Creono?</h2>
            <p class="tm-section-desc">
                Hàng ngàn nhà phát triển, sinh viên và nhà sáng tạo đã lựa chọn Creono để trao đổi tài nguyên và chia sẻ kiến thức số.
            </p>
        </div>

        <!-- Grid of Testimonials -->
        <?php if (!empty($testimonials)) : ?>
            <div class="tm-grid">
                <?php foreach ($testimonials as $tm) : ?>
                    <div class="tm-card <?php echo !empty($tm->is_featured) ? 'featured-highlight' : ''; ?>">
                        <div>
                            <!-- Star Rating -->
                            <div class="tm-card-stars">
                                <?php echo str_repeat('★', (int)$tm->rating) . str_repeat('☆', 5 - (int)$tm->rating); ?>
                            </div>

                            <!-- Quote Content -->
                            <p class="tm-card-quote">
                                <?php echo htmlspecialchars($tm->content); ?>
                            </p>
                        </div>

                        <!-- Author Meta -->
                        <div class="tm-card-author">
                            <div class="tm-card-avatar">
                                <?php if (!empty($tm->avatar_url)) : ?>
                                    <img src="<?php echo URLROOT . '/' . ltrim(htmlspecialchars($tm->avatar_url), '/'); ?>" alt="<?php echo htmlspecialchars($tm->user_name ?? 'User'); ?>">
                                <?php else : ?>
                                    <?php echo strtoupper(mb_substr($tm->user_name ?? 'U', 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="tm-card-info">
                                <h4><?php echo htmlspecialchars($tm->user_name ?? 'Thành viên Creono'); ?></h4>
                                <p>Thành viên xác thực &bull; Creono Community</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="tm-empty-frontend">
                <p>Chưa có đánh giá nào được hiển thị.</p>
            </div>
        <?php endif; ?>
    </div>
</section>


<?php
/**
 * Testimonials Section Partial
 * Hiển thị cảm nhận & đánh giá của khách hàng theo phong cách Apple / Bento Grid cao cấp
 * @var array $testimonials
 */
$testimonials = $testimonials ?? ($data['testimonials'] ?? []);
?>

<style>
/* ==========================================================================
   TESTIMONIALS SECTION (Apple-inspired Bento Grid)
   ========================================================================== */
.testimonials-section {
    padding: 80px 0;
    background: linear-gradient(180deg, rgba(245, 245, 247, 0.5) 0%, rgba(255, 255, 255, 1) 100%);
    position: relative;
    overflow: hidden;
}

.tm-section-header {
    text-align: center;
    max-width: 680px;
    margin: 0 auto 50px auto;
}

.tm-section-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0, 113, 227, 0.08);
    color: var(--apple-blue);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 16px;
    letter-spacing: -0.2px;
}

.tm-section-title {
    font-size: 38px;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--apple-black);
    margin-bottom: 14px;
    line-height: 1.2;
}

.tm-section-desc {
    font-size: 17px;
    color: var(--apple-gray);
    line-height: 1.5;
}

.tm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
    margin-top: 10px;
}

.tm-card {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: 20px;
    padding: 30px 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}

.tm-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    border-color: rgba(0, 113, 227, 0.2);
}

.tm-card.featured-highlight {
    border: 1.5px solid rgba(0, 113, 227, 0.3);
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.tm-card.featured-highlight::before {
    content: "Nổi bật";
    position: absolute;
    top: 16px;
    right: 18px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #0071e3;
    background: rgba(0, 113, 227, 0.1);
    padding: 3px 8px;
    border-radius: 12px;
}

.tm-card-stars {
    color: #ff9500;
    font-size: 16px;
    letter-spacing: 2px;
    margin-bottom: 16px;
}

.tm-card-quote {
    font-size: 15.5px;
    line-height: 1.6;
    color: #1d1d1f;
    margin-bottom: 24px;
    flex-grow: 1;
}

.tm-card-quote::before {
    content: "“";
    font-size: 24px;
    font-family: Georgia, serif;
    color: var(--apple-blue);
    line-height: 0;
    margin-right: 4px;
    vertical-align: -4px;
}

.tm-card-author {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-top: 18px;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.tm-card-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, #0071e3 0%, #42a5f5 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 113, 227, 0.2);
}

.tm-card-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tm-card-info h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--apple-black);
}

.tm-card-info p {
    margin: 2px 0 0 0;
    font-size: 12.5px;
    color: var(--apple-gray);
}

@media (max-width: 768px) {
    .testimonials-section {
        padding: 50px 0;
    }
    .tm-section-title {
        font-size: 28px;
    }
    .tm-grid {
        grid-template-columns: 1fr;
    }
}
</style>

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
            <div style="text-align: center; padding: 40px; color: var(--apple-gray);">
                <p>Chưa có đánh giá nào được hiển thị.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

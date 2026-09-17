<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
/* ==========================================================================
   ABOUT PAGE STYLING
   ========================================================================== */
.about-hero {
    padding: 70px 0 50px;
    text-align: center;
    background: radial-gradient(circle at center, rgba(0, 113, 227, 0.04) 0%, rgba(255, 255, 255, 0) 70%);
}

.about-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0, 113, 227, 0.08);
    color: var(--apple-blue);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 18px;
}

.about-hero h1 {
    font-size: 48px;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--apple-black);
    line-height: 1.15;
    margin-bottom: 18px;
}

.about-hero p.lead {
    font-size: 20px;
    color: var(--apple-gray);
    max-width: 680px;
    margin: 0 auto 30px auto;
    line-height: 1.5;
}

/* Bento Pillars */
.about-pillars-section {
    padding: 40px 0 60px;
}

.pillars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.pillar-card {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: 18px;
    padding: 28px 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    transition: transform 0.2s, box-shadow 0.2s;
}

.pillar-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
}

.pillar-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 18px;
}

.pillar-icon.blue { background: rgba(0, 113, 227, 0.1); color: var(--apple-blue); }
.pillar-icon.green { background: rgba(52, 199, 89, 0.1); color: var(--apple-green); }
.pillar-icon.purple { background: rgba(175, 82, 222, 0.1); color: #af52de; }
.pillar-icon.orange { background: rgba(255, 149, 0, 0.1); color: var(--apple-orange); }

.pillar-card h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--apple-black);
}

.pillar-card p {
    font-size: 14px;
    color: var(--apple-gray);
    line-height: 1.55;
    margin: 0;
}

/* About CTA */
.about-cta-box {
    background: #1d1d1f;
    border-radius: 24px;
    color: #fff;
    padding: 60px 40px;
    text-align: center;
    margin: 40px auto 80px auto;
    max-width: 900px;
}

.about-cta-box h2 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 14px;
    color: #fff;
}

.about-cta-box p {
    font-size: 16px;
    color: rgba(255, 255, 255, 0.75);
    max-width: 600px;
    margin: 0 auto 28px auto;
    line-height: 1.5;
}

.btn-cta-white {
    background: #fff;
    color: #1d1d1f;
    padding: 12px 28px;
    border-radius: 24px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.btn-cta-white:hover {
    background: #f0f0f5;
    transform: scale(1.03);
}

@media (max-width: 768px) {
    .about-hero h1 {
        font-size: 32px;
    }
    .about-hero p.lead {
        font-size: 16px;
    }
    .about-cta-box {
        padding: 40px 20px;
    }
    .about-cta-box h2 {
        font-size: 24px;
    }
}
</style>

<!-- Hero Section -->
<section class="about-hero">
    <div class="container">
        <span class="about-hero-badge">Về Creono Marketplace</span>
        <h1>Nền tảng mua bán & chia sẻ<br>tài nguyên số hàng đầu.</h1>
        <p class="lead">
            Creono là hệ thống C2C Marketplace chuyên biệt cho các sản phẩm trí tuệ: source code, đồ án, tài liệu học thuật và tài nguyên thiết kế số cao cấp.
        </p>
    </div>
</section>

<!-- Giá trị cốt lõi / Pillars -->
<section class="about-pillars-section">
    <div class="container">
        <div class="pillars-grid">
            <div class="pillar-card">
                <div class="pillar-icon blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3>Kiểm duyệt chặt chẽ</h3>
                <p>Mỗi tài liệu và source code được đăng tải đều trải qua quy trình xác minh chất lượng và phát hiện vi phạm bản quyền tự động.</p>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </div>
                <h3>Tải về tức thì</h3>
                <p>Thanh toán bảo mật qua Ví điện tử Creono hoặc cổng thanh toán nội địa, nhận link tải tài liệu nguyên bản ngay lập tức.</p>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon purple">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3>Cộng đồng sáng tạo</h3>
                <p>Kết nối hàng ngàn lập trình viên, nhà thiết kế và tác giả tự do, giúp tối đa hóa thu nhập từ các dự án cá nhân.</p>
            </div>

            <div class="pillar-card">
                <div class="pillar-icon orange">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <h3>Hỗ trợ 24/7</h3>
                <p>Chính sách hoàn tiền minh bạch và cơ chế bảo vệ quyền lợi người mua khi tài liệu không đúng cam kết mô tả.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section Partial -->
<?php require APPROOT . '/Views/inc/testimonials_section.php'; ?>

<!-- CTA Section -->
<div class="container">
    <div class="about-cta-box">
        <h2>Sẵn sàng bắt đầu với Creono?</h2>
        <p>Tham gia ngay hôm nay để khám phá kho tài nguyên số đa dạng hoặc mở gian hàng kinh doanh tri thức của bạn.</p>
        <a href="<?php echo URLROOT; ?>/products/index" class="btn-cta-white">Khám phá chợ tài liệu ↗</a>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- CSS chuyên biệt cho trang Giới thiệu & Testimonials -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/about.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/testimonials-section.css?v=<?php echo time(); ?>">

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
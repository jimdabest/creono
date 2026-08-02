<?php
/**
 * Footer Template cho Creono Project
 * Sử dụng Vanilla JS, không phụ thuộc thư viện
 */
?>

        </div> <!-- Đóng container -->
    </main> <!-- Đóng main-content -->

    <!-- ====================== FOOTER ====================== -->
    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">
                <!-- Cột 1: Thông tin -->
                <div class="footer-col">
                    <h4><?php echo SITENAME; ?></h4>
                    <p>Nền tảng mua bán tài liệu số C2C hàng đầu.</p>
                    <p class="footer-version">Version 1.0.0</p>
                </div>
                
                <!-- Cột 2: Liên kết nhanh -->
                <div class="footer-col">
                    <h4>Liên kết</h4>
                    <ul>
                        <li><a href="<?php echo URLROOT; ?>/pages/about">Giới thiệu</a></li>
                        <li><a href="<?php echo URLROOT; ?>/products/index">Chợ tài liệu</a></li>
                        <li><a href="#">Điều khoản</a></li>
                        <li><a href="#">Bảo mật</a></li>
                    </ul>
                </div>
                
                <!-- Cột 3: Hỗ trợ -->
                <div class="footer-col">
                    <h4>Hỗ trợ</h4>
                    <ul>
                        <li><a href="#">Trung tâm trợ giúp</a></li>
                        <li><a href="#">Hướng dẫn</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="mailto:support@creono.com">Email hỗ trợ</a></li>
                    </ul>
                </div>
                
                <!-- Cột 4: Mạng xã hội -->
                <div class="footer-col">
                    <h4>Theo dõi</h4>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook" class="social-link">📘</a>
                        <a href="#" aria-label="Twitter" class="social-link">🐦</a>
                        <a href="#" aria-label="YouTube" class="social-link">▶️</a>
                        <a href="#" aria-label="GitHub" class="social-link">🐙</a>
                    </div>
                    <p class="footer-email">
                        <a href="mailto:contact@creono.com">contact@creono.com</a>
                    </p>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITENAME; ?>. All rights reserved.</p>
                <p class="footer-tech">
                    Built with ❤️ using Vanilla PHP &amp; Vanilla JS
                </p>
            </div>
        </div>
    </footer>

    <!-- ====================== BACK TO TOP BUTTON ====================== -->
    <!-- Được tạo tự động bởi JavaScript -->
    
    <!-- ====================== LOADING OVERLAY ====================== -->
    <!-- Được tạo tự động bởi JavaScript -->

    <!-- ====================== JAVASCRIPT ====================== -->
    <!-- 
        Lưu ý: Các file JS đã được load ở header để tối ưu performance.
        Tuy nhiên, nếu muốn load ở cuối trang để cải thiện tốc độ load,
        có thể chuyển các script tag từ header xuống đây.
    -->
    
    <!-- Script cho các trang cụ thể (nếu cần) -->
    <?php if (isset($data['custom_js'])) : ?>
        <?php foreach ($data['custom_js'] as $js_file) : ?>
            <script src="<?php echo URLROOT; ?>/js/<?php echo $js_file; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline script cho trang hiện tại (nếu có) -->
    <?php if (isset($data['inline_js'])) : ?>
        <script>
            <?php echo $data['inline_js']; ?>
        </script>
    <?php endif; ?>

    <!-- Performance: Lazy load các script không quan trọng -->
    <script>
        // Lazy load các script không cần thiết ngay
        document.addEventListener('DOMContentLoaded', function() {
            // Ví dụ: load analytics sau khi trang đã load xong
            // const analyticsScript = document.createElement('script');
            // analyticsScript.src = 'https://analytics.example.com/script.js';
            // document.body.appendChild(analyticsScript);
        });
    </script>
</body>
</html>
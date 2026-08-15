<?php
/**
 * Footer Template - Apple Style (Không emoji)
 */
?>

        </div>
    </main>

    <!-- ====== FOOTER ====== -->
    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4><?php echo SITENAME; ?></h4>
                    <p>Nền tảng mua bán tài liệu số C2C hàng đầu.</p>
                    <p class="footer-version">Version 1.0.0</p>
                </div>
                
                <div class="footer-col">
                    <h4>Liên kết</h4>
                    <ul>
                        <li><a href="<?php echo URLROOT; ?>/pages/about">Giới thiệu</a></li>
                        <li><a href="<?php echo URLROOT; ?>/products/index">Chợ tài liệu</a></li>
                        <li><a href="#">Điều khoản</a></li>
                        <li><a href="#">Bảo mật</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Hỗ trợ</h4>
                    <ul>
                        <li><a href="#">Trung tâm trợ giúp</a></li>
                        <li><a href="#">Hướng dẫn</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="mailto:support@creono.com">Email hỗ trợ</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Theo dõi</h4>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook" class="social-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Twitter" class="social-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="YouTube" class="social-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="GitHub" class="social-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.15 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.62.24 2.85.12 3.15.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
                            </svg>
                        </a>
                    </div>
                    <p class="footer-email">
                        <a href="mailto:contact@creono.com">contact@creono.com</a>
                    </p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITENAME; ?>. All rights reserved.</p>
                <p class="footer-tech">Built with Vanilla PHP &amp; Vanilla JS</p>
            </div>
        </div>
    </footer>

    <!-- Global Cart Badge Loader -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('<?php echo URLROOT; ?>/carts/count')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    const badges = document.querySelectorAll('#nav-cart-badge');
                    badges.forEach(b => {
                        b.textContent = data.count;
                        b.style.display = 'flex';
                    });
                }
            })
            .catch(function(err) { /* silent */ });
    });
    </script>
</body>
</html>
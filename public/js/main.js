// public/js/main.js
'use strict';

/**
 * Main JavaScript cho dự án Creono
 * Sử dụng Vanilla JS, không phụ thuộc thư viện
 */

// Import các module
// Sử dụng IIFE để không làm ô nhiễm global scope
(function () {
    'use strict';

    console.log('🚀 Creono project loaded with Vanilla JS!');

    /**
     * Khởi tạo tất cả modules khi DOM đã sẵn sàng
     */
    function init() {
        // Đợi DOM load xong
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initModules);
        } else {
            initModules();
        }
    }

    /**
     * Khởi tạo các module
     */
    function initModules() {
        try {
            // Kiểm tra các module đã được định nghĩa
            if (typeof FlashModule !== 'undefined') {
                FlashModule.init();
                console.log('✅ FlashModule initialized');
            }

            if (typeof ValidationModule !== 'undefined') {
                ValidationModule.init();
                console.log('✅ ValidationModule initialized');
            }

            if (typeof AuthModule !== 'undefined') {
                AuthModule.init();
                console.log('✅ AuthModule initialized');
            }

            if (typeof ProfileModule !== 'undefined') {
                ProfileModule.init();
                console.log('✅ ProfileModule initialized');
            }

            // ====== MODULE MỚI: AJAX Form ======
            if (typeof AjaxFormModule !== 'undefined') {
                AjaxFormModule.init();
                console.log('✅ AjaxFormModule initialized');
            }

            // ====== MODULE BÁO CÁO VI PHẠM ======
            if (typeof ReportModule !== 'undefined') {
                ReportModule.init();
                console.log('✅ ReportModule initialized');
            }

            // Các tính năng bổ sung
            initNavbar();
            initNavbarScroll(); // THÊM MỚI: Hiệu ứng scroll cho navbar
            initDropdown();
            initBackToTop();
            initFormLoading(); // Thêm loading cho form không dùng AJAX

            console.log('✅ All modules initialized successfully');
        } catch (error) {
            console.error('Error initializing modules:', error);
        }
    }

    /**
     * Xử lý Navbar (responsive)
     */
    function initNavbar() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;

        let toggleBtn = navbar.querySelector('.navbar-toggle');
        if (!toggleBtn) {
            toggleBtn = document.createElement('button');
            toggleBtn.className = 'navbar-toggle';
            toggleBtn.innerHTML = '☰';
            toggleBtn.setAttribute('aria-label', 'Toggle navigation');
            toggleBtn.setAttribute('aria-expanded', 'false');
            const container = navbar.querySelector('.container');
            if (container) container.appendChild(toggleBtn);
        }

        const navLinks = document.querySelector('.nav-links');

        // Toggle menu khi click nút
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!navLinks) return;
            const isOpen = navLinks.classList.toggle('active');
            toggleBtn.setAttribute('aria-expanded', isOpen);
            toggleBtn.innerHTML = isOpen ? '✕' : '☰';
        });

        // Đóng menu khi click ra ngoài
        document.addEventListener('click', function (e) {
            if (navLinks && navLinks.classList.contains('active')) {
                if (!navbar.contains(e.target)) {
                    navLinks.classList.remove('active');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    toggleBtn.innerHTML = '☰';
                }
            }
        });

        // Xử lý resize: ẩn toggle trên desktop, hiển thị menu bình thường
        function handleResize() {
            const isMobile = window.innerWidth <= 735;
            toggleBtn.style.display = isMobile ? 'block' : 'none';

            if (!isMobile && navLinks) {
                navLinks.classList.remove('active');
                navLinks.style.display = '';
                navLinks.style.flexDirection = '';
                navLinks.style.width = '';
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.innerHTML = '☰';
            }
        }

        handleResize();
        window.addEventListener('resize', handleResize);
    }

    /**
     * ========== APPLE NAVBAR SCROLL EFFECT ==========
     * Hiệu ứng glassmorphism khi scroll (giống Apple Store)
     */
    function initNavbarScroll() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;

        let ticking = false;
        let lastScrollY = 0;

        function updateNavbar() {
            const scrollY = window.scrollY;

            // Thêm class 'scrolled' khi scroll > 50px
            if (scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            // Cập nhật độ trong suốt dựa trên scroll (Apple style)
            const progress = Math.min(scrollY / 200, 1);
            const opacity = 0.72 + (progress * 0.13); // 0.72 -> 0.85
            navbar.style.setProperty('--nav-bg-opacity', opacity);

            lastScrollY = scrollY;
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    updateNavbar();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // Khởi tạo lần đầu
        updateNavbar();
    }

    /**
     * Xử lý Dropdown Menu cho User
     */
    function initDropdown() {
        const navDropdown = document.querySelector('.nav-dropdown');
        if (!navDropdown) return; // Nếu chưa đăng nhập thì bỏ qua

        const navUser = navDropdown.querySelector('.nav-user');
        const dropdownMenu = navDropdown.querySelector('.dropdown-menu');

        if (navUser && dropdownMenu) {
            // Xử lý khi click vào tên user
            navUser.addEventListener('click', function (e) {
                e.preventDefault(); // Ngăn trình duyệt nhảy trang
                dropdownMenu.classList.toggle('active');
                navDropdown.classList.toggle('active'); // Để xoay cái mũi tên
            });

            // Xử lý bấm ra ngoài vùng menu thì tự động đóng lại
            document.addEventListener('click', function (e) {
                if (!navDropdown.contains(e.target)) {
                    dropdownMenu.classList.remove('active');
                    navDropdown.classList.remove('active');
                }
            });
        }
    }

    /**
     * Nút "Back to Top" - Apple Style
     */
    function initBackToTop() {
        // Tạo nút
        const backToTop = document.createElement('button');
        backToTop.className = 'back-to-top';
        backToTop.innerHTML = '↑';
        backToTop.setAttribute('aria-label', 'Back to top');

        // Apple style cho nút back to top
        backToTop.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #ffffff;
            color: #1d1d1f;
            border: 1px solid #d2d2d7;
            font-size: 20px;
            font-weight: 300;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            z-index: 999;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: inherit;
        `;

        document.body.appendChild(backToTop);

        // Xử lý scroll
        let isVisible = false;
        window.addEventListener('scroll', function () {
            const scrollY = window.scrollY;
            const shouldShow = scrollY > 300;

            if (shouldShow !== isVisible) {
                isVisible = shouldShow;
                if (shouldShow) {
                    backToTop.classList.add('visible');
                    backToTop.style.opacity = '1';
                    backToTop.style.visibility = 'visible';
                    backToTop.style.transform = 'translateY(0)';
                } else {
                    backToTop.classList.remove('visible');
                    backToTop.style.opacity = '0';
                    backToTop.style.visibility = 'hidden';
                    backToTop.style.transform = 'translateY(12px)';
                }
            }
        }, { passive: true });

        // Xử lý click - smooth scroll
        backToTop.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Hover effect
        backToTop.addEventListener('mouseenter', function () {
            this.style.background = '#1d1d1f';
            this.style.color = '#ffffff';
            this.style.borderColor = '#1d1d1f';
            this.style.transform = 'scale(1.05)';
        });

        backToTop.addEventListener('mouseleave', function () {
            this.style.background = '#ffffff';
            this.style.color = '#1d1d1f';
            this.style.borderColor = '#d2d2d7';
            this.style.transform = 'scale(1)';
        });
    }

    /**
     * Thêm loading cho form không dùng AJAX (fallback)
     */
    function initFormLoading() {
        // Chỉ áp dụng cho các form không có data-ajax
        const forms = document.querySelectorAll('form:not([data-ajax])');

        forms.forEach(function (form) {
            // Chỉ áp dụng cho form có action là các route quan trọng
            const action = form.getAttribute('action') || '';
            const importantActions = ['login', 'register', 'updateProfile', 'changePassword'];

            const isImportant = importantActions.some(function (keyword) {
                return action.includes(keyword);
            });

            if (!isImportant) return;

            form.addEventListener('submit', function () {
                const submitBtn = this.querySelector('[type="submit"]');

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Đang xử lý...';
                }

                if (typeof window.showLoading === 'function') {
                    window.showLoading(true);
                }
            });
        });
    }

    /**
     * Hàm tiện ích: show loading spinner (Apple Style)
     */
    window.showLoading = function (show = true, message = 'Đang xử lý...') {
        let loader = document.querySelector('.loader-overlay');

        if (show) {
            if (!loader) {
                loader = document.createElement('div');
                loader.className = 'loader-overlay';
                loader.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.85);
                    backdrop-filter: saturate(180%) blur(16px);
                    -webkit-backdrop-filter: saturate(180%) blur(16px);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 99999;
                    transition: all 0.3s ease;
                `;

                // Container cho spinner + text
                const container = document.createElement('div');
                container.style.cssText = `
                    text-align: center;
                    color: #1d1d1f;
                `;

                // Spinner Apple style
                const spinner = document.createElement('div');
                spinner.className = 'loader-spinner';
                spinner.style.cssText = `
                    width: 40px;
                    height: 40px;
                    border: 3px solid #d2d2d7;
                    border-top: 3px solid #0071e3;
                    border-radius: 50%;
                    animation: spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
                    margin: 0 auto 16px;
                `;

                // Text
                const text = document.createElement('p');
                text.className = 'loader-text';
                text.textContent = message;
                text.style.cssText = `
                    font-size: 16px;
                    font-weight: 400;
                    color: #86868b;
                    letter-spacing: -0.016em;
                    font-family: inherit;
                `;

                // Thêm keyframe animation
                if (!document.querySelector('#loader-styles')) {
                    const style = document.createElement('style');
                    style.id = 'loader-styles';
                    style.textContent = `
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                        .loader-overlay {
                            animation: fadeIn 0.3s ease;
                        }
                        @keyframes fadeIn {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }
                    `;
                    document.head.appendChild(style);
                }

                container.appendChild(spinner);
                container.appendChild(text);
                loader.appendChild(container);
                document.body.appendChild(loader);
            } else {
                // Cập nhật message nếu có
                const textEl = loader.querySelector('.loader-text');
                if (textEl) {
                    textEl.textContent = message;
                }
            }
            loader.style.display = 'flex';
            // Force reflow để animation chạy
            void loader.offsetWidth;
        } else {
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(function () {
                    loader.style.display = 'none';
                    loader.style.opacity = '1';
                }, 300);
            }
        }
    };

    // Khởi tạo khi trang load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
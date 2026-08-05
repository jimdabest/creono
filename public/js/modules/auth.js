// public/js/modules/auth.js
'use strict';

/**
 * Module xử lý authentication
 */
const AuthModule = (function() {
    'use strict';

    /**
     * Khởi tạo các tính năng auth
     */
    function init() {
        const logoutLinks = document.querySelectorAll('a[href*="logout"]');
        logoutLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault(); // Chặn chuyển trang ngay lập tức
                const targetHref = this.href;

                // Dùng Dialog mới tạo thay cho confirm()
                Utils.Dialog.show({
                    title: 'Đăng xuất',
                    message: 'Bạn có chắc chắn muốn đăng xuất khỏi Creono?',
                    type: 'confirm',
                    confirmText: 'Đăng xuất',
                    onConfirm: function() {
                        window.location.href = targetHref;
                    }
                });
            });
        });

        // Tự động focus vào input đầu tiên của form login
        const loginForm = document.querySelector('form[action*="login"]');
        if (loginForm) {
            const firstInput = loginForm.querySelector('input:not([type="hidden"])');
            if (firstInput) {
                firstInput.focus();
            }
        }
    }

    /**
     * Kiểm tra trạng thái đăng nhập
     */
    function isLoggedIn() {
        // Kiểm tra xem có user_id trong session không (thông qua element hidden)
        const userIdElement = document.querySelector('[data-user-id]');
        return userIdElement !== null;
    }

    /**
     * Lấy thông tin user từ DOM
     */
    function getUserInfo() {
        const userElement = document.querySelector('[data-user]');
        if (userElement) {
            try {
                return JSON.parse(userElement.getAttribute('data-user'));
            } catch (e) {
                return null;
            }
        }
        return null;
    }

    // Public API
    return {
        init: init,
        isLoggedIn: isLoggedIn,
        getUserInfo: getUserInfo
    };

})();

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthModule;
}
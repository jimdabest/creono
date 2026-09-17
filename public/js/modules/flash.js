// public/js/modules/flash.js
'use strict';

/**
 * Module xử lý flash messages (Apple Toast Style)
 */
const FlashModule = (function() {
    'use strict';

    /**
     * Khởi tạo flash messages
     */
    function init() {
        // #toast-container đã được PHP render sẵn trong HTML với các alert bên trong.
        // JS chỉ cần setup sự kiện đóng và auto-dismiss, không cần di chuyển DOM nữa.
        let container = document.getElementById('toast-container');

        // Nếu không có container từ server (không có flash), tạo sẵn để show() dùng sau
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        // Setup tất cả alert hiện có trong container
        const alerts = container.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setupAlert(alert);
        });
    }

    /**
     * Setup sự kiện cho từng alert
     */
    function setupAlert(alert) {
        // Tạo nút đóng nếu chưa có
        let closeBtn = alert.querySelector('.alert-close');
        if (!closeBtn) {
            closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Đóng thông báo');
            alert.appendChild(closeBtn);
        }
        
        // Xử lý sự kiện click nút đóng
        closeBtn.addEventListener('click', function() {
            fadeOut(alert, 300, function() {
                alert.remove();
            });
        });

        // Tự động ẩn (Mặc định 5s)
        const autoDismiss = alert.getAttribute('data-auto-dismiss') || 5000;
        setTimeout(function() {
            if (alert.parentNode) {
                fadeOut(alert, 300, function() {
                    alert.remove();
                });
            }
        }, parseInt(autoDismiss));
    }

    /**
     * Hiệu ứng fade out mượt mà lúc thu về
     */
    function fadeOut(element, duration = 300, callback) {
        element.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
        element.style.opacity = '0';
        element.style.transform = 'translateY(-16px) scale(0.9)';
        
        setTimeout(() => {
            element.style.display = 'none';
            if (typeof callback === 'function') {
                callback();
            }
        }, duration);
    }

    /**
     * Hiển thị flash message mới (Dùng cho form AJAX)
     * Hỗ trợ linh hoạt cả show(message, type) và show(type, message)
     */
    function show(arg1, arg2 = 'success', duration = 5000) {
        let message = arg1 || '';
        let type = arg2 || 'success';
        const knownTypes = ['success', 'error', 'info', 'warning', 'danger'];

        // Tự động nhận diện nếu gọi theo kiểu show('success', 'Nội dung thông báo')
        if (knownTypes.includes(arg1) && typeof arg2 === 'string' && !knownTypes.includes(arg2)) {
            type = arg1 === 'danger' ? 'error' : arg1;
            message = arg2;
        } else if (type === 'danger') {
            type = 'error';
        }

        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        const alert = document.createElement('div');
        const alertClass = (type === 'error' || type === 'danger') ? 'alert-danger alert-error' : `alert-${type}`;
        alert.className = `alert ${alertClass}`;
        alert.setAttribute('data-auto-dismiss', duration.toString());
        
        const icon = document.createElement('span');
        icon.className = 'alert-icon';
        const icons = {
            success: '✅',
            error: '❌',
            info: 'ℹ️',
            warning: '⚠️'
        };
        icon.textContent = icons[type] || '📢';
        alert.appendChild(icon);
        
        const text = document.createTextNode(' ' + message);
        alert.appendChild(text);
        
        // Chèn vào cuối container
        container.appendChild(alert);
        setupAlert(alert);

        return alert;
    }

    // Public API
    return {
        init: init,
        show: show,
        fadeOut: fadeOut
    };

})();

// Global browser window bindings
if (typeof window !== 'undefined') {
    window.FlashModule = FlashModule;
    window.showFlash = function(type, message, duration) {
        return FlashModule.show(message, type, duration);
    };
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FlashModule;
}
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
        // Tìm tất cả các alert hiện có (do PHP render ra ở header)
        const alerts = document.querySelectorAll('.alert');
        
        // Tạo container chứa Toast nếu chưa có
        let container = document.getElementById('toast-container');
        if (!container && alerts.length > 0) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
            
            // Di chuyển các alert cũ cắm rễ trong HTML vào container nổi
            alerts.forEach(function(alert) {
                container.appendChild(alert);
            });
        }

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
     */
    function show(message, type = 'success', duration = 5000) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
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

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FlashModule;
}
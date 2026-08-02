// public/js/modules/flash.js
'use strict';

/**
 * Module xử lý flash messages
 */
const FlashModule = (function() {
    'use strict';

    /**
     * Khởi tạo flash messages
     */
    function init() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach(function(alert) {
            // Tạo nút đóng
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

            // Tự động ẩn sau 5 giây
            const autoDismiss = alert.getAttribute('data-auto-dismiss');
            if (autoDismiss) {
                setTimeout(function() {
                    if (alert.parentNode) {
                        fadeOut(alert, 500, function() {
                            alert.remove();
                        });
                    }
                }, parseInt(autoDismiss));
            }
        });
    }

    /**
     * Hiệu ứng fade out
     */
    function fadeOut(element, duration = 300, callback) {
        const start = performance.now();
        const startOpacity = parseFloat(getComputedStyle(element).opacity) || 1;
        
        function animate(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            element.style.opacity = startOpacity * (1 - progress);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.style.display = 'none';
                if (typeof callback === 'function') {
                    callback();
                }
            }
        }
        
        requestAnimationFrame(animate);
    }

    /**
     * Hiển thị flash message mới
     */
    function show(message, type = 'success', duration = 5000) {
        const container = document.querySelector('.container');
        if (!container) return;

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
        
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'alert-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Đóng thông báo');
        alert.appendChild(closeBtn);
        
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';

        // Chèn vào đầu container
        container.insertBefore(alert, container.firstChild);

        // Fade in
        requestAnimationFrame(() => {
            alert.style.opacity = '1';
        });

        // Xử lý nút đóng
        closeBtn.addEventListener('click', function() {
            fadeOut(alert, 500, () => {
                alert.remove();
            });
        });

        // Tự động ẩn
        setTimeout(() => {
            if (alert.parentNode) {
                fadeOut(alert, 500, () => {
                    alert.remove();
                });
            }
        }, duration);

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
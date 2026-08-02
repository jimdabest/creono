// public/js/modules/utils.js
'use strict';

/**
 * Hàm tiện ích cho dự án Creono
 * Không sử dụng bất kỳ thư viện nào
 */
const Utils = {
    /**
     * Kiểm tra xem element có tồn tại không
     */
    exists: function(selector) {
        return document.querySelector(selector) !== null;
    },

    /**
     * Lấy element an toàn, trả về null nếu không tìm thấy
     */
    getElement: function(selector, context = document) {
        return context.querySelector(selector);
    },

    /**
     * Lấy tất cả elements an toàn
     */
    getElements: function(selector, context = document) {
        return context.querySelectorAll(selector);
    },

    /**
     * Thêm class cho element
     */
    addClass: function(element, className) {
        if (element && element.classList) {
            element.classList.add(className);
        }
    },

    /**
     * Xóa class của element
     */
    removeClass: function(element, className) {
        if (element && element.classList) {
            element.classList.remove(className);
        }
    },

    /**
     * Toggle class của element
     */
    toggleClass: function(element, className) {
        if (element && element.classList) {
            element.classList.toggle(className);
        }
    },

    /**
     * Kiểm tra element có class không
     */
    hasClass: function(element, className) {
        return element && element.classList && element.classList.contains(className);
    },

    /**
     * Thêm sự kiện an toàn
     */
    on: function(element, event, callback, useCapture = false) {
        if (element && element.addEventListener) {
            element.addEventListener(event, callback, useCapture);
        }
    },

    /**
     * Gửi request bằng Fetch API
     */
    fetchJSON: async function(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    },

    /**
     * Validate email
     */
    isValidEmail: function(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    /**
     * Escape HTML để tránh XSS
     */
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Debounce function để tối ưu performance
     */
    debounce: function(func, wait = 250) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    /**
     * Throttle function
     */
    throttle: function(func, limit = 250) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// Export để sử dụng ở các module khác
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Utils;
}
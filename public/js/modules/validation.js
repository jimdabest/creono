// public/js/modules/validation.js
'use strict';

/**
 * Module xử lý validation form
 */
const ValidationModule = (function() {
    'use strict';

    /**
     * Các rules validation
     */
    const rules = {
        required: function(value) {
            return value !== null && value !== undefined && value.trim() !== '';
        },
        email: function(value) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        },
        minLength: function(value, length) {
            return value.length >= length;
        },
        maxLength: function(value, length) {
            return value.length <= length;
        },
        confirm: function(value, compareValue) {
            return value === compareValue;
        }
    };

    /**
     * Khởi tạo validation cho tất cả form
     */
    function init() {
        const forms = document.querySelectorAll('form:not([data-no-validation])');
        
        forms.forEach(function(form) {
            // Thêm class để đánh dấu đã khởi tạo
            form.setAttribute('data-validated', 'true');
            
            // Xử lý submit
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                }
            });

            // Xử lý real-time validation (khi nhập)
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            inputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    validateField(this);
                });
                input.addEventListener('change', function() {
                    validateField(this);
                });
            });
        });
    }

    /**
     * Validate toàn bộ form
     */
    function validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(function(input) {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        // Tìm và xóa các message lỗi cũ
        const errorMessages = form.querySelectorAll('.error-text');
        if (isValid) {
            errorMessages.forEach(function(msg) {
                msg.remove();
            });
        }

        // Scroll đến field lỗi đầu tiên
        if (!isValid) {
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                firstError.focus();
            }
        }

        return isValid;
    }

    /**
     * Validate một field cụ thể
     */
    function validateField(input) {
        const form = input.closest('form');
        if (!form) return true;

        // Xóa error cũ
        removeError(input);

        const value = input.value;
        let isValid = true;
        let errorMessage = '';

        // Kiểm tra required
        if (input.hasAttribute('required')) {
            if (!rules.required(value)) {
                isValid = false;
                errorMessage = getErrorMessage(input, 'required') || 'Trường này là bắt buộc';
            }
        }

        // Kiểm tra type email
        if (isValid && input.type === 'email' && value) {
            if (!rules.email(value)) {
                isValid = false;
                errorMessage = getErrorMessage(input, 'email') || 'Vui lòng nhập đúng định dạng email';
            }
        }

        // Kiểm tra minlength
        if (isValid && input.hasAttribute('minlength')) {
            const minLength = parseInt(input.getAttribute('minlength'));
            if (value && value.length < minLength) {
                isValid = false;
                errorMessage = getErrorMessage(input, 'minlength') || `Tối thiểu ${minLength} ký tự`;
            }
        }

        // Kiểm tra maxlength
        if (isValid && input.hasAttribute('maxlength')) {
            const maxLength = parseInt(input.getAttribute('maxlength'));
            if (value && value.length > maxLength) {
                isValid = false;
                errorMessage = getErrorMessage(input, 'maxlength') || `Tối đa ${maxLength} ký tự`;
            }
        }

        // Kiểm tra confirm password
        if (isValid && input.hasAttribute('data-confirm')) {
            const targetId = input.getAttribute('data-confirm');
            const targetInput = document.getElementById(targetId) || 
                               document.querySelector(`[name="${targetId}"]`);
            if (targetInput && value !== targetInput.value) {
                isValid = false;
                errorMessage = getErrorMessage(input, 'confirm') || 'Mật khẩu không khớp';
            }
        }

        // Hiển thị error nếu không hợp lệ
        if (!isValid) {
            showError(input, errorMessage);
        }

        return isValid;
    }

    /**
     * Lấy message lỗi từ data attribute
     */
    function getErrorMessage(input, rule) {
        const dataKey = `error-${rule}`;
        return input.getAttribute(`data-${dataKey}`) || null;
    }

    /**
     * Hiển thị lỗi
     */
    function showError(input, message) {
        input.classList.add('is-invalid');
        
        // Tìm hoặc tạo error message
        let errorSpan = input.parentElement.querySelector('.error-text');
        if (!errorSpan) {
            errorSpan = document.createElement('span');
            errorSpan.className = 'error-text';
            input.parentElement.appendChild(errorSpan);
        }
        errorSpan.textContent = message;
    }

    /**
     * Xóa lỗi
     */
    function removeError(input) {
        input.classList.remove('is-invalid');
        const errorSpan = input.parentElement.querySelector('.error-text');
        if (errorSpan) {
            errorSpan.remove();
        }
    }

    /**
     * Thêm rule validation mới
     */
    function addRule(name, validator) {
        if (typeof validator === 'function') {
            rules[name] = validator;
        }
    }

    // Public API
    return {
        init: init,
        validateForm: validateForm,
        validateField: validateField,
        addRule: addRule
    };

})();

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ValidationModule;
}
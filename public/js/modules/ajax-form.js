// public/js/modules/ajax-form.js
'use strict';

/**
 * Module xử lý form bằng AJAX
 * Thay thế cho submit truyền thống, tích hợp FlashModule và Loading
 */
const AjaxFormModule = (function() {
    'use strict';

    // Cấu hình
    const CONFIG = {
        MIN_LOADING_TIME: 800,
        REDIRECT_DELAY: 1500,
    };

    /**
     * Khởi tạo xử lý form bằng AJAX
     */
    function init() {
        const forms = document.querySelectorAll('form[data-ajax="true"]');
        
        forms.forEach(function(form) {
            if (form.getAttribute('data-ajax') === 'false' || form.dataset.ajax === 'false') {
                return;
            }
            if (form.dataset.ajaxInitialized === 'true') {
                return;
            }
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                handleSubmit(this);
            });
            
            form.dataset.ajaxInitialized = 'true';
        });
    }
    
    /**
     * Xử lý submit form bằng AJAX
     */
    function handleSubmit(form) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : 'Submit';
        
        if (submitBtn) {
            submitBtn.dataset.originalText = originalText;
        }
        
        clearErrors(form);
        
        if (typeof window.showLoading === 'function') {
            window.showLoading(true);
        }
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang xử lý...';
        }
        
        const formData = new FormData(form);
        const startTime = Date.now();

        const actionUrl = form.getAttribute('action') || window.location.href;
        const methodType = form.getAttribute('method') || 'POST';

        fetch(actionUrl, {
            method: methodType,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned HTML instead of JSON');
            }
            return response.json();
        })
        .then(data => {
            const elapsed = Date.now() - startTime;
            const remaining = CONFIG.MIN_LOADING_TIME - elapsed;
            
            if (remaining > 0) {
                setTimeout(() => {
                    handleResponse(form, submitBtn, data);
                }, remaining);
            } else {
                handleResponse(form, submitBtn, data);
            }
        })
        .catch(function(error) {
            const elapsed = Date.now() - startTime;
            const remaining = CONFIG.MIN_LOADING_TIME - elapsed;
            
            if (remaining > 0) {
                setTimeout(() => {
                    handleError(form, submitBtn, error);
                }, remaining);
            } else {
                handleError(form, submitBtn, error);
            }
        });
    }
    
    /**
     * Xử lý response thành công
     */
    function handleResponse(form, submitBtn, data) {
        if (typeof window.showLoading === 'function') {
            window.showLoading(false);
        }
        
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
        }
        
        if (data.success) {
            if (typeof FlashModule !== 'undefined') {
                FlashModule.show(data.message || 'Thành công!', 'success');
            }
            
            if (data.redirect) {
                setTimeout(function() {
                    window.location.href = data.redirect;
                }, CONFIG.REDIRECT_DELAY);
            }
            
            const event = new CustomEvent('ajaxFormSuccess', {
                detail: { form: form, data: data }
            });
            document.dispatchEvent(event);
            
        } else {
            // Hiển thị thông báo lỗi chung
            if (typeof FlashModule !== 'undefined') {
                FlashModule.show(data.message || 'Có lỗi xảy ra', 'error');
            }
            
            // Hiển thị lỗi cho từng field
            if (data.errors && typeof data.errors === 'object') {
                showErrors(form, data.errors);
            }
            
            const event = new CustomEvent('ajaxFormError', {
                detail: { form: form, data: data }
            });
            document.dispatchEvent(event);
        }
    }
    
    /**
     * Xử lý lỗi
     */
    function handleError(form, submitBtn, error) {
        if (typeof window.showLoading === 'function') {
            window.showLoading(false);
        }
        
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
        }
        
        if (typeof FlashModule !== 'undefined') {
            FlashModule.show('Lỗi kết nối server. Vui lòng thử lại!', 'error');
        }
        
        console.error('AJAX Error:', error);
        
        const event = new CustomEvent('ajaxFormError', {
            detail: { form: form, error: error }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Clear old errors
     */
    function clearErrors(form) {
        // Clear error text spans - tìm theo ID hoặc class
        const errorSpans = form.querySelectorAll('.error-text');
        errorSpans.forEach(function(span) {
            span.textContent = '';
            span.style.display = 'none';
        });
        
        // Remove invalid class
        const invalidInputs = form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(function(input) {
            input.classList.remove('is-invalid');
        });
    }
    
    /**
     * Show field errors - CẢI THIỆN HIỂN THỊ LỖI
     */
    function showErrors(form, errors) {
        let hasError = false;
        
        Object.keys(errors).forEach(function(key) {
            const errorMessage = errors[key];
            if (!errorMessage) return;
            
            hasError = true;
            
            // Tìm error span theo ID trước
            let errorSpan = document.getElementById(key);
            
            // Nếu không tìm thấy theo ID, tìm theo data-field
            if (!errorSpan) {
                errorSpan = form.querySelector(`.error-text[data-field="${key}"]`);
            }
            
            // Nếu vẫn không tìm thấy, tìm theo class name (fallback)
            if (!errorSpan) {
                errorSpan = form.querySelector(`.${key}`);
            }
            
            // Nếu vẫn không tìm thấy, tạo mới error span
            if (!errorSpan) {
                const fieldName = key.replace('_err', '');
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (input && input.parentElement) {
                    errorSpan = document.createElement('span');
                    errorSpan.className = 'error-text';
                    errorSpan.id = key;
                    input.parentElement.appendChild(errorSpan);
                }
            }
            
            if (errorSpan) {
                errorSpan.textContent = errorMessage;
                errorSpan.style.display = 'block';
            }
            
            // Tìm input tương ứng và thêm class invalid
            const fieldName = key.replace('_err', '');
            const input = form.querySelector(`[name="${fieldName}"]`);
            if (input) {
                input.classList.add('is-invalid');
                // Thêm attribute aria-invalid
                input.setAttribute('aria-invalid', 'true');
            }
        });
        
        // Scroll đến lỗi đầu tiên
        if (hasError) {
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                firstError.focus();
            }
        }
    }
    
    /**
     * Reset form về trạng thái ban đầu
     */
    function resetForm(form) {
        form.reset();
        clearErrors(form);
        
        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
        }
    }
    
    // Public API
    return {
        init: init,
        handleSubmit: handleSubmit,
        resetForm: resetForm,
        clearErrors: clearErrors,
        CONFIG: CONFIG
    };
})();

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AjaxFormModule;
}
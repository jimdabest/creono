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
        MIN_LOADING_TIME: 800, // Thời gian tối thiểu hiển thị loading (ms)
        REDIRECT_DELAY: 1500,  // Thời gian chờ trước khi redirect (ms)
    };

    /**
     * Khởi tạo xử lý form bằng AJAX
     */
    function init() {
        const forms = document.querySelectorAll('form[data-ajax]');
        
        forms.forEach(function(form) {
            // Không init lại nếu đã có
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
        
        // Lưu lại text gốc
        if (submitBtn) {
            submitBtn.dataset.originalText = originalText;
        }
        
        // Clear old errors
        clearErrors(form);
        
        // Show loading
        if (typeof window.showLoading === 'function') {
            window.showLoading(true);
        }
        
        // Disable button và thay đổi text
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang xử lý...';
        }
        
        const formData = new FormData(form);
        const startTime = Date.now(); // Đánh dấu thời gian bắt đầu
        
        fetch(form.action, {
            method: form.method || 'POST',
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
            // Tính thời gian đã trôi qua
            const elapsed = Date.now() - startTime;
            const remaining = CONFIG.MIN_LOADING_TIME - elapsed;
            
            // Đảm bảo loading hiển thị ít nhất MIN_LOADING_TIME ms
            if (remaining > 0) {
                setTimeout(() => {
                    handleResponse(form, submitBtn, data);
                }, remaining);
            } else {
                handleResponse(form, submitBtn, data);
            }
        })
        .catch(function(error) {
            // Tính thời gian đã trôi qua
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
        // Hide loading
        if (typeof window.showLoading === 'function') {
            window.showLoading(false);
        }
        
        // Restore button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
        }
        
        if (data.success) {
            // Show success message
            if (typeof FlashModule !== 'undefined') {
                FlashModule.show(data.message || 'Thành công!', 'success');
            }
            
            // Redirect if needed
            if (data.redirect) {
                setTimeout(function() {
                    window.location.href = data.redirect;
                }, CONFIG.REDIRECT_DELAY);
            }
            
            // Trigger custom event cho các module khác
            const event = new CustomEvent('ajaxFormSuccess', {
                detail: { form: form, data: data }
            });
            document.dispatchEvent(event);
            
        } else {
            // Show error message
            if (typeof FlashModule !== 'undefined') {
                FlashModule.show(data.message || 'Có lỗi xảy ra', 'error');
            }
            
            // Show field errors
            if (data.errors) {
                showErrors(form, data.errors);
            }
            
            // Trigger custom event cho các module khác
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
        // Hide loading
        if (typeof window.showLoading === 'function') {
            window.showLoading(false);
        }
        
        // Restore button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
        }
        
        // Show error
        if (typeof FlashModule !== 'undefined') {
            FlashModule.show('Lỗi kết nối server. Vui lòng thử lại!', 'error');
        }
        
        console.error('AJAX Error:', error);
        
        // Trigger custom event
        const event = new CustomEvent('ajaxFormError', {
            detail: { form: form, error: error }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Clear old errors
     */
    function clearErrors(form) {
        // Clear error text spans
        const errorSpans = form.querySelectorAll('.error-text');
        errorSpans.forEach(function(span) {
            span.textContent = '';
        });
        
        // Remove invalid class
        const invalidInputs = form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(function(input) {
            input.classList.remove('is-invalid');
        });
    }
    
    /**
     * Show field errors
     */
    function showErrors(form, errors) {
        Object.keys(errors).forEach(function(key) {
            // Tìm error span theo id (field_err)
            const errorSpan = document.getElementById(key);
            if (errorSpan) {
                errorSpan.textContent = errors[key];
            }
            
            // Tìm input tương ứng và thêm class invalid
            const fieldName = key.replace('_err', '');
            const input = form.querySelector(`[name="${fieldName}"]`);
            if (input) {
                input.classList.add('is-invalid');
            }
        });
        
        // Scroll to first error
        const firstError = form.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            firstError.focus();
        }
    }
    
    /**
     * Reset form về trạng thái ban đầu
     */
    function resetForm(form) {
        form.reset();
        clearErrors(form);
        
        // Reset button
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
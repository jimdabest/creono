// public/js/modules/report.js
'use strict';

/**
 * Module xử lý logic đặc thù cho trang báo cáo vi phạm
 * Không xử lý submit form (đã có AjaxFormModule)
 */
const ReportModule = (function () {
    'use strict';

    /**
     * Khởi tạo các tính năng của trang báo cáo
     */
    function init() {
        // Các logic UI đặc thù cho form báo cáo (nếu có)
        // Ví dụ: tự động focus vào select đầu tiên, hiển thị thông báo hữu ích, v.v.

        const reasonSelect = document.getElementById('reason');
        if (reasonSelect) {
            // Tự động focus vào select khi trang load
            reasonSelect.focus();
        }

        // Xử lý sự kiện change cho lý do báo cáo (có thể mở rộng sau)
        const detailsTextarea = document.getElementById('details');
        if (reasonSelect && detailsTextarea) {
            reasonSelect.addEventListener('change', function () {
                // Nếu chọn "Khác", gợi ý nhập chi tiết
                if (this.value === 'Khác (vui lòng mô tả chi tiết)') {
                    detailsTextarea.placeholder = 'Vui lòng mô tả chi tiết hành vi vi phạm...';
                } else {
                    detailsTextarea.placeholder = 'Vui lòng mô tả cụ thể hành vi vi phạm, cung cấp bằng chứng nếu có...';
                }
            });
        }

        // Lắng nghe sự kiện thành công từ AjaxFormModule để có thể hiển thị thông báo bổ sung
        document.addEventListener('ajaxFormSuccess', function (e) {
            const form = e.detail.form;
            if (form && form.classList.contains('report-form')) {
                // Form báo cáo đã được gửi thành công
                // Có thể thực hiện thêm hành động UI nếu cần
                // Ví dụ: disable form, hiển thị overlay, v.v.
                const submitBtn = form.querySelector('.btn-submit-report');
                if (submitBtn) {
                    submitBtn.textContent = '✓ Đã gửi';
                    submitBtn.disabled = true;
                    submitBtn.style.background = 'var(--apple-green)';
                }
            }
        });

        // Lắng nghe sự kiện lỗi từ AjaxFormModule
        document.addEventListener('ajaxFormError', function (e) {
            const form = e.detail.form;
            if (form && form.classList.contains('report-form')) {
                // Nếu có lỗi validation từ server, enable lại nút submit
                const submitBtn = form.querySelector('.btn-submit-report');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Gửi báo cáo';
                }
            }
        });
    }

    // Public API
    return {
        init: init
    };

})();

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ReportModule;
}
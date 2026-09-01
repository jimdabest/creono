// public/js/modules/admin-products.js
'use strict';

/**
 * AdminProductsModule - Module xử lý AJAX cho Quản lý Sản phẩm (UC41)
 * Chức năng: Tìm kiếm, lọc, duyệt/từ chối (AJAX), xóa sản phẩm (AJAX)
 * Pattern: Module Pattern (IIFE)
 */
const AdminProductsModule = (function () {
    'use strict';

    // === CẤU HÌNH ===
    var CONFIG = {
        URLROOT: window.CREONO ? window.CREONO.URLROOT : '',
        TOAST_DURATION: 3500
    };

    // === CACHE DOM ===
    var elements = {};

    // =========================================================================
    // KHỞI TẠO MODULE
    // =========================================================================

    function init() {
        // Chỉ chạy trên trang quản lý sản phẩm
        if (!document.getElementById('ap-products-table')) {
            return;
        }

        cacheElements();
        bindEvents();
    }

    /**
     * Cache các DOM element thường dùng
     */
    function cacheElements() {
        elements = {
            tbody: document.getElementById('ap-products-tbody'),
            searchInput: document.getElementById('ap-search-input'),
            filterStatus: document.getElementById('ap-filter-status'),
            visibleCount: document.getElementById('ap-visible-count'),
            csrfToken: document.getElementById('ap-csrf-token'),
            // Reject modal
            rejectModal: document.getElementById('ap-reject-modal'),
            rejectProductId: document.getElementById('ap-reject-product-id'),
            rejectProductName: document.getElementById('ap-reject-product-name'),
            rejectNote: document.getElementById('ap-reject-note'),
            rejectModalClose: document.getElementById('ap-reject-modal-close'),
            rejectCancelBtn: document.getElementById('ap-reject-cancel-btn'),
            rejectConfirmBtn: document.getElementById('ap-reject-confirm-btn'),
            // Delete modal
            deleteModal: document.getElementById('ap-delete-modal'),
            deleteProductId: document.getElementById('ap-delete-product-id'),
            deleteProductName: document.getElementById('ap-delete-product-name'),
            deleteModalClose: document.getElementById('ap-delete-modal-close'),
            deleteCancelBtn: document.getElementById('ap-delete-cancel-btn'),
            deleteConfirmBtn: document.getElementById('ap-delete-confirm-btn')
        };
    }

    /**
     * Gắn tất cả event listeners
     */
    function bindEvents() {
        // Tìm kiếm realtime
        if (elements.searchInput) {
            elements.searchInput.addEventListener('input', handleFilter);
        }

        // Lọc theo trạng thái
        if (elements.filterStatus) {
            elements.filterStatus.addEventListener('change', handleFilter);
        }

        // Event Delegation trên tbody cho các nút hành động
        if (elements.tbody) {
            elements.tbody.addEventListener('click', function (e) {
                var approveBtn = e.target.closest('.ap-btn-approve');
                if (approveBtn) {
                    handleApprove(approveBtn);
                    return;
                }

                var rejectBtn = e.target.closest('.ap-btn-reject');
                if (rejectBtn) {
                    handleRejectClick(rejectBtn);
                    return;
                }

                var deleteBtn = e.target.closest('.ap-btn-delete');
                if (deleteBtn) {
                    handleDeleteClick(deleteBtn);
                    return;
                }
            });
        }

        // Reject modal events
        if (elements.rejectModalClose) {
            elements.rejectModalClose.addEventListener('click', closeRejectModal);
        }
        if (elements.rejectCancelBtn) {
            elements.rejectCancelBtn.addEventListener('click', closeRejectModal);
        }
        if (elements.rejectConfirmBtn) {
            elements.rejectConfirmBtn.addEventListener('click', handleConfirmReject);
        }
        if (elements.rejectModal) {
            elements.rejectModal.addEventListener('click', function (e) {
                if (e.target === elements.rejectModal) closeRejectModal();
            });
        }

        // Delete modal events
        if (elements.deleteModalClose) {
            elements.deleteModalClose.addEventListener('click', closeDeleteModal);
        }
        if (elements.deleteCancelBtn) {
            elements.deleteCancelBtn.addEventListener('click', closeDeleteModal);
        }
        if (elements.deleteConfirmBtn) {
            elements.deleteConfirmBtn.addEventListener('click', handleConfirmDelete);
        }
        if (elements.deleteModal) {
            elements.deleteModal.addEventListener('click', function (e) {
                if (e.target === elements.deleteModal) closeDeleteModal();
            });
        }

        // Đóng modal bằng phím Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (elements.rejectModal && elements.rejectModal.classList.contains('ap-modal--visible')) {
                    closeRejectModal();
                }
                if (elements.deleteModal && elements.deleteModal.classList.contains('ap-modal--visible')) {
                    closeDeleteModal();
                }
            }
        });
    }

    // =========================================================================
    // TÌM KIẾM & LỌC CLIENT-SIDE
    // =========================================================================

    /**
     * Lọc bảng sản phẩm theo keyword và status
     */
    function handleFilter() {
        var keyword = elements.searchInput ? elements.searchInput.value.toLowerCase().trim() : '';
        var statusFilter = elements.filterStatus ? elements.filterStatus.value : '';

        var rows = elements.tbody.querySelectorAll('tr[id^="ap-row-"]');
        var visibleCount = 0;

        rows.forEach(function (row) {
            var title = row.getAttribute('data-title') || '';
            var store = row.getAttribute('data-store') || '';
            var status = row.getAttribute('data-status') || '';

            // Kiểm tra keyword (tiêu đề hoặc cửa hàng)
            var matchKeyword = !keyword || title.indexOf(keyword) > -1 || store.indexOf(keyword) > -1;

            // Kiểm tra trạng thái
            var matchStatus = !statusFilter || status === statusFilter;

            if (matchKeyword && matchStatus) {
                row.classList.remove('ap-row-hidden');
                visibleCount++;
            } else {
                row.classList.add('ap-row-hidden');
            }
        });

        if (elements.visibleCount) {
            elements.visibleCount.textContent = visibleCount;
        }
    }

    // =========================================================================
    // DUYỆT SẢN PHẨM (AJAX - Approve)
    // =========================================================================

    /**
     * Xử lý phê duyệt sản phẩm qua AJAX
     */
    function handleApprove(btn) {
        var productId = btn.getAttribute('data-product-id');
        var productTitle = btn.getAttribute('data-product-title');

        if (!confirm('Bạn có chắc chắn muốn PHÊ DUYỆT sản phẩm "' + productTitle + '"?')) {
            return;
        }

        btn.classList.add('ap-btn-loading');

        var formData = new FormData();
        formData.append('csrf_token', getCsrfToken());

        fetch(CONFIG.URLROOT + '/adminProductController/approve/' + productId, {
            method: 'POST',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                btn.classList.remove('ap-btn-loading');

                if (data.success) {
                    updateStatusUI(productId, 2);
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(function () {
                btn.classList.remove('ap-btn-loading');
                showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
            });
    }

    // =========================================================================
    // TỪ CHỐI SẢN PHẨM (AJAX - Reject + Modal)
    // =========================================================================

    /**
     * Hiển thị modal từ chối (nhập lý do)
     */
    function handleRejectClick(btn) {
        var productId = btn.getAttribute('data-product-id');
        var productTitle = btn.getAttribute('data-product-title');

        elements.rejectProductId.value = productId;
        elements.rejectProductName.textContent = '"' + productTitle + '"';
        elements.rejectNote.value = '';

        elements.rejectModal.classList.add('ap-modal--visible');
        document.body.style.overflow = 'hidden';

        // Focus vào textarea
        setTimeout(function () {
            elements.rejectNote.focus();
        }, 350);
    }

    function closeRejectModal() {
        elements.rejectModal.classList.remove('ap-modal--visible');
        document.body.style.overflow = '';
    }

    /**
     * Xác nhận từ chối → Gửi AJAX POST
     */
    function handleConfirmReject() {
        var productId = elements.rejectProductId.value;
        var note = elements.rejectNote.value.trim();

        if (!productId) {
            showToast('ID sản phẩm không hợp lệ', 'error');
            return;
        }

        if (!note) {
            showToast('Vui lòng nhập lý do từ chối', 'warning');
            elements.rejectNote.focus();
            return;
        }

        // Loading state
        elements.rejectConfirmBtn.disabled = true;
        elements.rejectConfirmBtn.textContent = 'Đang xử lý...';

        var formData = new FormData();
        formData.append('csrf_token', getCsrfToken());
        formData.append('note', note);

        fetch(CONFIG.URLROOT + '/adminProductController/reject/' + productId, {
            method: 'POST',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                elements.rejectConfirmBtn.disabled = false;
                elements.rejectConfirmBtn.textContent = 'Xác nhận từ chối';

                if (data.success) {
                    updateStatusUI(productId, 3);
                    closeRejectModal();
                    showToast(data.message, 'warning');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(function () {
                elements.rejectConfirmBtn.disabled = false;
                elements.rejectConfirmBtn.textContent = 'Xác nhận từ chối';
                showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
            });
    }

    // =========================================================================
    // XÓA SẢN PHẨM (AJAX + Modal)
    // =========================================================================

    /**
     * Hiển thị modal xác nhận xóa
     */
    function handleDeleteClick(btn) {
        var productId = btn.getAttribute('data-product-id');
        var productTitle = btn.getAttribute('data-product-title');

        elements.deleteProductId.value = productId;
        elements.deleteProductName.textContent = '"' + productTitle + '"';

        elements.deleteModal.classList.add('ap-modal--visible');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        elements.deleteModal.classList.remove('ap-modal--visible');
        document.body.style.overflow = '';
    }

    /**
     * Xác nhận xóa → Gửi AJAX POST
     */
    function handleConfirmDelete() {
        var productId = elements.deleteProductId.value;

        if (!productId) {
            showToast('ID sản phẩm không hợp lệ', 'error');
            return;
        }

        elements.deleteConfirmBtn.disabled = true;
        elements.deleteConfirmBtn.textContent = 'Đang xóa...';

        var formData = new FormData();
        formData.append('csrf_token', getCsrfToken());

        fetch(CONFIG.URLROOT + '/adminProductController/delete/' + productId, {
            method: 'POST',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                elements.deleteConfirmBtn.disabled = false;
                elements.deleteConfirmBtn.textContent = 'Xóa sản phẩm';

                if (data.success) {
                    removeRowAnimated(productId);
                    closeDeleteModal();
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(function () {
                elements.deleteConfirmBtn.disabled = false;
                elements.deleteConfirmBtn.textContent = 'Xóa sản phẩm';
                showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
            });
    }

    // =========================================================================
    // UI UPDATE HELPERS
    // =========================================================================

    /**
     * Cập nhật badge trạng thái + ẩn/hiện nút sau khi approve/reject
     */
    function updateStatusUI(productId, newStatus) {
        var row = document.getElementById('ap-row-' + productId);
        var statusBadge = document.getElementById('ap-status-' + productId);

        if (!row || !statusBadge) return;

        // Cập nhật data attribute cho filter
        row.setAttribute('data-status', newStatus);

        // Cập nhật badge
        var statusMap = {
            1: { text: 'Chờ duyệt', className: 'ap-status-badge ap-status--pending' },
            2: { text: 'Đã duyệt', className: 'ap-status-badge ap-status--approved' },
            3: { text: 'Từ chối', className: 'ap-status-badge ap-status--rejected' }
        };

        var info = statusMap[newStatus];
        if (info) {
            statusBadge.className = info.className;
            statusBadge.textContent = info.text;
        }

        // Cập nhật nút hành động:
        // Ẩn nút Approve nếu đã Approved, hiện nút Reject
        // Ẩn nút Reject nếu đã Rejected, hiện nút Approve
        var actionGroup = row.querySelector('.ap-action-group');
        if (!actionGroup) return;

        var approveBtn = actionGroup.querySelector('.ap-btn-approve');
        var rejectBtn = actionGroup.querySelector('.ap-btn-reject');
        var productTitle = row.getAttribute('data-title') || '';

        if (newStatus === 2) {
            // Đã duyệt → Ẩn Approve, hiện Reject (nếu chưa có)
            if (approveBtn) approveBtn.remove();
            if (!rejectBtn) {
                rejectBtn = createActionButton('reject', productId, productTitle);
                var deleteBtn = actionGroup.querySelector('.ap-btn-delete');
                if (deleteBtn) {
                    actionGroup.insertBefore(rejectBtn, deleteBtn);
                } else {
                    actionGroup.appendChild(rejectBtn);
                }
            }
        } else if (newStatus === 3) {
            // Từ chối → Ẩn Reject, hiện Approve (nếu chưa có)
            if (rejectBtn) rejectBtn.remove();
            if (!approveBtn) {
                approveBtn = createActionButton('approve', productId, productTitle);
                var deleteBtn2 = actionGroup.querySelector('.ap-btn-delete');
                if (deleteBtn2) {
                    actionGroup.insertBefore(approveBtn, deleteBtn2);
                } else {
                    actionGroup.appendChild(approveBtn);
                }
            }
        }
    }

    /**
     * Tạo nút hành động mới (approve/reject) sau khi toggle
     */
    function createActionButton(type, productId, productTitle) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('data-product-id', productId);
        btn.setAttribute('data-product-title', productTitle);

        if (type === 'approve') {
            btn.className = 'btn-action ap-btn-approve';
            btn.title = 'Phê duyệt';
            btn.innerHTML =
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                '<polyline points="20 6 9 17 4 12"></polyline>' +
                '</svg> Duyệt';
        } else {
            btn.className = 'btn-action ap-btn-reject';
            btn.title = 'Từ chối';
            btn.innerHTML =
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                '<line x1="18" y1="6" x2="6" y2="18"></line>' +
                '<line x1="6" y1="6" x2="18" y2="18"></line>' +
                '</svg> Từ chối';
        }

        return btn;
    }

    /**
     * Xóa dòng khỏi bảng với animation
     */
    function removeRowAnimated(productId) {
        var row = document.getElementById('ap-row-' + productId);
        if (!row) return;

        row.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';

        setTimeout(function () {
            row.remove();
            updateVisibleCount();
        }, 400);
    }

    // =========================================================================
    // HELPER FUNCTIONS
    // =========================================================================

    function getCsrfToken() {
        return elements.csrfToken ? elements.csrfToken.value : '';
    }

    function updateVisibleCount() {
        if (!elements.visibleCount || !elements.tbody) return;
        var visibleRows = elements.tbody.querySelectorAll('tr[id^="ap-row-"]:not(.ap-row-hidden)');
        elements.visibleCount.textContent = visibleRows.length;
    }

    /**
     * Hiển thị toast notification
     * @param {string} message - Nội dung thông báo
     * @param {string} type - 'success', 'error', hoặc 'warning'
     */
    function showToast(message, type) {
        // Ưu tiên sử dụng FlashModule chuẩn của hệ thống nếu có
        if (typeof FlashModule !== 'undefined' && typeof FlashModule.show === 'function') {
            var flashType = (type === 'error') ? 'danger' : type;
            FlashModule.show(message, flashType, CONFIG.TOAST_DURATION);
            return;
        }

        // Fallback toast tự sinh nếu FlashModule chưa được nạp
        var existingToast = document.querySelector('.ap-toast');
        if (existingToast) existingToast.remove();

        var toast = document.createElement('div');
        toast.className = 'ap-toast ap-toast--' + type;

        var iconMap = {
            success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            error: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>'
        };

        toast.innerHTML = '<span class="ap-toast-icon">' + (iconMap[type] || iconMap.success) + '</span>' + message;

        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add('ap-toast--visible');
        });

        setTimeout(function () {
            toast.classList.remove('ap-toast--visible');
            setTimeout(function () {
                if (toast.parentNode) toast.remove();
            }, 400);
        }, CONFIG.TOAST_DURATION);
    }

    // =========================================================================
    // PUBLIC API
    // =========================================================================

    return {
        init: init
    };

})();

// Khởi chạy module khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', function () {
    AdminProductsModule.init();
});

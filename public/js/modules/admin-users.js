// public/js/modules/admin-users.js
'use strict';

/**
 * AdminUsersModule - Module xử lý AJAX cho Quản lý Người dùng (UC40)
 * Chức năng: Tìm kiếm, lọc, khóa/mở khóa, xóa user qua AJAX
 * Pattern: Module Pattern (IIFE)
 */
const AdminUsersModule = (function () {
    'use strict';

    // === CẤU HÌNH ===
    const CONFIG = {
        URLROOT: window.CREONO ? window.CREONO.URLROOT : '',
        TOAST_DURATION: 3500,
    };

    // === CACHE DOM ELEMENTS ===
    let elements = {};

    // =========================================================================
    // KHỞI TẠO MODULE
    // =========================================================================

    function init() {
        // Chỉ chạy trên trang quản lý user
        if (!document.getElementById('au-users-table')) {
            return;
        }

        cacheElements();
        bindEvents();
    }

    /**
     * Cache các DOM element thường dùng để tránh query lặp lại
     */
    function cacheElements() {
        elements = {
            table: document.getElementById('au-users-table'),
            tbody: document.getElementById('au-users-tbody'),
            searchInput: document.getElementById('au-search-input'),
            filterRole: document.getElementById('au-filter-role'),
            filterStatus: document.getElementById('au-filter-status'),
            visibleCount: document.getElementById('au-visible-count'),
            csrfToken: document.getElementById('au-csrf-token'),
            // Modal elements
            deleteModal: document.getElementById('au-delete-modal'),
            deleteUserId: document.getElementById('au-delete-user-id'),
            deleteUsername: document.getElementById('au-delete-username'),
            modalClose: document.getElementById('au-modal-close'),
            modalCancel: document.getElementById('au-modal-cancel-btn'),
            modalConfirm: document.getElementById('au-modal-confirm-btn'),
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

        // Lọc theo vai trò & trạng thái
        if (elements.filterRole) {
            elements.filterRole.addEventListener('change', handleFilter);
        }
        if (elements.filterStatus) {
            elements.filterStatus.addEventListener('change', handleFilter);
        }

        // Nút khóa/mở khóa (Event Delegation trên tbody)
        if (elements.tbody) {
            elements.tbody.addEventListener('click', function (e) {
                var btn = e.target.closest('.au-btn-toggle-lock');
                if (btn) {
                    handleToggleLock(btn);
                }

                var deleteBtn = e.target.closest('.au-btn-delete');
                if (deleteBtn) {
                    handleDeleteClick(deleteBtn);
                }
            });
        }

        // Modal events
        if (elements.modalClose) {
            elements.modalClose.addEventListener('click', closeDeleteModal);
        }
        if (elements.modalCancel) {
            elements.modalCancel.addEventListener('click', closeDeleteModal);
        }
        if (elements.modalConfirm) {
            elements.modalConfirm.addEventListener('click', handleConfirmDelete);
        }
        // Đóng modal khi click overlay
        if (elements.deleteModal) {
            elements.deleteModal.addEventListener('click', function (e) {
                if (e.target === elements.deleteModal) {
                    closeDeleteModal();
                }
            });
        }
        // Đóng modal bằng phím Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && elements.deleteModal.classList.contains('au-modal--visible')) {
                closeDeleteModal();
            }
        });
    }

    // =========================================================================
    // TÌM KIẾM & LỌC CLIENT-SIDE
    // =========================================================================

    /**
     * Lọc bảng user theo keyword, role, status
     */
    function handleFilter() {
        var keyword = elements.searchInput ? elements.searchInput.value.toLowerCase().trim() : '';
        var roleFilter = elements.filterRole ? elements.filterRole.value : '';
        var statusFilter = elements.filterStatus ? elements.filterStatus.value : '';

        var rows = elements.tbody.querySelectorAll('tr[id^="au-row-"]');
        var visibleCount = 0;

        rows.forEach(function (row) {
            var name = row.getAttribute('data-name') || '';
            var email = row.getAttribute('data-email') || '';
            var role = row.getAttribute('data-role') || '';
            var locked = row.getAttribute('data-locked') || '';

            // Kiểm tra keyword (tên hoặc email)
            var matchKeyword = !keyword || name.indexOf(keyword) > -1 || email.indexOf(keyword) > -1;

            // Kiểm tra vai trò
            var matchRole = !roleFilter || role === roleFilter;

            // Kiểm tra trạng thái
            var matchStatus = !statusFilter || locked === statusFilter;

            // Hiện/ẩn dòng
            if (matchKeyword && matchRole && matchStatus) {
                row.classList.remove('au-row-hidden');
                visibleCount++;
            } else {
                row.classList.add('au-row-hidden');
            }
        });

        // Cập nhật bộ đếm
        if (elements.visibleCount) {
            elements.visibleCount.textContent = visibleCount;
        }
    }

    // =========================================================================
    // KHÓA / MỞ KHÓA TÀI KHOẢN (AJAX)
    // =========================================================================

    /**
     * Xử lý toggle lock/unlock qua AJAX POST
     */
    function handleToggleLock(btn) {
        var userId = btn.getAttribute('data-user-id');
        var userName = btn.getAttribute('data-user-name');
        var isLocked = btn.getAttribute('data-locked') === '1';

        // Xác nhận hành động
        var actionText = isLocked ? 'mở khóa' : 'khóa';
        if (!confirm('Bạn có chắc chắn muốn ' + actionText + ' tài khoản "' + userName + '"?')) {
            return;
        }

        // Thêm trạng thái loading
        btn.classList.add('au-btn-loading');

        // Gửi AJAX POST
        var formData = new FormData();
        formData.append('csrf_token', getCsrfToken());

        fetch(CONFIG.URLROOT + '/adminUserController/toggleLock/' + userId, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                btn.classList.remove('au-btn-loading');

                if (data.success) {
                    // Cập nhật UI sau khi thành công
                    updateLockUI(userId, data.is_locked);
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(function (error) {
                btn.classList.remove('au-btn-loading');
                showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
            });
    }

    /**
     * Cập nhật giao diện sau khi toggle lock thành công
     */
    function updateLockUI(userId, isLocked) {
        var row = document.getElementById('au-row-' + userId);
        var statusBadge = document.getElementById('au-status-' + userId);
        var lockBtn = document.getElementById('au-lock-btn-' + userId);

        if (!row || !statusBadge || !lockBtn) return;

        // Cập nhật data attribute cho filter
        row.setAttribute('data-locked', isLocked ? 'locked' : 'active');

        // Cập nhật badge trạng thái
        if (isLocked) {
            statusBadge.className = 'au-status-badge au-status--locked';
            statusBadge.textContent = '🔒 Đã khóa';
        } else {
            statusBadge.className = 'au-status-badge au-status--active';
            statusBadge.textContent = '✅ Hoạt động';
        }

        // Cập nhật nút bấm
        lockBtn.setAttribute('data-locked', isLocked ? '1' : '0');
        lockBtn.className = 'btn-action au-btn-toggle-lock ' + (isLocked ? 'btn-action-unlock' : 'btn-action-lock');
        lockBtn.title = isLocked ? 'Mở khóa' : 'Khóa';

        if (isLocked) {
            lockBtn.innerHTML =
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>' +
                '<path d="M7 11V7a5 5 0 0 1 9.9-1"></path>' +
                '</svg> Mở khóa';
        } else {
            lockBtn.innerHTML =
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>' +
                '<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>' +
                '</svg> Khóa';
        }
    }

    // =========================================================================
    // XÓA TÀI KHOẢN (AJAX + Modal)
    // =========================================================================

    /**
     * Hiển thị modal xác nhận xóa
     */
    function handleDeleteClick(btn) {
        var userId = btn.getAttribute('data-user-id');
        var userName = btn.getAttribute('data-user-name');

        // Điền thông tin vào modal
        elements.deleteUserId.value = userId;
        elements.deleteUsername.textContent = '"' + userName + '"';

        // Hiển thị modal
        elements.deleteModal.classList.add('au-modal--visible');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Đóng modal xóa
     */
    function closeDeleteModal() {
        elements.deleteModal.classList.remove('au-modal--visible');
        document.body.style.overflow = '';
    }

    /**
     * Xác nhận xóa → Gửi AJAX POST
     */
    function handleConfirmDelete() {
        var userId = elements.deleteUserId.value;

        if (!userId) {
            showToast('ID người dùng không hợp lệ', 'error');
            return;
        }

        // Loading state cho nút confirm
        elements.modalConfirm.disabled = true;
        elements.modalConfirm.textContent = 'Đang xóa...';

        // Gửi AJAX POST
        var formData = new FormData();
        formData.append('csrf_token', getCsrfToken());

        fetch(CONFIG.URLROOT + '/adminUserController/delete/' + userId, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                elements.modalConfirm.disabled = false;
                elements.modalConfirm.textContent = 'Xóa vĩnh viễn';

                if (data.success) {
                    // Xóa dòng khỏi bảng với hiệu ứng fade
                    removeRowAnimated(userId);
                    closeDeleteModal();
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(function (error) {
                elements.modalConfirm.disabled = false;
                elements.modalConfirm.textContent = 'Xóa vĩnh viễn';
                showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
            });
    }

    /**
     * Xóa dòng khỏi bảng với animation
     */
    function removeRowAnimated(userId) {
        var row = document.getElementById('au-row-' + userId);
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

    /**
     * Lấy CSRF token từ hidden input
     */
    function getCsrfToken() {
        return elements.csrfToken ? elements.csrfToken.value : '';
    }

    /**
     * Cập nhật bộ đếm số dòng hiển thị
     */
    function updateVisibleCount() {
        if (!elements.visibleCount || !elements.tbody) return;
        var visibleRows = elements.tbody.querySelectorAll('tr[id^="au-row-"]:not(.au-row-hidden)');
        elements.visibleCount.textContent = visibleRows.length;
    }

    /**
     * Hiển thị toast notification
     * @param {string} message - Nội dung thông báo
     * @param {string} type - 'success' hoặc 'error'
     */
    function showToast(message, type) {
        // Ưu tiên sử dụng FlashModule chuẩn của hệ thống nếu có
        if (typeof FlashModule !== 'undefined' && typeof FlashModule.show === 'function') {
            var flashType = (type === 'error') ? 'danger' : type;
            FlashModule.show(message, flashType, CONFIG.TOAST_DURATION);
            return;
        }

        // Fallback toast tự sinh nếu FlashModule chưa được nạp
        var existingToast = document.querySelector('.au-toast');
        if (existingToast) {
            existingToast.remove();
        }

        var toast = document.createElement('div');
        toast.className = 'au-toast au-toast--' + type;

        var iconSvg = type === 'success'
            ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
            : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';

        toast.innerHTML = '<span class="au-toast-icon">' + iconSvg + '</span>' + message;

        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add('au-toast--visible');
        });

        setTimeout(function () {
            toast.classList.remove('au-toast--visible');
            setTimeout(function () {
                if (toast.parentNode) {
                    toast.remove();
                }
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
    AdminUsersModule.init();
});

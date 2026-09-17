<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Duyệt đăng ký cửa hàng</span>
            </nav>
            <h1 class="admin-title">Duyệt Đăng Ký Cửa Hàng</h1>
            <p class="admin-subtitle">Xét duyệt hồ sơ và nâng cấp quyền Người bán (Seller) cho các tài khoản đăng ký mở cửa hàng</p>
        </div>
        <div>
            <span class="badge badge-primary font-medium" id="top-pending-badge">
                Đang chờ duyệt: <strong id="pending-count-num"><?php echo count($data['stores']); ?></strong>
            </span>
        </div>
    </div>

    <!-- Bảng danh sách hồ sơ cửa hàng chờ duyệt -->
    <div class="admin-card mb-5">
        <div class="card-header flex-between">
            <h3>Danh sách hồ sơ chờ kiểm duyệt</h3>
            <span class="badge badge-warning">Status: Pending</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="pending-stores-table">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Tên cửa hàng</th>
                        <th>Người đăng ký</th>
                        <th>Số điện thoại</th>
                        <th>Mô tả</th>
                        <th>Giấy tờ đính kèm</th>
                        <th>Ngày gửi</th>
                        <th class="text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody id="stores-table-body">
                    <?php if (!empty($data['stores'])) : ?>
                        <?php foreach ($data['stores'] as $store) : ?>
                            <tr id="store-row-<?php echo $store->id; ?>">
                                <td>
                                    <?php if (!empty($store->logo_url)) : ?>
                                        <img src="<?php echo htmlspecialchars($store->logo_url); ?>" alt="Logo <?php echo htmlspecialchars($store->name); ?>" class="store-logo-thumb">
                                    <?php else : ?>
                                        <div class="store-logo-placeholder">
                                            <?php echo mb_strtoupper(mb_substr($store->name, 0, 1, 'UTF-8')); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="store-name-link"><?php echo htmlspecialchars($store->name); ?></strong>
                                    <?php if (!empty($store->slug)) : ?>
                                        <span class="code-badge"><?php echo htmlspecialchars($store->slug); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="store-applicant-name"><?php echo htmlspecialchars($store->applicant_name ?? 'Không rõ'); ?></span>
                                    <span class="store-applicant-email"><?php echo htmlspecialchars($store->applicant_email ?? ''); ?></span>
                                </td>
                                <td>
                                    <span><?php echo htmlspecialchars(!empty($store->phone) ? $store->phone : 'Chưa cung cấp'); ?></span>
                                </td>
                                <td>
                                    <div class="desc-col">
                                        <?php echo nl2br(htmlspecialchars(!empty($store->description) ? $store->description : 'Không có mô tả')); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($store->document_url)) : ?>
                                        <a href="<?php echo htmlspecialchars($store->document_url); ?>" target="_blank" rel="noopener noreferrer" class="doc-attached-link" title="Xem giấy tờ chứng minh / giấy phép">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                            </svg>
                                            Xem giấy tờ
                                        </a>
                                    <?php else : ?>
                                        <span class="doc-empty-badge">Chưa đính kèm</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="font-sm text-muted">
                                        <?php echo !empty($store->submission_date) ? date('d/m/Y H:i', strtotime($store->submission_date)) : '---'; ?>
                                    </span>
                                </td>
                                <td class="text-right store-actions-cell">
                                    <div class="store-actions">
                                        <button type="button" class="btn-action btn-action-approve" onclick="handleApprove(<?php echo $store->id; ?>, '<?php echo htmlspecialchars(addslashes($store->name)); ?>')" title="Phê duyệt mở cửa hàng">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            Phê duyệt
                                        </button>
                                        <button type="button" class="btn-action btn-action-delete" onclick="openRejectModal(<?php echo $store->id; ?>, '<?php echo htmlspecialchars(addslashes($store->name)); ?>')" title="Từ chối hồ sơ đăng ký">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                            Từ chối
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr id="empty-state-row">
                            <td colspan="8" class="table-empty-row">
                                Hiện không có hồ sơ đăng ký cửa hàng nào đang chờ duyệt.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nhập Lý Do Từ Chối -->
<div class="ap-modal-overlay" id="reject-modal-overlay">
    <div class="ap-modal">
        <div class="ap-modal-header">
            <h3>Từ chối đăng ký cửa hàng</h3>
            <button type="button" class="ap-modal-close" onclick="closeRejectModal()" aria-label="Đóng">&times;</button>
        </div>
        <div class="ap-modal-body">
            <div class="ap-modal-icon ap-modal-icon--danger">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <p>Bạn chuẩn bị từ chối hồ sơ đăng ký của cửa hàng:</p>
            <p class="modal-store-name" id="reject-modal-store-name">---</p>
            
            <textarea id="reject-reason-input" class="form-control modal-reason-textarea" placeholder="Nhập lý do từ chối cụ thể để gửi thông báo tới người đăng ký..."></textarea>
            <div class="modal-error-msg" id="reject-error-msg">Vui lòng nhập lý do từ chối hồ sơ.</div>
        </div>
        <div class="ap-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Hủy bỏ</button>
            <button type="button" class="btn btn-danger" id="btn-confirm-reject" onclick="handleRejectSubmit()">Xác nhận từ chối</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const CSRF_TOKEN = '<?php echo htmlspecialchars($data['csrf_token']); ?>';
        const APPROVE_URL_BASE = '<?php echo URLROOT; ?>/admin/stores/approve/';
        const REJECT_URL_BASE = '<?php echo URLROOT; ?>/admin/stores/reject/';

        let currentRejectStoreId = null;

        // Mở Modal từ chối
        window.openRejectModal = function (storeId, storeName) {
            currentRejectStoreId = storeId;
            document.getElementById('reject-modal-store-name').textContent = storeName;
            document.getElementById('reject-reason-input').value = '';
            document.getElementById('reject-error-msg').style.display = 'none';
            document.getElementById('reject-modal-overlay').classList.add('ap-modal--visible');
            document.getElementById('reject-reason-input').focus();
        };

        // Đóng Modal từ chối
        window.closeRejectModal = function () {
            currentRejectStoreId = null;
            document.getElementById('reject-modal-overlay').classList.remove('ap-modal--visible');
        };

        // Đóng modal khi click ra ngoài overlay
        document.getElementById('reject-modal-overlay').addEventListener('click', function (e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });

        // Xử lý Phê duyệt cửa hàng qua AJAX
        window.handleApprove = function (storeId, storeName) {
            if (!confirm('Bạn có chắc chắn muốn phê duyệt hồ sơ cửa hàng "' + storeName + '"?\n\nTài khoản đăng ký sẽ được tự động nâng cấp lên quyền Người bán (Seller).')) {
                return;
            }

            const row = document.getElementById('store-row-' + storeId);
            const buttons = row ? row.querySelectorAll('button') : [];
            const approveBtn = row ? row.querySelector('.btn-action-approve') : null;
            const originalBtnContent = approveBtn ? approveBtn.innerHTML : '';

            // Bật trạng thái loading
            buttons.forEach(btn => btn.disabled = true);
            if (approveBtn) {
                approveBtn.classList.add('is-loading');
                approveBtn.innerHTML = '<span class="btn-loading-spinner"></span> Đang duyệt...';
            }
            if (row) row.classList.add('row-processing');

            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('store_id', storeId);

            fetch(APPROVE_URL_BASE + storeId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToastNotification(data.message, 'success');
                    removeStoreRow(storeId);
                } else {
                    showToastNotification(data.message || 'Đã có lỗi xảy ra', 'error');
                    buttons.forEach(btn => btn.disabled = false);
                    if (approveBtn) {
                        approveBtn.classList.remove('is-loading');
                        approveBtn.innerHTML = originalBtnContent;
                    }
                    if (row) row.classList.remove('row-processing');
                }
            })
            .catch(err => {
                showToastNotification('Lỗi kết nối máy chủ khi phê duyệt.', 'error');
                buttons.forEach(btn => btn.disabled = false);
                if (approveBtn) {
                    approveBtn.classList.remove('is-loading');
                    approveBtn.innerHTML = originalBtnContent;
                }
                if (row) row.classList.remove('row-processing');
            });
        };

        // Xử lý Submit Từ chối cửa hàng qua AJAX
        window.handleRejectSubmit = function () {
            if (!currentRejectStoreId) return;

            const reasonInput = document.getElementById('reject-reason-input');
            const reason = reasonInput.value.trim();
            const errorMsg = document.getElementById('reject-error-msg');
            const confirmBtn = document.getElementById('btn-confirm-reject');
            const originalBtnContent = confirmBtn.innerHTML;

            if (!reason) {
                errorMsg.style.display = 'block';
                reasonInput.focus();
                return;
            }
            errorMsg.style.display = 'none';
            
            // Bật trạng thái loading
            confirmBtn.disabled = true;
            confirmBtn.classList.add('is-loading');
            confirmBtn.innerHTML = '<span class="btn-loading-spinner"></span> Đang xử lý...';

            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('store_id', currentRejectStoreId);
            formData.append('reason', reason);

            const storeId = currentRejectStoreId;

            fetch(REJECT_URL_BASE + storeId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('is-loading');
                confirmBtn.innerHTML = originalBtnContent;
                if (data.success) {
                    closeRejectModal();
                    showToastNotification(data.message, 'success');
                    removeStoreRow(storeId);
                } else {
                    showToastNotification(data.message || 'Đã có lỗi xảy ra', 'error');
                }
            })
            .catch(err => {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('is-loading');
                confirmBtn.innerHTML = originalBtnContent;
                showToastNotification('Lỗi kết nối máy chủ khi từ chối hồ sơ.', 'error');
            });
        };

        // Xóa dòng hiển thị sau khi xử lý thành công
        function removeStoreRow(storeId) {
            const row = document.getElementById('store-row-' + storeId);
            if (!row) return;

            row.classList.add('row-fade-out');
            setTimeout(() => {
                if (row && row.parentNode) {
                    row.parentNode.removeChild(row);
                }

                // Cập nhật số đếm badge
                const countElem = document.getElementById('pending-count-num');
                if (countElem) {
                    let current = parseInt(countElem.textContent, 10) || 0;
                    current = Math.max(0, current - 1);
                    countElem.textContent = current;
                }

                // Kiểm tra xem bảng còn dòng nào không
                const tbody = document.getElementById('stores-table-body');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    tbody.innerHTML = '<tr id="empty-state-row"><td colspan="8" class="table-empty-row">Hiện không có hồ sơ đăng ký cửa hàng nào đang chờ duyệt.</td></tr>';
                }
            }, 350);
        }

        // Hiển thị thông báo Toast chuẩn qua FlashModule
        function showToastNotification(message, type) {
            if (typeof FlashModule !== 'undefined' && typeof FlashModule.show === 'function') {
                FlashModule.show(message, type);
                return;
            }
            if (typeof window.showFlash === 'function') {
                window.showFlash(type, message);
                return;
            }

            // Fallback an toàn hiển thị vào toast-container fixed
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
            alertDiv.innerHTML = '<span class="alert-icon">' + (type === 'success' ? '✅' : '❌') + '</span> ' + message;
            container.appendChild(alertDiv);
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 4500);
        }
    })();
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

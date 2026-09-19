<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- Mượn tạm CSS của admin-products để dùng modal native -->
<link rel="stylesheet" href="<?= URLROOT; ?>/css/pages/admin-products.css?v=<?= time(); ?>">

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px;">
    <!-- Tiêu đề trang -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: #1d1d1f;">
            Kho tài liệu đã mua
        </h1>
        <p style="font-size: 15px; color: #86868b; margin: 0;">
            Quản lý các tài liệu đã thanh toán. Vui lòng chọn <strong>Chấp nhận & Tải xuống</strong> để chốt đơn hàng hoặc <strong>Yêu cầu hoàn tiền</strong> nếu muốn hủy mua.
        </p>
    </div>

    <!-- Container thông báo Flash động cho AJAX -->
    <div id="dynamicFlashContainer"></div>
    <?php displayFlash('success'); displayFlash('error'); ?>

    <?php if (empty($data['purchases'])): ?>
        <!-- Trạng thái trống -->
        <div class="empty-state" style="margin-top: 20px; text-align: center; padding: 70px 24px; background: #f9fafb; border-radius: 16px; border: 1px dashed rgba(0,0,0,0.12);">
            <p style="font-size: 48px; margin-bottom: 16px;">📚</p>
            <h3 style="font-size: 20px; font-weight: 600; color: var(--apple-black);">Bạn chưa mua tài liệu nào</h3>
            <p style="margin-bottom: 24px;">Khám phá hàng ngàn tài liệu chất lượng trên chợ.</p>
            <a href="<?= URLROOT ?>/products/index" class="btn btn-primary" style="padding: 12px 24px; border-radius: 980px; width: auto; display: inline-block;">Khám phá ngay</a>
        </div>
    <?php else: ?>
        <!-- Danh sách đơn hàng -->
        <div class="purchases-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 24px;">
            <?php foreach ($data['purchases'] as$order): ?>
                <?php
                $status = (int)$order->status;
                // Ràng buộc 7 ngày hoàn tiền
                $daysSincePurchase = (time() - strtotime($order->purchased_at)) / (60 * 60 * 24);$canRefund = ($status === 2 &&$daysSincePurchase <= 7);
                ?>
                <div class="purchase-card interactive-hover" id="purchase-card-<?= $order->order_id ?>" style="background: #ffffff; padding: 20px; display: flex; flex-direction: row; gap: 20px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 2px 12px rgba(0,0,0,0.03);">
                    
                    <!-- Thumbnail -->
                    <div style="width: 130px; height: 130px; flex-shrink: 0; background: var(--apple-gray-bg); border-radius: 14px; overflow: hidden; border: 1px solid var(--apple-gray-border);">
                        <?php if (!empty($order->preview_url)): ?>
                            <img src="<?= URLROOT . htmlspecialchars($order->preview_url) ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--apple-gray); font-size: 12px;">No Image</div>
                        <?php endif; ?>
                    </div>

                    <!-- Thông tin -->
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-start;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                            <h3 style="font-size: 17px; font-weight: 600; line-height: 1.4; margin: 0; color: var(--apple-black);">
                                <?= htmlspecialchars($order->title) ?>
                            </h3>
                        </div>
                        
                        <div style="font-size: 13px; color: #6b7280; display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px;">
                            <div>
                                Người bán: 
                                <?php if (!empty($order->store_slug)): ?>
                                    <a href="<?= URLROOT; ?>/storefront/<?= $order->store_slug; ?>" style="color: #0071e3; text-decoration: none; font-weight: 500;">
                                        <?= htmlspecialchars($order->store_name); ?>
                                    </a>
                                <?php else: ?>
                                    <strong style="color: #374151;"><?= htmlspecialchars($order->store_name); ?></strong>
                                <?php endif; ?>
                            </div>
                            <div>Ngày mua: <?= date('d/m/Y H:i', strtotime($order->purchased_at)); ?></div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <strong style="color: var(--apple-green); font-size: 18px;"><?= number_format($order->price, 0, ',', '.') ?>đ</strong>
                            <span id="order-badge-<?= $order->order_id ?>">
                                <?php if ($status === 2): ?>
                                    <span style="font-size: 12px; font-weight: 600; color: #92400e; background: #fef3c7; padding: 4px 10px; border-radius: 6px;">Chờ xác nhận</span>
                                <?php elseif ($status === 5): ?>
                                    <span style="font-size: 12px; font-weight: 600; color: #166534; background: #dcfce7; padding: 4px 10px; border-radius: 6px;">Đã nhận</span>
                                <?php elseif ($status === 4): ?>
                                    <span style="font-size: 12px; font-weight: 600; color: #991b1b; background: #fee2e2; padding: 4px 10px; border-radius: 6px;">Đã hoàn tiền</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <!-- Hướng dẫn trạng thái -->
                        <div id="order-notice-<?= $order->order_id ?>" style="margin-bottom: 12px;">
                            <?php if ($status === 2) : ?>
                                <div style="padding: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; font-size: 12px; color: #92400e; line-height: 1.4;">
                                    Tích vào ô bên dưới và bấm <strong>Chấp nhận & Tải xuống</strong> để chốt giao dịch.
                                </div>
                            <?php elseif ($status === 5) : ?>
                                <div style="padding: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 12px; color: #166534;">
                                    Giao dịch đã hoàn tất. Quyền hoàn tiền đã đóng vĩnh viễn.
                                </div>
                            <?php elseif ($status === 4) : ?>
                                <div style="padding: 10px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; font-size: 12px; color: #991b1b;">
                                    Tiền đã được hoàn về ví. Quyền tải tài liệu đã kết thúc.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Nút thao tác (Footer của Card) -->
                        <div id="order-footer-<?= $order->order_id ?>" style="margin-top: auto; display: flex; flex-direction: column; gap: 8px;">
                            <?php if ($status === 2): ?>
                                <!-- Checkbox xác nhận -->
                                <div class="confirm-checkbox-wrapper" style="background: #fff3cd; padding: 10px; border-radius: 8px; border: 1px solid #ffe69c;">
                                    <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 13px; color: #664d03; margin: 0;">
                                        <input type="checkbox" class="accept-checkbox" data-order-id="<?= $order->order_id ?>" style="margin-top: 3px;">
                                        <span>
                                            Tôi đồng ý <strong>Chấp nhận & Tải xuống</strong>.<br>
                                            <span style="color: #dc3545; font-weight: 600;">Hủy quyền hoàn tiền sau khi tải!</span>
                                        </span>
                                    </label>
                                </div>
                                
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <button type="button" id="btn-accept-<?= $order->order_id ?>" 
                                            onclick="handleAcceptAndDownload(<?= $order->order_id ?>, <?= $order->product_id ?>)" 
                                            class="btn btn-primary" disabled
                                            style="flex: 1; padding: 10px; font-size: 14px; font-weight: 600; border-radius: 10px; background: #0071e3; border: none;">
                                        Chấp nhận & Tải xuống
                                    </button>

                                    <?php if ($canRefund): ?>
                                        <button type="button" class="btn btn-danger-outline" 
                                                onclick="openRefundModal(<?= $order->order_id ?>, '<?= htmlspecialchars(addslashes($order->title)) ?>', '<?= htmlspecialchars($order->order_number ?? ('ORD-'.$order->order_id)) ?>', '<?= number_format((float)$order->price, 0, ',', '.') ?> đ')"
                                                style="padding: 10px 16px; font-size: 13px; font-weight: 600; border-radius: 10px; background: #fef2f2; border: 1px solid #ef4444; color: #dc2626;">
                                            Hoàn tiền
                                        </button>
                                    <?php endif; ?>
                                </div>

                            <?php elseif ($status === 5): ?>
                                <div style="display: flex; gap: 8px;">
                                    <a href="<?= URLROOT; ?>/downloads/file/<?= $order->product_id; ?>" class="btn btn-primary" style="flex: 1; text-align: center; padding: 10px; font-size: 14px; font-weight: 600; background: #10b981; border: none; border-radius: 10px; color: #fff; text-decoration: none;">
                                        Tải lại tài liệu
                                    </a>
                                    <a href="<?= URLROOT; ?>/products/detail/<?= $order->product_id; ?>" class="btn btn-secondary" style="padding: 10px 16px; font-size: 13px; border-radius: 10px; text-decoration: none; color: #4b5563; background: #e5e7eb; display: inline-flex; align-items: center;">
                                        Xem chi tiết
                                    </a>
                                </div>

                            <?php elseif ($status === 4): ?>
                                <div style="display: flex; gap: 8px;">
                                    <button disabled style="flex: 1; padding: 10px; font-size: 13px; font-weight: 500; background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb; border-radius: 10px; cursor: not-allowed;">
                                        Đã hoàn tiền (Khóa tải file)
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL YÊU CẦU HOÀN TIỀN DUY NHẤT (Dùng chung cho tất cả các item) -->
<div id="refundModal" class="native-modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="native-modal-box card" style="background: #ffffff; width: 100%; max-width: 480px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
        
        <div style="padding: 18px 22px; border-bottom: 1px solid rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 17px; font-weight: 700; margin: 0; color: #111827;">Xác nhận yêu cầu hoàn tiền</h3>
            <button type="button" onclick="closeRefundModal()" style="background: none; border: none; font-size: 22px; line-height: 1; color: #6b7280; cursor: pointer; padding: 0;">&times;</button>
        </div>

        <form id="refundForm" action="" method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" name="redirect_to" value="<?= URLROOT; ?>/orders/myPurchases">
            
            <div style="padding: 22px; display: flex; flex-direction: column; gap: 16px;">
                
                <div style="background: #f9fafb; padding: 14px 16px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.06);">
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Tài liệu hoàn tiền:</div>
                    <div id="modalProductTitle" style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 6px;"></div>
                    <div style="font-size: 13px; color: #374151; display: flex; justify-content: space-between;">
                        <span>Mã đơn: <strong id="modalOrderNumber"></strong></span>
                        <span>Số tiền hoàn: <strong id="modalOrderAmount" style="color: #059669;"></strong></span>
                    </div>
                </div>

                <div>
                    <label for="refundReason" style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Lý do hoàn tiền (Bắt buộc) <span class="text-danger">*</span>:
                    </label>
                    <textarea id="refundReason" name="reason" rows="3" required placeholder="Tài liệu bị lỗi, file bị hỏng, không đúng mô tả..." style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid #d1d5db; font-size: 14px; outline: none; font-family: inherit; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 10px; padding: 12px; font-size: 12px; color: #991b1b; line-height: 1.5;">
                    <strong>Lưu ý:</strong> Sau khi xác nhận, tiền sẽ được chuyển trực tiếp từ ví người bán về ví của bạn ngay lập tức, đồng thời quyền tải tài liệu này sẽ bị <strong>hủy bỏ vĩnh viễn</strong>.
                </div>
            </div>

            <div style="padding: 14px 22px; background: #f9fafb; border-top: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRefundModal()" style="padding: 9px 16px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; font-size: 13px; font-weight: 500; color: #374151; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 9px 18px; border-radius: 8px; border: none; background: #dc2626; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;">Xác nhận hoàn tiền</button>
            </div>
        </form>
    </div>
</div>

<script>
// ==========================================
// LOGIC CHECKBOX MỞ KHÓA NÚT TẢI
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.accept-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const orderId = this.getAttribute('data-order-id');
            const btn = document.getElementById('btn-accept-' + orderId);
            if (btn) {
                btn.disabled = !this.checked;
            }
        });
    });
});

// ==========================================
// LOGIC AJAX CHỐT GIAO DỊCH & TẢI XUỐNG
// ==========================================
async function handleAcceptAndDownload(orderId, productId) {
    const btn = document.getElementById('btn-accept-' + orderId);
    const originalText = btn ? btn.textContent : 'Chấp nhận & Tải xuống';

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Đang xử lý...';
    }

    const csrfToken = '<?= $_SESSION['csrf_token'] ?? ''; ?>';
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('is_ajax', '1');

    try {
        const response = await fetch('<?= URLROOT; ?>/orders/acceptAndDownload/' + orderId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // 1. Cập nhật Badge
            const badgeContainer = document.getElementById('order-badge-' + orderId);
            if (badgeContainer) {
                badgeContainer.innerHTML = '<span style="font-size: 12px; font-weight: 600; color: #166534; background: #dcfce7; padding: 4px 10px; border-radius: 6px;">Đã nhận</span>';
            }

            // 2. Cập nhật dòng Notice
            const noticeContainer = document.getElementById('order-notice-' + orderId);
            if (noticeContainer) {
                noticeContainer.innerHTML = '<div style="padding: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 12px; color: #166534;">Giao dịch đã hoàn tất. Quyền hoàn tiền đã đóng vĩnh viễn.</div>';
            }

            // 3. Thay đổi Footer Card thành nút Tải lại
            const footerContainer = document.getElementById('order-footer-' + orderId);
            const downloadUrl = data.download_url || ('<?= URLROOT; ?>/downloads/file/' + productId);

            if (footerContainer) {
                footerContainer.innerHTML = `
                    <div style="display: flex; gap: 8px;">
                        <a href="${downloadUrl}" class="btn btn-primary" style="flex: 1; text-align: center; padding: 10px; font-size: 14px; font-weight: 600; background: #10b981; border: none; border-radius: 10px; color: #fff; text-decoration: none;">
                            Tải lại tài liệu
                        </a>
                        <a href="<?= URLROOT; ?>/products/detail/${productId}" class="btn btn-secondary" style="padding: 10px 16px; font-size: 13px; border-radius: 10px; text-decoration: none; color: #4b5563; background: #e5e7eb; display: inline-flex; align-items: center;">
                            Xem chi tiết
                        </a>
                    </div>
                `;
            }

            // 4. Báo thành công
            showFlashToast(data.message || 'Chốt giao dịch thành công!', 'success');

            // 5. Mở link tải file
            if (downloadUrl) {
                const downloadLink = document.createElement('a');
                downloadLink.href = downloadUrl;
                downloadLink.setAttribute('download', '');
                document.body.appendChild(downloadLink);
                downloadLink.click();
                downloadLink.remove();
            }
        } else {
            if (btn) {
                btn.disabled = false;
                btn.textContent = originalText;
            }
            showFlashToast(data.message || 'Xác nhận thất bại.', 'error');
        }
    } catch (error) {
        console.error(error);
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText;
        }
        showFlashToast('Lỗi kết nối máy chủ.', 'error');
    }
}

// ==========================================
// HÀM HIỂN THỊ THÔNG BÁO TOAST
// ==========================================
function showFlashToast(message, type = 'success') {
    if (typeof FlashModule !== 'undefined' && typeof FlashModule.show === 'function') {
        FlashModule.show(message, type);
        return;
    }
    const container = document.getElementById('dynamicFlashContainer');
    if (container) {
        const isSuccess = (type === 'success');
        const alertBox = document.createElement('div');
        alertBox.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-danger');
        alertBox.style.cssText = `padding: 14px 20px; background: ${isSuccess ? '#dcfce7' : '#fee2e2'}; border: 1px solid ${isSuccess ? '#86efac' : '#fca5a5'}; color: ${isSuccess ? '#166534' : '#991b1b'}; border-radius: 12px; margin-bottom: 24px;`;
        alertBox.textContent = message;

        container.innerHTML = '';
        container.appendChild(alertBox);

        setTimeout(() => {
            alertBox.style.opacity = '0';
            alertBox.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alertBox.remove(), 500);
        }, 5000);
    }
}

// ==========================================
// LOGIC MODAL HOÀN TIỀN
// ==========================================
function openRefundModal(orderId, title, orderNumber, amount) {
    const modal = document.getElementById('refundModal');
    const form = document.getElementById('refundForm');
    
    // Gán action form tới đúng Order ID
    form.action = '<?= URLROOT; ?>/orders/refund/' + orderId;
    
    // Gán thông tin hiển thị lên Modal
    document.getElementById('modalProductTitle').textContent = title;
    document.getElementById('modalOrderNumber').textContent = orderNumber;
    document.getElementById('modalOrderAmount').textContent = amount;
    document.getElementById('refundReason').value = '';

    modal.style.display = 'flex';
}

function closeRefundModal() {
    document.getElementById('refundModal').style.display = 'none';
}

// Đóng modal khi bấm ra ngoài vùng xám
window.addEventListener('click', function(e) {
    const modal = document.getElementById('refundModal');
    if (e.target === modal) {
        closeRefundModal();
    }
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px; max-width: 1200px;">

    <!-- Tiêu đề trang -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: #1d1d1f;">
            Kho tài liệu của tôi
        </h1>
        <p style="font-size: 15px; color: #86868b; margin: 0;">
            Quản lý các tài liệu đã thanh toán. Vui lòng chọn <strong>Chấp nhận & Tải xuống</strong> để chốt đơn hàng hoặc <strong>Yêu cầu hoàn tiền</strong> nếu muốn hủy mua.
        </p>
    </div>

    <!-- Container thông báo Flash động cho AJAX -->
    <div id="dynamicFlashContainer"></div>

    <!-- Thông báo Flash Messages (CodeIgniter 4 session()->getFlashdata syntax) -->
    <?php if (function_exists('session') && ($successFlash = session()->getFlashdata('success'))) : ?>
        <div class="alert alert-success" style="padding: 14px 20px; background: #dcfce7; border: 1px solid #86efac; color: #166534; border-radius: 12px; margin-bottom: 24px;">
            <?= htmlspecialchars((string)$successFlash); ?>
        </div>
    <?php endif; ?>

    <?php if (function_exists('session') && ($errorFlash = session()->getFlashdata('error'))) : ?>
        <div class="alert alert-danger" style="padding: 14px 20px; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 12px; margin-bottom: 24px;">
            <?= htmlspecialchars((string)$errorFlash); ?>
        </div>
    <?php endif; ?>

    <?php if (function_exists('session') && ($warningFlash = session()->getFlashdata('warning'))) : ?>
        <div class="alert alert-warning" style="padding: 14px 20px; background: #fef3c7; border: 1px solid #fde047; color: #854d0e; border-radius: 12px; margin-bottom: 24px;">
            <?= htmlspecialchars((string)$warningFlash); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($data['purchases'])) : ?>
        <div class="purchases-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">
            <?php foreach ($data['purchases'] as $item) : 
                $status = (int)($item->order_status ?? 2);
            ?>
                <div class="purchase-card" id="purchase-card-<?= $item->order_id; ?>" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08); border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 2px 12px rgba(0,0,0,0.03); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    
                    <!-- Header Card: Thông tin trạng thái & Mã đơn -->
                    <div style="padding: 14px 20px; background: #f9fafb; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600; color: #6b7280; font-family: monospace;">
                            #<?= htmlspecialchars($item->order_number ?? ('ORD-' . $item->order_id)); ?>
                        </span>

                        <span id="order-badge-<?= $item->order_id; ?>">
                            <?php if ($status === 2) : ?>
                                <span style="font-size: 12px; font-weight: 600; color: #92400e; background: #fef3c7; padding: 4px 10px; border-radius: 6px;">
                                    Chờ xác nhận
                                </span>
                            <?php elseif ($status === 5) : ?>
                                <span style="font-size: 12px; font-weight: 600; color: #166534; background: #dcfce7; padding: 4px 10px; border-radius: 6px;">
                                    Đã nhận
                                </span>
                            <?php elseif ($status === 4) : ?>
                                <span style="font-size: 12px; font-weight: 600; color: #991b1b; background: #fee2e2; padding: 4px 10px; border-radius: 6px;">
                                    Đã hoàn tiền
                                </span>
                            <?php else : ?>
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 6px;">
                                    Trạng thái #<?= $status; ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <!-- Nội dung chính của Card -->
                    <div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column; gap: 12px;">
                        <h3 style="font-size: 16px; font-weight: 600; line-height: 1.4; margin: 0; color: #111827;">
                            <?= htmlspecialchars($item->title); ?>
                        </h3>

                        <div style="font-size: 13px; color: #6b7280; display: flex; flex-direction: column; gap: 4px;">
                            <div>
                                Người bán: 
                                <?php if (!empty($item->store_slug)): ?>
                                    <a href="<?= URLROOT; ?>/storefront/<?= $item->store_slug; ?>" style="color: #0071e3; text-decoration: none; font-weight: 500;">
                                        <?= htmlspecialchars($item->store_name); ?>
                                    </a>
                                <?php else: ?>
                                    <strong style="color: #374151;"><?= htmlspecialchars($item->store_name); ?></strong>
                                <?php endif; ?>
                            </div>
                            <div>
                                Ngày mua: <?= date('d/m/Y H:i', strtotime($item->purchased_at)); ?>
                            </div>
                            <div>
                                Giá thanh toán: <strong style="color: #111827;"><?= number_format((float)$item->price, 0, ',', '.'); ?> đ</strong>
                            </div>
                        </div>

                        <!-- Hướng dẫn trạng thái -->
                        <div id="order-notice-<?= $item->order_id; ?>" style="margin-top: auto;">
                            <?php if ($status === 2) : ?>
                                <div style="padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; font-size: 12px; color: #92400e; line-height: 1.5;">
                                    <strong>Lưu ý:</strong> Vui lòng chọn <em>Chấp nhận & Tải xuống</em> để chốt nhận tài liệu và kết thúc giao dịch, hoặc chọn <em>Yêu cầu hoàn tiền</em> để nhận lại tiền về ví.
                                </div>
                            <?php elseif ($status === 5) : ?>
                                <div style="padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; font-size: 12px; color: #166534;">
                                    Giao dịch đã hoàn tất. Quyền hoàn tiền đã đóng vĩnh viễn cho đơn hàng này.
                                </div>
                            <?php elseif ($status === 4) : ?>
                                <div style="padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; font-size: 12px; color: #991b1b;">
                                    Đã hoàn tiền về ví của bạn. Quyền truy cập tài liệu này đã kết thúc.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Footer: Các nút thao tác -->
                    <div id="order-footer-<?= $item->order_id; ?>" style="padding: 16px 20px; background: #f9fafb; border-top: 1px solid rgba(0,0,0,0.06);">
                        
                        <?php if ($status === 2) : ?>
                            <!-- TRẠNG THÁI 2: CHƯA CHO TẢI TRỰC TIẾP, HIỂN THỊ 2 NÚT LỰA CHỌN -->
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                
                                <!-- Nút 1: Chấp nhận & Tải xuống (AJAX/Fetch API) -->
                                <button type="button" 
                                        id="btn-accept-<?= $item->order_id; ?>"
                                        onclick="handleAcceptAndDownload(<?= $item->order_id; ?>, <?= $item->product_id; ?>)"
                                        class="btn btn-primary" 
                                        style="width: 100%; padding: 11px; font-size: 14px; font-weight: 600; border-radius: 10px; background: #0071e3; color: #ffffff; border: none; cursor: pointer; text-align: center; transition: background 0.2s ease;">
                                    Chấp nhận & Tải xuống
                                </button>

                                <div style="display: flex; gap: 8px;">
                                    <!-- Nút 2: Yêu cầu hoàn tiền -->
                                    <button type="button" 
                                            class="btn btn-danger-outline" 
                                            onclick="openRefundModal(<?= $item->order_id; ?>, '<?= htmlspecialchars(addslashes($item->title)); ?>', '<?= htmlspecialchars($item->order_number ?? ('ORD-' . $item->order_id)); ?>', '<?= number_format((float)$item->price, 0, ',', '.'); ?> đ')"
                                            style="flex: 1; padding: 10px; font-size: 13px; font-weight: 600; border-radius: 10px; background: #fff; border: 1px solid #ef4444; color: #dc2626; cursor: pointer; text-align: center; transition: all 0.2s ease;">
                                        Yêu cầu hoàn tiền
                                    </button>

                                    <!-- Xem chi tiết sản phẩm -->
                                    <a href="<?= URLROOT; ?>/products/detail/<?= $item->product_id; ?>" 
                                       class="btn btn-secondary" 
                                       style="padding: 10px 14px; font-size: 13px; border-radius: 10px; text-decoration: none; color: #4b5563; background: #e5e7eb; display: inline-flex; align-items: center; justify-content: center;">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>

                        <?php elseif ($status === 5) : ?>
                            <!-- TRẠNG THÁI 5: ĐÃ NHẬN -> CHO PHÉP TẢI FILE, KHÓA HOÀN TIỀN -->
                            <div style="display: flex; gap: 8px;">
                                <a href="<?= URLROOT; ?>/downloads/file/<?= $item->product_id; ?>" 
                                   class="btn btn-primary" 
                                   style="flex: 1; text-align: center; padding: 11px; font-size: 14px; font-weight: 600; background: #10b981; color: #fff; border-radius: 10px; text-decoration: none; display: block;">
                                    Tải file xuống
                                </a>

                                <a href="<?= URLROOT; ?>/products/detail/<?= $item->product_id; ?>" 
                                   class="btn btn-secondary" 
                                   style="padding: 11px 16px; font-size: 13px; border-radius: 10px; text-decoration: none; color: #4b5563; background: #e5e7eb; display: inline-flex; align-items: center; justify-content: center;">
                                    Xem lại
                                </a>
                            </div>

                        <?php elseif ($status === 4) : ?>
                            <!-- TRẠNG THÁI 4: ĐÃ HOÀN TIỀN -> HỦY QUYỀN TẢI -->
                            <div style="display: flex; gap: 8px;">
                                <button disabled 
                                        style="flex: 1; padding: 11px; font-size: 13px; font-weight: 500; background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb; border-radius: 10px; cursor: not-allowed;">
                                    Đã hoàn tiền (Không thể tải file)
                                </button>
                                <a href="<?= URLROOT; ?>/products/detail/<?= $item->product_id; ?>" 
                                   class="btn btn-secondary" 
                                   style="padding: 11px 16px; font-size: 13px; border-radius: 10px; text-decoration: none; color: #0071e3; background: #eff6ff; display: inline-flex; align-items: center; justify-content: center; font-weight: 500;">
                                    Mua lại
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="empty-state" style="text-align: center; padding: 70px 24px; background: #f9fafb; border-radius: 16px; border: 1px dashed rgba(0,0,0,0.12);">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 8px;">Kho tài liệu trống</h3>
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">Bạn chưa có tài liệu nào trong kho.</p>
            <a href="<?= URLROOT; ?>/products/index" class="btn btn-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 10px; background: #0071e3; color: #fff; text-decoration: none; display: inline-block;">
                Đến chợ tài liệu
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL YÊU CẦU HOÀN TIỀN (UC32) -->
<div id="refundModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 480px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
        
        <!-- Modal Header -->
        <div style="padding: 18px 22px; border-bottom: 1px solid rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 17px; font-weight: 700; margin: 0; color: #111827;">Xác nhận yêu cầu hoàn tiền</h3>
            <button type="button" onclick="closeRefundModal()" style="background: none; border: none; font-size: 22px; line-height: 1; color: #6b7280; cursor: pointer; padding: 0;">&times;</button>
        </div>

        <!-- Modal Body & Form -->
        <form id="refundForm" action="<?= URLROOT; ?>/orders/refund" method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?? ''; ?>">
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
                        Lý do hoàn tiền (tùy chọn):
                    </label>
                    <textarea id="refundReason" 
                              name="reason" 
                              rows="3" 
                              placeholder="Nhập lý do hoàn tiền nếu có..." 
                              style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid #d1d5db; font-size: 14px; outline: none; font-family: inherit; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 10px; padding: 12px; font-size: 12px; color: #991b1b; line-height: 1.5;">
                    <strong>Lưu ý:</strong> Sau khi xác nhận, tiền sẽ được chuyển trực tiếp từ ví người bán về ví của bạn ngay lập tức, đồng thời quyền truy cập và tải tài liệu này sẽ bị hủy bỏ.
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 14px 22px; background: #f9fafb; border-top: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRefundModal()" style="padding: 9px 16px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; font-size: 13px; font-weight: 500; color: #374151; cursor: pointer;">
                    Đóng
                </button>
                <button type="submit" style="padding: 9px 18px; border-radius: 8px; border: none; background: #dc2626; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Xác nhận hoàn tiền
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.purchase-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.06) !important;
}
.btn-danger-outline:hover {
    background: #fee2e2 !important;
}
</style>

<script>
// Xử lý gửi AJAX khi bấm 'Chấp nhận & Tải xuống'
async function handleAcceptAndDownload(orderId, productId) {
    const btn = document.getElementById('btn-accept-' + orderId);
    const originalText = btn ? btn.textContent : 'Chấp nhận & Tải xuống';

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Đang xử lý...';
    }

    const csrfToken = '<?= $data['csrf_token'] ?? ''; ?>';
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
            // 1. Cập nhật Badge của thẻ sang 'Đã nhận'
            const badgeContainer = document.getElementById('order-badge-' + orderId);
            if (badgeContainer) {
                badgeContainer.innerHTML = `
                    <span style="font-size: 12px; font-weight: 600; color: #166534; background: #dcfce7; padding: 4px 10px; border-radius: 6px;">
                        Đã nhận
                    </span>
                `;
            }

            // 2. Cập nhật dòng thông báo hướng dẫn
            const noticeContainer = document.getElementById('order-notice-' + orderId);
            if (noticeContainer) {
                noticeContainer.innerHTML = `
                    <div style="padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; font-size: 12px; color: #166534;">
                        Giao dịch đã hoàn tất. Quyền hoàn tiền đã đóng vĩnh viễn cho đơn hàng này.
                    </div>
                `;
            }

            // 3. Ẩn nút Chấp nhận/Hoàn tiền, hiện nút 'Tải file xuống' màu xanh
            const footerContainer = document.getElementById('order-footer-' + orderId);
            const downloadUrl = data.download_url || ('<?= URLROOT; ?>/downloads/file/' + productId);

            if (footerContainer) {
                footerContainer.innerHTML = `
                    <div style="display: flex; gap: 8px;">
                        <a href="${downloadUrl}" 
                           class="btn btn-primary" 
                           style="flex: 1; text-align: center; padding: 11px; font-size: 14px; font-weight: 600; background: #10b981; color: #fff; border-radius: 10px; text-decoration: none; display: block;">
                            Tải file xuống
                        </a>
                        <a href="<?= URLROOT; ?>/products/detail/${productId}" 
                           class="btn btn-secondary" 
                           style="padding: 11px 16px; font-size: 13px; border-radius: 10px; text-decoration: none; color: #4b5563; background: #e5e7eb; display: inline-flex; align-items: center; justify-content: center;">
                            Xem lại
                        </a>
                    </div>
                `;
            }

            // 4. Hiển thị thông báo Flash/Toast xanh
            showFlashToast(data.message || 'Xác nhận nhận tài liệu thành công! Giao dịch đã được chốt hoàn tất.', 'success');

            // 5. Tự động kích hoạt tải file về máy bằng JS
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
            showFlashToast(data.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
        }
    } catch (error) {
        console.error('Accept and download error:', error);
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText;
        }
        showFlashToast('Lỗi kết nối máy chủ. Vui lòng thử lại sau.', 'error');
    }
}

// Hàm hiển thị Flash message / Toast xanh
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

function openRefundModal(orderId, title, orderNumber, amount) {
    const modal = document.getElementById('refundModal');
    const form = document.getElementById('refundForm');
    form.action = '<?= URLROOT; ?>/orders/refund/' + orderId;
    
    document.getElementById('modalProductTitle').textContent = title;
    document.getElementById('modalOrderNumber').textContent = orderNumber;
    document.getElementById('modalOrderAmount').textContent = amount;
    document.getElementById('refundReason').value = '';

    modal.style.display = 'flex';
}

function closeRefundModal() {
    document.getElementById('refundModal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('refundModal');
    if (e.target === modal) {
        closeRefundModal();
    }
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
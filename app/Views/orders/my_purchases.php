<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- Mượn tạm CSS của admin-products để dùng modal native -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/admin-products.css?v=<?php echo time(); ?>">

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px;">
    <h1 style="font-size: 32px; font-weight: 700; color: var(--apple-black); margin-bottom: 32px;">Kho tài liệu đã mua</h1>
    
    <?php displayFlash('success'); displayFlash('error'); ?>
    
    <?php if (empty($data['purchases'])): ?>
        <div class="empty-state" style="margin-top: 20px;">
            <p style="font-size: 48px; margin-bottom: 16px;">📚</p>
            <h3 style="font-size: 20px; font-weight: 600; color: var(--apple-black);">Bạn chưa mua tài liệu nào</h3>
            <p style="margin-bottom: 24px;">Khám phá hàng ngàn tài liệu chất lượng trên chợ.</p>
            <a href="<?= URLROOT ?>/products/index" class="btn btn-primary" style="padding: 12px 24px; border-radius: 980px; width: auto;">Khám phá ngay</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 24px;">
            <?php foreach ($data['purchases'] as $order): ?>
                <?php 
                    // Ràng buộc 7 ngày hoàn tiền
                    $daysSincePurchase = (time() - strtotime($order->purchased_at)) / (60 * 60 * 24);
                    $canRefund = ($order->status == 2 && $daysSincePurchase <= 7);
                ?>
                <div class="card interactive-hover" style="padding: 20px; display: flex; flex-direction: row; gap: 20px; max-width: 100%; border-radius: 20px;">
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
                        <h3 style="font-size: 17px; font-weight: 600; line-height: 1.4; margin-bottom: 4px; color: var(--apple-black);">
                            <?= htmlspecialchars($order->title) ?>
                        </h3>
                        <span style="font-size: 13px; color: var(--apple-gray); margin-bottom: 8px; display: block;">
                            Bán bởi: <strong><?= htmlspecialchars($order->store_name) ?></strong>
                        </span>
                        
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <strong style="color: var(--apple-green); font-size: 18px;"><?= number_format($order->price, 0, ',', '.') ?>đ</strong>
                            <span style="font-size: 12px; color: var(--apple-gray-light);">Mua: <?= date('d/m/Y', strtotime($order->purchased_at)) ?></span>
                        </div>
                        
                        <!-- Nút thao tác -->
                        <div style="margin-top: auto; display: flex; gap: 8px; flex-wrap: wrap;">
                            <a href="<?= URLROOT ?>/downloads/file/<?= $order->product_id ?>" class="btn btn-primary" style="padding: 8px 16px; border-radius: 10px; font-size: 14px; flex: 1; text-align: center;">Tải xuống</a>
                            
                            <?php if ($canRefund): ?>
                                <button type="button" class="btn" style="padding: 8px 16px; border-radius: 10px; font-size: 14px; background: #fef2f2; color: var(--apple-red); border: 1px solid #fca5a5; width: auto;" onclick="openCustomModal('refundModal<?= $order->order_id ?>')">
                                    Hoàn tiền
                                </button>
                            <?php elseif ($order->status == 4): ?>
                                <span class="badge-pill red" style="align-self: center; background: var(--apple-red); color: white;">Đã hoàn tiền</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- MODAL HOÀN TIỀN -->
                <?php if ($canRefund): ?>
                <div id="refundModal<?= $order->order_id ?>" class="native-modal-overlay">
                    <div class="native-modal-box card" style="max-width: 480px; padding: 32px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h3 style="font-size: 20px; font-weight: 700; margin: 0;">Yêu cầu hoàn tiền</h3>
                            <button type="button" onclick="closeCustomModal('refundModal<?= $order->order_id ?>')" style="background: none; border: none; font-size: 24px; color: #86868b; cursor: pointer;">&times;</button>
                        </div>
                        
                        <div style="background: #fffbeb; color: #b45309; padding: 12px 16px; border-radius: 12px; font-size: 14px; line-height: 1.5; margin-bottom: 20px; border: 1px solid #fde68a;">
                            Bạn đang yêu cầu hoàn lại <strong><?= number_format($order->price, 0, ',', '.') ?>đ</strong>. Sau khi hoàn tất, bạn sẽ <strong>mất quyền tải xuống</strong> tài liệu này.
                        </div>

                        <form action="<?= URLROOT ?>/orders/refund/<?= $order->order_id ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="form-group mb-4">
                                <label class="form-label" style="font-weight: 600;">Lý do hoàn tiền <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" required placeholder="Tài liệu bị lỗi, không đúng mô tả, file bị hỏng..."></textarea>
                            </div>
                            
                            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                                <button type="button" class="btn btn-secondary" style="padding: 10px 20px; border-radius: 12px;" onclick="closeCustomModal('refundModal<?= $order->order_id ?>')">Hủy bỏ</button>
                                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; border-radius: 12px; background: var(--apple-red); color: white; border: none;">Xác nhận hoàn tiền</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function openCustomModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeCustomModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 200);
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('native-modal-overlay')) {
            event.target.classList.remove('active');
            setTimeout(() => event.target.style.display = 'none', 200);
        }
    }
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
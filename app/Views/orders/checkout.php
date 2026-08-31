<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container" style="max-width: 640px; margin: 40px auto; padding: 0 15px;">
    <div class="card" style="padding: 28px; border-radius: 10px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #e9ecef;">
        
        <h2 style="margin-top: 0; margin-bottom: 20px; font-size: 22px; color: #2c3e50; border-bottom: 2px solid #f1f2f6; padding-bottom: 12px;">
            Xác nhận thanh toán
        </h2>
        
        <!-- Thông tin sản phẩm -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <span style="font-size: 12px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Tài liệu</span>
                <h3 style="margin: 4px 0 0 0; font-size: 17px; color: #2c3e50; line-height: 1.4;">
                    <?= htmlspecialchars($data['product']->title); ?>
                </h3>
            </div>
            <div style="text-align: right; min-width: 120px;">
                <span style="font-size: 12px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Giá bán</span>
                <p style="margin: 4px 0 0 0; font-size: 18px; color: #e74c3c; font-weight: 700;">
                    <?= number_format($data['product']->price, 0, ',', '.'); ?> đ
                </p>
            </div>
        </div>
        
        <!-- Hộp thông tin số dư ví -->
        <div style="background: #f8f9fa; border: 1px solid #edf2f7; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #4a5568; font-size: 14px;">Số dư ví hiện tại:</span>
                <strong style="font-size: 16px; color: <?= ($data['wallet']->balance < $data['product']->price) ? '#e74c3c' : '#27ae60'; ?>;">
                    <?= number_format($data['wallet']->balance, 0, ',', '.'); ?> đ
                </strong>
            </div>
            
            <?php if($data['wallet']->balance >= $data['product']->price): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #cbd5e0; font-size: 13px; color: #718096;">
                    <span>Số dư sau khi mua:</span>
                    <span><?= number_format($data['wallet']->balance - $data['product']->price, 0, ',', '.'); ?> đ</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Form xử lý -->
        <?php if($data['wallet']->balance < $data['product']->price): ?>
            <div class="alert alert-danger" style="margin-bottom: 16px; padding: 12px; border-radius: 6px; background: #fff5f5; color: #e53e3e; border: 1px solid #feb2b2; font-size: 14px;">
                Số dư trong ví không đủ để thanh toán tài liệu này. Vui lòng nạp thêm tiền.
            </div>
            <a href="<?= URLROOT; ?>/wallets" class="btn" style="display: block; text-align: center; background: #edf2f7; color: #4a5568; text-decoration: none; padding: 10px; border-radius: 6px; font-weight: 600;">
                Đến trang nạp tiền
            </a>
        <?php else: ?>
            <form action="<?= URLROOT; ?>/orders/process" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token']; ?>">
                <input type="hidden" name="product_id" value="<?= $data['product']->id; ?>">
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 15px; font-weight: 600; cursor: pointer;">
                    Xác nhận thanh toán ngay
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
<?php /** @var array $data */ ?>
<!-- Gọi Header -->
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="wallet-container" style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">
    
    <!-- CỘT TRÁI: THÔNG TIN VÍ & FORM RÚT TIỀN -->
    <div class="card" style="flex: 1; min-width: 300px;">
        <h2>Ví Điện Tử</h2>
        
        <!-- Hiển thị Số dư -->
        <div class="balance-box" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <p style="margin: 0; color: #7f8c8d; font-size: 14px;">Số dư khả dụng</p>
            <h3 style="margin: 10px 0; color: #27ae60; font-size: 28px;">
                <?php echo number_format($data['wallet']->balance, 0, ',', '.'); ?> đ
            </h3>
            <p style="margin: 0; color: #e74c3c; font-size: 14px;">
                Đang đóng băng (chờ rút): <?php echo number_format($data['wallet']->frozen_balance, 0, ',', '.'); ?> đ
            </p>
        </div>

        <hr style="border: 1px solid #eee; margin: 20px 0;">

        <!-- Form Yêu cầu rút tiền -->
        <h3>Yêu cầu rút tiền</h3>
        <form action="<?php echo URLROOT; ?>/wallets/withdraw" method="POST">
            
            <?php if(!empty($data['bank_err'])) : ?>
                <div class="alert" style="color: red; margin-bottom: 15px; font-size: 14px;">
                    <?php echo $data['bank_err']; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="amount">Số tiền cần rút (đ): *</label>
                <input type="number" name="amount" class="<?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" placeholder="Nhập số tiền..." required>
                <?php if(!empty($data['amount_err'])) : ?>
                    <span class="error-text" style="color: red; font-size: 12px;"><?php echo $data['amount_err']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="bank_name">Ngân hàng: *</label>
                <input type="text" name="bank_name" placeholder="VD: Vietcombank, Techcombank..." required>
            </div>

            <div class="form-group">
                <label for="bank_acc_num">Số tài khoản: *</label>
                <input type="text" name="bank_acc_num" placeholder="Nhập số tài khoản..." required>
            </div>

            <div class="form-group">
                <label for="bank_acc_name">Tên chủ tài khoản: *</label>
                <input type="text" name="bank_acc_name" placeholder="VIẾT HOA KHÔNG DẤU" required>
            </div>

            <input type="submit" value="Tạo lệnh rút tiền" class="btn" style="width: 100%; background: #27ae60; color: white;">
        </form>
    </div>

    <!-- CỘT PHẢI: LỊCH SỬ GIAO DỊCH -->
    <div class="card" style="flex: 2; min-width: 400px;">
        <h2>Lịch sử dòng tiền</h2>
        
        <?php if(empty($data['transactions'])) : ?>
            <p style="text-align: center; color: #7f8c8d; padding: 30px 0;">Chưa có giao dịch nào phát sinh.</p>
        <?php else : ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background-color: #f1f2f6; text-align: left;">
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Thời gian</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Loại GD</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Số tiền</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Nội dung</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['transactions'] as $trans) : ?>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-size: 14px;">
                                <?php echo date('d/m/Y H:i', strtotime($trans->created_at)); ?>
                            </td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-size: 14px;">
                                <?php 
                                    // 1:Deposit, 2:Withdraw, 3:Payment, 4:Refund, 5:Earning
                                    switch($trans->type) {
                                        case 1: echo '<span style="color:#3498db;">Nạp tiền</span>'; break;
                                        case 2: echo '<span style="color:#e74c3c;">Rút tiền</span>'; break;
                                        case 3: echo '<span style="color:#e67e22;">Thanh toán</span>'; break;
                                        case 4: echo '<span style="color:#9b59b6;">Hoàn tiền</span>'; break;
                                        case 5: echo '<span style="color:#2ecc71;">Doanh thu</span>'; break;
                                        default: echo 'Khác';
                                    }
                                ?>
                            </td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; <?php echo ($trans->amount > 0) ? 'color: #2ecc71;' : 'color: #e74c3c;'; ?>">
                                <?php echo ($trans->amount > 0 ? '+' : '') . number_format($trans->amount, 0, ',', '.') . ' đ'; ?>
                            </td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; color: #555;">
                                <?php echo $trans->description; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<!-- Gọi Footer -->
<?php require APPROOT . '/Views/inc/footer.php'; ?>
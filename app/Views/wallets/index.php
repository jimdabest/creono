<?php /** @var array $data */ ?>
<!-- Gọi Header -->
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="wallet-container" style="display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; max-width: 1200px; margin: 0 auto; padding: 20px 16px;">

    <!-- CỘT TRÁI: THÔNG TIN VÍ & FORM RÚT TIỀN -->
    <div class="card" style="flex: 1; min-width: 280px; background: #fff; border-radius: 20px; padding: 28px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
        <h2 style="font-size: 24px; font-weight: 700; color: var(--apple-black, #1d1d1f); margin-bottom: 20px;">Ví Điện Tử</h2>

        <!-- Hiển thị Số dư -->
        <div class="balance-box" style="background: var(--apple-gray-bg, #f5f5f7); padding: 24px 20px; border-radius: 16px; margin-bottom: 24px; text-align: center;">
            <p style="margin: 0; color: var(--apple-gray, #86868b); font-size: 14px; font-weight: 500;">Số dư khả dụng</p>
            <h3 style="margin: 8px 0; color: var(--apple-green, #34c759); font-size: 32px; font-weight: 700;">
                <?php echo number_format($data['wallet']->balance, 0, ',', '.'); ?> đ
            </h3>
            <p style="margin: 0; color: var(--apple-red, #ff3b30); font-size: 14px;">
                Đang đóng băng (chờ rút): <?php echo number_format($data['wallet']->frozen_balance, 0, ',', '.'); ?> đ
            </p>
        </div>

        <hr style="border: none; border-top: 1px solid rgba(0,0,0,0.08); margin: 24px 0;">

        <!-- Form Yêu cầu rút tiền -->
        <h3 style="font-size: 20px; font-weight: 600; color: var(--apple-black, #1d1d1f); margin-bottom: 16px;">Yêu cầu rút tiền</h3>
        <form action="<?php echo URLROOT; ?>/wallets/withdraw" method="POST">

            <?php if(!empty($data['bank_err'])) : ?>
                <div class="alert" style="color: var(--apple-red, #ff3b30); background: rgba(255,59,48,0.08); padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 14px; border-left: 4px solid var(--apple-red, #ff3b30);">
                    <?php echo $data['bank_err']; ?>
                </div>
            <?php endif; ?>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="amount" style="display: block; font-size: 14px; font-weight: 500; color: var(--apple-black, #1d1d1f); margin-bottom: 6px;">Số tiền cần rút (đ): *</label>
                <input type="number" name="amount" class="<?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" placeholder="Nhập số tiền..." required style="width: 100%; padding: 12px 14px; border: 1px solid rgba(0,0,0,0.12); border-radius: 12px; font-size: 15px; outline: none; transition: border-color 0.2s; box-sizing: border-box;">
                <?php if(!empty($data['amount_err'])) : ?>
                    <span class="error-text" style="color: var(--apple-red, #ff3b30); font-size: 13px; display: block; margin-top: 4px;"><?php echo $data['amount_err']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="bank_name" style="display: block; font-size: 14px; font-weight: 500; color: var(--apple-black, #1d1d1f); margin-bottom: 6px;">Ngân hàng: *</label>
                <input type="text" name="bank_name" placeholder="VD: Vietcombank, Techcombank..." required style="width: 100%; padding: 12px 14px; border: 1px solid rgba(0,0,0,0.12); border-radius: 12px; font-size: 15px; outline: none; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="bank_acc_num" style="display: block; font-size: 14px; font-weight: 500; color: var(--apple-black, #1d1d1f); margin-bottom: 6px;">Số tài khoản: *</label>
                <input type="text" name="bank_acc_num" placeholder="Nhập số tài khoản..." required style="width: 100%; padding: 12px 14px; border: 1px solid rgba(0,0,0,0.12); border-radius: 12px; font-size: 15px; outline: none; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="bank_acc_name" style="display: block; font-size: 14px; font-weight: 500; color: var(--apple-black, #1d1d1f); margin-bottom: 6px;">Tên chủ tài khoản: *</label>
                <input type="text" name="bank_acc_name" placeholder="VIẾT HOA KHÔNG DẤU" required style="width: 100%; padding: 12px 14px; border: 1px solid rgba(0,0,0,0.12); border-radius: 12px; font-size: 15px; outline: none; box-sizing: border-box; text-transform: uppercase;">
            </div>

            <input type="submit" value="Tạo lệnh rút tiền" class="btn" style="width: 100%; padding: 14px; background: var(--apple-blue, #0071e3); color: #fff; border: none; border-radius: 14px; font-size: 16px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
        </form>
    </div>

    <!-- CỘT PHẢI: LỊCH SỬ GIAO DỊCH -->
    <div class="card" style="flex: 2; min-width: 300px; background: #fff; border-radius: 20px; padding: 28px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 24px rgba(0,0,0,0.04);">
        <h2 style="font-size: 24px; font-weight: 700; color: var(--apple-black, #1d1d1f); margin-bottom: 20px;">Lịch sử dòng tiền</h2>

        <?php if(empty($data['transactions'])) : ?>
            <p style="text-align: center; color: var(--apple-gray, #86868b); padding: 40px 0; font-size: 15px;">Chưa có giao dịch nào phát sinh.</p>
        <?php else : ?>
            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 14px;">
                    <thead>
                        <tr style="background-color: var(--apple-gray-bg, #f5f5f7); text-align: left;">
                            <th style="padding: 12px 10px; border-bottom: 2px solid rgba(0,0,0,0.06); font-weight: 600; color: var(--apple-gray, #86868b);">Thời gian</th>
                            <th style="padding: 12px 10px; border-bottom: 2px solid rgba(0,0,0,0.06); font-weight: 600; color: var(--apple-gray, #86868b);">Loại GD</th>
                            <th style="padding: 12px 10px; border-bottom: 2px solid rgba(0,0,0,0.06); font-weight: 600; color: var(--apple-gray, #86868b);">Số tiền</th>
                            <th style="padding: 12px 10px; border-bottom: 2px solid rgba(0,0,0,0.06); font-weight: 600; color: var(--apple-gray, #86868b);">Nội dung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['transactions'] as $trans) : ?>
                            <tr>
                                <td style="padding: 12px 10px; border-bottom: 1px solid rgba(0,0,0,0.05); color: var(--apple-black, #1d1d1f); white-space: nowrap;">
                                    <?php echo date('d/m/Y H:i', strtotime($trans->created_at)); ?>
                                </td>
                                <td style="padding: 12px 10px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                    <?php 
                                        // 1:Deposit, 2:Withdraw, 3:Payment, 4:Refund, 5:Earning
                                        switch($trans->type) {
                                            case 1: echo '<span style="color: var(--apple-blue, #0071e3); font-weight: 500;">Nạp tiền</span>'; break;
                                            case 2: echo '<span style="color: var(--apple-red, #ff3b30); font-weight: 500;">Rút tiền</span>'; break;
                                            case 3: echo '<span style="color: var(--apple-orange, #ff9500); font-weight: 500;">Thanh toán</span>'; break;
                                            case 4: echo '<span style="color: #9b59b6; font-weight: 500;">Hoàn tiền</span>'; break;
                                            case 5: echo '<span style="color: var(--apple-green, #34c759); font-weight: 500;">Doanh thu</span>'; break;
                                            default: echo 'Khác';
                                        }
                                    ?>
                                </td>
                                <td style="padding: 12px 10px; border-bottom: 1px solid rgba(0,0,0,0.05); font-weight: 600; <?php echo ($trans->amount > 0) ? 'color: var(--apple-green, #34c759);' : 'color: var(--apple-red, #ff3b30);'; ?>">
                                    <?php echo ($trans->amount > 0 ? '+' : '') . number_format($trans->amount, 0, ',', '.') . ' đ'; ?>
                                </td>
                                <td style="padding: 12px 10px; border-bottom: 1px solid rgba(0,0,0,0.05); color: var(--apple-gray, #555);">
                                    <?php echo htmlspecialchars($trans->description); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- STYLES RESPONSIVE -->
<style>
/* Responsive cho trang ví điện tử */
@media (max-width: 768px) {
    .wallet-container {
        flex-direction: column !important;
        gap: 20px !important;
        padding: 16px 12px !important;
    }
    .wallet-container .card {
        min-width: 0 !important;
        width: 100% !important;
        padding: 20px !important;
    }
    .balance-box h3 {
        font-size: 28px !important;
    }
    .wallet-container h2 {
        font-size: 22px !important;
    }
    .wallet-container h3 {
        font-size: 18px !important;
    }
    table, thead, tbody, th, td, tr {
        display: block;
    }
    thead tr {
        display: none;
    }
    tr {
        margin-bottom: 16px;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 12px;
        padding: 12px;
        background: #fff;
    }
    td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0 !important;
        border-bottom: 1px solid rgba(0,0,0,0.04) !important;
        font-size: 14px;
    }
    td:last-child {
        border-bottom: none !important;
    }
    td:before {
        content: attr(data-label);
        font-weight: 600;
        color: var(--apple-gray, #86868b);
        margin-right: 12px;
        flex-shrink: 0;
    }
    /* Gán nhãn cho các cột */
    td:nth-of-type(1):before { content: "Thời gian"; }
    td:nth-of-type(2):before { content: "Loại GD"; }
    td:nth-of-type(3):before { content: "Số tiền"; }
    td:nth-of-type(4):before { content: "Nội dung"; }
    .table-responsive {
        overflow-x: visible !important;
    }
}

@media (max-width: 480px) {
    .wallet-container .card {
        padding: 16px !important;
        border-radius: 16px !important;
    }
    .balance-box {
        padding: 16px !important;
    }
    .balance-box h3 {
        font-size: 24px !important;
    }
    .wallet-container h2 {
        font-size: 20px !important;
    }
    .wallet-container h3 {
        font-size: 17px !important;
    }
    input[type="text"],
    input[type="number"] {
        font-size: 14px !important;
        padding: 10px 12px !important;
    }
    input[type="submit"] {
        font-size: 15px !important;
        padding: 12px !important;
    }
}
</style>

<!-- Gọi Footer -->
<?php require APPROOT . '/Views/inc/footer.php'; ?>
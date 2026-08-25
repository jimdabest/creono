<?php require APPROOT . '/Views/inc/header.php'; ?>
<?php /** @var array $data */ ?>
<div class="container" style="max-width: 480px; margin-top: 40px;">
    <div class="card">
        <h2>Nạp tiền vào ví</h2>
        <p class="subtitle">Nhập số tiền bạn muốn nạp (giả lập).</p>
        <form action="<?= URLROOT ?>/wallets/deposit" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token']) ?>">
            <div class="form-group">
                <label for="amount">Số tiền (VNĐ)</label>
                <input type="number" name="amount" id="amount" class="form-control" placeholder="Nhập số tiền" required min="1000" step="1000">
            </div>
            <button type="submit" class="btn btn-primary">Xác nhận nạp tiền</button>
            <a href="<?= URLROOT ?>/wallets/index" class="btn btn-secondary" style="display: block; margin-top: 8px;">Quay lại ví</a>
        </form>
    </div>
</div>
<?php require APPROOT . '/Views/inc/footer.php'; ?>
<?php

/** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Duyệt rút tiền</span>
            </nav>
            <h1 class="admin-title">Quản Lý Yêu Cầu Rút Tiền</h1>
            <p class="admin-subtitle">Xử lý các yêu cầu rút doanh thu về tài khoản ngân hàng của Người bán.</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-header flex-between">
            <h3>Danh sách chờ chuyển khoản (<?php echo count($data['requests']); ?>)</h3>
            <span class="badge badge-warning">Status: Pending</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Thời gian yêu cầu</th>
                        <th>Seller Email</th>
                        <th>Thông tin Ngân hàng</th>
                        <th>Số tiền (VNĐ)</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['requests'])) : ?>
                        <?php foreach ($data['requests'] as $req) : ?>
                            <tr>
                                <td><span class="text-muted">#<?php echo $req->request_id; ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($req->created_at)); ?></td>
                                <td><strong class="font-medium text-dark"><?php echo htmlspecialchars($req->seller_email); ?></strong></td>
                                <td>
                                    <div class="font-sm">
                                        NH: <strong><?php echo htmlspecialchars($req->bank_name); ?></strong><br>
                                        STK: <strong><?php echo htmlspecialchars($req->bank_account_number); ?></strong><br>
                                        Tên: <strong><?php echo htmlspecialchars($req->bank_account_name ?? 'Không xác định'); ?></strong> </div>
                                </td>
                                <td><strong class="text-danger"><?php echo number_format($req->amount, 0, ',', '.'); ?>đ</strong></td>

                                <td class="text-right action-buttons">
                                    <!-- Nút Duyệt -->
                                    <form action="<?php echo URLROOT; ?>/admin/processWithdrawal" method="POST" data-ajax="true" style="display: inline-block;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">
                                        <input type="hidden" name="request_id" value="<?php echo $req->request_id; ?>">
                                        <input type="hidden" name="action" value="APPROVE">
                                        <button type="submit" class="btn-action btn-action-edit" title="Đã chuyển khoản xong">
                                            Duyệt & Đã CK
                                        </button>
                                    </form>

                                    <!-- Nút Từ chối -->
                                    <form action="<?php echo URLROOT; ?>/admin/processWithdrawal" method="POST" data-ajax="true" style="display: inline-block;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">
                                        <input type="hidden" name="request_id" value="<?php echo $req->request_id; ?>">
                                        <input type="hidden" name="action" value="REJECT">
                                        <button type="submit" class="btn-action btn-action-delete" title="Từ chối và Hoàn tiền về ví">
                                            Từ chối
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tất cả yêu cầu rút tiền đã được xử lý xong!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
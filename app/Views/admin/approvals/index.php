<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Duyệt sản phẩm</span>
            </nav>
            <h1 class="admin-title">Hệ Thống Duyệt Sản Phẩm</h1>
            <p class="admin-subtitle">Kiểm duyệt và đánh giá chất lượng tài liệu số trước khi đăng công khai lên chợ</p>
        </div>
        <div>
            <span class="badge badge-primary font-medium" style="font-size: 15px; padding: 8px 16px;">
                Đang chờ duyệt: <strong><?php echo count($data['pending_products']); ?></strong>
            </span>
        </div>
    </div>

    <!-- Bảng sản phẩm đang chờ duyệt -->
    <div class="admin-card mb-5">
        <div class="card-header flex-between">
            <h3>Danh sách sản phẩm chờ kiểm duyệt</h3>
            <span class="badge badge-light">Status: Pending</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Sản phẩm / Tiêu đề</th>
                        <th>Cửa hàng</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Nhãn AI & Điểm</th>
                        <th>Ngày tạo</th>
                        <th style="width: 220px;" class="text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['pending_products'])) : ?>
                        <?php foreach ($data['pending_products'] as $prod) : ?>
                            <tr>
                                <td><span class="text-muted">#<?php echo $prod->id; ?></span></td>
                                <td>
                                    <strong class="font-medium text-dark"><?php echo htmlspecialchars($prod->title); ?></strong>
                                    <?php if (!empty($prod->file_url)) : ?>
                                        <br><a href="<?php echo htmlspecialchars($prod->file_url); ?>" target="_blank" class="font-sm" style="color: var(--apple-blue);">&darr; Tải file kiểm tra</a>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-secondary"><?php echo htmlspecialchars($prod->store_name); ?></span></td>
                                <td><span class="badge badge-light"><?php echo htmlspecialchars($prod->category_name ?? 'Chưa phân loại'); ?></span></td>
                                <td><strong class="text-green"><?php echo number_format($prod->price, 0, ',', '.'); ?>đ</strong></td>
                                <td>
                                    <?php 
                                        $aiScore = (float)($prod->ai_score ?? 0);
                                        $aiLabel = $prod->ai_label_name ?? 'Chưa quét';
                                        $badgeClass = ($aiScore > 50) ? 'badge-danger' : (($aiScore > 20) ? 'badge-warning' : 'badge-success');
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($aiLabel); ?> (<?php echo number_format($aiScore, 1); ?>%)
                                    </span>
                                </td>
                                <td class="font-sm text-muted"><?php echo date('d/m/Y H:i', strtotime($prod->created_at)); ?></td>
                                <td class="text-right action-buttons">
                                    <!-- Form Duyệt (Approve) -->
                                    <form action="<?php echo URLROOT; ?>/admin/approveProduct/<?php echo $prod->id; ?>" method="POST" style="display: inline-block;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <button type="submit" class="btn-action btn-action-edit" title="Phê duyệt sản phẩm">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            Duyệt
                                        </button>
                                    </form>

                                    <!-- Nút Từ chối (Bật Reject Details) -->
                                    <button type="button" class="btn-action btn-action-delete" onclick="toggleRejectForm(<?php echo $prod->id; ?>)" title="Từ chối sản phẩm">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                        Từ chối
                                    </button>
                                </td>
                            </tr>
                            <!-- Hidden Row Form nhập lý do từ chối -->
                            <tr id="reject-row-<?php echo $prod->id; ?>" style="display: none; background: #fff5f5;">
                                <td colspan="8" class="p-3">
                                    <form action="<?php echo URLROOT; ?>/admin/rejectProduct/<?php echo $prod->id; ?>" method="POST" class="flex-between gap-2">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="text" name="note" class="form-control" placeholder="Nhập lý do từ chối đăng tải sản phẩm này..." required style="max-width: 80%;">
                                        <div>
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 8px 16px;">Xác nhận Từ chối</button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleRejectForm(<?php echo $prod->id; ?>)" style="padding: 8px 16px;">Hủy</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                ✨ Không có sản phẩm nào đang chờ kiểm duyệt!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bảng Lịch sử phê duyệt gần đây -->
    <div class="admin-card">
        <div class="card-header flex-between">
            <h3>Lịch sử phê duyệt sản phẩm gần đây</h3>
            <span class="badge badge-light">Audit Log</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Người kiểm duyệt</th>
                        <th>Hành động</th>
                        <th>Ghi chú / Lý do</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['recent_approvals'])) : ?>
                        <?php foreach ($data['recent_approvals'] as $log) : ?>
                            <tr>
                                <td><strong class="font-medium text-dark"><?php echo htmlspecialchars($log->product_title); ?></strong></td>
                                <td><?php echo htmlspecialchars($log->censor_name); ?> <span class="text-muted font-sm">(<?php echo htmlspecialchars($log->censor_email); ?>)</span></td>
                                <td>
                                    <?php if ($log->action === 'APPROVE') : ?>
                                        <span class="badge badge-success">✓ APPROVE</span>
                                    <?php else : ?>
                                        <span class="badge badge-danger">&times; REJECT</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-sm text-muted"><?php echo htmlspecialchars($log->note ?? 'Không có'); ?></td>
                                <td class="font-sm text-muted"><?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Chưa có dữ liệu lịch sử phê duyệt</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleRejectForm(id) {
    var row = document.getElementById('reject-row-' + id);
    if (row.style.display === 'none' || row.style.display === '') {
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

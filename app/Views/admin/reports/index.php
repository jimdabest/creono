<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Báo cáo & Khiếu nại</span>
            </nav>
            <h1 class="admin-title">Quản Lý Báo Cáo Vi Phạm & Khiếu Nại AI</h1>
            <p class="admin-subtitle">Xử lý các báo cáo vi phạm nội dung từ người dùng và khiếu nại nhãn AI từ người bán</p>
        </div>
    </div>

    <!-- Tab Navigation Button Group -->
    <div class="admin-tabs mb-4">
        <button type="button" class="tab-btn active" onclick="switchTab('reports-tab', this)">
            🚩 Báo cáo vi phạm (<?php echo count($data['reports']); ?>)
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('appeals-tab', this)">
            🤖 Khiếu nại nhãn AI (<?php echo count($data['appeals']); ?>)
        </button>
    </div>

    <!-- TAB 1: BÁO CÁO VI PHẠM TỪ NGƯỜI DÙNG -->
    <div id="reports-tab" class="tab-content active">
        <div class="admin-card">
            <div class="card-header flex-between">
                <h3>Danh sách Báo cáo vi phạm từ người dùng</h3>
                <span class="badge badge-light">User Reports</span>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Người báo cáo</th>
                            <th>Loại & Đối tượng</th>
                            <th>Lý do vi phạm</th>
                            <th>Chi tiết</th>
                            <th>Trạng thái</th>
                            <th>Ngày báo cáo</th>
                            <th style="width: 200px;" class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['reports'])) : ?>
                            <?php foreach ($data['reports'] as $rep) : ?>
                                <tr>
                                    <td><span class="text-muted">#<?php echo $rep->id; ?></span></td>
                                    <td>
                                        <strong class="font-medium text-dark"><?php echo htmlspecialchars($rep->reporter_name); ?></strong><br>
                                        <span class="font-sm text-muted"><?php echo htmlspecialchars($rep->reporter_email); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($rep->target_type); ?></span><br>
                                        <strong class="font-sm text-dark"><?php echo htmlspecialchars($rep->target_title ?? ('#' . $rep->target_id)); ?></strong>
                                    </td>
                                    <td><strong class="text-danger"><?php echo htmlspecialchars($rep->reason); ?></strong></td>
                                    <td class="font-sm text-muted"><?php echo !empty($rep->details) ? htmlspecialchars($rep->details) : '<em>Không có chi tiết</em>'; ?></td>
                                    <td>
                                        <?php 
                                            $st = (int)$rep->status;
                                            if ($st === 1) echo '<span class="badge badge-warning">⏳ Chờ xử lý</span>';
                                            elseif ($st === 2) echo '<span class="badge badge-primary">🔍 Đang điều tra</span>';
                                            elseif ($st === 3) echo '<span class="badge badge-success">✓ Đã giải quyết</span>';
                                            elseif ($st === 4) echo '<span class="badge badge-light">&times; Đã bác bỏ</span>';
                                        ?>
                                        <?php if (!empty($rep->resolver_name)) : ?>
                                            <br><span class="font-sm text-muted">Bởi: <?php echo htmlspecialchars($rep->resolver_name); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-sm text-muted"><?php echo date('d/m/Y H:i', strtotime($rep->created_at)); ?></td>
                                    <td class="text-right action-buttons">
                                        <?php if ($st === 1 || $st === 2) : ?>
                                            <!-- Form Chấp nhận & Khóa (Resolve) -->
                                            <form action="<?php echo URLROOT; ?>/admin/resolveReport/<?php echo $rep->id; ?>" method="POST" style="display: inline-block;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                <input type="hidden" name="action" value="resolve">
                                                <button type="submit" class="btn-action btn-action-edit" title="Chấp nhận báo cáo & Khóa sản phẩm vi phạm" onclick="return confirm('Xác nhận CHẤP NHẬN báo cáo vi phạm này và áp dụng hình phạt đối với sản phẩm/đối tượng?');">
                                                    Giải quyết
                                                </button>
                                            </form>

                                            <!-- Form Bác bỏ (Dismiss) -->
                                            <form action="<?php echo URLROOT; ?>/admin/resolveReport/<?php echo $rep->id; ?>" method="POST" style="display: inline-block;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                <input type="hidden" name="action" value="dismiss">
                                                <button type="submit" class="btn-action btn-action-delete" title="Bác bỏ báo cáo này" onclick="return confirm('Xác nhận BÁC BỎ báo cáo vi phạm này?');">
                                                    Bác bỏ
                                                </button>
                                            </form>
                                        <?php else : ?>
                                            <span class="text-muted font-sm">Đã hoàn tất</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Chưa có báo cáo vi phạm nào!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: KHIẾU NẠI NHÃN AI TỪ SELLER -->
    <div id="appeals-tab" class="tab-content">
        <div class="admin-card">
            <div class="card-header flex-between">
                <h3>Danh sách Khiếu nại dán nhãn AI từ Người bán</h3>
                <span class="badge badge-light">AI Label Appeals</span>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Sản phẩm khiếu nại</th>
                            <th>Người bán (Seller)</th>
                            <th>Lý do khiếu nại</th>
                            <th>Bằng chứng</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th style="width: 200px;" class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['appeals'])) : ?>
                            <?php foreach ($data['appeals'] as $app) : ?>
                                <tr>
                                    <td><span class="text-muted">#<?php echo $app->id; ?></span></td>
                                    <td>
                                        <strong class="font-medium text-dark"><?php echo htmlspecialchars($app->product_title); ?></strong><br>
                                        <span class="font-sm text-muted">Giá: <?php echo number_format($app->product_price, 0, ',', '.'); ?>đ</span>
                                    </td>
                                    <td>
                                        <strong class="font-medium text-dark"><?php echo htmlspecialchars($app->seller_name); ?></strong><br>
                                        <span class="font-sm text-muted"><?php echo htmlspecialchars($app->seller_email); ?></span>
                                    </td>
                                    <td class="font-sm"><?php echo htmlspecialchars($app->reason); ?></td>
                                    <td>
                                        <?php if (!empty($app->evidence_url)) : ?>
                                            <a href="<?php echo htmlspecialchars($app->evidence_url); ?>" target="_blank" class="font-sm" style="color: var(--apple-blue);">&darr; Xem bằng chứng</a>
                                        <?php else : ?>
                                            <span class="text-muted font-sm">Không đính kèm</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $ast = (int)$app->status;
                                            if ($ast === 1) echo '<span class="badge badge-warning">⏳ Chờ duyệt</span>';
                                            elseif ($ast === 2) echo '<span class="badge badge-success">✓ Chấp nhận khiếu nại</span>';
                                            elseif ($ast === 3) echo '<span class="badge badge-danger">&times; Từ chối khiếu nại</span>';
                                        ?>
                                        <?php if (!empty($app->processor_name)) : ?>
                                            <br><span class="font-sm text-muted">Bởi: <?php echo htmlspecialchars($app->processor_name); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-sm text-muted"><?php echo date('d/m/Y H:i', strtotime($app->created_at)); ?></td>
                                    <td class="text-right action-buttons">
                                        <?php if ($ast === 1) : ?>
                                            <!-- Form Chấp nhận khiếu nại AI -->
                                            <form action="<?php echo URLROOT; ?>/admin/processAppeal/<?php echo $app->id; ?>" method="POST" style="display: inline-block;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn-action btn-action-edit" title="Chấp nhận khiếu nại & Khôi phục sản phẩm" onclick="return confirm('Xác nhận CHẤP NHẬN khiếu nại và khôi phục hiển thị cho sản phẩm này?');">
                                                    Chấp nhận
                                                </button>
                                            </form>

                                            <!-- Form Từ chối khiếu nại AI -->
                                            <form action="<?php echo URLROOT; ?>/admin/processAppeal/<?php echo $app->id; ?>" method="POST" style="display: inline-block;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn-action btn-action-delete" title="Từ chối khiếu nại" onclick="return confirm('Xác nhận TỪ CHỐI khiếu nại nhãn AI này?');">
                                                    Từ chối
                                                </button>
                                            </form>
                                        <?php else : ?>
                                            <span class="text-muted font-sm">Đã hoàn tất</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Chưa có khiếu nại nhãn AI nào!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(function(el) {
        el.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(function(el) {
        el.classList.remove('active');
    });
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

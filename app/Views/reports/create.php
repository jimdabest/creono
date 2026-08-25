<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container report-page">
    <div class="report-header">
        <h1 class="report-title">Báo cáo vi phạm</h1>
        <p class="report-subtitle">Giúp chúng tôi duy trì một cộng đồng an toàn và chất lượng.</p>
    </div>

    <div class="report-card">
        <!-- Thông tin đối tượng bị báo cáo -->
        <div class="report-target-info">
            <span class="report-target-badge">Đối tượng bị báo cáo</span>
            <div class="report-target-detail">
                <span class="target-type-badge"><?php echo htmlspecialchars($data['target_type']); ?></span>
                <span class="target-name">
                    <?php
                    $info = $data['target_info'];
                    switch ($data['target_type']) {
                        case 'PRODUCT':
                            echo htmlspecialchars($info->title ?? 'Sản phẩm');
                            break;
                        case 'STORE':
                            echo htmlspecialchars($info->name ?? 'Cửa hàng');
                            break;
                        case 'USER':
                            echo htmlspecialchars($info->name ?? 'Người dùng');
                            break;
                        case 'REVIEW':
                            echo 'Đánh giá #' . $data['target_id'];
                            break;
                        default:
                            echo '#' . $data['target_id'];
                    }
                    ?>
                </span>
                <span class="target-id">ID: #<?php echo $data['target_id']; ?></span>
            </div>
        </div>

        <!-- Form báo cáo -->
        <form action="<?php echo URLROOT; ?>/reports/store" method="POST" class="report-form" data-ajax="true">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">
            <input type="hidden" name="target_type" value="<?php echo htmlspecialchars($data['target_type']); ?>">
            <input type="hidden" name="target_id" value="<?php echo (int)$data['target_id']; ?>">

            <!-- Lý do báo cáo -->
            <div class="form-group">
                <label for="reason">Lý do báo cáo <span class="text-danger">*</span></label>
                <select name="reason" id="reason" class="form-control" required>
                    <option value="">-- Chọn lý do --</option>
                    <option value="Vi phạm bản quyền">Vi phạm bản quyền</option>
                    <option value="Chứa mã độc hoặc phần mềm độc hại">Chứa mã độc hoặc phần mềm độc hại</option>
                    <option value="Nội dung lừa đảo, giả mạo">Nội dung lừa đảo, giả mạo</option>
                    <option value="Nội dung không phù hợp, phản cảm">Nội dung không phù hợp, phản cảm</option>
                    <option value="Sản phẩm không đúng mô tả">Sản phẩm không đúng mô tả</option>
                    <option value="Vi phạm điều khoản sử dụng">Vi phạm điều khoản sử dụng</option>
                    <option value="Người bán gian lận">Người bán gian lận</option>
                    <option value="Khác">Khác (vui lòng mô tả chi tiết)</option>
                </select>
                <span class="error-text" id="reason_err"></span>
            </div>

            <!-- Chi tiết báo cáo -->
            <div class="form-group">
                <label for="details">Mô tả chi tiết <span class="text-danger">*</span></label>
                <textarea name="details" id="details" class="form-control" rows="5" placeholder="Vui lòng mô tả cụ thể hành vi vi phạm, cung cấp bằng chứng nếu có..." required></textarea>
                <span class="error-text" id="details_err"></span>
                <span class="form-hint">Tối đa 1000 ký tự.</span>
            </div>

            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit-report">Gửi báo cáo</button>
                <a href="<?php echo URLROOT; ?>/products/index" class="btn btn-secondary btn-cancel-report">Hủy</a>
            </div>
        </form>

        <div class="report-footer-note">
            <p>Báo cáo của bạn sẽ được xem xét một cách nghiêm túc. Chúng tôi cam kết bảo mật thông tin người báo cáo.</p>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
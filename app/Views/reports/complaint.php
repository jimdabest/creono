<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container report-page">
    <div class="report-header">
        <h1 class="report-title">Khiếu nại</h1>
        <p class="report-subtitle">Gửi khiếu nại về sản phẩm, dịch vụ hoặc người dùng vi phạm. Chúng tôi cam kết xử lý công bằng.</p>
    </div>

    <div class="report-card">
        <!-- Thông tin đối tượng bị khiếu nại -->
        <div class="report-target-info">
            <span class="report-target-badge">Đối tượng bị khiếu nại</span>
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

        <!-- Form Khiếu nại -->
        <form action="<?php echo URLROOT; ?>/reports/store" method="POST" class="report-form" data-ajax="true">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">
            <input type="hidden" name="target_type" value="<?php echo htmlspecialchars($data['target_type']); ?>">
            <input type="hidden" name="target_id" value="<?php echo (int)$data['target_id']; ?>">
            <input type="hidden" name="report_type" value="COMPLAINT">

            <!-- Lý do khiếu nại -->
            <div class="form-group">
                <label for="reason">Lý do khiếu nại <span class="text-danger">*</span></label>
                <select name="reason" id="reason" class="form-control" required>
                    <option value="">-- Chọn lý do khiếu nại --</option>
                    <option value="Sản phẩm không đúng mô tả">Sản phẩm không đúng mô tả</option>
                    <option value="Lỗi tải xuống / Không thể sử dụng">Lỗi tải xuống / Không thể sử dụng</option>
                    <option value="Người bán gian lận">Người bán gian lận</option>
                    <option value="Thanh toán nhưng không nhận được sản phẩm">Thanh toán nhưng không nhận được sản phẩm</option>
                    <option value="Chất lượng sản phẩm quá kém">Chất lượng sản phẩm quá kém</option>
                    <option value="Nội dung không phù hợp, phản cảm">Nội dung không phù hợp, phản cảm</option>
                    <option value="Vi phạm điều khoản sử dụng">Vi phạm điều khoản sử dụng</option>
                    <option value="Khác">Khác (vui lòng mô tả chi tiết)</option>
                </select>
                <span class="error-text" id="reason_err"></span>
            </div>

            <!-- Chi tiết khiếu nại -->
            <div class="form-group">
                <label for="details">Mô tả chi tiết <span class="text-danger">*</span></label>
                <textarea name="details" id="details" class="form-control" rows="5" placeholder="Vui lòng mô tả cụ thể vấn đề bạn gặp phải, cung cấp bằng chứng nếu có (mã đơn hàng, ảnh chụp màn hình, v.v.)..." required></textarea>
                <span class="error-text" id="details_err"></span>
                <span class="form-hint">Tối đa 1000 ký tự.</span>
            </div>

            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit-report">Gửi khiếu nại</button>
                <a href="<?php echo URLROOT; ?>/products/index" class="btn btn-secondary btn-cancel-report">Hủy</a>
            </div>
        </form>

        <div class="report-footer-note">
            <p>Khiếu nại của bạn sẽ được xem xét trong vòng 24-48 giờ. Chúng tôi cam kết bảo mật thông tin người khiếu nại.</p>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

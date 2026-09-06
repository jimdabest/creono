<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container report-page">
    <div class="report-header">
        <h1 class="report-title">Tố cáo đạo nhái</h1>
        <p class="report-subtitle">Báo cáo hành vi sao chép, đạo nhái nội dung hoặc vi phạm bản quyền. Chúng tôi bảo vệ quyền lợi sáng tạo của bạn.</p>
    </div>

    <div class="report-card">
        <!-- Thông tin đối tượng bị tố cáo -->
        <div class="report-target-info">
            <span class="report-target-badge" style="background: rgba(255, 59, 48, 0.08); color: #ff3b30;">Đối tượng bị tố cáo đạo nhái</span>
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

        <!-- Form Tố cáo đạo nhái -->
        <form action="<?php echo URLROOT; ?>/reports/store" method="POST" class="report-form" data-ajax="true">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">
            <input type="hidden" name="target_type" value="<?php echo htmlspecialchars($data['target_type']); ?>">
            <input type="hidden" name="target_id" value="<?php echo (int)$data['target_id']; ?>">
            <input type="hidden" name="report_type" value="PLAGIARISM">

            <!-- Lý do tố cáo -->
            <div class="form-group">
                <label for="reason">Lý do tố cáo <span class="text-danger">*</span></label>
                <select name="reason" id="reason" class="form-control" required>
                    <option value="">-- Chọn lý do tố cáo --</option>
                    <option value="Vi phạm bản quyền / Sao chép nội dung">Vi phạm bản quyền / Sao chép nội dung</option>
                    <option value="Sử dụng trái phép tài liệu của tôi">Sử dụng trái phép tài liệu của tôi</option>
                    <option value="Đạo nhái giao diện / thiết kế">Đạo nhái giao diện / thiết kế</option>
                    <option value="Nội dung lấy từ nguồn công khai không ghi nguồn">Nội dung lấy từ nguồn công khai không ghi nguồn</option>
                    <option value="Bán lại sản phẩm của người khác">Bán lại sản phẩm của người khác</option>
                    <option value="Sử dụng AI tạo nội dung giả mạo công sức">Sử dụng AI tạo nội dung giả mạo công sức</option>
                    <option value="Khác">Khác (vui lòng mô tả chi tiết)</option>
                </select>
                <span class="error-text" id="reason_err"></span>
            </div>

            <!-- Chi tiết tố cáo -->
            <div class="form-group">
                <label for="details">Mô tả chi tiết <span class="text-danger">*</span></label>
                <textarea name="details" id="details" class="form-control" rows="5" placeholder="Vui lòng mô tả cụ thể hành vi đạo nhái: nội dung nào bị sao chép, nguồn gốc tài liệu gốc, thời gian xuất bản. Bạn có thể đính kèm link bằng chứng (Google Drive, Dropbox, link sản phẩm gốc) ngay trong mô tả." required></textarea>
                <span class="error-text" id="details_err"></span>
                <span class="form-hint">Tối đa 1000 ký tự. Cung cấp link bằng chứng sẽ giúp chúng tôi xử lý nhanh hơn.</span>
            </div>

            <!-- Submit -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit-report" style="background: #ff3b30;">Gửi tố cáo đạo nhái</button>
                <a href="<?php echo URLROOT; ?>/products/index" class="btn btn-secondary btn-cancel-report">Hủy</a>
            </div>
        </form>

        <div class="report-footer-note">
            <p>Tố cáo đạo nhái sẽ được đội ngũ kiểm duyệt xem xét nghiêm túc. Nếu được xác nhận, sản phẩm vi phạm sẽ bị gỡ bỏ và người bán có thể bị xử lý. Chúng tôi cam kết bảo mật thông tin người tố cáo.</p>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

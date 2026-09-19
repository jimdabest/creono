<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- Gọi file CSS mới thêm -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/admin-products.css?v=<?php echo time(); ?>">

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px; max-width: 1000px;">
    
    <div class="seller-manage-header">
        <h1>Quản lý sản phẩm</h1>
        <a href="<?php echo URLROOT; ?>/products/create" class="btn btn-primary" style="padding: 10px 24px; border-radius: 12px; width: auto;">Thêm sản phẩm mới</a>
    </div>

    <?php displayFlash('success'); displayFlash('error'); ?>

    <?php if (empty($data['products'])): ?>
        <div class="empty-state" style="padding: 60px 24px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
            <h3>Chưa có sản phẩm nào</h3>
            <p style="color: #86868b;">Bắt đầu đăng tải sản phẩm đầu tiên của bạn.</p>
            <a href="<?php echo URLROOT; ?>/products/create" class="btn btn-primary mt-2" style="width: auto;">Đăng sản phẩm mới</a>
        </div>
    <?php else: ?>
        <div class="seller-table-wrapper">
            <table class="seller-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Sản phẩm / Tiêu đề</th>
                        <th style="width: 15%;">Giá bán</th>
                        <th style="width: 20%;">Trạng thái & Nhãn</th>
                        <th style="width: 10%; text-align: center;">Lượt tải</th>
                        <th style="width: 20%; text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['products'] as $product): ?>
                        <tr style="transition: background 0.2s;" onmouseover="this.style.background='#f9f9fb'" onmouseout="this.style.background='transparent'">
                            <td>
                                <strong style="display: block; font-size: 15px; color: var(--apple-black); margin-bottom: 4px;"><?php echo htmlspecialchars($product->title); ?></strong>
                                <span style="font-size: 13px; color: #86868b;"><?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại'); ?></span>
                            </td>
                            
                            <td>
                                <strong style="color: var(--apple-green); font-size: 15px;"><?php echo number_format($product->price, 0, ',', '.'); ?>đ</strong>
                            </td>
                            
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-start;">
                                    <?php
                                    $statusMap = [
                                        1 => '<span class="badge-pill orange">Chờ duyệt</span>',
                                        2 => '<span class="badge-pill green">Đang bán</span>',
                                        3 => '<span class="badge-pill red">Bị từ chối</span>'
                                    ];
                                    echo $statusMap[$product->status] ?? '<span class="badge-pill">Không xác định</span>';
                                    ?>
                                    
                                    <!-- NẾU CÓ NHÃN AI = 2 -->
                                    <?php if (isset($product->ai_label_id) && $product->ai_label_id == 2): ?>
                                        <span class="badge-pill red" style="background: var(--apple-red); color: white;">🤖 AI Generated</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td style="text-align: center; font-weight: 600; color: #495057;">
                                <?php echo $product->download_count ?? 0; ?>
                            </td>
                            
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                                    <a href="<?php echo URLROOT; ?>/products/edit/<?php echo $product->id; ?>" class="seller-action-btn btn-sm-edit">Sửa</a>
                                    
                                    <form action="<?php echo URLROOT; ?>/products/delete/<?php echo $product->id; ?>" method="POST" style="margin: 0; display: inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">
                                        <button type="submit" class="seller-action-btn btn-sm-delete">Xóa</button>
                                    </form>

                                    <!-- NÚT KHÁNG CÁO AI (Bật Modal) -->
                                    <?php if (isset($product->ai_label_id) && $product->ai_label_id == 2): ?>
                                        <button type="button" class="seller-action-btn btn-sm-appeal" onclick="openAppealModal('modal-appeal-<?php echo $product->id; ?>')">
                                            Kháng cáo AI
                                        </button>
                                        
                                        <!-- MODAL KHÁNG CÁO GIAO DIỆN NATIVE -->
                                        <div id="modal-appeal-<?php echo $product->id; ?>" class="native-modal-overlay">
                                            <div class="native-modal-box">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                                    <h3 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--apple-black);">Kháng cáo nhãn AI</h3>
                                                    <button type="button" onclick="closeAppealModal('modal-appeal-<?php echo $product->id; ?>')" style="background: none; border: none; font-size: 24px; color: #86868b; cursor: pointer;">&times;</button>
                                                </div>
                                                
                                                <p style="font-size: 14px; color: #666; text-align: left; margin-bottom: 20px; line-height: 1.5;">
                                                    Nếu bạn chứng minh được <strong><?php echo htmlspecialchars($product->title); ?></strong> là do con người tạo ra 100%, Admin sẽ gỡ nhãn AI cho sản phẩm.
                                                </p>
                                                
                                                <form action="<?php echo URLROOT; ?>/seller/submitAppeal" method="POST" style="text-align: left;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                                                    
                                                    <div class="form-group mb-3">
                                                        <label class="form-label" style="font-weight: 600;">Lý do / Lời giải thích <span style="color: var(--apple-red);">*</span></label>
                                                        <textarea name="reason" class="form-control" rows="3" required placeholder="Giải thích quá trình bạn tạo ra tài liệu này..."></textarea>
                                                    </div>
                                                    <div class="form-group mb-4">
                                                        <label class="form-label" style="font-weight: 600;">Link bằng chứng</label>
                                                        <input type="url" name="evidence_url" class="form-control" placeholder="Google Drive, Figma... (Vui lòng mở quyền truy cập)">
                                                    </div>
                                                    
                                                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                                                        <button type="button" class="btn btn-secondary" style="padding: 10px 20px; border-radius: 12px;" onclick="closeAppealModal('modal-appeal-<?php echo $product->id; ?>')">Hủy bỏ</button>
                                                        <button type="submit" class="btn btn-primary" style="padding: 10px 20px; border-radius: 12px; background: var(--apple-blue); border: none;">Gửi kháng cáo</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <!-- END MODAL -->
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    // Xử lý bật tắt Modal bằng JS Thuần
    function openAppealModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeAppealModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 200);
    }

    // Đóng modal khi click nền đen
    window.onclick = function(event) {
        if (event.target.classList.contains('native-modal-overlay')) {
            event.target.classList.remove('active');
            setTimeout(() => event.target.style.display = 'none', 200);
        }
    }
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
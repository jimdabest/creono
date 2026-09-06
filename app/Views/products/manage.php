<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px; max-width: 1000px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 style="font-size: 28px; font-weight: 700;">Quản lý sản phẩm</h1>
        <a href="<?php echo URLROOT; ?>/products/create" class="btn btn-primary" style="width: auto; padding: 10px 24px; border-radius: 12px;">Thêm sản phẩm</a>
    </div>

    <?php if (empty($data['products'])): ?>
        <div class="empty-state" style="padding: 60px 24px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
            <h3>Chưa có sản phẩm nào</h3>
            <p style="color: #86868b;">Bắt đầu đăng tải sản phẩm đầu tiên của bạn.</p>
            <a href="<?php echo URLROOT; ?>/products/create" class="btn btn-primary" style="width: auto; margin-top: 16px;">Đăng sản phẩm mới</a>
        </div>
    <?php else: ?>
        <div class="table-responsive" style="background: #fff; border-radius: 16px; border: 1px solid rgba(0,0,0,0.08); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead style="background: #f5f5f7;">
                    <tr>
                        <th style="padding: 14px 16px; text-align: left; font-weight: 600; color: #86868b;">ID</th>
                        <th style="padding: 14px 16px; text-align: left; font-weight: 600; color: #86868b;">Tiêu đề</th>
                        <th style="padding: 14px 16px; text-align: left; font-weight: 600; color: #86868b;">Giá</th>
                        <th style="padding: 14px 16px; text-align: left; font-weight: 600; color: #86868b;">Trạng thái</th>
                        <th style="padding: 14px 16px; text-align: left; font-weight: 600; color: #86868b;">Ngày tạo</th>
                        <th style="padding: 14px 16px; text-align: right; font-weight: 600; color: #86868b;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['products'] as $product): ?>
                        <tr style="border-bottom: 1px solid rgba(0,0,0,0.04);">
                            <td style="padding: 14px 16px;">#<?php echo $product->id; ?></td>
                            <td style="padding: 14px 16px; font-weight: 500;"><?php echo htmlspecialchars($product->title); ?></td>
                            <td style="padding: 14px 16px;"><?php echo number_format($product->price, 0, ',', '.'); ?>đ</td>
                            <td style="padding: 14px 16px;">
                                <?php
                                $statusMap = [
                                    1 => '<span class="badge badge-warning">Chờ duyệt</span>',
                                    2 => '<span class="badge badge-success">Đã duyệt</span>',
                                    3 => '<span class="badge badge-danger">Từ chối</span>'
                                ];
                                echo $statusMap[$product->status] ?? '<span class="badge badge-light">Không xác định</span>';
                                ?>
                            </td>
                            <td style="padding: 14px 16px; color: #86868b;"><?php echo date('d/m/Y', strtotime($product->created_at)); ?></td>
                            <td style="padding: 14px 16px; text-align: right;">
                                <a href="<?php echo URLROOT; ?>/products/edit/<?php echo $product->id; ?>" class="btn-action btn-action-edit" style="margin-right: 8px;">Sửa</a>
                                <form action="<?php echo URLROOT; ?>/products/delete/<?php echo $product->id; ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">
                                    <button type="submit" class="btn-action btn-action-delete" style="background: none; border: none; cursor: pointer; font-size: 13px; padding: 6px 12px; border-radius: 8px; background: rgba(255,59,48,0.08); color: #ff3b30;">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container" style="margin-top: 30px;">
    <div class="card" style="max-width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Dashboard Người bán</h2>
            <a href="#" class="btn" style="width: auto; background: #28a745;">+ Thêm sản phẩm</a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <h3>Sản phẩm</h3>
                <p style="font-size: 24px; font-weight: bold;">0</p>
            </div>
            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <h3>Doanh thu</h3>
                <p style="font-size: 24px; font-weight: bold;">0đ</p>
            </div>
            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <h3>Đánh giá</h3>
                <p style="font-size: 24px; font-weight: bold;">⭐ 0.0</p>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <h3>Danh sách sản phẩm</h3>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; color: #999;">
                Chưa có sản phẩm nào. Bắt đầu đăng bán ngay!
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
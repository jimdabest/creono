<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container" style="margin-top: 30px;">
    <div class="card" style="max-width: 100%;">
        <h2>Dashboard Quản trị</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <h3>Users</h3>
                <p style="font-size: 24px; font-weight: bold;">0</p>
            </div>
            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <h3>Products</h3>
                <p style="font-size: 24px; font-weight: bold;">0</p>
            </div>
            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <h3>Orders</h3>
                <p style="font-size: 24px; font-weight: bold;">0</p>
            </div>
            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <h3>Revenue</h3>
                <p style="font-size: 24px; font-weight: bold;">0đ</p>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <h3>Chức năng quản trị</h3>
            <ul style="list-style: none; padding: 0;">
                <li><a href="#" style="display: block; padding: 10px; background: #f8f9fa; margin: 5px 0; border-radius: 4px;">📋 Quản lý người dùng</a></li>
                <li><a href="#" style="display: block; padding: 10px; background: #f8f9fa; margin: 5px 0; border-radius: 4px;">📦 Quản lý sản phẩm</a></li>
                <li><a href="#" style="display: block; padding: 10px; background: #f8f9fa; margin: 5px 0; border-radius: 4px;">💰 Quản lý rút tiền</a></li>
                <li><a href="#" style="display: block; padding: 10px; background: #f8f9fa; margin: 5px 0; border-radius: 4px;">🔍 Duyệt tài liệu</a></li>
            </ul>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
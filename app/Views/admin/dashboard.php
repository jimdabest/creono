<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4">
    <div class="card dashboard-card">
        <h2>Dashboard Quản trị</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Users</h3>
                <p class="stat-value">0</p>
            </div>
            <div class="stat-card">
                <h3>Products</h3>
                <p class="stat-value">0</p>
            </div>
            <div class="stat-card">
                <h3>Orders</h3>
                <p class="stat-value">0</p>
            </div>
            <div class="stat-card">
                <h3>Revenue</h3>
                <p class="stat-value">0đ</p>
            </div>
        </div>
        
        <div class="dashboard-section">
            <h3>Chức năng quản trị</h3>
            <ul class="action-list">
                <li><a href="#" class="action-item">📋 Quản lý người dùng</a></li>
                <li><a href="#" class="action-item">📦 Quản lý sản phẩm</a></li>
                <li><a href="#" class="action-item">💰 Quản lý rút tiền</a></li>
                <li><a href="#" class="action-item">🔍 Duyệt tài liệu</a></li>
            </ul>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
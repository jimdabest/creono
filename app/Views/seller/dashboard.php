<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container mt-4">
    <div class="card dashboard-card">
        <div class="dashboard-header">
            <h2>Dashboard Người bán</h2>
            <a href="#" class="btn btn-success">+ Thêm sản phẩm</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Sản phẩm</h3>
                <p class="stat-value">0</p>
            </div>
            <div class="stat-card">
                <h3>Doanh thu</h3>
                <p class="stat-value">0đ</p>
            </div>
            <div class="stat-card">
                <h3>Đánh giá</h3>
                <p class="stat-value">⭐ 0.0</p>
            </div>
        </div>
        
        <div class="dashboard-section">
            <h3>Danh sách sản phẩm</h3>
            <div class="empty-state">
                Chưa có sản phẩm nào. Bắt đầu đăng bán ngay!
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
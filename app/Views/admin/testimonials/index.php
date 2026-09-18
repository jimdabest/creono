<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- CSS chuyên biệt cho trang Quản lý Testimonials -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/admin-testimonials.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="tm-header mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Quản lý Testimonials</span>
            </nav>
            <h1 class="admin-title">Quản lý Testimonials</h1>
            <p class="admin-subtitle">Danh sách cảm nhận & đánh giá của khách hàng về hệ thống Creono</p>
        </div>
        <div class="admin-actions">
            <a href="<?php echo URLROOT; ?>/testimonialController/create" class="btn btn-success" id="btn-add-testimonial">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="tm-btn-icon">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm Testimonial
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <?php
    $totalCount = count($data['testimonials']);
    $featuredCount = 0;
    $totalRating = 0;
    foreach ($data['testimonials'] as $item) {
        if (!empty($item->is_featured)) $featuredCount++;
        $totalRating += (int)$item->rating;
    }
    $avgRating = $totalCount > 0 ? number_format($totalRating / $totalCount, 1) : '5.0';
    ?>
    <div class="tm-stats-grid mb-4">
        <div class="tm-stat-chip">
            <span class="tm-stat-icon tm-stat-icon--total">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </span>
            <div class="tm-stat-info">
                <span class="tm-stat-value"><?php echo $totalCount; ?></span>
                <span class="tm-stat-label">Tổng đánh giá</span>
            </div>
        </div>
        <div class="tm-stat-chip">
            <span class="tm-stat-icon tm-stat-icon--featured">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </span>
            <div class="tm-stat-info">
                <span class="tm-stat-value"><?php echo $featuredCount; ?></span>
                <span class="tm-stat-label">Hiển thị nổi bật</span>
            </div>
        </div>
        <div class="tm-stat-chip">
            <span class="tm-stat-icon tm-stat-icon--rating">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="12 6 13.8 9.6 18 10.2 15 13.1 15.7 17.2 12 15.2 8.3 17.2 9 13.1 6 10.2 10.2 9.6 12 6"></polygon>
                </svg>
            </span>
            <div class="tm-stat-info">
                <span class="tm-stat-value"><?php echo $avgRating; ?> ★</span>
                <span class="tm-stat-label">Điểm đánh giá TB</span>
            </div>
        </div>
    </div>

    <!-- Testimonial Table Card -->
    <div class="admin-card">
        <div class="card-header flex-between">
            <h3>Danh sách cảm nhận khách hàng (<?php echo $totalCount; ?>)</h3>
            <span class="badge badge-light">Sắp xếp: Nổi bật trước & Thứ tự tăng dần</span>
        </div>

        <?php if (!empty($data['testimonials'])) : ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="tm-col-id">ID</th>
                            <th class="tm-col-user">Người dùng</th>
                            <th>Nội dung cảm nhận</th>
                            <th class="tm-col-stars">Số sao</th>
                            <th class="tm-col-featured">Duyệt nổi bật</th>
                            <th class="tm-col-order">Thứ tự</th>
                            <th class="tm-col-date">Ngày tạo</th>
                            <th class="tm-col-actions text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['testimonials'] as $tm) : ?>
                            <tr id="tm-row-<?php echo $tm->id; ?>">
                                <td><span class="text-muted font-sm">#<?php echo $tm->id; ?></span></td>
                                <td>
                                    <div class="tm-user-cell">
                                        <div class="tm-avatar">
                                            <?php if (!empty($tm->avatar_url)) : ?>
                                                <img src="<?php echo URLROOT . '/' . ltrim(htmlspecialchars($tm->avatar_url), '/'); ?>" alt="Avatar">
                                            <?php else : ?>
                                                <?php echo strtoupper(mb_substr($tm->user_name ?? 'U', 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tm-user-meta">
                                            <strong><?php echo htmlspecialchars($tm->user_name ?? 'Khách hàng'); ?></strong>
                                            <?php if (!empty($tm->user_email)) : ?>
                                                <small><?php echo htmlspecialchars($tm->user_email); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="tm-content-box">
                                        &ldquo;<?php echo nl2br(htmlspecialchars($tm->content)); ?>&rdquo;
                                    </div>
                                </td>
                                <td>
                                    <span class="tm-stars" title="<?php echo (int)$tm->rating; ?> sao">
                                        <?php echo str_repeat('★', (int)$tm->rating) . str_repeat('☆', 5 - (int)$tm->rating); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="tm-badge-featured <?php echo $tm->is_featured ? 'is-active' : 'is-inactive'; ?>"
                                            data-id="<?php echo $tm->id; ?>"
                                            title="Bấm để bật/tắt hiển thị nổi bật">
                                        <?php if ($tm->is_featured) : ?>
                                            <span>⭐ Nổi bật</span>
                                        <?php else : ?>
                                            <span>⚪ Thường</span>
                                        <?php endif; ?>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light"><?php echo (int)$tm->sort_order; ?></span>
                                </td>
                                <td>
                                    <span class="text-muted font-sm">
                                        <?php echo date('d/m/Y', strtotime($tm->created_at)); ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="btn-action-group">
                                        <a href="<?php echo URLROOT; ?>/testimonialController/edit/<?php echo $tm->id; ?>" class="btn-action btn-action-edit" title="Chỉnh sửa">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            Sửa
                                        </a>

                                        <form action="<?php echo URLROOT; ?>/testimonialController/destroy/<?php echo $tm->id; ?>" method="POST" class="tm-inline-form" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá của \'<?php echo htmlspecialchars($tm->user_name ?? '', ENT_QUOTES); ?>\'?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>">
                                            <button type="submit" class="btn-action btn-action-delete" title="Xóa testimonial">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="tm-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="tm-empty-icon">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <p class="tm-empty-text">Chưa có testimonial nào được tạo.</p>
                <a href="<?php echo URLROOT; ?>/testimonialController/create" class="btn btn-primary btn-sm">Thêm cảm nhận đầu tiên</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Xử lý AJAX Toggle Featured
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?php echo $data['csrf_token']; ?>';
    
    document.querySelectorAll('.tm-badge-featured').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span>...</span>';

            const formData = new FormData();
            formData.append('csrf_token', csrfToken);

            fetch('<?php echo URLROOT; ?>/testimonialController/toggleFeatured/' + id, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.is_featured === 1) {
                        this.className = 'tm-badge-featured is-active';
                        this.innerHTML = '<span>⭐ Nổi bật</span>';
                    } else {
                        this.className = 'tm-badge-featured is-inactive';
                        this.innerHTML = '<span>⚪ Thường</span>';
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra.');
                    this.innerHTML = originalHtml;
                }
            })
            .catch(err => {
                alert('Không thể kết nối máy chủ.');
                this.innerHTML = originalHtml;
            });
        });
    });
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

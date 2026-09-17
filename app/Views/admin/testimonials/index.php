<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
/* CSS giao diện Quản lý Testimonials */
.tm-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
}
.tm-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}
.tm-stat-chip {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 14px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.tm-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.tm-stat-icon--total { background: rgba(0, 113, 227, 0.1); color: var(--apple-blue); }
.tm-stat-icon--featured { background: rgba(255, 149, 0, 0.12); color: var(--apple-orange); }
.tm-stat-icon--rating { background: rgba(52, 199, 89, 0.12); color: var(--apple-green); }
.tm-stat-info { display: flex; flex-direction: column; }
.tm-stat-value { font-size: 22px; font-weight: 700; color: var(--apple-black); line-height: 1.2; }
.tm-stat-label { font-size: 13px; color: var(--apple-gray); margin-top: 2px; }

/* Table styling */
.tm-user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.tm-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    overflow: hidden;
    background: #f0f0f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--apple-blue);
    font-size: 14px;
    flex-shrink: 0;
}
.tm-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tm-user-meta strong {
    display: block;
    color: var(--apple-black);
    font-size: 14px;
}
.tm-user-meta small {
    color: var(--apple-gray);
    font-size: 12px;
}
.tm-stars {
    color: #ffb800;
    font-size: 15px;
    letter-spacing: 1px;
}
.tm-content-box {
    max-width: 380px;
    font-size: 13.5px;
    line-height: 1.5;
    color: #333;
    font-style: italic;
}
.tm-badge-featured {
    cursor: pointer;
    border: none;
    background: none;
    padding: 0;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.2s ease;
}
.tm-badge-featured.is-active {
    background: rgba(255, 149, 0, 0.15);
    color: #d97706;
}
.tm-badge-featured.is-inactive {
    background: #f1f2f6;
    color: var(--apple-gray);
}
.tm-badge-featured:hover {
    transform: scale(1.04);
}
.btn-action-group {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}
</style>

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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
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
                            <th style="width: 50px;">ID</th>
                            <th style="width: 200px;">Người dùng</th>
                            <th>Nội dung cảm nhận</th>
                            <th style="width: 110px;">Số sao</th>
                            <th style="width: 140px; text-align: center;">Duyệt nổi bật</th>
                            <th style="width: 80px; text-align: center;">Thứ tự</th>
                            <th style="width: 110px;">Ngày tạo</th>
                            <th style="width: 140px;" class="text-right">Hành động</th>
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

                                        <form action="<?php echo URLROOT; ?>/testimonialController/destroy/<?php echo $tm->id; ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá của \'<?php echo htmlspecialchars($tm->user_name ?? '', ENT_QUOTES); ?>\'?');">
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
            <div style="text-align: center; padding: 48px 20px; color: var(--apple-gray);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; opacity: 0.5;">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <p style="font-size: 16px; margin-bottom: 16px;">Chưa có testimonial nào được tạo.</p>
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

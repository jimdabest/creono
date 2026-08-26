<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- CSS chuyên biệt cho trang Quản lý User -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/admin-users.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <!-- Breadcrumb & Header -->
    <div class="admin-header flex-between mb-4">
        <div>
            <nav class="breadcrumb mb-2">
                <a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Dashboard</a> &nbsp;&rsaquo;&nbsp;
                <span class="text-muted">Quản lý Người dùng</span>
            </nav>
            <h1 class="admin-title">Quản lý Người dùng</h1>
            <p class="admin-subtitle">Quản lý tài khoản Buyer, Seller & phân quyền trên Creono</p>
        </div>
        <div class="admin-actions">
            <a href="<?php echo URLROOT; ?>/adminUserController/create" class="btn btn-success" id="btn-add-user">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm người dùng
            </a>
        </div>
    </div>

    <!-- Thống kê nhanh -->
    <div class="au-stats-row mb-4">
        <div class="au-stat-chip">
            <span class="au-stat-icon au-stat-icon--total">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </span>
            <span class="au-stat-value"><?php echo number_format($data['total_users']); ?></span>
            <span class="au-stat-label">Tổng người dùng</span>
        </div>
        <div class="au-stat-chip">
            <span class="au-stat-icon au-stat-icon--locked">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </span>
            <span class="au-stat-value"><?php echo number_format($data['locked_count']); ?></span>
            <span class="au-stat-label">Đang bị khóa</span>
        </div>
    </div>

    <!-- Thanh tìm kiếm & lọc -->
    <div class="au-toolbar mb-3">
        <div class="au-search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="au-search-input" placeholder="Tìm theo tên, email..." autocomplete="off">
        </div>
        <div class="au-filter-group">
            <select id="au-filter-role" class="au-select">
                <option value="">Tất cả vai trò</option>
                <option value="1">Buyer</option>
                <option value="2">Seller</option>
                <option value="4">Censor</option>
            </select>
            <select id="au-filter-status" class="au-select">
                <option value="">Tất cả trạng thái</option>
                <option value="active">Hoạt động</option>
                <option value="locked">Đã khóa</option>
            </select>
        </div>
    </div>

    <!-- Bảng danh sách User -->
    <div class="admin-card">
        <div class="card-header flex-between">
            <h3>Danh sách người dùng (<span id="au-visible-count"><?php echo count($data['users']); ?></span>)</h3>
            <span class="badge badge-light">Sắp xếp: Mới nhất</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="au-users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody id="au-users-tbody">
                    <?php if (!empty($data['users'])) : ?>
                        <?php foreach ($data['users'] as $user) : ?>
                            <tr id="au-row-<?php echo $user->id; ?>"
                                data-name="<?php echo htmlspecialchars(strtolower($user->name)); ?>"
                                data-email="<?php echo htmlspecialchars(strtolower($user->email)); ?>"
                                data-role="<?php echo $user->role; ?>"
                                data-locked="<?php echo $user->is_locked ? 'locked' : 'active'; ?>">
                                <td><span class="text-muted">#<?php echo $user->id; ?></span></td>
                                <td>
                                    <div class="au-user-cell">
                                        <div class="au-avatar">
                                            <?php if (!empty($user->avatar_url)) : ?>
                                                <img src="<?php echo URLROOT . '/' . htmlspecialchars($user->avatar_url); ?>" alt="Avatar">
                                            <?php else : ?>
                                                <span class="au-avatar-placeholder">
                                                    <?php echo strtoupper(mb_substr($user->name, 0, 1)); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="au-user-info">
                                            <strong><?php echo htmlspecialchars($user->name); ?></strong>
                                            <?php if (!empty($user->full_name) && $user->full_name !== $user->name) : ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($user->full_name); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="au-email"><?php echo htmlspecialchars($user->email); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $roleMap = [1 => ['Buyer', 'badge-primary'], 2 => ['Seller', 'badge-success'], 4 => ['Censor', 'badge-warning']];
                                    $roleInfo = $roleMap[$user->role] ?? ['Unknown', 'badge-secondary'];
                                    ?>
                                    <span class="badge <?php echo $roleInfo[1]; ?>"><?php echo $roleInfo[0]; ?></span>
                                </td>
                                <td>
                                    <span class="au-status-badge <?php echo $user->is_locked ? 'au-status--locked' : 'au-status--active'; ?>"
                                          id="au-status-<?php echo $user->id; ?>">
                                        <?php echo $user->is_locked ? '🔒 Đã khóa' : '✅ Hoạt động'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted font-sm">
                                        <?php echo date('d/m/Y', strtotime($user->created_at)); ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="au-action-group">
                                        <!-- Nút chỉnh sửa -->
                                        <a href="<?php echo URLROOT; ?>/adminUserController/edit/<?php echo $user->id; ?>"
                                           class="btn-action btn-action-edit" title="Chỉnh sửa">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            Sửa
                                        </a>

                                        <!-- Nút khóa / mở khóa (AJAX) -->
                                        <button type="button"
                                                class="btn-action au-btn-toggle-lock <?php echo $user->is_locked ? 'btn-action-unlock' : 'btn-action-lock'; ?>"
                                                data-user-id="<?php echo $user->id; ?>"
                                                data-user-name="<?php echo htmlspecialchars($user->name, ENT_QUOTES); ?>"
                                                data-locked="<?php echo $user->is_locked ? '1' : '0'; ?>"
                                                id="au-lock-btn-<?php echo $user->id; ?>"
                                                title="<?php echo $user->is_locked ? 'Mở khóa' : 'Khóa'; ?>">
                                            <?php if ($user->is_locked) : ?>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                    <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                                                </svg>
                                                Mở khóa
                                            <?php else : ?>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                </svg>
                                                Khóa
                                            <?php endif; ?>
                                        </button>

                                        <!-- Nút xóa (AJAX) -->
                                        <button type="button"
                                                class="btn-action btn-action-delete au-btn-delete"
                                                data-user-id="<?php echo $user->id; ?>"
                                                data-user-name="<?php echo htmlspecialchars($user->name, ENT_QUOTES); ?>"
                                                id="au-delete-btn-<?php echo $user->id; ?>"
                                                title="Xóa tài khoản">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có người dùng nào trong hệ thống.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="au-modal-overlay" id="au-delete-modal">
    <div class="au-modal">
        <div class="au-modal-header">
            <h3>Xác nhận xóa tài khoản</h3>
            <button type="button" class="au-modal-close" id="au-modal-close">&times;</button>
        </div>
        <div class="au-modal-body">
            <div class="au-modal-icon au-modal-icon--danger">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <p>Bạn có chắc chắn muốn <strong>xóa vĩnh viễn</strong> tài khoản</p>
            <p class="au-modal-username" id="au-delete-username"></p>
            <p class="au-modal-warning">Hành động này không thể hoàn tác. Tất cả dữ liệu liên quan sẽ bị xóa.</p>
            <input type="hidden" id="au-delete-user-id" value="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        </div>
        <div class="au-modal-footer">
            <button type="button" class="btn btn-outline au-modal-cancel" id="au-modal-cancel-btn">Hủy bỏ</button>
            <button type="button" class="btn btn-danger" id="au-modal-confirm-btn">Xóa vĩnh viễn</button>
        </div>
    </div>
</div>

<!-- CSRF Token ẩn cho JS AJAX -->
<input type="hidden" id="au-csrf-token" value="<?php echo $_SESSION['csrf_token']; ?>">

<!-- JS Module xử lý AJAX -->
<script src="<?php echo URLROOT; ?>/js/modules/admin-users.js?v=<?php echo time(); ?>"></script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>

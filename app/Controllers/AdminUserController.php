<?php

/**
 * AdminUserController - Controller quản lý người dùng phía Admin (UC40)
 * Xử lý: Danh sách, Thêm, Sửa, Khóa/Mở khóa, Xóa user
 * Tuân thủ: CSRF check, RoleMiddleware, jsonResponse cho AJAX
 */

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Core/Validator.php';

class AdminUserController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        // Chỉ Admin (role = 3) mới được truy cập
        RoleMiddleware::check([3]);

        $this->userModel = $this->model('UserModel');
    }

    /**
     * Helper: Trả về JSON response chuẩn cho AJAX
     * Format: { success: bool, message: string, data: array }
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data));
        exit();
    }

    // =========================================================================
    // INDEX: Danh sách người dùng
    // =========================================================================

    /**
     * Trang danh sách người dùng (default method)
     */
    public function index(): void
    {
        $users = $this->userModel->getAllUsersWithProfile();

        $data = [
            'title'        => 'Quản lý Người dùng - Creono Admin',
            'users'        => $users,
            'total_users'  => $this->userModel->getTotalNonAdminUsers(),
            'locked_count' => $this->userModel->getLockedCount()
        ];

        $this->view('admin/users/index', $data);
    }

    // =========================================================================
    // CREATE: Form thêm user mới (GET)
    // =========================================================================

    /**
     * Hiển thị form tạo user mới
     */
    public function create(): void
    {
        $data = [
            'title'  => 'Thêm Người dùng mới - Creono Admin',
            'name'   => '',
            'email'  => '',
            'role'   => 1,
            'errors' => []
        ];

        $this->view('admin/users/create', $data);
    }

    // =========================================================================
    // STORE: Xử lý thêm user mới (POST)
    // =========================================================================

    /**
     * Lưu user mới vào database
     */
    public function store(): void
    {
        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: ' . URLROOT . '/adminUserController/index');
            exit();
        }

        // Xác thực CSRF Token
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        // Thu thập dữ liệu từ form
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = (int) ($_POST['role'] ?? 1);

        // Validate dữ liệu
        $errors = [];

        if (empty($name)) {
            $errors['name_err'] = 'Vui lòng nhập tên người dùng';
        }

        if (empty($email)) {
            $errors['email_err'] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email_err'] = 'Email không hợp lệ';
        } elseif ($this->userModel->emailExists($email)) {
            $errors['email_err'] = 'Email này đã được sử dụng';
        }

        if (empty($password)) {
            $errors['password_err'] = 'Vui lòng nhập mật khẩu';
        } elseif (strlen($password) < 6) {
            $errors['password_err'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }

        if (!in_array($role, [1, 2, 4])) {
            $errors['role_err'] = 'Vai trò không hợp lệ';
        }

        // Nếu có lỗi, render lại form
        if (!empty($errors)) {
            $data = [
                'title'  => 'Thêm Người dùng mới - Creono Admin',
                'name'   => $name,
                'email'  => $email,
                'role'   => $role,
                'errors' => $errors
            ];
            $this->view('admin/users/create', $data);
            return;
        }

        // Hash mật khẩu và tạo user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertData = [
            'name'      => $name,
            'email'     => $email,
            'password'  => $hashedPassword,
            'role'      => $role,
            'is_locked' => 0
        ];

        if ($this->userModel->create($insertData)) {
            // Tạo profile rỗng cho user mới
            $userId = $this->userModel->getLastInsertId();

            setFlash('success', "Đã tạo tài khoản '{$name}' thành công!");
            header('location: ' . URLROOT . '/adminUserController/index');
            exit();
        } else {
            setFlash('error', 'Có lỗi xảy ra khi tạo tài khoản. Vui lòng thử lại.');
            header('location: ' . URLROOT . '/adminUserController/create');
            exit();
        }
    }

    // =========================================================================
    // EDIT: Form chỉnh sửa user (GET)
    // =========================================================================

    /**
     * Hiển thị form chỉnh sửa thông tin user
     */
    public function edit(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID người dùng không hợp lệ');
            header('location: ' . URLROOT . '/adminUserController/index');
            exit();
        }

        $user = $this->userModel->getUserDetailById($id);
        if (!$user) {
            setFlash('error', 'Không tìm thấy người dùng');
            header('location: ' . URLROOT . '/adminUserController/index');
            exit();
        }

        $data = [
            'title'  => 'Chỉnh sửa Người dùng - Creono Admin',
            'user'   => $user,
            'name'   => $user->name,
            'email'  => $user->email,
            'role'   => $user->role,
            'errors' => []
        ];

        $this->view('admin/users/edit', $data);
    }

    // =========================================================================
    // UPDATE: Xử lý cập nhật user (POST)
    // =========================================================================

    /**
     * Cập nhật thông tin user vào database
     */
    public function update(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID người dùng không hợp lệ');
            header('location: ' . URLROOT . '/adminUserController/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: ' . URLROOT . '/adminUserController/edit/' . $id);
            exit();
        }

        // Xác thực CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        $user = $this->userModel->getUserDetailById($id);
        if (!$user) {
            setFlash('error', 'Không tìm thấy người dùng');
            header('location: ' . URLROOT . '/adminUserController/index');
            exit();
        }

        // Thu thập dữ liệu
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = (int) ($_POST['role'] ?? 1);

        // Validate
        $errors = [];

        if (empty($name)) {
            $errors['name_err'] = 'Vui lòng nhập tên người dùng';
        }

        if (empty($email)) {
            $errors['email_err'] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email_err'] = 'Email không hợp lệ';
        } elseif ($this->userModel->emailExists($email, $id)) {
            $errors['email_err'] = 'Email này đã được sử dụng bởi tài khoản khác';
        }

        if (!in_array($role, [1, 2, 4])) {
            $errors['role_err'] = 'Vai trò không hợp lệ';
        }

        // Nếu có lỗi, render lại form
        if (!empty($errors)) {
            $data = [
                'title'  => 'Chỉnh sửa Người dùng - Creono Admin',
                'user'   => $user,
                'name'   => $name,
                'email'  => $email,
                'role'   => $role,
                'errors' => $errors
            ];
            $this->view('admin/users/edit', $data);
            return;
        }

        // Cập nhật user
        $updateData = [
            'name'  => $name,
            'email' => $email,
            'role'  => $role
        ];

        if ($this->userModel->update($id, $updateData)) {
            setFlash('success', "Đã cập nhật thông tin '{$name}' thành công!");
            header('location: ' . URLROOT . '/adminUserController/index');
            exit();
        } else {
            setFlash('error', 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.');
            header('location: ' . URLROOT . '/adminUserController/edit/' . $id);
            exit();
        }
    }

    // =========================================================================
    // TOGGLE LOCK: Khóa / Mở khóa tài khoản (AJAX - POST)
    // =========================================================================

    /**
     * Toggle trạng thái khóa tài khoản
     * Trả về jsonResponse cho frontend xử lý AJAX
     */
    public function toggleLock(?int $id = null): void
    {
        // Chỉ chấp nhận POST (AJAX)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        // Xác thực CSRF từ header hoặc body
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(false, 'CSRF token không hợp lệ');
        }

        if (!$id) {
            $this->jsonResponse(false, 'ID người dùng không hợp lệ');
        }

        // Kiểm tra user tồn tại
        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->jsonResponse(false, 'Không tìm thấy người dùng');
        }

        // Không cho phép khóa Admin
        if ($user->role == 3) {
            $this->jsonResponse(false, 'Không thể khóa tài khoản Admin');
        }

        // Toggle trạng thái
        $newStatus = $this->userModel->toggleLock($id);

        if ($newStatus !== null) {
            $statusText = $newStatus ? 'khóa' : 'mở khóa';
            $this->jsonResponse(true, "Đã {$statusText} tài khoản '{$user->name}' thành công!", [
                'is_locked' => $newStatus,
                'user_id'   => $id
            ]);
        } else {
            $this->jsonResponse(false, 'Có lỗi xảy ra. Vui lòng thử lại.');
        }
    }

    // =========================================================================
    // DELETE: Xóa tài khoản (AJAX - POST)
    // =========================================================================

    /**
     * Xóa vĩnh viễn tài khoản người dùng
     * Trả về jsonResponse cho frontend xử lý AJAX
     */
    public function delete(?int $id = null): void
    {
        // Chỉ chấp nhận POST (AJAX)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        // Xác thực CSRF
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(false, 'CSRF token không hợp lệ');
        }

        if (!$id) {
            $this->jsonResponse(false, 'ID người dùng không hợp lệ');
        }

        // Kiểm tra user tồn tại
        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->jsonResponse(false, 'Không tìm thấy người dùng');
        }

        // Không cho xóa Admin
        if ($user->role == 3) {
            $this->jsonResponse(false, 'Không thể xóa tài khoản Admin');
        }

        // Xóa vĩnh viễn
        if ($this->userModel->forceDelete($id)) {
            $this->jsonResponse(true, "Đã xóa tài khoản '{$user->name}' thành công!", [
                'user_id' => $id
            ]);
        } else {
            $this->jsonResponse(false, 'Có lỗi xảy ra khi xóa. Vui lòng thử lại.');
        }
    }
}

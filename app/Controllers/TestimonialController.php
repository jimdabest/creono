<?php

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class TestimonialController extends Controller
{
    private Testimonial $testimonialModel;
    private User $userModel;

    public function __construct()
    {
        // Cho phép Admin (role 3) và Seller (role 2) quản lý
        RoleMiddleware::check([2, 3]);

        $this->testimonialModel = $this->model('Testimonial');
        $this->userModel = $this->model('User');
    }

    /**
     * Helper: Trả về JSON response chuẩn cho AJAX
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
    // INDEX: Danh sách testimonials
    // =========================================================================
    public function index(): void
    {
        $testimonials = $this->testimonialModel->getAllOrdered();

        $data = [
            'title'        => 'Quản lý Testimonials - Creono',
            'testimonials' => $testimonials,
            'csrf_token'   => generateCsrfToken()
        ];

        $this->view('admin/testimonials/index', $data);
    }

    // =========================================================================
    // CREATE: Hiển thị form thêm mới (GET)
    // =========================================================================
    public function create(): void
    {
        $users = $this->userModel->findAll();

        $data = [
            'title'       => 'Thêm Testimonial mới - Creono',
            'users'       => $users,
            'user_id'     => (int)($_SESSION['user_id'] ?? 0),
            'content'     => '',
            'rating'      => 5,
            'is_featured' => 1,
            'sort_order'  => 0,
            'errors'      => [],
            'csrf_token'  => generateCsrfToken()
        ];

        $this->view('admin/testimonials/create', $data);
    }

    // =========================================================================
    // STORE: Xử lý lưu testimonial mới (POST)
    // =========================================================================
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: ' . URLROOT . '/testimonialController/index');
            exit();
        }

        // Xác thực CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            setFlash('error', 'Lỗi bảo mật CSRF token. Vui lòng tải lại trang.');
            header('location: ' . URLROOT . '/testimonialController/create');
            exit();
        }

        // Lấy dữ liệu từ form
        $userId     = (int)($_POST['user_id'] ?? 0);
        $content    = trim($_POST['content'] ?? '');
        $rating     = (int)($_POST['rating'] ?? 5);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        // Validate dữ liệu
        $errors = [];

        if ($userId <= 0) {
            $errors['user_id_err'] = 'Vui lòng chọn người dùng';
        } else {
            $targetUser = $this->userModel->findById($userId);
            if (!$targetUser) {
                $errors['user_id_err'] = 'Người dùng được chọn không tồn tại';
            }
        }

        if (empty($content)) {
            $errors['content_err'] = 'Vui lòng nhập nội dung đánh giá / cảm nhận';
        } elseif (mb_strlen($content) < 10) {
            $errors['content_err'] = 'Nội dung đánh giá phải có ít nhất 10 ký tự';
        } elseif (mb_strlen($content) > 1000) {
            $errors['content_err'] = 'Nội dung không được vượt quá 1000 ký tự';
        }

        if ($rating < 1 || $rating > 5) {
            $errors['rating_err'] = 'Đánh giá phải từ 1 đến 5 sao';
        }

        if ($sortOrder < 0) {
            $errors['sort_order_err'] = 'Thứ tự sắp xếp phải là số không âm';
        }

        // Nếu có lỗi, render lại form với thông tin lỗi
        if (!empty($errors)) {
            $users = $this->userModel->findAll();
            $data = [
                'title'       => 'Thêm Testimonial mới - Creono',
                'users'       => $users,
                'user_id'     => $userId,
                'content'     => $content,
                'rating'      => $rating,
                'is_featured' => $isFeatured,
                'sort_order'  => $sortOrder,
                'errors'      => $errors,
                'csrf_token'  => generateCsrfToken()
            ];
            $this->view('admin/testimonials/create', $data);
            return;
        }

        // Tạo dữ liệu insert
        $insertData = [
            'user_id'     => $userId,
            'content'     => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
            'rating'      => $rating,
            'is_featured' => $isFeatured,
            'sort_order'  => $sortOrder
        ];

        if ($this->testimonialModel->create($insertData)) {
            setFlash('success', 'Đã thêm testimonial mới thành công!');
            header('location: ' . URLROOT . '/testimonialController/index');
            exit();
        } else {
            setFlash('error', 'Có lỗi xảy ra khi lưu dữ liệu vào hệ thống.');
            header('location: ' . URLROOT . '/testimonialController/create');
            exit();
        }
    }

    // =========================================================================
    // EDIT: Form chỉnh sửa testimonial (GET)
    // =========================================================================
    public function edit(?int $id = null): void
    {
        if (!$id || $id <= 0) {
            setFlash('error', 'ID Testimonial không hợp lệ.');
            header('location: ' . URLROOT . '/testimonialController/index');
            exit();
        }

        $testimonial = $this->testimonialModel->getByIdWithUser($id);
        if (!$testimonial) {
            setFlash('error', 'Không tìm thấy Testimonial yêu cầu.');
            header('location: ' . URLROOT . '/testimonialController/index');
            exit();
        }

        $users = $this->userModel->findAll();

        $data = [
            'title'       => 'Chỉnh sửa Testimonial #' . $id . ' - Creono',
            'testimonial' => $testimonial,
            'users'       => $users,
            'user_id'     => (int)$testimonial->user_id,
            'content'     => $testimonial->content,
            'rating'      => (int)$testimonial->rating,
            'is_featured' => (int)$testimonial->is_featured,
            'sort_order'  => (int)$testimonial->sort_order,
            'errors'      => [],
            'csrf_token'  => generateCsrfToken()
        ];

        $this->view('admin/testimonials/edit', $data);
    }

    // =========================================================================
    // UPDATE: Xử lý cập nhật testimonial (POST)
    // =========================================================================
    public function update(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id || $id <= 0) {
            header('location: ' . URLROOT . '/testimonialController/index');
            exit();
        }

        // Xác thực CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            setFlash('error', 'Lỗi bảo mật CSRF token. Vui lòng tải lại trang.');
            header('location: ' . URLROOT . '/testimonialController/edit/' . $id);
            exit();
        }

        $testimonial = $this->testimonialModel->findById($id);
        if (!$testimonial) {
            setFlash('error', 'Không tìm thấy Testimonial cần cập nhật.');
            header('location: ' . URLROOT . '/testimonialController/index');
            exit();
        }

        // Lấy dữ liệu
        $userId     = (int)($_POST['user_id'] ?? $testimonial->user_id);
        $content    = trim($_POST['content'] ?? '');
        $rating     = (int)($_POST['rating'] ?? 5);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        // Validate
        $errors = [];

        if ($userId <= 0) {
            $errors['user_id_err'] = 'Vui lòng chọn người dùng';
        } else {
            $targetUser = $this->userModel->findById($userId);
            if (!$targetUser) {
                $errors['user_id_err'] = 'Người dùng được chọn không tồn tại';
            }
        }

        if (empty($content)) {
            $errors['content_err'] = 'Vui lòng nhập nội dung đánh giá';
        } elseif (mb_strlen($content) < 10) {
            $errors['content_err'] = 'Nội dung đánh giá phải có ít nhất 10 ký tự';
        } elseif (mb_strlen($content) > 1000) {
            $errors['content_err'] = 'Nội dung không được vượt quá 1000 ký tự';
        }

        if ($rating < 1 || $rating > 5) {
            $errors['rating_err'] = 'Đánh giá phải từ 1 đến 5 sao';
        }

        if ($sortOrder < 0) {
            $errors['sort_order_err'] = 'Thứ tự sắp xếp phải là số không âm';
        }

        if (!empty($errors)) {
            $users = $this->userModel->findAll();
            $data = [
                'title'       => 'Chỉnh sửa Testimonial #' . $id . ' - Creono',
                'testimonial' => $testimonial,
                'users'       => $users,
                'user_id'     => $userId,
                'content'     => $content,
                'rating'      => $rating,
                'is_featured' => $isFeatured,
                'sort_order'  => $sortOrder,
                'errors'      => $errors,
                'csrf_token'  => generateCsrfToken()
            ];
            $this->view('admin/testimonials/edit', $data);
            return;
        }

        $updateData = [
            'user_id'     => $userId,
            'content'     => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
            'rating'      => $rating,
            'is_featured' => $isFeatured,
            'sort_order'  => $sortOrder
        ];

        if ($this->testimonialModel->update($id, $updateData)) {
            setFlash('success', 'Đã cập nhật testimonial #' . $id . ' thành công!');
            header('location: ' . URLROOT . '/testimonialController/index');
            exit();
        } else {
            setFlash('error', 'Có lỗi xảy ra khi cập nhật.');
            header('location: ' . URLROOT . '/testimonialController/edit/' . $id);
            exit();
        }
    }

    // =========================================================================
    // DESTROY: Xóa testimonial (POST / AJAX)
    // =========================================================================
    public function destroy(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id || $id <= 0) {
            $this->handleRedirectOrJson(false, 'Yêu cầu không hợp lệ.');
            return;
        }

        // Xác thực CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->handleRedirectOrJson(false, 'Lỗi bảo mật CSRF token.');
            return;
        }

        $testimonial = $this->testimonialModel->findById($id);
        if (!$testimonial) {
            $this->handleRedirectOrJson(false, 'Testimonial không tồn tại hoặc đã bị xóa.');
            return;
        }

        if ($this->testimonialModel->destroy($id)) {
            $this->handleRedirectOrJson(true, 'Đã xóa testimonial thành công!');
        } else {
            $this->handleRedirectOrJson(false, 'Lỗi hệ thống khi xóa dữ liệu.');
        }
    }

    // =========================================================================
    // TOGGLE FEATURED: Bật/Tắt duyệt hiển thị nổi bật (POST / AJAX)
    // =========================================================================
    public function toggleFeatured(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id || $id <= 0) {
            $this->handleRedirectOrJson(false, 'Yêu cầu không hợp lệ.');
            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->handleRedirectOrJson(false, 'Lỗi bảo mật CSRF token.');
            return;
        }

        $testimonial = $this->testimonialModel->findById($id);
        if (!$testimonial) {
            $this->handleRedirectOrJson(false, 'Testimonial không tồn tại.');
            return;
        }

        if ($this->testimonialModel->toggleFeatured($id)) {
            $updated = $this->testimonialModel->findById($id);
            $newStatus = (int)($updated->is_featured ?? 0);
            $msg = $newStatus === 1 
                ? 'Đã duyệt hiển thị nổi bật testimonial #' . $id 
                : 'Đã bỏ duyệt nổi bật testimonial #' . $id;

            $this->handleRedirectOrJson(true, $msg, ['is_featured' => $newStatus]);
        } else {
            $this->handleRedirectOrJson(false, 'Không thể thay đổi trạng thái.');
        }
    }

    /**
     * Helper xử lý trả về JSON nếu là request AJAX hoặc Redirect nếu là form submit
     */
    private function handleRedirectOrJson(bool $success, string $message, array $extraData = []): void
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            $this->jsonResponse($success, $message, $extraData);
        } else {
            setFlash($success ? 'success' : 'error', $message);
            header('location: ' . URLROOT . '/testimonialController/index');
            exit();
        }
    }
}

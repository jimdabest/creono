<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/GuestMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Users extends Controller {
    private User $userModel;
    private UserProfile $userProfileModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->userProfileModel = $this->model('UserProfile');
    }

    /**
     * Helper: Trả về JSON response
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data));
        exit();
    }

    /**
     * Helper: Lấy đường dẫn redirect theo role
     */
    private function getRedirectPath(int $role): string {
        $paths = [
            3 => '/admin/dashboard',
            2 => '/seller/dashboard',
            1 => '/products/index'
        ];
        return $paths[$role] ?? '/products/index';
    }

    /**
     * Helper: Kiểm tra request có phải AJAX không
     */
    private function isAjaxRequest(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    // Chức năng UC-01: Đăng ký tài khoản
    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                $this->jsonResponse(false, 'CSRF token validation failed');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Gộp dữ liệu đầu vào
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
            ];

            // Chạy Validator
            $validator = new Validator($_POST);
            $errors = $validator->validate([
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'confirm_password' => 'required|match:password'
            ]);

            // Check email trùng trong DB
            if (empty($errors['email_err']) && $this->userModel->findByEmail($data['email'])) {
                $errors['email_err'] = 'Email này đã được sử dụng';
            }

            // Nếu không có lỗi gì thì cho đăng ký
            if ($validator->passes() && empty($errors['email_err'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                if ($this->userModel->register($data)) {
                    $newUser = $this->userModel->getUserByEmail($data['email']);
                    $this->createUserSession($newUser);
                    return; // Thêm return để dừng execution
                } else {
                    $this->jsonResponse(false, 'Hệ thống đang bận, không thể đăng ký lúc này.');
                }
            } else {
                $this->jsonResponse(false, 'Vui lòng kiểm tra lại thông tin', ['errors' => $errors]);
            }
        } else {
            $data = [
                'name' => '', 'email' => '', 'password' => '', 'confirm_password' => '',
                'csrf_token' => generateCsrfToken(),
            ];
            $this->view('users/register', $data);
        }
    }
    
    // Chức năng UC-02: Đăng nhập
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                $this->jsonResponse(false, 'CSRF token validation failed');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
            ];

            $validator = new Validator($_POST);
            $errors = $validator->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validator->passes()) {
                if (!$this->userModel->findByEmail($data['email'])) {
                    $errors['email_err'] = 'Email không tồn tại trong hệ thống';
                } else {
                    $loggedInUser = $this->userModel->login($data['email'], $data['password']);
                    if ($loggedInUser) {
                        $this->createUserSession($loggedInUser);
                        return; // Thêm return để dừng execution
                    } else {
                        $errors['password_err'] = 'Mật khẩu không chính xác';
                    }
                }
            }
            
            if (!empty($errors)) {
                $this->jsonResponse(false, 'Vui lòng kiểm tra lại thông tin', ['errors' => $errors]);
            }
        } else {
            $data = [
                'email' => '', 'password' => '', 'csrf_token' => generateCsrfToken()
            ];
            $this->view('users/login', $data);
        }
    }

    // Hàm hỗ trợ lưu Session an toàn
    public function createUserSession(object $user): void {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name; 
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_role'] = $user->role; 
        
        $path = $this->getRedirectPath($user->role);
        $fullPath = URLROOT . $path;
        
        // Trả về JSON cho AJAX request
        if ($this->isAjaxRequest()) {
            $this->jsonResponse(true, 'Đăng nhập thành công!', [
                'redirect' => $fullPath
            ]);
        }
        
        // Fallback cho non-AJAX (redirect thường)
        header('location: ' . $fullPath);
        exit();
    }

    // Đăng xuất
    public function logout(): void {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        session_destroy();
        header('location: ' . URLROOT . '/users/login');
        exit();
    }

    // Trang Hồ sơ cá nhân
    public function profile(): void {
        AuthMiddleware::check();

        $user = $this->userModel->getUserWithProfile($_SESSION['user_id']);
        $data = [
            'user' => $user,
            'title' => 'Hồ sơ cá nhân'
        ];

        $this->view('users/profile', $data);
    }

    // Cập nhật Hồ sơ cá nhân
    public function updateProfile(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                $this->jsonResponse(false, 'CSRF token validation failed');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $updateData = [
                'full_name' => trim($_POST['full_name']),
                'bio' => trim($_POST['bio'])
            ];

            // Xử lý upload avatar
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_info = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($file_info, $_FILES['avatar']['tmp_name']);
                finfo_close($file_info);

                if (in_array($mime_type, $allowed_types)) {
                    $upload_dir = '../public/uploads/avatars/';
                    // Tạo thư mục nếu chưa có
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $file_name = time() . '_' . uniqid() . '.' . $extension;
                    $target_file = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                        $updateData['avatar_url'] = '/uploads/avatars/' . $file_name;
                    } else {
                        $this->jsonResponse(false, 'Không thể upload ảnh. Vui lòng thử lại.');
                    }
                } else {
                    $this->jsonResponse(false, 'File không hợp lệ! Chỉ chấp nhận ảnh JPG, PNG hoặc GIF.');
                }
            }

            if ($this->userProfileModel->updateProfile($_SESSION['user_id'], $updateData)) {
                $this->jsonResponse(true, 'Cập nhật hồ sơ thành công!', [
                    'redirect' => URLROOT . '/users/profile'
                ]);
            } else {
                $this->jsonResponse(false, 'Đã xảy ra lỗi khi cập nhật.');
            }
        }
    }

    // Xử lý Đổi mật khẩu
    public function changePassword(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                $this->jsonResponse(false, 'CSRF token validation failed');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            
            $data = [
                'old_password' => $_POST['old_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
            ];

            $validator = new Validator($_POST);
            $errors = $validator->validate([
                'old_password' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|match:new_password'
            ]);

            if ($validator->passes()) {
                $user = $this->userModel->findById($_SESSION['user_id']);
                
                if (!$user || !password_verify($data['old_password'], $user->password)) {
                    $errors['old_password_err'] = 'Mật khẩu hiện tại không chính xác';
                } else {
                    if ($this->userModel->changePassword($_SESSION['user_id'], $data['new_password'])) {
                        $this->jsonResponse(true, 'Đã cập nhật mật khẩu mới thành công!', [
                            'redirect' => URLROOT . '/users/profile'
                        ]);
                    } else {
                        $this->jsonResponse(false, 'Đã xảy ra lỗi hệ thống khi cập nhật mật khẩu.');
                    }
                }
            }
            
            if (!empty($errors)) {
                $this->jsonResponse(false, 'Vui lòng kiểm tra lại thông tin', ['errors' => $errors]);
            }
        } else {
            $data = [
                'old_password' => '', 'new_password' => '', 'confirm_password' => '',
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('users/change_password', $data);
        }
    }
}
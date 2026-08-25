<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/GuestMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Helpers/mail_helper.php';

class Users extends Controller
{
    private Cart $cartModel;
    private Product $productModel;  // Thêm product model để truy vấn bulk
    private User $userModel;
    private UserProfile $userProfileModel;
    private KycDocument $kycModel;


    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->userProfileModel = $this->model('UserProfile');
        $this->cartModel = $this->model('Cart');
        $this->productModel = $this->model('Product');
        $this->kycModel = $this->model('KycDocument');
    }

    /**
     * Helper: Trả về JSON response
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

    /**
     * Helper: Lấy đường dẫn redirect theo role
     */
    private function getRedirectPath(int $role): string
    {
        $paths = [
            3 => '/admin/dashboard',
            2 => '/seller/dashboard',
            1 => '/products/index'
        ];
        return $paths[$role] ?? '/products/index';
    }

    /**
     * Helper: Kiểm tra request có phải AJAX / Fetch không
     */
    private function isAjaxRequest(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
            (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
    }

    // ============================================================
    // CHỨC NĂNG ĐĂNG KÝ
    // ============================================================
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                $this->jsonResponse(false, 'CSRF token validation failed');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
            ];

            $errors = [];

            if (empty($data['name'])) {
                $errors['name_err'] = 'Vui lòng nhập họ và tên';
            } elseif (strlen($data['name']) < 2) {
                $errors['name_err'] = 'Họ và tên phải có ít nhất 2 ký tự';
            } elseif (strlen($data['name']) > 100) {
                $errors['name_err'] = 'Họ và tên không được vượt quá 100 ký tự';
            }

            if (empty($data['email'])) {
                $errors['email_err'] = 'Vui lòng nhập email';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email_err'] = 'Vui lòng nhập đúng định dạng email (ví dụ: name@example.com)';
            } elseif ($this->userModel->findByEmail($data['email'])) {
                $errors['email_err'] = 'Email này đã được sử dụng. Vui lòng chọn email khác';
            }

            if (empty($data['password'])) {
                $errors['password_err'] = 'Vui lòng nhập mật khẩu';
            } elseif (strlen($data['password']) < 6) {
                $errors['password_err'] = 'Mật khẩu phải có ít nhất 6 ký tự';
            } elseif (strlen($data['password']) > 255) {
                $errors['password_err'] = 'Mật khẩu không được vượt quá 255 ký tự';
            }

            if (empty($data['confirm_password'])) {
                $errors['confirm_password_err'] = 'Vui lòng xác nhận mật khẩu';
            } elseif ($data['password'] !== $data['confirm_password']) {
                $errors['confirm_password_err'] = 'Mật khẩu xác nhận không khớp';
            }

            if (empty($errors)) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                if ($this->userModel->register($data)) {
                    $newUser = $this->userModel->getUserByEmail($data['email']);
                    if ($newUser) {
                        $this->createUserSession($newUser, 'Đăng ký thành công!');
                        return;
                    } else {
                        $this->jsonResponse(false, 'Đăng ký thành công nhưng không thể tạo session. Vui lòng đăng nhập lại.');
                    }
                } else {
                    $this->jsonResponse(false, 'Hệ thống đang bận, không thể đăng ký lúc này. Vui lòng thử lại sau.');
                }
            } else {
                $this->jsonResponse(false, 'Vui lòng kiểm tra lại thông tin đăng ký', ['errors' => $errors]);
            }
        } else {
            $data = [
                'name' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'csrf_token' => generateCsrfToken(),
            ];
            $this->view('users/register', $data);
        }
    }

    // ============================================================
    // CHỨC NĂNG ĐĂNG NHẬP
    // ============================================================
    public function login(): void
    {
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
                        $this->createUserSession($loggedInUser, 'Đăng nhập thành công!');
                        return;
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
                'email' => '',
                'password' => '',
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('users/login', $data);
        }
    }

    // ============================================================
    // TẠO SESSION & MERGE GUEST CART (CẢI TIẾN)
    // ============================================================
    public function createUserSession(object $user, string $message = 'Thành công!'): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_role'] = $user->role;
        
        // Merge guest cart vào giỏ hàng của user
        $this->mergeGuestCart($user->id);

        $path = $this->getRedirectPath($user->role);
        $fullPath = URLROOT . $path;

        if ($this->isAjaxRequest() || $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->jsonResponse(true, $message, [
                'redirect' => $fullPath
            ]);
        }

        header('location: ' . $fullPath);
        exit();
    }

    /**
     * Hợp nhất giỏ hàng guest (session) vào giỏ hàng của user
     */
    private function mergeGuestCart(int $userId): void
    {
        $guestIds = $_SESSION['guest_cart'] ?? [];
        if (empty($guestIds) || !is_array($guestIds)) {
            return;
        }

        // Gọi phương thức từ Cart Model
        $result = $this->cartModel->mergeGuestCart($userId, $guestIds);

        // Xóa session bất kể kết quả (trừ trường hợp có lỗi nặng)
        // Nhưng nếu có lỗi, ta vẫn giữ guest cart để không mất dữ liệu
        if ($result['error'] === null) {
            unset($_SESSION['guest_cart']);
        } else {
            // Nếu có lỗi, giữ nguyên guest cart và thông báo
            setFlash('error', 'Không thể chuyển giỏ hàng tạm: ' . $result['error']);
            return;
        }

        // Flash messages
        if ($result['added'] > 0) {
            setFlash('success', "Đã chuyển {$result['added']} sản phẩm vào giỏ hàng của bạn.");
        }
        if ($result['invalid'] > 0) {
            setFlash('warning', "Có {$result['invalid']} sản phẩm không còn khả dụng và đã bị loại.");
        }
        if ($result['own'] > 0) {
            setFlash('info', "Có {$result['own']} sản phẩm là của bạn, không thể thêm vào giỏ hàng.");
        }
    }

    // ============================================================
    // ĐĂNG XUẤT
    // ============================================================
    public function logout(): void
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        session_destroy();
        header('location: ' . URLROOT . '/users/login');
        exit();
    }

    // ============================================================
    // HỒ SƠ CÁ NHÂN
    // ============================================================
    public function profile(): void
    {
        AuthMiddleware::check();
        $user = $this->userModel->getUserWithProfile($_SESSION['user_id']);
        $data = [
            'user' => $user,
            'title' => 'Hồ sơ cá nhân'
        ];
        $this->view('users/profile', $data);
    }

    // ============================================================
    // CẬP NHẬT HỒ SƠ
    // ============================================================
    public function updateProfile(): void
    {
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

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_info = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($file_info, $_FILES['avatar']['tmp_name']);
                finfo_close($file_info);

                if (in_array($mime_type, $allowed_types)) {
                    $upload_dir = '../public/uploads/avatars/';
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

    // ============================================================
    // ĐỔI MẬT KHẨU
    // ============================================================
    public function changePassword(): void
    {
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
                'old_password' => '',
                'new_password' => '',
                'confirm_password' => '',
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('users/change_password', $data);
        }
    }

    // ============================================================
    // QUÊN MẬT KHẨU
    // ============================================================
    // ... trong class Users, phương thức forgotPassword() ...

    public function forgotPassword(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('location: ' . URLROOT);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Kiểm tra CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                $this->jsonResponse(false, 'CSRF token validation failed', ['refresh_token_needed' => true]);
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $email = trim($_POST['email'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse(false, 'Vui lòng nhập địa chỉ email hợp lệ.', [
                    'errors' => ['email_err' => 'Email không đúng định dạng.']
                ]);
            }

            $userExists = $this->userModel->findByEmail($email);

            if ($userExists) {
                // Tạo token
                $token = $this->userModel->createPasswordResetToken($email);
                $resetLink = URLROOT . '/users/resetPassword/' . $token;

                // Render email template
                ob_start();
                include APPROOT . '/Views/emails/reset_password.php';
                $body = ob_get_clean();

                $subject = 'Đặt lại mật khẩu trên Creono';
                $altBody = "Đặt lại mật khẩu: $resetLink";

                // Gửi email (hàm sendEmail đã được load trong public/index.php)
                $mailSent = sendEmail($email, $subject, $body, $altBody);

                // Ghi log nếu thất bại (không ảnh hưởng đến phản hồi)
                if (!$mailSent && function_exists('logError')) {
                    logError("Không thể gửi email reset password cho $email");
                }
            }

            // Luôn trả về thành công (bảo mật)
            $this->jsonResponse(
                true,
                'Nếu email tồn tại trong hệ thống, hướng dẫn khôi phục đã được gửi.'
            );
        } else {
            // GET: hiển thị form
            $data = [
                'title'      => 'Quên mật khẩu - Creono',
                'email'      => '',
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('users/forgot_password', $data);
        }
    }

    // ============================================================
    // ĐẶT LẠI MẬT KHẨU
    // ============================================================
    public function resetPassword(?string $token = null): void
    {
        if (isset($_SESSION['user_id'])) {
            header('location: ' . URLROOT);
            exit();
        }

        if (!$token) {
            setFlash('error', 'Liên kết không hợp lệ.');
            header('location: ' . URLROOT . '/users/forgotPassword');
            exit();
        }

        $userId = $this->userModel->isValidPasswordResetToken($token);
        if (!$userId) {
            setFlash('error', 'Token không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu lại.');
            header('location: ' . URLROOT . '/users/forgotPassword');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                $this->jsonResponse(false, 'CSRF token validation failed', ['refresh_token_needed' => true]);
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            $errors = [];

            if (strlen($password) < 6) {
                $errors['password_err'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            }
            if ($password !== $confirm) {
                $errors['confirm_password_err'] = 'Mật khẩu xác nhận không khớp.';
            }

            if (!empty($errors)) {
                $this->jsonResponse(false, 'Vui lòng kiểm tra lại thông tin.', ['errors' => $errors]);
            }

            $userIdCheck = $this->userModel->isValidPasswordResetToken($token);
            if (!$userIdCheck) {
                $this->jsonResponse(false, 'Token không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu lại.');
            }

            if ($this->userModel->updatePasswordById($userIdCheck, $password)) {
                $this->userModel->deletePasswordResetToken($token);
                $this->jsonResponse(
                    true,
                    'Mật khẩu đã được đặt lại thành công!',
                    ['redirect' => URLROOT . '/users/login']
                );
            } else {
                $this->jsonResponse(false, 'Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.');
            }
        } else {
            $data = [
                'title'      => 'Đặt lại mật khẩu - Creono',
                'token'      => $token,
                'errors'     => [],
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('users/reset_password', $data);
        }
    }
    public function kyc()
    {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Kiểm tra CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                $this->jsonResponse(false, 'CSRF token validation failed');
            }

            // Xử lý upload ảnh
            if (isset($_FILES['front_image']) && $_FILES['front_image']['error'] == 0) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['front_image']['tmp_name']);
                if (in_array($mime, $allowed)) {
                    $upload_dir = '../public/uploads/kyc/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $ext = pathinfo($_FILES['front_image']['name'], PATHINFO_EXTENSION);
                    $filename = time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['front_image']['tmp_name'], $upload_dir . $filename)) {
                        $front_url = '/uploads/kyc/' . $filename;
                    } else {
                        setFlash('error', 'Không thể upload ảnh mặt trước.');
                        header('location: ' . URLROOT . '/users/kyc');
                        exit();
                    }
                } else {
                    setFlash('error', 'Ảnh không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF.');
                    header('location: ' . URLROOT . '/users/kyc');
                    exit();
                }
            } else {
                setFlash('error', 'Vui lòng chọn ảnh mặt trước của giấy tờ.');
                header('location: ' . URLROOT . '/users/kyc');
                exit();
            }
            $this->kycModel->submitKyc((int)$_SESSION['user_id'], $front_url);

            setFlash('success', 'Đã gửi yêu cầu xác minh. Vui lòng chờ admin duyệt.');
            header('location: ' . URLROOT . '/users/profile');
            exit();
        } else {
            $data = [
                'title' => 'Xác minh danh tính',
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('users/kyc', $data);
        }
    }
}

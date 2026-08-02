<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/GuestMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Users extends Controller {
    private User $userModel;
    private UserProfile $userProfileModel;

    public function __construct() {
        $this->userModel =$this->model('User');
        $this->userProfileModel =$this->model('UserProfile');
    }

    // Chức năng UC-01: Đăng ký tài khoản
    public function register(): void {
        // Kiểm tra request là POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }

            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'csrf_token' => generateCsrfToken(),
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Validate Name
            if (empty($data['name'])) {
                $data['name_err'] = 'Vui lòng nhập họ và tên';
            }

            // Validate Email
            if (empty($data['email'])) {
                $data['email_err'] = 'Vui lòng nhập email';
            } else {
                // Kiểm tra email đã tồn tại chưa bằng Model
                if ($this->userModel->findByEmail($data['email'])) {
                    $data['email_err'] = 'Email này đã được sử dụng';
                }
            }

            // Validate Password
            if (empty($data['password'])) {
                $data['password_err'] = 'Vui lòng nhập mật khẩu';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Mật khẩu phải có ít nhất 6 ký tự';
            }

            // Validate Confirm Password
            if (empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Vui lòng xác nhận mật khẩu';
            } else {
                if ($data['password'] != $data['confirm_password']) {
                    $data['confirm_password_err'] = 'Mật khẩu không khớp';
                }
            }

            // Make sure errors are empty
            if (empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                
                // Hash Password (Lưu trực tiếp vào $data['password'] theo DB mới)
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                // Gọi hàm register() của Model
                if ($this->userModel->register($data)) {
                    // 1. Lấy thông tin user vừa mới lưu vào Database
                    $newUser = $this->userModel->getUserByEmail($data['email']);
                    
                    // 2. Gọi hàm tạo Session đăng nhập luôn (hàm này sẽ tự động chuyển hướng trang)
                    $this->createUserSession($newUser);
                    
                } else {
                    setFlash('error', 'Hệ thống đang bận, không thể đăng ký lúc này.');
                    $this->view('users/register', $data);
                }
            } else {
                // Load view with errors
                $this->view('users/register', $data);
            }

        } else {
            // Khởi tạo data rỗng khi load form lần đầu
            $data = [
                'name' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'csrf_token' => generateCsrfToken(),
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Load view
            $this->view('users/register', $data);
        }
    }
    
    public function login(): void {
        // Kiểm tra xem submit POST hay truy cập GET
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'csrf_token' => generateCsrfToken(),
                'email_err' => '',
                'password_err' => ''
            ];

            // Validate Email
            if (empty($data['email'])) {
                $data['email_err'] = 'Vui lòng nhập email';
            } elseif (!$this->userModel->findByEmail($data['email'])) {
                $data['email_err'] = 'Email không tồn tại trong hệ thống';
            }

            // Validate Password
            if (empty($data['password'])) {
                $data['password_err'] = 'Vui lòng nhập mật khẩu';
            }

            // Nếu không có lỗi form thì tiến hành Login
            if (empty($data['email_err']) && empty($data['password_err'])) {
                // Kiểm tra với Database
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);

                if ($loggedInUser) {
                    // Đăng nhập thành công -> Tạo Session
                    $this->createUserSession($loggedInUser);
                } else {
                    $data['password_err'] = 'Mật khẩu không chính xác';
                    $this->view('users/login', $data);
                }
            } else {
                $this->view('users/login', $data);
            }
        } else {
            // Hiển thị form rỗng (Truy cập lần đầu)
            $data = [
                'email' => '',
                'password' => '',
                'csrf_token' => generateCsrfToken(),
                'email_err' => '',
                'password_err' => ''
            ];
            $this->view('users/login', $data);
        }
    }

    // Hàm hỗ trợ lưu Session sau khi đăng nhập thành công
    public function createUserSession(object $user): void {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_name'] = $user->name; // Đổi từ user_email thành user_name cho thân thiện
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_role'] = $user->role; // Cột trong DB giờ là 'role' thay vì 'role_id'
    
    // Chuyển hướng dựa trên Role
    if ($_SESSION['user_role'] == 3) {
        header('location: ' . URLROOT . '/admin/dashboard'); // Admin
        exit();
    } elseif ($_SESSION['user_role'] == 2) {
        header('location: ' . URLROOT . '/seller/dashboard'); // Seller
        exit();
    } else {
        header('location: ' . URLROOT . '/products/index'); // Buyer
        exit();
    }
}
    
    // Đăng xuất (UC-04)
    public function logout(): void {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        session_destroy();
        header('location: ' . URLROOT . '/users/login');
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
                die('CSRF token validation failed');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $updateData = [
                'full_name' => trim($_POST['full_name']),
                'bio' => trim($_POST['bio'])
            ];

            // Xử lý upload Avatar nếu có
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $upload_dir = '../public/uploads/avatars/';
                $file_name = time() . '_' . basename($_FILES['avatar']['name']);
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                    $updateData['avatar_url'] = '/uploads/avatars/' . $file_name;
                }
            }

            if ($this->userProfileModel->updateProfile($_SESSION['user_id'], $updateData)) {
                setFlash('success', 'Cập nhật hồ sơ thành công!');
                header('location: ' . URLROOT . '/users/profile');
                exit();
            } else {
                setFlash('error', 'Đã xảy ra lỗi khi cập nhật.');
                header('location: ' . URLROOT . '/users/profile');
                exit();
            }
        }
    }

    // Xử lý Đổi mật khẩu
    public function changePassword(): void {
        // Đảm bảo user đã đăng nhập
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }

            // Lọc dữ liệu đầu vào
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            
            $data = [
                'old_password' => trim($_POST['old_password']),
                'new_password' => trim($_POST['new_password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'csrf_token' => generateCsrfToken(),
                'old_password_err' => '',
                'new_password_err' => '',
                'confirm_password_err' => ''
            ];

            // 1. Kiểm tra mật khẩu hiện tại
            if (empty($data['old_password'])) {
                $data['old_password_err'] = 'Vui lòng nhập mật khẩu hiện tại';
            } else {
                // Lấy thông tin user hiện tại từ Database
                $user = $this->userModel->findById($_SESSION['user_id']);
                
                // Kiểm tra xem pass nhập vào có khớp với mã băm trong DB không
                if (!$user || !password_verify($data['old_password'], $user->password)) {
                    $data['old_password_err'] = 'Mật khẩu hiện tại không chính xác';
                }
            }

            // 2. Kiểm tra mật khẩu mới
            if (empty($data['new_password'])) {
                $data['new_password_err'] = 'Vui lòng nhập mật khẩu mới';
            } elseif (strlen($data['new_password']) < 6) {
                $data['new_password_err'] = 'Mật khẩu phải có ít nhất 6 ký tự';
            }

            // 3. Kiểm tra xác nhận mật khẩu
            if (empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Vui lòng xác nhận mật khẩu mới';
            } else {
                if ($data['new_password'] != $data['confirm_password']) {
                    $data['confirm_password_err'] = 'Mật khẩu xác nhận không khớp';
                }
            }

            // Nếu không có lỗi nào, tiến hành đổi mật khẩu
            if (empty($data['old_password_err']) && empty($data['new_password_err']) && empty($data['confirm_password_err'])) {
                
                // Gọi hàm changePassword ở Model (Hàm này đã có logic tự băm mật khẩu mới)
                if ($this->userModel->changePassword($_SESSION['user_id'], $data['new_password'])) {
                    // Set thông báo thành công và chuyển hướng về trang Profile
                    setFlash('success', 'Đã cập nhật mật khẩu mới thành công!');
                    header('location: ' . URLROOT . '/users/profile');
                    exit();
                } else {
                    setFlash('error', 'Đã xảy ra lỗi hệ thống khi cập nhật mật khẩu.');
                    header('location: ' . URLROOT . '/users/changePassword');
                    exit();
                }
            } else {
                // Nếu có lỗi, load lại view hiển thị form lỗi
                $this->view('users/change_password', $data);
            }
        } else {
            // Khi truy cập bằng phương thức GET (Mới click vào link)
            $data = [
                'old_password' => '',
                'new_password' => '',
                'confirm_password' => '',
                'csrf_token' => generateCsrfToken(),
                'old_password_err' => '',
                'new_password_err' => '',
                'confirm_password_err' => ''
            ];

            // Tải View form đổi mật khẩu
            $this->view('users/change_password', $data);
        }
    }
}
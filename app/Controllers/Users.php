<?php
class Users extends Controller {
    private $userModel;

    public function __construct() {
        // Nạp Model User vào Controller
        $this->userModel = $this->model('User');
    }

    // Chức năng UC-01: Đăng ký tài khoản
    public function register() {
        // Kiểm tra request là POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
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
                    die('Hệ thống đang bận, không thể đăng ký lúc này.');
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
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Load view
            $this->view('users/register', $data);
        }
    }
    
    public function login() {
        // Kiểm tra xem submit POST hay truy cập GET
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
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
                'email_err' => '',
                'password_err' => ''
            ];
            $this->view('users/login', $data);
        }
    }

    // Hàm hỗ trợ lưu Session sau khi đăng nhập thành công
    public function createUserSession($user) {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_name'] = $user->name; // Đổi từ user_email thành user_name cho thân thiện
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_role'] = $user->role; // Cột trong DB giờ là 'role' thay vì 'role_id'
    
    // Chuyển hướng dựa trên Role
    if ($_SESSION['user_role'] == 3) {
        header('location: ' . URLROOT . '/admin/dashboard'); // Admin
    } elseif ($_SESSION['user_role'] == 2) {
        header('location: ' . URLROOT . '/seller/dashboard'); // Seller
    } else {
        header('location: ' . URLROOT . '/products/index'); // Buyer
    }
}
    
    // Đăng xuất (UC-04)
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        session_destroy();
        header('location: ' . URLROOT . '/users/login');
    }
}
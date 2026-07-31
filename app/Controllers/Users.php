<?php
class Users extends Controller {
    private $userModel;

    public function __construct() {
        // Nạp Model User vào Controller
        $this->userModel = $this->model('User');
    }

    public function register() {
        // Kiểm tra xem người dùng đang Submit form hay chỉ đang load trang
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // Lọc dữ liệu đầu vào để chống mã độc (Sanitize POST)
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            // Thu thập dữ liệu từ Form
            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // 1. Xác thực Email
            if (empty($data['email'])) {
                $data['email_err'] = 'Vui lòng nhập địa chỉ email';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Định dạng email không hợp lệ';
            } else {
                // Kiểm tra trùng Email trong Database
                if ($this->userModel->findByEmail($data['email'])) {
                    $data['email_err'] = 'Email này đã được sử dụng. Vui lòng chọn email khác.';
                }
            }

            // 2. Xác thực Mật khẩu
            if (empty($data['password'])) {
                $data['password_err'] = 'Vui lòng nhập mật khẩu';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Mật khẩu phải có ít nhất 6 ký tự';
            }

            // 3. Xác thực Nhập lại mật khẩu
            if (empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Vui lòng xác nhận lại mật khẩu';
            } else {
                if ($data['password'] != $data['confirm_password']) {
                    $data['confirm_password_err'] = 'Mật khẩu xác nhận không khớp';
                }
            }

            // ĐẢM BẢO KHÔNG CÓ LỖI NÀO THÌ MỚI CHO LƯU
            if (empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                
                // Băm mật khẩu (Hash Password) để bảo mật
                $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

                // Gom dữ liệu mảng để dùng hàm create() của BaseModel
                // LƯU Ý: id là BIGINT AUTO_INCREMENT nên hệ thống tự sinh số
                $insertData = [
                    'email' => $data['email'],
                    'password_hash' => $password_hash,
                    'role_id' => 2, // 2 tương ứng với BUYER trong bảng roles
                    'status' => 1   // 1 tương ứng với Active
                ];

                // Thực thi thêm vào database
                if ($this->userModel->create($insertData)) {
                    // Thành công: Chuyển hướng về trang đăng nhập
                    header('location: ' . URLROOT . '/users/login');
                } else {
                    die('Đã xảy ra lỗi hệ thống khi lưu vào cơ sở dữ liệu.');
                }

            } else {
                // Nếu có lỗi, load lại View kèm theo mảng lỗi để hiển thị
                $this->view('users/register', $data);
            }

        } else {
            // Khởi tạo mảng dữ liệu rỗng cho lần đầu tiên truy cập trang (GET)
            $data = [
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Nạp View form đăng ký
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
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_role'] = $user->role_id; // Đã cập nhật thành role_id theo database mới
        
        // Chuyển hướng về trang chủ
        header('location: ' . URLROOT);
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
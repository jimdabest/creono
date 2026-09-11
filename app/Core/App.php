<?php
class App {
    protected $currentController = 'Pages'; // Controller mặc định
    protected $currentMethod = 'index';     // Hàm mặc định
    protected $params = [];

    public function __construct() {
        // Tự động kiểm tra và thu hồi phiên nếu tài khoản đã bị khóa trong Database (UC40)
        if (isset($_SESSION['user_id']) && class_exists('Database')) {
            try {
                $db = new Database();
                $db->query("SELECT is_locked FROM users WHERE id = :id");
                $db->bind(':id', (int)$_SESSION['user_id']);
                $currentUser = $db->single();
                if ($currentUser && !empty($currentUser->is_locked) && (int)$currentUser->is_locked === 1) {
                    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['is_locked']);
                    session_regenerate_id(true);
                    if (function_exists('setFlash')) {
                        setFlash('error', 'Tài khoản của bạn đã bị khóa bởi Quản trị viên.', 'error');
                    }
                }
            } catch (Exception $e) {
                // Tiếp tục nếu có sự cố tạm thời
            }
        }

        $url = $this->getUrl();
        // echo "Đang gọi: " . $this->currentController . "/" . $this->currentMethod; // Debug
        // Kiểm tra xem file Controller có tồn tại không
        if (isset($url[0]) && file_exists('../app/Controllers/' . ucwords($url[0]) . '.php')) {
            $this->currentController = ucwords($url[0]);
            unset($url[0]);
        }

        require_once '../app/Controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;

        // Kiểm tra method có tồn tại trong Controller không
        // Ngăn chặn gọi trực tiếp các hàm base như view(), model()
        if (isset($url[1])) {
            if (!in_array(strtolower($url[1]), ['view', 'model']) && method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Lấy các tham số còn lại
        $this->params = $url ? array_values($url) : [];

        // Gọi hàm trong Controller và truyền tham số
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return ['Pages', 'index'];// Mặc định nếu không có URL
    }
}
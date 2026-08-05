<?php
class App {
    protected $currentController = 'Pages'; // Controller mặc định
    protected $currentMethod = 'index';     // Hàm mặc định
    protected $params = [];

    public function __construct() {
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
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
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
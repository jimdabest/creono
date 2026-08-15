<?php
class Orders extends Controller {
    private $orderModel;
    private $productModel;
    private $walletModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }
        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
        $this->walletModel = $this->model('Wallet');
    }

    public function checkout($productId) {
        $product = $this->productModel->getProductWithSeller($productId);
        $wallet = $this->walletModel->getWalletByUserId($_SESSION['user_id']);

        if (!$product) {
            die('Sản phẩm không tồn tại');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validate CSRF Token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('Lỗi bảo mật CSRF Token');
            }

            // Gọi Transaction xử lý thanh toán
            $isSuccess = $this->orderModel->processPayment(
                $_SESSION['user_id'], 
                $product->seller_id, 
                $product->id, 
                $product->title, 
                $product->price
            );

            if ($isSuccess) {
                // Sửa thành header location thay vì dùng redirect
                header('location: ' . URLROOT . '/downloads/file/' . $product->id);
                exit();
            } else {
                // Sửa thành header location thay vì dùng redirect
                header('location: ' . URLROOT . '/orders/checkout/' . $product->id);
                exit();
            }
        } else {
            $data = [
                'product' => $product,
                'wallet' => $wallet,
                'csrf_token' => generateCsrfToken() // Giả sử helper này đã được tạo
            ];
            $this->view('orders/checkout', $data);
        }
    }
}
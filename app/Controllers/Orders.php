<?php

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Orders extends Controller
{
    private Order $orderModel;
    private Product $productModel;
    private Cart $cartModel;
    private Wallet $walletModel;

    // Hằng số trạng thái
    private const PRODUCT_STATUS_APPROVED = 2;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
        $this->cartModel = $this->model('Cart');
        $this->walletModel = $this->model('Wallet');
    }

    /**
     * Hiển thị trang thanh toán cho 1 sản phẩm (GET)
     * URL: /orders/checkout/{productId}
     *
     * @param int|null $productId
     * @return void
     */
    public function checkout(?int $productId = null): void
    {
        if (!$productId) {
            setFlash('error', 'Sản phẩm không hợp lệ.');
            header('location: ' . URLROOT . '/carts/index');
            exit();
        }

        $product = $this->productModel->getProductDetail($productId);
        if (!$product || (int)$product->status !== self::PRODUCT_STATUS_APPROVED) {
            setFlash('error', 'Sản phẩm không tồn tại hoặc chưa được duyệt.');
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        // Kiểm tra không mua sản phẩm của chính mình
        $userId = (int)$_SESSION['user_id'];
        if ((int)$product->seller_id === $userId) {
            setFlash('error', 'Bạn không thể mua sản phẩm của chính mình.');
            header('location: ' . URLROOT . '/products/detail/' . $productId);
            exit();
        }

        // Lấy ví của user
        $wallet = $this->walletModel->getWalletByUserId($userId);
        if (!$wallet) {
            setFlash('error', 'Bạn chưa có ví điện tử. Vui lòng liên hệ hỗ trợ.');
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        $data = [
            'title'      => 'Thanh toán - Creono',
            'product'    => $product,
            'wallet'     => $wallet,
            'csrf_token' => generateCsrfToken()
        ];

        $this->view('orders/checkout', $data);
    }

    /**
     * Xử lý thanh toán (POST)
     * URL: /orders/process
     *
     * @return void
     */
    public function process(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        // Kiểm tra CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        if ($productId <= 0) {
            setFlash('error', 'Sản phẩm không hợp lệ.');
            header('location: ' . URLROOT . '/carts/index');
            exit();
        }

        $product = $this->productModel->getProductDetail($productId);
        if (!$product || (int)$product->status !== self::PRODUCT_STATUS_APPROVED) {
            setFlash('error', 'Sản phẩm không tồn tại hoặc không khả dụng.');
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $sellerId = (int)$product->seller_id;

        // Kiểm tra không mua sản phẩm của chính mình
        if ($sellerId === $userId) {
            setFlash('error', 'Bạn không thể mua sản phẩm của chính mình.');
            header('location: ' . URLROOT . '/products/detail/' . $productId);
            exit();
        }

        // Kiểm tra số dư ví
        $wallet = $this->walletModel->getWalletByUserId($userId);
        if (!$wallet || (float)$wallet->balance < (float)$product->price) {
            setFlash('error', 'Số dư ví không đủ để thanh toán. Vui lòng nạp thêm tiền.');
            header('location: ' . URLROOT . '/wallets/index');
            exit();
        }

        // Xử lý thanh toán
        $result = $this->orderModel->processPayment(
            $userId,
            $sellerId,
            $productId,
            $product->title,
            (float)$product->price
        );

        if ($result) {
            // Xóa sản phẩm khỏi giỏ hàng nếu có
            $cart = $this->cartModel->getOrCreateCart($userId);
            $this->cartModel->removeItem((int)$cart->id, $productId);

            setFlash('success', 'Thanh toán thành công! Bạn có thể tải tài liệu ngay.');
            header('location: ' . URLROOT . '/products/detail/' . $productId);
        } else {
            setFlash('error', 'Thanh toán thất bại. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
            header('location: ' . URLROOT . '/orders/checkout/' . $productId);
        }
        exit();
    }
}

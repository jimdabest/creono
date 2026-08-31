<?php

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Services/RefundService.php';

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
     * Hàm mặc định (fallback) khi truy cập sai URL
     */
    public function index(): void
    {
        header('location: ' . URLROOT . '/products/index');
        exit();
    }

    /**
     * Hiển thị trang thanh toán cho 1 sản phẩm (GET)
     * URL: /orders/checkout/{productId}
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
     * Xử lý thanh toán 1 sản phẩm (POST)
     * URL: /orders/process
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

            setFlash('success', 'Thanh toán thành công! Dưới đây là tài liệu của bạn.');
            header('location: ' . URLROOT . '/orders/myPurchases');
        } else {
            setFlash('error', 'Thanh toán thất bại. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
            header('location: ' . URLROOT . '/orders/checkout/' . $productId);
        }
        exit();
    }

    /**
     * Xử lý thanh toán toàn bộ giỏ hàng (POST)
     * URL: /orders/processCart
     */
    public function processCart(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: ' . URLROOT . '/carts/index');
            exit();
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        $userId = (int)$_SESSION['user_id'];
        
        // 1. Lấy thông tin giỏ hàng hiện tại
        $cart = $this->cartModel->getOrCreateCart($userId);
        $cartItems = $this->cartModel->getCartItems((int)$cart->id);
        $totalAmount = $this->cartModel->getCartTotal((int)$cart->id);

        if (empty($cartItems)) {
            setFlash('error', 'Giỏ hàng của bạn đang trống.');
            header('location: ' . URLROOT . '/carts/index');
            exit();
        }

        // 2. Kiểm tra ví người mua
        $wallet = $this->walletModel->getWalletByUserId($userId);
        if (!$wallet || (float)$wallet->balance < $totalAmount) {
            setFlash('error', 'Số dư ví không đủ để thanh toán toàn bộ giỏ hàng. Vui lòng nạp thêm tiền.');
            header('location: ' . URLROOT . '/wallets/index');
            exit();
        }

        // 3. Tiến hành giao dịch
        $isSuccess = $this->orderModel->processCartPayment($userId, $cartItems, $totalAmount);

        if ($isSuccess) {
            // Thanh toán thành công -> Xóa sạch giỏ hàng
            $this->cartModel->clearCart((int)$cart->id);
            
            setFlash('success', 'Thanh toán thành công ' . count($cartItems) . ' tài liệu! Bạn có thể tải file về ngay bây giờ.');
            header('location: ' . URLROOT . '/orders/myPurchases'); 
        } else {
            setFlash('error', 'Thanh toán thất bại. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
            header('location: ' . URLROOT . '/carts/index');
        }
        exit();
    }

    /**
     * Trang danh sách tài liệu đã mua (Kho tài liệu của tôi)
     * URL: /orders/myPurchases
     */
    public function myPurchases(): void
    {
        $userId = (int)$_SESSION['user_id'];
        $purchases = $this->orderModel->getPurchasedProducts($userId);

        $data = [
            'title' => 'Kho tài liệu của tôi - Creono',
            'purchases' => $purchases
        ];

        $this->view('orders/my_purchases', $data);
    }

    /**
     * Yêu cầu hoàn tiền cho đơn hàng (UC32)
     * URL: /orders/refund/{orderId}
     *
     * @param int|null $orderId
     * @return void
     */
    public function refund(?int $orderId = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$orderId) {
            header('location: ' . URLROOT . '/wallets/index');
            exit();
        }

        // Kiểm tra CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        $userId = (int)$_SESSION['user_id'];
        $userRole = (int)($_SESSION['user_role'] ?? 1);
        $order = $this->orderModel->getOrderById($orderId);

        if (!$order) {
            setFlash('error', 'Không tìm thấy đơn hàng cần hoàn tiền.');
            header('location: ' . URLROOT . '/wallets/index');
            exit();
        }

        // Kiểm tra quyền: Người mua hoặc Admin
        if ((int)$order->user_id !== $userId && $userRole !== 3) {
            setFlash('error', 'Bạn không có quyền yêu cầu hoàn tiền cho đơn hàng này.');
            header('location: ' . URLROOT . '/wallets/index');
            exit();
        }

        $reason = trim((string)($_POST['reason'] ?? 'Người mua yêu cầu hoàn tiền'));
        $isAdmin = ($userRole === 3);

        $result = RefundService::processRefund($orderId, $reason, $isAdmin);

        if ($result['success']) {
            setFlash('success', $result['message']);
        } else {
            setFlash('error', $result['message']);
        }

        header('location: ' . URLROOT . '/wallets/index');
        exit();
    }
}

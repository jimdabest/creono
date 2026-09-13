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
            'title'      => 'Kho tài liệu của tôi - Creono',
            'purchases'  => $purchases,
            'csrf_token' => generateCsrfToken()
        ];

        $this->view('orders/my_purchases', $data);
    }

    /**
     * Chấp nhận nhận tài liệu & Tải xuống (Chốt giao dịch vĩnh viễn, khóa hoàn tiền)
     * URL: /orders/acceptAndDownload/{orderId}
     */
    public function acceptAndDownload(?int $orderId = null): void
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
               || (isset($_POST['is_ajax']) && (string)$_POST['is_ajax'] === '1');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$orderId) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
                exit();
            }
            header('location: ' . URLROOT . '/orders/myPurchases');
            exit();
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Xác thực CSRF token không hợp lệ.']);
                exit();
            }
            die('CSRF token validation failed');
        }

        $userId = (int)$_SESSION['user_id'];
        $order = $this->orderModel->getOrderById($orderId);

        if (!$order || (int)$order->user_id !== $userId) {
            $msg = 'Đơn hàng không hợp lệ hoặc bạn không có quyền thao tác.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            setFlash('error', $msg);
            header('location: ' . URLROOT . '/orders/myPurchases');
            exit();
        }

        $downloadUrl = URLROOT . '/downloads/file/' . $order->product_id;

        if ((int)$order->status === Order::STATUS_RECEIVED) {
            $msg = 'Xác nhận nhận tài liệu thành công! Giao dịch đã được chốt hoàn tất.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success'      => true,
                    'download_url' => $downloadUrl,
                    'message'      => $msg
                ]);
                exit();
            }
            header('location: ' . $downloadUrl);
            exit();
        }

        if ((int)$order->status !== Order::STATUS_PAID) {
            $msg = 'Đơn hàng không ở trạng thái hợp lệ để xác nhận nhận hàng.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            setFlash('error', $msg);
            header('location: ' . URLROOT . '/orders/myPurchases');
            exit();
        }

        $success = $this->orderModel->confirmReceived($orderId, $userId);

        if ($success) {
            $msg = 'Xác nhận nhận tài liệu thành công! Giao dịch đã được chốt hoàn tất.';
            setFlash('success', $msg);
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success'      => true,
                    'download_url' => $downloadUrl,
                    'message'      => $msg
                ]);
                exit();
            }
            header('location: ' . $downloadUrl);
        } else {
            $msg = 'Không thể xác nhận nhận tài liệu. Vui lòng thử lại.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            setFlash('error', $msg);
            header('location: ' . URLROOT . '/orders/myPurchases');
        }
        exit();
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
            header('location: ' . URLROOT . '/orders/myPurchases');
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
            header('location: ' . URLROOT . '/orders/myPurchases');
            exit();
        }

        // Kiểm tra quyền: Người mua hoặc Admin
        if ((int)$order->user_id !== $userId && $userRole !== 3) {
            setFlash('error', 'Bạn không có quyền yêu cầu hoàn tiền cho đơn hàng này.');
            header('location: ' . URLROOT . '/orders/myPurchases');
            exit();
        }

        $reason = trim((string)($_POST['reason'] ?? ''));
        if ($reason === '') {
            $reason = 'Người mua yêu cầu hoàn tiền';
        }
        $isAdmin = ($userRole === 3);

        $result = RefundService::processRefund($orderId, $reason, $isAdmin);

        if ($result['success']) {
            setFlash('success', 'Hoàn tiền thành công! Tiền đã được hoàn về ví của bạn và quyền truy cập tài liệu đã bị hủy.');
        } else {
            setFlash('error', $result['message']);
        }

        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : URLROOT . '/orders/myPurchases';
        header('location: ' . $redirectUrl);
        exit();
    }
}

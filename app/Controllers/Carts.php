<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Carts extends Controller {
    private $cartModel;
    private $productModel;

    public function __construct() {
        $this->cartModel = $this->model('Cart');
        $this->productModel = $this->model('Product');
    }

    /**
     * Helper: Trả về JSON response
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data));
        exit();
    }

    /**
     * Lấy cart_id của user hiện tại (hoặc session cart)
     */
    private function getCartId(): ?int {
        if (isset($_SESSION['user_id'])) {
            $cart = $this->cartModel->getOrCreateCart((int)$_SESSION['user_id']);
            return (int)$cart->id;
        }
        return null;
    }

    /**
     * UC18: Thêm sản phẩm vào giỏ hàng (AJAX)
     * POST /carts/add
     */
    public function add(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

        if ($productId <= 0) {
            $this->jsonResponse(false, 'Sản phẩm không hợp lệ');
        }

        // Kiểm tra sản phẩm tồn tại
        $product = $this->productModel->findById($productId);
        if (!$product || $product->status != 2) {
            $this->jsonResponse(false, 'Sản phẩm không tồn tại hoặc chưa mở bán');
        }

        // Nếu user đã đăng nhập -> Lưu vào DB
        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];

            // Không cho phép seller tự mua sản phẩm của mình
            $productDetail = $this->productModel->getProductDetail($productId);
            if ($productDetail && isset($productDetail->seller_id) && (int)$productDetail->seller_id === $userId) {
                $this->jsonResponse(false, 'Bạn không thể mua sản phẩm của chính mình');
            }

            $cartId = $this->getCartId();
            $this->cartModel->addItem($cartId, $productId);
            $cartCount = $this->cartModel->getCartCount($cartId);

            $this->jsonResponse(true, 'Đã thêm sản phẩm vào giỏ hàng!', [
                'cart_count' => $cartCount,
                'product_title' => $product->title
            ]);
        } else {
            // Guest -> Lưu vào Session
            if (!isset($_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'] = [];
            }

            if (!in_array($productId, $_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'][] = $productId;
            }

            $cartCount = count($_SESSION['guest_cart']);

            $this->jsonResponse(true, 'Đã thêm sản phẩm vào giỏ hàng!', [
                'cart_count' => $cartCount,
                'product_title' => $product->title
            ]);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng (AJAX)
     * POST /carts/remove
     */
    public function remove(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

        if ($productId <= 0) {
            $this->jsonResponse(false, 'Sản phẩm không hợp lệ');
        }

        if (isset($_SESSION['user_id'])) {
            $cartId = $this->getCartId();
            $this->cartModel->removeItem($cartId, $productId);
            $cartCount = $this->cartModel->getCartCount($cartId);
            $cartTotal = $this->cartModel->getCartTotal($cartId);

            $this->jsonResponse(true, 'Đã xóa sản phẩm khỏi giỏ hàng', [
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'formatted_total' => number_format($cartTotal, 0, ',', '.') . ' ₫'
            ]);
        } else {
            if (isset($_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'] = array_values(array_diff($_SESSION['guest_cart'], [$productId]));
            }

            $cartCount = isset($_SESSION['guest_cart']) ? count($_SESSION['guest_cart']) : 0;

            $this->jsonResponse(true, 'Đã xóa sản phẩm khỏi giỏ hàng', [
                'cart_count' => $cartCount,
                'cart_total' => 0,
                'formatted_total' => '0 ₫'
            ]);
        }
    }

    /**
     * Hiển thị trang giỏ hàng
     * GET /carts/index
     */
    public function index(): void {
        $items = [];
        $total = 0.0;

        if (isset($_SESSION['user_id'])) {
            $cartId = $this->getCartId();
            $items = $this->cartModel->getCartItems($cartId);
            $total = $this->cartModel->getCartTotal($cartId);
        } else {
            // Guest items từ session
            $guestCartIds = $_SESSION['guest_cart'] ?? [];
            if (!empty($guestCartIds)) {
                foreach ($guestCartIds as $pId) {
                    $prod = $this->productModel->getProductDetail((int)$pId);
                    if ($prod && $prod->status == 2) {
                        $items[] = (object)[
                            'item_id' => $prod->id,
                            'product_id' => $prod->id,
                            'title' => $prod->title,
                            'price' => $prod->price,
                            'preview_url' => $prod->preview_url,
                            'rating' => $prod->rating,
                            'review_count' => $prod->review_count,
                            'description' => $prod->description,
                            'store_name' => $prod->store_name,
                            'added_at' => date('Y-m-d H:i:s')
                        ];
                        $total += (float)$prod->price;
                    }
                }
            }
        }

        $data = [
            'title' => 'Giỏ hàng của bạn - Creono',
            'items' => $items,
            'total' => $total,
            'cart_count' => count($items),
            'csrf_token' => generateCsrfToken()
        ];

        $this->view('carts/index', $data);
    }

    /**
     * Lấy số lượng giỏ hàng hiện tại (cho badge trên Navbar)
     * GET /carts/count
     */
    public function count(): void {
        $count = 0;
        if (isset($_SESSION['user_id'])) {
            $cartId = $this->getCartId();
            $count = $this->cartModel->getCartCount($cartId);
        } else {
            $count = isset($_SESSION['guest_cart']) ? count($_SESSION['guest_cart']) : 0;
        }

        $this->jsonResponse(true, 'OK', ['count' => $count]);
    }
}

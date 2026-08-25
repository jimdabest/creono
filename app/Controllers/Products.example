<?php
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Products extends Controller {
    private $productModel;
    private $reviewModel;
    private $favoriteModel;
    private $cartModel;

    public function __construct() {
        // Load Models
        $this->productModel  = $this->model('Product');
        $this->reviewModel   = $this->model('Review');
        $this->favoriteModel = $this->model('Favorite');
        $this->cartModel     = $this->model('Cart');
    }

    // Hiển thị danh sách sản phẩm ra trang chủ/chợ tài liệu
    public function index() {
        // Lấy dữ liệu từ Model
        $products = $this->productModel->getProducts();

        // Lấy danh sách ID đã favorite nếu user đã login
        $favoriteIds = [];
        if (isset($_SESSION['user_id'])) {
            $favoriteIds = $this->favoriteModel->getFavoriteProductIds((int)$_SESSION['user_id']);
        }

        $data = [
            'title' => 'Chợ Tài Liệu - Sân Sàn C2C',
            'products' => $products,
            'favorite_ids' => $favoriteIds,
            'csrf_token' => generateCsrfToken()
        ];

        // Đổ dữ liệu sang View
        $this->view('products/index', $data);
    }

    /**
     * UC15: Hiển thị trang chi tiết sản phẩm (kèm đánh giá, yêu thích & giỏ hàng)
     * URL: /products/detail/{id}
     */
    public function detail(int $productId = 0): void {
        if ($productId <= 0) {
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        // Lấy chi tiết sản phẩm
        $product = $this->productModel->getProductDetail($productId);

        if (!$product || $product->status != 2) {
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        // Lấy đánh giá (review) của sản phẩm
        $reviews = $this->reviewModel->getReviewsByProductId($productId);

        // Lấy reply cho từng review
        foreach ($reviews as &$review) {
            $review->replies = $this->reviewModel->getRepliesByReviewId($review->id);
        }
        unset($review);

        // Lấy thống kê rating
        $ratingStats = $this->reviewModel->getRatingStats($productId);

        // Trạng thái user
        $hasReviewed = false;
        $isFavorited = false;
        $inCart = false;

        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $hasReviewed = $this->reviewModel->hasUserReviewed($productId, $userId);
            $isFavorited = $this->favoriteModel->isFavorited($userId, $productId);
            $cart = $this->cartModel->getOrCreateCart($userId);
            $inCart = $this->cartModel->hasItem((int)$cart->id, $productId);
        } else {
            $inCart = isset($_SESSION['guest_cart']) && in_array($productId, $_SESSION['guest_cart']);
        }

        $isSeller = isset($_SESSION['user_id']) && 
                    isset($product->seller_id) && 
                    (int)$product->seller_id === (int)$_SESSION['user_id'];

        $data = [
            'title' => htmlspecialchars($product->title) . ' - Creono',
            'description' => htmlspecialchars(substr($product->description ?? '', 0, 150)),
            'product' => $product,
            'reviews' => $reviews,
            'rating_stats' => $ratingStats,
            'has_reviewed' => $hasReviewed,
            'is_favorited' => $isFavorited,
            'in_cart' => $inCart,
            'is_seller' => $isSeller,
            'csrf_token' => generateCsrfToken()
        ];

        $this->view('products/detail', $data);
    }
}
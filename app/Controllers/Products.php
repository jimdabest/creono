<?php
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Products extends Controller {
    private $productModel;
    private $reviewModel;

    public function __construct() {
        // Load Models
        $this->productModel = $this->model('Product');
        $this->reviewModel  = $this->model('Review');
    }

    // Hiển thị danh sách sản phẩm ra trang chủ/chợ tài liệu
    public function index() {
        // Lấy dữ liệu từ Model
        $products = $this->productModel->getProducts();

        $data = [
            'title' => 'Chợ Tài Liệu - Sân Sàn C2C',
            'products' => $products
        ];

        // Đổ dữ liệu sang View
        $this->view('products/index', $data);
    }

    /**
     * UC15: Hiển thị trang chi tiết sản phẩm (kèm đánh giá & bình luận)
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
            // Sản phẩm không tồn tại hoặc chưa được duyệt
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

        // Kiểm tra user đã đánh giá chưa
        $hasReviewed = false;
        if (isset($_SESSION['user_id'])) {
            $hasReviewed = $this->reviewModel->hasUserReviewed($productId, $_SESSION['user_id']);
        }

        // Kiểm tra user có phải seller của sản phẩm không
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
            'is_seller' => $isSeller,
            'csrf_token' => generateCsrfToken()
        ];

        $this->view('products/detail', $data);
    }
}
<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Reviews extends Controller {
    private $reviewModel;
    private $productModel;

    public function __construct() {
        $this->reviewModel = $this->model('Review');
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
     * UC34: Gửi đánh giá sản phẩm (AJAX)
     * POST /reviews/store
     */
    public function store(): void {
        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(false, 'Vui lòng đăng nhập để đánh giá', [
                'require_login' => true
            ]);
        }

        // Lấy dữ liệu từ POST
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

        // Validate product_id
        if ($productId <= 0) {
            $this->jsonResponse(false, 'Sản phẩm không hợp lệ');
        }

        // Validate sản phẩm tồn tại
        $product = $this->productModel->getProductDetail($productId);
        if (!$product) {
            $this->jsonResponse(false, 'Sản phẩm không tồn tại');
        }

        // Không cho phép seller tự đánh giá sản phẩm của mình
        if (isset($product->seller_id) && (int)$product->seller_id === (int)$_SESSION['user_id']) {
            $this->jsonResponse(false, 'Bạn không thể tự đánh giá sản phẩm của mình');
        }

        // Validate rating (1-5)
        if ($rating < 1 || $rating > 5) {
            $this->jsonResponse(false, 'Vui lòng chọn số sao đánh giá (1-5)');
        }

        // Validate comment
        if (empty($comment)) {
            $this->jsonResponse(false, 'Vui lòng nhập nội dung đánh giá');
        }

        if (mb_strlen($comment) > 1000) {
            $this->jsonResponse(false, 'Nội dung đánh giá không được vượt quá 1000 ký tự');
        }

        // Kiểm tra đã đánh giá chưa
        if ($this->reviewModel->hasUserReviewed($productId, $_SESSION['user_id'])) {
            $this->jsonResponse(false, 'Bạn đã đánh giá sản phẩm này rồi');
        }

        // Tạo review
        $reviewData = [
            'product_id' => $productId,
            'user_id' => $_SESSION['user_id'],
            'parent_id' => null,
            'rating' => $rating,
            'comment' => htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')
        ];

        if ($this->reviewModel->createReview($reviewData)) {
            // Lấy rating stats mới để trả về cho client
            $ratingStats = $this->reviewModel->getRatingStats($productId);

            $this->jsonResponse(true, 'Đánh giá đã được gửi thành công!', [
                'review' => [
                    'user_name' => $_SESSION['user_name'] ?? 'Ẩn danh',
                    'rating' => $rating,
                    'comment' => $comment,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                'rating_stats' => $ratingStats
            ]);
        } else {
            $this->jsonResponse(false, 'Đã xảy ra lỗi khi gửi đánh giá. Vui lòng thử lại.');
        }
    }

    /**
     * UC35: Gửi reply (bình luận con) cho một review (AJAX)
     * POST /reviews/reply
     */
    public function reply(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(false, 'Vui lòng đăng nhập để bình luận', [
                'require_login' => true
            ]);
        }

        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

        if ($productId <= 0 || $parentId <= 0) {
            $this->jsonResponse(false, 'Dữ liệu không hợp lệ');
        }

        if (empty($comment)) {
            $this->jsonResponse(false, 'Vui lòng nhập nội dung bình luận');
        }

        if (mb_strlen($comment) > 500) {
            $this->jsonResponse(false, 'Nội dung bình luận không được vượt quá 500 ký tự');
        }

        // Kiểm tra review cha tồn tại
        $parentReview = $this->reviewModel->findById($parentId);
        if (!$parentReview) {
            $this->jsonResponse(false, 'Đánh giá gốc không tồn tại');
        }

        $replyData = [
            'product_id' => $productId,
            'user_id' => $_SESSION['user_id'],
            'parent_id' => $parentId,
            'rating' => null,
            'comment' => htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')
        ];

        if ($this->reviewModel->createReview($replyData)) {
            $this->jsonResponse(true, 'Phản hồi đã được gửi!', [
                'reply' => [
                    'user_name' => $_SESSION['user_name'] ?? 'Ẩn danh',
                    'comment' => $comment,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            $this->jsonResponse(false, 'Đã xảy ra lỗi. Vui lòng thử lại.');
        }
    }

    /**
     * Lấy danh sách đánh giá của sản phẩm (AJAX – cho lazy load)
     * GET /reviews/list/{product_id}
     */
    public function list(int $productId = 0): void {
        if ($productId <= 0) {
            $this->jsonResponse(false, 'Sản phẩm không hợp lệ');
        }

        $reviews = $this->reviewModel->getReviewsByProductId($productId);

        // Lấy reply cho mỗi review
        $reviewsWithReplies = [];
        foreach ($reviews as $review) {
            $reviewObj = (array)$review;
            $reviewObj['replies'] = $this->reviewModel->getRepliesByReviewId($review->id);
            $reviewObj['reply_count'] = count($reviewObj['replies']);
            $reviewsWithReplies[] = $reviewObj;
        }

        $ratingStats = $this->reviewModel->getRatingStats($productId);

        $this->jsonResponse(true, 'OK', [
            'reviews' => $reviewsWithReplies,
            'rating_stats' => $ratingStats
        ]);
    }
}

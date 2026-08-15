<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Favorites extends Controller {
    private $favoriteModel;

    public function __construct() {
        $this->favoriteModel = $this->model('Favorite');
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
     * UC17: Toggle yêu thích (thêm/xoá) - AJAX
     * POST /favorites/toggle
     */
    public function toggle(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(false, 'Vui lòng đăng nhập để thêm yêu thích', [
                'require_login' => true
            ]);
        }

        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

        if ($productId <= 0) {
            $this->jsonResponse(false, 'Sản phẩm không hợp lệ');
        }

        $userId = (int)$_SESSION['user_id'];

        if ($this->favoriteModel->isFavorited($userId, $productId)) {
            // Đã yêu thích → Xóa
            $this->favoriteModel->removeFavorite($userId, $productId);
            $this->jsonResponse(true, 'Đã bỏ yêu thích', [
                'action' => 'removed',
                'is_favorited' => false,
                'count' => $this->favoriteModel->countByUserId($userId)
            ]);
        } else {
            // Chưa yêu thích → Thêm
            $this->favoriteModel->addFavorite($userId, $productId);
            $this->jsonResponse(true, 'Đã thêm vào yêu thích', [
                'action' => 'added',
                'is_favorited' => true,
                'count' => $this->favoriteModel->countByUserId($userId)
            ]);
        }
    }

    /**
     * Trang danh sách yêu thích của user
     * GET /favorites/index
     */
    public function index(): void {
        AuthMiddleware::check();

        $userId = (int)$_SESSION['user_id'];
        $favorites = $this->favoriteModel->getFavoritesByUserId($userId);

        $data = [
            'title' => 'Sản phẩm yêu thích - Creono',
            'favorites' => $favorites,
            'csrf_token' => generateCsrfToken()
        ];

        $this->view('favorites/index', $data);
    }

    /**
     * Kiểm tra trạng thái yêu thích (cho AJAX check)
     * GET /favorites/status/{product_id}
     */
    public function status(int $productId = 0): void {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(true, 'OK', ['is_favorited' => false]);
        }

        $isFav = $this->favoriteModel->isFavorited((int)$_SESSION['user_id'], $productId);
        $this->jsonResponse(true, 'OK', ['is_favorited' => $isFav]);
    }
}

<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
// Bắt buộc nạp thêm 2 Helper này để xử lý Form
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Seller extends Controller
{
    private Product $productModel;
    private Order $orderModel;

    public function __construct()
    {
        RoleMiddleware::check([2]); // Chỉ Seller (role=2)
        $this->productModel = $this->model('Product');
        $this->orderModel = $this->model('Order');
    }

    public function dashboard()
    {
        // Lấy dữ liệu cho dashboard
        $data = [
            'title' => 'Dashboard Người bán',
            'user' => $this->model('User')->getUserWithProfile($_SESSION['user_id']),

            // Stats
            'total_products' => $this->productModel->getSellerProductsCount($_SESSION['user_id']),
            'total_revenue' => $this->orderModel->getSellerRevenue($_SESSION['user_id']),
            'avg_rating' => $this->productModel->getSellerAvgRating($_SESSION['user_id']),
            'total_reviews' => $this->productModel->getSellerTotalReviews($_SESSION['user_id']),
            'pending_orders' => $this->orderModel->getSellerPendingOrdersCount($_SESSION['user_id']),

            // Recent orders
            'recent_orders' => $this->orderModel->getSellerRecentOrders($_SESSION['user_id'], 5),

            // Top products
            'top_products' => $this->productModel->getSellerTopProducts($_SESSION['user_id'], 5)
        ];

        $this->view('seller/dashboard', $data);
    }

    public function stats(): void
    {
        RoleMiddleware::check([2]);

        // Lấy thêm dữ liệu thống kê chi tiết
        $userId = (int)$_SESSION['user_id'];

        // Lấy thông tin cửa hàng
        $storeModel = $this->model('Store');
        $store = $storeModel->getStoreByUserId($userId);

        $data = [
            'title' => 'Thống kê chi tiết',
            'store' => $store,
            'total_products' => $this->productModel->getSellerProductsCount($userId),
            'total_revenue' => $this->orderModel->getSellerRevenue($userId),
            'avg_rating' => $this->productModel->getSellerAvgRating($userId),
            'total_reviews' => $this->productModel->getSellerTotalReviews($userId),
            'pending_orders' => $this->orderModel->getSellerPendingOrdersCount($userId),
            'top_products' => $this->productModel->getSellerTopProducts($userId, 5),
        ];

        $this->view('seller/stats', $data);
    }

    public function index()
    {
        header('location: ' . URLROOT . '/seller/dashboard');
        exit();
    }

    // ==============================================================
    // THÊM MỚI: HÀM XỬ LÝ FORM KHÁNG CÁO AI
    // ==============================================================
    public function submitAppeal()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Xác thực bảo mật CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('Lỗi bảo mật CSRF.');
            }
            
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $reason = trim($_POST['reason'] ?? '');
            $evidenceUrl = trim($_POST['evidence_url'] ?? '');
            $sellerId = (int)$_SESSION['user_id'];

            // Xác thực quyền sở hữu: Seller chỉ được khiếu nại sản phẩm của chính mình
            $product = $this->productModel->getProductWithSeller($productId);
            
            if ($product && (int)$product->seller_id === $sellerId) { 
                // Gọi model AiAppeal để lưu dữ liệu
                $appealModel = $this->model('AiAppeal');
                $appealModel->create([
                    'product_id' => $productId,
                    'seller_id' => $sellerId,
                    'reason' => htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'),
                    'evidence_url' => htmlspecialchars($evidenceUrl, ENT_QUOTES, 'UTF-8'),
                    'status' => 1 // Trạng thái: Pending
                ]);
                setFlash('success', 'Đã gửi yêu cầu kháng cáo nhãn AI thành công. Admin sẽ phản hồi sớm.');
            } else {
                setFlash('error', 'Sản phẩm không hợp lệ hoặc bạn không có quyền thao tác.');
            }
            
            // Quay về trang quản lý sản phẩm
            header('location: ' . URLROOT . '/products/manage');
            exit();
        } else {
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }
    }
}
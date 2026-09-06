<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';

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

    // Thêm vào class Seller
    public function stats(): void
    {
        RoleMiddleware::check([2]);

        // Lấy thêm dữ liệu thống kê chi tiết
        $userId = (int)$_SESSION['user_id'];

        // Lấy thông tin cửa hàng
        $storeModel = $this->model('Store');
        $store = $storeModel->getStoreByUserId($userId);

        // Lấy tổng doanh thu theo từng sản phẩm (có thể thêm query)
        // Hiện tại chưa có sẵn, ta có thể lấy từ order items kết hợp với product
        // Nhưng để đơn giản, lấy dữ liệu từ các phương thức có sẵn

        $data = [
            'title' => 'Thống kê chi tiết',
            'store' => $store,
            'total_products' => $this->productModel->getSellerProductsCount($userId),
            'total_revenue' => $this->orderModel->getSellerRevenue($userId),
            'avg_rating' => $this->productModel->getSellerAvgRating($userId),
            'total_reviews' => $this->productModel->getSellerTotalReviews($userId),
            'pending_orders' => $this->orderModel->getSellerPendingOrdersCount($userId),
            'top_products' => $this->productModel->getSellerTopProducts($userId, 5),
            // Có thể thêm các thống kê khác
        ];

        $this->view('seller/stats', $data);
    }

    public function index()
    {
        header('location: ' . URLROOT . '/seller/dashboard');
        exit();
    }
}

<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';

class Seller extends Controller {
    private $productModel;
    private $orderModel;

    public function __construct() {
        RoleMiddleware::check([2]); // Chỉ Seller (role=2)
        $this->productModel = $this->model('Product');
        $this->orderModel = $this->model('Order');
    }

    public function dashboard() {
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
}
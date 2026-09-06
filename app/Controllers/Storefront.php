<?php

declare(strict_types=1);

require_once '../app/Helpers/flash_helper.php';
require_once '../app/Helpers/csrf_helper.php';

class Storefront extends Controller
{
    private Store $storeModel;
    private Product $productModel;
    private Review $reviewModel;
    private Order $orderModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->storeModel = $this->model('Store');
        $this->productModel = $this->model('Product');
        $this->reviewModel = $this->model('Review');
        $this->orderModel = $this->model('Order');
        $this->categoryModel = $this->model('Category');
    }

    public function index(?string $slug = null): void
    {
        if (empty($slug)) {
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        $store = $this->storeModel->getStoreBySlug($slug);

        if (!$store || (int)$store->status !== 2 || empty($store->slug)) {
            http_response_code(404);
            $this->view('pages/error', ['title' => 'Cửa hàng không tồn tại']);
            exit();
        }

        $category = $_GET['category'] ?? '';
        $sort = $_GET['sort'] ?? 'newest';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $orderBy = $this->getOrderBy($sort);

        $products = $this->productModel->getProductsByStoreId((int)$store->id, $limit, $offset, $orderBy, $category);
        $totalProducts = $this->productModel->countProductsByStoreId((int)$store->id, $category);

        $avgRating = $this->reviewModel->getStoreAvgRating((int)$store->id);
        $totalDownloads = $this->orderModel->getStoreTotalDownloads((int)$store->id);
        $categories = $this->categoryModel->getAllOrdered();

        $isOwner = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$store->user_id;

        $data = [
            'title' => htmlspecialchars($store->name) . ' - Creono',
            'description' => htmlspecialchars(substr($store->description ?? '', 0, 160)),
            'store' => $store,
            'products' => $products,
            'categories' => $categories,
            'avg_rating' => $avgRating,
            'total_downloads' => $totalDownloads,
            'total_products' => $totalProducts,
            'current_category' => $category,
            'current_sort' => $sort,
            'is_owner' => $isOwner,
            'pagination' => [
                'current' => $page,
                'total' => ceil($totalProducts / $limit),
                'base_url' => URLROOT . '/storefront/' . $slug . '?'
            ],
            'csrf_token' => generateCsrfToken()
        ];

        // Đảm bảo anh đã tạo file view tại: app/Views/store/view.php
        $this->view('store/view', $data);
    }

    private function getOrderBy(string $sort): string
    {
        return match ($sort) {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'rating'     => 'p.rating DESC, p.review_count DESC',
            'popular'    => 'p.download_count DESC',
            default      => 'p.created_at DESC'
        };
    }
}

<?php
class Pages extends Controller
{
    private Product $productModel;
    private Category $categoryModel;
    private Testimonial $testimonialModel;
    private User $userModel;

    public function __construct()
    {
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        $this->testimonialModel = $this->model('Testimonial');
        $this->userModel = $this->model('User');
    }

    public function index()
    {
        // Lấy sản phẩm nổi bật
        $featuredProducts = $this->productModel->getProducts();

        // Lấy danh mục có sản phẩm
        $categories = $this->categoryModel->getCategoriesWithProducts();

        // Lấy đánh giá nổi bật
        $testimonials = $this->testimonialModel->getFeatured(3);

        // Thống kê sử dụng các hàm từ Model
        $stats = [
            'products' => $this->productModel->getTotalProducts(),
            'users' => $this->userModel->getTotalUsers(),
            'sellers' => $this->userModel->getTotalSellers(),
            'rating' => '4.8'
        ];

        $data = [
            'title' => 'Chào mừng đến với Creono',
            'description' => 'Nền tảng mua bán tài liệu số C2C hàng đầu.',
            'featured_products' => $featuredProducts,
            'categories' => $categories,
            'testimonials' => $testimonials,
            'stats' => $stats
        ];

        $this->view('pages/index', $data);
    }

    public function about()
    {
        $data = [
            'title' => 'Về chúng tôi'
        ];
        $this->view('pages/about', $data);
    }

    public function js_test()
    {
        $data = [
            'title' => 'JavaScript Test'
        ];
        $this->view('pages/js_test', $data);
    }
}

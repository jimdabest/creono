<?php
class Products extends Controller {
    private $productModel;

    public function __construct() {
        // Load Model Product
        $this->productModel = $this->model('Product');
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
}
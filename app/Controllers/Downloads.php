<?php
class Downloads extends Controller {
    private $orderModel;
    private $productModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('location: ' . URLROOT . '/users/login');
            exit();
        }
        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
    }

    public function file($productId) {
        $userId = $_SESSION['user_id'];
        $product = $this->productModel->getProductWithSeller($productId);

        if (!$product) {
            die('Sản phẩm không tồn tại.');
        }

        // Kiểm tra quyền: Là chủ sản phẩm HOẶC đã mua thành công
        if ($product->seller_id != $userId && !$this->orderModel->hasPurchased($userId, $productId)) {
            die('Bạn không có quyền tải tài liệu này. Vui lòng mua sản phẩm trước.');
        }

        // Lấy link file từ DB
        $document = $this->productModel->getDocumentByProductId($productId);

        if ($document && !empty($document->file_url)) {
            // Ghi log download
            $this->productModel->logDownload($userId, $productId, $_SERVER['REMOTE_ADDR']);

            // Chuyển hướng tới link tải file
            header("Location: " . $document->file_url);
            exit();
        } else {
            die('Không tìm thấy file tài liệu để tải.');
        }
    }
}
<?php

declare(strict_types=1);

class Downloads extends Controller
{
    private Order $orderModel;
    private Product $productModel;

    public function __construct()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            setFlash('error', 'Vui lòng đăng nhập để tải tài liệu.');
            header('location: ' . URLROOT . '/users/login');
            exit();
        }

        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
    }

    /**
     * Tải file tài liệu của sản phẩm
     * 
     * @param int $productId ID của sản phẩm
     * @return void
     */
    public function file(int $productId): void
    {
        $userId = (int)$_SESSION['user_id'];

        // Lấy thông tin sản phẩm kèm seller_id
        $product = $this->productModel->getProductWithSeller($productId);

        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại.');
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        // Kiểm tra quyền: là chủ sản phẩm HOẶC đã mua thành công
        $isOwner = (int)$product->seller_id === $userId;
        $hasPurchased = $this->orderModel->hasPurchased($userId, $productId);

        if (!$isOwner && !$hasPurchased) {
            setFlash('error', 'Bạn không có quyền tải tài liệu này. Vui lòng mua sản phẩm trước.');
            header('location: ' . URLROOT . '/products/detail/' . $productId);
            exit();
        }

        // Lấy file URL từ bảng documents
        $document = $this->productModel->getDocumentByProductId($productId);

        if ($document && !empty($document->file_url)) {
            // Ghi log download
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $this->productModel->logDownload($userId, $productId, $ip);

            // Chuyển hướng đến file
            header('Location: ' . $document->file_url);
            exit();
        } else {
            setFlash('error', 'Không tìm thấy file tài liệu để tải.');
            header('location: ' . URLROOT . '/products/detail/' . $productId);
            exit();
        }
    }
}

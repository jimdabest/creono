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

        // Kiểm tra quyền: là chủ sản phẩm HOẶC đã xác nhận 'Đã nhận'
        $isOwner = (int)$product->seller_id === $userId;

        if (!$isOwner) {
            $order = $this->orderModel->getOrderByUserAndProduct($userId, $productId);

            if (!$order) {
                setFlash('error', 'Bạn không có quyền tải tài liệu này. Vui lòng mua sản phẩm trước.');
                header('location: ' . URLROOT . '/products/detail/' . $productId);
                exit();
            }

            $status = (int)$order->status;

            // Chặn cố tình tải file khi chưa bấm 'Chấp nhận & Tải xuống'
            if ($status === Order::STATUS_PAID) {
                setFlash('warning', '⚠️ Bạn chưa bấm "Chấp nhận & Tải xuống" cho tài liệu này. Vui lòng xác nhận nhận hàng trong Kho tài liệu để tải file!');
                header('location: ' . URLROOT . '/orders/myPurchases');
                exit();
            }

            // Đơn hàng đã hoàn tiền hoặc đã hủy -> Hủy quyền truy cập
            if ($status === Order::STATUS_REFUNDED || $status === Order::STATUS_CANCELLED) {
                setFlash('error', 'Quyền truy cập tài liệu này đã bị hủy (Đơn hàng đã hoàn tiền hoặc bị hủy).');
                header('location: ' . URLROOT . '/orders/myPurchases');
                exit();
            }

            // Chỉ cho phép tải khi đơn hàng đã ở trạng thái 5 (Đã nhận)
            if ($status !== Order::STATUS_RECEIVED) {
                setFlash('error', 'Tài liệu này hiện không ở trạng thái khả dụng để tải về.');
                header('location: ' . URLROOT . '/orders/myPurchases');
                exit();
            }
        }

        // Ghi log download
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->productModel->logDownload($userId, $productId, $ip);

        // Lấy thông tin document nếu có
        $document = $this->productModel->getDocumentByProductId($productId);
        $physicalFile = null;

        if ($document && !empty($document->file_url)) {
            $url = $document->file_url;
            if (!preg_match('/^https?:\/\//i', $url)) {
                $checkPaths = [
                    dirname(APPROOT) . '/public' . (str_starts_with($url, '/') ? $url : '/' . $url),
                    dirname(APPROOT) . (str_starts_with($url, '/') ? $url : '/' . $url)
                ];
                foreach ($checkPaths as $p) {
                    if (file_exists($p) && is_file($p)) {
                        $physicalFile = $p;
                        break;
                    }
                }
            }
        }

        // Trường hợp 1: Có file vật lý thực tế trên server
        if ($physicalFile && file_exists($physicalFile)) {
            $fileName = basename($physicalFile);
            $mimeType = mime_content_type($physicalFile) ?: 'application/octet-stream';
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($physicalFile));
            readfile($physicalFile);
            exit();
        }

        // Trường hợp 2: Link external hợp lệ thực sự (không phải AWS mock từ sample_data)
        if ($document && !empty($document->file_url) && preg_match('/^https?:\/\//i', $document->file_url) && !str_contains($document->file_url, 's3.aws.com/files/')) {
            header('Location: ' . $document->file_url);
            exit();
        }

        // Trường hợp 3: Tự động cung cấp file mẫu (Sample/Dummy File) khi chưa có file vật lý thực tế
        $storeName = $product->store_name ?? 'Creono Store';
        $orderNum = isset($order) && !empty($order->order_number) ? $order->order_number : ('ORD-' . $productId);

        $safeTitle = preg_replace('/[^\p{L}\p{N}_\-]/u', '_', (string)$product->title);
        $safeTitle = trim((string)preg_replace('/_+/', '_', $safeTitle), '_');
        if (empty($safeTitle)) {
            $safeTitle = 'Tai_lieu_' . $productId;
        }
        $downloadFileName = $safeTitle . '_sample.txt';

        $content = "======================================================================\n";
        $content .= "       CREONO DIGITAL MARKETPLACE - TÀI LIỆU SẢN PHẨM MẪU\n";
        $content .= "======================================================================\n\n";
        $content .= "Tên tài liệu : {$product->title}\n";
        $content .= "Mã tài liệu  : #{$productId}\n";
        $content .= "Cửa hàng     : {$storeName}\n";
        $content .= "Mã đơn hàng  : #{$orderNum}\n";
        $content .= "Thời gian tải: " . date('d/m/Y H:i:s') . "\n";
        $content .= "Trạng thái   : Đã nhận (Status: 5 - Chốt giao dịch hoàn tất vĩnh viễn)\n\n";
        $content .= "----------------------------------------------------------------------\n";
        $content .= "THÔNG BÁO TỪ HỆ THỐNG:\n";
        $content .= "- Đây là file tài liệu mẫu tự động phục vụ môi trường kiểm thử (Testing).\n";
        $content .= "- Người mua đã xác nhận nhận hàng thành công, giao dịch đã được chốt.\n";
        $content .= "- Tính năng Hoàn tiền đã khóa vĩnh viễn cho đơn hàng này.\n";
        $content .= "----------------------------------------------------------------------\n\n";
        $content .= "Mô tả sản phẩm:\n" . strip_tags((string)($product->description ?? 'Không có mô tả chi tiết.')) . "\n\n";
        $content .= "Cảm ơn bạn đã tin tưởng và sử dụng nền tảng Creono!\n";

        header('Content-Description: File Transfer');
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $content;
        exit();
    }
}

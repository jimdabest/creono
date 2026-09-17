<?php

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Helpers/mail_helper.php';

class Stores extends Controller
{
    private Store $storeModel;

    public function __construct()
    {
        // Bắt buộc áp dụng RoleMiddleware::check([1]) để bảo mật chỉ Admin mới truy cập được
        RoleMiddleware::check([1]);

        $this->storeModel = $this->model('Store');
    }

    /**
     * Mặc định chuyển về trang danh sách chờ duyệt
     */
    public function index(): void
    {
        $this->pending();
    }

    /**
     * Màn hình danh sách hồ sơ cửa hàng chờ phê duyệt (status = 0)
     * URL: /admin/stores/pending
     */
    public function pending(): void
    {
        $pendingStores = $this->storeModel->getPendingStores();

        $data = [
            'title' => 'Duyệt đăng ký cửa hàng - Creono Admin',
            'stores' => $pendingStores,
            'csrf_token' => generateCsrfToken()
        ];

        $this->view('admin/stores/pending', $data);
    }

    /**
     * Xử lý Phê duyệt hồ sơ cửa hàng (AJAX)
     * URL: /admin/stores/approve/{id}
     */
    public function approve(?int $id = null): void
    {
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Phương thức yêu cầu không hợp lệ.');
            return;
        }

        // Kiểm tra CSRF Token
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !verifyCsrfToken($token)) {
            $this->jsonResponse(false, 'Phiên bảo mật không hợp lệ (CSRF Token mismatch). Vui lòng thử lại.');
            return;
        }

        $storeId = (int)($id ?? ($_POST['store_id'] ?? 0));
        if ($storeId <= 0) {
            $this->jsonResponse(false, 'Mã cửa hàng không hợp lệ.');
            return;
        }

        // Kiểm tra thông tin hồ sơ cửa hàng
        $store = $this->storeModel->getStoreWithApplicant($storeId);
        if (!$store) {
            $this->jsonResponse(false, 'Không tìm thấy hồ sơ cửa hàng yêu cầu.');
            return;
        }

        if ((int)$store->status === 1) {
            $this->jsonResponse(false, 'Hồ sơ cửa hàng này đã được phê duyệt trước đó.');
            return;
        }

        // Gọi Model xử lý duyệt: cập nhật status = 1 và cập nhật role User từ Buyer thành Seller (2)
        $success = $this->storeModel->approveStore($storeId);

        if ($success) {
            // Gửi email thông báo cho người đăng ký nếu có email
            if (!empty($store->applicant_email) && function_exists('sendEmail')) {
                $subject = 'Chúc mừng! Hồ sơ đăng ký cửa hàng "' . $store->name . '" đã được phê duyệt';
                $body = 'Xin chào ' . htmlspecialchars($store->applicant_name) . ",\n\n"
                    . 'Hồ sơ đăng ký cửa hàng "' . htmlspecialchars($store->name) . '" của bạn trên Creono đã được Quản trị viên phê duyệt thành công!\n'
                    . 'Tài khoản của bạn đã được nâng cấp lên Người bán (Seller). Bạn có thể đăng nhập và bắt đầu đăng tải tài liệu ngay bây giờ.\n\n'
                    . 'Trân trọng,\nĐội ngũ Creono';
                @sendEmail($store->applicant_email, $subject, $body);
            }

            $this->jsonResponse(true, 'Đã phê duyệt hồ sơ cửa hàng thành công! Tài khoản đã được nâng cấp lên Người bán.');
        } else {
            $this->jsonResponse(false, 'Đã xảy ra lỗi khi phê duyệt cửa hàng trong hệ thống.');
        }
    }

    /**
     * Xử lý Từ chối hồ sơ cửa hàng kèm lý do (AJAX)
     * URL: /admin/stores/reject/{id}
     */
    public function reject(?int $id = null): void
    {
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Phương thức yêu cầu không hợp lệ.');
            return;
        }

        // Kiểm tra CSRF Token
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !verifyCsrfToken($token)) {
            $this->jsonResponse(false, 'Phiên bảo mật không hợp lệ (CSRF Token mismatch). Vui lòng thử lại.');
            return;
        }

        $storeId = (int)($id ?? ($_POST['store_id'] ?? 0));
        if ($storeId <= 0) {
            $this->jsonResponse(false, 'Mã cửa hàng không hợp lệ.');
            return;
        }

        $reason = trim($_POST['reason'] ?? '');
        if (empty($reason)) {
            $this->jsonResponse(false, 'Vui lòng nhập lý do từ chối hồ sơ đăng ký.');
            return;
        }

        // Kiểm tra thông tin hồ sơ
        $store = $this->storeModel->getStoreWithApplicant($storeId);
        if (!$store) {
            $this->jsonResponse(false, 'Không tìm thấy hồ sơ cửa hàng yêu cầu.');
            return;
        }

        // Cập nhật status = 2 (Từ chối) và lưu lý do
        $success = $this->storeModel->rejectStore($storeId, $reason);

        if ($success) {
            // Gửi email thông báo từ chối cho người đăng ký
            if (!empty($store->applicant_email) && function_exists('sendEmail')) {
                $subject = 'Thông báo về hồ sơ đăng ký cửa hàng "' . $store->name . '" trên Creono';
                $body = 'Xin chào ' . htmlspecialchars($store->applicant_name) . ",\n\n"
                    . 'Rất tiếc, hồ sơ đăng ký cửa hàng "' . htmlspecialchars($store->name) . '" của bạn chưa đáp ứng yêu cầu của nền tảng Creono.\n\n'
                    . 'Lý do từ chối: ' . htmlspecialchars($reason) . "\n\n"
                    . 'Vui lòng kiểm tra lại thông tin và nộp lại hồ sơ nếu cần thiết.\n\n'
                    . 'Trân trọng,\nĐội ngũ Creono';
                @sendEmail($store->applicant_email, $subject, $body);
            }

            $this->jsonResponse(true, 'Đã từ chối hồ sơ đăng ký cửa hàng thành công.');
        } else {
            $this->jsonResponse(false, 'Đã xảy ra lỗi khi từ chối hồ sơ cửa hàng.');
        }
    }

    /**
     * Helper: Trả về JSON format chuẩn theo yêu cầu
     * Format: {"success": true|false, "message": "..."}
     */
    private function jsonResponse(bool $success, string $message): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

<?php

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';

class Reports extends Controller
{
    private Report $reportModel;
    private Product $productModel;
    private Review $reviewModel;
    private Store $storeModel;
    private User $userModel;

    public function __construct()
    {
        // Bắt buộc đăng nhập để báo cáo
        AuthMiddleware::check();

        $this->reportModel = $this->model('Report');
        $this->productModel = $this->model('Product');
        $this->reviewModel  = $this->model('Review');
        $this->storeModel   = $this->model('Store');
        $this->userModel    = $this->model('User');
    }

    /**
     * Helper: Trả về JSON response
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data));
        exit();
    }

    /**
     * Hiển thị form báo cáo (GET)
     * URL: /reports/create?target_type=PRODUCT&target_id=123
     */
    public function create(): void
    {
        $targetType = isset($_GET['target_type']) ? strtoupper(trim($_GET['target_type'])) : '';
        $targetId   = isset($_GET['target_id']) ? (int)$_GET['target_id'] : 0;

        // Validate target_type
        $allowedTypes = ['PRODUCT', 'STORE', 'USER', 'REVIEW'];
        if (!in_array($targetType, $allowedTypes) || $targetId <= 0) {
            setFlash('error', 'Đường dẫn báo cáo không hợp lệ.');
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        // Lấy thông tin đối tượng bị báo cáo để hiển thị cho người dùng
        $targetInfo = $this->getTargetInfo($targetType, $targetId);

        if (!$targetInfo) {
            setFlash('error', 'Đối tượng bạn muốn báo cáo không tồn tại.');
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        // Không cho phép tự báo cáo chính mình
        if (isset($targetInfo->user_id) && (int)$targetInfo->user_id === (int)$_SESSION['user_id']) {
            setFlash('error', 'Bạn không thể báo cáo chính mình.');
            header('location: ' . URLROOT . '/products/index');
            exit();
        }

        $data = [
            'title'       => 'Báo cáo vi phạm - Creono',
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'target_info' => $targetInfo,
            'csrf_token'  => generateCsrfToken()
        ];

        $this->view('reports/create', $data);
    }

    /**
     * Xử lý gửi báo cáo (POST) – AJAX
     * URL: /reports/store
     */
    public function store(): void
    {
        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        // Kiểm tra CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $this->jsonResponse(false, 'CSRF token validation failed', ['refresh_token_needed' => true]);
        }

        // Lọc dữ liệu đầu vào
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $targetType = isset($_POST['target_type']) ? strtoupper(trim($_POST['target_type'])) : '';
        $targetId   = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;
        $reason     = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        $details    = isset($_POST['details']) ? trim($_POST['details']) : '';

        // Validate
        $errors = [];

        $allowedTypes = ['PRODUCT', 'STORE', 'USER', 'REVIEW'];
        if (!in_array($targetType, $allowedTypes) || $targetId <= 0) {
            $errors['target_err'] = 'Đối tượng báo cáo không hợp lệ.';
        }

        if (empty($reason)) {
            $errors['reason_err'] = 'Vui lòng chọn lý do báo cáo.';
        }

        // Nếu target hợp lệ, kiểm tra tồn tại
        if (empty($errors['target_err'])) {
            $targetInfo = $this->getTargetInfo($targetType, $targetId);
            if (!$targetInfo) {
                $errors['target_err'] = 'Đối tượng báo cáo không tồn tại.';
            }

            // Không cho phép tự báo cáo chính mình
            if (isset($targetInfo->user_id) && (int)$targetInfo->user_id === (int)$_SESSION['user_id']) {
                $errors['target_err'] = 'Bạn không thể báo cáo chính mình.';
            }
        }

        if (empty($details)) {
            $errors['details_err'] = 'Vui lòng nhập chi tiết báo cáo.';
        } elseif (mb_strlen($details) > 1000) {
            $errors['details_err'] = 'Chi tiết không được vượt quá 1000 ký tự.';
        }

        if (!empty($errors)) {
            $this->jsonResponse(false, 'Vui lòng kiểm tra lại thông tin', ['errors' => $errors]);
        }

        // Lưu báo cáo vào DB (status mặc định = 1: Pending)
        $reportData = [
            'reporter_id' => (int)$_SESSION['user_id'],
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'reason'      => htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'),
            'details'     => htmlspecialchars($details, ENT_QUOTES, 'UTF-8'),
            'status'      => 1 // Pending
        ];

        if ($this->reportModel->create($reportData)) {
            $this->jsonResponse(true, 'Báo cáo của bạn đã được gửi thành công! Chúng tôi sẽ xem xét và xử lý.');
        } else {
            $this->jsonResponse(false, 'Đã xảy ra lỗi khi gửi báo cáo. Vui lòng thử lại.');
        }
    }

    /**
     * Lấy thông tin đối tượng bị báo cáo
     */
    private function getTargetInfo(string $targetType, int $targetId): ?object
    {
        switch ($targetType) {
            case 'PRODUCT':
                return $this->productModel->getProductDetail($targetId);
            case 'REVIEW':
                return $this->reviewModel->findById($targetId);
            case 'STORE':
                return $this->storeModel->findById($targetId);
            case 'USER':
                return $this->userModel->findById($targetId);
            default:
                return null;
        }
    }
}

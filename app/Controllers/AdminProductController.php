<?php

/**
 * AdminProductController - Controller quản lý sản phẩm phía Admin (UC41)
 * Xử lý: Danh sách, Chi tiết, Sửa, Duyệt/Từ chối (AJAX), Xóa (AJAX)
 * Tuân thủ: CSRF check, RoleMiddleware, jsonResponse cho AJAX
 */

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Core/Validator.php';

class AdminProductController extends Controller
{
    private ProductModel $productModel;
    private ProductApproval $productApprovalModel;

    public function __construct()
    {
        // Chỉ Admin (role = 3) mới được truy cập
        RoleMiddleware::check([3]);

        $this->productModel = $this->model('ProductModel');
        $this->productApprovalModel = $this->model('ProductApproval');
    }

    /**
     * Helper: Trả về JSON response chuẩn cho AJAX
     * Format: { success: bool, message: string, ...data }
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

    // =========================================================================
    // INDEX: Danh sách sản phẩm
    // =========================================================================

    /**
     * Trang danh sách tất cả sản phẩm (default method)
     */
    public function index(): void
    {
        $products = $this->productModel->getAllForAdmin();

        $data = [
            'title'          => 'Quản lý Sản phẩm - Creono Admin',
            'products'       => $products,
            'total_products' => $this->productModel->getTotalActiveProducts(),
            'pending_count'  => $this->productModel->getCountByStatus(1),
            'approved_count' => $this->productModel->getCountByStatus(2),
            'rejected_count' => $this->productModel->getCountByStatus(3),
        ];

        $this->view('admin/products/index', $data);
    }

    // =========================================================================
    // CREATE: Form thêm sản phẩm mới (GET)
    // =========================================================================

    /**
     * Hiển thị form tạo sản phẩm mới
     */
    public function create(): void
    {
        $data = [
            'title'       => 'Thêm Sản phẩm mới - Creono Admin',
            'categories'  => $this->productModel->getAllCategories(),
            'stores'      => $this->productModel->getAllStores(),
            'product_title' => '',
            'description' => '',
            'price'       => '',
            'category_id' => '',
            'store_id'    => '',
            'status'      => 1,
            'errors'      => []
        ];

        $this->view('admin/products/create', $data);
    }

    // =========================================================================
    // STORE: Xử lý thêm sản phẩm mới (POST)
    // =========================================================================

    /**
     * Lưu sản phẩm mới vào database
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: ' . URLROOT . '/adminProductController/index');
            exit();
        }

        // Xác thực CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        // Thu thập dữ liệu từ form
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = trim($_POST['price'] ?? '');
        $categoryId  = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $storeId     = (int) ($_POST['store_id'] ?? 0);
        $status      = (int) ($_POST['status'] ?? 1);

        // Validate
        $errors = [];

        if (empty($title)) {
            $errors['title_err'] = 'Vui lòng nhập tiêu đề sản phẩm';
        } elseif (mb_strlen($title) > 255) {
            $errors['title_err'] = 'Tiêu đề không được vượt quá 255 ký tự';
        }

        if (empty($price) || !is_numeric($price) || (float) $price < 0) {
            $errors['price_err'] = 'Vui lòng nhập giá hợp lệ (>= 0)';
        }

        if ($storeId <= 0) {
            $errors['store_err'] = 'Vui lòng chọn cửa hàng';
        }

        if (!in_array($status, [1, 2, 3])) {
            $errors['status_err'] = 'Trạng thái không hợp lệ';
        }

        // Nếu có lỗi, render lại form
        if (!empty($errors)) {
            $data = [
                'title'         => 'Thêm Sản phẩm mới - Creono Admin',
                'categories'    => $this->productModel->getAllCategories(),
                'stores'        => $this->productModel->getAllStores(),
                'product_title' => $title,
                'description'   => $description,
                'price'         => $price,
                'category_id'   => $categoryId,
                'store_id'      => $storeId,
                'status'        => $status,
                'errors'        => $errors
            ];
            $this->view('admin/products/create', $data);
            return;
        }

        // Tạo sản phẩm
        $insertData = [
            'store_id'    => $storeId,
            'category_id' => $categoryId,
            'title'       => $title,
            'description' => $description,
            'price'       => (float) $price,
            'status'      => $status
        ];

        if ($this->productModel->create($insertData)) {
            setFlash('success', "Đã tạo sản phẩm '{$title}' thành công!");
            header('location: ' . URLROOT . '/adminProductController/index');
            exit();
        } else {
            setFlash('error', 'Có lỗi xảy ra khi tạo sản phẩm. Vui lòng thử lại.');
            header('location: ' . URLROOT . '/adminProductController/create');
            exit();
        }
    }

    // =========================================================================
    // EDIT: Form chỉnh sửa sản phẩm (GET)
    // =========================================================================

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     */
    public function edit(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID sản phẩm không hợp lệ');
            header('location: ' . URLROOT . '/adminProductController/index');
            exit();
        }

        $product = $this->productModel->getDetailForAdmin($id);
        if (!$product) {
            setFlash('error', 'Không tìm thấy sản phẩm');
            header('location: ' . URLROOT . '/adminProductController/index');
            exit();
        }

        $data = [
            'title'         => 'Chỉnh sửa Sản phẩm - Creono Admin',
            'product'       => $product,
            'categories'    => $this->productModel->getAllCategories(),
            'product_title' => $product->title,
            'description'   => $product->description ?? '',
            'price'         => $product->price,
            'category_id'   => $product->category_id,
            'status'        => $product->status,
            'errors'        => []
        ];

        $this->view('admin/products/edit', $data);
    }

    // =========================================================================
    // UPDATE: Xử lý cập nhật sản phẩm (POST)
    // =========================================================================

    /**
     * Cập nhật thông tin sản phẩm vào database
     */
    public function update(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID sản phẩm không hợp lệ');
            header('location: ' . URLROOT . '/adminProductController/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: ' . URLROOT . '/adminProductController/edit/' . $id);
            exit();
        }

        // Xác thực CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        $product = $this->productModel->getDetailForAdmin($id);
        if (!$product) {
            setFlash('error', 'Không tìm thấy sản phẩm');
            header('location: ' . URLROOT . '/adminProductController/index');
            exit();
        }

        // Thu thập dữ liệu
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = trim($_POST['price'] ?? '');
        $categoryId  = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $status      = (int) ($_POST['status'] ?? $product->status);

        // Validate
        $errors = [];

        if (empty($title)) {
            $errors['title_err'] = 'Vui lòng nhập tiêu đề sản phẩm';
        } elseif (mb_strlen($title) > 255) {
            $errors['title_err'] = 'Tiêu đề không được vượt quá 255 ký tự';
        }

        if (empty($price) || !is_numeric($price) || (float) $price < 0) {
            $errors['price_err'] = 'Vui lòng nhập giá hợp lệ (>= 0)';
        }

        if (!in_array($status, [1, 2, 3])) {
            $errors['status_err'] = 'Trạng thái không hợp lệ';
        }

        // Nếu có lỗi, render lại form
        if (!empty($errors)) {
            $data = [
                'title'         => 'Chỉnh sửa Sản phẩm - Creono Admin',
                'product'       => $product,
                'categories'    => $this->productModel->getAllCategories(),
                'product_title' => $title,
                'description'   => $description,
                'price'         => $price,
                'category_id'   => $categoryId,
                'status'        => $status,
                'errors'        => $errors
            ];
            $this->view('admin/products/edit', $data);
            return;
        }

        // Cập nhật sản phẩm
        $updateData = [
            'title'       => $title,
            'description' => $description,
            'price'       => (float) $price,
            'category_id' => $categoryId,
            'status'      => $status
        ];

        if ($this->productModel->adminUpdate($id, $updateData)) {
            // Nếu thay đổi status, ghi log kiểm duyệt
            if ($status != $product->status && in_array($status, [2, 3])) {
                $action = $status === 2 ? 'APPROVE' : 'REJECT';
                $note   = 'Admin cập nhật trạng thái từ trang chỉnh sửa';
                $this->productApprovalModel->logApproval($id, (int) $_SESSION['user_id'], $action, $note);
            }

            setFlash('success', "Đã cập nhật sản phẩm '{$title}' thành công!");
            header('location: ' . URLROOT . '/adminProductController/index');
            exit();
        } else {
            setFlash('error', 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.');
            header('location: ' . URLROOT . '/adminProductController/edit/' . $id);
            exit();
        }
    }

    // =========================================================================
    // APPROVE: Duyệt sản phẩm (AJAX - POST)
    // =========================================================================

    /**
     * Phê duyệt sản phẩm - set status = 2
     * Trả về jsonResponse cho frontend
     */
    public function approve(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(false, 'CSRF token không hợp lệ');
        }

        if (!$id) {
            $this->jsonResponse(false, 'ID sản phẩm không hợp lệ');
        }

        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->jsonResponse(false, 'Không tìm thấy sản phẩm');
        }

        // Chỉ cho duyệt khi đang Pending hoặc Rejected
        if ($product->status == 2) {
            $this->jsonResponse(false, 'Sản phẩm này đã được duyệt rồi');
        }

        $note = trim($_POST['note'] ?? 'Sản phẩm đạt yêu cầu và được phê duyệt đăng tải');

        if ($this->productModel->approveProduct($id)) {
            // Ghi log kiểm duyệt
            $this->productApprovalModel->logApproval($id, (int) $_SESSION['user_id'], 'APPROVE', $note);

            $this->jsonResponse(true, "Đã phê duyệt sản phẩm '{$product->title}' thành công!", [
                'product_id' => $id,
                'new_status' => 2
            ]);
        } else {
            $this->jsonResponse(false, 'Có lỗi xảy ra khi phê duyệt. Vui lòng thử lại.');
        }
    }

    // =========================================================================
    // REJECT: Từ chối sản phẩm (AJAX - POST)
    // =========================================================================

    /**
     * Từ chối sản phẩm - set status = 3
     * Trả về jsonResponse cho frontend
     */
    public function reject(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(false, 'CSRF token không hợp lệ');
        }

        if (!$id) {
            $this->jsonResponse(false, 'ID sản phẩm không hợp lệ');
        }

        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->jsonResponse(false, 'Không tìm thấy sản phẩm');
        }

        if ($product->status == 3) {
            $this->jsonResponse(false, 'Sản phẩm này đã bị từ chối rồi');
        }

        $note = trim($_POST['note'] ?? 'Sản phẩm chưa đạt tiêu chuẩn kiểm duyệt');

        if (empty($note)) {
            $this->jsonResponse(false, 'Vui lòng nhập lý do từ chối');
        }

        if ($this->productModel->rejectProduct($id)) {
            $this->productApprovalModel->logApproval($id, (int) $_SESSION['user_id'], 'REJECT', $note);

            $this->jsonResponse(true, "Đã từ chối sản phẩm '{$product->title}'.", [
                'product_id' => $id,
                'new_status' => 3
            ]);
        } else {
            $this->jsonResponse(false, 'Có lỗi xảy ra. Vui lòng thử lại.');
        }
    }

    // =========================================================================
    // DELETE: Xóa sản phẩm (AJAX - POST)
    // =========================================================================

    /**
     * Xóa mềm sản phẩm (set deleted_at)
     * Trả về jsonResponse cho frontend
     */
    public function delete(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method not allowed');
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !verifyCsrfToken($csrfToken)) {
            $this->jsonResponse(false, 'CSRF token không hợp lệ');
        }

        if (!$id) {
            $this->jsonResponse(false, 'ID sản phẩm không hợp lệ');
        }

        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->jsonResponse(false, 'Không tìm thấy sản phẩm');
        }

        if ($this->productModel->softDelete($id)) {
            $this->jsonResponse(true, "Đã xóa sản phẩm '{$product->title}' thành công!", [
                'product_id' => $id
            ]);
        } else {
            $this->jsonResponse(false, 'Có lỗi xảy ra khi xóa. Vui lòng thử lại.');
        }
    }
}

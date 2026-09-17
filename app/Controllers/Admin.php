<?php

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Helpers/mail_helper.php';
require_once '../app/Core/Validator.php';

class Admin extends Controller
{
    private Wallet $walletModel;
    private StatModel $statModel;
    private Category $categoryModel;
    private Product $productModel;
    private ProductApproval $productApprovalModel;
    private Report $reportModel;
    private AiAppeal $aiAppealModel;
    private KycDocument $kycModel;
    private User $userModel;
    private Store $storeModel;

    public function __construct()
    {
        // Chỉ cho phép Admin (role = 3) truy cập
        RoleMiddleware::check([3]);

        $this->statModel = $this->model('StatModel');
        $this->categoryModel = $this->model('Category');
        $this->productModel = $this->model('Product');
        $this->productApprovalModel = $this->model('ProductApproval');
        $this->reportModel = $this->model('Report');
        $this->aiAppealModel = $this->model('AiAppeal');
        $this->kycModel = $this->model('KycDocument');
        $this->userModel = $this->model('User');
        $this->walletModel = $this->model('Wallet');
        $this->storeModel = $this->model('Store');
    }

    public function index(): void
    {
        $this->dashboard();
    }

    /**
     * Giao diện Cấu hình hệ thống (UC45)
     */
    public function settings(): void {
        $settingModel = $this->model('Setting');
        
        $data = [
            'title' => 'Cấu hình hệ thống',
            'commission_rate' => $settingModel->getSetting('commission_rate', '5'),
            'csrf_token' => generateCsrfToken()
        ];
        
        $this->view('admin/settings/index', $data);
    }

    /**
     * API Cập nhật cấu hình (Xử lý AJAX)
     */
    public function updateSettings(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method không hợp lệ');
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $this->jsonResponse(false, 'Lỗi bảo mật CSRF');
        }

        $rate = $_POST['commission_rate'] ?? '';
        
        if (!is_numeric($rate) || (float)$rate < 0 || (float)$rate > 100) {
            $this->jsonResponse(false, 'Dữ liệu không hợp lệ', [
                'errors' => ['commission_rate_err' => 'Tỷ lệ hoa hồng phải là số từ 0 đến 100']
            ]);
        }

        $settingModel = $this->model('Setting');
        if ($settingModel->updateSetting('commission_rate', (string)$rate)) {
            $this->jsonResponse(true, 'Đã cập nhật tỷ lệ phí nền tảng thành công!');
        } else {
            $this->jsonResponse(false, 'Lỗi hệ thống khi lưu Database');
        }
    }

    // =========================================================================
    // UC39: Dashboard & Thống kê hệ thống
    // =========================================================================
    public function dashboard(): void
    {
        $data = [
            'title'                  => 'Tổng quan Quản trị viên - Creono Admin',
            'total_users'            => $this->statModel->getTotalUsers(),
            'total_products'         => $this->statModel->getTotalProducts(),
            'total_orders'           => $this->statModel->getTotalOrders(),
            'total_revenue'          => $this->statModel->getTotalRevenue(),
            'top_products'           => $this->statModel->getTopProducts(5),
            'seller_revenues'        => $this->statModel->getSellerRevenueOverview(5),
            'pending_approvals_count' => $this->productModel->getPendingCount(),
            'pending_reports_count'  => $this->reportModel->getPendingCount() + $this->aiAppealModel->getPendingCount()
        ];

        $this->view('admin/dashboard', $data);
    }

    // =========================================================================
    // UC42: Quản lý Danh mục (Category CRUD)
    // =========================================================================
    public function categories(): void
    {
        $categories = $this->categoryModel->getAllWithProductCount();
        $data = [
            'title'      => 'Quản lý Danh mục - Creono Admin',
            'categories' => $categories
        ];
        $this->view('admin/categories/index', $data);
    }

    public function categoryCreate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }

            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sort_order = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? (int)$_POST['sort_order'] : 0;

            if (empty($slug) && !empty($name)) {
                $slug = $this->slugify($name);
            } else {
                $slug = $this->slugify($slug);
            }

            $data = [
                'title'       => 'Thêm Danh mục Mới - Creono Admin',
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'sort_order'  => $sort_order,
                'errors'      => []
            ];

            $validator = new Validator(['name' => $name, 'slug' => $slug]);
            $errors = $validator->validate([
                'name' => 'required',
                'slug' => 'required'
            ]);

            if (!isset($errors['name_err']) && $this->categoryModel->nameExists($name)) {
                $errors['name_err'] = 'Tên danh mục này đã tồn tại';
            }

            if (!isset($errors['slug_err']) && $this->categoryModel->slugExists($slug)) {
                $errors['slug_err'] = 'Slug này đã tồn tại';
            }

            if (empty($errors)) {
                $insertData = [
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $description,
                    'sort_order'  => $sort_order
                ];

                if ($this->categoryModel->create($insertData)) {
                    setFlash('success', 'Thêm danh mục mới thành công!');
                    header('location: ' . URLROOT . '/admin/categories');
                    exit();
                } else {
                    setFlash('error', 'Có lỗi xảy ra khi tạo danh mục. Vui lòng thử lại.');
                }
            } else {
                $data['errors'] = $errors;
            }
            $this->view('admin/categories/create', $data);
        } else {
            $data = [
                'title'       => 'Thêm Danh mục Mới - Creono Admin',
                'name'        => '',
                'slug'        => '',
                'description' => '',
                'sort_order'  => 0,
                'errors'      => []
            ];
            $this->view('admin/categories/create', $data);
        }
    }

    public function categoryEdit(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID danh mục không hợp lệ');
            header('location: ' . URLROOT . '/admin/categories');
            exit();
        }

        $category = $this->categoryModel->findById($id);
        if (!$category) {
            setFlash('error', 'Không tìm thấy danh mục yêu cầu');
            header('location: ' . URLROOT . '/admin/categories');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }

            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sort_order = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? (int)$_POST['sort_order'] : 0;

            if (empty($slug) && !empty($name)) {
                $slug = $this->slugify($name);
            } else {
                $slug = $this->slugify($slug);
            }

            $data = [
                'title'       => 'Chỉnh sửa Danh mục - Creono Admin',
                'id'          => $id,
                'category'    => $category,
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'sort_order'  => $sort_order,
                'errors'      => []
            ];

            $validator = new Validator(['name' => $name, 'slug' => $slug]);
            $errors = $validator->validate([
                'name' => 'required',
                'slug' => 'required'
            ]);

            if (!isset($errors['name_err']) && $this->categoryModel->nameExists($name, $id)) {
                $errors['name_err'] = 'Tên danh mục này đã tồn tại';
            }

            if (!isset($errors['slug_err']) && $this->categoryModel->slugExists($slug, $id)) {
                $errors['slug_err'] = 'Slug này đã tồn tại';
            }

            if (empty($errors)) {
                $updateData = [
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $description,
                    'sort_order'  => $sort_order
                ];

                if ($this->categoryModel->update($id, $updateData)) {
                    setFlash('success', 'Cập nhật danh mục thành công!');
                    header('location: ' . URLROOT . '/admin/categories');
                    exit();
                } else {
                    setFlash('error', 'Có lỗi xảy ra khi cập nhật danh mục.');
                }
            } else {
                $data['errors'] = $errors;
            }
            $this->view('admin/categories/edit', $data);
        } else {
            $data = [
                'title'       => 'Chỉnh sửa Danh mục - Creono Admin',
                'id'          => $id,
                'category'    => $category,
                'name'        => $category->name,
                'slug'        => $category->slug,
                'description' => $category->description ?? '',
                'sort_order'  => $category->sort_order ?? 0,
                'errors'      => []
            ];
            $this->view('admin/categories/edit', $data);
        }
    }

    public function categoryDelete(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID danh mục không hợp lệ');
            header('location: ' . URLROOT . '/admin/categories');
            exit();
        }

        $category = $this->categoryModel->findById($id);
        if (!$category) {
            setFlash('error', 'Danh mục không tồn tại');
            header('location: ' . URLROOT . '/admin/categories');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }
        }

        if ($this->categoryModel->destroy($id)) {
            setFlash('success', "Đã xóa danh mục '{$category->name}' thành công!");
        } else {
            setFlash('error', 'Không thể xóa danh mục.');
        }

        header('location: ' . URLROOT . '/admin/categories');
        exit();
    }

    // =========================================================================
    // UC43: Duyệt sản phẩm
    // =========================================================================
    public function approvals(): void
    {
        $pendingProducts = $this->productModel->getPendingApprovals();
        $recentApprovals = $this->productApprovalModel->getRecentApprovals(10);

        $data = [
            'title'             => 'Duyệt Sản Phẩm - Creono Admin',
            'pending_products'  => $pendingProducts,
            'recent_approvals'  => $recentApprovals
        ];

        $this->view('admin/approvals/index', $data);
    }

    public function approveProduct(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID sản phẩm không hợp lệ');
            header('location: ' . URLROOT . '/admin/approvals');
            exit();
        }

        $product = $this->productModel->findById($id);
        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại');
            header('location: ' . URLROOT . '/admin/approvals');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }
        }

        $adminId = (int)$_SESSION['user_id'];
        $note = trim($_POST['note'] ?? 'Sản phẩm đạt yêu cầu và được phê duyệt đăng tải');

        if ($this->productModel->updateStatus($id, 2)) {
            $this->productApprovalModel->logApproval($id, $adminId, 'APPROVE', $note);
            setFlash('success', "Đã phê duyệt thành công sản phẩm '{$product->title}'!");
        } else {
            setFlash('error', 'Có lỗi xảy ra khi phê duyệt sản phẩm.');
        }

        header('location: ' . URLROOT . '/admin/approvals');
        exit();
    }

    public function rejectProduct(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID sản phẩm không hợp lệ');
            header('location: ' . URLROOT . '/admin/approvals');
            exit();
        }

        $product = $this->productModel->findById($id);
        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại');
            header('location: ' . URLROOT . '/admin/approvals');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }
        }

        $adminId = (int)$_SESSION['user_id'];
        $note = trim($_POST['note'] ?? 'Sản phẩm chưa đạt tiêu chuẩn kiểm duyệt');

        if ($this->productModel->updateStatus($id, 3)) {
            $this->productApprovalModel->logApproval($id, $adminId, 'REJECT', $note);
            setFlash('warning', "Đã từ chối đăng tải sản phẩm '{$product->title}'.");
        } else {
            setFlash('error', 'Có lỗi xảy ra khi từ chối sản phẩm.');
        }

        header('location: ' . URLROOT . '/admin/approvals');
        exit();
    }

    // =========================================================================
    // Phê duyệt rút tiền
    // =========================================================================
    public function withdrawals(): void
    {
        $data = [
            'title' => 'Phê duyệt rút tiền',
            'requests' => $this->walletModel->getPendingWithdrawals(),
            'csrf_token' => generateCsrfToken()
        ];
        $this->view('admin/withdrawals/index', $data);
    }

    public function processWithdrawal(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Method không hợp lệ');
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $this->jsonResponse(false, 'Lỗi bảo mật CSRF');
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        $action = strtoupper($_POST['action'] ?? '');
        $adminId = (int)$_SESSION['user_id'];

        if ($requestId <= 0 || !in_array($action, ['APPROVE', 'REJECT'])) {
            $this->jsonResponse(false, 'Dữ liệu không hợp lệ. Hành động bị từ chối.');
        }

        if ($this->walletModel->processWithdrawalAdmin($requestId, $adminId, $action)) {
            $msg = $action === 'APPROVE' ? 'Đã duyệt yêu cầu rút tiền thành công!' : 'Đã từ chối và hoàn tiền về ví cho người bán.';
            $this->jsonResponse(true, $msg, ['redirect' => URLROOT . '/admin/withdrawals']);
        } else {
            $this->jsonResponse(false, 'Lỗi hệ thống khi xử lý dòng tiền. Vui lòng kiểm tra lại.');
        }
    }

    // =========================================================================
    // UC44: Quản lý Báo cáo vi phạm & Khiếu nại AI (Reports & AI Appeals)
    // =========================================================================
    public function reports(): void
    {
        $reports = $this->reportModel->getAllWithDetails();
        $appeals = $this->aiAppealModel->getAllWithDetails();

        $data = [
            'title'   => 'Quản lý Báo cáo Vi phạm & Khiếu nại AI - Creono Admin',
            'reports' => $reports,
            'appeals' => $appeals
        ];

        $this->view('admin/reports/index', $data);
    }

    public function resolveReport(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID báo cáo không hợp lệ');
            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }

        $report = $this->reportModel->findById($id);
        if (!$report) {
            setFlash('error', 'Không tìm thấy báo cáo vi phạm');
            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }
        }

        $adminId = (int)$_SESSION['user_id'];
        $action = trim($_POST['action'] ?? 'resolve');

        if ($action === 'resolve') {
            if ($report->target_type === 'PRODUCT') {
                $this->productModel->updateStatus((int)$report->target_id, 3);
            }
            $this->reportModel->updateReportStatus($id, 3, $adminId);
            setFlash('success', "Đã giải quyết báo cáo vi phạm #{$id} thành công!");
            $this->sendReportNotification((int)$report->reporter_id, 'resolved', $id);
        } elseif ($action === 'dismiss') {
            $this->reportModel->updateReportStatus($id, 4, $adminId);
            setFlash('info', "Đã bác bỏ báo cáo vi phạm #{$id}.");
            $this->sendReportNotification((int)$report->reporter_id, 'dismissed', $id);
        } elseif ($action === 'investigate') {
            $this->reportModel->updateReportStatus($id, 2, $adminId);
            setFlash('warning', "Đang tiến hành điều tra báo cáo #{$id}.");
        }

        header('location: ' . URLROOT . '/admin/reports');
        exit();
    }

    /**
     * Xử lý khiếu nại nhãn AI từ Seller
     */
    public function processAppeal(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID khiếu nại không hợp lệ');
            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }

        $appeal = $this->aiAppealModel->findById($id);
        if (!$appeal) {
            setFlash('error', 'Không tìm thấy khiếu nại nhãn AI');
            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('Lỗi bảo mật CSRF.');
            }

            $adminId = (int)$_SESSION['user_id'];
            $action = trim($_POST['action'] ?? 'approve');

            // Lấy thông tin sản phẩm để chèn tên vào Email
            $product = $this->productModel->findById((int)$appeal->product_id);
            $productTitle = $product ? $product->title : 'Tài liệu số';

            if ($action === 'approve') {
                // Khôi phục trạng thái sản phẩm và đổi nhãn AI về Human
                $this->productModel->updateStatus((int)$appeal->product_id, 2);
                $documentModel = $this->model('Document');
                $documentModel->updateByProductId((int)$appeal->product_id, ['ai_label_id' => 1]);

                // Đánh dấu đã giải quyết khiếu nại
                $this->aiAppealModel->updateAppealStatus($id, 2, $adminId);
                
                // Gửi email thông báo Thành công
                $this->sendAppealNotification((int)$appeal->seller_id, 'approved', $id, $productTitle);
                
                setFlash('success', "Đã chấp nhận khiếu nại AI #{$id}, khôi phục sản phẩm và cập nhật nhãn Human!");
            } else {
                // Cập nhật trạng thái từ chối
                $this->aiAppealModel->updateAppealStatus($id, 3, $adminId);
                
                // Gửi email thông báo Từ chối
                $this->sendAppealNotification((int)$appeal->seller_id, 'rejected', $id, $productTitle);
                
                setFlash('warning', "Đã từ chối khiếu nại nhãn AI #{$id}.");
            }

            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }
    }

    // =========================================================================
    // KYC (Xác minh danh tính) – Admin duyệt
    // =========================================================================
    public function listKyc(): void
    {
        $kycs = $this->kycModel->getPendingKycs();
        $data = [
            'title' => 'Duyệt KYC - Creono Admin',
            'kycs'  => $kycs
        ];
        $this->view('admin/kyc/index', $data);
    }

    public function approveKyc(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID KYC không hợp lệ');
            header('location: ' . URLROOT . '/admin/listKyc');
            exit();
        }

        $kyc = $this->kycModel->findById($id);
        if (!$kyc) {
            setFlash('error', 'Không tìm thấy yêu cầu KYC');
            header('location: ' . URLROOT . '/admin/listKyc');
            exit();
        }

        if ($this->kycModel->approveKyc($id)) {
            setFlash('success', 'KYC đã được duyệt thành công.');
            $this->sendKycNotification((int)$kyc->user_id, 'approved');
        } else {
            setFlash('error', 'Có lỗi xảy ra khi duyệt KYC.');
        }

        header('location: ' . URLROOT . '/admin/listKyc');
        exit();
    }

    public function rejectKyc(?int $id = null): void
    {
        if (!$id) {
            setFlash('error', 'ID KYC không hợp lệ');
            header('location: ' . URLROOT . '/admin/listKyc');
            exit();
        }

        $kyc = $this->kycModel->findById($id);
        if (!$kyc) {
            setFlash('error', 'Không tìm thấy yêu cầu KYC');
            header('location: ' . URLROOT . '/admin/listKyc');
            exit();
        }

        $note = trim($_POST['note'] ?? 'Không đạt yêu cầu xác minh');

        if ($this->kycModel->rejectKyc($id, $note)) {
            setFlash('warning', 'KYC đã bị từ chối.');
            $this->sendKycNotification((int)$kyc->user_id, 'rejected', $note);
        } else {
            setFlash('error', 'Có lỗi xảy ra khi từ chối KYC.');
        }

        header('location: ' . URLROOT . '/admin/listKyc');
        exit();
    }

    // =========================================================================
    // Phê duyệt đăng ký cửa hàng (Trường hợp User ghép chung vào Controller Admin)
    // =========================================================================
    public function pendingStores(): void
    {
        // Kiểm tra xem method getPendingStores có tồn tại trong Store model không
        if (method_exists($this->storeModel, 'getPendingStores')) {
            $pendingStores = $this->storeModel->getPendingStores();
            $data = [
                'title' => 'Duyệt đăng ký cửa hàng - Creono Admin',
                'stores' => $pendingStores,
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('admin/stores/pending', $data);
        } else {
            setFlash('error', 'Hàm getPendingStores chưa được khai báo trong Store model.');
            header('location: ' . URLROOT . '/admin/dashboard');
            exit();
        }
    }

    public function stores(string $action = 'pending', ?int $id = null): void
    {
        if ($action === 'approve' && $id) {
            $this->approveStore($id);
            return;
        }

        if ($action === 'reject' && $id) {
            $this->rejectStore($id);
            return;
        }

        $this->pendingStores();
    }

    public function approveStore(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Phương thức yêu cầu không hợp lệ.');
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !verifyCsrfToken($token)) {
            $this->jsonResponse(false, 'Phiên bảo mật không hợp lệ (CSRF Token mismatch).');
            return;
        }

        $storeId = (int)($id ?? ($_POST['store_id'] ?? 0));
        if ($storeId <= 0) {
            $this->jsonResponse(false, 'Mã cửa hàng không hợp lệ.');
            return;
        }

        $store = method_exists($this->storeModel, 'getStoreWithApplicant') 
                 ? $this->storeModel->getStoreWithApplicant($storeId) 
                 : $this->storeModel->findById($storeId);

        if (!$store) {
            $this->jsonResponse(false, 'Không tìm thấy hồ sơ cửa hàng.');
            return;
        }

        if ((int)$store->status === 1) {
            $this->jsonResponse(false, 'Hồ sơ cửa hàng này đã được phê duyệt.');
            return;
        }

        if (method_exists($this->storeModel, 'approveStore')) {
            $success = $this->storeModel->approveStore($storeId);
            if ($success) {
                if (!empty($store->applicant_email) && function_exists('sendEmail')) {
                    $subject = 'Chúc mừng! Hồ sơ đăng ký cửa hàng "' . $store->name . '" đã được phê duyệt';
                    $body = 'Xin chào ' . htmlspecialchars($store->applicant_name ?? 'Bạn') . ",\n\n"
                        . 'Hồ sơ đăng ký cửa hàng "' . htmlspecialchars($store->name) . '" của bạn trên Creono đã được Quản trị viên phê duyệt thành công!\n'
                        . 'Tài khoản của bạn đã được nâng cấp lên Người bán (Seller). Bạn có thể đăng nhập và bắt đầu đăng tải tài liệu ngay bây giờ.\n\n'
                        . 'Trân trọng,\nĐội ngũ Creono';
                    @sendEmail($store->applicant_email, $subject, $body);
                }
                $this->jsonResponse(true, 'Đã phê duyệt hồ sơ cửa hàng thành công! Tài khoản đã được nâng cấp lên Người bán.');
            } else {
                $this->jsonResponse(false, 'Đã xảy ra lỗi khi phê duyệt cửa hàng.');
            }
        } else {
            $this->jsonResponse(false, 'Hàm approveStore chưa được cài đặt trong Model.');
        }
    }

    public function rejectStore(?int $id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Phương thức yêu cầu không hợp lệ.');
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !verifyCsrfToken($token)) {
            $this->jsonResponse(false, 'Phiên bảo mật không hợp lệ (CSRF Token mismatch).');
            return;
        }

        $storeId = (int)($id ?? ($_POST['store_id'] ?? 0));
        if ($storeId <= 0) {
            $this->jsonResponse(false, 'Mã cửa hàng không hợp lệ.');
            return;
        }

        $reason = trim($_POST['reason'] ?? '');
        if (empty($reason)) {
            $this->jsonResponse(false, 'Vui lòng nhập lý do từ chối hồ sơ.');
            return;
        }

        $store = method_exists($this->storeModel, 'getStoreWithApplicant') 
                 ? $this->storeModel->getStoreWithApplicant($storeId) 
                 : $this->storeModel->findById($storeId);

        if (!$store) {
            $this->jsonResponse(false, 'Không tìm thấy hồ sơ cửa hàng.');
            return;
        }

        if (method_exists($this->storeModel, 'rejectStore')) {
            $success = $this->storeModel->rejectStore($storeId, $reason);
            if ($success) {
                if (!empty($store->applicant_email) && function_exists('sendEmail')) {
                    $subject = 'Thông báo về hồ sơ đăng ký cửa hàng "' . $store->name . '" trên Creono';
                    $body = 'Xin chào ' . htmlspecialchars($store->applicant_name ?? 'Bạn') . ",\n\n"
                        . 'Rất tiếc, hồ sơ đăng ký cửa hàng "' . htmlspecialchars($store->name) . '" của bạn chưa đáp ứng yêu cầu của nền tảng Creono.\n\n'
                        . 'Lý do từ chối: ' . htmlspecialchars($reason) . "\n\n"
                        . 'Vui lòng kiểm tra lại thông tin và nộp lại hồ sơ nếu cần thiết.\n\n'
                        . 'Trân trọng,\nĐội ngũ Creono';
                    @sendEmail($store->applicant_email, $subject, $body);
                }
                $this->jsonResponse(true, 'Đã từ chối hồ sơ đăng ký cửa hàng thành công.');
            } else {
                $this->jsonResponse(false, 'Đã xảy ra lỗi khi từ chối hồ sơ.');
            }
        } else {
            $this->jsonResponse(false, 'Hàm rejectStore chưa được cài đặt trong Model.');
        }
    }

    // =========================================================================
    // CÁC HÀM TRỢ GIÚP (HELPERS)
    // =========================================================================

    private function jsonResponse(bool $success, string $message, array $data = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
        exit();
    }

    private function slugify(string $text): string
    {
        $utf8Map = [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
            'À' => 'a', 'Á' => 'a', 'Ả' => 'a', 'Ã' => 'a', 'Ạ' => 'a', 'Ă' => 'a', 'Ằ' => 'a', 'Ắ' => 'a', 'Ẳ' => 'a', 'Ẵ' => 'a', 'Ặ' => 'a', 'Â' => 'a', 'Ầ' => 'a', 'Ấ' => 'a', 'Ẩ' => 'a', 'Ẫ' => 'a', 'Ậ' => 'a',
            'Đ' => 'd',
            'È' => 'e', 'É' => 'e', 'Ẻ' => 'e', 'Ẽ' => 'e', 'Ẹ' => 'e', 'Ê' => 'e', 'Ề' => 'e', 'Ế' => 'e', 'Ể' => 'e', 'Ễ' => 'e', 'Ệ' => 'e',
            'Ì' => 'i', 'Í' => 'i', 'Ỉ' => 'i', 'Ĩ' => 'i', 'Ị' => 'i',
            'Ò' => 'o', 'Ó' => 'o', 'Ỏ' => 'o', 'Õ' => 'o', 'Ọ' => 'o', 'Ô' => 'o', 'Ồ' => 'o', 'Ố' => 'o', 'Ổ' => 'o', 'Ỗ' => 'o', 'Ộ' => 'o', 'Ơ' => 'o', 'Ờ' => 'o', 'Ớ' => 'o', 'Ở' => 'o', 'Ỡ' => 'o', 'Ợ' => 'o',
            'Ù' => 'u', 'Ú' => 'u', 'Ủ' => 'u', 'Ũ' => 'u', 'Ụ' => 'u', 'Ư' => 'u', 'Ừ' => 'u', 'Ứ' => 'u', 'Ử' => 'u', 'Ữ' => 'u', 'Ự' => 'u',
            'Ỳ' => 'y', 'Ý' => 'y', 'Ỷ' => 'y', 'Ỹ' => 'y', 'Ỵ' => 'y'
        ];

        $text = strtr($text, $utf8Map);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'danh-muc');
    }

    private function sendReportNotification(int $reporterId, string $status, int $reportId): void
    {
        $reporter = $this->userModel->findById($reporterId);
        if (!$reporter) return;

        $subject = 'Thông báo về báo cáo vi phạm #' . $reportId;
        if ($status === 'resolved') {
            $body = "<div style='font-family: -apple-system, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Xin chào {$reporter->name},</h2>
                <p>Báo cáo vi phạm <strong>#{$reportId}</strong> của bạn đã được xem xét và <strong style='color: #34c759;'>CHẤP NHẬN</strong>.</p>
                <p>Chúng tôi đã xử lý đối tượng vi phạm theo quy định. Cảm ơn bạn đã giúp chúng tôi duy trì cộng đồng an toàn.</p><br>
                <p>Trân trọng,<br>Đội ngũ Creono</p></div>";
        } else {
            $body = "<div style='font-family: -apple-system, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Xin chào {$reporter->name},</h2>
                <p>Báo cáo vi phạm <strong>#{$reportId}</strong> của bạn đã được xem xét và <strong style='color: #ff9500;'>BÁC BỎ</strong>.</p>
                <p>Chúng tôi không tìm thấy đủ bằng chứng cho vi phạm này. Nếu bạn có thêm thông tin, vui lòng gửi lại báo cáo mới.</p><br>
                <p>Trân trọng,<br>Đội ngũ Creono</p></div>";
        }
        $altBody = strip_tags($body);
        if (function_exists('sendEmail')) {
            sendEmail($reporter->email, $subject, $body, $altBody);
        }
    }

    private function sendKycNotification(int $userId, string $status, string $note = ''): void
    {
        $user = $this->userModel->findById($userId);
        if (!$user) return;

        $subject = 'Thông báo xác minh danh tính (KYC) - Creono';
        if ($status === 'approved') {
            $body = "<div style='font-family: -apple-system, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Xin chào {$user->name},</h2>
                <p>Yêu cầu xác minh danh tính (KYC) của bạn đã được <strong style='color: #34c759;'>DUYỆT</strong> thành công.</p>
                <p>Bạn có thể sử dụng đầy đủ các tính năng của nền tảng.</p><br>
                <p>Trân trọng,<br>Đội ngũ Creono</p></div>";
        } else {
            $body = "<div style='font-family: -apple-system, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Xin chào {$user->name},</h2>
                <p>Yêu cầu xác minh danh tính (KYC) của bạn đã bị <strong style='color: #ff3b30;'>TỪ CHỐI</strong>.</p>
                <p><strong>Lý do:</strong> " . htmlspecialchars($note) . "</p>
                <p>Vui lòng cập nhật lại giấy tờ và gửi lại yêu cầu mới.</p><br>
                <p>Trân trọng,<br>Đội ngũ Creono</p></div>";
        }
        $altBody = strip_tags($body);
        if (function_exists('sendEmail')) {
            sendEmail($user->email, $subject, $body, $altBody);
        }
    }

    private function sendAppealNotification(int $sellerId, string $status, int $appealId, string $productTitle): void
    {
        $seller = $this->userModel->findById($sellerId);
        if (!$seller) return;

        $subject = 'Kết quả Kháng cáo nhãn AI cho sản phẩm: ' . $productTitle;
        if ($status === 'approved') {
            $body = "<div style='font-family: -apple-system, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Xin chào {$seller->name},</h2>
                <p>Yêu cầu kháng cáo nhãn AI <strong>#{$appealId}</strong> cho sản phẩm <strong>{$productTitle}</strong> của bạn đã được <strong style='color: #34c759;'>CHẤP NHẬN</strong>.</p>
                <p>Chúng tôi đã gỡ bỏ nhãn AI và khôi phục nhãn 'Human Written' cho sản phẩm của bạn. Cảm ơn bạn đã đóng góp nội dung chất lượng cho Creono!</p><br>
                <p>Trân trọng,<br>Đội ngũ Quản trị Creono</p></div>";
        } else {
            $body = "<div style='font-family: -apple-system, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Xin chào {$seller->name},</h2>
                <p>Yêu cầu kháng cáo nhãn AI <strong>#{$appealId}</strong> cho sản phẩm <strong>{$productTitle}</strong> của bạn đã bị <strong style='color: #ff3b30;'>TỪ CHỐI</strong>.</p>
                <p>Sau khi xem xét bằng chứng, chúng tôi nhận thấy chưa đủ cơ sở để chứng minh tài liệu này hoàn toàn do con người tạo ra. Nhãn AI sẽ tiếp tục được giữ nguyên.</p><br>
                <p>Trân trọng,<br>Đội ngũ Quản trị Creono</p></div>";
        }
        $altBody = strip_tags($body);
        if (function_exists('sendEmail')) {
            sendEmail($seller->email, $subject, $body, $altBody);
        }
    }
}
<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Core/Validator.php';

class Admin extends Controller {
    private $statModel;
    private $categoryModel;
    private $productModel;
    private $productApprovalModel;
    private $reportModel;
    private $aiAppealModel;

    public function __construct() {
        // Chỉ cho phép Admin (role = 3) truy cập
        RoleMiddleware::check([3]);

        $this->statModel = $this->model('StatModel');
        $this->categoryModel = $this->model('Category');
        $this->productModel = $this->model('Product');
        $this->productApprovalModel = $this->model('ProductApproval');
        $this->reportModel = $this->model('Report');
        $this->aiAppealModel = $this->model('AiAppeal');
    }

    public function index() {
        $this->dashboard();
    }

    // =========================================================================
    // UC39: Dashboard & Thống kê hệ thống
    // =========================================================================
    public function dashboard() {
        $data = [
            'title' => 'Tổng quan Quản trị viên - Creono Admin',
            'total_users' => $this->statModel->getTotalUsers(),
            'total_products' => $this->statModel->getTotalProducts(),
            'total_orders' => $this->statModel->getTotalOrders(),
            'total_revenue' => $this->statModel->getTotalRevenue(),
            'top_products' => $this->statModel->getTopProducts(5),
            'seller_revenues' => $this->statModel->getSellerRevenueOverview(5),
            'pending_approvals_count' => $this->productModel->getPendingCount(),
            'pending_reports_count' => $this->reportModel->getPendingCount() + $this->aiAppealModel->getPendingCount()
        ];

        $this->view('admin/dashboard', $data);
    }

    // =========================================================================
    // UC42: Quản lý Danh mục (Category CRUD)
    // =========================================================================

    /**
     * Danh sách danh mục
     */
    public function categories() {
        $categories = $this->categoryModel->getAllWithProductCount();

        $data = [
            'title' => 'Quản lý Danh mục - Creono Admin',
            'categories' => $categories
        ];

        $this->view('admin/categories/index', $data);
    }

    /**
     * Thêm danh mục mới (GET / POST)
     */
    public function categoryCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['csrf_token'])) {
                verifyCsrfToken($_POST['csrf_token']);
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
                'title' => 'Thêm Danh mục Mới - Creono Admin',
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'sort_order' => $sort_order,
                'errors' => []
            ];

            $validator = new Validator([
                'name' => $name,
                'slug' => $slug
            ]);

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
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'sort_order' => $sort_order
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
                'title' => 'Thêm Danh mục Mới - Creono Admin',
                'name' => '',
                'slug' => '',
                'description' => '',
                'sort_order' => 0,
                'errors' => []
            ];

            $this->view('admin/categories/create', $data);
        }
    }

    /**
     * Chỉnh sửa danh mục (GET / POST)
     */
    public function categoryEdit($id = null) {
        if (!$id || !is_numeric($id)) {
            setFlash('error', 'ID danh mục không hợp lệ');
            header('location: ' . URLROOT . '/admin/categories');
            exit();
        }

        $categoryId = (int)$id;
        $category = $this->categoryModel->findById($categoryId);

        if (!$category) {
            setFlash('error', 'Không tìm thấy danh mục yêu cầu');
            header('location: ' . URLROOT . '/admin/categories');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['csrf_token'])) {
                verifyCsrfToken($_POST['csrf_token']);
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
                'title' => 'Chỉnh sửa Danh mục - Creono Admin',
                'id' => $categoryId,
                'category' => $category,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'sort_order' => $sort_order,
                'errors' => []
            ];

            $validator = new Validator([
                'name' => $name,
                'slug' => $slug
            ]);

            $errors = $validator->validate([
                'name' => 'required',
                'slug' => 'required'
            ]);

            if (!isset($errors['name_err']) && $this->categoryModel->nameExists($name, $categoryId)) {
                $errors['name_err'] = 'Tên danh mục này đã tồn tại';
            }

            if (!isset($errors['slug_err']) && $this->categoryModel->slugExists($slug, $categoryId)) {
                $errors['slug_err'] = 'Slug này đã tồn tại';
            }

            if (empty($errors)) {
                $updateData = [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'sort_order' => $sort_order
                ];

                if ($this->categoryModel->update($categoryId, $updateData)) {
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
                'title' => 'Chỉnh sửa Danh mục - Creono Admin',
                'id' => $category->id,
                'category' => $category,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description ?? '',
                'sort_order' => $category->sort_order ?? 0,
                'errors' => []
            ];

            $this->view('admin/categories/edit', $data);
        }
    }

    /**
     * Xóa danh mục
     */
    public function categoryDelete($id = null) {
        if (!$id || !is_numeric($id)) {
            setFlash('error', 'ID danh mục không hợp lệ');
            header('location: ' . URLROOT . '/admin/categories');
            exit();
        }

        $categoryId = (int)$id;
        $category = $this->categoryModel->findById($categoryId);

        if (!$category) {
            setFlash('error', 'Danh mục không tồn tại');
            header('location: ' . URLROOT . '/admin/categories');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
            verifyCsrfToken($_POST['csrf_token']);
        }

        if ($this->categoryModel->destroy($categoryId)) {
            setFlash('success', "Đã xóa danh mục '{$category->name}' thành công!");
        } else {
            setFlash('error', 'Khởi tạo xóa không thành công.');
        }

        header('location: ' . URLROOT . '/admin/categories');
        exit();
    }

    // =========================================================================
    // UC43: Duyệt sản phẩm (Product Approval System)
    // =========================================================================

    /**
     * Hiển thị danh sách sản phẩm chờ duyệt và lịch sử phê duyệt
     */
    public function approvals() {
        $pendingProducts = $this->productModel->getPendingApprovals();
        $recentApprovals = $this->productApprovalModel->getRecentApprovals(10);

        $data = [
            'title' => 'Duyệt Sản Phẩm - Creono Admin',
            'pending_products' => $pendingProducts,
            'recent_approvals' => $recentApprovals
        ];

        $this->view('admin/approvals/index', $data);
    }

    /**
     * Phê duyệt sản phẩm (Approve)
     */
    public function approveProduct($id = null) {
        if (!$id || !is_numeric($id)) {
            setFlash('error', 'ID sản phẩm không hợp lệ');
            header('location: ' . URLROOT . '/admin/approvals');
            exit();
        }

        $productId = (int)$id;
        $product = $this->productModel->findById($productId);

        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại');
            header('location: ' . URLROOT . '/admin/approvals');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
            verifyCsrfToken($_POST['csrf_token']);
        }

        $adminId = (int)$_SESSION['user_id'];
        $note = trim($_POST['note'] ?? 'Sản phẩm đạt yêu cầu và được phê duyệt đăng tải');

        // Cập nhật trạng thái sản phẩm sang Approved (status = 2)
        if ($this->productModel->updateStatus($productId, 2)) {
            // Ghi lịch sử phê duyệt
            $this->productApprovalModel->logApproval($productId, $adminId, 'APPROVE', $note);
            setFlash('success', "Đã phê duyệt thành công sản phẩm '{$product->title}'!");
        } else {
            setFlash('error', 'Có lỗi xảy ra khi phê duyệt sản phẩm.');
        }

        header('location: ' . URLROOT . '/admin/approvals');
        exit();
    }

    /**
     * Từ chối sản phẩm (Reject)
     */
    public function rejectProduct($id = null) {
        if (!$id || !is_numeric($id)) {
            setFlash('error', 'ID sản phẩm không hợp lệ');
            header('location: ' . URLROOT . '/admin/approvals');
            exit();
        }

        $productId = (int)$id;
        $product = $this->productModel->findById($productId);

        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại');
            header('location: ' . URLROOT . '/admin/approvals');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
            verifyCsrfToken($_POST['csrf_token']);
        }

        $adminId = (int)$_SESSION['user_id'];
        $note = trim($_POST['note'] ?? 'Sản phẩm chưa đạt tiêu chuẩn kiểm duyệt');

        // Cập nhật trạng thái sản phẩm sang Rejected (status = 3)
        if ($this->productModel->updateStatus($productId, 3)) {
            // Ghi lịch sử phê duyệt
            $this->productApprovalModel->logApproval($productId, $adminId, 'REJECT', $note);
            setFlash('warning', "Đã từ chối đăng tải sản phẩm '{$product->title}'.");
        } else {
            setFlash('error', 'Có lỗi xảy ra khi từ chối sản phẩm.');
        }

        header('location: ' . URLROOT . '/admin/approvals');
        exit();
    }

    // =========================================================================
    // UC44: Quản lý Báo cáo vi phạm & Khiếu nại AI (Reports & AI Appeals)
    // =========================================================================

    /**
     * Danh sách Báo cáo vi phạm và Khiếu nại nhãn AI
     */
    public function reports() {
        $reports = $this->reportModel->getAllWithDetails();
        $appeals = $this->aiAppealModel->getAllWithDetails();

        $data = [
            'title' => 'Quản lý Báo cáo Vi phạm & Khiếu nại AI - Creono Admin',
            'reports' => $reports,
            'appeals' => $appeals
        ];

        $this->view('admin/reports/index', $data);
    }

    /**
     * Xử lý báo cáo vi phạm từ người dùng (Resolve / Dismiss / Investigate)
     */
    public function resolveReport($id = null) {
        if (!$id || !is_numeric($id)) {
            setFlash('error', 'ID báo cáo không hợp lệ');
            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }

        $reportId = (int)$id;
        $report = $this->reportModel->findById($reportId);

        if (!$report) {
            setFlash('error', 'Không tìm thấy báo cáo vi phạm');
            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
            verifyCsrfToken($_POST['csrf_token']);
        }

        $adminId = (int)$_SESSION['user_id'];
        $action = trim($_POST['action'] ?? 'resolve'); // 'resolve', 'dismiss', 'investigate'

        if ($action === 'resolve') {
            // Nếu chấp nhận báo cáo vi phạm đối tượng là PRODUCT -> Khóa sản phẩm (status = 3)
            if ($report->target_type === 'PRODUCT') {
                $this->productModel->updateStatus((int)$report->target_id, 3);
            }

            $this->reportModel->updateReportStatus($reportId, 3, $adminId); // 3: Resolved
            setFlash('success', "Đã giải quyết báo cáo vi phạm #{$reportId} thành công!");
        } elseif ($action === 'dismiss') {
            $this->reportModel->updateReportStatus($reportId, 4, $adminId); // 4: Dismissed
            setFlash('info', "Đã bác bỏ báo cáo vi phạm #{$reportId}.");
        } elseif ($action === 'investigate') {
            $this->reportModel->updateReportStatus($reportId, 2, $adminId); // 2: Investigating
            setFlash('warning', "Đang tiến hành điều tra báo cáo #{$reportId}.");
        }

        header('location: ' . URLROOT . '/admin/reports');
        exit();
    }

    /**
     * Xử lý khiếu nại nhãn AI từ Seller (Approve / Reject Appeal)
     */
    public function processAppeal($id = null) {
        if (!$id || !is_numeric($id)) {
            setFlash('error', 'ID khiếu nại không hợp lệ');
            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }

        $appealId = (int)$id;
        $appeal = $this->aiAppealModel->findById($appealId);

        if (!$appeal) {
            setFlash('error', 'Không tìm thấy khiếu nại nhãn AI');
            header('location: ' . URLROOT . '/admin/reports');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
            verifyCsrfToken($_POST['csrf_token']);
        }

        $adminId = (int)$_SESSION['user_id'];
        $action = trim($_POST['action'] ?? 'approve'); // 'approve', 'reject'

        if ($action === 'approve') {
            // Chấp nhận khiếu nại AI -> Khôi phục sản phẩm sang Approved (status = 2)
            $this->productModel->updateStatus((int)$appeal->product_id, 2);
            $this->aiAppealModel->updateAppealStatus($appealId, 2, $adminId); // 2: Approved
            setFlash('success', "Đã chấp nhận khiếu nại AI #{$appealId} và khôi phục sản phẩm!");
        } else {
            // Từ chối khiếu nại AI
            $this->aiAppealModel->updateAppealStatus($appealId, 3, $adminId); // 3: Rejected
            setFlash('warning', "Đã từ chối khiếu nại nhãn AI #{$appealId}.");
        }

        header('location: ' . URLROOT . '/admin/reports');
        exit();
    }

    /**
     * Helper tạo URL Slug chuẩn Tiếng Việt
     */
    private function slugify(string $text): string {
        $utf8_map = [
            'à'=>'a', 'á'=>'a', 'ả'=>'a', 'ã'=>'a', 'ạ'=>'a',
            'ă'=>'a', 'ằ'=>'a', 'ắ'=>'a', 'ẳ'=>'a', 'ẵ'=>'a', 'ặ'=>'a',
            'â'=>'a', 'ầ'=>'a', 'ấ'=>'a', 'ẩ'=>'a', 'ẫ'=>'a', 'ậ'=>'a',
            'đ'=>'d',
            'è'=>'e', 'é'=>'e', 'ẻ'=>'e', 'ẽ'=>'e', 'ẹ'=>'e',
            'ê'=>'e', 'ề'=>'e', 'ế'=>'e', 'ể'=>'e', 'ễ'=>'e', 'ệ'=>'e',
            'ì'=>'i', 'í'=>'i', 'ỉ'=>'i', 'ĩ'=>'i', 'ị'=>'i',
            'ò'=>'o', 'ó'=>'o', 'ỏ'=>'o', 'õ'=>'o', 'ọ'=>'o',
            'ô'=>'o', 'ồ'=>'o', 'ố'=>'o', 'ổ'=>'o', 'ỗ'=>'o', 'ộ'=>'o',
            'ơ'=>'o', 'ờ'=>'o', 'ớ'=>'o', 'ở'=>'o', 'ỡ'=>'o', 'ợ'=>'o',
            'ù'=>'u', 'ú'=>'u', 'ủ'=>'u', 'ũ'=>'u', 'ụ'=>'u',
            'ư'=>'u', 'ừ'=>'u', 'ứ'=>'u', 'ử'=>'u', 'ữ'=>'u', 'ự'=>'u',
            'ỳ'=>'y', 'ý'=>'y', 'ỷ'=>'y', 'ỹ'=>'y', 'ỵ'=>'y',
            'À'=>'a', 'Á'=>'a', 'Ả'=>'a', 'Ã'=>'a', 'Ạ'=>'a',
            'Ă'=>'a', 'Ằ'=>'a', 'Ắ'=>'a', 'Ẳ'=>'a', 'Ẵ'=>'a', 'Ặ'=>'a',
            'Â'=>'a', 'Ầ'=>'a', 'Ấ'=>'a', 'Ẩ'=>'a', 'Ẫ'=>'a', 'Ậ'=>'a',
            'Đ'=>'d',
            'È'=>'e', 'É'=>'e', 'Ẻ'=>'e', 'Ẽ'=>'e', 'Ẹ'=>'e',
            'Ê'=>'e', 'Ề'=>'e', 'Ế'=>'e', 'Ể'=>'e', 'Ễ'=>'e', 'Ệ'=>'e',
            'Ì'=>'i', 'Í'=>'i', 'Ỉ'=>'i', 'Ĩ'=>'i', 'Ị'=>'i',
            'Ò'=>'o', 'Ó'=>'o', 'Ỏ'=>'o', 'Õ'=>'o', 'Ọ'=>'o',
            'Ô'=>'o', 'Ồ'=>'o', 'Ố'=>'o', 'Ổ'=>'o', 'Ỗ'=>'o', 'Ộ'=>'o',
            'Ơ'=>'o', 'Ờ'=>'o', 'Ớ'=>'o', 'Ở'=>'o', 'Ỡ'=>'o', 'Ợ'=>'o',
            'Ù'=>'u', 'Ú'=>'u', 'Ủ'=>'u', 'Ũ'=>'u', 'Ụ'=>'u',
            'Ư'=>'u', 'Ừ'=>'u', 'Ứ'=>'u', 'Ử'=>'u', 'Ữ'=>'u', 'Ự'=>'u',
            'Ỳ'=>'y', 'Ý'=>'y', 'Ỷ'=>'y', 'Ỹ'=>'y', 'Ỵ'=>'y'
        ];
        
        $text = strtr($text, $utf8_map);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'danh-muc');
    }
}
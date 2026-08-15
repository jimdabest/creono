<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Core/Validator.php';

class Admin extends Controller {
    private $statModel;
    private $categoryModel;

    public function __construct() {
        // Chỉ cho phép Admin (role = 3) truy cập
        RoleMiddleware::check([3]);

        $this->statModel = $this->model('StatModel');
        $this->categoryModel = $this->model('Category');
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
            'seller_revenues' => $this->statModel->getSellerRevenueOverview(5)
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
            // Kiểm tra CSRF Token
            if (isset($_POST['csrf_token'])) {
                verifyCsrfToken($_POST['csrf_token']);
            }

            // Lấy & làm sạch dữ liệu đầu vào
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sort_order = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? (int)$_POST['sort_order'] : 0;

            // Nếu người dùng không nhập slug, tự động tạo slug từ name
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

            // Validation bằng Validator
            $validator = new Validator([
                'name' => $name,
                'slug' => $slug
            ]);

            $errors = $validator->validate([
                'name' => 'required',
                'slug' => 'required'
            ]);

            // Kiểm tra trùng lặp Tên & Slug
            if (!isset($errors['name_err']) && $this->categoryModel->nameExists($name)) {
                $errors['name_err'] = 'Tên danh mục này đã tồn tại';
            }

            if (!isset($errors['slug_err']) && $this->categoryModel->slugExists($slug)) {
                $errors['slug_err'] = 'Slug này đã tồn tại';
            }

            if (empty($errors)) {
                // Thêm vào DB
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
            // GET request - Hiển thị form tạo mới
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
            // Kiểm tra CSRF Token
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

            // Kiểm tra trùng lặp loại trừ ID hiện tại
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
            // GET request - Load form với dữ liệu cũ
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

        // Đảm bảo có xác thực nếu gửi theo form POST CSRF
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

    /**
     * Helper tạo URL Slug chuẩn Tiếng Việt
     */
    private function slugify(string $text): string {
        // Chuyển ký tự Tiếng Việt có dấu thành không dấu
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
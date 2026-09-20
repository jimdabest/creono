<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Helpers/mail_helper.php'; // Sử dụng hàm gửi email

class Stores extends Controller
{
    private Store $storeModel;
    private User $userModel;

    public function __construct()
    {
        AuthMiddleware::check(); // Đảm bảo luôn phải đăng nhập
        $this->storeModel = $this->model('Store');
        $this->userModel = $this->model('User');
    }

    // [UC19] Hiển thị và xử lý form tạo cửa hàng
    public function create(): void
    {
        // Nếu đã có cửa hàng
        $existingStore = $this->storeModel->getStoreByUserId($_SESSION['user_id']);
        if ($existingStore) {
            if ((int)$existingStore->status === 0) {
                setFlash('info', 'Hồ sơ mở cửa hàng "' . htmlspecialchars($existingStore->name) . '" của bạn đang chờ Quản trị viên xét duyệt.');
                header('location: ' . URLROOT . '/pages/index');
                exit();
            }
            if ((int)$existingStore->status === 1) {
                setFlash('info', 'Bạn đã có cửa hàng, hãy quản lý thông tin tại đây.');
                header('location: ' . URLROOT . '/stores/edit');
                exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            
            $data = [
                'user_id' => $_SESSION['user_id'],
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'bank_name' => trim($_POST['bank_name'] ?? ''),
                'bank_account_number' => trim($_POST['bank_account_number'] ?? ''),
                'bank_account_name' => trim($_POST['bank_account_name'] ?? ''),
                'status' => 0 // 0: Đang chờ Quản trị viên phê duyệt
            ];

            $errors = [];
            if (empty($data['name'])) {
                $errors['name_err'] = 'Vui lòng nhập tên cửa hàng.';
            }

            // Xử lý upload logo an toàn
            $logo_url = '';
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadLogo($_FILES['logo']);
                if ($uploadResult['success']) {
                    $logo_url = $uploadResult['path'];
                } else {
                    $errors['logo_err'] = $uploadResult['message'];
                }
            }
            $data['logo_url'] = $logo_url;

            // Xử lý upload giấy tờ đính kèm (giấy phép KD, giấy tờ xác minh)
            $document_url = '';
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $uploadDocResult = $this->uploadDocument($_FILES['document']);
                if ($uploadDocResult['success']) {
                    $document_url = $uploadDocResult['path'];
                } else {
                    $errors['document_err'] = $uploadDocResult['message'];
                }
            }
            $data['document_url'] = $document_url;

            if (empty($errors)) {
                if ($this->storeModel->createStore($data)) {
                    if (!empty($_SESSION['user_email']) && function_exists('sendTemplatedEmail')) {
                        $subject = 'Đã nhận hồ sơ đăng ký cửa hàng';

                        $emailTitle = 'Đã nhận hồ sơ đăng ký cửa hàng';
                        $emailContent = '<p>Xin chào <strong>' . htmlspecialchars($_SESSION['user_name'] ?? 'bạn') . '</strong>,</p>'
                            . '<p>Hồ sơ mở cửa hàng <strong>' . htmlspecialchars($data['name']) . '</strong> của bạn đã được gửi thành công và đang chờ Quản trị viên Creono xét duyệt.</p>'
                            . '<p>Chúng tôi sẽ thông báo kết quả qua email trong thời gian sớm nhất.</p>';
                        $ctaText = 'Về trang chủ';
                        $ctaLink = URLROOT;
                        $footerNote = 'Cảm ơn bạn đã tin tưởng Creono.';

                        sendTemplatedEmail(
                            $_SESSION['user_email'],
                            $subject,
                            $emailTitle,
                            $emailContent,
                            $ctaText,
                            $ctaLink,
                            $footerNote
                        );
                    }

                    setFlash('success', 'Đăng ký bán hàng thành công! Hồ sơ của bạn đang được Quản trị viên xét duyệt.');
                    header('location: ' . URLROOT . '/pages/index');
                    exit();
                } else {
                    setFlash('error', 'Đã xảy ra lỗi hệ thống, vui lòng thử lại.');
                }
            }

            $viewData = [
                'title' => 'Đăng ký bán hàng',
                'store' => (object)$data,
                'errors' => $errors,
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('store/create', $viewData);
        } else {
            $viewData = [
                'title' => 'Đăng ký bán hàng',
                'store' => (object)[
                    'name' => '', 'description' => '', 'phone' => '', 'address' => '',
                    'bank_name' => '', 'bank_account_number' => '', 'bank_account_name' => ''
                ],
                'errors' => [],
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('store/create', $viewData);
        }
    }

    // [UC20] Hiển thị form chỉnh sửa cửa hàng
    public function edit(): void
    {
        RoleMiddleware::check([2]);
        $store = $this->storeModel->getStoreByUserId($_SESSION['user_id']);
        if (!$store) {
            setFlash('error', 'Bạn chưa có cửa hàng.');
            header('location: ' . URLROOT . '/stores/create');
            exit();
        }

        $data = [
            'title' => 'Chỉnh sửa cửa hàng',
            'store' => $store,
            'errors' => [],
            'csrf_token' => generateCsrfToken()
        ];
        $this->view('store/edit', $data);
    }

    // [UC20] Xử lý cập nhật cửa hàng
    public function update(): void
    {
        RoleMiddleware::check([2]);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: ' . URLROOT . '/stores/edit');
            exit();
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        $store = $this->storeModel->getStoreByUserId($_SESSION['user_id']);
        if (!$store) {
            setFlash('error', 'Cửa hàng không tồn tại.');
            header('location: ' . URLROOT . '/stores/create');
            exit();
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'bank_name' => trim($_POST['bank_name'] ?? ''),
            'bank_account_number' => trim($_POST['bank_account_number'] ?? ''),
            'bank_account_name' => trim($_POST['bank_account_name'] ?? ''),
        ];

        $errors = [];
        if (empty($data['name'])) {
            $errors['name_err'] = 'Vui lòng nhập tên cửa hàng.';
        }

        $logo_url = $store->logo_url;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadLogo($_FILES['logo']);
            if ($uploadResult['success']) {
                // Xóa logo cũ
                if (!empty($store->logo_url) && file_exists('../public' . $store->logo_url)) {
                    @unlink('../public' . $store->logo_url);
                }
                $logo_url = $uploadResult['path'];
            } else {
                $errors['logo_err'] = $uploadResult['message'];
            }
        }
        $data['logo_url'] = $logo_url;

        if (empty($errors)) {
            if ($this->storeModel->updateStore((int)$store->id, $data)) {
                setFlash('success', 'Cập nhật thông tin cửa hàng thành công!');
                header('location: ' . URLROOT . '/stores/edit');
                exit();
            } else {
                setFlash('error', 'Có lỗi xảy ra khi cập nhật.');
            }
        }

        $viewData = [
            'title' => 'Chỉnh sửa cửa hàng',
            'store' => (object)array_merge((array)$store, $data),
            'errors' => $errors,
            'csrf_token' => generateCsrfToken()
        ];
        $this->view('store/edit', $viewData);
    }

    private function uploadLogo(array $file): array
    {
        $targetDir = '../public/uploads/stores/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Validate Size Max 2MB
        if ($file['size'] > 2097152) { 
            return ['success' => false, 'message' => 'Kích thước file không được vượt quá 2MB.'];
        }

        // Validate MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return ['success' => false, 'message' => 'Chỉ hỗ trợ file ảnh (JPG, PNG, GIF, WebP).'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newName = time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $targetDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'path' => '/uploads/stores/' . $newName];
        }

        return ['success' => false, 'message' => 'Lỗi kỹ thuật, không thể tải lên file.'];
    }

    private function uploadDocument(array $file): array
    {
        $targetDir = '../public/uploads/stores/documents/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Validate Size Max 5MB
        if ($file['size'] > 5242880) {
            return ['success' => false, 'message' => 'Kích thước giấy tờ đính kèm không được vượt quá 5MB.'];
        }

        // Validate MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return ['success' => false, 'message' => 'Chỉ hỗ trợ file ảnh (JPG, PNG, WebP) hoặc tài liệu PDF.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newName = 'doc_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $targetDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'path' => '/uploads/stores/documents/' . $newName];
        }

        return ['success' => false, 'message' => 'Lỗi kỹ thuật, không thể tải lên giấy tờ đính kèm.'];
    }
}
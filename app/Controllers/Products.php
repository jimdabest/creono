<?php

declare(strict_types=1);

require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';
require_once '../app/Helpers/csrf_helper.php';
require_once '../app/Helpers/flash_helper.php';
require_once '../app/Helpers/WatermarkService.php';
require_once '../app/Services/AiDetectionService.php';

class Products extends Controller
{
    private Product $productModel;
    private Review $reviewModel;
    private Favorite $favoriteModel;
    private Cart $cartModel;
    private Category $categoryModel;
    private Order $orderModel;
    private Store $storeModel;

    public function __construct()
    {
        $this->productModel  = $this->model('Product');
        $this->reviewModel   = $this->model('Review');
        $this->favoriteModel = $this->model('Favorite');
        $this->cartModel     = $this->model('Cart');
        $this->categoryModel = $this->model('Category');
        $this->orderModel    = $this->model('Order');
        $this->storeModel    = $this->model('Store');
    }

    // ===================== CÁC ACTION CŨ =====================

    public function index(): void
    {
        $category = $_GET['category'] ?? '';
        $keyword  = $_GET['keyword'] ?? '';
        $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
        $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
        $sort     = $_GET['sort'] ?? 'newest';

        $products = $this->productModel->getProductsFiltered($category, $keyword, $minPrice, $maxPrice, $sort);

        $favoriteIds = [];
        if (isset($_SESSION['user_id'])) {
            $favoriteIds = $this->favoriteModel->getFavoriteProductIds((int)$_SESSION['user_id']);
        }

        $data = [
            'title' => 'Chợ Tài Liệu - Sân Sàn C2C',
            'products' => $products,
            'favorite_ids' => $favoriteIds,
            'csrf_token' => generateCsrfToken(),
            'category' => $category,
            'keyword' => $keyword,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sort
        ];

        $this->view('products/index', $data);
    }

    public function detail(int $productId = 0): void
    {
        if ($productId <= 0) {
            header('location: ' . URLROOT . '/products/index');
            exit();
        }
        $product = $this->productModel->getProductDetail($productId);
        if (!$product || $product->status != 2) {
            header('location: ' . URLROOT . '/products/index');
            exit();
        }
        $reviews = $this->reviewModel->getReviewsByProductId($productId);
        foreach ($reviews as &$review) {
            $review->replies = $this->reviewModel->getRepliesByReviewId($review->id);
        }
        unset($review);
        $ratingStats = $this->reviewModel->getRatingStats($productId);
        $hasReviewed = false;
        $isFavorited = false;
        $inCart = false;
        $hasPurchased = false;
        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $hasReviewed = $this->reviewModel->hasUserReviewed($productId, $userId);
            $isFavorited = $this->favoriteModel->isFavorited($userId, $productId);
            $cart = $this->cartModel->getOrCreateCart($userId);
            $inCart = $this->cartModel->hasItem((int)$cart->id, $productId);
            $hasPurchased = $this->orderModel->hasPurchased($userId, $productId);
        } else {
            $inCart = isset($_SESSION['guest_cart']) && in_array($productId, $_SESSION['guest_cart']);
        }
        $isSeller = isset($_SESSION['user_id']) &&
            isset($product->seller_id) &&
            (int)$product->seller_id === (int)$_SESSION['user_id'];
        $data = [
            'title' => htmlspecialchars($product->title) . ' - Creono',
            'description' => htmlspecialchars(substr($product->description ?? '', 0, 150)),
            'product' => $product,
            'reviews' => $reviews,
            'rating_stats' => $ratingStats,
            'has_reviewed' => $hasReviewed,
            'is_favorited' => $isFavorited,
            'in_cart' => $inCart,
            'is_seller' => $isSeller,
            'has_purchased' => $hasPurchased,
            'csrf_token' => generateCsrfToken()
        ];
        $this->view('products/detail', $data);
    }

    // ===================== ACTION CREATE (Seller) =====================

    public function create(): void
    {
        RoleMiddleware::check([2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price       = str_replace(',', '', $_POST['price'] ?? '0');
            $category_id = (int)($_POST['category_id'] ?? 0);

            $errors = [];
            if (empty($title)) {
                $errors['title_err'] = 'Vui lòng nhập tiêu đề sản phẩm';
            } elseif (strlen($title) > 255) {
                $errors['title_err'] = 'Tiêu đề không được vượt quá 255 ký tự';
            }
            if (empty($description)) {
                $errors['description_err'] = 'Vui lòng nhập mô tả sản phẩm';
            }
            if (empty($price) || $price <= 0) {
                $errors['price_err'] = 'Vui lòng nhập giá bán hợp lệ (lớn hơn 0)';
            } elseif (!is_numeric($price)) {
                $errors['price_err'] = 'Giá bán phải là số';
            }
            if ($category_id <= 0) {
                $errors['category_err'] = 'Vui lòng chọn danh mục';
            }

            $preview_url = '';
            if (isset($_FILES['preview_image']) && $_FILES['preview_image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadFile($_FILES['preview_image'], 'products/images/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                if ($uploadResult['success']) {
                    $preview_url = $uploadResult['path'];
                } else {
                    $errors['preview_err'] = $uploadResult['message'];
                }
            }

            $document_url = '';
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadFile($_FILES['document_file'], 'products/files/', ['application/zip', 'application/pdf', 'application/x-rar-compressed', 'application/octet-stream']);
                if ($uploadResult['success']) {
                    $document_url = $uploadResult['path'];
                } else {
                    $errors['document_err'] = $uploadResult['message'];
                }
            } else {
                $errors['document_err'] = 'Vui lòng tải lên file tài liệu (ZIP, PDF, RAR)';
            }

            if (empty($errors)) {
                $store_id = $this->getStoreIdByUserId($_SESSION['user_id']);
                if (!$store_id) {
                    setFlash('error', 'Bạn chưa có cửa hàng. Vui lòng liên hệ Admin.');
                    header('location: ' . URLROOT . '/products/index');
                    exit();
                }

                $store = $this->storeModel->findById($store_id);
                $storeName = $store ? $store->name : 'Creono';

                // ==== UC28: TỰ ĐỘNG ĐÓNG DẤU WATERMARK TRƯỚC KHI LƯU DB ====
                if (!empty($preview_url)) {
                    $physicalPreviewPath = '../public' . $preview_url;
                    WatermarkService::processUpload($physicalPreviewPath, $storeName);
                }

                if (!empty($document_url)) {
                    $physicalDocPath = '../public' . $document_url;
                    $docExt = strtolower(pathinfo($document_url, PATHINFO_EXTENSION));
                    if ($docExt === 'pdf') {
                        WatermarkService::processUpload($physicalDocPath, $storeName);
                    }
                }

                $productData = [
                    'store_id'     => $store_id,
                    'category_id'  => $category_id,
                    'title'        => $title,
                    'description'  => $description,
                    'price'        => $price,
                    'preview_url'  => $preview_url,
                    'status'       => 1, // Pending
                    'created_at'   => date('Y-m-d H:i:s')
                ];

                if ($this->productModel->create($productData)) {
                    $productId = $this->productModel->getLastInsertId();
                    if (!empty($document_url)) {
                        $documentModel = $this->model('Document');
                        $documentData = [
                            'product_id'  => $productId,
                            'file_url'    => $document_url,
                            'ai_score'    => null,
                            'ai_label_id' => null
                        ];
                        $documentModel->create($documentData);

                        // UC25: Phân tích AI thực tế bằng AiDetectionService
                        $aiResult  = AiDetectionService::detect((string)($description ?? ''), (string)($title ?? ''));
                        $documentModel->update($documentModel->getLastInsertId(), [
                            'ai_score'    => $aiResult['ai_score'],
                            'ai_label_id' => $aiResult['ai_label_id']
                        ]);
                    }
                    setFlash('success', 'Đã tạo sản phẩm thành công! Vui lòng chờ Admin duyệt.');
                    header('location: ' . URLROOT . '/seller/dashboard');
                    exit();
                } else {
                    setFlash('error', 'Có lỗi xảy ra khi lưu sản phẩm. Vui lòng thử lại.');
                }
            }

            $data = [
                'title'        => 'Đăng sản phẩm mới',
                'categories'   => $this->categoryModel->getAllOrdered(),
                'product'      => (object)[
                    'title'       => $title,
                    'description' => $description,
                    'price'       => $price,
                    'category_id' => $category_id,
                ],
                'errors'       => $errors,
                'csrf_token'   => generateCsrfToken()
            ];
            $this->view('products/create', $data);
        } else {
            $data = [
                'title'      => 'Đăng sản phẩm mới',
                'categories' => $this->categoryModel->getAllOrdered(),
                'product'    => null,
                'errors'     => [],
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('products/create', $data);
        }
    }

    // ===================== ACTION EDIT (Seller) =====================

    //    public function edit(?int $id = null): void
    //     {
    //         RoleMiddleware::check([2]);

    //         if (!$id) {
    //             setFlash('error', 'ID sản phẩm không hợp lệ');
    //             header('location: ' . URLROOT . '/seller/dashboard');
    //             exit();
    //         }

    //         $productId = $id;
    //         $product = $this->productModel->getProductDetail($productId);
    //         if (!$product) {
    //             setFlash('error', 'Sản phẩm không tồn tại');
    //             header('location: ' . URLROOT . '/seller/dashboard');
    //             exit();
    //         }
    //         if ((int)$product->seller_id !== (int)$_SESSION['user_id']) {
    //             setFlash('error', 'Bạn không có quyền chỉnh sửa sản phẩm này');
    //             header('location: ' . URLROOT . '/seller/dashboard');
    //             exit();
    //         }

    //         $document = $this->productModel->getDocumentByProductId($productId);

    //         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //             if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    //                 die('CSRF token validation failed');
    //             }
    //             $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    //             $title       = trim($_POST['title'] ?? '');
    //             $description = trim($_POST['description'] ?? '');
    //             $price       = str_replace(',', '', $_POST['price'] ?? '0');
    //             $category_id = (int)($_POST['category_id'] ?? 0);

    //             $errors = [];
    //             if (empty($title)) $errors['title_err'] = 'Vui lòng nhập tiêu đề';
    //             if (empty($description)) $errors['description_err'] = 'Vui lòng nhập mô tả';
    //             if (empty($price) || $price <= 0) $errors['price_err'] = 'Giá bán phải lớn hơn 0';
    //             if ($category_id <= 0) $errors['category_err'] = 'Vui lòng chọn danh mục';

    //             $preview_url = $product->preview_url;
    //             if (isset($_FILES['preview_image']) && $_FILES['preview_image']['error'] === UPLOAD_ERR_OK) {
    //                 $uploadResult = $this->uploadFile($_FILES['preview_image'], 'products/images/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    //                 if ($uploadResult['success']) {
    //                     if (!empty($product->preview_url) && file_exists('../public' . $product->preview_url)) {
    //                         unlink('../public' . $product->preview_url);
    //                     }
    //                     $preview_url = $uploadResult['path'];
    //                 } else {
    //                     $errors['preview_err'] = $uploadResult['message'];
    //                 }
    //             }

    //             $document_url = $document ? $document->file_url : '';
    //             if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
    //                 $uploadResult = $this->uploadFile($_FILES['document_file'], 'products/files/', ['application/zip', 'application/pdf', 'application/x-rar-compressed', 'application/octet-stream']);
    //                 if ($uploadResult['success']) {
    //                     if (!empty($document_url) && file_exists('../public' . $document_url)) {
    //                         unlink('../public' . $document_url);
    //                     }
    //                     $document_url = $uploadResult['path'];
    //                 } else {
    //                     $errors['document_err'] = $uploadResult['message'];
    //                 }
    //             }

    //             if (empty($errors)) {
    //                 $updateData = [
    //                     'title'        => $title,
    //                     'description'  => $description,
    //                     'price'        => $price,
    //                     'category_id'  => $category_id,
    //                     'preview_url'  => $preview_url
    //                 ];
    //                 if ($this->productModel->update($productId, $updateData)) {
    //                     if (!empty($document_url)) {
    //                         $documentModel = $this->model('Document');
    //                         if ($document) {
    //                             $documentModel->updateFileUrl($productId, $document_url);
    //                         } else {
    //                             $docData = ['product_id' => $productId, 'file_url' => $document_url];
    //                             $documentModel->create($docData);
    //                         }
    //                     }
    //                     setFlash('success', 'Cập nhật sản phẩm thành công!');
    //                     header('location: ' . URLROOT . '/seller/dashboard');
    //                     exit();
    //                 } else {
    //                     setFlash('error', 'Có lỗi xảy ra khi cập nhật sản phẩm');
    //                 }
    //             }

    //             $data = [
    //                 'title'      => 'Chỉnh sửa sản phẩm',
    //                 'categories' => $this->categoryModel->getAllOrdered(),
    //                 'product'    => (object) array_merge((array)$product, [
    //                     'title'       => $title,
    //                     'description' => $description,
    //                     'price'       => $price,
    //                     'category_id' => $category_id,
    //                 ]),
    //                 'document'   => $document,
    //                 'errors'     => $errors,
    //                 'csrf_token' => generateCsrfToken()
    //             ];
    //             $this->view('products/edit', $data);
    //         } else {
    //             $data = [
    //                 'title'      => 'Chỉnh sửa sản phẩm',
    //                 'categories' => $this->categoryModel->getAllOrdered(),
    //                 'product'    => $product,
    //                 'document'   => $document,
    //                 'errors'     => [],
    //                 'csrf_token' => generateCsrfToken()
    //             ];
    //             $this->view('products/edit', $data);
    //         }
    //     } 
    public function edit(?int $id = null): void
    {
        RoleMiddleware::check([2]);

        if (!$id) {
            setFlash('error', 'ID sản phẩm không hợp lệ');
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }

        $productId = $id;
        $product = $this->productModel->getProductDetail($productId);
        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại');
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }
        if ((int)$product->seller_id !== (int)$_SESSION['user_id']) {
            setFlash('error', 'Bạn không có quyền chỉnh sửa sản phẩm này');
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }

        $document = $this->productModel->getDocumentByProductId($productId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                die('CSRF token validation failed');
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price       = str_replace(',', '', $_POST['price'] ?? '0');
            $category_id = (int)($_POST['category_id'] ?? 0);

            $errors = [];
            if (empty($title)) $errors['title_err'] = 'Vui lòng nhập tiêu đề';
            if (empty($description)) $errors['description_err'] = 'Vui lòng nhập mô tả';
            if (empty($price) || $price <= 0) $errors['price_err'] = 'Giá bán phải lớn hơn 0';
            if ($category_id <= 0) $errors['category_err'] = 'Vui lòng chọn danh mục';

            // --- XỬ LÝ UPLOAD ẢNH COVER MỚI ---
            $preview_url = $product->preview_url;
            if (isset($_FILES['preview_image']) && $_FILES['preview_image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadFile(
                    $_FILES['preview_image'],
                    'products/images/',
                    ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
                );
                if ($uploadResult['success']) {
                    // Xóa ảnh cũ nếu tồn tại và là file thực
                    if (!empty($product->preview_url)) {
                        $oldPath = '../public' . $product->preview_url;
                        if (is_file($oldPath) && file_exists($oldPath)) {
                            if (!unlink($oldPath)) {
                                // Ghi log lỗi nếu không xóa được
                                error_log("Không thể xóa file ảnh cũ: " . $oldPath);
                            }
                        }
                    }
                    $preview_url = $uploadResult['path'];
                    $store_id = (int)$product->store_id;
                    $store = $this->storeModel->findById($store_id);
                    $storeName = $store ? $store->name : 'Creono';
                    WatermarkService::processUpload('../public' . $preview_url, $storeName);
                } else {
                    $errors['preview_err'] = $uploadResult['message'];
                }
            }

            // --- XỬ LÝ UPLOAD FILE TÀI LIỆU MỚI ---
            $document_url = $document ? $document->file_url : '';
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadFile(
                    $_FILES['document_file'],
                    'products/files/',
                    ['application/zip', 'application/pdf', 'application/x-rar-compressed', 'application/octet-stream']
                );
                if ($uploadResult['success']) {
                    // Xóa file cũ nếu tồn tại và là file thực
                    if (!empty($document_url)) {
                        $oldPath = '../public' . $document_url;
                        if (is_file($oldPath) && file_exists($oldPath)) {
                            if (!unlink($oldPath)) {
                                error_log("Không thể xóa file tài liệu cũ: " . $oldPath);
                            }
                        }
                    }
                    $document_url = $uploadResult['path'];
                    $docExt = strtolower(pathinfo($document_url, PATHINFO_EXTENSION));
                    if ($docExt === 'pdf') {
                        $store_id = (int)$product->store_id;
                        $store = $this->storeModel->findById($store_id);
                        $storeName = $store ? $store->name : 'Creono';
                        WatermarkService::processUpload('../public' . $document_url, $storeName);
                    }
                } else {
                    $errors['document_err'] = $uploadResult['message'];
                }
            }

            // --- CẬP NHẬT DATABASE ---
            if (empty($errors)) {
                $updateData = [
                    'title'        => $title,
                    'description'  => $description,
                    'price'        => $price,
                    'category_id'  => $category_id,
                    'preview_url'  => $preview_url
                ];
                if ($this->productModel->update($productId, $updateData)) {
                    // Cập nhật file tài liệu nếu có thay đổi
                    if (!empty($document_url)) {
                        $documentModel = $this->model('Document');
                        $documentData = [
                            'product_id'  => $productId,
                            'file_url'    => $document_url,
                            'ai_score'    => null,
                            'ai_label_id' => null
                        ];
                        $documentModel->create($documentData);

                        // UC25: Tái quét AI khi người bán cập nhật mô tả/tiêu đề
                        $aiResult = AiDetectionService::detect((string)($description ?? ''), (string)($title ?? ''));
                        $docId    = $documentModel->getLastInsertId();
                        $documentModel->update($docId, [
                            'ai_score'    => $aiResult['ai_score'],
                            'ai_label_id' => $aiResult['ai_label_id']
                        ]);
                    }
                    setFlash('success', 'Cập nhật sản phẩm thành công!');
                    header('location: ' . URLROOT . '/seller/dashboard');
                    exit();
                } else {
                    setFlash('error', 'Có lỗi xảy ra khi cập nhật sản phẩm');
                }
            }

            // --- NẾU CÓ LỖI, HIỂN THỊ LẠI FORM VỚI DỮ LIỆU CŨ ---
            $data = [
                'title'      => 'Chỉnh sửa sản phẩm',
                'categories' => $this->categoryModel->getAllOrdered(),
                'product'    => (object) array_merge((array)$product, [
                    'title'       => $title,
                    'description' => $description,
                    'price'       => $price,
                    'category_id' => $category_id,
                ]),
                'document'   => $document,
                'errors'     => $errors,
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('products/edit', $data);
        } else {
            // GET: hiển thị form với dữ liệu hiện tại
            $data = [
                'title'      => 'Chỉnh sửa sản phẩm',
                'categories' => $this->categoryModel->getAllOrdered(),
                'product'    => $product,
                'document'   => $document,
                'errors'     => [],
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('products/edit', $data);
        }
    }

    // ===================== ACTION DELETE (Seller) =====================

    public function delete(?int $id = null): void
    {
        RoleMiddleware::check([2]);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlash('error', 'Phương thức không được hỗ trợ');
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }

        if (!$id) {
            setFlash('error', 'ID sản phẩm không hợp lệ');
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }

        $productId = $id;
        $product = $this->productModel->getProductDetail($productId);
        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại');
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }
        if ((int)$product->seller_id !== (int)$_SESSION['user_id']) {
            setFlash('error', 'Bạn không có quyền xóa sản phẩm này');
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }

        if ($this->productModel->delete($productId)) {
            setFlash('success', 'Đã xóa sản phẩm thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra khi xóa sản phẩm');
        }
        header('location: ' . URLROOT . '/seller/dashboard');
        exit();
    }

    // ===================== HELPER: Upload file =====================

    /**
     * Upload file với kiểm tra MIME type
     *
     * @param array $file $_FILES item
     * @param string $subdir Thư mục con trong public/uploads/
     * @param array $allowedMimeTypes Danh sách MIME type cho phép
     * @return array ['success' => bool, 'path' => string, 'message' => string]
     */
    /**
     * Upload file với kiểm tra an toàn (MIME type, Size, Extension)
     */
    private function uploadFile(array $file, string $subdir, array $allowedMimeTypes): array
    {
        $targetDir = '../public/uploads/' . $subdir;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // 1. Kiểm tra dung lượng (giới hạn lấy từ config, ví dụ 5MB = 5242880 byte)
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return ['success' => false, 'message' => 'Dung lượng file vượt quá giới hạn cho phép (Tối đa 5MB).'];
        }

        // 2. Kiểm tra MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return ['success' => false, 'message' => 'Loại dữ liệu file không được hỗ trợ.'];
        }

        // 3. Kiểm tra đuôi file (Extension) để chống bypass giả mạo MIME type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'message' => 'Đuôi định dạng file không hợp lệ.'];
        }

        $newName = time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $targetDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'path' => '/uploads/' . $subdir . $newName];
        }

        return ['success' => false, 'message' => 'Không thể di chuyển file lưu trữ.'];
    }

    /**
     * Lấy store_id của seller
     *
     * @param int $userId
     * @return int|null
     */
    private function getStoreIdByUserId(int $userId): ?int
    {
        $storeModel = $this->model('Store');
        return $storeModel->getStoreIdByUserId($userId);
    }
}

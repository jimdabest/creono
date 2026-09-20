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
        $this->productModel = $this->model('Product');
        $this->reviewModel = $this->model('Review');
        $this->favoriteModel = $this->model('Favorite');
        $this->cartModel = $this->model('Cart');
        $this->categoryModel = $this->model('Category');
        $this->orderModel = $this->model('Order');
        $this->storeModel = $this->model('Store');
    }

    // ===================== CÁC ACTION CŨ =====================

    // app/Controllers/Products.php

    public function index(): void
    {
        // 1. Đọc tham số từ URL (sanitize)
        $search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
        $sort = isset($_GET['sort']) ? trim((string) $_GET['sort']) : 'newest';
        
        // Phân trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $limit = 12; // Hiển thị 12 sản phẩm mỗi trang (đẹp cho grid 3 hoặc 4 cột)
        $offset = ($page - 1) * $limit;

        // Whitelist sort để tránh lỗi khi URL thủ công
        $allowedSorts = ['newest', 'price_asc', 'price_desc', 'popular', 'rating'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        // Giới hạn độ dài keyword
        if (mb_strlen($search) > 100) {
            $search = mb_substr($search, 0, 100);
        }

        // 2. Lấy dữ liệu (Truyền thêm limit và offset)
        $products = $this->productModel->getProducts($search, $categoryId, $sort, $limit, $offset);
        $totalCount = $this->productModel->countProducts($search, $categoryId);
        $categories = $this->categoryModel->getAllOrdered();
        
        // Tính tổng số trang
        $totalPages = ceil($totalCount / $limit);

        // 3. Favorite IDs nếu đã đăng nhập
        $favoriteIds = [];
        if (isset($_SESSION['user_id'])) {
            $favoriteIds = $this->favoriteModel->getFavoriteProductIds((int) $_SESSION['user_id']);
        }

        // 4. Truyền dữ liệu ra View
        $data = [
            'title' => 'Chợ Tài Liệu - Creono',
            'description' => 'Khám phá hàng ngàn tài liệu số chất lượng cao trên Creono.',
            'products' => $products,
            'categories' => $categories,
            'favorite_ids' => $favoriteIds,
            'current_keyword' => $search,
            'current_category' => $categoryId,
            'current_sort' => $sort,
            'total_count' => $totalCount,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'csrf_token' => generateCsrfToken()
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
        $orderStatus = null;
        if (isset($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
            $hasPurchased = $this->orderModel->hasPurchased($userId, $productId);
            $hasReviewed = $this->reviewModel->hasUserReviewed($productId, $userId);
            $isFavorited = $this->favoriteModel->isFavorited($userId, $productId);
            $cart = $this->cartModel->getOrCreateCart($userId);
            $inCart = $this->cartModel->hasItem((int) $cart->id, $productId);
            $order = $this->orderModel->getOrderByUserAndProduct($userId, $productId);
            if ($order) {
                $orderStatus = (int) $order->status;
                if (in_array($orderStatus, [Order::STATUS_PAID, Order::STATUS_RECEIVED])) {
                    $hasPurchased = true;
                }
            }
        } else {
            $inCart = isset($_SESSION['guest_cart']) && in_array($productId, $_SESSION['guest_cart']);
        }
        $isSeller = isset($_SESSION['user_id']) &&
            isset($product->seller_id) &&
            (int) $product->seller_id === (int) $_SESSION['user_id'];
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
            'buyer_order_status' => $orderStatus,
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

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = str_replace(',', '', $_POST['price'] ?? '0');
            $category_id = (int) ($_POST['category_id'] ?? 0);

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
                $store_id = $this->getStoreIdByUserId((int) $_SESSION['user_id']);
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
                    'store_id' => $store_id,
                    'category_id' => $category_id,
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'preview_url' => $preview_url,
                    'status' => 1, // Pending
                    'created_at' => date('Y-m-d H:i:s')
                ];

                if ($this->productModel->create($productData)) {
                    $productId = $this->productModel->getLastInsertId();
                    if (!empty($document_url)) {
                        $documentModel = $this->model('Document');
                        $documentData = [
                            'product_id' => $productId,
                            'file_url' => $document_url,
                            'ai_score' => null,
                            'ai_label_id' => null
                        ];
                        $documentModel->create($documentData);

                        // UC25: Phân tích AI thực tế bằng AiDetectionService
                        $aiResult = AiDetectionService::detect((string) ($description ?? ''), (string) ($title ?? ''));
                        $documentModel->update($documentModel->getLastInsertId(), [
                            'ai_score' => $aiResult['ai_score'],
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
                'title' => 'Đăng sản phẩm mới',
                'categories' => $this->categoryModel->getAllOrdered(),
                'product' => (object) [
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'category_id' => $category_id,
                ],
                'errors' => $errors,
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('products/create', $data);
        } else {
            $data = [
                'title' => 'Đăng sản phẩm mới',
                'categories' => $this->categoryModel->getAllOrdered(),
                'product' => null,
                'errors' => [],
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
        if ((int) $product->seller_id !== (int) $_SESSION['user_id']) {
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

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = str_replace(',', '', $_POST['price'] ?? '0');
            $category_id = (int) ($_POST['category_id'] ?? 0);

            $errors = [];
            if (empty($title))
                $errors['title_err'] = 'Vui lòng nhập tiêu đề';
            if (empty($description))
                $errors['description_err'] = 'Vui lòng nhập mô tả';
            if (empty($price) || $price <= 0)
                $errors['price_err'] = 'Giá bán phải lớn hơn 0';
            if ($category_id <= 0)
                $errors['category_err'] = 'Vui lòng chọn danh mục';

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
                    $store_id = (int) $product->store_id;
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
                        $store_id = (int) $product->store_id;
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
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'category_id' => $category_id,
                    'preview_url' => $preview_url
                ];
                if ($this->productModel->update($productId, $updateData)) {
                    // Xử lý file tài liệu: cập nhật nếu đã có, tạo mới nếu chưa
                    if (!empty($document_url)) {
                        $documentModel = $this->model('Document');
                        $aiResult = AiDetectionService::detect((string) ($description ?? ''), (string) ($title ?? ''));

                        $documentId = isset($document->id) ? (int) $document->id : 0;

                        if ($documentId > 0) {
                            $documentModel->update($documentId, [
                                'file_url' => $document_url,
                                'ai_score' => $aiResult['ai_score'],
                                'ai_label_id' => $aiResult['ai_label_id']
                            ]);
                        } else {
                            $documentModel->create([
                                'product_id' => $productId,
                                'file_url' => $document_url,
                                'ai_score' => $aiResult['ai_score'],
                                'ai_label_id' => $aiResult['ai_label_id']
                            ]);
                        }
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
                'title' => 'Chỉnh sửa sản phẩm',
                'categories' => $this->categoryModel->getAllOrdered(),
                'product' => (object) array_merge((array) $product, [
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'category_id' => $category_id,
                ]),
                'document' => $document,
                'errors' => $errors,
                'csrf_token' => generateCsrfToken()
            ];
            $this->view('products/edit', $data);
        } else {
            // GET: hiển thị form với dữ liệu hiện tại
            $data = [
                'title' => 'Chỉnh sửa sản phẩm',
                'categories' => $this->categoryModel->getAllOrdered(),
                'product' => $product,
                'document' => $document,
                'errors' => [],
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
        if ((int) $product->seller_id !== (int) $_SESSION['user_id']) {
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

    // // Thêm vào class Products
    // public function manage(): void
    // {
    //     RoleMiddleware::check([2]); // Chỉ Seller

    //     $userId = (int)$_SESSION['user_id'];
    //     $storeId = $this->getStoreIdByUserId($userId);
    //     if (!$storeId) {
    //         setFlash('error', 'Bạn chưa có cửa hàng.');
    //         header('location: ' . URLROOT . '/seller/dashboard');
    //         exit();
    //     }

    //     $products = $this->productModel->getProductsByStoreId($storeId);

    //     $data = [
    //         'title' => 'Quản lý sản phẩm',
    //         'products' => $products,
    //         'csrf_token' => generateCsrfToken()
    //     ];
    //     $this->view('products/manage', $data);
    // }
    public function manage(): void
    {
        RoleMiddleware::check([2]); // Chỉ Seller

        $userId = (int) $_SESSION['user_id'];
        $storeId = $this->getStoreIdByUserId($userId);
        if (!$storeId) {
            setFlash('error', 'Bạn chưa có cửa hàng.');
            header('location: ' . URLROOT . '/seller/dashboard');
            exit();
        }

        // Gọi hàm mới với tham số phù hợp
        $products = $this->productModel->getProductsByStoreId(
            $storeId,
            null,
            0,
            'p.created_at DESC',
            '',
            false
        );

        $data = [
            'title' => 'Quản lý sản phẩm',
            'products' => $products,
            'csrf_token' => generateCsrfToken()
        ];
        $this->view('products/manage', $data);
    }

    // ===================== UC28: XEM TRƯỚC (PREVIEW) =====================

    /**
     * API lấy thông tin preview của sản phẩm đã lưu (cho trang detail)
     * URL: /products/getPreviewData/{productId}
     * Trả về JSON: mode, preview_url, pdf_url, preview_images, watermark_text
     */
    public function getPreviewData(?int $productId = null): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            exit();
        }

        $product = $this->productModel->getProductDetail($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
            exit();
        }

        $document = $this->productModel->getDocumentByProductId($productId);
        $watermarkText = 'CRENO.VN SHOP';

        $cacheDir = (defined('FCPATH') ? FCPATH : dirname(__DIR__, 2) . '/public/') . 'uploads/cache/previews/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        if ($document && !empty($document->file_url)) {
            $docPath = (defined('FCPATH') ? FCPATH : dirname(__DIR__, 2) . '/public/')
                     . ltrim($document->file_url, '/');

            if (file_exists($docPath)) {
                $ext = strtolower(pathinfo($docPath, PATHINFO_EXTENSION));

                if ($ext === 'pdf') {
                    $cachePdfPath = $cacheDir . 'preview_prod_' . $productId . '.pdf';

                    if (!file_exists($cachePdfPath) || filemtime($cachePdfPath) < filemtime($docPath)) {
                        $ok = WatermarkService::applyPdfWatermark(
                            $docPath,
                            $cachePdfPath,
                            $watermarkText,
                            ['maxPages' => 2, 'fontSize' => 28, 'angle' => 45.0]
                        );

                        if (!$ok || !file_exists($cachePdfPath)) {
                            echo json_encode([
                                'success'        => true,
                                'mode'           => 'canvas_fallback',
                                'pdf_url'        => URLROOT . $document->file_url,
                                'watermark_text' => $watermarkText
                            ]);
                            exit();
                        }
                    }

                    echo json_encode([
                        'success'        => true,
                        'mode'           => 'server_pdf',
                        'preview_url'    => URLROOT . '/uploads/cache/previews/preview_prod_' . $productId . '.pdf',
                        'watermark_text' => $watermarkText
                    ]);
                    exit();
                }

                $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($ext, $imageExts, true)) {
                    $cacheImg = $cacheDir . 'preview_prod_' . $productId . '.' . $ext;
                    if (!file_exists($cacheImg) || filemtime($cacheImg) < filemtime($docPath)) {
                        WatermarkService::applyImageWatermark($docPath, $cacheImg, $watermarkText, [
                            'type' => 'diagonal_repeat', 'opacity' => 28
                        ]);
                    }
                    echo json_encode([
                        'success'        => true,
                        'mode'           => 'server_pdf',
                        'preview_url'    => URLROOT . '/uploads/cache/previews/preview_prod_' . $productId . '.' . $ext,
                        'watermark_text' => $watermarkText
                    ]);
                    exit();
                }
            }
        }

        if (!empty($product->preview_url)) {
            $imgPath = (defined('FCPATH') ? FCPATH : dirname(__DIR__, 2) . '/public/')
                     . ltrim($product->preview_url, '/');
            if (file_exists($imgPath)) {
                $ext = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
                $cacheImg = $cacheDir . 'preview_prod_' . $productId . '_img.' . $ext;
                if (!file_exists($cacheImg) || filemtime($cacheImg) < filemtime($imgPath)) {
                    WatermarkService::applyImageWatermark($imgPath, $cacheImg, $watermarkText, [
                        'type' => 'diagonal_repeat', 'opacity' => 28
                    ]);
                }
                echo json_encode([
                    'success'        => true,
                    'mode'           => 'server_pdf',
                    'preview_url'    => URLROOT . '/uploads/cache/previews/preview_prod_' . $productId . '_img.' . $ext,
                    'watermark_text' => $watermarkText
                ]);
                exit();
            }
        }

        $samplePdf = $cacheDir . 'preview_prod_' . $productId . '_sample.pdf';
        if (!file_exists($samplePdf)) {
            WatermarkService::generateSamplePreviewPdf($product, $samplePdf, 2);
        }
        if (file_exists($samplePdf)) {
            echo json_encode([
                'success'        => true,
                'mode'           => 'server_pdf',
                'preview_url'    => URLROOT . '/uploads/cache/previews/preview_prod_' . $productId . '_sample.pdf',
                'watermark_text' => $watermarkText
            ]);
            exit();
        }

        $gd = WatermarkService::generateGdPreviewPages(
            $cacheDir,
            'gd_prod_' . $productId,
            (string) ($product->title ?? 'Tài liệu'),
            $watermarkText,
            2
        );
        if (!empty($gd['p1'])) {
            $urls = [];
            foreach (['p1', 'p2', 'locked'] as $key) {
                if (!empty($gd[$key])) {
                    $urls[] = URLROOT . '/uploads/cache/previews/gd_prod_' . $productId . '_' . $key . '.png';
                }
            }
            echo json_encode([
                'success'        => true,
                'mode'           => 'canvas_fallback',
                'preview_images' => $urls,
                'watermark_text' => $watermarkText
            ]);
            exit();
        }

        echo json_encode(['success' => false, 'message' => 'Chưa thể tạo bản xem trước cho sản phẩm này.']);
        exit();
    }

    /**
     * Preview tài liệu/ảnh đã được nhúng Watermark dành cho Buyer hoặc bất kỳ ai xem sản phẩm
     * Giới hạn trang, chống sao chép và chặn lưu file gốc
     */
    public function preview(?int $productId = null): void
    {
        if (!$productId) {
            http_response_code(404);
            die('Không tìm thấy tài liệu xem trước.');
        }

        $product = $this->productModel->getProductDetail($productId);
        if (!$product) {
            http_response_code(404);
            die('Tài liệu không tồn tại.');
        }

        $document = $this->productModel->getDocumentByProductId($productId);

        // Lấy hoặc sinh file preview (PDF/ảnh) đã nhúng Watermark
        $previewData = WatermarkService::getOrGeneratePreview($productId, $product, $document, 2);

        if (!$previewData || !file_exists($previewData['path'])) {
            http_response_code(404);
            die('Chưa tạo được bản xem trước cho tài liệu này.');
        }

        $filePath = $previewData['path'];
        $mimeType = $previewData['mime'];
        $filename = 'creono_preview_' . $productId . '.' . ($previewData['type'] === 'pdf' ? 'pdf' : pathinfo($filePath, PATHINFO_EXTENSION));

        // Thiết lập header bảo mật: Xem trực tiếp (inline), cấm cache, chặn copy/download tự do
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('X-Content-Type-Options: nosniff');

        readfile($filePath);
        exit();
    }

    /**
     * Seller xem trước Watermark trực tiếp khi đang Upload file tài liệu PDF trước khi lưu.
     * Chỉ hoạt động với file PDF (document_file). Ảnh đại diện không được đóng dấu xem trước tại đây.
     * Trả về JSON chứa preview_url tạm thời để hiển thị trực tiếp trong Modal.
     */
    public function previewUpload(): void
    {
        RoleMiddleware::check([2]);

        header('Content-Type: application/json; charset=utf-8');

        $userId = (int) $_SESSION['user_id'];
        $storeId = $this->getStoreIdByUserId($userId);
        $store = $storeId ? $this->storeModel->findById($storeId) : null;
        $storeName = $store ? $store->name : 'Creono';

        $cacheDir = (defined('FCPATH') ? FCPATH : dirname(__DIR__, 2) . '/public/') . 'uploads/cache/seller_previews/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // Chỉ xử lý file tài liệu PDF (document_file) - KHÔNG xử lý ảnh đại diện tại đây
        if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng chọn file tài liệu PDF trước khi bấm Xem trước Watermark.',
                'hint' => 'Tính năng xem trước chỉ hỗ trợ định dạng PDF. Để xem trước ảnh đại diện, hãy chọn file PDF tài liệu.'
            ]);
            exit();
        }

        $file = $_FILES['document_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Chỉ chấp nhận file PDF
        if ($ext !== 'pdf') {
            echo json_encode([
                'success' => false,
                'message' => 'File tài liệu định dạng "' . strtoupper($ext) . '" không hỗ trợ xem trước trực quan.',
                'hint' => 'Tính năng xem trước Watermark chỉ hoạt động với file PDF. File ZIP, RAR sẽ không hiển thị được nội dung xem trước.'
            ]);
            exit();
        }

        // Xác minh MIME type thực tế của file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($realMime !== 'application/pdf') {
            echo json_encode([
                'success' => false,
                'message' => 'File tải lên không phải là PDF hợp lệ (MIME: ' . $realMime . ').'
            ]);
            exit();
        }

        // Tạo file xem trước: Đóng dấu Watermark, giới hạn 2 trang + trang khóa nội dung
        $tempId = 'seller_' . $userId . '_' . time();
        $rawPdf = $cacheDir . 'raw_' . $tempId . '.pdf';
        @copy($file['tmp_name'], $rawPdf);

        $watermarkText = 'CRENO.VN SHOP';

        // Tạo ảnh GD fallback (2 trang đầu + trang khóa) phòng trường hợp PDF bị nén hoặc FPDI thiếu
        $gdImages = WatermarkService::generateGdPreviewPages(
            $cacheDir,
            'gd_' . $tempId,
            'Bản xem trước tài liệu',
            $watermarkText,
            2
        );

        $gdImageUrls = [
            URLROOT . '/uploads/cache/seller_previews/gd_' . $tempId . '_p1.png',
            URLROOT . '/uploads/cache/seller_previews/gd_' . $tempId . '_p2.png',
            URLROOT . '/uploads/cache/seller_previews/gd_' . $tempId . '_locked.png'
        ];

        $destPdf = $cacheDir . $tempId . '.pdf';
        $success = WatermarkService::applyPdfWatermark(
            $file['tmp_name'],
            $destPdf,
            $watermarkText,
            [
                'subText' => '',
                'maxPages' => 2,   // Chỉ hiển thị 2 trang đầu
                'fontSize' => 28,
                'angle' => 45.0,
                'footerText' => 'Tai lieu duoc bao ve ban quyen tai Creono.vn - Chi dung cho muc dich xem truoc.',
            ]
        );

        if ($success && file_exists($destPdf)) {
            echo json_encode([
                'success' => true,
                'mode' => 'server_pdf',
                'type' => 'pdf',
                'preview_url' => URLROOT . '/uploads/cache/seller_previews/' . $tempId . '.pdf',
                'watermark_text' => $watermarkText,
                'message' => 'Đã tạo bản xem trước Watermark thành công!'
            ]);
        } else {
            // Chế độ fallback Canvas / GD khi thiếu FPDI hoặc PDF mã hóa cao
            echo json_encode([
                'success' => true,
                'mode' => 'canvas_fallback',
                'type' => 'canvas',
                'pdf_url' => URLROOT . '/uploads/cache/seller_previews/raw_' . $tempId . '.pdf',
                'preview_images' => $gdImageUrls,
                'watermark_text' => $watermarkText,
                'message' => 'Đã tạo bản xem trước 2 trang đầu thành công!'
            ]);
        }
        exit();
    }
}
<?php

declare(strict_types=1);

class Product extends BaseModel
{
    protected string $table = 'products';

    /**
     * Lấy tất cả sản phẩm kèm tên cửa hàng
     * @return array<object>
     */
    public function getProducts(): array
    {
        $this->db->query("
            SELECT products.*, stores.name as store_name 
            FROM {$this->table} 
            JOIN stores ON products.store_id = stores.id 
            WHERE products.status = 2 
            ORDER BY products.created_at DESC
        ");
        return $this->db->resultSet();
    }

    public function getTotalProducts(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 2");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lấy số lượng sản phẩm của seller
     */
    public function getSellerProductsCount(int $user_id): int
    {
        $this->db->query("
            SELECT COUNT(*) as total
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = :user_id AND p.deleted_at IS NULL
        ");
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lấy điểm đánh giá trung bình của seller
     */
    public function getSellerAvgRating(int $user_id): float
    {
        $this->db->query("
            SELECT COALESCE(AVG(p.rating), 0) as avg_rating
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = :user_id AND p.rating > 0
        ");
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result ? (float)$result->avg_rating : 0.0;
    }

    /**
     * Lấy tổng số đánh giá của seller
     */
    public function getSellerTotalReviews(int $user_id): int
    {
        $this->db->query("
            SELECT COALESCE(SUM(p.review_count), 0) as total
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = :user_id
        ");
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lấy sản phẩm bán chạy nhất của seller
     * @return array<object>
     */
    public function getSellerTopProducts(int $user_id, int $limit = 5): array
    {
        $this->db->query("
            SELECT 
                p.id,
                p.title,
                p.price,
                p.download_count as sales_count
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = ? AND p.deleted_at IS NULL
            ORDER BY p.download_count DESC
            LIMIT ?
        ");
        $this->db->bind(1, $user_id);
        $this->db->bind(2, $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Lấy tất cả sản phẩm đang chờ duyệt (status = 1) kèm thông tin chi tiết
     * @return array<object>
     */
    public function getPendingApprovals(): array
    {
        $this->db->query("
            SELECT p.*, 
                   s.name as store_name, 
                   c.name as category_name,
                   d.file_url, d.ai_score,
                   al.name as ai_label_name
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN documents d ON p.id = d.product_id
            LEFT JOIN ai_labels al ON d.ai_label_id = al.id
            WHERE p.status = 1 AND p.deleted_at IS NULL
            ORDER BY p.created_at ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Đếm số sản phẩm đang chờ duyệt (status = 1)
     */
    public function getPendingCount(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 1 AND deleted_at IS NULL");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Cập nhật trạng thái sản phẩm (1: Pending, 2: Approved, 3: Rejected)
     */
    public function updateStatus(int $id, int $status): bool
    {
        $this->db->query("UPDATE {$this->table} SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Lấy chi tiết sản phẩm đầy đủ thông tin (kèm store, seller, category)
     */
    public function getProductDetail(int $id): ?object
    {
        $this->db->query("
            SELECT p.*, 
                   s.name as store_name, 
                   s.user_id as seller_id,
                   u.name as seller_name,
                   c.name as category_name,
                   c.slug as category_slug
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id AND p.deleted_at IS NULL
        ");
        $this->db->bind(':id', $id);
        return $this->db->single() ?: null;
    }

    public function getProductWithSeller(int $productId): ?object
    {
        $this->db->query("
            SELECT p.*, s.user_id as seller_id 
            FROM products p 
            JOIN stores s ON p.store_id = s.id 
            WHERE p.id = :id
        ");
        $this->db->bind(':id', $productId);
        return $this->db->single() ?: null;
    }

    /**
     * Lấy link file tải của sản phẩm từ bảng documents
     */
    public function getDocumentByProductId(int $productId): ?object
    {
        $this->db->query("SELECT file_url FROM documents WHERE product_id = :id");
        $this->db->bind(':id', $productId);
        return $this->db->single() ?: null;
    }

    /**
     * Ghi log vào bảng downloads
     */
    public function logDownload(int $userId, int $productId, string $ip): bool
    {
        $this->db->query("
            INSERT INTO downloads (user_id, product_id, ip_address) 
            VALUES (:user_id, :product_id, :ip)
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':ip', $ip);
        return $this->db->execute();
    }

    /**
     * Lấy danh sách sản phẩm với bộ lọc nâng cao
     * @return array<object>
     */
    public function getProductsFiltered(
        string $category,
        string $keyword,
        float $minPrice,
        float $maxPrice,
        string $sort
    ): array {
        $sql = "SELECT p.*, s.name as store_name 
                FROM {$this->table} p
                JOIN stores s ON p.store_id = s.id
                WHERE p.status = 2 AND p.deleted_at IS NULL";
        $bind = [];

        if ($category !== '') {
            $sql .= " AND p.category_id = (SELECT id FROM categories WHERE slug = :category)";
            $bind[':category'] = $category;
        }
        if ($keyword !== '') {
            $sql .= " AND (p.title LIKE :keyword OR p.description LIKE :keyword)";
            $bind[':keyword'] = '%' . $keyword . '%';
        }
        if ($minPrice > 0) {
            $sql .= " AND p.price >= :minPrice";
            $bind[':minPrice'] = $minPrice;
        }
        if ($maxPrice > 0) {
            $sql .= " AND p.price <= :maxPrice";
            $bind[':maxPrice'] = $maxPrice;
        }

        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY p.rating DESC, p.review_count DESC";
                break;
            default:
                $sql .= " ORDER BY p.created_at DESC";
        }

        $this->db->query($sql);
        foreach ($bind as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }
}
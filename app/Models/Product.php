<?php
class Product extends BaseModel {
    protected string $table = 'products';

    // Lấy tất cả sản phẩm kèm tên cửa hàng
    public function getProducts() {
        $this->db->query("SELECT products.*, stores.name as store_name 
                          FROM {$this->table} 
                          JOIN stores ON products.store_id = stores.id 
                          WHERE products.status = 2 
                          ORDER BY products.created_at DESC");
        
        return $this->db->resultSet();
    }
    
    public function getTotalProducts(): int {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 2");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }
     /**
     * Lấy số lượng sản phẩm của seller
     */
    public function getSellerProductsCount(int $user_id): int {
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
    public function getSellerAvgRating(int $user_id): float {
        $this->db->query("
            SELECT COALESCE(AVG(p.rating), 0) as avg_rating
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = :user_id AND p.rating > 0
        ");
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result ? (float)$result->avg_rating : 0;
    }

    /**
     * Lấy tổng số đánh giá của seller
     */
    public function getSellerTotalReviews(int $user_id): int {
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
     */
    public function getSellerTopProducts(int $user_id, int $limit = 5): array {
        $this->db->query("
            SELECT 
                p.id,
                p.title,
                p.price,
                p.download_count as sales_count
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = :user_id AND p.deleted_at IS NULL
            ORDER BY p.download_count DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * Lấy tất cả sản phẩm đang chờ duyệt (status = 1) kèm thông tin chi tiết
     */
    public function getPendingApprovals(): array {
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
    public function getPendingCount(): int {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 1 AND deleted_at IS NULL");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Cập nhật trạng thái sản phẩm (1: Pending, 2: Approved, 3: Rejected)
     */
    public function updateStatus(int $id, int $status): bool {
        $this->db->query("UPDATE {$this->table} SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Lấy chi tiết sản phẩm đầy đủ thông tin (kèm store, seller, category)
     */
    public function getProductDetail(int $id): ?object {
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
        $result = $this->db->single();
        return $result ?: null;
    }
}
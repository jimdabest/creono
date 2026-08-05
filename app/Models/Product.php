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
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
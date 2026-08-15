<?php
class Favorite extends BaseModel {
    protected string $table = 'favorites';

    /**
     * Thêm sản phẩm vào yêu thích
     */
    public function addFavorite(int $userId, int $productId): bool {
        $this->db->query("
            INSERT IGNORE INTO {$this->table} (user_id, product_id)
            VALUES (:user_id, :product_id)
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);
        return $this->db->execute();
    }

    /**
     * Xoá sản phẩm khỏi yêu thích
     */
    public function removeFavorite(int $userId, int $productId): bool {
        $this->db->query("
            DELETE FROM {$this->table}
            WHERE user_id = :user_id AND product_id = :product_id
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);
        return $this->db->execute();
    }

    /**
     * Kiểm tra sản phẩm đã được yêu thích chưa
     */
    public function isFavorited(int $userId, int $productId): bool {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE user_id = :user_id AND product_id = :product_id
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);
        $result = $this->db->single();
        return $result && (int)$result->total > 0;
    }

    /**
     * Lấy danh sách sản phẩm yêu thích của user (kèm thông tin SP)
     */
    public function getFavoritesByUserId(int $userId): array {
        $this->db->query("
            SELECT f.created_at as favorited_at,
                   p.id, p.title, p.price, p.rating, p.review_count,
                   p.preview_url, p.description,
                   s.name as store_name
            FROM {$this->table} f
            JOIN products p ON f.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            WHERE f.user_id = :user_id 
              AND p.status = 2 
              AND p.deleted_at IS NULL
            ORDER BY f.created_at DESC
        ");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Đếm số sản phẩm yêu thích của user
     */
    public function countByUserId(int $userId): int {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE user_id = :user_id
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lấy danh sách product_id đã yêu thích (dùng để highlight trên listing)
     */
    public function getFavoriteProductIds(int $userId): array {
        $this->db->query("
            SELECT product_id 
            FROM {$this->table} 
            WHERE user_id = :user_id
        ");
        $this->db->bind(':user_id', $userId);
        $results = $this->db->resultSet();
        return array_map(function($row) { return (int)$row->product_id; }, $results);
    }
}

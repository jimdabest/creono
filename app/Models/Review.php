<?php
class Review extends BaseModel {
    protected string $table = 'reviews';

    /**
     * Lấy tất cả đánh giá của một sản phẩm (kèm thông tin user, avatar)
     * Chỉ lấy review gốc (parent_id IS NULL), sắp xếp mới nhất trước
     */
    public function getReviewsByProductId(int $productId): array {
        $this->db->query("
            SELECT r.*, 
                   u.name as user_name,
                   up.avatar_url as user_avatar
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN user_profiles up ON r.user_id = up.user_id
            WHERE r.product_id = :product_id 
              AND r.parent_id IS NULL
              AND r.status = 1
            ORDER BY r.created_at DESC
        ");
        $this->db->bind(':product_id', $productId);
        return $this->db->resultSet();
    }

    /**
     * Lấy các reply (bình luận con) của một review
     */
    public function getRepliesByReviewId(int $reviewId): array {
        $this->db->query("
            SELECT r.*, 
                   u.name as user_name,
                   up.avatar_url as user_avatar
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN user_profiles up ON r.user_id = up.user_id
            WHERE r.parent_id = :parent_id
              AND r.status = 1
            ORDER BY r.created_at ASC
        ");
        $this->db->bind(':parent_id', $reviewId);
        return $this->db->resultSet();
    }

    /**
     * Tạo đánh giá mới (rating + comment)
     */
    public function createReview(array $data): bool {
        $this->db->query("
            INSERT INTO {$this->table} (product_id, user_id, parent_id, rating, comment)
            VALUES (:product_id, :user_id, :parent_id, :rating, :comment)
        ");
        $this->db->bind(':product_id', $data['product_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':parent_id', $data['parent_id'] ?? null);
        $this->db->bind(':rating', $data['rating'] ?? null);
        $this->db->bind(':comment', $data['comment']);
        return $this->db->execute();
    }

    /**
     * Kiểm tra user đã đánh giá sản phẩm này chưa
     * (Mỗi user chỉ được đánh giá 1 lần cho mỗi sản phẩm - review gốc)
     */
    public function hasUserReviewed(int $productId, int $userId): bool {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE product_id = :product_id 
              AND user_id = :user_id 
              AND parent_id IS NULL
              AND rating IS NOT NULL
        ");
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result && (int)$result->total > 0;
    }

    /**
     * Lấy thống kê rating (phân bổ số sao) của sản phẩm
     */
    public function getRatingStats(int $productId): array {
        $stats = [
            '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0,
            'total' => 0, 'average' => 0
        ];

        $this->db->query("
            SELECT rating, COUNT(*) as count
            FROM {$this->table}
            WHERE product_id = :product_id 
              AND rating IS NOT NULL
              AND parent_id IS NULL
              AND status = 1
            GROUP BY rating
        ");
        $this->db->bind(':product_id', $productId);
        $results = $this->db->resultSet();

        $totalRatings = 0;
        $sumRatings = 0;

        foreach ($results as $row) {
            $stats[(string)$row->rating] = (int)$row->count;
            $totalRatings += (int)$row->count;
            $sumRatings += $row->rating * $row->count;
        }

        $stats['total'] = $totalRatings;
        $stats['average'] = $totalRatings > 0 ? round($sumRatings / $totalRatings, 1) : 0;

        return $stats;
    }

    /**
     * Đếm tổng số đánh giá (review gốc) của sản phẩm
     */
    public function getReviewCount(int $productId): int {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE product_id = :product_id 
              AND parent_id IS NULL 
              AND status = 1
        ");
        $this->db->bind(':product_id', $productId);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Đếm số reply của một review
     */
    public function getReplyCount(int $reviewId): int {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM {$this->table} 
            WHERE parent_id = :parent_id 
              AND status = 1
        ");
        $this->db->bind(':parent_id', $reviewId);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }
}

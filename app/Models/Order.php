<?php
class Order extends BaseModel {
    protected string $table = 'orders';

    /**
     * Lấy doanh thu của seller
     */
    public function getSellerRevenue(int $user_id): float {
        $this->db->query("
            SELECT COALESCE(SUM(o.seller_amount), 0) as total
            FROM {$this->table} o
            JOIN products p ON o.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = :user_id AND o.status = 2
        ");
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result ? (float)$result->total : 0;
    }

    /**
     * Lấy số đơn hàng đang chờ của seller
     */
    public function getSellerPendingOrdersCount(int $user_id): int {
        $this->db->query("
            SELECT COUNT(*) as total
            FROM {$this->table} o
            JOIN products p ON o.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = :user_id AND o.status = 1
        ");
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lấy danh sách đơn hàng gần đây của seller
     */
    public function getSellerRecentOrders(int $user_id, int $limit = 5): array {
        $this->db->query("
            SELECT 
                o.id,
                o.total_amount as amount,
                o.status,
                o.created_at,
                p.title as product_title,
                CASE 
                    WHEN o.status = 1 THEN 'Chờ xử lý'
                    WHEN o.status = 2 THEN 'Hoàn thành'
                    WHEN o.status = 3 THEN 'Đã hủy'
                    ELSE 'Không xác định'
                END as status_text
            FROM {$this->table} o
            JOIN products p ON o.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            WHERE s.user_id = :user_id
            ORDER BY o.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
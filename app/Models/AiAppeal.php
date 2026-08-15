<?php
class AiAppeal extends BaseModel {
    protected string $table = 'ai_appeals';

    /**
     * Lấy tất cả khiếu nại nhãn AI kèm thông tin sản phẩm và Seller
     */
    public function getAllWithDetails(): array {
        $this->db->query("
            SELECT a.*, 
                   p.title as product_title, p.price as product_price,
                   u.name as seller_name, u.email as seller_email,
                   adm.name as processor_name
            FROM {$this->table} a
            JOIN products p ON a.product_id = p.id
            JOIN users u ON a.seller_id = u.id
            LEFT JOIN users adm ON a.processed_by = adm.id
            ORDER BY a.created_at DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Đếm số lượng khiếu nại AI đang chờ xử lý (status = 1)
     */
    public function getPendingCount(): int {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 1");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Cập nhật trạng thái xử lý khiếu nại AI
     */
    public function updateAppealStatus(int $id, int $status, int $adminId): bool {
        $this->db->query("
            UPDATE {$this->table} 
            SET status = :status, processed_by = :admin_id, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':admin_id', $adminId);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}

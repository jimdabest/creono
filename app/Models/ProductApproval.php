<?php
class ProductApproval extends BaseModel {
    protected string $table = 'product_approvals';

    /**
     * Ghi lịch sử duyệt / từ chối sản phẩm vào bảng product_approvals
     */
    public function logApproval(int $productId, int $censorId, string $action, ?string $note = null): bool {
        $data = [
            'product_id' => $productId,
            'censor_id' => $censorId,
            'action' => strtoupper($action),
            'note' => $note
        ];
        return $this->create($data);
    }

    /**
     * Lấy danh sách lịch sử duyệt sản phẩm gần đây
     */
    public function getRecentApprovals(int $limit = 10): array {
        $this->db->query("
            SELECT pa.*, p.title as product_title, u.name as censor_name, u.email as censor_email
            FROM {$this->table} pa
            JOIN products p ON pa.product_id = p.id
            JOIN users u ON pa.censor_id = u.id
            ORDER BY pa.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}

<?php
class Report extends BaseModel {
    protected string $table = 'reports';

    /**
     * Lấy danh sách tất cả các báo cáo vi phạm kèm thông tin người báo cáo và đối tượng bị báo cáo
     */
    public function getAllWithDetails(): array {
        $this->db->query("
            SELECT r.*, 
                   u.name as reporter_name, 
                   u.email as reporter_email,
                   adm.name as resolver_name,
                   CASE 
                       WHEN r.target_type = 'PRODUCT' THEN p.title
                       WHEN r.target_type = 'STORE' THEN s.name
                       WHEN r.target_type = 'USER' THEN target_u.name
                       WHEN r.target_type = 'REVIEW' THEN rev.comment
                       ELSE CONCAT('#', r.target_id)
                   END as target_title
            FROM {$this->table} r
            JOIN users u ON r.reporter_id = u.id
            LEFT JOIN users adm ON r.resolved_by = adm.id
            LEFT JOIN products p ON r.target_type = 'PRODUCT' AND r.target_id = p.id
            LEFT JOIN stores s ON r.target_type = 'STORE' AND r.target_id = s.id
            LEFT JOIN users target_u ON r.target_type = 'USER' AND r.target_id = target_u.id
            LEFT JOIN reviews rev ON r.target_type = 'REVIEW' AND r.target_id = rev.id
            ORDER BY r.created_at DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Đếm số lượng báo cáo vi phạm đang chờ xử lý (status = 1)
     */
    public function getPendingCount(): int {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 1");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Cập nhật trạng thái xử lý báo cáo
     */
    public function updateReportStatus(int $id, int $status, int $adminId): bool {
        $this->db->query("
            UPDATE {$this->table} 
            SET status = :status, resolved_by = :admin_id, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':admin_id', $adminId);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}

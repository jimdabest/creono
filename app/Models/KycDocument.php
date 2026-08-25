<?php
declare(strict_types=1);

class KycDocument extends BaseModel
{
    protected string $table = 'kyc_documents';

    /**
     * Lấy danh sách KYC đang chờ duyệt
     */
    public function getPendingKycs(): array
    {
        $this->db->query("SELECT k.*, u.name, u.email FROM {$this->table} k JOIN users u ON k.user_id = u.id WHERE k.status = 1 ORDER BY k.created_at ASC");
        return $this->db->resultSet();
    }

    /**
     * Admin duyệt KYC
     */
    public function approveKyc(int $id): bool
    {
        $this->db->query("UPDATE {$this->table} SET status = 2, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Admin từ chối KYC
     */
    public function rejectKyc(int $id, string $note): bool
    {
        $this->db->query("UPDATE {$this->table} SET status = 3, rejection_reason = :note, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':note', $note);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * User gửi yêu cầu KYC
     */
    public function submitKyc(int $userId, string $frontUrl): bool
    {
        $this->db->query("INSERT INTO {$this->table} (user_id, document_type, front_image_url, status) VALUES (:user_id, 'ID_CARD', :front_url, 1)");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':front_url', $frontUrl);
        return $this->db->execute();
    }
}
<?php

declare(strict_types=1);
class Store extends BaseModel
{
    protected string $table = 'stores';
    public function getStoreIdByUserId(int $userId): ?int
    {
        $this->db->query("SELECT id FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? (int)$result->id : null;
    }
    public function getStoreByUserId(int $userId): ?object
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? $result : null;
    }
    public function getStoreBySlug(string $slug): ?object
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $this->db->bind(':slug', $slug);

        $result = $this->db->single();

        // Nếu không có kết quả (false), trả về null để đúng với kiểu ?object
        return $result ? $result : null;
    }

    public function createStore(array $data): bool
    {
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        $slug = $data['slug'] ?? '';
        $counter = 1;
        while ($this->slugExists($slug)) {
            $slug = $data['slug'] . '-' . $counter++;
        }
        $data['slug'] = $slug;

        return $this->create($data);
    }

    public function updateStore(int $id, array $data): bool
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
            $counter = 1;
            $newSlug = $data['slug'];
            while ($this->slugExists($newSlug, $id)) {
                $newSlug = $data['slug'] . '-' . $counter++;
            }
            $data['slug'] = $newSlug;
        }
        return $this->update($id, $data);
    }

    private function generateSlug(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $text);
        $text = strtolower($text);
        $text = preg_replace('/[\s\-]+/', '-', $text);
        return trim($text, '-');
    }

    private function slugExists(string $slug, int $exceptId = 0): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE slug = :slug";
        if ($exceptId > 0) {
            $sql .= " AND id != :id";
        }
        $this->db->query($sql);
        $this->db->bind(':slug', $slug);
        if ($exceptId > 0) {
            $this->db->bind(':id', $exceptId);
        }
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Lấy danh sách cửa hàng đang chờ phê duyệt (status = 0)
     */
    public function getPendingStores(): array
    {
        $sql = "SELECT s.*, 
                       u.name AS applicant_name, 
                       u.email AS applicant_email,
                       u.role AS user_role,
                       COALESCE(s.created_at, u.created_at) AS submission_date
                FROM {$this->table} s
                INNER JOIN users u ON s.user_id = u.id
                WHERE s.status = 0
                ORDER BY s.id DESC";

        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Đếm số lượng cửa hàng đang chờ phê duyệt (status = 0)
     */
    public function getPendingCount(): int
    {
        $this->db->query("SELECT COUNT(*) AS total FROM {$this->table} WHERE status = 0");
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    /**
     * Phê duyệt hồ sơ cửa hàng:
     * - Cập nhật status cửa hàng = 1 (Đã duyệt / Active)
     * - Tự động cập nhật role_id của User từ Buyer (1) thành Seller (2)
     */
    public function approveStore(int $storeId): bool
    {
        $store = $this->findById($storeId);
        if (!$store) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1. Cập nhật trạng thái cửa hàng: status = 1 (Đã duyệt / Active)
            $this->db->query("UPDATE {$this->table} SET status = 1, rejection_reason = NULL WHERE id = :id");
            $this->db->bind(':id', $storeId);
            $this->db->execute();

            // 2. Tự động cập nhật role của User đăng ký thành Seller (role = 2)
            $this->db->query("UPDATE users SET role = 2 WHERE id = :user_id");
            $this->db->bind(':user_id', (int)$store->user_id);
            $this->db->execute();

            $this->db->commit();
            unset($this->cache[$this->table . '_' . $storeId]);
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Từ chối hồ sơ cửa hàng:
     * - Cập nhật status cửa hàng = 2 (Từ chối)
     * - Lưu lý do từ chối
     */
    public function rejectStore(int $storeId, string $reason): bool
    {
        $this->db->query("UPDATE {$this->table} SET status = 2, rejection_reason = :reason WHERE id = :id");
        $this->db->bind(':reason', $reason);
        $this->db->bind(':id', $storeId);
        $result = $this->db->execute();

        if ($result) {
            unset($this->cache[$this->table . '_' . $storeId]);
        }
        return $result;
    }

    /**
     * Lấy thông tin chi tiết cửa hàng kèm người đăng ký
     */
    public function getStoreWithApplicant(int $storeId): ?object
    {
        $sql = "SELECT s.*, 
                       u.name AS applicant_name, 
                       u.email AS applicant_email,
                       u.role AS user_role
                FROM {$this->table} s
                INNER JOIN users u ON s.user_id = u.id
                WHERE s.id = :id
                LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':id', $storeId);
        $row = $this->db->single();
        return $row ?: null;
    }
}

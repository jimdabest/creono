<?php

/**
 * UserModel - Model quản lý người dùng phía Admin (UC40)
 * Kế thừa BaseModel, bổ sung các phương thức CRUD + Lock/Unlock
 * cho chức năng Quản lý Người dùng của Admin.
 */
class UserModel extends BaseModel
{
    protected string $table = 'users';

    // =========================================================================
    // Lấy danh sách người dùng kèm thông tin profile (cho Admin)
    // =========================================================================

    /**
     * Lấy tất cả user kèm profile, sắp xếp mới nhất lên đầu
     * Bỏ qua Admin (role = 3) để tránh Admin tự quản lý chính mình
     */
    public function getAllUsersWithProfile(): array
    {
        $this->db->query("
            SELECT u.id, u.name, u.email, u.role, u.is_locked, u.created_at,
                   p.full_name, p.avatar_url
            FROM {$this->table} u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.role != 3
            ORDER BY u.created_at DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Lấy user theo ID kèm profile (chi tiết cho Admin xem/sửa)
     */
    public function getUserDetailById(int $id): ?object
    {
        $this->db->query("
            SELECT u.id, u.name, u.email, u.role, u.is_locked, u.created_at,
                   p.full_name, p.avatar_url, p.bio
            FROM {$this->table} u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // =========================================================================
    // Lock / Unlock tài khoản người dùng
    // =========================================================================

    /**
     * Khóa tài khoản: set is_locked = 1
     */
    public function lockUser(int $id): bool
    {
        $this->db->query("UPDATE {$this->table} SET is_locked = 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->execute();
        if ($result) {
            unset($this->cache[$this->table . '_' . $id]);
        }
        return $result;
    }

    /**
     * Mở khóa tài khoản: set is_locked = 0
     */
    public function unlockUser(int $id): bool
    {
        $this->db->query("UPDATE {$this->table} SET is_locked = 0 WHERE id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->execute();
        if ($result) {
            unset($this->cache[$this->table . '_' . $id]);
        }
        return $result;
    }

    /**
     * Toggle trạng thái khóa: Nếu đang khóa thì mở, ngược lại thì khóa
     * Trả về trạng thái mới sau khi toggle
     */
    public function toggleLock(int $id): ?bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return null;
        }

        $newStatus = $user->is_locked ? 0 : 1;
        $this->db->query("UPDATE {$this->table} SET is_locked = :status WHERE id = :id");
        $this->db->bind(':status', $newStatus);
        $this->db->bind(':id', $id);

        if ($this->db->execute()) {
            unset($this->cache[$this->table . '_' . $id]);
            return (bool) $newStatus; // true = đã khóa, false = đã mở
        }

        return null;
    }

    // =========================================================================
    // Kiểm tra email đã tồn tại (cho Admin tạo user mới)
    // =========================================================================

    /**
     * Kiểm tra email trùng, loại trừ user hiện tại (dùng khi cập nhật)
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $this->db->query("SELECT id FROM {$this->table} WHERE email = :email AND id != :id");
            $this->db->bind(':id', $excludeId);
        } else {
            $this->db->query("SELECT id FROM {$this->table} WHERE email = :email");
        }
        $this->db->bind(':email', $email);
        $this->db->single();
        return $this->db->rowCount() > 0;
    }

    // =========================================================================
    // Thống kê nhanh
    // =========================================================================

    /**
     * Đếm số user đang bị khóa
     */
    public function getLockedCount(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE is_locked = 1 AND role != 3");
        $result = $this->db->single();
        return $result ? (int) $result->total : 0;
    }

    /**
     * Đếm tổng user (không tính Admin)
     */
    public function getTotalNonAdminUsers(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE role != 3");
        $result = $this->db->single();
        return $result ? (int) $result->total : 0;
    }

    // =========================================================================
    // Xóa cứng user (Admin force delete)
    // =========================================================================

    /**
     * Xóa vĩnh viễn user - Chỉ Admin được phép
     * Cascade sẽ xóa profile, wallet liên quan (theo FK ON DELETE CASCADE)
     */
    public function forceDelete(int $id): bool
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id AND role != 3");
        $this->db->bind(':id', $id);
        $result = $this->db->execute();
        if ($result) {
            unset($this->cache[$this->table . '_' . $id]);
        }
        return $result;
    }
}

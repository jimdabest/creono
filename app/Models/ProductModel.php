<?php

/**
 * ProductModel - Model quản lý sản phẩm phía Admin (UC41)
 * Kế thừa BaseModel, bổ sung truy vấn cho Admin CRUD + Kiểm duyệt
 * Tách riêng khỏi Product.php (dùng cho Buyer/Seller) để tránh xung đột
 */

declare(strict_types=1);

class ProductModel extends BaseModel
{
    protected string $table = 'products';

    // =========================================================================
    // Danh sách sản phẩm cho Admin (toàn bộ trạng thái)
    // =========================================================================

    /**
     * Lấy tất cả sản phẩm kèm store, seller, category (cho Admin listing)
     * Bao gồm cả Pending, Approved, Rejected - không lọc deleted_at
     */
    public function getAllForAdmin(): array
    {
        $this->db->query("
            SELECT p.id, p.title, p.price, p.status, p.rating, p.review_count,
                   p.download_count, p.preview_url, p.created_at, p.deleted_at,
                   s.name AS store_name,
                   u.name AS seller_name, u.id AS seller_id,
                   c.name AS category_name
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.deleted_at IS NULL
            ORDER BY p.created_at DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Lấy chi tiết 1 sản phẩm cho Admin (kèm AI score, document)
     */
    public function getDetailForAdmin(int $id): ?object
    {
        $this->db->query("
            SELECT p.*,
                   s.name AS store_name, s.id AS store_id,
                   u.name AS seller_name, u.id AS seller_id, u.email AS seller_email,
                   c.name AS category_name, c.id AS category_id_val,
                   d.file_url, d.ai_score,
                   al.name AS ai_label_name
            FROM {$this->table} p
            JOIN stores s ON p.store_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN documents d ON p.id = d.product_id
            LEFT JOIN ai_labels al ON d.ai_label_id = al.id
            WHERE p.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single() ?: null;
    }

    // =========================================================================
    // Kiểm duyệt: Approve / Reject
    // =========================================================================

    /**
     * Duyệt sản phẩm (set status = 2: Approved)
     */
    public function approveProduct(int $id): bool
    {
        $this->db->query("UPDATE {$this->table} SET status = 2 WHERE id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->execute();
        if ($result) {
            unset($this->cache[$this->table . '_' . $id]);
        }
        return $result;
    }

    /**
     * Từ chối sản phẩm (set status = 3: Rejected)
     */
    public function rejectProduct(int $id): bool
    {
        $this->db->query("UPDATE {$this->table} SET status = 3 WHERE id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->execute();
        if ($result) {
            unset($this->cache[$this->table . '_' . $id]);
        }
        return $result;
    }

    /**
     * Đặt lại về trạng thái Pending (status = 1)
     */
    public function resetToPending(int $id): bool
    {
        $this->db->query("UPDATE {$this->table} SET status = 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->execute();
        if ($result) {
            unset($this->cache[$this->table . '_' . $id]);
        }
        return $result;
    }

    /**
     * Cập nhật trạng thái kiểm duyệt linh hoạt
     */
    public function updateStatus(int $id, int $status): bool
    {
        if (!in_array($status, [1, 2, 3])) {
            return false;
        }
        $this->db->query("UPDATE {$this->table} SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        $result = $this->db->execute();
        if ($result) {
            unset($this->cache[$this->table . '_' . $id]);
        }
        return $result;
    }

    // =========================================================================
    // Thống kê nhanh cho Admin
    // =========================================================================

    /**
     * Đếm sản phẩm theo trạng thái
     */
    public function getCountByStatus(int $status): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = :status AND deleted_at IS NULL");
        $this->db->bind(':status', $status);
        $result = $this->db->single();
        return $result ? (int) $result->total : 0;
    }

    /**
     * Tổng số sản phẩm (không tính đã xóa mềm)
     */
    public function getTotalActiveProducts(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL");
        $result = $this->db->single();
        return $result ? (int) $result->total : 0;
    }

    // =========================================================================
    // CRUD mở rộng cho Admin
    // =========================================================================

    /**
     * Admin cập nhật thông tin sản phẩm (title, price, category, description)
     */
    public function adminUpdate(int $id, array $data): bool
    {
        $allowedFields = ['title', 'price', 'category_id', 'description', 'status'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            return false;
        }

        return $this->update($id, $updateData);
    }

    /**
     * Admin xóa mềm sản phẩm (set deleted_at)
     */
    public function softDelete(int $id): bool
    {
        return $this->delete($id); // BaseModel::delete() đã set deleted_at
    }

    /**
     * Admin xóa cứng sản phẩm (vĩnh viễn)
     */
    public function forceDelete(int $id): bool
    {
        return $this->destroy($id); // BaseModel::destroy()
    }

    // =========================================================================
    // Ghi lịch sử kiểm duyệt (delegate sang ProductApproval)
    // =========================================================================

    /**
     * Lấy lịch sử kiểm duyệt của 1 sản phẩm
     */
    public function getApprovalHistory(int $productId): array
    {
        $this->db->query("
            SELECT pa.*, u.name AS censor_name, u.email AS censor_email
            FROM product_approvals pa
            JOIN users u ON pa.censor_id = u.id
            WHERE pa.product_id = :product_id
            ORDER BY pa.created_at DESC
        ");
        $this->db->bind(':product_id', $productId);
        return $this->db->resultSet();
    }

    /**
     * Lấy danh sách categories cho dropdown
     */
    public function getAllCategories(): array
    {
        $this->db->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC");
        return $this->db->resultSet();
    }

    /**
     * Lấy danh sách stores cho dropdown (khi Admin tạo sản phẩm)
     */
    public function getAllStores(): array
    {
        $this->db->query("
            SELECT s.id, s.name, u.name AS owner_name
            FROM stores s
            JOIN users u ON s.user_id = u.id
            WHERE s.status = 1
            ORDER BY s.name ASC
        ");
        return $this->db->resultSet();
    }
}

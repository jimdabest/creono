<?php
class StatModel extends BaseModel {
    protected string $table = 'users';

    /**
     * Lấy tổng số lượng người dùng trong hệ thống
     */
    public function getTotalUsers(): int {
        $this->db->query("SELECT COUNT(*) as total FROM users");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lấy tổng số lượng sản phẩm đã duyệt và chưa bị xóa
     */
    public function getTotalProducts(): int {
        $this->db->query("SELECT COUNT(*) as total FROM products WHERE status = 2 AND deleted_at IS NULL");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lấy tổng số đơn hàng đã hoàn tất thanh toán (status = 2)
     */
    public function getTotalOrders(): int {
        $this->db->query("SELECT COUNT(*) as total FROM orders WHERE status = 2");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lấy tổng doanh thu hệ thống từ các đơn hàng đã hoàn tất thanh toán
     */
    public function getTotalRevenue(): float {
        $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 2");
        $result = $this->db->single();
        return $result ? (float)$result->total : 0;
    }

    /**
     * Lấy danh sách sản phẩm bán chạy nhất (dùng view vw_topproducts)
     */
    public function getTopProducts(int $limit = 5): array {
        $this->db->query("SELECT * FROM vw_topproducts LIMIT :limit");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    /**
     * Lấy tổng quan doanh thu các người bán (dùng view vw_seller_revenue)
     */
    public function getSellerRevenueOverview(int $limit = 5): array {
        $this->db->query("SELECT * FROM vw_seller_revenue ORDER BY total_revenue DESC LIMIT :limit");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}

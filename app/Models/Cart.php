<?php
class Cart extends BaseModel {
    protected string $table = 'carts';

    /**
     * Lấy hoặc tự động tạo giỏ hàng cho user
     */
    public function getOrCreateCart(int $userId): object {
        $this->db->query("SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $cart = $this->db->single();

        if (!$cart) {
            $this->db->query("INSERT INTO {$this->table} (user_id) VALUES (:user_id)");
            $this->db->bind(':user_id', $userId);
            $this->db->execute();

            $cartId = $this->db->lastInsertId();
            return (object)[
                'id' => (int)$cartId,
                'user_id' => $userId
            ];
        }

        return $cart;
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addItem(int $cartId, int $productId): bool {
        // Kiểm tra xem sản phẩm đã có trong giỏ chưa (tài liệu số mỗi SP chỉ cần mua 1 lần)
        if ($this->hasItem($cartId, $productId)) {
            return true;
        }

        $this->db->query("
            INSERT INTO cart_items (cart_id, product_id) 
            VALUES (:cart_id, :product_id)
        ");
        $this->db->bind(':cart_id', $cartId);
        $this->db->bind(':product_id', $productId);
        return $this->db->execute();
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeItem(int $cartId, int $productId): bool {
        $this->db->query("
            DELETE FROM cart_items 
            WHERE cart_id = :cart_id AND product_id = :product_id
        ");
        $this->db->bind(':cart_id', $cartId);
        $this->db->bind(':product_id', $productId);
        return $this->db->execute();
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clearCart(int $cartId): bool {
        $this->db->query("DELETE FROM cart_items WHERE cart_id = :cart_id");
        $this->db->bind(':cart_id', $cartId);
        return $this->db->execute();
    }

    /**
     * Kiểm tra sản phẩm đã có trong giỏ chưa
     */
    public function hasItem(int $cartId, int $productId): bool {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM cart_items 
            WHERE cart_id = :cart_id AND product_id = :product_id
        ");
        $this->db->bind(':cart_id', $cartId);
        $this->db->bind(':product_id', $productId);
        $result = $this->db->single();
        return $result && (int)$result->total > 0;
    }

    /**
     * Lấy danh sách item trong giỏ hàng (kèm thông tin sản phẩm)
     */
    public function getCartItems(int $cartId): array {
        $this->db->query("
            SELECT ci.id as item_id,
                   ci.added_at,
                   p.id as product_id,
                   p.title,
                   p.price,
                   p.preview_url,
                   p.rating,
                   p.review_count,
                   p.description,
                   s.name as store_name,
                   s.user_id as seller_id
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            WHERE ci.cart_id = :cart_id 
              AND p.status = 2 
              AND p.deleted_at IS NULL
            ORDER BY ci.added_at DESC
        ");
        $this->db->bind(':cart_id', $cartId);
        return $this->db->resultSet();
    }

    /**
     * Đếm tổng số item trong giỏ hàng
     */
    public function getCartCount(int $cartId): int {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM cart_items 
            WHERE cart_id = :cart_id
        ");
        $this->db->bind(':cart_id', $cartId);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Tính tổng tiền giỏ hàng
     */
    public function getCartTotal(int $cartId): float {
        $this->db->query("
            SELECT COALESCE(SUM(p.price), 0) as total_price
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = :cart_id 
              AND p.status = 2 
              AND p.deleted_at IS NULL
        ");
        $this->db->bind(':cart_id', $cartId);
        $result = $this->db->single();
        return $result ? (float)$result->total_price : 0.0;
    }
}

<?php
class Order extends BaseModel
{
    protected string $table = 'orders';

    /**
     * Lấy doanh thu của seller
     */
    public function getSellerRevenue(int $user_id): float
    {
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
    public function getSellerPendingOrdersCount(int $user_id): int
    {
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
    public function getSellerRecentOrders(int $user_id, int $limit = 5): array
    {
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
        WHERE s.user_id = ?
        ORDER BY o.created_at DESC
        LIMIT ?
    ");
        $this->db->bind(1, $user_id);
        $this->db->bind(2, $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Xử lý thanh toán cho MỘT sản phẩm
     */
    public function processPayment(int $buyerId, int $sellerId, int $productId, string $productTitle, float $price)
    {
        // phí nền tảng là 5%
        $platformFee = $price * 0.05;
        $sellerAmount = $price - $platformFee;
        $orderNumber = 'ORD-' . date('Y') . '-' . time();

        try {
            $this->db->beginTransaction();

            // 1. Kiểm tra số dư người mua (Dùng FOR UPDATE để khóa dòng, tránh lỗi đồng thời)
            $this->db->query("SELECT id, balance FROM wallets WHERE user_id = :user_id FOR UPDATE");
            $this->db->bind(':user_id', $buyerId);
            $buyerWallet = $this->db->single();

            if (!$buyerWallet || $buyerWallet->balance < $price) {
                throw new Exception("Số dư không đủ.");
            }

            // 2. Lấy ví người bán
            $this->db->query("SELECT id FROM wallets WHERE user_id = :user_id FOR UPDATE");
            $this->db->bind(':user_id', $sellerId);
            $sellerWallet = $this->db->single();

            // 3. Trừ tiền người mua & Ghi log (Type 3: Payment)
            $this->db->query("UPDATE wallets SET balance = balance - :amount WHERE id = :id");
            $this->db->bind(':amount', $price);
            $this->db->bind(':id', $buyerWallet->id);
            $this->db->execute();

            $this->db->query("INSERT INTO transactions (wallet_id, type, amount, description) VALUES (:wallet_id, 3, :amount, :desc)");
            $this->db->bind(':wallet_id', $buyerWallet->id);
            $this->db->bind(':amount', -$price);
            $this->db->bind(':desc', "Thanh toán đơn hàng $orderNumber");
            $this->db->execute();

            // 4. Cộng tiền cho người bán & Ghi log (Type 5: Earning)
            $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE id = :id");
            $this->db->bind(':amount', $sellerAmount);
            $this->db->bind(':id', $sellerWallet->id);
            $this->db->execute();

            $this->db->query("INSERT INTO transactions (wallet_id, type, amount, description) VALUES (:wallet_id, 5, :amount, :desc)");
            $this->db->bind(':wallet_id', $sellerWallet->id);
            $this->db->bind(':amount', $sellerAmount);
            $this->db->bind(':desc', "Doanh thu từ đơn hàng $orderNumber");
            $this->db->execute();

            // 5. Tạo Order (Status 2: Paid)
            $this->db->query("INSERT INTO orders (order_number, user_id, product_id, total_amount, platform_fee, seller_amount, status) VALUES (:order_num, :user_id, :product_id, :total, :fee, :seller_amt, 2)");
            $this->db->bind(':order_num', $orderNumber);
            $this->db->bind(':user_id', $buyerId);
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':total', $price);
            $this->db->bind(':fee', $platformFee);
            $this->db->bind(':seller_amt', $sellerAmount);
            $this->db->execute();
            $orderId = $this->db->lastInsertId();

            // 6. Tạo Order Items
            $this->db->query("INSERT INTO order_items (order_id, product_id, product_name, unit_price, subtotal, platform_fee, seller_amount) VALUES (:order_id, :product_id, :name, :price, :subtotal, :fee, :seller_amt)");
            $this->db->bind(':order_id', $orderId);
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':name', $productTitle);
            $this->db->bind(':price', $price);
            $this->db->bind(':subtotal', $price);
            $this->db->bind(':fee', $platformFee);
            $this->db->bind(':seller_amt', $sellerAmount);
            $this->db->execute();

            // Hoàn tất giao dịch
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Nếu có bất kỳ lỗi nào xảy ra quay ngược toàn bộ thao tác
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Xử lý thanh toán cho TOÀN BỘ giỏ hàng
     */
    public function processCartPayment(int $buyerId, array $cartItems, float $totalAmount): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Kiểm tra và khóa dòng ví người mua
            $this->db->query("SELECT id, balance FROM wallets WHERE user_id = :user_id FOR UPDATE");
            $this->db->bind(':user_id', $buyerId);
            $buyerWallet = $this->db->single();

            if (!$buyerWallet || $buyerWallet->balance < $totalAmount) {
                throw new Exception("Số dư ví người mua không đủ.");
            }

            // 2. Trừ TỔNG TIỀN người mua & Ghi log 1 lần duy nhất
            $this->db->query("UPDATE wallets SET balance = balance - :amount WHERE id = :id");
            $this->db->bind(':amount', $totalAmount);
            $this->db->bind(':id', $buyerWallet->id);
            $this->db->execute();

            $orderGroupNumber = 'GRP-' . date('Ymd') . '-' . time(); // Mã nhóm đơn hàng

            $this->db->query("INSERT INTO transactions (wallet_id, type, amount, description) VALUES (:wallet_id, 3, :amount, :desc)");
            $this->db->bind(':wallet_id', $buyerWallet->id);
            $this->db->bind(':amount', -$totalAmount);
            $this->db->bind(':desc', "Thanh toán giỏ hàng ($orderGroupNumber)");
            $this->db->execute();

            // 3. Vòng lặp: Xử lý giải ngân và tạo đơn cho TỪNG sản phẩm trong giỏ
            foreach ($cartItems as $item) {
                $price = (float)$item->price;
                $platformFee = $price * 0.05; // Phí nền tảng 5%
                $sellerAmount = $price - $platformFee;
                $sellerId = (int)$item->seller_id;
                $orderNumber = 'ORD-' . date('YmdHis') . '-' . mt_rand(1000, 9999);

                // Lấy và khóa dòng ví người bán
                $this->db->query("SELECT id FROM wallets WHERE user_id = :user_id FOR UPDATE");
                $this->db->bind(':user_id', $sellerId);
                $sellerWallet = $this->db->single();

                if (!$sellerWallet) {
                    throw new Exception("Người bán (ID: $sellerId) chưa có ví.");
                }

                // Cộng tiền người bán
                $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE id = :id");
                $this->db->bind(':amount', $sellerAmount);
                $this->db->bind(':id', $sellerWallet->id);
                $this->db->execute();

                // Ghi log doanh thu người bán
                $this->db->query("INSERT INTO transactions (wallet_id, type, amount, description) VALUES (:wallet_id, 5, :amount, :desc)");
                $this->db->bind(':wallet_id', $sellerWallet->id);
                $this->db->bind(':amount', $sellerAmount);
                $this->db->bind(':desc', "Doanh thu từ đơn hàng $orderNumber");
                $this->db->execute();

                // Tạo Order (Bảng orders)
                $this->db->query("INSERT INTO orders (order_number, user_id, product_id, total_amount, platform_fee, seller_amount, status) VALUES (:order_num, :user_id, :product_id, :total, :fee, :seller_amt, 2)");
                $this->db->bind(':order_num', $orderNumber);
                $this->db->bind(':user_id', $buyerId);
                $this->db->bind(':product_id', $item->product_id);
                $this->db->bind(':total', $price);
                $this->db->bind(':fee', $platformFee);
                $this->db->bind(':seller_amt', $sellerAmount);
                $this->db->execute();
                
                $orderId = $this->db->lastInsertId();

                // Tạo Order Items
                $this->db->query("INSERT INTO order_items (order_id, product_id, product_name, unit_price, subtotal, platform_fee, seller_amount) VALUES (:order_id, :product_id, :name, :price, :subtotal, :fee, :seller_amt)");
                $this->db->bind(':order_id', $orderId);
                $this->db->bind(':product_id', $item->product_id);
                $this->db->bind(':name', $item->title);
                $this->db->bind(':price', $price);
                $this->db->bind(':subtotal', $price);
                $this->db->bind(':fee', $platformFee);
                $this->db->bind(':seller_amt', $sellerAmount);
                $this->db->execute();
            }

            // Mọi thứ hoàn hảo -> Xác nhận lưu Database
            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            // Có lỗi -> Hủy toàn bộ thao tác, không ai bị trừ tiền/cộng tiền sai
            $this->db->rollBack();
            error_log("Lỗi thanh toán giỏ hàng: " . $e->getMessage());
            return false;
        }
    }

    // Hàm kiểm tra User đã mua Product chưa để cho phép Download
    public function hasPurchased(int $userId, int $productId)
    {
        $this->db->query("SELECT id FROM orders WHERE user_id = :user_id AND product_id = :product_id AND status = 2");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);
        $row = $this->db->single();
        return !empty($row);
    }

    /**
     * Lấy danh sách tất cả các tài liệu đã mua của User
     */
    public function getPurchasedProducts(int $userId): array
    {
        $this->db->query("
            SELECT o.id as order_id, 
                   o.created_at as purchased_at, 
                   p.id as product_id, 
                   p.title, 
                   p.price, 
                   p.preview_url, 
                   p.description,
                   s.name as store_name
            FROM {$this->table} o
            JOIN products p ON o.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            WHERE o.user_id = :user_id AND o.status = 2
            ORDER BY o.created_at DESC
        ");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Lấy thông tin chi tiết đơn hàng
     */
    public function getOrderById(int $orderId): ?object
    {
        $this->db->query("
            SELECT o.*, 
                   p.title as product_title,
                   s.user_id as seller_id,
                   s.name as store_name,
                   u.name as buyer_name,
                   u.email as buyer_email
            FROM {$this->table} o
            JOIN products p ON o.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            JOIN users u ON o.user_id = u.id
            WHERE o.id = :id
        ");
        $this->db->bind(':id', $orderId);
        return $this->db->single() ?: null;
    }

    /**
     * Lấy danh sách đơn hàng đã mua của Buyer
     */
    public function getUserPurchasedOrders(int $userId): array
    {
        $this->db->query("
            SELECT o.*, p.title as product_title, p.preview_url, s.name as store_name
            FROM {$this->table} o
            JOIN products p ON o.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
        ");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus(int $orderId, int $status): bool
    {
        $this->db->query("UPDATE {$this->table} SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $orderId);
        return $this->db->execute();
    }
}

<?php

declare(strict_types=1);

/**
 * Service xử lý Hoàn tiền (Refund Management - UC32)
 * Thực hiện giao dịch nguyên tử (ACID Transaction) và SELECT ... FOR UPDATE để bảo toàn dòng tiền ví
 */
class RefundService
{
    /**
     * Thời hạn hoàn tiền tối đa (ngày) tính từ lúc tạo đơn
     */
    public const DEFAULT_REFUND_WINDOW_DAYS = 7;

    /**
     * Trạng thái đơn hàng
     */
    public const ORDER_STATUS_PENDING   = 1;
    public const ORDER_STATUS_PAID      = 2;
    public const ORDER_STATUS_CANCELLED = 3;
    public const ORDER_STATUS_REFUNDED  = 4;

    /**
     * Loại giao dịch trong bảng transactions
     */
    public const TRANS_TYPE_REFUND = 4;

    /**
     * Kiểm tra điều kiện hoàn tiền của đơn hàng
     *
     * @param int $orderId
     * @param int $maxDays
     * @return array
     */
    public static function checkEligibility(int $orderId, int $maxDays = self::DEFAULT_REFUND_WINDOW_DAYS): array
    {
        $db = new Database();
        $db->query("
            SELECT o.*, 
                   p.title as product_title,
                   s.user_id as seller_id,
                   s.name as store_name,
                   bu.name as buyer_name,
                   bu.email as buyer_email
            FROM orders o
            JOIN products p ON o.product_id = p.id
            JOIN stores s ON p.store_id = s.id
            JOIN users bu ON o.user_id = bu.id
            WHERE o.id = :id
        ");
        $db->bind(':id', $orderId);
        $order = $db->single();

        if (!$order) {
            return [
                'eligible' => false,
                'message'  => 'Đơn hàng không tồn tại trong hệ thống.',
                'order'    => null
            ];
        }

        if ((int)$order->status === self::ORDER_STATUS_REFUNDED) {
            return [
                'eligible' => false,
                'message'  => 'Đơn hàng đã được hoàn tiền trước đó (Status: Refunded).',
                'order'    => $order
            ];
        }

        if ((int)$order->status !== self::ORDER_STATUS_PAID) {
            $statusName = match ((int)$order->status) {
                self::ORDER_STATUS_PENDING => 'Đang chờ thanh toán (Pending)',
                self::ORDER_STATUS_CANCELLED => 'Đã bị hủy (Cancelled)',
                default => 'Không xác định'
            };
            return [
                'eligible' => false,
                'message'  => "Chỉ có thể hoàn tiền cho đơn hàng đã thanh toán thành công. Trạng thái hiện tại: {$statusName}.",
                'order'    => $order
            ];
        }

        // Kiểm tra thời hạn hoàn tiền
        if (!empty($order->created_at)) {
            $createdAt = strtotime((string)$order->created_at);
            $daysDiff = (time() - $createdAt) / 86400;
            if ($daysDiff > $maxDays) {
                $days = ceil($daysDiff);
                return [
                    'eligible' => false,
                    'message'  => "Đơn hàng đã quá hạn hoàn tiền ({$days} ngày > quy định {$maxDays} ngày).",
                    'order'    => $order
                ];
            }
        }

        return [
            'eligible' => true,
            'message'  => 'Đơn hàng đủ điều kiện hoàn tiền hợp lệ.',
            'order'    => $order
        ];
    }

    /**
     * Thực hiện quy trình hoàn tiền cho đơn hàng (ACID Transaction)
     *
     * @param int $orderId
     * @param string $reason Lý do hoàn tiền
     * @param bool $ignoreTimeLimit Bỏ qua kiểm tra thời hạn (dành cho Admin can thiệp)
     * @return array
     */
    public static function processRefund(int $orderId, string $reason = '', bool $ignoreTimeLimit = false): array
    {
        $reason = trim($reason) !== '' ? trim($reason) : 'Yêu cầu hoàn tiền từ người mua';

        // 1. Kiểm tra tính hợp lệ
        $check = self::checkEligibility($orderId, $ignoreTimeLimit ? 9999 : self::DEFAULT_REFUND_WINDOW_DAYS);
        if (!$check['eligible']) {
            return [
                'success' => false,
                'message' => $check['message'],
                'data'    => null
            ];
        }

        $order = $check['order'];
        $buyerId = (int)$order->user_id;
        $sellerId = (int)$order->seller_id;
        $refundTotal = (float)$order->total_amount;
        $sellerDeduction = (float)$order->seller_amount;
        $platformFeeRefund = (float)$order->platform_fee;

        $db = new Database();

        try {
            $db->beginTransaction();

            // 2. Lấy và khóa ví người mua (SELECT ... FOR UPDATE)
            $db->query("SELECT id, balance FROM wallets WHERE user_id = :user_id FOR UPDATE");
            $db->bind(':user_id', $buyerId);
            $buyerWallet = $db->single();

            if (!$buyerWallet) {
                // Tự động khởi tạo ví người mua nếu chưa có
                $db->query("INSERT INTO wallets (user_id, balance, frozen_balance) VALUES (:user_id, 0.0000, 0.0000)");
                $db->bind(':user_id', $buyerId);
                $db->execute();
                $buyerWalletId = (int)$db->lastInsertId();
                $buyerOldBalance = 0.0;
            } else {
                $buyerWalletId = (int)$buyerWallet->id;
                $buyerOldBalance = (float)$buyerWallet->balance;
            }

            // 3. Lấy và khóa ví người bán (SELECT ... FOR UPDATE)
            $db->query("SELECT id, balance FROM wallets WHERE user_id = :user_id FOR UPDATE");
            $db->bind(':user_id', $sellerId);
            $sellerWallet = $db->single();

            if (!$sellerWallet) {
                throw new Exception("Không tìm thấy ví của Người bán (Store Owner).");
            }
            $sellerWalletId = (int)$sellerWallet->id;
            $sellerOldBalance = (float)$sellerWallet->balance;

            // 4. Cộng hoàn tiền vào ví người mua
            $db->query("UPDATE wallets SET balance = balance + :amount WHERE id = :id");
            $db->bind(':amount', $refundTotal);
            $db->bind(':id', $buyerWalletId);
            $db->execute();

            // Ghi nhật ký giao dịch hoàn tiền cho Buyer (Type 4: Refund)
            $db->query("
                INSERT INTO transactions (wallet_id, reference_id, type, amount, description) 
                VALUES (:wallet_id, :ref_id, :type, :amount, :desc)
            ");
            $db->bind(':wallet_id', $buyerWalletId);
            $db->bind(':ref_id', $orderId);
            $db->bind(':type', self::TRANS_TYPE_REFUND);
            $db->bind(':amount', $refundTotal);
            $db->bind(':desc', "Hoàn tiền đơn hàng {$order->order_number} - {$reason}");
            $db->execute();
            $buyerTransId = (int)$db->lastInsertId();

            // 5. Khấu trừ doanh thu từ ví người bán
            $db->query("UPDATE wallets SET balance = balance - :amount WHERE id = :id");
            $db->bind(':amount', $sellerDeduction);
            $db->bind(':id', $sellerWalletId);
            $db->execute();

            // Ghi nhật ký trừ tiền hoàn trả cho Seller (Type 4: Refund)
            $db->query("
                INSERT INTO transactions (wallet_id, reference_id, type, amount, description) 
                VALUES (:wallet_id, :ref_id, :type, :amount, :desc)
            ");
            $db->bind(':wallet_id', $sellerWalletId);
            $db->bind(':ref_id', $orderId);
            $db->bind(':type', self::TRANS_TYPE_REFUND);
            $db->bind(':amount', -$sellerDeduction);
            $db->bind(':desc', "Khấu trừ hoàn tiền đơn hàng {$order->order_number} - {$reason}");
            $db->execute();
            $sellerTransId = (int)$db->lastInsertId();

            // 6. Cập nhật trạng thái đơn hàng thành 4 (Refunded)
            $db->query("UPDATE orders SET status = :status WHERE id = :id");
            $db->bind(':status', self::ORDER_STATUS_REFUNDED);
            $db->bind(':id', $orderId);
            $db->execute();

            // 7. Commit Transaction an toàn
            $db->commit();

            return [
                'success' => true,
                'message' => "Hoàn tiền thành công cho đơn hàng {$order->order_number}!",
                'data'    => [
                    'order_id'             => $orderId,
                    'order_number'         => $order->order_number,
                    'product_title'        => $order->product_title,
                    'refund_amount'        => $refundTotal,
                    'platform_fee_refund'  => $platformFeeRefund,
                    'seller_deduction'     => $sellerDeduction,
                    'buyer' => [
                        'user_id'     => $buyerId,
                        'name'        => $order->buyer_name,
                        'wallet_id'   => $buyerWalletId,
                        'old_balance' => $buyerOldBalance,
                        'new_balance' => $buyerOldBalance + $refundTotal,
                        'trans_id'    => $buyerTransId
                    ],
                    'seller' => [
                        'user_id'     => $sellerId,
                        'store_name'  => $order->store_name,
                        'wallet_id'   => $sellerWalletId,
                        'old_balance' => $sellerOldBalance,
                        'new_balance' => $sellerOldBalance - $sellerDeduction,
                        'trans_id'    => $sellerTransId
                    ],
                    'refunded_at'          => date('Y-m-d H:i:s'),
                    'reason'               => $reason
                ]
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            return [
                'success' => false,
                'message' => 'Xảy ra lỗi trong quá trình xử lý giao dịch hoàn tiền: ' . $e->getMessage(),
                'data'    => null
            ];
        }
    }
}

<?php
class Wallet
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // 1. Lấy thông tin ví dựa vào user_id
    public function getWalletByUserId(int $userId)
    {
        $this->db->query("SELECT * FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }

    // 2. Lấy lịch sử biến động số dư (transactions)
    public function getTransactions(int $wallet_id)
    {
        $this->db->query("SELECT * FROM transactions WHERE wallet_id = :wallet_id ORDER BY created_at DESC");
        $this->db->bind(':wallet_id', $wallet_id);
        return $this->db->resultSet();
    }

    // 3. Yêu cầu rút tiền (Gọi Stored Procedure)
    public function requestWithdrawal(array $data)
    {
        // Sử dụng Stored Procedure sp_RequestWithdrawal đã tạo trong DB V3
        $this->db->query('CALL sp_RequestWithdrawal(:wallet_id, :amount, :bank_name, :bank_acc_num, :bank_acc_name)');

        $this->db->bind(':wallet_id', $data['wallet_id']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':bank_name', $data['bank_name']);
        $this->db->bind(':bank_acc_num', $data['bank_acc_num']);
        $this->db->bind(':bank_acc_name', $data['bank_acc_name']);

        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            // Nếu số dư không đủ, Stored Procedure sẽ văng lỗi (SIGNAL SQLSTATE)
            return false;
        }
    }

    // 4. Kiểm tra số dư ví có đủ để thanh toán hay không
    public function checkBalance(int $userId, float $amount): bool
    {
        $wallet = $this->getWalletByUserId($userId);
        if ($wallet && $wallet->balance >= $amount) {
            return true;
        }
        return false;
    }

    // 5. Cập nhật số dư ví và ghi log transaction (Dùng trong Transaction)
    public function updateBalanceWithTransaction(Database $dbInstance, int $walletId, float $amount, int $type, int $referenceId, string $description)
    {
        // Cập nhật số dư
        $dbInstance->query("UPDATE wallets SET balance = balance + :amount WHERE id = :id");
        $dbInstance->bind(':amount', $amount); // Nếu trừ tiền thì $amount mang giá trị âm
        $dbInstance->bind(':id', $walletId);
        $dbInstance->execute();

        // Ghi log vào bảng transactions
        $dbInstance->query("INSERT INTO transactions (wallet_id, reference_id, type, amount, description) 
                            VALUES (:wallet_id, :ref_id, :type, :amount, :desc)");
        $dbInstance->bind(':wallet_id', $walletId);
        $dbInstance->bind(':ref_id', $referenceId);
        $dbInstance->bind(':type', $type);
        $dbInstance->bind(':amount', $amount);
        $dbInstance->bind(':desc', $description);
        $dbInstance->execute();

        return true;
    }
    public function deposit(int $walletId, float $amount): bool
    {
        try {
            $this->db->beginTransaction();

            $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE id = :id");
            $this->db->bind(':amount', $amount);
            $this->db->bind(':id', $walletId);
            $this->db->execute();

            $this->db->query("INSERT INTO transactions (wallet_id, type, amount, description, payment_method) 
                          VALUES (:wallet_id, 1, :amount, :desc, 'Giả lập')");
            $this->db->bind(':wallet_id', $walletId);
            $this->db->bind(':amount', $amount);
            $this->db->bind(':desc', 'Nạp tiền vào ví');
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Lấy danh sách yêu cầu rút tiền đang chờ duyệt (Sử dụng View có sẵn trong DB Final)
     */
    /**
     * Lấy danh sách yêu cầu rút tiền đang chờ duyệt
     */
    public function getPendingWithdrawals(): array
    {
        // Truy vấn trực tiếp thay vì dùng View vw_pendingwithdrawals bị thiếu cột
        $this->db->query("
            SELECT w.id AS request_id, 
                   u.email AS seller_email, 
                   w.amount AS amount, 
                   w.bank_name AS bank_name, 
                   w.bank_account_number AS bank_account_number, 
                   w.bank_account_name AS bank_account_name, 
                   w.created_at AS created_at 
            FROM withdraw_requests w 
            JOIN wallets wal ON w.wallet_id = wal.id 
            JOIN users u ON wal.user_id = u.id 
            WHERE w.status = 1
            ORDER BY w.created_at ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Xử lý Phê duyệt / Từ chối rút tiền
     */
    public function processWithdrawalAdmin(int $requestId, int $adminId, string $action): bool
    {
        if ($action === 'APPROVE') {
            // Gọi Stored Procedure đã có sẵn trong DB Final
            $this->db->query('CALL sp_ApproveWithdrawal(:req_id, :admin_id)');
            $this->db->bind(':req_id', $requestId);
            $this->db->bind(':admin_id', $adminId);
            try {
                return $this->db->execute();
            } catch (Exception $e) {
                error_log("Lỗi SP Duyệt rút tiền: " . $e->getMessage());
                return false;
            }
        } elseif ($action === 'REJECT') {
            // Viết Transaction để Từ chối: Hoàn lại tiền đang bị đóng băng (frozen_balance)
            try {
                $this->db->beginTransaction();
                
                $this->db->query("SELECT wallet_id, amount, status FROM withdraw_requests WHERE id = :id FOR UPDATE");
                $this->db->bind(':id', $requestId);
                $req = $this->db->single();

                if ($req && $req->status == 1) {
                    // Hoàn tiền từ frozen_balance về lại balance
                    $this->db->query("UPDATE wallets SET balance = balance + :amount, frozen_balance = frozen_balance - :amount WHERE id = :wallet_id");
                    $this->db->bind(':amount', $req->amount);
                    $this->db->bind(':wallet_id', $req->wallet_id);
                    $this->db->execute();

                    // Cập nhật trạng thái thành Bị từ chối (3)
                    $this->db->query("UPDATE withdraw_requests SET status = 3, processed_by = :admin_id WHERE id = :id");
                    $this->db->bind(':admin_id', $adminId);
                    $this->db->bind(':id', $requestId);
                    $this->db->execute();

                    $this->db->commit();
                    return true;
                }
                $this->db->rollBack();
                return false;
            } catch (Exception $e) {
                $this->db->rollBack();
                return false;
            }
        }
        return false;
    }
}

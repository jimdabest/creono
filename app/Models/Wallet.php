<?php
class Wallet {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // 1. Lấy thông tin ví dựa vào user_id
    public function getWalletByUserId($userId) {
        $this->db->query("SELECT * FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }

    // 2. Lấy lịch sử biến động số dư (transactions)
    public function getTransactions($wallet_id) {
        $this->db->query("SELECT * FROM transactions WHERE wallet_id = :wallet_id ORDER BY created_at DESC");
        $this->db->bind(':wallet_id', $wallet_id);
        return $this->db->resultSet();
    }

    // 3. Yêu cầu rút tiền (Gọi Stored Procedure)
    public function requestWithdrawal($data) {
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
    public function checkBalance($userId, $amount) {
        $wallet = $this->getWalletByUserId($userId);
        if ($wallet && $wallet->balance >= $amount) {
            return true;
        }
        return false;
    }

    // 5. Cập nhật số dư ví và ghi log transaction (Dùng trong Transaction)
    public function updateBalanceWithTransaction($dbInstance, $walletId, $amount, $type, $referenceId, $description) {
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
}
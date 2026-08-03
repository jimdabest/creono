<?php
class Wallet {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // 1. Lấy thông tin ví dựa vào user_id
    public function getWalletByUserId($user_id) {
        $this->db->query('SELECT * FROM wallets WHERE user_id = :user_id');
        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    // 2. Lấy lịch sử biến động số dư (transactions)
    public function getTransactions($wallet_id) {
        $this->db->query('SELECT * FROM transactions WHERE wallet_id = :wallet_id ORDER BY created_at DESC');
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
}
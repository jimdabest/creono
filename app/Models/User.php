<?php
class User extends BaseModel {
    // Định nghĩa bảng mà model này tương tác
    protected $table = 'users';

    // Hàm kiểm tra Email đã tồn tại chưa
    public function findByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        
        // Gán giá trị
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Kiểm tra xem có email nào trùng không
        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Lấy toàn bộ thông tin User bằng Email
    public function getUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        
        return $this->db->single();
    }

    // Đăng ký người dùng mới
    public function register($data) {
        // 1. Thêm vào bảng users (Bổ sung trường name, role = 1)
        $this->db->query('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 1)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);

        // Thực thi Insert User
        if ($this->db->execute()) {
            // Lấy ID của user vừa tạo
            $userId = $this->db->lastInsertId();

            // 2. Tự động tạo Ví (Wallet) cho user này
            $this->db->query('INSERT INTO wallets (user_id, balance, frozen_balance) VALUES (:user_id, 0, 0)');
            $this->db->bind(':user_id', $userId);
            $this->db->execute();

            return true;
        } else {
            return false;
        }
    }
    
    // Xác thực người dùng (Kiểm tra Email và Mật khẩu)
    public function login($email, $password) {
        // Đã xóa 'AND deleted_at IS NULL'
        $this->db->query("SELECT * FROM {$this->table} WHERE email = :email");
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Nếu email tồn tại, kiểm tra hash mật khẩu
        if ($row) {
            // Đã sửa thành $row->password để khớp với DB mới
            $hashed_password = $row->password; 
            
            if (password_verify($password, $hashed_password)) {
                return $row; // Trả về toàn bộ thông tin user nếu đúng mật khẩu
            } else {
                return false; // Sai mật khẩu
            }
        } else {
            return false; // Không tìm thấy user
        }
    }
}
<?php
class User extends BaseModel {
    // Định nghĩa bảng mà model này tương tác
    protected string $table = 'users';

    // Hàm kiểm tra Email đã tồn tại chưa
    public function findByEmail(string $email): bool {
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
    public function getUserByEmail(string $email): ?object {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        
        return $this->db->single();
    }

    // Đăng ký người dùng mới
    public function register(array $data): bool {
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
    public function login(string $email, string $password): object|false {
        $this->db->query("SELECT * FROM {$this->table} WHERE email = :email");
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Nếu email tồn tại, kiểm tra hash mật khẩu
        if ($row) {
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

    // Lấy user theo ID (kèm profile)
    public function getUserWithProfile(int $id): ?object {
        $this->db->query("SELECT u.id, u.name, u.email, u.role, 
                                p.full_name, p.avatar_url, p.bio 
                        FROM {$this->table} u 
                        LEFT JOIN user_profiles p ON u.id = p.user_id 
                        WHERE u.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Cập nhật KYC status
    public function updateKycStatus(int $user_id, int $status): bool {
        $this->db->query("UPDATE {$this->table} SET kyc_status = :status WHERE id = :id");
        $this->db->bind(':status',$status);
        $this->db->bind(':id',$user_id);
        return $this->db->execute();
    }

    public function changePassword(int $user_id, string $new_password): bool {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $this->db->query("UPDATE {$this->table} SET password = :hash WHERE id = :id");
        $this->db->bind(':hash', $hash);
        $this->db->bind(':id', $user_id);
        return $this->db->execute();
    }

    // Kiểm tra role
    public function hasRole(int $user_id, int $role): bool {
        $this->db->query("SELECT id FROM {$this->table} WHERE id = :id AND role = :role");
        $this->db->bind(':id', $user_id);
        $this->db->bind(':role', $role);
        return $this->db->rowCount() > 0;
    }

    // Lấy tổng số lượng người dùng
    public function getTotalUsers(): int {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    // Lấy tổng số lượng người bán (role = 2)
    public function getTotalSellers(): int {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE role = 2");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }
}
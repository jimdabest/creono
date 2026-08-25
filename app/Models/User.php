<?php
class User extends BaseModel
{
    // Định nghĩa bảng mà model này tương tác
    protected string $table = 'users';

    // Hàm kiểm tra Email đã tồn tại chưa
    public function findByEmail(string $email): bool
    {
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
    public function getUserByEmail(string $email): ?object
    {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        return $this->db->single();
    }

    // Đăng ký người dùng mới
    public function register(array $data): bool
    {
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
    public function login(string $email, string $password): object|false
    {
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
    public function getUserWithProfile(int $id): ?object
    {
        $this->db->query("SELECT u.id, u.name, u.email, u.role, 
                                p.full_name, p.avatar_url, p.bio 
                        FROM {$this->table} u 
                        LEFT JOIN user_profiles p ON u.id = p.user_id 
                        WHERE u.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Cập nhật KYC status
    public function updateKycStatus(int $user_id, int $status): bool
    {
        $this->db->query("UPDATE {$this->table} SET kyc_status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $user_id);
        return $this->db->execute();
    }

    public function changePassword(int $user_id, string $new_password): bool
    {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $this->db->query("UPDATE {$this->table} SET password = :hash WHERE id = :id");
        $this->db->bind(':hash', $hash);
        $this->db->bind(':id', $user_id);
        return $this->db->execute();
    }

    // Kiểm tra role
    public function hasRole(int $user_id, int $role): bool
    {
        $this->db->query("SELECT id FROM {$this->table} WHERE id = :id AND role = :role");
        $this->db->bind(':id', $user_id);
        $this->db->bind(':role', $role);
        return $this->db->rowCount() > 0;
    }

    // Lấy tổng số lượng người dùng
    public function getTotalUsers(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    // Lấy tổng số lượng người bán (role = 2)
    public function getTotalSellers(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE role = 2");
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }

    /**
     * Tạo token đặt lại mật khẩu và lưu vào bảng password_reset_tokens
     * Thời hạn 15 phút
     */
    /**
     * Tạo token đặt lại mật khẩu và lưu vào bảng password_reset_tokens
     * Thời hạn 15 phút
     */
    public function createPasswordResetToken(string $email): string
    {
        // Lấy user_id từ email
        $this->db->query("SELECT id FROM {$this->table} WHERE email = :email");
        $this->db->bind(':email', $email);
        $user = $this->db->single();
        if (!$user) {
            throw new Exception('Email không tồn tại');
        }
        $userId = (int)$user->id;

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Xóa token cũ nếu có
        $this->db->query("DELETE FROM password_reset_tokens WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $this->db->execute();

        // Lưu token mới
        $this->db->query("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':token', $token);
        $this->db->bind(':expires_at', $expiresAt);
        $this->db->execute();

        return $token;
    }

    /**
     * Kiểm tra token có hợp lệ và chưa hết hạn không
     * Trả về user_id nếu hợp lệ, ngược lại null
     */
    public function isValidPasswordResetToken(string $token): ?int
    {
        $this->db->query("SELECT user_id FROM password_reset_tokens WHERE token = :token AND expires_at > NOW() LIMIT 1");
        $this->db->bind(':token', $token);
        $result = $this->db->single();
        return $result ? (int)$result->user_id : null;
    }
    /**
     * Xóa token sau khi đã sử dụng thành công
     */
    public function deletePasswordResetToken(string $token): void
    {
        $this->db->query("DELETE FROM password_reset_tokens WHERE token = :token");
        $this->db->bind(':token', $token);
        $this->db->execute();
    }

    /**
     * Cập nhật mật khẩu mới cho user theo user_id
     */
    public function updatePasswordById(int $user_id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query("UPDATE {$this->table} SET password = :password WHERE id = :id");
        $this->db->bind(':password', $hash);
        $this->db->bind(':id', $user_id);
        return $this->db->execute();
    }
}

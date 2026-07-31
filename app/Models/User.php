<?php
class User extends BaseModel {
    // Định nghĩa bảng mà model này tương tác
    protected $table = 'users';

    // Hàm kiểm tra Email đã tồn tại chưa
    public function findByEmail($email) {
        $this->db->query("SELECT * FROM {$this->table} WHERE email = :email AND deleted_at IS NULL");
        $this->db->bind(':email', $email);
        
        $this->db->single(); // Thực thi truy vấn

        // Nếu rowCount > 0 nghĩa là email đã có người dùng
        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Xác thực người dùng (Kiểm tra Email và Mật khẩu)
    public function login($email, $password) {
        $this->db->query("SELECT * FROM {$this->table} WHERE email = :email AND deleted_at IS NULL");
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Nếu email tồn tại, kiểm tra hash mật khẩu
        if ($row) {
            $hashed_password = $row->password_hash;
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
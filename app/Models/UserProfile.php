<?php
class UserProfile extends BaseModel {
    // Chỉ định bảng user_profiles
    protected string $table = 'user_profiles';

    // Hàm cập nhật profile theo user_id
    public function updateProfile(int $user_id, array $data): bool {
        // Xây dựng câu lệnh SQL động
        $fields = [];
        $bindings = [];
        
        if (isset($data['full_name'])) {
            $fields[] = "full_name = :full_name";
            $bindings[':full_name'] = $data['full_name'];
        }
        
        if (isset($data['bio'])) {
            $fields[] = "bio = :bio";
            $bindings[':bio'] = $data['bio'];
        }
        
        if (isset($data['avatar_url'])) {
            $fields[] = "avatar_url = :avatar_url";
            $bindings[':avatar_url'] = $data['avatar_url'];
        }
        
        // Nếu không có field nào để update
        if (empty($fields)) {
            return false;
        }
        
        // Kiểm tra xem record đã tồn tại chưa
        $this->db->query("SELECT user_id FROM {$this->table} WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        $exists = $this->db->single();
        
        if ($exists) {
            // UPDATE
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id";
        } else {
            // INSERT (cần có đủ các field)
            $allFields = ['user_id', 'full_name', 'bio', 'avatar_url'];
            $allValues = [
                ':user_id' => $user_id,
                ':full_name' => $data['full_name'] ?? null,
                ':bio' => $data['bio'] ?? null,
                ':avatar_url' => $data['avatar_url'] ?? null
            ];
            
            $sql = "INSERT INTO {$this->table} (user_id, full_name, bio, avatar_url) 
                    VALUES (:user_id, :full_name, :bio, :avatar_url)";
            $bindings = $allValues;
        }
        
        $this->db->query($sql);
        
        // Bind các giá trị
        foreach ($bindings as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        // Bind user_id cho UPDATE
        if ($exists) {
            $this->db->bind(':user_id', $user_id);
        }
        
        return $this->db->execute();
    }
    
    // Hàm hỗ trợ cập nhật Avatar riêng
    public function updateAvatar(int $user_id, string $avatar_url): bool {
        $sql = "UPDATE {$this->table} SET avatar_url = :avatar_url WHERE user_id = :user_id";
        $this->db->query($sql);
        $this->db->bind(':avatar_url', $avatar_url);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }
}
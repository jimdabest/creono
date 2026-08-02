<?php
class UserProfile extends BaseModel {
    // Chỉ định bảng user_profiles
    protected string $table = 'user_profiles';

    // Hàm cập nhật profile theo user_id (Vì user_id là khóa chính trong bảng này)
    public function updateProfile(int $user_id, array $data): bool {
        $sql = "INSERT INTO {$this->table} (user_id, full_name, bio, avatar_url) 
                VALUES (:user_id, :full_name, :bio, :avatar_url)
                ON DUPLICATE KEY UPDATE 
                    full_name = VALUES(full_name), 
                    bio = VALUES(bio), 
                    avatar_url = VALUES(avatar_url), 
                    updated_at = CURRENT_TIMESTAMP";
        $this->db->query($sql);
        
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':bio', $data['bio']);
        $this->db->bind(':avatar_url', $data['avatar_url'] ?? null);
        
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
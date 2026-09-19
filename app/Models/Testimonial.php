<?php
class Testimonial extends BaseModel {
    protected string $table = 'testimonials';
    
    public function getAllOrdered(): array {
        $this->db->query("
            SELECT t.*, u.name as user_name, u.email as user_email, up.avatar_url, up.full_name
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            ORDER BY t.is_featured DESC, t.sort_order ASC, t.created_at DESC
        ");
        return $this->db->resultSet();
    }
    
    public function getFeatured(int $limit = 3): array {
        $this->db->query("
            SELECT t.*, u.name as user_name, u.email as user_email, up.avatar_url, up.full_name
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE t.is_featured = 1
            ORDER BY t.sort_order ASC, t.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
    
    public function getRandom(int $limit = 3): array {
        $this->db->query("
            SELECT t.*, u.name as user_name, u.email as user_email, up.avatar_url, up.full_name
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            ORDER BY RAND()
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getByIdWithUser(int $id): ?object {
        $this->db->query("
            SELECT t.*, u.name as user_name, u.email as user_email, up.avatar_url, up.full_name
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE t.id = :id
        ");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        return $result ?: null;
    }

    public function toggleFeatured(int $id): bool {
        $this->db->query("
            UPDATE {$this->table}
            SET is_featured = CASE WHEN is_featured = 1 THEN 0 ELSE 1 END
            WHERE id = :id
        ");
        $this->db->bind(':id', $id);
        $success = $this->db->execute();
        if ($success) {
            $this->clearCache($id);
        }
        return $success;
    }
}
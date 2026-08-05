<?php
class Testimonial extends BaseModel {
    protected string $table = 'testimonials';
    
    public function getAllOrdered(): array {
        $this->db->query("
            SELECT t.*, u.name as user_name
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            ORDER BY t.is_featured DESC, t.sort_order ASC, t.created_at DESC
        ");
        return $this->db->resultSet();
    }
    
    public function getFeatured(int $limit = 3): array {
        $this->db->query("
            SELECT t.*, u.name as user_name
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            WHERE t.is_featured = TRUE
            ORDER BY t.sort_order ASC, t.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
    
    public function getRandom(int $limit = 3): array {
        $this->db->query("
            SELECT t.*, u.name as user_name
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            ORDER BY RAND()
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
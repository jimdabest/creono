<?php
class Category extends BaseModel {
    protected string $table = 'categories';
    
    public function getAllOrdered(): array {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY sort_order ASC, name ASC");
        return $this->db->resultSet();
    }
    
    public function getCategoriesWithProducts(): array {
        $this->db->query("
            SELECT DISTINCT c.*, COUNT(p.id) as product_count 
            FROM {$this->table} c
            LEFT JOIN products p ON p.category_id = c.id AND p.status = 2
            WHERE p.id IS NOT NULL
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ");
        return $this->db->resultSet();
    }
    
    public function getBySlug(string $slug): ?object {
        $this->db->query("SELECT * FROM {$this->table} WHERE slug = :slug");
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }
    
    public function getProductsByCategory(int $category_id, int $limit = 8): array {
        $this->db->query("
            SELECT p.*, s.name as store_name 
            FROM products p
            JOIN stores s ON p.store_id = s.id
            WHERE p.category_id = :category_id AND p.status = 2
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':category_id', $category_id);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
<?php

declare(strict_types=1);
class Store extends BaseModel
{
    protected string $table = 'stores';
    public function getStoreIdByUserId(int $userId): ?int
    {
        $this->db->query("SELECT id FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? (int)$result->id : null;
    }
    public function getStoreByUserId(int $userId): ?object
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? $result : null;
    }
    public function getStoreBySlug(string $slug): ?object
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1");
        $this->db->bind(':slug', $slug);

        $result = $this->db->single();

        // Nếu không có kết quả (false), trả về null để đúng với kiểu ?object
        return $result ? $result : null;
    }

    public function createStore(array $data): bool
    {
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        $slug = $data['slug'] ?? '';
        $counter = 1;
        while ($this->slugExists($slug)) {
            $slug = $data['slug'] . '-' . $counter++;
        }
        $data['slug'] = $slug;

        return $this->create($data);
    }

    public function updateStore(int $id, array $data): bool
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
            $counter = 1;
            $newSlug = $data['slug'];
            while ($this->slugExists($newSlug, $id)) {
                $newSlug = $data['slug'] . '-' . $counter++;
            }
            $data['slug'] = $newSlug;
        }
        return $this->update($id, $data);
    }

    private function generateSlug(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $text);
        $text = strtolower($text);
        $text = preg_replace('/[\s\-]+/', '-', $text);
        return trim($text, '-');
    }

    private function slugExists(string $slug, int $exceptId = 0): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE slug = :slug";
        if ($exceptId > 0) {
            $sql .= " AND id != :id";
        }
        $this->db->query($sql);
        $this->db->bind(':slug', $slug);
        if ($exceptId > 0) {
            $this->db->bind(':id', $exceptId);
        }
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }
}

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
}

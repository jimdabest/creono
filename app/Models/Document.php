<?php
class Document extends BaseModel
{
    protected string $table = 'documents';
    public function updateFileUrl(int $productId, string $fileUrl): bool
    {
        $this->db->query("UPDATE {$this->table} SET file_url = :file_url WHERE product_id = :product_id");
        $this->db->bind(':file_url', $fileUrl);
        $this->db->bind(':product_id', $productId);
        return $this->db->execute();
    }
}

<?php
class Document extends BaseModel
{
    protected string $table = 'documents';

    // Hàm cập nhật URL của file
    public function updateFileUrl(int $productId, string $fileUrl): bool
    {
        $this->db->query("UPDATE {$this->table} SET file_url = :file_url WHERE product_id = :product_id");
        $this->db->bind(':file_url', $fileUrl);
        $this->db->bind(':product_id', $productId);
        return $this->db->execute();
    }

    // THÊM MỚI: Hàm cập nhật các trường dữ liệu dựa vào product_id (Dùng cho duyệt Kháng cáo AI)
    public function updateByProductId(int $productId, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $setClause = '';
        foreach ($data as $key => $value) {
            $setClause .= "{$key} = :{$key}, ";
        }
        $setClause = rtrim($setClause, ', '); // Xóa dấu phẩy thừa ở cuối

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE product_id = :product_id";
        $this->db->query($sql);

        // Bind ID sản phẩm
        $this->db->bind(':product_id', $productId);
        
        // Bind dữ liệu động (như ai_label_id = 1)
        foreach ($data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }

        return $this->db->execute();
    }
}
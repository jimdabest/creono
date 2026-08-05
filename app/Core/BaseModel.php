<?php
class BaseModel {
    protected Database $db;
    protected string $table = ''; // Tên bảng sẽ được Model con định nghĩa
    protected array $cache = [];

    public function __construct() {
        // Tự động kết nối DB cho tất cả các model kế thừa
        $this->db = new Database();
    }

    // 1. Lấy tất cả bản ghi (Bỏ qua các bản ghi đã bị xóa mềm)
    public function findAll(): array {
        $this->db->query("SELECT * FROM {$this->table}");
        return $this->db->resultSet();
    }

    // 2. Lấy 1 bản ghi theo ID
    public function findById(int $id): ?object {
        $cacheKey = $this->table . '_' . $id;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $result = $this->db->single();

        if ($result) {
            $this->cache[$cacheKey] = $result;
        }

        return $result;
    }

    // 3. Thêm mới bản ghi linh hoạt
    // Truyền vào mảng associative: ['title' => 'Sách PHP', 'price' => 50000]
    public function create(array $data): bool {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $this->db->query($sql);

        // Bind dữ liệu động
        foreach ($data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }

        return $this->db->execute();
    }

    // 4. Cập nhật bản ghi linh hoạt
    public function update(int $id, array $data): bool {
        $setClause = '';
        foreach ($data as $key => $value) {
            $setClause .= "{$key} = :{$key}, ";
        }
        $setClause = rtrim($setClause, ', '); // Xóa dấu phẩy thừa ở cuối

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $this->db->query($sql);

        // Bind ID
        $this->db->bind(':id', $id);
        
        // Bind dữ liệu động
        foreach ($data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }

        $updated = $this->db->execute();
        if ($updated) {
            unset($this->cache[$this->table . '_' . $id]);
        }

        return $updated;
    }

    // 5. Xóa mềm (Cập nhật cột deleted_at)
    public function delete(int $id): bool {
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $deleted = $this->db->execute();
        if ($deleted) {
            unset($this->cache[$this->table . '_' . $id]);
        }

        return $deleted;
    }

    // 6. Xóa cứng (Xóa vĩnh viễn khỏi DB)
    public function destroy(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $destroyed = $this->db->execute();
        if ($destroyed) {
            unset($this->cache[$this->table . '_' . $id]);
        }

        return $destroyed;
    }
}
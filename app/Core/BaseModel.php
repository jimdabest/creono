<?php
class BaseModel {
    protected $db;
    protected $table = ''; // Tên bảng sẽ được Model con định nghĩa

    public function __construct() {
        // Tự động kết nối DB cho tất cả các model kế thừa
        $this->db = new Database();
    }

    // 1. Lấy tất cả bản ghi (Bỏ qua các bản ghi đã bị xóa mềm)
    public function findAll() {
        // Kiểm tra xem bảng có cột deleted_at không, ở đây ta giả định hệ thống dùng Soft Delete
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    // 2. Lấy 1 bản ghi theo ID
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // 3. Thêm mới bản ghi linh hoạt
    // Truyền vào mảng associative: ['title' => 'Sách PHP', 'price' => 50000]
    public function create($data) {
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
    public function update($id, $data) {
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

        return $this->db->execute();
    }

    // 5. Xóa mềm (Cập nhật cột deleted_at)
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // 6. Xóa cứng (Xóa vĩnh viễn khỏi DB)
    public function destroy($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
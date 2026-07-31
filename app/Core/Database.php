<?php
/*
 * PDO Database Class
 * Kết nối Database, tạo Prepared Statements, Bind values và trả về kết quả
 */
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database Handler (Lưu trữ instance PDO)
    private $stmt; // Statement (Lưu trữ câu truy vấn chuẩn bị)
    private $error;

    public function __construct() {
        // Cấu hình DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        
        $options = array(
            PDO::ATTR_PERSISTENT => true, // Giữ kết nối mở để tăng hiệu suất
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Bật chế độ báo lỗi exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ // Mặc định trả về object ($result->name thay vì $result['name'])
        );

        // Khởi tạo PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die("Lỗi kết nối DB: " . $this->error);
        }
    }

    // 1. Chuẩn bị câu lệnh SQL
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // 2. Gán giá trị (Bind values) an toàn vào Prepared Statement
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR; // Mặc định là String (Dùng cho cả UUID)
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // 3. Thực thi Prepared Statement
    public function execute() {
        return $this->stmt->execute();
    }

    // 4. Lấy danh sách kết quả (Nhiều dòng - Dùng cho trang danh sách)
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(); // Do cấu hình mặc định nên sẽ trả về mảng Object
    }

    // 5. Lấy một dòng kết quả duy nhất (Dùng cho trang chi tiết, lấy 1 user)
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // 6. Lấy số lượng dòng bị tác động (Dùng cho INSERT, UPDATE, DELETE)
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    // 7. Bắt đầu một Transaction (Dành cho UC thanh toán Escrow / Giải ngân)
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    // 8. Chấp nhận Transaction
    public function commit() {
        return $this->dbh->commit();
    }

    // 9. Hủy bỏ Transaction nếu có lỗi
    public function rollBack() {
        return $this->dbh->rollBack();
    }
}
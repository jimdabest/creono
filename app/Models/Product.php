<?php
class Product {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Lấy tất cả sản phẩm kèm tên cửa hàng
    public function getProducts() {
        $this->db->query("SELECT products.*, stores.name as store_name 
                          FROM products 
                          JOIN stores ON products.store_id = stores.id 
                          WHERE products.status = 2 
                          ORDER BY products.created_at DESC");
        
        return $this->db->resultSet();
    }
}
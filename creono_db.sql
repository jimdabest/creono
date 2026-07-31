-- 1. CREATE DATABASE
CREATE DATABASE IF NOT EXISTS creono_db 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE creono_db;

-- 2. CREATE TABLES & CONSTRAINTS

-- System Roles
CREATE TABLE roles (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL
);

-- Users (IAM)
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id TINYINT NOT NULL,
    kyc_status TINYINT DEFAULT 0 COMMENT '0: Unverified, 1: Pending, 2: Verified, 3: Rejected',
    status TINYINT DEFAULT 1 COMMENT '0: Inactive, 1: Active, 2: Banned',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE user_profiles (
    user_id BIGINT PRIMARY KEY,
    full_name VARCHAR(255),
    avatar_url VARCHAR(500),
    bio TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wallet & Finance
CREATE TABLE wallets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    balance DECIMAL(19,4) DEFAULT 0.0000,
    status TINYINT DEFAULT 1,
    version INT DEFAULT 0 COMMENT 'Optimistic locking',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_wallets_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    wallet_id BIGINT NOT NULL,
    reference_id BIGINT NULL COMMENT 'ID của order/withdrawal',
    type TINYINT NOT NULL COMMENT '1: Nạp, 2: Rút, 3: Thanh toán, 4: Nhận tiền bán, 5: Hoàn tiền',
    amount DECIMAL(19,4) NOT NULL,
    description VARCHAR(255),
    status TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id)
);

-- Marketplace & Catalog
CREATE TABLE stores (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    seller_id BIGINT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_stores_seller FOREIGN KEY (seller_id) REFERENCES users(id)
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    status TINYINT DEFAULT 1,
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id)
);

CREATE TABLE products (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    store_id BIGINT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
    preview_url VARCHAR(500),
    status TINYINT DEFAULT 0 COMMENT '0: Draft, 1: Pending, 2: Active, 3: Rejected, 4: Hidden',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_products_store FOREIGN KEY (store_id) REFERENCES stores(id),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- AI & Content
CREATE TABLE ai_labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE documents (
    product_id BIGINT PRIMARY KEY,
    file_url VARCHAR(500) NOT NULL,
    watermark_url VARCHAR(500) NULL,
    ai_label_id INT NULL,
    ai_score DECIMAL(5,2) NULL,
    CONSTRAINT fk_documents_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_documents_ailabel FOREIGN KEY (ai_label_id) REFERENCES ai_labels(id)
);

-- Orders
CREATE TABLE orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    buyer_id BIGINT NOT NULL,
    total_amount DECIMAL(19,4) NOT NULL,
    status TINYINT DEFAULT 0 COMMENT '0: Pending, 1: Completed, 2: Failed, 3: Refunded',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    price DECIMAL(19,4) NOT NULL,
    CONSTRAINT fk_orderitems_order FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT fk_orderitems_product FOREIGN KEY (product_id) REFERENCES products(id)
);

-- System & Config
CREATE TABLE system_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value VARCHAR(255) NOT NULL,
    description TEXT
);

CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id BIGINT NOT NULL,
    old_value JSON NULL,
    new_value JSON NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. INDEXES
ALTER TABLE products ADD FULLTEXT INDEX ft_idx_products_title_desc (title, description);
CREATE INDEX idx_products_store_status ON products(store_id, status);
CREATE INDEX idx_transactions_wallet_time ON transactions(wallet_id, created_at);

-- 4. VIEWS
DELIMITER $$
CREATE VIEW vw_SellerDashboard AS
SELECT 
    s.id AS store_id,
    s.name AS store_name,
    COUNT(p.id) AS total_products,
    SUM(CASE WHEN o.status = 1 THEN oi.price ELSE 0 END) AS total_revenue
FROM stores s
LEFT JOIN products p ON s.id = p.store_id AND p.deleted_at IS NULL
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id
GROUP BY s.id;
$$
DELIMITER ;

-- 5. TRIGGERS
DELIMITER $$
CREATE TRIGGER trg_after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO user_profiles (user_id) VALUES (NEW.id);
    INSERT INTO wallets (user_id, balance, status) VALUES (NEW.id, 0, 1);
END;
$$
DELIMITER ;

-- 6. STORED PROCEDURES (Ví dụ Thanh toán nguyên tử)
DELIMITER $$
CREATE PROCEDURE sp_ProcessPayment(IN p_buyer_id BIGINT, IN p_order_id BIGINT)
BEGIN
    DECLARE v_total DECIMAL(19,4);
    DECLARE v_buyer_wallet_id BIGINT;
    DECLARE v_buyer_balance DECIMAL(19,4);
    
    -- Xử lý transaction với ROLLBACK an toàn
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;
    
    -- Lấy thông tin order và lock ví buyer
    SELECT total_amount INTO v_total FROM orders WHERE id = p_order_id FOR UPDATE;
    SELECT id, balance INTO v_buyer_wallet_id, v_buyer_balance 
    FROM wallets WHERE user_id = p_buyer_id FOR UPDATE;
    
    IF v_buyer_balance >= v_total THEN
        -- Trừ tiền Buyer
        UPDATE wallets SET balance = balance - v_total, version = version + 1 WHERE id = v_buyer_wallet_id;
        INSERT INTO transactions (wallet_id, reference_id, type, amount, description) 
        VALUES (v_buyer_wallet_id, p_order_id, 3, -v_total, 'Thanh toán đơn hàng');
        
        -- Cập nhật Order status
        UPDATE orders SET status = 1 WHERE id = p_order_id;
        
        -- (Logic cộng tiền Seller sẽ tiếp tục lặp qua order_items ở đây...)
        
        COMMIT;
    ELSE
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Số dư không đủ';
    END IF;
END;
$$
DELIMITER ;

-- 7. SAMPLE DATA
INSERT INTO roles (code, name) VALUES ('ADMIN', 'Administrator'), ('BUYER', 'Buyer'), ('SELLER', 'Seller'), ('CENSOR', 'Censor');
INSERT INTO system_configs (config_key, config_value, description) VALUES ('COMMISSION_RATE', '0.10', 'Phí sàn 10%');
INSERT INTO ai_labels (code, name) VALUES ('HUMAN', '100% Human Written'), ('AI_ASSISTED', 'AI Assisted'), ('AI_GENERATED', 'AI Generated');

-- 8. DỮ LIỆU MẪU DÀNH CHO TEAM PHÁT TRIỂN (SEED DATA)
-- Mật khẩu chung cho tất cả tài khoản dưới đây là: password
-- ==========================================

-- 8.1. Thêm Danh mục (Categories)
INSERT INTO categories (id, parent_id, name, slug, status) VALUES
(1, NULL, 'Công nghệ thông tin', 'cong-nghe-thong-tin', 1),
(2, 1, 'Lập trình Web', 'lap-trinh-web', 1),
(3, 1, 'Cơ sở dữ liệu', 'co-so-du-lieu', 1),
(4, NULL, 'Thiết kế đồ họa', 'thiet-ke-do-hoa', 1),
(5, 4, 'UI/UX Design', 'ui-ux-design', 1);

-- 8.2. Thêm Users mẫu (1 Admin, 2 Seller, 1 Buyer)
-- (Sử dụng INSERT IGNORE để nếu bạn đã tạo tài khoản trùng email thì không báo lỗi)
INSERT IGNORE INTO users (id, email, password_hash, role_id, kyc_status, status) VALUES
(100, 'admin@creono.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 2, 1),
(101, 'seller_dev@creono.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 2, 1),
(102, 'seller_design@creono.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 2, 1),
(103, 'buyer@creono.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 0, 1);

-- 8.3. Cập nhật Profile & Ví cho Users (Do Trigger đã tạo sẵn dòng)
UPDATE user_profiles SET full_name = 'Quản trị viên', bio = 'System Admin' WHERE user_id = 100;
UPDATE user_profiles SET full_name = 'Dev Master', bio = 'Chuyên gia IT' WHERE user_id = 101;
UPDATE user_profiles SET full_name = 'Design Studio', bio = 'Designer 10 năm kinh nghiệm' WHERE user_id = 102;
UPDATE user_profiles SET full_name = 'Khách Mua Hàng', bio = 'Học sinh sinh viên' WHERE user_id = 103;

UPDATE wallets SET balance = 5000000 WHERE user_id = 101; -- Seller có sẵn 5 triệu
UPDATE wallets SET balance = 10000000 WHERE user_id = 103; -- Buyer có sẵn 10 triệu để test mua hàng

-- 8.4. Thêm Cửa hàng (Stores)
INSERT INTO stores (id, seller_id, name, description, status) VALUES
(1, 101, 'IT Master Store', 'Chuyên cung cấp tài liệu lập trình, source code, tài liệu tối ưu hệ thống.', 1),
(2, 102, 'Art & Design', 'Cung cấp Template UI/UX, Mockup chất lượng cao.', 1);

-- 8.5. Thêm Sản phẩm (Products)
-- Sản phẩm 1 & 2 thuộc Store 1 (IT). Sản phẩm 3 thuộc Store 2 (Design)
INSERT INTO products (id, store_id, category_id, title, description, price, preview_url, status) VALUES
(1, 1, 2, 'Khóa học PHP MVC Cơ bản', 'Tài liệu PDF hướng dẫn code PHP thuần chuẩn kiến trúc MVC.', 150000.0000, 'https://via.placeholder.com/400', 2),
(2, 1, 3, 'Kỹ thuật xử lý Cycle Deadlock trong SQL Server', 'Báo cáo chi tiết về tình huống table lock chéo giữa KhachHang và HangThanhVien. Hướng dẫn thiết lập mức độ cô lập (Transaction Isolation) và sử dụng lệnh delay để mô phỏng deadlock.', 350000.0000, 'https://via.placeholder.com/400', 2),
(3, 2, 5, 'Bộ 50 Template Figma Thương mại điện tử', 'Thiết kế chuẩn Mobile app cho ứng dụng mua bán.', 400000.0000, 'https://via.placeholder.com/400', 2);

-- 8.6. Thêm File tài liệu thực tế (Documents)
INSERT INTO documents (product_id, file_url, ai_label_id, ai_score) VALUES
(1, 'https://www.youtube.com/results?search_query=rickroll', 1, 99.5),
(2, 'https://www.youtube.com/results?search_query=sql+deadlock+tutorial', 1, 100.0),
(3, 'https://www.youtube.com/results?search_query=figma+ecommerce+templates', 2, 45.0);

-- 8.7. Thêm Dữ liệu Đơn hàng giả lập để test Dashboard Admin/Seller
INSERT INTO orders (id, buyer_id, total_amount, status) VALUES
(1, 103, 150000.0000, 1),
(2, 103, 350000.0000, 1);

INSERT INTO order_items (id, order_id, product_id, price) VALUES
(1, 1, 1, 150000.0000),
(2, 2, 2, 350000.0000);
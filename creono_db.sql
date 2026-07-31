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
-- ==============================================================================
-- HỆ THỐNG CREONO - DATABASE FULL SCRIPT (VERSION 3)
-- Tối ưu hóa Schema, loại bỏ Alter dư thừa, tinh gọn Stored Procedures.
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Tạo database mới
DROP DATABASE IF EXISTS creono_db;
CREATE DATABASE creono_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE creono_db;

-- ==========================================
-- PHẦN 1: TẠO BẢNG (BASE SCHEMA) - Đã gộp toàn bộ cột tối ưu
-- ==========================================

CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role TINYINT DEFAULT 1 COMMENT '1:Buyer, 2:Seller, 3:Admin, 4:Censor',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE wallets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    balance DECIMAL(19,4) DEFAULT 0.0000,
    frozen_balance DECIMAL(19,4) DEFAULT 0.0000 COMMENT 'Giữ tiền khi đang chờ rút',
    CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE stores (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    status TINYINT DEFAULT 1,
    CONSTRAINT fk_store_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE user_profiles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    full_name VARCHAR(255) NULL,
    avatar_url VARCHAR(500) NULL,
    bio TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE products (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    store_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    price DECIMAL(19,4) NOT NULL,
    preview_url VARCHAR(500),
    rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    status TINYINT DEFAULT 1 COMMENT '1:Pending, 2:Approved, 3:Rejected',
    deleted_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_store FOREIGN KEY (store_id) REFERENCES stores(id)
);

CREATE TABLE transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    wallet_id BIGINT NOT NULL,
    reference_id BIGINT NULL,
    type TINYINT NOT NULL COMMENT '1:Deposit, 2:Withdraw, 3:Payment, 4:Refund, 5:Earning',
    amount DECIMAL(19,4) NOT NULL,
    description VARCHAR(255),
    gateway_transaction_id VARCHAR(255) NULL,
    payment_method VARCHAR(50) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_trans_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id)
);

CREATE TABLE orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    total_amount DECIMAL(19,4) NOT NULL,
    platform_fee DECIMAL(19,4) DEFAULT 0.0000,
    seller_amount DECIMAL(19,4) DEFAULT 0.0000,
    status TINYINT DEFAULT 1 COMMENT '1:Pending, 2:Paid, 3:Cancelled',
    order_expires_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE ai_labels (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    ai_score DECIMAL(5,2) DEFAULT 0.00,
    ai_label_id BIGINT NULL,
    CONSTRAINT fk_doc_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_doc_ailabel FOREIGN KEY (ai_label_id) REFERENCES ai_labels(id)
);

-- ==========================================
-- PHẦN 2: TẠO CÁC BẢNG MỞ RỘNG NGHIỆP VỤ (USE CASES)
-- ==========================================

-- UC03: Quên mật khẩu
CREATE TABLE password_reset_tokens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pwdreset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (token)
);

-- UC07: Xác thực KYC
CREATE TABLE kyc_documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    document_type VARCHAR(50) NOT NULL COMMENT 'ID_CARD, PASSPORT, DRIVER_LICENSE',
    front_image_url VARCHAR(500) NOT NULL,
    back_image_url VARCHAR(500),
    status TINYINT DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
    rejection_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_kyc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- UC11, UC12: Rút tiền
CREATE TABLE withdraw_requests (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    wallet_id BIGINT NOT NULL,
    amount DECIMAL(19,4) NOT NULL,
    bank_name VARCHAR(255) NOT NULL,
    bank_account_number VARCHAR(100) NOT NULL,
    bank_account_name VARCHAR(255) NOT NULL,
    status TINYINT DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
    admin_note TEXT,
    processed_by BIGINT NULL COMMENT 'Admin ID',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_withdraw_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    CONSTRAINT fk_withdraw_admin FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- UC14: Tag & Categorization
CREATE TABLE tags (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE product_tags (
    product_id BIGINT NOT NULL,
    tag_id BIGINT NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    CONSTRAINT fk_pt_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_pt_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- UC15, UC16: Giỏ hàng (Đã loại bỏ ràng buộc UNIQUE ở user_id)
CREATE TABLE carts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE cart_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cartitems_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    CONSTRAINT fk_cartitems_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- UC17: Yêu thích
CREATE TABLE favorites (
    user_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id),
    CONSTRAINT fk_fav_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_fav_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- UC31: Tải tài liệu
CREATE TABLE downloads (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    ip_address VARCHAR(45),
    downloaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dl_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_dl_product FOREIGN KEY (product_id) REFERENCES products(id)
);

-- UC34, UC35: Đánh giá & Bình luận
CREATE TABLE reviews (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    parent_id BIGINT NULL COMMENT 'Dành cho tính năng Reply',
    rating TINYINT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    status TINYINT DEFAULT 1 COMMENT '1: Visible, 0: Hidden',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_reviews_parent FOREIGN KEY (parent_id) REFERENCES reviews(id) ON DELETE CASCADE
);

-- UC36, UC37, UC38: Báo cáo vi phạm
CREATE TABLE reports (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    reporter_id BIGINT NOT NULL,
    target_type VARCHAR(50) NOT NULL COMMENT 'PRODUCT, STORE, USER, REVIEW',
    target_id BIGINT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    details TEXT,
    status TINYINT DEFAULT 1 COMMENT '1: Pending, 2: Investigating, 3: Resolved, 4: Dismissed',
    resolved_by BIGINT NULL COMMENT 'Admin ID',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id)
);

-- UC27: Kháng cáo AI
CREATE TABLE ai_appeals (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    seller_id BIGINT NOT NULL,
    reason TEXT NOT NULL,
    evidence_url VARCHAR(500),
    status TINYINT DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
    processed_by BIGINT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_appeal_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_appeal_seller FOREIGN KEY (seller_id) REFERENCES users(id),
    CONSTRAINT fk_appeal_admin FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- UC43: Duyệt tài liệu
CREATE TABLE product_approvals (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    censor_id BIGINT NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'APPROVE, REJECT',
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_approval_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_approval_censor FOREIGN KEY (censor_id) REFERENCES users(id)
);

-- ==========================================
-- PHẦN 3: INDEXES (Tối ưu truy vấn)
-- ==========================================
CREATE INDEX idx_pwd_reset_token ON password_reset_tokens(token);
CREATE INDEX idx_reviews_product_rating ON reviews(product_id, rating);
CREATE INDEX idx_reports_target ON reports(target_type, target_id);
CREATE INDEX idx_downloads_user_prod ON downloads(user_id, product_id);

-- ==========================================
-- PHẦN 4: TRIGGERS (Tự động hóa)
-- ==========================================
DELIMITER $$

CREATE TRIGGER trg_after_download
AFTER INSERT ON downloads
FOR EACH ROW
BEGIN
    UPDATE products 
    SET download_count = download_count + 1 
    WHERE id = NEW.product_id;
END$$

CREATE TRIGGER trg_after_review_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    IF NEW.rating IS NOT NULL THEN
        UPDATE products 
        SET 
            rating = ROUND(((rating * review_count) + NEW.rating) / (review_count + 1), 2),
            review_count = review_count + 1
        WHERE id = NEW.product_id;
    END IF;
END$$

DELIMITER ;

-- ==========================================
-- PHẦN 5: STORED PROCEDURES (Bảo toàn dòng tiền)
-- ==========================================
DELIMITER $$

CREATE PROCEDURE sp_RequestWithdrawal(
    IN p_wallet_id BIGINT, 
    IN p_amount DECIMAL(19,4),
    IN p_bank_name VARCHAR(255),
    IN p_bank_acc_num VARCHAR(100),
    IN p_bank_acc_name VARCHAR(255)
)
BEGIN
    DECLARE v_balance DECIMAL(19,4);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;
    
    -- Khóa bi quan dòng ví đang thao tác
    SELECT balance INTO v_balance FROM wallets WHERE id = p_wallet_id FOR UPDATE;
    
    IF v_balance >= p_amount THEN
        -- Đẩy tiền từ balance sang frozen_balance
        UPDATE wallets 
        SET balance = balance - p_amount, 
            frozen_balance = frozen_balance + p_amount
        WHERE id = p_wallet_id;
        
        INSERT INTO withdraw_requests (wallet_id, amount, bank_name, bank_account_number, bank_account_name, status)
        VALUES (p_wallet_id, p_amount, p_bank_name, p_bank_acc_num, p_bank_acc_name, 1);
        
        COMMIT;
    ELSE
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Số dư không đủ để rút tiền';
    END IF;
END$$

CREATE PROCEDURE sp_ApproveWithdrawal(
    IN p_request_id BIGINT,
    IN p_admin_id BIGINT
)
BEGIN
    DECLARE v_wallet_id BIGINT;
    DECLARE v_amount DECIMAL(19,4);
    DECLARE v_status TINYINT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;
    
    -- Khóa dòng request để xử lý
    SELECT wallet_id, amount, status INTO v_wallet_id, v_amount, v_status 
    FROM withdraw_requests WHERE id = p_request_id FOR UPDATE;
    
    IF v_status = 1 THEN 
        -- Trừ hẳn tiền đóng băng
        UPDATE wallets 
        SET frozen_balance = frozen_balance - v_amount
        WHERE id = v_wallet_id;
        
        UPDATE withdraw_requests 
        SET status = 2, processed_by = p_admin_id 
        WHERE id = p_request_id;
        
        INSERT INTO transactions (wallet_id, reference_id, type, amount, description) 
        VALUES (v_wallet_id, p_request_id, 2, -v_amount, 'Rút tiền thành công');
        
        COMMIT;
    ELSE
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Yêu cầu không hợp lệ hoặc đã được xử lý';
    END IF;
END$$

DELIMITER ;

-- ==========================================
-- PHẦN 6: VIEWS (Hỗ trợ báo cáo Dashboard)
-- ==========================================

CREATE OR REPLACE VIEW vw_TopProducts AS
SELECT 
    p.id, p.title, p.price, p.rating, p.review_count, p.download_count,
    s.name AS store_name
FROM products p
JOIN stores s ON p.store_id = s.id
WHERE p.status = 2 AND p.deleted_at IS NULL
ORDER BY p.download_count DESC, p.rating DESC;

CREATE OR REPLACE VIEW vw_PendingApprovals AS
SELECT 
    p.id AS product_id, p.title, s.name AS store_name, 
    d.ai_score, al.name AS ai_label_name, p.created_at
FROM products p
JOIN stores s ON p.store_id = s.id
JOIN documents d ON p.id = d.product_id
LEFT JOIN ai_labels al ON d.ai_label_id = al.id
WHERE p.status = 1;

CREATE OR REPLACE VIEW vw_PendingWithdrawals AS
SELECT 
    w.id AS request_id, u.email AS seller_email, w.amount, 
    w.bank_name, w.bank_account_number, w.created_at
FROM withdraw_requests w
JOIN wallets wal ON w.wallet_id = wal.id
JOIN users u ON wal.user_id = u.id
WHERE w.status = 1;

-- ==========================================
-- PHẦN 7: DỮ LIỆU MẪU (SEED DATA)
-- ==========================================

-- 1. Insert Users 
-- Mật khẩu của mọi tài khoản đều là: 123456
INSERT INTO users (name, email, password, role) VALUES 
('System Admin', 'admin@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 3),
('Seller One', 'seller1@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2),
('Seller Two', 'seller2@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2),
('Buyer John', 'buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1);

-- 2. Insert Wallets (Cấp ví cho User & nạp sẵn tiền giả định)
INSERT INTO wallets (user_id, balance, frozen_balance) VALUES 
(1, 0, 0), -- Admin
(2, 5000000.00, 0), -- Seller 1
(3, 150000.00, 0), -- Seller 2
(4, 1000000.00, 0); -- Buyer

-- 3. Insert Stores
INSERT INTO stores (user_id, name, status) VALUES 
(2, 'Code Master Store', 1),
(3, 'Design UI/UX Hub', 1);

-- 4. Insert Products
INSERT INTO products (store_id, title, price, status) VALUES 
(1, 'Source Code Quản lý Khách sạn PHP', 500000.00, 2), -- Đã duyệt
(1, 'Template Admin Dashboard VueJS', 200000.00, 1), -- Chờ duyệt
(2, 'Bộ icon Figma E-Commerce 2024', 150000.00, 2); -- Đã duyệt

-- 5. Insert AI Labels & Documents
INSERT INTO ai_labels (name) VALUES ('Human Written'), ('AI Generated'), ('Mixed');
INSERT INTO documents (product_id, file_url, ai_score, ai_label_id) VALUES 
(1, 'https://s3.aws.com/files/hotel_php.zip', 5.5, 1),
(2, 'https://s3.aws.com/files/vue_admin.zip', 12.0, 1),
(3, 'https://s3.aws.com/files/figma_icons.zip', 95.5, 2);

-- 6. Insert Tags
INSERT INTO tags (name, slug) VALUES ('PHP', 'php'), ('VueJS', 'vuejs'), ('Figma', 'figma');
INSERT INTO product_tags (product_id, tag_id) VALUES (1, 1), (2, 2), (3, 3);

-- 7. Test Triggers: Insert Review
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES 
(1, 4, 5, 'Source code rất tốt, dễ hiểu!'),
(3, 4, 4, 'Icon đẹp nhưng hơi ít màu.');

-- 8. Test Triggers: Insert Download
INSERT INTO downloads (user_id, product_id, ip_address) VALUES 
(4, 1, '192.168.1.1'),
(4, 1, '192.168.1.1'), 
(4, 3, '192.168.1.5');

-- 9. Test Stored Procedure: Tạo 1 yêu cầu rút tiền Pending cho Seller 1
CALL sp_RequestWithdrawal(2, 2000000.00, 'Vietcombank', '0123456789', 'SELLER ONE');

SET FOREIGN_KEY_CHECKS = 1;
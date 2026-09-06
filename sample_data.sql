-- ==============================================================================
-- HỆ THỐNG CREONO - DATABASE FULL SCRIPT (CẬP NHẬT ĐỒNG BỘ SCHEMA & SEED DATA)
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tự động tạo và chọn Database
DROP DATABASE IF EXISTS creono_db;
CREATE DATABASE creono_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE creono_db;

-- ==========================================
-- PHẦN 1: TẠO BẢNG & RÀNG BUỘC (BASE SCHEMA)
-- ==========================================

CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role TINYINT DEFAULT 1 COMMENT '1:Buyer, 2:Seller, 3:Admin, 4:Censor',
    is_locked TINYINT DEFAULT 0 COMMENT '0:Active, 1:Locked',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_profiles (
    user_id BIGINT PRIMARY KEY,
    full_name VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    avatar_url VARCHAR(500) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_profile FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wallets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    balance DECIMAL(19,4) DEFAULT 0.0000,
    frozen_balance DECIMAL(19,4) DEFAULT 0.0000 COMMENT 'Giữ tiền khi đang chờ rút',
    CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stores (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    status TINYINT DEFAULT 1,
    description TEXT DEFAULT NULL,
    logo_url VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    bank_name VARCHAR(100) DEFAULT NULL,
    bank_account_number VARCHAR(50) DEFAULT NULL,
    bank_account_name VARCHAR(100) DEFAULT NULL,
    slug VARCHAR(255) DEFAULT NULL UNIQUE,
    CONSTRAINT fk_store_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    store_id BIGINT NOT NULL,
    category_id BIGINT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(19,4) NOT NULL,
    preview_url VARCHAR(500) DEFAULT NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    status TINYINT DEFAULT 1 COMMENT '1:Pending, 2:Approved, 3:Rejected',
    deleted_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_store FOREIGN KEY (store_id) REFERENCES stores(id),
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_stats (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL UNIQUE,
    view_count INT DEFAULT 0,
    cart_count INT DEFAULT 0,
    purchase_count INT DEFAULT 0,
    last_viewed_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_stats_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    wallet_id BIGINT NOT NULL,
    reference_id BIGINT DEFAULT NULL,
    type TINYINT NOT NULL COMMENT '1:Deposit, 2:Withdraw, 3:Payment, 4:Refund, 5:Earning',
    amount DECIMAL(19,4) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    gateway_transaction_id VARCHAR(255) DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_trans_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL,
    user_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    total_amount DECIMAL(19,4) NOT NULL,
    platform_fee DECIMAL(19,4) DEFAULT 0.0000,
    seller_amount DECIMAL(19,4) DEFAULT 0.0000,
    status TINYINT DEFAULT 1 COMMENT '1:Pending, 2:Paid, 3:Cancelled',
    order_expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_order_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(19,4) NOT NULL,
    subtotal DECIMAL(19,4) NOT NULL,
    platform_fee DECIMAL(19,4) DEFAULT 0.0000,
    seller_amount DECIMAL(19,4) DEFAULT 0.0000,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ai_labels (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    ai_score DECIMAL(5,2) DEFAULT 0.00,
    ai_label_id BIGINT DEFAULT NULL,
    CONSTRAINT fk_doc_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_doc_ailabel FOREIGN KEY (ai_label_id) REFERENCES ai_labels(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_tags (
    product_id BIGINT NOT NULL,
    tag_id BIGINT NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    CONSTRAINT fk_pt_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_pt_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    content TEXT NOT NULL,
    rating TINYINT DEFAULT 5 CHECK (rating >= 1 and rating <= 5),
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_testimonial_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pwdreset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kyc_documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    document_type VARCHAR(50) NOT NULL COMMENT 'ID_CARD, PASSPORT, DRIVER_LICENSE',
    front_image_url VARCHAR(500) NOT NULL,
    back_image_url VARCHAR(500) DEFAULT NULL,
    status TINYINT DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
    rejection_reason TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_kyc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE withdraw_requests (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    wallet_id BIGINT NOT NULL,
    amount DECIMAL(19,4) NOT NULL,
    bank_name VARCHAR(255) NOT NULL,
    bank_account_number VARCHAR(100) NOT NULL,
    bank_account_name VARCHAR(255) NOT NULL,
    status TINYINT DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
    admin_note TEXT DEFAULT NULL,
    processed_by BIGINT DEFAULT NULL COMMENT 'Admin ID',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_withdraw_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    CONSTRAINT fk_withdraw_admin FOREIGN KEY (processed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE carts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cartitems_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    CONSTRAINT fk_cartitems_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE favorites (
    user_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id),
    CONSTRAINT fk_fav_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_fav_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE downloads (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    downloaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dl_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_dl_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    parent_id BIGINT DEFAULT NULL COMMENT 'Dành cho tính năng Reply',
    rating TINYINT DEFAULT NULL CHECK (rating >= 1 and rating <= 5),
    comment TEXT NOT NULL,
    status TINYINT DEFAULT 1 COMMENT '1: Visible, 0: Hidden',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_reviews_parent FOREIGN KEY (parent_id) REFERENCES reviews(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reports (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    reporter_id BIGINT NOT NULL,
    report_type VARCHAR(20) NOT NULL DEFAULT 'COMPLAINT' COMMENT 'COMPLAINT: Khiếu nại (UC37), PLAGIARISM: Tố cáo đạo nhái (UC38)',
    target_type VARCHAR(50) NOT NULL COMMENT 'PRODUCT, STORE, USER, REVIEW',
    target_id BIGINT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    details TEXT DEFAULT NULL,
    status TINYINT DEFAULT 1 COMMENT '1: Pending, 2: Investigating, 3: Resolved, 4: Dismissed',
    resolved_by BIGINT DEFAULT NULL COMMENT 'Admin ID',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ai_appeals (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    seller_id BIGINT NOT NULL,
    reason TEXT NOT NULL,
    evidence_url VARCHAR(500) DEFAULT NULL,
    status TINYINT DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
    processed_by BIGINT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_appeal_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_appeal_seller FOREIGN KEY (seller_id) REFERENCES users(id),
    CONSTRAINT fk_appeal_admin FOREIGN KEY (processed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_approvals (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    censor_id BIGINT NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'APPROVE, REJECT',
    note TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_approval_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_approval_censor FOREIGN KEY (censor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BẢNG CẤU HÌNH HỆ THỐNG
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- PHẦN 2: CHỈ MỤC (INDEXES TỐI ƯU HÓA)
-- ==========================================
CREATE INDEX idx_downloads_user_prod ON downloads(user_id, product_id);
CREATE INDEX idx_pwd_reset_token ON password_reset_tokens(token);
CREATE INDEX idx_reports_target ON reports(target_type, target_id);
CREATE INDEX idx_reviews_product_rating ON reviews(product_id, rating);
CREATE INDEX idx_order_product ON orders(product_id);
CREATE INDEX idx_products_status_created ON products(status, created_at DESC);
CREATE INDEX idx_products_store_status ON products(store_id, status);
CREATE INDEX idx_stores_slug ON stores(slug);

-- ==========================================
-- PHẦN 3: TRIGGERS (TỰ ĐỘNG HÓA)
-- ==========================================
DELIMITER $$

CREATE TRIGGER trg_after_download 
AFTER INSERT ON downloads FOR EACH ROW 
BEGIN
    UPDATE products SET download_count = download_count + 1 WHERE id = NEW.product_id;
END$$

CREATE TRIGGER trg_after_review_insert 
AFTER INSERT ON reviews FOR EACH ROW 
BEGIN
    IF NEW.rating IS NOT NULL THEN
        UPDATE products 
        SET rating = ROUND(((rating * review_count) + NEW.rating) / (review_count + 1), 2),
            review_count = review_count + 1
        WHERE id = NEW.product_id;
    END IF;
END$$

DELIMITER ;

-- ==========================================
-- PHẦN 4: STORED PROCEDURES (BẢO TOÀN DÒNG TIỀN)
-- ==========================================
DELIMITER $$

CREATE PROCEDURE sp_RequestWithdrawal(IN p_wallet_id BIGINT, IN p_amount DECIMAL(19,4), IN p_bank_name VARCHAR(255), IN p_bank_acc_num VARCHAR(100), IN p_bank_acc_name VARCHAR(255))
BEGIN
    DECLARE v_balance DECIMAL(19,4);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; END;
    START TRANSACTION;
    SELECT balance INTO v_balance FROM wallets WHERE id = p_wallet_id FOR UPDATE;
    IF v_balance >= p_amount THEN
        UPDATE wallets SET balance = balance - p_amount, frozen_balance = frozen_balance + p_amount WHERE id = p_wallet_id;
        INSERT INTO withdraw_requests (wallet_id, amount, bank_name, bank_account_number, bank_account_name, status) VALUES (p_wallet_id, p_amount, p_bank_name, p_bank_acc_num, p_bank_acc_name, 1);
        COMMIT;
    ELSE
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Số dư không đủ để rút tiền';
    END IF;
END$$

CREATE PROCEDURE sp_ApproveWithdrawal(IN p_request_id BIGINT, IN p_admin_id BIGINT)
BEGIN
    DECLARE v_wallet_id BIGINT;
    DECLARE v_amount DECIMAL(19,4);
    DECLARE v_status TINYINT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; END;
    START TRANSACTION;
    SELECT wallet_id, amount, status INTO v_wallet_id, v_amount, v_status FROM withdraw_requests WHERE id = p_request_id FOR UPDATE;
    IF v_status = 1 THEN 
        UPDATE wallets SET frozen_balance = frozen_balance - v_amount WHERE id = v_wallet_id;
        UPDATE withdraw_requests SET status = 2, processed_by = p_admin_id WHERE id = p_request_id;
        INSERT INTO transactions (wallet_id, reference_id, type, amount, description) VALUES (v_wallet_id, p_request_id, 2, -v_amount, 'Rút tiền thành công');
        COMMIT;
    ELSE
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Yêu cầu không hợp lệ hoặc đã được xử lý';
    END IF;
END$$

DELIMITER ;

-- ==========================================
-- PHẦN 5: VIEWS (BÁO CÁO DOANH THU & DASHBOARD)
-- ==========================================

CREATE OR REPLACE VIEW vw_pendingapprovals AS 
SELECT p.id AS product_id, p.title AS title, s.name AS store_name, d.ai_score AS ai_score, al.name AS ai_label_name, p.created_at AS created_at 
FROM products p 
JOIN stores s ON p.store_id = s.id 
JOIN documents d ON p.id = d.product_id 
LEFT JOIN ai_labels al ON d.ai_label_id = al.id 
WHERE p.status = 1;

CREATE OR REPLACE VIEW vw_pendingwithdrawals AS 
SELECT w.id AS request_id, u.email AS seller_email, w.amount AS amount, w.bank_name AS bank_name, w.bank_account_number AS bank_account_number, w.created_at AS created_at 
FROM withdraw_requests w 
JOIN wallets wal ON w.wallet_id = wal.id 
JOIN users u ON wal.user_id = u.id 
WHERE w.status = 1;

CREATE OR REPLACE VIEW vw_topproducts AS 
SELECT p.id AS id, p.title AS title, p.price AS price, p.rating AS rating, p.review_count AS review_count, p.download_count AS download_count, s.name AS store_name 
FROM products p 
JOIN stores s ON p.store_id = s.id 
WHERE p.status = 2 AND p.deleted_at IS NULL 
ORDER BY p.download_count DESC, p.rating DESC;

CREATE OR REPLACE VIEW vw_pending_orders_by_seller AS 
SELECT s.user_id AS seller_id, o.id AS order_id, o.order_number AS order_number, o.total_amount AS total_amount, o.created_at AS created_at, p.title AS product_title, u.name AS buyer_name, u.email AS buyer_email 
FROM orders o 
JOIN products p ON o.product_id = p.id 
JOIN stores s ON p.store_id = s.id 
JOIN users u ON o.user_id = u.id 
WHERE o.status = 1 
ORDER BY o.created_at DESC;

CREATE OR REPLACE VIEW vw_seller_revenue AS 
SELECT s.user_id AS seller_id, s.id AS store_id, s.name AS store_name, COUNT(DISTINCT o.id) AS total_orders, COALESCE(SUM(o.seller_amount),0) AS total_revenue, COALESCE(SUM(o.platform_fee),0) AS total_fee, COALESCE(AVG(p.rating),0) AS avg_rating, COUNT(DISTINCT p.id) AS total_products, COUNT(DISTINCT o.user_id) AS total_customers 
FROM stores s 
LEFT JOIN products p ON p.store_id = s.id AND p.deleted_at IS NULL 
LEFT JOIN orders o ON o.product_id = p.id AND o.status = 2 
GROUP BY s.id, s.user_id, s.name;

CREATE OR REPLACE VIEW vw_top_products_by_seller AS 
SELECT s.user_id AS seller_id, p.id AS product_id, p.title AS title, p.price AS price, p.rating AS rating, p.download_count AS total_sales, p.review_count AS review_count, ps.view_count AS view_count, ps.cart_count AS cart_count 
FROM products p 
JOIN stores s ON p.store_id = s.id 
LEFT JOIN product_stats ps ON p.id = ps.product_id 
WHERE p.deleted_at IS NULL 
ORDER BY p.download_count DESC, p.rating DESC;

-- ==========================================
-- PHẦN 6: SEED DATA (DỮ LIỆU MẪU ĐỒNG BỘ ĐẦY ĐỦ)
-- ==========================================

-- 1. Users (Bao gồm đầy đủ role, is_locked)
INSERT INTO users (id, name, email, password, role, is_locked, created_at) VALUES
(1, 'System Admin', 'admin@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 3, 0, '2026-08-01 14:03:20'),
(2, 'Seller One', 'seller1@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-08-01 14:03:20'),
(3, 'Seller Two', 'seller2@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-08-01 14:03:20'),
(4, 'Buyer John', 'buyer@mail.com', '$2y$10$swjH6f9z.3iCc465GO8xCeDU.UzTGyRocTFmaKiFT2CNtoscpJuxK', 2, 0, '2026-08-01 14:03:20'),
(5, 'Nguyễn Văn A', 'test@example.com', '$2y$10$Pddv8oOUrMPDKhzwDcEdV.bYib67eJn0wnIcpeuN08RvonPGi7MNa', 1, 0, '2026-08-02 11:45:59'),
(6, 'Test User', 'test+2aff08f0fd034765b9e74c0920fba66f@creono.test', '$2y$10$eomKcyX12hFRz7vavFk4i./VFXmVuRHl3Y6edZh4ezEFTL0cCX1a.', 1, 0, '2026-08-02 11:55:32'),
(100, 'Nguyễn Vũ Thuận', 'thuan.admin@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 3, 0, '2026-08-02 16:59:59'),
(101, 'Nguyễn Viết Thịnh', 'thinh.seller@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjLinY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-08-02 16:59:59'),
(102, 'Trần Tú Tâm', 'tam.seller@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-08-02 16:59:59'),
(103, 'Lê Quang Tân', 'tan.buyer@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-08-02 16:59:59'),
(104, 'Phạm Nguyễn', 'pham.buyer@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-08-02 16:59:59'),
(105, 'Bá Văn Khí', 'bakhi@mail.cc', '$2y$10$SH3YKI9CgI5fKGjLpmYL5uUjUsxybVX/Tg9ZkwnMznYmzfYh0OKNG', 2, 0, '2026-09-02 16:22:48'),
(106, 'Minh', 'minh@mail.com', '$2y$10$yVzmbrBz9f3vq0CWQG722uxac4yyCTkr/dNo.n1iFYhE0MgadJ6dG', 2, 0, '2026-09-02 16:37:05'),
(107, 'Bá Văn Khí', 'bakhi@mail.com', '$2y$10$rMYiVNM23ycKzpz0cgrNjOHcowL/8uAgj8d3KNKdz6MFmWfAFwNru', 2, 0, '2026-09-05 20:25:52'),
(108, 'Võ Bá Khí', 'bakhivo@gmail.cc', '$2y$10$VQtpOjxIp9891a5VIQoUlelD9cOD/J1jGXQ6770tGqN/2.F9ldFhi', 2, 0, '2026-09-05 20:54:45');

-- 2. User Profiles
INSERT INTO user_profiles (user_id, full_name, bio, avatar_url, created_at, updated_at) VALUES
(1, 'Sus admin', 'adadaada', '/uploads/avatars/1785903198_6a72b85ec1057.jpg', '2026-08-02 12:00:52', '2026-08-05 11:13:37'),
(2, 'Seller One', 'tui nà seo lơ', NULL, '2026-08-02 12:00:52', '2026-08-05 11:31:19'),
(3, 'Seller Two', NULL, NULL, '2026-08-02 12:00:52', '2026-08-02 12:00:52'),
(4, 'Buyer John', NULL, NULL, '2026-08-02 12:00:52', '2026-08-02 12:00:52'),
(5, 'Nguyễn Văn A', NULL, NULL, '2026-08-02 12:00:52', '2026-08-02 12:00:52'),
(6, 'Test User', NULL, NULL, '2026-08-02 12:00:52', '2026-08-02 12:00:52'),
(106, 'Minh', 'mình mính mình minh
', NULL, '2026-09-02 16:45:47', '2026-09-02 16:45:47'),
(108, 'Võ Bá Khí', 'dădawdawda', '/uploads/avatars/1788616706_6a9c2002238e7.jpg', '2026-09-05 20:58:04', '2026-09-05 20:58:26');

-- 3. Wallets
INSERT INTO wallets (id, user_id, balance, frozen_balance) VALUES
(1, 1, 0.0000, 0.0000), (2, 2, 3000000.0000, 2000000.0000), (3, 3, 150000.0000, 0.0000), (4, 4, 1000000.0000, 0.0000),
(5, 5, 0.0000, 0.0000), (6, 6, 0.0000, 0.0000), (100, 100, 99999999.0000, 0.0000), (101, 101, 15000000.0000, 200000.0000),
(102, 102, 5000000.0000, 0.0000), (103, 103, 1500000.0000, 0.0000), (104, 104, 500000.0000, 0.0000), (105, 105, 0.0000, 0.0000),
(106, 106, 0.0000, 0.0000), (107, 107, 0.0000, 0.0000), (108, 108, 0.0000, 0.0000);

-- 4. Stores (Đầy đủ tất cả các cột mới: description, logo_url, phone, address, ngân hàng, slug)
INSERT INTO stores (id, user_id, name, status, description, logo_url, phone, address, bank_name, bank_account_number, bank_account_name, slug) VALUES
(1, 2, 'Code Master Store', 2, 'abc', '', '0900000000', 'Đang cập nhật địa chỉ', 'Chưa thiết lập', '0000000000', 'CHƯA CẬP NHẬT', 'code-master-store'),
(2, 3, 'Design UI/UX Hub', 2, 'Chưa có mô tả cho cửa hàng này', '', '0900000000', 'Đang cập nhật địa chỉ', 'Chưa thiết lập', '0000000000', 'CHƯA CẬP NHẬT', 'design-ui-ux-hub'),
(100, 101, 'Thuật Toán & Database Hub', 2, 'Chưa có mô tả cho cửa hàng này', '', '0900000000', 'Đang cập nhật địa chỉ', 'Chưa thiết lập', '0000000000', 'CHƯA CẬP NHẬT', 'thuat-toan-database-hub'),
(101, 102, 'Underground Rap Beats', 2, 'Chưa có mô tả cho cửa hàng này', '', '0900000000', 'Đang cập nhật địa chỉ', 'Chưa thiết lập', '0000000000', 'CHƯA CẬP NHẬT', 'underground-rap-beats'),
(102, 100, 'Modding & Hardware Pro', 2, 'Chưa có mô tả cho cửa hàng này', '', '0900000000', 'Đang cập nhật địa chỉ', 'Chưa thiết lập', '0000000000', 'Nguyễn Bá Khí', 'modding-hardware-pro'),
(103, 4, 'Cửa Hàng Thái Dúi', 2, 'àhshfjkaofjioshfdjfiehfadafjeshui', '', '0123456789', '120 Yen Lang', 'Mờ Bê Banh', '123456789', 'Nguyễn Thái Dúi', 'cua-hang-thai-dui'),
(104, 105, 'Cửa Hàng Bá Khí', 2, 'Bá Khí Chấm Com', '', '036363636', '36 Yen Lang', '123456', '123456', '23', 'ca-hng-b-kh'),
(105, 106, 'hadƯU', 2, 'ADWHH', '', '15562365', '413441', '4451144114', '414845121548', '415487845121', 'hadu'),
(106, 107, 'Cửa Hàng Thái Dúi 1', 2, 'Bán đồ thái', '/uploads/stores/1788614784_6a9c1880b5a1e.jpg', '0123456789', '120 Yen Lang', 'dâdaw', '123456', 'Nguyễn Thái Dúi', 'ca-hng-thi-di-1'),
(107, 108, 'Bá Khí Phẩm', 2, 'Nơi bán hàng bá khí', '/uploads/stores/1788616544_6a9c1f609f89e.jpg', '0123456789', '123456', 'dâdaw', '45625562', 'jafuhbrjigeafhubjwfhajfhajujwfabjfjfjigehsjnawfjigeshjnakgjihsj', 'b-kh-phm');

-- 5. Categories
INSERT INTO categories (id, name, slug, description, sort_order, created_at) VALUES
(1, 'Lập trình', 'lap-trinh', 'Tài liệu lập trình, mã nguồn, framework', 1, '2026-08-05 09:50:11'),
(2, 'Thiết kế', 'thiet-ke', 'UI/UX, Figma, Photoshop, Illustrator', 2, '2026-08-05 09:50:11'),
(3, 'Kinh doanh', 'kinh-doanh', 'Marketing, E-commerce, Khởi nghiệp', 3, '2026-08-05 09:50:11'),
(4, 'Học thuật', 'hoc-thuat', 'Luận văn, Báo cáo, Giáo trình', 4, '2026-08-05 09:50:11'),
(5, 'Template', 'template', 'Website template, Admin dashboard', 5, '2026-08-05 09:50:11'),
(6, 'E-commerce', 'ecommerce', 'Giải pháp bán hàng online', 6, '2026-08-05 09:50:11');

-- 6. Products (Bao gồm cả các sản phẩm mới thêm #116, #117)
INSERT INTO products (id, store_id, title, description, price, preview_url, rating, review_count, download_count, status, deleted_at, created_at, category_id) VALUES
(1, 1, 'Source Code Quản lý Khách sạn PHP', 'Mã nguồn quản lý khách sạn chuyên nghiệp với PHP. Quản lý phòng, đặt phòng, thanh toán và báo cáo.', 500000.0000, NULL, 5.00, 2, 4, 2, NULL, '2026-08-01 14:03:21', 1),
(2, 1, 'Template Admin Dashboard VueJS', 'Dashboard admin VueJS với các component tái sử dụng, tối ưu hiệu suất.', 200000.0000, NULL, 0.00, 0, 0, 1, NULL, '2026-08-01 14:03:21', 5),
(3, 2, 'Bộ icon Figma E-Commerce 2024', 'Bộ icon Figma đa dạng cho thiết kế E-commerce, bao gồm 200+ icon.', 150000.0000, NULL, 4.00, 2, 2, 2, NULL, '2026-08-01 14:03:21', 2),
(100, 100, 'Source Code Quản Lý Cây Gia Phả (C++)', NULL, 150000.0000, NULL, 4.82, 27, 150, 2, NULL, '2026-08-02 17:00:00', 1),
(101, 100, 'Đồ án Quản lý Nhà hàng (Java & SQL Server)', NULL, 250000.0000, NULL, 5.00, 44, 210, 2, NULL, '2026-08-02 17:00:00', 1),
(102, 100, 'Báo cáo thuật toán TSP - Traveling Salesman Problem (Full File LaTeX)', NULL, 80000.0000, NULL, 4.50, 12, 60, 2, NULL, '2026-08-02 17:00:00', NULL),
(103, 100, 'Template Dashboard Quản trị bằng Next.js', NULL, 300000.0000, NULL, 4.90, 88, 500, 2, NULL, '2026-08-02 17:00:00', 5),
(104, 101, 'Beat Rap Melody (Style Hieuthuhai, Robber & Gill)', NULL, 500000.0000, NULL, 5.00, 12, 30, 2, NULL, '2026-08-02 17:00:00', NULL),
(105, 102, 'Ebook: Cẩm nang độ IC và Nhông Sên Dĩa cho Honda Wave Alpha 100', NULL, 99000.0000, NULL, 4.68, 58, 320, 2, NULL, '2026-08-02 17:00:00', NULL),
(106, 102, 'Bản Mod Firmware RCMloader cho Nintendo Switch 2026', NULL, 120000.0000, NULL, 4.60, 18, 90, 2, NULL, '2026-08-02 17:00:00', NULL),
(107, 102, 'Bí kíp tối ưu cấu hình ZingSpeed Mobile (Tặng kèm file Config)', NULL, 50000.0000, NULL, 4.30, 8, 45, 2, NULL, '2026-08-02 17:00:00', NULL),
(108, 1, 'Source Code Quản lý Bán hàng PHP', 'Mã nguồn hệ thống quản lý bán hàng hoàn chỉnh với PHP và MySQL. Bao gồm quản lý sản phẩm, đơn hàng, khách hàng và báo cáo doanh thu.', 350000.0000, NULL, 4.80, 12, 45, 2, NULL, '2026-08-05 09:50:11', 6),
(109, 1, 'Template Admin Dashboard React', 'Dashboard Admin hiện đại với React và Tailwind CSS. Tối ưu cho quản lý dữ liệu và phân tích.', 250000.0000, NULL, 4.50, 8, 32, 2, NULL, '2026-08-05 09:50:11', 5),
(110, 2, 'Bộ UI Kit Mobile App Figma', 'Bộ giao diện mobile app đẹp mắt cho Figma. Bao gồm 50+ màn hình thiết kế sẵn.', 180000.0000, NULL, 4.91, 16, 67, 2, NULL, '2026-08-05 09:50:11', 2),
(111, 2, 'Ebook Marketing Digital 2024', 'Hướng dẫn chiến lược marketing digital hiệu quả. Cập nhật xu hướng mới nhất 2024.', 120000.0000, NULL, 4.30, 6, 23, 2, NULL, '2026-08-05 09:50:11', 3),
(112, 1, 'Bộ Icon Vector 1000+', 'Bộ icon vector chất lượng cao cho thiết kế. Định dạng SVG, PNG, AI, EPS.', 95000.0000, NULL, 4.60, 9, 89, 2, NULL, '2026-08-05 09:50:11', 2),
(113, 2, 'Source Code Website Bán Hàng Laravel', 'Website bán hàng hoàn chỉnh với Laravel 10 và Livewire. Tối ưu SEO và hiệu suất cao.', 450000.0000, NULL, 4.90, 20, 56, 2, NULL, '2026-08-05 09:50:11', 6),
(114, 1, 'Template Portfolio UI/UX', 'Template portfolio hiện đại cho designer và developer. Responsive và animation mượt mà.', 150000.0000, NULL, 4.40, 7, 34, 2, NULL, '2026-08-05 09:50:11', 5),
(115, 2, 'Khóa học SEO từ A-Z', 'Tài liệu hướng dẫn SEO toàn diện từ cơ bản đến nâng cao. Cập nhật thuật toán Google mới nhất.', 200000.0000, NULL, 4.70, 11, 41, 2, NULL, '2026-08-05 09:50:11', 3),
(116, 1, 'da', 'dâdad', 248000.0000, '/uploads/products/images/1788615151_6a9c19efb8d2c.jpg', 5.00, 1, 0, 2, NULL, '2026-09-05 20:32:31', 4),
(117, 107, 'dâdwad', 'đưaă', 36000000000.0000, '/uploads/products/images/1788616741_6a9c2025bd6a7.jpg', 5.00, 2, 0, 2, NULL, '2026-09-05 20:59:01', 2);

-- 7. Product Stats
INSERT INTO product_stats (id, product_id, view_count, cart_count, purchase_count, last_viewed_at, updated_at) VALUES
(1, 1, 1250, 45, 2, '2026-08-05 09:00:00', '2026-08-05 11:27:49'), (2, 2, 890, 23, 0, '2026-08-04 15:30:00', '2026-08-05 11:27:49'),
(3, 3, 2100, 67, 1, '2026-08-05 08:45:00', '2026-08-05 11:27:49'), (4, 100, 560, 12, 150, '2026-08-03 14:20:00', '2026-08-05 11:27:49'),
(5, 101, 780, 34, 210, '2026-08-04 10:10:00', '2026-08-05 11:27:49'), (6, 102, 320, 8, 60, '2026-08-02 16:00:00', '2026-08-05 11:27:49'),
(7, 103, 2150, 89, 500, '2026-08-05 11:30:00', '2026-08-05 11:27:49'), (8, 104, 450, 15, 30, '2026-08-03 09:45:00', '2026-08-05 11:27:49'),
(9, 105, 890, 22, 320, '2026-08-04 13:20:00', '2026-08-05 11:27:49');

-- 8. AI Labels & Documents
INSERT INTO ai_labels (id, name) VALUES (1, 'Human Written'), (2, 'AI Generated'), (3, 'Mixed');

INSERT INTO documents (id, product_id, file_url, ai_score, ai_label_id) VALUES
(1, 1, 'https://s3.aws.com/files/hotel_php.zip', 5.50, 1),
(2, 2, 'https://s3.aws.com/files/vue_admin.zip', 12.00, 1),
(3, 3, 'https://s3.aws.com/files/figma_icons.zip', 95.50, 2),
(4, 116, '/uploads/products/files/1788615151_6a9c19efb9134.pdf', 0.00, 1),
(5, 117, '/uploads/products/files/1788616741_6a9c2025bdb74.pdf', 0.00, 1);

-- 9. Tags & Product Tags
INSERT INTO tags (id, name, slug) VALUES
(1, 'PHP', 'php'), (2, 'VueJS', 'vuejs'), (3, 'Figma', 'figma'), (100, 'C++', 'cpp'), (101, 'Java', 'java'),
(102, 'Next.js', 'nextjs'), (103, 'Rap Việt', 'rap-viet'), (104, 'Wave Alpha', 'wave-alpha'), (105, 'Nintendo Switch', 'nintendo-switch');

INSERT INTO product_tags (product_id, tag_id) VALUES (1, 1), (2, 2), (3, 3), (100, 100), (101, 101), (103, 102), (104, 103), (105, 104), (106, 105);

-- 10. Orders & Order Items
INSERT INTO orders (id, order_number, user_id, product_id, total_amount, platform_fee, seller_amount, status, order_expires_at, created_at) VALUES
(1, 'ORD-2026-001', 4, 1, 500000.0000, 25000.0000, 475000.0000, 2, NULL, '2026-07-25 10:00:00'),
(2, 'ORD-2026-002', 4, 2, 200000.0000, 10000.0000, 190000.0000, 1, NULL, '2026-07-28 14:30:00'),
(3, 'ORD-2026-003', 103, 100, 150000.0000, 7500.0000, 142500.0000, 2, NULL, '2026-07-30 09:15:00'),
(4, 'ORD-2026-004', 104, 101, 250000.0000, 12500.0000, 237500.0000, 1, NULL, '2026-08-01 16:45:00'),
(5, 'ORD-2026-005', 103, 104, 500000.0000, 25000.0000, 475000.0000, 2, NULL, '2026-08-02 11:20:00');

INSERT INTO order_items (id, order_id, product_id, product_name, quantity, unit_price, subtotal, platform_fee, seller_amount, created_at) VALUES
(1, 1, 1, 'Source Code Quản lý Khách sạn PHP', 1, 500000.0000, 500000.0000, 25000.0000, 475000.0000, '2026-07-25 10:00:00'),
(2, 2, 2, 'Template Admin Dashboard VueJS', 1, 200000.0000, 200000.0000, 10000.0000, 190000.0000, '2026-07-28 14:30:00'),
(3, 3, 100, 'Source Code Quản Lý Cây Gia Phả (C++)', 1, 150000.0000, 150000.0000, 7500.0000, 142500.0000, '2026-07-30 09:15:00'),
(4, 4, 101, 'Đồ án Quản lý Nhà hàng (Java & SQL Server)', 1, 250000.0000, 250000.0000, 12500.0000, 237500.0000, '2026-08-01 16:45:00'),
(5, 5, 104, 'Beat Rap Melody (Style Hieuthuhai, Robber & Gill)', 1, 500000.0000, 500000.0000, 25000.0000, 475000.0000, '2026-08-02 11:20:00');

-- 11. Transactions
INSERT INTO transactions (id, wallet_id, reference_id, type, amount, description, gateway_transaction_id, payment_method, created_at) VALUES
(100, 103, NULL, 1, 1500000.0000, 'Nạp tiền vào ví qua Momo', NULL, NULL, '2026-08-02 17:00:00'),
(101, 104, NULL, 1, 500000.0000, 'Nạp tiền vào ví qua VNPay', NULL, NULL, '2026-08-02 17:00:00'),
(102, 101, NULL, 5, 250000.0000, 'Doanh thu bán Đồ án Quản lý Nhà hàng', NULL, NULL, '2026-08-02 17:00:00'),
(103, 102, NULL, 5, 500000.0000, 'Doanh thu bán Beat Rap', NULL, NULL, '2026-08-02 17:00:00');

-- 12. Testimonials
INSERT INTO testimonials (id, user_id, content, rating, is_featured, sort_order, created_at) VALUES
(1, 4, 'Nền tảng tuyệt vời! Tôi đã tìm được nhiều tài liệu chất lượng cao cho dự án của mình.', 5, 1, 1, '2026-08-05 09:50:11'),
(2, 4, 'Giao diện đẹp, dễ sử dụng. Thanh toán nhanh chóng và an toàn.', 5, 1, 2, '2026-08-05 09:50:11'),
(3, 4, 'Cộng đồng người bán rất chuyên nghiệp. Tôi đã bán được nhiều sản phẩm.', 5, 0, 3, '2026-08-05 09:50:11'),
(4, 4, 'Tài liệu phong phú, đa dạng. Giá cả hợp lý.', 4, 0, 4, '2026-08-05 09:50:11');

-- 13. Reviews (Bao gồm các review mới #104 -> #110 kèm reply)
INSERT INTO reviews (id, product_id, user_id, parent_id, rating, comment, status, created_at, updated_at) VALUES
(1, 1, 4, NULL, 5, 'Source code rất tốt, dễ hiểu!', 1, '2026-08-01 14:03:22', '2026-08-01 14:03:22'),
(2, 3, 4, NULL, 4, 'Icon đẹp nhưng hơi ít màu.', 1, '2026-08-01 14:03:22', '2026-08-01 14:03:22'),
(100, 100, 103, NULL, 5, 'Source code chạy mượt, hướng dẫn chi tiết, 10 điểm!', 1, '2026-08-02 17:00:00', '2026-08-02 17:00:00'),
(101, 105, 104, NULL, 4, 'Tài liệu hướng dẫn độ xe rất thực tế, nhưng hình ảnh hơi mờ.', 1, '2026-08-02 17:00:00', '2026-08-02 17:00:00'),
(102, 104, 103, NULL, 5, 'Beat cháy quá shop ơi, rất hợp vocal của mình!', 1, '2026-08-02 17:00:00', '2026-08-02 17:00:00'),
(103, 101, 104, NULL, 5, 'Database thiết kế chuẩn, file thiết kế PlantUML rất rõ ràng.', 1, '2026-08-02 17:00:00', '2026-08-02 17:00:00'),
(104, 116, 1, NULL, 5, 'hay mày', 1, '2026-09-05 20:34:18', '2026-09-05 20:34:18'),
(105, 116, 1, 104, NULL, 'hay thiệt ko ba', 1, '2026-09-05 20:34:43', '2026-09-05 20:34:43'),
(106, 116, 1, 104, NULL, 'thiệt hong bá khí nha bro', 1, '2026-09-05 20:35:07', '2026-09-05 20:35:07'),
(107, 116, 1, 104, NULL, 'bá khí', 1, '2026-09-05 20:53:07', '2026-09-05 20:53:07'),
(108, 117, 2, NULL, 5, 'hay mày', 1, '2026-09-05 21:16:18', '2026-09-05 21:16:18'),
(109, 110, 2, NULL, 5, 'hay mày', 1, '2026-09-05 21:17:24', '2026-09-05 21:17:24'),
(110, 117, 1, NULL, 5, 'hay', 1, '2026-09-06 19:40:23', '2026-09-06 19:40:23');

-- 14. Downloads
INSERT INTO downloads (id, user_id, product_id, ip_address, downloaded_at) VALUES
(1, 4, 1, '192.168.1.1', '2026-08-01 14:03:22'), (2, 4, 1, '192.168.1.1', '2026-08-01 14:03:22'), (3, 4, 3, '192.168.1.5', '2026-08-01 14:03:22');

-- 15. Favorites
INSERT INTO favorites (user_id, product_id, created_at) VALUES
(106, 109, '2026-09-02 16:50:21');

-- 16. Carts
INSERT INTO carts (id, user_id, created_at, updated_at) VALUES
(1, 1, '2026-09-01 18:36:52', '2026-09-01 18:36:52'),
(2, 2, '2026-09-01 18:55:41', '2026-09-01 18:55:41'),
(3, 4, '2026-09-02 12:35:09', '2026-09-02 12:35:09'),
(4, 105, '2026-09-02 16:22:50', '2026-09-02 16:22:50'),
(5, 106, '2026-09-02 16:37:07', '2026-09-02 16:37:07'),
(6, 107, '2026-09-05 20:25:54', '2026-09-05 20:25:54'),
(7, 108, '2026-09-05 20:54:48', '2026-09-05 20:54:48');

-- 17. Product Approvals
INSERT INTO product_approvals (id, product_id, censor_id, action, note, created_at) VALUES
(1, 116, 1, 'APPROVE', 'Sản phẩm đạt yêu cầu và được phê duyệt đăng tải', '2026-09-05 20:32:59'),
(2, 117, 1, 'APPROVE', 'Sản phẩm đạt yêu cầu và được phê duyệt đăng tải', '2026-09-05 20:59:38');

-- 18. Reports (Bao gồm cột report_type và dữ liệu mới test thành công)
INSERT INTO reports (id, reporter_id, report_type, target_type, target_id, reason, details, status, resolved_by, created_at, updated_at) VALUES
(1, 1, 'COMPLAINT', 'STORE', 107, 'Vi phạm bản quyền', 'cặc', 1, NULL, '2026-09-06 19:34:45', '2026-09-06 19:34:45');

-- 19. Withdraw Requests
INSERT INTO withdraw_requests (id, wallet_id, amount, bank_name, bank_account_number, bank_account_name, status, admin_note, processed_by, created_at, updated_at) VALUES
(1, 2, 2000000.0000, 'Vietcombank', '0123456789', 'SELLER ONE', 1, NULL, NULL, '2026-08-01 14:03:22', '2026-08-01 14:03:22');

-- 20. Settings
INSERT INTO settings (id, setting_key, setting_value, description, updated_at) VALUES
(1, 'commission_rate', '36', 'Tỷ lệ phí nền tảng (%)', '2026-09-01 19:38:58');

-- Khởi tạo giá trị AUTO_INCREMENT cho các bảng
ALTER TABLE ai_appeals MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE ai_labels MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE carts MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
ALTER TABLE cart_items MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE categories MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE documents MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE downloads MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE kyc_documents MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE orders MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE order_items MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE password_reset_tokens MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE products MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;
ALTER TABLE product_approvals MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE product_stats MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
ALTER TABLE reports MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE reviews MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;
ALTER TABLE settings MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE stores MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;
ALTER TABLE tags MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;
ALTER TABLE testimonials MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE transactions MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;
ALTER TABLE users MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;
ALTER TABLE wallets MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;
ALTER TABLE withdraw_requests MODIFY id bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;


-- haha
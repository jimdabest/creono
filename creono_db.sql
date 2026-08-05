-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2026 at 06:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `creono_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ApproveWithdrawal` (IN `p_request_id` BIGINT, IN `p_admin_id` BIGINT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_RequestWithdrawal` (IN `p_wallet_id` BIGINT, IN `p_amount` DECIMAL(19,4), IN `p_bank_name` VARCHAR(255), IN `p_bank_acc_num` VARCHAR(100), IN `p_bank_acc_name` VARCHAR(255))   BEGIN
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

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ai_appeals`
--

CREATE TABLE `ai_appeals` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `seller_id` bigint(20) NOT NULL,
  `reason` text NOT NULL,
  `evidence_url` varchar(500) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
  `processed_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_labels`
--

CREATE TABLE `ai_labels` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_labels`
--

INSERT INTO `ai_labels` (`id`, `name`) VALUES
(1, 'Human Written'),
(2, 'AI Generated'),
(3, 'Mixed');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) NOT NULL,
  `cart_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `sort_order`, `created_at`) VALUES
(1, 'Lập trình', 'lap-trinh', 'Tài liệu lập trình, mã nguồn, framework', 1, '2026-08-05 09:50:11'),
(2, 'Thiết kế', 'thiet-ke', 'UI/UX, Figma, Photoshop, Illustrator', 2, '2026-08-05 09:50:11'),
(3, 'Kinh doanh', 'kinh-doanh', 'Marketing, E-commerce, Khởi nghiệp', 3, '2026-08-05 09:50:11'),
(4, 'Học thuật', 'hoc-thuat', 'Luận văn, Báo cáo, Giáo trình', 4, '2026-08-05 09:50:11'),
(5, 'Template', 'template', 'Website template, Admin dashboard', 5, '2026-08-05 09:50:11'),
(6, 'E-commerce', 'ecommerce', 'Giải pháp bán hàng online', 6, '2026-08-05 09:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `ai_score` decimal(5,2) DEFAULT 0.00,
  `ai_label_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `product_id`, `file_url`, `ai_score`, `ai_label_id`) VALUES
(1, 1, 'https://s3.aws.com/files/hotel_php.zip', 5.50, 1),
(2, 2, 'https://s3.aws.com/files/vue_admin.zip', 12.00, 1),
(3, 3, 'https://s3.aws.com/files/figma_icons.zip', 95.50, 2);

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `downloaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `downloads`
--

INSERT INTO `downloads` (`id`, `user_id`, `product_id`, `ip_address`, `downloaded_at`) VALUES
(1, 4, 1, '192.168.1.1', '2026-08-01 14:03:22'),
(2, 4, 1, '192.168.1.1', '2026-08-01 14:03:22'),
(3, 4, 3, '192.168.1.5', '2026-08-01 14:03:22');

--
-- Triggers `downloads`
--
DELIMITER $$
CREATE TRIGGER `trg_after_download` AFTER INSERT ON `downloads` FOR EACH ROW BEGIN
    UPDATE products 
    SET download_count = download_count + 1 
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_documents`
--

CREATE TABLE `kyc_documents` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `document_type` varchar(50) NOT NULL COMMENT 'ID_CARD, PASSPORT, DRIVER_LICENSE',
  `front_image_url` varchar(500) NOT NULL,
  `back_image_url` varchar(500) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
  `rejection_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `total_amount` decimal(19,4) NOT NULL,
  `platform_fee` decimal(19,4) DEFAULT 0.0000,
  `seller_amount` decimal(19,4) DEFAULT 0.0000,
  `status` tinyint(4) DEFAULT 1 COMMENT '1:Pending, 2:Paid, 3:Cancelled',
  `order_expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `product_id`, `total_amount`, `platform_fee`, `seller_amount`, `status`, `order_expires_at`, `created_at`) VALUES
(1, 'ORD-2026-001', 4, 1, 500000.0000, 25000.0000, 475000.0000, 2, NULL, '2026-07-25 10:00:00'),
(2, 'ORD-2026-002', 4, 2, 200000.0000, 10000.0000, 190000.0000, 1, NULL, '2026-07-28 14:30:00'),
(3, 'ORD-2026-003', 103, 100, 150000.0000, 7500.0000, 142500.0000, 2, NULL, '2026-07-30 09:15:00'),
(4, 'ORD-2026-004', 104, 101, 250000.0000, 12500.0000, 237500.0000, 1, NULL, '2026-08-01 16:45:00'),
(5, 'ORD-2026-005', 103, 104, 500000.0000, 25000.0000, 475000.0000, 2, NULL, '2026-08-02 11:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(19,4) NOT NULL,
  `subtotal` decimal(19,4) NOT NULL,
  `platform_fee` decimal(19,4) DEFAULT 0.0000,
  `seller_amount` decimal(19,4) DEFAULT 0.0000,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `subtotal`, `platform_fee`, `seller_amount`, `created_at`) VALUES
(1, 1, 1, 'Source Code Quản lý Khách sạn PHP', 1, 500000.0000, 500000.0000, 25000.0000, 475000.0000, '2026-07-25 10:00:00'),
(2, 2, 2, 'Template Admin Dashboard VueJS', 1, 200000.0000, 200000.0000, 10000.0000, 190000.0000, '2026-07-28 14:30:00'),
(3, 3, 100, 'Source Code Quản Lý Cây Gia Phả (C++)', 1, 150000.0000, 150000.0000, 7500.0000, 142500.0000, '2026-07-30 09:15:00'),
(4, 4, 101, 'Đồ án Quản lý Nhà hàng (Java & SQL Server)', 1, 250000.0000, 250000.0000, 12500.0000, 237500.0000, '2026-08-01 16:45:00'),
(5, 5, 104, 'Beat Rap Melody (Style Hieuthuhai, Robber & Gill)', 1, 500000.0000, 500000.0000, 25000.0000, 475000.0000, '2026-08-02 11:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(19,4) NOT NULL,
  `preview_url` varchar(500) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1 COMMENT '1:Pending, 2:Approved, 3:Rejected',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `category_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `store_id`, `title`, `description`, `price`, `preview_url`, `rating`, `review_count`, `download_count`, `status`, `deleted_at`, `created_at`, `category_id`) VALUES
(1, 1, 'Source Code Quản lý Khách sạn PHP', 'Mã nguồn quản lý khách sạn chuyên nghiệp với PHP. Quản lý phòng, đặt phòng, thanh toán và báo cáo.', 500000.0000, NULL, 5.00, 1, 2, 2, NULL, '2026-08-01 14:03:21', 1),
(2, 1, 'Template Admin Dashboard VueJS', 'Dashboard admin VueJS với các component tái sử dụng, tối ưu hiệu suất.', 200000.0000, NULL, 0.00, 0, 0, 1, NULL, '2026-08-01 14:03:21', 5),
(3, 2, 'Bộ icon Figma E-Commerce 2024', 'Bộ icon Figma đa dạng cho thiết kế E-commerce, bao gồm 200+ icon.', 150000.0000, NULL, 4.00, 1, 1, 2, NULL, '2026-08-01 14:03:21', 2),
(100, 100, 'Source Code Quản Lý Cây Gia Phả (C++)', NULL, 150000.0000, NULL, 4.81, 26, 150, 2, NULL, '2026-08-02 17:00:00', 1),
(101, 100, 'Đồ án Quản lý Nhà hàng (Java & SQL Server)', NULL, 250000.0000, NULL, 5.00, 43, 210, 2, NULL, '2026-08-02 17:00:00', 1),
(102, 100, 'Báo cáo thuật toán TSP - Traveling Salesman Problem (Full File LaTeX)', NULL, 80000.0000, NULL, 4.50, 12, 60, 2, NULL, '2026-08-02 17:00:00', NULL),
(103, 100, 'Template Dashboard Quản trị bằng Next.js', NULL, 300000.0000, NULL, 4.90, 88, 500, 2, NULL, '2026-08-02 17:00:00', 5),
(104, 101, 'Beat Rap Melody (Style Hieuthuhai, Robber & Gill)', NULL, 500000.0000, NULL, 5.00, 11, 30, 2, NULL, '2026-08-02 17:00:00', NULL),
(105, 102, 'Ebook: Cẩm nang độ IC và Nhông Sên Dĩa cho Honda Wave Alpha 100', NULL, 99000.0000, NULL, 4.69, 57, 320, 2, NULL, '2026-08-02 17:00:00', NULL),
(106, 102, 'Bản Mod Firmware RCMloader cho Nintendo Switch 2026', NULL, 120000.0000, NULL, 4.60, 18, 90, 2, NULL, '2026-08-02 17:00:00', NULL),
(107, 102, 'Bí kíp tối ưu cấu hình ZingSpeed Mobile (Tặng kèm file Config)', NULL, 50000.0000, NULL, 4.30, 8, 45, 2, NULL, '2026-08-02 17:00:00', NULL),
(108, 1, 'Source Code Quản lý Bán hàng PHP', 'Mã nguồn hệ thống quản lý bán hàng hoàn chỉnh với PHP và MySQL. Bao gồm quản lý sản phẩm, đơn hàng, khách hàng và báo cáo doanh thu.', 350000.0000, NULL, 4.80, 12, 45, 2, NULL, '2026-08-05 09:50:11', 6),
(109, 1, 'Template Admin Dashboard React', 'Dashboard Admin hiện đại với React và Tailwind CSS. Tối ưu cho quản lý dữ liệu và phân tích.', 250000.0000, NULL, 4.50, 8, 32, 2, NULL, '2026-08-05 09:50:11', 5),
(110, 2, 'Bộ UI Kit Mobile App Figma', 'Bộ giao diện mobile app đẹp mắt cho Figma. Bao gồm 50+ màn hình thiết kế sẵn.', 180000.0000, NULL, 4.90, 15, 67, 2, NULL, '2026-08-05 09:50:11', 2),
(111, 2, 'Ebook Marketing Digital 2024', 'Hướng dẫn chiến lược marketing digital hiệu quả. Cập nhật xu hướng mới nhất 2024.', 120000.0000, NULL, 4.30, 6, 23, 2, NULL, '2026-08-05 09:50:11', 3),
(112, 1, 'Bộ Icon Vector 1000+', 'Bộ icon vector chất lượng cao cho thiết kế. Định dạng SVG, PNG, AI, EPS.', 95000.0000, NULL, 4.60, 9, 89, 2, NULL, '2026-08-05 09:50:11', 2),
(113, 2, 'Source Code Website Bán Hàng Laravel', 'Website bán hàng hoàn chỉnh với Laravel 10 và Livewire. Tối ưu SEO và hiệu suất cao.', 450000.0000, NULL, 4.90, 20, 56, 2, NULL, '2026-08-05 09:50:11', 6),
(114, 1, 'Template Portfolio UI/UX', 'Template portfolio hiện đại cho designer và developer. Responsive và animation mượt mà.', 150000.0000, NULL, 4.40, 7, 34, 2, NULL, '2026-08-05 09:50:11', 5),
(115, 2, 'Khóa học SEO từ A-Z', 'Tài liệu hướng dẫn SEO toàn diện từ cơ bản đến nâng cao. Cập nhật thuật toán Google mới nhất.', 200000.0000, NULL, 4.70, 11, 41, 2, NULL, '2026-08-05 09:50:11', 3);

-- --------------------------------------------------------

--
-- Table structure for table `product_approvals`
--

CREATE TABLE `product_approvals` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `censor_id` bigint(20) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'APPROVE, REJECT',
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_stats`
--

CREATE TABLE `product_stats` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `view_count` int(11) DEFAULT 0,
  `cart_count` int(11) DEFAULT 0,
  `purchase_count` int(11) DEFAULT 0,
  `last_viewed_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_stats`
--

INSERT INTO `product_stats` (`id`, `product_id`, `view_count`, `cart_count`, `purchase_count`, `last_viewed_at`, `updated_at`) VALUES
(1, 1, 1250, 45, 2, '2026-08-05 09:00:00', '2026-08-05 11:27:49'),
(2, 2, 890, 23, 0, '2026-08-04 15:30:00', '2026-08-05 11:27:49'),
(3, 3, 2100, 67, 1, '2026-08-05 08:45:00', '2026-08-05 11:27:49'),
(4, 100, 560, 12, 150, '2026-08-03 14:20:00', '2026-08-05 11:27:49'),
(5, 101, 780, 34, 210, '2026-08-04 10:10:00', '2026-08-05 11:27:49'),
(6, 102, 320, 8, 60, '2026-08-02 16:00:00', '2026-08-05 11:27:49'),
(7, 103, 2150, 89, 500, '2026-08-05 11:30:00', '2026-08-05 11:27:49'),
(8, 104, 450, 15, 30, '2026-08-03 09:45:00', '2026-08-05 11:27:49'),
(9, 105, 890, 22, 320, '2026-08-04 13:20:00', '2026-08-05 11:27:49');

-- --------------------------------------------------------

--
-- Table structure for table `product_tags`
--

CREATE TABLE `product_tags` (
  `product_id` bigint(20) NOT NULL,
  `tag_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_tags`
--

INSERT INTO `product_tags` (`product_id`, `tag_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(100, 100),
(101, 101),
(103, 102),
(104, 103),
(105, 104),
(106, 105);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) NOT NULL,
  `reporter_id` bigint(20) NOT NULL,
  `target_type` varchar(50) NOT NULL COMMENT 'PRODUCT, STORE, USER, REVIEW',
  `target_id` bigint(20) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1 COMMENT '1: Pending, 2: Investigating, 3: Resolved, 4: Dismissed',
  `resolved_by` bigint(20) DEFAULT NULL COMMENT 'Admin ID',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `parent_id` bigint(20) DEFAULT NULL COMMENT 'Dành cho tính năng Reply',
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text NOT NULL,
  `status` tinyint(4) DEFAULT 1 COMMENT '1: Visible, 0: Hidden',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `parent_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 4, NULL, 5, 'Source code rất tốt, dễ hiểu!', 1, '2026-08-01 14:03:22', '2026-08-01 14:03:22'),
(2, 3, 4, NULL, 4, 'Icon đẹp nhưng hơi ít màu.', 1, '2026-08-01 14:03:22', '2026-08-01 14:03:22'),
(100, 100, 103, NULL, 5, 'Source code chạy mượt, hướng dẫn chi tiết, 10 điểm!', 1, '2026-08-02 17:00:00', '2026-08-02 17:00:00'),
(101, 105, 104, NULL, 4, 'Tài liệu hướng dẫn độ xe rất thực tế, nhưng hình ảnh hơi mờ.', 1, '2026-08-02 17:00:00', '2026-08-02 17:00:00'),
(102, 104, 103, NULL, 5, 'Beat cháy quá shop ơi, rất hợp vocal của mình!', 1, '2026-08-02 17:00:00', '2026-08-02 17:00:00'),
(103, 101, 104, NULL, 5, 'Database thiết kế chuẩn, file thiết kế PlantUML rất rõ ràng.', 1, '2026-08-02 17:00:00', '2026-08-02 17:00:00');

--
-- Triggers `reviews`
--
DELIMITER $$
CREATE TRIGGER `trg_after_review_insert` AFTER INSERT ON `reviews` FOR EACH ROW BEGIN
    IF NEW.rating IS NOT NULL THEN
        UPDATE products 
        SET 
            rating = ROUND(((rating * review_count) + NEW.rating) / (review_count + 1), 2),
            review_count = review_count + 1
        WHERE id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `user_id`, `name`, `status`) VALUES
(1, 2, 'Code Master Store', 1),
(2, 3, 'Design UI/UX Hub', 1),
(100, 101, 'Thuật Toán & Database Hub', 1),
(101, 102, 'Underground Rap Beats', 1),
(102, 100, 'Modding & Hardware Pro', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`) VALUES
(1, 'PHP', 'php'),
(2, 'VueJS', 'vuejs'),
(3, 'Figma', 'figma'),
(100, 'C++', 'cpp'),
(101, 'Java', 'java'),
(102, 'Next.js', 'nextjs'),
(103, 'Rap Việt', 'rap-viet'),
(104, 'Wave Alpha', 'wave-alpha'),
(105, 'Nintendo Switch', 'nintendo-switch');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `content` text NOT NULL,
  `rating` tinyint(4) DEFAULT 5 CHECK (`rating` >= 1 and `rating` <= 5),
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `content`, `rating`, `is_featured`, `sort_order`, `created_at`) VALUES
(1, 4, 'Nền tảng tuyệt vời! Tôi đã tìm được nhiều tài liệu chất lượng cao cho dự án của mình.', 5, 1, 1, '2026-08-05 09:50:11'),
(2, 4, 'Giao diện đẹp, dễ sử dụng. Thanh toán nhanh chóng và an toàn.', 5, 1, 2, '2026-08-05 09:50:11'),
(3, 4, 'Cộng đồng người bán rất chuyên nghiệp. Tôi đã bán được nhiều sản phẩm.', 5, 0, 3, '2026-08-05 09:50:11'),
(4, 4, 'Tài liệu phong phú, đa dạng. Giá cả hợp lý.', 4, 0, 4, '2026-08-05 09:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) NOT NULL,
  `wallet_id` bigint(20) NOT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `type` tinyint(4) NOT NULL COMMENT '1:Deposit, 2:Withdraw, 3:Payment, 4:Refund, 5:Earning',
  `amount` decimal(19,4) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `gateway_transaction_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `wallet_id`, `reference_id`, `type`, `amount`, `description`, `gateway_transaction_id`, `payment_method`, `created_at`) VALUES
(100, 103, NULL, 1, 1500000.0000, 'Nạp tiền vào ví qua Momo', NULL, NULL, '2026-08-02 17:00:00'),
(101, 104, NULL, 1, 500000.0000, 'Nạp tiền vào ví qua VNPay', NULL, NULL, '2026-08-02 17:00:00'),
(102, 101, NULL, 5, 250000.0000, 'Doanh thu bán Đồ án Quản lý Nhà hàng', NULL, NULL, '2026-08-02 17:00:00'),
(103, 102, NULL, 5, 500000.0000, 'Doanh thu bán Beat Rap', NULL, NULL, '2026-08-02 17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(4) DEFAULT 1 COMMENT '1:Buyer, 2:Seller, 3:Admin, 4:Censor',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 3, '2026-08-01 14:03:20'),
(2, 'Seller One', 'seller1@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, '2026-08-01 14:03:20'),
(3, 'Seller Two', 'seller2@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, '2026-08-01 14:03:20'),
(4, 'Buyer John', 'buyer@mail.com', '$2y$10$swjH6f9z.3iCc465GO8xCeDU.UzTGyRocTFmaKiFT2CNtoscpJuxK', 1, '2026-08-01 14:03:20'),
(5, 'Nguyễn Văn A', 'test@example.com', '$2y$10$Pddv8oOUrMPDKhzwDcEdV.bYib67eJn0wnIcpeuN08RvonPGi7MNa', 1, '2026-08-02 11:45:59'),
(6, 'Test User', 'test+2aff08f0fd034765b9e74c0920fba66f@creono.test', '$2y$10$eomKcyX12hFRz7vavFk4i./VFXmVuRHl3Y6edZh4ezEFTL0cCX1a.', 1, '2026-08-02 11:55:32'),
(100, 'Nguyễn Vũ Thuận', 'thuan.admin@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 3, '2026-08-02 16:59:59'),
(101, 'Nguyễn Viết Thịnh', 'thinh.seller@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjLinY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, '2026-08-02 16:59:59'),
(102, 'Trần Tú Tâm', 'tam.seller@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, '2026-08-02 16:59:59'),
(103, 'Lê Quang Tân', 'tan.buyer@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, '2026-08-02 16:59:59'),
(104, 'Phạm Nguyễn', 'pham.buyer@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, '2026-08-02 16:59:59');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` bigint(20) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `full_name`, `bio`, `avatar_url`, `created_at`, `updated_at`) VALUES
(1, 'Sus admin', 'adadaada', '/uploads/avatars/1785903198_6a72b85ec1057.jpg', '2026-08-02 12:00:52', '2026-08-05 11:13:37'),
(2, 'Seller One', 'tui nà seo lơ', NULL, '2026-08-02 12:00:52', '2026-08-05 11:31:19'),
(3, 'Seller Two', NULL, NULL, '2026-08-02 12:00:52', '2026-08-02 12:00:52'),
(4, 'Buyer John', NULL, NULL, '2026-08-02 12:00:52', '2026-08-02 12:00:52'),
(5, 'Nguyễn Văn A', NULL, NULL, '2026-08-02 12:00:52', '2026-08-02 12:00:52'),
(6, 'Test User', NULL, NULL, '2026-08-02 12:00:52', '2026-08-02 12:00:52');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_pendingapprovals`
-- (See below for the actual view)
--
CREATE TABLE `vw_pendingapprovals` (
`product_id` bigint(20)
,`title` varchar(255)
,`store_name` varchar(255)
,`ai_score` decimal(5,2)
,`ai_label_name` varchar(100)
,`created_at` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_pendingwithdrawals`
-- (See below for the actual view)
--
CREATE TABLE `vw_pendingwithdrawals` (
`request_id` bigint(20)
,`seller_email` varchar(255)
,`amount` decimal(19,4)
,`bank_name` varchar(255)
,`bank_account_number` varchar(100)
,`created_at` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_pending_orders_by_seller`
-- (See below for the actual view)
--
CREATE TABLE `vw_pending_orders_by_seller` (
`seller_id` bigint(20)
,`order_id` bigint(20)
,`order_number` varchar(50)
,`total_amount` decimal(19,4)
,`created_at` datetime
,`product_title` varchar(255)
,`buyer_name` varchar(255)
,`buyer_email` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_seller_revenue`
-- (See below for the actual view)
--
CREATE TABLE `vw_seller_revenue` (
`seller_id` bigint(20)
,`store_id` bigint(20)
,`store_name` varchar(255)
,`total_orders` bigint(21)
,`total_revenue` decimal(41,4)
,`total_fee` decimal(41,4)
,`avg_rating` decimal(7,6)
,`total_products` bigint(21)
,`total_customers` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_topproducts`
-- (See below for the actual view)
--
CREATE TABLE `vw_topproducts` (
`id` bigint(20)
,`title` varchar(255)
,`price` decimal(19,4)
,`rating` decimal(3,2)
,`review_count` int(11)
,`download_count` int(11)
,`store_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_top_products_by_seller`
-- (See below for the actual view)
--
CREATE TABLE `vw_top_products_by_seller` (
`seller_id` bigint(20)
,`product_id` bigint(20)
,`title` varchar(255)
,`price` decimal(19,4)
,`rating` decimal(3,2)
,`total_sales` int(11)
,`review_count` int(11)
,`view_count` int(11)
,`cart_count` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `balance` decimal(19,4) DEFAULT 0.0000,
  `frozen_balance` decimal(19,4) DEFAULT 0.0000 COMMENT 'Giữ tiền khi đang chờ rút'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `frozen_balance`) VALUES
(1, 1, 0.0000, 0.0000),
(2, 2, 3000000.0000, 2000000.0000),
(3, 3, 150000.0000, 0.0000),
(4, 4, 1000000.0000, 0.0000),
(5, 5, 0.0000, 0.0000),
(6, 6, 0.0000, 0.0000),
(100, 100, 99999999.0000, 0.0000),
(101, 101, 15000000.0000, 200000.0000),
(102, 102, 5000000.0000, 0.0000),
(103, 103, 1500000.0000, 0.0000),
(104, 104, 500000.0000, 0.0000);

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_requests`
--

CREATE TABLE `withdraw_requests` (
  `id` bigint(20) NOT NULL,
  `wallet_id` bigint(20) NOT NULL,
  `amount` decimal(19,4) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `bank_account_number` varchar(100) NOT NULL,
  `bank_account_name` varchar(255) NOT NULL,
  `status` tinyint(4) DEFAULT 1 COMMENT '1: Pending, 2: Approved, 3: Rejected',
  `admin_note` text DEFAULT NULL,
  `processed_by` bigint(20) DEFAULT NULL COMMENT 'Admin ID',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdraw_requests`
--

INSERT INTO `withdraw_requests` (`id`, `wallet_id`, `amount`, `bank_name`, `bank_account_number`, `bank_account_name`, `status`, `admin_note`, `processed_by`, `created_at`, `updated_at`) VALUES
(1, 2, 2000000.0000, 'Vietcombank', '0123456789', 'SELLER ONE', 1, NULL, NULL, '2026-08-01 14:03:22', '2026-08-01 14:03:22');

-- --------------------------------------------------------

--
-- Structure for view `vw_pendingapprovals`
--
DROP TABLE IF EXISTS `vw_pendingapprovals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_pendingapprovals`  AS SELECT `p`.`id` AS `product_id`, `p`.`title` AS `title`, `s`.`name` AS `store_name`, `d`.`ai_score` AS `ai_score`, `al`.`name` AS `ai_label_name`, `p`.`created_at` AS `created_at` FROM (((`products` `p` join `stores` `s` on(`p`.`store_id` = `s`.`id`)) join `documents` `d` on(`p`.`id` = `d`.`product_id`)) left join `ai_labels` `al` on(`d`.`ai_label_id` = `al`.`id`)) WHERE `p`.`status` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `vw_pendingwithdrawals`
--
DROP TABLE IF EXISTS `vw_pendingwithdrawals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_pendingwithdrawals`  AS SELECT `w`.`id` AS `request_id`, `u`.`email` AS `seller_email`, `w`.`amount` AS `amount`, `w`.`bank_name` AS `bank_name`, `w`.`bank_account_number` AS `bank_account_number`, `w`.`created_at` AS `created_at` FROM ((`withdraw_requests` `w` join `wallets` `wal` on(`w`.`wallet_id` = `wal`.`id`)) join `users` `u` on(`wal`.`user_id` = `u`.`id`)) WHERE `w`.`status` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `vw_pending_orders_by_seller`
--
DROP TABLE IF EXISTS `vw_pending_orders_by_seller`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_pending_orders_by_seller`  AS SELECT `s`.`user_id` AS `seller_id`, `o`.`id` AS `order_id`, `o`.`order_number` AS `order_number`, `o`.`total_amount` AS `total_amount`, `o`.`created_at` AS `created_at`, `p`.`title` AS `product_title`, `u`.`name` AS `buyer_name`, `u`.`email` AS `buyer_email` FROM (((`orders` `o` join `products` `p` on(`o`.`product_id` = `p`.`id`)) join `stores` `s` on(`p`.`store_id` = `s`.`id`)) join `users` `u` on(`o`.`user_id` = `u`.`id`)) WHERE `o`.`status` = 1 ORDER BY `o`.`created_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_seller_revenue`
--
DROP TABLE IF EXISTS `vw_seller_revenue`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_seller_revenue`  AS SELECT `s`.`user_id` AS `seller_id`, `s`.`id` AS `store_id`, `s`.`name` AS `store_name`, count(distinct `o`.`id`) AS `total_orders`, coalesce(sum(`o`.`seller_amount`),0) AS `total_revenue`, coalesce(sum(`o`.`platform_fee`),0) AS `total_fee`, coalesce(avg(`p`.`rating`),0) AS `avg_rating`, count(distinct `p`.`id`) AS `total_products`, count(distinct `o`.`user_id`) AS `total_customers` FROM ((`stores` `s` left join `products` `p` on(`p`.`store_id` = `s`.`id` and `p`.`deleted_at` is null)) left join `orders` `o` on(`o`.`product_id` = `p`.`id` and `o`.`status` = 2)) GROUP BY `s`.`id`, `s`.`user_id`, `s`.`name` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_topproducts`
--
DROP TABLE IF EXISTS `vw_topproducts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_topproducts`  AS SELECT `p`.`id` AS `id`, `p`.`title` AS `title`, `p`.`price` AS `price`, `p`.`rating` AS `rating`, `p`.`review_count` AS `review_count`, `p`.`download_count` AS `download_count`, `s`.`name` AS `store_name` FROM (`products` `p` join `stores` `s` on(`p`.`store_id` = `s`.`id`)) WHERE `p`.`status` = 2 AND `p`.`deleted_at` is null ORDER BY `p`.`download_count` DESC, `p`.`rating` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_top_products_by_seller`
--
DROP TABLE IF EXISTS `vw_top_products_by_seller`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_top_products_by_seller`  AS SELECT `s`.`user_id` AS `seller_id`, `p`.`id` AS `product_id`, `p`.`title` AS `title`, `p`.`price` AS `price`, `p`.`rating` AS `rating`, `p`.`download_count` AS `total_sales`, `p`.`review_count` AS `review_count`, `ps`.`view_count` AS `view_count`, `ps`.`cart_count` AS `cart_count` FROM ((`products` `p` join `stores` `s` on(`p`.`store_id` = `s`.`id`)) left join `product_stats` `ps` on(`p`.`id` = `ps`.`product_id`)) WHERE `p`.`deleted_at` is null ORDER BY `p`.`download_count` DESC, `p`.`rating` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_appeals`
--
ALTER TABLE `ai_appeals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_appeal_product` (`product_id`),
  ADD KEY `fk_appeal_seller` (`seller_id`),
  ADD KEY `fk_appeal_admin` (`processed_by`);

--
-- Indexes for table `ai_labels`
--
ALTER TABLE `ai_labels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_carts_user` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cartitems_cart` (`cart_id`),
  ADD KEY `fk_cartitems_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_doc_product` (`product_id`),
  ADD KEY `fk_doc_ailabel` (`ai_label_id`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dl_product` (`product_id`),
  ADD KEY `idx_downloads_user_prod` (`user_id`,`product_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`product_id`),
  ADD KEY `fk_fav_product` (`product_id`);

--
-- Indexes for table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kyc_user` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `idx_order_product` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`order_id`),
  ADD KEY `fk_order_items_product` (`product_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_pwdreset_user` (`user_id`),
  ADD KEY `idx_pwd_reset_token` (`token`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_store` (`store_id`),
  ADD KEY `fk_product_category` (`category_id`);

--
-- Indexes for table `product_approvals`
--
ALTER TABLE `product_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_approval_product` (`product_id`),
  ADD KEY `fk_approval_censor` (`censor_id`);

--
-- Indexes for table `product_stats`
--
ALTER TABLE `product_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_product_stats_product` (`product_id`);

--
-- Indexes for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD PRIMARY KEY (`product_id`,`tag_id`),
  ADD KEY `fk_pt_tag` (`tag_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reports_reporter` (`reporter_id`),
  ADD KEY `idx_reports_target` (`target_type`,`target_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_user` (`user_id`),
  ADD KEY `fk_reviews_parent` (`parent_id`),
  ADD KEY `idx_reviews_product_rating` (`product_id`,`rating`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_store_user` (`user_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_testimonial_user` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trans_wallet` (`wallet_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_wallet_user` (`user_id`);

--
-- Indexes for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_withdraw_wallet` (`wallet_id`),
  ADD KEY `fk_withdraw_admin` (`processed_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_appeals`
--
ALTER TABLE `ai_appeals`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_labels`
--
ALTER TABLE `ai_labels`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `product_approvals`
--
ALTER TABLE `product_approvals`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_stats`
--
ALTER TABLE `product_stats`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_appeals`
--
ALTER TABLE `ai_appeals`
  ADD CONSTRAINT `fk_appeal_admin` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_appeal_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_appeal_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cartitems_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cartitems_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_doc_ailabel` FOREIGN KEY (`ai_label_id`) REFERENCES `ai_labels` (`id`),
  ADD CONSTRAINT `fk_doc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `downloads`
--
ALTER TABLE `downloads`
  ADD CONSTRAINT `fk_dl_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_dl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_fav_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  ADD CONSTRAINT `fk_kyc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_pwdreset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`);

--
-- Constraints for table `product_approvals`
--
ALTER TABLE `product_approvals`
  ADD CONSTRAINT `fk_approval_censor` FOREIGN KEY (`censor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_approval_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `product_stats`
--
ALTER TABLE `product_stats`
  ADD CONSTRAINT `fk_product_stats_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD CONSTRAINT `fk_pt_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_parent` FOREIGN KEY (`parent_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `fk_store_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `fk_testimonial_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_trans_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_user_profile` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD CONSTRAINT `fk_withdraw_admin` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_withdraw_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

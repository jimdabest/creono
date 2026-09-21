-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2026 at 10:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_RequestWithdrawal` (IN `p_wallet_id` BIGINT, IN `p_amount` DECIMAL(19,4), IN `p_bank_name` VARCHAR(255), IN `p_bank_acc_num` VARCHAR(100), IN `p_bank_acc_name` VARCHAR(255))   BEGIN
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
  `status` tinyint(4) DEFAULT 1,
  `processed_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_appeals`
--

INSERT INTO `ai_appeals` (`id`, `product_id`, `seller_id`, `reason`, `evidence_url`, `status`, `processed_by`, `created_at`, `updated_at`) VALUES
(1, 17, 17, 'Model AI của tôi hoàn toàn hợp pháp, không vi phạm bản quyền. Yêu cầu xem xét lại.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+17', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(2, 18, 17, 'Prompt pack chỉ là tài liệu tham khảo, không vi phạm chính sách.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+18', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(3, 28, 17, 'Hệ thống điểm danh không sử dụng dữ liệu trái phép, có giấy phép đầy đủ.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+28', 2, 18, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(4, 4, 3, 'Icon pack do tôi tự thiết kế 100%, không copy từ nguồn nào.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+04', 2, 19, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(5, 51, 45, 'Smart contract đã được audit bởi công ty uy tín, không có backdoor.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+51', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(6, 54, 45, 'Token launchpad code 100% tự viết, có thể cung cấp git history.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+54', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(7, 62, 47, 'Khóa học ethical hacking có nội dung hợp pháp, chỉ dạy lý thuyết và lab an toàn.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+62', 2, 51, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(8, 31, 41, 'Slide bị lỗi font do máy khách thiếu font, không phải lỗi file.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+31', 2, 52, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(9, 33, 41, 'Code chạy tốt trên môi trường chuẩn, khách có thể thiếu dependency.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+33', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(10, 75, 18, 'ERP đầy đủ module, module kế toán nằm trong gói mở rộng.', 'https://placehold.co/600x400/94A3B8/white?text=Appeal+75', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45');

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

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 5, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(2, 6, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(3, 7, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(4, 8, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(5, 9, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(6, 10, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(7, 11, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(8, 12, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(9, 14, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(10, 21, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(11, 22, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(12, 23, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(13, 24, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(14, 25, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(15, 26, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(16, 27, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(17, 28, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(18, 29, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(19, 30, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(20, 31, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(21, 32, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(22, 33, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(23, 34, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(24, 35, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(25, 36, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(26, 37, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(27, 38, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(28, 39, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(29, 40, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(30, 55, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(31, 56, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(32, 57, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(33, 58, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(34, 59, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(35, 60, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(36, 1, '2026-09-19 17:56:34', '2026-09-19 17:56:34'),
(37, 2, '2026-09-19 18:38:10', '2026-09-19 18:38:10');

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

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `added_at`) VALUES
(1, 1, 2, '2026-09-19 17:55:45'),
(2, 1, 5, '2026-09-19 17:55:45'),
(3, 2, 4, '2026-09-19 17:55:45'),
(4, 2, 9, '2026-09-19 17:55:45'),
(5, 3, 11, '2026-09-19 17:55:45'),
(6, 3, 13, '2026-09-19 17:55:45'),
(7, 4, 7, '2026-09-19 17:55:45'),
(8, 4, 19, '2026-09-19 17:55:45'),
(9, 5, 20, '2026-09-19 17:55:45'),
(10, 5, 25, '2026-09-19 17:55:45'),
(11, 6, 26, '2026-09-19 17:55:45'),
(12, 6, 27, '2026-09-19 17:55:45'),
(13, 7, 29, '2026-09-19 17:55:45'),
(14, 7, 30, '2026-09-19 17:55:45'),
(15, 8, 10, '2026-09-19 17:55:45'),
(16, 8, 15, '2026-09-19 17:55:45'),
(17, 9, 21, '2026-09-19 17:55:45'),
(18, 9, 22, '2026-09-19 17:55:45'),
(19, 10, 31, '2026-09-19 17:55:45'),
(20, 10, 33, '2026-09-19 17:55:45'),
(21, 11, 36, '2026-09-19 17:55:45'),
(22, 11, 39, '2026-09-19 17:55:45'),
(23, 12, 41, '2026-09-19 17:55:45'),
(24, 12, 43, '2026-09-19 17:55:45'),
(25, 13, 46, '2026-09-19 17:55:45'),
(26, 13, 51, '2026-09-19 17:55:45'),
(27, 14, 55, '2026-09-19 17:55:45'),
(28, 14, 60, '2026-09-19 17:55:45'),
(29, 15, 62, '2026-09-19 17:55:45'),
(30, 15, 73, '2026-09-19 17:55:45'),
(31, 16, 75, '2026-09-19 17:55:45'),
(32, 16, 80, '2026-09-19 17:55:45'),
(33, 17, 32, '2026-09-19 17:55:45'),
(34, 17, 34, '2026-09-19 17:55:45'),
(35, 18, 37, '2026-09-19 17:55:45'),
(36, 18, 38, '2026-09-19 17:55:45'),
(37, 19, 42, '2026-09-19 17:55:45'),
(38, 19, 45, '2026-09-19 17:55:45'),
(39, 20, 47, '2026-09-19 17:55:45'),
(40, 20, 48, '2026-09-19 17:55:45'),
(41, 21, 52, '2026-09-19 17:55:45'),
(42, 21, 53, '2026-09-19 17:55:45'),
(43, 22, 56, '2026-09-19 17:55:45'),
(44, 22, 57, '2026-09-19 17:55:45'),
(45, 23, 61, '2026-09-19 17:55:45'),
(46, 23, 63, '2026-09-19 17:55:45'),
(47, 24, 64, '2026-09-19 17:55:45'),
(48, 24, 65, '2026-09-19 17:55:45'),
(49, 25, 67, '2026-09-19 17:55:45'),
(50, 25, 68, '2026-09-19 17:55:45'),
(51, 26, 71, '2026-09-19 17:55:45'),
(52, 26, 72, '2026-09-19 17:55:45'),
(53, 27, 76, '2026-09-19 17:55:45'),
(54, 27, 77, '2026-09-19 17:55:45'),
(55, 28, 78, '2026-09-19 17:55:45'),
(56, 28, 79, '2026-09-19 17:55:45'),
(57, 29, 35, '2026-09-19 17:55:45'),
(58, 29, 40, '2026-09-19 17:55:45'),
(59, 30, 44, '2026-09-19 17:55:45'),
(60, 30, 49, '2026-09-19 17:55:45'),
(61, 31, 50, '2026-09-19 17:55:45'),
(62, 31, 54, '2026-09-19 17:55:45'),
(63, 32, 58, '2026-09-19 17:55:45'),
(64, 32, 59, '2026-09-19 17:55:45'),
(65, 33, 66, '2026-09-19 17:55:45'),
(66, 33, 69, '2026-09-19 17:55:45'),
(67, 34, 70, '2026-09-19 17:55:45'),
(68, 34, 74, '2026-09-19 17:55:45'),
(69, 35, 1, '2026-09-19 17:55:45'),
(70, 35, 3, '2026-09-19 17:55:45');

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
(1, 'Source Code', 'source-code', 'Mã nguồn hệ thống, web app, mobile app', 1, '2026-09-19 17:55:45'),
(2, 'Thiết kế UI/UX', 'ui-ux-design', 'Figma UI Kits, Templates, Icons, Graphics', 2, '2026-09-19 17:55:45'),
(3, 'E-commerce', 'e-commerce', 'Giải pháp bán hàng, plugin thương mại điện tử', 3, '2026-09-19 17:55:45'),
(4, 'Tài liệu học thuật', 'tai-lieu-hoc-thuat', 'Giáo trình, luận văn, báo cáo chuyên ngành', 4, '2026-09-19 17:55:45'),
(5, 'Ebook & Khóa học', 'ebook-khoa-hoc', 'Sách điện tử và khóa học kỹ năng mềm', 5, '2026-09-19 17:55:45'),
(6, 'Mobile App', 'mobile-app', 'Ứng dụng di động iOS/Android, Flutter, React Native', 6, '2026-09-19 17:55:45'),
(7, 'Game Assets', 'game-assets', 'Tài nguyên game: sprite, tilemap, sound, 3D model', 7, '2026-09-19 17:55:45'),
(8, 'AI & Machine Learning', 'ai-ml', 'Model AI, dataset, notebook, prompt template', 8, '2026-09-19 17:55:45'),
(9, 'DevOps & Cloud', 'devops-cloud', 'Docker, Kubernetes, CI/CD, Terraform, Cloud scripts', 9, '2026-09-19 17:55:45'),
(10, 'Marketing Template', 'marketing-template', 'Template email, landing page, social media kit', 10, '2026-09-19 17:55:45'),
(11, 'Blockchain & Web3', 'blockchain-web3', 'Smart contract, DApp, NFT, DeFi', 11, '2026-09-19 17:55:45'),
(12, 'Cyber Security', 'cyber-security', 'Pentest, security tools, audit, course', 12, '2026-09-19 17:55:45'),
(13, 'AR/VR', 'ar-vr', 'AR/VR experience, 3D model, Unity AR', 13, '2026-09-19 17:55:45'),
(14, 'No-code/Low-code', 'no-code', 'Bubble, Webflow, Glide, AppSheet template', 14, '2026-09-19 17:55:45'),
(15, 'Video & Audio', 'video-audio', 'Template video, podcast, motion graphic', 15, '2026-09-19 17:55:45');

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
(1, 1, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 4.50, 1),
(2, 2, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 97.20, 1),
(3, 3, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.10, 1),
(4, 4, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.00, 1),
(5, 5, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 96.80, 1),
(6, 6, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.50, 1),
(7, 7, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 97.90, 1),
(8, 8, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.20, 1),
(9, 9, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(10, 10, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.40, 1),
(11, 11, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 97.60, 1),
(12, 12, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 96.50, 1),
(13, 13, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.80, 1),
(14, 14, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 97.30, 1),
(15, 15, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.10, 1),
(16, 16, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 97.70, 1),
(17, 17, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 85.20, 2),
(18, 18, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 92.40, 2),
(19, 19, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.60, 1),
(20, 20, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.20, 1),
(21, 21, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 95.80, 1),
(22, 22, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 97.10, 1),
(23, 23, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.30, 1),
(24, 24, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 97.50, 1),
(25, 25, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.30, 1),
(26, 26, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.00, 1),
(27, 27, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 97.80, 1),
(28, 28, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 60.00, 2),
(29, 29, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.90, 1),
(30, 30, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.70, 1),
(31, 31, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(32, 32, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.80, 1),
(33, 33, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.20, 1),
(34, 34, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.90, 1),
(35, 35, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.50, 1),
(36, 36, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.10, 1),
(37, 37, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.70, 1),
(38, 38, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.90, 1),
(39, 39, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.30, 1),
(40, 40, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.80, 1),
(41, 41, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.90, 1),
(42, 42, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.60, 1),
(43, 43, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(44, 44, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.40, 1),
(45, 45, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.70, 1),
(46, 46, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.20, 1),
(47, 47, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(48, 48, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.80, 1),
(49, 49, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.10, 1),
(50, 50, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(51, 51, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.20, 1),
(52, 52, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.30, 1),
(53, 53, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(54, 54, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.10, 1),
(55, 55, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.90, 1),
(56, 56, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(57, 57, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.20, 1),
(58, 58, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.70, 1),
(59, 59, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.80, 1),
(60, 60, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(61, 61, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.90, 1),
(62, 62, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.30, 1),
(63, 63, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.10, 1),
(64, 64, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.80, 1),
(65, 65, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(66, 66, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.60, 1),
(67, 67, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.70, 1),
(68, 68, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.90, 1),
(69, 69, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.50, 1),
(70, 70, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.80, 1),
(71, 71, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(72, 72, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.70, 1),
(73, 73, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.10, 1),
(74, 74, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(75, 75, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.20, 1),
(76, 76, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(77, 77, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.10, 1),
(78, 78, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 98.90, 1),
(79, 79, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.00, 1),
(80, 80, '/uploads/products/files/1789651524_6aabea447c0f3.pdf', 99.30, 1);

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
(1, 5, 1, '113.190.23.45', '2026-09-19 17:55:45'),
(2, 6, 3, '14.161.42.12', '2026-09-19 17:55:45'),
(3, 7, 6, '115.79.34.12', '2026-09-19 17:55:45'),
(4, 8, 5, '113.190.23.46', '2026-09-19 17:55:45'),
(5, 9, 7, '14.161.42.13', '2026-09-19 17:55:45'),
(6, 10, 9, '115.79.34.13', '2026-09-19 17:55:45'),
(7, 11, 11, '113.190.23.47', '2026-09-19 17:55:45'),
(8, 12, 13, '14.161.42.14', '2026-09-19 17:55:45'),
(9, 14, 15, '115.79.34.14', '2026-09-19 17:55:45'),
(10, 5, 19, '113.190.23.48', '2026-09-19 17:55:45'),
(11, 6, 20, '14.161.42.15', '2026-09-19 17:55:45'),
(12, 7, 21, '115.79.34.15', '2026-09-19 17:55:45'),
(13, 8, 22, '113.190.23.49', '2026-09-19 17:55:45'),
(14, 9, 25, '14.161.42.16', '2026-09-19 17:55:45'),
(15, 10, 26, '115.79.34.16', '2026-09-19 17:55:45'),
(16, 11, 27, '113.190.23.50', '2026-09-19 17:55:45'),
(17, 12, 29, '14.161.42.17', '2026-09-19 17:55:45'),
(18, 14, 30, '115.79.34.17', '2026-09-19 17:55:45'),
(19, 5, 2, '113.190.23.51', '2026-09-19 17:55:45'),
(20, 6, 4, '14.161.42.18', '2026-09-19 17:55:45'),
(21, 7, 6, '115.79.34.18', '2026-09-19 17:55:45'),
(22, 8, 10, '113.190.23.52', '2026-09-19 17:55:45'),
(23, 9, 12, '14.161.42.19', '2026-09-19 17:55:45'),
(24, 10, 14, '115.79.34.19', '2026-09-19 17:55:45'),
(25, 11, 16, '113.190.23.53', '2026-09-19 17:55:45'),
(26, 12, 23, '14.161.42.20', '2026-09-19 17:55:45'),
(27, 14, 24, '115.79.34.20', '2026-09-19 17:55:45'),
(28, 5, 3, '113.190.23.54', '2026-09-19 17:55:45'),
(29, 6, 8, '14.161.42.21', '2026-09-19 17:55:45'),
(30, 7, 1, '115.79.34.21', '2026-09-19 17:55:45'),
(31, 21, 31, '113.190.23.55', '2026-09-19 17:55:45'),
(32, 22, 31, '14.161.42.22', '2026-09-19 17:55:45'),
(33, 23, 31, '115.79.34.22', '2026-09-19 17:55:45'),
(34, 24, 33, '113.190.23.56', '2026-09-19 17:55:45'),
(35, 25, 33, '14.161.42.23', '2026-09-19 17:55:45'),
(36, 26, 33, '115.79.34.23', '2026-09-19 17:55:45'),
(37, 27, 33, '113.190.23.57', '2026-09-19 17:55:45'),
(38, 28, 36, '14.161.42.24', '2026-09-19 17:55:45'),
(39, 29, 36, '115.79.34.24', '2026-09-19 17:55:45'),
(40, 30, 36, '113.190.23.58', '2026-09-19 17:55:45'),
(41, 31, 39, '14.161.42.25', '2026-09-19 17:55:45'),
(42, 32, 39, '115.79.34.25', '2026-09-19 17:55:45'),
(43, 33, 39, '113.190.23.59', '2026-09-19 17:55:45'),
(44, 34, 39, '14.161.42.26', '2026-09-19 17:55:45'),
(45, 35, 41, '115.79.34.26', '2026-09-19 17:55:45'),
(46, 36, 41, '113.190.23.60', '2026-09-19 17:55:45'),
(47, 37, 41, '14.161.42.27', '2026-09-19 17:55:45'),
(48, 38, 43, '115.79.34.27', '2026-09-19 17:55:45'),
(49, 39, 43, '113.190.23.61', '2026-09-19 17:55:45'),
(50, 40, 46, '14.161.42.28', '2026-09-19 17:55:45'),
(51, 21, 46, '115.79.34.28', '2026-09-19 17:55:45'),
(52, 22, 46, '113.190.23.62', '2026-09-19 17:55:45'),
(53, 23, 46, '14.161.42.29', '2026-09-19 17:55:45'),
(54, 24, 51, '115.79.34.29', '2026-09-19 17:55:45'),
(55, 25, 51, '113.190.23.63', '2026-09-19 17:55:45'),
(56, 26, 51, '14.161.42.30', '2026-09-19 17:55:45'),
(57, 27, 55, '115.79.34.30', '2026-09-19 17:55:45'),
(58, 28, 55, '113.190.23.64', '2026-09-19 17:55:45'),
(59, 29, 55, '14.161.42.31', '2026-09-19 17:55:45'),
(60, 30, 60, '115.79.34.31', '2026-09-19 17:55:45'),
(61, 31, 60, '113.190.23.65', '2026-09-19 17:55:45'),
(62, 32, 62, '14.161.42.32', '2026-09-19 17:55:45'),
(63, 33, 62, '115.79.34.32', '2026-09-19 17:55:45'),
(64, 34, 62, '113.190.23.66', '2026-09-19 17:55:45'),
(65, 35, 62, '14.161.42.33', '2026-09-19 17:55:45'),
(66, 36, 73, '115.79.34.33', '2026-09-19 17:55:45'),
(67, 37, 73, '113.190.23.67', '2026-09-19 17:55:45'),
(68, 38, 73, '14.161.42.34', '2026-09-19 17:55:45'),
(69, 39, 73, '115.79.34.34', '2026-09-19 17:55:45'),
(70, 40, 75, '113.190.23.68', '2026-09-19 17:55:45'),
(71, 21, 75, '14.161.42.35', '2026-09-19 17:55:45'),
(72, 22, 75, '115.79.34.35', '2026-09-19 17:55:45'),
(73, 23, 80, '113.190.23.69', '2026-09-19 17:55:45'),
(74, 24, 80, '14.161.42.36', '2026-09-19 17:55:45'),
(75, 25, 80, '115.79.34.36', '2026-09-19 17:55:45'),
(76, 55, 31, '113.190.23.70', '2026-09-19 17:55:45'),
(77, 56, 33, '14.161.42.37', '2026-09-19 17:55:45'),
(78, 57, 41, '115.79.34.37', '2026-09-19 17:55:45'),
(79, 58, 46, '113.190.23.71', '2026-09-19 17:55:45'),
(80, 59, 51, '14.161.42.38', '2026-09-19 17:55:45'),
(81, 2, 1, '::1', '2026-09-19 18:38:22'),
(82, 2, 39, '::1', '2026-09-19 19:14:07'),
(83, 2, 73, '::1', '2026-09-19 19:23:43'),
(84, 2, 3, '::1', '2026-09-19 19:31:56'),
(85, 2, 29, '::1', '2026-09-19 19:38:17'),
(86, 2, 44, '::1', '2026-09-19 19:38:33'),
(87, 2, 78, '::1', '2026-09-19 20:03:03'),
(88, 2, 6, '::1', '2026-09-19 20:44:59'),
(89, 2, 10, '::1', '2026-09-19 20:51:07'),
(90, 2, 9, '::1', '2026-09-19 23:44:38'),
(91, 1, 75, '::1', '2026-09-20 15:26:08'),
(92, 1, 75, '::1', '2026-09-20 15:29:21');

--
-- Triggers `downloads`
--
DELIMITER $$
CREATE TRIGGER `trg_after_download` AFTER INSERT ON `downloads` FOR EACH ROW BEGIN
    UPDATE products SET download_count = download_count + 1 WHERE id = NEW.product_id;
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

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`user_id`, `product_id`, `created_at`) VALUES
(5, 3, '2026-09-19 17:55:45'),
(5, 7, '2026-09-19 17:55:45'),
(5, 11, '2026-09-19 17:55:45'),
(5, 19, '2026-09-19 17:55:45'),
(6, 5, '2026-09-19 17:55:45'),
(6, 9, '2026-09-19 17:55:45'),
(6, 13, '2026-09-19 17:55:45'),
(6, 25, '2026-09-19 17:55:45'),
(7, 1, '2026-09-19 17:55:45'),
(7, 15, '2026-09-19 17:55:45'),
(7, 21, '2026-09-19 17:55:45'),
(7, 29, '2026-09-19 17:55:45'),
(8, 5, '2026-09-19 17:55:45'),
(8, 7, '2026-09-19 17:55:45'),
(8, 19, '2026-09-19 17:55:45'),
(9, 9, '2026-09-19 17:55:45'),
(9, 11, '2026-09-19 17:55:45'),
(9, 20, '2026-09-19 17:55:45'),
(10, 13, '2026-09-19 17:55:45'),
(10, 25, '2026-09-19 17:55:45'),
(10, 26, '2026-09-19 17:55:45'),
(11, 15, '2026-09-19 17:55:45'),
(11, 27, '2026-09-19 17:55:45'),
(11, 29, '2026-09-19 17:55:45'),
(12, 17, '2026-09-19 17:55:45'),
(12, 21, '2026-09-19 17:55:45'),
(12, 30, '2026-09-19 17:55:45'),
(14, 2, '2026-09-19 17:55:45'),
(14, 4, '2026-09-19 17:55:45'),
(14, 6, '2026-09-19 17:55:45'),
(21, 31, '2026-09-19 17:55:45'),
(21, 33, '2026-09-19 17:55:45'),
(21, 46, '2026-09-19 17:55:45'),
(22, 31, '2026-09-19 17:55:45'),
(22, 36, '2026-09-19 17:55:45'),
(22, 41, '2026-09-19 17:55:45'),
(23, 33, '2026-09-19 17:55:45'),
(23, 43, '2026-09-19 17:55:45'),
(23, 51, '2026-09-19 17:55:45'),
(24, 36, '2026-09-19 17:55:45'),
(24, 46, '2026-09-19 17:55:45'),
(24, 75, '2026-09-19 17:55:45'),
(25, 39, '2026-09-19 17:55:45'),
(25, 51, '2026-09-19 17:55:45'),
(25, 80, '2026-09-19 17:55:45'),
(26, 41, '2026-09-19 17:55:45'),
(26, 55, '2026-09-19 17:55:45'),
(26, 62, '2026-09-19 17:55:45'),
(27, 43, '2026-09-19 17:55:45'),
(27, 60, '2026-09-19 17:55:45'),
(27, 73, '2026-09-19 17:55:45'),
(28, 46, '2026-09-19 17:55:45'),
(28, 62, '2026-09-19 17:55:45'),
(28, 75, '2026-09-19 17:55:45'),
(29, 51, '2026-09-19 17:55:45'),
(29, 73, '2026-09-19 17:55:45'),
(29, 80, '2026-09-19 17:55:45'),
(30, 55, '2026-09-19 17:55:45'),
(30, 75, '2026-09-19 17:55:45'),
(30, 80, '2026-09-19 17:55:45'),
(31, 31, '2026-09-19 17:55:45'),
(31, 39, '2026-09-19 17:55:45'),
(31, 62, '2026-09-19 17:55:45'),
(32, 33, '2026-09-19 17:55:45'),
(32, 41, '2026-09-19 17:55:45'),
(32, 73, '2026-09-19 17:55:45'),
(33, 36, '2026-09-19 17:55:45'),
(33, 43, '2026-09-19 17:55:45'),
(33, 75, '2026-09-19 17:55:45'),
(34, 39, '2026-09-19 17:55:45'),
(34, 46, '2026-09-19 17:55:45'),
(34, 80, '2026-09-19 17:55:45'),
(35, 41, '2026-09-19 17:55:45'),
(35, 51, '2026-09-19 17:55:45'),
(35, 62, '2026-09-19 17:55:45'),
(36, 43, '2026-09-19 17:55:45'),
(36, 55, '2026-09-19 17:55:45'),
(36, 73, '2026-09-19 17:55:45'),
(37, 46, '2026-09-19 17:55:45'),
(37, 60, '2026-09-19 17:55:45'),
(37, 75, '2026-09-19 17:55:45'),
(38, 51, '2026-09-19 17:55:45'),
(38, 62, '2026-09-19 17:55:45'),
(38, 80, '2026-09-19 17:55:45'),
(39, 55, '2026-09-19 17:55:45'),
(39, 73, '2026-09-19 17:55:45'),
(39, 75, '2026-09-19 17:55:45'),
(40, 60, '2026-09-19 17:55:45'),
(40, 75, '2026-09-19 17:55:45'),
(40, 80, '2026-09-19 17:55:45'),
(55, 31, '2026-09-19 17:55:45'),
(55, 33, '2026-09-19 17:55:45'),
(55, 41, '2026-09-19 17:55:45'),
(56, 36, '2026-09-19 17:55:45'),
(56, 43, '2026-09-19 17:55:45'),
(56, 46, '2026-09-19 17:55:45'),
(57, 39, '2026-09-19 17:55:45'),
(57, 51, '2026-09-19 17:55:45'),
(57, 55, '2026-09-19 17:55:45'),
(58, 41, '2026-09-19 17:55:45'),
(58, 60, '2026-09-19 17:55:45'),
(58, 62, '2026-09-19 17:55:45'),
(59, 43, '2026-09-19 17:55:45'),
(59, 73, '2026-09-19 17:55:45'),
(59, 75, '2026-09-19 17:55:45'),
(60, 46, '2026-09-19 17:55:45'),
(60, 75, '2026-09-19 17:55:45'),
(60, 80, '2026-09-19 17:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `kyc_documents`
--

CREATE TABLE `kyc_documents` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `front_image_url` varchar(500) NOT NULL,
  `back_image_url` varchar(500) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `rejection_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kyc_documents`
--

INSERT INTO `kyc_documents` (`id`, `user_id`, `document_type`, `front_image_url`, `back_image_url`, `status`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 2, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User2', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User2', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(2, 3, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User3', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User3', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(3, 4, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User4', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User4', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(4, 15, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User15', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User15', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(5, 16, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User16', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User16', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(6, 17, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User17', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User17', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(7, 5, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User5', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User5', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(8, 6, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User6', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User6', 3, 'Ảnh mặt trước bị mờ, vui lòng chụp lại.', '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(9, 41, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User41', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User41', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(10, 42, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User42', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User42', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(11, 43, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User43', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User43', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(12, 44, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User44', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User44', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(13, 45, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User45', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User45', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(14, 46, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User46', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User46', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(15, 47, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User47', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User47', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(16, 48, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User48', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User48', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(17, 49, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User49', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User49', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(18, 50, 'CMND/CCCD', 'https://placehold.co/600x400/64748B/white?text=CCCD+Front+User50', 'https://placehold.co/600x400/64748B/white?text=CCCD+Back+User50', 2, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45');

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
  `status` tinyint(4) NOT NULL COMMENT '1:Pending, 2:Paid, 3:Cancelled, 4:Refunded, 5:Received',
  `order_expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `product_id`, `total_amount`, `platform_fee`, `seller_amount`, `status`, `order_expires_at`, `created_at`) VALUES
(1, 'ORD-2026-0001', 5, 1, 1500000.0000, 75000.0000, 1425000.0000, 2, NULL, '2026-09-19 17:55:45'),
(2, 'ORD-2026-0002', 6, 3, 450000.0000, 22500.0000, 427500.0000, 2, NULL, '2026-09-19 17:55:45'),
(3, 'ORD-2026-0003', 7, 6, 199000.0000, 9950.0000, 189050.0000, 2, NULL, '2026-09-19 17:55:45'),
(4, 'ORD-2026-0004', 8, 5, 600000.0000, 30000.0000, 570000.0000, 2, NULL, '2026-09-19 17:55:45'),
(5, 'ORD-2026-0005', 9, 7, 2200000.0000, 110000.0000, 2090000.0000, 2, NULL, '2026-09-19 17:55:45'),
(6, 'ORD-2026-0006', 10, 9, 380000.0000, 19000.0000, 361000.0000, 2, NULL, '2026-09-19 17:55:45'),
(7, 'ORD-2026-0007', 11, 11, 750000.0000, 37500.0000, 712500.0000, 2, NULL, '2026-09-19 17:55:45'),
(8, 'ORD-2026-0008', 12, 13, 2500000.0000, 125000.0000, 2375000.0000, 2, NULL, '2026-09-19 17:55:45'),
(9, 'ORD-2026-0009', 14, 15, 550000.0000, 27500.0000, 522500.0000, 2, NULL, '2026-09-19 17:55:45'),
(10, 'ORD-2026-0010', 5, 19, 1350000.0000, 67500.0000, 1282500.0000, 2, NULL, '2026-09-19 17:55:45'),
(11, 'ORD-2026-0011', 6, 20, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(12, 'ORD-2026-0012', 7, 21, 650000.0000, 32500.0000, 617500.0000, 2, NULL, '2026-09-19 17:55:45'),
(13, 'ORD-2026-0013', 8, 22, 1100000.0000, 55000.0000, 1045000.0000, 2, NULL, '2026-09-19 17:55:45'),
(14, 'ORD-2026-0014', 9, 25, 520000.0000, 26000.0000, 494000.0000, 2, NULL, '2026-09-19 17:55:45'),
(15, 'ORD-2026-0015', 10, 26, 980000.0000, 49000.0000, 931000.0000, 2, NULL, '2026-09-19 17:55:45'),
(16, 'ORD-2026-0016', 11, 27, 290000.0000, 14500.0000, 275500.0000, 2, NULL, '2026-09-19 17:55:45'),
(17, 'ORD-2026-0017', 12, 29, 890000.0000, 44500.0000, 845500.0000, 2, NULL, '2026-09-19 17:55:45'),
(18, 'ORD-2026-0018', 14, 30, 3200000.0000, 160000.0000, 3040000.0000, 2, NULL, '2026-09-19 17:55:45'),
(19, 'ORD-2026-0019', 5, 2, 850000.0000, 42500.0000, 807500.0000, 1, '2026-09-20 17:55:45', '2026-09-19 17:55:45'),
(20, 'ORD-2026-0020', 6, 4, 250000.0000, 12500.0000, 237500.0000, 3, NULL, '2026-09-19 17:55:45'),
(21, 'ORD-2026-0021', 7, 6, 199000.0000, 9950.0000, 189050.0000, 2, NULL, '2026-09-19 17:55:45'),
(22, 'ORD-2026-0022', 8, 10, 320000.0000, 16000.0000, 304000.0000, 2, NULL, '2026-09-19 17:55:45'),
(23, 'ORD-2026-0023', 9, 12, 1200000.0000, 60000.0000, 1140000.0000, 2, NULL, '2026-09-19 17:55:45'),
(24, 'ORD-2026-0024', 10, 14, 1950000.0000, 97500.0000, 1852500.0000, 1, '2026-09-21 17:55:45', '2026-09-19 17:55:45'),
(25, 'ORD-2026-0025', 11, 16, 480000.0000, 24000.0000, 456000.0000, 2, NULL, '2026-09-19 17:55:45'),
(26, 'ORD-2026-0026', 12, 23, 1650000.0000, 82500.0000, 1567500.0000, 2, NULL, '2026-09-19 17:55:45'),
(27, 'ORD-2026-0027', 14, 24, 350000.0000, 17500.0000, 332500.0000, 2, NULL, '2026-09-19 17:55:45'),
(28, 'ORD-2026-0028', 5, 3, 450000.0000, 22500.0000, 427500.0000, 2, NULL, '2026-09-19 17:55:45'),
(29, 'ORD-2026-0029', 6, 8, 1800000.0000, 90000.0000, 1710000.0000, 1, '2026-09-22 17:55:45', '2026-09-19 17:55:45'),
(30, 'ORD-2026-0030', 7, 1, 1500000.0000, 75000.0000, 1425000.0000, 2, NULL, '2026-09-19 17:55:45'),
(31, 'ORD-2026-0031', 21, 31, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(32, 'ORD-2026-0032', 22, 31, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(33, 'ORD-2026-0033', 23, 31, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(34, 'ORD-2026-0034', 24, 33, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(35, 'ORD-2026-0035', 25, 33, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(36, 'ORD-2026-0036', 26, 33, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(37, 'ORD-2026-0037', 27, 33, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(38, 'ORD-2026-0038', 28, 36, 4200000.0000, 210000.0000, 3990000.0000, 2, NULL, '2026-09-19 17:55:45'),
(39, 'ORD-2026-0039', 29, 36, 4200000.0000, 210000.0000, 3990000.0000, 2, NULL, '2026-09-19 17:55:45'),
(40, 'ORD-2026-0040', 30, 36, 4200000.0000, 210000.0000, 3990000.0000, 2, NULL, '2026-09-19 17:55:45'),
(41, 'ORD-2026-0041', 31, 39, 1200000.0000, 60000.0000, 1140000.0000, 2, NULL, '2026-09-19 17:55:45'),
(42, 'ORD-2026-0042', 32, 39, 1200000.0000, 60000.0000, 1140000.0000, 2, NULL, '2026-09-19 17:55:45'),
(43, 'ORD-2026-0043', 33, 39, 1200000.0000, 60000.0000, 1140000.0000, 2, NULL, '2026-09-19 17:55:45'),
(44, 'ORD-2026-0044', 34, 39, 1200000.0000, 60000.0000, 1140000.0000, 2, NULL, '2026-09-19 17:55:45'),
(45, 'ORD-2026-0045', 35, 41, 1850000.0000, 92500.0000, 1757500.0000, 2, NULL, '2026-09-19 17:55:45'),
(46, 'ORD-2026-0046', 36, 41, 1850000.0000, 92500.0000, 1757500.0000, 2, NULL, '2026-09-19 17:55:45'),
(47, 'ORD-2026-0047', 37, 41, 1850000.0000, 92500.0000, 1757500.0000, 2, NULL, '2026-09-19 17:55:45'),
(48, 'ORD-2026-0048', 38, 43, 3200000.0000, 160000.0000, 3040000.0000, 2, NULL, '2026-09-19 17:55:45'),
(49, 'ORD-2026-0049', 39, 43, 3200000.0000, 160000.0000, 3040000.0000, 2, NULL, '2026-09-19 17:55:45'),
(50, 'ORD-2026-0050', 40, 46, 3500000.0000, 175000.0000, 3325000.0000, 2, NULL, '2026-09-19 17:55:45'),
(51, 'ORD-2026-0051', 21, 46, 3500000.0000, 175000.0000, 3325000.0000, 2, NULL, '2026-09-19 17:55:45'),
(52, 'ORD-2026-0052', 22, 46, 3500000.0000, 175000.0000, 3325000.0000, 2, NULL, '2026-09-19 17:55:45'),
(53, 'ORD-2026-0053', 23, 46, 3500000.0000, 175000.0000, 3325000.0000, 2, NULL, '2026-09-19 17:55:45'),
(54, 'ORD-2026-0054', 24, 51, 3800000.0000, 190000.0000, 3610000.0000, 2, NULL, '2026-09-19 17:55:45'),
(55, 'ORD-2026-0055', 25, 51, 3800000.0000, 190000.0000, 3610000.0000, 2, NULL, '2026-09-19 17:55:45'),
(56, 'ORD-2026-0056', 26, 51, 3800000.0000, 190000.0000, 3610000.0000, 2, NULL, '2026-09-19 17:55:45'),
(57, 'ORD-2026-0057', 27, 55, 1800000.0000, 90000.0000, 1710000.0000, 2, NULL, '2026-09-19 17:55:45'),
(58, 'ORD-2026-0058', 28, 55, 1800000.0000, 90000.0000, 1710000.0000, 2, NULL, '2026-09-19 17:55:45'),
(59, 'ORD-2026-0059', 29, 55, 1800000.0000, 90000.0000, 1710000.0000, 2, NULL, '2026-09-19 17:55:45'),
(60, 'ORD-2026-0060', 30, 60, 1500000.0000, 75000.0000, 1425000.0000, 2, NULL, '2026-09-19 17:55:45'),
(61, 'ORD-2026-0061', 31, 60, 1500000.0000, 75000.0000, 1425000.0000, 2, NULL, '2026-09-19 17:55:45'),
(62, 'ORD-2026-0062', 32, 62, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(63, 'ORD-2026-0063', 33, 62, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(64, 'ORD-2026-0064', 34, 62, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(65, 'ORD-2026-0065', 35, 62, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(66, 'ORD-2026-0066', 36, 73, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(67, 'ORD-2026-0067', 37, 73, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(68, 'ORD-2026-0068', 38, 73, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(69, 'ORD-2026-0069', 39, 73, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(70, 'ORD-2026-0070', 40, 75, 5500000.0000, 275000.0000, 5225000.0000, 2, NULL, '2026-09-19 17:55:45'),
(71, 'ORD-2026-0071', 21, 75, 5500000.0000, 275000.0000, 5225000.0000, 2, NULL, '2026-09-19 17:55:45'),
(72, 'ORD-2026-0072', 22, 75, 5500000.0000, 275000.0000, 5225000.0000, 2, NULL, '2026-09-19 17:55:45'),
(73, 'ORD-2026-0073', 23, 80, 4800000.0000, 240000.0000, 4560000.0000, 2, NULL, '2026-09-19 17:55:45'),
(74, 'ORD-2026-0074', 24, 80, 4800000.0000, 240000.0000, 4560000.0000, 2, NULL, '2026-09-19 17:55:45'),
(75, 'ORD-2026-0075', 25, 80, 4800000.0000, 240000.0000, 4560000.0000, 2, NULL, '2026-09-19 17:55:45'),
(76, 'ORD-2026-0076', 55, 31, 420000.0000, 21000.0000, 399000.0000, 2, NULL, '2026-09-19 17:55:45'),
(77, 'ORD-2026-0077', 56, 33, 2800000.0000, 140000.0000, 2660000.0000, 2, NULL, '2026-09-19 17:55:45'),
(78, 'ORD-2026-0078', 57, 41, 1850000.0000, 92500.0000, 1757500.0000, 2, NULL, '2026-09-19 17:55:45'),
(79, 'ORD-2026-0079', 58, 46, 3500000.0000, 175000.0000, 3325000.0000, 2, NULL, '2026-09-19 17:55:45'),
(80, 'ORD-2026-0080', 59, 51, 3800000.0000, 190000.0000, 3610000.0000, 2, NULL, '2026-09-19 17:55:45'),
(81, 'ORD-2026-1789817913', 2, 23, 1650000.0000, 82500.0000, 1567500.0000, 4, NULL, '2026-09-19 18:38:33'),
(82, 'ORD-2026-1789817964', 2, 39, 1200000.0000, 60000.0000, 1140000.0000, 1, NULL, '2026-09-19 18:39:24'),
(83, 'ORD-2026-1789820608', 2, 73, 420000.0000, 21000.0000, 399000.0000, 1, NULL, '2026-09-19 19:23:28'),
(84, 'ORD-2026-1789821107', 2, 3, 450000.0000, 22500.0000, 427500.0000, 5, NULL, '2026-09-19 19:31:47'),
(85, 'ORD-2026-1789821506', 2, 44, 2400000.0000, 120000.0000, 2280000.0000, 5, NULL, '2026-09-19 19:38:26'),
(86, 'ORD-2026-1789822964', 2, 78, 1200000.0000, 60000.0000, 1140000.0000, 5, NULL, '2026-09-19 20:02:44'),
(87, 'ORD-2026-1789825431', 2, 6, 199000.0000, 9950.0000, 189050.0000, 5, NULL, '2026-09-19 20:43:51'),
(88, 'ORD-2026-1789825843', 2, 10, 320000.0000, 16000.0000, 304000.0000, 5, NULL, '2026-09-19 20:50:43'),
(89, 'ORD-2026-1789825912', 2, 9, 380000.0000, 19000.0000, 361000.0000, 5, NULL, '2026-09-19 20:51:52'),
(90, 'ORD-2026-1789890993', 2, 80, 4800000.0000, 240000.0000, 4560000.0000, 4, NULL, '2026-09-20 14:56:33'),
(91, 'ORD-2026-1789891460', 2, 78, 1200000.0000, 60000.0000, 1140000.0000, 2, NULL, '2026-09-20 15:04:20'),
(92, 'ORD-2026-1789891608', 1, 1, 1500000.0000, 75000.0000, 1425000.0000, 4, NULL, '2026-09-20 15:06:48'),
(93, 'ORD-2026-1789892150', 1, 8, 1800000.0000, 90000.0000, 1710000.0000, 4, NULL, '2026-09-20 15:15:50'),
(94, 'ORD-2026-1789892519', 1, 22, 1100000.0000, 55000.0000, 1045000.0000, 4, NULL, '2026-09-20 15:21:59'),
(95, 'ORD-2026-1789892765', 1, 75, 5500000.0000, 275000.0000, 5225000.0000, 5, NULL, '2026-09-20 15:26:05');

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
(1, 1, 1, 'Hệ thống Quản lý Khách sạn Cao cấp (ReactJS + Node.js)', 1, 1500000.0000, 1500000.0000, 75000.0000, 1425000.0000, '2026-09-19 17:55:45'),
(2, 2, 3, 'Bộ UI Kit E-Commerce Premium 2026 (Figma)', 1, 450000.0000, 450000.0000, 22500.0000, 427500.0000, '2026-09-19 17:55:45'),
(3, 3, 6, 'Ebook: Nắm vững ReactJS qua 10 dự án thực tế', 1, 199000.0000, 199000.0000, 9950.0000, 189050.0000, '2026-09-19 17:55:45'),
(4, 4, 5, 'Template Admin Dashboard TailwindCSS & Next.js', 1, 600000.0000, 600000.0000, 30000.0000, 570000.0000, '2026-09-19 17:55:45'),
(5, 5, 7, 'API Gateway & Microservices Node.js', 1, 2200000.0000, 2200000.0000, 110000.0000, 2090000.0000, '2026-09-19 17:55:45'),
(6, 6, 9, 'Landing Page UI Kit 2026 (Figma)', 1, 380000.0000, 380000.0000, 19000.0000, 361000.0000, '2026-09-19 17:55:45'),
(7, 7, 11, 'Vue 3 + Vite Admin Template', 1, 750000.0000, 750000.0000, 37500.0000, 712500.0000, '2026-09-19 17:55:45'),
(8, 8, 13, 'Terraform AWS Infrastructure as Code', 1, 2500000.0000, 2500000.0000, 125000.0000, 2375000.0000, '2026-09-19 17:55:45'),
(9, 9, 15, 'After Effects Motion Pack Vol.1', 1, 550000.0000, 550000.0000, 27500.0000, 522500.0000, '2026-09-19 17:55:45'),
(10, 10, 19, 'Flutter E-commerce App Full Source', 1, 1350000.0000, 1350000.0000, 67500.0000, 1282500.0000, '2026-09-19 17:55:45'),
(11, 11, 20, 'Dashboard UI Kit Dark Mode', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(12, 12, 21, 'Đồ án tốt nghiệp CNTT mẫu (Full báo cáo + code)', 1, 650000.0000, 650000.0000, 32500.0000, 617500.0000, '2026-09-19 17:55:45'),
(13, 13, 22, 'Hệ thống Quản lý Nhân sự (HRM) PHP Laravel', 1, 1100000.0000, 1100000.0000, 55000.0000, 1045000.0000, '2026-09-19 17:55:45'),
(14, 14, 25, 'Mobile App UI Kit iOS 18', 1, 520000.0000, 520000.0000, 26000.0000, 494000.0000, '2026-09-19 17:55:45'),
(15, 15, 26, 'Real-time Chat App (Socket.io + React)', 1, 980000.0000, 980000.0000, 49000.0000, 931000.0000, '2026-09-19 17:55:45'),
(16, 16, 27, 'Pixel Art Game Asset Pack', 1, 290000.0000, 290000.0000, 14500.0000, 275500.0000, '2026-09-19 17:55:45'),
(17, 17, 29, 'Khóa học ReactJS nâng cao (Video + Source)', 1, 890000.0000, 890000.0000, 44500.0000, 845500.0000, '2026-09-19 17:55:45'),
(18, 18, 30, 'Spring Boot Microservices E-commerce', 1, 3200000.0000, 3200000.0000, 160000.0000, 3040000.0000, '2026-09-19 17:55:45'),
(19, 19, 2, 'Website Bán Hàng E-Commerce Laravel 10 (Full API + CMS)', 1, 850000.0000, 850000.0000, 42500.0000, 807500.0000, '2026-09-19 17:55:45'),
(20, 20, 4, 'Mega Icon Pack - 3000+ Vector Icons', 1, 250000.0000, 250000.0000, 12500.0000, 237500.0000, '2026-09-19 17:55:45'),
(21, 21, 6, 'Ebook: Nắm vững ReactJS qua 10 dự án thực tế', 1, 199000.0000, 199000.0000, 9950.0000, 189050.0000, '2026-09-19 17:55:45'),
(22, 22, 10, 'Social Media Marketing Kit', 1, 320000.0000, 320000.0000, 16000.0000, 304000.0000, '2026-09-19 17:55:45'),
(23, 23, 12, 'Game 2D Platformer Full Source (Unity)', 1, 1200000.0000, 1200000.0000, 60000.0000, 1140000.0000, '2026-09-19 17:55:45'),
(24, 24, 14, 'NestJS GraphQL E-commerce API', 1, 1950000.0000, 1950000.0000, 97500.0000, 1852500.0000, '2026-09-19 17:55:45'),
(25, 25, 16, 'Premiere Pro Wedding Template', 1, 480000.0000, 480000.0000, 24000.0000, 456000.0000, '2026-09-19 17:55:45'),
(26, 26, 23, 'Shopify App Boilerplate', 1, 1650000.0000, 1650000.0000, 82500.0000, 1567500.0000, '2026-09-19 17:55:45'),
(27, 27, 24, 'Email Marketing Template Pack', 1, 350000.0000, 350000.0000, 17500.0000, 332500.0000, '2026-09-19 17:55:45'),
(28, 28, 3, 'Bộ UI Kit E-Commerce Premium 2026 (Figma)', 1, 450000.0000, 450000.0000, 22500.0000, 427500.0000, '2026-09-19 17:55:45'),
(29, 29, 8, 'Docker & Kubernetes Full Stack Deployment', 1, 1800000.0000, 1800000.0000, 90000.0000, 1710000.0000, '2026-09-19 17:55:45'),
(30, 30, 1, 'Hệ thống Quản lý Khách sạn Cao cấp (ReactJS + Node.js)', 1, 1500000.0000, 1500000.0000, 75000.0000, 1425000.0000, '2026-09-19 17:55:45'),
(31, 31, 31, 'Pitch Deck Template Startup 2026', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(32, 32, 31, 'Pitch Deck Template Startup 2026', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(33, 33, 31, 'Pitch Deck Template Startup 2026', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(34, 34, 33, 'SaaS MVP Boilerplate (Next.js + Stripe)', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(35, 35, 33, 'SaaS MVP Boilerplate (Next.js + Stripe)', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(36, 36, 33, 'SaaS MVP Boilerplate (Next.js + Stripe)', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(37, 37, 33, 'SaaS MVP Boilerplate (Next.js + Stripe)', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(38, 38, 36, 'Credit Scoring ML Model', 1, 4200000.0000, 4200000.0000, 210000.0000, 3990000.0000, '2026-09-19 17:55:45'),
(39, 39, 36, 'Credit Scoring ML Model', 1, 4200000.0000, 4200000.0000, 210000.0000, 3990000.0000, '2026-09-19 17:55:45'),
(40, 40, 36, 'Credit Scoring ML Model', 1, 4200000.0000, 4200000.0000, 210000.0000, 3990000.0000, '2026-09-19 17:55:45'),
(41, 41, 39, 'Khóa học Python for Data Science', 1, 1200000.0000, 1200000.0000, 60000.0000, 1140000.0000, '2026-09-19 17:55:45'),
(42, 42, 39, 'Khóa học Python for Data Science', 1, 1200000.0000, 1200000.0000, 60000.0000, 1140000.0000, '2026-09-19 17:55:45'),
(43, 43, 39, 'Khóa học Python for Data Science', 1, 1200000.0000, 1200000.0000, 60000.0000, 1140000.0000, '2026-09-19 17:55:45'),
(44, 44, 39, 'Khóa học Python for Data Science', 1, 1200000.0000, 1200000.0000, 60000.0000, 1140000.0000, '2026-09-19 17:55:45'),
(45, 45, 41, 'Flutter Food Delivery App', 1, 1850000.0000, 1850000.0000, 92500.0000, 1757500.0000, '2026-09-19 17:55:45'),
(46, 46, 41, 'Flutter Food Delivery App', 1, 1850000.0000, 1850000.0000, 92500.0000, 1757500.0000, '2026-09-19 17:55:45'),
(47, 47, 41, 'Flutter Food Delivery App', 1, 1850000.0000, 1850000.0000, 92500.0000, 1757500.0000, '2026-09-19 17:55:45'),
(48, 48, 43, 'Flutter Ride Hailing App', 1, 3200000.0000, 3200000.0000, 160000.0000, 3040000.0000, '2026-09-19 17:55:45'),
(49, 49, 43, 'Flutter Ride Hailing App', 1, 3200000.0000, 3200000.0000, 160000.0000, 3040000.0000, '2026-09-19 17:55:45'),
(50, 50, 46, 'Kubernetes Production Setup', 1, 3500000.0000, 3500000.0000, 175000.0000, 3325000.0000, '2026-09-19 17:55:45'),
(51, 51, 46, 'Kubernetes Production Setup', 1, 3500000.0000, 3500000.0000, 175000.0000, 3325000.0000, '2026-09-19 17:55:45'),
(52, 52, 46, 'Kubernetes Production Setup', 1, 3500000.0000, 3500000.0000, 175000.0000, 3325000.0000, '2026-09-19 17:55:45'),
(53, 53, 46, 'Kubernetes Production Setup', 1, 3500000.0000, 3500000.0000, 175000.0000, 3325000.0000, '2026-09-19 17:55:45'),
(54, 54, 51, 'NFT Marketplace Smart Contract', 1, 3800000.0000, 3800000.0000, 190000.0000, 3610000.0000, '2026-09-19 17:55:45'),
(55, 55, 51, 'NFT Marketplace Smart Contract', 1, 3800000.0000, 3800000.0000, 190000.0000, 3610000.0000, '2026-09-19 17:55:45'),
(56, 56, 51, 'NFT Marketplace Smart Contract', 1, 3800000.0000, 3800000.0000, 190000.0000, 3610000.0000, '2026-09-19 17:55:45'),
(57, 57, 55, '3D Character Model Pack', 1, 1800000.0000, 1800000.0000, 90000.0000, 1710000.0000, '2026-09-19 17:55:45'),
(58, 58, 55, '3D Character Model Pack', 1, 1800000.0000, 1800000.0000, 90000.0000, 1710000.0000, '2026-09-19 17:55:45'),
(59, 59, 55, '3D Character Model Pack', 1, 1800000.0000, 1800000.0000, 90000.0000, 1710000.0000, '2026-09-19 17:55:45'),
(60, 60, 60, 'Pentest Checklist & Report', 1, 1500000.0000, 1500000.0000, 75000.0000, 1425000.0000, '2026-09-19 17:55:45'),
(61, 61, 60, 'Pentest Checklist & Report', 1, 1500000.0000, 1500000.0000, 75000.0000, 1425000.0000, '2026-09-19 17:55:45'),
(62, 62, 62, 'Khóa học Ethical Hacking', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(63, 63, 62, 'Khóa học Ethical Hacking', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(64, 64, 62, 'Khóa học Ethical Hacking', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(65, 65, 62, 'Khóa học Ethical Hacking', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(66, 66, 73, 'TikTok Content Template', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(67, 67, 73, 'TikTok Content Template', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(68, 68, 73, 'TikTok Content Template', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(69, 69, 73, 'TikTok Content Template', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(70, 70, 75, 'ERP System Full Source (Laravel + Vue)', 1, 5500000.0000, 5500000.0000, 275000.0000, 5225000.0000, '2026-09-19 17:55:45'),
(71, 71, 75, 'ERP System Full Source (Laravel + Vue)', 1, 5500000.0000, 5500000.0000, 275000.0000, 5225000.0000, '2026-09-19 17:55:45'),
(72, 72, 75, 'ERP System Full Source (Laravel + Vue)', 1, 5500000.0000, 5500000.0000, 275000.0000, 5225000.0000, '2026-09-19 17:55:45'),
(73, 73, 80, 'Fintech Wallet Full Source', 1, 4800000.0000, 4800000.0000, 240000.0000, 4560000.0000, '2026-09-19 17:55:45'),
(74, 74, 80, 'Fintech Wallet Full Source', 1, 4800000.0000, 4800000.0000, 240000.0000, 4560000.0000, '2026-09-19 17:55:45'),
(75, 75, 80, 'Fintech Wallet Full Source', 1, 4800000.0000, 4800000.0000, 240000.0000, 4560000.0000, '2026-09-19 17:55:45'),
(76, 76, 31, 'Pitch Deck Template Startup 2026', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 17:55:45'),
(77, 77, 33, 'SaaS MVP Boilerplate (Next.js + Stripe)', 1, 2800000.0000, 2800000.0000, 140000.0000, 2660000.0000, '2026-09-19 17:55:45'),
(78, 78, 41, 'Flutter Food Delivery App', 1, 1850000.0000, 1850000.0000, 92500.0000, 1757500.0000, '2026-09-19 17:55:45'),
(79, 79, 46, 'Kubernetes Production Setup', 1, 3500000.0000, 3500000.0000, 175000.0000, 3325000.0000, '2026-09-19 17:55:45'),
(80, 80, 51, 'NFT Marketplace Smart Contract', 1, 3800000.0000, 3800000.0000, 190000.0000, 3610000.0000, '2026-09-19 17:55:45'),
(81, 81, 23, 'Shopify App Boilerplate', 1, 1650000.0000, 1650000.0000, 82500.0000, 1567500.0000, '2026-09-19 18:38:33'),
(82, 82, 39, 'Khóa học Python for Data Science', 1, 1200000.0000, 1200000.0000, 60000.0000, 1140000.0000, '2026-09-19 18:39:24'),
(83, 83, 73, 'TikTok Content Template', 1, 420000.0000, 420000.0000, 21000.0000, 399000.0000, '2026-09-19 19:23:28'),
(84, 84, 3, 'Bộ UI Kit E-Commerce Premium 2026 (Figma)', 1, 450000.0000, 450000.0000, 22500.0000, 427500.0000, '2026-09-19 19:31:47'),
(85, 85, 44, 'Swift iOS Social Network', 1, 2400000.0000, 2400000.0000, 120000.0000, 2280000.0000, '2026-09-19 19:38:26'),
(86, 86, 78, 'Brand Identity Kit', 1, 1200000.0000, 1200000.0000, 60000.0000, 1140000.0000, '2026-09-19 20:02:44'),
(87, 87, 6, 'Ebook: Nắm vững ReactJS qua 10 dự án thực tế', 1, 199000.0000, 199000.0000, 9950.0000, 189050.0000, '2026-09-19 20:43:51'),
(88, 88, 10, 'Social Media Marketing Kit', 1, 320000.0000, 320000.0000, 16000.0000, 304000.0000, '2026-09-19 20:50:43'),
(89, 89, 9, 'Landing Page UI Kit 2026 (Figma)', 1, 380000.0000, 380000.0000, 19000.0000, 361000.0000, '2026-09-19 20:51:52'),
(90, 90, 80, 'Fintech Wallet Full Source', 1, 4800000.0000, 4800000.0000, 240000.0000, 4560000.0000, '2026-09-20 14:56:33'),
(91, 91, 78, 'Brand Identity Kit', 1, 1200000.0000, 1200000.0000, 60000.0000, 1140000.0000, '2026-09-20 15:04:20'),
(92, 92, 1, 'Hệ thống Quản lý Khách sạn Cao cấp (ReactJS + Node.js)', 1, 1500000.0000, 1500000.0000, 75000.0000, 1425000.0000, '2026-09-20 15:06:48'),
(93, 93, 8, 'Docker & Kubernetes Full Stack Deployment', 1, 1800000.0000, 1800000.0000, 90000.0000, 1710000.0000, '2026-09-20 15:15:50'),
(94, 94, 22, 'Hệ thống Quản lý Nhân sự (HRM) PHP Laravel', 1, 1100000.0000, 1100000.0000, 55000.0000, 1045000.0000, '2026-09-20 15:21:59'),
(95, 95, 75, 'ERP System Full Source (Laravel + Vue)', 1, 5500000.0000, 5500000.0000, 275000.0000, 5225000.0000, '2026-09-20 15:26:05');

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

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 5, 'tok_reset_user5_abc123', '2026-09-19 18:55:45', '2026-09-19 17:55:45'),
(2, 6, 'tok_reset_user6_def456', '2026-09-19 19:55:45', '2026-09-19 17:55:45'),
(3, 10, 'tok_reset_user10_ghi789', '2026-09-19 18:25:45', '2026-09-19 17:55:45'),
(4, 14, 'tok_reset_user14_jkl012', '2026-09-19 20:55:45', '2026-09-19 17:55:45'),
(5, 21, 'tok_reset_user21_mno345', '2026-09-19 18:55:45', '2026-09-19 17:55:45'),
(6, 22, 'tok_reset_user22_pqr678', '2026-09-19 19:55:45', '2026-09-19 17:55:45'),
(7, 23, 'tok_reset_user23_stu901', '2026-09-19 18:25:45', '2026-09-19 17:55:45'),
(8, 24, 'tok_reset_user24_vwx234', '2026-09-19 20:55:45', '2026-09-19 17:55:45'),
(9, 55, 'tok_reset_user55_yz5678', '2026-09-19 18:55:45', '2026-09-19 17:55:45'),
(10, 56, 'tok_reset_user56_abc901', '2026-09-19 19:55:45', '2026-09-19 17:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `category_id` bigint(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(19,4) NOT NULL,
  `preview_url` varchar(500) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1 COMMENT '1:Pending, 2:Approved, 3:Rejected',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `store_id`, `category_id`, `title`, `description`, `price`, `preview_url`, `rating`, `review_count`, `download_count`, `status`, `deleted_at`, `created_at`) VALUES
(1, 1, 1, 'Hệ thống Quản lý Khách sạn Cao cấp (ReactJS + Node.js)', 'Source code quản lý khách sạn đầy đủ tính năng: Đặt phòng realtime, quản lý buồng phòng, nhân sự, thống kê doanh thu. Code chuẩn MVC, dễ dàng deploy. Tài liệu hướng dẫn đi kèm cực kỳ chi tiết.', 1500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.69, 8, 27, 2, NULL, '2026-09-19 17:55:45'),
(2, 1, 3, 'Website Bán Hàng E-Commerce Laravel 10 (Full API + CMS)', 'Hệ thống website bán hàng hoàn chỉnh xây dựng bằng Laravel 10 và MySQL. Tích hợp thanh toán VNPay, Momo. Hệ thống Admin quản lý sản phẩm, đơn hàng siêu trực quan.', 850000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.70, 6, 19, 2, NULL, '2026-09-19 17:55:45'),
(3, 2, 2, 'Bộ UI Kit E-Commerce Premium 2026 (Figma)', 'Gồm hơn 150 screens thiết kế riêng cho Mobile App E-commerce. Áp dụng Design System chuẩn Apple, Auto Layout đầy đủ, dễ dàng tùy biến màu sắc và typography.', 450000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 5.00, 5, 38, 2, NULL, '2026-09-19 17:55:45'),
(4, 2, 2, 'Mega Icon Pack - 3000+ Vector Icons', 'Bộ siêu tập 3000+ icon thiết kế theo phong cách line-art tối giản. Hỗ trợ đa định dạng: SVG, PNG, EPS. Cực kỳ phù hợp cho các dự án Web/App.', 250000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.33, 3, 13, 2, NULL, '2026-09-19 17:55:45'),
(5, 3, 1, 'Template Admin Dashboard TailwindCSS & Next.js', 'Dashboard cực mượt và hiện đại được code bằng Next.js 14 và TailwindCSS. Chế độ Dark/Light mode đầy đủ, hỗ trợ sẵn các biểu đồ Chart.js.', 600000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.47, 6, 41, 2, NULL, '2026-09-19 17:55:45'),
(6, 3, 5, 'Ebook: Nắm vững ReactJS qua 10 dự án thực tế', 'Sách điện tử dày 200 trang đúc kết kinh nghiệm làm ReactJS từ con số 0. Hướng dẫn chi tiết từng dòng code để build ra các app thực tế như Todo, Chat App, Movie DB.', 199000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 5.00, 3, 53, 2, NULL, '2026-09-19 17:55:45'),
(7, 1, 1, 'API Gateway & Microservices Node.js', 'Bộ source code API Gateway kèm 5 microservices mẫu (Auth, Product, Order, Payment, Notification). Sử dụng RabbitMQ, Redis, PostgreSQL.', 2200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.68, 5, 16, 2, NULL, '2026-09-19 17:55:45'),
(8, 1, 9, 'Docker & Kubernetes Full Stack Deployment', 'Hướng dẫn + script triển khai full stack app lên K8s. Có sẵn Helm chart, CI/CD GitHub Actions.', 1800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.60, 2, 11, 2, NULL, '2026-09-19 17:55:45'),
(9, 2, 2, 'Landing Page UI Kit 2026 (Figma)', '50+ landing page templates cho SaaS, Startup, Agency. Auto layout, component variants đầy đủ.', 380000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.93, 6, 30, 2, NULL, '2026-09-19 17:55:45'),
(10, 2, 10, 'Social Media Marketing Kit', '100+ template Instagram, Facebook, TikTok. Kèm hướng dẫn sử dụng Canva & Figma.', 320000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.70, 3, 24, 2, NULL, '2026-09-19 17:55:45'),
(11, 3, 1, 'Vue 3 + Vite Admin Template', 'Admin dashboard Vue 3 Composition API, Pinia, TailwindCSS. Hỗ trợ TypeScript.', 750000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.68, 5, 31, 2, NULL, '2026-09-19 17:55:45'),
(12, 3, 7, 'Game 2D Platformer Full Source (Unity)', 'Game platformer 2D hoàn chỉnh, 10 level, có boss, sound, UI. Code C# sạch.', 1200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.50, 2, 9, 2, NULL, '2026-09-19 17:55:45'),
(13, 4, 9, 'Terraform AWS Infrastructure as Code', 'Module Terraform triển khai VPC, EC2, RDS, S3, CloudFront. Best practices AWS.', 2500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 5.00, 3, 13, 2, NULL, '2026-09-19 17:55:45'),
(14, 4, 1, 'NestJS GraphQL E-commerce API', 'Backend API e-commerce với NestJS, GraphQL, Prisma, PostgreSQL. Có auth JWT, RBAC.', 1950000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.70, 1, 7, 2, NULL, '2026-09-19 17:55:45'),
(15, 5, 7, 'After Effects Motion Pack Vol.1', '50 template After Effects: logo reveal, slideshow, infographic, transition.', 550000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.56, 5, 19, 2, NULL, '2026-09-19 17:55:45'),
(16, 5, 7, 'Premiere Pro Wedding Template', 'Template dựng phim cưới chuyên nghiệp, 4K ready, 20 scene.', 480000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 15, 2, NULL, '2026-09-19 17:55:45'),
(17, 6, 8, 'Vietnamese Sentiment Analysis Model', 'Model phân tích cảm xúc tiếng Việt, độ chính xác 92%. Kèm dataset 50k câu.', 3500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 0.00, 0, 0, 1, NULL, '2026-09-19 17:55:45'),
(18, 6, 8, 'Prompt Engineering Mega Pack', '500+ prompt template cho ChatGPT, Midjourney, Stable Diffusion.', 290000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 0.00, 0, 0, 1, NULL, '2026-09-19 17:55:45'),
(19, 1, 6, 'Flutter E-commerce App Full Source', 'App bán hàng Flutter kết nối API Laravel. Có giỏ hàng, thanh toán, push notification.', 1350000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.94, 5, 21, 2, NULL, '2026-09-19 17:55:45'),
(20, 2, 2, 'Dashboard UI Kit Dark Mode', 'UI Kit dashboard 80 screens, dark mode, Figma variables.', 420000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 3, 17, 2, NULL, '2026-09-19 17:55:45'),
(21, 3, 4, 'Đồ án tốt nghiệp CNTT mẫu (Full báo cáo + code)', 'Bộ tài liệu đồ án tốt nghiệp ngành CNTT: báo cáo Word 80 trang, slide, source code demo.', 650000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.27, 3, 26, 2, NULL, '2026-09-19 17:55:45'),
(22, 1, 1, 'Hệ thống Quản lý Nhân sự (HRM) PHP Laravel', 'Source code HRM: chấm công, tính lương, quản lý nhân viên, báo cáo.', 1100000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.73, 3, 12, 2, NULL, '2026-09-19 17:55:45'),
(23, 4, 3, 'Shopify App Boilerplate', 'Boilerplate Shopify app với Node.js, React, Polaris. Đã tích hợp OAuth, webhook.', 1650000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 1, 6, 2, NULL, '2026-09-19 17:55:45'),
(24, 5, 10, 'Email Marketing Template Pack', '30 template email responsive cho các dịp: sale, newsletter, welcome, abandoned cart.', 350000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.50, 2, 10, 2, NULL, '2026-09-19 17:55:45'),
(25, 2, 2, 'Mobile App UI Kit iOS 18', 'UI Kit cho iOS 18, 120 screens, component library đầy đủ.', 520000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.94, 5, 22, 2, NULL, '2026-09-19 17:55:45'),
(26, 1, 1, 'Real-time Chat App (Socket.io + React)', 'Ứng dụng chat realtime: private chat, group chat, gửi file, seen status.', 980000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.60, 4, 14, 2, NULL, '2026-09-19 17:55:45'),
(27, 3, 7, 'Pixel Art Game Asset Pack', '500+ sprite pixel art: nhân vật, quái, item, tilemap, UI.', 290000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.90, 4, 18, 2, NULL, '2026-09-19 17:55:45'),
(28, 6, 8, 'Face Recognition Attendance System', 'Hệ thống điểm danh bằng nhận diện khuôn mặt, Python + OpenCV + Flask.', 2800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 0.00, 0, 0, 3, NULL, '2026-09-19 17:55:45'),
(29, 1, 5, 'Khóa học ReactJS nâng cao (Video + Source)', '10 giờ video + source code, học Custom Hooks, Performance, Testing.', 890000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.78, 6, 35, 2, NULL, '2026-09-19 17:55:45'),
(30, 4, 1, 'Spring Boot Microservices E-commerce', 'Hệ thống e-commerce microservices với Spring Boot, Spring Cloud, Kafka.', 3200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 5.00, 4, 8, 2, NULL, '2026-09-19 17:55:45'),
(31, 8, 10, 'Pitch Deck Template Startup 2026', '50 slide pitch deck chuyên nghiệp cho startup gọi vốn. Có sẵn financial model, market analysis, team slide.', 420000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 7, 49, 2, NULL, '2026-09-19 17:55:45'),
(32, 8, 10, 'Business Plan Template Full', 'Template business plan 40 trang, có financial projection 3 năm, SWOT, competitor analysis.', 380000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.70, 3, 32, 2, NULL, '2026-09-19 17:55:45'),
(33, 8, 1, 'SaaS MVP Boilerplate (Next.js + Stripe)', 'Boilerplate SaaS MVP: auth, subscription, billing Stripe, dashboard, multi-tenant.', 2800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.97, 9, 43, 2, NULL, '2026-09-19 17:55:45'),
(34, 8, 1, 'Marketplace Platform Full Source', 'Source code marketplace đa vendor: seller dashboard, commission, payout, dispute.', 4500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 3, 22, 2, NULL, '2026-09-19 17:55:45'),
(35, 8, 3, 'Subscription Box E-commerce', 'Hệ thống e-commerce subscription box: recurring payment, box customization, delivery schedule.', 2400000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.75, 2, 18, 2, NULL, '2026-09-19 17:55:45'),
(36, 9, 8, 'Credit Scoring ML Model', 'Model ML chấm điểm tín dụng, accuracy 94%, kèm dataset 100k records, feature engineering.', 4200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.78, 6, 18, 2, NULL, '2026-09-19 17:55:45'),
(37, 9, 8, 'Sales Forecasting Notebook', 'Jupyter notebook dự báo doanh số với ARIMA, Prophet, LSTM. Có visualization đầy đủ.', 1800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 12, 2, NULL, '2026-09-19 17:55:45'),
(38, 9, 8, 'Customer Churn Prediction', 'Model dự đoán khách hàng rời bỏ, ROC-AUC 0.92. Kèm dashboard Streamlit.', 2100000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 3, 20, 2, NULL, '2026-09-19 17:55:45'),
(39, 9, 5, 'Khóa học Python for Data Science', '20 giờ video + 50 notebook, từ Python cơ bản đến pandas, numpy, matplotlib, scikit-learn.', 1200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.82, 8, 60, 2, NULL, '2026-09-19 17:55:45'),
(40, 9, 8, 'Vietnamese NLP Toolkit', 'Bộ công cụ NLP tiếng Việt: tokenizer, POS tagger, NER, sentiment, word embedding.', 2800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.75, 2, 14, 2, NULL, '2026-09-19 17:55:45'),
(41, 10, 6, 'Flutter Food Delivery App', 'App giao đồ ăn Flutter: restaurant list, cart, order tracking, push notification, payment.', 1850000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.76, 6, 32, 2, NULL, '2026-09-19 17:55:45'),
(42, 10, 6, 'React Native Fitness App', 'App fitness React Native: workout plan, tracking, nutrition, social sharing.', 1650000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.75, 2, 22, 2, NULL, '2026-09-19 17:55:45'),
(43, 10, 6, 'Flutter Ride Hailing App', 'App đặt xe Flutter: map, realtime tracking, fare estimate, driver/rider app.', 3200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.95, 4, 18, 2, NULL, '2026-09-19 17:55:45'),
(44, 10, 6, 'Swift iOS Social Network', 'App social network Swift: feed, story, chat, notification, profile.', 2400000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.70, 1, 11, 2, NULL, '2026-09-19 17:55:45'),
(45, 10, 6, 'Kotlin Android E-wallet', 'App ví điện tử Kotlin: topup, transfer, bill payment, QR code, history.', 2600000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 12, 2, NULL, '2026-09-19 17:55:45'),
(46, 11, 9, 'Kubernetes Production Setup', 'Full K8s production setup: ingress, cert-manager, monitoring, logging, autoscaling.', 3500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 8, 30, 2, NULL, '2026-09-19 17:55:45'),
(47, 11, 9, 'AWS CDK Infrastructure', 'AWS CDK TypeScript: VPC, ECS, RDS, S3, CloudFront, Route53, monitoring.', 2900000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 3, 18, 2, NULL, '2026-09-19 17:55:45'),
(48, 11, 9, 'CI/CD Pipeline GitLab', 'Full CI/CD pipeline GitLab: build, test, security scan, deploy multi-env.', 2200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 15, 2, NULL, '2026-09-19 17:55:45'),
(49, 11, 9, 'Terraform Multi-Cloud', 'Terraform module đa cloud: AWS, GCP, Azure. Có state management, workspace.', 3200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.90, 2, 12, 2, NULL, '2026-09-19 17:55:45'),
(50, 11, 9, 'Monitoring Stack Prometheus + Grafana', 'Stack monitoring hoàn chỉnh: Prometheus, Grafana, Alertmanager, Loki.', 2500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 3, 20, 2, NULL, '2026-09-19 17:55:45'),
(51, 12, 11, 'NFT Marketplace Smart Contract', 'Smart contract NFT marketplace: mint, buy, sell, auction, royalty. Solidity + Hardhat.', 3800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.78, 6, 22, 2, NULL, '2026-09-19 17:55:45'),
(52, 12, 11, 'DeFi Staking Protocol', 'Smart contract staking DeFi: stake, unstake, reward, compound. Audited.', 4200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.95, 2, 14, 2, NULL, '2026-09-19 17:55:45'),
(53, 12, 11, 'DAO Governance Template', 'Template DAO governance: proposal, voting, timelock, treasury management.', 3200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 10, 2, NULL, '2026-09-19 17:55:45'),
(54, 12, 11, 'Token Launchpad Full Stack', 'Full stack token launchpad: IDO, whitelist, vesting, claim. React + Solidity.', 4500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 2, 8, 2, NULL, '2026-09-19 17:55:45'),
(55, 13, 7, '3D Character Model Pack', '10 nhân vật 3D low-poly, rigged, animated. Định dạng FBX, OBJ, Blend.', 1800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.76, 6, 25, 2, NULL, '2026-09-19 17:55:45'),
(56, 13, 7, 'Unity FPS Game Template', 'Template game FPS Unity: weapon system, AI enemy, level, multiplayer.', 2400000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.90, 2, 16, 2, NULL, '2026-09-19 17:55:45'),
(57, 13, 7, 'Unreal Engine RPG Template', 'Template RPG Unreal: inventory, quest, dialogue, combat, save system.', 3200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.95, 2, 12, 2, NULL, '2026-09-19 17:55:45'),
(58, 13, 7, 'Game UI Kit Complete', 'UI Kit game hoàn chỉnh: 500+ element, 20 screen, 10 theme. PSD, AI, PNG.', 980000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.75, 3, 28, 2, NULL, '2026-09-19 17:55:45'),
(59, 13, 7, 'Sound Effects Pack Vol.2', '1000+ sound effect: weapon, explosion, footstep, UI, ambient. WAV, MP3.', 750000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 20, 2, NULL, '2026-09-19 17:55:45'),
(60, 14, 12, 'Pentest Checklist & Report', 'Checklist pentest đầy đủ + template báo cáo chuyên nghiệp. Word, Excel, PDF.', 1500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.91, 5, 20, 2, NULL, '2026-09-19 17:55:45'),
(61, 14, 12, 'Security Audit Scripts', 'Bộ script Python audit bảo mật: port scan, vuln scan, config check, log analysis.', 2200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 14, 2, NULL, '2026-09-19 17:55:45'),
(62, 14, 12, 'Khóa học Ethical Hacking', '40 giờ video + lab, từ cơ bản đến nâng cao. Có chứng chỉ hoàn thành.', 2800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 8, 39, 2, NULL, '2026-09-19 17:55:45'),
(63, 14, 12, 'SOC Analyst Toolkit', 'Bộ công cụ SOC: SIEM rule, incident response, threat hunting, forensics.', 3200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.90, 2, 12, 2, NULL, '2026-09-19 17:55:45'),
(64, 15, 13, 'AR Product Visualization', 'Template AR xem sản phẩm 3D trong không gian thực. Unity AR Foundation.', 2600000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 2, 10, 2, NULL, '2026-09-19 17:55:45'),
(65, 15, 13, 'VR Training Simulation', 'Template VR đào tạo: tương tác, quiz, tracking, report. Unity XR.', 3800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.90, 2, 8, 2, NULL, '2026-09-19 17:55:45'),
(66, 15, 13, 'AR Filter Pack', '20 AR filter cho Instagram, Facebook, TikTok. Spark AR, Lens Studio.', 1200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.75, 3, 25, 2, NULL, '2026-09-19 17:55:45'),
(67, 16, 14, 'Bubble SaaS Template', 'Template Bubble SaaS: auth, subscription, dashboard, admin, API integration.', 1800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 15, 2, NULL, '2026-09-19 17:55:45'),
(68, 16, 14, 'Webflow Agency Template', 'Template Webflow cho agency: portfolio, service, blog, contact, CMS.', 950000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 3, 22, 2, NULL, '2026-09-19 17:55:45'),
(69, 16, 14, 'Glide App Template Pack', '10 template Glide: CRM, inventory, event, survey, directory. Kèm hướng dẫn.', 680000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.70, 2, 18, 2, NULL, '2026-09-19 17:55:45'),
(70, 16, 14, 'No-code Automation Workflow', '50 workflow automation với Make, Zapier, n8n. Kèm video hướng dẫn.', 850000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.75, 2, 20, 2, NULL, '2026-09-19 17:55:45'),
(71, 17, 15, 'YouTube Video Template Pack', '30 template video YouTube: intro, outro, lower third, transition. After Effects.', 750000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 3, 30, 2, NULL, '2026-09-19 17:55:45'),
(72, 17, 15, 'Podcast Production Kit', 'Kit sản xuất podcast: intro, outro, jingle, sound effect, cover template.', 580000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.75, 2, 18, 2, NULL, '2026-09-19 17:55:45'),
(73, 17, 15, 'TikTok Content Template', '50 template TikTok: hook, transition, caption, effect. CapCut, Premiere.', 420000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.78, 8, 45, 2, NULL, '2026-09-19 17:55:45'),
(74, 17, 15, 'Motion Graphic Pack Vol.3', '100 motion graphic element: shape, line, text, transition, background.', 980000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.90, 3, 25, 2, NULL, '2026-09-19 17:55:45'),
(75, 18, 1, 'ERP System Full Source (Laravel + Vue)', 'Hệ thống ERP: quản lý kho, bán hàng, mua hàng, kế toán, nhân sự, báo cáo.', 5500000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.98, 6, 20, 2, NULL, '2026-09-19 17:55:45'),
(76, 18, 1, 'CRM System Full Source', 'Source code CRM: lead, customer, deal, pipeline, activity, report, email.', 3800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 2, 12, 2, NULL, '2026-09-19 17:55:45'),
(77, 18, 3, 'POS System Full Source', 'Source code POS: bán hàng, kho, nhân viên, ca làm, báo cáo, in hóa đơn.', 3200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.90, 2, 18, 2, NULL, '2026-09-19 17:55:45'),
(78, 19, 2, 'Brand Identity Kit', 'Kit nhận diện thương hiệu: logo, color, typography, mockup, stationery.', 1200000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.85, 3, 23, 2, NULL, '2026-09-19 17:55:45'),
(79, 19, 2, 'Mockup Mega Pack', '500+ mockup: device, packaging, stationery, apparel, outdoor. PSD.', 850000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.80, 2, 28, 2, NULL, '2026-09-19 17:55:45'),
(80, 20, 1, 'Fintech Wallet Full Source', 'Source code ví fintech: topup, transfer, bill, QR, history, KYC, admin.', 4800000.0000, '/uploads/products/images/1789492723_6aa97df32a88c.jpg', 4.81, 6, 19, 2, NULL, '2026-09-19 17:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `product_approvals`
--

CREATE TABLE `product_approvals` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `censor_id` bigint(20) NOT NULL,
  `action` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_approvals`
--

INSERT INTO `product_approvals` (`id`, `product_id`, `censor_id`, `action`, `note`, `created_at`) VALUES
(1, 1, 18, 'APPROVED', 'Sản phẩm chất lượng, mô tả rõ ràng.', '2026-09-19 17:55:45'),
(2, 2, 18, 'APPROVED', 'Đã kiểm tra file, hoạt động tốt.', '2026-09-19 17:55:45'),
(3, 3, 19, 'APPROVED', 'UI Kit đẹp, đúng mô tả.', '2026-09-19 17:55:45'),
(4, 4, 19, 'APPROVED', 'Icon pack đầy đủ, không vi phạm.', '2026-09-19 17:55:45'),
(5, 5, 18, 'APPROVED', 'Template tốt, code sạch.', '2026-09-19 17:55:45'),
(6, 6, 18, 'APPROVED', 'Ebook chất lượng, nội dung hữu ích.', '2026-09-19 17:55:45'),
(7, 7, 18, 'APPROVED', 'Microservices code chuẩn.', '2026-09-19 17:55:45'),
(8, 8, 19, 'APPROVED', 'Script K8s đầy đủ, có hướng dẫn.', '2026-09-19 17:55:45'),
(9, 9, 19, 'APPROVED', 'Landing kit đẹp, đa dạng.', '2026-09-19 17:55:45'),
(10, 10, 18, 'APPROVED', 'Template marketing ok.', '2026-09-19 17:55:45'),
(11, 11, 18, 'APPROVED', 'Vue 3 admin template tốt.', '2026-09-19 17:55:45'),
(12, 12, 19, 'APPROVED', 'Game 2D hoàn chỉnh.', '2026-09-19 17:55:45'),
(13, 13, 19, 'APPROVED', 'Terraform module chuẩn AWS.', '2026-09-19 17:55:45'),
(14, 14, 18, 'APPROVED', 'NestJS API chất lượng.', '2026-09-19 17:55:45'),
(15, 15, 18, 'APPROVED', 'Motion pack đẹp.', '2026-09-19 17:55:45'),
(16, 16, 19, 'APPROVED', 'Premiere template chuyên nghiệp.', '2026-09-19 17:55:45'),
(17, 17, 18, 'PENDING', 'Đang chờ kiểm duyệt AI score.', '2026-09-19 17:55:45'),
(18, 18, 18, 'PENDING', 'Đang chờ kiểm duyệt AI score.', '2026-09-19 17:55:45'),
(19, 19, 19, 'APPROVED', 'Flutter app hoàn chỉnh.', '2026-09-19 17:55:45'),
(20, 20, 19, 'APPROVED', 'Dashboard UI Kit dark mode đẹp.', '2026-09-19 17:55:45'),
(21, 21, 18, 'APPROVED', 'Đồ án mẫu hữu ích cho sinh viên.', '2026-09-19 17:55:45'),
(22, 22, 18, 'APPROVED', 'HRM Laravel đầy đủ chức năng.', '2026-09-19 17:55:45'),
(23, 23, 19, 'APPROVED', 'Shopify boilerplate tốt.', '2026-09-19 17:55:45'),
(24, 24, 19, 'APPROVED', 'Email template đa dạng.', '2026-09-19 17:55:45'),
(25, 25, 18, 'APPROVED', 'UI Kit iOS 18 chất lượng cao.', '2026-09-19 17:55:45'),
(26, 26, 18, 'APPROVED', 'Chat app realtime hoạt động tốt.', '2026-09-19 17:55:45'),
(27, 27, 19, 'APPROVED', 'Pixel art đẹp, đầy đủ.', '2026-09-19 17:55:45'),
(28, 28, 19, 'REJECTED', 'Phát hiện vi phạm bản quyền hình ảnh.', '2026-09-19 17:55:45'),
(29, 29, 18, 'APPROVED', 'Khóa học ReactJS nâng cao chất lượng.', '2026-09-19 17:55:45'),
(30, 30, 18, 'APPROVED', 'Spring Boot microservices chuẩn.', '2026-09-19 17:55:45'),
(31, 31, 51, 'APPROVED', 'Pitch deck chuyên nghiệp.', '2026-09-19 17:55:45'),
(32, 32, 51, 'APPROVED', 'Business plan đầy đủ.', '2026-09-19 17:55:45'),
(33, 33, 51, 'APPROVED', 'SaaS boilerplate chất lượng cao.', '2026-09-19 17:55:45'),
(34, 34, 52, 'APPROVED', 'Marketplace code tốt.', '2026-09-19 17:55:45'),
(35, 35, 52, 'APPROVED', 'Subscription box hoàn chỉnh.', '2026-09-19 17:55:45'),
(36, 36, 51, 'APPROVED', 'Model ML chính xác cao.', '2026-09-19 17:55:45'),
(37, 37, 51, 'APPROVED', 'Notebook chi tiết.', '2026-09-19 17:55:45'),
(38, 38, 52, 'APPROVED', 'Churn prediction tốt.', '2026-09-19 17:55:45'),
(39, 39, 52, 'APPROVED', 'Khóa học Python chất lượng.', '2026-09-19 17:55:45'),
(40, 40, 51, 'APPROVED', 'Vietnamese NLP hữu ích.', '2026-09-19 17:55:45'),
(41, 41, 51, 'APPROVED', 'Flutter app hoàn chỉnh.', '2026-09-19 17:55:45'),
(42, 42, 52, 'APPROVED', 'React Native fitness app tốt.', '2026-09-19 17:55:45'),
(43, 43, 52, 'APPROVED', 'Ride hailing app chất lượng.', '2026-09-19 17:55:45'),
(44, 44, 51, 'APPROVED', 'iOS social network tốt.', '2026-09-19 17:55:45'),
(45, 45, 51, 'APPROVED', 'Android wallet hoàn chỉnh.', '2026-09-19 17:55:45'),
(46, 46, 52, 'APPROVED', 'K8s production setup chuẩn.', '2026-09-19 17:55:45'),
(47, 47, 52, 'APPROVED', 'AWS CDK đầy đủ.', '2026-09-19 17:55:45'),
(48, 48, 51, 'APPROVED', 'CI/CD pipeline tốt.', '2026-09-19 17:55:45'),
(49, 49, 51, 'APPROVED', 'Terraform multi-cloud chất lượng.', '2026-09-19 17:55:45'),
(50, 50, 52, 'APPROVED', 'Monitoring stack đầy đủ.', '2026-09-19 17:55:45'),
(51, 51, 53, 'APPROVED', 'Smart contract audited.', '2026-09-19 17:55:45'),
(52, 52, 53, 'APPROVED', 'DeFi staking an toàn.', '2026-09-19 17:55:45'),
(53, 53, 53, 'APPROVED', 'DAO governance tốt.', '2026-09-19 17:55:45'),
(54, 54, 53, 'APPROVED', 'Token launchpad hoàn chỉnh.', '2026-09-19 17:55:45'),
(55, 55, 51, 'APPROVED', '3D character đẹp.', '2026-09-19 17:55:45'),
(56, 56, 51, 'APPROVED', 'Unity FPS template tốt.', '2026-09-19 17:55:45'),
(57, 57, 52, 'APPROVED', 'Unreal RPG template chất lượng.', '2026-09-19 17:55:45'),
(58, 58, 52, 'APPROVED', 'Game UI Kit đầy đủ.', '2026-09-19 17:55:45'),
(59, 59, 51, 'APPROVED', 'Sound effects đa dạng.', '2026-09-19 17:55:45'),
(60, 60, 53, 'APPROVED', 'Pentest checklist đầy đủ.', '2026-09-19 17:55:45'),
(61, 61, 53, 'APPROVED', 'Security audit scripts tốt.', '2026-09-19 17:55:45'),
(62, 62, 53, 'APPROVED', 'Khóa học ethical hacking chất lượng.', '2026-09-19 17:55:45'),
(63, 63, 51, 'APPROVED', 'SOC toolkit đầy đủ.', '2026-09-19 17:55:45'),
(64, 64, 52, 'APPROVED', 'AR product visualization tốt.', '2026-09-19 17:55:45'),
(65, 65, 52, 'APPROVED', 'VR training simulation chất lượng.', '2026-09-19 17:55:45'),
(66, 66, 51, 'APPROVED', 'AR filter pack đa dạng.', '2026-09-19 17:55:45'),
(67, 67, 53, 'APPROVED', 'Bubble SaaS template tốt.', '2026-09-19 17:55:45'),
(68, 68, 53, 'APPROVED', 'Webflow agency template đẹp.', '2026-09-19 17:55:45'),
(69, 69, 51, 'APPROVED', 'Glide app template hữu ích.', '2026-09-19 17:55:45'),
(70, 70, 51, 'APPROVED', 'No-code automation tốt.', '2026-09-19 17:55:45'),
(71, 71, 52, 'APPROVED', 'YouTube template đẹp.', '2026-09-19 17:55:45'),
(72, 72, 52, 'APPROVED', 'Podcast kit đầy đủ.', '2026-09-19 17:55:45'),
(73, 73, 53, 'APPROVED', 'TikTok template hot trend.', '2026-09-19 17:55:45'),
(74, 74, 53, 'APPROVED', 'Motion graphic pack chất lượng.', '2026-09-19 17:55:45'),
(75, 75, 51, 'APPROVED', 'ERP system đầy đủ module.', '2026-09-19 17:55:45'),
(76, 76, 51, 'APPROVED', 'CRM system hoàn chỉnh.', '2026-09-19 17:55:45'),
(77, 77, 52, 'APPROVED', 'POS system tốt.', '2026-09-19 17:55:45'),
(78, 78, 52, 'APPROVED', 'Brand identity kit đẹp.', '2026-09-19 17:55:45'),
(79, 79, 53, 'APPROVED', 'Mockup pack đa dạng.', '2026-09-19 17:55:45'),
(80, 80, 53, 'APPROVED', 'Fintech wallet full source.', '2026-09-19 17:55:45');

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
(1, 1, 1500, 40, 24, NULL, '2026-09-19 17:55:45'),
(2, 2, 1200, 30, 18, NULL, '2026-09-19 17:55:45'),
(3, 3, 2100, 60, 35, NULL, '2026-09-19 17:55:45'),
(4, 4, 800, 20, 12, NULL, '2026-09-19 17:55:45'),
(5, 5, 3000, 80, 40, NULL, '2026-09-19 17:55:45'),
(6, 6, 4500, 120, 50, NULL, '2026-09-19 17:55:45'),
(7, 7, 1800, 35, 15, NULL, '2026-09-19 17:55:45'),
(8, 8, 950, 18, 10, NULL, '2026-09-19 17:55:45'),
(9, 9, 2600, 55, 28, NULL, '2026-09-19 17:55:45'),
(10, 10, 1400, 30, 22, NULL, '2026-09-19 17:55:45'),
(11, 11, 3200, 70, 30, NULL, '2026-09-19 17:55:45'),
(12, 12, 700, 12, 8, NULL, '2026-09-19 17:55:45'),
(13, 13, 1100, 25, 12, NULL, '2026-09-19 17:55:45'),
(14, 14, 600, 10, 6, NULL, '2026-09-19 17:55:45'),
(15, 15, 1900, 40, 18, NULL, '2026-09-19 17:55:45'),
(16, 16, 1300, 28, 14, NULL, '2026-09-19 17:55:45'),
(17, 17, 200, 5, 0, NULL, '2026-09-19 17:55:45'),
(18, 18, 350, 8, 0, NULL, '2026-09-19 17:55:45'),
(19, 19, 2400, 50, 20, NULL, '2026-09-19 17:55:45'),
(20, 20, 1700, 36, 16, NULL, '2026-09-19 17:55:45'),
(21, 21, 2900, 65, 25, NULL, '2026-09-19 17:55:45'),
(22, 22, 1000, 22, 11, NULL, '2026-09-19 17:55:45'),
(23, 23, 500, 9, 5, NULL, '2026-09-19 17:55:45'),
(24, 24, 850, 15, 9, NULL, '2026-09-19 17:55:45'),
(25, 25, 2200, 48, 21, NULL, '2026-09-19 17:55:45'),
(26, 26, 1500, 32, 13, NULL, '2026-09-19 17:55:45'),
(27, 27, 2000, 42, 17, NULL, '2026-09-19 17:55:45'),
(28, 28, 150, 3, 0, NULL, '2026-09-19 17:55:45'),
(29, 29, 3800, 90, 33, NULL, '2026-09-19 17:55:45'),
(30, 30, 800, 16, 7, NULL, '2026-09-19 17:55:45'),
(31, 31, 2800, 65, 45, NULL, '2026-09-19 17:55:45'),
(32, 32, 1900, 45, 32, NULL, '2026-09-19 17:55:45'),
(33, 33, 3200, 75, 38, NULL, '2026-09-19 17:55:45'),
(34, 34, 2100, 50, 22, NULL, '2026-09-19 17:55:45'),
(35, 35, 1600, 38, 18, NULL, '2026-09-19 17:55:45'),
(36, 36, 1400, 35, 15, NULL, '2026-09-19 17:55:45'),
(37, 37, 1100, 28, 12, NULL, '2026-09-19 17:55:45'),
(38, 38, 1700, 42, 20, NULL, '2026-09-19 17:55:45'),
(39, 39, 4200, 100, 55, NULL, '2026-09-19 17:55:45'),
(40, 40, 1300, 32, 14, NULL, '2026-09-19 17:55:45'),
(41, 41, 2600, 60, 28, NULL, '2026-09-19 17:55:45'),
(42, 42, 2000, 48, 22, NULL, '2026-09-19 17:55:45'),
(43, 43, 1500, 35, 16, NULL, '2026-09-19 17:55:45'),
(44, 44, 900, 20, 10, NULL, '2026-09-19 17:55:45'),
(45, 45, 1100, 26, 12, NULL, '2026-09-19 17:55:45'),
(46, 46, 2400, 55, 25, NULL, '2026-09-19 17:55:45'),
(47, 47, 1800, 42, 18, NULL, '2026-09-19 17:55:45'),
(48, 48, 1400, 32, 15, NULL, '2026-09-19 17:55:45'),
(49, 49, 1200, 28, 12, NULL, '2026-09-19 17:55:45'),
(50, 50, 1900, 45, 20, NULL, '2026-09-19 17:55:45'),
(51, 51, 1700, 40, 18, NULL, '2026-09-19 17:55:45'),
(52, 52, 1300, 30, 14, NULL, '2026-09-19 17:55:45'),
(53, 53, 900, 22, 10, NULL, '2026-09-19 17:55:45'),
(54, 54, 800, 18, 8, NULL, '2026-09-19 17:55:45'),
(55, 55, 2100, 50, 22, NULL, '2026-09-19 17:55:45'),
(56, 56, 1500, 35, 16, NULL, '2026-09-19 17:55:45'),
(57, 57, 1200, 28, 12, NULL, '2026-09-19 17:55:45'),
(58, 58, 2600, 60, 28, NULL, '2026-09-19 17:55:45'),
(59, 59, 1900, 45, 20, NULL, '2026-09-19 17:55:45'),
(60, 60, 1700, 40, 18, NULL, '2026-09-19 17:55:45'),
(61, 61, 1300, 30, 14, NULL, '2026-09-19 17:55:45'),
(62, 62, 3500, 85, 35, NULL, '2026-09-19 17:55:45'),
(63, 63, 1100, 25, 12, NULL, '2026-09-19 17:55:45'),
(64, 64, 900, 22, 10, NULL, '2026-09-19 17:55:45'),
(65, 65, 750, 18, 8, NULL, '2026-09-19 17:55:45'),
(66, 66, 2300, 55, 25, NULL, '2026-09-19 17:55:45'),
(67, 67, 1400, 32, 15, NULL, '2026-09-19 17:55:45'),
(68, 68, 2100, 50, 22, NULL, '2026-09-19 17:55:45'),
(69, 69, 1700, 40, 18, NULL, '2026-09-19 17:55:45'),
(70, 70, 1900, 45, 20, NULL, '2026-09-19 17:55:45'),
(71, 71, 2800, 65, 30, NULL, '2026-09-19 17:55:45'),
(72, 72, 1600, 38, 18, NULL, '2026-09-19 17:55:45'),
(73, 73, 3600, 90, 40, NULL, '2026-09-19 17:55:45'),
(74, 74, 2400, 55, 25, NULL, '2026-09-19 17:55:45'),
(75, 75, 1500, 35, 15, NULL, '2026-09-19 17:55:45'),
(76, 76, 1200, 28, 12, NULL, '2026-09-19 17:55:45'),
(77, 77, 1800, 42, 18, NULL, '2026-09-19 17:55:45'),
(78, 78, 2200, 52, 22, NULL, '2026-09-19 17:55:45'),
(79, 79, 2700, 62, 28, NULL, '2026-09-19 17:55:45'),
(80, 80, 1600, 38, 16, NULL, '2026-09-19 17:55:45');

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
(1, 2),
(2, 5),
(3, 3),
(4, 3),
(5, 1),
(5, 4),
(6, 1),
(7, 1),
(7, 2),
(7, 9),
(7, 21),
(8, 9),
(8, 10),
(8, 11),
(9, 3),
(9, 25),
(10, 3),
(10, 24),
(11, 4),
(11, 6),
(11, 8),
(12, 13),
(13, 9),
(13, 11),
(14, 8),
(14, 21),
(14, 22),
(15, 14),
(16, 15),
(17, 16),
(17, 17),
(17, 18),
(18, 17),
(19, 12),
(19, 23),
(20, 3),
(20, 25),
(21, 16),
(22, 19),
(22, 20),
(23, 1),
(23, 2),
(24, 24),
(25, 3),
(25, 25),
(26, 1),
(26, 2),
(26, 8),
(27, 13),
(28, 16),
(28, 17),
(29, 1),
(29, 8),
(30, 9),
(30, 10),
(30, 21),
(31, 24),
(31, 26),
(32, 26),
(33, 7),
(33, 8),
(33, 27),
(34, 1),
(34, 2),
(34, 27),
(35, 23),
(35, 27),
(36, 16),
(36, 17),
(36, 18),
(36, 28),
(37, 16),
(37, 28),
(38, 16),
(38, 17),
(38, 28),
(39, 16),
(39, 28),
(40, 16),
(40, 17),
(41, 12),
(41, 29),
(42, 1),
(42, 29),
(43, 12),
(43, 29),
(44, 29),
(45, 29),
(46, 9),
(46, 10),
(47, 9),
(47, 11),
(48, 9),
(49, 9),
(49, 11),
(50, 9),
(50, 10),
(51, 32),
(51, 33),
(52, 32),
(53, 32),
(54, 32),
(54, 33),
(55, 13),
(55, 30),
(56, 13),
(56, 30),
(57, 30),
(58, 30),
(59, 30),
(60, 31),
(61, 16),
(61, 31),
(62, 31),
(63, 31),
(64, 34),
(65, 34),
(66, 34),
(67, 35),
(68, 35),
(69, 35),
(70, 35),
(71, 14),
(71, 36),
(72, 15),
(72, 36),
(73, 14),
(73, 36),
(74, 14),
(74, 36),
(75, 6),
(75, 19),
(75, 37),
(76, 6),
(76, 19),
(76, 38),
(77, 19),
(77, 23),
(78, 3),
(78, 40),
(79, 3),
(79, 40),
(80, 1),
(80, 2),
(80, 39);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) NOT NULL,
  `reporter_id` bigint(20) NOT NULL,
  `report_type` varchar(20) NOT NULL DEFAULT 'COMPLAINT',
  `target_type` varchar(50) NOT NULL,
  `target_id` bigint(20) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `resolved_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `reporter_id`, `report_type`, `target_type`, `target_id`, `reason`, `details`, `status`, `resolved_by`, `created_at`, `updated_at`) VALUES
(1, 5, 'COMPLAINT', 'PRODUCT', 4, 'Sản phẩm không đúng mô tả', 'Icon pack thiếu nhiều icon so với mô tả.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(2, 6, 'COMPLAINT', 'PRODUCT', 12, 'File lỗi không tải được', 'Giải nén bị lỗi corrupt.', 2, 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(3, 7, 'VIOLATION', 'REVIEW', 3, 'Ngôn từ không phù hợp', 'Đánh giá có lời lẽ xúc phạm.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(4, 8, 'COMPLAINT', 'USER', 4, 'Thái độ hỗ trợ kém', 'Seller phản hồi chậm, không hỗ trợ lỗi.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(5, 9, 'VIOLATION', 'PRODUCT', 28, 'Nghi ngờ vi phạm bản quyền', 'Sản phẩm có dấu hiệu sao chép.', 2, 18, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(6, 10, 'COMPLAINT', 'ORDER', 20, 'Đơn hàng bị hủy không lý do', 'Tôi đã thanh toán nhưng đơn bị hủy.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(7, 11, 'COMPLAINT', 'PRODUCT', 17, 'Sản phẩm chưa được duyệt quá lâu', 'Đã gửi 1 tuần vẫn pending.', 2, 19, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(8, 12, 'VIOLATION', 'USER', 13, 'Tài khoản có dấu hiệu gian lận', 'Nghi ngờ spam.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(9, 14, 'COMPLAINT', 'PRODUCT', 18, 'Mô tả không rõ ràng', 'Không biết prompt dùng cho model nào.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(10, 5, 'COMPLAINT', 'STORE', 7, 'Store không hoạt động', 'Store bị rejected nhưng vẫn hiển thị.', 2, 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(11, 21, 'COMPLAINT', 'PRODUCT', 31, 'Slide bị lỗi font', 'Một số slide hiển thị sai font.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(12, 22, 'COMPLAINT', 'PRODUCT', 33, 'Code không chạy được', 'Cài đặt theo hướng dẫn bị lỗi.', 2, 20, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(13, 23, 'VIOLATION', 'PRODUCT', 51, 'Nghi ngờ scam', 'Smart contract có backdoor.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(14, 24, 'COMPLAINT', 'USER', 15, 'Seller không phản hồi', 'Đã liên hệ 3 ngày không trả lời.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(15, 25, 'COMPLAINT', 'PRODUCT', 62, 'Video bị lỗi', 'Một số video không phát được.', 2, 51, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(16, 26, 'VIOLATION', 'REVIEW', 45, 'Review spam', 'Review copy từ nơi khác.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(17, 27, 'COMPLAINT', 'PRODUCT', 75, 'Thiếu module', 'ERP thiếu module kế toán như mô tả.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(18, 28, 'COMPLAINT', 'ORDER', 70, 'Đơn hàng chậm xử lý', 'Đã thanh toán 2 ngày chưa nhận file.', 2, 20, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(19, 29, 'VIOLATION', 'USER', 17, 'Tài khoản giả mạo', 'Nghi ngờ giả mạo seller khác.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(20, 30, 'COMPLAINT', 'PRODUCT', 80, 'Source code thiếu file', 'Thiếu file config quan trọng.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(21, 55, 'COMPLAINT', 'PRODUCT', 41, 'App bị crash', 'App crash khi mở cart.', 2, 52, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(22, 56, 'VIOLATION', 'PRODUCT', 54, 'Vi phạm bản quyền', 'Code copy từ GitHub public.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(23, 57, 'COMPLAINT', 'STORE', 9, 'Store bán hàng giả', 'Sản phẩm không đúng mô tả.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(24, 58, 'COMPLAINT', 'PRODUCT', 73, 'Template lỗi', 'File CapCut không mở được.', 2, 53, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(25, 59, 'VIOLATION', 'USER', 13, 'Spam tin nhắn', 'Gửi tin nhắn quảng cáo.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(26, 60, 'COMPLAINT', 'PRODUCT', 33, 'Documentation thiếu', 'Hướng dẫn setup không đầy đủ.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(27, 21, 'COMPLAINT', 'ORDER', 34, 'File không đúng', 'Nhận file khác với mô tả.', 2, 20, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(28, 22, 'VIOLATION', 'PRODUCT', 62, 'Nội dung không phù hợp', 'Có nội dung hack trái phép.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(29, 23, 'COMPLAINT', 'PRODUCT', 46, 'Thiếu Helm chart', 'Không có Helm chart như mô tả.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(30, 24, 'COMPLAINT', 'USER', 50, 'Seller thái độ kém', 'Trả lời thiếu tôn trọng khách.', 1, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text NOT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `parent_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 5, NULL, 5, 'Source code rất xịn, cấu trúc thư mục rõ ràng chuẩn mô hình Microservices. Mình setup theo file hướng dẫn chạy được ngay không gặp lỗi gì. Đáng từng đồng bỏ ra!', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(2, 1, 2, 1, NULL, 'Cảm ơn bạn Đức đã ủng hộ shop nhé. Chúc dự án của bạn thành công!', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(3, 1, 6, NULL, 4, 'Chức năng đặt phòng hoạt động trơn tru. Có điều phần UI của admin panel màu sắc hơi tối, mình phải sửa lại CSS một xíu. Nhìn chung là rất tuyệt.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(4, 3, 6, NULL, 5, 'Trời ơi UI Kit đẹp xuất sắc! Auto layout chuẩn tới từng pixel, component đặt tên rõ ràng, giúp team mình tiết kiệm được cả tuần thiết kế.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(5, 5, 5, NULL, 4, 'Template chạy mượt, giao diện Dark mode rất tinh tế. Nếu update thêm phần tích hợp sẵn Redux thì mình sẽ vote 5 sao luôn.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(6, 5, 4, 5, NULL, 'Cám ơn bạn đã góp ý. Bản update tháng sau bên mình sẽ bổ sung Redux Toolkit vào store bạn nhé!', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(7, 6, 7, NULL, 5, 'Ebook viết siêu dễ hiểu, đặc biệt là phần hướng dẫn setup Custom Hooks. Đã làm xong 3 project đầu tiên và thấy tay nghề lên hẳn.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(8, 2, 6, NULL, 5, 'Laravel 10 code sạch, tích hợp VNPay dễ dàng. Rất đáng mua!', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(9, 2, 1, 8, NULL, 'Cảm ơn bạn đã tin tưởng shop!', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(10, 2, 8, NULL, 4, 'Hệ thống tốt, nhưng phần CMS hơi khó dùng với người mới.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(11, 4, 7, NULL, 4, 'Icon đẹp, nhưng thiếu một số icon như mô tả. Vẫn ok.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(12, 4, 3, 11, NULL, 'Cảm ơn bạn đã góp ý, mình sẽ bổ sung trong bản update tới.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(13, 7, 9, NULL, 5, 'Microservices code cực xịn, RabbitMQ hoạt động trơn tru.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(14, 7, 10, NULL, 4, 'Tài liệu hướng dẫn hơi ngắn, cần thêm ví dụ.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(15, 9, 10, NULL, 5, 'Landing kit quá đẹp, nhiều template để chọn.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(16, 9, 11, NULL, 5, 'Figma auto layout chuẩn, tiết kiệm thời gian.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(17, 11, 11, NULL, 5, 'Vue 3 admin template tốt nhất mình từng dùng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(18, 11, 12, NULL, 4, 'Code ổn, nhưng TypeScript hơi phức tạp với người mới.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(19, 13, 12, NULL, 5, 'Terraform module viết chuẩn, deploy AWS nhanh gọn.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(20, 15, 14, NULL, 5, 'Motion pack đẹp, nhiều hiệu ứng độc đáo.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(21, 15, 5, NULL, 4, 'Template tốt, nhưng một số file cần chỉnh lại.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(22, 19, 5, NULL, 5, 'Flutter app chạy mượt, kết nối API Laravel dễ dàng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(23, 19, 6, NULL, 5, 'Source code đầy đủ, hỗ trợ tốt. Rất hài lòng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(24, 20, 6, NULL, 5, 'Dark mode UI Kit tuyệt đẹp, component đa dạng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(25, 21, 7, NULL, 4, 'Đồ án mẫu hữu ích, nhưng báo cáo hơi dài dòng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(26, 22, 8, NULL, 5, 'HRM Laravel đầy đủ chức năng, code dễ hiểu.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(27, 25, 9, NULL, 5, 'UI Kit iOS 18 quá đẹp, component chuẩn Apple.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(28, 25, 10, NULL, 5, 'Mình đã dùng cho 3 dự án, rất hài lòng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(29, 26, 10, NULL, 5, 'Chat app realtime hoạt động tốt, socket ổn định.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(30, 26, 11, NULL, 4, 'Code ổn, nhưng cần thêm tính năng gửi file.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(31, 27, 11, NULL, 5, 'Pixel art đẹp, đa dạng nhân vật và tilemap.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(32, 27, 12, NULL, 5, 'Asset pack chất lượng cao, giá hợp lý.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(33, 29, 12, NULL, 5, 'Khóa học ReactJS nâng cao rất hay, video chất lượng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(34, 29, 14, NULL, 5, 'Học xong tự tin làm dự án thực tế. Cảm ơn tác giả!', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(35, 29, 5, NULL, 4, 'Nội dung tốt, nhưng âm thanh hơi nhỏ.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(36, 30, 14, NULL, 5, 'Spring Boot microservices code chuẩn, Kafka hoạt động tốt.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(37, 30, 6, NULL, 5, 'Hệ thống e-commerce hoàn chỉnh, rất đáng tiền.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(38, 1, 9, NULL, 4, 'Source code tốt, nhưng cần cập nhật thêm tính năng mới.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(39, 3, 10, NULL, 5, 'UI Kit đẹp nhất mình từng mua trên Creono.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(40, 5, 11, NULL, 4, 'Dashboard template tốt, nhưng dark mode chưa hoàn thiện.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(41, 31, 21, NULL, 5, 'Pitch deck quá chuyên nghiệp, mình đã gọi vốn thành công nhờ nó!', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(42, 31, 22, NULL, 5, 'Slide đẹp, financial model chuẩn. Rất đáng tiền.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(43, 31, 23, NULL, 4, 'Template tốt, nhưng cần thêm slide cho team.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(44, 31, 8, 43, NULL, 'Cảm ơn bạn đã góp ý, bản update tới sẽ bổ sung!', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(45, 33, 24, NULL, 5, 'SaaS boilerplate cực xịn, tiết kiệm 2 tháng dev.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(46, 33, 25, NULL, 5, 'Stripe integration hoạt động hoàn hảo, code clean.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(47, 33, 26, NULL, 5, 'Multi-tenant setup chuẩn, rất recommend.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(48, 33, 27, NULL, 5, 'Best SaaS boilerplate mình từng dùng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(49, 36, 28, NULL, 5, 'Model chính xác cao, feature engineering rất kỹ.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(50, 36, 29, NULL, 5, 'Dataset chất lượng, code dễ hiểu.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(51, 36, 30, NULL, 4, 'Model tốt, nhưng cần thêm documentation.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(52, 39, 31, NULL, 5, 'Khóa học Python data science hay nhất mình từng học.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(53, 39, 32, NULL, 5, 'Notebook chi tiết, giải thích rõ ràng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(54, 39, 33, NULL, 5, 'Giảng viên dạy dễ hiểu, ví dụ thực tế.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(55, 39, 34, NULL, 4, 'Nội dung tốt, nhưng video hơi dài.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(56, 41, 35, NULL, 5, 'Food delivery app chạy mượt, UI đẹp.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(57, 41, 36, NULL, 5, 'Code Flutter sạch, dễ customize.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(58, 41, 37, NULL, 4, 'App tốt, nhưng cần thêm payment gateway VN.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(59, 43, 38, NULL, 5, 'Ride hailing app hoàn chỉnh, map integration tốt.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(60, 43, 39, NULL, 5, 'Realtime tracking mượt, code clean.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(61, 46, 40, NULL, 5, 'K8s production setup chuẩn enterprise.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(62, 46, 21, NULL, 5, 'Monitoring stack đầy đủ, rất professional.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(63, 46, 22, NULL, 5, 'Đã deploy production, chạy ổn định 6 tháng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(64, 46, 23, NULL, 4, 'Setup tốt, nhưng cần thêm doc cho beginner.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(65, 51, 24, NULL, 5, 'NFT marketplace contract audited, an toàn.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(66, 51, 25, NULL, 5, 'Code Solidity clean, gas optimize tốt.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(67, 51, 26, NULL, 4, 'Contract tốt, nhưng cần thêm test case.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(68, 55, 27, NULL, 5, '3D character đẹp, rigged chuẩn.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(69, 55, 28, NULL, 5, 'Animation mượt, texture chất lượng cao.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(70, 55, 29, NULL, 4, 'Model đẹp, nhưng cần thêm variant.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(71, 60, 30, NULL, 5, 'Pentest checklist đầy đủ, report template chuyên nghiệp.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(72, 60, 31, NULL, 5, 'Dùng cho công ty, tiết kiệm nhiều thời gian.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(73, 62, 32, NULL, 5, 'Khóa học ethical hacking hay, lab thực tế.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(74, 62, 33, NULL, 5, 'Giảng viên kinh nghiệm, nội dung cập nhật.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(75, 62, 34, NULL, 5, 'Học xong tự tin thi CEH.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(76, 62, 35, NULL, 4, 'Khóa học tốt, nhưng cần thêm lab nâng cao.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(77, 73, 36, NULL, 5, 'TikTok template đẹp, viral ngay lần đầu dùng.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(78, 73, 37, NULL, 5, 'Nhiều effect hot trend, rất hữu ích.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(79, 73, 38, NULL, 5, 'Tiết kiệm thời gian edit cực nhiều.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(80, 73, 39, NULL, 4, 'Template tốt, nhưng cần update thêm trend mới.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(81, 75, 40, NULL, 5, 'ERP system đầy đủ module, code Laravel + Vue clean.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(82, 75, 21, NULL, 5, 'Triển khai cho khách hàng, chạy ổn định.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(83, 75, 22, NULL, 5, 'Báo cáo đa dạng, export Excel/PDF tốt.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(84, 80, 23, NULL, 5, 'Fintech wallet full source, bảo mật tốt.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(85, 80, 24, NULL, 5, 'KYC integration hoàn chỉnh, code clean.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(86, 80, 25, NULL, 4, 'Source tốt, nhưng cần thêm doc API.', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45');

--
-- Triggers `reviews`
--
DELIMITER $$
CREATE TRIGGER `trg_after_review_insert` AFTER INSERT ON `reviews` FOR EACH ROW BEGIN
    IF NEW.rating IS NOT NULL THEN
        UPDATE products 
        SET rating = ROUND(((rating * review_count) + NEW.rating) / (review_count + 1), 2),
            review_count = review_count + 1
        WHERE id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'commission_rate', '5.0', 'Tỷ lệ phí nền tảng (%)', '2026-09-19 17:55:45'),
(2, 'platform_fee', '5.0', 'Phí nền tảng mặc định (%)', '2026-09-19 17:55:45'),
(3, 'min_withdraw_amount', '500000', 'Số tiền rút tối thiểu', '2026-09-19 17:55:45'),
(4, 'max_withdraw_amount', '50000000', 'Số tiền rút tối đa mỗi lần', '2026-09-19 17:55:45'),
(5, 'product_approval_required', '1', 'Sản phẩm cần duyệt trước khi bán', '2026-09-19 17:55:45'),
(6, 'ai_censorship_enabled', '1', 'Bật kiểm duyệt AI tự động', '2026-09-19 17:55:45'),
(7, 'ai_score_threshold', '70.0', 'Ngưỡng điểm AI để tự động duyệt', '2026-09-19 17:55:45'),
(8, 'order_expiry_hours', '24', 'Số giờ đơn hàng pending tự hủy', '2026-09-19 17:55:45'),
(9, 'platform_name', 'Creono', 'Tên nền tảng', '2026-09-19 17:55:45'),
(10, 'support_email', 'support@creono.com', 'Email hỗ trợ', '2026-09-19 17:55:45'),
(11, 'default_commission', '5.0', 'Tỷ lệ hoa hồng mặc định', '2026-09-19 17:55:45'),
(12, 'max_products_per_store', '1000', 'Sản phẩm tối đa mỗi store', '2026-09-19 17:55:45'),
(13, 'enable_kyc', '1', 'Bật xác thực KYC', '2026-09-19 17:55:45'),
(14, 'maintenance_mode', '0', 'Chế độ bảo trì', '2026-09-19 17:55:45'),
(15, 'currency', 'VND', 'Đơn vị tiền tệ', '2026-09-19 17:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(4) DEFAULT 1,
  `description` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `document_url` varchar(500) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_account_name` varchar(100) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `user_id`, `name`, `status`, `description`, `logo_url`, `document_url`, `phone`, `address`, `bank_name`, `bank_account_number`, `bank_account_name`, `slug`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 2, 'TechNova Software', 2, 'Cửa hàng cung cấp các bộ Source Code doanh nghiệp chất lượng cao, viết bằng React, Node.js, PHP và Java. Hỗ trợ support 1-1 cho khách mua hàng.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0901234567', 'Tòa nhà A, Khu công nghệ cao, TP.HCM', 'Vietcombank', '01234567890', 'CTY TNHH TECHNOVA', 'technova-software', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(2, 3, 'Pixel Design Space', 2, 'Góc nhỏ của dân thiết kế. Chuyên bán các bộ UI Kit Figma, Vector illustrations và tài nguyên design.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0987654321', 'Quận 1, TP.HCM', 'MB Bank', '987654321', 'LE THI B', 'pixel-design-space', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(3, 4, 'Front-end Masters', 2, 'Chuyên các dashboard template, landing page hiện đại tối ưu UI/UX & Perfomance sử dụng TailwindCSS, Vue, React.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0911223344', 'Cầu Giấy, Hà Nội', 'Techcombank', '1122334455', 'NGUYEN VAN C', 'front-end-masters', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(4, 15, 'CodeHub Microservices', 2, 'Chuyên cung cấp source code kiến trúc microservices, Docker, Kubernetes cho doanh nghiệp.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0933221100', 'Quận 7, TP.HCM', 'ACB', '123123123', 'CONG TY CODEHUB', 'codehub-microservices', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(5, 16, 'Motion Graphics Lab', 2, 'Cung cấp template After Effects, Premiere, animation 2D/3D chuyên nghiệp.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0944556677', 'Hai Bà Trưng, Hà Nội', 'VPBank', '456456456', 'MOTION LAB CO', 'motion-graphics-lab', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(6, 17, 'AI Models Vietnam', 1, 'Store đang chờ duyệt - cung cấp model AI, dataset, prompt engineering.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0955667788', 'Đà Nẵng', 'Sacombank', '789789789', 'AI MODELS VN', 'ai-models-vietnam', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(7, 15, 'CodeHub Academy', 3, 'Store bị từ chối do thiếu giấy tờ pháp lý.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0933221101', 'Quận 7, TP.HCM', 'ACB', '123123124', 'CONG TY CODEHUB', 'codehub-academy', 'Thiếu giấy phép kinh doanh và chứng nhận bản quyền sản phẩm.', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(8, 41, 'StartupKit Studio', 2, 'Template khởi nghiệp: pitch deck, business plan, landing page, MVP source code.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0966778899', 'Quận 3, TP.HCM', 'TPBank', '111222333', 'STARTUPKIT JSC', 'startupkit-studio', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(9, 42, 'DataScience Hub', 2, 'Model ML, dataset, notebook, khóa học data science từ cơ bản đến nâng cao.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0977889900', 'Cầu Giấy, Hà Nội', 'VIB', '222333444', 'DATA SCIENCE HUB', 'datascience-hub', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(10, 43, 'MobileFirst Dev', 2, 'Source code mobile app Flutter, React Native, Swift, Kotlin chất lượng cao.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0988990011', 'Quận 2, TP.HCM', 'SHB', '333444555', 'MOBILEFIRST CO', 'mobilefirst-dev', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(11, 44, 'CloudNative Co', 2, 'Giải pháp Cloud, DevOps, CI/CD, Kubernetes, Terraform, AWS, GCP, Azure.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0999001122', 'Hải Châu, Đà Nẵng', 'MSB', '444555666', 'CLOUDNATIVE JSC', 'cloudnative-co', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(12, 45, 'Blockchain Labs', 2, 'Smart contract, DApp, NFT marketplace, DeFi protocol source code.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0900112233', 'Quận 4, TP.HCM', 'OCB', '555666777', 'BLOCKCHAIN LABS', 'blockchain-labs', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(13, 46, 'GameDev Pro', 2, 'Game asset, Unity, Unreal Engine, sprite, 3D model, game template.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0911223344', 'Thanh Xuân, Hà Nội', 'LienVietPostBank', '666777888', 'GAMEDEV PRO CO', 'gamedev-pro', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(14, 47, 'Cyber Security VN', 2, 'Công cụ bảo mật, pentest script, security audit checklist, course.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0922334455', 'Quận 10, TP.HCM', 'SeABank', '777888999', 'CYBERSEC VN', 'cyber-security-vn', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(15, 48, 'AR/VR Studio', 2, 'Trải nghiệm AR/VR, 3D model, Unity AR Foundation, Vuforia template.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0933445566', 'Quận 5, TP.HCM', 'BacABank', '888999000', 'ARVR STUDIO', 'arvr-studio', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(16, 49, 'NoCode Academy', 2, 'Template Bubble, Webflow, Glide, AppSheet, hướng dẫn no-code.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0944556677', 'Quận 6, TP.HCM', 'PVcomBank', '999000111', 'NOCODE ACADEMY', 'nocode-academy', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(17, 50, 'Content Creator Pro', 2, 'Template video, podcast, thumbnail, social media content, script.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0955667788', 'Quận 8, TP.HCM', 'VietinBank', '000111222', 'CONTENT CREATOR', 'content-creator-pro', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(18, 2, 'TechNova Enterprise', 2, 'Giải pháp enterprise: ERP, CRM, HRM, POS source code.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0901234568', 'Tòa nhà B, Khu công nghệ cao, TP.HCM', 'Vietcombank', '01234567891', 'CTY TNHH TECHNOVA', 'technova-enterprise', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(19, 3, 'Pixel Branding', 2, 'Brand identity, logo, mockup, stationery design template.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0987654322', 'Quận 1, TP.HCM', 'MB Bank', '987654322', 'LE THI B', 'pixel-branding', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(20, 4, 'Devmaster Pro', 2, 'Premium source code: SaaS, marketplace, social network, fintech.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', NULL, '0911223345', 'Cầu Giấy, Hà Nội', 'Techcombank', '1122334456', 'NGUYEN VAN C', 'devmaster-pro', NULL, '2026-09-19 17:55:45', '2026-09-19 17:56:57');

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
(1, 'ReactJS', 'reactjs'),
(2, 'Node.js', 'nodejs'),
(3, 'Figma', 'figma'),
(4, 'TailwindCSS', 'tailwindcss'),
(5, 'Laravel', 'laravel'),
(6, 'VueJS', 'vuejs'),
(7, 'Next.js', 'nextjs'),
(8, 'TypeScript', 'typescript'),
(9, 'Docker', 'docker'),
(10, 'Kubernetes', 'kubernetes'),
(11, 'AWS', 'aws'),
(12, 'Flutter', 'flutter'),
(13, 'Unity', 'unity'),
(14, 'After Effects', 'after-effects'),
(15, 'Premiere', 'premiere'),
(16, 'Python', 'python'),
(17, 'AI', 'ai'),
(18, 'Machine Learning', 'machine-learning'),
(19, 'Laravel', 'laravel-2'),
(20, 'PHP', 'php'),
(21, 'Node.js', 'nodejs-2'),
(22, 'GraphQL', 'graphql'),
(23, 'E-commerce', 'ecommerce'),
(24, 'Marketing', 'marketing'),
(25, 'UI Kit', 'ui-kit'),
(26, 'Startup', 'startup'),
(27, 'SaaS', 'saas'),
(28, 'Data Science', 'data-science'),
(29, 'Mobile', 'mobile'),
(30, 'Game', 'game'),
(31, 'Security', 'security'),
(32, 'Blockchain', 'blockchain'),
(33, 'NFT', 'nft'),
(34, 'AR/VR', 'arvr'),
(35, 'No-code', 'nocode'),
(36, 'Video', 'video'),
(37, 'ERP', 'erp'),
(38, 'CRM', 'crm'),
(39, 'Fintech', 'fintech'),
(40, 'Branding', 'branding');

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
(1, 5, 'Creono là nền tảng tuyệt vời để mua source code chất lượng. Tôi đã tiết kiệm được rất nhiều thời gian cho dự án của mình.', 5, 1, 1, '2026-09-19 17:55:45'),
(2, 6, 'Giao diện dễ dùng, thanh toán nhanh, seller hỗ trợ nhiệt tình. Sẽ tiếp tục ủng hộ!', 5, 1, 2, '2026-09-19 17:55:45'),
(3, 7, 'Tôi đã mua được nhiều template xịn với giá hợp lý. Đặc biệt thích tính năng xem trước sản phẩm.', 4, 1, 3, '2026-09-19 17:55:45'),
(4, 8, 'Là seller, tôi thấy Creono có hệ thống quản lý đơn hàng và doanh thu rất chuyên nghiệp.', 5, 0, 4, '2026-09-19 17:55:45'),
(5, 9, 'Chất lượng sản phẩm trên Creono rất ổn định. Đội ngũ kiểm duyệt làm việc nghiêm túc.', 5, 0, 5, '2026-09-19 17:55:45'),
(6, 10, 'Một số sản phẩm cần cải thiện mô tả chi tiết hơn, nhưng nhìn chung trải nghiệm rất tốt.', 4, 0, 6, '2026-09-19 17:55:45'),
(7, 11, 'Tôi đã bán được hơn 50 đơn hàng chỉ trong 2 tháng. Creono thực sự là kênh thu nhập tốt.', 5, 1, 7, '2026-09-19 17:55:45'),
(8, 12, 'Nền tảng ổn định, ít khi gặp lỗi. Mong Creono sớm có thêm tính năng chat trực tiếp giữa buyer và seller.', 4, 0, 8, '2026-09-19 17:55:45'),
(9, 21, 'Đã mua 5 sản phẩm trên Creono, chất lượng đều rất tốt. Sẽ giới thiệu cho bạn bè.', 5, 0, 9, '2026-09-19 17:55:45'),
(10, 22, 'Seller hỗ trợ nhanh, sản phẩm đúng mô tả. Rất hài lòng với dịch vụ.', 5, 0, 10, '2026-09-19 17:55:45'),
(11, 23, 'Giá cả hợp lý, nhiều sản phẩm chất lượng. Đặc biệt thích các template startup.', 5, 1, 11, '2026-09-19 17:55:45'),
(12, 24, 'Là seller mới, tôi được Creono hỗ trợ rất nhiều trong việc setup store và quản lý đơn hàng.', 5, 0, 12, '2026-09-19 17:55:45'),
(13, 25, 'Các khóa học trên Creono rất chất lượng, giảng viên có kinh nghiệm thực tế.', 5, 0, 13, '2026-09-19 17:55:45'),
(14, 26, 'Tôi đã mua source code ERP và rất hài lòng. Code clean, documentation đầy đủ.', 5, 0, 14, '2026-09-19 17:55:45'),
(15, 27, 'Creono có nhiều sản phẩm độc đáo mà không nơi nào có. Rất đáng để khám phá.', 4, 0, 15, '2026-09-19 17:55:45'),
(16, 28, 'Dịch vụ khách hàng tốt, giải quyết khiếu nại nhanh chóng. Sẽ tiếp tục mua hàng.', 5, 0, 16, '2026-09-19 17:55:45'),
(17, 29, 'Đã mua khóa học ethical hacking, nội dung rất thực tế và cập nhật.', 5, 0, 17, '2026-09-19 17:55:45'),
(18, 30, 'Sản phẩm đa dạng từ source code đến design, đáp ứng mọi nhu cầu.', 5, 0, 18, '2026-09-19 17:55:45'),
(19, 55, 'Mua template TikTok, dùng là viral luôn. Rất đáng tiền!', 5, 0, 19, '2026-09-19 17:55:45'),
(20, 56, 'Creono là nền tảng uy tín, tôi đã giới thiệu cho nhiều đồng nghiệp.', 5, 0, 20, '2026-09-19 17:55:45');

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
(1, 2, NULL, 1, 5000000.0000, 'Nạp tiền vào ví', 'GW-20260101-001', 'VNPay', '2026-09-19 17:55:45'),
(2, 2, 1, 5, 1425000.0000, 'Thu nhập từ đơn hàng ORD-2026-0001', NULL, NULL, '2026-09-19 17:55:45'),
(3, 2, NULL, 2, -2000000.0000, 'Yêu cầu rút tiền', NULL, NULL, '2026-09-19 17:55:45'),
(4, 3, 2, 5, 427500.0000, 'Thu nhập từ đơn hàng ORD-2026-0002', NULL, NULL, '2026-09-19 17:55:45'),
(5, 4, 3, 5, 189050.0000, 'Thu nhập từ đơn hàng ORD-2026-0003', NULL, NULL, '2026-09-19 17:55:45'),
(6, 5, 1, 3, -1500000.0000, 'Thanh toán đơn hàng ORD-2026-0001', NULL, 'Wallet', '2026-09-19 17:55:45'),
(7, 6, 2, 3, -450000.0000, 'Thanh toán đơn hàng ORD-2026-0002', NULL, 'Wallet', '2026-09-19 17:55:45'),
(8, 7, 3, 3, -199000.0000, 'Thanh toán đơn hàng ORD-2026-0003', NULL, 'Wallet', '2026-09-19 17:55:45'),
(9, 8, 4, 3, -600000.0000, 'Thanh toán đơn hàng ORD-2026-0004', NULL, 'Wallet', '2026-09-19 17:55:45'),
(10, 9, 5, 3, -2200000.0000, 'Thanh toán đơn hàng ORD-2026-0005', NULL, 'Wallet', '2026-09-19 17:55:45'),
(11, 10, 6, 3, -380000.0000, 'Thanh toán đơn hàng ORD-2026-0006', NULL, 'Wallet', '2026-09-19 17:55:45'),
(12, 11, 7, 3, -750000.0000, 'Thanh toán đơn hàng ORD-2026-0007', NULL, 'Wallet', '2026-09-19 17:55:45'),
(13, 12, 8, 3, -2500000.0000, 'Thanh toán đơn hàng ORD-2026-0008', NULL, 'Wallet', '2026-09-19 17:55:45'),
(14, 14, 9, 3, -550000.0000, 'Thanh toán đơn hàng ORD-2026-0009', NULL, 'Wallet', '2026-09-19 17:55:45'),
(15, 15, NULL, 1, 10000000.0000, 'Nạp tiền vào ví', 'GW-20260115-002', 'Momo', '2026-09-19 17:55:45'),
(16, 15, 5, 5, 2090000.0000, 'Thu nhập từ đơn hàng ORD-2026-0005', NULL, NULL, '2026-09-19 17:55:45'),
(17, 15, NULL, 2, -3000000.0000, 'Yêu cầu rút tiền', NULL, NULL, '2026-09-19 17:55:45'),
(18, 16, NULL, 1, 8000000.0000, 'Nạp tiền vào ví', 'GW-20260120-003', 'VNPay', '2026-09-19 17:55:45'),
(19, 16, 9, 5, 522500.0000, 'Thu nhập từ đơn hàng ORD-2026-0009', NULL, NULL, '2026-09-19 17:55:45'),
(20, 17, NULL, 1, 15000000.0000, 'Nạp tiền vào ví', 'GW-20260125-004', 'Bank Transfer', '2026-09-19 17:55:45'),
(21, 2, 6, 5, 361000.0000, 'Thu nhập từ đơn hàng ORD-2026-0006', NULL, NULL, '2026-09-19 17:55:45'),
(22, 3, 7, 5, 712500.0000, 'Thu nhập từ đơn hàng ORD-2026-0007', NULL, NULL, '2026-09-19 17:55:45'),
(23, 4, 8, 5, 2375000.0000, 'Thu nhập từ đơn hàng ORD-2026-0008', NULL, NULL, '2026-09-19 17:55:45'),
(24, 5, 4, 3, -600000.0000, 'Thanh toán đơn hàng ORD-2026-0004', NULL, 'Wallet', '2026-09-19 17:55:45'),
(25, 6, 5, 3, -2200000.0000, 'Thanh toán đơn hàng ORD-2026-0005', NULL, 'Wallet', '2026-09-19 17:55:45'),
(26, 7, 6, 3, -380000.0000, 'Thanh toán đơn hàng ORD-2026-0006', NULL, 'Wallet', '2026-09-19 17:55:45'),
(27, 8, 7, 3, -750000.0000, 'Thanh toán đơn hàng ORD-2026-0007', NULL, 'Wallet', '2026-09-19 17:55:45'),
(28, 9, 8, 3, -2500000.0000, 'Thanh toán đơn hàng ORD-2026-0008', NULL, 'Wallet', '2026-09-19 17:55:45'),
(29, 10, 9, 3, -550000.0000, 'Thanh toán đơn hàng ORD-2026-0009', NULL, 'Wallet', '2026-09-19 17:55:45'),
(30, 11, 10, 3, -1350000.0000, 'Thanh toán đơn hàng ORD-2026-0010', NULL, 'Wallet', '2026-09-19 17:55:45'),
(31, 12, 11, 3, -420000.0000, 'Thanh toán đơn hàng ORD-2026-0011', NULL, 'Wallet', '2026-09-19 17:55:45'),
(32, 14, 12, 3, -650000.0000, 'Thanh toán đơn hàng ORD-2026-0012', NULL, 'Wallet', '2026-09-19 17:55:45'),
(33, 15, 13, 5, 1045000.0000, 'Thu nhập từ đơn hàng ORD-2026-0013', NULL, NULL, '2026-09-19 17:55:45'),
(34, 16, 14, 5, 494000.0000, 'Thu nhập từ đơn hàng ORD-2026-0014', NULL, NULL, '2026-09-19 17:55:45'),
(35, 17, 15, 5, 931000.0000, 'Thu nhập từ đơn hàng ORD-2026-0015', NULL, NULL, '2026-09-19 17:55:45'),
(36, 2, NULL, 4, 75000.0000, 'Hoàn phí nền tảng đơn hàng hủy', NULL, NULL, '2026-09-19 17:55:45'),
(37, 3, NULL, 4, 22500.0000, 'Hoàn phí nền tảng đơn hàng hủy', NULL, NULL, '2026-09-19 17:55:45'),
(38, 5, NULL, 1, 2000000.0000, 'Nạp tiền vào ví', 'GW-20260201-005', 'VNPay', '2026-09-19 17:55:45'),
(39, 6, NULL, 1, 500000.0000, 'Nạp tiền vào ví', 'GW-20260202-006', 'Momo', '2026-09-19 17:55:45'),
(40, 7, NULL, 1, 1000000.0000, 'Nạp tiền vào ví', 'GW-20260203-007', 'Bank Transfer', '2026-09-19 17:55:45'),
(41, 41, NULL, 1, 5000000.0000, 'Nạp tiền vào ví', 'GW-20260301-008', 'VNPay', '2026-09-19 17:55:45'),
(42, 41, 31, 5, 399000.0000, 'Thu nhập từ đơn hàng ORD-2026-0031', NULL, NULL, '2026-09-19 17:55:45'),
(43, 41, 32, 5, 399000.0000, 'Thu nhập từ đơn hàng ORD-2026-0032', NULL, NULL, '2026-09-19 17:55:45'),
(44, 41, 33, 5, 399000.0000, 'Thu nhập từ đơn hàng ORD-2026-0033', NULL, NULL, '2026-09-19 17:55:45'),
(45, 42, NULL, 1, 10000000.0000, 'Nạp tiền vào ví', 'GW-20260302-009', 'Bank Transfer', '2026-09-19 17:55:45'),
(46, 42, 38, 5, 3990000.0000, 'Thu nhập từ đơn hàng ORD-2026-0038', NULL, NULL, '2026-09-19 17:55:45'),
(47, 42, 39, 5, 3990000.0000, 'Thu nhập từ đơn hàng ORD-2026-0039', NULL, NULL, '2026-09-19 17:55:45'),
(48, 42, 40, 5, 3990000.0000, 'Thu nhập từ đơn hàng ORD-2026-0040', NULL, NULL, '2026-09-19 17:55:45'),
(49, 43, NULL, 1, 8000000.0000, 'Nạp tiền vào ví', 'GW-20260303-010', 'Momo', '2026-09-19 17:55:45'),
(50, 43, 45, 5, 1757500.0000, 'Thu nhập từ đơn hàng ORD-2026-0045', NULL, NULL, '2026-09-19 17:55:45'),
(51, 43, 46, 5, 1757500.0000, 'Thu nhập từ đơn hàng ORD-2026-0046', NULL, NULL, '2026-09-19 17:55:45'),
(52, 43, 47, 5, 1757500.0000, 'Thu nhập từ đơn hàng ORD-2026-0047', NULL, NULL, '2026-09-19 17:55:45'),
(53, 44, NULL, 1, 20000000.0000, 'Nạp tiền vào ví', 'GW-20260304-011', 'Bank Transfer', '2026-09-19 17:55:45'),
(54, 44, 50, 5, 3325000.0000, 'Thu nhập từ đơn hàng ORD-2026-0050', NULL, NULL, '2026-09-19 17:55:45'),
(55, 44, 51, 5, 3325000.0000, 'Thu nhập từ đơn hàng ORD-2026-0051', NULL, NULL, '2026-09-19 17:55:45'),
(56, 44, 52, 5, 3325000.0000, 'Thu nhập từ đơn hàng ORD-2026-0052', NULL, NULL, '2026-09-19 17:55:45'),
(57, 44, 53, 5, 3325000.0000, 'Thu nhập từ đơn hàng ORD-2026-0053', NULL, NULL, '2026-09-19 17:55:45'),
(58, 45, NULL, 1, 12000000.0000, 'Nạp tiền vào ví', 'GW-20260305-012', 'VNPay', '2026-09-19 17:55:45'),
(59, 45, 54, 5, 3610000.0000, 'Thu nhập từ đơn hàng ORD-2026-0054', NULL, NULL, '2026-09-19 17:55:45'),
(60, 45, 55, 5, 3610000.0000, 'Thu nhập từ đơn hàng ORD-2026-0055', NULL, NULL, '2026-09-19 17:55:45'),
(61, 45, 56, 5, 3610000.0000, 'Thu nhập từ đơn hàng ORD-2026-0056', NULL, NULL, '2026-09-19 17:55:45'),
(62, 46, NULL, 1, 15000000.0000, 'Nạp tiền vào ví', 'GW-20260306-013', 'Bank Transfer', '2026-09-19 17:55:45'),
(63, 46, 57, 5, 1710000.0000, 'Thu nhập từ đơn hàng ORD-2026-0057', NULL, NULL, '2026-09-19 17:55:45'),
(64, 46, 58, 5, 1710000.0000, 'Thu nhập từ đơn hàng ORD-2026-0058', NULL, NULL, '2026-09-19 17:55:45'),
(65, 46, 59, 5, 1710000.0000, 'Thu nhập từ đơn hàng ORD-2026-0059', NULL, NULL, '2026-09-19 17:55:45'),
(66, 47, NULL, 1, 18000000.0000, 'Nạp tiền vào ví', 'GW-20260307-014', 'Momo', '2026-09-19 17:55:45'),
(67, 47, 60, 5, 1425000.0000, 'Thu nhập từ đơn hàng ORD-2026-0060', NULL, NULL, '2026-09-19 17:55:45'),
(68, 47, 61, 5, 1425000.0000, 'Thu nhập từ đơn hàng ORD-2026-0061', NULL, NULL, '2026-09-19 17:55:45'),
(69, 48, NULL, 1, 10000000.0000, 'Nạp tiền vào ví', 'GW-20260308-015', 'VNPay', '2026-09-19 17:55:45'),
(70, 48, 64, 5, 2660000.0000, 'Thu nhập từ đơn hàng ORD-2026-0064', NULL, NULL, '2026-09-19 17:55:45'),
(71, 48, 65, 5, 2660000.0000, 'Thu nhập từ đơn hàng ORD-2026-0065', NULL, NULL, '2026-09-19 17:55:45'),
(72, 49, NULL, 1, 12000000.0000, 'Nạp tiền vào ví', 'GW-20260309-016', 'Bank Transfer', '2026-09-19 17:55:45'),
(73, 49, 67, 5, 399000.0000, 'Thu nhập từ đơn hàng ORD-2026-0067', NULL, NULL, '2026-09-19 17:55:45'),
(74, 49, 68, 5, 399000.0000, 'Thu nhập từ đơn hàng ORD-2026-0068', NULL, NULL, '2026-09-19 17:55:45'),
(75, 49, 69, 5, 399000.0000, 'Thu nhập từ đơn hàng ORD-2026-0069', NULL, NULL, '2026-09-19 17:55:45'),
(76, 50, NULL, 1, 15000000.0000, 'Nạp tiền vào ví', 'GW-20260310-017', 'Momo', '2026-09-19 17:55:45'),
(77, 50, 71, 5, 5225000.0000, 'Thu nhập từ đơn hàng ORD-2026-0071', NULL, NULL, '2026-09-19 17:55:45'),
(78, 50, 72, 5, 5225000.0000, 'Thu nhập từ đơn hàng ORD-2026-0072', NULL, NULL, '2026-09-19 17:55:45'),
(79, 8, NULL, 1, 2000000.0000, 'Nạp tiền vào ví', 'GW-20260311-018', 'VNPay', '2026-09-19 17:55:45'),
(80, 9, NULL, 1, 3000000.0000, 'Nạp tiền vào ví', 'GW-20260312-019', 'Momo', '2026-09-19 17:55:45'),
(81, 10, NULL, 1, 500000.0000, 'Nạp tiền vào ví', 'GW-20260313-020', 'Bank Transfer', '2026-09-19 17:55:45'),
(82, 21, NULL, 1, 3000000.0000, 'Nạp tiền vào ví', 'GW-20260314-021', 'VNPay', '2026-09-19 17:55:45'),
(83, 22, NULL, 1, 2000000.0000, 'Nạp tiền vào ví', 'GW-20260315-022', 'Momo', '2026-09-19 17:55:45'),
(84, 23, NULL, 1, 3000000.0000, 'Nạp tiền vào ví', 'GW-20260316-023', 'Bank Transfer', '2026-09-19 17:55:45'),
(85, 24, NULL, 1, 5000000.0000, 'Nạp tiền vào ví', 'GW-20260317-024', 'VNPay', '2026-09-19 17:55:45'),
(86, 25, NULL, 1, 1000000.0000, 'Nạp tiền vào ví', 'GW-20260318-025', 'Momo', '2026-09-19 17:55:45'),
(87, 26, NULL, 1, 7000000.0000, 'Nạp tiền vào ví', 'GW-20260319-026', 'Bank Transfer', '2026-09-19 17:55:45'),
(88, 27, NULL, 1, 2000000.0000, 'Nạp tiền vào ví', 'GW-20260320-027', 'VNPay', '2026-09-19 17:55:45'),
(89, 28, NULL, 1, 3000000.0000, 'Nạp tiền vào ví', 'GW-20260321-028', 'Momo', '2026-09-19 17:55:45'),
(90, 29, NULL, 1, 4000000.0000, 'Nạp tiền vào ví', 'GW-20260322-029', 'Bank Transfer', '2026-09-19 17:55:45'),
(91, 30, NULL, 1, 1000000.0000, 'Nạp tiền vào ví', 'GW-20260323-030', 'VNPay', '2026-09-19 17:55:45'),
(92, 55, NULL, 1, 3000000.0000, 'Nạp tiền vào ví', 'GW-20260324-031', 'Momo', '2026-09-19 17:55:45'),
(93, 56, NULL, 1, 2000000.0000, 'Nạp tiền vào ví', 'GW-20260325-032', 'Bank Transfer', '2026-09-19 17:55:45'),
(94, 57, NULL, 1, 3000000.0000, 'Nạp tiền vào ví', 'GW-20260326-033', 'VNPay', '2026-09-19 17:55:45'),
(95, 58, NULL, 1, 4000000.0000, 'Nạp tiền vào ví', 'GW-20260327-034', 'Momo', '2026-09-19 17:55:45'),
(96, 59, NULL, 1, 2000000.0000, 'Nạp tiền vào ví', 'GW-20260328-035', 'Bank Transfer', '2026-09-19 17:55:45'),
(97, 60, NULL, 1, 5000000.0000, 'Nạp tiền vào ví', 'GW-20260329-036', 'VNPay', '2026-09-19 17:55:45'),
(98, 2, NULL, 3, -1650000.0000, 'Thanh toán đơn hàng ORD-2026-1789817913', NULL, NULL, '2026-09-19 18:38:33'),
(99, 15, NULL, 5, 1567500.0000, 'Doanh thu từ đơn hàng ORD-2026-1789817913', NULL, NULL, '2026-09-19 18:38:33'),
(100, 2, 81, 4, 1650000.0000, 'Hoàn tiền đơn hàng ORD-2026-1789817913 - tai lieu qua ngu', NULL, NULL, '2026-09-19 18:38:50'),
(101, 15, 81, 4, -1567500.0000, 'Khấu trừ hoàn tiền đơn hàng ORD-2026-1789817913 - tai lieu qua ngu', NULL, NULL, '2026-09-19 18:38:50'),
(102, 2, NULL, 3, -1200000.0000, 'Thanh toán đơn hàng ORD-2026-1789817964', NULL, NULL, '2026-09-19 18:39:24'),
(103, 42, NULL, 5, 1140000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789817964', NULL, NULL, '2026-09-19 18:39:24'),
(104, 2, NULL, 3, -420000.0000, 'Thanh toán đơn hàng ORD-2026-1789820608', NULL, NULL, '2026-09-19 19:23:28'),
(105, 50, NULL, 5, 399000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789820608', NULL, NULL, '2026-09-19 19:23:28'),
(106, 2, NULL, 3, -450000.0000, 'Thanh toán đơn hàng ORD-2026-1789821107', NULL, NULL, '2026-09-19 19:31:47'),
(107, 3, NULL, 5, 427500.0000, 'Doanh thu từ đơn hàng ORD-2026-1789821107', NULL, NULL, '2026-09-19 19:31:47'),
(108, 2, NULL, 3, -2400000.0000, 'Thanh toán đơn hàng ORD-2026-1789821506', NULL, NULL, '2026-09-19 19:38:26'),
(109, 43, NULL, 5, 2280000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789821506', NULL, NULL, '2026-09-19 19:38:26'),
(110, 2, NULL, 3, -1200000.0000, 'Thanh toán đơn hàng ORD-2026-1789822964', NULL, NULL, '2026-09-19 20:02:44'),
(111, 3, NULL, 5, 1140000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789822964', NULL, NULL, '2026-09-19 20:02:44'),
(112, 2, NULL, 3, -199000.0000, 'Thanh toán đơn hàng ORD-2026-1789825431', NULL, NULL, '2026-09-19 20:43:51'),
(113, 4, NULL, 5, 189050.0000, 'Doanh thu từ đơn hàng ORD-2026-1789825431', NULL, NULL, '2026-09-19 20:43:51'),
(114, 2, NULL, 3, -320000.0000, 'Thanh toán đơn hàng ORD-2026-1789825843', NULL, NULL, '2026-09-19 20:50:43'),
(115, 3, NULL, 5, 304000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789825843', NULL, NULL, '2026-09-19 20:50:43'),
(116, 2, NULL, 3, -380000.0000, 'Thanh toán đơn hàng ORD-2026-1789825912', NULL, NULL, '2026-09-19 20:51:52'),
(117, 3, NULL, 5, 361000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789825912', NULL, NULL, '2026-09-19 20:51:52'),
(118, 2, NULL, 3, -4800000.0000, 'Thanh toán đơn hàng ORD-2026-1789890993', NULL, NULL, '2026-09-20 14:56:33'),
(119, 4, NULL, 5, 4560000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789890993', NULL, NULL, '2026-09-20 14:56:33'),
(120, 2, 90, 4, 4800000.0000, 'Hoàn tiền đơn hàng ORD-2026-1789890993 - Người mua yêu cầu hoàn tiền', NULL, NULL, '2026-09-20 15:02:32'),
(121, 4, 90, 4, -4560000.0000, 'Khấu trừ hoàn tiền đơn hàng ORD-2026-1789890993 - Người mua yêu cầu hoàn tiền', NULL, NULL, '2026-09-20 15:02:32'),
(122, 2, NULL, 3, -1200000.0000, 'Thanh toán đơn hàng ORD-2026-1789891460', NULL, NULL, '2026-09-20 15:04:20'),
(123, 3, NULL, 5, 1140000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789891460', NULL, NULL, '2026-09-20 15:04:20'),
(124, 1, NULL, 3, -1500000.0000, 'Thanh toán đơn hàng ORD-2026-1789891608', NULL, NULL, '2026-09-20 15:06:48'),
(125, 2, NULL, 5, 1425000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789891608', NULL, NULL, '2026-09-20 15:06:48'),
(126, 1, 92, 4, 1500000.0000, 'Hoàn tiền đơn hàng ORD-2026-1789891608 - Test lý do hoàn tiền', NULL, NULL, '2026-09-20 15:13:53'),
(127, 2, 92, 4, -1425000.0000, 'Khấu trừ hoàn tiền đơn hàng ORD-2026-1789891608 - Test lý do hoàn tiền', NULL, NULL, '2026-09-20 15:13:53'),
(128, 1, NULL, 3, -1800000.0000, 'Thanh toán đơn hàng ORD-2026-1789892150', NULL, NULL, '2026-09-20 15:15:50'),
(129, 2, NULL, 5, 1710000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789892150', NULL, NULL, '2026-09-20 15:15:50'),
(130, 1, 93, 4, 1800000.0000, 'Hoàn tiền đơn hàng ORD-2026-1789892150 - NGU', NULL, NULL, '2026-09-20 15:20:30'),
(131, 2, 93, 4, -1710000.0000, 'Khấu trừ hoàn tiền đơn hàng ORD-2026-1789892150 - NGU', NULL, NULL, '2026-09-20 15:20:30'),
(132, 1, NULL, 3, -1100000.0000, 'Thanh toán đơn hàng ORD-2026-1789892519', NULL, NULL, '2026-09-20 15:21:59'),
(133, 2, NULL, 5, 1045000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789892519', NULL, NULL, '2026-09-20 15:21:59'),
(134, 1, 94, 4, 1100000.0000, 'Hoàn tiền đơn hàng ORD-2026-1789892519 - NGU', NULL, NULL, '2026-09-20 15:22:07'),
(135, 2, 94, 4, -1045000.0000, 'Khấu trừ hoàn tiền đơn hàng ORD-2026-1789892519 - NGU', NULL, NULL, '2026-09-20 15:22:07'),
(136, 1, NULL, 3, -5500000.0000, 'Thanh toán đơn hàng ORD-2026-1789892765', NULL, NULL, '2026-09-20 15:26:05'),
(137, 2, NULL, 5, 5225000.0000, 'Doanh thu từ đơn hàng ORD-2026-1789892765', NULL, NULL, '2026-09-20 15:26:05'),
(138, 1, 14, 2, -3600000.0000, 'Rút tiền thành công', NULL, NULL, '2026-09-20 15:26:56');

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
  `is_locked` tinyint(4) DEFAULT 0 COMMENT '0:Active, 1:Locked',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_locked`, `created_at`) VALUES
(1, 'System Admin', 'admin@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 3, 0, '2026-09-19 17:55:45'),
(2, 'TechNova Solutionss', 'seller.technova@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(3, 'Pixel Crafters', 'seller.pixel@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(4, 'Devmaster Team', 'seller.devmaster@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(5, 'Trần Minh Đức', 'duc.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(6, 'Lê Hoàng Yến', 'yen.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(7, 'Nguyễn Nhật Nam', 'nam.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(8, 'Phạm Quốc Bảo', 'bao.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(9, 'Đỗ Thị Hồng Nhung', 'nhung.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(10, 'Vũ Đình Khánh', 'khanh.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(11, 'Bùi Thanh Tùng', 'tung.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(12, 'Hoàng Mai Phương', 'phuong.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(13, 'Ngô Gia Hân', 'han.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 1, '2026-09-19 17:55:45'),
(14, 'Lý Nhã Kỳ', 'ky.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(15, 'CodeHub Studio', 'seller.codehub@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(16, 'Motion Graphics Lab', 'seller.motion@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(17, 'AI Models Vietnam', 'seller.aimodels@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(18, 'Censor Minh', 'censor.minh@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 4, 0, '2026-09-19 17:55:45'),
(19, 'Censor Lan', 'censor.lan@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 4, 0, '2026-09-19 17:55:45'),
(20, 'System Admin 2', 'admin2@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 3, 0, '2026-09-19 17:55:45'),
(21, 'Nguyễn Văn An', 'an.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(22, 'Trần Thị Bích', 'bich.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(23, 'Lê Quang Cường', 'cuong.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(24, 'Phạm Thu Dung', 'dung.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(25, 'Hoàng Văn Em', 'em.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(26, 'Võ Thị Phương', 'phuong2.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(27, 'Đặng Minh Quân', 'quan.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(28, 'Bùi Thị Hoa', 'hoa.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(29, 'Ngô Văn Inh', 'inh.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(30, 'Dương Thị Kim', 'kim.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(31, 'Lý Văn Long', 'long.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(32, 'Trịnh Thị Mai', 'mai.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(33, 'Đinh Văn Nam', 'nam2.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(34, 'Vũ Thị Oanh', 'oanh.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(35, 'Phan Văn Phúc', 'phuc.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(36, 'Trương Thị Quỳnh', 'quynh.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(37, 'Lương Văn Sơn', 'son.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(38, 'Hồ Thị Trang', 'trang.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(39, 'Tạ Văn Uy', 'uy.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(40, 'Mai Thị Vân', 'van.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(41, 'StartupKit Studio', 'seller.startupkit@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(42, 'DataScience Hub', 'seller.datascience@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(43, 'MobileFirst Dev', 'seller.mobilefirst@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(44, 'CloudNative Co', 'seller.cloudnative@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(45, 'Blockchain Labs', 'seller.blockchain@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(46, 'GameDev Pro', 'seller.gamedev@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(47, 'Cyber Security VN', 'seller.cybersec@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(48, 'AR/VR Studio', 'seller.arvr@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(49, 'NoCode Academy', 'seller.nocode@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(50, 'Content Creator Pro', 'seller.content@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 2, 0, '2026-09-19 17:55:45'),
(51, 'Censor Hùng', 'censor.hung@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 4, 0, '2026-09-19 17:55:45'),
(52, 'Censor Thảo', 'censor.thao@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 4, 0, '2026-09-19 17:55:45'),
(53, 'Censor Quang', 'censor.quang@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 4, 0, '2026-09-19 17:55:45'),
(54, 'System Admin 3', 'admin3@creono.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 3, 0, '2026-09-19 17:55:45'),
(55, 'Nguyễn Thị Xuân', 'xuan.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(56, 'Trần Văn Yên', 'yen2.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(57, 'Lê Thị Zin', 'zin.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(58, 'Phạm Văn Anh', 'anh.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(59, 'Hoàng Thị Bình', 'binh.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45'),
(60, 'Võ Văn Cảnh', 'canh.buyer@mail.com', '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S', 1, 0, '2026-09-19 17:55:45');

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
(1, 'Creono Administrator', 'Quản trị viên hệ thống Creono.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(2, 'TechNova Solutions', 'Chuyên cung cấp mã nguồn hệ thống quản lý chuyên nghiệp và API services.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(3, 'Pixel Crafters', 'Studio thiết kế UI/UX với hơn 5 năm kinh nghiệm thực chiến.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(4, 'Devmaster Team', 'Đội ngũ lập trình viên chuyên các template front-end xịn xò nhất.', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(5, 'Trần Minh Đức', 'Sinh viên IT đam mê code', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(6, 'Lê Hoàng Yến', 'Designer freelance', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(7, 'Nguyễn Nhật Nam', NULL, '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(8, 'Phạm Quốc Bảo', 'Backend developer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(9, 'Đỗ Thị Hồng Nhung', 'Content creator & Marketer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(10, 'Vũ Đình Khánh', 'Sinh viên CNTT năm cuối', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(11, 'Bùi Thanh Tùng', 'Mobile developer (Flutter)', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(12, 'Hoàng Mai Phương', 'UX Researcher', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(13, 'Ngô Gia Hân', NULL, '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(14, 'Lý Nhã Kỳ', 'Freelancer đa lĩnh vực', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(15, 'CodeHub Studio', 'Chuyên source code microservices', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(16, 'Motion Graphics Lab', 'Studio motion & animation', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(17, 'AI Models Vietnam', 'Cung cấp model AI, dataset, prompt', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(18, 'Censor Minh', 'Kiểm duyệt viên nội dung', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(19, 'Censor Lan', 'Kiểm duyệt viên nội dung', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(20, 'System Admin 2', 'Quản trị viên cấp cao', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(21, 'Nguyễn Văn An', 'Frontend developer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(22, 'Trần Thị Bích', 'Business Analyst', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(23, 'Lê Quang Cường', 'Fullstack developer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(24, 'Phạm Thu Dung', 'Product Manager', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(25, 'Hoàng Văn Em', 'DevOps Engineer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(26, 'Võ Thị Phương', 'QA Engineer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(27, 'Đặng Minh Quân', 'Data Engineer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(28, 'Bùi Thị Hoa', 'UI Designer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(29, 'Ngô Văn Inh', 'System Architect', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(30, 'Dương Thị Kim', 'Content Writer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(31, 'Lý Văn Long', 'Backend developer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(32, 'Trịnh Thị Mai', 'Marketing Specialist', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(33, 'Đinh Văn Nam', 'Mobile developer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(34, 'Vũ Thị Oanh', 'Graphic Designer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(35, 'Phan Văn Phúc', 'Game Developer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(36, 'Trương Thị Quỳnh', 'SEO Specialist', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(37, 'Lương Văn Sơn', 'Cloud Engineer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(38, 'Hồ Thị Trang', 'Business Development', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(39, 'Tạ Văn Uy', 'Security Engineer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(40, 'Mai Thị Vân', 'HR Manager', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(41, 'StartupKit Studio', 'Chuyên template khởi nghiệp', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(42, 'DataScience Hub', 'Data science & ML models', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(43, 'MobileFirst Dev', 'Mobile app chuyên nghiệp', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(44, 'CloudNative Co', 'Cloud & DevOps solutions', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(45, 'Blockchain Labs', 'Blockchain & Web3', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(46, 'GameDev Pro', 'Game development assets', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(47, 'Cyber Security VN', 'Security tools & audit', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(48, 'AR/VR Studio', 'AR/VR experiences', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(49, 'NoCode Academy', 'No-code & low-code', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(50, 'Content Creator Pro', 'Digital content & media', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(51, 'Censor Hùng', 'Kiểm duyệt viên', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(52, 'Censor Thảo', 'Kiểm duyệt viên', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(53, 'Censor Quang', 'Kiểm duyệt viên', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(54, 'System Admin 3', 'Quản trị viên hệ thống', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(55, 'Nguyễn Thị Xuân', 'Sinh viên', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(56, 'Trần Văn Yên', 'Freelancer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(57, 'Lê Thị Zin', 'Designer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(58, 'Phạm Văn Anh', 'Developer', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(59, 'Hoàng Thị Bình', 'Tester', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57'),
(60, 'Võ Văn Cảnh', 'IT Support', '/uploads/avatars/1789651192_6aabe8f8e4f9a.jpg', '2026-09-19 17:55:45', '2026-09-19 17:56:57');

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
  `frozen_balance` decimal(19,4) DEFAULT 0.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `frozen_balance`) VALUES
(1, 1, 999999990900000.0000, 0.0000),
(2, 2, 999999999999999.9999, 2000000.0000),
(3, 3, 7872500.0000, 0.0000),
(4, 4, 9089050.0000, 0.0000),
(5, 5, 2000000.0000, 0.0000),
(6, 6, 500000.0000, 0.0000),
(7, 7, 0.0000, 0.0000),
(8, 8, 1500000.0000, 0.0000),
(9, 9, 3200000.0000, 500000.0000),
(10, 10, 0.0000, 0.0000),
(11, 11, 750000.0000, 0.0000),
(12, 12, 12000000.0000, 1000000.0000),
(13, 13, 200000.0000, 0.0000),
(14, 14, 5000000.0000, 0.0000),
(15, 15, 22000000.0000, 3000000.0000),
(16, 16, 9800000.0000, 0.0000),
(17, 17, 15500000.0000, 2500000.0000),
(18, 18, 0.0000, 0.0000),
(19, 19, 0.0000, 0.0000),
(20, 20, 0.0000, 0.0000),
(21, 21, 3000000.0000, 0.0000),
(22, 22, 1800000.0000, 0.0000),
(23, 23, 2500000.0000, 0.0000),
(24, 24, 4200000.0000, 0.0000),
(25, 25, 900000.0000, 0.0000),
(26, 26, 6500000.0000, 0.0000),
(27, 27, 1100000.0000, 0.0000),
(28, 28, 2800000.0000, 0.0000),
(29, 29, 3700000.0000, 0.0000),
(30, 30, 800000.0000, 0.0000),
(31, 31, 5200000.0000, 0.0000),
(32, 32, 1900000.0000, 0.0000),
(33, 33, 2400000.0000, 0.0000),
(34, 34, 3100000.0000, 0.0000),
(35, 35, 1600000.0000, 0.0000),
(36, 36, 2700000.0000, 0.0000),
(37, 37, 4300000.0000, 0.0000),
(38, 38, 3500000.0000, 0.0000),
(39, 39, 2100000.0000, 0.0000),
(40, 40, 1400000.0000, 0.0000),
(41, 41, 18500000.0000, 1500000.0000),
(42, 42, 25440000.0000, 2000000.0000),
(43, 43, 18980000.0000, 1000000.0000),
(44, 44, 28900000.0000, 4000000.0000),
(45, 45, 12400000.0000, 0.0000),
(46, 46, 19800000.0000, 2500000.0000),
(47, 47, 21000000.0000, 0.0000),
(48, 48, 14200000.0000, 1000000.0000),
(49, 49, 16800000.0000, 0.0000),
(50, 50, 23099000.0000, 3000000.0000),
(51, 51, 0.0000, 0.0000),
(52, 52, 0.0000, 0.0000),
(53, 53, 0.0000, 0.0000),
(54, 54, 0.0000, 0.0000),
(55, 55, 2200000.0000, 0.0000),
(56, 56, 1700000.0000, 0.0000),
(57, 57, 2900000.0000, 0.0000),
(58, 58, 3600000.0000, 0.0000),
(59, 59, 1300000.0000, 0.0000),
(60, 60, 4100000.0000, 0.0000);

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
  `status` tinyint(4) DEFAULT 1,
  `admin_note` text DEFAULT NULL,
  `processed_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdraw_requests`
--

INSERT INTO `withdraw_requests` (`id`, `wallet_id`, `amount`, `bank_name`, `bank_account_number`, `bank_account_name`, `status`, `admin_note`, `processed_by`, `created_at`, `updated_at`) VALUES
(1, 2, 2000000.0000, 'Vietcombank', '01234567890', 'CTY TNHH TECHNOVA', 1, NULL, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(2, 15, 3000000.0000, 'ACB', '123123123', 'CONG TY CODEHUB', 2, 'Đã chuyển khoản', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(3, 16, 1500000.0000, 'VPBank', '456456456', 'MOTION LAB CO', 2, 'Đã chuyển khoản', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(4, 3, 500000.0000, 'MB Bank', '987654321', 'LE THI B', 3, 'Số tài khoản không hợp lệ', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(5, 17, 2500000.0000, 'Sacombank', '789789789', 'AI MODELS VN', 1, NULL, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(6, 12, 1000000.0000, 'Techcombank', '1122334455', 'HOANG MAI PHUONG', 1, NULL, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(7, 41, 1500000.0000, 'TPBank', '111222333', 'STARTUPKIT JSC', 1, NULL, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(8, 42, 2000000.0000, 'VIB', '222333444', 'DATA SCIENCE HUB', 2, 'Đã chuyển khoản', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(9, 43, 1000000.0000, 'SHB', '333444555', 'MOBILEFIRST CO', 2, 'Đã chuyển khoản', 1, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(10, 44, 4000000.0000, 'MSB', '444555666', 'CLOUDNATIVE JSC', 1, NULL, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(11, 45, 2000000.0000, 'OCB', '555666777', 'BLOCKCHAIN LABS', 1, NULL, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(12, 46, 2500000.0000, 'LienVietPostBank', '666777888', 'GAMEDEV PRO CO', 1, NULL, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(13, 50, 3000000.0000, 'VietinBank', '000111222', 'CONTENT CREATOR', 1, NULL, NULL, '2026-09-19 17:55:45', '2026-09-19 17:55:45'),
(14, 1, 3600000.0000, '31231231', '312', '312312', 2, NULL, 1, '2026-09-20 15:26:41', '2026-09-20 15:26:56');

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
  ADD KEY `fk_product_category` (`category_id`),
  ADD KEY `idx_products_status_created` (`status`,`created_at`),
  ADD KEY `idx_products_store_status` (`store_id`,`status`);

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
  ADD UNIQUE KEY `product_id` (`product_id`);

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
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_store_user` (`user_id`),
  ADD KEY `idx_stores_slug` (`slug`);

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_labels`
--
ALTER TABLE `ai_labels`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `product_approvals`
--
ALTER TABLE `product_approvals`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `product_stats`
--
ALTER TABLE `product_stats`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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

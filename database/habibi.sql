-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2026 at 01:19 PM
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
-- Database: `habibi`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `recipient_name` varchar(200) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `title`, `recipient_name`, `phone`, `province`, `city`, `postal_code`, `address`, `is_default`, `created_at`, `updated_at`) VALUES
(3, 2, '', 'gas gas', '0991234567', 'jivhk', 'mmm', '123123', 'jiuvjnj fxgghk j', 0, '2026-08-25 03:10:05', '2026-08-25 03:10:05'),
(4, 3, 'اورمیه خیابان بهشتی', 'محمد نوری', '09123000000', 'آذربایجان غربی', 'اورمیه', '123123123', 'اورمیه بهشتی', 0, '2026-08-28 15:08:51', '2026-08-28 15:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'آجیل', 'ajil', 'انواع آجیل و مخلوط‌های خوشمزه', 'category_1787922127_8ef9420a40.jpg', 'active', '2026-08-16 07:13:01', '2026-08-28 13:02:07'),
(2, 'میوه خشک', 'dried-fruits', 'انواع میوه خشک تازه و باکیفیت', 'category_1787922143_7eb095283b.jpg', 'active', '2026-08-16 07:13:01', '2026-08-28 13:02:23'),
(3, 'مغزها', 'maghzha', 'انواع مغز پسته، بادام، گردو و فندق', 'category_1787922164_855993a357.jpg', 'active', '2026-08-16 07:13:01', '2026-08-28 13:02:44'),
(4, 'زعفران و ادویه', 'saffron-spices', 'زعفران و ادویه‌های باکیفیت', 'category_1787922194_1af14592dc.jpg', 'active', '2026-08-16 07:13:01', '2026-08-28 13:03:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `address_id` int(10) UNSIGNED DEFAULT NULL,
  `order_number` varchar(30) NOT NULL,
  `total_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `shipping_cost` decimal(12,0) NOT NULL DEFAULT 0,
  `discount_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `final_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','failed','refunded') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `address_id`, `order_number`, `total_amount`, `shipping_cost`, `discount_amount`, `final_amount`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES
(3, 2, 3, 'HAB20260825051005937', 145000, 0, 0, 145000, 'delivered', 'unpaid', '', '2026-08-25 03:10:05', '2026-08-28 14:57:13'),
(4, 2, 3, 'HAB20260825051053282', 200000, 0, 0, 200000, 'delivered', 'unpaid', '', '2026-08-25 03:10:53', '2026-08-28 15:09:52'),
(5, 3, 4, 'HAB20260828170851224', 200000, 0, 0, 200000, 'cancelled', 'unpaid', '', '2026-08-28 15:08:51', '2026-08-28 15:10:17');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_weight_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(12,0) NOT NULL DEFAULT 0,
  `total_price` decimal(12,0) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_weight_id`, `product_name`, `weight`, `unit`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(3, 3, 4, 10, 'انجیر خشک', 250.00, 'گرم', 1, 145000, 145000, '2026-08-25 03:10:05'),
(4, 4, 3, 7, 'مغز گردو ممتاز', 250.00, 'گرم', 1, 200000, 200000, '2026-08-25 03:10:53'),
(5, 5, 3, 7, 'مغز گردو ممتاز', 250.00, 'گرم', 1, 200000, 200000, '2026-08-28 15:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,0) NOT NULL DEFAULT 0,
  `method` enum('cash_on_delivery','online') NOT NULL DEFAULT 'cash_on_delivery',
  `status` enum('pending','successful','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_number` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `amount`, `method`, `status`, `transaction_number`, `payment_date`, `created_at`, `updated_at`) VALUES
(1, 3, 145000, 'cash_on_delivery', 'pending', NULL, NULL, '2026-08-25 03:10:05', '2026-08-25 03:10:05'),
(2, 4, 200000, 'cash_on_delivery', 'pending', NULL, NULL, '2026-08-25 03:10:54', '2026-08-25 03:10:54'),
(3, 5, 200000, 'cash_on_delivery', 'pending', NULL, NULL, '2026-08-28 15:08:51', '2026-08-28 15:08:51');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(12,0) NOT NULL DEFAULT 0,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `image`, `price`, `stock`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 3, 'پسته ممتاز', 'pistachio-premium', 'پسته ممتاز و تازه با کیفیت بالا', 'pistachio.jpg', 850000, 50, 'active', 1, '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(2, 3, 'بادام درختی', 'almond', 'بادام درختی تازه و باکیفیت', 'almond.jpg', 690000, 60, 'active', 1, '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(3, 3, 'مغز گردو ممتاز', 'walnut-premium', 'مغز گردوی تازه و خوش‌طعم', 'walnut.jpg', 740000, 40, 'active', 1, '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(4, 2, 'انجیر خشک', 'dried-fig', 'انجیر خشک مرغوب و شیرین', 'dried-fig.jpg', 540000, 70, 'active', 1, '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(7, 1, 'آجیل مخلوط ممتاز حبیبی', 'product-1787922677-320', 'ترکیبی خوش‌طعم و باکیفیت از پسته، بادام، فندق و بادام هندی؛ مناسب پذیرایی، مهمانی و میان‌وعده روزانه. تهیه‌شده از خشکبار تازه با طعمی دلچسب و کیفیت ممتاز.', '1787922677-InShot_20260828_163701338.jpg', 1000000, 50, 'active', 0, '2026-08-28 13:11:17', '2026-08-28 13:12:32'),
(8, 1, 'آجیل شور ویژه حبیبی', 'product-1787923074-419', 'ترکیبی متنوع از پسته، بادام، فندق و بادام هندی با طعم شور و دلچسب؛ انتخابی مناسب برای دورهمی‌ها و پذیرایی از مهمانان', '1787923074-InShot_20260828_164311728.jpg', 500000, 30, 'active', 1, '2026-08-28 13:17:54', '2026-08-28 13:17:54'),
(9, 1, 'آجیل چهارمغز ممتاز حبیبی', 'product-1787923171-776', 'ترکیبی لوکس از چهار مغز محبوب شامل پسته، بادام، بادام هندی و فندق. انتخابی عالی برای کسانی که به دنبال طعم خاص و کیفیت بالا هستند.', '1787923171-InShot_20260828_164409707.jpg', 750000, 55, 'active', 0, '2026-08-28 13:19:31', '2026-08-28 13:19:31'),
(10, 2, 'برگه زردآلو ممتاز حبیبی', 'product-1787923536-547', 'برگه زردآلوی خوش‌طعم و باکیفیت با طعم شیرین و طبیعی؛ مناسب برای مصرف روزانه و تهیه انواع میان‌وعده و دسر.', '1787923536-InShot_20260828_165205271.jpg', 300000, 60, 'active', 0, '2026-08-28 13:25:36', '2026-08-28 13:25:36'),
(11, 3, 'بادام هندی بو داده حبیبی', 'product-1787923779-849', 'بادام هندی بو داده با طعمی خوشایند و بافتی ترد؛ گزینه‌ای عالی برای میان‌وعده و پذیرایی در کنار خانواده و دوستان.', '1787923779-InShot_20260828_165714063.jpg', 900000, 57, 'active', 0, '2026-08-28 13:29:39', '2026-08-28 13:29:39'),
(12, 2, 'سیب خشک ممتاز حبیبی', 'product-1787925068-606', 'برش‌های خوش‌طعم سیب خشک با عطر و طعم طبیعی میوه؛ انتخابی سبک و دلچسب برای میان‌وعده روزانه و پذیرایی.', '1787925068-InShot_20260828_171413592.jpg', 250000, 80, 'active', 0, '2026-08-28 13:51:08', '2026-08-28 13:51:08'),
(13, 2, 'موز خشک ترد حبیبی', 'product-1787925180-628', 'حلقه‌های موز خشک با بافت ترد و طعمی شیرین و دلچسب؛ مناسب برای میان‌وعده و پذیرایی در طول روز', '1787925180-InShot_20260828_171526513.jpg', 500000, 70, 'active', 0, '2026-08-28 13:53:00', '2026-08-28 13:53:00'),
(14, 2, 'توت خشک ممتاز حبیبی', 'product-1787926409-636', 'توت خشک با طعم شیرین و طبیعی و بافتی دلچسب؛ مناسب برای مصرف روزانه، میان‌وعده و پذیرایی.', '1787926409-InShot_20260828_171614073.jpg', 450000, 90, 'active', 0, '2026-08-28 14:13:29', '2026-08-28 14:13:29'),
(15, 4, 'زعفران سرگل ممتاز حبیبی', 'product-1787927076-798', 'زعفران سرگل ممتاز با عطر و رنگ طبیعی و کیفیت بالا؛ انتخابی مناسب برای تهیه انواع غذا، دسر و نوشیدنی‌های سنتی.', '1787927076-InShot_20260828_175118250.jpg', 2000000, 100, 'active', 0, '2026-08-28 14:24:36', '2026-08-28 14:24:36'),
(16, 4, 'نبات زعفرانی حبیبی', 'product-1787927198-646', 'ترکیبی از زعفران باکیفیت و نبات، مناسب برای تهیه نوشیدنی گرم و پذیرایی؛ بسته‌بندی زیبا و مناسب برای هدیه.', '1787927215-InShot_20260828_175140789.jpg', 500000, 200, 'active', 0, '2026-08-28 14:26:38', '2026-08-28 14:26:55'),
(17, 4, 'دارچین آسیاب‌شده حبیبی', 'product-1787927555-494', 'دارچین خوش‌عطر و باکیفیت با طعمی گرم و دلپذیر؛ مناسب برای غذا، دسر، شیرینی و انواع نوشیدنی گرم.', '1787927555-InShot_20260828_175852459.jpg', 300000, 45, 'active', 0, '2026-08-28 14:32:35', '2026-08-28 14:32:35'),
(18, 4, 'زردچوبه ممتاز حبیبی', 'product-1787927646-550', 'زردچوبه خالص و خوش‌رنگ با عطر و طعم مطلوب، مناسب برای استفاده در انواع غذاهای ایرانی و خانگی.', '1787927646-InShot_20260828_175920840.jpg', 300000, 30, 'active', 0, '2026-08-28 14:34:06', '2026-08-28 14:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `product_weights`
--

CREATE TABLE `product_weights` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `weight` decimal(8,2) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'گرم',
  `price` decimal(12,0) NOT NULL DEFAULT 0,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_weights`
--

INSERT INTO `product_weights` (`id`, `product_id`, `weight`, `unit`, `price`, `stock`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 250.00, 'گرم', 230000, 30, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(2, 1, 500.00, 'گرم', 430000, 30, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(3, 1, 1000.00, 'گرم', 850000, 20, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(4, 2, 250.00, 'گرم', 185000, 30, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(5, 2, 500.00, 'گرم', 350000, 30, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(6, 2, 1000.00, 'گرم', 690000, 20, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(7, 3, 250.00, 'گرم', 200000, 18, 'active', '2026-08-16 07:13:01', '2026-08-28 15:08:51'),
(8, 3, 500.00, 'گرم', 380000, 20, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(9, 3, 1000.00, 'گرم', 740000, 15, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(10, 4, 250.00, 'گرم', 145000, 29, 'active', '2026-08-16 07:13:01', '2026-08-25 03:10:05'),
(11, 4, 500.00, 'گرم', 280000, 30, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(12, 4, 1000.00, 'گرم', 540000, 20, 'active', '2026-08-16 07:13:01', '2026-08-16 07:13:01'),
(17, 7, 1.00, 'کیلو گرم', 1500000, 13, 'active', '2026-08-28 13:12:32', '2026-08-28 13:12:32'),
(18, 7, 500.00, 'گرم', 1000000, 11, 'active', '2026-08-28 13:12:32', '2026-08-28 13:12:32'),
(19, 8, 500.00, 'گرم', 500000, 30, 'active', '2026-08-28 13:17:54', '2026-08-28 13:17:54'),
(20, 8, 1000.00, 'گرم', 1000000, 30, 'active', '2026-08-28 13:17:54', '2026-08-28 13:17:54'),
(21, 9, 500.00, 'گرم', 750000, 30, 'active', '2026-08-28 13:19:31', '2026-08-28 13:19:31'),
(22, 9, 250.00, 'گرم', 300000, 34, 'active', '2026-08-28 13:19:31', '2026-08-28 13:19:31'),
(23, 10, 250.00, 'گرم', 300000, 20, 'active', '2026-08-28 13:25:36', '2026-08-28 13:25:36'),
(24, 10, 500.00, 'گرم', 550000, 30, 'active', '2026-08-28 13:25:36', '2026-08-28 13:25:36'),
(25, 10, 1.00, 'کیلو گرم', 900000, 10, 'active', '2026-08-28 13:25:36', '2026-08-28 13:25:36'),
(26, 11, 250.00, 'گرم', 900000, 27, 'active', '2026-08-28 13:29:39', '2026-08-28 13:29:39'),
(27, 11, 500.00, 'گرم', 1500000, 30, 'active', '2026-08-28 13:29:39', '2026-08-28 13:29:39'),
(28, 12, 250.00, 'گرم', 250000, 40, 'active', '2026-08-28 13:51:08', '2026-08-28 13:51:08'),
(29, 12, 500.00, 'گرم', 499000, 20, 'active', '2026-08-28 13:51:08', '2026-08-28 13:51:08'),
(30, 12, 1000.00, 'گرم', 900000, 10, 'active', '2026-08-28 13:51:08', '2026-08-28 13:51:08'),
(31, 13, 250.00, 'گرم', 500000, 30, 'active', '2026-08-28 13:53:00', '2026-08-28 13:53:00'),
(32, 13, 500.00, 'گرم', 850000, 20, 'active', '2026-08-28 13:53:00', '2026-08-28 13:53:00'),
(33, 13, 1000.00, 'گرم', 1300000, 20, 'active', '2026-08-28 13:53:00', '2026-08-28 13:53:00'),
(34, 14, 250.00, 'گرم', 450000, 90, 'active', '2026-08-28 14:13:29', '2026-08-28 14:13:29'),
(35, 15, 2.00, 'گرم', 2000000, 100, 'active', '2026-08-28 14:24:36', '2026-08-28 14:24:36'),
(38, 16, 500.00, 'گرم', 500000, 100, 'active', '2026-08-28 14:26:55', '2026-08-28 14:26:55'),
(39, 16, 1000.00, 'گرم', 1000000, 100, 'active', '2026-08-28 14:26:55', '2026-08-28 14:26:55'),
(40, 17, 250.00, 'گرم', 300000, 20, 'active', '2026-08-28 14:32:35', '2026-08-28 14:32:35'),
(41, 17, 500.00, 'گرم', 600000, 25, 'active', '2026-08-28 14:32:35', '2026-08-28 14:32:35'),
(42, 18, 250.00, 'گرم', 300000, 30, 'active', '2026-08-28 14:34:06', '2026-08-28 14:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'admin', 'مدیر سیستم', '2026-08-16 07:13:00'),
(2, 'customer', 'مشتری', '2026-08-16 07:13:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL DEFAULT 2,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `full_name` varchar(201) GENERATED ALWAYS AS (concat(`first_name`,' ',`last_name`)) STORED,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'شادی', 'حبیبی', 'shadi@gmail.com', '09121234567', '12345', 'active', '2026-08-24 16:54:16', '2026-08-24 17:02:15'),
(2, 2, 'gas', 'gas', 'sa@gmail.com', '0991234567', '$2y$10$z6P67PyINpWPZnCHObbdFeCxZBg0xa68j6p2d9MZ5TadQ2zJ8EXbm', 'active', '2026-08-24 17:14:49', '2026-08-24 17:20:51'),
(3, 1, 'مدیر', 'حبیبی', 'admin@habibi.ir', '09120000000', '$2y$10$0N5tx1z0cOplMfdcjVUD5e7FIzY0LyRe.1SLUFRvuPMpmIjouju42', 'active', '2026-08-25 03:26:26', '2026-08-25 03:26:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_addresses_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categories_status` (`status`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `fk_orders_address` (`address_id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_weight` (`product_weight_id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payments_order` (`order_id`),
  ADD KEY `idx_payments_status` (`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_status` (`status`),
  ADD KEY `idx_products_featured` (`is_featured`);

--
-- Indexes for table `product_weights`
--
ALTER TABLE `product_weights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_weights_product` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_weights`
--
ALTER TABLE `product_weights`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_weight` FOREIGN KEY (`product_weight_id`) REFERENCES `product_weights` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `product_weights`
--
ALTER TABLE `product_weights`
  ADD CONSTRAINT `fk_product_weights_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

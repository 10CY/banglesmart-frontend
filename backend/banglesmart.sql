-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2026 at 11:24 AM
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
-- Database: `banglesmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(255) NOT NULL DEFAULT 'India',
  `type` varchar(255) NOT NULL DEFAULT 'shipping',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `full_name`, `phone`, `address_line_1`, `address_line_2`, `landmark`, `city`, `state`, `postal_code`, `country`, `type`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 'Chandan Yadav - Eggfirst.com', '9978985111', 'Goregaon', NULL, NULL, 'Mumbai', 'Maharashtra', '400063', 'India', 'shipping', 1, '2026-08-22 00:52:09', '2026-08-22 05:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 2, '2026-08-22 01:12:28', '2026-08-22 01:12:28');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_variant_id`, `quantity`, `created_at`, `updated_at`) VALUES
(18, 1, 2, 1, '2026-08-27 04:27:21', '2026-08-27 04:27:21'),
(19, 1, 7, 1, '2026-08-27 04:27:49', '2026-08-27 04:27:49');

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `hex_code` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name`, `display_name`, `hex_code`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Maroon', 'Maroon', '#800020', 0, 'active', '2026-08-21 07:03:29', '2026-08-21 07:03:29'),
(2, 'Red', 'Red', '#FF0000', 0, 'active', '2026-08-21 07:03:46', '2026-08-21 07:03:59'),
(3, 'Gold', 'Gold', '#D4AF37', 0, 'active', '2026-08-21 07:04:23', '2026-08-21 07:04:23');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'fixed',
  `value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `minimum_order_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `maximum_discount_amount` decimal(12,2) DEFAULT NULL,
  `usage_limit` int(10) UNSIGNED DEFAULT NULL,
  `per_user_limit` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `created_at`, `updated_at`, `code`, `type`, `value`, `minimum_order_amount`, `maximum_discount_amount`, `usage_limit`, `per_user_limit`, `starts_at`, `expires_at`, `status`) VALUES
(1, '2026-08-22 03:42:29', '2026-08-22 04:05:53', 'SAVE20', 'fixed', 100.00, 1798.96, NULL, 5, 10, '2026-08-21 18:12:00', '2026-08-28 18:12:00', 'active'),
(2, '2026-08-22 03:59:50', '2026-08-22 04:04:46', 'SAVE50', 'fixed', 500.00, 0.00, NULL, 20, 5, '2026-08-21 14:58:00', '2026-08-26 14:58:00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usages`
--

CREATE TABLE `coupon_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupon_usages`
--

INSERT INTO `coupon_usages` (`id`, `coupon_id`, `user_id`, `order_id`, `discount_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 5, 100.00, '2026-08-22 04:15:56', '2026-08-22 04:15:56'),
(2, 1, 2, 6, 100.00, '2026-08-22 07:54:47', '2026-08-22 07:54:47'),
(3, 1, 2, 7, 100.00, '2026-08-24 01:53:40', '2026-08-24 01:53:40'),
(4, 2, 2, 8, 500.00, '2026-08-24 03:19:17', '2026-08-24 03:19:17');

-- --------------------------------------------------------

--
-- Table structure for table `inventories`
--

CREATE TABLE `inventories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reserved_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `low_stock_limit` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventories`
--

INSERT INTO `inventories` (`id`, `product_variant_id`, `quantity`, `reserved_quantity`, `low_stock_limit`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 4, 20, '2026-08-21 23:55:02', '2026-08-25 07:18:06'),
(2, 2, 10, 0, 15, '2026-08-24 03:34:47', '2026-08-25 06:58:15'),
(3, 3, 10, 0, 5, '2026-08-26 04:23:53', '2026-08-26 04:26:38'),
(4, 4, 5, 0, 5, '2026-08-26 04:24:13', '2026-08-26 04:26:47'),
(5, 5, 10, 0, 5, '2026-08-26 04:27:58', '2026-08-26 04:27:58'),
(6, 6, 10, 0, 2, '2026-08-26 07:29:52', '2026-08-26 07:29:52'),
(7, 7, 20, 0, 2, '2026-08-27 04:27:04', '2026-08-27 04:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `before_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `after_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `product_variant_id`, `user_id`, `type`, `quantity`, `before_quantity`, `after_quantity`, `reference_type`, `reference_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'adjustment', -6, 15, 9, NULL, NULL, 'Manual inventory adjustment', '2026-08-22 00:20:06', '2026-08-22 00:20:06'),
(2, 1, 2, 'order_reserved', -1, 7, 6, 'order', 3, 'Stock reserved for order BM-20260822-2LKLEZ', '2026-08-22 01:59:14', '2026-08-22 01:59:14'),
(3, 1, 1, 'order_delivered', -1, 7, 6, 'order', 3, 'Stock deducted for delivered order BM-20260822-2LKLEZ', '2026-08-22 02:00:04', '2026-08-22 02:00:04'),
(4, 1, 2, 'order_reserved', -2, 6, 4, 'order', 4, 'Stock reserved for order BM-20260822-9FW6XX', '2026-08-22 03:44:52', '2026-08-22 03:44:52'),
(5, 1, 2, 'order_reserved', -1, 4, 3, 'order', 5, 'Stock reserved for order BM-20260822-8DWYPI', '2026-08-22 04:15:56', '2026-08-22 04:15:56'),
(6, 1, 2, 'order_cancelled', 2, 3, 5, 'order', 4, 'Stock released after cancelling order BM-20260822-9FW6XX', '2026-08-22 06:35:03', '2026-08-22 06:35:03'),
(7, 1, 2, 'order_reserved', -1, 5, 4, 'order', 6, 'Stock reserved for order BM-20260822-CBUQWL', '2026-08-22 07:54:47', '2026-08-22 07:54:47'),
(8, 1, 2, 'order_reserved', -1, 4, 3, 'order', 7, 'Stock reserved for order BM-20260824-3QXOAP', '2026-08-24 01:53:40', '2026-08-24 01:53:40'),
(9, 1, 1, 'order_delivered', -1, 6, 5, 'order', 7, 'Stock deducted for delivered order BM-20260824-3QXOAP', '2026-08-24 01:58:51', '2026-08-24 01:58:51'),
(10, 1, 2, 'order_reserved', -1, 3, 2, 'order', 8, 'Stock reserved for order BM-20260824-N7ASMI', '2026-08-24 03:19:17', '2026-08-24 03:19:17'),
(11, 1, 2, 'order_cancelled', 1, 2, 3, 'order', 8, 'Stock released after cancelling order BM-20260824-N7ASMI', '2026-08-24 03:25:08', '2026-08-24 03:25:08'),
(12, 1, 2, 'order_reserved', -1, 3, 2, 'order', 9, 'Stock reserved for order BM-20260824-84XOZO', '2026-08-24 03:44:46', '2026-08-24 03:44:46'),
(13, 2, 1, 'adjustment', 10, 0, 10, NULL, NULL, 'Manual inventory adjustment', '2026-08-25 06:58:15', '2026-08-25 06:58:15'),
(14, 1, 2, 'order_reserved', -1, 7, 6, 'order', 10, 'Stock reserved for order BM-20260825-DAUPMV', '2026-08-25 07:18:06', '2026-08-25 07:18:06');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'issued',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `order_id`, `invoice_number`, `issued_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 9, 'BM-INV-2026-000009', '2026-08-25 00:59:00', 'issued', '2026-08-25 00:59:00', '2026-08-25 00:59:00'),
(2, 10, 'BM-INV-2026-000010', '2026-08-25 07:18:06', 'issued', '2026-08-25 07:18:06', '2026-08-25 07:18:06');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) NOT NULL DEFAULT 'cod',
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `courier_name` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `coupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coupon_code` varchar(255) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`shipping_address`)),
  `billing_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`billing_address`)),
  `customer_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `payment_method`, `payment_status`, `courier_name`, `tracking_number`, `shipped_at`, `delivered_at`, `cancelled_at`, `subtotal`, `shipping_amount`, `discount_amount`, `coupon_id`, `coupon_code`, `total_amount`, `shipping_address`, `billing_address`, `customer_note`, `created_at`, `updated_at`) VALUES
(1, 2, 'BM-20260822-5QNKX1', 'delivered', 'cod', 'paid', NULL, NULL, NULL, NULL, NULL, 1798.97, 0.00, 0.00, NULL, NULL, 1798.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-22 01:22:26', '2026-08-22 01:35:53'),
(2, 2, 'BM-20260822-67RBMC', 'delivered', 'cod', 'paid', NULL, NULL, NULL, NULL, NULL, 1798.97, 0.00, 0.00, NULL, NULL, 1798.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-22 01:32:09', '2026-08-22 01:34:59'),
(3, 2, 'BM-20260822-2LKLEZ', 'delivered', 'cod', 'paid', NULL, NULL, NULL, NULL, NULL, 1798.97, 0.00, 0.00, NULL, NULL, 1798.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-22 01:59:14', '2026-08-22 02:00:04'),
(4, 2, 'BM-20260822-9FW6XX', 'cancelled', 'cod', 'pending', NULL, NULL, NULL, NULL, NULL, 3597.94, 0.00, 0.00, NULL, NULL, 3597.94, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-22 03:44:52', '2026-08-22 06:35:03'),
(5, 2, 'BM-20260822-8DWYPI', 'cancelled', 'cod', 'pending', NULL, NULL, NULL, NULL, NULL, 1798.97, 0.00, 100.00, 1, 'SAVE20', 1698.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9856989896\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-22 04:15:56', '2026-08-22 06:27:16'),
(6, 2, 'BM-20260822-CBUQWL', 'pending', 'cod', 'pending', NULL, NULL, NULL, NULL, NULL, 1798.97, 99.00, 100.00, 1, 'SAVE20', 1797.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-22 07:54:46', '2026-08-22 07:54:46'),
(7, 2, 'BM-20260824-3QXOAP', 'delivered', 'cod', 'paid', NULL, NULL, NULL, NULL, NULL, 1798.97, 99.00, 100.00, 1, 'SAVE20', 1797.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-24 01:53:40', '2026-08-24 01:58:51'),
(8, 2, 'BM-20260824-N7ASMI', 'cancelled', 'cod', 'pending', NULL, NULL, NULL, NULL, NULL, 1798.97, 99.00, 500.00, 2, 'SAVE50', 1397.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-24 03:19:17', '2026-08-24 03:25:08'),
(9, 2, 'BM-20260824-84XOZO', 'pending', 'cod', 'pending', NULL, NULL, NULL, NULL, NULL, 1798.97, 99.00, 0.00, NULL, NULL, 1897.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-24 03:44:46', '2026-08-24 03:44:46'),
(10, 2, 'BM-20260825-DAUPMV', 'pending', 'cod', 'pending', NULL, NULL, NULL, NULL, NULL, 1798.97, 99.00, 0.00, NULL, NULL, 1897.97, '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', '{\"full_name\":\"Chandan Yadav - Eggfirst.com\",\"phone\":\"9978985111\",\"address_line_1\":\"Goregaon\",\"address_line_2\":null,\"landmark\":null,\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"postal_code\":\"400063\",\"country\":\"India\"}', NULL, '2026-08-25 07:18:06', '2026-08-25 07:18:06');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `variant_sku` varchar(255) NOT NULL,
  `size_name` varchar(255) DEFAULT NULL,
  `color_name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `mrp` decimal(12,2) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_variant_id`, `product_name`, `variant_sku`, `size_name`, `color_name`, `image`, `mrp`, `price`, `quantity`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-22 01:22:26', '2026-08-22 01:22:26'),
(2, 2, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-22 01:32:09', '2026-08-22 01:32:09'),
(3, 3, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-22 01:59:14', '2026-08-22 01:59:14'),
(4, 4, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 2, 3597.94, '2026-08-22 03:44:52', '2026-08-22 03:44:52'),
(5, 5, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-22 04:15:56', '2026-08-22 04:15:56'),
(6, 6, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-22 07:54:47', '2026-08-22 07:54:47'),
(7, 7, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-24 01:53:40', '2026-08-24 01:53:40'),
(8, 8, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-24 03:19:17', '2026-08-24 03:19:17'),
(9, 9, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-24 03:44:46', '2026-08-24 03:44:46'),
(10, 10, 2, 1, 'Royal Kundan Bridal Bangle Set', 'RKB-MAR-24', '2.2', 'Maroon', 'products/3qlUwF6RkgkcRVeVAF4f24VFIlGawz5XLljvOBeq.png', 2499.00, 1798.97, 1, 1798.97, '2026-08-25 07:18:06', '2026-08-25 07:18:06');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(17, 'App\\Models\\User', 2, 'customer-token', '3cf2fef3c413ebb17764cd765c3ff789a71e86d905c9473863e457c73c7bf28e', '[\"*\"]', '2026-08-22 04:57:08', NULL, '2026-08-22 00:37:03', '2026-08-22 04:57:08'),
(19, 'App\\Models\\User', 2, 'customer-token', '86c5b89d5c0ec02e2e1fca96d272d359aab0ae8c7f8843b8828db7c1a32152a7', '[\"*\"]', '2026-08-22 04:58:07', NULL, '2026-08-22 04:57:53', '2026-08-22 04:58:07'),
(20, 'App\\Models\\User', 2, 'customer-token', 'be266f86d8e0947267afecf0f661d035649faa7d48c81cedbac9c28c90a0d366', '[\"*\"]', '2026-08-22 04:58:10', NULL, '2026-08-22 04:58:09', '2026-08-22 04:58:10'),
(21, 'App\\Models\\User', 2, 'customer-token', 'b50de078befa25807200bb688b3bbc052f795601b5e44561a7a8fd081d1c11e5', '[\"*\"]', '2026-08-22 04:58:13', NULL, '2026-08-22 04:58:12', '2026-08-22 04:58:13'),
(23, 'App\\Models\\User', 2, 'customer-token', 'bd933589e50540985c230fa18b6e87a14eaf7a500d6f0ec95829806ef7e2cbae', '[\"*\"]', '2026-08-23 23:55:57', NULL, '2026-08-22 07:21:08', '2026-08-23 23:55:57'),
(24, 'App\\Models\\User', 2, 'customer-token', '5d1d22907ce83e9eee6ec574a7738427704e52a03d6a60736eda7a61182a2e7a', '[\"*\"]', '2026-08-24 00:53:29', NULL, '2026-08-23 23:56:00', '2026-08-24 00:53:29'),
(26, 'App\\Models\\User', 2, 'customer-token', '7e590314735fee026ffab1a472c211d46caccfd7b40af6ebdb2248125b083191', '[\"*\"]', '2026-08-25 00:47:52', NULL, '2026-08-24 00:53:46', '2026-08-25 00:47:52'),
(28, 'App\\Models\\User', 2, 'customer-token', '9db015c1199050c25a9731706770f85a8a495e9b17089c4f7988dd47250495b0', '[\"*\"]', '2026-08-25 01:03:27', NULL, '2026-08-24 01:52:52', '2026-08-25 01:03:27'),
(34, 'App\\Models\\User', 2, 'customer-token', 'ed2cf2aee7a309fc572bd2158b0a758a60871a90f67298e10d20f92a06b36c95', '[\"*\"]', '2026-08-26 07:30:15', NULL, '2026-08-25 01:41:56', '2026-08-26 07:30:15'),
(39, 'App\\Models\\User', 2, 'customer-token', 'a0380202f50ca5eb5ab359a0ad90939c13289f7b7be3907828fb19832cfbbbc3', '[\"*\"]', '2026-08-27 07:06:08', NULL, '2026-08-26 23:56:56', '2026-08-27 07:06:08'),
(40, 'App\\Models\\User', 1, 'admin-token', 'e6f1d96fe28bd8f8cc2caf02f922515e755ea683aa281627738da23202d226be', '[\"*\"]', '2026-08-27 06:27:34', NULL, '2026-08-27 04:26:17', '2026-08-27 06:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `alt_text`, `sort_order`, `is_primary`, `created_at`, `updated_at`) VALUES
(21, 6, 'products/c5lkPCfDHfM4HZAm5e34CGaUEHuNiMM8yWSI88tP.webp', 'Ruby Red Stone Bangles', 0, 1, '2026-08-27 06:24:10', '2026-08-27 06:24:10'),
(22, 5, 'products/vqVn2YLmMUw9MPwEvuAuh3punJkkvUIxnjrtb133.webp', 'Pearl Elegance Gold Bangles', 0, 1, '2026-08-27 06:24:27', '2026-08-27 06:24:27'),
(23, 4, 'products/M7oZ1FLi7en1jik043Z4ZFbA7e37JJ6E8hUDZyTf.webp', 'Golden Pearl Bangles', 0, 0, '2026-08-27 06:24:58', '2026-08-27 06:25:01'),
(24, 4, 'products/zRfbeUEe1dq9gluwT2nzDKfHlM4s3678eBsHrfpe.webp', 'Golden Pearl Bangles', 1, 1, '2026-08-27 06:24:58', '2026-08-27 06:25:01'),
(25, 3, 'products/vDaQaqshFq4CcVJmk9hTDJidga8URZBnSoglNtkM.webp', 'Royal Red Stone Bangles', 0, 0, '2026-08-27 06:25:37', '2026-08-27 06:25:39'),
(26, 3, 'products/IYfkdtxDxH7VHuwNNNyLGxX9zH5CLpl27RRfl4n3.webp', 'Royal Red Stone Bangles', 1, 1, '2026-08-27 06:25:37', '2026-08-27 06:25:39'),
(27, 2, 'products/2MP7w63RCuXwIkZBkiAvvaMzn81y6V8otPV1GcQY.webp', 'Royal Kundan Bridal Bangle Set', 0, 1, '2026-08-27 06:26:07', '2026-08-27 06:26:13'),
(28, 2, 'products/wqcUW35uxVCqJuAk8tpgsBVpbOmyp3G8YOmjf914.webp', 'Royal Kundan Bridal Bangle Set', 1, 0, '2026-08-27 06:26:07', '2026-08-27 06:26:13'),
(29, 2, 'products/9km4PcwsA91TQEuFIBYRXuPX3A2GpQPZo1j4YCxv.webp', 'Royal Kundan Bridal Bangle Set', 2, 0, '2026-08-27 06:26:07', '2026-08-27 06:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `size_id` bigint(20) UNSIGNED NOT NULL,
  `color_id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(255) NOT NULL,
  `mrp` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size_id`, `color_id`, `sku`, `mrp`, `selling_price`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 'RKB-MAR-24', 2499.00, 1798.97, 'active', '2026-08-21 23:55:02', '2026-08-21 23:55:02'),
(2, 3, 2, 2, 'RKB-MAR-25', 2500.00, 2000.00, 'active', '2026-08-24 03:34:47', '2026-08-24 03:34:47'),
(3, 4, 1, 1, 'RKB-24', 1500.00, 1400.00, 'active', '2026-08-26 04:23:53', '2026-08-26 04:23:53'),
(4, 4, 2, 3, 'sdf', 1000.00, 500.00, 'active', '2026-08-26 04:24:13', '2026-08-26 04:24:13'),
(5, 4, 1, 2, 'RKB-26', 1500.00, 1000.00, 'active', '2026-08-26 04:27:58', '2026-08-26 04:27:58'),
(6, 6, 1, 1, 'Rkb-265', 500.00, 350.00, 'active', '2026-08-26 07:29:52', '2026-08-26 07:29:52'),
(7, 5, 1, 1, 'RKB-2689', 1500.00, 100.00, 'active', '2026-08-27 04:27:04', '2026-08-27 04:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 7, 4, 'Test', 'tes', 'approved', '2026-08-25 00:58:42', '2026-08-25 01:01:16');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_settings`
--

CREATE TABLE `shipping_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `flat_shipping_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `free_shipping_minimum` decimal(12,2) DEFAULT NULL,
  `shipping_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_settings`
--

INSERT INTO `shipping_settings` (`id`, `flat_shipping_amount`, `free_shipping_minimum`, `shipping_enabled`, `created_at`, `updated_at`) VALUES
(1, 99.00, 2000.00, 1, '2026-08-22 04:24:09', '2026-08-22 04:26:31');

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`id`, `name`, `display_name`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, '2.2', '2.2', 0, 'active', '2026-08-21 06:54:52', '2026-08-21 06:54:52'),
(2, '2.3', '2.3', 0, 'active', '2026-08-21 06:54:59', '2026-08-21 06:54:59'),
(3, '2.4', '2.4', 0, 'active', '2026-08-21 06:55:06', '2026-08-21 06:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'customer',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'BanglesMart Admin', 'admin@banglesmart.com', NULL, NULL, '$2y$10$iibhYiF2aEhUR5CQtTsWS.nmUnHzMeSyrswfOCSX4uZLpVdFAOpou', 'admin', 'active', NULL, '2026-08-21 04:44:00', '2026-08-21 04:44:00'),
(2, 'Chandan', 'web@eggfirst.com', '9978985111', NULL, '$2y$12$xx5Tn0UbluXMMfeFOiyY8effmDFihnf9GWfRJQpA9VwQ7/QChS1Kq', 'customer', 'active', NULL, '2026-08-22 00:36:52', '2026-08-22 05:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 2, '2026-08-22 03:08:32', '2026-08-22 03:08:32');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wishlist_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_is_default_index` (`user_id`,`is_default`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carts_user_id_unique` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_cart_id_product_variant_id_unique` (`cart_id`,`product_variant_id`),
  ADD KEY `cart_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colors_name_unique` (`name`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Indexes for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_usages_order_id_unique` (`order_id`),
  ADD KEY `coupon_usages_user_id_foreign` (`user_id`),
  ADD KEY `coupon_usages_coupon_id_user_id_index` (`coupon_id`,`user_id`);

--
-- Indexes for table `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventories_product_variant_id_unique` (`product_variant_id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_movements_user_id_foreign` (`user_id`),
  ADD KEY `inventory_movements_product_variant_id_created_at_index` (`product_variant_id`,`created_at`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_order_id_unique` (`order_id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `orders_status_payment_status_index` (`status`,`payment_status`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_product_variant_id_index` (`product_variant_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_size_color_unique` (`product_id`,`size_id`,`color_id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD KEY `product_variants_size_id_foreign` (`size_id`),
  ADD KEY `product_variants_color_id_foreign` (`color_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reviews_product_id_user_id_unique` (`product_id`,`user_id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`),
  ADD KEY `reviews_order_id_foreign` (`order_id`),
  ADD KEY `reviews_product_id_status_index` (`product_id`,`status`);

--
-- Indexes for table `shipping_settings`
--
ALTER TABLE `shipping_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sizes_name_unique` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_unique` (`user_id`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlist_items_wishlist_id_product_id_unique` (`wishlist_id`,`product_id`),
  ADD KEY `wishlist_items_product_id_foreign` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipping_settings`
--
ALTER TABLE `shipping_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  ADD CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventories`
--
ALTER TABLE `inventories`
  ADD CONSTRAINT `inventories_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`),
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variants_size_id_foreign` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_items_wishlist_id_foreign` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlists` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

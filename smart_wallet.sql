-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 09:35 PM
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
-- Database: `smart_wallet`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE `budget` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget`
--

INSERT INTO `budget` (`id`, `user_id`, `name`, `total_amount`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(18, 1, 'April 2025', 20000.00, '2025-04-01', '2025-04-30', '2025-04-14 09:45:43', '2025-04-14 09:45:43'),
(20, 1, '2025-05', 5400.00, '2025-05-01', '2025-05-31', '2025-05-07 14:06:46', '2025-05-07 14:06:46'),
(21, 2, 'May 2025', 5000.00, '2025-05-01', '2025-05-31', '2025-05-07 16:58:16', '2025-05-07 16:58:16'),
(22, 2, '2025-04', 5000.00, '2025-04-01', '2025-04-30', '2025-05-07 18:28:09', '2025-05-07 18:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `budget_category`
--

CREATE TABLE `budget_category` (
  `budget_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `allocated_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_category`
--

INSERT INTO `budget_category` (`budget_id`, `category_id`, `allocated_amount`, `created_at`) VALUES
(18, 11, 1000.00, '2025-04-14 09:45:43'),
(18, 13, 1000.00, '2025-04-14 09:45:43'),
(18, 15, 300.00, '2025-04-14 09:45:43'),
(18, 17, 700.00, '2025-04-14 09:45:43'),
(20, 17, 3000.00, '2025-05-07 14:06:46'),
(21, 6, 800.00, '2025-05-07 16:59:36'),
(21, 7, 300.00, '2025-05-07 16:59:36'),
(21, 8, 400.00, '2025-05-07 16:59:36'),
(21, 9, 600.00, '2025-05-07 16:59:36'),
(21, 11, 500.00, '2025-05-07 16:59:36'),
(21, 12, 300.00, '2025-05-07 16:59:36'),
(21, 14, 50.00, '2025-05-07 16:59:36'),
(22, 7, 1200.00, '2025-05-07 18:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(6, 'Rental Income', 'From real estate, equipment', '2025-04-05 08:39:05', '2025-04-05 08:39:05'),
(7, 'Gift', 'Money received as gift', '2025-04-05 08:39:05', '2025-04-05 08:39:05'),
(8, 'Refunds', 'Reimbursements, returned money', '2025-04-05 08:39:05', '2025-04-05 08:39:05'),
(9, 'Other', 'Catch-all for uncategorized income', '2025-04-05 08:39:05', '2025-04-05 08:39:05'),
(11, 'Groceries', 'Food and supermarket purchases', '2025-04-12 13:18:55', '2025-04-12 13:18:55'),
(12, 'Rent', 'Monthly house rent', '2025-04-12 13:18:55', '2025-04-12 13:18:55'),
(13, 'Utilities', 'Water, electricity, gas, internet', '2025-04-12 13:18:55', '2025-04-12 13:18:55'),
(14, 'Transportation', 'Public transit or fuel', '2025-04-12 13:18:55', '2025-04-12 13:18:55'),
(15, 'Entertainment', 'Movies, games, subscriptions', '2025-04-12 13:18:55', '2025-04-12 13:18:55'),
(16, 'Healthcare', 'Medicine, insurance, doctor visits', '2025-04-12 13:18:55', '2025-04-12 13:18:55'),
(17, 'Shopping', 'Clothing and non-essentials', '2025-04-12 13:18:55', '2025-04-12 13:18:55'),
(18, 'Dining Out', 'Restaurants and cafes', '2025-04-12 13:18:55', '2025-04-12 13:18:55'),
(19, 'Education', 'School or training fees', '2025-04-12 13:18:55', '2025-04-12 13:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `debt`
--

CREATE TABLE `debt` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `debt`
--

INSERT INTO `debt` (`id`, `title`, `amount`, `description`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Student Loan', 3000.00, '', '2025-07-16', 'pending', '2025-05-07 17:31:01', '2025-05-07 17:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `debt_payments`
--

CREATE TABLE `debt_payments` (
  `debt_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `debt_payments`
--

INSERT INTO `debt_payments` (`debt_id`, `user_id`, `amount`, `payment_date`, `note`, `created_at`, `updated_at`) VALUES
(6, 2, 1700.00, '2025-05-07', '', '2025-05-07 18:30:56', '2025-05-07 18:30:56');

-- --------------------------------------------------------

--
-- Table structure for table `expense`
--

CREATE TABLE `expense` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense`
--

INSERT INTO `expense` (`id`, `user_id`, `category_id`, `amount`, `description`, `expense_date`, `created_at`, `updated_at`) VALUES
(96, 1, 7, 1000.00, 'Gift for bf', '2025-05-07', '2025-05-06 22:26:48', '2025-05-07 12:06:19'),
(97, 1, 16, 150.00, '', '2025-05-05', '2025-05-07 06:41:33', '2025-05-07 06:41:33'),
(98, 1, 13, 200.00, '', '2025-04-07', '2025-05-07 07:36:28', '2025-05-07 07:36:28'),
(99, 1, 15, 420.00, '', '2024-08-07', '2025-05-07 07:50:22', '2025-05-07 07:50:22'),
(100, 1, 9, 500.00, '', '2025-05-07', '2025-05-07 14:24:16', '2025-05-07 14:24:16'),
(101, 1, 17, 1000.00, '', '2025-05-07', '2025-05-07 14:37:26', '2025-05-07 14:37:26'),
(150, 2, 6, 25.00, 'Vitamin C tablets', '2024-01-12', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(151, 2, 7, 120.00, 'Online course fee', '2024-01-25', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(152, 2, 8, 200.00, 'Monthly savings deposit', '2024-02-01', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(153, 2, 9, 75.00, 'Credit card bill', '2024-02-18', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(154, 2, 11, 15.00, NULL, '2024-02-21', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(155, 2, 11, 80.00, 'Mobile data plan', '2024-03-05', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(156, 2, 12, 60.00, 'Streaming services', '2024-03-10', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(157, 2, 13, 35.00, 'Clinic visit', '2024-03-17', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(158, 2, 14, 45.00, NULL, '2024-03-22', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(159, 2, 15, 150.00, 'Tuition fee', '2024-04-03', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(160, 2, 16, 90.00, 'Transfer to savings account', '2024-04-09', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(161, 2, 17, 40.00, 'Loan payment', '2024-04-15', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(162, 2, 18, 22.50, NULL, '2024-04-20', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(163, 2, 19, 10.00, 'Unplanned expense', '2024-04-28', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(164, 2, 6, 30.00, 'Prescription refill', '2024-05-02', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(165, 2, 7, 100.00, NULL, '2024-05-04', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(166, 2, 8, 250.00, 'Emergency savings top-up', '2024-05-06', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(167, 2, 9, 60.00, NULL, '2024-06-10', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(168, 2, 14, 18.75, 'Snacks and drinks', '2024-06-18', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(169, 2, 11, 95.00, 'Utility bill payment', '2024-07-01', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(170, 2, 12, 70.00, 'Subscription renewal', '2024-07-15', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(171, 2, 13, 50.00, 'Pharmacy purchase', '2024-07-23', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(172, 2, 14, 42.00, NULL, '2024-08-05', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(173, 2, 15, 180.00, 'Online course', '2024-08-18', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(174, 2, 16, 85.00, 'Savings transfer', '2024-08-25', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(175, 2, 17, 55.00, 'Debt installment', '2024-09-02', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(176, 2, 18, 20.00, NULL, '2024-09-11', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(177, 2, 19, 12.00, 'Unexpected item', '2024-10-03', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(178, 2, 6, 32.00, 'Multivitamins', '2024-10-18', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(179, 2, 7, 140.00, NULL, '2024-11-06', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(180, 2, 8, 230.00, 'Monthly savings', '2024-11-20', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(181, 2, 9, 68.00, NULL, '2024-12-01', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(182, 2, 7, 19.99, 'Drinks and snacks', '2024-12-15', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(183, 2, 11, 88.00, 'Electricity bill', '2024-12-28', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(184, 2, 12, 65.00, 'Streaming plan', '2025-01-05', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(185, 2, 13, 45.00, 'Clinic service', '2025-01-14', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(186, 2, 14, 38.50, NULL, '2025-01-27', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(187, 2, 15, 165.00, 'Course materials', '2025-02-04', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(188, 2, 16, 90.00, 'Emergency savings', '2025-02-15', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(189, 2, 17, 42.00, 'Loan repayment', '2025-02-27', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(190, 2, 18, 25.00, NULL, '2025-03-03', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(191, 2, 19, 11.00, 'Miscellaneous', '2025-03-11', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(192, 2, 6, 28.00, 'Cold medicine', '2025-03-19', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(193, 2, 7, 115.00, NULL, '2025-03-28', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(194, 2, 8, 240.00, 'Monthly goal savings', '2025-04-06', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(195, 2, 9, 70.00, 'Card payment', '2025-04-12', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(196, 2, 7, 20.00, NULL, '2025-04-17', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(197, 2, 11, 92.00, 'Water and gas bill', '2025-04-25', '2025-05-07 16:43:30', '2025-05-07 16:43:30'),
(198, 2, 6, 100.00, 'Groceries', '2025-05-01', '2025-05-07 16:52:05', '2025-05-07 16:52:05'),
(199, 2, 7, 50.00, 'Transportation', '2025-05-02', '2025-05-07 16:52:05', '2025-05-07 16:52:05'),
(200, 2, 8, 200.00, 'Dining out', '2025-05-03', '2025-05-07 16:52:05', '2025-05-07 16:52:05'),
(201, 2, 9, 150.00, 'Shopping', '2025-05-04', '2025-05-07 16:52:05', '2025-05-07 16:52:05'),
(202, 2, 14, 80.00, 'Utilities', '2025-05-05', '2025-05-07 16:52:05', '2025-05-07 16:52:05'),
(203, 2, 11, 300.00, 'Entertainment', '2025-05-06', '2025-05-07 16:52:05', '2025-05-07 16:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('pending','confirmed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income`
--

INSERT INTO `income` (`id`, `user_id`, `amount`, `description`, `date`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, 10000, '+Bonus', '2025-04-06', 'confirmed', '2025-04-06 17:05:32', '2025-04-14 09:42:57'),
(4, 1, 50000, 'Investment', '2025-05-06', 'pending', '2025-05-06 17:50:52', '2025-05-06 17:50:52'),
(5, 2, 1821.75, 'Refund', '2024-01-01', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(6, 2, 1869.77, 'Side Job', '2024-01-31', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(7, 2, 1754.71, 'Rent Payment', '2024-03-01', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(8, 2, 990.43, 'Gift', '2024-03-31', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(9, 2, 1805.74, 'Side Job', '2024-04-30', 'confirmed', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(10, 2, 1878.3, 'Rent Payment', '2024-05-30', 'confirmed', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(11, 2, 800.2, 'Gift', '2024-06-29', 'confirmed', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(12, 2, 630.01, 'Freelance Work', '2024-07-29', 'confirmed', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(13, 2, 511.78, 'Refund', '2024-08-28', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(14, 2, 1091.03, 'Bonus', '2024-09-27', 'confirmed', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(15, 2, 1106.16, 'Investment Return', '2024-10-27', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(16, 2, 1759.41, 'Investment Return', '2024-11-26', 'confirmed', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(17, 2, 2014.31, 'Side Job', '2024-12-26', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(18, 2, 1241.1, 'Investment Return', '2025-01-25', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(19, 2, 1686.63, 'Investment Return', '2025-02-24', 'confirmed', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(20, 2, 502.14, 'Investment Return', '2025-03-26', 'pending', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(21, 2, 1269.34, 'Bonus', '2025-04-25', 'confirmed', '2025-05-07 10:05:24', '2025-05-07 10:05:24'),
(22, 2, 1500, 'Salary for May', '2025-05-01', 'confirmed', '2025-05-07 16:51:35', '2025-05-07 16:51:35'),
(23, 2, 200, 'Freelance project', '2025-05-02', 'pending', '2025-05-07 16:51:35', '2025-05-07 16:51:35'),
(24, 2, 300, 'Side hustle earnings', '2025-05-03', 'confirmed', '2025-05-07 16:51:35', '2025-05-07 16:51:35'),
(25, 2, 500, 'Freelance project payment', '2025-05-04', 'confirmed', '2025-05-07 16:51:35', '2025-05-07 16:51:35'),
(26, 2, 1000, 'Income from investments', '2025-05-05', 'pending', '2025-05-07 16:51:35', '2025-05-07 16:51:35'),
(27, 2, 250, 'Cashback rewards', '2025-05-06', 'confirmed', '2025-05-07 16:51:35', '2025-05-07 16:51:35'),
(28, 2, 400, 'Additional freelance payment', '2025-05-07', 'confirmed', '2025-05-07 16:51:35', '2025-05-07 16:51:35'),
(29, 2, 4100, 'investment', '2025-05-07', 'pending', '2025-05-07 18:20:01', '2025-05-07 18:20:01');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('system','email') NOT NULL,
  `category` enum('reminder','warning','success','info') DEFAULT 'info',
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_sent` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `category`, `message`, `is_read`, `is_sent`, `created_at`) VALUES
(1, 1, '', 'info', '🔔 This is a test notification!', 1, 0, '2025-04-15 22:08:41'),
(2, 1, '', 'info', '🔔 This is a test notification!', 1, 0, '2025-04-15 22:22:33'),
(3, 1, '', 'info', '🔔 This is a test notification!', 0, 0, '2025-04-15 22:23:34'),
(4, 1, '', 'info', '🔔 This is a test notification!', 0, 0, '2025-04-15 22:36:52'),
(5, 1, 'system', 'info', '🔔 This is a test notification!', 0, 0, '2025-04-15 23:03:59'),
(6, 1, 'system', 'info', '🔔 This is a test notification!', 0, 0, '2025-04-16 16:18:04'),
(7, 1, '', 'info', 'New debt created: \'Debt Test 2\' due on 2025-05-02.', 0, 0, '2025-04-16 17:53:40'),
(8, 1, '', 'info', '🎉 Your debt \"4\" has been fully paid!', 0, 0, '2025-04-16 17:55:38'),
(9, 1, '', 'info', 'Debt payment of $150 recorded for debt ID #4.', 0, 0, '2025-04-16 17:55:38'),
(10, 1, '', 'info', '🎉 Your debt \"{\'\'}\" has been fully paid!', 0, 0, '2025-04-16 18:07:12'),
(11, 1, '', 'info', 'Debt payment of $10 recorded for debt ID #4.', 0, 0, '2025-04-16 18:07:12'),
(12, 1, '', 'info', 'New debt created: \'Noti test\' due on 2025-04-24.', 0, 0, '2025-04-16 18:30:23'),
(13, 1, '', 'info', '🎉 Your debt \"\" has been fully paid!', 0, 0, '2025-04-16 18:31:11'),
(14, 1, '', 'info', 'Debt payment of $50 recorded for .', 0, 0, '2025-04-16 18:31:11'),
(15, 1, '', 'info', '🎉 Your debt \"Noti test\" has been fully paid!', 0, 0, '2025-04-16 18:38:47'),
(16, 1, '', 'info', 'Debt payment of $10 recorded for Noti test.', 0, 0, '2025-04-16 18:38:47'),
(17, 1, '', 'info', '⚠️ Your debt \"Debt Test 1\" is overdue!', 0, 0, '2025-04-16 18:50:56'),
(18, 1, '', 'info', '🔔 Reminder: Your debt \"Debt Test 1\" is due tomorrow.', 0, 0, '2025-04-16 19:02:16'),
(19, 1, '', 'info', '💰 $1000 added to \"New Laptop\" savings.', 0, 0, '2025-04-16 19:35:44'),
(20, 1, '', 'info', '🌟 New goal \"Vacation\" has been added.', 0, 0, '2025-04-16 19:37:12'),
(21, 1, '', 'info', '🌟 New goal \"Necklace for Mom\" has been added.', 1, 0, '2025-05-07 16:50:55'),
(22, 1, '', 'info', '💰 $500 added to \"New Laptop\" savings.', 0, 0, '2025-05-07 22:34:24'),
(23, 2, '', 'info', '🌟 New goal \"iWatch\" has been added.', 0, 0, '2025-05-07 23:55:46'),
(24, 2, '', 'info', 'New debt created: \'Student Loan\' due on 2025-07-16.', 0, 0, '2025-05-08 00:01:01'),
(25, 2, '', 'info', '🌟 New goal \"Vacation\" has been added.', 0, 0, '2025-05-08 00:59:52'),
(26, 2, '', 'info', '🔚 Goal \"\" was deleted.', 0, 0, '2025-05-08 01:00:26'),
(27, 2, '', 'info', '🎉 Your debt \"Unknown\" has been fully paid!', 0, 0, '2025-05-08 01:00:56'),
(28, 2, '', 'info', 'Debt payment of $1700 recorded for Unknown.', 0, 0, '2025-05-08 01:00:56');

-- --------------------------------------------------------

--
-- Table structure for table `savings`
--

CREATE TABLE `savings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goal_name` varchar(100) NOT NULL,
  `target_amount` float NOT NULL,
  `current_amount` float DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `type` enum('short-term','long-term') NOT NULL,
  `status` enum('in-progress','completed') DEFAULT 'in-progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `savings`
--

INSERT INTO `savings` (`id`, `user_id`, `goal_name`, `target_amount`, `current_amount`, `start_date`, `end_date`, `type`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'New Laptop', 2000, 1500, '2025-04-05', '2025-04-25', 'short-term', 'in-progress', '2025-04-05 08:33:01', '2025-05-07 16:04:24'),
(2, 1, 'Vacation', 1000, 0, '2025-04-16', '2025-04-19', 'short-term', 'in-progress', '2025-04-16 13:07:12', '2025-04-16 13:07:12'),
(3, 1, 'Necklace for Mom', 1500, 0, '2025-05-01', '2025-05-29', 'short-term', 'in-progress', '2025-05-07 10:20:55', '2025-05-07 10:20:55'),
(4, 2, 'Emergency Fund', 5000, 5000, '2023-06-01', '2024-04-11', 'long-term', 'completed', '2025-05-07 17:16:02', '2025-05-07 17:16:31'),
(5, 2, 'Vacation to EU', 3000, 500, '2023-07-01', '2024-05-22', 'long-term', 'in-progress', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(6, 2, 'New Laptop', 1500, 900, '2024-07-31', '2024-05-02', 'short-term', 'in-progress', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(7, 2, 'Car Down Payment', 10000, 7200, '2023-08-30', '2024-05-22', 'long-term', 'in-progress', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(8, 2, 'Wedding Expenses', 8000, 1500, '2023-09-29', '2024-09-11', 'short-term', 'completed', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(9, 2, 'Birthday Gift for Mom', 500, 300, '2023-10-29', '2024-02-17', 'short-term', 'completed', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(10, 2, 'Home Renovation', 7000, 4500, '2023-11-28', '2024-06-28', 'long-term', 'completed', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(11, 2, 'School Fees', 2500, 1200, '2023-12-28', '2024-06-02', 'long-term', 'completed', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(12, 2, 'Health Insurance', 2000, 400, '2025-01-27', '2025-05-23', 'short-term', 'in-progress', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(13, 2, 'New Smartphone', 1200, 950, '2025-02-26', '2025-11-30', 'short-term', 'in-progress', '2025-05-07 17:16:02', '2025-05-07 17:16:02'),
(14, 2, 'iWatch', 2000, 0, '2025-04-01', '2025-05-30', 'short-term', 'in-progress', '2025-05-07 17:25:46', '2025-05-07 17:25:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'test 1', 'test1@email.com', '$2y$10$ue3OGEan1dy3fUz49gqFhuUIKmkdyvYJ1sFSodg5JsCV3jiZRKJ8y', '2025-04-05 08:32:00', '2025-05-07 16:16:27'),
(2, 'Hsu', 'hsu21@gmail.com', '$2y$10$RirB2X65ZkJ7nKbXTaLg9.YevKXJajyDnksVqAy1iqW5tsoeBw766', '2025-05-06 19:45:46', '2025-05-07 18:23:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `budget_category`
--
ALTER TABLE `budget_category`
  ADD PRIMARY KEY (`budget_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `debt`
--
ALTER TABLE `debt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `debt_payments`
--
ALTER TABLE `debt_payments`
  ADD PRIMARY KEY (`debt_id`,`user_id`),
  ADD KEY `debt_id` (`debt_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `expense`
--
ALTER TABLE `expense`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_income_user` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_sent_read` (`is_sent`,`is_read`);

--
-- Indexes for table `savings`
--
ALTER TABLE `savings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_savings_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budget`
--
ALTER TABLE `budget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `debt`
--
ALTER TABLE `debt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `expense`
--
ALTER TABLE `expense`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `savings`
--
ALTER TABLE `savings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget`
--
ALTER TABLE `budget`
  ADD CONSTRAINT `budget_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `budget_category`
--
ALTER TABLE `budget_category`
  ADD CONSTRAINT `budget_category_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budget` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Constraints for table `debt_payments`
--
ALTER TABLE `debt_payments`
  ADD CONSTRAINT `debt_payments_ibfk_1` FOREIGN KEY (`debt_id`) REFERENCES `debt` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `debt_payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expense`
--
ALTER TABLE `expense`
  ADD CONSTRAINT `expense_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `expense_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Constraints for table `income`
--
ALTER TABLE `income`
  ADD CONSTRAINT `fk_income_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `savings`
--
ALTER TABLE `savings`
  ADD CONSTRAINT `fk_savings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

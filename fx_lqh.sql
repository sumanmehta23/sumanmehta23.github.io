-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 27, 2024 at 01:52 PM
-- Server version: 8.0.37-0ubuntu0.22.04.3
-- PHP Version: 8.1.2-1ubuntu2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fx_lqh`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_types`
--

CREATE TABLE `account_types` (
  `ac_index` int NOT NULL,
  `ac_category` int DEFAULT NULL,
  `ac_book_type` int DEFAULT NULL,
  `ac_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ac_min_deposit` int DEFAULT NULL,
  `ac_max_deposit` int DEFAULT NULL,
  `ac_max_leverage` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ac_lot_size` double(4,2) DEFAULT NULL,
  `ac_group` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ac_spread` double(10,1) DEFAULT NULL,
  `ac_type` int DEFAULT NULL,
  `acc_ib_cat` int DEFAULT NULL,
  `ib_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `ac_swap` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'yes',
  `is_client_group` tinyint(1) NOT NULL DEFAULT '1',
  `inquiry_status` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `display_priority` int DEFAULT NULL
) ENGINE=InnoDB AVG_ROW_LENGTH=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_types`
--

INSERT INTO `account_types` (`ac_index`, `ac_category`, `ac_book_type`, `ac_name`, `ac_min_deposit`, `ac_max_deposit`, `ac_max_leverage`, `ac_lot_size`, `ac_group`, `ac_spread`, `ac_type`, `acc_ib_cat`, `ib_enabled`, `ac_swap`, `is_client_group`, `inquiry_status`, `status`, `created_at`, `updated_at`, `display_priority`) VALUES
(45, 16, 14, 'STP LIVE', 10, NULL, '500', NULL, 'LQH MARKETS\\LM-STP LIVE-A-USD', 1.5, 9, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:05:47', '2024-10-01 22:02:40', NULL),
(46, 16, 15, 'STP LIVE', 10, NULL, '500', NULL, 'LQH MARKETS\\LM-STP LIVE-B-USD', 1.5, 9, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:06:15', '2024-10-01 22:02:54', NULL),
(47, 17, 14, 'STP BONUS', 10, NULL, '500', NULL, 'LQH MARKETS\\LM-STP BONUS-A-USD', 1.5, 9, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:07:03', '2024-10-01 22:02:07', NULL),
(48, 17, 15, 'STP BONUS', 10, NULL, '500', NULL, 'LQH MARKETS\\LM-STP BONUS-B-USD', 1.5, 9, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:07:39', '2024-10-01 22:02:24', NULL),
(49, 18, 14, 'ECN LIVE', 500, NULL, '500', NULL, 'LQH MARKETS\\LM-ECN LIVE-A-USD', 0.0, 9, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:10:34', '2024-10-01 22:00:49', NULL),
(50, 18, 15, 'ECN LIVE', 500, NULL, '500', NULL, 'LQH MARKETS\\LM-ECN LIVE-B-USD', 0.0, 9, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:10:56', '2024-10-01 22:01:03', NULL),
(51, 19, 14, 'ECN BONUS', 500, NULL, '500', NULL, 'LQH MARKETS\\LM-ECN BONUS-A-USD', 0.0, 9, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:11:58', '2024-10-01 22:00:21', NULL),
(52, 19, 15, 'ECN BONUS', 500, NULL, '500', NULL, 'LQH MARKETS\\LM-ECN BONUS-B-USD', 0.0, 9, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:12:39', '2024-10-01 22:00:36', NULL),
(53, 13, 14, 'INSTITUTIONAL', 100000, NULL, '100', NULL, 'LQH MARKETS\\LM-INSTITUTIONAL-A-USD', 0.0, 9, NULL, 1, 'no', 0, 0, 1, '2024-09-04 20:13:57', '2024-10-01 22:08:32', NULL),
(54, 13, 15, 'INSTITUTIONAL', 100000, NULL, '100', NULL, 'LQH MARKETS\\LM-INSTITUTIONAL-B-USD', 0.0, 9, NULL, 1, 'yes', 1, 1, 1, '2024-09-04 20:14:24', '2024-10-04 15:40:55', 50),
(55, 11, 15, 'STP', 10, NULL, '500', NULL, 'demo\\LQH MARKETS\\LM-STP-B-USD', 1.5, 10, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:15:07', '2024-10-01 22:01:46', NULL),
(56, 12, 15, 'ECN', 500, NULL, '500', NULL, 'demo\\LQH MARKETS\\LM-ECN-B-USD', 0.0, 10, NULL, 1, 'yes', 0, 0, 0, '2024-09-04 20:15:28', '2024-10-01 21:59:58', NULL),
(57, 13, 15, 'INSTITUTIONAL', 100000, NULL, '100', NULL, 'demo\\LQH MARKETS\\LM-INSTITUTIONAL-B-USD', 0.0, 10, NULL, 1, 'yes', 1, 0, 1, '2024-09-04 20:16:01', '2024-10-04 15:41:20', 45),
(58, 20, 15, 'Standard', 10, NULL, '500', NULL, 'demo\\LQH MARKETS\\LM-STANDARD-B-USD', 1.6, 10, NULL, 0, 'no', 1, 0, 1, '2024-10-01 22:04:08', '2024-10-04 15:41:08', 95),
(59, 20, 14, 'Standard', 10, NULL, '500', NULL, 'LQH MARKETS\\LM-STANDARD-A-USD', 1.6, 9, NULL, 1, 'no', 0, 0, 1, '2024-10-01 22:04:42', '2024-10-01 22:07:38', NULL),
(60, 20, 15, 'Standard', 10, NULL, '500', NULL, 'LQH MARKETS\\LM-STANDARD-B-USD', 1.6, 9, NULL, 1, '', 1, 0, 1, '2024-10-01 22:06:46', '2024-10-04 15:40:39', 100),
(61, 21, 15, 'PRO', 1000, NULL, '500', NULL, 'demo\\LQH MARKETS\\LM-PRO-B-USD', 1.0, 10, NULL, 0, '', 1, 0, 1, '2024-10-01 22:09:10', '2024-10-04 15:41:12', 65),
(62, 21, 14, 'PRO', 1000, NULL, '500', NULL, 'LQH MARKETS\\LM-PRO-A-USD', 1.0, 9, NULL, 1, 'no', 0, 0, 1, '2024-10-01 22:09:48', '2024-10-01 22:54:51', NULL),
(63, 21, 15, 'PRO', 1000, NULL, '100', NULL, 'LQH MARKETS\\LM-PRO-B-USD', 1.0, 9, NULL, 1, '', 1, 0, 1, '2024-10-01 22:11:31', '2024-10-04 15:40:49', 70);

-- --------------------------------------------------------

--
-- Table structure for table `activation`
--

CREATE TABLE `activation` (
  `id` int UNSIGNED NOT NULL,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activation_key` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mobile` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aspnetusers`
--

CREATE TABLE `aspnetusers` (
  `id` int NOT NULL,
  `uid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noEmail',
  `email_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_code` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `number_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `lockout_end_date` datetime DEFAULT NULL,
  `lockout_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `access_count_failed` int NOT NULL DEFAULT '0',
  `username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fullname` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `byPartner` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `status` int DEFAULT '0',
  `country` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dial_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Isreferal` tinyint(1) NOT NULL DEFAULT '0',
  `referalId` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zipcode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `aboutme` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `imgName` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `education` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `industry` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `financial_industry` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `forex_exp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `monthly_transaction` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `investment_plan` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `funds_source` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `investment_purpose` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_value` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `annual_income` int DEFAULT NULL,
  `polotically_person` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankruptcy` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `usa_resident` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `usa_tax` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dob` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emailToken` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'english',
  `email_token_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `profile_image` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referral` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mail_otp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cfd` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `other` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_front` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_back` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_detail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_holder_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_account_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `IFSC_Code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `swift_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_verify` int NOT NULL DEFAULT '0',
  `client_status` int NOT NULL DEFAULT '0',
  `wallet_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `bank_status` int NOT NULL DEFAULT '0',
  `personal_status` int DEFAULT '0',
  `employemnet_status` int DEFAULT '0',
  `trading_status` int DEFAULT '0',
  `ib1` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'noIB',
  `ib2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib5` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib6` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib7` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib8` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib9` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib10` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib11` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib12` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib13` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib14` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib15` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wallet_requested` int DEFAULT NULL,
  `wallet_enabled` int DEFAULT '1',
  `wallet_requested_at` datetime DEFAULT NULL,
  `wallet_approved_at` datetime DEFAULT NULL
) ENGINE=MyISAM AVG_ROW_LENGTH=216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aspnetusers`
--

INSERT INTO `aspnetusers` (`id`, `uid`, `email`, `email_confirmed`, `password`, `country_code`, `number`, `number_confirmed`, `two_factor_enabled`, `lockout_end_date`, `lockout_enabled`, `access_count_failed`, `username`, `fullname`, `byPartner`, `date`, `status`, `country`, `dial_code`, `Isreferal`, `referalId`, `zipcode`, `address`, `aboutme`, `imgName`, `education`, `industry`, `financial_industry`, `forex_exp`, `monthly_transaction`, `investment_plan`, `funds_source`, `investment_purpose`, `total_value`, `annual_income`, `polotically_person`, `bankruptcy`, `usa_resident`, `usa_tax`, `dob`, `emailToken`, `state`, `city`, `lang`, `email_token_time`, `profile_image`, `gender`, `referral`, `mail_otp`, `employee_status`, `cfd`, `other`, `kyc_type`, `kyc_front`, `kyc_back`, `bank_detail`, `account_holder_name`, `bank_name`, `bank_account_no`, `IFSC_Code`, `swift_code`, `kyc_verify`, `client_status`, `wallet_address`, `reg_date`, `bank_status`, `personal_status`, `employemnet_status`, `trading_status`, `ib1`, `ib2`, `ib3`, `ib4`, `ib5`, `ib6`, `ib7`, `ib8`, `ib9`, `ib10`, `ib11`, `ib12`, `ib13`, `ib14`, `ib15`, `created_at`, `updated_at`, `wallet_requested`, `wallet_enabled`, `wallet_requested_at`, `wallet_approved_at`) VALUES
(109, NULL, 'tech+2@lqhmarkets.com', 1, 'tech+2@lqhmarkets.com', NULL, '+931234567890', 0, 0, NULL, 0, 0, 'tech+2@lqhmarkets.com', 'Tech +2', 0, NULL, 1, 'Algeria', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '22f9a56e2ea806cc3365a5659bdc5dcd', NULL, NULL, 'english', '2024-09-09 02:52:06', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, NULL, '2024-09-09 02:40:47', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-09 02:52:06', '2024-09-09 04:40:47', NULL, 1, NULL, NULL),
(108, NULL, 'Contact@bridgingfx.net', 1, 'test1234', NULL, '+971585431107', 0, 0, NULL, 0, 0, 'Contact@bridgingfx.net', 'Bridging FX', 0, NULL, 1, 'United Arab Emirates', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'f01bde36f4df79a7ae9e47a1afbac463', NULL, NULL, 'english', '2024-09-08 17:50:00', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-08 15:51:18', 0, 0, 0, 0, 'syedmohamedrafi@gmail.com', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-09-08 17:50:00', '2024-09-08 17:51:18', NULL, 1, NULL, NULL),
(107, NULL, 'muthuvenkatesh808@gmail.com', 1, '5700d$A4', NULL, '+971529938041', 0, 0, NULL, 0, 0, 'muthuvenkatesh808@gmail.com', 'Muthu Venkatesh', 0, NULL, 1, 'United Arab Emirates', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9790921726d79adcb32f0e119846aef1', NULL, NULL, 'english', '2024-09-07 16:51:10', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-09-11 08:29:26', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-07 16:51:10', '2024-09-11 10:29:26', NULL, 1, NULL, NULL),
(106, NULL, 'fajife5699@ploncy.com', 1, 'abcd', NULL, '+85663', 0, 0, NULL, 0, 0, 'fajife5699@ploncy.com', 'Burton Reese', 0, NULL, 1, 'Macedonia', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2f9d08dba4657653b8f6c7de0770da60', NULL, NULL, 'english', '2024-09-07 13:24:15', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-07 11:26:16', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-07 13:24:15', '2024-09-07 13:26:16', NULL, NULL, NULL, NULL),
(103, NULL, 'mediaslush@protonmail.com', 1, 'mediaslush@protonmail.com', NULL, '+933301222121', 0, 0, NULL, 0, 0, 'mediaslush@protonmail.com', 'Media Slush', 0, NULL, 1, 'Afghanistan', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'b147f68b4f6f68f9ff442afd22da7bd9', NULL, NULL, 'english', '2024-09-06 19:11:02', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-07 23:38:03', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-06 19:11:02', '2024-09-08 01:38:03', NULL, 1, NULL, NULL),
(104, NULL, 'jagadishkumar20011@gmail.com', 0, 'Jagu@0000', NULL, '+917878495245', 0, 0, NULL, 0, 0, 'jagadishkumar20011@gmail.com', 'Jagdish Kumar', 0, NULL, 0, 'India', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'cab6cfe9021f904bea14ce813091785b', NULL, NULL, 'english', '2024-09-07 08:45:09', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-07 06:45:40', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-07 08:45:09', '2024-09-07 08:45:40', NULL, NULL, NULL, NULL),
(105, NULL, 'wocevan815@rogtat.com', 1, 'abcd', NULL, '+59554', 0, 0, NULL, 0, 0, 'wocevan815@rogtat.com', 'Mary Vinson', 0, NULL, 1, 'Saint Martin', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a2f3267f1a50e26bc823c7c011865720', NULL, NULL, 'english', '2024-09-07 13:16:41', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-07 11:17:13', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-07 13:16:41', '2024-09-07 13:17:13', NULL, NULL, NULL, NULL),
(102, NULL, 'hegic47164@rogtat.com', 1, 'abcd', NULL, '+35812345', 0, 0, NULL, 0, 0, 'hegic47164@rogtat.com', 'Lucas Wiggins', 0, NULL, 1, 'Armenia', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '573367604fd973ee11cc9a8253bb9a15', NULL, NULL, 'english', '2024-09-06 17:55:50', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-06 15:56:14', 0, 0, 0, 0, 'rugmaramanathan@gmail.com', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-09-06 17:55:50', '2024-09-06 17:56:14', NULL, NULL, NULL, NULL),
(101, NULL, 'rugmaramanathan@gmail.com', 1, 'abcd', NULL, '+3551234567', 0, 0, NULL, 0, 0, 'rugmaramanathan@gmail.com', 'Rugma R', 0, NULL, 1, 'Armenia', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'c75a0325988b731df04a27d9380aa278', NULL, NULL, 'english', '2024-09-06 17:47:10', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-06 15:47:52', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-06 17:47:10', '2024-09-06 17:47:52', NULL, NULL, NULL, NULL),
(100, NULL, 'rugmar91@gmail.com', 1, 'abcd', NULL, '+35812345678', 0, 0, NULL, 0, 0, 'rugmar91@gmail.com', 'Rugma Ramanathan', 0, NULL, 1, 'Argentina', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a8223a453faf9e183b2d01c71ea3242d', NULL, NULL, 'english', '2024-09-06 15:22:55', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-09-10 14:49:49', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-06 15:22:55', '2024-09-10 16:49:49', NULL, 1, NULL, NULL),
(99, NULL, 'darkfintechnologies@gmail.com', 0, 'test1234', NULL, '07441428881', 0, 0, NULL, 0, 0, 'darkfintechnologies@gmail.com', 'Dark Fintech', 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ce5f08864dd78ecc0be7b62eda4125b0', NULL, NULL, 'english', '2024-09-05 08:03:01', NULL, NULL, 'Google Search', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-05 06:03:01', 0, 0, 0, 0, 'syedmohamedrafi@gmail.com', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-09-05 08:03:01', '2024-09-05 08:03:01', NULL, NULL, NULL, NULL),
(95, NULL, 'megastand@protonmail.com', 1, 'megastand@protonmail.com', NULL, '+91123456789', 0, 0, NULL, 0, 0, 'megastand@protonmail.com', 'Mega Stand', 0, NULL, 1, 'India', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '24c0167d9bfd698ceb5723ab14e0a1c0', NULL, NULL, 'english', '2024-09-04 05:20:09', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-04 06:06:00', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-04 05:20:09', '2024-09-04 08:06:00', NULL, 1, NULL, NULL),
(97, NULL, 'yourtradebro@gmail.com', 0, 'test1234', NULL, '000000000', 0, 0, NULL, 0, 0, 'yourtradebro@gmail.com', 'Dilavar', 0, NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'cfbf36ac1ae42db52bbc47714bcc69b3', NULL, NULL, 'english', '2024-09-04 23:02:28', NULL, NULL, 'Google Search', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-04 21:02:28', 0, 0, 0, 0, 'syedmohamedrafi@gmail.com', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-09-04 23:02:28', '2024-09-04 23:02:28', NULL, NULL, NULL, NULL),
(98, NULL, 'jalelwabou@gmail.com', 1, 'Lala2017!', NULL, '+17805200055', 0, 0, NULL, 0, 0, 'jalelwabou@gmail.com', 'Jalel Abougouche', 0, NULL, 1, 'Canada', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3c83bccc60a32e9fa3cb1fbbde77479e', NULL, NULL, 'english', '2024-09-04 23:09:00', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-10-01 13:35:31', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-04 23:09:00', '2024-10-01 15:35:31', NULL, 1, NULL, NULL),
(96, NULL, 'syedmohamedrafi@gmail.com', 1, 'test1234', NULL, '+9710585301312', 0, 0, NULL, 0, 0, 'syedmohamedrafi@gmail.com', 'Syed Mohamed Rafi Babu', 0, NULL, 1, 'United Arab Emirates', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5f49836488108b7f4e4bca1ed4a5dc42', NULL, NULL, 'english', '2024-09-04 20:17:38', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-10-01 20:55:31', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-04 20:17:38', '2024-10-01 22:55:31', NULL, 1, NULL, NULL),
(110, NULL, 'testfx111@gmail.com', 0, 'Forex1234', NULL, '+917056436453', 0, 0, NULL, 0, 0, 'testfx111@gmail.com', 'Testfx', 0, NULL, 0, 'India', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '326ea0a71497f823778ea24b051dea4c', NULL, NULL, 'english', '2024-09-09 03:10:17', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-09 01:10:17', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-09 03:10:17', '2024-09-09 03:10:17', NULL, 1, NULL, NULL),
(111, NULL, 'testfx333@yopmail.com', 1, 'Forex1234', NULL, '+917564649346', 0, 0, NULL, 0, 0, 'testfx333@yopmail.com', 'Testfx333@yopmail.com', 0, NULL, 1, 'India', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'f6feee14a1d3188e612de60e5077f3ec', NULL, NULL, 'english', '2024-09-09 03:12:30', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-09 01:13:01', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-09 03:12:30', '2024-09-09 03:13:01', NULL, 1, NULL, NULL),
(112, NULL, 'mt4mt5solutions@gmail.com', 1, '1234567890', NULL, '+35512121', 0, 0, NULL, 0, 0, 'mt4mt5solutions@gmail.com', 'Mt4 ', 0, NULL, 1, 'Albania', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '295a63d7e29c0d1af281dc5c71453324', NULL, NULL, 'english', '2024-09-09 03:16:45', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-09 01:17:15', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-09 03:16:45', '2024-09-09 03:17:15', NULL, 1, NULL, NULL),
(113, NULL, 'getscare@gmail.com', 1, 'getscare@g', NULL, '+919995689898', 0, 0, NULL, 0, 0, 'getscare@gmail.com', 'BAIJU', 0, NULL, 1, 'India', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a8fb9d383b0980cbfda4c98d06d811e7', NULL, NULL, 'english', '2024-09-09 04:27:47', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-09 02:28:53', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-09 04:27:47', '2024-09-09 04:28:53', NULL, 1, NULL, NULL),
(114, NULL, 'singh@serverfront.net', 1, 'ArbiCmL76Z6g$vT', NULL, '+911798379811', 0, 0, NULL, 0, 0, 'singh@serverfront.net', 'Digvijay Singh', 0, NULL, 1, 'India', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10f3785d1003ef00f6732a09b326a3f3', NULL, NULL, 'english', '2024-09-09 06:57:31', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-09 04:58:18', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-09 06:57:31', '2024-09-09 06:58:18', NULL, 1, NULL, NULL),
(115, NULL, 'tech+3@lqhmarkets.com', 1, 'tech+3@lqhmarkets.com', NULL, '+931234567890', 0, 0, NULL, 0, 0, 'tech+3@lqhmarkets.com', 'Tech 3', 0, NULL, 1, 'Afghanistan', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'd3e184cceb6728392e959015e94c2d8e', NULL, NULL, 'english', '2024-09-09 18:47:04', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-09 16:47:22', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-09 18:47:04', '2024-09-09 18:47:22', NULL, 1, NULL, NULL),
(116, NULL, 'gurkiran121@gmail.com', 1, '0629487&GSingh', NULL, '+93123456677889', 0, 0, NULL, 0, 0, 'gurkiran121@gmail.com', 'Gurkiran Singh', 0, NULL, 1, 'India', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '181fb843f22d43c58fbb8251434b82fa', NULL, NULL, 'english', '2024-09-09 20:44:33', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, NULL, '2024-09-09 18:51:14', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-09 20:44:33', '2024-09-09 20:51:14', NULL, 1, NULL, NULL),
(117, NULL, 'furnwest@gmail.com', 1, 'furnwest1994@', NULL, '+639096306820', 0, 0, NULL, 0, 0, 'furnwest@gmail.com', 'Dulce Mayon', 0, NULL, 1, 'Philippines', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5e9cb032098a3c8b506ff258570a5d5e', NULL, NULL, 'english', '2024-09-10 11:38:27', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-09-11 12:08:22', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-10 11:38:27', '2024-09-11 14:08:22', NULL, 1, NULL, NULL),
(118, NULL, 'priya234fx@gmail.com', 1, 'Priya@1234$', NULL, '+971456723456', 0, 0, NULL, 0, 0, 'priya234fx@gmail.com', 'Priya Singh', 0, NULL, 1, 'India', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'cfa96dab60021a1c3836ddeb6dd4aaf7', NULL, NULL, 'english', '2024-09-10 13:42:50', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-10 15:08:03', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-10 13:42:50', '2024-09-10 17:08:03', NULL, 1, NULL, NULL),
(119, NULL, 'dawood@lqhmarkets.com', 1, '1234', NULL, '+17805200055', 0, 0, NULL, 0, 0, 'dawood@lqhmarkets.com', 'Jay Abou', 0, NULL, 1, 'Canada', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a9c669128b2b5340a298338da24aa238', NULL, NULL, 'english', '2024-09-10 18:15:55', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-09-11 08:51:31', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-10 18:15:55', '2024-09-11 10:51:31', NULL, 1, NULL, NULL),
(120, NULL, 'operations@nextstepfunded.com', 1, '1234', NULL, '+17805200055', 0, 0, NULL, 0, 0, 'operations@nextstepfunded.com', 'Jay Abou', 0, NULL, 1, 'Canada', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0eca28ec801002f8e98a882b15ea2b61', NULL, NULL, 'english', '2024-09-11 10:54:05', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-09-11 08:57:01', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-11 10:54:05', '2024-09-11 10:57:01', NULL, 1, NULL, NULL),
(121, NULL, 'warisahmedbarak@gmail.com', 1, 'Warisjan1', NULL, '+9370300960', 0, 0, NULL, 0, 0, 'warisahmedbarak@gmail.com', 'Waris Ahmad Barak ', 0, NULL, 1, 'Afghanistan', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'e22a42f02bad5a16bffcd7237fe5764f', NULL, NULL, 'english', '2024-09-12 04:03:21', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-09-12 02:05:14', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-12 04:03:21', '2024-09-12 04:05:14', NULL, 1, NULL, NULL),
(122, NULL, 'nendir771@gmail.com', 1, 'Ramdani71@', NULL, '+6288218365315', 0, 0, NULL, 0, 0, 'nendir771@gmail.com', 'NENDI RAMDANI', 0, NULL, 1, 'Indonesia', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9e8bf2cb12e14270854e929479b8dc54', NULL, NULL, 'english', '2024-09-15 08:05:44', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-15 06:08:35', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-15 08:05:44', '2024-09-15 08:08:35', NULL, 1, NULL, NULL),
(123, NULL, 'lqhmarkets@gmail.com', 1, 'lqhmarkets@gmail.com', NULL, '+971123456890', 0, 0, NULL, 0, 0, 'lqhmarkets@gmail.com', 'LQH Markets', 0, NULL, 1, 'United Arab Emirates', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3f9dc448abfe9b48cdaf11400e628366', NULL, NULL, 'english', '2024-09-22 06:59:09', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-09-29 01:29:04', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-22 06:59:09', '2024-09-29 03:29:04', NULL, 1, NULL, NULL),
(124, NULL, 'garahaltopai@gmail.com', 1, 'Garahy77', NULL, '+9670772869186', 0, 0, NULL, 0, 0, 'garahaltopai@gmail.com', 'Younes Saif Alshoafi', 0, NULL, 1, 'Yemen', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a4b82720fd1d8a73f2c09d3ceb05006d', NULL, NULL, 'english', '2024-09-23 21:45:54', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-23 19:47:32', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-23 21:45:54', '2024-09-23 21:47:32', NULL, 1, NULL, NULL),
(125, NULL, 'eee666@rambler.ru', 0, 'Dppp222zzztx', NULL, '+126889822379162', 0, 0, NULL, 0, 0, 'eee666@rambler.ru', 'WilliamMor', 0, NULL, 0, 'Antigua and Barbuda', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'c20b0eebc3b0f8ea58c552d2a35acbf9', NULL, NULL, 'english', '2024-09-25 02:40:56', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-25 00:40:56', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-25 02:40:56', '2024-09-25 02:40:56', NULL, 1, NULL, NULL),
(126, NULL, 'dmtest005@rambler.ru', 0, 'Dppp222zzztx', NULL, '+96887573255459', 0, 0, NULL, 0, 0, 'dmtest005@rambler.ru', 'gorridaMor', 0, NULL, 0, 'Tajikistan', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '237a4dcafb0e45fd0f260f4586bc93e2', NULL, NULL, 'english', '2024-09-26 10:23:39', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-26 08:23:39', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-26 10:23:39', '2024-09-26 10:23:39', NULL, 1, NULL, NULL),
(127, NULL, 'l2test004@rambler.ru', 0, 'Dppp222zzztx', NULL, '+6181633859857', 0, 0, NULL, 0, 0, 'l2test004@rambler.ru', 'fernnostiMor', 0, NULL, 0, 'Sudan', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ea79b22970dacc40301717cf1af63612', NULL, NULL, 'english', '2024-09-26 22:08:12', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-26 20:08:12', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-26 22:08:12', '2024-09-26 22:08:12', NULL, 1, NULL, NULL),
(128, NULL, 'mshanafxhealer@gmail.com', 1, 'Solomon@1', NULL, '+270763449021', 0, 0, NULL, 0, 0, 'mshanafxhealer@gmail.com', 'Thokozani Shabalala', 0, NULL, 1, 'South Africa', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1a0b40de265bfff0db6b31311ae606ee', NULL, NULL, 'english', '2024-09-29 07:39:49', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-09-29 05:40:26', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-29 07:39:49', '2024-09-29 07:40:26', NULL, 1, NULL, NULL),
(129, NULL, 'jwadc8@gmail.com', 0, '1234', NULL, '+971569407562', 0, 0, NULL, 0, 0, 'jwadc8@gmail.com', 'jake', 0, NULL, 0, 'Canada', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9661c24a613370f185199e4ed7db89e9', NULL, NULL, 'english', '2024-10-01 15:08:51', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2024-10-01 13:08:51', 0, 0, 0, 0, 'jalelwabou@gmail.com', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-10-01 15:08:51', '2024-10-01 15:08:51', NULL, 1, NULL, NULL),
(130, NULL, 'abougouche22@gmail.com', 1, '1234', NULL, '+9323434523434', 0, 0, NULL, 0, 0, 'abougouche22@gmail.com', 'jake', 0, NULL, 1, 'Canada', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ebeae19b831356843fcb301b5956a1ab', NULL, NULL, 'english', '2024-10-01 15:11:51', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-10-01 13:12:35', 0, 0, 0, 0, 'jalelwabou@gmail.com', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-10-01 15:11:51', '2024-10-01 15:12:35', NULL, 1, NULL, NULL),
(131, NULL, 'contact@evsconnect.net', 1, 'PaC27@HyE!', NULL, '+17802228674', 0, 0, NULL, 0, 0, 'contact@evsconnect.net', 'Samuel Joseph Katallah', 0, NULL, 1, 'Canada', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5e8fdafd926061bfaa1ec900c1a682f3', NULL, NULL, 'english', '2024-10-04 11:25:00', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-10-07 19:31:44', 0, 0, 0, 0, 'jalelwabou@gmail.com', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-10-04 11:25:00', '2024-10-07 21:31:44', NULL, 1, NULL, NULL),
(132, NULL, 'mayongel94+LQH@gmail.com', 1, 'mayongel94+LQH', NULL, '+639096306820', 0, 0, NULL, 0, 0, 'mayongel94+LQH@gmail.com', 'Angel mayon', 0, NULL, 1, 'Philippines', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a9f6f0444d9b9df1cee2bcd8a4f6b992', NULL, NULL, 'english', '2024-10-14 00:36:36', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '2024-10-13 22:46:10', 0, 0, 0, 0, 'noIB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 00:36:36', '2024-10-14 00:46:10', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aspnetusers_log`
--

CREATE TABLE `aspnetusers_log` (
  `id` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `admin_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aspnetusers_log`
--

INSERT INTO `aspnetusers_log` (`id`, `email`, `admin_email`, `type`, `value`, `added_at`) VALUES
(1, 'abougouche22@gmail.com', 'jalel@lqhmarkets.com', 'status', '1', '2024-10-01 15:12:35'),
(2, 'abougouche22@gmail.com', 'jalel@lqhmarkets.com', 'email_confirmed', '1', '2024-10-01 15:12:35'),
(3, 'abougouche22@gmail.com', 'jalel@lqhmarkets.com', 'kyc_verify', '1', '2024-10-01 15:12:35'),
(4, 'jalelwabou@gmail.com', 'jalel@lqhmarkets.com', 'kyc_verify', '1', '2024-10-01 15:35:31'),
(5, 'syedmohamedrafi@gmail.com', 'admin@alphabullforex.com', 'kyc_verify', '1', '2024-10-01 22:55:31');

-- --------------------------------------------------------

--
-- Table structure for table `available_payment`
--

CREATE TABLE `available_payment` (
  `id` int NOT NULL,
  `payment_mode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_holdername` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_detail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_codename1` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_codename2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_ifsc_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_iban_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `agent_location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `agent_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `register_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `api_key` text COLLATE utf8mb4_general_ci,
  `secret_key` text COLLATE utf8mb4_general_ci,
  `additional_key` text COLLATE utf8mb4_general_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bankdetails`
--

CREATE TABLE `bankdetails` (
  `id` int NOT NULL,
  `bankName` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankDetails` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `accountNumber` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `swiftCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ifscCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accountName` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bonusdeposit`
--

CREATE TABLE `bonusdeposit` (
  `id` bigint NOT NULL,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noCode',
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `tradeAccId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uid` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bonuses`
--

CREATE TABLE `bonuses` (
  `bonus_id` int NOT NULL,
  `bonus_name` varchar(255) NOT NULL,
  `bonus_code` varchar(255) NOT NULL,
  `bonus_desc` text NOT NULL,
  `bonus_starts_at` datetime NOT NULL,
  `bonus_ends_at` datetime NOT NULL,
  `bonus_accessable` enum('First Deposit','Welcome Bonus') NOT NULL DEFAULT 'First Deposit',
  `bonus_shows_on` enum('all','groups','users') NOT NULL DEFAULT 'all',
  `bonus_show_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `bonus_type` enum('percentage','flat') NOT NULL DEFAULT 'percentage',
  `bonus_value` decimal(10,2) NOT NULL,
  `bonus_limit` int NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `bonus_updated_by` varchar(300) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `bonus_trans`
--

CREATE TABLE `bonus_trans` (
  `id` int NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bonus_amount` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bonus_currency` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `bonus_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Entry',
  `bonus_code_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bonus_code_desc` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bonus_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int NOT NULL DEFAULT '1',
  `adminRemark` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Js_Admin_Remark_Date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorylist`
--

CREATE TABLE `categorylist` (
  `categoryIndex` int NOT NULL,
  `date` datetime NOT NULL,
  `categoryFor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `categoryName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claimbonus`
--

CREATE TABLE `claimbonus` (
  `indexNo` bigint NOT NULL,
  `uniqueId` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bonusRdmType` int NOT NULL DEFAULT '0',
  `bonusType` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `userId` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `refUserId` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tradingAccId` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `date` datetime DEFAULT NULL,
  `typeAlias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `claimedOn` datetime DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `statusCode` tinyint(1) NOT NULL DEFAULT '0',
  `expDate` datetime DEFAULT NULL,
  `expState` int NOT NULL DEFAULT '0',
  `bonusState` int NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clientbankdetails`
--

CREATE TABLE `clientbankdetails` (
  `id` int NOT NULL,
  `bankName` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankDetails` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `accountNumber` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `swift_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ClientName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `processedOn` datetime DEFAULT NULL,
  `processedBy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `userId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM AVG_ROW_LENGTH=220 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clientbankdetails`
--

INSERT INTO `clientbankdetails` (`id`, `bankName`, `branch`, `bankDetails`, `accountNumber`, `status`, `code`, `swift_code`, `country`, `date`, `email`, `ClientName`, `address`, `processedOn`, `processedBy`, `document`, `userId`, `comment`) VALUES
(1, 'Olympia Rush', NULL, NULL, '14', 'success', 'Voluptas quasi sint ', 'Nostrum aliqua Null', NULL, NULL, NULL, 'Blossom Frazier', NULL, NULL, NULL, NULL, 'rugmar91@gmail.com', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_wallets`
--

CREATE TABLE `client_wallets` (
  `client_wallet_id` int NOT NULL,
  `wallet_name` varchar(255) NOT NULL,
  `wallet_currency` varchar(50) NOT NULL,
  `wallet_network` varchar(500) NOT NULL,
  `wallet_address` text NOT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `user_id` varchar(255) DEFAULT NULL,
  `admin_action_by` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `client_wallets`
--

INSERT INTO `client_wallets` (`client_wallet_id`, `wallet_name`, `wallet_currency`, `wallet_network`, `wallet_address`, `created_by`, `status`, `user_id`, `admin_action_by`, `created_at`, `updated_at`) VALUES
(1, 'Binance', 'USDT', 'USDT-TRX', '5drfyvubhinuh8yg7f5r46f7gy8t75r4efgh', 'syedmohamedrafi@gmail.com', 0, 'syedmohamedrafi@gmail.com', NULL, '2024-09-18 23:39:52', '2024-09-22 22:00:15'),
(2, 'Binance', 'USDT', 'USDT-TRX', 'dfdgbrhntjmyk,ulkhyjgfrdgrhtjhr', 'syedmohamedrafi@gmail.com', 1, 'syedmohamedrafi@gmail.com', NULL, '2024-09-19 00:13:48', '2024-09-22 22:00:12'),
(3, 'Binance', 'USDT', 'USDT-TRX', '5drfyvubhinuh8yg7f5r46f7gy8t75r4efgh', 'muthuvenkatesh808@gmail.com', 1, 'muthuvenkatesh808@gmail.com', NULL, '2024-09-19 10:18:22', '2024-09-22 22:00:09'),
(4, 'Trust', 'USDT', 'USDT-TRX', 'TTAQXDQXnVz3qambZwcVe79wu7hbgGJkXT', 'jalelwabou@gmail.com', 1, 'jalelwabou@gmail.com', NULL, '2024-09-20 14:30:15', '2024-09-22 22:00:07'),
(5, 'Test', 'USDT', 'USDT-TRX', '1234567890', 'lqhmarkets@gmail.com', 1, 'lqhmarkets@gmail.com', NULL, '2024-09-22 20:31:46', '2024-09-22 22:00:01'),
(6, 'Erc20Test', 'USDT', 'USDT-TRX', '0xECfE937f86539BE3bfB776076FC80a427b96b080', 'lqhmarkets@gmail.com', 1, 'lqhmarkets@gmail.com', NULL, '2024-09-22 20:34:08', '2024-09-22 21:59:59'),
(7, 'TrustWalletTRC20', 'USDT', 'USDT-TRX', 'TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq', 'lqhmarkets@gmail.com', 1, 'lqhmarkets@gmail.com', NULL, '2024-09-22 20:39:59', '2024-09-22 21:53:35'),
(8, 'Dulce withdraw', 'USDT', 'USDT-TRX', 'TMtg4KawGbyPwpoK23Pudet467fpMRaXVn', 'furnwest@gmail.com', 1, 'furnwest@gmail.com', NULL, '2024-09-24 03:36:54', '2024-09-24 03:36:54'),
(9, 'xo', 'USDT', 'USDT-TRX', 'TT3cz59WJ687gewPqghwNmu8hxojkPCBaF', 'lqhmarkets@gmail.com', 1, 'lqhmarkets@gmail.com', NULL, '2024-09-26 04:22:23', '2024-09-26 04:22:23'),
(10, 'Griffith Kirby', 'USDT', 'USDT-TRX', 'Tempora eu exercitat', 'rugmar91@gmail.com', 1, 'rugmar91@gmail.com', NULL, '2024-09-27 08:34:21', '2024-10-09 13:56:48'),
(11, 'Lilah Albert', 'USDT', 'USDT-TRX', 'Deserunt dolorem et ', 'rugmar91@gmail.com', 0, 'rugmar91@gmail.com', NULL, '2024-10-09 13:38:36', '2024-10-09 13:57:06'),
(12, 'NowPayment', 'USDT', 'USDT-TRX', 'tytcuyviohiuyftrd46dyu78o9g7ftdytj', 'muthuvenkatesh808@gmail.com', 1, 'muthuvenkatesh808@gmail.com', NULL, '2024-10-17 08:27:09', '2024-10-17 08:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `country_id` int NOT NULL,
  `country_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `country_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `country_alpha` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`country_id`, `country_name`, `country_code`, `country_alpha`) VALUES
(1, 'Afghanistan', '93', 'AF'),
(2, 'Aland Islands', '358', 'AX'),
(3, 'Albania', '355', 'AL'),
(4, 'Algeria', '213', 'DZ'),
(5, 'AmericanSamoa', '1684', 'AS'),
(6, 'Andorra', '376', 'AD'),
(7, 'Angola', '244', 'AO'),
(8, 'Anguilla', '1264', 'AI'),
(9, 'Antarctica', '672', 'AQ'),
(10, 'Antigua and Barbuda', '1268', 'AG'),
(11, 'Argentina', '54', 'AR'),
(12, 'Armenia', '374', 'AM'),
(13, 'Aruba', '297', 'AW'),
(14, 'Australia', '61', 'AU'),
(15, 'Austria', '43', 'AT'),
(16, 'Azerbaijan', '994', 'AZ'),
(17, 'Bahamas', '1242', 'BS'),
(18, 'Bahrain', '973', 'BH'),
(19, 'Bangladesh', '880', 'BD'),
(20, 'Barbados', '1246', 'BB'),
(21, 'Belarus', '375', 'BY'),
(22, 'Belgium', '32', 'BE'),
(23, 'Belize', '501', 'BZ'),
(24, 'Benin', '229', 'BJ'),
(25, 'Bermuda', '1441', 'BM'),
(26, 'Bhutan', '975', 'BT'),
(27, 'Bolivia, Plurinational State of', '591', 'BO'),
(28, 'Bosnia and Herzegovina', '387', 'BA'),
(29, 'Botswana', '267', 'BW'),
(30, 'Brazil', '55', 'BR'),
(31, 'British Indian Ocean Territory', '246', 'IO'),
(32, 'Brunei Darussalam', '673', 'BN'),
(33, 'Bulgaria', '359', 'BG'),
(34, 'Burkina Faso', '226', 'BF'),
(35, 'Burundi', '257', 'BI'),
(36, 'Cambodia', '855', 'KH'),
(37, 'Cameroon', '237', 'CM'),
(38, 'Canada', '1', 'CA'),
(39, 'Cape Verde', '238', 'CV'),
(40, 'Cayman Islands', '345', 'KY'),
(41, 'Central African Republic', '236', 'CF'),
(42, 'Chad', '235', 'TD'),
(43, 'Chile', '56', 'CL'),
(44, 'China', '86', 'CN'),
(45, 'Christmas Island', '61', 'CX'),
(46, 'Cocos (Keeling) Islands', '61', 'CC'),
(47, 'Colombia', '57', 'CO'),
(48, 'Comoros', '269', 'KM'),
(49, 'Congo', '242', 'CG'),
(50, 'Congo, The Democratic Republic of the Congo', '243', 'CD'),
(51, 'Cook Islands', '682', 'CK'),
(52, 'Costa Rica', '506', 'CR'),
(53, 'Cote d\'Ivoire', '225', 'CI'),
(54, 'Croatia', '385', 'HR'),
(55, 'Cuba', '53', 'CU'),
(56, 'Cyprus', '357', 'CY'),
(57, 'Czech Republic', '420', 'CZ'),
(58, 'Denmark', '45', 'DK'),
(59, 'Djibouti', '253', 'DJ'),
(60, 'Dominica', '1767', 'DM'),
(61, 'Dominican Republic', '1849', 'DO'),
(62, 'Ecuador', '593', 'EC'),
(63, 'Egypt', '20', 'EG'),
(64, 'El Salvador', '503', 'SV'),
(65, 'Equatorial Guinea', '240', 'GQ'),
(66, 'Eritrea', '291', 'ER'),
(67, 'Estonia', '372', 'EE'),
(68, 'Ethiopia', '251', 'ET'),
(69, 'Falkland Islands (Malvinas)', '500', 'FK'),
(70, 'Faroe Islands', '298', 'FO'),
(71, 'Fiji', '679', 'FJ'),
(72, 'Finland', '358', 'FI'),
(73, 'France', '33', 'FR'),
(74, 'French Guiana', '594', 'GF'),
(75, 'French Polynesia', '689', 'PF'),
(76, 'Gabon', '241', 'GA'),
(77, 'Gambia', '220', 'GM'),
(78, 'Georgia', '995', 'GE'),
(79, 'Germany', '49', 'DE'),
(80, 'Ghana', '233', 'GH'),
(81, 'Gibraltar', '350', 'GI'),
(82, 'Greece', '30', 'GR'),
(83, 'Greenland', '299', 'GL'),
(84, 'Grenada', '1473', 'GD'),
(85, 'Guadeloupe', '590', 'GP'),
(86, 'Guam', '1671', 'GU'),
(87, 'Guatemala', '502', 'GT'),
(88, 'Guernsey', '44', 'GG'),
(89, 'Guinea', '224', 'GN'),
(90, 'Guinea-Bissau', '245', 'GW'),
(91, 'Guyana', '595', 'GY'),
(92, 'Haiti', '509', 'HT'),
(93, 'Holy See (Vatican City State)', '379', 'VA'),
(94, 'Honduras', '504', 'HN'),
(95, 'Hong Kong', '852', 'HK'),
(96, 'Hungary', '36', 'HU'),
(97, 'Iceland', '354', 'IS'),
(98, 'India', '91', 'IN'),
(99, 'Indonesia', '62', 'ID'),
(100, 'Iran, Islamic Republic of Persian Gulf', '98', 'IR'),
(101, 'Iraq', '964', 'IQ'),
(102, 'Ireland', '353', 'IE'),
(103, 'Isle of Man', '44', 'IM'),
(104, 'Israel', '972', 'IL'),
(105, 'Italy', '39', 'IT'),
(106, 'Jamaica', '1876', 'JM'),
(107, 'Japan', '81', 'JP'),
(108, 'Jersey', '44', 'JE'),
(109, 'Jordan', '962', 'JO'),
(110, 'Kazakhstan', '77', 'KZ'),
(111, 'Kenya', '254', 'KE'),
(112, 'Kiribati', '686', 'KI'),
(113, 'Korea, Democratic People\'s Republic of Korea', '850', 'KP'),
(114, 'Korea, Republic of South Korea', '82', 'KR'),
(115, 'Kuwait', '965', 'KW'),
(116, 'Kyrgyzstan', '996', 'KG'),
(117, 'Laos', '856', 'LA'),
(118, 'Latvia', '371', 'LV'),
(119, 'Lebanon', '961', 'LB'),
(120, 'Lesotho', '266', 'LS'),
(121, 'Liberia', '231', 'LR'),
(122, 'Libyan Arab Jamahiriya', '218', 'LY'),
(123, 'Liechtenstein', '423', 'LI'),
(124, 'Lithuania', '370', 'LT'),
(125, 'Luxembourg', '352', 'LU'),
(126, 'Macao', '853', 'MO'),
(127, 'Macedonia', '389', 'MK'),
(128, 'Madagascar', '261', 'MG'),
(129, 'Malawi', '265', 'MW'),
(130, 'Malaysia', '60', 'MY'),
(131, 'Maldives', '960', 'MV'),
(132, 'Mali', '223', 'ML'),
(133, 'Malta', '356', 'MT'),
(134, 'Marshall Islands', '692', 'MH'),
(135, 'Martinique', '596', 'MQ'),
(136, 'Mauritania', '222', 'MR'),
(137, 'Mauritius', '230', 'MU'),
(138, 'Mayotte', '262', 'YT'),
(139, 'Mexico', '52', 'MX'),
(140, 'Micronesia, Federated States of Micronesia', '691', 'FM'),
(141, 'Moldova', '373', 'MD'),
(142, 'Monaco', '377', 'MC'),
(143, 'Mongolia', '976', 'MN'),
(144, 'Montenegro', '382', 'ME'),
(145, 'Montserrat', '1664', 'MS'),
(146, 'Morocco', '212', 'MA'),
(147, 'Mozambique', '258', 'MZ'),
(148, 'Myanmar', '95', 'MM'),
(149, 'Namibia', '264', 'NA'),
(150, 'Nauru', '674', 'NR'),
(151, 'Nepal', '977', 'NP'),
(152, 'Netherlands', '31', 'NL'),
(153, 'Netherlands Antilles', '599', 'AN'),
(154, 'New Caledonia', '687', 'NC'),
(155, 'New Zealand', '64', 'NZ'),
(156, 'Nicaragua', '505', 'NI'),
(157, 'Niger', '227', 'NE'),
(158, 'Nigeria', '234', 'NG'),
(159, 'Niue', '683', 'NU'),
(160, 'Norfolk Island', '672', 'NF'),
(161, 'Northern Mariana Islands', '1670', 'MP'),
(162, 'Norway', '47', 'NO'),
(163, 'Oman', '968', 'OM'),
(164, 'Pakistan', '92', 'PK'),
(165, 'Palau', '680', 'PW'),
(166, 'Palestinian Territory, Occupied', '970', 'PS'),
(167, 'Panama', '507', 'PA'),
(168, 'Papua New Guinea', '675', 'PG'),
(169, 'Paraguay', '595', 'PY'),
(170, 'Peru', '51', 'PE'),
(171, 'Philippines', '63', 'PH'),
(172, 'Pitcairn', '872', 'PN'),
(173, 'Poland', '48', 'PL'),
(174, 'Portugal', '351', 'PT'),
(175, 'Puerto Rico', '1939', 'PR'),
(176, 'Qatar', '974', 'QA'),
(177, 'Romania', '40', 'RO'),
(178, 'Russia', '7', 'RU'),
(179, 'Rwanda', '250', 'RW'),
(180, 'Reunion', '262', 'RE'),
(181, 'Saint Barthelemy', '590', 'BL'),
(182, 'Saint Helena, Ascension and Tristan Da Cunha', '290', 'SH'),
(183, 'Saint Kitts and Nevis', '1869', 'KN'),
(184, 'Saint Lucia', '1758', 'LC'),
(185, 'Saint Martin', '590', 'MF'),
(186, 'Saint Pierre and Miquelon', '508', 'PM'),
(187, 'Saint Vincent and the Grenadines', '1784', 'VC'),
(188, 'Samoa', '685', 'WS'),
(189, 'San Marino', '378', 'SM'),
(190, 'Sao Tome and Principe', '239', 'ST'),
(191, 'Saudi Arabia', '966', 'SA'),
(192, 'Senegal', '221', 'SN'),
(193, 'Serbia', '381', 'RS'),
(194, 'Seychelles', '248', 'SC'),
(195, 'Sierra Leone', '232', 'SL'),
(196, 'Singapore', '65', 'SG'),
(197, 'Slovakia', '421', 'SK'),
(198, 'Slovenia', '386', 'SI'),
(199, 'Solomon Islands', '677', 'SB'),
(200, 'Somalia', '252', 'SO'),
(201, 'South Africa', '27', 'ZA'),
(202, 'South Sudan', '211', 'SS'),
(203, 'South Georgia and the South Sandwich Islands', '500', 'GS'),
(204, 'Spain', '34', 'ES'),
(205, 'Sri Lanka', '94', 'LK'),
(206, 'Sudan', '249', 'SD'),
(207, 'Suriname', '597', 'SR'),
(208, 'Svalbard and Jan Mayen', '47', 'SJ'),
(209, 'Swaziland', '268', 'SZ'),
(210, 'Sweden', '46', 'SE'),
(211, 'Switzerland', '41', 'CH'),
(212, 'Syrian Arab Republic', '963', 'SY'),
(213, 'Taiwan', '886', 'TW'),
(214, 'Tajikistan', '992', 'TJ'),
(215, 'Tanzania, United Republic of Tanzania', '255', 'TZ'),
(216, 'Thailand', '66', 'TH'),
(217, 'Timor-Leste', '670', 'TL'),
(218, 'Togo', '228', 'TG'),
(219, 'Tokelau', '690', 'TK'),
(220, 'Tonga', '676', 'TO'),
(221, 'Trinidad and Tobago', '1868', 'TT'),
(222, 'Tunisia', '216', 'TN'),
(223, 'Turkey', '90', 'TR'),
(224, 'Turkmenistan', '993', 'TM'),
(225, 'Turks and Caicos Islands', '1649', 'TC'),
(226, 'Tuvalu', '688', 'TV'),
(227, 'Uganda', '256', 'UG'),
(228, 'Ukraine', '380', 'UA'),
(229, 'United Arab Emirates', '971', 'AE'),
(230, 'United Kingdom', '44', 'GB'),
(231, 'United States', '1', 'US'),
(232, 'Uruguay', '598', 'UY'),
(233, 'Uzbekistan', '998', 'UZ'),
(234, 'Vanuatu', '678', 'VU'),
(235, 'Venezuela, Bolivarian Republic of Venezuela', '58', 'VE'),
(236, 'Vietnam', '84', 'VN'),
(237, 'Virgin Islands, British', '1284', 'VG'),
(238, 'Virgin Islands, U.S.', '1340', 'VI'),
(239, 'Wallis and Futuna', '681', 'WF'),
(240, 'Yemen', '967', 'YE'),
(241, 'Zambia', '260', 'ZM'),
(242, 'Zimbabwe', '263', 'ZW');

-- --------------------------------------------------------

--
-- Table structure for table `demoaccount`
--

CREATE TABLE `demoaccount` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_type` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `leverage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `Balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(15,2) DEFAULT '0.00',
  `equity` double(15,2) DEFAULT '0.00',
  `tradePlatform` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'MetaTrader5',
  `lotsCompleted` int NOT NULL DEFAULT '0',
  `MarginFree` double(15,2) NOT NULL DEFAULT '0.00',
  `MarginLevel` double(15,2) NOT NULL DEFAULT '0.00',
  `MarginLevelType` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ok',
  `adj` double(10,4) NOT NULL DEFAULT '0.0000',
  `deposit` double(15,2) NOT NULL DEFAULT '0.00',
  `withdraw` double(15,2) NOT NULL DEFAULT '0.00',
  `internal_transfer` double(15,2) NOT NULL DEFAULT '0.00',
  `internalDeposit` double(15,2) NOT NULL DEFAULT '0.00',
  `trader_pwd` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invester_pwd` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_pwd` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Registered_Date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `bonusDeposit` double(15,2) NOT NULL DEFAULT '0.00',
  `wBonusDeposit` double(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM AVG_ROW_LENGTH=164 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demoaccount`
--

INSERT INTO `demoaccount` (`id`, `email`, `trade_id`, `account_type`, `leverage`, `currency`, `Balance`, `credit`, `equity`, `tradePlatform`, `lotsCompleted`, `MarginFree`, `MarginLevel`, `MarginLevelType`, `adj`, `deposit`, `withdraw`, `internal_transfer`, `internalDeposit`, `trader_pwd`, `invester_pwd`, `phone_pwd`, `Registered_Date`, `status`, `bonusDeposit`, `wBonusDeposit`) VALUES
(1, 'syedmohamedrafi@gmail.com', '92408010', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '0.00', '0.00', 0.00, 'MetaTrader5', 0, 0.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'eSr@$63S%', 'k3#N6_Uar', 'JrD3_K7ul', '2024-09-04 21:00:48', 'active', 0.00, 0.00),
(2, 'syedmohamedrafi@gmail.com', '69282590', 'demo\\LQH MARKETS\\LM-INSTITUTIONAL-B-USD', '100', 'USD', '100000.00', '0.00', 100000.00, 'MetaTrader5', 0, 100000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'S7&w9lf&P', 'u#SU!7+zB', 'Db&LlvQw2', '2024-09-04 21:46:54', 'active', 0.00, 0.00),
(3, 'syedmohamedrafi@gmail.com', '70755331', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '1000000.00', '0.00', 1000000.00, 'MetaTrader5', 0, 1000000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'gCso#N7-T', '4xAa2mZw)', 'pqO5_9ihK', '2024-09-04 21:47:07', 'active', 0.00, 0.00),
(4, 'megastand@protonmail.com', '42751577', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '1000.00', '0.00', 1000.00, 'MetaTrader5', 0, 1000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '+Yy@J#9l5', '-0BBH6r3b', 'lMFYy12s_', '2024-09-05 01:14:52', 'active', 0.00, 0.00),
(5, 'syedmohamedrafi@gmail.com', '84219423', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '1000.00', '0.00', 1000.00, 'MetaTrader5', 0, 1000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '8cY%rkEU3', 'Qxj7t+9*K', 'mpU1)J1tO', '2024-09-05 07:42:24', 'active', 0.00, 0.00),
(6, 'syedmohamedrafi@gmail.com', '89016552', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '1000.00', '0.00', 1000.00, 'MetaTrader5', 0, 1000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'h0i9ef@#K', '=zB*^myU2', 'IgGD&1rS!', '2024-09-05 07:42:25', 'active', 0.00, 0.00),
(7, 'syedmohamedrafi@gmail.com', '53171052', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '1000.00', '0.00', 1000.00, 'MetaTrader5', 0, 1000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '_1KM!_p2x', '6ad=Hy1+d', 'nh@Fq+#G6', '2024-09-05 07:42:32', 'active', 0.00, 0.00),
(8, 'syedmohamedrafi@gmail.com', '34298237', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '1000.00', '0.00', 1000.00, 'MetaTrader5', 0, 1000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '_6oHcWu^O', '8qHzTSe+d', '7Y&w3KIqL', '2024-09-05 07:42:37', 'active', 0.00, 0.00),
(9, 'megastand@protonmail.com', '79888704', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '5001.00', '0.00', 5001.00, 'MetaTrader5', 0, 5001.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'eJJ=QYa5J', 'NCCEa__0C', 'uD98Yn-+j', '2024-09-05 07:57:08', 'active', 0.00, 0.00),
(10, 'megastand@protonmail.com', '49945038', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '5001.00', '0.00', 5001.00, 'MetaTrader5', 0, 5001.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'A##myF3a^', 'g6PoK)TOw', '5m#-vTT9i', '2024-09-05 07:57:16', 'active', 0.00, 0.00),
(11, 'syedmohamedrafi@gmail.com', '39932106', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '1000.00', '0.00', 1000.00, 'MetaTrader5', 0, 1000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'hutL*O&^0', '_vS2q3nxl', '!DNXr5oqD', '2024-09-05 15:18:46', 'active', 0.00, 0.00),
(12, 'syedmohamedrafi@gmail.com', '44759447', 'demo\\LQH MARKETS\\LM-INSTITUTIONAL-B-USD', '100', 'USD', '150.00', '0.00', 150.00, 'MetaTrader5', 0, 150.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'e4x(LPopb', '5^HeR#Hiz', 'I-U8q82X@', '2024-09-05 21:46:07', 'active', 0.00, 0.00),
(13, 'syedmohamedrafi@gmail.com', '31719498', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '5250.00', '0.00', 5250.00, 'MetaTrader5', 0, 5250.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'a7wlN8W@B', '$bHbNRn3w', '1vWd_)2D#', '2024-09-05 21:46:56', 'active', 0.00, 0.00),
(14, 'syedmohamedrafi@gmail.com', '216069', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '150.00', '0.00', 150.00, 'MetaTrader5', 0, 150.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '8m@L6Ah@G', 'Wo@stYs4Z', 'DY0cCt!Qa', '2024-09-05 21:50:24', 'active', 0.00, 0.00),
(15, 'syedmohamedrafi@gmail.com', '501299', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '0.00', '0.00', 0.00, 'MetaTrader5', 0, 0.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'wBnc4G71#', 'ik7pj@hUQ', 'vbAK04js@', '2024-09-05 21:52:15', 'active', 0.00, 0.00),
(16, 'syedmohamedrafi@gmail.com', '168561', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '0.00', '0.00', 0.00, 'MetaTrader5', 0, 0.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'R5U#1ibVU', 'c0Pq@0Akb', '!KeV#uvl8', '2024-09-05 21:53:30', 'active', 0.00, 0.00),
(17, 'syedmohamedrafi@gmail.com', '73586887', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '400', 'USD', '500.00', '0.00', 500.00, 'MetaTrader5', 0, 500.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'i=B%vy4+b', 'DTA!cUl7h', ')Xtn6fM8W', '2024-09-05 21:57:41', 'active', 0.00, 0.00),
(18, 'syedmohamedrafi@gmail.com', '213108', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '300', 'USD', '1500.00', '0.00', 1500.00, 'MetaTrader5', 0, 1500.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'Tj#SujB60', 'HgS98Ntb#', 'r2!1MKFtK', '2024-09-05 22:06:46', 'active', 0.00, 0.00),
(19, 'syedmohamedrafi@gmail.com', '161557', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '300', 'USD', '1500.00', '0.00', 1500.00, 'MetaTrader5', 0, 1500.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'eJX87DF@h', 'i1#W#2Egb', '412Zk#kXj', '2024-09-05 22:07:07', 'active', 0.00, 0.00),
(20, 'syedmohamedrafi@gmail.com', '996847', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '1250.00', '0.00', 1250.00, 'MetaTrader5', 0, 1250.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'tk@2pdf7C', 'L#IgaPT4e', 'Xf60#JfKo', '2024-09-05 22:08:53', 'active', 0.00, 0.00),
(21, 'syedmohamedrafi@gmail.com', '659707', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '1250.00', '0.00', 1250.00, 'MetaTrader5', 0, 1250.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'Ek5QUS4@J', 'E6LEtqO@K', '1NPXRz!uN', '2024-09-05 22:10:40', 'active', 0.00, 0.00),
(22, 'syedmohamedrafi@gmail.com', '575501', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '1000.00', '0.00', 1000.00, 'MetaTrader5', 0, 1000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'CFO!7Yh6e', 'a0v@JSQrQ', 'Fq!5yj9Ih', '2024-09-05 22:44:45', 'active', 0.00, 0.00),
(23, 'syedmohamedrafi@gmail.com', '328653', 'demo\\LQH MARKETS\\LM-INSTITUTIONAL-B-USD', '100', 'USD', '10000.00', '0.00', 10000.00, 'MetaTrader5', 0, 10000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'R8s8jr#5e', 'Rf2E3@CFz', '@CQikV0Gz', '2024-09-05 22:59:24', 'active', 0.00, 0.00),
(24, 'syedmohamedrafi@gmail.com', '567679', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '1000.00', '0.00', 1000.00, 'MetaTrader5', 0, 1000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '2VBhUtV@n', '!Irq1q53k', 'FRikGM7!1', '2024-09-05 23:37:21', 'active', 0.00, 0.00),
(25, 'syedmohamedrafi@gmail.com', '207824', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '0.00', '0.00', 0.00, 'MetaTrader5', 0, 0.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'Xaep0wAa#', 'Pdi!74c3i', 'DQBUh@Y2v', '2024-09-06 01:08:28', 'active', 0.00, 0.00),
(26, 'syedmohamedrafi@gmail.com', '230061', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '0.00', '0.00', 0.00, 'MetaTrader5', 0, 0.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'jt#9MrC8w', '@WtdnUd07', '45!RnCPek', '2024-09-06 01:08:32', 'active', 0.00, 0.00),
(27, 'syedmohamedrafi@gmail.com', '778933', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '0.00', '0.00', 0.00, 'MetaTrader5', 0, 0.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'd4Ajfd0@F', '#3fPI7clp', '5tKfPi#B#', '2024-09-06 01:09:27', 'active', 0.00, 0.00),
(28, 'megastand@protonmail.com', '338444', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '2001.00', '0.00', 2001.00, 'MetaTrader5', 0, 2001.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'HTpyh#0J6', '#Q34uE8PY', 'h7LutH!7f', '2024-09-06 18:30:51', 'active', 0.00, 0.00),
(29, 'megastand@protonmail.com', '895558', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '100.00', '0.00', 100.00, 'MetaTrader5', 0, 100.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '#r7aPoUsr', 'y@76foQ9u', 'vz1Bb!ZEC', '2024-09-06 19:04:44', 'active', 0.00, 0.00),
(30, 'megastand@protonmail.com', '228368', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '333.00', '0.00', 333.00, 'MetaTrader5', 0, 333.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'Bplu4@IEE', '1@QDyGLxB', 'fFjUNE6#6', '2024-09-06 19:06:02', 'active', 0.00, 0.00),
(31, 'megastand@protonmail.com', '296958', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '444.00', '0.00', 444.00, 'MetaTrader5', 0, 444.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '!Fu8CuD69', '#!h4bi48Q', '4BDD!fagb', '2024-09-06 19:06:32', 'active', 0.00, 0.00),
(32, 'megastand@protonmail.com', '108030', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '551.00', '0.00', 551.00, 'MetaTrader5', 0, 551.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '!6ZfgBFvr', 'NusMdAM#4', 'Vu!zs33@Z', '2024-09-06 19:06:51', 'active', 0.00, 0.00),
(33, 'megastand@protonmail.com', '443727', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '850.00', '0.00', 850.00, 'MetaTrader5', 0, 850.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '2ES6#9hIL', 'WWxwA#8Bi', '!n0kD3tH3', '2024-09-06 19:15:13', 'active', 0.00, 0.00),
(34, 'syedmohamedrafi@gmail.com', '884266', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '100.00', '0.00', 100.00, 'MetaTrader5', 0, 100.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'uW0QH#S1I', 'i@#PnOg88', '@6GaKUfUG', '2024-09-07 03:41:54', 'active', 0.00, 0.00),
(35, 'syedmohamedrafi@gmail.com', '586918', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '300', 'USD', '500.00', '0.00', 500.00, 'MetaTrader5', 0, 500.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'ZGUhTl1@z', 'BTeP!Dl5m', 'V#4tlLkYx', '2024-09-07 11:19:17', 'active', 0.00, 0.00),
(36, 'syedmohamedrafi@gmail.com', '478099', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '300', 'USD', '500.00', '0.00', 500.00, 'MetaTrader5', 0, 500.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'YjJx@nNE2', 'yukNoa@1h', 'FIKj3zQ@6', '2024-09-07 11:20:27', 'active', 0.00, 0.00),
(37, 'syedmohamedrafi@gmail.com', '294868', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '500.00', '0.00', 500.00, 'MetaTrader5', 0, 500.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'MU2#A56Sp', '6S@L7aYMA', 'Y5FZn1m@L', '2024-09-07 11:20:41', 'active', 0.00, 0.00),
(38, 'muthuvenkatesh808@gmail.com', '136218', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '50.00', '0.00', 50.00, 'MetaTrader5', 0, 50.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'SsfZ!q9BS', 'Ns!trI71x', '60uNtB!1i', '2024-09-07 16:52:39', 'active', 0.00, 0.00),
(39, 'muthuvenkatesh808@gmail.com', '909043', 'demo\\LQH MARKETS\\LM-INSTITUTIONAL-B-USD', '100', 'USD', '100005.00', '0.00', 100005.00, 'MetaTrader5', 0, 100005.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '7yxwHqtu@', 'HRmYDdW2!', 'T6mMGQ#3U', '2024-09-07 17:34:17', 'active', 0.00, 0.00),
(40, 'muthuvenkatesh808@gmail.com', '729104', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '5000.00', '0.00', 5000.00, 'MetaTrader5', 0, 5000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'ai7!48Hzj', 'p@837bavC', 'DkBf!C514', '2024-09-07 17:39:28', 'active', 0.00, 0.00),
(41, 'mediaslush@protonmail.com', '630713', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '500.00', '0.00', 500.00, 'MetaTrader5', 0, 500.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'qtGPNE!2S', '5l@qBQq7z', 'fI2@tzcAu', '2024-09-08 01:39:15', 'active', 0.00, 0.00),
(42, 'gurkiran121@gmail.com', '416178', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '200', 'USD', '20000.00', '0.00', 20000.00, 'MetaTrader5', 0, 20000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '!htiX2kV@', 'kg!u2iSVK', 'Zya7i@stU', '2024-09-09 20:52:15', 'active', 0.00, 0.00),
(43, 'furnwest@gmail.com', '399233', 'demo\\LQH MARKETS\\LM-ECN-B-USD', '100', 'USD', '100000.00', '0.00', 100000.00, 'MetaTrader5', 0, 100000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'iR2v@gdJf', 'AHctZ#J72', 'w6!XrnDb#', '2024-09-12 03:30:37', 'active', 0.00, 0.00),
(44, 'rugmar91@gmail.com', '462866', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '70.00', '0.00', 70.00, 'MetaTrader5', 0, 70.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'bR#7UAy0Y', 'Vd8T#TRQp', 'UHwhP2i7@', '2024-09-12 15:46:50', 'active', 0.00, 0.00),
(45, 'lqhmarkets@gmail.com', '586475', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '50000.00', '0.00', 50001.95, 'MetaTrader5', 0, 50001.26, 7246659.42, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'C9NyMva@F', 'GD3q3Qfv#', '110j#lBqK', '2024-09-22 20:45:03', 'active', 0.00, 0.00),
(46, 'lqhmarkets@gmail.com', '532759', 'demo\\LQH MARKETS\\LM-STP-B-USD', '100', 'USD', '60000.00', '0.00', 60000.00, 'MetaTrader5', 0, 60000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, '2z19W3#hN', '8xgV3Ww@!', 'vjoODY0!Z', '2024-09-22 20:45:32', 'active', 0.00, 0.00),
(47, 'contact@evsconnect.net', '258858', 'demo\\LQH MARKETS\\LM-STANDARD-B-USD', '500', 'USD', '500.00', '0.00', 500.00, 'MetaTrader5', 0, 500.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'kP2#9C!DA', 'cR0Gw0@M@', 'H@wcFVBc9', '2024-10-07 21:35:53', 'active', 0.00, 0.00),
(48, 'jalelwabou@gmail.com', '619515', 'demo\\LQH MARKETS\\LM-PRO-B-USD', '500', 'USD', '50000.00', '0.00', 50000.00, 'MetaTrader5', 0, 50000.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'l3GdPDP#6', 'oTPQ5#O2B', 'IJw!0r!h2', '2024-10-08 17:35:57', 'active', 0.00, 0.00),
(49, 'warisahmedbarak@gmail.com', '608961', 'demo\\LQH MARKETS\\LM-STANDARD-B-USD', '500', 'USD', '1000.00', '0.00', 0.00, 'MetaTrader5', 0, 0.00, 0.00, 'ok', 0.0000, 0.00, 0.00, 0.00, 0.00, 'Waris@123', '56ojZYGG#', '@S8kGpFbc', '2024-10-15 13:41:13', 'active', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `demo_deposit`
--

CREATE TABLE `demo_deposit` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_from` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposted_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int NOT NULL DEFAULT '0',
  `AdminRemark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Js_Admin_Remark_Date` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_proof` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `demo_deposit`
--

INSERT INTO `demo_deposit` (`id`, `email`, `trade_id`, `deposit_amount`, `deposit_type`, `deposit_from`, `deposted_date`, `Status`, `AdminRemark`, `Js_Admin_Remark_Date`, `deposit_proof`) VALUES
(1, 'muthuvenkatesh808@gmail.com', '909043', '100005', NULL, NULL, '2024-09-07 15:34:17', 1, NULL, NULL, NULL),
(2, 'muthuvenkatesh808@gmail.com', '729104', '5000', NULL, NULL, '2024-09-07 15:39:28', 1, NULL, NULL, NULL),
(3, 'mediaslush@protonmail.com', '630713', '500', NULL, NULL, '2024-09-07 23:39:15', 1, NULL, NULL, NULL),
(4, 'gurkiran121@gmail.com', '416178', '20000', NULL, NULL, '2024-09-09 18:52:15', 1, NULL, NULL, NULL),
(5, 'furnwest@gmail.com', '399233', '100000', NULL, NULL, '2024-09-12 01:30:38', 1, NULL, NULL, NULL),
(6, 'rugmar91@gmail.com', '462866', '70', NULL, NULL, '2024-09-12 13:46:50', 1, NULL, NULL, NULL),
(7, 'lqhmarkets@gmail.com', '586475', '50000', NULL, NULL, '2024-09-22 18:45:03', 1, NULL, NULL, NULL),
(8, 'lqhmarkets@gmail.com', '532759', '60000', NULL, NULL, '2024-09-22 18:45:32', 1, NULL, NULL, NULL),
(9, 'contact@evsconnect.net', '258858', '500', NULL, NULL, '2024-10-07 19:35:53', 1, NULL, NULL, NULL),
(10, 'jalelwabou@gmail.com', '619515', '50000', NULL, NULL, '2024-10-08 15:35:57', 1, NULL, NULL, NULL),
(11, 'warisahmedbarak@gmail.com', '608961', '1000', NULL, NULL, '2024-10-15 11:41:13', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `dep_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dep_id` int NOT NULL,
  `dep_status` tinyint(1) DEFAULT '1',
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deposittabledemo`
--

CREATE TABLE `deposittabledemo` (
  `depositIndex` bigint NOT NULL,
  `orderId` int DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `gateway` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `batchId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `amount` double(10,4) NOT NULL DEFAULT '0.0000',
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clientAccountId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clientTradeAccountId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=MyISAM AVG_ROW_LENGTH=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emplist`
--

CREATE TABLE `emplist` (
  `client_index` bigint NOT NULL,
  `role_id` int NOT NULL DEFAULT '1',
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `dob` date DEFAULT NULL,
  `password` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `number` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_address` varbinary(255) DEFAULT NULL,
  `company_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `db_prefex` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` int DEFAULT '0',
  `profile_pic` longblob NOT NULL,
  `empId` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `userDepartment` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `userRole` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `userAccessLevel` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'subAdmin',
  `emailToken` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `email_token_time` timestamp NULL DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dial_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zipcode` int DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AVG_ROW_LENGTH=232 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emplist`
--

INSERT INTO `emplist` (`client_index`, `role_id`, `username`, `email`, `gender`, `dob`, `password`, `number`, `address`, `website`, `created_at`, `uid`, `company_name`, `company_address`, `company_number`, `db_prefex`, `status`, `profile_pic`, `empId`, `userDepartment`, `userRole`, `userAccessLevel`, `emailToken`, `email_confirmed`, `email_token_time`, `country`, `dial_code`, `zipcode`, `city`, `state`, `updated_at`) VALUES
(47, 1, 'alphabullforex', 'admin@alphabullforex.com', 'Male', '2022-09-19', 'BULL@1431', '0585948900', 'Dubai', 'alphabullforex.com', '2020-12-14 00:00:00', '0149B3B2927010FB', 'PROFX MEDIA LLC', 0x4b656d7020486f75736520313630204369747920526f6164204c6f6e646f6e20e2809320556e69746564204b696e67646f6d204543315620324e58, '', 'ALPHABULLFX', 1, '', '1000', 'admin', 'Super admin', 'admin', 'B202E2832', 1, '2019-02-02 07:00:00', 'United Arab Emirates', '+44', 1234, 'Dubai', 'Dubai', '2024-08-16 10:36:03'),
(70, 1, 'priya', 'priya@gmail.com', 'Male', NULL, 'priya', '9675645654', 'noAddress', '', '2024-12-31 00:00:00', '', 'FTM Global Markets Pte Ltd', 0x6e6f41646472657373, '', NULL, 1, '', '', '', 'Super admin', 'subAdmin', '', 0, NULL, 'India', '+91', 0, 'n', 'noState', '2024-08-16 10:36:03'),
(71, 1, 'raj', 'raj@gmail.com', 'Male', NULL, 'raj', '45355', 'noAddress', '', '2024-08-16 12:29:30', '', 'FTM Global Markets Pte Ltd', 0x6e6f41646472657373, '', NULL, 1, '', '', '', 'Admin', 'subAdmin', '', 0, NULL, 'Bahamas', '+91', 0, '3fdsfds', 'ffsdfsf', '2024-08-16 10:36:03'),
(72, 2, 'jenny', 'jenny@gmail.com', 'Male', '2015-11-26', 'jenny', '945645', 'noAddress', 'ddfgdf', '2024-12-31 00:00:00', '78412345678', 'FTM Global Markets Pte Ltd', 0x6e6f41646472657373, '', NULL, 1, '', '', '', 'Admin', 'subAdmin', '', 0, NULL, 'Australia', '+91', 834003, 'Dubai', 'jharkhand', '2024-09-03 10:19:15'),
(73, 2, 'simran', 'simran@gmail.com', 'Female', '2021-06-22', 'simran', '845678', 'burjuman', 'https://yahoo.com', '1900-01-22 00:00:00', 'dfd6567565dgdf', 'FX', 0x70756e65, '7765756545', NULL, 0, '', '', '', 'Admin', 'subAdmin', '', 0, NULL, 'India', '+91', 0, 'n', 'noState', '2024-09-03 10:19:30'),
(74, 2, 'Doe', 'doe@gmail.com', 'Female', '2016-06-22', 'doe', '94534534', 'Karama', 'https://google.com', '1900-01-24 00:00:00', 'reer56675756gdf', 'bridging', 0x6b6172616d61, '74544534534', NULL, 0, '', '', '', 'Admin', 'subAdmin', '', 0, NULL, 'India', '+91', 0, 'n', 'noState', '2024-09-03 10:19:27'),
(75, 2, 'Ram', 'ram@gmail.com', 'Male', NULL, 'ram', '', '', '', '1900-01-30 00:00:00', '', '', '', '', NULL, 0, 0xffd8ffe000104a46494600010100000100010000ffdb00840009060713131215121212161515171517161817151717171717161515171717181515181d2820181a251b151521312125292b2e2e2e171f3338332d37282d2e2b010a0a0a0e0d0e1b10101b2d2520262d2d2d2d2d2b2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2dffc000110800e100e103012200021101031101ffc4001c0000010501010100000000000000000000050203040607000108ffc400471000010302040207050505050607000000010002110304051221314151061322617181910732a1b1c1144252d1f0153392e1f1627282a2b21623345374b30824256373a3c2ffc400190100030101010000000000000000000000010203000405ffc4002711000202020201050002030100000000000001021103211231410413225161143252719105ffda000c03010002110311003f000382d40752516ba7823b3b283570beae949d0c4faf046ba3941af66bc1792e118a42029d4a372a0dfd07b84b7873476f698eb088d021d885efdc68ee526df2d181187b6a026468a69619d914b3aad6b7548eb9a7b905776d188a6969aa82e680ede14eaf5392175a8cba49558db051e625584404219460c9469f40424d0b20e3aa64f8989985dc00d4d5f552e9d53574e6b04043c5c983c965bd9a87295c344a1777752f5d55ce274090ea11a9558aa31eb5faca94dba820a1a412961a78a76ad504b85b7488650d03828379762a3b440db580097635fb5a2e68fa684368010769c143759e73b429b795b685cfab95aab17f463dbbc16dfa925ba3f2e8412497468238c9d1572ae1d59bef5278f11f4566c26b65a8d79e1f5569b9c5e9d52c6064c6e4ee6782ea8e455fa1523257b48dc11e2212495b9dfe0f48522fa948401cb4594637674dd5b2dbb7c4709e2a8f4e87524c052924ab15bf446b3bdf058381237f02a2d6e8f3daf2c2e0b06d0203d7ae729775855461022678853a9f446e9cceb1b4e444efaac6d00e42f52fec753f03ff0084fe4b9631b5e2762ea90d9801229dc3688c8d4aab898326502ace9768bc78295fc85ad85ae2e811deabd7af00e8a4d705aa039a49955afb356f43c6b38ecbc6e61ba25605ba484ce2955a0c0457d07484b69976c865fb5cd74468ac386d386ca8f88b83b4849ee252a14acd4bdd636e68be1770dcbcca88ec3038f253db6ada4c868d5526d5505ad02efe9973928dbf6538ccc49d13b4edaabf46b49f01282e8549b07ba1ba0dd453a9328dbf02a8c12e1f113e8a056c26a1d729cbcf5d55131fda9fd03dcd93a262f0108a3adb2a857c0046f607069ec18278a296f4e1b2146b7a61c5157b9ad6c04d27e0560a6b9ef7c225f672d00b8ca6ecd90ece53d795f390392675c4c2edab3512c1abb195db51db03b2af3984243aabce8d05242eed1aacd9fa4fd34b4fb31a6c21f51c21ac1cfbf9059ff4630c14dd9ea412759e13cb5556b4b7707827795a860bd1c7d6a61d9c73812bad654de8cf41ec57a416bf66730c4e5d1bc41e0b2775ad5a956580ba4adb307e82510d0fa9db76fa80425db61d6d4ae402d6878db48df65492e42a2bdd1df66a5ec6d4b8304eb946ba70928fe25d15a82939b4889e0381ee5776eda2e8428a518f7fb375bf005cb60c83905c853fb178985d0c383e4ceca255a21b5001cd3d6d5b283aa558daf5a4b89d415e5c57828d248f714a23288d50eb9a01a3c510ea9d9f2bb4842f1caa0111c13b8f9137e0934a90680495e754d7104a1b4eeb3403308857acc63419d52b8f2548d411a94606fa21770f12a1d6c449e2a1d52f77b81658d5520a41367724d6a8760249d10ba359ed3055a302a409eb0f0da372790299e3e3b1e18dca4a24ec2f070c607551e44c7f11fa6e9cb8be2dd1820726b607a93aa9377770dfc4e06206cd3cb4dcf3f9aac5e36a3c9d48f05356cf5b1e28e35a43389dd92649d7c7f922f875d97db10e39b2ec4f00469e2aab56d7b4019927c7d636562af4fab64347003d0007e4a8d68cbb05d76833b1f0dd03c52d09da74e3cd4e75c90ef3522b43869de556272e585ecacd902d3aeca6bea0526e29002541b2617bf28124aaba6ace36950a65cf0d9583a23d14a97ae7115030378012e33e7a04cde605d58cce11a4eaac1ecec8fb40199ed11bb337c602314ac9b610b4e8054a75dbd753eb69712069e247d15d311e855a1a272530c21b223c159e935cd6cb1fd608d89127c0f3f1486d6a758160ecbe3563a24788d8f92ac60a20a300c42cf23c8220ce9deafdd07b9734007dd29fc63a1c2abdecd58e225b3aebdc7ef0efdf814e742e8e563edeb34b2b30c39ae1123eeb9a7883cfc541636a5a055b2ff6f540007a2855b076bae3ae227b30a3da400c24e66660d9e47604f71faa3ce30ba931aacf3468ee0bcab5434493a26efdc05279e4c71ff2959a7487a4155d469f0696024733de92735109a4fdbd9cc2f164bfb46b7e0ff30fcd72dcc1602635afed34cf385370ca4e065a0c77f7211d117f5348e792e99f01c94e7e2c58d240e67d755c2a06b158e5d39a2751cfcd556ad424c953f15c4dd57451748d5166a26e561677a1b5e938ee7448cf1b2f1d58bbb968a482d791e3943752a7585f00d80d955b7bfb504e8895be28d60884ae0c0c45d92e7c9d24ab2e195c881b068891f123bf8055f6570f7030a57db206fbfc871f59f82d76d23bbd22a8b9169b6aa0ed006cd1c9bfcf5d794a93568cb486409125c7ee8e2e3f1006dbefb2ad61f7648cc7f53cbbf61e68a8ac5f1447de32f23bb84f2fa0084a37d1daa5431430f0f7cd305c07de3f4fe7aa8b8fd27b46c7bc2d1307b06b46c9ec47046d4f784aa2868939ab30aaafd784f2260fc548b0bc8218ed091c74e255aba57d0c7825d4b56ea721dfc8acd6fabb9aecae10e69f43e69e31bd1194a8b0e304888e3a77f87c42dbbd9bf41a9dbdbb2a56a60d778cce24496cea1a394058ff00472bb6a9a45c261ec76bcdae91f23f05f455862f4cb5b2e8d38ab636bc9cb997c8a27b5bc2c754ccac339b71c046a12bd8d5864a754b84cb841f2d42d0ef6da9d7a65ae01cd2a3e0585b2de9e466d24aa57cac8d6c9cfb769dda146ad83d0790e7526e66ece021c3c1c35535704c1a0562980b2b41152ad3734cb5cc76a08ee782103e9ad47d3a74ea369bdf5a9b8769ac30f611151ae8d811ac73015c903e905c566c368bda1c7839b223d52b4068a9741fa4d44baa5b542e649ccc158f68876e27890657b8ef4ade4f54d741a6f2d7c7de618870f220a1d8f743ef6e08aaf7d3a85bee800532359dc6ea977bf68a6fcb5590f6ba09cc0c81a477a94a4d2a3765ceefa6f50527d0319a0b093b891123c8a5b5d4aad93da7de6304776b3a2a15f383a5ed327720ee935b14eccb5c402d00848a57d99689bfb5fc7d57219e6b92ec4d969ad85380d3e4815fd954132745a0543d8006f0a0fec6eb357283953a3a1e36d942a1484c129ac519f851ec6f06148e882b983894d1df46e0c8186d225dda522f6de0e8bda8e00485ed9d604f68a3c5d8281356846ebadadc38a2f7d7148689cc2e8871901192690ad511dd4c53698d1407bb369e5eba7c8fc14fc7ea80f206c2079c4a1f6e38febf5aa945f93d3c71a82886a85400770123c784febeea33d1ebea59a3382662275d3455715a0379924fd3eaa3613655dcf0f0e00ccc0e52790f0e3cd560ad334e54d2363c4efdf42967637369a4981e2567f5fa6afcf35cd4a9dad18c3929e91313a1891cd6a552cbacb66023eeb67d107b4e84d299692cd4ec04ebbea6653a75a64defa23e0389fda599c31ed1af65e0f0ee3b2a37b46c15a5e2a8104883de42d929616ca4ccadf5e27bcaa474fac49a7039fe686d30e9e8cb701ac58fcbc674f1e0b69c3ee43e931e362d07c162cfa259520eff00afd7aad27a13779e91613ab4c8f077f39f5424472c7e37f46a181de06b20a354aa876c5500de656c4a2980e2c1bd973b75d18e7e19c965c805c8155c56a6ec6cb79a6dbd26646ba1e4a8e710a4c3952b00167b8b7494f5ae8d634f0844efb1b73876418548b830e7120c932a33c97a466822fe9956d4440eefaaa8e29503dc5d2e93accf14bbeb9871ec90a2d4a80b74dd249d8102ea8ca7624f794cf524b7829552839da0049ee51aad0a8cddaef446933531beacf32b935d77715c9b87e98d42df1461332a78c4dbc1672cb820eea48c44c44af0a7867f63294968b3632f65455dbbb56ec39a1efc45c1da9d11ab787095d7814d2a6ca41f2239c25b93bd08ad8696ec0ab3b591b94e547b3277ab7292124e8a19b425e255b70ca3946c87db5ae6abaec8f55706081c95a4a4d0b27d5142c4ea17d5772cc67f5cd28081038c01faf8fa28d55d351dfde3f34e3ebf01bc6a7801dca4d748f5a34b63d46a4bf4d868aef55eda74d9940cc48f2e65ddc150ec7b2e00f13b71ef56eb9bc14da2a54114e00cdb84e953a02926acd4e8e2b445168351bee8d8cfc902797d4717d073e9c73f75de2d9556c22c45521f428d620eb2d86b0e933da30ae4fa0fa6d3d6546b08139298cf53691a9d04c386a0098d55145b24da8f445a18ebdceeaea0878f43de1231f74d07b8898697473ca263e09185616e666ad55c5ee738901d072374868f493e28774df12eaed2a11b96968f17e9f594b5f2a0c9e8cc8e24eb8a8ea8e686c530d6b44c3434733c4c4f9a39d10c4babaadd60384793bf9fcd5730f6c35da7dd3ea026a8d7ca343b6debfaf54d357d135b8d3369bb6e612a25817f5806a93d15b93736ed7711d93de4692ac9696cda625d12149fa88625f238941dd05a9e28d65301cd320442a3e21784b8b808d4953f15c6b2e806882fdb839da8dd2af56a6eeb43ac8968358562cd7765c3822b4f0b6553b6882db5360ed29acc71b4cc0dd07ebf1aec6e50a7602e97e1cda2e803470f421556ce9973e2242b96277a2b197edba8d83d0a41e608dd6c7914e77e0e75b61fc1709a7d582e03698507a4b4299616b409e03452314c4325179a7b8698ef8546a7d217ceb48c9e657a9151f03f2b23fec77fe0f8ae45bf6bbbfe58f5fe4bd46907454ab56e496d69024a363071303c7b94bbac301600178eda4e8cd156350172b6618d9a7e4abb738616b840560c229b83754d11b127c86eabdd04a12ebe3cd5c0d887d323b956310c1f2955d204d08a0e339a61386e4976a676512b34b7444b06c30d5a8d039892aebe48c97c6ca86396e68d6a80e930e1e0fed4fd3c941ebf281104f7ec0efe6b4af6c5810a6db7acd1bb0d377f85c0b4ff009cacd2e1b0c613fa8414774cea536e0990ab5d3b30703a820c9e31f4dfd5689d14c7195a9f54f883a41fba792ceead382914eb3e9383d8483fad0f34f28292163371766d184d2750258c6d4cbc053710dd88da600d76562b1a552a402ccaddf793f90d9557a19d296bd8deb74740f03e079ab88c71836700913fb3a6f569226e245aca7aacff00a5d40d4b7aae8fba4b4786bf4566b8bb35c893d91f129bbfb706990797c14dbb764fc518ae0d524b87f61c7e5f9262a320b84733fd3c93b42d8d0aefa4f91ef8079b729d7d149b9a72e04684ea08e0e6ee3f9782abec9c768d3fd9ad2ea6c5af2673b9eeda2066cb1fe59452fef83cc668558c22edd449a0dcefa4fa42a30483966272c91237d073d94cb2b70f766738e53b765df92f072e372cae4debb2334c76eedc988d548b0a2d1a39a88b29b234d610cbcc483384c26f762e3c08f444c5af1d4c88f779842efae7334107546eb38d666a3bb651e9616d690e71d02d170d7e080ba941ce688f8a0d57ada2f064c2b362b8bb1bd9600502bbb8eb8705d98a56edf40d136ef1f69a61b067bf64d59dcb1f0080100ba68037d9396176176e3693b60b2d90cee5c85fda99cc2e5d5fc8c66b64efda8d012063227555f7549d125eed170fb31bb3b38168188533a984e51c5990408559602425d36908ac7143c6345868e3b974502fb15cc7443852713b271f6640945f1e80e29b11755a44a31d077d4357b3a8ef55dbda90d457a057555b5bb00c7868ab1d2166bc22e5ed32ddf52de9b5e789223c593f0594e3b4468d1b0fa69f1dfcd69fd21c53af2e2482da60b647bb986e07333bf7e9e19e63cd0065ddced4ff00646903c4fe496f94ad1d0a1c2093033a8cb7bdbf4d546baa12d3cc09f2d8fc917347471fd6b2a2d4a7ab2767340f1cd3f429b95315c741ce89d0cd4403fad5586d6c0877e6a2f412ccf52011c4fcd5d2961db28cd7c9948f485e174800a4d76e6d38276dedb2f0524d18049d96480ccd3a6d840fde81ab75f2e3f0954e6c9113da69d3be36239e8751dd2b61c5ed9af0413d983104c190470d0eeb19bda45951f486ec740ef1a16ebcc4ab4237a25375b0d61f8b38e4d4073092011313ef00791e48bb7a4858c0328104c41de49e13c26151c5d1e3c389027d612bedaee67cf8782597a48cded0beec7c9a66198ed2a81cd0e2d71130ed267783c50bbaa6e75480e3baa5d2bc70d8a9b431aaad208709046e27d6573bff00cca7717ff4e79f17b4693d77554c49e1f1f055cb8c41f51c753978469e2a455c79b56931db176fdc468403e2835f5cb5ba346fb2e7c3e91c2d326ed932e6d1a199a50ea4c2272a40bc73bb3f347f0fa0d0c9559258d6fb035455ea59bde72c149364ea6608568b9bc6b0e601087e301ef321342736bad0841c87915ea2bd631727e7f8351706f42e7610104c43a28f0e80d256af5aa963740878be64ead33e0a4b2c8f41b546775fa36fa6c93c90ba2c25d10b51bea9d6681a63c107b7c0087e6c8532c8eb68572ae809678439d0211aafd16219df08e506163845328cbea12df74a97293dd014efb32aabd147cc9d072d04aea54b2348796d268dc3489701ce0e633cb4568e9b74805b520deac759524367846eef290b24c4b162f044ea073da4febf9abc79cceac6e295b0a635d221a0608a6cd1add3b4e1b131c06a8161ec7d67b9c65c7ebac796e7c90cab25c07ebf5f923d835c8a74f28f79d249e43fa478931ce7a22b8a1252e522557a4002c1bfe423e7f241ae7f7a75d18328eeca00fa4a296f5097ebdfe43828468cbc73792e8ee986fc737a22bb34ba34ae86e1554526b9ad00113af19d498579b6b5701da683f0f409382d02da4c68e0d03d0226ca69546c0e443630c03960c7a24b2d8bbdfdb5d06ddde28a7569b7353710720654c3e99ddba77ac6bdaa61ada576d7b1b0da94f51c3330c1f5047a2dd0b1677ed830fcd6cdaa06b4ea34f93bb27fd49e1a909937131d76bfaf9a6da9d84dbd749ca2d717af2744db8a202c1d17aed738d17f1ed327fcc3c604faa398db6980d8de552b0b7e5aac7fe124fc08faa3b68f152a4ba179bea138e4e49e80dd3263ad750422ccb4701be90abf777f1532b4cc22f738839cc6e57191f92e79294b72e84ec1579710e2d770d14414a4e83529bb90e279927e6ae3d1dc25b9733f92ab9fb71fd0a2abf63a9c8af55fbec8dee5ea87f272ff0088691b17d9da7824fecfa7f8428767890729cdba1cd7a51716acbc934736cd83ee84e0b76f25e36b8e697d684fa17679f676f20bdea4725e75c39af4d50b68db312f6f35bff33458081968cc7139dce991c07659e3af2592dabbb5e2aefed32e995313bb7662e01ed689e1929b18e03b839ae5451a1d781faa1c7b29cba275bb3b4391fa82a5d0d89ee3f42122d69ffbb27f0f6bd2607869f144ad6da469c4023c0ff5f8299d0b6336e603dc760dfebf14e7465a6b5763ddf7abd1a6d1dc1c1d1fc34cfe8a918a5b0a541f3bb8e51e676efdfe0a77b30b6156face970675b55dde4d37813de06423c51c6b958b91f14acdc2c6943478298d09dea32e9c97a18b5508e562136e09e735272a20b182d43f18c105d51ab45c34753781fde2d21a7f8883e48c06a996f4e0779462ad8252a47c810604883c41e07926ea2b2f4fb0cfb3e21754a2075ce7b7965abfef5a07700f8ff0aadd40ba4e710dd921c754b6a69fba0c22a91d54ea2e73660a1e88b1914f38d79a86640684b6af6a4eea4b31024e551e9f6dc0342b1e1983b5ad25cb932ca315bec5a205adbd47196b640332ac6cba7ba9e5d5a469a6e3cc7052303b96329bcc0e3bf1e4a25bde873883a4f25cf3c8e5e360645fb1bff12e4532339fc5729e8d46816988b7bc29d6f7a09225564b81123e09965ec190755de77973177075257aebce4e55fb5c718e10fd0fc0fe4a40a61dab1de9a845a7e0d41aa57439a974ef0712aa552a399ef7af05e55c40063dc4c431c67943494bb40a30ec52ebacab52a7e3a8f7ff001bcbbeaa0d61a4f77f4f82f1bac01b404bbb3a2efa3943784d41d583c33169f13a8f5877a8566c2adc31b9809825ad03731a69e2411e45573a3f684db89ca3354cc4bb486b410ddf8cea110af7aea74cd3a6e97703f864448f8f7eab8b23dd23d1c2b56c8bd29b804306692d7f6b2ed9b29103b86c15afd855b17dfd4aa7ee5077917bda07c1ae59cd7d18d0772e9f45b2ff00e1face19775b9be9d2fe06979ffb8d5d385544e4ceee66b4fa72a3b9b0a5a43d928b8d934e882e292d29db8a446bbf8266d5a5c7e6a6d155d592e8539d5482b9a2042f5552a24dd988fb7bc272d7a1740695186938ff006a992e6f996b9dfc0b27a817d27ed730cebf0cac47bd4b2d76f77567b7ff00d65ebe6d7aa2e84630c4c3cf68a79875519e7b47f5c128474ec8e61b6e4684765c35409a745a5d2c332d1a4e3f7a9b0f91682b9fd43a414acaa58e1ae6d6ec8d382b432c6bbc18d94da36190b6ab1b980f7959cdc349600dcb3cc7c1704e76efc9684135b2857362ea0435c7de52ba35870acf7b4ef12116e93da6620ef047cd58b05c29b4ae2998d1ecf888489ead8ab1fc8aff00fb227bd72d3ba8ee5c9ad94f6a266d84dd4e61dd2a055bb8711de543c1eb768ebc147aae97bb5e2576a40b08baec42f2cb147b0f65d1f2f451a935a974e88cc8d1acb861f8cb6b3723c00ef81f0427a4344b2856e2d34aa7c5877429ce6b7594ee258a975a5769d49a4e00f888d564ad85bd1989d341b9dfb926e2a341d44f770f3e69da4c824f243eedd2badf4732ec2b87626e332240db989e478224d68c85f55d969ce83ef3c8e0398ef28060ff007bcbea9f73492a5ed26556692ec4dd56cd509d87003603905f457b0fb7cb85b5fc6a56ace3fe17f57f2a6be7377be7c57d35ec9c46156a07e073bf8aa3ddf554aa5489b76ed9715e12932bc4002b32f015e12b82c6152ba578126a158c44c6688a942ad33b3e9bda7fc4d23eabe462ed355f5e5cbe29bdc760d713e0012be3e0f9d53a01e34eaa355f79dfae09f6ee98b8f7c8f04ac2394ce8b6ec2289af696c340deaa9027b831b2b110b51f66f8b175aba938fee9c403fd877687a12e1e8b97d5af872fa1e2add176af6ac6b452a2331e3c878a837ad7b001a120e91bfa25e07888cae3cc903c39a8b88df439d11e3ccfe4bcbbd9d292ab1fa47b05d51903e68dbb10a4eeaa0ead1f4557b1b835aa4bcf65910de7e2a67483136329c31a3ac776478947f009a5b2cff00b659f8972cebf60d5ff987e0b9371fd3727f4576935cc1a1dd39429732a0b6e4cc94afb492bd4244e65c00614ba35d076344cf14fb6a9081ac2956dc3f8a1f8b878a0f630ea60794891e929db7b9941ba498b53fddb492e63a5dc818889f3462b6093d012e4e419264f1286d74f39f3aca66b2bb2449c18f69dfddfaa214dbc57b80db836d71532f69b52800ee203855ccd1e61a7c825720b47a3320d7fde1fd705f4cfb2c1ffa6db7ff001b7e4be67bcd1e7c015f51f4028e4b0b66ff00ed33fd21300b1ae5cbc25284f094a090d4b0b011e84d5429c4c3cac8cc07d3ebdea70cbca9307a87b41fed541d5b7e2f0be596adf3dbc623930f6511bd6aed07fbb4c1a87fcc29faac0822612374c5c7bde413e374c5c7bde4804f5ae561e87e29d5d47d2d4f5ad811c1cd93e904fc15758ce654ab0ccdab4f2c839dba8278983f0949923ca0d30a74cd4b0db98601c931775b3b5d9779d578ea1d90e66c84d5ae46dbcaf1d2decac9dad06707b92049d24aeb9ba2fab9b421bf34d5bd619248d6141a3544981da71d4725451a24dea829fb75fcd728dd43792e46906e65658a43345158a6500bbc7154dd29e0d94811c1394da7740c755640d3755bbe610f749d37d06e5c4ee3895696b8142b1ba30e6b80dc1f51fd53e37b165d15bab6a378ca543a808d0a3754cf0f450aea901bab3422611c0f1660b4ad6a5b0f7d46546bb9e5805a7940048f12913a851f00c37acebaa668ea69e703f14b837d00713e89f1ef2113322e20d9708dcb63f5eabeb2c1e9e4a54d9f85ad1e8217cb76b473dc5bb3f156a6cfe2a8d0bea6b63a261422d2bc7149a45738a509eb52d36d4b58c8e2a3b8a90e511e51466627edff0010cd736d401fddd173cf8d67c0f852f8acb82b2fb4bc43afc52e9c0cb5b53aa1e1440a663bb335c7cd56e113086a6aafbde49d1ba6ea9d50611ca54a46e8b746ed7356d76631ceff00f23fd4a152668a76017669d76f10e05ae1cc448f8b424cabe0ebe8cbb2eb6d532e9c1357b66d9042f2d2b6725c4437877a76e5dd9d4e9f15e5a5e032fb23ddd400083b21f644bdcea8369803c126eaf03e990d075d145a2f75221acd4f2f9a748cddbb0a75a3bd728bf6e77fca2b91a40b5f40da4a6d2d972e5d6545b53cd5e2e40c794774d7483dca7e27e4178b9343fb025d01e96de4835efd572e5d0c9209f457deb8ff00a5abf362f07bdebf45cb92c42c25d1cff8db4ffa9a5fea0be97a0b97261593a82f5cb972013d625af57206425ea23b75cb914067ca38effc5dcffd456ffbae510ae5c8846d46a9ef1f11f20b9720c28234f829161fbf6f8bbfd2e5cb90cbfd1ffa022ecdf71abaf76f25cb97968cc076db79fd538efdf0f0fa2e5cb7d8c495cb972518ffd9, '', '', 'Admin', 'subAdmin', '', 0, NULL, 'India', '+91', 0, 'n', 'noState', '2024-09-03 10:19:24'),
(76, 2, 'Sam', 'sam@gmail.com', 'Male', NULL, 'sam', '', '', 'stdrytguyihj', '2024-07-13 19:31:51', '', '', '', '', NULL, 0, '', '', '', 'Admin', 'subAdmin', '', 0, NULL, 'India', '+91', 0, 'n', 'noState', '2024-09-03 10:19:20'),
(77, 1, 'taj', 'taj@gmail.com', 'Male', NULL, 'taj', '5645646', '', '', '2024-07-15 12:00:26', '', '', '', '', NULL, 1, '', '', '', 'Admin', 'subAdmin', '', 0, NULL, 'Japan', '+91', 834003, 'Zfddfsdfs', 'Dohra', '2024-08-16 10:36:03'),
(78, 1, 'Rugma Admin', 'rugmar91@gmail.com', 'Female', '2024-07-22', 'abcd', '64646456', 'Dubai', 'https://google.com', '2024-07-17 21:43:38', 'sgdrg6456756dgrt', 'PROFX MEDIA LLC', 0x4475626169, '456465677', NULL, 1, '', '', '', 'Super admin', 'Super admin', 'af70cbaa1050694e4ad5cec0521927cb', 0, NULL, 'United Arab Emirates', NULL, 834003, 'Zexu', 'Dubayy [Dubai]', '2024-09-17 10:29:14'),
(79, 1, 'BFX', 'admin@bridgingfx.net', 'Male', '2022-09-19', 'BFX@1234', '0585948900', 'Dubai', 'bridgingfx.net', '2020-12-14 00:00:00', '0149B3B2927010FB', 'PROFX MEDIA LLC', 0x4b656d7020486f75736520313630204369747920526f6164204c6f6e646f6e20e2809320556e69746564204b696e67646f6d204543315620324e58, '', 'ALPHABULLFX', 1, 0xffd8ffe000104a46494600010102007600760000ffe100624578696600004d4d002a000000080005011200030000000100010000011a0005000000010000004a011b000500000001000000520128000300000001000300000213000300000001000100000000000000000076000000010000007600000001ffdb004300030202020202030202020303030304060404040404080606050609080a0a090809090a0c0f0c0a0b0e0b09090d110d0e0f101011100a0c12131210130f101010ffdb00430103030304030408040408100b090b1010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010ffc0001108004201fd03011100021101031101ffc4001c0001010003010101010000000000000000000102070806050403ffc400441000010303010406040a080603000000000100020304051106071221310813415161811422377115164274758292a1b1b32324323335527291254362a2b2f0344473ffc4001c0101000301010101010000000000000000000506070403010208ffc400401100020003050309070302050501000000000102030405061121317181a112142241516191b1c11335427282d1e13334f03252071523a2b225266292c2d2ffda000c03010002110311003f00eefdaa6dbaf9a07551b05becf435310a68e6df98bf7b2ecf0e071d8af177eead3db147ce66471278b5961d454ad9bc33acca9f610409ac13cf1eb3de6cef5e5bb683a762bc51eec550cfd1d5d3e7261971c478b4f307bbc4155eb6ac89b63553911e6b585f6afbf6937655a52ed4a753a0c9f5aec7fcd0f50a2092080f13b5bd775fb3cd3305f2dd454f552cb5aca62c98bb7435cc7bb3c08e3ea0feea7aeed932ed9ab74f36270a50b797734baf690f6dda51d974ea74b49b6d2cf63fb1764daeabf685a625be5c68a9e9658eb1f4c190976ee1ad61cf1278fac52f15932ec6ab54f2a26d3853cfbdbfb0b12d28ed4a673a6249e2d65b17dcf6aa04983f856d6d1db6925afb855454f4d034be4965706b58d1da49e4bd254a8e7c6a5cb4dc4f44b53f1326412a171cc7825d6cd2bab7a4e5ae8a67d2690b39b816923d2aa5c63889ef6b07ace1ef2df72be59d71274d85475b1f27b966f7bd17129f5d7be54b6e0a48395def25e1af91e264e92bb457c9bec8ad11b73fb0da6763ef793f7a9f86e3d96960dc4f7afb10cef6da0de2943e1f93d069ee9477064cc8f5569c825889c3a6a071639a3bf71e4877da0a36b6e0cb70b74735a7d9166bc56187833be96f8cc4f0a9969aed87ecfee8ddfa5f56e9fd636d6dd74f5c19550f2781c1f13bf95ed3c5a7fe8c859fd7d9d53664df63530e0f83ef4facb951d6c8af97ed64458ae2b6a3ec2e23acd1fab36f5a8f47eb7aad3573b0d01a4a5aa6832b4bf7dd4eec383871c6f6e1f765681675d1a5b4acf86ae54c8b94d3cb2c394b2c34d31e0532b6f2d45056c54f3205c94f5cf1c3b76e06ee8e464d1b6589e1ec780e6b81c820f22150224e1783d4b926a258a325f0fa101cf179e93978a5bcd6535aec56e9e861a87c704af73f7a48c3880e3838e2067cd6994d71244c910473a644a36936b2c13c34dc50a7def9b04e8a1970270a6f079e68dfb6cb8535dedb4b75a376f41590b2788f7b5cd047dc56713e4c74f362931eb0b69ed59179933619f2e19b068d26b79fa9791e8101a42fbb7cbf47af67d1da66c94156c15cda086495cfde7c990c713838c6fe7c82bfd25d0a68ace55d5732285f2794d2c34d56bddc4a6d4de59eab9d253409f4b929bc75d3ccddc338e3cd500b91092806f140378a028280a8020080200802008020080200802008020080200802008020080200802008020080200802021e08003940540100401004072a7491f6907e6107e2e5b0dc8f75fd4fd0ccaf67bc3e95ea7ccd337bd45b13d6ed15d0bdd04ac67a4c2d3ea54d33b8b5eccf68ce41ec2083da1755752d2de9b3ff00d379ac707d70c4b54fd7bb3ec39a92a2a2efd6f4d64f0c576a7d6bd3c3b4eb4b5dce82f56ea7bb5b2a593d2d546258a46f2734fe07bc76158d5448994b362933561142f068d464ce82a25a9b2de30bcd1fa9789ea6a3e939ecfa93e9687f2a5574b89ef28be47e70955bdffb087e65e51197466f67b53f4a4df9712f97ebde70fc8bce23edd1fd83f99f9236d2a616939736c9afae9b40d56dd17a70c92dbe9aa453451447ff002ea33ba5c7b0807837b3b7b786b9766c79363d1f3faaca36b16dfc30eb86dedf0335b7ad39b69d5733a7ce14f0497c4fb7ede26d0d9eec134c699a48ab352524377bab8073fae6efc111fe5630f0763f99c0f2e18552b66f755d746e0a589cb97dd944fbdbead8b8964b2eed535240a3a84a38fbf45b17abe06cd868e929e2f47829618e2031b8c600dc7b82a9c53238e2e544db658e197042b930ac11e53576c9b446b1a77b2b6cd0d2d5387a95948c114ad3de481877b9c0a99b3af15a166c49cb98dc3fdaf35f8dd81195d6251d7c2d47025176ac9fe779ceaf6eacd82ebd004bd6c630ee1911575313da3b0f3f16b876f6e9c9d1deeb3b4c1f1862fe78a282d555daaed715c2287f9e0ceadb25e287505a292f76d937e9ab616cd19edc11c8f711c88ef0563b554d328e7c54f3561142f0669d4f3e0aa950ce96f289628d13d2874c6e4f6ad5f047c240682a481f2865d19f31be3c82d0ae157e30cca289e9d25e4fd0a55f1a3c2297570f5f45f9af53626c37537c65d9e5075b26f54db3341364f1f500dc3f60b3cf2ab37ae8398da71e0ba31f496fd78e24f5ddace7741063ac3d17bb4e181b0156c9d3c86d6352fc55d0376b94726e544909a6a720f1eb24f5411e2012efaaa6aef50ff985a52a5359278bd8b3e3a6f22adbabe65433262d70c16d7970d4e58b6e88acb8e81bbeb766f7576cac829c340e0e6b81eb0f917c5e44ad827dab049b4a559ef58e16fc34f1c22e06672ace8e6d0ccac5a42d2fbf8628e82e8e9a97e1ad062d5349bd3d9a6753904f1ea9deb30fdee68fe959a5f5a1e6b68fb685653163bd64fd1ef2f7756af9c50fb27ac0f0dcf35eab71b51540b31f0b5c6a26693d2574d40e203a929dce881e4653eac63cdc5a14859544ed1ad974cbe279ecd5f0c4e2b46a95152c73df52cb6f57139e7a39e9d92fbaea6d43560c91da2274c5eee399e4cb5b9f2df77bc05a65f5ad54967aa683271bc3e959bf45bca15d5a57535aea23cd40b1def4f56751ac8cd28c7b501703b900c0ee40423b50141e080a80990100c84032806728067080202a0080990500ce100ce500c8403210150132100c840540100404c84032101500404c8280640403210150040424610101c202e7280a80990806414054072a7491f6907e6107e2e5b0dc8f75fd4fd0ccaf67bc3e95ea6e2da3ecd60da0e89a2348c632f1434ac928e43c37fd41989c7b9dd9dc707bf347b12dc8ac6b423e5fe9c4df297667aaef5c56e2d96ad930da9470f27f5214b0f0d37f99ab7619b4a9f475e1fa23533df0d054ce638ccdc3d0ea73820e7935c781ee3c787156fbd961c369c856852671a58bc3e287eebabb565d856aee5ad1504e74553942df5fc2fecfafb1ef3a6d64e68c6a3e939ecfa93e9687f2a5574b89ef28be47e70955bdff00b087e65e51197466f67b53f4a4df9712f97ebde70fc8bce23edd1fd83f99f923dc6d12f5269ed0f7bbc40fdc9a0a37889dfcb2386eb0fda7055fb169556da126445a38963b166f8226ad4a874b45366ad52786d792e2687e8c9a7e1b8eabafbfd4461ff04d335b1647eccb29203becb5e3cd6897eeb22934705342ff00ade7b21fcb5e0526e852a9b551cf8be0596d7f84ce9a5941a2840101a9ba4969e86e5a15b7c118f48b45431e1f8e3d5c8431cdfb4587eaab95c8ac8a45a3cdf1ca627e2b35c31f12af7b29619b45edbae06bc1e4fd0fcdd192f525768faeb34afdef832b3318fe58e41bc07da0f3e6bdafdd2a955d04f87e3873dab2f2c0f3ba150e6524525fc2f83fce27b9da7699f8dda1aeb66647bf50e84cd4dc38f5ccf59a07bc8ddf712abd6157ff00975a12a7b796383d8f27e1aee26ad7a3e7d45324ad70c56d59afb1a3fa33ea6f83b55566999e4c4576837e204ff9d164e07bd85ff642d02fd50fb7a382ae159c0f3d8ff387894cba359ecaa62a78b48d65b57e313a6565068a73d74a2d4bd6555a7494127089a6bea003f28e591f9801ff00682d2ee150e104dad896bd15e6fd3c0a1df1abc62974ababa4fc97af89b1b43683a7a7d91d3e92ad8c31f73a07baa491c5b24c0bb27c5b968faa155ed5b5e28eda8ab65bca08961b21cb8fa960b3acd860b2952c7f142f1db17dbd0d35b00bd54696da3cfa6ae19885c1b2514ac27836a2324b73e3c1edfacaf57c2961b42cb5572b3e46112f95ebe8f7151bb351151da0e9e665cac53dab4f55bcea45911a51a2fa506a6ea2dd6bd2504987d53cd6d40078ee372d603e05c5c7ea05a15c2a0e5cd995b12ca1e8adaf37e0b0f12977c2b3932e0a587af37b164b8e3e07a9e8fda67e00d9f53d6cd1eed4de24358fc8e3d59e118f76e8defac543df1afe7969452e17d196b93bf57c72dc495d8a3e6b40a37ac79eeeae19ef365aaa96231edf34064802021e4808d4052708080650177420272e08035014f24046a032404772401bc9007724041cb0100dd4008c201938c76a01ba801184050721015018f1280bba8084610141ca007920203840319e2500210069ec4053c50108c76a000650140c200e28080650023080a39203957a48fb483f3083f172d86e47bafea7e86657b3de1f4af53a7ecffc2287e6d17fc42c96a7f5e3dafccd2247e943b1791a4fa426cb7d263935f5829bf4b137fc4a160fdb68e5301de393bc307b0abedcdb7fd9b566d4bc9ff43eff00eddfd5df97614ebd16372d3ae90b35fd4bd7efe3da7d8d826d4be32db9ba46f9539bad0c7fabc8f3c6a611e3daf68e7de307b095c57bec0e63379ed3aff4e279afed7f67c1e5d8755dab679dcbe6b39f4e1d3bd7dd796f3fa749cf67d49f4b43f952afc5c4f7945f23f384fd5eff00d843f32f288cba337b3da9fa526fcb897cbf5ef387e45e711f6e8fec1fccfc91e836e11c92ecb2fcd8f3911c2ee1dc278c9fb8151b755a86d892df6bff008b3bef126ecc9b87779a35ef455963eab52c391bfbd48ef78c4bff007cd597fc4185f2a9e2eae97ff240dcc6b09cbe5f537e2ce4bc040101e0f6e724716caefa6423d6640d1e24cf1e158ae9c2e2b624e1dfff0016425e369599371eeff923c074558e414da96539dc73e91a3de04b9fc42b1ff88312e5d3c3d7845ffc9077313e4ce7f2fa9bed6745dce44d774551b31daecb5d411964705632e54ad1c03a279de2c1e19df6792daac99b0dbd6229731e6e1703dab2c7c9995da52e2b1ed571c1a26a25b1e7879a3ad28ab69ae14305c69650fa7a989b3c6fec2c70041fec563336547266452a359a6d3da8d465cc866c0a642f26b15b0e50693b58db582732d2565c33e1e890fe198d9fdcad8dff00dbd6076450c3fee8bf2fc11982ff00addb1db0b8bfdabf08eb6e4b1835239536d96da9d15b561a82dc3abf4a7c575a77760943bd6ff7b093fd4b61bad3e0b52c7e6d373e4e303d9d5c1e1b8ccaf0c98acfb4fdbcbebc225b7af8ac779d3f67ba535eed34778a33982b608ea23fe9734103dfc564b534f1d2ce8e447ac2da7b8d2244e86a2543360d2249f89ca3ac6a67da96d824a2a390ba1a9ad65be9dcde21b030ee978f0c073fcd6c5664b86c0b114c98b350b89ed79e1e48cc2be38ad9b59c1068df256c5d7e6ceb5a5a6828a961a3a58c470c11b628d83935ad1803fb058ccc9914d8dcc8de2dbc5ef352820865c2a0874591fd57e0fd18f6a01bde0806f78201c4a028184047734054054043c9011a80c9018f2280c90189e250192023b9200d4054043c9011a80c9010f24046a02bb9202020202ef0403782020e680a792020e680c900406239a0324043c9011a80c90108ca02038405c8280a80e54e923ed20fcc20fc5cb61b91eebfa9fa1995ecf787d2bd4e9fb3ff0008a1f9b45ff10b25a9fd78f6bf334891fa50ec5e47ea7b19231d1c8c0e6b810e6919041ec2bc5370bc51e8d26b0672bed5f41dc7657aae9f546987c905ba79faea3959ff00ad30e2623e1cf19e6dc8e382b5fbbd6bcabc14715255e71a5844bfb976fdfb1e7d66676d59b32c6aa5534d940de29f63ecfb77647a2da8ebfa1da16c6edf7483763ac86eb0455b4e0feea5ea65e23fd27983e5cc15196058f32c6b723931670b81b85f6ae543c575fe4efb66d382d4b2209ab28944935d8f08b83ea3d77466f67b53f4a4df971285bf5ef387e45e7112b747f60fe67e48d91a92cd16a2d3f71b14c406d7d2c94fbc7e4973480ef2383e4aad4352e8aa65d443f0b4fc1961aba755522390fe24d789cc5b16d48ed9fed165b4dfbf568aacbadb55be7021983bd527dce1bb9e403895acde8a156c596a75366e1c225deb0cf867b8ce2efd5ff95da0e54fc93e8bee78e5c72de757ac70d3c20080d13d26b5953c74147a228e60ea89646d5d6069fd8600771a7c493bd8ff0048ef5a1dc5b3227323b4235925c987bdf5bdda6f7d852af757c2a0868e079bcdecea5bf5dc7abe8fda625d3db3f86aaaa32ca8bbca6b483cc4640118f368defaca1af8d7c35b693820794b5c9dfabe396e24eec51ba5a15145ac6f1ddd5c33de6cb5552c468ee93fa63d26d36dd5904797d148692a081fe5bf8b09f00e047d75a05c3afe44e994513ca25ca5b56be2bc8a65f0a3e5ca82aa1d61c9ec7a783f33e6e99da5fa26c02e313aa3170b71369878fac44bfbb70fe9617e3ff9aeaaeb0fda5e496d2e847d37f4eab7bc3ff639e92d7e45851ac7a50f416fd3c163e07f3e8bba73adaebb6ab9a3f560636860247ca761cff30033ed15fabfb5bc9972a8e17af49eec97af81f9b9d498c732a9f57456fcdfa789d0eb332fa69de931a6fe11d2349a8a18f32da6a376438e50cb869ff788ff00b9577b8b5bec6b62a689e51acb6c39f96254ef7527b5a586a16b03e0ff00381e7b406d2fe09d86de58ea8c57d98ba8e978fadfa727aa77938c87dcc5276c587ce2f04a697426749fd3fd5e2b0dece0b32d7f6162cc4df4a0c97d5a7867e07cde8c9a67d3b51d7ea89e3cc76c87a88491fe7499c91ee6070fae1755fbaef654b051c2f38de2f62fbbc3c0e7ba147ed2a22a98b48560b6bfc799d2ab2b343080c7b50170100c04054010189e680a80a80879202350192031777a0283c1011a3b50192023b9200de480a80879202350192021e4808d405772404001405c04030100e1d8801e4808de680c900406239a0324043c9011a80c9013210020140423080a0e501cabd247da41f9841f8b96c3723dd7f53f4332bd9ef0fa57a9d3f67fe1143f368bfe2164b53faf1ed7e669123f4a1d8bc8fd8bc0f53e66a4d3b6bd5765aab0de20eb29aa99ba7f9987b1cd3d8e07042eba1ad9d67cf86a243c2287f983ee67355d2cbad931489ab14ff989c6bad74a5e3425f2af4d5c9cfdc0e1246f190ca88f8ee4807991e07782dcacbb4245ad4f0d5cad747da9f5afe6b93324b428a759b3a2a799f86ba9ff0034cce83e8cdecf6a7e949bf2e259adfaf79c3f22f388bddd1fd83f99f9236d2a616934a6dc763753a8a47eb0d2b4fd65c4347a652379d400301ecef780304768031c471bedd4bcd0d1254358f083e17d9dcfbbbfab6694fbc560c554dd5d2ae975aedef5dfe7b75f1fa0fa415f74940cb06adb74d72a7a5fd13642edca9840e1baedee0fc72c1c11dea6ed7b9d4f68c4ea68a2504516786b0bef5869bb15dc44d9b79e750c3ec2aa1e52597fe4bbb3d786d364c3d24366d245d63e4b9c4ec7eedf4b977dc48fbd55a2b916ac2f04a17bfee8b0c37aecf6b17ca5b8f2bab7a4f40ea7929745d9a613386055d70680cf16c6d2727bb27de0a97b3ae1c4a251d7cc587643d7bde1c16f232baf842e170d1c19f6c5f6fcee3ce6cbf6517dda1de46b1d67d79b63e5ebdef9c9eb2bddcf033f23bddddc077894b7af0d3d8d2398d061ed12c32d205f7ec5bdf7c7d8f62ceb52773bacc7918e39eb17e3bf72eee9d631b1b4318d0d6b460003000ee59336dbc59a3a492c1192f87d3e36b2d3d16abd2d73d3d2e3f5da773184f26c838b1de4e0d3e4bbaccac767d64ba95f0be1d7e2b1392be9556d34721fc4b8f571388653574a26b74ae923025065849c0eb19bc064778de70f32b7e87913309ab3cb27dcf07c704634f950632de59e6bbd7f19d87b1dd39f16367969a3923dd9ea62f4c9f871df97d6c1f10ddd6fd55885e5ade7f69cd8d3c93e4ad8b2e2f17bcd66c2a4e67412e07ab58bdaf3f2c11ed14112e7ccd4f64875269eb8d867c065753490e4fc9711eabbc8e0f92eba0aa8a86a65d4c3ac2d3fbade735653aab911c88be24d1c3938aca17545b273245b92e268b3c3ac66f0e23bc65c3ccafe8183d9cd50ce873cb27dcf0f3c8c662e5cbc65bcb3cd77a3aef629a67e2c6cf2dd0cb1ee54d7835f3f0e3bd2005a0f886060f782b14bd15fcfed399127d187a2b76bc71355bbf47cce8204f58ba4f7e9c303ddaaf1341018f6f9a032401004010108ca02038405de0808788ca00d406480878a0314064060202a023b9200de480a80879202350192021e4808d406480c788280bbc1010bbb90068ed4053c9011bcd01920080c47340648087920235019203170ed4003bbd0025000101e4754ec9f446b2ba7c337fb74d355756d8b79b50f60dd6e71c1a71da54dd9f786becc93ec29a24a1c71d13d76913596251d7cdf6d3e1c62d3568f590431d3c31d3c430c89a18d19ce00180a1a389c71389eac9486150250ad11fd17e4fd04079dd5fa034aeba8e9d9a92dde90695c4c4f6c8e8dedcf319690707870f052766db15964b89d2c5872b5c935c4e0aeb329ad14954438e1a75791fa34a690b168ab63ad1a7a99f0533e674e5af91cf3be4004e5c49e4d0bcad0b4aa2d49bedea5e3161868965bb69fba2a19167cbf652160b1c75c4fb4b84ec080f33aa366da2b58b8cb7eb0c12d4118f488f31cbe6f6e09f71c852b416e57d99953cc69763cd783f423ab2c9a3afce7c09bedd1f8a3c549d19767af937db5b7b8db9fd86d4c78fbe327ef53d0dfab4d2c1c303dcfff00d10eee8d0378a8a25bd7d8f41a7b627b39d39332a69ec42aea1872d96b5e66c1efdd3eae7c77546d6de9b52b6170453392bb21cb8ebc4efa5bbd67d23e543062fb5e7c34e07ba00000018015789a2a0080203c15cf61fb38bbdd2a6f15b6691d535733a794b6a646b5cf71c93ba0e389562917aad4a6930c89733a30ac164b45df81093aeed9f3e6b9b1c19b78bcd9ef000d01ad00003000ec55d6f1cd937a150040783ba6c4367378ba54de2bacd2baa6ae674f316d4c8d6b9ee392700e389562a7bd56a53498644b99d18560b25a2dc424ebbd67cf9b14d8e0cdbc5e6f53ddb5ad634318d0d6b46000300055e6db78b26d2c32455f00404c04054010040100404201403742008063080a8020311c4a03240101319403184054010131840540101318405401013742018080a802026004054010130101500404c6101500404c04030101500401004010040100401004010040100401004010040100401004010040100401004010040100401004010109401bc9015004010040100401004010040100401004010040100401004010040100401004010040100401004010040100401004010040100401004010040100401004010040100401004010040100401004044054010040100401004010040100401004010040100401004010040100401004010040100407ffd9, '1000', 'admin', 'Super admin', 'admin', 'B202E2832', 1, '2019-02-02 07:00:00', 'United Arab Emirates', '+44', 1234, 'Dubai', 'Dubai', '2024-08-16 10:36:03'),
(80, 1, 'Jalel', 'jalel@lqhmarkets.com', '0', NULL, 'LQH@1234', '000000000', NULL, NULL, '2024-09-04 05:03:06', '', 'LQH MARKETS', NULL, NULL, NULL, 1, '', '', '', 'Super Admin', 'subAdmin', '', 0, NULL, '', NULL, NULL, NULL, NULL, '2024-09-04 05:03:06'),
(81, 1, 'PAtel', 'patel@lqhmarkets.com', '0', NULL, 'LQH@1234', '0000000', NULL, NULL, '2024-09-04 05:03:34', '', 'LQH MARKETS', NULL, NULL, NULL, 1, '', '', '', 'Super Admin', 'subAdmin', '', 0, NULL, '', NULL, NULL, NULL, NULL, '2024-09-04 05:03:34'),
(82, 6, 'aG', 'ag@lqhmarkets.com', '0', NULL, 'KF*$$*$*###3333', '1234567890', NULL, NULL, '2024-09-17 08:19:03', '', 'ag', NULL, NULL, NULL, 1, '', '', '', 'Knowers', 'subAdmin', '', 0, NULL, '', NULL, NULL, NULL, NULL, '2024-09-17 08:22:55');

-- --------------------------------------------------------

--
-- Table structure for table `help_desk`
--

CREATE TABLE `help_desk` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `priority` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_date_js` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int NOT NULL DEFAULT '0',
  `AdminRemark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Js_Admin_Remark_Date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ib1`
--

CREATE TABLE `ib1` (
  `indexId` bigint NOT NULL,
  `acc_type` int DEFAULT NULL,
  `uid` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `website` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Zara FX',
  `company_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Zara FX',
  `dob` date DEFAULT NULL,
  `profile_pic` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accountCurrencyBase` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `emailToken` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zipcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib_ref_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noCode',
  `kyc_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_frontside` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `front_image` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_backside` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `back_image` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registered_date_js` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Admin_Remark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Admin_Remark_Date` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_status` int DEFAULT '0',
  `bank_detail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_holder_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_account_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_branch` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `IFSC_Code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ib1` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib3` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib4` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib5` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib6` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib7` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib8` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib9` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib10` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib11` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib12` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib13` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib14` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib15` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib_wallet` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM AVG_ROW_LENGTH=290 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ib1`
--

INSERT INTO `ib1` (`indexId`, `acc_type`, `uid`, `name`, `email`, `number`, `username`, `country`, `password`, `status`, `website`, `company_name`, `dob`, `profile_pic`, `accountCurrencyBase`, `address`, `email_confirmed`, `emailToken`, `state`, `zipcode`, `city`, `ib_ref_code`, `kyc_type`, `kyc_frontside`, `front_image`, `kyc_backside`, `back_image`, `registered_date_js`, `Admin_Remark`, `Admin_Remark_Date`, `kyc_status`, `bank_detail`, `account_holder_name`, `bank_name`, `bank_account_no`, `bank_branch`, `IFSC_Code`, `bank_status`, `reg_date`, `ib1`, `ib2`, `ib3`, `ib4`, `ib5`, `ib6`, `ib7`, `ib8`, `ib9`, `ib10`, `ib11`, `ib12`, `ib13`, `ib14`, `ib15`, `ib_wallet`) VALUES
(1, 1, '66d8c9c9c4314', 'Syed Mohamed Rafi Babu', 'syedmohamedrafi@gmail.com', '+9710585301312', 'syedmohamedrafi@gmail.com', NULL, 'test1234', 1, 'Zara FX', 'Zara FX', NULL, NULL, 'USD', NULL, 0, '79ff95834f4b5c9a924ac8bcac8ec2c6', NULL, NULL, NULL, 'noCode', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-04 20:59:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, '66db024ae857a', 'Rugma Ramanathan', 'rugmar91@gmail.com', '+35812345678', 'rugmar91@gmail.com', NULL, 'abcd', 1, 'Zara FX', 'Zara FX', NULL, NULL, 'USD', NULL, 0, '8f8a79eefa0ca01067bf02a76e53ed39', NULL, NULL, NULL, 'noCode', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-06 13:23:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 1, '66db24d2e316d', 'Rugma R', 'rugmaramanathan@gmail.com', '+3551234567', 'rugmaramanathan@gmail.com', NULL, 'abcd', 1, 'Zara FX', 'Zara FX', NULL, NULL, 'USD', NULL, 0, 'aaf72e39ec252d3ba73d9f0f7fcfb5e0', NULL, NULL, NULL, 'noCode', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-06 15:51:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, '66db38d826f52', 'Mega Stand', 'megastand@protonmail.com', '+91123456789', 'megastand@protonmail.com', NULL, 'megastand@protonmail.com', 1, 'Zara FX', 'Zara FX', NULL, NULL, 'USD', NULL, 0, '93cf9a35021fa85d19b1fec92a816433', NULL, NULL, NULL, 'noCode', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-10 05:30:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 1, '66dc9ee84749a', 'Jalel Abougouche', 'jalelwabou@gmail.com', '+17805200055', 'jalelwabou@gmail.com', NULL, 'Lala2017!', 1, 'Zara FX', 'Zara FX', NULL, NULL, 'USD', NULL, 0, 'cc6e5991fa0523e5969023dede2f8214', NULL, NULL, NULL, 'noCode', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-07 18:45:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 1, '66ec63a391aea', 'Jay Abou', 'dawood@lqhmarkets.com', '+17805200055', 'dawood@lqhmarkets.com', NULL, '1234', 1, 'Zara FX', 'Zara FX', NULL, NULL, 'USD', NULL, 0, '3883dec2d68926f26c4c7b2dbd9ac320', NULL, NULL, NULL, 'noCode', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-19 18:06:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 1, '66f068509eee0', 'LQH Markets', 'lqhmarkets@gmail.com', '+971123456890', 'lqhmarkets@gmail.com', NULL, 'lqhmarkets@gmail.com', 1, 'Zara FX', 'Zara FX', NULL, NULL, 'USD', NULL, 0, 'b7a0449c15e0581295ebf3065b6dc8ef', NULL, NULL, NULL, 'noCode', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-22 18:56:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, NULL, '66ffbbca17f14', 'Muthu Venkatesh', 'muthuvenkatesh808@gmail.com', '+971529938041', 'muthuvenkatesh808@gmail.com', NULL, '5700d$A4', 0, 'Zara FX', 'Zara FX', NULL, NULL, 'USD', NULL, 0, '3bbc968fff09b1f91b03bc592b3d635b', NULL, NULL, NULL, 'noCode', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-04 09:56:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ib1_commission`
--

CREATE TABLE `ib1_commission` (
  `id` int NOT NULL,
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volume` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `time_closed` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ib1_commission`
--

INSERT INTO `ib1_commission` (`id`, `user_id`, `order_id`, `login`, `volume`, `time_closed`, `status`, `created_at`, `updated_at`) VALUES
(1, 'syedmohamedrafi@gmail.com', '36', '747204', '0.001', '2024-09-05 08:57:24', 0, '2024-09-05 18:26:34', '2024-09-05 18:26:34'),
(2, 'syedmohamedrafi@gmail.com', '37', '747204', '0.025', '2024-09-05 08:57:32', 0, '2024-09-05 18:26:34', '2024-09-05 18:26:34'),
(35, 'lqhmarkets@gmail.com', '1511', '235001', '0.001', '2024-09-27 01:21:55', 0, '2024-09-27 00:22:42', '2024-09-27 00:22:42'),
(36, 'lqhmarkets@gmail.com', '1512', '235001', '0.001', '2024-09-27 01:22:03', 0, '2024-09-27 00:22:42', '2024-09-27 00:22:42'),
(45, 'lqhmarkets@gmail.com', '1579', '235001', '0.001', '2024-09-27 10:17:17', 0, '2024-09-27 09:17:21', '2024-09-27 09:17:21'),
(88, 'abougouche22@gmail.com', '2248', '493374', '0.1', '2024-10-01 16:16:32', 0, '2024-10-01 15:16:36', '2024-10-01 15:16:36'),
(103, 'abougouche22@gmail.com', '2249', '493374', '0.1', '2024-10-01 16:16:46', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(104, 'abougouche22@gmail.com', '2253', '493374', '0.1', '2024-10-01 16:20:32', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(105, 'abougouche22@gmail.com', '2254', '493374', '0.1', '2024-10-01 16:20:59', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(106, 'abougouche22@gmail.com', '2255', '493374', '0.1', '2024-10-01 16:24:43', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(107, 'abougouche22@gmail.com', '2256', '493374', '0.1', '2024-10-01 16:24:58', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(108, 'abougouche22@gmail.com', '2259', '493374', '0.1', '2024-10-01 16:33:35', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(109, 'abougouche22@gmail.com', '2260', '493374', '0.1', '2024-10-01 16:33:42', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(110, 'abougouche22@gmail.com', '2270', '493374', '0.1', '2024-10-01 16:41:33', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(111, 'abougouche22@gmail.com', '2287', '493374', '0.1', '2024-10-01 16:53:24', 0, '2024-10-01 17:20:52', '2024-10-01 17:20:52'),
(114, 'jalelwabou@gmail.com', '3042', '165718', '0.1', '2024-10-04 11:15:58', 0, '2024-10-05 17:02:14', '2024-10-05 17:02:14'),
(115, 'jalelwabou@gmail.com', '3043', '165718', '0.1', '2024-10-04 11:16:08', 0, '2024-10-05 17:02:14', '2024-10-05 17:02:14'),
(126, 'muthuvenkatesh808@gmail.com', '3327', '165718', '0.1', '2024-10-07 09:09:13', 0, '2024-10-08 18:22:49', '2024-10-08 18:22:49'),
(127, 'muthuvenkatesh808@gmail.com', '3328', '165718', '0.1', '2024-10-07 09:09:16', 0, '2024-10-08 18:22:49', '2024-10-08 18:22:49'),
(128, 'muthuvenkatesh808@gmail.com', '3329', '165718', '0.1', '2024-10-07 09:09:16', 0, '2024-10-08 18:22:49', '2024-10-08 18:22:49'),
(129, 'muthuvenkatesh808@gmail.com', '3330', '165718', '0.1', '2024-10-07 09:09:17', 0, '2024-10-08 18:22:49', '2024-10-08 18:22:49');

-- --------------------------------------------------------

--
-- Table structure for table `ib1_withdraw`
--

CREATE TABLE `ib1_withdraw` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int NOT NULL DEFAULT '0',
  `AdminRemark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Js_Admin_Remark_Date` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ib_categories`
--

CREATE TABLE `ib_categories` (
  `ib_cat_id` int NOT NULL,
  `ib_cat_name` varchar(255) NOT NULL,
  `ib_cat_type` varchar(100) NOT NULL DEFAULT 'ib',
  `ib_cat_desc` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ib_categories`
--

INSERT INTO `ib_categories` (`ib_cat_id`, `ib_cat_name`, `ib_cat_type`, `ib_cat_desc`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'STP LIVE', 'ib', 'STP LIVE', 1, '2024-09-04 22:54:04', '2024-09-04 22:54:04'),
(2, 'Default', 'ib', '', 1, '2024-10-02 05:08:22', '2024-10-02 05:08:22');

-- --------------------------------------------------------

--
-- Stand-in structure for view `ib_client_list`
-- (See below for the actual view)
--
CREATE TABLE `ib_client_list` (
`aboutme` mediumtext
,`access_count_failed` int
,`account_holder_name` varchar(100)
,`address` mediumtext
,`annual_income` int
,`bank_account_no` varchar(100)
,`bank_detail` varchar(100)
,`bank_name` varchar(100)
,`bank_status` int
,`bankruptcy` varchar(5)
,`byPartner` tinyint(1)
,`cfd` varchar(100)
,`city` varchar(50)
,`client_status` int
,`country` varchar(50)
,`created_at` datetime
,`date` datetime
,`dial_code` varchar(20)
,`dob` varchar(20)
,`education` varchar(150)
,`email` varchar(50)
,`email_confirmed` tinyint(1)
,`email_token_time` datetime
,`emailToken` varchar(200)
,`employee_status` varchar(100)
,`employemnet_status` int
,`financial_industry` varchar(150)
,`forex_exp` varchar(20)
,`fullname` varchar(150)
,`funds_source` varchar(15)
,`gender` varchar(50)
,`ib1` varchar(150)
,`ib10` varchar(255)
,`ib11` varchar(255)
,`ib12` varchar(255)
,`ib13` varchar(255)
,`ib14` varchar(255)
,`ib15` varchar(255)
,`ib2` varchar(255)
,`ib3` varchar(255)
,`ib4` varchar(255)
,`ib5` varchar(255)
,`ib6` varchar(255)
,`ib7` varchar(255)
,`ib8` varchar(255)
,`ib9` varchar(255)
,`id` int
,`IFSC_Code` varchar(100)
,`imgName` varchar(200)
,`industry` varchar(150)
,`investment_plan` varchar(10)
,`investment_purpose` varchar(20)
,`Isreferal` tinyint(1)
,`kyc_back` varchar(100)
,`kyc_front` varchar(100)
,`kyc_type` varchar(100)
,`kyc_verify` int
,`lang` varchar(50)
,`liveaccounts` bigint
,`lockout_enabled` tinyint(1)
,`lockout_end_date` datetime
,`mail_otp` varchar(50)
,`monthly_transaction` varchar(10)
,`number` varchar(100)
,`number_confirmed` tinyint(1)
,`other` varchar(100)
,`password` varchar(100)
,`personal_status` int
,`polotically_person` varchar(5)
,`profile_image` varchar(50)
,`referalId` varchar(150)
,`referral` varchar(50)
,`reg_date` timestamp
,`state` varchar(50)
,`status` int
,`swift_code` varchar(100)
,`total_deposit` double
,`total_value` varchar(20)
,`trading_status` int
,`two_factor_enabled` tinyint(1)
,`uid` varchar(50)
,`updated_at` datetime
,`usa_resident` varchar(5)
,`usa_tax` varchar(5)
,`username` varchar(150)
,`wallet_address` varchar(100)
,`wallet_approved_at` datetime
,`wallet_enabled` int
,`wallet_requested` int
,`wallet_requested_at` datetime
,`zipcode` varchar(20)
);

-- --------------------------------------------------------

--
-- Table structure for table `ib_commessions_report`
--

CREATE TABLE `ib_commessions_report` (
  `indexId` bigint NOT NULL,
  `ibType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibId` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commession` double(12,8) NOT NULL DEFAULT '0.00000000',
  `date` datetime DEFAULT NULL,
  `login` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'noLogin',
  `orderID` bigint DEFAULT '-1',
  `lot` double(10,4) NOT NULL DEFAULT '0.0000',
  `conversionRate` double(10,4) NOT NULL DEFAULT '1.0000',
  `lotConversion` double(10,4) NOT NULL DEFAULT '0.0000',
  `positionID` bigint NOT NULL DEFAULT '-1',
  `symbol` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noSymbol',
  `HedgeFull` double(10,4) NOT NULL DEFAULT '0.0000',
  `HedgeHalf` double(10,4) NOT NULL DEFAULT '0.0000',
  `openTime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noTime',
  `closeTime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noTime'
) ENGINE=MyISAM AVG_ROW_LENGTH=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ib_internal`
--

CREATE TABLE `ib_internal` (
  `id` int NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transfer_to` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'IB Wallet',
  `transfer_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ib_plans`
--

CREATE TABLE `ib_plans` (
  `ib_plan_id` int NOT NULL,
  `ib_plan_cat_id` int NOT NULL,
  `ib_acc_type_id` int NOT NULL,
  `ib_commission1` decimal(10,2) DEFAULT NULL,
  `ib_commission2` decimal(10,2) DEFAULT NULL,
  `ib_commission3` decimal(10,2) DEFAULT NULL,
  `ib_commission4` decimal(10,2) DEFAULT NULL,
  `ib_commission5` decimal(10,2) DEFAULT NULL,
  `ib_commission6` decimal(10,2) DEFAULT NULL,
  `ib_commission7` decimal(10,2) DEFAULT NULL,
  `ib_commission8` decimal(10,2) DEFAULT NULL,
  `ib_commission9` decimal(10,2) DEFAULT NULL,
  `ib_commission10` decimal(10,2) DEFAULT NULL,
  `ib_commission11` decimal(10,2) DEFAULT NULL,
  `ib_commission12` decimal(10,2) DEFAULT NULL,
  `ib_commission13` decimal(10,2) DEFAULT NULL,
  `ib_commission14` decimal(10,2) DEFAULT NULL,
  `ib_commission15` decimal(10,2) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `updated_by` varchar(255) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `unique_c` int GENERATED ALWAYS AS (if((`status` = 1),`status`,NULL)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ib_plans`
--

INSERT INTO `ib_plans` (`ib_plan_id`, `ib_plan_cat_id`, `ib_acc_type_id`, `ib_commission1`, `ib_commission2`, `ib_commission3`, `ib_commission4`, `ib_commission5`, `ib_commission6`, `ib_commission7`, `ib_commission8`, `ib_commission9`, `ib_commission10`, `ib_commission11`, `ib_commission12`, `ib_commission13`, `ib_commission14`, `ib_commission15`, `status`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 45, '10.00', '8.00', '6.00', '4.00', '2.00', '1.00', '1.00', '1.00', '1.00', '1.00', '1.00', '1.00', '1.00', '1.00', '1.00', 1, 'admin@bridgingfx.net', NULL, '2024-09-04 22:58:40', '2024-09-04 22:58:40');

-- --------------------------------------------------------

--
-- Table structure for table `ib_plan_details`
--

CREATE TABLE `ib_plan_details` (
  `id` int NOT NULL,
  `ib_plan_id` int NOT NULL,
  `acc_type` int NOT NULL,
  `level_id` int NOT NULL,
  `d1` decimal(10,2) DEFAULT '0.00',
  `d2` decimal(10,2) DEFAULT '0.00',
  `d3` decimal(10,2) DEFAULT '0.00',
  `d4` double(10,2) DEFAULT '0.00',
  `d5` decimal(10,2) DEFAULT '0.00',
  `d6` decimal(10,2) DEFAULT '0.00',
  `d7` decimal(10,2) DEFAULT '0.00',
  `d8` decimal(10,2) DEFAULT '0.00',
  `d9` decimal(10,2) DEFAULT '0.00',
  `d10` decimal(10,2) DEFAULT '0.00',
  `d11` decimal(10,2) DEFAULT '0.00',
  `d12` decimal(10,2) DEFAULT '0.00',
  `d13` decimal(10,2) DEFAULT '0.00',
  `d14` decimal(10,2) DEFAULT '0.00',
  `d15` decimal(10,2) DEFAULT '0.00',
  `status` tinyint NOT NULL DEFAULT '1',
  `updated_by` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ib_plan_details`
--

INSERT INTO `ib_plan_details` (`id`, `ib_plan_id`, `acc_type`, `level_id`, `d1`, `d2`, `d3`, `d4`, `d5`, `d6`, `d7`, `d8`, `d9`, `d10`, `d11`, `d12`, `d13`, `d14`, `d15`, `status`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 46, 1, '10.00', '0.00', '0.00', 0.00, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 1, 'jalel@lqhmarkets.com', '2024-10-01 15:20:18', '2024-10-01 15:20:18', NULL),
(2, 2, 62, 1, '6.00', '0.00', '0.00', 0.00, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 1, 'jalel@lqhmarkets.com', '2024-10-02 05:08:59', '2024-10-02 05:09:16', '2024-10-02 05:09:16'),
(3, 2, 62, 1, '6.00', '0.00', '0.00', 0.00, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 1, 'jalel@lqhmarkets.com', '2024-10-02 05:09:16', '2024-10-02 05:09:16', NULL),
(4, 2, 63, 1, '6.00', '0.00', '0.00', 0.00, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 1, 'jalel@lqhmarkets.com', '2024-10-02 05:09:36', '2024-10-02 05:09:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ib_wallet`
--

CREATE TABLE `ib_wallet` (
  `id` int NOT NULL,
  `ib_wallet` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ib_withdraw` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ib_level` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ib_wallet`
--

INSERT INTO `ib_wallet` (`id`, `ib_wallet`, `ib_withdraw`, `email`, `trade_id`, `order_id`, `remark`, `ib_level`, `reg_date`, `created_at`, `updated_at`) VALUES
(1, '0', NULL, 'jalelwabou@gmail.com', '493374', '2248', 'abougouche22@gmail.com', 'IB 1', '2024-10-01 13:16:36', '2024-10-01 15:16:36', '2024-10-01 15:16:36'),
(2, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2287', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29'),
(3, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2270', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29'),
(4, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2260', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29'),
(5, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2259', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29'),
(6, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2256', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29'),
(7, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2255', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29'),
(8, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2254', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29'),
(9, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2253', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29'),
(10, '0.5', NULL, 'jalelwabou@gmail.com', '493374', '2249', 'abougouche22@gmail.com', 'IB Level1 - D1', '2024-10-01 15:39:29', '2024-10-01 17:39:29', '2024-10-01 17:39:29');

-- --------------------------------------------------------

--
-- Table structure for table `ib_withdraw`
--

CREATE TABLE `ib_withdraw` (
  `w_index` bigint NOT NULL,
  `uid` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `orderId` int DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `gateway` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `amount` double(10,4) NOT NULL DEFAULT '0.0000',
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `ib_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ib_email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `processed_by` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `processed_on` datetime DEFAULT NULL,
  `comment_by_agent` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `withdraw_details` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `cancel_date` datetime DEFAULT NULL,
  `invoice_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invoice_portal` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'admin',
  `upload_date` datetime DEFAULT NULL,
  `uploaded_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_amount` double(8,4) NOT NULL DEFAULT '0.0000'
) ENGINE=MyISAM AVG_ROW_LENGTH=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internaltransfer`
--

CREATE TABLE `internaltransfer` (
  `itIndex` bigint NOT NULL,
  `orderId` int DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `fromCurrency` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `amount` double(10,4) NOT NULL DEFAULT '0.0000',
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `TransferFromAccountId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `TransferToAccountId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clientEmail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clientName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clientId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `toCurrency` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM AVG_ROW_LENGTH=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `internal_transfers_list`
-- (See below for the actual view)
--
CREATE TABLE `internal_transfers_list` (
`amount` varchar(100)
,`date` timestamp
,`email` varchar(50)
,`it_from` varchar(100)
,`it_to` varchar(100)
,`raw_id` int
,`source` varchar(4)
,`status` int
,`type` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `kyc_logs`
--

CREATE TABLE `kyc_logs` (
  `kyc_log_id` int NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `callback_code` text NOT NULL,
  `callback_payload` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kyc_logs`
--

INSERT INTO `kyc_logs` (`kyc_log_id`, `client_id`, `callback_code`, `callback_payload`, `created_at`, `updated_at`) VALUES
(1, 'muthuvenkatesh808@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"YIkax\",\"attemptId\":\"YZwkx\",\"attemptCnt\":\"0\",\"elapsedSincePendingMs\":\"22806\",\"elapsedSinceQueuedMs\":\"22806\",\"reprocessing\":\"false\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-11 07:04:45+0000\",\"reviewDate\":\"2024-09-11 07:05:08+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"confirmed\":\"true\",\"priority\":\"0\"}', '2024-09-11 10:19:40', '2024-09-11 10:19:40'),
(2, 'muthuvenkatesh808@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"YIkax\",\"attemptId\":\"YZwkx\",\"attemptCnt\":\"0\",\"elapsedSincePendingMs\":\"22806\",\"elapsedSinceQueuedMs\":\"22806\",\"reprocessing\":\"false\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-11 07:04:45+0000\",\"reviewDate\":\"2024-09-11 07:05:08+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"confirmed\":\"true\",\"priority\":\"0\"}', '2024-09-11 10:29:25', '2024-09-11 10:29:25'),
(3, 'dawood@lqhmarkets.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"kgViK\",\"attemptId\":\"LJlBP\",\"attemptCnt\":\"0\",\"elapsedSincePendingMs\":\"83547\",\"elapsedSinceQueuedMs\":\"83547\",\"reprocessing\":\"false\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-10 18:04:23+0000\",\"reviewDate\":\"2024-09-10 18:05:46+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"priority\":\"0\"}', '2024-09-11 10:51:31', '2024-09-11 10:51:31'),
(4, 'operations@nextstepfunded.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"NdZIs\",\"attemptId\":\"cIsXS\",\"attemptCnt\":\"0\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-11 08:56:59+0000\",\"expireDate\":\"2024-09-11 09:01:59+0000\",\"reviewStatus\":\"pending\",\"priority\":\"0\",\"autoChecked\":\"true\"}', '2024-09-11 10:57:00', '2024-09-11 10:57:00'),
(5, 'operations@nextstepfunded.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"NdZIs\",\"attemptId\":\"nMGnV\",\"attemptCnt\":\"1\",\"elapsedSincePendingMs\":\"1279\",\"elapsedSinceQueuedMs\":\"1279\",\"reprocessing\":\"true\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-11 08:56:59+0000\",\"reviewDate\":\"2024-09-11 08:57:00+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"priority\":\"0\"}', '2024-09-11 10:57:01', '2024-09-11 10:57:01'),
(6, 'muthuvenkatesh808@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"YIkax\",\"attemptId\":\"YZwkx\",\"attemptCnt\":\"0\",\"elapsedSincePendingMs\":\"22806\",\"elapsedSinceQueuedMs\":\"22806\",\"reprocessing\":\"false\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-11 07:04:45+0000\",\"reviewDate\":\"2024-09-11 07:05:08+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"confirmed\":\"true\",\"priority\":\"0\"}', '2024-09-11 11:37:41', '2024-09-11 11:37:41'),
(7, 'furnwest@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"Fmosn\",\"attemptId\":\"aEEDM\",\"attemptCnt\":\"0\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-11 12:08:19+0000\",\"expireDate\":\"2024-09-11 12:13:20+0000\",\"reviewStatus\":\"pending\",\"priority\":\"0\",\"autoChecked\":\"true\"}', '2024-09-11 14:08:20', '2024-09-11 14:08:20'),
(8, 'furnwest@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"Fmosn\",\"attemptId\":\"LQXnd\",\"attemptCnt\":\"1\",\"elapsedSincePendingMs\":\"1235\",\"elapsedSinceQueuedMs\":\"1235\",\"reprocessing\":\"true\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-11 12:08:19+0000\",\"reviewDate\":\"2024-09-11 12:08:21+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"priority\":\"0\"}', '2024-09-11 14:08:22', '2024-09-11 14:08:22'),
(9, 'warisahmedbarak@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"Cvyku\",\"attemptId\":\"nksIg\",\"attemptCnt\":\"0\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-12 02:05:12+0000\",\"expireDate\":\"2024-09-12 02:10:12+0000\",\"reviewStatus\":\"pending\",\"priority\":\"0\",\"autoChecked\":\"true\"}', '2024-09-12 04:05:12', '2024-09-12 04:05:12'),
(10, 'warisahmedbarak@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"Cvyku\",\"attemptId\":\"cilyP\",\"attemptCnt\":\"1\",\"elapsedSincePendingMs\":\"1189\",\"elapsedSinceQueuedMs\":\"1189\",\"reprocessing\":\"true\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-12 02:05:12+0000\",\"reviewDate\":\"2024-09-12 02:05:13+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"priority\":\"0\"}', '2024-09-12 04:05:14', '2024-09-12 04:05:14'),
(11, 'jalelwabou@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"dLhGB\",\"attemptId\":\"VThci\",\"attemptCnt\":\"0\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-14 20:03:07+0000\",\"expireDate\":\"2024-09-14 20:08:07+0000\",\"reviewStatus\":\"pending\",\"priority\":\"0\",\"autoChecked\":\"true\"}', '2024-09-14 22:03:08', '2024-09-14 22:03:08'),
(12, 'jalelwabou@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"dLhGB\",\"attemptId\":\"VThci\",\"attemptCnt\":\"0\",\"elapsedSincePendingMs\":\"535\",\"elapsedSinceQueuedMs\":\"535\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-09-14 20:03:07+0000\",\"reviewDate\":\"2024-09-14 20:03:08+0000\",\"reviewResult\":{\"reviewAnswer\":\"RED\",\"reviewRejectType\":\"RETRY\"},\"reviewStatus\":\"prechecked\",\"priority\":\"0\"}', '2024-09-14 22:03:09', '2024-09-14 22:03:09'),
(13, 'contact@evsconnect.net', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"GSlOC\",\"attemptId\":\"wwmOp\",\"attemptCnt\":\"0\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-10-07 19:31:42+0000\",\"expireDate\":\"2024-10-07 19:36:42+0000\",\"reviewStatus\":\"pending\",\"priority\":\"0\"}', '2024-10-07 21:31:43', '2024-10-07 21:31:43'),
(14, 'contact@evsconnect.net', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"GSlOC\",\"attemptId\":\"tRgNX\",\"attemptCnt\":\"1\",\"elapsedSincePendingMs\":\"1402\",\"elapsedSinceQueuedMs\":\"1402\",\"reprocessing\":\"true\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-10-07 19:31:42+0000\",\"reviewDate\":\"2024-10-07 19:31:44+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"priority\":\"0\"}', '2024-10-07 21:31:44', '2024-10-07 21:31:44'),
(15, 'mayongel94+LQH@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"xLcOR\",\"attemptId\":\"SLczX\",\"attemptCnt\":\"0\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-10-13 22:46:08+0000\",\"expireDate\":\"2024-10-13 22:51:08+0000\",\"reviewStatus\":\"pending\",\"priority\":\"0\"}', '2024-10-14 00:46:09', '2024-10-14 00:46:09'),
(16, 'mayongel94+LQH@gmail.com', '\"idCheck.onApplicantStatusChanged\"', '{\"reviewId\":\"xLcOR\",\"attemptId\":\"QWJPF\",\"attemptCnt\":\"1\",\"elapsedSincePendingMs\":\"1293\",\"elapsedSinceQueuedMs\":\"1293\",\"reprocessing\":\"true\",\"levelName\":\"basic-kyc-level\",\"levelAutoCheckMode\":\"\",\"createDate\":\"2024-10-13 22:46:08+0000\",\"reviewDate\":\"2024-10-13 22:46:09+0000\",\"reviewResult\":{\"reviewAnswer\":\"GREEN\"},\"reviewStatus\":\"completed\",\"priority\":\"0\"}', '2024-10-14 00:46:10', '2024-10-14 00:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `kyc_update`
--

CREATE TABLE `kyc_update` (
  `id` int NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_frontside` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `front_image` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kyc_backside` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `back_image` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registered_date_js` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Admin_Remark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Admin_Remark_Date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int DEFAULT '0',
  `added_by` int DEFAULT '0',
  `approved_by` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_update`
--

INSERT INTO `kyc_update` (`id`, `email`, `kyc_type`, `kyc_frontside`, `front_image`, `kyc_backside`, `back_image`, `registered_date_js`, `Admin_Remark`, `Admin_Remark_Date`, `Status`, `added_by`, `approved_by`) VALUES
(1, 'tech+2@lqhmarkets.com', 'ID Proof', '/_docs/gratisography-cyber-kitty-800x525.jpg', '', '/_docs/gratisography-cyber-kitty-800x525.jpg', NULL, '2024-09-09 02:40:47', '-', '2024-09-09 02:40:47', 1, 0, 'patel@lqhmarkets.com'),
(2, 'tech+2@lqhmarkets.com', 'Address Proof', NULL, '/_docs/gratisography-cyber-kitty-800x525.jpg', NULL, NULL, '2024-09-09 02:40:21', '-', '2024-09-09 02:40:21', 1, 0, 'patel@lqhmarkets.com'),
(3, 'testfx333@yopmail.com', 'ID Proof', '/_docs/IMG-20240909-WA0005.jpg', '', '/_docs/IMG-20240909-WA0005.jpg', NULL, '2024-09-09 06:04:55', NULL, '2024-09-09 06:04:55', 0, 0, '0'),
(4, 'testfx333@yopmail.com', 'Address Proof', NULL, '/_docs/IMG-20240909-WA0005.jpg', NULL, NULL, '2024-09-09 06:05:27', NULL, '2024-09-09 06:05:27', 0, 0, '0'),
(5, 'gurkiran121@gmail.com', 'ID Proof', '/_docs/download.jpeg', '', '/_docs/download.jpeg', NULL, '2024-09-09 18:51:00', '-', '2024-09-09 18:51:00', 1, 0, 'patel@lqhmarkets.com'),
(6, 'gurkiran121@gmail.com', 'Address Proof', NULL, '/_docs/download.jpeg', NULL, NULL, '2024-09-09 18:51:14', '-', '2024-09-09 18:51:14', 1, 0, 'patel@lqhmarkets.com'),
(7, 'furnwest@gmail.com', 'ID Proof', '/_docs/f.png', '', '/_docs/f.png', NULL, '2024-09-10 10:25:27', NULL, '2024-09-10 10:25:27', 0, 0, '0'),
(8, 'furnwest@gmail.com', 'Address Proof', NULL, '/_docs/f.png', NULL, NULL, '2024-09-10 10:25:42', NULL, '2024-09-10 10:25:42', 0, 0, '0'),
(9, 'rugmar91@gmail.com', 'ID Proof', '/_docs/b.jpg', '', '/_docs/b.jpg', NULL, '2024-09-10 12:22:37', 'f', '2024-09-10 12:22:37', 1, 0, '78'),
(10, 'rugmar91@gmail.com', 'Address Proof', NULL, '/_docs/b.jpg', NULL, NULL, '2024-09-10 12:24:17', 'sdfghjkl;\'', '2024-09-10 12:24:17', 1, 0, '78');

-- --------------------------------------------------------

--
-- Table structure for table `leverage`
--

CREATE TABLE `leverage` (
  `id` int NOT NULL,
  `account_type_id` int NOT NULL,
  `account_leverage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leverage`
--

INSERT INTO `leverage` (`id`, `account_type_id`, `account_leverage`, `created_at`, `updated_at`) VALUES
(61, 57, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(83, 54, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(89, 56, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(90, 56, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(91, 56, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(92, 56, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(93, 56, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(94, 51, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(95, 51, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(96, 51, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(97, 51, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(98, 51, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(99, 52, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(100, 52, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(101, 52, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(102, 52, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(103, 52, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(104, 49, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(105, 49, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(106, 49, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(107, 49, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(108, 49, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(109, 50, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(110, 50, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(111, 50, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(112, 50, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(113, 50, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(114, 55, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(115, 55, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(116, 55, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(117, 55, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(118, 55, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(119, 47, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(120, 47, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(121, 47, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(122, 47, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(123, 47, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(124, 48, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(125, 48, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(126, 48, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(127, 48, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(128, 48, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(129, 45, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(130, 45, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(131, 45, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(132, 45, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(133, 45, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(134, 46, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(135, 46, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(136, 46, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(137, 46, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(138, 46, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(159, 60, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(160, 60, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(161, 60, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(162, 60, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(163, 60, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(164, 58, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(165, 58, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(166, 58, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(167, 58, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(168, 58, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(174, 59, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(175, 59, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(176, 59, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(177, 59, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(178, 59, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(179, 53, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(180, 61, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(181, 61, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(182, 61, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(183, 61, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(184, 61, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(190, 63, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(191, 62, '100', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(192, 62, '200', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(193, 62, '300', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(194, 62, '400', '2024-10-21 10:23:05', '2024-10-21 10:23:05'),
(195, 62, '500', '2024-10-21 10:23:05', '2024-10-21 10:23:05');

-- --------------------------------------------------------

--
-- Table structure for table `liveaccount`
--

CREATE TABLE `liveaccount` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_type` int DEFAULT NULL,
  `credit` decimal(10,2) DEFAULT NULL,
  `leverage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `Balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `equity` double(15,4) DEFAULT '0.0000',
  `tradePlatform` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'MetaTrader5',
  `lotsCompleted` int NOT NULL DEFAULT '0',
  `MarginFree` double(15,4) NOT NULL DEFAULT '0.0000',
  `MarginLevel` double(15,4) NOT NULL DEFAULT '0.0000',
  `MarginLevelType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ok',
  `adj` double(10,4) NOT NULL DEFAULT '0.0000',
  `deposit` double(15,4) NOT NULL DEFAULT '0.0000',
  `withdraw` double(15,4) NOT NULL DEFAULT '0.0000',
  `internal_transfer` double(15,4) NOT NULL DEFAULT '0.0000',
  `internalDeposit` double(15,4) NOT NULL DEFAULT '0.0000',
  `trader_pwd` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invester_pwd` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_pwd` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Registered_Date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `bonusDeposit` double(15,4) NOT NULL DEFAULT '0.0000',
  `wBonusDeposit` double(15,4) NOT NULL DEFAULT '0.0000',
  `ib1` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM AVG_ROW_LENGTH=164 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `liveaccount`
--

INSERT INTO `liveaccount` (`id`, `name`, `email`, `trade_id`, `account_type`, `credit`, `leverage`, `currency`, `Balance`, `equity`, `tradePlatform`, `lotsCompleted`, `MarginFree`, `MarginLevel`, `MarginLevelType`, `adj`, `deposit`, `withdraw`, `internal_transfer`, `internalDeposit`, `trader_pwd`, `invester_pwd`, `phone_pwd`, `Registered_Date`, `status`, `bonusDeposit`, `wBonusDeposit`, `ib1`) VALUES
(1, 'Syed Mohamed Rafi Babu', 'syedmohamedrafi@gmail.com', '18256011', 46, '0.00', '100', 'USD', '5500.00', 5500.0000, 'MetaTrader5', 0, 5500.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'x9Te6)2VL', '$@L%p98qS', '+x00YZGr#', '2024-09-04 22:23:07', 'active', 0.0000, 0.0000, ''),
(2, 'Jalel Abougouche', 'jalelwabou@gmail.com', '283811', 46, '0.00', '500', 'USD', '25.20', 25.2000, 'MetaTrader5', 0, 25.2000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'jk@V40zsc', 'z3pEw!tml', '6KYJWmTZ!', '2024-09-28 15:34:29', 'active', 0.0000, 0.0000, ''),
(3, 'Syed Mohamed Rafi Babu', 'syedmohamedrafi@gmail.com', '747204', 52, '0.00', '100', 'USD', '25998.96', 25984.7800, 'MetaTrader5', 0, 25694.6100, 8955.0200, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '5700d$A4', '6Mzgbs!fQ', 'SW0FvGxz!', '2024-10-01 20:51:54', 'active', 0.0000, 0.0000, ''),
(4, 'Syed Mohamed Rafi Babu', 'syedmohamedrafi@gmail.com', '467660', 50, '0.00', '400', 'USD', '16350.00', 16350.0000, 'MetaTrader5', 0, 16350.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'b4!1Yt@1Z', 'x2L8#1HW#', '!bfL6xZP8', '2024-09-06 03:46:02', 'active', 0.0000, 0.0000, ''),
(5, 'Mega Stand', 'megastand@protonmail.com', '472713', 46, '0.00', '100', 'USD', '44.00', 44.0000, 'MetaTrader5', 0, 44.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'Loluthot123@', '#@90dVhE!', '7CiHIjVz#', '2024-09-07 00:32:58', 'active', 0.0000, 0.0000, ''),
(6, 'Syed Mohamed Rafi Babu', 'syedmohamedrafi@gmail.com', '736893', 50, '0.00', '1000', 'USD', '500.00', 500.0000, 'MetaTrader5', 0, 500.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '6y!uq@XO@', '1ZH6bFo7!', 'vWy040@!V', '2024-09-05 21:36:42', 'active', 0.0000, 0.0000, ''),
(7, 'Syed Mohamed Rafi Babu', 'syedmohamedrafi@gmail.com', '770276', 54, '0.00', '100', 'USD', '1000.00', 1000.0000, 'MetaTrader5', 0, 1000.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'Test@1234#', '#cHnwK0gT', 'G8L5ee9!K', '2024-09-08 20:09:08', 'active', 0.0000, 0.0000, ''),
(8, 'Syed Mohamed Rafi Babu', 'syedmohamedrafi@gmail.com', '159527', 52, '0.00', '100', 'USD', '350.00', 360.2200, 'MetaTrader5', 0, 360.2000, 1801100.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'gm4#68ndD', 'g5k44@kVH', 'y8GN@Q057', '2024-10-01 20:51:54', 'active', 0.0000, 0.0000, ''),
(9, 'Syed Mohamed Rafi Babu', 'syedmohamedrafi@gmail.com', '601458', 54, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'dC#xZy2Qc', '@W6PwX1eM', 's18NWIu!L', '2024-09-05 22:38:05', 'active', 0.0000, 0.0000, ''),
(10, 'Rugma R', 'rugmaramanathan@gmail.com', '258494', 46, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'GK@I1PY8d', 'L!XF!8xL@', 'AUOykay!9', '2024-09-06 15:52:23', 'active', 0.0000, 0.0000, ''),
(11, 'Mega Stand', 'megastand@protonmail.com', '716493', 48, '0.00', '100', 'USD', '13.00', 13.0000, 'MetaTrader5', 0, 13.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'pCD7JF#hL', '2jY!!Qj3H', 'jT@9oPdoK', '2024-09-08 21:14:42', 'active', 0.0000, 0.0000, ''),
(12, 'Mega Stand', 'megastand@protonmail.com', '357080', 46, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '@c8Dy0Pi8', 'SzDs4T#gE', '9tNhX@H2P', '2024-09-06 17:10:29', 'active', 0.0000, 0.0000, ''),
(13, 'Mega Stand', 'megastand@protonmail.com', '315802', 48, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'L!jo0v@qK', '0q@AkxSg0', 'qs5Vx9@VZ', '2024-09-06 17:10:29', 'active', 0.0000, 0.0000, ''),
(14, 'Burton Reese', 'fajife5699@ploncy.com', '950387', 46, '0.00', '100', 'USD', '8.00', 8.0000, 'MetaTrader5', 0, 8.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'aBPx068#@', 'n@o5!s6Gm', 'POL!E2msp', '2024-09-07 11:41:17', 'active', 0.0000, 0.0000, ''),
(15, 'Media Slush', 'mediaslush@protonmail.com', '707572', 46, '0.00', '100', 'USD', '60.00', 60.0000, 'MetaTrader5', 0, 60.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'u8Ji4Q!JN', '5N4!pnmRk', 'HyIpDNv7#', '2024-09-08 00:05:03', 'active', 0.0000, 0.0000, ''),
(16, 'Tech +2', 'tech+2@lqhmarkets.com', '876348', 46, '0.00', '100', 'USD', '10.00', 10.0000, 'MetaTrader5', 0, 10.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'gHm!5sZ8M', '1X3Oxm!N@', 'ZQtSy3NU#', '2024-09-22 04:54:15', 'active', 0.0000, 0.0000, ''),
(17, 'Rugma Ramanathan', 'rugmar91@gmail.com', '919514', 46, '0.00', '100', 'USD', '326.00', 326.0000, 'MetaTrader5', 0, 326.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'YywT9Yym!', 'H2Tb4@o!q', 'CRt25o@3x', '2024-10-17 12:52:48', 'active', 0.0000, 0.0000, ''),
(18, 'Rugma Ramanathan', 'rugmar91@gmail.com', '282918', 48, '0.00', '100', 'USD', '12.00', 12.0000, 'MetaTrader5', 0, 12.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'V3GkTv#Av', '0t!ZhKTBB', 'MMnGht8@I', '2024-10-15 10:31:19', 'active', 0.0000, 0.0000, ''),
(19, 'Jay Abou', 'operations@nextstepfunded.com', '426609', 46, NULL, '500', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'EkOy#OU5#', 'Fc!fP8h8a', 'ZgQp!6WoG', '2024-09-11 08:59:29', 'active', 0.0000, 0.0000, ''),
(20, 'Dulce Mayon', 'furnwest@gmail.com', '172349', 50, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'Vkzj#g0fG', '1@OSVVzN5', '3vXT@6e#O', '2024-09-12 01:27:58', 'active', 0.0000, 0.0000, ''),
(21, 'Waris Ahmad Barak ', 'warisahmedbarak@gmail.com', '685228', 46, '0.00', '500', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'Waris@123', 'e@2IP5TGB', 'u@3YCfK8g', '2024-09-12 02:09:31', 'active', 0.0000, 0.0000, ''),
(22, 'Rugma Ramanathan', 'rugmar91@gmail.com', '312391', 46, '1490.00', '100', 'USD', '10.00', 1500.0000, 'MetaTrader5', 0, 1500.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '!v2bHMjwr', 'atz!4oLYA', 'G@3vZrQ7p', '2024-10-09 11:02:41', 'active', 0.0000, 0.0000, ''),
(23, 'Jay Abou', 'dawood@lqhmarkets.com', '970092', 46, '0.00', '500', 'USD', '1007.99', 1007.0600, 'MetaTrader5', 0, 1007.0600, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '87zAJ#ZtZ', '!n8IEBubS', 'tnY2H2E#Q', '2024-10-01 06:46:45', 'active', 0.0000, 0.0000, ''),
(24, 'Muthu Venkatesh', 'muthuvenkatesh808@gmail.com', '264665', 46, '20.00', '400', 'USD', '6.00', 26.0000, 'MetaTrader5', 0, 26.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '8xVzh6if@', 'ysLLNh@1w', 'V0#C1#DsX', '2024-10-17 06:26:20', 'active', 0.0000, 0.0000, ''),
(25, 'LQH Markets', 'lqhmarkets@gmail.com', '235001', 46, '0.00', '100', 'USD', '10.78', 10.7800, 'MetaTrader5', 0, 10.7800, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'ZegaDyzi123@', '3beDg@WHb', 'EF0g3kJ#Z', '2024-10-17 23:35:30', 'active', 0.0000, 0.0000, ''),
(26, 'Dulce Mayon', 'furnwest@gmail.com', '435214', 46, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '5S!68fRPo', 't4MwU@VWv', 'fdiG2DA2@', '2024-09-24 01:31:34', 'active', 0.0000, 0.0000, ''),
(27, 'Dulce Mayon', 'furnwest@gmail.com', '700806', 48, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'mv3i!VxkW', 'B!QTxY0db', 'JV@Ir@mo1', '2024-09-24 02:13:11', 'active', 0.0000, 0.0000, ''),
(28, 'Dulce Mayon', 'furnwest@gmail.com', '184244', 54, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'TFtSQQ0@4', 'rD8G9f@kH', '@IHw3#2ag', '2024-09-24 02:13:11', 'active', 0.0000, 0.0000, ''),
(29, 'Dulce Mayon', 'furnwest@gmail.com', '282365', 52, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'i@BoDd1Ry', 'B@R2ocIwM', 'b@SC9rzm3', '2024-09-24 02:13:11', 'active', 0.0000, 0.0000, ''),
(30, 'Dulce Mayon', 'furnwest@gmail.com', '278685', 48, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'x!p1IH3!D', '@1N75V1jG', 'Tg#xAleB0', '2024-09-24 02:13:11', 'active', 0.0000, 0.0000, ''),
(31, 'LQH Markets', 'lqhmarkets@gmail.com', '731609', 46, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '7JD53h@tN', 'o@JV4vbWb', 'PZLzb#a2K', '2024-09-27 05:23:33', 'active', 0.0000, 0.0000, ''),
(33, 'jake', 'abougouche22@gmail.com', '493374', 46, '0.00', '500', 'USD', '1001.00', 1001.0000, 'MetaTrader5', 0, 1001.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'Jalel123!', '9pnOK0!A7', 'qSfe#59q#', '2024-10-01 14:47:54', 'active', 0.0000, 0.0000, 'jalelwabou@gmail.com'),
(32, 'Muthu Venkatesh', 'muthuvenkatesh808@gmail.com', '573702', 54, '23.50', '100', 'USD', '3.00', 26.5000, 'MetaTrader5', 0, 26.5000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'kdU6zSwE@', 'g1#o3gELI', 'nN@NcPz5R', '2024-10-08 16:02:32', 'active', 0.0000, 0.0000, ''),
(34, 'Jalel Abougouche', 'jalelwabou@gmail.com', '165718', 63, '0.00', '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'ekF2O#MpI', '6hB1Sa!DS', 'z5@EK3Mi4', '2024-10-16 13:43:19', 'active', 0.0000, 0.0000, ''),
(35, 'Angel mayon', 'mayongel94+LQH@gmail.com', '219026', 60, NULL, '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'N@Kp5O7sz', 'fyVzLA8!A', 'QyEkNA#35', '2024-10-13 22:54:03', 'active', 0.0000, 0.0000, ''),
(36, 'Angel mayon', 'mayongel94+LQH@gmail.com', '127578', 63, NULL, '100', 'USD', '0.00', 0.0000, 'MetaTrader5', 0, 0.0000, 0.0000, 'ok', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 'bh!!d#32F', 'n@19IJLhd', '@soxrqW2a', '2024-10-13 23:15:30', 'active', 0.0000, 0.0000, '');

-- --------------------------------------------------------

--
-- Table structure for table `login_details`
--

CREATE TABLE `login_details` (
  `id` int NOT NULL,
  `UserId` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `log_in` datetime DEFAULT NULL,
  `IP_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `System_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `browser_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_date_js` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`id`, `email`, `ip`, `country`, `action`, `created_date_js`, `status`) VALUES
(1, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(2, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(3, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(4, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(5, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(6, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(7, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(8, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(9, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(10, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(11, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(12, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(13, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(14, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(15, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(16, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(17, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(18, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(19, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(20, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(21, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(22, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(23, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(24, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(25, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(26, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(27, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(28, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(29, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(30, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(31, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(32, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(33, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(34, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(35, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(36, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(37, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(38, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(39, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(40, 'rugmaramanathan@gmail.com', NULL, NULL, 'login', NULL, 1),
(41, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(42, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(43, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(44, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(45, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(46, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(47, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(48, 'mediaslush@protonmail.com', NULL, NULL, 'login', NULL, 1),
(49, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(50, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(51, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(52, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(53, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(54, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(55, 'fajife5699@ploncy.com', NULL, NULL, 'login', NULL, 1),
(56, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(57, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(58, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(59, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(60, 'fajife5699@ploncy.com', NULL, NULL, 'login', NULL, 1),
(61, 'fajife5699@ploncy.com', NULL, NULL, 'login', NULL, 1),
(62, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(63, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(64, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(65, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(66, 'mediaslush@protonmail.com', NULL, NULL, 'login', NULL, 1),
(67, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(68, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(69, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(70, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(71, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(72, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(73, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(74, 'Contact@bridgingfx.net', NULL, NULL, 'login', NULL, 1),
(75, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(76, 'Contact@bridgingfx.net', NULL, NULL, 'login', NULL, 1),
(77, 'Contact@bridgingfx.net', NULL, NULL, 'login', NULL, 1),
(78, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(79, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(80, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(81, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(82, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(83, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(84, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(85, 'tech+2@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(86, 'testfx333@yopmail.com', NULL, NULL, 'login', NULL, 1),
(87, 'mt4mt5solutions@gmail.com', NULL, NULL, 'login', NULL, 1),
(88, 'testfx333@yopmail.com', NULL, NULL, 'login', NULL, 1),
(89, 'getscare@gmail.com', NULL, NULL, 'login', NULL, 1),
(90, 'testfx333@yopmail.com', NULL, NULL, 'login', NULL, 1),
(91, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(92, 'singh@serverfront.net', NULL, NULL, 'login', NULL, 1),
(93, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(94, 'testfx333@yopmail.com', NULL, NULL, 'login', NULL, 1),
(95, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(96, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(97, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(98, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(99, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(100, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(101, 'singh@serverfront.net', NULL, NULL, 'login', NULL, 1),
(102, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(103, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(104, 'tech+2@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(105, 'tech+3@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(106, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(107, 'gurkiran121@gmail.com', NULL, NULL, 'login', NULL, 1),
(108, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(109, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(110, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(111, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(112, 'rugmaramanathan@gmail.com', NULL, NULL, 'login', NULL, 1),
(113, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(114, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(115, 'priya234fx@gmail.com', NULL, NULL, 'login', NULL, 1),
(116, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(117, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(118, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(119, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(120, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(121, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(122, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(123, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(124, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(125, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(126, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(127, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(128, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(129, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(130, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(131, 'operations@nextstepfunded.com', NULL, NULL, 'login', NULL, 1),
(132, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(133, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(134, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(135, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(136, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(137, 'warisahmedbarak@gmail.com', NULL, NULL, 'login', NULL, 1),
(138, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(139, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(140, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(141, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(142, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(143, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(144, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(145, 'nendir771@gmail.com', NULL, NULL, 'login', NULL, 1),
(146, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(147, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(148, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(149, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(150, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(151, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(152, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(153, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(154, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(155, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(156, 'tech+2@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(157, 'megastand@protonmail.com', NULL, NULL, 'login', NULL, 1),
(158, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(159, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(160, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(161, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(162, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(163, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(164, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(165, 'garahaltopai@gmail.com', NULL, NULL, 'login', NULL, 1),
(166, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(167, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(168, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(169, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(170, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(171, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(172, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(173, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(174, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(175, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(176, 'garahaltopai@gmail.com', NULL, NULL, 'login', NULL, 1),
(177, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(178, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(179, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(180, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(181, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(182, 'gurkiran121@gmail.com', NULL, NULL, 'login', NULL, 1),
(183, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(184, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(185, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(186, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(187, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(188, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(189, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(190, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(191, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(192, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(193, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(194, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(195, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(196, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(197, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(198, 'mshanafxhealer@gmail.com', NULL, NULL, 'login', NULL, 1),
(199, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(200, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(201, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(202, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(203, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(204, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(205, 'dawood@lqhmarkets.com', NULL, NULL, 'login', NULL, 1),
(206, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(207, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(208, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(209, 'abougouche22@gmail.com', NULL, NULL, 'login', NULL, 1),
(210, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(211, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(212, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(213, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(214, 'abougouche22@gmail.com', NULL, NULL, 'login', NULL, 1),
(215, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(216, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(217, 'syedmohamedrafi@gmail.com', NULL, NULL, 'login', NULL, 1),
(218, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(219, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(220, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(221, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(222, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(223, 'contact@evsconnect.net', NULL, NULL, 'login', NULL, 1),
(224, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(225, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(226, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(227, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(228, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(229, 'contact@evsconnect.net', NULL, NULL, 'login', NULL, 1),
(230, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(231, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(232, 'contact@evsconnect.net', NULL, NULL, 'login', NULL, 1),
(233, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1),
(234, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(235, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(236, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(237, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(238, 'furnwest@gmail.com', NULL, NULL, 'login', NULL, 1),
(239, 'mayongel94+LQH@gmail.com', NULL, NULL, 'login', NULL, 1),
(240, 'mayongel94+LQH@gmail.com', NULL, NULL, 'login', NULL, 1),
(241, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(242, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(243, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(244, 'warisahmedbarak@gmail.com', NULL, NULL, 'login', NULL, 1),
(245, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(246, 'jalelwabou@gmail.com', NULL, NULL, 'login', NULL, 1),
(247, 'muthuvenkatesh808@gmail.com', NULL, NULL, 'login', NULL, 1),
(248, 'rugmar91@gmail.com', NULL, NULL, 'login', NULL, 1),
(249, 'lqhmarkets@gmail.com', NULL, NULL, 'login', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `metatradertradehistory`
--

CREATE TABLE `metatradertradehistory` (
  `tradeIndex` bigint NOT NULL,
  `closePrice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `closeTime` bigint DEFAULT NULL,
  `openTime` bigint DEFAULT NULL,
  `cmd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commission` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commissionAgent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `digits` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gwClosePrice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gwOpenPrice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gwOrder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gwVolume` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `magic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `marginRate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `openPrice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `orderId` int DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taxes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timestamp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volume` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volumeReal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lastUpdated` datetime DEFAULT NULL,
  `pnl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `hedgeOrder` tinyint(1) NOT NULL DEFAULT '0',
  `hedgedifference` double(10,4) NOT NULL DEFAULT '0.0000',
  `hedgeFL` double(10,4) NOT NULL DEFAULT '0.0000',
  `hedgeHL` double(10,4) NOT NULL DEFAULT '0.0000',
  `oMarginRate` double(10,4) NOT NULL DEFAULT '0.0000',
  `oTimestamp` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entry` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `positionID` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contractSize` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deal` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dealer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `digitsCurrency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expertID` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `externalID` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gateway` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `priceGateway` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pricePosition` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profitRaw` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tickSize` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tickValue` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `oTimeMsc` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volumeClosed` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cSts` tinyint(1) NOT NULL DEFAULT '0',
  `version` int NOT NULL DEFAULT '5',
  `cCmd` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cOrder` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cEntry` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cDeal` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cDealer` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cExtrnalID` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cGateway` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cPriceGateway` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cTickSize` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cTickValue` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timeMsc` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lotConversion` double NOT NULL DEFAULT '0',
  `showRow` tinyint(1) NOT NULL DEFAULT '1',
  `orderGroupOpen` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noGroup',
  `orderGroupClose` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noGroup'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `metatradertradehistorydemo`
--

CREATE TABLE `metatradertradehistorydemo` (
  `tradeIndex` bigint NOT NULL,
  `closePrice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `closeTime` bigint DEFAULT NULL,
  `openTime` bigint DEFAULT NULL,
  `cmd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commission` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commissionAgent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `digits` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gwClosePrice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gwOpenPrice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gwOrder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gwVolume` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `magic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `marginRate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `openPrice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `orderId` int DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taxes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timestamp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volume` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volumeReal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lastUpdated` datetime DEFAULT NULL,
  `pnl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `hedgeOrder` tinyint(1) NOT NULL DEFAULT '0',
  `hedgedifference` double(10,4) NOT NULL DEFAULT '0.0000',
  `hedgeFL` double(10,4) NOT NULL DEFAULT '0.0000',
  `hedgeHL` double(10,4) NOT NULL DEFAULT '0.0000',
  `oMarginRate` double(10,4) NOT NULL DEFAULT '0.0000',
  `oTimestamp` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entry` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `positionID` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contractSize` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deal` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dealer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `digitsCurrency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expertID` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `externalID` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gateway` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `priceGateway` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pricePosition` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profitRaw` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tickSize` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tickValue` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `oTimeMsc` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volumeClosed` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cSts` tinyint(1) NOT NULL DEFAULT '0',
  `version` int NOT NULL DEFAULT '4',
  `cCmd` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cOrder` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cEntry` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cDeal` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cDealer` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cExtrnalID` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cGateway` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cPriceGateway` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cTickSize` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cTickValue` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timeMsc` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `orderGroupOpen` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noGroup',
  `orderGroupClose` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noGroup'
) ENGINE=MyISAM AVG_ROW_LENGTH=237 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mt5_groups`
--

CREATE TABLE `mt5_groups` (
  `mt5_group_id` int NOT NULL,
  `mt5_group_name` varchar(255) NOT NULL,
  `mt5_group_type` enum('demo','live') NOT NULL DEFAULT 'demo',
  `mt5_group_desc` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_by` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mt5_groups`
--

INSERT INTO `mt5_groups` (`mt5_group_id`, `mt5_group_name`, `mt5_group_type`, `mt5_group_desc`, `is_active`, `updated_by`, `created_at`, `updated_at`) VALUES
(9, 'LQH MARKETS', 'live', 'LQH MARKETS - LIVE', 1, 'admin@bridgingfx.net', '2024-09-04 19:54:40', '2024-09-04 19:54:40'),
(10, 'LQH MARKETS', 'demo', 'LQH MARKETS - DEMO', 1, 'admin@bridgingfx.net', '2024-09-04 19:55:01', '2024-09-04 19:55:01');

-- --------------------------------------------------------

--
-- Table structure for table `mt5_group_categories`
--

CREATE TABLE `mt5_group_categories` (
  `mt5_grp_cat_id` int NOT NULL,
  `mt5_grp_cat_name` varchar(255) NOT NULL,
  `mt5_grp_cat_type` varchar(100) NOT NULL,
  `mt5_grp_cat_desc` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mt5_group_categories`
--

INSERT INTO `mt5_group_categories` (`mt5_grp_cat_id`, `mt5_grp_cat_name`, `mt5_grp_cat_type`, `mt5_grp_cat_desc`, `is_active`, `created_at`, `updated_at`, `created_by`) VALUES
(11, 'STP', 'type', 'STP GROUP', 1, '2024-09-04 01:08:11', '2024-10-01 11:23:52', NULL),
(12, 'ECN', 'type', 'ECN Groups', 1, '2024-09-04 01:08:33', '2024-09-04 01:08:33', NULL),
(13, 'INSTITUTIONAL', 'type', 'INSTITUTIONAL GROUP', 1, '2024-09-04 01:08:57', '2024-09-04 01:08:57', NULL),
(14, 'A', 'book', 'Book', 1, '2024-09-04 01:09:15', '2024-09-04 01:09:15', NULL),
(15, 'B', 'book', 'Book', 1, '2024-09-04 01:09:29', '2024-09-04 01:09:29', NULL),
(16, 'STP LIVE', 'type', 'STP LIVE GROUPS', 1, '2024-09-04 01:16:42', '2024-09-04 01:16:42', NULL),
(17, 'STP BONUS', 'type', 'STP BONUS GROUP', 0, '2024-09-04 01:16:58', '2024-09-11 15:44:17', NULL),
(18, 'ECN LIVE', 'type', 'ECN LIVE GROUPS', 1, '2024-09-04 01:17:13', '2024-09-04 01:17:13', NULL),
(19, 'ECN BONUS', 'type', 'ECN BONUS GROUP', 0, '2024-09-04 01:17:29', '2024-09-11 15:44:33', NULL),
(20, 'STANDARD', 'type', 'STANDARD GROUP', 1, '2024-10-01 21:59:08', '2024-10-01 21:59:08', NULL),
(21, 'PRO', 'type', 'PRO GROUP', 1, '2024-10-01 21:59:34', '2024-10-01 21:59:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `page_id` int NOT NULL,
  `page_category_id` int DEFAULT NULL,
  `pagename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_submenu` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `page_order` int NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `show_in_menu` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`page_id`, `page_category_id`, `pagename`, `filename`, `is_submenu`, `active`, `page_order`, `icon`, `show_in_menu`) VALUES
(1, 1, 'Dashboard', '/admin/dashboard', 0, 1, 1, 'fe fe-airplay', 1),
(2, 2, 'Client List', '/admin/client_list', 0, 1, 2, 'fe fe-users', 1),
(3, 5, 'Transactions', '#', 0, 1, 3, 'fe fe-credit-card', 1),
(4, 5, 'Wallet Deposit', '/admin/transactions/wallet_deposit', 3, 1, 1, '', 1),
(5, 5, 'Wallet Withdrawal', '/admin/transactions/wallet_withdrawal', 3, 1, 2, '', 1),
(6, 5, 'Trading Deposit', '/admin/transactions/trading_deposit', 3, 1, 3, '', 1),
(7, 5, 'Trading Withdrawal', '/admin/transactions/trading_withdrawal', 3, 1, 4, '', 1),
(8, 5, 'Internal Transfer', '/admin/transactions/internal_transfer', 3, 1, 5, '', 1),
(9, 2, 'Client Resource', '#', 0, 1, 4, 'fe fe-file-text', 1),
(10, 2, 'KYC History', '/admin/kyc_history', 9, 0, 1, '', 1),
(11, 2, 'Bank Details', '/admin/bank_details', 9, 1, 2, '', 1),
(12, 3, 'IB', '#', 0, 1, 5, 'fe fe-user', 1),
(13, 3, 'IB Requests', '/admin/iblist', 12, 1, 2, '', 1),
(14, 6, 'META Config', '#', 0, 1, 6, 'fe fe-help-circle', 1),
(15, 6, 'MT5 Groups', '/admin/mt5_groups', 14, 1, 1, '', 1),
(16, 4, 'Staff Management', '#', 0, 1, 7, 'fe fe-user', 1),
(17, 4, 'Roles', '/admin/roles', 16, 1, 1, '', 1),
(18, 4, 'Role Permissions', '/admin/role_permissions', 16, 1, 2, '', 1),
(19, 4, 'Staffs Management', '/admin/admin_users', 16, 1, 3, '', 1),
(20, 4, 'Help Desk', '#', 0, 1, 8, 'fe fe-help-circle', 1),
(21, 4, 'All Tickets', '/admin/all_tickets', 20, 1, 1, '', 1),
(22, 4, 'Open Tickets', '/admin/open_tickets', 20, 1, 2, '', 1),
(23, 4, 'Closed Tickets', '/admin/closed_tickets', 20, 1, 3, '', 1),
(24, 4, 'Settings', '#', 0, 1, 9, 'fe fe-settings', 1),
(25, 4, 'Update Password', '/admin/update_password', 24, 1, 1, '', 1),
(26, 4, 'Payment Gateways', '/admin/payment_gateways', 24, 0, 2, '', 1),
(27, 4, 'UI Settings', '/admin/ui_settings', 24, 1, 3, '', 1),
(56, 3, 'IB Com. Settings', '/admin/ib_settings', 12, 1, 4, '', 1),
(57, 5, 'Pend.,Transactions', '#', 0, 1, 4, 'fe fe-list', 1),
(58, 5, 'Wallet Deposit', '/admin/transactions/pending/wallet_deposit', 57, 1, 1, '', 1),
(59, 5, 'Wallet Withdrawal', '/admin/transactions/pending/wallet_withdrawal', 57, 1, 2, '', 1),
(60, 5, 'Trading Deposit', '/admin/transactions/pending/trading_deposit', 57, 1, 3, '', 1),
(61, 5, 'Trading Withdrawal', '/admin/transactions/pending/trading_withdrawal', 57, 1, 4, '', 1),
(62, 5, 'Internal Transfer', '/admin/transactions/pending/internal_transfer', 57, 0, 5, '', 1),
(63, 2, 'Client Accounts', '#', 0, 1, 2, 'fe fe-user-plus', 1),
(64, 2, 'Live Accounts', '/admin/clientAccounts/liveAccounts', 63, 1, 1, '', 1),
(65, 2, 'Demo Accounts', '/admin/clientAccounts/demoAccounts', 63, 1, 1, '', 1),
(67, 3, 'IB Dashboard', '/admin/ibdashboard', 12, 1, 1, '', 1),
(68, 3, 'IB Users', '/admin/iblist_active', 12, 1, 3, '', 1),
(70, 1, 'Wallet Deposit Details', '/admin/wallet_deposit_details', 0, 1, 1, 'fe fe-airplay', 0),
(71, 1, 'Wallet Withdrawal Details', '/admin/wallet_withdrawal_details', 0, 1, 1, 'fe fe-airplay', 0),
(72, 1, 'Trading Deposit Details', '/admin/trading_deposit_details', 0, 1, 1, 'fe fe-airplay', 0),
(73, 1, 'Trading Withdrawal Details', '/admin/trading_withdrawal_details', 0, 1, 1, 'fe fe-airplay', 0),
(74, 1, 'Client Details', '/admin/client_details', 0, 1, 1, 'fe fe-airplay', 0),
(75, 1, 'KYC Details', '/admin/kyc_details', 0, 1, 1, 'fe fe-airplay', 0),
(76, 1, 'IB Commission Add', '/admin/ibCommission', 0, 1, 1, 'fe fe-airplay', 0),
(77, 1, 'IB Commission Edit', '/admin/ibCommissionEdit', 0, 1, 1, 'fe fe-airplay', 0),
(78, 1, 'View MT5 Accounts', '/admin/view_account_details', 0, 1, 1, 'fe fe-airplay', 0),
(79, 1, 'Client Details', '/admin/client_details', 0, 1, 1, 'fe fe-airplay', 0),
(80, 1, 'Trading Withdrawal Details', '/admin/trading_withdrawal_details', 0, 1, 1, 'fe fe-airplay', 0),
(81, 1, 'Trading Deposit Details', '/admin/trading_deposit_details', 0, 1, 1, 'fe fe-airplay', 0),
(82, 1, 'Wallet Withdrawal Details', '/admin/wallet_withdrawal_details', 0, 1, 1, 'fe fe-airplay', 0),
(83, 1, 'Wallet Deposit Details', '/admin/wallet_deposit_details', 0, 1, 1, 'fe fe-airplay', 0),
(84, 5, 'Bonus', '/admin/bonus', 0, 1, 3, 'fe fe-dollar-sign', 0);

-- --------------------------------------------------------

--
-- Table structure for table `page_categories`
--

CREATE TABLE `page_categories` (
  `page_category_id` int NOT NULL,
  `category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category_desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint NOT NULL,
  `order_by` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_categories`
--

INSERT INTO `page_categories` (`page_category_id`, `category_name`, `category_desc`, `is_active`, `order_by`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'MAIN', '', 1, 10, 1, '2024-09-03 14:29:14', '2024-09-03 14:29:14'),
(2, 'CLIENT', '', 1, 20, 1, '2024-09-03 14:29:17', '2024-09-03 14:29:17'),
(3, 'INTRODUCING BROKER', '', 1, 40, 1, '2024-09-03 14:29:20', '2024-09-03 14:29:20'),
(4, 'ADMIN USERS', '', 1, 60, 1, '2024-09-03 14:29:22', '2024-09-03 14:29:22'),
(5, 'FINANCE', '', 1, 30, 1, '2024-09-03 14:29:24', '2024-09-03 14:29:24'),
(6, 'MT5 CONFIGURATION', '', 1, 50, 1, '2024-09-03 14:29:27', '2024-09-03 14:29:27');

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `payment_id` bigint NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_type` varchar(255) NOT NULL,
  `payment_req` text,
  `payment_reference_id` text,
  `payment_url` text,
  `payment_status` varchar(255) DEFAULT NULL,
  `payment_res` text,
  `initiated_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remarks` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payment_logs`
--

INSERT INTO `payment_logs` (`payment_id`, `payment_amount`, `payment_type`, `payment_req`, `payment_reference_id`, `payment_url`, `payment_status`, `payment_res`, `initiated_by`, `created_at`, `updated_at`, `remarks`) VALUES
(1, '14.00', 'NowPayment', '{\"price_amount\":\"14\",\"price_currency\":\"USD\",\"order_id\":\"nowPay1\",\"success_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=14&payment_id=c4ca4238a0b923820dcc509a6f75849b&status=success\",\"cancel_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=14&payment_id=c4ca4238a0b923820dcc509a6f75849b&status=cancel\"}', 'Wallet', 'https://nowpayments.io/payment/?iid=4504763300', 'Initiated', '', 'lqhmarkets@gmail.com', '2024-09-25 06:07:19', '2024-09-25 06:07:19', 'https://my.lqhmarkets.comnow_payments.php?amount=14&payment_id=c4ca4238a0b923820dcc509a6f75849b&status=success'),
(2, '10.00', 'NowPayment', '{\"price_amount\":\"10\",\"price_currency\":\"USD\",\"order_id\":\"nowPay2\",\"success_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=10&payment_id=c81e728d9d4c2f636f067f89cc14862c&status=success\",\"cancel_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=10&payment_id=c81e728d9d4c2f636f067f89cc14862c&status=cancel\"}', 'Wallet', 'https://nowpayments.io/payment/?iid=5686970690', 'success', '{\"amount\":\"10\",\"payment_id\":\"c81e728d9d4c2f636f067f89cc14862c\",\"status\":\"success\",\"NP_id\":\"5376645061\"}', 'lqhmarkets@gmail.com', '2024-09-25 06:09:11', '2024-09-25 06:12:59', 'https://my.lqhmarkets.comnow_payments.php?amount=10&payment_id=c81e728d9d4c2f636f067f89cc14862c&status=success'),
(3, '10.00', 'NowPayment', '{\"price_amount\":\"10\",\"price_currency\":\"USD\",\"order_id\":\"nowPay3\",\"success_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=10&payment_id=eccbc87e4b5ce2fe28308fd9f2a7baf3&status=success\",\"cancel_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=10&payment_id=eccbc87e4b5ce2fe28308fd9f2a7baf3&status=cancel\"}', 'Wallet', 'https://nowpayments.io/payment/?iid=5232329448', 'Initiated', '', 'lqhmarkets@gmail.com', '2024-09-25 19:41:17', '2024-09-25 19:41:17', 'https://my.lqhmarkets.comnow_payments.php?amount=10&payment_id=eccbc87e4b5ce2fe28308fd9f2a7baf3&status=success'),
(4, '10.00', 'NowPayment', '{\"price_amount\":\"10\",\"price_currency\":\"USD\",\"order_id\":\"nowPay4\",\"success_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=10&payment_id=a87ff679a2f3e71d9181a67b7542122c&status=success\",\"cancel_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=10&payment_id=a87ff679a2f3e71d9181a67b7542122c&status=cancel\"}', 'Wallet', 'https://nowpayments.io/payment/?iid=5522872134', 'Initiated', '', 'lqhmarkets@gmail.com', '2024-09-25 19:44:00', '2024-09-25 19:44:01', 'https://my.lqhmarkets.comnow_payments.php?amount=10&payment_id=a87ff679a2f3e71d9181a67b7542122c&status=success'),
(5, '10.00', 'NowPayment', '{\"price_amount\":\"10\",\"price_currency\":\"USD\",\"order_id\":\"nowPay5\",\"success_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=10&payment_id=e4da3b7fbbce2345d7772b0674a318d5&status=success\",\"cancel_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=10&payment_id=e4da3b7fbbce2345d7772b0674a318d5&status=cancel\"}', 'Wallet', 'https://nowpayments.io/payment/?iid=6333613449', 'Initiated', '', 'lqhmarkets@gmail.com', '2024-09-25 19:45:00', '2024-09-25 19:45:01', 'https://my.lqhmarkets.comnow_payments.php?amount=10&payment_id=e4da3b7fbbce2345d7772b0674a318d5&status=success'),
(6, '15.00', 'NowPayment', '{\"price_amount\":\"15\",\"price_currency\":\"USD\",\"order_id\":\"nowPay6\",\"success_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=15&payment_id=1679091c5a880faf6fb5e6087eb1b2dc&status=success\",\"cancel_url\":\"https:\\/\\/my.lqhmarkets.comnow_payments.php?amount=15&payment_id=1679091c5a880faf6fb5e6087eb1b2dc&status=cancel\"}', 'Wallet', 'https://nowpayments.io/payment/?iid=5927881386', 'success', '{\"amount\":\"15\",\"payment_id\":\"1679091c5a880faf6fb5e6087eb1b2dc\",\"status\":\"success\",\"NP_id\":\"5736825817\"}', 'lqhmarkets@gmail.com', '2024-09-25 19:52:29', '2024-09-25 19:59:01', 'https://my.lqhmarkets.comnow_payments.php?amount=15&payment_id=1679091c5a880faf6fb5e6087eb1b2dc&status=success'),
(7, '1345.00', 'NowPayment', '{\"price_amount\":\"1345\",\"price_currency\":\"USD\",\"order_id\":\"nowPay7\",\"success_url\":\"https:\\/\\/my.lqhmarkets.com\\/now_payments.php?amount=1345&payment_id=8f14e45fceea167a5a36dedd4bea2543&status=success\",\"cancel_url\":\"https:\\/\\/my.lqhmarkets.com\\/now_payments.php?amount=1345&payment_id=8f14e45fceea167a5a36dedd4bea2543&status=cancel\"}', 'Wallet', 'https://nowpayments.io/payment/?iid=5713890408', 'Initiated', '', 'lqhmarkets@gmail.com', '2024-09-25 22:13:58', '2024-09-25 22:13:58', 'https://my.lqhmarkets.com/now_payments.php?amount=1345&payment_id=8f14e45fceea167a5a36dedd4bea2543&status=success'),
(8, '13.00', 'NowPayment', '{\"price_amount\":\"13\",\"price_currency\":\"USD\",\"order_id\":\"nowPay8\",\"success_url\":\"https:\\/\\/my.lqhmarkets.com\\/now_payments.php?amount=13&payment_id=c9f0f895fb98ab9159f51fd0297e236d&status=success\",\"ipn_callback_url\":\"https:\\/\\/my.lqhmarkets.com\\/now_payments.php?amount=13&payment_id=c9f0f895fb98ab9159f51fd0297e236d&status=success&forceToLoad=true\",\"cancel_url\":\"https:\\/\\/my.lqhmarkets.com\\/now_payments.php?amount=13&payment_id=c9f0f895fb98ab9159f51fd0297e236d&status=cancel\"}', 'Wallet', 'https://nowpayments.io/payment/?iid=6161613665', 'Initiated', '', 'lqhmarkets@gmail.com', '2024-09-27 01:44:00', '2024-09-27 01:44:00', 'https://my.lqhmarkets.com/now_payments.php?amount=13&payment_id=c9f0f895fb98ab9159f51fd0297e236d&status=success');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int NOT NULL,
  `role_id` int NOT NULL,
  `page_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `role_id`, `page_id`, `created_by`, `created_at`, `updated_at`) VALUES
(200, 10, '1', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(201, 10, '3', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(202, 10, '4', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(203, 10, '5', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(204, 10, '6', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(205, 10, '7', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(206, 10, '8', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(207, 10, '9', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(208, 10, '10', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(209, 10, '11', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(210, 10, '14', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(211, 10, '15', '78', '2024-08-27 14:11:18', '2024-08-27 14:11:18'),
(212, 19, '12', '78', '2024-08-27 14:11:34', '2024-08-27 14:11:34'),
(213, 19, '13', '78', '2024-08-27 14:11:34', '2024-08-27 14:11:34'),
(214, 19, '24', '78', '2024-08-27 14:11:34', '2024-08-27 14:11:34'),
(215, 19, '25', '78', '2024-08-27 14:11:34', '2024-08-27 14:11:34'),
(216, 19, '26', '78', '2024-08-27 14:11:34', '2024-08-27 14:11:34'),
(217, 19, '27', '78', '2024-08-27 14:11:34', '2024-08-27 14:11:34'),
(218, 19, '29', '78', '2024-08-27 14:11:34', '2024-08-27 14:11:34'),
(219, 1, '1', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(220, 1, '2', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(221, 1, '4', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(222, 1, '6', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(223, 1, '7', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(224, 1, '9', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(225, 1, '10', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(226, 1, '11', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(227, 1, '20', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(228, 1, '21', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(229, 1, '22', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(230, 1, '23', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(231, 1, '57', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(232, 1, '58', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(233, 1, '59', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(234, 1, '60', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(235, 1, '61', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(236, 1, '62', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(237, 1, '63', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(238, 1, '64', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(239, 1, '65', '81', '2024-09-17 08:21:52', '2024-09-17 08:21:52'),
(274, 6, '1', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(275, 6, '2', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(276, 6, '4', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(277, 6, '5', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(278, 6, '6', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(279, 6, '7', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(280, 6, '8', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(281, 6, '9', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(282, 6, '10', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(283, 6, '11', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(284, 6, '57', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(285, 6, '58', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(286, 6, '59', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(287, 6, '60', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(288, 6, '61', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(289, 6, '62', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(290, 6, '63', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(291, 6, '64', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30'),
(292, 6, '65', '81', '2024-09-17 08:27:30', '2024-09-17 08:27:30');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `index` int NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `subtitle` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slag` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `catrgory` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `promotionId` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `relationship_manager`
--

CREATE TABLE `relationship_manager` (
  `id` int NOT NULL,
  `user_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `rm_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `added_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relationship_manager`
--

INSERT INTO `relationship_manager` (`id`, `user_id`, `rm_id`, `added_by`, `created_at`) VALUES
(1, '99', '74', NULL, '2024-08-31 17:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int NOT NULL,
  `role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_desc`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Super Admin Full Access', 1, 1, '2024-08-17 16:14:17', '2024-09-04 00:52:32'),
(2, 'Relationship Manager', 'Manager Access', 1, 1, '2024-08-17 16:14:17', '2024-09-04 00:52:11'),
(3, 'Admin', 'Admin Access', 1, 79, '2024-09-04 00:51:16', '2024-09-04 00:51:16'),
(4, 'Finance', 'Finance Team Access', 1, 79, '2024-09-04 00:51:34', '2024-09-04 00:51:34'),
(5, 'Sales', 'Sales Team Access', 1, 79, '2024-09-04 00:51:57', '2024-09-04 00:51:57'),
(6, 'Knowers', 'knowers', 1, 81, '2024-09-17 08:21:11', '2024-09-17 08:21:11');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`, `updated_at`) VALUES
(1, 'admin_title', 'LIQUIDITY HOUSE', NULL),
(4, 'favicon', 'images/Icon .png', NULL),
(5, 'sidebar_color', '#003e40', NULL),
(6, 'admin_sidebar_logo', 'images/Full Logo.png', NULL),
(7, 'admin_sidebar_dropdown_color', '#ffffff', NULL),
(8, 'admin_sidebar_dropdown_txtcolor', '#18bae2', NULL),
(9, 'admin_sidebar_dropdown_hover', '#04487e', NULL),
(10, 'sidebar_flaticon', '#ffffff', NULL),
(11, 'sidebar_nav_item_name', '#cb1010', NULL),
(12, 'scrollup_background_hover_color', '#f033e0', NULL),
(13, 'breadcrumb_color', '#0e1cdd', NULL),
(14, 'navbar_header_one', '#09497a', NULL),
(15, 'navbar_header_text', '#ffffff', NULL),
(16, 'navbar_hamburger_menu', '#b0cd1d', NULL),
(17, 'dropdown_panel_color', '#d75923', NULL),
(18, 'textColor', 'text-light', NULL),
(19, 'pastel_green_color', '#c7a845', NULL),
(20, 'orange_peel_color', '#567fa0', NULL),
(21, 'copyright_site_name_text', 'https://my.lqhmarkets.com', NULL),
(22, 'admin_settings_update_password_tab_txtcolor', '#37c5e1', NULL),
(23, 'admin_settings_update_password_tab_bgcolor', '#29c72b', NULL),
(24, 'admin_login_submit_button', '#ccc61e', NULL),
(25, 'email_from_address', 'support@lqhmarkets.com', NULL),
(26, 'open_live_tab_color', '#d1b86d', NULL),
(27, 'open_demo_tab_color', '#09497a', NULL),
(28, 'download_platform_tab_color', '#c7a845', NULL),
(29, 'custom_header_page_header', '#1db0cd', NULL),
(30, 'form_check_label_input', '', NULL),
(31, 'download_platform_tab_color', '#c7a845', NULL),
(32, 'custom_header_page_header', '#1db0cd', NULL),
(33, 'form_check_label_input', '', NULL),
(34, 'client_nav_item_menu_icon', '#d2d520', NULL),
(35, 'client_sub_menu_nav_item', '#e96016', NULL),
(36, 'client_login_button_color', '#e01ab5', NULL),
(37, 'client_login_button_hover_txt_color', '#2a14cc', NULL),
(38, 'client_login_button_hover_bgcolor', '#1bd03f', NULL),
(39, 'client_login_button_hover_brcolor', '#d4ea34', NULL),
(40, 'horizontal_menu_bottom_navbar', '#6baab3', NULL),
(41, 'horizontal_menu_title_color_hover', '#d11a8e', NULL),
(42, 'client_registration_page_input_border_color', '#421dc9', NULL),
(43, 'client_registration_button_txtcolor', '#14eb4a', NULL),
(44, 'client_registration_button_bgcolor', '#d4e83b', NULL),
(45, 'client_registration_button_brcolor', '#c94418', NULL),
(46, 'client_registration_button_txtcolor_hover', '#e10e9f', NULL),
(47, 'client_registration_button_bgcolor_hover', '#100dce', NULL),
(48, 'client_registration_button_brcolor_hover', '#1bda3b', NULL),
(49, 'latest_live_trading_account_active_button_txtcolor', '#d1e000', NULL),
(50, 'latest_live_trading_account_active_button_bgcolor', '#ec4d09', NULL),
(51, 'open_live_account_button_class', 'btn-danger', NULL),
(52, 'privacy_policy_hover_color', '#000000', NULL),
(53, 'mt5_windows_platform', 'https://download.metatrader.com/cdn/web/lqh.integrated.ltd/mt5/lqhintegrated5setup.exe', NULL),
(54, 'mt5_android_platform', 'https://download.metatrader.com/cdn/mobile/mt5/android?server=LQHIntegrated-Live', NULL),
(55, 'mt5_ios_platform', 'https://download.metatrader.com/cdn/mobile/mt5/ios?server=LQHIntegrated-Live', NULL),
(56, 'sender_name', 'LQH Markets', NULL),
(57, 'client_favicon', '', NULL),
(58, 'mt5_server_ip', '173.234.139.122', NULL),
(59, 'mt5_server_port', '443', NULL),
(60, 'mt5_server_web_login', '2000', NULL),
(61, 'sender_email_address', 'support@lqhmarkets.com', NULL),
(62, 'mt5_server_web_password', 'Lqh@2024#', NULL),
(63, 'title', '364575467657', NULL),
(64, 'logo_url', '', NULL),
(65, 'client_nav_menu_color', '#e734f4', NULL),
(66, 'client_login_checkbox_input_color', '', NULL),
(67, 'reg_logo_url', '', NULL),
(68, 'gb_textColor', 'text-light', NULL),
(69, 'api_key', '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', NULL),
(70, 'partner_key', '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', NULL),
(71, 'open_live_tab_txt_color', 'text-light', NULL),
(72, 'open_demo_tab_txt_color', 'text-light', NULL),
(73, 'open_platform_tab_txt_color', 'text-light', NULL),
(74, 'admin_sidebar_dropdown_hover_text', '#ffffff', NULL),
(75, 'admin_login_background_image', 'images/admin.jpg', NULL),
(76, 'client_login_background_image', 'images/login_bg4.jpg', NULL),
(77, 'mt5_company_name', 'LQH Integrated Ltd.', NULL),
(78, 'admin_sidebar_logo_dark', 'images/Full Logo.png', NULL),
(79, 'now_payment_api_key', '3ATGP0S-8PD472V-JKV7NKF-N77X4E7', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint NOT NULL,
  `client_id` int DEFAULT NULL,
  `ticket_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_id` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ticket_services` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `discription` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ticket_open` datetime DEFAULT NULL,
  `ticket_close` datetime DEFAULT NULL,
  `Status` enum('Open','Closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `U_Name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `U_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ticket_type_id` int NOT NULL,
  `ticket_status_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `created_user` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `client_id`, `ticket_no`, `subject_name`, `email_id`, `ticket_services`, `discription`, `ticket_open`, `ticket_close`, `Status`, `U_Name`, `U_id`, `ticket_type_id`, `ticket_status_id`, `created_at`, `created_by`, `created_user`) VALUES
(1, NULL, NULL, 'group', 'darkfintechnologies@gmail.com', NULL, 'group not showing', NULL, NULL, 'Open', NULL, NULL, 4, 1, '2024-09-04 03:28:38', 79, NULL),
(2, NULL, NULL, 'Oren Coffey', 'rugmar91@gmail.com', NULL, 'Nihil harum non et n', NULL, NULL, 'Open', NULL, NULL, 5, 1, '2024-09-07 15:31:40', NULL, 100),
(3, NULL, NULL, 'Unity Hardy', 'rugmar91@gmail.com', NULL, 'Ab non non adipisci ', NULL, NULL, 'Open', NULL, NULL, 4, 1, '2024-09-07 15:32:29', NULL, 100),
(4, NULL, NULL, 'Marcia Salinas', 'rugmar91@gmail.com', NULL, 'Earum dignissimos vo', NULL, NULL, 'Open', NULL, NULL, 1, 1, '2024-09-07 15:34:04', NULL, 100),
(5, NULL, NULL, 'Magee Marsh', 'rugmar91@gmail.com', NULL, 'Consequatur Exercit', NULL, NULL, 'Open', NULL, NULL, 2, 1, '2024-09-07 15:36:39', NULL, 100),
(6, NULL, NULL, 'Bert Stone', 'fajife5699@ploncy.com', NULL, 'Ex in voluptas non l', NULL, NULL, 'Open', NULL, NULL, 1, 1, '2024-09-07 15:44:49', NULL, 106),
(7, NULL, NULL, 'jalelel', 'jalelwabou@gmail.com', NULL, 'test\r\n', NULL, NULL, 'Open', NULL, NULL, 2, 1, '2024-09-22 09:42:23', NULL, 98),
(8, NULL, NULL, 'Hello', 'lqhmarkets@gmail.com', NULL, 'Hello Sir Plz Deposit My Funds', NULL, NULL, 'Open', NULL, NULL, 1, 1, '2024-09-22 20:50:08', NULL, 123),
(9, NULL, NULL, 'Hello', 'lqhmarkets@gmail.com', NULL, 'Hello Sir Plz Deposit My Funds', NULL, NULL, 'Open', NULL, NULL, 1, 1, '2024-09-22 20:50:49', NULL, 123),
(10, NULL, NULL, 'RefreshBug', 'lqhmarkets@gmail.com', NULL, 'RefreshBug', NULL, NULL, 'Open', NULL, NULL, 2, 1, '2024-09-22 20:51:24', NULL, 123),
(11, NULL, NULL, 'RefreshBug', 'lqhmarkets@gmail.com', NULL, 'RefreshBug', NULL, NULL, 'Open', NULL, NULL, 2, 1, '2024-09-22 20:51:29', NULL, 123),
(12, NULL, NULL, 'angel', 'furnwest@gmail.com', NULL, 'test', NULL, NULL, 'Open', NULL, NULL, 1, 1, '2024-09-24 03:18:55', NULL, 117),
(13, NULL, NULL, 'Live Account', 'muthuvenkatesh808@gmail.com', NULL, 'Cannot Open Live Account', NULL, NULL, 'Open', NULL, NULL, 4, 1, '2024-09-25 00:33:29', NULL, 107),
(14, NULL, NULL, 'Live Account is not working', 'muthuvenkatesh808@gmail.com', NULL, 'This is description against the Ticket', NULL, NULL, 'Open', NULL, NULL, 1, 1, '2024-09-25 00:39:07', NULL, 107),
(15, NULL, NULL, 'INSTITUTIONAL', 'muthuvenkatesh808@gmail.com', NULL, 'Regarding the Subjected Account Creation', NULL, NULL, 'Open', NULL, NULL, 5, 1, '2024-09-29 23:41:47', NULL, 107),
(16, NULL, NULL, 'INSTITUTIONAL', 'syedmohamedrafi@gmail.com', NULL, 'Regarding the Subjected Account Creation', NULL, NULL, 'Open', NULL, NULL, 5, 1, '2024-10-01 22:57:11', NULL, 96),
(17, NULL, NULL, 'INSTITUTIONAL', 'muthuvenkatesh808@gmail.com', NULL, 'Regarding the Subjected Account Creation', NULL, NULL, 'Open', NULL, NULL, 5, 1, '2024-10-06 03:03:22', NULL, 107);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_assignee`
--

CREATE TABLE `ticket_assignee` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `assignee` int NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assigned_by` int DEFAULT NULL,
  `assigned_user` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_assignee`
--

INSERT INTO `ticket_assignee` (`id`, `ticket_id`, `assignee`, `assigned_at`, `assigned_by`, `assigned_user`) VALUES
(1, 1, 47, '2024-09-04 03:28:38', 79, NULL),
(2, 5, 47, '2024-09-07 15:36:39', NULL, 100),
(3, 6, 47, '2024-09-07 15:44:49', NULL, 106),
(4, 7, 47, '2024-09-22 09:42:23', NULL, 98),
(5, 8, 47, '2024-09-22 20:50:08', NULL, 123),
(6, 9, 47, '2024-09-22 20:50:49', NULL, 123),
(7, 10, 47, '2024-09-22 20:51:24', NULL, 123),
(8, 11, 47, '2024-09-22 20:51:29', NULL, 123),
(9, 12, 47, '2024-09-24 03:18:55', NULL, 117),
(10, 13, 47, '2024-09-25 00:33:30', NULL, 107),
(11, 14, 47, '2024-09-25 00:39:07', NULL, 107),
(12, 15, 47, '2024-09-29 23:41:47', NULL, 107),
(13, 16, 47, '2024-10-01 22:57:11', NULL, 96),
(14, 17, 47, '2024-10-06 03:03:22', NULL, 107);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_followup`
--

CREATE TABLE `ticket_followup` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `attachment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` int DEFAULT NULL,
  `assignee` int DEFAULT NULL,
  `user_type` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_followup`
--

INSERT INTO `ticket_followup` (`id`, `ticket_id`, `remarks`, `attachment`, `status`, `assignee`, `user_type`, `user_id`, `admin_id`, `added_at`) VALUES
(1, 1, 'Ticket Created', NULL, 1, 47, 'admin', NULL, 79, '2024-09-04 03:28:38'),
(2, 1, 'hi', '', NULL, NULL, 'admin', NULL, 79, '2024-09-04 03:29:32'),
(3, 5, 'Ticket Created', NULL, 1, 47, 'user', 100, NULL, '2024-09-07 15:36:39'),
(4, 5, 'ttttttt', '', NULL, NULL, 'user', 100, NULL, '2024-09-07 15:37:18'),
(5, 6, 'Ticket Created', NULL, 1, 47, 'user', 106, NULL, '2024-09-07 15:44:49'),
(6, 7, 'Ticket Created', NULL, 1, 47, 'user', 98, NULL, '2024-09-22 09:42:23'),
(7, 7, 'how can i help\r\n', '', NULL, NULL, 'admin', NULL, 80, '2024-09-22 09:50:00'),
(8, 8, 'Ticket Created', NULL, 1, 47, 'user', 123, NULL, '2024-09-22 20:50:08'),
(9, 8, 'Test', '', NULL, NULL, 'admin', NULL, 81, '2024-09-22 20:50:31'),
(10, 9, 'Ticket Created', NULL, 1, 47, 'user', 123, NULL, '2024-09-22 20:50:49'),
(11, 10, 'Ticket Created', NULL, 1, 47, 'user', 123, NULL, '2024-09-22 20:51:24'),
(12, 11, 'Ticket Created', NULL, 1, 47, 'user', 123, NULL, '2024-09-22 20:51:29'),
(13, 8, 'Ticket \r\n', '', NULL, NULL, 'admin', NULL, 81, '2024-09-22 20:51:45'),
(14, 8, 'bla', '', NULL, NULL, 'user', 123, NULL, '2024-09-22 20:52:24'),
(15, 12, 'Ticket Created', NULL, 1, 47, 'user', 117, NULL, '2024-09-24 03:18:55'),
(16, 13, 'Ticket Created', NULL, 1, 47, 'user', 107, NULL, '2024-09-25 00:33:30'),
(17, 14, 'Ticket Created', NULL, 1, 47, 'user', 107, NULL, '2024-09-25 00:39:07'),
(18, 15, '<p><b>INSTITUTIONAL</b><br>Regarding the Subjected Account Creation</b></p>', NULL, 1, 47, 'user', 107, NULL, '2024-09-29 23:41:47'),
(19, 16, '<p><b>INSTITUTIONAL</b><br>Regarding the Subjected Account Creation</b></p>', NULL, 1, 47, 'user', 96, NULL, '2024-10-01 22:57:11'),
(20, 17, '<p><b>INSTITUTIONAL</b><br>Regarding the Subjected Account Creation</b></p>', NULL, 1, 47, 'user', 107, NULL, '2024-10-06 03:03:22'),
(21, 5, 'test ticket', '/_ticket_attachments/denied.png', NULL, NULL, 'user', 100, NULL, '2024-10-15 12:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_service_setting`
--

CREATE TABLE `ticket_service_setting` (
  `service_id` int NOT NULL,
  `service_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_status`
--

CREATE TABLE `ticket_status` (
  `id` int NOT NULL,
  `ticket_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ticket_label` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_status`
--

INSERT INTO `ticket_status` (`id`, `ticket_status`, `ticket_label`) VALUES
(1, 'Open', 'info'),
(2, 'Pending', 'warning'),
(3, 'Assigned', 'success'),
(4, 'Closed', 'danger');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_types`
--

CREATE TABLE `ticket_types` (
  `id` int NOT NULL,
  `ticket_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_types`
--

INSERT INTO `ticket_types` (`id`, `ticket_type`) VALUES
(1, 'Deposit'),
(2, 'Withdrawal'),
(3, 'Account Verification'),
(4, 'MT5 Support'),
(5, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `total_balance`
--

CREATE TABLE `total_balance` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_amount` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  `withdraw_amount` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  `trading_deposited` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  `trading_withdrawal` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  `refer_commission_amount` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deposit_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `total_balance`
--

INSERT INTO `total_balance` (`id`, `email`, `trade_id`, `deposit_amount`, `withdraw_amount`, `trading_deposited`, `trading_withdrawal`, `refer_commission_amount`, `reg_date`, `deposit_type`, `status`) VALUES
(2, 'jalelwabou@gmail.com', NULL, '0', '0', '100', '0', '0', '2024-09-04 21:31:38', NULL, NULL),
(3, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '1000', '0', '0', '2024-09-04 21:37:33', NULL, NULL),
(4, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '2000', '0', '0', '2024-09-04 22:11:42', NULL, NULL),
(5, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '2000', '0', '0', '2024-09-04 22:11:52', NULL, NULL),
(6, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '500', '0', '0', '2024-09-04 22:13:01', NULL, NULL),
(7, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '0', '0', '0', '2024-09-04 22:20:49', NULL, NULL),
(8, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '0', '0', '0', '2024-09-04 22:21:53', NULL, NULL),
(9, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '0', '-1000', '0', '2024-09-04 22:25:11', NULL, NULL),
(10, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '1000', '0', '0', '2024-09-05 05:45:05', NULL, NULL),
(11, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '0', '-500', '0', '2024-09-05 05:53:54', NULL, NULL),
(12, 'syedmohamedrafi@gmail.com', NULL, '0', '0', '5000', '0', '0', '2024-09-05 19:31:24', NULL, NULL),
(13, 'syedmohamedrafi@gmail.com', '770276', '0', '0', '300', '0', '0', '2024-09-05 20:58:32', 'Internal Transfer', NULL),
(14, 'syedmohamedrafi@gmail.com', '159527', '0', '0', '50', '0', '0', '2024-09-05 20:59:09', 'Internal Transfer', NULL),
(15, 'syedmohamedrafi@gmail.com', '736893', '0', '0', '150', '0', '0', '2024-09-05 21:04:55', 'Internal Transfer', NULL),
(16, 'syedmohamedrafi@gmail.com', '736893', '0', '0', '100', '0', '0', '2024-09-05 21:36:40', 'Internal Transfer', NULL),
(17, 'syedmohamedrafi@gmail.com', '159527', '0', '0', '100', '0', '0', '2024-09-05 21:36:56', 'Internal Transfer', NULL),
(18, 'megastand@protonmail.com', NULL, '11', '0', '0', '0', '0', '2024-09-05 22:16:38', NULL, NULL),
(19, 'megastand@protonmail.com', NULL, '0', '0', '11', '0', '0', '2024-09-06 18:20:11', NULL, NULL),
(20, 'megastand@protonmail.com', NULL, '0', '0', '11', '0', '0', '2024-09-06 18:20:11', NULL, NULL),
(21, 'megastand@protonmail.com', NULL, '0', '0', '11', '0', '0', '2024-09-06 18:20:11', NULL, NULL),
(22, 'megastand@protonmail.com', NULL, '0', '0', '11', '0', '0', '2024-09-06 18:20:11', NULL, NULL),
(23, 'jalelwabou@gmail.com', NULL, '0', '0', '3', '0', '0', '2024-09-07 00:52:50', NULL, NULL),
(24, 'jalelwabou@gmail.com', NULL, '0', '0', '3', '0', '0', '2024-09-07 00:52:50', NULL, NULL),
(25, 'jalelwabou@gmail.com', NULL, '0', '0', '3', '0', '0', '2024-09-07 00:52:50', NULL, NULL),
(26, 'jalelwabou@gmail.com', NULL, '0', '0', '3', '0', '0', '2024-09-07 00:52:50', NULL, NULL),
(27, 'fajife5699@ploncy.com', NULL, '0', '0', '5', '0', '0', '2024-09-07 11:33:21', NULL, NULL),
(28, 'fajife5699@ploncy.com', NULL, '0', '0', '5', '0', '0', '2024-09-07 11:37:13', NULL, NULL),
(29, 'fajife5699@ploncy.com', NULL, '0', '0', '0', '-2', '0', '2024-09-07 11:39:38', NULL, NULL),
(30, 'mediaslush@protonmail.com', NULL, '0', '0', '15', '0', '0', '2024-09-07 23:46:50', NULL, NULL),
(31, 'mediaslush@protonmail.com', NULL, '0', '0', '15', '0', '0', '2024-09-07 23:46:50', NULL, NULL),
(32, 'mediaslush@protonmail.com', NULL, '0', '0', '15', '0', '0', '2024-09-07 23:46:50', NULL, NULL),
(33, 'mediaslush@protonmail.com', NULL, '0', '0', '15', '0', '0', '2024-09-07 23:46:51', NULL, NULL),
(34, 'jalelwabou@gmail.com', NULL, '3', '0', '0', '0', '0', '2024-09-08 02:41:03', NULL, NULL),
(35, 'muthuvenkatesh808@gmail.com', NULL, '15', '0', '0', '0', '0', '2024-09-08 02:44:33', NULL, NULL),
(36, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 02:52:49', NULL, NULL),
(37, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 02:55:51', NULL, NULL),
(38, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 03:04:16', NULL, NULL),
(39, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 03:09:42', NULL, NULL),
(40, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 03:15:23', NULL, NULL),
(41, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 03:29:14', NULL, NULL),
(42, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 03:42:32', NULL, NULL),
(43, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 03:45:37', NULL, NULL),
(44, 'jalelwabou@gmail.com', '283811', '0', '10', '0', '0', '0', '2024-09-08 16:07:49', NULL, '1'),
(45, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 16:08:34', NULL, NULL),
(46, 'jalelwabou@gmail.com', NULL, '20', '0', '0', '0', '0', '2024-09-08 20:00:21', NULL, NULL),
(47, 'jalelwabou@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-08 20:02:04', NULL, NULL),
(48, 'syedmohamedrafi@gmail.com', NULL, '286', '0', '0', '0', '0', '2024-09-08 20:04:33', NULL, NULL),
(49, 'syedmohamedrafi@gmail.com', NULL, '1000', '0', '0', '0', '0', '2024-09-08 20:09:06', NULL, NULL),
(50, 'megastand@protonmail.com', NULL, '13', '0', '0', '0', '0', '2024-09-08 21:13:33', NULL, NULL),
(51, 'megastand@protonmail.com', '716493', '0', '13', '0', '0', '0', '2024-09-08 21:14:10', NULL, '1'),
(52, 'jalelwabou@gmail.com', NULL, '1000', '0', '0', '0', '0', '2024-09-09 11:55:34', NULL, NULL),
(53, 'muthuvenkatesh808@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-09 15:09:47', NULL, NULL),
(54, 'rugmar91@gmail.com', NULL, '1000', '0', '0', '0', '0', '2024-09-10 12:42:04', NULL, NULL),
(55, 'rugmar91@gmail.com', NULL, '1000', '0', '0', '0', '0', '2024-09-10 12:43:17', NULL, NULL),
(56, 'rugmar91@gmail.com', '919514', '0', '100', '0', '0', '0', '2024-09-10 12:44:29', NULL, '1'),
(57, 'rugmar91@gmail.com', '919514', '0', '100', '0', '0', '0', '2024-09-10 12:44:42', NULL, '1'),
(58, 'rugmar91@gmail.com', '282918', '0', '0', '10', '0', '0', '2024-09-10 12:45:30', 'Internal Transfer', NULL),
(59, 'operations@nextstepfunded.com', NULL, '10', '0', '0', '0', '0', '2024-09-11 08:58:26', NULL, NULL),
(60, 'operations@nextstepfunded.com', '426609', '0', '10', '0', '0', '0', '2024-09-11 08:59:45', NULL, '1'),
(61, 'warisahmedbarak@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-12 02:16:03', NULL, NULL),
(62, 'furnwest@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-12 02:33:25', NULL, NULL),
(63, 'tech+2@lqhmarkets.com', NULL, '15', '0', '0', '0', '0', '2024-09-22 04:47:58', NULL, NULL),
(64, 'tech+2@lqhmarkets.com', '876348', '0', '10', '0', '0', '0', '2024-09-22 04:54:10', NULL, '1'),
(65, 'lqhmarkets@gmail.com', NULL, '11', '0', '0', '0', '0', '2024-09-22 18:33:00', NULL, NULL),
(66, 'lqhmarkets@gmail.com', NULL, '0', '10', '0', '0', '0', '2024-09-22 18:35:01', NULL, NULL),
(67, 'lqhmarkets@gmail.com', NULL, '20', '0', '0', '0', '0', '2024-09-22 18:38:59', NULL, NULL),
(68, 'lqhmarkets@gmail.com', NULL, '0', '12', '0', '0', '0', '2024-09-22 20:09:13', NULL, NULL),
(69, 'lqhmarkets@gmail.com', NULL, '0', '9', '0', '0', '0', '2024-09-22 21:27:04', NULL, NULL),
(70, 'lqhmarkets@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-22 21:59:58', NULL, NULL),
(71, 'lqhmarkets@gmail.com', NULL, '0', '10', '0', '0', '0', '2024-09-22 22:01:07', NULL, NULL),
(72, 'furnwest@gmail.com', '435214', '0', '10', '0', '0', '0', '2024-09-24 01:21:27', NULL, '1'),
(73, 'furnwest@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-24 01:31:32', NULL, NULL),
(74, 'lqhmarkets@gmail.com', NULL, '0', '25', '0', '0', '0', '2024-09-26 02:23:27', NULL, NULL),
(75, 'jalelwabou@gmail.com', NULL, '0', '20', '0', '0', '0', '2024-09-26 15:30:00', NULL, NULL),
(76, 'lqhmarkets@gmail.com', NULL, '11', '0', '0', '0', '0', '2024-09-26 19:11:03', NULL, NULL),
(77, 'lqhmarkets@gmail.com', NULL, '0', '11', '0', '0', '0', '2024-09-26 19:11:51', NULL, NULL),
(78, 'lqhmarkets@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-26 22:15:18', NULL, NULL),
(79, 'lqhmarkets@gmail.com', '235001', '0', '10', '0', '0', '0', '2024-09-26 22:17:35', NULL, '1'),
(80, 'rugmar91@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-27 04:05:45', NULL, NULL),
(81, 'rugmar91@gmail.com', NULL, '0', '0', '0', '-10', '0', '2024-09-27 04:13:38', NULL, NULL),
(82, 'lqhmarkets@gmail.com', NULL, '5', '0', '0', '0', '0', '2024-09-27 05:22:43', NULL, NULL),
(83, 'lqhmarkets@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-27 07:15:31', NULL, NULL),
(84, 'lqhmarkets@gmail.com', '235001', '0', '10', '0', '0', '0', '2024-09-27 07:15:54', NULL, '1'),
(85, 'lqhmarkets@gmail.com', NULL, '5', '0', '0', '0', '0', '2024-09-27 07:16:24', NULL, NULL),
(86, 'muthuvenkatesh808@gmail.com', NULL, '0', '0', '1', '0', '0', '2024-09-29 01:42:02', NULL, NULL),
(87, 'lqhmarkets@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-29 01:42:46', NULL, NULL),
(88, 'lqhmarkets@gmail.com', NULL, '10', '0', '0', '0', '0', '2024-09-29 01:45:13', NULL, NULL),
(89, 'lqhmarkets@gmail.com', '235001', '0', '10', '0', '0', '0', '2024-09-29 01:45:52', NULL, '1'),
(90, 'muthuvenkatesh808@gmail.com', NULL, '0', '0', '10', '0', '0', '2024-09-29 01:48:11', NULL, NULL),
(91, 'muthuvenkatesh808@gmail.com', NULL, '2', '0', '0', '0', '0', '2024-09-29 01:49:07', NULL, NULL),
(92, 'lqhmarkets@gmail.com', NULL, '5', '0', '0', '0', '0', '2024-09-29 01:53:19', NULL, NULL),
(93, 'lqhmarkets@gmail.com', NULL, '1', '0', '0', '0', '0', '2024-09-29 01:56:01', NULL, NULL),
(94, 'muthuvenkatesh808@gmail.com', NULL, '3', '0', '0', '0', '0', '2024-09-29 02:05:04', NULL, NULL),
(95, 'lqhmarkets@gmail.com', NULL, '1', '0', '0', '0', '0', '2024-09-29 02:05:36', NULL, NULL),
(96, 'lqhmarkets@gmail.com', NULL, '0', '0', '50', '0', '0', '2024-09-29 02:06:38', NULL, NULL),
(97, 'lqhmarkets@gmail.com', NULL, '50', '0', '0', '0', '0', '2024-09-29 02:06:58', NULL, NULL),
(98, 'lqhmarkets@gmail.com', '235001', '0', '25', '0', '0', '0', '2024-09-29 02:07:13', NULL, '1'),
(99, 'lqhmarkets@gmail.com', NULL, '0', '20', '0', '0', '0', '2024-09-29 02:10:18', NULL, NULL),
(100, 'lqhmarkets@gmail.com', NULL, '5', '0', '0', '0', '0', '2024-09-29 23:57:49', NULL, NULL),
(101, 'abougouche22@gmail.com', NULL, '500', '0', '0', '0', '0', '2024-10-01 13:31:40', NULL, NULL),
(102, 'abougouche22@gmail.com', '493374', '0', '500', '0', '0', '0', '2024-10-01 13:31:57', NULL, '1'),
(103, 'muthuvenkatesh808@gmail.com', NULL, '0', '0', '10', '0', '0', '2024-10-04 19:17:16', NULL, NULL),
(104, 'muthuvenkatesh808@gmail.com', NULL, '0', '0', '10', '0', '0', '2024-10-04 19:17:16', NULL, NULL),
(105, 'muthuvenkatesh808@gmail.com', NULL, '0', '0', '1', '0', '0', '2024-10-04 19:22:35', NULL, NULL),
(106, 'muthuvenkatesh808@gmail.com', NULL, '0', '0', '2.5', '0', '0', '2024-10-04 19:23:06', NULL, NULL),
(107, 'jalelwabou@gmail.com', NULL, '0', '0', '10000', '0', '0', '2024-10-05 15:03:31', NULL, NULL),
(108, 'jalelwabou@gmail.com', NULL, '0', '0', '10000', '0', '0', '2024-10-05 15:06:07', NULL, NULL),
(109, 'jalelwabou@gmail.com', NULL, '9980', '0', '0', '0', '0', '2024-10-05 15:10:37', NULL, NULL),
(110, 'muthuvenkatesh808@gmail.com', '264665', '0', '0', '1', '0', '0', '2024-10-08 16:02:29', 'Internal Transfer', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trade_deposit`
--

CREATE TABLE `trade_deposit` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_currency` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `deposit_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_from` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposted_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int NOT NULL DEFAULT '0',
  `AdminRemark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Js_Admin_Remark_Date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deposit_proof` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trade_deposit`
--

INSERT INTO `trade_deposit` (`id`, `email`, `trade_id`, `deposit_amount`, `deposit_currency`, `deposit_type`, `deposit_from`, `deposted_date`, `Status`, `AdminRemark`, `Js_Admin_Remark_Date`, `deposit_proof`, `created_by`) VALUES
(1, 'syedmohamedrafi@gmail.com', '18256011', '1000', 'USD', 'Other Payments', '', '2024-09-04 21:37:33', 1, 'Just Approved', '2024-09-04 21:37:33', 'assets/uploads/deposit_proof/deposit_proof1725482664.jpg', ''),
(2, 'jalelwabou@gmail.com', '283811', '100', 'USD', 'USDT DEPOSIT', '', '2024-09-04 21:31:38', 1, 'JA', '2024-09-04 21:31:38', '', ''),
(3, 'syedmohamedrafi@gmail.com', '18256011', '2000', 'USD', 'USDT DEPOSIT', '', '2024-09-04 22:11:42', 1, 'JA', '2024-09-04 22:11:42', '', ''),
(4, 'syedmohamedrafi@gmail.com', '18256011', '500', 'USD', 'USDT DEPOSIT', '', '2024-09-04 22:13:01', 1, 'JA', '2024-09-04 22:13:01', '', ''),
(5, 'syedmohamedrafi@gmail.com', '747204', '1000', 'USD', 'USDT DEPOSIT', '', '2024-09-05 05:45:05', 1, 'APPROVE', '2024-09-05 05:45:05', '', ''),
(6, 'megastand@protonmail.com', '472713', '11', 'USD', 'USDT DEPOSIT', '', '2024-09-05 15:40:05', 0, NULL, '2024-09-05 15:40:05', '', ''),
(7, 'syedmohamedrafi@gmail.com', '747204', '5000', 'USD', 'USDT DEPOSIT', '', '2024-09-05 19:31:23', 1, 'Approve\r\n', '2024-09-05 19:31:23', '', ''),
(8, 'syedmohamedrafi@gmail.com', '18256011', '150', 'USD', 'USDT DEPOSIT', '', '2024-09-05 19:25:21', 0, NULL, '2024-09-05 19:25:21', '', ''),
(9, 'syedmohamedrafi@gmail.com', '467660', '150', 'USD', 'Internal Transfer', '747204', '2024-09-05 19:44:44', 0, NULL, '2024-09-05 19:44:44', NULL, ''),
(10, 'syedmohamedrafi@gmail.com', '467660', '1000', 'USD', 'Internal Transfer', '747204', '2024-09-05 20:14:27', 0, NULL, '2024-09-05 20:14:27', NULL, ''),
(11, 'syedmohamedrafi@gmail.com', '159527', '100', 'USD', 'Internal Transfer', '770276', '2024-09-05 20:53:04', 1, NULL, '2024-09-05 20:53:04', NULL, ''),
(12, 'syedmohamedrafi@gmail.com', '159527', '100', 'USD', 'Internal Transfer', '770276', '2024-09-05 20:53:16', 1, NULL, '2024-09-05 20:53:16', NULL, ''),
(13, 'syedmohamedrafi@gmail.com', '770276', '100', 'USD', 'Internal Transfer', '467660', '2024-09-05 20:55:23', 1, NULL, '2024-09-05 20:55:23', NULL, ''),
(14, 'syedmohamedrafi@gmail.com', '770276', '100', 'USD', 'Internal Transfer', '467660', '2024-09-05 20:55:27', 1, NULL, '2024-09-05 20:55:27', NULL, ''),
(15, 'syedmohamedrafi@gmail.com', '467660', '500', 'USD', 'Internal Transfer', '747204', '2024-09-05 20:56:31', 1, NULL, '2024-09-05 20:56:31', NULL, ''),
(16, 'syedmohamedrafi@gmail.com', '467660', '500', 'USD', 'Internal Transfer', '747204', '2024-09-05 20:56:51', 1, NULL, '2024-09-05 20:56:51', NULL, ''),
(17, 'syedmohamedrafi@gmail.com', '770276', '300', 'USD', 'Internal Transfer', '467660', '2024-09-05 20:57:27', 1, NULL, '2024-09-05 20:57:27', NULL, ''),
(18, 'syedmohamedrafi@gmail.com', '770276', '300', 'USD', 'Internal Transfer', '467660', '2024-09-05 20:58:32', 1, NULL, '2024-09-05 20:58:32', NULL, ''),
(19, 'syedmohamedrafi@gmail.com', '159527', '50', 'USD', 'Internal Transfer', '736893', '2024-09-05 20:59:09', 1, NULL, '2024-09-05 20:59:09', NULL, ''),
(20, 'syedmohamedrafi@gmail.com', '736893', '150', 'USD', 'Internal Transfer', '770276', '2024-09-05 21:04:55', 1, NULL, '2024-09-05 21:04:55', NULL, ''),
(21, 'syedmohamedrafi@gmail.com', '736893', '100', 'USD', 'Internal Transfer', '770276', '2024-09-05 21:36:40', 1, NULL, '2024-09-05 21:36:40', NULL, ''),
(22, 'syedmohamedrafi@gmail.com', '159527', '100', 'USD', 'Internal Transfer', '770276', '2024-09-05 21:36:56', 1, NULL, '2024-09-05 21:36:56', NULL, ''),
(23, 'megastand@protonmail.com', NULL, '11', 'USD', 'CryptoChill', NULL, '2024-09-06 18:20:11', 0, NULL, '2024-09-06 18:20:11', NULL, ''),
(24, 'megastand@protonmail.com', NULL, '11', 'USD', 'CryptoChill', NULL, '2024-09-06 18:20:11', 0, NULL, '2024-09-06 18:20:11', NULL, ''),
(25, 'megastand@protonmail.com', NULL, '11', 'USD', 'CryptoChill', NULL, '2024-09-06 18:20:11', 0, NULL, '2024-09-06 18:20:11', NULL, ''),
(26, 'megastand@protonmail.com', NULL, '11', 'USD', 'CryptoChill', NULL, '2024-09-06 18:20:11', 0, NULL, '2024-09-06 18:20:11', NULL, ''),
(27, 'jalelwabou@gmail.com', NULL, '3', 'USD', 'CryptoChill', NULL, '2024-09-07 00:52:50', 0, NULL, '2024-09-07 00:52:50', NULL, ''),
(28, 'jalelwabou@gmail.com', NULL, '3', 'USD', 'CryptoChill', NULL, '2024-09-07 00:52:50', 0, NULL, '2024-09-07 00:52:50', NULL, ''),
(29, 'jalelwabou@gmail.com', NULL, '3', 'USD', 'CryptoChill', NULL, '2024-09-07 00:52:50', 0, NULL, '2024-09-07 00:52:50', NULL, ''),
(30, 'jalelwabou@gmail.com', NULL, '3', 'USD', 'CryptoChill', NULL, '2024-09-07 00:52:50', 0, NULL, '2024-09-07 00:52:50', NULL, ''),
(31, 'fajife5699@ploncy.com', '950387', '5', 'USD', 'CryptoChill', NULL, '2024-09-07 11:37:13', 1, 'test', '2024-09-07 11:37:13', NULL, ''),
(32, 'mediaslush@protonmail.com', NULL, '15', 'USD', 'CryptoChill', NULL, '2024-09-07 23:46:50', 0, NULL, '2024-09-07 23:46:50', NULL, ''),
(33, 'mediaslush@protonmail.com', NULL, '15', 'USD', 'CryptoChill', NULL, '2024-09-07 23:46:50', 0, NULL, '2024-09-07 23:46:50', NULL, ''),
(34, 'mediaslush@protonmail.com', NULL, '15', 'USD', 'CryptoChill', NULL, '2024-09-07 23:46:50', 0, NULL, '2024-09-07 23:46:50', NULL, ''),
(35, 'mediaslush@protonmail.com', NULL, '15', 'USD', 'CryptoChill', NULL, '2024-09-07 23:46:51', 0, NULL, '2024-09-07 23:46:51', NULL, ''),
(36, 'jalelwabou@gmail.com', '283811', '10', 'USD', 'Wallet Transfer', '', '2024-09-29 00:02:10', 0, NULL, '2024-09-29 00:02:10', '', ''),
(38, 'jalelwabou@gmail.com', '283811', '10', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(39, 'jalelwabou@gmail.com', '283811', '10', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(40, 'megastand@protonmail.com', '716493', '13', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(41, 'rugmar91@gmail.com', '919514', '100', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(42, 'rugmar91@gmail.com', '919514', '100', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(43, 'rugmar91@gmail.com', '282918', '10', 'USD', 'Internal Transfer', '919514', '2024-09-10 12:45:30', 1, NULL, '2024-09-10 12:45:30', NULL, ''),
(44, 'operations@nextstepfunded.com', '426609', '10', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(45, 'tech+2@lqhmarkets.com', '876348', '10', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(46, 'furnwest@gmail.com', '435214', '10', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(47, 'lqhmarkets@gmail.com', '235001', '10', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(48, 'lqhmarkets@gmail.com', '235001', '10', 'USD', 'Wallet Transfer', NULL, '2024-09-29 00:02:10', 1, NULL, '2024-09-29 00:02:10', '', ''),
(49, 'muthuvenkatesh808@gmail.com', '573702', '1', 'USD', 'CRM', NULL, '2024-09-29 01:42:02', 1, 'Tester', '2024-09-29 01:42:02', NULL, 'admin@alphabullforex.com'),
(50, 'muthuvenkatesh808@gmail.com', '573702', '10', 'USD', 'CRM', NULL, '2024-09-29 01:48:11', 1, 'De', '2024-09-29 01:48:11', NULL, 'admin@alphabullforex.com'),
(51, 'lqhmarkets@gmail.com', '235001', '50', 'USD', 'CRM', NULL, '2024-09-29 02:06:38', 1, 'Test', '2024-09-29 02:06:38', NULL, 'patel@lqhmarkets.com'),
(52, 'lqhmarkets@gmail.com', '235001', '25', 'USD', 'Wallet Transfer', NULL, '2024-09-29 02:07:13', 1, NULL, '2024-09-29 02:07:13', '', NULL),
(53, 'abougouche22@gmail.com', '493374', '500', 'USD', 'Wallet Transfer', NULL, '2024-10-01 13:31:57', 1, NULL, '2024-10-01 13:31:57', '', NULL),
(54, 'muthuvenkatesh808@gmail.com', '573702', '10', 'USD', 'Bonus', NULL, '2024-10-04 19:17:16', 1, 'Test', '2024-10-04 19:17:16', NULL, 'admin@alphabullforex.com'),
(55, 'muthuvenkatesh808@gmail.com', '573702', '10', 'USD', 'Bonus', NULL, '2024-10-04 19:17:16', 1, 'Test', '2024-10-04 19:17:16', NULL, 'admin@alphabullforex.com'),
(56, 'muthuvenkatesh808@gmail.com', '573702', '1', 'USD', 'Bonus', NULL, '2024-10-04 19:22:35', 1, 'SecondTest', '2024-10-04 19:22:35', NULL, 'admin@alphabullforex.com'),
(57, 'muthuvenkatesh808@gmail.com', '573702', '2.5', 'USD', 'Bonus', NULL, '2024-10-04 19:23:06', 1, 'BonusTest', '2024-10-04 19:23:06', NULL, 'admin@alphabullforex.com'),
(58, 'jalelwabou@gmail.com', '165718', '10000', 'USD', 'Bonus', NULL, '2024-10-05 15:03:31', 1, 'Bonus', '2024-10-05 15:03:31', NULL, 'jalel@lqhmarkets.com'),
(59, 'jalelwabou@gmail.com', '165718', '10000', 'USD', 'Bonus', NULL, '2024-10-05 15:06:07', 1, 'Welcome Bonus', '2024-10-05 15:06:07', NULL, 'jalel@lqhmarkets.com'),
(60, 'muthuvenkatesh808@gmail.com', '264665', '1', 'USD', 'Internal Transfer', '573702', '2024-10-08 16:02:29', 1, NULL, '2024-10-08 16:02:29', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trade_withdrawal`
--

CREATE TABLE `trade_withdrawal` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdrawal_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_to` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wallet_qr` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Status` int NOT NULL DEFAULT '0',
  `AdminRemark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Js_Admin_Remark_Date` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trade_withdrawal`
--

INSERT INTO `trade_withdrawal` (`id`, `email`, `trade_id`, `withdrawal_amount`, `withdraw_type`, `withdraw_to`, `wallet_qr`, `withdraw_date`, `Status`, `AdminRemark`, `Js_Admin_Remark_Date`, `created_by`) VALUES
(1, 'syedmohamedrafi@gmail.com', '18256011', '1000', 'Other Withdrawal', 'Testing WD', '', '2024-09-04 22:14:11', 1, 'JA', NULL, NULL),
(2, 'syedmohamedrafi@gmail.com', '18256011', '1000', 'USDT Withdrawal', 'ctfvygbuhnijomkpl[', 'assets/uploads/wallet_qr_code/wallet_qr1725488611.jpg', '2024-09-04 22:23:31', 1, 'JA', NULL, NULL),
(3, 'syedmohamedrafi@gmail.com', '747204', '500', 'USDT Withdrawal', 'asfwefwqf', 'assets/uploads/wallet_qr_code/wallet_qr1725515580.png', '2024-09-05 05:53:00', 1, 'Approve', NULL, NULL),
(4, 'syedmohamedrafi@gmail.com', '770276', '100', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:53:04', 2, 'Test', NULL, NULL),
(5, 'syedmohamedrafi@gmail.com', '770276', '100', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:53:16', 0, NULL, NULL, NULL),
(6, 'syedmohamedrafi@gmail.com', '467660', '100', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:55:23', 0, NULL, NULL, NULL),
(7, 'syedmohamedrafi@gmail.com', '467660', '100', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:55:27', 0, NULL, NULL, NULL),
(8, 'syedmohamedrafi@gmail.com', '747204', '500', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:56:31', 0, NULL, NULL, NULL),
(9, 'syedmohamedrafi@gmail.com', '747204', '500', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:56:51', 0, NULL, NULL, NULL),
(10, 'syedmohamedrafi@gmail.com', '467660', '300', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:57:27', 0, NULL, NULL, NULL),
(11, 'syedmohamedrafi@gmail.com', '467660', '300', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:58:32', 0, NULL, NULL, NULL),
(12, 'syedmohamedrafi@gmail.com', '736893', '50', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 20:59:09', 0, NULL, NULL, NULL),
(13, 'syedmohamedrafi@gmail.com', '770276', '150', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 21:04:55', 0, NULL, NULL, NULL),
(14, 'syedmohamedrafi@gmail.com', '770276', '100', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 21:36:40', 0, NULL, NULL, NULL),
(15, 'syedmohamedrafi@gmail.com', '770276', '100', 'Internal Transfer', 'Trading Account', NULL, '2024-09-05 21:36:56', 0, NULL, NULL, NULL),
(16, 'fajife5699@ploncy.com', '950387', '2', 'Other Withdrawal', 'test', '', '2024-09-07 11:38:43', 1, 'hello test', NULL, NULL),
(17, 'jalelwabou@gmail.com', '283811', '10', 'Wallet Withdrawal', '', '', '2024-09-08 16:08:34', 1, NULL, NULL, NULL),
(18, 'jalelwabou@gmail.com', '283811', '20', 'Wallet Withdrawal', '', '', '2024-09-08 20:00:21', 1, NULL, NULL, NULL),
(19, 'jalelwabou@gmail.com', '283811', '10', 'Wallet Withdrawal', '', '', '2024-09-08 20:02:04', 1, NULL, NULL, NULL),
(20, 'syedmohamedrafi@gmail.com', '770276', '286', 'Wallet Withdrawal', '', '', '2024-09-08 20:04:33', 1, NULL, NULL, NULL),
(21, 'syedmohamedrafi@gmail.com', '770276', '1000', 'Wallet Withdrawal', '', '', '2024-09-08 20:09:06', 1, NULL, NULL, NULL),
(22, 'jalelwabou@gmail.com', '283811', '1000', 'Wallet Withdrawal', '', '', '2024-09-09 11:55:34', 1, NULL, NULL, NULL),
(23, 'rugmar91@gmail.com', '919514', '10', 'Internal Transfer', 'Trading Account', NULL, '2024-09-10 12:45:30', 1, 'Test Approval', NULL, NULL),
(24, 'furnwest@gmail.com', '435214', '10', 'Wallet Withdrawal', '', '', '2024-09-24 01:31:32', 1, NULL, NULL, NULL),
(25, 'rugmar91@gmail.com', '919514', '10', 'Wallet Withdrawal', '', '', '2024-09-27 04:05:45', 1, NULL, NULL, NULL),
(26, 'lqhmarkets@gmail.com', '235001', '5', 'Wallet Withdrawal', '', '', '2024-09-27 05:22:43', 1, NULL, NULL, NULL),
(27, 'lqhmarkets@gmail.com', '235001', '5', 'Wallet Withdrawal', '', '', '2024-09-27 07:16:24', 1, NULL, NULL, NULL),
(28, 'lqhmarkets@gmail.com', '235001', '10', 'Wallet Withdrawal', '', '', '2024-09-29 01:42:46', 1, NULL, NULL, NULL),
(29, 'muthuvenkatesh808@gmail.com', '573702', '2', 'CRM', NULL, NULL, '2024-09-29 01:49:07', 1, '', NULL, 'admin@alphabullforex.com'),
(30, 'lqhmarkets@gmail.com', '235001', '5', 'Wallet Withdrawal', '', '', '2024-09-29 01:53:19', 1, NULL, NULL, NULL),
(31, 'lqhmarkets@gmail.com', '235001', '1', 'Wallet Withdrawal', '', '', '2024-09-29 01:56:01', 1, NULL, NULL, NULL),
(32, 'muthuvenkatesh808@gmail.com', '573702', '3', 'Wallet Withdrawal', '', '', '2024-09-29 02:05:04', 1, NULL, NULL, NULL),
(33, 'lqhmarkets@gmail.com', '235001', '1', 'Wallet Withdrawal', '', '', '2024-09-29 02:05:35', 1, NULL, NULL, NULL),
(34, 'lqhmarkets@gmail.com', '235001', '50', 'Wallet Withdrawal', '', '', '2024-09-29 02:06:58', 1, NULL, NULL, NULL),
(35, 'lqhmarkets@gmail.com', '235001', '5', 'Wallet Withdrawal', '', '', '2024-09-29 23:57:49', 1, NULL, NULL, NULL),
(36, 'abougouche22@gmail.com', '493374', '500', 'Wallet Withdrawal', '', '', '2024-10-01 13:31:40', 1, NULL, NULL, NULL),
(37, 'jalelwabou@gmail.com', '165718', '9980', 'Wallet Withdrawal', '', '', '2024-10-05 15:10:37', 1, NULL, NULL, NULL),
(38, 'muthuvenkatesh808@gmail.com', '573702', '1', 'Internal Transfer', '264665', NULL, '2024-10-08 16:02:29', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trading_bonus`
--

CREATE TABLE `trading_bonus` (
  `id` int NOT NULL,
  `trade_id` int DEFAULT NULL,
  `bonus_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comments` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Registered_Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userdocuments`
--

CREATE TABLE `userdocuments` (
  `id` bigint NOT NULL,
  `uid` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doc_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doc_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `date` datetime DEFAULT NULL,
  `verified_by` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `verified_on` datetime DEFAULT NULL,
  `uploaded_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'client',
  `note` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `uploader_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noEmail',
  `client_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'client'
) ENGINE=MyISAM AVG_ROW_LENGTH=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_deposit`
--

CREATE TABLE `wallet_deposit` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposted_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int NOT NULL DEFAULT '0',
  `AdminRemark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Js_Admin_Remark_Date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `btc_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `callback_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `callback_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet_deposit`
--

INSERT INTO `wallet_deposit` (`id`, `email`, `deposit_amount`, `deposit_type`, `company_bank`, `client_bank`, `transaction_id`, `deposted_date`, `Status`, `AdminRemark`, `Js_Admin_Remark_Date`, `btc_amount`, `currency_type`, `created_at`, `updated_at`, `callback_data`, `callback_code`) VALUES
(1, 'jalelwabou@gmail.com', '100', 'USDT Deposit', NULL, NULL, NULL, '2024-09-04 21:14:24', 0, NULL, '2024-09-04 21:14:24', NULL, 'USDT TRC20', '2024-09-04 23:14:24', '2024-09-04 23:14:24', NULL, NULL),
(2, 'megastand@protonmail.com', '11', 'USDT Deposit', NULL, NULL, NULL, '2024-09-05 22:16:38', 1, 'approve', '2024-09-05 22:16:38', NULL, 'USDT TRC20', '2024-09-05 17:40:23', '2024-09-05 17:40:23', NULL, NULL),
(3, 'jalelwabou@gmail.com', '3', 'CryptoChill', 'CryptoChill', NULL, '1725763179', '2024-09-08 02:41:03', 1, NULL, '2024-09-08 02:41:03', NULL, 'USD', '2024-09-08 04:41:03', '2024-09-08 04:41:03', NULL, NULL),
(5, 'muthuvenkatesh808@gmail.com', '15', 'CryptoChill', 'CryptoChill', NULL, '1725763459', '2024-09-08 02:44:33', 1, NULL, '2024-09-08 02:44:33', NULL, 'USD', '2024-09-08 04:44:33', '2024-09-08 04:44:33', 'data98867457658', 'Code234567'),
(6, 'jalelwabou@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '1725763935', '2024-09-08 02:52:49', 1, NULL, '2024-09-08 02:52:49', NULL, 'USD', '2024-09-08 04:52:49', '2024-09-08 04:52:49', NULL, NULL),
(8, 'jalelwabou@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '1725764096', '2024-09-08 02:55:51', 1, NULL, '2024-09-08 02:55:51', NULL, 'USD', '2024-09-08 04:55:51', '2024-09-08 04:55:51', NULL, NULL),
(10, 'jalelwabou@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '1725764623', '2024-09-08 03:04:16', 1, NULL, '2024-09-08 03:04:16', NULL, 'USD', '2024-09-08 05:04:16', '2024-09-08 05:04:16', 'null', 'null'),
(14, 'jalelwabou@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '1725764954', '2024-09-08 03:09:41', 1, NULL, '2024-09-08 03:09:41', NULL, 'USD', '2024-09-08 05:09:41', '2024-09-08 05:09:41', 'null', 'null'),
(18, 'jalelwabou@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '17257652677236738000', '2024-09-08 03:15:23', 1, NULL, '2024-09-08 03:15:23', NULL, 'USD', '2024-09-08 05:15:23', '2024-09-08 05:15:23', 'null', 'null'),
(22, 'jalelwabou@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172576612146860260000', '2024-09-08 03:29:14', 1, NULL, '2024-09-08 03:29:14', NULL, 'USD', '2024-09-08 05:29:14', '2024-09-08 05:29:14', 'null', 'null'),
(26, 'jalelwabou@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172576690030444150000', '2024-09-08 03:42:32', 1, NULL, '2024-09-08 03:42:32', NULL, 'USD', '2024-09-08 05:42:32', '2024-09-08 05:42:32', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"3F9RCqwa7PrRgv7SRHSYfMVhns2emD2XPW\",\"network\":\"BTC\",\"addresses\":{\"BTC\":\"3F9RCqwa7PrRgv7SRHSYfMVhns2emD2XPW\"},\"qr\":{\"BTC\":\"true\"},\"id\":\"022AB325472847D7B94BBD413BEBD8F0\",\"payment_amount\":\"0.00018411\",\"payment_currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"lightning\":\"true\",\"kind\":\"crypto\",\"rate_usd\":\"54313.5550000000000000\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"54316.615\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00018411\",\"amount_usd\":\"10\",\"rate_usd\":\"54316.615\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"62.045\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.16117334\",\"amount_usd\":\"10\",\"rate_usd\":\"62.045\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2282.115\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.0043819\",\"amount_usd\":\"10\",\"rate_usd\":\"2282.115\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0005830064260526\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.994173\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0005830064260526\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"10\",\"amount_usd\":\"10\",\"rate_usd\":\"1\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0005830064260526\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.994173\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0005830064260526\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"10\",\"amount_usd\":\"10\",\"rate_usd\":\"1\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDT\",\"USDC-BSC\",\"USDC\",\"USDCE-POLYGON\",\"USDT-TRX\",\"USDC-POLYGON\",\"USDC-ARBITRUM\",\"USDT-POLYGON\",\"USDT-SOL\",\"BUSD-ETH\",\"USDT-TON\",\"BUSD\",\"USDC-CELO\",\"USDC-BASE\",\"USDT-BSC\",\"USDT-ARBITRUM\",\"USDC-TRX\",\"USDT-CELO\",\"L-USDT\",\"GUSD\",\"TUSD\",\"CUSD-CELO\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/022AB325472847D7B94BBD413BEBD8F0\\/\",\"expires_at\":\"2024-09-08T03:57:09.607545+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"0.000184110000000000\",\"missing_amount\":\"0\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(27, 'jalelwabou@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172576708936963650000', '2024-09-08 03:45:37', 1, NULL, '2024-09-08 03:45:37', NULL, 'USD', '2024-09-08 05:45:37', '2024-09-08 05:45:37', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"35f5xagnWNS6iJvqCtvRx1w1kgJB4rPyqR\",\"network\":\"BTC\",\"addresses\":{\"BTC\":\"35f5xagnWNS6iJvqCtvRx1w1kgJB4rPyqR\"},\"qr\":{\"BTC\":\"true\"},\"id\":\"55012D34A22C49CA88888B0693A0C173\",\"payment_amount\":\"0.00018416\",\"payment_currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"lightning\":\"true\",\"kind\":\"crypto\",\"rate_usd\":\"54301.1600000000000000\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"54301.655\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00018416\",\"amount_usd\":\"10\",\"rate_usd\":\"54301.655\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"62.021576109754896\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.16123421\",\"amount_usd\":\"10\",\"rate_usd\":\"62.021576109754895\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2281.94\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00438224\",\"amount_usd\":\"10\",\"rate_usd\":\"2281.94\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0005349209465983\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.994654\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0005349209465983\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"0.999786\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"10.00214\",\"amount_usd\":\"10\",\"rate_usd\":\"0.999786\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0005349209465983\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.994654\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0005349209465983\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"0.999786\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"10.00214\",\"amount_usd\":\"10\",\"rate_usd\":\"0.999786\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDC\",\"USDC-BSC\",\"BUSD-ETH\",\"USDC-CELO\",\"USDC-ARBITRUM\",\"USDT-POLYGON\",\"USDT\",\"CUSD-CELO\",\"L-USDT\",\"USDC-POLYGON\",\"USDT-CELO\",\"GUSD\",\"USDC-TRX\",\"USDCE-POLYGON\",\"USDT-TRX\",\"USDT-TON\",\"BUSD\",\"USDT-ARBITRUM\",\"USDT-BSC\",\"USDC-BASE\",\"USDT-SOL\",\"TUSD\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/55012D34A22C49CA88888B0693A0C173\\/\",\"expires_at\":\"2024-09-08T04:00:02.949176+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"0.000184160000000000\",\"missing_amount\":\"0\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(28, 'megastand@protonmail.com', '13', 'CryptoChill', 'CryptoChill', NULL, '172582995928547720000', '2024-09-08 21:13:33', 1, NULL, '2024-09-08 21:13:33', NULL, 'USD', '2024-09-08 23:13:33', '2024-09-08 23:13:33', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"13\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"13.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"0x46c7598dcF566e3E6E5cBc9Ec3a586d0Cb412f17\",\"network\":\"POLYGON\",\"addresses\":{\"POLYGON\":\"0x46c7598dcF566e3E6E5cBc9Ec3a586d0Cb412f17\"},\"qr\":{\"POLYGON\":\"true\"},\"id\":\"6EC38C0880B7474BA0125EA0A5A902B4\",\"payment_amount\":\"13\",\"payment_currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"54519.56379220815\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00023845\",\"amount_usd\":\"13\",\"rate_usd\":\"54519.563792208155\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"60.83849144377971\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.21368051\",\"amount_usd\":\"13\",\"rate_usd\":\"60.83849144377971\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2284.947374247646\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00568941\",\"amount_usd\":\"13\",\"rate_usd\":\"2284.9473742476463\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0011434170405296\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"12.985153\",\"amount_usd\":\"13\",\"rate_usd\":\"1.0011434170405295\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"13\",\"amount_usd\":\"13\",\"rate_usd\":\"1\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0011434170405296\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"12.985153\",\"amount_usd\":\"13\",\"rate_usd\":\"1.0011434170405295\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"13\",\"amount_usd\":\"13\",\"rate_usd\":\"1\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"13.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDC\",\"USDC-BSC\",\"BUSD-ETH\",\"USDC-CELO\",\"USDC-ARBITRUM\",\"USDT-POLYGON\",\"USDT\",\"CUSD-CELO\",\"L-USDT\",\"USDC-POLYGON\",\"USDT-CELO\",\"GUSD\",\"USDC-TRX\",\"USDCE-POLYGON\",\"USDT-TRX\",\"USDT-TON\",\"BUSD\",\"USDT-ARBITRUM\",\"USDT-BSC\",\"USDC-BASE\",\"USDT-SOL\",\"TUSD\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/6EC38C0880B7474BA0125EA0A5A902B4\\/\",\"expires_at\":\"2024-09-08T21:28:06.994929+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"13.000000000000000000\",\"missing_amount\":\"0\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(29, 'muthuvenkatesh808@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172589447668187330000', '2024-09-09 15:09:47', 1, NULL, '2024-09-09 15:09:47', NULL, 'USD', '2024-09-09 17:09:47', '2024-09-09 17:09:47', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TZBLS7B2iUQzewJtLd7urPgaxQD5sRMZfi\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TZBLS7B2iUQzewJtLd7urPgaxQD5sRMZfi\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"4270FD4685FF43FBA376E69A6F7FC17B\",\"payment_amount\":\"9.986588\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0018098491104188\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"55228.2129023682\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00018107\",\"amount_usd\":\"10\",\"rate_usd\":\"55228.2129023682\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"60.08141734328665\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.16644081\",\"amount_usd\":\"10\",\"rate_usd\":\"60.0814173432866491\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2290.270890339821\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.0043663\",\"amount_usd\":\"10\",\"rate_usd\":\"2290.2708903398208864\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0013430291207468\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.986588\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0013430291207469\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1.001\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.99001\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0009999999999999\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0013430291207468\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.986588\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0013430291207469\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1.001\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"9.99001\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0009999999999999\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDT\",\"USDC-BSC\",\"USDC\",\"USDCE-POLYGON\",\"USDT-TRX\",\"USDC-POLYGON\",\"USDC-ARBITRUM\",\"USDT-POLYGON\",\"USDT-SOL\",\"BUSD-ETH\",\"USDT-TON\",\"BUSD\",\"USDC-CELO\",\"USDC-BASE\",\"USDT-BSC\",\"USDT-ARBITRUM\",\"USDC-TRX\",\"USDT-CELO\",\"L-USDT\",\"GUSD\",\"TUSD\",\"CUSD-CELO\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/4270FD4685FF43FBA376E69A6F7FC17B\\/\",\"expires_at\":\"2024-09-09T15:23:06.927905+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"10.000000000000000000\",\"missing_amount\":\"-0.013412000000000000\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(30, 'rugmar91@gmail.com', '1000', 'CryptoChill', 'CryptoChill', NULL, '172597211058671600000', '2024-09-10 12:42:04', 1, NULL, '2024-09-10 12:42:04', NULL, 'USD', '2024-09-10 14:42:04', '2024-09-10 14:42:04', '\"abc\"', '\"abc\"'),
(31, 'rugmar91@gmail.com', '1000', 'CryptoChill', 'CryptoChill', NULL, '172597218211554300000', '2024-09-10 12:43:17', 1, NULL, '2024-09-10 12:43:17', NULL, 'USD', '2024-09-10 14:43:17', '2024-09-10 14:43:17', '\"abc\"', '\"abc\"'),
(32, 'operations@nextstepfunded.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172604506493536440000', '2024-09-11 08:58:26', 1, NULL, '2024-09-11 08:58:26', NULL, 'USD', '2024-09-11 10:58:26', '2024-09-11 10:58:26', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"3QuKbphqcPoQFdckRkbu1UyPxqFTLbM2Gg\",\"network\":\"BTC\",\"addresses\":{\"BTC\":\"3QuKbphqcPoQFdckRkbu1UyPxqFTLbM2Gg\"},\"qr\":{\"BTC\":\"true\"},\"id\":\"A44E434DBE154746A5A2CB9270380E0A\",\"payment_amount\":\"0.00017723\",\"payment_currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"lightning\":\"true\",\"kind\":\"crypto\",\"rate_usd\":\"56425.7516055114225761\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"56424.287307066785\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00017723\",\"amount_usd\":\"10\",\"rate_usd\":\"56424.2873070667825\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"61.64055203304103\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.16223086\",\"amount_usd\":\"10\",\"rate_usd\":\"61.640552033041035\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2326.156509478011\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00429894\",\"amount_usd\":\"10\",\"rate_usd\":\"2326.156509478011\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.000074944376625\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.999251\",\"amount_usd\":\"10\",\"rate_usd\":\"1.000074944376625\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1.0001734157714428\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.998266\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0001734157714427\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.000074944376625\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.999251\",\"amount_usd\":\"10\",\"rate_usd\":\"1.000074944376625\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1.0001734157714428\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"9.998266\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0001734157714427\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDC-BSC\",\"L-USDT\",\"USDT-SOL\",\"USDC-POLYGON\",\"CUSD-CELO\",\"GUSD\",\"TUSD\",\"USDT\",\"BUSD\",\"USDC\",\"USDC-ARBITRUM\",\"USDC-CELO\",\"USDT-TON\",\"USDT-POLYGON\",\"USDCE-POLYGON\",\"USDT-TRX\",\"USDT-BSC\",\"USDT-ARBITRUM\",\"BUSD-ETH\",\"USDC-BASE\",\"USDC-TRX\",\"USDT-CELO\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/A44E434DBE154746A5A2CB9270380E0A\\/\",\"expires_at\":\"2024-09-11T09:12:59.608045+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"0.000177230000000000\",\"missing_amount\":\"0\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(33, 'warisahmedbarak@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172610721199723740000', '2024-09-12 02:16:03', 1, NULL, '2024-09-12 02:16:03', NULL, 'USD', '2024-09-12 04:16:03', '2024-09-12 04:16:03', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TEKcWnTPGr17VBtm5gbjYUKSxLFwfaaYMZ\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TEKcWnTPGr17VBtm5gbjYUKSxLFwfaaYMZ\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"A8F42EA1BC1F4D87A8A49EE50BF4D17A\",\"payment_amount\":\"9.993588\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0006483503972236\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"58023.03031829889\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00017235\",\"amount_usd\":\"10\",\"rate_usd\":\"58023.030318298883\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"62.19778076287846\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.16077744\",\"amount_usd\":\"10\",\"rate_usd\":\"62.19778076287846\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2368.249347121381\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00422253\",\"amount_usd\":\"10\",\"rate_usd\":\"2368.2493471213813\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0006415613908894\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.993588\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0006415613908895\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1.001\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.99001\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0009999999999999\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0006415613908894\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.993588\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0006415613908895\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1.001\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"9.99001\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0009999999999999\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDC-POLYGON\",\"USDC-ARBITRUM\",\"BUSD\",\"USDCE-POLYGON\",\"BUSD-ETH\",\"USDC\",\"USDC-BSC\",\"USDT-BSC\",\"USDC-CELO\",\"USDT-ARBITRUM\",\"USDT\",\"L-USDT\",\"USDT-POLYGON\",\"USDC-TRX\",\"USDC-BASE\",\"CUSD-CELO\",\"USDT-CELO\",\"TUSD\",\"GUSD\",\"USDT-TON\",\"USDT-TRX\",\"USDT-SOL\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/A8F42EA1BC1F4D87A8A49EE50BF4D17A\\/\",\"expires_at\":\"2024-09-12T02:28:56.176878+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"9.993588000000000000\",\"missing_amount\":\"0\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(34, 'furnwest@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172610801338579200000', '2024-09-12 02:33:25', 1, NULL, '2024-09-12 02:33:25', NULL, 'USD', '2024-09-12 04:33:25', '2024-09-12 04:33:25', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TBQ767Her1LxusAm5nQUzMHUidGSaib6Pu\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TBQ767Her1LxusAm5nQUzMHUidGSaib6Pu\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"49C0FECA13CC4495B14933691D451D3D\",\"payment_amount\":\"9.993224\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0006628592411127\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"57895.99431556539\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00017272\",\"amount_usd\":\"10\",\"rate_usd\":\"57895.99431556539\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"61.99485169149576\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.16130372\",\"amount_usd\":\"10\",\"rate_usd\":\"61.99485169149576\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2363.9139525972096\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00423027\",\"amount_usd\":\"10\",\"rate_usd\":\"2363.91395259720975\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0006780191910305\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.993224\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0006780191910304\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"10\",\"amount_usd\":\"10\",\"rate_usd\":\"1\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0006780191910305\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.993224\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0006780191910304\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"10\",\"amount_usd\":\"10\",\"rate_usd\":\"1\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDC-POLYGON\",\"USDC-ARBITRUM\",\"BUSD\",\"USDCE-POLYGON\",\"BUSD-ETH\",\"USDC\",\"USDC-BSC\",\"USDT-BSC\",\"USDC-CELO\",\"USDT-ARBITRUM\",\"USDT\",\"L-USDT\",\"USDT-POLYGON\",\"USDC-TRX\",\"USDC-BASE\",\"CUSD-CELO\",\"USDT-CELO\",\"TUSD\",\"GUSD\",\"USDT-TON\",\"USDT-TRX\",\"USDT-SOL\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/49C0FECA13CC4495B14933691D451D3D\\/\",\"expires_at\":\"2024-09-12T02:43:36.268454+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"10.000000000000000000\",\"missing_amount\":\"-0.006776000000000000\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(35, 'tech+2@lqhmarkets.com', '15', 'CryptoChill', 'CryptoChill', NULL, '172697993032511320000', '2024-09-22 04:47:58', 1, NULL, '2024-09-22 04:47:58', NULL, 'USD', '2024-09-22 06:47:58', '2024-09-22 06:47:58', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"15\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"15.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TQyQ39bxgRFYhNAEEzM1Ez7T6pvjgWWbVa\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TQyQ39bxgRFYhNAEEzM1Ez7T6pvjgWWbVa\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"271F5236F61E494C817FE4801DBDFC59\",\"payment_amount\":\"14.994636\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0003278969942621\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"63147.345\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00023754\",\"amount_usd\":\"15\",\"rate_usd\":\"63147.345\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"67.16987649827556\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.22331439\",\"amount_usd\":\"15\",\"rate_usd\":\"67.16987649827555\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2597.485\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00577482\",\"amount_usd\":\"15\",\"rate_usd\":\"2597.485\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0003577568660842\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"14.994636\",\"amount_usd\":\"15\",\"rate_usd\":\"1.0003577568660842\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"0.999998\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"15.00003\",\"amount_usd\":\"15\",\"rate_usd\":\"0.9999980000000001\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0003577568660842\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"14.994636\",\"amount_usd\":\"15\",\"rate_usd\":\"1.0003577568660842\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"0.999998\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"15.00003\",\"amount_usd\":\"15\",\"rate_usd\":\"0.9999980000000001\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"15.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDT-POLYGON\",\"GUSD\",\"USDC-POLYGON\",\"USDC-ARBITRUM\",\"USDT-SOL\",\"USDC-TRX\",\"USDT\",\"USDT-BSC\",\"USDCE-POLYGON\",\"CUSD-CELO\",\"USDC-BASE\",\"USDT-ARBITRUM\",\"USDT-TRX\",\"USDC\",\"BUSD-ETH\",\"USDC-BSC\",\"USDT-CELO\",\"L-USDT\",\"BUSD\",\"USDC-CELO\",\"TUSD\",\"USDT-TON\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/271F5236F61E494C817FE4801DBDFC59\\/\",\"expires_at\":\"2024-09-22T04:54:03.106805+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"15.000000000000000000\",\"missing_amount\":\"-0.005364000000000000\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(36, 'lqhmarkets@gmail.com', '11', 'CryptoChill', 'CryptoChill', NULL, '172702994720757780000', '2024-09-22 18:33:00', 1, NULL, '2024-09-22 18:33:00', NULL, 'USD', '2024-09-22 20:33:00', '2024-09-22 20:33:00', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"11\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"11.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TW4eWsDERGUXUE85BpXfZRPzQg8trYGmMG\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TW4eWsDERGUXUE85BpXfZRPzQg8trYGmMG\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"826EE32926944EA8A14CF9A6AB1BB799\",\"payment_amount\":\"10.99675\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0003065261879029\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"62970.335711223386\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00017469\",\"amount_usd\":\"11\",\"rate_usd\":\"62970.3357112233850486\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"67.76\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.16233766\",\"amount_usd\":\"11\",\"rate_usd\":\"67.76\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2580.585\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.0042626\",\"amount_usd\":\"11\",\"rate_usd\":\"2580.585\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0002955530438373\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"10.99675\",\"amount_usd\":\"11\",\"rate_usd\":\"1.0002955530438374\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"11\",\"amount_usd\":\"11\",\"rate_usd\":\"1\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0002955530438373\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"10.99675\",\"amount_usd\":\"11\",\"rate_usd\":\"1.0002955530438374\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"11\",\"amount_usd\":\"11\",\"rate_usd\":\"1\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"11.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDC-BSC\",\"USDC-BASE\",\"USDT-TRX\",\"USDT-BSC\",\"USDC-CELO\",\"BUSD-ETH\",\"USDC-ARBITRUM\",\"USDT\",\"TUSD\",\"GUSD\",\"L-USDT\",\"USDT-POLYGON\",\"USDC-POLYGON\",\"BUSD\",\"CUSD-CELO\",\"USDT-TON\",\"USDT-ARBITRUM\",\"USDT-SOL\",\"USDT-CELO\",\"USDC-TRX\",\"USDCE-POLYGON\",\"USDC\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/826EE32926944EA8A14CF9A6AB1BB799\\/\",\"expires_at\":\"2024-09-22T18:47:42.375769+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"11.000000000000000000\",\"missing_amount\":\"-0.003250000000000000\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(37, 'lqhmarkets@gmail.com', '20', 'CryptoChill', 'CryptoChill', NULL, '172703031959215300000', '2024-09-22 18:38:59', 1, NULL, '2024-09-22 18:38:59', NULL, 'USD', '2024-09-22 20:38:59', '2024-09-22 20:38:59', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"20\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"20.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TQfPhX7QzBPvY5GjDsMr3nCQRnWeov63vW\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TQfPhX7QzBPvY5GjDsMr3nCQRnWeov63vW\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"A92A70B179364E6B8383220CE4B287FB\",\"payment_amount\":\"19.99405\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0002975966750443\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"62973.49\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00031759\",\"amount_usd\":\"20\",\"rate_usd\":\"62973.49\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"67.79567044125004\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.29500409\",\"amount_usd\":\"20\",\"rate_usd\":\"67.7956704412500324\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2582.320107044479\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00774497\",\"amount_usd\":\"20\",\"rate_usd\":\"2582.3201070444789709\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0002975966750443\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"19.99405\",\"amount_usd\":\"20\",\"rate_usd\":\"1.0002975966750443\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"20\",\"amount_usd\":\"20\",\"rate_usd\":\"1\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0002975966750443\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"19.99405\",\"amount_usd\":\"20\",\"rate_usd\":\"1.0002975966750443\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"20\",\"amount_usd\":\"20\",\"rate_usd\":\"1\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"20.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDT-POLYGON\",\"GUSD\",\"USDC-POLYGON\",\"USDC-ARBITRUM\",\"USDT-SOL\",\"USDC-TRX\",\"USDT\",\"USDT-BSC\",\"USDCE-POLYGON\",\"CUSD-CELO\",\"USDC-BASE\",\"USDT-ARBITRUM\",\"USDT-TRX\",\"USDC\",\"BUSD-ETH\",\"USDC-BSC\",\"USDT-CELO\",\"L-USDT\",\"BUSD\",\"USDC-CELO\",\"TUSD\",\"USDT-TON\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/A92A70B179364E6B8383220CE4B287FB\\/\",\"expires_at\":\"2024-09-22T18:53:46.712940+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"20.000000000000000000\",\"missing_amount\":\"-0.005950000000000000\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"');
INSERT INTO `wallet_deposit` (`id`, `email`, `deposit_amount`, `deposit_type`, `company_bank`, `client_bank`, `transaction_id`, `deposted_date`, `Status`, `AdminRemark`, `Js_Admin_Remark_Date`, `btc_amount`, `currency_type`, `created_at`, `updated_at`, `callback_data`, `callback_code`) VALUES
(38, 'lqhmarkets@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172704236786469930000', '2024-09-22 21:59:58', 1, NULL, '2024-09-22 21:59:58', NULL, 'USD', '2024-09-22 23:59:58', '2024-09-22 23:59:58', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TKkKjJLystAaNjBcFcMwxiZ9tLpbenZZDb\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TKkKjJLystAaNjBcFcMwxiZ9tLpbenZZDb\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"22BF8D09139D40B1A4AE608697FE0CA4\",\"payment_amount\":\"10.000743\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0008530025889445\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"62854.5\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.0001591\",\"amount_usd\":\"10\",\"rate_usd\":\"62854.5\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"67.42274489913835\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.1483179\",\"amount_usd\":\"10\",\"rate_usd\":\"67.4227448991383442\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2561.38504717371\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00390414\",\"amount_usd\":\"10\",\"rate_usd\":\"2561.3850471737101769\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"0.9999256646847772\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"10.000743\",\"amount_usd\":\"10\",\"rate_usd\":\"0.9999256646847772\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"0.9996187257482061\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"10.003814\",\"amount_usd\":\"10\",\"rate_usd\":\"0.9996187257482061\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"0.9999256646847772\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"10.000743\",\"amount_usd\":\"10\",\"rate_usd\":\"0.9999256646847772\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"0.9996187257482061\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"10.003814\",\"amount_usd\":\"10\",\"rate_usd\":\"0.9996187257482061\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDT-POLYGON\",\"GUSD\",\"USDC-POLYGON\",\"USDC-ARBITRUM\",\"USDT-SOL\",\"USDC-TRX\",\"USDT\",\"USDT-BSC\",\"USDCE-POLYGON\",\"CUSD-CELO\",\"USDC-BASE\",\"USDT-ARBITRUM\",\"USDT-TRX\",\"USDC\",\"BUSD-ETH\",\"USDC-BSC\",\"USDT-CELO\",\"L-USDT\",\"BUSD\",\"USDC-CELO\",\"TUSD\",\"USDT-TON\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/22BF8D09139D40B1A4AE608697FE0CA4\\/\",\"expires_at\":\"2024-09-22T22:14:38.022380+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"10.100000000000000000\",\"missing_amount\":\"-0.099257000000000000\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(39, 'lqhmarkets@gmail.com', '10.00', 'Now Payment', NULL, NULL, NULL, '2024-09-25 04:12:59', 1, NULL, '2024-09-25 04:12:59', NULL, 'USD', '2024-09-25 06:12:59', '2024-09-25 06:12:59', NULL, NULL),
(40, 'lqhmarkets@gmail.com', '15.00', 'Now Payment', NULL, NULL, NULL, '2024-09-25 17:59:01', 1, NULL, '2024-09-25 17:59:01', NULL, 'USD', '2024-09-25 19:59:01', '2024-09-25 19:59:01', NULL, NULL),
(41, 'lqhmarkets@gmail.com', '11', 'CryptoChill', 'CryptoChill', NULL, '172737780844412300000', '2024-09-26 19:11:03', 1, NULL, '2024-09-26 19:11:03', NULL, 'USD', '2024-09-26 21:11:03', '2024-09-26 21:11:03', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"11\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"11.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"31iDz3tQMNKarh1ajTXP71euw6uDC8hccK\",\"network\":\"BTC\",\"addresses\":{\"BTC\":\"31iDz3tQMNKarh1ajTXP71euw6uDC8hccK\"},\"qr\":{\"BTC\":\"true\"},\"id\":\"08424878DEC0459981C4C49B66B24CE8\",\"payment_amount\":\"0.0001686\",\"payment_currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"lightning\":\"true\",\"kind\":\"crypto\",\"rate_usd\":\"65254.5530286564600000\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"65243.15810285335\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.0001686\",\"amount_usd\":\"11\",\"rate_usd\":\"65243.15810285335\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"68.24980254565217\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.16117263\",\"amount_usd\":\"11\",\"rate_usd\":\"68.24980254565217\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2654.7410571827722\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00414353\",\"amount_usd\":\"11\",\"rate_usd\":\"2654.7410571827720273\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"0.9999159376870883\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"11.000925\",\"amount_usd\":\"11\",\"rate_usd\":\"0.9999159376870883\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"0.999661\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"11.00373\",\"amount_usd\":\"11\",\"rate_usd\":\"0.999661\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"0.9999159376870883\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"11.000925\",\"amount_usd\":\"11\",\"rate_usd\":\"0.9999159376870883\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"0.999661\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"11.00373\",\"amount_usd\":\"11\",\"rate_usd\":\"0.999661\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"11.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDCE-POLYGON\",\"USDC-ARBITRUM\",\"USDC-CELO\",\"USDC\",\"BUSD-ETH\",\"USDC-BSC\",\"BUSD\",\"USDC-TRX\",\"USDT-CELO\",\"USDT-BSC\",\"L-USDT\",\"USDT-ARBITRUM\",\"USDT\",\"USDT-TON\",\"TUSD\",\"USDC-BASE\",\"USDC-POLYGON\",\"USDT-TRX\",\"GUSD\",\"USDT-POLYGON\",\"CUSD-CELO\",\"USDT-SOL\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/08424878DEC0459981C4C49B66B24CE8\\/\",\"expires_at\":\"2024-09-26T19:25:14.674822+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"0.000168600000000000\",\"missing_amount\":\"0\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(42, 'lqhmarkets@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172738888943654700000', '2024-09-26 22:15:18', 1, NULL, '2024-09-26 22:15:18', NULL, 'USD', '2024-09-27 00:15:18', '2024-09-27 00:15:18', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"3NSLNwmUkJ77vmVxAbKfrmgRbxMCjKCNkc\",\"network\":\"BTC\",\"addresses\":{\"BTC\":\"3NSLNwmUkJ77vmVxAbKfrmgRbxMCjKCNkc\"},\"qr\":{\"BTC\":\"true\"},\"id\":\"EA945FAFB7364F54AB39864F5505D084\",\"payment_amount\":\"0.00015348\",\"payment_currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"lightning\":\"true\",\"kind\":\"crypto\",\"rate_usd\":\"65167.0913763917030000\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"65153.731121705554\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00015348\",\"amount_usd\":\"10\",\"rate_usd\":\"65153.731121705553\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"68.37209965800146\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.14625849\",\"amount_usd\":\"10\",\"rate_usd\":\"68.3720996580014623\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2644.7205017814736\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00378112\",\"amount_usd\":\"10\",\"rate_usd\":\"2644.7205017814735591\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0000262135241145\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.999738\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0000262135241145\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1.000308105671139\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.99692\",\"amount_usd\":\"10\",\"rate_usd\":\"1.000308105671139\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0000262135241145\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.999738\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0000262135241145\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1.000308105671139\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"9.99692\",\"amount_usd\":\"10\",\"rate_usd\":\"1.000308105671139\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDT-ARBITRUM\",\"USDT-BSC\",\"USDC\",\"USDT-TRX\",\"USDT-POLYGON\",\"USDC-ARBITRUM\",\"USDT\",\"TUSD\",\"USDC-TRX\",\"GUSD\",\"L-USDT\",\"USDT-TON\",\"BUSD-ETH\",\"USDC-BASE\",\"USDT-CELO\",\"USDC-BSC\",\"USDT-SOL\",\"USDC-POLYGON\",\"USDCE-POLYGON\",\"CUSD-CELO\",\"BUSD\",\"USDC-CELO\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/EA945FAFB7364F54AB39864F5505D084\\/\",\"expires_at\":\"2024-09-26T22:29:56.976754+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"0.000153480000000000\",\"missing_amount\":\"0\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(43, 'lqhmarkets@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172742129919263240000', '2024-09-27 07:15:31', 1, NULL, '2024-09-27 07:15:31', NULL, 'USD', '2024-09-27 09:15:31', '2024-09-27 09:15:31', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TFyUyxGUqBJAhEPRxzndnbvW9Gkdf5U863\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TFyUyxGUqBJAhEPRxzndnbvW9Gkdf5U863\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"6FDCE0C11B9D4A28A523E1EF052D39A5\",\"payment_amount\":\"9.999003\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0000997065178936\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"65398.23386087962\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00015291\",\"amount_usd\":\"10\",\"rate_usd\":\"65398.2338608796177579\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"69.06\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.14480162\",\"amount_usd\":\"10\",\"rate_usd\":\"69.06\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2659.78\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00375971\",\"amount_usd\":\"10\",\"rate_usd\":\"2659.78\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0000997065178936\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.999003\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0000997065178936\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"1.0005247230759626\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.994756\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0005247230759627\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0000997065178936\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.999003\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0000997065178936\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"1.0005247230759626\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"9.994756\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0005247230759627\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"USDT-ARBITRUM\",\"USDT-BSC\",\"USDC\",\"USDT-TRX\",\"USDT-POLYGON\",\"USDC-ARBITRUM\",\"USDT\",\"TUSD\",\"USDC-TRX\",\"GUSD\",\"L-USDT\",\"USDT-TON\",\"BUSD-ETH\",\"USDC-BASE\",\"USDT-CELO\",\"USDC-BSC\",\"USDT-SOL\",\"USDC-POLYGON\",\"USDCE-POLYGON\",\"CUSD-CELO\",\"BUSD\",\"USDC-CELO\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/6FDCE0C11B9D4A28A523E1EF052D39A5\\/\",\"expires_at\":\"2024-09-27T07:30:07.086436+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"10.000000000000000000\",\"missing_amount\":\"-0.000997000000000000\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(44, 'lqhmarkets@gmail.com', '10', 'CryptoChill', 'CryptoChill', NULL, '172757425850578100000', '2024-09-29 01:45:13', 1, NULL, '2024-09-29 01:45:13', NULL, 'USD', '2024-09-29 03:45:13', '2024-09-29 03:45:13', '{\"account\":\"bc38bb94-e7da-4b56-a07a-cfe3f06bab03\",\"profile\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"onSuccess\":\"true\",\"onCancel\":\"\",\"btnPrefix\":\"cryptochill\",\"product\":\"Deposit To MT5: LQH Integrated Ltd.\",\"amount\":\"10\",\"currency\":\"USD\",\"payment\":{\"invoice_amount\":\"10.00000000\",\"invoice_currency\":{\"slug\":\"united-states-dollar\",\"title\":\"United States Dollar\",\"full_title\":\"United States Dollar\",\"symbol\":\"USD\",\"lightning\":\"false\",\"kind\":\"fiat\",\"rate_usd\":\"1.0000000000000000\",\"network\":\"\",\"coin\":\"USD\"},\"lightning\":\"false\",\"address\":\"TJ3bB14Ry6sT4wBFt3bvGCTRkEqr4SwwkY\",\"network\":\"TRX\",\"addresses\":{\"TRX\":\"TJ3bB14Ry6sT4wBFt3bvGCTRkEqr4SwwkY\"},\"qr\":{\"TRX\":\"true\"},\"id\":\"9C6FEC2B0D554061ACAD204B0AEC3F14\",\"payment_amount\":\"9.995014\",\"payment_currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"lightning\":\"false\",\"kind\":\"crypto\",\"rate_usd\":\"1.0004988482180013\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"currency_rates\":[{\"currency\":{\"slug\":\"bitcoin\",\"title\":\"Bitcoin\",\"full_title\":\"Bitcoin\",\"symbol\":\"BTC\",\"kind\":\"crypto\",\"rate_usd\":\"65751.735\",\"network\":\"BTC\",\"coin\":\"BTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00015209\",\"amount_usd\":\"10\",\"rate_usd\":\"65751.735\"},{\"currency\":{\"slug\":\"litecoin\",\"title\":\"Litecoin\",\"full_title\":\"Litecoin\",\"symbol\":\"LTC\",\"kind\":\"crypto\",\"rate_usd\":\"69.485\",\"network\":\"LTC\",\"coin\":\"LTC\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.14391595\",\"amount_usd\":\"10\",\"rate_usd\":\"69.485\"},{\"currency\":{\"slug\":\"ethereum\",\"title\":\"Ethereum\",\"full_title\":\"Ethereum\",\"symbol\":\"ETH\",\"kind\":\"crypto\",\"rate_usd\":\"2668.38\",\"network\":\"ETH\",\"coin\":\"ETH\",\"platform\":\"\",\"platform_full\":\"\"},\"amount\":\"0.00374759\",\"amount_usd\":\"10\",\"rate_usd\":\"2668.38\"},{\"currency\":{\"slug\":\"tether\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT\",\"kind\":\"crypto\",\"rate_usd\":\"1.0004988482180013\",\"network\":\"ETH\",\"coin\":\"USDT\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"9.995014\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0004988482180013\"},{\"currency\":{\"slug\":\"usd-coin\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC\",\"kind\":\"crypto\",\"rate_usd\":\"0.99992\",\"network\":\"ETH\",\"coin\":\"USDC\",\"platform\":\"Ethereum\",\"platform_full\":\"Ethereum, ERC20\"},\"amount\":\"10.0008\",\"amount_usd\":\"10\",\"rate_usd\":\"0.99992\"},{\"currency\":{\"slug\":\"tether-tron\",\"title\":\"Tether\",\"full_title\":\"Tether\",\"symbol\":\"USDT-TRX\",\"kind\":\"crypto\",\"rate_usd\":\"1.0004988482180013\",\"network\":\"TRX\",\"coin\":\"USDT\",\"platform\":\"Tron\",\"platform_full\":\"Tron, TRC20\"},\"amount\":\"9.995014\",\"amount_usd\":\"10\",\"rate_usd\":\"1.0004988482180013\"},{\"currency\":{\"slug\":\"usd-coin-polygon\",\"title\":\"USD Coin\",\"full_title\":\"USD Coin\",\"symbol\":\"USDC-POLYGON\",\"kind\":\"crypto\",\"rate_usd\":\"0.99992\",\"network\":\"POLYGON\",\"coin\":\"USDC\",\"platform\":\"Polygon\",\"platform_full\":\"Polygon, ERC-20\"},\"amount\":\"10.0008\",\"amount_usd\":\"10\",\"rate_usd\":\"0.99992\"}],\"account\":{\"id\":\"BC38BB94E7DA4B56A07ACFE3F06BAB03\",\"name\":\"Lqh integrated\"},\"profile_has_integration\":\"false\",\"integration_onramper\":\"false\",\"integration_kado\":\"false\",\"invoice_amount_with_extra_fee\":\"10.00000000\",\"stablecoin_precision\":\"6\",\"stable_coins\":[\"BUSD\",\"USDT-TON\",\"USDC-BASE\",\"GUSD\",\"USDT-ARBITRUM\",\"CUSD-CELO\",\"USDT-BSC\",\"USDC-BSC\",\"USDC\",\"L-USDT\",\"USDC-CELO\",\"USDT-POLYGON\",\"USDC-POLYGON\",\"USDT-CELO\",\"USDT-TRX\",\"USDC-TRX\",\"USDT-SOL\",\"USDT\",\"TUSD\",\"USDC-ARBITRUM\",\"USDCE-POLYGON\",\"BUSD-ETH\"],\"payment_url\":\"https:\\/\\/cryptochill.com\\/invoice\\/9C6FEC2B0D554061ACAD204B0AEC3F14\\/\",\"expires_at\":\"2024-09-29T01:59:53.036396+00:00\",\"is_expired\":\"false\",\"invoice_expiration\":\"900\",\"paid_amount\":\"9.995014000000000000\",\"missing_amount\":\"0\",\"payment_status\":\"processing\",\"status\":\"processing\",\"notes\":\"Deposit To MT5: LQH Integrated Ltd.\"}}', '\"success\"'),
(45, 'muthuvenkatesh808@gmail.com', '3', 'Internal Transfer', NULL, NULL, NULL, '2024-09-29 02:05:04', 1, NULL, '2024-09-29 02:05:04', NULL, NULL, '2024-09-29 04:05:04', '2024-09-29 04:05:04', NULL, NULL),
(46, 'lqhmarkets@gmail.com', '1', 'Internal Transfer', NULL, NULL, NULL, '2024-09-29 02:05:36', 1, NULL, '2024-09-29 02:05:36', NULL, NULL, '2024-09-29 04:05:36', '2024-09-29 04:05:36', NULL, NULL),
(47, 'lqhmarkets@gmail.com', '50', 'Internal Transfer', NULL, NULL, NULL, '2024-09-29 02:06:58', 1, NULL, '2024-09-29 02:06:58', NULL, NULL, '2024-09-29 04:06:58', '2024-09-29 04:06:58', NULL, NULL),
(48, 'lqhmarkets@gmail.com', '5', 'Internal Transfer', NULL, NULL, NULL, '2024-09-29 23:57:49', 1, NULL, '2024-09-29 23:57:49', NULL, NULL, '2024-09-30 01:57:49', '2024-09-30 01:57:49', NULL, NULL),
(49, 'abougouche22@gmail.com', '500', 'Internal Transfer', NULL, NULL, NULL, '2024-10-01 13:31:40', 1, NULL, '2024-10-01 13:31:40', NULL, NULL, '2024-10-01 15:31:40', '2024-10-01 15:31:40', NULL, NULL),
(50, 'jalelwabou@gmail.com', '9980', 'Internal Transfer', NULL, NULL, NULL, '2024-10-05 15:10:37', 1, NULL, '2024-10-05 15:10:37', NULL, NULL, '2024-10-05 17:10:37', '2024-10-05 17:10:37', NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `wallet_deposits`
-- (See below for the actual view)
--
CREATE TABLE `wallet_deposits` (
`deposit_amount` varchar(100)
,`deposit_date` timestamp
,`deposit_type` varchar(100)
,`email` varchar(50)
,`fullname` varchar(150)
,`id` varchar(15)
,`number` varchar(100)
,`raw_id` int
,`status` int
,`trade_id` varchar(100)
,`TYPE` varchar(6)
);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_withdraw`
--

CREATE TABLE `wallet_withdraw` (
  `id` int NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_amount` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `withdraw_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` int NOT NULL DEFAULT '0',
  `payout_req` longtext COLLATE utf8mb4_general_ci,
  `payout_res` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `AdminRemark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Js_Admin_Remark_Date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wallet_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wallet_qr` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet_withdraw`
--

INSERT INTO `wallet_withdraw` (`id`, `email`, `withdraw_amount`, `withdraw_type`, `company_bank`, `client_bank`, `transaction_id`, `withdraw_date`, `Status`, `payout_req`, `payout_res`, `AdminRemark`, `Js_Admin_Remark_Date`, `wallet_id`, `wallet_qr`, `client_note`, `created_at`, `updated_at`) VALUES
(1, 'jalelwabou@gmail.com', '10', 'Internal Transfer', NULL, NULL, '283811', '2024-09-08 16:07:49', 1, NULL, NULL, NULL, '2024-09-08 16:07:49', NULL, NULL, NULL, '2024-09-08 18:07:49', '2024-09-08 18:07:49'),
(2, 'megastand@protonmail.com', '13', 'Internal Transfer', NULL, NULL, '716493', '2024-09-08 21:14:10', 1, NULL, NULL, NULL, '2024-09-08 21:14:10', NULL, NULL, NULL, '2024-09-08 23:14:10', '2024-09-08 23:14:10'),
(3, 'rugmar91@gmail.com', '100', 'Internal Transfer', NULL, NULL, '919514', '2024-09-10 12:44:29', 1, NULL, NULL, NULL, '2024-09-10 12:44:29', NULL, NULL, NULL, '2024-09-10 14:44:29', '2024-09-10 14:44:29'),
(4, 'rugmar91@gmail.com', '100', 'Internal Transfer', NULL, NULL, '919514', '2024-09-10 12:44:42', 1, NULL, NULL, NULL, '2024-09-10 12:44:42', NULL, NULL, NULL, '2024-09-10 14:44:42', '2024-09-10 14:44:42'),
(5, 'operations@nextstepfunded.com', '10', 'Internal Transfer', NULL, NULL, '426609', '2024-09-11 08:59:45', 1, NULL, NULL, NULL, '2024-09-11 08:59:45', NULL, NULL, NULL, '2024-09-11 10:59:45', '2024-09-11 10:59:45'),
(6, 'warisahmedbarak@gmail.com', '10', 'Other Withdrawal', NULL, NULL, NULL, '2024-09-12 02:16:40', 0, NULL, NULL, NULL, '2024-09-12 02:16:40', NULL, NULL, 'Checking withdrawal process ', '2024-09-12 04:16:40', '2024-09-12 04:16:40'),
(7, 'jalelwabou@gmail.com', '20', 'Wallet Withdrawal', NULL, '4', '1', '2024-09-26 15:30:00', 1, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"jalelwabou@gmail.com\\\"}\",\"reference_id\":\"LQHPO0007\",\"kind\":\"USDT-TRX\",\"recipients\":[{\"amount\":\"20\",\"currency\":\"USDT\",\"address\":\"TTAQXDQXnVz3qambZwcVe79wu7hbgGJkXT\",\"notes\":\"jalelwabou@gmail.com_7\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727364599000}', '{\"result\": {\"id\": \"1614bf0b-002d-45c0-acac-bf087109cfed\", \"kind\": \"USDT-TRX\", \"txid\": null, \"created_at\": \"2024-09-26T15:30:00.253891+00:00\", \"executed_at\": null, \"profile_id\": \"f759196c-cf55-4618-b277-9f311ff3efcb\", \"wallet_id\": \"613861d7-72e7-475d-9bd5-ee66cb529d5e\", \"confirmations\": 0, \"status\": \"draft\", \"network\": \"mainnet\", \"amount\": {\"total\": \"2E+1\", \"network_fee\": \"3E+1\"}, \"network_fee_preset\": \"normal\", \"network_fee_pays\": \"merchant\", \"network_fee\": 0, \"passthrough\": \"{\\\"user_id\\\":\\\"jalelwabou@gmail.com\\\"}\", \"reference_id\": \"LQHPO0007\", \"recipients\": [{\"id\": \"a00d95b9-9ede-4909-b6b3-bc93c0f3b65d\", \"address\": \"TTAQXDQXnVz3qambZwcVe79wu7hbgGJkXT\", \"amount\": {\"requested\": {\"amount\": \"2E+1\", \"currency\": \"USDT\"}, \"received\": {\"amount\": \"2E+1\", \"currency\": \"USDT-TRX\"}}, \"custom_fee\": null, \"notes\": \"jalelwabou@gmail.com_7\"}]}}', '1', '2024-09-26 15:30:00', NULL, NULL, NULL, '2024-09-20 14:30:28', '2024-09-26 17:30:00'),
(8, 'tech+2@lqhmarkets.com', '10', 'Wallet Withdrawal', NULL, NULL, NULL, '2024-09-22 04:52:27', 0, NULL, '{\"result\": \"error\", \"reason\": \"MissingRequiredFields\", \"message\": \"Missing required fields: kind\"}', NULL, '2024-09-22 04:52:27', NULL, NULL, NULL, '2024-09-22 06:48:32', '2024-09-22 06:52:27'),
(9, 'tech+2@lqhmarkets.com', '10', 'Internal Transfer', NULL, NULL, '876348', '2024-09-22 04:54:10', 1, NULL, NULL, NULL, '2024-09-22 04:54:10', NULL, NULL, NULL, '2024-09-22 06:54:10', '2024-09-22 06:54:10'),
(10, 'lqhmarkets@gmail.com', '10', 'Wallet Withdrawal', NULL, '6', 'test', '2024-09-22 18:35:01', 1, NULL, '{\"result\": {\"id\": \"ea28eb88-5e9e-43be-9209-4b101cdc8626\", \"kind\": \"ETH_USDT\", \"txid\": null, \"created_at\": \"2024-09-22T18:35:00.940053+00:00\", \"executed_at\": null, \"profile_id\": \"f759196c-cf55-4618-b277-9f311ff3efcb\", \"wallet_id\": \"ccc5ac78-a9b6-4462-b322-9d41ea7d12a9\", \"confirmations\": 0, \"status\": \"new\", \"network\": \"mainnet\", \"amount\": {\"total\": \"1E+1\", \"network_fee\": \"0.003669464763364895\"}, \"network_fee_preset\": \"normal\", \"network_fee_pays\": \"merchant\", \"network_fee\": 57, \"passthrough\": \"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\", \"reference_id\": \"LQHPO0010\", \"recipients\": [{\"id\": \"51ff0535-5668-4f8f-b3c5-1cb2183bcda8\", \"address\": \"0xECfE937f86539BE3bfB776076FC80a427b96b080\", \"amount\": {\"requested\": {\"amount\": \"1E+1\", \"currency\": \"USDT\"}, \"received\": {\"amount\": \"1E+1\", \"currency\": \"USDT\"}}, \"custom_fee\": null, \"notes\": \"lqhmarkets@gmail.com_10\"}]}}', 'test', '2024-09-22 18:35:01', NULL, NULL, NULL, '2024-09-22 20:34:21', '2024-09-22 20:35:01'),
(11, 'lqhmarkets@gmail.com', '12', 'Wallet Withdrawal', NULL, '7', 'F', '2024-09-22 20:09:13', 1, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\",\"reference_id\":\"LQHPO0011\",\"kind\":\"USDT-TRX\",\"recipients\":[{\"amount\":\"12\",\"currency\":\"USDT\",\"address\":\"TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq\",\"notes\":\"lqhmarkets@gmail.com_11\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727035753000}', '{\"result\": {\"id\": \"94d18882-2e31-410a-88ab-449d190bdbd0\", \"kind\": \"USDT-TRX\", \"txid\": null, \"created_at\": \"2024-09-22T20:09:13.692607+00:00\", \"executed_at\": null, \"profile_id\": \"f759196c-cf55-4618-b277-9f311ff3efcb\", \"wallet_id\": \"613861d7-72e7-475d-9bd5-ee66cb529d5e\", \"confirmations\": 0, \"status\": \"draft\", \"network\": \"mainnet\", \"amount\": {\"total\": \"12\", \"network_fee\": \"3E+1\"}, \"network_fee_preset\": \"normal\", \"network_fee_pays\": \"merchant\", \"network_fee\": 0, \"passthrough\": \"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\", \"reference_id\": \"LQHPO0011\", \"recipients\": [{\"id\": \"498f84a6-c0ec-4864-95fc-d2831ea34d99\", \"address\": \"TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq\", \"amount\": {\"requested\": {\"amount\": \"12\", \"currency\": \"USDT\"}, \"received\": {\"amount\": \"12\", \"currency\": \"USDT-TRX\"}}, \"custom_fee\": null, \"notes\": \"lqhmarkets@gmail.com_11\"}]}}', 'C', '2024-09-22 20:09:13', NULL, NULL, NULL, '2024-09-22 20:40:10', '2024-09-22 22:09:13'),
(12, 'lqhmarkets@gmail.com', '11', 'Wallet Withdrawal', NULL, '7', NULL, '2024-09-22 20:05:31', 0, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\",\"reference_id\":\"LQHPO0012\",\"kind\":\"USDT-TRX\",\"recipients\":[{\"amount\":\"11\",\"currency\":\"USDT\",\"address\":\"TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq\",\"notes\":\"lqhmarkets@gmail.com_12\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727035530000}', '{\"result\": \"error\", \"reason\": \"InsufficientFunds\", \"message\": \"Insufficient funds for network fee\"}', NULL, '2024-09-22 20:05:31', NULL, NULL, NULL, '2024-09-22 22:02:42', '2024-09-22 22:05:31'),
(13, 'lqhmarkets@gmail.com', '9', 'Wallet Withdrawal', NULL, '7', 'ok', '2024-09-22 21:27:04', 1, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\",\"reference_id\":\"LQHPO0013\",\"kind\":\"USDT-TRX\",\"recipients\":[{\"amount\":\"9\",\"currency\":\"USDT\",\"address\":\"TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq\",\"notes\":\"lqhmarkets@gmail.com_13\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727040422000}', '{\"result\": {\"id\": \"6d0d1004-5540-4123-ab50-a290097e01ad\", \"kind\": \"USDT-TRX\", \"txid\": null, \"created_at\": \"2024-09-22T21:27:03.083952+00:00\", \"executed_at\": null, \"profile_id\": \"f759196c-cf55-4618-b277-9f311ff3efcb\", \"wallet_id\": \"613861d7-72e7-475d-9bd5-ee66cb529d5e\", \"confirmations\": 0, \"status\": \"draft\", \"network\": \"mainnet\", \"amount\": {\"total\": \"9\", \"network_fee\": \"3E+1\"}, \"network_fee_preset\": \"normal\", \"network_fee_pays\": \"merchant\", \"network_fee\": 0, \"passthrough\": \"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\", \"reference_id\": \"LQHPO0013\", \"recipients\": [{\"id\": \"327d940e-ef5d-468c-b399-2a7a46b3e133\", \"address\": \"TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq\", \"amount\": {\"requested\": {\"amount\": \"9\", \"currency\": \"USDT\"}, \"received\": {\"amount\": \"9\", \"currency\": \"USDT-TRX\"}}, \"custom_fee\": null, \"notes\": \"lqhmarkets@gmail.com_13\"}]}}', 'ok', '2024-09-22 21:27:04', NULL, NULL, NULL, '2024-09-22 23:26:31', '2024-09-22 23:27:04'),
(14, 'lqhmarkets@gmail.com', '10', 'Wallet Withdrawal', NULL, '7', 'TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq', '2024-09-22 22:01:07', 1, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\",\"reference_id\":\"LQHPO0014\",\"kind\":\"USDT-TRX\",\"recipients\":[{\"amount\":\"10\",\"currency\":\"USDT\",\"address\":\"TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq\",\"notes\":\"lqhmarkets@gmail.com_14\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727042466000}', '{\"result\": {\"id\": \"05e9361e-a0b3-44a4-b169-d872f8dec282\", \"kind\": \"USDT-TRX\", \"txid\": null, \"created_at\": \"2024-09-22T22:01:06.744224+00:00\", \"executed_at\": null, \"profile_id\": \"f759196c-cf55-4618-b277-9f311ff3efcb\", \"wallet_id\": \"613861d7-72e7-475d-9bd5-ee66cb529d5e\", \"confirmations\": 0, \"status\": \"draft\", \"network\": \"mainnet\", \"amount\": {\"total\": \"1E+1\", \"network_fee\": \"3E+1\"}, \"network_fee_preset\": \"normal\", \"network_fee_pays\": \"merchant\", \"network_fee\": 0, \"passthrough\": \"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\", \"reference_id\": \"LQHPO0014\", \"recipients\": [{\"id\": \"b46e1cd4-1c9a-4de7-8b5e-6d33520424dd\", \"address\": \"TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq\", \"amount\": {\"requested\": {\"amount\": \"1E+1\", \"currency\": \"USDT\"}, \"received\": {\"amount\": \"1E+1\", \"currency\": \"USDT-TRX\"}}, \"custom_fee\": null, \"notes\": \"lqhmarkets@gmail.com_14\"}]}}', 'TX3TuURT8EJGVMBtm2Us8H2m4cL21zyTBq', '2024-09-22 22:01:07', NULL, NULL, NULL, '2024-09-23 00:00:44', '2024-09-23 00:01:07'),
(15, 'furnwest@gmail.com', '10', 'Internal Transfer', NULL, NULL, '435214', '2024-09-24 01:21:27', 1, NULL, NULL, NULL, '2024-09-24 01:21:27', NULL, NULL, NULL, '2024-09-24 03:21:27', '2024-09-24 03:21:27'),
(16, 'furnwest@gmail.com', '10', 'Wallet Withdrawal', NULL, '8', 'test', '2024-09-29 01:30:53', 2, NULL, NULL, 'test', '2024-09-29 01:30:53', NULL, NULL, NULL, '2024-09-24 03:37:02', '2024-09-29 03:30:53'),
(17, 'lqhmarkets@gmail.com', '25', 'Wallet Withdrawal', NULL, '9', '000', '2024-09-26 02:23:27', 1, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\",\"reference_id\":\"LQHPO0017\",\"kind\":\"USDT-TRX\",\"recipients\":[{\"amount\":\"25\",\"currency\":\"USDT\",\"address\":\"TT3cz59WJ687gewPqghwNmu8hxojkPCBaF\",\"notes\":\"lqhmarkets@gmail.com_17\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727317406000}', '{\"result\": {\"id\": \"96255e09-4ab8-4bed-a659-5550973e5bc3\", \"kind\": \"USDT-TRX\", \"txid\": null, \"created_at\": \"2024-09-26T02:23:27.024752+00:00\", \"executed_at\": null, \"profile_id\": \"f759196c-cf55-4618-b277-9f311ff3efcb\", \"wallet_id\": \"613861d7-72e7-475d-9bd5-ee66cb529d5e\", \"confirmations\": 0, \"status\": \"draft\", \"network\": \"mainnet\", \"amount\": {\"total\": \"25\", \"network_fee\": \"3E+1\"}, \"network_fee_preset\": \"normal\", \"network_fee_pays\": \"merchant\", \"network_fee\": 0, \"passthrough\": \"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\", \"reference_id\": \"LQHPO0017\", \"recipients\": [{\"id\": \"e246b0ed-d53c-4cbd-8bca-07ce237c13e9\", \"address\": \"TT3cz59WJ687gewPqghwNmu8hxojkPCBaF\", \"amount\": {\"requested\": {\"amount\": \"25\", \"currency\": \"USDT\"}, \"received\": {\"amount\": \"25\", \"currency\": \"USDT-TRX\"}}, \"custom_fee\": null, \"notes\": \"lqhmarkets@gmail.com_17\"}]}}', '0000', '2024-09-26 02:23:27', NULL, NULL, NULL, '2024-09-26 04:22:30', '2024-09-26 04:23:27'),
(18, 'lqhmarkets@gmail.com', '11', 'Wallet Withdrawal', NULL, '9', '--', '2024-09-26 19:11:51', 1, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\",\"reference_id\":\"LQHPO0018\",\"kind\":\"USDT-TRX\",\"recipients\":[{\"amount\":\"11\",\"currency\":\"USDT\",\"address\":\"TT3cz59WJ687gewPqghwNmu8hxojkPCBaF\",\"notes\":\"lqhmarkets@gmail.com_18\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727377911000}', '{\"result\": {\"id\": \"8c3a2158-9fb0-4622-b006-df54e624b901\", \"kind\": \"USDT-TRX\", \"txid\": null, \"created_at\": \"2024-09-26T19:11:51.585499+00:00\", \"executed_at\": null, \"profile_id\": \"f759196c-cf55-4618-b277-9f311ff3efcb\", \"wallet_id\": \"613861d7-72e7-475d-9bd5-ee66cb529d5e\", \"confirmations\": 0, \"status\": \"draft\", \"network\": \"mainnet\", \"amount\": {\"total\": \"11\", \"network_fee\": \"3E+1\"}, \"network_fee_preset\": \"normal\", \"network_fee_pays\": \"merchant\", \"network_fee\": 0, \"passthrough\": \"{\\\"user_id\\\":\\\"lqhmarkets@gmail.com\\\"}\", \"reference_id\": \"LQHPO0018\", \"recipients\": [{\"id\": \"523b349f-ad11-40d7-ad9e-3195741e2c55\", \"address\": \"TT3cz59WJ687gewPqghwNmu8hxojkPCBaF\", \"amount\": {\"requested\": {\"amount\": \"11\", \"currency\": \"USDT\"}, \"received\": {\"amount\": \"11\", \"currency\": \"USDT-TRX\"}}, \"custom_fee\": null, \"notes\": \"lqhmarkets@gmail.com_18\"}]}}', '---', '2024-09-26 19:11:51', NULL, NULL, NULL, '2024-09-26 21:11:26', '2024-09-26 21:11:51'),
(19, 'lqhmarkets@gmail.com', '10', 'Internal Transfer', NULL, NULL, '235001', '2024-09-26 22:17:35', 1, NULL, NULL, NULL, '2024-09-26 22:17:35', NULL, NULL, NULL, '2024-09-27 00:17:35', '2024-09-27 00:17:35'),
(20, 'rugmar91@gmail.com', '20', 'Wallet Withdrawal', NULL, NULL, NULL, '2024-09-27 04:09:45', 0, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"rugmar91@gmail.com\\\"}\",\"reference_id\":\"LQHPO0020\",\"kind\":null,\"recipients\":[{\"amount\":\"20\",\"currency\":null,\"address\":null,\"notes\":\"rugmar91@gmail.com_20\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727410185000}', '{\"result\": \"error\", \"reason\": \"MissingRequiredFields\", \"message\": \"Missing required fields: kind\"}', NULL, '2024-09-27 04:09:45', NULL, NULL, NULL, '2024-09-27 06:05:55', '2024-09-27 06:09:45'),
(21, 'rugmar91@gmail.com', '10', 'Wallet Withdrawal', NULL, NULL, NULL, '2024-09-27 05:53:19', 0, NULL, NULL, NULL, '2024-09-27 05:53:19', NULL, NULL, NULL, '2024-09-27 07:53:19', '2024-09-27 07:53:19'),
(22, 'rugmar91@gmail.com', '10', 'Wallet Withdrawal', NULL, '10', NULL, '2024-09-27 06:35:42', 0, '{\"profile_id\":\"f759196c-cf55-4618-b277-9f311ff3efcb\",\"passthrough\":\"{\\\"user_id\\\":\\\"rugmar91@gmail.com\\\"}\",\"reference_id\":\"LQHPO0022\",\"kind\":\"USDT-TRX\",\"recipients\":[{\"amount\":\"10\",\"currency\":\"USDT\",\"address\":\"Tempora eu exercitat\",\"notes\":\"rugmar91@gmail.com_22\"}],\"request\":\"\\/v1\\/payouts\\/\",\"nonce\":1727418941000}', '{\"result\": \"error\", \"reason\": \"InvalidAddress\", \"message\": \"Invalid recipient address: Tempora eu exercitat for TRX mainnet network\"}', NULL, '2024-09-27 06:35:42', NULL, NULL, NULL, '2024-09-27 08:34:29', '2024-09-27 08:35:42'),
(23, 'lqhmarkets@gmail.com', '10', 'Internal Transfer', NULL, NULL, '235001', '2024-09-27 07:15:54', 1, NULL, NULL, NULL, '2024-09-27 07:15:54', NULL, NULL, NULL, '2024-09-27 09:15:54', '2024-09-27 09:15:54'),
(24, 'lqhmarkets@gmail.com', '10', 'Internal Transfer', NULL, NULL, '235001', '2024-09-29 01:45:52', 1, NULL, NULL, NULL, '2024-09-29 01:45:52', NULL, NULL, NULL, '2024-09-29 03:45:52', '2024-09-29 03:45:52'),
(25, 'lqhmarkets@gmail.com', '25', 'Internal Transfer', NULL, NULL, '235001', '2024-09-29 02:07:13', 1, NULL, NULL, NULL, '2024-09-29 02:07:13', NULL, NULL, NULL, '2024-09-29 04:07:13', '2024-09-29 04:07:13'),
(26, 'lqhmarkets@gmail.com', '20', 'Wallet Withdrawal', NULL, '9', 'withdraw', '2024-09-29 02:10:18', 1, NULL, NULL, 'withdraw', '2024-09-29 02:10:18', NULL, NULL, NULL, '2024-09-29 04:08:59', '2024-09-29 04:10:18'),
(27, 'abougouche22@gmail.com', '500', 'Internal Transfer', NULL, NULL, '493374', '2024-10-01 13:31:57', 1, NULL, NULL, NULL, '2024-10-01 13:31:57', NULL, NULL, NULL, '2024-10-01 15:31:57', '2024-10-01 15:31:57');

-- --------------------------------------------------------

--
-- Stand-in structure for view `wallet_withdraws`
-- (See below for the actual view)
--
CREATE TABLE `wallet_withdraws` (
`admin_remark` varchar(100)
,`email` varchar(50)
,`fullname` varchar(150)
,`id` varchar(15)
,`number` varchar(100)
,`raw_id` int
,`status` int
,`trade_id` varchar(100)
,`type` varchar(6)
,`withdraw_amount` varchar(100)
,`withdraw_date` timestamp
,`withdraw_type` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure for view `ib_client_list`
--
DROP TABLE IF EXISTS `ib_client_list`;

CREATE ALGORITHM=UNDEFINED DEFINER=`fx_crm`@`%` SQL SECURITY DEFINER VIEW `ib_client_list`  AS SELECT count(`liveaccount`.`id`) AS `liveaccounts`, sum(`trade_deposit`.`deposit_amount`) AS `total_deposit`, `aspnetusers`.`id` AS `id`, `aspnetusers`.`uid` AS `uid`, `aspnetusers`.`email` AS `email`, `aspnetusers`.`email_confirmed` AS `email_confirmed`, `aspnetusers`.`password` AS `password`, `aspnetusers`.`number` AS `number`, `aspnetusers`.`number_confirmed` AS `number_confirmed`, `aspnetusers`.`two_factor_enabled` AS `two_factor_enabled`, `aspnetusers`.`lockout_end_date` AS `lockout_end_date`, `aspnetusers`.`lockout_enabled` AS `lockout_enabled`, `aspnetusers`.`access_count_failed` AS `access_count_failed`, `aspnetusers`.`username` AS `username`, `aspnetusers`.`fullname` AS `fullname`, `aspnetusers`.`byPartner` AS `byPartner`, `aspnetusers`.`date` AS `date`, `aspnetusers`.`status` AS `status`, `aspnetusers`.`country` AS `country`, `aspnetusers`.`dial_code` AS `dial_code`, `aspnetusers`.`Isreferal` AS `Isreferal`, `aspnetusers`.`referalId` AS `referalId`, `aspnetusers`.`zipcode` AS `zipcode`, `aspnetusers`.`address` AS `address`, `aspnetusers`.`aboutme` AS `aboutme`, `aspnetusers`.`imgName` AS `imgName`, `aspnetusers`.`education` AS `education`, `aspnetusers`.`industry` AS `industry`, `aspnetusers`.`financial_industry` AS `financial_industry`, `aspnetusers`.`forex_exp` AS `forex_exp`, `aspnetusers`.`monthly_transaction` AS `monthly_transaction`, `aspnetusers`.`investment_plan` AS `investment_plan`, `aspnetusers`.`funds_source` AS `funds_source`, `aspnetusers`.`investment_purpose` AS `investment_purpose`, `aspnetusers`.`total_value` AS `total_value`, `aspnetusers`.`annual_income` AS `annual_income`, `aspnetusers`.`polotically_person` AS `polotically_person`, `aspnetusers`.`bankruptcy` AS `bankruptcy`, `aspnetusers`.`usa_resident` AS `usa_resident`, `aspnetusers`.`usa_tax` AS `usa_tax`, `aspnetusers`.`dob` AS `dob`, `aspnetusers`.`emailToken` AS `emailToken`, `aspnetusers`.`state` AS `state`, `aspnetusers`.`city` AS `city`, `aspnetusers`.`lang` AS `lang`, `aspnetusers`.`email_token_time` AS `email_token_time`, `aspnetusers`.`profile_image` AS `profile_image`, `aspnetusers`.`gender` AS `gender`, `aspnetusers`.`referral` AS `referral`, `aspnetusers`.`mail_otp` AS `mail_otp`, `aspnetusers`.`employee_status` AS `employee_status`, `aspnetusers`.`cfd` AS `cfd`, `aspnetusers`.`other` AS `other`, `aspnetusers`.`kyc_type` AS `kyc_type`, `aspnetusers`.`kyc_front` AS `kyc_front`, `aspnetusers`.`kyc_back` AS `kyc_back`, `aspnetusers`.`bank_detail` AS `bank_detail`, `aspnetusers`.`account_holder_name` AS `account_holder_name`, `aspnetusers`.`bank_name` AS `bank_name`, `aspnetusers`.`bank_account_no` AS `bank_account_no`, `aspnetusers`.`IFSC_Code` AS `IFSC_Code`, `aspnetusers`.`swift_code` AS `swift_code`, `aspnetusers`.`kyc_verify` AS `kyc_verify`, `aspnetusers`.`client_status` AS `client_status`, `aspnetusers`.`wallet_address` AS `wallet_address`, `aspnetusers`.`reg_date` AS `reg_date`, `aspnetusers`.`bank_status` AS `bank_status`, `aspnetusers`.`personal_status` AS `personal_status`, `aspnetusers`.`employemnet_status` AS `employemnet_status`, `aspnetusers`.`trading_status` AS `trading_status`, `aspnetusers`.`ib1` AS `ib1`, `aspnetusers`.`ib2` AS `ib2`, `aspnetusers`.`ib3` AS `ib3`, `aspnetusers`.`ib4` AS `ib4`, `aspnetusers`.`ib5` AS `ib5`, `aspnetusers`.`ib6` AS `ib6`, `aspnetusers`.`ib7` AS `ib7`, `aspnetusers`.`ib8` AS `ib8`, `aspnetusers`.`ib9` AS `ib9`, `aspnetusers`.`ib10` AS `ib10`, `aspnetusers`.`ib11` AS `ib11`, `aspnetusers`.`ib12` AS `ib12`, `aspnetusers`.`ib13` AS `ib13`, `aspnetusers`.`ib14` AS `ib14`, `aspnetusers`.`ib15` AS `ib15`, `aspnetusers`.`created_at` AS `created_at`, `aspnetusers`.`updated_at` AS `updated_at`, `aspnetusers`.`wallet_requested` AS `wallet_requested`, `aspnetusers`.`wallet_enabled` AS `wallet_enabled`, `aspnetusers`.`wallet_requested_at` AS `wallet_requested_at`, `aspnetusers`.`wallet_approved_at` AS `wallet_approved_at` FROM ((`aspnetusers` left join `liveaccount` on((`liveaccount`.`email` = `aspnetusers`.`email`))) left join `trade_deposit` on(((`trade_deposit`.`email` = `aspnetusers`.`email`) and (`trade_deposit`.`Status` = 1)))) GROUP BY `aspnetusers`.`email` ;

-- --------------------------------------------------------

--
-- Structure for view `internal_transfers_list`
--
DROP TABLE IF EXISTS `internal_transfers_list`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `internal_transfers_list`  AS SELECT `trade_deposit`.`email` AS `email`, `trade_deposit`.`id` AS `raw_id`, 'TDID' AS `source`, `trade_deposit`.`trade_id` AS `it_to`, `trade_deposit`.`deposit_from` AS `it_from`, `trade_deposit`.`deposit_amount` AS `amount`, `trade_deposit`.`deposted_date` AS `date`, `trade_deposit`.`Status` AS `status`, `trade_deposit`.`deposit_type` AS `type` FROM `trade_deposit` WHERE (`trade_deposit`.`deposit_type` in ('Internal Transfer','Wallet Transfer')) ;

-- --------------------------------------------------------

--
-- Structure for view `wallet_deposits`
--
DROP TABLE IF EXISTS `wallet_deposits`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `wallet_deposits`  AS SELECT `wallet_deposit`.`id` AS `raw_id`, concat('WDID',`wallet_deposit`.`id`) AS `id`, `aspnetusers`.`number` AS `number`, `aspnetusers`.`fullname` AS `fullname`, 'email' AS `trade_id`, `wallet_deposit`.`email` AS `email`, `wallet_deposit`.`deposit_amount` AS `deposit_amount`, `wallet_deposit`.`deposit_type` AS `deposit_type`, `wallet_deposit`.`deposted_date` AS `deposit_date`, `wallet_deposit`.`Status` AS `status`, 'wallet' AS `TYPE` FROM (`wallet_deposit` join `aspnetusers` on((`aspnetusers`.`email` = `wallet_deposit`.`email`))) ;

-- --------------------------------------------------------

--
-- Structure for view `wallet_withdraws`
--
DROP TABLE IF EXISTS `wallet_withdraws`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `wallet_withdraws`  AS SELECT `wallet_withdraw`.`id` AS `raw_id`, concat('WWID',`wallet_withdraw`.`id`) AS `id`, `aspnetusers`.`number` AS `number`, `aspnetusers`.`fullname` AS `fullname`, 'email' AS `trade_id`, `wallet_withdraw`.`email` AS `email`, `wallet_withdraw`.`withdraw_amount` AS `withdraw_amount`, `wallet_withdraw`.`withdraw_type` AS `withdraw_type`, `wallet_withdraw`.`withdraw_date` AS `withdraw_date`, `wallet_withdraw`.`AdminRemark` AS `admin_remark`, `wallet_withdraw`.`Status` AS `status`, 'wallet' AS `type` FROM (`wallet_withdraw` join `aspnetusers` on((`aspnetusers`.`email` = `wallet_withdraw`.`email`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_types`
--
ALTER TABLE `account_types`
  ADD PRIMARY KEY (`ac_index`),
  ADD KEY `ac_type` (`ac_type`);

--
-- Indexes for table `activation`
--
ALTER TABLE `activation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aspnetusers`
--
ALTER TABLE `aspnetusers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aspnetusers_log`
--
ALTER TABLE `aspnetusers_log`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `available_payment`
--
ALTER TABLE `available_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bankdetails`
--
ALTER TABLE `bankdetails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bonusdeposit`
--
ALTER TABLE `bonusdeposit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bonuses`
--
ALTER TABLE `bonuses`
  ADD PRIMARY KEY (`bonus_id`);

--
-- Indexes for table `bonus_trans`
--
ALTER TABLE `bonus_trans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categorylist`
--
ALTER TABLE `categorylist`
  ADD PRIMARY KEY (`categoryIndex`);

--
-- Indexes for table `claimbonus`
--
ALTER TABLE `claimbonus`
  ADD PRIMARY KEY (`indexNo`);

--
-- Indexes for table `clientbankdetails`
--
ALTER TABLE `clientbankdetails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_wallets`
--
ALTER TABLE `client_wallets`
  ADD PRIMARY KEY (`client_wallet_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`country_id`);

--
-- Indexes for table `demoaccount`
--
ALTER TABLE `demoaccount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `demo_deposit`
--
ALTER TABLE `demo_deposit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`dep_id`),
  ADD UNIQUE KEY `dep_name` (`dep_name`);

--
-- Indexes for table `deposittabledemo`
--
ALTER TABLE `deposittabledemo`
  ADD PRIMARY KEY (`depositIndex`);

--
-- Indexes for table `emplist`
--
ALTER TABLE `emplist`
  ADD PRIMARY KEY (`client_index`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `client_unique_id` (`uid`) USING BTREE,
  ADD KEY `empId` (`empId`) USING BTREE,
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `help_desk`
--
ALTER TABLE `help_desk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ib1`
--
ALTER TABLE `ib1`
  ADD PRIMARY KEY (`indexId`),
  ADD UNIQUE KEY `uniqueId` (`uid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ib1_commission`
--
ALTER TABLE `ib1_commission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `closed_order` (`order_id`,`login`);

--
-- Indexes for table `ib1_withdraw`
--
ALTER TABLE `ib1_withdraw`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ib_categories`
--
ALTER TABLE `ib_categories`
  ADD PRIMARY KEY (`ib_cat_id`);

--
-- Indexes for table `ib_commessions_report`
--
ALTER TABLE `ib_commessions_report`
  ADD PRIMARY KEY (`indexId`);

--
-- Indexes for table `ib_internal`
--
ALTER TABLE `ib_internal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ib_plans`
--
ALTER TABLE `ib_plans`
  ADD PRIMARY KEY (`ib_plan_id`),
  ADD UNIQUE KEY `unique_ab_c` (`ib_plan_cat_id`,`ib_acc_type_id`,`unique_c`);

--
-- Indexes for table `ib_plan_details`
--
ALTER TABLE `ib_plan_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ib_wallet`
--
ALTER TABLE `ib_wallet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ib_withdraw`
--
ALTER TABLE `ib_withdraw`
  ADD PRIMARY KEY (`w_index`);

--
-- Indexes for table `internaltransfer`
--
ALTER TABLE `internaltransfer`
  ADD PRIMARY KEY (`itIndex`);

--
-- Indexes for table `kyc_logs`
--
ALTER TABLE `kyc_logs`
  ADD PRIMARY KEY (`kyc_log_id`);

--
-- Indexes for table `kyc_update`
--
ALTER TABLE `kyc_update`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leverage`
--
ALTER TABLE `leverage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_type_id` (`account_type_id`);

--
-- Indexes for table `liveaccount`
--
ALTER TABLE `liveaccount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_details`
--
ALTER TABLE `login_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `metatradertradehistory`
--
ALTER TABLE `metatradertradehistory`
  ADD PRIMARY KEY (`tradeIndex`),
  ADD UNIQUE KEY `order` (`orderId`);

--
-- Indexes for table `metatradertradehistorydemo`
--
ALTER TABLE `metatradertradehistorydemo`
  ADD PRIMARY KEY (`tradeIndex`),
  ADD UNIQUE KEY `order` (`orderId`);

--
-- Indexes for table `mt5_groups`
--
ALTER TABLE `mt5_groups`
  ADD PRIMARY KEY (`mt5_group_id`);

--
-- Indexes for table `mt5_group_categories`
--
ALTER TABLE `mt5_group_categories`
  ADD PRIMARY KEY (`mt5_grp_cat_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`),
  ADD KEY `page_category_id` (`page_category_id`);

--
-- Indexes for table `page_categories`
--
ALTER TABLE `page_categories`
  ADD PRIMARY KEY (`page_category_id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rolde_id` (`role_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`index`);

--
-- Indexes for table `relationship_manager`
--
ALTER TABLE `relationship_manager`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `rm_id` (`rm_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_type_id` (`ticket_type_id`),
  ADD KEY `ticket_status_id` (`ticket_status_id`);

--
-- Indexes for table `ticket_assignee`
--
ALTER TABLE `ticket_assignee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_id` (`ticket_id`) USING BTREE,
  ADD KEY `assignee` (`assignee`);

--
-- Indexes for table `ticket_followup`
--
ALTER TABLE `ticket_followup`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `status` (`status`),
  ADD KEY `assignee` (`assignee`),
  ADD KEY `added_by` (`user_type`);

--
-- Indexes for table `ticket_service_setting`
--
ALTER TABLE `ticket_service_setting`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `ticket_status`
--
ALTER TABLE `ticket_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_types`
--
ALTER TABLE `ticket_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `total_balance`
--
ALTER TABLE `total_balance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trade_deposit`
--
ALTER TABLE `trade_deposit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trade_withdrawal`
--
ALTER TABLE `trade_withdrawal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trading_bonus`
--
ALTER TABLE `trading_bonus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `userdocuments`
--
ALTER TABLE `userdocuments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wallet_deposit`
--
ALTER TABLE `wallet_deposit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `wallet_withdraw`
--
ALTER TABLE `wallet_withdraw`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_types`
--
ALTER TABLE `account_types`
  MODIFY `ac_index` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `activation`
--
ALTER TABLE `activation`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aspnetusers`
--
ALTER TABLE `aspnetusers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `aspnetusers_log`
--
ALTER TABLE `aspnetusers_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `available_payment`
--
ALTER TABLE `available_payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bankdetails`
--
ALTER TABLE `bankdetails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bonusdeposit`
--
ALTER TABLE `bonusdeposit`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bonuses`
--
ALTER TABLE `bonuses`
  MODIFY `bonus_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bonus_trans`
--
ALTER TABLE `bonus_trans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorylist`
--
ALTER TABLE `categorylist`
  MODIFY `categoryIndex` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `claimbonus`
--
ALTER TABLE `claimbonus`
  MODIFY `indexNo` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clientbankdetails`
--
ALTER TABLE `clientbankdetails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_wallets`
--
ALTER TABLE `client_wallets`
  MODIFY `client_wallet_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `country_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT for table `demoaccount`
--
ALTER TABLE `demoaccount`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `demo_deposit`
--
ALTER TABLE `demo_deposit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `dep_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposittabledemo`
--
ALTER TABLE `deposittabledemo`
  MODIFY `depositIndex` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emplist`
--
ALTER TABLE `emplist`
  MODIFY `client_index` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `help_desk`
--
ALTER TABLE `help_desk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ib1`
--
ALTER TABLE `ib1`
  MODIFY `indexId` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ib1_commission`
--
ALTER TABLE `ib1_commission`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `ib1_withdraw`
--
ALTER TABLE `ib1_withdraw`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ib_categories`
--
ALTER TABLE `ib_categories`
  MODIFY `ib_cat_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ib_commessions_report`
--
ALTER TABLE `ib_commessions_report`
  MODIFY `indexId` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ib_internal`
--
ALTER TABLE `ib_internal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ib_plans`
--
ALTER TABLE `ib_plans`
  MODIFY `ib_plan_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ib_plan_details`
--
ALTER TABLE `ib_plan_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ib_wallet`
--
ALTER TABLE `ib_wallet`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ib_withdraw`
--
ALTER TABLE `ib_withdraw`
  MODIFY `w_index` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `internaltransfer`
--
ALTER TABLE `internaltransfer`
  MODIFY `itIndex` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_logs`
--
ALTER TABLE `kyc_logs`
  MODIFY `kyc_log_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `kyc_update`
--
ALTER TABLE `kyc_update`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `leverage`
--
ALTER TABLE `leverage`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `liveaccount`
--
ALTER TABLE `liveaccount`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `login_details`
--
ALTER TABLE `login_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;

--
-- AUTO_INCREMENT for table `metatradertradehistory`
--
ALTER TABLE `metatradertradehistory`
  MODIFY `tradeIndex` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `metatradertradehistorydemo`
--
ALTER TABLE `metatradertradehistorydemo`
  MODIFY `tradeIndex` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mt5_groups`
--
ALTER TABLE `mt5_groups`
  MODIFY `mt5_group_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `mt5_group_categories`
--
ALTER TABLE `mt5_group_categories`
  MODIFY `mt5_grp_cat_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `page_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `page_categories`
--
ALTER TABLE `page_categories`
  MODIFY `page_category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `payment_id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=293;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `index` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `relationship_manager`
--
ALTER TABLE `relationship_manager`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `ticket_assignee`
--
ALTER TABLE `ticket_assignee`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `ticket_followup`
--
ALTER TABLE `ticket_followup`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `ticket_service_setting`
--
ALTER TABLE `ticket_service_setting`
  MODIFY `service_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_status`
--
ALTER TABLE `ticket_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ticket_types`
--
ALTER TABLE `ticket_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `total_balance`
--
ALTER TABLE `total_balance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `trade_deposit`
--
ALTER TABLE `trade_deposit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `trade_withdrawal`
--
ALTER TABLE `trade_withdrawal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `trading_bonus`
--
ALTER TABLE `trading_bonus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userdocuments`
--
ALTER TABLE `userdocuments`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_deposit`
--
ALTER TABLE `wallet_deposit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `wallet_withdraw`
--
ALTER TABLE `wallet_withdraw`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_types`
--
ALTER TABLE `account_types`
  ADD CONSTRAINT `account_types_ibfk_1` FOREIGN KEY (`ac_type`) REFERENCES `mt5_groups` (`mt5_group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `emplist`
--
ALTER TABLE `emplist`
  ADD CONSTRAINT `emplist_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `leverage`
--
ALTER TABLE `leverage`
  ADD CONSTRAINT `leverage_ibfk_1` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`ac_index`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

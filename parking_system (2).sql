-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 08:17 AM
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
-- Database: `parking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_table` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`log_id`, `user_id`, `action`, `target_table`, `target_id`, `old_value`, `new_value`, `ip_address`, `logged_at`) VALUES
(1, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-06 16:30:18'),
(2, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-06 16:35:07'),
(3, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-06 16:36:47'),
(4, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-06 16:36:50'),
(5, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 16:36:54'),
(6, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 16:37:08'),
(7, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-06 16:37:13'),
(8, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-06 16:38:54'),
(9, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-06 16:39:02'),
(10, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-06 17:03:53'),
(11, 6, 'USER_REGISTERED', 'users', 6, NULL, NULL, '::1', '2026-05-06 17:04:12'),
(12, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-06 17:04:12'),
(13, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-06 17:04:17'),
(14, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-06 17:04:20'),
(15, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-06 17:04:23'),
(16, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-06 17:22:56'),
(17, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 19:19:23'),
(18, 1, 'EMERGENCY_OVERRIDE', 'parking_spots', 2, NULL, 'ogjkjljkljl', NULL, '2026-05-06 19:21:03'),
(19, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 19:23:29'),
(20, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 19:23:31'),
(21, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 19:23:34'),
(22, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-06 19:23:39'),
(23, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-06 19:24:06'),
(24, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 19:24:14'),
(25, 1, 'EMERGENCY_OVERRIDE', 'parking_spots', 1, NULL, 'kjhjkhkjhkjhjk', NULL, '2026-05-06 19:24:36'),
(26, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 19:26:17'),
(27, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-06 19:26:57'),
(28, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-06 19:27:18'),
(29, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 19:27:22'),
(30, 1, 'EMERGENCY_OVERRIDE', 'parking_spots', 2, NULL, 'lhjkhlkklhk', NULL, '2026-05-06 21:01:53'),
(31, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 21:01:59'),
(32, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-06 21:02:04'),
(33, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-06 21:02:19'),
(34, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 21:02:22'),
(35, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 21:06:36'),
(36, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-06 21:06:39'),
(37, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-06 21:07:15'),
(38, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-06 21:07:24'),
(39, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-06 21:09:26'),
(40, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 21:09:33'),
(41, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 21:10:48'),
(42, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-06 21:10:55'),
(43, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-06 21:12:36'),
(44, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-06 21:12:39'),
(45, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-06 21:14:56'),
(46, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-06 21:15:01'),
(47, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-06 21:20:13'),
(48, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 21:20:16'),
(49, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 21:25:33'),
(50, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-06 21:25:40'),
(51, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-06 22:14:23'),
(52, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 22:14:35'),
(53, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-06 22:16:41'),
(54, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-06 22:16:47'),
(55, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-06 22:18:02'),
(56, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-06 22:18:16'),
(57, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-07 00:59:49'),
(58, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 00:59:53'),
(59, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 01:01:38'),
(60, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 01:01:42'),
(61, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 01:04:00'),
(62, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 02:58:14'),
(63, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 02:58:20'),
(64, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 03:01:51'),
(65, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 03:01:54'),
(66, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 03:08:02'),
(67, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 03:08:06'),
(68, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 03:09:27'),
(69, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-07 03:09:33'),
(70, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-07 03:10:13'),
(71, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 03:10:17'),
(72, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 03:10:38'),
(73, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 03:10:42'),
(74, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 03:11:56'),
(75, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-07 03:12:00'),
(76, 1, 'BLACKLIST_LIFTED', 'users', 6, NULL, NULL, '::1', '2026-05-07 03:12:18'),
(77, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-07 10:13:03'),
(78, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-07 10:13:06'),
(79, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-07 10:13:55'),
(80, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:14:03'),
(81, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:15:07'),
(82, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 10:15:11'),
(83, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 10:15:47'),
(84, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:15:51'),
(85, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:23:20'),
(86, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:23:27'),
(87, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:24:38'),
(88, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 10:24:42'),
(89, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 10:29:23'),
(90, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-07 10:29:27'),
(91, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-07 10:29:32'),
(92, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:29:36'),
(93, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:34:30'),
(94, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-07 10:34:33'),
(95, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-07 10:35:01'),
(96, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 10:35:05'),
(97, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 10:47:35'),
(98, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 10:47:40'),
(99, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 17:24:44'),
(100, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-07 17:24:48'),
(101, 1, 'BLACKLIST_LIFTED', 'users', 6, NULL, NULL, '::1', '2026-05-07 19:16:59'),
(102, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-07 19:31:29'),
(103, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 19:31:49'),
(104, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 19:34:34'),
(105, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 19:35:05'),
(106, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-07 19:35:46'),
(107, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-07 19:35:51'),
(108, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-07 19:49:52'),
(109, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-07 19:49:57'),
(110, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 22:06:09'),
(111, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-07 22:06:21'),
(112, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-07 22:07:26'),
(113, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-07 22:07:49'),
(114, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-07 22:12:56'),
(115, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-07 22:13:04'),
(116, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-08 11:18:58'),
(117, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-08 11:21:15'),
(118, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-08 11:21:22'),
(119, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-08 22:14:00'),
(120, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-08 22:14:06'),
(121, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-08 22:14:39'),
(122, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-08 22:14:43'),
(123, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-08 22:15:43'),
(124, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-08 22:15:46'),
(125, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-08 22:17:00'),
(126, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-08 22:17:03'),
(127, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-08 22:18:33'),
(128, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-08 22:18:37'),
(129, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-08 22:19:37'),
(130, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-08 22:19:42'),
(131, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-08 22:21:35'),
(132, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-08 22:21:39'),
(133, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-08 23:08:27'),
(134, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-08 23:08:31'),
(135, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-08 23:09:10'),
(136, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-08 23:09:13'),
(137, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-08 23:09:22'),
(138, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-08 23:09:26'),
(139, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-08 23:10:06'),
(140, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-08 23:10:09'),
(141, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-08 23:20:35'),
(142, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-08 23:20:39'),
(143, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-08 23:56:15'),
(144, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-08 23:56:58'),
(145, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 00:14:34'),
(146, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 00:14:38'),
(147, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 00:16:23'),
(148, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 00:16:25'),
(149, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 00:44:52'),
(150, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 00:44:56'),
(151, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 00:45:24'),
(152, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 00:45:27'),
(153, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 00:50:14'),
(154, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 00:50:17'),
(155, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 00:50:21'),
(156, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-09 00:50:26'),
(157, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-09 00:51:40'),
(158, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 00:51:44'),
(159, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:07:05'),
(160, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:10:59'),
(161, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:12:17'),
(162, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:20:22'),
(163, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:20:25'),
(164, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:26:38'),
(165, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:26:41'),
(166, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:32:48'),
(167, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 01:32:53'),
(168, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 01:32:59'),
(169, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 01:33:25'),
(170, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-09 01:33:29'),
(171, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-09 01:35:58'),
(172, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 01:36:01'),
(173, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 01:36:05'),
(174, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-09 01:36:09'),
(175, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-09 02:16:08'),
(176, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 02:16:12'),
(177, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 02:19:30'),
(178, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-09 02:19:35'),
(179, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-09 02:19:39'),
(180, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 02:19:43'),
(181, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 02:23:41'),
(182, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 02:23:45'),
(183, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 02:37:40'),
(184, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-09 02:37:46'),
(185, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-09 02:40:07'),
(186, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 02:40:11'),
(187, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 02:40:54'),
(188, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 02:40:58'),
(189, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 03:06:14'),
(190, 7, 'USER_REGISTERED', 'users', 7, NULL, NULL, '::1', '2026-05-09 03:07:14'),
(191, 7, 'USER_LOGIN', 'users', 7, NULL, NULL, '::1', '2026-05-09 03:07:14'),
(192, 7, 'USER_LOGOUT', 'users', 7, NULL, NULL, '::1', '2026-05-09 03:26:47'),
(193, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 03:26:52'),
(194, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:13:53'),
(195, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:14:31'),
(196, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:17:48'),
(197, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 04:17:51'),
(198, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 04:23:41'),
(199, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:23:44'),
(200, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:34:07'),
(201, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-09 04:34:15'),
(202, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-09 04:34:42'),
(203, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:35:42'),
(204, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:48:49'),
(205, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:49:40'),
(206, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-09 04:51:45'),
(207, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-11 18:39:29'),
(208, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-11 18:52:49'),
(209, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-11 18:52:53'),
(210, 1, 'BLACKLIST_LIFTED', 'users', 7, NULL, NULL, '::1', '2026-05-11 18:53:48'),
(211, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-11 19:05:16'),
(212, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-11 19:05:26'),
(213, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-11 19:05:30'),
(214, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-11 19:14:38'),
(215, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-11 19:14:46'),
(216, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-11 19:15:12'),
(217, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-11 19:18:14'),
(218, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-11 19:18:20'),
(219, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-11 19:21:15'),
(220, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-11 19:21:46'),
(221, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-11 19:24:36'),
(222, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-11 19:26:47'),
(223, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-11 19:26:53'),
(224, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-11 19:36:11'),
(225, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-11 19:47:31'),
(226, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-11 19:48:59'),
(227, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-11 20:38:26'),
(228, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-11 20:38:34'),
(229, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-11 21:50:54'),
(230, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-11 21:52:15'),
(231, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-11 21:52:22'),
(232, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-11 21:52:26'),
(233, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-11 21:52:38'),
(234, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-11 23:03:32'),
(235, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-11 23:03:38'),
(236, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-11 23:11:45'),
(237, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-11 23:11:51'),
(238, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-11 23:12:34'),
(239, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-11 23:12:39'),
(240, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-11 23:14:00'),
(241, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-11 23:14:03'),
(242, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-11 23:22:36'),
(243, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-11 23:22:40'),
(244, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-11 23:23:34'),
(245, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-11 23:23:38'),
(246, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-11 23:43:47'),
(247, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-11 23:48:35'),
(248, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-11 23:54:23'),
(249, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-11 23:54:27'),
(250, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 00:00:11'),
(251, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-12 00:00:16'),
(252, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-12 00:01:33'),
(253, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 00:01:41'),
(254, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 00:08:57'),
(255, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 00:09:14'),
(256, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-12 00:09:17'),
(257, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-12 00:10:15'),
(258, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 00:10:20'),
(259, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 00:11:25'),
(260, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 00:11:28'),
(261, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 00:13:04'),
(262, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 00:13:11'),
(263, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 00:43:47'),
(264, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-12 00:43:51'),
(265, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-12 01:09:29'),
(266, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 01:09:37'),
(267, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 03:00:47'),
(268, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 03:00:54'),
(269, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 03:02:56'),
(270, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 03:03:00'),
(271, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 03:03:52'),
(272, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 03:03:55'),
(273, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 03:13:18'),
(274, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 03:15:22'),
(275, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 03:27:19'),
(276, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-12 03:30:26'),
(277, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-12 03:44:49'),
(278, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 03:44:52'),
(279, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 03:45:27'),
(280, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 03:45:36'),
(281, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 03:46:48'),
(282, 7, 'USER_LOGIN', 'users', 7, NULL, NULL, '::1', '2026-05-12 03:46:53'),
(283, 7, 'USER_LOGOUT', 'users', 7, NULL, NULL, '::1', '2026-05-12 03:47:30'),
(284, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-12 03:47:36'),
(285, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-12 04:27:01'),
(286, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 04:27:06'),
(287, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 04:31:18'),
(288, 6, 'USER_LOGIN', 'users', 6, NULL, NULL, '::1', '2026-05-12 04:31:21'),
(289, 6, 'USER_LOGOUT', 'users', 6, NULL, NULL, '::1', '2026-05-12 04:53:13'),
(290, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 04:53:16'),
(291, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 04:53:33'),
(292, 7, 'USER_LOGIN', 'users', 7, NULL, NULL, '::1', '2026-05-12 04:53:37'),
(293, 7, 'USER_LOGOUT', 'users', 7, NULL, NULL, '::1', '2026-05-12 04:53:42'),
(294, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 04:53:46'),
(295, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 04:57:35'),
(296, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 05:00:08'),
(297, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 05:00:14'),
(298, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 05:11:24'),
(299, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 05:11:27'),
(300, 2, 'USER_LOGOUT', 'users', 2, NULL, NULL, '::1', '2026-05-12 05:12:33'),
(301, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 05:12:36'),
(302, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 05:30:27'),
(303, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 05:40:15'),
(304, 3, 'USER_LOGIN', 'users', 3, NULL, NULL, '::1', '2026-05-12 05:40:20'),
(305, 3, 'USER_LOGOUT', 'users', 3, NULL, NULL, '::1', '2026-05-12 06:13:53'),
(306, 1, 'USER_LOGIN', 'users', 1, NULL, NULL, '::1', '2026-05-12 06:13:56'),
(307, 1, 'USER_LOGOUT', 'users', 1, NULL, NULL, '::1', '2026-05-12 06:14:34'),
(308, 2, 'USER_LOGIN', 'users', 2, NULL, NULL, '::1', '2026-05-12 06:14:38');

-- --------------------------------------------------------

--
-- Table structure for table `event_zones`
--

CREATE TABLE `event_zones` (
  `zone_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `zone_name` varchar(100) DEFAULT NULL,
  `center_lat` decimal(10,8) DEFAULT NULL,
  `center_lng` decimal(11,8) DEFAULT NULL,
  `radius_km` decimal(5,2) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `active_from` datetime NOT NULL,
  `active_until` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_zones`
--

INSERT INTO `event_zones` (`zone_id`, `admin_id`, `zone_name`, `center_lat`, `center_lng`, `radius_km`, `reason`, `active_from`, `active_until`, `is_active`, `created_at`) VALUES
(1, 1, 'fhfk', 99.99999999, 999.99999999, 85.00, 'iukgjkj', '2026-05-07 14:50:00', '2026-05-15 22:50:00', 1, '2026-05-07 19:50:30');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `label` enum('home','work','other') DEFAULT 'other',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `fine_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `fine_type` enum('no_reservation','overstay','damage') DEFAULT 'no_reservation',
  `amount` decimal(8,2) NOT NULL,
  `overstay_minutes` int(11) DEFAULT 0,
  `status` enum('unpaid','paid','appealed','waived') DEFAULT 'unpaid',
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL,
  `officer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fines`
--

INSERT INTO `fines` (`fine_id`, `driver_id`, `spot_id`, `reservation_id`, `fine_type`, `amount`, `overstay_minutes`, `status`, `issued_at`, `paid_at`, `officer_id`) VALUES
(1, 2, 1, 3, 'overstay', 88.00, 88, 'paid', '2026-05-07 02:58:23', '2026-05-07 02:59:11', NULL),
(2, 2, 1, 4, 'overstay', 3.00, 3, 'waived', '2026-05-07 03:08:15', NULL, NULL),
(3, 2, 1, 2, 'overstay', 651.00, 651, 'paid', '2026-05-07 10:33:49', '2026-05-07 10:34:07', NULL),
(4, 2, 3, 14, 'overstay', 4.17, 5, 'paid', '2026-05-11 19:05:35', '2026-05-11 19:06:07', NULL),
(5, 2, 7, 17, 'overstay', 335.00, 201, 'waived', '2026-05-11 23:11:54', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fine_appeals`
--

CREATE TABLE `fine_appeals` (
  `appeal_id` int(11) NOT NULL,
  `fine_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `evidence_path` varchar(255) DEFAULT NULL COMMENT 'Uploaded photo/receipt path',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine_appeals`
--

INSERT INTO `fine_appeals` (`appeal_id`, `fine_id`, `driver_id`, `description`, `evidence_path`, `status`, `admin_response`, `submitted_at`, `reviewed_at`, `reviewed_by`) VALUES
(1, 2, 2, 'ghfjhgkjhlklknlknkn', 'uploads/evidence/appeal_2_1778123357.jpeg', 'approved', 'ok', '2026-05-07 03:09:17', '2026-05-07 03:10:07', 1),
(2, 5, 2, 'klkjljlkjkjljlkjl', NULL, 'approved', 'ok', '2026-05-11 23:12:28', '2026-05-11 23:12:51', 1);

-- --------------------------------------------------------

--
-- Table structure for table `garages`
--

CREATE TABLE `garages` (
  `garage_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city_zone` varchar(100) DEFAULT NULL,
  `total_floors` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `garages`
--

INSERT INTO `garages` (`garage_id`, `owner_id`, `name`, `address`, `city_zone`, `total_floors`, `description`, `is_verified`, `created_at`) VALUES
(8, 3, 'garage-one', 'el salam', 'Cairo', 1, 'kjkjgkgk', 1, '2026-05-08 11:19:35'),
(9, 3, 'garage-two', 'el salam', 'Mansoura', 1, '', 1, '2026-05-09 01:45:16'),
(10, 3, 'garage-3', 'el salam', 'Cairo', 1, ',mn,m,nm', 1, '2026-05-11 20:40:30'),
(11, 3, 'garage-7', 'el salam', 'Mansoura', 1, 'kjkjlkjl', 1, '2026-05-11 23:53:52'),
(12, 3, 'garage-7', 'el salam', 'Mansoura', 1, 'jfjlhljk', 0, '2026-05-12 00:01:13'),
(13, 3, 'garage-8', 'el salam', 'Mansoura', 1, '', 1, '2026-05-12 00:09:42'),
(14, 3, 'garage-9', 'el salam', 'Cairo', 1, 'jkljlknjknjkn', 1, '2026-05-12 03:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('expiry_warning','penalty_alert','booking_confirmed','fine_issued','appeal_update','payout_ready','waitlist_available','extension_approved') NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `channel` enum('web','email','sms') DEFAULT 'web',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `message`, `channel`, `is_read`, `created_at`) VALUES
(1, 2, 'penalty_alert', 'Overstay Fine Issued', 'You overstayed by 651 minute(s). A fine of 651 EGP has been added to your account.', 'web', 1, '2026-05-07 10:33:49'),
(2, 2, 'penalty_alert', 'Overstay Fine Issued', 'You overstayed by 5 minute(s). A fine of 4.17 EGP has been added to your account.', 'web', 1, '2026-05-11 19:05:35'),
(3, 2, 'penalty_alert', 'Overstay Fine Issued', 'You overstayed by 201 minute(s). A fine of 335 EGP has been added to your account.', 'web', 1, '2026-05-11 23:11:54');

-- --------------------------------------------------------

--
-- Table structure for table `owner_payouts`
--

CREATE TABLE `owner_payouts` (
  `payout_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processed','failed') DEFAULT 'pending',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `initiated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_payouts`
--

INSERT INTO `owner_payouts` (`payout_id`, `owner_id`, `amount`, `status`, `period_start`, `period_end`, `initiated_at`, `processed_at`) VALUES
(1, 3, 565.68, 'pending', '2026-05-05', '2026-05-12', '2026-05-12 04:53:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `owner_verifications`
--

CREATE TABLE `owner_verifications` (
  `verification_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `id_document` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded ID',
  `utility_bill` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded utility bill',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parking_spots`
--

CREATE TABLE `parking_spots` (
  `spot_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `garage_id` int(11) DEFAULT NULL,
  `spot_number` varchar(10) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `spot_type` enum('driveway','lot','garage','street') DEFAULT 'driveway',
  `status` enum('available','unavailable','maintenance','owner_use','pending_verification') DEFAULT 'pending_verification',
  `is_verified` tinyint(1) DEFAULT 0,
  `has_ev_charger` tinyint(1) DEFAULT 0,
  `price_per_hour` decimal(8,2) NOT NULL,
  `base_price` decimal(8,2) NOT NULL,
  `city_zone` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trust_score` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `difficulty_score` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_spots`
--

INSERT INTO `parking_spots` (`spot_id`, `owner_id`, `garage_id`, `spot_number`, `title`, `description`, `address`, `spot_type`, `status`, `is_verified`, `has_ev_charger`, `price_per_hour`, `base_price`, `city_zone`, `created_at`, `updated_at`, `trust_score`, `total_reviews`, `difficulty_score`) VALUES
(1, 3, NULL, NULL, 'Secure Driveway - Maadi', 'Clean, safe driveway with 24/7 camera surveillance. Easy access from ring road.', '15 Road 9, Maadi, Cairo', 'driveway', 'available', 1, 0, 30.00, 30.00, 'Maadi', '2026-05-06 16:29:14', '2026-05-08 22:18:28', 0.00, 0, 0.00),
(2, 3, NULL, NULL, 'Secure Driveway - Maadi', 'Clean, safe driveway with 24/7 camera surveillance. Easy access from ring road.', '15 Road 9, Maadi, Cairo', 'driveway', 'available', 1, 0, 30.00, 30.00, 'Maadi', '2026-05-06 17:03:36', '2026-05-08 22:18:25', 0.00, 0, 0.00),
(3, 3, 8, 'A1', 'garage-one — Spot A1', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(4, 3, 8, 'A2', 'garage-one — Spot A2', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(5, 3, 8, 'A3', 'garage-one — Spot A3', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(6, 3, 8, 'A4', 'garage-one — Spot A4', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(7, 3, 8, 'A5', 'garage-one — Spot A5', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(8, 3, 8, 'A6', 'garage-one — Spot A6', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(9, 3, 8, 'A7', 'garage-one — Spot A7', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(10, 3, 8, 'A8', 'garage-one — Spot A8', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(11, 3, 8, 'A9', 'garage-one — Spot A9', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(12, 3, 8, 'A10', 'garage-one — Spot A10', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-12 03:07:58', 0.00, 0, 0.00),
(13, 3, 8, 'B1', 'garage-one — Spot B1', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(14, 3, 8, 'B2', 'garage-one — Spot B2', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(15, 3, 8, 'B3', 'garage-one — Spot B3', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(16, 3, 8, 'B4', 'garage-one — Spot B4', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(17, 3, 8, 'B5', 'garage-one — Spot B5', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(18, 3, 8, 'B6', 'garage-one — Spot B6', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(19, 3, 8, 'B7', 'garage-one — Spot B7', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(20, 3, 8, 'B8', 'garage-one — Spot B8', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(21, 3, 8, 'B9', 'garage-one — Spot B9', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(22, 3, 8, 'B10', 'garage-one — Spot B10', NULL, 'el salam', 'garage', 'available', 1, 0, 50.00, 25.00, 'Cairo', '2026-05-08 11:20:39', '2026-05-11 22:54:46', 0.00, 0, 0.00),
(23, 3, 9, 'A1', 'garage-two — Spot A1', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:00', 0.00, 0, 0.00),
(24, 3, 9, 'A2', 'garage-two — Spot A2', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:05', 0.00, 0, 0.00),
(25, 3, 9, 'A3', 'garage-two — Spot A3', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:09', 0.00, 0, 0.00),
(26, 3, 9, 'A4', 'garage-two — Spot A4', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:13', 0.00, 0, 0.00),
(27, 3, 9, 'A5', 'garage-two — Spot A5', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:22', 0.00, 0, 0.00),
(28, 3, 9, 'A6', 'garage-two — Spot A6', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:25', 0.00, 0, 0.00),
(29, 3, 9, 'A7', 'garage-two — Spot A7', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:28', 0.00, 0, 0.00),
(30, 3, 9, 'A8', 'garage-two — Spot A8', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:33', 0.00, 0, 0.00),
(31, 3, 9, 'A9', 'garage-two — Spot A9', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:09:18', 0.00, 0, 0.00),
(32, 3, 9, 'A10', 'garage-two — Spot A10', NULL, 'el salam', 'garage', 'available', 1, 1, 50.00, 25.00, 'Mansoura', '2026-05-09 01:45:40', '2026-05-12 03:08:46', 0.00, 0, 0.00),
(33, 3, 10, 'A1', 'garage-3 — Spot A1', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:28', 0.00, 0, 0.00),
(34, 3, 10, 'A2', 'garage-3 — Spot A2', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:40', 0.00, 0, 0.00),
(35, 3, 10, 'A3', 'garage-3 — Spot A3', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:43', 0.00, 0, 0.00),
(36, 3, 10, 'A4', 'garage-3 — Spot A4', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:38', 0.00, 0, 0.00),
(37, 3, 10, 'A5', 'garage-3 — Spot A5', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:35', 0.00, 0, 0.00),
(38, 3, 10, 'A6', 'garage-3 — Spot A6', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:32', 0.00, 0, 0.00),
(39, 3, 10, 'A7', 'garage-3 — Spot A7', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:02:51', 0.00, 0, 0.00),
(40, 3, 10, 'A8', 'garage-3 — Spot A8', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:49', 0.00, 0, 0.00),
(41, 3, 10, 'A9', 'garage-3 — Spot A9', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:53', 0.00, 0, 0.00),
(42, 3, 10, 'A10', 'garage-3 — Spot A10', NULL, 'el salam', 'garage', 'available', 1, 0, 80.00, 25.00, 'Cairo', '2026-05-11 20:40:57', '2026-05-12 03:08:57', 0.00, 0, 0.00),
(43, 3, 11, 'A1', 'garage-7 — Spot A1', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-11 23:54:05', '2026-05-12 03:08:22', 0.00, 0, 0.00),
(44, 3, 11, 'A2', 'garage-7 — Spot A2', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-11 23:54:05', '2026-05-12 03:08:26', 0.00, 0, 0.00),
(45, 3, 12, 'A1', 'garage-7 — Spot A1', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:01:26', '2026-05-12 03:08:08', 0.00, 0, 0.00),
(46, 3, 12, 'A2', 'garage-7 — Spot A2', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:01:26', '2026-05-12 03:08:10', 0.00, 0, 0.00),
(47, 3, 12, 'A3', 'garage-7 — Spot A3', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:01:26', '2026-05-12 03:08:12', 0.00, 0, 0.00),
(48, 3, 12, 'A4', 'garage-7 — Spot A4', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:01:26', '2026-05-12 03:08:14', 0.00, 0, 0.00),
(49, 3, 12, 'A5', 'garage-7 — Spot A5', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:01:26', '2026-05-12 03:08:17', 0.00, 0, 0.00),
(50, 3, 12, 'A6', 'garage-7 — Spot A6', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:01:26', '2026-05-12 03:08:20', 0.00, 0, 0.00),
(51, 3, 13, 'A1', 'garage-8 — Spot A1', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:10:02', '2026-05-12 00:12:29', 0.00, 0, 0.00),
(52, 3, 13, 'A2', 'garage-8 — Spot A2', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:10:02', '2026-05-12 00:12:59', 0.00, 0, 0.00),
(53, 3, 13, 'A3', 'garage-8 — Spot A3', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:10:02', '2026-05-12 03:08:01', 0.00, 0, 0.00),
(54, 3, 13, 'A4', 'garage-8 — Spot A4', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:10:02', '2026-05-12 03:08:03', 0.00, 0, 0.00),
(55, 3, 13, 'A5', 'garage-8 — Spot A5', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Mansoura', '2026-05-12 00:10:02', '2026-05-12 03:08:06', 0.00, 0, 0.00),
(56, 3, 14, 'A1', 'garage-9 — Spot A1', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Cairo', '2026-05-12 03:44:38', '2026-05-12 06:14:55', 1.00, 1, 1.00),
(57, 3, 14, 'A2', 'garage-9 — Spot A2', NULL, 'el salam', 'garage', 'available', 1, 0, 25.00, 25.00, 'Cairo', '2026-05-12 03:44:38', '2026-05-12 03:45:22', 0.00, 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `peak_hour_rules`
--

CREATE TABLE `peak_hour_rules` (
  `rule_id` int(11) NOT NULL,
  `spot_id` int(11) DEFAULT NULL COMMENT 'NULL = applies to all spots',
  `day_of_week` tinyint(4) DEFAULT NULL COMMENT 'NULL = all days',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `multiplier` decimal(4,2) NOT NULL DEFAULT 1.50 COMMENT 'e.g. 1.5 = 50% price increase',
  `event_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peak_hour_rules`
--

INSERT INTO `peak_hour_rules` (`rule_id`, `spot_id`, `day_of_week`, `start_time`, `end_time`, `multiplier`, `event_name`, `created_at`) VALUES
(1, NULL, NULL, '08:00:00', '10:00:00', 1.50, 'Morning Rush Hour', '2026-05-06 16:28:00'),
(3, NULL, 6, '10:00:00', '22:00:00', 1.30, 'Weekend Busy Hours', '2026-05-06 16:28:00');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `promo_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount_value` decimal(8,2) NOT NULL,
  `max_uses` int(11) DEFAULT 100,
  `current_uses` int(11) DEFAULT 0,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`promo_id`, `code`, `discount_type`, `discount_value`, `max_uses`, `current_uses`, `valid_from`, `valid_until`, `is_active`, `created_at`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 100, 0, '2026-05-06 19:28:00', '2027-05-06 19:28:00', 1, '2026-05-06 16:28:00'),
(2, 'LOYALITY77', 'percentage', 5.00, 50, 0, '2026-05-07 00:24:00', '2026-05-08 12:24:00', 1, '2026-05-06 21:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `actual_checkin` datetime DEFAULT NULL,
  `actual_checkout` datetime DEFAULT NULL,
  `status` enum('pending','confirmed','active','completed','cancelled','no_show','extended') DEFAULT 'pending',
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurrence_days` varchar(20) DEFAULT NULL COMMENT 'e.g. 1,2,3,4,5 for Mon-Fri',
  `qr_code` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `cancellation_time` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(8,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `driver_id`, `spot_id`, `vehicle_id`, `start_time`, `end_time`, `actual_checkin`, `actual_checkout`, `status`, `is_recurring`, `recurrence_days`, `qr_code`, `total_amount`, `refund_amount`, `cancellation_time`, `cancellation_reason`, `promo_code`, `discount_amount`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '2026-05-06 19:40:00', '2026-05-06 20:37:00', '2026-05-06 19:38:33', NULL, 'cancelled', 0, NULL, '88460be10128e3077de0d68bba5539ce3ce26c89fd9950cc5ab1867bc25cd2e7', 56.86, 0.00, NULL, 'Emergency override by admin: kjhjkhkjhkjhjk', NULL, 0.00, NULL, '2026-05-06 16:38:17', '2026-05-06 19:24:36'),
(2, 2, 1, 1, '2026-05-07 00:08:00', '2026-05-07 01:42:00', '2026-05-07 00:08:22', '2026-05-07 13:33:49', 'completed', 0, NULL, '8a06e4e442ffd79b1e1eca4461737c30b89eba9610be288a3f1be3d052fc696e', 47.28, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-06 21:07:53', '2026-05-07 10:33:49'),
(3, 2, 1, 1, '2026-05-07 04:02:00', '2026-05-07 04:30:00', '2026-05-07 04:01:21', '2026-05-07 05:58:23', 'completed', 0, NULL, '46f4c6f4f34f7ad22ca844760c474183eb7c7cfb13c48df55e446e2578581812', 15.96, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-07 01:00:42', '2026-05-07 02:58:23'),
(4, 2, 1, 1, '2026-05-07 06:01:00', '2026-05-07 06:05:00', '2026-05-07 06:00:50', '2026-05-07 06:08:15', 'completed', 0, NULL, 'f8112b8121791bf127f4d4c3f94a810ede2f5544b6fcd234b7409371876f3ca6', 2.28, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-07 03:00:19', '2026-05-07 03:08:15'),
(5, 2, 2, 1, '2026-05-07 13:35:00', '2026-05-07 13:40:00', NULL, NULL, 'no_show', 0, NULL, '869a553046d95ab3f650c10328ae814692fa60ebe4bf19361a1443dd7a0bbabe', 2.85, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-07 10:33:21', '2026-05-07 10:49:29'),
(6, 2, 1, 1, '2026-05-07 13:50:00', '2026-05-07 13:59:00', '2026-05-07 13:49:13', '2026-05-07 13:49:38', 'completed', 0, NULL, '790fcfa08bd96a72a492bb0ec33bede9e2b29429670ee1e6fe9f8f15970f2b6a', 5.13, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-07 10:48:58', '2026-05-07 10:49:38'),
(7, 2, 1, 1, '2026-05-07 22:36:00', '2026-05-07 22:44:00', NULL, NULL, 'no_show', 0, NULL, '42f7181dd160d251cb23c42b523335c8e35e3d2ab785f6955706007a15f0540b', 4.56, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-07 19:35:39', '2026-05-07 22:13:11'),
(8, 2, 3, 1, '2026-05-09 01:35:00', '2026-05-09 02:15:00', '2026-05-09 01:39:10', '2026-05-09 02:01:56', 'completed', 0, NULL, '6a0f910cb3e9dffd0d5dcd31be53c1d9abfeaba6b084c39d075e84976a5a5be5', 17.25, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-08 22:35:02', '2026-05-08 23:01:56'),
(9, 2, 12, 1, '2026-05-09 02:08:00', '2026-05-09 03:15:00', '2026-05-09 02:08:11', '2026-05-09 02:10:22', 'completed', 0, NULL, '0e8db6847f055ceed7903a870986a2c6e5f326608785f9381b24cb58a207d3af', 30.23, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-08 23:07:38', '2026-05-08 23:10:22'),
(10, 2, 3, 1, '2026-05-09 05:40:00', '2026-05-09 06:36:00', '2026-05-09 05:37:27', '2026-05-09 05:42:49', 'completed', 0, NULL, '7806188bf5ed060ca640685c6b892f9fa4f0d10cb324c59303906fa1b7904fb6', 25.27, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-09 02:37:15', '2026-05-09 02:42:49'),
(11, 7, 9, 6, '2026-05-09 06:15:00', '2026-05-09 07:15:00', '2026-05-09 06:11:05', '2026-05-09 06:21:47', 'completed', 0, NULL, '052236ae8011ba1dadfdea48ebcbecb51694904d699c1cfa4499dbc4984e836a', 28.50, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-09 03:10:52', '2026-05-09 03:21:47'),
(12, 2, 3, 1, '2026-05-09 06:30:00', '2026-05-09 07:00:00', '2026-05-09 06:27:54', '2026-05-09 06:34:07', 'completed', 0, NULL, '925ddf3c20c575fd0086d819ea3dfff35a3dd8bbdee0b39394c66802a30fd945', 13.54, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-09 03:27:46', '2026-05-09 03:34:07'),
(13, 2, 3, 1, '2026-05-09 07:00:00', '2026-05-09 07:53:00', '2026-05-09 06:57:14', '2026-05-09 07:49:43', 'completed', 0, NULL, '7de531ca0b79e9bce4925e74593950c110ae9e47070332395eb5486633c09242', 23.92, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-09 03:53:40', '2026-05-09 04:49:43'),
(14, 2, 3, 1, '2026-05-11 21:45:00', '2026-05-11 22:00:00', '2026-05-11 21:41:16', '2026-05-11 22:05:35', 'completed', 0, NULL, 'b760df5e10b5d6876fbed2e8e0e8d9b426a778f88daf7df5e14e1dbd4aac5582', 6.77, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-11 18:40:48', '2026-05-11 19:05:35'),
(15, 2, 4, 1, '2026-05-11 22:10:00', '2026-05-11 22:25:00', '2026-05-11 22:08:04', '2026-05-11 22:17:04', 'completed', 0, NULL, 'c2e3702f753deaf024bd9f850460872b04be942948159f2bf6ee16355e0a5887', 6.42, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-11 19:07:39', '2026-05-11 19:17:04'),
(16, 6, 13, 3, '2026-05-11 22:25:00', '2026-05-11 22:35:00', NULL, NULL, 'cancelled', 0, NULL, '4f159759598a715c45044193ca09fa33bef217b6b20b1d5e24ff008782a26f06', 4.75, 0.00, '2026-05-11 22:20:58', 'hfjkhjhk', NULL, 0.00, NULL, '2026-05-11 19:20:23', '2026-05-11 19:20:58'),
(17, 2, 7, 1, '2026-05-11 22:30:00', '2026-05-11 22:50:00', '2026-05-11 22:25:33', '2026-05-12 02:11:54', 'completed', 0, NULL, 'f3b87984cd7424e733604ae3f91817fd3df43fa28c92a53a9dd07e16a69859ba', 8.55, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-11 19:25:12', '2026-05-11 23:11:54'),
(18, 2, 3, 1, '2026-05-12 02:15:00', '2026-05-12 02:30:00', '2026-05-12 02:15:07', '2026-05-12 02:15:25', 'completed', 0, NULL, '2172caeee87edf639bafb0e1b094b80949a85ce84aef38fbf48a19af6e856676', 12.83, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-11 23:14:53', '2026-05-11 23:15:25'),
(19, 2, 3, 1, '2026-05-12 02:20:00', '2026-05-12 03:35:00', '2026-05-12 02:18:38', '2026-05-12 02:22:12', 'completed', 0, NULL, 'dc3893c7646656602217d7a8f72c2930741aeff901a4ef71c39bf6b90aed85b8', 64.13, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-11 23:18:29', '2026-05-11 23:22:12'),
(20, 2, 4, 1, '2026-05-12 02:25:00', '2026-05-12 03:20:00', '2026-05-12 02:22:08', '2026-05-12 02:22:26', 'completed', 0, NULL, '4a7b6f1fdeb063add332cdf0c6a4ca5af2173911a78ccf9a8aa5534ea6283432', 47.03, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-11 23:21:50', '2026-05-11 23:22:26'),
(21, 2, 51, 1, '2026-05-12 03:16:00', '2026-05-12 04:18:00', '2026-05-12 03:19:26', '2026-05-12 03:40:04', 'completed', 0, NULL, 'ea88d5893f9a1a902f5e223b9f0ff701b8fa879ddcf07a8521ef620f8ffdc2e3', 26.51, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-12 00:13:35', '2026-05-12 00:40:04'),
(22, 2, 9, 1, '2026-05-12 06:05:00', '2026-05-12 08:05:00', '2026-05-12 06:03:44', '2026-05-12 06:15:25', 'completed', 0, NULL, 'ecd0e615c216e794636642ea4d95b99a40e174cf4c87ec1b7262db4334d9302f', 102.60, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-12 03:03:32', '2026-05-12 03:15:25'),
(23, 2, 3, 1, '2026-05-12 06:30:00', '2026-05-12 08:30:00', '2026-05-12 06:27:06', '2026-05-12 06:45:38', 'completed', 0, NULL, '93ceac3598c06806f85626de1a852642ee69c7c43dfb0107758a1dda55b3afc7', 102.60, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-12 03:26:57', '2026-05-12 03:45:38'),
(24, 2, 56, 1, '2026-05-12 06:50:00', '2026-05-12 09:45:00', NULL, NULL, 'no_show', 0, NULL, '550f0897fa297e0724f799533f36615899b5172046130906efa140453143ecef', 74.82, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-12 03:46:19', '2026-05-12 04:30:30'),
(25, 7, 57, 6, '2026-05-12 06:50:00', '2026-05-12 09:46:00', '2026-05-12 06:47:26', '2026-05-12 07:53:38', 'completed', 0, NULL, '7e8a389ef5e5257a73bca7d4cf8fb3d849fba6142f7a4bac48c8c8c1bc6b30b6', 83.60, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-12 03:47:19', '2026-05-12 04:53:38'),
(26, 2, 56, 1, '2026-05-12 07:32:00', '2026-05-12 09:35:00', '2026-05-12 07:31:09', '2026-05-12 07:53:18', 'completed', 0, NULL, 'fa357f73e4a6f786572f3d4e07a20eab92e839971b0de066651d07433fd2ded5', 52.59, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-12 04:31:01', '2026-05-12 04:53:18'),
(27, 2, 3, 1, '2026-05-12 09:20:00', '2026-05-12 10:19:00', '2026-05-12 09:15:56', '2026-05-12 09:16:11', 'completed', 0, NULL, '70ba5d3aff03016972b99a20164ecf815cc3d7a514970adddd0f179b3053275c', 75.67, 0.00, NULL, NULL, NULL, 0.00, NULL, '2026-05-12 06:15:47', '2026-05-12 06:16:11');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `difficulty_rating` tinyint(4) DEFAULT NULL CHECK (`difficulty_rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `reservation_id`, `reviewer_id`, `spot_id`, `rating`, `difficulty_rating`, `comment`, `created_at`) VALUES
(1, 3, 2, 1, 3, NULL, '', '2026-05-07 02:58:47'),
(2, 6, 2, 1, 1, NULL, '', '2026-05-07 15:07:12'),
(3, 8, 2, 3, 4, NULL, '', '2026-05-08 23:02:07'),
(4, 26, 2, 56, 1, 1, '', '2026-05-12 06:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `sensor_health`
--

CREATE TABLE `sensor_health` (
  `sensor_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `last_heartbeat` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('online','offline','warning') DEFAULT 'online'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensor_health`
--

INSERT INTO `sensor_health` (`sensor_id`, `spot_id`, `last_heartbeat`, `status`) VALUES
(1, 1, '2026-05-06 16:29:14', 'offline'),
(2, 1, '2026-05-06 17:03:36', 'offline'),
(3, 2, '2026-05-06 17:03:36', 'offline'),
(5, 1, '2026-05-06 21:02:53', 'offline'),
(6, 1, '2026-05-06 21:02:56', 'offline'),
(7, 1, '2026-05-06 21:03:01', 'offline'),
(8, 2, '2026-05-06 21:03:04', 'offline'),
(9, 1, '2026-05-06 21:03:05', 'offline'),
(10, 1, '2026-05-06 21:03:06', 'offline');

-- --------------------------------------------------------

--
-- Table structure for table `spot_availability`
--

CREATE TABLE `spot_availability` (
  `availability_id` int(11) NOT NULL,
  `spot_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '0=Sunday, 6=Saturday',
  `open_time` time NOT NULL,
  `close_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `payer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `platform_fee` decimal(8,2) DEFAULT 0.00,
  `owner_earnings` decimal(8,2) DEFAULT 0.00,
  `tax_amount` decimal(8,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'EGP',
  `payment_method` enum('card','wallet','cash') DEFAULT 'card',
  `payment_status` enum('escrow','released','refunded','failed') DEFAULT 'escrow',
  `escrow_released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `reservation_id`, `payer_id`, `amount`, `platform_fee`, `owner_earnings`, `tax_amount`, `currency`, `payment_method`, `payment_status`, `escrow_released_at`, `created_at`) VALUES
(1, 1, 2, 56.86, 8.53, 48.33, 6.98, 'EGP', 'card', 'refunded', NULL, '2026-05-06 16:38:17'),
(2, 2, 2, 2.28, 0.34, 1.94, 0.28, 'EGP', 'card', 'released', '2026-05-07 10:33:49', '2026-05-06 21:07:53'),
(3, 3, 2, 15.96, 2.39, 13.57, 1.96, 'EGP', 'card', 'released', '2026-05-07 02:58:23', '2026-05-07 01:00:42'),
(4, 4, 2, 2.28, 0.34, 1.94, 0.28, 'EGP', 'card', 'released', '2026-05-07 03:08:15', '2026-05-07 03:00:19'),
(5, 5, 2, 2.85, 0.43, 2.42, 0.35, 'EGP', 'card', 'escrow', NULL, '2026-05-07 10:33:21'),
(6, 6, 2, 5.13, 0.77, 4.36, 0.63, 'EGP', 'card', 'released', '2026-05-07 10:49:38', '2026-05-07 10:48:58'),
(7, 7, 2, 4.56, 0.68, 3.88, 0.56, 'EGP', 'card', 'escrow', NULL, '2026-05-07 19:35:39'),
(8, 8, 2, 4.75, 0.71, 4.04, 0.58, 'EGP', 'card', 'released', '2026-05-08 23:01:56', '2026-05-08 22:35:02'),
(9, 9, 2, 30.23, 4.53, 25.70, 3.71, 'EGP', 'card', 'released', '2026-05-08 23:10:22', '2026-05-08 23:07:38'),
(10, 10, 2, 25.27, 3.79, 21.48, 3.10, 'EGP', 'card', 'released', '2026-05-09 02:42:49', '2026-05-09 02:37:15'),
(11, 11, 7, 28.50, 4.28, 24.22, 3.50, 'EGP', 'card', 'released', '2026-05-09 03:21:47', '2026-05-09 03:10:52'),
(12, 12, 2, 13.54, 2.03, 11.51, 1.66, 'EGP', 'card', 'released', '2026-05-09 03:34:07', '2026-05-09 03:27:46'),
(13, 13, 2, 23.92, 3.59, 20.33, 2.94, 'EGP', 'card', 'released', '2026-05-09 04:49:43', '2026-05-09 03:53:40'),
(14, 14, 2, 6.77, 1.02, 5.75, 0.83, 'EGP', 'card', 'released', '2026-05-11 19:05:35', '2026-05-11 18:40:48'),
(15, 15, 2, 6.42, 0.96, 5.46, 0.79, 'EGP', 'card', 'released', '2026-05-11 19:17:04', '2026-05-11 19:07:39'),
(16, 16, 6, 4.75, 0.71, 4.04, 0.58, 'EGP', 'card', 'escrow', NULL, '2026-05-11 19:20:23'),
(17, 17, 2, 8.55, 1.28, 7.27, 1.05, 'EGP', 'card', 'released', '2026-05-11 23:11:54', '2026-05-11 19:25:12'),
(18, 18, 2, 12.83, 1.92, 10.91, 1.58, 'EGP', 'card', 'released', '2026-05-11 23:15:25', '2026-05-11 23:14:53'),
(19, 19, 2, 64.13, 9.62, 54.51, 7.88, 'EGP', 'card', 'released', '2026-05-11 23:22:12', '2026-05-11 23:18:29'),
(20, 20, 2, 47.03, 7.05, 39.98, 5.78, 'EGP', 'card', 'released', '2026-05-11 23:22:26', '2026-05-11 23:21:50'),
(21, 21, 2, 26.51, 3.98, 22.53, 3.26, 'EGP', 'card', 'released', '2026-05-12 00:40:04', '2026-05-12 00:13:35'),
(22, 22, 2, 102.60, 15.39, 87.21, 12.60, 'EGP', 'card', 'released', '2026-05-12 03:15:25', '2026-05-12 03:03:32'),
(23, 23, 2, 102.60, 15.39, 87.21, 12.60, 'EGP', 'card', 'released', '2026-05-12 03:45:38', '2026-05-12 03:26:57'),
(24, 24, 2, 74.82, 11.22, 63.60, 9.19, 'EGP', 'card', 'escrow', NULL, '2026-05-12 03:46:19'),
(25, 25, 7, 83.60, 12.54, 71.06, 10.27, 'EGP', 'card', 'released', '2026-05-12 04:53:38', '2026-05-12 03:47:19'),
(26, 26, 2, 52.59, 7.89, 44.70, 6.46, 'EGP', 'card', 'released', '2026-05-12 04:53:18', '2026-05-12 04:31:01'),
(27, 27, 2, 75.67, 11.35, 64.32, 9.29, 'EGP', 'card', 'released', '2026-05-12 06:16:11', '2026-05-12 06:15:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('driver','owner','admin') NOT NULL DEFAULT 'driver',
  `is_active` tinyint(1) DEFAULT 1,
  `is_blacklisted` tinyint(1) DEFAULT 0,
  `blacklist_reason` varchar(255) DEFAULT NULL,
  `unpaid_fines_count` int(11) DEFAULT 0,
  `preferred_language` varchar(10) DEFAULT 'en',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `preferred_currency` varchar(3) DEFAULT 'EGP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone`, `role`, `is_active`, `is_blacklisted`, `blacklist_reason`, `unpaid_fines_count`, `preferred_language`, `created_at`, `updated_at`, `preferred_currency`) VALUES
(1, 'System Admin', 'admin@parkingsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', 1, 0, NULL, 0, 'en', '2026-05-06 16:28:00', '2026-05-06 16:29:14', 'EGP'),
(2, 'Amira', 'driver@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01012345678', 'driver', 1, 0, NULL, 0, 'en', '2026-05-06 16:29:14', '2026-05-12 01:09:59', 'USD'),
(3, 'Sara Owner', 'owner@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01098765432', 'owner', 1, 0, NULL, 0, 'en', '2026-05-06 16:29:14', '2026-05-06 16:29:14', 'EGP'),
(6, 'Aya Ali', 'aaya92246@gmail.com', '$2y$10$N7XGqLh0m.W7Vjpk6NvuXu.PQ2dxJ006xnk8wUgzHjPyvOCJJ3VuS', '01021429624', 'driver', 1, 0, NULL, 0, 'en', '2026-05-06 17:04:12', '2026-05-11 19:20:23', 'EGP'),
(7, 'Donya', 'alidonya184@gmail.com', '$2y$10$wdAF6wqZGwDT0plMqXyzgeJj1j7j29wlTMMpShGssCh3OmvOukSuy', '+201021429624', 'driver', 1, 0, NULL, 0, 'en', '2026-05-09 03:07:14', '2026-05-11 18:53:48', 'EGP');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `license_plate` varchar(20) NOT NULL,
  `make` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `vehicle_type` enum('sedan','suv','motorcycle','truck','ev') DEFAULT 'sedan',
  `height_cm` decimal(6,2) DEFAULT NULL,
  `width_cm` decimal(6,2) DEFAULT NULL,
  `is_ev` tinyint(1) DEFAULT 0,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `user_id`, `license_plate`, `make`, `model`, `color`, `vehicle_type`, `height_cm`, `width_cm`, `is_ev`, `is_default`, `created_at`) VALUES
(1, 2, 'ABC 1234', 'Toyota', 'Corolla', 'White', 'sedan', NULL, NULL, 0, 1, '2026-05-06 16:29:14'),
(3, 6, 'XYZ999', 'BMW', 'E32', 'blue', 'sedan', NULL, NULL, 0, 1, '2026-05-09 02:38:09'),
(6, 7, 'XYZ998', 'BMW', 'E32', 'white', 'sedan', NULL, NULL, 0, 1, '2026-05-09 03:10:03'),
(7, 2, 'XYZ993', 'BMW', 'E28', 'white', 'sedan', NULL, NULL, 0, 0, '2026-05-09 03:57:43');

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

CREATE TABLE `waitlist` (
  `waitlist_id` int(11) NOT NULL,
  `spot_id` int(11) DEFAULT NULL,
  `driver_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `desired_start` datetime NOT NULL,
  `desired_end` datetime NOT NULL,
  `status` enum('watching','notified','converted','expired') DEFAULT 'watching',
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `garage_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waitlist`
--

INSERT INTO `waitlist` (`waitlist_id`, `spot_id`, `driver_id`, `vehicle_id`, `desired_start`, `desired_end`, `status`, `added_at`, `garage_id`) VALUES
(1, 2, 2, 1, '2026-05-07 13:50:00', '2026-05-07 13:53:00', 'watching', '2026-05-07 10:48:30', NULL),
(4, NULL, 6, 3, '2026-05-12 08:44:00', '2026-05-12 11:44:00', 'watching', '2026-05-12 04:53:05', 14);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_zones`
--
ALTER TABLE `event_zones`
  ADD PRIMARY KEY (`zone_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `driver_id` (`driver_id`,`spot_id`),
  ADD KEY `spot_id` (`spot_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`fine_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `spot_id` (`spot_id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `officer_id` (`officer_id`);

--
-- Indexes for table `fine_appeals`
--
ALTER TABLE `fine_appeals`
  ADD PRIMARY KEY (`appeal_id`),
  ADD KEY `fine_id` (`fine_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `garages`
--
ALTER TABLE `garages`
  ADD PRIMARY KEY (`garage_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `owner_payouts`
--
ALTER TABLE `owner_payouts`
  ADD PRIMARY KEY (`payout_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `owner_verifications`
--
ALTER TABLE `owner_verifications`
  ADD PRIMARY KEY (`verification_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `spot_id` (`spot_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD PRIMARY KEY (`spot_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `garage_id` (`garage_id`);

--
-- Indexes for table `peak_hour_rules`
--
ALTER TABLE `peak_hour_rules`
  ADD PRIMARY KEY (`rule_id`),
  ADD KEY `spot_id` (`spot_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `spot_id` (`spot_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `spot_id` (`spot_id`);

--
-- Indexes for table `sensor_health`
--
ALTER TABLE `sensor_health`
  ADD PRIMARY KEY (`sensor_id`),
  ADD KEY `spot_id` (`spot_id`);

--
-- Indexes for table `spot_availability`
--
ALTER TABLE `spot_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `spot_id` (`spot_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `payer_id` (`payer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD PRIMARY KEY (`waitlist_id`),
  ADD KEY `spot_id` (`spot_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=309;

--
-- AUTO_INCREMENT for table `event_zones`
--
ALTER TABLE `event_zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `fine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fine_appeals`
--
ALTER TABLE `fine_appeals`
  MODIFY `appeal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `garages`
--
ALTER TABLE `garages`
  MODIFY `garage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `owner_payouts`
--
ALTER TABLE `owner_payouts`
  MODIFY `payout_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `owner_verifications`
--
ALTER TABLE `owner_verifications`
  MODIFY `verification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parking_spots`
--
ALTER TABLE `parking_spots`
  MODIFY `spot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `peak_hour_rules`
--
ALTER TABLE `peak_hour_rules`
  MODIFY `rule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sensor_health`
--
ALTER TABLE `sensor_health`
  MODIFY `sensor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `spot_availability`
--
ALTER TABLE `spot_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `waitlist`
--
ALTER TABLE `waitlist`
  MODIFY `waitlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `event_zones`
--
ALTER TABLE `event_zones`
  ADD CONSTRAINT `event_zones_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`) ON DELETE CASCADE;

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fines_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`),
  ADD CONSTRAINT `fines_ibfk_3` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`),
  ADD CONSTRAINT `fines_ibfk_4` FOREIGN KEY (`officer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `fine_appeals`
--
ALTER TABLE `fine_appeals`
  ADD CONSTRAINT `fine_appeals_ibfk_1` FOREIGN KEY (`fine_id`) REFERENCES `fines` (`fine_id`),
  ADD CONSTRAINT `fine_appeals_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fine_appeals_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `garages`
--
ALTER TABLE `garages`
  ADD CONSTRAINT `garages_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `owner_payouts`
--
ALTER TABLE `owner_payouts`
  ADD CONSTRAINT `owner_payouts_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `owner_verifications`
--
ALTER TABLE `owner_verifications`
  ADD CONSTRAINT `owner_verifications_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `owner_verifications_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`),
  ADD CONSTRAINT `owner_verifications_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD CONSTRAINT `parking_spots_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parking_spots_ibfk_2` FOREIGN KEY (`garage_id`) REFERENCES `garages` (`garage_id`) ON DELETE CASCADE;

--
-- Constraints for table `peak_hour_rules`
--
ALTER TABLE `peak_hour_rules`
  ADD CONSTRAINT `peak_hour_rules_ibfk_1` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`);

--
-- Constraints for table `sensor_health`
--
ALTER TABLE `sensor_health`
  ADD CONSTRAINT `sensor_health_ibfk_1` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_availability`
--
ALTER TABLE `spot_availability`
  ADD CONSTRAINT `spot_availability_ibfk_1` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`payer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD CONSTRAINT `waitlist_ibfk_1` FOREIGN KEY (`spot_id`) REFERENCES `parking_spots` (`spot_id`),
  ADD CONSTRAINT `waitlist_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `waitlist_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

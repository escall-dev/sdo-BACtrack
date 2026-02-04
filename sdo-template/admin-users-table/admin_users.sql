-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2026 at 01:43 AM
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
-- Database: `sdo_cts`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `security_pin` varchar(255) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `email`, `password_hash`, `full_name`, `role_id`, `google_id`, `avatar_url`, `security_pin`, `unit`, `is_active`, `last_login`, `created_at`, `updated_at`, `created_by`) VALUES
(3, 'escall@deped-sanpedro.ph', '$2y$10$89WbpOITDkUUJBAmsv7CuO0.OTr9zKdQevYPBP5PX22X4nBeRk2IS', 'System Administrator', 1, NULL, NULL, NULL, 'OSDS', 1, '2026-01-06 03:26:31', '2026-01-06 03:26:19', '2026-01-06 03:26:31', NULL),
(4, 'joerenzescallente027@gmail.com', '$2y$10$3983W2Pb75U0hdf2PxWyPOQK9Plh440iM.bD.FfoPzCOq6Z92erg6', 'Alexander Joerenz Escallente', 1, NULL, '/SDO-cts/uploads/avatars/avatar_4_1767678739.jpg', '$2y$10$Dcd5Zra4ME3gNrhKJRlByuQYIIas0D2TtD/wlwDcYPQeHcuJKPWCm', 'SGOD', 1, '2026-01-20 00:18:12', '2026-01-06 03:37:05', '2026-01-20 00:18:12', 3),
(5, 'escall.dev027@gmail.com', '$2y$10$ZSVi1N9fhGjA0f78.z99Kej/7zWq633rLk3WMbJb2JF.b.P2.x8aC', 'escall dev', 4, '107714000475651491856', 'https://lh3.googleusercontent.com/a/ACg8ocImqRNkaj-XNIubC4kamlFKrGKdzWwKQQWW3LW3UCtGfkE2A98=s96-c', NULL, NULL, 1, '2026-01-12 05:00:33', '2026-01-06 04:05:26', '2026-01-12 05:00:33', NULL),
(6, 'joerenz.dev@gmail.com', '$2y$10$DGPpzC/weoc/rCMUhjYd.Ot.DNvwbXbJlurMK.dmjEg8M0AyCaafq', 'alex', 2, '108143253005440248236', '/SDO-cts/uploads/avatars/avatar_6_1767689011.jpg', NULL, 'SGOD', 1, '2026-01-19 07:28:06', '2026-01-06 08:39:34', '2026-01-19 07:28:06', NULL),
(7, 'bagwistv09@gmail.com', NULL, 'bagwis_', 3, '115985323403557869607', 'https://lh3.googleusercontent.com/a/ACg8ocLHNmdjfyFgJklYgbNAWvXt5iwnFAq6lqZJ44fLz06O_lBdIz8=s96-c', NULL, NULL, 1, '2026-01-06 08:43:55', '2026-01-06 08:43:55', '2026-01-07 01:39:53', NULL),
(8, 'ict.sanpedrocity@deped.com.ph', '$2y$10$fbSbmKisLpjHlpm9ykSKzei0ySmzKaRNutV1R.J72YNW3jtySpIau', 'sdo admin', 1, NULL, NULL, NULL, 'OSDS', 1, '2026-01-07 02:33:26', '2026-01-07 02:26:16', '2026-01-07 02:33:26', 4),
(9, 'goku@gmail.com', '$2y$10$5oDdMkKT6OC/8CkWN8azEe3xJu4jvOYDC2y7dPqTKYtQLLJfo/DQa', 'goku', 1, NULL, NULL, NULL, 'SGOD', 1, '2026-01-19 07:27:41', '2026-01-12 04:55:07', '2026-01-19 07:27:41', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`),
  ADD CONSTRAINT `admin_users_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

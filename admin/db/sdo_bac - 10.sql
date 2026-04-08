-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2026 at 05:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sdo_bac`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_documents`
--

CREATE TABLE `activity_documents` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_history_logs`
--

CREATE TABLE `activity_history_logs` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `action_type` enum('DATE_CHANGE','STATUS_CHANGE','COMPLIANCE_TAG') NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_history_logs`
--

INSERT INTO `activity_history_logs` (`id`, `activity_id`, `action_type`, `old_value`, `new_value`, `changed_by`, `changed_at`) VALUES
(51, 392, 'DATE_CHANGE', '{\"start\":\"2026-03-31\",\"end\":\"2026-03-31\"}', '{\"start\":\"2026-05-31\",\"end\":\"2026-05-31\"}', 4, '2026-04-03 09:50:40');

-- --------------------------------------------------------

--
-- Table structure for table `bac_cycles`
--

CREATE TABLE `bac_cycles` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `cycle_number` int(11) NOT NULL DEFAULT 1,
  `status` enum('ACTIVE','COMPLETED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bac_cycles`
--

INSERT INTO `bac_cycles` (`id`, `project_id`, `cycle_number`, `status`, `created_at`, `updated_at`) VALUES
(12, 1, 1, 'ACTIVE', '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(13, 2, 1, 'ACTIVE', '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(14, 3, 1, 'ACTIVE', '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(15, 4, 1, 'ACTIVE', '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(16, 5, 1, 'ACTIVE', '2026-03-26 00:40:48', '2026-03-26 00:40:48'),
(17, 6, 1, 'ACTIVE', '2026-03-26 00:44:55', '2026-03-26 00:44:55'),
(18, 7, 1, 'ACTIVE', '2026-03-26 05:10:44', '2026-03-26 05:10:44'),
(19, 8, 1, 'ACTIVE', '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(20, 9, 1, 'ACTIVE', '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(21, 10, 1, 'ACTIVE', '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(22, 11, 1, 'ACTIVE', '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(23, 12, 1, 'ACTIVE', '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(24, 13, 1, 'ACTIVE', '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(25, 14, 1, 'ACTIVE', '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(26, 15, 1, 'ACTIVE', '2026-03-26 07:10:23', '2026-03-26 07:10:23'),
(27, 16, 1, 'ACTIVE', '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(28, 17, 1, 'ACTIVE', '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(29, 18, 1, 'ACTIVE', '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(30, 19, 1, 'ACTIVE', '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(31, 20, 1, 'ACTIVE', '2026-03-27 06:04:09', '2026-03-27 06:04:09'),
(33, 22, 1, 'ACTIVE', '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(34, 23, 1, 'ACTIVE', '2026-03-30 10:40:45', '2026-03-30 10:40:45'),
(35, 24, 1, 'ACTIVE', '2026-03-30 10:42:50', '2026-03-30 10:42:50'),
(36, 25, 1, 'ACTIVE', '2026-03-30 10:44:11', '2026-03-30 10:44:11'),
(37, 26, 1, 'ACTIVE', '2026-03-30 10:45:10', '2026-03-30 10:45:10'),
(38, 27, 1, 'ACTIVE', '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(39, 28, 1, 'ACTIVE', '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(40, 29, 1, 'ACTIVE', '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(41, 30, 1, 'ACTIVE', '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(42, 31, 1, 'ACTIVE', '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(43, 32, 1, 'ACTIVE', '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(44, 33, 1, 'ACTIVE', '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(45, 34, 1, 'ACTIVE', '2026-04-03 09:18:09', '2026-04-03 09:18:09'),
(46, 35, 1, 'ACTIVE', '2026-04-03 09:19:02', '2026-04-03 09:19:02');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('DEADLINE_WARNING','ACTIVITY_DELAYED','DOCUMENT_UPLOADED','ADJUSTMENT_REQUEST','ADJUSTMENT_RESPONSE','PROJECT_REJECTED') NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_type`, `reference_id`, `is_read`, `created_at`) VALUES
(1, 3, 'Timeline Adjustment Request', 'Escall requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'CR FOR ALL GENDERS\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 1, 1, '2026-02-05 02:05:12'),
(2, 1, 'Timeline Adjustment Request', 'Escall requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'CR FOR ALL GENDERS\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 1, 0, '2026-02-05 02:05:12'),
(4, 3, 'Adjustment Request Approved', 'Your timeline adjustment request for \'Pre-Procurement Conference\' in project \'CR FOR ALL GENDERS\' has been approved.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 1, 1, '2026-02-08 01:55:17'),
(5, 3, 'Timeline Adjustment Request', 'Escall requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'SDO ict materials\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 2, 1, '2026-02-08 02:14:18'),
(6, 1, 'Timeline Adjustment Request', 'Escall requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'SDO ict materials\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 2, 0, '2026-02-08 02:14:18'),
(7, 3, 'Adjustment Request Approved', 'Your timeline adjustment request for \'Pre-Procurement Conference\' in project \'SDO ict materials\' has been approved.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 2, 1, '2026-02-08 02:16:46'),
(8, 3, 'Timeline Adjustment Request', 'Escall requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'CANTEEN\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 3, 1, '2026-02-08 02:17:08'),
(9, 1, 'Timeline Adjustment Request', 'Escall requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'CANTEEN\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 3, 0, '2026-02-08 02:17:08'),
(10, 3, 'Adjustment Request Approved', 'Your timeline adjustment request for \'Advertisement and Posting of Invitation to Bid\' in project \'CANTEEN\' has been approved.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 3, 1, '2026-02-08 02:17:35'),
(11, 3, 'Timeline Adjustment Request', 'Escall requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'CR FOR ALL GENDERS\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 4, 1, '2026-02-08 02:19:18'),
(12, 1, 'Timeline Adjustment Request', 'Escall requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'CR FOR ALL GENDERS\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 4, 0, '2026-02-08 02:19:18'),
(13, 3, 'Adjustment Request Approved', 'Your timeline adjustment request for \'Pre-Procurement Conference\' in project \'CR FOR ALL GENDERS\' has been approved.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 4, 1, '2026-02-08 02:19:21'),
(14, 3, 'Timeline Adjustment Request', 'Project Owner requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'CR FOR ALL GENDERS\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 5, 1, '2026-02-08 02:24:41'),
(15, 1, 'Timeline Adjustment Request', 'Project Owner requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'CR FOR ALL GENDERS\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 5, 0, '2026-02-08 02:24:41'),
(17, 3, 'Timeline Adjustment Request', 'redgine pinedes requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'procurement of printed materials for aral program\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 6, 1, '2026-02-10 06:22:17'),
(18, 1, 'Timeline Adjustment Request', 'redgine pinedes requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'procurement of printed materials for aral program\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 6, 0, '2026-02-10 06:22:17'),
(19, 4, 'Adjustment Request Rejected', 'Your timeline adjustment request for \'Pre-Procurement Conference\' in project \'procurement of printed materials for aral program\' has been rejected.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 6, 0, '2026-02-26 07:19:57'),
(20, 1, 'Timeline Adjustment Request', 'Seijun requested a timeline adjustment for \'Post-Qualification\' in project \'CANTEEN\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 7, 0, '2026-03-09 06:12:33'),
(21, 1, 'Timeline Adjustment Request', 'Seijun requested a timeline adjustment for \'Post-Qualification\' in project \'coffee stand\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 8, 0, '2026-03-09 07:03:04'),
(22, 1, 'Timeline Adjustment Request', 'redgine pinedes requested a timeline adjustment for \'Bid Evaluation\' in project \'infra\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 9, 0, '2026-03-09 08:15:52'),
(23, 1, 'Timeline Adjustment Request', 'Project Owner requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'New Computers for ICT Unit\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 10, 0, '2026-03-23 01:14:41'),
(25, 1, 'Timeline Adjustment Request', 'Project Owner requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'New Computers for ICT Unit\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 11, 0, '2026-03-23 01:17:51'),
(26, 1, 'Timeline Adjustment Request', 'Project Owner requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'New Computers for ICT Unit\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 12, 0, '2026-03-23 02:19:20'),
(28, 1, 'Timeline Adjustment Request', 'Project Owner requested a timeline adjustment for \'Issuance and Availability of Bidding Documents\' in project \'New Computers for ICT Unit\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 13, 0, '2026-03-23 02:32:33'),
(31, 16, 'Timeline Adjustment Request', 'Redgine Pinedes requested a timeline adjustment for \'Implementation\' in project \'unsolicited offer test\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 14, 0, '2026-03-31 02:00:28'),
(32, 10, 'Timeline Adjustment Request', 'Redgine Pinedes requested a timeline adjustment for \'Implementation\' in project \'unsolicited offer test\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 14, 0, '2026-03-31 02:00:28'),
(33, 4, 'Timeline Adjustment Request', 'Redgine Pinedes requested a timeline adjustment for \'Implementation\' in project \'unsolicited offer test\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 14, 0, '2026-03-31 02:00:28'),
(34, 3, 'Timeline Adjustment Request', 'Redgine Pinedes requested a timeline adjustment for \'Implementation\' in project \'unsolicited offer test\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 14, 0, '2026-03-31 02:00:28'),
(35, 4, 'Adjustment Request Approved', 'Your timeline adjustment request for \'Implementation\' in project \'unsolicited offer test\' has been approved.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 14, 0, '2026-04-03 09:50:40'),
(36, 16, 'Timeline Adjustment Request', 'BAC Secretary requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'Building for SGOD\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 15, 0, '2026-04-03 09:52:07'),
(37, 10, 'Timeline Adjustment Request', 'BAC Secretary requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'Building for SGOD\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 15, 0, '2026-04-03 09:52:07'),
(38, 4, 'Timeline Adjustment Request', 'BAC Secretary requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'Building for SGOD\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 15, 0, '2026-04-03 09:52:07'),
(39, 3, 'Timeline Adjustment Request', 'BAC Secretary requested a timeline adjustment for \'Advertisement and Posting of Invitation to Bid\' in project \'Building for SGOD\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 15, 0, '2026-04-03 09:52:07'),
(40, 4, 'Adjustment Request Disapproved', 'Your timeline adjustment request for \'Advertisement and Posting of Invitation to Bid\' in project \'Building for SGOD\' has been disapproved.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 15, 0, '2026-04-03 09:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `bactrack_id` varchar(32) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `procurement_type` varchar(50) NOT NULL DEFAULT 'PUBLIC_BIDDING',
  `project_start_date` date DEFAULT NULL,
  `approved_budget` decimal(15,2) DEFAULT NULL,
  `project_owner_name` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approval_status` enum('DRAFT','PENDING_APPROVAL','APPROVED','REJECTED') NOT NULL DEFAULT 'APPROVED',
  `rejection_remarks` text DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `bactrack_id`, `description`, `procurement_type`, `project_start_date`, `approved_budget`, `project_owner_name`, `created_by`, `approval_status`, `rejection_remarks`, `rejected_by`, `rejected_at`, `created_at`, `updated_at`) VALUES
(1, 'New Computers for ICT Unit', NULL, 'New Computers for ICT Unit', 'PUBLIC_BIDDING', '2026-03-25', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(2, 'New Printers for Supply', NULL, 'new  printers', 'PUBLIC_BIDDING', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(3, 'OLAGUER', NULL, '', 'PUBLIC_BIDDING', '2026-03-24', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(4, 'OLAGUER', NULL, 'Purified Water', 'PUBLIC_BIDDING', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(5, 'New Offices', NULL, '', 'SMALL_VALUE_PROCUREMENT_200K', '2026-03-30', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 00:40:48', '2026-03-26 00:40:48'),
(6, 'New Aircon', NULL, '', 'SMALL_VALUE_PROCUREMENT', '2026-03-27', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 00:44:55', '2026-03-26 00:44:55'),
(7, 'mcdo', NULL, '', 'COMPETITIVE_BIDDING', '2026-03-27', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 05:10:44', '2026-03-26 05:10:44'),
(8, 'cr', NULL, 'sdsadsd', 'SMALL_VALUE_PROCUREMENT_200K', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(9, 'rc', NULL, 'asdsdsda', 'SMALL_VALUE_PROCUREMENT_200K', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(10, 'gsdf', NULL, 'dsfdfsfds', 'SMALL_VALUE_PROCUREMENT', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(11, 'sdasda d', NULL, 'ss ffdfsfasf', 'COMPETITIVE_BIDDING', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(12, 'ffdfsf', NULL, 'sfaffdggsdggwefw', 'PUBLIC_BIDDING', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(13, 'sdadsasjhdgavavds jadjavsuydvajh', NULL, '', 'PUBLIC_BIDDING', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(14, 'aaaaaaa', NULL, 'sdsdadasdsadsdadasd', 'PUBLIC_BIDDING', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(15, 'jjjkkjkjkjj', NULL, 'hvjhv5656rhgchgcytdf', 'PUBLIC_BIDDING', '2026-03-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 07:10:23', '2026-03-26 07:10:23'),
(16, 'cafssasaf', NULL, 'sfdfad sfsf asfsfsfdfdfdffsd', 'PUBLIC_BIDDING', '2026-04-27', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(17, 'adadadadada', NULL, '', 'PUBLIC_BIDDING', '2026-05-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(18, 'wdqqwqweq', NULL, '', 'COMPETITIVE_BIDDING', '2026-05-26', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(19, 'competitive bidding test project', NULL, 'test', 'COMPETITIVE_BIDDING', '2026-06-27', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(20, 'svp 200k below test', NULL, 'sdadasdasdadsadasdasdasd', 'SMALL_VALUE_PROCUREMENT', '2026-03-27', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-27 06:04:09', '2026-03-27 06:04:09'),
(22, 'repeat order test', NULL, 'akjsdhgfakjdgkjabdksvdadadsdawwe', 'REPEAT_ORDER', '2026-04-13', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(23, 'NEGOTIATED_PROCUREMENT', NULL, 'test for NEGOTIATED_PROCUREMENT', 'NEGOTIATED_PROCUREMENT', '2026-05-30', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-30 10:40:45', '2026-03-30 10:40:45'),
(24, 'CONSULTING_SERVICES', NULL, 'test CONSULTING_SERVICES', 'CONSULTING_SERVICES', '2026-07-30', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-30 10:42:50', '2026-03-30 10:42:50'),
(25, 'svp test ulit 200k above', NULL, 'svp test 200k', 'SMALL_VALUE_PROCUREMENT_200K', '2026-03-31', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 10:44:11'),
(26, 'unsolicited offer test', NULL, 'unsolicited offer', 'UNSOLICITED_OFFER', '2026-03-31', NULL, NULL, 4, 'APPROVED', NULL, NULL, NULL, '2026-03-30 10:45:10', '2026-03-30 10:45:10'),
(27, 'avengers hall', NULL, 'townhall', 'PUBLIC_BIDDING', '2026-06-30', NULL, 'Stark Enterprises', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(28, 'Asgard Division Hall', 'BTHV9-202603-001', 'Test for asgard division hall in the earth missouri', 'SMALL_VALUE_PROCUREMENT_200K', '2026-05-31', NULL, 'Thor Odinson', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(29, 'ICT Materials', 'BT312-202604-001', 'Ict systems', 'PUBLIC_BIDDING', '2026-05-03', NULL, 'SDO', 4, 'APPROVED', NULL, NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(30, 'SDO new building', 'BTCVJ-202604-002', 'test', 'PUBLIC_BIDDING', '2026-05-18', NULL, 'Stark Enterprises', 4, 'APPROVED', NULL, NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:22:21'),
(31, 'Building for SGOD', 'BTKKX-202604-003', 'TEST', 'PUBLIC_BIDDING', '2026-05-08', NULL, 'DO', 4, 'APPROVED', NULL, NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(32, 'Building for CID', 'BTK1E-202604-004', 'CID  BUILDING', 'PUBLIC_BIDDING', '2026-06-03', NULL, 'DO', 4, 'APPROVED', NULL, NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(33, 'Building for OSDS', 'BTLWD-202604-005', 'OSDS BUILDING', 'PUBLIC_BIDDING', '2026-07-03', NULL, 'DO', 4, 'APPROVED', NULL, NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(34, 'Office renovation for ICT unit', 'BTJOR-202604-006', 'renovation of ICT unit for tiles repair and pathway', 'DIRECT_ACQUISITION', '2026-07-19', NULL, 'REGION IV A', 4, 'APPROVED', NULL, NULL, NULL, '2026-04-03 09:18:09', '2026-04-03 09:18:09'),
(35, 'OSDS & ASDS Office renovation', 'BTNE0-202604-007', 'OSDS & ASDS Office renovation', 'PUBLIC_BIDDING', '2026-09-03', NULL, 'COA', 4, 'APPROVED', NULL, NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02');

-- --------------------------------------------------------

--
-- Table structure for table `project_activities`
--

CREATE TABLE `project_activities` (
  `id` int(11) NOT NULL,
  `bac_cycle_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `step_name` varchar(255) NOT NULL,
  `step_order` int(11) NOT NULL,
  `planned_start_date` date NOT NULL,
  `planned_end_date` date NOT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `status` enum('PENDING','IN_PROGRESS','COMPLETED','DELAYED') NOT NULL DEFAULT 'PENDING',
  `compliance_status` enum('COMPLIANT','NON_COMPLIANT') DEFAULT NULL,
  `compliance_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_activities`
--

INSERT INTO `project_activities` (`id`, `bac_cycle_id`, `template_id`, `step_name`, `step_order`, `planned_start_date`, `planned_end_date`, `actual_completion_date`, `status`, `compliance_status`, `compliance_remarks`, `created_at`, `updated_at`) VALUES
(123, 12, 1, 'Pre-Procurement Conference', 1, '2026-03-25', '2026-03-25', NULL, 'DELAYED', NULL, NULL, '2026-03-24 03:22:46', '2026-03-26 00:15:19'),
(124, 12, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-26', '2026-04-01', NULL, 'DELAYED', NULL, NULL, '2026-03-24 03:22:46', '2026-04-03 05:08:50'),
(125, 12, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-02', '2026-04-08', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(126, 12, 4, 'Pre-Bid Conference', 4, '2026-04-09', '2026-04-09', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(127, 12, 5, 'Submission and Opening of Bids', 5, '2026-04-10', '2026-04-10', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(128, 12, 6, 'Bid Evaluation', 6, '2026-04-11', '2026-04-17', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(129, 12, 7, 'Post-Qualification', 7, '2026-04-18', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(130, 12, 8, 'BAC Resolution Recommending Award', 8, '2026-04-25', '2026-04-25', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(131, 12, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-26', '2026-04-27', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(132, 12, 10, 'Notice of Award Issuance', 10, '2026-04-28', '2026-04-28', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(133, 12, 11, 'Contract Preparation and Signing', 11, '2026-04-29', '2026-05-03', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(134, 12, 12, 'Notice to Proceed', 12, '2026-05-04', '2026-05-04', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(135, 12, 16, 'Delivery and Inspection', 13, '2026-05-05', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(136, 12, 17, 'Payment', 14, '2026-05-06', '2026-05-06', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(137, 13, 1, 'Pre-Procurement Conference', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-24 03:33:15', '2026-03-30 12:54:52'),
(138, 13, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-24 03:33:15', '2026-04-03 05:08:50'),
(139, 13, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-03', '2026-04-09', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(140, 13, 4, 'Pre-Bid Conference', 4, '2026-04-10', '2026-04-10', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(141, 13, 5, 'Submission and Opening of Bids', 5, '2026-04-11', '2026-04-11', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(142, 13, 6, 'Bid Evaluation', 6, '2026-04-12', '2026-04-18', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(143, 13, 7, 'Post-Qualification', 7, '2026-04-19', '2026-04-25', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(144, 13, 8, 'BAC Resolution Recommending Award', 8, '2026-04-26', '2026-04-26', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(145, 13, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-27', '2026-04-28', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(146, 13, 10, 'Notice of Award Issuance', 10, '2026-04-29', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(147, 13, 11, 'Contract Preparation and Signing', 11, '2026-04-30', '2026-05-04', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(148, 13, 12, 'Notice to Proceed', 12, '2026-05-05', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(149, 13, 16, 'Delivery and Inspection', 13, '2026-05-06', '2026-05-06', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(150, 13, 17, 'Payment', 14, '2026-05-07', '2026-05-07', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(151, 14, 1, 'Pre-Procurement Conference', 1, '2026-03-24', '2026-03-24', NULL, 'DELAYED', NULL, NULL, '2026-03-24 03:33:47', '2026-03-26 00:15:19'),
(152, 14, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-25', '2026-03-31', NULL, 'DELAYED', NULL, NULL, '2026-03-24 03:33:47', '2026-04-03 05:08:50'),
(153, 14, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-01', '2026-04-07', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(154, 14, 4, 'Pre-Bid Conference', 4, '2026-04-08', '2026-04-08', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(155, 14, 5, 'Submission and Opening of Bids', 5, '2026-04-09', '2026-04-09', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(156, 14, 6, 'Bid Evaluation', 6, '2026-04-10', '2026-04-16', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(157, 14, 7, 'Post-Qualification', 7, '2026-04-17', '2026-04-23', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(158, 14, 8, 'BAC Resolution Recommending Award', 8, '2026-04-24', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(159, 14, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-25', '2026-04-26', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(160, 14, 10, 'Notice of Award Issuance', 10, '2026-04-27', '2026-04-27', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(161, 14, 11, 'Contract Preparation and Signing', 11, '2026-04-28', '2026-05-02', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(162, 14, 12, 'Notice to Proceed', 12, '2026-05-03', '2026-05-03', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(163, 14, 16, 'Delivery and Inspection', 13, '2026-05-04', '2026-05-04', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(164, 14, 17, 'Payment', 14, '2026-05-05', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(165, 15, 1, 'Pre-Procurement Conference', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-24 03:34:38', '2026-03-30 12:54:52'),
(166, 15, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-24 03:34:38', '2026-04-03 05:08:50'),
(167, 15, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-03', '2026-04-09', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(168, 15, 4, 'Pre-Bid Conference', 4, '2026-04-10', '2026-04-10', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(169, 15, 5, 'Submission and Opening of Bids', 5, '2026-04-11', '2026-04-11', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(170, 15, 6, 'Bid Evaluation', 6, '2026-04-12', '2026-04-18', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(171, 15, 7, 'Post-Qualification', 7, '2026-04-19', '2026-04-25', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(172, 15, 8, 'BAC Resolution Recommending Award', 8, '2026-04-26', '2026-04-26', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(173, 15, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-27', '2026-04-28', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(174, 15, 10, 'Notice of Award Issuance', 10, '2026-04-29', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(175, 15, 11, 'Contract Preparation and Signing', 11, '2026-04-30', '2026-05-04', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(176, 15, 12, 'Notice to Proceed', 12, '2026-05-05', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(177, 15, 16, 'Delivery and Inspection', 13, '2026-05-06', '2026-05-06', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(178, 15, 17, 'Payment', 14, '2026-05-07', '2026-05-07', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(179, 19, 36, 'Preparation of Purchase Request', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:05:38', '2026-03-30 12:54:52'),
(180, 19, 37, 'Submission and Receipt of Approved PR', 2, '2026-03-27', '2026-03-27', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:05:38', '2026-03-30 12:54:52'),
(181, 19, 38, 'Preparation of Request for Quotation (RFQ)', 3, '2026-03-28', '2026-03-31', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:05:38', '2026-04-03 05:08:50'),
(182, 19, 39, 'Posting of RFQ or Conduct of Canvass', 4, '2026-04-01', '2026-04-03', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:05:38', '2026-04-05 01:29:36'),
(183, 19, 40, 'Preparation of Abstract of Quotation / Resolution to Award', 5, '2026-04-04', '2026-04-06', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(184, 19, 41, 'Notice of Award', 6, '2026-04-07', '2026-04-08', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(185, 19, 42, 'Preparation and Approval of Purchase Order (PO)', 7, '2026-04-09', '2026-04-12', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(186, 19, 43, 'Preparation and Signing of Notice to Proceed', 8, '2026-04-13', '2026-04-14', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(187, 19, 44, 'Allowance period of the supplier', 9, '2026-04-15', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(188, 20, 36, 'Preparation of Purchase Request', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:06:29', '2026-03-30 12:54:52'),
(189, 20, 37, 'Submission and Receipt of Approved PR', 2, '2026-03-27', '2026-03-27', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:06:29', '2026-03-30 12:54:52'),
(190, 20, 38, 'Preparation of Request for Quotation (RFQ)', 3, '2026-03-28', '2026-03-31', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:06:29', '2026-04-03 05:08:50'),
(191, 20, 39, 'Posting of RFQ or Conduct of Canvass', 4, '2026-04-01', '2026-04-03', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:06:29', '2026-04-05 01:29:36'),
(192, 20, 40, 'Preparation of Abstract of Quotation / Resolution to Award', 5, '2026-04-04', '2026-04-06', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(193, 20, 41, 'Notice of Award', 6, '2026-04-07', '2026-04-08', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(194, 20, 42, 'Preparation and Approval of Purchase Order (PO)', 7, '2026-04-09', '2026-04-12', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(195, 20, 43, 'Preparation and Signing of Notice to Proceed', 8, '2026-04-13', '2026-04-14', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(196, 20, 44, 'Allowance period of the supplier', 9, '2026-04-15', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(197, 21, 29, 'Preparation of Purchase Request', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:10:03', '2026-03-30 12:54:52'),
(198, 21, 30, 'Submission and Receipt of Approved Purchase Request', 2, '2026-03-27', '2026-03-27', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:10:03', '2026-03-30 12:54:52'),
(199, 21, 31, 'Preparation of Request for Quotation (RFQ)', 3, '2026-03-28', '2026-03-30', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:10:03', '2026-03-31 00:04:45'),
(200, 21, 32, 'Posting of RFQ or Conduct of Canvass', 4, '2026-03-31', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:10:03', '2026-04-03 05:08:50'),
(201, 21, 33, 'Opening of bids documents / Preparation of Abstract of Quotation', 5, '2026-04-03', '2026-04-03', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:10:03', '2026-04-05 01:29:36'),
(202, 21, 34, 'Preparation and Approval of Purchase Order (PO)', 6, '2026-04-04', '2026-04-07', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(203, 21, 35, 'Allowance period of the supplier', 7, '2026-04-08', '2026-04-17', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(204, 22, 18, 'Preparation of Bidding Documents', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:10:45', '2026-03-30 12:54:52'),
(205, 22, 19, 'Pre-Procurement Conference', 2, '2026-03-27', '2026-03-27', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:10:45', '2026-03-30 12:54:52'),
(206, 22, 20, 'Advertisement / Posting of Invitation to Bid', 3, '2026-03-28', '2026-04-03', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:10:45', '2026-04-05 01:29:36'),
(207, 22, 21, 'Pre-Bid Conference', 4, '2026-04-04', '2026-04-15', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(208, 22, 22, 'Eligibility Check / Deadline of Submission and Receipt of Bids / Bid Opening', 5, '2026-04-16', '2026-04-16', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(209, 22, 23, 'Bid Evaluation', 6, '2026-04-17', '2026-04-17', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(210, 22, 24, 'Post-Qualification', 7, '2026-04-18', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(211, 22, 25, 'Preparation and Approval of Resolution to Award', 8, '2026-04-30', '2026-05-10', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(212, 22, 26, 'Issuance and Signing of Notice of Award', 9, '2026-05-11', '2026-05-11', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(213, 22, 27, 'Contract Preparation and Signing of Contract', 10, '2026-05-12', '2026-05-22', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(214, 22, 28, 'Issuance and Signing of Notice to Proceed', 11, '2026-05-23', '2026-05-23', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(215, 23, 1, 'Pre-Procurement Conference', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:30:07', '2026-03-30 12:54:52'),
(216, 23, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:30:07', '2026-04-03 05:08:50'),
(217, 23, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-03', '2026-04-09', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(218, 23, 4, 'Pre-Bid Conference', 4, '2026-04-10', '2026-04-10', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(219, 23, 5, 'Submission and Opening of Bids', 5, '2026-04-11', '2026-04-11', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(220, 23, 6, 'Bid Evaluation', 6, '2026-04-12', '2026-04-18', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(221, 23, 7, 'Post-Qualification', 7, '2026-04-19', '2026-04-25', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(222, 23, 8, 'BAC Resolution Recommending Award', 8, '2026-04-26', '2026-04-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(223, 23, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-27', '2026-04-28', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(224, 23, 10, 'Notice of Award Issuance', 10, '2026-04-29', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(225, 23, 11, 'Contract Preparation and Signing', 11, '2026-04-30', '2026-05-04', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(226, 23, 12, 'Notice to Proceed', 12, '2026-05-05', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(227, 23, 16, 'Delivery and Inspection', 13, '2026-05-06', '2026-05-06', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(228, 23, 17, 'Payment', 14, '2026-05-07', '2026-05-07', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:30:07', '2026-03-26 06:30:07'),
(229, 24, 1, 'Pre-Procurement Conference', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:37:14', '2026-03-30 12:54:52'),
(230, 24, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:37:14', '2026-04-03 05:08:50'),
(231, 24, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-03', '2026-04-09', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(232, 24, 4, 'Pre-Bid Conference', 4, '2026-04-10', '2026-04-10', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(233, 24, 5, 'Submission and Opening of Bids', 5, '2026-04-11', '2026-04-11', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(234, 24, 6, 'Bid Evaluation', 6, '2026-04-12', '2026-04-18', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(235, 24, 7, 'Post-Qualification', 7, '2026-04-19', '2026-04-25', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(236, 24, 8, 'BAC Resolution Recommending Award', 8, '2026-04-26', '2026-04-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(237, 24, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-27', '2026-04-28', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(238, 24, 10, 'Notice of Award Issuance', 10, '2026-04-29', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(239, 24, 11, 'Contract Preparation and Signing', 11, '2026-04-30', '2026-05-04', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(240, 24, 12, 'Notice to Proceed', 12, '2026-05-05', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(241, 24, 16, 'Delivery and Inspection', 13, '2026-05-06', '2026-05-06', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(242, 24, 187, 'Implementation', 14, '2026-05-07', '2026-05-07', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(243, 24, 17, 'Payment', 15, '2026-05-08', '2026-05-08', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:37:14', '2026-03-26 06:37:14'),
(244, 25, 1, 'Pre-Procurement Conference', 1, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:44:18', '2026-03-30 12:54:52'),
(245, 25, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-26 06:44:18', '2026-04-03 05:08:50'),
(246, 25, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-03', '2026-04-09', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(247, 25, 4, 'Pre-Bid Conference', 4, '2026-04-10', '2026-04-10', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(248, 25, 5, 'Submission and Opening of Bids', 5, '2026-04-11', '2026-04-11', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(249, 25, 6, 'Bid Evaluation', 6, '2026-04-12', '2026-04-18', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(250, 25, 7, 'Post-Qualification', 7, '2026-04-19', '2026-04-25', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(251, 25, 8, 'BAC Resolution Recommending Award', 8, '2026-04-26', '2026-04-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(252, 25, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-27', '2026-04-28', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(253, 25, 10, 'Notice of Award Issuance', 10, '2026-04-29', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(254, 25, 11, 'Contract Preparation and Signing', 11, '2026-04-30', '2026-05-04', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(255, 25, 12, 'Notice to Proceed', 12, '2026-05-05', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(256, 25, 16, 'Delivery and Inspection', 13, '2026-05-06', '2026-05-06', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(257, 25, 187, 'Implementation', 14, '2026-05-07', '2026-05-07', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(258, 25, 17, 'Payment', 15, '2026-05-08', '2026-05-08', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:44:18', '2026-03-26 06:44:18'),
(259, 26, NULL, 'Pre-Procurement Conference', 1, '2026-02-23', '2026-02-25', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:10:23', '2026-03-26 07:26:24'),
(260, 26, NULL, 'Posting / Advertisement', 2, '2026-02-26', '2026-03-07', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:10:23', '2026-03-26 07:26:24'),
(261, 26, NULL, 'Bid Submission / Opening', 3, '2026-03-08', '2026-03-08', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:10:23', '2026-03-26 07:26:24'),
(262, 26, NULL, 'Evaluation', 4, '2026-03-09', '2026-03-15', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:10:23', '2026-03-26 07:26:24'),
(263, 26, NULL, 'BAC Resolution', 5, '2026-03-16', '2026-03-18', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:10:23', '2026-03-26 07:26:24'),
(264, 26, NULL, 'Notice of Award', 6, '2026-03-19', '2026-03-23', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:10:23', '2026-03-26 07:26:24'),
(265, 26, NULL, 'Notice to Proceed', 7, '2026-03-24', '2026-03-25', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:10:23', '2026-03-26 07:26:24'),
(266, 26, NULL, 'Implementation', 8, '2026-03-26', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:10:23', '2026-03-30 12:54:52'),
(267, 27, NULL, 'Pre-Procurement Conference', 1, '2026-03-13', '2026-03-15', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:27:50'),
(268, 27, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-16', '2026-03-25', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:27:50'),
(269, 27, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-03-26', '2026-04-01', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:26:44', '2026-04-03 05:08:50'),
(270, 27, NULL, 'Pre-Bid Conference', 4, '2026-04-02', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:26:44', '2026-04-03 05:08:50'),
(271, 27, NULL, 'Submission and Opening of Bids', 5, '2026-04-03', '2026-04-03', NULL, 'DELAYED', NULL, NULL, '2026-03-26 07:26:44', '2026-04-05 01:29:36'),
(272, 27, NULL, 'Bid Evaluation', 6, '2026-04-04', '2026-04-10', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(273, 27, NULL, 'Post-Qualification', 7, '2026-04-11', '2026-04-17', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(274, 27, NULL, 'BAC Resolution Recommending Award', 8, '2026-04-18', '2026-04-18', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(275, 27, NULL, 'Notice of Award Preparation and Approval', 9, '2026-04-19', '2026-04-20', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(276, 27, NULL, 'Notice of Award Issuance', 10, '2026-04-21', '2026-04-21', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(277, 27, NULL, 'Contract Preparation and Signing', 11, '2026-04-22', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(278, 27, NULL, 'Notice to Proceed', 12, '2026-04-25', '2026-04-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(279, 27, NULL, 'Implementation', 13, '2026-04-27', '2026-04-27', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(280, 27, NULL, 'Delivery', 14, '2026-04-28', '2026-04-28', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(281, 27, NULL, 'Inspection', 15, '2026-04-29', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(282, 27, NULL, 'Acceptance', 16, '2026-04-30', '2026-04-30', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(283, 27, NULL, 'Payment Processing', 17, '2026-05-01', '2026-05-01', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(284, 27, NULL, 'Project Closeout', 18, '2026-05-02', '2026-05-02', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:26:44', '2026-03-26 07:26:44'),
(285, 28, NULL, 'Pre-Procurement Conference', 1, '2026-04-11', '2026-04-13', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(286, 28, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-04-14', '2026-04-23', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(287, 28, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-04-24', '2026-04-30', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(288, 28, NULL, 'Pre-Bid Conference', 4, '2026-05-01', '2026-05-01', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(289, 28, NULL, 'Submission and Opening of Bids', 5, '2026-05-02', '2026-05-02', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(290, 28, NULL, 'Bid Evaluation', 6, '2026-05-03', '2026-05-09', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(291, 28, NULL, 'Post-Qualification', 7, '2026-05-10', '2026-05-16', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(292, 28, NULL, 'BAC Resolution Recommending Award', 8, '2026-05-17', '2026-05-17', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(293, 28, NULL, 'Notice of Award Preparation and Approval', 9, '2026-05-18', '2026-05-19', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(294, 28, NULL, 'Notice of Award Issuance', 10, '2026-05-20', '2026-05-20', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(295, 28, NULL, 'Contract Preparation and Signing', 11, '2026-05-21', '2026-05-23', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(296, 28, NULL, 'Notice to Proceed', 12, '2026-05-24', '2026-05-25', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(297, 28, NULL, 'Implementation', 13, '2026-05-26', '2026-05-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(298, 28, NULL, 'Delivery and Inspection', 14, '2026-05-27', '2026-05-27', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(299, 28, NULL, 'Payment Processing', 15, '2026-05-28', '2026-05-28', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:31:20', '2026-03-26 07:31:20'),
(300, 29, NULL, 'Pre-Procurement Conference', 1, '2026-04-11', '2026-04-13', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(301, 29, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-04-14', '2026-04-23', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(302, 29, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-04-24', '2026-04-30', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(303, 29, NULL, 'Pre-Bid Conference', 4, '2026-05-01', '2026-05-01', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(304, 29, NULL, 'Submission and Opening of Bids', 5, '2026-05-02', '2026-05-02', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(305, 29, NULL, 'Bid Evaluation', 6, '2026-05-03', '2026-05-09', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(306, 29, NULL, 'Post-Qualification', 7, '2026-05-10', '2026-05-16', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(307, 29, NULL, 'BAC Resolution Recommending Award', 8, '2026-05-17', '2026-05-17', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(308, 29, NULL, 'Notice of Award Preparation and Approval', 9, '2026-05-18', '2026-05-19', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(309, 29, NULL, 'Notice of Award Issuance', 10, '2026-05-20', '2026-05-20', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(310, 29, NULL, 'Contract Preparation and Signing', 11, '2026-05-21', '2026-05-23', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(311, 29, NULL, 'Notice to Proceed', 12, '2026-05-24', '2026-05-25', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(312, 29, NULL, 'Implementation', 13, '2026-05-26', '2026-05-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(313, 29, NULL, 'Delivery and Inspection', 14, '2026-05-27', '2026-05-27', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(314, 29, NULL, 'Payment Processing', 15, '2026-05-28', '2026-05-28', NULL, 'PENDING', NULL, NULL, '2026-03-26 07:39:45', '2026-03-26 07:39:45'),
(315, 30, NULL, 'Preparation of Bidding Documents', 1, '2026-04-29', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(316, 30, NULL, 'Pre-Procurement Conference', 2, '2026-04-30', '2026-04-30', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(317, 30, NULL, 'Advertisement / Posting of Invitation to Bid', 3, '2026-05-01', '2026-05-07', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(318, 30, NULL, 'Pre-Bid Conference', 4, '2026-05-08', '2026-05-19', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(319, 30, NULL, 'Eligibility Check / Deadline of Submission and Receipt of Bids / Bid Opening', 5, '2026-05-20', '2026-05-20', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(320, 30, NULL, 'Bid Evaluation', 6, '2026-05-21', '2026-05-21', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(321, 30, NULL, 'Post-Qualification', 7, '2026-05-22', '2026-06-02', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(322, 30, NULL, 'Preparation and Approval of Resolution to Award', 8, '2026-06-03', '2026-06-13', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(323, 30, NULL, 'Issuance and Signing of Notice of Award', 9, '2026-06-14', '2026-06-14', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(324, 30, NULL, 'Contract Preparation and Signing of Contract', 10, '2026-06-15', '2026-06-25', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(325, 30, NULL, 'Issuance and Signing of Notice to Proceed', 11, '2026-06-26', '2026-06-26', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(326, 30, NULL, 'Implementation', 12, '2026-06-27', '2026-06-27', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(327, 30, NULL, 'Delivery and Inspection', 13, '2026-06-28', '2026-06-28', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(328, 30, NULL, 'Payment Processing', 14, '2026-06-29', '2026-06-29', NULL, 'PENDING', NULL, NULL, '2026-03-27 05:20:21', '2026-03-27 05:20:21'),
(329, 31, NULL, 'Preparation of Purchase Request', 1, '2026-03-04', '2026-03-04', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(330, 31, NULL, 'Submission and Receipt of Approved Purchase Request', 2, '2026-03-04', '2026-03-04', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(331, 31, NULL, 'Preparation of Request for Quotation (RFQ)', 3, '2026-03-05', '2026-03-08', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(332, 31, NULL, 'Posting of RFQ or Conduct of Canvass', 4, '2026-03-09', '2026-03-11', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(333, 31, NULL, 'Opening of bids documents / Preparation of Abstract of Quotation', 5, '2026-03-12', '2026-03-12', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(334, 31, NULL, 'Preparation and Approval of Purchase Order (PO)', 6, '2026-03-13', '2026-03-16', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(335, 31, NULL, 'Allowance period of the supplier', 7, '2026-03-17', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(336, 31, NULL, 'Implementation', 8, '2026-03-27', '2026-03-27', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(337, 31, NULL, 'Delivery and Inspection', 9, '2026-03-28', '2026-03-28', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(338, 31, NULL, 'Payment Processing', 10, '2026-03-29', '2026-03-29', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:04:09', '2026-03-30 12:54:52'),
(351, 33, NULL, 'Pre-Procurement Conference', 1, '2026-02-27', '2026-03-01', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:12:03', '2026-03-30 12:54:52'),
(352, 33, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-02', '2026-03-11', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:12:03', '2026-03-30 12:54:52'),
(353, 33, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-03-12', '2026-03-18', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:12:03', '2026-03-30 12:54:52'),
(354, 33, NULL, 'Pre-Bid Conference', 4, '2026-03-19', '2026-03-19', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:12:03', '2026-03-30 12:54:52'),
(355, 33, NULL, 'Submission and Opening of Bids', 5, '2026-03-20', '2026-03-20', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:12:03', '2026-03-30 12:54:52'),
(356, 33, NULL, 'Bid Evaluation', 6, '2026-03-21', '2026-03-27', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:12:03', '2026-03-30 12:54:52'),
(357, 33, NULL, 'Post-Qualification', 7, '2026-03-28', '2026-04-03', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:12:03', '2026-04-05 01:29:36'),
(358, 33, NULL, 'BAC Resolution Recommending Award', 8, '2026-04-04', '2026-04-04', NULL, 'DELAYED', NULL, NULL, '2026-03-27 06:12:03', '2026-04-05 01:29:36'),
(359, 33, NULL, 'Notice of Award Preparation and Approval', 9, '2026-04-05', '2026-04-06', NULL, 'PENDING', NULL, NULL, '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(360, 33, NULL, 'Notice of Award Issuance', 10, '2026-04-07', '2026-04-07', NULL, 'PENDING', NULL, NULL, '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(361, 33, NULL, 'Contract Preparation and Signing', 11, '2026-04-08', '2026-04-10', NULL, 'PENDING', NULL, NULL, '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(362, 33, NULL, 'Notice to Proceed', 12, '2026-04-11', '2026-04-12', NULL, 'PENDING', NULL, NULL, '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(363, 33, NULL, 'Implementation', 13, '2026-04-13', '2026-04-13', NULL, 'PENDING', NULL, NULL, '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(364, 33, NULL, 'Delivery and Inspection', 14, '2026-04-14', '2026-04-14', NULL, 'PENDING', NULL, NULL, '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(365, 33, NULL, 'Payment Processing', 15, '2026-04-15', '2026-04-15', NULL, 'PENDING', NULL, NULL, '2026-03-27 06:12:03', '2026-03-27 06:12:03'),
(366, 34, NULL, 'Two Failed Biddings / Review', 1, '2026-05-28', '2026-05-28', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:40:45', '2026-03-30 10:40:45'),
(367, 34, NULL, 'Submission of Best Offer', 2, '2026-05-29', '2026-05-29', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:40:45', '2026-03-30 10:40:45'),
(368, 34, NULL, 'Implementation', 3, '2026-05-30', '2026-05-30', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:40:45', '2026-03-30 10:40:45'),
(369, 34, NULL, 'Delivery and Inspection', 4, '2026-05-31', '2026-05-31', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:40:45', '2026-03-30 10:40:45'),
(370, 34, NULL, 'Payment Processing', 5, '2026-06-01', '2026-06-01', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:40:45', '2026-03-30 10:40:45'),
(371, 35, NULL, 'Shortlisting Phase', 1, '2026-07-10', '2026-07-29', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:42:50', '2026-03-30 10:42:50'),
(372, 35, NULL, 'Implementation', 2, '2026-07-30', '2026-07-30', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:42:50', '2026-03-30 10:42:50'),
(373, 35, NULL, 'Delivery and Inspection', 3, '2026-07-31', '2026-07-31', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:42:50', '2026-03-30 10:42:50'),
(374, 35, NULL, 'Payment Processing', 4, '2026-08-01', '2026-08-01', NULL, 'PENDING', NULL, NULL, '2026-03-30 10:42:50', '2026-03-30 10:42:50'),
(375, 36, NULL, 'Preparation of Purchase Request', 1, '2026-03-01', '2026-03-01', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 12:54:52'),
(376, 36, NULL, 'Submission and Receipt of Approved Purchase Request', 2, '2026-03-01', '2026-03-01', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 12:54:52'),
(377, 36, NULL, 'Preparation of Request for Quotation (RFQ)', 3, '2026-03-02', '2026-03-05', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 12:54:52'),
(378, 36, NULL, 'Posting of RFQ or Conduct of Canvass', 4, '2026-03-06', '2026-03-08', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 12:54:52'),
(379, 36, NULL, 'Preparation of Abstract of Quotation / Resolution to Award', 5, '2026-03-09', '2026-03-12', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 12:54:52'),
(380, 36, NULL, 'Notice of Award', 6, '2026-03-13', '2026-03-14', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 12:54:52'),
(381, 36, NULL, 'Preparation and Approval of Purchase Order (PO)', 7, '2026-03-15', '2026-03-18', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 12:54:52'),
(382, 36, NULL, 'Preparation and Signing of Notice to Proceed', 8, '2026-03-19', '2026-03-20', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-30 12:54:52'),
(383, 36, NULL, 'Allowance period of the supplier', 9, '2026-03-21', '2026-03-30', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-03-31 00:04:45'),
(384, 36, NULL, 'Implementation', 10, '2026-03-31', '2026-03-31', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-04-03 05:08:50'),
(385, 36, NULL, 'Delivery and Inspection', 11, '2026-04-01', '2026-04-01', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-04-03 05:08:50'),
(386, 36, NULL, 'Payment Processing', 12, '2026-04-02', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:44:11', '2026-04-03 05:08:50'),
(387, 37, NULL, 'Pre-assessment of Proposal', 1, '2025-11-25', '2025-12-14', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:45:10', '2026-03-30 12:54:52'),
(388, 37, NULL, 'Submission of Initial Offer', 2, '2025-12-15', '2026-01-13', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:45:10', '2026-03-30 12:54:52'),
(389, 37, NULL, 'Detailed Offer Evaluation', 3, '2026-01-14', '2026-03-14', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:45:10', '2026-03-30 12:54:52'),
(390, 37, NULL, 'Negotiation of Terms', 4, '2026-03-15', '2026-03-15', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:45:10', '2026-03-30 12:54:52'),
(391, 37, NULL, 'Comparative Bid Matching', 5, '2026-03-16', '2026-03-30', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:45:10', '2026-03-31 00:04:45'),
(392, 37, NULL, 'Implementation', 6, '2026-05-31', '2026-05-31', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:45:10', '2026-04-03 09:50:40'),
(393, 37, NULL, 'Delivery and Inspection', 7, '2026-04-01', '2026-04-01', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:45:10', '2026-04-03 05:08:50'),
(394, 37, NULL, 'Payment Processing', 8, '2026-04-02', '2026-04-02', NULL, 'DELAYED', NULL, NULL, '2026-03-30 10:45:10', '2026-04-03 05:08:50'),
(395, 38, NULL, 'Pre-Procurement Conference', 1, '2026-05-16', '2026-05-18', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(396, 38, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-05-19', '2026-05-28', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(397, 38, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-05-29', '2026-06-04', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(398, 38, NULL, 'Pre-Bid Conference', 4, '2026-06-05', '2026-06-05', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(399, 38, NULL, 'Submission and Opening of Bids', 5, '2026-06-06', '2026-06-06', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(400, 38, NULL, 'Bid Evaluation', 6, '2026-06-07', '2026-06-13', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(401, 38, NULL, 'Post-Qualification', 7, '2026-06-14', '2026-06-20', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(402, 38, NULL, 'BAC Resolution Recommending Award', 8, '2026-06-21', '2026-06-21', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(403, 38, NULL, 'Notice of Award Preparation and Approval', 9, '2026-06-22', '2026-06-23', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(404, 38, NULL, 'Notice of Award Issuance', 10, '2026-06-24', '2026-06-24', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(405, 38, NULL, 'Contract Preparation and Signing', 11, '2026-06-25', '2026-06-27', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(406, 38, NULL, 'Notice to Proceed', 12, '2026-06-28', '2026-06-29', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(407, 38, NULL, 'Implementation', 13, '2026-06-30', '2026-06-30', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(408, 38, NULL, 'Delivery and Inspection', 14, '2026-07-01', '2026-07-01', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(409, 38, NULL, 'Payment Processing', 15, '2026-07-02', '2026-07-02', NULL, 'PENDING', NULL, NULL, '2026-03-30 15:20:46', '2026-03-30 15:20:46'),
(410, 39, NULL, 'Preparation of Purchase Request', 1, '2026-05-01', '2026-05-01', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(411, 39, NULL, 'Submission and Receipt of Approved Purchase Request', 2, '2026-05-01', '2026-05-01', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(412, 39, NULL, 'Preparation of Request for Quotation (RFQ)', 3, '2026-05-02', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(413, 39, NULL, 'Posting of RFQ or Conduct of Canvass', 4, '2026-05-06', '2026-05-08', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(414, 39, NULL, 'Preparation of Abstract of Quotation / Resolution to Award', 5, '2026-05-09', '2026-05-12', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(415, 39, NULL, 'Notice of Award', 6, '2026-05-13', '2026-05-14', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(416, 39, NULL, 'Preparation and Approval of Purchase Order (PO)', 7, '2026-05-15', '2026-05-18', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(417, 39, NULL, 'Preparation and Signing of Notice to Proceed', 8, '2026-05-19', '2026-05-20', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(418, 39, NULL, 'Allowance period of the supplier', 9, '2026-05-21', '2026-05-30', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(419, 39, NULL, 'Implementation', 10, '2026-05-31', '2026-05-31', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(420, 39, NULL, 'Delivery and Inspection', 11, '2026-06-01', '2026-06-01', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(421, 39, NULL, 'Payment Processing', 12, '2026-06-02', '2026-06-02', NULL, 'PENDING', NULL, NULL, '2026-03-31 00:57:50', '2026-03-31 00:57:50'),
(422, 40, NULL, 'Pre-Procurement Conference', 1, '2026-03-19', '2026-03-21', NULL, 'DELAYED', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:27:33'),
(423, 40, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-22', '2026-03-31', NULL, 'DELAYED', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:27:33'),
(424, 40, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-04-01', '2026-04-07', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(425, 40, NULL, 'Pre-Bid Conference', 4, '2026-04-08', '2026-04-08', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(426, 40, NULL, 'Submission and Opening of Bids', 5, '2026-04-09', '2026-04-09', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(427, 40, NULL, 'Bid Evaluation', 6, '2026-04-10', '2026-04-16', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(428, 40, NULL, 'Post-Qualification', 7, '2026-04-17', '2026-04-23', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(429, 40, NULL, 'BAC Resolution Recommending Award', 8, '2026-04-24', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(430, 40, NULL, 'Notice of Award Preparation and Approval', 9, '2026-04-25', '2026-04-26', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(431, 40, NULL, 'Notice of Award Issuance', 10, '2026-04-27', '2026-04-27', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(432, 40, NULL, 'Contract Preparation and Signing', 11, '2026-04-28', '2026-04-30', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(433, 40, NULL, 'Notice to Proceed', 12, '2026-05-01', '2026-05-02', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(434, 40, NULL, 'Implementation', 13, '2026-05-03', '2026-05-03', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(435, 40, NULL, 'Delivery and Inspection', 14, '2026-05-04', '2026-05-04', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(436, 40, NULL, 'Payment Processing', 15, '2026-05-05', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:12:00', '2026-04-03 09:12:00'),
(437, 41, NULL, 'Pre-Procurement Conference', 1, '2026-04-03', '2026-04-05', NULL, 'DELAYED', NULL, NULL, '2026-04-03 09:13:05', '2026-04-06 01:39:08'),
(438, 41, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-04-06', '2026-04-15', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(439, 41, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-04-16', '2026-04-22', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(440, 41, NULL, 'Pre-Bid Conference', 4, '2026-04-23', '2026-04-23', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(441, 41, NULL, 'Submission and Opening of Bids', 5, '2026-04-24', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(442, 41, NULL, 'Bid Evaluation', 6, '2026-04-25', '2026-05-01', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(443, 41, NULL, 'Post-Qualification', 7, '2026-05-02', '2026-05-08', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(444, 41, NULL, 'BAC Resolution Recommending Award', 8, '2026-05-09', '2026-05-09', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(445, 41, NULL, 'Notice of Award Preparation and Approval', 9, '2026-05-10', '2026-05-11', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(446, 41, NULL, 'Notice of Award Issuance', 10, '2026-05-12', '2026-05-12', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(447, 41, NULL, 'Contract Preparation and Signing', 11, '2026-05-13', '2026-05-15', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(448, 41, NULL, 'Notice to Proceed', 12, '2026-05-16', '2026-05-17', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(449, 41, NULL, 'Implementation', 13, '2026-05-18', '2026-05-18', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(450, 41, NULL, 'Delivery and Inspection', 14, '2026-05-19', '2026-05-19', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(451, 41, NULL, 'Payment Processing', 15, '2026-05-20', '2026-05-20', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:13:05', '2026-04-03 09:13:05'),
(452, 42, NULL, 'Pre-Procurement Conference', 1, '2026-03-24', '2026-03-26', NULL, 'DELAYED', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:27:33'),
(453, 42, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-05', NULL, 'DELAYED', NULL, NULL, '2026-04-03 09:15:15', '2026-04-06 01:39:08'),
(454, 42, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-04-06', '2026-04-12', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(455, 42, NULL, 'Pre-Bid Conference', 4, '2026-04-13', '2026-04-13', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(456, 42, NULL, 'Submission and Opening of Bids', 5, '2026-04-14', '2026-04-14', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(457, 42, NULL, 'Bid Evaluation', 6, '2026-04-15', '2026-04-21', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(458, 42, NULL, 'Post-Qualification', 7, '2026-04-22', '2026-04-28', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(459, 42, NULL, 'BAC Resolution Recommending Award', 8, '2026-04-29', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15');
INSERT INTO `project_activities` (`id`, `bac_cycle_id`, `template_id`, `step_name`, `step_order`, `planned_start_date`, `planned_end_date`, `actual_completion_date`, `status`, `compliance_status`, `compliance_remarks`, `created_at`, `updated_at`) VALUES
(460, 42, NULL, 'Notice of Award Preparation and Approval', 9, '2026-04-30', '2026-05-01', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(461, 42, NULL, 'Notice of Award Issuance', 10, '2026-05-02', '2026-05-02', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(462, 42, NULL, 'Contract Preparation and Signing', 11, '2026-05-03', '2026-05-05', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(463, 42, NULL, 'Notice to Proceed', 12, '2026-05-06', '2026-05-07', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(464, 42, NULL, 'Implementation', 13, '2026-05-08', '2026-05-08', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(465, 42, NULL, 'Delivery and Inspection', 14, '2026-05-09', '2026-05-09', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(466, 42, NULL, 'Payment Processing', 15, '2026-05-10', '2026-05-10', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:15', '2026-04-03 09:15:15'),
(467, 43, NULL, 'Pre-Procurement Conference', 1, '2026-04-19', '2026-04-21', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(468, 43, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-04-22', '2026-05-01', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(469, 43, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-05-02', '2026-05-08', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(470, 43, NULL, 'Pre-Bid Conference', 4, '2026-05-09', '2026-05-09', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(471, 43, NULL, 'Submission and Opening of Bids', 5, '2026-05-10', '2026-05-10', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(472, 43, NULL, 'Bid Evaluation', 6, '2026-05-11', '2026-05-17', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(473, 43, NULL, 'Post-Qualification', 7, '2026-05-18', '2026-05-24', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(474, 43, NULL, 'BAC Resolution Recommending Award', 8, '2026-05-25', '2026-05-25', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(475, 43, NULL, 'Notice of Award Preparation and Approval', 9, '2026-05-26', '2026-05-27', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(476, 43, NULL, 'Notice of Award Issuance', 10, '2026-05-28', '2026-05-28', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(477, 43, NULL, 'Contract Preparation and Signing', 11, '2026-05-29', '2026-05-31', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(478, 43, NULL, 'Notice to Proceed', 12, '2026-06-01', '2026-06-02', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(479, 43, NULL, 'Implementation', 13, '2026-06-03', '2026-06-03', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(480, 43, NULL, 'Delivery and Inspection', 14, '2026-06-04', '2026-06-04', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(481, 43, NULL, 'Payment Processing', 15, '2026-06-05', '2026-06-05', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:15:48', '2026-04-03 09:15:48'),
(482, 44, NULL, 'Pre-Procurement Conference', 1, '2026-05-19', '2026-05-21', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(483, 44, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-05-22', '2026-05-31', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(484, 44, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-06-01', '2026-06-07', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(485, 44, NULL, 'Pre-Bid Conference', 4, '2026-06-08', '2026-06-08', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(486, 44, NULL, 'Submission and Opening of Bids', 5, '2026-06-09', '2026-06-09', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(487, 44, NULL, 'Bid Evaluation', 6, '2026-06-10', '2026-06-16', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(488, 44, NULL, 'Post-Qualification', 7, '2026-06-17', '2026-06-23', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(489, 44, NULL, 'BAC Resolution Recommending Award', 8, '2026-06-24', '2026-06-24', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(490, 44, NULL, 'Notice of Award Preparation and Approval', 9, '2026-06-25', '2026-06-26', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(491, 44, NULL, 'Notice of Award Issuance', 10, '2026-06-27', '2026-06-27', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(492, 44, NULL, 'Contract Preparation and Signing', 11, '2026-06-28', '2026-06-30', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(493, 44, NULL, 'Notice to Proceed', 12, '2026-07-01', '2026-07-02', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(494, 44, NULL, 'Implementation', 13, '2026-07-03', '2026-07-03', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(495, 44, NULL, 'Delivery and Inspection', 14, '2026-07-04', '2026-07-04', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(496, 44, NULL, 'Payment Processing', 15, '2026-07-05', '2026-07-05', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:17:13', '2026-04-03 09:17:13'),
(497, 45, NULL, 'Market Identification (<= P200K)', 1, '2026-07-17', '2026-07-17', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:18:09', '2026-04-03 09:18:09'),
(498, 45, NULL, 'Direct Purchase and Recording', 2, '2026-07-18', '2026-07-18', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:18:09', '2026-04-03 09:18:09'),
(499, 45, NULL, 'Implementation', 3, '2026-07-19', '2026-07-19', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:18:09', '2026-04-03 09:18:09'),
(500, 45, NULL, 'Delivery and Inspection', 4, '2026-07-20', '2026-07-20', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:18:09', '2026-04-03 09:18:09'),
(501, 45, NULL, 'Payment Processing', 5, '2026-07-21', '2026-07-21', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:18:09', '2026-04-03 09:18:09'),
(502, 46, NULL, 'Pre-Procurement Conference', 1, '2026-07-20', '2026-07-22', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(503, 46, NULL, 'Advertisement and Posting of Invitation to Bid', 2, '2026-07-23', '2026-08-01', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(504, 46, NULL, 'Issuance and Availability of Bidding Documents', 3, '2026-08-02', '2026-08-08', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(505, 46, NULL, 'Pre-Bid Conference', 4, '2026-08-09', '2026-08-09', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(506, 46, NULL, 'Submission and Opening of Bids', 5, '2026-08-10', '2026-08-10', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(507, 46, NULL, 'Bid Evaluation', 6, '2026-08-11', '2026-08-17', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(508, 46, NULL, 'Post-Qualification', 7, '2026-08-18', '2026-08-24', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(509, 46, NULL, 'BAC Resolution Recommending Award', 8, '2026-08-25', '2026-08-25', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(510, 46, NULL, 'Notice of Award Preparation and Approval', 9, '2026-08-26', '2026-08-27', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(511, 46, NULL, 'Notice of Award Issuance', 10, '2026-08-28', '2026-08-28', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(512, 46, NULL, 'Contract Preparation and Signing', 11, '2026-08-29', '2026-08-31', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(513, 46, NULL, 'Notice to Proceed', 12, '2026-09-01', '2026-09-02', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(514, 46, NULL, 'Implementation', 13, '2026-09-03', '2026-09-03', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(515, 46, NULL, 'Delivery and Inspection', 14, '2026-09-04', '2026-09-04', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02'),
(516, 46, NULL, 'Payment Processing', 15, '2026-09-05', '2026-09-05', NULL, 'PENDING', NULL, NULL, '2026-04-03 09:19:02', '2026-04-03 09:19:02');

-- --------------------------------------------------------

--
-- Table structure for table `project_documents`
--

CREATE TABLE `project_documents` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'other',
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schema_migrations`
--

INSERT INTO `schema_migrations` (`id`, `filename`, `applied_at`) VALUES
(1, '001_add_registration_fields.sql', '2026-03-26 05:57:05'),
(2, '002_sessions_table.sql', '2026-03-26 05:57:05'),
(3, '003_project_approval_status.sql', '2026-03-26 05:57:09'),
(4, '004_project_rejection.sql', '2026-03-26 05:57:09'),
(5, '005_project_draft_review.sql', '2026-03-26 05:57:09'),
(6, '006_project_documents.sql', '2026-03-26 05:57:09'),
(7, '007_superadmin_role.sql', '2026-03-26 05:57:09'),
(8, '008_user_avatar.sql', '2026-03-26 05:57:19'),
(9, '009_user_is_active.sql', '2026-03-26 05:57:19'),
(10, '010_bac_chairman_secretary_roles.sql', '2026-03-26 05:57:19'),
(11, '011_competitive_bidding_annex_a_template.sql', '2026-03-26 05:57:35'),
(12, '012_small_value_procurement_templates.sql', '2026-03-26 05:57:50'),
(13, '013_enable_all_procurement_type_templates.sql', '2026-03-26 06:13:23'),
(14, '014_add_implementation_before_payment.sql', '2026-03-26 06:34:07'),
(15, '015_fix_implementation_step_order.sql', '2026-03-26 06:36:03'),
(16, '016_update_procurement_templates_from_image.sql', '2026-03-30 10:32:09'),
(17, '017_add_project_owner_name.sql', '2026-03-30 15:13:10'),
(18, '018_add_bactrack_id.sql', '2026-03-31 00:52:26'),
(19, '019_add_approved_budget.sql', '2026-03-31 15:00:26');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `token`, `device_info`, `login_time`, `expires_at`) VALUES
(1, 3, 'a7cf66e646d1eb69bd7f7d6efbaadf2691cd353f79e01758197b133cdb84c9cf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 01:46:33', '2026-02-05 17:46:32'),
(4, 3, 'e54d2f69a8bf2c8ed78afc8ef013a85fc2871cf3d81593c1c47ff6cfc0ca81a8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 01:48:12', '2026-02-05 18:05:12'),
(9, 3, '8c59ae2c19bc2a834d2fc5f2d31d013fc3426091d52bf2d85bf1b0d7356207b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:05:17', '2026-02-05 18:05:27'),
(10, 3, 'a99fbeb0484a0b0a3037c09b998159769bfad789a2cd3062c399bb59cde720d9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:05:30', '2026-02-05 18:05:35'),
(11, 3, '91c6e904ceb93727fb3537c3379893d3916c2c50438354ec44ee1de3b65ff146', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:06:01', '2026-02-05 18:08:26'),
(17, 3, '29f1347fa993388d260653c6a24aede0402cf1ad2d14f7355dddf5efe0ffcceb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:08:33', '2026-02-05 18:09:43'),
(22, 3, '812fd933ea7225a43e8e2090b678eb018a352ee2b1a65b694bcb7b35bb426d20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:17:52', '2026-02-05 18:17:52'),
(27, 3, '24a92a3d07584f4dc7718af7b536af533067de3ba24bed37b5ee2718c9ca429a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:18:18', '2026-02-05 18:18:20'),
(30, 3, '25354c624ffbebd38724adb214dfe8827a0ef8e760e50a095f2c816d190ebb5f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:19:02', '2026-02-05 18:21:29'),
(33, 3, '98344bae26220f78a33b349aaf22799d6648fcd93a869fa19df4e99ddc8d66ec', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:24:46', '2026-02-05 18:25:10'),
(35, 3, '64e6c9e2f5f75a7df5114414501636e39432fb111995d0d7c38b58962a79452c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:44:42', '2026-02-05 19:55:01'),
(36, 4, '7954600449ea97720f6397ffb205be515b780719ac68f63934b80498ef097113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:50:55', '2026-02-05 18:51:22'),
(37, 4, '7c104c35944bddafe3c5248186ba39b3bc356e93af6b85141185781d8e937042', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:51:25', '2026-02-05 18:51:25'),
(39, 4, 'bde0d912de52ae3a514b2778f7a7be7f1c5e7e3e825108fc2e2222eefd9cb2ed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:09:25', '2026-02-05 19:31:21'),
(41, 4, '63d8c6fac0a52c1a69a88bf0281ead468c57780df5e4b9b5ea300943a86f01b5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:31:31', '2026-02-06 03:43:28'),
(42, 4, 'fe38c6766d3999b7077e2ce7293404d67c804b6c873b5d2c7201d6de460883ca', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:55:03', '2026-02-05 19:55:03'),
(43, 3, '92869a1ec1a60e92d75e48aa7996fcd3c33f8e69742394d91b6f4b04875cb4bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:55:13', '2026-02-05 19:58:02'),
(44, 3, '50bf2fcc1d39ba95db9022bc73f7800dbf2c1581036b5c89016a56f9a303fbec', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:58:09', '2026-02-05 19:59:39'),
(45, 3, 'bfd3001561c2ddbe243e254ef4eb548ba9e8301ef0b6203d0639e60c2b65217b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:59:42', '2026-02-05 19:59:59'),
(47, 3, '1e3ec26426097c6ea0003729ddd6f0f267ed8d0ca7833ec9ba25796963939aa7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 05:10:55', '2026-02-06 03:43:28'),
(48, 3, '678110c398b44443e9970caa7f5dd4da75c67e67e645c0f18a09f5d5fb69f667', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:54:06', '2026-02-08 17:54:06'),
(49, 3, '76eff6ad9a5a997a08443ffaf303630c1bfede186413f666b1655ff910e324f9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:54:13', '2026-02-08 17:54:13'),
(50, 3, '376a26f6750bed6762be185facd93e67d880c19d2a4d7146430e0d697b1f545d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:54:22', '2026-02-08 17:54:22'),
(53, 3, 'e653db9585d2b2ea8d7f65bea07079b3656e9973ffcae280d942722a825f9f15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 04:43:31', '2026-02-10 22:45:27'),
(58, 3, '6bfb0397ee52644413c1bbcc5da9abdab6f30ee5eeecdfa7f524b9d37c1acbb6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:31:45', '2026-02-11 21:31:46'),
(59, 3, 'b3b6a7b82ca18894374694470947f15c6947290814a63921b5508bbc6a92bb5f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:31:52', '2026-02-11 21:31:52'),
(60, 3, '8ba7e34092d0f108fbe429ed8ee6ca3b4f337a6f7ca067553675f57e7371370b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:31:57', '2026-02-11 21:31:57'),
(61, 3, '60168e660c23b26edf5b70d28b5061c73231dbbcb6af53fd60ae5225cc642de6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:32:06', '2026-02-11 21:32:06'),
(62, 3, 'b1b0acd808a2f6e45028b03346c795ad76920c9de736598b16082e2685608098', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:32:24', '2026-02-11 21:32:24'),
(63, 3, '13a1b38de35daba2045a7adc50f50b01c3cd0e36df0c0905391412f275caf2e7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:32:33', '2026-02-11 21:32:33'),
(64, 3, '658637ca32989509536054b46799f7bd3fea77d50d655ca3d6abc77fdeeebbb6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:32:35', '2026-02-11 21:32:35'),
(65, 3, 'd04e014de8908a7769e636c4b9688a7984212b8366714bc64d5049189f551aa2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:32:37', '2026-02-11 21:32:37'),
(66, 3, '8c1bb480d412fa1f5e7498a7e20d12d8e6f0cf97c78068ba68ddb74c3edb5db8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:32:40', '2026-02-11 21:32:40'),
(68, 3, '2f16226e899f815aa4152a76226c3f1cf318e16d2d2e3e13881d82d1bf2a27a4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:32:53', '2026-02-11 21:36:56'),
(69, 4, '7d3c2186ed454e28f9e973fd2f658c18774fdb6893f5f655bd56991139e5efd4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 05:33:14', '2026-02-11 21:33:18'),
(70, 3, 'cee9e75dcd192da79bda41b7c71c63927f35a0994baf9896a85c1f01d542194c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 03:20:02', '2026-02-16 19:20:02'),
(72, 3, '9e4e5af28a8245da54afd42a5eb2a3757063ff03ff498bd0641be4404d495ae3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 03:20:40', '2026-02-16 19:20:40'),
(73, 3, 'e51f38d75938ea58ac7d1270851000819d295177e53e1070ae2bb84047860d69', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 03:20:44', '2026-02-16 19:20:50'),
(74, 3, 'b2335c129d3bb38bab1fde0b6aea4b04f48bfbdf5e7889b3882a79045f7d8b5b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 03:20:54', '2026-02-16 19:20:54'),
(75, 3, '6c97bec36dc4eb0a5a1d1e80344d813c7c83b7abaa748ad4d8abc60c341acf96', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 03:20:58', '2026-02-16 19:20:58'),
(76, 3, '2ed85f554586ae6906b780530992c488b2c653faf799c744f6ed182f6e93ce9b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 03:21:01', '2026-02-16 19:21:01'),
(77, 3, '80e98695e5d6fc860c9274adc29168522211301f57912dde82f6a1b516fa6e34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 03:21:04', '2026-02-16 19:21:04'),
(78, 3, '186e78fd21ce561719746721424693d0a70f819f56c99c2e10d099597550dcd0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 03:21:34', '2026-02-16 19:27:00'),
(80, 3, '941491ff72434f80a2b57a69eb6b52b0a35ca1834adc77d56833fb8cd2b0c42d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 07:16:44', '2026-02-26 23:16:44'),
(81, 3, 'c5ee9fbc47b42807c50bb06734a8354b6b53a5d9586094db3bb954cccfd69097', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 07:17:11', '2026-02-26 23:17:11'),
(82, 3, 'e7440253ee4e69190f40abaa57e2093be70e35293927d34991cc6a22ece8dd62', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 07:17:13', '2026-02-26 23:17:13'),
(83, 3, 'b8905f28bc2abbe4b44d05a9237e028e7c1b2992cb38334c0d71ed6652f84980', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 07:17:15', '2026-02-26 23:17:15'),
(84, 3, '12e5da60552c7de3c5c0acaa7b70dab0c28f9fec1dc4956b6ce9768a2dcd9437', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 07:17:17', '2026-02-26 23:17:17'),
(85, 3, 'e26f686b8052778e8a1cca0b5c15fe9554916ec995e915250572a13cd6f7fbf3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 07:17:42', '2026-02-26 23:20:48'),
(86, 3, '395bd8d616873ac73d6356636e72cc1b2a4c1e9338f097b3d129c00c45bffdbc', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 05:40:47', '2026-03-05 21:40:47'),
(87, 3, 'f46dff2ac7bbed171b093130148b0441500cf7580f2f3841c85441a19b271c59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 05:40:50', '2026-03-05 21:40:50'),
(88, 3, 'a814afc08f92b0a07281550cca9eeb721093772e7a66349d8b3eeed3f0d8e124', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 05:40:54', '2026-03-05 21:40:58'),
(90, 3, '6a9e78151008651298e695f70a86b2addae61111b06bca419599b78bb1f8ccf4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 05:53:23', '2026-03-05 21:53:23'),
(91, 3, 'c7db5a577f59b0dbb5cd35e35a7fd8d03e21fe35052ed7107f9cedebc1614e8d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 05:53:23', '2026-03-05 23:41:29'),
(92, 4, 'c1a82826b682c5558cbfb4e5c5256bc848dea0b50f8dd0d7010842cad8c366dd', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 07:35:43', '2026-03-05 23:41:24'),
(93, 3, 'e2080d3ffd1ad6331c754c9ecaf218ad175377e15a5cef60f9d96eafe697f3f9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 08:12:53', '2026-03-06 00:12:53'),
(94, 3, 'ae160fa8dffc983e99399a81b99b4047fc1951086cca6d817f23c2fbcc876e93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 08:13:03', '2026-03-06 00:13:03'),
(95, 3, 'c5bb570e82c66fff2c30287510066848f176464ba212c291e7d96ab0c310e150', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 08:13:13', '2026-03-06 00:13:15'),
(96, 3, '0085f8942c8daf2b6e54f275caf5d024c8ce2acbe898bc8b652d059f43164c91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 08:22:01', '2026-03-06 00:48:18'),
(97, 3, '3cd8d458d9de2cdbddc2a47b5999f2ed4cf0827ff8bdfb6e4e26d0d9257e4a9a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 08:48:18', '2026-03-06 00:48:20'),
(101, 4, '2b1b23f8e0d83b2ee67388384633bf7174026fa1aecdc49c900c6da7fd3bfbb1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-06 02:35:00', '2026-03-07 00:23:26'),
(104, 3, '8c5997e5fd4c67576e8a12e5ccf3357374b4927ddd6be34b23eee6266ff22b14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-06 08:16:37', '2026-03-07 00:45:55'),
(105, 4, '1a1d43f1ef3461e48a6786116ac02514634d3066eb5dcee1ef88276667f583e4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-06 08:25:32', '2026-03-07 00:32:11'),
(108, 10, 'b2fee4b99abe5ab7b596471ef1f74119014923fa14b2b7fbad41893d71398bd3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 06:07:16', '2026-03-10 00:13:33'),
(109, 4, '30dc415a428c0d0970146fd9033702462ed485344e0595fb03320c2338c07fd1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 06:46:37', '2026-03-10 00:35:09'),
(111, 10, '5cd8bfd6efa27bd87c3f54f45b83a0dc4473d75d834bb7d21662d2609d791d8b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 06:55:32', '2026-03-11 00:47:23'),
(112, 10, '8c3317d6b657f513c34bdaa354c9fbc0dcbc0504357bfd90531a1bcc15686afc', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 08:00:32', '2026-03-11 00:39:46'),
(113, 4, '1b943963319dbc88ca7ae8dc0331ee0730feb434beb0be9bc1fbfad99b12a4d0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 08:07:03', '2026-03-11 00:46:32'),
(114, 10, '5cf6d9f83580120b9351989588dfc2216739b371fc0f285d2fcdc36c88b77114', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 08:39:46', '2026-03-11 00:47:16'),
(115, 10, '28c2b961cef6d2bbb909c651386e72074fedf5ed5145b8a68bbefc0ddaf20470', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-11 00:35:28', '2026-03-11 22:43:35'),
(116, 4, '0b429bc352cd4254380e8aac4f6ad29170d975df3fb69509ba3598015166b9b6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-11 00:36:16', '2026-03-12 00:51:37'),
(117, 10, 'ffba7bcba52d9fee7d1ed0d8b352a9f8dc14562ba1759d32c50d042b7e2b6536', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-11 01:19:05', '2026-03-12 00:51:37'),
(118, 10, 'cf9a045601ccd15874944b9b6db8f05d609477c8aaeb8e5decf38ea4c3272e1c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-11 02:26:04', '2026-03-12 00:51:37'),
(119, 10, '1626b567f41958b4ceae4422b75f06fdec37298da5de6db57ab021d3e64d80bb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-11 06:43:54', '2026-03-12 00:51:52'),
(123, 10, 'ac8b14a6ab13441db4a2c81af39a73a5f7e4de7fd8874b05bfda9f3a8ba7955b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 07:59:48', '2026-03-13 00:46:51'),
(124, 1, 'ab98504f307bb4e33ce0ebc92504e237c9d5c378fc6bcc4a80c6f32f9d0fd4ea', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 08:02:45', '2026-03-13 00:23:04'),
(126, 1, '4b9c4447528ca39098e0c50f2f90a566b8906584b2f8ac34161389d2180bdb47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-12 08:35:03', '2026-03-13 00:42:10'),
(127, 10, 'c380de66d106c8f0d11e0c044850ad76f155d360cc976052a1c510144971fbb0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-12 15:10:10', '2026-03-13 07:10:48'),
(128, 10, 'c2a8f7c5e2a6cbe254c481e8ece000a5a9aad55590ad12e46fd260004b116329', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 01:37:34', '2026-03-14 01:06:06'),
(129, 4, 'd7a687772dec030b79dbbf0119cfe971b7f5655017ec339f540b30a9f12511b7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 06:49:08', '2026-03-14 01:05:36'),
(130, 10, '1d972c9e5eb94b89da07caca624aef9be0355203c32cdf0655a762c1ba30ae30', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-16 00:58:26', '2026-03-17 00:41:34'),
(132, 4, '75fb744859ca6bfa7da53f8f31976316bb027f45dbf5a0806b21da54b80fa373', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-16 03:12:00', '2026-03-17 00:41:34'),
(133, 4, '4c8ca5ac07582012fbe507fd9c5323f66bd8a5f83bb03e5433e6960540333a61', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-16 08:17:34', '2026-03-17 00:41:44'),
(134, 10, 'e2bcb3eb22e85e50311167857710b3509cfb628b934c80c40bb3d77ec7d313a9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-17 01:31:40', '2026-03-17 22:16:38'),
(135, 4, 'fea5fa478e5dad43bf28835ee74292f75c1baf5bc9ff59120fc5659482f0920c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-18 01:00:45', '2026-03-18 17:49:31'),
(136, 10, '64524a2d8e738840cb6280e0ce5fcd17433b79d61a5e3a678f7e0549c954dea2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-19 03:20:56', '2026-03-19 22:43:30'),
(138, 4, '28f3ce0b6b9d877e2a2c262b2ae69a39b7924014e00c2e22c768573a22fc842c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-23 01:12:23', '2026-03-24 01:18:16'),
(149, 4, '02024bf66619760cd7ff63723c508cea129be69958862d3da56fbaae7c6bff86', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-24 01:50:21', '2026-03-24 19:06:59'),
(155, 4, 'a2dddd0d967fcb53e4de0dd19753f0ba3efc4c473ac441641b772a67ce720db4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 02:22:08', '2026-03-26 21:38:42'),
(157, 4, '6dfd4e6adaa1d95cf2fa5d49fd8aa2afed4ad665778c00eb6d79d5ee5f025044', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 05:45:19', '2026-03-27 00:43:13'),
(160, 4, '5036edb3733c043606e9fd081f5a2a01e94b296d116728418836349aabec6e04', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 04:59:02', '2026-03-27 22:22:21'),
(163, 4, '7698fab76c5e5d034076a53077f7802062b03ba7df0bfa057c81be12893d2105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 14:55:12', '2026-03-31 07:48:09'),
(167, 4, '56ab068e84dacbf216db44af5aed90b96b667b3b499a91817cc3c1dce73dbca3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-31 13:38:36', '2026-04-01 06:46:29'),
(168, 4, '03c3daa4d9180c499641605ba489c45d1c7e769dcd10c15765759387026b66aa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-31 14:31:24', '2026-04-01 07:35:00'),
(169, 4, '798bdbe4d3909f9d0e3d4aa2d051ee6144e68d5f958558f742645d0dc97567fa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-02 02:40:29', '2026-04-02 21:53:07'),
(170, 4, 'c11c4a604ed78a316dbcbb9d63e803c3157babecfa6ac27781c6009c604f7985', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-03 03:39:01', '2026-04-04 05:35:28'),
(171, 4, '92efc6d024f6eb9dc28c819aa3818b5d76ec0c403013dd3898e6fb02d21651c4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 01:29:29', '2026-04-05 17:44:44'),
(172, 4, '9938970bc682e1ed502b732c4603494360cd51a570e78e65b402bfbf3d19175b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-06 01:39:04', '2026-04-06 18:02:07');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'helpdesk_url', 'http://192.168.11.1/icthelpdesk/login.php', '2026-03-10 00:28:19');

-- --------------------------------------------------------

--
-- Table structure for table `timeline_adjustment_requests`
--

CREATE TABLE `timeline_adjustment_requests` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `reason` text NOT NULL,
  `new_start_date` date NOT NULL,
  `new_end_date` date NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `reviewed_by` int(11) DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeline_adjustment_requests`
--

INSERT INTO `timeline_adjustment_requests` (`id`, `activity_id`, `requested_by`, `reason`, `new_start_date`, `new_end_date`, `status`, `reviewed_by`, `review_notes`, `created_at`, `reviewed_at`) VALUES
(14, 392, 4, 'test', '2026-05-31', '2026-05-31', 'APPROVED', 4, '', '2026-03-31 02:00:28', '2026-04-03 09:50:40'),
(15, 453, 4, 'need more time', '2026-04-05', '2026-04-14', 'REJECTED', 4, 'unsuitable', '2026-04-03 09:52:07', '2026-04-03 09:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `timeline_templates`
--

CREATE TABLE `timeline_templates` (
  `id` int(11) NOT NULL,
  `procurement_type` varchar(50) NOT NULL,
  `step_name` varchar(255) NOT NULL,
  `step_order` int(11) NOT NULL,
  `default_duration_days` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeline_templates`
--

INSERT INTO `timeline_templates` (`id`, `procurement_type`, `step_name`, `step_order`, `default_duration_days`, `created_at`) VALUES
(1, 'PUBLIC_BIDDING', 'Pre-Procurement Conference', 1, 1, '2026-02-04 08:47:44'),
(2, 'PUBLIC_BIDDING', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-02-04 08:47:44'),
(3, 'PUBLIC_BIDDING', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-02-04 08:47:44'),
(4, 'PUBLIC_BIDDING', 'Pre-Bid Conference', 4, 1, '2026-02-04 08:47:44'),
(5, 'PUBLIC_BIDDING', 'Submission and Opening of Bids', 5, 1, '2026-02-04 08:47:44'),
(6, 'PUBLIC_BIDDING', 'Bid Evaluation', 6, 7, '2026-02-04 08:47:44'),
(7, 'PUBLIC_BIDDING', 'Post-Qualification', 7, 7, '2026-02-04 08:47:44'),
(8, 'PUBLIC_BIDDING', 'BAC Resolution Recommending Award', 8, 1, '2026-02-04 08:47:44'),
(9, 'PUBLIC_BIDDING', 'Notice of Award Preparation and Approval', 9, 2, '2026-02-04 08:47:44'),
(10, 'PUBLIC_BIDDING', 'Notice of Award Issuance', 10, 1, '2026-02-04 08:47:44'),
(11, 'PUBLIC_BIDDING', 'Contract Preparation and Signing', 11, 5, '2026-02-04 08:47:44'),
(12, 'PUBLIC_BIDDING', 'Notice to Proceed', 12, 1, '2026-02-04 08:47:44'),
(13, 'DELIVERY_INSPECTION_PAYMENT', 'Delivery of Goods/Services', 1, 7, '2026-03-23 07:22:55'),
(14, 'DELIVERY_INSPECTION_PAYMENT', 'Inspection and Acceptance', 2, 3, '2026-03-23 07:22:55'),
(15, 'DELIVERY_INSPECTION_PAYMENT', 'Processing of Payment', 3, 5, '2026-03-23 07:22:55'),
(16, 'PUBLIC_BIDDING', 'Delivery and Inspection', 13, 1, '2026-03-23 07:34:28'),
(17, 'PUBLIC_BIDDING', 'Payment', 15, 1, '2026-03-23 07:34:28'),
(18, 'COMPETITIVE_BIDDING', 'Preparation of Bidding Documents', 1, 1, '2026-03-26 05:57:35'),
(19, 'COMPETITIVE_BIDDING', 'Pre-Procurement Conference', 2, 1, '2026-03-26 05:57:35'),
(20, 'COMPETITIVE_BIDDING', 'Advertisement / Posting of Invitation to Bid', 3, 7, '2026-03-26 05:57:35'),
(21, 'COMPETITIVE_BIDDING', 'Pre-Bid Conference', 4, 12, '2026-03-26 05:57:35'),
(22, 'COMPETITIVE_BIDDING', 'Eligibility Check / Deadline of Submission and Receipt of Bids / Bid Opening', 5, 1, '2026-03-26 05:57:35'),
(23, 'COMPETITIVE_BIDDING', 'Bid Evaluation', 6, 1, '2026-03-26 05:57:35'),
(24, 'COMPETITIVE_BIDDING', 'Post-Qualification', 7, 12, '2026-03-26 05:57:35'),
(25, 'COMPETITIVE_BIDDING', 'Preparation and Approval of Resolution to Award', 8, 11, '2026-03-26 05:57:35'),
(26, 'COMPETITIVE_BIDDING', 'Issuance and Signing of Notice of Award', 9, 1, '2026-03-26 05:57:35'),
(27, 'COMPETITIVE_BIDDING', 'Contract Preparation and Signing of Contract', 10, 11, '2026-03-26 05:57:35'),
(28, 'COMPETITIVE_BIDDING', 'Issuance and Signing of Notice to Proceed', 11, 1, '2026-03-26 05:57:35'),
(29, 'SMALL_VALUE_PROCUREMENT', 'Preparation of Purchase Request', 1, 1, '2026-03-26 05:57:50'),
(30, 'SMALL_VALUE_PROCUREMENT', 'Submission and Receipt of Approved Purchase Request', 2, 1, '2026-03-26 05:57:50'),
(31, 'SMALL_VALUE_PROCUREMENT', 'Preparation of Request for Quotation (RFQ)', 3, 3, '2026-03-26 05:57:50'),
(32, 'SMALL_VALUE_PROCUREMENT', 'Posting of RFQ or Conduct of Canvass', 4, 3, '2026-03-26 05:57:50'),
(33, 'SMALL_VALUE_PROCUREMENT', 'Opening of bids documents / Preparation of Abstract of Quotation', 5, 1, '2026-03-26 05:57:50'),
(34, 'SMALL_VALUE_PROCUREMENT', 'Preparation and Approval of Purchase Order (PO)', 6, 4, '2026-03-26 05:57:50'),
(35, 'SMALL_VALUE_PROCUREMENT', 'Allowance period of the supplier', 7, 10, '2026-03-26 05:57:50'),
(36, 'SMALL_VALUE_PROCUREMENT_200K', 'Preparation of Purchase Request', 1, 1, '2026-03-26 05:57:50'),
(37, 'SMALL_VALUE_PROCUREMENT_200K', 'Submission and Receipt of Approved PR', 2, 1, '2026-03-26 05:57:50'),
(38, 'SMALL_VALUE_PROCUREMENT_200K', 'Preparation of Request for Quotation (RFQ)', 3, 4, '2026-03-26 05:57:50'),
(39, 'SMALL_VALUE_PROCUREMENT_200K', 'Posting of RFQ or Conduct of Canvass', 4, 3, '2026-03-26 05:57:50'),
(40, 'SMALL_VALUE_PROCUREMENT_200K', 'Preparation of Abstract of Quotation / Resolution to Award', 5, 3, '2026-03-26 05:57:50'),
(41, 'SMALL_VALUE_PROCUREMENT_200K', 'Notice of Award', 6, 2, '2026-03-26 05:57:50'),
(42, 'SMALL_VALUE_PROCUREMENT_200K', 'Preparation and Approval of Purchase Order (PO)', 7, 4, '2026-03-26 05:57:50'),
(43, 'SMALL_VALUE_PROCUREMENT_200K', 'Preparation and Signing of Notice to Proceed', 8, 2, '2026-03-26 05:57:50'),
(44, 'SMALL_VALUE_PROCUREMENT_200K', 'Allowance period of the supplier', 9, 10, '2026-03-26 05:57:50'),
(165, 'DIRECT_PROCUREMENT_STI', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(166, 'DIRECT_PROCUREMENT_STI', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(167, 'DIRECT_PROCUREMENT_STI', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(168, 'DIRECT_PROCUREMENT_STI', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(169, 'DIRECT_PROCUREMENT_STI', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(170, 'DIRECT_PROCUREMENT_STI', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(171, 'DIRECT_PROCUREMENT_STI', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(172, 'DIRECT_PROCUREMENT_STI', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(173, 'DIRECT_PROCUREMENT_STI', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(174, 'DIRECT_PROCUREMENT_STI', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(175, 'DIRECT_PROCUREMENT_STI', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(176, 'DIRECT_PROCUREMENT_STI', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(177, 'DIRECT_PROCUREMENT_STI', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(178, 'DIRECT_PROCUREMENT_STI', 'Payment', 15, 1, '2026-03-26 06:13:23'),
(183, 'DIRECT_PROCUREMENT_STI', 'Implementation', 14, 1, '2026-03-26 06:34:07'),
(187, 'PUBLIC_BIDDING', 'Implementation', 14, 1, '2026-03-26 06:34:07'),
(190, 'COMPETITIVE_DIALOGUE', 'Invitation and Pre-qualification', 1, 75, '2026-03-30 10:32:09'),
(191, 'COMPETITIVE_DIALOGUE', 'Dialogue Stage', 2, 15, '2026-03-30 10:32:09'),
(192, 'COMPETITIVE_DIALOGUE', 'Submission of Final Proposals', 3, 20, '2026-03-30 10:32:09'),
(193, 'COMPETITIVE_DIALOGUE', 'Implementation', 4, 1, '2026-03-30 10:32:09'),
(194, 'COMPETITIVE_DIALOGUE', 'Delivery and Inspection', 5, 1, '2026-03-30 10:32:09'),
(195, 'COMPETITIVE_DIALOGUE', 'Payment Processing', 6, 1, '2026-03-30 10:32:09'),
(196, 'UNSOLICITED_OFFER', 'Pre-assessment of Proposal', 1, 20, '2026-03-30 10:32:09'),
(197, 'UNSOLICITED_OFFER', 'Submission of Initial Offer', 2, 30, '2026-03-30 10:32:09'),
(198, 'UNSOLICITED_OFFER', 'Detailed Offer Evaluation', 3, 60, '2026-03-30 10:32:09'),
(199, 'UNSOLICITED_OFFER', 'Negotiation of Terms', 4, 1, '2026-03-30 10:32:09'),
(200, 'UNSOLICITED_OFFER', 'Comparative Bid Matching', 5, 15, '2026-03-30 10:32:09'),
(201, 'UNSOLICITED_OFFER', 'Implementation', 6, 1, '2026-03-30 10:32:09'),
(202, 'UNSOLICITED_OFFER', 'Delivery and Inspection', 7, 1, '2026-03-30 10:32:09'),
(203, 'UNSOLICITED_OFFER', 'Payment Processing', 8, 1, '2026-03-30 10:32:09'),
(204, 'DIRECT_SALES', 'Issuance of Request (DSR)', 1, 180, '2026-03-30 10:32:09'),
(205, 'DIRECT_SALES', 'Supplier Written Acceptance', 2, 5, '2026-03-30 10:32:09'),
(206, 'DIRECT_SALES', 'Implementation', 3, 1, '2026-03-30 10:32:09'),
(207, 'DIRECT_SALES', 'Delivery and Inspection', 4, 1, '2026-03-30 10:32:09'),
(208, 'DIRECT_SALES', 'Payment Processing', 5, 1, '2026-03-30 10:32:09'),
(209, 'LIMITED_SOURCE_BIDDING', 'Direct Invitation to List', 1, 7, '2026-03-30 10:32:09'),
(210, 'LIMITED_SOURCE_BIDDING', 'Bid Evaluation and Award', 2, 23, '2026-03-30 10:32:09'),
(211, 'LIMITED_SOURCE_BIDDING', 'Implementation', 3, 1, '2026-03-30 10:32:09'),
(212, 'LIMITED_SOURCE_BIDDING', 'Delivery and Inspection', 4, 1, '2026-03-30 10:32:09'),
(213, 'LIMITED_SOURCE_BIDDING', 'Payment Processing', 5, 1, '2026-03-30 10:32:09'),
(214, 'DIRECT_CONTRACTING', 'Request for Quotation', 1, 1, '2026-03-30 10:32:09'),
(215, 'DIRECT_CONTRACTING', 'Evaluation and Negotiation', 2, 1, '2026-03-30 10:32:09'),
(216, 'DIRECT_CONTRACTING', 'Implementation', 3, 1, '2026-03-30 10:32:09'),
(217, 'DIRECT_CONTRACTING', 'Delivery and Inspection', 4, 1, '2026-03-30 10:32:09'),
(218, 'DIRECT_CONTRACTING', 'Payment Processing', 5, 1, '2026-03-30 10:32:09'),
(219, 'DIRECT_ACQUISITION', 'Market Identification (<= P200K)', 1, 1, '2026-03-30 10:32:09'),
(220, 'DIRECT_ACQUISITION', 'Direct Purchase and Recording', 2, 1, '2026-03-30 10:32:09'),
(221, 'DIRECT_ACQUISITION', 'Implementation', 3, 1, '2026-03-30 10:32:09'),
(222, 'DIRECT_ACQUISITION', 'Delivery and Inspection', 4, 1, '2026-03-30 10:32:09'),
(223, 'DIRECT_ACQUISITION', 'Payment Processing', 5, 1, '2026-03-30 10:32:09'),
(224, 'REPEAT_ORDER', 'Determination of Need', 1, 180, '2026-03-30 10:32:09'),
(225, 'REPEAT_ORDER', 'BAC Recommendation', 2, 1, '2026-03-30 10:32:09'),
(226, 'REPEAT_ORDER', 'Implementation', 3, 1, '2026-03-30 10:32:09'),
(227, 'REPEAT_ORDER', 'Delivery and Inspection', 4, 1, '2026-03-30 10:32:09'),
(228, 'REPEAT_ORDER', 'Payment Processing', 5, 1, '2026-03-30 10:32:09'),
(229, 'NEGOTIATED_PROCUREMENT', 'Two Failed Biddings / Review', 1, 1, '2026-03-30 10:32:09'),
(230, 'NEGOTIATED_PROCUREMENT', 'Submission of Best Offer', 2, 1, '2026-03-30 10:32:09'),
(231, 'NEGOTIATED_PROCUREMENT', 'Implementation', 3, 1, '2026-03-30 10:32:09'),
(232, 'NEGOTIATED_PROCUREMENT', 'Delivery and Inspection', 4, 1, '2026-03-30 10:32:09'),
(233, 'NEGOTIATED_PROCUREMENT', 'Payment Processing', 5, 1, '2026-03-30 10:32:09'),
(234, 'CONSULTING_SERVICES', 'Shortlisting Phase', 1, 20, '2026-03-30 10:32:09'),
(235, 'CONSULTING_SERVICES', 'Implementation', 2, 1, '2026-03-30 10:32:09'),
(236, 'CONSULTING_SERVICES', 'Delivery and Inspection', 3, 1, '2026-03-30 10:32:09'),
(237, 'CONSULTING_SERVICES', 'Payment Processing', 4, 1, '2026-03-30 10:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('PROJECT_OWNER','PROCUREMENT','BAC_CHAIRMAN','BAC_SECRETARY','SUPERADMIN') NOT NULL DEFAULT 'PROJECT_OWNER',
  `employee_no` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `unit_section` varchar(100) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `status` enum('PENDING','APPROVED') NOT NULL DEFAULT 'APPROVED',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `employee_no`, `position`, `office`, `unit_section`, `avatar_url`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Alex', 'joerenzescallente027@gmail.com', '$2y$10$A5O3U2FSGNg8kr/JaaKp8ueecAH57TrDkQSN2eFX0aJ6jJAHo/QIy', '', '', '', '', '', NULL, 'APPROVED', 1, '2026-02-04 08:47:44', '2026-03-23 07:14:06'),
(3, 'Escall', 'joerenz.dev@gmail.com', '$2y$10$1YlqOPMXSuAZaABGytJvDOPuVza/kPEuLY3nRAGnqGjUpRWdFD/bG', 'SUPERADMIN', NULL, NULL, NULL, NULL, '/SDO-BACtrack/uploads/avatars/Escall_2026-03-05.jpg', 'APPROVED', 1, '2026-02-04 13:55:59', '2026-03-05 07:08:22'),
(4, 'BAC Secretary', 'bacsec@deped.gov.ph', '$2y$10$R1UU9o7/vM2gFhw3BZdgAOkkSGSRq.ohLga9k1Qwot8.9gLLfBoom', 'BAC_SECRETARY', '', 'procurement', 'OSDS', '', '/SDO-BACtrack/uploads/avatars/Redgine_Pinedes_2026-04-03.png', 'APPROVED', 1, '2026-02-05 01:13:21', '2026-04-03 09:57:29'),
(10, 'Seijun', 'seijunqt@gmail.com', '$2y$10$vjgWhkcBz1Od1eCcirbCw.RKqaly2omoPdjGCRrk25hcBudgxzUQ6', 'SUPERADMIN', NULL, NULL, NULL, NULL, '/SDO-BACtrack/uploads/avatars/Seijun_2026-03-10.jpg', 'APPROVED', 1, '2026-03-09 06:05:50', '2026-03-10 02:09:49'),
(16, 'Superadmin', 'superadmin@sdo.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SUPERADMIN', NULL, NULL, NULL, NULL, NULL, 'APPROVED', 1, '2026-03-26 05:57:09', '2026-03-26 05:57:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_documents`
--
ALTER TABLE `activity_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_activity_id` (`activity_id`);

--
-- Indexes for table `activity_history_logs`
--
ALTER TABLE `activity_history_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_activity_id` (`activity_id`),
  ADD KEY `idx_action_type` (`action_type`);

--
-- Indexes for table `bac_cycles`
--
ALTER TABLE `bac_cycles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_project_cycle` (`project_id`,`cycle_number`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_projects_bactrack_id` (`bactrack_id`),
  ADD KEY `idx_procurement_type` (`procurement_type`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `fk_rejected_by` (`rejected_by`),
  ADD KEY `idx_project_owner_name` (`project_owner_name`),
  ADD KEY `idx_approved_budget` (`approved_budget`);

--
-- Indexes for table `project_activities`
--
ALTER TABLE `project_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bac_cycle_id` (`bac_cycle_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_planned_dates` (`planned_start_date`,`planned_end_date`),
  ADD KEY `idx_step_order` (`step_order`);

--
-- Indexes for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_project_id` (`project_id`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `filename` (`filename`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `timeline_adjustment_requests`
--
ALTER TABLE `timeline_adjustment_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `timeline_templates`
--
ALTER TABLE `timeline_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_procurement_type` (`procurement_type`),
  ADD KEY `idx_step_order` (`step_order`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_users_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_documents`
--
ALTER TABLE `activity_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_history_logs`
--
ALTER TABLE `activity_history_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `bac_cycles`
--
ALTER TABLE `bac_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `project_activities`
--
ALTER TABLE `project_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=517;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timeline_adjustment_requests`
--
ALTER TABLE `timeline_adjustment_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `timeline_templates`
--
ALTER TABLE `timeline_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=238;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_documents`
--
ALTER TABLE `activity_documents`
  ADD CONSTRAINT `activity_documents_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `project_activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `activity_history_logs`
--
ALTER TABLE `activity_history_logs`
  ADD CONSTRAINT `activity_history_logs_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `project_activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_history_logs_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `bac_cycles`
--
ALTER TABLE `bac_cycles`
  ADD CONSTRAINT `bac_cycles_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_activities`
--
ALTER TABLE `project_activities`
  ADD CONSTRAINT `project_activities_ibfk_1` FOREIGN KEY (`bac_cycle_id`) REFERENCES `bac_cycles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_activities_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `timeline_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD CONSTRAINT `project_documents_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timeline_adjustment_requests`
--
ALTER TABLE `timeline_adjustment_requests`
  ADD CONSTRAINT `timeline_adjustment_requests_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `project_activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timeline_adjustment_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `timeline_adjustment_requests_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

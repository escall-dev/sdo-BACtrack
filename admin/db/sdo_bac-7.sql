-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 07:16 AM
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
(22, 11, 1, 'ACTIVE', '2026-03-26 06:10:45', '2026-03-26 06:10:45');

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
(28, 1, 'Timeline Adjustment Request', 'Project Owner requested a timeline adjustment for \'Issuance and Availability of Bidding Documents\' in project \'New Computers for ICT Unit\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 13, 0, '2026-03-23 02:32:33');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `procurement_type` varchar(50) NOT NULL DEFAULT 'PUBLIC_BIDDING',
  `project_start_date` date DEFAULT NULL,
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

INSERT INTO `projects` (`id`, `title`, `description`, `procurement_type`, `project_start_date`, `created_by`, `approval_status`, `rejection_remarks`, `rejected_by`, `rejected_at`, `created_at`, `updated_at`) VALUES
(1, 'New Computers for ICT Unit', 'New Computers for ICT Unit', 'PUBLIC_BIDDING', '2026-03-25', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
(2, 'New Printers for Supply', 'new  printers', 'PUBLIC_BIDDING', '2026-03-26', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(3, 'OLAGUER', '', 'PUBLIC_BIDDING', '2026-03-24', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
(4, 'OLAGUER', 'Purified Water', 'PUBLIC_BIDDING', '2026-03-26', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(5, 'New Offices', '', 'SMALL_VALUE_PROCUREMENT_200K', '2026-03-30', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 00:40:48', '2026-03-26 00:40:48'),
(6, 'New Aircon', '', 'SMALL_VALUE_PROCUREMENT', '2026-03-27', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 00:44:55', '2026-03-26 00:44:55'),
(7, 'mcdo', '', 'COMPETITIVE_BIDDING', '2026-03-27', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 05:10:44', '2026-03-26 05:10:44'),
(8, 'cr', 'sdsadsd', 'SMALL_VALUE_PROCUREMENT_200K', '2026-03-26', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(9, 'rc', 'asdsdsda', 'SMALL_VALUE_PROCUREMENT_200K', '2026-03-26', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(10, 'gsdf', 'dsfdfsfds', 'SMALL_VALUE_PROCUREMENT', '2026-03-26', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(11, 'sdasda d', 'ss ffdfsfasf', 'COMPETITIVE_BIDDING', '2026-03-26', 4, 'APPROVED', NULL, NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45');

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
(124, 12, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-26', '2026-04-01', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:22:46', '2026-03-24 03:22:46'),
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
(137, 13, 1, 'Pre-Procurement Conference', 1, '2026-03-26', '2026-03-26', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
(138, 13, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-02', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:15', '2026-03-24 03:33:15'),
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
(152, 14, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-25', '2026-03-31', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:33:47', '2026-03-24 03:33:47'),
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
(165, 15, 1, 'Pre-Procurement Conference', 1, '2026-03-26', '2026-03-26', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
(166, 15, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-02', NULL, 'PENDING', NULL, NULL, '2026-03-24 03:34:38', '2026-03-24 03:34:38'),
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
(179, 19, 36, 'Preparation of Purchase Request', 1, '2026-03-26', '2026-03-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(180, 19, 37, 'Submission and Receipt of Approved PR', 2, '2026-03-27', '2026-03-27', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(181, 19, 38, 'Preparation of Request for Quotation (RFQ)', 3, '2026-03-28', '2026-03-31', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(182, 19, 39, 'Posting of RFQ or Conduct of Canvass', 4, '2026-04-01', '2026-04-03', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(183, 19, 40, 'Preparation of Abstract of Quotation / Resolution to Award', 5, '2026-04-04', '2026-04-06', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(184, 19, 41, 'Notice of Award', 6, '2026-04-07', '2026-04-08', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(185, 19, 42, 'Preparation and Approval of Purchase Order (PO)', 7, '2026-04-09', '2026-04-12', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(186, 19, 43, 'Preparation and Signing of Notice to Proceed', 8, '2026-04-13', '2026-04-14', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(187, 19, 44, 'Allowance period of the supplier', 9, '2026-04-15', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:05:38', '2026-03-26 06:05:38'),
(188, 20, 36, 'Preparation of Purchase Request', 1, '2026-03-26', '2026-03-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(189, 20, 37, 'Submission and Receipt of Approved PR', 2, '2026-03-27', '2026-03-27', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(190, 20, 38, 'Preparation of Request for Quotation (RFQ)', 3, '2026-03-28', '2026-03-31', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(191, 20, 39, 'Posting of RFQ or Conduct of Canvass', 4, '2026-04-01', '2026-04-03', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(192, 20, 40, 'Preparation of Abstract of Quotation / Resolution to Award', 5, '2026-04-04', '2026-04-06', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(193, 20, 41, 'Notice of Award', 6, '2026-04-07', '2026-04-08', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(194, 20, 42, 'Preparation and Approval of Purchase Order (PO)', 7, '2026-04-09', '2026-04-12', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(195, 20, 43, 'Preparation and Signing of Notice to Proceed', 8, '2026-04-13', '2026-04-14', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(196, 20, 44, 'Allowance period of the supplier', 9, '2026-04-15', '2026-04-24', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:06:29', '2026-03-26 06:06:29'),
(197, 21, 29, 'Preparation of Purchase Request', 1, '2026-03-26', '2026-03-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(198, 21, 30, 'Submission and Receipt of Approved Purchase Request', 2, '2026-03-27', '2026-03-27', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(199, 21, 31, 'Preparation of Request for Quotation (RFQ)', 3, '2026-03-28', '2026-03-30', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(200, 21, 32, 'Posting of RFQ or Conduct of Canvass', 4, '2026-03-31', '2026-04-02', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(201, 21, 33, 'Opening of bids documents / Preparation of Abstract of Quotation', 5, '2026-04-03', '2026-04-03', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(202, 21, 34, 'Preparation and Approval of Purchase Order (PO)', 6, '2026-04-04', '2026-04-07', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(203, 21, 35, 'Allowance period of the supplier', 7, '2026-04-08', '2026-04-17', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:03', '2026-03-26 06:10:03'),
(204, 22, 18, 'Preparation of Bidding Documents', 1, '2026-03-26', '2026-03-26', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(205, 22, 19, 'Pre-Procurement Conference', 2, '2026-03-27', '2026-03-27', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(206, 22, 20, 'Advertisement / Posting of Invitation to Bid', 3, '2026-03-28', '2026-04-03', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(207, 22, 21, 'Pre-Bid Conference', 4, '2026-04-04', '2026-04-15', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(208, 22, 22, 'Eligibility Check / Deadline of Submission and Receipt of Bids / Bid Opening', 5, '2026-04-16', '2026-04-16', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(209, 22, 23, 'Bid Evaluation', 6, '2026-04-17', '2026-04-17', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(210, 22, 24, 'Post-Qualification', 7, '2026-04-18', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(211, 22, 25, 'Preparation and Approval of Resolution to Award', 8, '2026-04-30', '2026-05-10', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(212, 22, 26, 'Issuance and Signing of Notice of Award', 9, '2026-05-11', '2026-05-11', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(213, 22, 27, 'Contract Preparation and Signing of Contract', 10, '2026-05-12', '2026-05-22', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45'),
(214, 22, 28, 'Issuance and Signing of Notice to Proceed', 11, '2026-05-23', '2026-05-23', NULL, 'PENDING', NULL, NULL, '2026-03-26 06:10:45', '2026-03-26 06:10:45');

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
(13, '013_enable_all_procurement_type_templates.sql', '2026-03-26 06:13:23');

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
(156, 4, 'aba2afdddfcbffaafa9d01ec1c5dff89af565f480482f016c5ef0f00b5ba0be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 05:40:51', '2026-03-26 22:02:09'),
(157, 4, '6dfd4e6adaa1d95cf2fa5d49fd8aa2afed4ad665778c00eb6d79d5ee5f025044', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 05:45:19', '2026-03-26 22:16:08');

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
(17, 'PUBLIC_BIDDING', 'Payment', 14, 1, '2026-03-23 07:34:28'),
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
(45, 'LIMITED_SOURCE_BIDDING', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(46, 'LIMITED_SOURCE_BIDDING', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(47, 'LIMITED_SOURCE_BIDDING', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(48, 'LIMITED_SOURCE_BIDDING', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(49, 'LIMITED_SOURCE_BIDDING', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(50, 'LIMITED_SOURCE_BIDDING', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(51, 'LIMITED_SOURCE_BIDDING', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(52, 'LIMITED_SOURCE_BIDDING', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(53, 'LIMITED_SOURCE_BIDDING', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(54, 'LIMITED_SOURCE_BIDDING', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(55, 'LIMITED_SOURCE_BIDDING', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(56, 'LIMITED_SOURCE_BIDDING', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(57, 'LIMITED_SOURCE_BIDDING', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(58, 'LIMITED_SOURCE_BIDDING', 'Payment', 14, 1, '2026-03-26 06:13:23'),
(60, 'COMPETITIVE_DIALOGUE', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(61, 'COMPETITIVE_DIALOGUE', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(62, 'COMPETITIVE_DIALOGUE', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(63, 'COMPETITIVE_DIALOGUE', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(64, 'COMPETITIVE_DIALOGUE', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(65, 'COMPETITIVE_DIALOGUE', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(66, 'COMPETITIVE_DIALOGUE', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(67, 'COMPETITIVE_DIALOGUE', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(68, 'COMPETITIVE_DIALOGUE', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(69, 'COMPETITIVE_DIALOGUE', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(70, 'COMPETITIVE_DIALOGUE', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(71, 'COMPETITIVE_DIALOGUE', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(72, 'COMPETITIVE_DIALOGUE', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(73, 'COMPETITIVE_DIALOGUE', 'Payment', 14, 1, '2026-03-26 06:13:23'),
(75, 'UNSOLICITED_OFFER', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(76, 'UNSOLICITED_OFFER', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(77, 'UNSOLICITED_OFFER', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(78, 'UNSOLICITED_OFFER', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(79, 'UNSOLICITED_OFFER', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(80, 'UNSOLICITED_OFFER', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(81, 'UNSOLICITED_OFFER', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(82, 'UNSOLICITED_OFFER', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(83, 'UNSOLICITED_OFFER', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(84, 'UNSOLICITED_OFFER', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(85, 'UNSOLICITED_OFFER', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(86, 'UNSOLICITED_OFFER', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(87, 'UNSOLICITED_OFFER', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(88, 'UNSOLICITED_OFFER', 'Payment', 14, 1, '2026-03-26 06:13:23'),
(90, 'DIRECT_CONTRACTING', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(91, 'DIRECT_CONTRACTING', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(92, 'DIRECT_CONTRACTING', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(93, 'DIRECT_CONTRACTING', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(94, 'DIRECT_CONTRACTING', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(95, 'DIRECT_CONTRACTING', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(96, 'DIRECT_CONTRACTING', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(97, 'DIRECT_CONTRACTING', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(98, 'DIRECT_CONTRACTING', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(99, 'DIRECT_CONTRACTING', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(100, 'DIRECT_CONTRACTING', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(101, 'DIRECT_CONTRACTING', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(102, 'DIRECT_CONTRACTING', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(103, 'DIRECT_CONTRACTING', 'Payment', 14, 1, '2026-03-26 06:13:23'),
(105, 'DIRECT_ACQUISITION', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(106, 'DIRECT_ACQUISITION', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(107, 'DIRECT_ACQUISITION', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(108, 'DIRECT_ACQUISITION', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(109, 'DIRECT_ACQUISITION', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(110, 'DIRECT_ACQUISITION', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(111, 'DIRECT_ACQUISITION', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(112, 'DIRECT_ACQUISITION', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(113, 'DIRECT_ACQUISITION', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(114, 'DIRECT_ACQUISITION', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(115, 'DIRECT_ACQUISITION', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(116, 'DIRECT_ACQUISITION', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(117, 'DIRECT_ACQUISITION', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(118, 'DIRECT_ACQUISITION', 'Payment', 14, 1, '2026-03-26 06:13:23'),
(120, 'REPEAT_ORDER', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(121, 'REPEAT_ORDER', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(122, 'REPEAT_ORDER', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(123, 'REPEAT_ORDER', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(124, 'REPEAT_ORDER', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(125, 'REPEAT_ORDER', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(126, 'REPEAT_ORDER', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(127, 'REPEAT_ORDER', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(128, 'REPEAT_ORDER', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(129, 'REPEAT_ORDER', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(130, 'REPEAT_ORDER', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(131, 'REPEAT_ORDER', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(132, 'REPEAT_ORDER', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(133, 'REPEAT_ORDER', 'Payment', 14, 1, '2026-03-26 06:13:23'),
(135, 'NEGOTIATED_PROCUREMENT', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(136, 'NEGOTIATED_PROCUREMENT', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(137, 'NEGOTIATED_PROCUREMENT', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(138, 'NEGOTIATED_PROCUREMENT', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(139, 'NEGOTIATED_PROCUREMENT', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(140, 'NEGOTIATED_PROCUREMENT', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(141, 'NEGOTIATED_PROCUREMENT', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(142, 'NEGOTIATED_PROCUREMENT', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(143, 'NEGOTIATED_PROCUREMENT', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(144, 'NEGOTIATED_PROCUREMENT', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(145, 'NEGOTIATED_PROCUREMENT', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(146, 'NEGOTIATED_PROCUREMENT', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(147, 'NEGOTIATED_PROCUREMENT', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(148, 'NEGOTIATED_PROCUREMENT', 'Payment', 14, 1, '2026-03-26 06:13:23'),
(150, 'DIRECT_SALES', 'Pre-Procurement Conference', 1, 1, '2026-03-26 06:13:23'),
(151, 'DIRECT_SALES', 'Advertisement and Posting of Invitation to Bid', 2, 7, '2026-03-26 06:13:23'),
(152, 'DIRECT_SALES', 'Issuance and Availability of Bidding Documents', 3, 7, '2026-03-26 06:13:23'),
(153, 'DIRECT_SALES', 'Pre-Bid Conference', 4, 1, '2026-03-26 06:13:23'),
(154, 'DIRECT_SALES', 'Submission and Opening of Bids', 5, 1, '2026-03-26 06:13:23'),
(155, 'DIRECT_SALES', 'Bid Evaluation', 6, 7, '2026-03-26 06:13:23'),
(156, 'DIRECT_SALES', 'Post-Qualification', 7, 7, '2026-03-26 06:13:23'),
(157, 'DIRECT_SALES', 'BAC Resolution Recommending Award', 8, 1, '2026-03-26 06:13:23'),
(158, 'DIRECT_SALES', 'Notice of Award Preparation and Approval', 9, 2, '2026-03-26 06:13:23'),
(159, 'DIRECT_SALES', 'Notice of Award Issuance', 10, 1, '2026-03-26 06:13:23'),
(160, 'DIRECT_SALES', 'Contract Preparation and Signing', 11, 5, '2026-03-26 06:13:23'),
(161, 'DIRECT_SALES', 'Notice to Proceed', 12, 1, '2026-03-26 06:13:23'),
(162, 'DIRECT_SALES', 'Delivery and Inspection', 13, 1, '2026-03-26 06:13:23'),
(163, 'DIRECT_SALES', 'Payment', 14, 1, '2026-03-26 06:13:23'),
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
(178, 'DIRECT_PROCUREMENT_STI', 'Payment', 14, 1, '2026-03-26 06:13:23');

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
(4, 'Redgine Pinedes', 'redginepinedes09@gmail.com', '$2y$10$R1UU9o7/vM2gFhw3BZdgAOkkSGSRq.ohLga9k1Qwot8.9gLLfBoom', '', '', 'procurement', 'OSDS', '', '/SDO-BACtrack/uploads/avatars/Redgine_Pinedes_2026-03-10.jpg', 'APPROVED', 1, '2026-02-05 01:13:21', '2026-03-10 08:15:02'),
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
  ADD KEY `idx_procurement_type` (`procurement_type`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `fk_rejected_by` (`rejected_by`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `bac_cycles`
--
ALTER TABLE `bac_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `project_activities`
--
ALTER TABLE `project_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timeline_adjustment_requests`
--
ALTER TABLE `timeline_adjustment_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `timeline_templates`
--
ALTER TABLE `timeline_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

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

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2026 at 01:52 AM
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
(32, 97, 'STATUS_CHANGE', 'PENDING', 'IN_PROGRESS', 4, '2026-03-16 04:42:19'),
(33, 97, 'STATUS_CHANGE', 'IN_PROGRESS', 'COMPLETED', 4, '2026-03-16 08:17:52'),
(34, 98, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:18:24'),
(35, 99, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:19:01'),
(36, 100, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:20:07'),
(37, 100, 'STATUS_CHANGE', 'COMPLETED', 'COMPLETED', 4, '2026-03-16 08:20:10'),
(38, 101, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:20:42'),
(39, 102, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:21:35'),
(40, 103, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:22:16'),
(41, 104, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:22:32'),
(42, 105, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:22:52'),
(43, 105, 'STATUS_CHANGE', 'COMPLETED', 'COMPLETED', 4, '2026-03-16 08:22:54'),
(44, 106, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:23:11'),
(45, 107, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:23:23'),
(46, 108, 'STATUS_CHANGE', 'PENDING', 'COMPLETED', 4, '2026-03-16 08:23:40');

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
(7, 11, 1, 'ACTIVE', '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(8, 12, 1, 'ACTIVE', '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(9, 13, 1, 'ACTIVE', '2026-03-12 08:40:39', '2026-03-12 08:40:39'),
(10, 14, 1, 'ACTIVE', '2026-03-16 03:11:05', '2026-03-16 03:11:05');

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
(3, 2, 'Project Declined', 'Your project \'coffee stand\' has been declined by BAC. Reason: not recommended', 'PROJECT_REJECTED', 'project', 5, 1, '2026-02-05 03:52:12'),
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
(16, 2, 'Adjustment Request Rejected', 'Your timeline adjustment request for \'Advertisement and Posting of Invitation to Bid\' in project \'CR FOR ALL GENDERS\' has been rejected.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 5, 1, '2026-02-08 02:25:25'),
(17, 3, 'Timeline Adjustment Request', 'redgine pinedes requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'procurement of printed materials for aral program\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 6, 1, '2026-02-10 06:22:17'),
(18, 1, 'Timeline Adjustment Request', 'redgine pinedes requested a timeline adjustment for \'Pre-Procurement Conference\' in project \'procurement of printed materials for aral program\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 6, 0, '2026-02-10 06:22:17'),
(19, 4, 'Adjustment Request Rejected', 'Your timeline adjustment request for \'Pre-Procurement Conference\' in project \'procurement of printed materials for aral program\' has been rejected.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 6, 0, '2026-02-26 07:19:57'),
(20, 1, 'Timeline Adjustment Request', 'Seijun requested a timeline adjustment for \'Post-Qualification\' in project \'CANTEEN\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 7, 0, '2026-03-09 06:12:33'),
(21, 1, 'Timeline Adjustment Request', 'Seijun requested a timeline adjustment for \'Post-Qualification\' in project \'coffee stand\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 8, 0, '2026-03-09 07:03:04'),
(22, 1, 'Timeline Adjustment Request', 'redgine pinedes requested a timeline adjustment for \'Bid Evaluation\' in project \'infra\'.', 'ADJUSTMENT_REQUEST', 'adjustment_request', 9, 0, '2026-03-09 08:15:52');

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
(11, 'Aircon for every unit', 'Every unit must have an aircon...', 'PUBLIC_BIDDING', NULL, 1, 'APPROVED', NULL, NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(12, 'SDO CANTEEN', 'for less hassle', 'PUBLIC_BIDDING', NULL, 1, 'APPROVED', NULL, NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(13, 'SEPARATE CR FOR UNIT HEADS', 'separate cr for unit heads', 'LIMITED_SOURCE_BIDDING', NULL, 1, 'APPROVED', NULL, NULL, NULL, '2026-03-12 08:40:39', '2026-03-12 08:40:39'),
(14, 'New Computers for ICT Unit', 'for better work', 'PUBLIC_BIDDING', '2026-03-26', 2, 'APPROVED', NULL, NULL, NULL, '2026-03-16 03:10:30', '2026-03-16 03:13:02');

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
(73, 7, 1, 'Pre-Procurement Conference', 1, '2026-03-30', '2026-03-30', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(74, 7, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-31', '2026-04-06', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(75, 7, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-07', '2026-04-13', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(76, 7, 4, 'Pre-Bid Conference', 4, '2026-04-14', '2026-04-14', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(77, 7, 5, 'Submission and Opening of Bids', 5, '2026-04-15', '2026-04-15', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(78, 7, 6, 'Bid Evaluation', 6, '2026-04-16', '2026-04-22', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(79, 7, 7, 'Post-Qualification', 7, '2026-04-23', '2026-04-29', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(80, 7, 8, 'BAC Resolution Recommending Award', 8, '2026-04-30', '2026-04-30', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(81, 7, 9, 'Notice of Award Preparation and Approval', 9, '2026-05-01', '2026-05-02', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(82, 7, 10, 'Notice of Award Issuance', 10, '2026-05-03', '2026-05-03', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(83, 7, 11, 'Contract Preparation and Signing', 11, '2026-05-04', '2026-05-08', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(84, 7, 12, 'Notice to Proceed', 12, '2026-05-09', '2026-05-09', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:07:43', '2026-03-12 08:07:43'),
(85, 8, 1, 'Pre-Procurement Conference', 1, '2026-03-14', '2026-03-14', NULL, 'DELAYED', NULL, NULL, '2026-03-12 08:23:04', '2026-03-16 00:58:32'),
(86, 8, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-15', '2026-03-21', NULL, 'DELAYED', NULL, NULL, '2026-03-12 08:23:04', '2026-03-23 00:34:02'),
(87, 8, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-03-22', '2026-03-28', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(88, 8, 4, 'Pre-Bid Conference', 4, '2026-03-29', '2026-03-29', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(89, 8, 5, 'Submission and Opening of Bids', 5, '2026-03-30', '2026-03-30', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(90, 8, 6, 'Bid Evaluation', 6, '2026-03-31', '2026-04-06', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(91, 8, 7, 'Post-Qualification', 7, '2026-04-07', '2026-04-13', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(92, 8, 8, 'BAC Resolution Recommending Award', 8, '2026-04-14', '2026-04-14', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(93, 8, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-15', '2026-04-16', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(94, 8, 10, 'Notice of Award Issuance', 10, '2026-04-17', '2026-04-17', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(95, 8, 11, 'Contract Preparation and Signing', 11, '2026-04-18', '2026-04-22', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(96, 8, 12, 'Notice to Proceed', 12, '2026-04-23', '2026-04-23', NULL, 'PENDING', NULL, NULL, '2026-03-12 08:23:04', '2026-03-12 08:23:04'),
(97, 10, 1, 'Pre-Procurement Conference', 1, '2026-03-26', '2026-03-26', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:17:52'),
(98, 10, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-03-27', '2026-04-02', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:18:24'),
(99, 10, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-04-03', '2026-04-09', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:19:01'),
(100, 10, 4, 'Pre-Bid Conference', 4, '2026-04-10', '2026-04-10', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:20:07'),
(101, 10, 5, 'Submission and Opening of Bids', 5, '2026-04-11', '2026-04-11', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:20:42'),
(102, 10, 6, 'Bid Evaluation', 6, '2026-04-12', '2026-04-18', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:21:35'),
(103, 10, 7, 'Post-Qualification', 7, '2026-04-19', '2026-04-25', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:22:16'),
(104, 10, 8, 'BAC Resolution Recommending Award', 8, '2026-04-26', '2026-04-26', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:22:32'),
(105, 10, 9, 'Notice of Award Preparation and Approval', 9, '2026-04-27', '2026-04-28', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:22:52'),
(106, 10, 10, 'Notice of Award Issuance', 10, '2026-04-29', '2026-04-29', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:23:11'),
(107, 10, 11, 'Contract Preparation and Signing', 11, '2026-04-30', '2026-05-04', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:23:23'),
(108, 10, 12, 'Notice to Proceed', 12, '2026-05-05', '2026-05-05', '2026-03-16', 'COMPLETED', NULL, NULL, '2026-03-16 03:11:05', '2026-03-16 08:23:40');

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

--
-- Dumping data for table `project_documents`
--

INSERT INTO `project_documents` (`id`, `project_id`, `category`, `file_path`, `original_name`, `file_size`, `description`, `uploaded_by`, `uploaded_at`) VALUES
(12, 11, 'Memorandum', 'projects/11/memorandum/69b2744f265e7_1773302863.docx', 'CLICK TIME USER\'S MANUAL.docx', 3857910, NULL, 1, '2026-03-12 08:07:43'),
(13, 11, 'Source of Fund (SAO)', 'projects/11/source-of-fund-sao/69b2744f27650_1773302863.docx', 'CLICK TIME USER\'S MANUAL.docx', 3857910, NULL, 1, '2026-03-12 08:07:43'),
(14, 11, 'Project Proposal', 'projects/11/project-proposal/69b2744f288a9_1773302863.docx', 'CLICK TIME USER\'S MANUAL.docx', 3857910, NULL, 1, '2026-03-12 08:07:43'),
(15, 11, 'Signed RFQ (Request for Quotation)', 'projects/11/signed-rfq-request-for-quotation/69b2744f296bf_1773302863.docx', 'CLICK TIME USER\'S MANUAL.docx', 3857910, NULL, 1, '2026-03-12 08:07:43'),
(16, 12, 'Memorandum', 'projects/12/memorandum/69b277e89d16e_1773303784.docx', 'SDO-WFH.docx', 3875596, NULL, 1, '2026-03-12 08:23:04'),
(17, 12, 'Source of Fund (SAO)', 'projects/12/source-of-fund-sao/69b277e89e6d9_1773303784.docx', 'SDO-WFH.docx', 3875596, NULL, 1, '2026-03-12 08:23:04'),
(18, 12, 'Project Proposal', 'projects/12/project-proposal/69b277e89f5ac_1773303784.docx', 'SDO-WFH.docx', 3875596, NULL, 1, '2026-03-12 08:23:04'),
(19, 12, 'Signed RFQ (Request for Quotation)', 'projects/12/signed-rfq-request-for-quotation/69b277e8a14bf_1773303784.docx', 'SDO-WFH.docx', 3875596, NULL, 1, '2026-03-12 08:23:04'),
(20, 13, 'Memorandum', 'projects/13/memorandum/69b27c072e7fd_1773304839.jpg', 'download.jpg', 58486, NULL, 1, '2026-03-12 08:40:39'),
(21, 13, 'Source of Fund (SAO)', 'projects/13/source-of-fund-sao/69b27c072f68e_1773304839.jpg', 'download.jpg', 58486, NULL, 1, '2026-03-12 08:40:39'),
(22, 13, 'Project Proposal', 'projects/13/project-proposal/69b27c0730703_1773304839.jpg', 'download.jpg', 58486, NULL, 1, '2026-03-12 08:40:39'),
(23, 13, 'Signed RFQ (Request for Quotation)', 'projects/13/signed-rfq-request-for-quotation/69b27c07320a0_1773304839.jpg', 'download.jpg', 58486, NULL, 1, '2026-03-12 08:40:39'),
(24, 14, 'Memorandum', 'projects/14/memorandum/69b774a6da7d7_1773630630.pdf', 'PURPLE WEEK 10 AND 11.pdf', 343360, NULL, 2, '2026-03-16 03:10:30'),
(25, 14, 'Source of Fund (SAO)', 'projects/14/source-of-fund-sao/69b774a6dbcb9_1773630630.pdf', 'PURPLE WEEK 10 AND 11.pdf', 343360, NULL, 2, '2026-03-16 03:10:30'),
(26, 14, 'Project Proposal', 'projects/14/project-proposal/69b774a6dcd42_1773630630.pdf', 'PURPLE WEEK 10 AND 11.pdf', 343360, NULL, 2, '2026-03-16 03:10:30'),
(27, 14, 'Signed RFQ (Request for Quotation)', 'projects/14/signed-rfq-request-for-quotation/69b774a6debf1_1773630630.pdf', 'PURPLE WEEK 10 AND 11.pdf', 343360, NULL, 2, '2026-03-16 03:10:30');

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
(2, 2, '4cd8bbe24393248f365a0413814718da45ac340c5ebf50a27730e3844d930a8e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 01:46:39', '2026-02-05 17:46:39'),
(3, 2, 'a63d476673f9e0a28277138992a4699166e7685506040a229666ea96b9332ec0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 01:48:08', '2026-02-05 17:48:08'),
(4, 3, 'e54d2f69a8bf2c8ed78afc8ef013a85fc2871cf3d81593c1c47ff6cfc0ca81a8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 01:48:12', '2026-02-05 18:05:12'),
(5, 2, '6a9af57a3de88db356e39e51df45508f42acfebeaaa4b1376acb50394c34664c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 01:49:43', '2026-02-05 17:49:43'),
(6, 2, '00c03b936f8eaddbd3f89160723fdc2840fd0b367ea66ed8826849cbea4069ac', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 01:53:54', '2026-02-05 17:53:54'),
(7, 2, '62024fb66580418e8dd5c766ea048f7567fb6c1aba51f3e7d9a100652d9af21e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:00:41', '2026-02-05 18:00:41'),
(8, 2, '8b248e2518613e0136caa7df630d8bbcaa933847ede2fbbaf0cd2f59cafd1717', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:03:48', '2026-02-05 18:07:20'),
(9, 3, '8c59ae2c19bc2a834d2fc5f2d31d013fc3426091d52bf2d85bf1b0d7356207b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:05:17', '2026-02-05 18:05:27'),
(10, 3, 'a99fbeb0484a0b0a3037c09b998159769bfad789a2cd3062c399bb59cde720d9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:05:30', '2026-02-05 18:05:35'),
(11, 3, '91c6e904ceb93727fb3537c3379893d3916c2c50438354ec44ee1de3b65ff146', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:06:01', '2026-02-05 18:08:26'),
(12, 2, 'cd860d4d46578416f917d10eae415d79d883f505b19ff145ffcc6b6b09cd3828', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:07:25', '2026-02-05 18:07:25'),
(13, 2, '0c86eb342c55213fa66f1597b6b9261ce7fa581d1a09eb5e5d7a8bbf910fbe3d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:07:30', '2026-02-05 18:07:30'),
(14, 2, '7b7f31ec5a4e82d91a79aaf8ad8849cc06bd257ee2989b7a380922ec9f4c663e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:07:33', '2026-02-05 18:07:33'),
(15, 2, '6151ff30ac59ca12aa4b8df4c5e81bf4704ccbb4766c0206b98b95daea6ecece', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:07:34', '2026-02-05 18:07:34'),
(16, 2, '10c8019787f87c629b8d919a4d27610c1170ef4e59c7569ef641f7e62042bf9f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:08:24', '2026-02-05 18:09:33'),
(17, 3, '29f1347fa993388d260653c6a24aede0402cf1ad2d14f7355dddf5efe0ffcceb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:08:33', '2026-02-05 18:09:43'),
(20, 2, 'b83a1ee5eb81b821e143619d4cbce329ee2e8ae22c240168845c0c1b884434ec', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:10:06', '2026-02-05 18:11:41'),
(22, 3, '812fd933ea7225a43e8e2090b678eb018a352ee2b1a65b694bcb7b35bb426d20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:17:52', '2026-02-05 18:17:52'),
(24, 2, 'cd317b3f141dccdd9c66496e81ad9b09ed0355d3149e902690e15c29bfd64b78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:18:02', '2026-02-05 18:18:02'),
(25, 2, 'aab19e150a9737b7254da6be5f5887b715c28e9d10df147e8f04fd6c3917dcf2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:18:09', '2026-02-05 18:18:09'),
(26, 2, '940e4c4943dbe20a9375971ec92b4376ec72a16b69aae7bfab6725fe0194869e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:18:12', '2026-02-05 18:18:12'),
(27, 3, '24a92a3d07584f4dc7718af7b536af533067de3ba24bed37b5ee2718c9ca429a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:18:18', '2026-02-05 18:18:20'),
(30, 3, '25354c624ffbebd38724adb214dfe8827a0ef8e760e50a095f2c816d190ebb5f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:19:02', '2026-02-05 18:21:29'),
(31, 2, '3739cd1829c468c56295b97f5eb902b56faafd2baabca27e9f0d44082bdf2ab5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:19:24', '2026-02-05 18:21:27'),
(33, 3, '98344bae26220f78a33b349aaf22799d6648fcd93a869fa19df4e99ddc8d66ec', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:24:46', '2026-02-05 18:25:10'),
(35, 3, '64e6c9e2f5f75a7df5114414501636e39432fb111995d0d7c38b58962a79452c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:44:42', '2026-02-05 19:55:01'),
(36, 4, '7954600449ea97720f6397ffb205be515b780719ac68f63934b80498ef097113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:50:55', '2026-02-05 18:51:22'),
(37, 4, '7c104c35944bddafe3c5248186ba39b3bc356e93af6b85141185781d8e937042', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 02:51:25', '2026-02-05 18:51:25'),
(39, 4, 'bde0d912de52ae3a514b2778f7a7be7f1c5e7e3e825108fc2e2222eefd9cb2ed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:09:25', '2026-02-05 19:31:21'),
(40, 2, 'e0a8a8483b2137a1f5c060d20470cdfb4e8fefcc5ea8619d1795bfc527dabf5d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 03:10:00', '2026-02-06 04:53:25'),
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
(54, 2, '988bdcb7ed6ae6713877f568195f95df728105c3e1af88a6fff3c13da0ddc004', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 05:58:20', '2026-02-10 21:58:43'),
(56, 2, '4e989cd402db795f9aa1578bcd422f10028c523a0d90937e66dce193ee7cbe27', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 05:58:57', '2026-02-10 21:59:05'),
(57, 2, '002fb991c9530c7e03e0450a338a0356016fb6740d7a38bc52955ec6abf38bad', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-10 05:59:15', '2026-02-10 21:59:15'),
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
(79, 2, '951291ec3492bffdd1a24721cb15e693554ef8f2bf4a5da9b18d40b6e0ccabd7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 08:52:20', '2026-02-17 06:00:57'),
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
(137, 10, '3ed143a5630c6816879731e285a1f617692e6c5293ed4a6b8f07a6619b46c69b', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-23 00:33:43', '2026-03-23 16:51:47');

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
(12, 'PUBLIC_BIDDING', 'Notice to Proceed', 12, 1, '2026-02-04 08:47:44');

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
(1, 'Alex', 'procurement@sdo.edu.ph', '$2y$10$UQOWm6eWsYXyJAgwoPOpFO3soPS/iLodVqS0MYUB1V3sC0kx5Z3LG', 'PROCUREMENT', '', '', '', '', NULL, 'APPROVED', 1, '2026-02-04 08:47:44', '2026-03-12 08:03:06'),
(2, 'Project Owner', 'owner@sdo.edu.ph', '$2y$10$mPyD.PBMgEnjmGWjO49KpuB9PuH8L91dcEjonrM1nW9IQ.JGkVgx2', 'PROJECT_OWNER', NULL, '', NULL, NULL, NULL, 'APPROVED', 1, '2026-02-04 08:47:44', '2026-03-16 03:06:59'),
(3, 'Escall', 'joerenz.dev@gmail.com', '$2y$10$1YlqOPMXSuAZaABGytJvDOPuVza/kPEuLY3nRAGnqGjUpRWdFD/bG', 'SUPERADMIN', NULL, NULL, NULL, NULL, '/SDO-BACtrack/uploads/avatars/Escall_2026-03-05.jpg', 'APPROVED', 1, '2026-02-04 13:55:59', '2026-03-05 07:08:22'),
(4, 'Redgine Pinedes', 'redginepinedes09@gmail.com', '$2y$10$R1UU9o7/vM2gFhw3BZdgAOkkSGSRq.ohLga9k1Qwot8.9gLLfBoom', 'BAC_SECRETARY', '', 'procurement', 'OSDS', '', '/SDO-BACtrack/uploads/avatars/Redgine_Pinedes_2026-03-10.jpg', 'APPROVED', 1, '2026-02-05 01:13:21', '2026-03-10 08:15:02'),
(9, 'AJ', 'aj@deped.gov.ph', '$2y$10$9Am9hD/omEPohbxSXu4LiuByVJKxLt6z/cKpM2ZEJzEdviwWmpKX2', 'BAC_CHAIRMAN', NULL, 'Public Schools District Supervisor', 'CID', 'CID-IM', NULL, 'APPROVED', 1, '2026-03-09 05:45:50', '2026-03-09 06:01:50'),
(10, 'Seijun', 'seijunqt@gmail.com', '$2y$10$vjgWhkcBz1Od1eCcirbCw.RKqaly2omoPdjGCRrk25hcBudgxzUQ6', 'SUPERADMIN', NULL, NULL, NULL, NULL, '/SDO-BACtrack/uploads/avatars/Seijun_2026-03-10.jpg', 'APPROVED', 1, '2026-03-09 06:05:50', '2026-03-10 02:09:49');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `bac_cycles`
--
ALTER TABLE `bac_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `project_activities`
--
ALTER TABLE `project_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timeline_adjustment_requests`
--
ALTER TABLE `timeline_adjustment_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `timeline_templates`
--
ALTER TABLE `timeline_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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

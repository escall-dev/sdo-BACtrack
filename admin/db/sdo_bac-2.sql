-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2026 at 08:46 AM
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
(1, 3, 'STATUS_CHANGE', 'PENDING', 'IN_PROGRESS', 1, '2026-02-04 13:46:23'),
(2, 3, 'STATUS_CHANGE', 'IN_PROGRESS', 'COMPLETED', 1, '2026-02-04 13:46:45'),
(3, 3, 'COMPLIANCE_TAG', NULL, '{\"status\":\"COMPLIANT\",\"remarks\":\"\"}', 1, '2026-02-04 13:47:15'),
(4, 1, 'COMPLIANCE_TAG', NULL, '{\"status\":\"NON_COMPLIANT\",\"remarks\":\"ayusin naman\"}', 1, '2026-02-04 13:48:32'),
(5, 1, 'STATUS_CHANGE', 'PENDING', 'PENDING', 1, '2026-02-04 13:48:34'),
(6, 1, 'STATUS_CHANGE', 'PENDING', 'IN_PROGRESS', 1, '2026-02-04 13:48:40'),
(7, 2, 'COMPLIANCE_TAG', NULL, '{\"status\":\"NON_COMPLIANT\",\"remarks\":\"okay gege\"}', 1, '2026-02-04 13:49:21'),
(8, 25, 'COMPLIANCE_TAG', NULL, '{\"status\":\"COMPLIANT\",\"remarks\":\"\"}', 3, '2026-02-05 02:05:27'),
(9, 25, 'COMPLIANCE_TAG', 'COMPLIANT', '{\"status\":\"COMPLIANT\",\"remarks\":\"\"}', 3, '2026-02-05 02:05:36'),
(10, 1, 'COMPLIANCE_TAG', 'NON_COMPLIANT', '{\"status\":\"NON_COMPLIANT\",\"remarks\":\"ayusin mo idol\"}', 3, '2026-02-05 02:08:48'),
(11, 25, 'COMPLIANCE_TAG', 'COMPLIANT', '{\"status\":\"NON_COMPLIANT\",\"remarks\":\"paayos\"}', 3, '2026-02-05 02:09:08'),
(12, 25, 'DATE_CHANGE', '{\"start\":\"2026-02-06\",\"end\":\"2026-02-06\"}', '{\"start\":\"2026-02-06\",\"end\":\"2026-02-06\"}', 3, '2026-02-08 01:55:17'),
(13, 13, 'DATE_CHANGE', '{\"start\":\"2026-02-05\",\"end\":\"2026-02-05\"}', '{\"start\":\"2026-02-05\",\"end\":\"2026-02-05\"}', 3, '2026-02-08 02:16:46'),
(14, 38, 'DATE_CHANGE', '{\"start\":\"2026-02-10\",\"end\":\"2026-02-16\"}', '{\"start\":\"2026-02-10\",\"end\":\"2026-02-16\"}', 3, '2026-02-08 02:17:35'),
(15, 25, 'DATE_CHANGE', '{\"start\":\"2026-02-06\",\"end\":\"2026-02-06\"}', '{\"start\":\"2026-02-06\",\"end\":\"2026-02-06\"}', 3, '2026-02-08 02:19:21');

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
(1, 1, 1, 'ACTIVE', '2026-02-04 08:49:01', '2026-02-04 08:49:01'),
(2, 2, 1, 'ACTIVE', '2026-02-05 00:33:32', '2026-02-05 00:33:32'),
(3, 3, 1, 'ACTIVE', '2026-02-05 02:03:08', '2026-02-05 02:03:08'),
(4, 4, 1, 'ACTIVE', '2026-02-05 02:51:22', '2026-02-05 02:51:22'),
(5, 5, 1, 'ACTIVE', '2026-02-05 03:50:44', '2026-02-05 03:50:44'),
(6, 6, 1, 'ACTIVE', '2026-02-10 06:06:38', '2026-02-10 06:06:38');

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
(19, 4, 'Adjustment Request Rejected', 'Your timeline adjustment request for \'Pre-Procurement Conference\' in project \'procurement of printed materials for aral program\' has been rejected.', 'ADJUSTMENT_RESPONSE', 'adjustment_request', 6, 0, '2026-02-26 07:19:57');

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
(1, 'infra', 'infra', 'PUBLIC_BIDDING', NULL, 1, 'APPROVED', NULL, NULL, NULL, '2026-02-04 08:49:01', '2026-02-04 08:49:01'),
(2, 'SDO ict materials', 'for schools implementations', 'PUBLIC_BIDDING', NULL, 3, 'APPROVED', NULL, NULL, NULL, '2026-02-05 00:33:32', '2026-02-05 00:33:32'),
(3, 'CR FOR ALL GENDERS', 'ADDITIONAL COMFORT ROOM', 'PUBLIC_BIDDING', NULL, 2, 'APPROVED', NULL, NULL, NULL, '2026-02-05 02:03:08', '2026-02-05 02:03:08'),
(4, 'CANTEEN', 'FOOD PRODUCTION', 'PUBLIC_BIDDING', NULL, 4, 'PENDING_APPROVAL', NULL, NULL, NULL, '2026-02-05 02:51:22', '2026-02-05 02:51:22'),
(5, 'coffee stand', 'coffeholic', 'PUBLIC_BIDDING', NULL, 2, 'REJECTED', 'not recommended', 3, '2026-02-05 03:52:12', '2026-02-05 03:50:44', '2026-02-05 03:52:12'),
(6, 'procurement of printed materials for aral program', 'procurement of printed materials for aral program', 'PUBLIC_BIDDING', '2026-02-10', 4, 'APPROVED', NULL, NULL, NULL, '2026-02-10 06:04:22', '2026-02-10 06:07:16'),
(7, 'INFRA', 'INFRA', 'PUBLIC_BIDDING', '2026-02-18', 2, 'DRAFT', NULL, NULL, NULL, '2026-02-16 08:52:53', '2026-02-16 08:52:53');

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
(1, 1, 1, 'Pre-Procurement Conference', 1, '2026-02-04', '2026-02-04', NULL, 'DELAYED', 'NON_COMPLIANT', 'ayusin mo idol', '2026-02-04 08:49:01', '2026-02-05 02:08:48'),
(2, 1, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-02-05', '2026-02-11', NULL, 'DELAYED', 'NON_COMPLIANT', 'okay gege', '2026-02-04 08:49:01', '2026-02-16 03:20:03'),
(3, 1, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-02-12', '2026-02-18', '2026-02-04', 'COMPLETED', 'COMPLIANT', '', '2026-02-04 08:49:01', '2026-02-04 13:47:15'),
(4, 1, 4, 'Pre-Bid Conference', 4, '2026-02-19', '2026-02-19', NULL, 'DELAYED', NULL, NULL, '2026-02-04 08:49:01', '2026-02-26 07:17:11'),
(5, 1, 5, 'Submission and Opening of Bids', 5, '2026-02-20', '2026-02-20', NULL, 'DELAYED', NULL, NULL, '2026-02-04 08:49:01', '2026-02-26 07:17:11'),
(6, 1, 6, 'Bid Evaluation', 6, '2026-02-21', '2026-02-27', NULL, 'DELAYED', NULL, NULL, '2026-02-04 08:49:01', '2026-03-05 05:40:51'),
(7, 1, 7, 'Post-Qualification', 7, '2026-02-28', '2026-03-06', NULL, 'PENDING', NULL, NULL, '2026-02-04 08:49:01', '2026-02-04 08:49:01'),
(8, 1, 8, 'BAC Resolution Recommending Award', 8, '2026-03-07', '2026-03-07', NULL, 'PENDING', NULL, NULL, '2026-02-04 08:49:01', '2026-02-04 08:49:01'),
(9, 1, 9, 'Notice of Award Preparation and Approval', 9, '2026-03-08', '2026-03-09', NULL, 'PENDING', NULL, NULL, '2026-02-04 08:49:01', '2026-02-04 08:49:01'),
(10, 1, 10, 'Notice of Award Issuance', 10, '2026-03-10', '2026-03-10', NULL, 'PENDING', NULL, NULL, '2026-02-04 08:49:01', '2026-02-04 08:49:01'),
(11, 1, 11, 'Contract Preparation and Signing', 11, '2026-03-11', '2026-03-15', NULL, 'PENDING', NULL, NULL, '2026-02-04 08:49:01', '2026-02-04 08:49:01'),
(12, 1, 12, 'Notice to Proceed', 12, '2026-03-16', '2026-03-16', NULL, 'PENDING', NULL, NULL, '2026-02-04 08:49:01', '2026-02-04 08:49:01'),
(13, 2, 1, 'Pre-Procurement Conference', 1, '2026-02-05', '2026-02-05', NULL, 'DELAYED', NULL, NULL, '2026-02-05 00:33:32', '2026-02-08 01:54:53'),
(14, 2, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-02-06', '2026-02-12', NULL, 'DELAYED', NULL, NULL, '2026-02-05 00:33:32', '2026-02-16 03:20:03'),
(15, 2, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-02-13', '2026-02-19', NULL, 'DELAYED', NULL, NULL, '2026-02-05 00:33:32', '2026-02-26 07:17:11'),
(16, 2, 4, 'Pre-Bid Conference', 4, '2026-02-20', '2026-02-20', NULL, 'DELAYED', NULL, NULL, '2026-02-05 00:33:32', '2026-02-26 07:17:11'),
(17, 2, 5, 'Submission and Opening of Bids', 5, '2026-02-21', '2026-02-21', NULL, 'DELAYED', NULL, NULL, '2026-02-05 00:33:32', '2026-02-26 07:17:11'),
(18, 2, 6, 'Bid Evaluation', 6, '2026-02-22', '2026-02-28', NULL, 'DELAYED', NULL, NULL, '2026-02-05 00:33:32', '2026-03-05 05:40:51'),
(19, 2, 7, 'Post-Qualification', 7, '2026-03-01', '2026-03-07', NULL, 'PENDING', NULL, NULL, '2026-02-05 00:33:32', '2026-02-05 00:33:32'),
(20, 2, 8, 'BAC Resolution Recommending Award', 8, '2026-03-08', '2026-03-08', NULL, 'PENDING', NULL, NULL, '2026-02-05 00:33:32', '2026-02-05 00:33:32'),
(21, 2, 9, 'Notice of Award Preparation and Approval', 9, '2026-03-09', '2026-03-10', NULL, 'PENDING', NULL, NULL, '2026-02-05 00:33:32', '2026-02-05 00:33:32'),
(22, 2, 10, 'Notice of Award Issuance', 10, '2026-03-11', '2026-03-11', NULL, 'PENDING', NULL, NULL, '2026-02-05 00:33:32', '2026-02-05 00:33:32'),
(23, 2, 11, 'Contract Preparation and Signing', 11, '2026-03-12', '2026-03-16', NULL, 'PENDING', NULL, NULL, '2026-02-05 00:33:32', '2026-02-05 00:33:32'),
(24, 2, 12, 'Notice to Proceed', 12, '2026-03-17', '2026-03-17', NULL, 'PENDING', NULL, NULL, '2026-02-05 00:33:32', '2026-02-05 00:33:32'),
(25, 3, 1, 'Pre-Procurement Conference', 1, '2026-02-06', '2026-02-06', NULL, 'DELAYED', 'NON_COMPLIANT', 'paayos', '2026-02-05 02:03:08', '2026-02-08 01:54:53'),
(26, 3, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-02-07', '2026-02-13', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:03:08', '2026-02-16 03:20:03'),
(27, 3, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-02-14', '2026-02-20', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:03:08', '2026-02-26 07:17:11'),
(28, 3, 4, 'Pre-Bid Conference', 4, '2026-02-21', '2026-02-21', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:03:08', '2026-02-26 07:17:11'),
(29, 3, 5, 'Submission and Opening of Bids', 5, '2026-02-22', '2026-02-22', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:03:08', '2026-02-26 07:17:11'),
(30, 3, 6, 'Bid Evaluation', 6, '2026-02-23', '2026-03-01', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:03:08', '2026-03-05 05:40:51'),
(31, 3, 7, 'Post-Qualification', 7, '2026-03-02', '2026-03-08', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:03:08', '2026-02-05 02:03:08'),
(32, 3, 8, 'BAC Resolution Recommending Award', 8, '2026-03-09', '2026-03-09', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:03:08', '2026-02-05 02:03:08'),
(33, 3, 9, 'Notice of Award Preparation and Approval', 9, '2026-03-10', '2026-03-11', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:03:08', '2026-02-05 02:03:08'),
(34, 3, 10, 'Notice of Award Issuance', 10, '2026-03-12', '2026-03-12', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:03:08', '2026-02-05 02:03:08'),
(35, 3, 11, 'Contract Preparation and Signing', 11, '2026-03-13', '2026-03-17', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:03:08', '2026-02-05 02:03:08'),
(36, 3, 12, 'Notice to Proceed', 12, '2026-03-18', '2026-03-18', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:03:08', '2026-02-05 02:03:08'),
(37, 4, 1, 'Pre-Procurement Conference', 1, '2026-02-09', '2026-02-09', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:51:22', '2026-02-10 04:43:32'),
(38, 4, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-02-10', '2026-02-16', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:51:22', '2026-02-26 07:17:11'),
(39, 4, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-02-17', '2026-02-23', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:51:22', '2026-02-26 07:17:11'),
(40, 4, 4, 'Pre-Bid Conference', 4, '2026-02-24', '2026-02-24', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:51:22', '2026-02-26 07:17:11'),
(41, 4, 5, 'Submission and Opening of Bids', 5, '2026-02-25', '2026-02-25', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:51:22', '2026-02-26 07:17:11'),
(42, 4, 6, 'Bid Evaluation', 6, '2026-02-26', '2026-03-04', NULL, 'DELAYED', NULL, NULL, '2026-02-05 02:51:22', '2026-03-05 05:40:51'),
(43, 4, 7, 'Post-Qualification', 7, '2026-03-05', '2026-03-11', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:51:22', '2026-02-05 02:51:22'),
(44, 4, 8, 'BAC Resolution Recommending Award', 8, '2026-03-12', '2026-03-12', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:51:22', '2026-02-05 02:51:22'),
(45, 4, 9, 'Notice of Award Preparation and Approval', 9, '2026-03-13', '2026-03-14', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:51:22', '2026-02-05 02:51:22'),
(46, 4, 10, 'Notice of Award Issuance', 10, '2026-03-15', '2026-03-15', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:51:22', '2026-02-05 02:51:22'),
(47, 4, 11, 'Contract Preparation and Signing', 11, '2026-03-16', '2026-03-20', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:51:22', '2026-02-05 02:51:22'),
(48, 4, 12, 'Notice to Proceed', 12, '2026-03-21', '2026-03-21', NULL, 'PENDING', NULL, NULL, '2026-02-05 02:51:22', '2026-02-05 02:51:22'),
(49, 5, 1, 'Pre-Procurement Conference', 1, '2026-02-09', '2026-02-09', NULL, 'DELAYED', NULL, NULL, '2026-02-05 03:50:44', '2026-02-10 04:43:32'),
(50, 5, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-02-10', '2026-02-16', NULL, 'DELAYED', NULL, NULL, '2026-02-05 03:50:44', '2026-02-26 07:17:11'),
(51, 5, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-02-17', '2026-02-23', NULL, 'DELAYED', NULL, NULL, '2026-02-05 03:50:44', '2026-02-26 07:17:11'),
(52, 5, 4, 'Pre-Bid Conference', 4, '2026-02-24', '2026-02-24', NULL, 'DELAYED', NULL, NULL, '2026-02-05 03:50:44', '2026-02-26 07:17:11'),
(53, 5, 5, 'Submission and Opening of Bids', 5, '2026-02-25', '2026-02-25', NULL, 'DELAYED', NULL, NULL, '2026-02-05 03:50:44', '2026-02-26 07:17:11'),
(54, 5, 6, 'Bid Evaluation', 6, '2026-02-26', '2026-03-04', NULL, 'DELAYED', NULL, NULL, '2026-02-05 03:50:44', '2026-03-05 05:40:51'),
(55, 5, 7, 'Post-Qualification', 7, '2026-03-05', '2026-03-11', NULL, 'PENDING', NULL, NULL, '2026-02-05 03:50:44', '2026-02-05 03:50:44'),
(56, 5, 8, 'BAC Resolution Recommending Award', 8, '2026-03-12', '2026-03-12', NULL, 'PENDING', NULL, NULL, '2026-02-05 03:50:44', '2026-02-05 03:50:44'),
(57, 5, 9, 'Notice of Award Preparation and Approval', 9, '2026-03-13', '2026-03-14', NULL, 'PENDING', NULL, NULL, '2026-02-05 03:50:44', '2026-02-05 03:50:44'),
(58, 5, 10, 'Notice of Award Issuance', 10, '2026-03-15', '2026-03-15', NULL, 'PENDING', NULL, NULL, '2026-02-05 03:50:44', '2026-02-05 03:50:44'),
(59, 5, 11, 'Contract Preparation and Signing', 11, '2026-03-16', '2026-03-20', NULL, 'PENDING', NULL, NULL, '2026-02-05 03:50:44', '2026-02-05 03:50:44'),
(60, 5, 12, 'Notice to Proceed', 12, '2026-03-21', '2026-03-21', NULL, 'PENDING', NULL, NULL, '2026-02-05 03:50:44', '2026-02-05 03:50:44'),
(61, 6, 1, 'Pre-Procurement Conference', 1, '2026-02-10', '2026-02-10', NULL, 'DELAYED', NULL, NULL, '2026-02-10 06:06:38', '2026-02-11 05:31:52'),
(62, 6, 2, 'Advertisement and Posting of Invitation to Bid', 2, '2026-02-11', '2026-02-17', NULL, 'DELAYED', NULL, NULL, '2026-02-10 06:06:38', '2026-02-26 07:17:11'),
(63, 6, 3, 'Issuance and Availability of Bidding Documents', 3, '2026-02-18', '2026-02-24', NULL, 'DELAYED', NULL, NULL, '2026-02-10 06:06:38', '2026-02-26 07:17:11'),
(64, 6, 4, 'Pre-Bid Conference', 4, '2026-02-25', '2026-02-25', NULL, 'DELAYED', NULL, NULL, '2026-02-10 06:06:38', '2026-02-26 07:17:11'),
(65, 6, 5, 'Submission and Opening of Bids', 5, '2026-02-26', '2026-02-26', NULL, 'DELAYED', NULL, NULL, '2026-02-10 06:06:38', '2026-03-05 05:40:51'),
(66, 6, 6, 'Bid Evaluation', 6, '2026-02-27', '2026-03-05', NULL, 'PENDING', NULL, NULL, '2026-02-10 06:06:38', '2026-02-10 06:06:38'),
(67, 6, 7, 'Post-Qualification', 7, '2026-03-06', '2026-03-12', NULL, 'PENDING', NULL, NULL, '2026-02-10 06:06:38', '2026-02-10 06:06:38'),
(68, 6, 8, 'BAC Resolution Recommending Award', 8, '2026-03-13', '2026-03-13', NULL, 'PENDING', NULL, NULL, '2026-02-10 06:06:38', '2026-02-10 06:06:38'),
(69, 6, 9, 'Notice of Award Preparation and Approval', 9, '2026-03-14', '2026-03-15', NULL, 'PENDING', NULL, NULL, '2026-02-10 06:06:38', '2026-02-10 06:06:38'),
(70, 6, 10, 'Notice of Award Issuance', 10, '2026-03-16', '2026-03-16', NULL, 'PENDING', NULL, NULL, '2026-02-10 06:06:38', '2026-02-10 06:06:38'),
(71, 6, 11, 'Contract Preparation and Signing', 11, '2026-03-17', '2026-03-21', NULL, 'PENDING', NULL, NULL, '2026-02-10 06:06:38', '2026-02-10 06:06:38'),
(72, 6, 12, 'Notice to Proceed', 12, '2026-03-22', '2026-03-22', NULL, 'PENDING', NULL, NULL, '2026-02-10 06:06:38', '2026-02-10 06:06:38');

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
(1, 5, 'Pre-Procurement Conference', 'projects/5/pre-procurement-conference/69842e6791be8_1770270311.docx', 'SIA - WEEK 6 Escallente.docx', 17128, 'ito na po', 2, '2026-02-05 05:45:11'),
(2, 3, 'Advertisement and Posting of Invitation to Bid', 'projects/3/advertisement-and-posting-of-invitation-to-bid/69842e794854c_1770270329.docx', 'SIA WEEK 6.docx', 17033, NULL, 2, '2026-02-05 05:45:29');

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
(92, 4, 'c1a82826b682c5558cbfb4e5c5256bc848dea0b50f8dd0d7010842cad8c366dd', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-05 07:35:43', '2026-03-05 23:41:24');

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
(1, 25, 3, 'ayusin sa susunod', '2026-02-06', '2026-02-06', 'APPROVED', 3, '', '2026-02-05 02:05:12', '2026-02-08 01:55:16'),
(2, 13, 3, 'ayos', '2026-02-05', '2026-02-05', 'APPROVED', 3, 'tama, ayusin muna ha?', '2026-02-08 02:14:18', '2026-02-08 02:16:46'),
(3, 38, 3, 'wait', '2026-02-10', '2026-02-16', 'APPROVED', 3, '', '2026-02-08 02:17:08', '2026-02-08 02:17:35'),
(4, 25, 3, 'wait po ulit hehe', '2026-02-06', '2026-02-06', 'APPROVED', 3, '', '2026-02-08 02:19:18', '2026-02-08 02:19:21'),
(5, 26, 2, 'may aayusin lang saglit sa region', '2026-02-07', '2026-02-13', 'REJECTED', 3, 'hindi p\'wede, hindi ka special', '2026-02-08 02:24:41', '2026-02-08 02:25:25'),
(6, 61, 4, 'po', '2026-02-10', '2026-02-11', 'REJECTED', 3, '', '2026-02-10 06:22:17', '2026-02-26 07:19:57');

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
  `role` enum('PROJECT_OWNER','PROCUREMENT','SUPERADMIN') NOT NULL DEFAULT 'PROJECT_OWNER',
  `employee_no` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `unit_section` varchar(100) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `status` enum('PENDING','APPROVED') NOT NULL DEFAULT 'APPROVED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `employee_no`, `position`, `office`, `unit_section`, `avatar_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'procurement@sdo.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROCUREMENT', NULL, NULL, NULL, NULL, NULL, 'APPROVED', '2026-02-04 08:47:44', '2026-02-04 13:55:46'),
(2, 'Project Owner', 'owner@sdo.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROJECT_OWNER', NULL, NULL, NULL, NULL, NULL, 'APPROVED', '2026-02-04 08:47:44', '2026-02-04 08:47:44'),
(3, 'Escall', 'joerenz.dev@gmail.com', '$2y$10$1YlqOPMXSuAZaABGytJvDOPuVza/kPEuLY3nRAGnqGjUpRWdFD/bG', 'SUPERADMIN', NULL, NULL, NULL, NULL, '/SDO-BACtrack/uploads/avatars/Escall_2026-03-05.jpg', 'APPROVED', '2026-02-04 13:55:59', '2026-03-05 07:08:22'),
(4, 'redgine pinedes', 'redginepinedes09@gmail.com', '$2y$10$R1UU9o7/vM2gFhw3BZdgAOkkSGSRq.ohLga9k1Qwot8.9gLLfBoom', 'PROJECT_OWNER', NULL, 'procurement', 'OSDS', 'OSDS-Procurement', '/SDO-BACtrack/uploads/avatars/redgine_pinedes_2026-03-05.jpg', 'APPROVED', '2026-02-05 01:13:21', '2026-03-05 07:41:20');

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
  ADD KEY `idx_role` (`role`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `bac_cycles`
--
ALTER TABLE `bac_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_activities`
--
ALTER TABLE `project_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `timeline_adjustment_requests`
--
ALTER TABLE `timeline_adjustment_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `timeline_templates`
--
ALTER TABLE `timeline_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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

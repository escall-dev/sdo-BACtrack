-- Add DRAFT status for pre-submission review
-- Project owners create drafts; BAC can review before project owner submits.
-- Only after submit does the project get timeline/activities.
USE sdo_bac;

ALTER TABLE projects MODIFY COLUMN approval_status ENUM('DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'APPROVED';
ALTER TABLE projects ADD COLUMN project_start_date DATE NULL AFTER procurement_type;

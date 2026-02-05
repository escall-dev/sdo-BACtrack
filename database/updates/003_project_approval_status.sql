-- Add project approval workflow (run once)
-- Projects created by Project Owners require BAC approval before progress can be made.
-- Existing projects default to APPROVED for backward compatibility.
USE sdo_bac;

ALTER TABLE projects ADD COLUMN approval_status ENUM('PENDING_APPROVAL', 'APPROVED') NOT NULL DEFAULT 'APPROVED' AFTER created_by;
ALTER TABLE projects ADD INDEX idx_approval_status (approval_status);

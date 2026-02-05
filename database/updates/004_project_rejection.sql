-- Add project rejection workflow (Accept/Decline with remarks)
-- BAC members can decline projects with required reason; project owner sees the remarks.
USE sdo_bac;

-- Add REJECTED status and rejection fields to projects
ALTER TABLE projects MODIFY COLUMN approval_status ENUM('PENDING_APPROVAL', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'APPROVED';
ALTER TABLE projects ADD COLUMN rejection_remarks TEXT NULL AFTER approval_status;
ALTER TABLE projects ADD COLUMN rejected_by INT NULL AFTER rejection_remarks;
ALTER TABLE projects ADD COLUMN rejected_at TIMESTAMP NULL AFTER rejected_by;
ALTER TABLE projects ADD CONSTRAINT fk_rejected_by FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add PROJECT_REJECTED notification type
ALTER TABLE notifications MODIFY COLUMN type ENUM('DEADLINE_WARNING', 'ACTIVITY_DELAYED', 'DOCUMENT_UPLOADED', 'ADJUSTMENT_REQUEST', 'ADJUSTMENT_RESPONSE', 'PROJECT_REJECTED') NOT NULL;

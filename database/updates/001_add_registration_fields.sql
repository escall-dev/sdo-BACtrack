-- Add registration fields to users table (run once if you already have the database)
-- Skip any statement that fails with "Duplicate column" (columns already exist).
USE sdo_bac;

ALTER TABLE users ADD COLUMN employee_no VARCHAR(50) NULL AFTER role;
ALTER TABLE users ADD COLUMN position VARCHAR(100) NULL AFTER employee_no;
ALTER TABLE users ADD COLUMN office VARCHAR(100) NULL AFTER position;
ALTER TABLE users ADD COLUMN unit_section VARCHAR(100) NULL AFTER office;
ALTER TABLE users ADD COLUMN status ENUM('PENDING', 'APPROVED') NOT NULL DEFAULT 'APPROVED' AFTER unit_section;

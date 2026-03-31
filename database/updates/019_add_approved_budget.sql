-- Add Approved Budget for Contract column to projects table

START TRANSACTION;

ALTER TABLE projects 
ADD COLUMN approved_budget DECIMAL(15, 2) NULL AFTER project_start_date;

CREATE INDEX idx_approved_budget ON projects (approved_budget);

COMMIT;

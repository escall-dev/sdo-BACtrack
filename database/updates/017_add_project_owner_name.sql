-- Add optional custom project owner/company name field for BAC Secretary project creation
ALTER TABLE projects
ADD COLUMN project_owner_name VARCHAR(255) NULL AFTER project_start_date;

CREATE INDEX idx_project_owner_name ON projects (project_owner_name);

-- Add active/inactive flag for user deactivation
ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER status;
CREATE INDEX idx_users_is_active ON users (is_active);

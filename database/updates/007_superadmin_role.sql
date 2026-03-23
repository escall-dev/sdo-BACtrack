-- Add SUPERADMIN role to users table
ALTER TABLE users MODIFY COLUMN role ENUM('PROJECT_OWNER', 'PROCUREMENT', 'SUPERADMIN') NOT NULL DEFAULT 'PROJECT_OWNER';

-- Create default superadmin account (password: admin123)
INSERT INTO users (name, email, password_hash, role, status) VALUES
('Superadmin', 'superadmin@sdo.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SUPERADMIN', 'APPROVED')
ON DUPLICATE KEY UPDATE role = 'SUPERADMIN';

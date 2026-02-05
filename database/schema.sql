-- SDO-BACtrack Database Schema
-- BAC Procedural Timeline Tracking System

CREATE DATABASE IF NOT EXISTS sdo_bac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sdo_bac;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('PROJECT_OWNER', 'PROCUREMENT') NOT NULL DEFAULT 'PROJECT_OWNER',
    employee_no VARCHAR(50) NULL,
    position VARCHAR(100) NULL,
    office VARCHAR(100) NULL,
    unit_section VARCHAR(100) NULL,
    status ENUM('PENDING', 'APPROVED') NOT NULL DEFAULT 'APPROVED',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    procurement_type VARCHAR(50) NOT NULL DEFAULT 'PUBLIC_BIDDING',
    created_by INT NOT NULL,
    approval_status ENUM('PENDING_APPROVAL', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'APPROVED',
    rejection_remarks TEXT NULL,
    rejected_by INT NULL,
    rejected_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_procurement_type (procurement_type),
    INDEX idx_created_by (created_by),
    INDEX idx_approval_status (approval_status)
) ENGINE=InnoDB;

-- BAC Cycles table
CREATE TABLE IF NOT EXISTS bac_cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    cycle_number INT NOT NULL DEFAULT 1,
    status ENUM('ACTIVE', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_cycle (project_id, cycle_number),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Timeline Templates table
CREATE TABLE IF NOT EXISTS timeline_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_type VARCHAR(50) NOT NULL,
    step_name VARCHAR(255) NOT NULL,
    step_order INT NOT NULL,
    default_duration_days INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_procurement_type (procurement_type),
    INDEX idx_step_order (step_order)
) ENGINE=InnoDB;

-- Project Activities table
CREATE TABLE IF NOT EXISTS project_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bac_cycle_id INT NOT NULL,
    template_id INT,
    step_name VARCHAR(255) NOT NULL,
    step_order INT NOT NULL,
    planned_start_date DATE NOT NULL,
    planned_end_date DATE NOT NULL,
    actual_completion_date DATE NULL,
    status ENUM('PENDING', 'IN_PROGRESS', 'COMPLETED', 'DELAYED') NOT NULL DEFAULT 'PENDING',
    compliance_status ENUM('COMPLIANT', 'NON_COMPLIANT') NULL,
    compliance_remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bac_cycle_id) REFERENCES bac_cycles(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES timeline_templates(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_planned_dates (planned_start_date, planned_end_date),
    INDEX idx_step_order (step_order)
) ENGINE=InnoDB;

-- Project Owner Documents table
CREATE TABLE IF NOT EXISTS project_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    category VARCHAR(255) NOT NULL DEFAULT 'other',
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL DEFAULT 0,
    description TEXT NULL,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_project_id (project_id),
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- Activity Documents table
CREATE TABLE IF NOT EXISTS activity_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL DEFAULT 0,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES project_activities(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_activity_id (activity_id)
) ENGINE=InnoDB;

-- Activity History Logs table
CREATE TABLE IF NOT EXISTS activity_history_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    action_type ENUM('DATE_CHANGE', 'STATUS_CHANGE', 'COMPLIANCE_TAG') NOT NULL,
    old_value TEXT,
    new_value TEXT,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES project_activities(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_activity_id (activity_id),
    INDEX idx_action_type (action_type)
) ENGINE=InnoDB;

-- Timeline Adjustment Requests table
CREATE TABLE IF NOT EXISTS timeline_adjustment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    requested_by INT NOT NULL,
    reason TEXT NOT NULL,
    new_start_date DATE NOT NULL,
    new_end_date DATE NOT NULL,
    status ENUM('PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
    reviewed_by INT NULL,
    review_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (activity_id) REFERENCES project_activities(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('DEADLINE_WARNING', 'ACTIVITY_DELAYED', 'DOCUMENT_UPLOADED', 'ADJUSTMENT_REQUEST', 'ADJUSTMENT_RESPONSE') NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id INT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Insert seed data for timeline templates (PUBLIC_BIDDING)
INSERT INTO timeline_templates (procurement_type, step_name, step_order, default_duration_days) VALUES
('PUBLIC_BIDDING', 'Pre-Procurement Conference', 1, 1),
('PUBLIC_BIDDING', 'Advertisement and Posting of Invitation to Bid', 2, 7),
('PUBLIC_BIDDING', 'Issuance and Availability of Bidding Documents', 3, 7),
('PUBLIC_BIDDING', 'Pre-Bid Conference', 4, 1),
('PUBLIC_BIDDING', 'Submission and Opening of Bids', 5, 1),
('PUBLIC_BIDDING', 'Bid Evaluation', 6, 7),
('PUBLIC_BIDDING', 'Post-Qualification', 7, 7),
('PUBLIC_BIDDING', 'BAC Resolution Recommending Award', 8, 1),
('PUBLIC_BIDDING', 'Notice of Award Preparation and Approval', 9, 2),
('PUBLIC_BIDDING', 'Notice of Award Issuance', 10, 1),
('PUBLIC_BIDDING', 'Contract Preparation and Signing', 11, 5),
('PUBLIC_BIDDING', 'Notice to Proceed', 12, 1);

-- Insert default admin/procurement user (password: admin123)
INSERT INTO users (name, email, password_hash, role) VALUES
('Procurement Admin', 'procurement@sdo.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROCUREMENT'),
('Project Owner', 'owner@sdo.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROJECT_OWNER');

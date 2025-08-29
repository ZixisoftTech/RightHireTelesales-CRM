-- Right Hire CRM Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS righthire_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE righthire_crm;

-- User Management
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('administrator', 'employee') NOT NULL,
    status TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at)
);

-- Geographical Data
CREATE TABLE states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at)
);

CREATE TABLE cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    status TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (state_id) REFERENCES states(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_state (state_id),
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at)
);

-- Employee Territory Assignments
CREATE TABLE employee_territories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    city_id INT NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (city_id) REFERENCES cities(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    UNIQUE KEY unique_assignment (employee_id, city_id),
    INDEX idx_deleted (deleted_at)
);

-- Lead Management
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    state_id INT NOT NULL,
    city_id INT NOT NULL,
    status ENUM('new', 'follow_up', 'not_attend', 'wrong_number', 'other', 'dead', 'interested', 'win') DEFAULT 'new',
    other_reason TEXT,
    assigned_to INT,
    follow_up_date DATETIME,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (state_id) REFERENCES states(id),
    FOREIGN KEY (city_id) REFERENCES cities(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_state_city (state_id, city_id),
    INDEX idx_assigned (assigned_to),
    INDEX idx_follow_up (follow_up_date),
    INDEX idx_deleted (deleted_at)
);

-- Call Logs
CREATE TABLE call_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    status ENUM('new', 'follow_up', 'not_attend', 'wrong_number', 'other', 'dead', 'interested', 'win') NOT NULL,
    remarks TEXT,
    follow_up_date DATETIME,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_lead (lead_id),
    INDEX idx_status (status),
    INDEX idx_follow_up (follow_up_date)
);

-- Audit Trail
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('create', 'update', 'delete', 'restore') NOT NULL,
    old_values JSON,
    new_values JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action)
);

-- Insert default admin user
-- Note: The password is 'Sales@112233' hashed with bcrypt
INSERT INTO users (name, email, password, role, status, created_at) 
VALUES ('Administrator', 'sales@getrigthhire.com', '$2y$12$Ht0vHVJmNJhxXDFdAbBIxuH.Dlq9VUCgHYwg1bwD8YJqIRQJwqU4e', 'administrator', 1, NOW());

-- Update the admin user's created_by to reference itself
UPDATE users SET created_by = 1, updated_by = 1 WHERE id = 1;


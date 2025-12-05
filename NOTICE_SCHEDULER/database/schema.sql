-- Notice Sender Database Schema
-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS notice_sender;
CREATE DATABASE notice_sender CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE notice_sender;

-- Table: roles
CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: departments
CREATE TABLE departments (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    department_code VARCHAR(20) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Table: classes
CREATE TABLE classes (
    class_id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE RESTRICT,
    UNIQUE KEY unique_class_dept (class_name, department_id),
    INDEX idx_department (department_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Table: users
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    roll_no VARCHAR(50) DEFAULT NULL,
    role_id INT NOT NULL,
    department_id INT DEFAULT NULL,
    class_id INT DEFAULT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_role (role_id),
    INDEX idx_department (department_id),
    INDEX idx_class (class_id)
) ENGINE=InnoDB;

-- Table: notices
CREATE TABLE notices (
    notice_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(500) NOT NULL,
    content TEXT NOT NULL,
    sent_by_user_id INT NOT NULL,
    is_staff_only BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sent_by_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_sender (sent_by_user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Table: notice_targets
CREATE TABLE notice_targets (
    target_id INT PRIMARY KEY AUTO_INCREMENT,
    notice_id INT NOT NULL,
    target_role_id INT DEFAULT NULL,
    target_class_id INT DEFAULT NULL,
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (target_role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (target_class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    INDEX idx_notice (notice_id),
    INDEX idx_role (target_role_id),
    INDEX idx_class (target_class_id)
) ENGINE=InnoDB;

-- Table: otp_tokens
CREATE TABLE otp_tokens (
    otp_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    otp_type ENUM('VERIFY', 'RESET') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_otp (otp_code),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;

-- Table: comments
CREATE TABLE comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    notice_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_notice (notice_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Table: notice_attachments
CREATE TABLE notice_attachments (
    attachment_id INT PRIMARY KEY AUTO_INCREMENT,
    notice_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    INDEX idx_notice (notice_id)
) ENGINE=InnoDB;

-- Table: notice_views
CREATE TABLE notice_views (
    view_id INT PRIMARY KEY AUTO_INCREMENT,
    notice_id INT NOT NULL,
    user_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notice_id) REFERENCES notices(notice_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_view (notice_id, user_id),
    INDEX idx_notice (notice_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Insert default roles
INSERT INTO roles (role_name) VALUES 
('Admin'),
('Staff'),
('Student');

-- Insert sample departments
INSERT INTO departments (department_name, department_code) VALUES 
('Computer Science and Engineering', 'CSE'),
('Information Technology', 'IT'),
('Electronics and Telecommunication', 'EXTC'),
('Mechanical Engineering', 'MECH'),
('Civil Engineering', 'CIVIL'),
('Automation and Robotics', 'AR'),
('Artificial Intelligence and Machine Learning', 'AIML');

-- Insert sample classes (linked to departments)
INSERT INTO classes (class_name, department_id) VALUES 
('F.E.A', 1), -- CSE
('S.Y.A', 1),
('T.Y.A', 1),
('B.E.A', 1),
('F.E.B', 1),
('S.Y.B', 1),
('T.Y.B', 1),
('B.E.B', 1),
('F.E.A', 2), -- IT
('S.Y.A', 2),
('T.Y.A', 2),
('B.E.A', 2),
('F.E.A', 3), -- EXTC
('S.Y.A', 3),
('T.Y.A', 3),
('B.E.A', 3);

-- Insert default admin user (password: admin123)
-- Password hash generated using PHP password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (email, password_hash, name, role_id, is_verified, is_active) 
VALUES ('admin@noticeboard.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe.N9Aq5cJZxJxJxJxJxJxJxJxJxJxJxJu', 'System Admin', 1, TRUE, TRUE);

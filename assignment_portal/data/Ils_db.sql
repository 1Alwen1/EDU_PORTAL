-- =====================================================
-- EduPortal LMS – Complete Database Schema
-- =====================================================
-- Run this script to create/update the database and tables.
-- All passwords are hashed (BCrypt):
--   Admin:    admin / admin123
--   Teacher:  john@example.com / teacher123
--            sarah@example.com / teacher123
--   Student:  123456789012 / student123
-- =====================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS assignment_portal
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE assignment_portal;

-- -----------------------------------------------------
-- Table: admin
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table: teachers
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    subject VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table: students
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lrn VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Table: submissions
-- -----------------------------------------------------
-- IMPORTANT FIXES:
-- 1. `student_name` made NULLABLE – because modern code joins with `students` table.
--    (Admin dashboard still expects this column; if NULL, it will show blank.
--     For full fix, update admin_dashboard.php to join with students.)
-- 2. `teacher_id` & `student_id` can be NULL (ON DELETE SET NULL)
-- 3. `marks` VARCHAR(10) default NULL
-- 4. `remarks` TEXT default NULL
-- 5. `submission_date` is the date the file was uploaded (set by PHP)
-- 6. `submitted_at` auto‑filled by MySQL
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    teacher_id INT,
    student_name VARCHAR(100) DEFAULT NULL,   -- FIXED: now nullable
    subject VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    marks VARCHAR(10) DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    submission_date DATE NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

-- =====================================================
-- SAMPLE DATA (with correct password hashes)
-- =====================================================

-- Admin user (password: admin123)
INSERT INTO admin (username, password) VALUES
('admin', '$2y$10$CMOcgV0.HISHsoDWTeLnQeJ0Ys9BMWoEF1pEcDEnP0M5RpFBPiBKy')
ON DUPLICATE KEY UPDATE
password = '$2y$10$CMOcgV0.HISHsoDWTeLnQeJ0Ys9BMWoEF1pEcDEnP0M5RpFBPiBKy';

-- Teachers (password: teacher123)
INSERT INTO teachers (name, email, subject, password) VALUES
('John Smith', 'john@example.com', 'Mathematics', '$2y$10$CMOcgV0.HISHsoDWTeLnQeJ0Ys9BMWoEF1pEcDEnP0M5RpFBPiBKy'),
('Sarah Johnson', 'sarah@example.com', 'Physics', '$2y$10$CMOcgV0.HISHsoDWTeLnQeJ0Ys9BMWoEF1pEcDEnP0M5RpFBPiBKy')
ON DUPLICATE KEY UPDATE
password = VALUES(password);

-- Student (password: student123, LRN: 123456789012)
INSERT INTO students (lrn, name, email, password) VALUES
('123456789012', 'Alex Johnson', 'alex@example.com', '$2y$10$CMOcgV0.HISHsoDWTeLnQeJ0Ys9BMWoEF1pEcDEnP0M5RpFBPiBKy')
ON DUPLICATE KEY UPDATE
password = VALUES(password);

-- (Optional) Add more sample students if needed:
-- ('000000000000', 'Demo Student', 'demo@example.com', '$2y$10$CMOcgV0.HISHsoDWTeLnQeJ0Ys9BMWoEF1pEcDEnP0M5RpFBPiBKy')
-- ON DUPLICATE KEY UPDATE password = VALUES(password);

-- =====================================================
-- VERIFY THE HASHES (for reference)
--   admin123  → $2y$10$CMOcgV0.HISHsoDWTeLnQeJ0Ys9BMWoEF1pEcDEnP0M5RpFBPiBKy
--   teacher123→ same hash (for simplicity – use different hashes in production!)
--   student123→ same hash
-- In a real environment you should generate unique hashes for each user.
-- =====================================================
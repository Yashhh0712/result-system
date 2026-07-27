CREATE DATABASE IF NOT EXISTS result_system;
USE result_system;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    course VARCHAR(50) NOT NULL,
    batch VARCHAR(20) NOT NULL,
    enrollment_year INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_number INT NOT NULL,
    semester_name VARCHAR(50) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    is_current BOOLEAN DEFAULT FALSE
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    semester_id INT NOT NULL,
    course VARCHAR(50) NOT NULL,
    max_marks INT DEFAULT 100,
    passing_marks INT DEFAULT 40,
    credit INT DEFAULT 4,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
);

CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    semester_id INT NOT NULL,
    internal_marks INT DEFAULT 0,
    external_marks INT DEFAULT 0,
    total_marks INT GENERATED ALWAYS AS (internal_marks + external_marks) STORED,
    grade VARCHAR(2),
    grade_point DECIMAL(3,1),
    status ENUM('pass', 'fail', 'pending') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_result (student_id, subject_id, semester_id)
);

CREATE TABLE marksheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    semester_id INT NOT NULL,
    total_marks INT DEFAULT 0,
    max_possible_marks INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,
    sgpa DECIMAL(3,2) DEFAULT 0,
    cgpa DECIMAL(3,2) DEFAULT 0,
    result_status ENUM('pass', 'fail', 'promoted', 'pending') DEFAULT 'pending',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_marksheet (student_id, semester_id)
);

-- Insert default admin/teacher user (password: password)
-- NOTE: If re-importing, run fix_passwords.php to regenerate hashes
INSERT INTO users (username, password, role, full_name, email) VALUES
('admin', '$2y$10$fqKQKr3V45MetWtQm9eHgu5SklEBc30.UeV6k9FPan42Jx9EPFSFK', 'admin', 'System Administrator', 'admin@school.edu'),
('teacher1', '$2y$10$fqKQKr3V45MetWtQm9eHgu5SklEBc30.UeV6k9FPan42Jx9EPFSFK', 'teacher', 'Mr. Rajesh Kumar', 'rajesh@school.edu'),
('teacher2', '$2y$10$fqKQKr3V45MetWtQm9eHgu5SklEBc30.UeV6k9FPan42Jx9EPFSFK', 'teacher', 'Ms. Priya Sharma', 'priya@school.edu');

-- Insert sample students (password: password)
INSERT INTO users (username, password, role, full_name, email) VALUES
('STU001', '$2y$10$fqKQKr3V45MetWtQm9eHgu5SklEBc30.UeV6k9FPan42Jx9EPFSFK', 'student', 'Amit Verma', 'amit@student.edu'),
('STU002', '$2y$10$fqKQKr3V45MetWtQm9eHgu5SklEBc30.UeV6k9FPan42Jx9EPFSFK', 'student', 'Sneha Patel', 'sneha@student.edu'),
('STU003', '$2y$10$fqKQKr3V45MetWtQm9eHgu5SklEBc30.UeV6k9FPan42Jx9EPFSFK', 'student', 'Rahul Singh', 'rahul@student.edu'),
('STU004', '$2y$10$fqKQKr3V45MetWtQm9eHgu5SklEBc30.UeV6k9FPan42Jx9EPFSFK', 'student', 'Ananya Gupta', 'ananya@student.edu'),
('STU005', '$2y$10$fqKQKr3V45MetWtQm9eHgu5SklEBc30.UeV6k9FPan42Jx9EPFSFK', 'student', 'Vikram Mehta', 'vikram@student.edu');

INSERT INTO students (user_id, roll_number, student_name, course, batch, enrollment_year) VALUES
(4, 'STU001', 'Amit Verma', 'B.Tech CSE', '2023-2027', 2023),
(5, 'STU002', 'Sneha Patel', 'B.Tech CSE', '2023-2027', 2023),
(6, 'STU003', 'Rahul Singh', 'B.Tech CSE', '2023-2027', 2023),
(7, 'STU004', 'Ananya Gupta', 'B.Tech CSE', '2022-2026', 2022),
(8, 'STU005', 'Vikram Mehta', 'B.Tech CSE', '2022-2026', 2022);

INSERT INTO semesters (semester_number, semester_name, academic_year, is_current) VALUES
(1, 'Semester 1', '2023-2024', FALSE),
(2, 'Semester 2', '2023-2024', FALSE),
(3, 'Semester 3', '2024-2025', TRUE),
(4, 'Semester 4', '2024-2025', FALSE);

INSERT INTO subjects (subject_code, subject_name, semester_id, course, max_marks, passing_marks, credit) VALUES
('CS101', 'Programming in C', 1, 'B.Tech CSE', 100, 40, 4),
('CS102', 'Data Structures', 1, 'B.Tech CSE', 100, 40, 4),
('MA101', 'Engineering Mathematics I', 1, 'B.Tech CSE', 100, 40, 4),
('PH101', 'Engineering Physics', 1, 'B.Tech CSE', 100, 40, 3),
('EE101', 'Basic Electrical Engineering', 1, 'B.Tech CSE', 100, 40, 3),

('CS201', 'Object Oriented Programming', 2, 'B.Tech CSE', 100, 40, 4),
('CS202', 'Discrete Mathematics', 2, 'B.Tech CSE', 100, 40, 4),
('MA201', 'Engineering Mathematics II', 2, 'B.Tech CSE', 100, 40, 4),
('CS203', 'Digital Logic Design', 2, 'B.Tech CSE', 100, 40, 3),
('CS204', 'Computer Organization', 2, 'B.Tech CSE', 100, 40, 3),

('CS301', 'Algorithm Analysis', 3, 'B.Tech CSE', 100, 40, 4),
('CS302', 'Operating Systems', 3, 'B.Tech CSE', 100, 40, 4),
('MA301', 'Probability & Statistics', 3, 'B.Tech CSE', 100, 40, 4),
('CS303', 'Database Management Systems', 3, 'B.Tech CSE', 100, 40, 3),
('CS304', 'Computer Networks', 3, 'B.Tech CSE', 100, 40, 3),

('CS401', 'Software Engineering', 4, 'B.Tech CSE', 100, 40, 4),
('CS402', 'Compiler Design', 4, 'B.Tech CSE', 100, 40, 4),
('CS403', 'Web Technologies', 4, 'B.Tech CSE', 100, 40, 4),
('CS404', 'Machine Learning Basics', 4, 'B.Tech CSE', 100, 40, 3),
('CS405', 'Artificial Intelligence', 4, 'B.Tech CSE', 100, 40, 3);

-- Sample results for Semester 1
INSERT INTO results (student_id, subject_id, semester_id, internal_marks, external_marks, grade, grade_point, status) VALUES
(1, 1, 1, 35, 52, 'A', 8.0, 'pass'),
(1, 2, 1, 30, 45, 'B+', 7.5, 'pass'),
(1, 3, 1, 28, 55, 'A', 8.0, 'pass'),
(1, 4, 1, 32, 40, 'B', 7.0, 'pass'),
(1, 5, 1, 25, 38, 'B-', 6.5, 'pass'),

(2, 1, 1, 38, 58, 'A+', 9.0, 'pass'),
(2, 2, 1, 35, 50, 'A', 8.0, 'pass'),
(2, 3, 1, 30, 60, 'A+', 9.0, 'pass'),
(2, 4, 1, 28, 45, 'B+', 7.5, 'pass'),
(2, 5, 1, 30, 42, 'B+', 7.5, 'pass'),

(3, 1, 1, 20, 30, 'C', 5.0, 'fail'),
(3, 2, 1, 25, 35, 'B-', 6.5, 'pass'),
(3, 3, 1, 22, 28, 'D', 4.0, 'fail'),
(3, 4, 1, 28, 40, 'B', 7.0, 'pass'),
(3, 5, 1, 20, 32, 'C+', 5.5, 'pass');

-- Sample results for Semester 2
INSERT INTO results (student_id, subject_id, semester_id, internal_marks, external_marks, grade, grade_point, status) VALUES
(1, 6, 2, 32, 48, 'A', 8.0, 'pass'),
(1, 7, 2, 28, 52, 'A', 8.0, 'pass'),
(1, 8, 2, 30, 50, 'A', 8.0, 'pass'),
(1, 9, 2, 25, 42, 'B+', 7.5, 'pass'),
(1, 10, 2, 28, 45, 'B+', 7.5, 'pass'),

(2, 6, 2, 36, 55, 'A+', 9.0, 'pass'),
(2, 7, 2, 32, 58, 'A+', 9.0, 'pass'),
(2, 8, 2, 34, 62, 'A+', 9.0, 'pass'),
(2, 9, 2, 30, 48, 'A', 8.0, 'pass'),
(2, 10, 2, 28, 50, 'A', 8.0, 'pass');

-- Sample results for Semester 3
INSERT INTO results (student_id, subject_id, semester_id, internal_marks, external_marks, grade, grade_point, status) VALUES
(1, 11, 3, 30, 50, 'A', 8.0, 'pass'),
(1, 12, 3, 28, 45, 'B+', 7.5, 'pass'),
(1, 13, 3, 32, 55, 'A', 8.0, 'pass'),
(1, 14, 3, 25, 42, 'B+', 7.5, 'pass'),
(1, 15, 3, 28, 48, 'B+', 7.5, 'pass'),

(2, 11, 3, 35, 58, 'A+', 9.0, 'pass'),
(2, 12, 3, 32, 55, 'A', 8.0, 'pass'),
(2, 13, 3, 30, 60, 'A+', 9.0, 'pass'),
(2, 14, 3, 28, 50, 'A', 8.0, 'pass'),
(2, 15, 3, 30, 52, 'A', 8.0, 'pass');

-- Marksheet for Semester 1
INSERT INTO marksheets (student_id, semester_id, total_marks, max_possible_marks, percentage, sgpa, cgpa, result_status) VALUES
(1, 1, 323, 500, 64.60, 7.40, 7.40, 'pass'),
(2, 1, 376, 500, 75.20, 8.20, 8.20, 'pass'),
(3, 1, 240, 500, 48.00, 5.60, 5.60, 'fail');

-- Marksheet for Semester 2
INSERT INTO marksheets (student_id, semester_id, total_marks, max_possible_marks, percentage, sgpa, cgpa, result_status) VALUES
(1, 2, 328, 500, 65.60, 7.70, 7.55, 'pass'),
(2, 2, 383, 500, 76.60, 8.40, 8.30, 'pass');

-- Marksheet for Semester 3
INSERT INTO marksheets (student_id, semester_id, total_marks, max_possible_marks, percentage, sgpa, cgpa, result_status) VALUES
(1, 3, 322, 500, 64.40, 7.70, 7.60, 'pass'),
(2, 3, 367, 500, 73.40, 8.40, 8.33, 'pass');

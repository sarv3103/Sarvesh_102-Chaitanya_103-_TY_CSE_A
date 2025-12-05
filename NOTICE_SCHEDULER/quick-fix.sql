-- Complete database fix with proper foreign key handling
SET FOREIGN_KEY_CHECKS = 0;

-- Clear all dependent tables first
DELETE FROM notice_targets;
DELETE FROM notice_views;
DELETE FROM comments;
DELETE FROM notices;
DELETE FROM users WHERE role_id != 1;
DELETE FROM classes;
DELETE FROM departments;

-- Reset auto increment
ALTER TABLE departments AUTO_INCREMENT = 1;
ALTER TABLE classes AUTO_INCREMENT = 1;

-- Insert departments
INSERT INTO departments (department_name, department_code) VALUES 
('Computer Science and Engineering', 'CSE'),
('Information Technology', 'IT'),
('Electronics and Telecommunication', 'EXTC'),
('Mechanical Engineering', 'MECH'),
('Civil Engineering', 'CIVIL'),
('Electrical Engineering', 'EE'),
('Chemical Engineering', 'CHEM');

-- Insert classes for each department
INSERT INTO classes (class_name, department_id) VALUES 
-- CSE Classes
('F.E.A', 1), ('S.Y.A', 1), ('T.Y.A', 1), ('B.E.A', 1),
('F.E.B', 1), ('S.Y.B', 1), ('T.Y.B', 1), ('B.E.B', 1),
-- IT Classes  
('F.E.A', 2), ('S.Y.A', 2), ('T.Y.A', 2), ('B.E.A', 2),
('F.E.B', 2), ('S.Y.B', 2), ('T.Y.B', 2), ('B.E.B', 2),
-- EXTC Classes
('F.E.A', 3), ('S.Y.A', 3), ('T.Y.A', 3), ('B.E.A', 3),
-- MECH Classes
('F.E.A', 4), ('S.Y.A', 4), ('T.Y.A', 4), ('B.E.A', 4),
-- CIVIL Classes
('F.E.A', 5), ('S.Y.A', 5), ('T.Y.A', 5), ('B.E.A', 5),
-- EE Classes
('F.E.A', 6), ('S.Y.A', 6), ('T.Y.A', 6), ('B.E.A', 6),
-- CHEM Classes
('F.E.A', 7), ('S.Y.A', 7), ('T.Y.A', 7), ('B.E.A', 7);

SET FOREIGN_KEY_CHECKS = 1;
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 07:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
SET FOREIGN_KEY_CHECKS=0;

--
-- Database: `payroll_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `att_id` int(11) NOT NULL,
  `emp_code` varchar(100) NOT NULL,
  `work_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `ot_hours` decimal(5,2) DEFAULT NULL,
  `attendance_status` enum('Present','Absent','On Leave','Holiday') DEFAULT 'Present',
  `source_system` enum('employee_management','payroll') DEFAULT 'payroll',
  `synced_at` datetime DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  KEY `idx_attendance_emp_code` (`emp_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`att_id`, `emp_code`, `work_date`, `time_in`, `time_out`, `hours_worked`, `ot_hours`, `attendance_status`) VALUES
(1, 'EMP-001', '2025-05-16', '08:00:00', '17:00:00', 8.00, 0.50, 'Present'),
(2, 'EMP-001', '2025-05-17', '08:00:00', '17:30:00', 8.50, 0.75, 'Present'),
(3, 'EMP-002', '2025-05-16', '08:00:00', '17:00:00', 8.00, 1.00, 'Present'),
(4, 'EMP-002', '2025-05-17', '08:00:00', '17:00:00', 8.00, 0.00, 'Present'),
(5, 'EMP-003', '2025-05-16', '08:00:00', '16:00:00', 7.50, 0.00, 'Present'),
(6, 'EMP-003', '2025-05-17', '08:00:00', '17:30:00', 8.50, 0.50, 'Present'),
(7, 'EMP-004', '2025-05-16', '09:00:00', '18:00:00', 8.00, 0.00, 'Present'),
(8, 'EMP-005', '2025-05-16', '08:00:00', '17:00:00', 8.00, 2.00, 'Present'),
(9, 'EMP-006', '2025-05-16', '08:30:00', '17:30:00', 8.00, 0.00, 'Present'),
(10, 'EMP-007', '2025-05-16', '08:00:00', '17:00:00', 8.00, 0.00, 'Present'),
(11, 'EMP-007', '2025-05-17', '08:00:00', '17:30:00', 8.50, 0.50, 'Present'),
(12, 'EMP-004', '2025-05-17', '09:00:00', '18:00:00', 8.00, 0.00, 'Present'),
(13, 'EMP-005', '2025-05-17', '08:00:00', '17:30:00', 8.50, 0.50, 'Present'),
(14, 'EMP-005', '2025-05-18', '08:00:00', '17:00:00', 8.00, 1.00, 'Present'),
(15, 'EMP-003', '2025-05-20', '08:00:00', '17:00:00', 8.00, 1.00, 'Present'),
(16, 'EMP-002', '2025-05-22', '08:00:00', '17:30:00', 8.50, 1.00, 'Present'),
(17, 'EMP-002', '2025-05-23', '08:00:00', '17:00:00', 8.00, 0.50, 'Present'),
(18, 'EMP-004', '2025-05-25', '09:00:00', '18:00:00', 8.00, 1.00, 'Present'),
(19, 'EMP-005', '2025-05-24', '08:00:00', '17:30:00', 8.50, 1.00, 'Present'),
(20, 'EMP-007', '2025-05-23', '08:00:00', '17:30:00', 8.50, 0.50, 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `audit_table`
--

DROP TABLE IF EXISTS `audit_table`;
CREATE TABLE `audit_table` (
  `audit_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL,
  `module` varchar(128) DEFAULT NULL,
  `affected_record` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Dumping data for table `audit_table`
--

INSERT INTO `audit_table` (`audit_id`, `user_id`, `action`, `timestamp`, `module`, `affected_record`) VALUES
(124, 1, 'update_user', '2025-11-21 20:24:17', 'User Management', 'user_id=5'),
(125, 1, 'update_setting', '2025-11-21 20:31:48', 'Security Controls', 'setting_name=Minimum Password Length, value=8'),
(126, 1, 'update_setting', '2025-11-21 20:31:48', 'Security Controls', 'setting_name=Require Uppercase Letters, value=1'),
(127, 1, 'update_setting', '2025-11-21 20:31:48', 'Security Controls', 'setting_name=Require Lowercase Letters, value=1'),
(128, 1, 'update_setting', '2025-11-21 20:31:48', 'Security Controls', 'setting_name=Require Numbers, value=1'),
(129, 1, 'update_setting', '2025-11-21 20:31:49', 'Security Controls', 'setting_name=Password Expiry, value=90'),
(130, 1, 'update_setting', '2025-11-21 20:31:49', 'Security Controls', 'setting_name=Require Symbols, value=1'),
(131, 1, 'update_setting', '2025-11-21 20:31:49', 'Security Controls', 'setting_name=Lock Account After, value=5'),
(136, 1, 'update_role_permissions', '2025-11-21 21:32:29', 'Role Management', 'role_id=1, items=9'),
(138, 1, 'update_user', '2025-11-22 00:47:26', 'User Management', 'user_id=5'),
(139, 1, 'restore_backup_data', '2025-11-22 01:47:14', 'Backup', 'restore={\"departments\":10,\"positions\":61,\"salary_structure\":20,\"deductions\":0,\"taxes\":0,\"benefits\":0}'),
(140, 1, 'update_user', '2025-11-22 01:47:40', 'User Management', 'user_id=5'),
(141, 1, 'restore_backup_data', '2025-11-22 01:47:54', 'Backup', 'restore={\"departments\":10,\"positions\":61,\"salary_structure\":20,\"deductions\":0,\"taxes\":0,\"benefits\":0}'),
(142, 1, 'restore_backup_data', '2025-11-22 01:48:43', 'Backup', 'restore={\"departments\":10,\"positions\":61,\"salary_structure\":20,\"deductions\":0,\"taxes\":0,\"benefits\":0}'),
(143, 1, 'restore_backup_data', '2025-11-22 01:48:52', 'Backup', 'restore={\"departments\":10,\"positions\":61,\"salary_structure\":20,\"deductions\":0,\"taxes\":0,\"benefits\":0}'),
(144, 1, 'create_user', '2025-11-22 01:50:13', 'User Management', 'user_id=7, email=restore@gmail.com'),
(145, 1, 'restore_backup_data', '2025-11-22 01:50:20', 'Backup', 'restore={\"departments\":10,\"positions\":61,\"salary_structure\":20,\"deductions\":0,\"taxes\":0,\"benefits\":0}'),
(146, 1, 'restore_backup_data', '2025-11-22 01:55:54', 'Backup', 'restore={\"departments\":10,\"positions\":61,\"salary_structure\":20,\"deductions\":0,\"taxes\":0,\"benefits\":0}'),
(147, 1, 'restore_backup_data', '2025-11-22 01:56:08', 'Backup', 'restore={\"departments\":10,\"positions\":61,\"salary_structure\":20,\"deductions\":0,\"taxes\":0,\"benefits\":0}'),
(148, 1, 'update_user', '2025-11-22 01:56:27', 'User Management', 'user_id=7'),
(149, 1, 'restore_backup_data', '2025-11-22 01:56:31', 'Backup', 'restore={\"departments\":10,\"positions\":61,\"salary_structure\":20,\"deductions\":0,\"taxes\":0,\"benefits\":0}');

-- --------------------------------------------------------

--
-- Table structure for table `benefits`
--

DROP TABLE IF EXISTS `benefits`;
CREATE TABLE `benefits` (
  `ben_id` int(11) NOT NULL,
  `ben_name` varchar(256) NOT NULL,
  `type` enum('fixed','custom_formula') DEFAULT NULL,
  `eligibility` enum('all_employees','full_time_only','as_needed','shift_workers','enrolled_employees') DEFAULT NULL,
  `rate_or_formula` varchar(1000) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `benefits`
--

INSERT INTO `benefits` (`ben_id`, `ben_name`, `type`, `eligibility`, `rate_or_formula`, `status`) VALUES
(1, 'Rice Subsidy', 'fixed', 'all_employees', '1000', 'active'),
(2, 'Transportation Allowance', 'fixed', 'full_time_only', '500', 'active'),
(3, 'Health Insurance', 'fixed', 'enrolled_employees', '5000', 'active');

-- --------------------------------------------------------


DROP TABLE IF EXISTS `deductions`;
CREATE TABLE `deductions` (
  `deduct_id` int(11) NOT NULL,
  `deduct_name` varchar(256) DEFAULT NULL,
  `type` enum('fixed','custom_formula') DEFAULT NULL,
  `rate_or_formula` varchar(256) DEFAULT NULL,
  `minimum` int(11) DEFAULT NULL,
  `maximum` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deductions`
--

INSERT INTO `deductions` (`deduct_id`, `deduct_name`, `type`, `rate_or_formula`, `minimum`, `maximum`, `status`) VALUES
(1, 'SSS', 'custom_formula', 'gross_pay * 0.05', NULL, NULL, 'active'),
(2, 'Philhealth', 'custom_formula', 'gross_pay * 0.025', NULL, NULL, 'active'),
(3, 'Pag-Ibig', 'fixed', '200', NULL, NULL, 'active'),
(4, 'tax', 'custom_formula', '(gross_pay-range_from)*(rate_on_excess/100)+additional_amount', NULL, NULL, 'active');

-- --------------------------------------------------------

DROP TABLE IF EXISTS `employee_benefits`;
CREATE TABLE `employee_benefits` (
  `emp_ben_id` int(11) NOT NULL,
  `emp_code` varchar(100) NOT NULL,
  `ben_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  KEY `idx_emp_benefit_code` (`emp_code`),
  KEY `idx_emp_benefit_ben` (`ben_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_benefits`
--

INSERT INTO `employee_benefits` (`emp_ben_id`, `emp_code`, `ben_id`, `status`) VALUES
(1, 'EMP-001', 1, 'active'),
(2, 'EMP-001', 2, 'active'),
(3, 'EMP-001', 3, 'active'),
(4, 'EMP-002', 1, 'active'),
(5, 'EMP-002', 2, 'active'),
(6, 'EMP-002', 3, 'active'),
(7, 'EMP-003', 1, 'active'),
(8, 'EMP-003', 2, 'active'),
(9, 'EMP-004', 1, 'active'),
(10, 'EMP-005', 1, 'active'),
(11, 'EMP-005', 2, 'active'),
(12, 'EMP-005', 3, 'active'),
(13, 'EMP-006', 1, 'active'),
(14, 'EMP-006', 2, 'active'),
(15, 'EMP-007', 1, 'active'),
(16, 'EMP-007', 2, 'active'),
(17, 'EMP-007', 3, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `employee_deductions`
--

DROP TABLE IF EXISTS `employee_deductions`;
CREATE TABLE `employee_deductions` (
  `emp_ded_id` int(11) NOT NULL,
  `emp_code` varchar(100) NOT NULL,
  `deduct_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  KEY `idx_emp_deduct_code` (`emp_code`),
  KEY `idx_emp_deduct_deduct` (`deduct_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_deductions`
--

INSERT INTO `employee_deductions` (`emp_ded_id`, `emp_code`, `deduct_id`, `status`) VALUES
(1, 'EMP-001', 1, 'active'),
(2, 'EMP-001', 2, 'active'),
(3, 'EMP-001', 3, 'active'),
(4, 'EMP-001', 4, 'active'),
(5, 'EMP-002', 1, 'active'),
(6, 'EMP-002', 2, 'active'),
(7, 'EMP-002', 3, 'active'),
(8, 'EMP-002', 4, 'active'),
(9, 'EMP-003', 1, 'active'),
(10, 'EMP-003', 2, 'active'),
(11, 'EMP-003', 3, 'active'),
(12, 'EMP-003', 4, 'active'),
(13, 'EMP-004', 1, 'active'),
(14, 'EMP-004', 2, 'active'),
(15, 'EMP-004', 3, 'active'),
(16, 'EMP-004', 4, 'active'),
(17, 'EMP-005', 1, 'active'),
(18, 'EMP-005', 2, 'active'),
(19, 'EMP-005', 3, 'active'),
(20, 'EMP-005', 4, 'active'),
(21, 'EMP-006', 1, 'active'),
(22, 'EMP-006', 2, 'active'),
(23, 'EMP-006', 3, 'active'),
(24, 'EMP-006', 4, 'active'),
(25, 'EMP-007', 1, 'active'),
(26, 'EMP-007', 2, 'active'),
(27, 'EMP-007', 3, 'active'),
(28, 'EMP-007', 4, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
CREATE TABLE `leave_requests` (
  `request_id` int(11) NOT NULL,
  `emp_code` varchar(100) NOT NULL,
  `request_type_id` int(11) DEFAULT NULL,
  `leave_type_id` int(11) DEFAULT NULL,
  `leave_type_name` varchar(100) DEFAULT NULL,
  `pay_category_id` int(11) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `pay_type` enum('Paid','Unpaid','Partially') DEFAULT 'Paid',
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_by_name` varchar(150) DEFAULT NULL,
  `requested_at` datetime DEFAULT current_timestamp(),
  `source_system` enum('employee_management','payroll') DEFAULT 'employee_management',
  `synced_at` datetime DEFAULT NULL,
  KEY `idx_leave_emp_code` (`emp_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`request_id`, `emp_code`, `request_type_id`, `leave_type_id`, `leave_type_name`, `pay_category_id`, `date_from`, `date_to`, `duration`, `pay_type`, `reason`, `status`, `approved_by`, `approved_by_name`, `requested_at`, `source_system`) VALUES
(1, 'EMP-003', 1, 1, 'Sick Leave', 1, '2025-05-20', '2025-05-21', 2, 'Paid', 'Medical leave request synced from employee_management', 'approved', 1, 'Payroll Admin', '2025-05-18 08:00:00', 'employee_management');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `attempt_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `attempt_time` datetime NOT NULL,
  `login_status` enum('success','failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`attempt_id`, `user_id`, `attempt_time`, `login_status`) VALUES
(1, 1, '2025-11-07 11:12:40', 'success'),
(2, 1, '2025-11-07 11:21:03', 'success'),
(3, 1, '2025-11-07 11:23:58', 'success'),
(4, 2, '2025-11-07 11:26:01', 'success'),
(5, 1, '2025-11-07 11:26:25', 'success');

-- --------------------------------------------------------

--
-- Table structure for table `overtime_requests`
--

DROP TABLE IF EXISTS `overtime_requests`;
CREATE TABLE `overtime_requests` (
  `overtime_id` int(11) NOT NULL,
  `emp_code` varchar(100) NOT NULL,
  `ot_date` date DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `rate` float DEFAULT NULL,
  `computed_amount` int(11) DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `source_system` enum('employee_management','payroll') DEFAULT 'payroll',
  `synced_at` datetime DEFAULT NULL,
  KEY `idx_ot_emp_code` (`emp_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `overtime_requests`
--

INSERT INTO `overtime_requests` (`overtime_id`, `emp_code`, `ot_date`, `hours`, `rate`, `computed_amount`, `status`, `approved_by`, `source_system`) VALUES
(1, 'EMP-002', '2025-05-16', 2, 1.25, NULL, 'approved', 1, 'payroll'),
(2, 'EMP-005', '2025-05-17', 3, 1.25, NULL, 'approved', 1, 'payroll'),
(3, 'EMP-001', '2025-05-17', 2, 1.25, NULL, 'approved', 1, 'payroll'),
(4, 'EMP-001', '2025-05-20', 2, 1.25, NULL, 'approved', 1, 'payroll'),
(5, 'EMP-002', '2025-05-22', 2, 1.25, NULL, 'approved', 1, 'payroll'),
(6, 'EMP-004', '2025-05-19', 2, 1.25, NULL, 'approved', 1, 'payroll'),
(7, 'EMP-004', '2025-05-25', 2, 1.25, NULL, 'approved', 1, 'payroll'),
(8, 'EMP-005', '2025-05-24', 2, 1.25, NULL, 'approved', 1, 'payroll'),
(9, 'EMP-007', '2025-05-18', 3, 1.25, NULL, 'approved', 1, 'payroll'),
(10, 'EMP-007', '2025-05-23', 2, 1.25, NULL, 'approved', 1, 'payroll');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

DROP TABLE IF EXISTS `password_reset`;
CREATE TABLE `password_reset` (
  `reset_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `session_id` varchar(256) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `status` enum('pending','used','expired') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

DROP TABLE IF EXISTS `payroll`;
CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `emp_code` varchar(100) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `position` varchar(150) DEFAULT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `salary_grade` int(11) DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `basic_pay` decimal(12,2) DEFAULT NULL,
  `days_worked` int(11) DEFAULT NULL,
  `ot_hours` int(11) DEFAULT NULL,
  `ot_pay` decimal(12,2) DEFAULT NULL,
  `allowances` decimal(12,2) DEFAULT NULL,
  `gross_pay` decimal(12,2) DEFAULT NULL,
  `late_absent_deductions` decimal(12,2) DEFAULT NULL,
  `sss` decimal(12,2) DEFAULT NULL,
  `philhealth` decimal(12,2) DEFAULT NULL,
  `pag_ibig` decimal(12,2) DEFAULT NULL,
  `tax` decimal(12,2) DEFAULT NULL,
  `other_deductions` decimal(12,2) DEFAULT NULL,
  `total_deduction` decimal(12,2) DEFAULT NULL,
  `net_pay` decimal(12,2) DEFAULT NULL,
  `payroll_status` enum('pending','approved','locked') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `payroll_period_id` int(11) DEFAULT NULL,
  `source_system` enum('payroll','employee_management') DEFAULT 'payroll',
  `synced_at` datetime DEFAULT NULL,
  KEY `idx_payroll_emp_code` (`emp_code`),
  KEY `idx_payroll_period` (`payroll_period_id`),
  KEY `idx_payroll_grade` (`salary_grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_period`
--

DROP TABLE IF EXISTS `payroll_period`;
CREATE TABLE `payroll_period` (
  `period_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('open','processing','locked','archived') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_period`
--

INSERT INTO `payroll_period` (`period_id`, `start_date`, `end_date`, `status`) VALUES
(1, '2025-01-01', '2025-01-15', 'archived'),
(2, '2025-01-16', '2025-01-31', 'archived'),
(3, '2025-02-01', '2025-02-15', 'archived'),
(4, '2025-02-16', '2025-02-28', 'archived'),
(5, '2025-03-01', '2025-03-15', 'locked'),
(6, '2025-03-16', '2025-03-31', 'locked'),
(7, '2025-04-01', '2025-04-15', 'processing'),
(8, '2025-04-16', '2025-04-30', 'processing'),
(9, '2025-05-01', '2025-05-15', 'open'),
(10, '2025-05-16', '2025-05-31', 'open');

-- --------------------------------------------------------

DROP TABLE IF EXISTS `remember_sessions`;
CREATE TABLE `remember_sessions` (
  `r_session_id` varchar(256) NOT NULL,
  `user_id` int(100) NOT NULL,
  `login_time` datetime NOT NULL,
  `expiry_date` datetime NOT NULL,
  `r_session_status` enum('active','ended') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles_table`
--

DROP TABLE IF EXISTS `roles_table`;
CREATE TABLE `roles_table` (
  `role_id` int(100) NOT NULL,
  `role_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles_table`
--

INSERT INTO `roles_table` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Payroll Officer'),
(3, 'Manager'),
(4, 'Employee');


DROP TABLE IF EXISTS `salary_grades`;
CREATE TABLE `salary_grades` (
  `salary_grade` int(11) NOT NULL,
  `step_1` int(11) DEFAULT NULL,
  `step_2` int(11) DEFAULT NULL,
  `step_3` int(11) DEFAULT NULL,
  `step_4` int(11) DEFAULT NULL,
  `step_5` int(11) DEFAULT NULL,
  `step_6` int(11) DEFAULT NULL,
  `step_7` int(11) DEFAULT NULL,
  `step_8` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_grades`
--

INSERT INTO `salary_grades` (`salary_grade`, `step_1`, `step_2`, `step_3`, `step_4`, `step_5`, `step_6`, `step_7`, `step_8`) VALUES
(1, 14061, 14164, 14278, 14393, 14509, 14626, 14743, 14862),
(2, 14925, 15035, 15146, 15258, 15371, 15484, 15599, 15714),
(3, 15852, 15971, 16088, 16208, 16329, 16448, 16571, 16693),
(4, 16833, 16958, 17084, 17209, 17337, 17464, 17594, 17724),
(5, 17866, 18000, 18133, 18267, 18401, 18538, 18676, 18813),
(6, 18957, 19098, 19239, 19383, 19526, 19670, 19816, 19963),
(7, 20110, 20258, 20409, 20560, 20711, 20865, 21019, 21175),
(8, 21448, 21642, 21839, 22035, 22234, 22435, 22638, 22843),
(9, 23226, 23411, 23599, 23788, 23978, 24170, 24364, 24558),
(10, 25586, 25790, 25996, 26203, 26412, 26623, 26835, 27050),
(11, 30024, 30308, 30597, 30889, 31185, 31486, 31790, 32099),
(12, 32245, 32529, 32817, 33108, 33403, 33702, 34044, 34310),
(13, 34421, 34733, 35049, 35369, 35694, 36022, 36354, 36691),
(14, 37024, 37384, 37749, 38118, 38491, 38869, 39252, 39640),
(15, 40208, 40604, 41006, 41413, 41824, 42241, 42662, 43090),
(16, 43560, 43996, 44438, 44885, 45338, 45796, 46261, 46730),
(17, 47247, 47727, 48213, 48705, 49203, 49708, 50218, 50735),
(18, 51304, 51832, 52367, 52907, 53456, 54010, 54572, 55140),
(19, 56390, 57165, 57953, 58753, 59567, 60394, 61235, 62089),
(20, 62967, 63842, 64732, 65637, 66557, 67479, 68409, 69342),
(21, 70103, 71000, 72004, 73024, 74061, 75115, 76151, 77239),
(22, 78162, 79277, 80411, 81564, 82735, 83887, 85096, 86342),
(23, 87315, 88574, 89855, 91163, 92592, 94043, 95518, 96955),
(24, 98185, 99721, 101283, 102871, 104483, 106123, 107739, 109431),
(25, 111727, 113476, 115254, 117062, 118899, 120766, 122664, 124591),
(26, 126252, 128228, 130238, 132280, 134356, 136465, 138608, 140788),
(27, 142663, 144897, 147169, 149407, 151752, 153850, 156267, 158723),
(28, 160469, 162988, 165548, 167994, 170634, 173320, 175803, 178572),
(29, 180492, 183332, 186218, 189151, 192131, 194797, 197870, 200993),
(30, 203200, 206401, 209558, 212766, 216022, 219434, 222797, 226319),
(31, 293191, 298773, 304464, 310119, 315883, 321846, 327895, 334059),
(32, 347888, 354743, 361736, 368694, 375969, 383391, 390963, 398686),
(33, 438844, 451713, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payroll_employee_profile`
--

DROP TABLE IF EXISTS `payroll_employee_profile`;
CREATE TABLE `payroll_employee_profile` (
  `emp_code` varchar(100) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `department` varchar(150) DEFAULT NULL,
  `position` varchar(150) DEFAULT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `salary_grade` int(11) DEFAULT NULL,
  `step` enum('step_1','step_2','step_3','step_4','step_5','step_6','step_7','step_8') DEFAULT NULL,
  `base_rate` decimal(12,2) DEFAULT NULL,
  `monthly_allowance` decimal(12,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `source_system` enum('employee_management','payroll') DEFAULT 'employee_management',
  `synced_at` datetime DEFAULT NULL,
  PRIMARY KEY (`emp_code`),
  KEY `idx_profile_grade` (`salary_grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payroll_employee_profile` (`emp_code`, `full_name`, `department`, `position`, `employment_type`, `salary_grade`, `step`, `base_rate`, `monthly_allowance`, `status`) VALUES
('EMP-001', 'Rhoanne Nicole Antonio', 'Human Resources (HR) Department', 'HR Manager', 'Full Time', 24, 'step_3', 104483.00, 3000.00, 'active'),
('EMP-002', 'Jhanna Jaroda', 'Human Resources (HR) Department', 'Recruitment Manager', 'Full Time', 20, 'step_2', 69342.00, 1500.00, 'active'),
('EMP-003', 'Shane Ella Cacho', 'Human Resources (HR) Department', 'Training and Development Coordinator', 'Full Time', 16, 'step_4', 44885.00, 1200.00, 'active'),
('EMP-004', 'Jodie Lyn Gutierrez', 'Human Resources (HR) Department', 'HR Officer', 'Full Time', 15, 'step_3', 41006.00, 1000.00, 'active'),
('EMP-005', 'Carlos Mendoza', 'Cardiology Department', 'Cardiac Technologist', 'Full Time', 17, 'step_2', 47727.00, 2000.00, 'active'),
('EMP-006', 'Miguel Santos', 'Utility Department', 'Utility Head', 'Contractual', 12, 'step_1', 32245.00, 800.00, 'active'),
('EMP-007', 'Patricia Gomez', 'Gastroenterology Department', 'Endoscopy Nurse', 'Regular', 15, 'step_5', 41824.00, 900.00, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `security_log_table`
--

DROP TABLE IF EXISTS `security_log_table`;
CREATE TABLE `security_log_table` (
  `security_log_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime NOT NULL,
  `status` enum('success','failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `session_id` varchar(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL,
  `session_status` enum('active','ended') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `user_id`, `login_time`, `logout_time`, `session_status`) VALUES
('1b4f9bae7ec124ea752b614bcc345941abb344da8448f05052b2dc2982f72209', 1, '2025-10-25 12:28:17', '2025-10-25 12:28:21', 'ended'),
('48bf69973c6dac3cbff37a34bc0243b4e07e6101a8a310e9e57d158eaf65a8be', 1, '2025-10-25 12:58:46', '2025-10-25 13:13:24', 'ended'),
('599458f4a75b8853e5fb210633d5d85a17e10e301345ab024ef4041d46f41bb6', 2, '2025-10-25 13:19:34', '2025-10-25 13:24:49', 'ended'),
('5ea68abf550df2c0792ff8be7578984b6322528fe48460f815c79713af06b896', 1, '2025-10-25 13:13:39', '2025-10-25 13:16:20', 'ended'),
('6271dbf6609e8017eb2bb1eb8fed221d321d25dcb6dde281c8454e8e35fd0dc2', 1, '2025-11-07 11:23:58', '2025-11-07 11:24:29', 'ended'),
('75bd155c507fc87aed2429b9b2259b30bb8c4cce9ca0e6ddc0c2ec8f273a4f06', 6, '2025-10-25 12:03:51', NULL, 'active'),
('813f9213a80c9b8c1ac72e8fb1be15ca24eab8054b2b7414c65e0c371d917665', 1, '2025-11-07 11:12:40', NULL, 'active'),
('93523258831534797a2a593cb7e4a9cff74611dd5cdb8354a656c746d09fdfd3', 6, '2025-10-25 12:04:31', NULL, 'active'),
('9f399492807fe28ff43687535599183144889ea119628ebbdbfd17d6e2b47526', 1, '2025-11-07 11:26:25', NULL, 'active'),
('a245f25008d511601a46c6a54826b1d27d9ff10ed8dda6c9564e63edcc4e1258', 2, '2025-11-07 11:26:01', '2025-11-07 11:26:13', 'ended'),
('cf60f09e79899069af757e9f0c793bb1713421e5db7fedee3455201008a4a2c2', 1, '2025-10-25 12:27:57', '2025-10-25 12:28:02', 'ended'),
('d6a7234c6b4839b70af49dcd264e9f97d3f68e4bbebad5fe0551f1d77a8d9e38', 4, '2025-10-25 13:16:34', '2025-10-25 13:17:20', 'ended'),
('db9e8324abbc94978341cfa7f8e0ee28419f27e7a25b9da21193dc1524b2bfb2', 1, '2025-10-25 13:25:32', '2025-10-30 16:06:44', 'ended'),
('e5bedfc0c3a757177a65d508a3586e18aaa4d11e37c2152917e88880d8720fab', 4, '2025-10-25 12:05:12', '2025-10-25 12:05:19', 'ended');

-- --------------------------------------------------------

--
-- Table structure for table `settings_table`
--

DROP TABLE IF EXISTS `settings_table`;
CREATE TABLE `settings_table` (
  `setting_id` int(100) NOT NULL,
  `setting_name` varchar(256) NOT NULL,
  `value` varchar(256) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings_table`
--

INSERT INTO `settings_table` (`setting_id`, `setting_name`, `value`, `description`) VALUES
(1, 'Minimum Password Length', '8', 'Minimum characters allowed for passwords.'),
(2, 'Require Uppercase Letters', '1', 'Require Uppercase Letters'),
(3, 'Require Lowercase Letters', '1', 'Require Lowercase Letters'),
(4, 'Require Numbers', '1', 'Require Lowercase Letters'),
(5, 'Require Symbols', '1', 'Require Symbols'),
(6, 'Password Expiry', '90', 'Password Expiry'),
(7, 'Lock Account After', '5', 'Lock Account After'),
(8, 'Company Name', 'SIA Health Systems', 'Organization legal name used in headers'),
(9, 'Company Address', '123 Health St., Pasig City', 'Organization address used in headers'),
(10, 'Company Contact', '+63 900 000 0000', 'Organization contact info used in headers');

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

DROP TABLE IF EXISTS `taxes`;
CREATE TABLE `taxes` (
  `tax_id` int(11) NOT NULL,
  `range_from` int(11) DEFAULT NULL,
  `range_to` int(11) DEFAULT NULL,
  `rate_on_excess` int(11) DEFAULT NULL,
  `additional_amount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taxes`
--

INSERT INTO `taxes` (`tax_id`, `range_from`, `range_to`, `rate_on_excess`, `additional_amount`) VALUES
(1, 0, 20833, 0, 0),
(2, 20833, 33332, 15, 0),
(3, 33333, 66666, 20, 2500),
(4, 66667, 166666, 25, 10833),
(5, 166667, 666666, 30, 40833),
(6, 666667, NULL, 35, 200833);

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

DROP TABLE IF EXISTS `user_table`;
CREATE TABLE `user_table` (
  `user_id` int(100) NOT NULL,
  `user_name` varchar(256) NOT NULL,
  `user_email` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `role_id` int(100) NOT NULL,
  `employee_code` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL,
  `login_type` enum('internal','employee') DEFAULT 'employee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`user_id`, `user_name`, `user_email`, `password`, `role_id`, `employee_code`, `status`, `login_type`) VALUES
(1, 'Klarenz Cobie Manrique', 'manrique_klarenzcobie@plpasig.edu.ph', '123456789', 1, NULL, 'active', 'internal'),
(2, 'Charles Jeramy De Padua', 'depadua_charlesjeramy@plpasig.edu.ph', '123456789', 2, 'EMP-001', 'active', 'employee'),
(3, 'Charl Joven Castro', 'castro_charljoven@plpasig.edu.ph', '123456789', 3, 'EMP-002', 'active', 'employee'),
(4, 'Jesseroe Piatos', 'piatos_jesseroe@plpasig.edu.ph', '123456789', 4, 'EMP-003', 'active', 'employee'),
(5, 'Graci Al Dei Medrano', 'medrano_gracialdei@plpasig.edu.ph', '123456789', 4, 'EMP-004', 'active', 'employee'),
(6, 'Admin', 'test@gmail.com', '123123123', 1, NULL, 'active', 'internal'),
(7, 'Stephen Strange', 'stephenstrange@gmail.com', '123456789', 4, 'EMP-005', 'active', 'employee');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`att_id`);

--
-- Indexes for table `audit_table`
--
ALTER TABLE `audit_table`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `audit_user_c` (`user_id`);

--
-- Indexes for table `benefits`
--
ALTER TABLE `benefits`
  ADD PRIMARY KEY (`ben_id`);

-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`deduct_id`);

-- Indexes for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  ADD PRIMARY KEY (`emp_ben_id`);

--
-- Indexes for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD PRIMARY KEY (`emp_ded_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `login_attemprt_user_id_c` (`user_id`);

--
-- Indexes for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  ADD PRIMARY KEY (`overtime_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `password_test_user_id_c` (`user_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`);

--
-- Indexes for table `payroll_period`
--
ALTER TABLE `payroll_period`
  ADD PRIMARY KEY (`period_id`);

-- Indexes for table `remember_sessions`
--
ALTER TABLE `remember_sessions`
  ADD PRIMARY KEY (`r_session_id`),
  ADD KEY `remember_user_id_constraint` (`user_id`);

--
-- Indexes for table `roles_table`
--
ALTER TABLE `roles_table`
  ADD PRIMARY KEY (`role_id`);

-- Indexes for table `salary_grades`
--
ALTER TABLE `salary_grades`
  ADD PRIMARY KEY (`salary_grade`);

--
-- Indexes for table `payroll_employee_profile`
--
-- Note: PRIMARY KEY is already defined in CREATE TABLE, no need to add it here

--
-- Indexes for table `security_log_table`
--
ALTER TABLE `security_log_table`
  ADD PRIMARY KEY (`security_log_id`),
  ADD KEY `security_log_user_id_c` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `sessions_user_id_c` (`user_id`);

--
-- Indexes for table `settings_table`
--
ALTER TABLE `settings_table`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`tax_id`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD KEY `user_tables_role_id_c` (`role_id`),
  ADD KEY `idx_user_employee_code` (`employee_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `att_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `audit_table`
--
ALTER TABLE `audit_table`
  MODIFY `audit_id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefits`
--
ALTER TABLE `benefits`
  MODIFY `ben_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `deduct_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- AUTO_INCREMENT for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  MODIFY `emp_ben_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  MODIFY `emp_ded_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  MODIFY `overtime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `reset_id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_period`
--
ALTER TABLE `payroll_period`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles_table`
--
ALTER TABLE `roles_table`
  MODIFY `role_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- AUTO_INCREMENT for table `security_log_table`
--
ALTER TABLE `security_log_table`
  MODIFY `security_log_id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings_table`
--
ALTER TABLE `settings_table`
  MODIFY `setting_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `tax_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_employee_fk` FOREIGN KEY (`emp_code`) REFERENCES `payroll_employee_profile` (`emp_code`) ON UPDATE CASCADE;

--
-- Constraints for table `audit_table`
--
ALTER TABLE `audit_table`
  ADD CONSTRAINT `audit_user_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE;

-- Constraints for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  ADD CONSTRAINT `employee_benefits_profile_fk` FOREIGN KEY (`emp_code`) REFERENCES `payroll_employee_profile` (`emp_code`) ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_benefits_ibfk_2` FOREIGN KEY (`ben_id`) REFERENCES `benefits` (`ben_id`);

--
-- Constraints for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD CONSTRAINT `employee_deductions_profile_fk` FOREIGN KEY (`emp_code`) REFERENCES `payroll_employee_profile` (`emp_code`) ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_deductions_ibfk_2` FOREIGN KEY (`deduct_id`) REFERENCES `deductions` (`deduct_id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_profile_fk` FOREIGN KEY (`emp_code`) REFERENCES `payroll_employee_profile` (`emp_code`) ON UPDATE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `user_table` (`user_id`);

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `login_attemprt_user_id_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  ADD CONSTRAINT `overtime_requests_profile_fk` FOREIGN KEY (`emp_code`) REFERENCES `payroll_employee_profile` (`emp_code`) ON UPDATE CASCADE,
  ADD CONSTRAINT `overtime_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `user_table` (`user_id`);

--
-- Constraints for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `password_test_user_id_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_profile_fk` FOREIGN KEY (`emp_code`) REFERENCES `payroll_employee_profile` (`emp_code`) ON UPDATE CASCADE,
  ADD CONSTRAINT `payroll_grade_fk` FOREIGN KEY (`salary_grade`) REFERENCES `salary_grades` (`salary_grade`) ON UPDATE CASCADE,
  ADD CONSTRAINT `payroll_period_fk` FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_period` (`period_id`);

ALTER TABLE `remember_sessions`
  ADD CONSTRAINT `remember_user_id_constraint` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE;

-- Constraints for table `security_log_table`
--
ALTER TABLE `security_log_table`
  ADD CONSTRAINT `security_log_user_id_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_user_id_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_table`
--
ALTER TABLE `user_table`
  ADD CONSTRAINT `user_tables_role_id_c` FOREIGN KEY (`role_id`) REFERENCES `roles_table` (`role_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_table_employee_fk` FOREIGN KEY (`employee_code`) REFERENCES `payroll_employee_profile` (`emp_code`) ON UPDATE CASCADE;

--
-- Constraints for table `payroll_employee_profile`
--
ALTER TABLE `payroll_employee_profile`
  ADD CONSTRAINT `profile_grade_fk` FOREIGN KEY (`salary_grade`) REFERENCES `salary_grades` (`salary_grade`) ON UPDATE CASCADE;

DROP TABLE IF EXISTS `backup_schedule`;
CREATE TABLE `backup_schedule` (
  `schedule_id` int(11) NOT NULL DEFAULT 1,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `frequency` enum('hourly','daily','weekly') NOT NULL DEFAULT 'daily',
  `run_time` varchar(5) NOT NULL DEFAULT '22:00',
  `next_run` datetime DEFAULT NULL,
  `last_run` datetime DEFAULT NULL,
  PRIMARY KEY (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `backup_snapshots`;
CREATE TABLE `backup_snapshots` (
  `backup_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `size_bytes` int(11) NOT NULL,
  `data_json` longtext NOT NULL,
  PRIMARY KEY (`backup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `pages_catalog`;
CREATE TABLE `pages_catalog` (
  `page_key` varchar(128) NOT NULL,
  `display_name` varchar(128) NOT NULL,
  `page_link` varchar(128) NOT NULL,
  `group_name` varchar(64) NOT NULL,
  PRIMARY KEY (`page_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pages_catalog` (`page_key`, `display_name`, `page_link`, `group_name`) VALUES
('admin_audit_logs', 'Audit Logs', 'admin_audit_logs.php', 'Admin'),
('admin_company_settings', 'Company Settings', 'admin_company_settings.php', 'Admin'),
('admin_dashboard', 'Admin Dashboard', 'admin_dashboard.php', 'Admin'),
('admin_data_backup_and_restore', 'Data Backup & Restore', 'admin_data_backup_and_restore.php', 'Admin'),
('admin_role_management', 'Role Management', 'admin_role_management.php', 'Admin'),
('admin_security_controls', 'Security Controls', 'admin_security_controls.php', 'Admin'),
('admin_summary_report', 'Summary Reports', 'admin_summary_report.php', 'Admin'),
('admin_system_configuration', 'System Configuration', 'admin_system_configuration.php', 'Admin'),
('admin_user_management', 'User Management', 'admin_user_management.php', 'Admin'),
('employee_attendance_log', 'Attendance Log', 'Employee_Attendance_Log.php', 'Employee'),
('employee_dashboard', 'Employee Dashboard', 'employee_dashboard.php', 'Employee'),
('employee_leave_overtime', 'Leave and Overtime', 'Employee_Leave_Overtime.php', 'Employee'),
('employee_payroll_history', 'Payroll History', 'employee_payroll_history.php', 'Employee'),
('employee_profile', 'Profile', 'Employee_Profile.php', 'Employee'),
('manager_approval_page', 'Approval', 'manager_approval_page.php', 'Manager'),
('manager_dashboard', 'Manager Dashboard', 'manager_dashboard.php', 'Manager'),
('manager_employee_list', 'Employee List', 'manager_employee_list.php', 'Manager'),
('manager_endorsement', 'Endorsement', 'manager_endorsement.php', 'Manager'),
('manager_payroll_summary_generation', 'Payroll Summary Generation', 'manager_payroll_summary_generation.php', 'Manager'),
('manager_report_generation', 'Report Generation', 'manager_report_generation.php', 'Manager'),
('payroll_officer_attendance_management', 'Attendance Management', 'payroll_officer_attendance_management.php', 'Payroll Officer'),
('payroll_officer_dashboard', 'Payroll Officer Dashboard', 'payroll_officer_dashboard.php', 'Payroll Officer'),
('payroll_officer_employee_management', 'Employee Management', 'payroll_officer_employee_management.php', 'Payroll Officer'),
('payroll_officer_government_remittance', 'Government Remittance', 'payroll_officer_government_remittance.php', 'Payroll Officer'),
('payroll_officer_ot_leave_bonus', 'OT/Leave/Bonus', 'payroll_officer_ot_leave_bonus.php', 'Payroll Officer'),
('payroll_officer_payroll_computation', 'Payroll Computation', 'payroll_officer_payroll_computation.php', 'Payroll Officer'),
('payroll_officer_payroll_history', 'Payroll History', 'payroll_officer_payroll_history.php', 'Payroll Officer'),
('payroll_officer_payslip_generation', 'Payslip Generation', 'payroll_officer_payslip_generation.php', 'Payroll Officer'),
('payroll_officer_period_management', 'Period Management', 'payroll_officer_period_management.php', 'Payroll Officer');

INSERT INTO `pages_catalog` (`page_key`, `display_name`, `page_link`, `group_name`) VALUES
('admin_work_schedules', 'Work Schedules', 'admin_work_schedules.php', 'Admin');

-- Note: date_from, date_to, hours, and computed_amount are already defined in CREATE TABLE for overtime_requests

ALTER TABLE `backup_snapshots`
  ADD CONSTRAINT `backup_snapshots_user_fk` FOREIGN KEY (`created_by`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE;

--
-- Table structure for table `role_page_permissions`
--

CREATE TABLE `role_page_permissions` (
  `role_id` int(11) NOT NULL,
  `page_key` varchar(128) NOT NULL,
  `can_access` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_page_permissions`
--

INSERT INTO `role_page_permissions` (`role_id`, `page_key`, `can_access`) VALUES
(1, 'admin_audit_logs', 1),
(1, 'admin_company_settings', 1),
(1, 'admin_dashboard', 1),
(1, 'admin_data_backup_and_restore', 1),
(1, 'admin_role_management', 1),
(1, 'admin_security_controls', 1),
(1, 'admin_summary_report', 1),
(1, 'admin_system_configuration', 1),
(1, 'admin_user_management', 1),
(1, 'employee_attendance_log', 0),
(1, 'employee_dashboard', 0),
(1, 'employee_leave_overtime', 0),
(1, 'employee_payroll_history', 0),
(1, 'employee_profile', 0),
(1, 'manager_approval_page', 0),
(1, 'manager_dashboard', 0),
(1, 'manager_employee_list', 0),
(1, 'manager_endorsement', 0),
(1, 'manager_payroll_summary_generation', 0),
(1, 'manager_report_generation', 0),
(1, 'payroll_officer_attendance_management', 0),
(1, 'payroll_officer_dashboard', 0),
(1, 'payroll_officer_employee_management', 0),
(1, 'payroll_officer_government_remittance', 0),
(1, 'payroll_officer_ot_leave_bonus', 0),
(1, 'payroll_officer_payroll_computation', 0),
(1, 'payroll_officer_payroll_history', 0),
(1, 'payroll_officer_payslip_generation', 0),
(1, 'payroll_officer_period_management', 0),
(2, 'admin_audit_logs', 0),
(2, 'admin_company_settings', 0),
(2, 'admin_dashboard', 0),
(2, 'admin_data_backup_and_restore', 0),
(2, 'admin_role_management', 0),
(2, 'admin_security_controls', 0),
(2, 'admin_summary_report', 0),
(2, 'admin_system_configuration', 0),
(2, 'admin_user_management', 0),
(2, 'employee_attendance_log', 0),
(2, 'employee_dashboard', 0),
(2, 'employee_leave_overtime', 0),
(2, 'employee_payroll_history', 0),
(2, 'employee_profile', 0),
(2, 'manager_approval_page', 0),
(2, 'manager_dashboard', 0),
(2, 'manager_employee_list', 0),
(2, 'manager_endorsement', 0),
(2, 'manager_payroll_summary_generation', 0),
(2, 'manager_report_generation', 0),
(2, 'payroll_officer_attendance_management', 1),
(2, 'payroll_officer_dashboard', 1),
(2, 'payroll_officer_employee_management', 1),
(2, 'payroll_officer_government_remittance', 1),
(2, 'payroll_officer_ot_leave_bonus', 1),
(2, 'payroll_officer_payroll_computation', 1),
(2, 'payroll_officer_payroll_history', 1),
(2, 'payroll_officer_payslip_generation', 1),
(2, 'payroll_officer_period_management', 1),
(1, 'admin_work_schedules', 1),
(3, 'admin_audit_logs', 0),
(3, 'admin_company_settings', 0),
(3, 'admin_dashboard', 0),
(3, 'admin_data_backup_and_restore', 0),
(3, 'admin_role_management', 0),
(3, 'admin_security_controls', 0),
(3, 'admin_summary_report', 0),
(3, 'admin_system_configuration', 0),
(3, 'admin_user_management', 0),
(3, 'employee_attendance_log', 0),
(3, 'employee_dashboard', 0),
(3, 'employee_leave_overtime', 0),
(3, 'employee_payroll_history', 0),
(3, 'employee_profile', 0),
(3, 'manager_approval_page', 1),
(3, 'manager_dashboard', 1),
(3, 'manager_employee_list', 1),
(3, 'manager_endorsement', 1),
(3, 'manager_payroll_summary_generation', 1),
(3, 'manager_report_generation', 1),
(3, 'payroll_officer_attendance_management', 0),
(3, 'payroll_officer_dashboard', 0),
(3, 'payroll_officer_employee_management', 0),
(3, 'payroll_officer_government_remittance', 0),
(3, 'payroll_officer_ot_leave_bonus', 0),
(3, 'payroll_officer_payroll_computation', 0),
(3, 'payroll_officer_payroll_history', 0),
(3, 'payroll_officer_payslip_generation', 0),
(3, 'payroll_officer_period_management', 0),
(4, 'admin_audit_logs', 0),
(4, 'admin_company_settings', 0),
(4, 'admin_dashboard', 0),
(4, 'admin_data_backup_and_restore', 0),
(4, 'admin_role_management', 0),
(4, 'admin_security_controls', 0),
(4, 'admin_summary_report', 0),
(4, 'admin_system_configuration', 0),
(4, 'admin_user_management', 0),
(4, 'employee_attendance_log', 1),
(4, 'employee_dashboard', 1),
(4, 'employee_leave_overtime', 1),
(4, 'employee_payroll_history', 1),
(4, 'employee_profile', 1),
(4, 'manager_approval_page', 0),
(4, 'manager_dashboard', 0),
(4, 'manager_employee_list', 0),
(4, 'manager_endorsement', 0),
(4, 'manager_payroll_summary_generation', 0),
(4, 'manager_report_generation', 0),
(4, 'payroll_officer_attendance_management', 0),
(4, 'payroll_officer_dashboard', 0),
(4, 'payroll_officer_employee_management', 0),
(4, 'payroll_officer_government_remittance', 0),
(4, 'payroll_officer_ot_leave_bonus', 0),
(4, 'payroll_officer_payroll_computation', 0),
(4, 'payroll_officer_payroll_history', 0),
(4, 'payroll_officer_payslip_generation', 0),
(4, 'payroll_officer_period_management', 0);

-- --------------------------------------------------------

INSERT INTO `backup_snapshots` (`backup_id`, `file_name`, `created_at`, `created_by`, `size_bytes`, `data_json`) VALUES
(6, 'backup_2025_11_21_19_03_59.sql', '2025-11-22 02:03:59', 1, 97230, 'SET FOREIGN_KEY_CHECKS=0;\nDROP TABLE IF EXISTS `attendance`;\nCREATE TABLE `attendance` (\n  `att_id` int(11) NOT NULL AUTO_INCREMENT,\n  `emp_id` int(11) DEFAULT NULL,\n  `date` date DEFAULT NULL,\n  `time_in` varchar(256) DEFAULT NULL,\n  `time_out` varchar(256) DEFAULT NULL,\n  `hours_worked` decimal(5,2) DEFAULT NULL,\n  `ot_hours` decimal(5,2) DEFAULT NULL,\n  `status` enum(\'active\',\'inactive\') DEFAULT \'active\',\n  PRIMARY KEY (`att_id`),\n  KEY `emp_id` (`emp_id`),\n  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `user_table` (`user_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `audit_table`;\nCREATE TABLE `audit_table` (\n  `audit_id` int(100) NOT NULL AUTO_INCREMENT,\n  `user_id` int(100) NOT NULL,\n  `action` varchar(100) NOT NULL,\n  `timestamp` datetime NOT NULL,\n  `module` varchar(128) DEFAULT NULL,\n  `affected_record` varchar(256) DEFAULT NULL,\n  PRIMARY KEY (`audit_id`),\n  KEY `audit_user_c` (`user_id`),\n  CONSTRAINT `audit_user_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'124\',\'1\',\'update_user\',\'2025-11-21 20:24:17\',\'User Management\',\'user_id=5\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'125\',\'1\',\'update_setting\',\'2025-11-21 20:31:48\',\'Security Controls\',\'setting_name=Minimum Password Length, value=8\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'126\',\'1\',\'update_setting\',\'2025-11-21 20:31:48\',\'Security Controls\',\'setting_name=Require Uppercase Letters, value=1\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'127\',\'1\',\'update_setting\',\'2025-11-21 20:31:48\',\'Security Controls\',\'setting_name=Require Lowercase Letters, value=1\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'128\',\'1\',\'update_setting\',\'2025-11-21 20:31:48\',\'Security Controls\',\'setting_name=Require Numbers, value=1\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'129\',\'1\',\'update_setting\',\'2025-11-21 20:31:49\',\'Security Controls\',\'setting_name=Password Expiry, value=90\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'130\',\'1\',\'update_setting\',\'2025-11-21 20:31:49\',\'Security Controls\',\'setting_name=Require Symbols, value=1\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'131\',\'1\',\'update_setting\',\'2025-11-21 20:31:49\',\'Security Controls\',\'setting_name=Lock Account After, value=5\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'136\',\'1\',\'update_role_permissions\',\'2025-11-21 21:32:29\',\'Role Management\',\'role_id=1, items=9\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'138\',\'1\',\'update_user\',\'2025-11-22 00:47:26\',\'User Management\',\'user_id=5\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'139\',\'1\',\'restore_backup_data\',\'2025-11-22 01:47:14\',\'Backup\',\'restore={\\\"departments\\\":10,\\\"positions\\\":61,\\\"salary_structure\\\":20,\\\"deductions\\\":0,\\\"taxes\\\":0,\\\"benefits\\\":0}\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'140\',\'1\',\'update_user\',\'2025-11-22 01:47:40\',\'User Management\',\'user_id=5\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'141\',\'1\',\'restore_backup_data\',\'2025-11-22 01:47:54\',\'Backup\',\'restore={\\\"departments\\\":10,\\\"positions\\\":61,\\\"salary_structure\\\":20,\\\"deductions\\\":0,\\\"taxes\\\":0,\\\"benefits\\\":0}\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'142\',\'1\',\'restore_backup_data\',\'2025-11-22 01:48:43\',\'Backup\',\'restore={\\\"departments\\\":10,\\\"positions\\\":61,\\\"salary_structure\\\":20,\\\"deductions\\\":0,\\\"taxes\\\":0,\\\"benefits\\\":0}\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'143\',\'1\',\'restore_backup_data\',\'2025-11-22 01:48:52\',\'Backup\',\'restore={\\\"departments\\\":10,\\\"positions\\\":61,\\\"salary_structure\\\":20,\\\"deductions\\\":0,\\\"taxes\\\":0,\\\"benefits\\\":0}\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'144\',\'1\',\'create_user\',\'2025-11-22 01:50:13\',\'User Management\',\'user_id=7, email=restore@gmail.com\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'145\',\'1\',\'restore_backup_data\',\'2025-11-22 01:50:20\',\'Backup\',\'restore={\\\"departments\\\":10,\\\"positions\\\":61,\\\"salary_structure\\\":20,\\\"deductions\\\":0,\\\"taxes\\\":0,\\\"benefits\\\":0}\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'146\',\'1\',\'restore_backup_data\',\'2025-11-22 01:55:54\',\'Backup\',\'restore={\\\"departments\\\":10,\\\"positions\\\":61,\\\"salary_structure\\\":20,\\\"deductions\\\":0,\\\"taxes\\\":0,\\\"benefits\\\":0}\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'147\',\'1\',\'restore_backup_data\',\'2025-11-22 01:56:08\',\'Backup\',\'restore={\\\"departments\\\":10,\\\"positions\\\":61,\\\"salary_structure\\\":20,\\\"deductions\\\":0,\\\"taxes\\\":0,\\\"benefits\\\":0}\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'148\',\'1\',\'update_user\',\'2025-11-22 01:56:27\',\'User Management\',\'user_id=7\');\nINSERT INTO `audit_table` (`audit_id`,`user_id`,`action`,`timestamp`,`module`,`affected_record`) VALUES (\'149\',\'1\',\'restore_backup_data\',\'2025-11-22 01:56:31\',\'Backup\',\'restore={\\\"departments\\\":10,\\\"positions\\\":61,\\\"salary_structure\\\":20,\\\"deductions\\\":0,\\\"taxes\\\":0,\\\"benefits\\\":0}\');\nDROP TABLE IF EXISTS `backup_schedule`;\nCREATE TABLE `backup_schedule` (\n  `schedule_id` int(11) NOT NULL DEFAULT 1,\n  `enabled` tinyint(1) NOT NULL DEFAULT 0,\n  `frequency` enum(\'hourly\',\'daily\',\'weekly\') NOT NULL DEFAULT \'daily\',\n  `run_time` varchar(5) NOT NULL DEFAULT \'22:00\',\n  `next_run` datetime DEFAULT NULL,\n  `last_run` datetime DEFAULT NULL,\n  PRIMARY KEY (`schedule_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `backup_schedule` (`schedule_id`,`enabled`,`frequency`,`run_time`,`next_run`,`last_run`) VALUES (\'1\',\'1\',\'daily\',\'22:00\',\'2025-11-21 22:00:00\',NULL);\nDROP TABLE IF EXISTS `backup_snapshots`;\nCREATE TABLE `backup_snapshots` (\n  `backup_id` int(11) NOT NULL AUTO_INCREMENT,\n  `file_name` varchar(255) NOT NULL,\n  `created_at` datetime NOT NULL,\n  `created_by` int(11) NOT NULL,\n  `size_bytes` int(11) NOT NULL,\n  `data_json` longtext NOT NULL,\n  PRIMARY KEY (`backup_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `benefits`;\nCREATE TABLE `benefits` (\n  `ben_id` int(11) NOT NULL AUTO_INCREMENT,\n  `ben_name` varchar(256) NOT NULL,\n  `type` enum(\'fixed\',\'percentage\',\'variable\') DEFAULT NULL,\n  `eligibility` enum(\'all_employees\',\'full_time_only\',\'as_needed\',\'shift_workers\',\'enrolled_employees\') DEFAULT NULL,\n  `status` enum(\'active\',\'inactive\') DEFAULT \'active\',\n  PRIMARY KEY (`ben_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `bonus_adjustments`;\nCREATE TABLE `bonus_adjustments` (\n  `ba_id` int(11) NOT NULL AUTO_INCREMENT,\n  `emp_id` int(11) DEFAULT NULL,\n  `description` varchar(256) DEFAULT NULL,\n  `type` enum(\'bonus\',\'adjustment\') DEFAULT NULL,\n  `date_from` date DEFAULT NULL,\n  `date_to` date DEFAULT NULL,\n  `amount` int(11) DEFAULT NULL,\n  `status` enum(\'pending\',\'approved\') DEFAULT NULL,\n  `approved_by` int(11) DEFAULT NULL,\n  PRIMARY KEY (`ba_id`),\n  KEY `emp_id` (`emp_id`),\n  KEY `approved_by` (`approved_by`),\n  CONSTRAINT `bonus_adjustments_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `user_table` (`user_id`),\n  CONSTRAINT `bonus_adjustments_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `user_table` (`user_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `deduction_type_defaults`;\nCREATE TABLE `deduction_type_defaults` (\n  `dtd_id` int(11) NOT NULL AUTO_INCREMENT,\n  `deduction_id` int(11) DEFAULT NULL,\n  PRIMARY KEY (`dtd_id`),\n  KEY `deduction_id` (`deduction_id`),\n  CONSTRAINT `deduction_type_defaults_ibfk_1` FOREIGN KEY (`deduction_id`) REFERENCES `deductions` (`deduct_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `deductions`;\nCREATE TABLE `deductions` (\n  `deduct_id` int(11) NOT NULL AUTO_INCREMENT,\n  `deduct_name` varchar(256) DEFAULT NULL,\n  `type` enum(\'percentage\',\'fixed\',\'custom_formula\') DEFAULT NULL,\n  `rate_or_formula` varchar(256) DEFAULT NULL,\n  `minimum` int(11) DEFAULT NULL,\n  `maximum` int(11) DEFAULT NULL,\n  `status` enum(\'active\',\'inactive\') DEFAULT \'active\',\n  PRIMARY KEY (`deduct_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `departments`;\nCREATE TABLE `departments` (\n  `dept_id` int(11) NOT NULL AUTO_INCREMENT,\n  `dept_name` varchar(256) NOT NULL,\n  `head_emp_id` int(11) DEFAULT NULL,\n  `num_of_emps` int(11) DEFAULT NULL,\n  PRIMARY KEY (`dept_id`),\n  KEY `head_emp_id` (`head_emp_id`),\n  CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`head_emp_id`) REFERENCES `user_table` (`user_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'1\',\'Anesthetics Department\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'2\',\'Breast Screening Department\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'3\',\'Cardiology Department\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'4\',\'Ear, Nose & Throat (ENT) Department\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'5\',\'Elderly Services (Geriatrics)\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'6\',\'Gastroenterology Department\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'7\',\'General Surgery Department\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'8\',\'Gynecology Department\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'9\',\'Hematology Department\',NULL,NULL);\nINSERT INTO `departments` (`dept_id`,`dept_name`,`head_emp_id`,`num_of_emps`) VALUES (\'10\',\'Human Resources (HR) Department\',NULL,NULL);\nDROP TABLE IF EXISTS `employees`;\nCREATE TABLE `employees` (\n  `emp_id` int(11) NOT NULL AUTO_INCREMENT,\n  `user_id` int(100) NOT NULL,\n  `dept_id` int(11) DEFAULT NULL,\n  `pos_id` int(11) DEFAULT NULL,\n  `hire_date` date DEFAULT NULL,\n  `employment_type` varchar(64) DEFAULT \'Full-Time\',\n  `status` enum(\'active\',\'inactive\') DEFAULT \'active\',\n  PRIMARY KEY (`emp_id`),\n  KEY `emp_user_fk` (`user_id`),\n  KEY `emp_dept_fk` (`dept_id`),\n  KEY `emp_pos_fk` (`pos_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `employees` (`emp_id`,`user_id`,`dept_id`,`pos_id`,`hire_date`,`employment_type`,`status`) VALUES (\'1\',\'1\',\'10\',\'59\',\'2023-01-01\',\'Full-Time\',\'active\');\nINSERT INTO `employees` (`emp_id`,`user_id`,`dept_id`,`pos_id`,`hire_date`,`employment_type`,`status`) VALUES (\'2\',\'2\',\'10\',\'59\',\'2023-01-01\',\'Full-Time\',\'active\');\nINSERT INTO `employees` (`emp_id`,`user_id`,`dept_id`,`pos_id`,`hire_date`,`employment_type`,`status`) VALUES (\'3\',\'3\',\'10\',\'55\',\'2022-03-10\',\'Full-Time\',\'active\');\nINSERT INTO `employees` (`emp_id`,`user_id`,`dept_id`,`pos_id`,`hire_date`,`employment_type`,`status`) VALUES (\'4\',\'4\',\'3\',\'15\',\'2024-05-01\',\'Full-Time\',\'active\');\nINSERT INTO `employees` (`emp_id`,`user_id`,`dept_id`,`pos_id`,`hire_date`,`employment_type`,`status`) VALUES (\'5\',\'5\',\'6\',\'35\',\'2024-06-15\',\'Full-Time\',\'active\');\nDROP TABLE IF EXISTS `leave_requests`;\nCREATE TABLE `leave_requests` (\n  `leave_id` int(11) NOT NULL AUTO_INCREMENT,\n  `emp_id` int(11) DEFAULT NULL,\n  `leave_type` varchar(256) DEFAULT NULL,\n  `date_from` date DEFAULT NULL,\n  `date_to` date DEFAULT NULL,\n  `days` int(11) DEFAULT NULL,\n  `pay_types` enum(\'paid\',\'unpaid\') DEFAULT NULL,\n  `status` enum(\'pending\',\'approved\') DEFAULT NULL,\n  `approved_by` int(11) DEFAULT NULL,\n  PRIMARY KEY (`leave_id`),\n  KEY `emp_id` (`emp_id`),\n  KEY `approved_by` (`approved_by`),\n  CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `user_table` (`user_id`),\n  CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `user_table` (`user_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `login_attempts`;\nCREATE TABLE `login_attempts` (\n  `attempt_id` int(100) NOT NULL AUTO_INCREMENT,\n  `user_id` int(100) NOT NULL,\n  `attempt_time` datetime NOT NULL,\n  `login_status` enum(\'success\',\'failed\') NOT NULL,\n  PRIMARY KEY (`attempt_id`),\n  KEY `login_attemprt_user_id_c` (`user_id`),\n  CONSTRAINT `login_attemprt_user_id_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'1\',\'1\',\'2025-11-20 23:01:21\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'2\',\'2\',\'2025-11-20 23:15:26\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'3\',\'2\',\'2025-11-20 23:15:28\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'4\',\'3\',\'2025-11-20 23:15:51\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'5\',\'3\',\'2025-11-20 23:15:52\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'6\',\'4\',\'2025-11-20 23:16:08\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'7\',\'4\',\'2025-11-20 23:16:10\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'8\',\'1\',\'2025-11-20 23:17:18\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'9\',\'1\',\'2025-11-20 23:17:20\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'10\',\'2\',\'2025-11-20 23:20:16\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'11\',\'2\',\'2025-11-20 23:20:18\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'12\',\'1\',\'2025-11-20 23:20:38\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'13\',\'1\',\'2025-11-20 23:20:40\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'14\',\'2\',\'2025-11-20 23:44:58\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'15\',\'2\',\'2025-11-20 23:45:00\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'16\',\'1\',\'2025-11-20 23:45:42\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'17\',\'1\',\'2025-11-20 23:45:44\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'18\',\'1\',\'2025-11-21 01:01:39\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'19\',\'1\',\'2025-11-21 01:01:41\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'20\',\'2\',\'2025-11-21 01:29:36\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'21\',\'2\',\'2025-11-21 01:29:38\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'22\',\'1\',\'2025-11-21 01:30:02\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'23\',\'1\',\'2025-11-21 01:30:03\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'24\',\'4\',\'2025-11-21 01:30:20\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'25\',\'4\',\'2025-11-21 01:30:23\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'26\',\'1\',\'2025-11-21 01:30:56\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'27\',\'1\',\'2025-11-21 01:30:58\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'28\',\'4\',\'2025-11-21 01:32:03\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'29\',\'4\',\'2025-11-21 01:32:05\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'30\',\'1\',\'2025-11-21 01:33:24\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'31\',\'1\',\'2025-11-21 01:33:26\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'32\',\'2\',\'2025-11-21 02:39:16\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'33\',\'2\',\'2025-11-21 02:39:18\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'34\',\'3\',\'2025-11-21 02:43:45\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'35\',\'3\',\'2025-11-21 02:43:46\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'36\',\'3\',\'2025-11-21 02:43:47\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'37\',\'3\',\'2025-11-21 02:43:47\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'38\',\'4\',\'2025-11-21 02:44:13\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'39\',\'4\',\'2025-11-21 02:44:15\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'40\',\'2\',\'2025-11-21 02:45:58\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'41\',\'2\',\'2025-11-21 02:45:58\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'42\',\'2\',\'2025-11-21 02:45:59\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'43\',\'2\',\'2025-11-21 02:46:03\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'44\',\'2\',\'2025-11-21 02:46:05\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'45\',\'2\',\'2025-11-21 02:46:07\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'46\',\'4\',\'2025-11-21 02:46:57\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'47\',\'4\',\'2025-11-21 02:46:59\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'48\',\'2\',\'2025-11-21 02:58:50\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'49\',\'2\',\'2025-11-21 02:58:52\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'50\',\'2\',\'2025-11-21 03:13:16\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'51\',\'2\',\'2025-11-21 03:13:18\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'52\',\'2\',\'2025-11-21 03:23:07\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'53\',\'2\',\'2025-11-21 03:23:09\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'54\',\'1\',\'2025-11-21 03:23:43\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'55\',\'1\',\'2025-11-21 03:23:43\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'56\',\'1\',\'2025-11-21 03:23:44\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'57\',\'1\',\'2025-11-21 03:23:48\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'58\',\'1\',\'2025-11-21 03:23:51\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'59\',\'1\',\'2025-11-21 03:23:53\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'60\',\'2\',\'2025-11-21 03:37:31\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'61\',\'2\',\'2025-11-21 03:37:33\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'62\',\'2\',\'2025-11-21 03:37:50\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'63\',\'2\',\'2025-11-21 03:38:13\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'64\',\'2\',\'2025-11-21 03:38:24\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'65\',\'2\',\'2025-11-21 03:38:30\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'66\',\'2\',\'2025-11-21 03:38:31\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'67\',\'2\',\'2025-11-21 03:38:33\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'68\',\'3\',\'2025-11-21 03:39:09\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'69\',\'3\',\'2025-11-21 03:39:11\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'70\',\'1\',\'2025-11-21 03:39:38\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'71\',\'1\',\'2025-11-21 03:39:40\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'72\',\'4\',\'2025-11-21 03:40:09\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'73\',\'4\',\'2025-11-21 03:40:11\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'74\',\'1\',\'2025-11-21 03:46:30\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'75\',\'1\',\'2025-11-21 03:46:32\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'76\',\'2\',\'2025-11-21 03:46:47\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'77\',\'2\',\'2025-11-21 03:46:49\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'78\',\'4\',\'2025-11-21 03:47:44\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'79\',\'4\',\'2025-11-21 03:47:45\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'80\',\'3\',\'2025-11-21 03:48:09\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'81\',\'3\',\'2025-11-21 03:48:11\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'82\',\'2\',\'2025-11-21 03:48:48\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'83\',\'2\',\'2025-11-21 03:48:50\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'84\',\'1\',\'2025-11-21 03:49:37\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'85\',\'1\',\'2025-11-21 03:49:39\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'86\',\'2\',\'2025-11-21 20:15:10\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'87\',\'1\',\'2025-11-21 20:15:36\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'88\',\'3\',\'2025-11-21 20:46:39\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'89\',\'1\',\'2025-11-21 21:32:12\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'90\',\'1\',\'2025-11-21 21:42:18\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'91\',\'1\',\'2025-11-21 22:48:04\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'92\',\'3\',\'2025-11-21 23:45:06\',\'success\');\nINSERT INTO `login_attempts` (`attempt_id`,`user_id`,`attempt_time`,`login_status`) VALUES (\'93\',\'1\',\'2025-11-22 00:24:57\',\'success\');\nDROP TABLE IF EXISTS `overtime_requests`;\nCREATE TABLE `overtime_requests` (\n  `overtime_id` int(11) NOT NULL AUTO_INCREMENT,\n  `emp_id` int(11) DEFAULT NULL,\n  `date_from` date DEFAULT NULL,\n  `date_to` date DEFAULT NULL,\n  `hours` int(11) DEFAULT NULL,\n  `rate` float DEFAULT NULL,\n  `computed_amount` int(11) DEFAULT NULL,\n  `status` enum(\'pending\',\'approved\') DEFAULT NULL,\n  `approved_by` int(11) DEFAULT NULL,\n  PRIMARY KEY (`overtime_id`),\n  KEY `emp_id` (`emp_id`),\n  KEY `approved_by` (`approved_by`),\n  CONSTRAINT `overtime_requests_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `user_table` (`user_id`),\n  CONSTRAINT `overtime_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `user_table` (`user_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `pages_catalog`;\nCREATE TABLE `pages_catalog` (\n  `page_key` varchar(128) NOT NULL,\n  `display_name` varchar(128) NOT NULL,\n  `page_link` varchar(128) NOT NULL,\n  `group_name` varchar(64) NOT NULL,\n  PRIMARY KEY (`page_key`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_audit_logs\',\'Audit Logs\',\'admin_audit_logs.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_company_settings\',\'Company Settings\',\'admin_company_settings.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_dashboard\',\'Admin Dashboard\',\'admin_dashboard.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_data_backup_and_restore\',\'Data Backup & Restore\',\'admin_data_backup_and_restore.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_role_management\',\'Role Management\',\'admin_role_management.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_security_controls\',\'Security Controls\',\'admin_security_controls.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_summary_report\',\'Summary Reports\',\'admin_summary_report.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_system_configuration\',\'System Configuration\',\'admin_system_configuration.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'admin_user_management\',\'User Management\',\'admin_user_management.php\',\'Admin\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'employee_attendance_log\',\'Attendance Log\',\'Employee_Attendance_Log.php\',\'Employee\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'employee_dashboard\',\'Employee Dashboard\',\'employee_dashboard.php\',\'Employee\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'employee_leave_overtime\',\'Leave and Overtime\',\'Employee_Leave_Overtime.php\',\'Employee\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'employee_payroll_history\',\'Payroll History\',\'employee_payroll_history.php\',\'Employee\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'employee_profile\',\'Profile\',\'Employee_Profile.php\',\'Employee\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'manager_approval_page\',\'Approval\',\'manager_approval_page.php\',\'Manager\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'manager_dashboard\',\'Manager Dashboard\',\'manager_dashboard.php\',\'Manager\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'manager_employee_list\',\'Employee List\',\'manager_employee_list.php\',\'Manager\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'manager_endorsement\',\'Endorsement\',\'manager_endorsement.php\',\'Manager\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'manager_payroll_summary_generation\',\'Payroll Summary Generation\',\'manager_payroll_summary_generation.php\',\'Manager\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'manager_report_generation\',\'Report Generation\',\'manager_report_generation.php\',\'Manager\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_attendance_management\',\'Attendance Management\',\'payroll_officer_attendance_management.php\',\'Payroll Officer\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_dashboard\',\'Payroll Officer Dashboard\',\'payroll_officer_dashboard.php\',\'Payroll Officer\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_employee_management\',\'Employee Management\',\'payroll_officer_employee_management.php\',\'Payroll Officer\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_government_remittance\',\'Government Remittance\',\'payroll_officer_government_remittance.php\',\'Payroll Officer\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_ot_leave_bonus\',\'OT/Leave/Bonus\',\'payroll_officer_ot_leave_bonus.php\',\'Payroll Officer\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_payroll_computation\',\'Payroll Computation\',\'payroll_officer_payroll_computation.php\',\'Payroll Officer\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_payroll_history\',\'Payroll History\',\'payroll_officer_payroll_history.php\',\'Payroll Officer\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_payslip_generation\',\'Payslip Generation\',\'payroll_officer_payslip_generation.php\',\'Payroll Officer\');\nINSERT INTO `pages_catalog` (`page_key`,`display_name`,`page_link`,`group_name`) VALUES (\'payroll_officer_period_management\',\'Period Management\',\'payroll_officer_period_management.php\',\'Payroll Officer\');\nDROP TABLE IF EXISTS `password_reset`;\nCREATE TABLE `password_reset` (\n  `reset_id` int(100) NOT NULL AUTO_INCREMENT,\n  `user_id` int(100) NOT NULL,\n  `session_id` varchar(256) NOT NULL,\n  `created_at` datetime NOT NULL,\n  `expires_at` datetime NOT NULL,\n  `status` enum(\'pending\',\'used\',\'expired\') NOT NULL,\n  PRIMARY KEY (`reset_id`),\n  KEY `password_test_user_id_c` (`user_id`),\n  CONSTRAINT `password_test_user_id_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `payroll`;\nCREATE TABLE `payroll` (\n  `payroll_id` int(11) NOT NULL AUTO_INCREMENT,\n  `emp_id` int(11) DEFAULT NULL,\n  `dept_id` int(11) DEFAULT NULL,\n  `pos_id` int(11) DEFAULT NULL,\n  `basic_pay` int(11) DEFAULT NULL,\n  `days_worked` int(11) DEFAULT NULL,\n  `ot_hours` int(11) DEFAULT NULL,\n  `ot_pay` int(11) DEFAULT NULL,\n  `allowances` int(11) DEFAULT NULL,\n  `gross_pay` int(11) DEFAULT NULL,\n  `late_absent_deductions` int(11) DEFAULT NULL,\n  `sss` int(11) DEFAULT NULL,\n  `philhealth` int(11) DEFAULT NULL,\n  `pag_ibig` int(11) DEFAULT NULL,\n  `tax` int(11) DEFAULT NULL,\n  `other_deductions` int(11) DEFAULT NULL,\n  `total_deduction` int(11) DEFAULT NULL,\n  `net_pay` int(11) DEFAULT NULL,\n  `payroll_status` enum(\'pending\',\'approved\',\'locked\') DEFAULT \'pending\',\n  `payroll_period_id` int(11) DEFAULT NULL,\n  PRIMARY KEY (`payroll_id`),\n  KEY `emp_id` (`emp_id`),\n  KEY `dept_id` (`dept_id`),\n  KEY `pos_id` (`pos_id`),\n  KEY `payroll_period_id` (`payroll_period_id`),\n  CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `user_table` (`user_id`),\n  CONSTRAINT `payroll_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`),\n  CONSTRAINT `payroll_ibfk_3` FOREIGN KEY (`pos_id`) REFERENCES `positions` (`pos_id`),\n  CONSTRAINT `payroll_ibfk_4` FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_period` (`period_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `payroll_period`;\nCREATE TABLE `payroll_period` (\n  `period_id` int(11) NOT NULL AUTO_INCREMENT,\n  `start_date` date DEFAULT NULL,\n  `end_date` date DEFAULT NULL,\n  `status` enum(\'open\',\'processing\',\'locked\',\'archived\') DEFAULT \'open\',\n  PRIMARY KEY (`period_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'1\',\'2025-01-01\',\'2025-01-15\',\'archived\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'2\',\'2025-01-16\',\'2025-01-31\',\'archived\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'3\',\'2025-02-01\',\'2025-02-15\',\'archived\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'4\',\'2025-02-16\',\'2025-02-28\',\'archived\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'5\',\'2025-03-01\',\'2025-03-15\',\'locked\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'6\',\'2025-03-16\',\'2025-03-31\',\'locked\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'7\',\'2025-04-01\',\'2025-04-15\',\'processing\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'8\',\'2025-04-16\',\'2025-04-30\',\'processing\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'9\',\'2025-05-01\',\'2025-05-15\',\'open\');\nINSERT INTO `payroll_period` (`period_id`,`start_date`,`end_date`,`status`) VALUES (\'10\',\'2025-05-16\',\'2025-05-31\',\'open\');\nDROP TABLE IF EXISTS `positions`;\nCREATE TABLE `positions` (\n  `pos_id` int(11) NOT NULL AUTO_INCREMENT,\n  `pos_name` varchar(256) NOT NULL,\n  `dept_id` int(11) DEFAULT NULL,\n  `sg_grade` int(11) DEFAULT NULL,\n  PRIMARY KEY (`pos_id`),\n  KEY `dept_id` (`dept_id`),\n  KEY `salary_grade_ibfk_1` (`sg_grade`),\n  CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`),\n  CONSTRAINT `salary_grade_ibfk_1` FOREIGN KEY (`sg_grade`) REFERENCES `salary_grades` (`salary_grade`) ON UPDATE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'1\',\'Consultant Anesthesiologist\',\'1\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'2\',\'Anesthesiology Resident / Registrar\',\'1\',\'1\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'3\',\'Nurse Anesthetist\',\'1\',\'17\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'4\',\'Anesthetic Technician\',\'1\',\'12\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'5\',\'Recovery Room Nurse (PACU Nurse)\',\'1\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'6\',\'Operating Room Assistant\',\'1\',\'8\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'7\',\'Consultant Radiologist\',\'2\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'8\',\'Mammography Technologist\',\'2\',\'14\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'9\',\'Breast Care Nurse\',\'2\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'10\',\'Radiology Assistant\',\'2\',\'10\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'11\',\'Screening Coordinator\',\'2\',\'12\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'12\',\'Medical Secretary (Breast Clinic)\',\'2\',\'9\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'13\',\'Cardiologist / Consultant Cardiologist\',\'3\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'14\',\'Cardiology Fellow / Resident\',\'3\',\'20\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'15\',\'Cardiac Nurse\',\'3\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'16\',\'ECG / ECHO Technician\',\'3\',\'11\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'17\',\'Cardiac Catheterization Lab Technologist\',\'3\',\'14\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'18\',\'Cardiac Rehabilitation Specialist\',\'3\',\'18\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'19\',\'ENT Consultant / Surgeon\',\'4\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'20\',\'ENT Resident / Registrar\',\'4\',\'20\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'21\',\'Audiologist\',\'4\',\'16\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'22\',\'Speech and Language Therapist\',\'4\',\'16\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'23\',\'ENT Nurse\',\'4\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'24\',\'ENT Clinic Assistant\',\'4\',\'8\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'25\',\'Geriatrician (Consultant in Elderly Medicine)\',\'5\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'26\',\'Geriatric Nurse / Nurse Practitioner\',\'5\',\'17\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'27\',\'Physiotherapist\',\'5\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'28\',\'Occupational Therapist\',\'5\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'29\',\'Social Worker\',\'5\',\'13\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'30\',\'Healthcare Assistant / Caregiver\',\'5\',\'6\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'31\',\'Gastroenterologist / Consultant Physician\',\'6\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'32\',\'Gastroenterology Fellow / Resident\',\'6\',\'20\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'33\',\'Endoscopy Nurse\',\'6\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'34\',\'Endoscopy Technician\',\'6\',\'12\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'35\',\'Nutritionist / Dietitian\',\'6\',\'13\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'36\',\'Medical Secretary (Gastro Department)\',\'6\',\'9\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'37\',\'General Surgeon / Consultant Surgeon\',\'7\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'38\',\'Surgical Resident / Registrar\',\'7\',\'20\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'39\',\'Scrub Nurse\',\'7\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'40\',\'Operating Room Nurse\',\'7\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'41\',\'Surgical Technician\',\'7\',\'12\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'42\',\'Ward Nurse (Post-op Care)\',\'7\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'43\',\'OB-GYN Consultant / Gynecologist\',\'8\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'44\',\'Midwife\',\'8\',\'11\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'45\',\'Gynecology Nurse\',\'8\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'46\',\'Ultrasound Technologist\',\'8\',\'14\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'47\',\'OB-GYN Resident / Registrar\',\'8\',\'20\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'48\',\'Medical Secretary (OB-GYN Unit)\',\'8\',\'9\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'49\',\'Hematologist / Consultant\',\'9\',\'26\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'50\',\'Hematology Resident / Fellow\',\'9\',\'20\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'51\',\'Medical Laboratory Scientist (Hematology)\',\'9\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'52\',\'Phlebotomist\',\'9\',\'8\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'53\',\'Oncology Nurse (Hematology Unit)\',\'9\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'54\',\'Transfusion Specialist\',\'9\',\'16\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'55\',\'HR Director / HR Manager\',\'10\',\'24\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'56\',\'HR Officer / HR Assistant\',\'10\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'57\',\'Recruitment Specialist\',\'10\',\'14\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'58\',\'Training and Development Coordinator\',\'10\',\'16\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'59\',\'Payroll Officer\',\'10\',\'14\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'60\',\'Employee Relations Officer\',\'10\',\'15\');\nINSERT INTO `positions` (`pos_id`,`pos_name`,`dept_id`,`sg_grade`) VALUES (\'61\',\'HR Clerk / Admin Staff\',\'10\',\'9\');\nDROP TABLE IF EXISTS `remember_sessions`;\nCREATE TABLE `remember_sessions` (\n  `r_session_id` varchar(256) NOT NULL,\n  `user_id` int(100) NOT NULL,\n  `login_time` datetime NOT NULL,\n  `expiry_date` datetime NOT NULL,\n  `r_session_status` enum(\'active\',\'ended\') NOT NULL,\n  PRIMARY KEY (`r_session_id`),\n  KEY `remember_user_id_constraint` (`user_id`),\n  CONSTRAINT `remember_user_id_constraint` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'01f5d9355c765bcaeb9860cd9f2aa4f4b6369093d42cc8e1a24c18745865205d\',\'1\',\'2025-11-21 22:48:04\',\'2025-12-21 15:48:04\',\'active\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'0800a485537ef248270a23da37a3e495336404077f47af50e1f4e41cf6743ab2\',\'2\',\'2025-11-21 01:29:36\',\'2025-12-20 18:29:36\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'0c9ad0a796e3bf2a26e1af5313cc9177df046fadb639e0c53e1b3945611aae5a\',\'4\',\'2025-11-20 23:16:08\',\'2025-12-20 16:16:08\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'0cec349f7045803d2286fa11930f6a8e46b2e25466d33870407638bd0e7b47fe\',\'1\',\'2025-11-21 03:23:43\',\'2025-12-20 20:23:43\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'1255e7ffe588e392c9d4a56b1441345b9302e3763228865e282c5daa2c185a57\',\'1\',\'2025-11-21 01:30:02\',\'2025-12-20 18:30:02\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'1322e520d776289dfadc59eee367dc2fb9567234cbfaea8212b751fc1f5b754a\',\'1\',\'2025-11-21 20:15:36\',\'2025-12-21 13:15:36\',\'active\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'195ad7f658b99972dbedf874b1ea3a2d0740ce617fafec91a07cbf912476878a\',\'2\',\'2025-11-21 02:45:58\',\'2025-12-20 19:45:58\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'1b69014714a68ecfa4d5b9bed6dbb4a7c5887c59ca998690fe159e9b29054281\',\'2\',\'2025-11-21 02:58:50\',\'2025-12-20 19:58:50\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'24de378eb8567d7f9205af8fff74ce57ff3a16bdd22875072b118b12034627e7\',\'1\',\'2025-11-22 00:24:57\',\'2025-12-21 17:24:57\',\'active\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'250f0821c0bca37f7b8945d15d6a169ec8b2874f0daf2217dc6e99b1f9ce1813\',\'2\',\'2025-11-21 03:46:47\',\'2025-12-20 20:46:47\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'2636cbb678c262864570746b6b2a91707018e026bbc6158e5c87db6d2eaad981\',\'3\',\'2025-11-21 03:39:09\',\'2025-12-20 20:39:09\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'2e1567237c2b3240e4270a4d36c08ccf55933739d0d46e53e67d95a511f771ae\',\'1\',\'2025-11-21 21:32:12\',\'2025-12-21 14:32:12\',\'active\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'3067fdac6a55b248afb150a7a953f50a46d7a4d9277875ab865a9e813ac5bb7a\',\'1\',\'2025-11-21 21:42:18\',\'2025-12-21 14:42:18\',\'active\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'36add3c23e4e2ea4de94646f3378c24dac611184003459cb35b9ca6b0e8169df\',\'1\',\'2025-11-20 23:17:18\',\'2025-12-20 16:17:18\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'3a824e94ddc992a2915bb84347ce583850c30f2c6e78c89123463b79bea35a0d\',\'2\',\'2025-11-20 23:44:58\',\'2025-12-20 16:44:58\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'3dc4971dd68bae58e2292a740395c9a1ea8b96b104c731eedec41b039a628bc8\',\'4\',\'2025-11-21 03:40:09\',\'2025-12-20 20:40:09\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'40e06d6b90cb5d400dd2ef52d6c106a9815055207e8a30444b936a4cf87e7524\',\'2\',\'2025-11-21 03:37:31\',\'2025-12-20 20:37:31\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'42c1b61474a8c35a9ca21c42099dc4377fc3a369a8e69066d709f3203b1c9133\',\'1\',\'2025-11-21 03:39:38\',\'2025-12-20 20:39:38\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'47bf3ab08202aed387966cce3221e21ddded55e7a9f375037260999ae5391299\',\'1\',\'2025-11-21 03:46:30\',\'2025-12-20 20:46:30\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'47c610f354ef81f159ae96b584db2e0f463dd777ee2df17f77a651dc88d631f5\',\'2\',\'2025-11-21 03:48:48\',\'2025-12-20 20:48:48\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'4838bb57b709dad39bbf9b0c0356e202078373d734793a0226fcae5d7dea821c\',\'4\',\'2025-11-21 02:46:57\',\'2025-12-20 19:46:57\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'4b07084ecb0b7f8260550ad738e469c2a359bc73c823e8e056fa75993b47fd02\',\'3\',\'2025-11-20 23:15:51\',\'2025-12-20 16:15:51\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'4c27307f096b453bc469ad08620f7c456a7073f487d084337b2a62ee9cb8b56b\',\'3\',\'2025-11-21 20:46:39\',\'2025-12-21 13:46:39\',\'active\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'4c8102d41889f63622d6bac0bb26e4cc24c349e0a26d7df135831b8cd99a8228\',\'1\',\'2025-11-21 01:01:39\',\'2025-12-20 18:01:39\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'5182d2316bc0e7e040ffe73eb78a9d79f5b62862a0c8eac6b7e8c40a30bc4a26\',\'4\',\'2025-11-21 03:47:44\',\'2025-12-20 20:47:44\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'5463d8ec8cd91c3bc39429ddf078de10157af76d2334850d0274c72b5508e9e3\',\'1\',\'2025-11-20 23:45:42\',\'2025-12-20 16:45:42\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'65a7614f3a2ffa2b2926e9b02e2761a8ac5e5150bc7676ed89efd64e83b4787e\',\'1\',\'2025-11-20 23:20:38\',\'2025-12-20 16:20:38\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'684472ff9e6f1279a92a46ee09e5b0d37b6529b7096315af4be1f2bebb65f3cf\',\'2\',\'2025-11-21 02:39:16\',\'2025-12-20 19:39:16\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'6f11fe4e356a538ec3aa80afa35972c155cb8230b0ab11f39ba54c5ccec7cd3a\',\'2\',\'2025-11-21 03:13:16\',\'2025-12-20 20:13:16\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'6f19b883b755102d7b9e587dd6832939a30aa8b83f6130d0634dd0ae2d128b3e\',\'3\',\'2025-11-21 02:43:45\',\'2025-12-20 19:43:45\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'739b8fd1aff74acc0aa8575f73184a5c427ff8be0dd9f76fad4a733a2e4c42a5\',\'1\',\'2025-11-21 01:30:56\',\'2025-12-20 18:30:56\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'755b42da1dc97f4b966aaecd2ffd11f6b237daccc52a575e53afb9896faf1872\',\'2\',\'2025-11-21 20:15:10\',\'2025-12-21 13:15:10\',\'active\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'826d48e46b25899af23dd76a3f9d9422820d7eed20b548b5fbb38e1053dae815\',\'4\',\'2025-11-21 02:44:13\',\'2025-12-20 19:44:13\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'874be41b6ca62aa53dabace0dfb0de77d37253a13f1f204d3d6bc420c1e95465\',\'2\',\'2025-11-20 23:20:16\',\'2025-12-20 16:20:16\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'8cbdbdb2eeac01f506065e8cd58986d4d9f5537283d2f28448e35118ece82cce\',\'1\',\'2025-11-21 03:49:37\',\'2025-12-20 20:49:37\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'912d1d31aea53c45d00cb09f6f4ad89a10f4b7c88784c44af14826ace1dd1f48\',\'3\',\'2025-11-21 03:48:09\',\'2025-12-20 20:48:09\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'91da9da6fc13158f833e8f18477b3c4b5913ce9980706894b0a5d9fe2217ac34\',\'3\',\'2025-11-21 23:45:06\',\'2025-12-21 16:45:06\',\'active\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'91e4144de98cb0cfd0675228a94d608ace0e424c00fc96ed9b8134fa1f9ee7e0\',\'2\',\'2025-11-21 03:23:07\',\'2025-12-20 20:23:07\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'b0f07e93cd775f53dfb219f0a08886de2a4a0b6aee4dd73674654b69be0a7491\',\'1\',\'2025-11-21 01:33:24\',\'2025-12-20 18:33:24\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'dccd2e238ce6393d155d0bcca5bccdcac9083ad4c12c72eaea6a20b98949f184\',\'2\',\'2025-11-20 23:15:26\',\'2025-12-20 16:15:26\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'e5508d76b1f172b03e1b27dcbbeec31dc98725c8d5bdc8dd3a25f6d7b584aa16\',\'4\',\'2025-11-21 01:32:03\',\'2025-12-20 18:32:03\',\'ended\');\nINSERT INTO `remember_sessions` (`r_session_id`,`user_id`,`login_time`,`expiry_date`,`r_session_status`) VALUES (\'f274cdf1957bc73b1836bc93f92078cc099c7da6b2d5ac6e892f4d805ef2691a\',\'4\',\'2025-11-21 01:30:21\',\'2025-12-20 18:30:21\',\'ended\');\nDROP TABLE IF EXISTS `role_page_permissions`;\nCREATE TABLE `role_page_permissions` (\n  `role_id` int(11) NOT NULL,\n  `page_key` varchar(128) NOT NULL,\n  `can_access` tinyint(1) NOT NULL DEFAULT 0,\n  PRIMARY KEY (`role_id`,`page_key`),\n  KEY `rpp_page_fk` (`page_key`),\n  CONSTRAINT `rpp_page_fk` FOREIGN KEY (`page_key`) REFERENCES `pages_catalog` (`page_key`) ON DELETE CASCADE ON UPDATE CASCADE,\n  CONSTRAINT `rpp_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles_table` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_audit_logs\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_company_settings\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_dashboard\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_data_backup_and_restore\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_role_management\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_security_controls\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_summary_report\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_system_configuration\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'admin_user_management\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'employee_attendance_log\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'employee_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'employee_leave_overtime\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'employee_payroll_history\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'employee_profile\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'manager_approval_page\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'manager_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'manager_employee_list\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'manager_endorsement\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'manager_payroll_summary_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'manager_report_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_attendance_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_employee_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_government_remittance\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_ot_leave_bonus\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_payroll_computation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_payroll_history\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_payslip_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'1\',\'payroll_officer_period_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_audit_logs\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_company_settings\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_data_backup_and_restore\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_role_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_security_controls\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_summary_report\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_system_configuration\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'admin_user_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'employee_attendance_log\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'employee_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'employee_leave_overtime\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'employee_payroll_history\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'employee_profile\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'manager_approval_page\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'manager_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'manager_employee_list\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'manager_endorsement\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'manager_payroll_summary_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'manager_report_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_attendance_management\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_dashboard\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_employee_management\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_government_remittance\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_ot_leave_bonus\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_payroll_computation\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_payroll_history\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_payslip_generation\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'2\',\'payroll_officer_period_management\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_audit_logs\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_company_settings\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_data_backup_and_restore\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_role_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_security_controls\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_summary_report\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_system_configuration\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'admin_user_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'employee_attendance_log\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'employee_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'employee_leave_overtime\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'employee_payroll_history\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'employee_profile\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'manager_approval_page\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'manager_dashboard\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'manager_employee_list\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'manager_endorsement\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'manager_payroll_summary_generation\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'manager_report_generation\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_attendance_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_employee_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_government_remittance\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_ot_leave_bonus\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_payroll_computation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_payroll_history\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_payslip_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'3\',\'payroll_officer_period_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_audit_logs\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_company_settings\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_data_backup_and_restore\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_role_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_security_controls\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_summary_report\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_system_configuration\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'admin_user_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'employee_attendance_log\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'employee_dashboard\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'employee_leave_overtime\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'employee_payroll_history\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'employee_profile\',\'1\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'manager_approval_page\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'manager_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'manager_employee_list\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'manager_endorsement\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'manager_payroll_summary_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'manager_report_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_attendance_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_dashboard\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_employee_management\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_government_remittance\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_ot_leave_bonus\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_payroll_computation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_payroll_history\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_payslip_generation\',\'0\');\nINSERT INTO `role_page_permissions` (`role_id`,`page_key`,`can_access`) VALUES (\'4\',\'payroll_officer_period_management\',\'0\');\nDROP TABLE IF EXISTS `roles_table`;\nCREATE TABLE `roles_table` (\n  `role_id` int(100) NOT NULL AUTO_INCREMENT,\n  `role_name` varchar(255) NOT NULL,\n  PRIMARY KEY (`role_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `roles_table` (`role_id`,`role_name`) VALUES (\'1\',\'Admin\');\nINSERT INTO `roles_table` (`role_id`,`role_name`) VALUES (\'2\',\'Payroll Officer\');\nINSERT INTO `roles_table` (`role_id`,`role_name`) VALUES (\'3\',\'Manager\');\nINSERT INTO `roles_table` (`role_id`,`role_name`) VALUES (\'4\',\'Employee\');\nDROP TABLE IF EXISTS `salary_benefits`;\nCREATE TABLE `salary_benefits` (\n  `sal_ben_id` int(11) NOT NULL AUTO_INCREMENT,\n  `sal_struct_id` int(11) DEFAULT NULL,\n  `ben_id` int(11) DEFAULT NULL,\n  PRIMARY KEY (`sal_ben_id`),\n  KEY `sal_struct_id` (`sal_struct_id`),\n  KEY `ben_id` (`ben_id`),\n  CONSTRAINT `salary_benefits_ibfk_1` FOREIGN KEY (`sal_struct_id`) REFERENCES `salary_structure` (`sal_struct_id`),\n  CONSTRAINT `salary_benefits_ibfk_2` FOREIGN KEY (`ben_id`) REFERENCES `benefits` (`ben_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `salary_grades`;\nCREATE TABLE `salary_grades` (\n  `salary_grade` int(11) NOT NULL,\n  `step_1` int(11) DEFAULT NULL,\n  `step_2` int(11) DEFAULT NULL,\n  `step_3` int(11) DEFAULT NULL,\n  `step_4` int(11) DEFAULT NULL,\n  `step_5` int(11) DEFAULT NULL,\n  `step_6` int(11) DEFAULT NULL,\n  `step_7` int(11) DEFAULT NULL,\n  `step_8` int(11) DEFAULT NULL,\n  PRIMARY KEY (`salary_grade`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'1\',\'14061\',\'14164\',\'14278\',\'14393\',\'14509\',\'14626\',\'14743\',\'14862\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'2\',\'14925\',\'15035\',\'15146\',\'15258\',\'15371\',\'15484\',\'15599\',\'15714\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'3\',\'15852\',\'15971\',\'16088\',\'16208\',\'16329\',\'16448\',\'16571\',\'16693\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'4\',\'16833\',\'16958\',\'17084\',\'17209\',\'17337\',\'17464\',\'17594\',\'17724\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'5\',\'17866\',\'18000\',\'18133\',\'18267\',\'18401\',\'18538\',\'18676\',\'18813\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'6\',\'18957\',\'19098\',\'19239\',\'19383\',\'19526\',\'19670\',\'19816\',\'19963\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'7\',\'20110\',\'20258\',\'20409\',\'20560\',\'20711\',\'20865\',\'21019\',\'21175\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'8\',\'21448\',\'21642\',\'21839\',\'22035\',\'22234\',\'22435\',\'22638\',\'22843\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'9\',\'23226\',\'23411\',\'23599\',\'23788\',\'23978\',\'24170\',\'24364\',\'24558\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'10\',\'25586\',\'25790\',\'25996\',\'26203\',\'26412\',\'26623\',\'26835\',\'27050\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'11\',\'30024\',\'30308\',\'30597\',\'30889\',\'31185\',\'31486\',\'31790\',\'32099\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'12\',\'32245\',\'32529\',\'32817\',\'33108\',\'33403\',\'33702\',\'34044\',\'34310\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'13\',\'34421\',\'34733\',\'35049\',\'35369\',\'35694\',\'36022\',\'36354\',\'36691\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'14\',\'37024\',\'37384\',\'37749\',\'38118\',\'38491\',\'38869\',\'39252\',\'39640\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'15\',\'40208\',\'40604\',\'41006\',\'41413\',\'41824\',\'42241\',\'42662\',\'43090\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'16\',\'43560\',\'43996\',\'44438\',\'44885\',\'45338\',\'45796\',\'46261\',\'46730\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'17\',\'47247\',\'47727\',\'48213\',\'48705\',\'49203\',\'49708\',\'50218\',\'50735\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'18\',\'51304\',\'51832\',\'52367\',\'52907\',\'53456\',\'54010\',\'54572\',\'55140\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'19\',\'56390\',\'57165\',\'57953\',\'58753\',\'59567\',\'60394\',\'61235\',\'62089\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'20\',\'62967\',\'63842\',\'64732\',\'65637\',\'66557\',\'67479\',\'68409\',\'69342\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'21\',\'70103\',\'71000\',\'72004\',\'73024\',\'74061\',\'75115\',\'76151\',\'77239\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'22\',\'78162\',\'79277\',\'80411\',\'81564\',\'82735\',\'83887\',\'85096\',\'86342\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'23\',\'87315\',\'88574\',\'89855\',\'91163\',\'92592\',\'94043\',\'95518\',\'96955\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'24\',\'98185\',\'99721\',\'101283\',\'102871\',\'104483\',\'106123\',\'107739\',\'109431\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'25\',\'111727\',\'113476\',\'115254\',\'117062\',\'118899\',\'120766\',\'122664\',\'124591\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'26\',\'126252\',\'128228\',\'130238\',\'132280\',\'134356\',\'136465\',\'138608\',\'140788\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'27\',\'142663\',\'144897\',\'147169\',\'149407\',\'151752\',\'153850\',\'156267\',\'158723\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'28\',\'160469\',\'162988\',\'165548\',\'167994\',\'170634\',\'173320\',\'175803\',\'178572\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'29\',\'180492\',\'183332\',\'186218\',\'189151\',\'192131\',\'194797\',\'197870\',\'200993\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'30\',\'203200\',\'206401\',\'209558\',\'212766\',\'216022\',\'219434\',\'222797\',\'226319\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'31\',\'293191\',\'298773\',\'304464\',\'310119\',\'315883\',\'321846\',\'327895\',\'334059\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'32\',\'347888\',\'354743\',\'361736\',\'368694\',\'375969\',\'383391\',\'390963\',\'398686\');\nINSERT INTO `salary_grades` (`salary_grade`,`step_1`,`step_2`,`step_3`,`step_4`,`step_5`,`step_6`,`step_7`,`step_8`) VALUES (\'33\',\'438844\',\'451713\',NULL,NULL,NULL,NULL,NULL,NULL);\nDROP TABLE IF EXISTS `salary_structure`;\nCREATE TABLE `salary_structure` (\n  `sal_struct_id` int(11) NOT NULL AUTO_INCREMENT,\n  `pos_id` int(11) DEFAULT NULL,\n  `basic_pay` int(11) DEFAULT NULL,\n  PRIMARY KEY (`sal_struct_id`),\n  KEY `pos_id` (`pos_id`),\n  CONSTRAINT `salary_structure_ibfk_1` FOREIGN KEY (`pos_id`) REFERENCES `positions` (`pos_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'1\',\'1\',\'126252\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'2\',\'2\',\'62967\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'3\',\'3\',\'47247\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'4\',\'4\',\'18957\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'5\',\'5\',\'40208\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'6\',\'6\',\'14061\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'7\',\'7\',\'126252\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'8\',\'8\',\'37024\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'9\',\'9\',\'40208\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'10\',\'10\',\'25586\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'11\',\'11\',\'32245\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'12\',\'12\',\'23226\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'13\',\'13\',\'126252\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'14\',\'14\',\'62967\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'15\',\'15\',\'40208\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'16\',\'16\',\'30024\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'17\',\'17\',\'37024\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'18\',\'18\',\'51304\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'19\',\'19\',\'126252\');\nINSERT INTO `salary_structure` (`sal_struct_id`,`pos_id`,`basic_pay`) VALUES (\'20\',\'20\',\'62967\');\nDROP TABLE IF EXISTS `security_log_table`;\nCREATE TABLE `security_log_table` (\n  `security_log_id` int(100) NOT NULL AUTO_INCREMENT,\n  `user_id` int(100) NOT NULL,\n  `login_time` datetime NOT NULL,\n  `logout_time` datetime NOT NULL,\n  `status` enum(\'success\',\'failed\') NOT NULL,\n  PRIMARY KEY (`security_log_id`),\n  KEY `security_log_user_id_c` (`user_id`),\n  CONSTRAINT `security_log_user_id_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `sessions`;\nCREATE TABLE `sessions` (\n  `session_id` varchar(100) NOT NULL,\n  `user_id` int(100) NOT NULL,\n  `login_time` datetime NOT NULL,\n  `logout_time` datetime DEFAULT NULL,\n  `session_status` enum(\'active\',\'ended\') NOT NULL,\n  PRIMARY KEY (`session_id`),\n  KEY `sessions_user_id_c` (`user_id`),\n  CONSTRAINT `sessions_user_id_c` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'0de48c2ce8666b7d90b7ed605018e8f275a0539aca49b211caa89fb73bc88629\',\'2\',\'2025-11-21 02:45:59\',NULL,\'active\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'113619650a5bb75b2e98eb98c4a77b68aafcf63e6092ac53ce5091c5d319524b\',\'2\',\'2025-11-20 23:15:26\',\'2025-11-20 23:15:47\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'1222af890dbe35831a1e80ed71eafd6d7e016707aea8c2ae2837210e6bb56ced\',\'2\',\'2025-11-21 03:46:47\',\'2025-11-21 03:47:39\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'1b4f9bae7ec124ea752b614bcc345941abb344da8448f05052b2dc2982f72209\',\'1\',\'2025-10-25 12:28:17\',\'2025-10-25 12:28:21\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'1ebb6b712c7b7b983b5a7ad731d7aff84b4860de0887e2c3dd9eb8a16c7d6bc0\',\'3\',\'2025-11-21 02:43:47\',\'2025-11-21 02:44:07\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'206ee83ed874e65d188f7003ff8a18760f42ba5fd7157afbd327c0667c97b16d\',\'1\',\'2025-11-20 23:01:21\',\'2025-11-20 23:15:23\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'210eb6fd20369a5e46f340f5a69c8bcb4fda70af21ba32c2b58c5091e5c2c4cc\',\'1\',\'2025-11-21 03:39:38\',\'2025-11-21 03:39:52\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'281f3c2d232184f80da5490b0edc65deba084e9cd46ca628883739fd75e7f576\',\'4\',\'2025-11-21 02:44:13\',\'2025-11-21 02:44:27\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'2ca0ffa1a96ee3719057f5e3d4b7b54702a058517bd00d0fad8af0e9a1b3a399\',\'1\',\'2025-11-21 01:30:02\',\'2025-11-21 01:30:17\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'31ff48e0be120ba18bb83450b35d93a55ff850bdb346ee21470261eea7db77d9\',\'3\',\'2025-11-21 02:43:45\',NULL,\'active\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'439d323f2d4f63c853af944fc3275701e64faa43e6b44a7ae432cb29287411d1\',\'4\',\'2025-11-21 01:32:03\',\'2025-11-21 01:33:18\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'48bf69973c6dac3cbff37a34bc0243b4e07e6101a8a310e9e57d158eaf65a8be\',\'1\',\'2025-10-25 12:58:46\',\'2025-10-25 13:13:24\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'4945e171fa7d8ea9930f2807f20ba043347e794f91028209420ac7a79ac6ac46\',\'1\',\'2025-11-21 21:32:12\',\'2025-11-21 21:42:13\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'49c4421f388c38694cd7d141f53c366866c4f40904869f1c265be03acf2ac2d9\',\'4\',\'2025-11-21 03:47:44\',\'2025-11-21 03:48:00\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'4a4252130068ad8dce01fafe26504bb049e5f5844f271223883e3119488f65e5\',\'2\',\'2025-11-21 03:23:07\',\'2025-11-21 03:23:19\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'4a5711fff5572ec2816f636defce708cf7ed65831165111ef395611fb78b686b\',\'2\',\'2025-11-21 02:39:16\',\'2025-11-21 02:43:41\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'54e61bb0473dc668877aac620afd7c4884d0310ea220cce69d3a758b833d169a\',\'2\',\'2025-11-21 20:15:10\',\'2025-11-21 20:15:32\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'5987cb90889068ab56793b0fee2b9ec4a81c4c92d7e237e7c25941b8b90753b2\',\'2\',\'2025-11-21 03:38:31\',\'2025-11-21 03:38:34\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'599458f4a75b8853e5fb210633d5d85a17e10e301345ab024ef4041d46f41bb6\',\'2\',\'2025-10-25 13:19:34\',\'2025-10-25 13:24:49\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'5aceeb300bedd8cab5ed7e99082cd53d6bda04b5b1df42e1208fe789461b3f28\',\'1\',\'2025-11-20 23:20:38\',\'2025-11-20 23:44:51\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'5e9288b604c1c54734b97933232522fafd9dbc170c0446ddd62011655a1c4f89\',\'2\',\'2025-11-21 02:58:50\',\'2025-11-21 02:59:58\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'5ea68abf550df2c0792ff8be7578984b6322528fe48460f815c79713af06b896\',\'1\',\'2025-10-25 13:13:39\',\'2025-10-25 13:16:20\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'5f15c43a63db744ed26e782fe1cb8a97379e5bc3929c85e7ef805246e0cfde22\',\'2\',\'2025-11-21 03:13:16\',\'2025-11-21 03:14:31\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'67b83615773ed32b848998572005425a318c2de830ce11556155f00c74e46499\',\'4\',\'2025-11-20 23:16:08\',\'2025-11-20 23:17:14\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'6c0e0b5a3f3c5ee2b6a7f4b0dbb54bd403bd79a7d60467808c4ce0386973b86f\',\'1\',\'2025-11-21 03:49:37\',\'2025-11-21 20:15:06\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'6e05609b85cfbbaae46053eabb8fb1b8c3c343c5e118b1bbcad8161f2b1d3bd0\',\'1\',\'2025-11-21 03:23:51\',\'2025-11-21 03:24:10\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'7be19d81b91271d56b7f8c3e77dfc5adcce341a505573fbcd5520d23f3f53053\',\'2\',\'2025-11-21 03:38:24\',NULL,\'active\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'7e6b4ae2b97092593a09a86dcf747a103427d30233f46a8c79293a4689d8219e\',\'2\',\'2025-11-20 23:44:58\',\'2025-11-20 23:45:36\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'869d3ff5197329b1cfb52505a5b2b6f312c89811ea7daa54fbf1a93e9d263c61\',\'2\',\'2025-11-21 02:45:58\',NULL,\'active\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'89a66930c24aab015babf6b53cbbc2a9966f572c1e7f24e1eda7adcf31d98e7f\',\'1\',\'2025-11-21 03:23:43\',NULL,\'active\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'8aff536247cecf72fe731fe423faa38a2775da0e8f9d9304ea11700d0bc1f078\',\'1\',\'2025-11-21 22:48:04\',\'2025-11-21 22:50:18\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'99368f60778819b419f68ae08a252538828df6bdd774eea01d62558b2de7f777\',\'1\',\'2025-11-22 00:24:57\',NULL,\'active\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'9b5abaa594508f2d3bc473a0b145b2f337d7d0acabac80a625e8d6f766e2237b\',\'3\',\'2025-11-21 03:39:09\',\'2025-11-21 03:39:32\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'9cf0ae6f8c88a3924c241562d22e47252ea80286d05d82f598584cbe1bdc25ae\',\'2\',\'2025-11-20 23:20:16\',\'2025-11-20 23:20:28\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'a2aab157dbd10e5f908c556548400ebce6e9857d18468ecfcff38f4e6bb6d1f2\',\'4\',\'2025-11-21 01:30:21\',\'2025-11-21 01:30:28\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'a56685387226018fab5799d83f27805c72144aa2fdf48ec5ee5f439116853988\',\'3\',\'2025-11-21 23:45:06\',\'2025-11-22 00:24:51\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'a75121c022b2648af153ea77cb0ea4c7d467cff393252fc0d84c0db698a3559c\',\'1\',\'2025-11-20 23:45:42\',\'2025-11-21 01:01:35\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'a97f5f4171d31b541ad1c1700052034170ff545171367cc9cf36c3f88f1fc89d\',\'1\',\'2025-11-21 21:42:18\',\'2025-11-21 21:47:32\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'ae1ee42790e602abcc0d95087e8719675485448e980a5b24eda4b7474b9bf1c9\',\'2\',\'2025-11-21 02:46:05\',\'2025-11-21 02:46:49\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'b26641e6c27b168a44df92424b7e0845600c30b4e2d46a1375107da15144fc0d\',\'1\',\'2025-11-20 23:17:18\',\'2025-11-20 23:20:06\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'b34e9174d8a9374d3923b66d560391568ba1d1d17aeb974f3de4f14e0f1f5197\',\'1\',\'2025-11-21 20:15:36\',\'2025-11-21 20:46:36\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'c01d833bfce811f973b84cc1254aa5111b3bff7302e99f6b50f5b6a65dad0829\',\'3\',\'2025-11-20 23:15:51\',\'2025-11-20 23:16:02\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'c0cf4c8e9536ca7257ebd17ccd12519edfbfcd1629c2a078386897e5ce398be3\',\'3\',\'2025-11-21 03:48:09\',\'2025-11-21 03:48:45\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'c10bf265ede2546c8d92ffe5e674190d57659d7f00af0ef153340f947b50448f\',\'2\',\'2025-11-21 03:37:50\',NULL,\'active\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'c3fe79c62d8377f126c0688476195500268e443659effb500f60423ae7c0c57c\',\'4\',\'2025-11-21 02:46:57\',\'2025-11-21 02:58:44\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'c8e120cc86f034d9eedad7b7c5d2203204385a2213b935a91e5da1b9d442e4bf\',\'1\',\'2025-11-21 03:46:30\',\'2025-11-21 03:46:38\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'cc303610ae2eaf8d354b25d8fb137a458c793ee8955ce4fb96bf98209ffe79a5\',\'2\',\'2025-11-21 03:48:48\',\'2025-11-21 03:49:29\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'cf60f09e79899069af757e9f0c793bb1713421e5db7fedee3455201008a4a2c2\',\'1\',\'2025-10-25 12:27:57\',\'2025-10-25 12:28:02\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'cfb7b7cf876487fca0fe6ded506a298296ebdfec27962b1abb1322b13b6a32df\',\'2\',\'2025-11-21 03:37:31\',NULL,\'active\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'d175dd8247b98c6b4b426833bccc613ad5a5abb27205120c71c365187bb4ddc3\',\'2\',\'2025-11-21 01:29:36\',\'2025-11-21 01:29:55\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'d54105cee35735cbc6b110790adf0a916d4d28e37b504eafb0e4c5dac5156528\',\'4\',\'2025-11-21 03:40:09\',\'2025-11-21 03:46:14\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'d6a7234c6b4839b70af49dcd264e9f97d3f68e4bbebad5fe0551f1d77a8d9e38\',\'4\',\'2025-10-25 13:16:34\',\'2025-10-25 13:17:20\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'db9e8324abbc94978341cfa7f8e0ee28419f27e7a25b9da21193dc1524b2bfb2\',\'1\',\'2025-10-25 13:25:32\',\'2025-10-30 16:06:44\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'dbf3a0175a349ececac78129e3e5d8a30a856d6c93db52b579c36a5d897da4a7\',\'1\',\'2025-11-21 01:30:56\',\'2025-11-21 01:31:59\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'dd845517245c2cd3f0dcb04e804bf724acf4cf1bb4b6714c6842ff1193cebd2c\',\'1\',\'2025-11-21 01:33:24\',\'2025-11-21 02:39:13\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'e5bedfc0c3a757177a65d508a3586e18aaa4d11e37c2152917e88880d8720fab\',\'4\',\'2025-10-25 12:05:12\',\'2025-10-25 12:05:19\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'e5e72ca5fa21e34391abf74d72f45460af60f5bbe6db58ebc060dc7130868241\',\'3\',\'2025-11-21 20:46:39\',\'2025-11-21 21:32:07\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'f3021c435bbc0f495ea84422c66c011d5c21aeb5dac73aa88940330216626541\',\'1\',\'2025-11-21 01:01:39\',\'2025-11-21 01:29:33\',\'ended\');\nINSERT INTO `sessions` (`session_id`,`user_id`,`login_time`,`logout_time`,`session_status`) VALUES (\'f4f23896ba06fb8aae72298aa4c61656324d92c514e737b36ec2e4e90b1081d3\',\'1\',\'2025-11-21 03:23:44\',NULL,\'active\');\nDROP TABLE IF EXISTS `settings_table`;\nCREATE TABLE `settings_table` (\n  `setting_id` int(100) NOT NULL AUTO_INCREMENT,\n  `setting_name` varchar(256) NOT NULL,\n  `value` varchar(256) NOT NULL,\n  `description` text NOT NULL,\n  PRIMARY KEY (`setting_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `settings_table` (`setting_id`,`setting_name`,`value`,`description`) VALUES (\'1\',\'Minimum Password Length\',\'8\',\'Minimum characters allowed for passwords.\');\nINSERT INTO `settings_table` (`setting_id`,`setting_name`,`value`,`description`) VALUES (\'2\',\'Require Uppercase Letters\',\'1\',\'Require Uppercase Letters\');\nINSERT INTO `settings_table` (`setting_id`,`setting_name`,`value`,`description`) VALUES (\'3\',\'Require Lowercase Letters\',\'1\',\'Require Lowercase Letters\');\nINSERT INTO `settings_table` (`setting_id`,`setting_name`,`value`,`description`) VALUES (\'4\',\'Require Numbers\',\'1\',\'Require Lowercase Letters\');\nINSERT INTO `settings_table` (`setting_id`,`setting_name`,`value`,`description`) VALUES (\'5\',\'Require Symbols\',\'1\',\'Require Symbols\');\nINSERT INTO `settings_table` (`setting_id`,`setting_name`,`value`,`description`) VALUES (\'6\',\'Password Expiry\',\'90\',\'Password Expiry\');\nINSERT INTO `settings_table` (`setting_id`,`setting_name`,`value`,`description`) VALUES (\'7\',\'Lock Account After\',\'5\',\'Lock Account After\');\nDROP TABLE IF EXISTS `taxes`;\nCREATE TABLE `taxes` (\n  `tax_id` int(11) NOT NULL AUTO_INCREMENT,\n  `range_from` int(11) DEFAULT NULL,\n  `range_to` int(11) DEFAULT NULL,\n  `rate_on_excess` int(11) DEFAULT NULL,\n  `additional_amount` int(11) DEFAULT NULL,\n  PRIMARY KEY (`tax_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nDROP TABLE IF EXISTS `user_table`;\nCREATE TABLE `user_table` (\n  `user_id` int(100) NOT NULL AUTO_INCREMENT,\n  `user_name` varchar(256) NOT NULL,\n  `user_email` varchar(256) NOT NULL,\n  `password` varchar(256) NOT NULL,\n  `role_id` int(100) NOT NULL,\n  `status` enum(\'active\',\'inactive\') NOT NULL,\n  PRIMARY KEY (`user_id`),\n  UNIQUE KEY `user_email` (`user_email`),\n  KEY `user_tables_role_id_c` (`role_id`),\n  CONSTRAINT `user_tables_role_id_c` FOREIGN KEY (`role_id`) REFERENCES `roles_table` (`role_id`) ON UPDATE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\nINSERT INTO `user_table` (`user_id`,`user_name`,`user_email`,`password`,`role_id`,`status`) VALUES (\'1\',\'Klarenz Cobie Manrique\',\'manrique_klarenzcobie@plpasig.edu.ph\',\'123456789\',\'1\',\'active\');\nINSERT INTO `user_table` (`user_id`,`user_name`,`user_email`,`password`,`role_id`,`status`) VALUES (\'2\',\'Charles Jeramy De Padua\',\'depadua_charlesjeramy@plpasig.edu.ph\',\'123456789\',\'2\',\'active\');\nINSERT INTO `user_table` (`user_id`,`user_name`,`user_email`,`password`,`role_id`,`status`) VALUES (\'3\',\'Charl Joven Castro\',\'castro_charljoven@plpasig.edu.ph\',\'123456789\',\'3\',\'active\');\nINSERT INTO `user_table` (`user_id`,`user_name`,`user_email`,`password`,`role_id`,`status`) VALUES (\'4\',\'Jesseroe Piatos\',\'piatos_jesseroe@plpasig.edu.ph\',\'123456789\',\'4\',\'active\');\nINSERT INTO `user_table` (`user_id`,`user_name`,`user_email`,`password`,`role_id`,`status`) VALUES (\'5\',\'Gracci Juicy\',\'medrano_gracialdei@plpasig.edu.ph\',\'123456789Aa@\',\'4\',\'active\');\nINSERT INTO `user_table` (`user_id`,`user_name`,`user_email`,`password`,`role_id`,`status`) VALUES (\'7\',\'Restore Guy\',\'restore@gmail.com\',\'123456789Aa@\',\'1\',\'active\');\nSET FOREIGN_KEY_CHECKS=1;\n');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

DROP TABLE IF EXISTS `work_schedules`;
CREATE TABLE `work_schedules` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `mon` tinyint(1) NOT NULL DEFAULT 1,
  `tue` tinyint(1) NOT NULL DEFAULT 1,
  `wed` tinyint(1) NOT NULL DEFAULT 1,
  `thu` tinyint(1) NOT NULL DEFAULT 1,
  `fri` tinyint(1) NOT NULL DEFAULT 1,
  `sat` tinyint(1) NOT NULL DEFAULT 0,
  `sun` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `work_schedules` (`schedule_id`, `name`, `mon`, `tue`, `wed`, `thu`, `fri`, `sat`, `sun`) VALUES
(1, 'Mon–Fri', 1,1,1,1,1,0,0),
(2, 'Tue–Sat', 0,1,1,1,1,1,0),
(3, 'Sun–Thu', 1,1,1,1,0,0,1),
(4, 'All Days', 1,1,1,1,1,1,1);

-- --------------------------------------------------------

--
-- Table structure for table `employee_work_schedules`
--

DROP TABLE IF EXISTS `employee_work_schedules`;
CREATE TABLE `employee_work_schedules` (
  `ews_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  PRIMARY KEY (`ews_id`),
  KEY `ews_emp_id` (`emp_id`),
  KEY `ews_schedule_id` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE employee_work_schedules 
  MODIFY ews_id INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `employee_work_schedules`
  ADD CONSTRAINT `ews_emp_fk` FOREIGN KEY (`emp_id`) REFERENCES `user_table` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ews_sched_fk` FOREIGN KEY (`schedule_id`) REFERENCES `work_schedules` (`schedule_id`) ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO `employee_work_schedules` (`ews_id`, `emp_id`, `schedule_id`, `effective_from`, `effective_to`)
VALUES
-- Employee 1
(1, 1, 1, '2025-01-01', '2025-03-31'),
(2, 1, 2, '2025-04-01', NULL),

-- Employee 2
(3, 2, 1, '2025-02-15', NULL),

-- Employee 3
(4, 3, 3, '2025-01-10', '2025-01-31'),
(5, 3, 2, '2025-02-01', NULL),

-- Employee 4
(6, 4, 2, '2025-01-01', NULL),

-- Employee 5
(7, 5, 1, '2025-03-01', '2025-06-30'),
(8, 5, 4, '2025-07-01', NULL),

-- Employee 6
(9, 6, 3, '2025-01-20', NULL),

-- Employee 7
(10, 7, 2, '2025-01-05', '2025-04-30'),
(11, 7, 4, '2025-05-01', NULL);

COMMIT;

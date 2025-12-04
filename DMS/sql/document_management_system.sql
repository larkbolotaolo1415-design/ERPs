-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 05:58 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `document_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `deptID` int(11) NOT NULL,
  `deptName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`deptID`, `deptName`) VALUES
(1, 'Anesthetics Department'),
(2, 'Breast Screening Department'),
(3, 'Cardiology Department'),
(4, 'Ear, Nose and Throat (ENT) Department'),
(5, 'Elderly Services (Geriatrics)'),
(6, 'Gastroenterology Department'),
(7, 'General Surgery Department'),
(8, 'Gynecology Department'),
(9, 'Hematology Department'),
(10, 'Human Resources (HR) Department'),
(15, 'IT Department'),
(17, 'Finance Department'),
(18, 'Sales and Operation Department'),
(19, 'Warehouse and Supply Department'),
(20, 'Records Management Department'),
(21, 'Medical and Health Services Department'),
(22, 'Marketing Department');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `file_size` int(11) DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `filename`, `mime_type`, `file_path`, `uploaded_by`, `upload_date`, `file_size`, `description`) VALUES
(1, 'SoftEng-CaseStudy_FGS.docx.pdf', 'application/pdf', 'uploads/documents/doc_692ea029863a98.96333709_SoftEng-CaseStudy_FGS.docx.pdf', 'admin@dms.com', '2025-12-02 16:15:37', 201826, 'Postcard');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `empID` varchar(100) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `email_address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employment_type`
--

CREATE TABLE `employment_type` (
  `emtypeID` int(11) NOT NULL,
  `typeName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employment_type`
--

INSERT INTO `employment_type` (`emtypeID`, `typeName`) VALUES
(1, 'Full Time'),
(2, 'Part Time'),
(3, 'Regular'),
(4, 'Contractual'),
(5, 'Internship');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size` bigint(20) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `uploader_id` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `name`, `original_filename`, `mime_type`, `size`, `file_path`, `uploader_id`, `upload_date`) VALUES
(1, 'Paul Justin D. Francisco', 'IT_ETHICS_FINALS (1).pdf', 'application/pdf', 143204, 'uploads/templates/tpl_692ea01ec0d707.70168623_IT_ETHICS_FINALS__1_.pdf', 1, '2025-12-02 08:15:26');

-- --------------------------------------------------------

--
-- Table structure for table `file_permissions`
--

CREATE TABLE `file_permissions` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_type_id` int(11) DEFAULT NULL,
  `can_download` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_permissions`
--

INSERT INTO `file_permissions` (`id`, `file_id`, `user_id`, `user_type_id`, `can_download`) VALUES
(7, 5, 4, 2, 1),
(8, 6, 2, 5, 1),
(9, 7, 4, 2, 1),
(10, 8, 4, 2, 1),
(13, 1, 3, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `one_time_tokens`
--

CREATE TABLE `one_time_tokens` (
  `token` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `one_time_tokens`
--

INSERT INTO `one_time_tokens` (`token`, `user_id`, `expires_at`, `used`) VALUES
('3072dff2a990a0e3b96522b2b42df3b0a66bb0d507f5dc38', 4, '2025-11-30 15:36:05', 1),
('bfc67ffd068afe98ded166d7f5ceb8d804802bd9cd80df54', 2, '2025-11-30 15:09:06', 1),
('f4388d92caf5831f655e625f3034ccf311856e148678e48b', 3, '2025-11-30 15:10:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_files`
--

CREATE TABLE `patient_files` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_files`
--

INSERT INTO `patient_files` (`id`, `patient_id`, `file_name`, `original_filename`, `file_size`, `mime_type`, `file_path`, `upload_date`, `description`) VALUES
(1, 2, 'IT_ETHICS_FINALS (1).pdf', 'IT_ETHICS_FINALS (1).pdf', 143204, 'application/pdf', 'uploads/patients/pf_692ea0822bb5c6.37585776_IT_ETHICS_FINALS__1_.pdf', '2025-12-02 08:17:06', 'Postcard');

-- --------------------------------------------------------

--
-- Table structure for table `patient_file_access`
--

CREATE TABLE `patient_file_access` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `granted_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_file_access`
--

INSERT INTO `patient_file_access` (`id`, `file_id`, `doctor_id`, `granted_date`) VALUES
(2, 2, 3, '2025-11-30 19:13:16'),
(3, 1, 3, '2025-12-02 08:17:06');

-- --------------------------------------------------------

--
-- Table structure for table `position`
--

CREATE TABLE `position` (
  `positionID` int(11) NOT NULL,
  `departmentID` int(11) NOT NULL,
  `emtypeID` int(11) DEFAULT NULL,
  `position_title` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position`
--

INSERT INTO `position` (`positionID`, `departmentID`, `emtypeID`, `position_title`) VALUES
(1, 1, NULL, 'Anesthetic Technician'),
(2, 1, NULL, 'Nurse Anesthetist'),
(3, 1, NULL, 'Anesthesiology Resident'),
(4, 1, NULL, 'Consultant Anesthesiologist'),
(5, 1, NULL, 'Recovery Room Nurse'),
(6, 1, NULL, 'Senior PACU Nurse'),
(7, 1, NULL, 'Operating Room Nurse'),
(8, 1, NULL, 'OR Nurse Supervisor'),
(9, 2, NULL, 'Radiology Assistant'),
(10, 2, NULL, 'Mammography Technologist'),
(11, 2, NULL, 'Senior Technologist'),
(12, 2, NULL, 'Screening Coordinator'),
(13, 2, NULL, 'Breast Care Nurse'),
(14, 2, NULL, 'Senior Breast Nurse'),
(15, 2, NULL, 'Breast Clinic Manager'),
(16, 3, NULL, 'ECG Technician'),
(17, 3, NULL, 'ECHO Technician'),
(18, 3, NULL, 'Cardiac Technologist'),
(19, 3, NULL, 'Cardiac Lab Supervisor'),
(20, 3, NULL, 'Cardiac Nurse'),
(21, 3, NULL, 'Senior Cardiac Nurse'),
(22, 3, NULL, 'Cardiac Rehabilitation Specialist'),
(23, 3, NULL, 'Cardiology Unit Manager'),
(24, 3, NULL, 'Cardiology Resident'),
(25, 3, NULL, 'Fellow'),
(26, 3, NULL, 'Consultant Cardiologist'),
(27, 4, NULL, 'ENT Clinic Assistant'),
(28, 4, NULL, 'ENT Nurse'),
(29, 4, NULL, 'ENT Resident'),
(30, 4, NULL, 'ENT Consultant'),
(31, 4, NULL, 'Audiologist'),
(32, 4, NULL, 'Senior Audiologist'),
(33, 4, NULL, 'Head of Audiology Services'),
(34, 5, NULL, 'Healthcare Assistant'),
(35, 5, NULL, 'Geriatric Nurse'),
(36, 5, NULL, 'Nurse Practitioner'),
(37, 5, NULL, 'Unit Head'),
(38, 5, NULL, 'Physiotherapist'),
(39, 5, NULL, 'Occupational Therapist'),
(40, 5, NULL, 'Senior Therapist'),
(41, 5, NULL, 'Rehabilitation Coordinator'),
(42, 5, NULL, 'Geriatric Resident'),
(43, 5, NULL, 'Consultant in Elderly Medicine'),
(44, 6, NULL, 'Endoscopy Technician'),
(45, 6, NULL, 'Endoscopy Nurse'),
(46, 6, NULL, 'Senior Endoscopy Nurse'),
(47, 6, NULL, 'Unit Supervisor'),
(48, 6, NULL, 'Gastroenterology Resident'),
(49, 6, NULL, 'Fellow'),
(50, 6, NULL, 'Consultant Gastroenterologist'),
(51, 6, NULL, 'Nutritionist'),
(52, 6, NULL, 'Dietitian'),
(53, 6, NULL, 'Senior Dietitian'),
(54, 6, NULL, 'Department Head (Nutrition)'),
(55, 7, NULL, 'Surgical Technician'),
(56, 7, NULL, 'Scrub Nurse'),
(57, 7, NULL, 'Operating Room Nurse'),
(58, 7, NULL, 'Surgical Charge Nurse'),
(59, 7, NULL, 'Surgical Resident'),
(60, 7, NULL, 'Senior Resident'),
(61, 7, NULL, 'Consultant Surgeon'),
(62, 7, NULL, 'Ward Nurse'),
(63, 7, NULL, 'Senior Nurse'),
(64, 7, NULL, 'Nurse Unit Manager'),
(65, 8, NULL, 'OB-GYN Resident'),
(66, 8, NULL, 'Consultant Gynecologist'),
(67, 8, NULL, 'Midwife'),
(68, 8, NULL, 'Senior Midwife'),
(69, 8, NULL, 'Labor and Delivery Supervisor'),
(70, 8, NULL, 'Gynecology Nurse'),
(71, 8, NULL, 'Nurse Coordinator'),
(72, 8, NULL, 'Nurse Manager'),
(73, 9, NULL, 'Phlebotomist'),
(74, 9, NULL, 'Medical Laboratory Scientist (Hematology)'),
(75, 9, NULL, 'Senior Lab Scientist'),
(76, 9, NULL, 'Lab Supervisor'),
(77, 9, NULL, 'Hematology Lab Manager'),
(78, 9, NULL, 'Hematology Resident'),
(79, 9, NULL, 'Consultant Hematologist'),
(80, 9, NULL, 'Oncology Nurse (Hematology Unit)'),
(81, 9, NULL, 'Senior Hematology Nurse'),
(82, 9, NULL, 'Nurse Unit Head'),
(84, 10, NULL, 'HR Assistant'),
(85, 10, NULL, 'HR Officer'),
(87, 10, NULL, 'HR Manager'),
(88, 10, NULL, 'HR Director'),
(89, 10, NULL, 'Recruitment Manager'),
(92, 10, NULL, 'Training and Development Coordinator'),
(101, 17, NULL, 'Payroll Admin'),
(102, 17, NULL, 'Payroll Manager'),
(103, 17, NULL, 'Payroll Officer'),
(104, 18, NULL, 'Point of Sales Admin'),
(105, 19, NULL, 'Inventory Admin'),
(106, 20, NULL, 'Document Management Admin'),
(107, 21, NULL, 'Patient Management Admin'),
(108, 22, NULL, 'Content Management Admin'),
(109, 10, NULL, 'Human Resource (HR) Admin'),
(110, 15, NULL, 'IT Manager'),
(111, 15, NULL, 'IT Associate'),
(112, 15, NULL, 'IT Associate Jr'),
(113, 15, NULL, 'IT Head');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` varchar(100) NOT NULL,
  `applicant_employee_id` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('admin','doctor','patient') NOT NULL DEFAULT 'patient',
  `user_type_id` int(11) DEFAULT NULL,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `username`, `password`, `password_hash`, `role`, `user_type_id`, `force_password_change`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@dms.com', NULL, '$2y$10$GxjnQ.2SFVN9Jx9pKMxbWOsJ9mT5DQUkKcoc662lSTmSp7y7AxzVe', NULL, 'admin', NULL, 0, NULL, '2025-10-18 04:00:55', '2025-10-25 01:43:53'),
(2, 'paulcancerous01', 'paulcancerous01@gmail.com', 'paulcancerous01@gmail.com', '$2y$10$Cb2n5pRsoG0avWopBpQMme1yfkgOfEb3zoX4.TQINjNW.l7rdBuyq', '$2y$10$Cb2n5pRsoG0avWopBpQMme1yfkgOfEb3zoX4.TQINjNW.l7rdBuyq', 'patient', 5, 0, 1, '2025-11-30 15:08:44', '2025-11-30 15:09:11'),
(3, 'pauljustinfrancisco', 'pauljustinfrancisco@gmail.com', 'pauljustinfrancisco@gmail.com', '$2y$10$GdPoYjg2TOUcGhbuiKu/uOeIwT9b6vzwKJeIgwS5h5q4rIABAGJt6', '$2y$10$GdPoYjg2TOUcGhbuiKu/uOeIwT9b6vzwKJeIgwS5h5q4rIABAGJt6', 'doctor', 2, 0, 1, '2025-11-30 15:10:27', '2025-11-30 15:10:41'),
(4, 'paulcancerous02', 'paulcancerous02@gmail.com', 'paulcancerous02@gmail.com', '$2y$10$2c3DPUuRbFPfdsi/iMsYieKKymVgbPESwP69vdZ7z2yKjzHBD8J4C', '$2y$10$2c3DPUuRbFPfdsi/iMsYieKKymVgbPESwP69vdZ7z2yKjzHBD8J4C', 'doctor', 2, 0, 1, '2025-11-30 15:35:49', '2025-11-30 15:36:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE `user_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_types`
--

INSERT INTO `user_types` (`id`, `name`) VALUES
(1, 'Admin'),
(2, 'Doctor'),
(3, 'Nurse'),
(5, 'Patient'),
(4, 'Staff');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_admin_id` (`admin_id`),
  ADD KEY `idx_audit_target` (`target_type`,`target_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`deptID`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_upload_date` (`upload_date`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`empID`);

--
-- Indexes for table `employment_type`
--
ALTER TABLE `employment_type`
  ADD PRIMARY KEY (`emtypeID`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_files_uploader_id` (`uploader_id`);

--
-- Indexes for table `file_permissions`
--
ALTER TABLE `file_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_file_permissions_file_id` (`file_id`),
  ADD KEY `idx_file_permissions_user_id` (`user_id`),
  ADD KEY `idx_file_permissions_user_type_id` (`user_type_id`);

--
-- Indexes for table `one_time_tokens`
--
ALTER TABLE `one_time_tokens`
  ADD PRIMARY KEY (`token`),
  ADD KEY `idx_ott_user_expires` (`user_id`,`expires_at`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `patient_files`
--
ALTER TABLE `patient_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_files_patient_id` (`patient_id`),
  ADD KEY `idx_patient_files_upload_date` (`upload_date`);

--
-- Indexes for table `patient_file_access`
--
ALTER TABLE `patient_file_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_file_doctor` (`file_id`,`doctor_id`),
  ADD KEY `idx_patient_file_access_file_id` (`file_id`),
  ADD KEY `idx_patient_file_access_doctor_id` (`doctor_id`);

--
-- Indexes for table `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`positionID`),
  ADD KEY `departmentID` (`departmentID`),
  ADD KEY `fk_position_employment` (`emtypeID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_user_type_id` (`user_type_id`);

--
-- Indexes for table `user_types`
--
ALTER TABLE `user_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_types_name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `deptID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `file_permissions`
--
ALTER TABLE `file_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_files`
--
ALTER TABLE `patient_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patient_file_access`
--
ALTER TABLE `patient_file_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `positionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_types`
--
ALTER TABLE `user_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `fk_files_uploader_users` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `one_time_tokens`
--
ALTER TABLE `one_time_tokens`
  ADD CONSTRAINT `fk_ott_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_files`
--
ALTER TABLE `patient_files`
  ADD CONSTRAINT `fk_patient_files_patient` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `position`
--
ALTER TABLE `position`
  ADD CONSTRAINT `fk_position_employment` FOREIGN KEY (`emtypeID`) REFERENCES `employment_type` (`emtypeID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `position_fk_department` FOREIGN KEY (`departmentID`) REFERENCES `department` (`deptID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

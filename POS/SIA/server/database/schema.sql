-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 04:35 AM
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
-- Database: `pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_configuration`
--

CREATE TABLE `admin_configuration` (
  `id` int(10) UNSIGNED NOT NULL,
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT 12.00,
  `senior_discount` decimal(5,2) NOT NULL DEFAULT 20.00,
  `pwd_discount` decimal(5,2) NOT NULL DEFAULT 20.00,
  `minimum_stock_for_shortage` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `minimum_medicine_count_for_warning` int(10) UNSIGNED NOT NULL DEFAULT 25,
  `minimum_medicine_count_for_critical` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_configuration`
--

INSERT INTO `admin_configuration` (`id`, `tax_percentage`, `senior_discount`, `pwd_discount`, `minimum_stock_for_shortage`, `minimum_medicine_count_for_warning`, `minimum_medicine_count_for_critical`, `date_created`) VALUES
(1, 12.00, 20.00, 20.00, 50, 25, 50, '2025-11-20 14:02:17');

-- --------------------------------------------------------

--
-- Table structure for table `admitted_patients`
--

CREATE TABLE `admitted_patients` (
  `patient_id` varchar(10) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `date_admitted` date DEFAULT NULL,
  `date_discharged` date DEFAULT NULL,
  `room_number` varchar(10) DEFAULT NULL,
  `status` enum('admitted','discharged') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admitted_patients`
--

INSERT INTO `admitted_patients` (`patient_id`, `first_name`, `last_name`, `date_admitted`, `date_discharged`, `room_number`, `status`) VALUES
('PT-1011', 'Maria', 'Santos', '2025-05-10', NULL, '201-B', 'admitted'),
('PT-1022', 'Leo', 'Mendoza', '2025-05-01', NULL, '304-A', 'admitted'),
('PT-1033', 'Carla', 'Dizon', '2025-04-20', '2025-04-27', '110-C', 'discharged');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(10) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `user_role` varchar(20) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `emp_id`, `firstname`, `lastname`, `user_role`, `email`, `password`, `otp_code`, `otp_expiry`) VALUES
(1, 'PH-0001', 'Charles Dustin', 'Campos', 'Admin', 'campos_charlesdustin@plpasig.edu.ph', '1234', '717325', '2025-11-19 20:30:26'),
(2, 'PH-0002', 'Carlruselle', 'Avecilla', 'Clerk', 'avecilla_carlruselle@plpasig.edu.ph', '1234', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `patient_id` varchar(10) DEFAULT NULL,
  `walkin_id` int(11) DEFAULT NULL,
  `employee_id` varchar(10) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_type` enum('none','pwd','senior','voucher') DEFAULT 'none',
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','gcash','card') DEFAULT 'cash',
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `patient_id`, `walkin_id`, `employee_id`, `subtotal`, `discount_type`, `discount_amount`, `tax_amount`, `total_amount`, `payment_method`, `date_created`) VALUES
(1, 'INV-2025-0001', 'pt-1011', NULL, '1', 98.00, 'none', 0.00, 11.76, 109.76, 'cash', '2025-11-21 07:38:28'),
(2, 'INV-2025-0002', 'pt-1011', NULL, '1', 162.00, 'none', 0.00, 19.44, 181.44, 'cash', '2025-11-21 07:39:22'),
(3, 'INV-2025-0003', NULL, 10, '1', 165.00, 'none', 0.00, 19.80, 184.80, 'cash', '2025-11-21 07:45:58'),
(4, 'INV-2025-0004', NULL, 11, '1', 241.00, 'none', 0.00, 28.92, 269.92, 'cash', '2025-11-21 07:52:07'),
(5, 'INV-2025-0005', 'pt-1011', NULL, '1', 2352.00, 'none', 0.00, 282.24, 2634.24, 'cash', '2025-11-21 07:54:33'),
(6, 'INV-2025-0006', 'pt-1022', NULL, '1', 15.00, 'senior', 3.00, 1.44, 13.44, 'cash', '2025-11-21 08:20:13'),
(7, 'INV-2025-0007', 'pt-1022', NULL, '1', 20.00, 'senior', 4.00, 1.92, 17.92, 'cash', '2025-11-21 08:20:23'),
(8, 'INV-2025-0008', 'pt-1011', NULL, '1', 5.00, 'pwd', 1.00, 0.48, 4.48, 'cash', '2025-11-21 08:20:34'),
(9, 'INV-2025-0009', NULL, 12, '1', 170.00, 'pwd', 34.00, 16.32, 152.32, 'cash', '2025-11-21 08:24:32'),
(10, 'INV-2025-0010', NULL, 13, '1', 880.00, 'none', 0.00, 105.60, 985.60, 'cash', '2025-11-21 08:25:48'),
(11, 'INV-2025-0011', 'pt-1011', NULL, '1', 6190.00, 'senior', 1238.00, 594.24, 5546.24, 'cash', '2025-11-21 08:29:32');

--
-- Triggers `invoices`
--
DELIMITER $$
CREATE TRIGGER `trg_generate_invoice_number` BEFORE INSERT ON `invoices` FOR EACH ROW BEGIN
    DECLARE nextNumber INT;


    SELECT
        IFNULL(MAX(CAST(SUBSTRING(invoice_number, 10) AS UNSIGNED)), 0) + 1
    INTO nextNumber
    FROM invoices
    WHERE SUBSTRING(invoice_number, 5, 4) = YEAR(CURDATE());


    SET NEW.invoice_number = CONCAT(
        'INV-',
        YEAR(CURDATE()),
        '-',
        LPAD(nextNumber, 4, '0')
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `medicine_id` varchar(50) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`item_id`, `invoice_number`, `medicine_id`, `medicine_name`, `price`, `quantity`) VALUES
(1, 'INV-2025-0001', 'AMB-0015S-001', '0', 98.00, 1),
(2, 'INV-2025-0002', 'AMB-0015S-001', '0', 98.00, 1),
(3, 'INV-2025-0002', 'PAR-0500T-001', '0', 5.00, 2),
(4, 'INV-2025-0002', 'LOR-0010T-001', '0', 45.00, 1),
(5, 'INV-2025-0002', 'AMI-0025T-001', '0', 9.00, 1),
(6, 'INV-2025-0003', 'LOR-0010T-001', '0', 45.00, 1),
(7, 'INV-2025-0003', 'AMB-0015S-001', '0', 98.00, 1),
(8, 'INV-2025-0003', 'DIC-0050T-001', '0', 22.00, 1),
(9, 'INV-2025-0004', 'AMB-0015S-001', 'Ambrolex', 98.00, 2),
(10, 'INV-2025-0004', 'LOR-0010T-001', 'Ativan', 45.00, 1),
(11, 'INV-2025-0005', 'AMB-0015S-001', 'Ambrolex', 98.00, 24),
(12, 'INV-2025-0006', 'PAR-0500T-001', 'Biogesic', 5.00, 3),
(13, 'INV-2025-0007', 'PAR-0500T-001', 'Biogesic', 5.00, 4),
(14, 'INV-2025-0008', 'PAR-0500T-001', 'Biogesic', 5.00, 1),
(15, 'INV-2025-0009', 'PAR-0500T-001', 'Biogesic', 5.00, 34),
(16, 'INV-2025-0010', 'LOS-0050T-001', 'Cozaar', 20.00, 44),
(17, 'INV-2025-0011', 'LOR-0010T-001', 'Ativan', 45.00, 15),
(18, 'INV-2025-0011', 'PAR-0500T-001', 'Biogesic', 5.00, 3),
(19, 'INV-2025-0011', 'PAR-0120S-001', 'Calpol', 110.00, 50);

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `medicine_id` varchar(50) NOT NULL,
  `medicine_group` varchar(100) NOT NULL,
  `medicine_name` varchar(150) NOT NULL,
  `generic_name` varchar(150) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `form` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`medicine_id`, `medicine_group`, `medicine_name`, `generic_name`, `dosage`, `form`, `stock`, `price`) VALUES
('AMB-0015S-001', 'Cough & Cold', 'Ambrolex', 'Ambroxol', '15mg/5mL', 'Syrup', 570, 98.00),
('AMI-0025T-001', 'Antidepressant', 'Elavil', 'Amitriptyline', '25mg', 'Tablet', 49, 9.00),
('AML-0010T-001', 'Hypertension', 'Norvasc', 'Amlodipine', '10mg', 'Tablet', 999, 38.50),
('AMP-0500C-001', 'Antibiotic', 'Generic Ampicillin', 'Ampicillin (Penicillin)', '500mg', 'Capsule', 20, 131.00),
('AZT-0500T-001', 'Antibiotic', 'Zithromax', 'Azithromycin (Macrolide)', '500mg', 'Tablet', 999, 75.00),
('CET-0010T-001', 'Antihistamine', 'Zyrtec', 'Cetirizine', '10mg', 'Tablet', 50, 18.00),
('DIC-0050T-001', 'Pain Relief', 'Cataflam', 'Diclofenac Potassium', '50mg', 'Tablet', 19, 22.00),
('FER-0325C-001', 'Supplements', 'Ferrous Sulfate', 'Ferrous Sulfate', '325mg', 'Capsule', 0, 7.00),
('IBU-0200C-001', 'Pain Relief', 'Medicol Advance', 'Ibuprofen', '200mg', 'Capsule', 999, 15.00),
('LOR-0010T-001', 'Antianxiety', 'Ativan', 'Lorazepam', '1mg', 'Tablet', 0, 45.00),
('LOS-0050T-001', 'Hypertension', 'Cozaar', 'Losartan', '50mg', 'Tablet', 656, 20.00),
('MET-0850T-001', 'Antidiabetic', 'Glucophage', 'Metformin', '850mg', 'Tablet', 500, 12.00),
('OME-0020C-001', 'Gastrointestinal', 'Omeprazole', 'Omeprazole', '20mg', 'Capsule', 550, 10.00),
('PAR-0120S-001', 'Pain Relief', 'Calpol', 'Paracetamol', '120mg/5mL', 'Syrup', 933, 110.00),
('PAR-0500T-001', 'Pain Relief', 'Biogesic', 'Paracetamol', '500mg', 'Tablet', 950, 5.00),
('SAL-0050N-001', 'Respiratory', 'Ventolin Nebules', 'Salbutamol', '2.5mg/2.5mL', 'Nebule', 900, 32.00);

-- --------------------------------------------------------

--
-- Table structure for table `walkin_customers`
--

CREATE TABLE `walkin_customers` (
  `walkin_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `walkin_customers`
--

INSERT INTO `walkin_customers` (`walkin_id`, `first_name`, `last_name`, `transaction_date`) VALUES
(1, 'bago', '', '2025-11-21 05:39:18'),
(2, 'charles', '', '2025-11-21 06:32:29'),
(10, 'charles', '', '2025-11-21 07:45:58'),
(11, 'rain', '', '2025-11-21 07:52:07'),
(12, 'ioooo9', '', '2025-11-21 08:24:32'),
(13, 'mesa', '', '2025-11-21 08:25:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_configuration`
--
ALTER TABLE `admin_configuration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admitted_patients`
--
ALTER TABLE `admitted_patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emp_id` (`emp_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `fk_walkin_id` (`walkin_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_invoice_items_invoice` (`invoice_number`),
  ADD KEY `fk_invoice_items_medicine` (`medicine_id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`medicine_id`);

--
-- Indexes for table `walkin_customers`
--
ALTER TABLE `walkin_customers`
  ADD PRIMARY KEY (`walkin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_configuration`
--
ALTER TABLE `admin_configuration`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `walkin_customers`
--
ALTER TABLE `walkin_customers`
  MODIFY `walkin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_walkin_id` FOREIGN KEY (`walkin_id`) REFERENCES `walkin_customers` (`walkin_id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_number`) REFERENCES `invoices` (`invoice_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoice_items_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

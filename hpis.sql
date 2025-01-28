-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2025 at 06:09 PM
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
-- Database: `hpis`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `doctor_username` varchar(255) DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `date`, `appointment_time`, `doctor_username`, `status`, `created_at`, `updated_at`) VALUES
(9, 20, '2025-01-02 12:27:00', NULL, '5sfAvFX4nIys3jWR/0EY6w==', 'Scheduled', '2025-01-02 04:57:38', '2025-01-02 04:57:38'),
(10, 20, '2025-01-02 14:00:00', NULL, '7XRAsOhNiNm1ih4Lrs5qhA==', 'Scheduled', '2025-01-02 08:12:36', '2025-01-02 08:12:36'),
(11, 21, '2025-01-02 15:08:00', NULL, '7XRAsOhNiNm1ih4Lrs5qhA==', 'Scheduled', '2025-01-02 09:36:37', '2025-01-02 09:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `quantity_received` int(11) DEFAULT NULL,
  `quantity_issued` int(11) DEFAULT NULL,
  `quantity_in_stock` int(11) GENERATED ALWAYS AS (`quantity_received` - `quantity_issued`) STORED,
  `price_per_unit` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `medicine_name` varchar(255) NOT NULL,
  `company_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `medicine_id`, `batch_number`, `quantity_received`, `quantity_issued`, `price_per_unit`, `expiry_date`, `created_at`, `updated_at`, `medicine_name`, `company_id`) VALUES
(49, 7, NULL, 50, NULL, NULL, '2026-02-12', '2024-12-31 07:09:04', '2024-12-31 07:09:04', '', NULL),
(50, 8, NULL, 30, NULL, NULL, '2026-05-12', '2024-12-31 07:09:24', '2024-12-31 14:26:27', '', NULL),
(53, 14, NULL, 45, NULL, NULL, '2026-05-12', '2025-01-01 14:18:51', '2025-01-02 05:39:44', '', NULL),
(54, 15, NULL, 48, NULL, NULL, '2025-01-17', '2025-01-02 10:39:50', '2025-01-02 11:07:51', '', NULL),
(55, 16, NULL, 48, NULL, NULL, '2026-10-14', '2025-01-02 16:59:12', '2025-01-02 17:04:39', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `details`, `timestamp`, `created_at`) VALUES
(11, NULL, 'Added inventory for medicine ID 8', NULL, '2024-12-20 20:30:54', '2024-12-20 20:30:54'),
(12, NULL, 'Added inventory for medicine ID 9', NULL, '2024-12-20 20:33:31', '2024-12-20 20:33:31'),
(17, NULL, 'Added inventory for medicine ID 10', NULL, '2024-12-25 20:14:32', '2024-12-25 20:14:32'),
(18, NULL, 'Added inventory for medicine ID 10', NULL, '2024-12-25 20:15:16', '2024-12-25 20:15:16'),
(19, NULL, 'Issued 1 units of medicine ID 7 to patient ID 1', NULL, '2024-12-25 20:15:31', '2024-12-25 20:15:31'),
(20, NULL, 'Added inventory for medicine ID 9', NULL, '2024-12-26 03:50:02', '2024-12-26 03:50:02'),
(21, NULL, 'Added inventory for medicine ID 9', NULL, '2024-12-26 03:50:37', '2024-12-26 03:50:37'),
(23, NULL, 'Added inventory for medicine ID 7', NULL, '2024-12-26 10:29:17', '2024-12-26 10:29:17'),
(24, NULL, 'Added inventory for medicine ID 7', NULL, '2024-12-26 10:44:34', '2024-12-26 10:44:34'),
(25, NULL, 'Issued 2 units of medicine ID 9 to patient ID 17', NULL, '2024-12-26 10:51:10', '2024-12-26 10:51:10'),
(26, NULL, 'Issued 2 units of medicine ID 9 to patient ID 17', NULL, '2024-12-27 07:08:51', '2024-12-27 07:08:51'),
(27, NULL, 'Added inventory for medicine ID 11', NULL, '2024-12-27 07:09:42', '2024-12-27 07:09:42'),
(28, NULL, 'Added inventory for medicine ID 11', NULL, '2024-12-31 05:14:00', '2024-12-31 05:14:00'),
(29, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 06:51:13', '2024-12-31 06:51:13'),
(32, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 07:06:15', '2024-12-31 07:06:15'),
(33, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 07:06:17', '2024-12-31 07:06:17'),
(34, NULL, 'Added 50 units of Medicine ID: 11 with expiry date 2026-05-12.', NULL, '2024-12-31 07:06:26', '2024-12-31 07:06:26'),
(35, NULL, 'Added  units of Medicine ID: 11 with expiry date .', NULL, '2024-12-31 07:07:15', '2024-12-31 07:07:15'),
(36, NULL, 'Added 50 units of Medicine ID: 8 with expiry date 2026-05-12.', NULL, '2024-12-31 07:07:36', '2024-12-31 07:07:36'),
(37, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 07:07:42', '2024-12-31 07:07:42'),
(38, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 07:07:43', '2024-12-31 07:07:43'),
(39, NULL, 'Added 50 units of Medicine ID: 7 with expiry date 2026-05-12.', NULL, '2024-12-31 07:07:53', '2024-12-31 07:07:53'),
(40, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 07:08:43', '2024-12-31 07:08:43'),
(41, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 07:08:48', '2024-12-31 07:08:48'),
(42, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 07:08:48', '2024-12-31 07:08:48'),
(43, NULL, 'Added 50 units of Medicine ID: 7 with expiry date 2026-02-12.', NULL, '2024-12-31 07:09:05', '2024-12-31 07:09:05'),
(44, NULL, 'Added 50 units of Medicine ID: 8 with expiry date 2026-05-12.', NULL, '2024-12-31 07:09:24', '2024-12-31 07:09:24'),
(45, NULL, 'Added 50 units of Medicine ID: 9 with expiry date 2026-05-12.', NULL, '2024-12-31 07:09:36', '2024-12-31 07:09:36'),
(46, NULL, 'Added  units of Medicine ID: 8 with expiry date .', NULL, '2024-12-31 07:10:08', '2024-12-31 07:10:08'),
(47, NULL, 'Added  units of Medicine ID: 8 with expiry date .', NULL, '2024-12-31 07:10:34', '2024-12-31 07:10:34'),
(48, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2024-12-31 14:24:46', '2024-12-31 14:24:46'),
(49, NULL, 'Added  units of Medicine ID: 12 with expiry date .', NULL, '2025-01-01 04:58:24', '2025-01-01 04:58:24'),
(50, NULL, 'Added  units of Medicine ID:  with expiry date .', NULL, '2025-01-01 05:02:32', '2025-01-01 05:02:32'),
(51, NULL, 'Added  units of Medicine ID: 14 with expiry date .', NULL, '2025-01-02 05:39:44', '2025-01-02 05:39:44'),
(52, NULL, 'Added  units of Medicine ID: 15 with expiry date .', NULL, '2025-01-02 11:07:51', '2025-01-02 11:07:51'),
(53, NULL, 'Added  units of Medicine ID: 16 with expiry date .', NULL, '2025-01-02 17:04:39', '2025-01-02 17:04:39');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `dosage_form` varchar(50) DEFAULT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `price_per_unit` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `medicine_name`, `generic_name`, `dosage_form`, `strength`, `manufacturer_id`, `price_per_unit`, `expiry_date`, `created_at`, `updated_at`) VALUES
(7, 'Paracetamol', 'Acetaminophen', 'Tablet', '500mg', 1, 1.50, '2025-12-31', '2024-12-14 07:03:06', '2024-12-14 07:03:06'),
(8, 'Ibuprofen', 'Ibuprofen', 'Tablet', '400mg', 1, 1.70, '2024-08-31', '2024-12-14 07:03:06', '2024-12-14 07:03:06'),
(9, 'Amoxicillin', 'Amoxicillin', 'Capsule', '250mg', 2, 1.20, '2026-01-15', '2024-12-14 07:03:06', '2024-12-14 07:03:06'),
(10, 'Cetirizine', 'Cetirizine Hydrochloride', 'Tablet', '10mg', 3, 0.30, '2025-05-30', '2024-12-14 07:03:06', '2024-12-14 07:03:06'),
(11, 'Omeprazole', 'Omeprazole', 'Capsule', '20mg', 2, 0.90, '2024-12-01', '2024-12-14 07:03:06', '2024-12-14 07:03:06'),
(14, 'Crocin', 'Acetaminophen', 'Tablet', '250mg', 5, NULL, '2026-10-21', '2025-01-01 14:18:22', '2025-01-01 14:18:22'),
(15, 'Dolo 650', 'Acetaminophen', 'Tablet', '250mg', 6, NULL, '2025-01-18', '2025-01-02 10:39:20', '2025-01-02 10:39:20'),
(16, 'Tremadol', 'Tremadol', 'Tablet', '200mg', 7, NULL, '2026-10-14', '2025-01-02 16:58:46', '2025-01-02 16:58:46');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `status` enum('Indoor','Outdoor') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone_number` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `patient_name`, `age`, `gender`, `contact_info`, `status`, `created_at`, `updated_at`, `phone_number`, `email`) VALUES
(19, 'Deep Patel 1', 21, 'Male', NULL, NULL, '2024-12-29 08:56:24', '2024-12-31 14:32:38', '9512271919', 'pranavpanchal192@gmail.com'),
(20, 'Kushal', 21, 'Male', NULL, NULL, '2024-12-31 14:33:18', '2024-12-31 14:33:26', '2222255556', 'kushal@gmail.com'),
(21, 'Pranav', 21, 'Male', NULL, NULL, '2025-01-02 05:05:04', '2025-01-02 05:05:04', '5885865456', '22bt04090@gsfcuniversity.ac.in'),
(23, 'Dashan Patel', 21, 'Male', NULL, NULL, '2025-01-02 17:02:57', '2025-01-02 17:02:57', '4567891230', 'darshan@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `pharma_companies`
--

CREATE TABLE `pharma_companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` bigint(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pharma_companies`
--

INSERT INTO `pharma_companies` (`id`, `company_name`, `address`, `phone`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Apollo Pharmacy', 'Muktanand, Vadodara', 7600041655, 'info@apollopharma.com', '2024-12-14 07:01:47', '2025-01-01 14:24:48'),
(2, 'MediKart Co.', 'Mani Nagar, Ahmedabad', 9811957360, 'contact@medikart.com', '2024-12-14 07:01:47', '2025-01-01 14:25:49'),
(3, 'Zydus Lifesciences', 'Jarod, Halol', 8779938779, 'support@zydus.com', '2024-12-14 07:01:47', '2025-01-01 14:26:44'),
(5, 'Tata Co.', 'Uttrakhand, India', 2147483647, 'tata@gmail.com', '2025-01-01 14:11:02', '2025-01-01 14:11:02'),
(6, 'Medikart', 'vadodara', 2514362514, 'm@gmail.com', '2025-01-02 10:37:24', '2025-01-02 10:37:24'),
(7, 'Indian Medicines', 'Gujarat, India', 2134567890, 'medicines@indian.com', '2025-01-02 16:57:58', '2025-01-02 16:57:58');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `quantity_issued` int(11) DEFAULT NULL,
  `price_per_unit` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`quantity_issued` * `price_per_unit`) STORED,
  `remarks` text DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `patient_id`, `medicine_id`, `quantity_issued`, `price_per_unit`, `remarks`, `transaction_date`, `created_at`) VALUES
(15, 19, 11, 5, 3.00, NULL, '2024-12-31 06:20:22', '2024-12-31 06:20:22'),
(16, 19, 11, 2, 3.00, NULL, '2024-12-31 06:37:33', '2024-12-31 06:37:33'),
(17, 19, 7, 5, 2.50, NULL, '2024-12-31 06:38:09', '2024-12-31 06:38:09'),
(18, 19, 11, 5, 5.00, NULL, '2024-12-31 07:07:15', '2024-12-31 07:07:15'),
(19, 19, 8, 5, 6.00, NULL, '2024-12-31 07:10:08', '2024-12-31 07:10:08'),
(20, 19, 8, 5, 6.00, NULL, '2024-12-31 07:10:34', '2024-12-31 07:10:34'),
(22, 19, 14, 5, 2.50, NULL, '2025-01-02 05:39:44', '2025-01-02 05:39:44'),
(23, 20, 15, 2, 2.50, NULL, '2025-01-02 11:07:50', '2025-01-02 11:07:50'),
(24, 20, 16, 2, 5.00, NULL, '2025-01-02 17:04:39', '2025-01-02 17:04:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Doctor','Pharmacist','Receptionist') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`, `updated_at`, `email`, `phone`, `reset_token`, `reset_token_expiry`) VALUES
(75, 'TmAOtQ29actwrO75AT/h5g==', '$2y$10$PDNi3.O8qxcoRcTJNPhel.sd6OXNDs902KdB0K0BPEdaT24OrudPm', 'Pharmacist', '2025-01-01 15:56:29', '2025-01-02 08:10:43', 'D71Jqav8UVGa5agTSDPkmA7Bjsde5QQN2YuuArKoyV0=', 'MVeYs8GU8jQMJWlqsH5BXQ==', NULL, NULL),
(76, '7XRAsOhNiNm1ih4Lrs5qhA==', '$2y$10$6pVIUPFz.urs0.Sd3JAIOeyMBiyvEs.0FBsZ7SiwWJitOs8GAKiXu', 'Doctor', '2025-01-01 16:00:52', '2025-01-02 08:09:47', '+6osKUOoQXeQqxf7k00NMb/BtAzf6JRX0xEnrGklAx4=', 'Wm3br29/yMPJs0/DVI/YhQ==', NULL, NULL),
(78, 'R7cAXb0iFNUSOq063nyezQ==', '$2y$10$C81heNdmpzVmCWX7SVzfQOqZLveaO/Dq/TGV4o5h9kNemTzKkUENa', 'Receptionist', '2025-01-01 16:11:59', '2025-01-02 08:10:55', 'F141AbuPiUM+1XAhyBcJjpkt6rSoP5cU2rQZ/UnBRRk=', 'MVeYs8GU8jQMJWlqsH5BXQ==', NULL, NULL),
(80, '9xz4vsbZLF9/4cpcZz4bPA==', '$2y$10$nEXx3GR/SsKV3sBUnifKge9TXA0fpamB02pECo5cTcm.UUyHTTX9a', 'Pharmacist', '2025-01-02 09:42:31', '2025-01-02 09:43:28', 'vF2fD0A6VPodG8oHiXxvSvaKAGPrVeY1oydX8hBd5hU=', 'Wm3br29/yMPJs0/DVI/YhQ==', NULL, NULL),
(81, 'S7DLnwbB6vYL0Cwqaudmww==', '$2y$10$2MQXVR6jEyqtVO68wGQB9.ApeSvHbvMezu6MAUR7wSZ6V5ysPgIwC', 'Admin', '2025-01-02 09:45:28', '2025-01-02 09:45:28', 'AMgmjvwg2H0wXJMO0dxmsAp/fJdzsjoFwpSuAreVO8Y=', 'Wm3br29/yMPJs0/DVI/YhQ==', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `appointments_ibfk_1` (`patient_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manufacturer_id` (`manufacturer_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `pharma_companies`
--
ALTER TABLE `pharma_companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `fk_patient_id` (`patient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `pharma_companies`
--
ALTER TABLE `pharma_companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `pharma_companies` (`id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicines`
--
ALTER TABLE `medicines`
  ADD CONSTRAINT `medicines_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `pharma_companies` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

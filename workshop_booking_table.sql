-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 03:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `workshop_booking_table`
--

-- --------------------------------------------------------

--
-- Table structure for table `detailtable`
--

CREATE TABLE `detailtable` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `seats` varchar(20) NOT NULL,
  `img` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detailtable`
--

INSERT INTO `detailtable` (`id`, `name`, `seats`, `img`) VALUES
(1, 'โซฟา', '4 ที่นั่ง', 'sofa.jpg'),
(2, 'บาร์', '2 ที่นั่ง', 'bar.jpg'),
(3, 'ริมหน้าต่าง', '6 ที่นั่ง', 'window.jpg'),
(4, 'ข้างเวที', '3 ที่นั่ง', 'stage.jpg'),
(5, 'โปสเตอร์โซน', '5 ที่นั่ง', 'poster.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_booking`
--

CREATE TABLE `tbl_booking` (
  `no` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `booking_name` varchar(100) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `price` decimal(10,2) DEFAULT 150.00,
  `booking_phone` varchar(10) NOT NULL,
  `slip_img` varchar(255) DEFAULT NULL,
  `booking_staff` varchar(100) DEFAULT 'ผู้ใช้ทั่วไป',
  `dateCreate` datetime DEFAULT current_timestamp(),
  `payment_status` enum('pending','paid') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_booking`
--

INSERT INTO `tbl_booking` (`no`, `table_id`, `booking_name`, `booking_date`, `booking_time`, `price`, `booking_phone`, `slip_img`, `booking_staff`, `dateCreate`, `payment_status`) VALUES
(501, 1, 'Jack', '2025-10-01', '19:00:00', 150.00, '0812345678', NULL, 'พนักงาน', '2025-10-20 11:02:46', 'paid'),
(502, 2, 'Bam', '2025-10-01', '19:30:00', 150.00, '0812345678', NULL, 'พนักงาน', '2025-10-20 11:02:46', 'paid'),
(503, 3, 'Dream', '2025-10-01', '20:00:00', 150.00, '0812345678', NULL, 'พนักงาน', '2025-10-20 11:02:46', 'paid'),
(504, 2, 'primmie', '2025-10-20', '11:04:00', 150.00, '1111111111', 'uploads/slips/slip_504_1760933131.webp', 'ผู้ใช้ทั่วไป', '2025-10-20 11:04:16', 'pending'),
(505, 3, 'jack', '2025-10-20', '13:57:00', 150.00, '1111111111', NULL, 'ผู้ใช้ทั่วไป', '2025-10-20 13:57:01', ''),
(506, 3, 'jack', '2025-11-19', '08:25:00', 150.00, '2222222222', 'uploads/slips/slip_506_1763515536.webp', 'ผู้ใช้ทั่วไป', '2025-11-19 08:25:28', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_receipt`
--

CREATE TABLE `tbl_receipt` (
  `id` int(11) NOT NULL,
  `booking_no` int(11) NOT NULL,
  `receipt_code` varchar(50) NOT NULL,
  `receipt_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT 150.00,
  `slip_img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_receipt`
--

INSERT INTO `tbl_receipt` (`id`, `booking_no`, `receipt_code`, `receipt_date`, `total_amount`, `slip_img`) VALUES
(1, 501, 'RCP-202510010001', '2025-10-01 19:00:00', 150.00, NULL),
(2, 502, 'RCP-202510010002', '2025-10-01 19:30:00', 150.00, NULL),
(3, 503, 'RCP-202510010003', '2025-10-01 20:00:00', 150.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_table`
--

CREATE TABLE `tbl_table` (
  `id` int(11) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `table_status` tinyint(1) DEFAULT 0 COMMENT '0=ว่าง, 1=ไม่ว่าง, 2=รออนุมัติ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_table`
--

INSERT INTO `tbl_table` (`id`, `table_name`, `table_status`) VALUES
(1, 'โซฟา', 0),
(2, 'บาร์', 2),
(3, 'ริมหน้าต่าง', 2),
(4, 'ข้างเวที', 0),
(5, 'โปสเตอร์โซน', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `phone`, `password`, `role`, `created_at`) VALUES
(1, '0812345678', '$2y$10$FunBdJc.2P7WsavGwVi1D.IYjIrEuT5jJarYFbO1rQJedSRrF9dXC', 'admin', '2025-10-20 04:02:46'),
(2, '0899999999', '$2y$10$qIyssvFVoM/yevXKkM5iF.FmeckdeDVUEbla7uCSw7QjUU49cwsCK', 'user', '2025-10-20 04:02:46'),
(3, '0642064899', '$2y$10$Hq9F8g6JzHDGYZfdL2DiKOcQt4jKFcHuH38qBGpt4lDwX1KKNILGy', 'user', '2025-10-20 04:02:46'),
(4, '1111111111', '$2y$10$euxm44eXW63.xUSeyZiv5u9aIEcwWHj9t/yEDENk/G.LehMmQ7Og.', 'user', '2025-10-20 04:03:27'),
(5, '2222222222', '$2y$10$/UsBy8Eqw8e352uDwt/R5OM.mrK0D46Vm0lk7O3GTdDjyNcqcJS46', 'user', '2025-11-19 01:25:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detailtable`
--
ALTER TABLE `detailtable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_booking`
--
ALTER TABLE `tbl_booking`
  ADD PRIMARY KEY (`no`),
  ADD KEY `fk_booking_table` (`table_id`);

--
-- Indexes for table `tbl_receipt`
--
ALTER TABLE `tbl_receipt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_receipt_booking` (`booking_no`);

--
-- Indexes for table `tbl_table`
--
ALTER TABLE `tbl_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detailtable`
--
ALTER TABLE `detailtable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_booking`
--
ALTER TABLE `tbl_booking`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=507;

--
-- AUTO_INCREMENT for table `tbl_receipt`
--
ALTER TABLE `tbl_receipt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_table`
--
ALTER TABLE `tbl_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_booking`
--
ALTER TABLE `tbl_booking`
  ADD CONSTRAINT `fk_booking_table` FOREIGN KEY (`table_id`) REFERENCES `tbl_table` (`id`);

--
-- Constraints for table `tbl_receipt`
--
ALTER TABLE `tbl_receipt`
  ADD CONSTRAINT `fk_receipt_booking` FOREIGN KEY (`booking_no`) REFERENCES `tbl_booking` (`no`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

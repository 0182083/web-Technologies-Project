-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2026 at 01:21 PM
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
-- Database: `user_reg`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ground_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `ground_id`, `booking_date`, `created_at`, `is_active`) VALUES
(1, 4, 1, '2026-01-03', '2025-12-31 18:50:11', 1),
(2, 1, 1, '2026-01-09', '2025-12-31 19:08:13', 1),
(3, 1, 1, '2026-01-01', '2025-12-31 20:22:41', 1),
(4, 1, 1, '2026-01-04', '2025-12-31 20:32:14', 1),
(5, 5, 1, '2026-01-22', '2025-12-31 21:10:58', 0),
(6, 5, 1, '2026-01-29', '2025-12-31 21:12:51', 0),
(7, 1, 1, '2026-01-10', '2026-01-01 10:35:57', 1),
(8, 1, 1, '2026-01-31', '2026-01-04 13:48:30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `ground_name` varchar(255) NOT NULL,
  `ground_location` varchar(255) NOT NULL,
  `facility_type` varchar(100) NOT NULL,
  `available_duration` varchar(100) NOT NULL,
  `fees` decimal(10,2) NOT NULL,
  `ground_picture` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `ground_name`, `ground_location`, `facility_type`, `available_duration`, `fees`, `ground_picture`, `created_at`) VALUES
(1, 'NDE Sports Facility', 'RCGM+67, Rd - 5, Dhaka 1229', 'Football Ground', '8:30 AM - 10:00 PM', 5000.00, 'uploads/NDE_sports_filed.jpg', '2025-12-31 08:50:31'),
(2, 'Sher E Bangla National Cricket Stadium', 'Mirpur Rd, Dhaka 1216, R947+RC Dhaka', 'Cricket Ground', '8:30 AM - 10:00 PM', 15000.00, 'uploads/sher-e-bangla-national-stadium1.jpg', '2026-01-19 12:10:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `role` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `dob`, `email`, `username`, `role`, `password`, `created_at`, `is_active`) VALUES
(1, 'Tawfique', 'Ahmed', '2002-12-12', 'tawfiqueahmed2025@gmail.com', 'Tawfique', 'User', '$2y$10$ehkVchNbpdgrM5Wa1mr5v.zMO5i26Md0MR2voRDSh/DmfaVwQVvhW', '2025-12-30 10:51:31', 1),
(2, 'Bruce', 'Wayne', '1999-12-12', 'bruce69@gmail.com', 'BM', 'Admin', '$2y$10$Jy6Z2FKObr5gfsJ6X2eMQ..AF77Wd8/ipOdWvg/CBqEmdz5poTGcK', '2025-12-30 13:16:49', 1),
(3, 'Alfred', 'Pennyworth', '1993-08-16', 'thaddeus00@gmail.com', 'Thaddeus', 'Admin', '$2y$10$hvt6BFvNeMeQo6iDGg8luexUefFfFEgWyHDZacG.UvnS8SEayKKGe', '2025-12-31 09:26:48', 1),
(4, 'Barry', 'Alen', '1999-03-14', 'ballen111@gmail.com', 'Flash', 'User', '$2y$10$au47TTtwRKmTqLHCmasO/eaqT2ejmTAskp1n5ZY8HmGTjNVOBE2aK', '2025-12-31 09:34:30', 1),
(5, 'Arthur', 'Curry', '1999-01-29', 'Acurry333@gmail.com', 'Aquaman', 'Admin', '$2y$10$oLk1EWi/vipAjNadOsv0D.yg27KiOUTlmy0I8BzCtPt57V6sm6M.G', '2025-12-31 21:10:32', 0),
(6, 'Victor', 'Stone', '2000-10-26', 'cyb1010@gmail.com', 'Cyborg', 'Manager', '$2y$10$pWgfi6oBNBRs/7NxPbygHOkGqeDG.x6d0JGjHqNIP26Ij2bGB5GTy', '2026-01-16 07:46:35', 1),
(7, 'Clark', 'Kent', '1998-06-18', 'sup222@gmail.com', 'Superman', 'User', '$2y$10$0Pkf6V77XebMwMUqHlIqVONvzs.iZgrv6SHT9AGKYK5i/qJJAH6t6', '2026-01-16 08:50:05', 1),
(8, 'Kent', 'Nelson', '1980-05-01', 'dF8999@gmail.com', 'Dr Fate', 'User', '$2y$10$jPdsQxkmXfe0sbianVQzcONwpCz30nJSRd8fjkQ7wPERrZWSC57Yq', '2026-01-16 09:00:59', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `Facility_id_cascade` (`ground_id`),
  ADD KEY `user_id_cascade` (`user_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQUE1` (`email`),
  ADD UNIQUE KEY `UNIQUE2` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `Facility_id_cascade` FOREIGN KEY (`ground_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_id_cascade` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

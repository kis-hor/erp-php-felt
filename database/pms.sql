-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Sep 12, 2024 at 02:57 AM
-- Server version: 5.7.39
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pms`
--

-- --------------------------------------------------------

--
-- Table structure for table `artisans`
--

CREATE TABLE `artisans` (
  `ArtisanID` int(11) NOT NULL,
  `ArtisanName` varchar(100) NOT NULL,
  `Specialization` varchar(255) NOT NULL,
  `JoinDate` date NOT NULL,
  `DepartmentID` int(11) DEFAULT NULL,
  `is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '0:not delete 1:delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `artisans`
--

INSERT INTO `artisans` (`ArtisanID`, `ArtisanName`, `Specialization`, `JoinDate`, `DepartmentID`, `is_delete`) VALUES
(1, 'Halee Torres', 'Ducimus magna error', '2011-10-15', 2, 1),
(2, 'Akeem Castro', 'Libero ut consequatu', '1970-09-01', 1, 1),
(3, 'Dante Espinoza', 'Voluptatem unde numq', '2012-08-29', 3, 1),
(4, 'Velma Conley', 'Ex odit deserunt vel', '2024-08-23', 2, 1),
(5, 'Idona Reynolds', 'Esse consequatur Si', '1982-05-27', 2, 1),
(6, 'Nirmala', 'felt heart, star, carrot', '2018-05-28', 1, 0),
(7, 'Ratna kadayat', 'Felt heart, felt ginger bread', '2017-06-29', 5, 0),
(8, 'Test', 'carrot', '2024-09-02', 2, 0),
(9, 'Sarita Batala', 'felt seat pad', '2024-01-31', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `DepartmentID` int(11) NOT NULL,
  `DepartmentName` varchar(100) NOT NULL,
  `is_delete` int(11) DEFAULT '0' COMMENT '0:not delete 1:delete',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updateed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`DepartmentID`, `DepartmentName`, `is_delete`, `created_at`, `updateed_at`) VALUES
(1, 'Large Felting', 0, '2024-08-21 13:26:31', '2024-08-21 13:26:31'),
(2, 'Needling', 0, '2024-08-22 10:19:46', '2024-08-22 10:19:46'),
(3, 'Stitching', 0, '2024-08-22 10:20:00', '2024-08-22 10:20:00'),
(5, 'Small Felting ', 0, '2024-08-28 11:44:26', '2024-08-28 11:44:26');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `CustomerName` varchar(100) NOT NULL,
  `Product` text NOT NULL,
  `Quantity` int(11) NOT NULL,
  `WagesPerPiece` decimal(10,2) NOT NULL,
  `ProductionDueDate` date NOT NULL,
  `ArtisanID` int(11) DEFAULT NULL,
  `DepartmentID` int(11) DEFAULT NULL,
  `Status` varchar(50) NOT NULL DEFAULT 'inprocess',
  `DispatchDate` datetime DEFAULT NULL,
  `DispatchMethod` varchar(50) DEFAULT NULL,
  `is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '0:not delete 1:delte'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `CustomerName`, `Product`, `Quantity`, `WagesPerPiece`, `ProductionDueDate`, `ArtisanID`, `DepartmentID`, `Status`, `DispatchDate`, `DispatchMethod`, `is_delete`) VALUES
(1, 'Octavia Briggs', 'Voluptatem aut quasi', 6, '15.00', '2017-06-04', 1, 2, 'Dispatch', NULL, NULL, 0),
(2, 'Jaime Jones', 'Adipisicing officia ', 524, '43.00', '1990-10-05', 1, 2, 'Dispatch', '2024-08-24 00:00:00', 'Office Vehical', 0),
(3, 'Walker Mckay', 'Libero temporibus qu', 997, '2.00', '1991-11-18', 1, 2, 'Dispatch', '2024-08-29 00:00:00', 'Office Vehical', 0),
(4, 'Jana Anderson', 'Dolorem laborum et r', 100, '93.00', '2024-08-31', 4, 1, 'Dispatch', '2024-08-29 00:00:00', 'Office Vehical', 0),
(5, 'Brendan Irwin', 'Alias modi voluptate', 357, '20.00', '2024-08-31', 1, 1, 'Dispatch', '2024-09-03 00:00:00', 'Office Runner', 0),
(6, 'Megan Benson', 'Felt heart 4cm- color #3 ', 100, '2.00', '2024-08-29', 6, 5, 'Dispatch', '2024-09-01 00:00:00', 'Office Vehical', 0),
(7, 'Rachel', 'Felt carrot 8cm - #40 in body and #35 in leaf', 100, '2.00', '2024-09-03', 6, 5, 'Dispatch', '2024-09-04 00:00:00', 'Office Runner', 0),
(8, 'Meridith ', 'Felt heart 5cm in color #4', 200, '2.00', '2024-09-03', 7, 5, 'Dispatch', '2024-09-03 00:00:00', 'Office Vehical', 0),
(9, 'Test', 'Test', 100, '2.00', '2024-09-02', 8, 2, 'inprocess', NULL, NULL, 0),
(10, 'Teramax', 'felt heart 8cm color #30', 100, '2.00', '2024-09-06', 7, 5, 'Dispatch', '2024-09-05 00:00:00', 'Office Vehical', 0),
(11, 'John Doe', 'Felt Dog Bed, #01, Size: 50cm', 99, '100.00', '2024-09-12', 7, 5, 'Dispatch', '2024-09-11 00:00:00', 'Office Vehicle', 0),
(12, 'Max Lenon', 'Felt Cat Cave, #43, Size: 50cm', 50, '100.00', '2024-09-14', 6, 1, 'Dispatch', '2024-09-11 00:00:00', 'Office Vehicle', 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductSize` varchar(50) DEFAULT NULL,
  `ProductColor` varchar(50) DEFAULT NULL,
  `ProductWeight` varchar(50) DEFAULT NULL,
  `ProductImage` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `ProductName`, `ProductSize`, `ProductColor`, `ProductWeight`, `ProductImage`, `created_at`) VALUES
(2, 'Felt Cat Cave', '50cm', '#38', '800', 'Screenshot 2024-08-16 at 11.20.01.png', '2024-09-11 16:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `quality_control`
--

CREATE TABLE `quality_control` (
  `QualityControlID` int(11) NOT NULL,
  `ApprovedQuantity` int(11) NOT NULL,
  `RejectedQuantity` int(11) NOT NULL,
  `ApprovalDate` date NOT NULL,
  `OrderID` int(11) NOT NULL,
  `ApprovalBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quality_control`
--

INSERT INTO `quality_control` (`QualityControlID`, `ApprovedQuantity`, `RejectedQuantity`, `ApprovalDate`, `OrderID`, `ApprovalBy`) VALUES
(1, 6, 0, '2024-08-24', 1, 1),
(2, 520, 4, '2024-08-30', 2, 1),
(3, 900, 97, '2024-08-29', 3, 1),
(4, 100, 0, '2024-08-29', 4, 1),
(5, 100, 0, '2024-08-30', 6, 1),
(6, 357, 0, '2024-08-31', 5, 1),
(7, 120, 80, '2024-09-01', 8, 1),
(8, 50, 50, '2024-09-02', 7, 11),
(9, 100, 0, '2024-09-05', 10, 1),
(10, 99, 0, '2024-09-11', 11, 1),
(11, 50, 0, '2024-09-10', 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('Admin','Manager','Quality Control','Dispatch','Accounts') NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:not delete 1:delete',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updateed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Password`, `Role`, `is_delete`, `created_at`, `updateed_at`) VALUES
(1, 'admin', 'kishor', 'Admin', 0, '2024-08-21 12:16:38', '2024-08-21 12:16:38'),
(2, 'nikita2', '$2y$10$BI7d5CqKxluOGzA6v.vXD.9EPcuGOhbzOfbIJNJI.BQK9s4Hrhqp6', 'Manager', 1, '2024-08-21 12:24:44', '2024-08-21 12:24:44'),
(3, 'UserDispatch', '$2y$10$71xKXp1DQ4JiC4SOLsq0oOnI3Z5ZVFrCi2Xp7xoww4RSspCuGbCRy', 'Dispatch', 1, '2024-08-21 12:25:05', '2024-08-21 12:25:05'),
(4, 'danteespinoza', '$2y$10$5rTnTq5a41ZceMYFznhe0uJ7x3BaBbqxSr3E8NZX4b/iB3X0lgRCC', 'Accounts', 1, '2024-08-21 12:25:21', '2024-08-21 12:25:21'),
(5, 'jiya', '$2y$10$KyowRfqOZqS8ajJpkLKS9.8xT3hr20bsm5biMef1bAiyGwng72jhe', 'Quality Control', 1, '2024-08-23 08:15:07', '2024-08-23 08:15:07'),
(6, '\'\'\'\'\'\'\'', '$2y$10$5buYdDlCadcb6nZO9kHz0OEEv/rNFxbdfGk1jmzC3H96NVaNk3Sxy', 'Quality Control', 1, '2024-08-24 13:33:34', '2024-08-24 13:33:34'),
(7, 'abc', '$2y$10$3fedBvnnoTQJgr6lz2xlx.AEUiBce7iNt3FUXKLV56rY38HWwwnaW', 'Quality Control', 1, '2024-08-28 10:04:01', '2024-08-28 10:04:01'),
(8, 'raj', '$2y$10$8js7vO0h/Xq0QW6owpletu4CsLTXnwIweCDgvUzqTuVDK8mV/rRi6', 'Quality Control', 1, '2024-08-28 11:49:10', '2024-08-28 11:49:10'),
(9, 'nikita', '$2y$10$DSfeHAFEwFrJwm3JY3iRUu3cOEgb10fJYJUv89NK5uupcTv58S1Ae', 'Manager', 1, '2024-08-28 11:54:01', '2024-08-28 11:54:01'),
(10, 'reteb', '$2y$10$7xdHA2zQ5iNFOHbnYIRt/uGwU6c0kjquG7Tb62Km7UmXgEigMdsn.', 'Quality Control', 1, '2024-08-29 09:34:23', '2024-08-29 09:34:23'),
(11, 'jina', '$2y$10$IrCGtcE/pxWjlxd7Wlw2CuH.y0zKIac3v2hkLhBIrf8/Eb56K/o7G', 'Quality Control', 0, '2024-09-01 11:57:50', '2024-09-01 11:57:50'),
(12, 'Manager', '$2y$10$Eq4FZJRSsLFJM0bSRn42XOoJK5HVz.H5X0wdVuK30u4IEbdl6tJZW', 'Manager', 1, '2024-09-01 11:58:34', '2024-09-01 11:58:34'),
(13, 'ranjan', '$2y$10$HmqzFkgZRlStjpfJZdY34eEkTCfqxvtNMx7TRr.u2BeN.122VnFmi', 'Accounts', 0, '2024-09-01 12:00:02', '2024-09-01 12:00:02'),
(14, 'Suman', '$2y$10$Ztj6sfLwqZswm3MdRaO7ZOS174VNF4wwf4ubkcFCGAKnxL8MLB3CK', 'Manager', 0, '2024-09-05 07:58:56', '2024-09-05 07:58:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `artisans`
--
ALTER TABLE `artisans`
  ADD PRIMARY KEY (`ArtisanID`),
  ADD KEY `DepartmentID` (`DepartmentID`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`DepartmentID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `ArtisanID` (`ArtisanID`),
  ADD KEY `DepartmentID` (`DepartmentID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`);

--
-- Indexes for table `quality_control`
--
ALTER TABLE `quality_control`
  ADD PRIMARY KEY (`QualityControlID`),
  ADD KEY `FK_Order_QualityControl` (`OrderID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `artisans`
--
ALTER TABLE `artisans`
  MODIFY `ArtisanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `DepartmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quality_control`
--
ALTER TABLE `quality_control`
  MODIFY `QualityControlID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `artisans`
--
ALTER TABLE `artisans`
  ADD CONSTRAINT `artisans_ibfk_1` FOREIGN KEY (`DepartmentID`) REFERENCES `departments` (`DepartmentID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`ArtisanID`) REFERENCES `artisans` (`ArtisanID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`DepartmentID`) REFERENCES `departments` (`DepartmentID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `quality_control`
--
ALTER TABLE `quality_control`
  ADD CONSTRAINT `FK_Order_QualityControl` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

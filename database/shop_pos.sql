-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 12:10 PM
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
-- Database: `shop_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `deliver`
--

CREATE TABLE `deliver` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `delivery_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliver`
--

INSERT INTO `deliver` (`id`, `transaction_id`, `status`, `delivery_date`) VALUES
(1, 2, 'pending', NULL),
(2, 3, 'pending', NULL),
(3, 4, 'pending', NULL),
(4, 4, 'pending', NULL),
(5, 4, 'pending', NULL),
(6, 5, 'pending', NULL),
(7, 6, 'pending', NULL),
(8, 7, 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pos_transaction`
--

CREATE TABLE `pos_transaction` (
  `transaction_id` int(11) NOT NULL,
  `customer_name` varchar(50) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pos_transaction`
--

INSERT INTO `pos_transaction` (`transaction_id`, `customer_name`, `transaction_date`) VALUES
(2, 'Asitha Silva', '2025-04-20 09:56:52'),
(3, 'crono baneee', '2025-04-20 09:59:33'),
(4, 'crono baneee', '2025-04-20 09:59:53'),
(5, 'Sachin Tendulkar', '2025-04-20 10:01:44'),
(6, 'Bill Gates', '2025-04-20 10:03:35'),
(7, 'crono', '2025-04-20 10:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `pos_transaction_details`
--

CREATE TABLE `pos_transaction_details` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(3) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `product_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pos_transaction_details`
--

INSERT INTO `pos_transaction_details` (`id`, `transaction_id`, `product_id`, `quantity`, `total`, `product_name`) VALUES
(3, 2, 34, 1, 1500.00, 'First Bag'),
(4, 3, 40, 1, 1650.00, 'Handbag'),
(5, 4, 41, 1, 2300.00, 'Laptop Bag'),
(6, 4, 40, 1, 1650.00, 'Handbag'),
(7, 4, 45, 1, 1200.00, 'First Aid Bag'),
(8, 5, 35, 1, 500.00, 'School Bag'),
(9, 6, 34, 1, 1500.00, 'First Bag'),
(10, 7, 43, 1, 1000.00, 'Sports Bag');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `deliver`
--
ALTER TABLE `deliver`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pos_transaction`
--
ALTER TABLE `pos_transaction`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `pos_transaction_details`
--
ALTER TABLE `pos_transaction_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deliver`
--
ALTER TABLE `deliver`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pos_transaction`
--
ALTER TABLE `pos_transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pos_transaction_details`
--
ALTER TABLE `pos_transaction_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pos_transaction_details`
--
ALTER TABLE `pos_transaction_details`
  ADD CONSTRAINT `pos_transaction_details_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `pos_transaction` (`transaction_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

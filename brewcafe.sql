-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2026 at 05:10 PM
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
-- Database: `brewcafe`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'Coffee',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `category`, `is_available`, `created_at`, `image_url`) VALUES
(15, 'Americano', '', 80.00, 'Coffee', 1, '2026-10-03 13:26:37', 'uploads/coffee/americano.jpg'),
(16, 'Salted Caramel Latte', '', 110.00, 'Coffee', 1, '2026-10-03 13:26:37', 'uploads/coffee/saltedcaramellatte.jpg'),
(17, 'Spanish Latte', '', 100.00, 'Coffee', 1, '2026-10-03 13:26:37', 'uploads/coffee/spanishlatte.jpg'),
(18, 'Chocolate Cookie Frappe', '', 145.00, 'Frappe', 1, '2026-10-03 13:26:37', 'uploads/frappe/chocolatecookie.jpg'),
(19, 'Matcha Frappe', '', 130.00, 'Frappe', 1, '2026-10-03 13:26:37', 'uploads/frappe/matcha.jpg'),
(20, 'Strawberry Cheesecake Frappe', '', 130.00, 'Frappe', 1, '2026-10-03 13:26:37', 'uploads/frappe/strawberrycheesecake.jpg'),
(21, 'Kiwi Refresher', '', 80.00, 'Refreshers', 1, '2026-10-03 13:26:37', 'uploads/refreshers/kiwi.jpg'),
(22, 'Lemonade', '', 80.00, 'Refreshers', 1, '2026-10-03 13:26:37', 'uploads/refreshers/lemonade.jpg'),
(23, 'Caffe Latte', '', 100.00, 'Coffee', 1, '2026-10-03 13:27:35', 'uploads/coffee/cafelatte.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT 0,
  `guest_name` varchar(50) DEFAULT NULL,
  `order_code` varchar(10) DEFAULT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `claimed_at` timestamp NULL DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `is_guest`, `guest_name`, `order_code`, `status`, `total`, `payment_status`, `created_at`, `updated_at`, `claimed_at`, `guest_phone`) VALUES
(12, NULL, 1, 'shanshan', 'BC-KH2G', 'completed', 80.00, 'paid', '2026-10-04 14:11:59', '2026-10-04 14:57:28', '2026-10-04 14:57:28', '09460722728');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `item_name` varchar(120) NOT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `item_name`, `qty`, `unit_price`) VALUES
(14, 12, 15, 'Americano', 1, 80.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','staff','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`, `is_active`) VALUES
(4, 'admin', 'admin@test.com', '$2y$10$0siqZMg5oh8AWKGgtUdvs.H6NKtBqcIzIRLt6GwbeGxULRwEbO4VW', 'admin', '2026-09-21 10:48:59', 1),
(7, 'Shanshan', 'staff@email.com', '$2y$10$Jt/hfw71Ifs11YcdsQzPUe18DTawtqKeQgPmfjrm//3Y3ryn.iK0y', 'staff', '2026-09-30 15:35:34', 1),
(8, 'customer', 'customer@gmail.com', '$2y$10$GFQxYbo8gBqm1LhvvYFAr.u73un.u4PO.gZ7GJ/BcqW6aSMMU.e2u', 'customer', '2026-10-02 14:44:39', 1),
(9, 'CHRISTIAN DELA CRUZ ORZALES', 'staff2@gmail.com', '$2y$10$aAHy//a43rasMhg0Y6IsXOGF6592ePZ1woHgEYETBOAOHu7Jab27i', 'staff', '2026-10-04 14:55:31', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_items_menu` (`menu_item_id`);

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
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_menu` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

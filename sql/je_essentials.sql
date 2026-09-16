-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 16, 2026 at 08:21 PM
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
-- Database: `je_essentials`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Unread','Read') NOT NULL DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Shakirah Tampos', 'shakirahtampos@gmail.com', '[Inquiry About the Place]', 'Where exactly in the calindagan area is your office?', 'Unread', '2026-09-15 07:10:08');

-- --------------------------------------------------------

--
-- Table structure for table `login_tokens`
--

CREATE TABLE `login_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_tokens`
--

INSERT INTO `login_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `created_at`) VALUES
(9, 1, '9f87b8c3f588faca15338e41c1971408724208ae6d7ebd0b34c4570c50069a8e', '2026-10-16 19:57:03', '2026-09-16 17:57:03');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `order_id`, `message`, `is_read`, `created_at`) VALUES
(1, 10, 11, 'Your order #11 has been updated to Completed.', 1, '2026-09-15 06:58:28'),
(2, 10, 12, 'Your order #12 has been updated to Pending.', 0, '2026-09-16 18:19:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','cancelled','completed') NOT NULL DEFAULT 'pending',
  `total` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `full_name`, `email`, `phone`, `address`, `payment_method`, `payment_proof`, `status`, `total`, `created_at`, `updated_at`) VALUES
(11, 10, 'Jujille Angela Eullaran', 'jujillea@gmail.com', '09929858661', '1002, Perdices Street, Barangay Calindagan, Dumaguete City, Negros Oriental, 6200', 'Cash on Delivery', NULL, 'completed', 478.00, '2026-09-15 01:43:01', '2026-09-15 14:58:28'),
(12, 10, 'Mylen Canete Letchido', 'mylenletchido3@gmail.com', '09918189995', 'Mabinay, Negros Oriental', 'Cash on Delivery', NULL, 'pending', 278.00, '2026-09-17 02:19:27', '2026-09-17 02:19:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `name`, `size`, `price`, `quantity`) VALUES
(4, 11, 1, 'Cream Moisturizer', '50ml', 199.00, 2),
(5, 12, 4, 'Rejuvinating Serum', '20ml', 99.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sizes` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `sizes`, `image`, `created_at`) VALUES
(2, 'Whitening Lotion', 'Achieve a brighter, more radiant-looking complexion with our Whitening Lotion, formulated to moisturize the skin while helping improve the appearance of uneven skin tone. Its lightweight, fast-absorbing formula leaves your skin feeling soft, smooth, and refreshed, making it ideal for everyday use.', 150.00, '{\"50ml\":150,\"300ml\":499}', 'images/first-product.png', '2026-09-16 18:07:02'),
(3, 'Cream Moisturizer', 'Keep your skin deeply moisturized and feeling nourished with our Cream Moisturizer, designed to help replenish moisture and support soft, smooth skin. Its rich yet comfortable formula leaves your skin feeling hydrated, supple, and refreshed throughout the day.', 199.00, '{\"80ml\":199,\"160ml\":299}', 'images/second-product.png', '2026-09-16 18:08:10'),
(4, 'Rejuvinating Serum', 'Refresh and revitalize your skin with our Rejuvenating Serum, formulated to hydrate and help improve the appearance of dull, tired-looking skin. Its lightweight formula absorbs easily, leaving your skin feeling soft, smooth, and refreshed for a healthy-looking glow.', 99.00, '{\"20ml\":99,\"50ml\":199}', 'images/fouth-product.png', '2026-09-16 18:09:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `middle_name`, `last_name`, `email`, `address`, `password`, `phone`, `created_at`, `profile_pic`, `is_admin`) VALUES
(1, 'essential_admin', 'Jujille Angela', '', 'Eullaran', 'jujillea@gmail.com', 'none', '$2y$10$bJlbQ3KSVBJbLD0QIaqAmufFBPA8MjyHUPfbl2/odm6YS9efI0sNy', '09929858661', '2026-09-14 17:18:32', NULL, 1),
(10, 'msmylenn_30', 'Mylen', 'Canete', 'Letchido', 'mylenletchido3@gmail.com', 'Mabinay, Negros Oriental', '$2y$10$B4o5rH0cDU14VhsOFkC0Wuzs1XWm9P7y4Jn0PDSSMhypuhuxtfPEy', '09918189995', '2026-09-14 17:41:51', NULL, 0),
(11, 'ebony', 'Yvonne', 'Ozoa', 'Eullaran', 'ysobrevinas@gmail.com', 'Calindagan, Dumaguete City', '$2y$10$mC7Hr6HIvDbmKXwo7XcXq.xMjMv40ay5wlnCPOcpxEIkAnmLaBSCy', '09532535925', '2026-09-15 07:33:05', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`),
  ADD KEY `fk_notifications_order` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_tokens`
--
ALTER TABLE `login_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD CONSTRAINT `login_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

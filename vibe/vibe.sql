-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 05:50 AM
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
-- Database: `vibe`
--

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_username` varchar(255) NOT NULL,
  `followed_username` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`id`, `follower_username`, `followed_username`, `created_at`) VALUES
(11, 'Raymond', 'Ralph', '2025-05-16 03:38:55'),
(15, 'Ralph', 'Raymond', '2025-05-16 03:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `hearts`
--

CREATE TABLE `hearts` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hearts`
--

INSERT INTO `hearts` (`id`, `post_id`, `username`, `created_at`) VALUES
(19, 74, 'Raymond', '2025-05-16 03:10:44'),
(20, 73, 'Raymond', '2025-05-16 03:10:45'),
(21, 72, 'Raymond', '2025-05-16 03:10:46'),
(22, 78, 'Ruben', '2025-05-16 03:16:04'),
(23, 77, 'Ruben', '2025-05-16 03:16:07'),
(24, 76, 'Ruben', '2025-05-16 03:16:13'),
(25, 75, 'Ruben', '2025-05-16 03:16:15'),
(26, 74, 'Ruben', '2025-05-16 03:16:16'),
(27, 73, 'Ruben', '2025-05-16 03:16:18'),
(28, 72, 'Ruben', '2025-05-16 03:16:19'),
(29, 82, 'Ralph', '2025-05-16 03:39:59'),
(30, 81, 'Ralph', '2025-05-16 03:40:00'),
(31, 80, 'Ralph', '2025-05-16 03:40:01'),
(32, 79, 'Ralph', '2025-05-16 03:40:03'),
(34, 77, 'Ralph', '2025-05-16 03:40:07'),
(35, 76, 'Ralph', '2025-05-16 03:40:10'),
(36, 75, 'Ralph', '2025-05-16 03:40:13'),
(40, 78, 'Ralph', '2025-05-16 03:43:40');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `recipient_username` varchar(255) NOT NULL,
  `sender_username` varchar(255) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `type` enum('heart','follow') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `recipient_username`, `sender_username`, `post_id`, `type`, `created_at`, `is_read`) VALUES
(11, 'Raymond', 'Ruben', 78, 'heart', '2025-05-16 03:16:04', 1),
(12, 'Raymond', 'Ruben', 77, 'heart', '2025-05-16 03:16:07', 1),
(13, 'Raymond', 'Ruben', 76, 'heart', '2025-05-16 03:16:13', 1),
(14, 'Raymond', 'Ruben', 75, 'heart', '2025-05-16 03:16:15', 1),
(15, 'Ralph', 'Ruben', 74, 'heart', '2025-05-16 03:16:16', 1),
(16, 'Ralph', 'Ruben', 73, 'heart', '2025-05-16 03:16:18', 1),
(17, 'Ralph', 'Ruben', 72, 'heart', '2025-05-16 03:16:19', 1),
(19, 'Ralph', 'Raymond', NULL, 'follow', '2025-05-16 03:34:00', 1),
(31, 'Ruben', 'Ralph', 82, 'heart', '2025-05-16 03:39:59', 0),
(32, 'Ruben', 'Ralph', 81, 'heart', '2025-05-16 03:40:00', 0),
(33, 'Ruben', 'Ralph', 80, 'heart', '2025-05-16 03:40:01', 0),
(34, 'Ruben', 'Ralph', 79, 'heart', '2025-05-16 03:40:03', 0),
(35, 'Raymond', 'Ralph', 78, 'heart', '2025-05-16 03:40:05', 0),
(36, 'Raymond', 'Ralph', 77, 'heart', '2025-05-16 03:40:07', 0),
(37, 'Raymond', 'Ralph', 76, 'heart', '2025-05-16 03:40:10', 0),
(38, 'Raymond', 'Ralph', 75, 'heart', '2025-05-16 03:40:13', 0),
(39, 'Raymond', 'Ralph', NULL, 'follow', '2025-05-16 03:43:42', 0),
(40, 'Raymond', 'Ralph', NULL, 'follow', '2025-05-16 03:43:46', 0),
(41, 'Raymond', 'Ralph', NULL, 'follow', '2025-05-16 03:43:47', 0),
(42, 'Raymond', 'Ralph', NULL, 'follow', '2025-05-16 03:43:48', 0);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `media` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`media`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `heart_count` int(11) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `username`, `content`, `media`, `created_at`, `heart_count`, `user_id`) VALUES
(72, 'Ralph', 'gwapo ni sir Zed', '[]', '2025-05-16 02:55:47', 2, NULL),
(73, 'Ralph', 'pinatamis na kahapon', '[]', '2025-05-16 02:56:09', 2, NULL),
(74, 'Ralph', 'ang sarap sarap\r\n', '[]', '2025-05-16 03:05:40', 2, NULL),
(75, 'Raymond', 'Coffee☕', '[\"6826acad650f8_495048252_2838585412978841_5885895707405535460_n.jpg\"]', '2025-05-16 03:10:37', 2, NULL),
(76, 'Raymond', 'another one☕', '[\"6826acc2d7cfa_494857555_2237427673381602_1301371154244338951_n.jpg\"]', '2025-05-16 03:10:58', 2, NULL),
(77, 'Raymond', 'Egg', '[\"6826adc44383b_490748694_9905885929477702_7746696230953419240_n.mp4\"]', '2025-05-16 03:15:16', 2, NULL),
(78, 'Raymond', 'Ganda', '[]', '2025-05-16 03:15:49', 2, NULL),
(79, 'Ruben', 'Just one small positive thought in the morning can change your whole day.', '[]', '2025-05-16 03:20:20', 1, NULL),
(80, 'Ruben', 'Opportunities don\'t happen, you create them.', '[]', '2025-05-16 03:20:28', 1, NULL),
(81, 'Ruben', 'SANA PUMASA 🍀🍀🍀🍀', '[]', '2025-05-16 03:20:37', 1, NULL),
(82, 'Ruben', 'SYSTEM DEFENDED 🤩🫶🏻\r\n', '[]', '2025-05-16 03:20:46', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(100) NOT NULL DEFAULT 'Guest',
  `profile_pic` varchar(255) DEFAULT '',
  `deletion_requested_at` timestamp NULL DEFAULT NULL,
  `deletion_expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `name`, `profile_pic`, `deletion_requested_at`, `deletion_expires_at`) VALUES
(17, 'Ralph', 'ralphpogi123@gmail.com', '$2y$10$4ghx6LoHHtzMicmBUIBXUOmLGiEC6RST30pcLOHA/QCIQg8Vwp2da', '2025-05-16 02:47:10', 'Rappu', '6826ab5c6914c_491093622_2087279188414802_6555505851704367980_n.jpg', NULL, NULL),
(18, 'Ruben', 'rubenravanera@yahoo.com', '$2y$10$NW0UaUeGMz94JwXXC68fY.o8FdsAchEnlhHTsELacepDZ4LZjz.ka', '2025-05-16 03:06:09', 'Ruben', '6826aeddcb638_494690536_1431819721498825_1249119235835078619_n.jpg', NULL, NULL),
(19, 'Lhan', 'lhanarlo@hotmail.com', '$2y$10$N4V2GPGWr19AN32w87A/Uu3Mbfbw6z0EjSjnAJ3GxtsjlxziDbrxa', '2025-05-16 03:06:56', 'Lhan', 'default.jpg', NULL, NULL),
(20, 'Raymond', 'simreraymond@bsu.edu.ph', '$2y$10$PoTTR9F9JVIxG3Ss6qr7pu9bXLqQGkzVwyJcXIh0J7s0jbVh2RWea', '2025-05-16 03:07:49', 'masikip', '6826ac8bef7d1_494861628_2385327658517720_1896995217006837786_n.jpg', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `follower_username` (`follower_username`),
  ADD KEY `followed_username` (`followed_username`);

--
-- Indexes for table `hearts`
--
ALTER TABLE `hearts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipient_username` (`recipient_username`),
  ADD KEY `sender_username` (`sender_username`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `hearts`
--
ALTER TABLE `hearts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`followed_username`) REFERENCES `users` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `hearts`
--
ALTER TABLE `hearts`
  ADD CONSTRAINT `hearts_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hearts_ibfk_2` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`recipient_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

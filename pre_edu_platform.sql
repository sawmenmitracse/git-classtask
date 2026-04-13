-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2025 at 03:13 AM
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
-- Database: `pre_edu_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `challenge_progress`
--

CREATE TABLE `challenge_progress` (
  `id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `challenge_id` int(11) DEFAULT NULL,
  `stars_earned` int(11) DEFAULT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenge_progress`
--

INSERT INTO `challenge_progress` (`id`, `child_id`, `challenge_id`, `stars_earned`, `completed_at`) VALUES
(1, 4, 1, 1, '2025-08-19 00:53:23'),
(2, 4, 1, 1, '2025-08-19 00:53:48');

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `screen_time` int(11) DEFAULT NULL,
  `stars` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`id`, `parent_id`, `name`, `screen_time`, `stars`) VALUES
(1, 110, 'sanzida ', 10, 0),
(2, 111, 'shammy', 7, 0),
(3, 111, 'sanzida', 20, 0),
(4, 113, 'tasbe', 4000, 2);

-- --------------------------------------------------------

--
-- Table structure for table `daily_challenges`
--

CREATE TABLE `daily_challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_challenges`
--

INSERT INTO `daily_challenges` (`id`, `title`, `description`, `date`) VALUES
(1, '?? Color Hunt!', 'Find 3 things that are <strong>yellow</strong> around you.', NULL),
(2, '?? Animal Sounds!', 'Make the sound of a <strong>lion</strong> and a <strong>dog</strong>!', NULL),
(3, '?? Shape Finder!', 'Can you find something shaped like a <strong>circle</strong>?', NULL),
(4, '?? Number Fun!', 'Count loudly from 1 to 10.', NULL),
(5, '?? Sing Along!', 'Sing your favorite song with your parent.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `name`, `email`, `password`) VALUES
(1, 'Tonmoy', 'tonmoy@gmail.com', '$2y$10$S2mjt6BLZUWiI.QIdkiG7OjcGIKwZ0Y4Iaw0STGwcDUil1kLiakr6'),
(110, 'Tonmoy', 'tonmoy1@gmail.com', '$2y$10$oRG/hdyVhYRWIQ93GGXdy.oKV5euz0j/VJmRHQfClpRcMC.y5oweG'),
(111, 'Rupak', 'rupak@gmail.com', '$2y$10$xwGwEv.oJoElmtiIdd8ep.1Tu8GLZnzB81df1F3/gku.EZqbmdEd.'),
(112, 'Emu', 'emu@gamil.com', '$2y$10$lbBHWVIZGNVmBAelQaTGC.1OuOEW55rNuPlVLv4g.B69PJVPsLhA.'),
(113, 'Emu', 'emu@gmaill.com', '$2y$10$6J6ATINyJPzWYXrtP6HmzeiVb4iPOLLIhcIjOnnswMAWIj4FMDEkm');

-- --------------------------------------------------------

--
-- Table structure for table `stars`
--

CREATE TABLE `stars` (
  `child_id` int(11) NOT NULL,
  `earned_stars` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stars`
--

INSERT INTO `stars` (`child_id`, `earned_stars`) VALUES
(1, 0),
(2, 0),
(3, 0),
(4, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `challenge_progress`
--
ALTER TABLE `challenge_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `stars`
--
ALTER TABLE `stars`
  ADD PRIMARY KEY (`child_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `challenge_progress`
--
ALTER TABLE `challenge_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `challenge_progress`
--
ALTER TABLE `challenge_progress`
  ADD CONSTRAINT `challenge_progress_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challenge_progress_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `daily_challenges` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stars`
--
ALTER TABLE `stars`
  ADD CONSTRAINT `stars_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

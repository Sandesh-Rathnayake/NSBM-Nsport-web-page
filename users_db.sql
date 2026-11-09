-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 05, 2026 at 08:34 AM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `users_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` int NOT NULL,
  `club_name` varchar(250) NOT NULL,
  `description` varchar(255) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'bx-trophy',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `image` varchar(50) DEFAULT 'background.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `club_name`, `description`, `icon`, `created_at`, `image`) VALUES
(1, 'Swimming', 'Lap training, technique sessions and inter-university meets.', 'bx-swim', '2026-10-27 01:20:52', 'C9.jpg'),
(2, 'Cricket', 'Net practice, league matches and the annual Big Match.', 'bx-cricket-ball', '2026-10-27 01:20:52', 'C4.jpg'),
(3, 'Tennis', 'Court sessions for beginners and tournament players.', 'bx-tennis-ball', '2026-10-27 01:20:52', 'C11.jpg'),
(4, 'Chess', 'Weekly games, rated tournaments and strategy workshops.', 'bxs-chess', '2026-10-27 01:20:52', 'C3.jpg'),
(5, 'Table Tennis', 'Indoor practice and campus championships.', 'bx-tennis-ball', '2026-10-27 01:20:52', 'C10.jpg'),
(6, 'Volleyball', 'Team training and inter-faculty matches.', 'bx-basketball', '2026-10-27 01:20:52', 'C12.jpg'),
(7, 'Martial Arts', 'Karate, taekwondo and self-defence training.', 'bx-body', '2026-10-27 01:20:52', 'C5.jpg'),
(8, 'Rugby', 'Contact and non-contact training with weekend fixtures.', 'bx-football', '2026-10-27 01:20:52', 'C8.jpg'),
(9, 'Netball', 'Team drills and competitive league games.', 'bx-basketball', '2026-10-27 01:20:52', 'C6.jpg'),
(10, 'Badminton', 'Doubles and singles practice with club tournaments.', 'bx-medal', '2026-10-27 01:20:52', 'C2.jpg'),
(11, 'Powerlifting', 'Strength training and powerlifting competitions.', 'bx-dumbbell', '2026-10-27 01:20:52', 'C7.jpg'),
(12, 'Athletics', 'Track and field training for sprints, distance and jumps.', 'bx-run', '2026-10-27 01:20:52', 'C1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `facility_bookings`
--

CREATE TABLE `facility_bookings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `facility_name` varchar(100) NOT NULL,
  `booking_date` date NOT NULL,
  `time_slot` varchar(50) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `facility_bookings`
--

INSERT INTO `facility_bookings` (`id`, `user_id`, `facility_name`, `booking_date`, `time_slot`, `status`, `created_at`) VALUES
(1, 1, 'Indoor Badminton Court', '2026-10-25', '04:00 PM - 06:00 PM', 'pending', '2026-10-20 18:20:03'),
(2, 1, 'Main Cricket Ground', '2026-10-28', '08:00 AM - 12:00 PM', 'pending', '2026-10-20 18:20:03');

-- --------------------------------------------------------

--
-- Table structure for table `matches_schedules`
--

CREATE TABLE `matches_schedules` (
  `id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `sport_type` varchar(100) NOT NULL,
  `event_type` enum('Match','Training','Tournament') DEFAULT 'Match',
  `event_date` date NOT NULL,
  `event_time` varchar(50) NOT NULL,
  `venue` varchar(100) NOT NULL,
  `status` enum('Upcoming','Live','Completed') DEFAULT 'Upcoming',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `matches_schedules`
--

INSERT INTO `matches_schedules` (`id`, `title`, `sport_type`, `event_type`, `event_date`, `event_time`, `venue`, `status`, `created_at`) VALUES
(1, 'NSBM vs UOC Friendly Match', 'Basketball', 'Match', '2026-11-05', '03:30 PM', 'Main Indoor Court', 'Upcoming', '2026-10-21 10:52:28'),
(2, 'Badminton Inter-Faculty Tournament', 'Badminton', 'Tournament', '2026-11-10', '09:00 AM', 'Badminton Complex', 'Upcoming', '2026-10-21 10:52:28'),
(3, 'Cricket Team Selection Practice', 'Cricket', 'Training', '2026-11-02', '04:00 PM', 'Main Cricket Ground', 'Upcoming', '2026-10-21 10:52:28'),
(4, 'NSBM & SLIIT Friendly Match', 'Badminton', 'Training', '2026-10-31', '03:30 p.m', 'indor cort', 'Upcoming', '2026-10-28 01:20:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Sandesh Rathnayake', 'sandesh@nsbm.ac.lk', '$2y$10$c1om.Kw4esybmH0B6eHt5.rSdD6njG//50hseDkJ4SDwFKftCSgqW', 'admin'),
(2, 'sandesh student', 'sandesh@students.nsbm.ac.lk', '$2y$10$oKTWtXjhXFlsLq2u6nl/QuPjuUqt5FQBG084/Li/L6kh0xaOYU5pW', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `user_clubs`
--

CREATE TABLE `user_clubs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `club_name` varchar(250) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_clubs`
--

INSERT INTO `user_clubs` (`id`, `user_id`, `club_name`, `status`, `joined_at`) VALUES
(1, 1, 'NSBM Basketball Club', 'approved', '2026-10-19 04:40:59'),
(2, 1, 'NSBM Badminton Club', 'approved', '2026-10-19 04:40:59'),
(3, 1, 'NSBM Swimming Club', 'approved', '2026-10-19 04:40:59'),
(6, 1, 'Swimming', 'approved', '2026-10-27 04:55:55'),
(7, 1, 'Netball', 'rejected', '2026-10-27 06:43:36'),
(8, 2, 'Badminton', 'pending', '2026-11-02 06:43:39'),
(9, 2, 'Volleyball', 'approved', '2026-11-05 08:10:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `club_name` (`club_name`);

--
-- Indexes for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `matches_schedules`
--
ALTER TABLE `matches_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_clubs`
--
ALTER TABLE `user_clubs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `matches_schedules`
--
ALTER TABLE `matches_schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_clubs`
--
ALTER TABLE `user_clubs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  ADD CONSTRAINT `facility_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_clubs`
--
ALTER TABLE `user_clubs`
  ADD CONSTRAINT `user_clubs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

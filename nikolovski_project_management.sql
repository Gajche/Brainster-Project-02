-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 02:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nikolovski_project_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `edited` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `task_id`, `user_id`, `content`, `created_at`, `edited`) VALUES
(1, 1, 2, 'Please provide more details about the login loop.', '2025-12-11 14:20:08', 0),
(2, 1, 3, 'Found that issue only happens on Safari.', '2025-12-11 14:20:08', 0),
(3, 2, 4, 'Started working on the chart layout.', '2025-12-11 14:20:08', 0),
(4, 3, 5, 'Modal loads but animations are missing.', '2025-12-11 14:20:08', 0),
(5, 4, 3, 'I implemented the cart logic. Review needed.', '2025-12-11 14:20:08', 0),
(6, 6, 2, 'Orders page must include pagination.', '2025-12-11 14:20:08', 0),
(7, 7, 4, 'Course list looks great!', '2025-12-11 14:20:08', 0),
(8, 9, 5, 'Quiz system design is ready.', '2025-12-11 14:20:08', 0),
(9, 8, 1, '[Admin User] changed the status from In Progress to Done', '2025-12-11 14:33:25', 0),
(10, 9, 1, '[Admin User] changed the status from To Do to Done', '2025-12-11 14:33:28', 0),
(11, 9, 1, '[Admin User] changed the status from Done to QA', '2025-12-11 14:34:08', 0),
(12, 7, 1, '[Admin User] changed the status from Done to To Do', '2025-12-11 14:34:10', 0),
(15, 1, 1, '[Admin User] changed the status from In Progress to To Do', '2025-12-12 10:39:34', 0),
(16, 2, 1, '[Admin User] changed the status from To Do to In Progress', '2025-12-12 11:08:24', 0),
(17, 10, 4, 'Test!!', '2025-12-12 11:38:33', 1),
(18, 10, 1, '[Admin User] changed the status from To Do to In Progress', '2025-12-12 12:43:25', 0),
(19, 2, 1, '[Admin User] changed the status from In Progress to To Do', '2025-12-12 12:43:28', 0);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `estimated_time` varchar(50) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `team_lead_id` int(11) DEFAULT NULL,
  `status` enum('Active','Done') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `requirements`, `estimated_time`, `deadline`, `team_lead_id`, `status`) VALUES
(1, 'Project Management System', 'Internal tool to handle tasks, teams, and comments.', 'PHP OOP, MySQL, JS, jQuery', '2 months', '2024-12-31', 6, 'Active'),
(2, 'E-Commerce Shop', 'Full online shop with cart, orders, and admin panel.', 'PHP MVC, JS, MySQL, Bootstrap', '3 months', '2025-02-15', 2, 'Active'),
(3, 'Learning Platform', 'Video courses, quizzes, progress tracking.', 'PHP, REST API, Vanilla JS', '4 months', '2025-03-10', 2, 'Done');

-- --------------------------------------------------------

--
-- Table structure for table `project_users`
--

CREATE TABLE `project_users` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_users`
--

INSERT INTO `project_users` (`project_id`, `user_id`) VALUES
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(3, 2),
(3, 3),
(3, 4),
(3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('To Do','In Progress','QA','Done') DEFAULT 'To Do',
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `created_at`, `status`, `assigned_to`) VALUES
(1, 1, 'Fix login bug', 'Users sometimes stay stuck in loop.', '2025-12-11 14:20:08', 'To Do', 3),
(2, 1, 'Improve dashboard UI', 'Add charts and stats.', '2025-12-11 14:20:08', 'To Do', 4),
(3, 1, 'Create task modal', 'Modal must load via AJAX.', '2025-12-11 14:20:08', 'QA', 5),
(4, 2, 'Implement cart system', 'Add-to-cart and cart API.', '2025-12-11 14:20:08', 'In Progress', 3),
(5, 2, 'Product filtering', 'Filter by category and price.', '2025-12-11 14:20:08', 'To Do', 4),
(6, 2, 'Orders page', 'Admin order management.', '2025-12-11 14:20:08', 'To Do', NULL),
(7, 3, 'Course list page', 'Display courses with search.', '2025-12-11 14:20:08', 'To Do', 3),
(8, 3, 'Video player', 'Add speed controls and chapters.', '2025-12-11 14:20:08', 'Done', 4),
(9, 3, 'Quiz system', 'Multiple-choice quiz engine.', '2025-12-11 14:20:08', 'QA', 5),
(10, 1, 'Fix Modal', 'Fix Modal', '2025-12-12 11:33:17', 'In Progress', NULL),
(11, 2, 'Task4', 'Task4', '2025-12-13 21:55:01', 'To Do', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('Admin','Senior','Mid','Junior') NOT NULL,
  `is_team_lead` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `level`, `is_team_lead`, `is_approved`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$12$cwzB93bdiO0odCJfN2fcCuaAdjBKJX7ouMhol1coQ0sX2muC.wXuS', 'Admin', 0, 1),
(2, 'Team Lead Senior', 'lead@example.com', '$2y$12$xe0HeDi6AQPmzyqDzeocIOKagO5.tjhld7Nc1MBXB9M05k0DSSvSC', 'Senior', 1, 1),
(3, 'Regular Senior', 'senior@example.com', '$2y$12$MKFYcMmebnoY5gb1bHTtoe4RiVRP64jqIgLxWg1E/PfYDGJU8cznS', 'Senior', 0, 1),
(4, 'Mid Developer', 'mid@example.com', '$2y$12$oUScrv0pq6qF1f//lC1/4e.qaYkAFQsWMvvabI5c2Ht9/TD1FkZsa', 'Mid', 0, 1),
(5, 'Junior Developer', 'junior@example.com', '$2y$12$iEV9UN8HU.FMESHD6zgrz.7ULvLA3x6pbyv8EcESsX9D4z.ZuVpvq', 'Junior', 0, 1),
(6, 'Dejan_TeamLead2', 'dejan-lead@example.com', '$2y$12$8Pb/kybq8ZzJ7n9ZGUf3j.yrDNctVgJ5m5mQ88MAxh37pK3dJm4Xm', 'Senior', 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_lead_id` (`team_lead_id`);

--
-- Indexes for table `project_users`
--
ALTER TABLE `project_users`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `assigned_to` (`assigned_to`);

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
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`team_lead_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_users`
--
ALTER TABLE `project_users`
  ADD CONSTRAINT `project_users_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

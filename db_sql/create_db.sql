-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2025 at 12:32 PM
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
(1, 1, 2, 'Please provide more details about the login loop.', '2025-12-23 19:47:05', 0),
(2, 1, 3, 'Found that issue only happens on Safari.', '2025-12-23 19:47:05', 0),
(3, 2, 4, 'Started working on the chart layout.', '2025-12-23 19:47:05', 0),
(4, 3, 5, 'Modal loads but animations are missing.', '2025-12-23 19:47:05', 0),
(5, 4, 3, 'I implemented the cart logic. Review needed.', '2025-12-23 19:47:05', 0),
(6, 6, 2, 'Orders page must include pagination.', '2025-12-23 19:47:05', 0),
(7, 7, 4, 'Course list looks great!', '2025-12-23 19:47:05', 0),
(8, 9, 5, 'Quiz system design is ready.', '2025-12-23 19:47:05', 0),
(9, 10, 6, 'Login screen implemented, testing complete.', '2025-12-23 19:47:05', 0),
(10, 11, 7, 'Push notifications working on iOS and Android.', '2025-12-23 19:47:05', 0),
(11, 12, 6, 'Performance optimized, load times reduced by 40%.', '2025-12-23 19:47:05', 0),
(12, 13, 2, 'Homepage layout updated, review pending.', '2025-12-23 19:47:05', 0),
(13, 14, 3, 'Blog section integrated with WordPress CMS.', '2025-12-23 19:47:05', 0),
(14, 15, 2, 'SEO tags added, sitemap generated.', '2025-12-23 19:47:05', 0),
(26, 19, 1, 'test', '2025-12-24 19:07:42', 1),
(27, 19, 1, 'test2', '2025-12-24 21:29:17', 0);

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
(1, 'Project Management System', 'Internal tool to handle tasks, teams, and comments.', 'PHP OOP, MySQL, JS, jQuery', '2 months', '2024-12-31', 2, 'Active'),
(2, 'E-Commerce Shop', 'Full online shop with cart, orders, and admin panel.', 'PHP MVC, JS, MySQL, Bootstrap', '3 months', '2026-02-15', 2, 'Active'),
(3, 'Learning Platform', 'Video courses, quizzes, progress tracking.', 'PHP, REST API, Vanilla JS', '4 months', '2026-03-10', 2, 'Active'),
(4, 'Mobile App Development', 'Build a cross-platform mobile app for task management.', 'React Native, Firebase, REST API', '5 months', '2024-06-30', 6, 'Done'),
(5, 'Website Redesign', 'Redesign company website with modern UI/UX.', 'HTML/CSS, JavaScript, Bootstrap', '6 months', '2026-06-15', 2, 'Active'),
(7, 'Project 6', 'Project 6', 'Project 6', '6 months', '2026-10-13', 2, 'Active'),
(20, 'Project 7', '', '', '6 months', '2026-10-13', 6, 'Active');

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
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(4, 7),
(5, 2),
(5, 3),
(5, 4),
(5, 5),
(5, 6),
(7, 2),
(7, 3),
(7, 4),
(7, 5),
(7, 6),
(20, 4),
(20, 5),
(20, 6);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('To Do','In Progress','QA','Done') DEFAULT 'To Do',
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `created_by`, `created_at`, `status`, `assigned_to`) VALUES
(1, 1, 'Fix login bug', 'Users sometimes stay stuck in loop.', 2, '2025-12-23 19:47:05', 'In Progress', 3),
(2, 1, 'Improve dashboard UI', 'Add charts and stats.', 3, '2025-12-23 19:47:05', 'To Do', 4),
(3, 1, 'Create task modal', 'Modal must load via AJAX.', 2, '2025-12-23 19:47:05', 'QA', 5),
(4, 2, 'Implement cart system', 'Add-to-cart and cart API.', 3, '2025-12-23 19:47:05', 'In Progress', 3),
(5, 2, 'Product filtering', 'Filter by category and price.', 2, '2025-12-23 19:47:05', 'To Do', 4),
(6, 2, 'Orders page', 'Admin order management.', 3, '2025-12-23 19:47:05', 'To Do', NULL),
(7, 3, 'Course list page', 'Display courses with search.', 2, '2025-12-23 19:47:05', 'Done', 3),
(8, 3, 'Video player', 'Add speed controls and chapters.', 3, '2025-12-23 19:47:05', 'In Progress', 4),
(9, 3, 'Quiz system', 'Multiple-choice quiz engine.', 2, '2025-12-23 19:47:05', 'To Do', 5),
(10, 4, 'Implement login screen', 'Add secure authentication.', 6, '2025-12-23 19:47:05', 'Done', 3),
(11, 4, 'Add push notifications', 'Real-time updates.', 7, '2025-12-23 19:47:05', 'Done', 4),
(12, 4, 'Optimize performance', 'Reduce load times.', 6, '2025-12-23 19:47:05', 'Done', 5),
(13, 5, 'Update homepage layout', 'Modern responsive design.', 2, '2025-12-23 19:47:05', 'In Progress', 3),
(14, 5, 'Add blog section', 'CMS integration.', 3, '2025-12-23 19:47:05', 'QA', 4),
(15, 5, 'SEO optimization', 'Meta tags and sitemap.', 2, '2025-12-23 19:47:05', 'To Do', 5),
(19, 7, 'Test Task 01', 'Test Task 01', 1, '2025-12-24 16:47:14', 'To Do', 4),
(20, 20, 'Test Task 01', 'Test Task 01', 6, '2025-12-25 10:52:41', 'To Do', 5);

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
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `level`, `is_team_lead`, `is_approved`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$12$n4E1P1dCwoWZkkcwvHoSi.1oOlJaP/PDCChUGqNg.1nkOKFBgZTIq', 'Admin', 0, 1),
(2, 'Team Lead Senior 1', 'lead1@example.com', '$2y$12$LNvynhIu2vFbHm7nNVmB0.GuSoo5.C3N1ZUYq86byxs61a7pQuwFS', 'Senior', 1, 1),
(3, 'Regular Senior 1', 'senior1@example.com', '$2y$12$pJjmZnGebE1RG8R6amtTg.8l7jnJSZeV81JZys7CpON6SY1DRd9ni', 'Senior', 0, 1),
(4, 'Mid Developer 1', 'mid1@example.com', '$2y$12$0bJLMOjuUU5uhuPlqmj1e.6JbKAdvA.WLOBnV4nIw0jsgHTX5g0RW', 'Mid', 0, 1),
(5, 'Junior Developer 1', 'junior1@example.com', '$2y$12$NKzaRPo1OVN2VMt/M.fkFuxW4ncYN58knUGYu3VzjONJMOg3r4u4e', 'Junior', 0, 1),
(6, 'Team Lead Senior 2', 'lead2@example.com', '$2y$12$4RihiqqnxzbH2hZByKFC4OyiUrI8iK8sUQHtGdRYKlKN9PT3r2QeW', 'Senior', 1, 1),
(7, 'Regular Senior 2', 'senior2@example.com', '$2y$12$SSgdi6qxeJRc103snnlbT.ez9Pvlv5NosoMsVgDdxgeOzPnTkXw7m', 'Senior', 0, 1),
(16, 'Dejan-junior', 'dejan-junior@example.com', '$2y$12$Vr1iSFhBkN2rvfgwxPqdDuydzzXeGmbybhCAb4lBmROGZuVSaV9TC', 'Junior', 0, 1);

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
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

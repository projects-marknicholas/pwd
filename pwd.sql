-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 04:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pwd`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `apply_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `job_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `job_id` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `job_description` varchar(10000) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `rate` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `job_id` varchar(255) NOT NULL,
  `notification_id` varchar(255) NOT NULL,
  `notification_title` varchar(255) NOT NULL,
  `notification_description` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `skills` varchar(5000) DEFAULT NULL,
  `education` varchar(5000) DEFAULT NULL,
  `resume` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `role`, `status`, `skills`, `education`, `resume`, `last_login`, `created_at`) VALUES
(2, 'a13e7b10c9fe648286aa7a9f26bdcc4d', 'test', 'account', 'employer', 'testemployer@gmail.com', '$2y$10$m8/mjnQ9n8BV16AhS4E/D.URJPXS89rD2BncIMfp3CPlV7/DS89YK', 'employer', 'pending', NULL, NULL, NULL, '2025-04-21 21:57:42', '2025-04-21 16:11:01'),
(3, 'b6d12e27fe0603625371c72fcce1216e', 'test ', 'account', 'user', 'testuser@gmail.com', '$2y$10$LLVg.j68DidpbyPsgVIIdOZDGyr.Dd9XU355lSQt..NbhngiKnYK6', 'user', 'pending', 'Programming Languages:\r\n\r\nJavaScript, PHP, HTML, CSS, SQL\r\n\r\nFrameworks &amp; Libraries:\r\n\r\nReact, Bootstrap, Tailwind CSS\r\n\r\nBackend &amp; Database:\r\n\r\nMySQL, PHP (OOP), REST APIs\r\n\r\nTools &amp; Technologies:\r\n\r\nGit, GitHub, VS Code, Postman, jsPDF\r\n\r\nSoft Skills:\r\n\r\nTeam Leadership, Problem Solving, Communication, Time Management\r\n', 'Bachelor of Science in Information Technology\r\nLaguna State Polytechnic University (LSPU)\r\n2020 – 2024\r\n\r\n- Relevant Coursework: Web Development, Database Systems, Software Engineering\r\n- Dean’s Lister\r\n', 'LABORATORY-EXERCISE-1-pH.pdf', '2025-04-21 21:58:54', '2025-04-21 16:11:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

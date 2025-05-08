-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 01:23 AM
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
-- Database: `studentsportal`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `images` text NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `images`, `date_posted`) VALUES
(1, 'New Library Hours', 'The library will now be open from 8 AM to 10 PM daily.', '[\"library1.JPG\", \"library2.JPG\"]', '2025-03-16 09:39:15'),
(2, 'Sports Day Event', 'Join us for the annual sports day on April 15th!', '[\"sports3.JPG\"]', '2025-03-16 09:39:15'),
(3, 'Exam Timetable Released', 'The final exam timetable is now available on the portal.', '[\"timetable1.JPG\"]', '2025-03-16 09:39:15'),
(4, 'Elections on its way', 'uchaguzi wa vyuo ni hivi karibuni', 'uploads/1744930596_nit.jpeg', '2025-04-17 21:56:36'),
(5, 'Elections on its way', 'uchaguzi wa vyuo ni hivi karibuni', 'uploads/1744930649_image.jpeg', '2025-04-17 21:57:29'),
(6, 'uchaguzi mkuu', 'uchaguzi mkuu', 'uploads/1744930734_image.jpeg', '2025-04-17 21:58:54');

-- --------------------------------------------------------

--
-- Table structure for table `announcement_comments`
--

CREATE TABLE `announcement_comments` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp(),
  `reg_no` varchar(30) NOT NULL,
  `first_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement_comments`
--

INSERT INTO `announcement_comments` (`id`, `announcement_id`, `user_name`, `comment`, `date_posted`, `reg_no`, `first_name`) VALUES
(1, 1, 'gabby mabamba', 'hello is this fine?', '2025-03-16 09:42:10', '', ''),
(2, 1, 'gabby mabamba', 'wow fantastic', '2025-03-16 11:56:14', '', ''),
(3, 3, 'gabby mabamba', 'woow i can feel the goosebumps ...omg the anxiety level 100%', '2025-03-24 18:23:30', '', ''),
(4, 1, 'Gabriel', 'all my people', '2025-04-17 16:16:54', '0', ''),
(5, 1, 'Gabriel', 'amaizing though', '2025-04-17 16:30:11', '0', '');

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `event_type` enum('academic','social','holiday','other') NOT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `title`, `description`, `start_datetime`, `end_datetime`, `location`, `event_type`, `is_public`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Test 1 Exams', 'mitihani ya jaribio la kwnaza wanafunzi wote mnahimizwa kumaliza da ya mwaka mzima ', '2025-04-22 07:00:00', '2025-04-30 19:00:00', 'NIT', 'academic', 1, 20, '2025-04-17 21:39:54', '2025-04-17 21:39:54');

-- --------------------------------------------------------

--
-- Table structure for table `contestants`
--

CREATE TABLE `contestants` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `reg_number` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `level` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `motto` varchar(255) NOT NULL,
  `status` enum('Pending','Accepted','Declined') DEFAULT 'Pending',
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contestants`
--

INSERT INTO `contestants` (`id`, `fullname`, `reg_number`, `course`, `level`, `position`, `reason`, `motto`, `status`, `image`) VALUES
(3, 'Gabriel Hamisi', 'NIT/BLTM/2022/4805', 'BLTM', 'Third Year', 'Parliament Representative', 'I want big changes', 'Forever Together', 'Accepted', 'food4.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `event_attendance`
--

CREATE TABLE `event_attendance` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('going','not_going','maybe') DEFAULT 'going',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `reg_no` varchar(255) NOT NULL,
  `feedback` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `reg_no`, `feedback`, `created_at`, `user_id`, `status`) VALUES
(1, 'Gabriel', 'NIT/BLTM/2022/4805', 'all good', '2025-03-16 10:28:41', 20, '');

-- --------------------------------------------------------

--
-- Table structure for table `leaders`
--

CREATE TABLE `leaders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `history` text NOT NULL,
  `contacts` int(12) NOT NULL,
  `email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaders`
--

INSERT INTO `leaders` (`id`, `name`, `position`, `picture`, `bio`, `history`, `contacts`, `email`) VALUES
(1, 'Stavius Alkadi', 'President', 'president.JPEG', 'A dedicated leader...', 'Our organization was founded in...', 78909876, ''),
(2, 'Alfred Makabala', 'Vice President', 'leader1.JPEG', 'Experienced in student affairs...', '', 8786867, ''),
(3, 'Janeth Johnson', 'General Secretary', 'leader2.WEBP', 'Handles all student records...', '', 8668798, ''),
(4, 'Robert Nziku', 'Treasurer', 'leader3.JPG', 'Manages finances responsibly...', 'yet we need to thrive', 87779798, ''),
(5, 'SAMIA SULUHU HASSAN', 'Cr', '1744918525_food4.jpeg', 'all we can', '', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `lost_found`
--

CREATE TABLE `lost_found` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) NOT NULL,
  `posted_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_found`
--

INSERT INTO `lost_found` (`id`, `item_name`, `description`, `location`, `contact`, `image`, `date_posted`, `status`, `posted_by`) VALUES
(1, 'ID', 'ID imeokotwa maeneo ya vimbweta vya cocacola', 'Mabibo mwisho', '0789787898', 'DO IT.jpg', '2025-03-16 09:16:58', '', ''),
(2, 'Card ya Bank', 'kadi ya crdb bank yenye majina juma salehr mwacha imeokotwa maeneo ya MPH', 'Chuo NIT ofisi ya minister of defence', '07868959857', 'candidates2.png', '2025-03-30 14:33:20', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_reg_no` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_reg_no`, `message`, `timestamp`, `reference`) VALUES
(12, 'NIT/BLTM/2022/4805', 'helooooo people', '2025-03-30 19:13:10', ''),
(13, 'NIT/BLTM/2022/4805', 'my fiends in degree', '2025-04-17 15:05:53', ''),
(14, 'NIT/BLTM/2022/4805', 'helooooo people', '2025-04-17 15:30:33', '');

-- --------------------------------------------------------

--
-- Table structure for table `presidents`
--

CREATE TABLE `presidents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `motto` text NOT NULL,
  `picture` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `president_votes`
--

CREATE TABLE `president_votes` (
  `id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `voted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prs`
--

CREATE TABLE `prs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_of_study` int(11) NOT NULL,
  `motto` text NOT NULL,
  `picture` varchar(255) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `year` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr_votes`
--

CREATE TABLE `pr_votes` (
  `id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `course` varchar(100) NOT NULL,
  `voted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pr_votes`
--

INSERT INTO `pr_votes` (`id`, `voter_id`, `reg_no`, `candidate_id`, `course`, `voted_at`) VALUES
(1, 20, 'NIT/BLTM/2022/4805', 3, 'BLTM', '2025-04-18 01:25:22');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` enum('enabled','disabled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `status`) VALUES
(1, 'election', 'enabled');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `first_name` varchar(50) DEFAULT NULL,
  `second_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `year_joined` int(11) DEFAULT NULL,
  `unique_no` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` int(12) NOT NULL,
  `department` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`first_name`, `second_name`, `last_name`, `password`, `course`, `year_joined`, `unique_no`, `reg_no`, `id`, `level`, `profile_pic`, `reference`, `email`, `phone`, `department`) VALUES
('Felix', 'John', 'Mkwawa', 'MKWAWA', 'BPLM', 2021, 1235, 'NIT/BPLM/2021/1235', 1, 0, '', '', '', 0, ''),
('Omar', 'Issa', 'Mohamed', 'MOHAMED', 'BATF', 2021, 1290, 'NIT/BATF/2021/1290', 2, 0, '', '', '', 0, ''),
('Sabrina', 'Mohammed', 'Mzee', 'MZEE', 'BBA', 2023, 1987, 'NIT/BBA/2023/1987', 3, 0, '', '', '', 0, ''),
('Juma', 'Suleiman', 'Ramadhani', 'RAMADHANI', 'BIT', 2021, 2109, 'NIT/BIT/2021/2109', 4, 0, '', '', '', 0, ''),
('Esther', 'Elizabeth', 'Mushi', 'MUSHI', 'BPLM', 2022, 2314, 'NIT/BPLM/2022/2314', 5, 0, '', '', '', 0, ''),
('Edward', 'Frank', 'Ngoma', 'NGOMA', 'BPLM', 2022, 2345, 'NIT/BPLM/2022/2345', 6, 0, '', '', '', 0, ''),
('Tatu', 'Said', 'Nyange', 'NYANGE', 'BPLM', 2023, 2346, 'NIT/BPLM/2023/2346', 7, 0, '', '', '', 0, ''),
('Moses', 'Paul', 'Kimario', 'KIMARIO', 'BIT', 2022, 2347, 'NIT/BIT/2022/2347', 8, 0, '', '', '', 0, ''),
('Zainab', 'Ali', 'Matata', 'MATATA', 'BBA', 2021, 2987, 'NIT/BBA/2021/2987', 9, 0, '', '', '', 0, ''),
('Rahma', 'Yusuph', 'Kibwana', 'KIBWANA', 'BLTM', 2021, 3210, 'NIT/BLTM/2021/3210', 10, 0, '', '', '', 0, ''),
('Patrick', 'Leonard', 'Temu', 'TEMU', 'BLTM', 2021, 3450, 'NIT/BLTM/2021/3450', 11, 0, '', '', '', 0, ''),
('Noel', 'Stanley', 'Komba', 'KOMBA', 'BATF', 2023, 3456, 'NIT/BATF/2023/3456', 12, 0, '', '', '', 0, ''),
('Elijah', 'Daniel', 'Kimaro', 'KIMARO', 'BPLM', 2023, 3478, 'NIT/BPLM/2023/3478', 13, 0, '', '', '', 0, ''),
('Fatma', 'Said', 'Omar', 'OMAR', 'BBA', 2022, 3890, 'NIT/BBA/2022/3890', 14, 0, '', '', '', 0, ''),
('Mohammed', 'Ali', 'Said', 'SAID', 'BATF', 2023, 4123, 'NIT/BATF/2023/4123', 15, 0, '', '', '', 0, ''),
('David', 'John', 'Mwenda', 'MWENDA', 'BPLM', 2021, 4321, 'NIT/BPLM/2021/4321', 16, 0, '', '', '', 0, ''),
('Lucas', 'Peter', 'Lusajo', 'LUSAJO', 'BPLM', 2023, 4324, 'NIT/BPLM/2023/4321', 17, 0, '', '', '', 0, ''),
('Joseph', 'Michael', 'Ndulu', 'NDULU', 'BIT', 2022, 4567, 'NIT/BIT/2022/4567', 18, 0, '', '', '', 0, ''),
('Agnes', 'Lucy', 'Chuwa', 'CHUWA', 'BBA', 2023, 4568, 'NIT/BBA/2023/4568', 19, 0, '', '', '', 0, ''),
('Gabriel', 'Mabamba', 'Hamisi', '123456', 'BLTM', 2022, 4805, 'NIT/BLTM/2022/4805', 20, 0, '', '', '', 0, ''),
('Aisha', 'Mwinyi', 'Kassim', 'KASSIM', 'BIT', 2023, 5012, 'NIT/BIT/2023/5012', 21, 0, '', '', '', 0, ''),
('Aneth', 'Lucas', 'Mandela', 'MANDELA', 'BIT', 2021, 5566, 'NIT/BIT/2021/5566', 22, 0, '', '', '', 0, ''),
('George', 'Stanley', 'Kazimoto', 'KAZIMOTO', 'BIT', 2022, 5671, 'NIT/BIT/2022/5671', 23, 0, '', '', '', 0, ''),
('Hassan', 'Juma', 'Abdallah', 'ABDALLAH', 'BATF', 2023, 5678, 'NIT/BATF/2023/5678', 24, 0, '', '', '', 0, ''),
('Zaitun', 'Hassan', 'Sharif', 'SHARIF', 'BBA', 2022, 5764, 'NIT/BBA/2022/5764', 25, 0, '', '', '', 0, ''),
('Kelvin', 'Amos', 'Kaseko', 'KASEKO', 'BLTM', 2022, 6542, 'NIT/BLTM/2022/6542', 26, 0, '', '', '', 0, ''),
('Samuel', 'Peter', 'Lema', 'LEMA', 'BPLM', 2023, 6543, 'NIT/BPLM/2023/6543', 27, 0, '', '', '', 0, ''),
('Sophia', 'Michael', 'Dotto', 'DOTTO', 'BBA', 2022, 6765, 'NIT/BBA/2022/6765', 28, 0, '', '', '', 0, ''),
('Zubeda', 'Kassim', 'Jaffar', 'JAFFAR', 'BIT', 2023, 6783, 'NIT/BIT/2023/6783', 29, 0, '', '', '', 0, ''),
('Winfrida', 'Catherine', 'Matemu', 'MATEMU', 'BIT', 2021, 7092, 'NIT/BIT/2021/7092', 30, 0, '', '', '', 0, ''),
('Hawa', 'Ramadhan', 'Hemed', 'HEMED', 'BLTM', 2023, 7658, 'NIT/BLTM/2023/7658', 31, 0, '', '', '', 0, ''),
('Shaban', 'Omar', 'Kasimu', 'KASIMU', 'BATF', 2022, 7775, 'NIT/BATF/2022/7775', 32, 0, '', '', '', 0, ''),
('Abdulkadir', 'Salim', 'Bakari', 'BAKARI', 'BATF', 2022, 7854, 'NIT/BATF/2022/7854', 33, 0, '', '', '', 0, ''),
('Neema', 'Paul', 'Mcharo', 'MCHARO', 'BLTM', 2023, 8765, 'NIT/BLTM/2023/8765', 34, 0, '', '', '', 0, ''),
('Suleiman', 'Abbas', 'Mwinyi', 'MWINYI', 'BBA', 2021, 9001, 'NIT/BBA/2021/9001', 35, 0, '', '', '', 0, ''),
('Jaffar', 'Suleiman', 'Ali', 'ALI', 'BATF', 2021, 9010, 'NIT/BATF/2021/9010', 36, 0, '', '', '', 0, ''),
('Maria', 'James', 'Nziku', 'NZIKU', 'BLTM', 2021, 9012, 'NIT/BLTM/2021/9012', 37, 0, '', '', '', 0, ''),
('Julius', 'Edward', 'Msafiri', 'MSAFIRI', 'BLTM', 2022, 9087, 'NIT/BLTM/2022/9087', 38, 0, '', '', '', 0, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcement_comments`
--
ALTER TABLE `announcement_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_id` (`announcement_id`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `contestants`
--
ALTER TABLE `contestants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaders`
--
ALTER TABLE `leaders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lost_found`
--
ALTER TABLE `lost_found`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `presidents`
--
ALTER TABLE `presidents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `president_votes`
--
ALTER TABLE `president_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voter_id` (`voter_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `prs`
--
ALTER TABLE `prs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pr_votes`
--
ALTER TABLE `pr_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voter_id` (`voter_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `announcement_comments`
--
ALTER TABLE `announcement_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contestants`
--
ALTER TABLE `contestants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_attendance`
--
ALTER TABLE `event_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leaders`
--
ALTER TABLE `leaders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lost_found`
--
ALTER TABLE `lost_found`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `presidents`
--
ALTER TABLE `presidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `president_votes`
--
ALTER TABLE `president_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prs`
--
ALTER TABLE `prs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pr_votes`
--
ALTER TABLE `pr_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcement_comments`
--
ALTER TABLE `announcement_comments`
  ADD CONSTRAINT `announcement_comments_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `students` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD CONSTRAINT `event_attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_attendance_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

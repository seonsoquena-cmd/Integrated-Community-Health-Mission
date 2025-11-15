-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 06:32 PM
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
-- Database: `ichm_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('Login','Logout','Create Account','Create Record') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `activity_type`, `timestamp`) VALUES
(1, 1, 'Logout', '2025-11-15 17:28:54'),
(2, 1, 'Login', '2025-11-15 17:29:18'),
(3, 3, 'Logout', '2025-11-15 17:30:18'),
(4, 1, 'Login', '2025-11-15 17:30:22'),
(5, 1, 'Logout', '2025-11-15 17:31:24');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `admin_id`, `title`, `content`, `image_path`, `is_archived`, `created_at`) VALUES
(2, 1, 'Archive: Dengue Awareness Seminar', 'Thank you to all who attended our Dengue Awareness Seminar last month. Your participation made it a huge success!', NULL, 1, '2025-11-15 15:08:30'),
(3, 1, 'Upcoming Medical Mission - Purok 1!', 'We are excited to announce a free medical mission for all residents of Purok 1 on November 20, 2025. Services include free check-ups, vitamin distribution, and dental consultation.', 'images/Mission Sample.jpg', 0, '2025-11-15 17:29:54');

-- --------------------------------------------------------

--
-- Table structure for table `follow_ups`
--

CREATE TABLE `follow_ups` (
  `follow_up_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `hw_id` int(11) DEFAULT NULL,
  `date_scheduled` date DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Done','Missed') NOT NULL DEFAULT 'Pending',
  `hw_comments` text DEFAULT NULL,
  `patient_request` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follow_ups`
--

INSERT INTO `follow_ups` (`follow_up_id`, `patient_id`, `hw_id`, `date_scheduled`, `status`, `hw_comments`, `patient_request`, `created_at`) VALUES
(1, 1, NULL, '2025-11-20', 'Pending', 'Please come back for a final check-up on your bronchitis.', NULL, '2025-11-15 15:08:30');

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `record_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `hw_id` int(11) DEFAULT NULL,
  `record_date` date NOT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `temperature_c` decimal(4,2) DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `chief_complaint` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`record_id`, `patient_id`, `hw_id`, `record_date`, `height_cm`, `weight_kg`, `temperature_c`, `blood_pressure`, `chief_complaint`, `diagnosis`, `notes`, `created_at`) VALUES
(1, 1, NULL, '2025-11-10', 170.00, 75.00, 36.50, '120/80', 'Persistent cough for 1 week', 'Acute Bronchitis', 'Prescribed cough syrup. Advised rest.', '2025-11-15 15:08:30'),
(2, 1, NULL, '2025-11-15', 170.00, 74.50, 36.70, '110/70', 'Follow-up on cough', 'Improving', 'Cough has subsided. Continue rest.', '2025-11-15 15:08:30');

-- --------------------------------------------------------

--
-- Table structure for table `hw_missions`
--

CREATE TABLE `hw_missions` (
  `hw_mission_id` int(11) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `hw_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `hw_notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hw_missions`
--

INSERT INTO `hw_missions` (`hw_mission_id`, `mission_id`, `hw_id`, `status`, `hw_notes`, `updated_at`) VALUES
(1, 1, 6, 'Approved', NULL, '2025-11-15 15:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `missions`
--

CREATE TABLE `missions` (
  `mission_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `missions`
--

INSERT INTO `missions` (`mission_id`, `admin_id`, `title`, `details`, `created_at`) VALUES
(1, 1, 'Purok 2 Love Sickness', 'Francelle is love sick', '2025-11-15 15:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `purok` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `full_name`, `dob`, `address`, `purok`, `contact_number`, `created_at`) VALUES
(1, 3, 'Leo Santos', '1990-05-15', 'Brgy. Health, Bacolod City', 'Purok 1', '09171234567', '2025-11-15 15:08:30'),
(3, 5, 'Francelle Herni Banjao', NULL, NULL, 'Purok 2', NULL, '2025-11-15 15:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review_type` enum('Suggestion','Feedback','Comment','Review') NOT NULL DEFAULT 'Feedback',
  `message` text NOT NULL,
  `admin_status` enum('New','Reviewed') NOT NULL DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `review_type`, `message`, `admin_status`, `created_at`) VALUES
(1, 3, 'Feedback', 'The consultation with HW Maria was very helpful. My cough is much better now. Thank you!', 'New', '2025-11-15 15:08:30'),
(3, 3, 'Review', 'Thank you', 'New', '2025-11-15 17:30:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_text` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('Admin','Health Worker','Patient','Guest') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_text`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', 'password', 'Admin User', 'Admin', '2025-11-15 15:08:30'),
(3, 'patient_leo', 'password', 'Leo Santos', 'Patient', '2025-11-15 15:08:30'),
(4, 'guest_sk', 'password', 'SK Chairman', 'Guest', '2025-11-15 15:08:30'),
(5, 'fcbanjao', '12345', 'Francelle Herni Banjao', 'Patient', '2025-11-15 15:10:14'),
(6, 'hw_maria', 'password', 'Maria Dela Cruz', 'Health Worker', '2025-11-15 15:13:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `follow_ups`
--
ALTER TABLE `follow_ups`
  ADD PRIMARY KEY (`follow_up_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `hw_id` (`hw_id`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `hw_id` (`hw_id`);

--
-- Indexes for table `hw_missions`
--
ALTER TABLE `hw_missions`
  ADD PRIMARY KEY (`hw_mission_id`),
  ADD UNIQUE KEY `mission_hw_unique` (`mission_id`,`hw_id`),
  ADD KEY `hw_id` (`hw_id`);

--
-- Indexes for table `missions`
--
ALTER TABLE `missions`
  ADD PRIMARY KEY (`mission_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `follow_ups`
--
ALTER TABLE `follow_ups`
  MODIFY `follow_up_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hw_missions`
--
ALTER TABLE `hw_missions`
  MODIFY `hw_mission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `missions`
--
ALTER TABLE `missions`
  MODIFY `mission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `follow_ups`
--
ALTER TABLE `follow_ups`
  ADD CONSTRAINT `follow_ups_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follow_ups_ibfk_2` FOREIGN KEY (`hw_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `health_records_ibfk_2` FOREIGN KEY (`hw_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `hw_missions`
--
ALTER TABLE `hw_missions`
  ADD CONSTRAINT `hw_missions_ibfk_1` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`mission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hw_missions_ibfk_2` FOREIGN KEY (`hw_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `missions`
--
ALTER TABLE `missions`
  ADD CONSTRAINT `missions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

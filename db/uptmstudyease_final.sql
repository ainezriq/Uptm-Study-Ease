-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2025 at 08:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;

-- Database: `uptmstudyease`

-- Table structure for table `user_events`
CREATE TABLE `user_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`userId`) REFERENCES `users`(`userId`) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table structure for table `enrollments`
CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject_code` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`subject_code`) REFERENCES `subjects`(`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `notices`
CREATE TABLE `notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `subjects`
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(255) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_code` (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(50) NOT NULL,
  `userId` varchar(255) NOT NULL,

  `userType` enum('Lecturer','Student') NOT NULL,
  `course` varchar(50) NOT NULL,
  `subjects` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `userId` (`userId`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data for `users`
INSERT INTO `users` (`username`, `email`, `password`, `userId`, `userType`, `course`, `subjects`) VALUES

('izzrieq', 'izzrieqilhan@gmail.com', 'hi', 'am123456789', 'Student', 'Computer Science', NULL),
('adminCC101', 'cc101cood@gmail.com', 'test', '232211', 'Lecturer', 'Computer Science', NULL),
('Adam', 'adam@gmail.com', 'adam123', '123456789', 'Student', 'Cyber Security', NULL),
('fazura', 'ct203cood@gmail.com', 'abc456', 'am55444', 'Lecturer', 'Early Childhood Educ', NULL),
('ainz', 'ainzrique@gmail.com', '12345678', 'AM2307013916', 'Student', 'CC101 - Diploma in Computer Science', 'FYP3024'),
('khairun', 'lecturer@uptm.edu.my', 'abc123', 'FP12345', 'Lecturer', 'CC101 - Diploma in Computer Science', NULL);

-- Insert sample data for `user_events`
INSERT INTO `user_events` (`userId`, `title`, `event_date`) VALUES
('am123456789', 'Math Exam', '2025-04-01 10:00:00'),
('232211', 'Lecture on Database Systems', '2025-04-02 14:00:00');


-- Insert sample data for `notices`
INSERT INTO `notices` (`content`, `created_at`, `file_path`, `user_id`, `subject_id`) VALUES
('Exam', '2025-03-07 06:04:38', 'uploads/67ca8c76d5ba7-IzzrieqIllhanPahlaviBinMohammadRedhaPahlavi(AM2311015184).pdf', 3, 0),
('This is a test', '2025-03-07 18:28:20', '', 3, 0),
('SWC3404', '2025-03-24 13:39:50', 'uploads/67e160a68172c-labTask_Ain(1).pdf', 3, 0);

-- Insert sample data for `subjects`
INSERT INTO `subjects` (`subject_code`, `subject_name`) VALUES
('ITC1083', 'Business Information Management Strategy'),
('ITC2173', 'Enterprise Information Systems'),
('ITC2193', 'Information Technology Essentials'),
('ARC3043', 'Linux OS'),
('SWC3403', 'Introduction to Mobile Application Development'),
('FYP3024', 'Computing Project');



COMMIT;

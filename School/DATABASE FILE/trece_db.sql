-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2025 at 08:23 AM
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
-- Database: `metanoiah_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `attendance` int(11) DEFAULT 0,
  `timestamp_in` timestamp NULL DEFAULT current_timestamp(),
  `timestamp_out` timestamp NULL DEFAULT NULL,
  `late` int(11) DEFAULT 0,
  `late_in_hours_minutes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `student_id`, `attendance`, `timestamp_in`, `timestamp_out`, `late`, `late_in_hours_minutes`) VALUES
(408, 57, 0, '2025-04-06 01:52:00', '2025-04-06 08:52:00', 202, '3:22'),
(409, 57, 0, '2025-04-14 01:52:00', '2025-04-14 01:52:00', 202, '3:22'),
(410, 101, 0, '2025-04-06 21:56:00', '2025-04-07 06:56:00', 0, '00:00'),
(411, 101, 0, '2025-04-13 23:57:00', '2025-04-21 07:57:00', 87, '1:27'),
(412, 60, 0, '2025-05-02 03:08:56', NULL, 278, '4:38'),
(413, 101, 0, '2025-05-02 12:00:08', NULL, 810, '13:30'),
(414, 102, 0, '2025-05-02 12:10:29', NULL, 820, '13:40'),
(415, 101, 0, '2025-05-03 07:20:00', NULL, 529, '8:49'),
(416, 102, 0, '2025-05-03 07:50:31', NULL, 555, '9:15'),
(417, 102, 0, '2025-05-05 01:16:28', NULL, 152, '2:32'),
(418, 103, 0, '2025-05-05 05:20:36', NULL, 410, '6:50');

--
-- Triggers `attendances`
--
DELIMITER $$
CREATE TRIGGER `before_attendance_insert` BEFORE INSERT ON `attendances` FOR EACH ROW BEGIN
  DECLARE late_minutes INT;

  -- Calculate late in minutes
  SET late_minutes = CASE
    WHEN TIMESTAMPDIFF(MINUTE, CONCAT(DATE(NEW.timestamp_in), ' 06:30:00'), NEW.timestamp_in) > 0
    THEN TIMESTAMPDIFF(MINUTE, CONCAT(DATE(NEW.timestamp_in), ' 06:30:00'), NEW.timestamp_in)
    ELSE 0
  END;

  -- Set late column
  SET NEW.late = late_minutes;

  -- Calculate late_in_hours_minutes in HH:MM format
  SET NEW.late_in_hours_minutes = CASE
    WHEN late_minutes > 0 THEN
      CONCAT(FLOOR(late_minutes / 60), ':', LPAD(late_minutes % 60, 2, '0'))
    ELSE '00:00'
  END;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calculate_minutes_late` BEFORE INSERT ON `attendances` FOR EACH ROW BEGIN
    DECLARE cutoff_time TIME;
    DECLARE minutes_late INT;

    -- Set the cutoff time to 6:30 AM
    SET cutoff_time = '06:30:00';

    -- Calculate minutes late
    IF TIME(NEW.timestamp_in) > cutoff_time THEN
        SET minutes_late = TIMESTAMPDIFF(MINUTE, CONCAT(DATE(NEW.timestamp_in), ' ', cutoff_time), NEW.timestamp_in);
        SET NEW.late = minutes_late;
    ELSE
        SET NEW.late = 0;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `set_late_on_insert` BEFORE INSERT ON `attendances` FOR EACH ROW BEGIN
    IF TIME(NEW.timestamp_in) > '06:30:00' THEN
        SET NEW.late = TRUE;
    ELSE
        SET NEW.late = FALSE;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `strand` varchar(255) DEFAULT NULL,
  `grade_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `strand`, `grade_level`) VALUES
(60, 'A', 'STEM', 11),
(61, 'B', 'HUMSS', 12),
(67, 'C', 'STEM', 11);

-- --------------------------------------------------------

--
-- Table structure for table `faculty_categories`
--

CREATE TABLE `faculty_categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_categories`
--

INSERT INTO `faculty_categories` (`id`, `user_id`, `categorie_id`) VALUES
(8, 44, 39),
(9, 52, 40),
(10, 54, 45),
(11, 55, 46),
(12, 56, 47),
(13, 57, 48),
(14, 58, 49),
(15, 59, 50),
(16, 60, 51),
(17, 61, 52),
(18, 62, 53),
(19, 63, 54),
(20, 64, 55),
(21, 65, 56),
(22, 66, 57),
(23, 67, 58),
(24, 68, 59),
(25, 88, 38),
(27, 162, 60);

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `file_name`, `file_type`) VALUES
(1, 'DALL·E 2023-04-07 12.21.20.png', 'image/png'),
(3, '55C4DECE-ADEE-421B-9920-51E6AB8F6B2C.png', 'image/png'),
(4, 'Discord-Logo-PNG-Images.png', 'image/png'),
(5, '3C6EFE39-21E8-4C67-89EB-9F5C5573B9D9.png', 'image/png'),
(6, 'Agent Kyuu.png', 'image/png'),
(7, 'default.png', 'image/png');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `student_username` varchar(100) DEFAULT NULL,
  `student_password` varchar(255) NOT NULL,
  `categorie_id` int(11) UNSIGNED NOT NULL,
  `media_id` int(11) DEFAULT 0,
  `date` datetime NOT NULL,
  `phone_id` varchar(20) DEFAULT NULL,
  `gender` varchar(10) NOT NULL,
  `address` varchar(255) NOT NULL,
  `student_image` varchar(255) DEFAULT 'default.png',
  `user_level` int(11) DEFAULT 4,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `name`, `student_username`, `student_password`, `categorie_id`, `media_id`, `date`, `phone_id`, `gender`, `address`, `student_image`, `user_level`, `user_id`) VALUES
(1, 'Juan Dela Cruz Jr.', NULL, '', 60, 1, '2025-04-20 00:00:00', '105', 'Male', 'Trece Martires', 'juan_jr.png', 4, NULL),
(2, 'Maria Santos Jr.', NULL, '', 60, 2, '2025-04-20 00:00:00', '106', 'Female', 'Perez', 'maria_jr.png', 4, NULL),
(3, 'Jose Ramirez Jr.', NULL, '', 60, 3, '2025-04-20 00:00:00', '107', 'Male', 'Trece Martires', 'jose_jr.png', 4, NULL),
(4, 'Anna Reyes Jr.', NULL, '', 60, 4, '2025-04-20 00:00:00', '108', 'Female', 'Perez', 'anna_jr.png', 4, NULL),
(5, 'Rafael Mendoza Jr.', NULL, '', 60, 5, '2025-04-20 00:00:00', '109', 'Male', 'Trece Martires', 'rafael_jr.png', 4, NULL),
(6, 'Shiela Garcia Jr.', NULL, '', 60, 6, '2025-04-20 00:00:00', '110', 'Female', 'Perez', 'shiela_jr.png', 4, NULL),
(7, 'Eduardo Torres Jr.', NULL, '', 60, 7, '2025-04-20 00:00:00', '111', 'Male', 'Trece Martires', 'eduardo_jr.png', 4, NULL),
(8, 'Cynthia Cruz Jr.', NULL, '', 60, 8, '2025-04-20 00:00:00', '112', 'Female', 'Perez', 'cynthia_jr.png', 4, NULL),
(9, 'Oliver Villanueva Jr.', NULL, '', 60, 9, '2025-04-20 00:00:00', '113', 'Male', 'Trece Martires', 'oliver_jr.png', 4, NULL),
(10, 'Felisa Castillo Jr.', NULL, '', 60, 10, '2025-04-20 00:00:00', '114', 'Female', 'Perez', 'felisa_jr.png', 4, NULL),
(11, 'Lucia Salazar Jr.', NULL, '', 60, 11, '2025-04-20 00:00:00', '115', 'Female', 'Trece Martires', 'lucia_jr.png', 4, NULL),
(12, 'Carlos Pascual Jr.', NULL, '', 60, 12, '2025-04-20 00:00:00', '116', 'Male', 'Perez', 'carlos_jr.png', 4, NULL),
(13, 'Isabella Ramos Jr.', NULL, '', 60, 13, '2025-04-20 00:00:00', '117', 'Female', 'Trece Martires', 'isabella_jr.png', 4, NULL),
(14, 'Antonio Navarro Jr.', NULL, '', 60, 14, '2025-04-20 00:00:00', '118', 'Male', 'Perez', 'antonio_jr.png', 4, NULL),
(15, 'Patricia Lim Jr.', NULL, '', 60, 15, '2025-04-20 00:00:00', '119', 'Female', 'Trece Martires', 'patricia_jr.png', 4, NULL),
(16, 'Victor Dela Cruz Jr.', NULL, '', 60, 16, '2025-04-20 00:00:00', '120', 'Male', 'Perez', 'victor_jr.png', 4, NULL),
(17, 'Monica De Guzman Jr.', NULL, '', 60, 17, '2025-04-20 00:00:00', '121', 'Female', 'Trece Martires', 'monica_jr.png', 4, NULL),
(18, 'Emilio Reyes Jr.', NULL, '', 60, 18, '2025-04-20 00:00:00', '122', 'Male', 'Perez', 'emilio_jr.png', 4, NULL),
(19, 'Teresa Garcia Jr.', NULL, '', 60, 19, '2025-04-20 00:00:00', '123', 'Female', 'Trece Martires', 'teresa_jr.png', 4, NULL),
(20, 'Miguel Villanueva Jr.', NULL, '', 60, 20, '2025-04-20 00:00:00', '124', 'Male', 'Perez', 'miguel_jr.png', 4, NULL),
(21, 'Raquel Fernandez Jr.', NULL, '', 60, 21, '2025-04-20 00:00:00', '125', 'Female', 'Trece Martires', 'raquel_jr.png', 4, NULL),
(22, 'Carlos Jimenez Jr.', NULL, '', 60, 22, '2025-04-20 00:00:00', '126', 'Male', 'Perez', 'carlos_jr.png', 4, NULL),
(23, 'Diana Moreno Jr.', NULL, '', 60, 23, '2025-04-20 00:00:00', '127', 'Female', 'Trece Martires', 'diana_jr.png', 4, NULL),
(24, 'Oscar Diaz Jr.', NULL, '', 60, 24, '2025-04-20 00:00:00', '128', 'Male', 'Perez', 'oscar_jr.png', 4, NULL),
(25, 'Elsa Navarro Jr.', NULL, '', 60, 25, '2025-04-20 00:00:00', '129', 'Female', 'Trece Martires', 'elsa_jr.png', 4, NULL),
(26, 'Ricardo Santos Jr.', NULL, '', 60, 26, '2025-04-20 00:00:00', '130', 'Male', 'Perez', 'ricardo_jr.png', 4, NULL),
(27, 'Jovita Medina', NULL, '', 60, 27, '2025-04-20 00:00:00', '32', 'Female', 'Trece Martires', 'jovita_jr.png', 4, NULL),
(28, 'Lydia Bautista Jr.', NULL, '', 60, 28, '2025-04-20 00:00:00', '132', 'Female', 'Perez', 'lydia_jr.png', 4, NULL),
(29, 'Rafael Perez Jr.', NULL, '', 60, 29, '2025-04-20 00:00:00', '133', 'Male', 'Trece Martires', 'rafael_jr.png', 4, NULL),
(30, 'Nina Aguilar Jr.', NULL, '', 60, 30, '2025-04-20 00:00:00', '134', 'Female', 'Perez', 'nina_jr.png', 4, NULL),
(31, 'Antonio Cabrera Jr.', NULL, '', 60, 31, '2025-04-20 00:00:00', '135', 'Male', 'Trece Martires', 'antonio_jr.png', 4, NULL),
(32, 'Rebecca Villanueva Jr.', NULL, '', 60, 32, '2025-04-20 00:00:00', '136', 'Female', 'Perez', 'rebecca_jr.png', 4, NULL),
(33, 'Antonio Reyes Jr.', NULL, '', 60, 33, '2025-04-20 00:00:00', '137', 'Male', 'Trece Martires', 'antonio_jr.png', 4, NULL),
(34, 'Benito Castro Jr.', NULL, '', 60, 34, '2025-04-20 00:00:00', '138', 'Male', 'Perez', 'benito_jr.png', 4, NULL),
(35, 'Eleanor Garcia Jr.', NULL, '', 60, 35, '2025-04-20 00:00:00', '139', 'Female', 'Trece Martires', 'eleanor_jr.png', 4, NULL),
(36, 'Erica Lopez Jr.', NULL, '', 60, 36, '2025-04-20 00:00:00', '140', 'Female', 'Perez', 'erica_jr.png', 4, NULL),
(37, 'Victorina Mendoza Jr.', NULL, '', 60, 37, '2025-04-20 00:00:00', '141', 'Female', 'Trece Martires', 'victorina_jr.png', 4, NULL),
(38, 'Patricia Vargas Jr.', NULL, '', 60, 38, '2025-04-20 00:00:00', '142', 'Female', 'Perez', 'patricia_jr.png', 4, NULL),
(39, 'Randy De La Cruz Jr.', NULL, '', 60, 39, '2025-04-20 00:00:00', '143', 'Male', 'Trece Martires', 'randy_jr.png', 4, NULL),
(40, 'Lourdes Garcia Jr.', NULL, '', 60, 40, '2025-04-20 00:00:00', '144', 'Female', 'Perez', 'lourdes_jr.png', 4, NULL),
(41, 'Carlos Fernandez Jr.', NULL, '', 60, 41, '2025-04-20 00:00:00', '145', 'Male', 'Trece Martires', 'carlosf_jr.png', 4, NULL),
(42, 'Hilda Castillo Jr.', NULL, '', 60, 42, '2025-04-20 00:00:00', '146', 'Female', 'Perez', 'hilda_jr.png', 4, NULL),
(43, 'Marco Dizon Jr.', NULL, '', 60, 43, '2025-04-20 00:00:00', '147', 'Male', 'Trece Martires', 'marco_jr.png', 4, NULL),
(44, 'Gloria Ramos Jr.', NULL, '', 60, 44, '2025-04-20 00:00:00', '148', 'Female', 'Perez', 'gloria_jr.png', 4, NULL),
(45, 'Gerardo Torres Jr.', NULL, '', 60, 45, '2025-04-20 00:00:00', '149', 'Male', 'Trece Martires', 'gerardo_jr.png', 4, NULL),
(46, 'Jasmine Bautista Jr.', NULL, '', 60, 46, '2025-04-20 00:00:00', '150', 'Female', 'Perez', 'jasmine_jr.png', 4, NULL),
(47, 'Lilian Mendoza Jr.', NULL, '', 60, 47, '2025-04-20 00:00:00', '151', 'Female', 'Trece Martires', 'lilian_jr.png', 4, NULL),
(48, 'Leonardo Dela Cruz Jr.', NULL, '', 60, 48, '2025-04-20 00:00:00', '152', 'Male', 'Perez', 'leonardo_jr.png', 4, NULL),
(49, 'Regina Perez', NULL, '', 61, 49, '2025-04-20 00:00:00', '32', 'Female', 'Trece Martires', 'regina_jr.png', 4, NULL),
(50, 'Clara Salazar Jr.', NULL, '', 60, 50, '2025-04-20 00:00:00', '154', 'Female', 'Perez', 'clara_jr.png', 4, NULL),
(51, 'Vicente Lopez Jr.', NULL, '', 60, 51, '2025-04-20 00:00:00', '155', 'Male', 'Trece Martires', 'vicente_jr.png', 4, NULL),
(52, 'Manuel Vargas Jr.', NULL, '', 60, 52, '2025-04-20 00:00:00', '156', 'Male', 'Perez', 'manuel_jr.png', 4, NULL),
(53, 'Maria Aquino Jr.', NULL, '', 60, 53, '2025-04-20 00:00:00', '157', 'Female', 'Trece Martires', 'maria_jr.png', 4, NULL),
(54, 'Eduardo Reyes Jr.', NULL, '', 60, 54, '2025-04-20 00:00:00', '158', 'Male', 'Perez', 'eduardo_jr.png', 4, NULL),
(55, 'Jovita Santos Jr.', NULL, '', 60, 55, '2025-04-20 00:00:00', '159', 'Female', 'Trece Martires', 'jovita_santos_jr.png', 4, NULL),
(56, 'Hector Mendoza Jr.', NULL, '', 60, 56, '2025-04-20 00:00:00', '160', 'Male', 'Perez', 'hector_jr.png', 4, NULL),
(57, 'Marcelino Lopez', NULL, '', 60, 57, '2025-04-20 00:00:00', '32', 'Male', 'Trece Martires', 'marcelino_jr.png', 4, NULL),
(58, 'Katherine Tan Jr.', NULL, '', 60, 58, '2025-04-20 00:00:00', '162', 'Female', 'Perez', 'katherine_jr.png', 4, NULL),
(59, 'Albert Gomez', NULL, '', 61, 59, '2025-04-20 00:00:00', '90', 'Male', 'Trece Martires', 'albert_jr.png', 4, NULL),
(60, 'Ramona Lim', NULL, '', 60, 60, '2025-04-20 00:00:00', '90', 'Female', 'Perez', 'ramona_jr.png', 4, NULL),
(101, 'Alexandra Carla', 'Carla', '$2y$10$hBPq61daKlRDKMCBBWGNYuPZf9teX9PCkPmXxdmMFYLgsR0fnhs22', 60, 0, '2025-04-21 09:55:54', '90', 'Female', 'Trece Martires', '', 4, 159),
(102, 'Chester Licuanan Pogi', 'AkoSichester', '$2y$10$61T8ru49J.IoEnd/YScY7OAqo6.bNaAbdcOPYdFKLJB8RorsCOnSm', 60, 0, '2025-05-02 20:08:45', '32', 'Male', 'adfafaf', '', 4, 161),
(103, 'Khenny', 'Khenny', '$2y$10$bPYTYwwCTInrGpvkNr0xS.nrGVngZpHEL36oCE2x3/RFWRif8tMDe', 60, 0, '2025-05-05 13:20:01', '164', 'Female', 'Trece Martires', '', 4, 165),
(116, 'Verna Kabaya', 'vkabaya', '$2y$10$Mcni3YG/RYMs1NLqfXmK2.SGysqxCidBT9.ZWjF/83VHLw/CHu5e.', 61, 0, '2025-05-05 00:00:00', '106', 'Female', 'Trece Martires', 'default.png', 4, 181);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `name` varchar(60) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_level` int(11) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `image` varchar(255) DEFAULT 'defualt.png',
  `status` int(1) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `name`, `username`, `password`, `user_level`, `phone_number`, `image`, `status`, `last_login`, `email`) VALUES
(32, NULL, 'Hat Dogy', 'Janzel User', '$2y$10$UWYzZfH7OdNxUqF1iJiy/u4YN6g57Z53TAOrUuz9mkDkKnj5NUpVy', 3, '+639664587096', 'v7rsg84f32.png', 1, '2025-05-05 08:40:02', ''),
(33, NULL, 'Thaliaadmin', 'Thaliaadmin', '$2y$10$K59neEKhwCByQ/2qZjypBOnaA7ALwvvKWArdp33t0VfXka/iH.lGW', 1, NULL, 'styrjg433.png', 1, '2025-05-06 11:23:36', ''),
(45, NULL, 'Chester Papa', 'Papamo', '$2y$10$yPbj04r0/k2fBldTaS7Jb.GTPuoVD33pTLjRpPMfTx7ra1p7M7Bsa', 3, '+18777804236', 'm1rqw02l45.png', 1, '2025-05-02 20:11:06', 'clicuanan007@gmail.com'),
(53, NULL, 'Pirate Bai', 'Pirate', '$2y$10$3TUs0jTepKIEqzhJXxxDBOoGcDJoChDRTjrD5tcHKgOEjoGbv1vhm', 1, '', 'lewvwpzw53.png', 0, NULL, 'pirate@gmail.com'),
(54, NULL, 'Faculty 1', 'faculty1', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty1@example.com'),
(55, NULL, 'Faculty 2', 'faculty2', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty2@example.com'),
(56, NULL, 'Faculty 3', 'faculty3', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty3@example.com'),
(57, NULL, 'Faculty 4', 'faculty4', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty4@example.com'),
(58, NULL, 'Faculty 5', 'faculty5', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty5@example.com'),
(59, NULL, 'Faculty 6', 'faculty6', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty6@example.com'),
(60, NULL, 'Faculty 7', 'faculty7', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty7@example.com'),
(61, NULL, 'Faculty 8', 'faculty8', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty8@example.com'),
(62, NULL, 'Faculty 9', 'faculty9', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty9@example.com'),
(63, NULL, 'Faculty 10', 'faculty10', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty10@example.com'),
(64, NULL, 'Faculty 11', 'faculty11', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty11@example.com'),
(65, NULL, 'Faculty 12', 'faculty12', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty12@example.com'),
(66, NULL, 'Faculty 13', 'faculty13', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty13@example.com'),
(67, NULL, 'Faculty 14', 'faculty14', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty14@example.com'),
(68, NULL, 'Faculty 15', 'faculty15', '$2y$10$exAp6ZPqrDF.b5pErU7pZsH3m5ESx63PSzVZ1SYhH9jOaPQPKPTfC', 2, NULL, 'no_image.jpg', 1, NULL, 'faculty15@example.com'),
(88, NULL, 'Chester Teacher', 'Chester', '$2y$10$Xp4HId5O7Exw390VIbc9gemVfrmnZ82MjAeMVCn8kUM38XDMb2g7a', 2, NULL, 'sbvtj1s588.png', 1, '2025-04-15 12:15:16', 'chester_licuanan@yahoo.com'),
(89, NULL, 'guro', 'guro', '$2y$10$DTkwC9e4tq7XBEIaYGc0SuOoIhFjKRnudwR9ZCTObLp0tWYOPgFKe', 2, NULL, 'defualt.png', 1, '2025-04-14 13:34:09', 'guro@gmail.com'),
(90, NULL, 'Chester Licuana', 'ChesterUser', '$2y$10$wCKQUMsSdlxTJXT.PaNUduwP1hIHzWzBXQS41eL0Z3TGyrp3B3iOi', 3, '+639159688404', 'defualt.png', 1, '2025-04-15 10:37:49', 'pogiako@gmail.com'),
(94, NULL, 'chester chester', 'chester', '$2y$10$u32G37kpcm79gL80oQl47ecLJXWRRSw77tDxzTL/VLHyk50xtBwqC', 1, NULL, '/test1/images/defualt.png', 1, NULL, 'clicuanan@gmail.com'),
(95, NULL, 'hahahah ahaha', 'ahaha', '$2y$10$1Wp95WF2/p9GtFV50LQ1qePHyEmipA2O4aDdOH7lZz/daFcT8hlSq', 1, NULL, '/test1/images/defualt.png', 1, NULL, 'ahaha@gmail.com'),
(101, NULL, 'pogiako akopogi', 'igop', '$2y$10$GLcjtnrp6DRtjAr6SlLk4.VzKEqcyjCQ18sutHIwW2T5uu9TXDbAe', 1, NULL, 'defualt.png', 1, NULL, 'pogi@gmail.com'),
(102, NULL, 'jm mj', 'jm', '$2y$10$1mhSZ901WhwHtCYj8yJf3urMgMWUByMp/6m/xKLpMzlKWpAUJ4eba', 1, NULL, 'defualt.png', 1, NULL, 'jm@gmail.com'),
(103, NULL, 'haha student', 'studentka', '$2y$10$PuOBb/mHGQ2D3t2rsIWxaOAX3krhC.5Pexmm22n/faGSX7Y0fiAx6', 4, NULL, 'defualt.png', 1, '2025-04-15 20:02:17', ''),
(104, NULL, 'try', 'try', '$2y$10$NYV2heUcfi9KwubfYxwZUOyNOl1h3ZIdrz47.dbj5.FcDaeX3W6CW', 4, NULL, 'rhxxtjgb104.png', 1, '2025-04-21 09:54:16', ''),
(105, NULL, 'Juan Dela Cruz', 'juan123', 'password123', 3, '09271234567', 'default.png', 1, NULL, 'juan@gmail.com'),
(106, NULL, 'Maria Santos', 'maria123', 'password123', 3, '09381234568', 'default.png', 1, NULL, 'maria@gmail.com'),
(107, NULL, 'Jose Ramirez', 'jose123', 'password123', 3, '09491234569', 'default.png', 1, NULL, 'jose@gmail.com'),
(108, NULL, 'Anna Reyes', 'anna123', 'password123', 3, '09501234570', 'default.png', 1, NULL, 'anna@gmail.com'),
(109, NULL, 'Rafael Mendoza', 'rafael123', 'password123', 3, '09611234571', 'default.png', 1, NULL, 'rafael@gmail.com'),
(110, NULL, 'Shiela Garcia', 'shiela123', 'password123', 3, '09721234572', 'default.png', 1, NULL, 'shiela@gmail.com'),
(111, NULL, 'Eduardo Torres', 'eduardo123', 'password123', 3, '09831234573', 'default.png', 1, NULL, 'eduardo@gmail.com'),
(112, NULL, 'Cynthia Cruz', 'cynthia123', 'password123', 3, '09941234574', 'default.png', 1, NULL, 'cynthia@gmail.com'),
(113, NULL, 'Oliver Villanueva', 'oliver123', 'password123', 3, '09151234575', 'default.png', 1, NULL, 'oliver@gmail.com'),
(114, NULL, 'Felisa Castillo', 'felisa123', 'password123', 3, '09261234576', 'default.png', 1, NULL, 'felisa@gmail.com'),
(115, NULL, 'Lucia Salazar', 'lucia123', 'password123', 3, '09371234577', 'default.png', 1, NULL, 'lucia@gmail.com'),
(116, NULL, 'Carlos Pascual', 'carlos123', 'password123', 3, '09481234578', 'default.png', 1, NULL, 'carlos@gmail.com'),
(117, NULL, 'Isabella Ramos', 'isabella123', 'password123', 3, '09591234579', 'default.png', 1, NULL, 'isabella@gmail.com'),
(118, NULL, 'Antonio Navarro', 'antonio123', 'password123', 3, '09601234580', 'default.png', 1, NULL, 'antonio@gmail.com'),
(119, NULL, 'Patricia Lim', 'patricia123', 'password123', 3, '09711234581', 'default.png', 1, NULL, 'patricia@gmail.com'),
(120, NULL, 'Victor Dela Cruz', 'victor123', 'password123', 3, '09821234582', 'default.png', 1, NULL, 'victor@gmail.com'),
(121, NULL, 'Monica De Guzman', 'monica123', 'password123', 3, '09931234583', 'default.png', 1, NULL, 'monica@gmail.com'),
(122, NULL, 'Emilio Reyes', 'emilio123', 'password123', 3, '09141234584', 'default.png', 1, NULL, 'emilio@gmail.com'),
(123, NULL, 'Teresa Garcia', 'teresa123', 'password123', 3, '09251234585', 'default.png', 1, NULL, 'teresa@gmail.com'),
(124, NULL, 'Miguel Villanueva', 'miguel123', 'password123', 3, '09361234586', 'default.png', 1, NULL, 'miguel@gmail.com'),
(125, NULL, 'Raquel Fernandez', 'raquel123', 'password123', 3, '09471234587', 'default.png', 1, NULL, 'raquel@gmail.com'),
(126, NULL, 'Carlos Jimenez', 'carlosj123', 'password123', 3, '09581234588', 'default.png', 1, NULL, 'carlosj@gmail.com'),
(127, NULL, 'Diana Moreno', 'diana123', 'password123', 3, '09691234589', 'default.png', 1, NULL, 'diana@gmail.com'),
(128, NULL, 'Oscar Diaz', 'oscar123', 'password123', 3, '09701234590', 'default.png', 1, NULL, 'oscar@gmail.com'),
(129, NULL, 'Elsa Navarro', 'elsa123', 'password123', 3, '09811234591', 'default.png', 1, NULL, 'elsa@gmail.com'),
(130, NULL, 'Ricardo Santos', 'ricardo123', 'password123', 3, '09921234592', 'default.png', 1, NULL, 'ricardo@gmail.com'),
(131, NULL, 'Jovita Medina', 'jovita123', 'password123', 3, '09161234593', 'default.png', 1, NULL, 'jovita@gmail.com'),
(132, NULL, 'Lydia Bautista', 'lydia123', 'password123', 3, '09271234594', 'default.png', 1, NULL, 'lydia@gmail.com'),
(133, NULL, 'Rafael Perez', 'rafael123', 'password123', 3, '09381234595', 'default.png', 1, NULL, 'rafael@gmail.com'),
(134, NULL, 'Nina Aguilar', 'nina123', 'password123', 3, '09491234596', 'default.png', 1, NULL, 'nina@gmail.com'),
(135, NULL, 'Antonio Cabrera', 'antonio123', 'password123', 3, '09501234597', 'default.png', 1, NULL, 'antonio@gmail.com'),
(136, NULL, 'Rebecca Villanueva', 'rebecca123', 'password123', 3, '09611234598', 'default.png', 1, NULL, 'rebecca@gmail.com'),
(137, NULL, 'Antonio Reyes', 'antonio123', 'password123', 3, '09721234599', 'default.png', 1, NULL, 'antonio@gmail.com'),
(138, NULL, 'Benito Castro', 'benito123', 'password123', 3, '09831234500', 'default.png', 1, NULL, 'benito@gmail.com'),
(139, NULL, 'Eleanor Garcia', 'eleanor123', 'password123', 3, '09941234501', 'default.png', 1, NULL, 'eleanor@gmail.com'),
(140, NULL, 'Erica Lopez', 'erica123', 'password123', 3, '09151234502', 'default.png', 1, NULL, 'erica@gmail.com'),
(141, NULL, 'Victorina Mendoza', 'victorina123', 'password123', 3, '09261234503', 'default.png', 1, NULL, 'victorina@gmail.com'),
(142, NULL, 'Patricia Vargas', 'patricia123', 'password123', 3, '09371234504', 'default.png', 1, NULL, 'patricia@gmail.com'),
(143, NULL, 'Randy De La Cruz', 'randy123', 'password123', 3, '09481234505', 'default.png', 1, NULL, 'randy@gmail.com'),
(144, NULL, 'Lourdes Garcia', 'lourdes123', 'password123', 3, '09591234506', 'default.png', 1, NULL, 'lourdes@gmail.com'),
(145, NULL, 'Carlos Fernandez', 'carlosf123', 'password123', 3, '09601234507', 'default.png', 1, NULL, 'carlosf@gmail.com'),
(146, NULL, 'Hilda Castillo', 'hilda123', 'password123', 3, '09711234508', 'default.png', 1, NULL, 'hilda@gmail.com'),
(147, NULL, 'Marco Dizon', 'marco123', 'password123', 3, '09821234509', 'default.png', 1, NULL, 'marco@gmail.com'),
(148, NULL, 'Gloria Ramos', 'gloria123', 'password123', 3, '09931234510', 'default.png', 1, NULL, 'gloria@gmail.com'),
(149, NULL, 'Gerardo Torres', 'gerardo123', 'password123', 3, '09141234511', 'default.png', 1, NULL, 'gerardo@gmail.com'),
(150, NULL, 'Jasmine Bautista', 'jasmine123', 'password123', 3, '09251234512', 'default.png', 1, NULL, 'jasmine@gmail.com'),
(151, NULL, 'Lilian Mendoza', 'lilian123', 'password123', 3, '09361234513', 'default.png', 1, NULL, 'lilian@gmail.com'),
(152, NULL, 'Leonardo Dela Cruz', 'leonardo123', 'password123', 3, '09471234514', 'default.png', 1, NULL, 'leonardo@gmail.com'),
(153, NULL, 'Regina Perez', 'regina123', 'password123', 3, '09581234515', 'default.png', 1, NULL, 'regina@gmail.com'),
(154, NULL, 'Clara Salazar', 'clara123', 'password123', 3, '09691234516', 'default.png', 1, NULL, 'clara@gmail.com'),
(155, NULL, 'Vicente Lopez', 'vicente123', 'password123', 3, '09701234517', 'default.png', 1, NULL, 'vicente@gmail.com'),
(156, NULL, 'Manuel Vargas', 'manuel123', 'password123', 3, '09811234518', 'default.png', 1, NULL, 'manuel@gmail.com'),
(157, NULL, 'Maria Aquino', 'maria123', 'password123', 3, '09921234519', 'default.png', 1, NULL, 'maria@gmail.com'),
(158, NULL, 'Eduardo Reyes', 'eduardo123', 'password123', 3, '09161234520', 'default.png', 1, NULL, 'eduardo@gmail.com'),
(159, NULL, 'Alexandra Carla', 'Alex', '$2y$10$hBPq61daKlRDKMCBBWGNYuPZf9teX9PCkPmXxdmMFYLgsR0fnhs22', 4, '', 'defualt.png', 1, '2025-05-06 10:58:23', ''),
(161, NULL, 'Chester Licuanan', 'AkoSichester', '$2y$10$61T8ru49J.IoEnd/YScY7OAqo6.bNaAbdcOPYdFKLJB8RorsCOnSm', 4, NULL, 'defualt.png', 1, '2025-05-02 20:42:27', ''),
(162, NULL, 'Adrian Gonzalo', 'Teacher', '$2y$10$1lG9srNsc0jG/XryDXR6be65wphLcA1iUIj6/LMYK8Oe3M116KO.K', 2, NULL, 'defualt.png', 1, '2025-05-05 19:56:59', 'adrian@gmail.com'),
(164, NULL, 'Jenmar Alano', 'Jenmar', '$2y$10$/3cc7cxLetrqgyQde7ioe.NBhHYpYDkyr8HOQ0IuyaSUPHP21.iKK', 3, '+639694911585', 'defualt.png', 1, '2025-05-05 13:22:42', 'jenmar@gmail.com'),
(165, NULL, 'Khenny', 'Khenny', '$2y$10$bPYTYwwCTInrGpvkNr0xS.nrGVngZpHEL36oCE2x3/RFWRif8tMDe', 4, NULL, 'defualt.png', 1, NULL, ''),
(174, NULL, 'Crystal Pruna', 'Crystal', '$2y$10$9idnhfFqk8xl9XgVLH6KceodayMJy6cxLzpuOyh.vYpuUx7lMlYNm', 1, NULL, 'defualt.png', 1, NULL, 'crystal@gmail.com'),
(175, NULL, 'Kiana Princess', 'Kiana', '$2y$10$I.fE6pkFa77bafKOTh1DDe.UGDJWkGJvMlGyV4v9oolyHgxUC8OKa', 3, '63123123123', 'defualt.png', 1, '2025-05-05 21:21:43', 'kiana@gmail.com'),
(181, NULL, 'Verna Kabaya', 'vkabaya', '$2y$10$Mcni3YG/RYMs1NLqfXmK2.SGysqxCidBT9.ZWjF/83VHLw/CHu5e.', 4, NULL, 'defualt.png', 1, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_level` int(11) NOT NULL,
  `group_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `group_name`, `group_level`, `group_status`) VALUES
(1, 'Admin', 1, 1),
(2, 'Faculty', 2, 1),
(3, 'Parent', 3, 1),
(5, 'student', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_password_resets`
--

CREATE TABLE `user_password_resets` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `token_expiration` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_password_resets`
--

INSERT INTO `user_password_resets` (`id`, `user_id`, `reset_token`, `token_expiration`) VALUES
(1, 32, '99266eb9e692396195215f3c58675cb1', '2025-04-15 10:16:32'),
(2, 32, '59ecaaae75876202857f5574c21cffa0', '2025-04-15 10:17:40'),
(3, 32, '6bcca8f4e681ec818b60623c98dd1950', '2025-04-15 10:17:49'),
(5, 53, '76db4ac00ac91c35f812e40b7b3ee879', '2025-04-15 10:29:31'),
(7, 53, '7a6c16f3db58b28301e612d3afea7c2d', '2025-04-15 10:36:50'),
(9, 90, 'fb60f0193b02d307429bc9da4f710c14', '2025-04-21 11:34:40'),
(10, 90, '349575b70f20806eb9fab2bb08b16420', '2025-04-22 09:11:45'),
(11, 90, '07b42d21e6d531fe7e4a3e149bc1b3ba', '2025-04-22 09:42:41'),
(12, 90, '0e8089b322c6b1297ef1773bb6c30a5d', '2025-04-23 09:28:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `faculty_categories`
--
ALTER TABLE `faculty_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_level` (`user_level`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_level` (`group_level`);

--
-- Indexes for table `user_password_resets`
--
ALTER TABLE `user_password_resets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=419;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `faculty_categories`
--
ALTER TABLE `faculty_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_password_resets`
--
ALTER TABLE `user_password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `FK_products` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_user` FOREIGN KEY (`user_level`) REFERENCES `user_groups` (`group_level`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

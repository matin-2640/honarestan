-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2026 at 09:16 PM
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
-- Database: `rahdanesh`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Ad_ID` int(11) NOT NULL,
  `Ad_fullName` varchar(50) NOT NULL,
  `Ad_nationalCode` varchar(11) NOT NULL,
  `Ad_password` varchar(100) NOT NULL,
  `Ad_phone` varchar(11) NOT NULL,
  `Ad_loginDate` varchar(30) NOT NULL,
  `Ad_logoutDate` varchar(30) NOT NULL,
  `Ad_type` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Ad_ID`, `Ad_fullName`, `Ad_nationalCode`, `Ad_password`, `Ad_phone`, `Ad_loginDate`, `Ad_logoutDate`, `Ad_type`) VALUES
(1, 'یوزر اول', '111', '111', '', '', '', 0),
(2, 'یوزر دوم', '1111', '1111', '', '', '', 0),
(3, 'یوزر سوم', '11111', '11111', '', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `A_ID` int(11) NOT NULL,
  `A_studentID` int(5) NOT NULL,
  `A_date` varchar(30) NOT NULL,
  `A_courseID` int(5) NOT NULL,
  `A_type` int(1) NOT NULL,
  `A_state` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `C_ID` int(11) NOT NULL,
  `C_grade` int(2) NOT NULL,
  `C_major` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`C_ID`, `C_grade`, `C_major`) VALUES
(11, 10, 'شبکه و نرم افزار رایانه'),
(12, 11, 'شبکه و نرم افزار رایانه'),
(13, 10, 'فتوگرافیک'),
(14, 12, 'حسابداری');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `Co_ID` int(11) NOT NULL,
  `Co_name` varchar(50) NOT NULL,
  `Co_teacherID` int(3) NOT NULL,
  `Co_classID` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `G_ID` int(11) NOT NULL,
  `G_num` varchar(2) NOT NULL,
  `G_studentID` int(5) NOT NULL,
  `G_courseID` int(5) NOT NULL,
  `G_type` int(1) NOT NULL,
  `G_date` varchar(30) NOT NULL,
  `G_term` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `Stu_ID` int(11) NOT NULL,
  `Stu_fullName` varchar(50) NOT NULL,
  `Stu_nationalCode` varchar(10) NOT NULL,
  `Stu_phone` varchar(11) NOT NULL,
  `Stu_fatherName` varchar(30) NOT NULL,
  `Stu_fatherPhone` varchar(11) NOT NULL,
  `Stu_classID` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`Stu_ID`, `Stu_fullName`, `Stu_nationalCode`, `Stu_phone`, `Stu_fatherName`, `Stu_fatherPhone`, `Stu_classID`) VALUES
(11, 'منیب رحیمی', '3810770000', '09186677745', '', '', 12),
(12, 'هنرجوی شماره دو', '1111111111', '09186677745', '', '', 11);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `T_ID` int(11) NOT NULL,
  `T_fullName` varchar(50) NOT NULL,
  `T_nationalCode` varchar(10) NOT NULL,
  `T_password` varchar(100) NOT NULL,
  `T_phone` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Ad_ID`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`A_ID`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`C_ID`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`Co_ID`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`G_ID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`Stu_ID`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`T_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `Ad_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `A_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `C_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `Co_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `G_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `Stu_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `T_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

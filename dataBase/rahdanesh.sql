-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2026 at 11:27 AM
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
(1, 'یوزر اول', '111', '111', '09186677745', '', '', 0),
(2, 'یوزر دوم', '1111', 'monib', '09187656765', '', '', 0),
(3, 'یوزر سوم', '11111', '11111', '09186677745', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `expiration_date` varchar(10) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `title`, `file_path`, `class_id`, `teacher_id`, `expiration_date`, `description`) VALUES
(4, 'تمرین 1', '../images/tamrin/1785926066_5211.png', 16, 4, '1405/05/11', 'توضح تمرین شماره 1'),
(5, 'عنوان تمرین دوم', 'none', 15, 4, '1405/05/28', 'در این بخش میتوان به ورت اختیاری تا 300 کاراکتر نوشت'),
(7, '11 شبکه', 'none', 15, 4, '1405/05/11', ''),
(9, 'جزوه', 'none', 17, 4, '1414/05/30', ''),
(10, 'عنوان جدیدترین خبر', '../images/tamrin/1785929925_8019.png', 16, 4, '1405/05/17', 'توضیحات خبر جدید'),
(11, 'f', 'none', 16, 4, '1405/05/14', 'f');

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

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`A_ID`, `A_studentID`, `A_date`, `A_courseID`, `A_type`, `A_state`) VALUES
(5, 17, '۱۴۰۵/۰۵/۰۸', 13, 0, 1),
(6, 14, '۱۴۰۵/۰۵/۰۸', 13, 0, 0),
(7, 17, '۱۴۰۵/۰۵/۰۷', 14, 0, 1),
(8, 14, '۱۴۰۵/۰۵/۰۷', 14, 0, 1),
(45, 17, '۱۴۰۵/۰۵/۰۹', 7, 0, 0),
(46, 14, '۱۴۰۵/۰۵/۰۹', 7, 0, 0),
(71, 17, '۱۴۰۵/۰۵/۰۹', 14, 0, 1),
(72, 14, '۱۴۰۵/۰۵/۰۹', 14, 0, 0),
(73, 17, '۱۴۰۵/۰۵/۱۶', 14, 0, 0),
(74, 14, '۱۴۰۵/۰۵/۱۶', 14, 0, 1),
(79, 14, '۱۴۰۵/۰۵/۱۴', 14, 0, 0),
(80, 14, '۱۴۰۵/۰۵/۱۱', 14, 0, 0),
(81, 17, '۱۴۰۵/۰۵/۱۵', 14, 0, 0),
(82, 14, '۱۴۰۵/۰۵/۱۵', 14, 0, 0),
(83, 14, '۱۴۰۵/۰۵/۲۰', 7, 0, 0),
(84, 17, '۱۴۰۵/۰۵/۲۰', 14, 0, 0),
(85, 14, '۱۴۰۵/۰۵/۲۰', 14, 0, 0),
(86, 17, '۱۴۰۵/۰۵/۲۷', 14, 0, 0),
(87, 14, '۱۴۰۵/۰۵/۲۷', 14, 0, 0),
(88, 14, '۱۴۰۵/۰۶/۰۱', 7, 0, 0),
(89, 14, '۱۴۰۵/۰۵/۳۱', 13, 0, 0),
(90, 14, '۱۴۰۵/۰۱/۲۳', 14, 0, 0),
(91, 18, '۱۴۰۵/۰۵/۱۳', 6, 2, 0),
(92, 14, '۱۴۰۵/۰۵/۱۵', 14, 1, 0),
(93, 14, '۱۴۰۵/۰۵/۱۵', 14, 2, 0),
(94, 18, '۱۴۰۵/۰۵/۱۳', 10, 1, 0),
(95, 17, '۱۴۰۵/۰۵/۰۸', 14, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `certificate`
--

CREATE TABLE `certificate` (
  `ID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT 1,
  `student_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `certificate`
--

INSERT INTO `certificate` (`ID`, `title`, `description`, `type`, `student_ID`) VALUES
(1, 'فوتبال دوره ای', 'برای قهرمانی در فوتباال می باشد .', 2, 14),
(2, 'fff', 'fff', 3, 18),
(3, 'fff', 'ffff', 3, 17);

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
(15, 11, 'شبکه و نرم افزار رایانه'),
(16, 12, 'شبکه و نرم افزار رایانه'),
(17, 10, 'شبکه و نرم افزار رایانه'),
(18, 12, 'حسابداری');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `Co_ID` int(11) NOT NULL,
  `Co_name` varchar(50) NOT NULL,
  `Co_num` int(2) NOT NULL,
  `Co_type` int(1) NOT NULL,
  `Co_teacherID` int(3) NOT NULL,
  `Co_classID` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`Co_ID`, `Co_name`, `Co_num`, `Co_type`, `Co_teacherID`, `Co_classID`) VALUES
(6, 'توسعه برنامه سازی و پایگاه داده', 6, 0, 4, 15),
(7, 'ریاضی 3', 4, 0, 5, 16),
(9, 'فارسی 1', 1, 1, 4, 17),
(10, 'ریاضی 2', 5, 1, 4, 15),
(11, 'پیاده سازی', 8, 0, 4, 15),
(12, 'عربی 1', 3, 1, 4, 17),
(14, 'تجارت الکترونیک', 8, 0, 4, 16),
(15, 'کاربرد فناوری نوین', 6, 0, 4, 15),
(16, 'شیمی', 3, 0, 4, 15),
(17, 'فیزیک', 5, 0, 4, 17),
(18, 'فارسی 3', 3, 1, 5, 16),
(21, 'فارسی 3', 3, 1, 4, 18);

-- --------------------------------------------------------

--
-- Table structure for table `disciplinary_records`
--

CREATE TABLE `disciplinary_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `incident_date` varchar(10) NOT NULL,
  `incident_time` time NOT NULL,
  `description` varchar(400) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `disciplinary_records`
--

INSERT INTO `disciplinary_records` (`id`, `student_id`, `title`, `incident_date`, `incident_time`, `description`, `created_at`) VALUES
(1, 14, 'تاخیر در حضور', '۱۴۰۵/۰۵/۱۰', '13:43:00', 'امروز دانش اموز منیب رحیمی در کلاس حضور نداشته و غیبت نموده است و 2 نمره از انضباط او کسر گردیده است', '2026-08-01 11:43:59'),
(4, 14, 'بی نظمی در هنرستان', '۱۴۰۵/۰۵/۱۰', '14:05:00', 'بی نظمی در محیط کارگاهی', '2026-08-01 12:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_albums`
--

CREATE TABLE `gallery_albums` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `gallery_albums`
--

INSERT INTO `gallery_albums` (`id`, `title`, `created_at`) VALUES
(5, 'تصویر جدید از اردو بهاری هنرجویان', '۱۴۰۵/۰۵/۱۳');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `album_id`, `image_path`) VALUES
(10, 5, 'images/uploads/1785862871_Screenshot 2026-07-19 161356.png'),
(11, 5, 'images/uploads/1785862871_Screenshot 2026-07-15 135733.png');

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

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`G_ID`, `G_num`, `G_studentID`, `G_courseID`, `G_type`, `G_date`, `G_term`) VALUES
(6, '16', 17, 14, 0, '', 5),
(7, '0', 14, 14, 0, '', 5),
(8, '19', 17, 13, 1, '', 2),
(9, '14', 14, 13, 1, '', 2),
(10, '13', 17, 14, 0, '', 2),
(11, '16', 14, 14, 0, '', 2),
(12, '15', 17, 14, 0, '', 3),
(13, '1', 14, 14, 0, '', 3),
(14, '18', 17, 13, 1, '', 3),
(15, '17', 14, 13, 1, '', 3),
(16, '16', 17, 13, 1, '', 6),
(17, '13', 14, 13, 1, '', 6),
(18, '13', 17, 14, 0, '', 6),
(19, '15', 14, 14, 0, '', 6),
(20, '14', 17, 13, 1, '', 5),
(21, '17', 14, 13, 1, '', 5),
(22, '17', 17, 14, 0, '', 1),
(23, '15', 14, 14, 0, '', 1),
(24, '15', 17, 14, 0, '', 4),
(25, '11', 14, 14, 0, '', 4),
(26, '14', 17, 18, 0, '', 1),
(27, '16', 14, 18, 0, '', 1),
(28, '19', 17, 18, 0, '', 2),
(29, '20', 14, 18, 0, '', 2),
(30, '5', 17, 18, 0, '', 3),
(31, '13', 14, 18, 0, '', 3),
(32, '19', 17, 18, 0, '', 4),
(33, '18', 14, 18, 0, '', 4),
(34, '6.', 17, 18, 0, '', 5),
(35, '13', 14, 18, 0, '', 5),
(36, '9.', 17, 18, 0, '', 6),
(37, '10', 14, 18, 0, '', 6),
(38, '13', 17, 7, 0, '', 1),
(39, '16', 14, 7, 0, '', 1),
(40, '19', 17, 7, 0, '', 2),
(41, '19', 14, 7, 0, '', 2),
(42, '8.', 17, 7, 0, '', 4),
(43, '16', 14, 7, 0, '', 4),
(44, '17', 17, 7, 0, '', 5),
(45, '17', 14, 7, 0, '', 5),
(46, '16', 17, 7, 0, '', 6),
(47, '14', 14, 7, 0, '', 6),
(48, '16', 18, 11, 0, '', 6),
(49, '6', 18, 6, 0, '', 1),
(50, '11', 18, 10, 1, '', 1),
(51, '6', 18, 6, 0, '', 6);

-- --------------------------------------------------------

--
-- Table structure for table `live_messages`
--

CREATE TABLE `live_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) NOT NULL,
  `course_id` bigint(20) NOT NULL,
  `sender_type` varchar(20) NOT NULL,
  `sender_id` bigint(20) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_messages`
--

INSERT INTO `live_messages` (`id`, `room_id`, `course_id`, `sender_type`, `sender_id`, `message`, `created_at`) VALUES
(1, 9, 11, 'teacher', 4, 'ggg', '2026-08-12 20:04:04'),
(2, 9, 11, 'teacher', 4, 'f\\', '2026-08-12 20:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` int(11) NOT NULL,
  `sender_type` enum('student','teacher') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reply_to_id` bigint(20) UNSIGNED DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `course_id`, `sender_type`, `sender_id`, `message`, `created_at`, `reply_to_id`, `edited_at`) VALUES
(1, 12, 'teacher', 4, 'سلام', '2026-08-17 10:15:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `message_audios`
--

CREATE TABLE `message_audios` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `sender_type` enum('student','teacher') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `audio_path` varchar(500) NOT NULL,
  `audio_name` varchar(255) NOT NULL,
  `duration` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_files`
--

CREATE TABLE `message_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` int(11) NOT NULL,
  `sender_type` enum('student','teacher') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT 0,
  `file_type` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `category`, `content`, `image_path`, `created_at`) VALUES
(4, 'ثبتنام کنکور', 'آموزشی', 'امکان ثبتنام در کنکور 1405/1405 در مرداد امسال با مراجعه به وبسایت آموزشی my.medu.ir', 'images/news/1785862432_Screenshot 2026-07-15 135733.png', '۱۴۰۵/۰۵/۱۴'),
(5, 'ثبتنام کنکور', 'آموزشی', 'امکان ثبتنام در کنکور 1405/1405 در مرداد امسال با مراجعه به وبسایت آموزشی my.medu.ir', 'images/news/1785862653_Screenshot 2026-07-15 135733.png', '۱۴۰۵/۰۵/۱۴'),
(6, 'ثبتنام کنکور', 'آموزشی', 'امکان ثبتنام در کنکور 1405/1405 در مرداد امسال با مراجعه به وبسایت آموزشی my.medu.ir', 'images/news/1785862696_Screenshot 2026-07-15 135733.png', '۱۴۰۵/۰۵/۱۴');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `title`, `file_path`, `class_id`, `teacher_id`) VALUES
(5, 'ijh', '../images/notes/1786049332_5521.png', 16, 4),
(6, 'تمرینات پودمان 1 ریاضی', '../images/notes/1786344166_1537.png', 16, 4),
(7, 'تمرینات پودمان 3 ریاضی', '../images/notes/1786344182_1271.png', 16, 4),
(8, 'تمرینات پودمان 1 و 2 تجارت الکترونیک', '../images/notes/1786344212_5162.png', 16, 4),
(9, 'جزوه سوالات نهایی سلامت و بهداشت', '../images/notes/1786344236_2888.png', 16, 4),
(10, 'جواب فعالیت های عربی 3', '../images/notes/1786344258_6958.png', 16, 4);

-- --------------------------------------------------------

--
-- Table structure for table `report_license`
--

CREATE TABLE `report_license` (
  `ID` int(11) NOT NULL,
  `term` int(2) DEFAULT NULL,
  `publish` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `report_license`
--

INSERT INTO `report_license` (`ID`, `term`, `publish`) VALUES
(4, 4, 1),
(5, 3, 1);

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
(14, 'منیب رحیمی', '3810770000', '09186677745', 'عبدالرحیم', '09186677745', 16),
(17, 'متین کریمی', '1113334445', '09111111111', 'تست', '', 16),
(18, 'مبین رحیمی', '1111111111', '0918667774', '', '', 15);

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
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`T_ID`, `T_fullName`, `T_nationalCode`, `T_password`, `T_phone`) VALUES
(4, 'منیب رحیمی', '1', '1', '09186677745'),
(5, 'هنرآموز 1', '1333333333', '123456', '09187990000');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_attendance`
--

CREATE TABLE `teacher_attendance` (
  `AT_ID` int(11) NOT NULL,
  `AT_studentID` int(11) NOT NULL,
  `AT_courseID` int(11) NOT NULL,
  `AT_teacherID` int(11) NOT NULL,
  `AT_date` varchar(10) NOT NULL,
  `AT_type` int(11) NOT NULL,
  `AT_state` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `teacher_attendance`
--

INSERT INTO `teacher_attendance` (`AT_ID`, `AT_studentID`, `AT_courseID`, `AT_teacherID`, `AT_date`, `AT_type`, `AT_state`) VALUES
(10, 18, 10, 4, '۱۴۰۵/۰۵/۰۱', 1, 0),
(11, 18, 6, 4, '۱۴۰۵/۰۵/۰۸', 1, 0),
(12, 18, 6, 4, '۱۴۰۵/۰۵/۰۸', 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_disciplinary`
--

CREATE TABLE `teacher_disciplinary` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `incident_date` varchar(10) NOT NULL,
  `incident_time` varchar(8) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_disciplinary`
--

INSERT INTO `teacher_disciplinary` (`id`, `student_id`, `title`, `incident_date`, `incident_time`, `description`, `course_id`, `teacher_id`, `created_at`, `is_read`) VALUES
(4, 18, 'ervreverver', '1405/05/13', '20:43', 'rwfverbvetrver', 6, 4, '2026-08-07 20:43:50', 1);

-- --------------------------------------------------------

--
-- Table structure for table `voice_participants`
--

CREATE TABLE `voice_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('student','teacher') NOT NULL,
  `user_id` int(11) NOT NULL,
  `mic_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `joined_at` datetime NOT NULL DEFAULT current_timestamp(),
  `left_at` datetime DEFAULT NULL,
  `last_seen` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voice_participants`
--

INSERT INTO `voice_participants` (`id`, `room_id`, `user_type`, `user_id`, `mic_enabled`, `joined_at`, `left_at`, `last_seen`) VALUES
(1, 1, 'teacher', 4, 1, '2026-08-17 10:14:46', '2026-08-17 10:14:57', '2026-08-17 10:14:57'),
(2, 2, 'teacher', 4, 0, '2026-08-17 10:15:57', '2026-08-17 10:16:11', '2026-08-17 10:16:11'),
(3, 3, 'teacher', 4, 0, '2026-08-18 12:55:05', '2026-08-18 12:55:19', '2026-08-18 12:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `voice_rooms`
--

CREATE TABLE `voice_rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `status` enum('active','ended') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voice_rooms`
--

INSERT INTO `voice_rooms` (`id`, `course_id`, `teacher_id`, `status`, `created_at`, `ended_at`) VALUES
(1, 14, 4, 'active', '2026-08-17 10:14:45', NULL),
(2, 12, 4, 'ended', '2026-08-17 10:15:56', '2026-08-17 10:16:11'),
(3, 11, 4, 'ended', '2026-08-18 12:55:04', '2026-08-18 12:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `voice_signals`
--

CREATE TABLE `voice_signals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `sender_type` enum('student','teacher') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_type` enum('student','teacher') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `signal_type` enum('offer','answer','ice') NOT NULL,
  `signal_data` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Ad_ID`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`A_ID`);

--
-- Indexes for table `certificate`
--
ALTER TABLE `certificate`
  ADD PRIMARY KEY (`ID`);

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
-- Indexes for table `disciplinary_records`
--
ALTER TABLE `disciplinary_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_albums`
--
ALTER TABLE `gallery_albums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `album_id` (`album_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`G_ID`);

--
-- Indexes for table `live_messages`
--
ALTER TABLE `live_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_id` (`room_id`),
  ADD KEY `idx_course_room` (`course_id`,`room_id`),
  ADD KEY `idx_room_id_id` (`room_id`,`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_id_id` (`course_id`,`id`),
  ADD KEY `idx_reply_to_id` (`reply_to_id`);

--
-- Indexes for table `message_audios`
--
ALTER TABLE `message_audios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `message_files`
--
ALTER TABLE `message_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_sender_id` (`sender_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`);

--
-- Indexes for table `report_license`
--
ALTER TABLE `report_license`
  ADD PRIMARY KEY (`ID`);

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
-- Indexes for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD PRIMARY KEY (`AT_ID`),
  ADD KEY `idx_student` (`AT_studentID`),
  ADD KEY `idx_course` (`AT_courseID`),
  ADD KEY `idx_teacher` (`AT_teacherID`);

--
-- Indexes for table `teacher_disciplinary`
--
ALTER TABLE `teacher_disciplinary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `voice_participants`
--
ALTER TABLE `voice_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_voice_participant` (`room_id`,`user_type`,`user_id`),
  ADD KEY `idx_voice_participants_room` (`room_id`),
  ADD KEY `idx_voice_participants_user` (`user_type`,`user_id`),
  ADD KEY `idx_voice_participants_online` (`room_id`,`left_at`,`last_seen`);

--
-- Indexes for table `voice_rooms`
--
ALTER TABLE `voice_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_voice_rooms_course` (`course_id`),
  ADD KEY `idx_voice_rooms_teacher` (`teacher_id`),
  ADD KEY `idx_voice_rooms_status` (`status`),
  ADD KEY `idx_voice_rooms_course_status` (`course_id`,`status`);

--
-- Indexes for table `voice_signals`
--
ALTER TABLE `voice_signals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_voice_signals_receiver` (`room_id`,`receiver_type`,`receiver_id`,`id`),
  ADD KEY `idx_voice_signals_room` (`room_id`,`id`),
  ADD KEY `idx_voice_signals_sender` (`room_id`,`sender_type`,`sender_id`,`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `Ad_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `A_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `certificate`
--
ALTER TABLE `certificate`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `C_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `Co_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `disciplinary_records`
--
ALTER TABLE `disciplinary_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gallery_albums`
--
ALTER TABLE `gallery_albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `G_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `live_messages`
--
ALTER TABLE `live_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `message_audios`
--
ALTER TABLE `message_audios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_files`
--
ALTER TABLE `message_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `report_license`
--
ALTER TABLE `report_license`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `Stu_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `T_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `AT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `teacher_disciplinary`
--
ALTER TABLE `teacher_disciplinary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `voice_participants`
--
ALTER TABLE `voice_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `voice_rooms`
--
ALTER TABLE `voice_rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `voice_signals`
--
ALTER TABLE `voice_signals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `gallery_albums` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_disciplinary`
--
ALTER TABLE `teacher_disciplinary`
  ADD CONSTRAINT `teacher_disciplinary_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`Stu_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

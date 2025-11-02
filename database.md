-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 27, 2025 at 05:55 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u337253893_PLPasigSSO`
--

-- --------------------------------------------------------

--
-- Table structure for table `acc_locking`
--

CREATE TABLE `acc_locking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `attempt_left` int(11) DEFAULT 5,
  `last_attempt_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_locking`
--

INSERT INTO `acc_locking` (`id`, `user_id`, `attempt_left`, `last_attempt_at`) VALUES
(1, 1, 5, '2025-10-17 16:10:01');

-- --------------------------------------------------------

--
-- Table structure for table `admission_cycles`
--

CREATE TABLE `admission_cycles` (
  `id` int(11) NOT NULL,
  `cycle_name` varchar(255) NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_cycles`
--

INSERT INTO `admission_cycles` (`id`, `cycle_name`, `is_archived`) VALUES
(1, '2026-2027 Admissions', 0),
(2, '2027-2028 Admissions	', 1),
(3, '2028-2029 Admissions	', 1),
(4, '2029-2030 Admissions', 1),
(5, '2030-2031 Admissions', 1),
(6, '2026-2027 Admissions', 0);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_types`
--

CREATE TABLE `applicant_types` (
  `id` int(11) NOT NULL,
  `admission_cycle_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `banner_image_path` varchar(512) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicant_types`
--

INSERT INTO `applicant_types` (`id`, `admission_cycle_id`, `name`, `banner_image_path`, `is_archived`, `is_active`) VALUES
(1, 1, 'Transferee', NULL, 1, 1),
(2, 1, 'SHS Graduate', NULL, 1, 1),
(3, 2, 'SHS Graduate', NULL, 1, 1),
(4, 1, 'SHS Graduates', 'uploads/banners/68fe66afcc90e_Dashboard_Empty_State.png', 0, 1),
(5, 1, 'SHS Graduates', 'uploads/banners/68fe841716fc0_Dashboard_-_Dashboard.png', 0, 1),
(6, 1, 'SHS Graduate', 'uploads/banners/68fe6709de3f6_copy.png', 0, 1),
(7, 6, 'SHS Graduate', 'uploads/banners/68ff8d3710e93_Dashboard_-_Dashboard.png', 0, 0),
(8, 6, 'SHS Graduate', 'uploads/banners/68ff919d57818_Dashboard_-_Dashboard.png', 0, 0),
(9, 6, 'SHS Graduate', 'uploads/banners/68ff91ffb65f7_Dashboard_-_Dashboard.png', 0, 0),
(10, 6, 'SHS Graduate', 'uploads/banners/68ff92c8b0e06_Dashboard_-_Dashboard.png', 0, 0),
(11, 6, 'Romeo John Ador', 'uploads/banners/68ff938f149f6_Dashboard_-_Dashboard.png', 1, 0),
(12, 6, 'sadadas', 'uploads/banners/68ff94038d537_Dashboard_-_Dashboard.png', 0, 0),
(13, 6, 'SHS Graduate', 'uploads/banners/68ff94b9135a1_Dashboard_-_Dashboard.png', 0, 0),
(14, 6, 'sadadas (Copy)', 'uploads/banners/68ff979c7943a_copy.png', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `email_template`
--

CREATE TABLE `email_template` (
  `id` int(50) NOT NULL,
  `title` varchar(250) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `html_code` longtext NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_template`
--

INSERT INTO `email_template` (`id`, `title`, `subject`, `html_code`, `date_added`, `is_active`) VALUES
(1, 'Account Registration', 'PLP - Student Success Office [Account Registration]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>{{greetings}},</p>\n            <p>Thank you for registering with the <strong>Pamantasan ng Lungsod ng Pasig - Student Success Office (SSO)</strong>. To complete your verification process, please click the button below:</p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"{{verification_link}}\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    Verify Account\n                </a>\n            </p>\n\n            <p>This link will remain valid until <strong>{{expire_at}}</strong>. If the link expires, you will need to request a new verification email through our system.</p>\n\n            <div class=\"notice\">\n                If you did not initiate this password reset request, you can safely ignore this email. For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 20px;\">Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-03 10:29:28', 1),
(2, 'Login Account With OTP', 'PLP - Student Success Office [Login Account With OTP]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>{{greetings}},</p>\n            <p>Thank you for logging in to the <strong>Pamantasan ng Lungsod ng Pasig - Student Success Office (SSO)</strong>. To continue, please use the One-Time Password (OTP) provided below:</p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    {{otp_code}}\n                </a>\n            </p>\n\n            <p>This code will remain valid until <strong>{{expire_at}}</strong>. If the link expires, you will need to request a new verification email through our system.</p>\n\n            <div class=\"notice\">\n                If you did not initiate this password reset request, you can safely ignore this email. For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 20px;\">Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-06 19:52:52', 1),
(3, 'Reset Password With Link', 'PLP - Student Success Office [Reset Password]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>{{greetings}},</p>\n            <p>We received a request to reset the password for your <strong>Pamantasan ng Lungsod ng Pasig - Student Success Office (SSO)</strong> account. To proceed, please click the button below to reset your password:</p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"{{reset_link}}\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    Reset Password\n                </a>\n            </p>\n\n            <p>This link will remain valid until <strong>{{expire_at}}</strong>. If it expires, simply request a new password reset link through the <strong>Forgot Password</strong> page.</p>\n\n            <div class=\"notice\">\n                If you did not initiate this password reset request, you can safely ignore this email. For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 20px;\">Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-08 20:54:58', 1),
(4, 'Account Reactivation', 'PLP - Student Success Office [Reactivate Account]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>{{greetings}},</p>\n            <p>We noticed that your account with the <strong>Pamantasan ng Lungsod ng Pasig - Student Success Office (SSO)</strong> has been deactivated. To reactivate your account and regain access, please click the button below:</p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"{{activation_link}}\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    Reactivate Account\n                </a>\n            </p>\n\n            <p>This link will remain valid until <strong>{{expire_at}}</strong></p>\n\n            <div class=\"notice\">\n                If you did not request this reactivation, please ignore this email, you can safely ignore this email. For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 20px;\">Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-15 10:42:26', 1);

-- --------------------------------------------------------

--
-- Table structure for table `expiration_config`
--

CREATE TABLE `expiration_config` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `interval_value` int(11) DEFAULT NULL,
  `interval_unit` enum('MINUTE','HOUR','DAY','MONTH') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expiration_config`
--

INSERT INTO `expiration_config` (`id`, `type`, `interval_value`, `interval_unit`) VALUES
(1, 'activation_account', 1, 'HOUR'),
(2, 'password_reset', 1, 'MONTH'),
(3, 'login_otp', 1, 'MINUTE'),
(4, 'session', 7, 'DAY');

-- --------------------------------------------------------

--
-- Table structure for table `form_fields`
--

CREATE TABLE `form_fields` (
  `id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `label` varchar(255) NOT NULL,
  `input_type` varchar(50) NOT NULL,
  `placeholder_text` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `field_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_fields`
--

INSERT INTO `form_fields` (`id`, `step_id`, `name`, `label`, `input_type`, `placeholder_text`, `is_archived`, `is_required`, `field_order`) VALUES
(1, 1, 'first_name', 'First Name', 'text', '', 0, 1, 1),
(2, 1, 'first_name', 'Last Name', 'text', 'e.g., Jane', 0, 1, 2),
(3, 1, 'valid_id', 'Valid ID', 'file', 'Government Issued Valid ID', 0, 1, 3),
(4, 2, 'first_name', 'First Name', 'text', '', 0, 1, 1),
(5, 2, 'first_name', 'Last Name', 'text', 'e.g., Jane', 0, 1, 2),
(6, 2, 'valid_id', 'Valid ID', 'file', 'Government Issued Valid ID', 0, 1, 3),
(7, 3, 'last_name', 'Last Name', 'text', 'Last Name', 0, 1, 1),
(9, 5, 'asdasdasdasdsadasd', 'adasd', 'text', '', 0, 1, 1),
(10, 7, 'sadasdasdasdasdas', 'asdsadasd', 'email', 'dasdasdas', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `form_field_options`
--

CREATE TABLE `form_field_options` (
  `id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `option_label` varchar(255) NOT NULL,
  `option_value` varchar(255) NOT NULL,
  `option_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_steps`
--

CREATE TABLE `form_steps` (
  `id` int(11) NOT NULL,
  `applicant_type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `step_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_steps`
--

INSERT INTO `form_steps` (`id`, `applicant_type_id`, `title`, `is_archived`, `step_order`) VALUES
(1, 1, 'Step 1: Personal Information', 0, 1),
(2, 2, 'Step 1: Personal Information', 0, 1),
(3, 3, 'Step 1', 0, 1),
(5, 14, 'sadasdas', 0, 1),
(7, 12, 'adasdasdas', 0, 3),
(8, 12, 'asdasdas', 0, 4),
(9, 12, '', 0, 5),
(10, 12, '', 0, 6);

-- --------------------------------------------------------

--
-- Table structure for table `msg_config`
--

CREATE TABLE `msg_config` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `msg_config`
--

INSERT INTO `msg_config` (`id`, `name`, `value`, `created_at`, `is_active`) VALUES
(1, 'REQUEST_METHOD_POST', 'To interact with this endpoint, be sure to send a POST request — other methods aren’t supported.', '2025-10-14 17:33:41', 1),
(2, 'EMPTY_EMAIL_PASS', 'To continue, please make sure you’ve entered both your email address and password.', '2025-10-14 18:05:54', 1),
(3, 'INVALID_EMAIL', 'Hmm... that doesn\'t look like a valid email address.', '2025-10-14 18:09:54', 1),
(4, 'ALREADY_REGISTERED', 'Looks like this email is already registered. Try logging in instead.', '2025-10-14 18:11:42', 1),
(5, 'REGISTER_SUCCESS', 'Your account has been created successfully! Please check your email for a verification link.', '2025-10-14 18:28:55', 1),
(6, 'INVALID_VERIFY_LINK', 'Oops — it looks like your verification link isn’t valid anymore. Try requesting a new link to verify your account.', '2025-10-14 20:12:58', 1),
(7, 'EXPIRED_VERIFY_LINK', 'This verification link has expired for security reasons. Please request a new link to verify your account.', '2025-10-14 20:19:53', 1),
(8, 'ALREADY_VERIFIED', 'It looks like your account has already been verified. If you’re having trouble signing in, try resetting your password or contacting support.', '2025-10-14 20:21:38', 1),
(9, 'USER_NOT_FOUND', 'We couldn’t find an account with this verification link. Please check your information and try again.', '2025-10-14 20:23:00', 1),
(10, 'NOT_REGISTER', 'We couldn’t find an account with that email address. Please check and try again.', '2025-10-14 20:31:15', 1),
(11, 'NOT_VERIFIED_ACCOUNT', 'Your account hasn’t been verified yet. Please check your inbox (and spam folder) for the verification link.', '2025-10-14 20:35:36', 1),
(12, 'ACCOUNT_BANNED_DELETED', 'It looks like your account has been deactivated or suspended. If this is unexpected, please contact our support team to restore access.', '2025-10-14 21:00:37', 1),
(13, 'INVALID_PASSWORD', 'Login unsuccessful — the password entered is incorrect. You can try again or reset your password to regain access.', '2025-10-15 07:50:24', 1),
(14, 'LOGIN_SUCCESS', 'Login successful! Your account is now active and ready to use.', '2025-10-15 07:52:58', 1),
(15, 'VERIFICATION_ACCOUNT_LINK_SUCCESS', 'A new verification link has been sent to your email address. Please check your inbox to continue.', '2025-10-15 09:20:56', 1),
(16, 'VERIFICATION_ACCOUNT_LINK_FAILED', 'We couldn’t send the verification link due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support.', '2025-10-15 09:21:33', 1),
(17, 'REACTIVATION_ACCOUNT_SUCCESS', 'A reactivation link has been sent to your email. Please check your inbox to reactivate your account.', '2025-10-15 10:44:25', 1),
(18, 'REACTIVATION_ACCOUNT_FAILED', 'Something went wrong while activation your account. Please try again.', '2025-10-15 10:45:31', 1),
(19, 'RESET_PASSWORD_SEND_SUCCESS', 'A password reset link has been sent to your email. Please check your inbox and reset your password within 24 hours to complete the process.', '2025-10-15 13:20:45', 1),
(20, 'RESET_PASSWORD_SEND_FAILED', 'We couldn’t send the password reset link due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support.', '2025-10-15 13:21:03', 1),
(21, 'RESET_PASSWORD_LINK_INVALID', 'We couldn’t find an account with this reset password link. Please check your reset password link and try again.', '2025-10-15 14:23:48', 1),
(22, 'RESET_PASSWORD_LINK_EXPIRED', 'This reset password link has expired for security reasons. Please request a new link to verify your account.', '2025-10-15 14:24:32', 1),
(23, 'CHANGE_PASSWORD_SUCCESS', 'Your password has been updated successfully! You can now log in with your new password.', '2025-10-15 14:59:00', 1),
(24, 'CHANGE_PASSWORD_FAILED', 'Hmm, we couldn\'t update your password. Please try again in a few moments.', '2025-10-15 14:59:49', 1),
(25, 'OTP_SEND_FAILED', 'We couldn’t send the OTP CODE due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support.', '2025-10-15 20:59:11', 1),
(26, 'OTP_SEND_SUCCESS', 'We’ve sent a 6-digit code to your registered email. Please check your inbox and enter the code to continue.', '2025-10-15 21:00:05', 1),
(27, 'INVALID_OTP', 'Hmm... that doesn\'t look like a valid OTP.', '2025-10-17 10:09:11', 1),
(28, 'OTP_ALREADY_USED', 'This verification code has already been used. Please request a new code by clicking <b>Resend OTP Code</b>.', '2025-10-17 11:11:56', 1),
(29, 'OTP_ALREADY_USED', 'This verification code has already been used. Please request a new code by clicking <b>Resend OTP Code</b>.', '2025-10-17 11:12:58', 1),
(30, 'OTP_EXPIRED', 'Your verification code has expired. Please request a new code by clicking <b>Resend OTP Code</b> and use the updated code sent to your email to proceed.', '2025-10-17 11:12:58', 1),
(31, 'OTP_VERIFICATION_SUCCESS', 'Verification successful! You can now proceed with your login.', '2025-10-17 11:12:58', 1),
(32, 'OTP_UPDATE_ERROR', 'An error occurred while processing your request. Please try again.', '2025-10-17 11:12:58', 1),
(33, 'OTP_INVALID_CODE', 'It looks like the code you entered is incorrect. Please double-check the code sent to your email and try again.<br><br>Click <b>Try Again</b> to re-enter your code, or click <b>Resend OTP Code</b> to request a new one.', '2025-10-17 11:12:58', 1),
(34, 'OTP_NOT_FOUND', 'No verification code found for this account. Please request a new code by clicking <b>Resend OTP Code</b>.', '2025-10-17 11:12:58', 1),
(35, 'LOGIN_ERROR', 'We couldn’t login your account due to a system issue. Please double-check your information and try again later. If the issue persists, contact support.', '2025-10-15 07:52:58', 1),
(36, 'SESSION_EXPIRED', 'Your session has expired or you’re not logged in. Please sign in to regain access.', '2025-10-17 20:15:16', 1),
(37, 'UNAUTHENTICATED', 'Authentication required. Please log in to access this resource.', '2025-10-17 20:20:29', 1),
(38, 'INVALID_FIRST_NAME', 'To continue, please make sure you’ve entered a valid first name.', '2025-10-19 20:51:46', 1),
(39, 'INVALID_LAST_NAME', 'To continue, please make sure you’ve entered a valid last name.', '2025-10-19 20:52:03', 1),
(40, 'SET_PROFILE_FAILED', 'We couldn’t update your profile at this time. Please check your input and try again. If the issue persists, contact support for help.', '2025-10-19 21:17:55', 1),
(41, 'SET_PROFILE_SUCCESS', 'Your profile has been set up successfully.', '2025-10-19 21:18:04', 1),
(42, 'INVALID_APPLICATION_TYPE', 'The provided application type is invalid or not supported.', '2025-10-21 11:36:08', 1);

-- --------------------------------------------------------

--
-- Table structure for table `otp_user`
--

CREATE TABLE `otp_user` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `value` text NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_user`
--

INSERT INTO `otp_user` (`id`, `user_id`, `value`, `is_used`, `expires_at`, `created_at`) VALUES
(1, 1, '148233', 0, '2025-10-17 19:01:42', '2025-10-17 18:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `remark_templates`
--

CREATE TABLE `remark_templates` (
  `id` int(11) NOT NULL,
  `remark_text` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remark_templates`
--

INSERT INTO `remark_templates` (`id`, `remark_text`, `is_active`) VALUES
(1, 'Missing required document: [Document Name]', 1),
(2, 'Information provided is unclear. Please clarify: [Specific Detail]', 1),
(3, 'Application approved pending final review.', 1),
(4, 'Application does not meet minimum requirements.', 1),
(5, 'Duplicate submission detected.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sso_user`
--

CREATE TABLE `sso_user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(250) NOT NULL,
  `middle_name` varchar(250) DEFAULT NULL,
  `last_name` varchar(250) NOT NULL,
  `suffix` varchar(250) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sso_user`
--

INSERT INTO `sso_user` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `password`, `status`, `created_at`) VALUES
(1, 'ROMEO JOHN', NULL, 'ADOR', NULL, 'testuser@example.com', 'some_password_hash', 'active', '2025-10-23 19:57:01'),
(2, 'ROMEO JOHN', NULL, 'ADOR', NULL, 'admin@sso.edu', '$2y$10$0RtgqYinB4p5bHMB7dKgVOipANdaYevQbrX.OUDqIN6IPsvtVIJe2', 'active', '2025-10-26 21:33:22'),
(3, '', NULL, '', NULL, 'admin@sso.edu.ph', '$2y$10$gsh6vf8hlqBdU4T.Ao.Mpuv1xWcMDV/jGobuXwpSgpafqn5L8WL1u', 'active', '2025-10-27 07:43:37'),
(4, 'John', 'Michael', 'Doe', 'Jr.', 'admin@test.com', '$2y$10$BuOzHpV3B8Ran5YlHL7O4.PA23HydDsJSiMN1HUawyX2UCt.i2EwO', 'active', '2025-10-27 13:43:56');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'e.g., Pending, Accepted',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Control order in dropdowns'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`, `display_order`) VALUES
(1, 'Pending', 10),
(2, 'In Review', 20),
(3, 'Waitlisted', 30),
(4, 'Accepted', 40),
(5, 'Rejected', 50);

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `applicant_type_id` int(11) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `user_id`, `applicant_type_id`, `submitted_at`, `status`, `remarks`) VALUES
(1, 1, 1, '2025-10-24 06:26:47', 'In Review', ''),
(2, 1, 1, '2025-10-24 06:30:07', 'In Review', ''),
(3, 1, 1, '2025-10-24 06:32:12', 'In Review', ''),
(4, 1, 1, '2025-10-24 06:32:39', 'In Review', ''),
(5, 1, 1, '2025-10-24 06:34:13', 'In Review', ''),
(6, 1, 1, '2025-10-24 06:42:34', 'In Review', ''),
(7, 1, 1, '2025-10-24 15:47:16', 'In Review', ''),
(8, 1, 1, '2025-10-24 16:00:28', 'In Review', ''),
(9, 1, 2, '2025-10-25 01:00:51', 'In Review', ''),
(10, 1, 3, '2025-10-26 03:18:41', 'Pending', 'Information provided is unclear. Please clarify: [Specific Detail]'),
(11, 1, 2, '2025-10-26 07:46:23', 'In Review', '');

-- --------------------------------------------------------

--
-- Table structure for table `submission_data`
--

CREATE TABLE `submission_data` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission_data`
--

INSERT INTO `submission_data` (`id`, `submission_id`, `field_name`, `field_value`) VALUES
(1, 1, 'applicant_type_id', '1'),
(2, 1, 'first_name', 'Ador'),
(3, 3, 'applicant_type_id', '1'),
(4, 3, 'first_name', 'Ador'),
(5, 4, 'applicant_type_id', '1'),
(6, 4, 'first_name', 'Ador'),
(7, 5, 'applicant_type_id', '1'),
(8, 5, 'first_name', 'Romeo John'),
(9, 6, 'applicant_type_id', '1'),
(10, 6, 'first_name', 'sadasd'),
(11, 7, 'first_name', 'Romeo John'),
(12, 8, 'first_name', 'Romeo John'),
(13, 9, 'first_name', 'asdasdas'),
(14, 10, 'last_name', 'sadadasda'),
(15, 11, 'first_name', 'asdas');

-- --------------------------------------------------------

--
-- Table structure for table `submission_files`
--

CREATE TABLE `submission_files` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission_files`
--

INSERT INTO `submission_files` (`id`, `submission_id`, `field_name`, `original_filename`, `file_path`) VALUES
(1, 7, 'valid_id', 'Module 4 – Configuring Resource Access.pdf', 'uploads/68fb2f04e7f84_Module_4_____Configuring_Resource_Access.pdf'),
(2, 8, 'valid_id', 'Module 4 – Configuring Resource Access.pdf', 'uploads/68fb321c10e12_Module_4_____Configuring_Resource_Access.pdf'),
(3, 9, 'valid_id', 'dahsboard.js', 'uploads/68fbb0c32b8f0_dahsboard.js'),
(4, 11, 'valid_id', 'sample.html', 'uploads/68fd614fa6b53_sample.html');

-- --------------------------------------------------------

--
-- Table structure for table `tokenization`
--

CREATE TABLE `tokenization` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `value` text NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tokenization`
--

INSERT INTO `tokenization` (`id`, `user_id`, `name`, `value`, `is_used`, `expires_at`, `created_at`) VALUES
(1, 1, 'VERIFY_ACCOUNT', 'c995ffe3064f9defbaebceb941aafe4239318fc1ef26e90b866b1cfcfeac3803', 1, '2025-10-18 16:11:24', '2025-10-17 16:10:01'),
(2, 1, 'RESET_PASSWORD', '2070763c96b1b1a7ac478c0e47df97724163f3690f8785a41408b633d264b8f1', 0, '2025-11-17 18:48:00', '2025-10-17 18:40:04'),
(3, 1, 'SESSION', '6a348abcbe6c7ce34b7dceabe3997408caef52b04c27900c4811c9cee6dada63', 0, '2025-11-03 07:17:17', '2025-10-17 20:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('student','applicant','sso','admin') NOT NULL,
  `acc_type` enum('admission','main') NOT NULL,
  `acc_status` enum('not_verified','banned','deleted','active','deactivated','locked') DEFAULT 'not_verified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `acc_type`, `acc_status`, `created_at`, `updated_at`) VALUES
(1, 'Mc+Mcxig2rdxnixLZkgbFUZib24yTStwUThXRHNGcklrd0w1MklLUFJCL2IrVENzc0h6a2hodVVyM2M9', '$2y$10$qqkbJr8jYNemgO.L1poJAudVVatRG1J7AU7lFmGN/49VenmXC6EPm', 'applicant', 'admission', 'active', '2025-10-17 16:10:01', '2025-10-17 16:11:37');

-- --------------------------------------------------------

--
-- Table structure for table `user_fullname`
--

CREATE TABLE `user_fullname` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(250) NOT NULL,
  `middle_name` varchar(250) DEFAULT NULL,
  `last_name` varchar(250) NOT NULL,
  `suffix` varchar(250) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_fullname`
--

INSERT INTO `user_fullname` (`id`, `user_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `last_updated`) VALUES
(1, 1, 'Romeo John', '', 'Ador', '', '2025-10-19 21:58:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_locking`
--
ALTER TABLE `acc_locking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admission_cycles`
--
ALTER TABLE `admission_cycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `applicant_types`
--
ALTER TABLE `applicant_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admission_cycle_id` (`admission_cycle_id`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `email_template`
--
ALTER TABLE `email_template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expiration_config`
--
ALTER TABLE `expiration_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`);

--
-- Indexes for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `step_id` (`step_id`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `form_field_options`
--
ALTER TABLE `form_field_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `form_steps`
--
ALTER TABLE `form_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applicant_type_id` (`applicant_type_id`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `msg_config`
--
ALTER TABLE `msg_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_user`
--
ALTER TABLE `otp_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `remark_templates`
--
ALTER TABLE `remark_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sso_user`
--
ALTER TABLE `sso_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `applicant_type_id` (`applicant_type_id`);

--
-- Indexes for table `submission_data`
--
ALTER TABLE `submission_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `submission_files`
--
ALTER TABLE `submission_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `tokenization`
--
ALTER TABLE `tokenization`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_fullname`
--
ALTER TABLE `user_fullname`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_locking`
--
ALTER TABLE `acc_locking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admission_cycles`
--
ALTER TABLE `admission_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `applicant_types`
--
ALTER TABLE `applicant_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `email_template`
--
ALTER TABLE `email_template`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `expiration_config`
--
ALTER TABLE `expiration_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `form_fields`
--
ALTER TABLE `form_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `form_field_options`
--
ALTER TABLE `form_field_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form_steps`
--
ALTER TABLE `form_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `msg_config`
--
ALTER TABLE `msg_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `otp_user`
--
ALTER TABLE `otp_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `remark_templates`
--
ALTER TABLE `remark_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sso_user`
--
ALTER TABLE `sso_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `submission_data`
--
ALTER TABLE `submission_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `submission_files`
--
ALTER TABLE `submission_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tokenization`
--
ALTER TABLE `tokenization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_fullname`
--
ALTER TABLE `user_fullname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `acc_locking`
--
ALTER TABLE `acc_locking`
  ADD CONSTRAINT `acc_locking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applicant_types`
--
ALTER TABLE `applicant_types`
  ADD CONSTRAINT `applicant_types_ibfk_1` FOREIGN KEY (`admission_cycle_id`) REFERENCES `admission_cycles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD CONSTRAINT `form_fields_ibfk_1` FOREIGN KEY (`step_id`) REFERENCES `form_steps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `form_field_options`
--
ALTER TABLE `form_field_options`
  ADD CONSTRAINT `form_field_options_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `form_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `form_steps`
--
ALTER TABLE `form_steps`
  ADD CONSTRAINT `form_steps_ibfk_1` FOREIGN KEY (`applicant_type_id`) REFERENCES `applicant_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_user`
--
ALTER TABLE `otp_user`
  ADD CONSTRAINT `otp_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `sso_user` (`id`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`applicant_type_id`) REFERENCES `applicant_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_data`
--
ALTER TABLE `submission_data`
  ADD CONSTRAINT `submission_data_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_files`
--
ALTER TABLE `submission_files`
  ADD CONSTRAINT `submission_files_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tokenization`
--
ALTER TABLE `tokenization`
  ADD CONSTRAINT `tokenization_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

-- This SQL script creates an exam scheduling table.
-- It includes columns for department and course (from the image data)
-- and specific 'floor', 'room', and 'capacity' columns.

CREATE TABLE ExamSchedules (
    schedule_id INT PRIMARY KEY,
    floor VARCHAR(50),
    room VARCHAR(50),
    capacity INT,
    start_date_and_time TIMESTAMP NOT NULL,
    status ENUM('Open', 'Full') NOT NULL
);

-- Insert the sample data
-- I've added accurate, sample data for floor, room, and capacity.
-- 50 sample INSERT statements with 'Open' or 'Full' status
INSERT INTO ExamSchedules (schedule_id, floor, room, capacity, start_date_and_time, status) VALUES
(1, '1st Floor', 'Room 101', 50, '2025-11-10 08:00:00', 'Open'),
(2, '1st Floor', 'Room 102', 40, '2025-11-10 08:00:00', 'Open'),
(3, '1st Floor', 'Room 103', 45, '2025-11-10 08:30:00', 'Full'),
(4, '2nd Floor', 'Room 201', 60, '2025-11-10 08:30:00', 'Open'),
(5, '2nd Floor', 'Room 202', 50, '2025-11-10 09:00:00', 'Full'),
(6, '2nd Floor', 'Room 203', 55, '2025-11-10 09:00:00', 'Open'),
(7, '3rd Floor', 'Room 301', 30, '2025-11-10 09:30:00', 'Open'),
(8, '3rd Floor', 'Room 302', 35, '2025-11-10 09:30:00', 'Open'),
(9, '3rd Floor', 'Room 303', 40, '2025-11-10 10:00:00', 'Full'),
(10, '4th Floor', 'Room 401', 70, '2025-11-10 10:00:00', 'Open'),
(11, '4th Floor', 'Room 402', 65, '2025-11-10 10:30:00', 'Full'),
(12, '1st Floor', 'Room 101', 50, '2025-11-10 10:30:00', 'Open'),
(13, '1st Floor', 'Room 102', 40, '2025-11-10 11:00:00', 'Full'),
(14, '1st Floor', 'Room 103', 45, '2025-11-10 11:00:00', 'Open'),
(15, '2nd Floor', 'Room 201', 60, '2025-11-10 11:30:00', 'Open'),
(16, '2nd Floor', 'Room 202', 50, '2025-11-10 11:30:00', 'Full'),
(17, '2nd Floor', 'Room 203', 55, '2025-11-10 13:00:00', 'Open'),
(18, '3rd Floor', 'Room 301', 30, '2025-11-10 13:00:00', 'Full'),
(19, '3rd Floor', 'Room 302', 35, '2025-11-10 13:30:00', 'Open'),
(20, '3rd Floor', 'Room 303', 40, '2025-11-10 13:30:00', 'Open'),
(21, '4th Floor', 'Room 401', 70, '2025-11-10 14:00:00', 'Open'),
(22, '4th Floor', 'Room 402', 65, '2025-11-10 14:00:00', 'Full'),
(23, '1st Floor', 'Room 101', 50, '2025-11-10 14:30:00', 'Full'),
(24, '1st Floor', 'Room 102', 40, '2025-11-10 14:30:00', 'Open'),
(25, '1st Floor', 'Room 103', 45, '2025-11-10 15:00:00', 'Open'),
(26, '2nd Floor', 'Room 201', 60, '2025-11-10 15:00:00', 'Full'),
(27, '2nd Floor', 'Room 202', 50, '2025-11-10 15:30:00', 'Open'),
(28, '2nd Floor', 'Room 203', 55, '2025-11-10 15:30:00', 'Open'),
(29, '3rd Floor', 'Room 301', 30, '2025-11-10 16:00:00', 'Full'),
(30, '3rd Floor', 'Room 302', 35, '2025-11-10 16:00:00', 'Open'),
(31, '1st Floor', 'Room 104', 25, '2025-11-11 08:00:00', 'Full'),
(32, '1st Floor', 'Room 105', 30, '2025-11-11 08:00:00', 'Open'),
(33, '2nd Floor', 'Room 204', 40, '2025-11-11 08:30:00', 'Open'),
(34, '2nd Floor', 'Room 205', 35, '2025-11-11 08:30:00', 'Full'),
(35, '3rd Floor', 'Room 304', 50, '2025-11-11 09:00:00', 'Open'),
(36, '3rd Floor', 'Room 305', 45, '2025-11-11 09:00:00', 'Full'),
(37, '4th Floor', 'Room 403', 60, '2025-11-11 09:30:00', 'Open'),
(38, '4th Floor', 'Room 404', 55, '2025-11-11 09:30:00', 'Open'),
(39, 'Gymnasium', 'Section A', 150, '2025-11-11 10:00:00', 'Full'),
(40, 'Gymnasium', 'Section B', 150, '2025-11-11 10:00:00', 'Open'),
(41, 'Auditorium', 'Main Hall', 200, '2025-11-11 10:30:00', 'Open'),
(42, '1st Floor', 'Room 104', 25, '2025-11-11 10:30:00', 'Full'),
(43, '1st Floor', 'Room 105', 30, '2025-11-11 11:00:00', 'Open'),
(44, '2nd Floor', 'Room 204', 40, '2025-11-11 11:00:00', 'Full'),
(45, '2nd Floor', 'Room 205', 35, '2025-11-11 13:00:00', 'Open'),
(46, '3rd Floor', 'Room 304', 50, '2025-11-11 13:00:00', 'Full'),
(47, '3rd Floor', 'Room 305', 45, '2025-11-11 13:30:00', 'Open'),
(48, '4th Floor', 'Room 403', 60, '2025-11-11 13:30:00', 'Full'),
(49, '4th Floor', 'Room 404', 55, '2025-11-11 14:00:00', 'Open'),
(50, 'Gymnasium', 'Section A', 150, '2025-11-11 14:00:00', 'Full');

CREATE TABLE ExamRegistrations (
    registration_id INT PRIMARY KEY,
    applicant_num VARCHAR(250),
    user_id INT,
    schedule_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (schedule_id) REFERENCES ExamSchedules(schedule_id)
);

CREATE TABLE IF NOT EXISTS `smtp_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL, -- store plaintext or manage encryption externally
  `address` VARCHAR(255) NOT NULL,  -- SMTP server address (e.g., smtp.gmail.com)
  `name` VARCHAR(255) NOT NULL,     -- display/sender name
  `status` ENUM('active', 'inactive') DEFAULT 'inactive',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `general_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL COMMENT 'Title of the uploaded file',
  `file_url` varchar(1024) NOT NULL COMMENT 'Full URL to the uploaded file',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `date_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `requirements_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `file_url` varchar(1024) NOT NULL,
  `title` varchar(255) NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `date_upload` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_requirements_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE admission_submission (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    can_apply BOOLEAN DEFAULT TRUE,
    can_update BOOLEAN DEFAULT TRUE,
    submitted_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
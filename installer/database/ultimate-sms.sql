-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 27, 2017 at 01:52 PM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 7.0.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ultimate_sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2017_02_11_160113_Create_AppConfig_Table', 1),
(2, '2017_02_15_051702_Create_Admins_Table', 1),
(3, '2017_02_15_051715_Create_Clients_Table', 1),
(4, '2017_02_24_140141_Create_SMS_Gateways_Table', 1),
(5, '2017_02_24_145218_Create_Payment_Gateways_Table', 1),
(6, '2017_02_24_153927_Create_Email_Templates_Table', 1),
(7, '2017_02_26_060604_Create_Client_Groups_Table', 1),
(8, '2017_02_27_174402_Create_Ticket_Table', 1),
(9, '2017_02_27_174448_Create_Ticket_Replies_Table', 1),
(10, '2017_02_27_174529_Create_Support_Department_Table', 1),
(11, '2017_02_27_174612_Create_Ticket_Files_Table', 1),
(12, '2017_02_28_134400_Create_Administrator_Role_Table', 1),
(13, '2017_02_28_134742_Create_Administrator_Role_Permission_Table', 1),
(14, '2017_03_01_170716_Create_Invoices_Table', 1),
(15, '2017_03_01_170742_Create_Invoice_Items_Table', 1),
(16, '2017_03_08_160657_Create_SMS_Transaction_Table', 1),
(17, '2017_03_10_175534_Create_Int_Country_Codes', 1),
(18, '2017_03_11_164932_Create_SenderID_Management_table', 1),
(19, '2017_03_14_163320_Create_SMS_Plan_Feature', 1),
(20, '2017_03_14_163416_Create_SMS_Price_Plan_Table', 1),
(21, '2017_03_27_150018_create_jobs_table', 1),
(22, '2017_04_09_145036_Create_Custom_SMS_Gateways_Table', 1),
(23, '2017_04_11_163310_Create_SMS_History_Table', 1),
(24, '2017_04_12_052528_Create_SMS_Templates_Table', 1),
(25, '2017_04_14_140621_Create_Schedule_SMS_Table', 1),
(26, '2017_04_24_175153_Create_SMS_Inbox_Table', 1),
(27, '2017_05_06_054309_Create_Language_Table', 1),
(28, '2017_05_06_054719_Create_Language_Data_Table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sys_admins`
--

CREATE TABLE `sys_admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `fname` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lname` text COLLATE utf8mb4_unicode_ci,
  `username` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `email` text COLLATE utf8mb4_unicode_ci,
  `image` text COLLATE utf8mb4_unicode_ci,
  `roleid` int(11) NOT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `pwresetkey` text COLLATE utf8mb4_unicode_ci,
  `pwresetexpiry` int(11) DEFAULT NULL,
  `emailnotify` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `online` int(11) NOT NULL DEFAULT '0',
  `menu_open` int(11) NOT NULL DEFAULT '0',
  `remember_token` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sys_admins`
--

INSERT INTO `sys_admins` (`id`, `fname`, `lname`, `username`, `password`, `status`, `email`, `image`, `roleid`, `lastlogin`, `pwresetkey`, `pwresetexpiry`, `emailnotify`, `online`, `menu_open`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Abul Kashem', 'Shamim', 'admin', '$2y$10$lfzgHO7eKnEsw0Nz/5jr.eFojD2Ey6o9asi7pktBT.GFwMF3gWLxq', 'Active', 'akasham67@gmail.com', 'profile.jpg', 0, NULL, NULL, NULL, 'No', 0, 0, 'rcgTRfBWvWcE5ll8waAWVTpyQmvp9dlmUyr75rb5XPPfRgBQwnDxeemMnKK8', '2017-05-27 02:32:20', '2017-05-27 02:32:20');

-- --------------------------------------------------------

--
-- Table structure for table `sys_admin_role`
--

CREATE TABLE `sys_admin_role` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_admin_role_perm`
--

CREATE TABLE `sys_admin_role_perm` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(11) NOT NULL,
  `perm_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_app_config`
--

CREATE TABLE `sys_app_config` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sys_app_config`
--

INSERT INTO `sys_app_config` (`id`, `setting`, `value`, `created_at`, `updated_at`) VALUES
(1, 'AppName', 'Ultimate SMS', '2017-05-27 02:32:18', '2017-05-27 02:41:54'),
(2, 'AppUrl', 'ultimatesms.coderpixel.com', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(3, 'purchase_key', '', '2017-05-27 02:32:18', '2017-05-27 02:34:20'),
(4, 'valid_domain', 'yes', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(5, 'Email', 'akasham67@gmail.com', '2017-05-27 02:32:18', '2017-05-27 02:41:55'),
(6, 'Address', 'House#11, Block#B, <br>Rampura<br>Banasree Project<br>Dhaka<br>1219<br>Bangladesh', '2017-05-27 02:32:18', '2017-05-27 02:41:55'),
(7, 'SoftwareVersion', '1.0.0', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(8, 'AppTitle', 'Ultimate SMS - Bulk SMS Application For Marketing', '2017-05-27 02:32:18', '2017-05-27 02:41:55'),
(9, 'FooterTxt', 'Copyright © Abul Kashem Shamim - 2017', '2017-05-27 02:32:18', '2017-05-27 02:41:55'),
(10, 'AppLogo', 'assets/img/logo.png', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(11, 'AppFav', 'assets/img/favicon.ico', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(12, 'Country', 'Bangladesh', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(13, 'Timezone', 'Asia/Dhaka', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(14, 'Currency', 'USD', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(15, 'CurrencyCode', '$', '2017-05-27 02:32:18', '2017-05-27 02:32:18'),
(16, 'Gateway', 'default', '2017-05-27 02:32:19', '2017-05-27 02:41:54'),
(17, 'SMTPHostName', 'smtp.gmail.com', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(18, 'SMTPUserName', 'user@example.com', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(19, 'SMTPPassword', 'testpassword', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(20, 'SMTPPort', '587', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(21, 'SMTPSecure', 'tls', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(22, 'AppStage', 'Live', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(23, 'DateFormat', 'jS M y', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(24, 'Language', '1', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(25, 'sms_api_permission', '1', '2017-05-27 02:32:19', '2017-05-27 02:41:54'),
(26, 'sms_api_gateway', '1', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(27, 'api_url', 'ultimatesms.coderpixel.com', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(28, 'api_key', 'YWRtaW46YWRtaW4ucGFzc3dvcmQ=', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(29, 'client_registration', '1', '2017-05-27 02:32:19', '2017-05-27 02:41:54'),
(30, 'registration_verification', '0', '2017-05-27 02:32:19', '2017-05-27 02:41:54'),
(31, 'captcha_in_admin', '0', '2017-05-27 02:32:19', '2017-05-27 02:41:55'),
(32, 'captcha_in_client', '0', '2017-05-27 02:32:19', '2017-05-27 02:41:55'),
(33, 'captcha_in_client_registration', '1', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(34, 'captcha_site_key', '6LcVTCEUAAAAAF2VucYNRFbnfD12MO41LpcS71o9', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(35, 'captcha_secret_key', '6LcVTCEUAAAAAGBbxACgcO6sBFPNIrMOkXJGh-Yu', '2017-05-27 02:32:19', '2017-05-27 02:32:19'),
(36, 'purchase_code_error_count', '0', '2017-05-27 02:32:19', '2017-05-27 02:32:19');

-- --------------------------------------------------------

--
-- Table structure for table `sys_clients`
--

CREATE TABLE `sys_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `groupid` int(11) NOT NULL DEFAULT '0',
  `parent` int(11) NOT NULL DEFAULT '0',
  `fname` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lname` text COLLATE utf8mb4_unicode_ci,
  `company` text COLLATE utf8mb4_unicode_ci,
  `website` text COLLATE utf8mb4_unicode_ci,
  `email` text COLLATE utf8mb4_unicode_ci,
  `username` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `address1` text COLLATE utf8mb4_unicode_ci,
  `address2` text COLLATE utf8mb4_unicode_ci,
  `state` text COLLATE utf8mb4_unicode_ci,
  `city` text COLLATE utf8mb4_unicode_ci,
  `postcode` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` text COLLATE utf8mb4_unicode_ci,
  `datecreated` date NOT NULL DEFAULT '2017-05-27',
  `sms_limit` int(11) NOT NULL DEFAULT '0',
  `api_access` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `api_key` text COLLATE utf8mb4_unicode_ci,
  `online` int(11) NOT NULL DEFAULT '0',
  `status` enum('Active','Inactive','Closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `reseller` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `sms_gateway` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `lastlogin` date DEFAULT NULL,
  `pwresetkey` text COLLATE utf8mb4_unicode_ci,
  `pwresetexpiry` int(11) DEFAULT NULL,
  `emailnotify` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `menu_open` int(11) NOT NULL DEFAULT '0',
  `remember_token` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_client_groups`
--

CREATE TABLE `sys_client_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `status` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_custom_sms_gateways`
--

CREATE TABLE `sys_custom_sms_gateways` (
  `id` int(10) UNSIGNED NOT NULL,
  `gateway_id` int(11) NOT NULL,
  `username_param` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_value` text COLLATE utf8mb4_unicode_ci,
  `password_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `action_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_value` text COLLATE utf8mb4_unicode_ci,
  `action_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `source_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_value` text COLLATE utf8mb4_unicode_ci,
  `source_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `destination_param` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_param` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unicode_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unicode_value` text COLLATE utf8mb4_unicode_ci,
  `unicode_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `route_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_value` text COLLATE utf8mb4_unicode_ci,
  `route_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `language_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language_value` text COLLATE utf8mb4_unicode_ci,
  `language_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `custom_one_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_one_value` text COLLATE utf8mb4_unicode_ci,
  `custom_one_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `custom_two_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_two_value` text COLLATE utf8mb4_unicode_ci,
  `custom_two_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `custom_three_param` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_three_value` text COLLATE utf8mb4_unicode_ci,
  `custom_three_status` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_email_templates`
--

CREATE TABLE `sys_email_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `tplname` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sys_email_templates`
--

INSERT INTO `sys_email_templates` (`id`, `tplname`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Client SignUp', 'Welcome to {{business_name}}', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <div width=\"125\" height=\"23\" style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\">{{business_name}}</div>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Welcome to {{business_name}}! This message is an automated reply to your User Access request. Login to your User panel by using the details below:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\"{{sys_url}}\">{{sys_url}}</a>.<br>\n                                    User Name: {{username}}<br>\n                                    Password: {{password}}\n            <br>\n            Regards,<br>\n            {{business_name}}<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:23', '2017-05-27 02:32:23'),
(2, 'Client Registration Verification', 'Registration Verification From {{business_name}}', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <div width=\"125\" height=\"23\" style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\">{{business_name}}</div>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Welcome to {{business_name}}! This message is an automated reply to your account verification request. Click the following url to verify your account:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\"{{sys_url}}\">{{sys_url}}</a>\n            <br>\n            Regards,<br>\n            {{business_name}}<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:23', '2017-05-27 02:32:23'),
(3, 'Ticket For Client', 'New Ticket From {{business_name}}', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <div width=\"125\" height=\"23\" style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\" >{{business_name}}</div>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Thank you for stay with us! This is a Support Ticket For Yours.. Login to your account to view  your support tickets details:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\"{{sys_url}}\">{{sys_url}}</a>.<br>\n                Ticket ID: {{ticket_id}}<br>\n                Ticket Subject: {{ticket_subject}}<br>\n                Message: {{message}}<br>\n                Created By: {{create_by}}\n            <br>\n            Regards,<br>\n            {{business_name}}<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">Â </td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\"> </td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright Â© {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n\n                ', '1', '2017-05-27 02:32:23', '2017-05-27 02:32:23'),
(4, 'Admin Password Reset', '{{business_name}} New Password', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <p  style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\">{{business_name}}</p>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Password Reset Successfully!   This message is an automated reply to your password reset request. Login to your account to set up your all details by using the details below:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\" {{sys_url}}\"> {{sys_url}}</a>.<br>\n                                    User Name: {{username}}<br>\n                                    Password: {{password}}\n            <br>\n            {{business_name}},<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:23', '2017-05-27 02:32:23'),
(5, 'Forgot Admin Password', '{{business_name}} password change request', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <p  style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\" >{{business_name}}</p>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Password Reset Successfully!   This message is an automated reply to your password reset request. Click this link to reset your password:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\" {{forgotpw_link}} \"> {{forgotpw_link}} </a>.<br>\nNotes: Until your password has been changed, your current password will remain valid. The Forgot Password Link will be available for a limited time only.\n\n            <br>\n            On behalf of the {{business_name}},<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:23', '2017-05-27 02:32:23');
INSERT INTO `sys_email_templates` (`id`, `tplname`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Ticket Reply', 'Reply to Ticket [TID-{{ticket_id}}]', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <div width=\"125\" height=\"23\" style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\"  {{business_name}} ></div>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Thank you for stay with us! This is a Support Ticket Reply. Login to your account to view  your support ticket reply details:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\"{{sys_url}}\">{{sys_url}}</a>.<br>\n                Ticket ID: {{ticket_id}}<br>\n                Ticket Subject: {{ticket_subject}}<br>\n                Message: {{message}}<br>\n                Replyed By: {{reply_by}} <br><br>\n                Should you have any questions in regards to this support ticket or any other tickets related issue, please feel free to contact the Support department by creating a new ticket from your Client/User Portal\n            <br><br>\n            Regards,<br>\n            {{business_name}}<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:23', '2017-05-27 02:32:23'),
(7, 'Forgot Client Password', '{{business_name}} password change request', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <p  style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\">{{business_name}} </p>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Password Reset Successfully!   This message is an automated reply to your password reset request. Click this link to reset your password:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\" {{forgotpw_link}} \"> {{forgotpw_link}} </a>.<br>\nNotes: Until your password has been changed, your current password will remain valid. The Forgot Password Link will be available for a limited time only.\n\n            <br>\n            {{business_name}}<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:23', '2017-05-27 02:32:23'),
(8, 'Client Registrar Activation', '{{business_name}} Registration Code', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <p  style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\">{{business_name}} </p>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Registration Successfully!   This message is an automated reply to your active registration request. Click this link to active your account:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\" {{registration_link}} \"> {{registration_link}} </a>.<br>\n            <br>\n            {{business_name}}<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:24', '2017-05-27 02:32:24'),
(9, 'Client Password Reset', '{{business_name}} New Password', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <p  style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\" >{{business_name}}</p>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>\n                 <br>\n                Password Reset Successfully!   This message is an automated reply to your password reset request. Login to your account to set up your all details by using the details below:\n            <br>\n                <a target=\"_blank\" style=\"color:#ff6600;font-weight:bold;font-family:helvetica,arial,sans-seif;text-decoration:none\" href=\" {{sys_url}}\"> {{sys_url}}</a>.<br>\n                                    User Name: {{username}}<br>\n                                    Password: {{password}}\n            <br>\n            {{business_name}}<br>\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:24', '2017-05-27 02:32:24'),
(10, 'Ticket For Admin', 'New Ticket From {{business_name}} Client', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <div width=\"125\" height=\"23\" style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\" >{{business_name}}</div>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>{{department_name}},<br>\n                 <br>\n\n                Ticket ID: {{ticket_id}}<br>\n                Ticket Subject: {{ticket_subject}}<br>\n                Message: {{message}}<br>\n                Created By: {{create_by}} <br><br>\n                Waiting for your quick response.\n            <br><br>\n            Thank you.\n            <br>\n            Regards,<br>\n            {{name}}<br>\n{{business_name}} User.\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:24', '2017-05-27 02:32:24');
INSERT INTO `sys_email_templates` (`id`, `tplname`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(11, 'Client Ticket Reply', 'Reply to Ticket [TID-{{ticket_id}}]', '<div style=\"margin:0;padding:0\">\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#439cc8\">\n  <tbody><tr>\n    <td align=\"center\">\n            <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n              <tbody><tr>\n                <td height=\"95\" bgcolor=\"#439cc8\" style=\"background:#439cc8;text-align:left\">\n                <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                      <tbody><tr>\n                        <td width=\"672\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                      </tr>\n                      <tr>\n                        <td style=\"text-align:left\">\n                        <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\">\n                          <tbody><tr>\n                            <td width=\"37\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\">\n                            </td>\n                            <td width=\"523\" height=\"24\" style=\"text-align:left\">\n                            <div width=\"125\" height=\"23\" style=\"display:block;color:#ffffff;font-size:20px;font-family:Arial,Helvetica,sans-serif;max-width:557px;min-height:auto\">{{business_name}}</div>\n                            </td>\n                            <td width=\"44\" style=\"text-align:left\"></td>\n                            <td width=\"30\" style=\"text-align:left\"></td>\n                            <td width=\"38\" height=\"24\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n                          </tr>\n                        </tbody></table>\n                        </td>\n                      </tr>\n                      <tr><td width=\"672\" height=\"33\" style=\"font-size:33px;line-height:33px;height:33px;text-align:left\"></td></tr>\n                    </tbody></table>\n\n                </td>\n              </tr>\n            </tbody></table>\n     </td>\n    </tr>\n </tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" bgcolor=\"#439cc8\"><tbody><tr><td height=\"5\" style=\"background:#439cc8;height:5px;font-size:5px;line-height:5px\"></td></tr></tbody></table>\n\n <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#e9eff0\">\n  <tbody><tr>\n    <td align=\"center\">\n      <table cellspacing=\"0\" cellpadding=\"0\" width=\"671\" border=\"0\" bgcolor=\"#e9eff0\" style=\"background:#e9eff0\">\n        <tbody><tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n          <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"596\" border=\"0\" bgcolor=\"#ffffff\">\n            <tbody><tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n              <td width=\"556\" style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\" style=\"font-family:helvetica,arial,sans-seif;color:#666666;font-size:16px;line-height:22px\">\n                <tbody><tr>\n                  <td style=\"text-align:left\"></td>\n                </tr>\n                <tr>\n                  <td style=\"text-align:left\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"556\" border=\"0\">\n                    <tbody><tr><td style=\"font-family:helvetica,arial,sans-serif;font-size:30px;line-height:40px;font-weight:normal;color:#253c44;text-align:left\"></td></tr>\n                    <tr><td width=\"556\" height=\"20\" style=\"font-size:20px;line-height:20px;height:20px;text-align:left\"></td></tr>\n                    <tr>\n                      <td style=\"text-align:left\">\n                 Hi {{name}},<br>{{department_name}},<br>\n                 <br>\n                 This is a Support Ticket Reply From Client.\n            <br>\n                Ticket ID: {{ticket_id}}<br>\n                Ticket Subject: {{ticket_subject}}<br>\n                Message: {{message}}<br>\n                Replyed By: {{reply_by}}  <br><br>\n                Waiting for your quick response.\n            <br><br>\n            Thank you.\n            <br>\n            Regards,<br>\n            {{name}}<br>\n{{business_name}} User.\n            <br>\n          </td>\n                    </tr>\n                    <tr>\n                      <td width=\"556\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\">&nbsp;</td>\n                    </tr>\n                  </tbody></table></td>\n                </tr>\n              </tbody></table></td>\n              <td width=\"20\" height=\"26\" style=\"font-size:26px;line-height:26px;height:26px;text-align:left\"></td>\n            </tr>\n            <tr>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"556\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n              <td width=\"20\" height=\"2\" bgcolor=\"#d9dfe1\" style=\"background-color:#d9dfe1;font-size:2px;line-height:2px;height:2px;text-align:left\"></td>\n            </tr>\n          </tbody></table></td>\n          <td width=\"37\" height=\"40\" style=\"font-size:40px;line-height:40px;height:40px;text-align:left\"></td>\n        </tr>\n        <tr>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"596\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"37\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n        </tr>\n      </tbody></table>\n  </td></tr>\n</tbody>\n</table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#273f47\"><tbody><tr><td align=\"center\">&nbsp;</td></tr></tbody></table>\n<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\" bgcolor=\"#364a51\">\n  <tbody><tr>\n    <td align=\"center\">\n       <table cellspacing=\"0\" cellpadding=\"0\" width=\"672\" border=\"0\" bgcolor=\"#364a51\">\n              <tbody><tr>\n              <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"569\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n          <td width=\"38\" height=\"30\" style=\"font-size:30px;line-height:30px;height:30px;text-align:left\"></td>\n              </tr>\n              <tr>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\">\n                </td>\n                <td valign=\"top\" style=\"font-family:helvetica,arial,sans-seif;font-size:12px;line-height:16px;color:#949fa3;text-align:left\">Copyright &copy; {{business_name}}, All rights reserved.<br><br><br></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n              <tr>\n              <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              <td width=\"569\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n                <td width=\"38\" height=\"40\" style=\"font-size:40px;line-height:40px;text-align:left\"></td>\n              </tr>\n            </tbody></table>\n     </td>\n  </tr>\n</tbody></table><div class=\"yj6qo\"></div><div class=\"adL\">\n\n</div></div>\n', '1', '2017-05-27 02:32:24', '2017-05-27 02:32:24');

-- --------------------------------------------------------

--
-- Table structure for table `sys_int_country_codes`
--

CREATE TABLE `sys_int_country_codes` (
  `id` int(10) UNSIGNED NOT NULL,
  `country_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iso_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tariff` decimal(5,2) NOT NULL DEFAULT '3.00',
  `active` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sys_int_country_codes`
--

INSERT INTO `sys_int_country_codes` (`id`, `country_name`, `iso_code`, `country_code`, `tariff`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Afghanistan', 'AF / AFG', '93', '1.00', '1', '2017-05-27 02:32:24', '2017-05-27 02:32:24'),
(2, 'Albania', 'AL / ALB', '355', '1.00', '1', '2017-05-27 02:32:24', '2017-05-27 02:32:24'),
(3, 'Algeria', 'DZ / DZA', '213', '1.00', '1', '2017-05-27 02:32:24', '2017-05-27 02:32:24'),
(4, 'Andorra', 'AD / AND', '376', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(5, 'Angola', 'AO / AGO', '244', '1.00', '0', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(6, 'Antarctica', 'AQ / ATA', '672', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(7, 'Argentina', 'AR / ARG', '54', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(8, 'Armenia', 'AM / ARM', '374', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(9, 'Aruba', 'AW / ABW', '297', '1.00', '0', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(10, 'Australia', 'AU / AUS', '61', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(11, 'Austria', 'AT / AUT', '43', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(12, 'Azerbaijan', 'AZ / AZE', '994', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(13, 'Bahrain', 'BH / BHR', '973', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(14, 'Bangladesh', 'BD / BGD', '880', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(15, 'Belarus', 'BY / BLR', '375', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(16, 'Belgium', 'BE / BEL', '32', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(17, 'Belize', 'BZ / BLZ', '501', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(18, 'Benin', 'BJ / BEN', '229', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(19, 'Bhutan', 'BT / BTN', '975', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(20, 'Bolivia', 'BO / BOL', '591', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(21, 'Bosnia and Herzegovina', 'BA / BIH', '387', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(22, 'Botswana', 'BW / BWA', '267', '1.00', '1', '2017-05-27 02:32:25', '2017-05-27 02:32:25'),
(23, 'Brazil', 'BR / BRA', '55', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(24, 'Brunei', 'BN / BRN', '673', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(25, 'Bulgaria', 'BG / BGR', '359', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(26, 'Burkina Faso', 'BF / BFA', '226', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(27, 'Burma (Myanmar)', 'MM / MMR', '95', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(28, 'Burundi', 'BI / BDI', '257', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(29, 'Cambodia', 'KH / KHM', '855', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(30, 'Cameroon', 'CM / CMR', '237', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(31, 'Canada', 'CA / CAN', '1', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(32, 'Cape Verde', 'CV / CPV', '238', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(33, 'Central African Republic', 'CF / CAF', '236', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(34, 'Chad', 'TD / TCD', '235', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(35, 'Chile', 'CL / CHL', '56', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(36, 'China', 'CN / CHN', '86', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(37, 'Christmas Island', 'CX / CXR', '61', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(38, 'Cocos (Keeling) Islands', 'CC / CCK', '61', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(39, 'Colombia', 'CO / COL', '57', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(40, 'Comoros', 'KM / COM', '269', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(41, 'Congo', 'CD / COD', '243', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(42, 'Cook Islands', 'CK / COK', '682', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(43, 'Costa Rica', 'CR / CRC', '506', '1.00', '1', '2017-05-27 02:32:26', '2017-05-27 02:32:26'),
(44, 'Croatia', 'HR / HRV', '385', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(45, 'Cuba', 'CU / CUB', '53', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(46, 'Cyprus', 'CY / CYP', '357', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(47, 'Czech Republic', 'CZ / CZE', '420', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(48, 'Denmark', 'DK / DNK', '45', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(49, 'Djibouti', 'DJ / DJI', '253', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(50, 'Ecuador', 'EC / ECU', '593', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(51, 'Egypt', 'EG / EGY', '20', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(52, 'El Salvador', 'SV / SLV', '503', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(53, 'Equatorial Guinea', 'GQ / GNQ', '240', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(54, 'Eritrea', 'ER / ERI', '291', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(55, 'Estonia', 'EE / EST', '372', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(56, 'Ethiopia', 'ET / ETH', '251', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(57, 'Falkland Islands', 'FK / FLK', '500', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(58, 'Faroe Islands', 'FO / FRO', '298', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(59, 'Fiji', 'FJ / FJI', '679', '1.00', '1', '2017-05-27 02:32:27', '2017-05-27 02:32:27'),
(60, 'Finland', 'FI / FIN', '358', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(61, 'France', 'FR / FRA', '33', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(62, 'French Polynesia', 'PF / PYF', '689', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(63, 'Gabon', 'GA / GAB', '241', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(64, 'Gambia', 'GM / GMB', '220', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(65, 'Gaza Strip', '/', '970', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(66, 'Georgia', 'GE / GEO', '995', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(67, 'Germany', 'DE / DEU', '49', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(68, 'Ghana', 'GH / GHA', '233', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(69, 'Gibraltar', 'GI / GIB', '350', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(70, 'Greece', 'GR / GRC', '30', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(71, 'Greenland', 'GL / GRL', '299', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(72, 'Guatemala', 'GT / GTM', '502', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(73, 'Guinea', 'GN / GIN', '224', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(74, 'Guinea-Bissau', 'GW / GNB', '245', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(75, 'Guyana', 'GY / GUY', '592', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(76, 'Haiti', 'HT / HTI', '509', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(77, 'Holy See (Vatican City)', 'VA / VAT', '39', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(78, 'Honduras', 'HN / HND', '504', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(79, 'Hong Kong', 'HK / HKG', '852', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(80, 'Hungary', 'HU / HUN', '36', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(81, 'Iceland', 'IS / IS', '354', '1.00', '1', '2017-05-27 02:32:28', '2017-05-27 02:32:28'),
(82, 'India', 'IN / IND', '91', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(83, 'Indonesia', 'ID / IDN', '62', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(84, 'Iran', 'IR / IRN', '98', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(85, 'Iraq', 'IQ / IRQ', '964', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(86, 'Ireland', 'IE / IRL', '353', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(87, 'Isle of Man', 'IM / IMN', '44', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(88, 'Israel', 'IL / ISR', '972', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(89, 'Italy', 'IT / ITA', '39', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(90, 'Ivory Coast', 'CI / CIV', '225', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(91, 'Japan', 'JP / JPN', '81', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(92, 'Jordan', 'JO / JOR', '962', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(93, 'Kazakhstan', 'KZ / KAZ', '7', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(94, 'Kenya', 'KE / KEN', '254', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(95, 'Kiribati', 'KI / KIR', '686', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(96, 'Kosovo', '/', '381', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(97, 'Kuwait', 'KW / KWT', '965', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(98, 'Kyrgyzstan', 'KG / KGZ', '996', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(99, 'Laos', 'LA / LAO', '856', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(100, 'Latvia', 'LV / LVA', '371', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(101, 'Lebanon', 'LB / LBN', '961', '1.00', '1', '2017-05-27 02:32:29', '2017-05-27 02:32:29'),
(102, 'Lesotho', 'LS / LSO', '266', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(103, 'Liberia', 'LR / LBR', '231', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(104, 'Libya', 'LY / LBY', '218', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(105, 'Liechtenstein', 'LI / LIE', '423', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(106, 'Lithuania', 'LT / LTU', '370', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(107, 'Luxembourg', 'LU / LUX', '352', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(108, 'Macau', 'MO / MAC', '853', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(109, 'Macedonia', 'MK / MKD', '389', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(110, 'Madagascar', 'MG / MDG', '261', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(111, 'Malawi', 'MW / MWI', '265', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(112, 'Malaysia', 'MY / MYS', '60', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(113, 'Maldives', 'MV / MDV', '960', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(114, 'Mali', 'ML / MLI', '223', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(115, 'Malta', 'MT / MLT', '356', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(116, 'Marshall Islands', 'MH / MHL', '692', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(117, 'Mauritania', 'MR / MRT', '222', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(118, 'Mauritius', 'MU / MUS', '230', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(119, 'Mayotte', 'YT / MYT', '262', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(120, 'Mexico', 'MX / MEX', '52', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(121, 'Micronesia', 'FM / FSM', '691', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(122, 'Moldova', 'MD / MDA', '373', '1.00', '1', '2017-05-27 02:32:30', '2017-05-27 02:32:30'),
(123, 'Monaco', 'MC / MCO', '377', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(124, 'Mongolia', 'MN / MNG', '976', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(125, 'Montenegro', 'ME / MNE', '382', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(126, 'Morocco', 'MA / MAR', '212', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(127, 'Mozambique', 'MZ / MOZ', '258', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(128, 'Namibia', 'NA / NAM', '264', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(129, 'Nauru', 'NR / NRU', '674', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(130, 'Nepal', 'NP / NPL', '977', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(131, 'Netherlands', 'NL / NLD', '31', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(132, 'Netherlands Antilles', 'AN / ANT', '599', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(133, 'New Caledonia', 'NC / NCL', '687', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(134, 'New Zealand', 'NZ / NZL', '64', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(135, 'Nicaragua', 'NI / NIC', '505', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(136, 'Niger', 'NE / NER', '227', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(137, 'Nigeria', 'NG / NGA', '234', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(138, 'Niue', 'NU / NIU', '683', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(139, 'Norfolk Island', '/ NFK', '672', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(140, 'North Korea', 'KP / PRK', '850', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(141, 'Norway', 'NO / NOR', '47', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(142, 'Oman', 'OM / OMN', '968', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(143, 'Pakistan', 'PK / PAK', '92', '1.00', '1', '2017-05-27 02:32:31', '2017-05-27 02:32:31'),
(144, 'Palau', 'PW / PLW', '680', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(145, 'Panama', 'PA / PAN', '507', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(146, 'Papua New Guinea', 'PG / PNG', '675', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(147, 'Paraguay', 'PY / PRY', '595', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(148, 'Peru', 'PE / PER', '51', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(149, 'Philippines', 'PH / PHL', '63', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(150, 'Pitcairn Islands', 'PN / PCN', '870', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(151, 'Poland', 'PL / POL', '48', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(152, 'Portugal', 'PT / PRT', '351', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(153, 'Puerto Rico', 'PR / PRI', '1', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(154, 'Qatar', 'QA / QAT', '974', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(155, 'Republic of the Congo', 'CG / COG', '242', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(156, 'Romania', 'RO / ROU', '40', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(157, 'Russia', 'RU / RUS', '7', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(158, 'Rwanda', 'RW / RWA', '250', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(159, 'Saint Barthelemy', 'BL / BLM', '590', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(160, 'Saint Helena', 'SH / SHN', '290', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(161, 'Saint Pierre and Miquelon', 'PM / SPM', '508', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(162, 'Samoa', 'WS / WSM', '685', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(163, 'San Marino', 'SM / SMR', '378', '1.00', '1', '2017-05-27 02:32:32', '2017-05-27 02:32:32'),
(164, 'Sao Tome and Principe', 'ST / STP', '239', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(165, 'Saudi Arabia', 'SA / SAU', '966', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(166, 'Senegal', 'SN / SEN', '221', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(167, 'Serbia', 'RS / SRB', '381', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(168, 'Seychelles', 'SC / SYC', '248', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(169, 'Sierra Leone', 'SL / SLE', '232', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(170, 'Singapore', 'SG / SGP', '65', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(171, 'Slovakia', 'SK / SVK', '421', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(172, 'Slovenia', 'SI / SVN', '386', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(173, 'Solomon Islands', 'SB / SLB', '677', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(174, 'Somalia', 'SO / SOM', '252', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(175, 'South Africa', 'ZA / ZAF', '27', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(176, 'South Korea', 'KR / KOR', '82', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(177, 'Spain', 'ES / ESP', '34', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(178, 'Sri Lanka', 'LK / LKA', '94', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(179, 'Sudan', 'SD / SDN', '249', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(180, 'Suriname', 'SR / SUR', '597', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(181, 'Swaziland', 'SZ / SWZ', '268', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(182, 'Sweden', 'SE / SWE', '46', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(183, 'Switzerland', 'CH / CHE', '41', '1.00', '1', '2017-05-27 02:32:33', '2017-05-27 02:32:33'),
(184, 'Syria', 'SY / SYR', '963', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(185, 'Taiwan', 'TW / TWN', '886', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(186, 'Tajikistan', 'TJ / TJK', '992', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(187, 'Tanzania', 'TZ / TZA', '255', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(188, 'Thailand', 'TH / THA', '66', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(189, 'Timor-Leste', 'TL / TLS', '670', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(190, 'Togo', 'TG / TGO', '228', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(191, 'Tokelau', 'TK / TKL', '690', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(192, 'Tonga', 'TO / TON', '676', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(193, 'Tunisia', 'TN / TUN', '216', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(194, 'Turkey', 'TR / TUR', '90', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(195, 'Turkmenistan', 'TM / TKM', '993', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(196, 'Tuvalu', 'TV / TUV', '688', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(197, 'Uganda', 'UG / UGA', '256', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(198, 'Ukraine', 'UA / UKR', '380', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(199, 'United Arab Emirates', 'AE / ARE', '971', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(200, 'United Kingdom', 'GB / GBR', '44', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(201, 'United States', 'US / USA', '1', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(202, 'Uruguay', 'UY / URY', '598', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(203, 'Uzbekistan', 'UZ / UZB', '998', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(204, 'Vanuatu', 'VU / VUT', '678', '1.00', '1', '2017-05-27 02:32:34', '2017-05-27 02:32:34'),
(205, 'Venezuela', 'VE / VEN', '58', '1.00', '1', '2017-05-27 02:32:35', '2017-05-27 02:32:35'),
(206, 'Vietnam', 'VN / VNM', '84', '1.00', '1', '2017-05-27 02:32:35', '2017-05-27 02:32:35'),
(207, 'Wallis and Futuna', 'WF / WLF', '681', '1.00', '1', '2017-05-27 02:32:35', '2017-05-27 02:32:35'),
(208, 'West Bank', '/', '970', '1.00', '1', '2017-05-27 02:32:35', '2017-05-27 02:32:35'),
(209, 'Yemen', 'YE / YEM', '967', '1.00', '1', '2017-05-27 02:32:35', '2017-05-27 02:32:35'),
(210, 'Zambia', 'ZM / ZMB', '260', '1.00', '1', '2017-05-27 02:32:35', '2017-05-27 02:32:35'),
(211, 'Zimbabwe', 'ZW / ZWE', '263', '1.00', '1', '2017-05-27 02:32:35', '2017-05-27 02:32:35');

-- --------------------------------------------------------

--
-- Table structure for table `sys_invoices`
--

CREATE TABLE `sys_invoices` (
  `id` int(10) UNSIGNED NOT NULL,
  `cl_id` int(11) NOT NULL,
  `client_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` date NOT NULL DEFAULT '2017-05-27',
  `duedate` date DEFAULT NULL,
  `datepaid` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('Unpaid','Paid','Partially Paid','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unpaid',
  `pmethod` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurring` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bill_created` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_invoice_items`
--

CREATE TABLE `sys_invoice_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `inv_id` int(11) NOT NULL,
  `cl_id` int(11) NOT NULL,
  `item` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty` int(11) NOT NULL DEFAULT '0',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_language`
--

CREATE TABLE `sys_language` (
  `id` int(10) UNSIGNED NOT NULL,
  `language` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sys_language`
--

INSERT INTO `sys_language` (`id`, `language`, `status`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'English', 'Active', 'us.gif', '2017-05-27 02:32:35', '2017-05-27 02:32:35');

-- --------------------------------------------------------

--
-- Table structure for table `sys_language_data`
--

CREATE TABLE `sys_language_data` (
  `id` int(10) UNSIGNED NOT NULL,
  `lan_id` int(11) NOT NULL,
  `lan_data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lan_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sys_language_data`
--

INSERT INTO `sys_language_data` (`id`, `lan_id`, `lan_data`, `lan_value`, `created_at`, `updated_at`) VALUES
(1, 1, 'Admin', 'Admin', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(2, 1, 'Login', 'Login', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(3, 1, 'Forget Password', 'Forget Password', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(4, 1, 'Sign to your account', 'Sign to your account', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(5, 1, 'User Name', 'User Name', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(6, 1, 'Password', 'Password', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(7, 1, 'Remember Me', 'Remember Me', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(8, 1, 'Reset your password', 'Reset your password', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(9, 1, 'Email', 'Email', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(10, 1, 'Add New Client', 'Add New Client', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(11, 1, 'First Name', 'First Name', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(12, 1, 'Last Name', 'Last Name', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(13, 1, 'Company', 'Company', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(14, 1, 'Website', 'Website', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(15, 1, 'If you leave this, then you can not reset password or can not maintain email related function', 'If you leave this, then you can not reset password or can not maintain email related function', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(16, 1, 'Confirm Password', 'Confirm Password', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(17, 1, 'Phone', 'Phone', '2017-05-27 02:32:36', '2017-05-27 02:32:36'),
(18, 1, 'Address', 'Address', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(19, 1, 'More Address', 'More Address', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(20, 1, 'State', 'State', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(21, 1, 'City', 'City', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(22, 1, 'Postcode', 'Postcode', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(23, 1, 'Country', 'Country', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(24, 1, 'Api Access', 'Api Access', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(25, 1, 'Yes', 'Yes', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(26, 1, 'No', 'No', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(27, 1, 'Client Group', 'Client Group', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(28, 1, 'None', 'None', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(29, 1, 'SMS Gateway', 'SMS Gateway', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(30, 1, 'SMS Limit', 'SMS Limit', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(31, 1, 'Avatar', 'Avatar', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(32, 1, 'Browse', 'Browse', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(33, 1, 'Notify Client with email', 'Notify Client with email', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(34, 1, 'Add', 'Add', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(35, 1, 'Add New Invoice', 'Add New Invoice', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(36, 1, 'Client', 'Client', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(37, 1, 'Invoice Type', 'Invoice Type', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(38, 1, 'One Time', 'One Time', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(39, 1, 'Recurring', 'Recurring', '2017-05-27 02:32:37', '2017-05-27 02:32:37'),
(40, 1, 'Invoice Date', 'Invoice Date', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(41, 1, 'Due Date', 'Due Date', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(42, 1, 'Paid Date', 'Paid Date', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(43, 1, 'Repeat Every', 'Repeat Every', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(44, 1, 'Week', 'Week', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(45, 1, '2 Weeks', '2 Weeks', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(46, 1, 'Month', 'Month', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(47, 1, '2 Months', '2 Months', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(48, 1, '3 Months', '3 Months', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(49, 1, '6 Months', '6 Months', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(50, 1, 'Year', 'Year', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(51, 1, '2 Years', '2 Years', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(52, 1, '3 Years', '3 Years', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(53, 1, 'Item Name', 'Item Name', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(54, 1, 'Price', 'Price', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(55, 1, 'Qty', 'Qty', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(56, 1, 'Quantity', 'Quantity', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(57, 1, 'Tax', 'Tax', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(58, 1, 'Discount', 'Discount', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(59, 1, 'Per Item Total', 'Per Item Total', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(60, 1, 'Add Item', 'Add Item', '2017-05-27 02:32:38', '2017-05-27 02:32:38'),
(61, 1, 'Item', 'Item', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(62, 1, 'Delete', 'Delete', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(63, 1, 'Total', 'Total', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(64, 1, 'Invoice Note', 'Invoice Note', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(65, 1, 'Create Invoice', 'Create Invoice', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(66, 1, 'Add Plan Feature', 'Add Plan Feature', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(67, 1, 'Show In Client', 'Show In Client', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(68, 1, 'Feature Name', 'Feature Name', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(69, 1, 'Feature Value', 'Feature Value', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(70, 1, 'Action', 'Action', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(71, 1, 'Add More', 'Add More', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(72, 1, 'Save', 'Save', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(73, 1, 'Add SMS Price Plan', 'Add SMS Price Plan', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(74, 1, 'Plan Name', 'Plan Name', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(75, 1, 'Mark Popular', 'Mark Popular', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(76, 1, 'Popular', 'Popular', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(77, 1, 'Show', 'Show', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(78, 1, 'Hide', 'Hide', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(79, 1, 'Add Plan', 'Add Plan', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(80, 1, 'Add Sender ID', 'Add Sender ID', '2017-05-27 02:32:39', '2017-05-27 02:32:39'),
(81, 1, 'All', 'All', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(82, 1, 'Status', 'Status', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(83, 1, 'Block', 'Block', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(84, 1, 'Unblock', 'Unblock', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(85, 1, 'Sender ID', 'Sender ID', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(86, 1, 'Add SMS Gateway', 'Add SMS Gateway', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(87, 1, 'Gateway Name', 'Gateway Name', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(88, 1, 'Gateway API Link', 'Gateway API Link', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(89, 1, 'Api link execute like', 'Api link execute like', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(90, 1, 'Active', 'Active', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(91, 1, 'Inactive', 'Inactive', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(92, 1, 'Parameter', 'Parameter', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(93, 1, 'Value', 'Value', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(94, 1, 'Add On URL', 'Add On URL', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(95, 1, 'Username_Key', 'Username/Key', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(96, 1, 'Set Blank', 'Set Blank', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(97, 1, 'Add on parameter', 'Add on parameter', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(98, 1, 'Source', 'Source', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(99, 1, 'Destination', 'Destination', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(100, 1, 'Message', 'Message', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(101, 1, 'Unicode', 'Unicode', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(102, 1, 'Type_Route', 'Type/Route', '2017-05-27 02:32:40', '2017-05-27 02:32:40'),
(103, 1, 'Language', 'Language', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(104, 1, 'Custom Value 1', 'Custom Value 1', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(105, 1, 'Custom Value 2', 'Custom Value 2', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(106, 1, 'Custom Value 3', 'Custom Value 3', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(107, 1, 'Administrator Roles', 'Administrator Roles', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(108, 1, 'Add Administrator Role', 'Add Administrator Role', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(109, 1, 'Role Name', 'Role Name', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(110, 1, 'SL', 'SL', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(111, 1, 'Set Roles', 'Set Roles', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(112, 1, 'Administrators', 'Administrators', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(113, 1, 'Add New Administrator', 'Add New Administrator', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(114, 1, 'Role', 'Role', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(115, 1, 'Notify Administrator with email', 'Notify Administrator with email', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(116, 1, 'Name', 'Name', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(117, 1, 'All Clients', 'All Clients', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(118, 1, 'Clients', 'Clients', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(119, 1, 'Created', 'Created', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(120, 1, 'Created By', 'Created By', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(121, 1, 'Manage', 'Manage', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(122, 1, 'Closed', 'Closed', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(123, 1, 'All Invoices', 'All Invoices', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(124, 1, 'Client Name', 'Client Name', '2017-05-27 02:32:41', '2017-05-27 02:32:41'),
(125, 1, 'Amount', 'Amount', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(126, 1, 'Type', 'Type', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(127, 1, 'Unpaid', 'Unpaid', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(128, 1, 'Paid', 'Paid', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(129, 1, 'Cancelled', 'Cancelled', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(130, 1, 'Partially Paid', 'Partially Paid', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(131, 1, 'Onetime', 'Onetime', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(132, 1, 'Recurring', 'Recurring', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(133, 1, 'Stop Recurring', 'Stop Recurring', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(134, 1, 'View', 'View', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(135, 1, 'Change Password', 'Change Password', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(136, 1, 'Current Password', 'Current Password', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(137, 1, 'New Password', 'New Password', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(138, 1, 'Update', 'Update', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(139, 1, 'Edit', 'Edit', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(140, 1, 'Clients Groups', 'Clients Groups', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(141, 1, 'Add New Group', 'Add New Group', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(142, 1, 'Group Name', 'Group Name', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(143, 1, 'Export Clients', 'Export Clients', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(144, 1, 'View Profile', 'View Profile', '2017-05-27 02:32:42', '2017-05-27 02:32:42'),
(145, 1, 'Location', 'Location', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(146, 1, 'SMS Balance', 'SMS Balance', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(147, 1, 'Send SMS', 'Send SMS', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(148, 1, 'Update Limit', 'Update Limit', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(149, 1, 'Change Image', 'Change Image', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(150, 1, 'Edit Profile', 'Edit Profile', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(151, 1, 'Support Tickets', 'Support Tickets', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(152, 1, 'Change', 'Change', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(153, 1, 'Basic Info', 'Basic Info', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(154, 1, 'Invoices', 'Invoices', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(155, 1, 'SMS Transaction', 'SMS Transaction', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(156, 1, 'Leave blank if you do not change', 'Leave blank if you do not change', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(157, 1, 'Subject', 'Subject', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(158, 1, 'Date', 'Date', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(159, 1, 'Pending', 'Pending', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(160, 1, 'Answered', 'Answered', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(161, 1, 'Customer Reply', 'Customer Reply', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(162, 1, 'characters remaining', 'characters remaining', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(163, 1, 'Close', 'Close', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(164, 1, 'Send', 'Send', '2017-05-27 02:32:43', '2017-05-27 02:32:43'),
(165, 1, 'Update with previous balance. Enter (-) amount for decrease limit', 'Update with previous balance. Enter (-) amount for decrease limit', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(166, 1, 'Update Image', 'Update Image', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(167, 1, 'Coverage', 'Coverage', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(168, 1, 'ISO Code', 'ISO Code', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(169, 1, 'Country Code', 'Country Code', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(170, 1, 'Tariff', 'Tariff', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(171, 1, 'Live', 'Live', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(172, 1, 'Offline', 'Offline', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(173, 1, 'Create New Ticket', 'Create New Ticket', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(174, 1, 'Ticket For Client', 'Ticket For Client', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(175, 1, 'Department', 'Department', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(176, 1, 'Create Ticket', 'Create Ticket', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(177, 1, 'Create SMS Template', 'Create SMS Template', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(178, 1, 'SMS Templates', 'SMS Templates', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(179, 1, 'Select Template', 'Select Template', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(180, 1, 'Template Name', 'Template Name', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(181, 1, 'From', 'From', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(182, 1, 'Insert Merge Filed', 'Insert Merge Filed', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(183, 1, 'Select Merge Field', 'Select Merge Field', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(184, 1, 'Phone Number', 'Phone Number', '2017-05-27 02:32:44', '2017-05-27 02:32:44'),
(185, 1, 'Add New', 'Add New', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(186, 1, 'Tickets', 'Tickets', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(187, 1, 'Invoices History', 'Invoices History', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(188, 1, 'Tickets History', 'Tickets History', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(189, 1, 'SMS Success History', 'SMS Success History', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(190, 1, 'SMS History By Date', 'SMS History By Date', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(191, 1, 'Recent 5 Invoices', 'Recent 5 Invoices', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(192, 1, 'Recent 5 Support Tickets', 'Recent 5 Support Tickets', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(193, 1, 'Edit Invoice', 'Edit Invoice', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(194, 1, 'View Invoice', 'View Invoice', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(195, 1, 'Send Invoice', 'Send Invoice', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(196, 1, 'Access Role', 'Access Role', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(197, 1, 'Super Admin', 'Super Admin', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(198, 1, 'Personal Details', 'Personal Details', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(199, 1, 'Unique For every User', 'Unique For every User', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(200, 1, 'Email Templates', 'Email Templates', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(201, 1, 'Manage Email Template', 'Manage Email Template', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(202, 1, 'Export and Import Clients', 'Export and Import Clients', '2017-05-27 02:32:45', '2017-05-27 02:32:45'),
(203, 1, 'Export Clients', 'Export Clients', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(204, 1, 'Export Clients as CSV', 'Export Clients as CSV', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(205, 1, 'Sample File', 'Sample File', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(206, 1, 'Download Sample File', 'Download Sample File', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(207, 1, 'Import Clients', 'Import Clients', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(208, 1, 'It will take few minutes. Please do not reload the page', 'It will take few minutes. Please do not reload the page', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(209, 1, 'Import', 'Import', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(210, 1, 'Reset My Password', 'Reset My Password', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(211, 1, 'Back To Sign in', 'Back To Sign in', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(212, 1, 'Invoice No', 'Invoice No', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(213, 1, 'Invoice', 'Invoice', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(214, 1, 'Invoice To', 'Invoice To', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(215, 1, 'Printable Version', 'Printable Version', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(216, 1, 'Invoice Status', 'Invoice Status', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(217, 1, 'Subtotal', 'Subtotal', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(218, 1, 'Grand Total', 'Grand Total', '2017-05-27 02:32:46', '2017-05-27 02:32:46'),
(219, 1, 'Amount Due', 'Amount Due', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(220, 1, 'Add Language', 'Add Language', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(221, 1, 'Flag', 'Flag', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(222, 1, 'All Languages', 'All Languages', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(223, 1, 'Translate', 'Translate', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(224, 1, 'Language Manage', 'Language Manage', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(225, 1, 'Language Name', 'Language Name', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(226, 1, 'English To', 'English To', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(227, 1, 'English To', 'English To', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(228, 1, 'Localization', 'Localization', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(229, 1, 'Date Format', 'Date Format', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(230, 1, 'Timezone', 'Timezone', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(231, 1, 'Default Language', 'Default Language', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(232, 1, 'Current Code', 'Current Code', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(233, 1, 'Current Symbol', 'Current Symbol', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(234, 1, 'Default Country', 'Default Country', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(235, 1, 'Manage Administrator', 'Manage Administrator', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(236, 1, 'Manage Coverage', 'Manage Coverage', '2017-05-27 02:32:47', '2017-05-27 02:32:47'),
(237, 1, 'Cost for per SMS', 'Cost for per SMS', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(238, 1, 'SMS Gateway Manage', 'SMS Gateway Manage', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(239, 1, 'Manage Plan Feature', 'Manage Plan Feature', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(240, 1, 'SMS Plan Features', 'SMS Plan Features', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(241, 1, 'Update Feature', 'Update Feature', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(242, 1, 'Manage SMS Price Plan', 'Manage SMS Price Plan', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(243, 1, 'SMS Price Plan', 'SMS Price Plan', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(244, 1, 'Update Plan', 'Update Plan', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(245, 1, 'Msisdn', 'Msisdn', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(246, 1, 'Account Sid', 'Account Sid', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(247, 1, 'SMS Api', 'SMS Api', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(248, 1, 'SMS Api User name', 'SMS Api User name', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(249, 1, 'Auth Token', 'Auth Token', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(250, 1, 'SMS Api key', 'SMS Api key', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(251, 1, 'SMS Api Password', 'SMS Api Password', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(252, 1, 'Extra Value', 'Extra Value', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(253, 1, 'Schedule SMS', 'Schedule SMS', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(254, 1, 'Manage SMS Template', 'Manage SMS Template', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(255, 1, 'Edit Administrator Role', 'Edit Administrator Role', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(256, 1, 'Manage Payment Gateway', 'Manage Payment Gateway', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(257, 1, 'Publishable Key', 'Publishable Key', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(258, 1, 'Bank Details', 'Bank Details', '2017-05-27 02:32:48', '2017-05-27 02:32:48'),
(259, 1, 'Api Login ID', 'Api Login ID', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(260, 1, 'Secret_Key_Signature', 'Secret Key/Signature', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(261, 1, 'Transaction Key', 'Transaction Key', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(262, 1, 'Payment Gateways', 'Payment Gateways', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(263, 1, 'Send Bulk SMS', 'Send Bulk SMS', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(264, 1, 'Bulk SMS', 'Bulk SMS', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(265, 1, 'After click on Send button, do not refresh your browser', 'After click on Send button, do not refresh your browser', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(266, 1, 'Schedule Time', 'Schedule Time', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(267, 1, 'Import Numbers', 'Import Numbers', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(268, 1, 'Set Rules', 'Set Rules', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(269, 1, 'Check All', 'Check All', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(270, 1, 'Send SMS From File', 'Send SMS From File', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(271, 1, 'Schedule SMS From File', 'Schedule SMS From File', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(272, 1, 'SMS History', 'SMS History', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(273, 1, 'Add Price Plan', 'Add Price Plan', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(274, 1, 'Sender ID Management', 'Sender ID Management', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(275, 1, 'Support Department', 'Support Department', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(276, 1, 'Department Name', 'Department Name', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(277, 1, 'Department Email', 'Department Email', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(278, 1, 'System Settings', 'System Settings', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(279, 1, 'Language Settings', 'Language Settings', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(280, 1, 'SMS API Info', 'SMS API Info', '2017-05-27 02:32:49', '2017-05-27 02:32:49'),
(281, 1, 'SMS API URL', 'SMS API URL', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(282, 1, 'Generate New', 'Generate New', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(283, 1, 'SMS API Details', 'SMS API Details', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(284, 1, 'Add Gateway', 'Add Gateway', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(285, 1, 'Two Way', 'Two Way', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(286, 1, 'Send By', 'Send By', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(287, 1, 'Sender', 'Sender', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(288, 1, 'Receiver', 'Receiver', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(289, 1, 'Inbox', 'Inbox', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(290, 1, 'Add Feature', 'Add Feature', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(291, 1, 'View Features', 'View Features', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(292, 1, 'Create Template', 'Create Template', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(293, 1, 'Application Name', 'Application Name', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(294, 1, 'Application Title', 'Application Title', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(295, 1, 'System Email', 'System Email', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(296, 1, 'Remember: All Email Going to the Receiver from this Email', 'Remember: All Email Going to the Receiver from this Email', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(297, 1, 'Footer Text', 'Footer Text', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(298, 1, 'Application Logo', 'Application Logo', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(299, 1, 'Application Favicon', 'Application Favicon', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(300, 1, 'API Permission', 'API Permission', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(301, 1, 'Allow Client Registration', 'Allow Client Registration', '2017-05-27 02:32:50', '2017-05-27 02:32:50'),
(302, 1, 'Client Registration Verification', 'Client Registration Verification', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(303, 1, 'Email Gateway', 'Email Gateway', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(304, 1, 'Server Default', 'Server Default', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(305, 1, 'SMTP', 'SMTP', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(306, 1, 'Host Name', 'Host Name', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(307, 1, 'Port', 'Port', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(308, 1, 'Secure', 'Secure', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(309, 1, 'TLS', 'TLS', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(310, 1, 'SSL', 'SSL', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(311, 1, 'Mark As', 'Mark As', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(312, 1, 'Preview', 'Preview', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(313, 1, 'PDF', 'PDF', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(314, 1, 'Print', 'Print', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(315, 1, 'Ticket Management', 'Ticket Management', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(316, 1, 'Ticket Details', 'Ticket Details', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(317, 1, 'Ticket Discussion', 'Ticket Discussion', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(318, 1, 'Ticket Files', 'Ticket Files', '2017-05-27 02:32:51', '2017-05-27 02:32:51'),
(319, 1, 'Created Date', 'Created Date', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(320, 1, 'Created By', 'Created By', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(321, 1, 'Department', 'Department', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(322, 1, 'Closed By', 'Closed By', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(323, 1, 'File Title', 'File Title', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(324, 1, 'Select File', 'Select File', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(325, 1, 'Files', 'Files', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(326, 1, 'Size', 'Size', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(327, 1, 'Upload By', 'Upload By', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(328, 1, 'Upload', 'Upload', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(329, 1, 'Dashboard', 'Dashboard', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(330, 1, 'Settings', 'Settings', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(331, 1, 'Logout', 'Logout', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(332, 1, 'Recent 5 Unpaid Invoices', 'Recent 5 Unpaid Invoices', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(333, 1, 'See All Invoices', 'See All Invoices', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(334, 1, 'Recent 5 Pending Tickets', 'Recent 5 Pending Tickets', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(335, 1, 'See All Tickets', 'See All Tickets', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(336, 1, 'Update Profile', 'Update Profile', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(337, 1, 'You do not have permission to view this page', 'You do not have permission to view this page', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(338, 1, 'This Option is Disable In Demo Mode', 'This Option is Disable In Demo Mode', '2017-05-27 02:32:52', '2017-05-27 02:32:52'),
(339, 1, 'User name already exist', 'User name already exist', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(340, 1, 'Email already exist', 'Email already exist', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(341, 1, 'Both password does not match', 'Both password does not match', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(342, 1, 'Administrator added successfully', 'Administrator added successfully', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(343, 1, 'Administrator not found', 'Administrator not found', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(344, 1, 'Administrator updated successfully', 'Administrator updated successfully', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(345, 1, 'Administrator have support tickets. First delete support ticket', 'Administrator have support tickets. First delete support ticket', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(346, 1, 'Administrator have SMS Log. First delete all sms', 'Administrator have SMS Log. First delete all sms', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(347, 1, 'Administrator created invoice. First delete all invoice', 'Administrator created invoice. First delete all invoice', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(348, 1, 'Administrator delete successfully', 'Administrator delete successfully', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(349, 1, 'Administrator Role added successfully', 'Administrator Role added successfully', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(350, 1, 'Administrator Role already exist', 'Administrator Role already exist', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(351, 1, 'Administrator Role updated successfully', 'Administrator Role updated successfully', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(352, 1, 'Administrator Role info not found', 'Administrator Role info not found', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(353, 1, 'Permission not assigned', 'Permission not assigned', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(354, 1, 'Permission Updated', 'Permission Updated', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(355, 1, 'An Administrator contain this role', 'An Administrator contain this role', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(356, 1, 'Administrator role deleted successfully', 'Administrator role deleted successfully', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(357, 1, 'Invalid User name or Password', 'Invalid User name or Password', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(358, 1, 'Please Check your Email Settings', 'Please Check your Email Settings', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(359, 1, 'Password Reset Successfully. Please check your email', 'Password Reset Successfully. Please check your email', '2017-05-27 02:32:53', '2017-05-27 02:32:53'),
(360, 1, 'Your Password Already Reset. Please Check your email', 'Your Password Already Reset. Please Check your email', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(361, 1, 'Sorry There is no registered user with this email address', 'Sorry There is no registered user with this email address', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(362, 1, 'A New Password Generated. Please Check your email.', 'A New Password Generated. Please Check your email.', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(363, 1, 'Sorry Password reset Token expired or not exist, Please try again.', 'Sorry Password reset Token expired or not exist, Please try again.', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(364, 1, 'Client Added Successfully But Email Not Send', 'Client Added Successfully But Email Not Send', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(365, 1, 'Client Added Successfully', 'Client Added Successfully', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(366, 1, 'Client info not found', 'Client info not found', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(367, 1, 'Limit updated successfully', 'Limit updated successfully', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(368, 1, 'Image updated successfully', 'Image updated successfully', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(369, 1, 'Please try again', 'Please try again', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(370, 1, 'Client updated successfully', 'Client updated successfully', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(371, 1, 'SMS gateway not active', 'SMS gateway not active', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(372, 1, 'Please check sms history', 'Please check sms history', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(373, 1, 'Insert Valid Excel or CSV file', 'Insert Valid Excel or CSV file', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(374, 1, 'Client imported successfully', 'Client imported successfully', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(375, 1, 'Client Group added successfully', 'Client Group added successfully', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(376, 1, 'Client Group updated successfully', 'Client Group updated successfully', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(377, 1, 'Client Group not found', 'Client Group not found', '2017-05-27 02:32:54', '2017-05-27 02:32:54'),
(378, 1, 'This Group exist in a client', 'This Group exist in a client', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(379, 1, 'Client group deleted successfully', 'Client group deleted successfully', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(380, 1, 'Invoice not found', 'Invoice not found', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(381, 1, 'Logout Successfully', 'Logout Successfully', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(382, 1, 'Profile Updated Successfully', 'Profile Updated Successfully', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(383, 1, 'Upload an Image', 'Upload an Image', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(384, 1, 'Password Change Successfully', 'Password Change Successfully', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(385, 1, 'Current Password Does Not Match', 'Current Password Does Not Match', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(386, 1, 'Select a Customer', 'Select a Customer', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(387, 1, 'Invoice Created date is required', 'Invoice Created date is required', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(388, 1, 'Invoice Paid date is required', 'Invoice Paid date is required', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(389, 1, 'Date Parsing Error', 'Date Parsing Error', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(390, 1, 'Invoice Due date is required', 'Invoice Due date is required', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(391, 1, 'At least one item is required', 'At least one item is required', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(392, 1, 'Invoice Updated Successfully', 'Invoice Updated Successfully', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(393, 1, 'Invoice Marked as Paid', 'Invoice Marked as Paid', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(394, 1, 'Invoice Marked as Unpaid', 'Invoice Marked as Unpaid', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(395, 1, 'Invoice Marked as Partially Paid', 'Invoice Marked as Partially Paid', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(396, 1, 'Invoice Marked as Cancelled', 'Invoice Marked as Cancelled', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(397, 1, 'Invoice Send Successfully', 'Invoice Send Successfully', '2017-05-27 02:32:55', '2017-05-27 02:32:55'),
(398, 1, 'Invoice deleted successfully', 'Invoice deleted successfully', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(399, 1, 'Stop Recurring Invoice Successfully', 'Stop Recurring Invoice Successfully', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(400, 1, 'Invoice Created Successfully', 'Invoice Created Successfully', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(401, 1, 'Reseller Panel', 'Reseller Panel', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(402, 1, 'Captcha In Admin Login', 'Captcha In Admin Login', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(403, 1, 'Captcha In Client Login', 'Captcha In Client Login', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(404, 1, 'Captcha In Client Registration', 'Captcha In Client Registration', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(405, 1, 'reCAPTCHA Site Key', 'reCAPTCHA Site Key', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(406, 1, 'reCAPTCHA Secret Key', 'reCAPTCHA Secret Key', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(407, 1, 'Registration Successful', 'Registration Successful', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(408, 1, 'Payment gateway required', 'Payment gateway required', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(409, 1, 'Cancelled the Payment', 'Cancelled the Payment', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(410, 1, 'Invoice paid successfully', 'Invoice paid successfully', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(411, 1, 'Purchase successfully.Wait for administrator response', 'Purchase successfully.Wait for administrator response', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(412, 1, 'SMS Not Found', 'SMS Not Found', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(413, 1, 'SMS info deleted successfully', 'SMS info deleted successfully', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(414, 1, 'Setting Update Successfully', 'Setting Update Successfully', '2017-05-27 02:32:56', '2017-05-27 02:32:56'),
(415, 1, 'Email Template Not Found', 'Email Template Not Found', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(416, 1, 'Email Template Update Successfully', 'Email Template Update Successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(417, 1, 'Payment Gateway not found', 'Payment Gateway not found', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(418, 1, 'Payment Gateway update successfully', 'Payment Gateway update successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(419, 1, 'Language Already Exist', 'Language Already Exist', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(420, 1, 'Language Added Successfully', 'Language Added Successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(421, 1, 'Language Translate Successfully', 'Language Translate Successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(422, 1, 'Language not found', 'Language not found', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(423, 1, 'Language updated Successfully', 'Language updated Successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(424, 1, 'Can not delete active language', 'Can not delete active language', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(425, 1, 'Language deleted successfully', 'Language deleted successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(426, 1, 'Information not found', 'Information not found', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(427, 1, 'Coverage updated successfully', 'Coverage updated successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(428, 1, 'Sender Id added successfully', 'Sender Id added successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(429, 1, 'Sender Id not found', 'Sender Id not found', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(430, 1, 'Sender id updated successfully', 'Sender id updated successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(431, 1, 'Sender id deleted successfully', 'Sender id deleted successfully', '2017-05-27 02:32:57', '2017-05-27 02:32:57'),
(432, 1, 'Plan already exist', 'Plan already exist', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(433, 1, 'Plan added successfully', 'Plan added successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(434, 1, 'Plan not found', 'Plan not found', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(435, 1, 'Plan updated successfully', 'Plan updated successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(436, 1, 'Plan features added successfully', 'Plan features added successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(437, 1, 'Plan feature not found', 'Plan feature not found', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(438, 1, 'Feature already exist', 'Feature already exist', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(439, 1, 'Feature updated successfully', 'Feature updated successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(440, 1, 'Plan feature deleted successfully', 'Plan feature deleted successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(441, 1, 'Price Plan deleted successfully', 'Price Plan deleted successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(442, 1, 'Gateway already exist', 'Gateway already exist', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(443, 1, 'Custom gateway added successfully', 'Custom gateway added successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(444, 1, 'Parameter or Value is empty', 'Parameter or Value is empty', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(445, 1, 'Gateway information not found', 'Gateway information not found', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(446, 1, 'Gateway name required', 'Gateway name required', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(447, 1, 'Custom gateway updated successfully', 'Custom gateway updated successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(448, 1, 'Client are registered with this gateway', 'Client are registered with this gateway', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(449, 1, 'Gateway deleted successfully', 'Gateway deleted successfully', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(450, 1, 'Delete option disable for this gateway', 'Delete option disable for this gateway', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(451, 1, 'SMS added in queue and will deliver one by one', 'SMS added in queue and will deliver one by one', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(452, 1, 'Insert Valid Excel or CSV file', 'Insert Valid Excel or CSV file', '2017-05-27 02:32:58', '2017-05-27 02:32:58'),
(453, 1, 'SMS are scheduled. Deliver in correct time', 'SMS are scheduled. Deliver in correct time', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(454, 1, 'Template already exist', 'Template already exist', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(455, 1, 'Sms template created successfully', 'Sms template created successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(456, 1, 'Sms template not found', 'Sms template not found', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(457, 1, 'Sms template updated successfully', 'Sms template updated successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(458, 1, 'Sms template delete successfully', 'Sms template delete successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(459, 1, 'API information updated successfully', 'API information updated successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(460, 1, 'Invalid Access', 'Invalid Access', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(461, 1, 'Invalid Captcha', 'Invalid Captcha', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(462, 1, 'Invalid Request', 'Invalid Request', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(463, 1, 'Verification code send successfully. Please check your email', 'Verification code send successfully. Please check your email', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(464, 1, 'Something wrong, Please contact with your provider', 'Something wrong, Please contact with your provider', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(465, 1, 'Verification code not found', 'Verification code not found', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(466, 1, 'Department Already exist', 'Department Already exist', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(467, 1, 'Department Added Successfully', 'Department Added Successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(468, 1, 'Department Updated Successfully', 'Department Updated Successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(469, 1, 'Support Ticket Created Successfully But Email Not Send', 'Support Ticket Created Successfully But Email Not Send', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(470, 1, 'Support Ticket Created Successfully', 'Support Ticket Created Successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(471, 1, 'Basic Info Update Successfully', 'Basic Info Update Successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(472, 1, 'Ticket Reply Successfully But Email Not Send', 'Ticket Reply Successfully But Email Not Send', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(473, 1, 'Ticket Reply Successfully', 'Ticket Reply Successfully', '2017-05-27 02:32:59', '2017-05-27 02:32:59'),
(474, 1, 'File Uploaded Successfully', 'File Uploaded Successfully', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(475, 1, 'Please Upload a File', 'Please Upload a File', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(476, 1, 'File Deleted Successfully', 'File Deleted Successfully', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(477, 1, 'Ticket File not found', 'Ticket File not found', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(478, 1, 'Ticket Deleted Successfully', 'Ticket Deleted Successfully', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(479, 1, 'Ticket info not found', 'Ticket info not found', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(480, 1, 'Department Deleted Successfully', 'Department Deleted Successfully', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(481, 1, 'There Have no Department For Delete', 'There Have no Department For Delete', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(482, 1, 'You do not have enough sms balance', 'You do not have enough sms balance', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(483, 1, 'SMS gateway not active.Contact with Provider', 'SMS gateway not active.Contact with Provider', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(484, 1, 'Sender ID required', 'Sender ID required', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(485, 1, 'Request send successfully', 'Request send successfully', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(486, 1, 'This Sender ID have Blocked By Administrator', 'This Sender ID have Blocked By Administrator', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(487, 1, 'Phone Number Coverage are not active', 'Phone Number Coverage are not active', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(488, 1, 'SMS plan not found', 'SMS plan not found', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(489, 1, 'Schedule feature not supported', 'Schedule feature not supported', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(490, 1, 'Need Account', 'Need Account', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(491, 1, 'Sign up', 'Sign up', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(492, 1, 'here', 'here', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(493, 1, 'User Registration', 'User Registration', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(494, 1, 'Already have an Account', 'Already have an Account', '2017-05-27 02:33:00', '2017-05-27 02:33:00'),
(495, 1, 'Request New Sender ID', 'Request New Sender ID', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(496, 1, 'Purchase Now', 'Purchase Now', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(497, 1, 'Purchase SMS Plan', 'Purchase SMS Plan', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(498, 1, 'Select Payment Method', 'Select Payment Method', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(499, 1, 'Pay with Credit Card', 'Pay with Credit Card', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(500, 1, 'User Registration Verification', 'User Registration Verification', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(501, 1, 'Verify Your Account', 'Verify Your Account', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(502, 1, 'Send Verification Email', 'Send Verification Email', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(503, 1, 'Pay', 'Pay', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(504, 1, 'Pay Invoice', 'Pay Invoice', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(505, 1, 'Reply Ticket', 'Reply Ticket', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(506, 1, 'Whoops! Page Not Found, Go To', 'Whoops! Page Not Found, Go To', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(507, 1, 'Home Page', 'Home Page', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(508, 1, 'Error', 'Error', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(509, 1, 'Client contain in', 'Client contain in', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(510, 1, 'Client sms limit not empty', 'Client sms limit not empty', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(511, 1, 'This client have some customer', 'This client have some customer', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(512, 1, 'Client delete successfully', 'Client delete successfully', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(513, 1, 'Client Group is empty', 'Client Group is empty', '2017-05-27 02:33:01', '2017-05-27 02:33:01'),
(514, 1, 'Country flag required', 'Country flag required', '2017-05-27 02:33:02', '2017-05-27 02:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `sys_payment_gateways`
--

CREATE TABLE `sys_payment_gateways` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extra_value` text COLLATE utf8mb4_unicode_ci,
  `password` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sys_payment_gateways`
--

INSERT INTO `sys_payment_gateways` (`id`, `name`, `value`, `settings`, `extra_value`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Paypal', 'apiemail@paypal.com', 'paypal', 'api_secret', 'api_password', 'Active', '2017-05-27 02:32:22', '2017-05-27 02:32:22'),
(2, 'Stripe', 'pk_test_ARblMczqDw61NusMMs7o1RVK', 'stripe', 'sk_test_BQokikJOvBiI2HlWgH4olfQ2', NULL, 'Active', '2017-05-27 02:32:22', '2017-05-27 02:32:22'),
(3, 'Bank', 'Make a Payment to Our Bank Account &lt;br&gt;Bank Name: Bank Name &lt;br&gt;Account Name: Account Holder Name &lt;br&gt;Account Number: Account Number &lt;br&gt;', 'manualpayment', '', NULL, 'Active', '2017-05-27 02:32:22', '2017-05-27 02:32:22');

-- --------------------------------------------------------

--
-- Table structure for table `sys_schedule_sms`
--

CREATE TABLE `sys_schedule_sms` (
  `id` int(10) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `sender` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `original_msg` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `encrypt_msg` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `use_gateway` int(11) NOT NULL,
  `ip` text COLLATE utf8mb4_unicode_ci,
  `submit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_sender_id_management`
--

CREATE TABLE `sys_sender_id_management` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cl_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `status` enum('pending','block','unblock') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'block',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_sms_gateways`
--

CREATE TABLE `sys_sms_gateways` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_link` text COLLATE utf8mb4_unicode_ci,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schedule` enum('No','Yes') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `custom` enum('No','Yes') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `two_way` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sys_sms_gateways`
--

INSERT INTO `sys_sms_gateways` (`id`, `name`, `api_link`, `username`, `password`, `api_id`, `schedule`, `custom`, `status`, `two_way`, `created_at`, `updated_at`) VALUES
(1, 'Twilio', '', 'username', 'auth_token', '', 'Yes', 'No', 'Active', 'Yes', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(2, 'Clickatell', 'http://api.clickatell.com', 'username', 'auth_token', '3537357', 'Yes', 'No', 'Active', 'Yes', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(3, 'Text Local', 'http://api.textlocal.in/send/', 'username', 'apihash', '', 'Yes', 'No', 'Active', 'Yes', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(4, 'Top10sms', 'http://trans.websmsapp.com/API/', 'username', 'api_key', '', 'Yes', 'No', 'Active', 'No', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(5, 'msg91', 'http://api.msg91.com/api/sendhttp.php', 'username', 'auth_key', '', 'Yes', 'No', 'Active', 'No', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(6, 'SMSGlobal', 'http://www.smsglobal.com/http-api.php', 'username', 'Password', '', 'Yes', 'No', 'Active', 'Yes', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(7, 'Bulk SMS', 'http://bulksms.2way.co.za', 'username', 'Password', '', 'Yes', 'No', 'Active', 'Yes', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(8, 'Nexmo', 'https://rest.nexmo.com/sms/json', 'api_key', 'api_secret', '', 'Yes', 'No', 'Active', 'Yes', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(9, 'Route SMS', 'http://smsplus1.routesms.com:8080', 'username', 'Password', '', 'Yes', 'No', 'Active', 'No', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(10, 'SMSKaufen', 'http://www.smskaufen.com/sms/gateway/sms.php', 'API User Name', 'SMS API Key', '', 'Yes', 'No', 'Active', 'No', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(11, 'Kapow', 'http://www.kapow.co.uk/scripts/sendsms.php', 'username', 'Password', '', 'Yes', 'No', 'Active', 'No', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(12, 'Zang', '', 'account_sid', 'auth_token', '', 'Yes', 'No', 'Active', 'No', '2017-05-27 02:32:21', '2017-05-27 02:32:21'),
(13, 'InfoBip', 'https://api.infobip.com/sms/1/text/single', 'username', 'Password', '', 'Yes', 'No', 'Active', 'Yes', '2017-05-27 02:32:22', '2017-05-27 02:32:22'),
(14, 'RANNH', 'http://rannh.com/sendsms.php', 'username', 'Password', '', 'Yes', 'No', 'Active', 'No', '2017-05-27 02:32:22', '2017-05-27 02:32:22');

-- --------------------------------------------------------

--
-- Table structure for table `sys_sms_history`
--

CREATE TABLE `sys_sms_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `sender` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `use_gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_sms_inbox`
--

CREATE TABLE `sys_sms_inbox` (
  `id` int(10) UNSIGNED NOT NULL,
  `msg_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `original_msg` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `encrypt_msg` text COLLATE utf8mb4_unicode_ci,
  `status` text COLLATE utf8mb4_unicode_ci,
  `ip` text COLLATE utf8mb4_unicode_ci,
  `send_by` enum('sender','receiver') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_sms_plan_feature`
--

CREATE TABLE `sys_sms_plan_feature` (
  `id` int(10) UNSIGNED NOT NULL,
  `pid` int(11) NOT NULL,
  `feature_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_sms_price_plan`
--

CREATE TABLE `sys_sms_price_plan` (
  `id` int(10) UNSIGNED NOT NULL,
  `plan_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `popular` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_sms_templates`
--

CREATE TABLE `sys_sms_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `cl_id` int(11) NOT NULL,
  `template_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `global` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_sms_transaction`
--

CREATE TABLE `sys_sms_transaction` (
  `id` int(10) UNSIGNED NOT NULL,
  `cl_id` int(11) NOT NULL,
  `amount` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_support_departments`
--

CREATE TABLE `sys_support_departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `show` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_tickets`
--

CREATE TABLE `sys_tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `did` int(11) NOT NULL,
  `cl_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pending','Answered','Customer Reply','Closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `admin` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `replyby` text COLLATE utf8mb4_unicode_ci,
  `closed_by` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_ticket_files`
--

CREATE TABLE `sys_ticket_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `cl_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `admin` text COLLATE utf8mb4_unicode_ci,
  `file_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sys_ticket_replies`
--

CREATE TABLE `sys_ticket_replies` (
  `id` int(10) UNSIGNED NOT NULL,
  `tid` int(11) NOT NULL,
  `cl_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `admin` text COLLATE utf8mb4_unicode_ci,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_reserved_at_index` (`queue`,`reserved_at`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_admins`
--
ALTER TABLE `sys_admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_admin_role`
--
ALTER TABLE `sys_admin_role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_admin_role_perm`
--
ALTER TABLE `sys_admin_role_perm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_app_config`
--
ALTER TABLE `sys_app_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_clients`
--
ALTER TABLE `sys_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_client_groups`
--
ALTER TABLE `sys_client_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_custom_sms_gateways`
--
ALTER TABLE `sys_custom_sms_gateways`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_email_templates`
--
ALTER TABLE `sys_email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_int_country_codes`
--
ALTER TABLE `sys_int_country_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_invoices`
--
ALTER TABLE `sys_invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_invoice_items`
--
ALTER TABLE `sys_invoice_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_language`
--
ALTER TABLE `sys_language`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_language_data`
--
ALTER TABLE `sys_language_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_payment_gateways`
--
ALTER TABLE `sys_payment_gateways`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_schedule_sms`
--
ALTER TABLE `sys_schedule_sms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_sender_id_management`
--
ALTER TABLE `sys_sender_id_management`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_sms_gateways`
--
ALTER TABLE `sys_sms_gateways`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_sms_history`
--
ALTER TABLE `sys_sms_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_sms_inbox`
--
ALTER TABLE `sys_sms_inbox`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_sms_plan_feature`
--
ALTER TABLE `sys_sms_plan_feature`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_sms_price_plan`
--
ALTER TABLE `sys_sms_price_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_sms_templates`
--
ALTER TABLE `sys_sms_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_sms_transaction`
--
ALTER TABLE `sys_sms_transaction`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_support_departments`
--
ALTER TABLE `sys_support_departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_tickets`
--
ALTER TABLE `sys_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_ticket_files`
--
ALTER TABLE `sys_ticket_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_ticket_replies`
--
ALTER TABLE `sys_ticket_replies`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
--
-- AUTO_INCREMENT for table `sys_admins`
--
ALTER TABLE `sys_admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `sys_admin_role`
--
ALTER TABLE `sys_admin_role`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_admin_role_perm`
--
ALTER TABLE `sys_admin_role_perm`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_app_config`
--
ALTER TABLE `sys_app_config`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT for table `sys_clients`
--
ALTER TABLE `sys_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_client_groups`
--
ALTER TABLE `sys_client_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_custom_sms_gateways`
--
ALTER TABLE `sys_custom_sms_gateways`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_email_templates`
--
ALTER TABLE `sys_email_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `sys_int_country_codes`
--
ALTER TABLE `sys_int_country_codes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;
--
-- AUTO_INCREMENT for table `sys_invoices`
--
ALTER TABLE `sys_invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_invoice_items`
--
ALTER TABLE `sys_invoice_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_language`
--
ALTER TABLE `sys_language`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `sys_language_data`
--
ALTER TABLE `sys_language_data`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=515;
--
-- AUTO_INCREMENT for table `sys_payment_gateways`
--
ALTER TABLE `sys_payment_gateways`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `sys_schedule_sms`
--
ALTER TABLE `sys_schedule_sms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_sender_id_management`
--
ALTER TABLE `sys_sender_id_management`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_sms_gateways`
--
ALTER TABLE `sys_sms_gateways`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `sys_sms_history`
--
ALTER TABLE `sys_sms_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_sms_inbox`
--
ALTER TABLE `sys_sms_inbox`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_sms_plan_feature`
--
ALTER TABLE `sys_sms_plan_feature`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_sms_price_plan`
--
ALTER TABLE `sys_sms_price_plan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_sms_templates`
--
ALTER TABLE `sys_sms_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_sms_transaction`
--
ALTER TABLE `sys_sms_transaction`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_support_departments`
--
ALTER TABLE `sys_support_departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_tickets`
--
ALTER TABLE `sys_tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_ticket_files`
--
ALTER TABLE `sys_ticket_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sys_ticket_replies`
--
ALTER TABLE `sys_ticket_replies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

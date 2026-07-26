-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2026 at 05:50 PM
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
-- Database: `smartbooking_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_beauty_requests`
--

CREATE TABLE `ai_beauty_requests` (
  `id` bigint(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `beauty_area` enum('lips','eyebrows') NOT NULL,
  `color_value` varchar(100) NOT NULL,
  `service_style` varchar(100) DEFAULT NULL,
  `finish_type` varchar(60) DEFAULT NULL,
  `brow_shape` varchar(60) DEFAULT NULL,
  `thickness` varchar(40) DEFAULT NULL,
  `instructions` varchar(600) DEFAULT NULL,
  `used_customer_photo` tinyint(1) NOT NULL DEFAULT 0,
  `consent_given` tinyint(1) NOT NULL DEFAULT 0,
  `provider` varchar(40) NOT NULL DEFAULT 'demo',
  `model_name` varchar(100) DEFAULT NULL,
  `status` enum('processing','completed','failed') NOT NULL DEFAULT 'processing',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_beauty_results`
--

CREATE TABLE `ai_beauty_results` (
  `id` bigint(20) NOT NULL,
  `request_id` bigint(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `result_number` tinyint(4) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `is_selected` tinyint(1) NOT NULL DEFAULT 0,
  `is_saved` tinyint(1) NOT NULL DEFAULT 1,
  `selected_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_daily_limit_resets`
--

CREATE TABLE `ai_daily_limit_resets` (
  `id` bigint(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `reset_by` int(11) NOT NULL,
  `reset_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_design_requests`
--

CREATE TABLE `ai_design_requests` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider` varchar(40) NOT NULL DEFAULT 'demo',
  `model_name` varchar(100) DEFAULT NULL,
  `nail_shape` varchar(40) NOT NULL,
  `nail_length` varchar(40) NOT NULL,
  `colors` varchar(180) NOT NULL,
  `design_style` varchar(100) NOT NULL,
  `occasion` varchar(100) DEFAULT NULL,
  `add_ons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`add_ons`)),
  `instructions` varchar(600) DEFAULT NULL,
  `prompt_text` text DEFAULT NULL,
  `status` enum('queued','processing','completed','failed') NOT NULL DEFAULT 'queued',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_design_requests`
--

INSERT INTO `ai_design_requests` (`id`, `customer_id`, `provider`, `model_name`, `nail_shape`, `nail_length`, `colors`, `design_style`, `occasion`, `add_ons`, `instructions`, `prompt_text`, `status`, `error_message`, `created_at`, `completed_at`) VALUES
(1, 4, 'demo', NULL, 'Round', 'Medium', 'Green', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Glitter\",\"Floral accents\"]', NULL, NULL, 'failed', 'Python AI service is offline: Failed to connect to 127.0.0.1 port 5000 after 2060 ms: Couldn\'t connect to server', '2026-07-26 03:50:35', NULL),
(2, 4, 'demo', NULL, 'Almond', 'Medium', 'white', 'Floral', 'Everyday', '[\"Gold foil\",\"Glitter\"]', NULL, NULL, 'failed', 'Python AI service is offline: Failed to connect to 127.0.0.1 port 5000 after 2025 ms: Couldn\'t connect to server', '2026-07-26 03:52:02', NULL),
(3, 4, 'demo', 'demo-preview', 'Round', 'Medium', 'Green', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Glitter\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: Green. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil, Glitter. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:14:12', '2026-07-26 04:14:12'),
(4, 4, 'demo', NULL, 'Almond', 'Medium', 'Green', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Glitter\"]', NULL, NULL, 'failed', 'OpenAI image request failed: Billing hard limit has been reached.', '2026-07-26 04:30:30', NULL),
(5, 4, 'demo', 'demo-preview', 'Almond', 'Medium', 'Green', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Glitter\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: Green. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil, Glitter. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:39:08', '2026-07-26 04:39:08'),
(6, 4, 'demo', 'preference-preview-v2', 'Almond', 'Medium', 'Green', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Glitter\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: Green. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil, Glitter. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:47:25', '2026-07-26 04:47:25'),
(7, 4, 'demo', 'preference-preview-v2', 'Round', 'Medium', 'Green', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Glitter\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: Green. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil, Glitter. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:47:32', '2026-07-26 04:47:32'),
(8, 4, 'demo', 'preference-preview-v2', 'Round', 'Medium', 'Green', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: Green. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:47:38', '2026-07-26 04:47:39'),
(9, 4, 'demo', 'preference-preview-v2', 'Round', 'Medium', 'Green', 'Classic French', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: Green. Style: Classic French. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:47:48', '2026-07-26 04:47:48'),
(10, 4, 'demo', 'preference-preview-v2', 'Round', 'Medium', 'Green', 'Classic French', 'Everyday', '[\"Chrome finish\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: Green. Style: Classic French. Occasion: Everyday. Add-ons: Chrome finish. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:47:51', '2026-07-26 04:47:51'),
(11, 4, 'demo', 'preference-preview-v2', 'Almond', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[\"Gold foil\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:03', '2026-07-26 04:51:03'),
(12, 4, 'demo', 'preference-preview-v2', 'Almond', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:10', '2026-07-26 04:51:10'),
(13, 4, 'demo', 'preference-preview-v2', 'Stiletto', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Stiletto. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:16', '2026-07-26 04:51:16'),
(14, 4, 'demo', 'preference-preview-v2', 'Coffin', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Coffin. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:24', '2026-07-26 04:51:24'),
(15, 4, 'demo', 'preference-preview-v2', 'Square', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Square. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:28', '2026-07-26 04:51:28'),
(16, 4, 'demo', 'preference-preview-v2', 'Oval', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:33', '2026-07-26 04:51:34'),
(17, 4, 'demo', 'preference-preview-v2', 'Oval', 'Short', 'Blue', 'Floral', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Short. Color palette: Blue. Style: Floral. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:46', '2026-07-26 04:51:46'),
(18, 4, 'demo', 'preference-preview-v2', 'Oval', 'Short', 'Blue', 'Floral', 'Everyday', '[\"Small rhinestones\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Short. Color palette: Blue. Style: Floral. Occasion: Everyday. Add-ons: Small rhinestones. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:51', '2026-07-26 04:51:51'),
(19, 4, 'demo', 'preference-preview-v2', 'Oval', 'Short', 'Blue', 'Floral', 'Everyday', '[\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Short. Color palette: Blue. Style: Floral. Occasion: Everyday. Add-ons: Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 04:51:55', '2026-07-26 04:51:55'),
(20, 4, 'demo', 'preference-preview-v2', 'Almond', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:05:30', '2026-07-26 05:05:30'),
(21, 4, 'demo', 'preference-preview-v2', 'Oval', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:05:57', '2026-07-26 05:05:57'),
(22, 4, 'demo', 'preference-preview-v2', 'Almond', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:09:58', '2026-07-26 05:09:59'),
(23, 4, 'demo', 'preference-preview-v2', 'Almond', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[\"Gold foil\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:10:11', '2026-07-26 05:10:11'),
(24, 4, 'demo', 'preference-preview-v2', 'Oval', 'Long', 'Blue', 'Floral', 'Everyday', '[\"Gold foil\",\"Small rhinestones\",\"Glitter\",\"Chrome finish\",\"Pearls\",\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Long. Color palette: Blue. Style: Floral. Occasion: Everyday. Add-ons: Gold foil, Small rhinestones, Glitter, Chrome finish, Pearls, Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:10:35', '2026-07-26 05:10:35'),
(25, 4, 'demo', 'preference-preview-v2', 'Oval', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:16:14', '2026-07-26 05:16:19'),
(26, 4, 'demo', 'preference-preview-v2', 'Oval', 'Medium', 'Blue', 'Elegant minimalist', 'Everyday', '[\"Gold foil\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Medium. Color palette: Blue. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:16:31', '2026-07-26 05:16:35'),
(27, 4, 'demo', 'preference-preview-v2', 'Oval', 'Medium', 'Green', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Medium. Color palette: Green. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:22:30', '2026-07-26 05:22:35'),
(28, 4, 'demo', 'preference-preview-v2', 'Almond', 'Medium', 'white', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: white. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:27:58', '2026-07-26 05:28:03'),
(29, 4, 'demo', 'preference-preview-v2', 'Round', 'Medium', 'white', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: white. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:28:17', '2026-07-26 05:28:22'),
(30, 4, 'demo', 'preference-preview-v2', 'Round', 'Medium', 'black', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: black. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:28:32', '2026-07-26 05:28:36'),
(31, 4, 'demo', 'preference-preview-v2', 'Round', 'Extra long', 'black', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Extra long. Color palette: black. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 05:29:23', '2026-07-26 05:29:28'),
(32, 4, 'demo', 'preference-preview-v2', 'Ballerina', 'Medium', '#2D5B9F', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Ballerina. Length: Medium. Color palette: #2D5B9F. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:04:07', '2026-07-26 06:04:08'),
(33, 4, 'demo', 'preference-preview-v2', 'Oval', 'Medium', '#2D5B9F', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Medium. Color palette: #2D5B9F. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:04:18', '2026-07-26 06:04:19'),
(34, 4, 'demo', 'preference-preview-v2', 'Squoval', 'Medium', '#2D5B9F', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Squoval. Length: Medium. Color palette: #2D5B9F. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:04:22', '2026-07-26 06:04:23'),
(35, 4, 'demo', 'preference-preview-v2', 'Almond', 'Medium', '#2D5B9F', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Almond. Length: Medium. Color palette: #2D5B9F. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:04:26', '2026-07-26 06:04:27'),
(36, 4, 'demo', 'preference-preview-v2', 'Cutout', 'Medium', '#2D5B9F', 'Elegant minimalist', 'Everyday', '[]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Cutout. Length: Medium. Color palette: #2D5B9F. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:04:30', '2026-07-26 06:04:31'),
(37, 4, 'demo', 'preference-preview-v2', 'Cutout', 'Medium', 'red', 'Elegant minimalist', 'Everyday', '[\"Gold foil\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Cutout. Length: Medium. Color palette: red. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:05:36', '2026-07-26 06:05:37'),
(38, 4, 'demo', 'preference-preview-v2', 'Oval', 'Medium', '#3162F7', 'Elegant minimalist', 'Everyday', '[\"Pearls\",\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Oval. Length: Medium. Color palette: #3162F7. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Pearls, Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:17:01', '2026-07-26 06:17:05'),
(39, 4, 'demo', 'preference-preview-v2', 'Square', 'Medium', 'Gold', 'Elegant minimalist', 'Everyday', '[\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Square. Length: Medium. Color palette: Gold. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:27:40', '2026-07-26 06:27:45'),
(40, 4, 'demo', 'preference-preview-v2', 'Square', 'Medium', 'Gold', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Small rhinestones\",\"Glitter\",\"Chrome finish\",\"Pearls\",\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Square. Length: Medium. Color palette: Gold. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil, Small rhinestones, Glitter, Chrome finish, Pearls, Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:28:59', '2026-07-26 06:29:04'),
(41, 4, 'demo', 'preference-preview-v2', 'Square', 'Medium', 'black', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Small rhinestones\",\"Glitter\",\"Chrome finish\",\"Pearls\",\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Square. Length: Medium. Color palette: black. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil, Small rhinestones, Glitter, Chrome finish, Pearls, Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:29:12', '2026-07-26 06:29:17'),
(42, 4, 'demo', 'preference-preview-v2', 'Square', 'Medium', 'black', 'Elegant minimalist', 'Everyday', '[\"Gold foil\",\"Small rhinestones\",\"Glitter\",\"Chrome finish\",\"Pearls\",\"Floral accents\"]', 'can you add a dragon on it?', 'Create a photorealistic professional nail art design preview. Nail shape: Square. Length: Medium. Color palette: black. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Gold foil, Small rhinestones, Glitter, Chrome finish, Pearls, Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: can you add a dragon on it?. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:29:36', '2026-07-26 06:29:41'),
(43, 4, 'demo', 'preference-preview-v2', 'Square', 'Medium', 'black', 'Elegant minimalist', 'Everyday', '[]', 'can you add a dragon on it?', 'Create a photorealistic professional nail art design preview. Nail shape: Square. Length: Medium. Color palette: black. Style: Elegant minimalist. Occasion: Everyday. Add-ons: no raised decorations. Creative direction: balanced salon-wearable composition. Customer notes: can you add a dragon on it?. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:29:50', '2026-07-26 06:29:55'),
(44, 4, 'demo', 'preference-preview-v2', 'Lipstick', 'Long', '#DBEAFE', 'Elegant minimalist', 'Everyday', '[\"Pearls\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Lipstick. Length: Long. Color palette: #DBEAFE. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Pearls. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:48:07', '2026-07-26 06:48:12'),
(45, 4, 'demo', 'preference-preview-v2', 'Lipstick', 'Long', '#E11D48', 'Elegant minimalist', 'Everyday', '[\"Pearls\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Lipstick. Length: Long. Color palette: #E11D48. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Pearls. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:48:32', '2026-07-26 06:48:37');

-- --------------------------------------------------------

--
-- Table structure for table `ai_generated_designs`
--

CREATE TABLE `ai_generated_designs` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `design_number` tinyint(3) UNSIGNED NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `is_selected` tinyint(1) NOT NULL DEFAULT 0,
  `is_saved` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `selected_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_generated_designs`
--

INSERT INTO `ai_generated_designs` (`id`, `request_id`, `customer_id`, `design_number`, `image_path`, `is_selected`, `is_saved`, `created_at`, `deleted_at`, `selected_at`) VALUES
(1, 3, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-0f3abe6e2f4346ac8b94e7c33084887b.png', 0, 1, '2026-07-26 04:14:12', NULL, NULL),
(2, 3, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-3d987dba16cc45e79e9ca3e976e0bdd9.png', 0, 1, '2026-07-26 04:14:12', NULL, NULL),
(3, 3, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-6dc650fd45fa490a8aaa47f0d179aa8a.png', 0, 1, '2026-07-26 04:14:12', NULL, NULL),
(4, 5, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-228c131b385c4eb7a7aef095f4844065.png', 0, 1, '2026-07-26 04:39:08', NULL, NULL),
(5, 5, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-2dfdea92b4044d19a01c64296a5fb306.png', 0, 1, '2026-07-26 04:39:08', NULL, NULL),
(6, 5, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-d8c77d9ce72845fc80666257c0e4e78e.png', 0, 1, '2026-07-26 04:39:08', NULL, NULL),
(7, 6, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-bfc8fa81cf0f404f9daf95e1615b076c.png', 0, 1, '2026-07-26 04:47:25', NULL, NULL),
(8, 6, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-bda0c15c79d844d2b379fc5fafefdbef.png', 0, 1, '2026-07-26 04:47:25', NULL, NULL),
(9, 6, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-2fbf7db5ea064971b91ff5d4653e68bb.png', 0, 1, '2026-07-26 04:47:25', NULL, NULL),
(10, 7, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-1eeeb41d55074759a93c63cc4315b0f1.png', 0, 1, '2026-07-26 04:47:32', NULL, NULL),
(11, 7, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-a00d557bb25447e0808a5482b9473de1.png', 0, 1, '2026-07-26 04:47:32', NULL, NULL),
(12, 7, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-a5d06137100c4f58b3f04419d5e315d3.png', 0, 1, '2026-07-26 04:47:32', NULL, NULL),
(13, 8, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-9fb862e7730042c3b3536597c4019997.png', 0, 1, '2026-07-26 04:47:39', NULL, NULL),
(14, 8, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-1c6d39d8520b4f9db647459227e3036d.png', 0, 1, '2026-07-26 04:47:39', NULL, NULL),
(15, 8, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-53b903fb26e44b2b95de893edb2bbf46.png', 0, 1, '2026-07-26 04:47:39', NULL, NULL),
(16, 9, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-1c826e47759541069d693fe05f54d656.png', 0, 1, '2026-07-26 04:47:48', NULL, NULL),
(17, 9, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-80423f585b62418a87a322596899e681.png', 0, 1, '2026-07-26 04:47:48', NULL, NULL),
(18, 9, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-b1b20dfbfa264c0192c7d0aaede56eec.png', 0, 1, '2026-07-26 04:47:48', NULL, NULL),
(19, 10, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-27815244d18a45729ac1b18c4d44b60a.png', 0, 1, '2026-07-26 04:47:51', NULL, NULL),
(20, 10, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-6d1334bd729046ec9f105215b5483d10.png', 0, 1, '2026-07-26 04:47:51', NULL, NULL),
(21, 10, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-5f978128811c4d0988584d4d1adbf5ae.png', 0, 1, '2026-07-26 04:47:51', NULL, NULL),
(22, 11, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-cd147cee5de94c02beb8262bc2ccaeeb.png', 0, 1, '2026-07-26 04:51:03', NULL, NULL),
(23, 11, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-d2ac55507f514d9e98a94e9933ede098.png', 0, 1, '2026-07-26 04:51:03', NULL, NULL),
(24, 11, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-c4c0c64ca4254c2d9b19a94694d0973f.png', 0, 1, '2026-07-26 04:51:03', NULL, NULL),
(25, 12, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-888b5568c118410eb9216428563f720d.png', 0, 1, '2026-07-26 04:51:10', NULL, NULL),
(26, 12, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-42f2002ac6dc4fc2b2ecbfcdd269f508.png', 0, 1, '2026-07-26 04:51:10', NULL, NULL),
(27, 12, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-a2d9ebb6e6df4423ad07801526b0e357.png', 0, 1, '2026-07-26 04:51:10', NULL, NULL),
(28, 13, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-e1a4a9093f1941178b10030942946ebd.png', 0, 1, '2026-07-26 04:51:16', NULL, NULL),
(29, 13, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-056b0b9659bf43d3a167f85c80c909f8.png', 0, 1, '2026-07-26 04:51:16', NULL, NULL),
(30, 13, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-e481eb14017f44e5a7e74fee19cb60aa.png', 0, 1, '2026-07-26 04:51:16', NULL, NULL),
(31, 14, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-ba66f73ad1e04b8ba2cf070d229d99cd.png', 0, 1, '2026-07-26 04:51:24', NULL, NULL),
(32, 14, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-51fbab2a68964b9189108e74f13d6dd1.png', 0, 1, '2026-07-26 04:51:24', NULL, NULL),
(33, 14, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-d12154c105eb4a3dadb11b882fea3bd8.png', 0, 1, '2026-07-26 04:51:24', NULL, NULL),
(34, 15, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-7fbd96d15fc14e61ae040dfe515814c8.png', 0, 1, '2026-07-26 04:51:28', NULL, NULL),
(35, 15, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-78e6e7828dd042ee88db01a90f90fbb3.png', 0, 1, '2026-07-26 04:51:28', NULL, NULL),
(36, 15, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-45b8da5c9228479f8e19780f6a5efc55.png', 0, 1, '2026-07-26 04:51:28', NULL, NULL),
(37, 16, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-b30f29cc84784fa99e86685f497c8dab.png', 0, 1, '2026-07-26 04:51:34', NULL, NULL),
(38, 16, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-1cfd37a717be4ebb854bbf293fb3a6a0.png', 0, 1, '2026-07-26 04:51:34', NULL, NULL),
(39, 16, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-9d64889ff2d945869ce54880f13378cc.png', 0, 1, '2026-07-26 04:51:34', NULL, NULL),
(40, 17, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-f77450470f7b4491ac636db137781537.png', 0, 1, '2026-07-26 04:51:46', NULL, NULL),
(41, 17, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-13d15d4005524415afd6cf79cdd01414.png', 0, 1, '2026-07-26 04:51:46', NULL, NULL),
(42, 17, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-d96bd6f9c929488cabee6fe2549f3183.png', 0, 1, '2026-07-26 04:51:46', NULL, NULL),
(43, 18, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-a934260f8f9f49d89535d7b2a9b45902.png', 0, 1, '2026-07-26 04:51:51', NULL, NULL),
(44, 18, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-e77b3d86a8864ea9a6e39e209a3595b2.png', 0, 1, '2026-07-26 04:51:51', NULL, NULL),
(45, 18, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-8c95965d7b704f1dbb21eb2c1cc6864a.png', 0, 1, '2026-07-26 04:51:51', NULL, NULL),
(46, 19, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-07bc82927e9945eda66f8a38d72a3baa.png', 0, 1, '2026-07-26 04:51:55', NULL, NULL),
(47, 19, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-39bbbc79d0284b3b838bc7100ea5292f.png', 0, 1, '2026-07-26 04:51:55', NULL, NULL),
(48, 19, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-12741dd33d8944a892c1a1bc00abad8e.png', 0, 1, '2026-07-26 04:51:55', NULL, NULL),
(49, 20, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-d6f362bc6b454aa692889f51ca19476a.png', 0, 1, '2026-07-26 05:05:30', NULL, NULL),
(50, 20, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-c4fa3dad759b4b869ba2230169f1dff6.png', 0, 1, '2026-07-26 05:05:30', NULL, NULL),
(51, 20, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-4cab3f9a24394dd396d6f03b3700181b.png', 0, 1, '2026-07-26 05:05:30', NULL, NULL),
(52, 21, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-a4ff33c2c8884ad3b4d2dadfe52ede42.png', 0, 1, '2026-07-26 05:05:57', NULL, NULL),
(53, 21, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-d9de1d6842ec41258ec6679294462b5e.png', 0, 1, '2026-07-26 05:05:57', NULL, NULL),
(54, 21, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-af9c4efbba2e47b48a3202d162868a7d.png', 0, 1, '2026-07-26 05:05:57', NULL, NULL),
(55, 22, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-dab0b5f38fb04587a30404843a809e5e.png', 0, 1, '2026-07-26 05:09:59', NULL, NULL),
(56, 22, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-cf6e620c3b4b4acfbd41da282fc739e8.png', 0, 1, '2026-07-26 05:09:59', NULL, NULL),
(57, 22, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-aa66fe2076f0409da2883876cbb40f2e.png', 0, 1, '2026-07-26 05:09:59', NULL, NULL),
(58, 23, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-ad30e44c92454317a37715744de79246.png', 0, 1, '2026-07-26 05:10:11', NULL, NULL),
(59, 23, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-82be8ceb5e834f8aa56e9ae6ea50cfca.png', 0, 1, '2026-07-26 05:10:11', NULL, NULL),
(60, 23, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-2f5dd6d6277444cdb70a099170e04700.png', 0, 1, '2026-07-26 05:10:11', NULL, NULL),
(61, 24, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-6d5dbcb04e2b40f48f69eb50630f6691.png', 0, 1, '2026-07-26 05:10:35', NULL, NULL),
(62, 24, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-2555ee06eef14c56b67623069904c9c3.png', 0, 1, '2026-07-26 05:10:35', NULL, NULL),
(63, 24, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-advanced-58abe12a254d4092a6c111de229af5fd.png', 0, 1, '2026-07-26 05:10:35', NULL, NULL),
(64, 25, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-ea4e7a01028a42ef950e0e3aca3fc0b5.png', 0, 1, '2026-07-26 05:16:19', NULL, NULL),
(65, 25, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-2018644952b9492d8a8ad114ff9a7e20.png', 0, 1, '2026-07-26 05:16:19', NULL, NULL),
(66, 25, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-6005966bbd094ec29c3060da67b85e59.png', 0, 1, '2026-07-26 05:16:19', NULL, NULL),
(67, 26, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-cfbf33a527f24da59b2f775fdb8cff1d.png', 0, 1, '2026-07-26 05:16:35', NULL, NULL),
(68, 26, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-5b1d8ff302ef4edaa382081cdf0f731d.png', 0, 1, '2026-07-26 05:16:35', NULL, NULL),
(69, 26, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-2991212e8b1645e994fc4e308c8c272c.png', 0, 1, '2026-07-26 05:16:35', NULL, NULL),
(70, 27, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-107e36d9c5974c8b8a585796d2ff6716.png', 0, 1, '2026-07-26 05:22:35', NULL, NULL),
(71, 27, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-55ef1dd8efba4a1da143ea0bd99f8083.png', 0, 1, '2026-07-26 05:22:35', NULL, NULL),
(72, 27, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-4ce66e49cad94bc7baf83b687764ccb9.png', 0, 1, '2026-07-26 05:22:35', NULL, NULL),
(73, 28, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-34d29fe552dc40c1a452e736eea60f22.png', 0, 1, '2026-07-26 05:28:03', NULL, NULL),
(74, 28, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-569ea6154dc54755967958df3972f493.png', 0, 1, '2026-07-26 05:28:03', NULL, NULL),
(75, 28, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-8d2ff6d23e3d4b449c0afa9b47532969.png', 0, 1, '2026-07-26 05:28:03', NULL, NULL),
(76, 29, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-8e59ea2286ea4a35b1b6cc124db3cb7a.png', 0, 1, '2026-07-26 05:28:22', NULL, NULL),
(77, 29, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-70df892863464e09b6a06c67b57ab478.png', 0, 1, '2026-07-26 05:28:22', NULL, NULL),
(78, 29, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-350596a08a5a46ea90696c625b8e9ff1.png', 0, 1, '2026-07-26 05:28:22', NULL, NULL),
(79, 30, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-9dad0d3b613c45149a59c6113e287ea5.png', 0, 1, '2026-07-26 05:28:36', NULL, NULL),
(80, 30, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-652f16f64d894737be5dbfd89ef7d47b.png', 0, 1, '2026-07-26 05:28:36', NULL, NULL),
(81, 30, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-7795838f1c0d48cc9e6879a5d2fe883f.png', 0, 1, '2026-07-26 05:28:36', NULL, NULL),
(82, 31, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-aa2ce1166331493d95b9d425392f9962.png', 0, 1, '2026-07-26 05:29:28', NULL, NULL),
(83, 31, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-d91b47cd907441bfba579e3bf7ca557c.png', 0, 1, '2026-07-26 05:29:28', NULL, NULL),
(84, 31, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-photo-1e0d2631c79a40398a43c8d5217335a5.png', 0, 1, '2026-07-26 05:29:28', NULL, NULL),
(85, 32, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-13cf7721bd4f4934880d4aa67c027c7b.png', 0, 1, '2026-07-26 06:04:08', NULL, NULL),
(86, 32, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-757af50ed9d84ee5843dbe00a408dde9.png', 0, 1, '2026-07-26 06:04:08', NULL, NULL),
(87, 32, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-32214f0d1d6a4bf5b6503aad68af6e63.png', 0, 1, '2026-07-26 06:04:08', NULL, NULL),
(88, 33, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-2e54015953294af186c58f92d744e2ba.png', 0, 1, '2026-07-26 06:04:19', NULL, NULL),
(89, 33, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-1f349157635b4522b3d06b341eb84014.png', 0, 1, '2026-07-26 06:04:19', NULL, NULL),
(90, 33, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-65d5f7f46bf2447188f607dfb9168d4e.png', 0, 1, '2026-07-26 06:04:19', NULL, NULL),
(91, 34, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-4827e9cba7a04ae3857272d293deec16.png', 0, 1, '2026-07-26 06:04:23', NULL, NULL),
(92, 34, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-e21077b114064907a3f3644df6070ffc.png', 0, 1, '2026-07-26 06:04:23', NULL, NULL),
(93, 34, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-80cd2a3837cc49c8a26e3d03bdb7778b.png', 0, 1, '2026-07-26 06:04:23', NULL, NULL),
(94, 35, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-8e5d161375804ce1a2d8de1f95a48b5e.png', 0, 1, '2026-07-26 06:04:27', NULL, NULL),
(95, 35, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-86066e8bd43d4ea4ac74c2f2f6813b84.png', 0, 1, '2026-07-26 06:04:27', NULL, NULL),
(96, 35, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-06cc54b185c247e8a4074b6dce376d8b.png', 0, 1, '2026-07-26 06:04:27', NULL, NULL),
(97, 36, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-f354affe9d2a4a538a3543b8ec59dffe.png', 0, 1, '2026-07-26 06:04:31', NULL, NULL),
(98, 36, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-5f21c502e1f14eda9ee76c34c9a9524b.png', 0, 1, '2026-07-26 06:04:31', NULL, NULL),
(99, 36, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-5397885cdd674b11b37ce20d28f82bea.png', 0, 1, '2026-07-26 06:04:31', NULL, NULL),
(100, 37, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-a5c6a9d1ad574fbdae003bc06b4df93b.png', 0, 1, '2026-07-26 06:05:37', NULL, NULL),
(101, 37, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-fed18e9216e6495fb1f2f13369f2e6ef.png', 0, 1, '2026-07-26 06:05:37', NULL, NULL),
(102, 37, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-supplied-hand-ea0790f640f245b08d93f0c3e2e2fbcc.png', 0, 1, '2026-07-26 06:05:37', NULL, NULL),
(103, 38, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-ae7c47ac601445beaec464981e092815.png', 0, 1, '2026-07-26 06:17:05', NULL, NULL),
(104, 38, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-464dd1948a604db1ad345c1c530db030.png', 0, 1, '2026-07-26 06:17:05', NULL, NULL),
(105, 38, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-db33a21df20447c5b923ab07748793a2.png', 0, 1, '2026-07-26 06:17:05', NULL, NULL),
(106, 39, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-41ef16beb39a4f8f8064ee23a900edea.png', 0, 1, '2026-07-26 06:27:45', NULL, NULL),
(107, 39, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-e12724f5e7734f27976af3100a6a6af5.png', 0, 1, '2026-07-26 06:27:45', NULL, NULL),
(108, 39, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-ddc27e4561504c918cca759e915064f3.png', 0, 1, '2026-07-26 06:27:45', NULL, NULL),
(109, 40, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-24d2242f43ec424199fb21055f4be2e4.png', 0, 1, '2026-07-26 06:29:04', NULL, NULL),
(110, 40, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-55b927543c4844db934dff73bdc4bb1d.png', 0, 1, '2026-07-26 06:29:04', NULL, NULL),
(111, 40, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-fa14328cb2cd438aa3ee8a6a59b4ba2e.png', 0, 1, '2026-07-26 06:29:04', NULL, NULL),
(112, 41, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-df3f8d53b28643cf8bb706cf139d71f7.png', 0, 1, '2026-07-26 06:29:17', NULL, NULL),
(113, 41, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-51be771714ac4e1584a8bfc8f270cf69.png', 0, 1, '2026-07-26 06:29:17', NULL, NULL),
(114, 41, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-a29c18a92e5743aea47f09399cc9a7c2.png', 0, 1, '2026-07-26 06:29:17', NULL, NULL),
(115, 42, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-5475ef12a80f481c94af64f47712f085.png', 0, 1, '2026-07-26 06:29:41', NULL, NULL),
(116, 42, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-38f185151cd442b29781e8c3ee70ac29.png', 0, 1, '2026-07-26 06:29:41', NULL, NULL),
(117, 42, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-fae48d7e4c5343358dd54fc22f2b2001.png', 0, 1, '2026-07-26 06:29:41', NULL, NULL),
(118, 43, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-5e4bfcbbee3e473996788b795a95af2f.png', 0, 1, '2026-07-26 06:29:55', NULL, NULL),
(119, 43, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-a66a1980957c4719a5c53f6e472cfacd.png', 0, 1, '2026-07-26 06:29:55', NULL, NULL),
(120, 43, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-8925b6b43cbd4420996b4ec78860a840.png', 0, 1, '2026-07-26 06:29:55', NULL, NULL),
(121, 44, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-9d37b13c479c46818ddab8a6d6daafd8.png', 0, 1, '2026-07-26 06:48:12', NULL, NULL),
(122, 44, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-ae26d7a60c9443899962a4b4dff2db1d.png', 0, 1, '2026-07-26 06:48:12', NULL, NULL),
(123, 44, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-fba8b702d4644e60b1404ad0d4afd939.png', 0, 1, '2026-07-26 06:48:12', NULL, NULL),
(124, 45, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-dd2459f53ed04ab288454b6ee0e98428.png', 0, 1, '2026-07-26 06:48:37', NULL, NULL),
(125, 45, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-b372534b7afe4d28bcd5cbfad6aeb73c.png', 0, 1, '2026-07-26 06:48:37', NULL, NULL),
(126, 45, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-ae6db7baf9914e0ab9c908530ac500be.png', 0, 1, '2026-07-26 06:48:37', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ai_settings`
--

CREATE TABLE `ai_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `is_secret` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_settings`
--

INSERT INTO `ai_settings` (`id`, `setting_key`, `setting_value`, `is_secret`, `updated_by`, `updated_at`) VALUES
(1, 'ai_enabled', '1', 0, NULL, '2026-07-26 03:47:45'),
(2, 'nail_generator_enabled', '1', 0, NULL, '2026-07-26 03:47:45'),
(3, 'chatbot_enabled', '0', 0, NULL, '2026-07-26 03:47:45'),
(4, 'receipt_verification_enabled', '0', 0, NULL, '2026-07-26 03:47:45'),
(5, 'style_recommendation_enabled', '0', 0, NULL, '2026-07-26 03:47:45'),
(6, 'ai_service_url', 'http://127.0.0.1:5000', 0, NULL, '2026-07-26 03:47:45'),
(7, 'daily_customer_limit', '10', 0, NULL, '2026-07-26 03:47:45'),
(8, 'images_per_request', '3', 0, NULL, '2026-07-26 03:47:45'),
(9, 'request_timeout_seconds', '240', 0, NULL, '2026-07-26 03:47:45'),
(10, 'image_model', 'gpt-image-2', 0, NULL, '2026-07-26 03:47:45'),
(11, 'lip_preview_enabled', '1', 0, NULL, '2026-07-26 07:26:02'),
(12, 'eyebrow_preview_enabled', '1', 0, NULL, '2026-07-26 07:26:02'),
(13, 'beauty_photo_retention_hours', '24', 0, NULL, '2026-07-26 07:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `ai_settings_phase1_backup`
--

CREATE TABLE `ai_settings_phase1_backup` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nail_generator_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `chatbot_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `receipt_verification_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `style_recommendation_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `daily_generation_limit` smallint(5) UNSIGNED NOT NULL DEFAULT 10,
  `ai_service_url` varchar(255) DEFAULT 'http://127.0.0.1:5000',
  `api_key` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_settings_phase1_backup`
--

INSERT INTO `ai_settings_phase1_backup` (`id`, `nail_generator_enabled`, `chatbot_enabled`, `receipt_verification_enabled`, `style_recommendation_enabled`, `daily_generation_limit`, `ai_service_url`, `api_key`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 10, 'http://127.0.0.1:5000', NULL, '2026-07-25 13:07:57');

-- --------------------------------------------------------

--
-- Table structure for table `ai_usage_events`
--

CREATE TABLE `ai_usage_events` (
  `id` bigint(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `event_type` enum('generated','selected','downloaded','booked','deleted','failed') NOT NULL,
  `design_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `provider` varchar(40) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `action` varchar(150) NOT NULL,
  `module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `service_id` int(10) UNSIGNED DEFAULT NULL,
  `staff_id` int(10) UNSIGNED DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `selected_ai_design_id` int(11) DEFAULT NULL,
  `selected_beauty_result_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_profiles`
--

CREATE TABLE `customer_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birthday` date DEFAULT NULL,
  `gender` enum('female','male','non_binary','prefer_not_to_say') DEFAULT NULL,
  `preferred_language` enum('english','filipino','taglish') NOT NULL DEFAULT 'english',
  `favorite_nail_shape` varchar(50) DEFAULT NULL,
  `favorite_colors` text DEFAULT NULL,
  `preferred_staff_id` int(10) UNSIGNED DEFAULT NULL,
  `registration_method` enum('personal_info','google','facebook') NOT NULL DEFAULT 'personal_info',
  `email_verification_token` varchar(128) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_profiles`
--

INSERT INTO `customer_profiles` (`id`, `user_id`, `first_name`, `middle_name`, `last_name`, `birthday`, `gender`, `preferred_language`, `favorite_nail_shape`, `favorite_colors`, `preferred_staff_id`, `registration_method`, `email_verification_token`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'Kirk Ervin', NULL, 'Galvan', '1990-03-03', 'prefer_not_to_say', 'english', NULL, NULL, NULL, 'personal_info', '1a65de04950ea7393db2f61b84e27a485662837529b9500931a117ba935d81d9', NULL, '2026-07-25 13:27:00', '2026-07-25 13:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `employment_records`
--

CREATE TABLE `employment_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `employee_number` varchar(50) NOT NULL,
  `date_hired` date NOT NULL,
  `employment_status` enum('full_time','part_time','contractual') NOT NULL DEFAULT 'full_time',
  `salary_type` enum('monthly','daily','hourly','commission') NOT NULL DEFAULT 'monthly',
  `salary_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `contract_end_date` date DEFAULT NULL,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `emergency_contact_number` varchar(50) DEFAULT NULL,
  `emergency_contact_relationship` varchar(100) DEFAULT NULL,
  `government_id_type` varchar(100) DEFAULT NULL,
  `government_id_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `record_status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('new','reviewed','flagged') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(50) DEFAULT NULL,
  `status` enum('in_stock','low_stock','out_of_stock') NOT NULL DEFAULT 'in_stock',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `localization_settings`
--

CREATE TABLE `localization_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `language_code` enum('english','filipino','taglish') NOT NULL DEFAULT 'english',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `localization_settings`
--

INSERT INTO `localization_settings` (`id`, `language_code`, `updated_at`) VALUES
(1, 'english', '2026-07-25 13:07:57');

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `push_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `booking_confirmation_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `payment_confirmation_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `promotion_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_minutes` int(10) UNSIGNED NOT NULL DEFAULT 30,
  `sms_sender_name` varchar(50) NOT NULL DEFAULT 'NBW',
  `reply_to_email` varchar(150) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_settings`
--

INSERT INTO `notification_settings` (`id`, `sms_enabled`, `email_enabled`, `push_enabled`, `booking_confirmation_enabled`, `payment_confirmation_enabled`, `promotion_enabled`, `reminder_minutes`, `sms_sender_name`, `reply_to_email`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 1, 1, 30, 'NBW', NULL, '2026-07-25 13:07:57');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `proof_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration` int(10) UNSIGNED NOT NULL DEFAULT 60,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_accounts`
--

CREATE TABLE `social_accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `provider` enum('google','facebook') NOT NULL,
  `provider_user_id` varchar(255) NOT NULL,
  `provider_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_profiles`
--

CREATE TABLE `staff_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `branch` varchar(150) NOT NULL DEFAULT 'Main Branch',
  `employment_status` enum('full_time','part_time','contractual') NOT NULL DEFAULT 'full_time',
  `hired_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_profiles`
--

INSERT INTO `staff_profiles` (`id`, `user_id`, `specialty`, `branch`, `employment_status`, `hired_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'Beauty Therapist', 'Main Branch', 'full_time', NULL, '2026-07-25 13:17:39', '2026-07-25 13:17:39');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `salon_name` varchar(150) NOT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `opening_time` time DEFAULT '09:00:00',
  `closing_time` time DEFAULT '18:00:00',
  `downpayment_percentage` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `grace_period_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 15,
  `default_language` enum('English','Filipino','Taglish') NOT NULL DEFAULT 'English',
  `timezone` varchar(100) NOT NULL DEFAULT 'Asia/Manila',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `salon_name`, `contact_number`, `email`, `address`, `facebook_url`, `opening_time`, `closing_time`, `downpayment_percentage`, `grace_period_minutes`, `default_language`, `timezone`, `updated_at`) VALUES
(1, 'Nails & Body Worx', NULL, NULL, NULL, NULL, '09:00:00', '18:00:00', 50, 15, 'English', 'Asia/Manila', '2026-07-25 13:07:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','staff','customer') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `phone_number`, `password`, `role`, `status`, `profile_picture`, `created_at`, `updated_at`) VALUES
(1, 'System Super Administrator', 'SupAdmin', 'superadmin@nbw.local', NULL, '$2y$12$AF9o69L1OJFcKA0RfKLDjeguu825xcOpPEp2fudFdzmkh3sRcd7R6', 'super_admin', 'active', NULL, '2026-07-25 13:07:57', '2026-07-25 13:07:57'),
(2, 'Test Admin', 'Test Admin', 'TestAdmin@gmail.com', NULL, '$2y$10$/0Uk9xvuSWe5D84FOCaRWut3m6VYA4LV3Jc.VCOOWriYD1eMb7LZ.', 'admin', 'active', NULL, '2026-07-25 13:16:41', '2026-07-25 13:16:41'),
(3, 'Test Staff', 'Test Staff', 'TestStaff@gmail.com', '09561234567', '$2y$10$n4Mkz7g4v5puI7xZdmSKoOtcTAQ4lXIs3PsWcUid4852T7ppDWfIO', 'staff', 'active', NULL, '2026-07-25 13:17:39', '2026-07-25 13:17:39'),
(4, 'Kirk Ervin Galvan', 'kirkervingalvan', 'kurtyxz3@gmail.com', '+639544361016', '$2y$10$QBdBtI8GZdTC/YXL9x2iueEHzH2rfAv13d5IDapwpNUuDuqSlTJRC', 'customer', 'active', NULL, '2026-07-25 13:27:00', '2026-07-25 13:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `user_language_preferences`
--

CREATE TABLE `user_language_preferences` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `language_code` enum('english','filipino','taglish') NOT NULL DEFAULT 'english',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_language_preferences`
--

INSERT INTO `user_language_preferences` (`user_id`, `language_code`, `updated_at`) VALUES
(4, 'english', '2026-07-25 13:27:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_beauty_requests`
--
ALTER TABLE `ai_beauty_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_beauty_request_customer` (`customer_id`,`created_at`),
  ADD KEY `idx_beauty_request_area` (`beauty_area`,`created_at`);

--
-- Indexes for table `ai_beauty_results`
--
ALTER TABLE `ai_beauty_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_beauty_request_result` (`request_id`,`result_number`),
  ADD KEY `idx_beauty_result_customer` (`customer_id`,`deleted_at`,`created_at`);

--
-- Indexes for table `ai_daily_limit_resets`
--
ALTER TABLE `ai_daily_limit_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_limit_reset_customer` (`customer_id`,`reset_at`);

--
-- Indexes for table `ai_design_requests`
--
ALTER TABLE `ai_design_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_request_customer_date` (`customer_id`,`created_at`),
  ADD KEY `idx_ai_request_status` (`status`);

--
-- Indexes for table `ai_generated_designs`
--
ALTER TABLE `ai_generated_designs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ai_request_number` (`request_id`,`design_number`),
  ADD KEY `idx_ai_design_customer` (`customer_id`),
  ADD KEY `idx_ai_design_request` (`request_id`),
  ADD KEY `idx_ai_design_deleted` (`deleted_at`);

--
-- Indexes for table `ai_settings`
--
ALTER TABLE `ai_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ai_settings_key` (`setting_key`),
  ADD KEY `idx_ai_settings_updated_by` (`updated_by`);

--
-- Indexes for table `ai_settings_phase1_backup`
--
ALTER TABLE `ai_settings_phase1_backup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ai_usage_events`
--
ALTER TABLE `ai_usage_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_usage_date` (`created_at`),
  ADD KEY `idx_ai_usage_customer` (`customer_id`,`created_at`),
  ADD KEY `idx_ai_usage_event` (`event_type`,`created_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user_id` (`user_id`),
  ADD KEY `idx_audit_module` (`module`),
  ADD KEY `idx_audit_created_at` (`created_at`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_customer` (`customer_id`),
  ADD KEY `idx_bookings_service` (`service_id`),
  ADD KEY `idx_bookings_staff_date` (`staff_id`,`booking_date`),
  ADD KEY `idx_bookings_status_date` (`status`,`booking_date`),
  ADD KEY `idx_booking_ai_design` (`selected_ai_design_id`),
  ADD KEY `idx_booking_beauty_result` (`selected_beauty_result_id`);

--
-- Indexes for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_profiles_user` (`user_id`),
  ADD KEY `idx_customer_profile_staff` (`preferred_staff_id`);

--
-- Indexes for table `employment_records`
--
ALTER TABLE `employment_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_employment_user` (`user_id`),
  ADD UNIQUE KEY `uq_employee_number` (`employee_number`),
  ADD KEY `idx_employment_status` (`employment_status`),
  ADD KEY `idx_employment_record_status` (`record_status`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_customer` (`customer_id`),
  ADD KEY `idx_feedback_booking` (`booking_id`),
  ADD KEY `idx_feedback_status` (`status`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_status` (`status`);

--
-- Indexes for table `localization_settings`
--
ALTER TABLE `localization_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payments_booking` (`booking_id`),
  ADD KEY `idx_payments_status_created` (`status`,`created_at`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_promotions_status_dates` (`status`,`start_date`,`end_date`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_status` (`status`);

--
-- Indexes for table `social_accounts`
--
ALTER TABLE `social_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_social_provider_user` (`provider`,`provider_user_id`),
  ADD KEY `idx_social_user` (`user_id`);

--
-- Indexes for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_staff_profiles_user` (`user_id`),
  ADD KEY `idx_staff_profile_user` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role_status` (`role`,`status`);

--
-- Indexes for table `user_language_preferences`
--
ALTER TABLE `user_language_preferences`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_beauty_requests`
--
ALTER TABLE `ai_beauty_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_beauty_results`
--
ALTER TABLE `ai_beauty_results`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_daily_limit_resets`
--
ALTER TABLE `ai_daily_limit_resets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_design_requests`
--
ALTER TABLE `ai_design_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `ai_generated_designs`
--
ALTER TABLE `ai_generated_designs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `ai_settings`
--
ALTER TABLE `ai_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ai_usage_events`
--
ALTER TABLE `ai_usage_events`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employment_records`
--
ALTER TABLE `employment_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_accounts`
--
ALTER TABLE `social_accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_staff` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD CONSTRAINT `fk_customer_preferred_staff` FOREIGN KEY (`preferred_staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_customer_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employment_records`
--
ALTER TABLE `employment_records`
  ADD CONSTRAINT `fk_employment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_feedback_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `social_accounts`
--
ALTER TABLE `social_accounts`
  ADD CONSTRAINT `fk_social_account_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD CONSTRAINT `fk_staff_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_language_preferences`
--
ALTER TABLE `user_language_preferences`
  ADD CONSTRAINT `fk_user_language_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

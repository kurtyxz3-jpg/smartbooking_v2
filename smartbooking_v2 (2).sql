-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 28, 2026 at 06:09 PM
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
(45, 4, 'demo', 'preference-preview-v2', 'Lipstick', 'Long', '#E11D48', 'Elegant minimalist', 'Everyday', '[\"Pearls\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Lipstick. Length: Long. Color palette: #E11D48. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Pearls. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-26 06:48:32', '2026-07-26 06:48:37'),
(46, 4, 'demo', 'preference-preview-v2', 'Round', 'Medium', '#A9DAA9', 'Elegant minimalist', 'Everyday', '[\"Small rhinestones\",\"Glitter\",\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: #A9DAA9. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Small rhinestones, Glitter, Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-27 12:09:25', '2026-07-27 12:09:30'),
(47, 4, 'demo', 'preference-preview-v2', 'Lipstick', 'Short', '#E72F2F', 'Elegant minimalist', 'Everyday', '[\"Small rhinestones\",\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Lipstick. Length: Short. Color palette: #E72F2F. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Small rhinestones, Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-27 14:55:58', '2026-07-27 14:56:03'),
(48, 4, 'demo', 'preference-preview-v2', 'Round', 'Medium', '#826EDC', 'Elegant minimalist', 'Everyday', '[\"Small rhinestones\",\"Floral accents\"]', NULL, 'Create a photorealistic professional nail art design preview. Nail shape: Round. Length: Medium. Color palette: #826EDC. Style: Elegant minimalist. Occasion: Everyday. Add-ons: Small rhinestones, Floral accents. Creative direction: balanced salon-wearable composition. Customer notes: none. Show one elegant manicured hand, realistic skin and nails, clean luxury salon lighting, uncluttered neutral background, no words, no logos, no watermark, anatomically correct fingers.', 'completed', NULL, '2026-07-28 09:19:13', '2026-07-28 09:19:18');

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
(126, 45, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-ae6db7baf9914e0ab9c908530ac500be.png', 0, 1, '2026-07-26 06:48:37', NULL, NULL),
(127, 46, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-6f96c767fdf14fc9b74a46579e726be3.png', 0, 1, '2026-07-27 12:09:30', NULL, NULL),
(128, 46, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-36bb455808a340a091658d7da5ba927f.png', 0, 1, '2026-07-27 12:09:30', NULL, NULL),
(129, 46, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-862faa2993694658b1a80b431f010bbb.png', 0, 1, '2026-07-27 12:09:30', NULL, NULL),
(130, 47, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-a17041c7008e4a09889df2a068d84b4e.png', 0, 1, '2026-07-27 14:56:03', NULL, NULL),
(131, 47, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-9bee8f39e0054e3d8ae1e2da103894af.png', 0, 1, '2026-07-27 14:56:03', NULL, NULL),
(132, 47, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-96d712460dc946a793fde2fe8f105c81.png', 0, 1, '2026-07-27 14:56:03', NULL, NULL),
(133, 48, 4, 1, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-1d08f3622a0d43cebaeb4ad40144de57.png', 0, 1, '2026-07-28 09:19:18', NULL, NULL),
(134, 48, 4, 2, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-bae36670e74c45cd9b78532217861fc8.png', 0, 1, '2026-07-28 09:19:18', NULL, NULL),
(135, 48, 4, 3, 'http://127.0.0.1:5000/api/generated/nail-demo-realistic-hand-10c6a20421e4454898b294b7f852ecf3.png', 0, 1, '2026-07-28 09:19:18', NULL, NULL);

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

--
-- Dumping data for table `ai_usage_events`
--

INSERT INTO `ai_usage_events` (`id`, `customer_id`, `request_id`, `event_type`, `design_id`, `booking_id`, `provider`, `created_at`) VALUES
(1, 4, 46, 'generated', NULL, NULL, 'demo', '2026-07-27 12:09:30'),
(2, 4, 47, 'generated', NULL, NULL, 'demo', '2026-07-27 14:56:03'),
(3, 4, 48, 'generated', NULL, NULL, 'demo', '2026-07-28 09:19:18');

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

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'SupAdmin', 'Created database backup', 'Backup & Restore', 'A full SQL backup was downloaded.', '::1', '2026-07-28 15:54:43');

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
  `status` enum('pending','confirmed','in_progress','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `selected_ai_design_id` int(11) DEFAULT NULL,
  `selected_beauty_result_id` bigint(20) DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_due_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `service_id`, `staff_id`, `booking_date`, `booking_time`, `status`, `notes`, `created_at`, `updated_at`, `selected_ai_design_id`, `selected_beauty_result_id`, `branch_id`, `payment_due_at`) VALUES
(1, 4, 1, NULL, '2026-07-30', '14:00:00', 'cancelled', '', '2026-07-27 14:20:38', '2026-07-27 14:22:30', NULL, NULL, 3, NULL),
(2, 4, 2, NULL, '2026-08-03', '14:00:00', 'pending', '', '2026-07-27 14:25:08', '2026-07-27 14:40:08', NULL, NULL, 2, '2026-07-27 22:55:08'),
(3, 4, 4, 3, '2026-08-03', '14:00:00', 'completed', '', '2026-07-28 12:29:50', '2026-07-28 12:32:51', NULL, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_status_history`
--

CREATE TABLE `booking_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_status_history`
--

INSERT INTO `booking_status_history` (`id`, `booking_id`, `from_status`, `to_status`, `changed_by`, `note`, `created_at`) VALUES
(1, 1, NULL, 'cancelled', NULL, 'Existing booking imported into the timeline', '2026-07-27 14:20:38'),
(2, 2, NULL, 'pending', NULL, 'Existing booking imported into the timeline', '2026-07-27 14:25:08'),
(3, 3, 'pending', 'confirmed', 2, NULL, '2026-07-28 12:31:04');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_code` varchar(30) NOT NULL,
  `branch_name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(40) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `opening_time` time DEFAULT '09:00:00',
  `closing_time` time DEFAULT '20:00:00',
  `gcash_merchant_name` varchar(150) DEFAULT NULL,
  `gcash_number` varchar(30) DEFAULT NULL,
  `gcash_qr_path` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_code`, `branch_name`, `address`, `contact_number`, `email`, `opening_time`, `closing_time`, `gcash_merchant_name`, `gcash_number`, `gcash_qr_path`, `status`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'BR-001', 'Main Branch', NULL, NULL, NULL, '09:00:00', '20:00:00', 'NBW Main Branch (DEMO ONLY)', '0917-XXX-0001', 'uploads/payment-qr/demo-gcash-branch-1.svg', 'active', 1, '2026-07-27 13:49:10', '2026-07-27 14:53:04'),
(2, 'BR-002', 'Branch 2', NULL, NULL, NULL, '09:00:00', '20:00:00', 'NBW Branch 2 (DEMO ONLY)', '0917-XXX-0002', 'uploads/payment-qr/demo-gcash-branch-2.svg', 'active', 2, '2026-07-27 13:49:10', '2026-07-27 14:53:04'),
(3, 'BR-003', 'Branch 3', NULL, NULL, NULL, '09:00:00', '20:00:00', 'NBW Branch 3 (DEMO ONLY)', '0917-XXX-0003', 'uploads/payment-qr/demo-gcash-branch-3.svg', 'active', 3, '2026-07-27 13:49:10', '2026-07-27 14:53:04');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `barcode` varchar(120) DEFAULT NULL,
  `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` varchar(500) DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `quantity`, `reorder_level`, `unit`, `status`, `created_at`, `updated_at`, `branch_id`, `category_id`, `supplier_id`, `sku`, `barcode`, `cost_price`, `notes`, `archived_at`) VALUES
(1, 'Hair Treatment Mask 1kg', 8.00, 3.00, 'tub', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:49:25', 1, 3, 1, 'BR-001-HAIR-MASK-1K', '880001003001', 540.00, 'Sample stock record for demonstration and testing.', '2026-07-28 16:49:25'),
(2, 'Acetone 1 Liter', 10.00, 4.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 1, 1, 'BR-001-NAIL-ACETONE-1L', '880001001006', 210.00, 'Sample stock record for demonstration and testing.', NULL),
(3, 'Nail Art Rhinestones', 18.00, 6.00, 'pack', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 1, 1, 'BR-001-NAIL-RHINE', '880001001005', 95.00, 'Sample stock record for demonstration and testing.', NULL),
(4, 'Soft Gel Nail Tips', 12.00, 5.00, 'box', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 1, 1, 'BR-001-NAIL-SOFT-TIP', '880001001004', 480.00, 'Sample stock record for demonstration and testing.', NULL),
(5, 'Gel Polish - Assorted Colors', 48.00, 15.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 1, 1, 'BR-001-NAIL-GEL-ASST', '880001001003', 185.00, 'Sample stock record for demonstration and testing.', NULL),
(6, 'Top Coat 15ml', 22.00, 8.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 1, 1, 'BR-001-NAIL-TOP-15', '880001001002', 155.00, 'Sample stock record for demonstration and testing.', NULL),
(7, 'Base Coat 15ml', 24.00, 8.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 1, 1, 'BR-001-NAIL-BASE-15', '880001001001', 145.00, 'Sample stock record for demonstration and testing.', NULL),
(8, 'Disposable Face Masks', 15.00, 5.00, 'box', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 5, 1, 'BR-001-SAN-MASK', '880001005003', 130.00, 'Sample stock record for demonstration and testing.', NULL),
(9, '70% Isopropyl Alcohol 1L', 16.00, 5.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 5, 1, 'BR-001-SAN-ALCOHOL-1L', '880001005002', 125.00, 'Sample stock record for demonstration and testing.', NULL),
(10, 'Nitrile Gloves', 20.00, 8.00, 'box', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 5, 1, 'BR-001-SAN-GLOVES', '880001005001', 285.00, 'Sample stock record for demonstration and testing.', NULL),
(11, 'Facial Cleanser 500ml', 11.00, 4.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 4, 1, 'BR-001-SKIN-CLEAN-500', '880001004001', 295.00, 'Sample stock record for demonstration and testing.', NULL),
(12, 'Disposable Face Towels', 30.00, 10.00, 'pack', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 2, 1, 'BR-001-SPA-FACE-TOWEL', '880001002003', 175.00, 'Sample stock record for demonstration and testing.', NULL),
(13, 'Massage Oil 1 Liter', 9.00, 4.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 2, 1, 'BR-001-SPA-OIL-1L', '880001002002', 320.00, 'Sample stock record for demonstration and testing.', NULL),
(14, 'Foot Scrub 500g', 14.00, 5.00, 'jar', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 1, 2, 1, 'BR-001-SPA-FOOT-SCRUB', '880001002001', 260.00, 'Sample stock record for demonstration and testing.', NULL),
(15, 'Hair Treatment Mask 1kg', 5.00, 3.00, 'tub', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 3, 2, 'BR-002-HAIR-MASK-1K', '880002003001', 540.00, 'Sample stock record for demonstration and testing.', NULL),
(16, 'Acetone 1 Liter', 6.00, 4.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 1, 2, 'BR-002-NAIL-ACETONE-1L', '880002001006', 210.00, 'Sample stock record for demonstration and testing.', NULL),
(17, 'Nail Art Rhinestones', 11.00, 6.00, 'pack', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 1, 2, 'BR-002-NAIL-RHINE', '880002001005', 95.00, 'Sample stock record for demonstration and testing.', NULL),
(18, 'Soft Gel Nail Tips', 8.00, 5.00, 'box', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 1, 2, 'BR-002-NAIL-SOFT-TIP', '880002001004', 480.00, 'Sample stock record for demonstration and testing.', NULL),
(19, 'Gel Polish - Assorted Colors', 34.00, 15.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 1, 2, 'BR-002-NAIL-GEL-ASST', '880002001003', 185.00, 'Sample stock record for demonstration and testing.', NULL),
(20, 'Top Coat 15ml', 14.00, 8.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 1, 2, 'BR-002-NAIL-TOP-15', '880002001002', 155.00, 'Sample stock record for demonstration and testing.', NULL),
(21, 'Base Coat 15ml', 16.00, 8.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 1, 2, 'BR-002-NAIL-BASE-15', '880002001001', 145.00, 'Sample stock record for demonstration and testing.', NULL),
(22, 'Disposable Face Masks', 8.00, 5.00, 'box', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 5, 2, 'BR-002-SAN-MASK', '880002005003', 130.00, 'Sample stock record for demonstration and testing.', NULL),
(23, '70% Isopropyl Alcohol 1L', 9.00, 5.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 5, 2, 'BR-002-SAN-ALCOHOL-1L', '880002005002', 125.00, 'Sample stock record for demonstration and testing.', NULL),
(24, 'Nitrile Gloves', 12.00, 8.00, 'box', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 5, 2, 'BR-002-SAN-GLOVES', '880002005001', 285.00, 'Sample stock record for demonstration and testing.', NULL),
(25, 'Facial Cleanser 500ml', 6.00, 4.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 4, 2, 'BR-002-SKIN-CLEAN-500', '880002004001', 295.00, 'Sample stock record for demonstration and testing.', NULL),
(26, 'Disposable Face Towels', 20.00, 10.00, 'pack', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 2, 2, 'BR-002-SPA-FACE-TOWEL', '880002002003', 175.00, 'Sample stock record for demonstration and testing.', NULL),
(27, 'Massage Oil 1 Liter', 5.00, 4.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 2, 2, 'BR-002-SPA-OIL-1L', '880002002002', 320.00, 'Sample stock record for demonstration and testing.', NULL),
(28, 'Foot Scrub 500g', 9.00, 5.00, 'jar', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 2, 2, 2, 'BR-002-SPA-FOOT-SCRUB', '880002002001', 260.00, 'Sample stock record for demonstration and testing.', NULL),
(29, 'Hair Treatment Mask 1kg', 2.00, 3.00, 'tub', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 3, 3, 'BR-003-HAIR-MASK-1K', '880003003001', 540.00, 'Sample stock record for demonstration and testing.', NULL),
(30, 'Acetone 1 Liter', 3.00, 4.00, 'bottle', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 1, 3, 'BR-003-NAIL-ACETONE-1L', '880003001006', 210.00, 'Sample stock record for demonstration and testing.', NULL),
(31, 'Nail Art Rhinestones', 5.00, 6.00, 'pack', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 1, 3, 'BR-003-NAIL-RHINE', '880003001005', 95.00, 'Sample stock record for demonstration and testing.', NULL),
(32, 'Soft Gel Nail Tips', 4.00, 5.00, 'box', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 1, 3, 'BR-003-NAIL-SOFT-TIP', '880003001004', 480.00, 'Sample stock record for demonstration and testing.', NULL),
(33, 'Gel Polish - Assorted Colors', 20.00, 15.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 1, 3, 'BR-003-NAIL-GEL-ASST', '880003001003', 185.00, 'Sample stock record for demonstration and testing.', NULL),
(34, 'Top Coat 15ml', 7.00, 8.00, 'bottle', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 1, 3, 'BR-003-NAIL-TOP-15', '880003001002', 155.00, 'Sample stock record for demonstration and testing.', NULL),
(35, 'Base Coat 15ml', 10.00, 8.00, 'bottle', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 1, 3, 'BR-003-NAIL-BASE-15', '880003001001', 145.00, 'Sample stock record for demonstration and testing.', NULL),
(36, 'Disposable Face Masks', 0.00, 5.00, 'box', 'out_of_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 5, 3, 'BR-003-SAN-MASK', '880003005003', 130.00, 'Sample stock record for demonstration and testing.', NULL),
(37, '70% Isopropyl Alcohol 1L', 4.00, 5.00, 'bottle', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 5, 3, 'BR-003-SAN-ALCOHOL-1L', '880003005002', 125.00, 'Sample stock record for demonstration and testing.', NULL),
(38, 'Nitrile Gloves', 6.00, 8.00, 'box', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 5, 3, 'BR-003-SAN-GLOVES', '880003005001', 285.00, 'Sample stock record for demonstration and testing.', NULL),
(39, 'Facial Cleanser 500ml', 3.00, 4.00, 'bottle', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 4, 3, 'BR-003-SKIN-CLEAN-500', '880003004001', 295.00, 'Sample stock record for demonstration and testing.', NULL),
(40, 'Disposable Face Towels', 12.00, 10.00, 'pack', 'in_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 2, 3, 'BR-003-SPA-FACE-TOWEL', '880003002003', 175.00, 'Sample stock record for demonstration and testing.', NULL),
(41, 'Massage Oil 1 Liter', 2.00, 4.00, 'bottle', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 2, 3, 'BR-003-SPA-OIL-1L', '880003002002', 320.00, 'Sample stock record for demonstration and testing.', NULL),
(42, 'Foot Scrub 500g', 4.00, 5.00, 'jar', 'low_stock', '2026-07-28 08:40:47', '2026-07-28 08:40:47', 3, 2, 3, 'BR-003-SPA-FOOT-SCRUB', '880003002001', 260.00, 'Sample stock record for demonstration and testing.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_categories`
--

CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_categories`
--

INSERT INTO `inventory_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Nail Supplies', 'Polish, gel, acrylic, extensions, and nail-art materials', 'active', '2026-07-28 08:37:46', '2026-07-28 08:37:46'),
(2, 'Spa and Massage', 'Oils, scrubs, towels, and treatment supplies', 'active', '2026-07-28 08:37:46', '2026-07-28 08:37:46'),
(3, 'Hair Care', 'Shampoo, color, treatment, and styling products', 'active', '2026-07-28 08:37:46', '2026-07-28 08:37:46'),
(4, 'Skin Care', 'Facial and skin-treatment consumables', 'active', '2026-07-28 08:37:46', '2026-07-28 08:37:46'),
(5, 'Sanitation', 'Disinfectants, gloves, masks, and hygiene supplies', 'active', '2026-07-28 08:37:46', '2026-07-28 08:37:46'),
(6, 'General Supplies', 'Other operational products and consumables', 'active', '2026-07-28 08:37:46', '2026-07-28 08:37:46');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `movement_type` enum('in','out','adjustment') NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `quantity_before` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity_after` decimal(12,2) NOT NULL DEFAULT 0.00,
  `note` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `inventory_id`, `branch_id`, `movement_type`, `quantity`, `quantity_before`, `quantity_after`, `note`, `created_by`, `created_at`) VALUES
(1, 1, 1, 'in', 8.00, 0.00, 8.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(2, 2, 1, 'in', 10.00, 0.00, 10.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(3, 3, 1, 'in', 18.00, 0.00, 18.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(4, 4, 1, 'in', 12.00, 0.00, 12.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(5, 5, 1, 'in', 48.00, 0.00, 48.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(6, 6, 1, 'in', 22.00, 0.00, 22.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(7, 7, 1, 'in', 24.00, 0.00, 24.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(8, 8, 1, 'in', 15.00, 0.00, 15.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(9, 9, 1, 'in', 16.00, 0.00, 16.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(10, 10, 1, 'in', 20.00, 0.00, 20.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(11, 11, 1, 'in', 11.00, 0.00, 11.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(12, 12, 1, 'in', 30.00, 0.00, 30.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(13, 13, 1, 'in', 9.00, 0.00, 9.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(14, 14, 1, 'in', 14.00, 0.00, 14.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(15, 15, 2, 'in', 5.00, 0.00, 5.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(16, 16, 2, 'in', 6.00, 0.00, 6.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(17, 17, 2, 'in', 11.00, 0.00, 11.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(18, 18, 2, 'in', 8.00, 0.00, 8.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(19, 19, 2, 'in', 34.00, 0.00, 34.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(20, 20, 2, 'in', 14.00, 0.00, 14.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(21, 21, 2, 'in', 16.00, 0.00, 16.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(22, 22, 2, 'in', 8.00, 0.00, 8.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(23, 23, 2, 'in', 9.00, 0.00, 9.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(24, 24, 2, 'in', 12.00, 0.00, 12.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(25, 25, 2, 'in', 6.00, 0.00, 6.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(26, 26, 2, 'in', 20.00, 0.00, 20.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(27, 27, 2, 'in', 5.00, 0.00, 5.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(28, 28, 2, 'in', 9.00, 0.00, 9.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(29, 29, 3, 'in', 2.00, 0.00, 2.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(30, 30, 3, 'in', 3.00, 0.00, 3.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(31, 31, 3, 'in', 5.00, 0.00, 5.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(32, 32, 3, 'in', 4.00, 0.00, 4.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(33, 33, 3, 'in', 20.00, 0.00, 20.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(34, 34, 3, 'in', 7.00, 0.00, 7.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(35, 35, 3, 'in', 10.00, 0.00, 10.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(36, 37, 3, 'in', 4.00, 0.00, 4.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(37, 38, 3, 'in', 6.00, 0.00, 6.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(38, 39, 3, 'in', 3.00, 0.00, 3.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(39, 40, 3, 'in', 12.00, 0.00, 12.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(40, 41, 3, 'in', 2.00, 0.00, 2.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47'),
(41, 42, 3, 'in', 4.00, 0.00, 4.00, 'Sample opening stock', NULL, '2026-07-28 08:40:47');

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
  `submitted_at` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `review_note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_method`, `reference_number`, `proof_path`, `status`, `submitted_at`, `verified_at`, `verified_by`, `review_note`, `created_at`, `updated_at`) VALUES
(1, 3, 65.00, 'GCash', '123456789', 'uploads/payment-proofs/booking-3-634d5831b48b3977.jpg', 'verified', '2026-07-28 20:30:23', '2026-07-28 20:38:01', 2, NULL, '2026-07-28 12:30:23', '2026-07-28 12:38:01');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `promo_price` decimal(10,2) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `button_label` varchar(80) NOT NULL DEFAULT 'View offer',
  `button_link` varchar(500) NOT NULL DEFAULT 'customer/services.php',
  `image_path` varchar(500) DEFAULT NULL,
  `theme` enum('gold','rose','brown') NOT NULL DEFAULT 'gold',
  `show_in_popup` tinyint(1) NOT NULL DEFAULT 0,
  `show_on_homepage` tinyint(1) NOT NULL DEFAULT 0,
  `is_global` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `branch_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `description`, `badge`, `discount_value`, `promo_price`, `original_price`, `button_label`, `button_link`, `image_path`, `theme`, `show_in_popup`, `show_on_homepage`, `is_global`, `display_order`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'Glow Starter Package', 'Enjoy a manicure, pedicure, and relaxing foot spa in one introductory visit.', 'New Guest Offer', 10.20, 449.00, 500.00, 'Book this offer', 'customer/book.php?service=Manicure', '/smartbooking_v2/assets/images/home-packages/package-foot-spa.webp', 'gold', 1, 1, 1, 10, '2026-07-28', '2026-10-26', 'active', '2026-07-28 13:09:12', '2026-07-28 13:09:12', 1),
(2, 'Gel Polish Pair', 'Book gel polish for hands and feet from Monday to Thursday and save on your set.', 'Weekday Beauty Treat', 13.47, 649.00, 750.00, 'Choose gel polish', 'customer/book.php?service=Gel%20Polish%20-%20Hands', '/smartbooking_v2/assets/images/services/nails/nails-14.webp', 'rose', 1, 1, 1, 20, '2026-07-28', '2026-10-26', 'active', '2026-07-28 13:09:12', '2026-07-28 13:09:12', 1),
(3, 'Massage + Foot Spa', 'A full hour of massage followed by a refreshing ladies or gentlemen foot spa.', 'Relax & Renew', 12.63, 699.00, 800.00, 'Reserve relaxation', 'customer/book.php?service=Regular%20Massage%20-%201%20Hour', '/smartbooking_v2/assets/images/home-packages/package-massage.webp', 'brown', 1, 1, 1, 30, '2026-07-28', '2026-10-26', 'active', '2026-07-28 13:09:12', '2026-07-28 13:09:12', 1);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `slug` varchar(180) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_note` varchar(160) DEFAULT NULL,
  `duration` int(10) UNSIGNED NOT NULL DEFAULT 60,
  `image` varchar(500) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category_id`, `slug`, `name`, `description`, `price`, `price_note`, `duration`, `image`, `featured`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'service-1', 'Hot Stone Massage - 1.5 Hours', 'Heated-stone massage for deep relaxation.', 800.00, NULL, 90, NULL, 0, 0, 'active', '2026-07-27 14:20:38', '2026-07-28 09:59:46'),
(2, NULL, 'service-2', 'Manicure', 'Classic nail shaping, cuticle care, and regular polish.', 120.00, NULL, 30, NULL, 0, 0, 'active', '2026-07-27 14:25:08', '2026-07-28 09:59:46'),
(3, 1, 'manicure', 'Manicure', NULL, 120.00, NULL, 45, NULL, 0, 10, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(4, 1, 'pedicure', 'Pedicure', NULL, 130.00, NULL, 50, NULL, 0, 20, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(5, 1, 'gel-polish-hands', 'Gel Polish - Hands', NULL, 350.00, NULL, 60, NULL, 0, 30, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(6, 1, 'gel-polish-feet', 'Gel Polish - Feet', NULL, 400.00, NULL, 60, NULL, 0, 40, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(7, 1, 'softgel-extension-with-gel-polish', 'Softgel Extension with Gel Polish', NULL, 800.00, NULL, 120, NULL, 0, 50, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(8, 1, 'softgel-extension-per-nail', 'Softgel Extension per Nail', NULL, 100.00, '₱100 per nail', 20, NULL, 0, 60, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(9, 1, 'softgel-removal', 'Softgel Removal', NULL, 200.00, NULL, 45, NULL, 0, 70, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(10, 1, 'softgel-removal-per-nail', 'Softgel Removal per Nail', NULL, 25.00, '₱25 per nail', 10, NULL, 0, 80, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(11, 1, 'polygel-or-acrylic-removal', 'Polygel or Acrylic Removal', NULL, 250.00, NULL, 60, NULL, 0, 90, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(12, 1, 'acrylic-removal-per-nail', 'Acrylic Removal per Nail', NULL, 30.00, '₱30 per nail', 10, NULL, 0, 100, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(13, 1, 'polygel-infill-with-gel-polish', 'Polygel Infill with Gel Polish', NULL, 600.00, NULL, 90, NULL, 0, 110, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(14, 1, 'nail-art', 'Nail Art', NULL, 150.00, '₱150–₱200', 30, NULL, 0, 120, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(15, 1, 'nail-stone-add-on', 'Nail Stone Add-on', NULL, 20.00, '₱20–₱50 each', 10, NULL, 0, 130, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(16, 1, 'french-tip-regular-polish', 'French Tip Regular Polish', NULL, 50.00, NULL, 20, NULL, 0, 140, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(17, 1, 'change-gel-polish', 'Change Gel Polish', NULL, 250.00, NULL, 45, NULL, 0, 150, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(18, 1, 'gel-polish-removal', 'Gel Polish Removal', NULL, 50.00, NULL, 30, NULL, 0, 160, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(19, 2, 'foot-spa-ladies', 'Foot Spa - Ladies', NULL, 300.00, NULL, 60, NULL, 0, 170, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(20, 2, 'foot-spa-gentlemen', 'Foot Spa - Gentlemen', NULL, 350.00, NULL, 60, NULL, 0, 180, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(21, 2, 'hand-scrub', 'Hand Scrub', NULL, 250.00, NULL, 30, NULL, 0, 190, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(22, 2, 'whitening-mask', 'Whitening Mask', NULL, 100.00, NULL, 30, NULL, 0, 200, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(23, 2, 'callus-remover', 'Callus Remover', NULL, 50.00, NULL, 20, NULL, 0, 210, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(24, 2, 'ear-candling', 'Ear Candling', NULL, 200.00, NULL, 30, NULL, 0, 220, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(25, 2, 'whitening-full-body-scrub', 'Whitening Full Body Scrub', NULL, 800.00, NULL, 60, NULL, 0, 230, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(26, 14, 'kiddie-manicure', 'Kiddie Manicure', NULL, 50.00, NULL, 30, NULL, 0, 240, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(27, 14, 'kiddie-pedicure', 'Kiddie Pedicure', NULL, 50.00, NULL, 30, NULL, 0, 250, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(28, 14, 'kiddie-polish-only', 'Kiddie Polish Only', NULL, 30.00, NULL, 20, NULL, 0, 260, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(29, 14, 'kiddie-foot-spa', 'Kiddie Foot Spa', NULL, 120.00, NULL, 40, NULL, 0, 270, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(30, 14, 'kiddie-whitening-mask', 'Kiddie Whitening Mask', NULL, 50.00, NULL, 25, NULL, 0, 280, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(31, 14, 'kiddie-package-mani-pedi-spa-and-mask', 'Kiddie Package - Mani, Pedi, Spa and Mask', NULL, 250.00, NULL, 90, NULL, 0, 290, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(32, 2, 'regular-massage-1-hour', 'Regular Massage - 1 Hour', NULL, 500.00, NULL, 60, NULL, 0, 300, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(33, 2, 'massage-with-stretching-1-hour', 'Massage with Stretching - 1 Hour', NULL, 600.00, NULL, 60, NULL, 0, 310, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(34, 2, 'ventosa-massage-1-hour', 'Ventosa Massage - 1 Hour', NULL, 650.00, NULL, 60, NULL, 0, 320, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(35, 2, 'massage-with-ear-candle-1-hour', 'Massage with Ear Candle - 1 Hour', NULL, 680.00, NULL, 60, NULL, 0, 330, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(36, 2, 'hot-stone-massage-1-5-hours', 'Hot Stone Massage - 1.5 Hours', NULL, 800.00, NULL, 90, NULL, 0, 340, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(37, 2, 'head-spa-1-hour', 'Head Spa - 1 Hour', NULL, 1200.00, NULL, 60, NULL, 0, 350, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(38, 2, 'massage-with-body-scrub-1-hour', 'Massage with Body Scrub - 1 Hour', NULL, 1200.00, NULL, 60, NULL, 0, 360, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(39, 2, 'back-massage-30-minutes', 'Back Massage - 30 Minutes', NULL, 350.00, NULL, 30, NULL, 0, 370, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(40, 2, 'hand-massage-30-minutes', 'Hand Massage - 30 Minutes', NULL, 250.00, NULL, 30, NULL, 0, 380, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(41, 2, 'foot-massage-30-minutes', 'Foot Massage - 30 Minutes', NULL, 300.00, NULL, 30, NULL, 0, 390, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(42, 2, 'hand-reflexology-30-minutes', 'Hand Reflexology - 30 Minutes', NULL, 300.00, NULL, 30, NULL, 0, 400, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(43, 2, 'foot-reflexology-30-minutes', 'Foot Reflexology - 30 Minutes', NULL, 350.00, NULL, 30, NULL, 0, 410, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(44, 5, 'threading', 'Threading', NULL, 100.00, NULL, 30, NULL, 0, 420, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(45, 5, 'eyelash-lift', 'Eyelash Lift', NULL, 500.00, NULL, 60, NULL, 0, 430, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(46, 5, 'eyelash-lift-with-tint', 'Eyelash Lift with Tint', NULL, 600.00, NULL, 75, NULL, 0, 440, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(47, 5, 'eyelash-extension-removal', 'Eyelash Extension Removal', NULL, 100.00, '₱100–₱150', 45, NULL, 0, 450, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(48, 5, 'brow-lamination', 'Brow Lamination', NULL, 500.00, NULL, 60, NULL, 0, 460, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(49, 8, 'package-1', 'Package #1', 'Manicure + Pedicure + Foot Spa', 500.00, 'Additional ₱50 for men', 120, NULL, 0, 470, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(50, 8, 'package-2', 'Package #2', 'Manicure + Pedicure + Foot Spa + Whitening Foot Mask', 580.00, 'Additional ₱50 for men', 120, NULL, 0, 480, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(51, 8, 'package-3', 'Package #3', 'Manicure + Pedicure + Foot Spa + Threading', 580.00, 'Additional ₱50 for men', 120, NULL, 0, 490, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(52, 8, 'package-4', 'Package #4', 'Manicure + Pedicure + Foot Spa + Ear Candling', 680.00, 'Additional ₱50 for men', 120, NULL, 0, 500, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(53, 8, 'package-5', 'Package #5', 'Manicure + Pedicure + Foot Spa + Underarm Wax', 730.00, 'Additional ₱50 for men', 120, NULL, 0, 510, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(54, 8, 'package-6', 'Package #6', 'Manicure + Pedicure + Foot Spa + Hand Scrub', 730.00, 'Additional ₱50 for men', 120, NULL, 0, 520, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(55, 8, 'package-7', 'Package #7', 'Manicure + Pedicure + Foot Spa + Hand Massage (30 minutes)', 730.00, 'Additional ₱50 for men', 120, NULL, 0, 530, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(56, 8, 'package-8', 'Package #8', 'Manicure + Pedicure + Foot Spa + Foot Massage (30 minutes)', 780.00, 'Additional ₱50 for men', 120, NULL, 0, 540, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(57, 8, 'package-9', 'Package #9', 'Manicure + Pedicure + Foot Spa + Regular Facial', 980.00, 'Additional ₱50 for men', 120, NULL, 0, 550, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(58, 8, 'package-10', 'Package #10', 'Manicure + Pedicure + Foot Spa + Lash Lift', 980.00, 'Additional ₱50 for men', 120, NULL, 0, 560, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(59, 8, 'package-11', 'Package #11', 'Manicure + Pedicure + Foot Spa + Regular Massage', 980.00, 'Additional ₱50 for men', 120, NULL, 0, 570, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(60, 8, 'package-12', 'Package #12', 'Manicure + Pedicure + Foot Spa + Brow Lamination', 980.00, 'Additional ₱50 for men', 120, NULL, 0, 580, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(61, 8, 'package-13', 'Package #13', 'Gel Manicure + Regular Pedicure', 450.00, 'Additional ₱50 for men', 120, NULL, 0, 590, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(62, 8, 'package-14', 'Package #14', 'Gel Manicure + Regular Pedicure + Foot Spa', 730.00, 'Additional ₱50 for men', 120, NULL, 0, 600, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(63, 8, 'package-15', 'Package #15', 'Gel Manicure + Gel Pedicure', 730.00, 'Additional ₱50 for men', 120, NULL, 0, 610, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(64, 8, 'package-16', 'Package #16', 'Gel Manicure + Gel Pedicure + Foot Spa', 1000.00, 'Additional ₱50 for men', 120, NULL, 0, 620, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(65, 8, 'package-17', 'Package #17', 'Gel Manicure + Gel Pedicure + Foot Spa + Whitening Mask', 1080.00, 'Additional ₱50 for men', 120, NULL, 0, 630, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(66, 8, 'package-18', 'Package #18', 'Softgel Extension + Gel Polish + Pedicure', 900.00, 'Additional ₱50 for men', 120, NULL, 0, 640, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(67, 8, 'package-19', 'Package #19', 'Softgel Extension + Gel Polish + Pedicure + Foot Spa', 1200.00, 'Additional ₱50 for men', 120, NULL, 0, 650, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(68, 8, 'package-20', 'Package #20', 'Softgel Extension + Gel Polish + Gel Pedicure + Foot Spa', 1450.00, 'Additional ₱50 for men', 120, NULL, 0, 660, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(69, 4, '12d-hifu', '12D HIFU', 'High Frequency Focused Ultrasound', 4500.00, NULL, 90, NULL, 0, 670, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(70, 10, 'emsculpt-30-minutes', 'EMSculpt - 30 Minutes', NULL, 1000.00, NULL, 30, NULL, 0, 680, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(71, 10, 'rf-face', 'RF Face', NULL, 350.00, NULL, 30, NULL, 0, 690, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(72, 10, 'rf-arms', 'RF Arms', NULL, 500.00, NULL, 30, NULL, 0, 700, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(73, 10, 'rf-legs', 'RF Legs', NULL, 600.00, NULL, 30, NULL, 0, 710, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(74, 10, 'rf-tummy', 'RF Tummy', NULL, 600.00, NULL, 30, NULL, 0, 720, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(75, 10, 'rf-thigh', 'RF Thigh', NULL, 800.00, NULL, 30, NULL, 0, 730, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(76, 10, 'mesolipo-rf-double-chin', 'Mesolipo + RF - Double Chin', NULL, 1500.00, 'Get 5 sessions + 1 free; payable in installment', 60, NULL, 0, 740, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(77, 10, 'mesolipo-rf-face', 'Mesolipo + RF - Face', NULL, 1700.00, 'Get 5 sessions + 1 free; payable in installment', 60, NULL, 0, 750, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(78, 10, 'mesolipo-rf-bra-line', 'Mesolipo + RF - Bra Line', NULL, 2000.00, 'Get 5 sessions + 1 free; payable in installment', 60, NULL, 0, 760, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(79, 10, 'mesolipo-rf-arms', 'Mesolipo + RF - Arms', NULL, 2500.00, 'Get 5 sessions + 1 free; payable in installment', 60, NULL, 0, 770, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(80, 10, 'mesolipo-rf-love-handle', 'Mesolipo + RF - Love Handle', NULL, 2500.00, 'Get 5 sessions + 1 free; payable in installment', 60, NULL, 0, 780, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(81, 10, 'mesolipo-rf-thigh', 'Mesolipo + RF - Thigh', NULL, 2500.00, 'Get 5 sessions + 1 free; payable in installment', 60, NULL, 0, 790, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(82, 10, 'mesolipo-rf-tummy', 'Mesolipo + RF - Tummy', NULL, 2700.00, 'Get 5 sessions + 1 free; payable in installment', 60, NULL, 0, 800, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(83, 13, 'lip-filler', 'Lip Filler', NULL, 8000.00, '1 mL / 1 syringe hyaluronic acid filler', 60, NULL, 0, 810, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(84, 13, 'chin-filler', 'Chin Filler', NULL, 8000.00, '1 mL / 1 syringe hyaluronic acid filler', 60, NULL, 0, 820, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(85, 13, 'jaw-filler', 'Jaw Filler', NULL, 15000.00, '2 mL / 2 syringes hyaluronic acid filler', 75, NULL, 0, 830, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(86, 13, 'botox-barbie-arms', 'Botox - Barbie Arms', NULL, 7000.00, NULL, 60, NULL, 0, 840, 'active', '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(87, 13, 'botox-frown-lines', 'Botox - Frown Lines', NULL, 5000.00, NULL, 60, NULL, 0, 850, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(88, 13, 'botox-crow-s-feet', 'Botox - Crow\'s Feet', NULL, 3500.00, NULL, 60, NULL, 0, 860, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(89, 13, 'botox-whole-forehead', 'Botox - Whole Forehead', NULL, 6500.00, NULL, 60, NULL, 0, 870, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(90, 13, 'botox-laugh-line', 'Botox - Laugh Line', NULL, 5000.00, NULL, 60, NULL, 0, 880, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(91, 13, 'botox-jawtox', 'Botox - Jawtox', NULL, 7000.00, NULL, 60, NULL, 0, 890, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(92, 13, 'botox-alartox', 'Botox - Alartox', NULL, 3000.00, NULL, 60, NULL, 0, 900, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(93, 13, 'botox-lip-flip', 'Botox - Lip Flip', NULL, 5000.00, NULL, 60, NULL, 0, 910, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(94, 13, 'botox-traptox', 'Botox - Traptox', NULL, 7500.00, NULL, 60, NULL, 0, 920, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(95, 13, 'botox-sweatox', 'Botox - Sweatox', NULL, 8000.00, NULL, 60, NULL, 0, 930, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(96, 13, 'full-face-botox-promo', 'Full Face Botox Promo', NULL, 9000.00, NULL, 60, NULL, 0, 940, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(97, 13, 'hiko-nose-lift-basic-or-retouch-package', 'Hiko Nose Lift - Basic or Retouch Package', '4 PCL threads, nose bridge filler, and take-home medicine', 5500.00, NULL, 75, NULL, 0, 950, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(98, 13, 'hiko-nose-lift-standard-package', 'Hiko Nose Lift - Standard Package', '8 PCL threads, nose bridge filler, Alartox, nose tip filler, and take-home medicine', 10000.00, NULL, 90, NULL, 0, 960, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(99, 13, 'hiko-nose-lift-advance-package', 'Hiko Nose Lift - Advance Package', 'Unlimited nose bridge threads, nose bridge fillers, Alartox, nose tip filler, and take-home medicine', 15000.00, NULL, 105, NULL, 0, 970, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(100, 13, 'hiko-nose-lift-ultimate-package', 'Hiko Nose Lift - Ultimate Package', 'Unlimited nose bridge threads, nose bridge fillers, Alartox, nose tip filler, nose tip threads, and take-home medicine', 20000.00, NULL, 120, NULL, 0, 980, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(101, 4, 'regular-facial-with-mask', 'Regular Facial with Mask', NULL, 500.00, NULL, 60, NULL, 0, 990, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(102, 4, 'anti-aging-24k-gold-mask', 'Anti-Aging 24K Gold Mask', NULL, 650.00, NULL, 60, NULL, 0, 1000, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(103, 4, 'diamond-peel', 'Diamond Peel', NULL, 700.00, NULL, 60, NULL, 0, 1010, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(104, 4, 'regular-facial-with-rf-face', 'Regular Facial with RF Face', NULL, 800.00, NULL, 60, NULL, 0, 1020, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(105, 4, 'supreme-diamond-peel', 'Supreme Diamond Peel', NULL, 850.00, NULL, 60, NULL, 0, 1030, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(106, 4, 'melasma-laser-removal', 'Melasma Laser Removal', NULL, 1000.00, NULL, 60, NULL, 0, 1040, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(107, 4, 'korean-bb-glow', 'Korean BB Glow', NULL, 1200.00, NULL, 60, NULL, 0, 1050, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(108, 4, 'blackdoll-carbon-laser', 'Blackdoll Carbon Laser', NULL, 1200.00, NULL, 60, NULL, 0, 1060, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(109, 4, 'supreme-blackdoll-laser', 'Supreme Blackdoll Laser', NULL, 1500.00, NULL, 60, NULL, 0, 1070, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(110, 4, 'hydra-3-in-1-facial', 'Hydra 3-in-1 Facial', NULL, 1500.00, NULL, 60, NULL, 0, 1080, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(111, 4, 'ultimate-hydra-10-in-1-facial', 'Ultimate Hydra 10-in-1 Facial', NULL, 2000.00, NULL, 60, NULL, 0, 1090, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(112, 4, 'ultimate-acne-treatment-sqt-resurfacing', 'Ultimate Acne Treatment - SQT Resurfacing', NULL, 3500.00, NULL, 60, NULL, 0, 1100, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(113, 4, 'ultimate-melasma-treatment-sqt-revitalizing', 'Ultimate Melasma Treatment - SQT Revitalizing', NULL, 4000.00, NULL, 60, NULL, 0, 1110, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(114, 15, 'underarm-carbon-laser', 'Underarm Carbon Laser', NULL, 700.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1120, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(115, 15, 'bikini-carbon-laser', 'Bikini Carbon Laser', NULL, 800.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1130, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(116, 15, 'bikini-diode-with-carbon', 'Bikini Diode with Carbon', NULL, 1500.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1140, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(117, 15, 'underarm-diode-with-carbon', 'Underarm Diode with Carbon', NULL, 1300.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1150, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(118, 15, 'glutathione-drip', 'Glutathione Drip', NULL, 1500.00, 'Get 5 sessions + 1 free; payable in installment', 60, NULL, 0, 1160, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(119, 7, 'waxing-fingers', 'Waxing - Fingers', NULL, 200.00, 'Add ₱50 for men', 30, NULL, 0, 1170, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(120, 7, 'waxing-toes', 'Waxing - Toes', NULL, 200.00, 'Add ₱50 for men', 30, NULL, 0, 1180, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(121, 7, 'waxing-upper-lip', 'Waxing - Upper Lip', NULL, 200.00, 'Add ₱50 for men', 30, NULL, 0, 1190, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(122, 7, 'waxing-lower-lip', 'Waxing - Lower Lip', NULL, 200.00, 'Add ₱50 for men', 30, NULL, 0, 1200, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(123, 7, 'waxing-underarm', 'Waxing - Underarm', NULL, 250.00, 'Add ₱50 for men', 30, NULL, 0, 1210, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(124, 7, 'waxing-full-arms', 'Waxing - Full Arms', NULL, 400.00, 'Add ₱50 for men', 30, NULL, 0, 1220, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(125, 7, 'waxing-half-legs', 'Waxing - Half Legs', NULL, 450.00, 'Add ₱50 for men', 30, NULL, 0, 1230, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(126, 7, 'waxing-full-legs', 'Waxing - Full Legs', NULL, 650.00, 'Add ₱50 for men', 30, NULL, 0, 1240, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(127, 7, 'waxing-bikini', 'Waxing - Bikini', NULL, 700.00, 'Add ₱50 for men', 30, NULL, 0, 1250, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(128, 15, 'diode-hair-removal-fingers', 'Diode Hair Removal - Fingers', NULL, 500.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1260, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(129, 15, 'diode-hair-removal-toes', 'Diode Hair Removal - Toes', NULL, 500.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1270, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(130, 15, 'diode-hair-removal-upper-lip', 'Diode Hair Removal - Upper Lip', NULL, 500.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1280, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(131, 15, 'diode-hair-removal-lower-lip', 'Diode Hair Removal - Lower Lip', NULL, 500.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1290, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(132, 15, 'diode-hair-removal-underarm', 'Diode Hair Removal - Underarm', NULL, 700.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1300, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(133, 15, 'diode-hair-removal-cheeks', 'Diode Hair Removal - Cheeks', NULL, 700.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1310, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(134, 15, 'diode-hair-removal-bikini', 'Diode Hair Removal - Bikini', NULL, 800.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1320, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(135, 15, 'diode-hair-removal-full-arms', 'Diode Hair Removal - Full Arms', NULL, 1100.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1330, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(136, 15, 'diode-hair-removal-half-legs', 'Diode Hair Removal - Half Legs', NULL, 1300.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1340, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(137, 15, 'diode-hair-removal-full-legs', 'Diode Hair Removal - Full Legs', NULL, 1500.00, 'Get 5 sessions + 1 free; payable in installment', 45, NULL, 0, 1350, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(138, 15, 'wart-or-skin-tag-removal', 'Wart or Skin Tag Removal', NULL, 800.00, '₱800–₱2,500; free consultation', 60, NULL, 0, 1360, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(139, 15, 'body-tattoo-removal', 'Body Tattoo Removal', NULL, 1000.00, '₱1,000–₱2,500; free consultation', 60, NULL, 0, 1370, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(140, 3, 'hair-cut', 'Hair Cut', NULL, 150.00, NULL, 45, NULL, 0, 1380, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(141, 3, 'blower', 'Blower', NULL, 100.00, NULL, 30, NULL, 0, 1390, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(142, 3, 'shampoo', 'Shampoo', NULL, 100.00, NULL, 30, NULL, 0, 1400, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(143, 3, 'hair-iron', 'Hair Iron', NULL, 200.00, NULL, 45, NULL, 0, 1410, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(144, 3, 'hair-all-in-package', 'Hair All-in Package', 'Shampoo, haircut, and blower', 350.00, NULL, 60, NULL, 0, 1420, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(145, 3, 'hair-color-short-hair', 'Hair Color - Short Hair', NULL, 600.00, NULL, 120, NULL, 0, 1430, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(146, 3, 'hair-color-medium-hair', 'Hair Color - Medium Hair', NULL, 900.00, NULL, 120, NULL, 0, 1440, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(147, 3, 'hair-color-long-hair', 'Hair Color - Long Hair', NULL, 1100.00, NULL, 120, NULL, 0, 1450, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(148, 3, 'hair-color-very-long-hair', 'Hair Color - Very Long Hair', NULL, 1300.00, NULL, 120, NULL, 0, 1460, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(149, 3, 'highlights-short-hair', 'Highlights - Short Hair', NULL, 600.00, NULL, 120, NULL, 0, 1470, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(150, 3, 'highlights-medium-hair', 'Highlights - Medium Hair', NULL, 900.00, NULL, 120, NULL, 0, 1480, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(151, 3, 'highlights-long-hair', 'Highlights - Long Hair', NULL, 1100.00, NULL, 120, NULL, 0, 1490, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(152, 3, 'highlights-very-long-hair', 'Highlights - Very Long Hair', NULL, 1300.00, NULL, 120, NULL, 0, 1500, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(153, 3, 'brazilian-short-hair', 'Brazilian - Short Hair', NULL, 600.00, NULL, 120, NULL, 0, 1510, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(154, 3, 'brazilian-medium-hair', 'Brazilian - Medium Hair', NULL, 900.00, NULL, 120, NULL, 0, 1520, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(155, 3, 'brazilian-long-hair', 'Brazilian - Long Hair', NULL, 1100.00, NULL, 120, NULL, 0, 1530, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(156, 3, 'brazilian-very-long-hair', 'Brazilian - Very Long Hair', NULL, 1300.00, '₱1,300–₱1,500 for very long hair', 120, NULL, 0, 1540, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(157, 3, 'hair-botox-short-hair', 'Hair Botox - Short Hair', NULL, 600.00, NULL, 120, NULL, 0, 1550, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(158, 3, 'hair-botox-medium-hair', 'Hair Botox - Medium Hair', NULL, 900.00, NULL, 120, NULL, 0, 1560, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(159, 3, 'hair-botox-long-hair', 'Hair Botox - Long Hair', NULL, 1100.00, NULL, 120, NULL, 0, 1570, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(160, 3, 'hair-botox-very-long-hair', 'Hair Botox - Very Long Hair', NULL, 1300.00, '₱1,300–₱1,500 for very long hair', 120, NULL, 0, 1580, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(161, 3, 'rebond-short-hair', 'Rebond - Short Hair', NULL, 1200.00, NULL, 120, NULL, 0, 1590, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(162, 3, 'rebond-medium-hair', 'Rebond - Medium Hair', NULL, 1700.00, NULL, 120, NULL, 0, 1600, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(163, 3, 'rebond-long-hair', 'Rebond - Long Hair', NULL, 2000.00, NULL, 120, NULL, 0, 1610, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(164, 3, 'rebond-very-long-hair', 'Rebond - Very Long Hair', NULL, 2200.00, '₱2,200–₱2,500 for very long hair', 120, NULL, 0, 1620, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(165, 3, 'hair-botox-with-color-short-hair', 'Hair Botox with Color - Short Hair', NULL, 1100.00, NULL, 120, NULL, 0, 1630, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(166, 3, 'hair-botox-with-color-medium-hair', 'Hair Botox with Color - Medium Hair', NULL, 1700.00, NULL, 120, NULL, 0, 1640, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(167, 3, 'hair-botox-with-color-long-hair', 'Hair Botox with Color - Long Hair', NULL, 2000.00, NULL, 120, NULL, 0, 1650, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(168, 3, 'hair-botox-with-color-very-long-hair', 'Hair Botox with Color - Very Long Hair', NULL, 2200.00, '₱2,200–₱2,500 for very long hair', 120, NULL, 0, 1660, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(169, 3, 'rebond-with-color-short-hair', 'Rebond with Color - Short Hair', NULL, 1700.00, NULL, 120, NULL, 0, 1670, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(170, 3, 'rebond-with-color-medium-hair', 'Rebond with Color - Medium Hair', NULL, 2500.00, NULL, 120, NULL, 0, 1680, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(171, 3, 'rebond-with-color-long-hair', 'Rebond with Color - Long Hair', NULL, 3000.00, NULL, 120, NULL, 0, 1690, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(172, 3, 'rebond-with-color-very-long-hair', 'Rebond with Color - Very Long Hair', NULL, 3400.00, '₱3,400–₱4,000 for very long hair', 120, NULL, 0, 1700, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(173, 3, 'rebond-with-brazilian-short-hair', 'Rebond with Brazilian - Short Hair', NULL, 1700.00, NULL, 120, NULL, 0, 1710, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(174, 3, 'rebond-with-brazilian-medium-hair', 'Rebond with Brazilian - Medium Hair', NULL, 2500.00, NULL, 120, NULL, 0, 1720, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(175, 3, 'rebond-with-brazilian-long-hair', 'Rebond with Brazilian - Long Hair', NULL, 3000.00, NULL, 120, NULL, 0, 1730, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(176, 3, 'rebond-with-brazilian-very-long-hair', 'Rebond with Brazilian - Very Long Hair', NULL, 3400.00, '₱3,400–₱4,000 for very long hair', 120, NULL, 0, 1740, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(177, 3, 'rebond-with-brazilian-and-color-short-hair', 'Rebond with Brazilian and Color - Short Hair', NULL, 2300.00, NULL, 120, NULL, 0, 1750, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(178, 3, 'rebond-with-brazilian-and-color-medium-hair', 'Rebond with Brazilian and Color - Medium Hair', NULL, 3300.00, NULL, 120, NULL, 0, 1760, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(179, 3, 'rebond-with-brazilian-and-color-long-hair', 'Rebond with Brazilian and Color - Long Hair', NULL, 4000.00, NULL, 120, NULL, 0, 1770, 'active', '2026-07-28 10:14:18', '2026-07-28 10:14:18'),
(180, 3, 'rebond-with-brazilian-and-color-very-long-hair', 'Rebond with Brazilian and Color - Very Long Hair', NULL, 4200.00, '₱4,200–₱4,500 for very long hair', 120, NULL, 0, 1780, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(181, 6, 'ombre-brows-first-session', 'Ombre Brows - First Session', NULL, 5500.00, NULL, 120, NULL, 0, 1790, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(182, 6, 'ombre-brows-second-session', 'Ombre Brows - Second Session', NULL, 2500.00, 'Second session: 1–3 months after first', 90, NULL, 0, 1800, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(183, 6, 'nano-combo-brows-first-session', 'Nano Combo Brows - First Session', NULL, 6500.00, NULL, 120, NULL, 0, 1810, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(184, 6, 'nano-combo-brows-second-session', 'Nano Combo Brows - Second Session', NULL, 3500.00, 'Second session: 1–3 months after first', 90, NULL, 0, 1820, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(185, 6, 'nano-machine-strokes-first-session', 'Nano Machine Strokes - First Session', NULL, 7900.00, NULL, 120, NULL, 0, 1830, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(186, 6, 'nano-machine-strokes-second-session', 'Nano Machine Strokes - Second Session', NULL, 3500.00, 'Second session: 2 weeks–2 months after first', 90, NULL, 0, 1840, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(187, 6, 'menstrokes-first-session', 'Menstrokes - First Session', NULL, 7900.00, NULL, 120, NULL, 0, 1850, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(188, 6, 'menstrokes-second-session', 'Menstrokes - Second Session', NULL, 3500.00, 'Second session: 2 weeks–2 months after first', 90, NULL, 0, 1860, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(189, 6, 'lip-micropigmentation-first-session', 'Lip Micropigmentation - First Session', NULL, 6500.00, NULL, 120, NULL, 0, 1870, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(190, 6, 'lip-micropigmentation-second-session', 'Lip Micropigmentation - Second Session', NULL, 3500.00, 'Second session: 45 days–3 months after first', 90, NULL, 0, 1880, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(191, 6, 'classic-or-winged-eyeliner-tattoo-first-session', 'Classic or Winged Eyeliner Tattoo - First Session', NULL, 3500.00, '₱3,500–₱4,000', 120, NULL, 0, 1890, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(192, 6, 'classic-or-winged-eyeliner-tattoo-second-session', 'Classic or Winged Eyeliner Tattoo - Second Session', NULL, 1500.00, '₱1,500–₱1,800; 1–3 months after first session', 90, NULL, 0, 1900, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(193, 6, 'brow-reconstruction-cover-up-charge', 'Brow Reconstruction - Cover-Up Charge', NULL, 500.00, NULL, 30, NULL, 0, 1910, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(194, 6, 'brow-reconstruction-color-correction', 'Brow Reconstruction - Color Correction', NULL, 700.00, NULL, 45, NULL, 0, 1920, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(195, 6, 'brow-reconstruction-camouflage', 'Brow Reconstruction - Camouflage', NULL, 700.00, NULL, 45, NULL, 0, 1930, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(196, 6, 'regular-laser-brow-removal', 'Regular Laser Brow Removal', NULL, 1500.00, 'Next session after 2–3 weeks if needed', 60, NULL, 0, 1940, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(197, 6, 'same-day-laser-brow-package', 'Same-Day Laser Brow Package', 'Includes Ombre Brows, cover-up charge, color correction, camouflage, and reshaping', 10000.00, NULL, 180, NULL, 0, 1950, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(198, 6, 'ombre-brows-maintenance-1-year-onwards', 'Ombre Brows Maintenance - 1 Year Onwards', NULL, 3500.00, 'For existing clients only', 90, NULL, 0, 1960, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(199, 6, 'ombre-brows-maintenance-2-years-onwards', 'Ombre Brows Maintenance - 2 Years Onwards', NULL, 4000.00, 'For existing clients only', 90, NULL, 0, 1970, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(200, 6, 'ombre-brows-maintenance-3-years-onwards', 'Ombre Brows Maintenance - 3 Years Onwards', NULL, 4500.00, 'For existing clients only', 90, NULL, 0, 1980, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(201, 6, 'nano-combo-brows-maintenance-1-year-onwards', 'Nano Combo Brows Maintenance - 1 Year Onwards', NULL, 4500.00, 'For existing clients only', 90, NULL, 0, 1990, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(202, 6, 'nano-combo-brows-maintenance-2-years-onwards', 'Nano Combo Brows Maintenance - 2 Years Onwards', NULL, 5000.00, 'For existing clients only', 90, NULL, 0, 2000, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(203, 6, 'nano-combo-brows-maintenance-3-years-onwards', 'Nano Combo Brows Maintenance - 3 Years Onwards', NULL, 5500.00, 'For existing clients only', 90, NULL, 0, 2010, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(204, 6, 'nano-machine-strokes-maintenance-1-year-onwards', 'Nano Machine Strokes Maintenance - 1 Year Onwards', NULL, 5900.00, 'For existing clients only', 90, NULL, 0, 2020, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(205, 6, 'nano-machine-strokes-maintenance-2-years-onwards', 'Nano Machine Strokes Maintenance - 2 Years Onwards', NULL, 6500.00, 'For existing clients only', 90, NULL, 0, 2030, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(206, 6, 'nano-machine-strokes-maintenance-3-years-onwards', 'Nano Machine Strokes Maintenance - 3 Years Onwards', NULL, 6900.00, 'For existing clients only', 90, NULL, 0, 2040, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(207, 6, 'menstrokes-maintenance-1-year-onwards', 'Menstrokes Maintenance - 1 Year Onwards', NULL, 5900.00, 'For existing clients only', 90, NULL, 0, 2050, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(208, 6, 'menstrokes-maintenance-2-years-onwards', 'Menstrokes Maintenance - 2 Years Onwards', NULL, 6500.00, 'For existing clients only', 90, NULL, 0, 2060, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(209, 6, 'menstrokes-maintenance-3-years-onwards', 'Menstrokes Maintenance - 3 Years Onwards', NULL, 6900.00, 'For existing clients only', 90, NULL, 0, 2070, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(210, 6, 'lip-micropigmentation-maintenance-1-year-onwards', 'Lip Micropigmentation Maintenance - 1 Year Onwards', NULL, 4500.00, 'For existing clients only', 90, NULL, 0, 2080, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(211, 6, 'lip-micropigmentation-maintenance-2-years-onwards', 'Lip Micropigmentation Maintenance - 2 Years Onwards', NULL, 5000.00, 'For existing clients only', 90, NULL, 0, 2090, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(212, 6, 'lip-micropigmentation-maintenance-3-years-onwards', 'Lip Micropigmentation Maintenance - 3 Years Onwards', NULL, 5500.00, 'For existing clients only', 90, NULL, 0, 2100, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(213, 6, 'eyeliner-tattoo-maintenance-1-year-onwards', 'Eyeliner Tattoo Maintenance - 1 Year Onwards', NULL, 2000.00, '₱2,000–₱2,500; for existing clients only', 90, NULL, 0, 2110, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(214, 6, 'eyeliner-tattoo-maintenance-2-years-onwards', 'Eyeliner Tattoo Maintenance - 2 Years Onwards', NULL, 2500.00, '₱2,500–₱3,000; for existing clients only', 90, NULL, 0, 2120, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19'),
(215, 6, 'eyeliner-tattoo-maintenance-3-years-onwards', 'Eyeliner Tattoo Maintenance - 3 Years Onwards', NULL, 3000.00, '₱3,000–₱3,500; for existing clients only', 90, NULL, 0, 2130, 'active', '2026-07-28 10:14:19', '2026-07-28 10:14:19');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `description`, `status`, `display_order`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Nail Care', 'Manicure, pedicure, polish, extensions, and nail art.', 'active', 10, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(2, 'Spa & Massage', 'Spa, scrub, and massage services.', 'active', 20, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(3, 'Hair Services', 'Hair styling, color, treatment, and care.', 'active', 30, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(4, 'Facial & Skin', 'Facial, skin-care, and beauty treatments.', 'active', 40, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(5, 'Lashes & Brows', 'Eyelash and eyebrow services.', 'active', 50, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(6, 'Semi-Permanent Makeup', 'Brows, eyeliner, and lip pigmentation.', 'active', 60, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(7, 'Waxing', 'Hair-removal and waxing services.', 'active', 70, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(8, 'Packages', 'Bundled salon and beauty packages.', 'active', 80, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(9, 'Other Services', 'Services that do not yet have a dedicated category.', 'active', 90, 0, '2026-07-28 09:59:46', '2026-07-28 09:59:46'),
(10, 'Body Contouring', NULL, 'active', 10, 0, '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(13, 'Injectables', NULL, 'active', 40, 0, '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(14, 'Kiddie Services', NULL, 'active', 50, 0, '2026-07-28 10:14:17', '2026-07-28 10:14:17'),
(15, 'Laser & Skin Treatments', NULL, 'active', 60, 0, '2026-07-28 10:14:17', '2026-07-28 10:14:17');

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
-- Table structure for table `staff_schedule_exceptions`
--

CREATE TABLE `staff_schedule_exceptions` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `exception_date` date NOT NULL,
  `exception_type` enum('day_off','unavailable','custom_hours') NOT NULL DEFAULT 'unavailable',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_schedule_exceptions`
--

INSERT INTO `staff_schedule_exceptions` (`id`, `staff_id`, `branch_id`, `exception_date`, `exception_type`, `start_time`, `end_time`, `reason`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2026-07-31', 'day_off', NULL, NULL, 'testing', 2, '2026-07-28 15:50:53', '2026-07-28 15:50:53');

-- --------------------------------------------------------

--
-- Table structure for table `staff_weekly_availability`
--

CREATE TABLE `staff_weekly_availability` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL,
  `is_working` tinyint(1) NOT NULL DEFAULT 1,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `staff_weekly_availability`
--

INSERT INTO `staff_weekly_availability` (`id`, `staff_id`, `branch_id`, `day_of_week`, `is_working`, `start_time`, `end_time`, `break_start`, `break_end`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, 1, '09:00:00', '18:00:00', '12:00:00', '13:00:00', '2026-07-28 08:16:47', '2026-07-28 08:16:47'),
(2, 3, 1, 2, 1, '09:00:00', '18:00:00', '12:00:00', '13:00:00', '2026-07-28 08:16:47', '2026-07-28 08:16:47'),
(3, 3, 1, 3, 1, '09:00:00', '18:00:00', '12:00:00', '13:00:00', '2026-07-28 08:16:47', '2026-07-28 08:16:47'),
(4, 3, 1, 4, 1, '09:00:00', '18:00:00', '12:00:00', '13:00:00', '2026-07-28 08:16:47', '2026-07-28 08:16:47'),
(5, 3, 1, 5, 1, '09:00:00', '18:00:00', '12:00:00', '13:00:00', '2026-07-28 08:16:47', '2026-07-28 08:16:47'),
(6, 3, 1, 6, 1, '09:00:00', '18:00:00', '12:00:00', '13:00:00', '2026-07-28 08:16:47', '2026-07-28 08:16:47'),
(7, 3, 1, 7, 0, '09:00:00', '18:00:00', '12:00:00', '13:00:00', '2026-07-28 08:16:47', '2026-07-28 08:16:47');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `contact_person` varchar(120) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `branch_id`, `supplier_name`, `contact_person`, `phone`, `email`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'NBW Central Supply - Main Branch', 'Sample Supply Coordinator', '0917-000-0000', 'br001@nbw.local', NULL, 'active', '2026-07-28 08:40:47', '2026-07-28 08:40:47'),
(2, 2, 'NBW Central Supply - Branch 2', 'Sample Supply Coordinator', '0917-000-0000', 'br002@nbw.local', NULL, 'active', '2026-07-28 08:40:47', '2026-07-28 08:40:47'),
(3, 3, 'NBW Central Supply - Branch 3', 'Sample Supply Coordinator', '0917-000-0000', 'br003@nbw.local', NULL, 'active', '2026-07-28 08:40:47', '2026-07-28 08:40:47');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `branch_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `phone_number`, `password`, `role`, `status`, `profile_picture`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'System Super Administrator', 'SupAdmin', 'superadmin@nbw.local', NULL, '$2y$12$AF9o69L1OJFcKA0RfKLDjeguu825xcOpPEp2fudFdzmkh3sRcd7R6', 'super_admin', 'active', NULL, '2026-07-25 13:07:57', '2026-07-25 13:07:57', NULL),
(2, 'Test Admin', 'Test Admin', 'testadmin@gmail.com', NULL, '$2y$10$/0Uk9xvuSWe5D84FOCaRWut3m6VYA4LV3Jc.VCOOWriYD1eMb7LZ.', 'admin', 'active', 'profile-20260728-165521-e2026cb78d1f.jpg', '2026-07-25 13:16:41', '2026-07-28 14:55:21', 1),
(3, 'Test Staff', 'Test Staff', 'TestStaff@gmail.com', '09561234567', '$2y$10$n4Mkz7g4v5puI7xZdmSKoOtcTAQ4lXIs3PsWcUid4852T7ppDWfIO', 'staff', 'active', NULL, '2026-07-25 13:17:39', '2026-07-27 13:49:11', 1),
(4, 'Kirk Ervin Galvan', 'kirkervingalvan', 'kurtyxz3@gmail.com', '+639544361016', '$2y$10$QBdBtI8GZdTC/YXL9x2iueEHzH2rfAv13d5IDapwpNUuDuqSlTJRC', 'customer', 'active', NULL, '2026-07-25 13:27:00', '2026-07-25 13:27:00', NULL);

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
  ADD KEY `idx_booking_beauty_result` (`selected_beauty_result_id`),
  ADD KEY `idx_bookings_branch` (`branch_id`),
  ADD KEY `idx_bookings_payment_due` (`status`,`payment_due_at`);

--
-- Indexes for table `booking_status_history`
--
ALTER TABLE `booking_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_history_booking` (`booking_id`,`created_at`),
  ADD KEY `idx_booking_history_actor` (`changed_by`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_branches_code` (`branch_code`),
  ADD KEY `idx_branches_status_order` (`status`,`display_order`);

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
  ADD KEY `idx_inventory_status` (`status`),
  ADD KEY `idx_inventory_branch` (`branch_id`),
  ADD KEY `idx_inventory_branch_status` (`branch_id`,`status`),
  ADD KEY `idx_inventory_branch_category` (`branch_id`,`category_id`),
  ADD KEY `idx_inventory_barcode` (`barcode`),
  ADD KEY `idx_inventory_sku` (`sku`);

--
-- Indexes for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_inventory_category_name` (`category_name`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_movement_product` (`inventory_id`,`created_at`),
  ADD KEY `idx_inventory_movement_branch` (`branch_id`,`created_at`);

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
  ADD KEY `idx_payments_status_created` (`status`,`created_at`),
  ADD KEY `idx_payments_reference` (`reference_number`),
  ADD KEY `idx_payments_verifier` (`verified_by`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_promotions_status_dates` (`status`,`start_date`,`end_date`),
  ADD KEY `idx_promotions_branch` (`branch_id`),
  ADD KEY `idx_promotions_popup_schedule` (`show_in_popup`,`status`,`start_date`,`end_date`,`display_order`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_services_slug` (`slug`),
  ADD KEY `idx_services_status` (`status`),
  ADD KEY `idx_services_category` (`category_id`),
  ADD KEY `idx_services_slug` (`slug`),
  ADD KEY `idx_services_featured` (`featured`),
  ADD KEY `idx_services_display_order` (`display_order`,`name`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_service_categories_name` (`name`),
  ADD KEY `idx_service_categories_status` (`status`),
  ADD KEY `idx_service_categories_status_order` (`status`,`display_order`,`name`);

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
-- Indexes for table `staff_schedule_exceptions`
--
ALTER TABLE `staff_schedule_exceptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_staff_exception_lookup` (`staff_id`,`branch_id`,`exception_date`,`exception_type`),
  ADD KEY `idx_staff_exception_branch` (`branch_id`,`exception_date`);

--
-- Indexes for table `staff_weekly_availability`
--
ALTER TABLE `staff_weekly_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_staff_weekly_day` (`staff_id`,`branch_id`,`day_of_week`),
  ADD KEY `idx_staff_weekly_branch` (`branch_id`,`staff_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier_branch_status` (`branch_id`,`status`),
  ADD KEY `idx_supplier_name` (`supplier_name`);

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
  ADD KEY `idx_users_role_status` (`role`,`status`),
  ADD KEY `idx_users_branch` (`branch_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `ai_generated_designs`
--
ALTER TABLE `ai_generated_designs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `ai_settings`
--
ALTER TABLE `ai_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ai_usage_events`
--
ALTER TABLE `ai_usage_events`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `booking_status_history`
--
ALTER TABLE `booking_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=429;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
-- AUTO_INCREMENT for table `staff_schedule_exceptions`
--
ALTER TABLE `staff_schedule_exceptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_weekly_availability`
--
ALTER TABLE `staff_weekly_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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

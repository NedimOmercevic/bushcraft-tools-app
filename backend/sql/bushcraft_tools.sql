-- Bushcraft Tools — MySQL schema and sample data (Milestone 2)
-- Engine: InnoDB, charset utf8mb4
-- Import: mysql -u root -p < backend/sql/bushcraft_tools.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS `bushcraft_tools`;
CREATE DATABASE `bushcraft_tools`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `bushcraft_tools`;

-- ---------------------------------------------------------------------------
-- 1. users
-- ---------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name` VARCHAR(100) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. categories
-- ---------------------------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 3. tools
-- ---------------------------------------------------------------------------
CREATE TABLE `tools` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `brand` VARCHAR(100) DEFAULT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tools_category_id` (`category_id`),
  CONSTRAINT `fk_tools_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 4. reviews
-- ---------------------------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `tool_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reviews_user_tool` (`user_id`, `tool_id`),
  KEY `idx_reviews_tool_id` (`tool_id`),
  CONSTRAINT `fk_reviews_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_tool`
    FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 5. favorites
-- ---------------------------------------------------------------------------
CREATE TABLE `favorites` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `tool_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_favorites_user_tool` (`user_id`, `tool_id`),
  KEY `idx_favorites_tool_id` (`tool_id`),
  CONSTRAINT `fk_favorites_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_favorites_tool`
    FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Sample data
-- Demo passwords (bcrypt):
--   admin@bushcraft.local  →  Admin123!
--   mara@bushcraft.local   →  User123!
--   jonas@bushcraft.local  →  User123!

INSERT INTO `users` (`id`, `email`, `password_hash`, `display_name`, `role`, `created_at`) VALUES
(1, 'admin@bushcraft.local', '$2y$10$pR4DDvCicXxjFnmHM7.dcuiUdcLsi7s/UlxSBTH3LVanzk.FrjvP.', 'Camp Admin', 'admin', '2026-10-01 09:00:00'),
(2, 'mara@bushcraft.local', '$2y$10$BJMn0SBwM1MhWJSQB7cEUeQ0p0sesf.fe.LzK6zPMSIt6WBfl8hnK', 'Mara Vuk', 'user', '2026-10-12 14:22:00'),
(3, 'jonas@bushcraft.local', '$2y$10$BJMn0SBwM1MhWJSQB7cEUeQ0p0sesf.fe.LzK6zPMSIt6WBfl8hnK', 'Jonas Reed', 'user', '2026-10-18 08:41:00');

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Axes', 'axes'),
(2, 'Knives', 'knives'),
(3, 'Ignition', 'ignition'),
(4, 'Shelter', 'shelter');

INSERT INTO `tools` (`id`, `category_id`, `name`, `brand`, `description`, `price`, `stock`) VALUES
(1, 1, 'Small Forest Axe', 'Gränsfors Bruk', 'Classic Scandinavian forest axe for felling small trees and splitting kindling.', 189.00, 8),
(2, 1, 'Wildlife Hatchet', 'Gränsfors Bruk', 'Compact hatchet for camp chores and carving.', 149.00, 12),
(3, 2, 'Mora Companion Heavy Duty', 'Morakniv', 'Carbon-steel bushcraft knife with a high-vis handle.', 32.50, 40),
(4, 2, 'S1 Forest Knife', 'Helle', 'Birch-handle laminated-steel knife for carving and food prep.', 164.00, 6),
(5, 3, 'Scout Firesteel', 'Light My Fire', '2,900-strike ferrocerium rod with striker.', 14.90, 75),
(6, 3, 'Tinderbox Kit', 'Pathfinder', 'Tin with char cloth, fatwood, and a compact striker.', 24.00, 20),
(7, 4, 'Superfly Tarp', 'DD Hammocks', '3×3 m silnylon tarp for a quick overhead shelter.', 89.00, 15),
(8, 4, 'Bivi Bag', 'Snugpak', 'Lightweight emergency bivouac bag.', 69.00, 10);

INSERT INTO `reviews` (`id`, `user_id`, `tool_id`, `rating`, `comment`, `created_at`) VALUES
(1, 2, 1, 5, 'Holds an edge after a weekend of processing firewood. Balance is excellent.', '2026-10-20 11:05:00'),
(2, 3, 3, 4, 'Tough blade for the price. Needs oil after wet work because it is carbon steel.', '2026-10-21 16:40:00'),
(3, 2, 5, 5, 'Throws fat sparks even after rain. Lives in my pocket kit.', '2026-10-22 09:12:00'),
(4, 3, 7, 4, 'Pitches fast. A bit noisy in high wind but stayed dry overnight.', '2026-10-25 19:03:00');

INSERT INTO `favorites` (`id`, `user_id`, `tool_id`, `created_at`) VALUES
(1, 2, 1, '2026-10-20 11:10:00'),
(2, 2, 7, '2026-10-22 09:15:00'),
(3, 3, 3, '2026-10-21 16:45:00'),
(4, 3, 5, '2026-10-26 07:30:00');

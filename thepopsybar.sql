-- The Popsy Bar - database schema and initial content
-- Import in phpMyAdmin (Import tab) or: mysql -u root < database/thepopsybar.sql

CREATE DATABASE IF NOT EXISTS `thepopsybar` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `thepopsybar`;

-- ---------------------------------------------------------------
-- Admin users
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(60) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `last_login` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default login: admin / PopsyAdmin@2026  (change it from Admin > My Account)
INSERT IGNORE INTO `admins` (`username`, `password_hash`) VALUES
('admin', '$2y$10$iajIoCg1zci8fkMh2bHmB.1VGoInbCxSwlQmrJodgqsyj0fDr0use');

-- ---------------------------------------------------------------
-- Site settings (texts, contact details, images). Missing keys fall
-- back to the defaults in includes/settings-schema.php.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- Services / experiences (each one has its own page)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(80) NOT NULL,
  `card_title` VARCHAR(150) NOT NULL,
  `card_text` VARCHAR(255) NOT NULL DEFAULT '',
  `card_image` VARCHAR(255) NOT NULL DEFAULT '',
  `eyebrow` VARCHAR(120) NOT NULL DEFAULT '',
  `page_title` VARCHAR(150) NOT NULL,
  `page_description` TEXT NULL,
  `hero_image` VARCHAR(255) NOT NULL DEFAULT '',
  `points` TEXT NULL COMMENT 'One signature point per line',
  `layout` ENUM('detail','cards') NOT NULL DEFAULT 'detail',
  `in_footer` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `services` (`slug`, `card_title`, `card_text`, `card_image`, `eyebrow`, `page_title`, `page_description`, `hero_image`, `points`, `layout`, `in_footer`, `sort_order`) VALUES
('coffee', 'Coffee & Matcha Bar', 'Espresso, matcha & handcrafted specialties', 'assets/images/coffee-latte.jpg',
 'Beverage experiences', 'Coffee',
 'Our coffee experiences are designed to feel premium, warm, and memorable - perfect for morning events, networking moments, and elevated post-meal service.',
 'assets/images/coffee-iced-latte.jpg',
 'Espresso bar setups\nCold brew and specialty pours\nBarista-led guest service\nSignature coffee pairings', 'detail', 0, 1),
('mocktail-bar', 'Mocktail Bar', 'Shaken, garnished & made to order', 'assets/images/mocktail-blue-orchid.jpg',
 'Zero-proof luxury', 'Mocktail Bar & Ethnic Fusion Blends',
 'Our mocktail bar blends vibrant flavor, global inspiration, and polished presentation into crafted zero-proof beverages.',
 'assets/images/mocktail-lemon-mint-mojito.jpg',
 'Fruit-forward and herb-infused blends\nEthnic-inspired flavor profiles\nFresh garnishes and elevated glass styling\nCreative presentation for all guest categories', 'detail', 0, 2),
('sweet-spot', 'Dessert Experiences', 'Interactive sweets, gelato & live dessert stations', 'assets/images/dessert-sundae-cart.jpg',
 'Dessert artistry', 'The Sweet Spot',
 'The Sweet Spot brings elevated dessert presentation, indulgent flavor, and visual wow-factor to every event.',
 'assets/images/dessert-crepe-bar.jpg',
 'Luxury dessert bars\nHigh-impact visual styling and themed setups\nCustom dessert accents for events and brands\nImmersive visual moments', 'detail', 1, 3),
('live-food-stations', 'Live Food Stations', 'Made-to-order experiences prepared for your guests.', 'assets/images/food-tacos.jpg',
 'Live culinary experiences', 'Live Food Stations',
 'Immersive food experiences with flavor-forward presentation that entertain, excite, and energize guests.',
 'assets/images/food-station-plates.jpg',
 '', 'cards', 0, 4),
('corporate-catering', 'Corporate & Brand Activations', 'Elevated service designed for high-volume events.', 'assets/images/corporate-monogram-bar.jpg',
 'Corporate Events', 'Corporate Events & Brand Activations',
 'Thoughtful hospitality for executive gatherings, company celebrations, and large-scale events that demand polished service, consistency, and unforgettable presentation.',
 'assets/images/gallery-hall-stations.jpg',
 'Large-scale guest counts and beyond\nExecutive seating, buffet, and live station setups\nFast, polished service teams and event flow planning\nFlexible menus tailored to brand and guest profile', 'detail', 1, 5),
('experience-catering', 'Curated Experiences', 'Perfume, Flower & Lip Bars made personal', 'assets/images/experience-perfume-host-card.jpg',
 'Experiential moments', 'Curated Experiences',
 'We transform events into interactive stories through sensory activations, personalized guest experiences, and premium branded concepts.',
 'assets/images/experience-floral.jpg',
 'Customized perfume bar\nSignature OUD Bar\nFlower station and floral activation moments\nLip bar and personalized guest touches', 'detail', 1, 6);

-- ---------------------------------------------------------------
-- Gallery photos. page_key = 'home' (home + services page) or a service slug.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_key` VARCHAR(80) NOT NULL DEFAULT 'home',
  `image` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_page` (`page_key`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gallery_images` (`page_key`, `image`, `caption`, `sort_order`)
SELECT * FROM (
  SELECT 'home' AS p, 'assets/images/mocktail-orchid-pink.jpg' AS i, 'Orchid-garnished mocktails in Popsy Bar cups' AS c, 1 AS s UNION ALL
  SELECT 'home', 'assets/images/gallery-flower-wall.jpg', 'Floral installation at a baby shower', 2 UNION ALL
  SELECT 'home', 'assets/images/gallery-mocktail-garnish.jpg', 'Mocktail being garnished in a Popsy Bar cup', 3 UNION ALL
  SELECT 'home', 'assets/images/gallery-wedding-bar.jpg', 'Bride and groom at The Popsy Bar', 4 UNION ALL
  SELECT 'home', 'assets/images/gallery-latte-hand.jpg', 'Latte art in a Popsy Bar cup', 5 UNION ALL
  SELECT 'home', 'assets/images/gallery-tablescape.jpg', 'Bridal shower tablescape with florals', 6 UNION ALL
  SELECT 'home', 'assets/images/mocktail-lemon-mint-mojito.jpg', 'Lemon mint mojito served through the bar window', 7 UNION ALL
  SELECT 'home', 'assets/images/gallery-couple-stage.jpg', 'Couple on a floral stage with cake', 8 UNION ALL
  SELECT 'coffee', 'assets/images/gallery-latte-hand.jpg', 'Latte art served at the coffee bar', 1 UNION ALL
  SELECT 'coffee', 'assets/images/gallery-iced-coffee-duo.jpg', 'Custom labelled iced coffees', 2 UNION ALL
  SELECT 'coffee', 'assets/images/gallery-iced-latte-sign.jpg', 'Iced latte at a baby shower', 3 UNION ALL
  SELECT 'mocktail-bar', 'assets/images/mocktail-orchid-pink.jpg', 'Orchid-garnished mocktails', 1 UNION ALL
  SELECT 'mocktail-bar', 'assets/images/mocktail-blue-orchid.jpg', 'Blue mocktail with orchid and pineapple', 2 UNION ALL
  SELECT 'mocktail-bar', 'assets/images/gallery-guests-drinks.jpg', 'Guests enjoying mocktails', 3 UNION ALL
  SELECT 'sweet-spot', 'assets/images/gallery-crepe-chocolate.jpg', 'Chocolate crepe with whipped cream', 1 UNION ALL
  SELECT 'sweet-spot', 'assets/images/gallery-crepe-strawberry.jpg', 'Caramel crepe with strawberry', 2 UNION ALL
  SELECT 'sweet-spot', 'assets/images/gallery-crepe-menu.jpg', 'Crepe bar menu display', 3 UNION ALL
  SELECT 'live-food-stations', 'assets/images/gallery-food-station.jpg', 'Guest at a live food station', 1 UNION ALL
  SELECT 'live-food-stations', 'assets/images/gallery-food-plate.jpg', 'Tacos and fries plated', 2 UNION ALL
  SELECT 'live-food-stations', 'assets/images/gallery-place-setting.jpg', 'Styled place setting', 3 UNION ALL
  SELECT 'corporate-catering', 'assets/images/gallery-hall-umbrella.jpg', 'Umbrella cart at a large event', 1 UNION ALL
  SELECT 'corporate-catering', 'assets/images/gallery-place-setting.jpg', 'Event table styling', 2 UNION ALL
  SELECT 'corporate-catering', 'assets/images/gallery-food-station.jpg', 'Guests served at a live station', 3 UNION ALL
  SELECT 'experience-catering', 'assets/images/gallery-flower-wall.jpg', 'Floral wall activation', 1 UNION ALL
  SELECT 'experience-catering', 'assets/images/experience-perfume-host.jpg', 'Signature scent presented by our host', 2 UNION ALL
  SELECT 'experience-catering', 'assets/images/gallery-couple-stage.jpg', 'Floral stage moment', 3
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `gallery_images` LIMIT 1);

-- ---------------------------------------------------------------
-- Event inquiries submitted from the website form
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `event_type` VARCHAR(190) NOT NULL DEFAULT '',
  `event_date` DATE NULL DEFAULT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL,
  `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- Failed admin logins (used to block brute-force attempts)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

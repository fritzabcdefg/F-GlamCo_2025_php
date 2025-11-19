SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `db_makeup_shop_2025`;
USE `db_makeup_shop_2025`;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: customers
-- --------------------------------------------------------
CREATE TABLE `customers` (
  `customer_id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(45) NOT NULL,
  `lname` VARCHAR(45) NOT NULL,
  `fname` CHAR(32) NOT NULL,
  `addressline` VARCHAR(45) NOT NULL,
  `town` VARCHAR(45) NOT NULL,
  `zipcode` CHAR(15) DEFAULT NULL,
  `phone` VARCHAR(45) DEFAULT NULL,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `fk_customers_user_id` (`user_id`),
  CONSTRAINT `customers_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Table: items
-- --------------------------------------------------------
CREATE TABLE `items` (
  `item_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `cost_price` DECIMAL(10,2) DEFAULT NULL,
  `sell_price` DECIMAL(10,2) DEFAULT NULL,
  `supplier_name` VARCHAR(45) DEFAULT NULL,
  `category_id` INT(11) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  CONSTRAINT `items_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Table: stocks
-- --------------------------------------------------------
CREATE TABLE `stocks` (
  `item_id` INT(11) NOT NULL,
  `quantity` INT(11) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  CONSTRAINT `stocks_item_fk` FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Table: product_images
-- --------------------------------------------------------
CREATE TABLE `product_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item_id` (`item_id`),
  CONSTRAINT `product_images_item_fk` FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


-- --------------------------------------------------------
-- Table: orderinfo
-- --------------------------------------------------------
CREATE TABLE `orderinfo` (
  `orderinfo_id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `date_placed` DATE NOT NULL,
  `date_shipped` DATE DEFAULT NULL,
  `shipping` DECIMAL(7,2) DEFAULT NULL,
  `status` ENUM('Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Processing',
  PRIMARY KEY (`orderinfo_id`),
  CONSTRAINT `orderinfo_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Table: orderline
-- --------------------------------------------------------
CREATE TABLE `orderline` (
  `orderinfo_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `quantity` SMALLINT(2) DEFAULT NULL,
  PRIMARY KEY (`orderinfo_id`, `item_id`),
  CONSTRAINT `orderline_item_fk` FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orderline_order_fk` FOREIGN KEY (`orderinfo_id`) REFERENCES `orderinfo`(`orderinfo_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Table: reviews (fixed)
-- --------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `orderinfo_id` INT(11) DEFAULT NULL,
  `user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `user_name` VARCHAR(100) DEFAULT NULL,
  `rating` TINYINT(1) DEFAULT NULL,
  `comment` TEXT NOT NULL,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reviews_item_id` (`item_id`),
  KEY `idx_reviews_orderinfo_id` (`orderinfo_id`),
  KEY `idx_reviews_user_id` (`user_id`),
  CONSTRAINT `reviews_item_fk` FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reviews_order_fk` FOREIGN KEY (`orderinfo_id`) REFERENCES `orderinfo`(`orderinfo_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Views
-- --------------------------------------------------------
CREATE OR REPLACE VIEW `salesperorder` AS
SELECT
  o.orderinfo_id,
  SUM(it.sell_price * ol.quantity) AS total,
  CONCAT(UPPER(LEFT(o.status,1)), SUBSTRING(o.status,2)) AS status
FROM orderinfo o
JOIN orderline ol ON o.orderinfo_id = ol.orderinfo_id
JOIN items it ON ol.item_id = it.item_id
GROUP BY o.orderinfo_id, o.status;

CREATE OR REPLACE VIEW `orderdetails` AS
SELECT
  o.orderinfo_id,
  c.lname,
  c.fname,
  c.addressline,
  c.town,
  c.zipcode,
  c.phone,
  CONCAT(UPPER(LEFT(o.status,1)), SUBSTRING(o.status,2)) AS status,
  it.name,
  ol.quantity,
  it.sell_price
FROM orderinfo o
JOIN customers c ON o.customer_id = c.customer_id
JOIN orderline ol ON o.orderinfo_id = ol.orderinfo_id
JOIN items it ON ol.item_id = it.item_id;

-- --------------------------------------------------------
-- Seed Data
-- --------------------------------------------------------
INSERT INTO `categories` (`name`, `description`) VALUES
('Eye Makeup', 'Products for enhancing and defining the eyes, including eyeshadow, eyeliner, mascara, and brow products.'),
('Lip Makeup', 'Products for coloring and caring for lips, including lipsticks, glosses, liners, and balms.'),
('Face Makeup', 'Products for complexion and contouring, including foundation, concealer, blush, bronzer, and highlighter.');

INSERT INTO `users` (`id`, `email`, `password`, `role`, `active`, `created_at`, `updated_at`) VALUES
(1, 'pretse@gmail.com', '$2y$10$HcRYzmlnoqB9L7t0.jgU5Ot8t.TYk0zttrlmAAZ6XktTz5DA5l8Re', 'customer', 1, NULL, NULL),
(2, 'fritziecadao@gmail.com', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'admin', 1, NULL, NULL);

INSERT INTO `customers` (`customer_id`, `title`, `lname`, `fname`, `addressline`, `town`, `zipcode`, `phone`, `user_id`, `image`) VALUES
(1, '', 'Cadao', 'Fritzie', '205 ML QUEZON ST NEW LOWER BICUTN TAGUIG CITY', 'TAGUIG', '1632', '09664259993', 1, 'Snaptik.app_74656941751832609616 (2).jpg'),
(2, 'Ms.', 'Cadao', 'Admin', '205 ML QUEZON ST NEW LOWER BICUTN TAGUIG CITY', 'TAGUIG', '1632', '09664259993', 2, 'Profile.jpg');

INSERT INTO `items` (`item_id`, `name`, `description`, `cost_price`, `sell_price`, `supplier_name`, `category_id`) VALUES
(1, 'Mat Rev Mrs Kisses', 'A luxurious matte lipstick in Mrs Kisses shade, offering long-lasting wear and rich color.', 2500.00, 2889.89, 'CHARLOTTE TILBURY', 2),
(2, '3D Voluming Gloss B07 Peach 70% 5.3G', 'High-shine volumizing lip gloss in Peach shade, adds plumpness and hydration.', 250.00, 450.00, 'FWEE', 2),
(3, 'Hollywood Flawless Filter 3 Fair', 'A complexion booster that blurs, smooths, and illuminates for a radiant glow.', 1800.00, 2189.00, 'CHARLOTTE TILBURY', 3),
(4, 'PRO FILTR HYDRATING PRIMER SOFT', 'Hydrating primer that preps skin for foundation, leaving a soft, smooth finish.', 1260.00, 1599.99, 'FENTY BEAUTY', 3),
(5, 'Creme Cheek Blush Purr', 'Cream blush in Purr shade, blends seamlessly for a natural flush of color.', 589.00, 658.89, 'ISSY', 3),
(6, 'Precision Fluid Liner in Pitch Black', 'Ultra-precise liquid eyeliner in deep black, perfect for sharp lines and definition.', 450.00, 569.00, 'ISSY', 1),
(7, 'Lip Butter Balm Sweet Mint', 'Nourishing lip balm with a sweet mint flavor, provides hydration and shine.', 880.00, 999.00, 'SUMMER FRIDAYS', 2),
(8, 'Tusm Foundation Shell', 'Lightweight foundation in Shell shade, offering buildable coverage and a natural finish.', 580.00, 759.00, 'TEVIANT', 3),
(9, 'The Porefessional Face Primer - 22ML', 'Oil-free primer that minimizes the look of pores and creates a smooth base.', 480.00, 699.00, 'THE PORE', 3),
(10, 'Match Stix Matte Contour Skinstick • 7.1g', 'Matte contour stick that defines and sculpts with a creamy, blendable formula.', 2089.00, 2350.00, 'FENTY BEAUTY', 3),
(11, 'Easy Bake Loose Powder • 20g', 'Lightweight loose powder that sets makeup and controls shine for a flawless finish.', 2450.00, 2600.00, 'Huda Beauty', 3),
(12, 'Impeccable Setting Spray', 'Long-lasting setting spray that locks makeup in place while keeping skin refreshed.', 2090.00, 2320.00, 'Anastasia Beverly Hills', 3),
(13, 'Perfect Strokes Universal Volumizing Mascara', 'Volumizing mascara that lifts and defines lashes with a universal brush design.', 1250.00, 1550.00, 'Rare Beauty', 1),
(14, 'Effortless Natural Lash Collection', 'A curated set of natural-looking false lashes for effortless everyday wear.', 1400.00, 1680.00, 'Velour Lashes', 3);



INSERT INTO `stocks` (`item_id`, `quantity`) VALUES
(1, 200),
(2, 50),
(3, 100),
(4, 200),
(5, 200),
(6, 100),
(7, 400),
(8, 25),
(9, 60),
(10, 500),
(11, 25),
(12, 56),
(13, 325),
(14, 100);

INSERT INTO `product_images` (`id`, `item_id`, `filename`, `created_at`) VALUES
(1, 1, '/F&LGlamCo/product/images/1763302294_3a1d08f0_CHARLOTTE_TILBURY__Mat_Rev_Mrs_Kisses__2_.png', '2025-11-16 14:11:34'),
(2, 1, '/F&LGlamCo/product/images/1763302294_ecef6919_CHARLOTTE_TILBURY__Mat_Rev_Mrs_Kisses__3_.png', '2025-11-16 14:11:34'),
(3, 1, '/F&LGlamCo/product/images/1763302294_5f27f507_CHARLOTTE_TILBURY__Mat_Rev_Mrs_Kisses.png', '2025-11-16 14:11:34'),
(4, 2, '/F&LGlamCo/product/images/1763304349_381b23d3_FWEE_3D_Voluming_Gloss_B07_Peach_70__5.3G__2_.png', '2025-11-16 14:45:49'),
(5, 2, '/F&LGlamCo/product/images/1763304349_81a2d118_FWEE_3D_Voluming_Gloss_B07_Peach_70__5.3G.png', '2025-11-16 14:45:49'),
(6, 3, '/F&LGlamCo/product/images/1763304524_710fc686_CHARLOTTE_TILBURY_Hollywood_Flawless_Filter_3_Fair__2_.png', '2025-11-16 14:48:44'),
(7, 3, '/F&LGlamCo/product/images/1763304524_cdc3609f_CHARLOTTE_TILBURY_Hollywood_Flawless_Filter_3_Fair__3_.png', '2025-11-16 14:48:44'),
(8, 3, '/F&LGlamCo/product/images/1763304524_721590a0_CHARLOTTE_TILBURY_Hollywood_Flawless_Filter_3_Fair.png', '2025-11-16 14:48:44'),
(9, 4, '/F&LGlamCo/product/images/1763306206_3e18d70d_FENTY_FENTY_BEAUTY_PRO_FILTR_HYDRATING_PRIMER_SOFT_SILK_30ML__2_.png', '2025-11-16 15:16:46'),
(10, 4, '/F&LGlamCo/product/images/1763306206_3d3f4aee_FENTY_FENTY_BEAUTY_PRO_FILTR_HYDRATING_PRIMER_SOFT_SILK_30ML__3_.png', '2025-11-16 15:16:46'),
(11, 4, '/F&LGlamCo/product/images/1763306206_15200df9_FENTY_FENTY_BEAUTY_PRO_FILTR_HYDRATING_PRIMER_SOFT_SILK_30ML.png', '2025-11-16 15:16:46'),
(12, 5, '/F&LGlamCo/product/images/1763306902_465b374e_ISSY_Creme_Cheek_Blush_Purr__2_.png', '2025-11-16 15:28:22'),
(13, 5, '/F&LGlamCo/product/images/1763306902_ea3e0138_ISSY_Creme_Cheek_Blush_Purr.png', '2025-11-16 15:28:22'),
(14, 6, '/F&LGlamCo/product/images/1763306954_6beaa3cd_ISSY_Precision_Fluid_Liner_in_Pitch_Black.png', '2025-11-16 15:29:14'),
(15, 7, '/F&LGlamCo/product/images/1763307031_5ad98522_SUMMER_FRIDAYS_Lip_Butter_Balm_Sweet_Mint__2_.png', '2025-11-16 15:30:31'),
(16, 7, '/F&LGlamCo/product/images/1763307031_f21c4dad_SUMMER_FRIDAYS_Lip_Butter_Balm_Sweet_Mint__3_.png', '2025-11-16 15:30:31'),
(17, 7, '/F&LGlamCo/product/images/1763307031_384ab6a0_SUMMER_FRIDAYS_Lip_Butter_Balm_Sweet_Mint.png', '2025-11-16 15:30:31'),
(18, 8, '/F&LGlamCo/product/images/1763307077_f950f8cc_TEVIANT_Tusm_Foundation_Shell.png', '2025-11-16 15:31:17'),
(19, 9, '/F&LGlamCo/product/images/1763307143_ea6b84c6_The_Porefessional_Face_Primer_-_22ML.png', '2025-11-16 15:32:23'),
(20, 10, '/F&LGlamCo/product/images/1763557377_eb8ee557_Fenty_Beauty_Match_Stix_Matte_Contour_Skinstick_____7.1g__2_.png', '2025-11-19 13:02:57'),
(21, 10, '/F&LGlamCo/product/images/1763557377_ae6c8f45_Fenty_Beauty_Match_Stix_Matte_Contour_Skinstick_____7.1g__3_.png', '2025-11-19 13:02:57'),
(22, 10, '/F&LGlamCo/product/images/1763557377_7662cca9_Fenty_Beauty_Match_Stix_Matte_Contour_Skinstick_____7.1g.png', '2025-11-19 13:02:57'),
(23, 11, '/F&LGlamCo/product/images/1763563712_e33695eb_Huda_Beauty_Easy_Bake_Loose_Powder_____20g__2_.png', '2025-11-19 14:48:32'),
(24, 11, '/F&LGlamCo/product/images/1763563712_16791cba_Huda_Beauty_Easy_Bake_Loose_Powder_____20g__3_.png', '2025-11-19 14:48:32'),
(25, 11, '/F&LGlamCo/product/images/1763563712_8e161473_Huda_Beauty_Easy_Bake_Loose_Powder_____20g.png', '2025-11-19 14:48:32'),
(26, 12, '/F&LGlamCo/product/images/1763563890_f1876068_Anastasia_Beverly_Hills_Impeccable_Setting_Spray__2_.png', '2025-11-19 14:51:30'),
(27, 12, '/F&LGlamCo/product/images/1763563890_e2dd0e54_Anastasia_Beverly_Hills_Impeccable_Setting_Spray__3_.png', '2025-11-19 14:51:30'),
(28, 12, '/F&LGlamCo/product/images/1763563890_36997ce0_Anastasia_Beverly_Hills_Impeccable_Setting_Spray.png', '2025-11-19 14:51:30'),
(29, 13, '/F&LGlamCo/product/images/1763564082_047cead6_Rare_Beauty_Perfect_Strokes_Universal_Volumizing_Mascara__2_.png', '2025-11-19 14:54:42'),
(30, 13, '/F&LGlamCo/product/images/1763564082_a6b17bb7_Rare_Beauty_Perfect_Strokes_Universal_Volumizing_Mascara__3_.png', '2025-11-19 14:54:42'),
(31, 13, '/F&LGlamCo/product/images/1763564082_c077c72e_Rare_Beauty_Perfect_Strokes_Universal_Volumizing_Mascara.png', '2025-11-19 14:54:42'),
(32, 14, '/F&LGlamCo/product/images/1763564380_857c6339_Velour_Lashes_Effortless_Natural_Lash_Collection__2_.png', '2025-11-19 14:59:40'),
(33, 14, '/F&LGlamCo/product/images/1763564380_05c17290_Velour_Lashes_Effortless_Natural_Lash_Collection__3_.png', '2025-11-19 14:59:40'),
(34, 14, '/F&LGlamCo/product/images/1763564380_610749a2_Velour_Lashes_Effortless_Natural_Lash_Collection.png', '2025-11-19 14:59:40');

COMMIT;
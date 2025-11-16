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
  `cost_price` DECIMAL(5,2) DEFAULT NULL,
  `sell_price` DECIMAL(5,2) DEFAULT NULL,
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
-- Table: products
-- --------------------------------------------------------
CREATE TABLE `products` (
  `product_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `cost_price` DECIMAL(3,2) DEFAULT NULL,
  `sell_price` DECIMAL(3,2) DEFAULT NULL,
  `quantity` INT(11) DEFAULT NULL,
  `supplier_name` VARCHAR(45) DEFAULT NULL,
  `category_id` INT(11) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`)
    ON DELETE SET NULL ON UPDATE CASCADE
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
  `status` ENUM('Pending','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'processing',
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
-- Table: reviews
-- --------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `user_name` VARCHAR(100) DEFAULT NULL,
  `rating` TINYINT(1) DEFAULT NULL,
  `comment` TEXT NOT NULL,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `reviews_item_fk` FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
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

INSERT INTO `items` (`item_id`, `name`, `cost_price`, `sell_price`, `supplier_name`, `category_id`) VALUES
(1, 'AVENE AVENE Cold Cream Nutri-Nour Lip Balm 4g', 420.00, 620.00, 'Avene.Co', 2),
(2, 'Mat Rev First Dance', 999.99, 999.99, 'CHARLOTTE TILBURY', 2),
(3, '3D Voluming Gloss B07 Peach 70% 5.3G', 850.00, 999.00, 'FWEE', 2);

INSERT INTO `product_images` (`id`, `item_id`, `filename`, `created_at`) VALUES
(1, 1, '/F&LGlamCo/product/images/1763266092_d00473f7_AVENE_Cold_Cream_Nutri-Nour_Lip_Balm_4g__2_.png', '2025-11-16 04:08:12'),
(2, 1, '/F&LGlamCo/product/images/1763266092_f3a47532_AVENE_Cold_Cream_Nutri-Nour_Lip_Balm_4g__3_.png', '2025-11-16 04:08:12'),
(3, 1, '/F&LGlamCo/product/images/1763266092_bfd9ad33_AVENE_Cold_Cream_Nutri-Nour_Lip_Balm_4g.png', '2025-11-16 04:08:12'),
(4, 2, '/F&LGlamCo/product/images/1763268842_1d8e9755_Mat_Rev_Mrs_Kisses__2_.png', '2025-11-16 04:54:02'),
(5, 2, '/F&LGlamCo/product/images/1763268842_82b93d0d_Mat_Rev_Mrs_Kisses__3_.png', '2025-11-16 04:54:02'),
(6, 2, '/F&LGlamCo/product/images/1763268842_28f38872_Mat_Rev_Mrs_Kisses.png', '2025-11-16 04:54:02'),
(7, 3, '/F&LGlamCo/product/images/1763268875_3d7b78ef_3D_Voluming_Gloss_B07_Peach_70__5.3G__2_.png', '2025-11-16 04:54:35'),
(8, 3, '/F&LGlamCo/product/images/1763268875_4f1ac694_3D_Voluming_Gloss_B07_Peach_70__5.3G.png', '2025-11-16 04:54:35');

INSERT INTO `stocks` (`item_id`, `quantity`) VALUES
(1, 100),
(2, 50),
(3, 100);

INSERT INTO `users` (`id`, `email`, `password`, `role`, `active`, `created_at`, `updated_at`) VALUES
(1, 'pretse@gmail.com', '$2y$10$HcRYzmlnoqB9L7t0.jgU5Ot8t.TYk0zttrlmAAZ6XktTz5DA5l8Re', 'customer', 1, NULL, NULL),
(2, 'fritziecadao@gmail.com', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'admin', 1, NULL, NULL);


COMMIT;

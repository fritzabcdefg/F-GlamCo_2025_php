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
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `fk_customers_user_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- add active flag to users (0 = deactivated, 1 = active)
ALTER TABLE `users` ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1;

-- --------------------------------------------------------
-- Table: customers
-- --------------------------------------------------------
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  `lname` varchar(45) NOT NULL,
  `fname` char(32) NOT NULL,
  `addressline` varchar(45) NOT NULL,
  `town` varchar(45) NOT NULL,
  `zipcode` char(15) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `user_id` bigint(10) UNSIGNED NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `fk_customers_user_id` (`user_id`)
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
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `cost_price` decimal(5,2) DEFAULT NULL,
  `sell_price` decimal(5,2) DEFAULT NULL,
  `supplier_name` varchar(45) DEFAULT NULL,
  `img_path` varchar(45) NOT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Add category_id to items for categorization (safe ALTER for existing DBs)
ALTER TABLE `items` 
  ADD COLUMN `category_id` INT(11) DEFAULT NULL;

-- Add FK to categories if the categories table exists
ALTER TABLE `items`
  ADD CONSTRAINT `items_category_id_fk` FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------
-- Table: orderinfo
-- --------------------------------------------------------
CREATE TABLE `orderinfo` (
  `orderinfo_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `date_placed` date NOT NULL,
  `date_shipped` date DEFAULT NULL,
  `shipping` decimal(7,2) DEFAULT NULL,
  `status` enum('processing','delivered','cancelled') NOT NULL DEFAULT 'processing',
  PRIMARY KEY (`orderinfo_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orderinfo_customer_id_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
-- Table: orderline
-- --------------------------------------------------------
CREATE TABLE `orderline` (
  `orderinfo_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` smallint(2) DEFAULT NULL,
  KEY `fk_items_has_orders_items1` (`item_id`),
  KEY `fk_items_has_orders_orders1` (`orderinfo_id`),
  CONSTRAINT `fk_items_has_orders_items1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_items_has_orders_orders1` FOREIGN KEY (`orderinfo_id`) REFERENCES `orderinfo` (`orderinfo_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


-- --------------------------------------------------------
-- Table: stocks
-- --------------------------------------------------------
CREATE TABLE `stocks` (
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `stock_item_id_fk` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`)
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
  CONSTRAINT `product_images_item_fk` FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


--
-- Views used by admin pages
-- salesperorder: total sales grouped by order
-- orderdetails: join customers, orderinfo, orderline, and items for details

CREATE OR REPLACE VIEW `salesperorder` AS
SELECT
  o.orderinfo_id AS orderinfo_id,
  SUM(it.sell_price * ol.quantity) AS total,
  CONCAT(UPPER(LEFT(o.status,1)), SUBSTRING(o.status,2)) AS status
FROM `orderinfo` o
JOIN `orderline` ol ON o.orderinfo_id = ol.orderinfo_id
JOIN `items` it ON ol.item_id = it.item_id
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
FROM `orderinfo` o
JOIN `customers` c ON o.customer_id = c.customer_id
JOIN `orderline` ol ON o.orderinfo_id = ol.orderinfo_id
JOIN `items` it ON ol.item_id = it.item_id;

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
  KEY `idx_reviews_item` (`item_id`),
  KEY `idx_reviews_user` (`user_id`),
  CONSTRAINT `reviews_item_fk` FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

COMMIT;

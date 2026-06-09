-- ============================================================
-- Database Setup Script for khamar12_khaled
-- Run as: sudo mysql < setup_db.sql
-- ============================================================

-- 1. Create database
CREATE DATABASE IF NOT EXISTS `khamar12_khaled`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2. Create user and grant privileges
CREATE USER IF NOT EXISTS 'khamar12_khaled'@'localhost' IDENTIFIED BY 'khaled2005';
GRANT ALL PRIVILEGES ON `khamar12_khaled`.* TO 'khamar12_khaled'@'localhost';
FLUSH PRIVILEGES;

-- 3. Use the database
USE `khamar12_khaled`;

-- ============================================================
-- Migrations (in order)
-- ============================================================

-- 001_users
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(40) NULL,
  locale ENUM('en','ar') NOT NULL DEFAULT 'en',
  role ENUM('customer','seller','admin') NOT NULL DEFAULT 'customer',
  status ENUM('active','pending','banned') NOT NULL DEFAULT 'active',
  email_verified_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SOURCE /home/khaled/Desktop/project/backend/database/migrations/002_seller_profiles.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/003_addresses.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/004_categories.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/005_products.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/006_product_images.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/007_carts.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/008_cart_items.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/009_wishlists.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/010_orders.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/011_order_items.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/012_reviews.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/013_coupons.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/014_coupon_redemptions.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/015_password_resets.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/017_inventory_movements.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/018_audit_log.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/019_rate_limits.sql
SOURCE /home/khaled/Desktop/project/backend/database/migrations/020_settings.sql

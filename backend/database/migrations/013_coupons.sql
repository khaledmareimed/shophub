CREATE TABLE coupons (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL,
  type ENUM('percent','fixed') NOT NULL,
  value DECIMAL(10,2) NOT NULL,
  min_subtotal DECIMAL(10,2) NULL,
  max_discount DECIMAL(10,2) NULL,
  starts_at TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  usage_limit INT UNSIGNED NULL,
  used_count INT UNSIGNED NOT NULL DEFAULT 0,
  scope ENUM('all','category','product') NOT NULL DEFAULT 'all',
  scope_id BIGINT UNSIGNED NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_coupon_code (code),
  KEY idx_coupon_active (active, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seller_profiles (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  business_name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  description TEXT NULL,
  logo_path VARCHAR(512) NULL,
  status ENUM('pending','approved','suspended') NOT NULL DEFAULT 'pending',
  approved_at TIMESTAMP NULL,
  approved_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_seller_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_seller_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY uq_seller_slug (slug),
  KEY idx_seller_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  seller_id BIGINT UNSIGNED NOT NULL,
  name_snapshot VARCHAR(255) NOT NULL,
  image_path_snapshot VARCHAR(512) NULL,
  price_snapshot DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  fulfillment_status ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_oi_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT,
  KEY idx_oi_order (order_id),
  KEY idx_oi_seller (seller_id, fulfillment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

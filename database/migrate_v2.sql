USE eventticket_gb;

ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS discount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total,
  ADD COLUMN IF NOT EXISTS coupon_id INT UNSIGNED NULL AFTER discount,
  ADD COLUMN IF NOT EXISTS payment_ref VARCHAR(120) NULL AFTER payment_method,
  ADD COLUMN IF NOT EXISTS stripe_session_id VARCHAR(190) NULL AFTER payment_ref,
  ADD COLUMN IF NOT EXISTS refunded_at DATETIME NULL AFTER status,
  ADD COLUMN IF NOT EXISTS refund_reason VARCHAR(255) NULL AFTER refunded_at;

-- MySQL 8.0 may not support IF NOT EXISTS on ADD COLUMN in older XAMPP; use procedure-safe approach below via PHP migrator.
-- Fallback columns created by migrate.php if needed.

CREATE TABLE IF NOT EXISTS coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    value DECIMAL(10,2) NOT NULL,
    max_uses INT UNSIGNED NULL,
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    min_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    expires_at DATETIME NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS seats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    ticket_type_id INT UNSIGNED NULL,
    row_label VARCHAR(10) NOT NULL,
    seat_number INT UNSIGNED NOT NULL,
    status ENUM('available','held','sold') NOT NULL DEFAULT 'available',
    UNIQUE KEY uq_seat (event_id, row_label, seat_number),
    CONSTRAINT fk_seats_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE events ADD COLUMN IF NOT EXISTS has_seats TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE order_items
  ADD COLUMN IF NOT EXISTS seat_id INT UNSIGNED NULL;

ALTER TABLE tickets
  ADD COLUMN IF NOT EXISTS seat_label VARCHAR(20) NULL AFTER code,
  ADD COLUMN IF NOT EXISTS holder_name VARCHAR(120) NULL AFTER seat_label;

CREATE TABLE IF NOT EXISTS complaints (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    nif VARCHAR(20) NULL,
    subject VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open','in_progress','closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity VARCHAR(80) NULL,
    entity_id INT UNSIGNED NULL,
    meta TEXT NULL,
    ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_action (action),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    email VARCHAR(190) NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_ip_time (ip, attempted_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO coupons (code, type, value, max_uses, min_total, expires_at, active) VALUES
('BEMVINDO10', 'percent', 10, 1000, 0, '2027-12-31 23:59:59', 1),
('GALA5', 'fixed', 5.00, 200, 20, '2027-12-31 23:59:59', 1);

UPDATE events SET has_seats = 1 WHERE slug = 'noite-de-gala-no-sta-djunto';

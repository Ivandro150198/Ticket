<?php

/**
 * php database/migrate.php
 */
require __DIR__ . '/../config/env.php';
env_load(__DIR__ . '/../.env');

$cfg = require __DIR__ . '/../config/db.php';
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['port'], $cfg['database']);
$pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $st->execute([$table, $column]);
    return (int) $st->fetchColumn() > 0;
}

function addColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    if (!columnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        echo "Added {$table}.{$column}\n";
    }
}

addColumn($pdo, 'orders', 'discount', 'DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total');
addColumn($pdo, 'orders', 'coupon_id', 'INT UNSIGNED NULL AFTER discount');
addColumn($pdo, 'orders', 'payment_ref', 'VARCHAR(120) NULL AFTER payment_method');
addColumn($pdo, 'orders', 'stripe_session_id', 'VARCHAR(190) NULL AFTER payment_ref');
addColumn($pdo, 'orders', 'refunded_at', 'DATETIME NULL AFTER status');
addColumn($pdo, 'orders', 'refund_reason', 'VARCHAR(255) NULL AFTER refunded_at');
addColumn($pdo, 'events', 'has_seats', 'TINYINT(1) NOT NULL DEFAULT 0');
addColumn($pdo, 'events', 'country', "CHAR(2) NOT NULL DEFAULT 'PT' AFTER city");
addColumn($pdo, 'events', 'currency', "CHAR(3) NOT NULL DEFAULT 'EUR' AFTER country");
addColumn($pdo, 'orders', 'currency', "CHAR(3) NOT NULL DEFAULT 'EUR' AFTER total");
addColumn($pdo, 'orders', 'country', "CHAR(2) NOT NULL DEFAULT 'PT' AFTER currency");
addColumn($pdo, 'order_items', 'seat_id', 'INT UNSIGNED NULL');
addColumn($pdo, 'tickets', 'seat_label', 'VARCHAR(20) NULL AFTER code');
addColumn($pdo, 'tickets', 'holder_name', 'VARCHAR(120) NULL AFTER seat_label');
addColumn($pdo, 'events', 'promoter_name', 'VARCHAR(190) NULL AFTER venue');
addColumn($pdo, 'events', 'promoter_nif', 'VARCHAR(20) NULL AFTER promoter_name');
addColumn($pdo, 'events', 'age_rating', "VARCHAR(20) NOT NULL DEFAULT 'Todos' AFTER currency");
addColumn($pdo, 'events', 'capacity', 'INT UNSIGNED NULL AFTER age_rating');
addColumn($pdo, 'orders', 'buyer_nif', 'VARCHAR(20) NULL AFTER buyer_phone');
addColumn($pdo, 'orders', 'gdpr_consent_at', 'DATETIME NULL AFTER buyer_nif');
addColumn($pdo, 'orders', 'invoice_number', 'VARCHAR(40) NULL AFTER payment_ref');
addColumn($pdo, 'users', 'nif', 'VARCHAR(20) NULL AFTER phone');

// Backfill promotor a partir do nome do utilizador
$pdo->exec("UPDATE events e JOIN users u ON u.id = e.producer_id SET e.promoter_name = COALESCE(NULLIF(e.promoter_name,''), u.name) WHERE e.promoter_name IS NULL OR e.promoter_name = ''");


$pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
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
) ENGINE=InnoDB");

$pdo->exec("CREATE TABLE IF NOT EXISTS seats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    ticket_type_id INT UNSIGNED NULL,
    row_label VARCHAR(10) NOT NULL,
    seat_number INT UNSIGNED NOT NULL,
    status ENUM('available','held','sold') NOT NULL DEFAULT 'available',
    UNIQUE KEY uq_seat (event_id, row_label, seat_number),
    CONSTRAINT fk_seats_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB");

$pdo->exec("CREATE TABLE IF NOT EXISTS complaints (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    nif VARCHAR(20) NULL,
    subject VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open','in_progress','closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
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
) ENGINE=InnoDB");

$pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    email VARCHAR(190) NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_ip_time (ip, attempted_at)
) ENGINE=InnoDB");

$pdo->exec("INSERT IGNORE INTO coupons (code, type, value, max_uses, min_total, expires_at, active) VALUES
('BEMVINDO10', 'percent', 10, 1000, 0, '2027-12-31 23:59:59', 1),
('GALA5', 'fixed', 5.00, 200, 20, '2027-12-31 23:59:59', 1)");

$pdo->exec("UPDATE events SET has_seats = 1 WHERE slug = 'noite-de-gala-no-sta-djunto'");

// Seed seats for gala if empty
$eventId = (int) $pdo->query("SELECT id FROM events WHERE slug = 'noite-de-gala-no-sta-djunto'")->fetchColumn();
$count = (int) $pdo->query("SELECT COUNT(*) FROM seats WHERE event_id = {$eventId}")->fetchColumn();
if ($eventId && $count === 0) {
    $typeId = (int) $pdo->query("SELECT id FROM ticket_types WHERE event_id = {$eventId} ORDER BY price ASC LIMIT 1")->fetchColumn();
    $ins = $pdo->prepare('INSERT INTO seats (event_id, ticket_type_id, row_label, seat_number, status) VALUES (?,?,?,?,?)');
    foreach (['A', 'B', 'C', 'D'] as $row) {
        for ($n = 1; $n <= 12; $n++) {
            $ins->execute([$eventId, $typeId ?: null, $row, $n, 'available']);
        }
    }
    echo "Seeded seats for event {$eventId}\n";
}

echo "Migration complete.\n";
